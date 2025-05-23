<?php
// api/batches.php

require_once __DIR__ . '/../db.php';

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

header('Content-Type: application/json');
$method = $_SERVER['REQUEST_METHOD'];

// Add the recently manufactured endpoint
if (isset($_GET['recentlyManufactured'])) {
    $stmt = $pdo->query("SELECT COUNT(*) FROM batches WHERE manufactured_date >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)");
    $count = $stmt->fetchColumn();
    echo json_encode(['success' => true, 'count' => $count]);
    exit;
}

/**
 * Generate a unique batch number in the format BATCH-YYYY-NNN.
 */
function generateBatchNumber(PDO $pdo, int $productId) {
    $year = date('Y');
    $stmt = $pdo->prepare("
        SELECT COUNT(*) as count 
        FROM batches 
        WHERE batch_number LIKE ?
    ");
    $stmt->execute(["BATCH-{$year}-%"]);
    $count = $stmt->fetchColumn() + 1;
    return sprintf("BATCH-%s-%03d", $year, $count);
}

/**
 * Check if a batch number is unique across all products.
 */
function isBatchNumberUnique(PDO $pdo, string $batchNumber, int $excludeBatchId = null) {
    $sql = "SELECT COUNT(*) FROM batches WHERE batch_number = ?";
    if ($excludeBatchId) {
        $sql .= " AND id != ?";
    }
    $stmt = $pdo->prepare($sql);
    $params = [$batchNumber];
    if ($excludeBatchId) {
        $params[] = $excludeBatchId;
    }
    $stmt->execute($params);
    return $stmt->fetchColumn() == 0;
}

if ($method === 'GET') {
    $search = $_GET['search'] ?? '';
    $productId = $_GET['product_id'] ?? '';
    // Pagination parameters (optional)
    $page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : null;
    $limit = isset($_GET['limit']) ? max(1, intval($_GET['limit'])) : null;

    $conds = [];
    $params = [];

    if ($search !== '') {
        $conds[] = "p.name LIKE :search OR b.batch_number LIKE :search";
        $params['search'] = "%$search%";
    }
    if (is_numeric($productId)) {
        $conds[] = "b.product_id = :product_id";
        $params['product_id'] = $productId;
    }
    $where = $conds ? 'WHERE ' . implode(' AND ', $conds) : '';

    // Optional pagination
    $isPaginated = ($page !== null && $limit !== null);
    $totalCount = 0;
    $totalPages = 0;
    
    // Get total count for pagination if needed
    if ($isPaginated) {
        $countSql = "
          SELECT COUNT(*) as total
          FROM batches b
          JOIN products p ON b.product_id = p.id
          JOIN product_sizes ps ON b.product_size_id = ps.id
          $where
        ";
        $countStmt = $pdo->prepare($countSql);
        $countStmt->execute($params);
        $totalCount = $countStmt->fetchColumn();
        $totalPages = ceil($totalCount / $limit);
        $offset = ($page - 1) * $limit;
    }

    // Build the base SQL query
    $sql = "
      SELECT b.id, b.product_id, b.product_size_id, b.batch_number, b.manufactured_date, b.stock,
             p.name AS product_name, ps.size_name
      FROM batches b
      JOIN products p ON b.product_id = p.id
      JOIN product_sizes ps ON b.product_size_id = ps.id
      $where
      ORDER BY b.created_at DESC
    ";
    
    // Add pagination if requested
    if ($isPaginated) {
        $sql .= " LIMIT :limit OFFSET :offset";
    }
    
    $stmt = $pdo->prepare($sql);
    
    // Bind pagination parameters if needed
    if ($isPaginated) {
        $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
        
        // Bind other params
        foreach ($params as $key => $value) {
            $stmt->bindValue(":$key", $value);
        }
    } else {
        // Without pagination, use the simpler execute with params array
        $stmt->execute($params);
    }
    
    // Execute the statement if pagination is used (we need to execute after binding all params)
    if ($isPaginated) {
        $stmt->execute();
    }
    
    $batches = $stmt->fetchAll();

    // Return with pagination metadata if paginated request, otherwise return batches array directly
    if ($isPaginated) {
        echo json_encode([
            'batches' => $batches,
            'pagination' => [
                'total' => (int)$totalCount,
                'limit' => (int)$limit,
                'current_page' => (int)$page,
                'total_pages' => (int)$totalPages,
                'from' => $totalCount > 0 ? ($page - 1) * $limit + 1 : 0,
                'to' => min($totalCount, $page * $limit)
            ]
        ]);
    } else {
        echo json_encode($batches);
    }
    exit;
}

if ($method === 'POST') {
    $input = $_POST;
    if (empty($input)
        && strpos($_SERVER['CONTENT_TYPE'] ?? '', 'application/json') !== false
    ) {
        $input = json_decode(file_get_contents('php://input'), true) ?? [];
    }

    if (!empty($input['action']) && $input['action'] === 'create') {
        if (empty($input['product_id']) || empty($input['product_size_id']) || empty($input['batch_number']) || empty($input['stock']) || empty($input['manufactured_date'])) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Product ID, Product Size ID, batch number, stock, and manufactured date are required']);
            exit;
        }

        try {
            $pdo->beginTransaction();

            $sizeStmt = $pdo->prepare("
                SELECT stock 
                FROM product_sizes 
                WHERE id = ? AND product_id = ?
            ");
            $sizeStmt->execute([$input['product_size_id'], $input['product_id']]);
            $currentSizeStock = $sizeStmt->fetchColumn();
            if ($currentSizeStock === false) {
                throw new Exception('Invalid product size');
            }

            $batchStockStmt = $pdo->prepare("
                SELECT SUM(stock) 
                FROM batches 
                WHERE product_size_id = ?
            ");
            $batchStockStmt->execute([$input['product_size_id']]);
            $totalBatchStock = $batchStockStmt->fetchColumn() ?: 0;

            $newBatchStock = (int)$input['stock'];
            $newTotalBatchStock = $totalBatchStock + $newBatchStock;

            if ($newTotalBatchStock > $currentSizeStock) {
                $updSizeStmt = $pdo->prepare("
                    UPDATE product_sizes 
                    SET stock = ? 
                    WHERE id = ?
                ");
                $updSizeStmt->execute([$newTotalBatchStock, $input['product_size_id']]);

                $totalStockStmt = $pdo->prepare("
                    SELECT SUM(stock) 
                    FROM product_sizes 
                    WHERE product_id = ?
                ");
                $totalStockStmt->execute([$input['product_id']]);
                $newProductStock = $totalStockStmt->fetchColumn() ?: 0;

                $pdo->prepare("UPDATE products SET stock = ? WHERE id = ?")
                    ->execute([$newProductStock, $input['product_id']]);
            }

            $batchNumber = $input['batch_number'];
            if (!isBatchNumberUnique($pdo, $batchNumber)) {
                $batchNumber = generateBatchNumber($pdo, $input['product_id']);
            }

            $insStmt = $pdo->prepare("
                INSERT INTO batches (product_id, product_size_id, batch_number, manufactured_date, stock)
                VALUES (?, ?, ?, ?, ?)
            ");
            $insStmt->execute([
                $input['product_id'],
                $input['product_size_id'],
                $batchNumber,
                $input['manufactured_date'],
                $newBatchStock
            ]);

            // Create notification for new batch
            $productStmt = $pdo->prepare("SELECT name FROM products WHERE id = ?");
            $productStmt->execute([$input['product_id']]);
            $productName = $productStmt->fetchColumn();
            
            $sizeStmt = $pdo->prepare("SELECT size_name FROM product_sizes WHERE id = ?");
            $sizeStmt->execute([$input['product_size_id']]);
            $sizeName = $sizeStmt->fetchColumn();
            
            $notificationTitle = "New Batch Added";
            $notificationMessage = "New batch '{$batchNumber}' for product '{$productName}' (Size: {$sizeName}) has been added with {$newBatchStock} units";
            createNotification($pdo, 'stock', $notificationTitle, $notificationMessage, 'all');

            $pdo->commit();
            echo json_encode(['success' => true]);
        } catch (Exception $e) {
            $pdo->rollBack();
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Failed to create batch: ' . $e->getMessage()]);
        }
        exit;
    }

    if (!empty($input['action']) && $input['action'] === 'update') {
        if (empty($input['id']) || empty($input['batch_number']) || empty($input['stock']) || empty($input['manufactured_date'])) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Batch ID, batch number, stock, and manufactured date are required']);
            exit;
        }

        try {
            $pdo->beginTransaction();

            $batchStmt = $pdo->prepare("
                SELECT product_id, product_size_id, stock, batch_number 
                FROM batches 
                WHERE id = ?
            ");
            $batchStmt->execute([$input['id']]);
            $batchData = $batchStmt->fetch();
            if (!$batchData) {
                throw new Exception('Invalid batch ID');
            }

            $batchNumber = $input['batch_number'];
            if ($batchNumber !== $batchData['batch_number'] && !isBatchNumberUnique($pdo, $batchNumber, $input['id'])) {
                $batchNumber = generateBatchNumber($pdo, $batchData['product_id']);
            }

            $sizeStockStmt = $pdo->prepare("
                SELECT stock 
                FROM product_sizes 
                WHERE id = ?
            ");
            $sizeStockStmt->execute([$batchData['product_size_id']]);
            $currentSizeStock = $sizeStockStmt->fetchColumn();

            $batchStockStmt = $pdo->prepare("
                SELECT SUM(stock) 
                FROM batches 
                WHERE product_size_id = ? AND id != ?
            ");
            $batchStockStmt->execute([$batchData['product_size_id'], $input['id']]);
            $totalOtherBatchStock = $batchStockStmt->fetchColumn() ?: 0;

            $newBatchStock = (int)$input['stock'];
            $newTotalBatchStock = $totalOtherBatchStock + $newBatchStock;

            if ($newTotalBatchStock > $currentSizeStock) {
                $updSizeStmt = $pdo->prepare("
                    UPDATE product_sizes 
                    SET stock = ? 
                    WHERE id = ?
                ");
                $updSizeStmt->execute([$newTotalBatchStock, $batchData['product_size_id']]);

                $totalStockStmt = $pdo->prepare("
                    SELECT SUM(stock) 
                    FROM product_sizes 
                    WHERE product_id = ?
                ");
                $totalStockStmt->execute([$batchData['product_id']]);
                $newProductStock = $totalStockStmt->fetchColumn() ?: 0;

                $pdo->prepare("UPDATE products SET stock = ? WHERE id = ?")
                    ->execute([$newProductStock, $batchData['product_id']]);
            }

            $updStmt = $pdo->prepare("
                UPDATE batches 
                SET batch_number = ?, manufactured_date = ?, stock = ?
                WHERE id = ?
            ");
            $updStmt->execute([
                $batchNumber,
                $input['manufactured_date'],
                $newBatchStock,
                $input['id']
            ]);

            // Create notification for batch update
            $productStmt = $pdo->prepare("SELECT p.name, ps.size_name 
                                         FROM batches b 
                                         JOIN products p ON b.product_id = p.id 
                                         JOIN product_sizes ps ON b.product_size_id = ps.id 
                                         WHERE b.id = ?");
            $productStmt->execute([$input['id']]);
            $productData = $productStmt->fetch();
            
            $notificationTitle = "Batch Updated";
            $notificationMessage = "Batch '{$batchNumber}' for product '{$productData['name']}' (Size: {$productData['size_name']}) has been updated to {$newBatchStock} units";
            createNotification($pdo, 'stock', $notificationTitle, $notificationMessage, 'all');

            $pdo->commit();
            echo json_encode(['success' => true]);
        } catch (Exception $e) {
            $pdo->rollBack();
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Failed to update batch: ' . $e->getMessage()]);
        }
        exit;
    }

    if (!empty($input['action']) && $input['action'] === 'delete') {
        if (empty($input['id'])) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Batch ID required']);
            exit;
        }

        try {
            $pdo->beginTransaction();

            $batchStmt = $pdo->prepare("
                SELECT b.batch_number, b.product_id, b.product_size_id, p.name as product_name, ps.size_name 
                FROM batches b
                JOIN products p ON b.product_id = p.id
                JOIN product_sizes ps ON b.product_size_id = ps.id
                WHERE b.id = ?
            ");
            $batchStmt->execute([$input['id']]);
            $batchData = $batchStmt->fetch();
            if (!$batchData) {
                throw new Exception('Invalid batch ID');
            }

            $pdo->prepare("DELETE FROM batches WHERE id = ?")
                ->execute([$input['id']]);

            $batchStockStmt = $pdo->prepare("
                SELECT SUM(stock) 
                FROM batches 
                WHERE product_size_id = ?
            ");
            $batchStockStmt->execute([$batchData['product_size_id']]);
            $totalBatchStock = $batchStockStmt->fetchColumn() ?: 0;

            $updSizeStmt = $pdo->prepare("
                UPDATE product_sizes 
                SET stock = ? 
                WHERE id = ?
            ");
            $updSizeStmt->execute([$totalBatchStock, $batchData['product_size_id']]);

            $totalStockStmt = $pdo->prepare("
                SELECT SUM(stock) 
                FROM product_sizes 
                WHERE product_id = ?
            ");
            $totalStockStmt->execute([$batchData['product_id']]);
            $newProductStock = $totalStockStmt->fetchColumn() ?: 0;

            $pdo->prepare("UPDATE products SET stock = ? WHERE id = ?")
                ->execute([$newProductStock, $batchData['product_id']]);

            // Create notification for batch deletion
            $notificationTitle = "Batch Deleted";
            $notificationMessage = "Batch '{$batchData['batch_number']}' for product '{$batchData['product_name']}' (Size: {$batchData['size_name']}) has been deleted";
            createNotification($pdo, 'other', $notificationTitle, $notificationMessage, 'all');

            $pdo->commit();
            echo json_encode(['success' => true]);
        } catch (Exception $e) {
            $pdo->rollBack();
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Failed to delete batch: ' . $e->getMessage()]);
        }
        exit;
    }

    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid action']);
    exit;
}

http_response_code(405);
echo json_encode(['success' => false, 'message' => 'Method Not Allowed']);