<?php
/* Prohibit direct script loading */
defined('ABSPATH') || die('No direct script access allowed!');

/**
 * Class WpmfMediaFolderOption
 * This class that holds most of the settings functionality for Media Folder.
 */
class WpmfMediaFolderOption
{

    public $breadcrumb_category = array();
    public $result_gennerate_thumb = '';
    public $type_import = array(
        'jpg', 'jpeg', 'jpe', 'gif',
        'png', 'bmp', 'tiff', 'tif',
        'ico', '7z', 'bz2', 'gz',
        'rar', 'tgz', 'zip',
        'csv', 'doc', 'docx',
        'ods', 'odt', 'pdf',
        'pps', 'ppt', 'pptx', 'ppsx',
        'rtf', 'txt', 'xls', 'xlsx',
        'psd', 'tif', 'tiff', 'mid',
        'mp3', 'mp4', 'ogg', 'wma',
        '3gp', 'avi', 'flv', 'm4v',
        'mkv', 'mov', 'mpeg', 'mpg',
        'swf', 'vob', 'wmv'
    );
    public $default_time_sync = 60;

    /**
     * Media_Folder_Option constructor.
     */
    public function __construct()
    {
        add_action('admin_menu', array($this, 'addSettingsMenu'));
        add_action('admin_enqueue_scripts', array($this, 'loadAdminScripts'));
        add_action('admin_enqueue_scripts', array($this, 'heartbeatEnqueue'));
        add_action('admin_head', array($this, 'adminHead'));
        add_filter('heartbeat_received', array($this, 'heartbeatReceived'), 10, 2);

        $wpmf_version = get_option('wpmf_version');
        if (version_compare(WPMF_VERSION, $wpmf_version, '>') || empty($wpmf_version)) {
            add_action('admin_init', array($this, 'addSettingsOption'));
        }

        $active_media = get_option('wpmf_active_media');
        if (isset($active_media) && $active_media == 1) {
            add_action('admin_init', array($this, 'createUserFolder'));
        }

        if (defined('NGG_PLUGIN_VERSION')) {
            if (!get_option('wpmf_import_nextgen_gallery', false)) {
                add_action('admin_notices', array($this, 'showNotice'), 3);
            }
        }

        add_action('wp_ajax_import_gallery', array($this, 'importGallery'));
        add_action('wp_ajax_import_categories', array($this, 'importCategories'));
        add_action('wp_ajax_wpmf_add_dimension', array($this, 'addDimension'));
        add_action('wp_ajax_wpmf_remove_dimension', array($this, 'removeDimension'));
        add_action('wp_ajax_wpmf_add_weight', array($this, 'addWeight'));
        add_action('wp_ajax_wpmf_remove_weight', array($this, 'removeWeight'));
        add_action('wp_ajax_wpmf_edit', array($this, 'edit'));
        add_action('wp_ajax_wpmf_get_folder', array($this, 'getFolder'));
        add_action('wp_ajax_wpmf_import_folder', array($this, 'importFolder'));
        add_action('wp_ajax_wpmfjao_checked', array($this, 'jaoChecked'));
        add_action('wp_ajax_wpmf_add_syncmedia', array($this, 'addSyncMedia'));
        add_action('wp_ajax_wpmf_remove_syncmedia', array($this, 'removeSyncMedia'));
        add_action('wp_ajax_wpmf_regeneratethumbnail', array($this, 'regenerateThumbnail'));
        add_action('wp_ajax_wpmf_syncmedia', array($this, 'syncMedia'));
        add_action('wp_ajax_wpmf_syncmedia_external', array($this, 'syncMediaExternal'));
        add_action('wp_ajax_wpmf_import_size_filetype', array($this, 'importSizeFiletype'));
    }

    /**
     * Import size and filetype to meta for attachment
     */
    public function importSizeFiletype()
    {
        if (!current_user_can('manage_options')) {
            wp_send_json(false);
        }
        global $wpdb;
        $limit = 50;
        $offset = (int)$_POST['wpmf_current_page'] * $limit;
        $attachments = $wpdb->get_results("SELECT ID FROM " . $wpdb->prefix . "posts as posts
               WHERE   posts.post_type     = 'attachment' LIMIT $limit OFFSET $offset");
        $i = 0;
        foreach ($attachments as $attachment) {
            $wpmf_size_filetype = wpmfGetSizeFiletype($attachment->ID);
            $size = $wpmf_size_filetype['size'];
            $ext = $wpmf_size_filetype['ext'];
            if (!get_post_meta($attachment->ID, 'wpmf_size')) {
                update_post_meta($attachment->ID, 'wpmf_size', $size);
            }

            if (!get_post_meta($attachment->ID, 'wpmf_filetype')) {
                update_post_meta($attachment->ID, 'wpmf_filetype', $ext);
            }
            $i++;
        }
        if ($i >= $limit) {
            wp_send_json(array('status' => false, 'page' => (int)$_POST['wpmf_current_page']));
        } else {
            update_option('_wpmf_import_size_notice_flag', 'yes');
            wp_send_json(array('status' => true));
        }
    }

    /**
     * Admin head
     */
    public function adminHead()
    {
        if (isset($_SESSION['wpmf_dir_checked'])) {
            unset($_SESSION['wpmf_dir_checked']);
        }
    }

    /**
     * Ajax checked folder tree ( tab FTP import )
     */
    public function jaoChecked()
    {
        if (!current_user_can('manage_options')) {
            wp_send_json(false);
        }
        if (isset($_POST['dir_checked'])) {
            $_SESSION['wpmf_dir_checked'] = $_POST['dir_checked'];
            wp_send_json($_SESSION['wpmf_dir_checked']);
        }
    }

    /**
     * Insert a attachment to database
     * @param string $upload_path path of file
     * @param string $upload_url url of file
     * @param string $file_title title of tile
     * @param string $file file name
     * @param string $content content of file
     * @param string $mime_type mime type of file
     * @param string $ext extension of file
     * @param int $term_id folder id
     * @param string $type check modified file
     * @param int $pid attachment id to update
     * @return bool
     */
    public function insertAttachmentMetadata(
        $upload_path,
        $upload_url,
        $file_title,
        $file,
        $form_file,
        $mime_type,
        $ext,
        $term_id
    ) {
        $file = wp_unique_filename($upload_path, $file);
        $upload = copy($form_file, $upload_path . '/' . $file);
        if ($upload) {
            $attachment = array(
                'guid' => $upload_url . '/' . $file,
                'post_mime_type' => $mime_type,
                'post_title' => str_replace('.' . $ext, '', $file_title),
                'post_status' => 'inherit'
            );

            $image_path = $upload_path . '/' . $file;
            // Insert attachment
            $attach_id = wp_insert_attachment($attachment, $image_path);
            $attach_data = wp_generate_attachment_metadata($attach_id, $image_path);
            wp_update_attachment_metadata($attach_id, $attach_data);
            // set attachment to term
            wp_set_object_terms((int)$attach_id, (int)$term_id, WPMF_TAXO, false);
            return true;
        }
        return false;
    }

    /**
     * Scan folder to insert term and attachment
     * @param string $dir
     * @param int $folder_name folder name
     * @param int $parent parent of folder
     * @param int $percent percent
     */
    public function addScandirFolder($dir, $folder_name, $parent, $percent)
    {
        global $wpdb;
        require_once('ForceUTF8/Encoding.php');
        $folder_name = WpmfEncoding::toUTF8($folder_name);
        $sql = $wpdb->prepare(
            "SELECT $wpdb->terms.term_id FROM $wpdb->terms,$wpdb->term_taxonomy
 WHERE taxonomy=%s AND name=%s AND parent=$parent AND $wpdb->terms.term_id=$wpdb->term_taxonomy.term_id",
            array(WPMF_TAXO, $folder_name)
        );
        $term_id = $wpdb->get_results($sql);
        $i = 0;

        if (empty($term_id)) {
            $inserted = wp_insert_term(
                $folder_name,
                WPMF_TAXO,
                array(
                    'parent' => $parent,
                    'slug' => sanitize_title($folder_name) . WPMF_TAXO
                )
            );

            if (is_array($inserted)) {
                $termID = $inserted['term_id'];
            } else {
                $termID = $inserted->error_data['term_exists'];
            }
        } else {
            $termID = $term_id[0]->term_id;
        }

        // List files and directories inside $dir path
        $files = scandir($dir);
        $files = array_diff($files, array('..', '.'));
        if (count($files) > 0) {
            // loop list files and directories
            foreach ($files as $file) {
                if ($i >= 3) {
                    wp_send_json(array('status' => 'error time', 'percent' => $percent)); // run again ajax
                } else {
                    if (is_dir($dir . '/' . $file)) { // is directory
                        $this->addScandirFolder($dir . '/' . $file, str_replace('  ', ' ', $file), $termID, $percent);
                    } else {
                        // is file
                        $upload_dir = wp_upload_dir();
                        $info_file = wp_check_filetype($dir . '/' . $file);
                        if (!empty($info_file) && !empty($info_file['ext'])
                            && in_array(strtolower($info_file['ext']), $this->type_import)
                        ) {
                            $form_file = $dir . '/' . $file;
                            $file_title = $file;
                            $file = sanitize_file_name($file);
                            // check file exist , if not exist then insert file
                            $pid = $this->checkExistPost('/' . $file, $termID);
                            if (empty($pid)) {
                                $check = $this->insertAttachmentMetadata(
                                    $upload_dir['path'],
                                    $upload_dir['url'],
                                    $file_title,
                                    $file,
                                    $form_file,
                                    $info_file['type'],
                                    $info_file['ext'],
                                    $termID
                                );
                                if ($check) {
                                    $i++;
                                }
                            }
                        }
                    }
                }
            }
        }
    }

    /**
     * Ajax add a row to lists sync media
     */
    public function addSyncMedia()
    {
        if (!current_user_can('manage_options')) {
            wp_send_json(false);
        }
        if (isset($_POST['folder_category']) && isset($_POST['folder_ftp'])) {
            $folder_ftp = str_replace('\\', '/', stripcslashes($_POST['folder_ftp']));
            $folder_category = $_POST['folder_category'];

            $lists = get_option('wpmf_list_sync_media');
            if (is_array($lists) && !empty($lists)) {
                $lists[$folder_category] = array('folder_ftp' => $folder_ftp);
            } else {
                $lists = array();
                $lists[$folder_category] = array('folder_ftp' => $folder_ftp);
            }

            update_option('wpmf_list_sync_media', $lists);
            wp_send_json(array('folder_category' => $folder_category, 'folder_ftp' => $folder_ftp));
        }
    }

    /**
     * Ajax remove a row to lists sync media
     */
    public function removeSyncMedia()
    {
        if (!current_user_can('manage_options')) {
            wp_send_json(false);
        }
        $lists = get_option('wpmf_list_sync_media');

        if (isset($_POST['key']) && $_POST['key'] != '') {
            foreach (explode(',', $_POST['key']) as $key) {
                if (isset($lists[$key])) {
                    unset($lists[$key]);
                }
            }
            update_option('wpmf_list_sync_media', $lists);
            wp_send_json(explode(',', $_POST['key']));
        }
        wp_send_json(false);
    }

    /**
     * This function do import from FTP to media library
     */
    public function importFolder()
    {
        if (current_user_can('install_plugins')) {
            if (isset($_POST['wpmf_list_import']) && $_POST['wpmf_list_import'] != '') {
                $lists = explode(',', $_POST['wpmf_list_import']);
                $i = 0;
                // get count files and directories in folder
                foreach ($lists as $list) {
                    $root = ABSPATH . $list;
                    foreach (new RecursiveIteratorIterator(new RecursiveDirectoryIterator($root)) as $filename) {
                        $info_file = wp_check_filetype((string)$filename);
                        if (is_dir((string)$filename)) {
                            $i++;
                        } else {
                            if (!empty($info_file['ext'])
                                && in_array(strtolower($info_file['ext']), $this->type_import)
                            ) {
                                $i++;
                            }
                        }
                    }
                }

                $percent = (100 * 3) / $i;

                foreach ($lists as $list) {
                    if ($list != '/') {
                        $root = ABSPATH . $list;
                        $info = pathinfo($list);
                        $filename = $info['basename'];
                        $parent = 0;
                        $this->addScandirFolder($root, $filename, $parent, $percent);
                    }
                }
            }
        }
    }

    /**
     * This function do validate path
     * @param $path : path of file
     * @return string
     */
    public function validatePath($path)
    {
        return rtrim(str_replace(DIRECTORY_SEPARATOR, '/', $path), '/');
    }

    /**
     * get term to display folder tree
     */
    public function getFolder()
    {
        if (!current_user_can('manage_options')) {
            wp_send_json(false);
        }
        $uploads_dir = wp_upload_dir();
        $uploads_dir_path = $uploads_dir['path'];
        $include_folders = isset($_SESSION['wpmf_dir_checked']) ? $_SESSION['wpmf_dir_checked'] : '';
        $selected_folders = explode(',', $include_folders);
        $path = $this->validatePath(WPMF_ABSPATH);
        $dir = $_REQUEST['dir'];
        $return = $dirs = array();
        require_once('ForceUTF8/Encoding.php');
        if (@file_exists($path . $dir)) {
            $files = scandir($path . $dir);
            $files = array_diff($files, array('..', '.'));
            natcasesort($files);
            if (count($files) > 0) {
                $baseDir = ltrim(rtrim(str_replace(DIRECTORY_SEPARATOR, '/', $dir), '/'), '/');
                if ($baseDir != '') {
                    $baseDir .= '/';
                }

                foreach ($files as $file) {
                    if (@file_exists($path . $dir . $file) && is_dir($path . $dir . $file)
                        && ($path . $dir . $file != $this->validatePath($uploads_dir_path))
                    ) {
                        $file = WpmfEncoding::toUTF8($file);
                        if (in_array($baseDir . $file, $selected_folders)) {
                            $dirs[] = array('type' => 'dir', 'dir' => $dir, 'file' => $file, 'checked' => true);
                        } else {
                            $hasSubFolderSelected = false;
                            foreach ($selected_folders as $selected_folder) {
                                if (strpos($selected_folder, $baseDir . $file) === 1) {
                                    $hasSubFolderSelected = true;
                                }
                            }

                            if ($hasSubFolderSelected) {
                                $dirs[] = array('type' => 'dir', 'dir' => $dir, 'file' => $file, 'pchecked' => true);
                            } else {
                                $dirs[] = array('type' => 'dir', 'dir' => $dir, 'file' => $file);
                            }
                        }
                    }
                }
                $return = $dirs;
            }
        }
        wp_send_json($return);
    }

    /**
     * create user folder
     */
    public function createUserFolder()
    {
        // insert term when user login and enable option 'Display only media by User/User'
        global $current_user;
        $user_roles = $current_user->roles;
        $role = array_shift($user_roles);
        if ($role != 'administrator' && current_user_can('upload_files')) {
            $wpmf_create_folder = get_option('wpmf_create_folder');
            if ($wpmf_create_folder == 'user') {
                $slug = sanitize_title($current_user->data->user_login) . '-wpmf';
                $check_term = get_term_by('slug', $slug, WPMF_TAXO);
                if (empty($check_term)) {
                    $wpmf_checkbox_tree = get_option('wpmf_checkbox_tree');
                    if (!empty($wpmf_checkbox_tree)) {
                        $current_parrent = get_term($wpmf_checkbox_tree, WPMF_TAXO);
                        if (!empty($current_parrent)) {
                            $parent = $wpmf_checkbox_tree;
                        } else {
                            $parent = 0;
                        }
                    } else {
                        $parent = 0;
                    }
                    $inserted = wp_insert_term(
                        $current_user->data->user_login,
                        WPMF_TAXO,
                        array('parent' => $parent, 'slug' => $slug)
                    );
                    if (!is_wp_error($inserted)) {
                        wp_update_term($inserted['term_id'], WPMF_TAXO, array('term_group' => $current_user->data->ID));
                    }
                }
            } elseif ($wpmf_create_folder == 'role') {
                $slug = sanitize_title($role) . '-wpmf-role';
                $check_term = get_term_by('slug', $slug, WPMF_TAXO);
                if (empty($check_term)) {
                    wp_insert_term($role, WPMF_TAXO, array('parent' => 0, 'slug' => $slug));
                }
            }
        }
    }

    /**
     * add default settings option
     */
    public function addSettingsOption()
    {
        update_option('wpmf_version', WPMF_VERSION);
        if (!get_option('wpmf_gallery_image_size_value', false)) {
            add_option('wpmf_gallery_image_size_value', '["thumbnail","medium","large","full"]');
        }
        if (!get_option('wpmf_padding_masonry', false)) {
            add_option('wpmf_padding_masonry', 5);
        }

        if (!get_option('wpmf_padding_portfolio', false)) {
            add_option('wpmf_padding_portfolio', 10);
        }

        if (!get_option('wpmf_usegellery', false)) {
            add_option('wpmf_usegellery', 1);
        }

        if (!get_option('wpmf_useorder', false)) {
            add_option('wpmf_useorder', 1, '', 'yes');
        }

        if (!get_option('wpmf_create_folder', false)) {
            add_option('wpmf_create_folder', 'role', '', 'yes');
        }

        if (!get_option('wpmf_option_override', false)) {
            add_option('wpmf_option_override', 0, '', 'yes');
        }

        if (!get_option('wpmf_option_duplicate', false)) {
            add_option('wpmf_option_duplicate', 0, '', 'yes');
        }

        if (!get_option('wpmf_active_media', false)) {
            add_option('wpmf_active_media', 0, '', 'yes');
        }

        if (!get_option('wpmf_folder_option2', false)) {
            add_option('wpmf_folder_option2', 1, '', 'yes');
        }

        if (!get_option('wpmf_option_searchall', false)) {
            add_option('wpmf_option_searchall', 0, '', 'yes');
        }

        if (!get_option('wpmf_usegellery_lightbox', false)) {
            add_option('wpmf_usegellery_lightbox', 1, '', 'yes');
        }

        if (!get_option('wpmf_media_rename', false)) {
            add_option('wpmf_media_rename', 0, '', 'yes');
        }

        if (!get_option('wpmf_patern_rename', false)) {
            add_option('wpmf_patern_rename', '{sitename} - {foldername} - #', '', 'yes');
        }

        if (!get_option('wpmf_rename_number', false)) {
            add_option('wpmf_rename_number', 0, '', 'yes');
        }

        if (!get_option('wpmf_option_media_remove', false)) {
            add_option('wpmf_option_media_remove', 0, '', 'yes');
        }

        $dimensions = array('400x300', '640x480', '800x600', '1024x768', '1600x1200');
        $dimensions_string = json_encode($dimensions);
        if (!get_option('wpmf_default_dimension', false)) {
            add_option('wpmf_default_dimension', $dimensions_string, '', 'yes');
        }

        if (!get_option('wpmf_selected_dimension', false)) {
            add_option('wpmf_selected_dimension', $dimensions_string, '', 'yes');
        }

        $weights = array(
            array('0-61440', 'kB'),
            array('61440-122880', 'kB'),
            array('122880-184320', 'kB'),
            array('184320-245760', 'kB'),
            array('245760-307200', 'kB')
        );
        $weight_string = json_encode($weights);
        if (!get_option('wpmf_weight_default', false)) {
            add_option('wpmf_weight_default', $weight_string, '', 'yes');
        }

        if (!get_option('wpmf_weight_selected', false)) {
            add_option('wpmf_weight_selected', $weight_string, '', 'yes');
        }

        $wpmf_color_singlefile = array(
            'bgdownloadlink' => '#444444',
            'hvdownloadlink' => '#888888',
            'fontdownloadlink' => '#ffffff',
            'hoverfontcolor' => '#ffffff'
        );
        if (!get_option('wpmf_color_singlefile', false)) {
            add_option('wpmf_color_singlefile', json_encode($wpmf_color_singlefile), '', 'yes');
        }

        if (!get_option('wpmf_option_singlefile', false)) {
            add_option('wpmf_option_singlefile', 0, '', 'yes');
        }

        if (!get_option('wpmf_option_sync_media', false)) {
            add_option('wpmf_option_sync_media', 0, '', 'yes');
        }

        if (!get_option('wpmf_option_sync_media_external', false)) {
            add_option('wpmf_option_sync_media_external', 0, '', 'yes');
        }

        if (!get_option('wpmf_list_sync_media', false)) {
            add_option('wpmf_list_sync_media', array(), '', 'yes');
        }

        if (!get_option('wpmf_time_sync', false)) {
            add_option('wpmf_time_sync', $this->default_time_sync, '', 'yes');
        }

        if (!get_option('wpmf_lastRun_sync', false)) {
            add_option('wpmf_lastRun_sync', time(), '', 'yes');
        }

        if (!get_option('wpmf_slider_animation', false)) {
            add_option('wpmf_slider_animation', 'slide', '', 'yes');
        }

        if (!get_option('wpmf_option_mediafolder', false)) {
            add_option('wpmf_option_mediafolder', 0, '', 'yes');
        }

        if (!get_option('wpmf_option_countfiles', false)) {
            add_option('wpmf_option_countfiles', 0, '', 'yes');
        }

        if (!get_option('wpmf_option_lightboximage', false)) {
            add_option('wpmf_option_lightboximage', 0, '', 'yes');
        }

        if (!get_option('wpmf_option_hoverimg', false)) {
            add_option('wpmf_option_hoverimg', 1, '', 'yes');
        }

        $format_title = array(
            'hyphen' => 1,
            'underscore' => 1,
            'period' => 0,
            'tilde' => 0,
            'plus' => 0,
            'capita' => 'cap_all',
            'alt' => 0,
            'caption' => 0,
            'description' => 0,
            'hash' => 0,
            'ampersand' => 0,
            'number' => 0,
            'square_brackets' => 0,
            'round_brackets' => 0,
            'curly_brackets' => 0
        );

        if (!get_option('wpmf_options_format_title', false)) {
            add_option('wpmf_options_format_title', $format_title, '', 'yes');
        }

        $watermark_apply = array(
            'all_size' => 1
        );
        $sizes = apply_filters('image_size_names_choose', array(
            'thumbnail' => __('Thumbnail', 'wpmf'),
            'medium' => __('Medium', 'wpmf'),
            'large' => __('Large', 'wpmf'),
            'full' => __('Full Size', 'wpmf'),
        ));
        foreach ($sizes as $ksize => $vsize) {
            $watermark_apply[$ksize] = 0;
        }

        if (!get_option('wpmf_image_watermark_apply', false)) {
            add_option('wpmf_image_watermark_apply', $watermark_apply, '', 'yes');
        }

        if (!get_option('wpmf_option_image_watermark', false)) {
            add_option('wpmf_option_image_watermark', 0, '', 'yes');
        }

        if (!get_option('wpmf_watermark_position', false)) {
            add_option('wpmf_watermark_position', 'top_left', '', 'yes');
        }

        if (!get_option('wpmf_watermark_image', false)) {
            add_option('wpmf_watermark_image', '', '', 'yes');
        }

        if (!get_option('wpmf_watermark_image_id', false)) {
            add_option('wpmf_watermark_image_id', 0, '', 'yes');
        }

        $gallery_settings = array(
            'theme' => array(
                'default_theme' => array(
                    'columns' => 3,
                    'size' => 'medium',
                    'targetsize' => 'large',
                    'link' => 'file',
                    'orderby' => 'post__in',
                    'order' => 'ASC'
                ),
                'portfolio_theme' => array(
                    'columns' => 3,
                    'size' => 'medium',
                    'targetsize' => 'large',
                    'link' => 'file',
                    'orderby' => 'post__in',
                    'order' => 'ASC'
                ),
                'masonry_theme' => array(
                    'columns' => 3,
                    'size' => 'medium',
                    'targetsize' => 'large',
                    'link' => 'file',
                    'orderby' => 'post__in',
                    'order' => 'ASC'
                ),
                'slider_theme' => array(
                    'columns' => 3,
                    'size' => 'medium',
                    'targetsize' => 'large',
                    'link' => 'file',
                    'orderby' => 'post__in',
                    'order' => 'ASC',
                    'animation' => 'slide',
                    'duration' => 4000,
                    'auto_animation' => 1
                ),
                'flowslide_theme' => array(
                    'columns' => 3,
                    'size' => 'medium',
                    'targetsize' => 'large',
                    'link' => 'file',
                    'orderby' => 'post__in',
                    'order' => 'ASC',
                    'show_buttons' => 1
                ),
                'square_grid_theme' => array(
                    'columns' => 3,
                    'size' => 'medium',
                    'targetsize' => 'large',
                    'link' => 'file',
                    'orderby' => 'post__in',
                    'order' => 'ASC'
                ),
                'material_theme' => array(
                    'columns' => 3,
                    'size' => 'medium',
                    'targetsize' => 'large',
                    'link' => 'file',
                    'orderby' => 'post__in',
                    'order' => 'ASC'
                ),
            )
        );
        if (!get_option('wpmf_gallery_settings', false)) {
            add_option('wpmf_gallery_settings', $gallery_settings, '', 'yes');
        }
    }

    /**
     * includes styles and some scripts
     */
    public function loadAdminScripts()
    {
        global $current_screen;
        if (!empty($current_screen->base) && $current_screen->base == 'settings_page_option-folder') {
            wp_enqueue_media();
            wp_enqueue_script(
                'wpmf-script-option',
                plugins_url('/assets/js/script-option.js', dirname(__FILE__)),
                array('jquery', 'plupload'),
                WPMF_VERSION
            );
            wp_localize_script('wpmf-script-option', 'wpmfoption', $this->localizeScript());
            wp_enqueue_script(
                'wpmf-folder-tree-sync',
                plugins_url('/assets/js/sync_media/folder_tree_sync.js', dirname(__FILE__)),
                array(),
                WPMF_VERSION
            );
            wp_enqueue_script(
                'wpmf-folder-tree-categories',
                plugins_url('/assets/js/sync_media/folder_tree_categories.js', dirname(__FILE__)),
                array(),
                WPMF_VERSION
            );
            wp_enqueue_script(
                'wpmf-folder-tree-user',
                plugins_url('/assets/js/tree_users_media.js', dirname(__FILE__)),
                array(),
                WPMF_VERSION
            );
            wp_enqueue_script(
                'wpmf-script-qtip',
                plugins_url('/assets/js/jquery.qtip.min.js', dirname(__FILE__)),
                array('jquery'),
                WPMF_VERSION,
                true
            );
            wp_enqueue_script(
                'wpmf-general-thumb',
                plugins_url('/assets/js/regenerate_thumbnails.js', dirname(__FILE__)),
                array(),
                WPMF_VERSION
            );
            wp_enqueue_style(
                'wpmf-setting-style',
                plugins_url('/assets/css/setting_style.css', dirname(__FILE__)),
                array(),
                WPMF_VERSION
            );
            wp_enqueue_style(
                'wpmf-material-design-iconic-font.min',
                plugins_url('/assets/css/material-design-iconic-font.min.css', dirname(__FILE__)),
                array(),
                WPMF_VERSION
            );
            wp_enqueue_style(
                'wpmf-style-qtip',
                plugins_url('/assets/css/jquery.qtip.css', dirname(__FILE__)),
                array(),
                WPMF_VERSION
            );
        }
    }

    /**
     * Get folder id to sync file from ftp to media library
     * @param string $folder_name folder name
     * @param int $parent id of parent folder
     * @return int
     */
    public function getTermInsert($folder_name, $parent)
    {
        if ($folder_name == '') {
            return 0;
        }
        global $wpdb;
        require_once('ForceUTF8/Encoding.php');
        $folder_name = WpmfEncoding::toUTF8($folder_name);
        $sql = $wpdb->prepare(
            "SELECT $wpdb->terms.term_id FROM $wpdb->terms,$wpdb->term_taxonomy
 WHERE taxonomy=%s AND name=%s AND parent=$parent AND $wpdb->terms.term_id=$wpdb->term_taxonomy.term_id",
            array(WPMF_TAXO, $folder_name)
        );
        $term_id = $wpdb->get_results($sql);
        if (empty($term_id)) {
            $inserted = wp_insert_term(
                $folder_name,
                WPMF_TAXO,
                array(
                    'parent' => $parent,
                    'slug' => sanitize_title($folder_name) . WPMF_TAXO)
            );

            if (is_array($inserted)) {
                $termID = $inserted['term_id'];
            } else {
                $termID = $inserted->error_data['term_exists'];
            }
        } else {
            $termID = $term_id[0]->term_id;
        }

        return $termID;
    }

    /**
     * Includes a script heartbeat
     * @param $hook_suffix
     */
    public function heartbeatEnqueue($hook_suffix)
    {
        wp_enqueue_script('heartbeat');
        add_action('admin_print_footer_scripts', array($this, 'heartbeatFooterJs'), 20);
    }

    /**
     * Inject our JS into the admin footer
     */
    public function heartbeatFooterJs()
    {
        ?>
        <script>
            (function ($) {
                var wpmfajaxsyn = function (current, wpmf_limit_external) {
                    $.ajax({
                        type: "POST",
                        url: ajaxurl,
                        dataType: 'json',
                        data: {
                            action: "wpmf_syncmedia",
                            current: current
                        },
                        success: function (response) {
                            if (response.status === 'error_time') {
                                wpmfajaxsyn(current, wpmf_limit_external);
                            } else {
                                if (typeof wpmf_limit_external !== "undefined") {
                                    wpmfajaxsyn_external(wpmf_limit_external[current[0]]);
                                }
                            }
                        }
                    });
                };

                var wpmfajaxsyn_external = function (current) {
                    $.ajax({
                        type: "POST",
                        url: ajaxurl,
                        dataType: 'json',
                        data: {
                            action: "wpmf_syncmedia_external",
                            current: current
                        },
                        success: function (response) {
                            if (response.status === 'error_time') {
                                wpmfajaxsyn_external(current);
                            }
                        }
                    });
                };
                // Hook into the heartbeat-send
                $(document).on('heartbeat-send', function (e, data) {
                    data['wpmf_heartbeat'] = 'wpmf_queue_process';
                });

                $(document).on('heartbeat-tick', function (e, data) {
                    // Only proceed if our EDD data is present
                    if (!data['wpmf_limit'] && !data['wpmf_limit_external']) {

                    } else if (data['wpmf_limit'] && !data['wpmf_limit_external']) {
                        $.each(data['wpmf_limit'], function (i, v) {
                            wpmfajaxsyn(v);
                        });
                    } else if (!data['wpmf_limit'] && data['wpmf_limit_external']) {
                        $.each(data['wpmf_limit_external'], function (i, v) {
                            wpmfajaxsyn_external(v);
                        });
                    } else {

                        $.each(data['wpmf_limit'], function (i, v) {
                            wpmfajaxsyn(v, data['wpmf_limit_external']);
                        });
                    }
                });
            }(jQuery));
        </script>
        <?php
    }

    /**
     * ajax sync from FTP to media library
     */
    public function syncMedia()
    {
        if (!current_user_can('manage_options')) {
            wp_send_json(false);
        }
        $lists = get_option('wpmf_list_sync_media');
        if (empty($lists)) {
            wp_send_json(array('status' => false));
        }
        $folderID = $_POST['current'][0];
        $v = $_POST['current'][1];
        $root = $v['folder_ftp'];
        if (!@file_exists($root)) {
            wp_send_json(array('status' => false));
        }
        $term = get_term($folderID, WPMF_TAXO);
        if (empty($term)) {
            $i = $this->syncFromFtpToMedia($root, '', 0);
        } else {
            $i = $this->syncFromFtpToMedia($root, $term->name, $term->parent);
        }

        if ($i >= 3) {
            wp_send_json(array('status' => 'error_time'));
        } else {
            wp_send_json(array('status' => 'done'));
        }
    }

    /**
     * ajax sync from media library to ftp
     */
    public function syncMediaExternal()
    {
        if (!current_user_can('manage_options')) {
            wp_send_json(false);
        }
        $lists = get_option('wpmf_list_sync_media');
        if (empty($lists)) {
            wp_send_json(array('status' => false));
        }
        $folderID = $_POST['current'][0];
        $ftp = $_POST['current'][1];
        $folder_ftp = $ftp['folder_ftp'];
        if (!@file_exists($folder_ftp)) {
            wp_send_json(array('status' => false));
        }
        $i = $this->syncFromMediaToFtp($folderID, $folder_ftp);
        if ($i >= 3) {
            wp_send_json(array('status' => 'error_time'));
        }
    }

    /**
     * ajax sync from media library to ftp
     * @param int $folderID id of folder on media library
     * @param string $folder_ftp path of folder on ftp
     * @return int
     */
    public function syncFromMediaToFtp($folderID, $folder_ftp)
    {
        $i = 0;
        $files_rename = get_option('wpmf_list_files_rename');
        // get file
        if (empty($folderID)) {
            $terms = get_categories(array('taxonomy' => WPMF_TAXO, 'hide_empty' => false));
            $unsetTags = array();
            foreach ($terms as $term) {
                $unsetTags[] = $term->slug;
            }
            $args = array(
                'posts_per_page' => -1,
                'post_status' => 'any',
                'post_type' => 'attachment',
                'tax_query' => array(
                    array(
                        'taxonomy' => WPMF_TAXO,
                        'field' => 'term_id',
                        'terms' => $unsetTags,
                        'operator' => 'NOT IN',
                        'include_children' => false
                    )
                )
            );
            $query = new WP_Query($args);
            $files = $query->get_posts();
        } else {
            $files = get_objects_in_term($folderID, WPMF_TAXO);
        }

        // each files & create file
        foreach ($files as $fileID) {
            $pathfile = get_attached_file($fileID);
            if ((!empty($files_rename) && !in_array($pathfile, $files_rename)) || empty($files_rename)) {
                $infofile = pathinfo($pathfile);
                $fileContent = file_get_contents($pathfile);
                if (!file_exists($folder_ftp . '/' . $infofile['basename'])) {
                    file_put_contents($folder_ftp . '/' . $infofile['basename'], $fileContent);
                    $i++;
                }
            }

            if ($i >= 3) {
                return $i;
            }
        }

        // get folder
        $subfolders = get_categories(array('taxonomy' => WPMF_TAXO, 'parent' => (int)$folderID, 'hide_empty' => false));
        if (count($subfolders) > 0) {
            foreach ($subfolders as $subfolder) {
                // create folder if not exist
                if (!file_exists($folder_ftp . '/' . $subfolder->name)) {
                    mkdir($folder_ftp . '/' . $subfolder->name);
                    $i++;
                }
                $subfiles = get_objects_in_term($subfolder->term_id, WPMF_TAXO);
                $subsubfolders = get_categories(
                    array(
                        'taxonomy' => WPMF_TAXO,
                        'parent' => (int)$subfolder->term_id,
                        'hide_empty' => false
                    )
                );
                if (!empty($subfiles) || !empty($subsubfolders)) {
                    $this->syncFromMediaToFtp($subfolder->term_id, $folder_ftp . '/' . $subfolder->name);
                }
                if ($i >= 3) {
                    return $i;
                }
            }
        }
        if ($i >= 3) {
            return $i;
        }

        return $i;
    }

    /**
     * ajax sync from FTP to media library
     * @param $dir : dir name folder folder on ftp
     * @param string $folder_name folder name
     * @param int $parent id of folder parent
     * @return int
     */
    public function syncFromFtpToMedia($dir, $folder_name, $parent)
    {
        $dir = (string)$dir;
        $i = 0;
        $termID = $this->getTermInsert($folder_name, $parent);
        $files = scandir($dir); // List files and directories inside $dir path
        $files = array_diff($files, array('..', '.'));
        $files_rename = get_option('wpmf_list_files_rename');
        if (count($files) > 0) {
            foreach ($files as $file) {
                if (is_dir($dir . '/' . $file)) { // is directory
                    $this->syncFromFtpToMedia($dir . '/' . $file, str_replace('  ', ' ', $file), $termID);
                } else {
                    // is file
                    $upload_dir = wp_upload_dir();
                    $info_file = wp_check_filetype($dir . '/' . $file);
                    if (!empty($info_file) && !empty($info_file['ext'])
                        && in_array(strtolower($info_file['ext']), $this->type_import)
                    ) {
                        $form_file = $dir . '/' . $file;
                        $file_title = $file;
                        $file = sanitize_file_name($file);
                        // check file exist , if not exist then insert file
                        $pid = $this->checkExistPost('/' . $file, $termID);
                        if (empty($pid)) {
                            if ($file_title != wp_unique_filename($upload_dir['path'], $file)) {
                                if (empty($files_rename)) {
                                    $files_rename = array();
                                    $files_rename[] = $upload_dir['path'] . '/
                                    ' . wp_unique_filename($upload_dir['path'], $file);
                                } else {
                                    $fn = $upload_dir['path'] . '/' . wp_unique_filename($upload_dir['path'], $file);
                                    if (!in_array($fn, $files_rename)) {
                                        $files_rename[] = $fn;
                                    }
                                }
                                update_option('wpmf_list_files_rename', $files_rename);
                            }
                            $check = $this->insertAttachmentMetadata(
                                $upload_dir['path'],
                                $upload_dir['url'],
                                $file_title,
                                $file,
                                $form_file,
                                $info_file['type'],
                                $info_file['ext'],
                                $termID
                            );
                            if ($check) {
                                $i++;
                            }
                        }
                    }
                }

                if ($i >= 3) {
                    return $i;
                }
            }
        }

        return $i;
    }

    /**
     * Modify the data that goes back with the heartbeat-tick
     *
     * @param array $response The Heartbeat response.
     * @param array $data The $_POST data sent.
     * @return mixed $response
     */
    public function heartbeatReceived($response, $data)
    {
        if (!current_user_can('install_plugins')) {
            return $response;
        }

        $sync = get_option('wpmf_option_sync_media');
        $sync_externa = get_option('wpmf_option_sync_media_external');
        if (empty($sync) && empty($sync_externa)) {
            return $response;
        }

        if (isset($data['wpmf_heartbeat']) && $data['wpmf_heartbeat'] == 'wpmf_queue_process') {
            $lists = get_option('wpmf_list_sync_media');
            $lastRun = get_option('wpmf_lastRun_sync');
            $time_sync = get_option('wpmf_time_sync');
            if (empty($lists)) {
                return $response;
            }

            if ($time_sync == 0) {
                return $response;
            }

            if (time() - (int)$lastRun < (int)$time_sync * 60) {
                return $response;
            }

            update_option('wpmf_lastRun_sync', time());
            foreach ($lists as $folderId => $v) {
                if (@file_exists($v['folder_ftp'])) {
                    $current = array($folderId, $v);
                    // check option sync from ftp to media active
                    $option_sync = get_option('wpmf_option_sync_media');
                    if (!empty($option_sync)) {
                        $response['wpmf_limit'][$folderId] = $current;
                    }
                    // check option sync from media to ftp active
                    $option_external = get_option('wpmf_option_sync_media_external');
                    if (!empty($option_external)) {
                        $response['wpmf_limit_external'][$folderId] = $current;
                    }
                }
            }
        }
        return $response;
    }

    /**
     * Check post exist to sync . If not exist then do sync
     * @param string $file url of file
     * @param int $termID id of folder
     * @return null|string
     */
    public function checkExistPost($file, $termID)
    {
        global $wpdb;
        $infos = pathinfo($file);
        $file = $infos['filename'];
        if (empty($termID)) {
            $sql = $wpdb->prepare(
                "SELECT COUNT(*) FROM " . $wpdb->prefix . "posts"
                . " WHERE guid LIKE %s AND post_type = 'attachment' "
                . "AND ID NOT IN(SELECT object_id FROM " . $wpdb->prefix . "term_relationships) ",
                array("%$file%")
            );
        } else {
            $sql = $wpdb->prepare(
                "SELECT COUNT(*) FROM " . $wpdb->prefix . "posts," . $wpdb->prefix . "term_relationships"
                . " WHERE guid LIKE %s AND post_type = 'attachment' "
                . "AND ID = object_id "
                . "AND term_taxonomy_id=%d",
                array("%$file%", $termID)
            );
        }
        $check_exist = $wpdb->get_var($sql);
        return $check_exist;
    }

    /**
     * Localize a script.
     * Works only if the script has already been added.
     * @return array
     */
    public function localizeScript()
    {
        $wpmf_folder_root_id = get_option('wpmf_folder_root_id');
        $root_media_root = get_term_by('id', $wpmf_folder_root_id, WPMF_TAXO);
        $l18n = array(
            'undimension' => __('Remove dimension', 'wpmf'),
            'editdimension' => __('Edit dimension', 'wpmf'),
            'unweight' => __('Remove weight', 'wpmf'),
            'editweight' => __('Edit weight', 'wpmf'),
            'media_library' => __('Media Library', 'wpmf'),
            'error' => __('This value is already existing', 'wpmf')
        );
        return array(
            'l18n' => $l18n,
            'vars' => array(
                'wpmf_root_site' => $this->validatePath(ABSPATH),
                'root_media_root' => $root_media_root->term_id
            )
        );
    }

    /**
     * add NextGEN galleries notice
     */
    public function showNotice()
    {
        if (current_user_can('manage_options')) {
            echo '<div class="error" id="wpmf_error">'
                . '<p>'
                . __('You\'ve just installed WP Media Folder,
                 to save your time we can import your nextgen gallery into WP Media Folder', 'wpmf')
                . '<a href="#" class="button button-primary"
                 style="margin: 0 5px;" id="wmpfImportgallery">
                 ' . __('Sync/Import NextGEN galleries', 'wpmf') . '</a> or
                  <a href="#" style="margin: 0 5px;" class="button wmpfNoImportgallery">
                  ' . __('No thanks ', 'wpmf') . '</a>
                  <span class="spinner" style="display:none; margin:0; float:none"></span>'
                . '</p>'
                . '</div>';
        }
    }

    /**
     * add WP Media Folder setting menu
     */
    public function addSettingsMenu()
    {
        add_options_page(
            'Setting Folder Options',
            'WP Media Folder',
            'manage_options',
            'option-folder',
            array($this, 'viewFolderOptions')
        );
    }

    /**
     * View settings page and update option
     */
    public function viewFolderOptions()
    {
        if (isset($_POST['btn_wpmf_save'])) {
            if (is_plugin_active('wp-media-folder-gallery-addon/wp-media-folder-gallery-addon.php')) {
                if (isset($_POST['wpmf_gallery_settings'])) {
                    update_option('wpmf_gallery_settings', $_POST['wpmf_gallery_settings']);
                }
            }

            if (isset($_POST['wpmf_options_format_title'])) {
                update_option('wpmf_options_format_title', $_POST['wpmf_options_format_title']);
            }

            if (isset($_POST['wpmf_image_watermark_apply'])) {
                update_option('wpmf_image_watermark_apply', $_POST['wpmf_image_watermark_apply']);
            }

            if (isset($_POST['wpmf_color_singlefile'])) {
                update_option('wpmf_color_singlefile', json_encode($_POST['wpmf_color_singlefile']));

                $file = WP_MEDIA_FOLDER_PLUGIN_DIR . '/assets/css/wpmf_single_file.css';
                if (@file_exists($file)) {
                    // get custom settings single file
                    $wpmf_color_singlefile = json_decode(get_option('wpmf_color_singlefile'));
                    $image_download = '../images/download.png';
                    // custom css by settings
                    $custom_css = "
                            .wpmf-defile{
                                background: " . $wpmf_color_singlefile->bgdownloadlink . " url(" . $image_download . ")
                                 no-repeat scroll 5px center !important;
                                color: " . $wpmf_color_singlefile->fontdownloadlink . ";
                                border: none;
                                border-radius: 0;
                                box-shadow: none;
                                text-shadow: none;
                                transition: all 0.2s ease 0s;
                                float: left;
                                margin: 7px;
                                padding: 10px 20px 10px 60px;
                                text-decoration: none;
                            }
                            
                            .wpmf-defile:hover{
                                background: " . $wpmf_color_singlefile->hvdownloadlink . " url(" . $image_download . ")
                                 no-repeat scroll 5px center !important;
                                box-shadow: 1px 1px 12px #ccc !important;
                                color: " . $wpmf_color_singlefile->hoverfontcolor . " !important;
                            }
                            ";

                    // write custom css to file wpmf_single_file.css
                    file_put_contents(
                        $file,
                        $custom_css
                    );
                }
            }

            // update selected dimension
            if (isset($_POST['dimension'])) {
                $selected_d = json_encode($_POST['dimension']);
                update_option('wpmf_selected_dimension', $selected_d);
            } else {
                update_option('wpmf_selected_dimension', '[]');
            }

            // update selected weight
            if (isset($_POST['weight'])) {
                $selected_w = array();
                foreach ($_POST['weight'] as $we) {
                    $s = explode(',', $we);
                    $selected_w[] = array($s[0], $s[1]);
                }

                $se_w = json_encode($selected_w);
                update_option('wpmf_weight_selected', $se_w);
            } else {
                update_option('wpmf_weight_selected', '[]');
            }

            // update padding gallery
            if (isset($_POST['padding_gallery'])) {
                $padding_themes = $_POST['padding_gallery'];
                foreach ($padding_themes as $key => $padding_theme) {
                    if (!is_numeric($padding_theme)) {
                        if ($key == 'wpmf_padding_masonry') {
                            $padding_theme = 5;
                        } else {
                            $padding_theme = 10;
                        }
                    }
                    $padding_theme = (int)$padding_theme;
                    if ($padding_theme > 30 || $padding_theme < 0) {
                        if ($key == 'wpmf_padding_masonry') {
                            $padding_theme = 5;
                        } else {
                            $padding_theme = 10;
                        }
                    }

                    $pad = get_option($key);
                    if (!isset($pad)) {
                        add_option($key, $padding_theme);
                    } else {
                        update_option($key, $padding_theme);
                    }
                }
            }

            // update list size
            if (isset($_POST['size_value'])) {
                $size_value = json_encode($_POST['size_value']);
                update_option('wpmf_gallery_image_size_value', $size_value);
            }

            if (isset($_POST['wpmf_patern'])) {
                $pattern = trim($_POST['wpmf_patern']);
                update_option('wpmf_patern_rename', $pattern);
            }

            if (isset($_POST['input_time_sync'])) {
                if ((int)$_POST['input_time_sync'] < 0) {
                    $time_sync = (int)$this->default_time_sync;
                } else {
                    $time_sync = (int)$_POST['input_time_sync'];
                }
                update_option('wpmf_time_sync', $time_sync);
            }

            if (isset($_POST['folder_design'])) {
                wpmfSetOption('folder_design', $_POST['folder_design']);
            }
            // update checkbox options
            $options_name = array(
                'wpmf_option_mediafolder',
                'wpmf_create_folder',
                'wpmf_option_override',
                'wpmf_option_duplicate',
                'wpmf_active_media',
                'wpmf_usegellery',
                'wpmf_useorder',
                'wpmf_option_searchall',
                'wpmf_option_media_remove',
                'wpmf_usegellery_lightbox',
                'wpmf_media_rename',
                'wpmf_option_singlefile',
                'wpmf_option_sync_media',
                'wpmf_option_sync_media_external',
                'wpmf_slider_animation',
                'wpmf_option_countfiles',
                'wpmf_option_lightboximage',
                'wpmf_option_hoverimg',
                'wpmf_option_image_watermark',
                'wpmf_watermark_position',
                'wpmf_watermark_image',
                'wpmf_watermark_image_id'
            );

            foreach ($options_name as $option) {
                $this->updateOption($option);
            }

            if (isset($_POST['wpmf_active_media']) && $_POST['wpmf_active_media'] == 1) {
                $wpmf_checkbox_tree = get_option('wpmf_checkbox_tree');
                if (!empty($wpmf_checkbox_tree)) {
                    $current_parrent = get_term($wpmf_checkbox_tree, WPMF_TAXO);
                    if (!empty($current_parrent)) {
                        $term_user_root = $wpmf_checkbox_tree;
                    } else {
                        $term_user_root = 0;
                    }
                } else {
                    $term_user_root = 0;
                }

                if (isset($_POST['wpmf_checkbox_tree']) && (int)$_POST['wpmf_checkbox_tree'] != (int)$term_user_root) {
                    global $wpdb;
                    $query = "SELECT $wpdb->terms.term_id,$wpdb->terms.term_group "
                        . " FROM $wpdb->terms "
                        . " INNER JOIN $wpdb->term_taxonomy mt ON mt.term_id = $wpdb->terms.term_id
                         AND mt.parent = $term_user_root "
                        . " WHERE $wpdb->terms.term_group !=0";
                    $lists_terms = $wpdb->get_results($query);
                    update_option('wpmf_checkbox_tree', $_POST['wpmf_checkbox_tree']);
                    $term_user_root = $_POST['wpmf_checkbox_tree'];
                    if (!empty($lists_terms)) {
                        foreach ($lists_terms as $lists_term) {
                            $user_data = get_userdata($lists_term->term_group);
                            $user_roles = $user_data->roles;
                            $role = array_shift($user_roles);
                            if (isset($role) && $role != 'administrator') {
                                wp_update_term(
                                    (int)$lists_term->term_id,
                                    WPMF_TAXO,
                                    array('parent' => (int)$term_user_root)
                                );
                            }
                        }
                    }
                }
            }

            $this->getSuccessMessage();
        }

        $design = wpmfGetOption('folder_design');
        $option_mediafolder = get_option('wpmf_option_mediafolder');
        $wpmf_create_folder = get_option('wpmf_create_folder');
        $option_override = get_option('wpmf_option_override');
        $option_duplicate = get_option('wpmf_option_duplicate');
        $wpmf_active_media = get_option('wpmf_active_media');
        $btnoption = get_option('wpmf_use_taxonomy');
        $btn_import_categories = get_option('_wpmf_import_notice_flag');

        $padding_masonry = get_option('wpmf_padding_masonry');
        $padding_portfolio = get_option('wpmf_padding_portfolio');
        $size_selected = json_decode(get_option('wpmf_gallery_image_size_value'));
        $usegellery = get_option('wpmf_usegellery');
        $slider_animation = get_option('wpmf_slider_animation');
        $useorder = get_option('wpmf_useorder');
        $option_searchall = get_option('wpmf_option_searchall');
        $use_glr_lightbox = get_option('wpmf_usegellery_lightbox');
        $wpmf_media_rename = get_option('wpmf_media_rename');
        $wpmf_pattern = get_option('wpmf_patern_rename');
        $option_hoverimg = get_option('wpmf_option_hoverimg');

        $option_media_remove = get_option('wpmf_option_media_remove');
        $s_dimensions = get_option('wpmf_default_dimension');
        $a_dimensions = json_decode($s_dimensions);
        $string_s_de = get_option('wpmf_selected_dimension');
        $array_s_de = json_decode($string_s_de);

        $s_weights = get_option('wpmf_weight_default');
        $a_weights = json_decode($s_weights);
        $string_s_we = get_option('wpmf_weight_selected');
        $array_s_we = json_decode($string_s_we);

        $option_countfiles = get_option('wpmf_option_countfiles');
        $option_lightboximage = get_option('wpmf_option_lightboximage');
        $option_singlefile = get_option('wpmf_option_singlefile');
        $wpmf_color_singlefile = json_decode(get_option('wpmf_color_singlefile'));
        $wpmf_list_sync_media = get_option('wpmf_list_sync_media');
        $option_sync_media = get_option('wpmf_option_sync_media');
        $sync_media_ex = get_option('wpmf_option_sync_media_external');
        $time_sync = get_option('wpmf_time_sync');
        $opts_format_title = get_option('wpmf_options_format_title');
        $option_image_watermark = get_option('wpmf_option_image_watermark');
        $watermark_position = get_option('wpmf_watermark_position');
        $watermark_apply = get_option('wpmf_image_watermark_apply');
        $watermark_image = get_option('wpmf_watermark_image');
        $watermark_image_id = get_option('wpmf_watermark_image_id');
        if (!empty($wpmf_list_sync_media)) {
            foreach ($wpmf_list_sync_media as $k => $v) {
                if (!empty($k)) {
                    $term = get_term($k, WPMF_TAXO);
                    if (!empty($term)) {
                        $this->getCategoryDir($k, $term->parent, $term->name);
                    }
                } else {
                    $this->breadcrumb_category[0] = '/';
                }
            }
        }

        if (is_plugin_active('wp-media-folder-addon/wp-media-folder-addon.php')) {
            if (file_exists(WP_PLUGIN_DIR . '/wp-media-folder-addon/class/wpmfGoogle.php')) {
                require_once(WP_PLUGIN_DIR . '/wp-media-folder-addon/class/wpmfGoogle.php');
            }
            if (file_exists(WP_PLUGIN_DIR . '/wp-media-folder-addon/class/wpmfDropbox.php')) {
                require_once(WP_PLUGIN_DIR . '/wp-media-folder-addon/class/wpmfDropbox.php');
            }
            if (file_exists(WP_PLUGIN_DIR . '/wp-media-folder-addon/class/wpmfOneDrive.php')) {
                require_once(WP_PLUGIN_DIR . '/wp-media-folder-addon/class/wpmfOneDrive.php');
            }
            if (file_exists(WP_PLUGIN_DIR . '/wp-media-folder-addon/class/wpmfHelper.php')) {
                require_once(WP_PLUGIN_DIR . '/wp-media-folder-addon/class/wpmfHelper.php');
            }
            // google drive
            $googleconfig = get_option('_wpmfAddon_cloud_config');
            if (isset($_POST['googleClientId']) && isset($_POST['googleClientSecret'])) {
                if (is_array($googleconfig) && !empty($googleconfig)) {
                    $googleconfig['googleClientId'] = trim($_POST['googleClientId']);
                    $googleconfig['googleClientSecret'] = trim($_POST['googleClientSecret']);
                } else {
                    $googleconfig = array(
                        'googleClientId' => $_POST['googleClientId'],
                        'googleClientSecret' => $_POST['googleClientSecret']
                    );
                }
                update_option('_wpmfAddon_cloud_config', $googleconfig);
            }

            $googleDrive = new WpmfAddonGoogleDrive();
            $googleconfig = get_option('_wpmfAddon_cloud_config');
            if (empty($googleconfig)) {
                $googleconfig = array('googleClientId' => '', 'googleClientSecret' => '');
            }

            $html_tabgoogle = apply_filters('wpmfaddon_ggsettings', $googleDrive, $googleconfig);
            // dropbox
            $Dropbox = new WpmfAddonDropbox();
            $dropboxconfig = get_option('_wpmfAddon_dropbox_config');
            if (isset($_POST['dropboxKey']) && isset($_POST['dropboxSecret'])) {
                if (is_array($dropboxconfig) && !empty($dropboxconfig)) {
                    if (!empty($_POST['dropboxAuthor'])) {
                        //convert code authorCOde to Token
                        $list = $Dropbox->convertAuthorizationCode($_POST['dropboxAuthor']);
                    }
                    if (!empty($list['accessToken'])) {
                        //save accessToken to database
                        $dropboxconfig['dropboxToken'] = $list['accessToken'];
                    }
                    $dropboxconfig['dropboxKey'] = trim($_POST['dropboxKey']);
                    $dropboxconfig['dropboxSecret'] = trim($_POST['dropboxSecret']);
                } else {
                    $dropboxconfig = array(
                        'dropboxKey' => $_POST['dropboxKey'],
                        'dropboxSecret' => $_POST['dropboxSecret']
                    );
                }
                update_option('_wpmfAddon_dropbox_config', $dropboxconfig);
            }

            $Dropbox = new WpmfAddonDropbox();
            $wpmfAddon_dropbox_config = get_option('_wpmfAddon_dropbox_config');
            if (empty($wpmfAddon_dropbox_config)) {
                $dropboxconfig = array('dropboxKey' => '', 'dropboxSecret' => '');
            }

            $html_tabdropbox = apply_filters('wpmfaddon_dbxsettings', $Dropbox, $dropboxconfig);

            // onedrive
            $onedriveconfig = get_option('_wpmfAddon_onedrive_config');
            if (isset($_POST['OneDriveClientId']) && isset($_POST['OneDriveClientSecret'])) {
                if (is_array($onedriveconfig) && !empty($onedriveconfig)) {
                    $onedriveconfig['OneDriveClientId'] = trim($_POST['OneDriveClientId']);
                    $onedriveconfig['OneDriveClientSecret'] = trim($_POST['OneDriveClientSecret']);
                } else {
                    $onedriveconfig = array(
                        'OneDriveClientId' => $_POST['OneDriveClientId'],
                        'OneDriveClientSecret' => $_POST['OneDriveClientSecret']
                    );
                }
                update_option('_wpmfAddon_onedrive_config', $onedriveconfig);
            }

            if (class_exists('WpmfAddonOneDrive')) {
                $onedriveDrive = new WpmfAddonOneDrive();
                $onedriveconfig = get_option('_wpmfAddon_onedrive_config');
                if (empty($onedriveconfig)) {
                    $onedriveconfig = array('OneDriveClientId' => '', 'OneDriveClientSecret' => '');
                }

                $html_tabonedrive = apply_filters('wpmfaddon_onedrivesettings', $onedriveDrive, $onedriveconfig);
            } else {
                $html_tabonedrive = '';
            }
        }

        // get gallery settings
        if (is_plugin_active('wp-media-folder-gallery-addon/wp-media-folder-gallery-addon.php')) {
            $gallery_configs = get_option('wpmf_gallery_settings');
            $gallery_settings = apply_filters('wpmfgallery_settings', '', $gallery_configs);
        }

        require_once(WP_MEDIA_FOLDER_PLUGIN_DIR . 'class/pages/wp-folder-options.php');
    }

    /**
     * @param $id
     * @param $term_id
     * @param $string
     */
    public function getCategoryDir($id, $term_id, $string)
    {
        $this->breadcrumb_category[$id] = '/' . $string . '/';
        if (!empty($term_id)) {
            $term = get_term($term_id, WPMF_TAXO);
            $this->getCategoryDir($id, $term->parent, $term->name . '/' . $string);
        }
    }

    /**
     * Display info after save settings
     */
    public function getSuccessMessage()
    {
        require_once(WP_MEDIA_FOLDER_PLUGIN_DIR . 'class/pages/saved_info.php');
    }

    /**
     * Update option checkbox
     * @param $option
     */
    public function updateOption($option)
    {
        if (isset($_POST[$option])) {
            update_option($option, $_POST[$option]);
        }
    }

    /**
     * Ajax import from next gallery to media library
     */
    public function importGallery()
    {
        if (!current_user_can('manage_options')) {
            wp_send_json(false);
        }
        global $wpdb;
        if ($_POST['doit'] === 'true') {
            update_option('wpmf_import_nextgen_gallery', 'yes');
        } else {
            update_option('wpmf_import_nextgen_gallery', 'no');
        }

        if ($_POST['doit'] == 'true') {
            $loop = 0;
            $limit = 3;
            $gallerys = $wpdb->get_results("SELECT path,title,gid FROM " . $wpdb->prefix . 'ngg_gallery', OBJECT);
            $site_path = get_home_path();
            $upload_dir = wp_upload_dir();

            if (is_multisite()) {
                $checks = get_term_by('name', 'sites-' . get_current_blog_id(), WPMF_TAXO);
                if (empty($checks) || ((!empty($checks) && $checks->parent != 0))) {
                    $sites_inserted = wp_insert_term('sites-' . get_current_blog_id(), WPMF_TAXO, array('parent' => 0));
                    if (is_wp_error($sites_inserted)) {
                        $sites_parrent = $checks->term_id;
                    } else {
                        $sites_parrent = $sites_inserted['term_id'];
                    }
                } else {
                    $sites_parrent = $checks->term_id;
                }
            } else {
                $sites_parrent = 0;
            }

            if (count($gallerys) > 0) {
                foreach ($gallerys as $gallery) {
                    $gallery_path = $gallery->path;
                    $gallery_path = str_replace('\\', '/', $gallery_path);
                    // create folder from nextgen gallery
                    $wpmf_category = get_term_by('name', $gallery->title, WPMF_TAXO);
                    if (empty($wpmf_category) || ((!empty($wpmf_category)
                            && $wpmf_category->parent != $sites_parrent))
                    ) {
                        $inserted = wp_insert_term($gallery->title, WPMF_TAXO, array('parent' => $sites_parrent));
                        if (is_wp_error($inserted)) {
                            $termID = $wpmf_category->term_id;
                        } else {
                            $termID = $inserted['term_id'];
                        }
                    } else {
                        $termID = $wpmf_category->term_id;
                    }

                    // =========================
                    $sql = $wpdb->prepare(
                        "SELECT pid,filename FROM  " . $wpdb->prefix . "ngg_pictures WHERE galleryid = %d",
                        array(
                            $gallery->gid
                        )
                    );
                    $image_childs = $wpdb->get_results($sql, OBJECT);
                    if (count($image_childs) > 0) {
                        foreach ($image_childs as $image_child) {
                            if ($loop >= $limit) {
                                wp_send_json('error time'); // run again ajax
                            } else {
                                $sql = $wpdb->prepare(
                                    "SELECT COUNT(*) FROM " . $wpdb->prefix . "posts WHERE post_content=%s",
                                    array(
                                        "[wpmf-nextgen-image-$image_child->pid]"
                                    )
                                );
                                $check_import = $wpdb->get_var($sql);
                                // check imported
                                if ($check_import == 0) {
                                    $url_image = $site_path . DIRECTORY_SEPARATOR . $gallery_path;
                                    $url_image .= DIRECTORY_SEPARATOR . $image_child->filename;
                                    $file_headers = @get_headers($url_image);
                                    if ($file_headers[0] != 'HTTP/1.1 404 Not Found') {
                                        $info = pathinfo($url_image);
                                        if (!empty($info) && !empty($info['extension'])) {
                                            $ext = '.' . $info['extension'];
                                            $fn = $upload_dir['path'] . DIRECTORY_SEPARATOR . $image_child->filename;
                                            if (@file_exists($fn)) {
                                                $filename = uniqid() . $ext;
                                            } else {
                                                $filename = $image_child->filename;
                                            }

                                            $upload = copy($url_image, $upload_dir['path'] . '/' . $filename);
                                            // upload images
                                            if ($upload) {
                                                if (($ext == '.jpg')) {
                                                    $post_mime_type = 'image/jpeg';
                                                } else {
                                                    $post_mime_type = 'image/' . substr($ext, 1);
                                                }
                                                $attachment = array(
                                                    'guid' => $upload_dir['url'] . '/' . $filename,
                                                    'post_mime_type' => $post_mime_type,
                                                    'post_title' => str_replace($ext, '', $filename),
                                                    'post_content' => '[wpmf-nextgen-image-' . $image_child->pid . ']',
                                                    'post_status' => 'inherit'
                                                );

                                                $image_path = $upload_dir['path'] . '/' . $filename;
                                                $attach_id = wp_insert_attachment($attachment, $image_path);

                                                $attach_data = wp_generate_attachment_metadata($attach_id, $image_path);
                                                wp_update_attachment_metadata($attach_id, $attach_data);

                                                // create image in folder
                                                wp_set_object_terms((int)$attach_id, (int)$termID, WPMF_TAXO, false);
                                            }
                                            $loop++;
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }
    }

    /**
     * this function do import wordpress category default
     */
    public static function importCategories()
    {
        require_once(WP_MEDIA_FOLDER_PLUGIN_DIR . '/class/class-media-folder.php');
        return WpMediaFolder::importCategories();
    }

    /**
     * Ajax add dimension in settings
     */
    public function addDimension()
    {
        if (!current_user_can('manage_options')) {
            wp_send_json(false);
        }
        if (isset($_POST['width_dimension']) && isset($_POST['height_dimension'])) {
            $min = $_POST['width_dimension'];
            $max = $_POST['height_dimension'];
            $new_dimension = $min . 'x' . $max;
            $s_dimensions = get_option('wpmf_default_dimension');
            $a_dimensions = json_decode($s_dimensions);
            if (in_array($new_dimension, $a_dimensions) == false) {
                array_push($a_dimensions, $new_dimension);
                update_option('wpmf_default_dimension', json_encode($a_dimensions));
                wp_send_json($new_dimension);
            } else {
                wp_send_json(false);
            }
        }
    }

    /**
     * Ajax edit selected size and weight filter
     * @param string $option_name option name
     * @param array $old_value old value
     * @param array $new_value new value
     */
    public function editSelected($option_name, $old_value, $new_value)
    {
        $s_selected = get_option($option_name);
        $a_selected = json_decode($s_selected);

        if (in_array($old_value, $a_selected) == true) {
            $key_selected = array_search($old_value, $a_selected);
            $a_selected[$key_selected] = $new_value;
            update_option($option_name, json_encode($a_selected));
        }
    }

    /**
     * Ajax remove selected size and weight filter
     * @param string $option_name option name
     * @param array $value value of option
     */
    public function removeSelected($option_name, $value)
    {
        $s_selected = get_option($option_name);
        $a_selected = json_decode($s_selected);
        if (in_array($value, $a_selected) == true) {
            $key_selected = array_search($value, $a_selected);
            unset($a_selected[$key_selected]);
            $a_selected = array_slice($a_selected, 0, count($a_selected));
            update_option($option_name, json_encode($a_selected));
        }
    }

    /**
     * Ajax remove size and weight filter
     */
    public function removeDimension()
    {
        if (!current_user_can('manage_options')) {
            wp_send_json(false);
        }
        if (isset($_POST['value']) && $_POST['value'] != '') {
            // remove dimension
            $s_dimensions = get_option('wpmf_default_dimension');
            $a_dimensions = json_decode($s_dimensions);
            if (in_array($_POST['value'], $a_dimensions) == true) {
                $key = array_search($_POST['value'], $a_dimensions);
                unset($a_dimensions[$key]);
                $a_dimensions = array_slice($a_dimensions, 0, count($a_dimensions));
                $update_demen = update_option('wpmf_default_dimension', json_encode($a_dimensions));
                if (is_wp_error($update_demen)) {
                    wp_send_json($update_demen->get_error_message());
                } else {
                    $this->removeSelected('wpmf_selected_dimension', $_POST['value']); // remove selected
                    wp_send_json(true);
                }
            } else {
                wp_send_json(false);
            }
        }
    }

    /**
     * ajax edit size and weight filter
     */
    public function edit()
    {
        if (!current_user_can('manage_options')) {
            wp_send_json(false);
        }
        if (isset($_POST['old_value']) && $_POST['old_value'] != ''
            && isset($_POST['new_value']) && $_POST['new_value'] != ''
        ) {
            $label = $_POST['label'];
            if ($label == 'dimension') {
                $s_dimensions = get_option('wpmf_default_dimension');
                $a_dimensions = json_decode($s_dimensions);
                if ((in_array($_POST['old_value'], $a_dimensions) == true)
                    && (in_array($_POST['new_value'], $a_dimensions) == false)
                ) {
                    $key = array_search($_POST['old_value'], $a_dimensions);
                    $a_dimensions[$key] = $_POST['new_value'];
                    $update_demen = update_option('wpmf_default_dimension', json_encode($a_dimensions));
                    if (is_wp_error($update_demen)) {
                        wp_send_json($update_demen->get_error_message());
                    } else {
                        $this->editSelected('wpmf_selected_dimension', $_POST['old_value'], $_POST['new_value']); // edit selected
                        wp_send_json(array('value' => $_POST['new_value']));
                    }
                } else {
                    wp_send_json(false);
                }
            } else {
                $s_weights = get_option('wpmf_weight_default');
                $a_weights = json_decode($s_weights);
                if (isset($_POST['unit'])) {
                    $old_values = explode(',', $_POST['old_value']);
                    $old = array($old_values[0], $old_values[1]);
                    $new_values = explode(',', $_POST['new_value']);
                    $new = array($new_values[0], $new_values[1]);

                    if ((in_array($old, $a_weights) == true) && (in_array($new, $a_weights) == false)) {
                        $key = array_search($old, $a_weights);
                        $a_weights[$key] = $new;
                        $new_labels = explode('-', $new_values[0]);
                        if ($new_values[1] == 'kB') {
                            $label = ($new_labels[0] / 1024) . ' ' . $new_values[1];
                            $label .= '-';
                            $label .= ($new_labels[1] / 1024) . ' ' . $new_values[1];
                        } else {
                            $label = ($new_labels[0] / (1024 * 1024)) . ' ';
                            $label .= $new_values[1] . '-' . ($new_labels[1] / (1024 * 1024)) . ' ' . $new_values[1];
                        }
                        $update_weight = update_option('wpmf_weight_default', json_encode($a_weights));
                        if (is_wp_error($update_weight)) {
                            wp_send_json($update_weight->get_error_message());
                        } else {
                            $this->editSelected('wpmf_weight_selected', $old, $new); // edit selected
                            wp_send_json(array('value' => $new_values[0], 'label' => $label));
                        }
                    } else {
                        wp_send_json(false);
                    }
                }
            }
        }
    }

    /**
     * ajax add size to size filter
     */
    public function addWeight()
    {
        if (!current_user_can('manage_options')) {
            wp_send_json(false);
        }
        if (isset($_POST['min_weight']) && isset($_POST['max_weight'])) {
            if (!$_POST['unit'] || $_POST['unit'] == 'kB') {
                $min = $_POST['min_weight'] * 1024;
                $max = $_POST['max_weight'] * 1024;
                $unit = 'kB';
            } else {
                $min = $_POST['min_weight'] * 1024 * 1024;
                $max = $_POST['max_weight'] * 1024 * 1024;
                $unit = 'MB';
            }
            $label = $_POST['min_weight'] . ' ' . $unit . '-' . $_POST['max_weight'] . ' ' . $unit;
            $new_weight = array($min . '-' . $max, $unit);

            $s_weights = get_option('wpmf_weight_default');
            $a_weights = json_decode($s_weights);
            if (in_array($new_weight, $a_weights) == false) {
                array_push($a_weights, $new_weight);
                update_option('wpmf_weight_default', json_encode($a_weights));
                wp_send_json(array('key' => $min . '-' . $max, 'unit' => $unit, 'label' => $label));
            } else {
                wp_send_json(false);
            }
        }
    }

    /**
     * ajax remove size to size filter
     */
    public function removeWeight()
    {
        if (!current_user_can('manage_options')) {
            wp_send_json(false);
        }
        if (isset($_POST['value']) && $_POST['value'] != '') {
            $s_weights = get_option('wpmf_weight_default');
            $a_weights = (array)json_decode($s_weights);
            $unit = $_POST['unit'];
            $weight_remove = array($_POST['value'], $unit);
            if (in_array($weight_remove, $a_weights) == true) {
                $key = array_search($weight_remove, $a_weights);
                unset($a_weights[$key]);
                $a_weights = array_slice($a_weights, 0, count($a_weights));
                $update_weight = update_option('wpmf_weight_default', json_encode($a_weights));
                if (is_wp_error($update_weight)) {
                    wp_send_json($update_weight->get_error_message());
                } else {
                    $this->removeSelected('wpmf_weight_selected', $weight_remove);  // remove selected
                    wp_send_json(true);
                }
            } else {
                wp_send_json(false);
            }
        }
    }

    /**
     * ajax generate thumbnail
     */
    public function regenerateThumbnail()
    {
        if (!current_user_can('manage_options')) {
            wp_send_json(false);
        }
        remove_filter('add_attachment', array($GLOBALS['wp_media_folder'], 'afterUpload'));
        global $wpdb;
        $limit = 1;
        $offset = ((int)$_POST['paged'] - 1) * $limit;
        $sql = $wpdb->prepare(
            "SELECT COUNT(ID) FROM " . $wpdb->posts . " WHERE  post_type = 'attachment'
             AND post_mime_type LIKE %s AND guid  NOT LIKE %s",
            array('image%', '%.svg')
        );
        $count_images = $wpdb->get_var($sql);

        $present = (100 / $count_images) * $limit;
        $k = 0;
        $urls = array();
        $sql = $wpdb->prepare(
            "SELECT ID FROM " . $wpdb->posts . " WHERE  post_type = 'attachment'
             AND post_mime_type LIKE %s AND guid  NOT LIKE %s LIMIT %d OFFSET %d",
            array(
                'image%',
                '%.svg',
                $limit,
                $offset
            )
        );
        $attachments = $wpdb->get_results($sql);
        if (empty($attachments)) {
            wp_send_json(array('status' => 'ok', 'paged' => 0, 'success' => $this->result_gennerate_thumb));
        }

        foreach ($attachments as $image) {
            $wpmf_size_filetype = wpmfGetSizeFiletype($image->ID);
            $size = $wpmf_size_filetype['size'];
            update_post_meta($image->ID, 'wpmf_size', $size);
            $fullsizepath = get_attached_file($image->ID);
            if (false === $fullsizepath || !@file_exists($fullsizepath)) {
                $message = sprintf(
                    __('The originally uploaded image file cannot be found at %s', 'wpmf'),
                    '<code>' . esc_html($fullsizepath) . '</code>'
                );
                $this->result_gennerate_thumb .= sprintf(
                    __('<p>&quot;%1$s&quot; (ID %2$s) failed to resize. The error message was: %3$s</p>', 'wpmf'),
                    esc_html(get_the_title($image->ID)),
                    $image->ID,
                    $message
                );
                wp_send_json(
                    array(
                        'status' => 'error_time',
                        'paged' => $_POST['paged'],
                        'success' => $this->result_gennerate_thumb
                    )
                );
            }

            $metadata = wp_generate_attachment_metadata($image->ID, $fullsizepath);
            $url_image = wp_get_attachment_url($image->ID);
            $urls[] = $url_image;
            if (is_wp_error($metadata)) {
                $message = $metadata->get_error_message();
                $this->result_gennerate_thumb .= sprintf(
                    __('<p>&quot;%1$s&quot; (ID %2$s) failed to resize. The error message was: %3$s</p>', 'wpmf'),
                    esc_html(get_the_title($image->ID)),
                    $image->ID,
                    $message
                );
                wp_send_json(
                    array(
                        'status' => 'error_time',
                        'paged' => $_POST['paged'],
                        'success' => $this->result_gennerate_thumb
                    )
                );
            }

            if (empty($metadata)) {
                $message = __('Unknown failure reason.', 'wpmf');
                $this->result_gennerate_thumb .= sprintf(
                    __('<p>&quot;%1$s&quot; (ID %2$s) failed to resize. The error message was: %3$s</p>', 'wpmf'),
                    esc_html(get_the_title($image->ID)),
                    $image->ID,
                    $message
                );
                wp_send_json(
                    array(
                        'status' => 'error_time',
                        'paged' => $_POST['paged'],
                        'success' => $this->result_gennerate_thumb
                    )
                );
            }

            wp_update_attachment_metadata($image->ID, $metadata);
            $this->result_gennerate_thumb .= sprintf(
                __('<p>&quot;%1$s&quot; (ID %2$s) was successfully resized in %3$s seconds.</p>', 'wpmf'),
                esc_html(get_the_title($image->ID)),
                $image->ID,
                timer_stop()
            );
            $k++;
        }

        if ($k >= $limit) {
            wp_send_json(
                array(
                    'status' => 'error_time',
                    'paged' => $_POST['paged'],
                    'success' => $this->result_gennerate_thumb,
                    'percent' => $present,
                    'url' => $urls
                )
            );
        }
    }
}
