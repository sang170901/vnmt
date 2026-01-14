<?php
// Simple config for backend - đọc cấu hình DB từ file dùng chung db_env.php

// Đường dẫn tới file cấu hình môi trường ở thư mục gốc project
$envConfig = require __DIR__ . '/../db_env.php';

// Chọn cấu hình theo mode
$mode = $envConfig['mode'] ?? 'local'; // mặc định là local
$dbConfig = $envConfig[$mode] ?? $envConfig['local'];

return [
    // MySQL database settings
    'db_host' => $dbConfig['db_host'] ?? 'localhost',
    'db_name' => $dbConfig['db_name'] ?? 'vnmt_db',
    'db_user' => $dbConfig['db_user'] ?? 'root',
    'db_password' => $dbConfig['db_password'] ?? '',
    
    // Legacy SQLite path (not used anymore)
    'db_path' => __DIR__ . '/database.sqlite',
    
    // Admin settings
    'admin_email' => 'admin@vnmt.com',
    'admin_password' => 'admin123', // plaintext for prototype only
];