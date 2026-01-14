<?php
require __DIR__ . '/inc/db.php';

$pdo = getPDO();

// Get table structure
$stmt = $pdo->query("DESCRIBE products");
$columns = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo "=== PRODUCTS TABLE STRUCTURE ===\n\n";
foreach ($columns as $col) {
    echo sprintf("%-20s %-30s %-10s %-10s\n", 
        $col['Field'], 
        $col['Type'], 
        $col['Null'], 
        $col['Key']
    );
}

echo "\n=== CURRENT CSV TEMPLATE COLUMNS ===\n\n";
$csv_columns = ['name', 'slug', 'sku', 'description', 'price', 'status', 'featured', 'images', 'supplier_id', 'category_id', 'classification'];
foreach ($csv_columns as $col) {
    echo "- $col\n";
}
