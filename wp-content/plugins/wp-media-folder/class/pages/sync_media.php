<?php
/* Prohibit direct script loading */
defined('ABSPATH') || die('No direct script access allowed!');
?>
<div class="content-box content-wpmf-media-sync">
    <div class="btnoption">
        <div class="wpmf_row_full">
            <input type="hidden" name="wpmf_option_sync_media" value="0">
            <label data-alt="<?php _e('Activate the sync from External folder to WordPress media library', 'wpmf') ?>"
                   class="text"><?php _e('Activate the sync', 'wpmf') ?></label>
            <div class="switch-optimization">
                <label class="switch switch-optimization">
                    <input type="checkbox" id="cb_option_sync_media"
                           name="wpmf_option_sync_media" value="1"
                        <?php
                        if (isset($option_sync_media) && $option_sync_media == 1) {
                            echo 'checked';
                        }
                        ?>
                    >
                    <div class="slider round"></div>
                </label>
            </div>
        </div>

        <div class="wpmf_row_full">
            <input type="hidden" name="wpmf_option_sync_media_external" value="0">
            <label data-alt="<?php _e('Also activate the sync from
             WordPress media library to external folders', 'wpmf') ?>"
                   class="text"><?php _e('Activate 2 ways sync', 'wpmf') ?></label>
            <div class="switch-optimization">
                <label class="switch switch-optimization">
                    <input type="checkbox" id="cb_option_sync_media_external"
                           name="wpmf_option_sync_media_external" value="1"
                        <?php
                        if (isset($sync_media_ex) && $sync_media_ex == 1) {
                            echo 'checked';
                        }
                        ?>
                    >
                    <div class="slider round"></div>
                </label>
            </div>
        </div>

        <div>
            <lable><?php _e('Sync delay', 'wpmf') ?></lable>
            <label>
                <input name="input_time_sync" class="input_time_sync" value="<?php echo $time_sync ?>">
            </label>
            <lable><?php _e('minutes', 'wpmf') ?></lable>
        </div>
        <hr>
        <div>
            <div class="wrap_dir_name_ftp">
                <div id="wpmf_foldertree_sync"></div>

            </div>

            <div class="wrap_dir_name_categories">
                <div id="wpmf_foldertree_categories"></div>

            </div>
        </div>
        <div class="time_sync" style="margin-top: 10px;">
            <div class="input_dir">
                <label>
                    <input type="text" name="dir_name_ftp" class="input_sync dir_name_ftp" readonly value="">
                </label>
                <label>
                    <input type="text" name="dir_name_categories" class="input_sync dir_name_categories" readonly
                           data-id_category="0" value="">
                </label>
            </div>

            <input type="button" class="button btn_addsync_media" value="<?php _e('Add', 'wpmf') ?>">
            <input type="button" class="button btn_deletesync_media" value="<?php _e('Delete selected', 'wpmf') ?>">
        </div>

        <table class="wp-list-table widefat striped wp-list-table-sync">
            <tr>
                <td style="width: 1%"><label for="cb-select-all"></label><input id="cb-select-all" type="checkbox"></td>
                <td style="width: 40%"><?php _e('Directory FTP', 'wpmf') ?></td>
                <td style="width: 40%"><?php _e('Folder category', 'wpmf') ?></td>
            </tr>
            <?php if (!empty($wpmf_list_sync_media)) : ?>
                <?php foreach ($wpmf_list_sync_media as $k => $v) : ?>
                    <tr data-id="<?php echo $k ?>">
                        <td>
                            <label for="cb-select-<?php echo $k ?>"></label>
                            <input id="cb-select-<?php echo $k ?>"
                                   type="checkbox" name="post[]" value="<?php echo $k ?>">
                        </td>
                        <td><?php echo $v['folder_ftp'] ?></td>
                        <td><?php echo @$this->breadcrumb_category[$k] ?></td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </table>
    </div>
</div>
