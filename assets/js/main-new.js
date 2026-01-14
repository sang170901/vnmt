// =======================================================
// MAIN JAVASCRIPT FOR VNMATERIAL WEBSITE
// =======================================================

document.addEventListener('DOMContentLoaded', function() {
    
    // =======================================================
    // SLIDER FUNCTIONALITY
    // =======================================================
    
    let currentSlide = 0;
    const slides = document.querySelectorAll('.slide');
    const dots = document.querySelectorAll('.dot');
    const totalSlides = slides.length;
    
    if (slides.length > 0) {
        
        // Function to go to specific slide
        function goToSlide(slideIndex) {
            // Remove active class from current slide
            slides[currentSlide].classList.remove('active');
            if (dots.length > 0 && dots[currentSlide]) {
                dots[currentSlide].classList.remove('active');
            }
            
            // Update current slide index
            currentSlide = slideIndex;
            
            // Add active class to new slide
            slides[currentSlide].classList.add('active');
            if (dots.length > 0 && dots[currentSlide]) {
                dots[currentSlide].classList.add('active');
            }
            
            // Note: This slider uses CSS opacity/visibility transitions, not translateX
        }
        
        // Function to go to next slide
        function nextSlide() {
            const nextIndex = (currentSlide + 1) % totalSlides;
            goToSlide(nextIndex);
        }
        
        // Function to go to previous slide
        function prevSlide() {
            const prevIndex = (currentSlide - 1 + totalSlides) % totalSlides;
            goToSlide(prevIndex);
        }
        
        // Arrow button event listeners
        const nextArrow = document.querySelector('.slider-next');
        const prevArrow = document.querySelector('.slider-prev');
        
        if (nextArrow) {
            nextArrow.addEventListener('click', nextSlide);
        }
        
        if (prevArrow) {
            prevArrow.addEventListener('click', prevSlide);
        }
        
        // Expose functions to global scope for inline onclick handlers
        window.nextSlide = nextSlide;
        window.prevSlide = prevSlide;
        
        // Dot navigation event listeners
        dots.forEach((dot, index) => {
            dot.addEventListener('click', () => {
                goToSlide(index);
            });
        });
        
        // Auto-play slider (optional)
        let autoPlayInterval;
        
        function startAutoPlay() {
            autoPlayInterval = setInterval(nextSlide, 3000); // Change slide every 3 seconds
        }
        
        function stopAutoPlay() {
            clearInterval(autoPlayInterval);
        }
        
        // Start auto-play
        startAutoPlay();
        
        // Pause auto-play on hover
        const sliderContainer = document.querySelector('.slider-container');
        if (sliderContainer) {
            sliderContainer.addEventListener('mouseenter', stopAutoPlay);
            sliderContainer.addEventListener('mouseleave', startAutoPlay);
        }
        
        // Handle slide clicks to navigate to product pages
        slides.forEach(slide => {
            slide.addEventListener('click', function() {
                const link = this.getAttribute('data-link');
                if (link) {
                    window.location.href = link;
                }
            });
        });
        
        // Global functions for inline onclick events (backward compatibility)
        window.changeSlide = function(direction) {
            if (direction === 1) {
                nextSlide();
            } else if (direction === -1) {
                prevSlide();
            }
        };
        
        window.currentSlide = function(slideIndex) {
            goToSlide(slideIndex - 1); // Convert from 1-based to 0-based index
        };
    }
    
    // =======================================================
    // SMOOTH SCROLLING FOR ANCHOR LINKS
    // =======================================================
    
    const anchorLinks = document.querySelectorAll('a[href^="#"]');
    anchorLinks.forEach(link => {
        link.addEventListener('click', function(e) {
            const targetId = this.getAttribute('href');
            if (targetId !== '#' && targetId.length > 1) {
                const targetElement = document.querySelector(targetId);
                if (targetElement) {
                    e.preventDefault();
                    targetElement.scrollIntoView({
                        behavior: 'smooth',
                        block: 'start'
                    });
                }
            }
        });
    });
    
    // =======================================================
    // INTERSECTION OBSERVER FOR ANIMATIONS
    // =======================================================
    
    // Animate elements when they come into view
    const observerOptions = {
        threshold: 0.1,
        rootMargin: '0px 0px -50px 0px'
    };
    
    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.style.opacity = '1';
                entry.target.style.transform = 'translateY(0)';
            }
        });
    }, observerOptions);
    
    // Observe elements that should animate on scroll
    const animateElements = document.querySelectorAll('.stat-item, .mission-card, .news-item, .partner-item');
    animateElements.forEach(el => {
        el.style.opacity = '0';
        el.style.transform = 'translateY(30px)';
        el.style.transition = 'opacity 0.6s ease, transform 0.6s ease';
        observer.observe(el);
    });
    
    // =======================================================
    // SEARCH FUNCTIONALITY (Basic)
    // =======================================================
    
    const searchTags = document.querySelectorAll('.search-tag');
    searchTags.forEach(tag => {
        tag.addEventListener('click', function(e) {
            e.preventDefault();
            const searchTerm = this.textContent.trim();
            // Here you could implement actual search functionality
            console.log('Searching for:', searchTerm);
            // For now, just add some visual feedback
            this.style.background = '#2563eb';
            this.style.color = 'white';
            setTimeout(() => {
                this.style.background = '';
                this.style.color = '';
            }, 1000);
        });
    });
    
    // =======================================================
    // MOBILE MENU TOGGLE (if needed in future)
    // =======================================================
    
    // This can be expanded later if mobile menu is needed
    
    // =======================================================
    // PERFORMANCE OPTIMIZATIONS
    // =======================================================
    
    // Lazy load images when they come into view
    const images = document.querySelectorAll('img[data-src]');
    const imageObserver = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                const img = entry.target;
                img.src = img.dataset.src;
                img.removeAttribute('data-src');
                imageObserver.unobserve(img);
            }
        });
    });
    
    images.forEach(img => imageObserver.observe(img));
    
    // =======================================================
    // ERROR HANDLING
    // =======================================================
    
    window.addEventListener('error', function(e) {
        console.log('An error occurred:', e.error);
        // You could send error logs to a service here
    });
    
});

// =======================================================
// UTILITY FUNCTIONS
// =======================================================

// Debounce function for performance
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

// Throttle function for scroll events
function throttle(func, limit) {
    let inThrottle;
    return function() {
        const args = arguments;
        const context = this;
        if (!inThrottle) {
            func.apply(context, args);
            inThrottle = true;
            setTimeout(() => inThrottle = false, limit);
        }
    }
}