<?php
$config = require 'config.php';

try {
    $pdo = new PDO(
        "mysql:host={$config['db_host']};dbname={$config['db_name']};charset=utf8mb4",
        $config['db_user'],
        $config['db_pass'] ?? '',
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
    
    echo "=== XÓA SẢN PHẨM CŨ CỦA SUPPLIER 29, 30, 31 ===\n\n";
    
    $supplier_ids = [29, 30, 31];
    $total_deleted = 0;
    
    foreach ($supplier_ids as $supplier_id) {
        // Đếm số sản phẩm
        $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM products WHERE supplier_id = ?");
        $stmt->execute([$supplier_id]);
        $count = $stmt->fetch()['count'];
        
        if ($count > 0) {
            // Xóa sản phẩm
            $stmt = $pdo->prepare("DELETE FROM products WHERE supplier_id = ?");
            $stmt->execute([$supplier_id]);
            
            echo "✅ Đã xóa $count sản phẩm của supplier ID $supplier_id\n";
            $total_deleted += $count;
        } else {
            echo "ℹ️  Supplier ID $supplier_id không có sản phẩm nào\n";
        }
    }
    
    echo "\n📊 Tổng cộng đã xóa: $total_deleted sản phẩm\n";
    echo "✅ Bây giờ bạn có thể import CSV mới!\n";
    
} catch (PDOException $e) {
    echo "❌ Lỗi: " . $e->getMessage() . "\n";
}
