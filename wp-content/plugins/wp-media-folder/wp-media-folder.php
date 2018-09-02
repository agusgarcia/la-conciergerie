<?php
/*
  Plugin Name: WP Media folder
  Plugin URI: http://www.joomunited.com
  Description: WP media Folder is a WordPress plugin that enhance the WordPress media manager by adding a folder manager inside.
  Author: Joomunited
  Version: 4.4.3
  Author URI: http://www.joomunited.com
  Text Domain: wpmf
  Domain Path: /languages
  Licence : GNU General Public License version 2 or later; http://www.gnu.org/licenses/gpl-2.0.html
  Copyright : Copyright (C) 2014 JoomUnited (http://www.joomunited.com). All rights reserved.
 */
// Prohibit direct script loading
defined('ABSPATH') || die('No direct script access allowed!');

//Check plugin requirements
if (version_compare(PHP_VERSION, '5.3', '<')) {
    if (!function_exists('wpmf_disable_plugin')) {
        function wpmf_disable_plugin()
        {
            if (current_user_can('activate_plugins') && is_plugin_active(plugin_basename(__FILE__))) {
                deactivate_plugins(__FILE__);
                unset($_GET['activate']);
            }
        }
    }

    if (!function_exists('wpmf_show_error')) {
        function wpmf_show_error()
        {
            echo '<div class="error"><p>';
            echo '<strong>WP Media Folder</strong>';
            echo ' need at least PHP 5.3 version, please update php before installing the plugin.</p></div>';
        }
    }

    //Add actions
    add_action('admin_init', 'wpmf_disable_plugin');
    add_action('admin_notices', 'wpmf_show_error');

    //Do not load anything more
    return;
}

//Include the jutranslation helpers
include_once('jutranslation' . DIRECTORY_SEPARATOR . 'jutranslation.php');
call_user_func(
    '\Joomunited\WPMediaFolder\Jutranslation\Jutranslation::init',
    __FILE__,
    'wpmf',
    'WP Media Folder',
    'wpmf',
    'languages' . DIRECTORY_SEPARATOR . 'wpmf-en_US.mo'
);

if (!defined('WP_MEDIA_FOLDER_PLUGIN_DIR')) {
    define('WP_MEDIA_FOLDER_PLUGIN_DIR', plugin_dir_path(__FILE__));
}

if (!defined('WPMF_FILE')) {
    define('WPMF_FILE', __FILE__);
}

if (!defined('WPMF_TAXO')) {
    define('WPMF_TAXO', 'wpmf-category');
}

if (!defined('WPMF_ABSPATH')) {
    define('WPMF_ABSPATH', ABSPATH);
}

define('WPMF_GALLERY_PREFIX', 'wpmf_gallery_');
define('_WPMF_GALLERY_PREFIX', '_wpmf_gallery_');
define('WPMF_PLUGIN_URL', plugin_dir_url(__FILE__));
define('WPMF_DOMAIN', 'wpmf');
define('WPMF_URL', plugin_dir_url(__FILE__));
define('WPMF_VERSION', '4.4.3');

include_once(ABSPATH . 'wp-admin/includes/plugin.php');
register_activation_hook(__FILE__, 'wpmfInstall');

function wpmfInstall()
{
    global $wpdb;
    $limit = 100;
    $values = array();
    $place_holders = array();
    $total = $wpdb->get_var("SELECT COUNT(posts.ID) as total FROM " . $wpdb->prefix . "posts as posts
               WHERE   posts.post_type = 'attachment'");

    if ($total <= 10000) {
        $j = ceil((int)$total / $limit);
        for ($i = 1; $i <= $j; $i++) {
            $offset = ($i - 1) * $limit;
            $attachments = $wpdb->get_results("SELECT ID FROM " . $wpdb->prefix . "posts as posts
               WHERE   posts.post_type     = 'attachment' LIMIT $limit OFFSET $offset");
            foreach ($attachments as $attachment) {
                $wpmf_size_filetype = wpmfGetSizeFiletype($attachment->ID);
                $size = $wpmf_size_filetype['size'];
                $ext = $wpmf_size_filetype['ext'];
                if (!get_post_meta($attachment->ID, 'wpmf_size')) {
                    array_push($values, $attachment->ID, 'wpmf_size', $size);
                    $place_holders[] = "('%d', '%s', '%s')";
                }

                if (!get_post_meta($attachment->ID, 'wpmf_filetype')) {
                    array_push($values, $attachment->ID, 'wpmf_filetype', $ext);
                    $place_holders[] = "('%d', '%s', '%s')";
                }
            }

            if (count($place_holders) > 0) {
                $query = "INSERT INTO " . $wpdb->prefix . "postmeta (post_id, meta_key, meta_value) VALUES ";
                $query .= implode(', ', $place_holders);
                $wpdb->query($wpdb->prepare("$query ", $values));
                $place_holders = array();
                $values = array();
            }
        }
    }
}

/**
 * Get size and file type for attachment
 * @param $pid
 * @return array
 */
function wpmfGetSizeFiletype($pid)
{
    $wpmf_size_filetype = array();
    $meta = get_post_meta($pid, '_wp_attached_file');
    $upload_dir = wp_upload_dir();
    $url_attachment = $upload_dir['basedir'] . '/' . $meta[0];
    if (file_exists($url_attachment)) {
        $size = filesize($url_attachment);
        $filetype = wp_check_filetype($url_attachment);
        $ext = $filetype['ext'];
    } else {
        $size = 0;
        $ext = '';
    }
    $wpmf_size_filetype['size'] = $size;
    $wpmf_size_filetype['ext'] = $ext;

    return $wpmf_size_filetype;
}

/**
 * Set a option
 * @param string $option_name option name
 * @param $value
 */
function wpmfSetOption($option_name, $value)
{
    $settings = get_option('wpmf_settings');
    if (empty($settings)) {
        $settings = array();
        $settings[$option_name] = $value;
    } else {
        $settings[$option_name] = $value;
    }

    update_option('wpmf_settings', $settings);
}

/**
 * Get a option
 * @param string $option_name option name
 * @return bool
 */
function wpmfGetOption($option_name)
{
    $default_settings = array(
        'folder_design' => 'material_design',
        'folder_color' => array()
    );
    $settings = get_option('wpmf_settings');
    if (isset($settings) && isset($settings[$option_name])) {
        return $settings[$option_name];
    }

    return $default_settings[$option_name];
}

$frontend = get_option('wpmf_option_mediafolder');
if (!empty($frontend) || is_admin()) {
    require_once(WP_MEDIA_FOLDER_PLUGIN_DIR . '/class/class-helper.php');
    require_once(WP_MEDIA_FOLDER_PLUGIN_DIR . '/class/class-media-folder.php');
    $GLOBALS['wp_media_folder'] = new WpMediaFolder;
    $useorder = get_option('wpmf_useorder');
    // todo : should this really be always loaded on each wp request?
    // todo : should we not loaded
    if (isset($useorder) && $useorder == 1) {
        require_once(WP_MEDIA_FOLDER_PLUGIN_DIR . 'class/class-orderby-media.php');
        new WpmfOrderbyMedia;
        require_once(WP_MEDIA_FOLDER_PLUGIN_DIR . 'class/class-filter-size.php');
        new WpmfFilterSize;
    }

    $option_duplicate = get_option('wpmf_option_duplicate');
    if (isset($option_duplicate) && $option_duplicate == 1) {
        require_once(WP_MEDIA_FOLDER_PLUGIN_DIR . 'class/class-duplicate-file.php');
        new WpmfDuplicateFile;
    }

    $wpmf_media_rename = get_option('wpmf_media_rename');
    if (isset($wpmf_media_rename) && $wpmf_media_rename == 1) {
        require_once(WP_MEDIA_FOLDER_PLUGIN_DIR . 'class/class-media-rename.php');
        new WpmfMediaRename;
    }

    require_once(WP_MEDIA_FOLDER_PLUGIN_DIR . '/class/class-background-folder.php');
    new WpmfBackgroundFolder;

    $option_override = get_option('wpmf_option_override');
    if (isset($option_override) && $option_override == 1) {
        require_once(WP_MEDIA_FOLDER_PLUGIN_DIR . 'class/class-replace-file.php');
        new WpmfReplaceFile;
    }

    require_once(WP_MEDIA_FOLDER_PLUGIN_DIR . '/class/class-image-watermark.php');
    new WpmfWatermark;
}

$usegellery = get_option('wpmf_usegellery');
if (isset($usegellery) && $usegellery == 1) {
    require_once(WP_MEDIA_FOLDER_PLUGIN_DIR . '/class/class-display-gallery.php');
    new WpmfDisplayGallery;
}

require_once(WP_MEDIA_FOLDER_PLUGIN_DIR . 'class/class-wp-folder-option.php');
new WpmfMediaFolderOption;
$wpmf_option_singlefile = get_option('wpmf_option_singlefile');
if (isset($wpmf_option_singlefile) && $wpmf_option_singlefile == 1) {
    require_once(WP_MEDIA_FOLDER_PLUGIN_DIR . '/class/class-single-file.php');
    new WpmfSingleFile();
}

$wpmf_option_lightboximage = get_option('wpmf_option_lightboximage');
if (isset($wpmf_option_lightboximage) && $wpmf_option_lightboximage == 1) {
    require_once(WP_MEDIA_FOLDER_PLUGIN_DIR . 'class/class-single-lightbox.php');
    new WpmfSingleLightbox;
}

require_once(WP_MEDIA_FOLDER_PLUGIN_DIR . '/class/class-pdf-embed.php');
new WpmfPdfEmbed();

add_action('wp_enqueue_media', 'wpmf_add_style');
add_action('admin_enqueue_scripts', 'wpmf_add_style');
function wpmf_add_style()
{
    wp_enqueue_style(
        'wpmf-material-design-iconic-font.min',
        plugins_url('/assets/css/material-design-iconic-font.min.css', __FILE__),
        array(),
        WPMF_VERSION
    );

    wp_enqueue_style(
        'wpmf-style-linkbtn',
        plugins_url('/assets/css/style_linkbtn.css', __FILE__),
        array(),
        WPMF_VERSION
    );

    wp_enqueue_script(
        'wpmf-link-dialog',
        plugins_url('/assets/js/open_link_dialog.js', __FILE__),
        array('jquery'),
        WPMF_VERSION
    );
}

/* Register WPMF_TAXO taxonomy */
add_action('init', 'wpmfRegisterTaxonomyForImages', 0);
function wpmfRegisterTaxonomyForImages()
{
    register_taxonomy(
        WPMF_TAXO,
        'attachment',
        array(
            'hierarchical' => true,
            'show_in_nav_menus' => false,
            'show_ui' => false,
            'public' => false,
            'labels' => array(
                'name' => __('WPMF Categories', 'wpmf'),
                'singular_name' => __('WPMF Category', 'wpmf'),
                'menu_name' => __('WPMF Categories', 'wpmf'),
                'all_items' => __('All WPMF Categories', 'wpmf'),
                'edit_item' => __('Edit WPMF Category', 'wpmf'),
                'view_item' => __('View WPMF Category', 'wpmf'),
                'update_item' => __('Update WPMF Category', 'wpmf'),
                'add_new_item' => __('Add New WPMF Category', 'wpmf'),
                'new_item_name' => __('New WPMF Category Name', 'wpmf'),
                'parent_item' => __('Parent WPMF Category', 'wpmf'),
                'parent_item_colon' => __('Parent WPMF Category:', 'wpmf'),
                'search_items' => __('Search WPMF Categories', 'wpmf'),
            )
        )
    );

    $root_id = get_option('wpmf_folder_root_id', false);
    if (!$root_id) {
        $tag = get_term_by('name', __('WP Media Folder Root', 'wpmf'), WPMF_TAXO);
        if (empty($tag)) {
            $inserted = wp_insert_term(__('WP Media Folder Root', 'wpmf'), WPMF_TAXO, array('parent' => 0));
            if (!get_option('wpmf_folder_root_id', false)) {
                add_option('wpmf_folder_root_id', $inserted['term_id'], '', 'yes');
            }
        } else {
            if (!get_option('wpmf_folder_root_id', false)) {
                add_option('wpmf_folder_root_id', $tag->term_id, '', 'yes');
            }
        }
    } else {
        $root = get_term_by('id', (int) $root_id, WPMF_TAXO);
        if (!$root) {
            $inserted = wp_insert_term(__('WP Media Folder Root', 'wpmf'), WPMF_TAXO, array('parent' => 0));
            update_option('wpmf_folder_root_id', (int) $inserted['term_id']);
        }
    }
}

//config section
if (!defined('JU_BASE')) {
    define('JU_BASE', 'https://www.joomunited.com/');
}

$remote_updateinfo = JU_BASE . 'juupdater_files/wp-media-folder.json';
//end config

require 'juupdater/juupdater.php';
$UpdateChecker = Jufactory::buildUpdateChecker(
    $remote_updateinfo,
    __FILE__
);
