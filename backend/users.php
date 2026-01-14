<?php
require __DIR__ . '/inc/db.php';
require __DIR__ . '/inc/auth.php';
$pdo = getPDO();
require __DIR__ . '/inc/activity.php';

$action = $_GET['action'] ?? '';
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$flash = ['type' => '', 'message' => ''];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['save_user'])) {
        $name = $_POST['name'] ?? '';
        $email = $_POST['email'] ?? '';
        $password = $_POST['password'] ?? '';
        $role = $_POST['role'] ?? 'user';

        try {
            if ($id && !empty($password)) {
                $hash = password_hash($password, PASSWORD_DEFAULT);
                $stmt = $pdo->prepare('UPDATE users SET name=?, email=?, password=?, role=? WHERE id=?');
                $stmt->execute([$name, $email, $hash, $role, $id]);
                log_activity($_SESSION['user']['id'] ?? null, 'update_user', 'user', $id, json_encode(['name'=>$name,'email'=>$email]));
            } elseif ($id) {
                $stmt = $pdo->prepare('UPDATE users SET name=?, email=?, role=? WHERE id=?');
                $stmt->execute([$name, $email, $role, $id]);
                log_activity($_SESSION['user']['id'] ?? null, 'update_user', 'user', $id, json_encode(['name'=>$name,'email'=>$email]));
            } else {
                $hash = password_hash($password ?: 'changeme', PASSWORD_DEFAULT);
                $stmt = $pdo->prepare('INSERT INTO users (name, email, password, role, status) VALUES (?, ?, ?, ?, 1)');
                $stmt->execute([$name, $email, $hash, $role]);
                $newId = $pdo->lastInsertId();
                log_activity($_SESSION['user']['id'] ?? null, 'create_user', 'user', $newId, json_encode(['name'=>$name,'email'=>$email]));
            }
            header('Location: users.php?msg=' . urlencode('Lưu thành công') . '&t=success');
            exit;
        } catch (PDOException $e) {
            // handle unique constraint (email)
            if ($e->getCode() === '23000' || strpos($e->getMessage(), 'UNIQUE') !== false) {
                $flash['type'] = 'error';
                $flash['message'] = 'Email đã tồn tại.';
            } else {
                $flash['type'] = 'error';
                $flash['message'] = 'Lỗi cơ sở dữ liệu: ' . $e->getMessage();
            }
        }
    }
}

// Search
$search = trim($_GET['q'] ?? '');

if ($action === 'delete' && $id) {
    try {
        $stmt = $pdo->prepare('DELETE FROM users WHERE id = ?');
        $stmt->execute([$id]);
        log_activity($_SESSION['user']['id'] ?? null, 'delete_user', 'user', $id, null);
        header('Location: users.php?msg=' . urlencode('Đã xóa') . '&t=success');
        exit;
    } catch (PDOException $e) {
        $flash['type'] = 'error';
        $flash['message'] = 'Không thể xóa: ' . $e->getMessage();
    }
}

// Toggle active/inactive
if ($action === 'toggle' && $id) {
    try {
        $stmt = $pdo->prepare('UPDATE users SET status = 1 - status WHERE id = ?');
        $stmt->execute([$id]);
        log_activity($_SESSION['user']['id'] ?? null, 'toggle_user_status', 'user', $id, null);
        header('Location: users.php');
        exit;
    } catch (PDOException $e) {
        $flash['type'] = 'error';
        $flash['message'] = 'Không thể thay đổi trạng thái: ' . $e->getMessage();
    }
}

// Get user data for AJAX (JSON)
if ($action === 'get' && $id) {
    header('Content-Type: application/json');
    try {
        $stmt = $pdo->prepare('SELECT * FROM users WHERE id = ?');
        $stmt->execute([$id]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($user) {
            // Don't send password to frontend
            unset($user['password']);
            echo json_encode(['success' => true, 'user' => $user]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Không tìm thấy user']);
        }
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
    exit;
}

require __DIR__ . '/inc/header.php';

// Show flash message from redirect
if (isset($_GET['msg'])) {
    $flash['message'] = $_GET['msg'];
    $flash['type'] = $_GET['t'] ?? 'success';
}

// Fetch users (with optional search)
if (!empty($search)) {
    $stmt = $pdo->prepare('SELECT id, name, email, role, status, created_at FROM users WHERE email LIKE ? OR name LIKE ? ORDER BY id DESC');
    $like = "%$search%";
    $stmt->execute([$like, $like]);
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
} else {
    $users = $pdo->query('SELECT id, name, email, role, status, created_at FROM users ORDER BY id DESC')->fetchAll(PDO::FETCH_ASSOC);
}

?>
<div class="card">
    <h2 class="page-main-title">Quản lý Khách hàng/Users</h2>
    
    <?php if (!empty($flash['message'])): ?>
        <div class="flash <?php echo $flash['type'] === 'success' ? 'success' : 'error' ?>">
            <?php echo htmlspecialchars($flash['message']) ?>
        </div>
    <?php endif; ?>
    
    <div style="display:flex;gap:12px;align-items:center;margin-bottom:12px">
        <button class="small-btn primary" onclick="openAddModal()">+ Thêm User</button>
        <form method="get" action="users.php" style="margin:0">
            <input type="text" name="q" placeholder="Tìm email hoặc tên" value="<?php echo htmlspecialchars($search ?? '') ?>" style="padding:8px;border-radius:6px;border:1px solid #e6e9ef">
            <button class="small-btn" type="submit">Tìm</button>
        </form>
    </div>
    
    <table class="table">
        <thead>
            <tr>
                <th>ID</th>
                <th>Tên</th>
                <th>Email</th>
                <th>Vai trò</th>
                <th>Trạng thái</th>
                <th>Ngày tạo</th>
                <th>Hành động</th>
            </tr>
        </thead>
        <tbody>
        <?php foreach ($users as $u): ?>
            <tr>
                <td><?php echo $u['id'] ?></td>
                <td><?php echo htmlspecialchars($u['name']) ?></td>
                <td><?php echo htmlspecialchars($u['email']) ?></td>
                <td><?php echo $u['role'] == 'admin' ? '<span style="color:#f59e0b;font-weight:600;">Admin</span>' : 'User' ?></td>
                <td>
                    <a href="users.php?action=toggle&id=<?php echo $u['id'] ?>" style="text-decoration:none;">
                        <?php echo $u['status'] ? '<span style="color:green;font-weight:600;">✓ Active</span>' : '<span style="color:red;font-weight:600;">✗ Inactive</span>' ?>
                    </a>
                </td>
                <td><?php echo $u['created_at'] ?></td>
                <td class="btn-row">
                    <button class="small-btn" onclick="openEditModal(<?php echo $u['id'] ?>)">Sửa</button>
                    <a class="small-btn warn" href="users.php?action=delete&id=<?php echo $u['id'] ?>" onclick="return confirm('Xóa user này?')">Xóa</a>
                </td>
            </tr>
        <?php endforeach; ?>
        <?php if(empty($users)): ?>
            <tr>
                <td colspan="7" style="text-align:center;color:#999;padding:40px;">
                    Chưa có user nào. Nhấn "Thêm User" để bắt đầu.
                </td>
            </tr>
        <?php endif; ?>
        </tbody>
    </table>
</div>

<!-- Modal Thêm User -->
<div id="addModal" class="modal">
    <div class="modal-content" style="max-width: 550px;">
        <div class="modal-header">
            <h3 style="margin:0">Thêm User Mới</h3>
            <span class="modal-close" onclick="closeAddModal()">&times;</span>
        </div>
        <div class="modal-body">
            <form method="post" id="addUserForm">
                <label>Tên <span style="color:red">*</span>
                    <input type="text" name="name" id="add_name" required style="width:100%">
                </label>

                <label style="margin-top:16px;">Email <span style="color:red">*</span>
                    <input type="email" name="email" id="add_email" required style="width:100%">
                </label>

                <label style="margin-top:16px;">Mật khẩu <span style="color:red">*</span>
                    <input type="password" name="password" id="add_password" required style="width:100%">
                    <small style="color:#666;display:block;margin-top:5px;">
                        Mật khẩu mặc định: changeme
                    </small>
                </label>

                <label style="margin-top:16px;">Vai trò
                    <select name="role" id="add_role" style="width:100%">
                        <option value="user">User</option>
                        <option value="admin">Admin</option>
                    </select>
                </label>

                <div style="margin-top:24px;display:flex;gap:12px;justify-content:flex-end">
                    <button type="button" class="small-btn" onclick="closeAddModal()">Hủy</button>
                    <button type="submit" class="small-btn primary" name="save_user">💾 Thêm user</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Sửa User -->
<div id="editModal" class="modal">
    <div class="modal-content" style="max-width: 550px;">
        <div class="modal-header">
            <h3 style="margin:0">Chỉnh Sửa User</h3>
            <span class="modal-close" onclick="closeEditModal()">&times;</span>
        </div>
        <div class="modal-body">
            <form method="post" id="editUserForm" action="users.php?action=edit&id=">
                <label>Tên <span style="color:red">*</span>
                    <input type="text" name="name" id="edit_name" required style="width:100%">
                </label>

                <label style="margin-top:16px;">Email <span style="color:red">*</span>
                    <input type="email" name="email" id="edit_email" required style="width:100%">
                </label>

                <label style="margin-top:16px;">Mật khẩu mới (để trống nếu không đổi)
                    <input type="password" name="password" id="edit_password" style="width:100%">
                    <small style="color:#666;display:block;margin-top:5px;">
                        Chỉ điền nếu muốn thay đổi mật khẩu
                    </small>
                </label>

                <label style="margin-top:16px;">Vai trò
                    <select name="role" id="edit_role" style="width:100%">
                        <option value="user">User</option>
                        <option value="admin">Admin</option>
                    </select>
                </label>

                <div style="margin-top:24px;display:flex;gap:12px;justify-content:flex-end">
                    <button type="button" class="small-btn" onclick="closeEditModal()">Hủy</button>
                    <button type="submit" class="small-btn primary" name="save_user">💾 Lưu thay đổi</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function openAddModal() {
    document.getElementById('addModal').style.display = 'block';
    document.body.style.overflow = 'hidden';
    document.getElementById('addUserForm').reset();
    document.getElementById('add_password').value = 'changeme';
}

function closeAddModal() {
    document.getElementById('addModal').style.display = 'none';
    document.body.style.overflow = 'auto';
}

function openEditModal(userId) {
    document.getElementById('editModal').style.display = 'block';
    document.body.style.overflow = 'hidden';
    
    document.getElementById('editUserForm').action = 'users.php?action=edit&id=' + userId;
    
    fetch('users.php?action=get&id=' + userId)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const u = data.user;
                document.getElementById('edit_name').value = u.name || '';
                document.getElementById('edit_email').value = u.email || '';
                document.getElementById('edit_password').value = '';
                document.getElementById('edit_role').value = u.role || 'user';
            } else {
                alert('Không thể tải thông tin user!');
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
