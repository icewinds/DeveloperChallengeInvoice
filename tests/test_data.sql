-- Test Data for Invoice System
-- This script creates additional test data for comprehensive testing

USE invoice_system;

-- Insert additional test customers
INSERT INTO customers (customer_name, email, phone, address, city, postal_code, country) VALUES
('Test Customer A', 'testa@example.com', '+1-555-1111', '100 Test Street', 'Test City', '11111', 'USA'),
('Test Customer B', 'testb@example.com', '+1-555-2222', '200 Test Avenue', 'Test Town', '22222', 'USA'),
('Test Customer C', 'testc@example.com', '+1-555-3333', '300 Test Boulevard', 'Test Village', '33333', 'USA');

-- Insert additional test products
INSERT INTO products (product_code, product_name, description, unit_price, unit, active) VALUES
('TEST-001', 'Test Product 1', 'Test description 1', 100.00, 'pcs', 1),
('TEST-002', 'Test Product 2', 'Test description 2', 50.00, 'pcs', 1),
('TEST-003', 'Test Product 3', 'Test description 3', 25.00, 'pcs', 1),
('TEST-INACTIVE', 'Inactive Product', 'Should not appear in selections', 999.00, 'pcs', 0);

-- Create test invoice with mixed taxable/non-taxable items
INSERT INTO invoices (invoice_number, customer_id, invoice_date, due_date, subtotal, tax_rate, tax_amount, total_amount, status, notes)
VALUES ('TEST-001', (SELECT id FROM customers WHERE customer_name = 'Test Customer A'), '2026-02-04', '2026-03-04', 300.00, 10.00, 20.00, 320.00, 'draft', 'Test invoice with mixed taxable items');

SET @test_invoice_id = LAST_INSERT_ID();

INSERT INTO invoice_items (invoice_id, product_id, description, quantity, unit_price, line_total, taxable, sort_order) VALUES
(@test_invoice_id, (SELECT id FROM products WHERE product_code = 'TEST-001'), 'Taxable Test Item', 2.00, 100.00, 200.00, 1, 1),
(@test_invoice_id, (SELECT id FROM products WHERE product_code = 'TEST-002'), 'Non-taxable Test Item', 2.00, 50.00, 100.00, 0, 2);

-- Create test invoice with all non-taxable items
INSERT INTO invoices (invoice_number, customer_id, invoice_date, due_date, subtotal, tax_rate, tax_amount, total_amount, status, notes)
VALUES ('TEST-002', (SELECT id FROM customers WHERE customer_name = 'Test Customer B'), '2026-02-04', '2026-03-04', 150.00, 10.00, 0.00, 150.00, 'draft', 'Test invoice with all non-taxable items');

SET @test_invoice_id2 = LAST_INSERT_ID();

INSERT INTO invoice_items (invoice_id, product_id, description, quantity, unit_price, line_total, taxable, sort_order) VALUES
(@test_invoice_id2, (SELECT id FROM products WHERE product_code = 'TEST-002'), 'Non-taxable Item 1', 1.00, 50.00, 50.00, 0, 1),
(@test_invoice_id2, (SELECT id FROM products WHERE product_code = 'TEST-003'), 'Non-taxable Item 2', 4.00, 25.00, 100.00, 0, 2);

-- Create test invoice with all taxable items
INSERT INTO invoices (invoice_number, customer_id, invoice_date, due_date, subtotal, tax_rate, tax_amount, total_amount, status, notes)
VALUES ('TEST-003', (SELECT id FROM customers WHERE customer_name = 'Test Customer C'), '2026-02-04', '2026-03-04', 200.00, 15.00, 30.00, 230.00, 'sent', 'Test invoice with all taxable items and 15% tax');

SET @test_invoice_id3 = LAST_INSERT_ID();

INSERT INTO invoice_items (invoice_id, product_id, description, quantity, unit_price, line_total, taxable, sort_order) VALUES
(@test_invoice_id3, (SELECT id FROM products WHERE product_code = 'TEST-001'), 'Taxable Item 1', 1.00, 100.00, 100.00, 1, 1),
(@test_invoice_id3, (SELECT id FROM products WHERE product_code = 'TEST-001'), 'Taxable Item 2', 1.00, 100.00, 100.00, 1, 2);

-- Test different currency configurations
INSERT INTO configuration (config_key, config_value, description) VALUES
('test_currency_eur', 'EUR', 'Test Euro currency'),
('test_currency_gbp', 'GBP', 'Test British Pound currency')
ON DUPLICATE KEY UPDATE config_value = VALUES(config_value);

SELECT 'Test data inserted successfully!' AS message;
SELECT COUNT(*) AS test_customers FROM customers WHERE customer_name LIKE 'Test Customer%';
SELECT COUNT(*) AS test_products FROM products WHERE product_code LIKE 'TEST-%';
SELECT COUNT(*) AS test_invoices FROM invoices WHERE invoice_number LIKE 'TEST-%';
