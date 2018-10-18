import Swiper from 'swiper';
require('viewport-units-buggyfill').init();

export default {
    init() {
        // JavaScript to be fired on all pages
        this.initEls();
        this.initEvents();
    },

    initEls() {
        this.$els = {
            body: document.querySelector('body'),
            color: document.querySelector('body').getAttribute('data-color'),
            mediationSlider: document.querySelector('.js-mediation__slider'),
        };
        this.mediationSlider = null;
        this.mediationSliderId = '.mediation__slider';
    },

    initEvents() {
        this.initColorPage();
        this.initMenu();
        this.initCommonSliders();
    },

    initColorPage() {
        this.$els.body.style.setProperty('--page-color', this.$els.color);
    },

    initMenu() {
        $('.hamburger').click(function () {
            $(this).toggleClass('is-active');
        });
    },

    initCommonSliders() {
        if (this.$els.mediationSlider !== null) {
            this.initMediationSlider();
        }
    },

    initMediationSlider() {
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
    },

    finalize() {
        // JavaScript to be fired on all pages, after page specific JS is fired
    },
};
