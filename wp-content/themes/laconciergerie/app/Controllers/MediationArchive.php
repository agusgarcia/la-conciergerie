<?php

namespace App\Controllers;

use Sober\Controller\Controller;

class MediationArchive extends Controller
{

    public static function lastPosts($number = 10)
    {
        $paged = (get_query_var('paged')) ? get_query_var('paged') : 1;
        $args = array(
            'posts_per_page' => $number,
//            'numberposts' => $number,
            // If different posts_per_page needed, change it too in Wordpress Settings > Reading
//            'posts_per_page' => 10,
            'post_type' => 'mediation',
            'paged' => $paged,
        );

        $posts = get_posts($args);

        $posts = array_map(function ($post) {
            return [

                // Title, Content and Image
                'title' => apply_filters('the_title', $post->post_title),
                'content' => apply_filters('the_content', $post->post_content),
                'thumbnail' => get_the_post_thumbnail($post->ID, 'medium_large'),
                'date' => get_the_date('', $post->ID),
                'link' => get_permalink($post->ID),
                // ACF Fields
                'quotes' => get_field('quotes', $post),
                'images' => get_field('images', $post),
            ];
        }, $posts);

        return $posts;

    }
}
