<?php
/* Prohibit direct script loading */
defined('ABSPATH') || die('No direct script access allowed!');
?>
<div class="tab-header">
    <div class="wpmf-tabs">
        <div class="wpmf-tab-header active" data-label="wpmf-general"><?php _e('General', 'wpmf'); ?></div>
        <div class="wpmf-tab-header" data-label="wpmf-gallery"><?php _e('Gallery', 'wpmf'); ?></div>
        <div class="wpmf-tab-header" data-label="wpmf-media-access"><?php _e('Media access & design', 'wpmf'); ?></div>
        <div class="wpmf-tab-header" data-label="wpmf-files-folders"><?php _e('Files & Folders', 'wpmf'); ?></div>
        <?php if (current_user_can('install_plugins')) : ?>
            <div class="wpmf-tab-header" data-label="wpmf-ftp-import"><?php _e('FTP import', 'wpmf'); ?></div>
            <div class="wpmf-tab-header" data-label="wpmf-media-sync"><?php _e('Sync external media', 'wpmf'); ?></div>
        <?php endif; ?>
        <div class="wpmf-tab-header" data-label="wpmf-regen-thumbnail">
            <?php _e('Regenerate Thumbnails', 'wpmf'); ?></div>
        <div class="wpmf-tab-header" data-label="wpmf-image-compression">
            <?php _e('Image compression', 'wpmf'); ?></div>
        <?php if (is_plugin_active('wp-media-folder-addon/wp-media-folder-addon.php')) : ?>
            <div class="wpmf-tab-header" data-label="wpmf-google-drive"><?php _e('Google Drive', 'wpmf'); ?></div>
            <div class="wpmf-tab-header" data-label="wpmf-dropbox"><?php _e('Dropbox', 'wpmf'); ?></div>
            <div class="wpmf-tab-header" data-label="wpmf-onedrive"><?php _e('OneDrive', 'wpmf'); ?></div>
        <?php endif; ?>
        <div class="wpmf-tab-header" data-label="wpmf-jutranslation"><?php _e('Translation', 'wpmf'); ?></div>
    </div>
</div>