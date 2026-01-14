# 🖼️ Visual Guide - Product Collection Scraper

## 📸 Screenshots Analysis (Based on Your Images)

### Screenshot 1: Collection Header
```
┌────────────────────────────────────────────────────────┐
│  HOME > KHAN > VIỆT NAM                                │
├────────────────────────────────────────────────────────┤
│                                                        │
│  alhambra    Alhambra: TIERRA COLLECTION               │
│  [LOGO]                                                │
│              ⭐⭐⭐⭐⭐ (Trở thành người đầu tiên đánh giá)│
│                                                        │
│  Thương hiệu Alhambra được thành lập tại Alicante,    │
│  Tây Ban Nha vào năm 1977. Bạn có thể tìm thấy...     │
│                                                        │
│  Chức năng, Phân loại    Nhà phân phối                │
│  Nơi sản xuất           Spain                          │
│  Loại vật tư            Vải bọc, màn, gối, nệm         │
│  Ứng dụng               Nội thất                       │
│  Loại công trình        Phòng khách, Phòng ngủ         │
│                         Công trình thương mại          │
│                                                        │
│  Website                🔗 alhamabrafabrics.com        │
│                                                        │
│  Nhà cung cấp           CÔNG TY TNHH QUẢN LÝ CHUỖI... │
│  Vị trí                 Hồ Chí Minh                    │
│  Điện thoại             0979380068                     │
│  Email                  info@homekhangroup.com         │
└────────────────────────────────────────────────────────┘
```

**Mapping to Database:**
```php
collection = [
  'name' => 'TIERRA COLLECTION',
  'brand' => 'Alhambra',
  'manufacturer_origin' => 'Spain',
  'material_type' => 'Vải bọc, màn, gối, nệm',
  'applications' => 'Nội thất',
  'construction_type' => 'Phòng khách, Phòng ngủ, Công trình thương mại',
  'website' => 'alhamabrafabrics.com',
  'supplier_name' => 'CÔNG TY TNHH QUẢN LÝ CHUỖI...',
  'supplier_location' => 'Hồ Chí Minh',
  'supplier_phone' => '0979380068',
  'supplier_email' => 'info@homekhangroup.com'
]
```

---

### Screenshot 2: Collection Description + Catalog
```
┌────────────────────────────────────────────────────────┐
│  Thông tin vật tư                                      │
│                                                        │
│  [IMAGE: Fabric texture sample]                        │
│                                                        │
│  Bộ sưu tập Tierra không chỉ là thiết kế: đó là hành  │
│  trình tìm về cội nguồn, tôn vinh con người chúng ta  │
│  và cả người chúng ta đã từng là.                      │
│                                                        │
│  Các loại sợi tự nhiên như cotton, lanh và lên sợi    │
│  lên sẽ sử chân thực và bền vững, trong khi lớp hoàn  │
│  thiện của chúng làm mỏi bật vẻ đẹp của sự không hoàn │
│  hảo, gợi nhớ đến những cánh quan truyền cảm hứng cho │
│  chúng ta.                                             │
│                                                        │
│  Tierra là bộ sưu tập bao gồm các loại vải hoàn hảo   │
│  cho đồ bọc, rèm cửa và đồ trang trí nhà cửa.         │
│                                                        │
│  Mõi tác phẩm là một bài ca ngợi cội nguồn của chúng  │
│  ta và là lời mời gọi hãy nhớ rằng, giống như chính   │
│  thái đất, chúng ta là nguồn gốc, lịch sử và tương   │
│  lai.                                                  │
│                                                        │
│  [BUTTON] Catalogue                                    │
│                                                        │
└────────────────────────────────────────────────────────┘
```

**Mapping to Database:**
```php
collection['description'] = "Bộ sưu tập Tierra không chỉ là thiết kế..."
collection['featured_image'] = "/assets/images/collections/tierra-fabric.jpg"

files = [
  [
    'file_type' => 'catalog',
    'title' => 'Catalogue',
    'file_url' => 'https://vnbuilding.vn/path/to/catalogue.pdf'
  ]
]
```

---

### Screenshot 3: Product Items List
```
┌────────────────────────────────────────────────────────┐
│  Danh sách sản phẩm                                    │
│                                                        │
│  ┌───────────┐  ┌───────────┐  ┌───────────┐         │
│  │ [IMAGE]   │  │ [IMAGE]   │  │ [IMAGE]   │         │
│  │           │  │           │  │           │         │
│  │ PIEDRA 06 │  │ ORIGEN 06 │  │ ORIGEN 01 │         │
│  │           │  │           │  │           │         │
│  │ Bộ sưu tập│  │ Bộ sưu tập│  │ Bộ sưu tập│         │
│  │ TIERRA    │  │ TIERRA    │  │ TIERRA    │         │
│  │           │  │           │  │           │         │
│  │ Thành phần│  │ Thành phần│  │ Thành phần│         │
│  │ 88% LI    │  │ 100% SE   │  │ 100% SE   │         │
│  │ 12% CO    │  │           │  │           │         │
│  │           │  │           │  │           │         │
│  │ Hoàn thiện│  │ Hoàn thiện│  │ Hoàn thiện│         │
│  │ HƯỚNG MẪU │  │ HƯỚNG MẪU │  │ HƯỚNG MẪU │         │
│  │ UP ROADED │  │ UP ROADED │  │ UP ROADED │         │
│  │           │  │           │  │           │         │
│  │ Kích thước│  │ Kích thước│  │ Kích thước│         │
│  │ W: 145 cm │  │ W: 135 cm │  │ W: 135 cm │         │
│  │           │  │           │  │           │         │
│  │ Giá bán:  │  │ Giá bán:  │  │ Giá bán:  │         │
│  │ Liên hệ   │  │ Liên hệ   │  │ Liên hệ   │         │
│  │           │  │           │  │           │         │
│  │ [Technical│  │ [Technical│  │ [Technical│         │
│  │ Data Sheet│  │ Data Sheet│  │ Data Sheet│         │
│  │ (TDS)]    │  │ (TDS)]    │  │ (TDS)]    │         │
│  └───────────┘  └───────────┘  └───────────┘         │
│                                                        │
│  ┌───────────┐  ┌───────────┐                         │
│  │ [IMAGE]   │  │ [IMAGE]   │                         │
│  │ GAIA 10   │  │ GAIA 07   │                         │
│  │           │  │           │                         │
│  │ Thành phần│  │ Thành phần│                         │
│  │ 50% CO    │  │ 50% CO    │                         │
│  │ 20% WO    │  │ 20% WO    │                         │
│  │ 20% CV    │  │ 20% CV    │                         │
│  │ 5% PB     │  │ 5% PB     │                         │
│  │ 5% OF     │  │ 5% OF     │                         │
│  │           │  │           │                         │
│  │ Kích thước│  │ Kích thước│                         │
│  │ W: 140 cm │  │ W: 140 cm │                         │
│  └───────────┘  └───────────┘                         │
│                                                        │
└────────────────────────────────────────────────────────┘
```

**Mapping to Database:**
```php
items = [
  [
    'name' => 'PIEDRA 06',
    'collection_name' => 'TIERRA',
    'composition' => '88% LI 12% CO',
    'finish_type' => 'HƯỚNG MẪU UP ROADED',
    'width' => 'W: 145 cm',
    'price_range' => 'Liên hệ',
    'specifications' => [
      'Bộ sưu tập' => 'TIERRA',
      'Thành phần' => '88% LI 12% CO',
      'Hoàn thiện' => 'HƯỚNG MẪU UP ROADED',
      'Kích thước' => 'W: 145 cm'
    ]
  ],
  [
    'name' => 'ORIGEN 06',
    'collection_name' => 'TIERRA',
    'composition' => '100% SE',
    'finish_type' => 'HƯỚNG MẪU UP ROADED',
    'width' => 'W: 135 cm'
  ],
  // ... more items
]
```

---

## 🎯 XPath Selectors Used

### For Collection Header
```php
// Brand & Name from H1
$titleNodes = $xpath->query("//h1");
// "Alhambra: TIERRA COLLECTION" → split by ":"

// Logo
$logoNodes = $xpath->query("//img[contains(@class, 'logo')]");

// Table rows for details
$rows = $xpath->query("//table//tr");
foreach ($rows) {
  $label = $xpath->query(".//td[1]", $row);  // "Nơi sản xuất"
  $value = $xpath->query(".//td[2]", $row);  // "Spain"
}
```

### For Description Section
```php
// Description paragraphs
$descNodes = $xpath->query("
  //div[contains(@class, 'description')] | 
  //section[contains(@class, 'info')]//p
");

// Catalog button/link
$catalogNodes = $xpath->query("
  //a[contains(@href, '.pdf')] | 
  //a[contains(text(), 'Catalogue')] |
  //button[contains(text(), 'Data Sheet')]
");
```

### For Product Items
```php
// Product cards
$productCards = $xpath->query("
  //div[contains(@class, 'product-card')] |
  //div[contains(@class, 'product-item')]
");

foreach ($productCards as $card) {
  // Product name
  $name = $xpath->query(".//h3 | .//h4", $card);
  
  // Product image
  $img = $xpath->query(".//img", $card);
  
  // Specifications
  $specs = $xpath->query(".//dl | .//table//tr", $card);
  
  // Technical Data Sheet link
  $tds = $xpath->query(".//a[contains(text(), 'TDS')]", $card);
}
```

---

## 🎨 CSS Selectors (for Frontend Display)

### Product Card Styling
```css
.product-card {
  background: white;
  border-radius: 8px;
  box-shadow: 0 2px 8px rgba(0,0,0,0.1);
  padding: 1rem;
  transition: transform 0.2s;
}

.product-card:hover {
  transform: translateY(-4px);
  box-shadow: 0 4px 12px rgba(0,0,0,0.15);
}

.product-card img {
  width: 100%;
  height: 200px;
  object-fit: cover;
  border-radius: 6px;
}

.product-name {
  font-size: 1.1rem;
  font-weight: 600;
  color: #1e293b;
  margin: 0.75rem 0 0.5rem;
}

.product-specs {
  font-size: 0.875rem;
  color: #64748b;
  line-height: 1.6;
}

.product-specs dt {
  font-weight: 600;
  color: #475569;
  margin-top: 0.5rem;
}

.product-specs dd {
  margin-left: 0;
  margin-bottom: 0.25rem;
}
```

---

## 📐 Layout Grid

### Desktop Layout (1200px+)
```
┌─────────────────────────────────────────┐
│         COLLECTION HEADER               │
│  [Logo] Alhambra: TIERRA COLLECTION     │
├─────────────────────────────────────────┤
│  [Left 60%]           [Right 40%]       │
│  Description          Contact Info      │
│  + Catalog Link       + Supplier        │
│                       + Phone/Email     │
├─────────────────────────────────────────┤
│  PRODUCT ITEMS (Grid: 4 columns)        │
│  [Item1] [Item2] [Item3] [Item4]        │
│  [Item5] [Item6] [Item7] [Item8]        │
└─────────────────────────────────────────┘
```

### Tablet Layout (768px - 1199px)
```
┌───────────────────────────┐
│   COLLECTION HEADER       │
├───────────────────────────┤
│  Description (full width) │
├───────────────────────────┤
│  Contact Info (full width)│
├───────────────────────────┤
│  PRODUCTS (Grid: 3 cols)  │
│  [Item1] [Item2] [Item3]  │
│  [Item4] [Item5] [Item6]  │
└───────────────────────────┘
```

### Mobile Layout (< 768px)
```
┌─────────────┐
│   HEADER    │
├─────────────┤
│ Description │
├─────────────┤
│   Contact   │
├─────────────┤
│ PRODUCTS    │
│  [Item 1]   │
│  [Item 2]   │
│  [Item 3]   │
└─────────────┘
```

---

## 🎯 Data Flow Visualization

```
WEBPAGE                    SCRAPER                  DATABASE
───────                    ───────                  ────────

┌─────────┐               ┌─────────┐              ┌─────────┐
│ Header  │──Parse───────>│Section 1│────INSERT───>│Collections│
│ Brand   │               │ Brand   │              │   Table   │
│ Logo    │               │ Supplier│              └─────────┘
└─────────┘               └─────────┘                    │
                                                         │
┌─────────┐               ┌─────────┐                   │
│ Content │──Parse───────>│Section 2│──────────────────>│
│ Desc    │               │ Details │              (Same record)
│ Catalog │               │ Files   │
└─────────┘               └─────────┘
                                │
                                │
                          ┌─────┴─────┐
                          │           │
                          ▼           ▼
                     ┌─────────┐ ┌─────────┐
                     │  Items  │ │  Files  │
                     │  Table  │ │  Table  │
                     └─────────┘ └─────────┘
┌─────────┐
│Products │──Parse─>│Section 3│────INSERT───>Items Table
│  List   │         │  Items  │              (Multiple records)
│ [Card1] │         │  Array  │
│ [Card2] │         └─────────┘
│ [Card3] │
└─────────┘
```

---

## 🎨 Preview Interface (fetch_product_collection.php)

```
┌──────────────────────────────────────────────────────┐
│  🎨 Intelligent Product Collection Scraper           │
│  Crawl toàn bộ thông tin: Brand → Details → Items   │
├──────────────────────────────────────────────────────┤
│  📥 Nhập URL Collection                              │
│  ┌────────────────────────────────────────────────┐ │
│  │ URL: https://vnbuilding.vn/vat-lieu/...        │ │
│  └────────────────────────────────────────────────┘ │
│  [🔍 Crawl & Preview]  [💾 Save to Database]        │
├──────────────────────────────────────────────────────┤
│  📋 Preview Data                                     │
│                                                      │
│  🏷️ Collection Information                          │
│  ┌──────────────┬──────────────┬──────────────┐    │
│  │ Brand        │ Name         │ Origin       │    │
│  │ Alhambra     │ TIERRA...    │ Spain        │    │
│  ├──────────────┼──────────────┼──────────────┤    │
│  │ Supplier     │ Phone        │ Email        │    │
│  │ EDSON VN     │ 0979380068   │ info@...     │    │
│  └──────────────┴──────────────┴──────────────┘    │
│                                                      │
│  📦 Product Items (5)                                │
│  ┌────────┐ ┌────────┐ ┌────────┐ ┌────────┐       │
│  │[IMAGE] │ │[IMAGE] │ │[IMAGE] │ │[IMAGE] │       │
│  │PIEDRA  │ │ORIGEN  │ │ORIGEN  │ │GAIA 10 │       │
│  │  06    │ │  06    │ │  01    │ │        │       │
│  └────────┘ └────────┘ └────────┘ └────────┘       │
│                                                      │
│  📄 Catalog Files (1)                                │
│  • Catalogue (catalog) - [Download]                 │
│                                                      │
├──────────────────────────────────────────────────────┤
│  📚 Saved Collections (3)                            │
│  ┌───┬──────────┬──────────────┬──────┬──────┐     │
│  │ID │Brand     │Name          │Items │Files │     │
│  ├───┼──────────┼──────────────┼──────┼──────┤     │
│  │1  │Alhambra  │TIERRA...     │5     │1     │     │
│  │2  │Another   │Collection X  │8     │2     │     │
│  └───┴──────────┴──────────────┴──────┴──────┘     │
└──────────────────────────────────────────────────────┘
```

---

## 📊 Success Metrics

### What Gets Extracted

| Metric | Expected | Actual (Alhambra Example) |
|--------|----------|---------------------------|
| **Collection** | 1 record | ✅ 1 (TIERRA COLLECTION) |
| **Items** | 5+ records | ✅ 5+ (PIEDRA, ORIGEN, GAIA...) |
| **Files** | 1+ PDFs | ✅ 1+ (Catalogue) |
| **Fields per Collection** | 20+ | ✅ 25+ fields filled |
| **Specs per Item** | 4+ | ✅ 4-6 specs each |

---

Created: 2025-12-10  
Purpose: Visual guide for understanding the scraper functionality  
Based on: Screenshots from https://vnbuilding.vn/vat-lieu/alhmabra-rd7ere
