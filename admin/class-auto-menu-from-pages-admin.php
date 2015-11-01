<?php

/**
 * The dashboard-specific functionality of the plugin.
 *
 * @link       http://wordpress.org/plugins/auto-menu-from-pages
 * @since      1.0.0
 *
 * @package    Auto_Menu_From_Pages
 * @subpackage Auto_Menu_From_Pages/admin
 */

/**
 * The dashboard-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the dashboard-specific stylesheet and JavaScript.
 *
 * @package    Auto_Menu_From_Pages
 * @subpackage Auto_Menu_From_Pages/admin
 * @author     MIGHTYminnow & Mickey Kay mickey@mickeykaycreative.com
 */
class Auto_Menu_From_Pages_Admin {

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
	 * The highest post ID in the database.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      int    $highest_db_post_id    The highest post ID in the database.
	 */
	private $highest_db_post_id;

	/**
	 * MIGHTYminnow admin link.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $Mm_url    URL to link to MIGHTYminnow site.
	 */
	private $Mm_url;

	/**
	 * The instance of this class.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      Auto_Menu_From_Pages_Admin    $instance    The instance of this class.
	 */
	private static $instance = null;

	/**
     * Creates or returns an instance of this class.
     *
     * @return    Auto_Menu_From_Pages_Admin    A single instance of this class.
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
	 * @var      string    $plugin_slug       The name of this plugin.
	 * @var      string    $version    The version of this plugin.
	 */
	public function __construct( $plugin ) {

		global $wpdb;

		$this->plugin = $plugin;
		$this->plugin_slug = $this->plugin->get( 'slug' );
		$this->plugin_name = $this->plugin->get( 'name' );
		$this->version = $this->plugin->get( 'version' );
		$this->Mm_url = '//mightyminnow.com/plugin-landing-page?utm_source=' . $this->plugin_slug . '&utm_medium=plugin&utm_campaign=WordPress%20Plugins';

		// Get highest post ID in database.
		$highest_id_array = $wpdb->get_col( "SELECT max(ID) FROM $wpdb->posts" );
		$this->highest_db_post_id = $highest_id_array[0];

	}

	/**
	 * Redirect to auto menu admin page on activation.
	 *
	 * @since    1.0.0
	 */
	public function do_activation_redirect() {

		if ( is_admin() && get_option( $this->plugin_slug . '_activated', false ) ) {

			// Remove activation option.
			delete_option( $this->plugin_slug . '_activated' );

			// Redirect to menu page.
			wp_redirect( admin_url( 'nav-menus.php?menu=' . $this->get_auto_menu_id() ) );
			exit;

		}

	}

	/**
	 * Register the JavaScript for the dashboard.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts( $hook ) {

		// Only proceed if user is logged in.
		if ( ! is_user_logged_in() ) {
			return;
		}

		// Enqueue admin styles if and only if user is logged in and viewing admin bar.
		wp_enqueue_style( $this->plugin_slug, plugin_dir_url( __FILE__ ) . 'css/auto-menu-from-pages-admin.css', array(), $this->version, 'all' );

		// Admin scripts.
		wp_enqueue_script( $this->plugin_slug, plugin_dir_url( __FILE__ ) . 'js/auto-menu-from-pages-admin.js', array( 'jquery' ), $this->version, false );

		// Only enqueue scripts on the nav menus page.
		if ( 'nav-menus.php' == $hook ) {

			// Pass description message to JS for admin menu screen.
			$php_admin_variables = array(
				'menu_title' => $this->plugin->get( 'menu_name' ),
				'menu_desc_text' => __( '<b>Note:</b> Pages/posts can not be added to this menu, as it is auto-generated from the existing page hierarchy.', 'auto-menu-from-pages' ),
			);
			wp_localize_script( $this->plugin_slug, 'amfpVars', $php_admin_variables );

		}

		// Manually pass ajaxurl to front-end.
		if ( ! is_admin() ) {
			wp_localize_script( $this->plugin_slug, 'ajaxurl', admin_url( 'admin-ajax.php' ) );
		}

	}

	/**
	 * Create auto menu object.
	 *
	 * @since  1.0.0
	 */
	public function create_auto_menu() {

		// Generate menu title.
		$menu_name = $this->plugin->get( 'menu_name' );

		// Create new menu if it doesn't already exist.
		$menu_exists = wp_get_nav_menu_object( $menu_name );
		if ( ! $menu_exists ) {
			wp_create_nav_menu( $menu_name );
		}

	}

	/**
	 * Destroy auto menu object (currently not used).
	 *
	 * @since  1.0.0
	 */
	public function destroy_auto_menu() {

		// Generate menu title.
		$menu_name = $this->plugin->get( 'menu_name' );

		// Create new menu if it doesn't already exist.
		$menu_exists = wp_get_nav_menu_object( $menu_name );
		if ( $menu_exists ) {
			wp_delete_nav_menu( $menu_name );
		}

	}

	/**
	 * Force sync auto menu.
	 *
	 * @since  1.1.0
	 */
	public function force_sync_auto_menu() {

		// Fire actual update function.
		$this->maybe_sync_auto_menu( true );

	}

	/**
	 * Sync auto menu if certain actions have fired.
	 *
	 * @since  1.0.0
	 */
	public function maybe_sync_auto_menu( $sync = false ) {

		// If the $sync flag is there, proceed directly to building the menu,
		// otherwise check whether one of our trigger actions has fired.
		if ( ! $sync ) {

			// Define actions that warrant rebuilding the menu.
			$trigger_actions = array(
				'amfp_force_update',
				'amfp_force_sync',
				'save_post',
				'updated_option',
				'load-pages_page_mypageorder', // My Page Order plugin
			);

			// Check if any have been triggered.
			$update_menu = false;
			foreach ( $trigger_actions as $action ) {

				if ( did_action( $action ) ) {
					$update_menu = true;
					break;
				}
			}

			// Only run if something changed on this load.
			if ( ! $update_menu ) {
				return;
			}
		}

		// Get auto menu ID.
		$auto_menu_id = $this->get_auto_menu_id();

		// Get saved auto menu items.
		$menu = wp_get_nav_menu_object( $auto_menu_id );

		// Exit if the menu doesn't exist (edge case after deactivation).
		if ( empty( $menu->term_id ) ) {
			return false;
		}

		$menu_item_ids = get_objects_in_term( $menu->term_id, 'nav_menu' );

		// Get array of non-excluded pages.
		$args = array(
			'post_type' => 'page',
			'posts_per_page' => -1,
			'order'     => 'ASC',
			'orderby'    => 'menu_order post_modified',
			'meta_query' => array(
				'relation' => 'OR',
				array(
					'key'       => '_amfp_exclude_from_menu',
					'compare'   => '!=',
					'value'     => 1,
				),
				array(
					'key'       => '_amfp_exclude_from_menu',
					'compare'   => 'NOT EXISTS',
					'value'     => 1,
				),
			),
		);
		$pages = get_posts( $args );

		$i = 1;
		foreach ( $pages as $index => $page ) {

			// Exclude this page if it has an excluded ancestor.
			if ( $this->has_excluded_ancestor( $page->ID ) ) {
				unset( $pages[ $index ] );
				continue;
			}

			// Set up menu item database ID based on post ID.
			$menu_item_db_id = $this->get_page_auto_menu_item_id( $page->ID );

			// Set up menu item parent database ID.
			$parent_menu_item_db_id = 0;

			// If the post has a parent, ensure we have a menu item ID for the parent.
			if ( $page->post_parent ) {

				$parent_menu_item_db_id = $this->get_page_auto_menu_item_id( $page->post_parent );

				// If the parent menu item hasn't already been created, create it now.
				$this->create_new_nav_menu_item( $parent_menu_item_db_id );
			}

			// Set up menu item args from page.
			$args = array(
				'menu-item-object-id' => $page->ID,
				'menu-item-object'    => $page->post_type,
				'menu-item-type'      => 'post_type',
				'menu-item-parent-id' => $parent_menu_item_db_id,
				'menu-item-position'  => $i,
				'menu-item-status'    => 'publish',
			);

			$this->create_new_nav_menu_item( $menu_item_db_id );

			$item = wp_update_nav_menu_item( $auto_menu_id, $menu_item_db_id, $args );

			$i++;
		}

		// Get all non-excluded page ID's.
		$included_menu_item_ids = array_map( function( $page ) {
			return $this->get_page_auto_menu_item_id( $page->ID );
		}, $pages );

		// Remove any items that aren't published or are otherwise excluded.
		foreach ( $menu_item_ids as $menu_item_id ) {

			$object_id = get_post_meta( $menu_item_id, '_menu_item_object_id', true );
			$post_status = get_post_status( $object_id );

			if ( ! in_array( $menu_item_id, $included_menu_item_ids ) ||
				'publish' != $post_status
			) {
				wp_delete_post( $menu_item_id );
			}
		}

		// Die properly if called via AJAX.
		if ( defined( 'DOING_AJAX' ) && DOING_AJAX ) {
			wp_die();
		}

	}

	/**
	 * Create a new 'nav_menu_item' post using the specified ID.
	 *
	 * @since  1.3.0
	 *
	 * @param  int  $menu_item_id  The menu item ID.
	 */
	public function create_new_nav_menu_item( $menu_item_id ) {

		if ( ! is_nav_menu_item( $menu_item_id ) ) {
			$menu_item_post = array(
				'import_id' => $menu_item_id, // Parameter used to force specific ID of new post.
				'post_type' => 'nav_menu_item',
			);

			wp_insert_post( $menu_item_post );
		}
	}

	/**
	 * Check if page has excluded ancestor.
	 *
	 * @since    1.0.0
	 *
	 * @param    [type]    $page_id    [description]
	 *
	 * @return    bool      [description]
	 */
	public function has_excluded_ancestor( $page_id ) {

		$parent_id = get_post( $page_id )->post_parent;

		// Return false if there's no parent.
		if ( ! $parent_id ) {
			return false;
		}

		// Get parent post.
		$parent = get_post( $parent_id );

		// Return true if parent is excluded.
		if ( get_post_meta( $parent->ID, '_amfp_exclude_from_menu', true ) ) {
			return true;
		}

		// Otherwise we have another ancestor to check
		return $this->has_excluded_ancestor( $parent->ID );

	}

	/**
	 * Get the ID of a menu item from a post ID.
	 *
	 * @since     1.0.0
	 *
	 * @param     int    $post_id    Post ID.
	 *
	 * @return    int                Corresponding menu item ID.
	 */
	public function get_page_auto_menu_item_id( $page_id ) {

		// Check if page already has assigned menu item.
		$menu_item_id = get_post_meta( $page_id, '_amfp_menu_item_id', true );

		// Get object id (post it points to) of menu item.
		$menu_item_object_id = get_post_meta( $menu_item_id, '_menu_item_object_id', true );

		/**
		 * If we already have a valid menu item ID, and it's points to
		 * this post, then return it.
		 */
		if ( $menu_item_id && is_nav_menu_item( $menu_item_id ) && $menu_item_object_id == $page_id ) {
			return $menu_item_id;
		}

		/**
		 * If no menu item is set or the value isn't good, generate a new menu item
		 * ID by incrementing the highest post ID in the database by one.
		 */

		// Make sure ID isn't already taken.
		while ( FALSE !== get_post_status( $this->highest_db_post_id ) ) {
			$this->highest_db_post_id++;
		}

		$menu_item_id = ++$this->highest_db_post_id;

		// Add post meta to hold ID of associated menu item.
		update_post_meta( $page_id, '_amfp_menu_item_id', $menu_item_id );

		return $menu_item_id;

	}

	/**
	 * Get the ID of the auto menu object.
	 *
	 * @since     1.0.0
	 *
	 * @return    int    ID of the auto menu.
	 */
	public function get_auto_menu_id() {

		$auto_menu_name = $menu_name = $this->plugin->get( 'menu_name' );
		$auto_menu = get_term_by( 'name', $auto_menu_name, 'nav_menu' );

		// Exit if the menu doesn't exist (edge case after deactivation)
		if ( empty( $auto_menu->term_id ) ) {
			return false;
		}

		return $auto_menu->term_id;

	}

	/**
	 * Add necessary admin body classes to identify auto menu page.
	 *
	 * @since     1.0.0
	 *
	 * @param     array    $class    Body classes.
	 *
	 * @return    array              Updated body classes.
	 */
	public function admin_body_class( $class ) {

		// Don't do anything unless on the menu page.
		$current_screen = get_current_screen();
		if ( 'nav-menus' != $current_screen->base ) {
			return $class;
		}

		// Explode class into array for easier handling.
		$class = explode( ' ', $class );

		/**
		 * Get ID of current menu.
		 *
		 * Uses code from core WordPress nav menus functionality.
		 *
		 * @see  wp-admin/nav-menus.php
		 */
		$nav_menu_selected_id = isset( $_REQUEST['menu'] ) ? (int) $_REQUEST['menu'] : 0;

		// Get all nav menus.
		$nav_menus = wp_get_nav_menus();
		$menu_count = count( $nav_menus );

		// Are we on the add new screen?
		$add_new_screen = ( isset( $_GET['menu'] ) && 0 == $_GET['menu'] ) ? true : false;

		$recently_edited = absint( get_user_option( 'nav_menu_recently_edited' ) );
		if ( empty( $recently_edited ) && is_nav_menu( $nav_menu_selected_id ) ) {
			$recently_edited = $nav_menu_selected_id;
		}

		// Use $recently_edited if none are selected.
		if ( empty( $nav_menu_selected_id ) && ! isset( $_GET['menu'] ) && is_nav_menu( $recently_edited ) ) {
			$nav_menu_selected_id = $recently_edited;
		}

		// On deletion of menu, if another menu exists, show it.
		if ( ! $add_new_screen && 0 < $menu_count && isset( $_GET['action'] ) && 'delete' == $_GET['action'] ) {
			$nav_menu_selected_id = $nav_menus[0]->term_id;
		}

		// Add .auto-menu-active if user is viewing the auto menu in the admin.
		$auto_menu_id = $this->get_auto_menu_id();
		if ( $nav_menu_selected_id == $auto_menu_id ) {
			$class[] = 'auto-menu-active';
		}

		return implode( ' ', $class );
	}

	/**
	 * Add functionality to dismiss admin notice.
	 *
	 * @since    1.0.0
	 */
	public function dismiss_notice() {

		global $current_user;
		$user_id = $current_user->ID;

		// If user clicks to ignore the notice, add that to their user meta.
		if ( isset($_GET['amfp_notice_ignore'] ) && '0' == $_GET['amfp_notice_ignore'] ) {
			add_user_meta( $user_id, 'amfp_notice_ignore', true, true );
		}

	}

	/**
	 * Do admin notices.
	 *
	 * @since    1.0.0
	 */
	public function admin_notices() {

		global $current_user, $wp ;

		// Get current user ID.
		$user_id = $current_user->ID;

		// Get dismiss URL.
		$dismiss_url = add_query_arg( 'amfp_notice_ignore', 0, $_SERVER['REQUEST_URI'] );

		// Check that the user hasn't already clicked to ignore the message.
		if ( ! get_user_meta( $user_id, 'amfp_notice_ignore' ) ) {

			$notice_message = sprintf( __( '
					<a href="%s" target="_blank" class="logo-link"><svg version="1.1" id="Layer_1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px"
					viewBox="0 0 118.475 74.194" enable-background="new 0 0 118.475 74.194" xml:space="preserve">
					<g>
					<path fill="#231F20" d="M57.613,1.073v58.129H46.237V41.016c0-12.206,0.415-20.594,0.332-20.594h-0.083
					c-0.083,0-4.152,7.142-5.148,8.803L29.24,47.404L17.256,29.225c-1.08-1.662-5.148-8.803-5.232-8.803h-0.083
					c-0.083,0,0.415,8.388,0.415,20.594v18.186H0.981V1.073h10.878l12.04,19.93c4.484,7.39,5.232,9.217,5.315,9.217h0.084
					c0.083,0,0.83-1.827,5.315-9.217l12.124-19.93H57.613z"/>
					<path fill="#231F20" d="M65.885,24.735h4.435v5.956c1.544-2.197,3.129-3.802,4.757-4.815c2.242-1.352,4.599-2.029,7.073-2.029
					c1.67,0,3.255,0.328,4.757,0.983c1.501,0.656,2.728,1.526,3.679,2.614c0.952,1.087,1.776,2.646,2.474,4.673
					c1.475-2.703,3.318-4.757,5.531-6.162c2.212-1.405,4.593-2.107,7.142-2.107c2.381,0,4.482,0.603,6.305,1.807
					c1.822,1.203,3.176,2.883,4.061,5.037c0.885,2.154,1.328,5.385,1.328,9.693v18.817h-4.53V40.385c0-3.696-0.264-6.235-0.789-7.619
					c-0.526-1.383-1.426-2.496-2.699-3.342c-1.274-0.843-2.795-1.267-4.562-1.267c-2.147,0-4.11,0.633-5.889,1.902
					c-1.779,1.267-3.079,2.956-3.899,5.067c-0.821,2.113-1.231,5.639-1.231,10.581v13.495h-4.435V41.557
					c0-4.161-0.259-6.984-0.776-8.473c-0.518-1.489-1.421-2.683-2.709-3.58c-1.288-0.897-2.819-1.347-4.593-1.347
					c-2.049,0-3.966,0.618-5.75,1.853c-1.785,1.235-3.104,2.898-3.96,4.989c-0.855,2.091-1.283,5.291-1.283,9.6v14.604h-4.435V24.735z"
					/>
					</g>
					<path fill="#231F20" d="M89.071,10.419c2.882,1.341,6.538,1.77,10.305,0.968c4.144-0.88,7.562-3.066,9.613-5.81
					c-2.946-1.514-6.794-2.032-10.762-1.19c-3.872,0.823-7.112,2.786-9.193,5.28c-3.283-1.936-6.119-4.716-8.751-7.893
					c0.781,4.589-0.141,8.822-2.435,12.772C81.32,12.782,84.848,11.107,89.071,10.419z"/>
					<g>
					<path fill="#231F20" d="M1.236,67.127h0.845l1.956,4.633l2.036-4.633h0.148l2.05,4.633l1.994-4.633h0.854l-2.772,6.381H8.195
					l-2.041-4.569l-2.04,4.569H3.962L1.236,67.127z"/>
					<path fill="#231F20" d="M17.43,71.391l0.692,0.363c-0.227,0.446-0.489,0.806-0.787,1.079c-0.297,0.274-0.632,0.482-1.004,0.625
					c-0.372,0.143-0.792,0.214-1.262,0.214c-1.041,0-1.855-0.341-2.442-1.023c-0.587-0.683-0.881-1.453-0.881-2.313
					c0-0.81,0.249-1.531,0.746-2.164c0.63-0.806,1.474-1.209,2.53-1.209c1.088,0,1.957,0.413,2.606,1.238
					c0.462,0.582,0.696,1.31,0.705,2.182h-5.742c0.016,0.741,0.252,1.349,0.71,1.822c0.458,0.475,1.024,0.711,1.697,0.711
					c0.325,0,0.641-0.056,0.948-0.169c0.307-0.113,0.568-0.263,0.784-0.45C16.946,72.11,17.179,71.808,17.43,71.391z M17.43,69.672
					c-0.11-0.438-0.27-0.788-0.479-1.05s-0.486-0.473-0.831-0.634c-0.345-0.16-0.707-0.24-1.086-0.24c-0.626,0-1.165,0.201-1.615,0.604
					c-0.329,0.293-0.577,0.733-0.746,1.319H17.43z"/>
					<path fill="#231F20" d="M19.892,73.508v-8.845h0.821v3.561c0.343-0.423,0.729-0.738,1.154-0.947c0.425-0.209,0.892-0.314,1.4-0.314
					c0.902,0,1.672,0.328,2.311,0.982c0.639,0.655,0.958,1.45,0.958,2.385c0,0.923-0.322,1.711-0.967,2.363
					c-0.645,0.653-1.42,0.979-2.326,0.979c-0.52,0-0.99-0.111-1.412-0.334s-0.795-0.558-1.119-1.003v1.173H19.892z M23.177,72.88
					c0.456,0,0.877-0.112,1.264-0.337s0.693-0.54,0.922-0.947c0.228-0.406,0.342-0.837,0.342-1.29c0-0.454-0.115-0.886-0.345-1.297
					c-0.23-0.41-0.54-0.729-0.928-0.959c-0.388-0.229-0.803-0.343-1.243-0.343c-0.449,0-0.875,0.114-1.279,0.343
					c-0.404,0.229-0.714,0.537-0.93,0.924c-0.217,0.388-0.325,0.823-0.325,1.309c0,0.738,0.243,1.356,0.729,1.854
					C21.869,72.632,22.466,72.88,23.177,72.88z"/>
					<path fill="#231F20" d="M34.518,67.825l-0.528,0.546c-0.439-0.427-0.868-0.64-1.288-0.64c-0.267,0-0.495,0.088-0.685,0.264
					c-0.19,0.177-0.285,0.382-0.285,0.616c0,0.207,0.078,0.404,0.235,0.593c0.157,0.191,0.486,0.416,0.987,0.674
					c0.611,0.317,1.027,0.622,1.246,0.915c0.216,0.298,0.323,0.632,0.323,1.003c0,0.524-0.184,0.968-0.552,1.332
					c-0.368,0.363-0.828,0.545-1.38,0.545c-0.368,0-0.719-0.08-1.054-0.24s-0.612-0.381-0.831-0.663l0.516-0.586
					c0.419,0.473,0.864,0.709,1.334,0.709c0.329,0,0.609-0.105,0.84-0.316c0.231-0.211,0.347-0.459,0.347-0.745
					c0-0.234-0.077-0.443-0.229-0.627c-0.153-0.18-0.498-0.407-1.034-0.681c-0.576-0.297-0.968-0.591-1.176-0.88
					s-0.312-0.619-0.312-0.991c0-0.484,0.166-0.888,0.497-1.208s0.749-0.481,1.254-0.481C33.332,66.962,33.923,67.251,34.518,67.825z"
					/>
					<path fill="#231F20" d="M36.664,64.757h0.821v2.37h1.302v0.709h-1.302v5.672h-0.821v-5.672h-1.12v-0.709h1.12V64.757z"/>
					<path fill="#231F20" d="M39.89,67.127h0.821v2.979c0,0.727,0.039,1.228,0.118,1.501c0.118,0.392,0.342,0.7,0.672,0.927
					c0.331,0.227,0.726,0.34,1.184,0.34c0.458,0,0.847-0.11,1.166-0.331s0.54-0.511,0.661-0.871c0.082-0.246,0.123-0.769,0.123-1.565
					v-2.979h0.839v3.132c0,0.88-0.103,1.542-0.308,1.988c-0.205,0.445-0.514,0.795-0.927,1.047s-0.929,0.378-1.551,0.378
					c-0.622,0-1.141-0.126-1.557-0.378s-0.727-0.604-0.933-1.056c-0.205-0.452-0.308-1.131-0.308-2.038V67.127z"/>
					<path fill="#231F20" d="M53.643,64.664v8.845h-0.81v-1.097c-0.344,0.418-0.73,0.733-1.158,0.944s-0.896,0.316-1.405,0.316
					c-0.903,0-1.674-0.327-2.314-0.982c-0.639-0.654-0.959-1.451-0.959-2.39c0-0.919,0.323-1.705,0.968-2.358
					c0.646-0.652,1.421-0.979,2.329-0.979c0.524,0,0.998,0.112,1.422,0.335s0.796,0.557,1.117,1.003v-3.637H53.643z M50.362,67.754
					c-0.457,0-0.879,0.112-1.265,0.337c-0.387,0.225-0.694,0.54-0.923,0.946s-0.343,0.836-0.343,1.289c0,0.449,0.115,0.879,0.346,1.289
					s0.54,0.729,0.928,0.957c0.389,0.229,0.806,0.343,1.251,0.343c0.449,0,0.875-0.113,1.277-0.34c0.402-0.227,0.711-0.532,0.928-0.919
					s0.325-0.822,0.325-1.307c0-0.738-0.243-1.355-0.729-1.852C51.671,68.002,51.073,67.754,50.362,67.754z"/>
					<path fill="#231F20" d="M55.763,64.5c0.187,0,0.347,0.066,0.479,0.199s0.199,0.293,0.199,0.481c0,0.184-0.066,0.342-0.199,0.475
					s-0.292,0.199-0.479,0.199c-0.183,0-0.34-0.066-0.473-0.199s-0.199-0.291-0.199-0.475c0-0.188,0.066-0.349,0.199-0.481
					S55.58,64.5,55.763,64.5z M55.355,67.127h0.821v6.381h-0.821V67.127z"/>
					<path fill="#231F20" d="M60.88,66.962c0.983,0,1.798,0.356,2.444,1.067c0.587,0.649,0.881,1.418,0.881,2.306
					c0,0.892-0.31,1.671-0.931,2.337c-0.621,0.667-1.419,1-2.394,1c-0.979,0-1.779-0.333-2.4-1c-0.621-0.666-0.931-1.445-0.931-2.337
					c0-0.884,0.294-1.65,0.881-2.3C59.076,67.321,59.892,66.962,60.88,66.962z M60.877,67.766c-0.683,0-1.27,0.252-1.76,0.757
					c-0.491,0.504-0.736,1.114-0.736,1.83c0,0.461,0.112,0.892,0.335,1.29c0.224,0.399,0.526,0.707,0.906,0.924
					c0.381,0.217,0.799,0.325,1.254,0.325s0.873-0.108,1.253-0.325c0.381-0.217,0.683-0.524,0.907-0.924
					c0.224-0.398,0.335-0.829,0.335-1.29c0-0.716-0.246-1.326-0.739-1.83C62.141,68.018,61.556,67.766,60.877,67.766z"/>
					<path fill="#231F20" d="M75.665,70.318l0.584,0.619c-0.375,0.332-0.731,0.619-1.068,0.86c0.234,0.216,0.541,0.513,0.918,0.89
					c0.298,0.297,0.554,0.57,0.769,0.821h-1.203l-1.145-1.179c-0.719,0.578-1.303,0.956-1.752,1.132
					c-0.45,0.176-0.925,0.264-1.426,0.264c-0.664,0-1.198-0.19-1.601-0.572c-0.403-0.381-0.604-0.857-0.604-1.428
					c0-0.431,0.136-0.862,0.407-1.294c0.271-0.433,0.825-0.979,1.662-1.64c-0.473-0.571-0.776-0.992-0.911-1.264
					s-0.202-0.53-0.202-0.777c0-0.43,0.166-0.785,0.499-1.066c0.383-0.328,0.859-0.492,1.431-0.492c0.359,0,0.688,0.072,0.985,0.217
					s0.523,0.339,0.681,0.583c0.156,0.245,0.234,0.504,0.234,0.777c0,0.29-0.102,0.598-0.306,0.924
					c-0.203,0.326-0.604,0.744-1.204,1.252l1.309,1.373l0.856,0.869C75.039,70.848,75.4,70.559,75.665,70.318z M71.774,69.442
					c-0.732,0.552-1.213,0.991-1.44,1.317c-0.228,0.327-0.341,0.629-0.341,0.907c0,0.328,0.139,0.618,0.417,0.871
					c0.278,0.252,0.619,0.378,1.022,0.378c0.306,0,0.605-0.062,0.899-0.188c0.462-0.199,0.992-0.538,1.589-1.016l-1.36-1.402
					C72.326,70.064,72.063,69.775,71.774,69.442z M71.88,68.3c0.419-0.325,0.734-0.644,0.945-0.956
					c0.149-0.223,0.224-0.421,0.224-0.593c0-0.199-0.097-0.371-0.291-0.516s-0.452-0.218-0.773-0.218c-0.306,0-0.555,0.075-0.747,0.224
					s-0.288,0.324-0.288,0.527c0,0.156,0.039,0.304,0.117,0.44C71.259,67.542,71.53,67.905,71.88,68.3z"/>
					<path fill="#231F20" d="M84.578,67.825l-0.527,0.546c-0.439-0.427-0.869-0.64-1.288-0.64c-0.267,0-0.495,0.088-0.686,0.264
					c-0.189,0.177-0.285,0.382-0.285,0.616c0,0.207,0.079,0.404,0.235,0.593c0.157,0.191,0.486,0.416,0.987,0.674
					c0.611,0.317,1.026,0.622,1.246,0.915c0.216,0.298,0.323,0.632,0.323,1.003c0,0.524-0.184,0.968-0.552,1.332
					c-0.368,0.363-0.828,0.545-1.381,0.545c-0.368,0-0.72-0.08-1.055-0.24c-0.334-0.16-0.611-0.381-0.831-0.663l0.517-0.586
					c0.419,0.473,0.864,0.709,1.334,0.709c0.329,0,0.609-0.105,0.841-0.316s0.347-0.459,0.347-0.745c0-0.234-0.076-0.443-0.229-0.627
					c-0.153-0.18-0.498-0.407-1.035-0.681c-0.576-0.297-0.968-0.591-1.175-0.88c-0.208-0.289-0.312-0.619-0.312-0.991
					c0-0.484,0.165-0.888,0.496-1.208s0.749-0.481,1.254-0.481C83.391,66.962,83.983,67.251,84.578,67.825z"/>
					<path fill="#231F20" d="M92.203,68.453l-0.65,0.404c-0.562-0.746-1.33-1.12-2.303-1.12c-0.777,0-1.423,0.25-1.937,0.75
					s-0.771,1.107-0.771,1.822c0,0.465,0.118,0.902,0.354,1.312s0.561,0.728,0.973,0.954s0.874,0.34,1.386,0.34
					c0.938,0,1.703-0.373,2.297-1.12l0.65,0.429c-0.305,0.459-0.715,0.815-1.229,1.068c-0.515,0.253-1.1,0.38-1.757,0.38
					c-1.009,0-1.846-0.32-2.51-0.962c-0.665-0.641-0.997-1.421-0.997-2.34c0-0.618,0.155-1.191,0.466-1.722
					c0.311-0.529,0.738-0.943,1.281-1.24c0.544-0.297,1.152-0.446,1.824-0.446c0.423,0,0.83,0.064,1.223,0.194
					c0.394,0.129,0.727,0.297,1,0.504C91.779,67.868,92.012,68.132,92.203,68.453z"/>
					<path fill="#231F20" d="M93.757,64.664h0.821v3.607c0.332-0.438,0.697-0.766,1.097-0.982c0.398-0.218,0.831-0.326,1.296-0.326
					c0.478,0,0.9,0.122,1.27,0.364c0.37,0.242,0.643,0.567,0.818,0.977c0.176,0.408,0.265,1.049,0.265,1.921v3.284h-0.821v-3.044
					c0-0.735-0.029-1.226-0.088-1.473c-0.103-0.422-0.287-0.739-0.555-0.953c-0.268-0.213-0.619-0.319-1.053-0.319
					c-0.497,0-0.941,0.164-1.334,0.493c-0.394,0.328-0.652,0.734-0.777,1.22c-0.078,0.312-0.117,0.892-0.117,1.736v2.34h-0.821V64.664z
					"/>
					<path fill="#231F20" d="M104.079,66.962c0.983,0,1.798,0.356,2.444,1.067c0.587,0.649,0.881,1.418,0.881,2.306
					c0,0.892-0.311,1.671-0.931,2.337c-0.621,0.667-1.419,1-2.395,1c-0.979,0-1.779-0.333-2.4-1c-0.621-0.666-0.931-1.445-0.931-2.337
					c0-0.884,0.293-1.65,0.881-2.3C102.275,67.321,103.092,66.962,104.079,66.962z M104.076,67.766c-0.683,0-1.27,0.252-1.76,0.757
					c-0.49,0.504-0.736,1.114-0.736,1.83c0,0.461,0.112,0.892,0.336,1.29c0.224,0.399,0.525,0.707,0.906,0.924s0.799,0.325,1.254,0.325
					s0.873-0.108,1.254-0.325c0.38-0.217,0.683-0.524,0.906-0.924c0.224-0.398,0.335-0.829,0.335-1.29c0-0.716-0.246-1.326-0.738-1.83
					C105.341,68.018,104.755,67.766,104.076,67.766z"/>
					<path fill="#231F20" d="M111.92,66.962c0.983,0,1.798,0.356,2.444,1.067c0.587,0.649,0.881,1.418,0.881,2.306
					c0,0.892-0.311,1.671-0.931,2.337c-0.621,0.667-1.419,1-2.395,1c-0.979,0-1.779-0.333-2.4-1c-0.621-0.666-0.931-1.445-0.931-2.337
					c0-0.884,0.293-1.65,0.881-2.3C110.116,67.321,110.933,66.962,111.92,66.962z M111.917,67.766c-0.683,0-1.27,0.252-1.76,0.757
					c-0.49,0.504-0.736,1.114-0.736,1.83c0,0.461,0.112,0.892,0.336,1.29c0.224,0.399,0.525,0.707,0.906,0.924s0.799,0.325,1.254,0.325
					s0.873-0.108,1.254-0.325c0.38-0.217,0.683-0.524,0.906-0.924c0.224-0.398,0.335-0.829,0.335-1.29c0-0.716-0.246-1.326-0.738-1.83
					C113.182,68.018,112.596,67.766,111.917,67.766z"/>
					<path fill="#231F20" d="M116.617,64.664h0.821v8.845h-0.821V64.664z"/>
					</g>
					<g>
					<path fill="#231F20" d="M1.236,67.127h0.845l1.956,4.633l2.036-4.633h0.148l2.05,4.633l1.994-4.633h0.854l-2.772,6.381H8.195
					l-2.041-4.569l-2.04,4.569H3.962L1.236,67.127z"/>
					<path fill="#231F20" d="M17.43,71.391l0.692,0.363c-0.227,0.446-0.489,0.806-0.787,1.079c-0.297,0.274-0.632,0.482-1.004,0.625
					c-0.372,0.143-0.792,0.214-1.262,0.214c-1.041,0-1.855-0.341-2.442-1.023c-0.587-0.683-0.881-1.453-0.881-2.313
					c0-0.81,0.249-1.531,0.746-2.164c0.63-0.806,1.474-1.209,2.53-1.209c1.088,0,1.957,0.413,2.606,1.238
					c0.462,0.582,0.696,1.31,0.705,2.182h-5.742c0.016,0.741,0.252,1.349,0.71,1.822c0.458,0.475,1.024,0.711,1.697,0.711
					c0.325,0,0.641-0.056,0.948-0.169c0.307-0.113,0.568-0.263,0.784-0.45C16.946,72.11,17.179,71.808,17.43,71.391z M17.43,69.672
					c-0.11-0.438-0.27-0.788-0.479-1.05s-0.486-0.473-0.831-0.634c-0.345-0.16-0.707-0.24-1.086-0.24c-0.626,0-1.165,0.201-1.615,0.604
					c-0.329,0.293-0.577,0.733-0.746,1.319H17.43z"/>
					<path fill="#231F20" d="M19.892,73.508v-8.845h0.821v3.561c0.343-0.423,0.729-0.738,1.154-0.947c0.425-0.209,0.892-0.314,1.4-0.314
					c0.902,0,1.672,0.328,2.311,0.982c0.639,0.655,0.958,1.45,0.958,2.385c0,0.923-0.322,1.711-0.967,2.363
					c-0.645,0.653-1.42,0.979-2.326,0.979c-0.52,0-0.99-0.111-1.412-0.334s-0.795-0.558-1.119-1.003v1.173H19.892z M23.177,72.88
					c0.456,0,0.877-0.112,1.264-0.337s0.693-0.54,0.922-0.947c0.228-0.406,0.342-0.837,0.342-1.29c0-0.454-0.115-0.886-0.345-1.297
					c-0.23-0.41-0.54-0.729-0.928-0.959c-0.388-0.229-0.803-0.343-1.243-0.343c-0.449,0-0.875,0.114-1.279,0.343
					c-0.404,0.229-0.714,0.537-0.93,0.924c-0.217,0.388-0.325,0.823-0.325,1.309c0,0.738,0.243,1.356,0.729,1.854
					C21.869,72.632,22.466,72.88,23.177,72.88z"/>
					<path fill="#231F20" d="M34.518,67.825l-0.528,0.546c-0.439-0.427-0.868-0.64-1.288-0.64c-0.267,0-0.495,0.088-0.685,0.264
					c-0.19,0.177-0.285,0.382-0.285,0.616c0,0.207,0.078,0.404,0.235,0.593c0.157,0.191,0.486,0.416,0.987,0.674
					c0.611,0.317,1.027,0.622,1.246,0.915c0.216,0.298,0.323,0.632,0.323,1.003c0,0.524-0.184,0.968-0.552,1.332
					c-0.368,0.363-0.828,0.545-1.38,0.545c-0.368,0-0.719-0.08-1.054-0.24s-0.612-0.381-0.831-0.663l0.516-0.586
					c0.419,0.473,0.864,0.709,1.334,0.709c0.329,0,0.609-0.105,0.84-0.316c0.231-0.211,0.347-0.459,0.347-0.745
					c0-0.234-0.077-0.443-0.229-0.627c-0.153-0.18-0.498-0.407-1.034-0.681c-0.576-0.297-0.968-0.591-1.176-0.88
					s-0.312-0.619-0.312-0.991c0-0.484,0.166-0.888,0.497-1.208s0.749-0.481,1.254-0.481C33.332,66.962,33.923,67.251,34.518,67.825z"
					/>
					<path fill="#231F20" d="M36.664,64.757h0.821v2.37h1.302v0.709h-1.302v5.672h-0.821v-5.672h-1.12v-0.709h1.12V64.757z"/>
					<path fill="#231F20" d="M39.89,67.127h0.821v2.979c0,0.727,0.039,1.228,0.118,1.501c0.118,0.392,0.342,0.7,0.672,0.927
					c0.331,0.227,0.726,0.34,1.184,0.34c0.458,0,0.847-0.11,1.166-0.331s0.54-0.511,0.661-0.871c0.082-0.246,0.123-0.769,0.123-1.565
					v-2.979h0.839v3.132c0,0.88-0.103,1.542-0.308,1.988c-0.205,0.445-0.514,0.795-0.927,1.047s-0.929,0.378-1.551,0.378
					c-0.622,0-1.141-0.126-1.557-0.378s-0.727-0.604-0.933-1.056c-0.205-0.452-0.308-1.131-0.308-2.038V67.127z"/>
					<path fill="#231F20" d="M53.643,64.664v8.845h-0.81v-1.097c-0.344,0.418-0.73,0.733-1.158,0.944s-0.896,0.316-1.405,0.316
					c-0.903,0-1.674-0.327-2.314-0.982c-0.639-0.654-0.959-1.451-0.959-2.39c0-0.919,0.323-1.705,0.968-2.358
					c0.646-0.652,1.421-0.979,2.329-0.979c0.524,0,0.998,0.112,1.422,0.335s0.796,0.557,1.117,1.003v-3.637H53.643z M50.362,67.754
					c-0.457,0-0.879,0.112-1.265,0.337c-0.387,0.225-0.694,0.54-0.923,0.946s-0.343,0.836-0.343,1.289c0,0.449,0.115,0.879,0.346,1.289
					s0.54,0.729,0.928,0.957c0.389,0.229,0.806,0.343,1.251,0.343c0.449,0,0.875-0.113,1.277-0.34c0.402-0.227,0.711-0.532,0.928-0.919
					s0.325-0.822,0.325-1.307c0-0.738-0.243-1.355-0.729-1.852C51.671,68.002,51.073,67.754,50.362,67.754z"/>
					<path fill="#231F20" d="M55.763,64.5c0.187,0,0.347,0.066,0.479,0.199s0.199,0.293,0.199,0.481c0,0.184-0.066,0.342-0.199,0.475
					s-0.292,0.199-0.479,0.199c-0.183,0-0.34-0.066-0.473-0.199s-0.199-0.291-0.199-0.475c0-0.188,0.066-0.349,0.199-0.481
					S55.58,64.5,55.763,64.5z M55.355,67.127h0.821v6.381h-0.821V67.127z"/>
					<path fill="#231F20" d="M60.88,66.962c0.983,0,1.798,0.356,2.444,1.067c0.587,0.649,0.881,1.418,0.881,2.306
					c0,0.892-0.31,1.671-0.931,2.337c-0.621,0.667-1.419,1-2.394,1c-0.979,0-1.779-0.333-2.4-1c-0.621-0.666-0.931-1.445-0.931-2.337
					c0-0.884,0.294-1.65,0.881-2.3C59.076,67.321,59.892,66.962,60.88,66.962z M60.877,67.766c-0.683,0-1.27,0.252-1.76,0.757
					c-0.491,0.504-0.736,1.114-0.736,1.83c0,0.461,0.112,0.892,0.335,1.29c0.224,0.399,0.526,0.707,0.906,0.924
					c0.381,0.217,0.799,0.325,1.254,0.325s0.873-0.108,1.253-0.325c0.381-0.217,0.683-0.524,0.907-0.924
					c0.224-0.398,0.335-0.829,0.335-1.29c0-0.716-0.246-1.326-0.739-1.83C62.141,68.018,61.556,67.766,60.877,67.766z"/>
					<path fill="#231F20" d="M75.665,70.318l0.584,0.619c-0.375,0.332-0.731,0.619-1.068,0.86c0.234,0.216,0.541,0.513,0.918,0.89
					c0.298,0.297,0.554,0.57,0.769,0.821h-1.203l-1.145-1.179c-0.719,0.578-1.303,0.956-1.752,1.132
					c-0.45,0.176-0.925,0.264-1.426,0.264c-0.664,0-1.198-0.19-1.601-0.572c-0.403-0.381-0.604-0.857-0.604-1.428
					c0-0.431,0.136-0.862,0.407-1.294c0.271-0.433,0.825-0.979,1.662-1.64c-0.473-0.571-0.776-0.992-0.911-1.264
					s-0.202-0.53-0.202-0.777c0-0.43,0.166-0.785,0.499-1.066c0.383-0.328,0.859-0.492,1.431-0.492c0.359,0,0.688,0.072,0.985,0.217
					s0.523,0.339,0.681,0.583c0.156,0.245,0.234,0.504,0.234,0.777c0,0.29-0.102,0.598-0.306,0.924
					c-0.203,0.326-0.604,0.744-1.204,1.252l1.309,1.373l0.856,0.869C75.039,70.848,75.4,70.559,75.665,70.318z M71.774,69.442
					c-0.732,0.552-1.213,0.991-1.44,1.317c-0.228,0.327-0.341,0.629-0.341,0.907c0,0.328,0.139,0.618,0.417,0.871
					c0.278,0.252,0.619,0.378,1.022,0.378c0.306,0,0.605-0.062,0.899-0.188c0.462-0.199,0.992-0.538,1.589-1.016l-1.36-1.402
					C72.326,70.064,72.063,69.775,71.774,69.442z M71.88,68.3c0.419-0.325,0.734-0.644,0.945-0.956
					c0.149-0.223,0.224-0.421,0.224-0.593c0-0.199-0.097-0.371-0.291-0.516s-0.452-0.218-0.773-0.218c-0.306,0-0.555,0.075-0.747,0.224
					s-0.288,0.324-0.288,0.527c0,0.156,0.039,0.304,0.117,0.44C71.259,67.542,71.53,67.905,71.88,68.3z"/>
					<path fill="#231F20" d="M84.578,67.825l-0.527,0.546c-0.439-0.427-0.869-0.64-1.288-0.64c-0.267,0-0.495,0.088-0.686,0.264
					c-0.189,0.177-0.285,0.382-0.285,0.616c0,0.207,0.079,0.404,0.235,0.593c0.157,0.191,0.486,0.416,0.987,0.674
					c0.611,0.317,1.026,0.622,1.246,0.915c0.216,0.298,0.323,0.632,0.323,1.003c0,0.524-0.184,0.968-0.552,1.332
					c-0.368,0.363-0.828,0.545-1.381,0.545c-0.368,0-0.72-0.08-1.055-0.24c-0.334-0.16-0.611-0.381-0.831-0.663l0.517-0.586
					c0.419,0.473,0.864,0.709,1.334,0.709c0.329,0,0.609-0.105,0.841-0.316s0.347-0.459,0.347-0.745c0-0.234-0.076-0.443-0.229-0.627
					c-0.153-0.18-0.498-0.407-1.035-0.681c-0.576-0.297-0.968-0.591-1.175-0.88c-0.208-0.289-0.312-0.619-0.312-0.991
					c0-0.484,0.165-0.888,0.496-1.208s0.749-0.481,1.254-0.481C83.391,66.962,83.983,67.251,84.578,67.825z"/>
					<path fill="#231F20" d="M92.203,68.453l-0.65,0.404c-0.562-0.746-1.33-1.12-2.303-1.12c-0.777,0-1.423,0.25-1.937,0.75
					s-0.771,1.107-0.771,1.822c0,0.465,0.118,0.902,0.354,1.312s0.561,0.728,0.973,0.954s0.874,0.34,1.386,0.34
					c0.938,0,1.703-0.373,2.297-1.12l0.65,0.429c-0.305,0.459-0.715,0.815-1.229,1.068c-0.515,0.253-1.1,0.38-1.757,0.38
					c-1.009,0-1.846-0.32-2.51-0.962c-0.665-0.641-0.997-1.421-0.997-2.34c0-0.618,0.155-1.191,0.466-1.722
					c0.311-0.529,0.738-0.943,1.281-1.24c0.544-0.297,1.152-0.446,1.824-0.446c0.423,0,0.83,0.064,1.223,0.194
					c0.394,0.129,0.727,0.297,1,0.504C91.779,67.868,92.012,68.132,92.203,68.453z"/>
					<path fill="#231F20" d="M93.757,64.664h0.821v3.607c0.332-0.438,0.697-0.766,1.097-0.982c0.398-0.218,0.831-0.326,1.296-0.326
					c0.478,0,0.9,0.122,1.27,0.364c0.37,0.242,0.643,0.567,0.818,0.977c0.176,0.408,0.265,1.049,0.265,1.921v3.284h-0.821v-3.044
					c0-0.735-0.029-1.226-0.088-1.473c-0.103-0.422-0.287-0.739-0.555-0.953c-0.268-0.213-0.619-0.319-1.053-0.319
					c-0.497,0-0.941,0.164-1.334,0.493c-0.394,0.328-0.652,0.734-0.777,1.22c-0.078,0.312-0.117,0.892-0.117,1.736v2.34h-0.821V64.664z
					"/>
					<path fill="#231F20" d="M104.079,66.962c0.983,0,1.798,0.356,2.444,1.067c0.587,0.649,0.881,1.418,0.881,2.306
					c0,0.892-0.311,1.671-0.931,2.337c-0.621,0.667-1.419,1-2.395,1c-0.979,0-1.779-0.333-2.4-1c-0.621-0.666-0.931-1.445-0.931-2.337
					c0-0.884,0.293-1.65,0.881-2.3C102.275,67.321,103.092,66.962,104.079,66.962z M104.076,67.766c-0.683,0-1.27,0.252-1.76,0.757
					c-0.49,0.504-0.736,1.114-0.736,1.83c0,0.461,0.112,0.892,0.336,1.29c0.224,0.399,0.525,0.707,0.906,0.924s0.799,0.325,1.254,0.325
					s0.873-0.108,1.254-0.325c0.38-0.217,0.683-0.524,0.906-0.924c0.224-0.398,0.335-0.829,0.335-1.29c0-0.716-0.246-1.326-0.738-1.83
					C105.341,68.018,104.755,67.766,104.076,67.766z"/>
					<path fill="#231F20" d="M111.92,66.962c0.983,0,1.798,0.356,2.444,1.067c0.587,0.649,0.881,1.418,0.881,2.306
					c0,0.892-0.311,1.671-0.931,2.337c-0.621,0.667-1.419,1-2.395,1c-0.979,0-1.779-0.333-2.4-1c-0.621-0.666-0.931-1.445-0.931-2.337
					c0-0.884,0.293-1.65,0.881-2.3C110.116,67.321,110.933,66.962,111.92,66.962z M111.917,67.766c-0.683,0-1.27,0.252-1.76,0.757
					c-0.49,0.504-0.736,1.114-0.736,1.83c0,0.461,0.112,0.892,0.336,1.29c0.224,0.399,0.525,0.707,0.906,0.924s0.799,0.325,1.254,0.325
					s0.873-0.108,1.254-0.325c0.38-0.217,0.683-0.524,0.906-0.924c0.224-0.398,0.335-0.829,0.335-1.29c0-0.716-0.246-1.326-0.738-1.83
					C113.182,68.018,112.596,67.766,111.917,67.766z"/>
					<path fill="#231F20" d="M116.617,64.664h0.821v8.845h-0.821V64.664z"/>
					</svg></a>
					<h4>Thank you for activating Auto Menu From Pages!</h4>
					<ol>
						<li>Assign this menu to one of your theme\'s menu locations just like any other menu (via the <a href="%s">admin menu editor</a>).</li>
						<li>Exlude a page from the menu by checking the "Hide from the auto menu" checkbox when editing that page.</li>
						<li>Click the <span class="dashicons dashicons-update"></span> <b>Sync Auto Menu</b> link in the admin bar to sync the menu after making changes to your pages.</li>
					</ol>
					<p><a href="%s" target="_blank">MIGHTYminnow Plugins</a> | <a href="%s">Dismiss Notice</a></p>
					', 'auto-menu-from-pages' ),
				$this->Mm_url,
				'nav-menus.php?menu=' . $this->get_auto_menu_id(),
				$this->Mm_url,
				$dismiss_url
			);

			echo '<div class="updated mm-notice">' . $notice_message . '</div>';
		}

	}

	/**
	 * Generate sync link in the admin bar.
	 *
	 * @since    1.1.0
	 *
	 * @param    WP_Admin_Bar    $wp_admin_bar    Admin bar object.
	 */
	public function create_admin_bar_link( $wp_admin_bar ) {

		// Set up args for adding the admin menu item.
		$args = array(
			'id'    => 'sync_auto_menu',
			'title' => '<span class="ab-icon"></span> <span class="ab-label">' . __( 'Sync Auto Menu', 'auto-menu-from-pages' ) . '</span>',
			'href'  => add_query_arg( 'sync_auto_menu', 'sync', $_SERVER['REQUEST_URI'] ),
		);

		$wp_admin_bar->add_menu( $args );

	}

	/**
	 * Filter nav walker to return null list for auto menu in admin.
	 *
	 * @since    1.0.0
	 *
	 * @param    string    $walker     Walker name.
	 * @param    int       $menu_id    Current menu ID.
	 *
	 * @return    Walker|null 		   Current walker, or null if we're looking at the auto menu.
	 */
	public function filter_auto_menu_walker_to_hide( $walker, $menu_id ) {

		// Return default walker if we're not on the auto menu.
		if ( $this->get_auto_menu_id() != $menu_id ) {
			return $walker;
		}

		// Otherwise return null, which will result in no output in the admin menu editor.
		return null;

	}

	/**
	 * Add custom metabox.
	 *
	 * @since    1.0.0
	 */
	public function add_metabox() {

		add_meta_box(
			$this->plugin_slug,
			$this->plugin_name,
			array( $this, 'metabox_callback' ),
			'page',
			'side',
			'low'
		);

	}

	/**
	 * Output custom metabox.
	 *
	 * @since    1.0.0
	 *
	 * @param    WP_Post    $post    Current post.
	 */
	public function metabox_callback( $post ) {

		// Add an nonce field so we can check for it later.
		wp_nonce_field( $this->plugin_slug, "{$this->plugin_slug}_nonce" );

		/*
		 * Use get_post_meta() to retrieve an existing value
		 * from the database and use the value for the form.
		 */
		$value = get_post_meta( $post->ID, '_amfp_exclude_from_menu', true );

		echo '<label for="amfp_exclude_from_menu">';
		echo '<input type="checkbox" id="amfp_exclude_from_menu" name="amfp_exclude_from_menu" value="1" ' . checked( $value, 1, false ) . ' /> ';
		_e( 'Hide from auto menu.', 'auto-menu-from-pages' );
		echo '</label> ';

	}

	/**
	 * Save per-page metabox data.
	 *
	 * @since    1.0.0
	 *
	 * @param    int    $post_id    ID of the current post.
	 */
	public function save_metabox( $post_id ) {

		/*
		 * We need to verify this came from our screen and with proper authorization,
		 * because the save_post action can be triggered at other times.
		 */

		// Check if our nonce is set.
		if ( ! isset( $_POST[ "{$this->plugin_slug}_nonce" ] ) ) {
			return;
		}

		// Verify that the nonce is valid.
		if ( ! wp_verify_nonce( $_POST[ "{$this->plugin_slug}_nonce" ], $this->plugin_slug ) ) {
			return;
		}

		// If this is an autosave, our form has not been submitted, so we don't want to do anything.
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return;
		}

		// Check the user's permissions.
		if ( isset( $_POST['post_type'] ) && 'page' == $_POST['post_type'] ) {

			if ( ! current_user_can( 'edit_page', $post_id ) ) {
				return;
			}

		} else {

			if ( ! current_user_can( 'edit_post', $post_id ) ) {
				return;
			}
		}

		/* OK, it's safe for us to save the data now. */

		// Sanitize user input.
		$meta_value = isset( $_POST['amfp_exclude_from_menu'] ) ? $_POST['amfp_exclude_from_menu'] : 0;

		// Update the meta field in the database.
		update_post_meta( $post_id, '_amfp_exclude_from_menu', $meta_value );

	}

}
