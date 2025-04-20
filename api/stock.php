<?php
// api/stock.php
require_once __DIR__ . '/../db.php';
header('Content-Type: application/json');

$method = $_SERVER['REQUEST_METHOD'];

// Save stock adjustment
if ($method === 'POST') {
    $data = $_POST;

    $product_id = $data['product_id'] ?? null;
    $size_id    = $data['product_size_id'] ?? null;
    $batch_id   = $data['batch_id'] ?? null;
    $quantity   = $data['quantity'] ?? null;
    $reason     = trim($data['reason'] ?? '');
    $location   = trim($data['location'] ?? '');
    $note       = trim($data['note'] ?? '');
    $user_id    = $data['user_id'] ?? 1; // Replace with session-based logic
    $type       = $data['type'] ?? 'in'; // 'in' or 'out'

    if (!$product_id || !$quantity || !$reason) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Missing required fields']);
        exit;
    }

    $quantity = (int) $quantity;
    $change = $type === 'out' ? -abs($quantity) : abs($quantity);

    try {
        $pdo->beginTransaction();

        // Update total product stock
        $pdo->prepare("UPDATE products SET stock = stock + ? WHERE id = ?")
            ->execute([$change, $product_id]);

        // Update size-specific stock
        if ($size_id) {
            $pdo->prepare("UPDATE product_sizes SET stock = stock + ? WHERE id = ?")
                ->execute([$change, $size_id]);
        }

        // Update batch stock (optional)
        if ($batch_id) {
            $pdo->prepare("UPDATE batches SET stock = stock + ? WHERE id = ?")
                ->execute([$change, $batch_id]);
        }

        // Insert into stock_logs
        $stmt = $pdo->prepare("
            INSERT INTO stock_logs (product_id, batch_id, changes, reason, user_id, timestamp)
            VALUES (?, ?, ?, ?, ?, NOW())
        ");
        $stmt->execute([
            $product_id,
            $batch_id,
            $change,
            $reason . ($note ? " ({$note})" : ''),
            $user_id
        ]);

        $pdo->commit();
        echo json_encode(['success' => true]);
    } catch (Exception $e) {
        $pdo->rollBack();
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
    exit;
}

// Load products and sizes for dropdowns
if ($method === 'GET') {
    try {
        // Fetch product list
        $stmt = $pdo->query("
            SELECT id, name, barcode
            FROM products
            WHERE deleted_at IS NULL
            ORDER BY name ASC
        ");
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

// Method not allowed
http_response_code(405);
echo json_encode(['success' => false, 'message' => 'Method not allowed']);
