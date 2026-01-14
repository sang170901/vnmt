<?php
require __DIR__ . '/inc/db.php';
require __DIR__ . '/inc/auth.php';
require __DIR__ . '/inc/activity.php';
$pdo = getPDO();

$action = $_GET['action'] ?? '';
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$flash = ['type' => '', 'message' => ''];

// Handle POST save
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['save_voucher'])) {
        $code = trim($_POST['code'] ?? '');
        $discount_type = trim($_POST['discount_type'] ?? 'fixed');
        $discount_value = (float)($_POST['discount_value'] ?? 0);
        $min_purchase = (float)($_POST['min_purchase'] ?? 0);
        $max_uses = (int)($_POST['max_uses'] ?? 0);
        $supplier_id = !empty($_POST['supplier_id']) ? (int)$_POST['supplier_id'] : null;
        $start_date = trim($_POST['start_date'] ?? null);
        $end_date = trim($_POST['end_date'] ?? null);
        $status = isset($_POST['status']) ? 1 : 0;

        try {
            if ($id) {
                $stmt = $pdo->prepare('UPDATE vouchers SET code=?, discount_type=?, discount_value=?, min_purchase=?, max_uses=?, supplier_id=?, start_date=?, end_date=?, status=? WHERE id=?');
                $stmt->execute([$code, $discount_type, $discount_value, $min_purchase, $max_uses, $supplier_id, $start_date, $end_date, $status, $id]);
                log_activity($_SESSION['user']['id'] ?? null, 'update_voucher', 'voucher', $id, json_encode(['code'=>$code]));
            } else {
                $stmt = $pdo->prepare('INSERT INTO vouchers (code, discount_type, discount_value, min_purchase, max_uses, supplier_id, start_date, end_date, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)');
                $stmt->execute([$code, $discount_type, $discount_value, $min_purchase, $max_uses, $supplier_id, $start_date, $end_date, $status]);
                $newId = $pdo->lastInsertId();
                log_activity($_SESSION['user']['id'] ?? null, 'create_voucher', 'voucher', $newId, json_encode(['code'=>$code]));
            }
            header('Location: vouchers.php?msg=' . urlencode('Lưu thành công') . '&t=success');
            exit;
        } catch (PDOException $e) {
            $flash['type'] = 'error';
            $flash['message'] = 'Lỗi DB: ' . $e->getMessage();
        }
    }
}

// Delete
if ($action === 'delete' && $id) {
    try {
        $stmt = $pdo->prepare('DELETE FROM vouchers WHERE id = ?');
        $stmt->execute([$id]);
        log_activity($_SESSION['user']['id'] ?? null, 'delete_voucher', 'voucher', $id, null);
        header('Location: vouchers.php?msg=' . urlencode('Đã xóa') . '&t=success');
        exit;
    } catch (PDOException $e) {
        $flash['type'] = 'error';
        $flash['message'] = 'Không thể xóa: ' . $e->getMessage();
    }
}

// Toggle status
if ($action === 'toggle' && $id) {
    try {
        $stmt = $pdo->prepare('UPDATE vouchers SET status = 1 - status WHERE id = ?');
        $stmt->execute([$id]);
        log_activity($_SESSION['user']['id'] ?? null, 'toggle_voucher_status', 'voucher', $id, null);
        header('Location: vouchers.php');
        exit;
    } catch (PDOException $e) {
        $flash['type'] = 'error';
        $flash['message'] = 'Không thể thay đổi trạng thái: ' . $e->getMessage();
    }
}

// Get voucher data for AJAX (JSON)
if ($action === 'get' && $id) {
    header('Content-Type: application/json');
    try {
        $stmt = $pdo->prepare('SELECT * FROM vouchers WHERE id = ?');
        $stmt->execute([$id]);
        $voucher = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($voucher) {
            echo json_encode(['success' => true, 'voucher' => $voucher]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Không tìm thấy voucher']);
        }
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
    exit;
}

require __DIR__ . '/inc/header.php';

// Suppliers for voucher assignment
$suppliers = $pdo->query('SELECT id,name FROM suppliers ORDER BY name')->fetchAll(PDO::FETCH_ASSOC);

// Show flash from redirect
if (isset($_GET['msg'])) {
    $flash['message'] = $_GET['msg'];
    $flash['type'] = $_GET['t'] ?? 'success';
}

// Search
$search = trim($_GET['q'] ?? '');
if (!empty($search)) {
    $stmt = $pdo->prepare('SELECT * FROM vouchers WHERE code LIKE ? ORDER BY id DESC');
    $like = "%$search%";
    $stmt->execute([$like]);
    $vouchers = $stmt->fetchAll(PDO::FETCH_ASSOC);
} else {
    $vouchers = $pdo->query('SELECT * FROM vouchers ORDER BY id DESC')->fetchAll(PDO::FETCH_ASSOC);
}

?>
<div class="card">
    <h2 class="page-main-title">Quản lý Voucher</h2>
    
    <?php if (!empty($flash['message'])): ?>
        <div class="flash <?php echo $flash['type'] === 'success' ? 'success' : 'error' ?>">
            <?php echo htmlspecialchars($flash['message']) ?>
        </div>
    <?php endif; ?>

    <?php
    // Stats: number of vouchers per supplier and total used_count per supplier
    $stats = $pdo->query("
        SELECT 
            COALESCE(s.id, 0) as supplier_id, 
            COALESCE(s.name, '(Tất cả)') as supplier_name, 
            COUNT(v.id) as voucher_count, 
            SUM(COALESCE(v.used_count, 0)) as total_used 
        FROM vouchers v 
        LEFT JOIN suppliers s ON v.supplier_id = s.id 
        GROUP BY COALESCE(s.id, 0), COALESCE(s.name, '(Tất cả)')
        ORDER BY voucher_count DESC
    ")->fetchAll(PDO::FETCH_ASSOC);
    if ($stats): ?>
        <div style="margin-bottom:12px;display:flex;gap:12px;flex-wrap:wrap">
            <?php foreach ($stats as $st): ?>
                <div style="background:#fff;padding:10px;border-radius:8px;box-shadow:0 1px 4px rgba(0,0,0,0.06);min-width:180px">
                    <div style="font-size:14px;color:#666"><?php echo htmlspecialchars($st['supplier_name']) ?></div>
                    <div style="font-weight:700;font-size:18px"><?php echo $st['voucher_count'] ?> vouchers</div>
                    <div style="font-size:12px;color:#888">Đã dùng: <?php echo $st['total_used'] ?></div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <div style="display:flex;gap:12px;align-items:center;margin-bottom:12px">
        <button class="small-btn primary" onclick="openAddModal()">+ Thêm voucher</button>
        <form method="get" action="vouchers.php" style="margin:0">
            <input type="text" name="q" placeholder="Tìm theo mã" value="<?php echo htmlspecialchars($search) ?>" style="padding:8px;border-radius:6px;border:1px solid #e6e9ef">
            <button class="small-btn" type="submit">Tìm</button>
        </form>
    </div>

    <table class="table">
        <thead>
            <tr>
                <th>ID</th>
                <th>Mã</th>
                <th>Loại</th>
                <th>Giá trị</th>
                <th>Điều kiện</th>
                <th>Nhà cung cấp</th>
                <th>Trạng thái</th>
                <th>Hành động</th>
            </tr>
        </thead>
        <tbody>
        <?php foreach ($vouchers as $v): ?>
            <tr>
                <td><?php echo $v['id'] ?></td>
                <td><strong><?php echo htmlspecialchars($v['code']) ?></strong></td>
                <td><?php echo $v['discount_type'] == 'percent' ? 'Phần trăm' : 'Cố định' ?></td>
                <td><?php echo htmlspecialchars($v['discount_value']) ?></td>
                <td><?php echo htmlspecialchars($v['min_purchase']) ?></td>
                <td><?php
                    if (!empty($v['supplier_id'])) {
                        $s = $pdo->prepare('SELECT name FROM suppliers WHERE id = ? LIMIT 1'); 
                        $s->execute([$v['supplier_id']]); 
                        $sr = $s->fetch(PDO::FETCH_ASSOC);
                        echo htmlspecialchars($sr['name'] ?? '');
                    } else { 
                        echo '<span style="color:#999;">-</span>'; 
                    }
                ?></td>
                <td>
                    <a href="vouchers.php?action=toggle&id=<?php echo $v['id'] ?>" style="text-decoration:none;">
                        <?php echo ($v['status'] ?? 1) ? '<span style="color:green;font-weight:600;">✓ Hoạt động</span>' : '<span style="color:red;font-weight:600;">✗ Không hoạt động</span>' ?>
                    </a>
                </td>
                <td class="btn-row">
                    <button class="small-btn" onclick="openEditModal(<?php echo $v['id'] ?>)">Sửa</button>
                    <a class="small-btn warn" href="vouchers.php?action=delete&id=<?php echo $v['id'] ?>" onclick="return confirm('Xóa voucher?')">Xóa</a>
                </td>
            </tr>
        <?php endforeach; ?>
        <?php if(empty($vouchers)): ?>
            <tr>
                <td colspan="8" style="text-align:center;color:#999;padding:40px;">
                    Chưa có voucher nào. Nhấn "Thêm voucher" để bắt đầu.
                </td>
            </tr>
        <?php endif; ?>
        </tbody>
    </table>
</div>

<!-- Modal Thêm Voucher -->
<div id="addModal" class="modal">
    <div class="modal-content" style="max-width: 650px;">
        <div class="modal-header">
            <h3 style="margin:0">Thêm Voucher Mới</h3>
            <span class="modal-close" onclick="closeAddModal()">&times;</span>
        </div>
        <div class="modal-body">
            <form method="post" id="addVoucherForm">
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px">
                    <div>
                        <label>Mã giảm giá <span style="color:red">*</span>
                            <input type="text" name="code" id="add_code" required style="width:100%">
                        </label>
                    </div>
                    <div>
                        <label>Loại giảm giá
                            <select name="discount_type" id="add_discount_type" style="width:100%">
                                <option value="fixed">Cố định</option>
                                <option value="percent">Theo phần trăm</option>
                            </select>
                        </label>
                    </div>
                </div>

                <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:16px;margin-top:16px">
                    <div>
                        <label>Giá trị giảm
                            <input type="number" name="discount_value" id="add_discount_value" step="0.01" style="width:100%">
                        </label>
                    </div>
                    <div>
                        <label>Giá trị tối thiểu
                            <input type="number" name="min_purchase" id="add_min_purchase" step="0.01" style="width:100%">
                        </label>
                    </div>
                    <div>
                        <label>Số lần tối đa
                            <input type="number" name="max_uses" id="add_max_uses" style="width:100%">
                        </label>
                    </div>
                </div>

                <label style="margin-top:16px;">Nhà cung cấp (tuỳ chọn)
                    <select name="supplier_id" id="add_supplier_id" style="width:100%">
                        <option value="">-- Tất cả / Không --</option>
                        <?php foreach ($suppliers as $s): ?>
                            <option value="<?php echo $s['id'] ?>"><?php echo htmlspecialchars($s['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </label>

                <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;margin-top:16px">
                    <div>
                        <label>Ngày bắt đầu
                            <input type="date" name="start_date" id="add_start_date" style="width:100%">
                        </label>
                    </div>
                    <div>
                        <label>Ngày kết thúc
                            <input type="date" name="end_date" id="add_end_date" style="width:100%">
                        </label>
                    </div>
                </div>

                <label style="margin-top:16px;display:flex;align-items:center;gap:8px;cursor:pointer">
                    <input type="checkbox" name="status" id="add_status" checked>
                    <span>Hoạt động</span>
                </label>

                <div style="margin-top:24px;display:flex;gap:12px;justify-content:flex-end">
                    <button type="button" class="small-btn" onclick="closeAddModal()">Hủy</button>
                    <button type="submit" class="small-btn primary" name="save_voucher">💾 Thêm voucher</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Sửa Voucher -->
<div id="editModal" class="modal">
    <div class="modal-content" style="max-width: 650px;">
        <div class="modal-header">
            <h3 style="margin:0">Chỉnh Sửa Voucher</h3>
            <span class="modal-close" onclick="closeEditModal()">&times;</span>
        </div>
        <div class="modal-body">
            <form method="post" id="editVoucherForm" action="vouchers.php?action=edit&id=">
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px">
                    <div>
                        <label>Mã giảm giá <span style="color:red">*</span>
                            <input type="text" name="code" id="edit_code" required style="width:100%">
                        </label>
                    </div>
                    <div>
                        <label>Loại giảm giá
                            <select name="discount_type" id="edit_discount_type" style="width:100%">
                                <option value="fixed">Cố định</option>
                                <option value="percent">Theo phần trăm</option>
                            </select>
                        </label>
                    </div>
                </div>

                <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:16px;margin-top:16px">
                    <div>
                        <label>Giá trị giảm
                            <input type="number" name="discount_value" id="edit_discount_value" step="0.01" style="width:100%">
                        </label>
                    </div>
                    <div>
                        <label>Giá trị tối thiểu
                            <input type="number" name="min_purchase" id="edit_min_purchase" step="0.01" style="width:100%">
                        </label>
                    </div>
                    <div>
                        <label>Số lần tối đa
                            <input type="number" name="max_uses" id="edit_max_uses" style="width:100%">
                        </label>
                    </div>
                </div>

                <label style="margin-top:16px;">Nhà cung cấp (tuỳ chọn)
                    <select name="supplier_id" id="edit_supplier_id" style="width:100%">
                        <option value="">-- Tất cả / Không --</option>
                        <?php foreach ($suppliers as $s): ?>
                            <option value="<?php echo $s['id'] ?>"><?php echo htmlspecialchars($s['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </label>

                <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;margin-top:16px">
                    <div>
                        <label>Ngày bắt đầu
                            <input type="date" name="start_date" id="edit_start_date" style="width:100%">
                        </label>
                    </div>
                    <div>
                        <label>Ngày kết thúc
                            <input type="date" name="end_date" id="edit_end_date" style="width:100%">
                        </label>
                    </div>
                </div>

                <label style="margin-top:16px;display:flex;align-items:center;gap:8px;cursor:pointer">
                    <input type="checkbox" name="status" id="edit_status">
                    <span>Hoạt động</span>
                </label>

                <div style="margin-top:24px;display:flex;gap:12px;justify-content:flex-end">
                    <button type="button" class="small-btn" onclick="closeEditModal()">Hủy</button>
                    <button type="submit" class="small-btn primary" name="save_voucher">💾 Lưu thay đổi</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function openAddModal() {
    document.getElementById('addModal').style.display = 'block';
    document.body.style.overflow = 'hidden';
    document.getElementById('addVoucherForm').reset();
    document.getElementById('add_status').checked = true;
}

function closeAddModal() {
    document.getElementById('addModal').style.display = 'none';
    document.body.style.overflow = 'auto';
}

function openEditModal(voucherId) {
    document.getElementById('editModal').style.display = 'block';
    document.body.style.overflow = 'hidden';
    
    document.getElementById('editVoucherForm').action = 'vouchers.php?action=edit&id=' + voucherId;
    
    fetch('vouchers.php?action=get&id=' + voucherId)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const v = data.voucher;
                document.getElementById('edit_code').value = v.code || '';
                document.getElementById('edit_discount_type').value = v.discount_type || 'fixed';
                document.getElementById('edit_discount_value').value = v.discount_value || 0;
                document.getElementById('edit_min_purchase').value = v.min_purchase || 0;
                document.getElementById('edit_max_uses').value = v.max_uses || 0;
                document.getElementById('edit_supplier_id').value = v.supplier_id || '';
                document.getElementById('edit_start_date').value = v.start_date || '';
                document.getElementById('edit_end_date').value = v.end_date || '';
                document.getElementById('edit_status').checked = v.status == 1;
            } else {
                alert('Không thể tải thông tin voucher!');
                closeEditModal();
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Lỗi khi tải dữ liệu!');
            closeEditModal();
        });
}

function closeEditModal() {
    document.getElementById('editModal').style.display = 'none';
    document.body.style.overflow = 'auto';
}

window.onclick = function(event) {
    const addModal = document.getElementById('addModal');
    const editModal = document.getElementById('editModal');
    
    if (event.target == addModal) closeAddModal();
    if (event.target == editModal) closeEditModal();
}

document.addEventListener('keydown', function(event) {
    if (event.key === 'Escape') {
        closeAddModal();
        closeEditModal();
    }
});
</script>

<?php require __DIR__ . '/inc/footer.php'; ?>
