<?php
/**
* Plugin Name: WP to Buffer Pro
* Plugin URI: http://www.wpcube.co.uk/plugins/wp-to-buffer-pro
* Version: 3.0.3
* Author: WP Cube
* Author URI: http://www.wpcube.co.uk
* Description: Send WordPress Pages, Posts or Custom Post Types to your Buffer (bufferapp.com) account for scheduled publishing to social networks.
*/

/**
* WP to Buffer Pro Class
* 
* @package WP Cube
* @subpackage WP to Buffer Pro
* @author Tim Carr
* @version 3.0
* @copyright WP Cube
*/
class WPToBufferPro {

    /**
     * Plugin
     *
     * @since 3.0
     *
     * @var object
     */
    public $plugin = '';

    /**
    * Constructor. Acts as a bootstrap to load the rest of the plugin
    *
    * @since 1.0.0
    */
    function __construct() {

        // Plugin Details
        $this->plugin = new stdClass;
        $this->plugin->name         = 'wp-to-buffer-pro';
        $this->plugin->settingsName = 'wp-to-buffer'; // Settings key - ensures upgrade users don't lose settings
        $this->plugin->displayName  = 'WP to Buffer Pro';
        $this->plugin->version      = '3.0.3';
        $this->plugin->folder       = plugin_dir_path( __FILE__ );
        $this->plugin->url          = plugin_dir_url( __FILE__ );
        $this->plugin->documentationURL = 'http://www.wpcube.co.uk/documentation/wp-to-buffer-pro';
        $this->plugin->subPanels = array(
            __( 'Settings', $this->plugin->name ),
        );

        // Buffer
        $this->plugin->ignorePostTypes                = array( 'attachment','revision', 'nav_menu_item' );     
        $this->plugin->ignoreTaxonomies               = array( 'post_tag', 'nav_menu', 'link_category', 'post_format' );
		$this->plugin->publish_default_status         = __( 'New Post: {title} {url}', $this->plugin->name );
		$this->plugin->update_default_status          = __( 'Updated Post: {title} {url}', $this->plugin->name );
		
        // Dashboard Submodule
        if ( ! class_exists( 'WPCubeDashboardWidget' ) ) {
			require_once( $this->plugin->folder . '_modules/dashboard/dashboard.php' );
		}
		$dashboard = new WPCubeDashboardWidget( $this->plugin ); 
		
		// Licensing Submodule
		if ( ! class_exists( 'LicensingUpdateManager' ) ) {
			require_once( $this->plugin->folder . '_modules/licensing/lum.php' );
		}
		$this->licensing = new LicensingUpdateManager($this->plugin, 'http://www.wpcube.co.uk/wp-content/plugins/lum', $this->plugin->name);

		// Hooks and Filters
        // Load required files
        require_once( $this->plugin->folder . 'includes/admin/admin.php' );
        require_once( $this->plugin->folder . 'includes/admin/common.php' );
        require_once( $this->plugin->folder . 'includes/admin/settings.php' );

        // Init non-static classes
        new WP_To_Buffer_Pro_Admin();

        // Licensed
        if ( get_site_transient($this->plugin->name . '_valid' ) == '1' ) {
            // Load licensed files
            require_once( $this->plugin->folder . 'includes/admin/ajax.php' );
            require_once( $this->plugin->folder . 'includes/admin/buffer-api.php' );
            require_once( $this->plugin->folder . 'includes/admin/log.php' );
            require_once( $this->plugin->folder . 'includes/admin/post.php' );
            require_once( $this->plugin->folder . 'includes/admin/publish.php' );

            // Init non-static classes
            new WP_To_Buffer_Pro_Ajax();
            new WP_To_Buffer_Pro_Log();
            new WP_To_Buffer_Pro_Post();
            new WP_To_Buffer_Pro_Publish();

            // Run the migration routine from Free + Pro v2.x --> Pro v3.x
            if ( is_admin() ) {
                WP_To_Buffer_Pro_Settings::migrate_settings();
            }
        }
        
    }

}

// Initialise class
$wpToBufferPro = new WPToBufferPro();
