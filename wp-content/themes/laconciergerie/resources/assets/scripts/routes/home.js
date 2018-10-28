import Swiper from 'swiper';

export default {
    init() {
        // JavaScript to be fired on all pages
        this.initEls();
        this.initEvents();
        console.log('init home init');
    },

    initEls() {
        this.$els = {
            body: document.querySelector('body'),
            currentExhibition: document.querySelector('.swiper-slide.current'),
            buttonScrollSeason: $('.js-scroll-season'),
        };
        this.currentlySlider = null;
        this.seasonSlider = null;
        this.currentlySliderId = '.currently__slider';
        this.seasonSliderId = '.season__slider';
    },

    initEvents() {
        this.initSliders();
        this.$els.buttonScrollSeason.on('click', this.scrollToSeason.bind(this))
    },

    initSliders() {
        console.log('init sliders home');
        this.currentlySlider = new Swiper(this.currentlySliderId, {
            watchOverflow: true,
            spaceBetween: 0,
        });
        console.log('init sliders home 2');

        this.seasonSlider = new Swiper(this.seasonSliderId, {
            watchOverflow: true,
            slidesPerView: 3,
            spaceBetween: 10,
            scrollbar: {
                el: `.swiper-scrollbar, ${this.seasonSliderId}`,
                draggable: true,
            },
            breakpoints: {
                1366: {
                    slidesPerView: 3,
                },
                768: {
                    slidesPerView: 2,
                },
                640: {
                    slidesPerView: 1,
                },
            },
        });

        const currentIndex = $(this.$els.currentExhibition).index();
        this.seasonSlider.slideTo(currentIndex);
    },

    scrollToSeason() {
        $('html, body').animate({scrollTop: $('#currentSeason').offset().top - 70}, 500);
    },

    finalize() {
        console.log("finalize home");
        console.log("----------");
        // JavaScript to be fired on the home page, after the init JS
    },
};
