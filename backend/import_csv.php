<?php
require __DIR__ . '/inc/db.php';
require __DIR__ . '/inc/auth.php';
$require_helpers = true;
require __DIR__ . '/inc/activity.php';
require_once __DIR__ . '/inc/helpers.php';

$pdo = getPDO();
$flash = ['type' => '', 'message' => ''];

// Product classifications
$productClassifications = [
    'Vật liệu',
    'Thiết Bị',
    'Công nghệ',
    'Cảnh quan'
];

// Get suppliers for dropdown
$suppliers = $pdo->query("SELECT id, name FROM suppliers ORDER BY name")->fetchAll();

// Get categories for dropdown
$categories = $pdo->query("SELECT id, name FROM categories ORDER BY name")->fetchAll();

// Handle CSV Upload
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['csv_file'])) {
    try {
        $file = $_FILES['csv_file'];
        
        // Validate file
        if ($file['error'] !== UPLOAD_ERR_OK) {
            throw new Exception("Lỗi tải file: " . $file['error']);
        }
        
        // Check file extension
        $filename = $file['name'];
        $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        if ($ext !== 'csv') {
            throw new Exception("Chỉ chấp nhận file CSV");
        }
        
        // Read CSV file
        $file_handle = fopen($file['tmp_name'], 'r');
        $header = fgetcsv($file_handle, 0, ',');
        
        if (!$header) {
            throw new Exception("File CSV rỗng hoặc không hợp lệ");
        }
        
        // Expected columns (name is required, others are optional)
        $expected_columns = [
            'name', 'name_en', 'slug', 'description', 'description_en', 'price', 
            'status', 'featured', 'images', 'supplier_id', 'category_id', 
            'manufacturer', 'origin', 'manufacturer_origin', 'material_type', 
            'application', 'applications', 'supplier_type', 'website', 
            'featured_image', 'product_function', 'category', 'thickness', 
            'color', 'warranty', 'stock', 'brand', 'classification'
        ];
        
        // Validate header
        $csv_columns = array_map('strtolower', array_map('trim', $header));
        
        // Map CSV columns to database fields
        $column_map = array_flip($csv_columns);
        
        $imported = 0;
        $skipped = 0;
        $errors = [];
        $row_num = 1;
        
        while (($row = fgetcsv($file_handle, 0, ',')) !== false) {
            $row_num++;
            
            try {
                // Skip empty rows
                if (count(array_filter($row)) === 0) {
                    continue;
                }
                
                // Build data array from CSV row
                $data = [];
                foreach ($csv_columns as $col => $idx) {
                    if (isset($row[$idx])) {
                        $data[$col] = trim($row[$idx]);
                    }
                }
                
                // Validate required fields
                if (empty($data['name'])) {
                    $errors[] = "Dòng $row_num: Tên sản phẩm không được để trống";
                    $skipped++;
                    continue;
                }
                
                // Check if product exists by name or slug
                $stmt = $pdo->prepare("SELECT id FROM products WHERE name = ? OR slug = ?");
                $temp_slug = !empty($data['slug']) ? $data['slug'] : createSlug($data['name']);
                $stmt->execute([$data['name'], $temp_slug]);
                $existing = $stmt->fetch();
                if ($existing) {
                    $errors[] = "Dòng $row_num: Sản phẩm '{$data['name']}' đã tồn tại";
                    $skipped++;
                    continue;
                }
                
                // Validate supplier_id if provided
                if (!empty($data['supplier_id'])) {
                    $stmt = $pdo->prepare("SELECT id FROM suppliers WHERE id = ?");
                    $stmt->execute([$data['supplier_id']]);
                    if (!$stmt->fetch()) {
                        $errors[] = "Dòng $row_num: Supplier ID '{$data['supplier_id']}' không tồn tại";
                        $skipped++;
                        continue;
                    }
                }
                
                // Validate category_id if provided
                if (!empty($data['category_id'])) {
                    $stmt = $pdo->prepare("SELECT id FROM categories WHERE id = ?");
                    $stmt->execute([$data['category_id']]);
                    if (!$stmt->fetch()) {
                        $errors[] = "Dòng $row_num: Category ID '{$data['category_id']}' không tồn tại";
                        $skipped++;
                        continue;
                    }
                }
                
                // Prepare data for insertion
                $name = $data['name'] ?? '';
                $name_en = $data['name_en'] ?? '';
                $base_slug = !empty($data['slug']) ? $data['slug'] : createSlug($name);
                $slug = ensureUniqueSlug($pdo, $base_slug, 'products', 'slug');
                $description = $data['description'] ?? '';
                $description_en = $data['description_en'] ?? '';
                $price = isset($data['price']) && is_numeric($data['price']) ? (float)$data['price'] : 0;
                $status = isset($data['status']) ? (in_array(strtolower($data['status']), ['1', 'true', 'yes', 'active']) ? 1 : 0) : 1;
                $featured = isset($data['featured']) ? (in_array(strtolower($data['featured']), ['1', 'true', 'yes', 'active']) ? 1 : 0) : 0;
                $images = $data['images'] ?? '';
                $supplier_id = !empty($data['supplier_id']) && is_numeric($data['supplier_id']) ? (int)$data['supplier_id'] : null;
                $category_id = !empty($data['category_id']) && is_numeric($data['category_id']) ? (int)$data['category_id'] : null;
                $manufacturer = $data['manufacturer'] ?? '';
                $origin = $data['origin'] ?? '';
                $manufacturer_origin = $data['manufacturer_origin'] ?? '';
                $material_type = $data['material_type'] ?? '';
                $application = $data['application'] ?? '';
                $applications = $data['applications'] ?? '';
                $supplier_type = $data['supplier_type'] ?? '';
                $website = $data['website'] ?? '';
                $featured_image = $data['featured_image'] ?? '';
                $product_function = $data['product_function'] ?? '';
                $category = $data['category'] ?? '';
                $thickness = $data['thickness'] ?? '';
                $color = $data['color'] ?? '';
                $warranty = $data['warranty'] ?? '';
                $stock = !empty($data['stock']) && is_numeric($data['stock']) ? (int)$data['stock'] : 0;
                $brand = $data['brand'] ?? '';
                $classification = $data['classification'] ?? '';
                
                // Insert product
                $sql = "INSERT INTO products (
                    name, name_en, slug, description, description_en, price, status, featured, 
                    images, supplier_id, category_id, manufacturer, origin, manufacturer_origin, 
                    material_type, application, applications, supplier_type, website, 
                    featured_image, product_function, category, thickness, color, warranty, 
                    stock, brand, classification, created_at
                ) VALUES (
                    ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW()
                )";
                
                $stmt = $pdo->prepare($sql);
                $stmt->execute([
                    $name, $name_en, $slug, $description, $description_en, $price, $status, $featured,
                    $images, $supplier_id, $category_id, $manufacturer, $origin, $manufacturer_origin,
                    $material_type, $application, $applications, $supplier_type, $website,
                    $featured_image, $product_function, $category, $thickness, $color, $warranty,
                    $stock, $brand, $classification
                ]);
                
                $imported++;
                
            } catch (Exception $e) {
                $errors[] = "Dòng $row_num: " . $e->getMessage();
                $skipped++;
            }
        }
        
        fclose($file_handle);
        
        $flash['type'] = 'success';
        $flash['message'] = "Nhập CSV thành công! Đã thêm $imported sản phẩm";
        if (!empty($errors)) {
            $flash['type'] = 'warning';
            $flash['message'] .= " (Bỏ qua $skipped dòng)";
        }
        
        // Log activity
        if ($imported > 0) {
            logActivity($_SESSION['user_id'] ?? null, 'import_csv_products', 'products', 0, json_encode(['count' => $imported]));
        }
        
    } catch (Exception $e) {
        $flash['type'] = 'danger';
        $flash['message'] = "Lỗi: " . $e->getMessage();
    }
}

// Generate CSV template download
if (isset($_GET['action']) && $_GET['action'] === 'download_template') {
    $filename = 'product_import_template_' . date('Y-m-d_His') . '.csv';
    
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    
    $output = fopen('php://output', 'w');
    
    // BOM for Excel UTF-8
    fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));
    
    // Header row
    fputcsv($output, [
        'name', 'name_en', 'slug', 'description', 'description_en', 'price', 'status', 'featured',
        'images', 'supplier_id', 'category_id', 'manufacturer', 'origin', 'manufacturer_origin',
        'material_type', 'application', 'applications', 'supplier_type', 'website',
        'featured_image', 'product_function', 'category', 'thickness', 'color', 'warranty',
        'stock', 'brand', 'classification'
    ]);
    
    // Example row
    fputcsv($output, [
        'Tên sản phẩm', 'Product Name', 'ten-san-pham', 'Mô tả sản phẩm', 'Product description',
        '100.00', '1', '0', 'url1.jpg|url2.jpg', '1', '1', 'Nhà sản xuất ABC', 'Việt Nam',
        'Tây Ban Nha', 'Vải bọc', 'Phòng khách', 'Phòng khách, Phòng ngủ', 'Nhà phân phối',
        'https://example.com', 'image.jpg', 'Chống thấm, chống mối', 'Vật liệu', '10mm',
        'Xanh', '12 tháng', '100', 'Brand XYZ', 'Vật liệu,Thiết Bị'
    ]);
    
    fclose($output);
    exit;
}

require __DIR__ . '/inc/header.php';
?>

<div class="container-fluid mt-5">
    <div class="row">
        <div class="col-md-10 offset-md-1">
            <h2>Nhập sản phẩm từ CSV</h2>
            
            <?php if ($flash['message']): ?>
                <div class="alert alert-<?php echo htmlspecialchars($flash['type'] === 'danger' ? 'danger' : ($flash['type'] === 'warning' ? 'warning' : 'success')); ?> alert-dismissible fade show" role="alert">
                    <?php echo htmlspecialchars($flash['message']); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>
            
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">Upload file CSV</h5>
                </div>
                <div class="card-body">
                    <form method="POST" enctype="multipart/form-data">
                        <div class="mb-3">
                            <label for="csv_file" class="form-label">Chọn file CSV</label>
                            <input type="file" class="form-control" id="csv_file" name="csv_file" accept=".csv" required>
                            <small class="form-text text-muted">
                                File phải có định dạng CSV. Chỉ cột <strong>name</strong> là bắt buộc, các cột khác tùy chọn. <a href="?action=download_template" target="_blank">Tải template</a> để xem đầy đủ các cột.
                            </small>
                        </div>
                        <div class="button-group">
                            <button type="submit" class="btn btn-success">
                                <i class="fas fa-upload"></i> Nhập CSV
                            </button>
                            <a href="?action=download_template" class="btn btn-info">
                                <i class="fas fa-download"></i> Tải template
                            </a>
                        </div>
                    </form>
                </div>
            </div>
            
            <!-- CSV Format Guide -->
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Hướng dẫn định dạng CSV</h5>
                </div>
                <div class="card-body">
                    <table class="table table-sm table-bordered">
                        <thead class="table-light">
                            <tr>
                                <th>Cột</th>
                                <th>Kiểu dữ liệu</th>
                                <th>Bắt buộc</th>
                                <th>Ghi chú</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td><strong>name</strong></td>
                                <td>Text</td>
                                <td><span class="badge bg-danger">Có</span></td>
                                <td>Tên sản phẩm (độc nhất)</td>
                            </tr>
                            <tr>
                                <td><strong>name_en</strong></td>
                                <td>Text</td>
                                <td><span class="badge bg-warning">Không</span></td>
                                <td>Tên sản phẩm tiếng Anh</td>
                            </tr>
                            <tr>
                                <td><strong>slug</strong></td>
                                <td>Text</td>
                                <td><span class="badge bg-warning">Không</span></td>
                                <td>Slug URL (tự động tạo nếu trống, đảm bảo unique)</td>
                            </tr>
                            <tr>
                                <td><strong>description</strong></td>
                                <td>Text</td>
                                <td><span class="badge bg-warning">Không</span></td>
                                <td>Mô tả chi tiết</td>
                            </tr>
                            <tr>
                                <td><strong>description_en</strong></td>
                                <td>Text</td>
                                <td><span class="badge bg-warning">Không</span></td>
                                <td>Mô tả tiếng Anh</td>
                            </tr>
                            <tr>
                                <td><strong>price</strong></td>
                                <td>Số thập phân</td>
                                <td><span class="badge bg-warning">Không</span></td>
                                <td>Giá (ví dụ: 100.50)</td>
                            </tr>
                            <tr>
                                <td><strong>status</strong></td>
                                <td>0/1 hoặc true/false</td>
                                <td><span class="badge bg-warning">Không</span></td>
                                <td>1, true, yes, active = kích hoạt; mặc định 1</td>
                            </tr>
                            <tr>
                                <td><strong>featured</strong></td>
                                <td>0/1 hoặc true/false</td>
                                <td><span class="badge bg-warning">Không</span></td>
                                <td>1, true, yes, active = nổi bật; mặc định 0</td>
                            </tr>
                            <tr>
                                <td><strong>images</strong></td>
                                <td>Text</td>
                                <td><span class="badge bg-warning">Không</span></td>
                                <td>URL hình ảnh, ngăn cách bởi | (pipe)</td>
                            </tr>
                            <tr>
                                <td><strong>supplier_id</strong></td>
                                <td>Số</td>
                                <td><span class="badge bg-warning">Không</span></td>
                                <td>ID nhà cung cấp (tìm trong danh sách bên dưới, sẽ validate)</td>
                            </tr>
                            <tr>
                                <td><strong>category_id</strong></td>
                                <td>Số</td>
                                <td><span class="badge bg-warning">Không</span></td>
                                <td>ID danh mục (tìm trong danh sách bên dưới, sẽ validate)</td>
                            </tr>
                            <tr>
                                <td><strong>manufacturer</strong></td>
                                <td>Text</td>
                                <td><span class="badge bg-warning">Không</span></td>
                                <td>Nhà sản xuất</td>
                            </tr>
                            <tr>
                                <td><strong>origin</strong></td>
                                <td>Text</td>
                                <td><span class="badge bg-warning">Không</span></td>
                                <td>Xuất xứ</td>
                            </tr>
                            <tr>
                                <td><strong>manufacturer_origin</strong></td>
                                <td>Text</td>
                                <td><span class="badge bg-warning">Không</span></td>
                                <td>Nguồn gốc nhà sản xuất</td>
                            </tr>
                            <tr>
                                <td><strong>material_type</strong></td>
                                <td>Text</td>
                                <td><span class="badge bg-warning">Không</span></td>
                                <td>Loại vật liệu</td>
                            </tr>
                            <tr>
                                <td><strong>application</strong></td>
                                <td>Text</td>
                                <td><span class="badge bg-warning">Không</span></td>
                                <td>Ứng dụng</td>
                            </tr>
                            <tr>
                                <td><strong>applications</strong></td>
                                <td>Text</td>
                                <td><span class="badge bg-warning">Không</span></td>
                                <td>Các ứng dụng (ngăn cách bởi ,)</td>
                            </tr>
                            <tr>
                                <td><strong>supplier_type</strong></td>
                                <td>Text</td>
                                <td><span class="badge bg-warning">Không</span></td>
                                <td>Loại nhà cung cấp</td>
                            </tr>
                            <tr>
                                <td><strong>website</strong></td>
                                <td>Text</td>
                                <td><span class="badge bg-warning">Không</span></td>
                                <td>Website sản phẩm</td>
                            </tr>
                            <tr>
                                <td><strong>featured_image</strong></td>
                                <td>Text</td>
                                <td><span class="badge bg-warning">Không</span></td>
                                <td>Ảnh đại diện chính</td>
                            </tr>
                            <tr>
                                <td><strong>product_function</strong></td>
                                <td>Text</td>
                                <td><span class="badge bg-warning">Không</span></td>
                                <td>Chức năng sản phẩm</td>
                            </tr>
                            <tr>
                                <td><strong>category</strong></td>
                                <td>Text</td>
                                <td><span class="badge bg-warning">Không</span></td>
                                <td>Danh mục (text)</td>
                            </tr>
                            <tr>
                                <td><strong>thickness</strong></td>
                                <td>Text</td>
                                <td><span class="badge bg-warning">Không</span></td>
                                <td>Độ dày</td>
                            </tr>
                            <tr>
                                <td><strong>color</strong></td>
                                <td>Text</td>
                                <td><span class="badge bg-warning">Không</span></td>
                                <td>Màu sắc</td>
                            </tr>
                            <tr>
                                <td><strong>warranty</strong></td>
                                <td>Text</td>
                                <td><span class="badge bg-warning">Không</span></td>
                                <td>Bảo hành</td>
                            </tr>
                            <tr>
                                <td><strong>stock</strong></td>
                                <td>Số</td>
                                <td><span class="badge bg-warning">Không</span></td>
                                <td>Số lượng tồn kho</td>
                            </tr>
                            <tr>
                                <td><strong>brand</strong></td>
                                <td>Text</td>
                                <td><span class="badge bg-warning">Không</span></td>
                                <td>Thương hiệu</td>
                            </tr>
                            <tr>
                                <td><strong>classification</strong></td>
                                <td>Text</td>
                                <td><span class="badge bg-warning">Không</span></td>
                                <td>Phân loại: Vật liệu, Thiết Bị, Công nghệ, Cảnh quan (ngăn cách bởi ,)</td>
                            </tr>
                        </tbody>
                    </table>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <h6 class="mt-4 mb-3">Danh sách nhà cung cấp (ID):</h6>
                            <div class="supplier-list">
                                <table class="table table-sm table-bordered">
                                    <thead class="table-light">
                                        <tr>
                                            <th>ID</th>
                                            <th>Tên nhà cung cấp</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($suppliers as $supplier): ?>
                                            <tr>
                                                <td><?php echo $supplier['id']; ?></td>
                                                <td><?php echo htmlspecialchars($supplier['name']); ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <h6 class="mt-4 mb-3">Danh sách danh mục (ID):</h6>
                            <div class="supplier-list">
                                <table class="table table-sm table-bordered">
                                    <thead class="table-light">
                                        <tr>
                                            <th>ID</th>
                                            <th>Tên danh mục</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($categories as $category): ?>
                                            <tr>
                                                <td><?php echo $category['id']; ?></td>
                                                <td><?php echo htmlspecialchars($category['name']); ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                    
                    <h6 class="mt-4 mb-2">Lưu ý:</h6>
                    <ul>
                        <li>Chỉ cột <strong>name</strong> là bắt buộc, tất cả các cột khác đều tùy chọn</li>
                        <li>Nếu không có giá trị, để trống hoặc bỏ qua cột đó</li>
                        <li>Slug sẽ tự động tạo từ tên nếu không cung cấp</li>
                        <li>Nhiều giá trị ngăn cách bởi dấu phẩy (,) hoặc pipe (|) tùy theo cột</li>
                        <li><strong>Tải template CSV</strong> để xem đầy đủ các cột và ví dụ</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.button-group {
    display: flex;
    gap: 10px;
}

.button-group .btn {
    flex: 1;
}

.supplier-list {
    max-height: 400px;
    overflow-y: auto;
}

@media (max-width: 768px) {
    .button-group {
        flex-direction: column;
    }
}
</style>

<?php require __DIR__ . '/inc/footer.php'; ?>
