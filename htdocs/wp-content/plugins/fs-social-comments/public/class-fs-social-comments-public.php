<?php

/**
 * The public-facing functionality of the plugin.
 *
 * @link       http://www.lifeisfood.it/
 * @since      1.0.0
 *
 * @package    Fs_Social_Comments
 * @subpackage Fs_Social_Comments/public
 */

/**
 * The public-facing functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Fs_Social_Comments
 * @subpackage Fs_Social_Comments/public
 * @author     Fabio Sirchia <fabio.sirchia@gmail.com>
 */
use Facebook\FacebookSession;
use Facebook\FacebookJavaScriptLoginHelper;
use Facebook\FacebookRequest;
class Fs_Social_Comments_Public {

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
	 * @param      string    $plugin_name       The name of the plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version = $version;

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
		 * defined in Fs_Social_Comments_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Fs_Social_Comments_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */
		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/fs-social-comments-public.css', array(), $this->version, 'all' );

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
		 * defined in Fs_Social_Comments_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Fs_Social_Comments_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */
		
		wp_enqueue_script( "jquery", "https://ajax.googleapis.com/ajax/libs/jquery/1.11.2/jquery.min.js", array( '' ), $this->version, false );
// 		wp_enqueue_script( "bootstrap", "https://maxcdn.bootstrapcdn.com/bootstrap/3.3.4/js/bootstrap.min.js", array( 'jquery' ), $this->version, false );
		wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/tab.js', array( 'jquery' ), $this->version, true );
		wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/fs-social-comments-public.js', array( 'jquery' ), $this->version, true );

	}
	
	public function add_opengraph(){?>
		<meta property="fb:app_id" content="<?php echo get_option("fs_social_comments_facebook_app_id");?>"/>
	<?php }
	
	public function add_ajaxurl_cdata_to_front(){ ?>
	    <script type="text/javascript"> //<![CDATA[
	        ajaxurl = '<?php echo admin_url( 'admin-ajax.php'); ?>';
	    //]]> </script>
	<?php }
	
	public function add_access_token_cdata_to_front(){
		FacebookSession::setDefaultApplication(get_option("fs_social_comments_facebook_app_id"), get_option("fs_social_comments_facebook_app_secret"));
		$helper = $helper = new FacebookJavaScriptLoginHelper();
		$session = FacebookSession::newAppSession();
	?>
		    <script type="text/javascript"> //<![CDATA[
		        accessToken = '<?php echo $session->getAccessToken(); ?>';
		    //]]> </script>
		<?php }
	
	public function fs_remove_facebook_comments_from_default($comments){
		$cleanedComments = array();
		foreach ($comments as $thecomment){
			if($thecomment->comment_type != "Facebook"){
				$cleanedComments[] = $thecomment;		
			}
		}
		return $cleanedComments;
	}
	
	public function	fs_get_comments_number($count, $post_id){
		$comment_args = array(
		'order'   => 'ASC',
		'orderby' => 'comment_date_gmt',
		'status'  => 'approve',
		'post_id' => $post_id,
		);
		
		if ( $user_ID ) {
			$comment_args['include_unapproved'] = array( $user_ID );
		} elseif ( ! empty( $comment_author_email ) ) {
			$comment_args['include_unapproved'] = array( $comment_author_email );
		}
		
		$comments = get_comments( $comment_args );
		$cleanedComments = 0;
		foreach ($comments as $thecomment){
			if($thecomment->comment_type != "Facebook"){
				$cleanedComments++;
			}
		}
		return $cleanedComments;
	}
	
	public function fs_comments_template($theme_template) {
		$theme_template =  plugin_dir_path( __FILE__ ) . 'partials/fs-social-comments-public-display.php';
		return $theme_template;
	}
	
	public function fs_get_facebook_comment_info(){
		if ( isset($_REQUEST) ) {
			$comment_fb_id = $_REQUEST['comment_id'];
			FacebookSession::setDefaultApplication(get_option("fs_social_comments_facebook_app_id"), get_option("fs_social_comments_facebook_app_secret"));
			$session = FacebookSession::newAppSession();
			if ($session) {
				try{
					$request = new FacebookRequest(
							$session,
							'GET',
							'/'.$comment_fb_id
					);
					$response = $request->execute();
					$graphObject = $response->getGraphObject();
					header('Content-Type: application/json');
					print_r(json_encode($graphObject->asArray()));
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
		die();
	}
	
	public function fs_comment_insert(){
		if ( isset($_REQUEST) ) {
			global $wpdb;
			$name = $_REQUEST['name'];
			$comment_fb_id = $_REQUEST['comment_id'];
			$comment_text = $_REQUEST['comment_txt'];
			$comment_post_id = $_REQUEST['comment_post_id'];
			$time = current_time('mysql');
			$commentdata = array(
					'comment_post_ID' => $comment_post_id,
					'comment_author' => $name,
					'comment_content' => $comment_text,
					'comment_type' => 'Facebook',
					'comment_parent' => 0,
					'user_id' => 1,
					'comment_date' => $time,
					'comment_approved' => 1,
			);
			$commentId = wp_insert_comment($commentdata);
			$wpdb->insert(
					'fs_social_comments_relation',
					array(
							'id_fb' => $comment_fb_id,
							'id_wp' => $commentId
					),
					array(
							'%s',
							'%d'
					)
			);
		}
		die();
	}

}
