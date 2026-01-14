<?php
/**
 * Translation Sync Detection
 * Tự động phát hiện khi content VI thay đổi và mark cần re-translate
 * 
 * Usage: Include trong form handlers
 */

require_once __DIR__ . '/../../lang/TranslationManager.php';

class TranslationSync {
    private static $pdo = null;
    
    /**
     * Hook: Được gọi TRƯỚC khi UPDATE
     * Phát hiện thay đổi và mark cần sync
     * 
     * @param string $table Table name
     * @param int $id Record ID
     * @param array $newData Data mới
     * @param bool $autoRetranslate Tự động re-translate ngay? (default: false)
     */
    public static function onBeforeUpdate($table, $id, $newData, $autoRetranslate = false) {
        try {
            $pdo = self::getPDO();
            
            // Get old data
            $stmt = $pdo->prepare("SELECT * FROM {$table} WHERE id = ?");
            $stmt->execute([$id]);
            $oldData = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$oldData) {
                return;
            }
            
            // Detect changes in translatable fields
            $hasChanges = self::detectChanges($oldData, $newData);
            
            if ($hasChanges) {
                // Mark for re-translation
                self::markForRetranslation($table, $id);
                
                // Optional: Auto re-translate ngay lập tức
                if ($autoRetranslate) {
                    self::autoRetranslate($table, $id, $newData);
                }
            }
            
        } catch (Exception $e) {
            error_log("Translation sync error: " . $e->getMessage());
        }
    }
    
    /**
     * Hook: Được gọi SAU khi INSERT
     * Tự động translate content mới
     * 
     * @param string $table Table name
     * @param int $id Record ID
     */
    public static function onAfterInsert($table, $id) {
        try {
            // Get inserted data
            $pdo = self::getPDO();
            $stmt = $pdo->prepare("SELECT * FROM {$table} WHERE id = ?");
            $stmt->execute([$id]);
            $data = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$data) {
                return;
            }
            
            // Auto translate new record
            self::autoRetranslate($table, $id, $data);
            
        } catch (Exception $e) {
            error_log("Translation sync error: " . $e->getMessage());
        }
    }
    
    /**
     * Detect nếu có thay đổi trong các field cần dịch
     */
    private static function detectChanges($oldData, $newData) {
        // Các field thường cần dịch
        $translatableFields = ['name', 'title', 'description', 'excerpt', 'content', 'subtitle'];
        
        foreach ($translatableFields as $field) {
            if (!isset($oldData[$field])) {
                continue;
            }
            
            $oldValue = $oldData[$field] ?? '';
            $newValue = $newData[$field] ?? '';
            
            if ($oldValue !== $newValue) {
                return true; // Có thay đổi
            }
        }
        
        return false; // Không có thay đổi
    }
    
    /**
     * Mark record cần re-translate
     */
    private static function markForRetranslation($table, $id) {
        $pdo = self::getPDO();
        
        // Clear all _en fields và mark pending
        $pdo->prepare("
            UPDATE {$table} 
            SET translation_status = 'pending'
            WHERE id = ?
        ")->execute([$id]);
        
        // Clear cache
        TranslationManager::clearCache();
    }
    
    /**
     * Auto re-translate ngay lập tức
     */
    private static function autoRetranslate($table, $id, $data) {
        $pdo = self::getPDO();
        
        // Get column info để tìm _en fields
        $columnsResult = $pdo->query("PRAGMA table_info({$table})");
        $columns = $columnsResult->fetchAll(PDO::FETCH_ASSOC);
        
        $updates = [];
        $params = [];
        
        foreach ($columns as $col) {
            if (strpos($col['name'], '_en') !== false) {
                $originalField = str_replace('_en', '', $col['name']);
                
                if (isset($data[$originalField]) && !empty($data[$originalField])) {
                    // Translate
                    $translated = self::translate($data[$originalField], 'en');
                    $updates[] = "{$col['name']} = ?";
                    $params[] = "[AUTO] " . $translated;
                }
            }
        }
        
        if (!empty($updates)) {
            $updates[] = "translation_status = 'auto'";
            $params[] = $id;
            
            $sql = "UPDATE {$table} SET " . implode(', ', $updates) . " WHERE id = ?";
            $pdo->prepare($sql)->execute($params);
        }
        
        // Clear cache
        TranslationManager::clearCache();
    }
    
    /**
     * Simple translation function
     */
    private static function translate($text, $targetLang) {
        // Check if Google Translate available
        if (function_exists('googleTranslate')) {
            try {
                return googleTranslate($text, 'vi', $targetLang);
            } catch (Exception $e) {
                error_log("Translation failed: " . $e->getMessage());
            }
        }
        
        // Fallback: Basic dictionary
        return self::basicTranslate($text);
    }
    
    /**
     * Basic translation với dictionary
     */
    private static function basicTranslate($text) {
        $dictionary = [
            'Gạch' => 'Tiles',
            'Xi măng' => 'Cement',
            'Thép' => 'Steel',
            'Sơn' => 'Paint',
            'Gỗ' => 'Wood',
            'Vật liệu' => 'Materials',
            'Thiết bị' => 'Equipment',
            'Công nghệ' => 'Technology',
            'Cảnh quan' => 'Landscape',
            'cao cấp' => 'premium',
            'chất lượng' => 'quality',
            'hiện đại' => 'modern',
        ];
        
        $translated = $text;
        foreach ($dictionary as $vi => $en) {
            $translated = str_ireplace($vi, $en, $translated);
        }
        
        return $translated;
    }
    
    /**
     * Get PDO connection
     */
    private static function getPDO() {
        if (self::$pdo === null) {
            if (function_exists('getPDO')) {
                self::$pdo = getPDO();
            } else {
                require_once __DIR__ . '/db.php';
                self::$pdo = getPDO();
            }
        }
        return self::$pdo;
    }
}

