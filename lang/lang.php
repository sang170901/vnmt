<?php
/**
 * VNMaterial - Multi-language System
 * Hệ thống đa ngôn ngữ cho toàn bộ website
 * 
 * Usage:
 * 1. Include this file at the top of your PHP page
 * 2. Use t('key') or trans('key') to get translation
 * 3. Use getCurrentLang() to get current language
 * 
 * Example:
 * <?php
 * require_once 'lang/lang.php';
 * echo t('nav_materials'); // Output: VẬT LIỆU or MATERIALS
 * ?>
 */

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Define available languages
define('AVAILABLE_LANGUAGES', ['vi', 'en']);
define('DEFAULT_LANGUAGE', 'vi');

// LANGUAGE SWITCHING DISABLED - Force Vietnamese only
// Detect language from URL path (/en/ prefix like vnbuilding.vn)
$detected_lang = DEFAULT_LANGUAGE;

/*
// Check if URL contains /en/ prefix
if (isset($_SERVER['REQUEST_URI'])) {
    $uri = $_SERVER['REQUEST_URI'];
    // Check for /vnmt/en/ or /en/ at the start
    if (preg_match('#/(en)/#', $uri, $matches)) {
        $detected_lang = 'en';
    }
}

// Also check query parameter ?lang=en (for backwards compatibility)
if (isset($_GET['lang']) && in_array($_GET['lang'], AVAILABLE_LANGUAGES)) {
    $detected_lang = $_GET['lang'];
}
*/

// Set session language - FORCED TO VIETNAMESE
$_SESSION['lang'] = 'vi'; // Force Vietnamese only

// Load language file
$current_lang = $_SESSION['lang'];
$lang_file = __DIR__ . '/' . $current_lang . '.php';

if (file_exists($lang_file)) {
    $translations = include($lang_file);
} else {
    // Fallback to default language
    $translations = include(__DIR__ . '/' . DEFAULT_LANGUAGE . '.php');
}

/**
 * Get translation by key
 * 
 * @param string $key Translation key
 * @param array $params Parameters to replace in translation (optional)
 * @return string Translated text
 */
function t($key, $params = []) {
    global $translations;
    $text = $translations[$key] ?? $key;
    
    // Replace parameters if provided
    if (!empty($params)) {
        foreach ($params as $param_key => $param_value) {
            $text = str_replace('{' . $param_key . '}', $param_value, $text);
        }
    }
    
    return $text;
}

/**
 * Alias for t() function
 */
function trans($key, $params = []) {
    return t($key, $params);
}

/**
 * Get current language code
 * 
 * @return string Current language code (vi, en)
 */
function getCurrentLang() {
    return $_SESSION['lang'] ?? DEFAULT_LANGUAGE;
}

/**
 * Get opposite language (for toggle button)
 * 
 * @return string Opposite language code
 */
function getOppositeLang() {
    $current = getCurrentLang();
    return $current === 'vi' ? 'en' : 'vi';
}

/**
 * Get language display name
 * 
 * @param string|null $lang Language code (null = current language)
 * @return string Display name (VI, EN)
 */
function getLangDisplay($lang = null) {
    $lang = $lang ?? getCurrentLang();
    return strtoupper($lang);
}

/**
 * Get full language name
 * 
 * @param string|null $lang Language code (null = current language)
 * @return string Full language name
 */
function getLangFullName($lang = null) {
    $lang = $lang ?? getCurrentLang();
    $names = [
        'vi' => 'Tiếng Việt',
        'en' => 'English'
    ];
    return $names[$lang] ?? $lang;
}

/**
 * Check if current language is Vietnamese
 * 
 * @return bool
 */
function isVietnamese() {
    return getCurrentLang() === 'vi';
}

/**
 * Check if current language is English
 * 
 * @return bool
 */
function isEnglish() {
    return getCurrentLang() === 'en';
}

/**
 * Get language URL parameter for links
 * 
 * @return string URL parameter or empty string
 */
function getLangParam() {
    $lang = getCurrentLang();
    return $lang !== DEFAULT_LANGUAGE ? "?lang=$lang" : '';
}

/**
 * Get language prefix for URLs (vnbuilding.vn style)
 * 
 * @param string|null $lang Language code (null = current language)
 * @return string Language prefix (/en/ or empty)
 */
function getLangPrefix($lang = null) {
    $lang = $lang ?? getCurrentLang();
    return $lang === 'en' ? '/en' : '';
}

/**
 * Get base path (e.g., /vnmt/)
 * 
 * @return string Base path
 */
function getBasePath() {
    // Auto-detect environment: localhost vs production
    $serverName = $_SERVER['SERVER_NAME'] ?? '';
    $isLocalhost = (
        $serverName === 'localhost' || 
        strpos($serverName, 'localhost') !== false ||
        strpos($serverName, '127.0.0.1') !== false ||
        strpos($serverName, '::1') !== false
    );
    
    return $isLocalhost ? '/vnmt' : '';
}

/**
 * Build URL with language prefix (clean URLs without .php)
 * 
 * @param string $path Path without base (e.g., 'products.php' or 'products')
 * @param string|null $lang Language code (null = current language)
 * @return string Full URL with language prefix (without .php extension)
 */
function buildLangUrl($path, $lang = null) {
    $lang = $lang ?? getCurrentLang();
    $basePath = getBasePath();
    $langPrefix = getLangPrefix($lang);
    
    // Remove leading slash from path if exists
    $path = ltrim($path, '/');
    
    // Remove .php extension for clean URLs
    $path = preg_replace('/\.php$/', '', $path);
    
    return $basePath . $langPrefix . '/' . $path;
}

/**
 * Get current page path (without language prefix and base path)
 * 
 * @return string Current page path
 */
function getCurrentPagePath() {
    $uri = $_SERVER['REQUEST_URI'] ?? '';
    
    // Remove query string
    $uri = strtok($uri, '?');
    
    // Remove base path
    $uri = str_replace(getBasePath(), '', $uri);
    
    // Remove language prefix
    $uri = preg_replace('#^/(en)/#', '/', $uri);
    
    // Remove leading slash
    $uri = ltrim($uri, '/');
    
    return $uri ?: 'index.php';
}

/**
 * Get language toggle URL (switch to opposite language)
 * 
 * @return string URL to toggle language
 */
function getLanguageToggleUrl() {
    $oppositeLang = getOppositeLang();
    $currentPage = getCurrentPagePath();
    
    return buildLangUrl($currentPage, $oppositeLang);
}

