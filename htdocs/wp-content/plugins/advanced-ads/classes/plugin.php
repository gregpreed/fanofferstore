<?php

/**
 * Wordpress integration and definitions:
 *
 * - posttypes
 * - taxonomy
 * - textdomain
 *
 * @since 1.5.0
 */
class Advanced_Ads_Plugin {

	/**
	 *
	 * @var Advanced_Ads_Plugin
	 */
	protected static $instance;

	/**
	 *
	 * @var Advanced_Ads_Model
	 */
	protected $model;

	/**
	 * plugin options
	 *
	 * @since   1.0.1
	 * @var     array (if loaded)
	 */
	protected $options;

	/**
	 * interal plugin options – set by the plugin
	 *
	 * @since   1.4.5
	 * @var     array (if loaded)
	 */
	protected $internal_options;

	private function __construct() {
		register_activation_hook( dirname( __FILE__ ), array( $this, 'activate' ) );
		register_deactivation_hook( dirname( __FILE__ ), array( $this, 'deactivate' ) );

		add_action( 'plugins_loaded', array( $this, 'wp_plugins_loaded' ) );
	}

	/**
	 *
	 * @return Advanced_Ads_Plugin
	 */
	public static function get_instance() {
		// If the single instance hasn't been set, set it now.
		if ( null === self::$instance ) {
			self::$instance = new self;
		}

		return self::$instance;
	}

	/**
	 *
	 * @param Advanced_Ads_Model $model
	 */
	public function set_model(Advanced_Ads_Model $model) {
		$this->model = $model;
	}

	public function wp_plugins_loaded() {
		// Load plugin text domain
		$this->load_plugin_textdomain();

		// activate plugin when new blog is added on multisites // -TODO this is admin-only
		add_action( 'wpmu_new_blog', array( $this, 'activate_new_site' ) );

		// Load public-facing style sheet and JavaScript.
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_styles' ) );
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts' ) );

		// add short codes
		add_shortcode( 'the_ad', array( $this, 'shortcode_display_ad' ) );
		add_shortcode( 'the_ad_group', array( $this, 'shortcode_display_ad_group' ) );
		add_shortcode( 'the_ad_placement', array( $this, 'shortcode_display_ad_placement' ) );

		// remove default ad group menu item // -TODO only for admin
		add_action( 'admin_menu', array( $this, 'remove_taxonomy_menu_item' ) );
		// load widgets
		add_action( 'widgets_init', array( $this, 'widget_init' ) );

		// update add-ons
		add_action( 'admin_init', array($this, 'add_on_updater'), 1 );

	}

	/**
	 * Register and enqueue public-facing style sheet.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {
		// wp_enqueue_style( $this->get_plugin_slug() . '-plugin-styles', plugins_url('assets/css/public.css', __FILE__), array(), ADVADS_VERSION);
	}

	/**
	 * Return the plugin slug.
	 *
	 * @since    1.0.0
	 * @return    Plugin slug variable.
	 */
	public function get_plugin_slug() {
	    return ADVADS_SLUG;
	}

	/**
	 * Register and enqueues public-facing JavaScript files.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {
		// wp_enqueue_script( $this->get_plugin_slug() . '-plugin-script', plugins_url('assets/js/public.js', __FILE__), array('jquery'), ADVADS_VERSION);
		$options = $this->options();
		$activated_js = apply_filters( 'advanced-ads-activate-advanced-js', isset( $options['advanced-js'] ) );
		if ( $activated_js ){
			wp_enqueue_script( $this->get_plugin_slug() . '-advanced-js', ADVADS_BASE_URL . 'public/assets/js/advanced.js', array( 'jquery' ), ADVADS_VERSION );
		}
	}

	public function widget_init() {
		register_widget( 'Advanced_Ads_Widget' );
	}

	/**
	 * Fired when a new site is activated with a WPMU environment.
	 *
	 * @since    1.0.0
	 * @param    int    $blog_id    ID of the new blog.
	 */
	public function activate_new_site($blog_id) {

		if ( 1 !== did_action( 'wpmu_new_blog' ) ) {
			return;
		}

		switch_to_blog( $blog_id );
		$this->single_activate();
		restore_current_blog();
	}

	/**
	 * Fired for each blog when the plugin is activated.
	 *
	 * @since    1.0.0
	 */
	protected function single_activate() {
		$this->post_types_rewrite_flush();
		// -TODO inform modules
	}

	/**
	 * Fired for each blog when the plugin is deactivated.
	 *
	 * @since    1.0.0
	 */
	protected function single_deactivate() {
		// -TODO inform modules
	}

	/**
	 * Load the plugin text domain for translation.
	 *
	 * @since    1.0.0
	 */
	public function load_plugin_textdomain() {
		// $locale = apply_filters('advanced-ads-plugin-locale', get_locale(), $domain);
		load_plugin_textdomain( ADVADS_SLUG, false, ADVADS_BASE_DIR . '/languages' );
	}

	/**
	 * Fired when the plugin is activated.
	 *
	 * @since    1.0.0
	 * @param    boolean    $network_wide    True if WPMU superadmin uses
	 *                                       "Network Activate" action, false if
	 *                                       WPMU is disabled or plugin is
	 *                                       activated on an individual blog.
	 */
	public function activate($network_wide) {

		if ( function_exists( 'is_multisite' ) && is_multisite() ) {

			if ( $network_wide ) {

				// Get all blog ids
				$blog_ids = $this->model->get_blog_ids();

				foreach ( $blog_ids as $blog_id ) {

					switch_to_blog( $blog_id );
					$this->single_activate();
				}

				restore_current_blog();
			} else {
				$this->single_activate();
			}
		} else {
			$this->single_activate();
		}
	}

	/**
	 * Fired when the plugin is deactivated.
	 *
	 * @since    1.0.0
	 * @param    boolean    $network_wide
	 *
	 * True if WPMU superadmin uses
	 * "Network Deactivate" action, false if
	 * WPMU is disabled or plugin is
	 * deactivated on an individual blog.
	 */
	public function deactivate($network_wide) {

		if ( function_exists( 'is_multisite' ) && is_multisite() ) {

			if ( $network_wide ) {

				// Get all blog ids
				$blog_ids = $this->model->get_blog_ids();

				foreach ( $blog_ids as $blog_id ) {

					switch_to_blog( $blog_id );
					$this->single_deactivate();
				}

				restore_current_blog();
			} else {
				$this->single_deactivate();
			}
		} else {
			$this->single_deactivate();
		}
	}

	/**
	 * flush rewrites on plugin activation so permalinks for them work from the beginning on
	 *
	 * @since 1.0.0
	 * @link http://codex.wordpress.org/Function_Reference/register_post_type#Flushing_Rewrite_on_Activation
	 */
	public function post_types_rewrite_flush(){
		// load custom post type
		Advanced_Ads::get_instance()->create_post_types();
		// flush rewrite rules
		flush_rewrite_rules();
	}

	/**
	 * remove WP tag edit page for the ad group taxonomy
	 *  needed, because we can’t remove it with `show_ui` without also removing the meta box
	 *
	 * @since 1.0.0
	 */
	public function remove_taxonomy_menu_item() {
		remove_submenu_page( 'edit.php?post_type=advanced_ads', 'edit-tags.php?taxonomy=advanced_ads_groups&amp;post_type=advanced_ads' );
	}

	/**
	 * shortcode to include ad in frontend
	 *
	 * @since 1.0.0
	 * @param arr $atts
	 */
	public function shortcode_display_ad($atts){
		$id = isset($atts['id']) ? (int) $atts['id'] : 0;

		// use the public available function here
		return get_ad( $id );
	}

	/**
	 * shortcode to include ad from an ad group in frontend
	 *
	 * @since 1.0.0
	 * @param arr $atts
	 */
	public function shortcode_display_ad_group($atts){
		$id = isset($atts['id']) ? (int) $atts['id'] : 0;

		// use the public available function here
		return get_ad_group( $id );
	}

	/**
	 * shortcode to display content of an ad placement in frontend
	 *
	 * @since 1.1.0
	 * @param arr $atts
	 */
	public function shortcode_display_ad_placement($atts){
		$id = isset($atts['id']) ? (string) $atts['id'] : '';

		// use the public available function here
		return get_ad_placement( $id );
	}

	/**
	 * return plugin options
	 * these are the options updated by the user
	 *
	 * @since 1.0.1
	 * @return array $options
	 * @todo parse default options
	 */
	public function options() {
		if ( ! isset( $this->options ) ) {
			$this->options = get_option( ADVADS_SLUG, array() );
		}

		return $this->options;
	}

	/**
	 * update plugin options (not for settings page, but if automatic options are needed)
	 *
	 * @since 1.5.1
	 * @param array $options new options
	 */
	public function update_options( array $options ) {
		// do not allow to clear options
		if ( $options === array() ) {
			return;
		}

		$this->options = $options;
		update_option( ADVADS_SLUG, $options );
	}

	/**
	 * return internal plugin options
	 * these are options set by the plugin
	 *
	 * @since 1.0.1
	 * @return array $options
	 * @todo parse default options
	 */
	public function internal_options() {
		if ( ! isset( $this->internal_options ) ) {
		    $defaults = array(
			'version' => ADVADS_VERSION,
			'installed' => time(), // when was this installed
		    );
		    $this->internal_options = get_option( ADVADS_SLUG . '-internal', array() );

		    // save defaults
		    if($this->internal_options === array()){
			$this->internal_options = $defaults;
			$this->update_internal_options($this->internal_options);
		    }

		    // for versions installed prior to 1.5.3 set installed date for now
		    if( ! isset( $this->internal_options['installed'] )){
			$this->internal_options['installed'] = time();
			$this->update_internal_options($this->internal_options);
		    }
		}

		return $this->internal_options;
	}

	/**
	 * update internal plugin options
	 *
	 * @since 1.5.1
	 * @param array $options new internal options
	 */
	public function update_internal_options( array $options ) {
		// do not allow to clear options
		if ( $options === array() ) {
			return;
		}

		$this->internal_options = $options;
		update_option( ADVADS_SLUG . '-internal', $options );
	}

	/*
         * add-on updater
	 *
	 * @since 1.5.7
         *
         */
        public function add_on_updater(){

	    /**
	     * list of registered add ons
	     * contains:
	     *	    name
	     *	    version
	     *	    path
	     *	    options_slug
	     *	    short option slug (=key)
	     */
	    $add_ons = apply_filters( 'advanced-ads-add-ons', array() );

	    if( $add_ons === array() ) {
		return;
	    }

	    foreach( $add_ons as $_add_on_key => $_add_on ){

		    // check if a license expired over time
		    $expiry_date = get_option($_add_on['options_slug'] . '-license-expires', false);
		    $now = time();
		    if( $expiry_date && strtotime( $expiry_date ) < $now ){
			    // remove license status
			    delete_option( $_add_on['options_slug'] . '-license-status' );
			    continue;
		    }

		    // check status
		    if( get_option($_add_on['options_slug'] . '-license-status', false) !== 'valid' ) {
			    continue;
		    }

		    // retrieve our license key from the DB
		    $licenses = get_option(ADVADS_SLUG . '-licenses', array());
		    $license_key = isset($licenses[$_add_on_key]) ? $licenses[$_add_on_key] : '';

		    // setup the updater
		    if( $license_key ){
			    new EDD_SL_Plugin_Updater( ADVADS_URL, $_add_on['path'], array(
				    'version' 	=> $_add_on['version'],
				    'license' 	=> $license_key,
				    'item_name' => $_add_on['name'],
				    'author' 	=> 'Thomas Maier'
				)
			    );
		    }
	    }
        }

	/**
	 * check if license keys are missing or invalid
	 *
	 * @since 1.6.6
	 * @return true if there are missing licenses
	 */
	static function check_licenses_invalid(){

	    $add_ons = apply_filters( 'advanced-ads-add-ons', array() );

	    if( $add_ons === array() ) {
		    return false;
	    }

	    foreach( $add_ons as $_add_on_key => $_add_on ){
		    $status = get_option($_add_on['options_slug'] . '-license-status', false);

		    // don’t check if license is valid
		    if( $status === 'valid' ) {
			    continue;
		    }

		    // retrieve our license key from the DB
		    $licenses = get_option(ADVADS_SLUG . '-licenses', array());

		    $license_key = isset($licenses[$_add_on_key]) ? $licenses[$_add_on_key] : false;

		    if( ! $license_key || $status !== 'valid' ){
			    return true;
		    }
	    }

	    return false;
	}

	/**
	 * check if license keys are going to expire within next 14 days
	 *
	 * @since 1.6.6
	 * @return true if there are expiring licenses
	 */
	static function check_licenses_expire(){

	    $add_ons = apply_filters( 'advanced-ads-add-ons', array() );

	    if( $add_ons === array() ) {
		    return false;
	    }

	    $now = time();

	    foreach( $add_ons as $_add_on_key => $_add_on ){
		    // don’t display error for invalid licenses
		    if( get_option($_add_on['options_slug'] . '-license-status', false) === 'invalid' ) {
			    continue;
		    }

		    $expiry_date = get_option($_add_on['options_slug'] . '-license-expires', false);

		    if( $expiry_date ){
			    $expiry_date_t = strtotime( $expiry_date );
			    $in_two_weeks = time() + ( WEEK_IN_SECONDS * 2) ;
			    // check if expiry date is within next comming 2 weeks
			    if( $expiry_date_t < $in_two_weeks && $expiry_date_t >= $now ){
				    return true;
			    }

		    }
	    }

	    return false;
	}

	/**
	 * check if license keys are already expired
	 *
	 * @since 1.6.6
	 * @return true if there are expired licenses
	 */
	static function check_licenses_expired(){

	    $add_ons = apply_filters( 'advanced-ads-add-ons', array() );

	    if( $add_ons === array() ) {
		    return false;
	    }

	    $now = time();

	    foreach( $add_ons as $_add_on_key => $_add_on ){
		    // don’t display error for invalid licenses
		    if( get_option($_add_on['options_slug'] . '-license-status', false) === 'invalid' ) {
			    continue;
		    }

		    $expiry_date = get_option($_add_on['options_slug'] . '-license-expires', false);

		    if( $expiry_date && strtotime( $expiry_date ) < $now ){
			    return true;
		    }
	    }

	    return false;
	}
}
