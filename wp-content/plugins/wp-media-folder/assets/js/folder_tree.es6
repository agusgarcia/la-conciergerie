/**
 * Folder tree for WP Media Folder
 */
let wpmfFoldersTreeModule;
(function($) {
    wpmfFoldersTreeModule =  {
        categories : [], // categories
        folders_states : [], // Contains open or closed status of folders
        show_files_count : false, // Whether or not show number of files contained in a folder
        last_scrolling_state : '', // Save the positioning while scrolling

        /**
         * Retrieve the Jquery tree view element
         * of the current frame
         * @return jQuery
         */
        getTreeElement : function() {
            return wpmfFoldersModule.getFrame().find('.wpmf-folder-tree').first();
        },

        /**
         * Initialize module related things
         */
        initModule : function($current_frame) {
            // Check if this frame has already the tree view
            let is_initialized = false;
            if (wpmfFoldersModule.page_type === 'upload-list') {
                is_initialized = $current_frame.find('.wpmf-folder-tree').length > 0;
            } else if ($current_frame.hasClass('hide-menu')) {
                is_initialized = $current_frame.find('.attachments-browser .wpmf-folder-tree').length > 0;
            } else {
                is_initialized = $current_frame.find('.media-menu .wpmf-folder-tree').length > 0;
            }

            if (is_initialized) {
                // Show folder tree in case it has been hidden previously
                wpmfFoldersTreeModule.getTreeElement().show();
                return;
            }

            // Initialize some variables
            wpmfFoldersTreeModule.show_files_count = wpmf.vars.option_countfiles === 1;

            // Import categories from wpmf main module
            wpmfFoldersTreeModule.importCategories();

            if (wpmfFoldersModule.page_type === 'upload-list') {
                // Wrap content
                $current_frame.children('ul.attachments, .screen-reader-text:nth-of-type(2), .tablenav.top, .wp-list-table, .tablenav.bottom').wrapAll('<div id="wpmf-wrapper"></div>');

                // Add the tree view to the main content
                $('<div class="wpmf-folder-tree"></div>').insertBefore($('#wpmf-wrapper'));
            } else if ($current_frame.hasClass('hide-menu')) {
                // Add the tree view to the main content
                $('<div class="wpmf-folder-tree"></div>').insertBefore($current_frame.find('.attachments-browser ul.attachments'));
            } else {
                // Add tree view to the left menu column
                $('<div class="wpmf-folder-tree"></div>').insertAfter($current_frame.find('.media-menu a:last-child'));
            }

            // Render the tree view
            wpmfFoldersTreeModule.loadTreeView();

            // Subscribe to the change folder event in main wpmf module
            wpmfFoldersModule.on('changeFolder', function(folder_id){
                wpmfFoldersTreeModule.changeFolder(folder_id);
            });

            // Subscribe to the add folder event in main wpmf module
            wpmfFoldersModule.on(['addFolder', 'deleteFolder', 'updateFolder', 'moveFolder'], function(folder){
                wpmfFoldersTreeModule.importCategories();
                wpmfFoldersTreeModule.loadTreeView();
                // Initialize folder tree resizing
                wpmfFoldersTreeModule.initContainerResizing($current_frame);
            });

            // Subscribe to the move file event in main wpmf module
            wpmfFoldersModule.on('moveFile', function(files_ids, folder_to_id, folder_from_id){
                // Update file count in main wpmf Module
                if (folder_from_id !== 0) {
                    wpmfFoldersModule.categories[folder_from_id].files_count -= files_ids.length;
                }

                if (folder_to_id !== 0) {
                    wpmfFoldersModule.categories[folder_to_id].files_count += files_ids.length;
                }

                // Import categories with updated count
                wpmfFoldersTreeModule.importCategories();

                // Reload tree view
                wpmfFoldersTreeModule.loadTreeView();
            });

            // Initialize the fixed tree view position on scrolling
            if (wpmf.vars.wpmf_pagenow === 'upload.php') {
                wpmfFoldersTreeModule.initFixedScrolling($current_frame);
            }

            // Subscribe to ordering folder filter
            wpmfFoldersFiltersModule.on('foldersOrderChanged', function(){
                wpmfFoldersTreeModule.importCategories();
                wpmfFoldersTreeModule.loadTreeView();
            });

            // Subscribe to gallery editing to hide folder tree
            wpmfFoldersModule.on('wpGalleryEdition', function(){
                wpmfFoldersTreeModule.getTreeElement().hide();
            });

            wpmfFoldersTreeModule.initContainerResizing($current_frame);

        },

        /**
         * Import categories from wpmf main module
         */
        importCategories : function() {
            let folders_ordered = [];

            // Add each category
            $(wpmfFoldersModule.categories_order).each(function() {
                folders_ordered.push(wpmfFoldersModule.categories[this]);
            });

            // Order the array depending on main ordering
            switch (wpmfFoldersModule.folder_ordering) {
                default:
                case 'name-ASC':
                    folders_ordered = Object.values(folders_ordered).sort(function(a, b){
                        if(a.id===0) return -1; // Root folder is always first
                        if(b.id===0) return 1; // Root folder is always first
                        return a.label.localeCompare(b.label);
                    });
                    break;
                case 'name-DESC':
                    folders_ordered = Object.values(folders_ordered).sort(function(a, b){
                        if(a.id===0) return -1; // Root folder is always first
                        if(b.id===0) return 1; // Root folder is always first
                        return b.label.localeCompare(a.label);
                    });
                    break;
                case 'id-ASC':
                    folders_ordered = Object.values(folders_ordered).sort(function(a, b){
                        if(a.id===0) return -1; // Root folder is always first
                        if(b.id===0) return 1; // Root folder is always first
                        return a.id - b.id;
                    });
                    break;
                case 'id-DESC':
                    folders_ordered = Object.values(folders_ordered).sort(function(a, b){
                        if(a.id===0) return -1; // Root folder is always first
                        if(b.id===0) return 1; // Root folder is always first
                        return b.id - a.id;
                    });
                    break;
            }

            // Reorder array based on children
            let folders_ordered_deep = [];
            let processed_ids = [];
            const loadChildren = function(id) {
                if(processed_ids.indexOf(id) < 0) {
                    processed_ids.push(id);
                    for (let ij=0; ij < folders_ordered.length; ij++) {
                        if(folders_ordered[ij].parent_id === id) {
                            folders_ordered_deep.push(folders_ordered[ij]);
                            loadChildren(folders_ordered[ij].id);
                        }
                    }
                }
            };
            loadChildren(0);

            // Finally save it to the global var
            wpmfFoldersTreeModule.categories = folders_ordered_deep;

        },

        /**
         * Render tree view inside content
         */
        loadTreeView : function() {
            wpmfFoldersTreeModule.getTreeElement().html(wpmfFoldersTreeModule.getRendering());

            // Throw a window resize event, so that WP recalculate the attachment width
            window.setTimeout(function(){
                $(window).trigger('resize');
            },200);

            let append_element;

            if (wpmfFoldersModule.page_type === 'upload-list') {
                append_element = '#posts-filter';
            } else {
                append_element = '.media-frame';
            }

            // Initialize dragping folder on tree view
            wpmfFoldersTreeModule.getTreeElement().find('ul a[data-id]').draggable({
                revert: true,
                helper: function(ui){
                    return $(ui.currentTarget).clone();
                },
                appendTo: append_element,
                delay: 100, // Prevent dragging when only trying to click
                distance: 10,
                cursorAt: {top: 0, left: 0},
                drag: function () {
                },
                start: function (event, ui) {
                    // Add the original size of element
                    $(ui.helper).css('width', $(ui.helper.context).outerWidth()+'px');
                    $(ui.helper).css('height', $(ui.helper.context).outerWidth()+'px');

                    // Add some style to original elements
                    $(this).addClass('wpmf-dragging');
                },
                stop: function (event, ui) {
                    // Revert style
                    $(this).removeClass('wpmf-dragging');
                }
            });

            // Initialize dropping folder on tree view
            wpmfFoldersTreeModule.getTreeElement().find('ul a[data-id]').droppable({
                hoverClass: "wpmf-hover-folder",
                tolerance: 'pointer',
                drop: function (event, ui) {
                    event.stopPropagation();
                    if (($(ui.draggable).hasClass('attachment') && !$(ui.draggable).hasClass('wpmf-attachment')) || $(ui.draggable).hasClass('wpmf-move')) {
                        // Transfer the event to the wpmf main module
                        wpmfFoldersModule.droppedAttachment($(this).data('id'));
                    } else {
                        // move folder with folder tree
                        wpmfFoldersModule.moveFolder($(ui.draggable).data('id'), $(this).data('id'));
                    }
                }
            });

            // Initialize change keyword to search folder
            wpmfFoldersTreeModule.getTreeElement().find('.searchfolder').on('click', function(e){
                wpmfFoldersTreeModule.doSearch();
            });

            // search with enter key
            $('.wpmf_search_folder').keypress(function (e) {
                if (e.which === 13) {
                    wpmfFoldersTreeModule.doSearch();
                    return false;
                }
            });

            // Initialize double click to folder title on tree view
            wpmfFoldersTreeModule.getTreeElement().find('ul a[data-id]').wpmfSingleDoubleClick(function () {
                // single click
                let id = $(this).data('id');
                wpmfFoldersModule.changeFolder(id);
            }, function (e) {
                // double click
                let id = $(this).data('id');
                wpmfFoldersModule.clickEditFolder(e, id);
                wpmfFoldersModule.houtside();
            });
        },

        /**
         *  Do search folder
         */
        doSearch: function() {
            wpmfFoldersModule.changeFolder(wpmfFoldersModule.last_selected_folder);
            // search on folder tree
            let keyword = $('.wpmf_search_folder').val();
            if (keyword !== '') {
                $('.wpmf-folder-tree li').addClass('folderhide');
                $.each(wpmfFoldersModule.folder_search, function (i, v) {
                    $('.wpmf-folder-tree li[data-id="' + v + '"]').addClass('foldershow').removeClass('folderhide closed');
                    $('.wpmf-folder-tree li[data-id="' + v + '"]').parents('.wpmf-folder-tree li').addClass('foldershow').removeClass('folderhide closed');
                });
            } else {
                $('.wpmf-folder-tree li').removeClass('foldershow folderhide');
            }
        },

        /**
         * Get the html resulting tree view
         * @return {string}
         */
        getRendering : function() {
            let ij = 0;
            let content = ''; // Final tree view content
            // render search folder box
            let search_folder = `
            <div class="wpmf-expandable-search mdl-cell--hide-phone">
                <form action="#">
                  <input type="search" class="wpmf_search_folder" placeholder="${wpmf.l18n.search_folder}" size="1">
                </form>
                <i class="material-icons searchfolder">search</i>
            </div>
            `;

            // get last status folder tree
            let lastStatusTree = wpmfFoldersModule.getCookie('lastStatusTree_' + wpmf.vars.site_url);
            if (lastStatusTree !== '') {
                lastStatusTree = JSON.parse(lastStatusTree);
            }

            /**
             * Recursively print list of folders
             * @return {boolean}
             */
            const generateList = function() {
                content += '<ul>';

                while (ij < wpmfFoldersTreeModule.categories.length) {
                    let className = '';
                    if (lastStatusTree.indexOf(wpmfFoldersTreeModule.categories[ij].id) !== -1) {
                        className += 'open ';
                    } else {
                        className += 'closed ';
                    }

                    // get last access folder
                    let lastAccessFolder = wpmfFoldersModule.getCookie('lastAccessFolder_' + wpmf.vars.site_url);
                    // Select the last element which was selected in wpmf main module
                    if (typeof lastAccessFolder === "undefined" || (typeof lastAccessFolder !== "undefined" && lastAccessFolder === '') || (typeof lastAccessFolder !== "undefined" && parseInt(lastAccessFolder) === 0) || typeof wpmfFoldersModule.categories[lastAccessFolder] === "undefined") {
                        if(wpmfFoldersTreeModule.categories[ij].id === wpmfFoldersModule.last_selected_folder) {
                            className += 'selected ';
                        }
                    } else {
                        if(wpmfFoldersTreeModule.categories[ij].id === parseInt(lastAccessFolder)) {
                            className += 'selected ';
                        }
                    }

                    // Open li tag
                    content += '<li class="' + className + '" data-id="' + wpmfFoldersTreeModule.categories[ij].id + '" >';

                    const a_tag = '<a data-id="'+wpmfFoldersTreeModule.categories[ij].id+'">';

                    // get color folder
                    let bgcolor = '';
                    if (typeof wpmf.vars.colors !== 'undefined' && typeof wpmf.vars.colors[wpmfFoldersTreeModule.categories[ij].id] !== 'undefined' && wpmfFoldersModule.folder_design === 'material_design') {
                        bgcolor = 'color: ' + wpmf.vars.colors[wpmfFoldersTreeModule.categories[ij].id];
                    } else {
                        bgcolor = 'color: #8f8f8f';
                    }

                    if(wpmfFoldersTreeModule.categories[ij+1]  && wpmfFoldersTreeModule.categories[ij+1].depth > wpmfFoldersTreeModule.categories[ij].depth) { // The next element is a sub folder
                        content += '<a onclick="wpmfFoldersTreeModule.toggle(' + wpmfFoldersTreeModule.categories[ij].id + ')"><i class="material-icons wpmf-arrow">keyboard_arrow_down</i></a>';

                        content += a_tag;

                        // Add folder icon
                        content += '<i class="material-icons" style="' + bgcolor+ '">folder</i>';
                    } else {
                        content += a_tag;

                        // Add folder icon
                        content += '<i class="material-icons wpmf-no-arrow" style="' + bgcolor+ '">folder</i>';
                    }

                    // Add current category name
                    if (wpmfFoldersTreeModule.categories[ij].id===0) {
                        // If this is the root folder then rename it
                        content += wpmf.l18n.media_folder;
                    } else{
                        content += '<span>' + wpmfFoldersTreeModule.categories[ij].label + '</span>';
                    }

                    content += '</a>';

                    if (wpmfFoldersTreeModule.show_files_count && wpmfFoldersTreeModule.categories[ij].files_count !== undefined) {
                        content +=  '<span data-files-count="' + wpmfFoldersTreeModule.categories[ij].files_count + '"></span>';
                    }

                    // This is the end of the array
                    if(wpmfFoldersTreeModule.categories[ij+1] === undefined) {
                        // Let's close all opened tags
                        for (let ik=wpmfFoldersTreeModule.categories[ij].depth; ik >= 0; ik--) {
                            content += '</li>';
                            content += '</ul>';
                        }

                        // We are at the end don't continue to process array
                        return false;
                    }


                    if(wpmfFoldersTreeModule.categories[ij+1].depth > wpmfFoldersTreeModule.categories[ij].depth) { // The next element is a sub folder
                        // Recursively list it
                        ij++;
                        if(generateList()===false) {
                            // We have reached the end, let's recursively end
                            return false;
                        }
                    } else if(wpmfFoldersTreeModule.categories[ij+1].depth < wpmfFoldersTreeModule.categories[ij].depth) { // The next element don't have the same parent
                        // Let's close opened tags
                        for (let ik=wpmfFoldersTreeModule.categories[ij].depth; ik > wpmfFoldersTreeModule.categories[ij+1].depth; ik--) {
                            content += '</li>';
                            content += '</ul>';
                        }

                        // We're not at the end of the array let's continue processing it
                        return true;
                    }

                    // Close the current element
                    content += '</li>';
                    ij++;
                }
            };

            // Start generation
            generateList();

            // Add the new folder button
            content = search_folder + '<a class="wpmf-new-folder" onclick="wpmfFoldersModule.newFolder(wpmfFoldersModule.last_selected_folder)"><i class="material-icons">create_new_folder</i>' + wpmf.l18n.create_folder + '</a>' + content;

            return content;
        },

        /**
         * Change the selected folder in tree view
         * @param folder_id
         */
        changeFolder : function(folder_id) {
            // Remove previous selection
            wpmfFoldersTreeModule.getTreeElement().find('li').removeClass('selected');

            // Select the folder
            wpmfFoldersTreeModule.getTreeElement().find('li[data-id="' + folder_id + '"]').addClass('selected').

            // Open parent folders
            parents('.wpmf-folder-tree li.closed').removeClass('closed');
        },

        /**
         * Toggle the open / closed state of a folder
         * @param folder_id
         */
        toggle : function(folder_id) {
            // get last status folder tree
            let lastStatusTree = [];
            // Check is folder has closed class
            if (wpmfFoldersTreeModule.getTreeElement().find('li[data-id="' + folder_id + '"]').hasClass('closed')) {
                // Open the folder
                wpmfFoldersTreeModule.openFolder(folder_id);
            } else {
                // Close the folder
                wpmfFoldersTreeModule.closeFolder(folder_id);
                // close all sub folder
                $('li[data-id="' + folder_id + '"]').find('li').addClass('closed');
            }

            wpmfFoldersTreeModule.getTreeElement().find('li:not(.closed)').each(function(i, v){
                let id = $(v).data('id');
                lastStatusTree.push(id);
            });
            // set last status folder tree
            wpmfFoldersModule.setCookie("lastStatusTree_" + wpmf.vars.site_url, JSON.stringify(lastStatusTree), 365);
        },


        /**
         * Open a folder to show children
         */
        openFolder : function(folder_id) {
            wpmfFoldersTreeModule.getTreeElement().find('li[data-id="' + folder_id + '"]').removeClass('closed');
            wpmfFoldersTreeModule.folders_states[folder_id] = 'open';
        },

        /**
         * Close a folder and hide children
         */
        closeFolder : function(folder_id) {
            wpmfFoldersTreeModule.getTreeElement().find('li[data-id="' + folder_id + '"]').addClass('closed');
            wpmfFoldersTreeModule.folders_states[folder_id] = 'close';
        },

        /**
         * Initialize the fixed position when user is scrolling
         * to keep the folder tree always visible
         */
        initFixedScrolling : function() {
            let $attachments_browser;
            if(wpmfFoldersModule.page_type === 'upload-list') {
                $attachments_browser = $('#wpmf-wrapper');
            } else {
                $attachments_browser = $('.attachments-browser ul.attachments');
            }

            setTimeout(function(){
                // Fix initial left margin in list view
                if(wpmfFoldersModule.page_type === 'upload-list') {
                    $('#wpmf-wrapper').css('margin-left', (wpmfFoldersTreeModule.getTreeElement().outerWidth() + 10) + 'px');
                    $('.rtl #wpmf-wrapper').css({'margin-right': (wpmfFoldersTreeModule.getTreeElement().outerWidth() + 10) + 'px', 'margin-left': 0});
                }

                // Get the position of folder tree in normal mode
                let original_top_position;
                if(wpmfFoldersModule.page_type === 'upload-list') {
                    original_top_position = $attachments_browser.offset().top;
                } else {
                    if (wpmfFoldersTreeModule.getTreeElement().length) {
                        original_top_position = wpmfFoldersTreeModule.getTreeElement().offset().top;
                    }
                }

                // Check on window scroll event
                $(window).on('scroll', function (e) {
                    // Check if the window has been scrolled more than the top position of the folder tree
                    if(original_top_position < this.scrollY + 40 && (window.outerHeight - window.innerHeight) < 100) {
                        // Check if folder tree was already in normal position
                        if(wpmfFoldersTreeModule.last_scrolling_state !== 'fixed') {
                            if(wpmfFoldersModule.page_type === 'upload-grid') {
                                // Add a margin to the attachments browser to keep the place on left side
                                $attachments_browser.css('margin-left', $attachments_browser.position().left + 30 + 'px');
                            }

                            // Set the folder tree in the fixed position
                            wpmfFoldersTreeModule.getTreeElement().css('position', 'fixed').css('top', '40px');

                            // Save the state
                            wpmfFoldersTreeModule.last_scrolling_state = 'fixed';
                        }
                    } else {
                        if(wpmfFoldersModule.page_type === 'upload-list') {
                            // Fix top positioning of folder tree
                            const top_position = $('#wpmf-breadcrumb').offset().top + $('#wpmf-breadcrumb').outerHeight() - this.scrollY;
                            wpmfFoldersTreeModule.getTreeElement().css('top', top_position);
                        }

                        // Check if folder tree was already in fixed position
                        if(wpmfFoldersTreeModule.last_scrolling_state !== 'initial') {
                            // Revert all fixed things
                            wpmfFoldersTreeModule.getTreeElement().css('position', '').css('top', '');

                            if(wpmfFoldersModule.page_type === 'upload-grid') {
                                $attachments_browser.css('margin-left', '');
                            }

                            // Save the state
                            wpmfFoldersTreeModule.last_scrolling_state = 'initial';
                        }
                    }

                    // Remove the loader on list page
                    if(wpmfFoldersModule.page_type === 'upload-list' && !$('.upload-php #posts-filter').hasClass('wpmf-not-loading')) {
                        setTimeout(function() {
                            $('.upload-php #posts-filter').addClass('wpmf-not-loading');
                        }, 200);
                    }
                });

                // Initialize all by simulating a scroll
                $(window).trigger('scroll');
            }, 200);
        },

        /**
         * Initialize folder tree resizing
         * @param $current_frame
         */
        initContainerResizing : function($current_frame) {
            let is_resizing = false;
            const $body = $('body');
            if (wpmf.vars.wpmf_pagenow === 'upload.php' && wpmfFoldersModule.page_type) {
                // Main upload.php page
                const $tree = $current_frame.find('.wpmf-folder-tree');
                const $handle = $('<div class="wpmf-folder-tree-resize"></div>').appendTo($tree);
                let $attachments;
                if (wpmfFoldersModule.page_type === 'upload-list') {
                    $attachments = $current_frame.find('#wpmf-wrapper');
                } else {
                    $attachments = $current_frame.find('.attachments');
                }


                $handle.on('mousedown', function (e) {
                    is_resizing = true;
                    $('body').css('user-select', 'none'); // prevent content selection while moving
                });

                $(document).on('mousemove', function (e) {
                    // we don't want to do anything if we aren't resizing.
                    if (!is_resizing)
                        return;

                    // Calculate tree width
                    $('.wpmf-folder-tree').css('max-width','100%');
                    const tree_width = (e.clientX - $tree.offset().left - 30);
                    $tree.css('width', tree_width+'px');

                    // We have to set margin if we are in a fixed tree position or in list page
                    if(wpmfFoldersTreeModule.last_scrolling_state === 'fixed' || wpmfFoldersModule.page_type === 'upload-list') {
                        $attachments.css('margin-left', (tree_width + 32)+'px');
                    }
                }).on('mouseup', function (e) {
                    if (is_resizing){
                        // stop resizing
                        is_resizing = false;
                        $body.css('user-select', '');
                        $(window).trigger('resize');
                    }
                });
            } else if ($current_frame.hasClass('hide-menu')) {
                // Modal window with no left menu
                const $tree = $current_frame.find('.wpmf-folder-tree');
                const $handle = $('<div class="wpmf-folder-tree-resize"></div>').insertAfter($tree);
                const $attachments = $current_frame.find('.attachments');
                const $uploader_inline = $current_frame.find('.uploader-inline');

                // Set handle initial position
                $handle.css({right : 'auto', left : ($attachments.position().left+10)+'px'});
                $uploader_inline.css('left', $attachments.position().left+'px');

                $handle.on('mousedown', function (e) {
                    is_resizing = true;
                    $body.css('user-select', 'none'); // prevent content selection while moving
                });

                $(document).on('mousemove', function (e) {
                    // we don't want to do anything if we aren't resizing.
                    if (!is_resizing)
                        return;

                    // Calculate tree width
                    $('.wpmf-folder-tree').css('max-width','100%');
                    const tree_width = (e.clientX - $tree.offset().left);

                    // Set positioning of the different elements
                    $tree.css('width', tree_width+'px');
                    $handle.css('left', (tree_width+10)+'px');

                    $attachments.css('left', (tree_width + 4)+'px');
                    $uploader_inline.css('left', (tree_width + 4)+'px');
                }).on('mouseup', function (e) {
                    if (is_resizing){
                        // stop resizing
                        is_resizing = false;
                        $('body').css('user-select', '');
                        $(window).trigger('resize');
                    }
                });
            } else {
                // Modal window with left menu
                const $menu = $current_frame.find('.media-frame-menu');
                const $handle = $('<div class="wpmf-folder-tree-resize"></div>').appendTo($menu);
                const $right_cols = $current_frame.find('.media-frame-content, .media-frame-router,  .media-frame-title, .media-frame-toolbar');

                $handle.on('mousedown', function (e) {
                    is_resizing = true;
                    $body.css('user-select', 'none'); // prevent content selection while moving
                });

                $(document).on('mousemove', function (e) {
                    // we don't want to do anything if we aren't resizing.
                    if (!is_resizing)
                        return;
                    $('.wpmf-folder-tree').css('max-width','100%');
                    const menu_width = (e.clientX - $menu.offset().left);

                    $menu.css('width', menu_width+'px');

                    $right_cols.css('left', (menu_width + 14)+'px');
                }).on('mouseup', function (e) {
                    if (is_resizing){
                        // stop resizing
                        is_resizing = false;
                        $body.css('user-select', '');
                        $(window).trigger('resize');
                    }
                });
            }

        }
    };

    // Let's initialize WPMF folder tree features
    $(document).ready(function () {
        if(wpmfFoldersModule.page_type === 'upload-list') {
            // Don't need to wait on list page
            wpmfFoldersTreeModule.initModule(wpmfFoldersModule.getFrame());
        } else {
            // Wait for the main wpmf module to be ready
            wpmfFoldersModule.on('ready', function ($current_frame) {
                wpmfFoldersTreeModule.initModule($current_frame);
            });
        }
    });
})(jQuery);

// call single click or double click on folder tree
jQuery.fn.wpmfSingleDoubleClick = function(single_click_callback, double_click_callback, timeout) {
    return this.each(function(){
        var clicks = 0, self = this;
        jQuery(this).click(function(event){
            clicks++;
            if (clicks === 1) {
                setTimeout(function(){
                    if(clicks === 1) {
                        single_click_callback.call(self, event);
                    } else {
                        double_click_callback.call(self, event);
                    }
                    clicks = 0;
                }, timeout || 300);
            }
        });
    });
};
