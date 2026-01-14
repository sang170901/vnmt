<?php
require_once __DIR__ . '/inc/db.php';

try {
    $pdo = getPDO();
    $stmt = $pdo->query('SELECT id, name, website FROM suppliers WHERE status = 1 ORDER BY id LIMIT 50');
    $suppliers = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "=== TOP 50 NHÀ CUNG CẤP ===\n\n";
    echo str_pad("ID", 6) . " | " . str_pad("Tên nhà cung cấp", 60) . " | Website\n";
    echo str_repeat("-", 150) . "\n";
    
    foreach($suppliers as $s) {
        echo str_pad($s['id'], 6) . " | " . 
             str_pad(mb_substr($s['name'], 0, 60), 60) . " | " . 
             ($s['website'] ?: 'N/A') . "\n";
    }
    
    echo "\n\n=== HƯỚNG DẪN ===\n";
    echo "Chọn ID nhà cung cấp bạn muốn tạo file CSV.\n";
    echo "Ví dụ: Nếu muốn tạo cho 'CÔNG TY ABC' có ID = 5, hãy cho tôi biết ID = 5\n";
    
} catch (Exception $e) {
    echo "Lỗi: " . $e->getMessage() . "\n";
    echo "\nVui lòng khởi động XAMPP và MySQL trước!\n";
}
