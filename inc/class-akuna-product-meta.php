<?php

/**
 * Akuna Product Meta Class
 *
 * @package  Akuna Custom Meta
 * @since    1.0.0
 */

if (!defined('ABSPATH')) {
    exit;
}

if (!class_exists('Akuna_Product_Meta')) :
    class Akuna_Product_Meta
    {
        public function __construct()
        {
            add_action('add_meta_boxes', array($this, 'akuna_add_product_meta_box'));
            add_action('save_post', array($this, 'akuna_save_product_meta'), 10, 1);
        }

        public function akuna_add_product_meta_box()
        {
            add_meta_box(
                'custom_product_meta_box',
                __('Additional Product Information <em>(optional)</em>', 'akuna-custom-metaboxes'),
                array($this, 'akuna_product_meta_box_html'),
                'product',
                'normal',
                'default'
            );
        }

        public function akuna_product_meta_box_html($post)
        {
            $prefix = '_akuna_'; // global $prefix;

            $ingredients = get_post_meta($post->ID, $prefix . 'ingredients_wysiwyg', true) ? get_post_meta($post->ID, $prefix . 'ingredients_wysiwyg', true) : '';
            $goodtoknow = get_post_meta($post->ID, $prefix . 'goodtoknow_wysiwyg', true) ? get_post_meta($post->ID, $prefix . 'goodtoknow_wysiwyg', true) : '';

            $args['textarea_rows'] = 6;
            echo '<p>' . __('Ingredients And Science', 'akuna-custom-metaboxes') . '</p>';
            wp_editor($ingredients, 'ingredients_wysiwyg', $args);
            echo '<p>' . __('Good To Know', 'akuna-custom-metaboxes') . '</p>';
            wp_editor($goodtoknow, 'goodtoknow_wysiwyg', $args);
            echo '<input type="hidden" name="custom_product_field_nonce" value="' . wp_create_nonce() . '">';
        }

        public function akuna_save_product_meta($post_id)
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
            update_post_meta($post_id, $prefix . 'ingredients_wysiwyg', wp_kses_post($_POST['ingredients_wysiwyg']));
            update_post_meta($post_id, $prefix . 'goodtoknow_wysiwyg', wp_kses_post($_POST['goodtoknow_wysiwyg']));
        }
    }
endif;

return new Akuna_Product_Meta();
