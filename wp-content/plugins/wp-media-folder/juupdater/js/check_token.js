(function ($) {
    if (typeof ajaxurl === "undefined") {
        ajaxurl = updaterparams.ajaxurl;
    }

    var ju_update_new = function (plugin, slug) {
        var $updateRow, $card, $message, message;
        if ('plugins' === pagenow || 'plugins-network' === pagenow) {
            $updateRow = $('tr[data-plugin="' + plugin + '"]');
            $message = $updateRow.find('.update-message').removeClass('notice-error').addClass('updating-message notice-warning').find('p');
            if(updaterparams.version === '4.8.0'){
                message = wp.updates.l10n.pluginUpdatingLabel.replace('%s', $updateRow.find('.plugin-title strong').text());
            }else{
                message = wp.updates.l10n.updatingLabel.replace('%s', $updateRow.find('.plugin-title strong').text());
            }
        } else if ('plugin-install' === pagenow || 'plugin-install-network' === pagenow) {
            $card = $('.plugin-card-' + slug);
            $message = $card.find('.update-now').addClass('updating-message');
            if(updaterparams.version === '4.8.0'){
                message = wp.updates.l10n.pluginUpdatingLabel.replace('%s', $message.data('name'));
            }else{
                message = wp.updates.l10n.updatingLabel.replace('%s', $message.data('name'));
            }

            // Remove previous error messages, if any.
            $card.removeClass('plugin-card-update-failed').find('.notice.notice-error').remove();
        }

        if ($message.html() !== wp.updates.l10n.updating) {
            $message.data('originaltext', $message.html());
        }

        $message
            .attr('aria-label', message)
            .text(wp.updates.l10n.updating);

        var args =  {
            plugin: plugin,
            slug:   slug
        };

        args = _.extend( {
            success: wp.updates.updatePluginSuccess,
            error: wp.updates.updatePluginError
        }, args );
        wp.updates.ajax( 'update-plugin', args );
    };

    var ju_update_old = function (plugin, slug) {
        var $message, name;
        if ('plugins' === pagenow || 'plugins-network' === pagenow) {
            $message = $('[data-slug="' + slug + '"]').next().find('.update-message');
        } else if ('plugin-install' === pagenow) {
            $message = $('.plugin-card-' + slug).find('.update-now');
            name = $message.data('name');
            if(updaterparams.version === '4.8.0'){
                $message.attr('aria-label', wp.updates.l10n.pluginUpdatingLabel.replace('%s', name));
            }else{
                $message.attr('aria-label', wp.updates.l10n.updatingLabel.replace('%s', name));
            }
        }

        $message.addClass('updating-message');
        if ($message.html() !== wp.updates.l10n.updating) {
            $message.data('originaltext', $message.html());
        }

        $message.text(wp.updates.l10n.updating);
        wp.a11y.speak(wp.updates.l10n.updatingMsg);

        if (wp.updates.updateLock) {
            wp.updates.updateQueue.push({
                type: 'update-plugin',
                data: {
                    plugin: plugin,
                    slug: slug
                }
            });
            return;
        }

        wp.updates.updateLock = true;

        var data = {
            _ajax_nonce: wp.updates.ajaxNonce,
            plugin: plugin,
            slug: slug,
            username: wp.updates.filesystemCredentials.ftp.username,
            password: wp.updates.filesystemCredentials.ftp.password,
            hostname: wp.updates.filesystemCredentials.ftp.hostname,
            connection_type: wp.updates.filesystemCredentials.ftp.connectionType,
            public_key: wp.updates.filesystemCredentials.ssh.publicKey,
            private_key: wp.updates.filesystemCredentials.ssh.privateKey
        };

        wp.ajax.post( 'update-plugin', data )
            .done( wp.updates.updateSuccess )
            .fail( wp.updates.updateError );
    };

    var JuupdatePlugin = function (plugin, slug) {
        var listplugins = [
            "wp-media-folder",
            "wp-media-folder-addon",
            "wp-file-download",
            "wp-file-download-addon",
            "wp-team-display",
            "wp-latest-post",
            "wp-table-manager",
            "wp-frontpage-news-pro-addon",
            "wp-meta-seo-addon"
        ];

        if($.inArray(slug,listplugins) !== -1){
            if (updaterparams.token && updaterparams.token !== '') {
                $('#' + slug + '-update .update-message').append('<a style="margin-left:10px;color: #a00;" class="ju_check">Checking token...</a>');
                if (slug === 'wp-frontpage-news-pro-addon') {
                    var link = updaterparams.ju_base + 'index.php?option=com_juupdater&task=download.checktoken&extension=wp-latest-posts-addon.zip&token=' + updaterparams.token;
                } else {
                    var link = updaterparams.ju_base + 'index.php?option=com_juupdater&task=download.checktoken&extension=' + slug + '.zip&token=' + updaterparams.token;
                }
                $.ajax({
                    url: link,
                    method: 'GET',
                    dataType: 'json',
                    data: {
                    },
                    success: function (response) {
                        $('#' + slug + '-update .update-message .ju_check').remove();
                        if (response.status === true) {
                            if(updaterparams.version === '4.6.0' || updaterparams.version === '4.8.0'){
                                ju_update_new(plugin, slug);
                            }else{
                                ju_update_old(plugin, slug);
                            }

                            //window.location.assign(response.linkdownload);
                        } else {
                            var r = confirm(response.datas);
                            if (r === true) {
                                window.open(updaterparams.ju_base, "_blank");
                            }
                        }
                    }
                });
            } else {
                $('tr[data-slug="' + slug + '"] .thickbox.ju_update').click();
            }
        } else {
            ju_update(plugin, slug);
        }
    };

    $(document).ready(function () {
        var ju_plugins = ['wp-media-folder', 'wp-file-download', 'wp-team-display', 'wp-latest-post', 'wp-table-manager', 'wp-frontpage-news-pro-addon'];
        $.each(ju_plugins, function (i, slug) {
            if (!updaterparams.token || updaterparams.token === '') {
                $('#' + slug + '-update .update-message a.update-link').addClass('ju-update-link').removeClass('update-link').html('Connect your Joomunited account to update');
            } else {
                $('#' + slug + '-update .update-message a.update-link').addClass('ju-update-link').removeClass('update-link');
            }
            $('#' + slug + '-update td.plugin-update').css({'border-left': '4px solid #d54e21', 'background-color': '#fef7f1'});
        });

        var eventMethod = window.addEventListener ? "addEventListener" : "attachEvent";
        var eventer = window[eventMethod];
        var messageEvent = eventMethod === "attachEvent" ? "onmessage" : "message";

        // Listen to message from child window
        eventer(messageEvent, function (e) {

            var res = e.data;
            if (typeof res !== "undefined" && typeof res.type !== "undefined" && res.type === "joomunited_login") {
                $.ajax({
                    url: ajaxurl,
                    type: 'POST',
                    data: {
                        'action': 'ju_add_token',
                        'token': res.token
                    },
                    success: function () {
                        window.location.assign(document.URL);
                        //                    window.location.assign(res.linkdownload);
                    }
                });
            }
        }, false);

        $('.plugin-update-tr').on('click', '.ju-update-link', function (e) {
            e.preventDefault();
            if (wp.updates.shouldRequestFilesystemCredentials && !wp.updates.ajaxLocked) {
                wp.updates.requestFilesystemCredentials(e);
            }
            var updateRow = $(e.target).parents('.plugin-update-tr');
            // Return the user to the input box of the plugin's table row after closing the modal.
            wp.updates.$elToReturnFocusToFromCredentialsModal = $('#' + updateRow.data('slug')).find('.check-column input');
            JuupdatePlugin(updateRow.data('plugin'), updateRow.data('slug'));
        });

        $(document).on('click', '.ju-btn-disconnect', function () {
            $.ajax({
                url: ajaxurl,
                type: 'POST',
                data: {
                    'action': 'ju_logout'
                },
                success: function () {
                    window.location.assign(document.URL);
                }
            });
        });

    });
}(jQuery));