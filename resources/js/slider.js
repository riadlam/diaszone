// Hero Slider Functionality
class HeroSlider {
    constructor() {
        this.currentSlide = 0;
        // Support both old hero-slider and new gaming-hero-slider
        const sliderElement = document.querySelector('.gaming-hero-slider') || document.querySelector('.hero-slider');
        if (!sliderElement) return;
        
        this.slides = sliderElement.querySelectorAll('.slider-container > div');
        this.totalSlides = this.slides.length;
        this.sliderContainer = sliderElement.querySelector('.slider-container');
        this.dots = sliderElement.querySelectorAll('.slider-dot');
        // Buttons are siblings, not children, so search from sliderElement
        this.prevBtn = sliderElement.querySelector('.slider-prev') || document.querySelector('.gaming-hero-slider .slider-prev');
        this.nextBtn = sliderElement.querySelector('.slider-next') || document.querySelector('.gaming-hero-slider .slider-next');
        this.autoplayInterval = null;
        this.sliderElement = sliderElement;
        
        this.init();
    }
    
    init() {
        if (!this.sliderContainer || this.totalSlides === 0) return;
        
        // Set up event listeners immediately (don't wait for images)
        this.setupEventListeners();
        
        // Wait for images to load and layout to be ready before positioning and autoplay
        this.waitForLayout(() => {
            // Calculate initial offset to center first slide
            this.calculateInitialOffset();
            
            // Start autoplay after everything is ready
            this.startAutoplay();
        });
    }
    
    setupEventListeners() {
        // Arrow navigation
        if (this.prevBtn) {
            this.prevBtn.addEventListener('click', (e) => {
                e.preventDefault();
                e.stopPropagation();
                console.log('Prev button clicked');
                this.prevSlide();
            });
        } else {
            console.warn('Previous button not found');
        }
        
        if (this.nextBtn) {
            this.nextBtn.addEventListener('click', (e) => {
                e.preventDefault();
                e.stopPropagation();
                console.log('Next button clicked');
                this.nextSlide();
            });
        } else {
            console.warn('Next button not found');
        }
        
        // Dot navigation
        this.dots.forEach((dot, index) => {
            dot.addEventListener('click', (e) => {
                e.preventDefault();
                e.stopPropagation();
                this.goToSlide(index);
            });
        });
        
        // Keyboard navigation
        document.addEventListener('keydown', (e) => {
            if (e.key === 'ArrowLeft') {
                e.preventDefault();
                this.prevSlide();
            }
            if (e.key === 'ArrowRight') {
                e.preventDefault();
                this.nextSlide();
            }
        });
        
        // Touch/swipe support
        this.initTouchEvents();
        
        // Handle window resize
        let resizeTimeout;
        window.addEventListener('resize', () => {
            clearTimeout(resizeTimeout);
            resizeTimeout = setTimeout(() => {
                this.updateSlider();
            }, 250);
        });
        
        // Pause on hover
        if (this.sliderElement) {
            this.sliderElement.addEventListener('mouseenter', () => this.stopAutoplay());
            this.sliderElement.addEventListener('mouseleave', () => this.startAutoplay());
        }
    }
    
    waitForLayout(callback) {
        // Wait for DOM to be ready
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', () => {
                this.waitForImages(callback);
            });
        } else {
            this.waitForImages(callback);
        }
    }
    
    waitForImages(callback) {
        const images = this.sliderElement.querySelectorAll('img');
        let loadedCount = 0;
        const totalImages = images.length;
        
        if (totalImages === 0) {
            // No images, just wait a bit for layout
            setTimeout(callback, 100);
            return;
        }
        
        const checkComplete = () => {
            loadedCount++;
            if (loadedCount === totalImages) {
                // Wait a bit more for layout to settle
                setTimeout(callback, 150);
            }
        };
        
        images.forEach(img => {
            if (img.complete) {
                checkComplete();
            } else {
                img.addEventListener('load', checkComplete);
                img.addEventListener('error', checkComplete); // Also proceed on error
            }
        });
        
        // Fallback timeout in case images don't load
        setTimeout(callback, 1000);
    }
    
    calculateInitialOffset() {
        if (!this.sliderContainer || this.slides.length === 0) return;
        
        const wrapper = this.sliderContainer.parentElement;
        if (!wrapper) return;
        
        // Force a reflow to ensure accurate measurements
        void wrapper.offsetHeight;
        
        const wrapperWidth = wrapper.getBoundingClientRect().width;
        const firstSlide = this.slides[0];
        
        if (!firstSlide) return;
        
        const slideWidth = firstSlide.getBoundingClientRect().width;
        
        // Only calculate if we have valid measurements
        if (wrapperWidth > 0 && slideWidth > 0) {
            // Center the first slide: (wrapper width - slide width) / 2
            const initialOffset = (wrapperWidth - slideWidth) / 2;
            this.sliderContainer.style.transform = `translateX(${initialOffset}px)`;
        }
    }
    
    goToSlide(index) {
        this.currentSlide = index;
        this.updateSlider();
    }
    
    nextSlide() {
        if (!this.sliderContainer || this.totalSlides === 0) return;
        this.currentSlide = (this.currentSlide + 1) % this.totalSlides;
        this.updateSlider();
    }
    
    prevSlide() {
        if (!this.sliderContainer || this.totalSlides === 0) return;
        this.currentSlide = (this.currentSlide - 1 + this.totalSlides) % this.totalSlides;
        this.updateSlider();
    }
    
    updateSlider() {
        if (!this.sliderContainer || this.slides.length === 0) return;
        
        const wrapper = this.sliderContainer.parentElement;
        if (!wrapper) return;
        
        // Get the first slide to calculate width
        const firstSlide = this.slides[0];
        if (!firstSlide) return;
        
        const wrapperWidth = wrapper.getBoundingClientRect().width;
        const slideWidth = firstSlide.getBoundingClientRect().width;
        const gap = 16; // 1rem = 16px (gap-4)
        
        // Calculate initial offset to center first slide
        const initialOffset = (wrapperWidth - slideWidth) / 2;
        
        // Calculate the distance to move from center position
        const translateX = initialOffset - (this.currentSlide * (slideWidth + gap));
        
        this.sliderContainer.style.transform = `translateX(${translateX}px)`;
        
        // Update dots
        this.dots.forEach((dot, index) => {
            if (index === this.currentSlide) {
                dot.classList.add('active');
                dot.style.opacity = '1';
            } else {
                dot.classList.remove('active');
                dot.style.opacity = '0.5';
            }
        });
    }
    
    startAutoplay() {
        this.stopAutoplay();
        if (!this.sliderContainer || this.totalSlides === 0) return;
        
        this.autoplayInterval = setInterval(() => {
            if (this.sliderContainer && this.totalSlides > 0) {
                this.nextSlide();
            }
        }, 6000); // Change slide every 6 seconds for gaming slider
    }
    
    stopAutoplay() {
        if (this.autoplayInterval) {
            clearInterval(this.autoplayInterval);
            this.autoplayInterval = null;
        }
    }
    
    initTouchEvents() {
        let startX = 0;
        let currentX = 0;
        let isDragging = false;
        
        if (!this.sliderElement) return;
        
        this.sliderElement.addEventListener('touchstart', (e) => {
            startX = e.touches[0].clientX;
            isDragging = true;
            this.stopAutoplay();
        });
        
        this.sliderElement.addEventListener('touchmove', (e) => {
            if (!isDragging) return;
            currentX = e.touches[0].clientX;
        });
        
        this.sliderElement.addEventListener('touchend', () => {
            if (!isDragging) return;
            isDragging = false;
            
            const diffX = startX - currentX;
            const threshold = 50;
            
            if (Math.abs(diffX) > threshold) {
                if (diffX > 0) {
                    this.nextSlide();
                } else {
                    this.prevSlide();
                }
            }
            
            this.startAutoplay();
        });
    }
}

// Initialize slider when DOM is ready
document.addEventListener('DOMContentLoaded', () => {
    new HeroSlider();
});

