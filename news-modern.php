<?php 
require_once 'config.php';
require_once 'lang/lang.php';
require_once 'lang/db_translate_helper.php';
require_once 'inc/db_frontend.php';
require_once 'inc/url_helpers.php';

include 'inc/header-new.php';

$pdo = getFrontendPDO();

// Get filter parameters
$category = isset($_GET['category']) ? $_GET['category'] : '';
$search = isset($_GET['search']) ? $_GET['search'] : '';
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$perPage = 9;

// Build query
$where = ["status = 'published'"];
$params = [];

if (!empty($category)) {
    $where[] = "category = ?";
    $params[] = $category;
}

if (!empty($search)) {
    $where[] = "(title LIKE ? OR content LIKE ? OR excerpt LIKE ?)";
    $searchTerm = "%{$search}%";
    $params[] = $searchTerm;
    $params[] = $searchTerm;
    $params[] = $searchTerm;
}

$whereClause = implode(' AND ', $where);

// Get total count
$countSql = "SELECT COUNT(*) as total FROM posts WHERE {$whereClause}";
$stmt = $pdo->prepare($countSql);
$stmt->execute($params);
$total = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
$totalPages = ceil($total / $perPage);

// Get posts with pagination (include English columns)
$offset = ($page - 1) * $perPage;
$sql = "SELECT p.*, p.title_en, p.excerpt_en, p.content_en,
        (SELECT COUNT(*) FROM comments WHERE post_id = p.id AND status = 'approved') as comment_count
        FROM posts p 
        WHERE {$whereClause} 
        ORDER BY published_at DESC, created_at DESC 
        LIMIT {$perPage} OFFSET {$offset}";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$newsList = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get categories
$categories = $pdo->query("SELECT DISTINCT category FROM posts WHERE status = 'published' AND category IS NOT NULL AND category != '' ORDER BY category")->fetchAll(PDO::FETCH_COLUMN);
?>

<style>
    :root {
        --primary: #38bdf8;
        --primary-dark: #0284c7;
        --primary-light: #e0f2fe;
        --text-dark: #0f172a;
        --text-gray: #64748b;
        --bg-light: #f8fafc;
        --white: #ffffff;
        --border: #e2e8f0;
    }

    body {
        background: var(--bg-light);
        font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
    }

    .news-container {
        max-width: 1280px;
        margin: 0 auto;
        padding: 2rem 1rem;
    }

    /* Hero Section - Compact */
    .news-hero {
        text-align: center;
        margin-bottom: 3rem;
        padding: 2rem 0;
    }

    .news-hero h1 {
        font-size: 3rem;
        font-weight: 800;
        color: var(--text-dark);
        margin: 0 0 0.75rem 0;
    }

    .news-hero p {
        font-size: 1.32rem;
        color: var(--text-gray);
        margin: 0;
    }

    /* Category Filter Pills */
    .category-filter {
        background: var(--white);
        border-radius: 16px;
        padding: 1.5rem;
        margin-bottom: 2rem;
        box-shadow: 0 1px 3px rgba(0,0,0,0.1);
    }

    .filter-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 1rem;
        flex-wrap: wrap;
        gap: 1rem;
    }

    .filter-title {
        font-size: 0.875rem;
        font-weight: 600;
        color: var(--text-gray);
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .search-box {
        position: relative;
        display: flex;
        flex: 1;
        max-width: 400px;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
        border-radius: 12px;
        overflow: hidden;
        background: var(--white);
    }

    .search-input {
        flex: 1;
        padding: 0.875rem 1.25rem;
        padding-right: 3.5rem;
        border: 2px solid var(--border);
        border-radius: 12px;
        font-size: 0.9375rem;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        background: var(--white);
    }

    .search-input:focus {
        outline: none;
        border-color: var(--primary);
        box-shadow: 0 4px 16px rgba(56, 189, 248, 0.15);
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
        background: var(--primary);
        color: var(--white);
        border: none;
        border-radius: 10px;
        font-size: 1rem;
        cursor: pointer;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .search-btn:hover {
        background: var(--primary-dark);
        transform: translateY(-50%) scale(1.05);
        box-shadow: 0 4px 12px rgba(56, 189, 248, 0.4);
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
        background: var(--bg-light);
        color: var(--text-gray);
        border: 2px solid transparent;
        border-radius: 50px;
        font-size: 0.875rem;
        font-weight: 600;
        text-decoration: none;
        transition: all 0.2s;
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
    }

    .category-pill:hover {
        background: var(--primary-light);
        color: var(--primary-dark);
        border-color: var(--primary);
    }

    .category-pill.active {
        background: var(--primary);
        color: var(--white);
        border-color: var(--primary);
    }

    .category-pill .count {
        background: rgba(255,255,255,0.3);
        padding: 0.125rem 0.5rem;
        border-radius: 50px;
        font-size: 0.75rem;
    }

    .category-pill.active .count {
        background: rgba(255,255,255,0.3);
    }

    /* News Grid */
    .news-grid {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 1.5rem;
        margin-bottom: 3rem;
    }

    @media (max-width: 1024px) {
        .news-grid {
            grid-template-columns: repeat(2, 1fr);
        }
    }

    @media (max-width: 640px) {
        .news-grid {
            grid-template-columns: 1fr;
        }

        .filter-header {
            flex-direction: column;
            align-items: stretch;
        }

        .search-box {
            max-width: 100%;
        }

        .search-input {
            font-size: 1rem;
        }
    }

    /* News Card */
    .news-card {
        background: var(--white);
        border-radius: 12px;
        overflow: hidden;
        box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        transition: all 0.3s;
        display: flex;
        flex-direction: column;
        text-decoration: none;
        color: inherit;
        cursor: pointer;
    }

    .news-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 12px 24px rgba(0,0,0,0.15);
    }
    
    .news-card:hover .news-card-title {
        color: var(--primary);
    }

    .news-card-image {
        width: 100%;
        height: 200px;
        object-fit: cover;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    }

    .news-card-body {
        padding: 1.5rem;
        flex: 1;
        display: flex;
        flex-direction: column;
    }

    .news-card-category {
        display: inline-block;
        padding: 0.25rem 0.75rem;
        background: var(--primary-light);
        color: var(--primary-dark);
        font-size: 0.75rem;
        font-weight: 600;
        border-radius: 50px;
        margin-bottom: 0.75rem;
        width: fit-content;
    }

    .news-card-title {
        font-size: 1.125rem;
        font-weight: 700;
        color: var(--text-dark);
        margin: 0 0 0.75rem 0;
        line-height: 1.4;
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }

    .news-card-excerpt {
        font-size: 0.875rem;
        color: var(--text-gray);
        line-height: 1.6;
        margin-bottom: 1rem;
        display: -webkit-box;
        -webkit-line-clamp: 3;
        -webkit-box-orient: vertical;
        overflow: hidden;
        flex: 1;
    }

    .news-card-footer {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding-top: 1rem;
        border-top: 1px solid var(--border);
    }

    .news-card-meta {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        font-size: 0.75rem;
        color: var(--text-gray);
    }

    .news-card-link {
        color: var(--primary);
        font-weight: 600;
        font-size: 0.875rem;
        text-decoration: none;
        display: flex;
        align-items: center;
        gap: 0.25rem;
        transition: all 0.2s;
    }

    .news-card-link:hover {
        color: var(--primary-dark);
        gap: 0.5rem;
    }

    /* Empty State */
    .empty-state {
        text-align: center;
        padding: 4rem 2rem;
        background: var(--white);
        border-radius: 12px;
        box-shadow: 0 1px 3px rgba(0,0,0,0.1);
    }

    .empty-state-icon {
        font-size: 4rem;
        margin-bottom: 1rem;
        opacity: 0.3;
    }

    .empty-state h3 {
        font-size: 1.5rem;
        color: var(--text-dark);
        margin: 0 0 0.5rem 0;
    }

    .empty-state p {
        color: var(--text-gray);
        margin: 0;
    }

    /* Pagination */
    .pagination {
        display: flex;
        justify-content: center;
        align-items: center;
        gap: 0.5rem;
        margin-top: 3rem;
    }

    .pagination-btn {
        padding: 0.625rem 1rem;
        background: var(--white);
        color: var(--text-gray);
        border: 2px solid var(--border);
        border-radius: 8px;
        font-weight: 600;
        text-decoration: none;
        transition: all 0.2s;
        font-size: 0.875rem;
    }

    .pagination-btn:hover:not(.disabled) {
        background: var(--primary-light);
        color: var(--primary-dark);
        border-color: var(--primary);
    }

    .pagination-btn.active {
        background: var(--primary);
        color: var(--white);
        border-color: var(--primary);
    }

    .pagination-btn.disabled {
        opacity: 0.4;
        cursor: not-allowed;
    }

    /* Results Info */
    .results-info {
        text-align: center;
        color: var(--text-gray);
        font-size: 0.875rem;
        margin-bottom: 1.5rem;
    }

    .results-info strong {
        color: var(--primary-dark);
        font-weight: 700;
    }
</style>

<div class="news-container">
    <!-- Hero -->
    <div class="news-hero">
        <h1>📰 Tin Tức & Bài Viết</h1>
        <p>Cập nhật tin tức mới nhất về vật liệu xây dựng, công nghệ và xu hướng ngành</p>
    </div>

    <!-- Category Filter -->
    <div class="category-filter">
        <div class="filter-header">
            <span class="filter-title">🏷️ Danh mục</span>
            <form method="GET" action="" class="search-box">
                <input type="text" 
                       name="search" 
                       class="search-input" 
                       placeholder="Tìm kiếm bài viết..." 
                       value="<?php echo htmlspecialchars($search); ?>">
                <button type="submit" class="search-btn" title="Tìm kiếm">
                    <i class="fas fa-search"></i>
                </button>
            </form>
        </div>

        <div class="category-pills">
            <a href="news-modern.php" class="category-pill <?php echo empty($category) ? 'active' : ''; ?>">
                <i class="fas fa-th"></i>
                <span>Tất cả</span>
                <span class="count"><?php echo $total; ?></span>
            </a>
            <?php 
            // Count posts per category
            $categoryCounts = [];
            $stmt = $pdo->query("SELECT category, COUNT(*) as count FROM posts WHERE status = 'published' AND category IS NOT NULL AND category != '' GROUP BY category");
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $categoryCounts[$row['category']] = $row['count'];
            }
            
            foreach ($categories as $cat): 
                if (empty($cat)) continue;
                $count = isset($categoryCounts[$cat]) ? $categoryCounts[$cat] : 0;
            ?>
            <a href="news-modern.php?category=<?php echo urlencode($cat); ?>" 
               class="category-pill <?php echo $category === $cat ? 'active' : ''; ?>">
                <span><?php echo htmlspecialchars($cat); ?></span>
                <span class="count"><?php echo $count; ?></span>
            </a>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- Results Info -->
    <?php if (!empty($search) || !empty($category)): ?>
    <div class="results-info">
        Tìm thấy <strong><?php echo $total; ?> bài viết</strong>
        <?php if (!empty($category)): ?>
            trong danh mục <strong><?php echo htmlspecialchars($category); ?></strong>
        <?php endif; ?>
        <?php if (!empty($search)): ?>
            với từ khóa "<strong><?php echo htmlspecialchars($search); ?></strong>"
        <?php endif; ?>
    </div>
    <?php endif; ?>

    <!-- News Grid -->
    <?php if (!empty($newsList)): ?>
    <div class="news-grid">
        <?php foreach ($newsList as $news): ?>
        <a href="<?php echo buildArticleUrl($news); ?>" class="news-card">
            <?php if($news['featured_image']): ?>
            <img src="<?php echo htmlspecialchars($news['featured_image']); ?>" 
                 alt="<?php echo htmlspecialchars(getTranslatedTitle($news)); ?>"
                 class="news-card-image"
                 onerror="this.src='data:image/svg+xml,%3Csvg xmlns=%22http://www.w3.org/2000/svg%22 width=%22400%22 height=%22200%22%3E%3Crect fill=%22%2338bdf8%22 width=%22400%22 height=%22200%22/%3E%3Ctext fill=%22%23ffffff%22 font-family=%22Arial%22 font-size=%2220%22 text-anchor=%22middle%22 x=%22200%22 y=%22100%22%3EBài viết%3C/text%3E%3C/svg%3E'">
            <?php else: ?>
            <div class="news-card-image" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); display: flex; align-items: center; justify-content: center; color: white; font-size: 24px; font-weight: 700;">
                <?php echo htmlspecialchars(mb_substr(getTranslatedTitle($news), 0, 30)); ?>
            </div>
            <?php endif; ?>
            
            <div class="news-card-body">
                <?php if($news['category']): ?>
                <span class="news-card-category">
                    <?php echo htmlspecialchars($news['category']); ?>
                </span>
                <?php endif; ?>
                
                <h2 class="news-card-title">
                    <?php echo htmlspecialchars(getTranslatedTitle($news)); ?>
                </h2>
                
                <p class="news-card-excerpt">
                    <?php echo htmlspecialchars(getTranslatedExcerpt($news)); ?>
                </p>
                
                <div class="news-card-footer">
                    <div class="news-card-meta">
                        <i class="far fa-calendar"></i>
                        <span><?php echo date('d/m/Y', strtotime($news['published_at'] ?? $news['created_at'])); ?></span>
                        <span>•</span>
                        <i class="far fa-comments"></i>
                        <span><?php echo $news['comment_count'] ?? 0; ?> bình luận</span>
                    </div>
                    
                    <span class="news-card-link">
                        Đọc thêm <i class="fas fa-arrow-right"></i>
                    </span>
                </div>
            </div>
        </a>
        <?php endforeach; ?>
    </div>

    <!-- Pagination -->
    <?php if ($totalPages > 1): ?>
    <div class="pagination">
        <!-- Previous -->
        <?php if ($page > 1): ?>
            <a href="?page=<?php echo ($page - 1); ?><?php echo !empty($category) ? '&category=' . urlencode($category) : ''; ?><?php echo !empty($search) ? '&search=' . urlencode($search) : ''; ?>" 
               class="pagination-btn">
                <i class="fas fa-chevron-left"></i> Trước
            </a>
        <?php else: ?>
            <span class="pagination-btn disabled">
                <i class="fas fa-chevron-left"></i> Trước
            </span>
        <?php endif; ?>

        <!-- Page Numbers -->
        <?php 
        $start = max(1, $page - 2);
        $end = min($totalPages, $page + 2);
        
        for ($i = $start; $i <= $end; $i++): 
        ?>
            <a href="?page=<?php echo $i; ?><?php echo !empty($category) ? '&category=' . urlencode($category) : ''; ?><?php echo !empty($search) ? '&search=' . urlencode($search) : ''; ?>" 
               class="pagination-btn <?php echo $i === $page ? 'active' : ''; ?>">
                <?php echo $i; ?>
            </a>
        <?php endfor; ?>

        <!-- Next -->
        <?php if ($page < $totalPages): ?>
            <a href="?page=<?php echo ($page + 1); ?><?php echo !empty($category) ? '&category=' . urlencode($category) : ''; ?><?php echo !empty($search) ? '&search=' . urlencode($search) : ''; ?>" 
               class="pagination-btn">
                Sau <i class="fas fa-chevron-right"></i>
            </a>
        <?php else: ?>
            <span class="pagination-btn disabled">
                Sau <i class="fas fa-chevron-right"></i>
            </span>
        <?php endif; ?>
    </div>
    <?php endif; ?>

    <?php else: ?>
    <!-- Empty State -->
    <div class="empty-state">
        <div class="empty-state-icon">🔍</div>
        <h3>Không tìm thấy bài viết</h3>
        <p>Thử tìm kiếm với từ khóa khác hoặc chọn danh mục khác</p>
        <?php if (!empty($search) || !empty($category)): ?>
        <a href="news-modern.php" class="search-btn" style="margin-top: 1.5rem; display: inline-block; text-decoration: none;">
            <i class="fas fa-redo"></i> Xem tất cả bài viết
        </a>
        <?php endif; ?>
    </div>
    <?php endif; ?>
</div>

<?php include 'inc/footer-new.php'; ?>
