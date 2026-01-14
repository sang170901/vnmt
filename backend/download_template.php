<?php
/**
 * Download Template CSV mẫu sản phẩm với UTF-8 BOM
 * Đảm bảo font chữ hiển thị đúng trong Excel
 */

session_start();

// Check login
if (empty($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    header('Location: login.php');
    exit;
}

// Xóa mọi output buffer để đảm bảo BOM ở đầu file
while (ob_get_level()) {
    ob_end_clean();
}

$filename = 'template_san_pham.csv';

// Set headers
header('Content-Type: text/csv; charset=UTF-8');
header('Content-Disposition: attachment; filename="' . $filename . '"');
header('Pragma: no-cache');
header('Expires: 0');

// Mở output stream
$fp = fopen('php://output', 'w');

// Ghi BOM để Excel hiển thị tiếng Việt đúng (PHẢI là ký tự đầu tiên)
fprintf($fp, "\xEF\xBB\xBF");

// Header - các cột mẫu
$headers = [
    'Tên sản phẩm (name)',
    'Mô tả (description)',
    'Giá (price)',
    'Hình đại diện - URL (featured_image)',
    'Hình ảnh - URL nhiều ảnh phân cách bởi dấu phẩy (images)',
    'Supplier ID (supplier_id)',
    'Category ID (category_id)',
    'Nhà sản xuất (manufacturer)',
    'Xuất xứ (origin)',
    'Loại vật tư (material_type)',
    'Ứng dụng (application)',
    'Website (website)',
    'Thương hiệu (brand)',
    'Phân loại (classification)',
    'Độ dày (thickness)',
    'Màu sắc (color)',
    'Bảo hành (warranty)',
    'File PDF - URL nhiều file phân cách bởi dấu phẩy (pdf_files)',
    'Ghi chú'
];

fputcsv($fp, $headers, ',', '"');

fclose($fp);
exit;
