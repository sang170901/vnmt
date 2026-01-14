<?php 
header('Content-Type: text/html; charset=UTF-8');
include 'inc/header-new.php'; 
?>

<style>
/* 🎨 Modern Contact Page */
.contact-hero {
    background: linear-gradient(135deg, #e0f2fe 0%, #bae6fd 100%);
    padding: 80px 0 60px;
    text-align: center;
    color: #0c4a6e;
    position: relative;
    overflow: hidden;
}

.contact-hero::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><defs><pattern id="grid" width="10" height="10" patternUnits="userSpaceOnUse"><path d="M 10 0 L 0 0 0 10" fill="none" stroke="rgba(14,165,233,0.1)" stroke-width="0.5"/></pattern></defs><rect width="100" height="100" fill="url(%23grid)"/></svg>');
    opacity: 0.5;
}

.contact-hero-content {
    position: relative;
    z-index: 2;
}

.contact-hero h1 {
    font-size: 3rem;
    font-weight: 800;
    margin: 0 0 16px 0;
    letter-spacing: -0.02em;
    color: #0369a1;
}

.contact-hero p {
    font-size: 1.2rem;
    color: #075985;
    max-width: 600px;
    margin: 0 auto;
    font-weight: 500;
}

.contact-section {
    padding: 80px 0;
    background: #f8fafc;
}

.contact-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 60px;
    max-width: 1200px;
    margin: 0 auto;
}

/* Contact Info Cards */
.contact-info {
    display: flex;
    flex-direction: column;
    gap: 24px;
}

.info-card {
    background: white;
    border-radius: 20px;
    padding: 32px;
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
    transition: all 0.3s;
    border: 2px solid transparent;
}

.info-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 12px 40px rgba(56, 189, 248, 0.2);
    border-color: #38bdf8;
}

.info-header {
    display: flex;
    align-items: center;
    gap: 16px;
    margin-bottom: 20px;
}

.info-icon {
    width: 56px;
    height: 56px;
    background: linear-gradient(135deg, #38bdf8 0%, #0ea5e9 100%);
    border-radius: 16px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 24px;
    flex-shrink: 0;
}

.info-title {
    font-size: 1.3rem;
    font-weight: 700;
    color: #1e293b;
    margin: 0;
}

.info-content {
    display: flex;
    flex-direction: column;
    gap: 12px;
    padding-left: 72px;
}

.info-item {
    display: flex;
    align-items: center;
    gap: 12px;
    color: #475569;
    font-size: 1rem;
}

.info-item i {
    color: #38bdf8;
    width: 20px;
}

.info-item a {
    color: #0284c7;
    text-decoration: none;
    transition: color 0.3s;
    font-weight: 500;
}

.info-item a:hover {
    color: #38bdf8;
    text-decoration: underline;
}

/* Contact Form */
.contact-form-container {
    background: white;
    border-radius: 24px;
    padding: 48px;
    box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
    position: sticky;
    top: 100px;
}

.form-title {
    font-size: 2rem;
    font-weight: 800;
    color: #1e293b;
    margin: 0 0 12px 0;
}

.form-subtitle {
    color: #64748b;
    margin-bottom: 32px;
}

.form-group {
    margin-bottom: 24px;
}

.form-group label {
    display: block;
    font-weight: 600;
    color: #334155;
    margin-bottom: 8px;
    font-size: 0.95rem;
}

.form-control {
    width: 100%;
    padding: 14px 18px;
    border: 2px solid #e2e8f0;
    border-radius: 12px;
    font-size: 1rem;
    transition: all 0.3s;
    font-family: inherit;
}

.form-control:focus {
    outline: none;
    border-color: #38bdf8;
    box-shadow: 0 0 0 4px rgba(56, 189, 248, 0.1);
}

textarea.form-control {
    resize: vertical;
    min-height: 150px;
}

.submit-btn {
    width: 100%;
    padding: 16px;
    background: linear-gradient(135deg, #38bdf8 0%, #0ea5e9 100%);
    color: white;
    border: none;
    border-radius: 12px;
    font-size: 1.1rem;
    font-weight: 700;
    cursor: pointer;
    transition: all 0.3s;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 12px;
}

.submit-btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 12px 32px rgba(56, 189, 248, 0.4);
    background: linear-gradient(135deg, #0ea5e9 0%, #0284c7 100%);
}

.submit-btn i {
    font-size: 1.2rem;
}

/* Map Section */
.map-section {
    padding: 0;
    background: white;
}

.map-container {
    width: 100%;
    height: 450px;
    background: #e2e8f0;
    border-radius: 20px;
    overflow: hidden;
    box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
}

.map-container iframe {
    width: 100%;
    height: 100%;
    border: none;
}

/* Address Cards */
.address-cards {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 24px;
    margin-top: 40px;
}

.address-card {
    background: white;
    color: #1e293b;
    padding: 32px;
    border-radius: 20px;
    position: relative;
    overflow: hidden;
    border: 2px solid #e0f2fe;
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
    transition: all 0.3s;
}

.address-card::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 4px;
    background: linear-gradient(90deg, #38bdf8, #0ea5e9);
    border-radius: 20px 20px 0 0;
}

.address-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 12px 40px rgba(56, 189, 248, 0.2);
    border-color: #38bdf8;
}

.address-card h3 {
    font-size: 1.3rem;
    margin: 0 0 16px 0;
    display: flex;
    align-items: center;
    gap: 12px;
    color: #0284c7;
    font-weight: 700;
}

.address-card h3 i {
    font-size: 1.5rem;
    color: #38bdf8;
}

.address-card p {
    margin: 0;
    line-height: 1.8;
    color: #475569;
    font-weight: 400;
}

/* Responsive */
@media (max-width: 1024px) {
    .contact-grid {
        grid-template-columns: 1fr;
        gap: 40px;
    }
    
    .contact-form-container {
        position: static;
    }
}

@media (max-width: 768px) {
    .contact-hero h1 {
        font-size: 2.2rem;
    }
    
    .contact-section {
        padding: 60px 0;
    }
    
    .contact-form-container {
        padding: 32px 24px;
    }
    
    .address-cards {
        grid-template-columns: 1fr;
    }
}
</style>

<!-- Hero Section -->
<section class="contact-hero">
    <div class="contact-hero-content">
        <h1>📞 Liên Hệ Với Chúng Tôi</h1>
        <p>Chúng tôi luôn sẵn sàng hỗ trợ bạn. Hãy liên hệ với chúng tôi qua bất kỳ kênh nào dưới đây!</p>
    </div>
</section>

<!-- Contact Section -->
<section class="contact-section">
    <div class="container">
        <div class="contact-grid">
            <!-- Contact Info -->
            <div class="contact-info">
                <!-- Hotline Card -->
                <div class="info-card">
                    <div class="info-header">
                        <div class="info-icon">
                            <i class="fas fa-phone-alt"></i>
                        </div>
                        <h3 class="info-title">Hotline</h3>
                    </div>
                    <div class="info-content">
                        <div class="info-item">
                            <i class="fas fa-chevron-right"></i>
                            <a href="tel:0829300555">0829 300555</a>
                        </div>
                    </div>
                </div>

                <!-- Email Card -->
                <div class="info-card">
                    <div class="info-header">
                        <div class="info-icon">
                            <i class="fas fa-envelope"></i>
                        </div>
                        <h3 class="info-title">Email</h3>
                    </div>
                    <div class="info-content">
                        <div class="info-item">
                            <i class="fas fa-chevron-right"></i>
                            <a href="mailto:hungnm@hprogroup.vn">hungnm@hprogroup.vn</a>
                        </div>
                        <div class="info-item">
                            <i class="fas fa-chevron-right"></i>
                            <a href="mailto:hungnm@hpro.com.vn">hungnm@hpro.com.vn</a>
                        </div>
                    </div>
                </div>

                <!-- Social & Website Card -->
                <div class="info-card">
                    <div class="info-header">
                        <div class="info-icon">
                            <i class="fas fa-globe"></i>
                        </div>
                        <h3 class="info-title">Kết nối với chúng tôi</h3>
                    </div>
                    <div class="info-content">
                        <div class="info-item">
                            <i class="fab fa-facebook"></i>
                            <a href="https://www.facebook.com/hprogroup.vn/" target="_blank">facebook.com/hprogroup.vn</a>
                        </div>
                        <div class="info-item">
                            <i class="fas fa-link"></i>
                            <a href="<?php echo (strpos($_SERVER['SERVER_NAME'], 'localhost') !== false) ? 'http://localhost:8080/vnmt/' : 'https://vnmaterials.com/'; ?>">vnmaterials.com</a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Contact Form -->
            <div class="contact-form-container">
                <h2 class="form-title">Gửi tin nhắn</h2>
                <p class="form-subtitle">Điền thông tin bên dưới và chúng tôi sẽ liên hệ lại với bạn sớm nhất!</p>
                
                <form action="#" method="POST">
                    <div class="form-group">
                        <label for="name">Họ và tên *</label>
                        <input type="text" id="name" name="name" class="form-control" placeholder="Nguyễn Văn A" required>
                    </div>

                    <div class="form-group">
                        <label for="email">Email *</label>
                        <input type="email" id="email" name="email" class="form-control" placeholder="email@example.com" required>
                    </div>

                    <div class="form-group">
                        <label for="phone">Số điện thoại *</label>
                        <input type="tel" id="phone" name="phone" class="form-control" placeholder="0829 300555" required>
                    </div>

                    <div class="form-group">
                        <label for="subject">Tiêu đề</label>
                        <input type="text" id="subject" name="subject" class="form-control" placeholder="Tôi muốn...">
                    </div>

                    <div class="form-group">
                        <label for="message">Nội dung *</label>
                        <textarea id="message" name="message" class="form-control" placeholder="Nhập nội dung tin nhắn của bạn..." required></textarea>
                    </div>

                    <button type="submit" class="submit-btn">
                        <span>Gửi tin nhắn</span>
                        <i class="fas fa-paper-plane"></i>
                    </button>
                </form>
            </div>
        </div>
    </div>
</section>

<!-- Address Cards Section -->
<section class="contact-section" style="padding-top: 0;">
    <div class="container">
        <div class="address-cards">
            <div class="address-card">
                <h3>
                    <i class="fas fa-store"></i>
                    Showroom
                </h3>
                <p>
                    Biệt thự 10, đường Sao Biển 3<br>
                    KĐT Vinhomes Ocean Park 2<br>
                    Hà Nội, Việt Nam
                </p>
            </div>
            
            <div class="address-card">
                <h3>
                    <i class="fas fa-building"></i>
                    Văn phòng
                </h3>
                <p>
                    457 Hoàng Hoa Thám<br>
                    Quận Ba Đình<br>
                    Hà Nội, Việt Nam
                </p>
            </div>
        </div>
    </div>
</section>

<!-- Map Section -->
<section class="map-section">
    <div class="container">
        <div class="map-container">
            <!-- Google Maps Embed - 457 Hoàng Hoa Thám, Ba Đình, Hà Nội -->
            <iframe 
                src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3724.0967842935486!2d105.82216631476282!3d21.030187785998973!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x3135ab8dfc201f7d%3A0x8e1b51b8f8f8e8e8!2zNDU3IEhvw6BuZyBIb2EgVGjDoW0sIE5nw6MgVMawIFPhu58sIMSQw7RuZyDEkGEsIEjDoCBO4buZaSwgVmnhu4d0IE5hbQ!5e0!3m2!1sen!2s!4v1234567890123" 
                allowfullscreen="" 
                loading="lazy" 
                referrerpolicy="no-referrer-when-downgrade">
            </iframe>
        </div>
    </div>
</section>

<?php include 'inc/footer-new.php'; ?>

