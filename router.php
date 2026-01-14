<?php
/**
 * URL Router - Xử lý URL đẹp
 * Chuyển các URL đẹp thành các tham số PHP chuẩn
 */

// Get the requested URI
$requestUri = $_SERVER['REQUEST_URI'];

// Remove query string
$requestUri = strtok($requestUri, '?');

// Remove base path if exists (for localhost/vnmt/)
$basePath = '/vnmt';
if (strpos($requestUri, $basePath) === 0) {
    $requestUri = substr($requestUri, strlen($basePath));
}

// Remove leading and trailing slashes
$requestUri = trim($requestUri, '/');

// Parse URL segments
$segments = explode('/', $requestUri);

// Route: /san-pham/{slug-id} hoặc /product/{slug-id}
if (count($segments) >= 2 && in_array($segments[0], ['san-pham', 'product'])) {
    $slugWithId = $segments[1];
    
    // Extract ID from slug (format: "product-name-123")
    $parts = explode('-', $slugWithId);
    $id = end($parts);
    
    if (is_numeric($id)) {
        $_GET['id'] = (int)$id;
        require_once __DIR__ . '/product-detail.php';
        exit;
    }
}

// Route: /nha-cung-cap/{slug-id} hoặc /supplier/{slug-id}
if (count($segments) >= 2 && in_array($segments[0], ['nha-cung-cap', 'supplier'])) {
    $slugWithId = $segments[1];
    
    $parts = explode('-', $slugWithId);
    $id = end($parts);
    
    if (is_numeric($id)) {
        $_GET['id'] = (int)$id;
        require_once __DIR__ . '/supplier-detail.php';
        exit;
    }
}

// Route: /tin-tuc/{slug-id} hoặc /news/{slug-id}
if (count($segments) >= 2 && in_array($segments[0], ['tin-tuc', 'news'])) {
    $slugWithId = $segments[1];
    
    $parts = explode('-', $slugWithId);
    $id = end($parts);
    
    if (is_numeric($id)) {
        $_GET['id'] = (int)$id;
        $_GET['slug'] = $slugWithId;
        require_once __DIR__ . '/post.php';
        exit;
    }
}

// If no route matched, return 404
http_response_code(404);
require_once __DIR__ . '/404.php';
exit;
