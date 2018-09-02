var selected_folder = null, curFolders = [], wpmf_list_import = '';
(function ($) {
    /**
     * Import nextgen gallery
     * @param doit true or false
     * @param button
     */
    var importWpmfgallery = function(doit,button){
        jQuery(button).closest("p").find(".spinner").show().css({"visibility":"visible"});
        jQuery.post(ajaxurl, {action: "import_gallery" , doit :doit}, function(response) {
            if(response === "error time"){
                jQuery("#wmpfImportgallery").click();
            }else{
                jQuery(button).closest("div#wpmf_error").hide();
                if(doit===true){
                    jQuery("#wpmf_error").after("<div class='updated'> <p><strong>NextGEN galleries successfully imported in WP Media Folder</strong></p></div>");
                }
            }
        });
    };

    /**
     * Import category
     * @param doit true or false
     * @param button
     */
    var importWpmfTaxo = function (doit, button) {
        jQuery(button).closest('div').find('.spinner').show().css('visibility', 'visible');
        $.ajax({
            type: "POST",
            url: ajaxurl,
            data: {
                action: "import_categories",
                doit: doit
            },
            success: function () {
                jQuery(button).closest('div').find('.spinner').hide();
                jQuery(button).closest('div').find('.wpmf_info_update').fadeIn(1000).delay(500).fadeOut(1000);
            }
        });
    };

    /**
     * Init event
     */
    var bindSelect = function () {
        /* show tooltip when hover label, button */
        jQuery('.wpmf_row_full label ,.content-box #wmpfImpoBtn').qtip({
            content: {
                attr: 'data-alt'
            },
            position: {
                my: 'bottom left',
                at: 'top center'
            },
            style: {
                tip: {
                    corner: true
                },
                classes: 'wpmf-qtip qtip-rounded'
            },
            show: 'hover',
            hide: {
                fixed: true,
                delay: 10
            }

        });

        /**
         * Remove list syng media
         */
        $('.btn_deletesync_media').on('click', function () {
            var list = [];
            $('[id^="cb-select-"]:checked').each(function (i, $this) {
                if ($($this).val() !== "on") {
                    list.push($($this).val());
                }
            });

            $.ajax({
                type: "POST",
                url: ajaxurl,
                dataType: 'json',
                data: {
                    action: "wpmf_remove_syncmedia",
                    key: list.toString()
                },
                success: function (response) {
                    if (response !== false) {
                        $.each(response, function (i, v) {
                            $('.wp-list-table-sync').find('tr[data-id="' + v + '"]').remove();
                        });
                    }
                }
            });
        });

        /**
         * check all list sync media
         */
        $('#cb-select-all').on('click', function () {
            if ($(this).attr('checked') === 'checked') {
                $('.wp-list-table-sync').find('[id^="cb-select-"]').prop('checked', true);
            } else {
                $('.wp-list-table-sync').find('[id^="cb-select-"]').prop('checked', false);
            }
        });

        /**
         * Add to list sync media
         */
        $('.btn_addsync_media').on('click', function () {
            var folder_ftp = $('.dir_name_ftp').val();
            var folder_category = $('.dir_name_categories').data('id_category');
            $.ajax({
                type: "POST",
                url: ajaxurl,
                dataType: 'json',
                data: {
                    action: "wpmf_add_syncmedia",
                    folder_ftp: folder_ftp,
                    folder_category: folder_category
                },
                success: function (response) {
                    var tr = '<tr data-id="' + response.folder_category + '">';
                    tr += '<td><input id="cb-select-' + response.folder_category + '" type="checkbox" name="post[]" value="' + response.folder_category + '"></td>';
                    tr += '<td>' + response.folder_ftp + '</td>';
                    tr += '<td>' + $('.dir_name_categories').val() + '</td>';
                    tr += '</tr>';
                    $('.wp-list-table-sync').find('tr[data-id="' + response.folder_category + '"]').remove();
                    $('.wp-list-table-sync').append(tr);
                }
            });
        });

        /**
         * change tab in settings
         */
        $('.wpmf-tab-header').on('click', function () {
            var $this = $(this);
            var label = $this.data('label');
            if (label === 'wpmf-regen-thumbnail' || label === 'wpmf-image-compression') {
                $('.btn_wpmf_saves').hide();
            } else {
                $('.btn_wpmf_saves').show();
            }
            $('.setting_tab_value').val(label);
            $('.wpmf-tab-header').removeClass('active');
            $this.addClass('active');
            $('.content-box').addClass('content-noactive').removeClass('content-active').hide();
            $('.content-' + label + '').addClass('content-active').removeClass('content-noactive').slideDown();
        });

        /**
         * FTP Import
         */
        $('#import_button').on('click', function () {
            var $this = $(this);
            $('.process_import_ftp_full').show();
            $this.parent('.btnoption').find('.spinner').show().css('visibility', 'initial');
            $.ajax({
                type: 'POST',
                url: ajaxurl,
                data: {
                    action: "wpmf_import_folder",
                    wpmf_list_import: wpmf_list_import
                },
                success: function (res) {
                    var w = $('.process_import_ftp').data('w');
                    if (typeof res.status !== "undefined" && res.status === 'error time') {
                        if (typeof res.percent !== "undefined") {
                            var new_w = parseFloat(w) + parseFloat(res.percent);
                            if (new_w > 100)
                                new_w = 100;
                            $('.process_import_ftp').data('w', new_w);
                            $('.process_import_ftp').css('width', new_w + '%');
                        }
                        $this.click();
                    } else {
                        $this.parent('.btnoption').find('.spinner').hide();
                        $this.parent('.btnoption').find('.info_import').fadeIn(500).fadeOut(3000);
                        $('.process_import_ftp_full').show();
                        $('.process_import_ftp').data('w', 0);
                        $('.process_import_ftp').css('width', '100%');

                        setTimeout(function () {
                            $('.process_import_ftp_full').hide();
                            $('.process_import_ftp').css('width', '0%');
                        }, 2000);


                    }
                }
            });
        });

        /**
         * Add custom weight in settings
         */
        $('#add_weight').on('click', function () {
            if (($('.wpmf_min_weight').val() === '') || ($('.wpmf_min_weight').val() === '' && $('.wpmf_max_weight').val() === '')) {
                $('.wpmf_min_weight').focus();
            } else if ($('.wpmf_max_weight').val() === '') {
                $('.wpmf_max_weight').focus();
            } else {
                $.ajax({
                    type: 'POST',
                    url: ajaxurl,
                    data: {
                        action: "wpmf_add_weight",
                        min_weight: $('.wpmf_min_weight').val(),
                        max_weight: $('.wpmf_max_weight').val(),
                        unit: $('.wpmfunit').val()
                    },
                    success: function (res) {
                        if (res !== false) {
                            var new_weight = '<li class="customize-control customize-control-select item_weight" style="display: list-item;" data-value="' + res.key + '" data-unit="' + res.unit + '">';
                            new_weight += '<input type="checkbox" name="weight[]" value="' + res.key + ',' + res.unit + '" data-unit="' + res.unit + '" >';
                            new_weight += '<span>' + res.label + '</span>';
                            new_weight += '<i class="zmdi zmdi-delete wpmf-delete" data-label="weight" data-value="' + res.key + '" data-unit="' + res.unit + '" title="' + wpmfoption.l18n.unweight + '"></i>';
                            new_weight += '<i class="zmdi zmdi-edit wpmf-md-edit" data-label="weight" data-value="' + res.key + '" data-unit="' + res.unit + '" title="' + wpmfoption.l18n.editweight + '"></i>';
                            new_weight += '</li>';
                            $('.content_list_fillweight li.weight').before(new_weight);
                        } else {
                            alert(wpmfoption.l18n.error);
                        }
                        $('li.weight input').val(null);
                        $('.wpmfunit option[value="kB"]').prop('selected', true).change();
                    }
                });
            }
        });

        /**
         * Add custom dimension in settings
         */
        $('#add_dimension').on('click', function () {
            if (($('.wpmf_width_dimension').val() === '') || ($('.wpmf_width_dimension').val() === '' && $('.wpmf_height_dimension').val() === '')) {
                $('.wpmf_width_dimension').focus();
            } else if ($('.wpmf_height_dimension').val() === '') {
                $('.wpmf_height_dimension').focus();
            } else {
                $.ajax({
                    type: 'POST',
                    url: ajaxurl,
                    data: {
                        action: "wpmf_add_dimension",
                        width_dimension: $('.wpmf_width_dimension').val(),
                        height_dimension: $('.wpmf_height_dimension').val()
                    },
                    success: function (res) {
                        if (res !== false) {
                            var new_dimension = '<li class="customize-control customize-control-select item_dimension" style="display: list-item;" data-value="' + res + '">';
                            new_dimension += '<input type="checkbox" name="dimension[]" value="' + res + '" >';
                            new_dimension += '<span>' + res + '</span>';
                            new_dimension += '<i class="zmdi zmdi-delete wpmf-delete" data-label="dimension" data-value="' + res + '" title="' + wpmfoption.l18n.undimension + '"></i>';
                            new_dimension += '<i class="zmdi zmdi-edit wpmf-md-edit" data-label="dimension" data-value="' + res + '" title="' + wpmfoption.l18n.editdimension + '"></i>';
                            new_dimension += '</li>';
                            $('.content_list_filldimension li.dimension').before(new_dimension);
                        } else {
                            alert(wpmfoption.l18n.error);
                        }
                        $('li.dimension input').val(null);
                    }
                });
            }
        });

        /**
         * remove custom weight/dimension in settings
         */
        $('.wpmf-delete').live('click', function () {
            var $this = $(this);
            var value = $this.data('value');
            var label = $this.data('label');
            var unit = $this.data('unit');
            if (label === 'dimension') {
                var action = 'wpmf_remove_dimension';
            } else {
                action = 'wpmf_remove_weight';
            }

            $.ajax({
                type: 'POST',
                url: ajaxurl,
                data: {
                    action: action,
                    value: value,
                    unit: unit
                },
                success: function (res) {
                    if (res === true) {
                        $this.closest('li').remove();
                    }
                }
            });
        });

        /**
         * edit custom weight/dimension in settings
         */
        $('.wpmfedit').live('click', function () {
            var $this = $(this);
            var label = $this.data('label');
            var current_value = $('#edit_' + label + '').data('value');
            var unit = $('.wpmfunit').val();
            if (label === 'dimension') {
                var new_value = $('.wpmf_width_dimension').val() + 'x' + $('.wpmf_height_dimension').val();
            } else {
                if (unit === 'kB') {
                    new_value = ($('.wpmf_min_weight').val() * 1024) + '-' + ($('.wpmf_max_weight').val() * 1024) + ',' + unit;
                } else {
                    new_value = ($('.wpmf_min_weight').val() * (1024 * 1024)) + '-' + ($('.wpmf_max_weight').val() * (1024 * 1024)) + ',' + unit;
                }
            }

            $.ajax({
                type: 'POST',
                url: ajaxurl,
                data: {
                    action: 'wpmf_edit',
                    label: label,
                    old_value: $this.data('value'),
                    new_value: new_value,
                    unit: unit
                },
                success: function (res) {
                    if (res !== false) {
                        if (label === 'dimension') {
                            $('li.item_' + label + '[data-value="' + current_value + '"]').find('.wpmf-delete').attr('data-value', res.value).data('value', res.value);
                            $('li.item_' + label + '[data-value="' + current_value + '"]').find('.wpmf-md-edit').attr('data-value', res.value).data('value', res.value);
                            $('li.item_' + label + '[data-value="' + current_value + '"]').find('input[name="' + label + '[]"]').val(res.value);
                            $('.content_list_filldimension li[data-value="' + current_value + '"]').find('span').html(new_value);
                            $('li.item_' + label + '[data-value="' + current_value + '"]').attr('data-value', res.value).data('value', res.value);
                        } else {
                            var cur_val = current_value.split(',');
                            $('li.item_' + label + '[data-value="' + cur_val[0] + '"]').find('.wpmf-delete').attr('data-value', res.value).data('value', res.value);
                            $('li.item_' + label + '[data-value="' + cur_val[0] + '"]').find('.wpmf-md-edit').attr('data-value', res.value).data('value', res.value);
                            $('li.item_' + label + '[data-value="' + cur_val[0] + '"]').find('input[name="' + label + '[]"]').val(res.value + ',' + cur_val[1]);
                            $('.content_list_fillweight li[data-value="' + cur_val[0] + '"]').find('span').html(res.label);
                            $('li.item_' + label + '[data-value="' + cur_val[0] + '"]').attr('data-value', res.value).data('value', res.value);
                        }

                    } else {
                        alert(wpmfoption.l18n.error);
                    }
                    $('.wpmf_can,#edit_' + label + '').hide();
                    $('#edit_' + label + '').attr('data-value', null).data('value', null);
                    $('#add_' + label + '').show();
                    $('li.' + label + ' input').val(null);
                }
            });
        });

        /**
         * open form custom weight/dimension in settings
         */
        $('.wpmf-md-edit').live('click', function () {
            var $this = $(this);
            var value = $this.data('value');
            var unit = $this.data('unit');
            var label = $this.data('label');
            $('.wpmf_can[data-label="' + label + '"]').show();
            $('#add_' + label + '').hide();

            if (label === 'dimension') {
                $('#edit_' + label + '').show().attr('data-value', value).data('value', value);
                var value_array = value.split('x');
                $('.wpmf_width_dimension').val(value_array[0]);
                $('.wpmf_height_dimension').val(value_array[1]);
            } else {
                $('#edit_' + label + '').show().attr('data-value', value + ',' + unit).data('value', value + ',' + unit);
                value_array = value.split('-');
                if (unit === 'kB') {
                    $('.wpmf_min_weight').val(value_array[0] / 1024);
                    $('.wpmf_max_weight').val(value_array[1] / 1024);
                } else {
                    $('.wpmf_min_weight').val(value_array[0] / (1024 * 1024));
                    $('.wpmf_max_weight').val(value_array[1] / (1024 * 1024));
                }
                $('select.wpmfunit option[value="' + unit + '"]').prop('selected', true).change();
            }
        });

        /**
         * close form custom weight/dimension in settings
         */
        $('.wpmf_can').live('click', function () {
            var $this = $(this);
            var label = $this.data('label');
            $this.hide();
            $('#edit_' + label + '').hide();
            $('#edit_' + label + '').attr('data-value', null).data('value', null);
            $('#add_' + label + '').show();
            $('li.' + label + ' input').val(null);
            if (label === 'weight') {
                $('.wpmfunit option[value="kB"]').prop('selected', true).change();
            }
        });

        $('.wpmf-section-title').on('click', function () {
            var title = $(this).data('title');
            if ($(this).closest('li').hasClass('open')) {
                $('.content_list_' + title + '').slideUp('fast');
                $(this).closest('li').removeClass('open');
            } else {
                $('.content_list_' + title + '').slideDown('fast');
                $(this).closest('li').addClass('open')
            }
        });

        $('#wmpfImpoBtn').on('click', function () {
            importWpmfTaxo(true, this);
        });

        /* click import nextgen gallery button */
        $('.btn_import_gallery').on('click', function () {
            var $this = $(this);
            $('.btn_import_gallery').closest('div').find('.spinner').show().css('visibility', 'visible');
            $(this).addClass('button-primary');
            $.ajax({
                type: 'POST',
                url: ajaxurl,
                data: {
                    action: "import_gallery",
                    doit: true
                },
                success: function (res) {
                    if (res === 'error time') {
                        $this.click();
                    } else {
                        $('.btn_import_gallery').closest('div').find('.spinner').hide();
                    }
                }
            });
        });
    };

    $(document).ready(function () {
        var sdir = '/';
        /**
         * options
         * @type {{root: string, showroot: string, onclick: onclick, oncheck: oncheck, usecheckboxes: boolean, expandSpeed: number, collapseSpeed: number, expandEasing: null, collapseEasing: null, canselect: boolean}}
         */
        var options = {
            'root': '/',
            'showroot': '/',
            'onclick': function (elem, type, file) {},
            'oncheck': function (elem, checked, type, file) {
                if (file.substring(file.length - 1) === sdir) {
                    file = file.substring(0, file.length - 1);
                }
                if (file.substring(0, 1) === sdir) {
                    file = file.substring(1, file.length);
                }
                if (checked) {
                    if (file !== "" && curFolders.indexOf(file) === -1) {
                        curFolders.push(file);
                    }
                } else {

                    if (file !== "" && !$(elem).next().hasClass('pchecked')) {
                        var temp = [];
                        for (var i = 0; i < curFolders.length; i++) {
                            var curDir = curFolders[i];
                            if (curDir.indexOf(file) !== 0) {
                                temp.push(curDir);
                            }
                        }
                        curFolders = temp;
                    } else {
                        var index = curFolders.indexOf(file);
                        if (index > -1) {
                            curFolders.splice(index, 1);
                        }
                    }
                }
            },
            'usecheckboxes': true, //can be true files dirs or false
            'expandSpeed': 500,
            'collapseSpeed': 500,
            'expandEasing': null,
            'collapseEasing': null,
            'canselect': true
        };

        /**
         * Main folder tree function for FTP import feature
         * @type {{init: init, open: open, close: close, getchecked: getchecked, getselected: getselected}}
         */
        var methods = {
            /**
             * Folder tree init
             */
            init: function () {
                $thisftp = $('#wpmf_foldertree');
                if ($thisftp === 0) {
                    return;
                }

                if (options.showroot !== '') {
                    var checkboxes = '';
                    if (options.usecheckboxes === true || options.usecheckboxes === 'dirs') {
                        checkboxes = '<input type="checkbox" /><span class="check" data-file="' + options.root + '" data-type="dir"></span>';
                    }
                    $thisftp.html('<ul class="jaofiletree"><li class="drive directory collapsed selected">' + checkboxes + '<a href="#" data-file="' + options.root + '" data-type="dir">' + options.showroot + '</a></li></ul>');
                }
                openfolderftp(options.root);
            },
            /**
             * open folder tree by dir name
             * @param dir
             */
            open: function (dir) {
                openfolderftp(dir);
            },
            /**
             * close folder tree by dir name
             * @param dir
             */
            close: function (dir) {
                closedirftp(dir);
            },
            /**
             * Get checked
             * @returns {Array}
             */
            getchecked: function () {
                var list = [];
                var ik = 0;
                $thisftp.find('input:checked + a').each(function () {
                    list[ik] = {
                        type: $(this).attr('data-type'),
                        file: $(this).attr('data-file')
                    };
                    ik++;

                    var curDir = this.file;
                    if (curDir.substring(curDir.length - 1) === sdir) {
                        curDir = curDir.substring(0, curDir.length - 1);
                    }
                    if (curDir.substring(0, 1) === sdir) {
                        curDir = curDir.substring(1, curDir.length);
                    }
                    if (curFolders.indexOf(curDir) === -1) {
                        curFolders.push(curDir);
                    }
                });
                spanCheckInit();
                return list;
            },
            /**
             * Get selected
             * @returns {Array}
             */
            getselected: function () {
                var list = [];
                var ik = 0;
                $thisftp.find('li.selected > a').each(function () {
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
         * @param dir dir name
         * @param callback
         */
        var openfolderftp = function (dir , callback) {
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
                    $('#wpmf_foldertree').find('a[data-file="' + dir + '"]').parent().addClass('wait');
                }
            }).done(function (datas) {

                selected_folder = dir;
                ret = '<ul class="jaofiletree" style="display: none">';
                for (var ij = 0; ij < datas.length; ij++) {
                    if (datas[ij].type === 'dir') {
                        var classe = 'directory collapsed';
                        var isdir = '/';
                    } else {
                        classe = 'file ext_' + datas[ij].ext;
                        isdir = '';
                    }
                    ret += '<li class="' + classe + '">';
                    if (options.usecheckboxes === true || (options.usecheckboxes === 'dirs' && datas[ij].type === 'dir') || (options.usecheckboxes === 'files' && datas[ij].type === 'file')) {
                        ret += '<input type="checkbox" data-file="' + dir + datas[ij].file + isdir + '" data-type="' + datas[ij].type + '" />';
                        var testFolder = dir + datas[ij].file;
                        if (testFolder.substring(0, 1) === '/') {
                            testFolder = testFolder.substring(1, testFolder.length);
                        }

                        if (curFolders.indexOf(testFolder) > -1) {
                            ret += '<span class="check checked" data-file="' + dir + datas[ij].file + isdir + '" data-type="' + datas[ij].type + '"></span>';
                        } else if (datas[ij].pchecked === true) {
                            ret += '<span class="check pchecked" data-file="' + dir + datas[ij].file + isdir + '" data-type="' + datas[ij].type + '" ></span>';
                        } else {
                            ret += '<span class="check" data-file="' + dir + datas[ij].file + isdir + '" data-type="' + datas[ij].type + '" ></span>';
                        }

                    } else {
                        
                    }
                    ret += '<a href="#" data-file="' + dir + datas[ij].file + isdir + '" data-type="' + datas[ij].type + '">' + datas[ij].file + '</a>';
                    ret += '</li>';
                }
                ret += '</ul>';

                $('#wpmf_foldertree').find('a[data-file="' + dir + '"]').parent().removeClass('wait').removeClass('collapsed').addClass('expanded');
                $('#wpmf_foldertree').find('a[data-file="' + dir + '"]').after(ret);
                $('#wpmf_foldertree').find('a[data-file="' + dir + '"]').next().slideDown(options.expandSpeed, options.expandEasing,
                    function () {
                        $thisftp.trigger('afteropen');
                        $thisftp.trigger('afterupdate');
                        if (typeof callback === 'function')
                            callback();
                    });

                seteventsftp();

                if (options.usecheckboxes) {
                    this.find('a[data-file="' + dir + '"]').parent().find('li input[type="checkbox"]').attr('checked', null);
                    for (ij = 0; ij < datas.length; ij++) {
                        testFolder = dir + datas[ij].file;
                        if (testFolder.substring(0, 1) === '/') {
                            testFolder = testFolder.substring(1, testFolder.length);
                        }
                        if (curFolders.indexOf(testFolder) > -1) {
                            this.find('input[data-file="' + dir + datas[ij].file + isdir + '"]').attr('checked', 'checked');
                        }
                    }

                    if (this.find('input[data-file="' + dir + '"]').is(':checked')) {
                        this.find('input[data-file="' + dir + '"]').parent().find('li input[type="checkbox"]').each(function () {
                            $(this).prop('checked', true).trigger('change');
                        });
                        this.find('input[data-file="' + dir + '"]').parent().find('li span.check').addClass("checked");
                    }

                }


            }).done(function () {
                //Trigger custom event
                $thisftp.trigger('afteropen');
                $thisftp.trigger('afterupdate');
            });

            wpmf_bindeventcheckbox($thisftp);
        };

        /**
         * remember checkbox
         * @param $thisftp
         */
        var wpmf_bindeventcheckbox = function ($thisftp) {
            $thisftp.find('li input[type="checkbox"]').bind('change', function () {
                var dir_checked = [];
                $('.directory span.check').each(function () {
                    if ($(this).hasClass('checked')) {
                        if ($(this).data('file') !== undefined) {
                            dir_checked.push($(this).data('file'));
                        }
                    }
                });

                var fchecked = [];
                fchecked.sort();
                for (var i = 0; i < dir_checked.length; i++) {
                    var curDir = dir_checked[i];
                    var valid = true;
                    for (var j = 0; j < i; j++) {
                        if (curDir.indexOf(dir_checked[j]) === 0) {
                            valid = false;
                        }
                    }
                    if (valid) {
                        fchecked.push(curDir);
                    }
                }

                wpmf_list_import = fchecked.toString();
                $.ajax({
                    type: "POST",
                    url: ajaxurl,
                    data: {
                        action: "wpmfjao_checked",
                        dir_checked: wpmf_list_import
                    }
                });
            });
        };

        /**
         * close folder tree by dir name
         * @param dir
         */
        var closedirftp = function (dir) {
            $thisftp.find('a[data-file="' + dir + '"]').next().slideUp(options.collapseSpeed, options.collapseEasing, function () {
                $(this).remove();
            });
            $thisftp.find('a[data-file="' + dir + '"]').parent().removeClass('expanded').addClass('collapsed');
            seteventsftp();

            //Trigger custom event
            $thisftp.trigger('afterclose');
            $thisftp.trigger('afterupdate');

        };

        /**
         * init event click to open/close folder tree
         */
        var seteventsftp = function () {
            $thisftp = $('#wpmf_foldertree');
            $thisftp.find('li a').unbind('click');
            //Bind userdefined function on click an element
            $thisftp.find('li a').bind('click', function () {

                options.onclick(this, $(this).attr('data-type'), $(this).attr('data-file'));
                if (options.usecheckboxes && $(this).attr('data-type') === 'file') {
                    $thisftp.find('li input[type="checkbox"]').attr('checked', null);
                    $(this).prev(':not(:disabled)').attr('checked', 'checked');
                    $(this).prev(':not(:disabled)').trigger('check');
                }
                if (options.canselect) {
                    $thisftp.find('li').removeClass('selected');
                    $(this).parent().addClass('selected');
                }
                return false;
            });
            //Bind checkbox check/uncheck
            $thisftp.find('li input[type="checkbox"]').bind('change', function () {
                options.oncheck(this, $(this).is(':checked'), $(this).next().attr('data-type'), $(this).next().attr('data-file'));
                if ($(this).is(':checked')) {
                    $(this).parent().find('li input[type="checkbox"]').attr('checked', 'checked');
                    $thisftp.trigger('check');
                } else {
                    $(this).parent().find('li input[type="checkbox"]').attr('checked', null);
                    $thisftp.trigger('uncheck');
                }

            });
            //Bind for collapse or expand elements
            $thisftp.find('li.directory.collapsed a').bind('click', function () {
                methods.open($(this).attr('data-file'));
                return false;
            });
            $thisftp.find('li.directory.expanded a').bind('click', function () {
                methods.close($(this).attr('data-file'));
                return false;
            });
        };

        /**
         * Folder tree function
         */
        methods.init();

        jQuery('#wpmf_foldertree').bind('afteropen', function () {
            methods.getchecked();
        });

        var spanCheckInit = function () {
            $("span.check").unbind('click').bind('click', function () {
                $(this).removeClass('pchecked');
                $(this).toggleClass('checked');
                if ($(this).hasClass('checked')) {
                    $(this).prev().prop('checked', true).trigger('change');
                } else {
                    $(this).prev().prop('checked', false).trigger('change');
                }
                setParentState(this);
                setChildrenState(this);
            });
        };

        var setParentState = function (obj) {
            var liObj = $(obj).parent().parent();
            var noCheck = 0, noUncheck = 0, totalEl = 0;
            liObj.find('li span.check').each(function () {

                if ($(this).hasClass('checked')) {
                    noCheck++;
                } else {
                    noUncheck++;
                }
                totalEl++;
            });

            if (parseInt(totalEl) === parseInt(noCheck)) {
                liObj.parent().children('span.check').addClass('pchecked');
                liObj.parent().children('input[type="checkbox"]').prop('checked', true).trigger('change');
            } else if (parseInt(totalEl) === parseInt(noUncheck)) {
                liObj.parent().children('span.check').removeClass('pchecked');
                liObj.parent().children('input[type="checkbox"]').prop('checked', false).trigger('change');
            } else {
                liObj.parent().children('span.check').addClass('pchecked');
                liObj.parent().children('input[type="checkbox"]').prop('checked', false).trigger('change');
            }

            if (liObj.parent().children('span.check').length > 0) {
                setParentState(liObj.parent().children('span.check'));
            }
        };

        var setChildrenState = function (obj) {
            if ($(obj).hasClass('checked')) {
                $(obj).parent().find('li span.check').removeClass('pchecked').addClass("checked");
                $(obj).parent().find('li input[type="checkbox"]').prop('checked', true).trigger('change');
            } else {
                $(obj).parent().find('li span.check').removeClass("checked");
                $(obj).parent().find('li input[type="checkbox"]').prop('checked', false).trigger('change');
            }
        };

        bindSelect();

        $('.wpmf_watermark_regeneration').on('click',function(){
            var $this = $(this);
            $('.process_watermark_thumb_full').show();
            $.ajax({
                url: ajaxurl,
                method: 'POST',
                dataType: 'json',
                data: {
                    action: 'wpmf_watermark_regeneration',
                    paged: $this.data('paged')
                },
                success: function (res) {
                    var w = $('.process_watermark_thumb').data('w');
                    if (res.status === 'ok') {
                        $('.process_watermark_thumb').data('w', 0);
                        $('.process_watermark_thumb').css('width', '100%');
                    }
                    $this.data('paged', parseInt(res.paged) + 1);
                    if (res.status === 'error_time') {
                        if (typeof res.percent !== "undefined") {
                            var new_w = parseFloat(w) + parseFloat(res.percent);
                            if (new_w > 100)
                                new_w = 100;
                            $('.process_watermark_thumb_full').show();
                            $('.process_watermark_thumb').data('w', new_w);
                            $('.process_watermark_thumb').css('width', new_w + '%');
                        }
                        $('.wpmf_watermark_regeneration').click();
                    }else{
                        $('.process_watermark_thumb_full').hide();
                    }
                }
            });
        });

        $('.wpmf_watermark_select_image').on('click',function(){
            if ( typeof frame !== "undefined" ) {
                frame.open();
                return;
            }

            // Create the media frame.
            var frame = wp.media({
                // Tell the modal to show only images.
                library: {
                    type: 'image'
                }
            });

            // When an image is selected, run a callback.
            frame.on( 'select', function() {
                // Grab the selected attachment.
                var attachment = frame.state().get('selection').first().toJSON();
                $('#wpmf_watermark_image').val(attachment.url);
                $('#wpmf_watermark_image_id').val(attachment.id);
            });

            frame.open();
        });

        $('.wpmf_watermark_clear_image').on('click',function(){
            $('#wpmf_watermark_image').val('');
            $('#wpmf_watermark_image_id').val(0);
        });

        /* import nextgen gallery */
        $('#wmpfImportgallery').on('click',function(){
            var $this = $(this);
            importWpmfgallery(true,$this);
        });

        $('.wmpfNoImportgallery').on('click',function(){
            var $this = $(this);
            importWpmfgallery(false,$this);
        });
    });
})(jQuery);