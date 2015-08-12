<?php

/**
 * The file that defines the core plugin class
 *
 * A class definition that includes attributes and functions used across both the
 * public-facing side of the site and the admin area.
 *
 * @link       http://www.lifeisfood.it/
 * @since      1.0.0
 *
 * @package    Fs_Social_Comments
 * @subpackage Fs_Social_Comments/includes
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
 * @package    Fs_Social_Comments
 * @subpackage Fs_Social_Comments/includes
 * @author     Fabio Sirchia <fabio.sirchia@gmail.com>
 */
class Fs_Social_Comments {

	/**
	 * The loader that's responsible for maintaining and registering all hooks that power
	 * the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      Fs_Social_Comments_Loader    $loader    Maintains and registers all hooks for the plugin.
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

		$this->plugin_name = 'fs-social-comments';
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
	 * - Fs_Social_Comments_Loader. Orchestrates the hooks of the plugin.
	 * - Fs_Social_Comments_i18n. Defines internationalization functionality.
	 * - Fs_Social_Comments_Admin. Defines all hooks for the admin area.
	 * - Fs_Social_Comments_Public. Defines all hooks for the public side of the site.
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
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-fs-social-comments-loader.php';

		/**
		 * The class responsible for defining internationalization functionality
		 * of the plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-fs-social-comments-i18n.php';

		/**
		 * The class responsible for defining all actions that occur in the admin area.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-fs-social-comments-admin.php';

		/**
		 * The class responsible for defining all actions that occur in the public-facing
		 * side of the site.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'public/class-fs-social-comments-public.php';

		$this->loader = new Fs_Social_Comments_Loader();

	}

	/**
	 * Define the locale for this plugin for internationalization.
	 *
	 * Uses the Fs_Social_Comments_i18n class in order to set the domain and to register the hook
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function set_locale() {

		$plugin_i18n = new Fs_Social_Comments_i18n();
		$plugin_i18n->set_domain( $this->get_plugin_name() );

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

		$plugin_admin = new Fs_Social_Comments_Admin( $this->get_plugin_name(), $this->get_version() );

		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_styles' );
		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_scripts' );
		$this->loader->add_action( 'load_textdomain_mofile', $plugin_admin, 'language_init' );
		/*Creazione url della pagina comments di wordpress*/
		$this->loader->add_action( 'admin_menu', $plugin_admin, 'fs_comments_settings' );
		/*registrazione delle options*/
		$this->loader->add_action( 'admin_menu', $plugin_admin, 'fs_register_settings' );
		
		/*registrazione delle options*/
		$this->loader->add_filter( 'admin_comment_types_dropdown', $plugin_admin, 'fs_add_facebook_comment_type' );
// 		$this->loader->add_action( 'delete_comment', $plugin_admin, 'fs_delete_comment' );

	}

	/**
	 * Register all of the hooks related to the public-facing functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_public_hooks() {

		$plugin_public = new Fs_Social_Comments_Public( $this->get_plugin_name(), $this->get_version() );

		$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_styles' );
		$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_scripts' );
		$this->loader->add_action( 'wp_head', $plugin_public, 'add_opengraph' );
		$this->loader->add_action( 'wp_head', $plugin_public, 'add_ajaxurl_cdata_to_front' );
// 		$this->loader->add_action( 'wp_head', $plugin_public, 'add_access_token_cdata_to_front' );
		/*Frontend ajax call*/
		$this->loader->add_action( 'wp_ajax_fs_social_comments_get_facebook_comment', $plugin_public, 'fs_get_facebook_comment_info' );
		$this->loader->add_action( 'wp_ajax_nopriv_fs_social_comments_get_facebook_comment', $plugin_public, 'fs_get_facebook_comment_info' );
		$this->loader->add_action( 'wp_ajax_fs_social_comments_register_comment', $plugin_public, 'fs_comment_insert' );
		$this->loader->add_action( 'wp_ajax_nopriv_fs_social_comments_register_comment', $plugin_public, 'fs_comment_insert' );
		
		
		$this->loader->add_filter("comments_array", $plugin_public, "fs_remove_facebook_comments_from_default");
		$this->loader->add_filter("get_comments_number", $plugin_public, "fs_get_comments_number",10,2);
		$this->loader->add_filter("comments_template", $plugin_public, "fs_comments_template");

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
	 * @return    Fs_Social_Comments_Loader    Orchestrates the hooks of the plugin.
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
