<?php
require __DIR__ . '/inc/db.php';
require __DIR__ . '/inc/auth.php';
require __DIR__ . '/inc/activity.php';
$pdo = getPDO();

$action = $_GET['action'] ?? '';
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$flash = ['type'=>'','message'=>''];

// Xử lý POST - Lưu bài viết
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['save_post'])) {
        $title = trim($_POST['title'] ?? '');
        $slug = trim($_POST['slug'] ?? '');
        $content = trim($_POST['content'] ?? '');
        $excerpt = trim($_POST['excerpt'] ?? '');
        $featured_image = trim($_POST['featured_image'] ?? '');
        $category = trim($_POST['category'] ?? '');
        $status = trim($_POST['status'] ?? 'draft');
        $featured = isset($_POST['featured']) ? 1 : 0;
        $published_at = trim($_POST['published_at'] ?? '');
        
        // Tự động tạo slug nếu trống
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
                $newId = $pdo->lastInsertId();
                log_activity($_SESSION['user']['id'] ?? null, 'create_post', 'post', $newId, null);
            }
            header('Location: posts.php?msg=' . urlencode('Lưu thành công') . '&t=success'); 
            exit;
        } catch (Exception $e) { 
            $flash['type']='error'; 
            $flash['message']='Lỗi: '.$e->getMessage(); 
        }
    }
}

// Xóa bài viết
if ($action==='delete' && $id) { 
    $pdo->prepare('DELETE FROM posts WHERE id=?')->execute([$id]); 
    log_activity($_SESSION['user']['id'] ?? null, 'delete_post', 'post', $id, null);
    header('Location: posts.php?msg=' . urlencode('Đã xóa') . '&t=success'); 
    exit; 
}

// Lấy dữ liệu bài viết để chỉnh sửa (AJAX)
if ($action === 'get' && $id) {
    header('Content-Type: application/json');
    try {
        $stmt = $pdo->prepare('SELECT * FROM posts WHERE id = ?');
        $stmt->execute([$id]);
        $post = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($post) {
            echo json_encode(['success' => true, 'post' => $post]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Không tìm thấy bài viết']);
        }
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
    exit;
}

require __DIR__ . '/inc/header.php';

// Lấy flash message từ URL
if (isset($_GET['msg'])) {
    $flash['type'] = $_GET['t'] ?? 'success';
    $flash['message'] = $_GET['msg'];
}

// Lấy tất cả bài viết
$posts = $pdo->query('SELECT * FROM posts ORDER BY created_at DESC')->fetchAll(PDO::FETCH_ASSOC);

// Thống kê
$totalPosts = count($posts);
$publishedPosts = count(array_filter($posts, function($p) { return $p['status'] === 'published'; }));
$draftPosts = count(array_filter($posts, function($p) { return $p['status'] === 'draft'; }));
?>

<div class="card">
    <h2 class="page-main-title">Quản lý Bài viết</h2>
    
    <?php if(!empty($flash['message'])): ?>
        <div class="flash <?php echo $flash['type']==='success'?'success':'error' ?>">
            <?php echo htmlspecialchars($flash['message']) ?>
        </div>
    <?php endif; ?>
    
    <!-- Thống kê -->
    <div style="display: flex; gap: 16px; margin-bottom: 24px;">
        <div style="flex: 1; padding: 20px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border-radius: 12px; color: white;">
            <div style="font-size: 14px; opacity: 0.9;">Tổng số bài viết</div>
            <div style="font-size: 32px; font-weight: 800; margin-top: 8px;"><?php echo $totalPosts; ?></div>
        </div>
        <div style="flex: 1; padding: 20px; background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%); border-radius: 12px; color: white;">
            <div style="font-size: 14px; opacity: 0.9;">Đã xuất bản</div>
            <div style="font-size: 32px; font-weight: 800; margin-top: 8px;"><?php echo $publishedPosts; ?></div>
        </div>
        <div style="flex: 1; padding: 20px; background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%); border-radius: 12px; color: white;">
            <div style="font-size: 14px; opacity: 0.9;">Bản nháp</div>
            <div style="font-size: 32px; font-weight: 800; margin-top: 8px;"><?php echo $draftPosts; ?></div>
        </div>
    </div>
    
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
        <a href="post_form.php" class="small-btn primary">
            <i class="fas fa-plus"></i> Thêm Bài viết
        </a>
        <a href="../news-modern.php" target="_blank" class="small-btn">
            <i class="fas fa-eye"></i> Xem trang tin tức
        </a>
    </div>
    
    <table class="table">
        <thead>
            <tr>
                <th>ID</th>
                <th>Tiêu đề</th>
                <th>Danh mục</th>
                <th>Trạng thái</th>
                <th>Nổi bật</th>
                <th>Lượt xem</th>
                <th>Ngày xuất bản</th>
                <th>Hành động</th>
            </tr>
        </thead>
        <tbody>
        <?php foreach($posts as $p): ?>
            <tr>
                <td><strong><?php echo $p['id'] ?></strong></td>
                <td>
                    <div style="display: flex; align-items: center; gap: 12px;">
                        <?php if($p['featured_image']): ?>
                            <img src="<?php echo htmlspecialchars($p['featured_image']) ?>" 
                                 style="width: 60px; height: 40px; object-fit: cover; border-radius: 6px;">
                        <?php endif; ?>
                        <div>
                            <strong><?php echo htmlspecialchars($p['title']) ?></strong>
                            <?php if($p['excerpt']): ?>
                                <br><small style="color: #64748b;"><?php echo htmlspecialchars(substr($p['excerpt'], 0, 60)) ?>...</small>
                            <?php endif; ?>
                        </div>
                    </div>
                </td>
                <td>
                    <?php if($p['category']): ?>
                        <span style="padding: 4px 12px; background: #e0f2fe; color: #0284c7; border-radius: 12px; font-size: 12px; font-weight: 600;">
                            <?php echo htmlspecialchars($p['category']) ?>
                        </span>
                    <?php endif; ?>
                </td>
                <td>
                    <?php if($p['status'] === 'published'): ?>
                        <span style="color: green; font-weight: 600;">✓ Đã xuất bản</span>
                    <?php elseif($p['status'] === 'draft'): ?>
                        <span style="color: orange; font-weight: 600;">⊙ Bản nháp</span>
                    <?php else: ?>
                        <span style="color: blue; font-weight: 600;">⏰ Đã lên lịch</span>
                    <?php endif; ?>
                </td>
                <td style="text-align: center;">
                    <?php if($p['featured']): ?>
                        <span style="color: #f59e0b; font-size: 18px;">⭐</span>
                    <?php endif; ?>
                </td>
                <td style="text-align: center;">
                    <strong style="color: #38bdf8;"><?php echo number_format($p['views']) ?></strong>
                </td>
                <td style="font-size: 13px;">
                    <?php echo $p['published_at'] ? date('d/m/Y', strtotime($p['published_at'])) : '-' ?>
                </td>
                <td class="btn-row">
                    <a href="post_form.php?id=<?php echo $p['id'] ?>" class="small-btn">
                        <i class="fas fa-edit"></i> Sửa
                    </a>
                    <a class="small-btn warn" href="posts.php?action=delete&id=<?php echo $p['id'] ?>" 
                       onclick="return confirm('Xóa bài viết này?')">
                        <i class="fas fa-trash"></i> Xóa
                    </a>
                </td>
            </tr>
        <?php endforeach; ?>
        <?php if(empty($posts)): ?>
            <tr>
                <td colspan="8" style="text-align:center; color:#999; padding:40px;">
                    Chưa có bài viết nào. Nhấn "Thêm Bài viết" để bắt đầu.
                </td>
            </tr>
        <?php endif; ?>
        </tbody>
    </table>
</div>

<!-- Modal Thêm Bài viết -->
<div id="addModal" class="modal">
    <div class="modal-content" style="max-width: 900px;">
        <div class="modal-header">
            <h3 style="margin:0">Thêm Bài viết Mới</h3>
            <span class="modal-close" onclick="closeAddModal()">&times;</span>
        </div>
        <div class="modal-body">
            <form method="post" id="addPostForm">
                <div style="display:grid;grid-template-columns:2fr 1fr;gap:16px">
                    <div>
                        <label>Tiêu đề <span style="color:red">*</span>
                            <input type="text" name="title" id="add_title" required style="width:100%">
                        </label>
                    </div>
                    <div>
                        <label>Slug (URL thân thiện)
                            <input type="text" name="slug" id="add_slug" style="width:100%">
                        </label>
                    </div>
                </div>

                <label style="margin-top:16px;">Tóm tắt
                    <textarea name="excerpt" id="add_excerpt" rows="2" style="width:100%"></textarea>
                </label>

                <label style="margin-top:16px;">Nội dung <span style="color:red">*</span>
                    <textarea name="content" id="add_content" rows="8" required style="width:100%"></textarea>
                </label>

                <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;margin-top:16px">
                    <div>
                        <label>URL Hình ảnh đại diện
                            <input type="text" name="featured_image" id="add_featured_image" style="width:100%">
                        </label>
                    </div>
                    <div>
                        <label>Danh mục
                            <select name="category" id="add_category" style="width:100%">
                                <option value="">-- Chọn danh mục --</option>
                                <option value="Vật liệu">Vật liệu</option>
                                <option value="Thiết bị">Thiết bị</option>
                                <option value="Công nghệ">Công nghệ</option>
                                <option value="Cảnh quan">Cảnh quan</option>
                                <option value="Tin tức">Tin tức</option>
                            </select>
                        </label>
                    </div>
                </div>

                <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:16px;margin-top:16px">
                    <div>
                        <label>Trạng thái
                            <select name="status" id="add_status" style="width:100%">
                                <option value="draft">Bản nháp</option>
                                <option value="published">Xuất bản</option>
                                <option value="scheduled">Lên lịch</option>
                            </select>
                        </label>
                    </div>
                    <div>
                        <label>Ngày xuất bản
                            <input type="datetime-local" name="published_at" id="add_published_at" style="width:100%">
                        </label>
                    </div>
                    <div>
                        <label style="margin-top:24px;display:flex;align-items:center;gap:8px;cursor:pointer">
                            <input type="checkbox" name="featured" id="add_featured">
                            <span>⭐ Bài viết nổi bật</span>
                        </label>
                    </div>
                </div>

                <div style="margin-top:24px;display:flex;gap:12px;justify-content:flex-end">
                    <button type="button" class="small-btn" onclick="closeAddModal()">Hủy</button>
                    <button type="submit" class="small-btn primary" name="save_post">💾 Thêm bài viết</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Sửa Bài viết -->
<div id="editModal" class="modal">
    <div class="modal-content" style="max-width: 900px;">
        <div class="modal-header">
            <h3 style="margin:0">Chỉnh Sửa Bài viết</h3>
            <span class="modal-close" onclick="closeEditModal()">&times;</span>
        </div>
        <div class="modal-body">
            <form method="post" id="editPostForm" action="posts.php?action=edit&id=">
                <div style="display:grid;grid-template-columns:2fr 1fr;gap:16px">
                    <div>
                        <label>Tiêu đề <span style="color:red">*</span>
                            <input type="text" name="title" id="edit_title" required style="width:100%">
                        </label>
                    </div>
                    <div>
                        <label>Slug (URL thân thiện)
                            <input type="text" name="slug" id="edit_slug" style="width:100%">
                        </label>
                    </div>
                </div>

                <label style="margin-top:16px;">Tóm tắt
                    <textarea name="excerpt" id="edit_excerpt" rows="2" style="width:100%"></textarea>
                </label>

                <label style="margin-top:16px;">Nội dung <span style="color:red">*</span>
                    <textarea name="content" id="edit_content" rows="8" required style="width:100%"></textarea>
                </label>

                <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;margin-top:16px">
                    <div>
                        <label>URL Hình ảnh đại diện
                            <input type="text" name="featured_image" id="edit_featured_image" style="width:100%">
                        </label>
                    </div>
                    <div>
                        <label>Danh mục
                            <select name="category" id="edit_category" style="width:100%">
                                <option value="">-- Chọn danh mục --</option>
                                <option value="Vật liệu">Vật liệu</option>
                                <option value="Thiết bị">Thiết bị</option>
                                <option value="Công nghệ">Công nghệ</option>
                                <option value="Cảnh quan">Cảnh quan</option>
                                <option value="Tin tức">Tin tức</option>
                            </select>
                        </label>
                    </div>
                </div>

                <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:16px;margin-top:16px">
                    <div>
                        <label>Trạng thái
                            <select name="status" id="edit_status" style="width:100%">
                                <option value="draft">Bản nháp</option>
                                <option value="published">Xuất bản</option>
                                <option value="scheduled">Lên lịch</option>
                            </select>
                        </label>
                    </div>
                    <div>
                        <label>Ngày xuất bản
                            <input type="datetime-local" name="published_at" id="edit_published_at" style="width:100%">
                        </label>
                    </div>
                    <div>
                        <label style="margin-top:24px;display:flex;align-items:center;gap:8px;cursor:pointer">
                            <input type="checkbox" name="featured" id="edit_featured">
                            <span>⭐ Bài viết nổi bật</span>
                        </label>
                    </div>
                </div>

                <div style="margin-top:24px;display:flex;gap:12px;justify-content:flex-end">
                    <button type="button" class="small-btn" onclick="closeEditModal()">Hủy</button>
                    <button type="submit" class="small-btn primary" name="save_post">💾 Lưu thay đổi</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- TinyMCE Editor -->
<script src="https://cdn.tiny.cloud/1/no-api-key/tinymce/6/tinymce.min.js" referrerpolicy="origin"></script>

<style>
/* Đảm bảo TinyMCE hoạt động trong modal */
.tox-tinymce {
    z-index: 1001 !important;
}
.tox-dialog-wrap,
.tox-dialog {
    z-index: 10000 !important;
}
.tox .tox-editor-container {
    pointer-events: auto !important;
}
.tox .tox-edit-area {
    pointer-events: auto !important;
}
.tox .tox-edit-area iframe {
    pointer-events: auto !important;
}
</style>

<script>
// Khởi tạo TinyMCE cho textarea content
function initTinyMCE(selector) {
    // Xóa instance cũ nếu có
    const editorId = selector.replace('#', '');
    if (tinymce.get(editorId)) {
        tinymce.get(editorId).remove();
    }
    
    tinymce.init({
        selector: selector,
        height: 450,
        menubar: 'file edit view insert format tools table help',
        plugins: [
            'advlist', 'autolink', 'lists', 'link', 'image', 'charmap', 'preview',
            'anchor', 'searchreplace', 'visualblocks', 'code', 'fullscreen',
            'insertdatetime', 'media', 'table', 'help', 'wordcount'
        ],
        toolbar: 'undo redo | formatselect | ' +
                 'bold italic underline strikethrough | forecolor backcolor | ' +
                 'alignleft aligncenter alignright alignjustify | ' +
                 'bullist numlist outdent indent | ' +
                 'link image | removeformat code | help',
        toolbar_mode: 'sliding',
        content_style: 'body { font-family: Arial, sans-serif; font-size: 14px; line-height: 1.6; padding: 10px; }',
        image_title: true,
        automatic_uploads: false,
        file_picker_types: 'image',
        relative_urls: false,
        remove_script_host: false,
        convert_urls: true,
        branding: false,
        promotion: false,
        statusbar: true,
        resize: true,
        readonly: false,
        // Quan trọng: Đảm bảo editor có thể nhận focus trong modal
        auto_focus: selector.replace('#', ''),
        setup: function(editor) {
            editor.on('init', function() {
                console.log('TinyMCE initialized for: ' + selector);
                // Force focus vào editor
                setTimeout(function() {
                    editor.focus();
                }, 100);
            });
        }
    });
}

function openAddModal() {
    document.getElementById('addModal').style.display = 'block';
    document.body.style.overflow = 'hidden';
    document.getElementById('addPostForm').reset();
    
    // Đợi modal hiển thị hoàn toàn trước khi khởi tạo TinyMCE
    setTimeout(() => {
        if (typeof tinymce !== 'undefined') {
            initTinyMCE('#add_content');
        } else {
            console.error('TinyMCE chưa được load');
        }
    }, 300);
}

function closeAddModal() {
    // Hủy TinyMCE trước khi đóng modal
    if (tinymce.get('add_content')) {
        tinymce.get('add_content').remove();
    }
    document.getElementById('addModal').style.display = 'none';
    document.body.style.overflow = 'auto';
}

function openEditModal(postId) {
    document.getElementById('editModal').style.display = 'block';
    document.body.style.overflow = 'hidden';
    
    document.getElementById('editPostForm').action = 'posts.php?action=edit&id=' + postId;
    
    fetch('posts.php?action=get&id=' + postId)
        .then(response => {
            if (!response.ok) throw new Error('Network error');
            return response.json();
        })
        .then(data => {
            if (data.success) {
                const p = data.post;
                document.getElementById('edit_title').value = p.title || '';
                document.getElementById('edit_slug').value = p.slug || '';
                document.getElementById('edit_excerpt').value = p.excerpt || '';
                document.getElementById('edit_featured_image').value = p.featured_image || '';
                document.getElementById('edit_category').value = p.category || '';
                document.getElementById('edit_status').value = p.status || 'draft';
                
                // Chuyển datetime sang datetime-local format
                if (p.published_at && p.published_at !== '0000-00-00 00:00:00') {
                    document.getElementById('edit_published_at').value = p.published_at.replace(' ', 'T').substring(0, 16);
                } else {
                    document.getElementById('edit_published_at').value = '';
                }
                
                document.getElementById('edit_featured').checked = p.featured == 1;
                
                // Khởi tạo TinyMCE và load nội dung
                setTimeout(() => {
                    if (typeof tinymce !== 'undefined') {
                        initTinyMCE('#edit_content');
                        // Đợi TinyMCE khởi tạo xong rồi mới set content
                        setTimeout(() => {
                            if (tinymce.get('edit_content')) {
                                tinymce.get('edit_content').setContent(p.content || '');
                            }
                        }, 500);
                    } else {
                        console.error('TinyMCE chưa được load');
                    }
                }, 300);
            } else {
                alert('Không thể tải thông tin bài viết: ' + (data.message || 'Lỗi không xác định'));
                closeEditModal();
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Lỗi khi tải dữ liệu: ' + error.message);
            closeEditModal();
        });
}

function closeEditModal() {
    // Hủy TinyMCE trước khi đóng modal
    if (tinymce.get('edit_content')) {
        tinymce.get('edit_content').remove();
    }
    document.getElementById('editModal').style.display = 'none';
    document.body.style.overflow = 'auto';
}

window.onclick = function(event) {
    const addModal = document.getElementById('addModal');
    const editModal = document.getElementById('editModal');
    
    if (event.target == addModal) closeAddModal();
    if (event.target == editModal) closeEditModal();
}

document.addEventListener('keydown', function(event) {
    if (event.key === 'Escape') {
        closeAddModal();
        closeEditModal();
    }
});

// Tự động tạo slug từ title
document.getElementById('add_title').addEventListener('input', function() {
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
    document.getElementById('add_slug').value = slug;
});
</script>

<?php require __DIR__ . '/inc/footer.php'; ?>

