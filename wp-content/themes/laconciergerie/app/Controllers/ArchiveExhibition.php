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

            $postlist = get_posts($args);
            $posts[] = array($term->slug, $postlist);
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

        $postlist = get_posts($args);

        $letter_keyed_posts = array();

        if ($postlist) {
            foreach ($postlist as $post) {
                $name = explode(' ', $post->post_title)[1];
                $first_letter = strtoupper(substr($name, 0, 1));

                if (!array_key_exists($first_letter, $letter_keyed_posts)) {
                    $letter_keyed_posts[$first_letter] = array();
                }

                $letter_keyed_posts[$first_letter][] = $post;
            }
        }
        return $letter_keyed_posts;
    }

}
