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
        // Log the sizes being passed
        error_log("Saving sizes for product $productId: " . json_encode($sizes));
        
        // First, get existing sizes that are referenced in batches (which can't be deleted)
        $usedSizesStmt = $pdo->prepare("
            SELECT DISTINCT ps.id, ps.size_name 
            FROM product_sizes ps
            JOIN batches b ON ps.id = b.product_size_id
            WHERE ps.product_id = ?
        ");
        $usedSizesStmt->execute([$productId]);
        $usedSizes = $usedSizesStmt->fetchAll();
        
        // Create a lookup map of existing sizes by name
        $existingSizesStmt = $pdo->prepare("SELECT id, size_name FROM product_sizes WHERE product_id = ?");
        $existingSizesStmt->execute([$productId]);
        $existingSizes = [];
        while($row = $existingSizesStmt->fetch()) {
            $existingSizes[$row['size_name']] = $row['id'];
        }
        
        // Track which sizes should be kept (used in batches or in new list)
        $sizesToKeep = [];
        foreach($usedSizes as $usedSize) {
            $sizesToKeep[$usedSize['size_name']] = true;
        }
        
        // Process new sizes
        $updateStmt = $pdo->prepare("UPDATE product_sizes SET stock = ? WHERE id = ?");
        $insertStmt = $pdo->prepare("INSERT INTO product_sizes (product_id, size_name, stock) VALUES (?, ?, ?)");
        
        // If there are used sizes (in batches), make sure they're included in the input sizes
        $usedSizeNames = [];
        foreach($usedSizes as $usedSize) {
            $usedSizeNames[] = $usedSize['size_name'];
            
            // Check if this used size exists in the input sizes
            $sizeExists = false;
            foreach($sizes as $inputSize) {
                if (isset($inputSize['size']) && trim($inputSize['size']) === $usedSize['size_name']) {
                    $sizeExists = true;
                    break;
                }
            }
            
            // If a size used in batches doesn't exist in input, add it with 0 stock
            // This prevents foreign key constraint violations
            if (!$sizeExists) {
                $sizes[] = ['size' => $usedSize['size_name'], 'stock' => 0];
                error_log("Adding missing size used in batches: " . $usedSize['size_name']);
            }
        }
        
        $totalStock = 0;
        foreach ($sizes as $size) {
            if (!isset($size['size']) || !isset($size['stock'])) {
                throw new Exception("Invalid size format: " . json_encode($size));
            }
            
            $sizeName = trim($size['size']);
            $stock = max(0, (int)$size['stock']);
            
            if (empty($sizeName)) {
                continue; // Skip empty sizes
            }
            
            // Mark this size to be kept
            $sizesToKeep[$sizeName] = true;
            
            // Update or insert
            if (isset($existingSizes[$sizeName])) {
                // Update existing size
                $updateStmt->execute([$stock, $existingSizes[$sizeName]]);
            } else {
                // Insert new size
                $insertStmt->execute([$productId, $sizeName, $stock]);
            }
            
            $totalStock += $stock;
        }
        
        // Delete sizes that aren't used in batches and aren't in the new list
        $deleteList = [];
        foreach($existingSizes as $sizeName => $sizeId) {
            if (!isset($sizesToKeep[$sizeName])) {
                $deleteList[] = $sizeId;
            }
        }
        
        if (!empty($deleteList)) {
            try {
                $placeholders = implode(',', array_fill(0, count($deleteList), '?'));
                $deleteStmt = $pdo->prepare("DELETE FROM product_sizes WHERE id IN ($placeholders)");
                $deleteStmt->execute($deleteList);
            } catch (Exception $e) {
                error_log("Warning: Failed to delete unused sizes: " . $e->getMessage());
                // Continue execution even if deletion fails - we'll just have unused sizes in the database
            }
        }
        
        // Update the total stock in the products table
        try {
            $pdo->prepare("UPDATE products SET stock = ? WHERE id = ?")
                ->execute([$totalStock, $productId]);
        } catch (Exception $e) {
            error_log("Error updating product total stock: " . $e->getMessage());
            throw new Exception("Failed to update product total stock: " . $e->getMessage());
        }
            
        return true;
    } catch (Exception $e) {
        // Log the error
        error_log("Error saving sizes: " . $e->getMessage() . "\n" . $e->getTraceAsString());
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

    // Add low stock endpoint for dashboard
    if (isset($_GET['lowStock']) && $_GET['lowStock'] === 'true') {
        try {
            $sql = "
                SELECT 
                    p.id,
                    p.name,
                    c.name as category,
                    p.stock,
                    p.min_stock
                FROM products p
                LEFT JOIN categories c ON p.category_id = c.id
                WHERE p.stock < p.min_stock AND p.min_stock > 0
                ORDER BY (p.min_stock - p.stock) DESC
                LIMIT 10
            ";
            
            $stmt = $pdo->prepare($sql);
            $stmt->execute();
            $lowStockProducts = $stmt->fetchAll();
            
            echo json_encode(['success' => true, 'products' => $lowStockProducts]);
            exit;
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => 'Error fetching low stock products: ' . $e->getMessage()]);
            exit;
        }
    }

    echo json_encode($products);
    exit;
}

if ($method === 'POST') {
    $input = $_POST;
    
    // Try to get JSON input if no POST data
    if (empty($input)) {
        $contentType = isset($_SERVER["CONTENT_TYPE"]) ? trim($_SERVER["CONTENT_TYPE"]) : '';
        
        // Check if request contains JSON
        if (strpos($contentType, 'application/json') !== false) {
            $json_input = file_get_contents('php://input');
            $input = json_decode($json_input, true) ?? [];
        }
    }

    // Handle file upload for image
    $imageFileName = null;
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $uploadDir = __DIR__ . '/../uploads/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }
        $ext = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
        $imageFileName = uniqid('prod_', true) . '.' . $ext;
        $targetPath = $uploadDir . $imageFileName;
        if (!move_uploaded_file($_FILES['image']['tmp_name'], $targetPath)) {
            http_response_code(500);
            echo json_encode(['success'=>false,'message'=>'Failed to upload image']);
            exit;
        }
    }

    // If sizes is a JSON string, decode it
    if (isset($input['sizes']) && is_string($input['sizes'])) {
        $input['sizes'] = json_decode($input['sizes'], true);
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
            // Log detailed debugging information
            error_log("Update product - Input data: " . json_encode($input));
            
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

            // First check if the product exists
            $checkStmt = $pdo->prepare("SELECT id FROM products WHERE id = ?");
            $checkStmt->execute([$input['id']]);
            if (!$checkStmt->fetch()) {
                throw new Exception("Product with ID {$input['id']} not found");
            }

            // Update basic product information
            $upd = $pdo->prepare("
              UPDATE products SET
                name           = :name,
                category_id    = :category_id,
                price          = :price,
                selling_price  = :selling_price,
                stock          = :stock,
                min_stock      = :min_stock,
                location       = :location,
                description    = :description
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
                'description'    => $input['description']
            ]);

            // Handle barcode separately - only create if it doesn't exist
            $barcodeCheck = $pdo->prepare("SELECT barcode FROM products WHERE id = ?");
            $barcodeCheck->execute([$input['id']]);
            $barcodeData = $barcodeCheck->fetch();
            
            if (empty($barcodeData['barcode'])) {
                $gen = new BarcodeGeneratorPNG();
                $barcodePath = 'barcodes/' . $input['id'] . '.png';
                $fullPath = __DIR__ . '/../' . $barcodePath;
                
                // Make sure the barcodes directory exists
                $barcodeDir = __DIR__ . '/../barcodes';
                if (!is_dir($barcodeDir)) {
                    if (!mkdir($barcodeDir, 0755, true)) {
                        throw new Exception("Failed to create barcode directory");
                    }
                }
                
                // Try to write the barcode file
                if (!file_put_contents($fullPath, $gen->getBarcode($input['id'], $gen::TYPE_CODE_128))) {
                    throw new Exception("Failed to write barcode file");
                }
                
                $barcodeUpdate = $pdo->prepare("UPDATE products SET barcode = ? WHERE id = ?");
                $barcodeUpdate->execute([$barcodePath, $input['id']]);
            }

            // Update product sizes
            if (!empty($input['sizes']) && is_array($input['sizes'])) {
                saveSizes($pdo, $input['id'], $input['sizes']);
            } else {
                // If no sizes provided, ensure there's at least one default size
                saveSizes($pdo, $input['id'], [['size' => 'Default', 'stock' => $input['stock']]]);
            }

            $pdo->commit();
            echo json_encode(['success' => true]);
        } catch (Exception $e) {
            $pdo->rollBack();
            error_log("Update product error: " . $e->getMessage() . "\n" . $e->getTraceAsString());
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Failed to update product: ' . $e->getMessage()]);
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
            'image'          => $imageFileName ?? ($input['image'] ?? null),
            'description'    => $input['description'] ?? null
        ]);
        $newId = $pdo->lastInsertId();

        $gen  = new BarcodeGeneratorPNG();
        $barcodePath = 'barcodes/' . $newId . '.png';
        $fullPath = __DIR__ . '/../' . $barcodePath;
        
        // Make sure the barcodes directory exists
        $barcodeDir = __DIR__ . '/../barcodes';
        if (!is_dir($barcodeDir)) {
            if (!mkdir($barcodeDir, 0755, true)) {
                throw new Exception("Failed to create barcode directory");
            }
        }
        
        // Try to write the barcode file
        if (!file_put_contents($fullPath, $gen->getBarcode($newId, $gen::TYPE_CODE_128))) {
            throw new Exception("Failed to write barcode file");
        }
        
        $pdo->prepare("UPDATE products SET barcode = ? WHERE id = ?")
            ->execute([$barcodePath, $newId]);

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