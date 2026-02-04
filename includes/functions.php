<?php
/**
 * Helper Functions
 * 
 * Common utility functions used throughout the application.
 */

/**
 * Sanitize input data
 * 
 * @param mixed $data Input data to sanitize
 * @return mixed Sanitized data
 */
function sanitize($data) {
    if (is_array($data)) {
        foreach ($data as $key => $value) {
            $data[$key] = sanitize($value);
        }
    } else {
        $data = trim($data);
        $data = stripslashes($data);
        $data = htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
    }
    return $data;
}

/**
 * Validate email address
 * 
 * @param string $email Email to validate
 * @return bool True if valid, false otherwise
 */
function validateEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

/**
 * Validate date format (YYYY-MM-DD)
 * 
 * @param string $date Date string to validate
 * @return bool True if valid, false otherwise
 */
function validateDate($date) {
    $d = DateTime::createFromFormat('Y-m-d', $date);
    return $d && $d->format('Y-m-d') === $date;
}

/**
 * Validate numeric value
 * 
 * @param mixed $value Value to validate
 * @param float $min Minimum value (optional)
 * @param float $max Maximum value (optional)
 * @return bool True if valid, false otherwise
 */
function validateNumeric($value, $min = null, $max = null) {
    if (!is_numeric($value)) {
        return false;
    }
    
    $value = floatval($value);
    
    if ($min !== null && $value < $min) {
        return false;
    }
    
    if ($max !== null && $value > $max) {
        return false;
    }
    
    return true;
}

/**
 * Generate unique invoice number
 * 
 * @param PDO $db Database connection
 * @return string Unique invoice number
 */
function generateInvoiceNumber($db) {
    $year = date('Y');
    $prefix = INVOICE_PREFIX . $year . '-';
    
    // Get the last invoice number for this year
    $stmt = $db->prepare("
        SELECT invoice_number 
        FROM invoices 
        WHERE invoice_number LIKE :prefix 
        ORDER BY id DESC 
        LIMIT 1
    ");
    $stmt->execute(['prefix' => $prefix . '%']);
    $lastInvoice = $stmt->fetch();
    
    if ($lastInvoice) {
        // Extract the number and increment
        $lastNumber = intval(substr($lastInvoice['invoice_number'], strlen($prefix)));
        $newNumber = $lastNumber + 1;
    } else {
        $newNumber = 1;
    }
    
    // Pad with zeros (e.g., 001, 002, etc.)
    return $prefix . str_pad($newNumber, 3, '0', STR_PAD_LEFT);
}

/**
 * Send JSON response
 * 
 * @param mixed $data Data to send
 * @param int $statusCode HTTP status code
 * @return void
 */
function sendJSON($data, $statusCode = 200) {
    http_response_code($statusCode);
    header('Content-Type: application/json');
    echo json_encode($data, JSON_PRETTY_PRINT);
    exit;
}

/**
 * Send error response
 * 
 * @param string $message Error message
 * @param int $statusCode HTTP status code
 * @return void
 */
function sendError($message, $statusCode = 400) {
    sendJSON([
        'success' => false,
        'error' => $message
    ], $statusCode);
}

/**
 * Send success response
 * 
 * @param mixed $data Data to send
 * @param string $message Success message (optional)
 * @return void
 */
function sendSuccess($data = null, $message = null) {
    $response = ['success' => true];
    
    if ($message !== null) {
        $response['message'] = $message;
    }
    
    if ($data !== null) {
        $response['data'] = $data;
    }
    
    sendJSON($response, 200);
}

/**
 * Log error message
 * 
 * @param string $message Error message
 * @param Exception $exception Exception object (optional)
 * @return void
 */
function logError($message, $exception = null) {
    $logMessage = date('Y-m-d H:i:s') . " - " . $message;
    
    if ($exception) {
        $logMessage .= " - " . $exception->getMessage();
        $logMessage .= " - " . $exception->getTraceAsString();
    }
    
    error_log($logMessage);
}

/**
 * Format currency
 * 
 * @param float $amount Amount to format
 * @param string $currency Currency symbol
 * @return string Formatted currency string
 */
function formatCurrency($amount, $currency = '$') {
    return $currency . number_format($amount, 2);
}

/**
 * Calculate line total
 * 
 * @param float $quantity Quantity
 * @param float $unitPrice Unit price
 * @return float Line total
 */
function calculateLineTotal($quantity, $unitPrice) {
    return round($quantity * $unitPrice, 2);
}

/**
 * Calculate tax amount
 * 
 * @param float $subtotal Subtotal amount
 * @param float $taxRate Tax rate percentage
 * @return float Tax amount
 */
function calculateTax($subtotal, $taxRate) {
    return round($subtotal * ($taxRate / 100), 2);
}
