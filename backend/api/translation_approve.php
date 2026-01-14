<?php
/**
 * API: Approve Translation
 * Approve và lưu translation đã được review
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
    
    if (!isset($input['table'], $input['id'], $input['translations'])) {
        throw new Exception('Missing required parameters');
    }
    
    $table = $input['table'];
    $id = intval($input['id']);
    $translations = $input['translations'];
    
    // Validate table name (security)
    $allowedTables = ['products', 'posts', 'suppliers', 'partners', 'sliders'];
    if (!in_array($table, $allowedTables)) {
        throw new Exception('Invalid table name');
    }
    
    // Get PDO
    $pdo = getPDO();
    
    // Build UPDATE query
    $sets = [];
    $params = [];
    
    foreach ($translations as $field => $value) {
        $fieldEn = $field . '_en';
        $sets[] = "{$fieldEn} = ?";
        $params[] = $value; // Remove [AUTO] prefix
    }
    
    // Add translation_status
    $sets[] = "translation_status = 'reviewed'";
    $sets[] = "updated_at = CURRENT_TIMESTAMP";
    
    // Add ID to params
    $params[] = $id;
    
    // Execute update
    $sql = "UPDATE {$table} SET " . implode(', ', $sets) . " WHERE id = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    
    // Clear cache
    TranslationManager::clearCache();
    
    // Log activity
    if (function_exists('log_activity')) {
        log_activity($_SESSION['user_id'], 'approve_translation', $table, $id, json_encode($translations));
    }
    
    echo json_encode([
        'success' => true,
        'message' => 'Translation approved successfully'
    ]);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}

