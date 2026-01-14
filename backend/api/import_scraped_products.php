<?php
session_start();
require_once __DIR__ . '/../inc/db.php';
header('Content-Type: application/json');

if (!isset($_SESSION['scraped_products']) || empty($_SESSION['scraped_products'])) {
    echo json_encode(['success' => false, 'error' => 'Không có sản phẩm nào để import']);
    exit;
}

try {
    $pdo = getPDO();
    $pdo->beginTransaction();
    
    $imported = 0;
    $errors = [];
    
    foreach ($_SESSION['scraped_products'] as $index => $product) {
        try {
            // Download ảnh nếu có
            $featuredImage = null;
            if (!empty($product['featured_image'])) {
                $featuredImage = downloadImage($product['featured_image'], $product['slug']);
            }
            
            // Insert product
            $stmt = $pdo->prepare("
                INSERT INTO products (
                    name, slug, description, brand, 
                    manufacturer_origin, supplier_id, category_id,
                    featured_image, website, source_url, status, created_at
                ) VALUES (
                    :name, :slug, :description, :brand,
                    :manufacturer_origin, :supplier_id, :category_id,
                    :featured_image, :website, :source_url, 'active', NOW()
                )
            ");
            
            $stmt->execute([
                ':name' => $product['name'] ?? '',
                ':slug' => $product['slug'] ?? '',
                ':description' => $product['description'] ?? '',
                ':brand' => $product['brand'] ?? '',
                ':manufacturer_origin' => $product['manufacturer_origin'] ?? '',
                ':supplier_id' => $product['supplier_id'] ?? null,
                ':category_id' => $product['category_id'] ?? null,
                ':featured_image' => $featuredImage,
                ':website' => $product['website'] ?? '',
                ':source_url' => $product['source_url'] ?? ''
            ]);
            
            $productId = $pdo->lastInsertId();
            
            // Insert product items (as product_collections)
            if (!empty($product['items'])) {
                // Tạo collection cho product này
                $collectionStmt = $pdo->prepare("
                    INSERT INTO product_collections (
                        name, brand, supplier_name, supplier_phone, 
                        manufacturer_origin, items_count, files_count, created_at
                    ) VALUES (
                        :name, :brand, :supplier_name, :supplier_phone,
                        :manufacturer_origin, :items_count, :files_count, NOW()
                    )
                ");
                
                $collectionStmt->execute([
                    ':name' => $product['name'] ?? '',
                    ':brand' => $product['brand'] ?? '',
                    ':supplier_name' => $product['supplier_name'] ?? '',
                    ':supplier_phone' => $product['supplier_phone'] ?? '',
                    ':manufacturer_origin' => $product['manufacturer_origin'] ?? '',
                    ':items_count' => count($product['items']),
                    ':files_count' => count($product['files'] ?? [])
                ]);
                
                $collectionId = $pdo->lastInsertId();
                
                // Link product to collection
                $pdo->exec("UPDATE products SET collection_id = $collectionId WHERE id = $productId");
                
                // Insert items
                $itemStmt = $pdo->prepare("
                    INSERT INTO product_collection_items (
                        collection_id, name, composition, width, finishing, 
                        warranty, price, display_order, created_at
                    ) VALUES (
                        :collection_id, :name, :composition, :width, :finishing,
                        :warranty, :price, :display_order, NOW()
                    )
                ");
                
                foreach ($product['items'] as $itemIndex => $item) {
                    $itemStmt->execute([
                        ':collection_id' => $collectionId,
                        ':name' => $item['name'] ?? '',
                        ':composition' => $item['composition'] ?? null,
                        ':width' => $item['width'] ?? null,
                        ':finishing' => $item['finishing'] ?? null,
                        ':warranty' => $item['warranty'] ?? null,
                        ':price' => $item['price'] ?? null,
                        ':display_order' => $itemIndex + 1
                    ]);
                }
                
                // Insert files
                if (!empty($product['files'])) {
                    $fileStmt = $pdo->prepare("
                        INSERT INTO product_collection_files (
                            collection_id, file_type, file_name, file_url, created_at
                        ) VALUES (
                            :collection_id, :file_type, :file_name, :file_url, NOW()
                        )
                    ");
                    
                    foreach ($product['files'] as $file) {
                        // Download file nếu cần
                        $localUrl = $file['url'];
                        if (!empty($file['url']) && strpos($file['url'], 'http') === 0) {
                            $downloaded = downloadFile($file['url'], $product['slug'], $file['type']);
                            if ($downloaded) {
                                $localUrl = $downloaded;
                            }
                        }
                        
                        $fileStmt->execute([
                            ':collection_id' => $collectionId,
                            ':file_type' => $file['type'] ?? 'File',
                            ':file_name' => $file['name'] ?? '',
                            ':file_url' => $localUrl
                        ]);
                    }
                }
            }
            
            $imported++;
            
        } catch (Exception $e) {
            $errors[] = "Product {$index}: " . $e->getMessage();
        }
    }
    
    $pdo->commit();
    
    // Clear session sau khi import thành công
    $_SESSION['scraped_products'] = [];
    
    echo json_encode([
        'success' => true,
        'imported' => $imported,
        'errors' => $errors,
        'message' => "Đã import thành công $imported sản phẩm"
    ]);
    
} catch (Exception $e) {
    $pdo->rollBack();
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}

function downloadImage($url, $slug) {
    try {
        $uploadDir = __DIR__ . '/../../assets/images/products/';
        if (!file_exists($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }
        
        $ext = pathinfo(parse_url($url, PHP_URL_PATH), PATHINFO_EXTENSION);
        if (empty($ext)) {
            $ext = 'jpg';
        }
        
        $filename = $slug . '-' . time() . '.' . $ext;
        $filepath = $uploadDir . $filename;
        
        $imageData = @file_get_contents($url);
        if ($imageData !== false) {
            file_put_contents($filepath, $imageData);
            return 'assets/images/products/' . $filename;
        }
    } catch (Exception $e) {
        // Log error but don't fail
    }
    
    return null;
}

function downloadFile($url, $slug, $type) {
    try {
        $uploadDir = __DIR__ . '/../../assets/files/products/';
        if (!file_exists($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }
        
        $ext = pathinfo(parse_url($url, PHP_URL_PATH), PATHINFO_EXTENSION);
        if (empty($ext)) {
            $ext = 'pdf';
        }
        
        $filename = $slug . '-' . strtolower(str_replace(' ', '-', $type)) . '-' . time() . '.' . $ext;
        $filepath = $uploadDir . $filename;
        
        $fileData = @file_get_contents($url);
        if ($fileData !== false) {
            file_put_contents($filepath, $fileData);
            return 'assets/files/products/' . $filename;
        }
    } catch (Exception $e) {
        // Log error but don't fail
    }
    
    return null;
}
?>
