<?php
session_start();
header('Content-Type: text/html; charset=UTF-8');

require_once 'config.php';
// Include translation system
require_once 'lang/lang.php';
require_once 'lang/db_translate_helper.php';
require_once 'inc/db_frontend.php';
require_once 'inc/url_helpers.php';

// Kiểm tra đăng nhập
$isLoggedIn = isset($_SESSION['user_id']);
$userData = null;

if ($isLoggedIn) {
    // Lấy thông tin user từ database
    $pdo = getFrontendPDO();
    $stmt = $pdo->prepare('SELECT * FROM users WHERE id = ?');
    $stmt->execute([$_SESSION['user_id']]);
    $userData = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$userData) {
        // User không tồn tại, logout
        session_destroy();
        $isLoggedIn = false;
    }
}

$slug = $_GET['slug'] ?? '';
$id = $_GET['id'] ?? 0;
$pdo = getFrontendPDO();

// Lấy bài viết (include English columns)
if ($id) {
    // Nếu có id, query theo id
    $stmt = $pdo->prepare('SELECT *, title_en, excerpt_en, content_en FROM posts WHERE id = ? AND status = "published"');
    $stmt->execute([$id]);
} else {
    // Nếu không có id, query theo slug (backward compatibility)
    $stmt = $pdo->prepare('SELECT *, title_en, excerpt_en, content_en FROM posts WHERE slug = ? AND status = "published"');
    $stmt->execute([$slug]);
}
$post = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$post) {
    header('Location: ' . buildLangUrl('news-modern.php'));
    exit;
}

// Tăng lượt xem
$pdo->prepare('UPDATE posts SET views = views + 1 WHERE id = ?')->execute([$post['id']]);

// Xử lý submit comment
$commentSubmitted = false;
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_comment'])) {
    $content = trim($_POST['content'] ?? '');
    $userId = $isLoggedIn ? $_SESSION['user_id'] : null;
    
    // Nếu đã đăng nhập, lấy thông tin từ session
    if ($isLoggedIn) {
        $name = $userData['full_name'];
        $email = $userData['email'];
        $website = '';
    } else {
        $name = trim($_POST['name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $website = trim($_POST['website'] ?? '');
    }
    
    if (!empty($name) && !empty($email) && !empty($content) && filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $stmt = $pdo->prepare('INSERT INTO comments (post_id, user_id, author_name, author_email, content, status) VALUES (?, ?, ?, ?, ?, ?)');
        $stmt->execute([
            $post['id'],
            $userId,
            $name,
            $email,
            $content,
            'approved' // Mặc định đã duyệt - hiển thị ngay
        ]);
        
        // Redirect để tránh double submit và hiển thị comment ngay
        header('Location: ' . $_SERVER['REQUEST_URI'] . '#comments');
        exit;
    }
}

// Lấy comments đã duyệt (join với users để lấy avatar)
$stmt = $pdo->prepare('
    SELECT c.*, u.avatar, u.username 
    FROM comments c
    LEFT JOIN users u ON c.user_id = u.id
    WHERE c.post_id = ? AND c.status = "approved" 
    ORDER BY c.created_at DESC
');
$stmt->execute([$post['id']]);
$comments = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Lấy 3 bài viết liên quan (cùng category) - include English columns
$relatedPosts = [];
if (!empty($post['category'])) {
    $stmt = $pdo->prepare('SELECT *, title_en, excerpt_en FROM posts WHERE category = ? AND id != ? AND status = "published" ORDER BY published_at DESC LIMIT 3');
    $stmt->execute([$post['category'], $post['id']]);
    $relatedPosts = $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Lấy 3 bài viết nổi bật (most viewed) - include English columns
$stmt = $pdo->prepare('SELECT *, title_en, excerpt_en FROM posts WHERE id != ? AND status = "published" ORDER BY views DESC LIMIT 3');
$stmt->execute([$post['id']]);
$featuredPosts = $stmt->fetchAll(PDO::FETCH_ASSOC);

include 'inc/header-new.php';
?>

<style>
/* Two Column Layout - v2 */
.content-wrapper {
    max-width: 100% !important;
    width: 100% !important;
    margin: 40px 0 !important;
    padding: 0 40px !important;
    display: flex !important;
    gap: 2% !important;
    align-items: flex-start !important;
}

.post-main-column {
    flex: 0 0 68% !important;
    width: 68% !important;
    max-width: 68% !important;
}

.sidebar {
    flex: 0 0 30% !important;
    width: 30% !important;
    max-width: 30% !important;
    position: sticky !important;
    top: 100px !important;
}

.post-detail {
    background: white;
    padding: 40px;
    border-radius: 16px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.08);
}

.post-header {
    margin-bottom: 40px;
}

.post-title {
    font-size: 2.5rem;
    font-weight: 800;
    color: #1e293b;
    margin-bottom: 16px;
    line-height: 1.2;
}

.post-meta {
    display: flex;
    gap: 24px;
    color: #64748b;
    font-size: 14px;
    margin-bottom: 24px;
}

.post-meta-item {
    display: flex;
    align-items: center;
    gap: 8px;
}

.post-featured-image {
    width: 100%;
    height: 400px;
    object-fit: cover;
    border-radius: 12px;
    margin-bottom: 32px;
}

.post-content {
    font-size: 1.1rem;
    line-height: 1.8;
    color: #334155;
    margin-bottom: 48px;
}

.post-content h1, .post-content h2, .post-content h3 {
    margin-top: 32px;
    margin-bottom: 16px;
    color: #1e293b;
}

.post-content ul, .post-content ol {
    margin: 16px 0;
    padding-left: 32px;
}

.post-content li {
    margin: 8px 0;
}

.post-content img {
    max-width: 100%;
    border-radius: 8px;
    margin: 24px 0;
}

/* Comments Section */
.comments-section {
    margin-top: 64px;
    padding-top: 40px;
    border-top: 2px solid #e2e8f0;
}

.comments-title {
    font-size: 1.8rem;
    font-weight: 700;
    color: #1e293b;
    margin-bottom: 24px;
}

.comment-form {
    background: #f8fafc;
    padding: 32px;
    border-radius: 12px;
    margin-bottom: 40px;
}

.comment-form-title {
    font-size: 1.3rem;
    font-weight: 600;
    margin-bottom: 20px;
    color: #1e293b;
}

.form-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 16px;
    margin-bottom: 16px;
}

.form-group {
    margin-bottom: 16px;
}

.form-group label {
    display: block;
    font-weight: 600;
    margin-bottom: 8px;
    color: #475569;
}

.form-group input,
.form-group textarea {
    width: 100%;
    padding: 12px;
    border: 2px solid #e2e8f0;
    border-radius: 8px;
    font-size: 14px;
}

.form-group textarea {
    resize: vertical;
    min-height: 120px;
}

.submit-btn {
    background: linear-gradient(135deg, #38bdf8 0%, #22d3ee 100%);
    color: white;
    padding: 12px 32px;
    border: none;
    border-radius: 8px;
    font-size: 16px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s;
}

.submit-btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 20px rgba(56, 189, 248, 0.3);
}

.success-message {
    background: #dcfce7;
    border: 2px solid #86efac;
    color: #059669;
    padding: 16px;
    border-radius: 8px;
    margin-bottom: 24px;
}

.comments-list {
    margin-top: 32px;
}

.comment-item {
    background: white;
    padding: 24px;
    border-radius: 12px;
    margin-bottom: 20px;
    border: 1px solid #e2e8f0;
}

.comment-header {
    display: flex;
    align-items: center;
    gap: 12px;
    margin-bottom: 12px;
}

.comment-avatar {
    width: 48px;
    height: 48px;
    border-radius: 50%;
    background: linear-gradient(135deg, #38bdf8 0%, #22d3ee 100%);
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-weight: 700;
    font-size: 18px;
}

.comment-author {
    font-weight: 700;
    color: #1e293b;
}

.comment-date {
    color: #94a3b8;
    font-size: 13px;
}

.comment-content {
    color: #475569;
    line-height: 1.6;
}

.back-link {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    color: #38bdf8;
    text-decoration: none;
    font-weight: 600;
    margin-bottom: 24px;
    transition: all 0.3s;
}

.back-link:hover {
    color: #0ea5e9;
    transform: translateX(-4px);
}

/* Sidebar Widgets */
.sidebar-widget {
    background: white;
    border-radius: 12px;
    padding: 24px;
    margin-bottom: 24px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.08);
}

.widget-title {
    font-size: 1.25rem;
    font-weight: 700;
    color: #1e293b;
    margin: 0 0 20px 0;
    padding-bottom: 12px;
    border-bottom: 2px solid #38bdf8;
    display: flex;
    align-items: center;
    gap: 8px;
}

.widget-title i {
    color: #38bdf8;
    font-size: 1.125rem;
}

.sidebar-article {
    display: flex;
    gap: 12px;
    padding: 16px 0;
    border-bottom: 1px solid #e2e8f0;
    text-decoration: none;
    color: inherit;
    transition: all 0.3s ease;
}

.sidebar-article:last-child {
    border-bottom: none;
    padding-bottom: 0;
}

.sidebar-article:first-child {
    padding-top: 0;
}

.sidebar-article:hover {
    transform: translateX(4px);
}

.sidebar-article:hover .sidebar-article-title {
    color: #38bdf8;
}

.sidebar-thumbnail {
    flex-shrink: 0;
    width: 80px;
    height: 80px;
    border-radius: 8px;
    overflow: hidden;
    background: linear-gradient(135deg, #f0f9ff, #e0f2fe);
    display: flex;
    align-items: center;
    justify-content: center;
}

.sidebar-thumbnail img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.sidebar-thumbnail i {
    font-size: 1.5rem;
    color: rgba(56,189,248,0.4);
}

.sidebar-article-content {
    flex: 1;
    min-width: 0;
}

.sidebar-article-meta {
    font-size: 0.75rem;
    color: #64748b;
    margin-bottom: 6px;
}

.sidebar-article-title {
    font-size: 0.9375rem;
    font-weight: 600;
    color: #1e293b;
    line-height: 1.4;
    margin: 0;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
    transition: color 0.3s ease;
}

.sidebar-article-views {
    font-size: 0.75rem;
    color: #64748b;
    margin-top: 6px;
    display: flex;
    align-items: center;
    gap: 4px;
}

.sidebar-article-views i {
    font-size: 0.625rem;
}

/* Responsive */
@media (max-width: 1024px) {
    .content-wrapper {
        flex-direction: column !important;
        gap: 40px !important;
        padding: 0 24px !important;
    }
    
    .post-main-column {
        flex: 1 !important;
        width: 100% !important;
        max-width: 100% !important;
    }
    
    .sidebar {
        flex: 1 !important;
        width: 100% !important;
        max-width: 100% !important;
        position: static !important;
        display: grid !important;
        grid-template-columns: repeat(2, 1fr) !important;
        gap: 24px !important;
    }
    
    .sidebar-widget {
        margin-bottom: 0 !important;
    }
}

@media (max-width: 768px) {
    .content-wrapper {
        padding: 0 16px !important;
        margin: 24px 0 !important;
    }
    
    .post-detail {
        padding: 20px !important;
        border-radius: 12px !important;
    }
    
    .post-title {
        font-size: 1.5rem !important;
        line-height: 1.3 !important;
    }
    
    .post-meta {
        flex-wrap: wrap !important;
        gap: 12px !important;
        font-size: 13px !important;
    }
    
    .post-featured-image {
        height: 240px !important;
        margin-bottom: 24px !important;
    }
    
    .post-content {
        font-size: 1rem !important;
        line-height: 1.7 !important;
    }
    
    .sidebar {
        grid-template-columns: 1fr !important;
        gap: 16px !important;
    }
    
    .sidebar-widget {
        padding: 20px !important;
    }
    
    .widget-title {
        font-size: 1.1rem !important;
    }
    
    .form-grid {
        grid-template-columns: 1fr !important;
    }
    
    .comment-form {
        padding: 20px !important;
    }
    
    .comment-form-title {
        font-size: 1.1rem !important;
    }
    
    .comments-title {
        font-size: 1.4rem !important;
    }
    
    .sidebar-thumbnail {
        width: 70px !important;
        height: 70px !important;
    }
    
    .sidebar-article-title {
        font-size: 0.875rem !important;
    }
}

@media (max-width: 480px) {
    .content-wrapper {
        padding: 0 12px !important;
        margin: 16px 0 !important;
    }
    
    .post-detail {
        padding: 16px !important;
    }
    
    .post-title {
        font-size: 1.25rem !important;
        margin-bottom: 12px !important;
    }
    
    .post-meta {
        gap: 8px !important;
        font-size: 12px !important;
    }
    
    .post-meta-item {
        gap: 4px !important;
    }
    
    .post-featured-image {
        height: 200px !important;
        border-radius: 8px !important;
        margin-bottom: 20px !important;
    }
    
    .post-content {
        font-size: 0.9375rem !important;
        margin-bottom: 32px !important;
    }
    
    .back-link {
        font-size: 14px !important;
        margin-bottom: 16px !important;
    }
    
    .comments-section {
        margin-top: 40px !important;
        padding-top: 32px !important;
    }
    
    .comments-title {
        font-size: 1.25rem !important;
        margin-bottom: 16px !important;
    }
    
    .comment-form {
        padding: 16px !important;
        margin-bottom: 24px !important;
    }
    
    .comment-form-title {
        font-size: 1rem !important;
        margin-bottom: 16px !important;
    }
    
    .form-group label {
        font-size: 14px !important;
        margin-bottom: 6px !important;
    }
    
    .form-group input,
    .form-group textarea {
        padding: 10px !important;
        font-size: 14px !important;
    }
    
    .form-group textarea {
        min-height: 100px !important;
    }
    
    .submit-btn {
        width: 100% !important;
        padding: 12px 24px !important;
        font-size: 15px !important;
    }
    
    .comment-item {
        padding: 16px !important;
        margin-bottom: 12px !important;
    }
    
    .comment-avatar {
        width: 40px !important;
        height: 40px !important;
        font-size: 16px !important;
    }
    
    .comment-author {
        font-size: 14px !important;
    }
    
    .comment-date {
        font-size: 12px !important;
    }
    
    .comment-content {
        font-size: 14px !important;
        line-height: 1.5 !important;
    }
    
    .sidebar-widget {
        padding: 16px !important;
    }
    
    .widget-title {
        font-size: 1rem !important;
        margin-bottom: 16px !important;
        padding-bottom: 10px !important;
    }
    
    .sidebar-article {
        gap: 10px !important;
        padding: 12px 0 !important;
    }
    
    .sidebar-thumbnail {
        width: 60px !important;
        height: 60px !important;
    }
    
    .sidebar-thumbnail i {
        font-size: 1.25rem !important;
    }
    
    .sidebar-article-meta {
        font-size: 0.6875rem !important;
        margin-bottom: 4px !important;
    }
    
    .sidebar-article-title {
        font-size: 0.8125rem !important;
        -webkit-line-clamp: 3 !important;
    }
    
    .sidebar-article-views {
        font-size: 0.6875rem !important;
        margin-top: 4px !important;
    }
}
</style>

<div class="content-wrapper" style="display: flex; width: 100%; max-width: 100%; padding: 0 40px; margin: 40px 0; gap: 2%; box-sizing: border-box;">
    <!-- Main Content (70%) -->
    <div class="post-main-column" style="flex: 0 0 68%; width: 68%; max-width: 68%; box-sizing: border-box;">
        <div class="post-detail">
            <a href="<?php echo buildLangUrl('news-modern.php'); ?>" class="back-link">
                <i class="fas fa-arrow-left"></i> <?php echo t('btn_back'); ?>
            </a>
    
    <article>
        <div class="post-header">
            <h1 class="post-title"><?php echo htmlspecialchars(getTranslatedTitle($post)); ?></h1>
            
            <div class="post-meta">
                <div class="post-meta-item">
                    <i class="fas fa-calendar"></i>
                    <?php echo date('d/m/Y', strtotime($post['published_at'])); ?>
                </div>
                <div class="post-meta-item">
                    <i class="fas fa-eye"></i>
                    <?php echo number_format($post['views']); ?> lượt xem
                </div>
                <?php if($post['category']): ?>
                <div class="post-meta-item">
                    <i class="fas fa-folder"></i>
                    <?php echo htmlspecialchars($post['category']); ?>
                </div>
                <?php endif; ?>
            </div>
            
            <?php if($post['featured_image']): ?>
            <img src="<?php echo htmlspecialchars($post['featured_image']); ?>" 
                 alt="<?php echo htmlspecialchars(getTranslatedTitle($post)); ?>"
                 class="post-featured-image">
            <?php endif; ?>
        </div>
        
        <div class="post-content">
            <?php echo getTranslatedContent($post); ?>
        </div>
    </article>
    
    <!-- Comments Section -->
    <div class="comments-section" id="comments">
        <h2 class="comments-title">
            <i class="fas fa-comments"></i> Bình luận (<?php echo count($comments); ?>)
        </h2>
        
        <!-- Comment Form -->
        <div class="comment-form">
            <h3 class="comment-form-title">Để lại bình luận</h3>
            
            <?php if ($isLoggedIn): ?>
                <!-- Logged in user -->
                <div style="background: #e0f2fe; padding: 16px; border-radius: 8px; margin-bottom: 20px; display: flex; align-items: center; gap: 12px;">
                    <img src="<?php echo htmlspecialchars($userData['avatar']); ?>" 
                         alt="<?php echo htmlspecialchars($userData['full_name']); ?>"
                         style="width: 40px; height: 40px; border-radius: 50%;">
                    <div>
                        <div style="font-weight: 600; color: #0369a1;">
                            Đăng bình luận với tên <?php echo htmlspecialchars($userData['full_name']); ?>
                        </div>
                        <div style="font-size: 13px; color: #64748b;">
                            @<?php echo htmlspecialchars($userData['username']); ?>
                        </div>
                    </div>
                </div>
                
                <form method="post" action="">
                    <div class="form-group">
                        <label>Nội dung <span style="color:red">*</span></label>
                        <textarea name="content" required placeholder="Viết bình luận của bạn..."></textarea>
                    </div>
                    
                    <button type="submit" name="submit_comment" class="submit-btn">
                        <i class="fas fa-paper-plane"></i> Gửi bình luận
                    </button>
                </form>
            <?php else: ?>
                <!-- Not logged in -->
                <div style="background: #fef3c7; padding: 16px; border-radius: 8px; margin-bottom: 20px;">
                    <strong>💡 Tip:</strong> 
                    <a href="/vnmt/login.php?redirect=<?php echo urlencode($_SERVER['REQUEST_URI']); ?>" style="color: #0284c7; font-weight: 600;">Đăng nhập</a> 
                    để bình luận nhanh hơn, không cần nhập thông tin mỗi lần!
                </div>
                
                <form method="post" action="">
                    <div class="form-grid">
                        <div class="form-group">
                            <label>Họ tên <span style="color:red">*</span></label>
                            <input type="text" name="name" required placeholder="Nguyễn Văn A">
                        </div>
                        <div class="form-group">
                            <label>Email <span style="color:red">*</span></label>
                            <input type="email" name="email" required placeholder="email@example.com">
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label>Nội dung <span style="color:red">*</span></label>
                        <textarea name="content" required placeholder="Viết bình luận của bạn..."></textarea>
                    </div>
                    
                    <button type="submit" name="submit_comment" class="submit-btn">
                        <i class="fas fa-paper-plane"></i> Gửi bình luận
                    </button>
                </form>
            <?php endif; ?>
        </div>
        
        <!-- Comments List -->
        <?php if(count($comments) > 0): ?>
        <div class="comments-list">
            <?php foreach($comments as $comment): ?>
            <div class="comment-item">
                <div class="comment-header">
                    <?php if ($comment['avatar']): ?>
                        <img src="<?php echo htmlspecialchars($comment['avatar']); ?>" 
                             alt="<?php echo htmlspecialchars($comment['author_name']); ?>"
                             class="comment-avatar"
                             style="object-fit: cover;">
                    <?php else: ?>
                        <div class="comment-avatar">
                            <?php echo strtoupper(mb_substr($comment['author_name'], 0, 1)); ?>
                        </div>
                    <?php endif; ?>
                    <div>
                        <div class="comment-author">
                            <?php echo htmlspecialchars($comment['author_name']); ?>
                            <?php if ($comment['username']): ?>
                                <span style="font-weight: 400; color: #64748b; font-size: 13px;">
                                    @<?php echo htmlspecialchars($comment['username']); ?>
                                </span>
                            <?php endif; ?>
                        </div>
                        <div class="comment-date"><?php echo date('d/m/Y H:i', strtotime($comment['created_at'])); ?></div>
                    </div>
                </div>
                <div class="comment-content">
                    <?php echo nl2br(htmlspecialchars($comment['content'])); ?>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <?php else: ?>
        <p style="text-align: center; color: #94a3b8; padding: 40px;">
            Chưa có bình luận nào. Hãy là người đầu tiên bình luận!
        </p>
        <?php endif; ?>
        </div>
        </div>
    </div>
    
    <!-- Sidebar (30%) -->
    <aside class="sidebar" style="flex: 0 0 30%; width: 30%; max-width: 30%; position: sticky; top: 100px; box-sizing: border-box;">
        <!-- Related Posts Widget -->
        <div class="sidebar-widget">
            <h3 class="widget-title">
                <i class="fas fa-layer-group"></i>
                Bài Viết Liên Quan
            </h3>
            <?php if (empty($relatedPosts)): ?>
                <p style="text-align: center; color: #64748b; font-size: 0.875rem; padding: 20px 0;">
                    Chưa có bài viết liên quan
                </p>
            <?php else: ?>
                <?php foreach ($relatedPosts as $related): ?>
                    <a href="<?php echo buildArticleUrl($related); ?>" class="sidebar-article">
                        <div class="sidebar-thumbnail">
                            <?php if (!empty($related['featured_image'])): ?>
                                <img src="<?php echo htmlspecialchars($related['featured_image']); ?>" 
                                     alt="<?php echo htmlspecialchars(getTranslatedTitle($related)); ?>"
                                     onerror="this.style.display='none';">
                            <?php else: ?>
                                <i class="fas fa-image"></i>
                            <?php endif; ?>
                        </div>
                        <div class="sidebar-article-content">
                            <div class="sidebar-article-meta">
                                <?php echo date('d/m/Y', strtotime($related['published_at'])); ?>
                            </div>
                            <h4 class="sidebar-article-title"><?php echo htmlspecialchars(getTranslatedTitle($related)); ?></h4>
                            <div class="sidebar-article-views">
                                <i class="far fa-eye"></i>
                                <?php echo number_format($related['views']); ?> lượt xem
                            </div>
                        </div>
                    </a>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
        
        <!-- Featured Posts Widget -->
        <div class="sidebar-widget">
            <h3 class="widget-title">
                <i class="fas fa-fire"></i>
                Bài Viết Nổi Bật
            </h3>
            <?php if (empty($featuredPosts)): ?>
                <p style="text-align: center; color: #64748b; font-size: 0.875rem; padding: 20px 0;">
                    Chưa có bài viết nổi bật
                </p>
            <?php else: ?>
                <?php foreach ($featuredPosts as $featured): ?>
                    <a href="<?php echo buildArticleUrl($featured); ?>" class="sidebar-article">
                        <div class="sidebar-thumbnail">
                            <?php if (!empty($featured['featured_image'])): ?>
                                <img src="<?php echo htmlspecialchars($featured['featured_image']); ?>" 
                                     alt="<?php echo htmlspecialchars(getTranslatedTitle($featured)); ?>"
                                     onerror="this.style.display='none';">
                            <?php else: ?>
                                <i class="fas fa-image"></i>
                            <?php endif; ?>
                        </div>
                        <div class="sidebar-article-content">
                            <div class="sidebar-article-meta">
                                <?php echo htmlspecialchars($featured['category']); ?>
                            </div>
                            <h4 class="sidebar-article-title"><?php echo htmlspecialchars(getTranslatedTitle($featured)); ?></h4>
                            <div class="sidebar-article-views">
                                <i class="far fa-eye"></i>
                                <?php echo number_format($featured['views']); ?> lượt xem
                            </div>
                        </div>
                    </a>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </aside>
</div>

<?php include 'inc/footer-new.php'; ?>
