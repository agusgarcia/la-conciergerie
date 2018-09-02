<?php
/* Prohibit direct script loading */
defined('ABSPATH') || die('No direct script access allowed!');

/**
 * Class WpmfPdfEmbed
 * This class that holds most of the PDF embed functionality for Media Folder.
 */
class WpmfPdfEmbed
{

    /**
     * Wpmf_Pdf_Embed constructor.
     */
    public function __construct()
    {
        add_action('wp_enqueue_media', array($this, 'loadScript'));
        add_filter('media_send_to_editor', array($this, 'addImageFiles'), 10, 3);
        add_action('wp_enqueue_scripts', array($this, 'loadStyleScript'));
        add_filter("attachment_fields_to_edit", array($this, "attachmentFieldsToEdit"), 10, 2);
        add_filter("attachment_fields_to_save", array($this, "attachmentFieldsToSave"), 10, 2);
    }

    /**
     * Load styles and scripts
     */
    public function loadStyleScript()
    {
        global $post;
        if (!empty($post)) {
            if (strpos($post->post_content, 'wpmf-pdfemb-viewer') !== false
                && strpos($post->post_content, 'data-wpmf_pdf_embed="embed"') !== false
            ) {
                wp_enqueue_script(
                    'wpmf_embed_pdf_js',
                    plugins_url('assets/js/pdf-embed/all-pdfemb-basic.min.js', dirname(__FILE__)),
                    array('jquery')
                );
                wp_localize_script(
                    'wpmf_embed_pdf_js',
                    'wpmf_pdfemb_trans',
                    $this->getTranslation()
                );
                wp_enqueue_script(
                    'wpmf_compat_js',
                    plugins_url('assets/js/pdf-embed/compatibility.js', dirname(__FILE__)),
                    array('jquery')
                );
                wp_enqueue_script(
                    'wpmf_pdf_js',
                    plugins_url('assets/js/pdf-embed/pdf.js', dirname(__FILE__)),
                    array('wpmf_compat_js')
                );
                wp_enqueue_style(
                    'pdfemb_embed_pdf_css',
                    plugins_url('assets/css/pdfemb-embed-pdf.css', dirname(__FILE__))
                );
            }
        }
    }

    /**
     * Localize a script.
     * Works only if the script has already been added.
     * @return array
     */
    public function getTranslation()
    {
        $array = array(
            'worker_src' => plugins_url('assets/js/pdf-embed/pdf.worker.min.js', dirname(__FILE__)),
            'cmap_url' => plugins_url('assets/js/pdf-embed/cmaps/', dirname(__FILE__)),
            'objectL10n' =>
                array(
                    'loading' => __('Loading...', 'wpmf'),
                    'page' => __('Page', 'wpmf'),
                    'zoom' => __('Zoom', 'wpmf'),
                    'prev' => __('Previous page', 'wpmf'),
                    'next' => __('Next page', 'wpmf'),
                    'zoomin' => __('Zoom In', 'wpmf'),
                    'zoomout' => __('Zoom Out', 'wpmf'),
                    'secure' => __('Secure', 'wpmf'),
                    'download' => __('Download PDF', 'wpmf'),
                    'fullscreen' => __('Full Screen', 'wpmf'),
                    'domainerror' => __('Error: URL to the PDF file must be on exactly
                 the same domain as the current web page.', 'wpmf'),
                    'clickhereinfo' => __('Click here for more info', 'wpmf'),
                    'widthheightinvalid' => __('PDF page width or height are invalid', 'wpmf'),
                    'viewinfullscreen' => __('View in Full Screen', 'wpmf'),
                    'poweredby' => 1));
        return $array;
    }

    /**
     * Add pdf embed html to editor
     * @param string $html HTML markup for a media item sent to the editor.
     * @param int $id The first key from the $_POST['send'] data.
     * @param array $attachment Array of attachment metadata.
     * @return string $html
     */
    public function addImageFiles($html, $id, $attachment)
    {
        $post = get_post($id);
        $mimetype = explode("/", $post->post_mime_type);
        $pdf_embed = get_post_meta($id, 'wpmf_pdf_embed', true);
        $target = get_post_meta($id, '_gallery_link_target', true);
        if ($mimetype[1] == 'pdf') {
            if (isset($pdf_embed) && $pdf_embed == 'embed') {
                $doc = new DOMDocument();
                libxml_use_internal_errors(true);
                @$doc->loadHTML(mb_convert_encoding($html, 'HTML-ENTITIES', 'UTF-8'));
                $tags = $doc->getElementsByTagName('a');
                if ($tags->length > 0) {
                    if (!empty($tags)) {
                        $class = $tags->item(0)->getAttribute('class');
                        if (!empty($class)) {
                            $newclass = $class . ' wpmf-pdfemb-viewer';
                        } else {
                            $newclass = 'wpmf-pdfemb-viewer';
                        }
                        $tags->item(0)->setAttribute('data-wpmf_pdf_embed', $pdf_embed);
                        $tags->item(0)->setAttribute('target', $target);
                        $tags->item(0)->setAttribute('class', $newclass);
                        $html = $doc->saveHTML();
                    }
                }
            } else {
                $singlefile = get_option('wpmf_option_singlefile');
                if (isset($singlefile) && $singlefile == 1) {
                    $meta = get_post_meta($id, '_wp_attached_file');
                    $upload_dir = wp_upload_dir();
                    $url_attachment = $upload_dir['basedir'] . '/' . $meta[0];
                    if (file_exists($url_attachment)) {
                        $size = filesize($url_attachment);
                        if ($size < 1024 * 1024) {
                            $size = round($size / 1024, 1) . ' kB';
                        } elseif ($size > 1024 * 1024) {
                            $size = round($size / (1024 * 1024), 1) . ' MB';
                        }
                    } else {
                        $size = 0;
                    }

                    $type = wp_check_filetype($post->guid);
                    $ext = $type['ext'];
                    $html = '<span class="wpmf_mce-wrap" data-file="' . $id . '" style="overflow: hidden;">';
                    $html .= '<a class="wpmf-defile wpmf_mce-single-child"
                     href="' . $post->guid . '" data-id="' . $id . '" target="' . $target . '">';
                    $html .= '<span class="wpmf_mce-single-child" style="font-weight: bold;">';
                    $html .= $post->post_title;
                    $html .= '</span><br>';
                    $html .= '<span class="wpmf_mce-single-child" style="font-weight: normal;font-size: 0.8em;">';
                    $html .= '<b class="wpmf_mce-single-child">Size : </b>' . $size;
                    $html .= '<b class="wpmf_mce-single-child"> Format : </b>' . strtoupper($ext) . '</span>';
                    $html .= '</a>';
                    $html .= '</span>';
                } else {
                    $html = preg_replace('/(<a\b[^><]*)>/i', '$1 target="'.$target.'">', $html);
                }
            }
        }
        return $html;
    }

    /**
     * add footer
     */
    public function adminFooterPdfEmbed()
    {
        ?>
        <script type="text/javascript">
            jQuery(function () {
                if (wp && wp.media && wp.media.events) {
                    wp.media.events.on('editor:image-edit', function (data) {
                        data.metadata.wpmf_pdf_embed = data.editor.dom.getAttrib(data.image, 'data-wpmf_pdf_embed');
                    });
                }
            });
        </script>
        <?php
    }

    /**
     * add footer script
     */
    public function loadScript()
    {
        add_action('admin_footer', array($this, 'adminFooterPdfEmbed'), 11);
        add_action('wp_footer', array($this, 'adminFooterPdfEmbed'), 11);
    }

    /**
     * Create enable PDF embed field
     * Based on /wp-admin/includes/media.php
     * @param array $form_fields An array of attachment form fields.
     * @param WP_Post $post The WP_Post attachment object.
     * @return mixed
     */
    public function attachmentFieldsToEdit($form_fields, $post)
    {
        $infosfile = wp_check_filetype($post->guid);
        if (!empty($infosfile['ext']) && $infosfile['ext'] == 'pdf') {
            $value = get_post_meta($post->ID, 'wpmf_pdf_embed', true);
            if (empty($value)) {
                $value = 'large';
            }
            $embed = array(
                'link' => __('Off', 'wpmf'),
                'embed' => __('On', 'wpmf'),
            );
            $option = '';
            foreach ($embed as $k => $v) {
                if ($value == $k) {
                    $option .= '<option selected value="' . $k . '">' . $v . '</option>';
                } else {
                    $option .= '<option value="' . $k . '">' . $v . '</option>';
                }
            }
            $form_fields['wpmf_pdf_embed'] = array(
                'label' => __('PDF Embed', 'wpmf'),
                'input' => 'html',
                'html' => '
                            <select name="attachments[' . $post->ID . '][wpmf_pdf_embed]"
                             id="attachments[' . $post->ID . '][wpmf_pdf_embed]">
                                    ' . $option . '
                            </select>'
            );
        }

        return $form_fields;
    }

    /**
     * Save enable PDF embed option
     * Based on /wp-admin/includes/media.php
     * @param array $post An array of post data.
     * @param array $attachment An array of attachment metadata.
     * @return mixed $post
     */
    public function attachmentFieldsToSave($post, $attachment)
    {
        if (isset($attachment['wpmf_pdf_embed'])) {
            update_post_meta($post['ID'], 'wpmf_pdf_embed', $attachment['wpmf_pdf_embed']);
        }
        return $post;
    }
}
