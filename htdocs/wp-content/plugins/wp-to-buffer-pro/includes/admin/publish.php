<?php
/**
* Publishing class
* 
* @package WP Cube
* @subpackage WP to Buffer Pro
* @author Tim Carr
* @version 3.0
*/
class WP_To_Buffer_Pro_Publish {

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
        add_action( 'plugins_loaded',  array( &$this, 'plugin_loaded' ) );
        add_action( 'wp_loaded', array( &$this, 'register_publish_hooks' ) );
        add_action( 'wp_to_buffer_pro', array( &$this, 'publish' ), 1, 2 );

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
    * Registers publish hooks against all public Post Types
    *
    * @since 3.0
    */
    public function register_publish_hooks() {

        // Get Post Types
        $post_types = WP_To_Buffer_Pro_Common::get_post_types_with_taxonomies();
        if ( ! is_array( $post_types ) || count( $post_types ) == 0 ) {
            return;
        }

        // Register publish hooks for each Post Type
        foreach ( $post_types as $post_type => $data ) {
            add_action( 'publish_' . $post_type, array( &$this, 'publish_now' ) );
            add_action( 'publish_future_' . $post_type, array( &$this, 'publish_future' ), 10, 1 ); 
        }
        
    }

    /**
    * Called when a Post is Published or Updated, with no Post scheduled date
    *
    * @since 3.0
    *
    * @param int $post_id   Post ID
    */
    public function publish_now( $post_id ) {

        $this->publish( $post_id );

    }

    /**
    * Called when a Post is set to be Published or Updated
    *
    * @since 3.0
    *
    * @param int $post_id   Post ID
    */
    public function publish_future( $post_id ) {

        $this->publish( $post_id, true );

    }

    /**
    * Main function. Called when any Page, Post or CPT is published or updated
    *
    * @param int    $post_id            Post ID
    * @param bool   $is_publish_action  Is Publish Action
    */
    public function publish( $post_id, $is_publish_action = false ) {

        /**
        * If a draft or new Post is published, this function is always called before WP_To_Buffer_Pro_Post::save_post()
        * We can't control this, therefore we need to save the Post's plugin settings first, before checking them - 
        * otherwise we would be looking at an old copy of the Post's settings (if any exist).
        */
        $result = WP_To_Buffer_Pro_Post::save_post( $post_id );

        // Get Post
        global $post; // Required for Quick Edit to work
        $post = get_post( $post_id );
        if ( ! $post ) {
            WP_To_Buffer_Pro_Log::update_log( $post_id, array(
                'date'              => strtotime('now'),
                'success'           => false,
                'message'           => __( 'WordPress Post could not be found.', $this->plugin->name ),
            ) );
            return false;
        }

        // Check a valid access token exists
        $access_token = WP_To_Buffer_Pro_Settings::get_access_token();
        if ( ! $access_token ) {
            WP_To_Buffer_Pro_Log::update_log( $post_id, array(
                'date'              => strtotime('now'),
                'success'           => false,
                'message'           => __( 'No access token was specified.', $this->plugin->name ),
            ) );
            return false;
        }

        // Determine if action = publish or update
        $action = 'publish';

        // Quick Edit = update
        if ( isset( $_POST['_inline_edit'] ) ) {
            $action = 'update';
        } elseif ( ! $is_publish_action && ! defined( 'XMLRPC_REQUEST' ) && isset( $_POST['original_post_status'] ) && $_POST['original_post_status'] == 'publish' ) {
            $action = 'update';
        }

        // Get Settings from either this Post or the Plugin's Settings, depending
        // on the Post's override setting
        $override = WP_To_Buffer_Pro_Post::get_setting_by_post_id( $post->ID, '[override]', 0 );
        switch ( $override ) {
            case '1':
                // Use Post Settings
                $settings = WP_To_Buffer_Pro_Post::get_settings( $post->ID );
                break;
            case '0':
                // Use Plugin Settings
                $settings = WP_To_Buffer_Pro_Settings::get_settings( $post->post_type );
                break;
            case '-1':
                // Do not Post to Buffer
                return false;
                break;
        }

        // Check settings exist
        // If not, this means the CPT or Post-level settings have not been configured, so we
        // don't need to do anything
        if ( ! $settings ) {
            return false;
        }

        // Get Profiles from Buffer
        WP_To_Buffer_Pro_Buffer_API::$access_token = $access_token;
        $profiles = WP_To_Buffer_Pro_Buffer_API::profiles();

        // Array for storing statuses we'll send to Buffer
        $statuses = array();
  
        // Iterate through each social media profile
        foreach ( $settings as $profile_id => $profile_settings ) {

            // Get detailed settings from Post or Plugin
            switch ( $override ) {
                case '1':
                    // Use Post Settings
                    $profile_enabled = WP_To_Buffer_Pro_Post::get_setting_by_post_id( $post->ID, '[' . $profile_id . '][enabled]', 0 );
                    $profile_override = WP_To_Buffer_Pro_Post::get_setting_by_post_id( $post->ID, '[' . $profile_id . '][override]', 0 );
                    if ( $profile_override ) {
                        $action_enabled = WP_To_Buffer_Pro_Post::get_setting_by_post_id( $post->ID, '[' . $profile_id . '][' . $action . '][enabled]', array() );
                        $status_settings = WP_To_Buffer_Pro_Post::get_setting_by_post_id( $post->ID, '[' . $profile_id . '][' . $action . '][status]', array() );
                        $conditions_enabled = WP_To_Buffer_Pro_Post::get_setting_by_post_id( $post->ID, '[' . $profile_id . '][conditions][enabled]', 0 );
                        $condition_settings = WP_To_Buffer_Pro_Post::get_setting_by_post_id( $post->ID, '[' . $profile_id . '][' . $action . '][conditions]', array() );
                    } else {
                        $action_enabled = WP_To_Buffer_Pro_Post::get_setting_by_post_id( $post->ID, '[default][' . $action . '][enabled]', array() );
                        $status_settings = WP_To_Buffer_Pro_Post::get_setting_by_post_id( $post->ID, '[default][' . $action . '][status]', array() );
                        $conditions_enabled = WP_To_Buffer_Pro_Post::get_setting_by_post_id( $post->ID, '[default][conditions][enabled]', 0 );
                        $condition_settings = WP_To_Buffer_Pro_Post::get_setting_by_post_id( $post->ID, '[default][' . $action . '][conditions]', array() );
                    }
                    break;

                case '0':
                    // Use Plugin Settings
                    $profile_enabled = WP_To_Buffer_Pro_Settings::get_setting( $post->post_type, '[' . $profile_id . '][enabled]', 0 );
                    $profile_override = WP_To_Buffer_Pro_Settings::get_setting( $post->post_type, '[' . $profile_id . '][override]', 0 );
                    if ( $profile_override ) {
                        $action_enabled = WP_To_Buffer_Pro_Settings::get_setting( $post->post_type, '[' . $profile_id . '][' . $action . '][enabled]', 0 );
                        $status_settings = WP_To_Buffer_Pro_Settings::get_setting( $post->post_type, '[' . $profile_id . '][' . $action . '][status]', array() );
                        $conditions_enabled = WP_To_Buffer_Pro_Settings::get_setting( $post->post_type, '[' . $profile_id . '][conditions][enabled]', 0 );
                        $condition_settings = WP_To_Buffer_Pro_Settings::get_setting( $post->post_type, '[' . $profile_id . '][conditions]', array() );
                    } else {
                        $action_enabled = WP_To_Buffer_Pro_Settings::get_setting( $post->post_type, '[default][' . $action . '][enabled]', 0 );
                        $status_settings = WP_To_Buffer_Pro_Settings::get_setting( $post->post_type, '[default][' . $action . '][status]', array() );
                        $conditions_enabled = WP_To_Buffer_Pro_Settings::get_setting( $post->post_type, '[default][conditions][enabled]', 0 );
                        $condition_settings = WP_To_Buffer_Pro_Settings::get_setting( $post->post_type, '[default][conditions]', array() );
                    }
                    break;
            }

            // Check if this profile is enabled
            if ( ! $profile_enabled ) {
                continue;
            }

            // Check if this profile's action is enabled
            if ( ! $action_enabled ) {
                continue;
            }

            // Check if conditions are enabled. If so, make sure the Post matches them before proceeding
            if ( $conditions_enabled ) {
                // By default, assume conditions aren't met
                $conditions_satisfied = false;

                // Iterate through condition taxonomies, checking if terms exist in the Post
                foreach ( $condition_settings as $taxonomy => $terms ) {
                    // Skip 'enabled' key
                    if ( $taxonomy == 'enabled' ) {
                        continue;
                    }

                    // Get Post's terms for this taxonomy
                    $post_terms = wp_get_post_terms( $post_id, $taxonomy );
                    
                    // If no terms exist, continue to the next taxonomy
                    if ( count( $post_terms ) == 0 ) {
                        continue;
                    }

                    // Terms will be a string if non-hierarchal, or an array if hierarchal
                    if ( is_array( $terms ) ) {
                        // Hierarchal (category based)
                        foreach ( $terms as $term_id => $term_enabled ) {
                            // Iterate through post terms, checking if one matches the settings
                            foreach ( $post_terms as $post_term ) {
                                if ( $post_term->term_id == $term_id ) {
                                    // Match found
                                    $conditions_satisfied = true;
                                    break;
                                }
                            }

                            // Break the loop if a term was matched
                            if ( $conditions_satisfied ) {
                                break;
                            }
                        }
                    } else {
                        // Non-hierarchal (tag based)
                        $terms = explode( ',', $terms );
                        foreach ( $terms as $term ) {
                            // @TODO
                            
                        }
                    }

                    // Break the loop if a term was matched
                    if ( $conditions_satisfied ) {
                        break;
                    }
                }

                // If conditions aren't satisfied, don't send any updates for this profile
                if ( ! $conditions_satisfied ) {
                    continue;
                }
            }

            // Determine which social media service this profile ID belongs to
            foreach ( $profiles as $profile ) {
                if ( $profile['id'] == $profile_id ) {
                    $service = $profile['service'];
                    break;
                }
            }

            // Iterate through each $status_settings, building arguments
            foreach ( $status_settings as $index => $status ) {
                $statuses[] = $this->build_args( $post, $profile_id, $service, $status );
            }

        }

        // Check if any statuses exist
        // If not, exit
        if ( count( $statuses ) == 0 ) {
            return;
        }

        // Send status messages to Buffer API
        // If CRON is enabled, schedule the API calls to run via CRON
        $cron_enabled = WP_To_Buffer_Pro_Settings::get_option( 'cron' );
        if ( ! $cron_enabled ) {
            // Now
            $this->send( $statuses, $post_id, $action );
        } else {
            // Schedule registered action
            wp_schedule_single_event( time() + 60, 'wp_to_buffer_pro_publish_cron', array(
                $statuses,
                $post_id,
                $action,
            ));
        }

    }

    /**
    * Helper method to build arguments and create a status via the Buffer API
    *
    * @since 3.0
    *
    * @param obj    $post           Post
    * @param string $profile_id     Profile ID
    * @param string $service        Service
    * @param array  $status_settings Status Settings
    * @return bool  Success
    */
    private function build_args( $post, $profile_id, $service, $status ) {

        // Build each API argument
        // Profile ID
        $args = array(
            'profile_ids'   => array( $profile_id ),
        );

        // Text
        $args['text'] = $this->parse_text( $post, $status['message'] );

        // Shorten
        $args['shorten'] = true;

        // Schedule
        switch( $status['schedule'] ) {
            case 'queue_bottom':
                // This is the default for the API, so nothing more to do here
                break;
            case 'queue_top':
                $args['top'] = true;
                break;
            case 'now':
                $args['now'] = true;
                break;
            case 'custom':
                // Add days, hours and minutes to Post's post_modified_gmt
                $timestamp = strtotime( '+' . $status['days'] . ' days ' . $status['hours'] . ' hours ' . $status['minutes'] . ' minutes', strtotime( $post->post_modified_gmt ) );
                $args['scheduled_at'] = date( 'Y-m-d H:i:s', $timestamp );
                break;
        }

        // Media
        // LinkedIn ignores the 'Use Feat Image' parameter as it shows a link to the Amazon S3 image
        if ( isset( $status['image'] ) && $status['image'] == '1' && $service != 'linkedin' ) {
            $featured_image_id = get_post_thumbnail_id( $post->ID );
            if ( $featured_image_id > 0 ) {
                $featured_image = wp_get_attachment_image_src( $featured_image_id, 'large' );
                if ( is_array( $featured_image ) ) {
                    $args['media'] = array();
                    $args['media']['title']         = $post->post_title;// Required for LinkedIn to work
                    $args['media']['picture']       = $featured_image[0];
                    $args['media']['thumbnail']     = $featured_image[0];
                    $args['media']['description']   = $post->post_title;
                    unset( $args['media']['link'] );                    // Important: if set, this attaches a link and drops the image!
                }
            }
        }

        // Pinterest
        if ( $service == 'pinterest' ) {
            $args['subprofile_ids'] = array(
                $status['sub_profile'],
            );
            $args['source_url'] = get_permalink( $post->ID );
        }

        // Allow devs to filter before returning
        $args = apply_filters( 'wp_to_buffer_pro_publish_build_args', $args, $post, $profile_id, $service, $status );

        // Return args
        return $args;

    }

    /**
    * Populates the status message by replacing tags with Post/Author data
    *
    * @since 3.0
    *
    * @param obj $post          Post
    * @param string $message    Message
    * @return string            Message
    */
    private function parse_text( $post, $message ) {

        // 1. Get author
        $author = get_user_by( 'id', $post->post_author );
        
        // 2. Check if we have an excerpt. If we don't (i.e. it's a Page or CPT with no excerpt functionality), we need
        // to create an excerpt
        if ( empty( $post->post_excerpt ) ) {
            $excerpt = wp_trim_words( strip_shortcodes( $post->post_content ) );
        } else {
            $excerpt = $post->post_excerpt;
        }
        
        // 2a. Decode certain entities for FB + G+ compatibility
        $excerpt = str_replace( '&hellip;', '...', $excerpt );

        // 3. Get all available tags
        $tags = WP_To_Buffer_Pro_Common::get_tags( $post->post_type );
        if ( ! $tags ) {
            return $message;
        }

        // 3. Parse message
        $text = $message;
        $text = str_replace( '{sitename}', get_bloginfo('name'), $text );
        $text = str_replace( '{title}', $post->post_title, $text );
        $text = str_replace( '{excerpt}', $excerpt, $text );
        $text = str_replace( '{date}', date('dS F Y', strtotime($post->post_date)), $text );
        $text = str_replace( '{url}', rtrim(get_permalink($post->ID), '/'), $text );
        $text = str_replace( '{author}', $author->display_name, $text ); // Historical; 2.3.7+ uses {author_display_name}
        $text = str_replace( '{author_user_login}', $author->user_login, $text ); 
        $text = str_replace( '{author_user_nicename}', $author->user_nicename, $text );   
        $text = str_replace( '{author_user_email}', $author->user_email, $text ); 
        $text = str_replace( '{author_user_url}', $author->user_url, $text ); 
        $text = str_replace( '{author_display_name}', $author->display_name, $text ); 
        
        // 4. Go through available taxonomies, checking if any tags exist
        $taxonomies = get_object_taxonomies( $post->post_type, 'names' );
        if ( count( $taxonomies ) > 0 ) {
            foreach ( $taxonomies as $taxonomy ) {
                // Check if this taxonomy template tag exists in status
                $tag = '{taxonomy_' . $taxonomy . '}';
                if ( strpos( $text, $tag ) === false ) {
                    continue;
                }
                
                // Check if the taxonomy has any terms
                $terms = wp_get_post_terms( $post->ID, $taxonomy );
                $termNames = '';
                if ( ! is_array( $terms ) || count( $terms ) == 0 ) {
                    continue;
                }

                // Iterate through terms
                foreach ( $terms as $term ) {
                    $term_name = strtolower( str_replace(' ', '', $term->name ) );
                    $term_name = '#' . $term_name;
                    $term_name = apply_filters( 'wp_to_buffer_pro_term', $term_name, $term->name );
                    
                    // Add term to term names string
                    $term_names .= $term_name . ' ';
                }   
                
                // Replace tags with terms
                $text = str_replace( $tag, trim( $term_names ), $text );
            }
        }
        
        // Replace custom_field_XXX and author_field_XXX
        $text = $this->replace_partial_tags( $text, '{custom_field_', $post->ID );
        $text = $this->replace_partial_tags( $text, '{author_field_', $author->ID );
        
        return $text;

    }

    /**
    * Replaces partial tag matches in a string
    *
    * @since 3.0
    * 
    * @param string $text   Text
    * @param string $needle Tag Needle
    * @param int    $id     Post or Author Object
    * @return string        Text
    */
    private function replace_partial_tags( $text, $needle, $id ) {

        $customFieldStartPos = 0;
        $positions = array();
        
        // Keep iterating through the string from the start every time until there are no {custom_field_'s left
        // This won't result in an endless loop as we replace each occurance we find, so eventually this will be false
        // and break the loop.
        while ( ( $customFieldStartPos = strpos( $text, $needle ) ) !== false ) {
            // Get tag
            $customFieldEndPos = strpos( $text, '}', $customFieldStartPos );
            $customFieldTag = substr( $text, $customFieldStartPos, ( ( $customFieldEndPos - $customFieldStartPos ) + 1 ) );
            
            // Get custom field name and value
            $customFieldName = strtolower( str_replace( '}', '', str_replace( $needle, '', $customFieldTag ) ) );
            
            switch ( $needle ) {
                case '{custom_field_':
                    $customFieldValue = get_post_meta( $id, $customFieldName, true );
                    break;
                case '{author_field_':
                    $customFieldValue = get_user_meta( $id, $customFieldName, true);
                    break;
            }
            
            
            // Replace tag with value
            $text = str_replace( $customFieldTag, $customFieldValue, $text );
        }

        return $text;

    }

    /**
    * Helper method to iterate through statuses, sending each via a separate API call
    * to the Buffer API
    *
    * @since 3.0
    *
    * @param array $statuses    Statuses
    * @param int $post_id       Post ID
    * @param string $action     Action
    */
    static public function send( $statuses, $post_id, $action ) {

        // Assume no errors
        $errors = false;

        // Setup logging
        $log = array();
        $log_enabled = WP_To_Buffer_Pro_Settings::get_option( 'log' );

        foreach ( $statuses as $index => $status ) {
            // Send request
            $result = WP_To_Buffer_Pro_Buffer_API::updates_create( $status );

            // Only continue if logging is enabled
            if ( ! $log_enabled ) {
                continue;
            }

            // Store result
            if ( is_wp_error( $result ) ) {
                // Error
                $error = true;
                $log[] = array(
                    'date'              => strtotime('now'),
                    'success'           => false,
                    'status'            => $status,
                    'message'           => $result->get_error_message(),
                );
            } else {
                // OK
                $log[] = array(
                    'date'              => strtotime('now'),
                    'success'           => true,
                    'status'            => $status,
                    'message'           => '',
                    'status_text'       => $result->updates[0]->text,
                    'status_created_at' => $result->updates[0]->created_at,
                    'status_due_at'     => $result->updates[0]->due_at,
                );
            }
        }

        // If no errors were reported, set a meta key to show a success message
        // This triggers admin_notices() to tell the user what happened
        if ( ! $errors ) {
            update_post_meta( $post_id, '_wp_to_buffer_pro_success', 1 );
            update_post_meta( $post_id, '_wp_to_buffer_pro_error', 0 );
        } else {
            update_post_meta( $post_id, '_wp_to_buffer_pro_success', 0 );
            update_post_meta( $post_id, '_wp_to_buffer_pro_error', 1 );
        }

        // Save log
        if ( $log_enabled ) {
            WP_To_Buffer_Pro_Log::update_log( $post_id, $log );
        }
        
    }

}

/**
* WP Cron to send status updates to Buffer
*
* Defined here as WP Cron doesn't support class functions
*
* @since 3.0
* 
* @param array $statuses    Statuses
* @param int $post_id       Post ID
* @param string $action     Action
*/
function wp_to_buffer_pro_publish_cron( $statuses, $post_id, $action ) {

    // Setup the Buffer API
    $access_token = WP_To_Buffer_Pro_Settings::get_access_token();
    if ( ! $access_token ) {
        WP_To_Buffer_Pro_Log::update_log( $post_id, array(
            'date'              => strtotime('now'),
            'success'           => false,
            'message'           => __( 'No access token was specified.', 'wp-to-buffer-pro' ),
        ) );
        return false;
    }

    // Set Access Token
    WP_To_Buffer_Pro_Buffer_API::$access_token = $access_token;

    // Send request to Buffer API
    WP_To_Buffer_Pro_Publish::send( $statuses, $post_id, $action );

}
add_action( 'wp_to_buffer_pro_publish_cron', 'wp_to_buffer_pro_publish_cron', 10, 3 );