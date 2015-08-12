<?php
/**
* AJAX class
* 
* @package WP Cube
* @subpackage WP to Buffer Pro
* @author Tim Carr
* @version 3.0
*/
class WP_To_Buffer_Pro_Ajax {

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
    *
    * @param obj $plugin Plugin Data
    */
    function __construct() {

        // Actions
        add_action( 'wp_ajax_wp_to_buffer_pro_clear_log', array( $this, 'clear_log' ) );

    }

    /**
    * Clears the plugin log for the given Post ID
    *
    * @since 3.0
    */
    function clear_log() {

        // Run a security check first.
        check_ajax_referer( 'wp-to-buffer-pro-clear-log', 'nonce' );

        // Clear log
        WP_To_Buffer_Pro_Log::clear_log();

        // Done
        wp_die( 1 );

    }

}