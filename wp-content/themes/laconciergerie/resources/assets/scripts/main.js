// import external dependencies
import 'jquery';

// Import everything from autoload
import "./autoload/**/*"

// Import Barba.js
import barbaInit from './barba/init';

// import local dependencies
import Router from './util/Router';
import common from './routes/common';
import home from './routes/home';
import single from './routes/single';
import archive from './routes/archive';

/** Populate Router instance with DOM routes */
const routes = new Router({
  // All pages
  common,
  // Home page
  home,
  single,
  archive,
});

// Load Events
jQuery(document).ready(() => {
      //routes.loadEvents() is now called after Barba transition;
      barbaInit(routes);
    }
);
