<?php
/**
 * URL Helper Functions
 * Tạo URL đẹp và SEO-friendly cho sản phẩm, bài viết, nhà cung cấp
 */

/**
 * Chuyển chuỗi tiếng Việt thành slug (URL-friendly)
 * VD: "Thiết bị an toàn điện ABTECH" -> "thiet-bi-an-toan-dien-abtech"
 */
function createSlug($str) {
    // Chuyển về chữ thường
    $str = mb_strtolower($str, 'UTF-8');
    
    // Bảng chuyển đổi ký tự tiếng Việt
    $vietnamese = [
        'à' => 'a', 'á' => 'a', 'ạ' => 'a', 'ả' => 'a', 'ã' => 'a',
        'â' => 'a', 'ầ' => 'a', 'ấ' => 'a', 'ậ' => 'a', 'ẩ' => 'a', 'ẫ' => 'a',
        'ă' => 'a', 'ằ' => 'a', 'ắ' => 'a', 'ặ' => 'a', 'ẳ' => 'a', 'ẵ' => 'a',
        'è' => 'e', 'é' => 'e', 'ẹ' => 'e', 'ẻ' => 'e', 'ẽ' => 'e',
        'ê' => 'e', 'ề' => 'e', 'ế' => 'e', 'ệ' => 'e', 'ể' => 'e', 'ễ' => 'e',
        'ì' => 'i', 'í' => 'i', 'ị' => 'i', 'ỉ' => 'i', 'ĩ' => 'i',
        'ò' => 'o', 'ó' => 'o', 'ọ' => 'o', 'ỏ' => 'o', 'õ' => 'o',
        'ô' => 'o', 'ồ' => 'o', 'ố' => 'o', 'ộ' => 'o', 'ổ' => 'o', 'ỗ' => 'o',
        'ơ' => 'o', 'ờ' => 'o', 'ớ' => 'o', 'ợ' => 'o', 'ở' => 'o', 'ỡ' => 'o',
        'ù' => 'u', 'ú' => 'u', 'ụ' => 'u', 'ủ' => 'u', 'ũ' => 'u',
        'ư' => 'u', 'ừ' => 'u', 'ứ' => 'u', 'ự' => 'u', 'ử' => 'u', 'ữ' => 'u',
        'ỳ' => 'y', 'ý' => 'y', 'ỵ' => 'y', 'ỷ' => 'y', 'ỹ' => 'y',
        'đ' => 'd',
        ' ' => '-', '/' => '-', '\\' => '-', '_' => '-',
        '.' => '', ',' => '', ':' => '', ';' => '', '!' => '', '?' => '',
        '(' => '', ')' => '', '[' => '', ']' => '', '{' => '', '}' => '',
        '"' => '', "'" => '', '`' => '', '~' => '', '@' => '', '#' => '',
        '$' => '', '%' => '', '^' => '', '&' => '', '*' => '', '+' => '',
        '=' => '', '|' => '', '<' => '', '>' => '',
    ];
    
    $str = strtr($str, $vietnamese);
    
    // Loại bỏ các ký tự không phải chữ cái, số, hoặc dấu gạch ngang
    $str = preg_replace('/[^a-z0-9\-]/', '', $str);
    
    // Loại bỏ nhiều dấu gạch ngang liên tiếp
    $str = preg_replace('/-+/', '-', $str);
    
    // Loại bỏ dấu gạch ngang ở đầu và cuối
    $str = trim($str, '-');
    
    return $str;
}

/**
 * Tạo URL đẹp cho sản phẩm
 * VD: /san-pham/thiet-bi-an-toan-dien-abtech-80
 */
function buildProductUrl($product, $lang = null) {
    global $current_lang;
    
    if (!$lang) {
        $lang = $current_lang ?? 'vi';
    }
    
    // Lấy tên sản phẩm
    $name = ($lang === 'en' && !empty($product['name_en'])) 
        ? $product['name_en'] 
        : $product['name'];
    
    // Tạo slug từ tên
    $slug = createSlug($name);
    
    // Thêm ID vào cuối để đảm bảo URL unique
    $id = $product['id'];
    
    // Prefix theo ngôn ngữ
    $prefix = ($lang === 'vi') ? 'san-pham' : 'product';
    
    // Base URL
    $baseUrl = BASE_URL;
    
    // Tạo URL đầy đủ
    $url = rtrim($baseUrl, '/') . "/{$prefix}/{$slug}-{$id}";
    
    return $url;
}

/**
 * Tạo URL đẹp cho nhà cung cấp
 * VD: /nha-cung-cap/abtech-company-50
 */
function buildSupplierUrl($supplier, $lang = null) {
    global $current_lang;
    
    if (!$lang) {
        $lang = $current_lang ?? 'vi';
    }
    
    // Lấy tên nhà cung cấp
    $name = ($lang === 'en' && !empty($supplier['name_en'])) 
        ? $supplier['name_en'] 
        : $supplier['name'];
    
    $slug = createSlug($name);
    $id = $supplier['id'];
    
    $prefix = ($lang === 'vi') ? 'nha-cung-cap' : 'supplier';
    $baseUrl = BASE_URL;
    
    $url = rtrim($baseUrl, '/') . "/{$prefix}/{$slug}-{$id}";
    
    return $url;
}

/**
 * Tạo URL đẹp cho bài viết/tin tức
 * VD: /tin-tuc/xu-huong-vat-lieu-xay-dung-2024-150
 */
function buildArticleUrl($article, $lang = null) {
    global $current_lang;
    
    if (!$lang) {
        $lang = $current_lang ?? 'vi';
    }
    
    // Lấy tiêu đề bài viết
    $title = ($lang === 'en' && !empty($article['title_en'])) 
        ? $article['title_en'] 
        : $article['title'];
    
    $slug = createSlug($title);
    $id = $article['id'];
    
    $prefix = ($lang === 'vi') ? 'tin-tuc' : 'news';
    $baseUrl = BASE_URL;
    
    $url = rtrim($baseUrl, '/') . "/{$prefix}/{$slug}-{$id}";
    
    return $url;
}
