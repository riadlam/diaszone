// Hero Slider Functionality
class HeroSlider {
    constructor() {
        this.currentSlide = 0;
        const sliderElement = document.querySelector('.gaming-hero-slider');
        if (!sliderElement) return;

        this.sliderElement = sliderElement;
        this.sliderContainer = sliderElement.querySelector('.slider-container');
        this.slides = this.sliderContainer
            ? this.sliderContainer.querySelectorAll('.slide')
            : [];
        this.totalSlides = this.slides.length;
        this.dots = sliderElement.querySelectorAll('.slider-dot');
        this.prevBtn = sliderElement.querySelector('.slider-prev');
        this.nextBtn = sliderElement.querySelector('.slider-next');
        this.autoplayInterval = null;
        this.prefersReducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

        this.init();
    }

    init() {
        if (!this.sliderContainer || this.totalSlides === 0) return;

        this.setupEventListeners();
        this.waitForLayout(() => {
            this.updateSlider(false);
            this.startAutoplay();
        });
    }

    setupEventListeners() {
        if (this.prevBtn) {
            this.prevBtn.addEventListener('click', (event) => {
                event.preventDefault();
                this.prevSlide();
            });
        }

        if (this.nextBtn) {
            this.nextBtn.addEventListener('click', (event) => {
                event.preventDefault();
                this.nextSlide();
            });
        }

        this.dots.forEach((dot, index) => {
            dot.addEventListener('click', (event) => {
                event.preventDefault();
                this.goToSlide(index);
            });
        });

        document.addEventListener('keydown', (event) => {
            if (!this.sliderElement.contains(document.activeElement) && document.activeElement !== document.body) {
                return;
            }

            if (event.key === 'ArrowLeft') {
                event.preventDefault();
                this.prevSlide();
            }

            if (event.key === 'ArrowRight') {
                event.preventDefault();
                this.nextSlide();
            }
        });

        this.initTouchEvents();

        let resizeTimeout;
        window.addEventListener('resize', () => {
            clearTimeout(resizeTimeout);
            resizeTimeout = setTimeout(() => {
                this.updateSlider(false);
            }, 150);
        });

        this.sliderElement.addEventListener('mouseenter', () => this.stopAutoplay());
        this.sliderElement.addEventListener('mouseleave', () => this.startAutoplay());
    }

    waitForLayout(callback) {
        const images = this.sliderElement.querySelectorAll('img');
        let loadedCount = 0;
        const totalImages = images.length;

        if (totalImages === 0) {
            setTimeout(callback, 50);
            return;
        }

        const checkComplete = () => {
            loadedCount += 1;
            if (loadedCount === totalImages) {
                setTimeout(callback, 50);
            }
        };

        images.forEach((image) => {
            if (image.complete) {
                checkComplete();
            } else {
                image.addEventListener('load', checkComplete, { once: true });
                image.addEventListener('error', checkComplete, { once: true });
            }
        });

        setTimeout(callback, 800);
    }

    goToSlide(index) {
        this.currentSlide = index;
        this.updateSlider();
    }

    nextSlide() {
        if (this.totalSlides <= 1) return;
        this.currentSlide = (this.currentSlide + 1) % this.totalSlides;
        this.updateSlider();
    }

    prevSlide() {
        if (this.totalSlides <= 1) return;
        this.currentSlide = (this.currentSlide - 1 + this.totalSlides) % this.totalSlides;
        this.updateSlider();
    }

    getSlideWidth() {
        const viewport = this.sliderContainer?.parentElement;
        if (!viewport) return 0;
        return viewport.getBoundingClientRect().width;
    }

    updateSlider(restartAutoplay = true) {
        if (!this.sliderContainer || this.totalSlides === 0) return;

        const slideWidth = this.getSlideWidth();
        if (slideWidth <= 0) return;

        this.sliderContainer.style.transform = `translateX(-${this.currentSlide * slideWidth}px)`;

        this.dots.forEach((dot, index) => {
            const isActive = index === this.currentSlide;
            dot.classList.toggle('is-active', isActive);
            dot.classList.toggle('active', isActive);
            dot.setAttribute('aria-selected', isActive ? 'true' : 'false');
        });

        if (restartAutoplay) {
            this.startAutoplay();
        }
    }

    startAutoplay() {
        this.stopAutoplay();
        if (this.prefersReducedMotion || this.totalSlides <= 1) return;

        this.autoplayInterval = setInterval(() => {
            this.nextSlide();
        }, 6000);
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

        this.sliderElement.addEventListener('touchstart', (event) => {
            startX = event.touches[0].clientX;
            isDragging = true;
            this.stopAutoplay();
        }, { passive: true });

        this.sliderElement.addEventListener('touchmove', (event) => {
            if (!isDragging) return;
            currentX = event.touches[0].clientX;
        }, { passive: true });

        this.sliderElement.addEventListener('touchend', () => {
            if (!isDragging) return;
            isDragging = false;

            const diffX = startX - currentX;
            const threshold = 48;

            if (Math.abs(diffX) > threshold) {
                if (diffX > 0) {
                    this.nextSlide();
                } else {
                    this.prevSlide();
                }
            } else {
                this.startAutoplay();
            }
        });
    }
}

document.addEventListener('DOMContentLoaded', () => {
    new HeroSlider();
});
