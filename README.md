# Developer Challenge - Invoicing System

A PHP and MySQL based invoicing application for capturing and managing invoices.

## Requirements

- XAMPP (Apache + MySQL + PHP 7.4+)
- Modern web browser

## Installation & Setup

1. **Clone/Copy this project to XAMPP's htdocs folder:**
   ```
   C:\xampp\htdocs\invoice-system\
   ```

2. **Create the database:**
   - Open phpMyAdmin (http://localhost/phpmyadmin)
   - Create a new database named `invoice_system`
   - Import the SQL schema: `database/schema.sql`
   - Optionally import sample data: `database/seed_data.sql`

3. **Configure database connection:**
   - Open `includes/config.php`
   - Update database credentials if needed (default: root with no password)

4. **Start XAMPP:**
   - Start Apache and MySQL services

5. **Access the application:**
   - Open browser and navigate to: `http://localhost/invoice-system/`

## Project Structure

```
invoice-system/
├── index.php              # Main invoice capture UI
├── api/                   # API endpoints
│   ├── invoices.php       # Invoice CRUD operations
│   ├── customers.php      # Customer data
│   └── products.php       # Product data
├── includes/              # Core PHP files
│   ├── config.php         # Database configuration
│   ├── db.php            # Database connection
│   └── functions.php      # Helper functions
├── database/              # Database scripts
│   ├── schema.sql         # Database schema
│   └── seed_data.sql      # Sample data
├── assets/                # Static assets
│   ├── css/
│   │   └── style.css      # Application styles
│   └── js/
│       └── app.js         # Frontend JavaScript
└── README.md              # This file
```

## Features

- ✅ Create invoices with multiple line items
- ✅ Select from pre-configured customers
- ✅ Select from pre-configured products
- ✅ Automatic total calculations
- ✅ Input validation (client & server-side)
- ✅ Error handling
- ✅ RESTful API design
- ✅ SQL injection prevention (prepared statements)
- ✅ XSS prevention
- ✅ Clean, maintainable code structure

## API Endpoints

### Customers
- `GET /api/customers.php` - List all customers

### Products
- `GET /api/products.php` - List all products

### Invoices
- `POST /api/invoices.php` - Create new invoice
- `GET /api/invoices.php?id={id}` - Get invoice by ID

## Technical Details

- **No frameworks** - Pure PHP implementation
- **No ORM** - Plain SQL with PDO prepared statements
- **Security** - Input validation, parameterized queries, XSS prevention
- **Scalability** - Normalized database design, separation of concerns
- **Code Quality** - Clear naming, comments, error handling

## Database Schema

### Tables
1. **customers** - Customer information
2. **products** - Product catalog with pricing
3. **invoices** - Invoice header information
4. **invoice_items** - Invoice line items

See `database/schema.sql` for complete schema definition.

## Testing

1. Navigate to the main page
2. Select a customer from the dropdown
3. Add invoice items:
   - Select a product
   - Enter quantity
   - Unit price auto-fills from product
   - Click "Add Item"
4. Review totals
5. Click "Save Invoice"
6. Check response for success/error messages

## Notes

- All monetary values stored as DECIMAL for precision
- Timestamps automatically tracked (created_at, updated_at)
- Foreign key constraints ensure data integrity
- Transaction support for invoice creation (atomic operations)
