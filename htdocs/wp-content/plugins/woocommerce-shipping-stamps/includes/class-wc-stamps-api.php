<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * WC_Stamps_API class
 *
 * Used to interact with the Stamps API
 */
class WC_Stamps_API {

	private static $client        = false;
	private static $authenticator = false;
	private static $logger        = false;

	/**
	 * Get rate name
	 * @param  string $type
	 * @return string
	 */
	public static function get_rate_type_name( $type ) {
		switch ( $type ) {
			case 'US-FC' :
				return 'First-Class Mail';
			break;
			case 'US-MM' :
				return 'Media Mail';
			break;
			case 'US-PP' :
				return 'Parcel Post';
			break;
			case 'US-PM' :
				return 'Priority Mail';
			break;
			case 'US-XM' :
				return 'Priority Mail Express';
			break;
			case 'US-EMI' :
				return 'Priority Mail Express International';
			break;
			case 'US-PMI' :
				return 'Priority Mail International';
			break;
			case 'US-FCI' :
				return 'First Class Mail International';
			break;
			case 'US-CM' :
				return 'Critical Mail';
			break;
			case 'US-PS' :
				return 'Parcel Select';
			break;
			case 'US-LM' :
				return 'Library Mail';
			break;
		}
	}

	/**
	 * Get addon name
	 * @param  string $type
	 * @return string
	 */
	public static function get_addon_type_name( $type ) {
		switch ( $type ) {
			case 'SC-A-HP' :
				return __( 'Stamps.com hidden postage', 'woocommerce-shipping-stamps' );
			break;
			case 'SC-A-INS' :
				return __( 'Stamps.com insurance', 'woocommerce-shipping-stamps' );
			break;
			case 'SC-A-INSRM' :
				return __( 'Stamps.com insurance for registered mail', 'woocommerce-shipping-stamps' );
			break;
			case 'US-A-CM' :
				return __( 'Certified mail', 'woocommerce-shipping-stamps' );
			break;
			case 'US-A-COD' :
				return __( 'Collect on delivery', 'woocommerce-shipping-stamps' );
			break;
			case 'US-A-DC' :
				return __( 'Delivery confirmation', 'woocommerce-shipping-stamps' );
			break;
			case 'US-A-ESH' :
				return __( 'Express sunday/holiday guaranteed', 'woocommerce-shipping-stamps' );
			break;
			case 'US-A-INS' :
				return __( 'USPS insurance', 'woocommerce-shipping-stamps' );
			break;
			case 'US-A-NDW' :
				return __( 'No delivery on saturdays', 'woocommerce-shipping-stamps' );
			break;
			case 'US-A-RD' :
				return __( 'Restricted delivery', 'woocommerce-shipping-stamps' );
			break;
			case 'US-A-REG' :
				return __( 'Registered mail', 'woocommerce-shipping-stamps' );
			break;
			case 'US-A-RR' :
				return __( 'Return reciept requested', 'woocommerce-shipping-stamps' );
			break;
			case 'US-A-RRM' :
				return __( 'Return reciept for merchandise', 'woocommerce-shipping-stamps' );
			break;
			case 'US-A-SC' :
				return __( 'Signature confirmation', 'woocommerce-shipping-stamps' );
			break;
			case 'US-A-SH' :
				return __( 'Fragile', 'woocommerce-shipping-stamps' );
			break;
			case 'US-A-PR' :
				return __( 'Perishable', 'woocommerce-shipping-stamps' );
			break;
			case 'US-A-WDS' :
				return __( 'Waive delivery signature', 'woocommerce-shipping-stamps' );
			break;
			case 'US-A-SR' :
				return __( 'Signature required', 'woocommerce-shipping-stamps' );
			break;
			case 'US-A-NDW' :
				return __( 'Do not deliver on saturday', 'woocommerce-shipping-stamps' );
			break;
			case 'US-A-ESH' :
				return __( 'Sunday/holiday guaranteed', 'woocommerce-shipping-stamps' );
			break;
			case 'US-A-NND' :
				return __( 'Notice of non-delivery', 'woocommerce-shipping-stamps' );
			break;
			case 'US-A-RRE' :
				return __( 'Electronic return reciept', 'woocommerce-shipping-stamps' );
			break;
			case 'US-A-LANS' :
				return __( 'Live animal no surcharge', 'woocommerce-shipping-stamps' );
			break;
			case 'US-A-LAWS' :
				return __( 'Live animal with surcharge', 'woocommerce-shipping-stamps' );
			break;
			case 'US-A-HM' :
				return __( 'Hazardous materials', 'woocommerce-shipping-stamps' );
			break;
			case 'US-A-CR' :
				return __( 'Cremated remains', 'woocommerce-shipping-stamps' );
			break;
			case 'US-A-1030' :
				return __( 'Deliver priority mail express by 10:30am', 'woocommerce-shipping-stamps' );
			break;
			case 'US-A-ASR' :
				return __( 'Adult signature required', 'woocommerce-shipping-stamps' );
			break;
			case 'US-A-ASRD' :
				return __( 'Adult signature restricted delivery', 'woocommerce-shipping-stamps' );
			break;
		}
	}

	/**
	 * Get SOAP client for Stamps service
	 * @return SoapClient|WP_Error
	 */
	public static function get_client() {
		if ( self::$client ) {
			return self::$client;
		} else {
			return self::$client = new SoapClient( plugin_dir_path( dirname( __FILE__ ) ) . 'includes/wsdl/' . WC_STAMPS_INTEGRATION_WSDL_FILE, array( 'trace' => 1 ) );
		}
	}

	/**
	 * Logging
	 * @param  string $message
	 */
	public static function log( $message ) {
		if ( ! self::$logger ) {
			self::$logger = new WC_Logger();
		}

		self::$logger->add( 'stamps', $message );
	}

	/**
	 * Make an API request
	 * @param  string $endpoint
	 * @param  array $request
	 * @return array|WP_Error response on success
	 */
	public static function do_request( $endpoint, $request = array(), $retry = false ) {
		$response = array();

		@ini_set( "soap.wsdl_cache_enabled", 0 );

		try {
			if ( empty( $request['Authenticator'] ) ) {
				$request['Authenticator'] = self::get_authenticator();
			}

			if ( 'yes' === get_option( 'wc_settings_stamps_logging' ) ) {
				self::log( "Endpoint {$endpoint} Request: " . print_r( $request, true ) );
			}

			$client   = self::get_client();
			$response = $client->$endpoint( $request );

			if ( 'yes' === get_option( 'wc_settings_stamps_logging' ) ) {
				self::log( "Endpoint {$endpoint} Response: " . print_r( $response, true ) );
			}

			self::update_authenticator( $response );
			self::update_balance( $response );
			return $response;
		} catch( SoapFault $e ) {
			// Try again if authenticator is bad
			if ( ! $retry && isset( $e->detail->sdcerror ) && ( strstr( $e->detail->sdcerror, '0x002b0201' ) || strstr( $e->detail->sdcerror, '0x002b0202' ) || strstr( $e->detail->sdcerror, '0x002b0203' ) || strstr( $e->detail->sdcerror, '0x002b0204' ) ) ) {
				self::$authenticator      = false;
				$request['Authenticator'] = false;
				delete_transient( 'stamps_authenticator' );
				return self::do_request( $endpoint, $request, true );
			}
			if ( 'yes' === get_option( 'wc_settings_stamps_logging' ) ) {
				self::log( "Endpoint {$endpoint} SoapFault: " . $e->faultstring );
			}
			return new WP_Error( $e->faultcode, $e->faultstring );
		}
	}

	/**
	 * Authenticate a user
	 * @return string|WP_Error
	 */
	public static function authenticate() {
		$response = wp_remote_post( WC_STAMPS_INTEGRATION_AUTH_ENDPOINT, array(
			'method'      => 'POST',
			'timeout'     => 10,
			'httpversion' => '1.1',
			'user-agent'  => 'WooCommerce/' . WC_VERSION . '; ' . get_bloginfo( 'url' ),
			'body'        => array(
				'username' => get_option( 'wc_settings_stamps_username' ),
				'password' => get_option( 'wc_settings_stamps_password' )
			)
		) );
		if ( ! is_wp_error( $response ) && ! empty( $response['body'] ) && ! strstr( $response['body'], 'error' ) ) {
			self::$authenticator = trim( $response['body'], '"' );
			set_transient( 'stamps_authenticator', self::$authenticator );
			return self::$authenticator;
		} elseif ( is_wp_error( $response ) ) {
			self::log( "Error getting stamps_authenticator: " . $response->get_error_message() );
		} else {
			self::log( "Error getting stamps_authenticator: " . print_r( $response, true ) );
		}
		return new WP_Error( 'Unable to authenticate with Stamps.com' );
	}

	/**
	 * Get authenticator for requests
	 * @return string|bool
	 */
	public static function get_authenticator() {
		if ( self::$authenticator ) {
			return self::$authenticator;
		} elseif ( ( $authenticator = get_transient( 'stamps_authenticator' ) ) ) {
			return $authenticator;
		} else {
			$authenticator = self::authenticate();

			if ( ! is_wp_error( $authenticator ) && ! empty( $authenticator ) ) {
				return $authenticator;
			}
		}
		return false;
	}

	/**
	 * Update authenticator after a request
	 * @param string $authenticator
	 */
	public static function update_authenticator( $response ) {
		if ( isset( $response->Authenticator ) ) {
			self::$authenticator = $response->Authenticator;
			set_transient( 'stamps_authenticator', self::$authenticator );
		}
	}

	/**
	 * Update stamps balance
	 */
	public static function update_balance( $response ) {
		if ( isset( $response->PostageBalance ) ) {
			set_transient( 'wc_stamps_balance', $response->PostageBalance->AvailablePostage, DAY_IN_SECONDS );
			set_transient( 'wc_stamps_control_total', $response->PostageBalance->ControlTotal, DAY_IN_SECONDS );

			WC_Stamps_Balance::check_balance( $response->PostageBalance->AvailablePostage );
		}
	}

	/**
	 * Purchase postage on behalf of the user
	 * @param  int $amount Amount to top up. Must be an integer.
	 * @param  float $control_total Current balance
	 * @return array|WP_Error
	 */
	public static function purchase_postage( $amount, $control_total ) {
		$request = array(
			'PurchaseAmount' => absint( $amount ),
			'ControlTotal'   => number_format( $control_total, 2, '.', '' )
		);
		return self::do_request( 'PurchasePostage', $request );
	}

	/**
	 * Check purchase status.
	 * @param  string $transaction_id
	 * @return array|WP_Error
	 */
	public static function get_purchase_status( $transaction_id ) {
		$request = array(
			'TransactionID' => $transaction_id
		);
		return self::do_request( 'GetPurchaseStatus', $request );
	}

	/**
	 * Get account info
	 * @return string|WP_Error
	 */
	public static function get_account_info() {
		return self::do_request( 'getAccountInfo' );
	}

	/**
	 * Verify an address
	 * @param  WC_Order $order
	 * @return array
	 */
	public static function verify_address( $order ) {
		$address = array(
			'FullName' => $order->shipping_first_name . ' ' . $order->shipping_last_name,
			'Company'  => $order->shipping_company,
			'Address1' => $order->shipping_address_1,
			'Address2' => $order->shipping_address_2,
			'City'     => $order->shipping_city
		);

		if ( 'US' === $order->shipping_country ) {
			$address['State']   = $order->shipping_state;
			$address['ZIPCode'] = substr( $order->shipping_postcode, 0, 5 );
		} else {
			$address['Province']   = $order->shipping_state;
			$address['PostalCode'] = $order->shipping_postcode;
			$address['Country']    = $order->shipping_country;
		}

		$request = array(
			'Address' => $address
		);

		$result = self::do_request( 'CleanseAddress', $request );

		if ( is_wp_error( $result ) ) {
			return $result;
		}

		if ( $result->AddressMatch ) {
			// Return address in our own format
			return array(
				'matched'      => true,
				'matched_zip'  => true,
				'hash'         => $result->Address->CleanseHash,
				'overide_hash' => $result->Address->OverrideHash,
				'address'      => array(
					'full_name' => $result->Address->FullName,
					'company'   => $result->Address->Company,
					'address_1' => $result->Address->Address1,
					'address_2' => $result->Address->Address2,
					'city'      => $result->Address->City,
					'state'     => isset( $result->Address->Province ) ? $result->Address->Province : $result->Address->State,
					'postcode'  => isset( $result->Address->PostalCode ) ? $result->Address->PostalCode : $result->Address->ZIPCode,
					'country'   => isset( $result->Address->Country ) ? $result->Address->Country : '',
				)
			);
		}

		if ( 'US' == $order->shipping_country ) {
			// User can proceed anyway
			if ( $result->CityStateZipOK ) {
				return array(
					'matched'      => false,
					'matched_zip'  => true,
					'overide_hash' => $result->Address->OverrideHash,
				);
			}
		}

		return array(
			'matched'      => false,
			'matched_zip'  => false
		);
	}

	/**
	 * Get rates for a package
	 * @param  WC_Order $order
	 * @param  array $args
	 * @return array
	 */
	public static function get_rates( $order, $args ) {
		$request = array(
			'Rate'       => array(
				'FromZIPCode'   => get_option( 'wc_settings_stamps_zip' ),
				'ToZIPCode'     => $order->shipping_postcode,
				'ToCountry'     => $order->shipping_country,
				'WeightLb'      => floor( $args['weight'] ),
				'WeightOz'      => number_format( ( $args['weight'] - floor( $args['weight'] ) ) * 16, 2 ),
				'ShipDate'      => $args['date'],
				'InsuredValue'  => $args['value'],
				'CODValue'      => $args['value'],
				'DeclaredValue' => $args['value'],
				'Length'        => $args['length'],
				'Width'         => $args['width'],
				'Height'        => $args['height'],
				'PackageType'   => $args['type'],
				'PrintLayout'   => 'Normal4X6'
			)
		);

		$result = self::do_request( 'GetRates', $request );

		if ( is_wp_error( $result ) ) {
			self::log( "Error getting rates for request: " . print_r( $request, true ) . '. Response: ' . print_r( $result, true ) );
			return $result;
		}

		if ( empty( $result->Rates ) ) {
			return false;
		}

		if ( ! is_array( $result->Rates->Rate ) ) {
			$api_rates = array( $result->Rates->Rate );
		} else {
			$api_rates = $result->Rates->Rate;
		}

		foreach ( $api_rates as $rate ) {
			$rates[] = (object) array(
				'cost'          => $rate->Amount,
				'service'       => $rate->ServiceType,
				'package'       => $rate->PackageType,
				'name'          => self::get_rate_type_name( $rate->ServiceType ),
				'dim_weighting' => isset( $rate->DimWeighting ) ? $rate->DimWeighting : 0,
				'rate_object'   => $rate
			);
		}

		return $rates;
	}

	/**
	 * Get label for a rate
	 * @param  WC_Order $order
	 * @param  object $args
	 * @return array
	 */
	public static function get_label( $order, $args ) {
		$rate    = $args['rate'];
		$customs = $args['customs'];
		$tx_id   = uniqid( 'wc_' . $order->id . '_' );

		update_post_meta( $order->id, '_last_label_tx_id', $tx_id );

		$request = array(
			'IntegratorTxID' => $tx_id,
			'Rate'           => $rate,
			'SampleOnly'     => get_option( 'wc_settings_stamps_sample_only', "yes" ) === "yes",
			'ImageType'      => get_option( 'wc_settings_stamps_image_type', "Pdf" ),
			'PaperSize'      => get_option( 'wc_settings_stamps_paper_size', 'Default' ),
			'From'           => array(
				'FullName'    => get_option( 'wc_settings_stamps_full_name' ),
				'Company'     => get_option( 'wc_settings_stamps_company' ),
				'Address1'    => get_option( 'wc_settings_stamps_address_1' ),
				'Address2'    => get_option( 'wc_settings_stamps_address_2' ),
				'City'        => get_option( 'wc_settings_stamps_city' ),
				'State'       => get_option( 'wc_settings_stamps_state' ),
				'ZIPCode'     => get_option( 'wc_settings_stamps_zip' ),
				'Country'     => 'US',
				'PhoneNumber' => get_option( 'wc_settings_stamps_phone' ),
			)
		);

		if ( $customs ) {
			$request['Customs'] = $customs;
			$request['To'] = array(
				'FullName'    => $order->shipping_first_name . ' ' . $order->shipping_last_name,
				'Company'     => $order->shipping_company,
				'Address1'    => $order->shipping_address_1,
				'Address2'    => $order->shipping_address_2,
				'City'        => $order->shipping_city,
				'Province'    => $order->shipping_state,
				'PostalCode'  => $order->shipping_postcode,
				'Country'     => $order->shipping_country,
				'CleanseHash' => $order->_stamps_hash,
				'PhoneNumber' => $order->billing_phone,
			);
		} else {
			$request['To'] = array(
				'FullName'    => $order->shipping_first_name . ' ' . $order->shipping_last_name,
				'Company'     => $order->shipping_company,
				'Address1'    => $order->shipping_address_1,
				'Address2'    => $order->shipping_address_2,
				'City'        => $order->shipping_city,
				'State'       => $order->shipping_state,
				'ZIPCode'     => $order->shipping_postcode,
				'Country'     => $order->shipping_country,
				'CleanseHash' => $order->_stamps_hash
			);
		}

		$result   = self::do_request( 'CreateIndicium', $request );

		if ( is_wp_error( $result ) ) {
			return $result;
		}
		if ( empty( $result->URL ) ) {
			return new WP_Error( 'stamps-api', __( 'Cannot create a label for the package with the requested service.', 'woocommerce-shipping-stamps' ) );
		}

		$label_id = WC_Stamps_Labels::create_label( $order, $result );

		if ( is_wp_error( $label_id ) ) {
			return $label_id;
		}

		return new WC_Stamps_Label( $label_id );
	}

	/**
	 * Cancel a label
	 * @param  WC_Order $order
	 * @param  string $tx_id
	 * @return bool|WP_Error true on success
	 */
	public static function cancel_label( $order, $tx_id ) {
		$request = array(
			'StampsTxID' => $tx_id
		);
		$result  = self::do_request( 'CancelIndicium', $request );

		if ( is_wp_error( $result ) ) {
			return $result;
		}

		return true;
	}

	/**
	 * Get a URL to an account page
	 * @param  string $endpoint
	 * @return string|bool
	 */
	public static function get_url( $endpoint ) {
		$request = array(
			'URLType'            => $endpoint,
			'ApplicationContext' => ''
		);
		$result  = self::do_request( 'GetURL', $request );

		if ( is_wp_error( $result ) ) {
			return false;
		}

		return esc_url_raw( $result->URL );
	}
}
