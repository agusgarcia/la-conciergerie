<?php
/* Prohibit direct script loading */
defined('ABSPATH') || die('No direct script access allowed!');

/**
 * Class WpmfBackgroundFolder
 * This class that holds most of the background folder functionality for Media Folder.
 */
class WpmfBackgroundFolder
{

    /**
     * Wpmf_Background_Folder constructor.
     */
    public function __construct()
    {
        add_filter("attachment_fields_to_edit", array($this, "attachmentFieldsToEdit"), 10, 2);
        add_filter("attachment_fields_to_save", array($this, "attachmentFieldsToSave"), 10, 2);
    }

    /**
     * add custom field background color for attachment
     * @param array $form_fields An array of attachment form fields.
     * @param WP_Post $post The WP_Post attachment object.
     * @return mixed
     */
    public function attachmentFieldsToEdit($form_fields, $post)
    {
        global $pagenow;
        if ($pagenow != 'post.php') {
            $currentFolder = 0;
            $current_folder = get_the_terms($post, WPMF_TAXO);
            if (!empty($current_folder) && is_array($current_folder)) {
                foreach ($current_folder as $folder) {
                    if ($folder->taxonomy == 'wpmf-category') {
                        $currentFolder = $folder->term_id;
                    }
                }
            }
            if (!empty($current_folder) && substr($post->post_mime_type, 0, 5) == 'image') {
                $option_bgfolder = get_option('wpmf_field_bgfolder');
                $name = 'attachments[' . $post->ID . '][wpmf_field_bgfolder]';
                $id = 'attachments-' . $post->ID . '-wpmf_field_bgfolder';
                $class = 'wpmf_field_bgfolder';
                if (!empty($option_bgfolder) && !empty($current_folder)
                    && !empty($option_bgfolder[$currentFolder]) && $option_bgfolder[$currentFolder][0] == $post->ID
                ) {
                    $html = '<input checked type="checkbox"
                     class="' . $class . '" id="' . $id . '" name="' . $name . '">';
                } else {
                    $html = '<input type="checkbox" class="' . $class . '" id="' . $id . '" name="' . $name . '">';
                }
                $form_fields['wpmf_field_bgfolder'] = array(
                    "label" => __('Folder cover', 'wpmf'),
                    "input" => "html",
                    'html' => $html
                );
            }
        }

        return $form_fields;
    }

    /**
     * Save background for folder
     * @param array $post An array of post data.
     * @param array $attachment An array of attachment metadata.
     * @return mixed $post
     */
    public function attachmentFieldsToSave($post, $attachment)
    {
        // Retrieve previous image covers
        $cover_images = get_option('wpmf_field_bgfolder');

        // Define array if not yet any cover image defined
        if (empty($cover_images)) {
            $cover_images  = array();
        }

        // Retrieve the current folder the post is in
        $current_folder_id = (int)$_POST['wpmf_folder'];

        if (isset($attachment['wpmf_field_bgfolder']) && $attachment['wpmf_field_bgfolder'] == 'on') {
            // This image should be the cover image

            // Retrieve the thumbnail image
            $image_thumb = wp_get_attachment_image_src($post['ID'], 'thumbnail');

            // Affect post ID and image thumbnail to the folder
            $cover_images[$current_folder_id] = array((int)$post['ID'], $image_thumb[0]);

            $post['wpmf_cover_updated'] = true;
        } elseif (isset($cover_images[$current_folder_id]) && $cover_images[$current_folder_id][0] === $post['ID']) {
            // Delete the cover for this folder
            unset($cover_images [$current_folder_id]);
        }

        update_option('wpmf_field_bgfolder', $cover_images);
        return $post;
    }
}
