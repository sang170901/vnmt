# HƯỚNG DẪN TẢI VÀ QUẢN LÝ HÌNH ẢNH SẢN PHẨM

## 🎯 Mục tiêu

Hệ thống tự động:
1. ✅ Tải hình ảnh từ web về máy local
2. ✅ Đặt tên file theo slug sản phẩm
3. ✅ Lưu vào thư mục đúng cấu trúc
4. ✅ Cập nhật CSV với đường dẫn hình ảnh
5. ✅ Database gọi đúng hình ảnh khi hiển thị

---

## 📁 Cấu trúc thư mục

```
vnmt/
├── images/
│   └── products/
│       ├── 24/                          # A2 Sweden
│       │   ├── khoa-thong-minh-a2-sweden-face-id.jpg
│       │   ├── khoa-thong-minh-a2-sweden-van-tay.jpg
│       │   └── ...
│       ├── 25/                          # ABTECH
│       │   ├── san-nang-ky-thuat-abtech-hpl.jpg
│       │   └── ...
│       └── 27/                          # ATC STONE
│           ├── da-granite-atc-stone.jpg
│           └── ...
└── backend/
    ├── auto_download_images.php         # Script tự động tải hình
    ├── image_urls_mapping.php           # Mapping URL hình ảnh
    └── csv_data/
        ├── supplier_24_a2_sweden.csv    # CSV đã có đường dẫn hình
        ├── supplier_25_abtech.csv
        └── supplier_27_atc_stone.csv
```

---

## 🚀 CÁCH SỬ DỤNG

### **Bước 1: Chạy script tải hình ảnh**

```bash
cd c:\xampp\htdocs\vnmt\backend
php auto_download_images.php
```

**Kết quả:**
```
╔════════════════════════════════════════════════════════╗
║     TỰ ĐỘNG TẢI HÌNH ẢNH SẢN PHẨM                    ║
╚════════════════════════════════════════════════════════╝

╔════════════════════════════════════════════════════════╗
║  Nhà cung cấp: A2 Sweden Vietnam (ID: 24)
╚════════════════════════════════════════════════════════╝

[1] Khóa thông minh A2 Sweden Face ID
    Slug: khoa-thong-minh-a2-sweden-face-id
    Đang tải: https://...
    ✅ Đã lưu: khoa-thong-minh-a2-sweden-face-id.jpg (245678 bytes)

...

✅ Hoàn thành: 36 hình ảnh / 36 sản phẩm
```

### **Bước 2: Kiểm tra hình ảnh đã tải**

```bash
dir c:\xampp\htdocs\vnmt\images\products\24
dir c:\xampp\htdocs\vnmt\images\products\25
dir c:\xampp\htdocs\vnmt\images\products\27
```

### **Bước 3: Kiểm tra CSV đã cập nhật**

Mở file CSV, cột `images` và `featured_image` sẽ có giá trị:
```
/images/products/24/khoa-thong-minh-a2-sweden-face-id.jpg
```

### **Bước 4: Import CSV vào database**

```
http://localhost:8080/vnmt/backend/import_csv.php
```

Upload file CSV → Hệ thống sẽ lưu đường dẫn hình ảnh vào database

### **Bước 5: Kiểm tra hiển thị trên web**

```
http://localhost:8080/vnmt/backend/products.php
```

Hình ảnh sẽ hiển thị từ đường dẫn: `/images/products/{supplier_id}/{slug}.jpg`

---

## 🔧 TÙY CHỈNH URL HÌNH ẢNH

### **Thêm URL hình ảnh cho sản phẩm mới:**

Mở file `backend/image_urls_mapping.php`:

```php
return [
    '24' => [
        'slug-san-pham-moi' => [
            'https://example.com/image1.jpg',
            'https://example.com/image2.jpg',  // Hình phụ
        ],
    ],
];
```

### **Tìm URL hình ảnh từ website:**

1. **Truy cập website nhà cung cấp**
2. **Tìm sản phẩm** tương ứng
3. **Click chuột phải vào hình ảnh** → "Copy image address"
4. **Paste URL** vào file `image_urls_mapping.php`

### **Sử dụng hình ảnh từ Unsplash (miễn phí):**

```
https://images.unsplash.com/photo-{id}?w=800
```

Ví dụ:
- Đá granite: `https://images.unsplash.com/photo-1615529182904-14819c35db37?w=800`
- Khóa cửa: `https://images.unsplash.com/photo-1558618666-fcd25c85cd64?w=800`

---

## 📊 ĐỊNH DẠNG ĐƯỜNG DẪN TRONG DATABASE

### **Cột `images` (nhiều hình):**
```
/images/products/24/khoa-face-id.jpg,/images/products/24/khoa-face-id-1.jpg
```
Phân cách bằng dấu phẩy

### **Cột `featured_image` (hình đại diện):**
```
/images/products/24/khoa-face-id.jpg
```
Chỉ 1 hình

---

## 🎨 HIỂN THỊ HÌNH ẢNH TRÊN WEB

### **PHP:**
```php
<?php
$product = getProduct($id);
$image_path = $product['featured_image'];
?>
<img src="<?= $image_path ?>" alt="<?= $product['name'] ?>">
```

### **HTML:**
```html
<img src="/images/products/24/khoa-face-id.jpg" alt="Khóa Face ID">
```

### **Với nhiều hình:**
```php
<?php
$images = explode(',', $product['images']);
foreach ($images as $image) {
    echo "<img src='$image' alt='{$product['name']}'>";
}
?>
```

---

## ⚠️ LƯU Ý QUAN TRỌNG

### **1. Quyền truy cập thư mục:**
```bash
chmod -R 755 c:\xampp\htdocs\vnmt\images\products
```

### **2. Dung lượng hình ảnh:**
- Khuyến nghị: **< 500KB** mỗi hình
- Tối đa: **2MB**

### **3. Định dạng hình ảnh:**
- Hỗ trợ: **JPG, PNG, WEBP**
- Khuyến nghị: **JPG** (nhẹ, tốc độ tải nhanh)

### **4. Kích thước hình ảnh:**
- Hình đại diện: **800x600px**
- Hình chi tiết: **1200x900px**
- Thumbnail: **300x300px**

### **5. Tên file:**
- Sử dụng **slug** (không dấu, chữ thường, dấu gạch ngang)
- Ví dụ: `khoa-thong-minh-a2-sweden-face-id.jpg`
- **KHÔNG** dùng: `Khóa Thông Minh A2.jpg` ❌

---

## 🔍 KIỂM TRA VÀ DEBUG

### **Kiểm tra hình ảnh đã tải:**
```bash
php -r "echo file_exists('c:/xampp/htdocs/vnmt/images/products/24/khoa-face-id.jpg') ? 'OK' : 'NOT FOUND';"
```

### **Kiểm tra kích thước file:**
```bash
php -r "echo filesize('c:/xampp/htdocs/vnmt/images/products/24/khoa-face-id.jpg') . ' bytes';"
```

### **Kiểm tra CSV đã cập nhật:**
```bash
php -r "
$csv = fopen('backend/csv_data/supplier_24_a2_sweden.csv', 'r');
fgetcsv($csv); // Skip header
$row = fgetcsv($csv);
echo 'Images: ' . $row[8] . PHP_EOL;
echo 'Featured: ' . $row[19] . PHP_EOL;
"
```

---

## 🐛 XỬ LÝ LỖI

### **Lỗi: "Failed to download image"**
- Kiểm tra URL có đúng không
- Kiểm tra kết nối internet
- Thử tải thủ công bằng trình duyệt

### **Lỗi: "Permission denied"**
```bash
chmod -R 755 c:\xampp\htdocs\vnmt\images
```

### **Lỗi: "Image not found on web"**
- Hình ảnh không hiển thị trên trang sản phẩm
- Kiểm tra đường dẫn trong database
- Kiểm tra file có tồn tại không

### **Hình ảnh bị vỡ:**
- Kiểm tra URL trong CSV
- Kiểm tra file có bị corrupt không
- Tải lại hình ảnh

---

## 📈 TỐI ƯU HÓA

### **1. Nén hình ảnh:**
```bash
# Sử dụng ImageMagick
convert input.jpg -quality 85 -resize 800x600 output.jpg
```

### **2. Chuyển sang WebP:**
```bash
cwebp -q 80 input.jpg -o output.webp
```

### **3. Lazy loading:**
```html
<img src="/images/products/24/khoa.jpg" loading="lazy" alt="Khóa">
```

### **4. CDN (tùy chọn):**
- Upload hình lên Cloudinary, Imgur
- Cập nhật URL trong CSV

---

## ✅ CHECKLIST

- [ ] Chạy script `auto_download_images.php`
- [ ] Kiểm tra thư mục `images/products/{supplier_id}/`
- [ ] Kiểm tra CSV đã có đường dẫn hình ảnh
- [ ] Import CSV vào database
- [ ] Kiểm tra hiển thị trên web
- [ ] Tối ưu hóa kích thước hình ảnh (nếu cần)

---

## 🎯 KẾT QUẢ MONG ĐỢI

Sau khi hoàn thành:
- ✅ **36 hình ảnh** đã được tải về
- ✅ **3 file CSV** đã có đường dẫn hình ảnh
- ✅ **Database** có đường dẫn chính xác
- ✅ **Web** hiển thị hình ảnh đúng

---

**Chúc bạn thành công! 🎉**

Nếu gặp vấn đề, hãy kiểm tra lại từng bước hoặc liên hệ hỗ trợ.
