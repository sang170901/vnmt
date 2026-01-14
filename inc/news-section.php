<?php
/**
 * News Section Component - Carousel Style
 * Hiển thị tin tức trong carousel với fade effect
 */

require_once __DIR__ . '/../lang/lang.php';
require_once __DIR__ . '/../lang/db_translate_helper.php';
require_once __DIR__ . '/url_helpers.php';

// Lấy dữ liệu tin tức từ database
try {
    require_once __DIR__ . '/../backend/inc/db.php';
    $pdo = getPDO();
    
    // Lấy posts thay vì news
    $stmt = $pdo->prepare("SELECT *, title_en, excerpt_en FROM posts WHERE status = 'published' ORDER BY published_at DESC LIMIT 10");
    $stmt->execute();
    $newsList = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
} catch (Exception $e) {
    $newsList = [];
    error_log("Error fetching news: " . $e->getMessage());
}
?>

<style>
/* News Section - Carousel Style */
.news-section {
    padding: 80px 0;
    background: #ffffff;
    position: relative;
    overflow: hidden;
}

.news-container {
    max-width: 1600px;
    margin: 0 auto;
    position: relative;
}

.news-header {
    text-align: center;
    margin-bottom: 60px;
    padding: 0 20px;
}

.news-header-icon {
    width: 80px;
    height: 80px;
    margin: 0 auto 20px;
    background: linear-gradient(135deg, #e0f2fe 0%, #bae6fd 100%);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    box-shadow: 0 10px 30px rgba(56, 189, 248, 0.2);
}

.news-header-icon i {
    font-size: 36px;
    color: #38bdf8;
}

.news-header h2 {
    font-size: 2.8rem;
    font-weight: 800;
    color: #1e293b;
    margin-bottom: 16px;
}

.news-header p {
    font-size: 1.2rem;
    color: #64748b;
    max-width: 600px;
    margin: 0 auto;
}

/* Carousel Container */
.news-carousel-wrapper {
    position: relative;
    padding: 0 80px;
}

.news-carousel {
    overflow-x: auto;
    overflow-y: hidden;
    scroll-behavior: smooth;
    display: flex;
    gap: 24px;
    padding: 20px 40px;
    -ms-overflow-style: none;
    scrollbar-width: none;
    position: relative;
}

.news-carousel::-webkit-scrollbar {
    display: none;
}

/* News Item */
.news-item {
    flex: 0 0 calc((100% - 96px) / 5);
    min-width: 280px;
    max-width: 350px;
    background: white;
    border-radius: 16px;
    overflow: hidden;
    box-shadow: 0 4px 20px rgba(0,0,0,0.08);
    transition: all 0.3s ease;
    display: flex;
    flex-direction: column;
    cursor: pointer;
    text-decoration: none;
}

.news-item:hover {
    transform: translateY(-10px) scale(1.02);
    box-shadow: 0 12px 40px rgba(56, 189, 248, 0.2);
}

.news-image {
    width: 100%;
    height: 200px;
    background: linear-gradient(135deg, #e0f2fe 0%, #bae6fd 100%);
    position: relative;
    overflow: hidden;
    display: flex;
    align-items: center;
    justify-content: center;
}

.news-image img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    position: relative;
    z-index: 1;
}

.news-image::after {
    content: '📰';
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    font-size: 4rem;
    opacity: 0.2;
    z-index: 0;
}

.news-image:has(img) ::after {
    display: none;
}

.news-category {
    position: absolute;
    top: 12px;
    left: 12px;
    background: linear-gradient(135deg, #38bdf8 0%, #22d3ee 100%);
    color: white;
    padding: 6px 14px;
    border-radius: 20px;
    font-size: 0.7rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    z-index: 2;
}

.news-content {
    padding: 20px;
    flex: 1;
    display: flex;
    flex-direction: column;
}

.news-item h3 {
    font-size: 1.1rem;
    margin-bottom: 10px;
    line-height: 1.4;
    color: #1e293b;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
    transition: color 0.3s;
    text-decoration: none;
}

.news-item:hover h3 {
    color: #38bdf8;
    text-decoration: none;
}

.news-date {
    font-size: 0.85rem;
    color: #94a3b8;
    margin-bottom: 12px;
    display: flex;
    align-items: center;
    gap: 6px;
}

.news-date i {
    color: #38bdf8;
}

.news-description {
    font-size: 0.9rem;
    color: #64748b;
    line-height: 1.6;
    margin-bottom: 16px;
    flex: 1;
    display: -webkit-box;
    -webkit-line-clamp: 3;
    -webkit-box-orient: vertical;
    overflow: hidden;
}

.news-read-more {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    color: #38bdf8;
    font-weight: 600;
    text-decoration: none;
    font-size: 0.9rem;
    transition: all 0.3s;
}

.news-read-more:hover {
    gap: 12px;
    color: #0ea5e9;
}

/* Navigation Buttons */
.carousel-nav {
    position: absolute;
    top: 50%;
    transform: translateY(-50%);
    width: 50px;
    height: 50px;
    background: white;
    border: none;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    box-shadow: 0 4px 20px rgba(0,0,0,0.15);
    transition: all 0.3s;
    z-index: 10;
}

.carousel-nav:hover {
    background: #38bdf8;
    color: white;
    transform: translateY(-50%) scale(1.1);
}

.carousel-nav.prev {
    left: 10px;
}

.carousel-nav.next {
    right: 10px;
}

.carousel-nav i {
    font-size: 20px;
}

/* View All Button */
.news-more {
    text-align: center;
    margin-top: 50px;
}

.news-more-btn {
    display: inline-flex;
    align-items: center;
    gap: 12px;
    padding: 16px 40px;
    background: linear-gradient(135deg, #38bdf8 0%, #22d3ee 100%);
    color: white;
    text-decoration: none;
    border-radius: 50px;
    font-weight: 700;
    font-size: 1.05rem;
    transition: all 0.3s;
    box-shadow: 0 8px 25px rgba(56, 189, 248, 0.3);
}

.news-more-btn:hover {
    transform: translateY(-3px);
    box-shadow: 0 12px 40px rgba(56, 189, 248, 0.4);
}

/* Responsive */
@media (max-width: 1400px) {
    .news-item {
        flex: 0 0 calc((100% - 72px) / 4);
    }
}

@media (max-width: 1024px) {
    .news-item {
        flex: 0 0 calc((100% - 48px) / 3);
    }
    
    .news-carousel-wrapper {
        padding: 0 60px;
    }
}

@media (max-width: 768px) {
    .news-section {
        padding: 60px 0;
    }
    
    .news-header h2 {
        font-size: 2.2rem;
    }
    
    .news-item {
        flex: 0 0 calc((100% - 24px) / 2);
        min-width: 240px;
    }
    
    .news-carousel-wrapper {
        padding: 0 50px;
    }
    
    .carousel-nav {
        width: 40px;
        height: 40px;
    }
}

@media (max-width: 480px) {
    .news-header h2 {
        font-size: 1.8rem;
    }
    
    .news-item {
        flex: 0 0 85%;
        min-width: 260px;
    }
    
    .news-carousel {
        gap: 16px;
        padding: 20px;
    }
    
    .news-carousel-wrapper {
        padding: 0 40px;
    }
}
</style>

<section class="news-section">
    <div class="news-container">
        <div class="news-header">
            <div class="news-header-icon">
                <i class="fas fa-newspaper"></i>
            </div>
            <h2><?php echo t('news_section_title'); ?></h2>
            <p><?php echo t('news_section_subtitle'); ?></p>
        </div>
        
        <?php if (!empty($newsList)): ?>
            <div class="news-carousel-wrapper">
                <button class="carousel-nav prev" onclick="scrollCarousel('prev')">
                    <i class="fas fa-chevron-left"></i>
                </button>
                
                <div class="news-carousel" id="newsCarousel">
                    <?php foreach ($newsList as $news): ?>
                    <a href="<?php echo buildArticleUrl($news); ?>" class="news-item">
                        <div class="news-image">
                            <?php if (!empty($news['featured_image'])): ?>
                                <img src="<?php echo htmlspecialchars($news['featured_image']); ?>" 
                                     alt="<?php echo htmlspecialchars(getTranslatedTitle($news)); ?>"
                                     loading="lazy">
                            <?php endif; ?>
                            <?php if (!empty($news['category'])): ?>
                                <div class="news-category"><?php echo htmlspecialchars($news['category']); ?></div>
                            <?php endif; ?>
                        </div>
                        
                        <div class="news-content">
                            <h3><?php echo htmlspecialchars(getTranslatedTitle($news)); ?></h3>
                            
                            <p class="news-date">
                                <i class="far fa-calendar-alt"></i>
                                <?php echo date('d/m/Y', strtotime($news['published_at'])); ?>
                            </p>
                            
                            <p class="news-description">
                                <?php echo htmlspecialchars(getTranslatedExcerpt($news)); ?>
                            </p>
                            
                            <span class="news-read-more">
                                <span><?php echo t('read_more'); ?></span>
                                <i class="fas fa-arrow-right"></i>
                            </span>
                        </div>
                    </a>
                    <?php endforeach; ?>
                </div>
                
                <button class="carousel-nav next" onclick="scrollCarousel('next')">
                    <i class="fas fa-chevron-right"></i>
                </button>
            </div>
            
            <div class="news-more">
                <a href="<?php echo buildLangUrl('news-modern.php'); ?>" class="news-more-btn">
                    <span><?php echo t('view_all_news'); ?></span>
                    <i class="fas fa-arrow-right"></i>
                </a>
            </div>
        <?php else: ?>
            <div style="text-align:center;padding:60px 20px;color:#64748b;">
                <p style="font-size:1.1rem;"><?php echo t('no_news_available'); ?></p>
            </div>
        <?php endif; ?>
    </div>
</section>

<script>
function scrollCarousel(direction) {
    const carousel = document.getElementById('newsCarousel');
    const scrollAmount = carousel.offsetWidth * 0.6;
    
    if (direction === 'next') {
        carousel.scrollBy({ left: scrollAmount, behavior: 'smooth' });
    } else {
        carousel.scrollBy({ left: -scrollAmount, behavior: 'smooth' });
    }
}

// Auto-scroll support with mouse drag
let isDown = false;
let startX;
let scrollLeft;

const carousel = document.getElementById('newsCarousel');

carousel.addEventListener('mousedown', (e) => {
    isDown = true;
    carousel.style.cursor = 'grabbing';
    startX = e.pageX - carousel.offsetLeft;
    scrollLeft = carousel.scrollLeft;
});

carousel.addEventListener('mouseleave', () => {
    isDown = false;
    carousel.style.cursor = 'grab';
});

carousel.addEventListener('mouseup', () => {
    isDown = false;
    carousel.style.cursor = 'grab';
});

carousel.addEventListener('mousemove', (e) => {
    if (!isDown) return;
    e.preventDefault();
    const x = e.pageX - carousel.offsetLeft;
    const walk = (x - startX) * 2;
    carousel.scrollLeft = scrollLeft - walk;
});

// Set cursor style
carousel.style.cursor = 'grab';
</script>
