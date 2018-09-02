<?php
/* Prohibit direct script loading */
defined('ABSPATH') || die('No direct script access allowed!');
wp_enqueue_script('jquery-masonry');
wp_enqueue_script('wpmf-gallery');
// getting rid of float
$class[] = "gallery-{$display}";
$class[] = "galleryid-{$id}";
$class[] = "gallery-columns-{$columns}";
$class[] = "gallery-size-{$size_class}";
$class[] = 'wpmf-gallery-bottomspace-' . $bottomspace;
$class[] = 'wpmf-gallery-clear';

$class = implode(' ', $class);

$padding_masonry = get_option('wpmf_padding_masonry');
if (!isset($padding_masonry) && $padding_masonry == '') {
    $padding_masonry = 5;
}
$output = "<div class='wpmf-gallerys'>";
$output .= "<div id='$selector'
 data-gutter-width='" . $padding_masonry . "'
  data-wpmfcolumns='" . $columns . "' class='{$class}'>";
$i = 0;
$pos = 1;


foreach ($attachments as $id => $attachment) {
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

    $output .= "<div class='wpmf-gallery-item
     wpmf-gallery-item-position-" . $pos . " wpmf-gallery-item-attachment-" . $id . "'>";
    $output .= "<div class='gallery-icon {$orientation}'>$image_output</div>";
    $output .= "</div>";
    $pos++;
}
$output .= "</div></div>\n";
