<?php

namespace App\Controllers;

use Sober\Controller\Controller;

class SingleEvent extends Controller
{
// Pass on all fields from Advanced Custom Fields to the view
    protected $acf = true;
    public function adjacentPosts()
    {
        $currentPost = get_post()->ID;
        $currentTerm = get_the_terms($currentPost, 'season')[0];

        $args = array(
            'numberposts' => -1,
            'post_type' => array('exhibition', 'event'),
            'orderby' => 'meta_value',
            'meta_key' => 'opening_date',
            'order' => 'ASC',
            'tax_query' => array(
                array(
                    'taxonomy' => 'season',
                    'field' => 'slug',
                    'terms' => $currentTerm->slug,
                )
            )
        );

        $postlist = get_posts($args);
        $posts = array();
        foreach ($postlist as $post) {
            $posts[] += $post->ID;
        }

        $current = array_search(get_the_ID(), $posts);
        $nextID = null;
        $prevID = null;

        if (array_key_exists($current + 1, $posts)) {
            $nextID = $posts[$current + 1];
        }
        if (array_key_exists($current - 1, $posts)) {
            $prevID = $posts[$current - 1];
        }

        return $adjacentPosts = [$prevID, $nextID];
    }

}
