<?php
// Get suppliers list without database connection requirement
// This will output a simple list we can work with

$suppliers_data = [
    // Add sample data in case database is not available
    ['id' => 1, 'name' => 'Sample Supplier 1', 'website' => ''],
];

// Try to get from database
try {
    require_once __DIR__ . '/inc/db.php';
    $pdo = getPDO();
    $stmt = $pdo->query('SELECT id, name, website, description FROM suppliers WHERE status = 1 ORDER BY id');
    $suppliers_data = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    // Use sample data if database fails
}

// Output as JSON for easy parsing
header('Content-Type: application/json; charset=utf-8');
echo json_encode($suppliers_data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
