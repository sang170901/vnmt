# 🎨 Product Collection Scraper - Quick Start

## Tổng Quan

Hệ thống crawl **thông minh** cho phép lấy toàn bộ thông tin từ trang sản phẩm collection trên vnbuilding.vn, chia làm **3 phần chính**:

### 📦 3 Phần Dữ Liệu

| Phần | Nội Dung | Ví Dụ |
|------|----------|-------|
| **1️⃣ Brand/Supplier** | Thương hiệu, nhà cung cấp, logo | Alhambra, EDSON Vietnam |
| **2️⃣ Collection Details** | Bộ sưu tập, mô tả, catalog PDF | TIERRA COLLECTION + catalog.pdf |
| **3️⃣ Product Items** | Danh sách sản phẩm chi tiết | PIEDRA 06, ORIGEN 06... |

---

## 🚀 Cài Đặt Nhanh (3 Bước)

### Bước 1: Chạy Setup
```
http://localhost/vnmt/backend/database/setup_product_collections.php
```
→ Tạo 3 bảng: `product_collections`, `product_collection_items`, `product_files`

### Bước 2: Mở Tool
```
http://localhost/vnmt/backend/fetch_product_collection.php
```

### Bước 3: Test với URL
Nhập URL:
```
https://vnbuilding.vn/vat-lieu/alhmabra-rd7ere
```

Click **"🔍 Crawl & Preview"** → Xem dữ liệu → Click **"💾 Save to Database"**

---

## 📊 Database Schema

### Bảng Chính

#### 1. `product_collections` (Brand/Collection Info)
```sql
- id, name, slug, brand
- description, features
- supplier_name, supplier_phone, supplier_email
- manufacturer_origin, website
- featured_image, logo_image
```

#### 2. `product_collection_items` (Product Items)
```sql
- id, collection_id
- name, sku, slug
- collection_name, finish_type, composition, width
- specifications (JSON), primary_image
```

#### 3. `product_files` (Catalogs & Documents)
```sql
- id, collection_id
- file_type (catalog/technical_sheet)
- file_url, title
```

---

## 📁 Files Created

```
backend/
├── database/
│   ├── migration_product_collections.sql    # SQL migration
│   ├── setup_product_collections.php        # Web installer
│   └── PRODUCT_COLLECTION_SCRAPER_DOCS.md   # Full docs
└── fetch_product_collection.php             # Main tool
```

---

## 💡 Ví Dụ Sử Dụng

### Input
```
URL: https://vnbuilding.vn/vat-lieu/alhmabra-rd7ere
```

### Output

**Collection:**
- Name: `TIERRA COLLECTION`
- Brand: `Alhambra`
- Origin: `Spain` (1977)
- Supplier: `CÔNG TY TNHH... EDSON (VIỆT NAM)`
- Phone: `0979380068`
- Catalog: `catalogue.pdf`

**Items (5+):**
1. PIEDRA 06 - 88% LI 12% CO - W: 145 cm
2. ORIGEN 06 - 100% SE - W: 135 cm
3. ORIGEN 01 - 100% SE - W: 135 cm
4. GAIA 10 - 50% CO 20% WO 20% CV 5% PB 5% OF
5. GAIA 07 - 50% CO 20% WO 20% CV 5% PB 5% OF

---

## 🎯 Query Examples

```sql
-- Lấy collection với số lượng items
SELECT c.*, COUNT(i.id) as items_count
FROM product_collections c
LEFT JOIN product_collection_items i ON c.id = i.collection_id
WHERE c.brand = 'Alhambra'
GROUP BY c.id;

-- Lấy tất cả items của collection
SELECT * FROM product_collection_items
WHERE collection_id = 1
ORDER BY display_order;

-- Lấy catalog files
SELECT * FROM product_files
WHERE collection_id = 1 AND file_type = 'catalog';
```

---

## 🔧 Tính Năng Thông Minh

✅ **Auto-detect brand** từ title (Alhambra: TIERRA COLLECTION)  
✅ **Smart field mapping** (Ứng dụng → applications)  
✅ **Catalog detection** (tìm PDF links tự động)  
✅ **Unique slug generation** (alhambra-tierra-collection)  
✅ **Supplier linking** (tự động link với bảng suppliers)  
✅ **Preview before save** (xem dữ liệu trước khi lưu)  

---

## 📖 Documentation

Đọc full docs: `backend/database/PRODUCT_COLLECTION_SCRAPER_DOCS.md`

---

## ⚙️ Tech Stack

- **Language:** PHP 7.4+
- **Database:** MySQL/MariaDB
- **Libraries:** DOMDocument, DOMXPath
- **Frontend:** HTML5, CSS3, Vanilla JS

---

## 📞 Support

- **Issues:** Check error logs in browser console
- **Database:** Verify tables with `SHOW TABLES;`
- **Docs:** Read full documentation in DOCS.md

---

**Created:** 2025-12-10  
**Version:** 1.0  
**Author:** GitHub Copilot

---

## 🎓 Learning Resources

Phân tích từ trang mẫu:
- https://vnbuilding.vn/vat-lieu/alhmabra-rd7ere

Cấu trúc trang:
```
┌─────────────────────────────────┐
│  Header: Brand Logo + Name      │  ← Phần 1
├─────────────────────────────────┤
│  Thông tin chung:               │
│  - Nhà sản xuất: Spain          │
│  - Nhà cung cấp: EDSON VN       │  ← Phần 2
│  - Email, Phone, Website        │
│  - Link Catalogue (PDF)         │
├─────────────────────────────────┤
│  Danh sách sản phẩm:            │
│  [PIEDRA 06] [ORIGEN 06] ...    │  ← Phần 3
│   └─ Specs (size, composition)  │
└─────────────────────────────────┘
```

---

🎉 **Happy Scraping!**
