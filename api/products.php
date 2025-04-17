<?php
// api/products.php
require_once __DIR__ . '/../db.php';
header('Content-Type: application/json');

$method = $_SERVER['REQUEST_METHOD'];

// ── GET: list products (with optional search) ─────────────────────────────────
if ($method === 'GET') {
    $search = $_GET['search'] ?? '';

    $sql = "
      SELECT 
        p.id,
        p.name,
        p.price,
        p.stock,
        p.min_stock,
        p.location,
        p.image,
        p.description,
        p.barcode,
        p.discount,
        c.id   AS category_id,
        c.name AS category_name
      FROM products p
      JOIN categories c ON p.category_id = c.id
      WHERE p.deleted_at IS NULL
        AND p.name LIKE :search
      ORDER BY p.created_at DESC
    ";
    $stmt = $pdo->prepare($sql);
    $stmt->execute(['search' => "%$search%"]);
    $products = $stmt->fetchAll();

    // attach sizes
    foreach ($products as &$p) {
        $sizeStmt = $pdo->prepare("SELECT id, size, stock FROM product_sizes WHERE product_id = ?");
        $sizeStmt->execute([$p['id']]);
        $p['sizes'] = $sizeStmt->fetchAll();
    }

    echo json_encode($products);
    exit;
}

// ── POST: add new product or delete existing ───────────────────────────────────
if ($method === 'POST') {
    // handle JSON or form-data
    $input = $_POST;
    if (empty($input) && strpos($_SERVER['CONTENT_TYPE'], 'application/json') !== false) {
        $raw = file_get_contents('php://input');
        $input = json_decode($raw, true);
    }

    // ─ delete?
    if (isset($input['action']) && $input['action'] === 'delete') {
        if (empty($input['id'])) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Product ID required']);
            exit;
        }
        $stmt = $pdo->prepare("UPDATE products SET deleted_at = NOW() WHERE id = ?");
        $stmt->execute([$input['id']]);
        echo json_encode(['success' => true]);
        exit;
    }

    // ─ add new product ───────────────────────────────────────────────────────────
    $required = ['name','category_id','price','stock'];
    foreach ($required as $f) {
        if (empty($input[$f])) {
            http_response_code(422);
            echo json_encode(['success' => false, 'message' => "Field {$f} is required."]);
            exit;
        }
    }

    try {
        // insert into products
        $ins = $pdo->prepare("
            INSERT INTO products 
              (name, category_id, price, stock, min_stock, location, image, description, barcode, discount)
            VALUES 
              (:name, :category_id, :price, :stock, :min_stock, :location, :image, :description, :barcode, :discount)
        ");
        $ins->execute([
            'name'         => $input['name'],
            'category_id'  => $input['category_id'],
            'price'        => $input['price'],
            'stock'        => $input['stock'],
            'min_stock'    => $input['min_stock']    ?? 5,
            'location'     => $input['location']     ?? null,
            'image'        => $input['image']        ?? null,
            'description'  => $input['description']  ?? null,
            'barcode'      => $input['barcode']      ?? null,
            'discount'     => $input['discount']     ?? 0,
        ]);

        $productId = $pdo->lastInsertId();

        // for now seed a default size record
        $sizeIns = $pdo->prepare("
            INSERT INTO product_sizes (product_id, size, stock) 
            VALUES (:pid, :size, :stock)
        ");
        $sizeIns->execute([
            'pid'   => $productId,
            'size'  => 'Default',
            'stock' => $input['stock']
        ]);

        echo json_encode(['success' => true, 'id' => $productId]);
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
    exit;
}

// ── unsupported methods ────────────────────────────────────────────────────────
http_response_code(405);
echo json_encode(['success' => false, 'message' => 'Method Not Allowed']);
