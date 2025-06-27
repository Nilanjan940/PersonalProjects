<?php
function saveProductImage($file, $product_id, $is_primary = true) {
    $upload_dir = __DIR__ . "/../../assets/uploads/products/$product_id/";
    
    if (!file_exists($upload_dir)) {
        mkdir($upload_dir, 0755, true);
        mkdir($upload_dir . 'additional', 0755);
    }

    $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    $filename = $is_primary ? 'primary.' . $extension : 'img_' . time() . '_' . bin2hex(random_bytes(4)) . '.' . $extension;
    $subdir = $is_primary ? '' : 'additional/';
    $target_path = $upload_dir . $subdir . $filename;

    $allowed_types = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
    if (!in_array($file['type'], $allowed_types)) {
        throw new Exception("Only JPG, PNG, GIF, and WebP images are allowed.");
    }

    if ($file['size'] > 2000000) {
        throw new Exception("Image size must be less than 2MB.");
    }

    if (!getimagesize($file['tmp_name'])) {
        throw new Exception("Uploaded file is not a valid image.");
    }

    if (!move_uploaded_file($file['tmp_name'], $target_path)) {
        throw new Exception("Failed to upload image.");
    }

    return "assets/uploads/products/$product_id/$subdir$filename";
}