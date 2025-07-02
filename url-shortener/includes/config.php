<?php
session_start();

// Database configuration
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'url_shortener');

// Base URL of the application
define('BASE_URL', 'http://localhost/PersonalProjects/url-shortener/');

// Error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Create database connection
try {
    $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}

// Include functions
require_once 'functions.php';

// Handle AJAX requests
if (isset($_GET['action']) && $_GET['action'] === 'shorten' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json');
    
    try {
        $long_url = $_POST['long_url'];
        
        // Validate URL
        if (!filter_var($long_url, FILTER_VALIDATE_URL)) {
            echo json_encode(['success' => false, 'message' => 'Invalid URL format']);
            exit();
        }
        
        // Shorten URL
        $short_url = shortenURL($long_url, $pdo);
        $short_code = str_replace(BASE_URL, '', $short_url);
        
        echo json_encode([
            'success' => true,
            'short_url' => $short_url,
            'short_code' => $short_code
        ]);
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
    
    exit();
}
?>