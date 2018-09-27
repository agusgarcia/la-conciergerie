<?php

namespace App\Controllers;

use Sober\Controller\Controller;

class NewsArchive extends Controller
{

    public static function lastPosts($number = 2)
    {
        $paged = ( get_query_var( 'paged' ) ) ? get_query_var( 'paged' ) : 1;
        $args = array(
            'numberposts' => $number,
            'paged'          => $paged
        );

        $posts = get_posts($args);

        return array_map(function ($post) {
            return [
                // Title, Content and Image
                'title' => apply_filters('the_title', $post->post_title),
                'content' => apply_filters('the_content', $post->post_content),
                'thumbnail' => get_the_post_thumbnail($post->ID, 'thumbnail'),
                'date' => get_the_date('', $post->ID),
                'link' => get_permalink($post->ID),
                // ACF Fields
//                'quotes' => get_field('quotes', $post),
//                'images' => get_field('images', $post),
            ];
        }, $posts);

    }
}
