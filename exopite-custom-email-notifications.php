<?php

/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              http://joe.szalai.org
 * @since             1.0.0
 * @package           Exopite_Custom_Email_Notifications
 *
 * @wordpress-plugin
 * Plugin Name:       Custom Email Notifications
 * Plugin URI:        http://joe.szalai.org/exopite/custom-email-notifications
 * Description:       Send email notifications on add, update or comment a post, page or custom post type.
 * Version:           1.0.0
 * Author:            Joe Szalai
 * Author URI:        http://joe.szalai.org
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       exopite-custom-email-notifications
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

define( 'EXOPITE_CUSTOM_EMAIL_NOTIFICATIONS_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );

/*
 * ToDo:
 * - metabox with email selection (checkbox or multiselect)
 * - send email (post/page): new post, update post, new comment (checkbox or multiselect)
 *   - comments:
 *     https://wordpress.stackexchange.com/questions/36033/if-new-comment-posted-in-custom-post-send-notification-to-custom-email-from-cu
 * - send email on admin/not admin login -> settings
 * - settings (codestar):
 *   - email template editor
 *   - admin/not admin login/wrong login?
 * - email template from template (editor with [#variable] fields)
 */

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-exopite-custom-email-notifications-activator.php
 */
function activate_exopite_custom_email_notifications() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-exopite-custom-email-notifications-activator.php';
	Exopite_Custom_Email_Notifications_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-exopite-custom-email-notifications-deactivator.php
 */
function deactivate_exopite_custom_email_notifications() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-exopite-custom-email-notifications-deactivator.php';
	Exopite_Custom_Email_Notifications_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'activate_exopite_custom_email_notifications' );
register_deactivation_hook( __FILE__, 'deactivate_exopite_custom_email_notifications' );

/**
 * Initialize custom templater
 */
if( ! class_exists( 'Exopite_Template' ) ) {
    require EXOPITE_CUSTOM_EMAIL_NOTIFICATIONS_PLUGIN_DIR . 'includes/libraries/class-exopite-template.php';
}

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-exopite-custom-email-notifications.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function run_exopite_custom_email_notifications() {

	$plugin = new Exopite_Custom_Email_Notifications();
	$plugin->run();

}
run_exopite_custom_email_notifications();
