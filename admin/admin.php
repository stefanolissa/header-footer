<?php

namespace HeaderFooter;

defined('ABSPATH') || exit;

class Admin {

    public function __construct() {
        add_action('admin_menu', function () {
            add_options_page('Head and Footer', 'Head and Footer', 'administrator', 'header-footer-index', function () {
                include __DIR__ . '/index.php';
            });
        });

        if ($this->is_admin_page()) {
            header('X-XSS-Protection: 0');
            add_action('admin_enqueue_scripts', function () {
                $plugin_url = plugins_url('header-footer');
                wp_enqueue_script('jquery-ui-tabs');
                wp_enqueue_style('hefo-jquery-ui', $plugin_url . '/vendor/jquery-ui/jquery-ui.min.css', []);
                wp_enqueue_style('hefo', $plugin_url . '/admin/admin.css', [], time());
                wp_enqueue_code_editor(['type' => 'php']);
                wp_enqueue_script('jquery-ui-tabs');
            });
            include __DIR__ . '/controls.php';
        }
    }

    function is_admin_page() {
        return isset($_GET['page']) && strpos($_GET['page'], 'header-footer-') === 0;
    }

}

new Admin();



//add_action('add_meta_boxes', function () {
//    add_meta_box('hefo', __('Head and Footer', 'header-footer'), 'hefo_meta_boxes_callback', ['post', 'page']);
//});
//
//add_action('save_post', 'hefo_save_post');
//
//function hefo_meta_boxes_callback($post) {
//
//    // Use nonce for verification
//    wp_nonce_field(plugin_basename(__FILE__), 'hefo');
//
//    // The actual fields for data entry
//    // Use get_post_meta to retrieve an existing value from the database and use the value for the form
//    $before = get_post_meta($post->ID, 'hefo_before', true);
//    $after = get_post_meta($post->ID, 'hefo_after', true);
//    echo '<label>';
//    echo '<input type="checkbox" id="hefo_before" name="hefo_before" ' . (empty($before) ? "" : "checked") . '> ';
//    esc_html_e("Disable top injection", 'header-footer');
//    echo '</label> ';
//    echo '<br>';
//    echo '<label>';
//    echo '<input type="checkbox" id="hefo_after" name="hefo_after]" ' . (empty($after) ? "" : "checked") . '> ';
//    esc_html_e("Disable bottom injection", 'header-footer');
//    echo '</label> ';
//}
//
//function hefo_save_post($post_id) {
//
//    if (!isset($_POST['hefo']))
//        return;
//
//    // First we need to check if the current user is authorised to do this action.
//    if (isset($_POST['post_type']) && 'page' == $_POST['post_type']) {
//        if (!current_user_can('edit_page', $post_id))
//            return;
//    } else {
//        if (!current_user_can('edit_post', $post_id))
//            return;
//    }
//
//    // Secondly we need to check if the user intended to change this value.
//    if (!wp_verify_nonce($_POST['hefo'], plugin_basename(__FILE__)))
//        return;
//
//    update_post_meta($post_id, 'hefo_before', isset($_REQUEST['hefo_before']) ? 1 : 0);
//    update_post_meta($post_id, 'hefo_after', isset($_REQUEST['hefo_after']) ? 1 : 0);
//}
