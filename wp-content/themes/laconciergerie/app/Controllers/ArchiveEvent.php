<?php

namespace App\Controllers;

use Sober\Controller\Controller;

class ArchiveEvent extends Controller
{
    protected $acf = true;

    public function postsBySeason()
    {
        // Get all values in 'season' taxonomy (= $terms)
        $terms = get_terms(array(
            'taxonomy' => 'season',
            'order' => 'DESC',
            'order_by' => 'name',
        ));

        $posts = array();
        // For each term, get all its posts
        foreach ($terms as $term) {
            wp_reset_query();

            $args = array(
                'numberposts' => -1,
                'post_type' => 'event',
                'orderby' => 'meta_value',
                'order' => 'ASC',
                'meta_key' => 'opening_date',
                'tax_query' => array(
                    array(
                        'taxonomy' => 'season',
                        'field' => 'slug',
                        'terms' => $term->slug,
                    )
                ),
            );

            $postsList = get_posts($args);

            $result = array_map(function ($post) {
                $post->link = get_permalink($post->ID);
                // ACF Fields
                $post->title = get_field('event_title', $post);
                $post->opening_date = get_field('opening_date', $post);
                $post->hour = get_field('event_hour', $post);
                $post->place = get_field('event_place', $post);
                $post->thumbnail = get_field('thumbnail', $post);
                $post->color = get_field('color', $post);

                return $post;

            }, $postsList);

            $posts[] = array($term->name, $result);
        }

        return $posts;
    }
}
