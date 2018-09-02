<?php
/* Prohibit direct script loading */
defined('ABSPATH') || die('No direct script access allowed!');
wp_enqueue_script('wpmf-gallery');
$class_default = array();
$class_default[] = "gallery gallery_default";
$class_default[] = "galleryid-{$id}";
$class_default[] = "gallery-columns-{$columns}";
$class_default[] = "gallery-size-{$size_class}";
$class_default[] = "gallery-link-{$link}";
$output = "<div id='$selector' class='" . implode(' ', $class_default) . "'>";
$i = 0;
$pos = 1;
foreach ($attachments as $id => $attachment) {
    $post_excerpt = htmlentities($attachment->post_excerpt);
    $link_target = get_post_meta($attachment->ID, '_gallery_link_target', true);
    if ($customlink) {
        $image_output = $this->getAttachmentLink($id, $size, false, $targetsize, $customlink, $link_target);
    } elseif (!empty($link) && 'file' === $link) {
        $image_output = $this->getAttachmentLink($id, $size, false, $targetsize, $customlink, $link_target);
    } elseif (!empty($link) && 'none' === $link) {
        if (get_post_meta($id, _WPMF_GALLERY_PREFIX . 'custom_image_link', true) != '') {
            $image_output = $this->getAttachmentLink($id, $size, false, $targetsize, $customlink, $link_target);
        } else {
            $image_output = wp_get_attachment_image($id, $size, false);
        }
    } else {
        $image_output = $this->getAttachmentLink($id, $size, true, 'large', false, $link_target);
    }

    $image_meta = wp_get_attachment_metadata($id);

    $orientation = '';
    if (isset($image_meta['height'], $image_meta['width'])) {
        $orientation = ($image_meta['height'] > $image_meta['width']) ? 'portrait' : 'landscape';
    }

    $width_item = 100 / $columns . '%';
    $output .= "<figure class='wpmf-gallery-item gallery-item' style='width:$width_item'>";
    $output .= "
				<div class='gallery-icon {$orientation}'>
					$image_output
				</div>";
    $caption_text = trim($post_excerpt);
    if (!empty($caption_text)) {
        $output .= "<figcaption class='wp-caption-text gallery-caption'>";
        $output .= wptexturize($caption_text);
        $output .= "</figcaption>";
    }
    $output .= "</figure>";
    $pos++;
}
$output .= "</div>";
