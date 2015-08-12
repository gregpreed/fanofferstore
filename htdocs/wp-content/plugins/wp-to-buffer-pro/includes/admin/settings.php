<?php
/**
* Settings class
* 
* @package WP Cube
* @subpackage WP to Buffer Pro
* @author Tim Carr
* @version 3.0
*/
class WP_To_Buffer_Pro_Settings {

    /**
    * Migrates settings from the free version or Pro version 2.x
    *
    * @since 3.0
    */
    static public function migrate_settings() {

        // Check if we have any old settings
        $old_settings = get_option( 'wp-to-buffer' );

        // If old settings are empty, bail
        if ( ! $old_settings ) {
            return;
        }

        // Store the old settings in a backup option key, just in case
        update_option( 'wp-to-buffer-v2', $old_settings );

        // Migrate into new settings
        // Access token
        if ( ! empty( $old_settings['accessToken'] ) ) {
            self::update_access_token( $old_settings['accessToken'] );
        }

        // Get Buffer Profiles
        WP_To_Buffer_Pro_Buffer_API::set_access_token( WP_To_Buffer_Pro_Settings::get_access_token() );
        $profiles       = WP_To_Buffer_Pro_Buffer_API::profiles( true );

        // Get Actions
        $actions = WP_To_Buffer_Pro_Common::get_post_actions();

        // Iterate through each Post Type
        foreach ( $old_settings['enabled'] as $post_type => $old_actions ) {
            $new_settings = array();

            // Default profile
            $new_settings['default'] = array();
            
            // Default profile: actions
            foreach ( $actions as $action => $action_label ) {
                if ( $action == 'conditions' ) {
                    /**
                    * Conditions (was Filters)
                    */
                    $new_settings['default']['conditions'] = array();
                    $new_settings['default']['conditions']['enabled'] = ( isset( $old_settings['filter'][ $post_type ] ) ? $old_settings['filter'][ $post_type ] : 0 );
                    if ( $new_settings['default']['conditions']['enabled'] ) {
                        foreach ( $old_settings['tax'][ $post_type ] as $taxonomy => $items ) {
                            $new_settings['default']['conditions'][ $taxonomy ] = $items;
                        }
                    }
                } else {
                    /**
                    * Publish/Update
                    */
                    $new_settings['default'][ $action ] = array();
                    $new_settings['default'][ $action ]['enabled'] = ( isset( $old_settings['enabled'][ $post_type ][ $action ] ) ? $old_settings['enabled'][ $post_type ][ $action ] : 0 );
                    $new_settings['default'][ $action ]['status'] = array();
                    $new_settings['default'][ $action ]['status'][] = array(
                        'image'         => ( isset( $old_settings['image'][ $post_type ][ $action ] ) ? $old_settings['image'][ $post_type ][ $action ]: 0 ),
                        'sub_profile'   => 0, // Pinterest not supported in free or v2.x
                        'message'       => ( isset( $old_settings['message'][ $post_type ][ $action ] ) ? $old_settings['message'][ $post_type ][ $action ] : '' ),
                        'schedule'      => ( ( isset( $old_settings['enabled'][ $post_type ]['instant'] ) && $old_settings['enabled'][ $post_type ]['instant'] == 1 ) ? 'now' : 'queue_bottom' ),
                        'days'          => 0,
                        'hours'         => 0,
                        'minutes'       => 0,
                    );
                    if ( $old_settings['number'][ $post_type ][ $action ] == 2 ) {
                        // Alternate status
                        $new_settings['default'][ $action ]['status'][] = array(
                            'image'         => ( isset( $old_settings['image'][ $post_type ][ $action ] ) ? $old_settings['image'][ $post_type ][ $action ]: 0 ),
                            'sub_profile'   => 0, // Pinterest not supported in free or v2.x
                            'message'       => ( isset( $old_settings['alternateMessage'][ $post_type ][ $action ] ) ? $old_settings['alternateMessage'][ $post_type ][ $action ] : '' ),
                            'schedule'      => ( ( isset( $old_settings['enabled'][ $post_type ]['instant'] ) && $old_settings['enabled'][ $post_type ]['instant'] == 1 ) ? 'now' : 'queue_bottom' ),
                            'days'          => 0,
                            'hours'         => 0,
                            'minutes'       => 0,
                        );
                    }
                    if ( $old_settings['number'][ $post_type ][ $action ] == 3 ) {
                        // Original status, again
                        $new_settings['default'][ $action ]['status'][] = array(
                            'image'         => ( isset( $old_settings['image'][ $post_type ][ $action ] ) ? $old_settings['image'][ $post_type ][ $action ]: 0 ),
                            'sub_profile'   => 0, // Pinterest not supported in free or v2.x
                            'message'       => ( isset( $old_settings['message'][ $post_type ][ $action ] ) ? $old_settings['message'][ $post_type ][ $action ] : '' ),
                            'schedule'      => ( ( isset( $old_settings['enabled'][ $post_type ]['instant'] ) && $old_settings['enabled'][ $post_type ]['instant'] == 1 ) ? 'now' : 'queue_bottom' ),
                            'days'          => 0,
                            'hours'         => 0,
                            'minutes'       => 0,
                        );
                    }
                }
            }

            // Iterate through Buffer Profiles
            foreach ( $profiles as $profile_id => $profile ) {        
                // Default profile
                $new_settings[ $profile_id ] = array();
                
                // Default profile: actions
                foreach ( $actions as $action => $action_label ) {
                    if ( $action == 'conditions' ) {
                        /**
                        * Conditions (was Filters)
                        */
                        $new_settings[ $profile_id ]['conditions'] = array();
                        $new_settings[ $profile_id ]['conditions']['enabled'] = ( isset( $old_settings[ $profile_id ]['filter'][ $post_type ] ) ? $old_settings[ $profile_id ]['filter'][ $post_type ] : 0 );
                        if ( $new_settings[ $profile_id ]['conditions']['enabled'] ) {
                            foreach ( $old_settings[ $profile_id ]['tax'][ $post_type ] as $taxonomy => $items ) {
                                $new_settings[ $profile_id ]['conditions'][ $taxonomy ] = $items;
                            }
                        }
                    } else {
                        /**
                        * Publish/Update
                        */

                        // Profile enabled + overriding?
                        $new_settings[ $profile_id ]['enabled'] = ( isset( $old_settings['ids'][ $post_type ][ $profile_id ] ) ? 1 : 0 );
                        $new_settings[ $profile_id ]['override'] = ( isset( $old_settings['override'][ $post_type ][ $profile_id ] ) ? 1 : 0 );
                        
                        // Profile action
                        $new_settings[ $profile_id ][ $action ] = array();
                        $new_settings[ $profile_id ][ $action ]['enabled'] = ( isset( $old_settings[ $profile_id ]['enabled'][ $post_type ][ $action ] ) ? 1 : 0 );
                        $new_settings[ $profile_id ][ $action ]['status'] = array();
                        $new_settings[ $profile_id ][ $action ]['status'][] = array(
                            'image'         => ( isset( $old_settings[ $profile_id ]['image'][ $post_type ][ $action ] ) ? $old_settings[ $profile_id ]['image'][ $post_type ][ $action ]: 0 ),
                            'sub_profile'   => 0, // Pinterest not supported in free or v2.x
                            'message'       => ( isset( $old_settings[ $profile_id ]['message'][ $post_type ][ $action ] ) ? $old_settings[ $profile_id ]['message'][ $post_type ][ $action ] : '' ),
                            'schedule'      => ( ( isset( $old_settings[ $profile_id ]['enabled'][ $post_type ]['instant'] ) && $old_settings[ $profile_id ]['enabled'][ $post_type ]['instant'] == 1 ) ? 'now' : 'queue_bottom' ),
                            'days'          => 0,
                            'hours'         => 0,
                            'minutes'       => 0,
                        );
                        if ( $old_settings['number'][ $post_type ][ $action ] == 2 ) {
                            // Alternate status
                            $new_settings[ $profile_id ][ $action ]['status'][] = array(
                                'image'         => ( isset( $old_settings[ $profile_id ]['image'][ $post_type ][ $action ] ) ? $old_settings[ $profile_id ]['image'][ $post_type ][ $action ]: 0 ),
                                'sub_profile'   => 0, // Pinterest not supported in free or v2.x
                                'message'       => ( isset( $old_settings[ $profile_id ]['alternateMessage'][ $post_type ][ $action ] ) ? $old_settings[ $profile_id ]['alternateMessage'][ $post_type ][ $action ] : '' ),
                                'schedule'      => ( ( isset( $old_settings[ $profile_id ]['enabled'][ $post_type ]['instant'] ) && $old_settings[ $profile_id ]['enabled'][ $post_type ]['instant'] == 1 ) ? 'now' : 'queue_bottom' ),
                                'days'          => 0,
                                'hours'         => 0,
                                'minutes'       => 0,
                            );
                        }
                        if ( $old_settings['number'][ $post_type ][ $action ] == 3 ) {
                            // Original status, again
                            $new_settings[ $profile_id ][ $action ]['status'][] = array(
                                'image'         => ( isset( $old_settings[ $profile_id ]['image'][ $post_type ][ $action ] ) ? $old_settings[ $profile_id ]['image'][ $post_type ][ $action ]: 0 ),
                                'sub_profile'   => 0, // Pinterest not supported in free or v2.x
                                'message'       => ( isset( $old_settings[ $profile_id ]['message'][ $post_type ][ $action ] ) ? $old_settings[ $profile_id ]['message'][ $post_type ][ $action ] : '' ),
                                'schedule'      => ( ( isset( $old_settings[ $profile_id ]['enabled'][ $post_type ]['instant'] ) && $old_settings[ $profile_id ]['enabled'][ $post_type ]['instant'] == 1 ) ? 'now' : 'queue_bottom' ),
                                'days'          => 0,
                                'hours'         => 0,
                                'minutes'       => 0,
                            );
                        }
                    }
                }
            }

            // We now have a new settings array that's v3 compatible
            update_option( 'wp-to-buffer-pro-' . $post_type, $new_settings );
        } // Close post type

        // Clear old settings
        delete_option( 'wp-to-buffer' );

    }

    /**
     * Retrieves a setting from the options table.
     *
     * Safely checks if the key(s) exist before returning the default
     * or the value.
     *
     * @since 3.0
     *
     * @param string $type      Post Type
     * @param string $key       Setting key value to retrieve
     * @param string $default   Default Value
     * @return string           Value/Default Value
     */
    static public function get_setting( $post_type, $key, $default = '' ) {

        // Get settings
        $settings = self::get_settings( $post_type );

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
    * @param string $post_type Post Type
    * @return array Settings
    */
    static public function get_settings( $post_type ) {

        // Get current settings
        $settings = get_option( 'wp-to-buffer-pro-' . $post_type );

        // Allow devs to filter before returning
        $settings = apply_filters( 'wp_to_buffer_pro_get_settings', $settings, $post_type );

        // Return result
        return $settings;

    }

    /**
    * Stores the given settings for the given Post Type into the options table
    *
    * @since 3.0.0
    *
    * @param string $type       Post Type
    * @param array $settings    Settings
    * @return bool              Success
    */
    static public function update_settings( $post_type, $settings ) {

        // Makes the given $settings statuses associative
        $settings = self::make_statuses_associative( $settings );
        
        // Allow devs to filter before saving
        $settings = apply_filters( 'wp_to_buffer_pro_update_settings', $settings, $post_type );

        // update_option will return false if no changes were made, so we can't rely on this
        update_option( 'wp-to-buffer-pro-' . $post_type, $settings );
        
        return true;
    }

    /**
    * Makes the given $settings statuses associative e.g.
    * $settings[profile_id][publish][status][message][0] --> $settings[profile_id][publish][status][0][message]
    *
    * @since 3.0
    *
    * @param array $settings Settings
    * @return array Associative Settings
    */
    static public function make_statuses_associative( $settings ) {

        // Get available actions
        $actions = WP_To_Buffer_Pro_Common::get_post_actions();

        // Iterate through settings, updatning statuses so they are are associative
        foreach ( $settings as $profile_id => $profile_settings ) {
            // Iterate through actions for each profile
            foreach ( $actions as $action => $action_label ) {
                // Check some statuses are specified for this action
                if ( ! isset( $profile_settings[ $action ] ) ) {
                    continue;
                }
                if ( ! isset( $profile_settings[ $action ]['status'] ) ) {
                    continue;
                }

                // Iterate through each status, to build the associative array
                $statuses = array();
                foreach ($profile_settings[ $action ]['status']['message'] as $index => $message ) {
                    $statuses[] = array(
                        'image'         => ( isset( $profile_settings[ $action ]['status']['image'][ $index ] ) ? $profile_settings[ $action ]['status']['image'][ $index ] : 0 ),
                        'sub_profile'   => ( isset( $profile_settings[ $action ]['status']['sub_profile'][ $index ] ) ? $profile_settings[ $action ]['status']['sub_profile'][ $index ] : 0 ),
                        'message'       => ( isset( $profile_settings[ $action ]['status']['message'][ $index ] ) ? $profile_settings[ $action ]['status']['message'][ $index ] : '' ),
                        'schedule'      => ( isset( $profile_settings[ $action ]['status']['schedule'][ $index ] ) ? $profile_settings[ $action ]['status']['schedule'][ $index ] : '' ),
                        'days'          => ( isset( $profile_settings[ $action ]['status']['days'][ $index ] ) ? $profile_settings[ $action ]['status']['days'][ $index ] : 0 ),
                        'hours'         => ( isset( $profile_settings[ $action ]['status']['hours'][ $index ] ) ? $profile_settings[ $action ]['status']['hours'][ $index ] : 0 ),
                        'minutes'       => ( isset( $profile_settings[ $action ]['status']['minutes'][ $index ] ) ? $profile_settings[ $action ]['status']['minutes'][ $index ] : 0 ), 
                    );
                }

                // Assign statuses back to status key
                $settings[ $profile_id ][ $action ]['status'] = $statuses;
                
            }
        }

        return $settings;

    }

    /**
    * Retrieves the access token from the options table
    *
    * @since 3.0
    *
    * @return string Access Token
    */
    static public function get_access_token() {

        return get_option( 'wp-to-buffer-pro-access-token' );

    }

    /**
    * Stores the given access token into the options table
    *
    * @param string $access_token   Access Token
    * @return bool                  Success
    */
    static public function update_access_token( $access_token ) {

        // Allow devs to filter before saving
        $access_token = apply_filters( 'wp_to_buffer_pro_update_access_token', $access_token );

        // Return result
        return update_option( 'wp-to-buffer-pro-access-token', $access_token );

    }

    /**
    * Helper method to get a value from the options table
    *
    * @since 3.0
    *
    * @return string Access Token
    */
    static public function get_option( $key ) {

        return get_option( 'wp-to-buffer-pro-' . $key );

    }

    /**
    * Helper method to store a value to the options table
    *
    * @param string $key    Key
    * @param string $value  Value
    * @return bool          Success
    */
    static public function update_option( $key, $value ) {

        // Allow devs to filter before saving
        $value = apply_filters( 'wp_to_buffer_pro_update_option', $value, $key );

        // Update
        update_option( 'wp-to-buffer-pro-' . $key, $value );

        return true;

    }

}