<?php
require_once __DIR__ . '/image_utils.php';

// Set the products upload directory
$uploadDir = __DIR__ . '/../uploads/products/';

// Check if directory exists
if (!is_dir($uploadDir)) {
    die("Products upload directory not found.\n");
}

// Get all image files
$files = glob($uploadDir . '*.{jpg,jpeg,png,gif,webp}', GLOB_BRACE);

if (empty($files)) {
    die("No images found in the products directory.\n");
}

echo "Found " . count($files) . " images to process.\n";

$successCount = 0;
$failCount = 0;

foreach ($files as $file) {
    $filename = basename($file);
    echo "Processing: $filename\n";
    
    // Create a temporary file for the optimized version
    $tempFile = $uploadDir . 'temp_' . $filename;
    
    // Try to optimize the image
    if (optimizeImage($file, $tempFile, 'product')) {
        // If successful, replace the original with the optimized version
        if (rename($tempFile, $file)) {
            echo "✓ Successfully optimized: $filename\n";
            $successCount++;
        } else {
            echo "✗ Failed to replace original file: $filename\n";
            // Clean up temp file
            if (file_exists($tempFile)) {
                unlink($tempFile);
            }
            $failCount++;
        }
    } else {
        echo "✗ Failed to optimize: $filename\n";
        // Clean up temp file if it exists
        if (file_exists($tempFile)) {
            unlink($tempFile);
        }
        $failCount++;
    }
}

echo "\nOptimization complete!\n";
echo "Successfully processed: $successCount images\n";
echo "Failed to process: $failCount images\n"; 