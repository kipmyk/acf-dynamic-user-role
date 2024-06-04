<?php
/**
* Plugin Name: ACF: Dynamic User Role
* Description: Change default role dynamically on role selection change - ACF role-based conditions on user new / edit form
* Version: 1.0.8
* Author: Mike Kipruto
* Author URI: https://kipmyk.co.ke
**/


if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

class MK_DynamicUserRole
{
    function __construct() {
        add_action('acf/input/admin_head', array($this, 'acf_dynamic_default_role'));
        add_action('wp_ajax_acf_dynamic_user_role', array($this, 'ajax_acf_dynamic_user_role'));
        add_action('wp_ajax_acf_dynamic_default_role', array($this, 'ajax_acf_dynamic_default_role'));
    }

    function acf_dynamic_default_role() {
        global $pagenow;

        if ($pagenow == 'user-new.php') {
            ?>
            <script>
                jQuery(document).ready(function ($) {
                    $('select#role').on('change', function (e) {
                        $.ajax({
                            url: '<?php echo admin_url('admin-ajax.php'); ?>',
                            type: 'post',
                            data: {
                                action: 'acf_dynamic_default_role',
                                security: '<?php echo wp_create_nonce('acf_dynamic_default_role'); ?>',
                                default_role: e.target.value,
                            },
                            success: function () {
                                window.location.reload();
                            }
                        });
                    });
                });
            </script>
            <?php
        } else if ($pagenow == 'user-edit.php') {
            ?>
            <script>
                jQuery(document).ready(function ($) {
                    $('select#role').on('change', function (e) {
                        $.ajax({
                            url: '<?php echo admin_url('admin-ajax.php'); ?>',
                            type: 'post',
                            data: {
                                action: 'acf_dynamic_user_role',
                                security: '<?php echo wp_create_nonce('acf_dynamic_user_role'); ?>',
                                user_id: <?php echo sanitize_key($_GET['user_id']); ?>,
                                user_role: e.target.value,
                            },
                            success: function () {
                                window.location.reload();
                            }
                        });
                    });
                });
            </script>
            <?php
        }
    }

    function ajax_acf_dynamic_default_role() {
        check_ajax_referer('acf_dynamic_default_role', 'security');

        if (isset($_POST['default_role'])) {
            $default_role = sanitize_text_field($_POST['default_role']);
            update_option('default_role', $default_role);
            wp_send_json_success(__('Default role updated', 'acf'));
        } else {
            wp_send_json_error(__('Failed to update default role', 'acf'));
        }
        wp_die();
    }

    function ajax_acf_dynamic_user_role() {
        check_ajax_referer('acf_dynamic_user_role', 'security');

        if (isset($_POST['user_id']) && isset($_POST['user_role'])) {
            $user_id = sanitize_text_field($_POST['user_id']);
            $user_role = sanitize_text_field($_POST['user_role']);
            $user = new WP_User($user_id);
            $user->set_role($user_role);
            wp_send_json_success(__('User role updated', 'acf'));
        } else {
            wp_send_json_error(__('Failed to update user role', 'acf'));
        }
        wp_die();
    }
}

new MK_DynamicUserRole();
