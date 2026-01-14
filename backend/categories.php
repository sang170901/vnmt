<?php
require __DIR__ . '/inc/db.php';
require __DIR__ . '/inc/auth.php';
require __DIR__ . '/inc/activity.php';
$pdo = getPDO();

$flash = ['type'=>'','message'=>''];

// Xử lý thêm danh mục con
if (isset($_POST['add_classification'])) {
    $category = $_POST['category'] ?? '';
    $classification = trim($_POST['classification'] ?? '');
    
    if (!empty($category) && !empty($classification)) {
        // Kiểm tra xem đã tồn tại chưa
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM products WHERE category = ? AND classification = ?");
        $stmt->execute([$category, $classification]);
        
        if ($stmt->fetchColumn() > 0) {
            $flash = ['type' => 'error', 'message' => 'Danh mục con này đã tồn tại!'];
        } else {
            $flash = ['type' => 'success', 'message' => "Danh mục con '$classification' đã được thêm. Hãy gán sản phẩm vào danh mục này."];
        }
    }
}

// Xử lý đổi tên danh mục con
if (isset($_POST['rename_classification'])) {
    $category = $_POST['category'] ?? '';
    $old_name = $_POST['old_name'] ?? '';
    $new_name = trim($_POST['new_name'] ?? '');
    
    if (!empty($category) && !empty($old_name) && !empty($new_name)) {
        $stmt = $pdo->prepare("UPDATE products SET classification = ? WHERE category = ? AND classification = ?");
        $stmt->execute([$new_name, $category, $old_name]);
        $count = $stmt->rowCount();
        
        log_activity($_SESSION['user']['id'] ?? null, 'rename_classification', 'category', null, "Đổi '$old_name' thành '$new_name' trong $category");
        
        $flash = ['type' => 'success', 'message' => "Đã đổi tên '$old_name' thành '$new_name' ($count sản phẩm)"];
    }
}

// Xử lý xóa danh mục con
if (isset($_POST['delete_classification'])) {
    $category = $_POST['category'] ?? '';
    $classification = $_POST['classification'] ?? '';
    
    if (!empty($category) && !empty($classification)) {
        // Set classification = NULL cho các sản phẩm
        $stmt = $pdo->prepare("UPDATE products SET classification = NULL WHERE category = ? AND classification = ?");
        $stmt->execute([$category, $classification]);
        $count = $stmt->rowCount();
        
        log_activity($_SESSION['user']['id'] ?? null, 'delete_classification', 'category', null, "Xóa '$classification' trong $category");
        
        $flash = ['type' => 'success', 'message' => "Đã xóa danh mục '$classification' ($count sản phẩm bị bỏ phân loại)"];
    }
}

// Xử lý di chuyển sản phẩm
if (isset($_POST['move_products'])) {
    $from_category = $_POST['from_category'] ?? '';
    $from_classification = $_POST['from_classification'] ?? '';
    $to_category = $_POST['to_category'] ?? '';
    $to_classification = $_POST['to_classification'] ?? '';
    
    if (!empty($from_category) && !empty($from_classification) && !empty($to_category)) {
        $stmt = $pdo->prepare("UPDATE products SET category = ?, classification = ? WHERE category = ? AND classification = ?");
        $stmt->execute([$to_category, $to_classification, $from_category, $from_classification]);
        $count = $stmt->rowCount();
        
        log_activity($_SESSION['user']['id'] ?? null, 'move_products', 'category', null, "Di chuyển $count sản phẩm từ [$from_category]$from_classification sang [$to_category]$to_classification");
        
        $flash = ['type' => 'success', 'message' => "Đã di chuyển $count sản phẩm"];
    }
}

require __DIR__ . '/inc/header.php';

// Lấy thống kê danh mục
$categories = ['vật liệu', 'thiết bị', 'cảnh quan', 'công nghệ'];
$categoryData = [];

foreach ($categories as $cat) {
    $stmt = $pdo->prepare("
        SELECT classification, COUNT(*) as count 
        FROM products 
        WHERE category = ? AND classification IS NOT NULL 
        GROUP BY classification 
        ORDER BY classification
    ");
    $stmt->execute([$cat]);
    $categoryData[$cat] = $stmt->fetchAll(PDO::FETCH_ASSOC);
}
?>

<style>
.category-section {
    background: white;
    padding: 24px;
    border-radius: 12px;
    margin-bottom: 24px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.08);
}

.category-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 20px;
    padding-bottom: 16px;
    border-bottom: 3px solid #3b82f6;
}

.category-title {
    font-size: 1.5rem;
    font-weight: 700;
    color: #1e293b;
    text-transform: uppercase;
}

.classification-list {
    display: grid;
    gap: 12px;
}

.classification-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 16px;
    background: #f8fafc;
    border-radius: 8px;
    border-left: 4px solid #3b82f6;
}

.classification-info {
    flex: 1;
}

.classification-name {
    font-weight: 600;
    color: #1e293b;
    margin-bottom: 4px;
}

.classification-count {
    font-size: 0.875rem;
    color: #64748b;
}

.classification-actions {
    display: flex;
    gap: 8px;
}

.modal {
    display: none;
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(0,0,0,0.5);
    z-index: 1000;
    align-items: center;
    justify-content: center;
}

.modal.active {
    display: flex;
}

.modal-content {
    background: white;
    padding: 32px;
    border-radius: 12px;
    max-width: 500px;
    width: 90%;
}

.modal-header {
    font-size: 1.25rem;
    font-weight: 700;
    margin-bottom: 20px;
    color: #1e293b;
}

.form-group {
    margin-bottom: 16px;
}

.form-group label {
    display: block;
    font-weight: 600;
    margin-bottom: 8px;
    color: #475569;
}

.form-group input,
.form-group select {
    width: 100%;
    padding: 10px;
    border: 2px solid #e2e8f0;
    border-radius: 6px;
    font-size: 14px;
}

.modal-actions {
    display: flex;
    gap: 12px;
    margin-top: 24px;
}
</style>

<div class="card">
    <h2 class="page-main-title">Quản lý Danh Mục</h2>
    
    <?php if(!empty($flash['message'])): ?>
        <div class="flash <?php echo $flash['type']==='success'?'success':'error' ?>">
            <?php echo htmlspecialchars($flash['message']) ?>
        </div>
    <?php endif; ?>
    
    <!-- Nút thêm danh mục con -->
    <div style="margin-bottom: 24px;">
        <button onclick="showAddModal()" class="btn" style="background: #059669;">
            <i class="fas fa-plus"></i> Thêm Danh Mục Con
        </button>
    </div>
    
    <!-- Hiển thị danh mục theo từng loại -->
    <?php foreach ($categories as $cat): ?>
        <div class="category-section">
            <div class="category-header">
                <div class="category-title">
                    <i class="fas fa-folder"></i> <?php echo $cat; ?>
                </div>
                <div style="color: #64748b; font-size: 0.875rem;">
                    <?php 
                    $total = array_sum(array_column($categoryData[$cat], 'count'));
                    echo count($categoryData[$cat]) . " danh mục con, $total sản phẩm";
                    ?>
                </div>
            </div>
            
            <div class="classification-list">
                <?php if (empty($categoryData[$cat])): ?>
                    <div style="text-align: center; padding: 32px; color: #94a3b8;">
                        Chưa có danh mục con nào
                    </div>
                <?php else: ?>
                    <?php foreach ($categoryData[$cat] as $item): ?>
                        <div class="classification-item">
                            <div class="classification-info">
                                <div class="classification-name">
                                    <?php echo htmlspecialchars($item['classification']); ?>
                                </div>
                                <div class="classification-count">
                                    <i class="fas fa-box"></i> <?php echo $item['count']; ?> sản phẩm
                                </div>
                            </div>
                            
                            <div class="classification-actions">
                                <button onclick="showRenameModal('<?php echo $cat; ?>', '<?php echo htmlspecialchars($item['classification'], ENT_QUOTES); ?>')" 
                                        class="small-btn" style="background: #3b82f6;">
                                    <i class="fas fa-edit"></i> Đổi tên
                                </button>
                                
                                <button onclick="showMoveModal('<?php echo $cat; ?>', '<?php echo htmlspecialchars($item['classification'], ENT_QUOTES); ?>')" 
                                        class="small-btn" style="background: #8b5cf6;">
                                    <i class="fas fa-exchange-alt"></i> Di chuyển
                                </button>
                                
                                <button onclick="deleteClassification('<?php echo $cat; ?>', '<?php echo htmlspecialchars($item['classification'], ENT_QUOTES); ?>', <?php echo $item['count']; ?>)" 
                                        class="small-btn warn">
                                    <i class="fas fa-trash"></i> Xóa
                                </button>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    <?php endforeach; ?>
</div>

<!-- Modal Thêm danh mục con -->
<div id="addModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">Thêm Danh Mục Con</div>
        <form method="post">
            <div class="form-group">
                <label>Danh mục chính *</label>
                <select name="category" required>
                    <option value="">-- Chọn --</option>
                    <?php foreach ($categories as $cat): ?>
                        <option value="<?php echo $cat; ?>"><?php echo ucfirst($cat); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div class="form-group">
                <label>Tên danh mục con *</label>
                <input type="text" name="classification" required placeholder="VD: Xi măng, Gạch ốp lát...">
            </div>
            
            <div class="modal-actions">
                <button type="submit" name="add_classification" class="btn" style="background: #059669;">
                    <i class="fas fa-plus"></i> Thêm
                </button>
                <button type="button" onclick="hideModal('addModal')" class="btn" style="background: #64748b;">
                    Hủy
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Đổi tên -->
<div id="renameModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">Đổi Tên Danh Mục Con</div>
        <form method="post">
            <input type="hidden" name="category" id="rename_category">
            <input type="hidden" name="old_name" id="rename_old_name">
            
            <div class="form-group">
                <label>Tên hiện tại</label>
                <input type="text" id="rename_current" readonly style="background: #f1f5f9;">
            </div>
            
            <div class="form-group">
                <label>Tên mới *</label>
                <input type="text" name="new_name" id="rename_new_name" required>
            </div>
            
            <div class="modal-actions">
                <button type="submit" name="rename_classification" class="btn" style="background: #3b82f6;">
                    <i class="fas fa-save"></i> Lưu
                </button>
                <button type="button" onclick="hideModal('renameModal')" class="btn" style="background: #64748b;">
                    Hủy
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Di chuyển -->
<div id="moveModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">Di Chuyển Sản Phẩm</div>
        <form method="post">
            <input type="hidden" name="from_category" id="move_from_category">
            <input type="hidden" name="from_classification" id="move_from_classification">
            
            <div class="form-group">
                <label>Từ</label>
                <input type="text" id="move_from_text" readonly style="background: #f1f5f9;">
            </div>
            
            <div class="form-group">
                <label>Sang danh mục chính *</label>
                <select name="to_category" id="move_to_category" required>
                    <option value="">-- Chọn --</option>
                    <?php foreach ($categories as $cat): ?>
                        <option value="<?php echo $cat; ?>"><?php echo ucfirst($cat); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div class="form-group">
                <label>Danh mục con mới</label>
                <input type="text" name="to_classification" placeholder="Để trống nếu giữ nguyên tên">
            </div>
            
            <div class="modal-actions">
                <button type="submit" name="move_products" class="btn" style="background: #8b5cf6;">
                    <i class="fas fa-exchange-alt"></i> Di chuyển
                </button>
                <button type="button" onclick="hideModal('moveModal')" class="btn" style="background: #64748b;">
                    Hủy
                </button>
            </div>
        </form>
    </div>
</div>

<script>
function showAddModal() {
    document.getElementById('addModal').classList.add('active');
}

function showRenameModal(category, name) {
    document.getElementById('rename_category').value = category;
    document.getElementById('rename_old_name').value = name;
    document.getElementById('rename_current').value = name;
    document.getElementById('rename_new_name').value = name;
    document.getElementById('renameModal').classList.add('active');
}

function showMoveModal(category, classification) {
    document.getElementById('move_from_category').value = category;
    document.getElementById('move_from_classification').value = classification;
    document.getElementById('move_from_text').value = '[' + category + '] ' + classification;
    document.getElementById('moveModal').classList.add('active');
}

function hideModal(modalId) {
    document.getElementById(modalId).classList.remove('active');
}

function deleteClassification(category, classification, count) {
    if (!confirm(`Xóa danh mục "${classification}"?\n\n${count} sản phẩm sẽ bị bỏ phân loại (classification = NULL)`)) {
        return;
    }
    
    const form = document.createElement('form');
    form.method = 'POST';
    form.innerHTML = `
        <input type="hidden" name="delete_classification" value="1">
        <input type="hidden" name="category" value="${category}">
        <input type="hidden" name="classification" value="${classification}">
    `;
    document.body.appendChild(form);
    form.submit();
}

// Click outside to close modal
document.querySelectorAll('.modal').forEach(modal => {
    modal.addEventListener('click', function(e) {
        if (e.target === this) {
            this.classList.remove('active');
        }
    });
});
</script>

<?php require __DIR__ . '/inc/footer.php'; ?>

