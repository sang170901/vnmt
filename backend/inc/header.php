<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <title>VNMaterial Admin</title>
    <link rel="stylesheet" href="assets/css/admin.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <script>window.appConfig = {};</script>
</head>
<body>
<?php $request = $_SERVER['REQUEST_URI'] ?? $_SERVER['PHP_SELF'] ?? ''; ?>
<div class="app">
  <aside class="sidebar">
    <div class="brand">
      <div class="brand-logo"><i class="fas fa-cube"></i></div>
      <div class="brand-text">VNMaterial</div>
    </div>
    <nav>
  <a href="index.php" class="nav-item <?php echo (strpos($request, 'index.php') !== false) ? 'active' : '' ?>">
        <span class="nav-icon"><i class="fas fa-tachometer-alt"></i></span>
        <span class="nav-text">Tổng quan</span>
      </a>
  <a href="suppliers.php" class="nav-item <?php echo (strpos($request, 'suppliers.php') !== false) ? 'active' : '' ?>">
        <span class="nav-icon"><i class="fas fa-truck"></i></span>
        <span class="nav-text">Nhà cung cấp</span>
      </a>
  <a href="users.php" class="nav-item <?php echo (strpos($request, 'users.php') !== false) ? 'active' : '' ?>">
        <span class="nav-icon"><i class="fas fa-users"></i></span>
        <span class="nav-text">Khách hàng</span>
      </a>
  <a href="products.php" class="nav-item <?php echo (strpos($request, 'products.php') !== false) ? 'active' : '' ?>">
        <span class="nav-icon"><i class="fas fa-box"></i></span>
        <span class="nav-text">Sản phẩm</span>
      </a>
  <a href="import_csv.php" class="nav-item <?php echo (strpos($request, 'import_csv.php') !== false) ? 'active' : '' ?>">
        <span class="nav-icon"><i class="fas fa-file-csv"></i></span>
        <span class="nav-text">Nhập CSV</span>
      </a>
  <a href="categories.php" class="nav-item <?php echo (strpos($request, 'categories.php') !== false) ? 'active' : '' ?>">
        <span class="nav-icon"><i class="fas fa-folder-tree"></i></span>
        <span class="nav-text">Danh mục</span>
      </a>
  <a href="posts.php" class="nav-item <?php echo (strpos($request, 'posts.php') !== false || strpos($request, 'post_form.php') !== false) ? 'active' : '' ?>">
        <span class="nav-icon"><i class="fas fa-newspaper"></i></span>
        <span class="nav-text">Bài viết</span>
      </a>
  <a href="comments.php" class="nav-item <?php echo (strpos($request, 'comments.php') !== false) ? 'active' : '' ?>">
        <span class="nav-icon"><i class="fas fa-comments"></i></span>
        <span class="nav-text">Bình luận</span>
      </a>
  <a href="vouchers.php" class="nav-item <?php echo (strpos($request, 'vouchers.php') !== false) ? 'active' : '' ?>">
        <span class="nav-icon"><i class="fas fa-tags"></i></span>
        <span class="nav-text">Mã giảm giá</span>
      </a>
  <a href="sliders.php" class="nav-item <?php echo (strpos($request, 'sliders.php') !== false) ? 'active' : '' ?>">
        <span class="nav-icon"><i class="fas fa-images"></i></span>
        <span class="nav-text">Banner</span>
      </a>
  <a href="partners.php" class="nav-item <?php echo (strpos($request, 'partners.php') !== false) ? 'active' : '' ?>">
        <span class="nav-icon"><i class="fas fa-handshake"></i></span>
        <span class="nav-text">Đối tác</span>
      </a>
  <a href="activity_logs.php" class="nav-item <?php echo (strpos($request, 'activity_logs.php') !== false) ? 'active' : '' ?>">
        <span class="nav-icon"><i class="fas fa-history"></i></span>
        <span class="nav-text">Nhật ký</span>
      </a>
  <a href="logout.php" class="nav-item <?php echo (strpos($request, 'logout.php') !== false) ? 'active' : '' ?>">
        <span class="nav-icon"><i class="fas fa-sign-out-alt"></i></span>
        <span class="nav-text">Đăng xuất</span>
      </a>
    </nav>
  </aside>
  <div class="main">
    <div class="main-header">
      <div style="display:flex;gap:12px;align-items:center;justify-content:center">
        <div style="font-weight:700;color:#1f2937;font-size:18px"></div>
      </div>
    </div>
    <div class="main-content">
