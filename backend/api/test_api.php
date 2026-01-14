<?php
/**
 * Test API endpoint
 * Truy cập trực tiếp: http://localhost:8080/vnmt/backend/api/test_api.php
 */

header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Test API</title>
    <style>
        body { font-family: Arial; padding: 20px; }
        .success { color: green; background: #d4edda; padding: 10px; border-radius: 5px; margin: 10px 0; }
        .error { color: red; background: #f8d7da; padding: 10px; border-radius: 5px; margin: 10px 0; }
        pre { background: #f5f5f5; padding: 15px; border-radius: 5px; overflow-x: auto; }
    </style>
</head>
<body>
    <h1>Test API Scrape Product</h1>
    
    <?php
    $url = 'http://localhost:8080/vnmt/backend/api/scrape_product.php';
    $data = json_encode(['url' => 'https://vnbuilding.vn/vat-lieu/longhi-armchairs-qau9n3']);
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'Content-Length: ' . strlen($data)
    ]);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $contentType = curl_getinfo($ch, CURLINFO_CONTENT_TYPE);
    $error = curl_error($ch);
    curl_close($ch);
    
    echo "<h2>Response:</h2>";
    echo "<p>HTTP Code: <strong>$httpCode</strong></p>";
    echo "<p>Content-Type: <strong>$contentType</strong></p>";
    
    if ($error) {
        echo "<div class='error'>CURL Error: $error</div>";
    }
    
    if (strpos($contentType, 'application/json') !== false || strpos($contentType, 'json') !== false) {
        echo "<div class='success'>✅ Response là JSON!</div>";
        $json = json_decode($response, true);
        if ($json) {
            echo "<pre>" . json_encode($json, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "</pre>";
        } else {
            echo "<div class='error'>❌ Không parse được JSON: " . json_last_error_msg() . "</div>";
            echo "<pre>" . htmlspecialchars(substr($response, 0, 500)) . "</pre>";
        }
    } else {
        echo "<div class='error'>❌ Response KHÔNG phải JSON! Content-Type: $contentType</div>";
        echo "<pre>" . htmlspecialchars(substr($response, 0, 1000)) . "</pre>";
    }
    ?>
    
    <hr>
    <p><a href="javascript:location.reload()">🔄 Refresh để test lại</a></p>
</body>
</html>
