<?php
/**
* Buffer Class
* 
* @package WP Cube
* @subpackage WP to Buffer Pro
* @author Tim Carr
* @version 3.0
* @copyright WP Cube
*/
class WP_To_Buffer_Pro_Buffer_API {

	/**
     * Access Token
     *
     * @since 3.0
     *
     * @var object
     */
    static public $access_token = '';


    /**
    * Sets this class' access token
    *
    * @since 3.0
    *
    * @param string $access_token Access Token
    */
    static public function set_access_token( $access_token ) {

    	self::$access_token = $access_token;

    }

    /**
    * Checks if an access token was set.  Called by any function which 
    * performs a call to the Buffer API
    *
    * @since 3.0
    *
    * @return bool Token Exists
    */
    static public function check_access_token_exists() {

        if ( empty( self::$access_token ) ) {
            return false;
        }

        return true;

    }

    /**
    * Returns the User object
    *
    * @since 3.0
    */
	static public function user( ) {

        // Check access token
        if ( ! self::check_access_token_exists() ) {
            return false;
        }

		return self::get( 'user.json' );

	}

	/**
    * Returns a list of Social Media Profiles
    *
    * @since 3.0
    *
    * @param bool $force Force API call (false = use WordPress transient)
    */
	static public function profiles( $force = false ) {

        // Check access token
        if ( ! self::check_access_token_exists() ) {
            return false;
        }

        // Setup profiles array
        $profiles = array();

        // Check if our WordPress transient already has this data.
        // This reduces the number of times we query the API
        if ( $force || false === ( $profiles = get_transient( 'wp_to_buffer_pro_buffer_api_profiles' ) ) ) {
            // Get profiles
            $results = self::get( 'profiles.json?subprofiles=1' );

            // Check data is valid
            foreach ( $results as $result ) {
                $profiles[ $result->id ] = array(
                    'id'                => $result->id,
                    'formatted_service' => $result->formatted_service,
                    'formatted_username'=> $result->formatted_username,
                    'avatar'            => $result->avatar,
                    'service'           => $result->service,
                );

                // Pinterest: add subprofiles
                if ( isset( $result->subprofiles ) && count( $result->subprofiles ) > 0 ) {
                    $profiles[ $result->id ]['subprofiles'] = array();
                    foreach ( $result->subprofiles as $sub_profile ) {
                        $profiles[ $result->id ]['subprofiles'][ $sub_profile->id ] = array(
                            'id'        => $sub_profile->id,
                            'name'      => $sub_profile->name,
                            'service'   => $sub_profile->service,
                        );
                    }
                }
            }
            
            // Store profiles in transient
            set_transient( 'wp_to_buffer_pro_buffer_api_profiles', $profiles, WP_To_Buffer_Pro_Common::get_transient_expiration_time() );
        }

		// Return results
        return $profiles;

	}

    /**
    * Creates an Update
    *
    * @since 3.0
    */
    static public function updates_create( $params ) {

        return self::post( 'updates/create.json', $params );

    }

	/**
	* Private function to perform a GET request
	*
	* @since 3.0
	*
	* @param string $cmd 	Command (required)
	* @param array $params 	Params (optional)
	* @return object 		Result
	*/
	static private function get( $cmd, $params = array() ) {

		return self::request( $cmd, 'get', $params );

	}

	/**
	* Private function to perform a POST request
	*
	* @since 3.0
	*
	* @param string $cmd 	Command (required)
	* @param array $params 	Params (optional)
	* @return object 		Result
	*/
	static private function post( $cmd, $params = array() ) {

		return self::request( $cmd, 'post', $params );

	}

	/**
    * Main function which handles sending requests to the Buffer API
    *
    * @since 3.0
    *
    * @param string $cmd 			Command
    * @param string $method 		Method (get|post)
    * @param array $params 			Parameters (optional)
    * @return mixed 				JSON decoded object or error string
    */
    static private function request( $cmd, $method = 'get', $params = array() ) {

    	// Check required parameters exist
    	if ( empty( self::$access_token ) ) {
            return new WP_Error( 'missing_access_token', __( 'No access token was specified', 'wp-to-buffer-pro' ) );
    	}

    	// Add access token to command, depending on the command's format
    	if ( strpos ( $cmd, '?' ) !== false ) {
    		$cmd .= '&access_token=' . self::$access_token;
    	} else {
    		$cmd .= '?access_token=' . self::$access_token;
    	}

    	// Build endpoint URL
    	$url = 'https://api.bufferapp.com/1/' . $cmd;
				
		// Send request
		switch ( $method ) {
            /**
            * GET
            */
			case 'get':
                $result = wp_remote_get( $url, array(
		    		'body'      => $params,
		    		'sslverify' => false,
		    	) );
				break;
            
            /**
            * POST
            */
			case 'post':
				$result = wp_remote_post( $url, array(
		    		'body' 		=> $params,
		    		'sslverify' => false,
		    	));
				break;
		}

    	// Check the request is valid
    	if ( is_wp_error( $result ) ) {
            return $result;
        }
		if ( $result['response']['code'] != 200 ) {
			$body = json_decode( $result['body'] );
			return new WP_Error( 
				$result['response']['code'], 
				'Buffer API Error: ' . $result['response']['code'] . ' ' . $result['response']['message'] . '. ' . $body->message 
			);
		}
		
        // OK
		return json_decode( $result['body'] );		

    }



}