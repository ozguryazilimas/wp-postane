(function ($) {
    var l10n = wp.media.view.l10n = typeof _wpMediaViewsL10n === 'undefined' ? {} : _wpMediaViewsL10n;

    wp.media.view.MediaFrame.Select = wp.media.view.MediaFrame.Select.extend({

        bindHandlers: function () {
            this.on('router:create:browse', this.createRouter, this);
            this.on('router:render:browse', this.browseRouter, this);
            this.on('content:create:browse', this.browseContent, this);
            this.on('content:render:upload', this.uploadContent, this);
            this.on('toolbar:create:select', this.createSelectToolbar, this);

            this.on('content:create:dreamstime', this.dreamstimeContent, this);
            this.on('content:render:dreamstime', this.dreamstimeContent, this);

            this.on('content:create:apt', this.aptContent, this);
            this.on('content:render:apt', this.aptContent, this);
        },

        browseRouter: function (view) {
            view.set({
                upload: {
                    text: l10n.uploadFilesTitle,
                    priority: 20
                },
                browse: {
                    text: l10n.mediaLibraryTitle,
                    priority: 40
                },
                dreamstime: {
                    text: 'Dreamstime',
                    priority: 60
                },
                apt: {
                    text: 'Auto Featured Images',
                    priority: 70
                }
            });
        },

        dreamstimeContent: function (content) {
            this.$el.removeClass('hide-toolbar');
            this.state().set('src', dreamstimeIframeSrc); //set in Dreamstime::loadCssJs with wp_localize_script()
            content.view = new wp.media.view.Iframe({
                controller: this
            });
        },
        aptContent: function (content) {
            this.$el.removeClass('hide-toolbar');
            this.state().set('src', apt_media_iframe.src); //set in Dreamstime::loadCssJs with wp_localize_script()
            content.view = new wp.media.view.Iframe({
                controller: this
            });
        }

    });

    var mediaFrameSelect = wp.media.view.MediaFrame.Select;
    wp.media.view.MediaFrame.Post = wp.media.view.MediaFrame.Post.extend({
        bindHandlers: function () {
            var handlers, checkCounts;

            mediaFrameSelect.prototype.bindHandlers.apply(this, arguments);

            this.on('activate', this.activate, this);

            // Only bother checking media type counts if one of the counts is zero
            checkCounts = _.find(this.counts, function (type) {
                return type.count === 0;
            });

            if (typeof checkCounts !== 'undefined') {
                this.listenTo(wp.media.model.Attachments.all, 'change:type', this.mediaTypeCounts);
            }

            this.on('menu:create:gallery', this.createMenu, this);
            this.on('menu:create:playlist', this.createMenu, this);
            this.on('menu:create:video-playlist', this.createMenu, this);
            this.on('toolbar:create:main-insert', this.createToolbar, this);
            this.on('toolbar:create:main-gallery', this.createToolbar, this);
            this.on('toolbar:create:main-playlist', this.createToolbar, this);
            this.on('toolbar:create:main-video-playlist', this.createToolbar, this);
            this.on('toolbar:create:featured-image', this.featuredImageToolbar, this);
            this.on('toolbar:create:main-embed', this.mainEmbedToolbar, this);

            handlers = {
                menu: {
                    'default': 'mainMenu',
                    'gallery': 'galleryMenu',
                    'playlist': 'playlistMenu',
                    'video-playlist': 'videoPlaylistMenu'
                },

                content: {
                    'embed': 'embedContent',
                    'edit-image': 'editImageContent',
                    'edit-selection': 'editSelectionContent'
                },

                toolbar: {
                    'main-insert': 'mainInsertToolbar',
                    'main-gallery': 'mainGalleryToolbar',
                    'gallery-edit': 'galleryEditToolbar',
                    'gallery-add': 'galleryAddToolbar',
                    'main-playlist': 'mainPlaylistToolbar',
                    'playlist-edit': 'playlistEditToolbar',
                    'playlist-add': 'playlistAddToolbar',
                    'main-video-playlist': 'mainVideoPlaylistToolbar',
                    'video-playlist-edit': 'videoPlaylistEditToolbar',
                    'video-playlist-add': 'videoPlaylistAddToolbar'
                }
            };

            _.each(handlers, function (regionHandlers, region) {
                _.each(regionHandlers, function (callback, handler) {
                    this.on(region + ':render:' + handler, this[callback], this);
                }, this);
            }, this);
        },

        browseRouter: function (view) {
            view.set({
                upload: {
                    text: l10n.uploadFilesTitle,
                    priority: 20
                },
                browse: {
                    text: l10n.mediaLibraryTitle,
                    priority: 40
                },
                dreamstime: {
                    text: 'Dreamstime',
                    priority: 60
                },
                apt: {
                    text: 'Auto Featured Images',
                    priority: 70
                }
            });
        },

        dreamstimeContent: function (content) {
            this.$el.removeClass('hide-toolbar');
            this.state().set('src', dreamstimeIframeSrc); //set in Dreamstime::loadCssJs with wp_localize_script()
            content.view = new wp.media.view.Iframe({
                controller: this
            });
        },
        aptContent: function (content) {
            this.$el.removeClass('hide-toolbar');
            this.state().set('src', apt_media_iframe.src); //set in Dreamstime::loadCssJs with wp_localize_script()
            content.view = new wp.media.view.Iframe({
                controller: this
            });
        }

    });

}(jQuery));

window.cvapt_media_refresh = window.cvapt_media_refresh || function () {
    wp.media.frame.content.mode('browse');
    wp.media.frame.content.get().collection.props.set({ignore: (+new Date())});
}

