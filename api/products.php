<?php
// api/products.php

require_once __DIR__ . '/../db.php';
require_once __DIR__ . '/../vendor/autoload.php';

use Picqer\Barcode\BarcodeGeneratorPNG;

header('Content-Type: application/json');
$method = $_SERVER['REQUEST_METHOD'];

/**
 * Overwrite all sizes for a product.
 */
function saveSizes(PDO $pdo, int $productId, array $sizes) {
    $pdo->prepare("DELETE FROM product_sizes WHERE product_id = ?")
        ->execute([$productId]);

    $ins = $pdo->prepare("
        INSERT INTO product_sizes (product_id, size_name, stock)
        VALUES (?, ?, ?)
    ");
    foreach ($sizes as $s) {
        $ins->execute([
            $productId,
            $s['size'],
            $s['stock']
        ]);
    }

    $totalStock = $pdo->prepare("
        SELECT SUM(stock) 
        FROM product_sizes 
        WHERE product_id = ?
    ");
    $totalStock->execute([$productId]);
    $newStock = $totalStock->fetchColumn() ?: 0;

    $pdo->prepare("UPDATE products SET stock = ? WHERE id = ?")
        ->execute([$newStock, $productId]);
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
function isBatchNumberUnique(PDO $pdo, string $batchNumber) {
    $stmt = $pdo->prepare("
        SELECT COUNT(*) 
        FROM batches 
        WHERE batch_number = ?
    ");
    $stmt->execute([$batchNumber]);
    return $stmt->fetchColumn() == 0;
}

if ($method === 'GET') {
    if (!empty($_GET['action']) && $_GET['action'] === 'get_batches') {
        if (empty($_GET['product_id'])) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Product ID required']);
            exit;
        }
        $stmt = $pdo->prepare("
            SELECT b.id, b.batch_number, b.manufactured_date, b.stock, b.created_at, b.product_size_id, ps.size_name
            FROM batches b
            JOIN product_sizes ps ON b.product_size_id = ps.id
            WHERE b.product_id = ?
        ");
        $stmt->execute([$_GET['product_id']]);
        $batches = $stmt->fetchAll();
        echo json_encode($batches);
        exit;
    }

    $search      = $_GET['search']       ?? '';
    $stockFilter = $_GET['stock_filter'] ?? '';
    $categoryId  = $_GET['category_id']  ?? '';

    $conds = ["p.deleted_at IS NULL"];
    $params = [];

    if ($search !== '') {
        $conds[] = "p.name LIKE :search";
        $params['search'] = "%$search%";
    }
    if (is_numeric($categoryId)) {
        $conds[] = "p.category_id = :category_id";
        $params['category_id'] = $categoryId;
    }
    switch ($stockFilter) {
        case 'in_stock':    $conds[] = "p.stock > 0"; break;
        case 'low_stock':   $conds[] = "p.stock <= p.min_stock AND p.stock > 0"; break;
        case 'out_of_stock':$conds[] = "p.stock = 0"; break;
    }
    $where = $conds ? 'WHERE '.implode(' AND ',$conds) : '';

    $sql = "
      SELECT
        p.id, p.name, p.price, p.selling_price, p.stock, p.min_stock,
        p.location, p.image, p.description, p.barcode,
        c.id   AS category_id, c.name AS category_name
      FROM products p
      JOIN categories c ON p.category_id = c.id
      $where
      ORDER BY p.created_at DESC
    ";
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $products = $stmt->fetchAll();

    foreach ($products as &$p) {
        $sz = $pdo->prepare("SELECT id, size_name, stock FROM product_sizes WHERE product_id = ?");
        $sz->execute([$p['id']]);
        $p['sizes'] = $sz->fetchAll();

        $bt = $pdo->prepare("
            SELECT b.id, b.batch_number, b.manufactured_date, b.stock, b.created_at, b.product_size_id, ps.size_name
            FROM batches b
            JOIN product_sizes ps ON b.product_size_id = ps.id
            WHERE b.product_id = ?
        ");
        $bt->execute([$p['id']]);
        $p['batches'] = $bt->fetchAll();
    }

    echo json_encode($products);
    exit;
}

if ($method === 'POST') {
    $input = $_POST;
    if (empty($input)
        && strpos($_SERVER['CONTENT_TYPE'] ?? '', 'application/json') !== false
    ) {
        $input = json_decode(file_get_contents('php://input'), true) ?? [];
    }

    if (!empty($input['action']) && $input['action'] === 'create_batch') {
        if (empty($input['product_id']) || empty($input['product_size_id']) || empty($input['batch_number']) || empty($input['stock']) || empty($input['manufactured_date'])) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Product ID, Product Size ID, batch number, stock, and manufactured date are required']);
            exit;
        }

        try {
            $sizeStock = $pdo->prepare("
                SELECT stock 
                FROM product_sizes 
                WHERE id = ? AND product_id = ?
            ");
            $sizeStock->execute([$input['product_size_id'], $input['product_id']]);
            $currentSizeStock = $sizeStock->fetchColumn();
            if ($currentSizeStock === false) {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => 'Invalid product size']);
                exit;
            }

            $currentBatchStock = $pdo->prepare("
                SELECT SUM(stock) 
                FROM batches 
                WHERE product_size_id = ?
            ");
            $currentBatchStock->execute([$input['product_size_id']]);
            $totalBatchStock = $currentBatchStock->fetchColumn() ?: 0;

            $newBatchStock = (int)$input['stock'];
            $newTotalBatchStock = $totalBatchStock + $newBatchStock;

            if ($newTotalBatchStock > $currentSizeStock) {
                $updSizeStock = $pdo->prepare("
                    UPDATE product_sizes 
                    SET stock = ? 
                    WHERE id = ?
                ");
                $updSizeStock->execute([$newTotalBatchStock, $input['product_size_id']]);

                $totalStock = $pdo->prepare("
                    SELECT SUM(stock) 
                    FROM product_sizes 
                    WHERE product_id = ?
                ");
                $totalStock->execute([$input['product_id']]);
                $newProductStock = $totalStock->fetchColumn() ?: 0;

                $pdo->prepare("UPDATE products SET stock = ? WHERE id = ?")
                    ->execute([$newProductStock, $input['product_id']]);
            }

            // Ensure batch_number is unique
            $batchNumber = $input['batch_number'];
            if (!isBatchNumberUnique($pdo, $batchNumber)) {
                $batchNumber = generateBatchNumber($pdo, $input['product_id']);
            }

            $ins = $pdo->prepare("
                INSERT INTO batches (product_id, product_size_id, batch_number, manufactured_date, stock)
                VALUES (?, ?, ?, ?, ?)
            ");
            $ins->execute([
                $input['product_id'],
                $input['product_size_id'],
                $batchNumber,
                $input['manufactured_date'],
                $newBatchStock
            ]);

            echo json_encode(['success' => true]);
        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Failed to create batch: ' . $e->getMessage()]);
        }
        exit;
    }

    if (!empty($input['action']) && $input['action'] === 'update_batch') {
        if (empty($input['batch_id']) || empty($input['batch_number']) || empty($input['stock']) || empty($input['manufactured_date'])) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Batch ID, batch number, stock, and manufactured date are required']);
            exit;
        }

        try {
            $batch = $pdo->prepare("
                SELECT product_id, product_size_id, stock, batch_number 
                FROM batches 
                WHERE id = ?
            ");
            $batch->execute([$input['batch_id']]);
            $batchData = $batch->fetch();
            if (!$batchData) {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => 'Invalid batch ID']);
                exit;
            }

            // Check if the new batch_number is unique (excluding the current batch)
            $batchNumber = $input['batch_number'];
            if ($batchNumber !== $batchData['batch_number'] && !isBatchNumberUnique($pdo, $batchNumber)) {
                $batchNumber = generateBatchNumber($pdo, $batchData['product_id']);
            }

            $sizeStock = $pdo->prepare("
                SELECT stock 
                FROM product_sizes 
                WHERE id = ?
            ");
            $sizeStock->execute([$batchData['product_size_id']]);
            $currentSizeStock = $sizeStock->fetchColumn();

            $currentBatchStock = $pdo->prepare("
                SELECT SUM(stock) 
                FROM batches 
                WHERE product_size_id = ? AND id != ?
            ");
            $currentBatchStock->execute([$batchData['product_size_id'], $input['batch_id']]);
            $totalOtherBatchStock = $currentBatchStock->fetchColumn() ?: 0;

            $newBatchStock = (int)$input['stock'];
            $newTotalBatchStock = $totalOtherBatchStock + $newBatchStock;

            if ($newTotalBatchStock > $currentSizeStock) {
                $updSizeStock = $pdo->prepare("
                    UPDATE product_sizes 
                    SET stock = ? 
                    WHERE id = ?
                ");
                $updSizeStock->execute([$newTotalBatchStock, $batchData['product_size_id']]);

                $totalStock = $pdo->prepare("
                    SELECT SUM(stock) 
                    FROM product_sizes 
                    WHERE product_id = ?
                ");
                $totalStock->execute([$batchData['product_id']]);
                $newProductStock = $totalStock->fetchColumn() ?: 0;

                $pdo->prepare("UPDATE products SET stock = ? WHERE id = ?")
                    ->execute([$newProductStock, $batchData['product_id']]);
            }

            $upd = $pdo->prepare("
                UPDATE batches 
                SET batch_number = ?, manufactured_date = ?, stock = ?
                WHERE id = ?
            ");
            $upd->execute([
                $batchNumber,
                $input['manufactured_date'],
                $newBatchStock,
                $input['batch_id']
            ]);

            echo json_encode(['success' => true]);
        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Failed to update batch: ' . $e->getMessage()]);
        }
        exit;
    }

    if (!empty($input['action']) && $input['action'] === 'delete_batch') {
        if (empty($input['batch_id'])) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Batch ID required']);
            exit;
        }

        try {
            $batch = $pdo->prepare("
                SELECT product_id, product_size_id, stock 
                FROM batches 
                WHERE id = ?
            ");
            $batch->execute([$input['batch_id']]);
            $batchData = $batch->fetch();
            if (!$batchData) {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => 'Invalid batch ID']);
                exit;
            }

            $pdo->prepare("DELETE FROM batches WHERE id = ?")
                ->execute([$input['batch_id']]);

            $currentBatchStock = $pdo->prepare("
                SELECT SUM(stock) 
                FROM batches 
                WHERE product_size_id = ?
            ");
            $currentBatchStock->execute([$batchData['product_size_id']]);
            $totalBatchStock = $currentBatchStock->fetchColumn() ?: 0;

            $updSizeStock = $pdo->prepare("
                UPDATE product_sizes 
                SET stock = ? 
                WHERE id = ?
            ");
            $updSizeStock->execute([$totalBatchStock, $batchData['product_size_id']]);

            $totalStock = $pdo->prepare("
                SELECT SUM(stock) 
                FROM product_sizes 
                WHERE product_id = ?
            ");
            $totalStock->execute([$batchData['product_id']]);
            $newProductStock = $totalStock->fetchColumn() ?: 0;

            $pdo->prepare("UPDATE products SET stock = ? WHERE id = ?")
                ->execute([$newProductStock, $batchData['product_id']]);

            echo json_encode(['success' => true]);
        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Failed to delete batch: ' . $e->getMessage()]);
        }
        exit;
    }

    if (!empty($input['action']) && $input['action'] === 'delete') {
        if (empty($input['id'])) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Product ID required']);
            exit;
        }
        $pdo->prepare("UPDATE products SET deleted_at = NOW() WHERE id = ?")
            ->execute([$input['id']]);
        echo json_encode(['success' => true]);
        exit;
    }

    if (!empty($input['action']) && $input['action'] === 'update') {
        foreach (['id', 'name', 'category_id', 'price', 'selling_price', 'stock'] as $f) {
            if (!isset($input[$f])) {
                http_response_code(422);
                echo json_encode(['success' => false, "message" => "Field {$f} is required"]);
                exit;
            }
        }

        // Validate category_id
        $catStmt = $pdo->prepare("SELECT id FROM categories WHERE id = ?");
        $catStmt->execute([$input['category_id']]);
        if (!$catStmt->fetch()) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Invalid category ID']);
            exit;
        }

        try {
            $upd = $pdo->prepare("
              UPDATE products SET
                name           = :name,
                category_id    = :category_id,
                price          = :price,
                selling_price  = :selling_price,
                stock          = :stock,
                min_stock      = :min_stock,
                location       = :location,
                image          = :image,
                description    = :description,
                barcode        = :barcode
              WHERE id = :id
            ");
            $upd->execute([
                'id'             => $input['id'],
                'name'           => $input['name'],
                'category_id'    => $input['category_id'],
                'price'          => $input['price'],
                'selling_price'  => $input['selling_price'],
                'stock'          => $input['stock'],
                'min_stock'      => $input['min_stock']   ?? 5,
                'location'       => $input['location']    ?? null,
                'image'          => $input['image']       ?? null,
                'description'    => $input['description'] ?? null,
                'barcode'        => $input['barcode']     ?? null
            ]);

            $gen  = new BarcodeGeneratorPNG();
            $path = __DIR__ . '/../barcodes/' . $input['id'] . '.png';
            file_put_contents($path, $gen->getBarcode($input['id'], $gen::TYPE_CODE_128));
            $pdo->prepare("UPDATE products SET barcode = ? WHERE id = ?")
                ->execute(['barcodes/' . $input['id'] . '.png', $input['id']]);

            if (!empty($input['sizes']) && is_array($input['sizes'])) {
                saveSizes($pdo, $input['id'], $input['sizes']);
            }

            echo json_encode(['success' => true]);
        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Failed to update product: ' . $e->getMessage()]);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Failed to update product (non-DB error): ' . $e->getMessage()]);
        }
        exit;
    }

    foreach (['name', 'category_id', 'price', 'selling_price', 'stock'] as $f) {
        if (empty($input[$f])) {
            http_response_code(422);
            echo json_encode(['success' => false, "message" => "Field {$f} is required"]);
            exit;
        }
    }

    if (empty($input['batch_number']) || empty($input['manufactured_date'])) {
        http_response_code(422);
        echo json_encode(['success' => false, "message" => "Batch number and manufactured date are required"]);
        exit;
    }

    // Validate category_id
    $catStmt = $pdo->prepare("SELECT id FROM categories WHERE id = ?");
    $catStmt->execute([$input['category_id']]);
    if (!$catStmt->fetch()) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Invalid category ID']);
        exit;
    }

    try {
        $ins = $pdo->prepare("
          INSERT INTO products
            (name, category_id, price, selling_price, stock,
             min_stock, location, image, description, barcode)
          VALUES
            (:name, :category_id, :price, :selling_price, :stock,
             :min_stock, :location, :image, :description, NULL)
        ");
        $ins->execute([
            'name'           => $input['name'],
            'category_id'    => $input['category_id'],
            'price'          => $input['price'],
            'selling_price'  => $input['selling_price'],
            'stock'          => $input['stock'],
            'min_stock'      => $input['min_stock']   ?? 5,
            'location'       => $input['location']    ?? null,
            'image'          => $input['image']       ?? null,
            'description'    => $input['description'] ?? null
        ]);
        $newId = $pdo->lastInsertId();

        $gen  = new BarcodeGeneratorPNG();
        $path = __DIR__ . '/../barcodes/' . $newId . '.png';
        file_put_contents($path, $gen->getBarcode($newId, $gen::TYPE_CODE_128));
        $pdo->prepare("UPDATE products SET barcode = ? WHERE id = ?")
            ->execute(['barcodes/' . $newId . '.png', $newId]);

        if (!empty($input['sizes']) && is_array($input['sizes'])) {
            saveSizes($pdo, $newId, $input['sizes']);
        } else {
            saveSizes($pdo, $newId, [['size' => 'Default', 'stock' => $input['stock']]]);
        }

        if (!empty($input['sizes']) && is_array($input['sizes'])) {
            $sizeStmt = $pdo->prepare("
                SELECT id 
                FROM product_sizes 
                WHERE product_id = ? AND size_name = ?
            ");
            $sizeStmt->execute([$newId, $input['sizes'][0]['size']]);
            $productSizeId = $sizeStmt->fetchColumn();

            if ($productSizeId) {
                $batchNumber = $input['batch_number'] ?? generateBatchNumber($pdo, $newId);
                // Ensure batch_number is unique
                if (!isBatchNumberUnique($pdo, $batchNumber)) {
                    $batchNumber = generateBatchNumber($pdo, $newId);
                }
                $insBatch = $pdo->prepare("
                    INSERT INTO batches (product_id, product_size_id, batch_number, manufactured_date, stock)
                    VALUES (?, ?, ?, ?, ?)
                ");
                $insBatch->execute([
                    $newId,
                    $productSizeId,
                    $batchNumber,
                    $input['manufactured_date'],
                    $input['sizes'][0]['stock']
                ]);
            }
        }

        echo json_encode(['success' => true, 'id' => $newId]);
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Failed to create product: ' . $e->getMessage()]);
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Failed to create product (non-DB error): ' . $e->getMessage()]);
    }
    exit;
}

http_response_code(405);
echo json_encode(['success' => false, 'message' => 'Method Not Allowed']);
?>