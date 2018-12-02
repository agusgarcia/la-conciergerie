import Swiper from 'swiper';

require('viewport-units-buggyfill').init();

export default {
  init () {
    // JavaScript to be fired on all pages
    this.initEls();
    this.initEvents();
  },

  initEls () {
    this.$els = {
      body: document.querySelector('#body'),
      color: document.querySelector('#body').getAttribute('data-color'),
      sliderThree: document.querySelector('.js-slider--three'),
      sliderFour: document.querySelector('.js-slider--four'),
    };
    this.sliderThree = null;
    this.sliderFour = null;
    this.sliderThreeId = '.slider__three';
    this.sliderFourId = '.slider__four';
  },

  initEvents () {
    this.initColorPage();
    this.initMenu();
    this.initCommonSliders();
  },

  initColorPage () {
    this.$els.body.style.setProperty('--page-color', this.$els.color);
  },

  initMenu () {
    $('.hamburger').click(function () {
      $(this).toggleClass('is-active');
    });
  },

  initCommonSliders () {
    if (this.$els.sliderThree !== null) {
      this.initSliderThree();
    }
    if (this.$els.sliderFour !== null) {
      this.initSliderFour();
    }
  },

  initSliderThree () {
    this.sliderThree = new Swiper(this.sliderThreeId, {
      watchOverflow: true,
      slidesPerView: 3,
      spaceBetween: 30,
      autoHeight: true,
      breakpoints: {
        960: {
          slidesPerView: 2,
        },
        640: {
          slidesPerView: 1,
        },
      },
      navigation: {
        nextEl: `.swiper-button-next, ${this.sliderThreeId}`,
        prevEl: `.swiper-button-prev, ${this.sliderThreeId}`,
      },
    });
  },
  initSliderFour () {
    this.sliderFour = new Swiper(this.sliderFourId, {
      watchOverflow: true,
      slidesPerView: 4,
      spaceBetween: 30,
      breakpoints: {
        960: {
          slidesPerView: 3,
        },
        640: {
          slidesPerView: 2,
        },
        480: {
          slidesPerView: 1,
        },
      },
      navigation: {
        nextEl: `.swiper-button-next, ${this.sliderFourId}`,
        prevEl: `.swiper-button-prev, ${this.sliderFourId}`,
      },
    });
  },


  finalize () {
    // JavaScript to be fired on all pages, after page specific J/S is fired
    $(window).on("load", function () {
      $('.loading').removeClass('loading transition');
    });
  },
};
