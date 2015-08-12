<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * @link       http://www.lifeisfood.it/
 * @since      1.0.0
 *
 * @package    Fs_Social_Comments
 * @subpackage Fs_Social_Comments/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Fs_Social_Comments
 * @subpackage Fs_Social_Comments/admin
 * @author     Fabio Sirchia <fabio.sirchia@gmail.com>
 */
use Facebook\FacebookSession;
use Facebook\FacebookJavaScriptLoginHelper;
use Facebook\FacebookRequest;
class Fs_Social_Comments_Admin {

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string    $plugin_name       The name of this plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version = $version;

	}

	/**
	 * Register the stylesheets for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Fs_Social_Comments_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Fs_Social_Comments_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/fs-social-comments-admin.css', array(), $this->version, 'all' );

	}

	/**
	 * Register the JavaScript for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Fs_Social_Comments_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Fs_Social_Comments_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/fs-social-comments-admin.js', array( 'jquery' ), $this->version, false );

	}
	
	public function language_init($mofile){
		if(!file_exists($mofile)){
			return WP_PLUGIN_DIR.'/'. $this->plugin_name . '/languages/'.$this->plugin_name.'-en.mo';
		}
		return  $mofile;
	}
	
	public function fs_register_settings() { // whitelist options
		register_setting( 'fs-comments-opt-group', 'fs_social_comments_hide_default' );
		register_setting( 'fs-comments-opt-group', 'fs_social_comments_enable_facebook' );
		register_setting( 'fs-comments-opt-group', 'fs_social_comments_facebook_app_id' );
		register_setting( 'fs-comments-opt-group', 'fs_social_comments_facebook_app_secret' );
		register_setting( 'fs-comments-opt-group', 'fs_social_comments_lang_code', array( $this, 'default_language_code' ) );
		register_setting( 'fs-comments-opt-group', 'fs_social_comments_facebook_label', array( $this, 'default_facebook_label' ) );
		register_setting( 'fs-comments-opt-group', 'fs_social_comments_wordpress_label', array( $this, 'default_wordpress_label' ) );
	}
	
	public function default_facebook_label($theLabel){
		$theLabel = trim($theLabel);
		if($theLabel == ""){
			$theLabel = "Facebook";
		}
		return $theLabel;
	}
	
	public function default_wordpress_label($theLabel){
		$theLabel = trim($theLabel);
		if($theLabel == ""){
			$theLabel = "Wordpress";
		}
		return $theLabel;
	}
	
	public function default_language_code($theCode){
		$theCode = trim($theCode);
		if($theCode == ""){
			$theCode = "en_EN";
		}
		return $theCode;
	}
	
	
	public function fs_comments_settings(){
		add_comments_page( __("Settings"), __("Settings"), "administrator", 'fs-social-comments-options', array( $this, 'fs_social_comments_options' ));
	}
	
	public function fs_social_comments_options() {
		include plugin_dir_path( __FILE__ ) . 'partials/fs-social-comments-options.php';
	}
	
	public function fs_add_facebook_comment_type($commentTypeArray){
		$commentTypeArray["facebook"] = "Facebook";
		return $commentTypeArray; 
	}
	
	public function fs_delete_comment($comment_id){
		FacebookSession::setDefaultApplication(get_option("fs_social_comments_facebook_app_id"), get_option("fs_social_comments_facebook_app_secret"));
		global $wpdb;
		$fb_id = $wpdb->get_var( $wpdb->prepare(
				"
				SELECT id_fb
				FROM fs_social_comments_relation
				WHERE id_wp = %s
				",
				$comment_id
		) );
		$helper = new FacebookJavaScriptLoginHelper();
		$session = $helper->getSession();
		if ($session) {
			try{
			$request = new FacebookRequest(
			  $session,
			  'POST',
			  '/'.$fb_id,
			  array (
				'is_hidden' => 'true',
				)
			);
			$response = $request->execute();
			$graphObject = $response->getGraphObject();
			var_dump($graphObject);
// 			exit;
		} catch(FacebookRequestException $ex) {
			// When Facebook returns an error
			var_dump($ex);
// 			exit;
		} catch(\Exception $ex) {
			var_dump($ex);
// 			exit;
			// When validation fails or other local issues
		}
	}else{
		die("NON HO LA SESSIONE");
	}
	}
}
