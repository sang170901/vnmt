    </main>
    <!-- MAIN CONTENT END -->

    <!-- Modern Compact Footer -->
    <footer class="footer">
        <div class="container">
            <div class="footer-grid">
                <!-- About Section -->
                <div class="footer-col">
                    <h3 class="footer-logo">VNMaterials</h3>
                    <p class="footer-desc"><?php echo t('footer_about_desc'); ?></p>
                    <div class="footer-contact">
                        <a href="tel:+84829300555"><i class="fas fa-phone"></i> 0829 300555</a>
                        <a href="mailto:info@vnmaterial.vn"><i class="fas fa-envelope"></i> info.vnmaterials@gmail.com</a>
                    </div>
                </div>
                
                <!-- Quick Links -->
                <div class="footer-col">
                    <h4 class="footer-title">NHÀ CUNG CẤP</h4>
                    <ul class="footer-links">
                        <li><a href="materials.php"><?php echo t('materials_title'); ?></a></li>
                        <li><a href="equipment.php"><?php echo t('equipment_title'); ?></a></li>
                        <li><a href="technology.php"><?php echo t('technology_title'); ?></a></li>
                        <li><a href="landscape.php"><?php echo t('landscape_title'); ?></a></li>
                    </ul>
                </div>
                
                <!-- Company -->
                <div class="footer-col">
                    <h4 class="footer-title">CÔNG TY</h4>
                    <ul class="footer-links">
                        <li><a href="index.php#about"><?php echo t('nav_about'); ?></a></li>
                        <li><a href="news.php"><?php echo t('nav_news'); ?></a></li>
                        <li><a href="suppliers.php">ĐỐI TÁC</a></li>
                        <li><a href="contact.php"><?php echo t('nav_contact'); ?></a></li>
                    </ul>
                </div>
                
                <!-- Social -->
                <div class="footer-col">
                    <h4 class="footer-title">MẠNG XÃ HỘI</h4>
                    <div class="social-links">
                        <a href="#" class="social-link" title="Facebook"><i class="fab fa-facebook-f"></i></a>
                        <a href="#" class="social-link" title="LinkedIn"><i class="fab fa-linkedin-in"></i></a>
                        <a href="#" class="social-link" title="YouTube"><i class="fab fa-youtube"></i></a>
                        <a href="#" class="social-link" title="Instagram"><i class="fab fa-instagram"></i></a>
                    </div>
                    <div class="footer-badge">
                        <span>🏆 Tin cậy bởi 500+</span>
                        <small>Nhà thầu & Chủ đầu tư</small>
                    </div>
                </div>
            </div>
            
            <!-- Footer Bottom -->
            <div class="footer-bottom">
                <p class="copyright"><?php echo t('footer_copyright', ['year' => date('Y')]); ?></p>
                <div class="footer-legal">
                    <a href="<?php echo buildLangUrl('terms'); ?>">Điều khoản sử dụng</a>
                    <span class="footer-separator">|</span>
                    <a href="<?php echo buildLangUrl('provider-rights'); ?>">Quyền và nghĩa vụ nhà cung cấp</a>
                    <span class="footer-separator">|</span>
                    <a href="<?php echo buildLangUrl('website-usage'); ?>">Sử dụng website</a>
                    <span class="footer-separator">|</span>
                    <a href="<?php echo buildLangUrl('legal-responsibility'); ?>">Trách nhiệm pháp lý</a>
                </div>
            </div>
        </div>
    </footer>
    
    <style>
    /* 🎨 Modern Compact Footer */
    .footer {
        background: linear-gradient(135deg, #1e293b 0%, #0f172a 100%);
        color: #e2e8f0;
        padding: 50px 0 0;
        margin-top: 80px;
        position: relative;
    }
    
    .footer::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 1px;
        background: linear-gradient(90deg, transparent, #38bdf8, transparent);
    }
    
    .footer-grid {
        display: grid;
        grid-template-columns: 1.3fr 1fr 1fr 1fr;
        gap: 40px;
        padding-bottom: 40px;
        border-bottom: 1px solid rgba(148, 163, 184, 0.1);
    }
    
    .footer-col {
        display: flex;
        flex-direction: column;
        gap: 16px;
        align-items: flex-start;
        text-align: left;
    }
    
    /* Dịch 2 cột giữa (KHÁM PHÁ và CÔNG TY) sang phải 10% */
    .footer-col:nth-child(2),
    .footer-col:nth-child(3) {
        margin-left: 10%;
    }

    /* Mobile: Căn giữa tất cả các cột */
    @media (max-width: 768px) {
        .footer-col {
            align-items: center;
            text-align: center;
        }

        .footer-col:first-child {
            align-items: center;
            text-align: center;
        }

        .footer-col:nth-child(2),
        .footer-col:nth-child(3) {
            margin-left: 0;
            align-items: center;
            text-align: center;
        }

        .footer-logo {
            text-align: center;
        }

        .footer-desc {
            text-align: center;
        }

        .footer-contact {
            align-items: center;
            justify-content: center;
        }

        .footer-contact a {
            justify-content: center;
        }

        .footer-title {
            text-align: center;
        }

        .footer-links {
            align-items: center;
        }

        .footer-links a {
            text-align: center;
        }
    }
    
    .footer-logo {
        font-size: 1.5rem;
        font-weight: 800;
        color: #38bdf8;
        margin: 0 0 8px 0;
        letter-spacing: -0.02em;
        text-align: left;
    }
    
    .footer-desc {
        font-size: 0.9rem;
        color: #94a3b8;
        line-height: 1.6;
        margin: 0;
        text-align: left;
    }
    
    .footer-contact {
        display: flex;
        flex-direction: column;
        gap: 8px;
        margin-top: 8px;
        width: 100%;
    }
    
    .footer-contact a {
        color: #cbd5e1;
        text-decoration: none;
        font-size: 0.875rem;
        display: flex;
        align-items: center;
        gap: 8px;
        transition: all 0.3s;
        justify-content: flex-start;
    }
    
    .footer-contact a:hover {
        color: #38bdf8;
        transform: translateX(4px);
    }
    
    .footer-contact i {
        width: 16px;
        color: #38bdf8;
        flex-shrink: 0;
    }
    
    .footer-title {
        font-size: 0.875rem;
        font-weight: 700;
        color: #f1f5f9;
        margin: 0 0 16px 0;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        text-align: left;
        width: 100%;
    }
    
    /* Tiêu đề "MẠNG XÃ HỘI" căn giữa */
    .footer-col:last-child .footer-title {
        text-align: center;
    }
    
    .footer-links {
        list-style: none;
        padding: 0;
        margin: 0;
        display: flex;
        flex-direction: column;
        gap: 10px;
        align-items: flex-start;
        width: 100%;
    }
    
    .footer-links li {
        width: 100%;
        display: flex;
        justify-content: flex-start;
    }
    
    .footer-links a {
        color: #cbd5e1;
        text-decoration: none;
        font-size: 0.875rem;
        transition: all 0.3s;
        display: block;
        text-align: left;
        width: 100%;
    }
    
    /* Chỉ in hoa phần KHÁM PHÁ (cột thứ 2) */
    .footer-col:nth-child(2) .footer-links a {
        text-transform: uppercase;
    }
    
    .footer-links a:hover {
        color: #38bdf8;
    }
    
    .social-links {
        display: flex;
        gap: 12px;
        flex-wrap: wrap;
        justify-content: center;
        margin-bottom: 0;
        width: 100%;
    }
    
    .social-link {
        width: 40px;
        height: 40px;
        border-radius: 10px;
        background: rgba(56, 189, 248, 0.1);
        border: 1px solid rgba(56, 189, 248, 0.2);
        display: flex;
        align-items: center;
        justify-content: center;
        color: #38bdf8;
        text-decoration: none;
        transition: all 0.3s;
        font-size: 1rem;
    }
    
    .social-link:hover {
        background: #38bdf8;
        color: white;
        transform: translateY(-4px);
        box-shadow: 0 8px 20px rgba(56, 189, 248, 0.3);
    }
    
    .footer-badge {
        margin-top: 12px;
        padding: 12px 16px;
        background: rgba(56, 189, 248, 0.05);
        border: 1px solid rgba(56, 189, 248, 0.1);
        border-radius: 10px;
        text-align: center;
        width: 100%;
    }
    
    .footer-badge span {
        display: block;
        font-size: 0.9rem;
        font-weight: 600;
        color: #38bdf8;
        margin-bottom: 4px;
    }
    
    .footer-badge small {
        font-size: 0.75rem;
        color: #94a3b8;
    }
    
    .footer-bottom {
        padding: 24px 0;
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
        gap: 16px;
    }
    
    .copyright {
        font-size: 0.875rem;
        color: #64748b;
        margin: 0;
    }
    
    .footer-legal {
        display: flex;
        gap: 16px;
        flex-wrap: wrap;
        justify-content: flex-end;
        align-items: center;
    }
    
    .footer-legal a {
        font-size: 0.875rem;
        color: #64748b;
        text-decoration: none;
        transition: color 0.3s;
        white-space: nowrap;
        padding: 4px 0;
    }
    
    .footer-legal a:hover {
        color: #38bdf8;
    }
    
    .footer-separator {
        color: #475569;
        font-size: 0.75rem;
        user-select: none;
    }
    
    /* Responsive */
    @media (max-width: 1024px) {
        .footer-grid {
            grid-template-columns: repeat(2, 1fr);
            gap: 25px;
        }
    }
    
    @media (max-width: 768px) {
        .footer {
            padding: 35px 0 0;
            margin-top: 50px;
        }

        .footer .container {
            padding: 0 1rem;
        }
        
        .footer-grid {
            grid-template-columns: repeat(2, 1fr);
            gap: 24px;
            padding-bottom: 30px;
        }
        
        .footer-logo {
            font-size: 1.3rem;
        }
        
        .footer-desc {
            font-size: 0.85rem;
            line-height: 1.5;
        }

        .footer-contact a {
            font-size: 0.85rem;
        }
        
        .footer-links a {
            font-size: 0.85rem;
        }
        
        .footer-title {
            font-size: 0.85rem;
            margin-bottom: 14px;
        }
        
        .social-link {
            width: 38px;
            height: 38px;
            font-size: 0.9rem;
        }
        
        .footer-badge {
            padding: 10px 14px;
            margin-top: 10px;
        }
        
        .footer-badge span {
            font-size: 0.85rem;
        }
        
        .footer-badge small {
            font-size: 0.7rem;
        }
        
        .footer-bottom {
            padding: 20px 1rem;
            flex-wrap: wrap;
        }
        
        .copyright {
            font-size: 0.8rem;
            text-align: center;
            width: 100%;
            margin-bottom: 8px;
        }

        .footer-legal {
            width: 100%;
            justify-content: center;
            flex-wrap: wrap;
            gap: 8px;
        }
        
        .footer-legal a {
            font-size: 0.75rem;
            white-space: nowrap;
        }

        .footer-separator {
            display: inline-block;
            margin: 0 4px;
        }
    }
    
    @media (max-width: 640px) {
        .footer {
            padding: 30px 0 0;
            margin-top: 40px;
        }

        .footer .container {
            padding: 0 0.75rem;
        }
        
        .footer-grid {
            display: flex; /* use flex so 2 cột có thể nằm cùng hàng ổn định */
            flex-wrap: wrap;
            grid-template-columns: 1fr;
            gap: 16px;
            padding-bottom: 20px;
            justify-content: center;
        }
        
        /* Hiển thị description (slogan) */
        .footer-desc {
            display: block;
            font-size: 0.8rem;
            line-height: 1.5;
            margin: 8px 0 12px 0;
            text-align: center;
        }
        
        /* Column 1: Logo + Slogan + Contact (1 dòng) */
        .footer-col:first-child {
            gap: 8px;
            padding-bottom: 16px;
            border-bottom: 1px solid rgba(148, 163, 184, 0.1);
            margin-bottom: 16px;
            align-items: center;
            text-align: center;
            width: 100%;
        }
        
        /* Contact: phone + email cùng 1 dòng */
        .footer-contact {
            display: flex;
            flex-direction: row;
            gap: 12px;
            margin-top: 4px;
            flex-wrap: wrap;
            justify-content: center;
            align-items: center;
        }
        
        .footer-contact a {
            font-size: 0.75rem;
            white-space: nowrap;
            justify-content: center;
        }
        
        .footer-contact i {
            width: 14px;
        }
        
        /* Column 1: Full width */
        .footer-col:first-child { 
            flex: 0 0 100%; 
        }
        
        /* Column 2: KHÁM PHÁ - Full width trên mobile */
        .footer-col:nth-child(2) {
            display: flex;
            flex-direction: column;
            flex: 0 0 100%;
            padding: 0;
            border: none;
            margin-right: 0;
            align-items: center;
            text-align: center;
            margin-top: 16px;
            padding-top: 16px;
            border-top: 1px solid rgba(148, 163, 184, 0.1);
        }
        
        /* Wrapper cho columns */
        .footer-grid::after {
            content: '';
            display: block;
            clear: both;
            padding-bottom: 16px;
            border-bottom: 1px solid rgba(148, 163, 184, 0.1);
            margin-bottom: 16px;
        }
        
        /* Ẩn cột CÔNG TY trên mobile */
        .footer-col:nth-child(3) {
            display: none !important;
        }

        /* Accordion title cho KHÁM PHÁ */
        .footer-col:nth-child(2) .footer-title {
            position: relative;
            font-size: 0.8rem !important;
            text-transform: uppercase !important;
            line-height: 1;
            margin-bottom: 10px;
            cursor: pointer;
            white-space: nowrap;
            text-align: center;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }
        .footer-col:nth-child(2) .footer-title::before { 
            content: ''; 
        }
        .footer-col:nth-child(2) .footer-title::after {
            content: '\f078';
            font-family: 'Font Awesome 5 Free';
            font-weight: 900;
            font-size: 0.7rem;
            color: #38bdf8;
            transition: transform 0.25s ease;
        }
        .footer-col:nth-child(2).active .footer-title::after { 
            transform: rotate(180deg); 
        }

        /* Collapsible links cho KHÁM PHÁ */
        .footer-col:nth-child(2) .footer-links { 
            max-height: 0; 
            overflow: hidden; 
            transition: max-height 0.3s ease;
            align-items: center;
        }
        .footer-col:nth-child(2).active .footer-links { 
            max-height: 320px; 
        }
        
        /* Column 4: Social - Title + Icons cùng 1 dòng */
        .footer-col:last-child {
            padding-top: 0;
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 8px;
            width: 100%;
            flex: 0 0 100%;
            margin-top: 16px;
            padding-top: 16px;
            border-top: 1px solid rgba(148, 163, 184, 0.1);
        }
        
        .footer-col:last-child .footer-title {
            font-size: 0.8rem;
            text-transform: none;
            margin: 0 0 2px 0;
            white-space: nowrap;
            text-align: center;
        }

        .footer-col:last-child .social-links {
            justify-content: center;
            margin-bottom: 0;
            gap: 8px;
        }
        
        .footer-col {
            gap: 10px;
        }
        
        .footer-logo {
            font-size: 1.2rem;
            margin-bottom: 0;
            text-align: center;
        }
        
        .footer-links a {
            font-size: 0.75rem;
            text-align: center;
        }
        
        .footer-links {
            gap: 6px;
            align-items: center;
        }
        
        .social-links {
            gap: 8px;
            display: flex;
            flex-direction: row;
            justify-content: center;
        }
        
        .social-link {
            width: 34px;
            height: 34px;
            font-size: 0.8rem;
            border-radius: 8px;
        }
        
        /* Ẩn badge */
        .footer-badge {
            display: none;
        }
        
        .footer-bottom {
            padding: 18px 0.75rem;
            flex-direction: column;
            text-align: center;
            gap: 12px;
        }
        
        .copyright {
            font-size: 0.75rem;
            line-height: 1.5;
            margin-bottom: 0;
        }
        
        .footer-legal {
            gap: 8px;
            justify-content: center;
            flex-wrap: wrap;
            width: 100%;
        }
        
        .footer-legal a {
            font-size: 0.7rem;
            white-space: normal;
            text-align: center;
            line-height: 1.4;
            padding: 2px 0;
        }
        
        .footer-separator {
            display: none;
        }
    }
    
    @media (max-width: 480px) {
        .footer {
            padding: 24px 0 0;
            margin-top: 32px;
        }

        .footer .container {
            padding: 0 0.75rem;
        }
        
        .footer-grid {
            padding-bottom: 18px;
            gap: 14px;
        }
        
        .footer-col:first-child {
            padding-bottom: 14px;
            margin-bottom: 14px;
        }
        
        .footer-logo {
            font-size: 1.1rem;
            margin-bottom: 6px;
        }
        
        .footer-desc {
            font-size: 0.75rem;
            margin: 6px 0 10px 0;
            line-height: 1.5;
        }
        
        .footer-contact {
            gap: 10px;
            flex-wrap: wrap;
        }
        
        .footer-contact a {
            font-size: 0.7rem;
            line-height: 1.4;
        }

        .footer-contact i {
            width: 14px;
        }
        
        /* Ẩn cột CÔNG TY trên mobile nhỏ */
        .footer-col:nth-child(3) {
            display: none !important;
        }

        .footer-col:nth-child(2) {
            flex: 0 0 100%;
            margin-right: 0;
        }
        
        .footer-col:nth-child(2) .footer-title {
            font-size: 0.75rem !important;
            margin-bottom: 8px;
        }

        .footer-col:nth-child(2) .footer-title::before {
            font-size: 0.75rem;
        }
        
        .footer-links a {
            font-size: 0.7rem;
            line-height: 1.4;
        }
        
        .footer-links {
            gap: 5px;
        }
        
        .footer-col:last-child {
            gap: 10px;
            flex-wrap: wrap;
        }
        
        .footer-col:last-child .footer-title {
            font-size: 0.75rem;
        }
        
        .social-links {
            gap: 7px;
            flex-wrap: wrap;
        }
        
        .social-link {
            width: 32px;
            height: 32px;
            font-size: 0.75rem;
        }
        
        .footer-bottom {
            padding: 15px 0.75rem;
            gap: 10px;
        }
        
        .copyright {
            font-size: 0.7rem;
            line-height: 1.5;
            margin-bottom: 0;
        }
        
        .footer-legal {
            gap: 6px;
            flex-wrap: wrap;
            justify-content: center;
            width: 100%;
        }
        
        .footer-legal a {
            font-size: 0.65rem;
            white-space: normal;
            text-align: center;
            line-height: 1.4;
            padding: 2px 0;
        }
        
        .footer-separator {
            display: none;
        }
    }
    
    @media (max-width: 375px) {
        .footer {
            padding: 20px 0 0;
            margin-top: 28px;
        }

        .footer .container {
            padding: 0 0.5rem;
        }
        
        .footer-grid {
            padding-bottom: 15px;
            gap: 12px;
        }
        
        .footer-col:first-child {
            padding-bottom: 12px;
            margin-bottom: 12px;
        }
        
        .footer-logo {
            font-size: 1.05rem;
            margin-bottom: 5px;
        }
        
        .footer-desc {
            font-size: 0.7rem;
            margin: 5px 0 8px 0;
            line-height: 1.4;
        }
        
        .footer-contact {
            gap: 8px;
            flex-wrap: wrap;
        }
        
        .footer-contact a {
            font-size: 0.68rem;
            line-height: 1.3;
        }

        .footer-contact i {
            width: 13px;
        }
        
        /* Ẩn cột CÔNG TY trên mobile nhỏ */
        .footer-col:nth-child(3) {
            display: none !important;
        }

        .footer-col:nth-child(2) {
            flex: 0 0 100%;
            margin-right: 0;
        }
        
        .footer-col:nth-child(2) .footer-title {
            font-size: 0.7rem !important;
            margin-bottom: 7px;
        }
        
        .footer-links a {
            font-size: 0.68rem;
        }
        
        .footer-links {
            gap: 4px;
        }
        
        .footer-col:last-child {
            gap: 8px;
        }
        
        .footer-col:last-child .footer-title {
            font-size: 0.7rem;
        }
        
        .social-link {
            width: 30px;
            height: 30px;
            font-size: 0.7rem;
        }
        
        .footer-bottom {
            padding: 12px 0.5rem;
            gap: 8px;
        }
        
        .copyright {
            font-size: 0.65rem;
            line-height: 1.4;
            margin-bottom: 0;
        }
        
        .footer-legal {
            gap: 6px;
            flex-wrap: wrap;
            justify-content: center;
            width: 100%;
        }
        
        .footer-legal a {
            font-size: 0.6rem;
            white-space: normal;
            text-align: center;
            line-height: 1.3;
            padding: 2px 0;
        }
        
        .footer-separator {
            display: none;
        }
    }
    
    @media (max-width: 320px) {
        .footer {
            padding: 18px 0 0;
            margin-top: 24px;
        }
        
        .footer-grid {
            padding-bottom: 14px;
        }
        
        .footer-col:first-child {
            padding-bottom: 10px;
            margin-bottom: 10px;
        }
        
        .footer-logo {
            font-size: 1rem;
        }
        
        .footer-desc {
            font-size: 0.68rem;
            margin: 4px 0 7px 0;
            line-height: 1.4;
        }
        
        .footer-contact {
            gap: 7px;
            flex-direction: column;
            align-items: flex-start;
        }
        
        .footer-contact a {
            font-size: 0.66rem;
        }
        
        /* Ẩn cột CÔNG TY trên mobile rất nhỏ */
        .footer-col:nth-child(3) {
            display: none !important;
        }

        .footer-col:nth-child(2) {
            width: 100%;
            margin-right: 0;
        }
        
        .footer-col:nth-child(2) .footer-title {
            font-size: 0.68rem;
            margin-bottom: 6px;
        }
        
        .footer-links a {
            font-size: 0.64rem;
        }
        
        .footer-links {
            gap: 4px;
        }
        
        .footer-col:last-child {
            gap: 7px;
        }
        
        .footer-col:last-child .footer-title {
            font-size: 0.68rem;
        }
        
        .social-links {
            gap: 5px;
        }
        
        .social-link {
            width: 28px;
            height: 28px;
            font-size: 0.68rem;
        }
        
        .footer-bottom {
            padding: 12px 0;
        }
        
        .copyright,
        .footer-legal a {
            font-size: 0.66rem;
        }
        
        .footer-legal {
            gap: 10px;
        }
    }
    </style>

    <!-- Chat Widget -->
    <div class="chat-widget">
        <!-- Chat Button -->
        <button class="chat-button" id="chatButton" aria-label="Chat với chúng tôi">
            <i class="fas fa-comments"></i>
            <span class="chat-button-text">Chat</span>
            <span class="chat-pulse"></span>
        </button>
        
        <!-- Chat Popup -->
        <div class="chat-popup" id="chatPopup">
            <div class="chat-popup-header">
                <div class="chat-popup-title">
                    <i class="fas fa-headset"></i>
                    <div>
                        <h4>Hỗ trợ trực tuyến</h4>
                        <p>Chúng tôi luôn sẵn sàng hỗ trợ bạn</p>
                    </div>
                </div>
                <button class="chat-close" id="chatClose">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            
            <div class="chat-popup-body">
                <a href="https://zalo.me/0829300555" target="_blank" class="chat-option zalo">
                    <div class="chat-option-icon">
                        <svg width="27" height="27" viewBox="0 0 48 48" fill="none">
                            <circle cx="24" cy="24" r="24" fill="white"/>
                            <path d="M24 8C14.059 8 6 15.163 6 24c0 4.988 2.4 9.413 6.15 12.463v5.537l5.213-2.85c1.488.413 3.075.65 4.637.65 9.941 0 18-7.163 18-16S33.941 8 24 8z" fill="#0068FF"/>
                            <path d="M28.788 26.525l-2.925-2.963 2.925-2.962c.375-.375.375-.975 0-1.35-.375-.375-.975-.375-1.35 0l-3.413 3.45-3.412-3.45c-.375-.375-.975-.375-1.35 0-.375.375-.375.975 0 1.35l2.925 2.962-2.925 2.963c-.375.375-.375.975 0 1.35.188.187.45.3.675.3.225 0 .487-.113.675-.3l3.412-3.45 3.413 3.45c.188.187.45.3.675.3.225 0 .487-.113.675-.3.375-.375.375-.975 0-1.35z" fill="white"/>
                        </svg>
                    </div>
                    <div class="chat-option-info">
                        <div class="chat-option-name">Chat qua Zalo</div>
                        <div class="chat-option-desc">Trả lời nhanh, hỗ trợ 24/7</div>
                    </div>
                    <i class="fas fa-chevron-right"></i>
                </a>
                
                <a href="tel:+84829300555" class="chat-option phone">
                    <div class="chat-option-icon">
                        <i class="fas fa-phone-alt"></i>
                    </div>
                    <div class="chat-option-info">
                        <div class="chat-option-name">Gọi điện</div>
                        <div class="chat-option-desc">0829 300555</div>
                    </div>
                    <i class="fas fa-chevron-right"></i>
                </a>
                
                <a href="mailto:info@vnmaterial.vn" class="chat-option email">
                    <div class="chat-option-icon">
                        <i class="fas fa-envelope"></i>
                    </div>
                    <div class="chat-option-info">
                        <div class="chat-option-name">Gửi Email</div>
                        <div class="chat-option-desc">info.vnmaterials@gmail.com</div>
                    </div>
                    <i class="fas fa-chevron-right"></i>
                </a>
            </div>
            
            <div class="chat-popup-footer">
                <small>Thời gian làm việc: 8:00 - 18:00 (T2-T7)</small>
            </div>
        </div>
    </div>

    <style>
    /* Chat Widget Styles */
    .chat-widget {
        position: fixed;
        bottom: 24px;
        right: 24px;
        z-index: 9999;
    }
    
    .chat-button {
        position: relative;
        width: 54px;
        height: 54px;
        border-radius: 50%;
        background: linear-gradient(135deg, #0ea5e9 0%, #06b6d4 100%);
        border: none;
        color: white;
        cursor: pointer;
        box-shadow: 0 6px 20px rgba(14, 165, 233, 0.4);
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        gap: 1px;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        overflow: visible;
    }
    
    .chat-button:hover {
        transform: scale(1.1);
        box-shadow: 0 10px 28px rgba(14, 165, 233, 0.5);
    }
    
    .chat-button i {
        font-size: 20px;
    }
    
    .chat-button-text {
        font-size: 8.5px;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }
    
    .chat-pulse {
        position: absolute;
        top: -2px;
        right: -2px;
        width: 14px;
        height: 14px;
        background: #ef4444;
        border-radius: 50%;
        border: 2px solid white;
        animation: pulse 2s infinite;
    }
    
    @keyframes pulse {
        0%, 100% { transform: scale(1); opacity: 1; }
        50% { transform: scale(1.2); opacity: 0.8; }
    }
    
    .chat-popup {
        position: absolute;
        bottom: 68px;
        right: 0;
        width: 306px;
        background: white;
        border-radius: 14px;
        box-shadow: 0 17px 51px rgba(0, 0, 0, 0.2);
        opacity: 0;
        visibility: hidden;
        transform: translateY(17px) scale(0.95);
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    }
    
    .chat-popup.active {
        opacity: 1;
        visibility: visible;
        transform: translateY(0) scale(1);
    }
    
    .chat-popup-header {
        padding: 17px;
        background: linear-gradient(135deg, #0ea5e9 0%, #06b6d4 100%);
        border-radius: 14px 14px 0 0;
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        color: white;
    }
    
    .chat-popup-title {
        display: flex;
        gap: 10px;
        align-items: flex-start;
    }
    
    .chat-popup-title i {
        font-size: 20px;
        margin-top: 3px;
    }
    
    .chat-popup-title h4 {
        margin: 0 0 3px 0;
        font-size: 0.96rem;
        font-weight: 700;
    }
    
    .chat-popup-title p {
        margin: 0;
        font-size: 0.74rem;
        opacity: 0.9;
    }
    
    .chat-close {
        background: rgba(255, 255, 255, 0.2);
        border: none;
        width: 28px;
        height: 28px;
        border-radius: 7px;
        color: white;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: all 0.3s;
    }
    
    .chat-close:hover {
        background: rgba(255, 255, 255, 0.3);
        transform: rotate(90deg);
    }
    
    .chat-popup-body {
        padding: 14px;
        display: flex;
        flex-direction: column;
        gap: 10px;
    }
    
    .chat-option {
        display: flex;
        align-items: center;
        gap: 14px;
        padding: 14px;
        background: #f8fafc;
        border-radius: 10px;
        text-decoration: none;
        color: inherit;
        transition: all 0.3s;
        border: 2px solid transparent;
    }
    
    .chat-option:hover {
        background: white;
        border-color: #0ea5e9;
        transform: translateX(3px);
        box-shadow: 0 3px 10px rgba(14, 165, 233, 0.15);
    }
    
    .chat-option-icon {
        width: 41px;
        height: 41px;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
        font-size: 17px;
    }
    
    .chat-option.zalo .chat-option-icon {
        background: linear-gradient(135deg, #0068FF 0%, #0052CC 100%);
        color: white;
    }
    
    .chat-option.phone .chat-option-icon {
        background: linear-gradient(135deg, #10b981 0%, #059669 100%);
        color: white;
    }
    
    .chat-option.email .chat-option-icon {
        background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
        color: white;
    }
    
    .chat-option-info {
        flex: 1;
    }
    
    .chat-option-name {
        font-weight: 600;
        color: #1e293b;
        margin-bottom: 3px;
        font-size: 0.9rem;
    }
    
    .chat-option-desc {
        font-size: 0.74rem;
        color: #64748b;
    }
    
    .chat-option i.fa-chevron-right {
        color: #cbd5e1;
        font-size: 12px;
    }
    
    .chat-popup-footer {
        padding: 10px 17px;
        background: #f8fafc;
        border-radius: 0 0 14px 14px;
        text-align: center;
        border-top: 1px solid #e2e8f0;
    }
    
    .chat-popup-footer small {
        color: #64748b;
        font-size: 0.64rem;
    }
    
    /* Mobile Responsive */
    @media (max-width: 768px) {
        .chat-widget {
            bottom: 12px;
            right: 12px;
        }
        
        .chat-button {
            width: 50px;
            height: 50px;
        }
        
        .chat-button i {
            font-size: 19px;
        }
        
        .chat-button-text {
            font-size: 7.5px;
        }
        
        .chat-popup {
            width: calc(100vw - 24px);
            right: -6px;
        }
        
        .chat-popup-header {
            padding: 15px;
        }
        
        .chat-popup-title h4 {
            font-size: 0.9rem;
        }
        
        .chat-popup-title p {
            font-size: 0.7rem;
        }
        
        .chat-popup-body {
            padding: 12px;
        }
        
        .chat-option {
            padding: 12px;
        }
        
        .chat-option-name {
            font-size: 0.85rem;
        }
        
        .chat-option-desc {
            font-size: 0.7rem;
        }
    }
    
    @media (max-width: 640px) {
        .chat-widget {
            bottom: 10px;
            right: 10px;
        }
        
        .chat-button {
            width: 48px;
            height: 48px;
        }
        
        .chat-button i {
            font-size: 18px;
        }
        
        .chat-button-text {
            font-size: 7px;
        }
        
        .chat-popup {
            width: calc(100vw - 20px);
            right: -5px;
        }
    }
    
    @media (max-width: 480px) {
        .chat-widget {
            bottom: 8px;
            right: 8px;
        }
        
        .chat-button {
            width: 46px;
            height: 46px;
        }
        
        .chat-button i {
            font-size: 17px;
        }
        
        .chat-button-text {
            font-size: 6.5px;
        }
        
        .chat-pulse {
            width: 12px;
            height: 12px;
        }
        
        .chat-popup {
            width: calc(100vw - 16px);
            right: -4px;
            bottom: 56px;
        }
        
        .chat-popup-header {
            padding: 13px;
        }
        
        .chat-popup-title h4 {
            font-size: 0.85rem;
        }
        
        .chat-popup-title p {
            font-size: 0.68rem;
        }
        
        .chat-popup-body {
            padding: 11px;
            gap: 8px;
        }
        
        .chat-option {
            padding: 11px;
            gap: 11px;
        }
        
        .chat-option-icon {
            width: 36px;
            height: 36px;
            font-size: 15px;
        }
        
        .chat-option-name {
            font-size: 0.8rem;
        }
        
        .chat-option-desc {
            font-size: 0.68rem;
        }
        
        .chat-popup-footer {
            padding: 8px 13px;
        }
        
        .chat-popup-footer small {
            font-size: 0.6rem;
        }
    }
    
    @media (max-width: 375px) {
        .chat-button {
            width: 44px;
            height: 44px;
        }
        
        .chat-button i {
            font-size: 16px;
        }
        
        .chat-pulse {
            width: 11px;
            height: 11px;
        }
    }
    
    @media (max-width: 320px) {
        .chat-widget {
            bottom: 6px;
            right: 6px;
        }
        
        .chat-button {
            width: 42px;
            height: 42px;
        }
        
        .chat-button i {
            font-size: 15px;
        }
        
        .chat-button-text {
            font-size: 6px;
        }
        
        .chat-popup-header {
            padding: 12px;
        }
        
        .chat-popup-body {
            padding: 10px;
        }
        
        .chat-option {
            padding: 10px;
        }
        
        .chat-option-icon {
            width: 34px;
            height: 34px;
        }
    }
    </style>
    
    <script>
    // Chat Widget Script + Footer Accordion (mobile)
    document.addEventListener('DOMContentLoaded', function() {
        const chatButton = document.getElementById('chatButton');
        const chatPopup = document.getElementById('chatPopup');
        const chatClose = document.getElementById('chatClose');
        
        if (chatButton && chatPopup && chatClose) {
            chatButton.addEventListener('click', function() {
                chatPopup.classList.toggle('active');
            });
            
            chatClose.addEventListener('click', function() {
                chatPopup.classList.remove('active');
            });
            
            // Close when clicking outside
            document.addEventListener('click', function(e) {
                if (!chatButton.contains(e.target) && !chatPopup.contains(e.target)) {
                    chatPopup.classList.remove('active');
                }
            });
        }

        // Footer accordion only on mobile width
        function bindFooterAccordion() {
            // Chỉ xử lý cột KHÁM PHÁ (nth-child(2)) trên mobile
            const cols = document.querySelectorAll('.footer-col:nth-child(2)');
            cols.forEach(function(col) {
                const title = col.querySelector('.footer-title');
                if (!title) return;
                if (title.dataset.bound === 'true') return;
                title.dataset.bound = 'true';
                title.addEventListener('click', function() {
                    col.classList.toggle('active');
                });
            });
        }

        if (window.innerWidth <= 640) bindFooterAccordion();
        window.addEventListener('resize', function() {
            if (window.innerWidth <= 640) bindFooterAccordion();
        });
    });
    </script>

    <!-- JAVASCRIPT -->
    <script src="assets/js/main-new.js"></script>
</body>
</html>
<?php
// End auto-translation output buffering
// DISABLED: Causes mixed language issues - use t() function instead
// end_auto_translate();
?>