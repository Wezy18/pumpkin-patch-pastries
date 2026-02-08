# Pumpkin Patch Pastries — Online Cake Order Form

A simple, server-side PHP web application for placing custom cake orders and generating an invoice-style confirmation page.

This project simulates an online bakery order workflow, allowing users to customize a cake, submit order details, and receive a calculated total and delivery date. Valid orders are stored in a CSV file for easy review and record keeping.

The focus of this project is clean structure, readable logic, and thoughtful user experience rather than heavy frameworks or databases.

## About the Project

Pumpkin Patch Pastries allows users to:

- Enter customer and delivery information
- Customize a cake (size, layers, flavor, colors, message)
- Automatically calculate pricing based on business rules
- Generate a clear invoice-style summary after submission
- Persist orders in a lightweight CSV format

The interface is intentionally simple and friendly, with a warm visual design inspired by a small bakery storefront.

## Features

- Custom cake order form with multiple configuration options
- Server-side validation for required fields and valid selections
- Dynamic pricing based on:
  - Cake size and number of layers
  - State-based pricing adjustments
- Automatic delivery date calculation (7 days after order date)
- Invoice-style confirmation page with order summary
- CSV file storage for submitted orders
- Responsive, card-based layout
- Small client-side enhancement to enforce valid layer selection

## Pricing Logic

### Base Cake Pricing

- **9" Round**
  - 1 layer — $12
  - 2 layers — $24
  - 3 layers — $36
- **12" Round**
  - 1 layer — $14
  - 2 layers — $28
  - 3 layers — $42
- **18×18" Square**
  - 1 layer — $30
- **24×24" Square**
  - 1 layer — $48

Square cakes are restricted to one layer.

### State Adjustment

- **Missouri (MO) or Kansas (KS):** 15% discount  
- **All other states:** 20% increase  

Pricing adjustments are applied server-side to ensure consistency and correctness.

## Tech Stack

- **PHP** — form handling, validation, calculations, CSV writing
- **HTML5** — structure and semantic markup
- **CSS3** — layout, responsive design, and visual styling
- **Vanilla JavaScript** — small UI enhancement for layer selection

No frameworks or external libraries are required.

## How It Works

1. User fills out the order form and submits it.
2. PHP validates all inputs and normalizes selections.
3. The application calculates:
   - Base price
   - State-adjusted total
   - Delivery date (order date + 7 days)
4. If validation passes:
   - The order is appended to `orders.csv`
   - An invoice-style confirmation page is displayed
5. If validation fails:
   - Errors are shown clearly above the form for correction

## Project Structure

```text
project-root/
├── index.php # Main application logic and rendering
├── style.css # Layout and visual styling
├── orders.csv # Created automatically after first valid order
└── README.md
```

## How to Run Locally

### Option 1: PHP Built-in Server

1. Clone the repository:
   ```bash
   git clone https://github.com/Wezy18/pumpkin-patch-pastries.git
   cd pumpkin-patch-pastries
2. Start the server:
   ```bash
   php -S localhost:8000
   ```
3. Open in your browser:
   ```text
   http://localhost:8000
   ```

### Option 2: Local Web Server (XAMPP / MAMP / WAMP)

   1. Place the project folder in your server’s web directory.
   2. Start Apache.
   3. Navigate to the project in your browser.

## CSV Output

Each successful order is appended to orders.csv with headers automatically generated on first run. Stored fields include:

- Timestamp
- Customer and contact information
- Order and delivery dates
- Cake configuration details
- Base price and total price

This approach keeps the project lightweight while demonstrating server-side persistence.

## Possible Future Improvements

- Replace CSV storage with a relational database
- Add client-side price preview before submission
- Strengthen validation for phone and address fields
- Add an admin view to review submitted orders
- Improve accessibility with additional ARIA support

## Purpose & Learning Goals

This project was built as a learning exercise to practice:

- PHP form handling and validation
- Server-side business logic
- Data persistence without a database
- Clean separation of structure, style, and behavior
- Designing a complete, user-facing web workflow
- It reflects an emphasis on clarity, correctness, and maintainable code.

## License

This project is for educational and personal use. Feel free to fork, modify, and expand it.
