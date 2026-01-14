<?php
/**
 * API: Scrape Product from VNBuilding.vn
 * Luôn trả về JSON
 */

// Tắt tất cả output không mong muốn
ob_start();
error_reporting(0);
ini_set('display_errors', 0);

// Set headers ngay lập tức
header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-cache, must-revalidate');
header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');

// Function để trả về JSON và dừng mọi thứ
function jsonResponse($data, $statusCode = 200) {
    // Xóa toàn bộ output buffer
    while (ob_get_level()) {
        ob_end_clean();
    }
    
    http_response_code($statusCode);
    echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit(0);
}

// Bắt đầu session
if (session_status() === PHP_SESSION_NONE) {
    @session_start();
}

// Khởi tạo session array
if (!isset($_SESSION['scraped_products'])) {
    $_SESSION['scraped_products'] = [];
}

// Lấy và parse input
$rawInput = @file_get_contents('php://input');
$input = @json_decode($rawInput, true);

// Nếu không có JSON, thử POST
if ($input === null && !empty($_POST)) {
    $input = $_POST;
}

// Nếu vẫn null, trả về lỗi
if ($input === null) {
    jsonResponse([
        'success' => false,
        'error' => 'Không nhận được dữ liệu. Vui lòng gửi JSON hoặc POST data.',
        'debug' => [
            'raw_input' => substr($rawInput, 0, 100),
            'post' => !empty($_POST)
        ]
    ], 400);
}

$action = isset($input['action']) ? $input['action'] : 'scrape';

// Xử lý delete action
if ($action === 'delete') {
    $index = isset($input['index']) ? intval($input['index']) : -1;
    if (isset($_SESSION['scraped_products'][$index])) {
        array_splice($_SESSION['scraped_products'], $index, 1);
        jsonResponse(['success' => true, 'message' => 'Đã xóa sản phẩm']);
    } else {
        jsonResponse(['success' => false, 'error' => 'Không tìm thấy sản phẩm'], 404);
    }
}

// Xử lý clear_all action
if ($action === 'clear_all') {
    $_SESSION['scraped_products'] = [];
    jsonResponse(['success' => true, 'message' => 'Đã xóa tất cả']);
}

// Xử lý update action
if ($action === 'update') {
    $index = isset($input['index']) ? intval($input['index']) : -1;
    $field = isset($input['field']) ? $input['field'] : '';
    $value = isset($input['value']) ? $input['value'] : '';
    
    if (isset($_SESSION['scraped_products'][$index])) {
        $_SESSION['scraped_products'][$index][$field] = $value;
        jsonResponse(['success' => true]);
    } else {
        jsonResponse(['success' => false, 'error' => 'Không tìm thấy sản phẩm'], 404);
    }
}

// Xử lý scrape action
$url = isset($input['url']) ? trim($input['url']) : '';

if (empty($url)) {
    jsonResponse(['success' => false, 'error' => 'URL không được để trống'], 400);
}

if (!filter_var($url, FILTER_VALIDATE_URL)) {
    jsonResponse(['success' => false, 'error' => 'URL không hợp lệ: ' . $url], 400);
}

if (strpos($url, 'vnbuilding.vn') === false) {
    jsonResponse(['success' => false, 'error' => 'URL phải từ vnbuilding.vn'], 400);
}

// Bắt đầu scrape
try {
    // Sử dụng cURL
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
    curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36');
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,*/*;q=0.8',
        'Accept-Language: vi-VN,vi;q=0.9,en-US;q=0.8,en;q=0.7',
        'Accept-Encoding: gzip, deflate, br',
        'Connection: keep-alive',
        'Upgrade-Insecure-Requests: 1',
        'Sec-Fetch-Dest: document',
        'Sec-Fetch-Mode: navigate',
        'Sec-Fetch-Site: none',
    ]);
    
    $html = @curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curlError = curl_error($ch);
    curl_close($ch);
    
    if ($html === false || !empty($curlError)) {
        throw new Exception('Không thể tải trang: ' . ($curlError ?: 'Lỗi không xác định'));
    }
    
    if ($httpCode !== 200) {
        throw new Exception('HTTP Error: ' . $httpCode);
    }
    
    // Kiểm tra HTML
    $htmlTrimmed = trim($html);
    if (strlen($htmlTrimmed) < 100) {
        throw new Exception('Response quá ngắn: ' . substr($htmlTrimmed, 0, 100));
    }
    
    if (strpos($htmlTrimmed, '<!DOCTYPE') === false && strpos($htmlTrimmed, '<html') === false && strpos($htmlTrimmed, '<body') === false) {
        throw new Exception('Không phải HTML. Response: ' . substr($htmlTrimmed, 0, 200));
    }
    
    // Parse HTML
    libxml_use_internal_errors(true);
    $dom = new DOMDocument('1.0', 'UTF-8');
    
    // Fix encoding
    if (function_exists('mb_convert_encoding')) {
        $html = mb_convert_encoding($html, 'HTML-ENTITIES', 'UTF-8');
    }
    
    @$dom->loadHTML('<?xml encoding="UTF-8">' . $html);
    libxml_clear_errors();
    
    $xpath = new DOMXPath($dom);
    
    // Khởi tạo product data
    $product = [
        'source_url' => $url,
        'scraped_at' => date('Y-m-d H:i:s')
    ];
    
    // Lấy tên sản phẩm
    $nameNodes = $xpath->query("//h1");
    if ($nameNodes->length > 0) {
        $product['name'] = trim($nameNodes->item(0)->textContent);
    } else {
        $titleNodes = $xpath->query("//title");
        if ($titleNodes->length > 0) {
            $title = $titleNodes->item(0)->textContent;
            $parts = explode(' - ', $title);
            $product['name'] = trim($parts[0]);
        } else {
            $product['name'] = 'Sản phẩm không có tên';
        }
    }
    
    // Tạo slug
    if (!empty($product['name'])) {
        $product['slug'] = createSlug($product['name']);
    }
    
    // Lấy thông tin chi tiết
    $fields = [
        'brand' => ['Brand', 'Thương hiệu'],
        'supplier_name' => ['Nhà cung cấp', 'Nhà phân phối'],
        'supplier_phone' => ['Điện thoại', 'Phone'],
        'manufacturer_origin' => ['Nơi sản xuất', 'Xuất xứ'],
        'application' => ['Ứng dụng'],
        'material_type' => ['Loại vật tư']
    ];
    
    foreach ($fields as $key => $labels) {
        foreach ($labels as $label) {
            // Tìm pattern: label: value hoặc <dt>label</dt><dd>value</dd>
            $patterns = [
                "//*[contains(text(), '$label:')]",
                "//dt[contains(text(), '$label')]/following-sibling::dd[1]",
                "//*[contains(text(), '$label')]/following-sibling::*[1]",
            ];
            
            foreach ($patterns as $pattern) {
                $nodes = $xpath->query($pattern);
                if ($nodes->length > 0) {
                    $text = trim($nodes->item(0)->textContent);
                    if (preg_match('/' . preg_quote($label, '/') . '[\s:]+(.+)/i', $text, $matches)) {
                        $value = trim($matches[1]);
                        if (!empty($value) && strlen($value) < 500) {
                            $product[$key] = $value;
                            break 2;
                        }
                    } elseif (strlen($text) > 0 && strlen($text) < 500 && strpos($text, $label) === false) {
                        $product[$key] = $text;
                        break 2;
                    }
                }
            }
        }
    }
    
    // Lấy supplier info
    $supplierSection = $xpath->query("//*[contains(@class, 'supplier') or contains(text(), 'Nhà cung cấp')]");
    if ($supplierSection->length > 0) {
        $supplierText = $supplierSection->item(0)->textContent;
        
        $supplierNameNodes = $xpath->query(".//h3 | .//h4 | .//strong | .//b", $supplierSection->item(0));
        if ($supplierNameNodes->length > 0 && empty($product['supplier_name'])) {
            $product['supplier_name'] = trim($supplierNameNodes->item(0)->textContent);
        }
        
        if (preg_match('/(\+84|0)[\d\s-]{9,}/', $supplierText, $matches) && empty($product['supplier_phone'])) {
            $product['supplier_phone'] = trim($matches[0]);
        }
    }
    
    // Lấy mô tả
    $descNodes = $xpath->query("//div[contains(@class, 'description')]//p | //article//p | //main//p");
    if ($descNodes->length > 0) {
        foreach ($descNodes as $node) {
            $desc = trim($node->textContent);
            if (strlen($desc) > 50) {
                $product['description'] = $desc;
                break;
            }
        }
    }
    
    // Lấy website
    $websiteNodes = $xpath->query("//a[contains(@href, 'www.')]/@href | //*[contains(text(), 'Website')]/following-sibling::*/a/@href");
    if ($websiteNodes->length > 0) {
        $product['website'] = $websiteNodes->item(0)->value;
    }
    
    // Lấy items (sản phẩm con)
    $product['items'] = [];
    $itemHeaders = $xpath->query("//h3 | //h4");
    $skipHeaders = ['danh sách sản phẩm', 'sản phẩm', 'thông tin vật tư'];
    
    foreach ($itemHeaders as $header) {
        $headerText = trim($header->textContent);
        $headerLower = mb_strtolower($headerText, 'UTF-8');
        
        if (in_array($headerLower, $skipHeaders) || strlen($headerText) < 2 || strlen($headerText) > 100) {
            continue;
        }
        
        $item = ['name' => $headerText];
        $parent = $header->parentNode;
        
        if ($parent) {
            $listItems = $xpath->query(".//ul/li | .//*[contains(text(), 'Bộ sưu tập') or contains(text(), 'Thành phần')]", $parent);
            
            foreach ($listItems as $li) {
                $liText = trim($li->textContent);
                if (preg_match('/(.+?)[\s:]+(.+)/', $liText, $matches)) {
                    $key = mb_strtolower(trim($matches[1]), 'UTF-8');
                    $value = trim($matches[2]);
                    
                    $keyMap = [
                        'bộ sưu tập' => 'collection',
                        'thành phần' => 'composition',
                        'hoàn thiện' => 'finishing',
                        'kích thước' => 'width',
                        'bảo hành' => 'warranty',
                        'giá bán' => 'price'
                    ];
                    
                    $mappedKey = isset($keyMap[$key]) ? $keyMap[$key] : str_replace(' ', '_', $key);
                    if (!empty($value) && $value !== 'File đính kèm' && $value !== 'Liên hệ') {
                        $item[$mappedKey] = $value;
                    }
                }
            }
        }
        
        if (!empty($item['name'])) {
            $product['items'][] = $item;
        }
    }
    
    // Lấy files
    $product['files'] = [];
    $fileTypes = ['2D', '3D', 'Guideline', 'Technical data sheet', 'TDS', 'Catalogue'];
    
    foreach ($fileTypes as $type) {
        $fileNodes = $xpath->query("//a[contains(., '$type')]");
        foreach ($fileNodes as $node) {
            $fileUrl = $node->getAttribute('href');
            if (empty($fileUrl) || $fileUrl === '#') continue;
            
            if (strpos($fileUrl, 'http') !== 0) {
                $fileUrl = (strpos($fileUrl, '/') === 0) 
                    ? 'https://vnbuilding.vn' . $fileUrl 
                    : 'https://vnbuilding.vn/' . $fileUrl;
            }
            
            $product['files'][] = [
                'type' => $type,
                'name' => trim($node->textContent) ?: $type,
                'url' => $fileUrl
            ];
        }
    }
    
    // Lấy ảnh
    $imgNodes = $xpath->query("//img[contains(@class, 'product')]/@src | //article//img[1]/@src | //main//img[1]/@src");
    if ($imgNodes->length > 0) {
        $imgSrc = $imgNodes->item(0)->value;
        if (strpos($imgSrc, 'http') !== 0) {
            $imgSrc = (strpos($imgSrc, '/') === 0) 
                ? 'https://vnbuilding.vn' . $imgSrc 
                : 'https://vnbuilding.vn/' . $imgSrc;
        }
        $product['featured_image'] = $imgSrc;
    }
    
    // Lưu vào session
    $_SESSION['scraped_products'][] = $product;
    
    jsonResponse([
        'success' => true,
        'product' => $product,
        'message' => 'Đã lấy dữ liệu thành công'
    ]);
    
} catch (Exception $e) {
    jsonResponse([
        'success' => false,
        'error' => $e->getMessage()
    ], 500);
}

// Function tạo slug
function createSlug($string) {
    if (empty($string)) return '';
    
    $string = mb_strtolower($string, 'UTF-8');
    
    $vietnamese = [
        'á' => 'a', 'à' => 'a', 'ả' => 'a', 'ã' => 'a', 'ạ' => 'a',
        'ă' => 'a', 'ắ' => 'a', 'ằ' => 'a', 'ẳ' => 'a', 'ẵ' => 'a', 'ặ' => 'a',
        'â' => 'a', 'ấ' => 'a', 'ầ' => 'a', 'ẩ' => 'a', 'ẫ' => 'a', 'ậ' => 'a',
        'đ' => 'd',
        'é' => 'e', 'è' => 'e', 'ẻ' => 'e', 'ẽ' => 'e', 'ẹ' => 'e',
        'ê' => 'e', 'ế' => 'e', 'ề' => 'e', 'ể' => 'e', 'ễ' => 'e', 'ệ' => 'e',
        'í' => 'i', 'ì' => 'i', 'ỉ' => 'i', 'ĩ' => 'i', 'ị' => 'i',
        'ó' => 'o', 'ò' => 'o', 'ỏ' => 'o', 'õ' => 'o', 'ọ' => 'o',
        'ô' => 'o', 'ố' => 'o', 'ồ' => 'o', 'ổ' => 'o', 'ỗ' => 'o', 'ộ' => 'o',
        'ơ' => 'o', 'ớ' => 'o', 'ờ' => 'o', 'ở' => 'o', 'ỡ' => 'o', 'ợ' => 'o',
        'ú' => 'u', 'ù' => 'u', 'ủ' => 'u', 'ũ' => 'u', 'ụ' => 'u',
        'ư' => 'u', 'ứ' => 'u', 'ừ' => 'u', 'ử' => 'u', 'ữ' => 'u', 'ự' => 'u',
        'ý' => 'y', 'ỳ' => 'y', 'ỷ' => 'y', 'ỹ' => 'y', 'ỵ' => 'y',
    ];
    
    $string = strtr($string, $vietnamese);
    $string = preg_replace('/[^a-z0-9-]/', '-', $string);
    $string = preg_replace('/-+/', '-', $string);
    $string = trim($string, '-');
    
    return $string;
}
?>
