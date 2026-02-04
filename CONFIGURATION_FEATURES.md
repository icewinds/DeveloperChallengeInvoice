# Configuration and Taxable Items Feature

## New Features Added

### 1. Configuration Table
A new configuration table has been added to store company settings:

- **Company Name**: Your company's name (displayed on invoices)
- **Default Currency**: Default currency code (e.g., USD, EUR, GBP) - **Automatically changes currency symbols throughout the UI**
- **Company Date**: Company founding or fiscal year start date
- **Tax Percent**: Default tax percentage (0-100) - **Applied automatically to all invoices**

### 2. Taxable Line Items
Invoice line items now support a "taxable" flag:
- Each line item can be marked as taxable or non-taxable
- Tax is calculated only on items marked as taxable
- Checkbox is checked by default (items are taxable by default)
- Unchecking the "Tax" checkbox will exclude that item from tax calculation

## Installation

### For New Installations:
1. Run the schema: `database/schema.sql`
2. Run the seed data: `database/seed_data.sql`

### For Existing Databases:
Run the migration script: `database/migration_add_config.sql`

## Usage

### Managing Configuration (SQL Only)
Configuration is managed **directly in the database** using SQL commands. Users cannot change these settings from the UI.

To update configuration, run SQL commands like:

```sql
-- Update company name
UPDATE configuration SET config_value = 'My Company Inc.' WHERE config_key = 'company_name';

-- Update default tax percentage
UPDATE configuration SET config_value = '15.00' WHERE config_key = 'tax_percent';

-- Update default currency
UPDATE configuration SET config_value = 'EUR' WHERE config_key = 'default_currency';

-- Update company date
UPDATE configuration SET config_value = '2026-01-01' WHERE config_key = 'company_date';
```

**Supported Currency Codes:**
- USD ($), EUR (€), GBP (£), JPY (¥), CNY (¥), INR (₹)
- AUD (A$), CAD (C$), CHF (Fr), SEK (kr), NZD (NZ$)
- KRW (₩), SGD (S$), HKD (HK$), NOK (kr), MXN ($)
- ZAR (R), BRL (R$), RUB (₽), TRY (₺)

When you change the `default_currency` in the database, all currency symbols in the invoice UI will automatically update to match.

### Tax Rate Behavior
- The tax rate is **read-only** in the invoice form
- It is automatically loaded from the configuration table
- Users **cannot change** the tax rate when creating invoices
- To change the tax rate, update it in the database configuration table (see above)

### Managing Taxable Items
When adding invoice items:
1. Add items as usual (select product, quantity, price)
2. Each item has a "Tax" checkbox in the Taxable column
3. Check the box to include the item in tax calculation
4. Uncheck the box to exclude the item from tax calculation
5. Tax amount is automatically recalculated based on taxable items only

## API Changes

### New Endpoint: `/api/config.php`

**GET** - Retrieve all configuration settings
```javascript
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

**POST** - Update configuration settings
```javascript
{
  "company_name": "My Company",
  "default_currency": "USD",
  "company_date": "2026-01-01",
  "tax_percent": "10.00"
}
```

### Updated: `/api/invoices.php`

The invoice items now support a `taxable` field:

```javascript
{
  "customer_id": 1,
  "invoice_date": "2026-02-04",
  "items": [
    {
      "product_id": 1,
      "description": "Service",
      "quantity": 1,
      "unit_price": 100,
      "taxable": true  // NEW: indicates if item is taxable
    },
    {
      "product_id": 2,
      "description": "Non-taxable item",
      "quantity": 1,
      "unit_price": 50,
      "taxable": false  // This item will not be taxed
    }
  ]
}
```

## Database Schema Changes

### New Table: `configuration`
```sql
CREATE TABLE configuration (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    config_key VARCHAR(100) NOT NULL UNIQUE,
    config_value TEXT NOT NULL,
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);
```

### Updated Table: `invoice_items`
Added `taxable` column:
```sql
AL**Currency symbols automatically update** based on the `default_currency` setting in the configuration
- Tax calculation only applies to items marked as taxable
- The company name from configuration is displayed in the page header
- All existing invoice items are marked as taxable by default when running the migration
- **To modify configuration settings, update them directly in the database** using SQL UPDATE statements
- All UI elements are text-based with no icon dependencie
## Notes

- Configuration is loaded automatically when the page loads
- The tax rate from configuration is displayed and **cannot be changed** in the invoice form
- Tax calculation only applies to items marked as taxable
- The company name from configuration is displayed in the page header
- All existing invoice items are marked as taxable by default when running the migration
- **To modify configuration settings, update them directly in the database** using SQL UPDATE statements
