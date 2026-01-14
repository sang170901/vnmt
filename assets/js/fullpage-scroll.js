/**
 * Full Page Scroll Navigation
 * Tối ưu cho màn hình 14 inch (1920x1080)
 */

(function() {
    'use strict';
    
    let currentSection = 0;
    let sections = [];
    let isScrolling = false;
    let scrollTimeout;
    
    // Initialize when DOM is ready
    function init() {
        const container = document.querySelector('.fullpage-container');
        if (!container) return;
        
        sections = Array.from(document.querySelectorAll('.fullpage-section'));
        if (sections.length === 0) return;
        
        // Create navigation dots
        createNavigationDots();
        
        // Create navigation arrows
        createNavigationArrows();
        
        // Create progress bar
        createProgressBar();
        
        // Update active section on scroll
        container.addEventListener('scroll', handleScroll, { passive: true });
        
        // Update active section on load
        updateActiveSection();
        
        // Handle keyboard navigation
        document.addEventListener('keydown', handleKeyboard);
        
        // Handle resize
        window.addEventListener('resize', debounce(updateActiveSection, 150));
        
        // Smooth scroll to section on hash change
        window.addEventListener('hashchange', handleHashChange);
    }
    
    // Create navigation dots
    function createNavigationDots() {
        const navContainer = document.createElement('div');
        navContainer.className = 'fullpage-nav-dots';
        
        const sectionLabels = [
            'Trang chủ',
            'Danh mục',
            'Tìm kiếm',
            'Tin tức',
            'Đối tác',
            'Giới thiệu'
        ];
        
        sections.forEach((section, index) => {
            const dot = document.createElement('div');
            dot.className = 'fullpage-dot';
            dot.setAttribute('data-section', index);
            dot.setAttribute('data-label', sectionLabels[index] || `Section ${index + 1}`);
            dot.addEventListener('click', () => scrollToSection(index));
            navContainer.appendChild(dot);
        });
        
        document.body.appendChild(navContainer);
    }
    
    // Create navigation arrows
    function createNavigationArrows() {
        const arrowsContainer = document.createElement('div');
        arrowsContainer.className = 'fullpage-nav-arrows';
        
        const prevArrow = document.createElement('div');
        prevArrow.className = 'fullpage-arrow fullpage-arrow-prev';
        prevArrow.innerHTML = '<i class="fas fa-chevron-up"></i>';
        prevArrow.addEventListener('click', () => scrollToSection(currentSection - 1));
        
        const nextArrow = document.createElement('div');
        nextArrow.className = 'fullpage-arrow fullpage-arrow-next';
        nextArrow.innerHTML = '<i class="fas fa-chevron-down"></i>';
        nextArrow.addEventListener('click', () => scrollToSection(currentSection + 1));
        
        arrowsContainer.appendChild(prevArrow);
        arrowsContainer.appendChild(nextArrow);
        
        document.body.appendChild(arrowsContainer);
    }
    
    // Create progress bar
    function createProgressBar() {
        const progressBar = document.createElement('div');
        progressBar.className = 'fullpage-progress';
        document.body.appendChild(progressBar);
    }
    
    // Handle scroll event
    function handleScroll() {
        if (isScrolling) return;
        
        clearTimeout(scrollTimeout);
        scrollTimeout = setTimeout(() => {
            updateActiveSection();
        }, 100);
        
        updateProgress();
    }
    
    // Update active section based on scroll position
    function updateActiveSection() {
        const container = document.querySelector('.fullpage-container');
        if (!container) return;
        
        const scrollTop = container.scrollTop || window.pageYOffset;
        const windowHeight = window.innerHeight;
        
        let newActiveSection = 0;
        let minDistance = Infinity;
        
        sections.forEach((section, index) => {
            const rect = section.getBoundingClientRect();
            const distance = Math.abs(rect.top);
            
            if (distance < minDistance && rect.top <= windowHeight / 2) {
                minDistance = distance;
                newActiveSection = index;
            }
        });
        
        if (newActiveSection !== currentSection) {
            currentSection = newActiveSection;
            updateNavigation();
        }
    }
    
    // Scroll to specific section
    function scrollToSection(index) {
        if (index < 0 || index >= sections.length) return;
        
        isScrolling = true;
        currentSection = index;
        
        const section = sections[index];
        const container = document.querySelector('.fullpage-container');
        
        if (container && section) {
            const offsetTop = section.offsetTop;
            container.scrollTo({
                top: offsetTop,
                behavior: 'smooth'
            });
        }
        
        updateNavigation();
        
        // Reset scrolling flag after animation
        setTimeout(() => {
            isScrolling = false;
        }, 1000);
    }
    
    // Update navigation UI
    function updateNavigation() {
        // Update dots
        const dots = document.querySelectorAll('.fullpage-dot');
        dots.forEach((dot, index) => {
            if (index === currentSection) {
                dot.classList.add('active');
            } else {
                dot.classList.remove('active');
            }
        });
        
        // Update arrows
        const prevArrow = document.querySelector('.fullpage-arrow-prev');
        const nextArrow = document.querySelector('.fullpage-arrow-next');
        
        if (prevArrow) {
            if (currentSection === 0) {
                prevArrow.classList.add('disabled');
            } else {
                prevArrow.classList.remove('disabled');
            }
        }
        
        if (nextArrow) {
            if (currentSection === sections.length - 1) {
                nextArrow.classList.add('disabled');
            } else {
                nextArrow.classList.remove('disabled');
            }
        }
    }
    
    // Update progress bar
    function updateProgress() {
        const container = document.querySelector('.fullpage-container');
        if (!container) return;
        
        const scrollTop = container.scrollTop || window.pageYOffset;
        const scrollHeight = container.scrollHeight - container.clientHeight;
        const progress = scrollHeight > 0 ? (scrollTop / scrollHeight) * 100 : 0;
        
        const progressBar = document.querySelector('.fullpage-progress');
        if (progressBar) {
            progressBar.style.width = progress + '%';
        }
    }
    
    // Handle keyboard navigation
    function handleKeyboard(e) {
        // Only handle if not in input/textarea
        if (e.target.tagName === 'INPUT' || e.target.tagName === 'TEXTAREA') {
            return;
        }
        
        switch(e.key) {
            case 'ArrowDown':
            case 'PageDown':
                e.preventDefault();
                scrollToSection(currentSection + 1);
                break;
            case 'ArrowUp':
            case 'PageUp':
                e.preventDefault();
                scrollToSection(currentSection - 1);
                break;
            case 'Home':
                e.preventDefault();
                scrollToSection(0);
                break;
            case 'End':
                e.preventDefault();
                scrollToSection(sections.length - 1);
                break;
        }
    }
    
    // Handle hash change
    function handleHashChange() {
        const hash = window.location.hash;
        if (hash) {
            const section = document.querySelector(hash);
            if (section && section.classList.contains('fullpage-section')) {
                const index = sections.indexOf(section);
                if (index !== -1) {
                    scrollToSection(index);
                }
            }
        }
    }
    
    // Debounce function
    function debounce(func, wait) {
        let timeout;
        return function executedFunction(...args) {
            const later = () => {
                clearTimeout(timeout);
                func(...args);
            };
            clearTimeout(timeout);
            timeout = setTimeout(later, wait);
        };
    }
    
    // Initialize on DOM ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
    
    // Export for external use
    window.FullPageScroll = {
        scrollToSection: scrollToSection,
        getCurrentSection: () => currentSection,
        getTotalSections: () => sections.length
    };
})();

