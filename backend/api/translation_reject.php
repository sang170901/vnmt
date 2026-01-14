<?php
/**
 * API: Reject Translation
 * Mark translation for re-translation
 */

session_start();
header('Content-Type: application/json');

// Check authentication
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => 'Unauthorized']);
    exit;
}

require_once __DIR__ . '/../inc/db.php';
require_once __DIR__ . '/../../lang/TranslationManager.php';

try {
    // Get POST data
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!isset($input['table'], $input['id'])) {
        throw new Exception('Missing required parameters');
    }
    
    $table = $input['table'];
    $id = intval($input['id']);
    
    // Validate table name (security)
    $allowedTables = ['products', 'posts', 'suppliers', 'partners', 'sliders'];
    if (!in_array($table, $allowedTables)) {
        throw new Exception('Invalid table name');
    }
    
    // Get PDO
    $pdo = getPDO();
    
    // Get column info to find _en fields
    $columnsResult = $pdo->query("PRAGMA table_info({$table})");
    $columns = $columnsResult->fetchAll(PDO::FETCH_ASSOC);
    
    // Clear all _en fields
    $sets = [];
    foreach ($columns as $col) {
        if (strpos($col['name'], '_en') !== false) {
            $sets[] = "{$col['name']} = NULL";
        }
    }
    
    // Add status
    $sets[] = "translation_status = 'pending'";
    $sets[] = "updated_at = CURRENT_TIMESTAMP";
    
    // Execute update
    $sql = "UPDATE {$table} SET " . implode(', ', $sets) . " WHERE id = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$id]);
    
    // Clear cache
    TranslationManager::clearCache();
    
    // Log activity
    if (function_exists('log_activity')) {
        log_activity($_SESSION['user_id'], 'reject_translation', $table, $id, null);
    }
    
    echo json_encode([
        'success' => true,
        'message' => 'Marked for re-translation'
    ]);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}

