<?php
/* Prohibit direct script loading */
defined('ABSPATH') || die('No direct script access allowed!');

/**
 * Class WpmfOrderbyMedia
 * This class that holds most of the order functionality for Media Folder.
 */
class WpmfOrderbyMedia
{

    /**
     * Wpmf_Orderby_Media constructor.
     */
    public function __construct()
    {
        add_filter('manage_media_columns', array($this, 'manageMediaColumns'));
        add_filter('manage_upload_sortable_columns', array($this, 'imwidthColumnRegisterSortable'));
        add_filter('manage_media_custom_column', array($this, 'manageMediaCustomColumn'), 10, 2);
        add_action('pre_get_posts', array($this, 'filter'), 0, 1);
        add_filter('post_mime_types', array($this, 'modifyPostMimeTypes'));
    }

    /**
     * Add file type to Filetype filter
     * @param $post_mime_types : list of post mime types.
     * @return array $post_mime_types
     */
    public function modifyPostMimeTypes($post_mime_types)
    {
        if (empty($post_mime_types['wpmf-pdf'])) {
            $post_mime_types['wpmf-pdf'] = array(__('PDF', 'wpmf'));
        }
        if (empty($post_mime_types['wpmf-zip'])) {
            $post_mime_types['wpmf-zip'] = array(__('Zip & archives', 'wpmf'));
        }

        $post_mime_types['wpmf-other'] = array(__('Other', 'wpmf'));
        return $post_mime_types;
    }

    /**
     * Query attachment by file type
     * Base on /wp-includes/class-wp-query.php
     * @param $query
     * @return mixed $query
     */
    public function filter($query)
    {
        if (!isset($query->query_vars['post_type']) || $query->query_vars['post_type'] != 'attachment') {
            return $query;
        }

        global $pagenow, $wpdb;

        $views = get_user_meta(get_current_user_id(), $wpdb->prefix . 'media_library_mode');
        if (!empty($views)) {
            $current_view = $views[0];
        } else {
            $current_view = 'grid';
        }

        if ($pagenow == 'upload.php') {
            if ($current_view == 'list') {
                if (isset($_GET["media-order-media"]) && empty($_GET['orderby']) && empty($_GET['order'])) {
                    if ($_GET["media-order-media"] == 'custom') {
                        $query->set('meta_key', 'wpmf_order');
                        $query->set('orderby', 'meta_value_num');
                        $query->set('order', 'ASC');
                    } else {
                        if ($_GET["media-order-media"] == 'all') {
                            $order_media = 'title|asc';
                        } else {
                            $order_media = $_GET["media-order-media"];
                        }
                        $cook = explode('|', $order_media);
                        $wpmf_allowed = array(
                            'name', 'author', 'date',
                            'title', 'modified', 'uploadedTo',
                            'id', 'post__in', 'menuOrder'
                        );
                        if ($cook) {
                            if ($cook[0] == 'size') {
                                $query->set('meta_key', 'wpmf_size');
                                $query->set('orderby', 'meta_value_num');
                                $query->set('order', $cook[1]);
                            } elseif ($cook[0] == 'filetype') {
                                $query->set('meta_key', 'wpmf_filetype');
                                $query->set('orderby', 'meta_value');
                                $query->set('order', $cook[1]);
                            } elseif (in_array($cook[0], $wpmf_allowed)) {
                                $query->set('orderby', $cook[0]);
                                $query->set('order', $cook[1]);
                            }
                        }
                    }
                } elseif (isset($_GET['orderby'])) {
                    $orderby = $_GET['orderby'];
                    if ('size' == $orderby) {
                        $query->set('meta_key', 'wpmf_size');
                        $query->set('orderby', 'meta_value_num');
                    }

                    if ('filetype' == $orderby) {
                        $query->set('meta_key', 'wpmf_filetype');
                        $query->set('orderby', 'meta_value');
                    }
                }
            }
        } else {
            if (isset($_COOKIE["gridwpmf_media_order"]) && empty($_REQUEST['query']['meta_key'])) {
                $g_cook = explode('|', $_COOKIE["gridwpmf_media_order"]);
                if ($g_cook[0] == 'size') {
                    $query->query_vars['meta_key'] = 'wpmf_size';
                    $query->query_vars['orderby'] = 'meta_value_num';
                } elseif ($g_cook[0] == 'filetype') {
                    $query->query_vars['meta_key'] = 'wpmf_filetype';
                    $query->query_vars['orderby'] = 'meta_value';
                } else {
                    if (isset($_REQUEST['query']['wpmf_orderby']) && isset($_REQUEST['query']['order'])) {
                        $query->query_vars['orderby'] = $_REQUEST['query']['wpmf_orderby'];
                        $query->query_vars['order'] = $_REQUEST['query']['order'];
                    }
                }
            } else {
                if (isset($_REQUEST['query']['meta_key']) && $_REQUEST['query']['wpmf_orderby']) {
                    $query->query_vars['meta_key'] = $_REQUEST['query']['meta_key'];
                    $query->query_vars['orderby'] = $_REQUEST['query']['wpmf_orderby'];
                }
            }
        }

        if (isset($_GET['attachment-filter'])) {
            $filetype_wpmf = $_GET['attachment-filter'];
        }
        if (isset($_REQUEST['query']['post_mime_type'])) {
            $filetype_wpmf = $_REQUEST['query']['post_mime_type'];
        }

        if (isset($filetype_wpmf)) {
            if ($filetype_wpmf == 'wpmf-pdf' || $filetype_wpmf == 'wpmf-zip' || $filetype_wpmf == 'wpmf-other') {
                $filetypes = explode('-', $filetype_wpmf);
                $filetype = $filetypes[1];
                if ($filetype == 'zip' || $filetype == 'pdf' || $filetype == 'other') {
                    $query->query_vars['post_mime_type'] = '';
                    $query->query_vars['meta_key'] = 'wpmf_filetype';
                    switch ($filetype) {
                        case 'pdf':
                            $query->query_vars['meta_value'] = 'pdf';
                            break;
                        case 'zip':
                            $query->query_vars['meta_value'] = array(
                                'zip', 'rar', 'ace', 'arj',
                                'bz2', 'cab', 'gzip', 'iso',
                                'jar', 'lzh', 'tar', 'uue',
                                'xz', 'z', '7-zip'
                            );
                            break;
                        case 'other':
                            $exts = array(
                                'jpg', 'jpeg', 'jpe', 'gif',
                                'png', 'bmp', 'tiff', 'tif',
                                'ico', 'asf', 'asx', 'wmv',
                                'wmx', 'wm', 'avi', 'divx',
                                'flv', 'mov', 'qt', 'mpeg',
                                'mpg', 'mpe', 'mp4', 'm4v',
                                'ogv', 'webm', 'mkv', '3gp',
                                '3gpp', '3g2', '3gp2', 'txt',
                                'asc', 'c', 'cc', 'h',
                                'srt', 'csv', 'tsv', 'ics',
                                'rtx', 'css', 'html', 'htm',
                                'vtt', 'dfxp', 'mp3', 'm4a',
                                'm4b', 'ra', 'ram', 'wav',
                                'ogg', 'oga', 'mid', 'midi',
                                'wma', 'wax', 'mka', 'rtf',
                                'js', 'pdf', 'class', 'tar',
                                'zip', 'gz', 'gzip', 'rar',
                                '7z', 'psd', 'xcf', 'doc',
                                'pot', 'pps', 'ppt', 'wri',
                                'xla', 'xls', 'xlt', 'xlw',
                                'mdp', 'mpp', 'docx', 'docm',
                                'dotx', 'xlsx', 'xlsm', 'xlsb',
                                'xltx', 'xltm', 'xlam', 'pptx',
                                'pptm', 'ppsx', 'ppsm', 'potx',
                                'potm', 'ppam', 'sldx', 'sldm',
                                'onetoc', 'onetoc2', 'onetmp', 'onepkg',
                                'oxps', 'xps', 'odt', 'odp',
                                'ods', 'odg', 'odc', 'odb',
                                'odf', 'wp', 'wpd', 'key', 'numbers', 'pages'
                            );
                            $other = array_diff(
                                $exts,
                                array(
                                    "zip", "rar", "ace", "arj",
                                    "bz2", "cab", "gzip", "iso",
                                    "jar", "lzh", "tar", "uue",
                                    "xz", "z", "7-zip", "pdf",
                                    "mp3", "mp4", "jpg", "png",
                                    "gif", "bmp", "svg"
                                )
                            );
                            if (empty($other)) {
                                $other = 'wpmf_none';
                            }
                            $query->query_vars['meta_value'] = $other;
                            break;
                    }
                }
            }
        }
        return $query;
    }


    /**
     * Add size column and filetype column
     * @param $columns : An array of columns displayed in the Media list table.
     * @return array $columns
     */
    public static function manageMediaColumns($columns)
    {
        $columns['wpmf_size'] = __('Size', 'wpmf');
        $columns['wpmf_filetype'] = __('File type', 'wpmf');
        return $columns;
    }

    /**
     * register sortcolumn
     * @param $columns : An array of sort columns.
     * @return array $columns
     */
    public function imwidthColumnRegisterSortable($columns)
    {
        $columns['wpmf_size'] = 'size';
        $columns['wpmf_filetype'] = 'filetype';
        return $columns;
    }

    /**
     * get size and filetype of attachment
     * @param $pid : id of attachment
     * @return array $wpmf_size_filetype
     */
    public function getSizeFiletype($pid)
    {
        $wpmf_size_filetype = array();
        $meta = get_post_meta($pid, '_wp_attached_file');
        $upload_dir = wp_upload_dir();
        // get path file
        $path_attachment = $upload_dir['basedir'] . '/' . $meta[0];
        if (file_exists($path_attachment)) {
            // get size
            $size = filesize($path_attachment);
            // get file type
            $filetype = wp_check_filetype($path_attachment);
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
     * get size and filetype of attachment
     * @param $column_name : column name
     * @param $id : id of attachment
     */
    public function manageMediaCustomColumn($column_name, $id)
    {
        $wpmf_size_filetype = $this->getSizeFiletype($id);
        $size = $wpmf_size_filetype['size'];
        $ext = $wpmf_size_filetype['ext'];
        if ($size < 1024 * 1024) {
            $size = round($size / 1024, 1) . ' kB';
        } elseif ($size > 1024 * 1024) {
            $size = round($size / (1024 * 1024), 1) . ' MB';
        }

        switch ($column_name) {
            case 'wpmf_size':
                echo $size;
                break;

            case 'wpmf_filetype':
                echo $ext;
                break;
        }
    }
}
