<?php
// Script để download ảnh từ server
session_start();

// Check login
if (empty($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    http_response_code(403);
    die('Access denied');
}

if (!isset($_GET['path']) || empty($_GET['path'])) {
    http_response_code(400);
    die('Missing image path');
}

$imagePath = urldecode($_GET['path']);

// Bảo mật: chỉ cho phép path trong assets/images/products
$isValidPath = false;
$normalizedPath = '';

// Xử lý các dạng path khác nhau
if (strpos($imagePath, '/assets/images/products/') === 0) {
    // Path bắt đầu bằng /assets/images/products/
    $normalizedPath = ltrim($imagePath, '/');
    $isValidPath = true;
} elseif (strpos($imagePath, 'assets/images/products/') === 0) {
    // Path bắt đầu bằng assets/images/products/
    $normalizedPath = $imagePath;
    $isValidPath = true;
} elseif (strpos($imagePath, 'assets/images/products/') !== false) {
    // Path chứa assets/images/products/ ở đâu đó
    $normalizedPath = $imagePath;
    $isValidPath = true;
}

if (!$isValidPath) {
    http_response_code(403);
    die('Invalid image path: ' . htmlspecialchars($imagePath));
}

// Xây dựng đường dẫn file thực tế
$filePath = __DIR__ . '/../' . $normalizedPath;

// Kiểm tra file có tồn tại không
if (!file_exists($filePath)) {
    http_response_code(404);
    die('Image not found: ' . htmlspecialchars($filePath));
}

// Kiểm tra đây có phải là file ảnh không
$allowedExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
$extension = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));
if (!in_array($extension, $allowedExtensions)) {
    http_response_code(403);
    die('Invalid file type');
}

// Lấy tên file từ path
$filename = basename($filePath);

// Set headers để force download
header('Content-Type: application/octet-stream');
header('Content-Disposition: attachment; filename="' . $filename . '"');
header('Content-Length: ' . filesize($filePath));
header('Cache-Control: must-revalidate');
header('Pragma: public');

// Đọc và output file
readfile($filePath);
exit;
?>
