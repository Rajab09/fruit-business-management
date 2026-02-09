# Fruit Business Management System

A full-stack business management system for fruit retailers built with PHP and MySQL. Features inventory tracking, sales processing, employee attendance, supplier management, and a dashboard with real-time statistics.

## Features

### Dashboard
- KPI statistics cards (total fruits, employees, sales, suppliers)
- Low stock alerts with threshold monitoring
- Recent sales overview with payment status

### Inventory Management
- Full CRUD operations on fruits and categories
- Stock tracking with minimum threshold alerts
- Expiration date management
- Automatic stock deduction on sales (via database trigger)
- Complete inventory transaction audit log

### Sales Processing
- Two-step sales workflow: create sale → add items
- Real-time stock availability display
- Automatic subtotal calculation (generated column)
- Payment status tracking (Pending/Paid)
- Database trigger prevents overselling

### Employee Management
- Employee CRUD with roles and hourly rates
- Attendance tracking with clock in/out
- Automatic punctuality detection (Late/On Time via trigger)
- Shift management

### Supplier Management
- Supplier contact information CRUD
- Supplier-fruit relationship tracking

## Tech Stack

- **Backend**: PHP 8.2+ (procedural with prepared statements)
- **Database**: MySQL/MariaDB 10.4+
- **Frontend**: HTML5, CSS3, Vanilla JavaScript
- **Server**: XAMPP (Apache + PHP + MySQL)

## Database Schema

**9 tables** with foreign keys, triggers, and generated columns:

| Table | Purpose |
|-------|---------|
| `fruit` | Product inventory with stock levels |
| `fruit_category` | Product categories |
| `employee` | Staff with roles and hourly rates |
| `sale` | Sales transactions |
| `sale_item` | Line items with auto-calculated subtotals |
| `inventory_log` | Audit trail of all stock movements |
| `attendance` | Employee clock in/out records |
| `shift` | Work shift definitions |
| `supplier` | Supplier contact information |
| `supplier_fruit` | Supplier-fruit relationships |

### Database Triggers
- **trg_check_stock_before_sale_item** - Validates stock, decrements quantity, logs transaction
- **trg_attendance_status** - Auto-sets punctuality status based on clock-in vs shift time

## Project Structure

```
├── index.php                    # Dashboard
├── fruit_master.sql             # Database schema & sample data
├── includes/
│   ├── db.php                   # Database connection (mysqli)
│   ├── header.php               # Navigation sidebar & topbar
│   └── footer.php               # Footer & JavaScript
├── pages/
│   ├── fruits.php               # Fruits CRUD & inventory
│   ├── categories.php           # Categories CRUD
│   ├── employees.php            # Employee CRUD
│   ├── sales.php                # Sales management
│   ├── suppliers.php            # Supplier CRUD
│   ├── attendance.php           # Attendance tracking
│   └── inventory.php            # Transaction audit log
└── assets/
    └── css/
        └── style.css            # Responsive styling
```

## Getting Started

### Prerequisites
- XAMPP (or any Apache + PHP + MySQL stack)
- PHP 8.2+
- MySQL/MariaDB 10.4+

### Installation

1. Clone the repository:
   ```bash
   git clone https://github.com/Rajab09/fruit-business-management.git
   ```

2. Copy the project to your web server directory:
   ```bash
   cp -r fruit-business-management /Applications/XAMPP/htdocs/
   ```

3. Import the database:
   - Open phpMyAdmin (http://localhost/phpmyadmin)
   - Create a database named `fruit_master`
   - Import `fruit_master.sql`

4. Access the application:
   ```
   http://localhost/fruit-business-management/
   ```

## Security

- Prepared statements throughout (SQL injection prevention)
- HTML output escaping with `htmlspecialchars()` (XSS prevention)
- Type casting for numeric inputs
- Input validation on all forms

## UI/UX

- Responsive sidebar navigation with emoji icons
- Mobile-friendly with hamburger menu (< 768px)
- Color-coded status badges and alerts
- Grid-based statistics cards
- Hover effects on table rows

## License

This project is developed as part of a university database course project.
