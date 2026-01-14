<?php
/**
 * Helper functions for supplier display
 */

/**
 * Lấy base path cho localhost (ví dụ: /vnmt) hoặc rỗng cho production
 */
function getBasePathForAssets(): string
{
    $serverName = $_SERVER['SERVER_NAME'] ?? '';
    $isLocalhost = (
        $serverName === 'localhost' || 
        strpos($serverName, 'localhost') !== false ||
        strpos($serverName, '127.0.0.1') !== false
    );
    
    if ($isLocalhost) {
        // Kiểm tra SCRIPT_NAME để xác định subdirectory
        $scriptName = $_SERVER['SCRIPT_NAME'] ?? '';
        if (strpos($scriptName, '/vnmt/') !== false) {
            return '/vnmt';
        }
    }
    
    return '';
}

/**
 * Xử lý đường dẫn logo nhà cung cấp
 * Tự động thêm base path cho localhost và tìm file trong thư mục assets/images/suppliers/
 * 
 * @param string|null $logo Đường dẫn logo từ database
 * @return string|null Đường dẫn logo đã được xử lý, hoặc null nếu không tìm thấy
 */
function getSupplierLogoPath(?string $logo): ?string
{
    if (empty($logo)) {
        return null;
    }
    
    $logo = trim($logo);
    
    // Nếu là URL đầy đủ (http/https), trả về nguyên
    if (strpos($logo, 'http://') === 0 || strpos($logo, 'https://') === 0) {
        return $logo;
    }
    
    // Lấy base path (ví dụ: /vnmt trên localhost)
    $basePath = getBasePathForAssets();
    
    // Xác định đường dẫn gốc của project
    $docRoot = $_SERVER['DOCUMENT_ROOT'] ?? '';
    $basePathFile = rtrim($docRoot, '/\\');
    
    // Nếu đã là đường dẫn relative đầy đủ (bắt đầu bằng /)
    if (strpos($logo, '/') === 0) {
        // Thêm base path vào đường dẫn cho URL (ví dụ: /vnmt/assets/images/suppliers/...)
        $urlPath = $basePath . $logo;
        
        // Kiểm tra file có tồn tại không (không cần base path trong file system)
        $filePath = $basePathFile . str_replace('/', DIRECTORY_SEPARATOR, $logo);
        if (file_exists($filePath)) {
            return $urlPath;
        }
        
        // Nếu không tồn tại, thử tìm trong thư mục suppliers với tên file
        $filename = basename($logo);
        $suppliersPath = '/assets/images/suppliers/' . $filename;
        $suppliersUrlPath = $basePath . $suppliersPath;
        $suppliersFilePath = $basePathFile . str_replace('/', DIRECTORY_SEPARATOR, $suppliersPath);
        
        if (file_exists($suppliersFilePath)) {
            return $suppliersUrlPath;
        }
        
        // Trả về đường dẫn với base path dù không tồn tại
        // Browser sẽ xử lý lỗi 404 và onerror sẽ hiển thị placeholder
        return $urlPath;
    }
    
    // Nếu chỉ là tên file hoặc đường dẫn không bắt đầu bằng /
    // Tìm trong thư mục suppliers
    $filename = basename($logo);
    $suppliersPath = '/assets/images/suppliers/' . $filename;
    $suppliersUrlPath = $basePath . $suppliersPath;
    $suppliersFilePath = $basePathFile . str_replace('/', DIRECTORY_SEPARATOR, $suppliersPath);
    
    if (file_exists($suppliersFilePath)) {
        return $suppliersUrlPath;
    }
    
    // Nếu không tìm thấy, thử với đường dẫn đầy đủ từ logo
    if (strpos($logo, 'assets/images/suppliers/') !== false) {
        $fullPath = '/' . ltrim($logo, '/');
        $fullUrlPath = $basePath . $fullPath;
        $fullFilePath = $basePathFile . str_replace('/', DIRECTORY_SEPARATOR, $fullPath);
        if (file_exists($fullFilePath)) {
            return $fullUrlPath;
        }
    }
    
    // Cuối cùng, trả về đường dẫn với base path và /assets/images/suppliers/ prefix
    return $basePath . '/assets/images/suppliers/' . $filename;
}

