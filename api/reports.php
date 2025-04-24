<?php
// Make sure we have no output before headers
ob_start();

require_once '../db.php';
require_once '../authcheck.php';

// Ensure user is logged in
requireLogin();

// Ensure user is admin
requireRole('admin');

// Get request parameters
$reportType = $_GET['reportType'] ?? '';
$timeRange = $_GET['timeRange'] ?? 'all_time';
$startDate = $_GET['startDate'] ?? '';
$endDate = $_GET['endDate'] ?? '';

// Get additional filters
$customerId = $_GET['customerId'] ?? '';
$status = $_GET['status'] ?? '';
$categoryId = $_GET['categoryId'] ?? '';
$productId = $_GET['productId'] ?? '';
$userId = $_GET['userId'] ?? '';
$manufacturedStart = $_GET['manufacturedStart'] ?? '';
$manufacturedEnd = $_GET['manufacturedEnd'] ?? '';

// Set up time filter conditions
$timeFilter = "";
$timeFilterParams = [];

if ($timeRange === 'today') {
    $timeFilter = "DATE(created_at) = CURDATE()";
} elseif ($timeRange === 'this_week') {
    $timeFilter = "YEARWEEK(created_at, 1) = YEARWEEK(CURDATE(), 1)";
} elseif ($timeRange === 'this_month') {
    $timeFilter = "YEAR(created_at) = YEAR(CURDATE()) AND MONTH(created_at) = MONTH(CURDATE())";
} elseif ($timeRange === 'last_month') {
    $timeFilter = "YEAR(created_at) = YEAR(DATE_SUB(CURDATE(), INTERVAL 1 MONTH)) AND MONTH(created_at) = MONTH(DATE_SUB(CURDATE(), INTERVAL 1 MONTH))";
} elseif ($timeRange === 'custom' && $startDate && $endDate) {
    $timeFilter = "DATE(created_at) BETWEEN ? AND ?";
    $timeFilterParams = [$startDate, $endDate];
}

// Generate the report based on type
$response = [
    'success' => true,
    'reportType' => $reportType,
    'data' => []
];

try {
    // Connect to database
    $db = new PDO("mysql:host={$dbConfig['host']};dbname={$dbConfig['dbname']}", $dbConfig['username'], $dbConfig['password']);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    if ($reportType === 'sales') {
        // Sales Report
        $whereConditions = [];
        $params = [];
        
        if ($timeFilter) {
            $whereConditions[] = $timeFilter;
            $params = array_merge($params, $timeFilterParams);
        }
        
        if ($customerId) {
            $whereConditions[] = "customer_id = ?";
            $params[] = $customerId;
        }
        
        if ($status) {
            $whereConditions[] = "status = ?";
            $params[] = $status;
        }
        
        $whereClause = !empty($whereConditions) ? "WHERE " . implode(" AND ", $whereConditions) : "";
        
        $query = "
            SELECT 
                s.id, 
                s.customer_id,
                COALESCE(c.name, 'Walk-in Customer') as customer_name,
                s.total,
                s.discount_total,
                s.status,
                s.note,
                s.created_at
            FROM sales s
            LEFT JOIN customers c ON s.customer_id = c.id
            $whereClause
            ORDER BY s.created_at DESC
        ";
        
        $stmt = $db->prepare($query);
        $stmt->execute($params);
        $sales = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        $response['data']['sales'] = $sales ?: [];
    } 
    elseif ($reportType === 'product_sales') {
        // Product Sales Report
        $whereConditions = [];
        $havingConditions = [];
        $params = [];
        
        if ($timeFilter) {
            $whereConditions[] = $timeFilter;
            $params = array_merge($params, $timeFilterParams);
        }
        
        if ($categoryId) {
            $whereConditions[] = "p.category_id = ?";
            $params[] = $categoryId;
        }
        
        if ($productId) {
            $whereConditions[] = "si.product_id = ?";
            $params[] = $productId;
        }
        
        $whereClause = !empty($whereConditions) ? "WHERE " . implode(" AND ", $whereConditions) : "";
        $havingClause = !empty($havingConditions) ? "HAVING " . implode(" AND ", $havingConditions) : "";
        
        $query = "
            SELECT 
                p.id,
                p.name,
                c.name as category_name,
                SUM(si.quantity) as quantity_sold,
                SUM(si.price * si.quantity) as total_revenue
            FROM sale_items si
            JOIN products p ON si.product_id = p.id
            JOIN categories c ON p.category_id = c.id
            JOIN sales s ON si.sale_id = s.id
            $whereClause
            GROUP BY p.id, p.name, c.name
            $havingClause
            ORDER BY total_revenue DESC
        ";
        
        $stmt = $db->prepare($query);
        $stmt->execute($params);
        $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        $response['data']['products'] = $products ?: [];
    } 
    elseif ($reportType === 'stock_movement') {
        // Stock Movement Report
        $whereConditions = [];
        $params = [];
        
        // Adjust timeFilter for stock_logs
        if ($timeRange === 'today') {
            $timeFilter = "DATE(sl.timestamp) = CURDATE()";
        } elseif ($timeRange === 'this_week') {
            $timeFilter = "YEARWEEK(sl.timestamp, 1) = YEARWEEK(CURDATE(), 1)";
        } elseif ($timeRange === 'this_month') {
            $timeFilter = "YEAR(sl.timestamp) = YEAR(CURDATE()) AND MONTH(sl.timestamp) = MONTH(CURDATE())";
        } elseif ($timeRange === 'last_month') {
            $timeFilter = "YEAR(sl.timestamp) = YEAR(DATE_SUB(CURDATE(), INTERVAL 1 MONTH)) AND MONTH(sl.timestamp) = MONTH(DATE_SUB(CURDATE(), INTERVAL 1 MONTH))";
        } elseif ($timeRange === 'custom' && $startDate && $endDate) {
            $timeFilter = "DATE(sl.timestamp) BETWEEN ? AND ?";
            $timeFilterParams = [$startDate, $endDate];
        }
        
        if ($timeFilter) {
            $whereConditions[] = $timeFilter;
            $params = array_merge($params, $timeFilterParams);
        }
        
        if ($productId) {
            $whereConditions[] = "sl.product_id = ?";
            $params[] = $productId;
        }
        
        if ($userId) {
            $whereConditions[] = "sl.user_id = ?";
            $params[] = $userId;
        }
        
        $whereClause = !empty($whereConditions) ? "WHERE " . implode(" AND ", $whereConditions) : "";
        
        $query = "
            SELECT 
                sl.id,
                p.name as product_name,
                ps.name as size_name,
                sl.type,
                sl.changes,
                sl.reason,
                u.username as user_name,
                sl.timestamp
            FROM stock_logs sl
            JOIN products p ON sl.product_id = p.id
            LEFT JOIN product_sizes ps ON sl.product_size_id = ps.id
            LEFT JOIN users u ON sl.user_id = u.id
            $whereClause
            ORDER BY sl.timestamp DESC
        ";
        
        $stmt = $db->prepare($query);
        $stmt->execute($params);
        $logs = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        $response['data']['logs'] = $logs ?: [];
    } 
    elseif ($reportType === 'batch') {
        // Batch Report
        $whereConditions = [];
        $params = [];
        
        if ($productId) {
            $whereConditions[] = "b.product_id = ?";
            $params[] = $productId;
        }
        
        if ($manufacturedStart) {
            $whereConditions[] = "b.manufactured_date >= ?";
            $params[] = $manufacturedStart;
        }
        
        if ($manufacturedEnd) {
            $whereConditions[] = "b.manufactured_date <= ?";
            $params[] = $manufacturedEnd;
        }
        
        $whereClause = !empty($whereConditions) ? "WHERE " . implode(" AND ", $whereConditions) : "";
        
        $query = "
            SELECT 
                b.id,
                b.batch_number,
                p.name as product_name,
                ps.name as size_name,
                b.manufactured_date,
                b.stock
            FROM batches b
            JOIN products p ON b.product_id = p.id
            JOIN product_sizes ps ON b.product_size_id = ps.id
            $whereClause
            ORDER BY b.manufactured_date DESC
        ";
        
        $stmt = $db->prepare($query);
        $stmt->execute($params);
        $batches = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        $response['data']['batches'] = $batches ?: [];
    } 
    else {
        throw new Exception("Invalid report type");
    }
} catch (Exception $e) {
    $response = [
        'success' => false,
        'message' => $e->getMessage()
    ];
}

// Clear any output that might have been sent before
ob_clean();

// Return JSON response
header('Content-Type: application/json');
echo json_encode($response, JSON_NUMERIC_CHECK);
exit;
