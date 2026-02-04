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
**GET** `/api/customers.php`
- List all customers
- **Response:**
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "customer_name": "Acme Corporation",
      "email": "accounting@acme.com",
      "phone": "+1-555-0101",
      "address": "123 Business Street",
      "city": "New York",
      "postal_code": "10001",
      "country": "USA"
    }
  ]
}
```

### Products
**GET** `/api/products.php`
- List all products
- **Response:**
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "product_code": "WEB-001",
      "product_name": "Website Development",
      "description": "Custom website development service",
      "unit_price": "5000.00",
      "unit": "project",
      "active": "1"
    }
  ]
}
```

### Configuration
**GET** `/api/config.php`
- Get all configuration settings
- **Response:**
```json
{
  "success": true,
  "data": {
    "company_name": "Your Company Name",
    "default_currency": "USD",
    "company_date": "2026-01-01",
    "tax_percent": "10.00"
  }
}
```

**POST** `/api/config.php`
- Update configuration settings
- **Request Body:**
```json
{
  "company_name": "My Company Inc.",
  "default_currency": "EUR",
  "tax_percent": "15.00"
}
```

### Invoices
**POST** `/api/invoices.php`
- Create new invoice
- **Request Body:**
```json
{
  "customer_id": 1,
  "invoice_date": "2026-02-04",
  "due_date": "2026-03-04",
  "tax_rate": 10,
  "notes": "Thank you for your business",
  "items": [
    {
      "product_id": 1,
      "description": "Website Development",
      "quantity": 1,
      "unit_price": 5000.00,
      "taxable": true
    },
    {
      "product_id": 2,
      "description": "Hosting (non-taxable)",
      "quantity": 3,
      "unit_price": 50.00,
      "taxable": false
    }
  ]
}
```
- **Response:**
```json
{
  "success": true,
  "data": {
    "invoice_id": 5,
    "invoice_number": "INV-2026-005",
    "subtotal": 5150.00,
    "tax_amount": 500.00,
    "total_amount": 5650.00
  },
  "message": "Invoice created successfully"
}
```

**GET** `/api/invoices.php?id={id}`
- Get invoice by ID
- **Response:**
```json
{
  "success": true,
  "data": {
    "id": 1,
    "invoice_number": "INV-2026-001",
    "customer_id": 1,
    "customer_name": "Acme Corporation",
    "invoice_date": "2026-02-01",
    "due_date": "2026-03-01",
    "subtotal": "6500.00",
    "tax_rate": "10.00",
    "tax_amount": "650.00",
    "total_amount": "7150.00",
    "status": "sent",
    "items": [
      {
        "id": 1,
        "product_id": 1,
        "description": "Website Development",
        "quantity": "1.00",
        "unit_price": "5000.00",
        "line_total": "5000.00",
        "taxable": 1
      }
    ]
  }
}
```

## Postman Collection

A Postman collection is included for easy API testing:

**Import Collection:**
1. Open Postman
2. Click **Import**
3. Select file: `Postman/Developer Invoice Assessment.postman_collection.json`
4. Collection will be added to your workspace

**Available Requests:**
- Get Customers
- Get Products
- Get Invoice Details
- Create Invoice (configure request body)

**Base URL:** `http://localhost/DeveloperChallengeInvoice/api`

## Key Features

### Configuration System
- **Company Settings** - Stored in database configuration table
- **Multi-Currency** - Supports 20+ currencies (USD, EUR, GBP, JPY, etc.)
- **Dynamic Tax Rate** - Configurable default tax percentage
- **Read-Only UI** - Tax rate cannot be changed from the invoice form

### Taxable Line Items
- **Individual Item Control** - Each line item can be marked taxable or non-taxable
- **Smart Tax Calculation** - Tax only calculated on items marked as taxable
- **Mixed Invoices** - Support for invoices with both taxable and non-taxable items

### Currency Support
**Supported Currencies:**
- USD ($), EUR (€), GBP (£), JPY (¥), CNY (¥), INR (₹)
- AUD (A$), CAD (C$), CHF (Fr), SEK (kr), NZD (NZ$)
- KRW (₩), SGD (S$), HKD (HK$), NOK (kr), MXN ($)
- ZAR (R), BRL (R$), RUB (₽), TRY (₺)

### Automated Tests

**API Tests:**
- Navigate to: `http://localhost/DeveloperChallengeInvoice/tests/api_tests.php`
- Tests all API endpoints automatically
- Visual results with pass/fail indicators

**Frontend Tests:**
- Navigate to: `http://localhost/DeveloperChallengeInvoice/tests/frontend_tests.html`
- Click "Run Tests" button
- Tests JavaScript functions and calculations

**Test Data:**
```sql
-- Load additional test data
source tests/test_data.sql;
```

See `tests/README_TESTING.md` for complete testing documentation.

### Manual Testing

1. Navigate to the main page
2. Select a customer from the dropdown
3. Add invoice items:
   - Select a product
   - Enter quantity
   - Unit price auto-fills from product
   - Check/uncheck "Tax" checkbox for taxable items
   - Click "Add Item"
4. Review totals (tax calculated only on taxable items)
5. Click "Save Invoice"
6. Check response for success/error messages

###Configuration Management

Configuration settings are managed via **direct SQL updates**:

```sql
-- Update company name
UPDATE configuration SET config_value = 'My Company Inc.' WHERE config_key = 'company_name';

-- Change currency (updates all UI symbols)
UPDATE configuration SET config_value = 'EUR' WHERE config_key = 'default_currency';

-- Set default tax rate
UPDATE configuration SET config_value = '15.00' WHERE config_key = 'tax_percent';
```

## Notes

- All monetary values stored as DECIMAL for precision
- Timestamps automatically tracked (created_at, updated_at)
- Foreign key constraints ensure data integrity
- Transaction support for invoice creation (atomic operations)
- Tax calculations respect individual item taxable flags
- Currency symbols dynamically update based on configuration
- Clean UI with no icons - text-only interface

## Additional Documentation

- **Configuration & Features:** See `CONFIGURATION_FEATURES.md`
- **Testing Guide:** See `tests/README_TESTING.md`
- **API Testing:** Import `Postman/Developer Invoice Assessment.postman_collection.json`

## Repository

GitHub: [https://github.com/icewinds/DeveloperChallengeInvoice](https://github.com/icewinds/DeveloperChallengeInvoice
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
