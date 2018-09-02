(function ($) {
    $(document).ready(function () {
        /**
         * options
         * @type {{root: string, showroot: string, onclick: onclick, oncheck: oncheck, usecheckboxes: boolean, expandSpeed: number, collapseSpeed: number, expandEasing: null, collapseEasing: null, canselect: boolean}}
         */
        var options_sync = {
            'root': '/',
            'showroot': '/',
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
         * Main folder tree of ftp function for sync feature
         */
        var methods_sync = {
            init_sync: function () {
                $thisftp = $('#wpmf_foldertree_sync');
                if ($thisftp.length === 0) {
                    return;
                }

                if (options_sync.showroot !== '') {
                    $thisftp.html('<ul class="jaofiletree"><li class="drive directory collapsed selected"><a href="#" data-file="' + options_sync.root + '" data-type="dir">' + options_sync.showroot + '</a></li></ul>');
                }
                openfolder_sync(options_sync.root);
            },
            /**
             * open folder tree by dir name
             * @param dir
             */
            open_sync: function (dir) {
                openfolder_sync(dir);
            },
            /**
             * close folder tree by dir name
             * @param dir
             */
            close_sync: function (dir) {
                closedir_sync(dir);
            }
        };

        /**
         * open folder tree by dir name
         * @param dir dir name
         * @param callback
         */
        var openfolder_sync = function (dir , callback) {
            if ($thisftp.find('a[data-file="' + dir + '"]').parent().hasClass('expanded')) {
                return;
            }

            if ($thisftp.find('a[data-file="' + dir + '"]').parent().hasClass('expanded') || $thisftp.find('a[data-file="' + dir + '"]').parent().hasClass('wait')) {
                if (typeof callback === 'function')
                    callback();
                return;
            }
            var ret;
            ret = $.ajax({
                url: ajaxurl,
                method:'POST',
                data: {dir: dir, action: 'wpmf_get_folder'},
                context: $thisftp,
                dataType: 'json',
                beforeSend: function () {
                    $('#wpmf_foldertree_sync').find('a[data-file="' + dir + '"]').parent().addClass('wait');
                }
            }).done(function (datas) {
                ret = '<ul class="jaofiletree" style="display: none">';
                for (var ij = 0; ij < datas.length; ij++) {
                    if (datas[ij].type == 'dir') {
                        var classe = 'directory collapsed';
                        var isdir = '/';
                    } else {
                        classe = 'file ext_' + datas[ij].ext;
                        isdir = '';
                    }
                    ret += '<li class="' + classe + '">';
                    ret += '<a href="#" data-file="' + dir + datas[ij].file + isdir + '" data-type="' + datas[ij].type + '">' + datas[ij].file + '</a>';
                    ret += '</li>';
                }
                ret += '</ul>';

                $('#wpmf_foldertree_sync').find('a[data-file="' + dir + '"]').parent().removeClass('wait').removeClass('collapsed').addClass('expanded');
                $('#wpmf_foldertree_sync').find('a[data-file="' + dir + '"]').after(ret);
                $('#wpmf_foldertree_sync').find('a[data-file="' + dir + '"]').next().slideDown(options_sync.expandSpeed, options_sync.expandEasing,
                    function () {
                        $thisftp.trigger('afteropen');
                        $thisftp.trigger('afterupdate');
                        if (typeof callback === 'function')
                            callback();
                    });
                $('.dir_name_ftp').val(wpmfoption.vars.wpmf_root_site + dir);
                setevents_sync();
            }).done(function () {
                //Trigger custom event
                $thisftp.trigger('afteropen');
                $thisftp.trigger('afterupdate');
            });

        };

        /**
         * close folder tree by dir name
         * @param dir
         */
        var closedir_sync = function (dir) {
            $thisftp.find('a[data-file="' + dir + '"]').next().slideUp(options_sync.collapseSpeed, options_sync.collapseEasing, function () {
                $(this).remove();
            });
            $thisftp.find('a[data-file="' + dir + '"]').parent().removeClass('expanded').addClass('collapsed');
            $('.dir_name_ftp').val('');
            setevents_sync();

            //Trigger custom event
            $thisftp.trigger('afterclose');
            $thisftp.trigger('afterupdate');

        };

        /**
         * init event click to open/close folder tree
         */
        var setevents_sync = function () {
            $thisftp = $('#wpmf_foldertree_sync');
            $thisftp.find('li a').unbind('click');
            //Bind userdefined function on click an element
            $thisftp.find('li a').bind('click', function () {
                options_sync.onclick(this, $(this).attr('data-type'), $(this).attr('data-file'));
                if (options_sync.canselect) {
                    $thisftp.find('li').removeClass('selected');
                    $(this).parent().addClass('selected');
                }
                return false;
            });

            //Bind for collapse or expand elements
            $thisftp.find('li.directory.collapsed a').bind('click', function () {
                methods_sync.open_sync($(this).attr('data-file'));
                return false;
            });
            $thisftp.find('li.directory.expanded a').bind('click', function () {
                methods_sync.close_sync($(this).attr('data-file'));
                return false;
            });
        };

        /**
         * Folder tree function
         */
        methods_sync.init_sync();
    });
})(jQuery);