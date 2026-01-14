<?php
/**
 * Translation Dashboard
 * Admin panel để review và approve translations
 */

session_start();
require_once 'inc/auth_check.php';
require_once 'inc/db.php';
require_once __DIR__ . '/../lang/TranslationManager.php';

$pdo = getPDO();

// Get filter parameters
$status = $_GET['status'] ?? 'auto';
$table = $_GET['table'] ?? 'products';
$page = max(1, intval($_GET['page'] ?? 1));
$perPage = 20;
$offset = ($page - 1) * $perPage;

// Get available tables with translations
$availableTables = ['products', 'posts', 'suppliers', 'partners', 'sliders'];

// Get statistics
$stats = [];
foreach ($availableTables as $tbl) {
    try {
        $statQuery = $pdo->query("
            SELECT 
                translation_status,
                COUNT(*) as count
            FROM {$tbl}
            GROUP BY translation_status
        ");
        $stats[$tbl] = $statQuery->fetchAll(PDO::FETCH_KEY_PAIR);
    } catch (Exception $e) {
        $stats[$tbl] = [];
    }
}

// Get items for review
try {
    // Count total
    $countSql = "SELECT COUNT(*) as total FROM {$table} WHERE translation_status = :status";
    $countStmt = $pdo->prepare($countSql);
    $countStmt->execute(['status' => $status]);
    $total = $countStmt->fetch(PDO::FETCH_ASSOC)['total'];
    $totalPages = ceil($total / $perPage);
    
    // Get items
    $sql = "SELECT * FROM {$table} WHERE translation_status = :status ORDER BY updated_at DESC LIMIT :limit OFFSET :offset";
    $stmt = $pdo->prepare($sql);
    $stmt->bindValue(':status', $status);
    $stmt->bindValue(':limit', $perPage, PDO::PARAM_INT);
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    $stmt->execute();
    $items = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Get column info to find _en fields
    $columnsResult = $pdo->query("PRAGMA table_info({$table})");
    $columns = $columnsResult->fetchAll(PDO::FETCH_ASSOC);
    $translationFields = [];
    foreach ($columns as $col) {
        if (strpos($col['name'], '_en') !== false) {
            $originalField = str_replace('_en', '', $col['name']);
            $translationFields[] = [
                'original' => $originalField,
                'translated' => $col['name']
            ];
        }
    }
    
} catch (Exception $e) {
    $items = [];
    $total = 0;
    $totalPages = 0;
    $translationFields = [];
    $error = $e->getMessage();
}

include 'inc/header.php';
?>

<style>
    .translation-dashboard {
        padding: 20px;
    }
    
    .stats-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 15px;
        margin-bottom: 30px;
    }
    
    .stat-card {
        background: white;
        padding: 20px;
        border-radius: 8px;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    }
    
    .stat-card h3 {
        margin: 0 0 10px 0;
        font-size: 14px;
        color: #666;
        text-transform: uppercase;
    }
    
    .stat-number {
        font-size: 32px;
        font-weight: bold;
        color: #333;
    }
    
    .stat-label {
        font-size: 12px;
        color: #999;
        margin-top: 5px;
    }
    
    .filters {
        background: white;
        padding: 20px;
        border-radius: 8px;
        margin-bottom: 20px;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    }
    
    .translation-item {
        background: white;
        padding: 20px;
        margin-bottom: 15px;
        border-radius: 8px;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        border-left: 4px solid #ffc107;
    }
    
    .translation-item.reviewed {
        border-left-color: #4caf50;
    }
    
    .translation-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 15px;
    }
    
    .item-id {
        font-weight: bold;
        color: #666;
    }
    
    .translation-fields {
        margin-bottom: 15px;
    }
    
    .field-group {
        margin-bottom: 15px;
        padding: 15px;
        background: #f5f5f5;
        border-radius: 4px;
    }
    
    .field-label {
        font-weight: bold;
        margin-bottom: 5px;
        color: #666;
        font-size: 12px;
        text-transform: uppercase;
    }
    
    .field-value {
        padding: 10px;
        background: white;
        border-radius: 4px;
        margin-bottom: 10px;
    }
    
    .field-value.original {
        border-left: 3px solid #2196f3;
    }
    
    .field-value.translated {
        border-left: 3px solid #ff9800;
    }
    
    .field-value input {
        width: 100%;
        padding: 8px;
        border: 1px solid #ddd;
        border-radius: 4px;
        font-size: 14px;
    }
    
    .actions {
        display: flex;
        gap: 10px;
    }
    
    .btn {
        padding: 10px 20px;
        border: none;
        border-radius: 4px;
        cursor: pointer;
        font-size: 14px;
        font-weight: 500;
        transition: all 0.3s;
    }
    
    .btn-approve {
        background: #4caf50;
        color: white;
    }
    
    .btn-approve:hover {
        background: #45a049;
    }
    
    .btn-reject {
        background: #f44336;
        color: white;
    }
    
    .btn-reject:hover {
        background: #da190b;
    }
    
    .btn-edit {
        background: #2196f3;
        color: white;
    }
    
    .pagination {
        display: flex;
        justify-content: center;
        gap: 10px;
        margin-top: 30px;
    }
    
    .pagination a {
        padding: 8px 12px;
        background: white;
        border: 1px solid #ddd;
        border-radius: 4px;
        text-decoration: none;
        color: #333;
    }
    
    .pagination a.active {
        background: #2196f3;
        color: white;
        border-color: #2196f3;
    }
    
    .auto-badge {
        background: #ff9800;
        color: white;
        padding: 2px 8px;
        border-radius: 3px;
        font-size: 11px;
        font-weight: bold;
    }
</style>

<div class="translation-dashboard">
    <h1>🌍 Translation Dashboard</h1>
    <p>Review and manage auto-translated content</p>
    
    <!-- Statistics -->
    <div class="stats-grid">
        <?php foreach ($availableTables as $tbl): ?>
            <?php
            $pending = $stats[$tbl]['pending'] ?? 0;
            $auto = $stats[$tbl]['auto'] ?? 0;
            $reviewed = $stats[$tbl]['reviewed'] ?? 0;
            $total = $pending + $auto + $reviewed;
            ?>
            <div class="stat-card">
                <h3><?= ucfirst($tbl) ?></h3>
                <div class="stat-number"><?= $auto ?></div>
                <div class="stat-label">
                    ⏳ Pending: <?= $pending ?> |
                    ✅ Reviewed: <?= $reviewed ?>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
    
    <!-- Filters -->
    <div class="filters">
        <form method="GET" style="display: flex; gap: 15px; align-items: center;">
            <div>
                <label>Table:</label>
                <select name="table" onchange="this.form.submit()">
                    <?php foreach ($availableTables as $tbl): ?>
                        <option value="<?= $tbl ?>" <?= $tbl === $table ? 'selected' : '' ?>>
                            <?= ucfirst($tbl) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div>
                <label>Status:</label>
                <select name="status" onchange="this.form.submit()">
                    <option value="pending" <?= $status === 'pending' ? 'selected' : '' ?>>⏳ Pending</option>
                    <option value="auto" <?= $status === 'auto' ? 'selected' : '' ?>>🤖 Auto-translated</option>
                    <option value="reviewed" <?= $status === 'reviewed' ? 'selected' : '' ?>>✅ Reviewed</option>
                </select>
            </div>
            
            <div style="margin-left: auto;">
                <strong>Total: <?= $total ?></strong>
            </div>
        </form>
    </div>
    
    <?php if (isset($error)): ?>
        <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>
    
    <?php if (empty($items)): ?>
        <div style="text-align: center; padding: 40px; background: white; border-radius: 8px;">
            <h3>✅ No items to review</h3>
            <p>All items in this status have been processed.</p>
        </div>
    <?php else: ?>
        <!-- Translation Items -->
        <?php foreach ($items as $item): ?>
            <div class="translation-item <?= $item['translation_status'] === 'reviewed' ? 'reviewed' : '' ?>" data-id="<?= $item['id'] ?>">
                <div class="translation-header">
                    <span class="item-id">ID: <?= $item['id'] ?></span>
                    <?php if (strpos($item[$translationFields[0]['translated']] ?? '', '[AUTO]') === 0): ?>
                        <span class="auto-badge">AUTO-TRANSLATED</span>
                    <?php endif; ?>
                </div>
                
                <div class="translation-fields">
                    <?php foreach ($translationFields as $field): ?>
                        <?php 
                        $original = $item[$field['original']] ?? '';
                        $translated = $item[$field['translated']] ?? '';
                        $translatedClean = preg_replace('/^\[AUTO\]\s*/', '', $translated);
                        ?>
                        
                        <?php if (!empty($original)): ?>
                        <div class="field-group">
                            <div class="field-label"><?= ucfirst($field['original']) ?></div>
                            <div class="field-value original">
                                <strong>🇻🇳 Vietnamese:</strong><br>
                                <?= htmlspecialchars($original) ?>
                            </div>
                            <div class="field-value translated">
                                <strong>🇬🇧 English:</strong><br>
                                <input type="text" 
                                       class="translation-input"
                                       data-field="<?= $field['original'] ?>"
                                       value="<?= htmlspecialchars($translatedClean) ?>"
                                       placeholder="Enter translation...">
                            </div>
                        </div>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </div>
                
                <div class="actions">
                    <button class="btn btn-approve" onclick="approveItem(<?= $item['id'] ?>, '<?= $table ?>')">
                        ✓ Approve All
                    </button>
                    <button class="btn btn-reject" onclick="rejectItem(<?= $item['id'] ?>, '<?= $table ?>')">
                        ✗ Re-translate
                    </button>
                </div>
            </div>
        <?php endforeach; ?>
        
        <!-- Pagination -->
        <?php if ($totalPages > 1): ?>
            <div class="pagination">
                <?php if ($page > 1): ?>
                    <a href="?table=<?= $table ?>&status=<?= $status ?>&page=<?= $page - 1 ?>">← Previous</a>
                <?php endif; ?>
                
                <?php for ($i = max(1, $page - 2); $i <= min($totalPages, $page + 2); $i++): ?>
                    <a href="?table=<?= $table ?>&status=<?= $status ?>&page=<?= $i ?>" 
                       class="<?= $i === $page ? 'active' : '' ?>">
                        <?= $i ?>
                    </a>
                <?php endfor; ?>
                
                <?php if ($page < $totalPages): ?>
                    <a href="?table=<?= $table ?>&status=<?= $status ?>&page=<?= $page + 1 ?>">Next →</a>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    <?php endif; ?>
</div>

<script>
function approveItem(id, table) {
    const item = document.querySelector(`.translation-item[data-id="${id}"]`);
    const inputs = item.querySelectorAll('.translation-input');
    
    const translations = {};
    inputs.forEach(input => {
        const field = input.dataset.field;
        translations[field] = input.value;
    });
    
    fetch('api/translation_approve.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify({
            table: table,
            id: id,
            translations: translations
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            item.style.borderLeftColor = '#4caf50';
            item.style.opacity = '0.5';
            setTimeout(() => {
                item.remove();
            }, 500);
            
            alert('✅ Translation approved!');
        } else {
            alert('❌ Error: ' + data.error);
        }
    })
    .catch(error => {
        alert('❌ Error: ' + error);
    });
}

function rejectItem(id, table) {
    if (!confirm('Mark this item for re-translation?')) {
        return;
    }
    
    fetch('api/translation_reject.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify({
            table: table,
            id: id
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('✅ Marked for re-translation');
            location.reload();
        } else {
            alert('❌ Error: ' + data.error);
        }
    })
    .catch(error => {
        alert('❌ Error: ' + error);
    });
}
</script>

<?php include 'inc/footer.php'; ?>

