import Swiper from 'swiper';
import { Autoplay, EffectCoverflow, Pagination } from 'swiper/modules';
import 'swiper/css';
import 'swiper/css/effect-coverflow';
import 'swiper/css/pagination';

function initCoverflowHero() {
    const root = document.querySelector('.diaszone-swiper');
    if (!root || root.dataset.swiperReady === '1') return;

    const slides = root.querySelectorAll('.swiper-slide');
    if (slides.length === 0) return;

    root.dataset.swiperReady = '1';

    new Swiper(root, {
        modules: [EffectCoverflow, Pagination, Autoplay],
        effect: 'coverflow',
        grabCursor: true,
        centeredSlides: true,
        slidesPerView: 'auto',
        loop: slides.length > 2,
        speed: 700,
        watchSlidesProgress: true,
        coverflowEffect: {
            rotate: 0,
            stretch: 0,
            depth: 400,
            modifier: 1,
            slideShadows: false,
        },
        pagination: {
            el: root.querySelector('.swiper-pagination'),
            clickable: true,
        },
        autoplay: slides.length > 1
            ? {
                delay: 3000,
                disableOnInteraction: false,
                pauseOnMouseEnter: true,
            }
            : false,
        breakpoints: {
            0: {
                spaceBetween: 12,
            },
            1024: {
                spaceBetween: 24,
            },
        },
    });
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initCoverflowHero);
} else {
    initCoverflowHero();
}
