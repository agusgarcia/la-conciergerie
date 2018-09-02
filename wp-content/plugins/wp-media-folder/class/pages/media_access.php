<?php
/* Prohibit direct script loading */
defined('ABSPATH') || die('No direct script access allowed!');
?>
    <div class="content-box content-wpmf-media-access">
        <div class="cboption">
            <div class="wpmf_row_full">
                <input type="hidden" name="wpmf_active_media" value="0">
                <label data-alt="<?php _e('Once user upload some media, he will have a
             personal folder, can be per User or per User Role', 'wpmf'); ?>"
                       class="text"><?php _e('Media access by User or User Role', 'wpmf') ?></label>
                <div class="switch-optimization">
                    <label class="switch switch-optimization">
                        <input type="checkbox" name="wpmf_active_media"
                               id="cb_option_active_media" value="1"
                            <?php
                            if (isset($wpmf_active_media) && $wpmf_active_media == 1) {
                                echo 'checked';
                            }
                            ?>
                        >
                        <span class="slider round"></span>
                    </label>
                </div>
            </div>
            <div class="wpmf_row_full">
                <label data-alt="<?php _e('Automatically create a
             folder per User or per WordPress User Role', 'wpmf'); ?>"
                       class="text"><?php _e('Folder automatic creation', 'wpmf') ?></label>
                <label>
                    <select name="wpmf_create_folder">
                        <option
                        <?php selected($wpmf_create_folder, 'user'); ?> value="user">
                        <?php _e('By user', 'wpmf') ?>
                        </option>
                        <option
                        <?php selected($wpmf_create_folder, 'role'); ?> value="role">
                        <?php _e('By role', 'wpmf') ?>
                        </option>
                    </select>
                </label>
            </div>

            <div class="wpmf_row_full">
                <label data-alt="<?php _e('Select the root folder to store all user media and
             folders (only if Media by User or User Role is activated above)', 'wpmf'); ?>"
                       class="text"><?php _e('User media folder root', 'wpmf') ?></label>
            </div>
            <div class="wpmf_row_full">
                <span id="wpmfjaouser"></span>
            </div>

            <div class="wpmf_row_full">
                <input type="hidden" name="wpmf_option_singlefile" value="0">
                <label data-alt="<?php _e('Apply single file design with below
             parameters when insert file to post / page', 'wpmf'); ?>" class="text">
                    <?php _e('Enable single file design', 'wpmf') ?></label>
                <div class="switch-optimization">
                    <label class="switch switch-optimization">
                        <input type="checkbox" name="wpmf_option_singlefile"
                               value="1"
                            <?php
                            if (isset($option_singlefile) && $option_singlefile == 1) {
                                echo 'checked';
                            }
                            ?>
                        >
                        <span class="slider round"></span>
                    </label>
                </div>
            </div>
        </div>

        <div class="wpmf_group_color">
            <div class="wpmf_group_singlefile">
                <label class="control-label" for="singlebg"><?php _e('Background color', 'wpmf') ?></label>
                <label>
                    <input name="wpmf_color_singlefile[bgdownloadlink]" type="text"
                           value="<?php echo $wpmf_color_singlefile->bgdownloadlink ?>"
                           class="inputbox input-block-level wp-color-field-bg wp-color-picker">
                </label>
            </div>

            <div class="wpmf_group_singlefile">
                <label class="control-label" for="singlebg"><?php _e('Hover color', 'wpmf') ?></label>
                <label>
                    <input name="wpmf_color_singlefile[hvdownloadlink]" type="text"
                           value="<?php echo $wpmf_color_singlefile->hvdownloadlink ?>"
                           class="inputbox input-block-level wp-color-field-hv wp-color-picker">
                </label>
            </div>

            <div class="wpmf_group_singlefile">
                <label class="control-label" for="singlebg"><?php _e('Font color', 'wpmf') ?></label>
                <label>
                    <input name="wpmf_color_singlefile[fontdownloadlink]" type="text"
                           value="<?php echo $wpmf_color_singlefile->fontdownloadlink ?>"
                           class="inputbox input-block-level wp-color-field-font wp-color-picker">
                </label>
            </div>

            <div class="wpmf_group_singlefile">
                <label class="control-label" for="singlebg"><?php _e('Hover font color', 'wpmf') ?></label>
                <label>
                    <input name="wpmf_color_singlefile[hoverfontcolor]" type="text"
                           value="<?php echo $wpmf_color_singlefile->hoverfontcolor ?>"
                           class="inputbox input-block-level wp-color-field-hvfont wp-color-picker">
                </label>
            </div>
        </div>

        <div class="cboption">
            <div class="wpmf_row_full">
                <input type="hidden" name="wpmf_option_lightboximage" value="0">
                <label data-alt="<?php _e('Add a lightbox option on each image of your WordPress content', 'wpmf'); ?>"
                       class="text"><?php _e('Enable the single image lightbox feature', 'wpmf') ?></label>
                <div class="switch-optimization">
                    <label class="switch switch-optimization">
                        <input type="checkbox" name="wpmf_option_lightboximage"
                               id="cb_option_lightboximage" value="1"
                            <?php
                            if (isset($option_lightboximage) && $option_lightboximage == 1) {
                                echo 'checked';
                            }
                            ?>
                        >
                        <span class="slider round"></span>
                    </label>
                </div>
            </div>
        </div>
    </div>
<?php
wp_enqueue_style('wp-color-picker');
wp_enqueue_script('wp-color-picker');
?>