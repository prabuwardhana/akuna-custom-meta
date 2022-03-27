<?php

/**
 * Akuna Product Tag Meta Class
 *
 * @package  Akuna Product Tag Meta
 * @since    1.0.0
 */

if (!defined('ABSPATH')) {
    exit;
}

if (!class_exists('Akuna_Product_Tag_Meta')) :
    class Akuna_Product_Tag_Meta
    {
        public function __construct()
        {
            // Add form.
            add_action('product_tag_add_form_fields', array($this, 'add_product_tag_image'), 10, 2);
            add_action('product_tag_edit_form_fields', array($this, 'edit_product_tag_image'), 10, 2);
            add_action('created_term', array($this, 'save_product_tag_image'), 20, 3);
            add_action('edit_term', array($this, 'save_product_tag_image'), 20, 3);

            // Enqueue media.
            add_action('admin_enqueue_scripts', array($this, 'load_media'));

            // Add columns.
            add_filter('manage_edit-product_tag_columns', array($this, 'product_tag_columns'));
            add_filter('manage_product_tag_custom_column', array($this, 'product_tag_column'), 10, 3);
        }

        public function add_product_tag_image($taxonomy)
        { ?>
            <div class="form-field term-thumbnail-wrap">
                <label><?php esc_html_e('Thumbnail', 'akuna-custom-metaboxes'); ?></label>
                <div id="product_tag_thumbnail" style="float: left; margin-right: 10px;"><img src="<?php echo esc_url(wc_placeholder_img_src()); ?>" width="60px" height="60px" /></div>
                <div style="line-height: 60px;">
                    <input type="hidden" id="product_tag_thumbnail_id" name="product_tag_thumbnail_id" />
                    <button type="button" class="upload_image_button button"><?php esc_html_e('Upload/Add image', 'akuna-custom-metaboxes'); ?></button>
                    <button type="button" class="remove_image_button button"><?php esc_html_e('Remove image', 'akuna-custom-metaboxes'); ?></button>
                </div>
                <script type="text/javascript">
                    // Only show the "remove image" button when needed
                    if (!jQuery('#product_tag_thumbnail_id').val()) {
                        jQuery('.remove_image_button').hide();
                    }

                    // Uploading files
                    var file_frame;

                    jQuery(document).on('click', '.upload_image_button', function(event) {

                        event.preventDefault();

                        // If the media frame already exists, reopen it.
                        if (file_frame) {
                            file_frame.open();
                            return;
                        }

                        // Create the media frame.
                        file_frame = wp.media.frames.downloadable_file = wp.media({
                            title: '<?php esc_html_e('Choose an image', 'akuna-custom-metaboxes'); ?>',
                            button: {
                                text: '<?php esc_html_e('Use image', 'akuna-custom-metaboxes'); ?>'
                            },
                            multiple: false
                        });

                        // When an image is selected, run a callback.
                        file_frame.on('select', function() {
                            var attachment = file_frame.state().get('selection').first().toJSON();
                            var attachment_thumbnail = attachment.sizes.thumbnail || attachment.sizes.full;

                            jQuery('#product_tag_thumbnail_id').val(attachment.id);
                            jQuery('#product_tag_thumbnail').find('img').attr('src', attachment_thumbnail.url);
                            jQuery('.remove_image_button').show();
                        });

                        // Finally, open the modal.
                        file_frame.open();
                    });

                    jQuery(document).on('click', '.remove_image_button', function() {
                        jQuery('#product_tag_thumbnail').find('img').attr('src', '<?php echo esc_js(wc_placeholder_img_src()); ?>');
                        jQuery('#product_tag_thumbnail_id').val('');
                        jQuery('.remove_image_button').hide();
                        return false;
                    });

                    jQuery(document).ajaxComplete(function(event, request, options) {
                        if (request && 4 === request.readyState && 200 === request.status &&
                            options.data && 0 <= options.data.indexOf('action=add-tag')) {

                            var res = wpAjax.parseAjaxResponse(request.responseXML, 'ajax-response');
                            if (!res || res.errors) {
                                return;
                            }
                            // Clear Thumbnail fields on submit
                            jQuery('#product_tag_thumbnail').find('img').attr('src', '<?php echo esc_js(wc_placeholder_img_src()); ?>');
                            jQuery('#product_tag_thumbnail_id').val('');
                            jQuery('.remove_image_button').hide();
                            // Clear Display type field on submit
                            jQuery('#display_type').val('');
                            return;
                        }
                    });
                </script>
                <div class="clear"></div>
            </div>
        <?php
        }

        public function edit_product_tag_image($term, $taxonomy)
        {
            $thumbnail_id = absint(get_term_meta($term->term_id, 'thumbnail_id', true));

            if ($thumbnail_id) {
                $image = wp_get_attachment_thumb_url($thumbnail_id);
            } else {
                $image = wc_placeholder_img_src();
            } ?>
            <tr class="form-field term-thumbnail-wrap">
                <th scope="row" valign="top"><label><?php esc_html_e('Thumbnail', 'akuna-custom-metaboxes'); ?></label></th>
                <td>
                    <div id="product_tag_thumbnail" style="float: left; margin-right: 10px;"><img src="<?php echo esc_url($image); ?>" width="60px" height="60px" /></div>
                    <div style="line-height: 60px;">
                        <input type="hidden" id="product_tag_thumbnail_id" name="product_tag_thumbnail_id" value="<?php echo esc_attr($thumbnail_id); ?>" />
                        <button type="button" class="upload_image_button button"><?php esc_html_e('Upload/Add image', 'akuna-custom-metaboxes'); ?></button>
                        <button type="button" class="remove_image_button button"><?php esc_html_e('Remove image', 'akuna-custom-metaboxes'); ?></button>
                    </div>
                    <script type="text/javascript">
                        // Only show the "remove image" button when needed
                        if ('0' === jQuery('#product_tag_thumbnail_id').val()) {
                            jQuery('.remove_image_button').hide();
                        }

                        // Uploading files
                        var file_frame;

                        jQuery(document).on('click', '.upload_image_button', function(event) {

                            event.preventDefault();

                            // If the media frame already exists, reopen it.
                            if (file_frame) {
                                file_frame.open();
                                return;
                            }

                            // Create the media frame.
                            file_frame = wp.media.frames.downloadable_file = wp.media({
                                title: '<?php esc_html_e('Choose an image', 'akuna-custom-metaboxes'); ?>',
                                button: {
                                    text: '<?php esc_html_e('Use image', 'akuna-custom-metaboxes'); ?>'
                                },
                                multiple: false
                            });

                            // When an image is selected, run a callback.
                            file_frame.on('select', function() {
                                var attachment = file_frame.state().get('selection').first().toJSON();
                                var attachment_thumbnail = attachment.sizes.thumbnail || attachment.sizes.full;

                                jQuery('#product_tag_thumbnail_id').val(attachment.id);
                                jQuery('#product_tag_thumbnail').find('img').attr('src', attachment_thumbnail.url);
                                jQuery('.remove_image_button').show();
                            });

                            // Finally, open the modal.
                            file_frame.open();
                        });

                        jQuery(document).on('click', '.remove_image_button', function() {
                            jQuery('#product_tag_thumbnail').find('img').attr('src', '<?php echo esc_js(wc_placeholder_img_src()); ?>');
                            jQuery('#product_tag_thumbnail_id').val('');
                            jQuery('.remove_image_button').hide();
                            return false;
                        });
                    </script>
                    <div class="clear"></div>
                </td>
            </tr>
<?php
        }

        public function save_product_tag_image($term_id, $tt_id, $taxonomy)
        {
            echo '<pre>', var_dump($taxonomy), '</pre>';
            if (isset($_POST['product_tag_thumbnail_id']) && 'product_tag' === $taxonomy) { // WPCS: CSRF ok, input var ok.
                update_term_meta($term_id, 'thumbnail_id', absint($_POST['product_tag_thumbnail_id'])); // WPCS: CSRF ok, input var ok.
            }
        }

        public function load_media()
        {
            wp_enqueue_media();
        }

        public function product_tag_columns($columns)
        {
            $new_columns = array();

            if (isset($columns['cb'])) {
                $new_columns['cb'] = $columns['cb'];
                unset($columns['cb']);
            }

            $new_columns['thumb'] = __('Image', 'akuna-custom-metaboxes');

            $columns           = array_merge($new_columns, $columns);
            $columns['handle'] = '';

            return $columns;
        }

        public function product_tag_column($columns, $column, $id)
        {
            if ('thumb' === $column) {
                // Prepend tooltip for default category.
                $default_category_id = absint(get_option('default_product_cat', 0));

                if ($default_category_id === $id) {
                    $columns .= wc_help_tip(__('This is the default category and it cannot be deleted. It will be automatically assigned to products with no category.', 'woocommerce'));
                }

                $thumbnail_id = get_term_meta($id, 'thumbnail_id', true);

                if ($thumbnail_id) {
                    $image = wp_get_attachment_thumb_url($thumbnail_id);
                } else {
                    $image = wc_placeholder_img_src();
                }

                // Prevent esc_url from breaking spaces in urls for image embeds. Ref: https://core.trac.wordpress.org/ticket/23605 .
                $image    = str_replace(' ', '%20', $image);
                $columns .= '<img src="' . esc_url($image) . '" alt="' . esc_attr__('Thumbnail', 'akuna-custom-metaboxes') . '" class="wp-post-image" height="48" width="48" />';
            }
            if ('handle' === $column) {
                $columns .= '<input type="hidden" name="term_id" value="' . esc_attr($id) . '" />';
            }
            return $columns;
        }
    }
endif;

return new Akuna_Product_Tag_Meta();
