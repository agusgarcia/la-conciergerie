<?php

namespace App\Controllers;

use Sober\Controller\Controller;

class FrontPage extends Controller
{

    protected $acf = true;

    public function currentSeason()
    {
        $upcoming_found = null;
        $current_found = null;
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
                    'terms' => App::currentSeason(),
                )
            )
        );

        $season_results = get_posts($args);

        $results = array_map(function ($post) use ($season_results) {
            global $current_found;
            global $upcoming_found;
            $lastElement = end($season_results);

            if ($post->post_type == 'exhibition') {
                // Link
                $post->link = get_permalink($post->ID);
                // ACF Fields
                $post->opening_date = get_field('opening_date', $post);
                $post->preview_hour = get_field('preview_hour', $post);
//                $post->start_date = get_sub_field('exhibition_date_opening', $post);
//                $post->closing_date = get_sub_field('exhibition_date_closing', $post);
                $post->artist_name = get_field('artist_name', $post);
                $post->exhibition_title = get_field('exhibition_title', $post);
                $post->thumbnail = get_field('thumbnail', $post);
                $post->color = get_field('color', $post);
                $post->start_date = get_field('exhibition_date', get_post($post))["exhibition_date_opening"];
                $post->closing_date = get_field('exhibition_date', get_post($post))["exhibition_date_closing"];
                $post->other_dates = get_field('oo', get_post($post));
                if ($current_found === null) {
                    // Get current date
                    $current_date = date_create(date('Ymd'));

                    // Get last day of the exhibition
                    $closing_date_field = get_field('exhibition_date', $post->ID)["exhibition_date_closing"];
                    $closing_date = date_create($closing_date_field);

                    // Compare both dates
                    $date_diff = date_diff($current_date, $closing_date)->format('%R%a');

                    // If there's more than 0 days until the last day of the exhibiton
                    // Set as the current exhibition and break
                    if ($date_diff >= 0) {
                        $post->current = true;
                        $current_found = $post;
                    }
                    if ($lastElement === $post) {
                        $post->current = true;
                        $current_found = $post;
                    }
                }

                return $post;
            } else {
                // Link
                $post->link = get_permalink($post->ID);
                $post->title = get_field('event_title', $post);
                // ACF Fields
                $post->opening_date = get_field('opening_date', $post);
                $post->hour = get_field('event_hour', $post);
                $post->place = get_field('event_place', $post);
                $post->thumbnail = get_field('thumbnail', $post);
                $post->color = get_field('color', $post);

                if ($upcoming_found === null) {
                    // Get current date
                    $current_date = date_create(date('Ymd'));

                    // Get last day of the exhibition
                    $event_date_field = get_field('opening_date', $post->ID);
                    $event_date = date_create($event_date_field);

                    // Compare both dates
                    $date_diff = date_diff($current_date, $event_date)->format('%R%a');

                    // If there's more than 0 days until the the next event
                    // But less than 10
                    // Set as the current event and break
                    if ($date_diff >= 0 && $date_diff < 15) {
                        $upcoming_found = $post;
                    }
                }

                return $post;
            }

        }, $season_results);

        global $current_season;
        $current_season = $results;

        return $results;
    }

    public function upcomingEvent()
    {
        global $upcoming_found;
        $upcoming_event = $upcoming_found;
        return $upcoming_event;
    }

    public function currentExhibition()
    {
        global $current_found;
        $post = $current_found;
        return $post;
    }
}
