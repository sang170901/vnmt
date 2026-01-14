# 🎨 INTELLIGENT PRODUCT COLLECTION SCRAPER - Documentation

## 📋 Tổng Quan

Hệ thống crawl thông minh cho phép lấy **toàn bộ thông tin** từ trang sản phẩm collection trên vnbuilding.vn, được thiết kế để xử lý 3 phần chính:

### 🎯 3 Phần Dữ Liệu Chính

#### **PHẦN 1: Thông Tin Chung về Brand/Nhà Cung Cấp**
- Tên thương hiệu (Brand)
- Logo thương hiệu
- Thông tin nhà cung cấp tại Việt Nam
- Thông tin liên hệ (điện thoại, email, địa chỉ)

#### **PHẦN 2: Thông Tin Collection**
- Tên bộ sưu tập
- Mô tả chi tiết
- Loại vật liệu
- Ứng dụng
- Xuất xứ, năm thành lập
- **Link Catalog PDF** (quan trọng!)
- Website chính thức

#### **PHẦN 3: Danh Sách Sản Phẩm trong Collection**
- Tên từng sản phẩm (VD: PIEDRA 06, ORIGEN 06)
- Thông số kỹ thuật (Bộ sưu tập, Hoàn thiện, Thành phần, Kích thước)
- Hình ảnh sản phẩm
- Giá cả (nếu có)

---

## 🗄️ Cấu Trúc Database

### 1. Bảng `product_collections`
Lưu thông tin về bộ sưu tập/brand (Phần 1 & 2)

```sql
CREATE TABLE product_collections (
  id INT PRIMARY KEY AUTO_INCREMENT,
  name VARCHAR(255) NOT NULL,              -- Tên collection: "TIERRA COLLECTION"
  slug VARCHAR(255) UNIQUE NOT NULL,       -- alhambra-tierra-collection
  brand VARCHAR(255),                      -- Alhambra
  
  -- Collection Details
  description TEXT,                        -- Mô tả đầy đủ
  features TEXT,                           -- Điểm nổi bật
  material_type VARCHAR(255),              -- Vải bọc, mỏng, gối, nệm
  applications TEXT,                       -- Phòng khách, Phòng ngủ...
  construction_type VARCHAR(255),          -- Loại công trình
  
  -- Supplier Info (Nhà cung cấp VN)
  supplier_id INT,                         -- Link to suppliers table
  supplier_name VARCHAR(255),              -- CÔNG TY TNHH QUẢN LÝ CHUỖI...
  supplier_location VARCHAR(255),          -- Hồ Chí Minh
  supplier_phone VARCHAR(50),              -- 0979380068
  supplier_email VARCHAR(255),             -- info@homekhangroup.com
  
  -- Manufacturer (Nhà sản xuất gốc)
  manufacturer VARCHAR(255),               -- Alhambra
  manufacturer_origin VARCHAR(255),        -- Spain
  year_established VARCHAR(50),            -- 1977
  website VARCHAR(500),                    -- alhamabrafabrics.com
  
  -- Media
  featured_image VARCHAR(500),             -- Main image
  logo_image VARCHAR(500),                 -- Brand logo
  
  -- Metadata
  source_url VARCHAR(500),                 -- URL gốc từ vnbuilding.vn
  status TINYINT(1) DEFAULT 1,
  is_featured TINYINT(1) DEFAULT 0,
  
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);
```

### 2. Bảng `product_collection_items`
Lưu từng sản phẩm trong collection (Phần 3)

```sql
CREATE TABLE product_collection_items (
  id INT PRIMARY KEY AUTO_INCREMENT,
  collection_id INT NOT NULL,              -- Link to product_collections
  product_id INT,                          -- Link to products (optional)
  
  -- Product Info
  name VARCHAR(255) NOT NULL,              -- PIEDRA 06, ORIGEN 06
  sku VARCHAR(100),                        -- Mã sản phẩm
  slug VARCHAR(255) NOT NULL,              -- piedra-06
  
  -- Specifications
  collection_name VARCHAR(255),            -- TIERRA
  finish_type VARCHAR(255),                -- HƯỚNG MẪU UP ROADED
  composition VARCHAR(255),                -- 88% LI 12% CO
  width VARCHAR(100),                      -- W: 145 cm
  thickness VARCHAR(100),
  color VARCHAR(100),
  specifications TEXT,                     -- JSON: all specs
  
  -- Media & Display
  primary_image VARCHAR(500),
  display_order INT DEFAULT 0,             -- Thứ tự hiển thị
  status TINYINT(1) DEFAULT 1,
  
  created_at TIMESTAMP,
  updated_at TIMESTAMP,
  
  FOREIGN KEY (collection_id) REFERENCES product_collections(id) ON DELETE CASCADE
);
```

### 3. Bảng `product_files`
Lưu catalog PDF và tài liệu

```sql
CREATE TABLE product_files (
  id INT PRIMARY KEY AUTO_INCREMENT,
  collection_id INT,                       -- Link to collection
  product_id INT,                          -- Link to individual product
  
  -- File Details
  file_type VARCHAR(50) NOT NULL,          -- catalog, technical_sheet, datasheet
  file_name VARCHAR(255) NOT NULL,
  file_url VARCHAR(500) NOT NULL,          -- URL hoặc path local
  file_size VARCHAR(50),
  
  -- Metadata
  title VARCHAR(255),                      -- "Catalogue", "Technical Data Sheet"
  description TEXT,
  language VARCHAR(10) DEFAULT 'vi',       -- vi, en
  
  download_count INT DEFAULT 0,
  status TINYINT(1) DEFAULT 1,
  created_at TIMESTAMP,
  
  FOREIGN KEY (collection_id) REFERENCES product_collections(id) ON DELETE CASCADE
);
```

---

## 🚀 Cách Sử Dụng

### Bước 1: Chạy Migration
```bash
# Trong phpMyAdmin hoặc MySQL console
SOURCE backend/database/migration_product_collections.sql;
```

Hoặc qua PHP:
```php
require 'backend/inc/db.php';
$pdo = getPDO();
$sql = file_get_contents('backend/database/migration_product_collections.sql');
$pdo->exec($sql);
```

### Bước 2: Truy Cập Tool
```
http://localhost/vnmt/backend/fetch_product_collection.php
```

### Bước 3: Nhập URL Collection
Ví dụ:
```
https://vnbuilding.vn/vat-lieu/alhmabra-rd7ere
```

### Bước 4: Preview & Save
1. Click **"🔍 Crawl & Preview"** → Xem preview dữ liệu
2. Kiểm tra 3 phần:
   - ✅ Collection Information
   - ✅ Product Items (danh sách sản phẩm)
   - ✅ Catalog Files
3. Click **"💾 Save to Database"** → Lưu vào DB

---

## 🎯 Ví Dụ: Alhambra TIERRA Collection

### Input URL
```
https://vnbuilding.vn/vat-lieu/alhmabra-rd7ere
```

### Output Data Structure

#### ✅ Collection (1 record)
```php
[
  'name' => 'TIERRA COLLECTION',
  'slug' => 'alhambra-tierra-collection',
  'brand' => 'Alhambra',
  'description' => 'Thương hiệu Alhambra được thành lập tại Alicante...',
  'material_type' => 'Vải bọc, mỏng, gối, nệm',
  'applications' => 'Phòng khách, Phòng ngủ, Công trình dân dụng...',
  'supplier_name' => 'CÔNG TY TNHH QUẢN LÝ CHUỖI CUNG ỨNG EDSON',
  'supplier_location' => 'Hồ Chí Minh',
  'supplier_phone' => '0979380068',
  'supplier_email' => 'info@homekhangroup.com',
  'manufacturer_origin' => 'Spain',
  'year_established' => '1977',
  'website' => 'alhamabrafabrics.com',
  'featured_image' => '...',
  'logo_image' => '...',
]
```

#### ✅ Items (5+ records - PIEDRA 06, ORIGEN 06, ORIGEN 01, GAIA 10, GAIA 07)
```php
[
  [
    'name' => 'PIEDRA 06',
    'slug' => 'piedra-06',
    'collection_name' => 'TIERRA',
    'finish_type' => 'HƯỚNG MẪU UP ROADED',
    'composition' => '88% LI 12% CO',
    'width' => 'W: 145 cm',
    'primary_image' => '...',
    'specifications' => [
      'Bộ sưu tập' => 'TIERRA',
      'Hoàn thiện' => 'HƯỚNG MẪU UP ROADED',
      'Thành phần' => '88% LI 12% CO',
      'Kích thước' => 'W: 145 cm'
    ]
  ],
  [
    'name' => 'ORIGEN 06',
    'composition' => '100% SE',
    'width' => 'W: 135 cm',
    // ...
  ],
  // ... more items
]
```

#### ✅ Files (1+ records - Catalogue PDF)
```php
[
  [
    'file_type' => 'catalog',
    'file_name' => 'tierra-catalogue.pdf',
    'file_url' => 'https://...',
    'title' => 'Catalogue'
  ],
  [
    'file_type' => 'technical_sheet',
    'file_name' => 'technical-data-sheet.pdf',
    'file_url' => 'https://...',
    'title' => 'Technical Data Sheet (TDS)'
  ]
]
```

---

## 🔧 Tính Năng Thông Minh

### 1. **Auto-detect Brand & Collection**
```php
// Từ title: "Alhambra: TIERRA COLLECTION"
// → Brand: "Alhambra"
// → Collection: "TIERRA COLLECTION"
```

### 2. **Smart Field Mapping**
```php
$labelMap = [
  'Chức năng, Phân loại' => 'category',
  'Loại vật tư' => 'material_type',
  'Ứng dụng' => 'applications',
  'Nhà cung cấp' => 'supplier_name',
  'Điện thoại' => 'supplier_phone',
  'Email' => 'supplier_email',
  'Website' => 'website',
  // ... tự động ánh xạ
];
```

### 3. **Catalog Detection**
Tự động tìm:
- PDF links (`href` contains `.pdf`)
- Buttons/links với text: "Catalogue", "catalog", "Data Sheet", "Download"
- Phân loại: `catalog` vs `technical_sheet`

### 4. **Unique Slug Generation**
- Collection: `alhambra-tierra-collection`
- Items: `piedra-06`, `origen-06-1` (nếu trùng)

### 5. **Relationship Linking**
- Tự động link `supplier_id` nếu tìm thấy trong bảng `suppliers`
- Tạo reference giữa `product_collections` ↔ `product_collection_items` ↔ `product_files`

---

## 📊 Query Examples

### Lấy toàn bộ collection với items
```sql
SELECT 
  c.*,
  COUNT(i.id) as items_count,
  COUNT(f.id) as files_count
FROM product_collections c
LEFT JOIN product_collection_items i ON c.id = i.collection_id
LEFT JOIN product_files f ON c.id = f.collection_id
WHERE c.brand = 'Alhambra'
GROUP BY c.id;
```

### Lấy tất cả items của một collection
```sql
SELECT * FROM product_collection_items
WHERE collection_id = 1
ORDER BY display_order ASC;
```

### Lấy catalog files
```sql
SELECT * FROM product_files
WHERE collection_id = 1 AND file_type IN ('catalog', 'technical_sheet');
```

### View có sẵn
```sql
-- Dùng view đã tạo sẵn
SELECT * FROM v_collections_full WHERE brand = 'Alhambra';
SELECT * FROM v_collection_items_full WHERE collection_id = 1;
```

---

## 🎨 Frontend Display Example

### Display Collection Page
```php
<?php
$collectionId = 1;
$pdo = getPDO();

// Get collection
$collection = $pdo->query("
  SELECT * FROM product_collections WHERE id = $collectionId
")->fetch();

// Get items
$items = $pdo->query("
  SELECT * FROM product_collection_items 
  WHERE collection_id = $collectionId 
  ORDER BY display_order
")->fetchAll();

// Get files
$files = $pdo->query("
  SELECT * FROM product_files 
  WHERE collection_id = $collectionId
")->fetchAll();
?>

<div class="collection-page">
  <!-- Header with brand logo -->
  <div class="collection-header">
    <img src="<?= $collection['logo_image'] ?>" alt="<?= $collection['brand'] ?>">
    <h1><?= $collection['name'] ?></h1>
  </div>
  
  <!-- Collection Info -->
  <div class="collection-info">
    <p><?= $collection['description'] ?></p>
    <div class="specs">
      <span>Material: <?= $collection['material_type'] ?></span>
      <span>Origin: <?= $collection['manufacturer_origin'] ?></span>
    </div>
  </div>
  
  <!-- Catalog Downloads -->
  <div class="downloads">
    <?php foreach ($files as $file): ?>
    <a href="<?= $file['file_url'] ?>" class="download-btn">
      📄 <?= $file['title'] ?>
    </a>
    <?php endforeach; ?>
  </div>
  
  <!-- Product Items Grid -->
  <div class="products-grid">
    <?php foreach ($items as $item): ?>
    <div class="product-card">
      <img src="<?= $item['primary_image'] ?>">
      <h3><?= $item['name'] ?></h3>
      <p><?= $item['composition'] ?></p>
      <p>Size: <?= $item['width'] ?></p>
    </div>
    <?php endforeach; ?>
  </div>
  
  <!-- Supplier Contact -->
  <div class="contact">
    <h3>Nhà cung cấp tại Việt Nam</h3>
    <p><?= $collection['supplier_name'] ?></p>
    <p>📞 <?= $collection['supplier_phone'] ?></p>
    <p>✉️ <?= $collection['supplier_email'] ?></p>
  </div>
</div>
```

---

## 🔄 Workflow Diagram

```
┌─────────────────────────────────────────────────────┐
│  INPUT: https://vnbuilding.vn/vat-lieu/alhambra... │
└──────────────────┬──────────────────────────────────┘
                   │
                   ▼
         ┌─────────────────────┐
         │  SCRAPE PAGE HTML   │
         └──────────┬──────────┘
                    │
        ┌───────────┴───────────┐
        │                       │
        ▼                       ▼
┌───────────────┐      ┌────────────────┐
│ SECTION 1 & 2 │      │   SECTION 3    │
│  Collection   │      │  Product Items │
│    Info       │      │     List       │
└───────┬───────┘      └────────┬───────┘
        │                       │
        │              ┌────────┴────────┐
        │              │                 │
        ▼              ▼                 ▼
┌─────────────┐  ┌──────────┐    ┌──────────┐
│  Catalog    │  │  Item 1  │    │  Item N  │
│    Files    │  │ (specs)  │... │ (specs)  │
└──────┬──────┘  └─────┬────┘    └────┬─────┘
       │               │              │
       └───────────────┴──────────────┘
                       │
                       ▼
          ┌────────────────────────┐
          │  PREVIEW IN BROWSER    │
          │  (3 sections visible)  │
          └───────────┬────────────┘
                      │
                      ▼
          ┌────────────────────────┐
          │  SAVE TO DATABASE      │
          │  - product_collections │
          │  - collection_items    │
          │  - product_files       │
          └────────────────────────┘
```

---

## ⚠️ Lưu Ý Quan Trọng

1. **Unique Constraints**
   - `product_collections.slug` phải unique
   - `product_collection_items` có unique key `(collection_id, slug)`

2. **Image Handling**
   - Hình ảnh có thể download về server hoặc giữ URL gốc
   - Catalog PDF nên download về `/assets/catalogs/`

3. **Supplier Linking**
   - Tự động tìm `supplier_id` từ bảng `suppliers`
   - Nếu không có, tạo mới supplier sau

4. **Data Validation**
   - Phải có `name` cho collection và items
   - URL phải hợp lệ

5. **Performance**
   - Batch insert cho nhiều items
   - Transaction để đảm bảo data integrity

---

## 🎯 Kết Luận

Hệ thống này giải quyết được bài toán:
- ✅ Crawl thông minh 3 phần dữ liệu
- ✅ Tổ chức database logic, dễ query
- ✅ Linh hoạt: 1 collection → nhiều items → nhiều files
- ✅ Mở rộng: Có thể link với bảng `products` chính
- ✅ SEO-friendly: Có slug, meta, source_url

**Use Cases:**
- Quản lý bộ sưu tập sản phẩm theo brand
- Catalog management system
- Product comparison tools
- Multi-language product database
- Supplier relationship management

---

Created: 2025-12-10  
Author: GitHub Copilot  
Version: 1.0
