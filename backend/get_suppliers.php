<?php
require __DIR__ . '/inc/db.php';

$pdo = getPDO();
$suppliers = $pdo->query('SELECT id, name, website, email, phone FROM suppliers ORDER BY id')->fetchAll(PDO::FETCH_ASSOC);

echo "=== DANH SÁCH NHÀ CUNG CẤP ===\n\n";
echo str_pad("ID", 5) . " | " . str_pad("Tên nhà cung cấp", 50) . " | Website\n";
echo str_repeat("-", 120) . "\n";

foreach($suppliers as $s) {
    echo str_pad($s['id'], 5) . " | " . 
         str_pad($s['name'], 50) . " | " . 
         ($s['website'] ?: 'N/A') . "\n";
}

echo "\nTổng số: " . count($suppliers) . " nhà cung cấp\n";
