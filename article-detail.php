<?php 
require_once 'config.php';
require_once 'inc/url_helpers.php';
include 'inc/header-new.php';
require_once 'backend/inc/news_manager.php';

// Get article slug from URL
$slug = isset($_GET['slug']) ? $_GET['slug'] : '';

// Get current article (for demo, we'll use sample data)
$currentArticle = null;
$allNews = NewsManager::getSampleNews();

foreach ($allNews as $article) {
    if ($article['slug'] === $slug) {
        $currentArticle = $article;
        break;
    }
}

// If no article found, use first article as default
if (!$currentArticle) {
    $currentArticle = $allNews[0];
}

// Get related articles from same category
$relatedArticles = [];
foreach ($allNews as $article) {
    if ($article['category'] === $currentArticle['category'] && $article['slug'] !== $currentArticle['slug']) {
        $relatedArticles[] = $article;
    }
}

// Limit to 3 related articles
$relatedArticles = array_slice($relatedArticles, 0, 3);
?>

<style>
/* Article Detail Styles */
.article-hero {
    background: linear-gradient(135deg, #f0f9ff 0%, #e0f2fe 50%, #bae6fd 100%);
    padding: 4rem 0 2rem; /* Giảm khoảng cách để sát với header */
    position: relative;
    overflow: hidden;
    text-align: center;
}

.article-hero .hero-title {
    font-size: 2.75rem; /* Đồng bộ kích thước chữ */
    font-weight: bold;
    color: #1565c0;
}

.article-hero .hero-subtitle {
    font-size: 1.25rem; /* Đồng bộ kích thước chữ */
    color: #1976d2;
    margin-top: 1rem;
}

.article-container {
    max-width: 640px;
    margin: 0 auto;
    padding: 0 3rem;
    position: relative;
}

.breadcrumb {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    margin-bottom: 2rem;
    font-size: 0.9rem;
    color: #64748b;
}

.breadcrumb a {
    color: #3b82f6;
    text-decoration: none;
    transition: color 0.3s ease;
}

.breadcrumb a:hover {
    color: #8b5cf6;
}

.article-meta {
    display: flex;
    flex-wrap: wrap;
    gap: 1.5rem;
    margin-bottom: 2rem;
    font-size: 0.9rem;
    color: #64748b;
}

.article-category {
    background: linear-gradient(135deg, #3b82f6, #8b5cf6);
    color: white;
    padding: 0.4rem 1rem;
    border-radius: 25px;
    font-weight: 600;
    font-size: 0.85rem;
}

.article-title {
    font-size: 2.7rem;
    font-weight: 800;
    color: #1e293b;
    line-height: 1.2;
    margin-bottom: 1.5rem;
    text-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.article-excerpt {
    font-size: 1.17rem;
    color: #64748b;
    line-height: 1.6;
    margin-bottom: 2rem;
    font-weight: 400;
}

.author-info {
    display: flex;
    align-items: center;
    gap: 1rem;
    padding: 1.5rem;
    background: rgba(255,255,255,0.9);
    border-radius: 16px;
    backdrop-filter: blur(10px);
}

.author-avatar {
    width: 50px;
    height: 50px;
    border-radius: 50%;
    background: linear-gradient(135deg, #3b82f6, #8b5cf6);
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-weight: 600;
    font-size: 1.2rem;
}

.author-details h4 {
    color: #1e293b;
    margin-bottom: 0.2rem;
    font-weight: 600;
}

.author-details p {
    color: #64748b;
    font-size: 0.9rem;
    margin: 0;
}

/* Article Content */
.article-content {
    background: white;
    margin: -1rem auto 0;
    border-radius: 20px 20px 0 0;
    box-shadow: 0 -10px 30px rgba(0,0,0,0.1);
    position: relative;
    z-index: 10;
}

.article-body {
    padding: 2.5rem;
    max-width: none;
}

.article-body h2 {
    font-size: 1.62rem;
    font-weight: 700;
    color: #1e293b;
    margin: 2.5rem 0 1rem;
    padding-bottom: 0.5rem;
    border-bottom: 3px solid transparent;
    background: linear-gradient(135deg, #3b82f6, #8b5cf6);
    background-clip: text;
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
}

.article-body h3 {
    font-size: 1.26rem;
    font-weight: 600;
    color: #1e293b;
    margin: 2rem 0 1rem;
}

.article-body p {
    font-size: 0.99rem;
    line-height: 1.8;
    color: #374151;
    margin-bottom: 1.5rem;
    text-align: justify;
}

.article-body ul,
.article-body ol {
    margin: 1.5rem 0;
    padding-left: 2rem;
}

.article-body li {
    font-size: 0.99rem;
    line-height: 1.7;
    color: #374151;
    margin-bottom: 0.5rem;
}

.article-body blockquote {
    border-left: 4px solid #3b82f6;
    background: #f8fafc;
    padding: 1.5rem 2rem;
    margin: 2rem 0;
    border-radius: 0 12px 12px 0;
    font-style: italic;
    color: #64748b;
    position: relative;
}

.article-body blockquote::before {
    content: '"';
    position: absolute;
    top: -10px;
    left: 15px;
    font-size: 3rem;
    color: #3b82f6;
    font-family: 'Times New Roman', Georgia, serif;
}

.highlight-box {
    background: linear-gradient(135deg, #f0f9ff 0%, #e0f2fe 100%);
    border: 1px solid #bae6fd;
    border-radius: 16px;
    padding: 2rem;
    margin: 2rem 0;
    position: relative;
}

.highlight-box::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    width: 4px;
    height: 100%;
    background: linear-gradient(135deg, #3b82f6, #8b5cf6);
    border-radius: 2px 0 0 2px;
}

.highlight-box h4 {
    color: #1e293b;
    margin-bottom: 1rem;
    font-weight: 600;
}

/* Image Styles */
.article-image {
    width: 100%;
    border-radius: 12px;
    margin: 2rem 0;
    box-shadow: 0 8px 25px rgba(0,0,0,0.15);
    transition: transform 0.3s ease;
}

.article-image:hover {
    transform: scale(1.02);
}

.image-caption {
    text-align: center;
    font-size: 0.9rem;
    color: #64748b;
    margin-top: 0.5rem;
    font-style: italic;
}

/* Tags and Social */
.article-footer {
    padding: 2rem 3rem 3rem;
    border-top: 1px solid #e2e8f0;
}

.article-tags {
    margin-bottom: 2rem;
}

.article-tags h4 {
    color: #1e293b;
    margin-bottom: 1rem;
    font-weight: 600;
}

.tag-list {
    display: flex;
    flex-wrap: wrap;
    gap: 0.5rem;
}

.tag {
    background: #f1f5f9;
    color: #3b82f6;
    padding: 0.3rem 0.8rem;
    border-radius: 20px;
    font-size: 0.85rem;
    font-weight: 500;
    text-decoration: none;
    transition: all 0.3s ease;
}

.tag:hover {
    background: linear-gradient(135deg, #3b82f6, #8b5cf6);
    color: white;
    transform: translateY(-1px);
}

.social-share {
    text-align: center;
}

.social-share h4 {
    color: #1e293b;
    margin-bottom: 1rem;
    font-weight: 600;
}

.share-buttons {
    display: flex;
    justify-content: center;
    gap: 1rem;
}

.share-btn {
    width: 50px;
    height: 50px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    text-decoration: none;
    color: white;
    font-size: 1.2rem;
    transition: all 0.3s ease;
}

.share-btn.facebook { background: #1877f2; }
.share-btn.twitter { background: #1da1f2; }
.share-btn.linkedin { background: #0a66c2; }
.share-btn.email { background: #ea4335; }

.share-btn:hover {
    transform: translateY(-3px) scale(1.1);
    box-shadow: 0 8px 25px rgba(0,0,0,0.2);
}

/* Related Articles */
.related-articles {
    background: #f8fafc;
    padding: 3rem 0;
}

.related-container {
    max-width: 1200px;
    margin: 0 auto;
    padding: 0 2rem;
}

.related-title {
    text-align: center;
    font-size: 2rem;
    font-weight: 700;
    color: #1e293b;
    margin-bottom: 2rem;
}

.related-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap: 2rem;
}

.related-card {
    background: white;
    border-radius: 16px;
    overflow: hidden;
    box-shadow: 0 4px 20px rgba(0,0,0,0.08);
    transition: all 0.3s ease;
    text-decoration: none;
    color: inherit;
    display: block;
}

.related-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 8px 30px rgba(0,0,0,0.15);
}

.related-image {
    height: 150px;
    background: linear-gradient(45deg, #f1f5f9, #e2e8f0);
}

.related-content {
    padding: 1.5rem;
}

.related-meta {
    font-size: 0.85rem;
    color: #64748b;
    margin-bottom: 0.5rem;
}

.related-card-title {
    font-size: 1.1rem;
    font-weight: 600;
    color: #1e293b;
    margin-bottom: 0.8rem;
    line-height: 1.4;
}

.related-excerpt {
    color: #64748b;
    font-size: 0.95rem;
    line-height: 1.6;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}

/* Responsive */
@media (max-width: 768px) {
    .article-title {
        font-size: 1.8rem;
    }
    
    .article-container,
    .article-body,
    .article-footer {
        padding-left: 1.5rem;
        padding-right: 1.5rem;
    }
    
    .article-meta {
        flex-direction: column;
        gap: 1rem;
    }
    
    .author-info {
        flex-direction: column;
        text-align: center;
    }
    
    .share-buttons {
        flex-wrap: wrap;
    }
    
    .related-grid {
        grid-template-columns: 1fr;
    }
    
    /* Force lists to display in 1 column on mobile */
    .article-body ul,
    .article-body ol,
    .highlight-box ul,
    .highlight-box ol {
        columns: 1 !important;
        column-count: 1 !important;
        -webkit-columns: 1 !important;
        -moz-columns: 1 !important;
    }
}

@media (max-width: 480px) {
    .article-hero {
        padding: 4rem 0 2rem;
    }
    
    .article-title {
        font-size: 1.8rem;
    }
    
    .article-excerpt {
        font-size: 1.1rem;
    }
    
    .article-container,
    .article-body,
    .article-footer {
        padding-left: 1rem;
        padding-right: 1rem;
    }
    
    /* Ensure lists display in 1 column on small mobile */
    .article-body ul,
    .article-body ol,
    .highlight-box ul,
    .highlight-box ol {
        columns: 1 !important;
        column-count: 1 !important;
        -webkit-columns: 1 !important;
        -moz-columns: 1 !important;
        padding-left: 1.5rem;
    }
    
    .article-body li {
        width: 100%;
        display: block;
    }
}
</style>

<!-- Article Hero -->
<section class="article-hero">
    <div class="article-container">
        <!-- Breadcrumb -->
        <nav class="breadcrumb">
            <a href="/vnmt/">Trang chủ</a>
            <i class="fas fa-chevron-right"></i>
            <a href="/vnmt/news-modern.php">Tin tức</a>
            <i class="fas fa-chevron-right"></i>
            <span>Công Nghệ AI Trong Sản Xuất Vật Liệu</span>
        </nav>

        <!-- Article Meta -->
        <div class="article-meta">
            <span class="article-category"><?php echo htmlspecialchars($currentArticle['category']); ?></span>
            <span><i class="fas fa-calendar"></i> <?php echo date('d/m/Y', strtotime($currentArticle['published_date'])); ?></span>
            <span><i class="fas fa-clock"></i> <?php echo $currentArticle['reading_time']; ?> phút đọc</span>
            <span><i class="fas fa-eye"></i> <?php echo number_format($currentArticle['views'] ?? 1234); ?> lượt xem</span>
        </div>

        <!-- Article Title -->
        <h1 class="article-title"><?php echo htmlspecialchars($currentArticle['title']); ?></h1>

        <!-- Article Excerpt -->
        <p class="article-excerpt">
            <?php echo htmlspecialchars($currentArticle['excerpt']); ?>
        </p>

        <!-- Author Info -->
        <div class="author-info">
            <div class="author-avatar">ND</div>
            <div class="author-details">
                <h4>Nguyễn Đức Anh</h4>
                <p>Chuyên gia Công nghệ Vật liệu • 8 năm kinh nghiệm</p>
            </div>
        </div>
    </div>
</section>

<!-- Article Content -->
<article class="article-content">
    <div class="article-body">
        <!-- Featured Image -->
        <img src="assets/images/news/ai-construction.jpg" alt="AI trong xây dựng" class="article-image"
             onerror="this.style.display='none'">
        <p class="image-caption">Công nghệ AI đang thay đổi cách chúng ta sản xuất vật liệu xây dựng</p>

        <!-- Article Content -->
        <p>
            <strong>Trí tuệ nhân tạo (AI)</strong> không còn là khái niệm xa lạ trong thời đại số hóa hiện nay. 
            Từ y tế đến giáo dục, từ giao thông đến sản xuất, AI đang từng bước thay đổi cách chúng ta làm việc 
            và sinh hoạt. Trong ngành vật liệu xây dựng, những ứng dụng AI đang mở ra những khả năng mới 
            chưa từng có, hứa hẹn một cuộc cách mạng toàn diện.
        </p>

        <h2>1. AI Trong Tối Ưu Hóa Quy Trình Sản Xuất</h2>

        <p>
            Một trong những ứng dụng quan trọng nhất của AI trong sản xuất vật liệu xây dựng là 
            <em>tối ưu hóa quy trình sản xuất</em>. Thông qua việc phân tích big data từ các cảm biến 
            IoT được đặt khắp dây chuyền sản xuất, AI có thể:
        </p>

        <ul>
            <li><strong>Dự đoán và phòng ngừa hỏng hóc thiết bị</strong> - Giảm thời gian chết máy xuống 30-40%</li>
            <li><strong>Tối ưu hóa điều kiện sản xuất</strong> - Nhiệt độ, độ ẩm, áp suất được điều chỉnh real-time</li>
            <li><strong>Kiểm soát chất lượng tự động</strong> - Phát hiện lỗi sản phẩm với độ chính xác 99.5%</li>
            <li><strong>Quản lý nguyên liệu thông minh</strong> - Giảm lãng phí nguyên liệu 15-25%</li>
        </ul>

        <div class="highlight-box">
            <h4><i class="fas fa-lightbulb"></i> Thực tế từ nhà máy xi măng Holcim</h4>
            <p>
                Tập đoàn Holcim đã triển khai hệ thống AI tại nhà máy xi măng ở Thụy Sĩ, 
                kết quả cho thấy năng suất tăng 12%, giảm tiêu thụ năng lượng 8% và 
                cải thiện chất lượng sản phẩm đáng kể chỉ trong 6 tháng đầu.
            </p>
        </div>

        <h2>2. Phát Triển Vật Liệu Thông Minh Mới</h2>

        <p>
            AI không chỉ cải thiện quy trình sản xuất mà còn đóng vai trò quan trọng trong 
            <strong>nghiên cứu và phát triển vật liệu mới</strong>. Machine Learning giúp các nhà khoa học:
        </p>

        <img src="assets/images/news/smart-materials.jpg" alt="Vật liệu thông minh" class="article-image"
             onerror="this.style.display='none'">
        <p class="image-caption">Vật liệu tự phục hồi được phát triển nhờ công nghệ AI</p>

        <h3>2.1. Vật Liệu Tự Phục Hồi (Self-Healing Materials)</h3>

        <p>
            Thông qua mô phỏng phân tử và deep learning, các nhà nghiên cứu đã phát triển 
            thành công các loại bê tông có khả năng <em>tự phục hồi vết nứt</em>. Khi xuất hiện 
            vết nứt nhỏ, các vi sinh vật được nhúng trong bê tông sẽ được kích hoạt và 
            tiết ra carbonate canxi để "hàn gắn" vết nứt.
        </p>

        <h3>2.2. Vật Liệu Thích Ứng Môi Trường</h3>

        <p>
            AI giúp tạo ra các vật liệu có thể thay đổi tính chất dựa trên điều kiện môi trường:
        </p>

        <blockquote>
            "Chúng tôi đang phát triển loại sơn có thể tự động thay đổi màu sắc để phản xạ 
            nhiều hơn vào mùa hè và hấp thụ nhiều hơn vào mùa đông, góp phần tiết kiệm 
            năng lượng cho tòa nhà lên đến 20%." - Tiến sĩ Maria Rodriguez, MIT
        </blockquote>

        <h2>3. Tác Động Đến Môi Trường và Tính Bền Vững</h2>

        <p>
            Một trong những lợi ích lớn nhất của việc ứng dụng AI trong sản xuất vật liệu 
            xây dựng là <strong>giảm thiểu tác động môi trường</strong>:
        </p>

        <div class="highlight-box">
            <h4><i class="fas fa-leaf"></i> Những con số ấn tượng</h4>
            <ul>
                <li>Giảm 25% lượng CO2 phát thải trong sản xuất xi măng</li>
                <li>Tăng 40% hiệu quả sử dụng nguyên liệu tái chế</li>
                <li>Giảm 30% lượng nước sử dụng trong quá trình sản xuất</li>
                <li>Tăng tuổi thọ sản phẩm lên 50-70%</li>
            </ul>
        </div>

        <h2>4. Thách Thức và Giải Pháp</h2>

        <p>
            Mặc dù mang lại nhiều lợi ích, việc ứng dụng AI trong sản xuất vật liệu xây dựng 
            cũng đối mặt với không ít thách thức:
        </p>

        <h3>4.1. Chi Phí Đầu Tư Ban Đầu</h3>
        <p>
            Đầu tư hệ thống AI và IoT đòi hỏi chi phí lớn, đặc biệt đối với các doanh nghiệp 
            vừa và nhỏ. Tuy nhiên, ROI thường đạt được trong vòng 18-24 tháng.
        </p>

        <h3>4.2. Thiếu Nhân Lực Chuyên Môn</h3>
        <p>
            Việt Nam cần đầu tư mạnh vào đào tạo nhân lực có khả năng vận hành và bảo trì 
            các hệ thống AI trong sản xuất.
        </p>

        <h2>5. Triển Vọng Tương Lai</h2>

        <p>
            Nhìn về tương lai, AI sẽ tiếp tục đóng vai trò then chốt trong việc định hình 
            ngành vật liệu xây dựng. Các xu hướng đáng chú ý bao gồm:
        </p>

        <ul>
            <li><strong>Digital Twin Technology</strong> - Mô phỏng số toàn bộ nhà máy sản xuất</li>
            <li><strong>Blockchain + AI</strong> - Truy xuất nguồn gốc và chất lượng vật liệu</li>
            <li><strong>Edge AI</strong> - Xử lý dữ liệu real-time ngay tại thiết bị sản xuất</li>
            <li><strong>Collaborative Robots</strong> - Robot thông minh làm việc cùng con người</li>
        </ul>

        <img src="assets/images/news/future-construction.jpg" alt="Tương lai xây dựng" class="article-image"
             onerror="this.style.display='none'">
        <p class="image-caption">Tương lai của ngành xây dựng với công nghệ AI và robot</p>

        <h2>Kết Luận</h2>

        <p>
            Công nghệ AI trong sản xuất vật liệu xây dựng không còn là viễn cảnh xa vời mà 
            đã trở thành hiện thực. Từ việc tối ưu hóa quy trình sản xuất, phát triển vật liệu 
            mới đến bảo vệ môi trường, AI đang mở ra những cơ hội to lớn cho ngành này.
        </p>

        <p>
            <strong>Những doanh nghiệp nào biết nắm bắt và ứng dụng sớm các công nghệ này 
            sẽ có lợi thế cạnh tranh vượt trội trong thời đại công nghệ 4.0.</strong>
        </p>
    </div>

    <!-- Article Footer -->
    <div class="article-footer">
        <!-- Tags -->
        <div class="article-tags">
            <h4>Thẻ liên quan:</h4>
            <div class="tag-list">
                <a href="#" class="tag">AI</a>
                <a href="#" class="tag">Trí tuệ nhân tạo</a>
                <a href="#" class="tag">Vật liệu xây dựng</a>
                <a href="#" class="tag">Công nghệ 4.0</a>
                <a href="#" class="tag">Bền vững</a>
                <a href="#" class="tag">IoT</a>
                <a href="#" class="tag">Machine Learning</a>
            </div>
        </div>

        <!-- Social Share -->
        <div class="social-share">
            <h4>Chia sẻ bài viết:</h4>
            <div class="share-buttons">
                <a href="#" class="share-btn facebook" title="Chia sẻ trên Facebook">
                    <i class="fab fa-facebook-f"></i>
                </a>
                <a href="#" class="share-btn twitter" title="Chia sẻ trên Twitter">
                    <i class="fab fa-twitter"></i>
                </a>
                <a href="#" class="share-btn linkedin" title="Chia sẻ trên LinkedIn">
                    <i class="fab fa-linkedin-in"></i>
                </a>
                <a href="#" class="share-btn email" title="Chia sẻ qua Email">
                    <i class="fas fa-envelope"></i>
                </a>
            </div>
        </div>
    </div>
</article>

<!-- Related Articles -->
<section class="related-articles">
    <div class="related-container">
        <h2 class="related-title">Bài Viết Liên Quan</h2>
        <div class="related-grid">
            <?php if (empty($relatedArticles)): ?>
                <p style="text-align: center; color: #64748b; grid-column: 1 / -1;">
                    Không có bài viết liên quan trong danh mục "<?php echo htmlspecialchars($currentArticle['category']); ?>"
                </p>
            <?php else: ?>
                <?php foreach ($relatedArticles as $index => $related): ?>
                    <?php 
                    // Different gradient colors for each card
                    $gradients = [
                        'linear-gradient(45deg, #3b82f6, #8b5cf6)',
                        'linear-gradient(45deg, #06b6d4, #3b82f6)', 
                        'linear-gradient(45deg, #10b981, #06b6d4)'
                    ];
                    $gradient = $gradients[$index % 3];
                    ?>
                    <a href="<?php echo buildArticleUrl($related); ?>" class="related-card">
                        <div class="related-image" style="background: <?php echo $gradient; ?>;"></div>
                        <div class="related-content">
                            <div class="related-meta">
                                <?php echo htmlspecialchars($related['category']); ?> • 
                                <?php echo date('d/m/Y', strtotime($related['published_date'])); ?>
                            </div>
                            <h3 class="related-card-title"><?php echo htmlspecialchars($related['title']); ?></h3>
                            <p class="related-excerpt">
                                <?php echo htmlspecialchars($related['excerpt']); ?>
                            </p>
                        </div>
                    </a>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</section>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Smooth scroll for internal links
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
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

    // Reading progress bar (using existing progress bar)
    const progressBar = document.getElementById('progressBar');
    if (progressBar) {
        window.addEventListener('scroll', function() {
            const article = document.querySelector('.article-body');
            if (article) {
                const articleTop = article.offsetTop;
                const articleHeight = article.offsetHeight;
                const scrolled = window.pageYOffset - articleTop;
                const progress = Math.min(Math.max(scrolled / articleHeight, 0), 1) * 100;
                
                if (scrolled > 0) {
                    progressBar.style.width = progress + '%';
                } else {
                    progressBar.style.width = '0%';
                }
            }
        });
    }

    // Social share functionality
    document.querySelectorAll('.share-btn').forEach(btn => {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            const url = encodeURIComponent(window.location.href);
            const title = encodeURIComponent(document.title);
            const text = encodeURIComponent(document.querySelector('.article-excerpt').textContent);
            
            let shareUrl = '';
            
            if (this.classList.contains('facebook')) {
                shareUrl = `https://www.facebook.com/sharer/sharer.php?u=${url}`;
            } else if (this.classList.contains('twitter')) {
                shareUrl = `https://twitter.com/intent/tweet?url=${url}&text=${title}`;
            } else if (this.classList.contains('linkedin')) {
                shareUrl = `https://www.linkedin.com/sharing/share-offsite/?url=${url}`;
            } else if (this.classList.contains('email')) {
                shareUrl = `mailto:?subject=${title}&body=${text}%0A%0A${url}`;
            }
            
            if (shareUrl) {
                window.open(shareUrl, '_blank', 'width=600,height=400');
            }
        });
    });

    // Animate elements on scroll
    const observerOptions = {
        threshold: 0.1,
        rootMargin: '0px 0px -50px 0px'
    };
    
    const observer = new IntersectionObserver(function(entries) {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.style.opacity = '1';
                entry.target.style.transform = 'translateY(0)';
            }
        });
    }, observerOptions);
    
    // Observe highlight boxes and images
    document.querySelectorAll('.highlight-box, .article-image, .related-card').forEach(el => {
        el.style.opacity = '0';
        el.style.transform = 'translateY(20px)';
        el.style.transition = 'opacity 0.6s ease, transform 0.6s ease';
        observer.observe(el);
    });
});
</script>

<?php include 'inc/footer-new.php'; ?>