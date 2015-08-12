<?php
/**
* Common class
* 
* @package WP Cube
* @subpackage WP to Buffer Pro
* @author Tim Carr
* @version 3.0
*/
class WP_To_Buffer_Pro_Common {

    /**
     * Helper method to retrieve schedule options
     *
     * @since 3.0
     *
     * @return array Schedule Options
     */
    static public function get_schedule_options() {

        // Build schedule options
        $schedule = array(
            'queue_bottom'  => __( 'Add to End of Buffer Queue', 'wp-to-buffer-pro' ),
            'queue_top'     => __( 'Add to Start of Buffer Queue', 'wp-to-buffer-pro' ),
            'now'           => __( 'Post Immediately', 'wp-to-buffer-pro' ),
            'custom'        => __( 'Custom Time', 'wp-to-buffer-pro' ),
        );

        // Return filtered results
        return apply_filters( 'wp_to_buffer_pro_get_schedule_options', $schedule );

    }

    /**
    * Helper method to retrieve public Post Types
    *
    * @since 3.0
    *
    * @return array Public Post Types
    */
    static public function get_post_types() {

        // Get public Post Types
        $types = get_post_types( array(
            'public' => true,
        ), 'objects' );

        // Filter out excluded post types
        $excluded_types = self::get_excluded_post_types();
        if ( is_array( $excluded_types ) ) {
            foreach ( $excluded_types as $excluded_type ) {
                unset( $types[ $excluded_type ] );
            }
        }

        // Return filtered results
        return apply_filters( 'wp_to_buffer_pro_get_post_types', $types );

    }

    /**
     * Helper method to retrieve excluded Post Types
     *
     * @since 3.0
     *
     * @return array Excluded Post Types
     */
    static public function get_excluded_post_types() {

        // Get excluded Post Types
        $types = array(
            'attachment',
        );

        // Return filtered results
        return apply_filters( 'wp_to_buffer_pro_get_excluded_post_types', $types );

    }

    /**
    * Helper method to retrieve public Post Types with Taxonomies
    *
    * @since 3.0
    *
    * @return array Public Post Types
    */
    static public function get_post_types_with_taxonomies() {

        // Get Public Post Types
        $types = self::get_post_types();
        if ( ! is_array( $types ) ) {
            return false;
        }

        // Get taxonomies for each Post Type
        foreach ( $types as $key => $type ) {
            $types[ $key ]->taxonomies = self::get_taxonomies( $key );
        }

        // Return filtered results
        return apply_filters( 'wp_to_buffer_pro_get_post_types_with_taxonomies', $types );

    }

    /**
    * Helper method to retrieve a Post Type's taxonomies
    *
    * @since 3.0
    *
    * @param string $post_type Post Type
    * @return array Taxonomies
    */
    static public function get_taxonomies( $post_type ) {

        // Get Post Type Taxonomies
        $taxonomies = get_object_taxonomies( $post_type, 'objects' );

        // Get terms for each Taxonomy
        foreach ( $taxonomies as $taxonomy => $details ) {
            $taxonomies[ $taxonomy ]->terms = self::get_taxonomy_terms( $taxonomy );
        }

        // Return filtered results
        return apply_filters( 'wp_to_buffer_pro_get_taxonomies', $taxonomies, $post_type );

    }

    /**
    * Helper method to retrieve a Taxonomies' Terms
    *
    * @since 3.0
    *
    * @param string $taxonomy Taxonomy
    * @return array Terms
    */
    static public function get_taxonomy_terms( $taxonomy ) {

        // Get Taxonomy Terms
        $terms = get_terms( $taxonomy, array(
            'hide_empty' => false,
        ) );

        // Return filtered results
        return apply_filters( 'wp_to_buffer_pro_get_taxonomy_terms', $terms, $taxonomy );

    }

    /**
     * Helper method to retrieve available tags for status updates
     *
     * @since 3.0
     *
     * @param string $post_type Post Type
     * @return array Tags
     */
    static public function get_tags( $post_type ) {

        // Get post type
        $post_types = self::get_post_types_with_taxonomies();
        $post_type_obj = $post_types[ $post_type ];

        // Build tags array
        $tags = array(
            'post' => array(
                '{sitename}'            => __( 'Site Name', 'wp-to-buffer-pro' ),
                '{title}'               => __( 'Post Title', 'wp-to-buffer-pro' ),
                '{excerpt}'             => __( 'Post Excerpt', 'wp-to-buffer-pro' ),
                '{date}'                => __( 'Post Date', 'wp-to-buffer-pro' ),
                '{url}'                 => __( 'Post URL', 'wp-to-buffer-pro' ),
                '{custom_field_NAME}'   => __( 'Post Meta Field', 'wp-to-buffer-pro' ), 
            ),

            'author' => array(
                '{author_user_login}'   => __( 'Author Login', 'wp-to-buffer-pro' ), 
                '{author_user_nicename}'=> __( 'Author Nice Name', 'wp-to-buffer-pro' ), 
                '{author_user_email}'   => __( 'Author Email', 'wp-to-buffer-pro' ), 
                '{author_user_url}'     => __( 'Author URL', 'wp-to-buffer-pro' ), 
                '{author_display_name}' => __( 'Author Display Name', 'wp-to-buffer-pro' ), 
                '{author_field_NAME}'   => __( 'Author Meta Field', 'wp-to-buffer-pro' ), 
            ),

        );

        // Add any taxonomies for the given Post Type
        if ( count( $post_type_obj->taxonomies ) > 0 ) {
            $tags['taxonomy'] = array();

            // We have taxonomies for this Post Type - output tags
            foreach ( $post_type_obj->taxonomies as $tax => $details ) {
                $tags['taxonomy']['{taxonomy_' . $tax . '}'] = __( 'Taxonomy: ', 'wp-to-buffer-pro' ) . $details->labels->singular_name;
            }   
        }  

        // Return filtered results
        return apply_filters( 'wp_to_buffer_pro_get_tags', $tags, $post_type, $post_type_obj );

    }

    /**
    * Helper method to retrieve Post actions
    *
    * @since 3.0
    *
    * @return array Post Actions
    */
    static public function get_post_actions() {

        // Build post actions
        $actions = array(
            'publish'   => __( 'Publish', 'wp-to-buffer-pro' ),
            'update'    => __( 'Update', 'wp-to-buffer-pro' ),
            'conditions'=> __( 'Conditions', 'wp-to-buffer-pro' ),
        );

        // Return filtered results
        return apply_filters( 'wp_to_buffer_pro_get_post_actions', $actions );

    }

    /**
    * Helper method to retrieve transient expiration time
    *
    * @since 3.0
    *
    * @return int Expiration Time (seconds)
    */
    static public function get_transient_expiration_time() {

        // Set expiration time for all transients = 12 hours
        $expiration_time = ( 12 * HOUR_IN_SECONDS );

        // Return filtered results
        return apply_filters( 'wp_to_buffer_pro_get_transient_expiration_time', $expiration_time );

    }

}