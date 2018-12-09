<?php

namespace App;

/**
 * Add <body> classes
 */
add_filter('body_class', function (array $classes) {
    /** Add page slug if it doesn't exist */
    if (is_single() || is_page() && !is_front_page()) {
        if (!in_array(basename(get_permalink()), $classes)) {
            $classes[] = basename(get_permalink());
        }
    }

    /** Add a global class to everything.
     *  We want it to come first, so stuff its filter does can be overridden.
     */
    array_unshift($classes, 'app');

    /** Add class if sidebar is active */
    if (display_sidebar()) {
        $classes[] = 'sidebar-primary';
    }

    /** Clean up class names for custom templates */
    $classes = array_map(function ($class) {
        return preg_replace(['/-blade(-php)?$/', '/^page-template-views/'], '', $class);
    }, $classes);

    return array_filter($classes);
});

/**
 * Add "… Continued" to the excerpt
 */
add_filter('excerpt_more', function () {
    return ' &hellip; <a href="' . get_permalink() . '">' . __('Continued', 'sage') . '</a>';
});

/**
 * Template Hierarchy should search for .blade.php files
 */
collect([
    'index', '404', 'archive', 'author', 'category', 'tag', 'taxonomy', 'date', 'home',
    'frontpage', 'page', 'paged', 'search', 'single', 'singular', 'attachment'
])->map(function ($type) {
    add_filter("{$type}_template_hierarchy", __NAMESPACE__ . '\\filter_templates');
});

/**
 * Render page using Blade
 */
add_filter('template_include', function ($template) {
    $data = collect(get_body_class())->reduce(function ($data, $class) use ($template) {
        return apply_filters("sage/template/{$class}/data", $data, $template);
    }, []);
    if ($template) {
        echo template($template, $data);
        return get_stylesheet_directory() . '/index.php';
    }
    return $template;
}, PHP_INT_MAX);

/**
 * Render comments.blade.php
 */
add_filter('comments_template', function ($comments_template) {
    $comments_template = str_replace(
        [get_stylesheet_directory(), get_template_directory()],
        '',
        $comments_template
    );

    $theme_template = locate_template(["views/{$comments_template}", $comments_template]);

    if ($theme_template) {
        echo template($theme_template);
        return get_stylesheet_directory() . '/index.php';
    }

    return $comments_template;
}, 100);

/**
 * Render WordPress searchform using Blade
 */
add_filter('get_search_form', function () {
    return template('partials.searchform');
});

/**
 * Collect data for searchform.
 */
add_filter('sage/template/app/data', function ($data) {
    return $data + [
            'sf_action' => esc_url(home_url('/')),
            'sf_screen_reader_text' => _x('Search for:', 'label', 'sage'),
            'sf_placeholder' => esc_attr_x('Search &hellip;', 'placeholder', 'sage'),
            'sf_current_query' => get_search_query(),
            'sf_submit_text' => esc_attr_x('Search', 'submit button', 'sage'),
        ];
});

// What next lines do :
// - Create "season" and "exhibition title" columns
// - Add terms under each column for each exhibition/event
// - Make "season" column sortable
// - Sort exhibitions by opening date

// Creates "season" and "exhibition title" columns in Exhibition page / Exhibition
add_filter('manage_exhibition_posts_columns', function ($columns) {
    $columns['exhibition_title'] = ('Titre de l\'exposition');
    $columns['season'] = ('Saison');
    return $columns;
});

// Creates "season" column in Event page / Event
add_filter('manage_edit-event_columns', function ($columns) {
    $columns['season'] = __('Saison');
    return $columns;
});

// Insert taxonomy next to each event under the season column / Event
add_action('manage_event_posts_custom_column', function ($column, $post_id) {
    switch ($column) {
        case 'season' :
            $terms = get_the_term_list($post_id, 'season', '', ',', '');
            if (is_string($terms))
                echo $terms;
            else
                _e('Unable to get season');
            break;
    }
}, 10, 2);

// Insert value next to each exhibition under the season and title columns / Exhibitions
add_action('manage_exhibition_posts_custom_column', function ($column, $post_id) {
    switch ($column) {
        case 'exhibition_title' :
            $terms = get_field('exhibition_title', $post_id);
            if (is_string($terms))
                echo $terms;
            else
                _e('Unable to get the title');
            break;
        case 'season' :
            $terms = get_the_term_list($post_id, 'season', '', ',', '');
            if (is_string($terms))
                echo $terms;
            else
                _e('Unable to get season');
            break;
    }
}, 10, 2);

// Add "season" as sortable column / Exhibitions
add_filter('manage_edit-exhibition_sortable_columns', function () {
    $columns['title'] = 'title';
    $columns['season'] = 'season';
    return $columns;
});

// Add "season" as sortable column / Events
add_filter('manage_edit-event_sortable_columns', function () {
    $columns['season'] = 'season';
    return $columns;
});

// Sort exhibtion listing by "opening date" by default
add_action('pre_get_posts', function ($query) {
    // Get current page
    global $pagenow;
    // If current page is "edit.php" and order is not set
    // Order by opening date
    if (is_admin() && 'edit.php' == $pagenow && !isset($_GET['orderby']) && in_array ( $query->get('post_type'), array('event','exhibition'))) {
        $query->set('meta_key', 'opening_date');
        $query->set('orderby', 'meta_value');
    }
    // If order is set and is order by season,
    // Order by season in the correct order (ASC or DESC)
    $orderby = $query->get('orderby');
    if ('season' == $orderby) {
        $query->set('meta_key', 'opening_date');
        $query->set('orderby', 'meta_value');
    }
}, 1);