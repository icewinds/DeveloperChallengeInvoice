-- Sample data for Invoice System
USE invoice_system;

-- Insert default configuration values
INSERT INTO configuration (config_key, config_value, description) VALUES
('company_name', 'Your Company Name', 'Company name displayed on invoices'),
('default_currency', 'USD', 'Default currency for invoices (e.g., USD, EUR, GBP)'),
('company_date', '2026-01-01', 'Company founding or fiscal year start date'),
('tax_percent', '10.00', 'Default tax percentage (0-100)');

-- Insert sample customers
INSERT INTO customers (customer_name, email, phone, address, city, postal_code, country) VALUES
('Acme Corporation', 'accounting@acme.com', '+1-555-0101', '123 Business Street', 'New York', '10001', 'USA'),
('Tech Solutions Ltd', 'billing@techsolutions.com', '+1-555-0102', '456 Innovation Ave', 'San Francisco', '94102', 'USA'),
('Global Imports Inc', 'finance@globalimports.com', '+1-555-0103', '789 Trade Boulevard', 'Chicago', '60601', 'USA'),
('Premier Services', 'accounts@premierservices.com', '+1-555-0104', '321 Service Road', 'Boston', '02101', 'USA'),
('Digital Dynamics', 'payments@digitaldynamics.com', '+1-555-0105', '654 Tech Park', 'Austin', '78701', 'USA'),
('Metro Wholesale', 'ap@metrowholesale.com', '+1-555-0106', '987 Commerce Center', 'Seattle', '98101', 'USA'),
('Quality Products Co', 'billing@qualityproducts.com', '+1-555-0107', '147 Industrial Way', 'Denver', '80201', 'USA'),
('Elite Enterprises', 'invoices@eliteenterprises.com', '+1-555-0108', '258 Corporate Drive', 'Miami', '33101', 'USA');

-- Insert sample products
INSERT INTO products (product_code, product_name, description, unit_price, unit, active) VALUES
('WEB-001', 'Website Development', 'Custom website development service', 5000.00, 'project', 1),
('WEB-002', 'Website Maintenance', 'Monthly website maintenance and updates', 500.00, 'month', 1),
('APP-001', 'Mobile App Development', 'iOS and Android app development', 15000.00, 'project', 1),
('APP-002', 'App Maintenance', 'Monthly app maintenance and support', 800.00, 'month', 1),
('HOST-001', 'Web Hosting - Basic', 'Basic web hosting package', 50.00, 'month', 1),
('HOST-002', 'Web Hosting - Premium', 'Premium web hosting with SSD', 150.00, 'month', 1),
('CONS-001', 'Consulting - Junior', 'Junior consultant hourly rate', 75.00, 'hour', 1),
('CONS-002', 'Consulting - Senior', 'Senior consultant hourly rate', 150.00, 'hour', 1),
('CONS-003', 'Consulting - Expert', 'Expert consultant hourly rate', 250.00, 'hour', 1),
('SEO-001', 'SEO Optimization', 'Search engine optimization service', 1200.00, 'month', 1),
('SEO-002', 'SEO Audit', 'Comprehensive SEO audit', 800.00, 'project', 1),
('DESIGN-001', 'Logo Design', 'Professional logo design', 500.00, 'project', 1),
('DESIGN-002', 'UI/UX Design', 'User interface and experience design', 2000.00, 'project', 1),
('TRAIN-001', 'Training Session', 'Technical training session', 300.00, 'session', 1),
('SUPP-001', 'Technical Support', 'Hourly technical support', 100.00, 'hour', 1),
('LIC-001', 'Software License - Basic', 'Basic software license', 99.00, 'year', 1),
('LIC-002', 'Software License - Pro', 'Professional software license', 299.00, 'year', 1),
('LIC-003', 'Software License - Enterprise', 'Enterprise software license', 999.00, 'year', 1),
('DATA-001', 'Database Setup', 'Database design and setup', 1500.00, 'project', 1),
('DATA-002', 'Database Migration', 'Data migration service', 2500.00, 'project', 1);

-- Insert a sample invoice
INSERT INTO invoices (invoice_number, customer_id, invoice_date, due_date, subtotal, tax_rate, tax_amount, total_amount, status, notes)
VALUES ('INV-2026-001', 1, '2026-02-01', '2026-03-01', 6500.00, 10.00, 650.00, 7150.00, 'sent', 'Thank you for your business!');

-- Insert sample invoice items
INSERT INTO invoice_items (invoice_id, product_id, description, quantity, unit_price, line_total, taxable, sort_order) VALUES
(1, 1, 'Website Development', 1.00, 5000.00, 5000.00, 1, 1),
(1, 12, 'Logo Design', 1.00, 500.00, 500.00, 1, 2),
(1, 5, 'Web Hosting - Basic (3 months)', 3.00, 50.00, 150.00, 0, 3),
(1, 7, 'Consulting - Junior (8.5 hours)', 8.50, 75.00, 637.50, 1, 4),
(1, 15, 'Technical Support (2.125 hours)', 2.125, 100.00, 212.50, 1, 5);

-- Insert another sample invoice
INSERT INTO invoices (invoice_number, customer_id, invoice_date, due_date, subtotal, tax_rate, tax_amount, total_amount, status, notes)
VALUES ('INV-2026-002', 2, '2026-02-03', '2026-03-03', 2400.00, 10.00, 240.00, 2640.00, 'draft', '');

INSERT INTO invoice_items (invoice_id, product_id, description, quantity, unit_price, line_total, taxable, sort_order) VALUES
(2, 10, 'SEO Optimization', 2.00, 1200.00, 2400.00, 1, 1);
