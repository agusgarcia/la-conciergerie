import Swiper from 'swiper';

export default {
    init() {
        // JavaScript to be fired on all pages
        this.initEls();
        this.initEvents();
    },

    initEls() {
        this.$els = {
            body: document.querySelector('body'),
            currentExhibition: document.querySelector('.swiper-slide.current'),
        };
        this.currentlySlider = null;
        this.seasonSlider = null;
        this.mediationSlider = null;
        this.currentlySliderId = '.currently__slider';
        this.seasonSliderId = '.season__slider';
        this.mediationSliderId = '.mediation__slider';
    },

    initEvents() {
        this.initSliders();
    },

    initSliders() {
        this.currentlySlider = new Swiper(this.currentlySliderId, {
            watchOverflow: true,
        });
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

        this.mediationSlider = new Swiper(this.mediationSliderId, {
            watchOverflow: true,
            slidesPerView: 3,
            spaceBetween: 30,
            breakpoints: {
                960: {
                    slidesPerView: 2,
                },
                640: {
                    slidesPerView: 1,
                },
                480: {
                    slidesPerView: 1,
                },
            },
            navigation: {
                nextEl: `.swiper-button-next, ${this.mediationSliderId}`,
                prevEl: `.swiper-button-prev, ${this.mediationSliderId}`,
            },
        });

        const currentIndex = $(this.$els.currentExhibition).index();
        this.seasonSlider.slideTo(currentIndex);
    },

    finalize() {
        // JavaScript to be fired on the home page, after the init JS
    },
};
