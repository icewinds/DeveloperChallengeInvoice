<?php
/**
 * API Testing Suite
 * Run this file in your browser: http://localhost/DeveloperChallengeInvoice/tests/api_tests.php
 */

// Configuration
$API_BASE = 'http://localhost/DeveloperChallengeInvoice/api';
$tests_passed = 0;
$tests_failed = 0;
$results = [];

/**
 * Make API request
 */
function makeRequest($url, $method = 'GET', $data = null) {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HEADER, false);
    
    if ($method === 'POST') {
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
    }
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    return [
        'code' => $httpCode,
        'body' => json_decode($response, true)
    ];
}

/**
 * Test function
 */
function test($name, $callback) {
    global $tests_passed, $tests_failed, $results;
    
    try {
        $callback();
        $tests_passed++;
        $results[] = ['name' => $name, 'status' => 'PASS', 'message' => ''];
    } catch (Exception $e) {
        $tests_failed++;
        $results[] = ['name' => $name, 'status' => 'FAIL', 'message' => $e->getMessage()];
    }
}

/**
 * Assertion function
 */
function assert_true($condition, $message = 'Assertion failed') {
    if (!$condition) {
        throw new Exception($message);
    }
}

function assert_equals($expected, $actual, $message = 'Values not equal') {
    if ($expected !== $actual) {
        throw new Exception("$message - Expected: " . json_encode($expected) . ", Got: " . json_encode($actual));
    }
}

// ==================== TESTS START ====================

// Test 1: Get all customers
test('GET /api/customers.php - Should return list of customers', function() use ($API_BASE) {
    $response = makeRequest("$API_BASE/customers.php");
    assert_equals(200, $response['code'], 'HTTP status should be 200');
    assert_true($response['body']['success'], 'Response should be successful');
    assert_true(isset($response['body']['data']), 'Response should have data');
    assert_true(is_array($response['body']['data']), 'Data should be an array');
});

// Test 2: Get all products
test('GET /api/products.php - Should return list of products', function() use ($API_BASE) {
    $response = makeRequest("$API_BASE/products.php");
    assert_equals(200, $response['code'], 'HTTP status should be 200');
    assert_true($response['body']['success'], 'Response should be successful');
    assert_true(isset($response['body']['data']), 'Response should have data');
    assert_true(is_array($response['body']['data']), 'Data should be an array');
});

// Test 3: Get configuration
test('GET /api/config.php - Should return configuration settings', function() use ($API_BASE) {
    $response = makeRequest("$API_BASE/config.php");
    assert_equals(200, $response['code'], 'HTTP status should be 200');
    assert_true($response['body']['success'], 'Response should be successful');
    assert_true(isset($response['body']['data']), 'Response should have data');
    assert_true(isset($response['body']['data']['company_name']), 'Should have company_name');
    assert_true(isset($response['body']['data']['tax_percent']), 'Should have tax_percent');
});

// Test 4: Create invoice with taxable items
test('POST /api/invoices.php - Should create invoice with taxable items', function() use ($API_BASE) {
    $invoiceData = [
        'customer_id' => 1,
        'invoice_date' => '2026-02-04',
        'due_date' => '2026-03-04',
        'tax_rate' => 10,
        'notes' => 'Test invoice',
        'items' => [
            [
                'product_id' => 1,
                'description' => 'Taxable Item',
                'quantity' => 2,
                'unit_price' => 100.00,
                'taxable' => true
            ],
            [
                'product_id' => 2,
                'description' => 'Non-taxable Item',
                'quantity' => 1,
                'unit_price' => 50.00,
                'taxable' => false
            ]
        ]
    ];
    
    $response = makeRequest("$API_BASE/invoices.php", 'POST', $invoiceData);
    assert_equals(200, $response['code'], 'HTTP status should be 200');
    assert_true($response['body']['success'], 'Response should be successful');
    assert_true(isset($response['body']['data']['invoice_id']), 'Should return invoice_id');
    
    // Verify tax calculation (only on taxable items)
    // Subtotal: 200 + 50 = 250
    // Taxable amount: 200 (only first item)
    // Tax: 200 * 0.10 = 20
    // Total: 250 + 20 = 270
    assert_equals(250.00, (float)$response['body']['data']['subtotal'], 'Subtotal should be 250.00');
    assert_equals(20.00, (float)$response['body']['data']['tax_amount'], 'Tax should be 20.00 (only on taxable items)');
    assert_equals(270.00, (float)$response['body']['data']['total_amount'], 'Total should be 270.00');
});

// Test 5: Create invoice without items (should fail)
test('POST /api/invoices.php - Should fail when no items provided', function() use ($API_BASE) {
    $invoiceData = [
        'customer_id' => 1,
        'invoice_date' => '2026-02-04',
        'items' => []
    ];
    
    $response = makeRequest("$API_BASE/invoices.php", 'POST', $invoiceData);
    assert_true(!$response['body']['success'], 'Response should fail');
    assert_true(isset($response['body']['error']), 'Should return error message');
});

// Test 6: Create invoice without customer (should fail)
test('POST /api/invoices.php - Should fail when customer_id missing', function() use ($API_BASE) {
    $invoiceData = [
        'invoice_date' => '2026-02-04',
        'items' => [
            [
                'product_id' => 1,
                'description' => 'Test',
                'quantity' => 1,
                'unit_price' => 100.00,
                'taxable' => true
            ]
        ]
    ];
    
    $response = makeRequest("$API_BASE/invoices.php", 'POST', $invoiceData);
    assert_true(!$response['body']['success'], 'Response should fail');
});

// Test 7: Get specific invoice
test('GET /api/invoices.php?id=1 - Should return invoice details', function() use ($API_BASE) {
    $response = makeRequest("$API_BASE/invoices.php?id=1");
    assert_equals(200, $response['code'], 'HTTP status should be 200');
    assert_true($response['body']['success'], 'Response should be successful');
    assert_true(isset($response['body']['data']['invoice_number']), 'Should have invoice_number');
    assert_true(isset($response['body']['data']['items']), 'Should have items array');
    assert_true(is_array($response['body']['data']['items']), 'Items should be an array');
});

// Test 8: Get non-existent invoice (should fail)
test('GET /api/invoices.php?id=99999 - Should fail for non-existent invoice', function() use ($API_BASE) {
    $response = makeRequest("$API_BASE/invoices.php?id=99999");
    assert_true(!$response['body']['success'], 'Response should fail');
});

// Test 9: Update configuration
test('POST /api/config.php - Should update configuration', function() use ($API_BASE) {
    $configData = [
        'company_name' => 'Test Company',
        'default_currency' => 'EUR',
        'tax_percent' => '15.00'
    ];
    
    $response = makeRequest("$API_BASE/config.php", 'POST', $configData);
    assert_equals(200, $response['code'], 'HTTP status should be 200');
    assert_true($response['body']['success'], 'Response should be successful');
    
    // Verify the update
    $getResponse = makeRequest("$API_BASE/config.php");
    assert_equals('Test Company', $getResponse['body']['data']['company_name'], 'Company name should be updated');
    assert_equals('EUR', $getResponse['body']['data']['default_currency'], 'Currency should be updated');
});

// Test 10: Create invoice with all non-taxable items
test('POST /api/invoices.php - Should calculate zero tax for all non-taxable items', function() use ($API_BASE) {
    $invoiceData = [
        'customer_id' => 1,
        'invoice_date' => '2026-02-04',
        'tax_rate' => 10,
        'items' => [
            [
                'product_id' => 1,
                'description' => 'Non-taxable Item 1',
                'quantity' => 1,
                'unit_price' => 100.00,
                'taxable' => false
            ],
            [
                'product_id' => 2,
                'description' => 'Non-taxable Item 2',
                'quantity' => 1,
                'unit_price' => 50.00,
                'taxable' => false
            ]
        ]
    ];
    
    $response = makeRequest("$API_BASE/invoices.php", 'POST', $invoiceData);
    assert_equals(200, $response['code'], 'HTTP status should be 200');
    assert_true($response['body']['success'], 'Response should be successful');
    
    // All items non-taxable, so tax should be 0
    assert_equals(150.00, (float)$response['body']['data']['subtotal'], 'Subtotal should be 150.00');
    assert_equals(0.00, (float)$response['body']['data']['tax_amount'], 'Tax should be 0.00 (no taxable items)');
    assert_equals(150.00, (float)$response['body']['data']['total_amount'], 'Total should equal subtotal');
});

// ==================== DISPLAY RESULTS ====================
?>
<!DOCTYPE html>
<html>
<head>
    <title>Invoice System - API Test Results</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 1000px;
            margin: 50px auto;
            padding: 20px;
            background: #f5f5f5;
        }
        .header {
            background: #2c3e50;
            color: white;
            padding: 20px;
            border-radius: 5px;
            margin-bottom: 20px;
        }
        .summary {
            display: flex;
            gap: 20px;
            margin-bottom: 20px;
        }
        .summary-box {
            flex: 1;
            padding: 20px;
            border-radius: 5px;
            text-align: center;
        }
        .summary-box.passed {
            background: #27ae60;
            color: white;
        }
        .summary-box.failed {
            background: #e74c3c;
            color: white;
        }
        .summary-box.total {
            background: #3498db;
            color: white;
        }
        .summary-box h2 {
            margin: 0;
            font-size: 2em;
        }
        .summary-box p {
            margin: 5px 0 0 0;
        }
        .test-result {
            background: white;
            padding: 15px;
            margin-bottom: 10px;
            border-radius: 5px;
            border-left: 5px solid #ddd;
        }
        .test-result.pass {
            border-left-color: #27ae60;
        }
        .test-result.fail {
            border-left-color: #e74c3c;
        }
        .test-name {
            font-weight: bold;
            margin-bottom: 5px;
        }
        .test-status {
            display: inline-block;
            padding: 3px 10px;
            border-radius: 3px;
            font-size: 0.9em;
            font-weight: bold;
        }
        .test-status.pass {
            background: #27ae60;
            color: white;
        }
        .test-status.fail {
            background: #e74c3c;
            color: white;
        }
        .test-message {
            color: #e74c3c;
            margin-top: 5px;
            font-size: 0.9em;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Invoice System - API Test Results</h1>
        <p>Test run completed on <?php echo date('Y-m-d H:i:s'); ?></p>
    </div>
    
    <div class="summary">
        <div class="summary-box total">
            <h2><?php echo $tests_passed + $tests_failed; ?></h2>
            <p>Total Tests</p>
        </div>
        <div class="summary-box passed">
            <h2><?php echo $tests_passed; ?></h2>
            <p>Passed</p>
        </div>
        <div class="summary-box failed">
            <h2><?php echo $tests_failed; ?></h2>
            <p>Failed</p>
        </div>
    </div>
    
    <div class="test-results">
        <?php foreach ($results as $result): ?>
            <div class="test-result <?php echo strtolower($result['status']); ?>">
                <div class="test-name">
                    <span class="test-status <?php echo strtolower($result['status']); ?>">
                        <?php echo $result['status']; ?>
                    </span>
                    <?php echo htmlspecialchars($result['name']); ?>
                </div>
                <?php if ($result['message']): ?>
                    <div class="test-message">
                        <?php echo htmlspecialchars($result['message']); ?>
                    </div>
                <?php endif; ?>
            </div>
        <?php endforeach; ?>
    </div>
</body>
</html>
