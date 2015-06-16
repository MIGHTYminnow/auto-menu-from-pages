<?php

/**
 * Fired during plugin activation
 *
 * @link       http://wordpress.org/plugins/auto-menu-from-pages
 * @since      1.0.0
 *
 * @package    Auto_Menu_From_Pages
 * @subpackage Auto_Menu_From_Pages/includes
 */

/**
 * Fired during plugin activation.
 *
 * This class defines all code necessary to run during the plugin's activation.
 *
 * @since      1.0.0
 * @package    Auto_Menu_From_Pages
 * @subpackage Auto_Menu_From_Pages/includes
 * @author     MIGHTYminnow & Mickey Kay mickey@mickeykaycreative.com
 */
class Auto_Menu_From_Pages_Activator {

	/**
	 * Run plugin activation actions.
	 *
	 * @since    1.0.0
	 */
	public static function activate() {

		// Get plugin and admin instances.
		$plugin = Auto_Menu_From_Pages::get_instance();
		$plugin_admin = Auto_Menu_From_Pages_Admin::get_instance( $plugin );

		// Force initial menu creation/update run.
		$plugin_admin->create_auto_menu();
		$plugin_admin->maybe_sync_auto_menu();

		// Add plugin activation option for redirect.
		add_option( $plugin->get( 'slug' ) . '_activated', true );

	}

}
