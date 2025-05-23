<?php
// api/products.php

require_once __DIR__ . '/../db.php';
require_once __DIR__ . '/../vendor/autoload.php';

use Picqer\Barcode\BarcodeGeneratorPNG;

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
    // Pagination parameters (optional)
    $page        = isset($_GET['page']) ? max(1, intval($_GET['page'])) : null;
    $limit       = isset($_GET['limit']) ? max(1, intval($_GET['limit'])) : null;

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

    // Optional pagination
    $isPaginated = ($page !== null && $limit !== null);
    $totalCount = 0;
    $totalPages = 0;
    
    // Get total count for pagination if needed
    if ($isPaginated) {
        $countSql = "
          SELECT COUNT(*) as total
          FROM products p
          JOIN categories c ON p.category_id = c.id
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
      SELECT
        p.id, p.name, p.price, p.selling_price, p.stock, p.min_stock,
        p.location, p.image, p.description, p.barcode,
        c.id   AS category_id, c.name AS category_name
      FROM products p
      JOIN categories c ON p.category_id = c.id
      $where
      ORDER BY p.created_at DESC
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
    
    // Return with pagination metadata if paginated request, otherwise return products array directly
    if ($isPaginated) {
        echo json_encode([
            'products' => $products,
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
        echo json_encode($products);
    }
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

    // Log the incoming payload for debugging
    error_log("Received payload: " . json_encode($input));

    if (!empty($input['action']) && $input['action'] === 'delete') {
        if (empty($input['id'])) {
            http_response_code(400);
            echo json_encode(['success'=>false,'message'=>'Product ID required']);
            exit;
        }

        try {
            // Get product name before deleting
            $stmt = $pdo->prepare("SELECT id, name FROM products WHERE id = ?");
            $stmt->execute([$input['id']]);
            $product = $stmt->fetch();
            
            if ($product) {
                $pdo->prepare("UPDATE products SET deleted_at = NOW() WHERE id = ?")
                    ->execute([$input['id']]);
                
                // Create notification for product deletion
                $notificationTitle = "Product Deleted";
                $notificationMessage = "Product '{$product['name']}' has been deleted";
                createNotification($pdo, 'other', $notificationTitle, $notificationMessage, 'all');
                
                echo json_encode(['success'=>true]);
            } else {
                throw new Exception("Product not found");
            }
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['success'=>false, 'message'=>$e->getMessage()]);
        }
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

            // Process image upload
            $imagePath = isset($input['current_image']) ? $input['current_image'] : null;
            
            if (!empty($_FILES['product_image']['name'])) {
                $uploadDir = __DIR__ . '/../uploads/products/';
                
                // Create directory if it doesn't exist
                if (!is_dir($uploadDir)) {
                    if (!mkdir($uploadDir, 0755, true)) {
                        throw new Exception("Failed to create image upload directory");
                    }
                }
                
                // Generate unique filename
                $extension = pathinfo($_FILES['product_image']['name'], PATHINFO_EXTENSION);
                $fileName = 'product_' . $input['id'] . '_' . time() . '.' . $extension;
                $uploadPath = $uploadDir . $fileName;
                $relativePath = 'uploads/products/' . $fileName;
                
                // Try to upload the file
                if (move_uploaded_file($_FILES['product_image']['tmp_name'], $uploadPath)) {
                    $imagePath = $relativePath;
                } else {
                    throw new Exception("Failed to upload image");
                }
            }

            // First check if the product exists and get its current name
            $checkStmt = $pdo->prepare("SELECT name FROM products WHERE id = ?");
            $checkStmt->execute([$input['id']]);
            $currentProduct = $checkStmt->fetch();
            if (!$currentProduct) {
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
                description    = :description,
                image          = :image
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
                'description'    => $input['description'],
                'image'          => $imagePath
            ]);

            // Create notification for product update
            $notificationTitle = "Product Updated";
            $notificationMessage = "Product '{$currentProduct['name']}' has been updated";
            createNotification($pdo, 'stock', $notificationTitle, $notificationMessage, 'all');

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
            if (!empty($input['sizes_json'])) {
                $sizes = json_decode($input['sizes_json'], true);
                if (is_array($sizes)) {
                    saveSizes($pdo, $input['id'], $sizes);
                }
            } else if (!empty($input['sizes']) && is_array($input['sizes'])) {
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

        // Process image upload
        $imagePath = null;
        if (!empty($_FILES['product_image']['name'])) {
            $uploadDir = __DIR__ . '/../uploads/products/';
            
            // Create directory if it doesn't exist
            if (!is_dir($uploadDir)) {
                if (!mkdir($uploadDir, 0755, true)) {
                    throw new Exception("Failed to create image upload directory");
                }
            }
            
            // Generate a temporary filename - we'll update it after we get the product ID
            $tempFileName = 'product_temp_' . time() . '.' . pathinfo($_FILES['product_image']['name'], PATHINFO_EXTENSION);
            $uploadPath = $uploadDir . $tempFileName;
            $tempRelativePath = 'uploads/products/' . $tempFileName;
            
            // Try to upload the file
            if (move_uploaded_file($_FILES['product_image']['tmp_name'], $uploadPath)) {
                $imagePath = $tempRelativePath;
            } else {
                throw new Exception("Failed to upload image");
            }
        }

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
            'image'          => $imagePath,
            'description'    => $input['description'] ?? null
        ]);
        $newId = $pdo->lastInsertId();

        // Rename the image file with the product ID if we uploaded one
        if ($imagePath && strpos($imagePath, 'product_temp_') !== false) {
            $newFileName = 'product_' . $newId . '_' . time() . '.' . pathinfo($imagePath, PATHINFO_EXTENSION);
            $newUploadPath = __DIR__ . '/../uploads/products/' . $newFileName;
            $newRelativePath = 'uploads/products/' . $newFileName;
            
            if (rename(__DIR__ . '/../' . $imagePath, $newUploadPath)) {
                $pdo->prepare("UPDATE products SET image = ? WHERE id = ?")
                    ->execute([$newRelativePath, $newId]);
                $imagePath = $newRelativePath;
            }
        }

        // Create notification for new product
        $notificationTitle = "New Product Added";
        $notificationMessage = "New product '{$input['name']}' has been added to inventory";
        createNotification($pdo, 'stock', $notificationTitle, $notificationMessage, 'all');

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

        // Process sizes
        if (!empty($input['sizes_json'])) {
            $sizes = json_decode($input['sizes_json'], true);
            if (is_array($sizes)) {
                saveSizes($pdo, $newId, $sizes);
            }
        } else if (!empty($input['sizes']) && is_array($input['sizes'])) {
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