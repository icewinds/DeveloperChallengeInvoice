-- Migration script to add configuration table and taxable field
-- Run this if you already have an existing database

USE invoice_system;

-- Add configuration table
CREATE TABLE IF NOT EXISTS configuration (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    config_key VARCHAR(100) NOT NULL UNIQUE,
    config_value TEXT NOT NULL,
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_config_key (config_key)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert default configuration values (if not exists)
INSERT IGNORE INTO configuration (config_key, config_value, description) VALUES
('company_name', 'Your Company Name', 'Company name displayed on invoices'),
('default_currency', 'USD', 'Default currency for invoices (e.g., USD, EUR, GBP)'),
('company_date', '2026-01-01', 'Company founding or fiscal year start date'),
('tax_percent', '10.00', 'Default tax percentage (0-100)');

-- Add taxable column to invoice_items if it doesn't exist
ALTER TABLE invoice_items 
ADD COLUMN IF NOT EXISTS taxable TINYINT(1) DEFAULT 1 AFTER line_total;

-- Update existing records to be taxable by default
UPDATE invoice_items SET taxable = 1 WHERE taxable IS NULL;
