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
                $post->artist_name = get_field('artist_name', $post);
                $post->exhibition_title = get_field('exhibition_title', $post);
                $post->thumbnail = get_field('thumbnail', $post);
                $post->color = get_field('color', $post);
                $post->name = explode(' ', $post->artist_name)[1];

                return $post;

            }, $postsList);

            $posts[] = array($term->name, $result);
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
            'orderby' => 'meta_value',
            'order' => 'DESC',
            'meta_key' => 'opening_date',
        );

        $postsList = get_posts($args);

        $result = array_map(function ($post) {
            $post->link = get_permalink($post->ID);
            $post->artist_name = get_field('artist_name', $post);
            $post->exhibition_title = get_field('exhibition_title', $post);
            $post->thumbnail = get_field('thumbnail', $post);
            $post->color = get_field('color', $post);

            return $post;

        }, $postsList);

        $letter_keyed_posts = array();


        if ($result) {
            foreach ($result as $post) {
                $postArray = (array)$post;
                $name = explode(' ', $postArray['artist_name'])[1];
                if ($name == null) {
                    $name = $postArray['artist_name'];
                }

                $secondName = explode(' et ', $postArray['artist_name'])[1];
                $secondName = explode(' ', $secondName)[1];

                $first_letter = strtoupper(substr($name, 0, 1));
                $post->name = $name;

                // If the array $letter_keyed_posts hasn't a key $first_letter
                // Create an array into the key
                if (!array_key_exists($first_letter, $letter_keyed_posts)) {
                    $letter_keyed_posts[$first_letter] = array();
                }

                $letter_keyed_posts[$first_letter][] = $post;

                if ($secondName !== null) {
                    $first_letter_second = strtoupper(substr($secondName, 0, 1));
                    if (!array_key_exists($first_letter_second, $letter_keyed_posts)) {
                        $letter_keyed_posts[$first_letter_second] = array();
                    }

                    $letter_keyed_posts[$first_letter_second][] = $post;
                }
            }
        }

        // Sort array by key
        ksort($letter_keyed_posts);

        return $letter_keyed_posts;
    }

}
