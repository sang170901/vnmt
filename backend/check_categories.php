<?php
$config = require 'config.php';

try {
    $pdo = new PDO(
        "mysql:host={$config['db_host']};dbname={$config['db_name']};charset=utf8mb4",
        $config['db_user'],
        $config['db_pass'],
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
    
    echo "=== DANH SÁCH CATEGORIES ===\n\n";
    
    $stmt = $pdo->query("SELECT id, name FROM categories ORDER BY id LIMIT 20");
    $categories = $stmt->fetchAll();
    
    if (empty($categories)) {
        echo "⚠️ KHÔNG CÓ CATEGORY NÀO TRONG DATABASE!\n";
        echo "Cần tạo categories trước khi import sản phẩm.\n";
    } else {
        echo "ID\tTên Category\n";
        echo "----------------------------\n";
        foreach ($categories as $cat) {
            echo $cat['id'] . "\t" . $cat['name'] . "\n";
        }
    }
} catch (PDOException $e) {
    echo "Lỗi kết nối database: " . $e->getMessage() . "\n";
}
