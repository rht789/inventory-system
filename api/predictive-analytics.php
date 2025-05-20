<?php
// Make sure we have no output before headers
ob_start();

require_once __DIR__ . '/../db.php';
require_once __DIR__ . '/../authcheck.php';

// Ensure user is logged in
requireLogin();

// Ensure user is admin
requireRole('admin');

// Get the analysis type from the request
$analysisType = $_GET['type'] ?? '';

// Set up response
$response = [
    'success' => true,
    'data' => []
];

try {
    $db = new PDO(
        "mysql:host={$dbConfig['host']};dbname={$dbConfig['dbname']}", 
        $dbConfig['username'],
        $dbConfig['password']
    );
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    switch ($analysisType) {
        case 'sales_forecast':
            // Get sales data for forecasting
            generateSalesForecast($db, $response);
            break;
            
        case 'inventory_prediction':
            // Predict inventory needs
            generateInventoryPrediction($db, $response);
            break;
            
        case 'profit_margin_analysis':
            // Analyze profit margins
            generateProfitMarginAnalysis($db, $response);
            break;
            
        case 'product_performance':
            // Product performance metrics
            generateProductPerformanceMetrics($db, $response);
            break;
            
        case 'customer_segmentation':
            // Customer segmentation
            generateCustomerSegmentation($db, $response);
            break;
            
        default:
            // Default to returning overall analytics
            generateOverallAnalytics($db, $response);
            break;
    }
} catch (PDOException $e) {
    // Database or SQL error
    $error = $e->getMessage();
    $errorCode = $e->getCode();
    
    // Add helpful message for common errors
    $additionalMsg = '';
    if (strpos($error, "Unknown column 'p.sku'") !== false) {
        $additionalMsg = "The 'products' table is missing the 'sku' column. Please run the appropriate migration.";
    } else if (strpos($error, "inventory_system.stock") !== false) {
        $additionalMsg = "The 'stock' table doesn't exist. Your inventory is stored directly in the products table.";
    } else if (strpos($error, "min_stock_level") !== false) {
        $additionalMsg = "The column name for minimum stock is 'min_stock' not 'min_stock_level'.";
    } else if (strpos($error, "buying_price") !== false) {
        $additionalMsg = "The column name for buying price is 'price' not 'buying_price'.";
    } else if (strpos($error, "Unknown column 'si.price'") !== false) {
        $additionalMsg = "The 'sale_items' table uses 'subtotal' column, not 'price'. The query has been fixed.";
    }
    
    $response['success'] = false;
    $response['message'] = 'Database error: ' . $error;
    if (!empty($additionalMsg)) {
        $response['suggestion'] = $additionalMsg;
    }
    $response['error_code'] = $errorCode;
    $response['analytics_type'] = $analysisType;
} catch (Exception $e) {
    // General error
    $response['success'] = false;
    $response['message'] = 'Failed to generate analytics: ' . $e->getMessage();
}

// Return JSON response
header('Content-Type: application/json');
echo json_encode($response);
exit;

/**
 * Generate sales forecast based on historical data
 */
function generateSalesForecast($db, &$response) {
    // Get the last 6 months of sales data
    $query = "
        SELECT 
            DATE_FORMAT(created_at, '%Y-%m') AS month,
            SUM(total) AS total_sales,
            COUNT(*) AS transaction_count
        FROM sales 
        WHERE created_at >= DATE_SUB(CURDATE(), INTERVAL 6 MONTH)
            AND deleted_at IS NULL
        GROUP BY DATE_FORMAT(created_at, '%Y-%m')
        ORDER BY month ASC
    ";
    
    $stmt = $db->prepare($query);
    $stmt->execute();
    $historicalData = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Calculate average monthly growth
    $monthlyGrowth = 0;
    $previousSales = 0;
    $growthRates = [];
    
    foreach ($historicalData as $index => $month) {
        if ($index > 0 && $previousSales > 0) {
            $currentSales = floatval($month['total_sales']);
            $growthRate = ($currentSales - $previousSales) / $previousSales;
            $growthRates[] = $growthRate;
        }
        $previousSales = floatval($month['total_sales']);
    }
    
    // Calculate average growth rate
    if (count($growthRates) > 0) {
        $monthlyGrowth = array_sum($growthRates) / count($growthRates);
    }
    
    // Generate forecast for the next 3 months
    $forecast = [];
    $lastMonth = end($historicalData);
    $lastMonthSales = floatval($lastMonth['total_sales']);
    
    for ($i = 1; $i <= 3; $i++) {
        $projectedSales = $lastMonthSales * (1 + $monthlyGrowth) * $i;
        
        // Get the month name for the forecast
        $nextMonthDate = date('Y-m', strtotime("+$i month"));
        $nextMonthName = date('F Y', strtotime("+$i month"));
        
        $forecast[] = [
            'month' => $nextMonthDate,
            'month_name' => $nextMonthName,
            'projected_sales' => round($projectedSales, 2),
            'confidence_level' => calculateConfidenceLevel($growthRates, $i)
        ];
    }
    
    $response['data'] = [
        'historical_data' => $historicalData,
        'forecast' => $forecast,
        'monthly_growth_rate' => $monthlyGrowth,
        'growth_volatility' => count($growthRates) > 0 ? standardDeviation($growthRates) : 0
    ];
}

/**
 * Generate inventory prediction based on sales velocity
 */
function generateInventoryPrediction($db, &$response) {
    // Get current inventory levels and calculate days of supply
    $query = "
        SELECT 
            p.id,
            p.name,
            c.name as category_name,
            p.stock as current_stock,
            p.min_stock,
            COALESCE(
                (SELECT SUM(si.quantity) 
                FROM sale_items si 
                JOIN sales sa ON si.sale_id = sa.id 
                WHERE si.product_id = p.id 
                AND sa.created_at >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)
                AND sa.deleted_at IS NULL), 0
            ) as monthly_sales,
            p.price,
            p.selling_price
        FROM products p
        LEFT JOIN categories c ON p.category_id = c.id
        WHERE p.deleted_at IS NULL
        ORDER BY monthly_sales DESC
    ";
    
    $stmt = $db->prepare($query);
    $stmt->execute();
    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $inventoryPredictions = [];
    
    foreach ($products as $product) {
        // Avoid division by zero
        $dailySales = $product['monthly_sales'] > 0 ? $product['monthly_sales'] / 30 : 0.01;
        
        // Calculate days of supply
        $daysOfSupply = $dailySales > 0 ? $product['current_stock'] / $dailySales : 999;
        
        // Calculate reorder recommendation
        $reorderRecommendation = 0;
        $status = 'ok';
        
        if ($daysOfSupply < 7) {
            $status = 'critical';
            // Recommend stock for 30 days
            $reorderRecommendation = ceil(($dailySales * 30) - $product['current_stock']);
        } else if ($daysOfSupply < 14) {
            $status = 'low';
            // Recommend stock for 21 days
            $reorderRecommendation = ceil(($dailySales * 21) - $product['current_stock']);
        } else if ($daysOfSupply < 30) {
            $status = 'warning';
            // Recommend stock to reach minimum for 14 days
            $reorderRecommendation = max(0, ceil(($dailySales * 14) - $product['current_stock']));
        }
        
        // Only include products that need attention or high performers
        if ($status !== 'ok' || $product['monthly_sales'] > 10) {
            $inventoryPredictions[] = [
                'id' => $product['id'],
                'name' => $product['name'],
                'category' => $product['category_name'],
                'current_stock' => $product['current_stock'],
                'min_stock' => $product['min_stock'],
                'monthly_sales' => $product['monthly_sales'],
                'daily_sales' => round($dailySales, 2),
                'days_of_supply' => round($daysOfSupply, 1),
                'reorder_recommendation' => $reorderRecommendation,
                'estimated_reorder_cost' => round($reorderRecommendation * $product['price'], 2),
                'status' => $status
            ];
        }
    }
    
    $response['data'] = $inventoryPredictions;
}

/**
 * Generate profit margin analysis by product and category
 */
function generateProfitMarginAnalysis($db, &$response) {
    // Get profit margins by product and category
    $query = "
        SELECT 
            p.id,
            p.name,
            c.name as category_name,
            p.price as buying_price,
            p.selling_price,
            ((p.selling_price - p.price) / p.selling_price * 100) as profit_margin,
            COALESCE(
                (SELECT SUM(si.quantity) 
                FROM sale_items si 
                JOIN sales sa ON si.sale_id = sa.id 
                WHERE si.product_id = p.id 
                AND sa.created_at >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)
                AND sa.deleted_at IS NULL), 0
            ) as monthly_sales,
            COALESCE(
                (SELECT SUM((si.subtotal/si.quantity - p.price) * si.quantity) 
                FROM sale_items si 
                JOIN sales sa ON si.sale_id = sa.id 
                WHERE si.product_id = p.id 
                AND sa.created_at >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)
                AND sa.deleted_at IS NULL), 0
            ) as monthly_profit
        FROM products p
        LEFT JOIN categories c ON p.category_id = c.id
        WHERE p.deleted_at IS NULL
        ORDER BY monthly_profit DESC
    ";
    
    $stmt = $db->prepare($query);
    $stmt->execute();
    $profitByProduct = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Get profit margins by category
    $query = "
        SELECT 
            c.id,
            c.name,
            AVG((p.selling_price - p.price) / p.selling_price * 100) as avg_profit_margin,
            SUM(
                COALESCE(
                    (SELECT SUM((si.subtotal/si.quantity - p.price) * si.quantity) 
                    FROM sale_items si 
                    JOIN sales sa ON si.sale_id = sa.id 
                    WHERE si.product_id = p.id 
                    AND sa.created_at >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)
                    AND sa.deleted_at IS NULL), 0
                )
            ) as monthly_profit,
            COUNT(p.id) as product_count
        FROM categories c
        LEFT JOIN products p ON c.id = p.category_id
        WHERE c.deleted_at IS NULL
        GROUP BY c.id, c.name
        ORDER BY monthly_profit DESC
    ";
    
    $stmt = $db->prepare($query);
    $stmt->execute();
    $profitByCategory = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $response['data'] = [
        'by_product' => $profitByProduct,
        'by_category' => $profitByCategory
    ];
}

/**
 * Generate product performance metrics
 */
function generateProductPerformanceMetrics($db, &$response) {
    // Get product performance data
    $query = "
        SELECT 
            p.id,
            p.name,
            c.name as category_name,
            COALESCE(
                (SELECT SUM(si.quantity) 
                FROM sale_items si 
                JOIN sales sa ON si.sale_id = sa.id 
                WHERE si.product_id = p.id 
                AND sa.deleted_at IS NULL), 0
            ) as total_quantity_sold,
            COALESCE(
                (SELECT COUNT(DISTINCT sa.id) 
                FROM sale_items si 
                JOIN sales sa ON si.sale_id = sa.id 
                WHERE si.product_id = p.id 
                AND sa.deleted_at IS NULL), 0
            ) as order_count,
            COALESCE(
                (SELECT AVG(si.quantity) 
                FROM sale_items si 
                JOIN sales sa ON si.sale_id = sa.id 
                WHERE si.product_id = p.id 
                AND sa.deleted_at IS NULL), 0
            ) as avg_quantity_per_order,
            p.price as buying_price,
            p.selling_price,
            ((p.selling_price - p.price) / p.selling_price * 100) as profit_margin
        FROM products p
        LEFT JOIN categories c ON p.category_id = c.id
        WHERE p.deleted_at IS NULL
        ORDER BY total_quantity_sold DESC
        LIMIT 20
    ";
    
    $stmt = $db->prepare($query);
    $stmt->execute();
    $productPerformance = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $response['data'] = $productPerformance;
}

/**
 * Generate customer segmentation analysis
 */
function generateCustomerSegmentation($db, &$response) {
    // Get customer purchase data
    $query = "
        SELECT 
            c.id,
            c.name,
            COUNT(s.id) as order_count,
            SUM(s.total) as total_spent,
            AVG(s.total) as avg_order_value,
            MAX(s.created_at) as last_purchase_date,
            DATEDIFF(CURDATE(), MAX(s.created_at)) as days_since_last_purchase
        FROM customers c
        LEFT JOIN sales s ON c.id = s.customer_id
        WHERE c.deleted_at IS NULL
        AND (s.id IS NULL OR s.deleted_at IS NULL)
        GROUP BY c.id, c.name
        ORDER BY total_spent DESC
    ";
    
    $stmt = $db->prepare($query);
    $stmt->execute();
    $customerData = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Segment customers based on RFM (Recency, Frequency, Monetary)
    $segments = [
        'high_value' => [],
        'loyal' => [],
        'potential' => [],
        'at_risk' => [],
        'inactive' => []
    ];
    
    foreach ($customerData as $customer) {
        if (empty($customer['last_purchase_date'])) {
            $segments['inactive'][] = $customer;
            continue;
        }
        
        // RFM scoring (simple version)
        $recency = $customer['days_since_last_purchase'] ?? 999;
        $frequency = $customer['order_count'] ?? 0;
        $monetary = $customer['total_spent'] ?? 0;
        
        if ($recency <= 30 && $frequency >= 3 && $monetary >= 1000) {
            $segments['high_value'][] = $customer;
        } else if ($recency <= 60 && $frequency >= 2) {
            $segments['loyal'][] = $customer;
        } else if ($recency <= 30 && $monetary >= 500) {
            $segments['potential'][] = $customer;
        } else if ($recency > 60 && $recency <= 90 && $frequency >= 2) {
            $segments['at_risk'][] = $customer;
        } else if ($recency > 90) {
            $segments['inactive'][] = $customer;
        } else {
            // By default, put in potential
            $segments['potential'][] = $customer;
        }
    }
    
    $response['data'] = [
        'all_customers' => $customerData,
        'segments' => $segments
    ];
}

/**
 * Generate overall analytics across multiple metrics
 */
function generateOverallAnalytics($db, &$response) {
    $data = [];
    
    // Sales trend over last 12 months
    $query = "
        SELECT 
            DATE_FORMAT(created_at, '%Y-%m') AS month,
            DATE_FORMAT(created_at, '%b %Y') AS month_name,
            SUM(total) AS total_sales,
            COUNT(*) AS transaction_count,
            AVG(total) AS average_sale
        FROM sales 
        WHERE created_at >= DATE_SUB(CURDATE(), INTERVAL 12 MONTH)
            AND deleted_at IS NULL
        GROUP BY DATE_FORMAT(created_at, '%Y-%m'), DATE_FORMAT(created_at, '%b %Y')
        ORDER BY month ASC
    ";
    
    $stmt = $db->prepare($query);
    $stmt->execute();
    $data['sales_trend'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Top 5 products by sales
    $query = "
        SELECT 
            p.id,
            p.name,
            SUM(si.quantity) as quantity_sold,
            SUM(si.subtotal) as revenue
        FROM sale_items si
        JOIN products p ON si.product_id = p.id
        JOIN sales s ON si.sale_id = s.id
        WHERE s.created_at >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)
            AND s.deleted_at IS NULL
            AND p.deleted_at IS NULL
        GROUP BY p.id, p.name
        ORDER BY quantity_sold DESC
        LIMIT 5
    ";
    
    $stmt = $db->prepare($query);
    $stmt->execute();
    $data['top_products'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Low stock alerts
    $query = "
        SELECT 
            p.id,
            p.name,
            c.name as category_name,
            p.stock as current_stock,
            p.min_stock
        FROM products p
        LEFT JOIN categories c ON p.category_id = c.id
        WHERE p.deleted_at IS NULL
            AND p.stock <= p.min_stock
        ORDER BY (p.stock / p.min_stock) ASC
        LIMIT 5
    ";
    
    $stmt = $db->prepare($query);
    $stmt->execute();
    $data['low_stock'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $response['data'] = $data;
}

/**
 * Calculate standard deviation of an array
 */
function standardDeviation(array $a, $sample = false) {
    $n = count($a);
    if ($n === 0) {
        return 0;
    }
    $mean = array_sum($a) / $n;
    $carry = 0.0;
    foreach ($a as $val) {
        $d = ((double) $val) - $mean;
        $carry += $d * $d;
    }
    if ($sample) {
        --$n;
    }
    return $n ? sqrt($carry / $n) : 0;
}

/**
 * Calculate confidence level based on growth rate volatility and forecast horizon
 */
function calculateConfidenceLevel($growthRates, $horizon) {
    // Calculate standard deviation of growth rates (volatility)
    $volatility = standardDeviation($growthRates);
    
    // Base confidence level
    $confidence = 0.9;
    
    // Reduce confidence based on volatility 
    // (higher volatility = lower confidence)
    $volatilityFactor = min(0.5, $volatility * 2);
    
    // Reduce confidence based on forecast horizon
    // (further in the future = lower confidence)
    $horizonFactor = min(0.3, $horizon * 0.1);
    
    // Calculate total confidence
    $totalConfidence = $confidence - $volatilityFactor - $horizonFactor;
    
    // Ensure confidence stays within 0.1 to 0.95 range
    return min(0.95, max(0.1, $totalConfidence));
} 