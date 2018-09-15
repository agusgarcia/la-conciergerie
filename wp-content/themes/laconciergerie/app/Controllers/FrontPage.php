<?php

namespace App\Controllers;

use Sober\Controller\Controller;

class FrontPage extends Controller
{
    protected $acf = true;

    public function upcomingEvent()
    {
        $current_season = $this->currentSeason();
        $criteria = array('post_type' => 'event');
        $season_events = wp_list_filter($current_season, $criteria);
        $upcoming_event = null;
        foreach ($season_events as $event) {
            // Get current date
            $current_date = date_create(date('Ymd'));

            // Get the event's date
            $event_date = date_create($event->opening_date);

            // Compare both dates
            $date_diff = date_diff($current_date, $event_date)->format('%R%a');

            // If there's more than 0 days until the event
            // And less than 10 days
            // Set as the upcoming event and break
            if ($date_diff >= 0 && $date_diff < 10) {
                $upcoming_event = $event;
                break;
            }
        }

        return $upcoming_event;
    }

    public function currentExhibition()
    {
        $current_season = $this->currentSeason();
        $criteria = array('post_type' => 'exhibition');
        $season_exhibitions = wp_list_filter($current_season, $criteria);
        $current_exhibition = null;
        foreach ($season_exhibitions as $item) {
            // Get current date
            $current_date = date_create(date('Ymd'));

            // Get last day of the exhibition
            $closing_date_field = get_field('exhibition_date', $item->ID)["exhibition_date_closing"];
            $closing_date = date_create($closing_date_field);

            // Compare both dates
            $date_diff = date_diff($current_date, $closing_date)->format('%R%a');

            // If there's more than 0 days until the last day of the exhibiton
            // Set as the current exhibition and break
            if ($date_diff >= 0) {
                $current_exhibition = $item;
                break;
            }
        }
        return $current_exhibition;
    }

    public function currentSeason()
    {
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
        return $season_results;
    }
}
