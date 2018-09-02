(function ($) {
    $(document).ready(function () {
        /**
         * options
         * @type {{root: string, showroot: string, onclick: onclick, oncheck: oncheck, usecheckboxes: boolean, expandSpeed: number, collapseSpeed: number, expandEasing: null, collapseEasing: null, canselect: boolean}}
         */
        var optionsuser = {
            'root': '/',
            'showroot': wpmfoption.l18n.media_library,
            'onclick': function (elem, type, file) {
            },
            'oncheck': function (elem, checked, type, file) {
            },
            'usecheckboxes': true, //can be true files dirs or false
            'expandSpeed': 500,
            'collapseSpeed': 500,
            'expandEasing': null,
            'collapseEasing': null,
            'canselect': true
        };

        /**
         * Main folder tree function for user media root feature
         * @type {{init: init, open: open, close: close, getchecked: getchecked, getselected: getselected}}
         */
        var methods_users = {
            /**
             * Folder tree init
             */
            init: function () {
                $userimagetree = $('#wpmfjaouser');
                if ($userimagetree.length === 0) {
                    return;
                }

                var attachment_id = $('.attachment-details').data('id');
                if (typeof attachment_id === "undefined")
                    attachment_id = $('#post_ID').val();

                if (optionsuser.showroot !== '') {
                    var tree_init = '';
                    tree_init += '<ul class="jaofiletree">';
                    tree_init += '<li data-id="0" class="directory_users collapsed_users selected">';
                    tree_init += '<div class="pure-checkbox">';

                    tree_init += '<input type="checkbox" value="0" id="/" name="wpmf_checkbox_tree" class="wpmf_checkbox_tree" data-file="/" data-type="dir">';

                    tree_init += '<label class="checked" for="/">';
                    tree_init += '<a class="title-folder title-root" data-id="0" data-file="' + optionsuser.root + '" data-type="dir">' + optionsuser.showroot + '</a>';
                    tree_init += '</label>';
                    tree_init += '</div>';
                    tree_init += '</li>';
                    tree_init += '</ul>';
                    $userimagetree.html(tree_init);
                }
                openfolderuser(attachment_id, optionsuser.root);

            },
            /**
             * open folder tree by dir name
             * @param dir
             */
            open: function (dir) {
                var attachment_id = $('.attachment-details').data('id');
                if (typeof attachment_id === "undefined")
                    attachment_id = $('#post_ID').val();
                openfolderuser(attachment_id, dir);
            },
            /**
             * close folder tree by dir name
             * @param dir
             */
            close: function (dir) {
                closediruser(dir);
            },
            /**
             * Get checked
             * @returns {Array}
             */
            getchecked: function () {
                var list = [];
                var ik = 0;
                $userimagetree.find('input:checked + a').each(function () {
                    list[ik] = {
                        type: $(this).attr('data-type'),
                        file: $(this).attr('data-file')
                    };
                    ik++;
                });
                return list;
            },
            /**
             * Get selected
             * @returns {Array}
             */
            getselected: function () {
                var list = [];
                var ik = 0;
                $userimagetree.find('li.selected > a').each(function () {
                    list[ik] = {
                        type: $(this).attr('data-type'),
                        file: $(this).attr('data-file')
                    };
                    ik++;
                });
                return list;
            }
        };

        /**
         * open folder tree by dir name
         * @param attachment_id attachment id
         * @param dir dir name
         * @param callback
         */
        var openfolderuser = function (attachment_id, dir, callback) {
            if (typeof $userimagetree === "undefined")
                return;
            var id = $userimagetree.find('a[data-file="' + dir + '"]').data('id');
            if ($userimagetree.find('a[data-file="' + dir + '"]').closest('li').hasClass('expanded_users') || $userimagetree.find('a[data-file="' + dir + '"]').closest('li').hasClass('wait')) {
                if (typeof callback === 'function')
                    callback();
                return;
            }
            /* Ajax get user media */
            var ret;
            ret = $.ajax({
                method: 'POST',
                url: ajaxurl,
                data: {
                    dir: dir,
                    id: id,
                    attachment_id: attachment_id,
                    action: 'wpmf',
                    task: 'get_user_media_tree'
                },
                context: $userimagetree,
                dataType: 'json',
                beforeSend: function () {
                    this.find('a[data-file="' + dir + '"]').closest('li').addClass('wait');
                }
            }).done(function (res) {

                var selectedId = $('#wpmfjaouser').find('.directory_users.selected').data('id');
                ret = '<ul class="jaofiletree">';
                if (res.status) {
                    var datas = res.dirs;
                    for (var ij = 0; ij < datas.length; ij++) {
                        if (parseInt(wpmfoption.vars.root_media_root) !== datas[ij].id) {
                            var classe = '';
                            if (datas[ij].type === 'dir') {
                                classe = 'directory_users collapsed_users';
                            } else {
                                classe = 'file ext_' + datas[ij].ext;
                            }

                            if (parseInt(datas[ij].id) === parseInt(selectedId)) {
                                classe += ' selected';
                            }

                            ret += '<li class="' + classe + '" data-id="' + datas[ij].id + '" data-parent_id="' + datas[ij].parent_id + '" data-group="' + datas[ij].term_group + '">';
                            if (datas[ij].count_child > 0) {
                                ret += '<div class="icon-open-close" data-id="' + datas[ij].id + '" data-parent_id="' + datas[ij].parent_id + '" data-file="' + dir + datas[ij].file + '/" data-type="' + datas[ij].type + '"></div>';
                            } else {
                                ret += '<div class="icon-open-close" data-id="' + datas[ij].id + '" data-parent_id="' + datas[ij].parent_id + '" data-file="' + dir + datas[ij].file + '/" data-type="' + datas[ij].type + '" style="opacity:0"></div>';
                            }

                            ret += '<div class="pure-checkbox">';

                            if (res.user_media_folder_root === datas[ij].id) {
                                ret += '<input type="checkbox" checked id="' + dir + datas[ij].file + '/" name="wpmf_checkbox_tree" class="wpmf_checkbox_tree" value="' + datas[ij].id + '" data-id="' + datas[ij].id + '" data-file="' + dir + datas[ij].file + '" data-type="' + datas[ij].type + '">';
                            } else {
                                ret += '<input type="checkbox" id="' + dir + datas[ij].file + '/" name="wpmf_checkbox_tree" class="wpmf_checkbox_tree" value="' + datas[ij].id + '" data-id="' + datas[ij].id + '" data-file="' + dir + datas[ij].file + '" data-type="' + datas[ij].type + '">';
                            }

                            if (datas[ij].checked) {
                                ret += '<label class="check" for="' + dir + datas[ij].file + '/">';
                            } else {
                                if (datas[ij].pchecked) {
                                    ret += '<label class="pchecked" for="' + dir + datas[ij].file + '/">';
                                    ret += '<span class="ppp"></span>';
                                } else {
                                    ret += '<label for="' + dir + datas[ij].file + '/">';
                                }
                            }

                            if (parseInt(datas[ij].id) === parseInt(selectedId)) {
                                ret += '<i class="zmdi wpmf-zmdi-folder-open"></i>';
                            } else {
                                ret += '<i class="zmdi zmdi-folder"></i>';
                            }
                            ret += '<a class="title-folder" data-id="' + datas[ij].id + '" data-parent_id="' + datas[ij].parent_id + '" data-file="' + dir + datas[ij].file + '/" data-type="' + datas[ij].type + '">' + datas[ij].file + '</a>';
                            ret += '</label>';
                            ret += '</div>';
                            ret += '</li>';
                        }
                    }
                }
                ret += '</ul>';

                this.find('a[data-file="' + dir + '"]').closest('li').removeClass('wait').removeClass('collapsed_users').addClass('expanded_users');
                this.find('a[data-file="' + dir + '"]').closest('li').append(ret);
                this.find('a[data-file="' + dir + '"]').closest('li').children('.jaofiletree').slideDown(optionsuser.expandSpeed, optionsuser.expandEasing,
                    function () {
                        $userimagetree.trigger('afteropen');
                        $userimagetree.trigger('afterupdate');
                        if (typeof callback === 'function')
                            callback();
                    });

                seteventsuser();

            }).done(function () {
                $userimagetree.trigger('afteropen');
                $userimagetree.trigger('afterupdate');
            });

        };

        /**
         * close folder tree by dir name
         * @param dir
         */
        var closediruser = function (dir) {

            if (typeof $userimagetree === "undefined")
                return;
            $userimagetree.find('a[data-file="' + dir + '"]').closest('li').children('.jaofiletree').slideUp(optionsuser.collapseSpeed, optionsuser.collapseEasing, function () {
                $(this).remove();
            });

            $userimagetree.find('a[data-file="' + dir + '"]').closest('li').removeClass('expanded_users').addClass('collapsed_users');
            seteventsuser();

            //Trigger custom event
            $userimagetree.trigger('afterclose');
            $userimagetree.trigger('afterupdate');
        };

        /**
         * init event click to open/close folder tree
         */
        var seteventsuser = function () {
            var $userimagetree = $('#wpmfjaouser');
            $userimagetree.find('li a,li .icon-open-close').unbind('click');
            //Bind for collapse or expand elements
            $userimagetree.find('li.directory_users a').bind('click', function (e) {
                e.preventDefault();
                if (!$(this).hasClass('wpmfaddFolder')) {
                    $userimagetree.find('li').removeClass('selected');
                    $userimagetree.find('i.zmdi').removeClass('wpmf-zmdi-folder-open').addClass("zmdi-folder");
                    $(this).closest('li').addClass("selected");
                    $(this).closest('li').find(' > .pure-checkbox i.zmdi').removeClass("zmdi-folder").addClass("wpmf-zmdi-folder-open");
                    methods_users.open($(this).attr('data-file'));
                }

            });

            /* open folder tree use icon */
            $userimagetree.find('li.directory_users.collapsed_users .icon-open-close').bind('click', function () {
                methods_users.open($(this).attr('data-file'));
            });

            /* close folder tree use icon */
            $userimagetree.find('li.directory_users.expanded_users .icon-open-close').bind('click', function () {
                methods_users.close($(this).attr('data-file'));
            });

            /* Check/uncheck folder */
            $userimagetree.find('li.directory_users.expanded_users .wpmf_checkbox_tree').bind('click', function () {
                $('.wpmf_checkbox_tree').not($(this)).prop('checked', false);
                if ($(this).is(':checked')) {
                    $(this).closest('.pure-checkbox').find('label').removeClass('pchecked').addClass('checked');
                } else {
                    $(this).closest('.pure-checkbox').find('label').removeClass('checked');
                }
            });
        };

        /**
         * Folder tree function
         */
        methods_users.init();
    });
}(jQuery));