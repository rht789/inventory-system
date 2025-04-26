<?php
header('Content-Type: application/json');

// This file should be placed in the SA directory:
// C:/xampp/htdocs/Inventory_management_System/SA/predict-demand.php

// Check if a file was uploaded
if (!isset($_FILES['sales_file']) || $_FILES['sales_file']['error'] !== UPLOAD_ERR_OK) {
    echo json_encode(['error' => 'No file uploaded or upload error']);
    exit;
}

// Validate the uploaded file
$uploaded_file = $_FILES['sales_file']['tmp_name'];
$csv_data = file($uploaded_file);
if (!$csv_data || count($csv_data) < 2) { // At least header + 1 row
    echo json_encode(['error' => 'Invalid or empty CSV file']);
    exit;
}

// Validate CSV format (ds,product,y)
$header = str_getcsv($csv_data[0]);
if ($header[0] !== 'ds' || $header[1] !== 'product' || $header[2] !== 'y') {
    echo json_encode(['error' => 'CSV must have "ds,product,y" header']);
    exit;
}

// Calculate total sales per product
$sales_by_product = [];
for ($i = 1; $i < count($csv_data); $i++) {
    $row = str_getcsv($csv_data[$i]);
    if (count($row) < 3) continue; // Skip malformed rows
    $product = $row[1];
    $sales = floatval($row[2]);
    
    if (!isset($sales_by_product[$product])) {
        $sales_by_product[$product] = 0;
    }
    $sales_by_product[$product] += $sales;
}

// Convert to array of objects and sort by total sales (descending)
$formatted_data = [];
foreach ($sales_by_product as $product => $total_sales) {
    $formatted_data[] = [
        'Product' => $product,
        'Total_Sales' => round($total_sales, 2)
    ];
}

// Sort by Total_Sales in descending order
usort($formatted_data, function($a, $b) {
    return $b['Total_Sales'] - $a['Total_Sales'];
});

echo json_encode($formatted_data);
?>