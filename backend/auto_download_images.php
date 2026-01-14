<?php
/**
 * Script tự động tải hình ảnh sản phẩm và cập nhật CSV
 * Sử dụng mapping URL từ image_urls_mapping.php
 */

set_time_limit(300); // 5 phút

$image_base_dir = __DIR__ . '/../images/products/';
$csv_dir = __DIR__ . '/csv_data/';

// Load mapping URL
$image_urls = require __DIR__ . '/image_urls_mapping.php';

// Tạo thư mục gốc nếu chưa có
if (!is_dir($image_base_dir)) {
    mkdir($image_base_dir, 0755, true);
}

/**
 * Tải hình ảnh từ URL
 */
function downloadImage($url, $save_path) {
    $dir = dirname($save_path);
    if (!is_dir($dir)) {
        mkdir($dir, 0755, true);
    }
    
    // Sử dụng file_get_contents với context
    $context = stream_context_create([
        'http' => [
            'method' => 'GET',
            'header' => "User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36\r\n",
            'timeout' => 30,
            'follow_location' => 1,
        ],
        'ssl' => [
            'verify_peer' => false,
            'verify_peer_name' => false,
        ]
    ]);
    
    $image_data = @file_get_contents($url, false, $context);
    
    if ($image_data && strlen($image_data) > 1000) {
        file_put_contents($save_path, $image_data);
        return true;
    }
    
    return false;
}

/**
 * Xử lý CSV và tải hình ảnh
 */
function processCSV($csv_file, $supplier_id, $supplier_name, $image_urls) {
    global $image_base_dir;
    
    echo "\n╔════════════════════════════════════════════════════════╗\n";
    echo "║  Nhà cung cấp: $supplier_name (ID: $supplier_id)\n";
    echo "╚════════════════════════════════════════════════════════╝\n";
    
    if (!file_exists($csv_file)) {
        echo "❌ File không tồn tại: $csv_file\n";
        return;
    }
    
    // Đọc CSV
    $rows = [];
    $handle = fopen($csv_file, 'r');
    $header = fgetcsv($handle);
    
    // Tìm index các cột
    $name_index = array_search('name', $header);
    $slug_index = array_search('slug', $header);
    $images_index = array_search('images', $header);
    $featured_image_index = array_search('featured_image', $header);
    
    $count = 0;
    $success = 0;
    $updated_rows = [];
    
    while (($row = fgetcsv($handle)) !== false) {
        $count++;
        $product_name = $row[$name_index];
        $slug = $row[$slug_index];
        
        echo "\n[$count] $product_name\n";
        echo "    Slug: $slug\n";
        
        // Kiểm tra có URL mapping không
        if (isset($image_urls[$supplier_id][$slug])) {
            $urls = $image_urls[$supplier_id][$slug];
            $downloaded = false;
            
            foreach ($urls as $index => $url) {
                $image_filename = $slug . ($index > 0 ? "-$index" : '') . '.jpg';
                $image_path = $image_base_dir . $supplier_id . '/' . $image_filename;
                $image_url_for_db = '/images/products/' . $supplier_id . '/' . $image_filename;
                
                echo "    Đang tải: $url\n";
                
                if (downloadImage($url, $image_path)) {
                    echo "    ✅ Đã lưu: $image_filename (" . filesize($image_path) . " bytes)\n";
                    
                    // Cập nhật CSV
                    if ($index == 0) {
                        $row[$featured_image_index] = $image_url_for_db;
                        $row[$images_index] = $image_url_for_db;
                    } else {
                        // Thêm hình phụ vào cột images (phân cách bằng dấu phẩy)
                        $row[$images_index] .= ',' . $image_url_for_db;
                    }
                    
                    $downloaded = true;
                    $success++;
                } else {
                    echo "    ⚠️  Không tải được từ URL này\n";
                }
            }
            
            if (!$downloaded) {
                echo "    ❌ Không tải được hình ảnh nào\n";
            }
        } else {
            echo "    ⚠️  Chưa có URL mapping cho sản phẩm này\n";
        }
        
        $updated_rows[] = $row;
    }
    
    fclose($handle);
    
    // Ghi lại CSV
    $handle = fopen($csv_file, 'w');
    fputcsv($handle, $header);
    foreach ($updated_rows as $row) {
        fputcsv($handle, $row);
    }
    fclose($handle);
    
    echo "\n✅ Hoàn thành: $success hình ảnh / $count sản phẩm\n";
}

// Danh sách CSV cần xử lý
$csv_files = [
    ['file' => $csv_dir . 'supplier_24_a2_sweden.csv', 'id' => '24', 'name' => 'A2 Sweden Vietnam'],
    ['file' => $csv_dir . 'supplier_25_abtech.csv', 'id' => '25', 'name' => 'ABTECH'],
    ['file' => $csv_dir . 'supplier_27_atc_stone.csv', 'id' => '27', 'name' => 'ATC STONE'],
    ['file' => $csv_dir . 'supplier_29_all_best_enterprise.csv', 'id' => '29', 'name' => 'All Best Enterprise'],
    ['file' => $csv_dir . 'supplier_30_agc_glass.csv', 'id' => '30', 'name' => 'AGC Glass'],
    ['file' => $csv_dir . 'supplier_31_amy_grupo.csv', 'id' => '31', 'name' => 'AMY GRUPO'],
];

echo "\n";
echo "╔════════════════════════════════════════════════════════╗\n";
echo "║                                                        ║\n";
echo "║     TỰ ĐỘNG TẢI HÌNH ẢNH SẢN PHẨM                    ║\n";
echo "║                                                        ║\n";
echo "╚════════════════════════════════════════════════════════╝\n";

$total_start = microtime(true);

foreach ($csv_files as $csv_info) {
    processCSV($csv_info['file'], $csv_info['id'], $csv_info['name'], $image_urls);
}

$total_time = round(microtime(true) - $total_start, 2);

echo "\n";
echo "╔════════════════════════════════════════════════════════╗\n";
echo "║     HOÀN THÀNH                                         ║\n";
echo "╚════════════════════════════════════════════════════════╝\n";
echo "\n📁 Thư mục hình ảnh: $image_base_dir\n";
echo "📄 CSV đã được cập nhật với đường dẫn hình ảnh\n";
echo "⏱️  Thời gian: {$total_time}s\n";
echo "\n🎯 Bước tiếp theo:\n";
echo "   1. Kiểm tra thư mục: $image_base_dir\n";
echo "   2. Import CSV vào database\n";
echo "   3. Kiểm tra hiển thị hình ảnh trên web\n\n";
