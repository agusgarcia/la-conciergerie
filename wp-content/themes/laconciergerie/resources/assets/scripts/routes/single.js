import Swiper from 'swiper';
import ScrollMagic from 'scrollmagic';
import TweenLite from 'gsap/TweenLite';
import 'scrollmagic/scrollmagic/uncompressed/plugins/animation.gsap';
import 'scrollmagic/scrollmagic/uncompressed/plugins/debug.addIndicators';

export default {
  init () {
    this.initEls();
    this.initEvents();
  },

  initEls () {
    this.$els = {
      body: document.querySelector('body'),
      slider: document.querySelector('.gallery__slider'),
      title: document.querySelector('.exhibition .title, .event .title'),
    };

    this.gallerySlider = null;
    this.gallerySliderId = '.gallery__slider';
    this.smController = new ScrollMagic.Controller({addIndicators: false});
  },

  initEvents () {
    this.initSliders();
    this.initAnimations();
  },

  initSliders () {
    if (this.$els.slider !== null) {
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
  initAnimations () {
    if (this.$els.title !== null) {
      const tween = TweenLite.fromTo(this.$els.title, 0.6, {
        opacity: 0,
        y: 100,
      }, {
        opacity: 1,
        y: 0,
        delay: 1,
      });
      console.log('play tween');
      tween.play();
    }

    // Create Animation for 0.5s
    /*  const tween = TweenLite.fromTo('figure', 0.5, {
          opacity: 0,
          x: 100,
      }, {
          opacity: 1,
          x: 0,
      });*/

    // const triggerElements = ['.content__first', '.content__second'];
    const triggerElementsToTop = $('.content__first figure, .content__second figure');
    const triggerElementsToLeft = $('.gallery figure:nth-child(even)');
    const triggerElementsToRight = $('.gallery figure:nth-child(odd)');

    Array.forEach(triggerElementsToLeft, (trigger) => {
      // Create the Scene and trigger when visible with ScrollMagic
      const tween = TweenLite.fromTo(trigger, 0.5, {
        opacity: 0,
        x: 100,
      }, {
        opacity: 1,
        x: 0,
      });

      const scene = new ScrollMagic.Scene({
        triggerElement: trigger,
        offset: -100,
        reverse: false,
        /* offset the trigger 100px below #scene's top */
      });
      scene
          .setTween(tween)
          .addTo(this.smController);
    });

    Array.forEach(triggerElementsToRight, (trigger) => {
      // Create the Scene and trigger when visible with ScrollMagic
      const tween = TweenLite.fromTo(trigger, 0.5, {
        opacity: 0,
        x: -100,
      }, {
        opacity: 1,
        x: 0,
      });

      const scene = new ScrollMagic.Scene({
        triggerElement: trigger,
        offset: -100,
        reverse: false,
        /* offset the trigger 100px below #scene's top */
      });
      scene
          .setTween(tween)
          .addTo(this.smController);
    });

    Array.forEach(triggerElementsToTop, (trigger) => {
      // Create the Scene and trigger when visible with ScrollMagic
      const tween = TweenLite.fromTo(trigger, 0.5, {
        opacity: 0,
        y: 100,
      }, {
        opacity: 1,
        y: 0,
      });

      const scene = new ScrollMagic.Scene({
        triggerElement: trigger,
        offset: -200,
        reverse: false,
        /* offset the trigger 200px below #scene's top */
      });
      scene
          .setTween(tween)
          .addTo(this.smController);
    });

  },
};
