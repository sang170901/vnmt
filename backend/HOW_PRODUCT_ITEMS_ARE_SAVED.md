# Cách Sản Phẩm Con Được Lưu Trong Hệ Thống

## 📊 Cấu Trúc Database

### 1. Bảng `products` (Sản phẩm chính)
- Lưu thông tin sản phẩm chính
- Có field `collection_id` để link đến collection
- **Quan hệ:** 1 product → 1 collection (optional)

### 2. Bảng `product_collections` (Bộ sưu tập)
- Lưu thông tin collection/bộ sưu tập
- Chứa thông tin chung của collection (name, brand, supplier, etc.)
- **Quan hệ:** 1 collection → Nhiều items

### 3. Bảng `product_collection_items` (Sản phẩm con)
- Lưu các sản phẩm con/items trong collection
- **Quan hệ:** N items → 1 collection (via `collection_id`)

### 4. Bảng `product_collection_files` (Files tài liệu)
- Lưu các file tài liệu của collection (2D, 3D, TDS, etc.)

## 🔄 Flow Lưu Dữ Liệu

### Bước 1: Scrape (product_scraper.php)
```
URL vnbuilding.vn → Scrape HTML
    ↓
Tìm các items (h3, h4, product cards)
    ↓
Lưu vào session: $_SESSION['scraped_products'][index]['items'] = [...]
```

**Dữ liệu scrape được:**
- `name`: Tên sản phẩm con (ví dụ: "VIVIENNE", "SOL", "SAKI")
- `image` / `primary_image`: URL ảnh
- `composition`: Thành phần
- `width`: Kích thước
- `finishing`: Hoàn thiện
- `color`: Màu sắc
- `collection`: Bộ sưu tập
- `price`: Giá
- `warranty`: Bảo hành
- `files`: Tài liệu (2D, 3D, TDS)

### Bước 2: Import (import_products.php)

#### 2.1. Tạo Collection
```sql
INSERT INTO product_collections (
    name,                    -- Tên collection (lấy từ product name)
    brand,                   -- Thương hiệu
    supplier_name,           -- Tên nhà cung cấp
    supplier_phone,          -- SĐT nhà cung cấp
    manufacturer_origin,     -- Xuất xứ
    items_count,             -- Số lượng items
    files_count,             -- Số lượng files
    created_at
) VALUES (...)
```

**Lấy `collectionId = lastInsertId()`**

#### 2.2. Link Product → Collection
```sql
UPDATE products 
SET collection_id = ? 
WHERE id = ?
```

#### 2.3. Insert Items vào `product_collection_items`
```sql
INSERT INTO product_collection_items (
    collection_id,           -- FK → product_collections.id
    name,                    -- Tên item
    slug,                    -- URL slug (tự động tạo từ name)
    composition,             -- Thành phần
    width,                   -- Kích thước
    finishing,               -- Hoàn thiện (hoặc finish_type)
    warranty,                -- Bảo hành
    price,                   -- Giá
    primary_image,           -- Ảnh chính (đã download về server)
    thumbnail,               -- Thumbnail (dùng primary_image)
    display_order,           -- Thứ tự hiển thị (itemIndex + 1)
    created_at
) VALUES (...)
```

**Lưu ý:**
- **Ảnh items**: Được download về server và lưu path local
  - Path: `assets/images/products/{slug}-item-{item-slug}-{timestamp}.jpg`
  - Chỉ lưu nếu download thành công
- **Fields động**: Chỉ insert fields có tồn tại trong table (kiểm tra bằng `SHOW COLUMNS`)

#### 2.4. Insert Files (nếu có)
```sql
INSERT INTO product_collection_files (
    collection_id,           -- FK → product_collections.id
    file_type,               -- Loại file (2D, 3D, TDS, etc.)
    file_name,               -- Tên file
    file_url,                -- URL hoặc path local
    created_at
) VALUES (...)
```

## 📋 Dữ Liệu Được Lưu

### Collection (product_collections)
| Field | Giá trị |
|-------|---------|
| `name` | Tên sản phẩm chính (từ scraped data) |
| `brand` | Thương hiệu |
| `supplier_name` | Tên nhà cung cấp |
| `manufacturer_origin` | Xuất xứ |
| `items_count` | Số lượng items |
| `files_count` | Số lượng files |

### Item (product_collection_items)
| Field | Giá trị | Nguồn |
|-------|---------|-------|
| `collection_id` | ID của collection | Từ `lastInsertId()` |
| `name` | "VIVIENNE", "SOL", etc. | Scraped từ HTML |
| `slug` | "vivienne", "sol" | Tự động tạo từ name |
| `composition` | "88% LI 12% CO" | Scraped |
| `width` | "W: 145 cm" | Scraped |
| `finishing` | "HƯỚNG MẪU..." | Scraped |
| `primary_image` | "assets/images/products/..." | **Download về server** |
| `thumbnail` | Cùng với primary_image | |
| `display_order` | 1, 2, 3... | itemIndex + 1 |

## 🔗 Quan Hệ Giữa Các Bảng

```
products (1) ──collection_id──→ (1) product_collections (1) ──collection_id──→ (N) product_collection_items
                                                                                        │
                                                                                        │ (optional)
                                                                                        ↓
                                                                                   products (nếu item được tạo thành product riêng)
```

## ⚠️ Lưu Ý Quan Trọng

1. **Ảnh items PHẢI được download về server**
   - Không lưu URL gốc
   - Chỉ lưu path local nếu download thành công
   - Nếu download fail → không lưu ảnh

2. **Dynamic Fields**
   - Code tự động kiểm tra columns có tồn tại không
   - Chỉ insert fields có sẵn trong table
   - Tránh lỗi khi table structure khác nhau

3. **Collection được tạo chỉ khi:**
   - Product có items (`!empty($product['items'])`)
   - Table `product_collections` tồn tại

4. **Link Product → Collection chỉ khi:**
   - Table `products` có field `collection_id`
   - Collection được tạo thành công

## 🐛 Debug

Để kiểm tra items đã được lưu:
```sql
-- Xem collections
SELECT * FROM product_collections;

-- Xem items của collection
SELECT * FROM product_collection_items WHERE collection_id = ?;

-- Xem product có collection_id chưa
SELECT id, name, collection_id FROM products WHERE id = ?;
```

Hoặc dùng debug script:
```
http://localhost/vnmt/backend/debug_product_items.php?id=142
```

## 📝 Ví Dụ Thực Tế

**Sản phẩm:** "Longhi Armchairs"
**Items scraped:** ["VIVIENNE", "SOL", "SAKI", "PEARL"]

**Kết quả trong DB:**

```
product_collections:
  id: 1
  name: "Longhi Armchairs"
  items_count: 4

products:
  id: 142
  name: "Longhi Armchairs"
  collection_id: 1

product_collection_items:
  id: 1, collection_id: 1, name: "VIVIENNE", display_order: 1
  id: 2, collection_id: 1, name: "SOL", display_order: 2
  id: 3, collection_id: 1, name: "SAKI", display_order: 3
  id: 4, collection_id: 1, name: "PEARL", display_order: 4
```
