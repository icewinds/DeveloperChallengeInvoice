/**
 * Invoice System - Frontend JavaScript
 */

// Global state
let customers = [];
let products = [];
let invoiceItems = [];
let itemCounter = 0;
let appConfig = {}; // Store configuration
let currencySymbol = '$'; // Default currency symbol

// Configuration
const API_BASE = './api';
const TAX_RATE = 10; // Default tax rate percentage (fallback)

// Currency symbol mapping
const CURRENCY_SYMBOLS = {
    'USD': '$',
    'EUR': '€',
    'GBP': '£',
    'JPY': '¥',
    'CNY': '¥',
    'INR': '₹',
    'AUD': 'A$',
    'CAD': 'C$',
    'CHF': 'Fr',
    'SEK': 'kr',
    'NZD': 'NZ$',
    'KRW': '₩',
    'SGD': 'S$',
    'HKD': 'HK$',
    'NOK': 'kr',
    'MXN': '$',
    'ZAR': 'R',
    'BRL': 'R$',
    'RUB': '₽',
    'TRY': '₺'
};

/**
 * Get currency symbol from currency code
 */
function getCurrencySymbol(currencyCode) {
    return CURRENCY_SYMBOLS[currencyCode] || currencyCode + ' ';
}

// Initialize on page load
document.addEventListener('DOMContentLoaded', function() {
    initializeApp();
});

/**
 * Initialize the application
 */
async function initializeApp() {
    try {
        await Promise.all([
            loadConfiguration(),
            loadCustomers(),
            loadProducts()
        ]);
        
        setupEventListeners();
        updateTotals();
        
    } catch (error) {
        console.error('Initialization error:', error);
        showAlert('Failed to initialize application', 'error');
    }
}

/**
 * Load configuration from API
 */
async function loadConfiguration() {
    try {
        const response = await fetch(`${API_BASE}/config.php`);
        const data = await response.json();
        
        if (data.success) {
            appConfig = data.data;
            // Update tax rate with configured value
            if (appConfig.tax_percent) {
                document.getElementById('tax_rate').value = appConfig.tax_percent;
                document.getElementById('tax_rate_display').textContent = `(${appConfig.tax_percent}%)`;
            }
            // Update currency symbol
            if (appConfig.default_currency) {
                currencySymbol = getCurrencySymbol(appConfig.default_currency);
            }
            // Update page title if company name is set
            if (appConfig.company_name) {
                document.querySelector('.header h1').textContent = appConfig.company_name;
            }
        } else {
            console.warn('Failed to load configuration, using defaults');
        }
    } catch (error) {
        console.error('Error loading configuration:', error);
        // Continue with default values
    }
}

/**
 * Load customers from API
 */
async function loadCustomers() {
    try {
        const response = await fetch(`${API_BASE}/customers.php`);
        const data = await response.json();
        
        if (data.success) {
            customers = data.data;
            populateCustomerDropdown();
        } else {
            throw new Error(data.error || 'Failed to load customers');
        }
    } catch (error) {
        console.error('Error loading customers:', error);
        throw error;
    }
}

/**
 * Load products from API
 */
async function loadProducts() {
    try {
        const response = await fetch(`${API_BASE}/products.php`);
        const data = await response.json();
        
        if (data.success) {
            products = data.data;
        } else {
            throw new Error(data.error || 'Failed to load products');
        }
    } catch (error) {
        console.error('Error loading products:', error);
        throw error;
    }
}

/**
 * Populate customer dropdown
 */
function populateCustomerDropdown() {
    const select = document.getElementById('customer_id');
    select.innerHTML = '<option value="">-- Select Customer --</option>';
    
    customers.forEach(customer => {
        const option = document.createElement('option');
        option.value = customer.id;
        option.textContent = customer.customer_name;
        select.appendChild(option);
    });
}

/**
 * Setup event listeners
 */
function setupEventListeners() {
    // Add item button
    document.getElementById('addItemBtn').addEventListener('click', addInvoiceItem);
    
    // Save invoice button
    document.getElementById('saveInvoiceBtn').addEventListener('click', saveInvoice);
}

/**
 * Add a new invoice item row
 */
function addInvoiceItem() {
    itemCounter++;
    const tbody = document.getElementById('itemsTableBody');
    
    // Create new row
    const row = document.createElement('tr');
    row.dataset.itemId = itemCounter;
    
    row.innerHTML = `
        <td>
            <select class="form-control item-product" required>
                <option value="">-- Select Product --</option>
                ${products.map(p => `
                    <option value="${p.id}" 
                            data-price="${p.unit_price}" 
                            data-name="${escapeHtml(p.product_name)}"
                            data-unit="${escapeHtml(p.unit)}">
                        ${escapeHtml(p.product_name)} (${escapeHtml(p.product_code)})
                    </option>
                `).join('')}
            </select>
        </td>
        <td>
            <input type="text" 
                   class="form-control item-description" 
                   placeholder="Description"
                   required>
        </td>
        <td>
            <input type="number" 
                   class="form-control item-quantity" 
                   placeholder="Qty"
                   min="0.01"
                   step="0.01"
                   value="1"
                   required>
        </td>
        <td>
            <input type="number" 
                   class="form-control item-price" 
                   placeholder="0.00"
                   min="0"
                   step="0.01"
                   required>
        </td>
        <td class="text-right item-total">0.00</td>
        <td class="text-center">
            <label class="taxable-checkbox">
                <input type="checkbox" class="item-taxable" checked>
                <span>Tax</span>
            </label>
        </td>
        <td class="text-center">
            <button type="button" class="btn btn-danger btn-remove" onclick="removeItem(${itemCounter})">
                Remove
            </button>
        </td>
    `;
    
    tbody.appendChild(row);
    
    // Add event listeners to new row
    const productSelect = row.querySelector('.item-product');
    const description = row.querySelector('.item-description');
    const quantity = row.querySelector('.item-quantity');
    const price = row.querySelector('.item-price');
    const taxable = row.querySelector('.item-taxable');
    
    productSelect.addEventListener('change', function() {
        const selected = this.options[this.selectedIndex];
        if (selected.value) {
            description.value = selected.dataset.name;
            price.value = selected.dataset.price;
            calculateLineTotal(row);
        }
    });
    
    quantity.addEventListener('input', () => calculateLineTotal(row));
    price.addEventListener('input', () => calculateLineTotal(row));
    taxable.addEventListener('change', () => updateTotals());
    
    // Show empty state message
    updateEmptyState();
}

/**
 * Remove an invoice item
 */
function removeItem(itemId) {
    const row = document.querySelector(`tr[data-item-id="${itemId}"]`);
    if (row) {
        row.remove();
        updateTotals();
        updateEmptyState();
    }
}

/**
 * Calculate line total for a row
 */
function calculateLineTotal(row) {
    const quantity = parseFloat(row.querySelector('.item-quantity').value) || 0;
    const price = parseFloat(row.querySelector('.item-price').value) || 0;
    const total = quantity * price;
    
    row.querySelector('.item-total').textContent = formatCurrency(total);
    updateTotals();
}

/**
 * Update total calculations
 */
function updateTotals() {
    const rows = document.querySelectorAll('#itemsTableBody tr');
    let subtotal = 0;
    let taxableAmount = 0;
    
    rows.forEach(row => {
        const quantity = parseFloat(row.querySelector('.item-quantity').value) || 0;
        const price = parseFloat(row.querySelector('.item-price').value) || 0;
        const lineTotal = quantity * price;
        const isTaxable = row.querySelector('.item-taxable').checked;
        
        subtotal += lineTotal;
        if (isTaxable) {
            taxableAmount += lineTotal;
        }
    });
    
    const taxRate = parseFloat(document.getElementById('tax_rate').value) || 0;
    const taxAmount = taxableAmount * (taxRate / 100);
    const total = subtotal + taxAmount;
    
    document.getElementById('subtotalAmount').textContent = formatCurrency(subtotal);
    document.getElementById('taxAmount').textContent = formatCurrency(taxAmount);
    document.getElementById('totalAmount').textContent = formatCurrency(total);
}

/**
 * Update empty state message
 */
function updateEmptyState() {
    const tbody = document.getElementById('itemsTableBody');
    const emptyState = document.getElementById('emptyState');
    
    if (tbody.children.length === 0) {
        emptyState.classList.remove('hidden');
    } else {
        emptyState.classList.add('hidden');
    }
}

/**
 * Validate invoice data
 */
function validateInvoice() {
    const customerId = document.getElementById('customer_id').value;
    const invoiceDate = document.getElementById('invoice_date').value;
    const rows = document.querySelectorAll('#itemsTableBody tr');
    
    if (!customerId) {
        showAlert('Please select a customer', 'error');
        return false;
    }
    
    if (!invoiceDate) {
        showAlert('Please enter an invoice date', 'error');
        return false;
    }
    
    if (rows.length === 0) {
        showAlert('Please add at least one item', 'error');
        return false;
    }
    
    // Validate each row
    for (let i = 0; i < rows.length; i++) {
        const row = rows[i];
        const product = row.querySelector('.item-product').value;
        const description = row.querySelector('.item-description').value;
        const quantity = parseFloat(row.querySelector('.item-quantity').value);
        const price = parseFloat(row.querySelector('.item-price').value);
        
        if (!product) {
            showAlert(`Please select a product for item ${i + 1}`, 'error');
            return false;
        }
        
        if (!description.trim()) {
            showAlert(`Please enter a description for item ${i + 1}`, 'error');
            return false;
        }
        
        if (!quantity || quantity <= 0) {
            showAlert(`Please enter a valid quantity for item ${i + 1}`, 'error');
            return false;
        }
        
        if (price < 0) {
            showAlert(`Please enter a valid price for item ${i + 1}`, 'error');
            return false;
        }
    }
    
    return true;
}

/**
 * Collect invoice data
 */
function collectInvoiceData() {
    const rows = document.querySelectorAll('#itemsTableBody tr');
    const items = [];
    
    rows.forEach(row => {
        items.push({
            product_id: parseInt(row.querySelector('.item-product').value),
            description: row.querySelector('.item-description').value.trim(),
            quantity: parseFloat(row.querySelector('.item-quantity').value),
            unit_price: parseFloat(row.querySelector('.item-price').value),
            taxable: row.querySelector('.item-taxable').checked
        });
    });
    
    return {
        customer_id: parseInt(document.getElementById('customer_id').value),
        invoice_date: document.getElementById('invoice_date').value,
        due_date: document.getElementById('due_date').value || null,
        tax_rate: parseFloat(document.getElementById('tax_rate').value),
        notes: document.getElementById('notes').value.trim(),
        status: 'draft',
        items: items
    };
}

/**
 * Save invoice
 */
async function saveInvoice() {
    // Hide previous alerts
    hideAlert();
    
    // Validate
    if (!validateInvoice()) {
        return;
    }
    
    // Collect data
    const invoiceData = collectInvoiceData();
    
    // Disable button and show loading
    const saveBtn = document.getElementById('saveInvoiceBtn');
    const originalText = saveBtn.innerHTML;
    saveBtn.disabled = true;
    saveBtn.innerHTML = '<span class="spinner"></span> Saving...';
    
    try {
        const response = await fetch(`${API_BASE}/invoices.php`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(invoiceData)
        });
        
        const data = await response.json();
        
        if (data.success) {
            showAlert(`Invoice ${data.data.invoice_number} created successfully! Total: ${formatCurrency(data.data.total_amount)}`, 'success');
            
            // Reset form after 2 seconds
            setTimeout(() => {
                resetForm();
            }, 2000);
        } else {
            showAlert(data.error || 'Failed to create invoice', 'error');
        }
        
    } catch (error) {
        console.error('Error saving invoice:', error);
        showAlert('An error occurred while saving the invoice', 'error');
    } finally {
        saveBtn.disabled = false;
        saveBtn.innerHTML = originalText;
    }
}

/**
 * Reset form
 */
function resetForm() {
    document.getElementById('invoiceForm').reset();
    document.getElementById('itemsTableBody').innerHTML = '';
    itemCounter = 0;
    updateTotals();
    updateEmptyState();
    hideAlert();
    
    // Set default date to today
    document.getElementById('invoice_date').valueAsDate = new Date();
}

/**
 * Show alert message
 */
function showAlert(message, type = 'success') {
    const alert = document.getElementById('alertMessage');
    alert.className = `alert alert-${type} show`;
    alert.textContent = message;
    alert.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
}

/**
 * Hide alert message
 */
function hideAlert() {
    const alert = document.getElementById('alertMessage');
    alert.className = 'alert';
}

/**
 * Format currency
 */
function formatCurrency(amount) {
    return currencySymbol + amount.toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ',');
}

/**
 * Escape HTML to prevent XSS
 */
function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

// Set default date to today
document.addEventListener('DOMContentLoaded', function() {
    const today = new Date().toISOString().split('T')[0];
    document.getElementById('invoice_date').value = today;
});
