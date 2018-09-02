=== Media test ===
Tags: media, folder
Requires at least: 3.5.1
Tested up to: 4.9.4
Stable tag: 4.4.3
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

WP media Folder is a WordPress plugin that enhance the WordPress media manager by adding a folder manager inside.

== Description ==

If you were struggling with files and you didn't know how to organize them... 
It's over! With WP Media Folder life is easy, you can manage files, images from the native Wordpress media manager. 
YES, It's true! We did in the media manager of WordPress a file manager where you can drag and drop images and files so easely. 
I can not tell more just watch our demo and please try it to make your own idea.

Stop searching for an image through thousand of media, just navigate like you do on your desktop file manager.

= Changelog = 

= 4.4.3 =
* Fix : Conflict with WP Smush, Enhanced Media Library plugin
* Fix : Create gallery from folder
* Fix : Filters hidden

= 4.4.2 =
* Add : Settings for the new gallery plugin ADDON
* Add : Reload button to handle some page builder content refresh
* Fix : Open context menu when attachment is empty
* Fix : Drag media to folder tree in list view

= 4.4.1 =
* Fix : Media window is not loaded (modal)
* Fix : Align controls in settings, improve layout in settings
* Fix : Change settings text, add help text

= 4.4.0 =
* Add : New admin design, Google Drive grid like
* Add : Implement right click manu and actions for folders and media
* Add : Store Open/Close folder status (folder tree)
* Add : Search bar on top of the folder tree to filter folder only
* Add : Custom ordering inplementation for folders and media (global, not by user)
* Add : Fallback to legacy design accessible though a new setting
* Add : Move folders from folder tree
* Add : Rename folders from folder tree (double click)

= 4.3.6 =
* Fix : rename file when upload
* Fix : import & sync
* Fix : move file

= 4.3.5 =
* Fix : Replace file failed in some case
* Fix : Conflict with Envira Gallery, Easing Slider plugins
* Fix : Sort image with auto update gallery

= 4.3.4 =
* Fix : Auto update image to gallery
* Fix : Import folders including special characters in name
* Fix : Duplicate media, replace, media folder selection button with next and prev media items

= 4.3.3 =
* Fix : Apply status filter
* Fix : Conflict with post filters

= 4.3.2 =
* Fix : Move multiple files in list view
* Fix : Media Folder in modal view not loaded properly
* Fix : Right to left style

= 4.3.1 =
* Fix : Import and Synchronization feature folder selection
* Fix : Translation tool (JU Translation)
* Fix : Conflict with WP Smush, ImageRecycle, Master Slider plugin
* Fix : JS Error on folder tree resizing

= 4.3.0 =
* Add : Full code rewriting to enhance plugin performance
* Add : Implement progressive loading in every folder from post edition media lightbox
* Add : New resizable folder tree
* Add : Rewrite media filtering system based on a dropdown lists

= 4.2.8 =
* Fix : Create folder by user/role

= 4.2.7 =
* Fix : Prohibit direct script loading
* Fix : Remove some unused code
* Fix : Change filename for some class file

= 4.2.6 =
* Fix : WPMF gallery conflict with DIVI builder gallery
* Fix : Encoding issue when embeded a pdf

= 4.2.5 =
* Fix : Overflow width in the plugin settings
* Fix : Update dimensions when replacing an image

= 4.2.4 =
* Fix : Escaping of already secured datas
* Fix : Update compatibility with old addon versions

= 4.2.3 =
* Add : Microsoft OneDrive settings and comaptibility (addon)
* Fix : Conflict with Enhanced Media Library plugin
* Fix : File replacement .svg and .html formats
* Fix : Upload a remote video

= 4.2.2 =
* Fix : XSS issue when hover image
* Fix : Add video in lightbox
* Fix : Check user permissions for AJAX requests

= 4.2.1 =
* Fix : Update the updater for WordPress 4.8

= 4.2.0 =
* Add : Possibility to add a remote Youtube video among other media
* Add : Apply image watermark to the library and on media upload
* Add : Option to remove additional characters in the rename feature
* Add : User media access restriction activated: Select a media root folder

= 4.1.4 =
* Fix : Upload slow down when WPML plugin is active

= 4.1.3 =
* Fix : Media automatic rename
* Fix : Conflict with Enhanced Media Library plugin
* Fix : Query attachment
* Fix : Import from server folders

= 4.1.2 =
* Fix : Display all files in the root folder in list view
* Fix : Error of synchronization
* Fix : Missing translation strings
* Fix : Wrong path when site install is in a subdirectory

= 4.1.1 =
* Fix : Media not shown when not affected to a folder
* Fix : PHP warning in grid view

= 4.1.0 =
* Add : 2 ways synchronization: From server to Media Folder and From Media Folder to server
* Add : Apply multiple folders per media
* Add : Batch apply multiple folders per media
* Add : Avanced rename on upload: remove/add special characters, control capitalization

= 4.0.2 =
* Fix : Use default en_US language
* Fix : Allow saving an empty translation override file

= 4.0.1 =
* Fix : Folder tree style fix whith long titles
* Fix : Hover effect with very small image height or long titles

= 4.0.0 =
* Add : New material design interface
* Add : Notification system on media actions: upload, remove, rename, move, replace, apply filter
* Add : Undo last action from notification when: delete folder, edit folder, move folder, move file, filter
* Add : Load medium image size on mouse hover as an option
* Add : New media replace tool with intant refresh and thumbnail generation
* Add : Extensible folder tree using CSS
* Add : All settings now use material design

= 3.8.7 =
* Fix : SVG format error on regenerate thumbnail
* Fix : Style right to left
* Fix : FTP import & sync not work when rename the wp-content folder

= 3.8.6 =
* Fix : Media rename don't apply
* Fix : Replace PNG file keep transparency background
* Fix : Auto insert image in folder feature

= 3.8.5 =
* Fix : SQL get count post
* Fix : Undefined function get_userdata error

= 3.8.4 =
* Fix : Import meta size and file type on large image
* Fix : Some folder not displayed in tree folder
* Fix : Duplicate folder when double click on the folder tree

= 3.8.3 =
* Fix : Compatibility with WordPress theme customizer
* Fix : Open PDF file new window

= 3.8.2 =
* Fix : User permissions not correctly checked

= 3.8.1 =
* Add : Add cloud configuration documentation link in settings
* Fix : JoomUnited updater compatible with new WordPress 4.6 shiny updates

= 3.8.0 =
* Add : Embed pdf from media library option
* Add : Add settings to connect Google Drive and Dropbox (for the addon)
* Fix : Speed optimization
* Fix : Duplicate replace button when saving parameters

= 3.7.0 =
* Add : ImageRecycle Image compression integration in parameters
* Add : Lightbox on single image, as an option
* Add : Display the direct number files in a folder
* Fix : Conflict with CFS image plugin (Custom Field Suite)

= 3.6.0 =
* Add : Option to disable by default the JS called on frontend (for frontend page builders)
* Add : Whole code optimization regarding plugin performance
* Add : SQL query optimization regarding plugin performance
* Add : Sanitize all elements prints on frontend (XSS)
* Add : Update folder and tree design
* Fix : Drag and drop when edit multiple selection
* Fix : Button insert link not working on some specific configuration
* Fix : Masonry gallery display on small screen size
* Fix : Duplicate folder when using an access restriction by user role

= 3.5.6 =
* Fix : Conflict with Autoptimize plugin
* Fix : Display folder tree in custom media frame
* Fix : Load script in page table on multiple site

= 3.5.5 =
* Fix : Clean CSS & JS on frontend
* Fix : CSS layout when filter and ordering feature is disabled
* Fix : Display folder per user
* Fix : Server folder import with uppercase file extension

= 3.5.4 =
* Add : Setting Animation for slider gallery
* Fix : Compatiblility with Cornerstone plugin
* Fix : Install / blank white screen

= 3.5.3 =
* Add : Load jQuery on frontend to be conpatible with public side edition plugins
* Add : Compatiblity with WP Sweep plugin
* Add : Make WPMF work with all plugins that use Media Library in front-end

= 3.5.2 =
* Add : Make media folder work with svg images
* Fix : Display limitation of post and folder by user role
* Fix : Remove filter wp_generate_attachment_metadata when regenerate thumbnail

= 3.5.1 =
* Fix : fix FTP Import doesn't show directories

= 3.5.0 =
* Add : Media access: limit access by user role (a folder per user role)
* Add : Possibility to duplicate a media
* Add : Possibility drag'n drop a media in the current folder from desktop
* Add : Possibility to replace all file types, not just images (zip, pdf...)
* Add : Compatibility/work with with ACF
* Add : Compatibility/work with Beaver builder
* Add : Compatibility/work with Site Origine page builder
* Add : Compatibility/work with Themify builder
* Add : Compatibility/work with Live composer page builder
* Fix : fix sync media and import ftp with file name has special characters
* Fix : compatibility with Beaver Builder , Live composer page builder ...
* Fix : replace other file than image

= 3.4.2 =
* Fix : Fix image conflict style with YoImages plugin
* Fix : unbind click when drag folder
* Fix : update langguages

= 3.4.1 =
* Fix : Fix image replacer

= 3.4.0 =
* Add : Regenerate thumbnails tool in parameters
* Add : Add process bar when use FTP import, allow massive import
* Add : Sync external media
* Add : Sort images by title and date in gallery
* Add : DIVI builder compatibility
* Fix : Remove css background for image replacement

= 3.3.6 =
* Fix : FTP import
* Fix : Folder stay opened when called from multiple media views

= 3.3.5 =
* Fix : Update file size when file replace is complete
* Fix : Portfolio theme JS wrong calculation when resizing the screen

= 3.3.4 =
* Fix : conflict with RokSprocket plugin
* Fix : conflict with WP Table Manager plugin

= 3.3.3 =
* Fix : fix error when active plugin on multisite
* Fix : fix conflict with Gleam theme
* Fix : fix conflict with retina 2x plugin

= 3.3.1 =
* Fix : Update filter layout to fit new WP 4.4 admin CSS
* Fix : Portfolio gallery style is not loading proper thumbnail size
* Fix : Clean CSS & JS from portfolio gallery theme
* Fix : Update Material-Design-Iconic-Font
* Fix : Use current_user_can to check user rights for importer from FTP

= 3.3.0 =
* Add : Rename file on upload with a pattern
* Add : Remove a folder with all it's media inside (as an option)
* Fix : File insertion, remove file on clicking on the cross
* Fix : Gallery lightbox going to top of the screen

= 3.2.0 =
* Add : Search option to search in current folder or in the whole media library
* Add : Possibility to setup an image as folder cover
* Fix : .pot laguage file for translators

= 3.1.0 =
* Fix : Single file insertion design

= 3.0.7 =
* Fix : AJAX automatic reload
* Fix : Get url lightbox not work
* Fix : Register taxonomy in back-end and front-end

= 3.0.6 =
* Add : Include the automatic updater

= 3.0.5 =
* Add : New file type in import tool
* Add : Defined( 'ABSPATH' ) || die( 'No direct script access allowed!' );
* Fix : Remove file github=checked.php
* Fix : Warning and get role
* Fix : Change general settings title

= 3.0.4 =
* Add : New file type in import tool
* Add : Search attachment on all folders function
* Fix : Same variable name
* Fix : Optimize code when active plugin

= 3.0.3 =
* Fix : duplicate #jao
* Fix : js conflict with wp-table-manager plugin

= 3.0.2 =
* Fix : .js error when adding media into post

= 3.0.1 =
* Add : WordPress 4.3 compatibility
* Add : Compatibility with plugin with WPML plugin
* Fix : Slider @ single column don't load the good image size
* Fix : Image disappear when using the bulk select
* Fix : Upload file to folder in list view
* Fix : Check page when using move_file

= 3.0.0 =
* Add : Import media and folder structure from folder/sub-folder from your server
* Add : Style settings in 4 tabs
* Fix : Image size not selected properly in masonry theme
* Fix : Single Jquery load
* Fix : Style gallery conflict with WPML plugin
* Fix : Set 'wpmf-category' is default

= 2.4.1 =
* Fix : Error script and performance
* Fix : Auto insert gallery from folder
* Fix : Update title when replace image
* Fix : Auto insert gallery from folder
* Fix : Style in screen ipad
* Fix : Import nextgen gallery

= 2.4.0 =
* Add : Possibility to override a media with another one (replace media)
* Fix : Move a parent folder into one of its subfolders
* Fix : Change name $_SESSION['child'] to $_SESSION['wpmf_child']
* Fix : Conflict style with Advanced Custom Fields plugin

= 2.3.0 =
* Add : Possibility to drag'n drop media in left column folder tree
* Fix : Style broken in right to left language
* Fix : Enqueue style gallery when the gallery is not empty
* Fix : Change image on hover
* Fix : Error in the french file

= 2.2.0 =
* Add : Media filtering by image dimension
* Add : Filtering by media type (zip, image, pdf,...)
* Add : Media filtering by media weight
* Add : Define custom weight and dimension to be applied in media filtering
* Add : Small and large view of media
* Add : Sorting folders by name and ID
* Add : Sorting media by date
* Add : Sorting media by title
* Add : Sorting media by size
* Add : Sorting media by file type
* Add : Save user sorting and ordering using cookies
* Add : Possibility to disable the feature
* Add : Spanish and German languages

= 2.1.0 =
* Add : Localization standard files (English and french included)

= 2.0.0 =
* Add : Own media display restriction
* Add : Admin option to filter own media with session
* Fix : Firefox display
* Fix : Default gallery theme broken in some themes
* Fix : Alert display when create same folder with same name

= 1.3.1 =
* Add : Use backbone js to create progress bar when upload attachment
* Fix : Style conflict with enhanced media library pro
* Fix : Error : images after upload vanished
* Fix : JS conflict MailPoet Plugin
* Fix : Reset query when delete folder
* Fix : Support right to left language
* Fix : Use $wpdb->prefix.'table_name' instead use wp_ prefix
* Fix : Sanitize sql function
* Fix : Slider theme disappear when select size = 'large' or 'fullsize'

= 1.3.0 =
* Add : NextGEN gallery importer
* Add : Change config text and add NextGEN sync button

= 1.2.1 =
* Add : Possibility to disable gallery feature
* Add : Use svg icon for button next and prev
* Fix : Theme conflict WP Latest Posts plugin
* Fix : Random order selected by default
* Fix : Custom link in gallery broken
* Fix : Custom _blank link in portfolio gallery
* Fix : When lightbox open , double click to load next/previous image in portfolio theme
* Fix : Random order is broken when active Advanced Custom Fields plugin
* Fix : Auto insert image from folder in Page

= 1.2.0 =
* Add : Gallery function: masonry
* Add : Gallery function: portfolio
* Add : Gallery function: slider
* Add : Override default WordPress gallery function with new parameters and lightbox
* Add : Parameter view for custom image size choice
* Add : Parameter for gallery display

= 1.1.3 =
* Fix : WordPress 4.2 compatibility, in some case only folders are loaded, not images

= 1.1.2 =
* Fix : Progress bar disappear on image upload
* Fix : Date filter disappear in the media popup from an article

= 1.1.1 =
* Add : JS and CSS compatibility with theme builder

= 1.1.0 =
* Add : Folder tree on left part

= 1.0.3 to 1.0.4 =
* Fix : JS error and style

= 1.0.2 =
* Add : Custom taxonomy for folder
* Add : Import post into new categories
* Fix : JS error on post page which are not articles or posts or pages

= 1.0.1 =
* Fix : Fix backend display, the folder are going over media parameters

= 1.0.0 =
* Add : Initial release version 

