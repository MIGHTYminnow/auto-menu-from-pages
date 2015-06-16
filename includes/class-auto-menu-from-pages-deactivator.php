<?php

/**
 * Fired during plugin deactivation
 *
 * @link       http://wordpress.org/plugins/auto-menu-from-pages
 * @since      1.0.0
 *
 * @package    Auto_Menu_From_Pages
 * @subpackage Auto_Menu_From_Pages/includes
 */

/**
 * Fired during plugin deactivation.
 *
 * This class defines all code necessary to run during the plugin's deactivation.
 *
 * @since      1.0.0
 * @package    Auto_Menu_From_Pages
 * @subpackage Auto_Menu_From_Pages/includes
 * @author     MIGHTYminnow & Mickey Kay mickey@mickeykaycreative.com
 */
class Auto_Menu_From_Pages_Deactivator {

	/**
	 * Run plugin deactivation actions.
	 *
	 * @since    1.0.0
	 */
	public static function deactivate() {

		// Get plugin and admin instances.
		$plugin = Auto_Menu_From_Pages::get_instance();
		$plugin_admin = Auto_Menu_From_Pages_Admin::get_instance( $plugin );

		// Force destruction of auto menu on deactivation.
		// $plugin_admin->destroy_auto_menu();

	}

}
