<?php

/**
 * Akuna Page Meta Class
 *
 * @package  Akuna Custom Meta
 * @since    1.0.0
 */

if (!defined('ABSPATH')) {
    exit;
}

if (!class_exists('Akuna_Page_Meta')) :
    class Akuna_Page_Meta
    {
        public function __construct()
        {
            add_action('add_meta_boxes', array($this, 'akuna_add_page_meta_box'));
            add_action('save_post', array($this, 'akuna_save_page_meta'), 10, 1);
        }

        public function akuna_add_page_meta_box()
        {
            add_meta_box(
                'custom_page_meta_box',
                __('Page Subtitle', 'akuna-custom-metaboxes'),
                array($this, 'akuna_page_meta_box_html'),
                'page',
                'normal',
                'default'
            );
        }

        public function akuna_page_meta_box_html($post)
        {
            $prefix = '_akuna_'; // global $prefix;

            $subtitle = get_post_meta($post->ID, $prefix . 'page_subtitle', true) ? get_post_meta($post->ID, $prefix . 'page_subtitle', true) : '';

?>
            <label for="page_subtitle"><?php esc_html_e('Page Subtitle', 'akuna-custom-metaboxes'); ?></label>
            <br />
            <input class="widefat" type="text" name="akuna_page_subtitle_field" id="page_subtitle" value="<?php echo esc_attr($subtitle); ?>" />
            </p>
<?php
            echo '<input type="hidden" name="custom_product_field_nonce" value="' . wp_create_nonce() . '">';
        }

        public function akuna_save_page_meta($post_id)
        {
            $prefix = '_akuna_'; // global $prefix;

            // We need to verify this with the proper authorization (security stuff).
            // Check if our nonce is set.
            if (!isset($_POST['custom_product_field_nonce'])) {
                return $post_id;
            }
            $nonce = $_REQUEST['custom_product_field_nonce'];
            //Verify that the nonce is valid.
            if (!wp_verify_nonce($nonce)) {
                return $post_id;
            }
            // If this is an autosave, our form has not been submitted, so we don't want to do anything.
            if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
                return $post_id;
            }
            // Check the user's permissions.
            if ('product' == $_POST['post_type']) {
                if (!current_user_can('edit_product', $post_id))
                    return $post_id;
            } else {
                if (!current_user_can('edit_post', $post_id))
                    return $post_id;
            }

            // Sanitize user input and update the meta field in the database.
            update_post_meta($post_id, $prefix . 'page_subtitle', wp_kses_post($_POST['page_subtitle']));
        }
    }
endif;

return new Akuna_Page_Meta();
