<?php
/* Prohibit direct script loading */
defined('ABSPATH') || die('No direct script access allowed!');
?>
<div class="content-box content-wpmf-files-folders">
    <div class="cboption">
        <div class="wpmf_row_full">
            <input type="hidden" name="wpmf_option_media_remove" value="0">
            <label data-alt="<?php _e('When you remove a folder all media inside will also be
             removed if this option is activated. Use with caution.', 'wpmf'); ?>"
                   class="text"><?php _e('Remove a folder with its media', 'wpmf') ?></label>
            <div class="switch-optimization">
                <label class="switch switch-optimization">
                    <input type="checkbox" id="cb_option_media_remove"
                           name="wpmf_option_media_remove" value="1"
                        <?php
                        if (isset($option_media_remove) && $option_media_remove == 1) {
                            echo 'checked';
                        }
                        ?>
                    >
                    <span class="slider round"></span>
                </label>
            </div>
        </div>
    </div>

    <div class="cboption">
        <div class="wpmf_row_full">
            <input type="hidden" name="wpmf_media_rename" value="0">
            <label data-alt="<?php _e('Tag available: {sitename} - {foldername} - {date} - {original name} .
             Note: # will be replaced by increasing numbers', 'wpmf') ?>"
                   class="text"><?php _e('Activate media rename on upload', 'wpmf') ?>
            </label>
            <div class="switch-optimization">
                <label class="switch switch-optimization">
                    <input type="checkbox" name="wpmf_media_rename" value="1"
                        <?php
                        if (isset($wpmf_media_rename) && $wpmf_media_rename == 1) {
                            echo 'checked';
                        }
                        ?>
                    >
                    <span class="slider round"></span>
                </label>
            </div>
        </div>
        <div class="wpmf_row_full">
            <label for="wpmf_patern"
                   data-alt="<?php _e('Tag avaiable: {sitename} - {foldername} - {date} - {original name} .
                    Note: # will be replaced by increasing numbers', 'wpmf') ?>">
                <?php _e('Pattern', 'wpmf') ?>
            </label>
            <input type="text" name="wpmf_patern"
                   id="wpmf_patern" class="regular-text" value="<?php echo $wpmf_pattern; ?>">
        </div>
    </div>

    <div class="cboption">
        <h3><?php _e('Format Media Titles', 'wpmf'); ?></h3>
        <div class="wpmf_row_full">
            <label data-alt="<?php _e('Remove characters automatically on media upload', 'wpmf'); ?>"
                   class="text"><?php _e('Remove Characters', 'wpmf') ?>
            </label>
            <div class="wrap_remove_character">
                <input type="hidden" name="wpmf_options_format_title[hyphen]" value="0">
                <div class="pure-checkbox">
                    <input id="wpmf_hyphen" type="checkbox" name="wpmf_options_format_title[hyphen]"
                        <?php checked($opts_format_title['hyphen'], 1) ?> value="1">
                    <label for="wpmf_hyphen"><?php _e('Hyphen', 'wpmf') ?> -</label>
                </div>

                <div class="pure-checkbox">
                    <input type="hidden" name="wpmf_options_format_title[underscore]" value="0">
                    <input id="wpmf_underscore" type="checkbox"
                           name="wpmf_options_format_title[underscore]"
                        <?php checked($opts_format_title['underscore'], 1) ?> value="1">
                    <label for="wpmf_underscore"><?php _e('Underscore', 'wpmf') ?> _</label>
                </div>

                <div class="pure-checkbox">
                    <input type="hidden" name="wpmf_options_format_title[period]" value="0">
                    <input id="wpmf_period" type="checkbox"
                           name="wpmf_options_format_title[period]"
                        <?php checked($opts_format_title['period'], 1) ?> value="1">
                    <label for="wpmf_period"><?php _e('Period', 'wpmf') ?> .</label>
                </div>

                <div class="pure-checkbox">
                    <input type="hidden" name="wpmf_options_format_title[tilde]" value="0">
                    <input id="wpmf_tilde" type="checkbox"
                           name="wpmf_options_format_title[tilde]"
                        <?php checked($opts_format_title['tilde'], 1) ?> value="1">
                    <label for="wpmf_tilde"><?php _e('Tilde', 'wpmf') ?> ~</label>
                </div>

                <div class="pure-checkbox">
                    <input type="hidden" name="wpmf_options_format_title[plus]" value="0">
                    <input id="wpmf_plus" type="checkbox"
                           name="wpmf_options_format_title[plus]"
                        <?php checked($opts_format_title['plus'], 1) ?> value="1">
                    <label for="wpmf_plus"><?php _e('Plus', 'wpmf') ?> +</label>
                </div>

                <div class="pure-checkbox">
                    <input type="hidden" name="wpmf_options_format_title[hash]" value="0">
                    <input id="wpmf_hash" type="checkbox"
                           name="wpmf_options_format_title[hash]"
                        <?php checked($opts_format_title['hash'], 1) ?> value="1">
                    <label for="wpmf_hash"><?php _e('Hash/pound', 'wpmf') ?> #</label>
                </div>

                <div class="pure-checkbox">
                    <input type="hidden" name="wpmf_options_format_title[ampersand]" value="0">
                    <input id="wpmf_ampersand" type="checkbox"
                           name="wpmf_options_format_title[ampersand]"
                        <?php checked($opts_format_title['ampersand'], 1) ?> value="1">
                    <label for="wpmf_ampersand"><?php _e('Ampersand', 'wpmf') ?> @</label>
                </div>

                <div class="pure-checkbox">
                    <input type="hidden" name="wpmf_options_format_title[number]" value="0">
                    <input id="wpmf_number" type="checkbox"
                           name="wpmf_options_format_title[number]"
                        <?php checked($opts_format_title['number'], 1) ?> value="1">
                    <label for="wpmf_number"><?php _e('All numbers', 'wpmf') ?> 0-9</label>
                </div>

                <div class="pure-checkbox">
                    <input type="hidden" name="wpmf_options_format_title[square_brackets]" value="0">
                    <input id="wpmf_square_brackets" type="checkbox"
                           name="wpmf_options_format_title[square_brackets]"
                        <?php checked($opts_format_title['square_brackets'], 1) ?> value="1">
                    <label for="wpmf_square_brackets"><?php _e('Square brackets', 'wpmf') ?> []</label>
                </div>

                <div class="pure-checkbox">
                    <input type="hidden" name="wpmf_options_format_title[round_brackets]" value="0">
                    <input id="wpmf_round_brackets" type="checkbox"
                           name="wpmf_options_format_title[round_brackets]"
                        <?php checked($opts_format_title['round_brackets'], 1) ?> value="1">
                    <label for="wpmf_round_brackets"><?php _e('Round brackets', 'wpmf') ?> ()</label>
                </div>

                <div class="pure-checkbox">
                    <input type="hidden" name="wpmf_options_format_title[curly_brackets]" value="0">
                    <input id="wpmf_curly_brackets" type="checkbox"
                           name="wpmf_options_format_title[curly_brackets]"
                        <?php checked($opts_format_title['curly_brackets'], 1) ?> value="1">
                    <label for="wpmf_curly_brackets"><?php _e('Curly brackets', 'wpmf') ?> {}</label>
                </div>
            </div>
        </div>

        <div class="wpmf_row_full">
            <label data-alt="<?php _e('Automatic media information completion on upload', 'wpmf'); ?>"
                   class="text"><?php _e('Other options', 'wpmf') ?>
            </label>
            <div class="wrap_remove_other_character">
                <div class="pure-checkbox">
                    <input type="hidden" name="wpmf_options_format_title[alt]" value="0">
                    <input id="wpmf_alt" type="checkbox"
                           name="wpmf_options_format_title[alt]"
                        <?php checked($opts_format_title['alt'], 1) ?> value="1">
                    <label for="wpmf_alt"><?php _e("Copy title to 'Alternative Text' Field?", "wpmf") ?> (-)</label>
                </div>

                <div class="pure-checkbox">
                    <input type="hidden" name="wpmf_options_format_title[caption]" value="0">
                    <input id="wpmf_caption" type="checkbox"
                           name="wpmf_options_format_title[caption]"
                        <?php checked($opts_format_title['caption'], 1) ?> value="1">
                    <label for="wpmf_caption"><?php _e("Copy title to 'Caption' Field?", "wpmf") ?> (_)</label>
                </div>

                <div class="pure-checkbox">
                    <input type="hidden" name="wpmf_options_format_title[description]" value="0">
                    <input id="wpmf_description" type="checkbox"
                           name="wpmf_options_format_title[description]"
                        <?php checked($opts_format_title['description'], 1) ?> value="1">
                    <label for="wpmf_description"><?php _e("Copy title to 'Description' Field?", "wpmf") ?> (.)</label>
                </div>
            </div>
        </div>

        <div class="wpmf_row_full">
            <label data-alt="<?php _e('Add capital letters automatically on media upload', 'wpmf'); ?>"
                   class="text"><?php _e('Automatic capitalization', 'wpmf') ?>
            </label>
            <div class="wpmf_rdo_cap">
                <label class="radio">
                    <input id="radio1" type="radio" name="wpmf_options_format_title[capita]" checked value="cap_all">
                    <span class="outer">
                        <span class="inner"></span>
                    </span>
                    <?php _e('Capitalize All Words', 'wpmf'); ?>
                </label>
                <label class="radio">
                    <input id="radio2" type="radio" name="wpmf_options_format_title[capita]"
                        <?php checked($opts_format_title['capita'], 'cap_first') ?> value="cap_first">
                    <span class="outer">
                        <span class="inner"></span>
                    </span>
                    <?php _e('Capitalize First Word Only', 'wpmf'); ?>
                </label>
                <label class="radio">
                    <input id="radio2" type="radio" name="wpmf_options_format_title[capita]"
                        <?php checked($opts_format_title['capita'], 'all_lower') ?> value="all_lower">
                    <span class="outer">
                        <span class="inner"></span>
                    </span>
                    <?php _e('All Words Lower Case', 'wpmf'); ?>
                </label>
                <label class="radio">
                    <input id="radio2" type="radio" name="wpmf_options_format_title[capita]"
                        <?php checked($opts_format_title['capita'], 'all_upper') ?> value="all_upper">
                    <span class="outer">
                        <span class="inner"></span>
                    </span>
                    <?php _e('All Words Upper Case', 'wpmf'); ?>
                </label>
                <label class="radio">
                    <input id="radio2" type="radio" name="wpmf_options_format_title[capita]"
                        <?php checked($opts_format_title['capita'], 'dont_alter') ?> value="dont_alter">
                    <span class="outer">
                        <span class="inner"></span>
                    </span>
                    <?php _e("Don't Alter (title text isn't modified in any way)", "wpmf"); ?>
                </label>
            </div>
        </div>
    </div>

    <div class="cboption">
        <h3><?php _e('Watermark', 'wpmf'); ?></h3>
        <div class="wpmf_row_full">
            <input type="hidden" name="wpmf_option_image_watermark" value="0">
            <label data-alt="<?php _e('Watermark will be applied only after saving the
             settings and regenerate the thumnails (hit the "regenerate thumnails" button)', 'wpmf'); ?>"
                   class="text"><?php _e('Images watermark', 'wpmf') ?></label>
            <div class="switch-optimization">
                <label class="switch switch-optimization">
                    <input type="checkbox" id="cb_option_image_watermark"
                           name="wpmf_option_image_watermark" value="1"
                        <?php
                        if (isset($option_image_watermark) && $option_image_watermark == 1) {
                            echo 'checked';
                        }
                        ?>
                    >
                    <div class="slider round"></div>
                </label>
            </div>
        </div>
    </div>

    <div class="cboption">
        <div class="wpmf_row_full">
            <label data-alt="<?php _e('Select an image', 'wpmf') ?>" class="text">
                <?php _e('Select an image', 'wpmf') ?>
            </label>
            <label for="wpmf_watermark_image"></label>
            <input type="text" readonly name="wpmf_watermark_image"
                   id="wpmf_watermark_image" class="regular-text" value="<?php echo $watermark_image; ?>">
            <input type="hidden" name="wpmf_watermark_image_id"
                   id="wpmf_watermark_image_id" class="regular-text" value="<?php echo $watermark_image_id; ?>">
            <div class="button wpmf_watermark_select_image"><?php _e('Select', 'wpmf') ?></div>
            <div class="button wpmf_watermark_clear_image"><?php _e('Clear', 'wpmf') ?></div>
        </div>
    </div>

    <div class="cboption">
        <div class="wpmf_row_full">
            <label data-alt="<?php _e('Watermark position', 'wpmf'); ?>" class="text">
                <?php _e('Watermark position', 'wpmf') ?>
            </label>
            <label>
                <select name="wpmf_watermark_position">
                    <option
                        <?php selected($watermark_position, 'center'); ?>
                        value="center"><?php _e('Center', 'wpmf') ?></option>
                    <option
                        <?php selected($watermark_position, 'bottom_left'); ?>
                        value="bottom_left"><?php _e('Bottom Left', 'wpmf') ?></option>
                    <option
                        <?php selected($watermark_position, 'bottom_right'); ?>
                        value="bottom_right"><?php _e('Bottom Right', 'wpmf') ?></option>
                    <option
                        <?php selected($watermark_position, 'top_right'); ?>
                        value="top_right"><?php _e('Top Right', 'wpmf') ?></option>
                    <option
                        <?php selected($watermark_position, 'top_left'); ?>
                        value="top_left"><?php _e('Top Left', 'wpmf') ?></option>
                </select>
            </label>
        </div>
    </div>

    <div class="cboption">
        <div class="wpmf_row_full">
            <label data-alt="<?php _e('Apply watermark on', 'wpmf'); ?>"
                   class="text"><?php _e('Apply watermark on', 'wpmf') ?></label>
            <div class="wrap_apply">
                <div class="pure-checkbox">
                    <input type="hidden" name="wpmf_image_watermark_apply[all_size]" value="0">
                    <input id="wpmf_watermark_position_all" type="checkbox"
                           name="wpmf_image_watermark_apply[all_size]"
                        <?php checked($watermark_apply['all_size'], 1) ?> value="1">
                    <label for="wpmf_watermark_position_all"><?php _e('All sizes', 'wpmf') ?></label>
                </div>

                <?php
                $sizes = apply_filters('image_size_names_choose', array(
                    'thumbnail' => __('Thumbnail', 'wpmf'),
                    'medium' => __('Medium', 'wpmf'),
                    'large' => __('Large', 'wpmf'),
                    'full' => __('Full Size', 'wpmf'),
                ));
                foreach ($sizes as $ksize => $vsize) :
                    ?>
                    <div class="pure-checkbox">
                        <input type="hidden" name="wpmf_image_watermark_apply[<?php echo $ksize ?>]" value="0">
                        <?php if (isset($watermark_apply[$ksize]) && $watermark_apply[$ksize] == 1) : ?>
                            <input id="wpmf_watermark_position_<?php echo $ksize ?>"
                                   type="checkbox" name="wpmf_image_watermark_apply[<?php echo $ksize ?>]"
                                   checked value="1">
                        <?php else : ?>
                            <input id="wpmf_watermark_position_<?php echo $ksize ?>"
                                   type="checkbox" name="wpmf_image_watermark_apply[<?php echo $ksize ?>]" value="1">
                        <?php endif; ?>
                        <label for="wpmf_watermark_position_<?php echo $ksize ?>"><?php echo $vsize ?></label>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

    <div class="cboption">
        <div class="wpmf_row_full">
            <label class="text"></label>
            <div class="button wpmf_watermark_regeneration"
                 data-paged="1"><?php _e('Thumbnail regeneration', 'wpmf') ?></div>
            <div class="process_watermark_thumb_full">
                <div class="process_watermark_thumb" data-w="0"></div>
            </div>
        </div>

    </div>
</div>