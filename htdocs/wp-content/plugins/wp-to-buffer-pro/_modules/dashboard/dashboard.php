<?php
/**
* Dashboard Widget
* 
* @package WP Cube
* @subpackage Dashboard
* @author Tim Carr
* @version 1.0
* @copyright WP Cube
*/
class WPCubeDashboardWidget {     

	/**
	* Constructor
    *
    * @since 1.0
	*
	* @param object $plugin Plugin Object (name, displayName, version, folder, url)
	*/
	function __construct( $plugin ) {

		// Plugin Details
        $this->dashboard = $plugin;
        $this->dashboard_url = plugin_dir_url( __FILE__ );

		// Hooks
		add_action( 'admin_enqueue_scripts', array( &$this, 'admin_scripts_css' ) );
		add_action( 'wp_dashboard_setup', array( &$this, 'dashboard_widget' ) );
		add_action( 'wp_network_dashboard_setup', array( &$this, 'dashboard_widget' ) );	

	}     
	
	/**
    * Register and enqueue shared Admin UI CSS for WP Cube Plugins
    *
    * @since 1.0
    */
    function admin_scripts_css() {    

    	// JS
    	// This will only enqueue once, despite this hook being called by up to several plugins,
    	// as we have set a single, distinct name
    	wp_enqueue_script( 'wpcube-admin', $this->dashboard_url . 'js/min/admin-min.js', array( 'jquery' ), $this->dashboard->version, true );
    	   
    	// CSS
    	// This will only enqueue once, despite this hook being called by up to several plugins,
    	// as we have set a single, distinct name
        wp_enqueue_style( 'wpcube-admin', $this->dashboard_url . 'css/admin.css'); 

    }	
    
	/**
    * Adds a dashboard widget to list WP Cube Products + News
    *
    * Checks if another WP Cube plugin has already created this widget - if so, doesn't duplicate it
    *
    * @since 1.0
    */
    function dashboard_widget() {

    	global $wp_meta_boxes;

        // Check if another plugin has already registered this widget
    	if ( isset( $wp_meta_boxes['dashboard']['normal']['core']['wp_cube'] ) ) {
            return;
        }

        // Register widget 
    	wp_add_dashboard_widget( 'wp_cube', 'WP Cube', array( &$this, 'output_dashboard_widget' ) );

    }
    
    /**
    * Called by dashboardWidget(), includes dashboard.php to output the Dashboard Widget
    */
    function output_dashboard_widget() {

    	$result = wp_remote_get( 'https://www.wpcube.co.uk/feed/products' );
    	if ( ! is_wp_error( $result )) {
	    	if ( $result['response']['code'] == 200 ) {
	    		$xml = simplexml_load_string( $result['body'] );
	    		$products = $xml->channel;
	    	}
	    	
	    	include_once( WP_PLUGIN_DIR . '/' . $this->dashboard->name . '/_modules/dashboard/views/dashboard.php' );
    	} else {
    		include_once( WP_PLUGIN_DIR . '/' . $this->dashboard->name . '/_modules/dashboard/views/dashboard-nodata.php' );
    	}

    }
}
?>