<?php 
// Set proper encoding for Vietnamese
header('Content-Type: text/html; charset=UTF-8');
require_once 'config.php';
include 'inc/header-new.php'; 
?>

<?php
require_once 'inc/db_frontend.php';
require_once 'inc/url_helpers.php';
require_once 'backend/inc/helpers.php';

// Pagination and filter parameters
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 40;
$offset = ($page - 1) * $limit;
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$categoryId = isset($_GET['category_id']) ? (int)$_GET['category_id'] : 0; // Lọc theo category_id của danh mục con

try {
    $pdo = getFrontendPDO();
    
    // Build query with filters - sử dụng cả category text VÀ category_id để lấy đủ 54 sản phẩm
    if ($categoryId > 0) {
        // Lọc theo danh mục con cụ thể
        $whereClause = "WHERE p.category_id = :category_id AND p.status = 1";
        $params = [':category_id' => $categoryId];
    } else {
        // Lấy TẤT CẢ sản phẩm vật liệu: cả category text VÀ category_id
        $whereClause = "WHERE ((p.category = 'Vật liệu' OR p.category = 'vật liệu') 
                        OR p.category_id IN (SELECT id FROM categories WHERE id = 1 OR parent_id = 1)) 
                        AND p.status = 1";
        $params = [];
    }
    
    // Lấy tất cả sản phẩm trước (để lọc bằng PHP)
    $allProductsQuery = "SELECT p.* FROM products p $whereClause ORDER BY p.created_at DESC";
    $allProductsStmt = $pdo->prepare($allProductsQuery);
    foreach ($params as $key => $value) {
        $allProductsStmt->bindValue($key, $value);
    }
    $allProductsStmt->execute();
    $allProducts = $allProductsStmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Lọc theo tìm kiếm bằng PHP (hỗ trợ không dấu)
    if (!empty($search)) {
        $searchLower = mb_strtolower(trim($search), 'UTF-8');
        $searchNoAccent = mb_strtolower(removeVietnameseAccents($search), 'UTF-8');
        $searchLength = mb_strlen($searchNoAccent);
        $hasAccent = ($searchLower !== $searchNoAccent);
        
        $filteredProducts = [];
        foreach ($allProducts as $product) {
            $nameLower = mb_strtolower(trim($product['name']), 'UTF-8');
            $nameNoAccent = mb_strtolower(removeVietnameseAccents($product['name']), 'UTF-8');
            
            $matched = false;
            
            // Hàm kiểm tra match trong từ
            $checkMatch = function($text, $searchTerm) use ($searchLength) {
                $words = preg_split('/[\s\-_\.]+/', $text);
                foreach ($words as $word) {
                    $word = trim($word);
                    if (empty($word)) continue;
                    $wordLength = mb_strlen($word);
                    if ($word === $searchTerm && $searchLength >= 2) return true;
                    if ($wordLength > $searchLength && mb_substr($word, 0, $searchLength) === $searchTerm) {
                        if (($wordLength - $searchLength) >= 2) return true;
                    }
                }
                return false;
            };
            
            if ($hasAccent) {
                // Từ khóa có dấu - chỉ tìm có dấu
                if ($nameLower === $searchLower && $searchLength >= 2) $matched = true;
                elseif ($searchLength >= 2 && strpos($nameLower, $searchLower) === 0) $matched = true;
                elseif ($checkMatch($nameLower, $searchLower)) $matched = true;
            } else {
                // Từ khóa không dấu - tìm cả có dấu và không dấu
                if ($nameNoAccent === $searchNoAccent && $searchLength >= 2) $matched = true;
                elseif ($searchLength >= 2 && strpos($nameNoAccent, $searchNoAccent) === 0) $matched = true;
                elseif ($checkMatch($nameNoAccent, $searchNoAccent)) $matched = true;
            }
            
            if ($matched) $filteredProducts[] = $product;
        }
        $allProducts = $filteredProducts;
    }
    
    // Pagination
    $totalItems = count($allProducts);
    $totalPages = ceil($totalItems / $limit);
    $materials = array_slice($allProducts, $offset, $limit);
    
    // Get material SUB-CATEGORIES (danh mục con) for filter
    $categoriesQuery = "
        SELECT c.id, c.name, COUNT(p.id) as product_count
        FROM categories c
        LEFT JOIN products p ON c.id = p.category_id AND p.status = 1
        WHERE c.parent_id = 1
        GROUP BY c.id, c.name
        ORDER BY c.id
    ";
    $categoriesStmt = $pdo->query($categoriesQuery);
    $subCategories = $categoriesStmt->fetchAll(PDO::FETCH_ASSOC);
    
    // For backward compatibility - keep old categories variable
    $categories = [];
    $categoryCounts = [];
    
    // Build old format for existing code, thêm 'id' để filter
    foreach ($subCategories as $subCat) {
        $categories[] = [
            'id' => $subCat['id'],
            'classification' => $subCat['name']
        ];
        $categoryCounts[$subCat['name']] = $subCat['product_count'];
    }
    
} catch (Exception $e) {
    $materials = [];
    $categories = [];
    $totalItems = 0;
    $totalPages = 0;
    error_log("Lỗi khi truy xuất vật liệu: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Vật Liệu Xây Dựng - VNMaterial</title>
    
    <!-- Fonts -->
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
            background: #f0f9ff;
            color: #1e293b;
            line-height: 1.6;
        }
        
        .materials-hero {
            position: relative;
            left: 50%;
            right: 50%;
            margin-left: -50vw;
            margin-right: -50vw;
            width: 100vw;
            background: linear-gradient(135deg, #e0f2fe 0%, #bae6fd 100%);
            padding: 120px 0 80px;
            overflow: hidden;
            min-height: 420px;
        }
        
        .materials-hero::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><defs><pattern id="grid" width="10" height="10" patternUnits="userSpaceOnUse"><path d="M 10 0 L 0 0 0 10" fill="none" stroke="rgba(56,189,248,0.15)" stroke-width="0.5"/></pattern></defs><rect width="100" height="100" fill="url(%23grid)"/></svg>');
            opacity: 0.4;
        }
        
        .hero-content {
            max-width: 1400px;
            margin: 0 auto;
            padding: 0 20px;
            text-align: center;
            position: relative;
            z-index: 2;
        }
        
        .hero-title {
            font-size: 3.5rem;
            font-weight: 800;
            color: #0284c7;
            margin-bottom: 1.5rem;
            text-shadow: 0 2px 8px rgba(56,189,248,0.2);
        }
        
        .hero-subtitle {
            font-size: 1.3rem;
            color: #0ea5e9;
            max-width: 900px;
            margin: 0 auto 2.5rem;
            font-weight: 400;
        }
        
        /* Category Filter */
        .category-filter {
            background: white;
            border-radius: 16px;
            padding: 1.5rem;
            margin: -60px auto 2rem;
            max-width: 1280px;
            position: relative;
            z-index: 10;
            box-shadow: 0 10px 40px rgba(0,0,0,0.08);
        }

        .filter-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 1.2rem;
            flex-wrap: wrap;
            gap: 1rem;
        }

        .filter-title {
            font-size: 0.875rem;
            font-weight: 700;
            color: #64748b;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .search-box {
            position: relative;
            display: flex;
            flex: 1;
            max-width: 450px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
            border-radius: 12px;
            overflow: hidden;
            background: white;
        }

        .search-input {
            flex: 1;
            padding: 0.875rem 1.25rem;
            padding-right: 3.5rem;
            border: 2px solid #e0f2fe;
            border-radius: 12px;
            font-size: 0.9375rem;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            background: white;
        }

        .search-input:focus {
            outline: none;
            border-color: #3b82f6;
            box-shadow: 0 4px 16px rgba(59, 130, 246, 0.15);
            transform: translateY(-2px);
        }

        .search-input::placeholder {
            color: #94a3b8;
        }

        .search-btn {
            position: absolute;
            right: 4px;
            top: 50%;
            transform: translateY(-50%);
            width: 40px;
            height: 40px;
            padding: 0;
            background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
            color: white;
            border: none;
            border-radius: 50%;
            font-size: 1rem;
            cursor: pointer;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .search-btn:hover {
            background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%);
            transform: translateY(-50%) scale(1.05);
            box-shadow: 0 4px 12px rgba(59, 130, 246, 0.4);
        }

        .search-btn:active {
            transform: translateY(-50%) scale(0.95);
        }

        .search-btn i {
            font-size: 1rem;
        }

        .category-pills {
            display: flex;
            flex-wrap: wrap;
            gap: 0.75rem;
        }

        .category-pill {
            padding: 0.625rem 1.25rem;
            background: #f1f5f9;
            color: #64748b;
            border: 2px solid transparent;
            border-radius: 50px;
            font-size: 0.875rem;
            font-weight: 600;
            text-decoration: none;
            transition: all 0.2s;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            cursor: pointer;
        }

        .category-pill:hover {
            background: #e0f2fe;
            color: #2563eb;
            border-color: #3b82f6;
        }

        .category-pill.active {
            background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
            color: white;
            border-color: #3b82f6;
        }

        .category-pill .count {
            background: rgba(255,255,255,0.25);
            padding: 0.125rem 0.5rem;
            border-radius: 50px;
            font-size: 0.75rem;
            font-weight: 700;
        }
        
        .main-content {
            max-width: 1400px;
            margin: 0 auto;
            padding: 0 30px;
        }
        
        .materials-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 1.25rem;
            margin-bottom: 2.5rem;
            align-items: stretch;
        }
        
        .material-card {
            background: white;
            border-radius: 16px;
            overflow: hidden;
            box-shadow: 0 6px 20px rgba(0,0,0,0.06);
            transition: all 0.28s ease;
            position: relative;
            border: 1px solid #f1f5f9;
            padding: 0;
            display: flex;
            flex-direction: column;
            height: 100%;
            cursor: pointer;
        }
        
        .material-card:hover {
            transform: translateY(-8px);
            box-shadow: 0 20px 60px rgba(0,0,0,0.15);
        }
        
        .material-header {
            padding: 0;
            background: linear-gradient(135deg, #f8fafc 0%, #f0f9ff 100%);
            position: relative;
            display: flex;
            align-items: center;
            justify-content: center;
            height: 160px;
            overflow: hidden;
        }
        
        .material-image {
            width: 100%;
            height: 100%;
            object-fit: cover;
            display: block;
        }
        
        .material-category {
            position: absolute;
            top: 0.75rem;
            right: 0.75rem;
            background: linear-gradient(135deg, #38bdf8 0%, #22d3ee 100%);
            color: white;
            padding: 0.35rem 0.75rem;
            border-radius: 16px;
            font-size: 0.7rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            box-shadow: 0 2px 8px rgba(56, 189, 248, 0.25);
        }
        
        .material-body {
            padding: 0.85rem 0.85rem 0.75rem;
            flex: 1 1 auto;
            display: flex;
            flex-direction: column;
        }
        
        .material-name {
            font-size: 0.95rem;
            font-weight: 700;
            color: #1e293b;
            margin-bottom: 0.4rem;
            text-align: center;
            min-height: 2.3rem;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }
        
        .material-description {
            color: #64748b;
            margin-bottom: 0.65rem;
            line-height: 1.35;
            text-align: center;
            font-size: 0.82rem;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
            min-height: 2.2rem;
        }
        
        .material-details {
            margin-bottom: 0.6rem;
        }
        
        .material-detail {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            color: #475569;
            font-size: 0.82rem;
            margin-bottom: 0.4rem;
        }
        
        .material-detail i {
            width: 14px;
            color: #38bdf8;
            font-size: 0.9rem;
        }
        
        .material-footer {
            padding: 0.65rem 0.85rem 0.85rem;
            display: flex;
            justify-content: center;
            align-items: center;
            flex-shrink: 0;
            border-top: 1px solid #f1f5f9;
        }
        
        .view-material {
            background: linear-gradient(135deg, #38bdf8 0%, #22d3ee 100%);
            color: white;
            padding: 0.5rem 1.2rem;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 600;
            font-size: 0.8rem;
            transition: all 0.22s;
            display: inline-flex;
            align-items: center;
            gap: 0.4rem;
            box-shadow: 0 2px 8px rgba(56, 189, 248, 0.25);
        }

        .view-material:hover {
            transform: translateY(-1px);
            box-shadow: 0 4px 15px rgba(56, 189, 248, 0.35);
            text-decoration: none;
            color: white;
            background: linear-gradient(135deg, #0ea5e9 0%, #06b6d4 100%);
        }
        
        .material-price {
            color: #059669;
            font-size: 0.85rem;
            font-weight: 700;
            display: none;
        }
        
        .pagination {
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 0.5rem;
            margin: 3rem 0;
        }
        
        .pagination a, .pagination span {
            padding: 0.8rem 1.2rem;
            border-radius: 10px;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s;
        }
        
        .pagination a {
            background: white;
            color: #475569;
            border: 1px solid #e2e8f0;
        }
        
        .pagination a:hover {
            background: #38bdf8;
            color: white;
            border-color: #38bdf8;
        }
        
        .pagination .current {
            background: #38bdf8;
            color: white;
            border: 1px solid #38bdf8;
        }
        
        .no-results {
            text-align: center;
            padding: 4rem 2rem;
            color: #64748b;
        }
        
        .no-results i {
            font-size: 4rem;
            color: #cbd5e1;
            margin-bottom: 1.5rem;
        }
        
        .no-results h3 {
            font-size: 1.5rem;
            margin-bottom: 1rem;
            color: #475569;
        }
        
        @media (max-width: 1200px) {
            .materials-grid {
                grid-template-columns: repeat(3, 1fr);
            }
        }

        @media (max-width: 900px) {
            .materials-grid {
                grid-template-columns: repeat(2, 1fr);
            }
        }

        @media (max-width: 560px) {
            .materials-grid {
                grid-template-columns: 1fr;
            }

            .hero-title {
                font-size: 2rem;
            }
        }
    </style>
</head>
<body>
    <!-- Hero Section -->
    <section class="materials-hero">
        <div class="hero-content">
            <h1 class="hero-title"><?php echo t('materials_title'); ?></h1>
            <p class="hero-subtitle">
                <?php echo t('materials_description'); ?>
            </p>
        </div>
    </section>

    <!-- Search Section -->
    <div class="main-content">
        <!-- Category Filter Pills -->
        <div class="category-filter">
            <div class="filter-header">
                <span class="filter-title">
                    <i class="fas fa-tags"></i> DANH MỤC
                </span>
                <form method="GET" action="" class="search-box">
                    <input type="text" 
                           name="search" 
                           class="search-input" 
                           placeholder="<?php echo t('materials_search_placeholder'); ?>" 
                           value="<?php echo htmlspecialchars($search); ?>">
                    <?php if ($categoryId > 0): ?>
                        <input type="hidden" name="category_id" value="<?php echo $categoryId; ?>">
                    <?php endif; ?>
                    <button type="submit" class="search-btn" title="Tìm kiếm">
                        <i class="fas fa-search"></i>
                    </button>
                </form>
            </div>
            
            <div class="category-pills">
                <!-- All categories pill -->
                <a href="materials.php<?php echo !empty($search) ? '?search=' . urlencode($search) : ''; ?>" 
                   class="category-pill <?php echo $categoryId == 0 ? 'active' : ''; ?>">
                    <i class="fas fa-th"></i>
                    <span>Tất cả</span>
                    <span class="count"><?php echo $totalItems; ?></span>
                </a>
                
                <?php foreach ($categories as $cat): ?>
                    <?php 
                    $catName = $cat['classification'];
                    $catId = $cat['id']; // Lấy ID từ categories table
                    $catCount = isset($categoryCounts[$catName]) ? $categoryCounts[$catName] : 0;
                    $isActive = ($categoryId == $catId);
                    $url = 'materials.php?category_id=' . $catId;
                    if (!empty($search)) {
                        $url .= '&search=' . urlencode($search);
                    }
                    ?>
                    <a href="<?php echo $url; ?>" 
                       class="category-pill <?php echo $isActive ? 'active' : ''; ?>">
                        <span><?php echo htmlspecialchars($catName); ?></span>
                        <span class="count"><?php echo $catCount; ?></span>
                    </a>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Results -->
        <?php if (!empty($search) || $categoryId > 0): ?>
            <div style="margin-bottom: 2rem; text-align: center;">
                <h2 style="color: #1e293b; margin-bottom: 0.5rem;">
                    Tìm thấy <?php echo $totalItems; ?> vật liệu
                </h2>
                <?php if ($search): ?>
                    <p style="color: #64748b;">Từ khóa: "<?php echo htmlspecialchars($search); ?>"</p>
                <?php endif; ?>
                <?php if ($categoryId > 0): ?>
                    <?php
                    // Tìm tên danh mục từ $categories
                    $selectedCatName = '';
                    foreach ($categories as $cat) {
                        if ($cat['id'] == $categoryId) {
                            $selectedCatName = $cat['classification'];
                            break;
                        }
                    }
                    ?>
                    <p style="color: #64748b;">Danh mục: <?php echo htmlspecialchars($selectedCatName); ?></p>
                <?php endif; ?>
            </div>
        <?php endif; ?>

        <!-- Materials Grid -->
        <?php if (!empty($materials)): ?>
            <div class="materials-grid">
                <?php foreach ($materials as $material): ?>
                    <div class="material-card" onclick="window.location.href='<?php echo buildProductUrl($material); ?>'">
                        <?php if (!empty($material['classification'])): ?>
                            <div class="material-category">
                                <?php echo htmlspecialchars($material['classification']); ?>
                            </div>
                        <?php endif; ?>
                        
                        <div class="material-header">
                            <?php if (!empty($material['featured_image'])): ?>
                                <img src="<?php echo htmlspecialchars($material['featured_image']); ?>" 
                                     alt="<?php echo htmlspecialchars($material['name']); ?>" 
                                     class="material-image">
                            <?php else: ?>
                                <div class="material-image" style="background: linear-gradient(135deg, #38bdf8 0%, #22d3ee 100%); display: flex; align-items: center; justify-content: center;">
                                    <i class="fas fa-boxes" style="color: white; font-size: 3rem; opacity: 0.7;"></i>
                                </div>
                            <?php endif; ?>
                        </div>
                        
                        <div class="material-body">
                            <h3 class="material-name"><?php echo htmlspecialchars($material['name']); ?></h3>
                            <p class="material-description">
                                <?php echo htmlspecialchars(substr($material['description'] ?? 'Vật liệu xây dựng chất lượng cao', 0, 80)); ?>
                                <?php if (strlen($material['description'] ?? '') > 80): ?>...<?php endif; ?>
                            </p>
                            
                            <div class="material-details">
                                <?php if ($material['brand']): ?>
                                    <div class="material-detail">
                                        <i class="fas fa-industry"></i>
                                        <span><?php echo htmlspecialchars($material['brand']); ?></span>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                        
                        <div class="material-footer">
                            <a href="<?php echo buildProductUrl($material); ?>" class="view-material" onclick="event.stopPropagation();">
                                <span>Xem chi tiết</span>
                                <i class="fas fa-arrow-right"></i>
                            </a>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <!-- Pagination -->
            <?php if ($totalPages > 1): ?>
                <div class="pagination">
                    <?php
                    $baseUrl = '?';
                    if ($search) $baseUrl .= 'search=' . urlencode($search) . '&';
                    if ($categoryId > 0) $baseUrl .= 'category_id=' . $categoryId . '&';
                    ?>
                    
                    <?php if ($page > 1): ?>
                        <a href="<?php echo $baseUrl; ?>page=<?php echo $page - 1; ?>">
                            <i class="fas fa-chevron-left"></i> Trước
                        </a>
                    <?php endif; ?>
                    
                    <?php for ($i = max(1, $page - 2); $i <= min($totalPages, $page + 2); $i++): ?>
                        <?php if ($i === $page): ?>
                            <span class="current"><?php echo $i; ?></span>
                        <?php else: ?>
                            <a href="<?php echo $baseUrl; ?>page=<?php echo $i; ?>"><?php echo $i; ?></a>
                        <?php endif; ?>
                    <?php endfor; ?>
                    
                    <?php if ($page < $totalPages): ?>
                        <a href="<?php echo $baseUrl; ?>page=<?php echo $page + 1; ?>">
                            Sau <i class="fas fa-chevron-right"></i>
                        </a>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        <?php else: ?>
            <div class="no-results">
                <i class="fas fa-boxes"></i>
                <h3><?php echo t('materials_no_results'); ?></h3>
                <p><?php echo t('materials_no_results_hint'); ?></p>
                <a href="materials.php" style="color: #38bdf8; text-decoration: none; font-weight: 600; margin-top: 1rem; display: inline-block;">
                    ← <?php echo t('materials_view_all'); ?>
                </a>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>

<?php include 'inc/footer-new.php'; ?>
