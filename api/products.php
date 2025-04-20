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
    // Delete old sizes
    $pdo->prepare("DELETE FROM product_sizes WHERE product_id = ?")
        ->execute([$productId]);

    // Insert each new size
    $ins = $pdo->prepare("
        INSERT INTO product_sizes (product_id, size_name, stock)
        VALUES (?, ?, ?)
    ");
    foreach ($sizes as $s) {
        // expect $s = ['size' => string, 'stock' => int]
        $ins->execute([
            $productId,
            $s['size'],
            $s['stock']
        ]);
    }
}

if ($method === 'GET') {
    // Filters
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

    // Fetch products
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

    // Attach sizes
    foreach ($products as &$p) {
        $sz = $pdo->prepare("SELECT id, size_name, stock FROM product_sizes WHERE product_id = ?");
        $sz->execute([$p['id']]);
        $p['sizes'] = $sz->fetchAll();
    }

    echo json_encode($products);
    exit;
}

if ($method === 'POST') {
    // Parse JSON if needed
    $input = $_POST;
    if (empty($input)
        && strpos($_SERVER['CONTENT_TYPE'] ?? '', 'application/json') !== false
    ) {
        $input = json_decode(file_get_contents('php://input'), true) ?? [];
    }

    // DELETE action
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

    // UPDATE action
    if (!empty($input['action']) && $input['action'] === 'update') {
        // Validate
        foreach (['id','name','category_id','price','selling_price','stock'] as $f) {
            if (!isset($input[$f])) {
                http_response_code(422);
                echo json_encode(['success'=>false,"message"=>"Field {$f} is required"]);
                exit;
            }
        }

        // Update products row
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
            'barcode'        => $input['barcode']     ?? null,
        ]);

        // Regenerate barcode PNG
        $gen  = new BarcodeGeneratorPNG();
        $path = __DIR__.'/../barcodes/'.$input['id'].'.png';
        file_put_contents($path, $gen->getBarcode($input['id'], $gen::TYPE_CODE_128));
        $pdo->prepare("UPDATE products SET barcode = ? WHERE id = ?")
            ->execute(['barcodes/'.$input['id'].'.png', $input['id']]);

        // Save sizes array
        if (!empty($input['sizes']) && is_array($input['sizes'])) {
            saveSizes($pdo, $input['id'], $input['sizes']);
        }

        echo json_encode(['success'=>true]);
        exit;
    }

    // CREATE action
    // Validate
    foreach (['name','category_id','price','selling_price','stock'] as $f) {
        if (empty($input[$f])) {
            http_response_code(422);
            echo json_encode(['success'=>false,"message"=>"Field {$f} is required"]);
            exit;
        }
    }

    try {
        // Insert product
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

        // Generate barcode PNG
        $gen  = new BarcodeGeneratorPNG();
        $path = __DIR__.'/../barcodes/'.$newId.'.png';
        file_put_contents($path, $gen->getBarcode($newId, $gen::TYPE_CODE_128));
        $pdo->prepare("UPDATE products SET barcode = ? WHERE id = ?")
            ->execute(['barcodes/'.$newId.'.png', $newId]);

        // Insert sizes array (or default fallback)
        if (!empty($input['sizes']) && is_array($input['sizes'])) {
            saveSizes($pdo, $newId, $input['sizes']);
        } else {
            saveSizes($pdo, $newId, [['size'=>'Default','stock'=>$input['stock']]]);
        }

        echo json_encode(['success'=>true,'id'=>$newId]);
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(['success'=>false,'message'=>$e->getMessage()]);
    }
    exit;
}

// Unsupported method
http_response_code(405);
echo json_encode(['success'=>false,'message'=>'Method Not Allowed']);
