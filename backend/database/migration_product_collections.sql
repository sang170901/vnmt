-- Migration: Enhanced Product Collections & Catalog Management
-- Created: 2025-12-10
-- Purpose: Support intelligent product collection scraping with brands, catalogs, and related products

-- ============================================
-- 1. PRODUCT COLLECTIONS (Brands/Series)
-- ============================================
CREATE TABLE IF NOT EXISTS `product_collections` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(255) NOT NULL COMMENT 'Collection name (e.g., TIERRA COLLECTION)',
  `name_en` VARCHAR(255) DEFAULT NULL,
  `slug` VARCHAR(255) NOT NULL,
  `brand` VARCHAR(255) DEFAULT NULL COMMENT 'Brand name (e.g., Alhambra)',
  `supplier_id` INT(11) DEFAULT NULL COMMENT 'Link to suppliers table',
  
  -- Collection Details
  `description` TEXT DEFAULT NULL COMMENT 'Full description from website',
  `description_en` TEXT DEFAULT NULL,
  `short_description` VARCHAR(500) DEFAULT NULL,
  `features` TEXT DEFAULT NULL COMMENT 'Key features/highlights',
  
  -- Classification
  `category` VARCHAR(100) DEFAULT NULL COMMENT 'Main category (Vật liệu, etc)',
  `material_type` VARCHAR(255) DEFAULT NULL COMMENT 'Material type (Vải bọc, mỏng, etc)',
  `applications` TEXT DEFAULT NULL COMMENT 'Usage applications',
  `construction_type` VARCHAR(255) DEFAULT NULL COMMENT 'Loại công trình',
  
  -- Supplier/Provider Info
  `supplier_name` VARCHAR(255) DEFAULT NULL COMMENT 'Vietnamese distributor',
  `supplier_location` VARCHAR(255) DEFAULT NULL,
  `supplier_phone` VARCHAR(50) DEFAULT NULL,
  `supplier_email` VARCHAR(255) DEFAULT NULL,
  
  -- Origin & Branding
  `manufacturer` VARCHAR(255) DEFAULT NULL COMMENT 'Original manufacturer',
  `manufacturer_origin` VARCHAR(255) DEFAULT NULL COMMENT 'Country of origin (Spain, etc)',
  `year_established` VARCHAR(50) DEFAULT NULL,
  `website` VARCHAR(500) DEFAULT NULL COMMENT 'Brand official website',
  
  -- Media
  `featured_image` VARCHAR(500) DEFAULT NULL COMMENT 'Main collection image',
  `logo_image` VARCHAR(500) DEFAULT NULL COMMENT 'Brand/collection logo',
  `gallery` TEXT DEFAULT NULL COMMENT 'JSON array of images',
  
  -- SEO & Metadata
  `meta_title` VARCHAR(255) DEFAULT NULL,
  `meta_description` TEXT DEFAULT NULL,
  `source_url` VARCHAR(500) DEFAULT NULL COMMENT 'Original vnbuilding.vn URL',
  
  -- Status
  `status` TINYINT(1) DEFAULT 1 COMMENT '1=active, 0=inactive',
  `is_featured` TINYINT(1) DEFAULT 0,
  `views_count` INT(11) DEFAULT 0,
  
  -- Timestamps
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  
  PRIMARY KEY (`id`),
  UNIQUE KEY `slug` (`slug`),
  KEY `idx_brand` (`brand`),
  KEY `idx_supplier` (`supplier_id`),
  KEY `idx_category` (`category`),
  KEY `idx_status` (`status`, `is_featured`),
  
  FOREIGN KEY (`supplier_id`) REFERENCES `suppliers`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- ============================================
-- 2. PRODUCT COLLECTION ITEMS (Individual Products)
-- ============================================
CREATE TABLE IF NOT EXISTS `product_collection_items` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `collection_id` INT(11) NOT NULL COMMENT 'Link to product_collections',
  `product_id` INT(11) DEFAULT NULL COMMENT 'Link to products table (if created)',
  
  -- Product Details
  `name` VARCHAR(255) NOT NULL COMMENT 'Product name (e.g., PIEDRA 06)',
  `sku` VARCHAR(100) DEFAULT NULL COMMENT 'Product code/SKU',
  `slug` VARCHAR(255) NOT NULL,
  
  -- Specifications
  `collection_name` VARCHAR(255) DEFAULT NULL COMMENT 'Bộ sưu tập (TIERRA, etc)',
  `finish_type` VARCHAR(255) DEFAULT NULL COMMENT 'Hoàn thiện (HƯỚNG MẪU UP ROADED)',
  `composition` VARCHAR(255) DEFAULT NULL COMMENT 'Thành phần (88% LI 12% CO)',
  `width` VARCHAR(100) DEFAULT NULL COMMENT 'Kích thước W: (cm)',
  `thickness` VARCHAR(100) DEFAULT NULL,
  `color` VARCHAR(100) DEFAULT NULL,
  `pattern` VARCHAR(100) DEFAULT NULL,
  `specifications` TEXT DEFAULT NULL COMMENT 'JSON of all specs',
  
  -- Pricing & Availability
  `price` DECIMAL(15,2) DEFAULT NULL,
  `price_range` VARCHAR(100) DEFAULT NULL,
  `unit` VARCHAR(50) DEFAULT 'Liên hệ',
  `availability_status` VARCHAR(50) DEFAULT 'in_stock',
  
  -- Images
  `primary_image` VARCHAR(500) DEFAULT NULL,
  `thumbnail` VARCHAR(500) DEFAULT NULL,
  
  -- Display
  `display_order` INT(11) DEFAULT 0 COMMENT 'Order in collection',
  `status` TINYINT(1) DEFAULT 1,
  
  -- Timestamps
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  
  PRIMARY KEY (`id`),
  UNIQUE KEY `collection_slug` (`collection_id`, `slug`),
  KEY `idx_collection` (`collection_id`),
  KEY `idx_product` (`product_id`),
  KEY `idx_status` (`status`),
  
  FOREIGN KEY (`collection_id`) REFERENCES `product_collections`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`product_id`) REFERENCES `products`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- ============================================
-- 3. PRODUCT FILES & CATALOGS
-- ============================================
CREATE TABLE IF NOT EXISTS `product_files` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `collection_id` INT(11) DEFAULT NULL COMMENT 'Link to collection',
  `product_id` INT(11) DEFAULT NULL COMMENT 'Link to individual product',
  
  -- File Details
  `file_type` VARCHAR(50) NOT NULL COMMENT 'catalog, technical_sheet, datasheet, 2d, 3d, pdf, etc',
  `file_name` VARCHAR(255) NOT NULL,
  `file_url` VARCHAR(500) NOT NULL COMMENT 'Original URL or local path',
  `file_size` VARCHAR(50) DEFAULT NULL,
  `mime_type` VARCHAR(100) DEFAULT NULL,
  
  -- Metadata
  `title` VARCHAR(255) DEFAULT NULL COMMENT 'Display title',
  `description` TEXT DEFAULT NULL,
  `language` VARCHAR(10) DEFAULT 'vi' COMMENT 'vi, en, etc',
  
  -- Display
  `display_order` INT(11) DEFAULT 0,
  `download_count` INT(11) DEFAULT 0,
  `status` TINYINT(1) DEFAULT 1,
  
  -- Timestamps
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  
  PRIMARY KEY (`id`),
  KEY `idx_collection` (`collection_id`),
  KEY `idx_product` (`product_id`),
  KEY `idx_type` (`file_type`),
  
  FOREIGN KEY (`collection_id`) REFERENCES `product_collections`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`product_id`) REFERENCES `products`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- ============================================
-- 4. ADD COLLECTION REFERENCE TO EXISTING PRODUCTS
-- ============================================
-- Add collection_id to products table if not exists
ALTER TABLE `products` 
ADD COLUMN IF NOT EXISTS `collection_id` INT(11) DEFAULT NULL COMMENT 'Link to product_collections' AFTER `supplier_id`,
ADD KEY IF NOT EXISTS `idx_collection` (`collection_id`);

-- Add foreign key if not exists (may fail if already exists, that's OK)
-- ALTER TABLE `products` 
-- ADD CONSTRAINT `fk_product_collection` 
-- FOREIGN KEY (`collection_id`) REFERENCES `product_collections`(`id`) ON DELETE SET NULL;


-- ============================================
-- 5. VIEWS FOR EASY QUERYING
-- ============================================

-- View: Complete collection data with supplier info
CREATE OR REPLACE VIEW `v_collections_full` AS
SELECT 
    c.*,
    s.name AS supplier_full_name,
    s.logo AS supplier_logo,
    s.email AS supplier_email_full,
    s.phone AS supplier_phone_full,
    s.address AS supplier_address,
    (SELECT COUNT(*) FROM product_collection_items WHERE collection_id = c.id) AS items_count,
    (SELECT COUNT(*) FROM product_files WHERE collection_id = c.id) AS files_count
FROM product_collections c
LEFT JOIN suppliers s ON c.supplier_id = s.id;


-- View: Collection items with parent collection info
CREATE OR REPLACE VIEW `v_collection_items_full` AS
SELECT 
    i.*,
    c.name AS collection_name_full,
    c.brand AS collection_brand,
    c.supplier_name AS collection_supplier,
    p.name AS product_name_full,
    p.price AS product_price
FROM product_collection_items i
INNER JOIN product_collections c ON i.collection_id = c.id
LEFT JOIN products p ON i.product_id = p.id;


-- ============================================
-- 6. SAMPLE DATA (for testing)
-- ============================================

-- Sample: Alhambra TIERRA Collection
INSERT INTO `product_collections` 
(`name`, `slug`, `brand`, `description`, `material_type`, `applications`, `manufacturer_origin`, `website`, `supplier_name`, `supplier_location`, `supplier_phone`, `supplier_email`, `year_established`, `category`, `status`, `is_featured`)
VALUES
('TIERRA COLLECTION', 'alhambra-tierra-collection', 'Alhambra', 
'Thương hiệu Alhambra được thành lập tại Alicante, Tây Ban Nha vào năm 1977. Bạn có thể tìm thấy chúng tôi trên khắp thế giới.\n\nBộ sưu tập Tierra không chỉ là thiết kế: đó là hành trình tìm về cội nguồn, tôn vinh con người chúng ta và cả người chúng ta đã từng là. Các loại sợi tự nhiên như cotton, lanh và lên sợi lên sẽ sử chân thực và bền vững, trong khi lớp hoàn thiện của chúng làm mỏi bật vẻ đẹp của sự không hoàn hảo, gợi nhớ đến những cánh quan truyền cảm hứng cho chúng ta. Tierra là bộ sưu tập bao gồm các loại vải hoàn hảo cho đồ bọc, rèm cửa và đồ trang trí như của.',
'Vải bọc, mỏng, gối, nệm', 'Phòng khách, Phòng ngủ, Công trình dân dụng, Công trình thương mại',
'Spain', 'alhamabrafabrics.com',
'CÔNG TY TNHH QUẢN LÝ CHUỖI CUNG ỨNG EDSON (VIỆT NAM)',
'Hồ Chí Minh', '0979380068', 'info@homekhangroup.com',
'1977', 'vật liệu', 1, 1)
ON DUPLICATE KEY UPDATE updated_at = CURRENT_TIMESTAMP;

-- ============================================
-- COMPLETED MIGRATION
-- ============================================
