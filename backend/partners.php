<?php
require __DIR__ . '/inc/db.php';
require __DIR__ . '/inc/auth.php';
require __DIR__ . '/inc/activity.php';
$pdo = getPDO();

$action = $_GET['action'] ?? '';
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$flash = ['type'=>'','message'=>''];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['save_partner'])) {
        $name = trim($_POST['name'] ?? '');
        $image_path = trim($_POST['image_path'] ?? '');
        $status = isset($_POST['status']) ? 1 : 0;
        $order = (int)($_POST['display_order'] ?? 0);
        
        try {
            if ($id) {
                $pdo->prepare('UPDATE partners SET name=?, image_path=?, status=?, display_order=? WHERE id=?')
                    ->execute([$name, $image_path, $status, $order, $id]);
                log_activity($_SESSION['user']['id'] ?? null, 'update_partner', 'partner', $id, null);
                $flash['type'] = 'success';
                $flash['message'] = 'Đã cập nhật đối tác thành công!';
            } else {
                $pdo->prepare('INSERT INTO partners (name, image_path, status, display_order) VALUES (?,?,?,?)')
                    ->execute([$name, $image_path, $status, $order]);
                $newId = $pdo->lastInsertId();
                log_activity($_SESSION['user']['id'] ?? null, 'create_partner', 'partner', $newId, null);
                header('Location: partners.php?msg=' . urlencode('Đã thêm đối tác thành công') . '&t=success'); 
                exit;
            }
        } catch (Exception $e) { 
            $flash['type'] = 'error'; 
            $flash['message'] = 'Lỗi: ' . $e->getMessage(); 
        }
    }
}

if ($action === 'delete' && $id) { 
    try {
        $pdo->prepare('DELETE FROM partners WHERE id=?')->execute([$id]); 
        log_activity($_SESSION['user']['id'] ?? null, 'delete_partner', 'partner', $id, null);
        header('Location: partners.php?msg=' . urlencode('Đã xóa đối tác thành công') . '&t=success'); 
        exit;
    } catch (Exception $e) {
        header('Location: partners.php?msg=' . urlencode('Lỗi: ' . $e->getMessage()) . '&t=error'); 
        exit;
    }
}

// Toggle status
if ($action === 'toggle' && $id) {
    try {
        $stmt = $pdo->prepare('SELECT status FROM partners WHERE id=?');
        $stmt->execute([$id]);
        $partner = $stmt->fetch();
        if ($partner) {
            $newStatus = $partner['status'] ? 0 : 1;
            $pdo->prepare('UPDATE partners SET status=? WHERE id=?')->execute([$newStatus, $id]);
            log_activity($_SESSION['user']['id'] ?? null, 'toggle_partner', 'partner', $id, null);
        }
        header('Location: partners.php'); 
        exit;
    } catch (Exception $e) {
        header('Location: partners.php?msg=' . urlencode('Lỗi: ' . $e->getMessage()) . '&t=error'); 
        exit;
    }
}

// Get partner data for AJAX (JSON)
if ($action === 'get' && $id) {
    header('Content-Type: application/json');
    try {
        $stmt = $pdo->prepare('SELECT * FROM partners WHERE id = ?');
        $stmt->execute([$id]);
        $partner = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($partner) {
            echo json_encode(['success' => true, 'partner' => $partner]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Không tìm thấy đối tác']);
        }
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
    exit;
}

require __DIR__ . '/inc/header.php';

// Get flash message from URL
if (isset($_GET['msg'])) {
    $flash['type'] = $_GET['t'] ?? 'success';
    $flash['message'] = $_GET['msg'];
}

$partners = $pdo->query('SELECT * FROM partners ORDER BY display_order ASC, id ASC')->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="card">
    <h2 class="page-main-title">Quản lý Đối tác</h2>
    
    <?php if(!empty($flash['message'])): ?>
        <div class="flash <?php echo $flash['type'] === 'success' ? 'success' : 'error' ?>">
            <?php echo htmlspecialchars($flash['message']) ?>
        </div>
    <?php endif; ?>
    
    <p style="color: #666; margin-bottom: 20px;">
        Quản lý logo đối tác hiển thị trên trang chủ. Bật/tắt để kiểm soát hiển thị.
    </p>
    
    <button class="small-btn primary" onclick="openAddModal()">+ Thêm Đối tác</button>
    
    <table class="table">
        <thead>
            <tr>
                <th>ID</th>
                <th>Logo</th>
                <th>Tên đối tác</th>
                <th>Thứ tự</th>
                <th>Trạng thái</th>
                <th>Hành động</th>
            </tr>
        </thead>
        <tbody>
        <?php foreach($partners as $p): ?>
            <tr>
                <td><?php echo $p['id'] ?></td>
                <td>
                    <?php if($p['image_path']): ?>
                        <img src="../<?php echo htmlspecialchars($p['image_path']) ?>" 
                             style="height:40px; max-width:120px; object-fit:contain; border-radius:4px; background:#f5f5f5; padding:5px;" 
                             onerror="this.style.display='none'; this.nextElementSibling.style.display='inline';" 
                             alt="<?php echo htmlspecialchars($p['name']) ?>">
                        <span style="display:none; color:#999; font-size:12px;">Không có ảnh</span>
                    <?php else: ?>
                        <span style="color:#999; font-size:12px;">Chưa có hình</span>
                    <?php endif; ?>
                </td>
                <td><strong><?php echo htmlspecialchars($p['name']) ?></strong></td>
                <td><?php echo $p['display_order'] ?></td>
                <td>
                    <a href="partners.php?action=toggle&id=<?php echo $p['id'] ?>" 
                       style="text-decoration:none;">
                        <?php if($p['status']): ?>
                            <span style="color:green; font-weight:600;">✓ Hiển thị</span>
                        <?php else: ?>
                            <span style="color:red; font-weight:600;">✗ Ẩn</span>
                        <?php endif; ?>
                    </a>
                </td>
                <td class="btn-row">
                    <button class="small-btn" onclick="openEditModal(<?php echo $p['id'] ?>)">Sửa</button>
                    <a class="small-btn warn" 
                       href="partners.php?action=delete&id=<?php echo $p['id'] ?>" 
                       onclick="return confirm('Bạn có chắc muốn xóa đối tác này?')">Xóa</a>
                </td>
            </tr>
        <?php endforeach; ?>
        <?php if(empty($partners)): ?>
            <tr>
                <td colspan="6" style="text-align:center; color:#999; padding:40px;">
                    Chưa có đối tác nào. Nhấn "Thêm Đối tác" để bắt đầu.
                </td>
            </tr>
        <?php endif; ?>
        </tbody>
    </table>
</div>

<!-- Modal Thêm Đối Tác -->
<div id="addModal" class="modal">
    <div class="modal-content" style="max-width: 600px;">
        <div class="modal-header">
            <h3 style="margin:0">Thêm Đối Tác Mới</h3>
            <span class="modal-close" onclick="closeAddModal()">&times;</span>
        </div>
        <div class="modal-body">
            <form method="post" id="addPartnerForm">
                <label>
                    Tên đối tác <span style="color:red">*</span>
                    <input type="text" 
                           name="name" 
                           id="add_name"
                           placeholder="VD: Armstrong, AICA, ABC Play..." 
                           required
                           style="width:100%">
                </label>
                
                <label style="margin-top:16px;">
                    Đường dẫn hình ảnh <span style="color:red">*</span>
                    <input type="text" 
                           name="image_path" 
                           id="add_image_path"
                           placeholder="assets/images/partner-1.svg" 
                           required
                           style="width:100%">
                    <small style="color:#666; display:block; margin-top:5px;">
                        Ví dụ: assets/images/partner-1.svg hoặc https://example.com/logo.png
                    </small>
                </label>
                
                <label style="margin-top:16px;">
                    Thứ tự hiển thị
                    <input type="number" 
                           name="display_order" 
                           id="add_display_order"
                           value="0" 
                           min="0"
                           style="width:100%">
                    <small style="color:#666; display:block; margin-top:5px;">
                        Số nhỏ sẽ hiển thị trước. Ví dụ: 1, 2, 3...
                    </small>
                </label>
                
                <label style="margin-top:16px; display:flex; align-items:center; gap:8px; cursor:pointer;">
                    <input type="checkbox" name="status" id="add_status" checked>
                    <span>Hiển thị trên trang chủ</span>
                </label>
                
                <div style="margin-top:24px; display:flex; gap:12px; justify-content:flex-end;">
                    <button type="button" class="small-btn" onclick="closeAddModal()">Hủy</button>
                    <button type="submit" class="small-btn primary" name="save_partner">💾 Thêm đối tác</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Sửa Đối Tác -->
<div id="editModal" class="modal">
    <div class="modal-content" style="max-width: 600px;">
        <div class="modal-header">
            <h3 style="margin:0">Chỉnh Sửa Đối Tác</h3>
            <span class="modal-close" onclick="closeEditModal()">&times;</span>
        </div>
        <div class="modal-body">
            <form method="post" id="editPartnerForm" action="partners.php?action=edit&id=">
                <label>
                    Tên đối tác <span style="color:red">*</span>
                    <input type="text" 
                           name="name" 
                           id="edit_name"
                           required
                           style="width:100%">
                </label>
                
                <label style="margin-top:16px;">
                    Đường dẫn hình ảnh <span style="color:red">*</span>
                    <input type="text" 
                           name="image_path" 
                           id="edit_image_path"
                           required
                           style="width:100%">
                    <small style="color:#666; display:block; margin-top:5px;">
                        Ví dụ: assets/images/partner-1.svg hoặc https://example.com/logo.png
                    </small>
                </label>
                
                <div id="imagePreview" style="margin-top:16px; display:none;">
                    <label style="display:block; margin-bottom:8px; font-weight:600;">Xem trước:</label>
                    <img id="previewImg" 
                         style="max-height:80px; max-width:200px; object-fit:contain; border:1px solid #ddd; border-radius:4px; padding:10px; background:#f9f9f9;" 
                         alt="Preview">
                </div>
                
                <label style="margin-top:16px;">
                    Thứ tự hiển thị
                    <input type="number" 
                           name="display_order" 
                           id="edit_display_order"
                           min="0"
                           style="width:100%">
                    <small style="color:#666; display:block; margin-top:5px;">
                        Số nhỏ sẽ hiển thị trước. Ví dụ: 1, 2, 3...
                    </small>
                </label>
                
                <label style="margin-top:16px; display:flex; align-items:center; gap:8px; cursor:pointer;">
                    <input type="checkbox" name="status" id="edit_status">
                    <span>Hiển thị trên trang chủ</span>
                </label>
                
                <div style="margin-top:24px; display:flex; gap:12px; justify-content:flex-end;">
                    <button type="button" class="small-btn" onclick="closeEditModal()">Hủy</button>
                    <button type="submit" class="small-btn primary" name="save_partner">💾 Lưu thay đổi</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
// Open Add Modal
function openAddModal() {
    document.getElementById('addModal').style.display = 'block';
    document.body.style.overflow = 'hidden';
    
    // Reset form
    document.getElementById('addPartnerForm').reset();
    document.getElementById('add_status').checked = true;
}

// Close Add Modal
function closeAddModal() {
    document.getElementById('addModal').style.display = 'none';
    document.body.style.overflow = 'auto';
}

// Open Edit Modal
function openEditModal(partnerId) {
    document.getElementById('editModal').style.display = 'block';
    document.body.style.overflow = 'hidden';
    
    // Set form action
    document.getElementById('editPartnerForm').action = 'partners.php?action=edit&id=' + partnerId;
    
    // Fetch partner data
    fetch('partners.php?action=get&id=' + partnerId)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const p = data.partner;
                document.getElementById('edit_name').value = p.name || '';
                document.getElementById('edit_image_path').value = p.image_path || '';
                document.getElementById('edit_display_order').value = p.display_order || 0;
                document.getElementById('edit_status').checked = p.status == 1;
                
                // Show image preview
                if (p.image_path) {
                    const previewDiv = document.getElementById('imagePreview');
                    const previewImg = document.getElementById('previewImg');
                    previewImg.src = '../' + p.image_path;
                    previewImg.onerror = function() {
                        previewDiv.style.display = 'none';
                    };
                    previewImg.onload = function() {
                        previewDiv.style.display = 'block';
                    };
                }
            } else {
                alert('Không thể tải thông tin đối tác!');
                closeEditModal();
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Lỗi khi tải dữ liệu!');
            closeEditModal();
        });
}

// Close Edit Modal
function closeEditModal() {
    document.getElementById('editModal').style.display = 'none';
    document.body.style.overflow = 'auto';
}

// Close modals when clicking outside
window.onclick = function(event) {
    const addModal = document.getElementById('addModal');
    const editModal = document.getElementById('editModal');
    
    if (event.target == addModal) {
        closeAddModal();
    }
    if (event.target == editModal) {
        closeEditModal();
    }
}

// Close modals with ESC key
document.addEventListener('keydown', function(event) {
    if (event.key === 'Escape') {
        closeAddModal();
        closeEditModal();
    }
});
</script>

<?php require __DIR__ . '/inc/footer.php'; ?>
