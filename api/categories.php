<?php
// api/categories.php
require_once __DIR__ . '/../db.php';
header('Content-Type: application/json');

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    // ── List all categories ────────────────────────────────────────────────────────
    $stmt = $pdo->query("SELECT id, name, description, created_at FROM categories ORDER BY created_at DESC");
    $categories = $stmt->fetchAll();
    echo json_encode($categories);
    exit;
}

if ($method === 'POST') {
    // handle JSON payload or form-data
    $input = $_POST;
    if (empty($input) && strpos($_SERVER['CONTENT_TYPE'], 'application/json') !== false) {
        $raw = file_get_contents('php://input');
        $input = json_decode($raw, true) ?? [];
    }

    // ── Delete a category ──────────────────────────────────────────────────────────
    if (isset($input['action']) && $input['action'] === 'delete') {
        $id = $input['id'] ?? null;
        if (!$id) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Category ID is required']);
            exit;
        }

        // prevent deletion if in use
        $countStmt = $pdo->prepare("
            SELECT COUNT(*) 
            FROM products 
            WHERE category_id = ? AND deleted_at IS NULL
        ");
        $countStmt->execute([$id]);
        $inUse = $countStmt->fetchColumn();

        if ($inUse > 0) {
            http_response_code(409);
            echo json_encode([
                'success' => false,
                'message' => 'Cannot delete: category is in use by products'
            ]);
            exit;
        }

        // safe to delete
        $del = $pdo->prepare("DELETE FROM categories WHERE id = ?");
        $del->execute([$id]);
        echo json_encode(['success' => true]);
        exit;
    }

    // ── Add a new category ────────────────────────────────────────────────────────
    $name = trim($input['name'] ?? '');
    $description = trim($input['description'] ?? '');

    if ($name === '') {
        http_response_code(422);
        echo json_encode(['success' => false, 'message' => 'Category name is required']);
        exit;
    }

    try {
        $ins = $pdo->prepare("
            INSERT INTO categories (name, description)
            VALUES (:name, :description)
        ");
        $ins->execute([
            'name'        => $name,
            'description' => $description
        ]);

        echo json_encode([
            'success' => true,
            'id'      => $pdo->lastInsertId()
        ]);
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'message' => $e->getMessage()
        ]);
    }
    exit;
}

// ── Unsupported HTTP method ────────────────────────────────────────────────────
http_response_code(405);
echo json_encode(['success' => false, 'message' => 'Method Not Allowed']);
