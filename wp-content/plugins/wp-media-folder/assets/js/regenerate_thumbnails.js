(function ($) {
    $(document).ready(function ($) {
        /* click 'Regenerate all image thumbnails' button */
        $('.btn_regenerate_thumbnails').on('click', function () {
            $(this).hide();
            $('.btn_stop_regenerate_thumbnails').show();
            wpmf_regenthumbs($(this));
        });

        /* stop regenerate thumbnails */
        $('.btn_stop_regenerate_thumbnails').on('click', function () {
            $('.btn_regenerate_thumbnails').unbind('click');
            $(this).hide();
        });

        /**
         * Regenerate thumbnails
         * @param $this button
         */
        var wpmf_regenthumbs = function ($this) {
            $('.process_gennerate_thumb_full').show();
            $.ajax({
                type: 'POST',
                url: ajaxurl,
                data: {
                    action: "wpmf_regeneratethumbnail",
                    paged: $this.data('paged')
                },
                success: function (res) {
                    var w = $('.process_gennerate_thumb').data('w');
                    /* Check status and set progress bar */
                    if (res.status === 'ok') {
                        $('.process_gennerate_thumb').data('w', 0).css('width', '100%');
                        $('.btn_stop_regenerate_thumbnails').hide();
                        $('.btn_regenerate_thumbnails').show();
                    }
                    $this.data('paged', parseInt(res.paged) + 1);
                    /* Check status and set progress bar */
                    if (res.status === 'error_time') {
                        if (typeof res.percent !== "undefined") {
                            var new_w = parseFloat(w) + parseFloat(res.percent);
                            if (new_w > 100)
                                new_w = 100;
                            $('.process_gennerate_thumb_full').show();
                            $('.process_gennerate_thumb').data('w', new_w).css('width', new_w + '%');
                        }
                        $('.btn_regenerate_thumbnails').click();
                    }

                    if (typeof res.url !== "undefined" && typeof res.url[0] !== "undefined") {
                        $('.img_thumbnail').show().attr('src', res.url[0]);
                    }
                    $('.result_gennerate_thumb').append(res.success);
                }
            });
        };
    });
})(jQuery);