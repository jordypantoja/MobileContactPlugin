<?php
/**
 * @package   Mobile Contact Section
 *
 *
 * @wordpress-plugin
 * Plugin Name:       Mobile Contact Section
 * Description:       Add Sticky Mobile contact section to your website.
 * Version:           1.0
 * Requires at least: 3.5
 * Requires PHP:      5.2
 * Author:            Clarity Creative Group
 * Text Domain:       mobile-contact-section
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/*----------------------------------------------------------------------------*
 * Public-Facing Functionality
 *----------------------------------------------------------------------------*/

require_once( plugin_dir_path( __FILE__ ) . 'public/class-mobile-contact-section.php' );

/*
 * Register hooks that are fired when the plugin is activated or deactivated.
 * When the plugin is deleted, the uninstall.php file is loaded.
 */
register_activation_hook( __FILE__, array( 'Mobile_Contact_Section', 'activate' ) );
register_deactivation_hook( __FILE__, array( 'Mobile_Contact_Section', 'deactivate' ) );

add_action( 'plugins_loaded', array( 'Mobile_Contact_Section', 'get_instance' ) );

/*----------------------------------------------------------------------------*
 * Dashboard and Administrative Functionality
 *----------------------------------------------------------------------------*/

/*
 * If you want to include Ajax within the dashboard, change the following
 * conditional to:
 *
 * if ( is_admin() ) {
 *   ...
 * }
 *
 * The code below is intended to to give the lightest footprint possible.
 */
if ( is_admin() && ( ! defined( 'DOING_AJAX' ) || ! DOING_AJAX ) ) {

	require_once( plugin_dir_path( __FILE__ ) . 'admin/class-mobile-contact-section-admin.php' );
	add_action( 'plugins_loaded', array( 'Mobile_Contact_Bar_Admin', 'get_instance' ) );

}
