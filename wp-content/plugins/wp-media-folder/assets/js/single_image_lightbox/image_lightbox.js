(function ($) {
    $(document).ready(function () {
        var myEmbedImage = wp.media.view.ImageDetails;
        wp.media.view.ImageDetails = wp.media.view.ImageDetails.extend({
            initialize: function() {
                myEmbedImage.prototype.initialize.apply(this, arguments);
                this.on('post-render', this.add_settings);
            },
            // To add the Settings
            add_settings: function() {
                var $el = this.$el;
                $el.find('.embed-media-settings .column-settings .setting.link-to').after(wp.media.template('image-wpmf'));
                //this.controller.image.set({"data-settings": 'wpmf_size_lightbox'})
                $el.find('.wpmf_size_lightbox option[value="'+ this.controller.image.attributes.wpmf_size_lightbox +'"]').prop('selected',true).change();
            }
        });
    });
})(jQuery);