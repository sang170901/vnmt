<?php
/**
 * Export danh sách Suppliers
 * Xuất danh sách nhà cung cấp với ID để dùng khi import
 */

session_start();

// Check login
if (empty($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    header('Location: login.php');
    exit;
}

require_once __DIR__ . '/inc/db.php';

$pdo = getPDO();

// Lấy danh sách suppliers
$suppliers = $pdo->query("SELECT id, name, email, phone, address FROM suppliers ORDER BY id")->fetchAll(PDO::FETCH_ASSOC);

// Nếu có request export CSV
if (isset($_GET['export']) && $_GET['export'] === 'csv') {
    // Xóa mọi output buffer để đảm bảo BOM ở đầu file
    while (ob_get_level()) {
        ob_end_clean();
    }
    
    $filename = 'danh-sach-nha-cung-cap-' . date('Y-m-d-H-i-s') . '.csv';
    
    // Set headers
    header('Content-Type: text/csv; charset=UTF-8');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    header('Pragma: no-cache');
    header('Expires: 0');
    
    // Mở output stream
    $fp = fopen('php://output', 'w');
    
    // Ghi BOM để Excel hiển thị tiếng Việt đúng (PHẢI là ký tự đầu tiên)
    fprintf($fp, "\xEF\xBB\xBF");
    
    // Header
    $headers = ['ID', 'Tên nhà cung cấp', 'Email', 'Điện thoại', 'Địa chỉ'];
    fputcsv($fp, $headers, ',', '"');
    
    // Data
    foreach ($suppliers as $supplier) {
        $row = [
            $supplier['id'],
            $supplier['name'] ?? '',
            $supplier['email'] ?? '',
            $supplier['phone'] ?? '',
            $supplier['address'] ?? '',
        ];
        fputcsv($fp, $row, ',', '"');
    }
    
    fclose($fp);
    exit;
}

?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Danh sách Nhà cung cấp</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #f5f5f5;
            padding: 20px;
        }
        .container {
            max-width: 1000px;
            margin: 0 auto;
            background: white;
            border-radius: 8px;
            padding: 30px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        h1 {
            color: #333;
            margin-bottom: 30px;
            border-bottom: 3px solid #007bff;
            padding-bottom: 10px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .btn {
            padding: 10px 20px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 14px;
            font-weight: 600;
            text-decoration: none;
            display: inline-block;
            margin-right: 10px;
        }
        .btn-success { background: #28a745; color: white; }
        .btn-primary { background: #007bff; color: white; }
        .btn:hover { opacity: 0.9; }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        th, td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        th {
            background: #f8f9fa;
            font-weight: 600;
            color: #555;
        }
        tr:hover {
            background: #f8f9fa;
        }
        .id-col {
            font-weight: bold;
            color: #007bff;
            width: 80px;
        }
        .info-box {
            background: #e7f3ff;
            padding: 15px;
            border-radius: 4px;
            margin-bottom: 20px;
            border-left: 4px solid #007bff;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>
            <span><i class="fas fa-building"></i> Danh sách Nhà cung cấp</span>
            <a href="?export=csv" class="btn btn-success">
                <i class="fas fa-file-excel"></i> Export CSV
            </a>
        </h1>
        
        <div class="info-box">
            <strong>Tổng số nhà cung cấp:</strong> <?php echo count($suppliers); ?>
            <br><small style="color: #666;">Sử dụng ID này khi import sản phẩm vào Excel. Format: "ID (Tên)" hoặc chỉ ID</small>
        </div>
        
        <table>
            <thead>
                <tr>
                    <th class="id-col">ID</th>
                    <th>Tên nhà cung cấp</th>
                    <th>Email</th>
                    <th>Điện thoại</th>
                    <th>Địa chỉ</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($suppliers)): ?>
                    <tr>
                        <td colspan="5" style="text-align: center; padding: 40px; color: #999;">
                            Chưa có nhà cung cấp nào trong database
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($suppliers as $supplier): ?>
                    <tr>
                        <td class="id-col"><?php echo $supplier['id']; ?></td>
                        <td><strong><?php echo htmlspecialchars($supplier['name'] ?? 'N/A'); ?></strong></td>
                        <td><?php echo htmlspecialchars($supplier['email'] ?? '-'); ?></td>
                        <td><?php echo htmlspecialchars($supplier['phone'] ?? '-'); ?></td>
                        <td><?php echo htmlspecialchars($supplier['address'] ?? '-'); ?></td>
                    </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
        
        <div style="margin-top: 30px; padding-top: 20px; border-top: 1px solid #ddd;">
            <h3 style="margin-bottom: 15px; color: #555;">Thông tin bổ sung</h3>
            <p style="color: #666; margin-bottom: 15px;">
                <strong>Lưu ý:</strong> Khi import sản phẩm, bạn có thể sử dụng:
            </p>
            <ul style="color: #666; margin-left: 20px; margin-bottom: 20px;">
                <li>Supplier ID: <code><?php echo !empty($suppliers) ? $suppliers[0]['id'] : 'X'; ?> (<?php echo !empty($suppliers) ? htmlspecialchars($suppliers[0]['name']) : 'Tên'; ?>)</code> hoặc chỉ <code><?php echo !empty($suppliers) ? $suppliers[0]['id'] : 'X'; ?></code></li>
                <li>Category ID: Tương tự, xem trong phần import hoặc database</li>
            </ul>
            
            <a href="multi_source_scraper.php" class="btn btn-primary">
                <i class="fas fa-arrow-left"></i> Quay lại Scraper
            </a>
            <a href="import_excel_to_db.php" class="btn btn-primary">
                <i class="fas fa-file-import"></i> Import Excel
            </a>
        </div>
    </div>
</body>
</html>
