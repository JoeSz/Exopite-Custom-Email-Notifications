<?php

/**
 * The file that defines the core plugin class
 *
 * A class definition that includes attributes and functions used across both the
 * public-facing side of the site and the admin area.
 *
 * @link       http://joe.szalai.org
 * @since      1.0.0
 *
 * @package    Exopite_Custom_Email_Notifications
 * @subpackage Exopite_Custom_Email_Notifications/includes
 */

/**
 * The core plugin class.
 *
 * This is used to define internationalization, admin-specific hooks, and
 * public-facing site hooks.
 *
 * Also maintains the unique identifier of this plugin as well as the current
 * version of the plugin.
 *
 * @since      1.0.0
 * @package    Exopite_Custom_Email_Notifications
 * @subpackage Exopite_Custom_Email_Notifications/includes
 * @author     Joe Szalai <joe@szalai.org>
 */
class Exopite_Custom_Email_Notifications {

	/**
	 * The loader that's responsible for maintaining and registering all hooks that power
	 * the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      Exopite_Custom_Email_Notifications_Loader    $loader    Maintains and registers all hooks for the plugin.
	 */
	protected $loader;

	/**
	 * The unique identifier of this plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $plugin_name    The string used to uniquely identify this plugin.
	 */
	protected $plugin_name;

	/**
	 * The current version of the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $version    The current version of the plugin.
	 */
	protected $version;

	/**
	 * Define the core functionality of the plugin.
	 *
	 * Set the plugin name and the plugin version that can be used throughout the plugin.
	 * Load the dependencies, define the locale, and set the hooks for the admin area and
	 * the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function __construct() {

		$this->plugin_name = 'exopite-custom-email-notifications';
		$this->version = '1.0.0';

		$this->load_dependencies();
		$this->set_locale();
		$this->define_admin_hooks();
		$this->define_public_hooks();

	}

	/**
	 * Load the required dependencies for this plugin.
	 *
	 * Include the following files that make up the plugin:
	 *
	 * - Exopite_Custom_Email_Notifications_Loader. Orchestrates the hooks of the plugin.
	 * - Exopite_Custom_Email_Notifications_i18n. Defines internationalization functionality.
	 * - Exopite_Custom_Email_Notifications_Admin. Defines all hooks for the admin area.
	 * - Exopite_Custom_Email_Notifications_Public. Defines all hooks for the public side of the site.
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
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-exopite-custom-email-notifications-loader.php';

		/**
		 * The class responsible for defining internationalization functionality
		 * of the plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-exopite-custom-email-notifications-i18n.php';

		/**
		 * The class responsible for defining all actions that occur in the admin area.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-exopite-custom-email-notifications-admin.php';

		/**
		 * The class responsible for defining all actions that occur in the public-facing
		 * side of the site.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'public/class-exopite-custom-email-notifications-public.php';

        /**
         * The class responsible for managing meta boxes
         */
        require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-exopite-custom-email-notifications-meta-boxes.php';

		$this->loader = new Exopite_Custom_Email_Notifications_Loader();

	}

	/**
	 * Define the locale for this plugin for internationalization.
	 *
	 * Uses the Exopite_Custom_Email_Notifications_i18n class in order to set the domain and to register the hook
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function set_locale() {

		$plugin_i18n = new Exopite_Custom_Email_Notifications_i18n();

		$this->loader->add_action( 'plugins_loaded', $plugin_i18n, 'load_plugin_textdomain' );

	}

	/**
	 * Register all of the hooks related to the admin area functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_admin_hooks() {

		$plugin_admin = new Exopite_Custom_Email_Notifications_Admin( $this->get_plugin_name(), $this->get_version() );
        $plugin_meta_boxes = new Exopite_Custom_Email_Notifications_Meta_Boxes();

		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_styles' );
        $this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_scripts' );

        /**
         * Add metabox and register custom fields
         *
         * @link https://code.tutsplus.com/articles/rock-solid-wordpress-30-themes-using-custom-post-types--net-12093
         */
        $this->loader->add_action( 'admin_init', $plugin_meta_boxes, 'render_meta_options' );
        $this->loader->add_action( 'save_post', $plugin_meta_boxes, 'save_meta_options' );

        /*
         * Save Post hook
         *
         * @link https://codex.wordpress.org/Plugin_API/Action_Reference/save_post
         * @link https://wordpress.stackexchange.com/questions/134664/what-is-correct-way-to-hook-when-update-post/134667#134667
         */
		$this->loader->add_action( 'save_post', $plugin_admin, 'save_post_or_page', 10, 3 );

        /*
         * Nofity users when approve a comment.
         *
         * From Plugin:
         * Plugin Name: Post Notification by Email
         * Plugin URI: http://wordpress.org/plugins/notify-users-e-mail/
         */
        $this->loader->add_action( 'wp_insert_comment', $plugin_admin, 'pre_send_notification_new_comment', 10, 2 );
        $this->loader->add_action( 'transition_comment_status', $plugin_admin, 'pre_send_notification_update_comment', 10, 3 );

	}

	/**
	 * Register all of the hooks related to the public-facing functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_public_hooks() {

		$plugin_public = new Exopite_Custom_Email_Notifications_Public( $this->get_plugin_name(), $this->get_version() );

		$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_styles' );
		$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_scripts' );

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
	 * The name of the plugin used to uniquely identify it within the context of
	 * WordPress and to define internationalization functionality.
	 *
	 * @since     1.0.0
	 * @return    string    The name of the plugin.
	 */
	public function get_plugin_name() {
		return $this->plugin_name;
	}

	/**
	 * The reference to the class that orchestrates the hooks with the plugin.
	 *
	 * @since     1.0.0
	 * @return    Exopite_Custom_Email_Notifications_Loader    Orchestrates the hooks of the plugin.
	 */
	public function get_loader() {
		return $this->loader;
	}

	/**
	 * Retrieve the version number of the plugin.
	 *
	 * @since     1.0.0
	 * @return    string    The version number of the plugin.
	 */
	public function get_version() {
		return $this->version;
	}

}
