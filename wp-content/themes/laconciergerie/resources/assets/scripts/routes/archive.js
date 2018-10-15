export default {
    init() {
        // JavaScript to be fired on all pages
        this.initEls();
        this.initEvents();
    },

    initEls() {
        this.$els = {
            body: document.querySelector('body'),
            sortButtons: document.querySelectorAll('.js-sort-button'),
            itemsContainer: document.querySelectorAll('.js-items-container'),
        };
    },

    initEvents() {
        this.$els.sortButtons.forEach(function (button) {
            button.addEventListener('click', this.updateSort.bind(this), false);
        }, this)
    },

    updateSort(e) {
        const currentTarget = e.currentTarget;
        const currentSort = currentTarget.dataset.sort;
        const activeButton = document.querySelector('.js-sort-button.active');
        activeButton.classList.remove('active');
        currentTarget.classList.add('active');
        const currentContainer = document.querySelector(`.js-items-container[data-sort=${currentSort}]`);
        document.querySelector('.js-items-container.active').classList.remove('active');
        currentContainer.classList.add('active');
    },

    finalize() {
        // JavaScript to be fired on the home page, after the init JS
    },
};
