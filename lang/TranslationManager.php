<?php
/**
 * TranslationManager - Advanced Translation System with Caching
 * Hệ thống dịch thuật nâng cao với 3 layers: Memory Cache → Database → Auto-translate
 * 
 * @version 2.0
 * @date 2025-10-28
 */

class TranslationManager {
    /**
     * Memory cache - Fastest layer
     */
    private static $cache = [];
    
    /**
     * Database connection
     */
    private static $pdo = null;
    
    /**
     * Statistics
     */
    private static $stats = [
        'cache_hits' => 0,
        'db_hits' => 0,
        'auto_translates' => 0
    ];
    
    /**
     * Get translated content with 3-layer caching
     * 
     * @param array $record Database record
     * @param string $field Field name to translate
     * @return string Translated text
     */
    public static function get($record, $field) {
        if (empty($record)) {
            return '';
        }
        
        $lang = getCurrentLang();
        
        // Vietnamese → return original
        if ($lang === 'vi') {
            return $record[$field] ?? '';
        }
        
        // === LAYER 1: Memory Cache (0.01ms) ===
        $cacheKey = self::getCacheKey($record, $field, $lang);
        if (isset(self::$cache[$cacheKey])) {
            self::$stats['cache_hits']++;
            return self::$cache[$cacheKey];
        }
        
        // === LAYER 2: Database Column (10-50ms) ===
        $fieldTranslated = $field . '_' . $lang;
        if (isset($record[$fieldTranslated]) && !empty($record[$fieldTranslated])) {
            $value = self::cleanAutoPrefix($record[$fieldTranslated]);
            self::$cache[$cacheKey] = $value;
            self::$stats['db_hits']++;
            return $value;
        }
        
        // === LAYER 3: Auto-translate + Save (100-500ms, only once) ===
        if (!empty($record[$field])) {
            $translated = self::autoTranslate($record[$field], $lang);
            self::saveTranslation($record, $fieldTranslated, $translated);
            self::$cache[$cacheKey] = $translated;
            self::$stats['auto_translates']++;
            return $translated;
        }
        
        // Fallback
        return $record[$field] ?? '';
    }
    
    /**
     * Generate cache key
     */
    private static function getCacheKey($record, $field, $lang) {
        $table = $record['_table'] ?? 'unknown';
        $id = $record['id'] ?? 0;
        return "{$table}_{$id}_{$field}_{$lang}";
    }
    
    /**
     * Auto-translate text with marking
     * 
     * @param string $text Source text
     * @param string $targetLang Target language
     * @return string Translated text with [AUTO] prefix
     */
    private static function autoTranslate($text, $targetLang) {
        // Check if Google Translate function exists
        if (function_exists('googleTranslate')) {
            try {
                $translated = googleTranslate($text, 'vi', $targetLang);
                return "[AUTO] " . $translated;
            } catch (Exception $e) {
                error_log("Translation failed: " . $e->getMessage());
            }
        }
        
        // Fallback: Simple word replacement (basic dictionary)
        $translated = self::basicTranslate($text, $targetLang);
        return "[AUTO] " . $translated;
    }
    
    /**
     * Basic translation using dictionary (fallback)
     */
    private static function basicTranslate($text, $targetLang) {
        if ($targetLang !== 'en') {
            return $text; // Only support EN for now
        }
        
        $dictionary = [
            // Common construction materials
            'Gạch' => 'Tiles',
            'Xi măng' => 'Cement',
            'Thép' => 'Steel',
            'Sơn' => 'Paint',
            'Gỗ' => 'Wood',
            'Đá' => 'Stone',
            'Cát' => 'Sand',
            'Sàn' => 'Floor',
            'Tường' => 'Wall',
            'Mái' => 'Roof',
            
            // Descriptive words
            'cao cấp' => 'premium',
            'chất lượng' => 'quality',
            'hiện đại' => 'modern',
            'truyền thống' => 'traditional',
            'bền vững' => 'sustainable',
            'thân thiện' => 'friendly',
            'môi trường' => 'environment',
            
            // Common phrases
            'Vật liệu xây dựng' => 'Construction materials',
            'Thiết bị' => 'Equipment',
            'Công nghệ' => 'Technology',
            'Cảnh quan' => 'Landscape',
        ];
        
        $translated = $text;
        foreach ($dictionary as $vi => $en) {
            $translated = str_ireplace($vi, $en, $translated);
        }
        
        return $translated;
    }
    
    /**
     * Save translation to database
     * 
     * @param array $record Database record
     * @param string $field Field name (e.g., 'name_en')
     * @param string $value Translated value
     */
    private static function saveTranslation($record, $field, $value) {
        try {
            $pdo = self::getPDO();
            $table = $record['_table'] ?? self::guessTable($record);
            $id = $record['id'] ?? null;
            
            if (!$id || !$table) {
                return;
            }
            
            $sql = "UPDATE {$table} 
                    SET {$field} = ?, 
                        translation_status = 'auto',
                        updated_at = CURRENT_TIMESTAMP
                    WHERE id = ?";
            
            $pdo->prepare($sql)->execute([$value, $id]);
            
        } catch (Exception $e) {
            error_log("Failed to save translation: " . $e->getMessage());
        }
    }
    
    /**
     * Guess table name from record
     */
    private static function guessTable($record) {
        // Try to detect table from fields
        if (isset($record['supplier_id'])) return 'products';
        if (isset($record['category_id'])) return 'suppliers';
        if (isset($record['slug'])) return 'posts';
        return 'products'; // Default
    }
    
    /**
     * Remove [AUTO] prefix from translation
     */
    private static function cleanAutoPrefix($text) {
        return preg_replace('/^\[AUTO\]\s*/', '', $text);
    }
    
    /**
     * Get PDO connection
     */
    private static function getPDO() {
        if (self::$pdo === null) {
            if (function_exists('getFrontendPDO')) {
                self::$pdo = getFrontendPDO();
            } elseif (function_exists('getPDO')) {
                self::$pdo = getPDO();
            } else {
                require_once __DIR__ . '/../inc/db_frontend.php';
                self::$pdo = getFrontendPDO();
            }
        }
        return self::$pdo;
    }
    
    /**
     * Clear memory cache
     */
    public static function clearCache() {
        self::$cache = [];
    }
    
    /**
     * Get statistics
     */
    public static function getStats() {
        return self::$stats;
    }
    
    /**
     * Mark translation as reviewed/approved
     * 
     * @param string $table Table name
     * @param int $id Record ID
     * @param string $field Field name (e.g., 'name')
     * @param string $value New approved value
     */
    public static function approve($table, $id, $field, $value) {
        try {
            $pdo = self::getPDO();
            $fieldEn = $field . '_en';
            
            $sql = "UPDATE {$table} 
                    SET {$fieldEn} = ?, 
                        translation_status = 'reviewed',
                        updated_at = CURRENT_TIMESTAMP
                    WHERE id = ?";
            
            $pdo->prepare($sql)->execute([$value, $id]);
            
            // Clear cache for this item
            self::clearCache();
            
            return true;
        } catch (Exception $e) {
            error_log("Failed to approve translation: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Mark translation for re-translation
     */
    public static function markForRetranslation($table, $id) {
        try {
            $pdo = self::getPDO();
            
            $sql = "UPDATE {$table} 
                    SET translation_status = 'pending',
                        updated_at = CURRENT_TIMESTAMP
                    WHERE id = ?";
            
            $pdo->prepare($sql)->execute([$id]);
            
            // Clear cache
            self::clearCache();
            
            return true;
        } catch (Exception $e) {
            error_log("Failed to mark for retranslation: " . $e->getMessage());
            return false;
        }
    }
}

