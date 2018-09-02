(function ($) {
    $(document).ready(function () {
        /**
         * options
         * @type {{root: string, showroot: string, onclick: onclick, oncheck: oncheck, usecheckboxes: boolean, expandSpeed: number, collapseSpeed: number, expandEasing: null, collapseEasing: null, canselect: boolean}}
         */
        var options_categories = {
            'root': '/',
            'showroot': wpmfoption.l18n.media_library,
            'onclick': function (elem, type, file) {
            },
            'oncheck': function (elem, checked, type, file) {
            },
            'usecheckboxes': false, //can be true files dirs or false
            'expandSpeed': 500,
            'collapseSpeed': 500,
            'expandEasing': null,
            'collapseEasing': null,
            'canselect': true
        };

        /**
         * Main folder tree of WPMF category function for sync feature
         * @type {{init: init, open_categories: open_categories, close_categories: close_categories}}
         */
        var methods_categories = {
            /**
             * Folder tree init
             */
            init: function () {
                $thiscategories = $('#wpmf_foldertree_categories');
                if ($thiscategories.length === 0) {
                    return;
                }
                if (options_categories.showroot !== '') {
                    $thiscategories.html('<ul class="jaofiletree"><li class="drive directory_library collapsed_library selected"><a href="#" data-file="' + options_categories.root + '" data-type="dir">' + options_categories.showroot + '</a></li></ul>');
                }
                openfolder_categories(options_categories.root);
            },
            /**
             * open folder tree by dir name
             * @param dir
             */
            open_categories: function (dir) {
                openfolder_categories(dir);
            },
            /**
             * close folder tree by dir name
             * @param dir
             */
            close_categories: function (dir) {
                closedir_categories(dir);
            }
        };

        /**
         * open folder tree by dir name
         * @param dir dir name
         * @param callback
         */
        var openfolder_categories = function (dir , callback) {
            if (typeof $thiscategories === "undefined")
                return;

            var id = $thiscategories.find('a[data-file="' + dir + '"]').data('id');
            if ($thiscategories.find('a[data-file="' + dir + '"]').parent().hasClass('expanded_library') || $thiscategories.find('a[data-file="' + dir + '"]').parent().hasClass('wait')) {
                if (typeof callback === 'function')
                    callback();
                return;
            }

            if(typeof id === 'undefined') id = 0;
            var ret;
            ret = $.ajax({
                url: ajaxurl,
                method: 'POST',
                data: {
                    action: 'wpmf',
                    task: 'get_terms',
                    dir: dir,
                    id: id,
                    wpmf_display_media: 'all'
                },
                context: $thiscategories,
                dataType: 'json',
                beforeSend: function () {
                    $('#wpmf_foldertree_categories').find('a[data-file="' + dir + '"]').parent().addClass('wait');
                }
            }).done(function (datas) {
                var selectedId = $('#wpmf_foldertree_categories').find('.directory_library.selected').data('id');
                ret = '<ul class="jaofiletree" style="display: none">';
                for (var ij = 0; ij < datas.length; ij++) {
                    if(parseInt(wpmfoption.vars.root_media_root) !== parseInt(datas[ij].id)) {
                        var classe = '';
                        if (datas[ij].type === 'dir') {
                            classe = 'directory_library collapsed_library';
                        } else {
                            classe = 'file ext_' + datas[ij].ext;
                        }

                        if (datas[ij].id === id.toString()) {
                            classe += ' selected';
                        }

                        ret += '<li class="' + classe + '" data-id="' + datas[ij].id + '" data-parent_id="' + datas[ij].parent_id + '" data-group="' + datas[ij].term_group + '">';
                        if (datas[ij].count_child > 0) {
                            ret += '<div class="icon-open-close" data-id="' + datas[ij].id + '" data-parent_id="' + datas[ij].parent_id + '" data-file="' + dir + datas[ij].file + '/" data-type="' + datas[ij].type + '"></div>';
                        } else {
                            ret += '<div class="icon-open-close" data-id="' + datas[ij].id + '" data-parent_id="' + datas[ij].parent_id + '" data-file="' + dir + datas[ij].file + '/" data-type="' + datas[ij].type + '" style="opacity:0"></div>';
                        }

                        ret += '<i class="zmdi zmdi-folder"></i>';
                        ret += '<a href="#" class="title-folder" data-id="' + datas[ij].id + '" data-parent_id="' + datas[ij].parent_id + '" data-file="' + dir + datas[ij].file + '/" data-type="' + datas[ij].type + '">' + datas[ij].file + '</a>';
                        ret += '</li>';
                    }
                }
                ret += '</ul>';
                $('#wpmf_foldertree_categories').find('a[data-file="' + dir + '"]').parent().removeClass('wait').removeClass('collapsed_library').addClass('expanded_library');
                $('#wpmf_foldertree_categories').find('a[data-file="' + dir + '"]').after(ret);
                $('#wpmf_foldertree_categories').find('a[data-file="' + dir + '"]').next().slideDown(options_categories.expandSpeed, options_categories.expandEasing,
                    function () {
                        $thiscategories.trigger('afteropen');
                        $thiscategories.trigger('afterupdate');
                        if (typeof callback === 'function')
                            callback();
                    });

                $('.dir_name_categories').val(dir.replace("\\", "/")).data('id_category' , id);
                setevents_categories();

            }).done(function () {
                $thiscategories.trigger('afteropen');
                $thiscategories.trigger('afterupdate');
            });

        };

        /**
         * close folder tree by dir name
         * @param dir
         */
        var closedir_categories = function (dir) {
            $thiscategories.find('a[data-file="' + dir + '"]').next().slideUp(options_categories.collapseSpeed, options_categories.collapseEasing, function () {
                $(this).remove();
            });
            $thiscategories.find('a[data-file="' + dir + '"]').parent().removeClass('expanded_library').addClass('collapsed_library');
            $('.dir_name_categories').val('').data('id_category' , 0);
            setevents_categories();

            //Trigger custom event
            $thiscategories.trigger('afterclose');
            $thiscategories.trigger('afterupdate');

        };

        /**
         * init event click to open/close folder tree
         */
        var setevents_categories = function () {
            $thiscategories = $('#wpmf_foldertree_categories');
            $thiscategories.find('li a, li .icon-open-close').unbind('click');
            //Bind userdefined function on click an element
            $thiscategories.find('li a').bind('click', function (e) {
                e.preventDefault();
                if (!$(this).hasClass('wpmfaddFolder')) {
                    var id = $(this).data('id');
                    $thiscategories.find('li').removeClass('selected');
                    $thiscategories.find('i.zmdi').removeClass('wpmf-zmdi-folder-open').addClass("zmdi-folder");
                    $(this).closest('li').addClass("selected");
                    $(this).closest('li').find('> i.zmdi').removeClass("zmdi-folder").addClass("wpmf-zmdi-folder-open");
                    methods_categories.open_categories($(this).attr('data-file'));
                }

                return false;
            });

            //Bind for collapse or expand elements
            $thiscategories.find('li.directory_library.collapsed_library .icon-open-close').bind('click', function () {
                methods_categories.open_categories($(this).attr('data-file'));
                return false;
            });
            $thiscategories.find('li.directory_library.expanded_library .icon-open-close').bind('click', function () {
                methods_categories.close_categories($(this).attr('data-file'));
                return false;
            });
        };

        /**
         * Folder tree function
         */
        methods_categories.init();
    });
})(jQuery);