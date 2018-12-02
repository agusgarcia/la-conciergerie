import Barba from 'barba.js/dist/barba';

export default function (routes) {
  Barba.Pjax.Dom.wrapperId = 'barba-wrapper';
  Barba.Pjax.Dom.containerClass = 'barba-container';

  // Blacklist all WordPress Links (e.g. for adminbar)
  function addBlacklistClass () {
    $('a').each(function () {
      if (this.href.indexOf('/wp-admin/') !== -1 ||
          this.href.indexOf('/wp-login.php') !== -1) {
        $(this).addClass('no-barba').addClass('wp-link');
      }
    });
  }

  // Set blacklist links

  addBlacklistClass();

  const HideShowTransition = Barba.BaseTransition.extend({
    start: function () {
      /**
       * This function is automatically called as soon the Transition starts
       * this.newContainerLoading is a Promise for the loading of the new container
       */

      // As soon the loading is finished and the old page is faded out, let's fade the new page
      Promise
          .all([this.newContainerLoading, this.fadeOut()])
          .then(this.fadeIn.bind(this));
    },

    fadeOut: function () {
      $('#transition-wrapper').addClass('transition');
      return $(this.oldContainer).promise();
    },

    fadeIn: function () {
      let timeout = window.setTimeout(() => {
        window.pageYOffset = 0;
        document.documentElement.scrollTop = 0;
        document.body.scrollTop = 0;

        this.done();
        // $('#body', this.newContainer).html($('#body', this.newContainer).html().replace(/http:\/\/conciergerie.localhost/g, 'http://localhost:3000'));
        document.querySelector('#transition-wrapper').style.setProperty('--page-color', $('#body', this.newContainer).data('color'));
        $('#transition-wrapper').removeClass('transition');
        clearTimeout(timeout);

      }, 1500);

    },
  });


  Barba.Pjax.getTransition = () => {
    return HideShowTransition;
  };

  // Fire Barba.js
  Barba.Pjax.start();
  Barba.Prefetch.init();


  if (!$('body').hasClass('app')) {
    $('body').attr('class', $('#body').attr('class'));
    routes.loadEvents();
  }

  Barba.Dispatcher.on('transitionCompleted', function () {
    // Set new classes from #af-classes to body
    $('body').attr('class', $('#body').attr('class'));
    // Fire routes after new content loaded
    routes.loadEvents();
  });


}