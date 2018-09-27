<?php

namespace App\Controllers;

use Sober\Controller\Controller;

class ArchiveExhibition extends Controller
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
                'post_type' => 'exhibition',
                'tax_query' => array(
                    array(
                        'taxonomy' => 'season',
                        'field' => 'slug',
                        'terms' => $term->slug,
                    )
                )
            );

            $postsList = get_posts($args);

            $postsList = array_map(function ($post) {
                return [

                    // Link
                    'link' => get_permalink($post->ID),

                    // ACF Fields
                    'opening_date' => get_field('opening_date', $post),
                    'artist_name' => get_field('artist_name', $post),
                    'exhibition_title' => get_field('exhibition_title', $post),
                    'thumbnail' => get_field('thumbnail', $post),
                ];
            }, $postsList);

            $posts[] = array($term->slug, $postsList);
        }

        return $posts;
    }

    public static function ExhibitionDateClosing()
    {
        return get_field('artist_name');
    }

    public function postsByArtist()
    {

        $args = array(
            'numberposts' => -1,
            'post_type' => 'exhibition',
            'order' => 'ASC',
            'order_by' => 'post_title'
        );

        $postsList = get_posts($args);

        $postsList = array_map(function ($post) {
            return [
                // Link
                'link' => get_permalink($post->ID),

                // ACF Fields
                'opening_date' => get_field('opening_date', $post),
                'artist_name' => get_field('artist_name', $post),
                'exhibition_title' => get_field('exhibition_title', $post),
                'thumbnail' => get_field('thumbnail', $post),
            ];
        }, $postsList);

        $letter_keyed_posts = array();

        if ($postsList) {
            foreach ($postsList as $post) {
                $name = explode(' ', $post['artist_name'])[1];
                $first_letter = strtoupper(substr($name, 0, 1));

                // If the array $letter_keyed_posts hasn't a key $first_letter
                // Create an array into the key
                if (!array_key_exists($first_letter, $letter_keyed_posts)) {
                    $letter_keyed_posts[$first_letter] = array();
                }

                $letter_keyed_posts[$first_letter][] = $post;
            }
        }
        return $letter_keyed_posts;
    }

}
