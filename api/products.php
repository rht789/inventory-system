<?php
// api/products.php
require_once __DIR__ . '/../db.php';
header('Content-Type: application/json');

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    //── Read filters from query string ───────────────────────────────────────────
    $search      = $_GET['search']      ?? '';
    $stockFilter = $_GET['stock_filter']?? '';
    $categoryId  = $_GET['category_id'] ?? '';

    //── Build WHERE clauses dynamically ────────────────────────────────────────
    $conds = ["p.deleted_at IS NULL"];
    $params = [];

    if ($search !== '') {
        $conds[] = "p.name LIKE :search";
        $params['search'] = "%{$search}%";
    }
    if (is_numeric($categoryId)) {
        $conds[] = "p.category_id = :category_id";
        $params['category_id'] = $categoryId;
    }
    switch ($stockFilter) {
        case 'in_stock':
            $conds[] = "p.stock > 0";
            break;
        case 'low_stock':
            $conds[] = "p.stock > 0 AND p.stock <= p.min_stock";
            break;
        case 'out_of_stock':
            $conds[] = "p.stock = 0";
            break;
    }

    $where = count($conds)
        ? 'WHERE ' . implode(' AND ', $conds)
        : '';

    //── Fetch products with their category names ────────────────────────────────
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
        {$where}
        ORDER BY p.created_at DESC
    ";
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $products = $stmt->fetchAll();

    //── Attach size breakdown to each product ─────────────────────────────────
    foreach ($products as &$p) {
        $sizeStmt = $pdo->prepare(
            "SELECT id, size, stock FROM product_sizes WHERE product_id = ?"
        );
        $sizeStmt->execute([$p['id']]);
        $p['sizes'] = $sizeStmt->fetchAll();
    }

    echo json_encode($products);
    exit;
}

if ($method === 'POST') {
    //── Support both form-data and raw JSON bodies ───────────────────────────────
    $input = $_POST;
    if (empty($input)
        && strpos($_SERVER['CONTENT_TYPE'] ?? '', 'application/json') !== false
    ) {
        $input = json_decode(file_get_contents('php://input'), true) ?? [];
    }

    //── DELETE action? ─────────────────────────────────────────────────────────
    if (!empty($input['action']) && $input['action'] === 'delete') {
        if (empty($input['id'])) {
            http_response_code(400);
            echo json_encode(['success'=>false, 'message'=>'Product ID required']);
            exit;
        }
        $del = $pdo->prepare(
            "UPDATE products SET deleted_at = NOW() WHERE id = ?"
        );
        $del->execute([$input['id']]);
        echo json_encode(['success'=>true]);
        exit;
    }

    //── UPDATE action? ─────────────────────────────────────────────────────────
    if (!empty($input['action']) && $input['action'] === 'update') {
        $required = ['id','name','category_id','price','stock'];
        foreach ($required as $f) {
            if (empty($input[$f])) {
                http_response_code(422);
                echo json_encode([
                    'success'=>false,
                    'message'=>"Field {$f} is required for update"
                ]);
                exit;
            }
        }
        $upd = $pdo->prepare("
            UPDATE products SET
                name        = :name,
                category_id = :category_id,
                price       = :price,
                stock       = :stock,
                min_stock   = :min_stock,
                location    = :location,
                image       = :image,
                description = :description,
                barcode     = :barcode,
                discount    = :discount
            WHERE id = :id
        ");
        $upd->execute([
            'id'          => $input['id'],
            'name'        => $input['name'],
            'category_id' => $input['category_id'],
            'price'       => $input['price'],
            'stock'       => $input['stock'],
            'min_stock'   => $input['min_stock']   ?? 5,
            'location'    => $input['location']    ?? null,
            'image'       => $input['image']       ?? null,
            'description' => $input['description'] ?? null,
            'barcode'     => $input['barcode']     ?? null,
            'discount'    => $input['discount']    ?? 0,
        ]);
        echo json_encode(['success'=>true]);
        exit;
    }

    //── ADD new product ─────────────────────────────────────────────────────────
    $required = ['name','category_id','price','stock'];
    foreach ($required as $f) {
        if (empty($input[$f])) {
            http_response_code(422);
            echo json_encode([
                'success'=>false,
                'message'=>"Field {$f} is required."
            ]);
            exit;
        }
    }

    try {
        $ins = $pdo->prepare("
            INSERT INTO products
                (name, category_id, price, stock, min_stock,
                 location, image, description, barcode, discount)
            VALUES
                (:name, :category_id, :price, :stock, :min_stock,
                 :location, :image, :description, :barcode, :discount)
        ");
        $ins->execute([
            'name'        => $input['name'],
            'category_id' => $input['category_id'],
            'price'       => $input['price'],
            'stock'       => $input['stock'],
            'min_stock'   => $input['min_stock']   ?? 5,
            'location'    => $input['location']    ?? null,
            'image'       => $input['image']       ?? null,
            'description' => $input['description'] ?? null,
            'barcode'     => $input['barcode']     ?? null,
            'discount'    => $input['discount']    ?? 0,
        ]);

        $newId = $pdo->lastInsertId();
        // Seed a default size record so your frontend badges render
        $sizeIns = $pdo->prepare("
            INSERT INTO product_sizes (product_id, size, stock)
            VALUES (:pid, 'Default', :stock)
        ");
        $sizeIns->execute([
            'pid'   => $newId,
            'stock' => $input['stock']
        ]);

        echo json_encode(['success'=>true, 'id'=>$newId]);
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode([
            'success'=>false,
            'message'=>$e->getMessage()
        ]);
    }
    exit;
}

//── Unsupported methods ───────────────────────────────────────────────────────
http_response_code(405);
echo json_encode(['success'=>false, 'message'=>'Method Not Allowed']);
