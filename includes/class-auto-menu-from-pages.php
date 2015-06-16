<?php

/**
 * The file that defines the core plugin class
 *
 * A class definition that includes attributes and functions used across both the
 * public-facing side of the site and the dashboard.
 *
 * @link       http://wordpress.org/plugins/auto-menu-from-pages
 * @since      1.0.0
 *
 * @package    Auto_Menu_From_Pages
 * @subpackage Auto_Menu_From_Pages/includes
 */

/**
 * The core plugin class.
 *
 * This is used to define internationalization, dashboard-specific hooks, and
 * public-facing site hooks.
 *
 * Also maintains the unique identifier of this plugin as well as the current
 * version of the plugin.
 *
 * @since      1.0.0
 * @package    Auto_Menu_From_Pages
 * @subpackage Auto_Menu_From_Pages/includes
 * @author     MIGHTYminnow & Mickey Kay mickey@mickeykaycreative.com
 */
class Auto_Menu_From_Pages {

	/**
	 * The loader that's responsible for maintaining and registering all hooks that power
	 * the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      Auto_Menu_From_Pages_Loader    $loader    Maintains and registers all hooks for the plugin.
	 */
	protected $loader;

	/**
	 * The unique identifier of this plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $slug    The string used to uniquely identify this plugin.
	 */
	protected $slug;

	/**
	 * The display name of this plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $name    The plugin display name.
	 */
	protected $name;

	/**
	 * The name of this plugin the auto-generated menu.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $menu_name    The name of this plugin the auto-generated menu.
	 */
	protected $menu_name;

	/**
	 * The current version of the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $version    The current version of the plugin.
	 */
	protected $version;

	/**
	 * The instance of this class.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      Auto_Menu_From_Pages    $instance    The instance of this class.
	 */
	private static $instance = null;

	/**
     * Creates or returns an instance of this class.
     *
     * @return    Auto_Menu_From_Pages    A single instance of this class.
     */
    public static function get_instance( $args = array() ) {

        if ( null == self::$instance ) {
            self::$instance = new self( $args );
        }

        return self::$instance;

    }

	/**
	 * Define the core functionality of the plugin.
	 *
	 * Set the plugin name and the plugin version that can be used throughout the plugin.
	 * Load the dependencies, define the locale, and set the hooks for the Dashboard and
	 * the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function __construct() {

		$this->slug = 'auto-menu-from-pages';
		$this->name = __( 'Auto Menu From Pages', 'auto-menu-from-pages' );
		$this->menu_name = apply_filters( 'amfp_menu_title', $this->name );
		$this->version = '1.0.2';

		$this->load_dependencies();
		$this->set_locale();
		$this->define_admin_hooks();
		$this->define_public_hooks();
		$this->define_shared_hooks();

	}

	/**
	 * Load the required dependencies for this plugin.
	 *
	 * Include the following files that make up the plugin:
	 *
	 * - Auto_Menu_From_Pages_Loader. Orchestrates the hooks of the plugin.
	 * - Auto_Menu_From_Pages_i18n. Defines internationalization functionality.
	 * - Auto_Menu_From_Pages_Admin. Defines all hooks for the dashboard.
	 * - Auto_Menu_From_Pages_Public. Defines all hooks for the public side of the site.
	 *
	 * Create an instance of the loader which will be used to register the hooks
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function load_dependencies() {

		/**
		 * The class responsible for orchestrating the actions and filters of the
		 * core plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-auto-menu-from-pages-loader.php';

		/**
		 * The class responsible for defining internationalization functionality
		 * of the plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-auto-menu-from-pages-i18n.php';

		/**
		 * The class responsible for defining all actions that occur in the Dashboard.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-auto-menu-from-pages-admin.php';

		/**
		 * The class responsible for defining all actions that occur in the public-facing
		 * side of the site.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'public/class-auto-menu-from-pages-public.php';

		$this->loader = new Auto_Menu_From_Pages_Loader();

	}

	/**
	 * Define the locale for this plugin for internationalization.
	 *
	 * Uses the Auto_Menu_From_Pages_i18n class in order to set the domain and to register the hook
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function set_locale() {

		$plugin_i18n = new Auto_Menu_From_Pages_i18n();
		$plugin_i18n->set_domain( $this->slug );

		$this->loader->add_action( 'plugins_loaded', $plugin_i18n, 'load_plugin_textdomain' );

	}

	/**
	 * Register all of the hooks related to the dashboard functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_admin_hooks() {

		$plugin_admin = Auto_Menu_From_Pages_Admin::get_instance( $this );

		// Do activation redirect.
		$this->loader->add_action( 'admin_init', $plugin_admin, 'do_activation_redirect' );

		// Create auto menu object.
		$this->loader->add_action( 'admin_init', $plugin_admin, 'create_auto_menu' );

		// Add functionality to dismiss notice.
		$this->loader->add_action( 'admin_init', $plugin_admin, 'dismiss_notice' );

		// Enqueue admin scripts and styles - both front- and back- end since it affects the admin bar.
		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_scripts' );
		$this->loader->add_action( 'wp_enqueue_scripts', $plugin_admin, 'enqueue_scripts' );

		// Add body class to distinguish auto menu admin page.
		$this->loader->add_action( 'admin_body_class', $plugin_admin, 'admin_body_class' );

		// Do admin notices.
		$this->loader->add_action( 'admin_notices', $plugin_admin, 'admin_notices' );

		// Create admin bar link.
		$this->loader->add_action( 'admin_bar_menu', $plugin_admin, 'create_admin_bar_link', 999 );

		// Filter admin menu walker to prevent output in menu editor.
		$this->loader->add_filter( 'wp_edit_nav_menu_walker', $plugin_admin, 'filter_auto_menu_walker_to_hide', 10, 2 );

		// Do metabox functionality to exclude page from auto menu.
		$this->loader->add_action( 'add_meta_boxes', $plugin_admin, 'add_metabox' );
		$this->loader->add_action( 'save_post', $plugin_admin, 'save_metabox' );

		// Synch menu via AJAX.
		$this->loader->add_action( 'wp_ajax_sync_auto_menu', $plugin_admin, 'force_sync_auto_menu' );

		/**
		 * Auto sync menu on shutdown.
		 *
		 * By default, this feature is turned off, and can be enabled via the
		 * amfp_auto_sync_menu filter.
		 */
		if ( is_admin() && apply_filters( 'amfp_auto_sync_menu', false ) ) {
			$this->loader->add_action( 'shutdown', $plugin_admin, 'maybe_sync_auto_menu', 15 ); // Load after Exclude Pages plugin.
		}

	}

	/**
	 * Register all of the hooks related to the public-facing functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_public_hooks() {
		$plugin_public = Auto_Menu_From_Pages_Public::get_instance( $this );
	}

	/**
	 * Register all of the hooks related to both the admin and public-facing
	 * functionality of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_shared_hooks() {
		$plugin_shared = $this;
	}

	/**
	 * Run the loader to execute all of the hooks with WordPress.
	 *
	 * @since    1.0.0
	 */
	public function run() {
		$this->loader->run();
	}

	/**
	 * The reference to the class that orchestrates the hooks with the plugin.
	 *
	 * @since     1.0.0
	 * @return    Auto_Menu_From_Pages_Loader    Orchestrates the hooks of the plugin.
	 */
	public function get_loader() {
		return $this->loader;
	}

	/**
	 * Get any plugin property.
	 *
	 * @since     1.0.0
	 * @return    mixed    The plugin property.
	 */
	public function get( $property = '' ) {
		return $this->$property;
	}

}
