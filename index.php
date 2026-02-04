<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invoice System - Create Invoice</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <div class="container">
        <!-- Header -->
        <div class="header">
            <h1>Invoice System</h1>
            <p>Create and manage invoices efficiently</p>
        </div>

        <!-- Alert Message -->
        <div id="alertMessage" class="alert"></div>

        <!-- Invoice Form -->
        <form id="invoiceForm">
            <!-- Customer & Date Information -->
            <div class="form-section">
                <h2>Invoice Details</h2>
                <div class="form-row">
                    <div class="form-group">
                        <label for="customer_id" class="required">Customer</label>
                        <select id="customer_id" name="customer_id" class="form-control" required>
                            <option value="">-- Select Customer --</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="invoice_date" class="required">Invoice Date</label>
                        <input type="date" 
                               id="invoice_date" 
                               name="invoice_date" 
                               class="form-control" 
                               required>
                    </div>
                    
                    <div class="form-group">
                        <label for="due_date">Due Date</label>
                        <input type="date" 
                               id="due_date" 
                               name="due_date" 
                               class="form-control">
                    </div>
                </div>
            </div>

            <!-- Invoice Items -->
            <div class="form-section items-section">
                <h2>Invoice Items</h2>
                
                <div class="items-table-container">
                    <table class="items-table">
                        <thead>
                            <tr>
                                <th style="width: 22%;">Product</th>
                                <th style="width: 22%;">Description</th>
                                <th style="width: 10%;">Quantity</th>
                                <th style="width: 11%;">Unit Price</th>
                                <th style="width: 11%;" class="text-right">Line Total</th>
                                <th style="width: 10%;" class="text-center">Taxable</th>
                                <th style="width: 14%;" class="text-center">Action</th>
                            </tr>
                        </thead>
                        <tbody id="itemsTableBody">
                            <!-- Items will be added here dynamically -->
                        </tbody>
                    </table>
                    
                    <!-- Empty State -->
                    <div id="emptyState" class="empty-state">
                        <p>No items added yet. Click "Add Item" to get started.</p>
                    </div>
                </div>
                
                <button type="button" id="addItemBtn" class="btn btn-primary">
                    Add Item
                </button>
            </div>

            <!-- Totals -->
            <div class="totals-section">
                <div class="totals-box">
                    <div class="total-row subtotal">
                        <span class="total-label">Subtotal:</span>
                        <span class="total-value" id="subtotalAmount">0.00</span>
                    </div>
                    
                    <div class="total-row">
                        <span class="total-label">
                            Tax <span id="tax_rate_display">(10%)</span>
                            <input type="hidden" id="tax_rate" name="tax_rate" value="10">
                        </span>
                        <span class="total-value" id="taxAmount">0.00</span>
                    </div>
                    
                    <div class="total-row total">
                        <span class="total-label">Total:</span>
                        <span class="total-value" id="totalAmount">0.00</span>
                    </div>
                </div>
            </div>

            <!-- Notes -->
            <div class="form-section">
                <div class="form-group">
                    <label for="notes">Notes / Terms</label>
                    <textarea id="notes" 
                              name="notes" 
                              class="form-control" 
                              rows="3"
                              placeholder="Additional notes or payment terms..."></textarea>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="action-buttons">
                <button type="button" class="btn btn-secondary" onclick="resetForm()">
                    Reset Form
                </button>
                <button type="button" id="saveInvoiceBtn" class="btn btn-success">
                    Save Invoice
                </button>
            </div>
        </form>
    </div>

    <script src="assets/js/app.js"></script>
</body>
</html>
