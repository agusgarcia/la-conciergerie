(function ($) {
    if (typeof ajaxurl === "undefined") {
        ajaxurl = wpmf.vars.ajaxurl;
    }
    $(document).ready(function () {
        if (typeof wp !== "undefined") {
            if (wp.media && $('body.upload-php table.media').length === 0) {
                if (wp.media.view.AttachmentFilters === undefined || wp.media.view.AttachmentsBrowser === undefined)
                    return;
                /* Create display own media filter */
                var wpmffilterDisplayMedia = function () {
                    //=========================================================================
                    wp.media.view.AttachmentFilters['wpmf_filter_display_media'] = wp.media.view.AttachmentFilters.extend({
                        className: 'wpmf-filter-display-media attachment-filters',
                        id: 'wpmf-display-media-filters',
                        createFilters: function () {
                            var filters = {};
                            filters['yes'] = {
                                text: 'Yes',
                                props: {
                                    wpmf_display_media: 'yes'
                                }
                            };

                            filters.all = {
                                text: 'No',
                                props: {
                                    wpmf_display_media: 'no'
                                },
                                priority: 10
                            };

                            this.filters = filters;
                        }
                    });

                    /* backup the method */
                    var orig = wp.media.view.AttachmentsBrowser;
                    /* render filter */
                    wp.media.view.AttachmentsBrowser = wp.media.view.AttachmentsBrowser.extend({
                        createToolbar: function () {
                            // call the original method
                            orig.prototype.createToolbar.apply(this, arguments);
                            this.toolbar.set('displaymediatags', new wp.media.view.AttachmentFilters['wpmf_filter_display_media']({
                                controller: this.controller,
                                model: this.collection.props,
                                priority: -80
                            }).render());
                        }
                    });

                    //=========================================================================
                };
                if (typeof wpmf.vars.wpmf_role !== 'undefined') {
                    if (wpmf.vars.wpmf_role === 'administrator') {
                        wpmffilterDisplayMedia();
                    }
                }

            } else {
                /* table page */
                if (typeof wpmf.l18n.no_media_label === "undefined")
                    wpmf.l18n.no_media_label = 'No';
                if (typeof wpmf.l18n.yes_media_label === "undefined")
                    wpmf.l18n.yes_media_label = 'Yes';
                var filter_displaymedia = '<select name="wpmf-display-media-filters" id="wpmf-display-media-filters" class="wpmf-filter-display-media attachment-filters">';
                filter_displaymedia += '<option value="all" selected>' + wpmf.l18n.no_media_label + '</option>';
                filter_displaymedia += '<option value="yes" selected>' + wpmf.l18n.yes_media_label + '</option>';
                filter_displaymedia += '</select>';
                $('.wpmf-categories').after(filter_displaymedia);
            }
        }
    });
}(jQuery));