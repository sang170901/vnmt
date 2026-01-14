<?php
session_start();
require_once 'backend/inc/db.php';

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $full_name = trim($_POST['full_name'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    
    // Validation
    if (empty($username) || empty($email) || empty($full_name) || empty($password)) {
        $error = 'Vui lòng điền đầy đủ thông tin!';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Email không hợp lệ!';
    } elseif (strlen($password) < 6) {
        $error = 'Mật khẩu phải có ít nhất 6 ký tự!';
    } elseif ($password !== $confirm_password) {
        $error = 'Mật khẩu xác nhận không khớp!';
    } else {
        try {
            $pdo = getPDO();
            
            // Check if username or email exists
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE username = ? OR email = ?");
            $stmt->execute([$username, $email]);
            
            if ($stmt->fetchColumn() > 0) {
                $error = 'Username hoặc email đã tồn tại!';
            } else {
                // Create user
                $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
                $avatar = "https://ui-avatars.com/api/?name=" . urlencode($full_name) . "&background=38bdf8&color=fff&size=200";
                
                $stmt = $pdo->prepare("
                    INSERT INTO users (username, email, password, full_name, avatar, role, status)
                    VALUES (?, ?, ?, ?, ?, 'user', 1)
                ");
                $stmt->execute([$username, $email, $hashedPassword, $full_name, $avatar]);
                
                $success = 'Đăng ký thành công! Đang chuyển hướng đến trang đăng nhập...';
                header("refresh:2;url=login.php");
            }
        } catch (PDOException $e) {
            $error = 'Lỗi: ' . $e->getMessage();
        }
    }
}

include 'inc/header-new.php';
?>

<style>
    .auth-container {
        max-width: 500px;
        margin: 80px auto;
        padding: 40px;
        background: white;
        border-radius: 16px;
        box-shadow: 0 4px 20px rgba(0,0,0,0.1);
    }
    
    .auth-header {
        text-align: center;
        margin-bottom: 32px;
    }
    
    .auth-header h1 {
        font-size: 2rem;
        color: #1e293b;
        margin-bottom: 8px;
    }
    
    .auth-header p {
        color: #64748b;
        font-size: 0.95rem;
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
    
    .form-group input {
        width: 100%;
        padding: 12px 16px;
        border: 2px solid #e2e8f0;
        border-radius: 8px;
        font-size: 0.95rem;
        transition: all 0.2s;
    }
    
    .form-group input:focus {
        outline: none;
        border-color: #38bdf8;
        box-shadow: 0 0 0 3px rgba(56, 189, 248, 0.1);
    }
    
    .btn-primary {
        width: 100%;
        padding: 14px;
        background: linear-gradient(135deg, #38bdf8 0%, #0284c7 100%);
        color: white;
        border: none;
        border-radius: 8px;
        font-size: 1rem;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.2s;
    }
    
    .btn-primary:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 16px rgba(56, 189, 248, 0.3);
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
    
    .auth-footer {
        text-align: center;
        margin-top: 24px;
        color: #64748b;
    }
    
    .auth-footer a {
        color: #38bdf8;
        font-weight: 600;
        text-decoration: none;
    }
    
    .auth-footer a:hover {
        text-decoration: underline;
    }
</style>

<div class="auth-container">
    <div class="auth-header">
        <h1>📝 Đăng ký tài khoản</h1>
        <p>Tạo tài khoản để bình luận và tương tác</p>
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
    
    <form method="POST" action="">
        <div class="form-group">
            <label for="full_name">Họ và tên *</label>
            <input type="text" id="full_name" name="full_name" 
                   value="<?php echo htmlspecialchars($_POST['full_name'] ?? ''); ?>" required>
        </div>
        
        <div class="form-group">
            <label for="username">Tên đăng nhập *</label>
            <input type="text" id="username" name="username" 
                   value="<?php echo htmlspecialchars($_POST['username'] ?? ''); ?>" required>
        </div>
        
        <div class="form-group">
            <label for="email">Email *</label>
            <input type="email" id="email" name="email" 
                   value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>" required>
        </div>
        
        <div class="form-group">
            <label for="password">Mật khẩu * (tối thiểu 6 ký tự)</label>
            <input type="password" id="password" name="password" required>
        </div>
        
        <div class="form-group">
            <label for="confirm_password">Xác nhận mật khẩu *</label>
            <input type="password" id="confirm_password" name="confirm_password" required>
        </div>
        
        <button type="submit" class="btn-primary">
            <i class="fas fa-user-plus"></i> Đăng ký
        </button>
    </form>
    
    <div class="auth-footer">
        Đã có tài khoản? <a href="login.php">Đăng nhập ngay</a>
    </div>
</div>

<?php include 'inc/footer-new.php'; ?>

