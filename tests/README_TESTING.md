# Invoice System - Testing Guide

This document explains how to test the invoice system functionality.

## Test Suite Overview

The testing suite includes:
1. **API Tests** - Backend PHP API endpoint tests
2. **Frontend Tests** - JavaScript unit tests
3. **Test Data** - Sample data for testing scenarios

## Running the Tests

### 1. API Tests (Backend)

**Prerequisites:**
- XAMPP/Apache and MySQL running
- Database schema and seed data loaded
- PHP cURL extension enabled

**How to Run:**
1. Open your browser
2. Navigate to: `http://localhost/DeveloperChallengeInvoice/tests/api_tests.php`
3. The tests will run automatically and display results

**What It Tests:**
- GET all customers
- GET all products
- GET configuration settings
- POST create invoice with taxable items
- POST create invoice validation (missing fields)
- GET specific invoice by ID
- GET non-existent invoice (error handling)
- POST update configuration
- Tax calculation with mixed taxable/non-taxable items
- Tax calculation with all non-taxable items

### 2. Frontend Tests (JavaScript)

**How to Run:**
1. Open your browser
2. Navigate to: `http://localhost/DeveloperChallengeInvoice/tests/frontend_tests.html`
3. Click the "Run Tests" button
4. View the results

**What It Tests:**
- Currency symbol mapping for different currencies
- Currency formatting with comma separators
- Tax calculation logic (taxable vs non-taxable items)
- Line total calculations
- Decimal quantity handling
- HTML escaping (XSS prevention)

### 3. Test Data

**How to Load:**
```sql
-- In MySQL command line or phpMyAdmin
source tests/test_data.sql;
```

**What It Provides:**
- 3 additional test customers
- 4 additional test products (including inactive product)
- 3 test invoices with different scenarios:
  - Mixed taxable/non-taxable items
  - All non-taxable items
  - All taxable items with different tax rate

## Manual Testing Checklist

### Configuration Testing
- [ ] Update company name in database - verify it displays in UI
- [ ] Change default currency (USD → EUR) - verify symbols change
- [ ] Change tax percent - verify it applies to new invoices
- [ ] Change company date - verify configuration is stored

### Invoice Creation Testing
- [ ] Create invoice with all required fields
- [ ] Create invoice without customer (should fail)
- [ ] Create invoice without items (should fail)
- [ ] Create invoice with invalid date (should fail)

### Taxable Items Testing
- [ ] Add item with taxable checkbox checked - verify tax calculated
- [ ] Add item with taxable checkbox unchecked - verify no tax
- [ ] Add multiple items with mixed taxable status - verify correct tax
- [ ] Create invoice with all non-taxable items - verify 0 tax

### Currency Display Testing
```sql
-- Test USD
UPDATE configuration SET config_value = 'USD' WHERE config_key = 'default_currency';

-- Test EUR
UPDATE configuration SET config_value = 'EUR' WHERE config_key = 'default_currency';

-- Test GBP
UPDATE configuration SET config_value = 'GBP' WHERE config_key = 'default_currency';
```
- [ ] Verify currency symbol changes in totals
- [ ] Verify currency symbol changes in line items
- [ ] Verify currency symbol changes in success messages

### UI Testing
- [ ] Verify no icons are displayed (text-only buttons)
- [ ] Verify tax rate is read-only (cannot be changed)
- [ ] Verify settings button is not visible
- [ ] Verify responsive design on mobile devices

## Expected Test Results

### API Tests
- **Total Tests:** 10
- **Expected Pass Rate:** 100%
- **Critical Tests:**
  - Tax calculation with mixed items (subtotal: 250, tax: 20, total: 270)
  - Tax calculation with no taxable items (subtotal: 150, tax: 0, total: 150)
  - Configuration update and retrieval

### Frontend Tests
- **Total Tests:** 13
- **Expected Pass Rate:** 100%
- **Critical Tests:**
  - Currency symbols for USD, EUR, GBP
  - Tax calculation on taxable items only
  - Zero tax when all items non-taxable

## Common Issues and Solutions

### Issue: API tests fail with connection errors
**Solution:** Ensure XAMPP is running and database is accessible

### Issue: Tests show "Invoice not found"
**Solution:** Run seed_data.sql to populate the database

### Issue: Currency symbols not displaying
**Solution:** Ensure your browser supports UTF-8 encoding

### Issue: Tax calculations are incorrect
**Solution:** Verify taxable field is properly set in database (1 or 0)

## Test Data Cleanup

To remove test data after testing:

```sql
-- Remove test invoices
DELETE FROM invoices WHERE invoice_number LIKE 'TEST-%';

-- Remove test customers
DELETE FROM customers WHERE customer_name LIKE 'Test Customer%';

-- Remove test products
DELETE FROM products WHERE product_code LIKE 'TEST-%';
```

## Automated Testing (Future Enhancement)

Consider adding:
- PHPUnit for more comprehensive PHP testing
- Jest or Mocha for JavaScript testing
- Selenium for end-to-end browser testing
- CI/CD integration with GitHub Actions

## Contributing

When adding new features:
1. Add corresponding tests in api_tests.php
2. Add frontend tests in frontend_tests.html
3. Document the test cases in this README
4. Ensure all tests pass before committing

## Support

If you encounter issues with the tests:
1. Check that all prerequisites are met
2. Verify database connection settings
3. Review browser console for JavaScript errors
4. Check Apache error logs for PHP errors
