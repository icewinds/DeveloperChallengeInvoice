<?php
/**
 * Configuration API Endpoint
 * 
 * GET /api/config.php - Get all configuration settings
 * POST /api/config.php - Update configuration settings
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT');
header('Access-Control-Allow-Headers: Content-Type');

require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';

try {
    $db = getDB();
    
    // Handle GET request - Fetch all configuration
    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        $stmt = $db->query("SELECT config_key, config_value FROM configuration");
        $configRows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Convert to key-value pairs
        $config = [];
        foreach ($configRows as $row) {
            $config[$row['config_key']] = $row['config_value'];
        }
        
        sendSuccess($config);
    }
    
    // Handle POST request - Update configuration
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $input = json_decode(file_get_contents('php://input'), true);
        
        if (!$input || !is_array($input)) {
            sendError('Invalid input data');
        }
        
        $db->beginTransaction();
        
        try {
            $stmt = $db->prepare("
                INSERT INTO configuration (config_key, config_value)
                VALUES (:key, :value)
                ON DUPLICATE KEY UPDATE config_value = :value
            ");
            
            $allowedKeys = ['company_name', 'default_currency', 'company_date', 'tax_percent'];
            
            foreach ($input as $key => $value) {
                if (in_array($key, $allowedKeys)) {
                    $stmt->execute([
                        'key' => $key,
                        'value' => $value
                    ]);
                }
            }
            
            $db->commit();
            sendSuccess(['message' => 'Configuration updated successfully']);
            
        } catch (Exception $e) {
            $db->rollBack();
            throw $e;
        }
    }
    
} catch (Exception $e) {
    error_log('Configuration API Error: ' . $e->getMessage());
    sendError('Configuration operation failed: ' . $e->getMessage(), 500);
}
