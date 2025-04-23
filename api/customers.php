<?php
// api/customers.php

require_once __DIR__ . '/../db.php';
require_once __DIR__ . '/../authcheck.php';

header('Content-Type: application/json');
$method = $_SERVER['REQUEST_METHOD'];

// Ensure user is logged in and has appropriate role
requireLogin();
allowRoles(['admin', 'staff']);

// GET endpoint - Fetch customers with optional search
if ($method === 'GET') {
    $search = $_GET['search'] ?? '';
    
    $sql = "
        SELECT id, name, phone, email, address
        FROM customers
    ";
    
    $params = [];
    
    if ($search) {
        $sql .= " WHERE name LIKE ? OR phone LIKE ? OR email LIKE ?";
        $params = ["%$search%", "%$search%", "%$search%"];
    }
    
    $sql .= " ORDER BY name ASC";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $customers = $stmt->fetchAll();
    
    echo json_encode([
        'success' => true,
        'customers' => $customers
    ]);
    exit;
}

// POST endpoint - Create a new customer
if ($method === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    
    // Validate required data
    if (!isset($data['name']) || empty(trim($data['name']))) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Customer name is required']);
        exit;
    }
    
    try {
        // Check if customer with the same phone or email already exists
        $checkSql = "SELECT id FROM customers WHERE ";
        $checkParams = [];
        $checkConditions = [];
        
        if (!empty($data['phone'])) {
            $checkConditions[] = "phone = ?";
            $checkParams[] = $data['phone'];
        }
        
        if (!empty($data['email'])) {
            $checkConditions[] = "email = ?";
            $checkParams[] = $data['email'];
        }
        
        if (!empty($checkConditions)) {
            $checkSql .= implode(" OR ", $checkConditions);
            
            $stmt = $pdo->prepare($checkSql);
            $stmt->execute($checkParams);
            
            if ($stmt->rowCount() > 0) {
                http_response_code(400);
                echo json_encode([
                    'success' => false,
                    'message' => 'A customer with the same phone or email already exists'
                ]);
                exit;
            }
        }
        
        // Insert new customer
        $stmt = $pdo->prepare("
            INSERT INTO customers (name, phone, email, address, created_at)
            VALUES (?, ?, ?, ?, NOW())
        ");
        
        $stmt->execute([
            trim($data['name']),
            $data['phone'] ?? null,
            $data['email'] ?? null,
            $data['address'] ?? null
        ]);
        
        $customerId = $pdo->lastInsertId();
        
        echo json_encode([
            'success' => true,
            'customer_id' => $customerId,
            'message' => 'Customer created successfully'
        ]);
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'message' => $e->getMessage()
        ]);
    }
    exit;
}

// Invalid method
http_response_code(405);
echo json_encode(['success' => false, 'message' => 'Method not allowed']);
exit;
