<?php
/* Prohibit direct script loading */
defined('ABSPATH') || die('No direct script access allowed!');

/**
 * Class WpmfDisplayGallery
 * This class that holds most of the gallery functionality for Media Folder.
 */
class WpmfDisplayGallery
{

    /**
     * Wpmf_Display_Gallery constructor.
     */
    public function __construct()
    {
        add_action('wp_enqueue_media', array($this, 'galleryEnqueueAdminScripts'));
        add_action('wp_enqueue_scripts', array($this, 'galleryScripts'));
        add_shortcode('wpmf_gallery', array($this, 'galleryShortcode'));
        add_filter('post_gallery', array($this, 'galleryDefaultShortcode'), 11, 3);
        add_action('print_media_templates', array($this, 'galleryPrintMediaTemplates'));
        add_filter("attachment_fields_to_edit", array($this, "galleryAttachmentFieldsToEdit"), 10, 2);
        add_filter("attachment_fields_to_save", array($this, "galleryAttachmentFieldsToSave"), 10, 2);
        add_action('wp_ajax_update_link', array($this, 'updateLink'));
        add_filter('wp_generate_attachment_metadata', array($this, 'uploadAfter'), 10, 2);
        add_action('delete_post', array($this, 'deleteAttachment'));
        add_filter('widget_media_gallery_instance_schema', array($this, 'mediaGalleryInstanceSchema'), 10, 2);
    }

    /**
     * compatible with elementor plugin
     * @param $schema
     * @return mixed
     */
    public function mediaGalleryInstanceSchema($schema)
    {
        $schema['display'] = array(
            'type' => 'string',
            'enum' => array(
                'default',
                'masonry',
                'portfolio',
                'slider',
            ),
            'default' => 'default'
        );

        $schema['wpmf_autoinsert'] = array(
            'type' => 'string',
            'enum' => array(0,1),
            'default' => 0
        );

        $schema['wpmf_orderby'] = array(
            'type' => 'string',
            'enum' => array(
                'post__in',
                'rand',
                'title',
                'date'
            ),
            'default' => 'post__in'
        );

        $schema['wpmf_order'] = array(
            'type' => 'string',
            'enum' => array(
                'ASC',
                'DESC'
            ),
            'default' => 'ASC'
        );

        return $schema;
    }

    /**
     * includes styles and some scripts
     */
    public function galleryScripts()
    {
        wp_register_style(
            'wpmf-flexslider-style',
            plugins_url('assets/css/display-gallery/flexslider.css', dirname(__FILE__)),
            array(),
            '2.4.0'
        );
        wp_register_script(
            'wordpresscanvas-imagesloaded',
            plugins_url('/assets/js/display-gallery/imagesloaded.pkgd.min.js', dirname(__FILE__)),
            array(),
            '3.1.5',
            true
        );
        wp_register_script(
            'wpmf-gallery-popup',
            plugins_url('/assets/js/display-gallery/jquery.magnific-popup.min.js', dirname(__FILE__)),
            array('jquery'),
            '0.9.9',
            true
        );
        wp_register_script(
            'wpmf-gallery-flexslider',
            plugins_url('assets/js/display-gallery/flexslider/jquery.flexslider.js', dirname(__FILE__)),
            array('jquery'),
            '2.0.0',
            true
        );
        wp_register_script(
            'wpmf-gallery',
            plugins_url('assets/js/display-gallery/site_gallery.js', dirname(__FILE__)),
            array('jquery', 'wordpresscanvas-imagesloaded'),
            WPMF_VERSION,
            true
        );
        wp_localize_script(
            'wpmf-gallery',
            'wpmfggr',
            $this->localizeScript()
        );
    }

    /**
     * Localize a script.
     * Works only if the script has already been added.
     * @return array
     */
    public function localizeScript()
    {
        $option_usegellery_lightbox = get_option('wpmf_usegellery_lightbox');
        $option_current_theme = get_option('current_theme');
        $slider_animation = get_option('wpmf_slider_animation');
        return array(
            'wpmf_lightbox_gallery' => (int) $option_usegellery_lightbox,
            'wpmf_current_theme' => $option_current_theme,
            'slider_animation' => $slider_animation
        );
    }

    /**
     * includes some scripts
     */
    public function galleryEnqueueAdminScripts()
    {
        global $pagenow;
        if ($pagenow != 'upload.php') {
            wp_enqueue_script(
                'wpmf-gallery-admin-js',
                plugins_url('assets/js/display-gallery/admin_gallery.js', dirname(__FILE__)),
                array('jquery'),
                WPMF_VERSION,
                true
            );
        }
    }

    public function gallery($attr)
    {
        $post = get_post();
        static $instance = 0;
        $instance++;
        if (isset($attr['orderby'])) {
            $attr['orderby'] = sanitize_sql_orderby($attr['orderby']);
            if (!$attr['orderby']) {
                unset($attr['orderby']);
            }
        }

        extract(shortcode_atts(array(
            'order' => 'ASC', 'orderby' => 'menu_order ID', 'id' => $post ? $post->ID : 0,
            'columns' => 3, 'gutterwidth' => '5', 'link' => 'post',
            'size' => 'thumbnail', 'targetsize' => 'large',
            'display' => 'default', 'wpmf_orderby' => 'post__in', 'wpmf_order' => 'ASC',
            'customlink' => 0, 'bottomspace' => 'default', 'hidecontrols' => 'false',
            'class' => '', 'include' => '', 'exclude' => '', 'wpmf_folder_id' => 0,
            'wpmf_autoinsert' => 0), $attr, 'gallery'));


        $custom_class = trim($class);
        $id = intval($id);
        $orderby = $wpmf_orderby;
        $order = $wpmf_order;
        if ('RAND' == $order) {
            $orderby = 'none';
        }

        if (isset($wpmf_autoinsert) && $wpmf_autoinsert == 1 && isset($wpmf_folder_id)) {
            $root_id = (int) get_option('wpmf_folder_root_id');
            if ($wpmf_folder_id == 0) {
                $terms = get_categories(
                    array(
                        'taxonomy' => WPMF_TAXO,
                        'hide_empty' => false,
                        'hierarchical' => false
                    )
                );
                $unsetTags = array();
                foreach ($terms as $term) {
                    $unsetTags[] = $term->term_id;
                }

                if (in_array($root_id, $unsetTags)) {
                    $key = array_search($root_id, $unsetTags);
                    unset($unsetTags[$key]);
                }

                $args = array(
                    'posts_per_page' => -1,
                    'post_status' => 'any',
                    'post_type' => 'attachment',
                    'order' => $order,
                    'orderby' => $orderby,
                    'tax_query' => array(
                        'relation' => 'OR',
                        array(
                            'taxonomy' => WPMF_TAXO,
                            'field' => 'term_id',
                            'terms' => $unsetTags,
                            'operator' => 'NOT IN',
                            'include_children' => false
                        ),
                        array(
                            'taxonomy' => WPMF_TAXO,
                            'field' => 'term_id',
                            'terms' => (int) $root_id,
                            'include_children' => false
                        )
                    )
                );
                $query = new WP_Query($args);
                $_attachments = $query->get_posts();
            } else {
                $args = array(
                    'posts_per_page' => -1,
                    'post_status' => 'any',
                    'post_type' => 'attachment',
                    'order' => $order,
                    'orderby' => $orderby,
                    'tax_query' => array(
                        array(
                            'taxonomy' => WPMF_TAXO,
                            'field' => 'term_id',
                            'terms' => (int)$wpmf_folder_id,
                            'operator' => 'IN',
                            'include_children' => false
                        )
                    )
                );
                $query = new WP_Query($args);
                $_attachments = $query->get_posts();
            }

            $attachments = array();
            foreach ($_attachments as $key => $val) {
                $attachments[$val->ID] = $_attachments[$key];
            }
        } else {
            if (!empty($include)) {
                $_attachments = get_posts(
                    array(
                        'include' => $include,
                        'post_status' => 'inherit',
                        'post_type' => 'attachment',
                        'post_mime_type' => 'image',
                        'order' => $order,
                        'orderby' => $orderby
                    )
                );
                $attachments = array();
                foreach ($_attachments as $key => $val) {
                    $attachments[$val->ID] = $_attachments[$key];
                }
            } elseif (!empty($exclude)) {
                $attachments = get_children(
                    array(
                        'post_parent' => $id,
                        'exclude' => $exclude,
                        'post_status' => 'inherit',
                        'post_type' => 'attachment',
                        'post_mime_type' => 'image',
                        'order' => $order,
                        'orderby' => $orderby
                    )
                );
            } else {
                $attachments = get_children(
                    array(
                        'post_parent' => $id,
                        'post_status' => 'inherit',
                        'post_type' => 'attachment',
                        'post_mime_type' => 'image',
                        'order' => $order,
                        'orderby' => $orderby
                    )
                );
            }
        }

        if (empty($attachments)) {
            return '';
        }

        if (is_feed()) {
            $output = "\n";
            foreach ($attachments as $att_id => $attachment) {
                $output .= wp_get_attachment_link($att_id, $size, true) . "\n";
            }

            return $output;
        }

        $columns = intval($columns);

        $selector = "gallery-{$instance}";
        $size_class = sanitize_html_class($size);
        $customlink = 1 == $customlink ? true : false;
        $class = array();
        $class[] = 'gallery';

        if ($link == 'file' || $link == 'none') {
            $customlink = false;
        } else {
            $customlink = true;
        }
        if (!empty($custom_class)) {
            $class[] = esc_attr($custom_class);
        }

        if (!$customlink) {
            $class[] = "gallery-link-{$link}";
        }


        if ($link == 'file') {
            wp_enqueue_script('wpmf-gallery-popup');
        }

        wp_enqueue_script('jquery');
        wp_enqueue_style(
            'wpmf-gallery-style',
            plugins_url('/assets/css/display-gallery/style-display-gallery.css', dirname(__FILE__)),
            array(),
            WPMF_VERSION
        );
        wp_enqueue_style(
            'wpmf-material-design-iconic-font.min',
            plugins_url('/assets/css/material-design-iconic-font.min.css', dirname(__FILE__))
        );
        wp_enqueue_style(
            'wpmf-gallery-popup-style',
            plugins_url('/assets/css/display-gallery/magnific-popup.css', dirname(__FILE__)),
            array(),
            '0.9.9'
        );

        switch ($display) {
            case "slider":
                require(WP_MEDIA_FOLDER_PLUGIN_DIR . 'themes-gallery/gallery-slider.php');
                break;

            case "masonry":
                require(WP_MEDIA_FOLDER_PLUGIN_DIR . 'themes-gallery/gallery-mansory.php');
                break;

            case "portfolio":
                require(WP_MEDIA_FOLDER_PLUGIN_DIR . 'themes-gallery/gallery-portfolio.php');
                break;

            default:
                require(WP_MEDIA_FOLDER_PLUGIN_DIR . 'themes-gallery/gallery-default.php');
                break;
        }

        return $output;
    }

    /**
     * Display gallery on frontend
     * @param $blank
     * @param array $attr Attributes of the gallery shortcode.
     * @return string $output   The gallery output. Default empty.
     */
    public function galleryDefaultShortcode($blank, $attr)
    {
        $output = $this->gallery($attr);
        return $output;
    }

    /**
     * Display gallery from folder on frontend
     * @param array $attr Attributes of the gallery shortcode.
     * @return string $output   The gallery output. Default empty.
     */
    public function galleryShortcode($attr)
    {
        $output = $this->gallery($attr);
        return $output;
    }

    /**
     * Generate html attachment link
     * @param int $id id of image
     * @param string $size size of image
     * @param bool $permalink
     * @param string $targetsize Optional. Image size. Accepts any valid image size, or an array of width
     * @param bool $customlink
     * @param string $target target of link
     * @return mixed|string
     */
    public function getAttachmentLink(
        $id = 0,
        $size = 'thumbnail',
        $permalink = false,
        $targetsize = 'large',
        $customlink = false,
        $target = '_self'
    ) {
        $id = intval($id);
        $_post = get_post($id);

        if (empty($_post) || ('attachment' != $_post->post_type) || !$url = wp_get_attachment_url($_post->ID)) {
            return __('Missing Attachment', 'wpmf');
        }

        $lb = 0;
        if ($customlink) {
            $url = get_post_meta($_post->ID, _WPMF_GALLERY_PREFIX . 'custom_image_link', true);
            if ($url == '') {
                $url = get_attachment_link($_post->ID);
            }
        } elseif ($permalink) {
            $url = get_attachment_link($_post->ID);
        } elseif ($targetsize) {
            if (get_post_meta($id, _WPMF_GALLERY_PREFIX . 'custom_image_link', true) != '') {
                $lb = 0;
                $url = get_post_meta($_post->ID, _WPMF_GALLERY_PREFIX . 'custom_image_link', true);
            } else {
                $lb = 1;
                $img = wp_get_attachment_image_src($_post->ID, $targetsize);
                $url = $img[0];
            }
        }

        $title = esc_attr($_post->post_title);

        if ($size && 'none' != $size) {
            $text = wp_get_attachment_image($id, $size);
        } else {
            $text = '';
        }

        if (trim($text) == '') {
            $text = $_post->post_title;
        }

        $current_theme = get_option('current_theme');
        if (isset($current_theme) && $current_theme == 'Gleam') {
            $tclass = 'fancybox';
        } else {
            $tclass = '';
        }

        $remote_video = get_post_meta($id, 'wpmf_remote_video_link', true);
        if (empty($remote_video)) {
            $class = $tclass . ' not_video';
        } else {
            $class = $tclass . ' isvideo';
            $url = $remote_video;
        }

        return apply_filters(
            'wp_get_attachment_link',
            "<a class='$class' data-lightbox='$lb' href='$url' title='$title' target='$target'>$text</a>",
            $id,
            $size,
            $permalink,
            false,
            false
        );
    }

    /**
     * Display settings gallery when custom gallery in back-end
     */
    public function galleryPrintMediaTemplates()
    {
        $display_types = array(
            'default' => __('Default', 'wpmf'),
            'masonry' => __('Masonry', 'wpmf'),
            'portfolio' => __('Portfolio', 'wpmf'),
            'slider' => __('Slider', 'wpmf'),
        );
        ?>

        <script type="text/html" id="tmpl-wpmf-gallery-settings">
            <label class="setting">
                <span><?php _e('Gallery themes', 'wpmf'); ?></span>
            </label>

            <label class="setting">
                <select class="display" name="display" data-setting="display">
                    <?php foreach ($display_types as $key => $value) : ?>
                        <option
                                value="<?php echo esc_attr($key); ?>" <?php selected($key, 'default'); ?>>
                            <?php echo esc_html($value); ?></option>
                    <?php endforeach; ?>
                </select>
            </label>

            <label class="setting">
                <span><?php _e('Columns', 'wpmf'); ?></span>
            </label>

            <label class="setting">
                <select class="columns" name="columns" data-setting="columns">
                    <option value="1">1</option>
                    <option value="2">2</option>
                    <option value="3" selected>3</option>
                    <option value="4">4</option>
                    <option value="5">5</option>
                    <option value="6">6</option>
                    <option value="7">7</option>
                    <option value="8">8</option>
                    <option value="9">9</option>
                </select>
            </label>

            <label class="setting size">
                <span><?php _e('Gallery image size', 'wpmf'); ?></span>
            </label>

            <label class="setting size">
                <select class="size" name="size" data-setting="size">
                    <?php
                    $sizes_value = json_decode(get_option('wpmf_gallery_image_size_value'));
                    $sizes = apply_filters('image_size_names_choose', array(
                        'thumbnail' => __('Thumbnail', 'wpmf'),
                        'medium' => __('Medium', 'wpmf'),
                        'large' => __('Large', 'wpmf'),
                        'full' => __('Full Size', 'wpmf'),
                    ));
                    ?>

                    <?php foreach ($sizes_value as $key) : ?>
                        <option
                                value="<?php echo esc_attr($key); ?>" <?php selected($key, 'thumbnail'); ?>>
                            <?php echo esc_html($sizes[$key]); ?></option>
                    <?php endforeach; ?>

                </select>
            </label>

            <label class="setting">
                <span><?php _e('Lightbox size', 'wpmf'); ?></span>
            </label>

            <label class="setting">
                <select class="targetsize" name="targetsize" data-setting="targetsize">
                    <?php
                    $sizes = array(
                        'thumbnail' => __('Thumbnail', 'wpmf'),
                        'medium' => __('Medium', 'wpmf'),
                        'large' => __('Large', 'wpmf'),
                        'full' => __('Full Size', 'wpmf'),
                    );
                    ?>

                    <?php foreach ($sizes as $key => $name) : ?>
                        <option
                                value="<?php echo esc_attr($key); ?>" <?php selected($key, 'large'); ?>>
                            <?php echo esc_html($name); ?></option>
                    <?php endforeach; ?>
                </select>
            </label>

            <label class="setting">
                <span><?php _e('Action on click', 'wpmf'); ?></span>
            </label>

            <label class="setting">
                <select class="link-to" name="link" data-setting="link">
                    <option value="file" selected><?php _e('Lightbox', 'wpmf'); ?></option>
                    <option value="post"><?php _e('Attachment Page', 'wpmf'); ?></option>
                    <option value="none"><?php _e('None', 'wpmf'); ?></option>
                </select>
            </label>

            <label class="setting">
                <span><?php _e('Auto insert image in folder', 'wpmf'); ?></span>
            </label>

            <label class="setting">
                <select class="wpmf_autoinsert" name="wpmf_autoinsert" data-setting="wpmf_autoinsert">
                    <option value="0" selected><?php _e('No', 'wpmf'); ?></option>
                    <option value="1"><?php _e('Yes', 'wpmf'); ?></option>
                </select>
            </label>

            <label class="setting">
                <span><?php _e('Order by', 'wpmf'); ?></span>
            </label>

            <label class="setting">
                <select class="wpmf_orderby" name="wpmf_orderby" data-setting="wpmf_orderby">
                    <option value="post__in" selected><?php _e('Custom', 'wpmf'); ?></option>
                    <option value="rand"><?php _e('Random', 'wpmf'); ?></option>
                    <option value="title"><?php _e('Title', 'wpmf'); ?></option>
                    <option value="date"><?php _e('Date', 'wpmf'); ?></option>
                </select>
            </label>

            <label class="setting">
                <span><?php _e('Order', 'wpmf'); ?></span>
            </label>

            <label class="setting">
                <select class="wpmf_order" name="wpmf_order" data-setting="wpmf_order">
                    <option value="ASC" selected><?php _e('Ascending', 'wpmf'); ?></option>
                    <option value="DESC"><?php _e('Descending', 'wpmf'); ?></option>
                </select>
            </label>

            <label>
                <input type="text" class="wpmf_folder_id" data-setting="wpmf_folder_id" style="display: none">
            </label>
        </script>
        <?php
    }

    /**
     * Add custom field for attachment
     * Based on /wp-admin/includes/media.php
     * @param array $form_fields An array of attachment form fields.
     * @param WP_Post $post The WP_Post attachment object.
     * @return mixed $form_fields
     */
    public function galleryAttachmentFieldsToEdit($form_fields, $post)
    {
        $target_value = get_post_meta($post->ID, '_gallery_link_target', true);
        $form_fields['gallery_link_target'] = array(
            'label' => __('Link target', 'wpmf'),
            'input' => 'html',
            'html' => '
                        <select name="attachments[' . $post->ID . '][gallery_link_target]"
                         id="attachments[' . $post->ID . '][gallery_link_target]">
                                <option value="">' . __('Same Window', 'wpmf') . '</option>
                                <option value="_blank"' . ($target_value == '_blank' ? ' selected="selected"' : '') . '>
                                ' . __('New Window', 'wpmf') . '</option>
                        </select>'
        );

        return $form_fields;
    }

    /**
     * Save custom field for attachment
     * Based on /wp-admin/includes/media.php
     * @param array $post An array of post data.
     * @param array $attachment An array of attachment metadata.
     * @return mixed $post
     */
    public function galleryAttachmentFieldsToSave($post, $attachment)
    {
        if (isset($attachment['wpmf_gallery_custom_image_link'])) {
            update_post_meta(
                $post['ID'],
                _WPMF_GALLERY_PREFIX . 'custom_image_link',
                esc_url_raw($attachment['wpmf_gallery_custom_image_link'])
            );
        }

        if (isset($attachment['gallery_link_target'])) {
            update_post_meta($post['ID'], '_gallery_link_target', $attachment['gallery_link_target']);
        }

        return $post;
    }

    /**
     * Ajax update link for attachment
     */
    public function updateLink()
    {
        if (!current_user_can('upload_files')) {
            wp_send_json(false);
        }
        $attachment_id = $_POST['id'];
        update_post_meta($attachment_id, '_wpmf_gallery_custom_image_link', esc_url_raw($_POST['link']));
        update_post_meta($attachment_id, '_gallery_link_target', $_POST['link_target']);
        $link = get_post_meta($attachment_id, '_wpmf_gallery_custom_image_link');
        $target = get_post_meta($attachment_id, '_gallery_link_target');
        wp_send_json(array('link' => $link, 'target' => $target));
    }

    /**
     * When use 'auto insert image from folder' feature , do Ajax update gallery when delete attachment
     * @param $pid : id of post
     */
    public function deleteAttachment($pid)
    {
        $post_type = get_post_type($pid);
        $post_types = get_post_types(array('public' => true, 'exclude_from_search' => false));
        if (in_array($post_type, $post_types)) {
            $this->updateGallery();
        }
    }

    /**
     * When use 'auto insert image from folder' feature , do Ajax update gallery after upload
     * Base on /wp-admin/includes/image.php
     * @param array $metadata An array of attachment meta data.
     * @param int $attachment_id Current attachment ID.
     * @return mixed $metadata
     */
    public function uploadAfter($metadata, $attachment_id)
    {
        $this->updateGallery();
        return $metadata;
    }

    /**
     * get all images id in root folder
     * @param $gallery
     * @return array
     */
    public function autoInsertGalleryFolder($gallery)
    {
        $root_id = (int)get_option('wpmf_folder_root_id');
        $terms = get_categories(array('hide_empty' => false, 'taxonomy' => WPMF_TAXO));
        $cats = array();
        foreach ($terms as $term) {
            if (!empty($term->term_id)) {
                $cats[] = $term->term_id;
            }
        }

        if (in_array($root_id, $cats)) {
            $key = array_search($root_id, $cats);
            unset($cats[$key]);
        }

        $args = array(
            'posts_per_page' => -1,
            'post_type' => 'attachment',
            'fields' => 'ids',
            'post_status' => 'any',
            'orderby' => isset($gallery['wpmf_orderby'])?$gallery['wpmf_orderby']:'post__in',
            'order' => isset($gallery['wpmf_order'])?$gallery['wpmf_order']:'ASC',
            'tax_query' => array(
                'relation' => 'OR',
                array(
                    'taxonomy' => WPMF_TAXO,
                    'field' => 'term_id',
                    'terms' => $cats,
                    'operator' => 'NOT IN'
                ),
                array(
                    'taxonomy' => WPMF_TAXO,
                    'field' => 'term_id',
                    'terms' => $root_id,
                    'operator' => 'IN'
                )
            ),
        );
        $query = new WP_Query($args);
        $allimg = $query->get_posts();
        return $allimg;
    }

    /**
     * Get all images id in current folder
     * @param array $gallery current gallery
     * @return array $allimg array new list image gallery
     */
    public function uploadUpdatePost($gallery)
    {
        $folder_ids = explode(',', $gallery['wpmf_folder_id']);
        $imgs_root = array();
        $img_subroot = array();
        foreach ($folder_ids as $folder_id) {
            if (isset($folder_id) && $folder_id != '') {
                if ($folder_id != 0) {
                    $args = array(
                        'posts_per_page' => -1,
                        'post_type' => 'attachment',
                        'fields' => 'ids',
                        'post_status' => 'any',
                        'orderby' => isset($gallery['wpmf_orderby'])?$gallery['wpmf_orderby']:'post__in',
                        'order' => isset($gallery['wpmf_order'])?$gallery['wpmf_order']:'ASC',
                        'tax_query' => array(
                            array(
                                'taxonomy' => WPMF_TAXO,
                                'field'    => 'term_id',
                                'terms'    => (int) $folder_id,
                                'include_children' => false
                            )
                        ),
                    );
                    $query = new WP_Query($args);
                    $imgs = $query->get_posts();
                    foreach ($imgs as $img) {
                        if (in_array($img, $img_subroot) == false) {
                            array_push($img_subroot, $img);
                        }
                    }
                } else {
                    $imgs_root = $this->autoInsertGalleryFolder($gallery);
                }
            }
        }

        $allimg = array_merge($img_subroot, $imgs_root);
        return $allimg;
    }

    /**
     * auto update gallery
     */
    public function updateGallery()
    {
        global $wpdb;
        // compatiple with fusion builder plugin
        if (is_plugin_active('fusion-builder/fusion-builder.php')) {
            global $shortcode_tags;
            foreach ($shortcode_tags as $tag => $shortcode) {
                if (strpos($tag, 'fusion_') !== false) {
                    remove_shortcode($tag);
                }
            }
        }
        $post_types = get_post_types(array('public' => true, 'exclude_from_search' => false));
        $sql = $wpdb->prepare(
            "SELECT ID,post_content,post_type FROM " . $wpdb->prefix . "posts WHERE post_content LIKE %s ",
            array('%wpmf_autoinsert="1"%')
        );

        $posts = $wpdb->get_results($sql);

        foreach ($posts as $post) {
            if (!empty($post_types) && !empty($post->post_type) && in_array($post->post_type, $post_types)) {
                $galleries = get_post_galleries($post->ID, false);
                foreach ($galleries as $gallery) {
                    $ids_old = 'ids="' . $gallery['ids'] . '"';
                    $ids_old_array = explode(',', $gallery['ids']);
                    if (isset($gallery['wpmf_folder_id']) && isset($gallery['wpmf_autoinsert'])
                        && $gallery['wpmf_autoinsert'] == 1) {
                        $allimages = $this->uploadUpdatePost($gallery);
                        $ids_new = 'ids="' . trim(implode(',', $allimages), ',') . '"';
                        if ($allimages != $ids_old_array) {
                            $post_content = str_replace($ids_old, $ids_new, $post->post_content);
                            wp_update_post(array('ID' => $post->ID, 'post_content' => $post_content));
                        }
                    }
                }
            }
        }
    }
}
