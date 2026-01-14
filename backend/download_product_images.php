<?php
/**
 * Script tự động tải hình ảnh sản phẩm từ web
 * Đặt tên file theo slug và lưu vào thư mục đúng
 */

// Cấu hình
$image_base_dir = __DIR__ . '/../images/products/';
$csv_dir = __DIR__ . '/csv_data/';

// Tạo thư mục nếu chưa có
if (!is_dir($image_base_dir)) {
    mkdir($image_base_dir, 0755, true);
}

/**
 * Tải hình ảnh từ URL về local
 */
function downloadImage($url, $save_path) {
    // Kiểm tra URL hợp lệ
    if (empty($url) || !filter_var($url, FILTER_VALIDATE_URL)) {
        return false;
    }
    
    // Tạo thư mục nếu chưa có
    $dir = dirname($save_path);
    if (!is_dir($dir)) {
        mkdir($dir, 0755, true);
    }
    
    // Tải hình ảnh
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36');
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    
    $image_data = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($http_code == 200 && $image_data) {
        file_put_contents($save_path, $image_data);
        return true;
    }
    
    return false;
}

/**
 * Tìm hình ảnh sản phẩm trên Google Images
 */
function searchProductImage($product_name, $supplier_name) {
    // Tạo query tìm kiếm
    $query = urlencode($product_name . ' ' . $supplier_name);
    
    // Danh sách URL mẫu (trong thực tế cần API hoặc scraping)
    // Đây là placeholder, bạn cần thay bằng URL thực tế
    $placeholder_images = [
        'https://via.placeholder.com/800x600.png?text=' . urlencode($product_name),
    ];
    
    return $placeholder_images[0];
}

/**
 * Xử lý CSV và tải hình ảnh
 */
function processCSVImages($csv_file, $supplier_id, $supplier_name) {
    global $image_base_dir;
    
    echo "\n=== Xử lý: $csv_file ===\n";
    
    if (!file_exists($csv_file)) {
        echo "❌ File không tồn tại: $csv_file\n";
        return;
    }
    
    // Đọc CSV
    $rows = [];
    $handle = fopen($csv_file, 'r');
    $header = fgetcsv($handle);
    
    // Tìm vị trí các cột
    $name_index = array_search('name', $header);
    $slug_index = array_search('slug', $header);
    $images_index = array_search('images', $header);
    $featured_image_index = array_search('featured_image', $header);
    
    $count = 0;
    $success = 0;
    
    while (($row = fgetcsv($handle)) !== false) {
        $count++;
        $product_name = $row[$name_index];
        $slug = $row[$slug_index];
        
        echo "\n[$count] Xử lý: $product_name\n";
        
        // Tạo tên file từ slug
        $image_filename = $slug . '.jpg';
        $image_path = $image_base_dir . $supplier_id . '/' . $image_filename;
        $image_url_for_db = '/images/products/' . $supplier_id . '/' . $image_filename;
        
        // Tạo thư mục supplier nếu chưa có
        $supplier_dir = $image_base_dir . $supplier_id;
        if (!is_dir($supplier_dir)) {
            mkdir($supplier_dir, 0755, true);
        }
        
        // Tìm và tải hình ảnh
        $search_url = searchProductImage($product_name, $supplier_name);
        
        if (downloadImage($search_url, $image_path)) {
            echo "   ✅ Đã tải: $image_filename\n";
            $row[$images_index] = $image_url_for_db;
            $row[$featured_image_index] = $image_url_for_db;
            $success++;
        } else {
            echo "   ⚠️  Không tải được hình ảnh\n";
        }
        
        $rows[] = $row;
    }
    
    fclose($handle);
    
    // Ghi lại CSV với đường dẫn hình ảnh
    $handle = fopen($csv_file, 'w');
    fputcsv($handle, $header);
    foreach ($rows as $row) {
        fputcsv($handle, $row);
    }
    fclose($handle);
    
    echo "\n✅ Hoàn thành: $success/$count hình ảnh\n";
}

// Danh sách CSV cần xử lý
$csv_files = [
    ['file' => $csv_dir . 'supplier_24_a2_sweden.csv', 'id' => '24', 'name' => 'A2 Sweden'],
    ['file' => $csv_dir . 'supplier_25_abtech.csv', 'id' => '25', 'name' => 'ABTECH'],
    ['file' => $csv_dir . 'supplier_27_atc_stone.csv', 'id' => '27', 'name' => 'ATC STONE'],
];

echo "╔════════════════════════════════════════════════════════╗\n";
echo "║   SCRIPT TỰ ĐỘNG TẢI HÌNH ẢNH SẢN PHẨM                ║\n";
echo "╚════════════════════════════════════════════════════════╝\n";

foreach ($csv_files as $csv_info) {
    processCSVImages($csv_info['file'], $csv_info['id'], $csv_info['name']);
}

echo "\n╔════════════════════════════════════════════════════════╗\n";
echo "║   HOÀN THÀNH TẢI HÌNH ẢNH                             ║\n";
echo "╚════════════════════════════════════════════════════════╝\n";
echo "\nHình ảnh đã được lưu vào: $image_base_dir\n";
echo "CSV đã được cập nhật với đường dẫn hình ảnh.\n";
