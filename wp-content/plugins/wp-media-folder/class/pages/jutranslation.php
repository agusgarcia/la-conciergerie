<?php
/* Prohibit direct script loading */
defined('ABSPATH') || die('No direct script access allowed!');
?>
<div class="content-box wpmf-config-jutranslation content-wpmf-jutranslation">
    <div id="wpmf-jutranslation-config" class="tab-pane ">
        <?php \Joomunited\WPMediaFolder\Jutranslation\Jutranslation::getInput(); ?>
    </div>
</div>