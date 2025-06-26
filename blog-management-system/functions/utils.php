<?php
require_once '../config/constants.php';

function sanitizeInput($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

function validateEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL);
}

function validatePassword($password) {
    // At least 8 characters, 1 uppercase, 1 lowercase, 1 number
    return preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d).{8,}$/', $password);
}

function generateSlug($string) {
    $slug = strtolower($string);
    $slug = preg_replace('/[^a-z0-9-]/', '-', $slug);
    $slug = preg_replace('/-+/', '-', $slug);
    $slug = trim($slug, '-');
    return $slug;
}

function uploadFile($file, $targetDir = UPLOAD_DIR) {
    if ($file['error'] !== UPLOAD_ERR_OK) {
        return ['success' => false, 'error' => 'File upload error'];
    }
    
    if ($file['size'] > MAX_FILE_SIZE) {
        return ['success' => false, 'error' => 'File too large'];
    }
    
    if (!in_array($file['type'], ALLOWED_TYPES)) {
        return ['success' => false, 'error' => 'Invalid file type'];
    }
    
    $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
    $filename = uniqid() . '.' . $ext;
    $targetPath = $targetDir . $filename;
    
    if (move_uploaded_file($file['tmp_name'], $targetPath)) {
        return ['success' => true, 'filename' => $filename];
    } else {
        return ['success' => false, 'error' => 'Failed to move uploaded file'];
    }
}

function deleteFile($filename, $targetDir = UPLOAD_DIR) {
    if ($filename && file_exists($targetDir . $filename)) {
        return unlink($targetDir . $filename);
    }
    return false;
}

function formatDate($dateString, $format = 'F j, Y') {
    $date = new DateTime($dateString);
    return $date->format($format);
}

function truncateText($text, $length = 100, $append = '...') {
    if (strlen($text) > $length) {
        $text = substr($text, 0, $length);
        $text = substr($text, 0, strrpos($text, ' ')) . $append;
    }
    return $text;
}

function getExcerpt($content, $length = 200) {
    $content = strip_tags($content);
    $content = preg_replace('/\s+/', ' ', $content);
    return truncateText($content, $length);
}
?>