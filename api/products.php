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
    try {
        $pdo->prepare("DELETE FROM product_sizes WHERE product_id = ?")
            ->execute([$productId]);

        $ins = $pdo->prepare("
            INSERT INTO product_sizes (product_id, size_name, stock)
            VALUES (?, ?, ?)
        ");
        foreach ($sizes as $s) {
            if (!isset($s['size']) || !isset($s['stock'])) {
                throw new Exception("Invalid size format: " . json_encode($s));
            }
            $ins->execute([
                $productId,
                $s['size'],
                (int)$s['stock']
            ]);
        }
    } catch (Exception $e) {
        throw new Exception("Failed to save sizes: " . $e->getMessage());
    }
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

    // Log the incoming payload for debugging
    error_log("Received payload: " . json_encode($input));

    if (!empty($input['action']) && $input['action'] === 'delete') {
        if (empty($input['id'])) {
            http_response_code(400);
            echo json_encode(['success'=>false,'message'=>'Product ID required']);
            exit;
        }
        $pdo->prepare("UPDATE products SET deleted_at = NOW() WHERE id = ?")
            ->execute([$input['id']]);
        echo json_encode(['success'=>true]);
        exit;
    }

    if (!empty($input['action']) && $input['action'] === 'update') {
        foreach (['id','name','category_id','price','selling_price','stock'] as $f) {
            if (!isset($input[$f])) {
                http_response_code(422);
                echo json_encode(['success'=>false,"message"=>"Field {$f} is required"]);
                exit;
            }
        }

        try {
            $pdo->beginTransaction();

            // Validate numeric fields
            $input['id'] = (int)$input['id'];
            $input['category_id'] = (int)$input['category_id'];
            $input['stock'] = (int)$input['stock'];
            $input['min_stock'] = isset($input['min_stock']) ? (int)$input['min_stock'] : 5;
            $input['price'] = (float)$input['price'];
            $input['selling_price'] = (float)$input['selling_price'];

            // Ensure description and location are strings or null
            $input['description'] = isset($input['description']) && $input['description'] !== '' ? $input['description'] : null;
            $input['location'] = isset($input['location']) && $input['location'] !== '' ? $input['location'] : null;

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
                'min_stock'      => $input['min_stock'],
                'location'       => $input['location'],
                'image'          => $input['image'] ?? null,
                'description'    => $input['description'],
                'barcode'        => $input['barcode'] ?? null,
            ]);

            $gen  = new BarcodeGeneratorPNG();
            $path = __DIR__.'/../barcodes/'.$input['id'].'.png';
            file_put_contents($path, $gen->getBarcode($input['id'], $gen::TYPE_CODE_128));
            $pdo->prepare("UPDATE products SET barcode = ? WHERE id = ?")
                ->execute(['barcodes/'.$input['id'].'.png', $input['id']]);

            if (!empty($input['sizes']) && is_array($input['sizes'])) {
                saveSizes($pdo, $input['id'], $input['sizes']);
            }

            $pdo->commit();
            echo json_encode(['success'=>true]);
        } catch (Exception $e) {
            $pdo->rollBack();
            http_response_code(500);
            echo json_encode(['success'=>false,'message'=>'Failed to update product: ' . $e->getMessage()]);
        }
        exit;
    }

    // CREATE action
    foreach (['name','category_id','price','selling_price','stock'] as $f) {
        if (empty($input[$f])) {
            http_response_code(422);
            echo json_encode(['success'=>false,"message"=>"Field {$f} is required"]);
            exit;
        }
    }

    try {
        $pdo->beginTransaction();

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
        $path = __DIR__.'/../barcodes/'.$newId.'.png';
        file_put_contents($path, $gen->getBarcode($newId, $gen::TYPE_CODE_128));
        $pdo->prepare("UPDATE products SET barcode = ? WHERE id = ?")
            ->execute(['barcodes/'.$newId.'.png', $newId]);

        if (!empty($input['sizes']) && is_array($input['sizes'])) {
            saveSizes($pdo, $newId, $input['sizes']);
        } else {
            saveSizes($pdo, $newId, [['size'=>'Default','stock'=>$input['stock']]]);
        }

        // Create initial batch if provided
        if (isset($input['initial_batch_size']) && isset($input['batch_number']) && isset($input['manufactured_date']) && isset($input['initial_batch_stock'])) {
            $sizeStmt = $pdo->prepare("
                SELECT id 
                FROM product_sizes 
                WHERE product_id = ? AND size_name = ?
            ");
            $sizeStmt->execute([$newId, $input['initial_batch_size']]);
            $productSizeId = $sizeStmt->fetchColumn();

            if (!$productSizeId) {
                throw new Exception("Size not found: " . $input['initial_batch_size']);
            }

            $batchNumber = $input['batch_number'];
            if (!isBatchNumberUnique($pdo, $batchNumber)) {
                $batchNumber = generateBatchNumber($pdo, $newId);
            }

            $batchStmt = $pdo->prepare("
                INSERT INTO batches (product_id, product_size_id, batch_number, manufactured_date, stock)
                VALUES (?, ?, ?, ?, ?)
            ");
            $batchStmt->execute([
                $newId,
                $productSizeId,
                $batchNumber,
                $input['manufactured_date'],
                $input['initial_batch_stock']
            ]);
        }

        $pdo->commit();
        echo json_encode(['success'=>true,'id'=>$newId]);
    } catch (Exception $e) {
        $pdo->rollBack();
        http_response_code(500);
        echo json_encode(['success'=>false,'message'=>$e->getMessage()]);
    }
    exit;
}

http_response_code(405);
echo json_encode(['success'=>false,'message'=>'Method Not Allowed']);