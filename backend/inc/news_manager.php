<?php
/*
Template cơ bản cho quản lý tin tức
Sau này có thể mở rộng thành hệ thống CMS hoàn chỉnh
*/

class NewsManager {
    
    private $pdo;
    
    // Constructor
    public function __construct() {
        require_once __DIR__ . '/db.php';
        $this->pdo = getPDO();
    }
    
    // Instance method getNews()
    public function getNews($category = '', $search = '') {
        // Try to get from database first
        try {
            $sql = "SELECT * FROM posts WHERE status = 'published'";
            $params = [];
            
            // Filter by category
            if (!empty($category)) {
                $sql .= " AND category = ?";
                $params[] = $category;
            }
            
            // Filter by search
            if (!empty($search)) {
                $sql .= " AND (title LIKE ? OR excerpt LIKE ? OR content LIKE ?)";
                $searchTerm = "%$search%";
                $params[] = $searchTerm;
                $params[] = $searchTerm;
                $params[] = $searchTerm;
            }
            
            $sql .= " ORDER BY created_at DESC";
            
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($params);
            $posts = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // If we have posts from database, format and return them
            if (!empty($posts)) {
                return array_map(function($post) {
                    return [
                        'id' => $post['id'],
                        'title' => $post['title'],
                        'slug' => $post['slug'],
                        'excerpt' => $post['excerpt'],
                        'content' => $post['content'],
                        'category' => $post['category'],
                        'category_slug' => self::createSlug($post['category']),
                        'author' => 'VNMaterial Team',
                        'author_title' => 'Biên tập viên',
                        'author_bio' => 'Đội ngũ chuyên gia',
                        'featured_image' => $post['featured_image'] ?? 'assets/images/news/default.jpg',
                        'tags' => !empty($post['tags']) ? explode(',', $post['tags']) : [],
                        'published_date' => date('Y-m-d', strtotime($post['created_at'])),
                        'reading_time' => ceil(str_word_count(strip_tags($post['content'])) / 200),
                        'views' => $post['views'] ?? 0,
                        'status' => $post['status'],
                        'featured' => false
                    ];
                }, $posts);
            }
        } catch (Exception $e) {
            // If database fails, fall back to sample news
            error_log("NewsManager error: " . $e->getMessage());
        }
        
        // Fallback to sample news if no database posts
        $news = self::getSampleNews();
        
        // Filter by category
        if (!empty($category)) {
            $news = array_filter($news, function($item) use ($category) {
                return $item['category'] === $category;
            });
        }
        
        // Filter by search
        if (!empty($search)) {
            $news = array_filter($news, function($item) use ($search) {
                return stripos($item['title'], $search) !== false || 
                       stripos($item['content'], $search) !== false ||
                       stripos($item['excerpt'], $search) !== false;
            });
        }
        
        return array_values($news);
    }
    
    // Static method getCategories()
    public function getCategories() {
        // Try to get from database first
        try {
            $stmt = $this->pdo->query("SELECT DISTINCT category FROM posts WHERE status = 'published' AND category IS NOT NULL AND category != '' ORDER BY category");
            $categories = $stmt->fetchAll(PDO::FETCH_COLUMN);
            
            if (!empty($categories)) {
                return $categories;
            }
        } catch (Exception $e) {
            error_log("NewsManager getCategories error: " . $e->getMessage());
        }
        
        // Fallback to sample news
        $news = self::getSampleNews();
        $categories = array_unique(array_column($news, 'category'));
        return array_values($categories);
    }
    
    // Template dữ liệu mẫu cho tin tức
    public static function getSampleNews() {
        return [
            [
                'id' => 1,
                'title' => 'Công Nghệ AI Trong Sản Xuất Vật Liệu Xây Dựng - Tương Lai Đã Đến',
                'slug' => 'cong-nghe-ai-trong-san-xuat-vat-lieu',
                'excerpt' => 'Khám phá cách trí tuệ nhân tạo đang cách mạng hóa ngành sản xuất vật liệu xây dựng, từ tối ưu hóa quy trình sản xuất đến phát triển vật liệu thông minh mới.',
                'content' => 'Trí tuệ nhân tạo (AI) đang mang lại những thay đổi cách mạng trong ngành sản xuất vật liệu xây dựng. Từ việc tối ưu hóa quy trình sản xuất đến phát triển các loại vật liệu thông minh mới, AI đang mở ra những cơ hội to lớn cho ngành này.

Các ứng dụng AI trong sản xuất vật liệu bao gồm:
- Dự đoán và kiểm soát chất lượng sản phẩm
- Tối ưu hóa công thức và quy trình sản xuất  
- Phân tích dữ liệu cảm biến để giám sát thiết bị
- Phát triển vật liệu composite thông minh

Với khả năng xử lý lượng dữ liệu khổng lồ và học hỏi từ kinh nghiệm, AI giúp các nhà sản xuất tạo ra những sản phẩm có chất lượng cao hơn, chi phí thấp hơn và thân thiện với môi trường hơn.',
                'category' => 'Công Nghệ Mới',
                'category_slug' => 'cong-nghe-moi',
                'author' => 'Nguyễn Đức Anh',
                'author_title' => 'Chuyên gia Công nghệ Vật liệu',
                'author_bio' => '8 năm kinh nghiệm',
                'featured_image' => 'assets/images/news/ai-construction.jpg',
                'tags' => ['AI', 'Trí tuệ nhân tạo', 'Vật liệu xây dựng', 'Công nghệ 4.0'],
                'published_date' => '2025-10-15',
                'reading_time' => 5,
                'views' => 1234,
                'status' => 'published',
                'featured' => true
            ],
            [
                'id' => 2,
                'title' => 'Gạch Sinh Thái Từ Phế Thải Nông Nghiệp',
                'slug' => 'gach-sinh-thai-tu-phe-thai-nong-nghiep',
                'excerpt' => 'Công nghệ mới cho phép sản xuất gạch xây dựng từ rơm rạ và phế thải nông nghiệp, không chỉ giải quyết vấn đề môi trường mà còn tạo ra sản phẩm bền vững.',
                'content' => 'Chúng tôi cung cấp đầy đủ các loại vật liệu xây dựng từ cơ bản đến cao cấp. Với hơn 10 năm kinh nghiệm trong ngành, chúng tôi cam kết mang đến những sản phẩm chất lượng cao nhất với giá cả hợp lý.

Sản phẩm gạch sinh thái được sản xuất từ:
- Rơm rạ và phế thải nông nghiệp
- Công nghệ nén hiện đại
- Chất kết dính thân thiện môi trường
- Quy trình sản xuất sạch

Ưu điểm vượt trội:
- Cách âm, cách nhiệt tốt
- Độ bền cao, chống thấm
- Giá thành hợp lý
- Thân thiện với môi trường',
                'category' => 'Vật Liệu',
                'category_slug' => 'vat-lieu',
                'author' => 'Trần Minh Châu',
                'author_title' => 'Kỹ sư Vật liệu',
                'author_bio' => '6 năm kinh nghiệm',
                'featured_image' => 'assets/images/news/eco-brick.jpg',
                'tags' => ['Sinh thái', 'Bền vững', 'Gạch', 'Môi trường'],
                'published_date' => '2025-10-12',
                'reading_time' => 4,
                'views' => 856,
                'status' => 'published',
                'featured' => false
            ],
            [
                'id' => 3,
                'title' => 'Giá Vật Liệu Xây Dựng Quý 4/2025',
                'slug' => 'gia-vat-lieu-xay-dung-quy-4-2025',
                'excerpt' => 'Tổng quan về biến động giá cả các loại vật liệu xây dựng chính trong quý 4, xu hướng và dự báo cho những tháng tới.',
                'content' => 'Ứng dụng công nghệ tiên tiến vào ngành xây dựng giúp tối ưu hóa thời gian và chi phí. Chúng tôi luôn cập nhật những công nghệ mới nhất để phục vụ khách hàng tốt nhất.

Báo cáo giá vật liệu xây dựng quý 4/2025:

Vật liệu chính:
- Xi măng: Tăng 3-5% so với quý trước
- Thép xây dựng: Giảm nhẹ 2-3%
- Gạch ốp lát: Ổn định, biến động nhỏ
- Cát, sỏi: Tăng 5-7% do khan hiếm

Nguyên nhân biến động:
- Giá nguyên liệu thô tăng
- Chi phí vận chuyển cao
- Nhu cầu thị trường gia tăng
- Chính sách môi trường mới

Dự báo xu hướng 6 tháng tới: Giá có thể tiếp tục tăng nhẹ 2-4%.',
                'category' => 'Thị Trường',
                'category_slug' => 'thi-truong',
                'author' => 'Lê Văn Nam',
                'author_title' => 'Chuyên gia Thị trường',
                'author_bio' => '10 năm kinh nghiệm',
                'featured_image' => 'assets/images/news/market-price.jpg',
                'tags' => ['Giá cả', 'Thị trường', 'Dự báo', 'Vật liệu'],
                'published_date' => '2025-10-10',
                'reading_time' => 6,
                'views' => 2156,
                'status' => 'published',
                'featured' => false
            ]
        ];
    }
    
    // Hàm lấy tin tức theo trang
    public static function getNewsByPage($page = 1, $perPage = 6) {
        $news = self::getSampleNews();
        $total = count($news);
        $offset = ($page - 1) * $perPage;
        
        return [
            'data' => array_slice($news, $offset, $perPage),
            'pagination' => [
                'current_page' => $page,
                'per_page' => $perPage,
                'total' => $total,
                'total_pages' => ceil($total / $perPage)
            ]
        ];
    }
    
    // Hàm lấy tin tức theo danh mục
    public static function getNewsByCategory($category) {
        $news = self::getSampleNews();
        if ($category === 'tat-ca') {
            return $news;
        }
        
        return array_filter($news, function($item) use ($category) {
            return $item['category_slug'] === $category;
        });
    }
    
    // Hàm lấy bài viết nổi bật
    public static function getFeaturedNews() {
        $news = self::getSampleNews();
        $featured = array_filter($news, function($item) {
            return $item['featured'] === true;
        });
        
        return !empty($featured) ? array_values($featured)[0] : null;
    }
    
    // Hàm lấy bài viết liên quan
    public static function getRelatedNews($currentId, $limit = 3) {
        $news = self::getSampleNews();
        $related = array_filter($news, function($item) use ($currentId) {
            return $item['id'] !== $currentId;
        });
        
        return array_slice(array_values($related), 0, $limit);
    }
    
    // Hàm tìm kiếm tin tức
    public static function searchNews($keyword) {
        $news = self::getSampleNews();
        $keyword = strtolower($keyword);
        
        return array_filter($news, function($item) use ($keyword) {
            return strpos(strtolower($item['title']), $keyword) !== false ||
                   strpos(strtolower($item['excerpt']), $keyword) !== false ||
                   in_array($keyword, array_map('strtolower', $item['tags']));
        });
    }
    
    // Hàm format thời gian
    public static function formatDate($date) {
        $months = [
            1 => 'tháng 1', 2 => 'tháng 2', 3 => 'tháng 3',
            4 => 'tháng 4', 5 => 'tháng 5', 6 => 'tháng 6',
            7 => 'tháng 7', 8 => 'tháng 8', 9 => 'tháng 9',
            10 => 'tháng 10', 11 => 'tháng 11', 12 => 'tháng 12'
        ];
        
        $timestamp = strtotime($date);
        $day = date('j', $timestamp);
        $month = intval(date('n', $timestamp));
        $year = date('Y', $timestamp);
        
        return "$day {$months[$month]}, $year";
    }
    
    // Hàm tạo URL slug
    public static function createSlug($string) {
        $string = trim($string);
        $string = preg_replace('/[àáạảãâầấậẩẫăằắặẳẵ]/u', 'a', $string);
        $string = preg_replace('/[èéẹẻẽêềếệểễ]/u', 'e', $string);
        $string = preg_replace('/[ìíịỉĩ]/u', 'i', $string);
        $string = preg_replace('/[òóọỏõôồốộổỗơờớợởỡ]/u', 'o', $string);
        $string = preg_replace('/[ùúụủũưừứựửữ]/u', 'u', $string);
        $string = preg_replace('/[ỳýỵỷỹ]/u', 'y', $string);
        $string = preg_replace('/[đ]/u', 'd', $string);
        $string = preg_replace('/[ÀÁẠẢÃÂẦẤẬẨẪĂẰẮẶẲẴ]/u', 'A', $string);
        $string = preg_replace('/[ÈÉẸẺẼÊỀẾỆỂỄ]/u', 'E', $string);
        $string = preg_replace('/[ÌÍỊỈĨ]/u', 'I', $string);
        $string = preg_replace('/[ÒÓỌỎÕÔỒỐỘỔỖƠỜỚỢỞỠ]/u', 'O', $string);
        $string = preg_replace('/[ÙÚỤỦŨƯỪỨỰỬỮ]/u', 'U', $string);
        $string = preg_replace('/[ỲÝỴỶỸ]/u', 'Y', $string);
        $string = preg_replace('/[Đ]/u', 'D', $string);
        $string = strtolower($string);
        $string = preg_replace('/[^a-z0-9\s-]/', '', $string);
        $string = preg_replace('/[\s-]+/', '-', $string);
        $string = trim($string, '-');
        
        return $string;
    }
    
    // Hàm tăng view count (placeholder)
    public static function incrementViews($newsId) {
        // TODO: Implement database update
        return true;
    }
}

// Ví dụ sử dụng:
/*
// Lấy tất cả tin tức với phân trang
$newsData = NewsManager::getNewsByPage(1, 6);
$newsList = $newsData['data'];
$pagination = $newsData['pagination'];

// Lấy bài viết nổi bật
$featuredNews = NewsManager::getFeaturedNews();

// Lấy tin tức theo danh mục
$techNews = NewsManager::getNewsByCategory('cong-nghe');

// Tìm kiếm
$searchResults = NewsManager::searchNews('AI');

// Format ngày
$formattedDate = NewsManager::formatDate('2025-10-15');
*/
?>