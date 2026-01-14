# 🧪 HƯỚNG DẪN TEST HỆ THỐNG

## ✅ BƯỚC 1: Setup Database (BẮT BUỘC)

### Cách 1: Chạy qua Web Browser (Khuyến nghị)
1. Đảm bảo XAMPP đang chạy (Apache + MySQL)
2. Truy cập: `http://localhost/vnmt/backend/setup_all_tables.php`
3. Kiểm tra kết quả:
   - ✅ Bảng `product_items` đã được tạo
   - ✅ Field `collection` đã được thêm vào `product_items`
   - ✅ Bảng `product_files` đã được tạo
   - ✅ Bảng `product_item_files` đã được tạo

### Cách 2: Import SQL thủ công
1. Mở phpMyAdmin: `http://localhost/phpmyadmin`
2. Chọn database của bạn
3. Click tab "SQL"
4. Copy nội dung file `backend/setup_database_complete.sql`
5. Paste và click "Go"

---

## ✅ BƯỚC 2: Test Scrape Sản Phẩm

1. **Truy cập Product Scraper:**
   ```
   http://localhost/vnmt/backend/product_scraper.php
   ```

2. **Paste URL sản phẩm có sản phẩm con:**
   Ví dụ: `https://vnbuilding.vn/vat-lieu/...` (URL có 3 sản phẩm con như trong hình)

3. **Click "Lấy thông tin"**

4. **Kiểm tra kết quả:**
   - ✅ Sản phẩm mẹ có đầy đủ thông tin (tên, mô tả, nhà cung cấp, etc.)
   - ✅ Có đúng **3 sản phẩm con** trong danh sách
   - ✅ Mỗi sản phẩm con có:
     - Mã sản phẩm (ví dụ: `65067LF-PC-ECO`)
     - Ảnh (URL)
     - Bộ sưu tập (Collection)
     - Hoàn thiện (Finishing)
     - Thành phần (Composition)
     - Kích thước (Width)
     - Bảo hành (Warranty)
     - Files (2D, 3D, TDS) - nếu có
   - ✅ Sản phẩm mẹ có Files/Catalogue - nếu có

5. **Nếu thiếu thông tin:**
   - Kiểm tra logs trong `error_log` hoặc browser console
   - Có thể cần cải thiện logic scrape (tùy cấu trúc HTML của từng trang)

---

## ✅ BƯỚC 3: Test Import vào Database

1. **Sau khi scrape thành công, click "Import vào Database"**

2. **Kiểm tra kết quả:**
   - ✅ Thông báo "Đã import thành công X sản phẩm"
   - ✅ Không có lỗi

3. **Kiểm tra trong Database (phpMyAdmin):**
   ```sql
   -- Kiểm tra sản phẩm mẹ
   SELECT id, name, slug FROM products ORDER BY id DESC LIMIT 1;
   
   -- Kiểm tra sản phẩm con
   SELECT * FROM product_items WHERE product_id = [ID_SẢN_PHẨM_MẸ];
   
   -- Kiểm tra files của sản phẩm mẹ
   SELECT * FROM product_files WHERE product_id = [ID_SẢN_PHẨM_MẸ];
   
   -- Kiểm tra files của sản phẩm con
   SELECT pif.*, pi.name as item_name 
   FROM product_item_files pif
   JOIN product_items pi ON pif.product_item_id = pi.id
   WHERE pi.product_id = [ID_SẢN_PHẨM_MẸ];
   ```

4. **Kiểm tra ảnh:**
   - ✅ Ảnh sản phẩm mẹ đã được download về `assets/images/products/`
   - ✅ Ảnh sản phẩm con đã được download về `assets/images/products/`
   - ✅ Trong database, field `image` chứa path local (không phải URL)

---

## ✅ BƯỚC 4: Test Hiển thị trên Frontend

1. **Truy cập trang chi tiết sản phẩm:**
   ```
   http://localhost/vnmt/product-detail.php?id=[ID_SẢN_PHẨM_MẸ]
   ```

2. **Kiểm tra các tab:**
   - ✅ Tab "Mô tả sản phẩm" - hiển thị thông tin sản phẩm mẹ
   - ✅ Tab "Thông số kỹ thuật" - hiển thị các thông số
   - ✅ Tab "Sản phẩm con (3)" - hiển thị đúng 3 sản phẩm con với:
     - Ảnh sản phẩm con (đã download)
     - Mã sản phẩm
     - Bộ sưu tập
     - Hoàn thiện
     - Thành phần
     - Kích thước
     - Bảo hành
     - Files (2D, 3D, TDS) - nếu có
   - ✅ Tab "Tài liệu" - hiển thị catalogue/files của sản phẩm mẹ (nếu có)

3. **Kiểm tra ảnh:**
   - ✅ Ảnh sản phẩm mẹ hiển thị đúng
   - ✅ Ảnh sản phẩm con hiển thị đúng (không bị lỗi 404)

---

## 🐛 XỬ LÝ LỖI THƯỜNG GẶP

### Lỗi: "Table doesn't exist"
- **Giải pháp:** Chạy lại `setup_all_tables.php`

### Lỗi: "Field 'collection' doesn't exist"
- **Giải pháp:** Script sẽ tự động thêm, hoặc chạy:
  ```sql
  ALTER TABLE product_items 
  ADD COLUMN collection VARCHAR(255) DEFAULT NULL 
  AFTER name;
  ```

### Lỗi: "Failed to download image"
- **Nguyên nhân:** URL ảnh không hợp lệ hoặc server chặn
- **Giải pháp:** Kiểm tra URL ảnh trong scraped data, có thể cần cải thiện logic scrape

### Không tìm thấy sản phẩm con
- **Nguyên nhân:** Logic scrape chưa phù hợp với cấu trúc HTML của trang
- **Giải pháp:** 
  1. Kiểm tra HTML source của trang
  2. Cải thiện XPath patterns trong `product_scraper.php`
  3. Kiểm tra logs để xem scrape được bao nhiêu items

### Ảnh không hiển thị trên frontend
- **Nguyên nhân:** Path ảnh sai hoặc file không tồn tại
- **Giải pháp:**
  1. Kiểm tra path trong database
  2. Kiểm tra file có tồn tại trong `assets/images/products/` không
  3. Kiểm tra permissions của thư mục

---

## 📝 CHECKLIST HOÀN CHỈNH

- [ ] Đã chạy `setup_all_tables.php` thành công
- [ ] Đã test scrape 1 sản phẩm có sản phẩm con
- [ ] Scrape được đúng số lượng sản phẩm con (3 items)
- [ ] Mỗi sản phẩm con có đầy đủ thông tin
- [ ] Đã import thành công vào database
- [ ] Kiểm tra database có đúng dữ liệu
- [ ] Ảnh đã được download về server
- [ ] Frontend hiển thị đúng sản phẩm con
- [ ] Files/Catalogue hiển thị đúng (nếu có)

---

## 🎯 BƯỚC TIẾP THEO SAU KHI TEST THÀNH CÔNG

1. **Scrape thêm nhiều sản phẩm khác** để đảm bảo logic hoạt động với nhiều loại trang
2. **Tối ưu hóa logic scrape** nếu cần (tùy cấu trúc HTML của từng trang)
3. **Cải thiện UI/UX** nếu cần
4. **Thêm validation** và error handling tốt hơn

---

## 📞 HỖ TRỢ

Nếu gặp lỗi, kiểm tra:
1. Error logs trong PHP
2. Browser console (F12)
3. Database logs
4. File permissions
