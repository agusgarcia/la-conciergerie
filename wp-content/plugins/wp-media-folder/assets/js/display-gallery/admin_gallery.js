(function ($) {
    "use strict";
    if ('undefined' === typeof (wp) || 'undefined' === typeof (wp.media)) {
        return;
    }

    var media = wp.media;
    var setTime;
    media.view.Settings.Gallery = media.view.Settings.Gallery.extend({
        render: function () {
            var $el = this.$el;
            var id_folder = wpmfFoldersModule.last_selected_folder;
            media.view.Settings.prototype.render.apply(this, arguments);
            $el.find('[data-setting="size"]').parent('label').remove();
            $el.find('[data-setting="link"]').parent('label').remove();
            $el.find('[data-setting="columns"]').parent('label').remove();
            $el.find('[data-setting="_orderbyRandom"]').parent('label').remove();
            $el.append(media.template('wpmf-gallery-settings'));

            media.gallery.defaults.display = 'default';
            media.gallery.defaults.targetsize = 'large';
            media.gallery.defaults.wpmf_folder_id = '';
            media.gallery.defaults.wpmf_autoinsert = '0';
            media.gallery.defaults.wpmf_orderby = 'post__in';
            media.gallery.defaults.wpmf_order = 'ASC';


            this.update.apply(this, ['link']);
            this.update.apply(this, ['columns']);
            this.update.apply(this, ['size']);
            this.update.apply(this, ['display']);
            this.update.apply(this, ['targetsize']);
            this.update.apply(this, ['wpmf_folder_id']);
            this.update.apply(this, ['wpmf_orderby']);
            this.update.apply(this, ['wpmf_order']);

            if (typeof id_folder !== "undefined") {
                var oldfIds = $el.find('.wpmf_folder_id').val();
                var oldfIds_array = oldfIds.split(",").map(Number);

                if (oldfIds !== '') {
                    if (oldfIds_array.indexOf(id_folder) < 0) {
                        $el.find('.wpmf_folder_id').val(oldfIds + ',' + id_folder).change();
                    }
                } else {
                    $el.find('.wpmf_folder_id').val(id_folder).change();
                }
            }

            this.update.apply(this, ['wpmf_autoinsert']);
            return this;
        }
    });

    /* when click Create a gallery from folder button */
    var selectallGallery = function () {
        $('.media-menu-item:nth-child(2)').click();
        var $li_attm = $('li.attachment:not(.wpmf-attachment)');
        $li_attm.find('.thumbnail').click();
        if ($('.button.media-button.button-primary.button-large.media-button-gallery').attr('disabled') === undefined) {
            $('.button.media-button.button-primary.button-large.media-button-gallery').click();
        }

        if ($li_attm.find('.thumbnail').length === 0) {
            setTime = setTimeout(function () {
                selectallGallery();
            }, 100);
        }
    };

    /* sort image gallery */
    $(document).on('change', '.wpmf_orderby', function (event) {
        $('.media-button-wpmf_reverse_gallery').click();
        if ($(this).val() === 'title' || $(this).val() === 'date') {
            $(this).closest('.attachments-browser').find('.media-button-reverse').hide();
        } else {
            $(this).closest('.attachments-browser').find('.media-button-reverse').show();
        }
    });

    /* sort image gallery */
    $(document).on('change', '.wpmf_order', function () {
        $('.media-button-wpmf_reverse_gallery').click();
    });

    /* when change category */
    $(document).on('change', '.wpmf-categories', function () {
        clearTimeout(setTime);
    });

    /* when click Create a gallery from folder button */
    $(document).on('click', 'a.btn-selectall,a.btn-selectall-gallery', function () {
        selectallGallery();
    });
})(jQuery);