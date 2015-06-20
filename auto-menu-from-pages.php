<?php

/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * Dashboard. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              http://wordpress.org/plugins/auto-menu-from-pages
 * @since             1.0.0
 * @package           Auto_Menu_From_Pages
 *
 * @wordpress-plugin
 * Plugin Name:       Auto Menu From Pages
 * Plugin URI:        http://wordpress.org/plugins/auto-menu-from-pages
 * Description:       Automatically generate a navigation menu from your page hierarchy.
 * Version:           1.2.0
 * Author:            MIGHTYminnow
 * Author URI:        http://mightyminnow.com/plugin-landing-page?utm_source=auto-menu-from-pages&utm_medium=plugin-repo&utm_campaign=WordPress%20Plugins
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       auto-menu-from-pages
 * Domain Path:       /languages
 */

/**
 * @todo
 *
 * Add install notice explaining where to set up menu.
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-auto-menu-from-pages-activator.php
 */
function activate_auto_menu_from_pages() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-auto-menu-from-pages-activator.php';
	Auto_Menu_From_Pages_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-auto-menu-from-pages-deactivator.php
 */
function deactivate_auto_menu_from_pages() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-auto-menu-from-pages-deactivator.php';
	Auto_Menu_From_Pages_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'activate_auto_menu_from_pages' );
register_deactivation_hook( __FILE__, 'deactivate_auto_menu_from_pages' );

/**
 * The core plugin class that is used to define internationalization,
 * dashboard-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-auto-menu-from-pages.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function run_auto_menu_from_pages() {

	// Pass main plugin file through to plugin class for later use.
	$args = array(
		'plugin_file' => __FILE__,
	);

	$plugin = Auto_Menu_From_Pages::get_instance( $args );
	$plugin->run();

}
run_auto_menu_from_pages();
