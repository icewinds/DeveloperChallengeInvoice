<?php
/**
 * Invoices API Endpoint
 * 
 * POST /api/invoices.php - Create a new invoice
 * GET /api/invoices.php?id=X - Get invoice by ID
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST');
header('Access-Control-Allow-Headers: Content-Type');

require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';

try {
    $db = getDB();
    
    // Handle GET request - Fetch invoice
    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
            sendError('Invoice ID is required');
        }
        
        $invoiceId = intval($_GET['id']);
        
        // Get invoice with customer details
        $stmt = $db->prepare("
            SELECT 
                i.*,
                c.customer_name,
                c.email,
                c.phone,
                c.address,
                c.city,
                c.postal_code,
                c.country
            FROM invoices i
            INNER JOIN customers c ON i.customer_id = c.id
            WHERE i.id = :id
        ");
        
        $stmt->execute(['id' => $invoiceId]);
        $invoice = $stmt->fetch();
        
        if (!$invoice) {
            sendError('Invoice not found', 404);
        }
        
        // Get invoice items
        $stmt = $db->prepare("
            SELECT 
                ii.*,
                p.product_code,
                p.product_name,
                p.unit
            FROM invoice_items ii
            INNER JOIN products p ON ii.product_id = p.id
            WHERE ii.invoice_id = :invoice_id
            ORDER BY ii.sort_order ASC
        ");
        
        $stmt->execute(['invoice_id' => $invoiceId]);
        $invoice['items'] = $stmt->fetchAll();
        
        sendSuccess($invoice);
    }
    
    // Handle POST request - Create invoice
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        // Get JSON input
        $input = json_decode(file_get_contents('php://input'), true);
        
        if (json_last_error() !== JSON_ERROR_NONE) {
            sendError('Invalid JSON data');
        }
        
        // Validate required fields
        $requiredFields = ['customer_id', 'invoice_date', 'items'];
        foreach ($requiredFields as $field) {
            if (!isset($input[$field]) || empty($input[$field])) {
                sendError("Field '{$field}' is required");
            }
        }
        
        // Validate customer_id
        if (!validateNumeric($input['customer_id'], 1)) {
            sendError('Invalid customer ID');
        }
        
        // Validate invoice_date
        if (!validateDate($input['invoice_date'])) {
            sendError('Invalid invoice date format. Use YYYY-MM-DD');
        }
        
        // Validate due_date if provided
        if (isset($input['due_date']) && !empty($input['due_date']) && !validateDate($input['due_date'])) {
            sendError('Invalid due date format. Use YYYY-MM-DD');
        }
        
        // Validate items array
        if (!is_array($input['items']) || count($input['items']) === 0) {
            sendError('At least one item is required');
        }
        
        // Validate tax_rate
        $taxRate = isset($input['tax_rate']) ? floatval($input['tax_rate']) : TAX_RATE;
        if (!validateNumeric($taxRate, 0, 100)) {
            sendError('Invalid tax rate. Must be between 0 and 100');
        }
        
        // Validate each item
        foreach ($input['items'] as $index => $item) {
            if (!isset($item['product_id']) || !validateNumeric($item['product_id'], 1)) {
                sendError("Invalid product ID for item " . ($index + 1));
            }
            
            if (!isset($item['quantity']) || !validateNumeric($item['quantity'], 0.01)) {
                sendError("Invalid quantity for item " . ($index + 1));
            }
            
            if (!isset($item['unit_price']) || !validateNumeric($item['unit_price'], 0)) {
                sendError("Invalid unit price for item " . ($index + 1));
            }
            
            if (!isset($item['description']) || trim($item['description']) === '') {
                sendError("Description is required for item " . ($index + 1));
            }
        }
        
        // Start transaction
        $db->beginTransaction();
        
        try {
            // Generate invoice number
            $invoiceNumber = generateInvoiceNumber($db);
            
            // Calculate totals with taxable items
            $subtotal = 0;
            $taxableAmount = 0;
            foreach ($input['items'] as $item) {
                $lineTotal = calculateLineTotal($item['quantity'], $item['unit_price']);
                $subtotal += $lineTotal;
                
                // Check if item is taxable (default true if not specified)
                $isTaxable = isset($item['taxable']) ? (bool)$item['taxable'] : true;
                if ($isTaxable) {
                    $taxableAmount += $lineTotal;
                }
            }
            
            // Calculate tax only on taxable items
            $taxAmount = calculateTax($taxableAmount, $taxRate);
            $totalAmount = $subtotal + $taxAmount;
            
            // Insert invoice
            $stmt = $db->prepare("
                INSERT INTO invoices (
                    invoice_number,
                    customer_id,
                    invoice_date,
                    due_date,
                    subtotal,
                    tax_rate,
                    tax_amount,
                    total_amount,
                    notes,
                    status
                ) VALUES (
                    :invoice_number,
                    :customer_id,
                    :invoice_date,
                    :due_date,
                    :subtotal,
                    :tax_rate,
                    :tax_amount,
                    :total_amount,
                    :notes,
                    :status
                )
            ");
            
            $stmt->execute([
                'invoice_number' => $invoiceNumber,
                'customer_id' => $input['customer_id'],
                'invoice_date' => $input['invoice_date'],
                'due_date' => $input['due_date'] ?? null,
                'subtotal' => $subtotal,
                'tax_rate' => $taxRate,
                'tax_amount' => $taxAmount,
                'total_amount' => $totalAmount,
                'notes' => $input['notes'] ?? '',
                'status' => $input['status'] ?? 'draft'
            ]);
            
            $invoiceId = $db->lastInsertId();
            
            // Insert invoice items
            $stmt = $db->prepare("
                INSERT INTO invoice_items (
                    invoice_id,
                    product_id,
                    description,
                    quantity,
                    unit_price,
                    line_total,
                    taxable,
                    sort_order
                ) VALUES (
                    :invoice_id,
                    :product_id,
                    :description,
                    :quantity,
                    :unit_price,
                    :line_total,
                    :taxable,
                    :sort_order
                )
            ");
            
            foreach ($input['items'] as $index => $item) {
                $lineTotal = calculateLineTotal($item['quantity'], $item['unit_price']);
                $isTaxable = isset($item['taxable']) ? (bool)$item['taxable'] : true;
                
                $stmt->execute([
                    'invoice_id' => $invoiceId,
                    'product_id' => $item['product_id'],
                    'description' => trim($item['description']),
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['unit_price'],
                    'line_total' => $lineTotal,
                    'taxable' => $isTaxable ? 1 : 0,
                    'sort_order' => $index + 1
                ]);
            }
            
            // Commit transaction
            $db->commit();
            
            // Return success with invoice details
            sendSuccess([
                'invoice_id' => $invoiceId,
                'invoice_number' => $invoiceNumber,
                'subtotal' => $subtotal,
                'tax_rate' => $taxRate,
                'tax_amount' => $taxAmount,
                'total_amount' => $totalAmount
            ], 'Invoice created successfully');
            
        } catch (Exception $e) {
            // Rollback transaction on error
            $db->rollBack();
            throw $e;
        }
    }
    
    // Method not allowed
    sendError('Method not allowed', 405);
    
} catch (PDOException $e) {
    logError("Database error in invoices API", $e);
    sendError('Database error occurred', 500);
} catch (Exception $e) {
    logError("Error in invoices API", $e);
    sendError($e->getMessage(), 500);
}
