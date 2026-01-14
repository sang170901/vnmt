<?php
require_once 'backend/inc/db.php';
require_once __DIR__ . '/../lang/db_translate_helper.php';

// Lấy danh sách slider active và trong thời gian hiển thị
function getActiveSliders() {
    try {
        $pdo = getPDO();
        $today = date('Y-m-d');
        
        $sql = "SELECT *, title_en, subtitle_en, description_en, link_text_en FROM sliders 
                WHERE status = 1 
                AND (start_date IS NULL OR start_date <= ?) 
                AND (end_date IS NULL OR end_date >= ?) 
                ORDER BY display_order ASC, id ASC";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$today, $today]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
        error_log("Lỗi khi lấy slider: " . $e->getMessage());
        return [];
    }
}

$sliders = getActiveSliders();
?>

<?php if (!empty($sliders)): ?>
<!-- Slider Section -->
<section class="main-slider fullpage-section" id="main-slider" data-section-label="Trang chủ">
    <div class="slider-container">
        <div class="slider-wrapper">
            <?php foreach ($sliders as $index => $slider): ?>
            <div class="slide <?php echo $index === 0 ? 'active' : ''; ?>" data-slide="<?php echo $index; ?>">
                <div class="slide-background" style="background-image: url('<?php echo htmlspecialchars($slider['image']); ?>');">
                    <div class="slide-overlay"></div>
                </div>
                <div class="slide-content">
                    <div class="container">
                        <h2 class="slide-title"><?php echo htmlspecialchars(getTranslated($slider, 'title')); ?></h2>
                        <?php if (!empty(getTranslated($slider, 'subtitle'))): ?>
                        <p class="slide-subtitle"><?php echo htmlspecialchars(getTranslated($slider, 'subtitle')); ?></p>
                        <?php endif; ?>
                        <?php if (!empty(getTranslated($slider, 'description'))): ?>
                        <p class="slide-description"><?php echo htmlspecialchars(getTranslated($slider, 'description')); ?></p>
                        <?php endif; ?>
                        <?php if (!empty($slider['link']) && !empty(getTranslated($slider, 'link_text'))): ?>
                        <a href="<?php echo htmlspecialchars($slider['link']); ?>" class="slide-btn">
                            <?php echo htmlspecialchars(getTranslated($slider, 'link_text')); ?>
                        </a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        
        <?php if (count($sliders) > 1): ?>
        <!-- Slider Navigation -->
        <div class="slider-nav">
            <button class="slider-prev" onclick="prevSlide()">
                <i class="fas fa-chevron-left"></i>
            </button>
            <button class="slider-next" onclick="nextSlide()">
                <i class="fas fa-chevron-right"></i>
            </button>
        </div>
        <?php endif; ?>
    </div>
</section>

<style>
/* 🎨 Ultra Modern Slider with Glass Morphism & Animations */

@keyframes slideInUp {
    from {
        opacity: 0;
        transform: translateY(60px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

@keyframes fadeInScale {
    from {
        opacity: 0;
        transform: scale(0.9);
    }
    to {
        opacity: 1;
        transform: scale(1);
    }
}

.main-slider {
    position: relative;
    width: 100%;
    height: 700px;
    overflow: hidden;
    margin: 0 !important;
    padding: 0 !important;
    background: #1e293b; /* Background tối để tránh flash trắng/xám */
    left: 0;
    right: 0;
    border: none !important;
    outline: none !important;
    box-shadow: none !important;
}

.slider-container {
    position: relative;
    width: 100%;
    height: 100%;
    margin: 0;
    padding: 0;
    border: none !important;
    outline: none !important;
}

.slider-wrapper {
    position: relative;
    width: 100%;
    height: 100%;
    margin: 0;
    padding: 0;
}

.slide {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    opacity: 0;
    visibility: hidden;
    transition: opacity 0.8s cubic-bezier(0.4, 0, 0.2, 1), visibility 0.8s;
    z-index: 1;
    background: #1e293b; /* Background tối để tránh flash */
}

.slide.active {
    opacity: 1;
    visibility: visible;
    z-index: 2;
}

.slide-background {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background-size: cover;
    background-position: center center;
    background-repeat: no-repeat;
    background-color: #1e293b; /* Background tối để tránh flash khi load ảnh */
    transform: scale(1.05);
    transition: transform 12s ease-out, opacity 0.8s ease-in-out;
    opacity: 0;
    will-change: opacity, transform; /* Tối ưu performance */
    border: none !important;
}

.slide.active .slide-background {
    transform: scale(1);
    opacity: 1;
}

/* Slide đầu tiên hiển thị ngay không cần transition */
.slide:first-child.active .slide-background {
    opacity: 1;
    transform: scale(1);
}

.slide-overlay {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: linear-gradient(135deg, rgba(0, 0, 0, 0.2) 0%, rgba(0, 0, 0, 0.4) 100%);
    opacity: 0;
    transition: opacity 0.8s ease-in-out;
    will-change: opacity; /* Tối ưu performance */
}

.slide.active .slide-overlay {
    opacity: 1;
}

/* Slide đầu tiên overlay hiển thị ngay */
.slide:first-child.active .slide-overlay {
    opacity: 1;
}

.slide-content {
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    text-align: center;
    color: white;
    z-index: 3;
    width: 100%;
    max-width: 1200px;
    padding: 40px 20px;
    opacity: 0;
    transition: opacity 0.6s cubic-bezier(0.4, 0, 0.2, 1);
    pointer-events: none; /* Không block clicks đến navigation buttons */
}

.slide-content .container {
    padding: 0 !important;
    margin: 0 auto;
    max-width: 100%;
}

/* Override body container padding for slider */
body > .main-slider {
    margin-left: 0 !important;
    margin-right: 0 !important;
}

section.main-slider {
    margin-left: 0 !important;
    margin-right: 0 !important;
    padding-left: 0 !important;
    padding-right: 0 !important;
}

/* Chỉ hiển thị content khi hover vào slide */
.slide:hover .slide-content {
    opacity: 1;
}

.slide:not(.active) .slide-content > * {
    opacity: 0;
}

.slide.active .slide-title {
    animation: slideInUp 0.8s cubic-bezier(0.4, 0, 0.2, 1) 0.2s both;
}

.slide.active .slide-subtitle {
    animation: slideInUp 0.8s cubic-bezier(0.4, 0, 0.2, 1) 0.4s both;
}

.slide.active .slide-description {
    animation: slideInUp 0.8s cubic-bezier(0.4, 0, 0.2, 1) 0.6s both;
}

.slide.active .slide-btn {
    animation: fadeInScale 0.8s cubic-bezier(0.4, 0, 0.2, 1) 0.8s both;
}

.slide-title {
    font-size: 4.5rem;
    font-weight: 900;
    margin-bottom: 1.5rem;
    line-height: 1.1;
    letter-spacing: -0.02em;
    background: linear-gradient(135deg, #ffffff 0%, #e0f2fe 100%);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
    text-shadow: 0 4px 20px rgba(0, 0, 0, 0.3);
    position: relative;
}

.slide-title::after {
    content: '';
    position: absolute;
    bottom: -15px;
    left: 50%;
    transform: translateX(-50%);
    width: 100px;
    height: 4px;
    background: linear-gradient(90deg, transparent, #38bdf8, transparent);
    border-radius: 2px;
}

.slide-subtitle {
    font-size: 1.8rem;
    font-weight: 400;
    margin-bottom: 1.5rem;
    color: #e0f2fe;
    letter-spacing: 0.02em;
    line-height: 1.6;
    background: rgba(255, 255, 255, 0.15);
    backdrop-filter: blur(20px);
    border: 1px solid rgba(255, 255, 255, 0.25);
    padding: 15px 35px;
    border-radius: 50px;
    display: inline-block;
    box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
}

.slide-description {
    font-size: 1.3rem;
    margin: 1.5rem auto;
    max-width: 800px;
    color: #f0f9ff;
    line-height: 1.8;
    font-weight: 300;
    letter-spacing: 0.01em;
    opacity: 0.95;
}

.slide-btn {
    display: inline-flex;
    align-items: center;
    gap: 12px;
    padding: 16px 40px;
    margin-top: 20px;
    background: linear-gradient(135deg, #C8102E 0%, #A00D26 100%);
    color: #ffffff;
    text-decoration: none;
    border-radius: 50px;
    font-weight: 700;
    font-size: 1.05rem;
    text-transform: uppercase;
    letter-spacing: 0.04em;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    box-shadow: 0 8px 28px rgba(200, 16, 46, 0.35);
    border: 1px solid rgba(255, 255, 255, 0.25);
    position: relative;
    overflow: hidden;
    pointer-events: auto; /* Cho phép click nút kể cả khi parent tắt pointer-events */
    z-index: 1002;
}

.slide-btn::before {
    content: '';
    position: absolute;
    top: 0;
    left: -100%;
    width: 100%;
    height: 100%;
    background: linear-gradient(90deg, transparent, rgba(255,255,255,0.3), transparent);
    transition: left 0.6s;
}

.slide-btn:hover::before {
    left: 100%;
}

.slide-btn:hover {
    transform: translateY(-2px) scale(1.03);
    box-shadow: 0 16px 44px rgba(200, 16, 46, 0.5);
    color: #ffffff;
    text-decoration: none;
    border-color: rgba(255, 255, 255, 0.45);
}

.slide-btn::after {
    content: '→';
    margin-left: 8px;
    transition: transform 0.3s;
}

.slide-btn:hover::after {
    transform: translateX(5px);
}

/* Navigation Buttons */
.slider-nav {
    position: absolute;
    top: 50%;
    left: 0;
    right: 0;
    transform: translateY(-50%);
    z-index: 1000; /* Tăng z-index cao để đảm bảo không bị che */
    pointer-events: none;
}

.slider-prev,
.slider-next {
    position: absolute;
    background: rgba(255, 255, 255, 0.08);
    backdrop-filter: blur(12px);
    -webkit-backdrop-filter: blur(12px);
    border: 1px solid rgba(255, 255, 255, 0.15);
    color: rgba(255, 255, 255, 0.9);
    width: 44px;
    height: 44px;
    border-radius: 12px;
    cursor: pointer;
    font-size: 16px;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    pointer-events: auto !important;
    display: flex;
    align-items: center;
    justify-content: center;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1), 0 1px 3px rgba(0, 0, 0, 0.08);
    z-index: 1001;
    user-select: none;
    -webkit-user-select: none;
    -moz-user-select: none;
    -ms-user-select: none;
    opacity: 0.7;
}

/* Re-enable interactions for links inside slide content */
.slide-content a { pointer-events: auto; }

/* Trên thiết bị cảm ứng, luôn hiển thị nội dung để có thể bấm nút */
@media (hover: none) {
    .slide.active .slide-content { opacity: 1; }
}

.slider-prev {
    left: 20px;
}

.slider-next {
    right: 20px;
}

.slider-prev:hover,
.slider-next:hover {
    background: rgba(255, 255, 255, 0.18);
    border-color: rgba(255, 255, 255, 0.3);
    color: white;
    transform: scale(1.08) translateY(-1px);
    box-shadow: 0 4px 16px rgba(0, 0, 0, 0.15), 0 2px 6px rgba(0, 0, 0, 0.1);
    opacity: 1;
}

.slider-prev:active,
.slider-next:active {
    transform: scale(1.02) translateY(0);
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.12);
}

/* Icon inside button - smoother */
.slider-prev i,
.slider-next i {
    transition: transform 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    font-size: 14px;
}

.slider-prev:hover i {
    transform: translateX(-2px);
}

.slider-next:hover i {
    transform: translateX(2px);
}

/* Dot Navigation - REMOVED */

/* Responsive Design */
@media (max-width: 1024px) {
    .main-slider {
        height: 600px;
    }
    
    .slide-title {
        font-size: 3.5rem;
    }
    
    .slide-subtitle {
        font-size: 1.5rem;
        padding: 12px 28px;
    }
    
    .slide-description {
        font-size: 1.2rem;
    }
}

@media (max-width: 768px) {
    .main-slider {
        height: 400px; /* Quay lại chiều cao ban đầu */
        margin: 0;
        padding: 0;
        overflow: hidden;
    }
    
    .slide-content {
        width: 100%;
        padding: 30px 15px;
    }
    
    .slide-content .container {
        padding: 0;
    }
    
    .slide-title {
        font-size: 2.4rem;
    }
    
    .slide-title::after {
        width: 60px;
        height: 3px;
    }
    
    .slide-subtitle {
        font-size: 1.2rem;
        padding: 8px 20px;
    }
    
    .slide-description {
        font-size: 1rem;
    }
    
    .slide-btn {
        padding: 14px 30px;
        font-size: 0.95rem;
    }
    
    .slider-prev,
    .slider-next {
        width: 40px;
        height: 40px;
        font-size: 13px;
        border-radius: 10px;
    }
    
    .slider-prev {
        left: 15px;
    }
    
    .slider-next {
        right: 15px;
    }
    
    .slider-prev i,
    .slider-next i {
        font-size: 12px;
    }
    
}

@media (max-width: 640px) {
    .main-slider {
        height: 350px; /* Quay lại chiều cao ban đầu */
        margin: 0;
        padding: 0;
    }
    
    .slide-content {
        width: 100%;
        padding: 25px 12px;
    }
    
    .slide-content .container {
        padding: 0;
    }
    
    .slide-title {
        font-size: 2.2rem;
        margin-bottom: 0.9rem;
    }
    
    .slide-subtitle {
        font-size: 1.1rem;
        padding: 7px 18px;
    }
    
    .slide-description {
        font-size: 0.95rem;
    }
    
    .slide-btn {
        padding: 13px 28px;
        font-size: 0.9rem;
    }
    
    .slider-prev,
    .slider-next {
        width: 36px;
        height: 36px;
        font-size: 12px;
        border-radius: 9px;
    }
    
    .slider-prev {
        left: 12px;
    }
    
    .slider-next {
        right: 12px;
    }
    
    .slider-prev i,
    .slider-next i {
        font-size: 11px;
    }
}

@media (max-width: 480px) {
    .main-slider {
        height: 300px; /* Quay lại chiều cao ban đầu */
        margin: 0;
        padding: 0;
    }
    
    .slide-content {
        width: 100%;
        padding: 20px 10px;
    }
    
    .slide-content .container {
        padding: 0;
    }
    
    .slide-title {
        font-size: 1.8rem;
        margin-bottom: 0.8rem;
    }
    
    .slide-title::after {
        width: 50px;
        height: 2px;
        bottom: -8px;
    }
    
    .slide-subtitle {
        font-size: 1rem;
        padding: 6px 16px;
        margin-bottom: 0.8rem;
    }
    
    .slide-description {
        font-size: 0.88rem;
        margin: 0.8rem auto;
    }
    
    .slide-btn {
        padding: 12px 26px;
        font-size: 0.85rem;
        margin-top: 12px;
    }
    
    .slide-btn::after {
        content: '';
    }
    
    .slider-prev,
    .slider-next {
        width: 34px;
        height: 34px;
        font-size: 11px;
        border-radius: 8px;
    }
    
    .slider-prev {
        left: 10px;
    }
    
    .slider-next {
        right: 10px;
    }
    
    .slider-prev i,
    .slider-next i {
        font-size: 10px;
    }
}

@media (max-width: 375px) {
    .main-slider {
        height: 280px; /* Quay lại chiều cao ban đầu */
        margin: 0;
        padding: 0;
    }
    
    .slide-content {
        width: 100%;
        padding: 18px 8px;
    }
    
    .slide-content .container {
        padding: 0;
    }
    
    .slide-title {
        font-size: 1.65rem;
    }
    
    .slide-subtitle {
        font-size: 0.95rem;
        padding: 6px 14px;
    }
    
    .slide-description {
        font-size: 0.82rem;
    }
    
    .slide-btn {
        padding: 11px 24px;
        font-size: 0.8rem;
    }
    
    .slider-prev,
    .slider-next {
        width: 32px;
        height: 32px;
        font-size: 10px;
        border-radius: 7px;
    }
    
    .slider-prev i,
    .slider-next i {
        font-size: 9px;
    }
}

@media (max-width: 320px) {
    .main-slider {
        height: 260px; /* Quay lại chiều cao ban đầu */
        margin: 0;
        padding: 0;
    }
    
    .slide-content {
        width: 100%;
        padding: 15px 8px;
    }
    
    .slide-content .container {
        padding: 0;
    }
    
    .slide-title {
        font-size: 1.5rem;
        margin-bottom: 0.7rem;
    }
    
    .slide-subtitle {
        font-size: 0.9rem;
        padding: 5px 12px;
    }
    
    .slide-description {
        font-size: 0.78rem;
        margin: 0.6rem auto;
    }
    
    .slide-btn {
        padding: 10px 22px;
        font-size: 0.75rem;
    }
    
    .slider-prev,
    .slider-next {
        width: 30px;
        height: 30px;
        font-size: 9px;
        border-radius: 6px;
    }
    
    .slider-prev {
        left: 8px;
    }
    
    .slider-next {
        right: 8px;
    }
    
    .slider-prev i,
    .slider-next i {
        font-size: 8px;
    }
}
</style>

<script>
// Slider JavaScript
let currentSlideIndex = 0;
let slideInterval;

function showSlide(n) {
    const slides = document.querySelectorAll('.slide');
    
    if (n >= slides.length) currentSlideIndex = 0;
    if (n < 0) currentSlideIndex = slides.length - 1;
    
    // Preload next slide image để tránh flash
    const nextIndex = currentSlideIndex;
    if (slides[nextIndex]) {
        const nextSlide = slides[nextIndex];
        const bgImg = nextSlide.querySelector('.slide-background');
        if (bgImg) {
            const imgUrl = bgImg.style.backgroundImage.match(/url\(['"]?([^'"]+)['"]?\)/);
            if (imgUrl && imgUrl[1]) {
                const img = new Image();
                img.src = imgUrl[1];
            }
        }
    }
    
    // Remove active class từ tất cả slides
    slides.forEach(slide => slide.classList.remove('active'));
    
    // Add active class cho slide hiện tại với delay nhỏ để transition mượt
    if (slides[currentSlideIndex]) {
        setTimeout(() => {
            slides[currentSlideIndex].classList.add('active');
        }, 50);
    }
}

function nextSlide() {
    currentSlideIndex++;
    showSlide(currentSlideIndex);
    stopAutoSlide(); // Dừng auto slide khi user click
    setTimeout(() => startAutoSlide(), 5000); // Resume sau 5 giây
}

function prevSlide() {
    currentSlideIndex--;
    showSlide(currentSlideIndex);
    stopAutoSlide(); // Dừng auto slide khi user click
    setTimeout(() => startAutoSlide(), 5000); // Resume sau 5 giây
}

// Đảm bảo functions được định nghĩa trong global scope
window.nextSlide = nextSlide;
window.prevSlide = prevSlide;

function currentSlide(n) {
    currentSlideIndex = n;
    showSlide(currentSlideIndex);
}

// Auto slide (chỉ khi có nhiều hơn 1 slide)
function startAutoSlide() {
    const slides = document.querySelectorAll('.slide');
    if (slides.length > 1) {
        slideInterval = setInterval(nextSlide, 6000); // 6 seconds
    }
}

function stopAutoSlide() {
    if (slideInterval) {
        clearInterval(slideInterval);
    }
}

// Preload all slide images
function preloadSlideImages() {
    const slides = document.querySelectorAll('.slide');
    slides.forEach(slide => {
        const bgImg = slide.querySelector('.slide-background');
        if (bgImg) {
            const imgUrl = bgImg.style.backgroundImage.match(/url\(['"]?([^'"]+)['"]?\)/);
            if (imgUrl && imgUrl[1]) {
                const img = new Image();
                img.src = imgUrl[1];
                img.onload = function() {
                    // Image loaded successfully
                    bgImg.style.opacity = slide.classList.contains('active') ? '1' : '0';
                };
            }
        }
    });
}

// Initialize slider
document.addEventListener('DOMContentLoaded', function() {
    const slider = document.getElementById('main-slider');
    if (slider) {
        // Preload images first
        preloadSlideImages();
        
        // Ensure first slide is visible immediately
        const firstSlide = slider.querySelector('.slide.active');
        if (firstSlide) {
            const firstBg = firstSlide.querySelector('.slide-background');
            if (firstBg) {
                firstBg.style.opacity = '1';
            }
            const firstOverlay = firstSlide.querySelector('.slide-overlay');
            if (firstOverlay) {
                firstOverlay.style.opacity = '1';
            }
        }
        
        // Add event listeners cho navigation buttons
        const prevBtn = slider.querySelector('.slider-prev');
        const nextBtn = slider.querySelector('.slider-next');
        
        if (prevBtn) {
            prevBtn.addEventListener('click', function(e) {
                e.preventDefault();
                e.stopPropagation();
                prevSlide();
            });
            // Đảm bảo button có thể click
            prevBtn.style.pointerEvents = 'auto';
            prevBtn.style.cursor = 'pointer';
        }
        
        if (nextBtn) {
            nextBtn.addEventListener('click', function(e) {
                e.preventDefault();
                e.stopPropagation();
                nextSlide();
            });
            // Đảm bảo button có thể click
            nextBtn.style.pointerEvents = 'auto';
            nextBtn.style.cursor = 'pointer';
        }
        
        startAutoSlide();
        
        // Pause auto slide on hover vào toàn bộ slider
        slider.addEventListener('mouseenter', function() {
            stopAutoSlide();
        });
        
        slider.addEventListener('mouseleave', function() {
            startAutoSlide();
        });
    }
});
</script>

<?php endif; ?>