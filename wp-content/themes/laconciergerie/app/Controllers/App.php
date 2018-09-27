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
        return strftime('%e %B %Y', $date_s);
    }

    public static function formattedDateWithDay($date)
    {
        setlocale(LC_ALL, "fr_FR");
        $date_s = strtotime($date);
        return strftime('%A %e %B %Y', $date_s);
    }
}
