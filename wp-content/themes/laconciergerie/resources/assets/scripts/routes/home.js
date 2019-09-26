import Swiper from 'swiper';

export default {
  init () {
    // JavaScript to be fired on all pages
    this.initEls();
    this.initEvents();
  },

  initEls () {
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

  initEvents () {
    this.initSliders();
    this.$els.buttonScrollSeason.on('click', this.scrollToSeason.bind(this))
  },

  initSliders () {
    this.currentlySlider = new Swiper(this.currentlySliderId, {
      watchOverflow: true,
      spaceBetween: 0,
      autoplay: {
        delay: 5000,
      },
      speed: 500,
      loop: true,
      pagination: {
        el: `.swiper-pagination, ${this.currentlySliderId}`,
        type: 'bullets',
        clickable: true,
      },
      navigation: {
        nextEl: `.swiper-button-next, ${this.currentlySliderId}`,
        prevEl: `.swiper-button-prev, ${this.currentlySliderId}`,
      },
    });

    this.seasonSlider = new Swiper(this.seasonSliderId, {
      watchOverflow: true,
      slidesPerView: 3,
      spaceBetween: 10,
      scrollbar: {
        el: `.swiper-scrollbar, ${this.seasonSliderId}`,
        draggable: true,
      },
      navigation: {
        nextEl: `.swiper-button-next, ${this.seasonSliderId}`,
        prevEl: `.swiper-button-prev, ${this.seasonSliderId}`,
      },
      breakpoints: {
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

  scrollToSeason () {
    $('html, body').animate({scrollTop: $('#currentSeason').offset().top - 70}, 500);
  },

  finalize () {
    this.seasonSlider.update();
    // JavaScript to be fired on the home page, after the init JS
  },
};
