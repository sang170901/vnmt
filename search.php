<?php
header('Content-Type: text/html; charset=UTF-8');
require_once 'config.php';
include 'inc/header-new.php';
require_once 'inc/db_frontend.php';
require_once 'inc/supplier_helpers.php';
require_once 'inc/url_helpers.php';

$query = isset($_GET['q']) ? trim($_GET['q']) : '';
$pdo = getFrontendPDO();

$products = [];
$suppliers = [];
$posts = [];
$totalResults = 0;

if (!empty($query)) {
    // Tìm kiếm sản phẩm
    $stmt = $pdo->prepare("
        SELECT id, name, category, classification, brand, featured_image 
        FROM products 
        WHERE status = 1 
        AND (name LIKE :query OR description LIKE :query OR brand LIKE :query OR classification LIKE :query)
        ORDER BY created_at DESC 
        LIMIT 20
    ");
    $stmt->execute([':query' => "%$query%"]);
    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Tìm kiếm nhà cung cấp
    $stmt = $pdo->prepare("
        SELECT id, name, email, phone, address, logo, description, website 
        FROM suppliers 
        WHERE status = 1 
        AND (name LIKE :query OR email LIKE :query OR description LIKE :query OR address LIKE :query)
        LIMIT 10
    ");
    $stmt->execute([':query' => "%$query%"]);
    $suppliers = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Tìm kiếm bài viết
    $stmt = $pdo->prepare("
        SELECT id, title, slug, excerpt, featured_image, published_at 
        FROM posts 
        WHERE status = 'published' 
        AND (title LIKE :query OR content LIKE :query OR excerpt LIKE :query)
        ORDER BY published_at DESC 
        LIMIT 10
    ");
    $stmt->execute([':query' => "%$query%"]);
    $posts = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $totalResults = count($products) + count($suppliers) + count($posts);
}
?>

<style>
.search-page {
    max-width: 1200px;
    margin: 40px auto;
    padding: 0 20px;
}

.search-header {
    text-align: center;
    margin-bottom: 40px;
}

.search-header h1 {
    font-size: 2rem;
    color: #1e293b;
    margin-bottom: 10px;
}

.search-stats {
    color: #64748b;
    font-size: 1.1rem;
}

.search-query {
    color: #0ea5e9;
    font-weight: 600;
}

.search-section {
    margin-bottom: 40px;
}

.section-title {
    font-size: 1.5rem;
    font-weight: 700;
    color: #1e293b;
    margin-bottom: 20px;
    padding-bottom: 10px;
    border-bottom: 3px solid #0ea5e9;
}

.results-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
    gap: 20px;
    align-items: start;
}

.result-card {
    background: white;
    border-radius: 12px;
    padding: 20px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.08);
    transition: all 0.3s;
    text-decoration: none;
    color: inherit;
    display: block;
}

.result-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 8px 24px rgba(14, 165, 233, 0.2);
}

.result-image {
    width: 100%;
    height: 200px !important;
    min-height: 200px;
    max-height: 200px;
    object-fit: cover;
    border-radius: 8px;
    margin-bottom: 12px;
    background: linear-gradient(135deg, #f0f9ff, #e0f2fe);
    display: block;
}

.result-category {
    display: inline-block;
    padding: 4px 12px;
    background: #e0f2fe;
    color: #0284c7;
    border-radius: 20px;
    font-size: 0.75rem;
    font-weight: 600;
    margin-bottom: 8px;
}

.result-title {
    font-size: 1rem;
    font-weight: 600;
    color: #1e293b;
    margin-bottom: 8px;
    min-height: 2.5rem;
    max-height: 2.5rem;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
    line-height: 1.25;
}

.result-desc {
    font-size: 0.875rem;
    color: #64748b;
    margin-bottom: 12px;
    min-height: 3rem;
    max-height: 3rem;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
    line-height: 1.5;
}

.result-meta {
    display: flex;
    gap: 12px;
    font-size: 0.8rem;
    color: #94a3b8;
    min-height: 1.5rem;
}

.no-results {
    text-align: center;
    padding: 80px 20px;
}

.no-results i {
    font-size: 4rem;
    color: #cbd5e1;
    margin-bottom: 20px;
}

.no-results h3 {
    font-size: 1.5rem;
    color: #475569;
    margin-bottom: 10px;
}

.no-results p {
    color: #64748b;
}

@media (max-width: 768px) {
    .results-grid {
        grid-template-columns: 1fr;
    }
}
</style>

<div class="search-page">
    <div class="search-header">
        <h1>
            <i class="fas fa-search" style="color: #0ea5e9;"></i>
            Kết quả tìm kiếm
        </h1>
        <?php if (!empty($query)): ?>
            <div class="search-stats">
                Tìm thấy <strong><?php echo $totalResults; ?></strong> kết quả cho 
                "<span class="search-query"><?php echo htmlspecialchars($query); ?></span>"
            </div>
        <?php endif; ?>
    </div>

    <?php if (empty($query)): ?>
        <div class="no-results">
            <i class="fas fa-search"></i>
            <h3>Nhập từ khóa để tìm kiếm</h3>
            <p>Hãy nhập tên sản phẩm, nhà cung cấp hoặc bài viết bạn muốn tìm</p>
        </div>
    <?php elseif ($totalResults == 0): ?>
        <div class="no-results">
            <i class="fas fa-inbox"></i>
            <h3>Không tìm thấy kết quả</h3>
            <p>Không có kết quả nào phù hợp với từ khóa "<strong><?php echo htmlspecialchars($query); ?></strong>"</p>
            <p>Hãy thử lại với từ khóa khác</p>
        </div>
    <?php else: ?>
        
        <!-- Sản phẩm -->
        <?php if (!empty($products)): ?>
        <div class="search-section">
            <h2 class="section-title">
                <i class="fas fa-box"></i> Sản phẩm (<?php echo count($products); ?>)
            </h2>
            <div class="results-grid">
                <?php foreach ($products as $product): ?>
                <a href="<?php echo buildProductUrl($product); ?>" class="result-card">
                    <?php if (!empty($product['featured_image'])): ?>
                        <img src="<?php echo htmlspecialchars($product['featured_image']); ?>" 
                             alt="<?php echo htmlspecialchars($product['name']); ?>" 
                             class="result-image">
                    <?php else: ?>
                        <div class="result-image" style="display: flex; align-items: center; justify-content: center;">
                            <i class="fas fa-box" style="font-size: 3rem; color: rgba(14, 165, 233, 0.3);"></i>
                        </div>
                    <?php endif; ?>
                    
                    <span class="result-category"><?php echo htmlspecialchars($product['category']); ?></span>
                    <h3 class="result-title"><?php echo htmlspecialchars($product['name']); ?></h3>
                    
                    <div class="result-meta">
                        <?php if ($product['brand']): ?>
                            <span><i class="fas fa-industry"></i> <?php echo htmlspecialchars($product['brand']); ?></span>
                        <?php endif; ?>
                        <?php if ($product['classification']): ?>
                            <span><i class="fas fa-tag"></i> <?php echo htmlspecialchars($product['classification']); ?></span>
                        <?php endif; ?>
                    </div>
                </a>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>
        
        <!-- Nhà cung cấp -->
        <?php if (!empty($suppliers)): ?>
        <div class="search-section">
            <h2 class="section-title">
                <i class="fas fa-truck"></i> Nhà cung cấp (<?php echo count($suppliers); ?>)
            </h2>
            <div class="results-grid">
                <?php foreach ($suppliers as $supplier): ?>
                <a href="<?php echo buildSupplierUrl($supplier); ?>" class="result-card">
                    <?php 
                    $logoPath = getSupplierLogoPath($supplier['logo'] ?? null);
                    if ($logoPath): 
                    ?>
                        <img src="<?php echo htmlspecialchars($logoPath); ?>" 
                             alt="<?php echo htmlspecialchars($supplier['name']); ?>" 
                             class="result-image"
                             onerror="this.onerror=null; this.style.display='none'; this.parentElement.innerHTML='<div class=\'result-image\' style=\'display: flex; align-items: center; justify-content: center;\'><i class=\'fas fa-building\' style=\'font-size: 3rem; color: rgba(14, 165, 233, 0.3);\'></i></div>';">
                    <?php else: ?>
                        <div class="result-image" style="display: flex; align-items: center; justify-content: center;">
                            <i class="fas fa-building" style="font-size: 3rem; color: rgba(14, 165, 233, 0.3);"></i>
                        </div>
                    <?php endif; ?>
                    
                    <span class="result-category">Nhà cung cấp</span>
                    <h3 class="result-title"><?php echo htmlspecialchars($supplier['name']); ?></h3>
                    
                    <?php if ($supplier['address']): ?>
                    <div class="result-desc">
                        <i class="fas fa-map-marker-alt"></i> <?php echo htmlspecialchars($supplier['address']); ?>
                    </div>
                    <?php endif; ?>
                    
                    <div class="result-meta">
                        <?php if ($supplier['phone']): ?>
                            <span><i class="fas fa-phone"></i> <?php echo htmlspecialchars($supplier['phone']); ?></span>
                        <?php endif; ?>
                        <?php if ($supplier['website']): ?>
                            <span><i class="fas fa-globe"></i> Website</span>
                        <?php endif; ?>
                    </div>
                </a>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>
        
        <!-- Bài viết -->
        <?php if (!empty($posts)): ?>
        <div class="search-section">
            <h2 class="section-title">
                <i class="fas fa-newspaper"></i> Bài viết (<?php echo count($posts); ?>)
            </h2>
            <div class="results-grid">
                <?php foreach ($posts as $post): ?>
                <a href="<?php echo buildArticleUrl($post); ?>" class="result-card">
                    <?php if (!empty($post['featured_image'])): ?>
                        <img src="<?php echo htmlspecialchars($post['featured_image']); ?>" 
                             alt="<?php echo htmlspecialchars($post['title']); ?>" 
                             class="result-image">
                    <?php else: ?>
                        <div class="result-image" style="display: flex; align-items: center; justify-content: center;">
                            <i class="fas fa-newspaper" style="font-size: 3rem; color: rgba(14, 165, 233, 0.3);"></i>
                        </div>
                    <?php endif; ?>
                    
                    <span class="result-category">Tin tức</span>
                    <h3 class="result-title"><?php echo htmlspecialchars($post['title']); ?></h3>
                    
                    <?php if ($post['excerpt']): ?>
                    <div class="result-desc"><?php echo htmlspecialchars(substr($post['excerpt'], 0, 100)); ?>...</div>
                    <?php endif; ?>
                    
                    <div class="result-meta">
                        <span><i class="far fa-calendar"></i> <?php echo date('d/m/Y', strtotime($post['published_at'])); ?></span>
                    </div>
                </a>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>
        
    <?php endif; ?>
</div>

<?php include 'inc/footer-new.php'; ?>

