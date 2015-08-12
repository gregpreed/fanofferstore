<?php
/**
* Administration class
* 
* @package WP Cube
* @subpackage WP to Buffer Pro
* @author Tim Carr
* @version 3.0
*/
class WP_To_Buffer_Pro_Admin {

    /**
    * Plugin
    *
    * @since 3.0
    */
    public $plugin;

    /**
    * Constructor
    *
    * @since 3.0
    */
    function __construct() {

        // Actions
        add_action( 'plugins_loaded',  array( &$this, 'plugin_loaded' ) );
        add_action( 'admin_enqueue_scripts', array( &$this, 'admin_scripts_css' ) );
        add_action( 'admin_menu', array( &$this, 'admin_menu' ) );
        add_action( 'plugins_loaded', array( &$this, 'load_language_files' ) );

        // Import + Export
        add_action( 'wp-to-buffer-pro-import', array( &$this, 'import' ) );
        add_filter( 'wp-to-buffer-pro-export', array( &$this, 'export' ) );

    }

    /**
     * Gets the main plugin information once it's loaded,
     * making it accessible to this class.
     *
     * @since 3.0
     */
    function plugin_loaded() {

        global $wpToBufferPro;
        $this->plugin = $wpToBufferPro->plugin;

    }

    /**
    * Register and enqueue any JS and CSS for the WordPress Administration
    *
    * @since 1.0.0
    */
    function admin_scripts_css() {

        // CSS and JS to always load
        wp_enqueue_style( $this->plugin->name, $this->plugin->url . 'assets/css/admin.css', array(), $this->plugin->version );
        wp_enqueue_script( $this->plugin->name . '-quick-edit', $this->plugin->url . 'assets/js/quick-edit.js', array( 'jquery' ), $this->plugin->version, true );

        // Check we are on either the Plugin Settings or Post Edit screens
        $screen = get_current_screen();
        if ( ! isset( $screen->base ) ) {
            return;
        }
        if ( strpos ( $screen->base, $this->plugin->name ) === false && $screen->base != 'post' ) {
            return;
        }

        // JS
        wp_enqueue_script( 'jquery-ui-sortable' );
        wp_enqueue_script( $this->plugin->name . '-tooltipster', $this->plugin->url . 'assets/js/jquery.tooltipster.js', array( 'jquery' ), $this->plugin->version, true );
        wp_enqueue_script( $this->plugin->name . '-admin', $this->plugin->url . 'assets/js/admin.js', array( 'jquery' ), $this->plugin->version, true );
        wp_localize_script($this->plugin->name . '-admin', 'wp_to_buffer_pro', array(
            'ajax'                      => admin_url( 'admin-ajax.php' ),
            'clear_log_message'         => __( 'Are you sure you want to clear the log file associated with this Post?', $this->plugin->name ),
            'clear_log_nonce'           => wp_create_nonce( 'wp-to-buffer-pro-clear-log' ),
            'clear_log_completed'       => __( 'No status updates have been sent to Buffer.', $this->plugin->name ),
            'delete_condition_message'  => __( 'Are you sure you want to delete this condition?', $this->plugin->name ), 
            'delete_status_message'     => __( 'Are you sure you want to delete this status?', $this->plugin->name ), 
            'screen_options_nonce'      => wp_create_nonce( 'wp-to-buffer-pro-screen-options' ),
        ));
        
    }
    
    /**
    * Add the Plugin to the WordPress Administration Menu
    *
    * @since 1.0.0
    */
    function admin_menu() {

        // Licensing
        add_menu_page( $this->plugin->displayName, $this->plugin->displayName, 'manage_options', $this->plugin->name, array( &$this, 'admin_screen' ), $this->plugin->url . 'assets/images/icons/buffer-dark.svg' );
        add_submenu_page( $this->plugin->name, __( 'Licensing', $this->plugin->name ), __( 'Licensing', $this->plugin->name ), 'manage_options', $this->plugin->name, array( &$this, 'admin_screen' ) );
        
        // Other Menu Items, if licensed
        if ( get_site_transient( $this->plugin->name . '_valid' ) == '1') {
            // Menu Items
            foreach ( $this->plugin->subPanels as $key => $subPanel ) {
                $url = str_replace( ' ', '-', strtolower( $subPanel ) );
                $url = str_replace( '+', '', strtolower( $url ) );
                
                add_submenu_page( $this->plugin->name, $subPanel, $subPanel, 'manage_options', $this->plugin->name . '-' . $url, array( &$this, 'admin_screen' ) );
            }
        }
         
    }

    /**
    * Output the Administration Panel
    * Save POSTed data from the Administration Panel into a WordPress option
    *
    * @since 3.0
    */
    function admin_screen() {

        // Check command to determine what to output
        $page = strtolower( str_replace( $this->plugin->name . '-', '', $_GET['page'] ) );
        switch ( $page ) {
            /**
            * Settings
            */
            case 'settings':
                // Notices
                $notices = array(
                    'success'   => array(),
                    'error'     => array(),
                );

                // Maybe disconnect from Buffer
                $result = $this->disconnect();
                if ( is_string( $result ) ) {
                    // Error - add to array of errors for output
                    $notices['error'][] = $result; 
                } elseif ( $result === true ) {
                    // Success
                    $notices['success'][] = __( 'Buffer account disconnected successfully.', $this->plugin->name ); 
                }

                // Maybe save settings
                $result = $this->save_settings();
                if ( is_string( $result ) ) {
                    // Error - add to array of errors for output
                    $notices['error'][] = $result;
                } elseif ( $result === true ) {
                    // Success
                    $notices['success'][] = __( 'Settings saved successfully.', $this->plugin->name ); 
                }
                
                // Get Buffer Profiles
                WP_To_Buffer_Pro_Buffer_API::set_access_token( WP_To_Buffer_Pro_Settings::get_access_token() );
                $profiles       = WP_To_Buffer_Pro_Buffer_API::profiles( true );

                // Get Post Types, Schedule Options and Post Actions
                $post_types     = WP_To_Buffer_Pro_Common::get_post_types_with_taxonomies();
                $schedule       = WP_To_Buffer_Pro_Common::get_schedule_options();
                $post_actions   = WP_To_Buffer_Pro_Common::get_post_actions();
                
                // Get URL parameters
                $tab            = ( isset( $_GET['tab'] ) ? $_GET['tab'] : 'auth' );
                $post_type      = ( isset( $_GET['type'] ) ? $_GET['type'] : '' );
                if ( ! empty( $post_type ) ) {
                    $tags       = WP_To_Buffer_Pro_Common::get_tags( $post_type );
                    $taxonomies = ( isset( $post_types[ $post_type ]->taxonomies ) ? $post_types[ $post_type ]->taxonomies : false );
                }

                // View
                $view = 'views/settings.php';
            
                break;

            default:
                // Licensing
                // Save routine is handled in licensing submodule
                
                $view = '_modules/licensing/views/licensing.php';
                
                break;
        }
    
        // Load Settings Form
        include_once( $this->plugin->folder . $view ); 

    }

    /**
     * Helper method to get the setting value from the plugin settings
     *
     * @since 3.0
     *
     * @param string    $type       Setting Type
     * @param string    $keys       Setting Key(s)
     * @param mixed     $default    Default Value if Setting does not exist
     * @return mixed                Value
     */
    function get_setting( $post_type = '', $key = '', $default = '' ) {

        // Depending on the key, return either the access token or the setting
        switch ( $key ) {
            /**
            * Access Token
            */
            case 'access_token':
                return WP_To_Buffer_Pro_Settings::get_access_token();
                break;

            /**
            * WP CRON
            * Log
            */
            case 'cron':
            case 'log':
                return WP_To_Buffer_Pro_Settings::get_option( $key );
                break;
            /**
            * Post Type Setting
            */
            default:
                return WP_To_Buffer_Pro_Settings::get_setting( $post_type, $key, $default );
                break;
        }

    }

    /**
    * Disconnect from Buffer by removing the access token
    *
    * @since 3.0
    *
    * @return string Result
    */
    function disconnect() {

        if ( ! isset( $_GET['disconnect'] ) ) {
            return false;
        }

        return WP_To_Buffer_Pro_Settings::update_access_token( '' );

    }

    /**
    * Helper method to save settings
    *
    * @since 3.0
    *
    * @return mixed Error String on error, true on success
    */
    function save_settings() {

        // Check if a POST request was made
        if ( ! isset( $_POST['submit'] ) ) {
            return false;
        }

        // Run security checks
        // Missing nonce 
        if ( ! isset( $_POST[ $this->plugin->name . '_nonce' ] ) ) { 
            return __( 'Nonce field is missing. Settings NOT saved.', $this->plugin->name );
        }

        // Invalid nonce
        if ( ! wp_verify_nonce( $_POST[$this->plugin->name.'_nonce'], $this->plugin->name ) ) {
            return __('Invalid nonce specified. Settings NOT saved.', $this->plugin->name );
        }

        // Get URL parameters
        $tab            = ( isset( $_GET['tab'] ) ? $_GET['tab'] : 'auth' );
        $post_type      = ( isset( $_GET['type'] ) ? $_GET['type'] : '' );
        
        switch ( $tab ) {
            /**
            * Authentication
            */
            case 'auth':
                // Check token exists
                if ( empty( $_POST['access_token'] ) ) {
                    return __( 'Please enter an access token to use this plugin. You can obtain one by following the instructions below.', $this->plugin->name );    
                }

                // Check token meets valid format
                if ( substr( $_POST['access_token'], 0, 2 ) != '1/' ) {
                    // Missing
                    return __( 'Oops - you\'ve not quite copied your access token from Buffer correctly. It should start with 1/. Please try again.', $this->plugin->name );
                } 
                if ( substr($_POST['access_token'], ( strlen( $_POST['access_token'] ) - 4 ), 4 ) == 'Edit') {
                    return __( 'Oops - you\'ve not quite copied your access token from Buffer correctly. It should not end with the word Edit. Please try again.', $this->plugin->name );
                }

                // Test Token
                WP_To_Buffer_Pro_Buffer_API::set_access_token( $_POST['access_token'] );
                $result = WP_To_Buffer_Pro_Buffer_API::user();
                if ( is_wp_error( $result ) ) {
                    return $result->getMessage();
                }
                
                // Save Token
                WP_To_Buffer_Pro_Settings::update_access_token( $_POST['access_token'] );

                // Save other Settings
                WP_To_Buffer_Pro_Settings::update_option( 'cron', ( isset( $_POST['cron'] ) ? 1 : 0 ) );
                WP_To_Buffer_Pro_Settings::update_option( 'log', ( isset( $_POST['log'] ) ? 1 : 0 ) );

                return true;

                break;

            /**
            * Post Type
            */
            default:
                // Save Settings for this Post Type
                return WP_To_Buffer_Pro_Settings::update_settings( $post_type, $_POST[ $this->plugin->name ] );

                break;
        }

    }

    /**
    * Import data when Licensing submodule import routine runs
    *
    * @since 3.0
    *
    * @param array $data Data
    */
    function import( $data ) {
        
        foreach ( $data as $key => $value ) {
            update_option( $key, $value );
        }
        
    }
    
    /**
    * Export data when Licensing submodule export routine runs
    *
    * @since 3.0
    *
    * @param array $data Data
    * @return array Data
    */
    function export( $data ) {
        
        // Get option data
        $data['wp-to-buffer-pro-access-token'] = get_option( 'wp-to-buffer-pro-access-token' );
        $data['wp-to-buffer-pro-cron'] = get_option( 'wp-to-buffer-pro-cron' );
        $data['wp-to-buffer-pro-log'] = get_option( 'wp-to-buffer-pro-log' );
        
        // Get Post Type option data
        $post_types     = WP_To_Buffer_Pro_Common::get_post_types_with_taxonomies();
        foreach ( $post_types as $type => $post_type_obj ) {
            $data[ 'wp-to-buffer-pro-' . $type ] = get_option( 'wp-to-buffer-pro-' . $type );
        }
        
        return $data;
        
    }

    /**
    * Loads plugin textdomain
    *
    * @since 3.0
    */
    function load_language_files() {

        load_plugin_textdomain( $this->plugin->name, false, $this->plugin->name . '/languages/' );

    } 

}