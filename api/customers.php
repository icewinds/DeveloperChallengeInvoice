<?php
/**
 * Customers API Endpoint
 * 
 * GET /api/customers.php - Get all active customers
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');

require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';

try {
    $db = getDB();
    
    // Only support GET method
    if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
        sendError('Method not allowed', 405);
    }
    
    // Get all customers
    $stmt = $db->prepare("
        SELECT 
            id,
            customer_name,
            email,
            phone,
            address,
            city,
            postal_code,
            country
        FROM customers
        ORDER BY customer_name ASC
    ");
    
    $stmt->execute();
    $customers = $stmt->fetchAll();
    
    sendSuccess($customers);
    
} catch (PDOException $e) {
    logError("Database error in customers API", $e);
    sendError('Database error occurred', 500);
} catch (Exception $e) {
    logError("Error in customers API", $e);
    sendError($e->getMessage(), 500);
}
