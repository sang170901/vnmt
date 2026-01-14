<?php 
// Start session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Load language system
require_once __DIR__ . '/../lang/lang.php';

// Load database translation helper
require_once __DIR__ . '/../lang/db_translate_helper.php';

// Load auto-translate helper for automatic translation
// DISABLED: Causes mixed language issues - use t() function instead
// require_once __DIR__ . '/../lang/auto_translate_helper.php';
// start_auto_translate();

// Track visits for analytics
if (!defined('TRACKING_DISABLED')) {
    require_once __DIR__ . '/../backend/inc/track_visit.php';
    trackVisit($_SERVER['REQUEST_URI'] ?? '/');
}

// Check if user is logged in
$isLoggedIn = isset($_SESSION['user_id']);
$userData = $isLoggedIn ? [
    'full_name' => $_SESSION['full_name'] ?? '',
    'email' => $_SESSION['email'] ?? '',
    'avatar' => $_SESSION['avatar'] ?? '',
    'username' => $_SESSION['username'] ?? ''
] : null;
?>
<!doctype html>
<html lang="<?php echo getCurrentLang(); ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>VNMaterial</title>
    <meta name="description" content="VNMaterial - Vật liệu xây dựng Việt Nam">
    
    <!-- Favicon (using logo.png temporarily until proper favicon is created) -->
    <?php 
    $logoPath = (defined('IMAGES_PATH') ? IMAGES_PATH : '/assets/images/') . 'logo.png';
    $logoVersion = file_exists(__DIR__ . '/../assets/images/logo.png') ? filemtime(__DIR__ . '/../assets/images/logo.png') : time();
    ?>
    <link rel="icon" type="image/png" href="<?php echo $logoPath; ?>?v=<?php echo $logoVersion; ?>">
    <link rel="apple-touch-icon" href="<?php echo $logoPath; ?>?v=<?php echo $logoVersion; ?>">
    
    <!-- NEW CSS ONLY -->
    <link rel="stylesheet" href="assets/css/styles-new.css?v=<?php echo time(); ?>">
    
    <!-- Responsive CSS - Must be loaded after main styles -->
    <link rel="stylesheet" href="assets/css/responsive.css?v=<?php echo time(); ?>">
    
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Nunito+Sans:wght@300;400;500;600;700;800&family=Open+Sans:wght@300;400;500;600;700&family=Source+Sans+Pro:wght@300;400;600;700&display=swap&subset=vietnamese" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Roboto+Slab:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    
    <!-- Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        /* Modern Header CSS - Embedded - Kết hợp Đỏ Pantone & Xanh Dương */
        :root {
            --header-height: 80px;
            /* Xanh dương làm màu chính */
            --primary-color: #2563eb;         /* Blue 600 */
            --primary-dark: #1d4ed8;         /* Blue 700 */
            --primary-light: #3b82f6;       /* Blue 500 */
            --primary-lighter: #60a5fa;      /* Blue 400 */
            /* Đỏ Pantone làm accent */
            --accent-color: #C8102E;         /* Pantone 186 C */
            --accent-dark: #A00D26;         /* Đỏ đậm */
            --accent-light: #DA1A32;        /* Pantone 485 C */
            --text-primary: #1e293b;
            --text-secondary: #64748b;
            --text-hover: #0f172a;
            --bg-white: #ffffff;
            /* Background kết hợp đỏ nhạt và xanh nhạt */
            --bg-header: linear-gradient(135deg, #fef2f2 0%, #fee2e2 20%, #eff6ff 50%, #dbeafe 80%, #bfdbfe 100%);
            --bg-glass: rgba(254, 242, 242, 0.95);
            --shadow-light: 0 1px 3px rgba(0, 0, 0, 0.1);
            --shadow-medium: 0 4px 6px rgba(0, 0, 0, 0.1);
            --shadow-strong: 0 10px 25px rgba(0, 0, 0, 0.15);
            --border-light: rgba(0, 0, 0, 0.08);
            /* Gradients kết hợp đỏ và xanh */
            --gradient-primary: linear-gradient(135deg, #2563eb 0%, #3b82f6 50%, #C8102E 100%);
            --gradient-hover: linear-gradient(135deg, #1d4ed8 0%, #2563eb 50%, #A00D26 100%);
            --gradient-blue: linear-gradient(135deg, #2563eb 0%, #3b82f6 100%);
            --gradient-red: linear-gradient(135deg, #C8102E 0%, #DA1A32 100%);
            --transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Open Sans', Gadget, sans-serif;
            padding-top: var(--header-height);
            transition: var(--transition);
        }

        /* Main Header */
        .new-header {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            height: var(--header-height);
            background: #fef2f2; /* Fallback color - đỏ nhạt */
            background: linear-gradient(135deg, #fef2f2 0%, #fee2e2 15%, #eff6ff 35%, #dbeafe 65%, #bfdbfe 85%, #fef2f2 100%);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border-bottom: 2px solid rgba(200, 16, 46, 0.15);
            border-bottom: 2px solid rgba(37, 99, 235, 0.2);
            border-image: linear-gradient(90deg, rgba(200, 16, 46, 0.3) 0%, rgba(37, 99, 235, 0.3) 100%) 1;
            z-index: 1000;
            transition: var(--transition);
            box-shadow: 0 4px 20px rgba(200, 16, 46, 0.1), 0 4px 20px rgba(37, 99, 235, 0.1);
        }

        /* Header animations when scrolling */
        .new-header.scrolled {
            background: linear-gradient(135deg, #fef2f2 0%, #fee2e2 30%, #eff6ff 50%, #dbeafe 70%, #fef2f2 100%);
            box-shadow: 0 10px 25px rgba(200, 16, 46, 0.15), 0 10px 25px rgba(37, 99, 235, 0.15);
            transform: translateY(-2px);
        }

        .new-header.scroll-up {
            transform: translateY(0);
        }

        .new-header.scroll-down {
            transform: translateY(-100%);
        }

        .header-container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 0 2rem;
            height: 100%;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        /* Logo Section */
        .logo-section {
            flex: 0 0 200px; /* Fixed width để cân bằng với header-actions */
            transition: var(--transition);
        }

        .logo-link {
            display: block;
            transition: var(--transition);
        }

        .logo-link:hover {
            transform: scale(1.05);
        }

        .logo {
            height: 62px; /* Giảm 30%: 88px * 0.7 = 61.6px */
            width: auto;
            transition: var(--transition);
            filter: brightness(1) contrast(1.1);
            drop-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        /* Top Progress Bar */
        .progress-bar {
            position: absolute;
            top: 0;
            left: 0;
            height: 3px;
            background: linear-gradient(90deg, #C8102E 0%, #2563eb 50%, #C8102E 100%);
            width: 0%;
            transition: width 0.3s ease;
            z-index: 1001;
        }

        /* Navigation */
        .main-nav {
            flex: 1;
            display: flex;
            justify-content: center;
        }

        .nav-list {
            display: flex;
            list-style: none;
            gap: 2.2rem;
            margin: 0;
            padding: 0;
        }

        .nav-item {
            position: relative;
            cursor: pointer;
        }

        .nav-link {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            gap: 0;
            text-decoration: none;
            color: inherit;
            padding: 0.65rem 0.95rem;
            border-radius: 0; /* Bỏ viền bo tròn */
            transition: var(--transition);
            position: relative;
            overflow: hidden;
        }

        /* Hover effect with background */
        .nav-link::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(200, 16, 46, 0.15); /* Đỏ nhạt khi hover */
            opacity: 0;
            transition: var(--transition);
            z-index: -1;
            border-radius: 0; /* Bỏ viền bo tròn */
            border: 2px solid transparent;
        }

        .nav-link:hover::before {
            opacity: 1;
            background: rgba(200, 16, 46, 0.25); /* Đỏ đậm hơn khi hover */
            border-color: rgba(200, 16, 46, 0.4);
        }

        .nav-link:hover {
            transform: translateY(-4px);
            box-shadow: 0 8px 20px rgba(200, 16, 46, 0.3);
        }

        /* Top bar indicator */
        .nav-link::after {
            content: '';
            position: absolute;
            top: 0;
            left: 50%;
            transform: translateX(-50%);
            width: 0;
            height: 4px;
            background: #C8102E; /* Đỏ cho indicator khi hover */
            transition: var(--transition);
            border-radius: 0 0 4px 4px;
            box-shadow: 0 2px 10px rgba(200, 16, 46, 0.5);
        }

        .nav-link:hover::after,
        .nav-link.active::after {
            width: 100%;
        }

        .nav-number {
            font-size: 0.85rem;
            font-weight: 700;
            color: #2563eb; /* Xanh mặc định */
            letter-spacing: 2px;
            transition: var(--transition);
        }

        .nav-text {
            font-size: 0.9rem;
            font-weight: 700;
            color: #2563eb; /* Xanh mặc định */
            letter-spacing: 1.8px;
            white-space: nowrap;
            transition: var(--transition);
        }

        .nav-link:hover .nav-number,
        .nav-link.active .nav-number {
            color: #C8102E; /* Đỏ khi hover - màu ngược lại */
            transform: translateY(-2px) scale(1.15);
            filter: drop-shadow(0 2px 8px rgba(200, 16, 46, 0.6));
        }

        .nav-link:hover .nav-text,
        .nav-link.active .nav-text {
            color: #C8102E; /* Đỏ khi hover - màu ngược lại */
            transform: translateY(-2px);
            filter: drop-shadow(0 3px 10px rgba(200, 16, 46, 0.7));
        }

        /* Action Buttons */
        .header-actions {
            display: flex;
            gap: 0.75rem;
            align-items: center;
            flex: 0 0 200px; /* Fixed width để cân bằng với logo-section */
            justify-content: flex-end; /* Align buttons to right */
            height: 44px;
        }

        .action-btn {
            width: 44px;
            height: 44px;
            min-width: 44px;
            min-height: 44px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: transparent;
            border: none;
            border-radius: 8px; /* Bỏ bo tròn, dùng góc vuông */
            color: #2563eb; /* Xanh mặc định */
            text-decoration: none;
            font-size: 1.1rem;
            font-weight: 600;
            cursor: pointer;
            transition: var(--transition);
            position: relative;
            overflow: hidden;
            flex-shrink: 0;
        }

        .action-btn::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(200, 16, 46, 0.1); /* Đỏ nhạt khi hover */
            opacity: 0;
            transition: var(--transition);
            border-radius: 8px; /* Bỏ bo tròn */
        }

        .action-btn:hover::before {
            opacity: 1;
        }

        .action-btn:hover {
            border-color: #C8102E;
            color: #C8102E; /* Đỏ khi hover */
            transform: translateY(-3px) scale(1.08);
            box-shadow: 0 4px 12px rgba(200, 16, 46, 0.4);
        }

        .action-btn i,
        .action-btn span {
            position: relative;
            z-index: 1;
        }
        
        /* User Dropdown */
        .user-menu {
            position: relative;
            display: flex;
            align-items: center;
            height: 44px;
        }
        
        .user-avatar-btn {
            width: 104px; /* Tăng 50%: 69px * 1.5 = 103.5px */
            height: 104px;
            min-width: 104px;
            min-height: 104px;
            border-radius: 50%;
            border: 3px solid rgba(37, 99, 235, 0.4); /* Xanh mặc định */
            cursor: pointer;
            transition: var(--transition);
            display: block;
            object-fit: cover;
            flex-shrink: 0;
        }
        
        .user-avatar-btn:hover {
            transform: scale(1.1);
            border-color: #C8102E; /* Đỏ khi hover */
            box-shadow: 0 0 0 3px rgba(200, 16, 46, 0.3);
        }
        
        .user-dropdown {
            position: absolute;
            top: calc(100% + 12px);
            right: 0;
            background: white;
            border-radius: 12px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.15);
            min-width: 240px;
            opacity: 0;
            visibility: hidden;
            transform: translateY(-10px);
            transition: var(--transition);
            z-index: 1002;
            border: 1px solid #e2e8f0;
        }
        
        .user-menu:hover .user-dropdown {
            opacity: 1;
            visibility: visible;
            transform: translateY(0);
        }
        
        .user-dropdown-header {
            padding: 16px;
            border-bottom: 1px solid #e2e8f0;
        }
        
        .user-dropdown-name {
            font-weight: 600;
            color: #1e293b;
            margin-bottom: 4px;
        }
        
        .user-dropdown-email {
            font-size: 0.85rem;
            color: #64748b;
        }
        
        .user-dropdown-menu {
            padding: 8px;
        }
        
        .user-dropdown-item {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 10px 12px;
            color: #334155;
            text-decoration: none;
            border-radius: 8px;
            transition: var(--transition);
        }
        
        .user-dropdown-item:hover {
            background: #f1f5f9;
            color: #38bdf8;
        }
        
        .user-dropdown-item i {
            width: 20px;
            text-align: center;
        }
        
        .user-dropdown-divider {
            height: 1px;
            background: #e2e8f0;
            margin: 8px 0;
        }
        
        .btn-login {
            width: 44px;
            height: 44px;
            min-width: 44px;
            min-height: 44px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: transparent;
            border: none;
            border-radius: 8px; /* Bỏ bo tròn, dùng góc vuông */
            color: #2563eb; /* Xanh mặc định */
            text-decoration: none;
            font-size: 1.1rem;
            cursor: pointer;
            transition: var(--transition);
            position: relative;
            overflow: hidden;
            flex-shrink: 0;
        }
        
        .btn-login::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(200, 16, 46, 0.1); /* Đỏ nhạt khi hover */
            opacity: 0;
            transition: var(--transition);
            border-radius: 8px; /* Bỏ bo tròn */
        }
        
        .btn-login:hover::before {
            opacity: 1;
        }
        
        .btn-login:hover {
            color: #C8102E; /* Đỏ khi hover */
            transform: translateY(-3px) scale(1.08);
            box-shadow: 0 4px 12px rgba(200, 16, 46, 0.4);
        }
        
        .btn-login i {
            position: relative;
            z-index: 1;
        }

        /* Hamburger Menu Button (Hidden on Desktop) */
        .mobile-menu-toggle {
            display: none;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            gap: 6px;
            width: 50px;
            height: 50px;
            background: transparent;
            border: none;
            cursor: pointer;
            padding: 8px;
            z-index: 1001;
            margin: 0 auto; /* Center horizontally */
        }

        .hamburger-line {
            width: 32px;
            height: 4px;
            background: #2563eb; /* Xanh mặc định */
            border-radius: 4px;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }
        
        .mobile-menu-toggle:hover .hamburger-line {
            background: #C8102E; /* Đỏ khi hover */
        }

        .mobile-menu-toggle.active .hamburger-line:nth-child(1) {
            transform: rotate(45deg) translateY(11px);
        }

        .mobile-menu-toggle.active .hamburger-line:nth-child(2) {
            opacity: 0;
        }

        .mobile-menu-toggle.active .hamburger-line:nth-child(3) {
            transform: rotate(-45deg) translateY(-11px);
        }

        /* Mobile Responsive - Enhanced */
        @media (max-width: 1024px) {
            .header-container {
                padding: 0 1.2rem;
            }
            
            .nav-list {
                gap: 1.6rem;
            }
            
            .nav-link {
                padding: 0.6rem 0.8rem;
            }
            
            .nav-text {
                font-size: 0.82rem;
                letter-spacing: 1.5px;
            }
        }

        @media (max-width: 768px) {
            :root {
                --header-height: 65px;
            }
            
            body {
                padding-top: 65px;
            }
            
            .header-container {
                padding: 0 0.75rem;
            }
            
            .logo {
                height: 50px; /* Giảm 30% từ base: 72 * 0.7 = 50.4px */
            }
            
            /* Logo & Actions sections - Equal width for center nav */
            .logo-section {
                flex: 0 0 120px;
            }
            
            .header-actions {
                flex: 0 0 220px;
            }
            
            /* Action buttons and icons */
            .action-btn {
                width: 38px;
                height: 38px;
                min-width: 38px;
                min-height: 38px;
                font-size: 0.8rem;
            }
            
            .user-avatar-btn {
                width: 38px;
                height: 38px;
                min-width: 38px;
                min-height: 38px;
            }
            
            /* Show Hamburger Menu - Centered */
            .mobile-menu-toggle {
                display: flex;
                position: absolute;
                left: 50%;
                transform: translateX(-50%);
            }
            
            /* Hide Desktop Nav */
            .main-nav {
                position: fixed;
                top: 60px;
                left: -100%;
                width: 280px;
                height: calc(100vh - 60px);
                background: white;
                box-shadow: 2px 0 16px rgba(0, 0, 0, 0.08);
                transition: left 0.3s ease;
                z-index: 999;
                overflow-y: auto;
            }
            
            .main-nav.active {
                left: 0;
            }
            
            .nav-list {
                flex-direction: column;
                gap: 0;
                padding: 0.5rem 0;
            }
            
            .nav-item {
                width: 100%;
            }
            
            .nav-link {
                width: 100%;
                padding: 1.2rem 1.5rem;
                flex-direction: row;
                justify-content: flex-start;
                align-items: center;
                gap: 1rem;
                border-radius: 0;
                background: transparent;
                border: none;
                border-left: 3px solid transparent;
                transition: all 0.2s ease;
                position: relative;
            }
            
            .nav-link::before,
            .nav-link::after {
                display: none;
            }
            
            .nav-link:hover {
                background: rgba(200, 16, 46, 0.1);
                border-left-color: #C8102E;
                transform: none;
                box-shadow: none;
            }

            .nav-link.active {
                background: rgba(37, 99, 235, 0.1);
                border-left-color: #2563eb;
            }

            .nav-number {
                font-size: 0.85rem;
                font-weight: 600;
                color: #2563eb; /* Xanh mặc định */
                min-width: 24px;
            }

            .nav-text {
                font-size: 1rem;
                font-weight: 500;
                letter-spacing: 0;
                color: #2563eb; /* Xanh mặc định */
                flex: 1;
            }

            .nav-link:hover .nav-text {
                color: #C8102E; /* Đỏ khi hover */
            }
            
            .nav-link:hover .nav-number {
                color: #C8102E; /* Đỏ khi hover */
            }
            
            .action-btn, .btn-login, .lang-toggle-btn, .user-avatar-btn {
                width: 38px;
                height: 38px;
                min-width: 38px;
                min-height: 38px;
                font-size: 0.8rem;
            }
            
            .header-actions {
                gap: 0.5rem;
            }
        }
        
        /* Overlay for mobile menu */
        @media (max-width: 768px) {
            .mobile-menu-overlay {
                display: none;
                position: fixed;
                top: 60px;
                left: 0;
                right: 0;
                bottom: 0;
                background: rgba(0, 0, 0, 0.5);
                z-index: 998;
            }
            
            .mobile-menu-overlay.active {
                display: block;
            }
        }
        
        @media (max-width: 640px) {
            :root {
                --header-height: 62px;
            }
            
            body {
                padding-top: 62px;
            }
            
            .header-container {
                padding: 0 0.45rem;
            }
            
            .logo {
                height: 48px; /* Giảm 30%: 68 * 0.7 = 47.6px */
            }
            
            .logo-section {
                flex: 0 0 110px;
            }
            
            .header-actions {
                flex: 0 0 200px;
            }
            
            /* Action buttons */
            .action-btn {
                width: 36px;
                height: 36px;
                min-width: 36px;
                min-height: 36px;
                font-size: 0.75rem;
            }
            
            .user-avatar-btn {
                width: 36px;
                height: 36px;
                min-width: 36px;
                min-height: 36px;
            }
            
            .nav-list {
                gap: 0.85rem;
            }
            
            .nav-link {
                padding: 0.4rem 0.6rem;
            }
            
            .nav-text {
                font-size: 0.68rem;
                letter-spacing: 1px;
            }
            
            .nav-number {
                font-size: 0.62rem;
            }
            
            .action-btn, .btn-login, .lang-toggle-btn, .user-avatar-btn {
                width: 36px;
                height: 36px;
                min-width: 36px;
                min-height: 36px;
                font-size: 0.75rem;
            }
            
            .header-actions {
                gap: 0.4rem;
            }
        }

        @media (max-width: 480px) {
            :root {
                --header-height: 58px;
            }
            
            body {
                padding-top: 58px;
            }
            
            .header-container {
                padding: 0 0.3rem;
            }
            
            .logo {
                height: 43px; /* Giảm 30%: 62 * 0.7 = 43.4px */
            }
            
            .logo-section {
                flex: 0 0 100px;
            }
            
            .header-actions {
                flex: 0 0 180px;
                gap: 0.4rem;
            }
            
            /* Action buttons */
            .action-btn {
                width: 34px;
                height: 34px;
                min-width: 34px;
                min-height: 34px;
                font-size: 0.7rem;
            }
            
            .user-avatar-btn {
                width: 34px;
                height: 34px;
                min-width: 34px;
                min-height: 34px;
            }
            
            .nav-list {
                gap: 0.7rem;
            }
            
            .nav-link {
                padding: 0.35rem 0.5rem;
            }
            
            .nav-text {
                font-size: 0.64rem;
                letter-spacing: 0.8px;
            }
            
            .nav-number {
                display: none;
            }
            
            .action-btn, .btn-login, .lang-toggle-btn, .user-avatar-btn {
                width: 34px;
                height: 34px;
                min-width: 34px;
                min-height: 34px;
                font-size: 0.7rem;
            }
            
            .header-actions {
                gap: 0.35rem;
                height: 34px;
            }
        }
        
        @media (max-width: 375px) {
            :root {
                --header-height: 56px;
            }
            
            body {
                padding-top: 56px;
            }
            
            .header-container {
                padding: 0 0.3rem;
            }
            
            .logo {
                height: 41px; /* Giảm 30%: 58 * 0.7 = 40.6px */
            }
            
            .logo-section {
                flex: 0 0 90px;
            }
            
            .header-actions {
                flex: 0 0 170px;
                gap: 0.35rem;
            }
            
            /* Action buttons */
            .action-btn {
                width: 32px;
                height: 32px;
                min-width: 32px;
                min-height: 32px;
                font-size: 0.65rem;
            }
            
            .user-avatar-btn {
                width: 32px;
                height: 32px;
                min-width: 32px;
                min-height: 32px;
            }
            
            .nav-list {
                gap: 0.7rem;
            }
            
            .nav-link {
                padding: 0.38rem 0.55rem;
            }
            
            .nav-text {
                font-size: 0.65rem;
                letter-spacing: 0.8px;
            }
            
            .action-btn, .btn-login, .lang-toggle-btn, .user-avatar-btn {
                width: 32px;
                height: 32px;
                min-width: 32px;
                min-height: 32px;
                font-size: 0.65rem;
            }
            
            .header-actions {
                gap: 0.35rem;
                height: 32px;
            }
        }
        
        @media (max-width: 320px) {
            :root {
                --header-height: 54px;
            }
            
            body {
                padding-top: 54px;
            }
            
            .header-container {
                padding: 0 0.25rem;
            }
            
            .logo {
                height: 36px; /* Giảm 30%: 52 * 0.7 = 36.4px */
            }
            
            .logo-section {
                flex: 0 0 80px;
            }
            
            .header-actions {
                flex: 0 0 160px;
                gap: 0.3rem;
            }
            
            /* Action buttons */
            .action-btn {
                width: 30px;
                height: 30px;
                min-width: 30px;
                min-height: 30px;
                font-size: 0.6rem;
            }
            
            .user-avatar-btn {
                width: 30px;
                height: 30px;
                min-width: 30px;
                min-height: 30px;
            }
            
            .nav-list {
                gap: 0.5rem;
            }
            
            .nav-link {
                padding: 0.35rem 0.48rem;
            }
            
            .nav-text {
                font-size: 0.62rem;
                letter-spacing: 0.6px;
            }
            
            .action-btn, .btn-login, .lang-toggle-btn, .user-avatar-btn {
                width: 30px;
                height: 30px;
                min-width: 30px;
                min-height: 30px;
                font-size: 0.6rem;
            }
            
            .header-actions {
                gap: 0.3rem;
                height: 30px;
            }
        }

        /* Smooth animations */
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .new-header {
            animation: fadeInUp 0.6s ease-out;
        }

        .nav-link:focus {
            outline: none;
        }

        /* Active state - enhance visibility */
        .nav-link.active {
            background: linear-gradient(135deg, rgba(37, 99, 235, 0.12) 0%, rgba(200, 16, 46, 0.12) 100%);
        }
    
        /* Custom Language Toggle Button */
        .lang-toggle-btn {
            width: 44px;
            height: 44px;
            min-width: 44px;
            min-height: 44px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: transparent;
            border: 2px solid rgba(37, 99, 235, 0.3);
            border-radius: 8px; /* Bỏ bo tròn */
            color: #2563eb; /* Xanh mặc định */
            text-decoration: none;
            font-size: 0.85rem;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
            flex-shrink: 0;
            padding: 0;
        }
        
        .lang-toggle-btn::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(200, 16, 46, 0.1); /* Đỏ nhạt khi hover */
            opacity: 0;
            transition: all 0.3s ease;
            border-radius: 8px; /* Bỏ bo tròn */
        }
        
        .lang-toggle-btn:hover::before {
            opacity: 1;
        }
        
        .lang-toggle-btn:hover {
            border-color: #C8102E;
            color: #C8102E; /* Đỏ khi hover */
            transform: translateY(-3px) scale(1.08);
            box-shadow: 0 8px 20px rgba(200, 16, 46, 0.25);
        }
        
        .lang-toggle-btn span {
            position: relative;
            z-index: 1;
        }

    </style>
</head>
<body>
    <!-- NEW CLEAN HEADER -->
    <header class="new-header">
        <!-- Progress Bar -->
        <div class="progress-bar" id="progressBar"></div>
        
        <div class="header-container">
            <!-- Logo -->
            <div class="logo-section">
                <a href="<?php echo buildLangUrl('/'); ?>" class="logo-link">
                    <img src="<?php echo $logoPath; ?>?v=<?php echo $logoVersion; ?>" alt="VNMaterial" class="logo">
                </a>
            </div>

            <!-- Hamburger Menu Button (Mobile Only) -->
            <button class="mobile-menu-toggle" id="mobileMenuToggle" aria-label="Menu">
                <span class="hamburger-line"></span>
                <span class="hamburger-line"></span>
                <span class="hamburger-line"></span>
            </button>

            <!-- Navigation -->
            <nav class="main-nav" id="mainNav">
                <ul class="nav-list">
                    <li class="nav-item">
                        <a href="<?php echo buildLangUrl('materials'); ?>" class="nav-link">
                            <span class="nav-text"><?php echo t('nav_materials'); ?></span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="<?php echo buildLangUrl('equipment'); ?>" class="nav-link">
                            <span class="nav-text"><?php echo t('nav_equipment'); ?></span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="<?php echo buildLangUrl('technology'); ?>" class="nav-link">
                            <span class="nav-text"><?php echo t('nav_technology'); ?></span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="<?php echo buildLangUrl('landscape'); ?>" class="nav-link">
                            <span class="nav-text"><?php echo t('nav_landscape'); ?></span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="<?php echo buildLangUrl('suppliers'); ?>" class="nav-link">
                            <span class="nav-text"><?php echo t('nav_suppliers'); ?></span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="<?php echo buildLangUrl('news-modern'); ?>" class="nav-link">
                            <span class="nav-text"><?php echo t('nav_news'); ?></span>
                        </a>
                    </li>
                </ul>
            </nav>

            <!-- Action Buttons -->
            <div class="header-actions">
                <button class="action-btn" id="searchButton" aria-label="<?php echo t('search'); ?>">
                    <i class="fas fa-search"></i>
                </button>
                
                <?php if ($isLoggedIn): ?>
                    <!-- User Menu (Logged In) -->
                    <div class="user-menu">
                        <img src="<?php echo htmlspecialchars($userData['avatar']); ?>" 
                             alt="<?php echo htmlspecialchars($userData['full_name']); ?>"
                             class="user-avatar-btn">
                        
                        <div class="user-dropdown">
                            <div class="user-dropdown-header">
                                <div class="user-dropdown-name">
                                    <?php echo htmlspecialchars($userData['full_name']); ?>
                                </div>
                                <div class="user-dropdown-email">
                                    @<?php echo htmlspecialchars($userData['username']); ?>
                                </div>
                            </div>
                            
                            <div class="user-dropdown-menu">
                                <a href="<?php echo buildLangUrl('account'); ?>" class="user-dropdown-item">
                                    <i class="fas fa-user"></i>
                                    <span><?php echo t('my_account'); ?></span>
                                </a>
                                <div class="user-dropdown-divider"></div>
                                <a href="<?php echo buildLangUrl('logout'); ?>" class="user-dropdown-item">
                                    <i class="fas fa-sign-out-alt"></i>
                                    <span><?php echo t('logout'); ?></span>
                                </a>
                            </div>
                        </div>
                    </div>
                <?php else: ?>
                    <!-- Login Button (Not Logged In) -->
                    <a href="<?php echo buildLangUrl('login'); ?>?redirect=<?php echo urlencode($_SERVER['REQUEST_URI']); ?>" 
                       class="btn-login"
                       title="<?php echo t('login'); ?>"
                       aria-label="<?php echo t('login'); ?>">
                        <i class="fas fa-user"></i>
                    </a>
                <?php endif; ?>
                
                <!-- Language Toggle Button (VNBuilding.vn Style) -->
                <!-- DISABLED: Chức năng chuyển ngôn ngữ tạm thời bị khóa -->
                <!--
                <a href="<?php echo getLanguageToggleUrl(); ?>" 
                   class="lang-toggle-btn" 
                   id="customLangToggle"
                   title="<?php echo isVietnamese() ? 'Switch to English' : 'Chuyển sang Tiếng Việt'; ?>">
                    <span id="langText"><?php echo getLangDisplay(getOppositeLang()); ?></span>
                </a>
                -->
            </div>
        </div>
    </header>

    <!-- Search Popup -->
    <div class="search-overlay" id="searchOverlay">
        <div class="search-popup">
            <button class="search-popup-close" id="searchClose">
                <i class="fas fa-times"></i>
            </button>
            
            <div class="search-popup-content">
                <h2 class="search-popup-title">
                    <i class="fas fa-search"></i>
                    Tìm kiếm
                </h2>
                <p class="search-popup-desc">Nhập từ khóa để tìm sản phẩm, nhà cung cấp, bài viết...</p>
                
                <form action="<?php echo buildLangUrl('search'); ?>" method="GET" class="search-form">
                    <div class="search-input-wrapper">
                        <i class="fas fa-search search-icon"></i>
                        <input type="text" 
                               name="q" 
                               class="search-input" 
                               placeholder="VD: Xi măng, Gạch ốp lát, Sơn..." 
                               autocomplete="off"
                               autofocus
                               required>
                        <button type="submit" class="search-submit">
                            <i class="fas fa-arrow-right"></i>
                        </button>
                    </div>
                </form>
                
                <div class="search-suggestions">
                    <p class="suggestions-title">Tìm kiếm phổ biến:</p>
                    <div class="suggestions-tags">
                        <a href="<?php echo buildLangUrl('search'); ?>?q=xi%20măng" class="suggestion-tag">Xi măng</a>
                        <a href="<?php echo buildLangUrl('search'); ?>?q=gạch%20ốp%20lát" class="suggestion-tag">Gạch ốp lát</a>
                        <a href="<?php echo buildLangUrl('search'); ?>?q=sơn" class="suggestion-tag">Sơn</a>
                        <a href="<?php echo buildLangUrl('search'); ?>?q=thép" class="suggestion-tag">Thép</a>
                        <a href="<?php echo buildLangUrl('search'); ?>?q=cửa%20gỗ" class="suggestion-tag">Cửa gỗ</a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <style>
    /* Search Popup Styles */
    .search-overlay {
        position: fixed;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: rgba(0, 0, 0, 0.7);
        backdrop-filter: blur(4px);
        z-index: 10000;
        display: flex;
        align-items: flex-start;
        justify-content: center;
        padding-top: 120px;
        opacity: 0;
        visibility: hidden;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    }
    
    .search-overlay.active {
        opacity: 1;
        visibility: visible;
    }
    
    .search-popup {
        background: white;
        border-radius: 20px;
        width: 90%;
        max-width: 700px;
        padding: 40px;
        position: relative;
        transform: translateY(-30px) scale(0.95);
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
    }
    
    .search-overlay.active .search-popup {
        transform: translateY(0) scale(1);
    }
    
    .search-popup-close {
        position: absolute;
        top: 20px;
        right: 20px;
        width: 40px;
        height: 40px;
        border: none;
        background: #f1f5f9;
        border-radius: 50%;
        color: #64748b;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: all 0.3s;
        font-size: 1.2rem;
    }
    
    .search-popup-close:hover {
        background: #e2e8f0;
        color: #1e293b;
        transform: rotate(90deg);
    }
    
    .search-popup-title {
        font-size: 2rem;
        font-weight: 800;
        color: #1e293b;
        margin-bottom: 8px;
        display: flex;
        align-items: center;
        gap: 12px;
    }
    
    .search-popup-title i {
        color: #0ea5e9;
    }
    
    .search-popup-desc {
        color: #64748b;
        margin-bottom: 32px;
        font-size: 1rem;
    }
    
    .search-input-wrapper {
        position: relative;
        margin-bottom: 32px;
    }
    
    .search-icon {
        position: absolute;
        left: 20px;
        top: 50%;
        transform: translateY(-50%);
        color: #94a3b8;
        font-size: 1.2rem;
    }
    
    .search-input {
        width: 100%;
        padding: 18px 60px 18px 56px;
        border: 3px solid #e2e8f0;
        border-radius: 16px;
        font-size: 1.1rem;
        transition: all 0.3s;
        background: #f8fafc;
    }
    
    .search-input:focus {
        outline: none;
        border-color: #0ea5e9;
        background: white;
        box-shadow: 0 0 0 4px rgba(14, 165, 233, 0.1);
    }
    
    .search-submit {
        position: absolute;
        right: 8px;
        top: 50%;
        transform: translateY(-50%);
        width: 48px;
        height: 48px;
        border: none;
        background: linear-gradient(135deg, #0ea5e9 0%, #06b6d4 100%);
        color: white;
        border-radius: 12px;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: all 0.3s;
        font-size: 1rem;
    }
    
    .search-submit:hover {
        transform: translateY(-50%) scale(1.05);
        box-shadow: 0 4px 12px rgba(14, 165, 233, 0.4);
    }
    
    .search-suggestions {
        padding-top: 24px;
        border-top: 1px solid #e2e8f0;
    }
    
    .suggestions-title {
        font-size: 0.875rem;
        font-weight: 600;
        color: #64748b;
        margin-bottom: 12px;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }
    
    .suggestions-tags {
        display: flex;
        flex-wrap: wrap;
        gap: 10px;
    }
    
    .suggestion-tag {
        padding: 8px 16px;
        background: #f1f5f9;
        color: #475569;
        border-radius: 20px;
        text-decoration: none;
        font-size: 0.9rem;
        transition: all 0.3s;
        border: 2px solid transparent;
    }
    
    .suggestion-tag:hover {
        background: #e0f2fe;
        color: #0284c7;
        border-color: #38bdf8;
        transform: translateY(-2px);
    }
    
    @media (max-width: 768px) {
        .search-overlay {
            padding-top: 100px;
        }
        
        .search-popup {
            padding: 28px 18px;
            width: 88%;
        }
        
        .search-popup-close {
            width: 38px;
            height: 38px;
            top: 18px;
            right: 18px;
        }
        
        .search-popup-title {
            font-size: 1.5rem;
            margin-bottom: 7px;
        }
        
        .search-popup-desc {
            font-size: 0.95rem;
            margin-bottom: 28px;
        }
        
        .search-input {
            font-size: 1rem;
            padding: 16px 54px 16px 48px;
        }
        
        .search-icon {
            left: 18px;
        }
        
        .search-submit {
            width: 46px;
            height: 46px;
        }
    }
    
    @media (max-width: 640px) {
        .search-overlay {
            padding-top: 80px;
        }
        
        .search-popup {
            padding: 24px 16px;
            width: 90%;
        }
        
        .search-popup-close {
            width: 36px;
            height: 36px;
            top: 16px;
            right: 16px;
            font-size: 1.1rem;
        }
        
        .search-popup-title {
            font-size: 1.4rem;
            margin-bottom: 6px;
        }
        
        .search-popup-desc {
            font-size: 0.9rem;
            margin-bottom: 26px;
        }
        
        .search-input {
            padding: 15px 52px 15px 46px;
            font-size: 0.95rem;
        }
        
        .search-icon {
            font-size: 1.1rem;
            left: 17px;
        }
        
        .search-submit {
            width: 44px;
            height: 44px;
            font-size: 0.95rem;
        }
        
        .suggestions-title {
            font-size: 0.8rem;
        }
        
        .suggestion-tag {
            padding: 7px 13px;
            font-size: 0.85rem;
        }
    }
    
    @media (max-width: 480px) {
        .search-overlay {
            padding-top: 70px;
        }
        
        .search-popup {
            padding: 22px 14px;
            width: 92%;
        }
        
        .search-popup-close {
            width: 34px;
            height: 34px;
            top: 14px;
            right: 14px;
            font-size: 1rem;
        }
        
        .search-popup-title {
            font-size: 1.3rem;
            margin-bottom: 5px;
            gap: 10px;
        }
        
        .search-popup-desc {
            font-size: 0.85rem;
            margin-bottom: 24px;
        }
        
        .search-input-wrapper {
            margin-bottom: 24px;
        }
        
        .search-input {
            padding: 14px 50px 14px 44px;
            font-size: 0.9rem;
            border-radius: 14px;
        }
        
        .search-icon {
            font-size: 1rem;
            left: 16px;
        }
        
        .search-submit {
            width: 42px;
            height: 42px;
            font-size: 0.9rem;
            right: 6px;
            border-radius: 10px;
        }
        
        .search-suggestions {
            padding-top: 20px;
        }
        
        .suggestions-title {
            font-size: 0.75rem;
            margin-bottom: 10px;
        }
        
        .suggestions-tags {
            gap: 8px;
        }
        
        .suggestion-tag {
            padding: 6px 12px;
            font-size: 0.8rem;
            border-radius: 16px;
        }
    }
    
    @media (max-width: 375px) {
        .search-overlay {
            padding-top: 60px;
        }
        
        .search-popup {
            padding: 20px 12px;
            width: 94%;
        }
        
        .search-popup-title {
            font-size: 1.2rem;
        }
        
        .search-popup-desc {
            font-size: 0.8rem;
        }
        
        .search-input {
            padding: 13px 48px 13px 42px;
            font-size: 0.85rem;
        }
    }
    
    @media (max-width: 320px) {
        .search-overlay {
            padding-top: 55px;
        }
        
        .search-popup {
            padding: 18px 10px;
            width: 95%;
        }
        
        .search-popup-close {
            width: 32px;
            height: 32px;
            top: 12px;
            right: 12px;
        }
        
        .search-popup-title {
            font-size: 1.15rem;
        }
        
        .search-popup-desc {
            font-size: 0.78rem;
            margin-bottom: 22px;
        }
        
        .search-input {
            padding: 12px 46px 12px 40px;
            font-size: 0.82rem;
        }
        
        .search-submit {
            width: 40px;
            height: 40px;
        }
        
        .suggestion-tag {
            padding: 5px 10px;
            font-size: 0.75rem;
        }
    }
    </style>

    <script>
        // Mobile Menu Toggle
        document.addEventListener('DOMContentLoaded', function() {
            const mobileMenuToggle = document.getElementById('mobileMenuToggle');
            const mainNav = document.getElementById('mainNav');
            const mobileMenuOverlay = document.getElementById('mobileMenuOverlay');
            
            if (mobileMenuToggle && mainNav) {
                mobileMenuToggle.addEventListener('click', function() {
                    this.classList.toggle('active');
                    mainNav.classList.toggle('active');
                    mobileMenuOverlay.classList.toggle('active');
                    document.body.style.overflow = mainNav.classList.contains('active') ? 'hidden' : '';
                });
                
                // Close menu when clicking overlay
                mobileMenuOverlay.addEventListener('click', function() {
                    mobileMenuToggle.classList.remove('active');
                    mainNav.classList.remove('active');
                    this.classList.remove('active');
                    document.body.style.overflow = '';
                });
                
                // Close menu when clicking a link
                const navLinks = mainNav.querySelectorAll('.nav-link');
                navLinks.forEach(link => {
                    link.addEventListener('click', function() {
                        mobileMenuToggle.classList.remove('active');
                        mainNav.classList.remove('active');
                        mobileMenuOverlay.classList.remove('active');
                        document.body.style.overflow = '';
                    });
                });
            }
        });
        
        // Header scroll effects and progress bar
        document.addEventListener('DOMContentLoaded', function() {
            const header = document.querySelector('.new-header');
            const progressBar = document.getElementById('progressBar');
            let lastScrollTop = 0;
            let ticking = false;

            function updateScrollProgress() {
                const scrollTop = window.pageYOffset || document.documentElement.scrollTop;
                const scrollHeight = document.documentElement.scrollHeight - window.innerHeight;
                const scrollPercentage = (scrollTop / scrollHeight) * 100;
                
                progressBar.style.width = scrollPercentage + '%';
            }

            function updateHeaderOnScroll() {
                const scrollTop = window.pageYOffset || document.documentElement.scrollTop;
                
                // Add/remove scrolled class
                if (scrollTop > 50) {
                    header.classList.add('scrolled');
                } else {
                    header.classList.remove('scrolled');
                }

                // Show/hide header based on scroll direction
                if (scrollTop > 100) {
                    if (scrollTop > lastScrollTop && !header.classList.contains('scroll-down')) {
                        // Scrolling down
                        header.classList.remove('scroll-up');
                        header.classList.add('scroll-down');
                    } else if (scrollTop < lastScrollTop && !header.classList.contains('scroll-up')) {
                        // Scrolling up
                        header.classList.remove('scroll-down');
                        header.classList.add('scroll-up');
                    }
                } else {
                    header.classList.remove('scroll-down', 'scroll-up');
                }

                lastScrollTop = scrollTop;
                updateScrollProgress();
            }

            function requestTick() {
                if (!ticking) {
                    requestAnimationFrame(updateHeaderOnScroll);
                    ticking = true;
                }
            }

            function handleScroll() {
                ticking = false;
                requestTick();
            }

            // Smooth scroll for navigation links
            document.querySelectorAll('.nav-link[href^="#"]').forEach(anchor => {
                anchor.addEventListener('click', function (e) {
                    e.preventDefault();
                    const target = document.querySelector(this.getAttribute('href'));
                    if (target) {
                        target.scrollIntoView({
                            behavior: 'smooth',
                            block: 'start'
                        });
                    }
                });
            });

            // Add scroll event listener
            window.addEventListener('scroll', handleScroll, { passive: true });
            
            // Initial call
            updateScrollProgress();

            // Highlight active menu based on current page
            const currentPath = window.location.pathname;
            const currentPage = currentPath.split('/').pop() || 'index.php';
            
            // Get all nav links
            const navLinks = document.querySelectorAll('.nav-link');
            
            // Category mapping for product pages
            const categoryMap = {
                'vật liệu': 'materials.php',
                'vat lieu': 'materials.php',
                'thiết bị': 'equipment.php',
                'thiet bi': 'equipment.php',
                'công nghệ': 'technology.php',
                'cong nghe': 'technology.php',
                'cảnh quan': 'landscape.php',
                'canh quan': 'landscape.php'
            };
            
            // Function to highlight menu item
            function highlightMenu(targetPage) {
                navLinks.forEach(link => {
                    const linkHref = link.getAttribute('href');
                    if (linkHref && linkHref.includes(targetPage)) {
                        link.classList.add('active');
                    }
                });
            }
            
            // Check if it's a product detail page
            if (currentPage === 'product-detail.php') {
                // Try to get category from URL parameter
                const urlParams = new URLSearchParams(window.location.search);
                const category = urlParams.get('category');
                
                if (category && categoryMap[category.toLowerCase()]) {
                    highlightMenu(categoryMap[category.toLowerCase()]);
                } else {
                    // Try to get category from page meta or data attribute
                    const categoryElement = document.querySelector('[data-product-category]');
                    if (categoryElement) {
                        const catValue = categoryElement.getAttribute('data-product-category').toLowerCase();
                        if (categoryMap[catValue]) {
                            highlightMenu(categoryMap[catValue]);
                        }
                    }
                }
            }
            // Check if it's a supplier detail page
            else if (currentPage === 'supplier-detail.php') {
                highlightMenu('suppliers.php');
            }
            // Check if it's a product listing page (products.php)
            else if (currentPage === 'products.php') {
                const urlParams = new URLSearchParams(window.location.search);
                const category = urlParams.get('category');
                
                if (category && categoryMap[category.toLowerCase()]) {
                    highlightMenu(categoryMap[category.toLowerCase()]);
                }
            }
            // Regular page matching
            else {
                highlightMenu(currentPage);
            }
            
            // Search popup functionality
            const searchBtn = document.getElementById('searchButton');
            const searchOverlay = document.getElementById('searchOverlay');
            const searchClose = document.getElementById('searchClose');
            const searchInput = document.querySelector('.search-input');
            
            if (searchBtn) {
                searchBtn.addEventListener('click', function(e) {
                    e.preventDefault();
                    searchOverlay.classList.add('active');
                    // Focus on input after animation
                    setTimeout(() => {
                        if (searchInput) searchInput.focus();
                    }, 300);
                });
            }
            
            if (searchClose) {
                searchClose.addEventListener('click', function() {
                    searchOverlay.classList.remove('active');
                });
            }
            
            // Close on overlay click (not popup content)
            if (searchOverlay) {
                searchOverlay.addEventListener('click', function(e) {
                    if (e.target === searchOverlay) {
                        searchOverlay.classList.remove('active');
                    }
                });
            }
            
            // Close on ESC key
            document.addEventListener('keydown', function(e) {
                if (e.key === 'Escape' && searchOverlay.classList.contains('active')) {
                    searchOverlay.classList.remove('active');
                }
            });
        });
    </script>

    <!-- Mobile Menu Overlay -->
    <div class="mobile-menu-overlay" id="mobileMenuOverlay"></div>

    <!-- MAIN CONTENT START -->
    <main class="main-content">