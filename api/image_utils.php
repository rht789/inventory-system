<?php

/**
 * Resizes and optimizes an uploaded image
 * 
 * @param string $sourcePath Path to the source image
 * @param string $targetPath Path where the resized image should be saved
 * @param int $maxWidth Maximum width of the resized image
 * @param int $maxHeight Maximum height of the resized image
 * @param int $quality JPEG quality (0-100)
 * @return bool True if successful, false otherwise
 */
function resizeAndOptimizeImage($sourcePath, $targetPath, $maxWidth = 800, $maxHeight = 800, $quality = 85) {
    // Get image info
    $imageInfo = getimagesize($sourcePath);
    if ($imageInfo === false) {
        return false;
    }

    // Create image from file
    switch ($imageInfo[2]) {
        case IMAGETYPE_JPEG:
            $sourceImage = imagecreatefromjpeg($sourcePath);
            break;
        case IMAGETYPE_PNG:
            $sourceImage = imagecreatefrompng($sourcePath);
            break;
        case IMAGETYPE_GIF:
            $sourceImage = imagecreatefromgif($sourcePath);
            break;
        case IMAGETYPE_WEBP:
            $sourceImage = imagecreatefromwebp($sourcePath);
            break;
        default:
            return false;
    }

    if (!$sourceImage) {
        return false;
    }

    // Get original dimensions
    $width = imagesx($sourceImage);
    $height = imagesy($sourceImage);

    // Calculate new dimensions
    $ratio = min($maxWidth / $width, $maxHeight / $height);
    $newWidth = round($width * $ratio);
    $newHeight = round($height * $ratio);

    // Create new image
    $newImage = imagecreatetruecolor($newWidth, $newHeight);

    // Preserve transparency for PNG
    if ($imageInfo[2] === IMAGETYPE_PNG) {
        imagealphablending($newImage, false);
        imagesavealpha($newImage, true);
        $transparent = imagecolorallocatealpha($newImage, 255, 255, 255, 127);
        imagefilledrectangle($newImage, 0, 0, $newWidth, $newHeight, $transparent);
    }

    // Resize
    imagecopyresampled(
        $newImage, $sourceImage,
        0, 0, 0, 0,
        $newWidth, $newHeight,
        $width, $height
    );

    // Save the resized image
    $success = false;
    switch ($imageInfo[2]) {
        case IMAGETYPE_JPEG:
            $success = imagejpeg($newImage, $targetPath, $quality);
            break;
        case IMAGETYPE_PNG:
            $success = imagepng($newImage, $targetPath, round(9 * $quality / 100));
            break;
        case IMAGETYPE_GIF:
            $success = imagegif($newImage, $targetPath);
            break;
        case IMAGETYPE_WEBP:
            $success = imagewebp($newImage, $targetPath, $quality);
            break;
    }

    // Clean up
    imagedestroy($sourceImage);
    imagedestroy($newImage);

    return $success;
}

/**
 * Optimizes an uploaded image with specific dimensions based on type
 * 
 * @param string $sourcePath Path to the source image
 * @param string $targetPath Path where the optimized image should be saved
 * @param string $type Type of image ('product', 'profile', 'logo')
 * @return bool True if successful, false otherwise
 */
function optimizeImage($sourcePath, $targetPath, $type = 'product') {
    // Define dimensions based on type
    $dimensions = [
        'product' => ['width' => 800, 'height' => 800],
        'profile' => ['width' => 400, 'height' => 400],
        'logo' => ['width' => 300, 'height' => 300]
    ];

    // Get dimensions for the specified type, default to product if type not found
    $dim = $dimensions[$type] ?? $dimensions['product'];

    // Resize and optimize the image
    return resizeAndOptimizeImage(
        $sourcePath,
        $targetPath,
        $dim['width'],
        $dim['height'],
        85
    );
}