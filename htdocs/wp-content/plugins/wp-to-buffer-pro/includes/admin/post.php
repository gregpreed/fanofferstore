<?php
/**
* Post class
* 
* @package WP Cube
* @subpackage WP to Buffer Pro
* @author Tim Carr
* @version 3.0
*/
class WP_To_Buffer_Pro_Post {

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

        // Admin Notices
        add_action( 'plugins_loaded',  array( &$this, 'plugin_loaded' ) );
        add_action( 'admin_notices', array( &$this, 'admin_notices' ) );

        // Quick Edit and Bulk Edit
        //add_filter( 'manage_posts_columns', array( $this, 'wp_list_table_columns' ) );
        //add_action( 'manage_posts_custom_column', array( $this, 'wp_list_table_columns_output' ), 10, 2 );
        //add_action( 'quick_edit_custom_box', array( &$this, 'quick_edit_custom_box' ), 10, 2 ); // Single Item
        //add_action( 'bulk_edit_custom_box', array( $this, 'bulk_edit_custom_box' ), 10, 2 ); // Multiple Items
        //add_action( 'post_updated', array( $this, 'bulk_edit_save' ) );

        // Post Metabox
        add_action( 'admin_menu', array( &$this, 'admin_meta_boxes' ) );
        add_action( 'save_post', array( &$this, 'save_post' ), 10, 1 );

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
    * Outputs a notice if the user is editing a Post, which has a meta key indicating
    * that status(es) were published to Buffer API
    *
    * - A Post has been sent to Buffer and we have a valid message response
    *
    * @since 3.0
    */
    function admin_notices() {

        // Check we can get the current screen the user is viewing
        $screen = get_current_screen();
        if ( ! $screen || ! isset( $screen->base ) || ! isset( $screen->parent_base ) ) {
            return;
        }

        // Check we are on a Post based screen (includes Pages + CPTs)
        if ( $screen->base != 'post' ) {
            return;
        }

        // Check we are editing a Post, Page or CPT
        if ( $screen->parent_base != 'edit' ) {
            return;
        }

        // Check we have a Post ID
        if ( ! isset( $_GET['post'] ) ) {
            return;
        }
        $post_id = absint( $_GET['post'] );

        // Check if this Post has a success or error meta key set by this plugin
        $success= get_post_meta( $post_id, '_wp_to_buffer_pro_success', true );
        $error  = get_post_meta( $post_id, '_wp_to_buffer_pro_error', true );

        // Check for success
        if ( $success ) {
            // Show notice and clear meta key, so we don't display this notice again
            delete_post_meta( $post_id, '_wp_to_buffer_pro_success' );
            ?>
            <div class="updated success">
                <p>
                    <?php _e( 'WP to Buffer Pro: Post successfully added to Buffer.', $this->plugin->name ); ?> 
                </p>
            </div>
            <?php
        }

        // Check for error
        if ( $error ) {
            // Show notice and clear meta key, so we don't display this notice again
            delete_post_meta( $post_id, '_wp_to_buffer_pro_error' );
            ?>
            <div class="error">
                <p>
                    <?php _e( 'WP to Buffer Pro: Error(s) occured when attempting to add some/all status updates to Buffer. Please check the log at the bottom of this screen for further details.', $this->plugin->name ); ?> 
                </p>
            </div>
            <?php
        }

    } 

    /**
     * Adds a WP to Buffer Pro column to the Pages WP_List_Table
     *
     * Note: We don't use this column, as it's hidden by CSS - but it enables us to 
     * use the quick_edit_custom_box action, so we can let users Quick + Bulk Edit
     * this plugin's settings.
     *
     * @since 3.0.3
     *
     * @param array $columns WP_List_Table Columns
     * @return array WP_List_Table Columns
     */
    function wp_list_table_columns( $columns ) {
        
        $columns[ $this->plugin->name ] = __( $this->plugin->displayName, $this->plugin->name );
        return $columns;

    }

    /**
    * Outputs hidden field values for Post-specific settings.
    *
    * JS then reads these to populate Quick Edit field values.
    *
    * @since 3.0.3
    *
    * @param string $column_name    Column Name
    * @param int    $post_id        Post ID
    */
    function wp_list_table_columns_output( $column_name, $post_id ) {
        
        // Check the column we're on is the Custom Fields column
        if ( $this->plugin->name !== $column_name ) {
            return;
        }

        // Output settings as hidden fields. We'll use this when Quick Editing.
        echo '<input type="hidden" name="wp_to_buffer_pro_override_' . $post_id . '" value="' . $this->get_setting( $post_id, '[override]', 0 ) . '" />';

    }

    /**
     * Adds dropdown option for publishing to Buffer to the quick editing and bulk editing screens
     *
     * @since 3.0.3
     *
     * @param string $column_name   Column Name
     * @param string $post_type     Post Type
     * @return HTML
     */
    function quick_edit_custom_box( $column_name, $post_type ) {

        // Only apply to our plugin's column (in case other plugins register their own custom columns)
        if ( $this->plugin->name !== $column_name ) {
            return;
        }
        
        // Output dropdown for plugin settings
        // Form field values/selections are populated by JS, as we don't have a $post object available
        ?>
        <fieldset class="inline-edit-col <?php echo $this->plugin->name; ?>">
            <h4><?php echo $this->plugin->displayName; ?></h4>
            <div class="inline-edit-col inline-edit-custom-fields">
                <label class="inline-edit-group">
                    <select name="wp-to-buffer-pro[override]" size="1">
                        <option value="-1"><?php _e( 'Do NOT Post to Buffer', $this->plugin->name ); ?></option>
                        <option value="0"><?php _e( 'Use Plugin Settings', $this->plugin->name ); ?></option>
                        <option value="1"><?php _e( 'Post to Buffer using Manual Settings (Edit Post to see settings)', $this->plugin->name ); ?></option>
                    </select>
                    <?php 
                    // Include plugin nonce field
                    wp_nonce_field( $this->plugin->name, $this->plugin->name . '_nonce' ); 
                    ?>
                </label>
            </div>
        </fieldset>
        <?php
        
    }

    /**
    * Adds Metaboxes to Post Edit Screens
    *
    * @since 3.0
    */
    function admin_meta_boxes() {

        // Get Post Types
        $post_types = WP_To_Buffer_Pro_Common::get_post_types();

        // Add meta boxes for each
        foreach ( $post_types as $post_type => $post_type_obj ) {
            add_meta_box( $this->plugin->name, __( 'WP to Buffer', $this->plugin->name ), array( &$this, 'meta_settings' ), $post_type, 'normal', 'low' );
        }

    }

    /**
    * Outputs settings to allow the user to override default settings for publishing to Buffer
    *
    * @since 3.0
    *
    * @param WP_Post $post   Post
    */
    function meta_settings( $post ) {

        // Get override settings
        $override = $this->get_setting_by_post_id( $post->ID, '[override]', 0 );
        ?>
        <div class="option">
            <p>
                <select name="wp-to-buffer-pro[override]" size="1" data-conditional="<?php echo $this->plugin->name; ?>-override" data-conditional-value="1">
                    <option value="-1"<?php selected( $override, '-1' ); ?>><?php _e( 'Do NOT Post to Buffer', $this->plugin->name ); ?></option>
                    <option value="0"<?php selected( $override, '0' ); ?>><?php _e( 'Use Plugin Settings', $this->plugin->name ); ?></option>
                    <option value="1"<?php selected( $override, '1' ); ?>><?php _e( 'Post to Buffer using Manual Settings', $this->plugin->name ); ?></option>
                </select>
                <?php 
                // Include plugin nonce field
                wp_nonce_field( $this->plugin->name, $this->plugin->name . '_nonce' ); 
                ?>
            </p>
        </div>
        
        <?php
        // Get Buffer Profiles
        WP_To_Buffer_Pro_Buffer_API::set_access_token( WP_To_Buffer_Pro_Settings::get_access_token() );
        $profiles       = WP_To_Buffer_Pro_Buffer_API::profiles();
      
        // Get Post Types, Schedule Options and Post Actions
        $post_types     = WP_To_Buffer_Pro_Common::get_post_types_with_taxonomies();
        $schedule       = WP_To_Buffer_Pro_Common::get_schedule_options();
        $post_actions   = WP_To_Buffer_Pro_Common::get_post_actions();
        
        // Get URL parameters
        $tab            = 'post';
        $post_type      = $post->post_type;
        if ( ! empty( $post_type ) ) {
            $tags           = WP_To_Buffer_Pro_Common::get_tags( $post_type );
        }
        ?>
        <div id="<?php echo $this->plugin->name; ?>-override">
            <?php
            // Load Post Settings View (Tabs + Statuses for each Profile)
            require( $this->plugin->folder . 'views/settings-post.php' );
            ?>
        </div>
        <?php

    }

    /**
     * Retrieves a setting from the Post meta.
     *
     * Safely checks if the key(s) exist before returning the default
     * or the value.
     *
     * This function exists so that views/ files, which call $this->get_setting() in both Post
     * and Setting contexts, works correctly, meaning we don't need to duplicate our views.
     *
     * @since 3.0
     *
     * @param string $post_type Post Type
     * @param string $key       Setting key value to retrieve
     * @param string $default   Default Value
     * @return string           Value/Default Value
     */
    static public function get_setting( $post_type = '', $key, $default = '' ) {

        // Get Post ID
        global $post;
        $post_id = $post->ID;

        // Get Setting now we have a Post ID
        return self::get_setting_by_post_id( $post_id, $key, $default );

    }

    /**
     * Retrieves a setting from the Post meta by a Post ID.
     *
     * Safely checks if the key(s) exist before returning the default
     * or the value.
     *
     * @since 3.0.3
     *
     * @param mixed $type       Post Type or ID
     * @param string $key       Setting key value to retrieve
     * @param string $default   Default Value
     * @return string           Value/Default Value
     */
    static public function get_setting_by_post_id( $post_id, $key, $default = '' ) {

        // Get settings
        $settings = self::get_settings( $post_id );
        
        // Convert string to keys
        $keys = explode( '][', $key );

        foreach ( $keys as $count => $key ) {
            // Cleanup key
            $key = trim( $key, '[]' );

            // Check if key exists
            if ( ! isset( $settings[ $key ] ) ) {
                return $default;
            }

            // Key exists - make settings the value (which could be an array or the final value)
            // of this key
            $settings = $settings[ $key ];
        }

        // If here, setting exists
        return $settings; // This will be a non-array value

    }

    /**
    * Returns the settings for the given Post Type
    *
    * @since 3.0
    *
    * @param int $post_id Post ID
    * @return array Settings
    */
    static public function get_settings( $post_id ) {

        // Get current settings
        $settings = get_post_meta( $post_id, 'wp-to-buffer-pro', true );

        // Allow devs to filter before returning
        $settings = apply_filters( 'wp_to_buffer_pro_get_post_meta', $settings, $post_id );

        // Return result
        return $settings;

    }

    /**
    * Save Post-specific Plugin Settings
    *
    * @since 3.0
    *
    * @param int $post_id Post ID
    */
    static public function save_post( $post_id ) {

        // Run security checks
        // Missing nonce 
        if ( ! isset( $_POST['wp-to-buffer-pro_nonce'] ) ) { 
            return false;
        }

        // Invalid nonce
        if ( ! wp_verify_nonce( $_POST['wp-to-buffer-pro_nonce'], 'wp-to-buffer-pro' ) ) {
            return false;
        }

        // Check if any plugin settings were sent
        if ( ! isset( $_POST['wp-to-buffer-pro'] ) ) {
            return false;
        }

        // Store settings in var
        $settings = $_POST['wp-to-buffer-pro'];

        // Check if we are Quick Editing or not
        // Quick Edit will only send a few fields
        if ( isset( $_POST['_inline_edit'] ) ) {
            // Get settings
            $old_settings = self::get_settings( $post_id );

            // Merge in new settings
            if ( is_array( $old_settings ) ) {
                $settings = array_merge( $old_settings, $settings );
            }
        } else {
            // Makes the given $settings statuses associative
            $settings = WP_To_Buffer_Pro_Settings::make_statuses_associative( $settings );
        }

        // Save settings
        update_post_meta( $post_id, 'wp-to-buffer-pro', $settings );

        return true;

    } 

}