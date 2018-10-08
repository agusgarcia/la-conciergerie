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
        }
    },

    initEvents() {
        this.initColorPage();
        this.initMenu();
    },

    initColorPage() {
        this.$els.body.style.setProperty('--page-color', this.$els.color);
    },

    initMenu() {
        $('.hamburger').click(function () {
            $(this).toggleClass('is-active');
        });
    },

    finalize() {
        // JavaScript to be fired on all pages, after page specific JS is fired
    },
};
