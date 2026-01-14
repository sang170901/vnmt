<?php
/**
 * Security Headers and Functions
 * Include this file at the top of your PHP pages for enhanced security
 */

// Prevent direct access
if (!defined('SECURITY_INCLUDED')) {
    define('SECURITY_INCLUDED', true);
}

// Start secure session if not already started
if (session_status() === PHP_SESSION_NONE) {
    // Secure session configuration
    ini_set('session.cookie_httponly', 1);
    ini_set('session.cookie_secure', isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 1 : 0);
    ini_set('session.use_only_cookies', 1);
    ini_set('session.cookie_samesite', 'Strict');
    
    session_start();
    
    // Regenerate session ID periodically to prevent session fixation
    if (!isset($_SESSION['created'])) {
        $_SESSION['created'] = time();
    } else if (time() - $_SESSION['created'] > 1800) { // 30 minutes
        session_regenerate_id(true);
        $_SESSION['created'] = time();
    }
}

/**
 * Set security headers
 */
function setSecurityHeaders() {
    // Prevent clickjacking
    header('X-Frame-Options: SAMEORIGIN');
    
    // Prevent MIME type sniffing
    header('X-Content-Type-Options: nosniff');
    
    // Enable XSS protection
    header('X-XSS-Protection: 1; mode=block');
    
    // Referrer policy
    header('Referrer-Policy: strict-origin-when-cross-origin');
    
    // Remove server signature
    header_remove('X-Powered-By');
    
    // Content Security Policy (adjust based on your needs)
    // Allow Font Awesome from cdnjs.cloudflare.com
    $csp = "default-src 'self'; " .
           "script-src 'self' 'unsafe-inline' 'unsafe-eval' https://cdnjs.cloudflare.com https://cdn.jsdelivr.net; " .
           "style-src 'self' 'unsafe-inline' https://cdnjs.cloudflare.com https://fonts.googleapis.com; " .
           "font-src 'self' data: https://cdnjs.cloudflare.com https://fonts.gstatic.com; " .
           "img-src 'self' data: https:; " .
           "connect-src 'self';";
    header("Content-Security-Policy: $csp");
}

/**
 * Sanitize input data
 */
function sanitizeInput($data) {
    if (is_array($data)) {
        return array_map('sanitizeInput', $data);
    }
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
    return $data;
}

/**
 * Validate and sanitize email
 */
function sanitizeEmail($email) {
    return filter_var(trim($email), FILTER_SANITIZE_EMAIL);
}

/**
 * Validate email format
 */
function isValidEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

/**
 * Generate CSRF token
 */
function generateCSRFToken() {
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Verify CSRF token
 */
function verifyCSRFToken($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

/**
 * Escape output for HTML
 */
function escapeHtml($string) {
    return htmlspecialchars($string, ENT_QUOTES, 'UTF-8');
}

/**
 * Escape output for JavaScript
 */
function escapeJs($string) {
    return json_encode($string, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT);
}

/**
 * Rate limiting (simple implementation)
 */
function checkRateLimit($key, $maxRequests = 10, $timeWindow = 60) {
    if (!isset($_SESSION['rate_limit'])) {
        $_SESSION['rate_limit'] = [];
    }
    
    $now = time();
    $userKey = $key . '_' . ($_SERVER['REMOTE_ADDR'] ?? 'unknown');
    
    if (!isset($_SESSION['rate_limit'][$userKey])) {
        $_SESSION['rate_limit'][$userKey] = [
            'count' => 1,
            'reset' => $now + $timeWindow
        ];
        return true;
    }
    
    $limit = &$_SESSION['rate_limit'][$userKey];
    
    // Reset if time window passed
    if ($now > $limit['reset']) {
        $limit['count'] = 1;
        $limit['reset'] = $now + $timeWindow;
        return true;
    }
    
    // Check if limit exceeded
    if ($limit['count'] >= $maxRequests) {
        return false;
    }
    
    $limit['count']++;
    return true;
}

// Set security headers automatically
setSecurityHeaders();

