let wpmfDuplicateModule;
(function ($) {
    if (typeof ajaxurl === "undefined") {
        ajaxurl = wpmf.vars.ajaxurl;
    }

    wpmfDuplicateModule = {
        /**
         * init event
         */
        doEvent: function () {
            /* Click duplicate file button */
            $('.wpmf_btn_duplicate').unbind('click').bind('click', function () {
                $('.wpmf_spinner_duplicate').show().css('visibility', 'visible');
                $('.wpmf_message_duplicate').html(null);
                var id = $('.attachment-details').data('id');
                wpmfDuplicateModule.doDuplicate(id);
            });
        },

        /**
         * Duplicate attachment
         * @param id
         */
        doDuplicate: function (id) {
            if (typeof id !== 'undefined') {
                $.ajax({
                    method: 'post',
                    url: ajaxurl,
                    dataType: 'json',
                    data: {
                        action: 'wpmf_duplicate_file',
                        id: id
                    },
                    success: function (res) {
                        $('.wpmf_spinner_duplicate').hide();
                        if (res.status) {
                            $('.wpmf_message_duplicate').html('<div class="updated">' + res.message + '</div>');
                        } else {
                            $('.wpmf_message_duplicate').html('<div class="error">' + res.message + '</div>');
                        }

                        /* reset iframe after duplicate */
                        if (!$('body.upload-php table.media').length && wpmf.vars.wpmf_pagenow !== 'upload.php') {
                            setTimeout(function () {
                                wp.Uploader.queue.reset();
                            }, 1000);
                        }
                        wpmfFoldersModule.reloadAttachments();
                        wpmfFoldersModule.renderFolders();
                    }
                });
            }
        }
    };

    $(document).ready(function () {
        if ((wpmf.vars.wpmf_pagenow === 'upload.php' && !wpmfFoldersModule.page_type) || typeof wp.media === "undefined") {
            return;
        }
        if (wpmfFoldersModule.page_type !== 'upload-list') {
            /* base on /wp-includes/js/media-views.js */
            var myduplicateForm = wp.media.view.AttachmentsBrowser;
            var form_uplicate = '<a class="wpmf_btn_duplicate">' + wpmf.l18n.duplicate_text + '</a><span class="spinner wpmf_spinner_duplicate"></span><p class="wpmf_message_duplicate"></p>';
            wp.media.view.AttachmentsBrowser = wp.media.view.AttachmentsBrowser.extend({
                createSingle: function () {
                    /* Create duplicate button setting */
                    myduplicateForm.prototype.createSingle.apply(this, arguments);
                    var sidebar = this.sidebar;
                    if (wpmf.vars.wpmf_pagenow !== 'upload.php') {
                        if (typeof wpmf.vars.duplicate !== 'undefined' && parseInt(wpmf.vars.duplicate) === 1) {
                            $('.wpmf_btn_duplicate, .wpmf_spinner_duplicate, .wpmf_message_duplicate').remove();
                            $(sidebar.$el).find('.attachment-info .details').append(form_uplicate);
                            wpmfDuplicateModule.doEvent();
                        }
                    }
                }
            });

            /* Create duplicate button when wp smush plugin active*/
            if (wpmf.vars.get_plugin_active.indexOf('wp-smush.php') !== -1) {
                if( 'undefined' !== typeof wp.media.view &&
                    'undefined' !== typeof wp.media.view.Attachment.Details.TwoColumn ) {
                    // Local instance of the Attachment Details TwoColumn used in the edit attachment modal view
                    var wpmfAssignMediaTwoColumn = wp.media.view.Attachment.Details.TwoColumn;

                    /**
                     * Add Smush details to attachment.
                     */
                    wp.media.view.Attachment.Details.TwoColumn = wp.media.view.Attachment.Details.TwoColumn.extend({
                        render: function () {
                            // Get Smush status for the image
                            wpmfAssignMediaTwoColumn.prototype.render.apply(this);
                            $( document ).ajaxComplete(function( event, xhr, settings ) {
                                var data = settings.data;
                                if ( data.indexOf('smush_get_attachment_details') !== -1 ) {
                                    $('.wpmf_btn_duplicate, .wpmf_spinner_duplicate, .wpmf_message_duplicate').remove();
                                    $('.details').append(form_uplicate);
                                    wpmfDuplicateModule.doEvent();
                                }
                            });
                        }
                    });
                }
            }

            /* base on /wp-includes/js/media-views.js */
            var myDuplicate = wp.media.view.Modal;
            wp.media.view.Modal = wp.media.view.Modal.extend({
                open: function () {
                    /* Create duplicate button setting */
                    myDuplicate.prototype.open.apply(this, arguments);
                    if (wpmf.vars.wpmf_pagenow === 'upload.php') {
                        if (typeof wpmf.vars.duplicate !== 'undefined' && parseInt(wpmf.vars.duplicate) === 1) {
                            setTimeout(function(){
                                $('.wpmf_btn_duplicate, .wpmf_spinner_duplicate, .wpmf_message_duplicate').remove();
                                $('.attachment-details .details').append(form_uplicate);
                                wpmfDuplicateModule.doEvent();
                            },150);

                        }
                    }
                }
            });

            if (wpmf.vars.wpmf_pagenow === 'upload.php') {
                // create duplicate button when next and prev media items
                var myEditAttachments = wp.media.view.MediaFrame.EditAttachments;
                wp.media.view.MediaFrame.EditAttachments = wp.media.view.MediaFrame.EditAttachments.extend({
                    previousMediaItem: function () {
                        /* Create duplicate button setting */
                        myEditAttachments.prototype.previousMediaItem.apply(this, arguments);
                        if (typeof wpmf.vars.duplicate !== 'undefined' && parseInt(wpmf.vars.duplicate) === 1) {
                            $('.wpmf_btn_duplicate, .wpmf_spinner_duplicate, .wpmf_message_duplicate').remove();
                            $('.attachment-details .details').append(form_uplicate);
                            wpmfDuplicateModule.doEvent();

                        }
                    },

                    nextMediaItem: function () {
                        /* Create duplicate button setting */
                        myEditAttachments.prototype.nextMediaItem.apply(this, arguments);
                        if (typeof wpmf.vars.duplicate !== 'undefined' && parseInt(wpmf.vars.duplicate) === 1) {
                            $('.wpmf_btn_duplicate, .wpmf_spinner_duplicate, .wpmf_message_duplicate').remove();
                            $('.attachment-details .details').append(form_uplicate);
                            wpmfDuplicateModule.doEvent();
                        }
                    }
                });
            }
        }
    });
}(jQuery));