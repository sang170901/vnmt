<?php
// Redirect legacy news.php to news-modern.php
header('Location: /vnmt/news-modern.php' . (empty($_SERVER['QUERY_STRING']) ? '' : '?' . $_SERVER['QUERY_STRING']));
exit;

// include 'inc/header-new.php';
// require_once 'backend/inc/news_manager.php';

// Initialize NewsManager
$newsManager = new NewsManager();

// Get filter parameters
$category = isset($_GET['category']) ? $_GET['category'] : '';
$search = isset($_GET['search']) ? $_GET['search'] : '';

// Get news list
$newsList = $newsManager->getNews($category, $search);

// Get categories for filter
$categories = $newsManager->getCategories();
?>

<style>
    :root {
        --primary-color: #4da6ff; /* xanh nước biển nhạt */
        --primary-600: #3d8ef0;
        --secondary-color: #eaf6ff; /* nền nhạt */
        --accent-color: #60a5fa;
        --text-primary: #0f172a;
        --text-secondary: #475569;
        --border-color: #dbeeff;
        --card-bg: #ffffff;
    }

    body {
        background: linear-gradient(180deg, var(--secondary-color) 0%, #f7fdff 100%);
    }

    .news-page {
        max-width: 1200px;
        margin: 0 auto;
        padding: 2.5rem 1rem;
        font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', 'Roboto', sans-serif;
        color: var(--text-primary);
    }

    /* Hero Header */
    .news-hero {
        background: transparent;
        border-radius: 12px;
        padding: 2.5rem 1.5rem;
        text-align: center;
        color: var(--text-primary);
        margin-bottom: 2rem;
        position: relative;
    }

    .news-hero h1 {
        font-size: 2.4rem;
        font-weight: 700;
        margin-bottom: 0.5rem;
        color: var(--text-primary);
    }

    .news-hero .subtitle {
        font-size: 1rem;
        opacity: 0.9;
        font-weight: 400;
        color: var(--text-secondary);
    }

    /* Filters container */
    .news-filters {
        background: transparent;
        padding: 0;
        margin-bottom: 1.5rem;
    }

    .filter-grid {
        display: grid;
        grid-template-columns: 1fr 360px;
        gap: 1.5rem;
        align-items: start;
    }

    .category-pill {
        padding: 0.5rem 1rem;
        background: rgba(77,166,255,0.12);
        color: var(--primary-600);
        border-radius: 999px;
        font-weight: 600;
        font-size: 0.9rem;
        border: 1px solid rgba(77,166,255,0.18);
    }

    .search-input {
        background: white;
        border: 1px solid var(--border-color);
    }

    /* Main Content Grid */
    .content-grid {
        display: grid;
        grid-template-columns: 2fr 1fr;
        gap: 2.5rem;
    }

    /* Article Cards */
    .articles-section h2 {
        font-size: 1.6rem;
        font-weight: 700;
        color: var(--text-primary);
        margin-bottom: 1.5rem;
    }

    .article-card {
        background: var(--card-bg);
        border-radius: 14px;
        overflow: hidden;
        margin-bottom: 1.5rem;
        box-shadow: 0 6px 18px rgba(30,58,90,0.06);
        border: 1px solid var(--border-color);
        transition: transform 0.28s ease, box-shadow 0.28s ease;
        display: flex;
        gap: 0;
    }

    .article-card:hover {
        transform: translateY(-6px);
        box-shadow: 0 20px 40px rgba(15,23,42,0.08);
    }

    .article-image {
        width: 40%;
        min-width: 220px;
        height: auto;
        background: linear-gradient(135deg, rgba(77,166,255,0.9), rgba(96,165,250,0.9));
        display: flex;
        align-items: center;
        justify-content: center;
        color: rgba(255,255,255,0.95);
        font-size: 2.8rem;
    }

    .article-content {
        padding: 1.6rem 1.8rem;
        flex: 1;
    }

    .article-meta {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 0.75rem;
        gap: 1rem;
    }

    .article-category {
        background: rgba(77,166,255,0.12);
        color: var(--primary-600);
        padding: 0.35rem 0.8rem;
        border-radius: 999px;
        font-size: 0.78rem;
        font-weight: 700;
        letter-spacing: 0.6px;
    }

    .article-date { color: var(--text-secondary); font-size: 0.9rem; }

    .article-title { font-size: 1.2rem; font-weight: 700; margin-bottom: 0.6rem; }

    .article-excerpt { color: var(--text-secondary); line-height: 1.7; margin-bottom: 1rem; }

    .read-more-btn {
        padding: 0.5rem 1rem;
        background: transparent;
        color: var(--primary-600);
        border: 1px solid rgba(77,166,255,0.15);
        border-radius: 8px;
        font-weight: 700;
    }

    /* Sidebar styles */
    .sidebar-widget {
        background: white;
        border-radius: 12px;
        padding: 1rem;
        margin-bottom: 1.25rem;
        box-shadow: 0 6px 18px rgba(15,23,42,0.04);
        border: 1px solid var(--border-color);
    }

    .widget-title { font-size: 1rem; font-weight: 700; color: var(--text-primary); margin-bottom: 0.8rem; }

    .recent-item { display: flex; gap: 0.8rem; padding: 0.4rem 0; align-items: center; }
    .recent-image { width: 48px; height: 48px; border-radius: 8px; background: rgba(77,166,255,0.12); display:flex; align-items:center; justify-content:center; font-size:1.1rem; }
    .recent-title a { color: var(--text-primary); text-decoration: none; font-weight: 600; }
    .recent-meta { color: var(--text-secondary); font-size: 0.85rem; }

    .stats-grid { display:flex; gap: 1rem; }
    .stat-item { background: rgba(77,166,255,0.06); padding: 0.6rem 0.9rem; border-radius: 8px; text-align:center; }
    .stat-number { font-size: 1.1rem; font-weight:700; color: var(--primary-600); }

    /* Responsive adjustments */
    @media (max-width: 1200px) {
        .content-grid { grid-template-columns: 1fr; }
        .article-card { flex-direction: column; }
        .article-image { width: 100%; height: 180px; min-width: auto; }
        .article-content { padding: 1.25rem; }
    }

    @media (max-width: 480px) {
        .news-page { padding: 1.25rem; }
        .news-hero h1 { font-size: 1.6rem; }
        .article-image { height: 140px; }
    }


    /* Sidebar */
    .sidebar {
        position: sticky;
        top: 2rem;
        height: fit-content;
    }

    .sidebar-widget {
        background: white;
        border-radius: 16px;
        padding: 2rem;
        margin-bottom: 2rem;
        box-shadow: 0 4px 25px rgba(0,0,0,0.08);
        border: 1px solid var(--border-color);
    }

    .widget-title {
        font-size: 1.3rem;
        font-weight: 700;
        color: var(--text-primary);
        margin-bottom: 1.5rem;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .recent-item {
        display: flex;
        gap: 1rem;
        padding: 1rem 0;
        border-bottom: 1px solid var(--border-color);
    }

    .recent-item:last-child {
        border-bottom: none;
    }

    .recent-image {
        width: 60px;
        height: 60px;
        border-radius: 10px;
        background: linear-gradient(45deg, var(--primary-color), var(--accent-color));
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-size: 1.2rem;
        flex-shrink: 0;
    }

    .recent-content {
        flex: 1;
    }

    .recent-title {
        font-weight: 600;
        font-size: 0.9rem;
        line-height: 1.4;
        margin-bottom: 0.5rem;
    }

    .recent-title a {
        color: var(--text-primary);
        text-decoration: none;
    }

    .recent-title a:hover {
        color: var(--primary-color);
    }

    .recent-meta {
        font-size: 0.8rem;
        color: var(--text-secondary);
    }

    /* Stats Widget */
    .stats-grid {
        display: grid;
        grid-template-columns: 1fr;
        gap: 1rem;
    }

    .stat-item {
        text-align: center;
        padding: 1rem;
        background: var(--secondary-color);
        border-radius: 12px;
    }

    .stat-number {
        font-size: 1.5rem;
        font-weight: 700;
        color: var(--primary-color);
    }

    .stat-label {
        font-size: 0.8rem;
        color: var(--text-secondary);
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    /* Responsive */
    @media (max-width: 1024px) {
        .content-grid {
            grid-template-columns: 1fr;
        }
        
        .sidebar {
            position: static;
        }
    }

    @media (max-width: 768px) {
        .news-page {
            padding: 1rem 0.5rem;
        }

        .news-hero {
            padding: 2rem 1rem;
        }

        .news-hero h1 {
            font-size: 2.2rem;
        }

        .filter-grid {
            grid-template-columns: 1fr;
            gap: 1.5rem;
        }

        .category-pills {
            justify-content: center;
        }

        .article-content {
            padding: 1.5rem;
        }

        .stats-grid {
            grid-template-columns: 1fr;
        }
    }
</style>

<div class="news-page">
    <!-- Hero Header -->
    <div class="news-hero">
        <h1>📰 Bản Tin Vật Tư</h1>
        <p class="subtitle">Cập nhật thông tin mới nhất về vật liệu xây dựng, công nghệ và xu hướng thị trường</p>
    </div>

    <!-- Modern Filters -->
    <div class="news-filters">
        <div class="filter-grid">
            <div class="category-section">
                <h3><i class="fas fa-layer-group"></i> Danh mục tin tức</h3>
                <div class="category-pills">
                    <a href="news-modern.php" class="category-pill <?php echo empty($category) ? 'active' : ''; ?>">
                        Tất cả
                    </a>
                    <?php foreach ($categories as $cat): ?>
                        <a href="news-modern.php?category=<?php echo urlencode($cat); ?>" 
                           class="category-pill <?php echo $category === $cat ? 'active' : ''; ?>">
                            <?php echo htmlspecialchars($cat); ?>
                        </a>
                    <?php endforeach; ?>
                </div>
            </div>
            
            <div class="search-section">
                <h3><i class="fas fa-search"></i> Tìm kiếm bài viết</h3>
                <div class="search-wrapper">
                    <i class="fas fa-search search-icon"></i>
                    <input type="text" 
                           class="search-input"
                           placeholder="Nhập từ khóa..." 
                           value="<?php echo htmlspecialchars($search); ?>"
                           onkeypress="if(event.key==='Enter') window.location.href='news-modern.php?search='+encodeURIComponent(this.value)">
                </div>
            </div>
        </div>
    </div>

    <!-- Main Content Grid -->
    <div class="content-grid">
        <!-- Articles Section -->
        <div class="articles-section">
            <h2><i class="fas fa-newspaper"></i> Bài viết mới nhất</h2>
            
            <?php foreach ($newsList as $news): ?>
            <article class="article-card">
                <?php $img = !empty($news['featured_image']) ? $news['featured_image'] : 'assets/images/news/default.jpg'; ?>
                <div class="article-image" style="background-image: url('<?php echo $img; ?>'); background-size: cover; background-position: center;"></div>
                <div class="article-content">
                    <div class="article-meta">
                        <span class="article-category"><?php echo $news['category']; ?></span>
                        <span class="article-date">
                            <i class="fas fa-calendar-alt"></i>
                            <?php echo date('d/m/Y', strtotime($news['published_date'])); ?>
                        </span>
                    </div>
                    
                    <h3 class="article-title">
                        <a href="post.php?id=<?php echo $news['id']; ?>">
                            <?php echo htmlspecialchars($news['title']); ?>
                        </a>
                    </h3>
                    
                    <p class="article-excerpt">
                        <?php echo htmlspecialchars($news['excerpt']); ?>
                    </p>
                    
                    <a href="post.php?id=<?php echo $news['id']; ?>" class="read-more-btn">
                        Đọc tiếp <i class="fas fa-arrow-right"></i>
                    </a>
                </div>
            </article>
            <?php endforeach; ?>
        </div>

        <!-- Sidebar -->
        <div class="sidebar">
            <!-- Recent Posts Widget -->
            <div class="sidebar-widget">
                <h3 class="widget-title">
                    <i class="fas fa-clock"></i> Bài viết gần đây
                </h3>
                
                <?php 
                $recentNews = array_slice($newsList, 0, 4);
                foreach ($recentNews as $recent): 
                ?>
                <div class="recent-item">
                    <div class="recent-image">
                        📝
                    </div>
                    <div class="recent-content">
                        <div class="recent-title">
                            <a href="<?php echo buildLangUrl('article-detail'); ?>?slug=<?php echo $recent['slug']; ?>">
                                <?php echo htmlspecialchars($recent['title']); ?>
                            </a>
                        </div>
                        <div class="recent-meta">
                            <?php echo date('d/m/Y', strtotime($recent['published_date'])); ?>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>

            <!-- Stats Widget -->
            <div class="sidebar-widget">
                <h3 class="widget-title">
                    <i class="fas fa-chart-line"></i> Thống kê
                </h3>
                
                <div class="stats-grid">
                    <div class="stat-item">
                        <div class="stat-number"><?php echo count($categories); ?></div>
                        <div class="stat-label">Danh mục</div>
                    </div>
                </div>
            </div>

            <!-- Categories Widget -->
            <div class="sidebar-widget">
                <h3 class="widget-title">
                    <i class="fas fa-tags"></i> Danh mục
                </h3>
                
                <?php foreach ($categories as $cat): ?>
                <div style="margin-bottom: 0.5rem;">
                    <a href="news-modern.php?category=<?php echo urlencode($cat); ?>" 
                       style="color: var(--text-secondary); text-decoration: none; font-size: 0.9rem;">
                        <i class="fas fa-tag" style="margin-right: 0.5rem; color: var(--primary-color);"></i>
                        <?php echo htmlspecialchars($cat); ?>
                    </a>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</div>

<?php include 'inc/footer-new.php'; ?>