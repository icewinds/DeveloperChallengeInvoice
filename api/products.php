<?php
/**
 * Products API Endpoint
 * 
 * GET /api/products.php - Get all active products
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
    
    // Get all active products
    $stmt = $db->prepare("
        SELECT 
            id,
            product_code,
            product_name,
            description,
            unit_price,
            unit
        FROM products
        WHERE active = 1
        ORDER BY product_name ASC
    ");
    
    $stmt->execute();
    $products = $stmt->fetchAll();
    
    sendSuccess($products);
    
} catch (PDOException $e) {
    logError("Database error in products API", $e);
    sendError('Database error occurred', 500);
} catch (Exception $e) {
    logError("Error in products API", $e);
    sendError($e->getMessage(), 500);
}
