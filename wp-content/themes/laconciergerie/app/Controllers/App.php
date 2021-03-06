<?php

namespace App\Controllers;

use Sober\Controller\Controller;

class App extends Controller
{
    protected $acf = true;

    public function siteName()
    {
        return get_bloginfo('name');
    }

    public static function pageColor()
    {
        if (is_archive()) {
            return "";
        }
        if (get_field('page_color')) {
            return get_field('page_color');
        } else {
            return get_field('color');
        }
    }

    public static function title()
    {
        if (is_home()) {
            if ($home = get_option('page_for_posts', true)) {
                return get_the_title($home);
            }
            return __('Latest Posts', 'sage');
        }
        if (is_archive()) {
            return post_type_archive_title('', false);
        }
        if (is_search()) {
            return sprintf(__('Search Results for %s', 'sage'), get_search_query());
        }
        if (is_404()) {
            return __('Not Found', 'sage');
        }
        return get_the_title();
    }

    public static function currentSeason()
    {

        $year = date('Y');
        $month = date('m');
        $next_year = (int)$year + 1;
        $last_year = (int)$year - 1;

        // If current month is August or later,
        // current season is thisYear-nextYear (2018-2019)
        // If current month is between January and August
        // current season is lastYear-thisYear (2018-2019)
        // until next August (2019-2020)

        if ($month >= 8) {
            $current_season = $year . '-' . $next_year;
        } else {
            $current_season = $last_year . '-' . $year;
        }
        return $current_season;
    }

    public static function formattedDate($date)
    {
        setlocale(LC_ALL, "fr_FR");
        $date_s = strtotime($date);
        return utf8_encode(strftime('%e %B %Y', $date_s));
    }

    public static function formattedDateNoYear($date)
    {
        setlocale(LC_ALL, "fr_FR");
        $date_s = strtotime($date);
        return utf8_encode(strftime('%e %B', $date_s));
    }

    public static function formattedDateWithDay($date)
    {
        setlocale(LC_ALL, "fr_FR");
        $date_s = strtotime($date);
        return utf8_encode(strftime('%A %e %B %Y', $date_s));
    }

    public static function formattedDayAndMonth($date)
    {
        setlocale(LC_ALL, "fr_FR");
        $date_s = strtotime($date);
        $day = utf8_encode(strftime('%d', $date_s));
        $month = utf8_encode(strftime('%m', $date_s));
        return array($day, $month);
    }

    // Given two dates, it returns a string "From XX/XX to XX/XX",
    // with or without the year for the first date
    // if it's the same than the year from the second date
    public static function fromToDate($dateFrom, $dateTo)
    {
        setlocale(LC_ALL, "fr_FR");
        $date_from = strtotime($dateFrom);
        $date_to = strtotime($dateTo);
        $date_from_year = date("Y", $date_from);
        $date_to_year = date("Y", $date_to);

        if ($date_from_year === $date_to_year) {
            return "Du " . App::formattedDateNoYear($dateFrom) . " au " . App::formattedDate($dateTo);
        } else {
            return "Du " . App::formattedDate($dateFrom) . " au " . App::formattedDate($dateTo);
        }
    }
}
