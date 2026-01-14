<?php
header('Content-Type: text/html; charset=UTF-8');

require_once 'config.php';
require_once 'lang/lang.php';
require_once 'lang/db_translate_helper.php';
require_once 'inc/db_frontend.php';
require_once 'inc/supplier_helpers.php';
require_once 'inc/url_helpers.php';

// Get supplier ID from URL
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if (!$id) {
    header('Location: suppliers.php');
    exit;
}

try {
    $pdo = getFrontendPDO();
    
    // Get supplier details (include English columns)
    $stmt = $pdo->prepare("SELECT *, name_en, description_en FROM suppliers WHERE id = ? AND status = 1");
    $stmt->execute([$id]);
    $supplier = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$supplier) {
        header('Location: suppliers.php');
        exit;
    }
    
    // Get supplier's products (include English columns)
    $stmt = $pdo->prepare("
        SELECT id, name, name_en, price, featured_image, category, brand, description, description_en
        FROM products 
        WHERE supplier_id = ? AND status = 1 
        ORDER BY created_at DESC
        LIMIT 12
    ");
    $stmt->execute([$id]);
    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Get product count
    $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM products WHERE supplier_id = ? AND status = 1");
    $stmt->execute([$id]);
    $productCount = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
    
} catch (Exception $e) {
    error_log("Error loading supplier: " . $e->getMessage());
    header('Location: suppliers.php');
    exit;
}

include __DIR__ . '/inc/header-new.php';
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars(getTranslatedName($supplier)) ?> - VNMaterial</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Inter', sans-serif;
            background: #f8fafc;
            color: #1e293b;
            line-height: 1.6;
        }
        
        .page-container {
            max-width: 1400px;
            margin: 70px auto 3rem;
            padding: 0 30px;
        }
        
        /* Breadcrumb */
        .breadcrumb {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            margin-bottom: 1.5rem;
            font-size: 0.9rem;
            color: #64748b;
        }
        
        .breadcrumb a {
            color: #0284c7;
            text-decoration: none;
            transition: color 0.2s;
        }
        
        .breadcrumb a:hover {
            color: #38bdf8;
        }
        
        /* Supplier Header */
        .supplier-header {
            background: linear-gradient(135deg, #e0f2fe 0%, #bae6fd 100%);
            border-radius: 20px;
            padding: 3rem;
            margin-bottom: 2rem;
            box-shadow: 0 6px 20px rgba(0,0,0,0.06);
            position: relative;
            overflow: hidden;
        }
        
        .supplier-header::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><defs><pattern id="grid" width="10" height="10" patternUnits="userSpaceOnUse"><path d="M 10 0 L 0 0 0 10" fill="none" stroke="rgba(56,189,248,0.1)" stroke-width="0.5"/></pattern></defs><rect width="100" height="100" fill="url(%23grid)"/></svg>');
            opacity: 0.5;
        }
        
        .supplier-header-content {
            position: relative;
            z-index: 1;
            display: grid;
            grid-template-columns: auto 1fr;
            gap: 2rem;
            align-items: center;
        }
        
        .supplier-logo {
            width: 150px;
            height: 150px;
            background: white;
            border-radius: 20px;
            padding: 1rem;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .supplier-logo img {
            max-width: 100%;
            max-height: 100%;
            object-fit: contain;
        }
        
        .supplier-info {
            display: flex;
            flex-direction: column;
            gap: 1rem;
        }
        
        .supplier-name {
            font-size: 2.5rem;
            font-weight: 800;
            color: #0284c7;
            margin-bottom: 0.5rem;
        }
        
        .supplier-meta {
            display: flex;
            gap: 2rem;
            flex-wrap: wrap;
        }
        
        .supplier-meta-item {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            color: #475569;
            font-size: 0.95rem;
        }
        
        .supplier-meta-item i {
            color: #38bdf8;
            width: 20px;
        }
        
        .supplier-meta-item a {
            color: #0284c7;
            text-decoration: none;
        }
        
        .supplier-meta-item a:hover {
            color: #38bdf8;
        }
        
        /* Main Content */
        .supplier-content {
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 2rem;
            margin-bottom: 3rem;
        }
        
        .content-card {
            background: white;
            border-radius: 16px;
            padding: 2rem;
            box-shadow: 0 6px 20px rgba(0,0,0,0.06);
        }
        
        .card-title {
            font-size: 1.5rem;
            font-weight: 700;
            color: #1e293b;
            margin-bottom: 1.5rem;
            display: flex;
            align-items: center;
            gap: 0.75rem;
            padding-bottom: 1rem;
            border-bottom: 2px solid #e0f2fe;
        }
        
        .card-title i {
            color: #38bdf8;
        }
        
        .supplier-description {
            color: #475569;
            line-height: 1.8;
            margin-bottom: 1.5rem;
        }
        
        .contact-info {
            display: flex;
            flex-direction: column;
            gap: 1rem;
        }
        
        .contact-item {
            display: flex;
            align-items: flex-start;
            gap: 1rem;
            padding: 1rem;
            background: #f8fafc;
            border-radius: 10px;
            transition: all 0.3s;
        }
        
        .contact-item:hover {
            background: #e0f2fe;
        }
        
        .contact-item i {
            color: #38bdf8;
            width: 24px;
            font-size: 1.2rem;
            margin-top: 0.2rem;
        }
        
        .contact-item-content {
            flex: 1;
        }
        
        .contact-item-label {
            font-size: 0.85rem;
            color: #64748b;
            margin-bottom: 0.25rem;
        }
        
        .contact-item-value {
            color: #1e293b;
            font-weight: 600;
        }
        
        .contact-item-value a {
            color: #0284c7;
            text-decoration: none;
        }
        
        .contact-item-value a:hover {
            color: #38bdf8;
        }
        
        /* Products Section */
        .products-section {
            margin-bottom: 2rem;
        }
        
        .section-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
        }
        
        .section-title {
            font-size: 1.75rem;
            font-weight: 800;
            color: #1e293b;
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }
        
        .section-title i {
            color: #38bdf8;
        }
        
        .products-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 1.5rem;
        }
        
        .product-card {
            background: white;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 4px 12px rgba(0,0,0,0.06);
            transition: all 0.3s;
            text-decoration: none;
            color: inherit;
            display: flex;
            flex-direction: column;
            height: 440px;
        }
        
        .product-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 12px 30px rgba(0,0,0,0.15);
        }
        
        .product-image {
            width: 100%;
            height: 180px;
            min-height: 180px;
            max-height: 180px;
            object-fit: cover;
            background: linear-gradient(135deg, #f8fafc 0%, #f0f9ff 100%);
            flex-shrink: 0;
        }
        
        .product-body {
            padding: 1.1rem;
            flex: 1;
            display: flex;
            flex-direction: column;
        }
        
        .product-category {
            font-size: 0.7rem;
            color: #64748b;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 0.4rem;
            font-weight: 600;
        }
        
        .product-name {
            font-size: 0.95rem;
            font-weight: 700;
            color: #1e293b;
            margin-bottom: 0.6rem;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
            line-height: 1.4;
            min-height: 2.6rem;
            flex-shrink: 0;
        }
        
        .product-brand {
            font-size: 0.8rem;
            color: #0284c7;
            margin-bottom: 0.6rem;
            font-weight: 500;
            display: flex;
            align-items: center;
            gap: 0.3rem;
        }
        
        .product-description {
            font-size: 0.8rem;
            color: #64748b;
            margin-bottom: 0.6rem;
            line-height: 1.55;
            overflow: hidden;
            display: -webkit-box;
            -webkit-line-clamp: 3;
            -webkit-box-orient: vertical;
            flex: 1;
        }
        
        .product-price {
            display: none;
        }
        
        .product-footer {
            padding-top: 0.9rem;
            margin-top: auto;
            border-top: 1px solid #e2e8f0;
            text-align: center;
        }
        
        .view-detail-btn {
            display: inline-flex;
            align-items: center;
            gap: 0.4rem;
            padding: 0.6rem 1.2rem;
            background: linear-gradient(135deg, #38bdf8 0%, #22d3ee 100%);
            color: white;
            text-decoration: none;
            border-radius: 8px;
            font-size: 0.8rem;
            font-weight: 600;
            transition: all 0.3s;
            letter-spacing: 0.3px;
        }
        
        .view-detail-btn:hover {
            background: linear-gradient(135deg, #0ea5e9 0%, #06b6d4 100%);
            transform: translateY(-2px);
            box-shadow: 0 6px 16px rgba(34, 211, 238, 0.4);
        }
        
        .no-products {
            text-align: center;
            padding: 4rem 2rem;
            background: white;
            border-radius: 16px;
            box-shadow: 0 6px 20px rgba(0,0,0,0.06);
        }
        
        .no-products i {
            font-size: 4rem;
            color: #cbd5e1;
            margin-bottom: 1rem;
        }
        
        .no-products h3 {
            font-size: 1.5rem;
            color: #475569;
            margin-bottom: 0.5rem;
        }
        
        /* Responsive */
        @media (max-width: 1200px) {
            .products-grid {
                grid-template-columns: repeat(3, 1fr);
                gap: 1.5rem;
            }
        }
        
        @media (max-width: 900px) {
            .products-grid {
                grid-template-columns: repeat(2, 1fr);
                gap: 1.5rem;
            }
            
            .product-card {
                height: 460px;
            }
            
            .supplier-content {
                grid-template-columns: 1fr;
            }
            
            .supplier-header-content {
                grid-template-columns: 1fr;
                text-align: center;
            }
            
            .supplier-logo {
                margin: 0 auto;
            }
        }
        
        @media (max-width: 640px) {
            .page-container {
                margin-top: 50px;
                padding: 0 15px;
            }
            
            .supplier-header {
                padding: 2rem 1.5rem;
            }
            
            .supplier-name {
                font-size: 2rem;
            }
            
            .products-grid {
                grid-template-columns: 1fr;
                gap: 1.25rem;
            }
            
            .product-card {
                height: auto;
                min-height: 420px;
            }
            
            .product-image {
                height: 180px;
                min-height: 180px;
                max-height: 180px;
            }
        }
    </style>
</head>
<body>
    <div class="page-container">
        <!-- Breadcrumb -->
        <nav class="breadcrumb">
            <a href="index.php"><i class="fas fa-home"></i> Trang chủ</a>
            <span>/</span>
            <a href="suppliers.php">Nhà cung cấp</a>
            <span>/</span>
            <span><?php echo htmlspecialchars(getTranslatedName($supplier)) ?></span>
        </nav>

        <!-- Supplier Header -->
        <div class="supplier-header">
            <div class="supplier-header-content">
                <?php 
                $logoPath = getSupplierLogoPath($supplier['logo'] ?? null);
                if ($logoPath): 
                ?>
                <div class="supplier-logo">
                    <img src="<?php echo htmlspecialchars($logoPath) ?>" 
                         alt="<?php echo htmlspecialchars(getTranslatedName($supplier)) ?>"
                         onerror="this.onerror=null; this.style.display='none'; this.parentElement.innerHTML='<i class=\'fas fa-building\' style=\'font-size: 4rem; color: #38bdf8; opacity: 0.5;\'></i>';">
                </div>
                <?php else: ?>
                <div class="supplier-logo">
                    <i class="fas fa-building" style="font-size: 4rem; color: #38bdf8; opacity: 0.5;"></i>
                </div>
                <?php endif; ?>
                
                <div class="supplier-info">
                    <h1 class="supplier-name"><?php echo htmlspecialchars(getTranslatedName($supplier)) ?></h1>
                    
                    <div class="supplier-meta">
                        <?php if ($supplier['phone']): ?>
                        <div class="supplier-meta-item">
                            <i class="fas fa-phone"></i>
                            <a href="tel:<?php echo htmlspecialchars($supplier['phone']) ?>">
                                <?php echo htmlspecialchars($supplier['phone']) ?>
                            </a>
                        </div>
                        <?php endif; ?>
                        
                        <?php if ($supplier['email']): ?>
                        <div class="supplier-meta-item">
                            <i class="fas fa-envelope"></i>
                            <a href="mailto:<?php echo htmlspecialchars($supplier['email']) ?>">
                                <?php echo htmlspecialchars($supplier['email']) ?>
                            </a>
                        </div>
                        <?php endif; ?>
                        
                        <?php if ($supplier['website']): ?>
                        <div class="supplier-meta-item">
                            <i class="fas fa-globe"></i>
                            <a href="<?php echo htmlspecialchars($supplier['website']) ?>" target="_blank">
                                Website
                            </a>
                        </div>
                        <?php endif; ?>
                        
                        <div class="supplier-meta-item">
                            <i class="fas fa-box"></i>
                            <span><?php echo $productCount ?> sản phẩm</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Main Content -->
        <div class="supplier-content">
            <!-- Description -->
            <div class="content-card">
                <h2 class="card-title">
                    <i class="fas fa-info-circle"></i>
                    Giới thiệu
                </h2>
                <div class="supplier-description">
                    <?php if (getTranslatedDescription($supplier)): ?>
                        <?php echo nl2br(htmlspecialchars(getTranslatedDescription($supplier))) ?>
                    <?php else: ?>
                        <p><?php echo t('supplier_no_info'); ?></p>
                    <?php endif; ?>
                </div>
            </div>
            
            <!-- Contact Info -->
            <div class="content-card">
                <h2 class="card-title">
                    <i class="fas fa-address-card"></i>
                    Thông tin liên hệ
                </h2>
                
                <div class="contact-info">
                    <?php if ($supplier['phone']): ?>
                    <div class="contact-item">
                        <i class="fas fa-phone"></i>
                        <div class="contact-item-content">
                            <div class="contact-item-label">Điện thoại</div>
                            <div class="contact-item-value">
                                <a href="tel:<?php echo htmlspecialchars($supplier['phone']) ?>">
                                    <?php echo htmlspecialchars($supplier['phone']) ?>
                                </a>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>
                    
                    <?php if ($supplier['email']): ?>
                    <div class="contact-item">
                        <i class="fas fa-envelope"></i>
                        <div class="contact-item-content">
                            <div class="contact-item-label">Email</div>
                            <div class="contact-item-value">
                                <a href="mailto:<?php echo htmlspecialchars($supplier['email']) ?>">
                                    <?php echo htmlspecialchars($supplier['email']) ?>
                                </a>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>
                    
                    <?php if ($supplier['address']): ?>
                    <div class="contact-item">
                        <i class="fas fa-map-marker-alt"></i>
                        <div class="contact-item-content">
                            <div class="contact-item-label">Địa chỉ</div>
                            <div class="contact-item-value">
                                <?php echo htmlspecialchars($supplier['address']) ?>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>
                    
                    <?php if ($supplier['website']): ?>
                    <div class="contact-item">
                        <i class="fas fa-globe"></i>
                        <div class="contact-item-content">
                            <div class="contact-item-label">Website</div>
                            <div class="contact-item-value">
                                <a href="<?php echo htmlspecialchars($supplier['website']) ?>" target="_blank">
                                    <?php echo htmlspecialchars($supplier['website']) ?>
                                </a>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Products Section -->
        <section class="products-section">
            <div class="section-header">
                <h2 class="section-title">
                    <i class="fas fa-boxes"></i>
                    <?php echo t('products_from'); ?> <?php echo htmlspecialchars(getTranslatedName($supplier)) ?>
                </h2>
            </div>
            
            <?php if (!empty($products)): ?>
            <div class="products-grid">
                <?php foreach ($products as $product): ?>
                <a href="<?php echo buildProductUrl($product); ?>" class="product-card">
                    <?php if ($product['featured_image']): ?>
                        <img src="<?php echo htmlspecialchars($product['featured_image']) ?>" 
                             alt="<?php echo htmlspecialchars(getTranslatedName($product)) ?>" 
                             class="product-image">
                    <?php else: ?>
                        <div class="product-image" style="display: flex; align-items: center; justify-content: center;">
                            <i class="fas fa-box-open" style="font-size: 2.5rem; color: #cbd5e1; opacity: 0.5;"></i>
                        </div>
                    <?php endif; ?>
                    
                    <div class="product-body">
                        <?php if ($product['category']): ?>
                            <div class="product-category"><?php echo htmlspecialchars(ucfirst($product['category'])) ?></div>
                        <?php endif; ?>
                        
                        <h3 class="product-name"><?php echo htmlspecialchars(getTranslatedName($product)) ?></h3>
                        
                        <?php if ($product['brand']): ?>
                            <div class="product-brand">
                                <i class="fas fa-copyright"></i> <?php echo htmlspecialchars($product['brand']) ?>
                            </div>
                        <?php endif; ?>
                        
                        <?php if ($product['description']): ?>
                            <div class="product-description">
                                <?php 
                                $desc = strip_tags($product['description']);
                                echo htmlspecialchars(mb_substr($desc, 0, 100)) . (mb_strlen($desc) > 100 ? '...' : '');
                                ?>
                            </div>
                        <?php endif; ?>
                        
                        <?php if ($product['price']): ?>
                            <div class="product-price">
                                <?php echo number_format($product['price']) ?>đ
                            </div>
                        <?php endif; ?>
                        
                        <div class="product-footer">
                            <span class="view-detail-btn">
                                Xem chi tiết
                                <i class="fas fa-arrow-right"></i>
                            </span>
                        </div>
                    </div>
                </a>
                <?php endforeach; ?>
            </div>
            <?php else: ?>
            <div class="no-products">
                <i class="fas fa-box-open"></i>
                <h3>Chưa có sản phẩm</h3>
                <p>Nhà cung cấp này chưa có sản phẩm nào.</p>
            </div>
            <?php endif; ?>
        </section>
    </div>
</body>
</html>

<?php include __DIR__ . '/inc/footer-new.php'; ?>
