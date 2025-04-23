<?php
// api/sales.php

require_once __DIR__ . '/../db.php';
require_once __DIR__ . '/../authcheck.php';

header('Content-Type: application/json');
$method = $_SERVER['REQUEST_METHOD'];

// Get database name from connection
$dbname = 'inventory_system'; // Use the same name as in db.php

// Ensure user is logged in and has appropriate role
requireLogin();
allowRoles(['admin', 'staff']);

// Function to add note column to sales table if it doesn't exist
function addNoteColumnIfMissing($pdo) {
    try {
        // Check if the column exists
        $columnExistStmt = $pdo->prepare("
            SELECT COUNT(*) 
            FROM information_schema.COLUMNS 
            WHERE TABLE_SCHEMA = :dbname
            AND TABLE_NAME = 'sales' 
            AND COLUMN_NAME = 'note'
        ");
        $columnExistStmt->execute([':dbname' => $GLOBALS['dbname']]);
        $noteColumnExists = $columnExistStmt->fetchColumn() > 0;
        
        // If column doesn't exist, add it
        if (!$noteColumnExists) {
            $pdo->exec("ALTER TABLE sales ADD COLUMN note TEXT DEFAULT NULL");
            return true; // Column was added
        }
        
        return false; // Column already exists
    } catch (Exception $e) {
        error_log("Error checking/adding note column: " . $e->getMessage());
        return false;
    }
}

// Try to add the note column at the beginning of the script
addNoteColumnIfMissing($pdo);

// Helper function to format order ID (e.g., ORD-015)
function formatOrderId($id) {
    return 'ORD-' . str_pad($id, 3, '0', STR_PAD_LEFT);
}

// Helper function to log audit actions
function logAudit($pdo, $userId, $saleId, $action) {
    if ($action === "Deleted Sale " . formatOrderId($saleId)) {
        // For deletions, don't include the sale_id foreign key
        $stmt = $pdo->prepare("
            INSERT INTO audit_logs (user_id, action, timestamp)
            VALUES (?, ?, NOW())
        ");
        $stmt->execute([$userId, $action]);
    } else {
        // For other actions, include the sale_id
        $stmt = $pdo->prepare("
            INSERT INTO audit_logs (user_id, sale_id, action, timestamp)
            VALUES (?, ?, ?, NOW())
        ");
        $stmt->execute([$userId, $saleId, $action]);
    }
}

// Helper function to log stock changes
function logStock($pdo, $userId, $productId, $changes, $reason) {
    $stmt = $pdo->prepare("
        INSERT INTO stock_logs (product_id, changes, reason, user_id, timestamp)
        VALUES (?, ?, ?, ?, NOW())
    ");
    $stmt->execute([$productId, $changes, $reason, $userId]);
}

// Helper function to update product stock
function updateProductStock($pdo, $productId) {
    $stmt = $pdo->prepare("
        UPDATE products
        SET stock = (SELECT SUM(stock) FROM product_sizes WHERE product_id = ?)
        WHERE id = ?
    ");
    $stmt->execute([$productId, $productId]);
}

// Helper function to get or create customer
function getOrCreateCustomer($pdo, $customerData) {
    // Check if customer exists based on phone or email
    $whereConditions = [];
    $params = [];
    
    if (!empty($customerData['phone'])) {
        $whereConditions[] = "phone = ?";
        $params[] = $customerData['phone'];
    }
    
    if (!empty($customerData['email'])) {
        $whereConditions[] = "email = ?";
        $params[] = $customerData['email'];
    }
    
    $customerId = null;
    
    if (!empty($whereConditions)) {
        $whereClause = implode(" OR ", $whereConditions);
        $stmt = $pdo->prepare("SELECT id FROM customers WHERE $whereClause LIMIT 1");
        $stmt->execute($params);
        $result = $stmt->fetch();
        
        if ($result) {
            $customerId = $result['id'];
        }
    }
    
    // If customer doesn't exist, create new
    if (!$customerId) {
        $stmt = $pdo->prepare("
            INSERT INTO customers (name, phone, email, address)
            VALUES (?, ?, ?, ?)
        ");
        $stmt->execute([
            $customerData['name'] ?? 'Guest Customer',
            $customerData['phone'] ?? null,
            $customerData['email'] ?? null,
            $customerData['address'] ?? null
        ]);
        
        $customerId = $pdo->lastInsertId();
    }
    
    return $customerId;
}

// GET endpoint - Fetch sales with optional filters or a single sale
if ($method === 'GET') {
    // If an ID is provided, fetch a single sale
    if (isset($_GET['id'])) {
        $saleId = $_GET['id'];
        
        try {
            // First check if the note column exists in the sales table
            $columnExistStmt = $pdo->prepare("
                SELECT COUNT(*) 
                FROM information_schema.COLUMNS 
                WHERE TABLE_SCHEMA = :dbname
                AND TABLE_NAME = 'sales' 
                AND COLUMN_NAME = 'note'
            ");
            $columnExistStmt->execute([':dbname' => $dbname]);
            $noteColumnExists = $columnExistStmt->fetchColumn() > 0;
            
            // Build query based on column existence
            $query = "
                SELECT 
                    s.id, 
                    s.total, 
                    s.status, 
                    s.discount_total,
                    " . ($noteColumnExists ? "s.note," : "'' as note,") . "
                    s.created_at, 
                    c.id as customer_id,
                    c.name as customer_name,
                    c.phone as customer_phone,
                    c.email as customer_email,
                    c.address as customer_address
                FROM sales s
                LEFT JOIN customers c ON s.customer_id = c.id
                WHERE s.id = ?
            ";
            
            // Get the sale with proper error handling for missing relation
            $stmt = $pdo->prepare($query);
            $stmt->execute([$saleId]);
            $sale = $stmt->fetch();
            
            if (!$sale) {
                http_response_code(404);
                echo json_encode(['success' => false, 'message' => 'Sale not found']);
                exit;
            }
            
            // Calculate discount percentage
            if ($sale['discount_total'] > 0) {
                $subtotal = $sale['total'] + $sale['discount_total'];
                $sale['discount_percentage'] = ($sale['discount_total'] / $subtotal) * 100;
            } else {
                $sale['discount_percentage'] = 0;
            }
            
            // Get the sale items with proper error handling
            $itemsStmt = $pdo->prepare("
                SELECT 
                    si.id,
                    si.product_id,
                    si.product_size_id,
                    si.quantity,
                    si.subtotal,
                    p.name as product_name,
                    p.selling_price as price,
                    ps.size_name
                FROM sale_items si
                LEFT JOIN products p ON si.product_id = p.id
                LEFT JOIN product_sizes ps ON si.product_size_id = ps.id
                WHERE si.sale_id = ?
            ");
            $itemsStmt->execute([$saleId]);
            $sale['items'] = $itemsStmt->fetchAll();
            
            // Format order ID
            $sale['order_id'] = formatOrderId($sale['id']);
            
            echo json_encode([
                'success' => true,
                'sale' => $sale
            ]);
        } catch (Exception $e) {
            error_log("Error fetching sale #$saleId: " . $e->getMessage());
            http_response_code(500);
            echo json_encode([
                'success' => false, 
                'message' => 'Error retrieving sale data: ' . $e->getMessage()
            ]);
        }
        exit;
    }
    
    // List sales with filters
    $search = $_GET['search'] ?? '';
    $status = $_GET['status'] ?? '';
    
    $conds = [];
    $params = [];
    
    if ($search) {
        $conds[] = "(s.id LIKE :search OR c.name LIKE :search)";
        $params[':search'] = "%$search%";
    }
    
    if ($status && $status !== 'All Statuses') {
        $conds[] = "s.status = :status";
        $params[':status'] = strtolower($status);
    }
    
    $whereClause = $conds ? 'WHERE ' . implode(' AND ', $conds) : '';
    
    try {
        $sql = "
            SELECT 
                s.id, 
                s.total, 
                s.status, 
                s.created_at, 
                COALESCE(c.name, 'Unknown Customer') as customer_name
            FROM sales s
            LEFT JOIN customers c ON s.customer_id = c.id
            $whereClause
            ORDER BY s.created_at DESC
        ";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $sales = $stmt->fetchAll();
        
        // For each sale, get its items
        foreach ($sales as &$sale) {
            $itemsStmt = $pdo->prepare("
                SELECT 
                    si.id,
                    si.quantity,
                    si.subtotal,
                    p.name as product_name,
                    ps.size_name
                FROM sale_items si
                LEFT JOIN products p ON si.product_id = p.id
                LEFT JOIN product_sizes ps ON si.product_size_id = ps.id
                WHERE si.sale_id = ?
            ");
            $itemsStmt->execute([$sale['id']]);
            $sale['items'] = $itemsStmt->fetchAll();
            
            // Format order ID 
            $sale['order_id'] = formatOrderId($sale['id']);
        }
        
        echo json_encode([
            'success' => true,
            'sales' => $sales
        ]);
    } catch (Exception $e) {
        error_log("Error fetching sales list: " . $e->getMessage());
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'message' => 'Error retrieving sales data: ' . $e->getMessage() 
        ]);
    }
    exit;
}

// POST endpoint - Create a new sale
if ($method === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    
    // Validate required data
    if (!isset($data['customer']) || !isset($data['items']) || empty($data['items'])) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Missing required data']);
        exit;
    }
    
    try {
        $pdo->beginTransaction();
        
        // Get or create customer
        $customerId = getOrCreateCustomer($pdo, $data['customer']);
        
        // Calculate total
        $total = 0;
        foreach ($data['items'] as $item) {
            $total += $item['subtotal'];
        }
        
        // Apply discount if provided
        $discountTotal = 0;
        if (isset($data['discount']) && is_numeric($data['discount']) && $data['discount'] > 0) {
            $discountTotal = $total * ($data['discount'] / 100);
            $total -= $discountTotal;
        }
        
        // Create sale record
        $status = $data['status'] ?? 'pending';
        $stmt = $pdo->prepare("
            INSERT INTO sales (user_id, customer_id, total, discount_total, status, created_at)
            VALUES (?, ?, ?, ?, ?, NOW())
        ");
        $stmt->execute([$_SESSION['user_id'], $customerId, $total, $discountTotal, $status]);
        $saleId = $pdo->lastInsertId();
        
        // Create sale items and update stock
        foreach ($data['items'] as $item) {
            $productId = $item['product_id'];
            $productSizeId = $item['product_size_id'] ?? null;
            $quantity = $item['quantity'];
            $subtotal = $item['subtotal'];
            $itemDiscount = $item['discount'] ?? 0;
            
            // Insert sale item
            $stmt = $pdo->prepare("
                INSERT INTO sale_items (sale_id, product_id, product_size_id, quantity, subtotal, discount, created_at)
                VALUES (?, ?, ?, ?, ?, ?, NOW())
            ");
            $stmt->execute([$saleId, $productId, $productSizeId, $quantity, $subtotal, $itemDiscount]);
            
            // Update stock if item is confirmed or pending
            if ($status !== 'canceled') {
                // Update product size stock
                if ($productSizeId) {
                    $stmt = $pdo->prepare("
                        UPDATE product_sizes
                        SET stock = stock - ?
                        WHERE id = ? AND stock >= ?
                    ");
                    $stmt->execute([$quantity, $productSizeId, $quantity]);
                    
                    // Check if stock update was successful
                    if ($stmt->rowCount() === 0) {
                        throw new Exception("Insufficient stock for product size ID $productSizeId");
                    }
                    
                    // Update product total stock
                    updateProductStock($pdo, $productId);
                    
                    // Log stock change
                    $changes = "Reduced $quantity Stock";
                    $reason = "Sale " . formatOrderId($saleId);
                    logStock($pdo, $_SESSION['user_id'], $productId, $changes, $reason);
                }
            }
        }
        
        // Log audit action
        $action = "Created Sale " . formatOrderId($saleId);
        logAudit($pdo, $_SESSION['user_id'], $saleId, $action);
        
        $pdo->commit();
        
        echo json_encode([
            'success' => true,
            'sale_id' => $saleId,
            'order_id' => formatOrderId($saleId),
            'message' => 'Sale created successfully'
        ]);
    } catch (Exception $e) {
        $pdo->rollBack();
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'message' => $e->getMessage()
        ]);
    }
    exit;
}

// DELETE endpoint - Delete a sale
if ($method === 'DELETE') {
    $data = json_decode(file_get_contents('php://input'), true);
    
    if (!isset($data['id'])) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Missing sale ID']);
        exit;
    }
    
    $saleId = $data['id'];
    
    try {
        $pdo->beginTransaction();
        
        // Get the sale details to check status
        $stmt = $pdo->prepare("SELECT status FROM sales WHERE id = ?");
        $stmt->execute([$saleId]);
        $saleStatus = $stmt->fetchColumn();
        
        if (!$saleStatus) {
            throw new Exception("Sale not found");
        }
        
        // If the sale was not canceled, restore stock
        if ($saleStatus !== 'canceled') {
            // Get all items in the sale
            $itemsStmt = $pdo->prepare("
                SELECT product_id, product_size_id, quantity
                FROM sale_items
                WHERE sale_id = ?
            ");
            $itemsStmt->execute([$saleId]);
            $items = $itemsStmt->fetchAll();
            
            // Restore stock for each item
            foreach ($items as $item) {
                if ($item['product_size_id']) {
                    // Update product_sizes stock
                    $stmt = $pdo->prepare("
                        UPDATE product_sizes
                        SET stock = stock + ?
                        WHERE id = ?
                    ");
                    $stmt->execute([$item['quantity'], $item['product_size_id']]);
                    
                    // Update product total stock
                    updateProductStock($pdo, $item['product_id']);
                    
                    // Log stock change
                    $changes = "Added {$item['quantity']} Stock";
                    $reason = "Sale " . formatOrderId($saleId) . " Deleted";
                    logStock($pdo, $_SESSION['user_id'], $item['product_id'], $changes, $reason);
                }
            }
        }
        
        // First delete related audit logs for the sale
        $stmt = $pdo->prepare("DELETE FROM audit_logs WHERE sale_id = ?");
        $stmt->execute([$saleId]);
        
        // Delete sale items
        $stmt = $pdo->prepare("DELETE FROM sale_items WHERE sale_id = ?");
        $stmt->execute([$saleId]);
        
        // Delete the sale
        $stmt = $pdo->prepare("DELETE FROM sales WHERE id = ?");
        $stmt->execute([$saleId]);
        
        // Log this deletion in a more general audit log without sale_id reference
        $stmt = $pdo->prepare("
            INSERT INTO audit_logs (user_id, action, timestamp)
            VALUES (?, ?, NOW())
        ");
        $stmt->execute([$_SESSION['user_id'], "Deleted Sale " . formatOrderId($saleId)]);
        
        $pdo->commit();
        
        echo json_encode([
            'success' => true,
            'message' => 'Sale deleted successfully'
        ]);
    } catch (Exception $e) {
        $pdo->rollBack();
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'message' => $e->getMessage()
        ]);
    }
    exit;
}

// PUT endpoint - Update sale status or full sale update
if ($method === 'PUT') {
    // Log incoming data for debugging
    error_log("PUT request data: " . file_get_contents('php://input'));
    
    $data = json_decode(file_get_contents('php://input'), true);
    
    // Log decoded data
    if (!$data) {
        error_log("Error decoding JSON from PUT request: " . json_last_error_msg());
        http_response_code(400);
        echo json_encode([
            'success' => false, 
            'message' => 'Invalid JSON: ' . json_last_error_msg()
        ]);
        exit;
    }
    
    error_log("Decoded PUT data: " . print_r($data, true));
    
    // If only ID and status are provided, this is a status update
    if (isset($data['id']) && isset($data['status']) && count($data) === 2) {
        $saleId = $data['id'];
        $newStatus = strtolower($data['status']);
        
        // Validate status
        $validStatuses = ['pending', 'confirmed', 'delivered', 'canceled'];
        if (!in_array($newStatus, $validStatuses)) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Invalid status']);
            exit;
        }
        
        try {
            $pdo->beginTransaction();
            
            // Get current status
            $stmt = $pdo->prepare("SELECT status FROM sales WHERE id = ?");
            $stmt->execute([$saleId]);
            $currentStatus = $stmt->fetchColumn();
            
            if (!$currentStatus) {
                throw new Exception("Sale not found");
            }
            
            // Handle stock changes if status changes to or from 'canceled'
            if ($currentStatus !== 'canceled' && $newStatus === 'canceled') {
                // If changing to canceled, restore stock
                $itemsStmt = $pdo->prepare("
                    SELECT product_id, product_size_id, quantity
                    FROM sale_items
                    WHERE sale_id = ?
                ");
                $itemsStmt->execute([$saleId]);
                $items = $itemsStmt->fetchAll();
                
                foreach ($items as $item) {
                    if ($item['product_size_id']) {
                        // Increase stock in product_sizes
                        $stmt = $pdo->prepare("
                            UPDATE product_sizes
                            SET stock = stock + ?
                            WHERE id = ?
                        ");
                        $stmt->execute([$item['quantity'], $item['product_size_id']]);
                        
                        // Update product total stock
                        updateProductStock($pdo, $item['product_id']);
                        
                        // Log stock change
                        $changes = "Added {$item['quantity']} Stock";
                        $reason = "Sale " . formatOrderId($saleId) . " Canceled";
                        logStock($pdo, $_SESSION['user_id'], $item['product_id'], $changes, $reason);
                    }
                }
            } else if ($currentStatus === 'canceled' && $newStatus !== 'canceled') {
                // If changing from canceled, reduce stock again
                $itemsStmt = $pdo->prepare("
                    SELECT product_id, product_size_id, quantity
                    FROM sale_items
                    WHERE sale_id = ?
                ");
                $itemsStmt->execute([$saleId]);
                $items = $itemsStmt->fetchAll();
                
                foreach ($items as $item) {
                    if ($item['product_size_id']) {
                        // Check if enough stock
                        $stockStmt = $pdo->prepare("
                            SELECT stock FROM product_sizes WHERE id = ?
                        ");
                        $stockStmt->execute([$item['product_size_id']]);
                        $currentStock = $stockStmt->fetchColumn();
                        
                        if ($currentStock < $item['quantity']) {
                            throw new Exception("Insufficient stock to reactivate sale");
                        }
                        
                        // Decrease stock in product_sizes
                        $stmt = $pdo->prepare("
                            UPDATE product_sizes
                            SET stock = stock - ?
                            WHERE id = ?
                        ");
                        $stmt->execute([$item['quantity'], $item['product_size_id']]);
                        
                        // Update product total stock
                        updateProductStock($pdo, $item['product_id']);
                        
                        // Log stock change
                        $changes = "Reduced {$item['quantity']} Stock";
                        $reason = "Sale " . formatOrderId($saleId) . " Reactivated as $newStatus";
                        logStock($pdo, $_SESSION['user_id'], $item['product_id'], $changes, $reason);
                    }
                }
            }
            
            // Update sale status
            $stmt = $pdo->prepare("UPDATE sales SET status = ? WHERE id = ?");
            $stmt->execute([$newStatus, $saleId]);
            
            // Log audit action
            $action = "Changed status of Sale " . formatOrderId($saleId) . " to " . ucfirst($newStatus);
            logAudit($pdo, $_SESSION['user_id'], $saleId, $action);
            
            $pdo->commit();
            
            echo json_encode([
                'success' => true,
                'message' => 'Sale status updated successfully'
            ]);
        } catch (Exception $e) {
            $pdo->rollBack();
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        }
        exit;
    }
    
    // Full sale update
    if (isset($data['id']) && isset($data['customer']) && isset($data['items'])) {
        $saleId = $data['id'];
        
        // Double check that the ID is valid
        if (!is_numeric($saleId) || $saleId <= 0) {
            http_response_code(400);
            echo json_encode([
                'success' => false, 
                'message' => 'Invalid sale ID'
            ]);
            exit;
        }
        
        try {
            $pdo->beginTransaction();
            
            // Additional validation
            if (empty($data['items'])) {
                throw new Exception("Order must contain at least one item");
            }

            // Validate all items have required fields
            foreach ($data['items'] as $index => $item) {
                if (!isset($item['product_id']) || !is_numeric($item['product_id'])) {
                    throw new Exception("Item #" . ($index + 1) . " has invalid product ID");
                }
                if (!isset($item['quantity']) || !is_numeric($item['quantity']) || $item['quantity'] <= 0) {
                    throw new Exception("Item #" . ($index + 1) . " has invalid quantity");
                }
            }
            
            // Get current sale status to handle stock changes
            $stmt = $pdo->prepare("SELECT status FROM sales WHERE id = ?");
            $stmt->execute([$saleId]);
            $currentStatus = $stmt->fetchColumn();
            
            if (!$currentStatus) {
                throw new Exception("Sale not found");
            }
            
            // Get or update customer
            $customerId = getOrCreateCustomer($pdo, $data['customer']);
            
            // Get current sale items to compare with new items for stock adjustment
            $currentItemsStmt = $pdo->prepare("
                SELECT product_id, product_size_id, quantity
                FROM sale_items
                WHERE sale_id = ?
            ");
            $currentItemsStmt->execute([$saleId]);
            $currentItems = $currentItemsStmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Create a map of current items for easier comparison
            $currentItemsMap = [];
            foreach ($currentItems as $item) {
                $key = $item['product_id'] . '_' . ($item['product_size_id'] ?? 'null');
                $currentItemsMap[$key] = $item;
            }
            
            // Calculate total and handle stock changes for new items
            $total = 0;
            $newItemsMap = [];
            
            foreach ($data['items'] as $item) {
                $total += $item['subtotal'];
                
                // Skip if we don't have product_size_id or if it's explicitly null
                if (!isset($item['product_size_id']) || $item['product_size_id'] === null || $item['product_size_id'] === '') {
                    continue;
                }
                
                $key = $item['product_id'] . '_' . $item['product_size_id'];
                
                // Track new items for stock comparison
                if (!isset($newItemsMap[$key])) {
                    $newItemsMap[$key] = [
                        'product_id' => $item['product_id'],
                        'product_size_id' => $item['product_size_id'],
                        'quantity' => $item['quantity']
                    ];
                } else {
                    $newItemsMap[$key]['quantity'] += $item['quantity'];
                }
            }
            
            // Apply discount if provided
            $discountTotal = 0;
            if (isset($data['discount']) && is_numeric($data['discount']) && $data['discount'] > 0) {
                $discountTotal = $total * ($data['discount'] / 100);
                $total -= $discountTotal;
            }
            
            // Update the sale record first
            $status = $data['status'] ?? $currentStatus; // Keep current status if not provided
            $note = $data['note'] ?? null;
            
            // Check if note column exists
            $columnExistStmt = $pdo->prepare("
                SELECT COUNT(*) 
                FROM information_schema.COLUMNS 
                WHERE TABLE_SCHEMA = :dbname
                AND TABLE_NAME = 'sales' 
                AND COLUMN_NAME = 'note'
            ");
            $columnExistStmt->execute([':dbname' => $dbname]);
            $noteColumnExists = $columnExistStmt->fetchColumn() > 0;
            
            // Build the query based on column existence
            if ($noteColumnExists) {
                $updateQuery = "
                    UPDATE sales 
                    SET customer_id = ?, total = ?, discount_total = ?, status = ?, note = ?
                    WHERE id = ?
                ";
                $stmt = $pdo->prepare($updateQuery);
                $stmt->execute([$customerId, $total, $discountTotal, $status, $note, $saleId]);
            } else {
                $updateQuery = "
                    UPDATE sales 
                    SET customer_id = ?, total = ?, discount_total = ?, status = ?
                    WHERE id = ?
                ";
                $stmt = $pdo->prepare($updateQuery);
                $stmt->execute([$customerId, $total, $discountTotal, $status, $saleId]);
            }
            
            // Delete all existing sale items
            $stmt = $pdo->prepare("DELETE FROM sale_items WHERE sale_id = ?");
            $stmt->execute([$saleId]);
            
            // Create new sale items
            foreach ($data['items'] as $item) {
                $productId = $item['product_id'];
                $productSizeId = $item['product_size_id'] === '' ? null : $item['product_size_id']; // Handle empty strings
                $quantity = $item['quantity'];
                $subtotal = $item['subtotal'];
                $itemDiscount = $item['discount'] ?? 0;
                
                // Insert sale item
                $stmt = $pdo->prepare("
                    INSERT INTO sale_items (sale_id, product_id, product_size_id, quantity, subtotal, discount, created_at)
                    VALUES (?, ?, ?, ?, ?, ?, NOW())
                ");
                $stmt->execute([$saleId, $productId, $productSizeId, $quantity, $subtotal, $itemDiscount]);
            }
            
            // If sale status is not 'canceled', adjust stock based on differences
            if ($currentStatus !== 'canceled') {
                // First return all current items to stock
                foreach ($currentItems as $item) {
                    if ($item['product_size_id']) {
                        // Add the items back to stock
                        $stmt = $pdo->prepare("
                            UPDATE product_sizes
                            SET stock = stock + ?
                            WHERE id = ?
                        ");
                        $stmt->execute([$item['quantity'], $item['product_size_id']]);
                        
                        // Update product stock
                        updateProductStock($pdo, $item['product_id']);
                        
                        // Log stock change
                        $changes = "Added {$item['quantity']} Stock";
                        $reason = "Sale " . formatOrderId($saleId) . " Update - Returned";
                        logStock($pdo, $_SESSION['user_id'], $item['product_id'], $changes, $reason);
                    }
                }
                
                // Then deduct new items from stock
                foreach ($newItemsMap as $key => $item) {
                    if ($item['product_size_id']) {
                        // Check if enough stock
                        $stockStmt = $pdo->prepare("
                            SELECT stock FROM product_sizes WHERE id = ?
                        ");
                        $stockStmt->execute([$item['product_size_id']]);
                        $currentStock = $stockStmt->fetchColumn();
                        
                        if ($currentStock < $item['quantity']) {
                            throw new Exception("Insufficient stock for product with size ID {$item['product_size_id']}");
                        }
                        
                        // Deduct from stock
                        $stmt = $pdo->prepare("
                            UPDATE product_sizes
                            SET stock = stock - ?
                            WHERE id = ?
                        ");
                        $stmt->execute([$item['quantity'], $item['product_size_id']]);
                        
                        // Update product stock
                        updateProductStock($pdo, $item['product_id']);
                        
                        // Log stock change
                        $changes = "Reduced {$item['quantity']} Stock";
                        $reason = "Sale " . formatOrderId($saleId) . " Update - Added";
                        logStock($pdo, $_SESSION['user_id'], $item['product_id'], $changes, $reason);
                    }
                }
            }
            
            // Log audit action
            $action = "Updated Sale " . formatOrderId($saleId);
            logAudit($pdo, $_SESSION['user_id'], $saleId, $action);
            
            $pdo->commit();
            
            echo json_encode([
                'success' => true,
                'message' => 'Sale updated successfully'
            ]);
        } catch (Exception $e) {
            $pdo->rollBack();
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        }
        exit;
    }
    
    // If we get here, it's an invalid PUT request
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid update data']);
    exit;
}

// Invalid method
http_response_code(405);
echo json_encode(['success' => false, 'message' => 'Method not allowed']);
exit;
