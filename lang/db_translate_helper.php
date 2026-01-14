<?php
/**
 * Database Translation Helper (Enhanced with TranslationManager)
 * Helper functions to get translated content from database based on current language
 * Now with 3-layer caching for better performance
 * 
 * @version 2.0
 * @date 2025-10-28
 */

require_once __DIR__ . '/lang.php';
require_once __DIR__ . '/TranslationManager.php';

/**
 * Get translated field value from database record
 * 
 * @param array $record Database record
 * @param string $field Field name (e.g., 'name', 'title', 'description')
 * @return string Translated value or original if translation not available
 */
function getTranslated($record, $field) {
    // Use new TranslationManager with caching
    return TranslationManager::get($record, $field);
}

/**
 * Get translated name
 */
function getTranslatedName($record) {
    return getTranslated($record, 'name');
}

/**
 * Get translated title
 */
function getTranslatedTitle($record) {
    return getTranslated($record, 'title');
}

/**
 * Get translated description
 */
function getTranslatedDescription($record) {
    return getTranslated($record, 'description');
}

/**
 * Get translated content
 */
function getTranslatedContent($record) {
    return getTranslated($record, 'content');
}

/**
 * Get translated excerpt
 */
function getTranslatedExcerpt($record) {
    return getTranslated($record, 'excerpt');
}

/**
 * Modify SQL SELECT to include English columns
 * 
 * @param string $table Table name
 * @param string $alias Table alias (optional)
 * @return string Modified SELECT clause
 */
function getTranslatableSelect($table, $alias = null) {
    $prefix = $alias ? "$alias." : '';
    
    $fields = [];
    
    switch ($table) {
        case 'products':
            $fields = [
                "{$prefix}*",
                "{$prefix}name_en",
                "{$prefix}description_en"
            ];
            break;
            
        case 'posts':
            $fields = [
                "{$prefix}*",
                "{$prefix}title_en",
                "{$prefix}excerpt_en",
                "{$prefix}content_en"
            ];
            break;
            
        case 'suppliers':
            $fields = [
                "{$prefix}*",
                "{$prefix}name_en",
                "{$prefix}description_en"
            ];
            break;
            
        case 'partners':
            $fields = [
                "{$prefix}*",
                "{$prefix}name_en"
            ];
            break;
            
        case 'sliders':
            $fields = [
                "{$prefix}*",
                "{$prefix}title_en",
                "{$prefix}subtitle_en",
                "{$prefix}description_en",
                "{$prefix}link_text_en"
            ];
            break;
            
        default:
            $fields = ["{$prefix}*"];
    }
    
    return implode(', ', $fields);
}

