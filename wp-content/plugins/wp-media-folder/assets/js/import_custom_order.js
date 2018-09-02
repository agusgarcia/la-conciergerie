(function ($) {
    if (typeof ajaxurl === "undefined") {
        ajaxurl = wpmf.vars.ajaxurl;
    }

    var current_import_page = 0;
    $(document).ready(function () {
        /**
         * Import order
         * @param current_import_page
         */
        var wpmfImportOrder = function (current_import_page) {
            /* Ajax import */
            jQuery.ajax({
                type: 'POST',
                url: ajaxurl,
                data: {
                    action: "wpmf",
                    task: 'import_order',
                    current_import_page: current_import_page
                },
                success: function (res) {
                    if (!res.status) {
                        current_import_page++;
                        wpmfImportOrder(current_import_page);
                    }
                }
            });
        };

        wpmfImportOrder(current_import_page);
    });
}(jQuery));