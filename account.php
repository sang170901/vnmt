<?php
session_start();
require_once 'config.php';
require_once 'backend/inc/db.php';
require_once 'inc/url_helpers.php';

// Redirect if not logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php?redirect=' . urlencode($_SERVER['REQUEST_URI']));
    exit;
}

$pdo = getPDO();
$success = '';
$error = '';

// Handle profile update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update_profile') {
    $full_name = trim($_POST['full_name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $bio = trim($_POST['bio'] ?? '');
    $avatar = trim($_POST['avatar'] ?? '');
    
    if (empty($full_name) || empty($email)) {
        $error = 'Họ tên và email không được để trống!';
    } else {
        try {
            $stmt = $pdo->prepare("
                UPDATE users 
                SET full_name = ?, email = ?, bio = ?, avatar = ?, updated_at = CURRENT_TIMESTAMP
                WHERE id = ?
            ");
            $stmt->execute([$full_name, $email, $bio, $avatar, $_SESSION['user_id']]);
            
            // Update session
            $_SESSION['full_name'] = $full_name;
            $_SESSION['email'] = $email;
            $_SESSION['avatar'] = $avatar;
            
            $success = 'Cập nhật thông tin thành công!';
        } catch (PDOException $e) {
            $error = 'Lỗi: ' . $e->getMessage();
        }
    }
}

// Handle password change
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'change_password') {
    $current_password = $_POST['current_password'] ?? '';
    $new_password = $_POST['new_password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    
    if (empty($current_password) || empty($new_password)) {
        $error = 'Vui lòng điền đầy đủ thông tin!';
    } elseif (strlen($new_password) < 6) {
        $error = 'Mật khẩu mới phải có ít nhất 6 ký tự!';
    } elseif ($new_password !== $confirm_password) {
        $error = 'Mật khẩu xác nhận không khớp!';
    } else {
        try {
            $stmt = $pdo->prepare("SELECT password FROM users WHERE id = ?");
            $stmt->execute([$_SESSION['user_id']]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (password_verify($current_password, $user['password'])) {
                $hashedPassword = password_hash($new_password, PASSWORD_DEFAULT);
                $stmt = $pdo->prepare("UPDATE users SET password = ? WHERE id = ?");
                $stmt->execute([$hashedPassword, $_SESSION['user_id']]);
                
                $success = 'Đổi mật khẩu thành công!';
            } else {
                $error = 'Mật khẩu hiện tại không đúng!';
            }
        } catch (PDOException $e) {
            $error = 'Lỗi: ' . $e->getMessage();
        }
    }
}

// Get user info
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

// Get user comments
$stmt = $pdo->prepare("
    SELECT c.*, p.id as post_id, p.title as post_title, p.slug as post_slug, p.title_en
    FROM comments c
    LEFT JOIN posts p ON c.post_id = p.id
    WHERE c.user_id = ?
    ORDER BY c.created_at DESC
    LIMIT 10
");
$stmt->execute([$_SESSION['user_id']]);
$comments = $stmt->fetchAll(PDO::FETCH_ASSOC);

include 'inc/header-new.php';
?>

<style>
    .account-container {
        max-width: 1200px;
        margin: 40px auto;
        padding: 20px;
    }
    
    .account-header {
        background: linear-gradient(135deg, #38bdf8 0%, #0284c7 100%);
        color: white;
        padding: 40px;
        border-radius: 16px;
        margin-bottom: 30px;
        display: flex;
        align-items: center;
        gap: 24px;
    }
    
    .account-avatar {
        width: 100px;
        height: 100px;
        border-radius: 50%;
        border: 4px solid rgba(255,255,255,0.3);
    }
    
    .account-info h1 {
        font-size: 2rem;
        margin-bottom: 8px;
    }
    
    .account-info p {
        opacity: 0.9;
        margin: 4px 0;
    }
    
    .account-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 24px;
    }
    
    @media (max-width: 768px) {
        .account-grid {
            grid-template-columns: 1fr;
        }
    }
    
    .account-card {
        background: white;
        padding: 30px;
        border-radius: 12px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    }
    
    .account-card h2 {
        font-size: 1.5rem;
        color: #1e293b;
        margin-bottom: 24px;
        padding-bottom: 12px;
        border-bottom: 2px solid #e2e8f0;
    }
    
    .form-group {
        margin-bottom: 20px;
    }
    
    .form-group label {
        display: block;
        margin-bottom: 8px;
        font-weight: 600;
        color: #334155;
    }
    
    .form-group input,
    .form-group textarea {
        width: 100%;
        padding: 12px 16px;
        border: 2px solid #e2e8f0;
        border-radius: 8px;
        font-size: 0.95rem;
        font-family: inherit;
    }
    
    .form-group input:focus,
    .form-group textarea:focus {
        outline: none;
        border-color: #38bdf8;
        box-shadow: 0 0 0 3px rgba(56, 189, 248, 0.1);
    }
    
    .btn-primary {
        padding: 12px 24px;
        background: linear-gradient(135deg, #38bdf8 0%, #0284c7 100%);
        color: white;
        border: none;
        border-radius: 8px;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.2s;
    }
    
    .btn-primary:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(56, 189, 248, 0.3);
    }
    
    .alert {
        padding: 12px 16px;
        border-radius: 8px;
        margin-bottom: 20px;
    }
    
    .alert-error {
        background: #fee2e2;
        color: #991b1b;
        border: 1px solid #fecaca;
    }
    
    .alert-success {
        background: #d1fae5;
        color: #065f46;
        border: 1px solid #a7f3d0;
    }
    
    .comment-item {
        padding: 16px;
        background: #f8fafc;
        border-radius: 8px;
        margin-bottom: 12px;
    }
    
    .comment-post {
        font-weight: 600;
        color: #38bdf8;
        margin-bottom: 8px;
    }
    
    .comment-post a {
        color: inherit;
        text-decoration: none;
    }
    
    .comment-post a:hover {
        text-decoration: underline;
    }
    
    .comment-content {
        color: #475569;
        margin-bottom: 8px;
    }
    
    .comment-meta {
        font-size: 0.85rem;
        color: #94a3b8;
    }
    
    .comment-status {
        display: inline-block;
        padding: 2px 8px;
        border-radius: 4px;
        font-size: 0.75rem;
        font-weight: 600;
        margin-left: 8px;
    }
    
    .comment-status.approved {
        background: #d1fae5;
        color: #065f46;
    }
    
    .comment-status.pending {
        background: #fef3c7;
        color: #92400e;
    }
</style>

<div class="account-container">
    <div class="account-header">
        <img src="<?php echo htmlspecialchars($user['avatar'] ?? 'https://ui-avatars.com/api/?name=User&background=38bdf8&color=fff&size=200'); ?>" 
             alt="Avatar" class="account-avatar">
        <div class="account-info">
            <h1><?php echo htmlspecialchars($user['full_name']); ?></h1>
            <p><i class="fas fa-user"></i> @<?php echo htmlspecialchars($user['username']); ?></p>
            <p><i class="fas fa-envelope"></i> <?php echo htmlspecialchars($user['email']); ?></p>
            <p><i class="fas fa-clock"></i> Tham gia: <?php echo date('d/m/Y', strtotime($user['created_at'])); ?></p>
        </div>
    </div>
    
    <?php if ($error): ?>
        <div class="alert alert-error">
            <i class="fas fa-exclamation-circle"></i> <?php echo htmlspecialchars($error); ?>
        </div>
    <?php endif; ?>
    
    <?php if ($success): ?>
        <div class="alert alert-success">
            <i class="fas fa-check-circle"></i> <?php echo htmlspecialchars($success); ?>
        </div>
    <?php endif; ?>
    
    <div class="account-grid">
        <!-- Update Profile -->
        <div class="account-card">
            <h2><i class="fas fa-user-edit"></i> Cập nhật thông tin</h2>
            <form method="POST" action="">
                <input type="hidden" name="action" value="update_profile">
                
                <div class="form-group">
                    <label for="full_name">Họ và tên</label>
                    <input type="text" id="full_name" name="full_name" 
                           value="<?php echo htmlspecialchars($user['full_name']); ?>" required>
                </div>
                
                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" id="email" name="email" 
                           value="<?php echo htmlspecialchars($user['email']); ?>" required>
                </div>
                
                <div class="form-group">
                    <label for="avatar">URL Avatar</label>
                    <input type="url" id="avatar" name="avatar" 
                           value="<?php echo htmlspecialchars($user['avatar'] ?? ''); ?>">
                </div>
                
                <div class="form-group">
                    <label for="bio">Giới thiệu</label>
                    <textarea id="bio" name="bio" rows="3"><?php echo htmlspecialchars($user['bio'] ?? ''); ?></textarea>
                </div>
                
                <button type="submit" class="btn-primary">
                    <i class="fas fa-save"></i> Lưu thay đổi
                </button>
            </form>
        </div>
        
        <!-- Change Password -->
        <div class="account-card">
            <h2><i class="fas fa-key"></i> Đổi mật khẩu</h2>
            <form method="POST" action="">
                <input type="hidden" name="action" value="change_password">
                
                <div class="form-group">
                    <label for="current_password">Mật khẩu hiện tại</label>
                    <input type="password" id="current_password" name="current_password" required>
                </div>
                
                <div class="form-group">
                    <label for="new_password">Mật khẩu mới</label>
                    <input type="password" id="new_password" name="new_password" required>
                </div>
                
                <div class="form-group">
                    <label for="confirm_password">Xác nhận mật khẩu mới</label>
                    <input type="password" id="confirm_password" name="confirm_password" required>
                </div>
                
                <button type="submit" class="btn-primary">
                    <i class="fas fa-lock"></i> Đổi mật khẩu
                </button>
            </form>
        </div>
    </div>
    
    <!-- Recent Comments -->
    <div class="account-card" style="margin-top: 24px;">
        <h2><i class="fas fa-comments"></i> Bình luận gần đây</h2>
        
        <?php if (!empty($comments)): ?>
            <?php foreach ($comments as $comment): ?>
                <div class="comment-item">
                    <div class="comment-post">
                        <a href="<?php echo buildArticleUrl(['id' => $comment['post_id'], 'title' => $comment['post_title'], 'title_en' => $comment['title_en'] ?? '']); ?>">
                            <?php echo htmlspecialchars($comment['post_title']); ?>
                        </a>
                        <span class="comment-status <?php echo $comment['status']; ?>">
                            <?php echo $comment['status'] === 'approved' ? 'Đã duyệt' : 'Chờ duyệt'; ?>
                        </span>
                    </div>
                    <div class="comment-content">
                        <?php echo htmlspecialchars($comment['content']); ?>
                    </div>
                    <div class="comment-meta">
                        <i class="far fa-clock"></i> <?php echo date('d/m/Y H:i', strtotime($comment['created_at'])); ?>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p style="color: #94a3b8; text-align: center; padding: 20px;">
                Bạn chưa có bình luận nào.
            </p>
        <?php endif; ?>
    </div>
</div>

<?php include 'inc/footer-new.php'; ?>

