(function ($) {
    "use strict";
    $(document).ready(function () {
        /* open wordpress link dialog */
        $(document).on('click', '#link-btn', function () {
            if (typeof wpLink !== "undefined") {
                wpLink.open('link-btn');
                /* Bind to open link editor! */
                $('#wp-link-backdrop').show();
                $('#wp-link-wrap').show();
                $('#url-field, #wp-link-url').closest('div').find('span').html(wpmf.l18n.link_to);
                $('#link-title-field').closest('div').hide();
                $('.wp-link-text-field').hide();

                $('#url-field, #wp-link-url').val($('.compat-field-wpmf_gallery_custom_image_link input.text').val());
                if ($('.compat-field-gallery_link_target select').val() === '_blank') {
                    $('#link-target-checkbox,#wp-link-target').prop('checked', true);
                } else {
                    $('#link-target-checkbox,#wp-link-target').prop('checked', false);
                }
            }
        });

        /* Update link for file */
        $(document).on('click', '#wp-link-submit', function () {
            var attachment_id = $('.attachment-details').data('id');
            if (typeof attachment_id === "undefined") {
                attachment_id = $('#post_ID').val();
            }

            var link = $('#url-field').val();
            if (typeof link === "undefined") {
                link = $('#wp-link-url').val();
            }  // version 4.2+

            var link_target = $('#link-target-checkbox:checked').val();
            if (typeof link_target === "undefined") {
                link_target = $('#wp-link-target:checked').val();
            } // version 4.2+

            if (link_target === 'on') {
                link_target = '_blank';
            } else {
                link_target = '';
            }

            $.ajax({
                type: "POST",
                url: ajaxurl,
                data: {
                    action: "update_link",
                    id: attachment_id,
                    link: link,
                    link_target: link_target
                },
                success: function (response) {
                    $('.compat-field-wpmf_gallery_custom_image_link input.text').val(response.link);
                    $('.compat-field-gallery_link_target select option[value="' + response.target + '"]').prop('selected', true).change();
                }
            });
        });
    });
})(jQuery);