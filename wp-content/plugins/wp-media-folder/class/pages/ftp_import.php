<?php
/* Prohibit direct script loading */
defined('ABSPATH') || die('No direct script access allowed!');
?>
<div class="content-box content-wpmf-ftp-import">
    <div class="btnoption">
        <div id="wpmf_foldertree"></div>
        <div class="process_import_ftp_full" style="">
            <div class="process_import_ftp" data-w="0"></div>
        </div>
        <input type="button" id="import_button"
               name="import_folder" value="<?php _e('Import Folder', 'wpmf'); ?>"
               class="button" style="margin: 10px 0 10px 10px;">
        <span class="spinner" style="float: left;margin: 15px 10px 15px 6px;"></span>
        <span class="info_import"><?php _e('Imported !', 'wpmf'); ?></span>
    </div>
    <p style="margin-left:10px;" class="description">
        <?php _e('Import folder structure and media from your
         server in the standard WordPress media manager', 'wpmf'); ?>
        <br>7z,bz2,gz,rar,tgz,zip,csv,doc,docx,ods,odt,pdf,
        pps,ppt,pptx,rtf,txt,xls,xlsx,bmp,psd,tif,tiff,mid,
        mp3,mp4,ogg,wma,3gp,avi,flv,m4v,mkv,mov,mpeg,mpg,swf,vob,wmv
    </p>
</div>