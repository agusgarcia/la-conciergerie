<?php
/* Prohibit direct script loading */
defined('ABSPATH') || die('No direct script access allowed!');

/**
 * Class WpmfMediaRename
 * This class that holds most of the rename file functionality for Media Folder.
 */
class WpmfMediaRename
{
    /**
     * WpmfMediaRename constructor.
     */
    public function __construct()
    {
        add_filter('wp_handle_upload_prefilter', array($this, 'customUploadFilter'));
        add_filter('wp_generate_attachment_metadata', array($this, 'afterUpload'), 10, 2);
    }

    /**
     * rename attachment after upload
     * @param array $file An array of data for a single file.
     * @return array $file
     */
    public function customUploadFilter($file)
    {
        global $pagenow;
        if (isset($pagenow) && $pagenow == 'update.php') {
            return $file;
        }

        $pattern = get_option('wpmf_patern_rename');
        $upload_dir = wp_upload_dir();
        $info = pathinfo($file['name']);
        $wpmf_rename_number = get_option('wpmf_rename_number');
        if (!empty($_POST['wpmf_folder'])) {
            $current_folder = get_term((int)$_POST['wpmf_folder'], WPMF_TAXO);
            $foldername = $current_folder->name;
        } else {
            $foldername = 'uncategorized';
        }

        $sitename = get_bloginfo('name');
        $original_filename = $info['filename'];
        $date = trim($upload_dir['subdir'], '/');
        $ext = empty($info['extension']) ? '' : '.' . $info['extension'];
        $number = (int)$wpmf_rename_number + 1;
        $pattern = str_replace('{sitename}', $sitename, $pattern);
        $pattern = str_replace('{date}', $date, $pattern);
        $pattern = str_replace(' - #', '', $pattern);

        if (strpos($pattern, '{foldername}') !== false
            || strpos($pattern, '{original name}') !== false) {
            $pattern = str_replace('{foldername}', $foldername, $pattern);
            $pattern = str_replace('{original name}', $original_filename, $pattern);
            $filename = wp_unique_filename($upload_dir['path'], $pattern . $ext);
        } else {
            $filename = $pattern . '-' . $number . $ext;
        }

        $file['name'] = $filename;
        return $file;
    }

    /**
     * update option wpmf_rename_number
     * Base on /wp-admin/includes/image.php
     * @param array $metadata An array of attachment meta data.
     * @param int $attachment_id Current attachment ID.
     * @return mixed $metadata
     */
    public function afterUpload($metadata, $attachment_id)
    {
        $wpmf_rename_number = get_option('wpmf_rename_number');
        $number = (int)$wpmf_rename_number + 1;
        update_option('wpmf_rename_number', $number);
        return $metadata;
    }
}
