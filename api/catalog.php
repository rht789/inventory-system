<?php
// api/public_products.php - Public API for product catalog, doesn't require authentication

require_once __DIR__ . '/../db.php';

// Set proper content type and encoding for JSON
header('Content-Type: application/json; charset=utf-8');

// Set default response structure
$response = [
    'status' => 'error',
    'message' => 'Unknown error',
    'data' => null
];

try {
    // Get and sanitize request parameters
    $page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
    $limit = isset($_GET['limit']) ? min(50, max(1, intval($_GET['limit']))) : 12;
    $offset = ($page - 1) * $limit;
    
    $search = isset($_GET['search']) ? trim($_GET['search']) : '';
    $categoryId = isset($_GET['category_id']) ? intval($_GET['category_id']) : 0;
    $minPrice = isset($_GET['min_price']) ? floatval($_GET['min_price']) : 0;
    $maxPrice = isset($_GET['max_price']) ? floatval($_GET['max_price']) : PHP_FLOAT_MAX;
    $stockFilter = isset($_GET['stock']) ? trim($_GET['stock']) : 'all';
    
    // Sorting parameters
    $sortBy = isset($_GET['sort_by']) ? trim($_GET['sort_by']) : 'created_at';
    $sortDir = isset($_GET['sort_dir']) && strtoupper($_GET['sort_dir']) === 'ASC' ? 'ASC' : 'DESC';
    
    // Validate sort column to prevent SQL injection
    $allowedSortColumns = ['name', 'selling_price', 'created_at'];
    if (!in_array($sortBy, $allowedSortColumns)) {
        $sortBy = 'created_at'; // Default if invalid
    }
    
    // Build SQL conditions
    $conditions = ["p.deleted_at IS NULL"];
    $params = [];
    
    if (!empty($search)) {
        $conditions[] = "(p.name LIKE ? OR p.description LIKE ?)";
        $params[] = "%$search%";
        $params[] = "%$search%";
    }
    
    if ($categoryId > 0) {
        $conditions[] = "p.category_id = ?";
        $params[] = $categoryId;
    }
    
    if ($minPrice > 0) {
        $conditions[] = "p.selling_price >= ?";
        $params[] = $minPrice;
    }
    
    if ($maxPrice < PHP_FLOAT_MAX) {
        $conditions[] = "p.selling_price <= ?";
        $params[] = $maxPrice;
    }
    
    // Stock filter
    if ($stockFilter === 'in_stock') {
        $conditions[] = "p.stock > 0";
    } elseif ($stockFilter === 'out_of_stock') {
        $conditions[] = "p.stock = 0";
    }
    
    // Build WHERE clause
    $whereClause = count($conditions) > 0 ? "WHERE " . implode(" AND ", $conditions) : "";
    
    // Get total count for pagination
    $countSql = "
        SELECT COUNT(*) as total
        FROM products p
        $whereClause
    ";
    
    $countStmt = $pdo->prepare($countSql);
    $countStmt->execute($params);
    $totalProducts = $countStmt->fetchColumn();
    
    // Get the products with pagination
    $productsSql = "
        SELECT 
            p.id, 
            p.name, 
            p.description, 
            p.price, 
            p.selling_price, 
            p.stock, 
            p.min_stock,
            p.image,
            p.created_at,
            c.id AS category_id, 
            c.name AS category_name
        FROM products p
        JOIN categories c ON p.category_id = c.id
        $whereClause
        ORDER BY p.$sortBy $sortDir
        LIMIT $limit OFFSET $offset
    ";
    
    $productsStmt = $pdo->prepare($productsSql);
    $productsStmt->execute($params);
    $products = $productsStmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Enhance products with additional data
    foreach ($products as &$product) {
        // Fetch sizes for the product
        $sizesSql = "
            SELECT id, size_name, stock 
            FROM product_sizes 
            WHERE product_id = ?
        ";
        $sizesStmt = $pdo->prepare($sizesSql);
        $sizesStmt->execute([$product['id']]);
        $product['sizes'] = $sizesStmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Add helpful flags
        $product['is_new'] = (strtotime($product['created_at']) > strtotime('-30 days'));
        $product['is_low_stock'] = ($product['stock'] > 0 && $product['stock'] <= $product['min_stock']);
        
        // Format values for display
        $product['created_at'] = date('Y-m-d', strtotime($product['created_at']));
    }
    
    // Calculate pagination data
    $totalPages = ceil($totalProducts / $limit);
    $pagination = [
        'total' => (int)$totalProducts,
        'per_page' => (int)$limit,
        'current_page' => (int)$page,
        'last_page' => (int)$totalPages,
        'from' => (int)($offset + 1),
        'to' => (int)min($offset + $limit, $totalProducts)
    ];
    
    // Set successful response
    $response = [
        'status' => 'success',
        'message' => 'Products retrieved successfully',
        'data' => [
            'products' => $products,
            'pagination' => $pagination
        ]
    ];
    
} catch (Exception $e) {
    // Log the error
    error_log("Public Products API Error: " . $e->getMessage());
    
    // Set error response
    $response = [
        'status' => 'error',
        'message' => 'Failed to retrieve products: ' . $e->getMessage(),
        'data' => null
    ];
}

// Return JSON response with error handling
try {
    echo json_encode($response, JSON_NUMERIC_CHECK);
} catch (Exception $e) {
    error_log("JSON encoding error: " . $e->getMessage());
    echo json_encode([
        'status' => 'error',
        'message' => 'JSON encoding error occurred',
        'data' => null
    ]);
}
exit; 