<?php
/**
 * Website Configuration
 * Tự động detect môi trường (localhost hoặc production)
 */

// Detect environment
$isLocalhost = (
    $_SERVER['SERVER_NAME'] === 'localhost' || 
    $_SERVER['SERVER_ADDR'] === '127.0.0.1' ||
    strpos($_SERVER['SERVER_NAME'], 'localhost') !== false
);

// Base URL
if ($isLocalhost) {
    define('BASE_URL', 'http://localhost:8080/vnmt/');
    define('SITE_URL', 'http://localhost:8080/vnmt');
} else {
    define('BASE_URL', 'https://vnmaterials.com/');
    define('SITE_URL', 'https://vnmaterials.com');
}

// Site Information
define('SITE_NAME', 'VN Materials');
define('SITE_DESCRIPTION', 'Nền tảng kết nối vật liệu xây dựng & nhà cung cấp');
define('SITE_EMAIL', 'contact@vnmaterials.com');
define('SITE_PHONE', '1900 xxxx');

// Paths
define('ASSETS_PATH', BASE_URL . 'assets/');
define('IMAGES_PATH', BASE_URL . 'assets/images/');
define('CSS_PATH', BASE_URL . 'assets/css/');
define('JS_PATH', BASE_URL . 'assets/js/');

// Environment
define('IS_LOCALHOST', $isLocalhost);
define('ENVIRONMENT', $isLocalhost ? 'development' : 'production');

// Debug mode (chỉ bật trên localhost)
if ($isLocalhost) {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
    define('DEBUG_MODE', true);
} else {
    error_reporting(0);
    ini_set('display_errors', 0);
    define('DEBUG_MODE', false);
}

// Timezone
date_default_timezone_set('Asia/Ho_Chi_Minh');

// Load security functions
require_once __DIR__ . '/inc/security.php';

// Session settings (now handled by security.php)
// Session is started in security.php with secure settings

