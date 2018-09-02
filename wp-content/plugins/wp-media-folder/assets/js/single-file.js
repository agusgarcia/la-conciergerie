tinymce.PluginManager.add('wpmf_mce', function (editor) {
    editor.on('init', function () {
        editor.on('mousedown mouseup click touchend', function (event) {
            /* remove element in editor */
            jQuery('.mce-ico.mce-i-dashicon.dashicons-no').on('click', function () {
                if (event.target.nodeName !== "IMG" && jQuery(event.target).hasClass('wpmf_mce-single-child')) {
                    editor.dom.remove(jQuery(event.target).closest('.wpmf_mce-wrap'));
                }
            });
        });
    });
});