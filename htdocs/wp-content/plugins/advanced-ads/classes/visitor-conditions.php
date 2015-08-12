<?php

/**
 * visitor conditions under which to (not) show an ad
 *
 * @since 1.5.4
 *
 */
class Advanced_Ads_Visitor_Conditions {

	/**
	 *
	 * @var Advanced_Ads_Visitor_Conditions
	 */
	protected static $instance;

	/**
	 * registered visitor conditions
	 */
	public $conditions;

	/**
	 * start of name in form elements
	 */
	const FORM_NAME = 'advanced_ad[visitors]';

	public function __construct() {

	    // register conditions
	    $this->conditions = apply_filters( 'advanced-ads-visitor-conditions', array(
			'mobile' => array( // type of the condition
				'label' => __( 'mobile device', ADVADS_SLUG ),
				'description' => __( 'Display ads only on mobile devices or hide them.', ADVADS_SLUG ),
				'metabox' => array( 'Advanced_Ads_Visitor_Conditions', 'metabox_is_or_not' ), // callback to generate the metabox
				'check' => array( 'Advanced_Ads_Visitor_Conditions', 'check_mobile' ) // callback for frontend check
			),
			'loggedin' => array(
				'label' => __( 'logged in visitor', ADVADS_SLUG ),
				'description' => __( 'Whether the visitor has to be logged in or not in order to see the ads.', ADVADS_SLUG ),
				'metabox' => array( 'Advanced_Ads_Visitor_Conditions', 'metabox_is_or_not' ), // callback to generate the metabox
				'check' => array( 'Advanced_Ads_Visitor_Conditions', 'check_logged_in' ) // callback for frontend check
			),
	    ));

	    ksort( $this->conditions );
	}

	/**
	 *
	 * @return Advanced_Ads_Plugin
	 */
	public static function get_instance() {
		// If the single instance hasn't been set, set it now.
		if ( null === self::$instance ) {
			self::$instance = new self;
		}

		return self::$instance;
	}

	/**
	 * callback to display the "is not" condition
	 *
	 * @param arr $options options of the condition
	 * @param int $index index of the condition
	 */
	static function metabox_is_or_not( $options, $index = 0 ){

	    if ( ! isset ( $options['type'] ) || '' === $options['type'] ) { return; }

	    $type_options = self::get_instance()->conditions;

	    if ( ! isset( $type_options[ $options['type'] ] ) ) {
		    return;
	    }

	    // form name basis
	    $name = self::FORM_NAME . '[' . $index . ']';

	    // options
	    $operator = isset( $options['operator'] ) ? $options['operator'] : 'is';
	    $connector = isset( $options['connector'] ) ? $options['connector'] : 'and';

	    ?><input type="hidden" name="<?php echo $name; ?>[type]" value="<?php echo $options['type']; ?>"/>
		<input type="hidden" name="<?php echo $name; ?>[connector]" value="<?php echo $connector; ?>"/>
		<select name="<?php echo $name; ?>[operator]">
		<option value="is" <?php selected( 'is', $operator ); ?>><?php _e( 'is' ); ?></option>
		<option value="is_not" <?php selected( 'is_not', $operator ); ?>><?php _e( 'is not' ); ?></option>
	    </select>
	    <p class="description"><?php echo $type_options[ $options['type'] ]['description']; ?></p><?php
	}

	/**
	 * callback to display the any condition based on a number
	 *
	 * @param arr $options options of the condition
	 * @param int $index index of the condition
	 */
	static function metabox_number( $options, $index = 0 ){

	    if ( ! isset ( $options['type'] ) || '' === $options['type'] ) { return; }

	    $type_options = self::get_instance()->conditions;

	    if ( ! isset( $type_options[ $options['type'] ] ) ) {
		    return;
	    }

	    // form name basis
	    $name = self::FORM_NAME . '[' . $index . ']';

	    // options
	    $value = isset( $options['value'] ) ? $options['value'] : 0;
	    $operator = isset( $options['operator'] ) ? $options['operator'] : 'is_equal';
	    $connector = isset( $options['connector'] ) ? $options['connector'] : 'and';

	    ?><input type="hidden" name="<?php echo $name; ?>[type]" value="<?php echo $options['type']; ?>"/>
		<input type="hidden" name="<?php echo $name; ?>[connector]" value="<?php echo $connector; ?>"/>
		<select name="<?php echo $name; ?>[operator]">
		    <option value="is_equal" <?php selected( 'is_equal', $operator ); ?>><?php _e( 'equal', ADVADS_SLUG ); ?></option>
		    <option value="is_higher" <?php selected( 'is_higher', $operator ); ?>><?php _e( 'equal or higher', ADVADS_SLUG ); ?></option>
		    <option value="is_lower" <?php selected( 'is_lower', $operator ); ?>><?php _e( 'equal or lower', ADVADS_SLUG ); ?></option>
		</select><input type="number" name="<?php echo $name; ?>[value]" value="<?php echo absint( $value ); ?>"/>
	    <p class="description"><?php echo $type_options[ $options['type'] ]['description']; ?></p><?php
	}

	/**
	 * callback to display the any condition based on a number
	 *
	 * @param arr $options options of the condition
	 * @param int $index index of the condition
	 */
	static function metabox_string( $options, $index = 0 ){

	    if ( ! isset ( $options['type'] ) || '' === $options['type'] ) { return; }

	    $type_options = self::get_instance()->conditions;

	    if ( ! isset( $type_options[ $options['type'] ] ) ) {
		    return;
	    }

	    // form name basis
	    $name = self::FORM_NAME . '[' . $index . ']';

	    // options
	    $value = isset( $options['value'] ) ? $options['value'] : '';
	    $operator = isset( $options['operator'] ) ? $options['operator'] : 'contains';
	    $connector = isset( $options['connector'] ) ? $options['connector'] : 'and';

	    ?><input type="hidden" name="<?php echo $name; ?>[type]" value="<?php echo $options['type']; ?>"/>
		<input type="hidden" name="<?php echo $name; ?>[connector]" value="<?php echo $connector; ?>"/>
		<select name="<?php echo $name; ?>[operator]">
		    <option value="contain" <?php selected( 'contain', $operator ); ?>><?php _e( 'contains', ADVADS_SLUG ); ?></option>
		    <option value="start" <?php selected( 'start', $operator ); ?>><?php _e( 'starts with', ADVADS_SLUG ); ?></option>
		    <option value="end" <?php selected( 'end', $operator ); ?>><?php _e( 'ends with', ADVADS_SLUG ); ?></option>
		    <option value="match" <?php selected( 'match', $operator ); ?>><?php _e( 'matches', ADVADS_SLUG ); ?></option>
		    <option value="regex" <?php selected( 'regex', $operator ); ?>><?php _e( 'matches regex', ADVADS_SLUG ); ?></option>
		    <option value="contain_not" <?php selected( 'contain_not', $operator ); ?>><?php _e( 'does not contain', ADVADS_SLUG ); ?></option>
		    <option value="start_not" <?php selected( 'start_not', $operator ); ?>><?php _e( 'does not start with', ADVADS_SLUG ); ?></option>
		    <option value="end_not" <?php selected( 'end_not', $operator ); ?>><?php _e( 'does not end with', ADVADS_SLUG ); ?></option>
		    <option value="match_not" <?php selected( 'match_not', $operator ); ?>><?php _e( 'does not match', ADVADS_SLUG ); ?></option>
		    <option value="regex_not" <?php selected( 'regex_not', $operator ); ?>><?php _e( 'does not match regex', ADVADS_SLUG ); ?></option>
		</select><input type="text" name="<?php echo $name; ?>[value]" value="<?php echo $value; ?>"/>
	    <p class="description"><?php echo $type_options[ $options['type'] ]['description']; ?></p><?php
	}

	/**
	 * controls frontend checks for conditions
	 *
	 * @param arr $options options of the condition
	 * @return bool false, if ad can’t be delivered
	 */
	static function frontend_check( $options = array() ){
		$visitor_conditions = Advanced_Ads_Visitor_Conditions::get_instance()->conditions;

		if ( is_array( $options ) && isset( $visitor_conditions[ $options['type'] ]['check'] ) ) {
			$check = $visitor_conditions[ $options['type'] ]['check'];
		} else {
			return true;
		}

		// call frontend check callback
		if ( method_exists( $check[0], $check[1] ) ) {
			return call_user_func( array( $check[0], $check[1] ), $options );
		}

		return true;
	}

	/**
	 * check mobile visitor condition in frontend
	 *
	 * @param arr $options options of the condition
	 * @return bool true if can be displayed
	 */
	static function check_mobile( $options = array() ){

	    if ( ! isset( $options['operator'] ) ) {
			return true;
	    }

	    switch ( $options['operator'] ){
		    case 'is' :
			    if ( ! wp_is_mobile() ) { return false; }
			    break;
		    case 'is_not' :
			    if ( wp_is_mobile() ) { return false; }
			    break;
	    }

	    return true;
	}

	/**
	 * check mobile visitor condition in frontend
	 *
	 * @since 1.6.3
	 * @param arr $options options of the condition
	 * @return bool true if can be displayed
	 */
	static function check_logged_in( $options = array() ){

	    if ( ! isset( $options['operator'] ) ) {
			return true;
	    }

	    switch ( $options['operator'] ){
		    case 'is' :
			    if ( ! is_user_logged_in() ) { return false; }
			    break;
		    case 'is_not' :
			    if ( is_user_logged_in() ) { return false; }
			    break;
	    }

	    return true;
	}

	/**
	 * helper for check with strings
	 *
	 * @since 1.6.3
	 * @param str $string string that is going to be checked
	 * @return bool true if ad can be displayed
	 */
	static function helper_check_string( $string = '', $options = array() ){

		if ( ! isset( $options['operator'] ) || ! isset( $options['value'] ) || '' === $options['value'] ){
			return true;
		}

		$operator = $options['operator'];
		$value = $options['value'];

		// check length of url
		if( $operator !== 'regex' && $operator !== 'regex_not' ){
			if ( strlen( $value ) > strlen( $string ) ) {
				return false;
			}
		}

		// check the condition by mode and bool
		$condition = true;
		switch ( $operator ){
			// referrer contains string on any position
			case 'contain' :
				$condition = strpos( $string, $value ) !== false;
				break;

			// referrer does not contain string on any position
			case 'contain_not' :
				$condition = strpos( $string, $value ) === false;
				break;

			// referrer starts with the string
			case 'start' :
				// -TODO should allow a (locale aware) case insensitive comparision
				$condition = strpos( $string, $value ) === 0;
				break;
			// referrer does not start with the string
			case 'start_not' :
				// -TODO should allow a (locale aware) case insensitive comparision
				$condition = strpos( $string, $value ) !== 0;
				break;
			// referrer ends with the string
			case 'end' :
				// check if string is longer than referrer
				$strlen = strlen( $string );
				$vallen = strlen( $value );

				$condition = substr_compare( $string, $value, $strlen - $vallen, $vallen, true ) === 0;
				break;
			// referrer does not end with the string
			case 'end_not' :
				// check if string is longer than referrer
				$strlen = strlen( $string );
				$vallen = strlen( $value );

				$condition = substr_compare( $string, $value, $strlen - $vallen, $vallen, true ) !== 0;
				break;

			// referrer is equal to the string
			case 'match' :
				// -TODO should allow a (locale aware) case insensitive comparision
				// strings do match, but should not or not match but should
				$condition = $value === $string;
				break;
			// referrer is not equal to the string
			case 'match_not' :
				// -TODO should allow a (locale aware) case insensitive comparision
				// strings do match, but should not or not match but should
				$condition = $value !== $string;
				break;
			// string is a regular expression
			case 'regex' :
				// check regular expression first
				if( @preg_match( $value, null ) === false ){
					Advanced_Ads::log( "Advanced Ads: regular expression '$value' in visitor condition is broken." );
				} else {
					$condition = preg_match( $value, $string );
				}
				break;
			// string is not a regular expression
			case 'regex_not' :
				if( @preg_match( $value, null ) === false ){
					Advanced_Ads::log( "Advanced Ads: regular expression '$value' in visitor condition is broken." );
				} else {
					! $condition = preg_match( $value, $string );
				}
				break;
		}

		return $condition;
	}
}

