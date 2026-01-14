<?php
require __DIR__ . '/inc/db.php';
require __DIR__ . '/inc/auth.php';
require __DIR__ . '/inc/activity.php';
$pdo = getPDO();

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$post = null;
$pageTitle = $id ? 'Chỉnh Sửa Bài Viết' : 'Thêm Bài Viết Mới';

// Lấy thông tin bài viết nếu đang sửa
if ($id) {
    $stmt = $pdo->prepare('SELECT * FROM posts WHERE id = ?');
    $stmt->execute([$id]);
    $post = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$post) {
        header('Location: posts.php?msg=' . urlencode('Không tìm thấy bài viết') . '&t=error');
        exit;
    }
}

// Xử lý submit
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title'] ?? '');
    $slug = trim($_POST['slug'] ?? '');
    $content = $_POST['content'] ?? ''; // Không trim vì có HTML
    $excerpt = trim($_POST['excerpt'] ?? '');
    $featured_image = trim($_POST['featured_image'] ?? '');
    $category = trim($_POST['category'] ?? '');
    $status = trim($_POST['status'] ?? 'draft');
    $featured = isset($_POST['featured']) ? 1 : 0;
    $published_at = trim($_POST['published_at'] ?? '');
    
    // Tự động tạo slug
    if (empty($slug) && !empty($title)) {
        $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', 
               iconv('UTF-8', 'ASCII//TRANSLIT', $title))));
    }
    
    try {
        if ($id) {
            // Cập nhật
            $pdo->prepare('UPDATE posts SET title=?, slug=?, content=?, excerpt=?, featured_image=?, category=?, status=?, featured=?, published_at=? WHERE id=?')
                ->execute([$title, $slug, $content, $excerpt, $featured_image, $category, $status, $featured, $published_at ?: null, $id]);
            log_activity($_SESSION['user']['id'] ?? null, 'update_post', 'post', $id, null);
        } else {
            // Thêm mới
            $pdo->prepare('INSERT INTO posts (title, slug, content, excerpt, featured_image, category, status, featured, published_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)')
                ->execute([$title, $slug, $content, $excerpt, $featured_image, $category, $status, $featured, $published_at ?: null]);
            log_activity($_SESSION['user']['id'] ?? null, 'create_post', 'post', $pdo->lastInsertId(), null);
        }
        header('Location: posts.php?msg=' . urlencode('Lưu thành công') . '&t=success');
        exit;
    } catch (Exception $e) {
        $error = 'Lỗi: ' . $e->getMessage();
    }
}

require __DIR__ . '/inc/header.php';
?>

<div class="card">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px;">
        <h2 class="page-main-title"><?php echo $pageTitle; ?></h2>
        <a href="posts.php" class="small-btn">
            <i class="fas fa-arrow-left"></i> Quay lại
        </a>
    </div>
    
    <?php if(isset($error)): ?>
        <div class="flash error"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>
    
    <form method="post" id="postForm">
        <div style="display:grid;grid-template-columns:2fr 1fr;gap:20px;margin-bottom:20px">
            <div>
                <label style="font-weight:600;margin-bottom:8px;display:block">
                    Tiêu đề <span style="color:red">*</span>
                </label>
                <input type="text" name="title" id="title" required 
                       value="<?php echo htmlspecialchars($post['title'] ?? ''); ?>"
                       style="width:100%;padding:12px;font-size:16px;border:2px solid #e2e8f0;border-radius:8px">
            </div>
            <div>
                <label style="font-weight:600;margin-bottom:8px;display:block">
                    Slug (URL thân thiện)
                </label>
                <input type="text" name="slug" id="slug" 
                       value="<?php echo htmlspecialchars($post['slug'] ?? ''); ?>"
                       style="width:100%;padding:12px;font-size:14px;border:2px solid #e2e8f0;border-radius:8px">
            </div>
        </div>

        <div style="margin-bottom:20px">
            <label style="font-weight:600;margin-bottom:8px;display:block">
                Tóm tắt
            </label>
            <textarea name="excerpt" rows="2" 
                      style="width:100%;padding:12px;font-size:14px;border:2px solid #e2e8f0;border-radius:8px"><?php echo htmlspecialchars($post['excerpt'] ?? ''); ?></textarea>
        </div>

        <div style="margin-bottom:20px">
            <label style="font-weight:600;margin-bottom:8px;display:block">
                Nội dung <span style="color:red">*</span>
            </label>
            <textarea name="content" id="content" required 
                      style="width:100%;height:500px"><?php echo htmlspecialchars($post['content'] ?? ''); ?></textarea>
        </div>

        <div style="display:grid;grid-template-columns:1fr 1fr;gap:20px;margin-bottom:20px">
            <div>
                <label style="font-weight:600;margin-bottom:8px;display:block">
                    URL Hình ảnh đại diện
                </label>
                <input type="text" name="featured_image" 
                       value="<?php echo htmlspecialchars($post['featured_image'] ?? ''); ?>"
                       style="width:100%;padding:12px;font-size:14px;border:2px solid #e2e8f0;border-radius:8px"
                       placeholder="https://example.com/image.jpg">
            </div>
            <div>
                <label style="font-weight:600;margin-bottom:8px;display:block">
                    Danh mục
                </label>
                <select name="category" style="width:100%;padding:12px;font-size:14px;border:2px solid #e2e8f0;border-radius:8px">
                    <option value="">-- Chọn danh mục --</option>
                    <option value="Vật liệu" <?php echo ($post['category'] ?? '') === 'Vật liệu' ? 'selected' : ''; ?>>Vật liệu</option>
                    <option value="Thiết bị" <?php echo ($post['category'] ?? '') === 'Thiết bị' ? 'selected' : ''; ?>>Thiết bị</option>
                    <option value="Công nghệ" <?php echo ($post['category'] ?? '') === 'Công nghệ' ? 'selected' : ''; ?>>Công nghệ</option>
                    <option value="Cảnh quan" <?php echo ($post['category'] ?? '') === 'Cảnh quan' ? 'selected' : ''; ?>>Cảnh quan</option>
                    <option value="Tin tức" <?php echo ($post['category'] ?? '') === 'Tin tức' ? 'selected' : ''; ?>>Tin tức</option>
                </select>
            </div>
        </div>

        <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:20px;margin-bottom:20px">
            <div>
                <label style="font-weight:600;margin-bottom:8px;display:block">
                    Trạng thái
                </label>
                <select name="status" style="width:100%;padding:12px;font-size:14px;border:2px solid #e2e8f0;border-radius:8px">
                    <option value="draft" <?php echo ($post['status'] ?? 'draft') === 'draft' ? 'selected' : ''; ?>>Bản nháp</option>
                    <option value="published" <?php echo ($post['status'] ?? '') === 'published' ? 'selected' : ''; ?>>Xuất bản</option>
                    <option value="scheduled" <?php echo ($post['status'] ?? '') === 'scheduled' ? 'selected' : ''; ?>>Lên lịch</option>
                </select>
            </div>
            <div>
                <label style="font-weight:600;margin-bottom:8px;display:block">
                    Ngày xuất bản
                </label>
                <input type="datetime-local" name="published_at" 
                       value="<?php echo $post && $post['published_at'] ? date('Y-m-d\TH:i', strtotime($post['published_at'])) : ''; ?>"
                       style="width:100%;padding:12px;font-size:14px;border:2px solid #e2e8f0;border-radius:8px">
            </div>
            <div>
                <label style="font-weight:600;margin-bottom:24px;display:block">&nbsp;</label>
                <label style="display:flex;align-items:center;gap:8px;cursor:pointer;padding:12px;background:#fef3c7;border-radius:8px">
                    <input type="checkbox" name="featured" <?php echo ($post['featured'] ?? 0) ? 'checked' : ''; ?>>
                    <span style="font-weight:600">⭐ Bài viết nổi bật</span>
                </label>
            </div>
        </div>

        <div style="margin-top:32px;display:flex;gap:12px;justify-content:flex-end;padding-top:24px;border-top:2px solid #e2e8f0">
            <a href="posts.php" class="small-btn">
                <i class="fas fa-times"></i> Hủy
            </a>
            <button type="submit" class="small-btn primary" style="padding:12px 32px">
                <i class="fas fa-save"></i> <?php echo $id ? 'Cập nhật' : 'Thêm mới'; ?>
            </button>
        </div>
    </form>
</div>

<!-- CKEditor 5 - Miễn phí, không cần API key -->
<script src="https://cdn.ckeditor.com/ckeditor5/40.0.0/classic/ckeditor.js"></script>
<script>
// Khởi tạo CKEditor
ClassicEditor
    .create(document.querySelector('#content'), {
        toolbar: {
            items: [
                'undo', 'redo',
                '|', 'heading',
                '|', 'bold', 'italic', 'underline', 'strikethrough',
                '|', 'link', 'insertImage', 'insertTable',
                '|', 'bulletedList', 'numberedList',
                '|', 'outdent', 'indent',
                '|', 'blockQuote', 'code', 'codeBlock',
                '|', 'fontColor', 'fontBackgroundColor',
                '|', 'alignment',
                '|', 'removeFormat'
            ],
            shouldNotGroupWhenFull: true
        },
        language: 'en',
        table: {
            contentToolbar: [
                'tableColumn',
                'tableRow',
                'mergeTableCells'
            ]
        },
        heading: {
            options: [
                { model: 'paragraph', title: 'Paragraph', class: 'ck-heading_paragraph' },
                { model: 'heading1', view: 'h1', title: 'Heading 1', class: 'ck-heading_heading1' },
                { model: 'heading2', view: 'h2', title: 'Heading 2', class: 'ck-heading_heading2' },
                { model: 'heading3', view: 'h3', title: 'Heading 3', class: 'ck-heading_heading3' }
            ]
        }
    })
    .then(editor => {
        console.log('CKEditor đã sẵn sàng!');
        window.editor = editor;
    })
    .catch(error => {
        console.error('Lỗi khởi tạo CKEditor:', error);
    });

// Tự động tạo slug từ title
document.getElementById('title').addEventListener('input', function() {
    const slug = this.value.toLowerCase()
        .replace(/[àáạảãâầấậẩẫăằắặẳẵ]/g, 'a')
        .replace(/[èéẹẻẽêềếệểễ]/g, 'e')
        .replace(/[ìíịỉĩ]/g, 'i')
        .replace(/[òóọỏõôồốộổỗơờớợởỡ]/g, 'o')
        .replace(/[ùúụủũưừứựửữ]/g, 'u')
        .replace(/[ỳýỵỷỹ]/g, 'y')
        .replace(/đ/g, 'd')
        .replace(/[^a-z0-9]+/g, '-')
        .replace(/^-+|-+$/g, '');
    document.getElementById('slug').value = slug;
});
</script>

<style>
/* Style cho CKEditor */
.ck-editor__editable {
    min-height: 500px;
}
.ck.ck-editor__main>.ck-editor__editable {
    border-radius: 0 0 8px 8px;
}
.ck.ck-toolbar {
    border-radius: 8px 8px 0 0;
    background: #f8fafc !important;
}
</style>

<?php require __DIR__ . '/inc/footer.php'; ?>

