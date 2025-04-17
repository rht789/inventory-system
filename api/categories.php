<?php
// api/categories.php
require_once __DIR__ . '/../db.php';
header('Content-Type: application/json');

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    // List all categories
    $stmt = $pdo->query("
        SELECT id, name, description, created_at
        FROM categories
        ORDER BY created_at DESC
    ");
    echo json_encode($stmt->fetchAll());
    exit;
}

if ($method === 'POST') {
    // support form-data or JSON
    $input = $_POST;
    if (empty($input)
        && strpos($_SERVER['CONTENT_TYPE'] ?? '', 'application/json') !== false
    ) {
        $input = json_decode(file_get_contents('php://input'), true) ?? [];
    }

    // DELETE?
    if (!empty($input['action']) && $input['action'] === 'delete') {
        $id = $input['id'] ?? null;
        if (!$id) {
            http_response_code(400);
            echo json_encode(['success'=>false, 'message'=>'Category ID required']);
            exit;
        }
        // ensure no products still reference this category
        $chk = $pdo->prepare("
            SELECT COUNT(*) FROM products
            WHERE category_id = ? AND deleted_at IS NULL
        ");
        $chk->execute([$id]);
        if ($chk->fetchColumn() > 0) {
            http_response_code(409);
            echo json_encode([
                'success'=>false,
                'message'=>'Cannot delete: category in use'
            ]);
            exit;
        }
        $del = $pdo->prepare("DELETE FROM categories WHERE id = ?");
        $del->execute([$id]);
        echo json_encode(['success'=>true]);
        exit;
    }

    // UPDATE?
    if (!empty($input['action']) && $input['action'] === 'update') {
        if (empty($input['id']) || empty(trim($input['name'] ?? ''))) {
            http_response_code(422);
            echo json_encode([
                'success'=>false,
                'message'=>'ID and new name required for update'
            ]);
            exit;
        }
        $upd = $pdo->prepare("
            UPDATE categories
            SET name = :name, description = :description
            WHERE id = :id
        ");
        $upd->execute([
            'id'          => $input['id'],
            'name'        => trim($input['name']),
            'description' => trim($input['description'] ?? '')
        ]);
        echo json_encode(['success'=>true]);
        exit;
    }

    // ADD new category
    $name = trim($input['name'] ?? '');
    if ($name === '') {
        http_response_code(422);
        echo json_encode(['success'=>false, 'message'=>'Category name required']);
        exit;
    }
    try {
        $ins = $pdo->prepare("
            INSERT INTO categories (name, description)
            VALUES (:name, :description)
        ");
        $ins->execute([
            'name'        => $name,
            'description' => trim($input['description'] ?? '')
        ]);
        echo json_encode([
            'success'=>true,
            'id'     =>$pdo->lastInsertId()
        ]);
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode([
            'success'=>false,
            'message'=>$e->getMessage()
        ]);
    }
    exit;
}

// Unsupported
http_response_code(405);
echo json_encode(['success'=>false, 'message'=>'Method Not Allowed']);
