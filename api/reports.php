<?php
// Make sure we have no output before headers
ob_start();

require_once '../db.php';
require_once '../authcheck.php';

// Debug mode - set to false in production
$debugMode = false;

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
    $timeFilter = "DATE(s.created_at) = CURDATE()";
} elseif ($timeRange === 'this_week') {
    $timeFilter = "YEARWEEK(s.created_at, 1) = YEARWEEK(CURDATE(), 1)";
} elseif ($timeRange === 'this_month') {
    $timeFilter = "YEAR(s.created_at) = YEAR(CURDATE()) AND MONTH(s.created_at) = MONTH(CURDATE())";
} elseif ($timeRange === 'last_month') {
    $timeFilter = "YEAR(s.created_at) = YEAR(DATE_SUB(CURDATE(), INTERVAL 1 MONTH)) AND MONTH(s.created_at) = MONTH(DATE_SUB(CURDATE(), INTERVAL 1 MONTH))";
} elseif ($timeRange === 'custom' && $startDate && $endDate) {
    $timeFilter = "DATE(s.created_at) BETWEEN ? AND ?";
    $timeFilterParams = [$startDate, $endDate];
}

// Generate the report based on type
$response = [
    'success' => true,
    'reportType' => $reportType,
    'data' => []
];

// Initialize debug array if debug mode is on
if ($debugMode) {
    $response['debug'] = [
        'timeRange' => $timeRange,
        'startDate' => $startDate,
        'endDate' => $endDate,
        'customerId' => $customerId,
        'status' => $status,
        'categoryId' => $categoryId,
        'productId' => $productId,
        'userId' => $userId
    ];
}

try {
    // Ensure database connection values are properly defined
    if (empty($dbConfig['host']) || empty($dbConfig['dbname'])) {
        throw new Exception("Database configuration is incomplete");
    }
    
    // Connect to database with explicit username and password
    $db = new PDO(
        "mysql:host={$dbConfig['host']};dbname={$dbConfig['dbname']}", 
        $dbConfig['username'],
        $dbConfig['password']
    );
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Initialize summary values
    $response['summary'] = [
        'totalSales' => 0,
        'totalRevenue' => 0,
        'averageSale' => 0,
        'totalQuantity' => 0,
        'productCount' => 0,
        'totalMovements' => 0,
        'totalStockIn' => 0,
        'totalStockOut' => 0,
        'totalBatches' => 0,
        'activeBatches' => 0,
        'expiringSoon' => 0
    ];
    
    if ($reportType === 'sales') {
        // Sales Report
        $whereConditions = [];
        $params = [];
        
        if ($timeFilter) {
            $whereConditions[] = $timeFilter;
            $params = array_merge($params, $timeFilterParams);
        }
        
        if ($customerId) {
            $whereConditions[] = "s.customer_id = ?";
            $params[] = $customerId;
        }
        
        if ($status) {
            $whereConditions[] = "s.status = ?";
            $params[] = $status;
        } else {
            // If no specific status filter is set, we'll still show all statuses in the report
            // But will only count delivered orders for revenue calculation later
        }
        
        $whereClause = !empty($whereConditions) ? "WHERE " . implode(" AND ", $whereConditions) : "";
        
        // First query to get sales data
        $query = "
            SELECT 
                s.id, 
                s.customer_id,
                COALESCE(c.name, 'Walk-in Customer') as customer_name,
                s.total,
                s.discount_total,
                s.status,
                s.note,
                DATE_FORMAT(s.created_at, '%Y-%m-%d') as date,
                CONCAT('INV-', LPAD(s.id, 6, '0')) as invoice_number
            FROM sales s
            LEFT JOIN customers c ON s.customer_id = c.id
            $whereClause
            ORDER BY s.created_at DESC
        ";
        
        if ($debugMode) {
            $response['debug']['salesQuery'] = $query;
            $response['debug']['salesParams'] = $params;
        }
        
        $stmt = $db->prepare($query);
        $stmt->execute($params);
        $sales = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Add item count to each sale
        if (!empty($sales)) {
            $saleIds = array_column($sales, 'id');
            $saleIdsString = implode(',', $saleIds);
            
            $itemCountQuery = "
                SELECT 
                    sale_id, 
                    COUNT(*) as item_count,
                    SUM(quantity) as total_quantity
                FROM sale_items
                WHERE sale_id IN ($saleIdsString)
                GROUP BY sale_id
            ";
            
            if ($debugMode) {
                $response['debug']['itemCountQuery'] = $itemCountQuery;
            }
            
            $itemStmt = $db->prepare($itemCountQuery);
            $itemStmt->execute();
            $itemCounts = $itemStmt->fetchAll(PDO::FETCH_ASSOC);
            
            $itemCountMap = [];
            foreach ($itemCounts as $count) {
                $itemCountMap[$count['sale_id']] = $count;
            }
            
            foreach ($sales as &$sale) {
                $saleId = $sale['id'];
                if (isset($itemCountMap[$saleId])) {
                    $sale['item_count'] = $itemCountMap[$saleId]['total_quantity'];
                } else {
                    $sale['item_count'] = 0;
                }
            }
        }
        
        // Store sales data directly in the main response
        $response['sales'] = $sales ?: [];
        
        // For backwards compatibility
        $response['data']['sales'] = $sales ?: [];
        
        // Calculate summary
        $totalSales = count($sales);
        $totalRevenue = 0;
        $deliveredSalesCount = 0;
        
        foreach ($sales as $sale) {
            // Only count revenue from delivered orders
            if (strtolower($sale['status']) === 'delivered') {
                $totalRevenue += $sale['total'];
                $deliveredSalesCount++;
            }
        }
        
        // Calculate average based on delivered sales only
        $averageSale = $deliveredSalesCount > 0 ? $totalRevenue / $deliveredSalesCount : 0;
        
        $response['summary'] = [
            'totalSales' => $totalSales,
            'totalRevenue' => $totalRevenue,
            'averageSale' => $averageSale,
            'deliveredSales' => $deliveredSalesCount
        ];
    } 
    elseif ($reportType === 'product_sales') {
        // Product Sales Report
        $whereConditions = [];
        $havingConditions = [];
        $params = [];
        
        // Modify timeFilter for product_sales (needs to be on sales table)
        if ($timeRange === 'today') {
            $timeFilter = "DATE(s.created_at) = CURDATE()";
        } elseif ($timeRange === 'this_week') {
            $timeFilter = "YEARWEEK(s.created_at, 1) = YEARWEEK(CURDATE(), 1)";
        } elseif ($timeRange === 'this_month') {
            $timeFilter = "YEAR(s.created_at) = YEAR(CURDATE()) AND MONTH(s.created_at) = MONTH(CURDATE())";
        } elseif ($timeRange === 'last_month') {
            $timeFilter = "YEAR(s.created_at) = YEAR(DATE_SUB(CURDATE(), INTERVAL 1 MONTH)) AND MONTH(s.created_at) = MONTH(DATE_SUB(CURDATE(), INTERVAL 1 MONTH))";
        } elseif ($timeRange === 'custom' && $startDate && $endDate) {
            $timeFilter = "DATE(s.created_at) BETWEEN ? AND ?";
            $timeFilterParams = [$startDate, $endDate];
        }
        
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
        
        // Add condition for delivered orders
        $whereConditions[] = "s.status = 'delivered'";
        
        $whereClause = !empty($whereConditions) ? "WHERE " . implode(" AND ", $whereConditions) : "";
        $havingClause = !empty($havingConditions) ? "HAVING " . implode(" AND ", $havingConditions) : "";
        
        $query = "
            SELECT 
                p.id,
                p.name,
                c.name as category,
                SUM(si.quantity) as quantity_sold,
                SUM(si.subtotal) as revenue,
                CASE WHEN SUM(si.quantity) > 0 
                     THEN SUM(si.subtotal) / SUM(si.quantity) 
                     ELSE p.selling_price
                END as average_price
            FROM sale_items si
            JOIN products p ON si.product_id = p.id
            JOIN categories c ON p.category_id = c.id
            JOIN sales s ON si.sale_id = s.id
            $whereClause
            GROUP BY p.id, p.name, c.name, p.selling_price
            $havingClause
            ORDER BY revenue DESC
        ";
        
        if ($debugMode) {
            $response['debug']['productQuery'] = $query;
            $response['debug']['productParams'] = $params;
        }
        
        $stmt = $db->prepare($query);
        $stmt->execute($params);
        $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        $response['data']['products'] = $products ?: [];
        
        // Calculate summary
        $totalQuantity = 0;
        $totalRevenue = 0;
        foreach ($products as $product) {
            $totalQuantity += $product['quantity_sold'];
            $totalRevenue += $product['revenue'];
        }
        
        $response['summary'] = [
            'totalQuantity' => $totalQuantity,
            'totalRevenue' => $totalRevenue,
            'productCount' => count($products)
        ];
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
        
        // Add movement type filter
        if (isset($_GET['movementType']) && !empty($_GET['movementType'])) {
            $movementType = $_GET['movementType'];
            if ($movementType === 'in') {
                $whereConditions[] = "sl.changes LIKE 'Added%'";
            } else if ($movementType === 'out') {
                $whereConditions[] = "sl.changes LIKE 'Reduced%'";
            } else if ($movementType === 'adjustment') {
                $whereConditions[] = "sl.changes NOT LIKE 'Added%' AND sl.changes NOT LIKE 'Reduced%'";
            }
        }
        
        $whereClause = !empty($whereConditions) ? "WHERE " . implode(" AND ", $whereConditions) : "";
        
        $query = "
            SELECT 
                sl.id,
                p.name as product_name,
                'Default' as size_name,
                CASE 
                    WHEN sl.changes LIKE 'Added%' THEN 'Stock In'
                    WHEN sl.changes LIKE 'Reduced%' THEN 'Stock Out'
                    ELSE 'Adjustment'
                END as type,
                CAST(SUBSTRING_INDEX(SUBSTRING_INDEX(sl.changes, ' ', 2), ' ', -1) AS UNSIGNED) as quantity,
                sl.reason,
                u.username as user_name,
                sl.timestamp as date
            FROM stock_logs sl
            JOIN products p ON sl.product_id = p.id
            LEFT JOIN users u ON sl.user_id = u.id
            $whereClause
            ORDER BY sl.timestamp DESC
        ";
        
        if ($debugMode) {
            $response['debug']['movementQuery'] = $query;
            $response['debug']['movementParams'] = $params;
        }
        
        $stmt = $db->prepare($query);
        $stmt->execute($params);
        $movements = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        $response['data']['movements'] = $movements ?: [];
        
        // Calculate summary
        $totalMovements = count($movements);
        $totalStockIn = 0;
        $totalStockOut = 0;
        $totalStockInQuantity = 0;
        $totalStockOutQuantity = 0;
        $totalAdjustmentQuantity = 0;
        
        foreach ($movements as $movement) {
            if (strpos(strtolower($movement['type']), 'in') !== false) {
                $totalStockIn++;
                $totalStockInQuantity += intval($movement['quantity']);
            } else if (strpos(strtolower($movement['type']), 'out') !== false) {
                $totalStockOut++;
                $totalStockOutQuantity += intval($movement['quantity']);
            } else {
                // Adjustment
                $totalAdjustmentQuantity += intval($movement['quantity']);
            }
        }
        
        $response['summary'] = [
            'totalMovements' => $totalMovements,
            'totalStockIn' => $totalStockIn,
            'totalStockOut' => $totalStockOut,
            'totalStockInQuantity' => $totalStockInQuantity,
            'totalStockOutQuantity' => $totalStockOutQuantity,
            'totalAdjustmentQuantity' => $totalAdjustmentQuantity
        ];
    } 
    elseif ($reportType === 'user_sales') {
        // User Sales Report
        $whereConditions = [];
        $params = [];
        
        // Adjust timeFilter for sales
        if ($timeRange === 'today') {
            $timeFilter = "DATE(s.created_at) = CURDATE()";
        } elseif ($timeRange === 'this_week') {
            $timeFilter = "YEARWEEK(s.created_at, 1) = YEARWEEK(CURDATE(), 1)";
        } elseif ($timeRange === 'this_month') {
            $timeFilter = "YEAR(s.created_at) = YEAR(CURDATE()) AND MONTH(s.created_at) = MONTH(CURDATE())";
        } elseif ($timeRange === 'last_month') {
            $timeFilter = "YEAR(s.created_at) = YEAR(DATE_SUB(CURDATE(), INTERVAL 1 MONTH)) AND MONTH(s.created_at) = MONTH(DATE_SUB(CURDATE(), INTERVAL 1 MONTH))";
        } elseif ($timeRange === 'custom' && $startDate && $endDate) {
            $timeFilter = "DATE(s.created_at) BETWEEN ? AND ?";
            $timeFilterParams = [$startDate, $endDate];
        }
        
        if ($timeFilter) {
            $whereConditions[] = $timeFilter;
            $params = array_merge($params, $timeFilterParams);
        }
        
        if ($userId) {
            $whereConditions[] = "s.user_id = ?";
            $params[] = $userId;
        }
        
        if ($status) {
            $whereConditions[] = "s.status = ?";
            $params[] = $status;
        }
        
        $whereClause = !empty($whereConditions) ? "WHERE " . implode(" AND ", $whereConditions) : "";
        
        // Query to get user sales summary
        $query = "
            SELECT 
                u.id,
                u.username,
                u.role,
                COUNT(DISTINCT s.id) as sales_count,
                SUM(CASE WHEN s.status = 'delivered' THEN s.total ELSE 0 END) as revenue,
                CASE
                    WHEN COUNT(DISTINCT CASE WHEN s.status = 'delivered' THEN s.id ELSE NULL END) > 0 
                    THEN SUM(CASE WHEN s.status = 'delivered' THEN s.total ELSE 0 END) / COUNT(DISTINCT CASE WHEN s.status = 'delivered' THEN s.id ELSE NULL END)
                    ELSE 0
                END as average_sale,
                (
                    SELECT SUM(si.quantity)
                    FROM sale_items si
                    JOIN sales s2 ON si.sale_id = s2.id
                    WHERE s2.user_id = u.id
                    AND (? = '' OR s2.status = ?)
                ) as items_sold
            FROM users u
            LEFT JOIN sales s ON u.id = s.user_id
            $whereClause
            GROUP BY u.id, u.username, u.role
            ORDER BY revenue DESC
        ";
        
        // Add status parameter for the subquery (repeat it as it's used twice in the prepared statement)
        $subqueryParams = [$status ?: '', $status ?: ''];
        $params = array_merge($subqueryParams, $params);
        
        if ($debugMode) {
            $response['debug']['userSalesQuery'] = $query;
            $response['debug']['userSalesParams'] = $params;
        }
        
        $stmt = $db->prepare($query);
        $stmt->execute($params);
        $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        $response['data']['users'] = $users ?: [];
        
        // Calculate summary data
        $totalUsers = count($users);
        $totalSales = 0;
        $totalRevenue = 0;
        $totalItems = 0;
        
        foreach ($users as $user) {
            $totalSales += $user['sales_count'];
            $totalRevenue += $user['revenue'];
            $totalItems += $user['items_sold'] ?: 0;
        }
        
        $response['summary'] = [
            'totalUsers' => $totalUsers,
            'totalSales' => $totalSales,
            'totalRevenue' => $totalRevenue,
            'totalItems' => $totalItems
        ];
    } 
    else {
        throw new Exception("Invalid report type");
    }
} catch (Exception $e) {
    $response = [
        'success' => false,
        'message' => $e->getMessage()
    ];
    
    if ($debugMode) {
        $response['debug']['error'] = $e->getTraceAsString();
    }
}

// Clear any output that might have been sent before
ob_clean();

// Return JSON response
header('Content-Type: application/json');
echo json_encode($response, JSON_NUMERIC_CHECK);
exit;
