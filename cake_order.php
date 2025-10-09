<?php

function h($v) { return htmlspecialchars($v ?? '', ENT_QUOTES, 'UTF-8'); }

function price_table(): array {
    return [
        '9-round'  => [1 => 12.00, 2 => 24.00, 3 => 36.00],
        '12-round' => [1 => 14.00, 2 => 28.00, 3 => 42.00],
        '18-square'=> [1 => 30.00],
        '24-square'=> [1 => 48.00],
    ];
}

function compute_base_price(string $cakeType, int $layers): float {
    $table = price_table();
    if (!isset($table[$cakeType])) return 0.0;
    if (in_array($cakeType, ['18-square','24-square'], true)) {
        $layers = 1;
    }
    return $table[$cakeType][$layers] ?? 0.0;
}

function apply_state_adjustment(float $base, string $state): float {
    $state = strtoupper(trim($state));
    if ($state === 'MO' || $state === 'KS' || stripos($state, 'MISSOURI') !== false || stripos($state, 'KANSAS') !== false) {
        return round($base * 0.85, 2);
    }
    return round($base * 1.20, 2);
}

function compute_delivery_date(string $orderDateStr): ?string {
    try {
        $order = new DateTime($orderDateStr);
        $order->modify('+7 days');
        return $order->format('Y-m-d');
    } catch (Exception $e) {
        return null;
    }
}

function write_csv(array $row, string $file = 'orders.csv'): void {
    $isNew = !file_exists($file);
    $fp = fopen($file, 'a');
    if ($fp) {
        if ($isNew) {
            fputcsv($fp, array_keys($row));
        }
        fputcsv($fp, array_values($row));
        fclose($fp);
    }
}

$errors = [];
$isPost = ($_SERVER['REQUEST_METHOD'] === 'POST');

if ($isPost) {
    $name    = trim($_POST['name'] ?? '');
    $order_date = trim($_POST['order_date'] ?? '');
    $address = trim($_POST['address'] ?? '');
    $city    = trim($_POST['city'] ?? '');
    $state   = trim($_POST['state'] ?? '');
    $zip     = trim($_POST['zip'] ?? '');
    $phone   = trim($_POST['phone'] ?? '');
    $email   = trim($_POST['email'] ?? '');

    $cake_type = $_POST['cake_type'] ?? '';
    $layers    = (int)($_POST['layers'] ?? 1);

    $flavor   = $_POST['flavor'] ?? '';
    $frosting = $_POST['frosting_color'] ?? '';
    $trim     = $_POST['trim_color'] ?? '';
    $textc    = $_POST['text_color'] ?? '';
    $message  = trim($_POST['cake_message'] ?? '');
    $details  = trim($_POST['details'] ?? '');

    if ($name === '') $errors[] = 'Name is required.';
    if ($order_date === '') $errors[] = 'Order date is required.';
    if ($cake_type === '') $errors[] = 'Please choose a cake type.';
    if (!in_array($cake_type, ['9-round','12-round','18-square','24-square'], true)) $errors[] = 'Invalid cake type.';
    if (in_array($cake_type, ['9-round','12-round'], true) && !in_array($layers, [1,2,3], true)) $errors[] = 'Invalid number of layers for round cake.';
    if (in_array($cake_type, ['18-square','24-square'], true)) $layers = 1;
    if ($email && !filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Invalid email format.';

    $base = compute_base_price($cake_type, $layers);
    if ($base <= 0) $errors[] = 'Could not resolve cake price. Check selection.';
    $total = apply_state_adjustment($base, $state);

    $delivery_date = compute_delivery_date($order_date);
    if (!$delivery_date) $errors[] = 'Invalid order date.';

    if (!$errors) {
        $csvRow = [
            'timestamp'      => (new DateTime('now'))->format('Y-m-d H:i:s'),
            'name'           => $name,
            'order_date'     => $order_date,
            'delivery_date'  => $delivery_date,
            'address'        => $address,
            'city'           => $city,
            'state'          => $state,
            'zip'            => $zip,
            'phone'          => $phone,
            'email'          => $email,
            'cake_type'      => $cake_type,
            'layers'         => $layers,
            'flavor'         => $flavor,
            'frosting_color' => $frosting,
            'trim_color'     => $trim,
            'text_color'     => $textc,
            'cake_message'   => $message,
            'details'        => $details,
            'base_price'     => number_format($base, 2, '.', ''),
            'total_price'    => number_format($total, 2, '.', ''),
        ];
        write_csv($csvRow);
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Cake Order Form & Invoice</title>
  <link rel="stylesheet" href="style.css" />
</head>
<body>
  <div class="wrap">
    <header>
      <h1>Pumpkin Patch Pastries — Online Cake Order Form</h1>
    </header>

    <?php if ($isPost && !$errors): ?>
      <section class="card">
        <div class="content">
          <h2>Invoice</h2>
          <div class="invoice">
            <div>
              <div class="kv">
                <div>Customer</div><div><?=h($name)?></div>
                <div>Email</div><div><?=h($email)?></div>
                <div>Phone</div><div><?=h($phone)?></div>
                <div>Order Date</div><div><?=h($order_date)?></div>
                <div>Delivery Date</div><div><strong><?=h($delivery_date)?></strong></div>
                <div>Address</div><div><?=h($address)?>, <?=h($city)?>, <?=h($state)?> <?=h($zip)?></div>
                <div>Cake</div>
                <div>
                  <?php
                    $cakeLabelMap = [
                      '9-round'  => '9" Round',
                      '12-round' => '12" Round',
                      '18-square'=> '18×18" Square',
                      '24-square'=> '24×24" Square',
                    ];
                    $cakeLabel = $cakeLabelMap[$cake_type] ?? $cake_type;
                  ?>
                  <?=h($cakeLabel)?><?= in_array($cake_type,['9-round','12-round'], true) ? ' · '.h($layers).' layer(s)' : '' ?>
                </div>
                <div>Flavor</div><div><?=h($flavor)?></div>
                <div>Frosting</div><div><?=h($frosting)?></div>
                <div>Trim</div><div><?=h($trim)?></div>
                <div>Text Color</div><div><?=h($textc)?></div>
              </div>
              <div class="divider"></div>
              <div>
                <div class="muted">Cake Message</div>
                <p><?= nl2br(h($message)) ?></p>
              </div>
              <div class="divider"></div>
              <div>
                <div class="muted">Details</div>
                <p><?= nl2br(h($details)) ?></p>
              </div>
            </div>
            <div>
              <div class="total">
                Total Due<br>
                $<?= number_format($total, 2) ?>
              </div>
              <div class="thankyou" style="margin-top:1rem">
                <span>Thank you!</span>
              </div>
              <div class="actions" style="margin-top:1rem">
                <a class="btn-ghost" href="<?= h($_SERVER['PHP_SELF']) ?>">Place another order</a>
              </div>
            </div>
          </div>
        </div>
      </section>
    <?php else: ?>
      <?php if ($isPost && $errors): ?>
        <section class="card"><div class="content"><div class="errors"><strong>Fix the following:</strong><ul><?php foreach ($errors as $e): ?><li><?=h($e)?></li><?php endforeach; ?></ul></div></div></section>
      <?php endif; ?>

      <section class="card">
        <div class="content">
          <h2>Order Form</h2>
          <form method="post" action="<?= h($_SERVER['PHP_SELF']) ?>" novalidate>
            <label for="name">Name:</label>
            <input type="text" id="name" name="name" value="<?= h($_POST['name'] ?? '') ?>" required>

            <label for="order_date">Order Date:</label>
            <input type="date" id="order_date" name="order_date" value="<?= h($_POST['order_date'] ?? '') ?>" required>

            <label for="address">Address:</label>
            <input type="text" id="address" name="address" value="<?= h($_POST['address'] ?? '') ?>">

            <label for="city">City:</label>
            <input type="text" id="city" name="city" value="<?= h($_POST['city'] ?? '') ?>">

            <label for="state">State:</label>
            <input type="text" id="state" name="state" value="<?= h($_POST['state'] ?? '') ?>">

            <label for="zip">Zip:</label>
            <input type="text" id="zip" name="zip" value="<?= h($_POST['zip'] ?? '') ?>">

            <label for="phone">Phone:</label>
            <input type="text" id="phone" name="phone" value="<?= h($_POST['phone'] ?? '') ?>">

            <label for="email">Email:</label>
            <input type="email" id="email" name="email" value="<?= h($_POST['email'] ?? '') ?>">

            <label for="cake_type">Type of Cake:</label>
            <select id="cake_type" name="cake_type" required>
              <option value="">-- Select --</option>
              <option value="9-round"  <?= (($_POST['cake_type'] ?? '')==='9-round') ? 'selected' : '' ?>>9" Round</option>
              <option value="12-round" <?= (($_POST['cake_type'] ?? '')==='12-round') ? 'selected' : '' ?>>12" Round</option>
              <option value="18-square"<?= (($_POST['cake_type'] ?? '')==='18-square') ? 'selected' : '' ?>>18×18" Square</option>
              <option value="24-square"<?= (($_POST['cake_type'] ?? '')==='24-square') ? 'selected' : '' ?>>24×24" Square</option>
            </select>

            <label for="layers">Number of Layers:</label>
            <select id="layers" name="layers">
              <option value="1" <?= (($_POST['layers'] ?? '')==='1') ? 'selected' : '' ?>>1</option>
              <option value="2" <?= (($_POST['layers'] ?? '')==='2') ? 'selected' : '' ?>>2</option>
              <option value="3" <?= (($_POST['layers'] ?? '')==='3') ? 'selected' : '' ?>>3</option>
            </select>

            <label for="flavor">Flavor:</label>
            <select id="flavor" name="flavor">
              <option value="Vanilla">Vanilla</option>
              <option value="Chocolate">Chocolate</option>
              <option value="Strawberry">Strawberry</option>
            </select>

            <label for="frosting_color">Frosting Color:</label>
            <select id="frosting_color" name="frosting_color">
              <option value="White">White</option>
              <option value="Yellow">Yellow</option>
              <option value="Red">Red</option>
              <option value="Purple">Purple</option>
              <option value="Blue-Green">Blue-Green</option>
            </select>

            <label for="trim_color">Trim Color:</label>
            <select id="trim_color" name="trim_color">
              <option value="White">White</option>
              <option value="Yellow">Yellow</option>
              <option value="Red">Red</option>
              <option value="Purple">Purple</option>
              <option value="Blue-Green">Blue-Green</option>
            </select>

            <label for="text_color">Text Color:</label>
            <select id="text_color" name="text_color">
              <option value="White">White</option>
              <option value="Yellow">Yellow</option>
              <option value="Red">Red</option>
              <option value="Purple">Purple</option>
              <option value="Blue-Green">Blue-Green</option>
            </select>

            <label for="cake_message">Cake Message:</label>
            <textarea id="cake_message" name="cake_message"><?= h($_POST['cake_message'] ?? '') ?></textarea>

            <label for="details">Details:</label>
            <textarea id="details" name="details"><?= h($_POST['details'] ?? '') ?></textarea>

            <input type="submit" value="Submit Order">
          </form>
        </div>
      </section>

    <?php endif; ?>

    <footer>
      <p>© <?= date('Y') ?> Pumpkin Patch Pastries</p>
    </footer>
  </div>

  <script>
    const cakeTypeSel = document.getElementById('cake_type');
    const layersSel   = document.getElementById('layers');
    function syncLayers(){
      const v = cakeTypeSel.value;
      const isSquare = v === '18-square' || v === '24-square';
      layersSel.value = '1';
      layersSel.disabled = isSquare;
    }
    if (cakeTypeSel && layersSel) {
      cakeTypeSel.addEventListener('change', syncLayers);
      syncLayers();
    }
  </script>
</body>
</html>
