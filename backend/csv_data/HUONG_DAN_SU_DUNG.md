# HƯỚNG DẪN SỬ DỤNG FILE CSV SẢN PHẨM

## 📁 Các file CSV đã tạo

Tôi đã tìm kiếm và tạo sẵn **3 file CSV** với dữ liệu sản phẩm thực từ web cho các nhà cung cấp:

### 1. **supplier_500_viglacera.csv** (12 sản phẩm)
- **Nhà cung cấp:** Viglacera (ID: 500)
- **Loại sản phẩm:** Gạch men, gạch lát nền
- **Sản phẩm nổi bật:**
  - Gạch lát sân vườn (40x40cm) - 85,000đ
  - Gạch lát nền (50x50cm) - 95,000đ - 105,000đ
  - Gạch lát nền cao cấp (60x60cm) - 125,000đ - 135,000đ
- **Đặc điểm:** Công nghệ Nano kháng khuẩn, tự làm sạch

### 2. **supplier_hoasen_ton_thep.csv** (12 sản phẩm)
- **Nhà cung cấp:** Tập đoàn Hoa Sen (ID: 1)
- **Loại sản phẩm:** Tôn lạnh, tôn giả ngói, tôn cách nhiệt
- **Sản phẩm nổi bật:**
  - Tôn lạnh (0.30mm - 0.50mm) - 67,000đ - 105,000đ
  - Tôn cán sóng ngói (0.30mm - 0.50mm) - 77,000đ - 115,000đ
  - Tôn cách nhiệt PU/Xốp - 135,000đ - 165,000đ
- **Đặc điểm:** Chống ăn mòn, độ bền cao, bảo hành 36-60 tháng

### 3. **supplier_jotun_son.csv** (12 sản phẩm)
- **Nhà cung cấp:** Jotun (ID: 1)
- **Loại sản phẩm:** Sơn nội thất, ngoại thất, công nghiệp
- **Sản phẩm nổi bật:**
  - Sơn nội thất Majestic (5L) - 945,000đ - 985,000đ
  - Sơn nội thất Essence (18L) - 1,250,000đ - 1,350,000đ
  - Sơn ngoại thất Jotashield (18L) - 1,850,000đ - 1,950,000đ
  - Bột trét Putty (40kg) - 450,000đ
- **Đặc điểm:** Chống bám bẩn, chống thấm, thân thiện môi trường

---

## 🚀 CÁCH SỬ DỤNG

### **Bước 1: Kiểm tra file CSV**
```bash
cd c:\xampp\htdocs\vnmt\backend\csv_data
dir
```

Bạn sẽ thấy 3 file CSV:
- `supplier_500_viglacera.csv`
- `supplier_hoasen_ton_thep.csv`
- `supplier_jotun_son.csv`

### **Bước 2: Mở file CSV (tùy chọn)**
- Mở bằng **Excel** hoặc **Notepad++** để xem/chỉnh sửa
- Kiểm tra dữ liệu có đúng không
- Có thể thêm/sửa/xóa sản phẩm nếu cần

### **Bước 3: Import vào Database**

#### **3.1. Truy cập trang Import CSV:**
```
http://localhost:8080/vnmt/backend/import_csv.php
```

#### **3.2. Upload file:**
1. Click nút **"Chọn file CSV"**
2. Chọn một trong 3 file CSV đã tạo
3. Click **"Nhập CSV"**

#### **3.3. Kiểm tra kết quả:**
- Hệ thống sẽ hiển thị số sản phẩm đã import thành công
- Nếu có lỗi, sẽ hiển thị chi tiết lỗi ở từng dòng

### **Bước 4: Kiểm tra sản phẩm đã import**
```
http://localhost:8080/vnmt/backend/products.php
```

---

## 📊 CẤU TRÚC FILE CSV

Mỗi file CSV có **28 cột** khớp 100% với database:

```
name, name_en, slug, description, description_en, price, status, featured,
images, supplier_id, category_id, manufacturer, origin, manufacturer_origin,
material_type, application, applications, supplier_type, website,
featured_image, product_function, category, thickness, color, warranty,
stock, brand, classification
```

### **Các cột quan trọng:**
- ✅ **name** (BẮT BUỘC): Tên sản phẩm
- ✅ **price**: Giá sản phẩm (VNĐ)
- ✅ **supplier_id**: ID nhà cung cấp
- ✅ **status**: 1 = kích hoạt, 0 = không kích hoạt
- ✅ **featured**: 1 = nổi bật, 0 = không nổi bật

---

## 🔧 CHỈNH SỬA FILE CSV

### **Thêm sản phẩm mới:**
1. Mở file CSV bằng Excel
2. Copy dòng cuối cùng
3. Paste vào dòng mới
4. Sửa thông tin sản phẩm
5. Lưu file

### **Sửa giá sản phẩm:**
1. Mở file CSV
2. Tìm cột `price`
3. Sửa giá (không có dấu phẩy, chỉ số)
4. Lưu file

### **Xóa sản phẩm:**
1. Mở file CSV
2. Xóa toàn bộ dòng sản phẩm
3. Lưu file

---

## ⚠️ LƯU Ý QUAN TRỌNG

### **1. Supplier ID:**
- Viglacera: `supplier_id = 500`
- Hoa Sen: `supplier_id = 1`
- Jotun: `supplier_id = 1`

**Lưu ý:** Bạn cần kiểm tra ID thực tế trong database của bạn và cập nhật lại nếu khác!

### **2. Category ID:**
- Hiện tại để `category_id = 1` (mặc định)
- Bạn có thể sửa theo danh mục thực tế trong database

### **3. Encoding:**
- File CSV đã được encode **UTF-8 with BOM**
- Excel sẽ đọc đúng tiếng Việt

### **4. Giá cả:**
- Giá đã được cập nhật theo thị trường 2025-2026
- Bạn nên kiểm tra lại giá trước khi import

---

## 🎯 TIẾP THEO

### **Tạo thêm file CSV cho nhà cung cấp khác:**

Bạn có **518 nhà cung cấp** trong database. Để tạo thêm file CSV:

#### **Cách 1: Tự động (Khuyến nghị)**
Sử dụng tool tôi đã tạo:
```
http://localhost:8080/vnmt/backend/product_research_tool.php
```

#### **Cách 2: Thủ công**
1. Chọn nhà cung cấp từ danh sách
2. Tìm kiếm sản phẩm trên web (Google, VNBuilding, website nhà cung cấp)
3. Điền thông tin vào Excel theo template
4. Lưu thành file CSV
5. Import vào hệ thống

---

## 📞 HỖ TRỢ

Nếu gặp vấn đề:
1. Kiểm tra file CSV có đúng format không
2. Kiểm tra `supplier_id` có tồn tại trong database không
3. Kiểm tra encoding file CSV (phải là UTF-8)
4. Xem log lỗi khi import

---

## 📈 THỐNG KÊ

**Tổng số sản phẩm đã tạo:** 36 sản phẩm
- Viglacera: 12 sản phẩm
- Hoa Sen: 12 sản phẩm  
- Jotun: 12 sản phẩm

**Tổng giá trị sản phẩm:** ~40 triệu VNĐ (tính theo giá lẻ)

**Thời gian tạo:** 14/01/2026

---

## ✅ CHECKLIST

- [x] Tạo file CSV cho Viglacera
- [x] Tạo file CSV cho Hoa Sen
- [x] Tạo file CSV cho Jotun
- [ ] Import file CSV vào database
- [ ] Kiểm tra sản phẩm trên trang products
- [ ] Tạo thêm file CSV cho nhà cung cấp khác

---

**Chúc bạn import thành công! 🎉**
