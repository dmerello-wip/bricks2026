<?php

use A17\Twill\Facades\TwillRoutes;

// Register Twill routes here eg.
// TwillRoutes::module('posts');

TwillRoutes::singleton('homepage');
TwillRoutes::singleton('seoDefault');
TwillRoutes::module('pages');
TwillRoutes::module('menuitems');
TwillRoutes::module('menus');
TwillRoutes::module('articles');
TwillRoutes::module('categories');
