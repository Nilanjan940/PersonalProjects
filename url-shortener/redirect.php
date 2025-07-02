<?php
require_once 'includes/config.php';

if (isset($_GET['code'])) {
    $short_code = $_GET['code'];
    $long_url = getLongURL($short_code, $pdo);
    
    if ($long_url) {
        header("Location: " . $long_url);
        exit();
    }
}

// If code doesn't exist or something went wrong
header("Location: 404.php");
exit();
?>