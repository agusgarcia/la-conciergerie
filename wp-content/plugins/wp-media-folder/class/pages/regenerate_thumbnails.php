<?php
/* Prohibit direct script loading */
defined('ABSPATH') || die('No direct script access allowed!');
?>
<div class="content-box content-wpmf-regen-thumbnail">
    <div class="btnoption">
        <h3><?php _e('Regenerate Thumbnails', 'wpmf'); ?></h3>
        <input type="button"
               class="btn waves-effect waves-light waves-input-wrapper btn_regenerate_thumbnails stop"
               data-paged="1" value="<?php _e('Regenerate all image thumbnails', 'wpmf') ?>">
        <input type="button"
               class="btn waves-effect waves-light waves-input-wrapper btn_stop_regenerate_thumbnails"
               value="<?php _e('Stop the process', 'wpmf') ?>">
        <div style="width: 100%;text-align: center;max-height:500px; overflow: hidden;">
            <img class="img_thumbnail" src="">
        </div>

        <div class="process_gennerate_thumb_full" style="">
            <div class="process_gennerate_thumb" data-w="0"></div>
        </div>

        <div class="result_gennerate_thumb"></div>
    </div>
</div>
