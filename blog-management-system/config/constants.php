<?php
// Site configuration
define('SITE_NAME', 'Blog Management System');
define('SITE_URL', 'http://localhost/PersonalProjects/blog-management-system');
define('BASE_PATH', dirname(__DIR__));

// Database configuration
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'blog_management_system');

// File upload configuration
define('UPLOAD_DIR', BASE_PATH . '/uploads/');
define('MAX_FILE_SIZE', 2 * 1024 * 1024); // 2MB
define('ALLOWED_TYPES', ['image/jpeg', 'image/png', 'image/gif']);

// User roles
define('ROLE_ADMIN', 'admin');
define('ROLE_AUTHOR', 'author');
define('ROLE_USER', 'user');

// Default user avatar
define('DEFAULT_AVATAR', SITE_URL . '/assets/images/default-avatar.png');

// Timezone
date_default_timezone_set('UTC');

// Error reporting
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);