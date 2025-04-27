<?php
// api/home.php - Dedicated API endpoints for staff home page data

require_once __DIR__ . '/../db.php';
require_once __DIR__ . '/../authcheck.php';

// Ensure user is logged in
requireLogin();

header('Content-Type: application/json');
$method = $_SERVER['REQUEST_METHOD'];

if ($method !== 'GET') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

// Get the current user ID from session
$userId = $_SESSION['user_id'] ?? null;
if (!$userId) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

// Get the requested action
$action = $_GET['action'] ?? '';

// Today's sales summary for a specific user
if ($action === 'today_sales') {
    try {
        // Get today's date in SQL format (YYYY-MM-DD)
        $today = date('Y-m-d');
        
        // Get timezone info for debugging
        $serverTimezone = date_default_timezone_get();
        $mysqlTimezone = $pdo->query("SELECT @@session.time_zone, @@global.time_zone")->fetch();
        
        // Query to get sales for today by the current user
        $query = "
            SELECT 
                s.id, 
                s.total, 
                s.status, 
                s.created_at,
                c.name as customer_name
            FROM sales s
            LEFT JOIN customers c ON s.customer_id = c.id
            WHERE s.user_id = ? 
            AND DATE(s.created_at) = ?
            ORDER BY s.created_at DESC
        ";
        
        $stmt = $pdo->prepare($query);
        $stmt->execute([$userId, $today]);
        $sales = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Calculate total revenue with proper data validation
        $totalRevenue = 0;
        foreach ($sales as $sale) {
            // Ensure the value is numeric before adding
            $saleTotal = isset($sale['total']) && is_numeric($sale['total']) ? floatval($sale['total']) : 0;
            $totalRevenue += $saleTotal;
        }
        
        // Also get the raw total from database for debugging
        $rawTotalQuery = "
            SELECT SUM(total) as raw_total
            FROM sales 
            WHERE user_id = ? 
            AND DATE(created_at) = ?
        ";
        $rawStmt = $pdo->prepare($rawTotalQuery);
        $rawStmt->execute([$userId, $today]);
        $rawTotal = $rawStmt->fetchColumn();
        
        // Get total sales count for the day (all users) for comparison
        $allSalesQuery = "
            SELECT COUNT(*) as all_sales_count, SUM(total) as all_sales_total
            FROM sales 
            WHERE DATE(created_at) = ?
        ";
        $allStmt = $pdo->prepare($allSalesQuery);
        $allStmt->execute([$today]);
        $allSales = $allStmt->fetch();
        
        // Format the data for response
        echo json_encode([
            'success' => true,
            'count' => count($sales),
            'total' => $totalRevenue,
            'raw_total' => $rawTotal,
            'sales' => $sales,
            'debug' => [
                'user_id' => $userId,
                'today' => $today,
                'server_time' => date('Y-m-d H:i:s'),
                'server_timezone' => $serverTimezone,
                'mysql_timezone' => $mysqlTimezone,
                'all_sales_count' => $allSales['all_sales_count'] ?? 0,
                'all_sales_total' => $allSales['all_sales_total'] ?? 0,
            ]
        ]);
        
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'message' => 'Error fetching today\'s sales: ' . $e->getMessage()
        ]);
    }
    exit;
}

// Today's stock updates for a specific user
if ($action === 'today_stock') {
    try {
        // Get today's date in SQL format
        $today = date('Y-m-d');
        
        // Query to get stock logs for today by the current user
        $query = "
            SELECT 
                sl.id,
                sl.product_id,
                sl.changes,
                sl.reason,
                sl.timestamp,
                p.name as product_name
            FROM stock_logs sl
            JOIN products p ON sl.product_id = p.id
            WHERE sl.user_id = ?
            AND DATE(sl.timestamp) = ?
            ORDER BY sl.timestamp DESC
        ";
        
        $stmt = $pdo->prepare($query);
        $stmt->execute([$userId, $today]);
        $logs = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Get all stock updates for the day for comparison
        $allStockQuery = "
            SELECT COUNT(*) as all_stock_count
            FROM stock_logs
            WHERE DATE(timestamp) = ?
        ";
        $allStmt = $pdo->prepare($allStockQuery);
        $allStmt->execute([$today]);
        $allStockCount = $allStmt->fetchColumn();
        
        // Check recent stock logs regardless of date for debugging
        $recentStockQuery = "
            SELECT 
                sl.id,
                sl.product_id,
                sl.changes,
                sl.reason,
                sl.user_id,
                sl.timestamp,
                p.name as product_name
            FROM stock_logs sl
            JOIN products p ON sl.product_id = p.id
            ORDER BY sl.timestamp DESC
            LIMIT 5
        ";
        $recentStmt = $pdo->query($recentStockQuery);
        $recentLogs = $recentStmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo json_encode([
            'success' => true,
            'count' => count($logs),
            'logs' => $logs,
            'debug' => [
                'user_id' => $userId,
                'today' => $today,
                'server_time' => date('Y-m-d H:i:s'),
                'all_stock_updates_today' => $allStockCount,
                'recent_logs' => $recentLogs
            ]
        ]);
        
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'message' => 'Error fetching today\'s stock updates: ' . $e->getMessage()
        ]);
    }
    exit;
}

// Admin dashboard metrics
if ($action === 'admin_metrics') {
    try {
        $metrics = [];
        
        // Get product count
        $productQuery = "SELECT COUNT(*) FROM products WHERE deleted_at IS NULL";
        $stmt = $pdo->query($productQuery);
        $metrics['product_count'] = $stmt->fetchColumn();
        
        // Get user count
        $userQuery = "SELECT COUNT(*) FROM users";
        $stmt = $pdo->query($userQuery);
        $metrics['user_count'] = $stmt->fetchColumn();
        
        // Get low stock count
        $lowStockQuery = "SELECT COUNT(*) FROM products WHERE stock <= min_stock AND min_stock > 0";
        $stmt = $pdo->query($lowStockQuery);
        $metrics['low_stock_count'] = $stmt->fetchColumn();
        
        // Get pending sales count
        $pendingSalesQuery = "SELECT COUNT(*) FROM sales WHERE status = 'pending'";
        $stmt = $pdo->query($pendingSalesQuery);
        $metrics['pending_sales_count'] = $stmt->fetchColumn();
        
        // Get today's sales total
        $today = date('Y-m-d');
        $todaySalesQuery = "SELECT SUM(total) FROM sales WHERE DATE(created_at) = ?";
        $stmt = $pdo->prepare($todaySalesQuery);
        $stmt->execute([$today]);
        $metrics['today_sales'] = $stmt->fetchColumn() ?: 0;
        
        // Get this week's sales total (last 7 days)
        $weekSalesQuery = "SELECT SUM(total) FROM sales WHERE created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)";
        $stmt = $pdo->query($weekSalesQuery);
        $metrics['week_sales'] = $stmt->fetchColumn() ?: 0;
        
        // Get this month's sales total
        $monthSalesQuery = "SELECT SUM(total) FROM sales WHERE MONTH(created_at) = MONTH(CURRENT_DATE()) AND YEAR(created_at) = YEAR(CURRENT_DATE())";
        $stmt = $pdo->query($monthSalesQuery);
        $metrics['month_sales'] = $stmt->fetchColumn() ?: 0;
        
        echo json_encode([
            'success' => true,
            'metrics' => $metrics
        ]);
        
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'message' => 'Error fetching admin metrics: ' . $e->getMessage()
        ]);
    }
    exit;
}

// Get low stock products
if ($action === 'low_stock') {
    try {
        $query = "
            SELECT 
                p.id,
                p.name,
                p.stock,
                p.min_stock,
                c.name as category_name
            FROM products p
            LEFT JOIN categories c ON p.category_id = c.id
            WHERE p.stock <= p.min_stock AND p.min_stock > 0
            ORDER BY (p.min_stock - p.stock) DESC
        ";
        
        $stmt = $pdo->query($query);
        $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo json_encode([
            'success' => true,
            'count' => count($products),
            'products' => $products
        ]);
        
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'message' => 'Error fetching low stock products: ' . $e->getMessage()
        ]);
    }
    exit;
}

// Test database connection and table structure
if ($action === 'check') {
    try {
        $response = ['success' => true, 'tables' => []];
        
        // Check sales table
        $salesCheckQuery = "
            SHOW COLUMNS FROM sales
        ";
        $stmt = $pdo->query($salesCheckQuery);
        $salesColumns = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $response['tables']['sales'] = $salesColumns;
        
        // Check stock_logs table
        $stockLogsCheckQuery = "
            SHOW COLUMNS FROM stock_logs
        ";
        $stmt = $pdo->query($stockLogsCheckQuery);
        $stockLogsColumns = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $response['tables']['stock_logs'] = $stockLogsColumns;
        
        // Check for recent sales entries
        $recentSalesQuery = "
            SELECT id, user_id, customer_id, total, status, created_at
            FROM sales
            ORDER BY created_at DESC
            LIMIT 5
        ";
        $stmt = $pdo->query($recentSalesQuery);
        $recentSales = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $response['recent_sales'] = $recentSales;
        
        // Add database timezone info
        $timezoneQuery = "SELECT @@session.time_zone, @@global.time_zone";
        $stmt = $pdo->query($timezoneQuery);
        $timezoneInfo = $stmt->fetch(PDO::FETCH_ASSOC);
        $response['timezone'] = $timezoneInfo;
        
        // Add server information
        $response['server'] = [
            'php_version' => PHP_VERSION,
            'server_time' => date('Y-m-d H:i:s'),
            'timezone' => date_default_timezone_get()
        ];
        
        echo json_encode($response);
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'message' => 'Database check failed: ' . $e->getMessage()
        ]);
    }
    exit;
}

// If no valid action is specified
echo json_encode([
    'success' => false,
    'message' => 'Invalid action specified',
    'available_actions' => ['today_sales', 'today_stock', 'admin_metrics', 'low_stock', 'check']
]); 