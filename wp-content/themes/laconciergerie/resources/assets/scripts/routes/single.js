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
            slider: document.querySelector('.gallery__slider'),
        };

        this.gallerySlider = null;
        this.gallerySliderId = '.gallery__slider';
    },

    initEvents() {
        this.initSliders();
    },

    initSliders() {
        if(this.$els.slider) {
            this.gallerySlider = new Swiper(this.gallerySliderId, {
                watchOverflow: true,
                navigation: {
                    nextEl: `.swiper-button-next, ${this.gallerySliderId}`,
                    prevEl: `.swiper-button-prev, ${this.gallerySliderId}`,
                },
                autoHeight: true,
            });
        }


    },

    finalize() {
        // JavaScript to be fired on the home page, after the init JS
    },
};
