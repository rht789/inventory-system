<?php
require_once __DIR__ . '/../db.php';
header('Content-Type: application/json');

// Helper function to create a notification
function createNotification($pdo, $type, $title, $message, $role = 'all') {
    try {
        $stmt = $pdo->prepare("
            INSERT INTO notifications (type, title, message, role, created_at)
            VALUES (?, ?, ?, ?, NOW())
        ");
        $stmt->execute([$type, $title, $message, $role]);
        return $pdo->lastInsertId();
    } catch (Exception $e) {
        error_log("Error creating notification: " . $e->getMessage());
        return false;
    }
}

// Save stock adjustment (POST)
$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'POST') {
    $data = $_POST;

    $product_id = $data['product_id'] ?? null;
    $size_id = $data['product_size_id'] ?? null;
    $quantity = $data['quantity'] ?? null;
    $reason = trim($data['reason'] ?? '');
    $location = trim($data['location'] ?? '');
    $type = $data['type'] ?? 'in'; // 'in' or 'out'
    $user_id = 1; // Replace with session-based logic, e.g., $_SESSION['user_id']

    // Validate required fields with specific messages
    if (!$product_id) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Product ID is required']);
        exit;
    }
    if (!$size_id) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Size ID is required']);
        exit;
    }
    if (!$quantity) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Quantity is required']);
        exit;
    }
    if (!$reason) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Reason is required']);
        exit;
    }

    $quantity = (int) $quantity;
    if ($quantity <= 0) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Quantity must be greater than 0']);
        exit;
    }

    // Construct the changes string
    $action = ($type === 'in') ? 'Added' : 'Reduced';
    $changes = "$action $quantity Stock";

    try {
        $pdo->beginTransaction();

        // Validate product_id exists and fetch product name for notification
        $stmt = $pdo->prepare("SELECT id, name FROM products WHERE id = ? AND deleted_at IS NULL");
        $stmt->execute([$product_id]);
        $product = $stmt->fetch();
        if (!$product) {
            throw new Exception("Invalid product selected");
        }
        $product_name = $product['name'];

        // Validate size_id exists and belongs to the product
        $stmt = $pdo->prepare("SELECT stock FROM product_sizes WHERE id = ? AND product_id = ?");
        $stmt->execute([$size_id, $product_id]);
        $current_stock = $stmt->fetchColumn();
        if ($current_stock === false) {
            throw new Exception("Invalid size selected for this product");
        }

        // Calculate new stock based on type
        $stock_change = ($type === 'in') ? $quantity : -$quantity;
        $new_stock = $current_stock + $stock_change;
        if ($new_stock < 0) {
            throw new Exception("Stock cannot be reduced below 0");
        }

        // Update product stock (total stock across all sizes)
        $pdo->prepare("UPDATE products SET stock = stock + ?, location = ? WHERE id = ?")
            ->execute([$stock_change, $location ?: null, $product_id]);

        // Update size-specific stock
        $pdo->prepare("UPDATE product_sizes SET stock = stock + ? WHERE id = ?")
            ->execute([$stock_change, $size_id]);

        // Insert into stock_logs
        $stmt = $pdo->prepare("
            INSERT INTO stock_logs (product_id, changes, reason, user_id, timestamp)
            VALUES (?, ?, ?, ?, NOW())
        ");
        $stmt->execute([$product_id, $changes, $reason, $user_id]);

        // Create notification for stock update
        $notificationTitle = "Stock updated";
        $notificationMessage = "$action $quantity units for $product_name.";
        createNotification($pdo, 'stock', $notificationTitle, $notificationMessage, 'all');

        $pdo->commit();
        echo json_encode(['success' => true]);
    } catch (Exception $e) {
        $pdo->rollBack();
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
    exit;
}

// Load products, sizes, or locations (GET)
if ($method === 'GET') {
    $action = $_GET['action'] ?? '';

    if ($action === 'get_logs') {
        try {
            // Get parameters
            $search = $_GET['search'] ?? '';
            $typeFilter = $_GET['type'] ?? '';
            $page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
            $perPage = isset($_GET['per_page']) ? max(1, (int)$_GET['per_page']) : 15;
            $offset = ($page - 1) * $perPage;
            
            // Base query for counting total logs
            $countQuery = "
                SELECT COUNT(*) 
                FROM stock_logs sl
                JOIN products p ON sl.product_id = p.id
                JOIN users u ON sl.user_id = u.id
                WHERE 1=1
            ";
            
            // Base query for fetching logs
            $query = "
                SELECT 
                    sl.*,
                    p.name AS product_name,
                    u.username
                FROM stock_logs sl
                JOIN products p ON sl.product_id = p.id
                JOIN users u ON sl.user_id = u.id
                WHERE 1=1
            ";
            
            $params = [];
            
            // Add search condition if provided
            if (!empty($search)) {
                $searchCondition = " AND (p.name LIKE ? OR sl.reason LIKE ?)";
                $countQuery .= $searchCondition;
                $query .= $searchCondition;
                $params[] = "%$search%";
                $params[] = "%$search%";
            }
            
            // Add type filter if provided
            if (!empty($typeFilter)) {
                $typeCondition = " AND sl.changes LIKE ?";
                $countQuery .= $typeCondition;
                $query .= $typeCondition;
                $params[] = "%$typeFilter%";
            }
            
            // Get total count
            $countStmt = $pdo->prepare($countQuery);
            $countStmt->execute($params);
            $totalLogs = $countStmt->fetchColumn();
            $totalPages = ceil($totalLogs / $perPage);
            
            // Add order and limit to main query
            $query .= " ORDER BY sl.timestamp DESC LIMIT $offset, $perPage";
            
            // Execute main query
            $stmt = $pdo->prepare($query);
            $stmt->execute($params);
            $logs = $stmt->fetchAll();
            
            // Build pagination info
            $pagination = [
                'current_page' => $page,
                'per_page' => $perPage,
                'total_logs' => $totalLogs,
                'total_pages' => $totalPages
            ];
            
            echo json_encode([
                'success' => true,
                'logs' => $logs,
                'pagination' => $pagination
            ]);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
        exit;
    }

    if ($action === 'get_locations') {
        try {
            $stmt = $pdo->query("SELECT DISTINCT location FROM products WHERE location IS NOT NULL ORDER BY location ASC");
            $locations = $stmt->fetchAll(PDO::FETCH_COLUMN);
            echo json_encode($locations);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
        exit;
    }

    try {
        // Fetch product list
        $stmt = $pdo->query("SELECT id, name, barcode FROM products WHERE deleted_at IS NULL ORDER BY name ASC");
        $products = $stmt->fetchAll();

        // Attach sizes for each product
        foreach ($products as &$p) {
            $stmt = $pdo->prepare("SELECT id, size_name FROM product_sizes WHERE product_id = ?");
            $stmt->execute([$p['id']]);
            $p['sizes'] = $stmt->fetchAll();
        }

        echo json_encode(['success' => true, 'products' => $products]);
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
    exit;
}

http_response_code(405);
echo json_encode(['success' => false, 'message' => 'Method not allowed']);
?>