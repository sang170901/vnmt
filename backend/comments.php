<?php
require __DIR__ . '/inc/db.php';
require __DIR__ . '/inc/auth.php';
require __DIR__ . '/inc/activity.php';
$pdo = getPDO();

$action = $_GET['action'] ?? '';
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$flash = ['type'=>'','message'=>''];

// Duyệt comment
if ($action === 'approve' && $id) {
    $pdo->prepare('UPDATE comments SET status=? WHERE id=?')->execute(['approved', $id]);
    log_activity($_SESSION['user']['id'] ?? null, 'approve_comment', 'comment', $id, null);
    header('Location: comments.php?msg=' . urlencode('Đã duyệt') . '&t=success');
    exit;
}

// Từ chối/Pending comment
if ($action === 'pending' && $id) {
    $pdo->prepare('UPDATE comments SET status=? WHERE id=?')->execute(['pending', $id]);
    log_activity($_SESSION['user']['id'] ?? null, 'pending_comment', 'comment', $id, null);
    header('Location: comments.php?msg=' . urlencode('Đã chuyển về chờ duyệt') . '&t=success');
    exit;
}

// Đánh dấu spam
if ($action === 'spam' && $id) {
    $pdo->prepare('UPDATE comments SET status=? WHERE id=?')->execute(['spam', $id]);
    log_activity($_SESSION['user']['id'] ?? null, 'spam_comment', 'comment', $id, null);
    header('Location: comments.php?msg=' . urlencode('Đã đánh dấu spam') . '&t=success');
    exit;
}

// Xóa comment
if ($action === 'delete' && $id) {
    $pdo->prepare('DELETE FROM comments WHERE id=?')->execute([$id]);
    log_activity($_SESSION['user']['id'] ?? null, 'delete_comment', 'comment', $id, null);
    header('Location: comments.php?msg=' . urlencode('Đã xóa') . '&t=success');
    exit;
}

require __DIR__ . '/inc/header.php';

// Lấy flash message
if (isset($_GET['msg'])) {
    $flash['type'] = $_GET['t'] ?? 'success';
    $flash['message'] = $_GET['msg'];
}

// Lấy tất cả comments với thông tin bài viết
$comments = $pdo->query('
    SELECT c.*, p.title as post_title, p.slug as post_slug 
    FROM comments c 
    LEFT JOIN posts p ON c.post_id = p.id 
    ORDER BY c.created_at DESC
')->fetchAll(PDO::FETCH_ASSOC);

// Thống kê
$totalComments = count($comments);
$approvedComments = count(array_filter($comments, function($c) { return $c['status'] === 'approved'; }));
$pendingComments = count(array_filter($comments, function($c) { return $c['status'] === 'pending'; }));
$spamComments = count(array_filter($comments, function($c) { return $c['status'] === 'spam'; }));
?>

<div class="card">
    <h2 class="page-main-title">Quản lý Bình luận</h2>
    
    <?php if(!empty($flash['message'])): ?>
        <div class="flash <?php echo $flash['type']==='success'?'success':'error' ?>">
            <?php echo htmlspecialchars($flash['message']) ?>
        </div>
    <?php endif; ?>
    
    <!-- Thống kê -->
    <div style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 16px; margin-bottom: 24px;">
        <div style="padding: 20px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border-radius: 12px; color: white;">
            <div style="font-size: 14px; opacity: 0.9;">Tổng số</div>
            <div style="font-size: 32px; font-weight: 800; margin-top: 8px;"><?php echo $totalComments; ?></div>
        </div>
        <div style="padding: 20px; background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%); border-radius: 12px; color: white;">
            <div style="font-size: 14px; opacity: 0.9;">Đã duyệt</div>
            <div style="font-size: 32px; font-weight: 800; margin-top: 8px;"><?php echo $approvedComments; ?></div>
        </div>
        <div style="padding: 20px; background: linear-gradient(135deg, #fad961 0%, #f76b1c 100%); border-radius: 12px; color: white;">
            <div style="font-size: 14px; opacity: 0.9;">Chờ duyệt</div>
            <div style="font-size: 32px; font-weight: 800; margin-top: 8px;"><?php echo $pendingComments; ?></div>
        </div>
        <div style="padding: 20px; background: linear-gradient(135deg, #a8edea 0%, #fed6e3 100%); border-radius: 12px; color: #333;">
            <div style="font-size: 14px; opacity: 0.9;">Spam</div>
            <div style="font-size: 32px; font-weight: 800; margin-top: 8px;"><?php echo $spamComments; ?></div>
        </div>
    </div>
    
    <table class="table">
        <thead>
            <tr>
                <th>ID</th>
                <th>Người bình luận</th>
                <th>Nội dung</th>
                <th>Bài viết</th>
                <th>Trạng thái</th>
                <th>Ngày tạo</th>
                <th>Hành động</th>
            </tr>
        </thead>
        <tbody>
        <?php foreach($comments as $c): ?>
            <tr style="<?php echo $c['status'] === 'spam' ? 'background: #fef2f2;' : ''; ?>">
                <td><strong><?php echo $c['id'] ?></strong></td>
                <td>
                    <div>
                        <strong><?php echo htmlspecialchars($c['author_name']) ?></strong>
                        <br><small style="color: #64748b;"><?php echo htmlspecialchars($c['author_email']) ?></small>
                    </div>
                </td>
                <td>
                    <div style="max-width: 300px; overflow: hidden; text-overflow: ellipsis;">
                        <?php echo htmlspecialchars(substr($c['content'], 0, 100)) ?><?php echo strlen($c['content']) > 100 ? '...' : ''; ?>
                    </div>
                </td>
                <td>
                    <?php if($c['post_title']): ?>
                        <a href="../post.php?slug=<?php echo htmlspecialchars($c['post_slug']) ?>" target="_blank" style="color: #38bdf8; text-decoration: none;">
                            <?php echo htmlspecialchars($c['post_title']) ?>
                        </a>
                    <?php else: ?>
                        <span style="color: #999;">Bài viết đã xóa</span>
                    <?php endif; ?>
                </td>
                <td>
                    <?php if($c['status'] === 'approved'): ?>
                        <span style="padding: 4px 12px; background: #dcfce7; color: #059669; border-radius: 12px; font-size: 12px; font-weight: 600;">
                            ✓ Đã duyệt
                        </span>
                    <?php elseif($c['status'] === 'pending'): ?>
                        <span style="padding: 4px 12px; background: #fef3c7; color: #d97706; border-radius: 12px; font-size: 12px; font-weight: 600;">
                            ⏳ Chờ duyệt
                        </span>
                    <?php elseif($c['status'] === 'spam'): ?>
                        <span style="padding: 4px 12px; background: #fee2e2; color: #dc2626; border-radius: 12px; font-size: 12px; font-weight: 600;">
                            🚫 Spam
                        </span>
                    <?php endif; ?>
                </td>
                <td style="font-size: 13px;">
                    <?php echo date('d/m/Y H:i', strtotime($c['created_at'])) ?>
                </td>
                <td class="btn-row">
                    <?php if($c['status'] === 'pending'): ?>
                        <a href="comments.php?action=approve&id=<?php echo $c['id'] ?>" class="small-btn" style="background: #059669; color: white;">
                            <i class="fas fa-check"></i> Duyệt
                        </a>
                    <?php elseif($c['status'] === 'approved'): ?>
                        <a href="comments.php?action=pending&id=<?php echo $c['id'] ?>" class="small-btn">
                            <i class="fas fa-clock"></i> Pending
                        </a>
                    <?php endif; ?>
                    
                    <?php if($c['status'] !== 'spam'): ?>
                        <a href="comments.php?action=spam&id=<?php echo $c['id'] ?>" class="small-btn" style="background: #dc2626; color: white;">
                            <i class="fas fa-ban"></i> Spam
                        </a>
                    <?php endif; ?>
                    
                    <a class="small-btn warn" href="comments.php?action=delete&id=<?php echo $c['id'] ?>" 
                       onclick="return confirm('Xóa bình luận này?')">
                        <i class="fas fa-trash"></i> Xóa
                    </a>
                </td>
            </tr>
        <?php endforeach; ?>
        <?php if(empty($comments)): ?>
            <tr>
                <td colspan="7" style="text-align:center; color:#999; padding:40px;">
                    Chưa có bình luận nào.
                </td>
            </tr>
        <?php endif; ?>
        </tbody>
    </table>
</div>

<?php require __DIR__ . '/inc/footer.php'; ?>

