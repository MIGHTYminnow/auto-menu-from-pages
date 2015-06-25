<?php

/**
 * The public-facing functionality of the plugin.
 *
 * @link       http://wordpress.org/plugins/auto-menu-from-pages
 * @since      1.0.0
 *
 * @package    Auto_Menu_From_Pages
 * @subpackage Auto_Menu_From_Pages/public
 */

/**
 * The public-facing functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the dashboard-specific stylesheet and JavaScript.
 *
 * @package    Auto_Menu_From_Pages
 * @subpackage Auto_Menu_From_Pages/public
 * @author     MIGHTYminnow & Mickey Kay mickey@mickeykaycreative.com
 */
class Auto_Menu_From_Pages_Public {

	/**
	 * The main plugin instance.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      Auto_Menu_From_Pages    $plugin    The main plugin instance.
	 */
	private $plugin;

	/**
	 * The slug of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $plugin_slug    The slug of this plugin.
	 */
	private $plugin_slug;

	/**
	 * The display name of this plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $plugin_name    The plugin display name.
	 */
	protected $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

	/**
	 * The instance of this class.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      Auto_Menu_From_Pages_Public    $instance    The instance of this class.
	 */
	private static $instance = null;

	/**
     * Creates or returns an instance of this class.
     *
     * @return    Auto_Menu_From_Pages_Public    A single instance of this class.
     */
    public static function get_instance( $plugin ) {

        if ( null == self::$instance ) {
            self::$instance = new self( $plugin );
        }

        return self::$instance;

    }

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @var      string    $plugin_slug    The name of the plugin.
	 * @var      string    $version        The version of this plugin.
	 */
	public function __construct( $plugin ) {

		$this->plugin = $plugin;
		$this->plugin_slug = $this->plugin->get( 'slug' );
		$this->plugin_name = $this->plugin->get( 'name' );
		$this->version = $this->plugin->get( 'version' );

	}

	/**
	 * Register the stylesheets for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Auto_Menu_From_Pages_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Auto_Menu_From_Pages_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_style( $this->plugin_slug, plugin_dir_url( __FILE__ ) . 'css/auto-menu-from-pages-public.css', array(), $this->version, 'all' );

	}

	/**
	 * Register the stylesheets for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Auto_Menu_From_Pages_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Auto_Menu_From_Pages_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_script( $this->plugin_slug, plugin_dir_url( __FILE__ ) . 'js/auto-menu-from-pages-public.js', array( 'jquery' ), $this->version, false );

	}

	/**
	 * Filter widget instance.
	 *
	 * For use with Simple Section Nav plugin to incorporate excluded pages.
	 *
	 * @since    1.2.0
	 *
	 * @param    array        $instance    The current widget instance's settings.
	 * @param    WP_Widget    $widget      The current widget instance.
	 * @param    array        $args        An array of default widget arguments.
	 */
	public function filter_widget_instance( $instance, $widget, $args ) {

		// Setup array of ID's of widgets to filter.
		$widgets_to_filter = array( 'simple-section-nav' );

		// Only proceed if we're filtering intended widgets.
		if ( ! in_array( $widget->id_base, $widgets_to_filter ) ) {
			return $instance;
		}

		// Get comma-separated list of excluded page ID's.
		$exclude_ids_array = $this->plugin->get_excluded_page_ids();
		$exclude_ids = implode( ',', $exclude_ids_array );

		// Filter in excluded page ID's.
		$instance['exclude'] = $exclude_ids;

		return $instance;

	}

}
