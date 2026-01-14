<?php
/**
 * Product Research Tool - Tìm kiếm và tạo CSV cho sản phẩm theo nhà cung cấp
 * 
 * Công cụ này giúp:
 * 1. Nhập thông tin nhà cung cấp
 * 2. Tìm kiếm sản phẩm trên web
 * 3. Tạo file CSV để import vào hệ thống
 */

require __DIR__ . '/inc/header.php';

// Danh sách nhà cung cấp mẫu (có thể cập nhật từ database khi cần)
$sample_suppliers = [
    ['id' => 1, 'name' => 'Nhà cung cấp mẫu 1'],
    ['id' => 2, 'name' => 'Nhà cung cấp mẫu 2'],
];

// Lấy danh sách nhà cung cấp từ database nếu có
try {
    require_once __DIR__ . '/inc/db.php';
    $pdo = getPDO();
    $suppliers = $pdo->query('SELECT id, name, website FROM suppliers ORDER BY name')->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    $suppliers = $sample_suppliers;
}

// Lấy danh sách categories
try {
    $categories = $pdo->query('SELECT id, name FROM categories ORDER BY name')->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    $categories = [];
}
?>

<div class="container-fluid mt-5">
    <div class="row">
        <div class="col-md-12">
            <h2><i class="fas fa-search"></i> Công cụ Nghiên cứu & Tạo CSV Sản phẩm</h2>
            <p class="text-muted">Tìm kiếm thông tin sản phẩm và tạo file CSV để import hàng loạt</p>
            
            <div class="card mb-4">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="fas fa-info-circle"></i> Hướng dẫn sử dụng</h5>
                </div>
                <div class="card-body">
                    <ol>
                        <li><strong>Chọn nhà cung cấp</strong> từ danh sách bên dưới</li>
                        <li><strong>Nhập thông tin sản phẩm</strong> vào form (có thể thêm nhiều sản phẩm)</li>
                        <li><strong>Tìm kiếm trên web</strong> để lấy thông tin chi tiết (tùy chọn)</li>
                        <li><strong>Tạo file CSV</strong> để import vào hệ thống</li>
                        <li><strong>Import CSV</strong> tại trang <a href="import_csv.php" target="_blank">Import CSV</a></li>
                    </ol>
                </div>
            </div>

            <!-- Form nhập liệu -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-edit"></i> Nhập thông tin sản phẩm</h5>
                </div>
                <div class="card-body">
                    <form id="productForm">
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label"><strong>Nhà cung cấp *</strong></label>
                                <select class="form-select" id="supplier_id" required>
                                    <option value="">-- Chọn nhà cung cấp --</option>
                                    <?php foreach ($suppliers as $supplier): ?>
                                        <option value="<?php echo $supplier['id']; ?>">
                                            <?php echo htmlspecialchars($supplier['name']); ?>
                                            <?php if (!empty($supplier['website'])): ?>
                                                (<?php echo htmlspecialchars($supplier['website']); ?>)
                                            <?php endif; ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label"><strong>Danh mục</strong></label>
                                <select class="form-select" id="category_id">
                                    <option value="">-- Chọn danh mục --</option>
                                    <?php foreach ($categories as $category): ?>
                                        <option value="<?php echo $category['id']; ?>">
                                            <?php echo htmlspecialchars($category['name']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>

                        <div id="productsContainer">
                            <!-- Product entries will be added here -->
                        </div>

                        <div class="mb-3">
                            <button type="button" class="btn btn-secondary" onclick="addProductRow()">
                                <i class="fas fa-plus"></i> Thêm sản phẩm
                            </button>
                        </div>

                        <hr>

                        <div class="d-flex gap-2">
                            <button type="button" class="btn btn-success" onclick="generateCSV()">
                                <i class="fas fa-file-csv"></i> Tạo file CSV
                            </button>
                            <button type="button" class="btn btn-info" onclick="searchOnline()">
                                <i class="fas fa-search"></i> Tìm kiếm trên web
                            </button>
                            <button type="button" class="btn btn-warning" onclick="clearForm()">
                                <i class="fas fa-eraser"></i> Xóa form
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Kết quả tìm kiếm -->
            <div class="card mb-4" id="searchResults" style="display: none;">
                <div class="card-header bg-info text-white">
                    <h5 class="mb-0"><i class="fas fa-search"></i> Kết quả tìm kiếm</h5>
                </div>
                <div class="card-body" id="searchResultsBody">
                    <!-- Search results will appear here -->
                </div>
            </div>

            <!-- Preview CSV -->
            <div class="card mb-4" id="csvPreview" style="display: none;">
                <div class="card-header bg-success text-white">
                    <h5 class="mb-0"><i class="fas fa-file-csv"></i> Preview CSV</h5>
                </div>
                <div class="card-body">
                    <pre id="csvContent" class="bg-light p-3 rounded" style="max-height: 400px; overflow-y: auto;"></pre>
                    <button type="button" class="btn btn-success" onclick="downloadCSV()">
                        <i class="fas fa-download"></i> Tải xuống CSV
                    </button>
                </div>
            </div>

            <!-- Danh sách nhà cung cấp -->
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-building"></i> Danh sách nhà cung cấp</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-sm table-bordered">
                            <thead class="table-light">
                                <tr>
                                    <th>ID</th>
                                    <th>Tên nhà cung cấp</th>
                                    <th>Website</th>
                                    <th>Thao tác</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($suppliers as $supplier): ?>
                                    <tr>
                                        <td><?php echo $supplier['id']; ?></td>
                                        <td><?php echo htmlspecialchars($supplier['name']); ?></td>
                                        <td>
                                            <?php if (!empty($supplier['website'])): ?>
                                                <a href="<?php echo htmlspecialchars($supplier['website']); ?>" target="_blank">
                                                    <?php echo htmlspecialchars($supplier['website']); ?>
                                                </a>
                                            <?php else: ?>
                                                <span class="text-muted">N/A</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <button class="btn btn-sm btn-primary" onclick="selectSupplier(<?php echo $supplier['id']; ?>)">
                                                <i class="fas fa-check"></i> Chọn
                                            </button>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
let productCounter = 0;
let csvData = [];

// CSV columns matching the import template
const csvColumns = [
    'name', 'name_en', 'slug', 'description', 'description_en', 'price', 'status', 'featured',
    'images', 'supplier_id', 'category_id', 'manufacturer', 'origin', 'manufacturer_origin',
    'material_type', 'application', 'applications', 'supplier_type', 'website',
    'featured_image', 'product_function', 'category', 'thickness', 'color', 'warranty',
    'stock', 'brand', 'classification'
];

function addProductRow() {
    productCounter++;
    const container = document.getElementById('productsContainer');
    const productDiv = document.createElement('div');
    productDiv.className = 'product-entry card mb-3';
    productDiv.id = `product-${productCounter}`;
    
    productDiv.innerHTML = `
        <div class="card-header d-flex justify-content-between align-items-center">
            <h6 class="mb-0">Sản phẩm #${productCounter}</h6>
            <button type="button" class="btn btn-sm btn-danger" onclick="removeProduct(${productCounter})">
                <i class="fas fa-trash"></i> Xóa
            </button>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Tên sản phẩm *</label>
                    <input type="text" class="form-control" data-field="name" data-product="${productCounter}" required>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Tên tiếng Anh</label>
                    <input type="text" class="form-control" data-field="name_en" data-product="${productCounter}">
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Slug (tự động nếu trống)</label>
                    <input type="text" class="form-control" data-field="slug" data-product="${productCounter}">
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Giá</label>
                    <input type="number" step="0.01" class="form-control" data-field="price" data-product="${productCounter}">
                </div>
                <div class="col-md-12 mb-3">
                    <label class="form-label">Mô tả</label>
                    <textarea class="form-control" rows="2" data-field="description" data-product="${productCounter}"></textarea>
                </div>
                <div class="col-md-12 mb-3">
                    <label class="form-label">Mô tả tiếng Anh</label>
                    <textarea class="form-control" rows="2" data-field="description_en" data-product="${productCounter}"></textarea>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Thương hiệu</label>
                    <input type="text" class="form-control" data-field="brand" data-product="${productCounter}">
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Nhà sản xuất</label>
                    <input type="text" class="form-control" data-field="manufacturer" data-product="${productCounter}">
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Xuất xứ</label>
                    <input type="text" class="form-control" data-field="origin" data-product="${productCounter}">
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Nguồn gốc nhà sản xuất</label>
                    <input type="text" class="form-control" data-field="manufacturer_origin" data-product="${productCounter}">
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Loại vật liệu</label>
                    <input type="text" class="form-control" data-field="material_type" data-product="${productCounter}">
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Màu sắc</label>
                    <input type="text" class="form-control" data-field="color" data-product="${productCounter}">
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Độ dày</label>
                    <input type="text" class="form-control" data-field="thickness" data-product="${productCounter}">
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Bảo hành</label>
                    <input type="text" class="form-control" data-field="warranty" data-product="${productCounter}">
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Tồn kho</label>
                    <input type="number" class="form-control" data-field="stock" data-product="${productCounter}">
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Website</label>
                    <input type="url" class="form-control" data-field="website" data-product="${productCounter}">
                </div>
                <div class="col-md-12 mb-3">
                    <label class="form-label">Hình ảnh (ngăn cách bởi |)</label>
                    <input type="text" class="form-control" data-field="images" data-product="${productCounter}" placeholder="url1.jpg|url2.jpg">
                </div>
                <div class="col-md-12 mb-3">
                    <label class="form-label">Ứng dụng (ngăn cách bởi ,)</label>
                    <input type="text" class="form-control" data-field="applications" data-product="${productCounter}" placeholder="Phòng khách, Phòng ngủ">
                </div>
                <div class="col-md-12 mb-3">
                    <label class="form-label">Phân loại (ngăn cách bởi ,)</label>
                    <input type="text" class="form-control" data-field="classification" data-product="${productCounter}" placeholder="Vật liệu, Thiết Bị">
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Trạng thái</label>
                    <select class="form-select" data-field="status" data-product="${productCounter}">
                        <option value="1" selected>Kích hoạt</option>
                        <option value="0">Không kích hoạt</option>
                    </select>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Nổi bật</label>
                    <select class="form-select" data-field="featured" data-product="${productCounter}">
                        <option value="0" selected>Không</option>
                        <option value="1">Có</option>
                    </select>
                </div>
            </div>
        </div>
    `;
    
    container.appendChild(productDiv);
}

function removeProduct(id) {
    const element = document.getElementById(`product-${id}`);
    if (element) {
        element.remove();
    }
}

function selectSupplier(supplierId) {
    document.getElementById('supplier_id').value = supplierId;
    alert('Đã chọn nhà cung cấp ID: ' + supplierId);
}

function generateCSV() {
    const supplierId = document.getElementById('supplier_id').value;
    const categoryId = document.getElementById('category_id').value;
    
    if (!supplierId) {
        alert('Vui lòng chọn nhà cung cấp!');
        return;
    }
    
    const products = [];
    const productEntries = document.querySelectorAll('.product-entry');
    
    if (productEntries.length === 0) {
        alert('Vui lòng thêm ít nhất một sản phẩm!');
        return;
    }
    
    productEntries.forEach(entry => {
        const product = {};
        const inputs = entry.querySelectorAll('[data-field]');
        
        inputs.forEach(input => {
            const field = input.getAttribute('data-field');
            product[field] = input.value || '';
        });
        
        // Add supplier and category
        product.supplier_id = supplierId;
        product.category_id = categoryId;
        
        // Check if name is provided
        if (product.name) {
            products.push(product);
        }
    });
    
    if (products.length === 0) {
        alert('Không có sản phẩm nào có tên. Vui lòng nhập tên sản phẩm!');
        return;
    }
    
    // Generate CSV
    csvData = products;
    let csv = csvColumns.join(',') + '\n';
    
    products.forEach(product => {
        const row = csvColumns.map(col => {
            const value = product[col] || '';
            // Escape quotes and wrap in quotes if contains comma
            if (value.includes(',') || value.includes('"') || value.includes('\n')) {
                return '"' + value.replace(/"/g, '""') + '"';
            }
            return value;
        });
        csv += row.join(',') + '\n';
    });
    
    // Show preview
    document.getElementById('csvContent').textContent = csv;
    document.getElementById('csvPreview').style.display = 'block';
    
    // Scroll to preview
    document.getElementById('csvPreview').scrollIntoView({ behavior: 'smooth' });
}

function downloadCSV() {
    if (csvData.length === 0) {
        alert('Chưa có dữ liệu CSV. Vui lòng tạo CSV trước!');
        return;
    }
    
    const csv = document.getElementById('csvContent').textContent;
    const blob = new Blob(['\ufeff' + csv], { type: 'text/csv;charset=utf-8;' });
    const link = document.createElement('a');
    const url = URL.createObjectURL(blob);
    
    const supplierId = document.getElementById('supplier_id').value;
    const timestamp = new Date().toISOString().slice(0, 19).replace(/:/g, '-');
    
    link.setAttribute('href', url);
    link.setAttribute('download', `products_supplier_${supplierId}_${timestamp}.csv`);
    link.style.visibility = 'hidden';
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
}

function searchOnline() {
    const supplierId = document.getElementById('supplier_id').value;
    if (!supplierId) {
        alert('Vui lòng chọn nhà cung cấp trước!');
        return;
    }
    
    const supplierName = document.getElementById('supplier_id').options[document.getElementById('supplier_id').selectedIndex].text;
    
    document.getElementById('searchResults').style.display = 'block';
    document.getElementById('searchResultsBody').innerHTML = `
        <div class="alert alert-info">
            <h6>Gợi ý tìm kiếm cho: ${supplierName}</h6>
            <p>Bạn có thể tìm kiếm sản phẩm trên các nguồn sau:</p>
            <ul>
                <li><a href="https://www.google.com/search?q=${encodeURIComponent(supplierName + ' sản phẩm')}" target="_blank">Google Search</a></li>
                <li><a href="https://vnbuilding.vn/search?q=${encodeURIComponent(supplierName)}" target="_blank">VNBuilding</a></li>
                <li><a href="https://vlxd.vn/search?q=${encodeURIComponent(supplierName)}" target="_blank">VLXD.vn</a></li>
            </ul>
            <p class="mb-0"><strong>Lưu ý:</strong> Sau khi tìm thấy thông tin, hãy điền vào form bên trên và tạo CSV.</p>
        </div>
    `;
    
    document.getElementById('searchResults').scrollIntoView({ behavior: 'smooth' });
}

function clearForm() {
    if (confirm('Bạn có chắc muốn xóa toàn bộ form?')) {
        document.getElementById('productsContainer').innerHTML = '';
        productCounter = 0;
        csvData = [];
        document.getElementById('csvPreview').style.display = 'none';
        document.getElementById('searchResults').style.display = 'none';
    }
}

// Add first product row on load
window.addEventListener('DOMContentLoaded', function() {
    addProductRow();
});
</script>

<style>
.product-entry {
    border-left: 4px solid #0d6efd;
}

.product-entry .card-header {
    background-color: #f8f9fa;
}

#csvContent {
    font-family: 'Courier New', monospace;
    font-size: 12px;
    white-space: pre-wrap;
    word-wrap: break-word;
}

.table-responsive {
    max-height: 500px;
    overflow-y: auto;
}
</style>

<?php require __DIR__ . '/inc/footer.php'; ?>
