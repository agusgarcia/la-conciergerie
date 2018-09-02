(function ($) {
    if (typeof ajaxurl === "undefined") {
        ajaxurl = wpmf.vars.ajaxurl;
    }

    $(document).ready(function () {
        /**
         * Import size and filetype
         * @param button
         */
        var wpmfimport_meta_size = function (button) {
            var $this = jQuery(button);
            var wpmf_current_page = $this.data("page");
            $this.find(".spinner").show().css({"visibility": "visible"});
            /* Ajax import */
            jQuery.ajax({
                type: 'POST',
                url: ajaxurl,
                data: {
                    action: "wpmf_import_size_filetype",
                    wpmf_current_page: wpmf_current_page
                },
                success: function (res) {
                    if (!res.status) {
                        $this.data("page", parseInt(res.page) + 1);
                        $this.click();
                    } else {
                        $this.closest("div#wpmf_error").hide();
                    }
                }
            });
        };

        /* click import button */
        $('#wmpfImportsize').on('click', function () {
            wpmfimport_meta_size($(this));
        });
    });
}(jQuery));