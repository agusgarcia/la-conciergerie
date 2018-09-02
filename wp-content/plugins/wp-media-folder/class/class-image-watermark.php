<?php
/* Prohibit direct script loading */
defined('ABSPATH') || die('No direct script access allowed!');

/**
 * Class WpmfWatermark
 * This class that holds most of the image watermark functionality for Media Folder.
 */
class WpmfWatermark
{
    public $allowed_ext = array('jpg', 'jpeg', 'png', 'gif');

    /**
     * Wpmf_Watermark constructor.
     */
    public function __construct()
    {
        add_action('wp_ajax_wpmf_watermark_regeneration', array($this, 'regeneratePictures'));
        add_filter('wp_generate_attachment_metadata', array($this, 'createWatermarkImage'), 10, 2);
    }

    /**
     * create watermark image after upload image
     * @param array $metadata An array of attachment meta data.
     * @param int $attachment_id Current attachment ID.
     * @return mixed $metadata
     */
    public function createWatermarkImage($metadata, $attachment_id)
    {
        $option_image_watermark = get_option('wpmf_option_image_watermark');
        $post_upload = get_post($attachment_id);
        if (empty($option_image_watermark) || (isset($post_upload->post_mime_type)
                && strpos($post_upload->post_mime_type, 'image') === false)
        ) {
            return $metadata;
        }
        if (!empty($attachment_id)) {
            $check_remote = get_post($attachment_id);
            if (!empty($check_remote) && $check_remote->post_content == 'wpmf_remote_video') {
                return $metadata;
            }
            $watermark_apply = get_option('wpmf_image_watermark_apply');
            $uploads = wp_upload_dir();
            if (isset($watermark_apply['all_size']) && $watermark_apply['all_size'] == 1) {
                $sizes = array_merge(array('full'), get_intermediate_image_sizes());
                foreach ($sizes as $imageSize) {
                    $image_url = '';
                    if ($imageSize == 'full') {
                        $image_url = $uploads['baseurl'] . '/' . $metadata['file'];
                    } else {
                        if (isset($metadata['sizes'][$imageSize])) {
                            $image_url = $uploads['url'] . '/' . $metadata['sizes'][$imageSize]['file'];
                        }
                    }
                    // Using the wp_upload_dir replace the baseurl with the basedir
                    $path = str_replace($uploads['baseurl'], $uploads['basedir'], $image_url);
                    $pathinfo = pathinfo($path);
                    $imageInfo = getimagesize($path);
                    try {
                        $this->generatePicture($pathinfo['basename'], $imageInfo, $pathinfo['dirname']);
                    } catch (Exception $e) {
                        wp_send_json(array('status' => false));
                    }
                }
            } else {
                foreach ($watermark_apply as $imageSize => $value) {
                    if ($value == 1) {
                        $image_url = '';
                        if ($imageSize == 'full') {
                            $image_url = $uploads['baseurl'] . '/' . $metadata['file'];
                        } else {
                            if (isset($metadata['sizes'][$imageSize])) {
                                $image_url = $uploads['url'] . '/' . $metadata['sizes'][$imageSize]['file'];
                            }
                        }
                        // Using the wp_upload_dir replace the baseurl with the basedir
                        $path = str_replace($uploads['baseurl'], $uploads['basedir'], $image_url);
                        $pathinfo = pathinfo($path);
                        $imageInfo = getimagesize($path);
                        try {
                            $this->generatePicture($pathinfo['basename'], $imageInfo, $pathinfo['dirname']);
                        } catch (Exception $e) {
                            wp_send_json(array('status' => false));
                        }
                    }
                }
            }
        }

        return $metadata;
    }

    /**
     * Ajax create watermark image
     */
    public function regeneratePictures()
    {
        if (!current_user_can('upload_files')) {
            wp_send_json(false);
        }
        global $wpdb;
        $limit = 1;
        $offset = ((int)$_POST['paged'] - 1) * $limit;
        $logo_image_id = get_option('wpmf_watermark_image_id', 0);
        $sql = $wpdb->prepare(
            "SELECT COUNT(ID) FROM " . $wpdb->posts . "
             WHERE  post_type = 'attachment' AND ID != %d
              AND post_mime_type LIKE %s AND guid  NOT LIKE %s AND post_content != %s",
            array($logo_image_id, 'image%', '%.svg', 'wpmf_remote_video')
        );
        $count_images = $wpdb->get_var($sql);
        $present = (100 / $count_images) * $limit;
        $k = 0;
        $sql = $wpdb->prepare(
            "SELECT ID FROM " . $wpdb->posts . " WHERE  post_type = 'attachment'
             AND ID != %d AND post_mime_type
              LIKE %s AND guid NOT LIKE %s AND post_content != %s LIMIT %d OFFSET %d",
            array($logo_image_id, 'image%', '%.svg', 'wpmf_remote_video', $limit, $offset)
        );
        $attachments = $wpdb->get_results($sql);
        if (empty($attachments)) {
            wp_send_json(array('status' => 'ok', 'paged' => 0));
        }

        $watermark_apply = get_option('wpmf_image_watermark_apply');
        $uploads = wp_upload_dir();
        if (empty($watermark_apply)) {
            wp_send_json(array('status' => false));
        }
        if (!empty($attachments)) {
            foreach ($attachments as $attachment) {
                $check_remote = get_post_meta($attachment->ID, 'wpmf_remote_video_link');
                if (empty($check_remote)) {
                    if (isset($watermark_apply['all_size']) && $watermark_apply['all_size'] == 1) {
                        $sizes = array_merge(array('full'), get_intermediate_image_sizes());
                        foreach ($sizes as $imageSize) {
                            $image_object = wp_get_attachment_image_src($attachment->ID, $imageSize);
                            // Isolate the url
                            $image_url = $image_object[0];
                            // Using the wp_upload_dir replace the baseurl with the basedir
                            $path = str_replace($uploads['baseurl'], $uploads['basedir'], $image_url);
                            $pathinfo = pathinfo($path);
                            $imageInfo = getimagesize($path);
                            try {
                                $this->generatePicture($pathinfo['basename'], $imageInfo, $pathinfo['dirname']);
                            } catch (Exception $e) {
                                wp_send_json(array('status' => false));
                            }
                        }
                    } else {
                        foreach ($watermark_apply as $imageSize => $value) {
                            if ($value == 1) {
                                $image_object = wp_get_attachment_image_src($attachment->ID, $imageSize);
                                // Isolate the url
                                $image_url = $image_object[0];
                                // Using the wp_upload_dir replace the baseurl with the basedir
                                $path = str_replace($uploads['baseurl'], $uploads['basedir'], $image_url);
                                $pathinfo = pathinfo($path);
                                $imageInfo = getimagesize($path);
                                try {
                                    $this->generatePicture($pathinfo['basename'], $imageInfo, $pathinfo['dirname']);
                                } catch (Exception $e) {
                                    wp_send_json(array('status' => false));
                                }
                            }
                        }
                    }
                    $k++;
                }
            }

            if ($k >= $limit) {
                wp_send_json(array('status' => 'error_time', 'paged' => $_POST['paged'], 'percent' => $present));
            }
        }
    }

    /**
     * Generate Picture
     * @param $newname : new name of image
     * @param $imageInfo : image infomartion
     * @param $full_dir : path of image
     */
    public function generatePicture($newname, $imageInfo, $full_dir)
    {
        $option_image_watermark = get_option('wpmf_option_image_watermark');
        $wtm_images = get_option('wpmf_option_image_watermark');
        $wtm_apply_on = get_option('wpmf_image_watermark_apply');
        if ($option_image_watermark == 0) {
            $logo_image_id = 0;
        } else {
            $logo_image_id = get_option('wpmf_watermark_image_id');
        }
        if ($logo_image_id == 0) {
            $check_image_logo_exit = false;
        } else {
            $wtm_image_logo = get_attached_file($logo_image_id);
            $info_logo = pathinfo($wtm_image_logo);
            $check_image_logo_exit = true;
            if (!empty($info_logo['extension']) && !in_array(strtolower($info_logo['extension']), $this->allowed_ext)) {
                $check_image_logo_exit = false;
            }
        }

        $this->copyFileWithNewName($full_dir, $newname, 'initimage');
        if ($imageInfo['mime'] == 'image/jpeg') {
            if (!empty($wtm_images) && $check_image_logo_exit) {
                $this->checkCopyFileWithNewName($full_dir, $newname, $wtm_apply_on);
            }
        } elseif ($imageInfo['mime'] == 'image/png') {
            if (!empty($wtm_images) && $check_image_logo_exit) {
                $this->checkCopyFileWithNewName($full_dir, $newname, $wtm_apply_on);
            }
        } elseif ($imageInfo['mime'] == 'image/gif') {
            if (!empty($wtm_images) && $check_image_logo_exit) {
                $this->checkCopyFileWithNewName($full_dir, $newname, $wtm_apply_on);
            }
        }
    }

    /**
     * @param $full_dir
     * @param $newname
     * @param $wtm_apply_on
     */
    public function checkCopyFileWithNewName($full_dir, $newname, $wtm_apply_on)
    {
        foreach ($wtm_apply_on as $size => $value) {
            if ($value == 1) {
                $this->copyFileWithNewName($full_dir, $newname, $size);
            }
        }
    }

    /**
     * @param $pathdir
     * @param $fname
     * @param $wtmApplyOn
     * @return bool|string
     */
    public function copyFileWithNewName($pathdir, $fname, $wtmApplyOn)
    {
        $option_image_watermark = get_option('wpmf_option_image_watermark');
        if ($option_image_watermark == 0) {
            $logo_image_id = 0;
        } else {
            $logo_image_id = get_option('wpmf_watermark_image_id');
        }
        $wtm_image_logo = get_attached_file($logo_image_id);
        $wtm_position = get_option('wpmf_watermark_position');
        $wtm_apply_on = get_option('wpmf_image_watermark_apply');

        $check_name_wtm = 'imageswatermark';
        $name_change_file_wtm = pathinfo($fname, PATHINFO_FILENAME) . $check_name_wtm;
        $name_change_file_wtm .= '.' . pathinfo($fname, PATHINFO_EXTENSION);
        $file = $pathdir . '/' . $fname;
        $newfile = $pathdir . '/' . $name_change_file_wtm;
        if ($wtmApplyOn == 'initimage') {
            if (file_exists($newfile)) {
                if (unlink($file)) {
                    if (copy($newfile, $file)) {
                        unlink($newfile);
                        return $newfile;
                    }
                }
            }
            return $newfile;
        }

        if ($wtm_apply_on['all_size'] == 1) {
            if (file_exists($newfile)) {
                $this->watermark($file, $wtm_image_logo, $wtm_position);
                return $newfile;
            } else {
                if (copy($file, $newfile)) {
                    $this->watermark($file, $wtm_image_logo, $wtm_position);
                    return $newfile;
                }
            }
        } else {
            if (empty($wtm_apply_on[$wtmApplyOn])) {
                if (file_exists($newfile)) {
                    unlink($file);
                    copy($newfile, $file);
                    unlink($newfile);
                }
            } else {
                if (file_exists($newfile)) {
                    if (unlink($file)) {
                        if (copy($newfile, $file)) {
                            $this->watermark($file, $wtm_image_logo, $wtm_position);
                            return $newfile;
                        }
                    }
                } else {
                    if (file_exists($file)) {
                        if (copy($file, $newfile)) {
                            $this->watermark($file, $wtm_image_logo, $wtm_position);
                            return $newfile;
                        }//
                    }
                }
            }
        }
        return false;
    }

    /**
     * Create a new image from file or URL
     * @param $image string Path to the JPEG image.
     * @return resource
     */
    public function imagecreatefrom($image)
    {
        $size = getimagesize($image);
        // Load image from file
        switch (strtolower($size['mime'])) {
            case 'image/jpeg':
            case 'image/pjpeg':
                return imagecreatefromjpeg($image);
                break;
            case 'image/png':
                return imagecreatefrompng($image);
                break;
            case 'image/gif':
                return imagecreatefromgif($image);
                break;
            default:
                die("Image is of unsupported type.");
        }
    }

    /**
     * @param $image_path
     * @param $logoImage_path
     * @param $position
     */
    public function watermark($image_path, $logoImage_path, $position)
    {
        if (!file_exists($image_path)) {
            die("Image does not exist.");
        }

        try {
            // Find base image size
            $image = $this->imagecreatefrom($image_path);
            $logoImage = $this->imagecreatefrom($logoImage_path);
            list($image_x, $image_y) = getimagesize($image_path);
            list($logo_x, $logo_y) = getimagesize($logoImage_path);
            $watermark_pos_x = 0;
            $watermark_pos_y = 0;
            if ($position === 'center' || $position === 0) {
                $watermark_pos_x = ($image_x - $logo_x) / 2; //watermark left
                $watermark_pos_y = ($image_y - $logo_y) / 2; //watermark bottom
            }
            if ($position === 'top_left') {
                $watermark_pos_x = 0;
                $watermark_pos_y = 0;
            }
            if ($position === 'top_right') {
                $watermark_pos_x = $image_x - $logo_x;
                $watermark_pos_y = 0;
            }
            if ($position === 'bottom_right') {
                $watermark_pos_x = $image_x - $logo_x;
                $watermark_pos_y = $image_y - $logo_y;
            }
            if ($position === 'bottom_left') {
                $watermark_pos_x = 0;
                $watermark_pos_y = $image_y - $logo_y;
            }

            imagecopy($image, $logoImage, $watermark_pos_x, $watermark_pos_y, 0, 0, $logo_x, $logo_y);

            // Output to the browser
            $imageInfo = getimagesize($image_path);
            switch (strtolower($imageInfo['mime'])) {
                case 'image/jpeg':
                case 'image/pjpeg':
                    header("Content-Type: image/jpeg");
                    imagejpeg($image, $image_path);
                    break;
                case 'image/png':
                    header("Content-Type: image/png");
                    imagepng($image, $image_path);
                    break;
                case 'image/gif':
                    header("Content-Type: image/gif");
                    imagegif($image, $image_path);
                    break;
                default:
                    die("Image is of unsupported type.");
            }
            // Destroy the images
            imagedestroy($image);
            imagedestroy($logoImage);
        } catch (Exception $e) {
            return;
        }
    }
}
