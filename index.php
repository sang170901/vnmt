<?php 
// Set proper encoding for Vietnamese
header('Content-Type: text/html; charset=UTF-8');

require_once 'config.php';
// Load language system (header will also load it, but this ensures it's available)
require_once 'lang/lang.php';
require_once 'inc/url_helpers.php';

include 'inc/header-new.php'; 
?>

<!-- Full Page Scroll Container -->
<div class="fullpage-container">
<?php include 'inc/slider.php'; ?>
<?php
require_once 'backend/inc/db.php';

// Đảm bảo kết nối cơ sở dữ liệu
$conn = getPDO(); // Sử dụng hàm getPDO() để khởi tạo kết nối

// Lấy số liệu từ cơ sở dữ liệu
try {
    $pdo = getPDO();

    // Đếm số sản phẩm
    $stmtProducts = $pdo->query("SELECT COUNT(*) AS total_products FROM products");
    $totalProducts = $stmtProducts->fetch(PDO::FETCH_ASSOC)['total_products'];

    // Đếm số nhà cung cấp (active)
    $stmtSuppliers = $pdo->query("SELECT COUNT(*) AS total_suppliers FROM suppliers WHERE status = 1");
    $totalSuppliers = $stmtSuppliers->fetch(PDO::FETCH_ASSOC)['total_suppliers'];

    // Đếm số danh mục sản phẩm (distinct categories)
    $stmtCategories = $pdo->query("SELECT COUNT(DISTINCT category) AS total_categories FROM products WHERE category IS NOT NULL AND category != ''");
    $totalCategories = $stmtCategories->fetch(PDO::FETCH_ASSOC)['total_categories'];

} catch (Exception $e) {
    $totalProducts = 0;
    $totalSuppliers = 0;
    $totalCategories = 0;
    error_log("Lỗi khi truy xuất số liệu: " . $e->getMessage());
}
?>

    <!-- Product Categories Section -->
    <section class="stats fullpage-section" data-section-label="Danh mục">
        <div class="container">
            <div class="categories-header">
                <h2 class="categories-title">
                    VNMaterials cung cấp thông tin đáng tin cậy và giải pháp đồng bộ về Vật liệu và Công nghệ xây dựng tại Việt Nam
                </h2>
            </div>
            <div class="stats-grid">
                <!-- Column 1 -->
                <div class="stat-item categories-column">
                    <ul class="categories-list">
                        <li class="category-item">
                            <a href="<?php echo buildLangUrl('materials'); ?>?search=nhôm+kính" class="category-link">
                                <span class="category-bullet"></span>
                                <span class="category-text">NHÔM KÍNH - ALU - POLY</span>
                            </a>
                        </li>
                        <li class="category-item">
                            <a href="<?php echo buildLangUrl('materials.php'); ?>?search=trần+xuyên+sáng" class="category-link">
                                <span class="category-bullet"></span>
                                <span class="category-text">TRẦN XUYÊN SÁNG – TRẦN IN</span>
                            </a>
                        </li>
                        <li class="category-item">
                            <a href="<?php echo buildLangUrl('materials.php'); ?>?search=trần+nhôm" class="category-link">
                                <span class="category-bullet"></span>
                                <span class="category-text">TRẦN NHÔM - TRẦN NHỰA</span>
                            </a>
                        </li>
                        <li class="category-item">
                            <a href="<?php echo buildLangUrl('materials.php'); ?>?search=trần+thạch+cao" class="category-link">
                                <span class="category-bullet"></span>
                                <span class="category-text">TRẦN THẠCH CAO – TRẦN GỖ</span>
                            </a>
                        </li>
                        <li class="category-item">
                            <a href="<?php echo buildLangUrl('materials.php'); ?>?search=gỗ+mặt+dựng" class="category-link">
                                <span class="category-bullet"></span>
                                <span class="category-text">GỖ MẶT DỰNG - SÀN GỖ</span>
                            </a>
                        </li>
                        <li class="category-item">
                            <a href="<?php echo buildLangUrl('materials.php'); ?>?search=vật+liệu+giả+gỗ" class="category-link">
                                <span class="category-bullet"></span>
                                <span class="category-text">VẬT LIỆU GIẢ GỖ - TRE TẦM VÔNG</span>
                            </a>
                        </li>
                        <li class="category-item">
                            <a href="<?php echo buildLangUrl('materials.php'); ?>?search=sơn+tường" class="category-link">
                                <span class="category-bullet"></span>
                                <span class="category-text">SƠN TƯỜNG – SƠN KIM LOẠI</span>
                            </a>
                        </li>
                        <li class="category-item">
                            <a href="<?php echo buildLangUrl('materials.php'); ?>?search=mái+ngói" class="category-link">
                                <span class="category-bullet"></span>
                                <span class="category-text">MÁI NGÓI – TÔN – NHÔM - NHỰA</span>
                            </a>
                        </li>
                    </ul>
                </div>
                
                <!-- Column 2 -->
                <div class="stat-item categories-column">
                    <ul class="categories-list">
                        <li class="category-item">
                            <a href="<?php echo buildLangUrl('materials.php'); ?>?search=nano+phủ+chống+thấm" class="category-link">
                                <span class="category-bullet"></span>
                                <span class="category-text">NANO PHỦ CHỐNG THẤM</span>
                            </a>
                        </li>
                        <li class="category-item">
                            <a href="<?php echo buildLangUrl('materials.php'); ?>?search=nano+phủ+bảo+vệ" class="category-link">
                                <span class="category-bullet"></span>
                                <span class="category-text">NANO PHỦ BẢO VỆ BỀ MẶT</span>
                            </a>
                        </li>
                        <li class="category-item">
                            <a href="<?php echo buildLangUrl('equipment'); ?>?search=thiết+bị+phòng+cháy" class="category-link">
                                <span class="category-bullet"></span>
                                <span class="category-text">THIẾT BỊ PHÒNG CHÁY CHỮA CHÁY</span>
                            </a>
                        </li>
                        <li class="category-item">
                            <a href="<?php echo buildLangUrl('materials.php'); ?>?search=gạch+ốp+lát" class="category-link">
                                <span class="category-bullet"></span>
                                <span class="category-text">GẠCH ỐP LÁT – GẠCH TRANG TRÍ</span>
                            </a>
                        </li>
                        <li class="category-item">
                            <a href="<?php echo buildLangUrl('materials.php'); ?>?search=vữa+keo+dán" class="category-link">
                                <span class="category-bullet"></span>
                                <span class="category-text">VỮA - KEO DÁN - BỘT BẢ</span>
                            </a>
                        </li>
                        <li class="category-item">
                            <a href="<?php echo buildLangUrl('materials.php'); ?>?search=màng+keo+chống+thấm" class="category-link">
                                <span class="category-bullet"></span>
                                <span class="category-text">MÀNG - KEO CHỐNG THẤM</span>
                            </a>
                        </li>
                        <li class="category-item">
                            <a href="<?php echo buildLangUrl('materials.php'); ?>?search=ghế+sofa" class="category-link">
                                <span class="category-bullet"></span>
                                <span class="category-text">GHẾ - SOFA - BÀN TỦ - GIƯỜNG KỆ</span>
                            </a>
                        </li>
                        <li class="category-item">
                            <a href="<?php echo buildLangUrl('materials.php'); ?>?search=rèm+vải" class="category-link">
                                <span class="category-bullet"></span>
                                <span class="category-text">RÈM VẢI – RÈM GỖ - RÈM KIM LOẠI</span>
                            </a>
                        </li>
                    </ul>
                </div>
                
                <!-- Column 3 -->
                <div class="stat-item categories-column">
                    <ul class="categories-list">
                        <li class="category-item">
                            <a href="<?php echo buildLangUrl('materials.php'); ?>?search=bê+tông+mài" class="category-link">
                                <span class="category-bullet"></span>
                                <span class="category-text">BÊ TÔNG MÀI - BÊ TÔNG ĐÚC</span>
                            </a>
                        </li>
                        <li class="category-item">
                            <a href="<?php echo buildLangUrl('materials.php'); ?>?search=grc+gfrc" class="category-link">
                                <span class="category-bullet"></span>
                                <span class="category-text">GRC - GFRC - TERRAZZO</span>
                            </a>
                        </li>
                        <li class="category-item">
                            <a href="<?php echo buildLangUrl('materials.php'); ?>?search=đá+tự+nhiên" class="category-link">
                                <span class="category-bullet"></span>
                                <span class="category-text">ĐÁ TỰ NHIÊN – ĐÁ NHÂN TẠO</span>
                            </a>
                        </li>
                        <li class="category-item">
                            <a href="<?php echo buildLangUrl('equipment'); ?>?search=thiết+bị+điện" class="category-link">
                                <span class="category-bullet"></span>
                                <span class="category-text">THIẾT BỊ ĐIỆN - THIẾT BỊ BẾP</span>
                            </a>
                        </li>
                        <li class="category-item">
                            <a href="<?php echo buildLangUrl('equipment'); ?>?search=thiết+bị+văn+phòng" class="category-link">
                                <span class="category-bullet"></span>
                                <span class="category-text">THIẾT BỊ VĂN PHÒNG – NHÀ</span>
                            </a>
                        </li>
                        <li class="category-item">
                            <a href="<?php echo buildLangUrl('equipment'); ?>?search=thiết+bị+xây+dựng" class="category-link">
                                <span class="category-bullet"></span>
                                <span class="category-text">THIẾT BỊ VÀ MÁY XÂY DỰNG</span>
                            </a>
                        </li>
                        <li class="category-item">
                            <a href="<?php echo buildLangUrl('landscape'); ?>?search=cây+xanh" class="category-link">
                                <span class="category-bullet"></span>
                                <span class="category-text">CÂY XANH – CÔNG NGHỆ TƯỚI</span>
                            </a>
                        </li>
                        <li class="category-item">
                            <a href="<?php echo buildLangUrl('materials.php'); ?>?search=gốm+sứ" class="category-link">
                                <span class="category-bullet"></span>
                                <span class="category-text">GỐM SỨ - DÉCOR...</span>
                            </a>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </section>

    <style>
    /* Categories Header in Stats Section */
    .stats .categories-header {
        width: 100%;
        text-align: center;
        margin-bottom: 50px;
        padding: 0 2rem;
    }

    .stats .categories-title {
        font-size: 1.75rem;
        font-weight: 700;
        color: #1e293b;
        line-height: 1.6;
        max-width: 1400px;
        margin: 0 auto;
        position: relative;
        display: inline-block;
    }

    .stats .categories-title::after {
        content: '';
        position: absolute;
        bottom: -12px;
        left: 50%;
        transform: translateX(-50%);
        width: 120px;
        height: 3px;
        background: linear-gradient(90deg, transparent 0%, #ef4444 50%, transparent 100%);
        border-radius: 2px;
    }

    /* Categories in Stats Section */
    .stats .stat-item.categories-column {
        background: white;
        border-radius: 16px;
        padding: 32px 28px;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
        border: 1px solid rgba(226, 232, 240, 0.8);
        text-align: left;
        height: auto;
        min-height: 400px;
    }

    .stats .stat-item.categories-column .stat-icon,
    .stats .stat-item.categories-column .stat-number,
    .stats .stat-item.categories-column .stat-label {
        display: none;
    }

    .stats .categories-list {
        list-style: none;
        padding: 0;
        margin: 0;
    }

    .stats .category-item {
        margin-bottom: 14px;
    }

    .stats .category-item:last-child {
        margin-bottom: 0;
    }

    .stats .category-link {
        display: flex;
        align-items: flex-start;
        gap: 12px;
        text-decoration: none;
        color: #334155;
        padding: 10px 6px;
        border-radius: 8px;
        transition: all 0.25s ease;
        position: relative;
    }

    .stats .category-link::before {
        content: '';
        position: absolute;
        left: 0;
        top: 0;
        bottom: 0;
        width: 0;
        background: linear-gradient(135deg, rgba(239, 68, 68, 0.08) 0%, rgba(200, 16, 46, 0.08) 100%);
        border-radius: 8px;
        transition: width 0.3s ease;
    }

    .stats .category-link:hover::before {
        width: 100%;
    }

    .stats .category-link:hover {
        color: #C8102E;
        transform: translateX(6px);
    }

    .stats .category-bullet {
        display: inline-block;
        width: 8px;
        height: 8px;
        background: #ef4444;
        border-radius: 50%;
        margin-top: 8px;
        flex-shrink: 0;
        transition: all 0.25s ease;
        position: relative;
        z-index: 1;
    }

    .stats .category-link:hover .category-bullet {
        width: 10px;
        height: 10px;
        background: #C8102E;
        box-shadow: 0 0 0 3px rgba(200, 16, 46, 0.2);
    }

    .stats .category-text {
        font-size: 0.9rem;
        font-weight: 500;
        line-height: 1.6;
        flex: 1;
        position: relative;
        z-index: 1;
        transition: all 0.25s ease;
    }

    .stats .category-link:hover .category-text {
        font-weight: 600;
        color: #A00D26;
    }

    /* Stats Grid Responsive */
    .stats-grid {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 24px;
    }

    /* Responsive */
    @media (max-width: 1024px) {
        .stats-grid {
            grid-template-columns: repeat(2, 1fr);
            gap: 20px;
        }

        .stats .categories-header {
            margin-bottom: 40px;
            padding: 0 1.5rem;
        }

        .stats .categories-title {
            font-size: 1.5rem;
        }

        .stats .stat-item.categories-column {
            padding: 28px 24px;
            min-height: 350px;
        }
    }

    @media (max-width: 768px) {
        .stats-grid {
            grid-template-columns: 1fr;
            gap: 16px;
        }

        .stats .categories-header {
            margin-bottom: 30px;
            padding: 0 1rem;
        }

        .stats .categories-title {
            font-size: 1.3rem;
            line-height: 1.5;
        }

        .stats .stat-item.categories-column {
            padding: 24px 20px;
            min-height: auto;
        }

        .stats .category-text {
            font-size: 0.85rem;
        }
    }

    @media (max-width: 480px) {
        .stats .categories-title {
            font-size: 1.1rem;
        }

        .stats .stat-item.categories-column {
            padding: 20px 16px;
        }

        .stats .category-text {
            font-size: 0.8rem;
        }
    }
    </style>

    <!-- Modern Search Section -->
    <section class="modern-search-section fullpage-section" data-section-label="Tìm kiếm">
        <div class="search-hero">
        <div class="container">
                <div class="search-hero-content">
                    <div class="search-icon-main">
                        <i class="fas fa-search"></i>
                </div>
                    <h2 class="search-hero-title"><?php echo t('search_products_title'); ?></h2>
                    <p class="search-hero-subtitle"><?php echo t('search_products_subtitle'); ?></p>
                    
                    <!-- Modern Search Box -->
                    <div class="modern-search-container">
                        <form action="<?php echo buildLangUrl('products'); ?>" method="GET" class="modern-search-form">
                            <div class="search-box-wrapper">
                                <div class="search-icon-wrapper">
                                    <i class="fas fa-search"></i>
            </div>
                        <input type="text" 
                               name="q" 
                                       id="modernSearchInput"
                                       placeholder="<?php echo t('search_placeholder'); ?>" 
                                       class="modern-search-input"
                               autocomplete="off">
                                <button type="submit" class="modern-search-btn">
                                    <span><?php echo t('btn_search'); ?></span>
                                    <i class="fas fa-arrow-right"></i>
                        </button>
                            </div>
                            <div id="modernSearchResults" class="modern-search-results" style="display: none;"></div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <!-- Quick Categories -->
        <div class="container">
            <div class="quick-categories">
                <h3 class="quick-title">
                    <i class="fas fa-layer-group"></i>
                    Danh mục
                </h3>
                <div class="categories-grid">
                    <a href="<?php echo buildLangUrl('materials'); ?>" class="category-quick-card">
                        <div class="category-icon">
                            <i class="fas fa-boxes"></i>
                        </div>
                        <div class="category-info">
                            <h4><?php echo t('materials_title'); ?></h4>
                            <p><?php echo t('materials_desc'); ?></p>
                        </div>
                        <div class="category-arrow">
                            <i class="fas fa-chevron-right"></i>
                        </div>
                    </a>

                    <a href="<?php echo buildLangUrl('equipment'); ?>" class="category-quick-card">
                        <div class="category-icon">
                            <i class="fas fa-tools"></i>
                        </div>
                        <div class="category-info">
                            <h4><?php echo t('equipment_title'); ?></h4>
                            <p><?php echo t('equipment_desc'); ?></p>
                        </div>
                        <div class="category-arrow">
                            <i class="fas fa-chevron-right"></i>
                        </div>
                    </a>

                    <a href="<?php echo buildLangUrl('landscape'); ?>" class="category-quick-card">
                        <div class="category-icon">
                            <i class="fas fa-seedling"></i>
                        </div>
                        <div class="category-info">
                            <h4><?php echo t('landscape_title'); ?></h4>
                            <p><?php echo t('landscape_desc'); ?></p>
                        </div>
                        <div class="category-arrow">
                            <i class="fas fa-chevron-right"></i>
                        </div>
                    </a>

                    <a href="<?php echo buildLangUrl('technology'); ?>" class="category-quick-card">
                        <div class="category-icon">
                            <i class="fas fa-microchip"></i>
                        </div>
                        <div class="category-info">
                            <h4><?php echo t('technology_title'); ?></h4>
                            <p><?php echo t('technology_desc'); ?></p>
                        </div>
                        <div class="category-arrow">
                            <i class="fas fa-chevron-right"></i>
                        </div>
                    </a>

                    <a href="<?php echo buildLangUrl('suppliers'); ?>" class="category-quick-card">
                        <div class="category-icon">
                            <i class="fas fa-building"></i>
                        </div>
                        <div class="category-info">
                            <h4><?php echo t('suppliers_title'); ?></h4>
                            <p><?php echo number_format($totalSuppliers); ?> <?php echo t('suppliers_count'); ?></p>
                        </div>
                        <div class="category-arrow">
                            <i class="fas fa-chevron-right"></i>
                        </div>
                    </a>
                </div>
            </div>
        </div>
    </section>
    
    <style>
    /* Modern Search Section */
    .modern-search-section {
        padding: 0;
        background: #fef2f2;
    }

    .search-hero {
        background: linear-gradient(135deg, #fee2e2 0%, #fecaca 50%, #fef2f2 100%);
        padding: 80px 0;
        position: relative;
        overflow: hidden;
    }

    .search-hero::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><defs><pattern id="grid" width="10" height="10" patternUnits="userSpaceOnUse"><path d="M 10 0 L 0 0 0 10" fill="none" stroke="rgba(37,99,235,0.15)" stroke-width="0.5"/></pattern></defs><rect width="100" height="100" fill="url(%23grid)"/></svg>');
        opacity: 0.5;
    }

    .search-hero-content {
        text-align: center;
        position: relative;
        z-index: 2;
    }

    .search-icon-main {
        width: 80px;
        height: 80px;
        margin: 0 auto 24px;
        background: linear-gradient(135deg, #C8102E 0%, #DA1A32 100%);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        box-shadow: 0 10px 30px rgba(200, 16, 46, 0.3), 0 10px 30px rgba(218, 26, 50, 0.2);
        animation: floatIcon 3s ease-in-out infinite;
    }

    .search-icon-main i {
        font-size: 32px;
        color: white;
    }

    @keyframes floatIcon {
        0%, 100% { transform: translateY(0px); }
        50% { transform: translateY(-10px); }
    }

    .search-hero-title {
        font-size: 3rem;
        font-weight: 800;
        color: #A00D26;
        margin-bottom: 16px;
        text-shadow: 0 2px 4px rgba(160, 13, 38, 0.1);
    }

    .search-hero-subtitle {
        font-size: 1.2rem;
        color: #C8102E;
        margin-bottom: 40px;
        font-weight: 400;
    }

    /* Modern Search Box */
    .modern-search-container {
        max-width: 800px;
        margin: 0 auto;
        position: relative;
    }
    
    .modern-search-form {
        position: relative;
    }
    
    .search-box-wrapper {
        display: flex;
        align-items: center;
        background: white;
        border-radius: 60px;
        padding: 8px;
        box-shadow: 0 20px 60px rgba(200, 16, 46, 0.2), 0 10px 30px rgba(218, 26, 50, 0.1);
        border: 3px solid white;
        transition: all 0.3s ease;
    }
    
    .search-box-wrapper:focus-within {
        box-shadow: 0 25px 80px rgba(200, 16, 46, 0.35), 0 15px 40px rgba(218, 26, 50, 0.2);
        transform: translateY(-2px);
    }

    .search-icon-wrapper {
        width: 50px;
        height: 50px;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #C8102E;
        font-size: 20px;
    }

    .modern-search-input {
        flex: 1;
        border: none;
        outline: none;
        font-size: 18px;
        padding: 12px 20px;
        background: transparent;
        color: #1e293b;
    }

    .modern-search-input::placeholder {
        color: #94a3b8;
    }

    .modern-search-btn {
        background: linear-gradient(135deg, #C8102E 0%, #DA1A32 100%);
        color: white;
        border: none;
        padding: 16px 36px;
        border-radius: 50px;
        font-size: 16px;
        font-weight: 600;
        cursor: pointer;
        display: flex;
        align-items: center;
        gap: 12px;
        transition: all 0.3s ease;
        box-shadow: 0 4px 15px rgba(200, 16, 46, 0.3), 0 4px 15px rgba(218, 26, 50, 0.2);
    }

    .modern-search-btn:hover {
        background: linear-gradient(135deg, #A00D26 0%, #B91C1C 100%);
        box-shadow: 0 6px 25px rgba(200, 16, 46, 0.4), 0 6px 25px rgba(218, 26, 50, 0.3);
        transform: translateX(5px);
    }

    .modern-search-btn i {
        transition: transform 0.3s ease;
    }

    .modern-search-btn:hover i {
        transform: translateX(5px);
    }

    /* Quick Categories */
    .quick-categories {
        padding: 60px 0;
    }

    .quick-title {
        font-size: 1.8rem;
        font-weight: 700;
        color: #1e293b;
        text-align: center;
        margin-bottom: 40px;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 12px;
    }

    .quick-title i {
        color: #C8102E;
        font-size: 1.6rem;
    }

    .categories-grid {
        display: grid;
        grid-template-columns: repeat(5, 1fr);
        gap: 24px;
        margin-bottom: 40px;
    }

    .category-quick-card {
        background: white;
        border-radius: 20px;
        padding: 32px 24px;
        text-decoration: none;
        transition: all 0.3s ease;
        border: 2px solid #fee2e2;
        display: flex;
        flex-direction: column;
        align-items: center;
        text-align: center;
        position: relative;
        overflow: hidden;
    }

    .category-quick-card::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 4px;
        background: linear-gradient(135deg, #C8102E 0%, #DA1A32 100%);
        transform: scaleX(0);
        transition: transform 0.3s ease;
    }

    .category-quick-card:hover::before {
        transform: scaleX(1);
    }

    .category-quick-card:hover {
        transform: translateY(-8px);
        box-shadow: 0 20px 60px rgba(200, 16, 46, 0.2), 0 10px 30px rgba(218, 26, 50, 0.1);
        border-color: #C8102E;
    }

    .category-icon {
        width: 70px;
        height: 70px;
        background: linear-gradient(135deg, #fee2e2 0%, #fecaca 100%);
        border-radius: 20px;
        display: flex;
        align-items: center;
        justify-content: center;
        margin-bottom: 20px;
        transition: all 0.3s ease;
    }

    .category-icon i {
        font-size: 32px;
        color: #C8102E;
        transition: all 0.3s ease;
    }

    .category-quick-card:hover .category-icon {
        background: linear-gradient(135deg, #C8102E 0%, #DA1A32 100%);
        transform: scale(1.1) rotate(-5deg);
    }

    .category-quick-card:hover .category-icon i {
        color: white;
    }

    .category-info h4 {
        font-size: 1.3rem;
        font-weight: 700;
        color: #1e293b;
        margin-bottom: 8px;
        transition: color 0.3s ease;
    }

    .category-info p {
        font-size: 0.95rem;
        color: #64748b;
        margin: 0;
    }

    .category-arrow {
        margin-top: 16px;
        color: #C8102E;
        font-size: 18px;
        opacity: 0;
        transform: translateX(-10px);
        transition: all 0.3s ease;
    }

    .category-quick-card:hover .category-arrow {
        opacity: 1;
        transform: translateX(0);
    }

    .category-quick-card:hover .category-info h4 {
        color: #C8102E;
    }

    /* Quick Links - Removed, merged into categories-grid */

    /* Search Results */
    .modern-search-results {
        position: absolute;
        top: calc(100% + 10px);
        left: 0;
        right: 0;
        background: white;
        border-radius: 20px;
        box-shadow: 0 20px 60px rgba(0,0,0,0.1);
        z-index: 1000;
        max-height: 400px;
        overflow-y: auto;
        border: 2px solid #fee2e2;
    }

    /* Responsive */
    @media (max-width: 1300px) {
        .categories-grid {
            grid-template-columns: repeat(4, 1fr);
        }
    }

    @media (max-width: 1024px) {
        .categories-grid {
            grid-template-columns: repeat(3, 1fr);
            gap: 20px;
        }

        .search-hero {
            padding: 60px 0;
        }
    }

    @media (max-width: 768px) {
        .search-hero {
            padding: 50px 0;
        }

        .search-icon-main {
            width: 60px;
            height: 60px;
            margin-bottom: 20px;
        }

        .search-icon-main i {
            font-size: 24px;
        }

        .search-hero-title {
            font-size: 2rem;
            margin-bottom: 12px;
        }

        .search-hero-subtitle {
            font-size: 1rem;
            margin-bottom: 30px;
        }

        .modern-search-container {
            padding: 0 1rem;
        }

        .categories-grid {
            grid-template-columns: repeat(2, 1fr);
            gap: 16px;
        }

        .modern-search-btn {
            padding: 14px 24px;
            font-size: 14px;
        }

        .modern-search-btn span {
            display: none;
        }

        .search-box-wrapper {
            padding: 6px;
        }

        .search-icon-wrapper {
            width: 40px;
            height: 40px;
            font-size: 16px;
        }

        .modern-search-input {
            font-size: 16px;
            padding: 10px 16px;
        }

        .quick-categories {
            padding: 40px 0;
        }

        .quick-title {
            font-size: 1.5rem;
            margin-bottom: 30px;
        }

        .category-quick-card {
            padding: 24px 20px;
        }

        .category-icon {
            width: 60px;
            height: 60px;
            margin-bottom: 16px;
        }

        .category-icon i {
            font-size: 28px;
        }

        .category-info h4 {
            font-size: 1.1rem;
        }

        .category-info p {
            font-size: 0.85rem;
        }
    }

    @media (max-width: 480px) {
        .search-hero {
            padding: 40px 0;
        }

        .search-icon-main {
            width: 50px;
            height: 50px;
            margin-bottom: 16px;
        }

        .search-icon-main i {
            font-size: 20px;
        }

        .search-hero-title {
            font-size: 1.6rem;
        }

        .search-hero-subtitle {
            font-size: 0.9rem;
            margin-bottom: 24px;
        }

        .modern-search-container {
            padding: 0 0.5rem;
        }

        .categories-grid {
            grid-template-columns: 1fr;
            gap: 12px;
        }

        .modern-search-btn {
            padding: 12px 20px;
            font-size: 12px;
        }

        .search-icon-wrapper {
            width: 36px;
            height: 36px;
            font-size: 14px;
        }

        .modern-search-input {
            font-size: 14px;
            padding: 8px 12px;
        }

        .quick-categories {
            padding: 30px 0;
        }

        .quick-title {
            font-size: 1.3rem;
            margin-bottom: 24px;
        }

        .category-quick-card {
            padding: 20px 16px;
        }

        .category-icon {
            width: 50px;
            height: 50px;
            margin-bottom: 12px;
        }

        .category-icon i {
            font-size: 24px;
        }

        .category-info h4 {
            font-size: 1rem;
        }

        .category-info p {
            font-size: 0.8rem;
        }
    }
    </style>
    
    <script>
    // Helper function to create product URL
    function buildProductUrl(product) {
        const baseUrl = '<?php echo rtrim(BASE_URL, '/'); ?>';
        const lang = '<?php echo $current_lang ?? 'vi'; ?>';
        const prefix = (lang === 'vi') ? 'san-pham' : 'product';
        
        // Create slug from name
        let name = (lang === 'en' && product.name_en) ? product.name_en : product.name;
        let slug = createSlug(name);
        
        return `${baseUrl}/${prefix}/${slug}-${product.id}`;
    }
    
    // Helper function to create slug
    function createSlug(str) {
        const vietnamese = {
            'à': 'a', 'á': 'a', 'ạ': 'a', 'ả': 'a', 'ã': 'a',
            'â': 'a', 'ầ': 'a', 'ấ': 'a', 'ậ': 'a', 'ẩ': 'a', 'ẫ': 'a',
            'ă': 'a', 'ằ': 'a', 'ắ': 'a', 'ặ': 'a', 'ẳ': 'a', 'ẵ': 'a',
            'è': 'e', 'é': 'e', 'ẹ': 'e', 'ẻ': 'e', 'ẽ': 'e',
            'ê': 'e', 'ề': 'e', 'ế': 'e', 'ệ': 'e', 'ể': 'e', 'ễ': 'e',
            'ì': 'i', 'í': 'i', 'ị': 'i', 'ỉ': 'i', 'ĩ': 'i',
            'ò': 'o', 'ó': 'o', 'ọ': 'o', 'ỏ': 'o', 'õ': 'o',
            'ô': 'o', 'ồ': 'o', 'ố': 'o', 'ộ': 'o', 'ổ': 'o', 'ỗ': 'o',
            'ơ': 'o', 'ờ': 'o', 'ớ': 'o', 'ợ': 'o', 'ở': 'o', 'ỡ': 'o',
            'ù': 'u', 'ú': 'u', 'ụ': 'u', 'ủ': 'u', 'ũ': 'u',
            'ư': 'u', 'ừ': 'u', 'ứ': 'u', 'ự': 'u', 'ử': 'u', 'ữ': 'u',
            'ỳ': 'y', 'ý': 'y', 'ỵ': 'y', 'ỷ': 'y', 'ỹ': 'y',
            'đ': 'd'
        };
        
        str = str.toLowerCase();
        
        // Replace Vietnamese characters
        for (let char in vietnamese) {
            str = str.replace(new RegExp(char, 'g'), vietnamese[char]);
        }
        
        // Remove special characters and replace spaces with hyphens
        str = str.replace(/[^a-z0-9\s-]/g, '')
                 .replace(/\s+/g, '-')
                 .replace(/-+/g, '-')
                 .replace(/^-+|-+$/g, '');
        
        return str;
    }
    
    document.addEventListener('DOMContentLoaded', function() {
        const searchInput = document.getElementById('modernSearchInput');
        const searchResults = document.getElementById('modernSearchResults');
        
        if (searchInput && searchResults) {
            let searchTimeout;
            
            searchInput.addEventListener('input', function() {
                const query = this.value.trim();
                
                clearTimeout(searchTimeout);
                
                if (query.length < 2) {
                    searchResults.style.display = 'none';
                    return;
                }
                
                searchTimeout = setTimeout(() => {
                    fetch(`search_ajax.php?q=${encodeURIComponent(query)}`)
                        .then(response => response.json())
                        .then(data => {
                            displayResults(data);
                        })
                        .catch(error => {
                            console.error('Search error:', error);
                        });
                }, 300);
            });
            
            function displayResults(results) {
                if (results.length === 0) {
                    searchResults.style.display = 'none';
                    return;
                }
                
                let html = '<div style="padding: 12px 20px; background: #fef2f2; border-bottom: 2px solid #fee2e2;"><strong style="color: #A00D26;"><?php echo t('search_results'); ?></strong></div>';
                results.slice(0, 5).forEach(item => {
                    const productUrl = buildProductUrl(item);
                    html += `
                        <div style="padding: 16px 20px; border-bottom: 1px solid #f1f5f9; cursor: pointer; transition: background 0.2s; display: flex; align-items: center; gap: 12px;" 
                             onmouseover="this.style.background='#f8fafc'" 
                             onmouseout="this.style.background='white'"
                             onclick="window.location.href='${productUrl}'">
                            <i class="fas fa-box" style="color: #C8102E; font-size: 18px;"></i>
                            <div style="flex: 1;">
                                <div style="font-weight: 600; color: #1e293b; margin-bottom: 4px;">${item.name}</div>
                                <div style="font-size: 12px; color: #64748b;">${item.category || 'Sản phẩm'}</div>
                            </div>
                            <i class="fas fa-arrow-right" style="color: #ef4444; font-size: 14px;"></i>
                        </div>
                    `;
                });
                html += `<div style="padding: 12px 20px; text-align: center; background: #f8fafc;"><a href="<?php echo buildLangUrl('products'); ?>?q=${encodeURIComponent(searchInput.value)}" style="color: #C8102E; text-decoration: none; font-weight: 600;"><?php echo t('view_all_results'); ?> <i class="fas fa-arrow-right"></i></a></div>`;
                
                searchResults.innerHTML = html;
                searchResults.style.display = 'block';
            }
            
            // Hide results when clicking outside
            document.addEventListener('click', function(e) {
                if (!e.target.closest('.modern-search-container')) {
                    searchResults.style.display = 'none';
                }
            });

            // Hide results when pressing Escape
            document.addEventListener('keydown', function(e) {
                if (e.key === 'Escape') {
                    searchResults.style.display = 'none';
                }
            });
        }
    });
    </script>

    <!-- News/Blog Section -->
    <section class="fullpage-section" data-section-label="Tin tức">
        <?php include 'inc/news-section.php'; ?>
    </section>

    <!-- Partners Section -->
    <?php
    // Lấy danh sách đối tác từ database
    try {
        $stmtPartners = $pdo->prepare("SELECT * FROM partners WHERE status = 1 ORDER BY display_order ASC, id ASC");
        $stmtPartners->execute();
        $partners = $stmtPartners->fetchAll(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
        $partners = [];
        error_log("Lỗi khi truy xuất đối tác: " . $e->getMessage());
    }
    ?>
    
    <?php if (!empty($partners)): ?>
    <section class="partners fullpage-section" data-section-label="Đối tác">
        <div class="container">
            <div class="section-header">
                <h2 class="section-title"><?php echo t('partners_title'); ?></h2>
            </div>
            <div class="partners-grid">
                <?php foreach ($partners as $partner): ?>
                <div class="partner-item">
                    <img src="<?php echo htmlspecialchars($partner['image_path']); ?>" 
                         alt="<?php echo htmlspecialchars($partner['name']); ?>"
                         title="<?php echo htmlspecialchars($partner['name']); ?>">
                </div>
                <?php endforeach; ?>
            </div>
            <?php if (count($partners) > 7): ?>
            <div class="partners-footer">
                <a href="<?php echo buildLangUrl('suppliers'); ?>" class="btn btn-outline"><?php echo t('view_more_partners'); ?></a>
            </div>
            <?php endif; ?>
        </div>
    </section>
    <?php endif; ?>

    <!-- About & Mission Section -->
    <section id="about" class="about fullpage-section" data-section-label="Giới thiệu">
        <div class="container">
            <div class="section-header">
                <h2 class="section-title"><?php echo t('about_title'); ?></h2>
            </div>
            <div class="mission-grid">
                <div class="mission-card">
                    <div class="mission-icon">
                        <i class="fas fa-users"></i>
                    </div>
                    <div class="mission-number">01.</div>
                    <h3 class="mission-title">Kết nối cộng đồng</h3>
                    <p class="mission-description">
                        Cùng đối tác và khách hàng xây dựng nền tảng kết nối toàn diện giữa nhà sản xuất, nhà phân phối, nhà thầu, kiến trúc sư, kỹ sư xây dựng và khách hàng trong ngành vật liệu, thiết bị và công nghệ xây dựng. Tạo môi trường hợp tác bền vững, chia sẻ thông tin và kinh nghiệm để thúc đẩy sự phát triển chung của ngành xây dựng Việt Nam.
                    </p>
                </div>
                <div class="mission-card">
                    <div class="mission-icon">
                        <i class="fas fa-bullseye"></i>
                    </div>
                    <div class="mission-number">02.</div>
                    <h3 class="mission-title">Tầm Nhìn và Mục Tiêu</h3>
                    <p class="mission-description">
                    Trở thành nền tảng số hàng đầu về giải pháp vật liệu, thiết bị và công nghệ trong xây dựng tại Việt Nam. Mục tiêu của chúng tôi là cung cấp thông tin chính xác, tin cậy và giải pháp đồng bộ, giúp các doanh nghiệp và cá nhân đưa ra quyết định tốt nhất trong lựa chọn vật liệu, thiết bị và công nghệ xây dựng.
                    </p>
                </div>
                <div class="mission-card">
                    <div class="mission-icon">
                        <i class="fas fa-leaf"></i>
                    </div>
                    <div class="mission-number">03.</div>
                    <h3 class="mission-title">Xanh và Bền Vững</h3>
                    <p class="mission-description">
                    Cùng nhau thúc đẩy việc sử dụng vật liệu xanh, công nghệ xây dựng bền vững và thân thiện với môi trường sống. Khuyến khích các giải pháp tiết kiệm năng lượng, tái chế và giảm thiểu tác động đến môi trường, góp phần xây dựng tương lai bền vững cho ngành xây dựng Việt Nam.
                    </p>
                </div>
            </div>
        </div>
        <style>
        /* Mission Grid - 3 columns layout */
        .mission-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 2.5rem;
            margin-top: 3rem;
        }

        /* Mission Icon - Font Awesome support */
        .mission-icon i {
            font-size: 2.5rem;
            color: white;
            display: block;
        }

        /* Mission Description - Căn đều text */
        .mission-description {
            text-align: justify;
            text-justify: inter-word;
            hyphens: auto;
            -webkit-hyphens: auto;
            -moz-hyphens: auto;
            -ms-hyphens: auto;
        }

        /* Responsive for mission grid */
        @media (max-width: 1024px) {
            .mission-grid {
                grid-template-columns: repeat(2, 1fr);
                gap: 2rem;
            }
        }

        @media (max-width: 768px) {
            .mission-grid {
                grid-template-columns: 1fr;
                gap: 1.5rem;
            }

            .mission-card {
                padding: 1.5rem;
            }

            .mission-icon {
                width: 60px;
                height: 60px;
            }

            .mission-icon i {
                font-size: 2rem;
            }

            .mission-title {
                font-size: 1.2rem;
            }

            .mission-description {
                font-size: 0.9rem;
            }
        }

        @media (max-width: 480px) {
            .mission-card {
                padding: 1.2rem;
            }

            .mission-icon {
                width: 50px;
                height: 50px;
            }

            .mission-icon i {
                font-size: 1.8rem;
            }

            .mission-title {
                font-size: 1.1rem;
            }

            .mission-description {
                font-size: 0.85rem;
            }
        }
        </style>
    </section>

</div>
<!-- End Full Page Scroll Container -->

<!-- Full Page Scroll JavaScript -->
<script src="assets/js/fullpage-scroll.js?v=<?php echo time(); ?>"></script>

<?php include 'inc/footer-new.php'; ?>