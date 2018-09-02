<?php
/* Prohibit direct script loading */
defined('ABSPATH') || die('No direct script access allowed!');

/**
 * Class WpmfFilterSize
 * This class do filter file by size for Media Folder.
 */
class WpmfFilterSize
{

    /**
     * Wpmf_Filter_Size constructor.
     */
    public function __construct()
    {
        // Filter attachments when requesting posts
        add_action('pre_get_posts', array($this, 'filterAttachments'));
    }

    /**
     * Filter attachments
     *
     * @param $query
     * @return mixed
     */
    public function filterAttachments($query)
    {
        // Only filter attachments post type
        if (!isset($query->query_vars['post_type']) || $query->query_vars['post_type'] != 'attachment') {
            return $query;
        }

        // We are on the upload page
        global $pagenow;
        if ($pagenow === 'upload.php') {
            return $this->uploadPageFilter($query);
        }

        // It could be an ajax request
        return $this->modalFilter($query);
    }

    /**
     * Filter attachments for modal windows and upload.php in grid mode
     * More generally handle attachment queries via ajax requests
     *
     * @param $query object
     * @return mixed $query
     */
    protected function modalFilter($query)
    {
        $id_pots = array();
        if ((empty($_REQUEST['query']['wpmf_weight']) || $_REQUEST['query']['wpmf_weight'] == 'all')
            && (isset($_REQUEST['query']['wpmf_size']) && $_REQUEST['query']['wpmf_size'] != 'all')
        ) {
            $id_pots = $this->getSize($_REQUEST['query']['wpmf_size'], '');
        }

        if ((empty($_REQUEST['query']['wpmf_size']) || $_REQUEST['query']['wpmf_size'] == 'all')
            && (isset($_REQUEST['query']['wpmf_weight']) && $_REQUEST['query']['wpmf_weight'] != 'all')
        ) {
            $id_pots = $this->getSize('', $_REQUEST['query']['wpmf_weight']);
        }

        if ((isset($_REQUEST['query']['wpmf_size']) && $_REQUEST['query']['wpmf_size'] != 'all')
            && (isset($_REQUEST['query']['wpmf_weight']) && $_REQUEST['query']['wpmf_weight'] != 'all')
        ) {
            $id_pots = $this->getSize($_REQUEST['query']['wpmf_size'], $_REQUEST['query']['wpmf_weight']);
        }

        if (!empty($id_pots)) {
            $query->query_vars['post__in'] = $id_pots;
        }


        return $query;
    }

    /**
     * Query attachment by size and weight for upload.php page
     * Base on /wp-includes/class-wp-query.php
     * @param $query
     * @return mixed
     */
    protected function uploadPageFilter($query)
    {
        // Save display own media filter in session to use it upon navigation
        if (isset($_GET['wpmf-display-media-filters'])) {
            if ($_GET['wpmf-display-media-filters'] == 'yes') {
                $_SESSION['wpmf_display_media'] = 'yes';
            } elseif ($_GET['wpmf-display-media-filters'] == 'all') {
                unset($_SESSION['wpmf_display_media']);
            }
        }

        // If needed only show current user media
        if (isset($_SESSION['wpmf_display_media'])) {
            $query->query_vars['author'] = get_current_user_id();
        }

        // Save folder ordering in session to use it upon navigation
        if (isset($_GET['folder_order'])) {
            $_SESSION['wpmf_folder_orderby'] = $_GET['folder_order'];
        }

        $id_pots = array();
        if ((isset($_GET['attachment_size']) && $_GET['attachment_size'] != 'all')
            && (empty($_GET['attachment_weight']) || $_GET['attachment_weight'] == 'all')
        ) {
            $id_pots = $this->getSize($_GET['attachment_size'], '');
        }

        if ((isset($_GET['attachment_weight']) && $_GET['attachment_weight'] != 'all')
            && (empty($_GET['attachment_size']) || $_GET['attachment_size'] == 'all')
        ) {
            $id_pots = $this->getSize('', $_GET['attachment_weight']);
        }

        if ((isset($_GET['attachment_size']) && $_GET['attachment_size'] != 'all')
            && (isset($_GET['attachment_weight']) && $_GET['attachment_weight'] != 'all')
        ) {
            $id_pots = $this->getSize($_GET['attachment_size'], $_GET['attachment_weight']);
        }

        if (!empty($id_pots)) {
            $query->query_vars['post__in'] = $id_pots;
        }

        return $query;
    }

    /**
     * Get attachment size
     * @param $sizes string width x height of file
     * @param $weights string min-weight - max-weight of file
     * @return array $id_pots
     */
    protected function getSize($sizes, $weights)
    {
        $w_size = 0;
        $h_size = 0;
        $min_weight = 0;
        $max_weight = 0;
        if ($sizes != '') {
            $size = explode('x', $sizes);
            $w_size = (float)$size[0];
            $h_size = (float)$size[1];
        }

        if ($weights != '') {
            $weight = explode('-', $weights);
            $min_weight = (float)$weight[0];
            $max_weight = (float)$weight[1];
        }
        $id_pots = array(0);
        $upload_dir = wp_upload_dir();
        global $wpdb;
        $sql = $wpdb->prepare(
            "SELECT ID FROM " . $wpdb->prefix . 'posts' . " WHERE post_type = %s ",
            array('attachment')
        );
        $attachments = $wpdb->get_results($sql);
        foreach ($attachments as $attachment) {
            $meta_img = wp_get_attachment_metadata($attachment->ID);
            $meta = get_post_meta($attachment->ID, '_wp_attached_file');
            if (isset($meta[0])) {
                $url_path = $upload_dir['basedir'] . '/' . $meta[0];
                if (file_exists($url_path)) {
                    $weight_att = filesize($url_path);
                } else {
                    $weight_att = 0;
                }
            } else {
                $weight_att = 0;
            }

            // Not an image
            if (!is_array($meta_img)) {
                continue;
            }

            if (empty($meta_img['width'])) {
                $meta_img['width'] = 0;
            }

            if (empty($meta_img['height'])) {
                $meta_img['height'] = 0;
            }

            if ($weights == '') {
                if ((float)$meta_img['width'] >= $w_size || (float)$meta_img['height'] >= $h_size) {
                    if (substr(get_post_mime_type($attachment->ID), 0, 5) == 'image') {
                        $id_pots[] = $attachment->ID;
                    }
                }
            } elseif ($sizes == '') {
                if ((float)$weight_att >= $min_weight && (float)$weight_att <= $max_weight) {
                    $id_pots[] = $attachment->ID;
                }
            } else {
                if (((float)$meta_img['width'] >= $w_size || (float)$meta_img['height'] >= $h_size)
                    && ((float)$weight_att >= $min_weight && (float)$weight_att <= $max_weight)
                ) {
                    if (substr(get_post_mime_type($attachment->ID), 0, 5) == 'image') {
                        $id_pots[] = $attachment->ID;
                    }
                }
            }
        }

        return $id_pots;
    }

    /**
     * setcookie for sort folder
     */
    public function folderOrder()
    {
        if (!current_user_can('upload_files')) {
            wp_send_json(false);
        }
        if (isset($_POST['folderOrder']) && $_POST['folderOrder'] != 'all') {
            $sortbys = explode('-', $_POST['folderOrder']);
            $_SESSION['folderOrderby'] = $sortbys[0];
            $_SESSION['folderOrder'] = $sortbys[1];

            $cookie_name = "folderOrder";
            $cookie_value = $_POST['folderOrder'];
            setcookie($cookie_name, $cookie_value, time() + (86400 * 30), "/");
            wp_send_json($_SESSION['folderOrder']);
        } else {
            setcookie('folderOrder', null, -1, '/');
            wp_send_json('all');
        }
    }
}
