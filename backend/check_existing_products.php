<?php
$config = require 'config.php';

try {
    $pdo = new PDO(
        "mysql:host={$config['db_host']};dbname={$config['db_name']};charset=utf8mb4",
        $config['db_user'],
        $config['db_pass'] ?? '',
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
    
    echo "=== KIỂM TRA SẢN PHẨM ĐÃ TỒN TẠI ===\n\n";
    
    // Kiểm tra sản phẩm của supplier 29
    $stmt = $pdo->prepare("SELECT id, name, slug, supplier_id FROM products WHERE supplier_id = ? LIMIT 5");
    $stmt->execute([29]);
    $products = $stmt->fetchAll();
    
    if (empty($products)) {
        echo "✅ Chưa có sản phẩm nào của supplier ID 29 (All Best Enterprise)\n";
        echo "→ Có thể import CSV ngay!\n\n";
    } else {
        echo "⚠️ ĐÃ CÓ " . count($products) . " sản phẩm của supplier ID 29:\n\n";
        foreach ($products as $p) {
            echo "  - ID: {$p['id']} | {$p['name']} | Slug: {$p['slug']}\n";
        }
        echo "\n→ Đây là lý do CSV bị bỏ qua (sản phẩm đã tồn tại)\n\n";
    }
    
    // Đếm tổng số sản phẩm
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM products");
    $total = $stmt->fetch()['total'];
    echo "📊 Tổng số sản phẩm trong database: $total\n";
    
} catch (PDOException $e) {
    echo "Lỗi: " . $e->getMessage() . "\n";
}
