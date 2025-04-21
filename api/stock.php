<?php
require_once __DIR__ . '/../db.php';
header('Content-Type: application/json');

// Save stock adjustment (POST)
$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'POST') {
    $data = $_POST;

    $product_id = $data['product_id'] ?? null;
    $size_id = $data['product_size_id'] ?? null;
    $batch_number = trim($data['batch_number'] ?? '');
    $quantity = $data['quantity'] ?? null;
    $reason = trim($data['reason'] ?? '');
    $location = trim($data['location'] ?? '');
    $mode = $data['mode'] ?? 'edit'; // 'add' or 'edit'
    $type = $data['type'] ?? 'in'; // 'in' or 'out'
    $user_id = 1; // Replace with session-based logic, e.g., $_SESSION['user_id']

    if (!$product_id || !$quantity || !$reason) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Missing required fields']);
        exit;
    }

    $quantity = (int) $quantity;
    $change = $type === 'out' ? -abs($quantity) : abs($quantity);
    $batch_id = null;

    try {
        $pdo->beginTransaction();

        // If mode is 'add' and batch_number is provided, create or update batch
        if ($mode === 'add' && $batch_number) {
            $stmt = $pdo->prepare("SELECT id FROM batches WHERE product_id = ? AND batch_number = ?");
            $stmt->execute([$product_id, $batch_number]);
            $batch = $stmt->fetch();

            if ($batch) {
                $batch_id = $batch['id'];
                $pdo->prepare("UPDATE batches SET stock = stock + ? WHERE id = ?")
                    ->execute([$change, $batch_id]);
            } else {
                $stmt = $pdo->prepare("INSERT INTO batches (product_id, batch_number, expiry_date, stock) VALUES (?, ?, DATE_ADD(NOW(), INTERVAL 1 YEAR), ?)");
                $stmt->execute([$product_id, $batch_number, abs($quantity)]);
                $batch_id = $pdo->lastInsertId();
            }
        }

        // Update total product stock
        $pdo->prepare("UPDATE products SET stock = stock + ?, location = ? WHERE id = ?")
            ->execute([$change, $location ?: null, $product_id]);

        // Update size-specific stock
        if ($size_id) {
            $stmt = $pdo->prepare("SELECT stock FROM product_sizes WHERE id = ?");
            $stmt->execute([$size_id]);
            $current_stock = $stmt->fetchColumn();
            $new_stock = $current_stock + $change;

            if ($new_stock < 0) {
                throw new Exception("Stock cannot be reduced below 0");
            }

            $pdo->prepare("UPDATE product_sizes SET stock = stock + ? WHERE id = ?")
                ->execute([$change, $size_id]);
        }

        // Update batch stock (if batch_id exists from edit mode)
        if ($batch_id && $mode !== 'add') {
            $pdo->prepare("UPDATE batches SET stock = stock + ? WHERE id = ?")
                ->execute([$change, $batch_id]);
        }

        // Insert into stock_logs
        $stmt = $pdo->prepare("
            INSERT INTO stock_logs (product_id, batch_id, changes, reason, user_id, timestamp)
            VALUES (?, ?, ?, ?, ?, NOW())
        ");
        $stmt->execute([$product_id, $batch_id, $change, $reason, $user_id]);

        $pdo->commit();
        echo json_encode(['success' => true]);
    } catch (Exception $e) {
        $pdo->rollBack();
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
    exit;
}

// Load products and sizes for dropdowns, or locations (GET)
if ($method === 'GET') {
    $action = $_GET['action'] ?? '';

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