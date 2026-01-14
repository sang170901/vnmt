<?php 
// Set proper encoding for Vietnamese
header('Content-Type: text/html; charset=UTF-8');

require_once 'config.php';
require_once 'lang/lang.php';
require_once 'lang/db_translate_helper.php';
require_once 'inc/db_frontend.php';
require_once 'inc/supplier_helpers.php';
require_once 'inc/url_helpers.php';
require_once 'backend/inc/helpers.php';

include 'inc/header-new.php';

// Pagination and filter parameters
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 40;
$offset = ($page - 1) * $limit;
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$category = isset($_GET['category']) ? trim($_GET['category']) : '';

try {
    $pdo = getFrontendPDO();
    
    // Build query with filters
    $whereClause = "WHERE s.status = 1";
    $params = [];
    
    // Không dùng SQL LIKE cho tìm kiếm nữa - sẽ dùng PHP để lọc sau
    // Vì SQL LIKE không hỗ trợ tìm không dấu (ví dụ: "da" không match với "đá" trong DB)
    
    if (!empty($category)) {
        $whereClause .= " AND s.category_id = :category";
        $params[':category'] = $category;
    }
    
    // Get total count
    $countQuery = "SELECT COUNT(*) as total FROM suppliers s LEFT JOIN categories c ON s.category_id = c.id $whereClause";
    $countStmt = $pdo->prepare($countQuery);
    $countStmt->execute($params);
    $totalItems = $countStmt->fetch(PDO::FETCH_ASSOC)['total'];
    $totalPages = ceil($totalItems / $limit);
    
    // Get suppliers with pagination (join với categories, include English)
    $suppliersQuery = "SELECT s.*, s.name_en, s.description_en, c.name as category_name 
                      FROM suppliers s 
                      LEFT JOIN categories c ON s.category_id = c.id 
                      $whereClause";
    $suppliersStmt = $pdo->prepare($suppliersQuery);
    foreach ($params as $key => $value) {
        $suppliersStmt->bindValue($key, $value);
    }
    $suppliersStmt->execute();
    $allSuppliers = $suppliersStmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Sắp xếp theo độ liên quan nếu có search
    if (!empty($search)) {
        $searchLower = mb_strtolower(trim($search), 'UTF-8');
        $searchNoAccent = mb_strtolower(removeVietnameseAccents($search), 'UTF-8');
        $searchLength = mb_strlen($searchNoAccent);
        
        // Kiểm tra xem từ khóa có chứa dấu không
        // Nếu từ khóa có dấu, chỉ tìm chính xác có dấu
        // Nếu từ khóa không dấu, tìm cả có dấu và không dấu
        $hasAccent = ($searchLower !== $searchNoAccent);
        
        // Tính điểm liên quan cho mỗi supplier và lọc kết quả
        $filteredSuppliers = [];
        foreach ($allSuppliers as &$supplier) {
            $nameLower = mb_strtolower(trim($supplier['name']), 'UTF-8');
            $nameNoAccent = mb_strtolower(removeVietnameseAccents($supplier['name']), 'UTF-8');
            
            $score = 0;
            $matched = false;
            
            // Hàm kiểm tra match chính xác - chỉ match khi từ khóa là từ đầy đủ hoặc prefix hợp lý
            $checkMatch = function($text, $searchTerm, $textOriginal = '', $requireAccentMatch = false) use ($searchLength, $hasAccent) {
                // Tách thành các từ
                $words = preg_split('/[\s\-_\.]+/', $text);
                $wordsOriginal = !empty($textOriginal) ? preg_split('/[\s\-_\.]+/', $textOriginal) : [];
                
                foreach ($words as $idx => $word) {
                    $word = trim($word);
                    if (empty($word)) continue;
                    
                    $wordLength = mb_strlen($word);
                    $wordOriginal = isset($wordsOriginal[$idx]) ? trim($wordsOriginal[$idx]) : '';
                    
                    // Nếu từ khóa có dấu và yêu cầu match có dấu, kiểm tra từ gốc
                    if ($requireAccentMatch && $hasAccent && !empty($wordOriginal)) {
                        $wordToCheck = $wordOriginal;
                    } else {
                        $wordToCheck = $word;
                    }
                    
                    // Exact match
                    if ($word === $searchTerm) {
                        // Nếu từ khóa có dấu, chỉ match khi từ trong tên cũng có dấu tương ứng
                        if ($hasAccent && $requireAccentMatch) {
                            // Kiểm tra từ gốc có dấu
                            if (!empty($wordOriginal) && mb_strtolower($wordOriginal, 'UTF-8') === mb_strtolower($searchTerm, 'UTF-8')) {
                                if ($searchLength >= 2) {
                                    return true;
                                }
                            }
                        } else {
                            // Từ khóa không dấu hoặc không yêu cầu match có dấu
                            if ($searchLength >= 2) {
                                return true;
                            }
                        }
                        if ($wordLength < 2) {
                            return true;
                        }
                        continue;
                    }
                    
                    // Prefix match - từ khóa phải bắt đầu từ đầu từ
                    if ($wordLength > $searchLength && mb_substr($word, 0, $searchLength) === $searchTerm) {
                        // Nếu từ khóa có dấu, kiểm tra từ gốc có dấu
                        if ($hasAccent && $requireAccentMatch && !empty($wordOriginal)) {
                            $wordOriginalLower = mb_strtolower($wordOriginal, 'UTF-8');
                            $searchTermLower = mb_strtolower($searchTerm, 'UTF-8');
                            if (mb_strlen($wordOriginalLower) > mb_strlen($searchTermLower) && 
                                mb_substr($wordOriginalLower, 0, mb_strlen($searchTermLower)) === $searchTermLower) {
                                if (($wordLength - $searchLength) >= 2) {
                                    return true;
                                }
                            }
                        } else {
                            // Chỉ match nếu từ dài hơn từ khóa ít nhất 2 ký tự
                            if (($wordLength - $searchLength) >= 2) {
                                return true;
                            }
                        }
                    }
                }
                return false;
            };
            
            // Nếu từ khóa có dấu, chỉ tìm chính xác có dấu
            if ($hasAccent) {
                // Exact match (có dấu)
                if ($nameLower === $searchLower && $searchLength >= 2) {
                    $score = 1000;
                    $matched = true;
                }
                // Starts with (có dấu)
                elseif ($searchLength >= 2 && strpos($nameLower, $searchLower) === 0) {
                    $score = 500;
                    $matched = true;
                }
                // Match trong từ (có dấu)
                elseif ($checkMatch($nameLower, $searchLower, $nameLower, true)) {
                    $score = 100;
                    $matched = true;
                }
            } else {
                // Từ khóa không dấu, tìm cả có dấu và không dấu
                // Ưu tiên match không dấu trước
                
                // Exact match (không dấu)
                if ($nameNoAccent === $searchNoAccent && $searchLength >= 2) {
                    $score = 1000;
                    $matched = true;
                }
                // Starts with (không dấu)
                elseif ($searchLength >= 2 && strpos($nameNoAccent, $searchNoAccent) === 0) {
                    $score = 500;
                    $matched = true;
                }
                // Match trong từ (không dấu)
                elseif ($checkMatch($nameNoAccent, $searchNoAccent, $nameLower, false)) {
                    $score = 100;
                    $matched = true;
                }
            }
            
            // Chỉ thêm vào kết quả nếu có match trong tên
            if ($matched) {
                $supplier['_relevance_score'] = $score;
                $filteredSuppliers[] = $supplier;
            }
        }
        unset($supplier);
        
        // Sắp xếp theo điểm liên quan (cao -> thấp), sau đó theo tên
        usort($filteredSuppliers, function($a, $b) {
            if ($a['_relevance_score'] != $b['_relevance_score']) {
                return $b['_relevance_score'] - $a['_relevance_score'];
            }
            return strcmp($a['name'], $b['name']);
        });
        
        // Áp dụng pagination sau khi sort
        $suppliers = array_slice($filteredSuppliers, $offset, $limit);
        
        // Xóa score tạm thời
        foreach ($suppliers as &$supplier) {
            unset($supplier['_relevance_score']);
        }
        unset($supplier);
        
        // Cập nhật totalItems sau khi filter
        $totalItems = count($filteredSuppliers);
        $totalPages = ceil($totalItems / $limit);
    } else {
        // Không có search, lấy theo pagination bình thường
        $suppliersQuery .= " ORDER BY s.name ASC LIMIT :limit OFFSET :offset";
        $suppliersStmt = $pdo->prepare($suppliersQuery);
        foreach ($params as $key => $value) {
            $suppliersStmt->bindValue($key, $value);
        }
        $suppliersStmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $suppliersStmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $suppliersStmt->execute();
        $suppliers = $suppliersStmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    // Get categories (lấy từ bảng categories)
    $categoriesQuery = "SELECT id, name FROM categories ORDER BY name";
    $categoriesStmt = $pdo->query($categoriesQuery);
    $categories = $categoriesStmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Get stats
    $statsQuery = "SELECT 
        COUNT(*) as total_suppliers,
        COUNT(DISTINCT category_id) as total_categories,
        AVG(DATEDIFF(NOW(), created_at)) as avg_days
        FROM suppliers WHERE status = 1";
    $statsStmt = $pdo->query($statsQuery);
    $stats = $statsStmt->fetch(PDO::FETCH_ASSOC);
    
} catch (Exception $e) {
    $suppliers = [];
    $categories = [];
    $totalItems = 0;
    $totalPages = 0;
    $stats = ['total_suppliers' => 0, 'total_categories' => 0, 'avg_days' => 0];
    error_log("Lỗi khi truy xuất nhà cung cấp: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nhà Cung Cấp - VNMaterial</title>
    
    <!-- Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Inter', sans-serif;
            background: #f0f9ff;
            color: #1e293b;
            line-height: 1.6;
        }
        
        .suppliers-hero {
            /* make background span full viewport width */
            position: relative;
            left: 50%;
            right: 50%;
            margin-left: -50vw;
            margin-right: -50vw;
            width: 100vw;
            background: linear-gradient(135deg, #e3f2fd 0%, #bbdefb 100%);
            padding: 120px 0 80px;
            overflow: hidden;
            min-height: 420px; /* ensure visible hero area */
        }
        
        .suppliers-hero::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><defs><pattern id="grid" width="10" height="10" patternUnits="userSpaceOnUse"><path d="M 10 0 L 0 0 0 10" fill="none" stroke="rgba(21,101,192,0.1)" stroke-width="0.5"/></pattern></defs><rect width="100" height="100" fill="url(%23grid)"/></svg>');
            opacity: 0.3;
        }
        
        .hero-content {
            max-width: 1400px; /* wider content */
            margin: 0 auto;
            padding: 0 20px;
            text-align: center;
            position: relative;
            z-index: 2;
        }
        
        .hero-title {
            font-size: 3.5rem;
            font-weight: 800;
            color: #1565c0;
            margin-bottom: 1.5rem;
            text-shadow: 0 2px 8px rgba(21,101,192,0.2);
        }
        
        .hero-subtitle {
            font-size: 1.3rem;
            color: #1976d2;
            max-width: 900px; /* allow wider subtitle */
            margin: 0 auto 2.5rem;
            font-weight: 400;
        }
        
        /* hero stats removed as requested */
        
        /* Category Filter Pills - Like News */
        .category-filter {
            background: white;
            border-radius: 16px;
            padding: 1.5rem;
            margin: -60px auto 2rem;
            max-width: 1280px;
            position: relative;
            z-index: 10;
            box-shadow: 0 10px 40px rgba(0,0,0,0.08);
        }
        
        .filter-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 1.2rem;
            flex-wrap: wrap;
            gap: 1rem;
        }
        
        .filter-title {
            font-size: 0.875rem;
            font-weight: 700;
            color: #64748b;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        
        .search-box {
            position: relative;
            display: flex;
            flex: 1;
            max-width: 450px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
            border-radius: 12px;
            overflow: hidden;
            background: white;
        }
        
        .search-input {
            flex: 1;
            padding: 0.875rem 1.25rem;
            padding-right: 3.5rem;
            border: 2px solid #e0f2fe;
            border-radius: 12px;
            font-size: 0.9375rem;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            background: white;
        }
        
        .search-input:focus {
            outline: none;
            border-color: #3b82f6;
            box-shadow: 0 4px 16px rgba(59, 130, 246, 0.15);
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
            background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
            color: white;
            border: none;
            border-radius: 50%;
            font-size: 1rem;
            cursor: pointer;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .search-btn:hover {
            background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%);
            transform: translateY(-50%) scale(1.05);
            box-shadow: 0 4px 12px rgba(59, 130, 246, 0.4);
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
            background: #f1f5f9;
            color: #64748b;
            border: 2px solid transparent;
            border-radius: 50px;
            font-size: 0.875rem;
            font-weight: 600;
            text-decoration: none;
            transition: all 0.2s;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            cursor: pointer;
        }
        
        .category-pill:hover {
            background: #e0f2fe;
            color: #2563eb;
            border-color: #3b82f6;
        }
        
        .category-pill.active {
            background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
            color: white;
            border-color: #3b82f6;
        }
        
        .category-pill .count {
            background: rgba(255,255,255,0.25);
            padding: 0.125rem 0.5rem;
            border-radius: 50px;
            font-size: 0.75rem;
            font-weight: 700;
        }
        
        .category-pill.active .count {
            background: rgba(255,255,255,0.3);
        }
        
        .main-content {
            max-width: 1400px; /* wider page content */
            margin: 0 auto;
            padding: 0 30px;
        }
        
        .suppliers-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 1.25rem;
            margin-bottom: 2.5rem;
            align-items: stretch;
        }
        
        .supplier-card {
            background: white;
            border-radius: 16px;
            overflow: hidden;
            box-shadow: 0 6px 20px rgba(0,0,0,0.06);
            transition: all 0.28s ease;
            position: relative;
            border: 1px solid #f1f5f9;
            padding: 0;
            display: flex;
            flex-direction: column;
            height: 100%;
            cursor: pointer;
        }
        
        .supplier-card:hover {
            transform: translateY(-8px);
            box-shadow: 0 20px 60px rgba(0,0,0,0.15);
        }
        
        .supplier-header-link {
            display: block;
            text-decoration: none;
            transition: all 0.3s ease;
        }
        
        .supplier-header-link:hover .supplier-header {
            transform: scale(1.05);
        }
        
        .supplier-header {
            padding: 20px 20px 10px;
            background: linear-gradient(135deg, #f8fafc 0%, #f0f9ff 100%);
            position: relative;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            min-height: 160px;
            overflow: hidden;
            transition: all 0.3s ease;
        }
        
        .supplier-logo {
            max-width: 100%;
            max-height: 100%;
            width: auto;
            height: auto;
            object-fit: contain;
            display: block;
            cursor: pointer;
            margin: 0 auto;
            padding: 12px;
        }
        
        .supplier-logo-wrapper {
            width: 85px;
            height: 85px;
            background: white;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 12px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
        }
        
        .supplier-logo-placeholder {
            width: 85px;
            height: 85px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 12px;
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
        }
        
        .supplier-category {
            position: absolute;
            top: 0.9rem;
            right: 0.9rem;
            background: linear-gradient(135deg, #42a5f5 0%, #64b5f6 100%);
            color: white;
            padding: 0.45rem 0.9rem;
            border-radius: 18px;
            font-size: 0.72rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.45px;
            box-shadow: 0 2px 8px rgba(66, 165, 245, 0.3);
        }
        
        .supplier-name {
            font-size: 0.9rem;
            font-weight: 700;
            color: #1e293b;
            margin-top: 8px;
            text-align: center;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
            line-height: 1.4;
        }
        
        .supplier-body {
            padding: 0.9rem 0.9rem 0.9rem;
            flex: 1 1 auto;
            display: flex;
            flex-direction: column;
        }
        
        .supplier-details {
            space-y: 0.4rem;
            min-height: auto;
            margin-bottom: 0;
        }
        
        .supplier-detail {
            display: flex;
            align-items: flex-start;
            gap: 0.6rem;
            color: #475569;
            font-size: 0.765rem;
            margin-bottom: 0.4rem;
        }
        
        .supplier-detail i {
            width: 14px;
            color: #42a5f5;
            font-size: 0.9rem;
            flex-shrink: 0;
            margin-top: 2px;
        }
        
        .supplier-detail span {
            flex: 1;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
            line-height: 1.35;
        }
        
        .supplier-footer {
            padding: 0.9rem;
            display: flex;
            justify-content: center;
            align-items: center;
            flex-shrink: 0;
        }
        
        .view-supplier {
            background: linear-gradient(135deg, #42a5f5 0%, #64b5f6 100%);
            color: white;
            padding: 0.6rem 1.2rem;
            border-radius: 9px;
            text-decoration: none;
            font-weight: 600;
            font-size: 0.8rem;
            transition: all 0.22s;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            box-shadow: 0 2px 8px rgba(66, 165, 245, 0.3);
            width: 100%;
            justify-content: center;
        }

        .view-supplier:hover {
            transform: translateY(-1px);
            box-shadow: 0 4px 15px rgba(66, 165, 245, 0.4);
            text-decoration: none;
            color: white;
        }
        
        .pagination {
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 0.5rem;
            margin: 3rem 0;
        }
        
        .pagination a, .pagination span {
            padding: 0.8rem 1.2rem;
            border-radius: 10px;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s;
        }
        
        .pagination a {
            background: white;
            color: #475569;
            border: 1px solid #e2e8f0;
        }
        
        .pagination a:hover {
            background: #60a5fa; /* light blue */
            color: white;
            border-color: #60a5fa;
        }
        
        .pagination .current {
            background: #60a5fa; /* light blue */
            color: white;
            border: 1px solid #60a5fa;
        }
        
        .no-results {
            text-align: center;
            padding: 4rem 2rem;
            color: #64748b;
        }
        
        .no-results i {
            font-size: 4rem;
            color: #cbd5e1;
            margin-bottom: 1.5rem;
        }
        
        .no-results h3 {
            font-size: 1.5rem;
            margin-bottom: 1rem;
            color: #475569;
        }
        
        @media (max-width: 1200px) {
            .suppliers-grid {
                grid-template-columns: repeat(3, 1fr);
            }
        }

        @media (max-width: 900px) {
            .suppliers-grid {
                grid-template-columns: repeat(2, 1fr);
            }
            
            .filter-header {
                flex-direction: column;
                align-items: stretch;
            }
            
            .search-box {
                max-width: 100%;
            }
        }

        @media (max-width: 560px) {
            .suppliers-grid {
                grid-template-columns: 1fr;
            }
            
            .search-box {
                max-width: 100%;
            }

            .search-input {
                font-size: 1rem;
            }
            
            .search-btn {
                width: 40px;
                height: 40px;
            }
            
            .category-pill {
                flex: 1 1 calc(50% - 0.375rem);
                justify-content: center;
            }

            .hero-title {
                font-size: 2rem;
            }

            .hero-title {
                font-size: 2rem;
            }

            .supplier-header {
                min-height: 140px;
                padding: 15px 15px 8px;
            }

            .supplier-logo-wrapper {
                width: 70px;
                height: 70px;
            }
            
            .supplier-logo-placeholder {
                width: 70px;
                height: 70px;
            }
        }
    </style>
</head>
<body>
    <!-- Hero Section -->
    <section class="suppliers-hero">
        <div class="hero-content">
            <h1 class="hero-title">Nhà Cung Cấp</h1>
            <p class="hero-subtitle">
                Khám phá mạng lưới đối tác tin cậy với các nhà cung cấp hàng đầu về vật liệu xây dựng, 
                thiết bị công nghiệp và giải pháp công nghệ tiên tiến
            </p>
            <!-- hero stats removed -->
        </div>
    </section>

    <!-- Category Filter Pills - Like News -->
    <div class="main-content">
        <div class="category-filter">
            <div class="filter-header">
                <span class="filter-title">
                    <i class="fas fa-tags"></i> DANH MỤC
                </span>
                <form method="GET" action="suppliers.php" class="search-box">
                    <input type="text" 
                           name="search" 
                           class="search-input" 
                           placeholder="<?php echo t('suppliers_search_placeholder'); ?>" 
                           value="<?php echo htmlspecialchars($search); ?>">
                    <?php if (!empty($category)): ?>
                        <input type="hidden" name="category" value="<?php echo htmlspecialchars($category); ?>">
                    <?php endif; ?>
                    <button type="submit" class="search-btn" title="Tìm kiếm">
                        <i class="fas fa-search"></i>
                    </button>
                </form>
            </div>
            
            <div class="category-pills">
                <!-- All categories pill -->
                <a href="suppliers.php<?php echo !empty($search) ? '?search=' . urlencode($search) : ''; ?>" 
                   class="category-pill <?php echo empty($category) ? 'active' : ''; ?>">
                    <i class="fas fa-th"></i>
                    <span>Tất cả</span>
                    <span class="count"><?php echo $totalItems; ?></span>
                </a>
                
                <?php 
                // Count suppliers per category
                $categoryCounts = [];
                $stmt = $pdo->query("SELECT category_id, COUNT(*) as count FROM suppliers WHERE status = 1 AND category_id IS NOT NULL GROUP BY category_id");
                while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                    $categoryCounts[$row['category_id']] = $row['count'];
                }
                
                foreach ($categories as $cat): 
                    $count = isset($categoryCounts[$cat['id']]) ? $categoryCounts[$cat['id']] : 0;
                    if ($count == 0) continue; // Skip categories with 0 suppliers
                ?>
                <a href="suppliers.php?category=<?php echo urlencode($cat['id']); ?><?php echo !empty($search) ? '&search=' . urlencode($search) : ''; ?>" 
                   class="category-pill <?php echo $category == $cat['id'] ? 'active' : ''; ?>">
                    <span><?php echo htmlspecialchars($cat['name']); ?></span>
                    <span class="count"><?php echo $count; ?></span>
                </a>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Results -->
        <?php if (!empty($search) || !empty($category)): ?>
            <div style="margin-bottom: 2rem; text-align: center;">
                <h2 style="color: #1e293b; margin-bottom: 0.5rem;">
                    Tìm thấy <?php echo $totalItems; ?> nhà cung cấp
                </h2>
                <?php if ($search): ?>
                    <p style="color: #64748b;">Từ khóa: "<?php echo htmlspecialchars($search); ?>"</p>
                <?php endif; ?>
                <?php if ($category): ?>
                    <?php 
                    // Tìm tên category từ ID
                    $categoryName = '';
                    foreach ($categories as $cat) {
                        if ($cat['id'] == $category) {
                            $categoryName = $cat['name'];
                            break;
                        }
                    }
                    ?>
                    <p style="color: #64748b;">Danh mục: <?php echo htmlspecialchars($categoryName); ?></p>
                <?php endif; ?>
            </div>
        <?php endif; ?>

        <!-- Suppliers Grid -->
        <?php if (!empty($suppliers)): ?>
            <div class="suppliers-grid">
                <?php foreach ($suppliers as $supplier): ?>
                    <div class="supplier-card" onclick="window.location.href='<?php echo buildSupplierUrl($supplier); ?>'">
                        <?php if (!empty($supplier['category_name'])): ?>
                            <div class="supplier-category">
                                <?php echo htmlspecialchars($supplier['category_name']); ?>
                            </div>
                        <?php endif; ?>
                        
                        <a href="<?php echo buildSupplierUrl($supplier); ?>" class="supplier-header-link" onclick="event.stopPropagation();">
                            <div class="supplier-header">
                                <?php 
                                $logoPath = getSupplierLogoPath($supplier['logo'] ?? null);
                                if ($logoPath): 
                                ?>
                                    <div class="supplier-logo-wrapper">
                                        <img src="<?php echo htmlspecialchars($logoPath); ?>" 
                                             alt="<?php echo htmlspecialchars(getTranslatedName($supplier)); ?>" 
                                             class="supplier-logo"
                                             onerror="this.onerror=null; var placeholder=document.createElement('div');placeholder.className='supplier-logo-placeholder';placeholder.style.cssText='background: linear-gradient(135deg, #38bdf8 0%, #22d3ee 100%); display: flex; align-items: center; justify-content: center; width: 85px; height: 85px; margin: 0 auto 12px; border-radius: 12px; box-shadow: 0 2px 8px rgba(0,0,0,0.08);';placeholder.innerHTML='<i class=\'fas fa-building\' style=\'color: white; font-size: 1.75rem; opacity: 0.7;\'></i>';this.parentNode.replaceChild(placeholder, this);">
                                    </div>
                                <?php else: ?>
                                    <div class="supplier-logo-placeholder" style="background: linear-gradient(135deg, #38bdf8 0%, #22d3ee 100%);">
                                        <i class="fas fa-building" style="color: white; font-size: 1.75rem; opacity: 0.7;"></i>
                                    </div>
                                <?php endif; ?>
                                <h3 class="supplier-name"><?php echo htmlspecialchars(getTranslatedName($supplier)); ?></h3>
                            </div>
                        </a>
                        
                        <div class="supplier-body">
                            
                            <div class="supplier-details">
                                <?php if ($supplier['address']): ?>
                                    <div class="supplier-detail">
                                        <i class="fas fa-map-marker-alt"></i>
                                        <span><?php echo htmlspecialchars($supplier['address']); ?></span>
                                    </div>
                                <?php endif; ?>
                                
                                <?php if ($supplier['phone']): ?>
                                    <div class="supplier-detail">
                                        <i class="fas fa-phone"></i>
                                        <span><?php echo htmlspecialchars($supplier['phone']); ?></span>
                                    </div>
                                <?php endif; ?>
                                
                                <?php if ($supplier['email']): ?>
                                    <div class="supplier-detail">
                                        <i class="fas fa-envelope"></i>
                                        <span><?php echo htmlspecialchars($supplier['email']); ?></span>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                        
                        <div class="supplier-footer">
                            <a href="<?php echo buildSupplierUrl($supplier); ?>" class="view-supplier" onclick="event.stopPropagation();">
                                <span>Xem chi tiết</span>
                                <i class="fas fa-arrow-right"></i>
                            </a>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <!-- Pagination -->
            <?php if ($totalPages > 1): ?>
                <div class="pagination">
                    <?php
                    $baseUrl = '?';
                    if ($search) $baseUrl .= 'search=' . urlencode($search) . '&';
                    if ($category) $baseUrl .= 'category=' . urlencode($category) . '&';
                    ?>
                    
                    <?php if ($page > 1): ?>
                        <a href="<?php echo $baseUrl; ?>page=<?php echo $page - 1; ?>">
                            <i class="fas fa-chevron-left"></i> Trước
                        </a>
                    <?php endif; ?>
                    
                    <?php for ($i = max(1, $page - 2); $i <= min($totalPages, $page + 2); $i++): ?>
                        <?php if ($i === $page): ?>
                            <span class="current"><?php echo $i; ?></span>
                        <?php else: ?>
                            <a href="<?php echo $baseUrl; ?>page=<?php echo $i; ?>"><?php echo $i; ?></a>
                        <?php endif; ?>
                    <?php endfor; ?>
                    
                    <?php if ($page < $totalPages): ?>
                        <a href="<?php echo $baseUrl; ?>page=<?php echo $page + 1; ?>">
                            Sau <i class="fas fa-chevron-right"></i>
                        </a>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        <?php else: ?>
            <div class="no-results">
                <i class="fas fa-building"></i>
                <h3><?php echo t('suppliers_not_found'); ?></h3>
                <p><?php echo t('suppliers_not_found_hint'); ?></p>
                <a href="suppliers.php" style="color: #667eea; text-decoration: none; font-weight: 600;">
                    ← <?php echo t('suppliers_view_all'); ?>
                </a>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>

<?php include 'inc/footer-new.php'; ?>