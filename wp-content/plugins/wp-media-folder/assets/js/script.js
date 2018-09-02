'use strict';

var _typeof = typeof Symbol === "function" && typeof Symbol.iterator === "symbol" ? function (obj) { return typeof obj; } : function (obj) { return obj && typeof Symbol === "function" && obj.constructor === Symbol && obj !== Symbol.prototype ? "symbol" : typeof obj; };

/**
 * Main WP Media Folder script
 * It handles the categories filtering
 */
var wpmfFoldersModule = void 0;
(function ($) {
    wpmfFoldersModule = {
        taxonomy: null, // WPMF taxonomy
        categories_order: null, // Categories ids in order
        categories: null, // All categories objects
        media_root: null, // Id of media folder root category
        relation_category_filter: [], // Relation between categories variable and filter select
        relation_filter_category: [], // Relation between filter select content and category categories variable
        last_selected_folder: 0, // Last folder we moved into
        attachments_browser_initialized: false, // Is the attachment browser already initialized
        attachments_browser: null, // Variable used to store attachment browser reference to use it later
        dragging_elements: null, // Variable used to store elements while dragging files or folders
        hover_image: false, // Do we show or not the image on hover
        hover_images: [], // hover images
        global_search: false, // Do we search in all folder
        doing_global_search: false, // Save status of search
        folder_ordering: 'name-ASC', // Folder ordering
        page_type: null, // Current page type upload-list, upload-grid
        editFolderId: 0, // Current folder id to edit or delete ...
        editFileId: 0, // Current file id to edit
        folder_search: null,

        events: [], // event handling

        /**
         * Retrieve the current displayed frame
         */
        getFrame: function getFrame() {
            if (wpmfFoldersModule.page_type === 'upload-list') {
                // We're in the list mode
                return $('.upload-php #posts-filter');
            } else {
                return $('[id^="__wp-uploader-id-"]:visible div.media-frame');
            }
        },

        /**
         * Initialize module related things
         */
        initModule: function initModule() {
            // Retrieve values we'll use
            wpmfFoldersModule.taxonomy = wpmf.vars.taxo;
            wpmfFoldersModule.categories_order = wpmf.vars.wpmf_categories_order;
            wpmfFoldersModule.categories = wpmf.vars.wpmf_categories;
            wpmfFoldersModule.media_root = wpmf.vars.root_media_root;
            wpmfFoldersModule.folder_design = wpmf.vars.folder_design;
            wpmfFoldersModule.global_search = wpmf.vars.wpmf_search === '1';

            // Define the page type
            if (wpmf.vars.wpmf_pagenow === 'upload.php' && $('#posts-filter input[name="mode"][value="list"]').length && $('#posts-filter .media').length) {
                wpmfFoldersModule.page_type = 'upload-list';

                wpmfFoldersModule.folder_ordering = wpmf.vars.wpmf_order_f;
            } else if (wpmf.vars.wpmf_pagenow === 'upload.php' && $('#wp-media-grid').length) {
                wpmfFoldersModule.page_type = 'upload-grid';
            }

            if (wpmf.vars.option_hoverimg === 1) wpmfFoldersModule.hover_image = true;

            var init = function init() {
                var $current_frame = wpmfFoldersModule.getFrame();
                // get last access folder
                var lastAccessFolder = wpmfFoldersModule.getCookie('lastAccessFolder_' + wpmf.vars.site_url);
                if (wpmfFoldersModule.page_type !== 'upload-list') {
                    // Do not add WPMF when editing a gallery
                    if (wp.media.frame !== undefined && wp.media.frame._state === 'gallery-edit') {
                        wpmfFoldersModule.trigger('wpGalleryEdition');
                        return;
                    }
                }

                // Initialize select folder filter
                wpmfFoldersModule.initLoadingFolder();
                // Select the first item of folder filter
                if (wpmfFoldersModule.page_type !== 'upload-list') {
                    if (typeof lastAccessFolder === "undefined" || typeof lastAccessFolder !== "undefined" && lastAccessFolder === '' || typeof lastAccessFolder !== "undefined" && parseInt(lastAccessFolder) === 0 || typeof wpmfFoldersModule.categories[lastAccessFolder] === "undefined") {
                        $current_frame.find('#wpmf-media-category').val(wpmfFoldersModule.relation_category_filter[wpmfFoldersModule.last_selected_folder]).trigger('change');
                    } else {
                        $current_frame.find('#wpmf-media-category').val(wpmfFoldersModule.relation_category_filter[lastAccessFolder]).trigger('change');
                    }
                }

                // render context menu box
                if (wpmfFoldersModule.folder_design === 'material_design') {
                    wpmfFoldersModule.renderContextMenu();
                }

                // Add the breadcrumb
                if ($current_frame.find('#wpmf-breadcrumb').length === 0) {
                    if (wpmfFoldersModule.page_type !== 'upload-list') {
                        $current_frame.find('.attachments-browser ul.attachments').before('<ul id="wpmf-breadcrumb"></ul>');
                    } else {
                        $current_frame.find('.tablenav.top').before('<ul id="wpmf-breadcrumb"></ul>');
                    }
                }

                // Initialize some thing for listing page
                if (wpmfFoldersModule.page_type === 'upload-list') {
                    // Create folder container for list view
                    $current_frame.find('.tablenav.top').before('<ul class="attachments"></ul>');
                    if (typeof lastAccessFolder === "undefined" || typeof lastAccessFolder !== "undefined" && lastAccessFolder === '' || typeof lastAccessFolder !== "undefined" && parseInt(lastAccessFolder) === 0) {
                        wpmfFoldersModule.last_selected_folder = 0;
                    } else {
                        wpmfFoldersModule.last_selected_folder = lastAccessFolder;
                        $('#wpmf-media-category').val(wpmfFoldersModule.last_selected_folder);
                    }

                    // Change the upload href link to add current folder as parameter
                    $('.page-title-action').attr('href', $('.page-title-action').attr('href') + '?wpmf-folder=' + wpmfFoldersModule.last_selected_folder);
                }

                // Change the upload href link to add current folder as parameter
                var new_media_url = $('#menu-media').find('a[href="media-new.php"]').attr('href');
                $('#menu-media li a[href="media-new.php"]').attr('href', new_media_url + '?wpmf-folder=' + wpmfFoldersModule.last_selected_folder);

                // Initialize breadcrumb
                wpmfFoldersModule.updateBreadcrumb();

                // check if enable display own media
                if (parseInt(wpmf.vars.wpmf_active_media) === 1 && wpmf.vars.wpmf_role !== 'administrator' && wpmfFoldersModule.page_type !== 'upload-list' && wpmf.vars.term_root_id) {
                    // Finally render folders
                    wpmfFoldersModule.renderFolders(wpmf.vars.term_root_id);
                    // selected top folder if enable display own media
                    wpmfFoldersModule.getFrame().find('#wpmf-media-category option[value="0"]').prop('selected', true);
                } else {
                    if (typeof lastAccessFolder === "undefined" || typeof lastAccessFolder !== "undefined" && lastAccessFolder === '' || typeof lastAccessFolder !== "undefined" && parseInt(lastAccessFolder) === 0) {
                        // Finally render folders
                        wpmfFoldersModule.renderFolders();
                    } else {
                        // Finally render folders
                        if (typeof wpmfFoldersModule.categories[parseInt(lastAccessFolder)] !== "undefined") {
                            wpmfFoldersModule.renderFolders(parseInt(lastAccessFolder));
                        } else {
                            wpmfFoldersModule.renderFolders();
                        }
                    }
                }

                if (wpmfFoldersModule.page_type !== 'upload-list') {
                    // Attach event when something is added to the attachments list
                    var timeout = void 0;
                    // call drag folderempty attachment
                    wpmfFoldersModule.initializeDragAndDropAttachments();
                    // call open context menu when empty attachment
                    wpmfFoldersModule.openContextMenuFolder();
                    $current_frame.find('.attachments-browser ul.attachments').on("DOMNodeInserted", function () {

                        // Wait All DOMInserted events to be thrown before calling the initialization functions
                        window.clearTimeout(timeout);
                        timeout = window.setTimeout(function () {
                            // Attach drag and drop event to the attachments
                            wpmfFoldersModule.initializeDragAndDropAttachments();

                            // Hovering image intialization
                            wpmfFoldersModule.initHoverImage();

                            // open / close context menu box
                            if (wpmfFoldersModule.folder_design === 'material_design') {
                                wpmfFoldersModule.openContextMenuFolder();
                                wpmfFoldersModule.openContextMenuFile();
                            }
                        }, 300);
                    });

                    // Add the creation gallery from folder button
                    wpmfFoldersModule.addCreateGalleryBtn();
                } else {
                    // Attach drag and drop event to the attachments
                    wpmfFoldersModule.initializeDragAndDropAttachments();

                    // Hovering image intialization
                    wpmfFoldersModule.initHoverImage();

                    // open / close context menu box
                    if (wpmfFoldersModule.folder_design === 'material_design') {
                        wpmfFoldersModule.openContextMenuFolder();
                        wpmfFoldersModule.openContextMenuFile();
                    }
                }

                wpmfFoldersModule.trigger('ready', $current_frame);
            };

            if ($('.upload-php #posts-filter input[name="mode"][value="list"]').length) {
                // Initialize directly in list mode
                init();
            } else {
                // Initialize folders rendering when the attachment browser is ready
                if (typeof wp.media !== "undefined" && typeof wp.media.view !== "undefined") {
                    wp.media.view.AttachmentsBrowser.prototype.on('ready', function () {
                        init();
                    });
                }
            }

            if (wpmfFoldersModule.page_type !== 'upload-list') {
                // Extend uploader to send some POST datas with the uploaded file
                if (typeof wp.Uploader === "undefined") {
                    return;
                }

                $.extend(wp.Uploader.prototype, {
                    init: function init() {
                        // Add the current wpmf folder to the request
                        this.uploader.bind('BeforeUpload', function () {
                            this.settings.multipart_params['wpmf_folder'] = wpmfFoldersModule.last_selected_folder;
                        });

                        // Reload attachments so they can show up if we're inside a folder
                        this.uploader.bind('UploadComplete', function () {
                            wpmfFoldersModule.reloadAttachments();
                            wpmfFoldersModule.renderFolders();
                        });
                    }
                });

                // Extend attachment model to send extra info on saving attachment properties
                $.extend(wp.media.model.Attachment.prototype, {
                    parentSaveCompat: wp.media.model.Attachment.prototype.saveCompat, // Save original compat
                    saveCompat: function saveCompat() {
                        // Add current folder to the request parameters
                        arguments[0]['wpmf_folder'] = wpmfFoldersModule.last_selected_folder;
                        // Store post id
                        var post_id = this.id;
                        // Call original method
                        var ret = wp.media.model.Attachment.prototype.parentSaveCompat.apply(this, arguments);

                        if (arguments[0]['attachments[' + this.id + '][wpmf_field_bgfolder]'] === 'on') {
                            // The attachment is set as cover

                            // Wait the response from server
                            ret.then(function (data) {
                                // Retrieve thumbnail url if available
                                var image = void 0;
                                if (data.sizes.thumbnail !== undefined) {
                                    image = data.sizes.thumbnail.url;
                                } else {
                                    image = data.url;
                                }

                                // Initialize cover image as array is not already an array
                                if (wpmfFoldersModule.categories[wpmfFoldersModule.last_selected_folder].cover_image === undefined || wpmfFoldersModule.categories[wpmfFoldersModule.last_selected_folder].cover_image === '') {
                                    wpmfFoldersModule.categories[wpmfFoldersModule.last_selected_folder].cover_image = [];
                                }

                                // Save the new image as cover
                                wpmfFoldersModule.categories[wpmfFoldersModule.last_selected_folder].cover_image[0] = post_id;
                                wpmfFoldersModule.categories[wpmfFoldersModule.last_selected_folder].cover_image[1] = image;
                            });
                        } else if (wpmfFoldersModule.categories[wpmfFoldersModule.last_selected_folder].cover_image !== undefined && wpmfFoldersModule.categories[wpmfFoldersModule.last_selected_folder].cover_image[0] === post_id) {
                            // The attachment has been removed as cover

                            // Remove cover from category informations
                            wpmfFoldersModule.categories[wpmfFoldersModule.last_selected_folder].cover_image = '';
                        }

                        return ret;
                    }
                });

                // Initialize folders rendering on media modal events
                var myMediaViewModal = wp.media.view.Modal;
                wp.media.view.Modal = wp.media.view.Modal.extend({
                    open: function open() {
                        myMediaViewModal.prototype.open.apply(this, arguments);
                        if (typeof wp.media.frame !== "undefined") {
                            wp.media.frame.on('router:render:browse', function () {
                                init();
                            });
                            wp.media.frame.on('content:activate:browse', function () {
                                init();
                            });
                        } else {
                            wpmfFoldersModule.renderFolders();
                        }
                    }
                });

                // Hide create gallery button if attachments are selected
                if (typeof wpmf.vars.usegellery !== "undefined" && parseInt(wpmf.vars.usegellery) === 1) {
                    var _myMediaViewToolbar = wp.media.view.Toolbar;
                    wp.media.view.Toolbar = wp.media.view.Toolbar.extend({
                        refresh: function refresh() {
                            _myMediaViewToolbar.prototype.refresh.apply(this, arguments);
                            var state = this.controller.state(),
                                selection = state.get('selection');
                            if (typeof state !== "undefined" && typeof selection !== "undefined") {
                                if (selection.length === 0) {
                                    $('.btn-selectall,.btn-selectall-gallery').show();
                                    $('.media-button-gallery').hide();
                                } else {
                                    $('.btn-selectall,.btn-selectall-gallery').hide();
                                    $('.media-button-gallery').show();
                                }
                            }
                        }
                    });
                }

                var myMediaControllerCollectionEdit = wp.media.controller.CollectionEdit;
                wp.media.controller.CollectionEdit = wp.media.controller.CollectionEdit.extend({
                    activate: function activate() {
                        myMediaControllerCollectionEdit.prototype.activate.apply(this, arguments);
                    },
                    deactivate: function deactivate() {
                        myMediaControllerCollectionEdit.prototype.deactivate.apply(this, arguments);
                    }
                });

                // display folder on feature image
                var myMediaControllerFeaturedImage = wp.media.controller.FeaturedImage;
                wp.media.controller.FeaturedImage = wp.media.controller.FeaturedImage.extend({
                    updateSelection: function updateSelection() {
                        myMediaControllerFeaturedImage.prototype.updateSelection.apply(this, arguments);
                        wpmfFoldersModule.renderFolders();
                    }
                });

                // Create and initialize select filter used to filter by folder
                wpmfFoldersModule.initFolderFilter();

                // Add button to the uploader content page
                var myMediaViewToolbar = wp.media.view.UploaderInline;
                wp.media.view.UploaderInline = wp.media.view.UploaderInline.extend({
                    ready: function ready() {
                        myMediaViewToolbar.prototype.ready.apply(this, arguments);
                        // Add remote video button
                        if (!this.$el.find('.wpmf_btn_remote_video').length) {
                            this.$el.find('.upload-ui button').after('<button href="#" class="wpmf_btn_remote_video button button-hero">' + wpmf.l18n.remote_video + '</button>');
                            wpmfFoldersModule.initRemoteVideo();
                        }
                    }
                });

                // Manage reset iframe
                wp.Uploader.queue.on('reset', function () {
                    // remove attachment loading
                    $('.attachment.loading').remove();
                });

                // Manage adding an uploaded file
                wp.Uploader.queue.on('add', function (file_info) {
                    if (parseInt(wpmf.vars.wpmf_post_type) !== 1 || !$('#wpb_visual_composer').is(":visible")) {
                        // Show snackbar
                        wpmfSnackbarModule.show({
                            id: file_info.attributes.file.id,
                            content: wpmf.l18n.wpmf_fileupload,
                            auto_close: false,
                            is_progress: true
                        });

                        // Create the download attachment
                        wpmfFoldersModule.getFrame().find('.attachments-browser .attachments').prepend('<li data-cid="' + file_info.attributes.file.id + '" class="attachment loading"><div class="attachment-preview js--select-attachment type-image subtype-jpeg portrait"><div class="thumbnail"><div class="media-progress-bar"><div style="width:0"></div></div></div></div></li>');
                    }
                });

                // Get upload progress infos
                var myMediaUploaderStatus = wp.media.view.UploaderStatus;
                wp.media.view.UploaderStatus = wp.media.view.UploaderStatus.extend({
                    progress: function progress(file_info) {
                        // Call parent function
                        myMediaUploaderStatus.prototype.progress.apply(this, arguments);

                        // This is not a uploading update
                        if (file_info === undefined || file_info.changed === undefined || file_info.changed.percent === undefined) {
                            return;
                        }

                        // Retrieve snackbar from its id
                        var $snack = wpmfSnackbarModule.getFromId(file_info.attributes.file.id);

                        // Is the upload finished
                        if (file_info.changed.percent === 100) {
                            wpmfSnackbarModule.close($snack);
                            wpmfSnackbarModule.show({
                                content: wpmf.l18n.wpmf_media_uploaded
                            });
                        } else {
                            wpmfSnackbarModule.setProgress($snack, file_info.changed.percent);
                        }

                        // Update the uploaded percentage for this file
                        $('li.attachment[data-cid=' + file_info.attributes.file.id + '] .media-progress-bar > div').css({ 'width': file_info.changed.percent + '%' });
                    },
                    error: function error(_error) {
                        if (_error.get('message') === wpmf.l18n.error_replace) {
                            $('.upload-errors').addClass('wpmferror_replace');
                            wp.Uploader.queue.reset();
                        }
                        myMediaUploaderStatus.prototype.error.apply(this, arguments);
                    }
                });
            }

            wpmfFoldersModule.trigger('afterFiltersInitialization');
        },

        /**
         * Create the folder/taxonomy filtering
         */
        initFolderFilter: function initFolderFilter() {
            /**
             * We extend the AttachmentFilters view to add our own filtering
             */
            wp.media.view.AttachmentFilters['wpmf_categories'] = wp.media.view.AttachmentFilters.extend({
                className: 'wpmf-media-categories attachment-filters',
                id: 'wpmf-media-category',
                createFilters: function createFilters() {
                    var filters = {};
                    var ij = 0;
                    var space = '&nbsp;&nbsp;';
                    _.each(wpmfFoldersModule.categories_order || [], function (key) {
                        var term = wpmfFoldersModule.categories[key];
                        if (typeof term !== "undefined") {
                            if (wpmfFoldersModule.media_root !== term.id) {
                                var query = {
                                    taxonomy: wpmfFoldersModule.taxonomy,
                                    term_id: parseInt(term.id, 10),
                                    term_slug: term.slug,
                                    wpmf_taxonomy: 'true'
                                };

                                if (typeof term.depth === 'undefined') {
                                    term.depth = 0;
                                }

                                filters[ij] = {
                                    text: space.repeat(term.depth) + term.label,
                                    props: query
                                };

                                wpmfFoldersModule.relation_category_filter[term.id] = ij;
                                wpmfFoldersModule.relation_filter_category[ij] = term.id;
                                ij++;
                            }
                        }
                    });

                    this.filters = filters;
                }
            });

            // render filter
            var myAttachmentsBrowser = wp.media.view.AttachmentsBrowser;
            wp.media.view.AttachmentsBrowser = wp.media.view.AttachmentsBrowser.extend({

                createToolbar: function createToolbar() {
                    wp.media.model.Query.defaultArgs.filterSource = 'filter-attachment-category';
                    myAttachmentsBrowser.prototype.createToolbar.apply(this, arguments);
                    //Save the attachments because we'll need it to change the category filter
                    wpmfFoldersModule.attachments_browser = this;

                    this.toolbar.set(wpmfFoldersModule.taxonomy, new wp.media.view.AttachmentFilters['wpmf_categories']({
                        controller: this.controller,
                        model: this.collection.props,
                        priority: -75
                    }).render());
                },

                // Add video icon for each remote video attachment
                updateContent: function updateContent() {
                    myAttachmentsBrowser.prototype.updateContent.apply(this, arguments);
                    wpmfFoldersModule.getFrame().find('.attachments-browser .attachment').each(function (i, v) {
                        var id_img = $(v).data('id');
                        if (wp.media.attachment(id_img).get('description') === 'wpmf_remote_video') {
                            if ($('li.attachment[data-id="' + id_img + '"] .attachment-preview .wpmf_remote_video').length === 0) {
                                $('li.attachment[data-id="' + id_img + '"] .attachment-preview').append('<i class="material-icons wpmf_remote_video">play_circle_filled</i>');
                            }
                        }
                    });
                }
            });

            // If the filter has already been rendered, force it to be reloaded
            if (wpmfFoldersModule.attachments_browser !== null) {
                // Remove previous filter
                wpmfFoldersModule.getFrame().find('#wpmf-media-category').remove();

                // Regenerate filter
                wpmfFoldersModule.attachments_browser.toolbar.set(wpmfFoldersModule.taxo, new wp.media.view.AttachmentFilters['wpmf_categories']({
                    controller: wpmfFoldersModule.attachments_browser.controller,
                    model: wpmfFoldersModule.attachments_browser.collection.props,
                    priority: -75
                }).render());
                wpmfFoldersModule.initLoadingFolder();
            }

            // order image gallery
            var myMediaControllerGalleryEdit = wp.media.controller.GalleryEdit;
            wp.media.controller.GalleryEdit = wp.media.controller.GalleryEdit.extend({
                gallerySettings: function gallerySettings(browser) {
                    // Apply original method
                    myMediaControllerGalleryEdit.prototype.gallerySettings.apply(this, arguments);
                    var library = this.get('library');
                    browser.toolbar.set('wpmf_reverse_gallery', {
                        text: 'Order by',
                        priority: 70,
                        click: function click() {
                            /* Sort images gallery by setting */
                            var lists_i = library.toArray();
                            var listsId = [];
                            var wpmf_orderby = $('.wpmf_orderby').val();
                            var wpmf_order = $('.wpmf_order').val();
                            $.each(lists_i, function (i, v) {
                                listsId.push(v.id);
                            });

                            var wpmf_img_order = [];
                            $.ajax({
                                method: "POST",
                                dataType: 'json',
                                url: ajaxurl,
                                data: {
                                    action: "wpmf",
                                    ids: listsId,
                                    wpmf_orderby: wpmf_orderby,
                                    wpmf_order: wpmf_order,
                                    task: "gallery_get_image"
                                },
                                success: function success(res) {
                                    if (res !== false) {
                                        $.each(res, function (i, v) {
                                            $.each(lists_i, function (k, h) {
                                                if (h.id === v.ID) wpmf_img_order.push(h);
                                            });
                                        });

                                        library.reset(wpmf_img_order);
                                    }
                                }
                            });
                        }
                    });
                }
            });

            // Reload folders after searching
            var mySearch = wp.media.view.Search;
            var search_initialized = false;
            wp.media.view.Search = wp.media.view.Search.extend({
                search: function search() {
                    // Apply original method
                    mySearch.prototype.search.apply(this, arguments);

                    // Save as a global variable is we're currently doing a global search or not
                    wpmfFoldersModule.doing_global_search = !!(event.target.value && wpmfFoldersModule.global_search);

                    // Register on change event if not already done
                    if (!search_initialized) {
                        this.model.on('change', function () {
                            wpmfFoldersModule.renderFolders();
                        });

                        // Prevent to register the function on the event each time search is called
                        search_initialized = true;
                    }
                }
            });
        },

        setFolderOrdering: function setFolderOrdering(ordering) {
            wpmfFoldersModule.folder_ordering = ordering;

            // Rerender folders
            wpmfFoldersModule.renderFolders();
        },

        /**
         * Force attachments to be reloaded in the current view
         */
        reloadAttachments: function reloadAttachments() {
            // Force reloading files
            if (typeof wp.media.frame !== "undefined") {
                if (wp.media.frame.library !== undefined) {
                    wp.media.frame.library.props.set({ ignore: +new Date() });
                    if (wpmf.vars.wpmf_pagenow === 'upload.php') {
                        if (typeof wpmf_move_fi !== "undefined") {
                            wpmf_move_fi.controller.state().get('selection').reset();
                        }
                    }
                } else if (wp.media.frame.content.get() !== null && wp.media.frame.content.get().collection !== undefined) {
                    wp.media.frame.content.get().collection.props.set({ ignore: +new Date() });
                    wp.media.frame.content.get().options.selection.reset();
                } else {
                    // Nothing to do attachments have not been already loaded
                }
            } else {
                $('.wpmf-snackbar').remove();
            }
        },

        /**
         * Initialize the events on which the folders should be reloaded
         */
        initLoadingFolder: function initLoadingFolder() {
            wpmfFoldersModule.getFrame().find('#wpmf-media-category').on('change', function () {
                if (wpmfFoldersModule.page_type === 'upload-list') {
                    // In list view submit filter form
                    $('.upload-php #posts-filter').submit();
                } else {
                    wpmfFoldersModule.renderFolders(wpmfFoldersModule.relation_filter_category[$(this).val()]);
                    wpmfFoldersModule.updateBreadcrumb(wpmfFoldersModule.relation_filter_category[$(this).val()]);

                    // Trigger change changeFolder event for other modules
                    wpmfFoldersModule.trigger('changeFolder', wpmfFoldersModule.relation_filter_category[$(this).val()]);
                }
            });
        },

        /**
         * set a cookie
         * @param cname cookie name
         * @param cvalue cookie value
         * @param exdays
         */
        setCookie: function setCookie(cname, cvalue, exdays) {
            var d = new Date();
            d.setTime(d.getTime() + exdays * 24 * 60 * 60 * 1000);
            var expires = "expires=" + d.toUTCString();
            document.cookie = cname + "=" + cvalue + ";" + expires + ";path=/";
        },

        /**
         * get a cookie
         * @param cname cookie name
         * @returns {*}
         */
        getCookie: function getCookie(cname) {
            var name = cname + "=";
            var ca = document.cookie.split(';');
            for (var i = 0; i < ca.length; i++) {
                var c = ca[i];
                while (c.charAt(0) === ' ') {
                    c = c.substring(1);
                }
                if (c.indexOf(name) === 0) {
                    return c.substring(name.length, c.length);
                }
            }
            return "";
        },

        /**
         * Move into the term_id folder
         * It will change the selected option in the filter
         * This will update the attachments and render the folders
         *
         * @param term_id
         */
        changeFolder: function changeFolder(term_id) {
            // set cookie last access folder
            if (typeof term_id === "undefined") {
                wpmfFoldersModule.setCookie('lastAccessFolder_' + wpmf.vars.site_url, 0, 365);
            } else {
                wpmfFoldersModule.setCookie('lastAccessFolder_' + wpmf.vars.site_url, term_id, 365);
            }

            // Select the filter folder
            if (wpmfFoldersModule.page_type === 'upload-list') {
                wpmfFoldersModule.getFrame().find('#wpmf-media-category').val(term_id).trigger('change');
            } else {
                // set value folder id auto insert image gallery
                wpmfFoldersModule.getFrame().find('#wpmf-media-category').val(wpmfFoldersModule.relation_category_filter[term_id]).trigger('change');
            }
        },

        /**
         * Generate the html tag for a folder attachment
         *
         * @param type string type of folder
         * @param name string folder name
         * @param term_id int folder term id
         * @param parent_id int folder parent id
         * @param cover_image string cover image url
         *
         * @return {string} the string that contains the single folder attachment rendered
         */
        getFolderRendering: function getFolderRendering(type, name, term_id, parent_id, cover_image) {
            var buttons = '';
            var class_names = '';
            var main_icon = '';
            var action = '';
            var cover = '';

            if (type === 'folder') {
                // This is a folder
                buttons = '<span class="dashicons dashicons-edit" onclick="wpmfFoldersModule.clickEditFolder(event, ' + term_id + ')"></span>\n                            <span class="dashicons dashicons-trash" onclick="wpmfFoldersModule.clickDeleteFolder(event, ' + term_id + ')"></span>';
                class_names = 'wpmf-folder';
                action = 'onclick="wpmfFoldersModule.changeFolder(' + term_id + ');"';
                if ((typeof cover_image === 'undefined' ? 'undefined' : _typeof(cover_image)) === 'object' && wpmfFoldersModule.folder_design === 'classic') {
                    cover = '<img src="' + cover_image[1] + '" />';
                    main_icon = '';
                } else {
                    main_icon = '<i class="material-icons wpmf-icon-category">folder</i>';
                }
            } else if (type === 'back') {
                // This is a back folder
                class_names = 'wpmf-folder wpmf-back';
                main_icon = '<span class="dashicons dashicons-arrow-left-alt2"></span>';
                action = 'onclick="wpmfFoldersModule.changeFolder(' + term_id + ');"';
            } else if (type === 'new') {
                // This is a create new folder button
                class_names = 'wpmf-new';
                main_icon = '<i class="material-icons wpmf-icon-category">create_new_folder</i>';
                action = 'onclick="wpmfFoldersModule.newFolder(' + term_id + ');"';
            } else if (type === 'line break') {
                class_names = 'wpmf-line-break';
            }

            // check if enable display own media
            if (parseInt(wpmf.vars.wpmf_active_media) === 1 && wpmf.vars.wpmf_role !== 'administrator') {
                if (type === 'back' && parent_id === 0) {
                    return '';
                }
            }

            if (wpmfFoldersModule.folder_design === 'classic') {
                return '<li\n                    class="wpmf-attachment attachment ' + wpmfFoldersModule.folder_design + ' ' + class_names + '" \n                    data-parent_id="' + parent_id + '" \n                    data-id="' + term_id + '"\n                    ' + action + '\n                >\n                    <div class="wpmf-attachment-preview attachment-preview">\n                        <div class="thumbnail">\n                        \n                            ' + buttons + '\n                            \n                            ' + main_icon + '\n                            \n                            <div class="centered">' + cover + '</div>\n                            <div class="filename">\n                                <div>' + name + '</div>\n                            </div>\n                        </div>\n                    </div>\n                </li>';
            } else {
                // get color folder
                if (typeof wpmf.vars.colors !== 'undefined' && typeof wpmf.vars.colors[term_id] !== 'undefined' && type === 'folder') {
                    var bgcolor = 'color: ' + wpmf.vars.colors[term_id];
                } else {
                    bgcolor = '#8f8f8f';
                }
                return '<li class="mdc-list-item attachment wpmf-attachment ' + wpmfFoldersModule.folder_design + ' ' + class_names + ' mdc-ripple-upgraded"\n                    data-parent_id="' + parent_id + '" \n                    data-id="' + term_id + '"\n                    ' + action + '\n                >\n            <span class="mdc-list-item__start-detail white-bg" role="presentation" style="' + bgcolor + '">\n              ' + main_icon + '\n            </span>\n                <span class="mdc-list-item__text" title="' + name + '">\n              ' + name + '\n            </span>\n            </li>';
            }
        },

        /**
         * Render the folders to the attachments listing
         *
         * @param term_id
         */
        renderFolders: function renderFolders(term_id) {
            if (wpmfFoldersModule.doing_global_search) {
                // If we're currently doing a global search, we do not show folders
                return;
            }

            if (term_id === undefined) {
                // check if enable display own media
                if (parseInt(wpmf.vars.wpmf_active_media) === 1 && wpmf.vars.wpmf_role !== 'administrator' && wpmfFoldersModule.page_type !== 'upload-list' && wpmf.vars.term_root_id) {
                    // If not term id is set we use the latest used
                    term_id = wpmf.vars.term_root_id;
                } else {
                    // If not term id is set we use the latest used
                    term_id = wpmfFoldersModule.last_selected_folder;
                }
            } else {
                // Let's save this term as the last used one
                wpmfFoldersModule.last_selected_folder = term_id;
            }

            // Retrieve current frame
            var $frame = wpmfFoldersModule.getFrame();

            // Retrieve the attachments container
            var $attachments_container = void 0;
            if (wpmfFoldersModule.page_type === 'upload-list') {
                $attachments_container = $frame.find('ul.attachments');
            } else {
                $attachments_container = $frame.find('.attachments-browser ul.attachments');
            }

            // Remove previous folders
            $attachments_container.find('.wpmf-attachment').remove();

            // Retrieve the folders that may be added to current view
            var folders_ordered = [];
            // get search keyword
            var search = $('.wpmf_search_folder').val();
            wpmfFoldersModule.folder_search = [];
            var folder_search = wpmfFoldersModule.folder_search;
            if (typeof search === "undefined") {
                search = '';
            }

            for (var folder_id in wpmfFoldersModule.categories) {
                if (search === '') {
                    if (wpmfFoldersModule.categories[folder_id].id !== 0 && // We don't show the root folder
                    wpmfFoldersModule.categories[folder_id].parent_id === term_id // We only show folders of the current parent
                    ) {
                            folders_ordered.push(wpmfFoldersModule.categories[folder_id]);
                        }
                } else {
                    var folder_name = wpmfFoldersModule.categories[folder_id].label;
                    // check folder name with search keyword
                    if (folder_name.indexOf(search) !== -1 && wpmfFoldersModule.categories[folder_id].parent_id === term_id && wpmfFoldersModule.categories[folder_id].id !== 0) {
                        folders_ordered.push(wpmfFoldersModule.categories[folder_id]);
                    }

                    if (folder_name.indexOf(search) !== -1) {
                        folder_search.push(folder_id);
                    }
                }
            }

            // Order folders
            switch (wpmfFoldersModule.folder_ordering) {
                default:
                case 'name-ASC':
                    folders_ordered = folders_ordered.sort(function (a, b) {
                        return a.label.localeCompare(b.label);
                    });
                    break;
                case 'name-DESC':
                    folders_ordered = folders_ordered.sort(function (a, b) {
                        return b.label.localeCompare(a.label);
                    });
                    break;
                case 'id-ASC':
                    folders_ordered = folders_ordered.sort(function (a, b) {
                        return a.id - b.id;
                    });
                    break;
                case 'id-DESC':
                    folders_ordered = folders_ordered.sort(function (a, b) {
                        return b.id - a.id;
                    });
                    break;
                case 'custom':
                    folders_ordered = folders_ordered.sort(function (a, b) {
                        return a.order - b.order;
                    });
                    break;
            }

            // Add each folder to the attachments listing
            $(folders_ordered).each(function () {
                // Get the formatted folder for the attachment listing
                var folder = wpmfFoldersModule.getFolderRendering('folder', this.label, this.id, this.parent_id, this.cover_image);

                // Add the folder to the attachment listing
                $attachments_container.append(folder);
            });

            // Get the formatted new button
            var folder = wpmfFoldersModule.getFolderRendering('new', wpmf.l18n['create_folder'], term_id, '', '');

            // Add the new folder button to the attachment listing
            $attachments_container.prepend(folder);

            // Check if we're not on the top folder
            if (wpmfFoldersModule.categories[term_id].id !== 0) {
                // Get the formatted back button
                var _folder = wpmfFoldersModule.getFolderRendering('back', wpmf.l18n['back'], wpmfFoldersModule.categories[term_id].parent_id, wpmfFoldersModule.categories[term_id].parent_id, wpmfFoldersModule.categories[term_id].cover_image);

                // Add the back button to the attachment listing
                $attachments_container.prepend(_folder);
            }

            // Get the formatted folder to use as a line break
            var line_break = wpmfFoldersModule.getFolderRendering('line break', '', '', '', '');

            // Add the folder to the attachment listing
            $attachments_container.append(line_break);
        },

        /**
         * Set status folder color
         */
        appendCheckColor: function appendCheckColor() {
            $('.color-wrapper .color .color_check:not(.custom_color .color_check)').remove();
            $('.color-wrapper .color[data-color="' + wpmf.vars.colors[wpmfFoldersModule.editFolderId] + '"]').append('<i class="material-icons color_check">done</i>');
        },
        /**
         * right click on folder to open menu
         */
        openContextMenuFolder: function openContextMenuFolder() {
            // init context menu on folders
            $('.wpmf-attachment').bind('contextmenu', function (e) {
                if ($(this).hasClass('wpmf-folder') && !$(this).hasClass('wpmf-back')) {
                    wpmfFoldersModule.houtside();
                    var x = e.clientX; // Get the horizontal coordinate
                    var y = e.clientY;
                    if ($(e.target).hasClass('wpmf-attachment')) {
                        wpmfFoldersModule.editFolderId = $(e.target).data('id');
                    } else {
                        wpmfFoldersModule.editFolderId = $(e.target).closest('li').data('id');
                    }

                    // render custom color
                    wpmfFoldersModule.renderCustomColor();
                    // change color for folder
                    wpmfFoldersModule.setFolderColor();
                    // Set status folder color
                    wpmfFoldersModule.appendCheckColor();
                    $('.wpmf-contextmenu').removeClass('context_overflow');
                    if (x + $('.wpmf-contextmenu-folder').width() + 236 > $(window).width()) {
                        $('.wpmf-contextmenu.wpmf-contextmenu-folder').addClass('context_overflow').slideDown().css({ 'right': $(window).width() - x + 'px', 'left': 'auto', 'top': y + 'px' });
                    } else {
                        $('.wpmf-contextmenu.wpmf-contextmenu-folder').slideDown().css({ 'left': x + 'px', 'right': 'auto', 'top': y + 'px' });
                    }
                }
                return false;
            });

            $('body').bind('click', function (e) {
                if (!$(e.target).hasClass('colorsub') && !$(e.target).hasClass('wp-color-folder')) {
                    wpmfFoldersModule.houtside();
                }
            });

            // edit folder
            $('.material_editfolder').unbind('click').bind('click', function (e) {
                wpmfFoldersModule.clickEditFolder(e, wpmfFoldersModule.editFolderId);
                wpmfFoldersModule.houtside();
            });

            // delete folder
            $('.material_deletefolder').unbind('click').bind('click', function (e) {
                wpmfFoldersModule.clickDeleteFolder(e, wpmfFoldersModule.editFolderId);
                wpmfFoldersModule.houtside();
            });

            // change color for folder
            wpmfFoldersModule.setFolderColor();
        },

        /**
         * render custom color
         */
        renderCustomColor: function renderCustomColor() {
            // remove old html
            $('.custom_color_wrap').remove();
            var value = '';
            var custom_color = '';
            var colorlists = wpmf.l18n.colorlists;
            var folder_color = '<div class="custom_color_wrap">';
            if (typeof colorlists[wpmf.vars.colors[wpmfFoldersModule.editFolderId]] === 'undefined') {
                if (typeof wpmf.vars.colors[wpmfFoldersModule.editFolderId] === 'undefined') {
                    custom_color = '#8f8f8f';
                } else {
                    custom_color = wpmf.vars.colors[wpmfFoldersModule.editFolderId];
                    value = wpmf.vars.colors[wpmfFoldersModule.editFolderId];
                }
            } else {
                custom_color = '#8f8f8f';
            }
            folder_color += '\n                        <input name="wpmf_color_folder" type="text"\n                         placeholder="' + wpmf.l18n.placegolder_color + '"\n                                       value="' + value + '"\n                                       class="inputbox input-block-level wp-color-folder wp-color-picker">';
            if (value === '') {
                folder_color += '<div data-color="' + custom_color + '" class="color custom_color" style="background: ' + custom_color + '"><i class="material-icons color_uncheck">clear</i></div>';
            } else {
                folder_color += '<div data-color="' + custom_color + '" class="color custom_color" style="background: ' + custom_color + '"><i class="material-icons color_check">done</i></div>';
            }

            folder_color += '</div>';
            $('.color-wrapper').append(folder_color);
        },

        /**
         * Set folder color
         */
        setFolderColor: function setFolderColor() {
            $('.wp-color-folder').keyup(function (e) {
                var val = $(this).val();
                if (val.length >= 4) {
                    $('.color.custom_color').data('color', val).css('background', val);
                } else {
                    $('.color.custom_color').data('color', 'transparent').css('background', 'transparent');
                }
            });

            // change color for folder
            $('.wpmf-contextmenu.wpmf-contextmenu-folder .color').unbind('click').bind('click', function (e) {
                var color = $(this).data('color');
                $('.wpmf-attachment[data-id="' + wpmfFoldersModule.editFolderId + '"] .mdc-list-item__start-detail').css('color', color);
                $('.wpmf-folder-tree a[data-id="' + wpmfFoldersModule.editFolderId + '"] > i').css('color', color);
                wpmf.vars.colors[wpmfFoldersModule.editFolderId] = color;
                wpmfFoldersModule.appendCheckColor();
                $.ajax({
                    type: "POST",
                    url: ajaxurl,
                    data: {
                        action: "wpmf",
                        task: "set_folder_color",
                        color: color,
                        folder_id: wpmfFoldersModule.editFolderId
                    },
                    success: function success(response) {
                        if (!response.status) {
                            // Show dialog when set background folder failed
                            showDialog({
                                title: wpmf.l18n.information, // todo : use the response message instead of a predefined one
                                text: wpmf.l18n.bgcolorerror,
                                closeicon: true
                            });
                        }
                    }
                });
            });
        },

        /**
         * render form replace
         */
        renderFormReplace: function renderFormReplace() {
            $('.replace_wrap, .wpmf-replaced').remove();
            var form_replace = '\n                    <div class="replace_wrap" style="display: none">\n                    <form id="wpmf_form_upload" method="post" action="' + wpmf.vars.ajaxurl + '" enctype="multipart/form-data">\n                    <input class="hide" type="file" name="wpmf_replace_file" id="wpmf_upload_input_version">\n                    <input type="hidden" name="action" value="wpmf_replace_file">\n                    <input type="hidden" name="post_selected" value="' + wpmfFoldersModule.editFileId + '">\n                    </form>\n                    <div class="wpmf-replaced" data-wpmftype="replace" data-timeout="3000" data-html-allowed="true" data-content="\' + wpmf.l18n.wpmf_file_replace + \'"></div>\n                ';
            if (!$('.replace_wrap').length) {
                $('body').append(form_replace);
            }
        },

        /**
         * render folder cover on context menu
         */
        renderFolderCover: function renderFolderCover() {
            if (wpmfFoldersModule.last_selected_folder !== 0) {
                $('.context_folder_cover').remove();
                var cover = '';
                if (parseInt(wpmfFoldersModule.categories[wpmfFoldersModule.last_selected_folder].cover_image[0]) === wpmfFoldersModule.editFileId) {
                    cover = '\n                        <li class="context_folder_cover">\n                            <div class="items_menu item_folder_cover">\n                                ' + wpmf.l18n.folder_cover + '            \n                                <div class="waves waves-effect"></div>\n                            <i class="material-icons">insert_photo</i>\n                            <i class="material-icons right">done</i>\n                            </div>\n                            \n                        </li>\n                        ';
                } else {
                    cover = '\n                        <li class="context_folder_cover">\n                            <div class="items_menu item_folder_cover">\n                                ' + wpmf.l18n.folder_cover + '            \n                                <div class="waves waves-effect"></div>\n                            <i class="material-icons">insert_photo</i>\n                            </div>\n                        </li>\n                        ';
                }

                $('.wpmf-contextmenu-file').append(cover);
                // click to set folder cover
                $('.item_folder_cover').unbind('click').bind('click', function (e) {
                    wpmfFoldersModule.saveFolderCover();
                    wpmfFoldersModule.houtside();
                });
            } else {
                $('.context_folder_cover').remove();
            }
        },

        /**
         * right click on file to open menu
         */
        openContextMenuFile: function openContextMenuFile() {
            // init context menu on files
            $('.attachments-browser .attachment:not(.wpmf-attachment)').bind('contextmenu', function (e) {
                wpmfFoldersModule.houtside();
                var x = e.clientX; // Get the horizontal coordinate
                var y = e.clientY;
                if ($(e.target).hasClass('thumbnail')) {
                    wpmfFoldersModule.editFileId = $(e.target).closest('li').data('id');
                } else {
                    wpmfFoldersModule.editFileId = $(e.target).data('id');
                }

                $('.wpmf-contextmenu').removeClass('context_overflow');
                if (x + $('.wpmf-contextmenu-file').width() > $(window).width()) {
                    $('.wpmf-contextmenu.wpmf-contextmenu-file').addClass('context_overflow').slideDown().css({ 'right': $(window).width() - x + 'px', 'left': 'auto', 'top': y + 'px' });
                } else {
                    $('.wpmf-contextmenu.wpmf-contextmenu-file').slideDown().css({ 'left': x + 'px', 'right': 'auto', 'top': y + 'px' });
                }

                // create form replace
                wpmfFoldersModule.renderFormReplace();
                // create folder cover menu
                wpmfFoldersModule.renderFolderCover();
                return false;
            });

            // edit folder
            $('.material_editfile').unbind('click').bind('click', function (e) {
                $('.attachments-browser .attachments .attachment[data-id="' + wpmfFoldersModule.editFileId + '"]').click();
                wpmfFoldersModule.houtside();
            });

            // delete folder
            $('.material_deletefile').unbind('click').bind('click', function (e) {
                wpmfFoldersModule.clickDeleteFile(e, wpmfFoldersModule.editFileId);
                wpmfFoldersModule.houtside();
            });

            // duplicate file
            $('.material_duplicatefile').unbind('click').bind('click', function (e) {
                wpmfDuplicateModule.doDuplicate(wpmfFoldersModule.editFileId);
                wpmfFoldersModule.houtside();
            });

            // get URL attachment
            $('.material_geturlfile').unbind('click').bind('click', function (e) {
                var url = wp.media.attachment(wpmfFoldersModule.editFileId).get('url');
                wpmfFoldersModule.setClipboardText(url);
                wpmfFoldersModule.houtside();
            });

            // Add the form replace to body

            $('.material_overridefile').unbind('click').bind('click', function (e) {
                $('#wpmf_upload_input_version').click();
                wpmfReplaceModule.doEvent();
                wpmfReplaceModule.replace_attachment(wpmfFoldersModule.editFileId, 'material');
                wpmfFoldersModule.houtside();
            });

            // change folder for file
            $('.material_changefolder').unbind('click').bind('click', function (e) {
                wpmfAssignModule.showdialog('one');
                wpmfAssignModule.initTree(wpmfFoldersModule.editFileId);
                wpmfFoldersModule.houtside();
            });
        },

        /**
         * click outside
         */
        houtside: function houtside() {
            $('.wpmf-contextmenu-file, .wpmf-contextmenu-folder').hide();
        },

        /**
         * set clipboard text
         * @param text
         */
        setClipboardText: function setClipboardText(text) {
            var id = "mycustom-clipboard-textarea-hidden-id";
            var existsTextarea = document.getElementById(id);

            if (!existsTextarea) {
                var textarea = document.createElement("textarea");
                textarea.id = id;
                // Place in top-left corner of screen regardless of scroll position.
                textarea.style.position = 'fixed';
                textarea.style.top = 0;
                textarea.style.left = 0;

                // Ensure it has a small width and height. Setting to 1px / 1em
                // doesn't work as this gives a negative w/h on some browsers.
                textarea.style.width = '1px';
                textarea.style.height = '1px';

                // We don't need padding, reducing the size if it does flash render.
                textarea.style.padding = 0;

                // Clean up any borders.
                textarea.style.border = 'none';
                textarea.style.outline = 'none';
                textarea.style.boxShadow = 'none';

                // Avoid flash of white box if rendered for any reason.
                textarea.style.background = 'transparent';
                document.querySelector("body").appendChild(textarea);
                existsTextarea = document.getElementById(id);
            }

            existsTextarea.value = text;
            existsTextarea.select();

            try {
                var status = document.execCommand('copy');
                if (!status) {
                    showDialog({
                        title: wpmf.l18n.information, // todo : use the response message instead of a predefined one
                        text: wpmf.l18n.cannot_copy,
                        closeicon: true
                    });
                } else {
                    wpmfSnackbarModule.show({
                        content: wpmf.l18n.copy_url,
                        auto_close_delay: 1000
                    });
                }
            } catch (err) {
                showDialog({
                    title: wpmf.l18n.information, // todo : use the response message instead of a predefined one
                    text: wpmf.l18n.unable_copy,
                    closeicon: true
                });
            }
        },

        /**
         * render context menu box
         */
        renderContextMenu: function renderContextMenu() {
            var colors = '';
            // render list color
            $.each(wpmf.l18n.colorlists, function (i, title) {
                colors += '<div data-color="' + i + '" title="' + title + '" class="color" \n                 style="background: ' + i + '"></div>';
            });

            // render context menu for folder
            var context_folder = '\n            <ul class="wpmf-contextmenu wpmf-contextmenu-folder contextmenu z-depth-1 grey-text text-darken-2">\n                <li><div class="material_editfolder items_menu">' + wpmf.l18n.edit_folder + '<i class="material-icons">border_color</i></div></li>\n                <li><div class="material_deletefolder items_menu">' + wpmf.l18n.delete + '<i class="material-icons">delete</i></div></li>\n                <li class="sub">\n                    <div class="items_menu">\n                        ' + wpmf.l18n.change_color + '            \n                        <div class="waves waves-effect"></div>\n                    <i class="material-icons">palette</i>\n                    <i class="material-icons right">keyboard_arrow_right</i>\n                    </div>\n                    <ul class="colorsub submenu z-depth-1">\n                        <li class="waves-effect wpmf-color-picker">\n                                <div class="color-wrapper">\n                                ' + colors + '\n                                </div>\n                        </li>\n                    </ul>\n                    \n                </li>\n            </ul>\n            ';

            // render context menu for file
            // duplicate menu
            var duplicate = '';
            if (typeof wpmf.vars.duplicate !== 'undefined' && parseInt(wpmf.vars.duplicate) === 1) {
                duplicate = '<li><div class="material_duplicatefile items_menu">' + wpmf.l18n.duplicate_text + '<i class="material-icons">content_copy</i></div></li>';
            }

            // replace menu
            var override = '';
            if (typeof wpmf.vars.override !== 'undefined' && parseInt(wpmf.vars.override) === 1) {
                override = '<li><div class="material_overridefile items_menu">' + wpmf.l18n.replace + '<i class="material-icons">cached</i></div></li>';
            }
            var context_file = '\n            <ul class="wpmf-contextmenu wpmf-contextmenu-file contextmenu z-depth-1 grey-text text-darken-2">\n                <li><div class="material_editfile items_menu">' + wpmf.l18n.edit_file + '<i class="material-icons">border_color</i></div></li>\n                <li><div class="material_deletefile items_menu">' + wpmf.l18n.remove + '<i class="material-icons">delete</i></div></li>\n                <li><div class="material_geturlfile items_menu">' + wpmf.l18n.get_url_file + '<i class="material-icons">link</i></div></li>\n                ' + duplicate + '\n                ' + override + '\n                <li><div class="material_changefolder open-popup-tree items_menu">' + wpmf.l18n.change_folder + '<i class="material-icons">keyboard_tab</i></div></li>\n            </ul>\n            ';

            // Add the context menu box for folder to body
            if (!$('.wpmf-contextmenu.wpmf-contextmenu-folder').length) {
                $('body').append(context_folder);
            }

            // Add the context menu box for attachment to body
            if (!$('.wpmf-contextmenu.wpmf-contextmenu-file').length) {
                $('body').append(context_file);
            }
        },

        /**
         * Open a lightbox to enter the new folder name
         *
         * @param parent_id id parent folder
         */
        newFolder: function newFolder(parent_id) {
            var options = {
                title: wpmf.l18n.create_folder,
                text: '<input type="text" name="wpmf_newfolder_input" class="wpmf_newfolder_input" placeholder="' + wpmf.l18n.new_folder + '">',
                negative: {
                    title: wpmf.l18n.cancel
                },
                positive: {
                    title: wpmf.l18n.create,
                    onClick: function onClick() {
                        // Call php script to create the folder
                        wpmfFoldersModule.createNewFolder($('.wpmf_newfolder_input').val(), parent_id);

                        // Hide the dialog
                        hideDialog(jQuery('#orrsDiag'));
                    }
                }
            };
            showDialog(options);

            // Bind the press enter key to submit the modal
            $('.wpmf_newfolder_input').focus().keypress(function (e) {
                if (e.which === 13) {
                    options.positive.onClick.call(this);
                }
            });
        },

        /**
         * Save folder cover
         */
        saveFolderCover: function saveFolderCover() {
            $.ajax({
                type: "POST",
                url: ajaxurl,
                data: {
                    action: "wpmf",
                    task: "save_folder_cover",
                    folder_id: wpmfFoldersModule.last_selected_folder,
                    post_id: wpmfFoldersModule.editFileId
                },
                success: function success(response) {
                    if (response.status === true) {
                        wpmfFoldersModule.categories[wpmfFoldersModule.last_selected_folder].cover_image = response.params;
                        wpmfFoldersModule.reloadAttachments();
                    }
                }
            });
        },

        /**
         * Send ajax request to create a new folder
         *
         * @param name string new folder name
         * @param parent_id int parent folder
         */
        createNewFolder: function createNewFolder(name, parent_id) {
            return $.ajax({
                type: "POST",
                url: ajaxurl,
                data: {
                    action: "wpmf",
                    task: "add_folder",
                    name: name,
                    parent: parent_id
                },
                success: function success(response) {
                    if (response.status === true) {
                        if (wpmfFoldersModule.page_type === 'upload-list') {
                            // In list view reload the page
                            $('.upload-php #posts-filter').submit();
                            return;
                        }

                        // Update the categories variables
                        wpmfFoldersModule.categories = response.categories;
                        wpmfFoldersModule.categories_order = response.categories_order;

                        // Regenerate the folder filter
                        wpmfFoldersModule.initFolderFilter();

                        // Reload the folders
                        wpmfFoldersModule.renderFolders();

                        // Show snackbar
                        wpmfSnackbarModule.show({
                            content: wpmf.l18n.wpmf_addfolder
                        });

                        wpmfFoldersModule.trigger('addFolder', response.term);
                    } else {
                        // Show dialog when adding folder failed
                        showDialog({
                            title: wpmf.l18n.information, // todo : use the response message instead of a predefined one
                            text: wpmf.l18n.alert_add,
                            closeicon: true
                        });
                    }
                }
            });
        },

        /**
         * Clicki on edit icon on a folder
         */
        clickEditFolder: function clickEditFolder(event, folder_id) {
            event.stopPropagation();

            // Retrieve the current folder name
            var name = wpmfFoldersModule.categories[folder_id].label;

            // Show the input dialog
            var options = {
                title: wpmf.l18n.promt,
                text: '<input type="text" name="wpmf_editfolder_input" class="wpmf_newfolder_input" value="' + name + '">',
                negative: {
                    title: wpmf.l18n.cancel
                },
                positive: {
                    title: wpmf.l18n.save,
                    onClick: function onClick() {
                        var new_name = $('.wpmf_newfolder_input').val();
                        if (new_name !== '' && new_name !== 'null') {
                            // Call php script to update folder name
                            wpmfFoldersModule.updateFolderName(folder_id, new_name);

                            // Close the dialog
                            hideDialog($('#orrsDiag'));
                        }
                    }
                }
            };
            showDialog(options);

            // Bind the press enter key to submit the modal
            $('.wpmf_newfolder_input').keypress(function (e) {
                if (e.which === 13) {
                    options.positive.onClick.call(this);
                }
            });
        },

        /**
         * Update folder name
         *
         * @param id int id of folder
         * @param name string new name of folder
         */
        updateFolderName: function updateFolderName(id, name) {
            return $.ajax({
                type: "POST",
                url: ajaxurl,
                data: {
                    action: "wpmf",
                    task: "edit_folder",
                    name: name,
                    id: id
                },
                success: function success(response) {
                    if (response === false) {
                        if (name !== wpmfFoldersModule.categories[id].label) {
                            // todo: why do we check that?
                            showDialog({
                                title: wpmf.l18n.information,
                                text: wpmf.l18n.alert_add,
                                closeicon: true
                            });

                            if ($(wpmfFoldersTreeModule.editable).length) {
                                $(wpmfFoldersTreeModule.editable).text(wpmfFoldersModule.categories[id].label);
                            }
                        }
                    } else {
                        // Store variables in case of undo
                        var old_name = wpmfFoldersModule.categories[response.term_id].label;

                        // Update the name in stored variables
                        wpmfFoldersModule.categories[response.term_id].label = response.name;

                        // Render folders to update name
                        wpmfFoldersModule.renderFolders();

                        if (wpmfFoldersModule.page_type === 'upload-list') {
                            // Update the name in select input with the same number of spaces
                            var $selected_option = $('#wpmf-media-category option[value="' + id + '"]');
                            $selected_option.html($selected_option.html().slice(0, $selected_option.html().lastIndexOf('&nbsp;')) + name);
                        } else {
                            // Update the select filter
                            wpmfFoldersModule.initFolderFilter();
                        }

                        // Show snackbar
                        wpmfSnackbarModule.show({
                            content: wpmf.l18n.wpmf_undo_editfolder,
                            is_undoable: true,
                            onUndo: function onUndo() {
                                // Cancel delete folder
                                wpmfFoldersModule.updateFolderName(id, old_name);
                            }
                        });

                        wpmfFoldersModule.trigger('updateFolder', id);
                    }
                }
            });
        },

        /**
         * Delete folder click function in template
         * @param event Object
         * @param id int folder id to delete
         */
        clickDeleteFolder: function clickDeleteFolder(event, id) {
            event = event || window.event; // FF IE fix if event has not been passed in function

            event.stopPropagation();

            // Show an alter depending on if we delete also included images inside the folder
            var alert_delete = void 0;
            if (typeof wpmf.vars.wpmf_remove_media !== "undefined" && parseInt(wpmf.vars.wpmf_remove_media) === 1) {
                alert_delete = wpmf.l18n.alert_delete_all;
            } else {
                alert_delete = wpmf.l18n.alert_delete;
            }

            showDialog({
                title: alert_delete,
                negative: {
                    title: wpmf.l18n.cancel
                },
                positive: {
                    title: wpmf.l18n.delete,
                    onClick: function onClick() {
                        // Add effect in the folder deleted while we wait the response from server
                        $('.wpmf-attachment[data-id="' + id + '"]').css({ 'opacity': '0.5' });
                        $('.wpmf-attachment[data-id="' + id + '"] .wpmf-attachment-preview').append('<div class="wpmfdeletefolderprogress"> <div class="indeterminate"></div></div>');

                        wpmfFoldersModule.deleteFolder(id);
                    }
                }
            });
        },

        /**
         * Send ajax request to delete a folder
         * @param id
         */
        deleteFolder: function deleteFolder(id) {
            // Store some values in case of undo
            var old_folder_name = wpmfFoldersModule.categories[id].label,
                old_parent = wpmfFoldersModule.categories[id].parent_id;

            return $.ajax({
                type: "POST",
                url: ajaxurl,
                data: {
                    action: "wpmf",
                    task: "delete_folder",
                    id: id
                },
                success: function success(response) {
                    if (response.status === true) {
                        if (wpmfFoldersModule.page_type === 'upload-list') {
                            // In list view reload the page
                            $('.upload-php #posts-filter').submit();
                            return;
                        }

                        // Update the categories variables
                        wpmfFoldersModule.categories = response.categories;
                        wpmfFoldersModule.categories_order = response.categories_order;

                        // Regenerate the folder filter
                        wpmfFoldersModule.initFolderFilter();

                        // Reload the folders
                        wpmfFoldersModule.renderFolders();

                        // Show snackbar
                        wpmfSnackbarModule.show({
                            content: wpmf.l18n.wpmf_undo_remove,
                            is_undoable: true,
                            onUndo: function onUndo() {
                                // Cancel delete folder
                                wpmfFoldersModule.createNewFolder(old_folder_name, old_parent);
                            }
                        });

                        wpmfFoldersModule.trigger('deleteFolder', id);
                    } else {
                        // todo : show error message from json response
                        showDialog({
                            title: wpmf.l18n.information,
                            text: wpmf.l18n.alert_delete1
                        });
                    }
                }
            });
        },

        /**
         * Delete file click function in template
         * @param event Object
         * @param id int file id to delete
         */
        clickDeleteFile: function clickDeleteFile(event, id) {
            showDialog({
                title: wpmf.l18n.alert_delete_file,
                negative: {
                    title: wpmf.l18n.cancel
                },
                positive: {
                    title: wpmf.l18n.remove,
                    onClick: function onClick() {
                        wpmfFoldersModule.deletefile(id);
                    }
                }
            });
        },

        /**
         * Send ajax request to delete a file
         * @param id
         */
        deletefile: function deletefile(id) {
            // Store some values in case of undo
            return $.ajax({
                type: "POST",
                url: ajaxurl,
                data: {
                    action: "wpmf",
                    task: "delete_file",
                    id: id
                },
                success: function success(response) {
                    if (response.status) {
                        wpmfFoldersModule.reloadAttachments();
                    }
                }
            });
        },

        /**
         * Change the breadcrumb content
         * depending on the selected folder
         *
         * @param term_id
         */
        updateBreadcrumb: function updateBreadcrumb(term_id) {
            if (term_id === undefined) {
                // If not term id is set we use the latest used
                if (parseInt(wpmf.vars.wpmf_active_media) === 1 && wpmf.vars.wpmf_role !== 'administrator' && wpmfFoldersModule.page_type !== 'upload-list' && wpmf.vars.term_root_id) {
                    term_id = wpmf.vars.term_root_id;
                } else {
                    term_id = wpmfFoldersModule.last_selected_folder;
                }
            } else {
                // Let's save this term as the last used one
                wpmfFoldersModule.last_selected_folder = term_id;
            }

            // Get breadcrumb element
            var $wpmf_breadcrumb = wpmfFoldersModule.getFrame().find('#wpmf-breadcrumb');

            // Remove breadcrumb content
            $wpmf_breadcrumb.html(null);

            var category = wpmfFoldersModule.categories[term_id];

            var breadcrumb_content = '';

            // Ascend until there is no more parent
            while (parseInt(category.parent_id) !== parseInt(wpmf.vars.parent)) {
                // Generate breadcrumb element
                breadcrumb_content = '<li>&nbsp;&nbsp;/&nbsp;&nbsp;<a href="#" data-id="' + wpmfFoldersModule.categories[category.id].id + '">' + wpmfFoldersModule.categories[category.id].label + '</a></li>' + breadcrumb_content;

                // Get the parent
                category = wpmfFoldersModule.categories[wpmfFoldersModule.categories[category.id].parent_id];
            }

            if (parseInt(category.id) !== 0) {
                breadcrumb_content = '<li><a href="#" data-id="' + wpmfFoldersModule.categories[category.id].id + '">' + wpmfFoldersModule.categories[category.id].label + '</a></li>' + breadcrumb_content;
            }

            breadcrumb_content = '<li><span>' + wpmf.l18n.youarehere + '</span>&nbsp;&nbsp;:<a href="#" data-id="0">&nbsp;&nbsp;' + wpmf.l18n.home + '&nbsp;&nbsp;</a>/&nbsp;&nbsp;</li>' + breadcrumb_content;

            // Finally update breadcrumb content
            $wpmf_breadcrumb.prepend(breadcrumb_content);

            /* bind breadcrumb click event */
            $wpmf_breadcrumb.find('a').click(function () {
                wpmfFoldersModule.changeFolder($(this).data('id'));
            });
        },

        /**
         * Initialize dragging and dropping folders and files
         */
        initializeDragAndDropAttachments: function initializeDragAndDropAttachments() {
            // Initialize draggable
            var $frame = wpmfFoldersModule.getFrame();
            var draggable_attachments = 'ul.attachments .attachment:not(.attachment.uploading):not(.wpmf-new):not(.wpmf-back):not(.ui-droppable):not(.ui-state-disabled)';
            var append_element = void 0;

            if (wpmfFoldersModule.page_type === 'upload-list') {
                append_element = '.upload-php #posts-filter';
                if (wpmf.vars.wpmf_order_media === 'custom') {
                    draggable_attachments += ', #the-list tr';
                } else {
                    draggable_attachments += ', .wpmf-move';
                }
                // Add attachments move handle on list table
                $('.upload-php #posts-filter .wp-list-table thead tr, .upload-php #posts-filter .wp-list-table tfoot tr').prepend('<th class="wpmf-move-header"></th>');
                $('.upload-php #posts-filter .wp-list-table tbody th').before('<td class="wpmf-move" title="' + wpmf.l18n.dragdrop + '"><span class="zmdi zmdi-more"></span></td>');
            } else {
                draggable_attachments = '.attachments-browser ' + draggable_attachments;
                append_element = '.media-frame';
            }

            var order_media = 'all';
            var order_folder = 'name-ASC';
            var items_sortable = '';
            var preview_sortable = '';
            var placeholder = '';
            var accept = '.attachment:not(.wpmf-back):not(.wpmf-new):not(.wpmf-line-break)';
            if (wpmfFoldersModule.page_type === 'upload-list') {
                order_folder = wpmf.vars.folder_order;
            } else {
                order_folder = $('#media-order-folder').val();
            }

            if (wpmfFoldersModule.page_type === 'upload-list') {
                order_media = wpmf.vars.wpmf_order_media;
            } else {
                order_media = $('#media-order-media').val();
            }

            if (order_folder === 'custom' && order_media !== 'custom') {
                if (wpmfFoldersModule.page_type === 'upload-list') {
                    accept = '.wpmf-move';
                } else {
                    accept = '.attachment.save-ready';
                }
                wpmfFoldersModule.sortableFolder($frame, append_element);

                if (wpmfFoldersModule.page_type === 'upload-list') {
                    wpmfFoldersModule.draggableFile($frame, '.wpmf-move', append_element);
                } else {
                    wpmfFoldersModule.draggableFile($frame, 'ul.attachments .attachment:not(.wpmf-attachment)', append_element);
                }
            } else if (order_folder !== 'custom' && order_media === 'custom') {
                if (wpmfFoldersModule.page_type === 'upload-list') {
                    items_sortable = draggable_attachments;
                    preview_sortable = '.upload-php .wp-list-table.media';
                    placeholder = 'wpmf-highlight';
                } else {
                    items_sortable = '.attachment.save-ready';
                    preview_sortable = '.attachments';
                    placeholder = 'attachment';
                }

                // if set custom media order filter
                wpmfFoldersModule.sortableFile($frame, append_element, items_sortable, preview_sortable, placeholder);
                wpmfFoldersModule.draggableFile($frame, 'ul.attachments .wpmf-attachment:not(.wpmf-new):not(.wpmf-back):not(.ui-droppable)', append_element);
            } else if (order_folder === 'custom' && order_media === 'custom') {
                if (wpmfFoldersModule.page_type === 'upload-list') {
                    items_sortable = draggable_attachments;
                    preview_sortable = '.upload-php .wp-list-table.media, .attachments';
                    accept = '.wpmf-move';
                } else {
                    items_sortable = '.attachment:not(.ui-state-disabled)';
                    preview_sortable = '.attachments';
                    accept = '.attachment.save-ready';
                }
                wpmfFoldersModule.sortableAll($frame, preview_sortable, append_element, items_sortable);
            } else {
                if (wpmfFoldersModule.page_type === 'upload-list') {
                    accept = '.wpmf-move, .wpmf-folder:not(.wpmf-back)';
                } else {

                    accept = '.attachment';
                }
                wpmfFoldersModule.draggableFile($frame, draggable_attachments, append_element);
            }

            // Initialize droppable on folders
            var droppable_element = 'ul.attachments .wpmf-folder';
            if (wpmfFoldersModule.page_type !== 'upload-list') {
                droppable_element = '.attachments-browser ' + droppable_element;
            }

            $frame.find(droppable_element).droppable({
                hoverClass: "wpmf-hover-folder",
                tolerance: 'pointer',
                accept: accept,
                over: function over(event, ui) {
                    $(event.target).addClass("wpmfdropzoom");
                    $('.wpmf-file-handle').addClass("overfolder");
                },
                out: function out(event, ui) {
                    $(event.target).removeClass("wpmfdropzoom");
                    $('.wpmf-file-handle').removeClass("overfolder");
                },
                drop: function drop(event, ui) {
                    wpmfFoldersModule.droppedAttachment($(this).data('id'));
                }
            });
        },

        /**
         * Drag file
         * @param $frame
         * @param draggable_attachments
         * @param append_element
         */
        draggableFile: function draggableFile($frame, draggable_attachments, append_element) {
            $frame.find(draggable_attachments).draggable({
                helper: function helper(ui) {
                    if (wpmfFoldersModule.page_type === 'upload-list' && $(ui.currentTarget).is('td')) {
                        return '<div class="wpmf-dragging-list">Moving files</div>';
                    } else {
                        return $(ui.currentTarget).clone();
                    }
                },
                appendTo: append_element,
                delay: 100, // Prevent dragging when only trying to click
                distance: 10,
                cursorAt: { top: 0, left: 0 },
                drag: function drag() {},
                start: function start(event, ui) {
                    // Save the element we drag in a variable to use this later
                    wpmfFoldersModule.dragging_elements = [this];
                    // Add the original size of element
                    $(ui.helper).css('width', $(ui.helper.context).outerWidth() + 'px');
                    $(ui.helper).css('height', $(ui.helper.context).outerWidth() + 'px');
                    if (!$(this).hasClass('wpmf-folder')) {
                        // We're moving a file, it could be multiple files dragging
                        if (wpmfFoldersModule.page_type === 'upload-list') {
                            // Save the element we drag in a variable to use this later
                            $frame.find('#the-list input[name="media[]"]:checked:not(".ui-draggable-dragging")').closest('tr').find('td.wpmf-move').each(function () {
                                if (this !== wpmfFoldersModule.dragging_elements[0]) {
                                    // Check that the element is not already in the list
                                    wpmfFoldersModule.dragging_elements.push(this);
                                }
                            });
                        } else {
                            $frame.find('.attachments-browser ul.attachments .attachment[aria-checked="true"]:not(".ui-draggable-dragging")').each(function () {
                                if (this !== wpmfFoldersModule.dragging_elements[0]) {
                                    // Check that the element is not already in the list
                                    wpmfFoldersModule.dragging_elements.push(this);
                                }
                            });
                        }
                    }

                    // Add some style to original elements
                    $(wpmfFoldersModule.dragging_elements).each(function () {
                        $(this).addClass('wpmf-dragging');
                    });

                    // Remove the checkbox of the attachment
                    ui.helper.find('button.check').remove();

                    // Add the number of elements dragged if more than 1
                    if (wpmfFoldersModule.dragging_elements.length > 1) {
                        ui.helper.append('<div class="wpmf-drag-count">' + wpmfFoldersModule.dragging_elements.length + '</div>');
                    }
                },
                stop: function stop(event, ui) {
                    // Revert style
                    $(wpmfFoldersModule.dragging_elements).each(function () {
                        $(this).removeClass('wpmf-dragging');
                    });

                    wpmfFoldersModule.dragging_elements = null;
                }
            });
        },

        sortableAll: function sortableAll($frame, preview_sortable, append_element, items_sortable) {
            if (wpmfFoldersModule.page_type === 'upload-list') {
                // sortable folder
                var placeholder = '';
                if (wpmfFoldersModule.folder_design === 'material_design') {
                    placeholder = 'mdc-list-item attachment wpmf-attachment material_design wpmf-folder mdc-ripple-upgraded wpmf-transparent';
                } else {
                    placeholder = 'wpmf-attachment attachment wpmf-folder wpmf-transparent';
                }

                $('.attachments').sortable({
                    placeholder: placeholder,
                    revert: true,
                    items: '.wpmf-folder:not(.wpmf-back)',
                    distance: 5,
                    tolerance: "pointer",
                    appendTo: append_element,
                    helper: function helper(e, item) {
                        return $(item).clone();
                    },
                    /** Prevent firefox bug positionnement **/
                    start: function start(event, ui) {},
                    stop: function stop(event, ui) {},
                    beforeStop: function beforeStop(event, ui) {
                        var userAgent = navigator.userAgent.toLowerCase();
                        if (ui.offset !== "undefined" && userAgent.match(/firefox/)) {
                            ui.helper.css('margin-top', 0);
                        }
                    },
                    update: function update() {
                        var order = '';
                        $.each($('.attachments .wpmf-folder'), function (i, val) {
                            if (order !== '') {
                                order += ',';
                            }
                            order += '"' + i + '":' + $(val).data('id');
                            wpmfFoldersModule.categories[$(val).data('id')].order = i;
                        });
                        order = '{' + order + '}';

                        // do re-order file
                        $.ajax({
                            type: "POST",
                            url: ajaxurl,
                            data: {
                                action: "wpmf",
                                task: "reorderfolder",
                                order: order
                            },
                            success: function success(response) {}
                        });
                    }
                });

                // sortable file
                placeholder = '';
                if (wpmfFoldersModule.folder_design === 'material_design') {
                    placeholder = 'mdc-list-item attachment wpmf-attachment material_design wpmf-folder mdc-ripple-upgraded wpmf-transparent';
                } else {
                    placeholder = 'wpmf-attachment attachment wpmf-folder wpmf-transparent';
                }

                $('.upload-php .wp-list-table.media').sortable({
                    placeholder: 'wpmf-highlight',
                    revert: true,
                    distance: 5,
                    items: '#the-list tr',
                    tolerance: "pointer",
                    appendTo: append_element,
                    helper: function helper(e, item) {
                        if (wpmfFoldersModule.page_type === 'upload-list' && $(item).is('tr')) {
                            var label = $(item).find('.filename span').text();
                            var full_label = $(item).find('.filename').text();
                            var filename = full_label.replace(label, "");
                            return '<div class="wpmf-file-handle"><div>' + filename + '</div></div>';
                        } else {
                            return $(item).clone();
                        }
                    },
                    /** Prevent firefox bug positionnement **/
                    start: function start(event, ui) {
                        // Save the element we drag in a variable to use this later
                        wpmfFoldersModule.dragging_elements = [$(ui.item).find('.wpmf-move')];

                        // Add the original size of element
                        if (!$($(ui.helper)).hasClass('wpmf-folder')) {
                            // Save the element we drag in a variable to use this later
                            $frame.find('#the-list input[name="media[]"]:checked:not(".ui-draggable-dragging")').closest('tr').find('td.wpmf-move').each(function () {
                                if (this !== wpmfFoldersModule.dragging_elements[0][0]) {
                                    // Check that the element is not already in the list
                                    wpmfFoldersModule.dragging_elements.push($(this));
                                }
                            });
                        }

                        // Remove the checkbox of the attachment
                        ui.helper.find('button.check').remove();

                        // Add the number of elements dragged if more than 1
                        if (wpmfFoldersModule.dragging_elements.length > 1) {
                            ui.helper.append('<div class="wpmf-drag-count">' + wpmfFoldersModule.dragging_elements.length + '</div>');
                        }

                        var cols = $('.wp-list-table.media thead tr th').length + $('.wp-list-table.media thead tr td').length;
                        ui.placeholder.html("<td colspan='" + cols + "'></td>");
                    },
                    stop: function stop(event, ui) {
                        if (wpmfFoldersModule.page_type === 'upload-list') {
                            $('.wpmf-file-handle').removeClass('wpmfzoomin');
                        }
                        wpmfFoldersModule.dragging_elements = null;
                    },
                    beforeStop: function beforeStop(event, ui) {
                        var userAgent = navigator.userAgent.toLowerCase();
                        if (ui.offset !== "undefined" && userAgent.match(/firefox/)) {
                            ui.helper.css('margin-top', 0);
                        }
                    },
                    beforeRevert: function beforeRevert(e, ui) {
                        if ($('.wpmfdropzoom').length) {
                            return false; // copy/move file
                        }

                        $('.wpmf-file-handle').addClass('wpmfzoomin').fadeOut();
                        return true;
                    },
                    update: function update() {
                        var order = '';
                        var element = '';
                        $.each($('#the-list tr'), function (i, val) {
                            var string_id = $(val).attr('id');
                            if (order !== '') {
                                order += ',';
                            }
                            order += '"' + i + '":' + string_id.replace("post-", "");
                        });
                        order = '{' + order + '}';

                        // do re-order file
                        $.ajax({
                            type: "POST",
                            url: ajaxurl,
                            data: {
                                action: "wpmf",
                                task: "reorderfile",
                                order: order
                            },
                            success: function success(response) {
                                if (wpmfFoldersModule.page_type !== 'upload-list') {
                                    wpmfFoldersModule.reloadAttachments();
                                }
                            }
                        });
                    }
                });

                $(".upload-php .wp-list-table.media").disableSelection();
            } else {
                var _placeholder = '';
                $('.attachments').sortable('enable');
                $('.attachments').sortable({
                    placeholder: '',
                    revert: true,
                    cancel: ".ui-state-disabled",
                    items: '.attachment:not(.ui-state-disabled):not(.wpmf-new):not(.wpmf-back)',
                    distance: 5,
                    tolerance: "pointer",
                    appendTo: append_element,
                    helper: function helper(e, item) {
                        return $(item).clone();
                    },
                    /** Prevent firefox bug positionnement **/
                    start: function start(event, ui) {
                        if ($(ui.item).hasClass('attachment save-ready')) {
                            $('.wpmf-attachment').addClass('ui-state-disabled');
                            _placeholder = 'attachment';
                        } else {
                            $('.attachment.save-ready').addClass('ui-state-disabled');
                            if (wpmfFoldersModule.folder_design === 'material_design') {
                                _placeholder = 'mdc-list-item attachment wpmf-attachment material_design wpmf-folder mdc-ripple-upgraded wpmf-transparent';
                            } else {
                                _placeholder = 'wpmf-attachment attachment wpmf-folder wpmf-transparent';
                            }
                        }
                        $(ui.placeholder).addClass(_placeholder);
                        wpmfFoldersModule.dragging_elements = [$(ui.item)];

                        // Add the original size of element
                        if (!$($(ui.helper)).hasClass('wpmf-folder')) {
                            // We're moving a file, it could be multiple files dragging
                            $frame.find('.attachments-browser ul.attachments .attachment[aria-checked="true"]:not(".ui-draggable-dragging")').each(function () {
                                if (this !== wpmfFoldersModule.dragging_elements[0][0]) {
                                    // Check that the element is not already in the list
                                    wpmfFoldersModule.dragging_elements.push($(this));
                                }
                            });
                        }

                        // Remove the checkbox of the attachment
                        ui.helper.find('button.check').remove();

                        // Add the number of elements dragged if more than 1
                        if (wpmfFoldersModule.dragging_elements.length > 1) {
                            ui.helper.append('<div class="wpmf-drag-count">' + wpmfFoldersModule.dragging_elements.length + '</div>');
                        }

                        ui.placeholder.html("<div></div>");
                    },
                    stop: function stop(event, ui) {
                        $('.attachment').removeClass('ui-state-disabled');
                    },
                    beforeStop: function beforeStop(event, ui) {
                        var userAgent = navigator.userAgent.toLowerCase();
                        if (ui.offset !== "undefined" && userAgent.match(/firefox/)) {
                            ui.helper.css('margin-top', 0);
                        }
                    },
                    update: function update(event, ui) {
                        if ($(ui.item).hasClass('wpmf-folder')) {
                            var order = '';
                            $.each($('.attachments .wpmf-folder'), function (i, val) {
                                if (order !== '') {
                                    order += ',';
                                }
                                order += '"' + i + '":' + $(val).data('id');
                                wpmfFoldersModule.categories[$(val).data('id')].order = i;
                            });
                            order = '{' + order + '}';

                            // do re-order file
                            $.ajax({
                                type: "POST",
                                url: ajaxurl,
                                data: {
                                    action: "wpmf",
                                    task: "reorderfolder",
                                    order: order
                                }
                            });
                        } else {
                            var _order = '';
                            if (wpmfFoldersModule.page_type === 'upload-list') {
                                $.each($('#the-list tr'), function (i, val) {
                                    var string_id = $(val).attr('id');
                                    if (_order !== '') {
                                        _order += ',';
                                    }
                                    _order += '"' + i + '":' + string_id.replace("post-", "");
                                });
                                _order = '{' + _order + '}';
                            } else {
                                $.each($('.attachments .attachment:not(.wpmf-attachment)'), function (i, val) {
                                    if (_order !== '') {
                                        _order += ',';
                                    }
                                    _order += '"' + i + '":' + $(val).data('id');
                                });
                                _order = '{' + _order + '}';
                            }

                            // do re-order file
                            $.ajax({
                                type: "POST",
                                url: ajaxurl,
                                data: {
                                    action: "wpmf",
                                    task: "reorderfile",
                                    order: _order
                                },
                                success: function success(response) {
                                    if (wpmfFoldersModule.page_type !== 'upload-list') {
                                        wpmfFoldersModule.reloadAttachments();
                                    }
                                }
                            });
                        }
                    }
                });

                $(".attachments").disableSelection();
            }
        },

        /**
         * Custom order
         * @param $frame
         * @param append_element
         */
        sortableFolder: function sortableFolder($frame, append_element) {
            var placeholder = '';
            if (wpmfFoldersModule.folder_design === 'material_design') {
                placeholder = 'mdc-list-item attachment wpmf-attachment material_design wpmf-folder mdc-ripple-upgraded wpmf-transparent';
            } else {
                placeholder = 'wpmf-attachment attachment wpmf-folder wpmf-transparent';
            }

            if (wpmfFoldersModule.page_type !== 'upload-list') {
                $('.attachments').sortable('enable');
            }

            $('.attachments').sortable({
                placeholder: placeholder,
                revert: true,
                distance: 5,
                items: '.wpmf-folder:not(.wpmf-back)',
                tolerance: "pointer",
                appendTo: append_element,
                helper: function helper(e, item) {
                    return $(item).clone();
                },
                /** Prevent firefox bug positionnement **/
                start: function start(event, ui) {},
                stop: function stop(event, ui) {},
                beforeStop: function beforeStop(event, ui) {
                    var userAgent = navigator.userAgent.toLowerCase();
                    if (ui.offset !== "undefined" && userAgent.match(/firefox/)) {
                        ui.helper.css('margin-top', 0);
                    }
                },
                update: function update() {
                    var order = '';
                    $.each($('.attachments .wpmf-folder'), function (i, val) {
                        if (order !== '') {
                            order += ',';
                        }
                        order += '"' + i + '":' + $(val).data('id');
                        wpmfFoldersModule.categories[$(val).data('id')].order = i;
                    });
                    order = '{' + order + '}';

                    // do re-order file
                    $.ajax({
                        type: "POST",
                        url: ajaxurl,
                        data: {
                            action: "wpmf",
                            task: "reorderfolder",
                            order: order
                        }
                    });
                }
            });

            $(".attachments").disableSelection();
        },

        /**
         * Custom order
         * @param $frame
         * @param append_element
         * @param items
         * @param preview
         * @param placeholder
         */
        sortableFile: function sortableFile($frame, append_element, items, preview, placeholder) {
            if (wpmfFoldersModule.page_type !== 'upload-list') {
                $(preview).sortable('enable');
            }
            $(preview).sortable({
                placeholder: placeholder,
                revert: true,
                distance: 5,
                items: items,
                tolerance: "pointer",
                appendTo: append_element,
                helper: function helper(e, item) {
                    if (wpmfFoldersModule.page_type === 'upload-list' && $(item).is('tr')) {
                        var label = $(item).find('.filename span').text();
                        var full_label = $(item).find('.filename').text();
                        var filename = full_label.replace(label, "");
                        return '<div class="wpmf-file-handle"><div>' + filename + '</div></div>';
                    } else {
                        return $(item).clone();
                    }
                },
                /** Prevent firefox bug positionnement **/
                start: function start(event, ui) {
                    // Save the element we drag in a variable to use this later
                    if (wpmfFoldersModule.page_type === 'upload-list') {
                        wpmfFoldersModule.dragging_elements = [$(ui.item).find('.wpmf-move')];
                    } else {
                        wpmfFoldersModule.dragging_elements = [$(ui.item)];
                    }

                    // Add the original size of element
                    if (!$($(ui.helper)).hasClass('wpmf-folder')) {
                        // We're moving a file, it could be multiple files dragging
                        if (wpmfFoldersModule.page_type === 'upload-list') {
                            // Save the element we drag in a variable to use this later
                            $frame.find('#the-list input[name="media[]"]:checked:not(".ui-draggable-dragging")').closest('tr').find('td.wpmf-move').each(function () {
                                if (this !== wpmfFoldersModule.dragging_elements[0][0]) {
                                    // Check that the element is not already in the list
                                    wpmfFoldersModule.dragging_elements.push($(this));
                                }
                            });
                        } else {
                            $frame.find('.attachments-browser ul.attachments .attachment[aria-checked="true"]:not(".ui-draggable-dragging")').each(function () {
                                if (this !== wpmfFoldersModule.dragging_elements[0][0]) {
                                    // Check that the element is not already in the list
                                    wpmfFoldersModule.dragging_elements.push($(this));
                                }
                            });
                        }
                    }

                    // Remove the checkbox of the attachment
                    ui.helper.find('button.check').remove();

                    // Add the number of elements dragged if more than 1
                    if (wpmfFoldersModule.dragging_elements.length > 1) {
                        ui.helper.append('<div class="wpmf-drag-count">' + wpmfFoldersModule.dragging_elements.length + '</div>');
                    }

                    if (wpmfFoldersModule.page_type === 'upload-list') {
                        var cols = $('.wp-list-table.media thead tr th').length + $('.wp-list-table.media thead tr td').length;
                        ui.placeholder.html("<td colspan='" + cols + "'></td>");
                    } else {
                        ui.placeholder.html("<div></div>");
                    }
                },
                stop: function stop(event, ui) {
                    if (wpmfFoldersModule.page_type === 'upload-list') {
                        $('.wpmf-file-handle').removeClass('wpmfzoomin');
                    }
                    wpmfFoldersModule.dragging_elements = null;
                },
                beforeStop: function beforeStop(event, ui) {
                    var userAgent = navigator.userAgent.toLowerCase();
                    if (ui.offset !== "undefined" && userAgent.match(/firefox/)) {
                        ui.helper.css('margin-top', 0);
                    }
                },
                beforeRevert: function beforeRevert(e, ui) {
                    if ($('.wpmfdropzoom').length) {
                        return false; // copy/move file
                    }

                    if (wpmfFoldersModule.page_type === 'upload-list') {
                        $('.wpmf-file-handle').addClass('wpmfzoomin').fadeOut();
                    }

                    return true;
                },
                update: function update() {
                    var order = '';
                    if (wpmfFoldersModule.page_type === 'upload-list') {
                        $.each($('#the-list tr'), function (i, val) {
                            var string_id = $(val).attr('id');
                            if (order !== '') {
                                order += ',';
                            }
                            order += '"' + i + '":' + string_id.replace("post-", "");
                        });
                        order = '{' + order + '}';
                    } else {
                        $.each($('.attachments .attachment:not(.wpmf-attachment)'), function (i, val) {
                            if (order !== '') {
                                order += ',';
                            }
                            order += '"' + i + '":' + $(val).data('id');
                        });
                        order = '{' + order + '}';
                    }

                    // do re-order file
                    $.ajax({
                        type: "POST",
                        url: ajaxurl,
                        data: {
                            action: "wpmf",
                            task: "reorderfile",
                            order: order
                        },
                        success: function success(response) {
                            if (wpmfFoldersModule.page_type !== 'upload-list') {
                                wpmfFoldersModule.reloadAttachments();
                            }
                        }
                    });
                }
            });

            $(".attachments").disableSelection();
        },

        /**
         * Function called when an attachment is dropped in a folder
         * @param to_folder_id
         */
        droppedAttachment: function droppedAttachment(to_folder_id) {
            if ($(wpmfFoldersModule.dragging_elements).hasClass('wpmf-folder')) {
                // We're dropping a folder
                // Send request to move folder
                wpmfFoldersModule.moveFolder($(wpmfFoldersModule.dragging_elements).data('id'), to_folder_id);
            } else {
                // We're dropping an attachment
                var files_ids = [];

                // Retrieve the ids of files dragged
                $(wpmfFoldersModule.dragging_elements).each(function () {
                    if (wpmfFoldersModule.page_type === 'upload-list') {
                        files_ids.push($(this).next().find('input').attr('value'));
                    } else {
                        files_ids.push($(this).data('id'));
                    }
                });

                // Send request to move files
                wpmfFoldersModule.moveFile(files_ids, to_folder_id, wpmfFoldersModule.last_selected_folder);
            }
        },

        /**
         * Move a folder inside another folder
         *
         * @param folder_id int folder we're moving
         * @param folder_to_id int folder we're moving into
         * @return jqXHR
         */
        moveFolder: function moveFolder(folder_id, folder_to_id) {
            // Store parent id in order to use it in the undo function
            var parent_id = wpmfFoldersModule.categories[folder_id].parent_id;

            return $.ajax({
                type: "POST",
                url: ajaxurl,
                data: {
                    action: "wpmf",
                    task: "move_folder",
                    id: folder_id,
                    id_category: folder_to_id,
                    type: 'move' // todo: handle the undo feature
                },
                success: function success(response) {
                    if (response.status === true) {
                        // Update the categories variables
                        wpmfFoldersModule.categories = response.categories;
                        wpmfFoldersModule.categories_order = response.categories_order;

                        // Reload the folders
                        wpmfFoldersModule.renderFolders();

                        // Trigger event
                        wpmfFoldersModule.trigger('moveFolder', folder_id, folder_to_id);

                        // Show snackbar
                        wpmfSnackbarModule.show({
                            content: wpmf.l18n.wpmf_undo_movefolder,
                            is_undoable: true,
                            onUndo: function onUndo() {
                                // Move back to old folder
                                wpmfFoldersModule.moveFolder(folder_id, parent_id);
                            }
                        });
                    } else {
                        if (typeof response.wrong === "undefined") {
                            //todo: change wrong variable name to something more understandable like message or error_message, and what should we do if wrong is set?
                            showDialog({
                                title: wpmf.l18n.information,
                                text: wpmf.l18n.alert_add
                            });
                        }
                    }
                }
            });
        },

        /**
         * Move a file into a folder
         *
         * @param files_ids array(int) Array of files to move
         * @param folder_to_id int folder to move the files into
         * @param folder_from_id int folder we move the file from
         * @return jqXHR
         */
        moveFile: function moveFile(files_ids, folder_to_id, folder_from_id) {
            return $.ajax({
                type: "POST",
                url: ajaxurl,
                data: {
                    action: "wpmf",
                    task: "move_file",
                    ids: files_ids,
                    id_category: folder_to_id,
                    current_category: folder_from_id,
                    type: 'move' // todo: handle the undo feature
                },
                success: function success(response) {
                    if (response.status === true) {
                        if (wpmfFoldersModule.page_type === 'upload-list') {
                            // In list view reload the page
                            $('.upload-php #posts-filter').submit();
                            return;
                        }

                        // reload attachment after move file
                        $.each(files_ids, function (i, v) {
                            $('.attachment[data-id="' + v + '"]').remove();
                        });

                        var order_media = $('#media-order-media').val();
                        // if set custom media order filter
                        if (order_media === 'custom') {
                            setTimeout(function () {
                                wpmfFoldersModule.reloadAttachments();
                            }, 400);
                        } else {
                            wpmfFoldersModule.reloadAttachments();
                        }

                        // Reload the folders to update
                        wpmfFoldersModule.renderFolders();

                        // Show snackbar
                        wpmfSnackbarModule.show({
                            content: wpmf.l18n.wpmf_undo_movefile,
                            is_undoable: true,
                            onUndo: function onUndo() {
                                // Cancel moving files
                                wpmfFoldersModule.moveFile(files_ids, folder_from_id, folder_to_id);
                            }
                        });

                        wpmfFoldersModule.trigger('moveFile', files_ids, folder_to_id, folder_from_id);
                    }
                }
            });
        },

        /**
         *
         */
        initHoverImage: function initHoverImage() {
            // Return if the config do not allow it
            if (wpmfFoldersModule.hover_image === false) {
                return;
            }

            // todo : rewrite and comment this part
            var yOffset = 30;
            // these 2 variable determine popup's distance from the cursor
            // you might want to adjust to get the right result
            /* END CONFIG */
            wpmfFoldersModule.getFrame().find('.attachments-browser ul.attachments .attachment .thumbnail').unbind('hover').hover(function (e) {
                var $this = $(this);
                if ($this.closest('.attachment-preview').hasClass('type-image') && !$this.closest('.attachment').hasClass('uploading')) {
                    var id_img = $(this).closest('.attachment').data('id');
                    var ext = '!svg';
                    if (typeof wpmfFoldersModule.hover_images[id_img] === "undefined") {
                        /* Get some attribute */
                        var sizes = wp.media.attachment(id_img).get('sizes');
                        var title = wp.media.attachment(id_img).get('title');
                        var filename = wp.media.attachment(id_img).get('filename');
                        var width = 0;
                        if ($this.closest('.attachment-preview').hasClass('subtype-svg+xml')) {
                            var wpmfurl = $this.find('img').attr('src');
                            ext = 'svg';
                        } else {
                            if (typeof sizes !== "undefined") {
                                if (typeof sizes.medium !== "undefined" && typeof sizes.medium.url !== "undefined") {
                                    wpmfurl = sizes.medium.url;
                                    if (typeof sizes.medium.width !== "undefined") {
                                        width = sizes.medium.width;
                                    }
                                } else {
                                    wpmfurl = $this.find('img').attr('src');
                                    width = $this.find('img').width();
                                }
                            } else {
                                wpmfurl = $this.find('img').attr('src');
                                width = $this.find('img').width();
                            }
                        }

                        if (typeof title === "undefined") {
                            title = "";
                        }

                        if (typeof filename === "undefined") {
                            filename = "";
                        }
                        title = wpmfescapeScripts(title);
                        wpmfFoldersModule.hover_images[id_img] = {
                            'title': title,
                            'wpmfurl': wpmfurl,
                            'filename': filename,
                            'width': width,
                            'ext': ext
                        };
                    }
                    var html = "<div id='wpmf_preview_image'>";
                    if (wpmfFoldersModule.hover_images[id_img].ext === 'svg') {
                        html += "<div><img src='" + wpmfFoldersModule.hover_images[id_img].wpmfurl + "' width='300' /></div>";
                    } else {
                        html += "<div><img src='" + wpmfFoldersModule.hover_images[id_img].wpmfurl + "' /></div>";
                    }
                    html += "<span class='bottomlegend'>";
                    html += "<span class='bottomlegend_filename'>";
                    html += wpmfFoldersModule.hover_images[id_img].filename;
                    html += "</span>";
                    html += "<br>";
                    html += "<span class='bottomlegend_filetitle'>";
                    html += wpmfFoldersModule.hover_images[id_img].title;
                    html += "</span>";
                    html += "</span>";
                    html += "</div>";
                    if ($('#wpmf_preview_image').length === 0) {
                        $("body").append(html);
                        $("#wpmf_preview_image").fadeIn("fast");
                        if (e.pageX + wpmfFoldersModule.hover_images[id_img].width > $('body').width()) {
                            $("#wpmf_preview_image").css("top", e.pageY - 100 - $("#wpmf_preview_image").height() + "px").css("left", e.pageX - wpmfFoldersModule.hover_images[id_img].width - 50 + "px").fadeIn("fast");
                        } else {
                            $("#wpmf_preview_image").css("top", e.pageY - 100 - $("#wpmf_preview_image").height() + "px").css("left", e.pageX + yOffset + "px").fadeIn("fast");
                        }
                    }
                }
            }, function () {
                $("#wpmf_preview_image").remove();
            });
        },

        addCreateGalleryBtn: function addCreateGalleryBtn() {
            if (parseInt(wpmf.vars.usegellery) === 1) {
                if ($('.btn-selectall').length === 0) {
                    var btnSelectAll = "<a href='#' class='button media-button button-primary button-large btn-selectall'>" + wpmf.l18n.create_gallery_folder + "</a>";
                    $('.button.media-button.button-primary.button-large.media-button-gallery').before(btnSelectAll);
                }

                if ($('.btn-selectall-gallery').length === 0) {
                    var btnSelectAll1 = "<a href='#' class='button media-button button-primary button-large btn-selectall-gallery'>" + wpmf.l18n.create_gallery_folder + "</a>";
                    $('.button.media-button.button-primary.button-large.media-button-insert').before(btnSelectAll1);
                }
            }
        },

        initRemoteVideo: function initRemoteVideo($current_frame) {
            if ($current_frame === undefined) {
                $current_frame = wpmfFoldersModule.getFrame();
            }
            // Ajax function which creates the video
            var create_remote_video = function create_remote_video() {
                var remote_link = $('.wpmf_remote_video_input').val();
                $.ajax({
                    type: "POST",
                    url: ajaxurl,
                    data: {
                        action: "wpmf",
                        task: "create_remote_video",
                        wpmf_remote_link: remote_link,
                        folder_id: wpmfFoldersModule.last_selected_folder
                    },
                    success: function success(response) {
                        if (response.status) {
                            wpmfFoldersModule.reloadAttachments();
                        } else {
                            showDialog({
                                title: wpmf.l18n.information,
                                text: response.msg,
                                closeicon: true
                            });
                        }
                    }
                });
            };

            // Add remote button
            if (!$current_frame.find('.media-frame-content .media-toolbar-secondary .wpmf_btn_remote_video').length) {
                $current_frame.find('.media-frame-content .media-toolbar-secondary').append('<i class="material-icons wpmf_icon_remote_video" data-for="' + wpmf.l18n.remote_video_tooltip + '">play_circle_outline</i>');
                wpmfFoldersModule.showTooltip();
            }

            // Add reload button
            if (!$current_frame.find('.media-frame-content .media-toolbar-secondary .wpmf_btn_reload').length) {
                $current_frame.find('.media-frame-content .media-toolbar-secondary').append('<i class="material-icons wpmf_btn_reload" data-for="' + wpmf.l18n.reload_media + '">refresh</i>');
                wpmfFoldersModule.showTooltip();
            }

            // Initialize main functionality
            $('.wpmf_btn_remote_video,.wpmf_icon_remote_video').unbind('click').click(function () {
                showDialog({
                    title: wpmf.l18n.remote_video,
                    text: '<input type="text" name="wpmf_remote_video_input" class="wpmf_remote_video_input">',
                    negative: {
                        title: wpmf.l18n.cancel
                    },
                    positive: {
                        title: wpmf.l18n.upload,
                        onClick: function onClick() {
                            create_remote_video();
                        }
                    }
                });

                $('.wpmf_newfolder_input').focus().keypress(function (e) {
                    if (e.which === 13) {
                        create_remote_video();
                        hideDialog(jQuery('#orrsDiag'));
                    }
                });
            });

            $('.wpmf_btn_reload').unbind('click').click(function () {
                wpmfFoldersModule.reloadAttachments();
            });
        },

        /**
         * Show the tooltip
         */
        showTooltip: function showTooltip() {
            $('.wpmf_icon_remote_video, .wpmf_btn_reload').qtip({
                content: {
                    attr: 'data-for'
                },
                position: {
                    my: 'bottom center',
                    at: 'top center'
                },
                style: {
                    tip: {
                        corner: false
                    },
                    classes: 'wpmf-qtip qtip-rounded'
                },
                show: 'hover',
                hide: {
                    fixed: true,
                    delay: 10
                }

            });
        },

        /**
         * Trigger an event
         * @param event string the event name
         * @param arguments
         */
        trigger: function trigger(event) {
            // Retrieve the list of arguments to send to the function
            var args = Array.prototype.slice.call(arguments).slice(1); // Cross browser compatible let args = Array.from(arguments).slice(1);

            // Retrieve registered function
            var events = wpmfFoldersModule.events[event];

            // For each registered function apply arguments
            if (events) {
                for (var i = 0; i < events.length; i++) {
                    events[i].apply(this, args);
                }
            }
        },

        /**
         * Subscribe to an or multiple events
         * @param events {string|array} event name
         * @param subscriber function the callback function
         */
        on: function on(events, subscriber) {
            // If event is a string convert it as an array
            if (typeof events === 'string') {
                events = [events];
            }

            // Allow multiple event to subscript
            for (var ij in events) {
                if (typeof subscriber === 'function') {
                    if (typeof wpmfFoldersModule.events[events[ij]] === "undefined") {
                        this.events[events[ij]] = [];
                    }
                    wpmfFoldersModule.events[events[ij]].push(subscriber);
                }
            }
        }
    };

    // add filter work with Easing Slider plugin
    if (wpmf.vars.base === 'toplevel_page_easingslider') {
        wpmfFoldersModule.initFolderFilter();
    }

    // Let's initialize WPMF features
    $(document).ready(function () {
        wpmfFoldersModule.initModule();
    });
})(jQuery);

/**
 * Escape string
 * @param s string
 */
var wpmfescapeScripts = function wpmfescapeScripts(s) {
    return s.replace(/&/g, "&amp;").replace(/</g, "&lt;").replace(/>/g, "&gt;").replace(/"/g, "&quot;").replace(/'/g, "&#039;");
};

/**
 * ECMAScript 5 repeat function
 * https://developer.mozilla.org/en-US/docs/Web/JavaScript/Reference/Global_Objects/String/repeat
 */
if (!String.prototype.repeat) {
    String.prototype.repeat = function (count) {
        'use strict';

        if (this == null) {
            throw new TypeError('can\'t convert ' + this + ' to object');
        }
        var str = '' + this;
        count = +count;
        if (count != count) {
            count = 0;
        }
        if (count < 0) {
            throw new RangeError('repeat count must be non-negative');
        }
        if (count == Infinity) {
            throw new RangeError('repeat count must be less than infinity');
        }
        count = Math.floor(count);
        if (str.length == 0 || count == 0) {
            return '';
        }
        // Ensuring count is a 31-bit integer allows us to heavily optimize the
        // main part. But anyway, most current (August 2014) browsers can't handle
        // strings 1 << 28 chars or longer, so:
        if (str.length * count >= 1 << 28) {
            throw new RangeError('repeat count must not overflow maximum string size');
        }
        var rpt = '';
        for (var i = 0; i < count; i++) {
            rpt += str;
        }
        return rpt;
    };
}

if (!Object.values) {
    Object.values = function objectValues(obj) {
        var res = [];
        for (var i in obj) {
            if (obj.hasOwnProperty(i)) {
                res.push(obj[i]);
            }
        }
        return res;
    };
}
