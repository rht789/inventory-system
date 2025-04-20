<?php
// api/products.php
require_once __DIR__ . '/../db.php';
require_once __DIR__ . '/../vendor/autoload.php';

use Picqer\Barcode\BarcodeGeneratorPNG;

header('Content-Type: application/json');
$method = $_SERVER['REQUEST_METHOD'];

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
        p.id,
        p.name,
        p.price,
        p.selling_price,
        p.stock,
        p.min_stock,
        p.location,
        p.image,
        p.description,
        p.barcode,
        c.id   AS category_id,
        c.name AS category_name
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

    // DELETE
    if (!empty($input['action']) && $input['action']==='delete') {
        if (empty($input['id'])) {
            http_response_code(400);
            die(json_encode(['success'=>false,'message'=>'ID required']));
        }
        $pdo->prepare("UPDATE products SET deleted_at=NOW() WHERE id=?")
            ->execute([$input['id']]);
        echo json_encode(['success'=>true]);
        exit;
    }

    // UPDATE
    if (!empty($input['action']) && $input['action']==='update') {
        $req = ['id','name','category_id','price','selling_price','stock'];
        foreach ($req as $f) {
            if (!isset($input[$f])) {
                http_response_code(422);
                die(json_encode(['success'=>false,"message"=>"{$f} required"]));
            }
        }
        $upd = $pdo->prepare("
          UPDATE products SET
            name= :name,category_id=:category_id,
            price=:price,selling_price=:selling_price,
            stock=:stock,min_stock=:min_stock,
            location=:location,image=:image,
            description=:description,barcode=:barcode
          WHERE id=:id
        ");
        $upd->execute([
            'id'            =>$input['id'],
            'name'          =>$input['name'],
            'category_id'   =>$input['category_id'],
            'price'         =>$input['price'],
            'selling_price' =>$input['selling_price'],
            'stock'         =>$input['stock'],
            'min_stock'     =>$input['min_stock']   ?? 5,
            'location'      =>$input['location']    ?? null,
            'image'         =>$input['image']       ?? null,
            'description'   =>$input['description'] ?? null,
            'barcode'       =>$input['barcode']     ?? null,
        ]);
        // regenerate barcode PNG
        $gen = new BarcodeGeneratorPNG();
        $path = __DIR__.'/../barcodes/'.$input['id'].'.png';
        file_put_contents($path, $gen->getBarcode($input['id'], $gen::TYPE_CODE_128));
        $pdo->prepare("UPDATE products SET barcode=? WHERE id=?")
            ->execute(['barcodes/'.$input['id'].'.png',$input['id']]);
        echo json_encode(['success'=>true]);
        exit;
    }

    // CREATE
    $req = ['name','category_id','price','selling_price','stock'];
    foreach ($req as $f) {
        if (empty($input[$f])) {
            http_response_code(422);
            die(json_encode(['success'=>false,"message"=>"{$f} required"]));
        }
    }

    try {
        $ins = $pdo->prepare("
          INSERT INTO products
            (name,category_id,price,selling_price,stock,
             min_stock,location,image,description,barcode)
          VALUES
            (:name,:category_id,:price,:selling_price,:stock,
             :min_stock,:location,:image,:description,:barcode)
        ");
        $ins->execute([
            'name'          =>$input['name'],
            'category_id'   =>$input['category_id'],
            'price'         =>$input['price'],
            'selling_price' =>$input['selling_price'],
            'stock'         =>$input['stock'],
            'min_stock'     =>$input['min_stock']   ?? 5,
            'location'      =>$input['location']    ?? null,
            'image'         =>$input['image']       ?? null,
            'description'   =>$input['description'] ?? null,
            'barcode'       =>null
        ]);
        $id = $pdo->lastInsertId();

        // generate barcode image
        $gen = new BarcodeGeneratorPNG();
        $path = __DIR__.'/../barcodes/'.$id.'.png';
        file_put_contents($path, $gen->getBarcode($id, $gen::TYPE_CODE_128));
        $pdo->prepare("UPDATE products SET barcode=? WHERE id=?")
            ->execute(['barcodes/'.$id.'.png',$id]);

        // insert default size to keep UI happy
        $pdo->prepare("
          INSERT INTO product_sizes(product_id,size_name,stock)
          VALUES(?, 'Default', ?)
        ")->execute([$id,$input['stock']]);

        echo json_encode(['success'=>true,'id'=>$id]);
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(['success'=>false,'message'=>$e->getMessage()]);
    }
    exit;
}

// unsupported
http_response_code(405);
echo json_encode(['success'=>false,'message'=>'Method Not Allowed']);
