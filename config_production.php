<?php
/**
 * Production Configuration Template
 * Copy nội dung này vào backend/inc/db.php sau khi upload lên TenTen
 */

// ========================================
// DATABASE CONFIGURATION (PRODUCTION)
// ========================================

// ⚠️ QUAN TRỌNG: Cập nhật thông tin này sau khi tạo database trên TenTen
$config = [
    'host' => 'localhost',
    'dbname' => 'vnmater_db',         // ← Thay bằng database name từ TenTen
    'username' => 'vnmater_user',     // ← Thay bằng database username từ TenTen
    'password' => 'YOUR_PASSWORD',    // ← Thay bằng database password từ TenTen
    'charset' => 'utf8mb4'
];

// Database connection
try {
    $pdo = new PDO(
        "mysql:host={$config['host']};dbname={$config['dbname']};charset={$config['charset']}",
        $config['username'],
        $config['password'],
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
            PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4"
        ]
    );
} catch (PDOException $e) {
    // Production: Không hiển thị chi tiết lỗi
    error_log("Database Connection Error: " . $e->getMessage());
    die("Không thể kết nối database. Vui lòng thử lại sau.");
}

function getPDO() {
    global $pdo;
    return $pdo;
}

function getFrontendPDO() {
    return getPDO();
}

// ========================================
// SITE CONFIGURATION (PRODUCTION)
// ========================================

define('SITE_URL', 'https://vnmaterials.com');
define('SITE_NAME', 'VNMaterial');
define('SITE_DESCRIPTION', 'Nền tảng vật liệu xây dựng Việt Nam');

// ========================================
// SECURITY SETTINGS
// ========================================

// Disable error display in production
ini_set('display_errors', 0);
ini_set('display_startup_errors', 0);
error_reporting(E_ALL);

// Enable error logging
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/../../error.log');

// ========================================
// SESSION CONFIGURATION
// ========================================

if (session_status() === PHP_SESSION_NONE) {
    session_start([
        'cookie_httponly' => true,
        'cookie_secure' => true,  // HTTPS only
        'cookie_samesite' => 'Lax'
    ]);
}

