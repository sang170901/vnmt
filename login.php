<?php
session_start();
require_once 'backend/inc/db.php';

// Redirect if already logged in
if (isset($_SESSION['user_id'])) {
    header('Location: account.php');
    exit;
}

$error = '';
$oauthError = '';

// Check for OAuth error
if (isset($_SESSION['oauth_error'])) {
    $oauthError = $_SESSION['oauth_error'];
    unset($_SESSION['oauth_error']);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    
    if (empty($username) || empty($password)) {
        $error = 'Vui lòng nhập username và mật khẩu!';
    } else {
        try {
            $pdo = getPDO();
            
            // Find user by username or email
            $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ? OR email = ? LIMIT 1");
            $stmt->execute([$username, $username]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($user && password_verify($password, $user['password'])) {
                // Check if account is active
                if ($user['status'] == 0) {
                    $error = 'Tài khoản của bạn đã bị khóa!';
                } else {
                    // Login successful
                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['username'] = $user['username'];
                    $_SESSION['full_name'] = $user['full_name'];
                    $_SESSION['email'] = $user['email'];
                    $_SESSION['avatar'] = $user['avatar'];
                    $_SESSION['role'] = $user['role'];
                    
                    // Redirect to previous page or homepage
                    $redirect = $_GET['redirect'] ?? 'index.php';
                    header('Location: ' . $redirect);
                    exit;
                }
            } else {
                $error = 'Tên đăng nhập hoặc mật khẩu không đúng!';
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
    
    .divider {
        display: flex;
        align-items: center;
        text-align: center;
        margin: 24px 0;
        color: #94a3b8;
        font-size: 0.875rem;
    }
    
    .divider::before,
    .divider::after {
        content: '';
        flex: 1;
        border-bottom: 1px solid #e2e8f0;
    }
    
    .divider span {
        padding: 0 12px;
    }
    
    .social-login {
        display: flex;
        gap: 12px;
        margin-bottom: 24px;
    }
    
    .social-btn {
        flex: 1;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        padding: 12px;
        border: 2px solid #e2e8f0;
        border-radius: 8px;
        background: white;
        color: #334155;
        font-weight: 600;
        text-decoration: none;
        transition: all 0.2s;
        cursor: pointer;
    }
    
    .social-btn:hover {
        border-color: #cbd5e1;
        background: #f8fafc;
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(0,0,0,0.1);
    }
    
    .social-btn.google {
        color: #4285f4;
    }
    
    .social-btn.google:hover {
        border-color: #4285f4;
        background: #f0f7ff;
    }
    
    .social-btn.facebook {
        color: #1877f2;
    }
    
    .social-btn.facebook:hover {
        border-color: #1877f2;
        background: #eff6ff;
    }
    
    .social-btn i {
        font-size: 1.2rem;
    }
    
    .oauth-notice {
        background: #fef3c7;
        border: 1px solid #fde68a;
        color: #92400e;
        padding: 12px;
        border-radius: 8px;
        font-size: 0.85rem;
        margin-bottom: 20px;
        text-align: center;
    }
    
    .oauth-notice a {
        color: #0284c7;
        font-weight: 600;
        text-decoration: none;
    }
    
    .oauth-notice a:hover {
        text-decoration: underline;
    }
    
    .remember-forgot {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 20px;
        font-size: 0.875rem;
    }
    
    .remember-forgot a {
        color: #38bdf8;
        text-decoration: none;
    }
</style>

<div class="auth-container">
    <div class="auth-header">
        <h1>🔐 Đăng nhập</h1>
        <p>Đăng nhập để bình luận và quản lý tài khoản</p>
    </div>
    
    <?php if ($error): ?>
        <div class="alert alert-error">
            <i class="fas fa-exclamation-circle"></i> <?php echo htmlspecialchars($error); ?>
        </div>
    <?php endif; ?>
    
    <?php 
    // Check if OAuth is configured
    $oauthConfig = file_exists('oauth_config.php') ? include('oauth_config.php') : null;
    $isOAuthConfigured = $oauthConfig && 
                         $oauthConfig['google']['client_id'] !== 'YOUR_GOOGLE_CLIENT_ID' &&
                         $oauthConfig['facebook']['app_id'] !== 'YOUR_FACEBOOK_APP_ID';
    ?>
    
    <?php if ($isOAuthConfigured): ?>
        <!-- Social Login Buttons -->
        <div class="social-login">
            <a href="oauth_login.php?provider=google" class="social-btn google">
                <i class="fab fa-google"></i>
                <span>Google</span>
            </a>
            <a href="oauth_login.php?provider=facebook" class="social-btn facebook">
                <i class="fab fa-facebook-f"></i>
                <span>Facebook</span>
            </a>
        </div>
        
        <div class="divider">
            <span>hoặc đăng nhập bằng tài khoản</span>
        </div>
    <?php else: ?>
        <!-- OAuth not configured - hide social buttons -->
        <!-- Để cấu hình OAuth, xem file OAUTH-SETUP-GUIDE.md -->
    <?php endif; ?>
    
    <form method="POST" action="">
        <div class="form-group">
            <label for="username">Tên đăng nhập hoặc Email</label>
            <input type="text" id="username" name="username" 
                   value="<?php echo htmlspecialchars($_POST['username'] ?? ''); ?>" 
                   required autofocus>
        </div>
        
        <div class="form-group">
            <label for="password">Mật khẩu</label>
            <input type="password" id="password" name="password" required>
        </div>
        
        <button type="submit" class="btn-primary">
            <i class="fas fa-sign-in-alt"></i> Đăng nhập
        </button>
    </form>
    
    <div class="auth-footer">
        Chưa có tài khoản? <a href="register.php">Đăng ký ngay</a>
    </div>
</div>

<?php include 'inc/footer-new.php'; ?>

