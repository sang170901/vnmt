<?php
// Database connection for frontend (supplier pages)

function getFrontendPDO() {
    // Đọc cấu hình DB dùng chung
    $envConfig = require __DIR__ . '/../db_env.php';
    $mode = $envConfig['mode'] ?? 'local';
    $dbConfig = $envConfig[$mode] ?? $envConfig['local'];

    $host = $dbConfig['db_host'] ?? 'localhost';
    $dbname = $dbConfig['db_name'] ?? 'vnmt_db';
    $username = $dbConfig['db_user'] ?? 'root';
    $password = $dbConfig['db_password'] ?? '';
    
    try {
        $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        
        // Set UTF-8 encoding for Vietnamese characters
        $pdo->exec("SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci");
        
        return $pdo;
    } catch (Exception $e) {
        throw new Exception("Database connection failed: " . $e->getMessage());
    }
}
?>