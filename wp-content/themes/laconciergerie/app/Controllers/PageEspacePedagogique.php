<?php

namespace App\Controllers;

use Sober\Controller\Controller;

class PageEspacePedagogique extends Controller
{
// Pass on all fields from Advanced Custom Fields to the view
    protected $acf = true;

    public function mediationPosts()
    {
        $args = array(
            'numberposts' => 3,
            'post_type' => 'mediation',
        );

        return get_posts($args);
    }
}
