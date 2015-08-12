<?php
/* 
Plugin Name: WooCommerce Sales Countdown
Plugin URI: http://thimpress.com
Description: Show countdown for sales products from WooCommerce plugin.
Version: 1.9
Author: Andy Ha (tu@thimpress.com)
Author URI: http://thimpress.com
Copyright 2007-2015 ThimPress.com. All rights reserved.
*/

/* Register hook */
@session_start();
if ( !class_exists( 'woosalescountdown' ) ) {
	require_once WP_PLUGIN_DIR . DIRECTORY_SEPARATOR . "woosalescountdown" . DIRECTORY_SEPARATOR . "widget.php";
}
define( 'WSCD_JS', WP_PLUGIN_URL . "/woosalescountdown/js/" );

class WSCD {

	public function __construct() {
		register_activation_hook( __FILE__, array( $this, 'ob_install' ) );
		register_deactivation_hook( __FILE__, array( $this, 'ob_uninstall' ) );

		/**
		 * add action of plugin
		 */
		add_shortcode( 'product_sale', array( $this, 'product_count_down_timer' ) );

		add_action( 'wp_enqueue_scripts', array( $this, 'obScriptInit' ) );
		add_action( 'woocommerce_before_single_product', array( $this, 'single_product' ) );
		add_action( 'woocommerce_before_shop_loop_item', array( $this, 'categories_product' ) );
		add_action( 'widgets_init', array( $this, 'woosalescountdown_register_widgets' ) );
		add_action( 'woocommerce_order_items_table', array( $this, 'woo_update_sale' ) );
		add_action( 'woocommerce_product_options_general_product_data', array( $this, 'woo_add_custom_general_fields' ) );
		add_action( 'woocommerce_process_product_meta', array( $this, 'woo_add_custom_general_fields_save' ) );
		add_action( 'woocommerce_product_after_variable_attributes', array( $this, 'woo_add_custom_product_variable' ) );
		add_action( 'woocommerce_save_product_variation', array( $this, 'woo_add_custom_product_variable_save' ) );
		add_action( 'woocommerce_variable_product_bulk_edit_actions', array( $this, 'bulk_variable_options' ) );
		add_action( 'wp_print_scripts', array( $this, 'woosalescountdown_style_load' ) );
		add_action( 'woocommerce_order_status_changed', array( $this, 'woo_sale_update_quantity_sale' ) );

		/*Setting*/
		add_action( 'plugins_loaded', array( $this, 'init_woosalecountdown' ) );

		/*Add setting in Product edit*/
		add_filter( 'product_type_options', array( $this, 'product_type_options' ) );
		add_filter( 'woocommerce_get_cart_item_from_session', array( $this, 'check_quantity_product_in_sale' ) );
		add_filter( 'plugin_action_links_woosalescountdown/woosalescountdown.php', array( $this, 'settings_link' ) );

	}


	/**
	 * Add bulk option in product variable
	 */
	function bulk_variable_options() {
		wp_enqueue_script( 'woosalescountdown-admin', WSCD_JS . "woosalescountdown-admin.js", array(), false, true );
		?>
		<optgroup label="<?php esc_attr_e( 'Sales Countdown', 'woosalescountdown' ); ?>">
			<option value="variable_sale_price_dates_from"><?php _e( 'Start date', 'woosalescountdown' ); ?></option>
			<option value="variable_sale_price_dates_to"><?php _e( 'End date', 'woosalescountdown' ); ?></option>
			<option value="_quantity_discount"><?php _e( 'Total product discount', 'woosalescountdown' ); ?></option>
			<option value="_quantity_sale"><?php _e( 'Quantity product sale', 'woosalescountdown' ); ?></option>
		</optgroup>
	<?php
	}

	/**
	 * add turn off countdown in each product
	 *
	 * @param $options
	 *
	 * @return array
	 */
	function product_type_options( $options ) {
		$product_type_options = array(
			'turn_off_countdown'  => array(
				'id'            => '_turn_off_countdown',
				'wrapper_class' => 'show_if_simple show_if_variable',
				'label'         => __( 'Hide all countdown and sale bar', 'woosalescountdown' ),
				'description'   => __( 'Hide countdown on this product.', 'woosalescountdown' ),
				'default'       => 'no'
			),
			'hide_only_countdown' => array(
				'id'            => '_hide_only_countdown',
				'wrapper_class' => 'show_if_simple show_if_variable',
				'label'         => __( 'Hide only countdown', 'woosalescountdown' ),
				'description'   => __( 'Hide only countdown on product sale. ', 'woosalescountdown' ),
				'default'       => 'no'
			),
			'hide_only_salebar'   => array(
				'id'            => '_hide_only_salebar',
				'wrapper_class' => 'show_if_simple show_if_variable',
				'label'         => __( 'Hide only Sale Bar', 'woosalescountdown' ),
				'description'   => __( 'Hide only Sale Bar on product sale.', 'woosalescountdown' ),
				'default'       => 'no'
			)
		);

		$options = array_merge( $options, $product_type_options );

		return $options;
	}

	/**
	 * Function check quantity in sale
	 */
	function check_quantity_product_in_sale( $data ) {
		if ( isset( $data['product_id'] ) ) {
			$_quantity_discount = get_post_meta( $data['product_id'], '_quantity_discount', true );
			$_quantity_sale     = get_post_meta( $data['product_id'], '_quantity_sale', true );
			$_quantity_sale     = $_quantity_sale ? $_quantity_sale : 0;
			if ( $_quantity_discount ) {
				$total = $_quantity_discount - $_quantity_sale;
				if ( $total > 0 && $total < $data['quantity'] ) {
					$data['quantity'] = $total;
				}
			}
		}

		return $data;
	}

	/**
	 * This is an extremely useful function if you need to execute any actions when your plugin is activated.
	 */
	function ob_install() {
		global $wp_version;
		If ( version_compare( $wp_version, "2.9", "<" ) ) {
			deactivate_plugins( basename( __FILE__ ) ); // Deactivate our plugin
			wp_die( "This plugin requires WordPress version 2.9 or higher." );
		}
	}

	/**
	 * This function is called when deactive.
	 */
	function ob_uninstall() {
		//do something
	}

	/**
	 * Start Short  code
	 */


	function product_count_down_timer( $atts, $content = null ) {
		extract(
			shortcode_atts( array(
				'id' => '' // set attribute default
			), $atts ) );
		add_filter( 'widget_text', 'shortcode_unautop' );
		add_filter( 'widget_text', 'do_shortcode' );
		$gmt_off = get_option( 'gmt_offset' ) ? get_option( 'gmt_offset' ) : 0;
		if ( $id ) {
			$localization = ', localization:{ days: "' . __( 'days', 'woosalescountdown' ) . '", hours: "' . __( 'hours', 'woosalescountdown' ) . '", minutes: "' . __( 'minutes', 'woosalescountdown' ) . '", seconds: "' . __( 'seconds', 'woosalescountdown' ) . '" }';
			$id           = explode( ',', $id );
			$show_message = get_option( 'ob_message_show', 1 );
			@$show_datetext = get_option( 'ob_datetext_show', 1 );
			$date_text = '';
			if ( !$show_datetext ) {
				$date_text = ', showText:0';
			}
			$shortcode = '<div class="woocommerce ob_shortcode"><ul class="shortcode_products products">';
			for ( $i = 0; $i < count( $id ); $i ++ ) {
				/*Init product Object base on WooCommerce*/
				$product             = get_product( $id[$i] );
				$_turn_off_countdown = get_post_meta( $product->id, "_turn_off_countdown", true );
				$hide_countdown      = get_post_meta( $product->id, "_hide_only_countdown", true );
				$hide_salebar        = get_post_meta( $product->id, "_hide_only_salebar", true );

				if ( trim( $product->product_type ) != 'variable' ) {
					if ( trim( $product->post->post_type ) != 'product' || trim( $product->post->post_status ) != 'publish' ) {
						continue;
					}
					$time_from = get_post_meta( $product->id, "_sale_price_dates_from", true );
					$time_end  = get_post_meta( $product->id, "_sale_price_dates_to", true );
					$_woosale_from_time     = get_post_meta( $product->id, "_woosale_from_time", true );
					$_woosale_to_time      = get_post_meta( $product->id, "_woosale_to_time", true );

					if ( !$time_from || !$time_end ) {
						continue;
					}
					if ( $_woosale_from_time != '' ){
						$time_from = $this->add_specified_time( $time_from, $_woosale_from_time );
						update_post_meta( $product->id, '_sale_price_dates_from', $time_from );
					}
					if ( $_woosale_to_time != '' ){
						$time_end = $this->add_specified_time( $time_end, $_woosale_to_time );
						update_post_meta( $product->id, '_sale_price_dates_to', $time_end );
					}
					$ob_product_show_title     = get_option( 'ob_product_show_title', 'yes' );
					$ob_product_show_price     = get_option( 'ob_product_show_price', 'yes' );
					$ob_product_show_image     = get_option( 'ob_product_show_image', 'yes' );
					$ob_product_show_addtocart = get_option( 'ob_product_show_addtocart', 'yes' );
					$ob_product_show_linkable  = get_option( 'ob_product_show_linkable', 'yes' );
					$ob_product_images_sizes   = get_option( 'ob_product_images_sizes', 'shop_thumbnail' );

					$shortcode .= '<li class="product product-' . $product->id . '">';
					if ( trim( $ob_product_show_linkable ) == 'yes' ) {
						$shortcode .= '<a href="' . get_permalink( $product->id ) . '">';
					}
					if ( trim( $ob_product_show_image ) == 'yes' ) {
						$shortcode .= $product->get_image( $ob_product_images_sizes );
					}

					if ( trim( $ob_product_show_title ) == 'yes' ) {
						$shortcode .= '<h3>' . $product->get_title() . '</h3>';
					}

					if ( trim( $ob_product_show_price ) == 'yes' ) {
						$shortcode .= '<span class="price">' . $product->get_price_html() . '</span>';
					}
					if ( trim( $ob_product_show_linkable ) == 'yes' ) {
						$shortcode .= '</a>';
					}
					/*Get time schedule*/
					$discount      = get_post_meta( $product->id, "_quantity_discount", true );
					$sale          = get_post_meta( $product->id, "_quantity_sale", true );
					$stock         = get_post_meta( $product->id, "_stock", true );
					$_manage_stock = get_post_meta( $product->id, "_manage_stock", true );
					if ( $_manage_stock ) {

						if ( trim( $_manage_stock ) == 'yes' ) {
							if ( $stock < 1 ) {
								$discount = 0;
							}
						}
					}
					if ( !$sale ) {
						$sale = 0;
					}
					$current_time = strtotime( current_time( "Y-m-d G:i:s" ) );
					if ( $current_time < $time_end && !$_turn_off_countdown ) {
						if ( $time_end && $time_from ) {
							$check_sale = 1;
							if ( $sale < $discount ) {
								$per_sale = intval( $sale / $discount * 100 );
							} else {
								$check_sale = 0;
							}
							$date_format = get_option( 'date_format', 'F j, Y' );
							if ( $current_time < $time_from ) {
								$time = $time_from; // - time();
								@$message = esc_html( __( get_option( 'ob_title_coming', 'Coming' ), 'woosalescountdown' ) );
								$time_text = '<h5 class="schedule_text">' . date( $date_format, $time_from ) . __( ' to ', 'woosalescountdown' ) . date( $date_format, $time_end ) . '</h5>';
							} else {
								$time = $time_end; // - time();
								@$message = esc_html( __( get_option( 'ob_title_sale', 'Sale' ), 'woosalescountdown' ) );
								$time_text = '<h5 class="schedule_text">' . date( $date_format, $time_from ) . __( ' to ', 'woosalescountdown' ) . date( $date_format, $time_end ) . '</h5>';
							}

							@$ob_time_show = get_option( 'ob_time_show' );
							switch ( $ob_time_show ) {
								case 1:
									if ( $hide_countdown != 'yes' ) {
										{
											$shortcode .= '	<div class="ob_wrapper">';
										}
										if ( $show_message ) {
											$shortcode .= '<h3>' . $message . '</h3>';
										}
										$shortcode .= '<div class="shortcode_product_' . $product->id . ' shortcode_product"></div>
                                </div>
                                ';
									}
									break;
								case 2:
									if ( $check_sale && $hide_salebar != 'yes' ) {
										$shortcode .= '	<div class="ob_wrapper">';
										if ( $show_message ) {
											$shortcode .= '<h3>' . $message . '</h3>';
										}
										$shortcode .= '	<div class="ob_discount"><div class="ob_sale" style="width:' . $per_sale . '%"></div></div>
										<span>' . $sale . '/' . $discount . __( ' sold', 'woosalescountdown' ) . '</span> </div>';
									}
									break;
								case 3:
									$shortcode .= "<div class=\"ob_wrapper\">" . $message . $time_text . "</div>";
									break;
								default:
									$shortcode .= '	<div class="ob_wrapper">';
									if ( $show_message ) {
										$shortcode .= '<h3>' . $message . '</h3>';
									}
									if ( $check_sale && $hide_salebar != 'yes' ) {
										$shortcode .= '<div class="ob_discount"><div class="ob_sale" style="width:' . $per_sale . '%"></div></div>
										<span>' . $sale . '/' . $discount . __( ' sold', 'woosalescountdown' ) . '</span>';

									}
									if ( $hide_countdown != 'yes' ) {
										$shortcode .= '<div class="shortcode_product_' . $product->id . ' shortcode_product"></div>';
									}
									$shortcode .= '</div>';
							}


							if ( !empty( $time_end ) && !empty( $time_from ) ) {

								$shortcode .= '
								<script type="text/javascript">
									jQuery(function () {
										jQuery(".shortcode_product_' . $product->id . '").mbComingsoon({ expiryDate: new Date(' . date( "Y", $time ) . ', ' . ( date( "m", $time ) - 1 ) . ', ' . date( "d", $time ) . ', ' . date( "G", $time ) . ',' . date( "i", $time ) . ', ' . date( "s", $time ) . '),speed: 500, gmt:' . $gmt_off . $date_text . $localization . ' });
									});
								</script>';

							}

						}
					}
					if ( trim( $ob_product_show_addtocart ) == 'yes' ) {
						$shortcode .= apply_filters( 'woocommerce_loop_add_to_cart_link',
							sprintf( '<a href="%s" rel="nofollow" data-product_id="%s" data-product_sku="%s" class="button add_to_cart_button %s product_type_%s">%s</a>',
								esc_url( $product->add_to_cart_url() ),
								esc_attr( $product->id ),
								esc_attr( $product->get_sku() ),
								$product->is_purchasable() ? 'add_to_cart_button' : '',
								esc_attr( $product->product_type ),
								esc_html( $product->add_to_cart_text() ) ),
							$product );
					}
					$shortcode .= '</li>';

				} elseif ( trim( $product->product_type ) == 'variable' ) {
					//Product variable
					if ( trim( $product->post->post_type ) != 'product' || trim( $product->post->post_status ) != 'publish' ) {
						continue;
					}
					$_product_variables         = $product->get_available_variations();
					$_product_variables_default = $product->get_variation_default_attributes();
					$product_var_id             = '';

					if ( count( $_product_variables ) ) {
						foreach ( $_product_variables as $_product_variable ) {

							if ( count( $_product_variables_default ) ) {
								if ( count( array_diff( $_product_variable['attributes'], $_product_variables_default ) ) < 1 ) {
									$product_var_id = $_product_variable['variation_id'];
									break;
								} else {
									$product_var_id = 0;
								}
							}
						}
					}
					if ( !$product_var_id ) {
						continue;
					}
					$time_from = get_post_meta( $product_var_id, "_sale_price_dates_from", true );
					$time_end  = get_post_meta( $product_var_id, "_sale_price_dates_to", true );
					$_woosale_from_time     = get_post_meta( $product_var_id, "_woosale_from_time", true );
					$_woosale_to_time      = get_post_meta( $product_var_id, "_woosale_to_time", true );

					if ( !$time_from || !$time_end ) {
						continue;
					}
					if ( $_woosale_from_time != '' ){
						$time_from = $this->add_specified_time( $time_from, $_woosale_from_time );
						update_post_meta( $product_var_id, '_sale_price_dates_from', $time_from );
					}
					if ( $_woosale_to_time != '' ){
						$time_end = $this->add_specified_time( $time_end, $_woosale_to_time );
						update_post_meta( $product_var_id, '_sale_price_dates_to', $time_end );
					}
					$ob_product_show_title     = get_option( 'ob_product_show_title', 'yes' );
					$ob_product_show_price     = get_option( 'ob_product_show_price', 'yes' );
					$ob_product_show_image     = get_option( 'ob_product_show_image', 'yes' );
					$ob_product_show_addtocart = get_option( 'ob_product_show_addtocart', 'yes' );
					$ob_product_show_linkable  = get_option( 'ob_product_show_linkable', 'yes' );
					$ob_product_images_sizes   = get_option( 'ob_product_images_sizes', 'shop_thumbnail' );

					$shortcode .= '<li class="product product-' . $product->id . '">';
					if ( trim( $ob_product_show_linkable ) == 'yes' ) {
						$shortcode .= '<a href="' . get_permalink( $product->id ) . '">';
					}
					if ( trim( $ob_product_show_image ) == 'yes' ) {
						$shortcode .= $product->get_image( $ob_product_images_sizes );
					}

					if ( trim( $ob_product_show_title ) == 'yes' ) {
						$shortcode .= '<h3>' . $product->get_title() . '</h3>';
					}

					if ( trim( $ob_product_show_price ) == 'yes' ) {
						$shortcode .= '<span class="price">' . $product->get_price_html() . '</span>';
					}
					if ( trim( $ob_product_show_linkable ) == 'yes' ) {
						$shortcode .= '</a>';
					}
					/*Get time schedule*/

					$current_time = strtotime( current_time( "Y-m-d G:i:s" ) );
					if ( $current_time < $time_end && !$_turn_off_countdown ) {
						if ( $time_end && $time_from ) {

							$date_format = get_option( 'date_format', 'F j, Y' );
							if ( $current_time < $time_from ) {
								$time = $time_from; // - time();
								@$message = __( get_option( 'ob_title_coming', 'Coming' ), 'woosalescountdown' );
								$time_text = '<h5 class="schedule_text">' . date( $date_format, $time_from ) . __( ' to ', 'woosalescountdown' ) . date( $date_format, $time_end ) . '</h5>';
							} else {
								$time = $time_end; // - time();
								@$message = __( get_option( 'ob_title_sale', 'Sale' ), 'woosalescountdown' );
								$time_text = '<h5 class="schedule_text">' . date( $date_format, $time_from ) . __( ' to ', 'woosalescountdown' ) . date( $date_format, $time_end ) . '</h5>';
							}

							@$ob_time_show = get_option( 'ob_time_show' );
							switch ( $ob_time_show ) {
								case 1:
									if ( $hide_countdown != 'yes' ) {
										$shortcode .= '	<div class="ob_wrapper">';
										if ( $show_message ) {
											$shortcode .= '<h3>' . $message . '</h3>';
										}
										$shortcode .= '<div class="shortcode_product_' . $product->id . ' shortcode_product"></div></div>';
									}
									break;
								case 2:

									if ( $show_message && $hide_salebar != 'yes' ) {
										$shortcode .= '	<div class="ob_wrapper">';
										$shortcode .= '<h3>' . $message . '</h3>';
										$shortcode .= '	</div>';
									}

									break;
								case 3:
									$shortcode .= "<div class=\"ob_wrapper\">" . $message . $time_text . "</div>";
									break;
								default:
									$shortcode .= '	<div class="ob_wrapper">';
									if ( $show_message && $hide_salebar != 'yes' ) {
										$shortcode .= '<h3>' . $message . '</h3>';
									}
									if ( $hide_countdown != 'yes' ) {
										$shortcode .= '<div class="shortcode_product_' . $product->id . ' shortcode_product"></div>';
									}
									$shortcode .= '</div>';
							}


							if ( !empty( $time_end ) && !empty( $time_from ) ) {

								$shortcode .= '
								<script type="text/javascript">
									jQuery(function () {
										jQuery(".shortcode_product_' . $product->id . '").mbComingsoon({ expiryDate: new Date(' . date( "Y", $time ) . ', ' . ( date( "m", $time ) - 1 ) . ', ' . date( "d", $time ) . ', ' . date( "G", $time ) . ',' . date( "i", $time ) . ', ' . date( "s", $time ) . '),speed: 500, gmt:' . $gmt_off . $date_text . $localization . ' });
									});
								</script>';

							}

						}
					}
					if ( trim( $ob_product_show_addtocart ) == 'yes' ) {
						$shortcode .= apply_filters( 'woocommerce_loop_add_to_cart_link',
							sprintf( '<a href="%s" rel="nofollow" data-product_id="%s" data-product_sku="%s" class="button add_to_cart_button %s product_type_%s">%s</a>',
								esc_url( $product->add_to_cart_url() ),
								esc_attr( $product->id ),
								esc_attr( $product->get_sku() ),
								$product->is_purchasable() ? 'add_to_cart_button' : '',
								esc_attr( $product->product_type ),
								esc_html( $product->add_to_cart_text() ) ),
							$product );
					}
					$shortcode .= '</li>';
				} else {
					if ( trim( $product->post->post_type ) != 'product' || trim( $product->post->post_status ) != 'publish' ) {
						continue;
					}

					$ob_product_show_title     = get_option( 'ob_product_show_title', 'yes' );
					$ob_product_show_price     = get_option( 'ob_product_show_price', 'yes' );
					$ob_product_show_image     = get_option( 'ob_product_show_image', 'yes' );
					$ob_product_show_addtocart = get_option( 'ob_product_show_addtocart', 'yes' );
					$ob_product_show_linkable  = get_option( 'ob_product_show_linkable', 'yes' );
					$ob_product_images_sizes   = get_option( 'ob_product_images_sizes', 'shop_thumbnail' );


					$shortcode .= '<li class="product product-' . $product->id . '">';
					if ( trim( $ob_product_show_linkable ) == 'yes' ) {
						$shortcode .= '<a href="' . get_permalink( $product->id ) . '">';
					}
					if ( trim( $ob_product_show_image ) == 'yes' ) {
						$shortcode .= $product->get_image( $ob_product_images_sizes );
					}

					if ( trim( $ob_product_show_title ) == 'yes' ) {
						$shortcode .= '<h3>' . $product->get_title() . '</h3>';
					}

					if ( trim( $ob_product_show_price ) == 'yes' ) {
						$shortcode .= '<span class="price">' . $product->get_price_html() . '</span>';
					}
					if ( trim( $ob_product_show_linkable ) == 'yes' ) {
						$shortcode .= '</a>';
					}

					if ( trim( $ob_product_show_addtocart ) == 'yes' ) {
						$shortcode .= apply_filters( 'woocommerce_loop_add_to_cart_link',
							sprintf( '<a href="%s" rel="nofollow" data-product_id="%s" data-product_sku="%s" class="button add_to_cart_button %s product_type_%s">%s</a>',
								esc_url( $product->add_to_cart_url() ),
								esc_attr( $product->id ),
								esc_attr( $product->get_sku() ),
								$product->is_purchasable() ? 'add_to_cart_button' : '',
								esc_attr( $product->product_type ),
								esc_html( $product->add_to_cart_text() ) ),
							$product );
					}
					$shortcode .= '</li>';
				}
			}
			$shortcode .= '</ul></div>';

			return $shortcode;
		} else {
			return __( "Shortcode not correct", 'woosalescountdown' );
		}

	}

	/**
	 * Function set up include javascript, css.
	 */
	function obScriptInit() {
		wp_enqueue_script( 'wscd-script-name', plugin_dir_url( '' ) . basename( dirname( __FILE__ ) ) . '/js/jquery.mb-comingsoon.min.js', array(), false, true );
		wp_enqueue_style( 'wscd-style-name', plugin_dir_url( '' ) . basename( dirname( __FILE__ ) ) . '/css/woosalescountdown.css' );
	}

	/**
	 * This function is used to add specified time into date.
	 */
	function add_specified_time( $date, $time ) {
		$date_str = date( 'Y-m-d', $date ) . ' ' . $time;
		$timestamp = strtotime( $date_str );

		return $timestamp;
	}

	/**
	 * This function is run when go to product detail
	 */
	function single_product() {
		global $wp_query;
		@$check_showon = get_option( 'ob_where_show' );
		@$show_message = get_option( 'ob_message_show', 1 );
		@$show_datetext = get_option( 'ob_datetext_show', 1 );
		$date_text    = $time_text = '';
		$localization = ', localization:{ days: "' . __( 'days', 'woosalescountdown' ) . '", hours: "' . __( 'hours', 'woosalescountdown' ) . '", minutes: "' . __( 'minutes', 'woosalescountdown' ) . '", seconds: "' . __( 'seconds', 'woosalescountdown' ) . '" }';
		if ( !$show_datetext ) {
			$date_text = ', showText:0';
		}
		if ( $check_showon == 1 ) {
			return;
		}
		if ( is_admin() || !$wp_query->post->ID ) {
			return;
		}
		$_product            = get_product( $wp_query->post->ID );
		$_turn_off_countdown = get_post_meta( $_product->id, '_turn_off_countdown', true );
		$hide_countdown      = get_post_meta( $_product->id, "_hide_only_countdown", true );
		$hide_salebar        = get_post_meta( $_product->id, "_hide_only_salebar", true );
		if ( $_turn_off_countdown == 'yes' ) {
			return;
		}
		$gmt_off = get_option( 'gmt_offset' ) ? get_option( 'gmt_offset' ) : 0;

		if ( trim( $_product->product_type ) != 'variable' ) {
			$time_from     = get_post_meta( $wp_query->post->ID, "_sale_price_dates_from", true );
			$time_end      = get_post_meta( $wp_query->post->ID, "_sale_price_dates_to", true );
			$_woosale_from_time     = get_post_meta( $wp_query->post->ID, "_woosale_from_time", true );
			$_woosale_to_time      = get_post_meta( $wp_query->post->ID, "_woosale_to_time", true );
			$discount      = get_post_meta( $wp_query->post->ID, "_quantity_discount", true );
			$sale          = get_post_meta( $wp_query->post->ID, "_quantity_sale", true );
			$stock         = get_post_meta( $wp_query->post->ID, "_stock", true );
			$_manage_stock = get_post_meta( $wp_query->post->ID, "_manage_stock", true );

			if ( $_manage_stock ) {
				if ( trim( $_manage_stock ) == 'yes' ) {
					if ( $stock < 1 ) {
						$discount = 0;
					}
				}
			}
			if ( !$sale ) {
				$sale = 0;
			}

			if ( empty( $time_end ) || empty( $time_from ) ) {
				return;
			}
			if ( $_woosale_from_time != '' ){
				$time_from = $this->add_specified_time( $time_from, $_woosale_from_time );
				update_post_meta( $wp_query->post->ID, '_sale_price_dates_from', $time_from );
			}
			if ( $_woosale_to_time != '' ){
				$time_end = $this->add_specified_time( $time_end, $_woosale_to_time );
				update_post_meta( $wp_query->post->ID, '_sale_price_dates_to', $time_end );
			}
			$message      = '';
			$current_time = strtotime( current_time( "Y-m-d G:i:s" ) );
			$date_format  = get_option( 'date_format', 'F j, Y' );
			if ( $current_time < $time_from ) {
				$time = $time_from; // - time();
				if ( $show_message ) {
					@$message = '<h3>' . esc_html( __( get_option( 'ob_title_coming', 'Coming' ), 'woosalescountdown' ) ) . '</h3>';
				}
				$time_text = '<h5 class="schedule_text">' . date( $date_format, $time_from ) . __( ' to ', 'woosalescountdown' ) . date( $date_format, $time_end ) . '</h5>';
			} else {
				$time = $time_end; // - time();
				if ( $show_message ) {
					@$message = '<h3>' . esc_html( __( get_option( 'ob_title_sale', 'Sale' ), 'woosalescountdown' ) ) . '</h3>';
				}
				$time_text = '<h5 class="schedule_text">' . date( $date_format, $time_from ) . __( ' to ', 'woosalescountdown' ) . date( $date_format, $time_end ) . '</h5>';
			}
			if ( $current_time > $time_end ) {
				$this->hideProduct( $wp_query->post->ID );

				return;
			}
			$check_sale = 1;
			if ( $sale < $discount ) {
				@$per_sale = intval( $sale / $discount * 100 );
			} else {
				$check_sale = 0;
			}
			@$ob_time_show = get_option( 'ob_time_show', '' );

			switch ( $ob_time_show ) {
				case 1:
					if ( $hide_countdown != 'yes' ) {
						$show = "<div class=\"ob_wrapper ob_product_detal\">" . $message . " <div class=\"myCounter widget_product_detail\"></div></div>";
					}
					break;
				case 2:
					if ( $check_sale && $hide_salebar != 'yes' ) {
						$show = "<div class=\"ob_wrapper ob_product_detal\">" . $message . "  <span>" . $sale . '/' . $discount . __( ' sold', 'woosalescountdown' ) . "</span> <div class=\"ob_discount\"><div class=\"ob_sale\" style=\"width:" . $per_sale . "%\"></div></div></div>";
					} else {
						$show = '';
					}

					break;
				case 3:
					$show = "<div class=\"ob_wrapper ob_product_detal\">" . $message . $time_text . "</div>";

					break;
				default:
					$show = "<div class=\"ob_wrapper ob_product_detal\">" . $message . "";
					if ( $hide_salebar != 'yes' ) {
						if ( $check_sale ) {
							$show .= "<div class=\"ob_discount\"><div class=\"ob_sale\" style=\"width:" . $per_sale . "%\"></div></div><span>" . $sale . '/' . $discount . __( ' sold', 'woosalescountdown' ) . "</span> ";
						}
					}
					if ( $hide_countdown != 'yes' ) {
						$show .= "  <div class=\"myCounter widget_product_detail\"></div>";
					}
					$show .= "</div>";
			}
			@$countdown_position = get_option( 'ob_detail_position', 0 );

			/*Clear Cache*/
			$this->clear_cache();

			if ( $show ) {
				switch ( $countdown_position ) {
					case 1:
						echo "<script type='text/javascript'>
						jQuery(document).ready(function(){
							jQuery('" . $show . "').insertAfter('.woocommerce-tabs');
						});
					</script>
					";
						break;
					case 2:
						echo "<script type='text/javascript'>
						jQuery(document).ready(function(){
							jQuery('div[itemprop=\"description\"]').each(function (index, value) {
								if (index == 0){
									jQuery('" . $show . "').insertBefore(jQuery(this));
								}
							});
						});
					</script>
					";
						break;
					case 3:
						echo "<script type='text/javascript'>
						jQuery(document).ready(function(){
							jQuery('div[itemprop=\"description\"]').each(function (index, value) {
								if (index == 0){
									jQuery('" . $show . "').insertAfter('div[itemprop=\"description\"]');
								}
							});
						});
					</script>
					";
						break;
					case
					4:
						echo "<script type='text/javascript'>
						jQuery(document).ready(function(){
							jQuery('" . $show . "').insertBefore('form.cart');
						});
					</script>
					";
						break;
					case 5:
						echo "<script type='text/javascript'>
						jQuery(document).ready(function(){
							jQuery('" . $show . "').insertAfter('form.cart');
						});
					</script>
					";
						break;
					default:
						echo "<script type='text/javascript'>
				jQuery(document).ready(function(){
					jQuery('" . $show . "').insertBefore('.woocommerce-tabs');
				});
			</script>
			";

				}
				echo "<script type='text/javascript'>
			jQuery(function () {
				jQuery('.myCounter').mbComingsoon({ expiryDate: new Date(" . date( "Y", $time ) . ", " . ( date( "m", $time ) - 1 ) . ", " . date( "d", $time ) . ", " . date( "G", $time ) . ", " . date( "i", $time ) . ", " . date( "s", $time ) . "), speed:500, gmt:" . $gmt_off . $date_text . $localization . " });
			});
			</script>";
			}
		} else {

			$_product_variables         = $_product->get_available_variations();
			$_check_bar_sale_variations = get_option( "ob_bar_sale_variations", 0 );
			if ( count( array_filter( $_product_variables ) ) > 0 ) {
				$_product_variables = array_filter( $_product_variables );
				$total_discount     = $total_sold = 0;
				if ( $_check_bar_sale_variations ) {

					foreach ( $_product_variables as $_product_variable ) {
						$wpml_id = get_post_meta( $_product_variable['variation_id'], '_wcml_duplicate_of_variation', true );
						if ( $wpml_id ) {
							$_product_variable['variation_id'] = $wpml_id;
						}
						$discount = get_post_meta( $_product_variable['variation_id'], "_quantity_discount", true );
						$sold     = get_post_meta( $_product_variable['variation_id'], "_quantity_sale", true );

						$total_discount += $discount;
						$total_sold += $sold;

					}
				}
				$this->clear_cache();
				foreach ( $_product_variables as $_product_variable ) {
					$wpml_id = get_post_meta( $_product_variable['variation_id'], '_wcml_duplicate_of_variation', true );

					$time_from = get_post_meta( $_product_variable['variation_id'], "_sale_price_dates_from", true );
					$time_end  = get_post_meta( $_product_variable['variation_id'], "_sale_price_dates_to", true );
					$_woosale_from_time     = get_post_meta( $_product_variable['variation_id'], "_woosale_from_time", true );
					$_woosale_to_time      = get_post_meta( $_product_variable['variation_id'], "_woosale_to_time", true );


					if ( $_check_bar_sale_variations ) {
						$discount = $total_discount;
						$sale     = $total_sold;
					} else {
						if ( $wpml_id ) {
							$discount = get_post_meta( $wpml_id, "_quantity_discount", true );
							$sale     = get_post_meta( $wpml_id, "_quantity_sale", true );

						} else {
							$discount = get_post_meta( $_product_variable['variation_id'], "_quantity_discount", true );
							$sale     = get_post_meta( $_product_variable['variation_id'], "_quantity_sale", true );
						}
					}
					if ( !$sale ) {
						$sale = 0;
					}

					if ( empty( $time_end ) || empty( $time_from ) ) {
						continue;
					}
					if ( $_woosale_from_time != '' ){
						$time_from = $this->add_specified_time( $time_from, $_woosale_from_time );
						update_post_meta( $_product_variable['variation_id'], '_sale_price_dates_from', $time_from );
					}
					if ( $_woosale_to_time != '' ){
						$time_end = $this->add_specified_time( $time_end, $_woosale_to_time );
						update_post_meta( $_product_variable['variation_id'], '_sale_price_dates_to', $time_end );
					}

					$message      = '';
					$current_time = strtotime( current_time( "Y-m-d G:i:s" ) );
					$date_format  = get_option( 'date_format', 'F j, Y' );

					if ( $current_time < $time_from ) {
						$time = $time_from; // - time();
						if ( $show_message ) {
							@$message = '<h3>' . esc_html( __( get_option( 'ob_title_coming', 'Coming' ), 'woosalescountdown' ) ) . '</h3>';
						}
						$time_text = '<h5 class="schedule_text">' . date( $date_format, $time_from ) . __( ' to ', 'woosalescountdown' ) . date( $date_format, $time_end ) . '</h5>';
					} else {
						$time = $time_end; // - time();
						if ( $show_message ) {
							@$message = '<h3>' . esc_html( __( get_option( 'ob_title_sale', 'Sale' ), 'woosalescountdown' ) ) . '</h3>';
						}
						$time_text = '<h5 class="schedule_text">' . date( $date_format, $time_from ) . __( ' to ', 'woosalescountdown' ) . date( $date_format, $time_end ) . '</h5>';
					}
					if ( $current_time > $time_end ) {
						$this->hideProduct( $_product_variable['variation_id'] );

						return;
					}
					$check_sale = 1;

					if ( $sale < $discount ) {
						@$per_sale = intval( $sale / $discount * 100 );
					} else {
						$check_sale = 0;
					}
					$k = 0;

					$values_default = $_product->get_variation_default_attributes();

					if ( count( $values_default ) ) {

						foreach ( $values_default as $key => $value_default ) {
							if ( $value_default != $_product_variable['attributes']['attribute_' . $key] ) {
								$k = 0;
								break;
							}
							$k = 1;
						}
					}

					if ( $k ) {
						$style = ' style="display:block;" ';
					} else {
						$style = ' style="display:none;" ';
					}
					@$ob_time_show = get_option( 'ob_time_show' );

					switch ( $ob_time_show ) {
						case 1:
							if ( $hide_countdown != 'yes' ) {
								$show = "<div " . $style . " class=\"ob_wrapper ob_product_avariable_detail ob_product_detail_" . $_product_variable['variation_id'] . "\">" . $message . "<div class=\"myCounter widget_product_detail\"></div></div>";
							}
							break;
						case 2:
							if ( $check_sale && $hide_salebar != 'yes' ) {
								$show = "<div " . $style . " class=\"ob_wrapper ob_product_avariable_detail ob_product_detail_" . $_product_variable['variation_id'] . "\">" . $message . " <span>" . $sale . '/' . $discount . __( ' sold', 'woosalescountdown' ) . "</span> <div class=\"ob_discount\"><div class=\"ob_sale\" style=\"width:" . $per_sale . "%\"></div></div></div>";
							} else {
								$show = '';
							}

							break;
						case 3:
							$show = "<div " . $style . " class=\"ob_wrapper ob_product_avariable_detail ob_product_detail_" . $_product_variable['variation_id'] . "\">" . $message . $time_text . "</div>";
							break;
						default:
							$show = "<div " . $style . " class=\"ob_wrapper ob_product_avariable_detail ob_product_detail_" . $_product_variable['variation_id'] . "\">" . $message;
							if ( $check_sale && $hide_salebar != 'yes' ) {
								$show .= "<div class=\"ob_discount\"><div class=\"ob_sale\" style=\"width:" . $per_sale . "%\"></div></div><span>" . $sale . '/' . $discount . __( ' sold', 'woosalescountdown' ) . "</span> ";
							}
							if ( $hide_countdown != 'yes' ) {
								$show .= "  <div class=\"myCounter widget_product_detail\"></div>";
							}
							$show .= "</div>";
					}

					@$countdown_position = get_option( 'ob_detail_position', 0 );
					/*Clear Cache*/

					switch ( $countdown_position ) {
						case 1:
							echo "<script type='text/javascript'>
							jQuery(document).ready(function(){
								jQuery('" . $show . "').insertAfter('.woocommerce-tabs');
							});
						</script>
						";
							break;
						case 2:
							echo "<script type='text/javascript'>
							jQuery(document).ready(function(){
								jQuery('div[itemprop=\"description\"]').each(function (index, value) {
									if (index == 0){
										jQuery('" . $show . "').insertBefore(jQuery(this));
									}
								});
							});
						</script>
						";
							break;
						case 3:
							echo "<script type='text/javascript'>
							jQuery(document).ready(function(){
								jQuery('div[itemprop=\"description\"]').each(function (index, value) {
									if (index == 0){
										jQuery('" . $show . "').insertAfter('div[itemprop=\"description\"]');
									}
								});
							});
						</script>
						";
							break;
						case 4:
							echo "<script type='text/javascript'>
						jQuery(document).ready(function(){
							jQuery('" . $show . "').insertBefore('div.variations_button');
						});
					</script>
					";
							break;
						case 5:
							echo "<script type='text/javascript'>
						jQuery(document).ready(function(){
							jQuery('" . $show . "').insertAfter('div.variations_button');
						});
					</script>
					";
							break;
						default:
							echo "<script type='text/javascript'>
							jQuery(document).ready(function(){
								jQuery('" . $show . "').insertBefore('.woocommerce-tabs');
							});
						</script>
						";

					}
					echo "<script type='text/javascript'>
					jQuery(function () {
						jQuery('.ob_product_detail_" . $_product_variable['variation_id'] . " .myCounter').mbComingsoon({ expiryDate: new Date(" . date( "Y", $time ) . ", " . ( date( "m", $time ) - 1 ) . ", " . date( "d", $time ) . ", " . date( "G", $time ) . ", " . date( "i", $time ) . ", " . date( "s", $time ) . "), speed:500,gmt: " . $gmt_off . $date_text . $localization . " });
					});
					</script>";
				}
				echo "<script type='text/javascript'>
					jQuery(window).load(function(){
						jQuery('input[name=\"variation_id\"]').change(function(){
							var pv_id = parseInt(jQuery(this).val());
							jQuery('.ob_product_avariable_detail').hide();
							if(pv_id){
								jQuery('.ob_product_detail_'+pv_id).show();
							}
							//console.log(pv_id);
						});
					});
				</script>";
			}
		}
	}

	/**
	 * This function is run when go to categories what show all products
	 */
	function categories_product() {
		global $post;
		@$check_showon = get_option( 'ob_where_show' );
		@$show_message = get_option( 'ob_message_show', 1 );
		@$show_datetext = get_option( 'ob_datetext_show', 1 );
		$date_text    = '';
		$localization = ', localization:{ days: "' . __( 'days', 'woosalescountdown' ) . '", hours: "' . __( 'hours', 'woosalescountdown' ) . '", minutes: "' . __( 'minutes', 'woosalescountdown' ) . '", seconds: "' . __( 'seconds', 'woosalescountdown' ) . '" }';
		if ( !$show_datetext ) {
			$date_text = ', showText:0';
		}
		if ( $check_showon == 2 ) {
			return;
		}
		if ( is_admin() || !$post->ID ) {
			return;
		}

		$product_id = $post->ID;

		$wpml_id = get_post_meta( $product_id, '_wcml_duplicate_of_variation', true );
		$old_id  = $product_id;
		if ( $wpml_id ) {
			$product_id = $wpml_id;
		}
		$product             = get_product( $product_id );
		$_turn_off_countdown = get_post_meta( $product_id, '_turn_off_countdown', true );
		$hide_countdown      = get_post_meta( $product_id, "_hide_only_countdown", true );
		$hide_salebar        = get_post_meta( $product_id, "_hide_only_salebar", true );
		if ( $_turn_off_countdown == 'yes' ) {
			return;
		}
		$gmt_off = get_option( 'gmt_offset' ) ? get_option( 'gmt_offset' ) : 0;
		if ( $product->product_type == 'variable' ) {
			$_product_variables     = $product->get_available_variations();
			$check_default          = get_post_meta( $product->id, '_default_attributes', true );
			$ob_bar_sale_variations = get_option( 'ob_bar_sale_variations', 0 );
			$total_discount         = $total_sold = 0;
			if ( $check_default ) {
				$key_select_default = key( $check_default );
				$check_default      = $check_default[$key_select_default];
				foreach ( $_product_variables as $_product_variable ) {
					$key_attr  = str_replace( 'attribute_', '', key( $_product_variable['attributes'] ) );
					$data_attr = $_product_variable['attributes']['attribute_' . $key_attr];
					if ( $data_attr == $check_default ) {
						$product_id = $_product_variable['variation_id'];
					}
					if ( $ob_bar_sale_variations ) {
						$discount = get_post_meta( $_product_variable['variation_id'], "_quantity_discount", true );
						$sold     = get_post_meta( $_product_variable['variation_id'], "_quantity_sale", true );

						$total_discount += $discount;
						$total_sold += $sold;
					}
				}
			}
			if ( $product_id == $post->ID ) {
				return;
			}
			$time_from = get_post_meta( $product_id, "_sale_price_dates_from", true );
			$time_end  = get_post_meta( $product_id, "_sale_price_dates_to", true );
			$_woosale_from_time     = get_post_meta( $product_id, "_woosale_from_time", true );
			$_woosale_to_time      = get_post_meta( $product_id, "_woosale_to_time", true );
			if ( $ob_bar_sale_variations ) {
				$discount = $total_discount;
				$sale     = $total_sold;
			} else {
				$discount = get_post_meta( $product_id, "_quantity_discount", true );
				$sale     = get_post_meta( $product_id, "_quantity_sale", true );
			}
			$stock         = get_post_meta( $product_id, "_stock", true );
			$_manage_stock = get_post_meta( $product_id, "_manage_stock", true );

		} else {
			$time_from     = get_post_meta( $product_id, "_sale_price_dates_from", true );
			$time_end      = get_post_meta( $product_id, "_sale_price_dates_to", true );
			$_woosale_from_time     = get_post_meta( $product_id, "_woosale_from_time", true );
			$_woosale_to_time      = get_post_meta( $product_id, "_woosale_to_time", true );
			$discount      = get_post_meta( $product_id, "_quantity_discount", true );
			$sale          = get_post_meta( $product_id, "_quantity_sale", true );
			$stock         = get_post_meta( $product_id, "_stock", true );
			$_manage_stock = get_post_meta( $product_id, "_manage_stock", true );
		}

		if ( count( $_manage_stock ) > 0 ) {
			if ( trim( $_manage_stock ) == 'yes' ) {
				if ( $stock < 1 ) {
					$discount = 0;
				}
			}
		}
		if ( !$sale ) {
			$sale = 0;
		}

		if ( empty( $time_end ) || empty( $time_from ) ) {
			return;
		}
		if ( $_woosale_from_time != '' ){
			$time_from = $this->add_specified_time( $time_from, $_woosale_from_time );
			update_post_meta( $product_id, '_sale_price_dates_from', $time_from );
		}
		if ( $_woosale_to_time != '' ){
			$time_end = $this->add_specified_time( $time_end, $_woosale_to_time );
			update_post_meta( $product_id, '_sale_price_dates_to', $time_end );
		}
		$message      = '';
		$current_time = strtotime( current_time( "Y-m-d G:i:s" ) );
		$date_format  = get_option( 'date_format', 'F j, Y' );
		if ( $current_time < $time_from ) {
			$time = $time_from; // - time();
			if ( $show_message ) {
				@$message = '<h3>' . esc_html( __( get_option( 'ob_title_coming', 'Coming' ), 'woosalescountdown' ) ) . '</h3>';
			}
			$time_text = '<h5 class="schedule_text">' . date( $date_format, $time_from ) . __( ' to ', 'woosalescountdown' ) . date( $date_format, $time_end ) . '</h5>';
		} else {
			$time = $time_end; // - time();
			if ( $show_message ) {
				@$message = '<h3>' . esc_html( __( get_option( 'ob_title_sale', 'Sale' ), 'woosalescountdown' ) ) . '</h3>';
			}
			$time_text = '<h5 class="schedule_text">' . date( $date_format, $time_from ) . __( ' to ', 'woosalescountdown' ) . date( $date_format, $time_end ) . '</h5>';
		}
		if ( $current_time > $time_end ) {
			$this->hideProduct( $post->ID );

			return;
		}
		$check_sale = 1;
		if ( $sale < $discount ) {
			@$per_sale = intval( $sale / $discount * 100 );
		} else {
			$check_sale = 0;
		}
		@$ob_time_show = get_option( 'ob_time_show' );
		switch ( $ob_time_show ) {
			case 1:
				if ( $hide_countdown != 'yes' ) {
					$show = "<div class=\"ob_wrapper ob_categories\">" . $message . " <div class=\"myCounter_pro" . $product_id . " widget_product_loop\"></div></div>";
				}
				break;
			case 2:
				if ( $check_sale && $hide_salebar != 'yes' ) {
					$show = "<div class=\"ob_wrapper ob_categories\">" . $message . " <div class=\"ob_discount\"><div class=\"ob_sale\" style=\"width:" . $per_sale . "%\"></div></div><span>" . $sale . '/' . $discount . __( ' sold', 'woosalescountdown' ) . "</span></div>";
				}
				break;
			case 3:
				$show = "<div class=\"ob_wrapper ob_categories\">" . $message . $time_text . "</div>";
				break;
			default:
				$show = "<div class=\"ob_wrapper ob_categories\">" . $message;
				if ( $check_sale && $hide_salebar != 'yes' ) {
					$show .= " <div class=\"ob_discount\"><div class=\"ob_sale\" style=\"width:" . $per_sale . "%\"></div></div><span>" . $sale . '/' . $discount . __( ' sold', 'woosalescountdown' ) . "</span>";
				}
				if ( $hide_countdown != 'yes' ) {
					$show .= " <div class=\"myCounter_pro" . $product_id . " widget_product_loop\"></div>";
				}
				$show .= "</div>";
		}
		if ( $time_end && $time_from ) {
			@$countdown_position = get_option( 'ob_category_position', 0 );
			/*Clear Cache*/
			$this->clear_cache();
			switch ( $countdown_position ) {
				case 1:
					echo "
						<script type='text/javascript'>
							jQuery(document).ready(function(){
							    if(jQuery('li.post-" . $old_id . " .ob_categories').length < 1){
									jQuery('" . $show . "').insertBefore('li.post-" . $old_id . " h3');
								}
							});
						</script>
						";
					break;
				case 2:
					echo "
					<script type='text/javascript'>
						jQuery(document).ready(function(){
						if(jQuery('li.post-" . $old_id . " .ob_categories').length < 1){
							jQuery('" . $show . "').insertBefore('li.post-" . $old_id . " a.add_to_cart_button');
							}
						});
					</script>
					";
					break;
				case 3:
					echo "
					<script type='text/javascript'>
						jQuery(document).ready(function(){
						if(jQuery('li.post-" . $old_id . " .ob_categories').length < 1){
							jQuery('" . $show . "').insertAfter('li.post-" . $old_id . " a.add_to_cart_button');
							}
						});
					</script>
					";
					break;
				default :
					echo "
					<script type='text/javascript'>
						jQuery(document).ready(function(){
						if(jQuery('li.post-" . $old_id . " .ob_categories').length < 1){
							jQuery('" . $show . "').insertBefore('li.post-" . $old_id . " span.price');
							}
						});
					</script>
					";
			}
			echo "<script type='text/javascript'>
		jQuery(function () {
			jQuery('.myCounter_pro" . $product_id . "').mbComingsoon({ expiryDate: new Date(" . date( "Y", $time ) . ", " . ( date( "m", $time ) - 1 ) . ", " . date( "d", $time ) . ", " . date( "G", $time ) . ", " . date( "i", $time ) . ", " . date( "s", $time ) . "), speed:500,gmt: " . $gmt_off . $date_text . $localization . " });
		});
		</script>";
		}
	}

	/**
	 * Register widget
	 */
	function woosalescountdown_register_widgets() {
		register_widget( 'woosalescountdown' );
	}

	/**
	 * Update sale
	 */
	function woo_update_sale( $order ) {
		$order_id = $order->id;

		@$array_id = $_SESSION['ids'];
		if ( count( $array_id ) > 0 ) {
			if ( in_array( $order_id, $array_id ) ) {
				return;
			} else {
				$array_id[]      = $order_id;
				$_SESSION['ids'] = $array_id;
			}
		}
		$order = new WC_Order( $order_id );

		$items = $order->get_items();

		foreach ( $items as $item ) {
			if ( $item['variation_id'] == 0 ) {
				$time_from = get_post_meta( $item['product_id'], "_sale_price_dates_from", true );
				$time_end  = get_post_meta( $item['product_id'], "_sale_price_dates_to", true );
				$_woosale_from_time     = get_post_meta( $item['product_id'], "_woosale_from_time", true );

				if ( ! $time_from || ! $time_end ) {
					continue;
				}
				if ( $_woosale_from_time != '' ){
					$time_from = $this->add_specified_time( $time_from, $_woosale_from_time );
				}
				$current_time = strtotime( current_time( "Y-m-d G:i:s" ) );
				if ( $time_from > $current_time ) {
					continue;
				}

				if ( isset( $_SESSION['order_id'] ) ) {
					if ( $_SESSION['order_id'][ $order_id ] ) {
						return;
					}
				}

				$woocommerce_quantity_sale     = get_post_meta( $item['product_id'], '_quantity_sale', true );
				$woocommerce_quantity_discount = get_post_meta( $item['product_id'], '_quantity_discount', true );
				$woocommerce_quantity_sale += $item['qty'];
				if ( $order->post_status == 'wc-completed' ) {
					update_post_meta( $item['product_id'], '_quantity_sale', esc_attr( $woocommerce_quantity_sale ) );
				}
				if ( $woocommerce_quantity_discount <= $woocommerce_quantity_sale && $woocommerce_quantity_discount ) {

					$woocommerce_regular_price = get_post_meta( $item['product_id'], '_regular_price', true );
					delete_post_meta( $item['product_id'], '_sale_price' );
					delete_post_meta( $item['product_id'], '_sale_price_dates_from' );
					delete_post_meta( $item['product_id'], '_sale_price_dates_to' );
					delete_post_meta( $item['product_id'], '_woosale_from_time', '' );
					delete_post_meta( $item['product_id'], '_woosale_to_time', '' );
					update_post_meta( $item['product_id'], '_price', esc_attr( $woocommerce_regular_price ) );
				}
			} else {
				$time_from = get_post_meta( $item['variation_id'], "_sale_price_dates_from", true );
				$time_end  = get_post_meta( $item['variation_id'], "_sale_price_dates_to", true );
				$_woosale_from_time     = get_post_meta( $item['variation_id'], "_woosale_from_time", true );

				if ( ! $time_from || ! $time_end ) {
					continue;
				}
				if ( $_woosale_from_time != '' ){
					$time_from = $this->add_specified_time( $time_from, $_woosale_from_time );
				}
				$current_time = strtotime( current_time( "Y-m-d G:i:s" ) );
				if ( $time_from > $current_time ) {
					continue;
				}
				if ( isset( $_SESSION['order_id'] ) ) {
					if ( $_SESSION['order_id'][ $order_id ] ) {
						return;
					}
				}

				$woocommerce_quantity_sale     = get_post_meta( $item['variation_id'], '_quantity_sale', true );
				$woocommerce_quantity_discount = get_post_meta( $item['variation_id'], '_quantity_discount', true );
				$woocommerce_quantity_sale += $item['qty'];
				if( $order->post_status == 'wc-completed' ) {
					update_post_meta( $item['variation_id'], '_quantity_sale', esc_attr( $woocommerce_quantity_sale ) );
				}
				if ( $woocommerce_quantity_discount <= $woocommerce_quantity_sale && $woocommerce_quantity_discount ) {

					$woocommerce_regular_price = get_post_meta( $item['variation_id'], '_regular_price', true );
					delete_post_meta( $item['variation_id'], '_sale_price' );
					delete_post_meta( $item['variation_id'], '_sale_price_dates_from' );
					delete_post_meta( $item['variation_id'], '_sale_price_dates_to' );
					delete_post_meta( $item['variation_id'], '_woosale_from_time', '' );
					delete_post_meta( $item['variation_id'], '_woosale_to_time', '' );
					update_post_meta( $item['variation_id'], '_price', esc_attr( $woocommerce_regular_price ) );
				}
			}
		}
		$_SESSION['order_id'][$order_id] = 1;
	}

	/**
	 * Function save Custom field
	 *
	 * @param $post_id
	 */

	function woo_add_custom_general_fields_save( $post_id ) {
		if ( isset( $_POST['product-type'] )) {
			if ( $_POST['product-type'] != 'simple' && $_POST['product-type'] != 'variable' ) {
				return;
			}
		} else {
			return;
		}
		// Text Field
		if ( isset( $_POST['_quantity_sale'] ) ) {
			$woocommerce_quantity_sale = $_POST['_quantity_sale'];
			if ( !empty( $woocommerce_quantity_sale ) ) {
				update_post_meta( $post_id, '_quantity_sale', esc_attr( $woocommerce_quantity_sale ) );
			} else {
				update_post_meta( $post_id, '_quantity_sale', 0 );
			}
		}
		if ( isset( $_POST['_quantity_discount'] ) ) {
			$woocommerce_quantity_discount = $_POST['_quantity_discount'];
			if ( !$woocommerce_quantity_discount ) {
				$woocommerce_quantity_discount = $_POST['_stock'];
			}
			if ( !empty( $woocommerce_quantity_discount ) ) {
				update_post_meta( $post_id, '_quantity_discount', esc_attr( $woocommerce_quantity_discount ) );
			} else {
				update_post_meta( $post_id, '_quantity_discount', 0 );
			}
		}
		if ( isset( $_POST['_turn_off_countdown'] ) ) {
			$_turn_off_countdown = $_POST['_turn_off_countdown'];
			if ( !empty( $_turn_off_countdown ) ) {
				update_post_meta( $post_id, '_turn_off_countdown', 'yes' );
			} else {
				update_post_meta( $post_id, '_turn_off_countdown', '' );
			}
		} else {
			update_post_meta( $post_id, '_turn_off_countdown', '' );
		}
		if ( isset( $_POST['_hide_only_countdown'] ) ) {
			$hide_only_countdown = $_POST['_hide_only_countdown'];
			if ( !empty( $hide_only_countdown ) ) {
				update_post_meta( $post_id, '_hide_only_countdown', 'yes' );
			} else {
				update_post_meta( $post_id, '_hide_only_countdown', '' );
			}
		} else {
			update_post_meta( $post_id, '_hide_only_countdown', '' );
		}
		if ( isset( $_POST['_hide_only_salebar'] ) ) {
			$hide_only_salebar = $_POST['_hide_only_salebar'];
			if ( !empty( $hide_only_salebar ) ) {
				update_post_meta( $post_id, '_hide_only_salebar', 'yes' );
			} else {
				update_post_meta( $post_id, '_hide_only_salebar', '' );
			}
		} else{
			update_post_meta( $post_id, '_hide_only_salebar', '' );
		}
		/* Save specified time */
		if ( isset( $_POST['_woosale_from_time'] ) ) {
			$_woosale_from_time = $_POST['_woosale_from_time'];
			if ( !empty( $_woosale_from_time ) ) {
				update_post_meta( $post_id, '_woosale_from_time', esc_attr( $_woosale_from_time ) );
			} else {
				update_post_meta( $post_id, '_woosale_from_time', '' );
			}
		}
		if ( isset( $_POST['_woosale_to_time'] ) ) {
			$_woosale_to_time = $_POST['_woosale_to_time'];
			if ( !empty( $_woosale_to_time ) ) {
				update_post_meta( $post_id, '_woosale_to_time', esc_attr( $_woosale_to_time ) );
			} else {
				update_post_meta( $post_id, '_woosale_to_time', '' );
			}
		}
	}

	/*Custom field*/
	function woo_add_custom_general_fields() {

		global $post;
		$_product = get_product( $post->ID );
		if ( trim( $_product->product_type ) != 'simple' ) {
			return;
		}
		$_woosale_from_time = get_post_meta( $post->ID, '_woosale_from_time', true );
		$_woosale_to_time     = get_post_meta( $post->ID, '_woosale_to_time', true );
		// Display Custom Field Value
		echo '<div class="options_group">';
		if ( get_option('use_specified_time') ) {
			woocommerce_wp_text_input(
				array(
					'id'          => '_woosale_from_time',
					'label'       => __( 'Sale start time', 'woosalescountdown' ),
					'placeholder' => '',
					'desc_tip'    => 'true',
					'description' => __( 'Enter the Specified start time of your sales with format <strong>H:i:s</strong>.', 'woosalescountdown' ),
					'value'       => $_woosale_from_time ? $_woosale_from_time : ''
				)
			);
			woocommerce_wp_text_input(
				array(
					'id'          => '_woosale_to_time',
					'label'       => __( 'Sale end time', 'woosalescountdown' ),
					'placeholder' => '',
					'desc_tip'    => 'true',
					'description' => __( 'Enter the Specified end time of your sales with format <strong>H:i:s</strong>.', 'woosalescountdown' ),
					'value'       => $_woosale_to_time ? $_woosale_to_time : ''
				)
			);
		}

		woocommerce_wp_text_input(
			array(
				'id'          => '_quantity_discount',
				'label'       => __( 'Total product discount', 'woosalescountdown' ),
				'placeholder' => '',
				'desc_tip'    => 'true',
				'description' => __( 'Enter the TOTAL Sale product.', 'woosalescountdown' ),
				'default'     => '0'
			)
		);
		woocommerce_wp_text_input(
			array(
				'id'          => '_quantity_sale',
				'label'       => __( 'Quantity product sale', 'woosalescountdown' ),
				'placeholder' => '',
				'desc_tip'    => 'true',
				'description' => __( 'Quantity sale of this product is sold.', 'woosalescountdown' )
			)
		);
		echo '</div>';
	}
	/*Custom field in product variable*/
	//$loop = 0;
	function woo_add_custom_product_variable( $loop ) {

		global $woocommerce, $post;

		@$_product = get_product( $post->ID );

		// Get variations
		$args               = array(
			'post_type'   => 'product_variation',
			'post_status' => array( 'private', 'publish' ),
			'numberposts' => - 1,
			'orderby'     => 'menu_order',
			'order'       => 'asc',
			'post_parent' => @$post->ID
		);
		$variations         = get_posts( $args );
		$_quantity_discount = get_post_meta( $variations[$loop]->ID, '_quantity_discount', true );
		$_quantity_sale     = get_post_meta( $variations[$loop]->ID, '_quantity_sale', true );
		$_woosale_from_time = get_post_meta( $variations[$loop]->ID, '_woosale_from_time', true );
		$_woosale_to_time     = get_post_meta( $variations[$loop]->ID, '_woosale_to_time', true );

		if ( $_product ) {
			if ( trim( @$_product->product_type ) != 'variable' ) {
				return;
			}
		}
		// Display Custom Field Value
		echo '<tr class="options_group"><td>';
		if ( get_option('use_specified_time') ) {
			woocommerce_wp_text_input(
				array(
					'id'          => '_woosale_from_time[' . $loop . ']',
					'label'       => __( 'Sale start time', 'woosalescountdown' ),
					'placeholder' => '',
					'desc_tip'    => 'true',
					'description' => __( 'Enter the Specified start time of your sales with format <strong>H:i:s</strong>.', 'woosalescountdown' ),
					'value'       => $_woosale_from_time ? $_woosale_from_time : ''
				)
			);
			woocommerce_wp_text_input(
				array(
					'id'          => '_woosale_to_time[' . $loop . ']',
					'label'       => __( 'Sale end time', 'woosalescountdown' ),
					'placeholder' => '',
					'desc_tip'    => 'true',
					'description' => __( 'Enter the Specified end time of your sales with format <strong>H:i:s</strong>.', 'woosalescountdown' ),
					'value'       => $_woosale_to_time ? $_woosale_to_time : ''
				)
			);
		}
		// Text Field
		@woocommerce_wp_text_input(
			array(
				'id'          => '_quantity_discount[' . $loop . ']',
				'label'       => __( 'Total product discount', 'woosalescountdown' ),
				'placeholder' => '',
				'desc_tip'    => 'true',
				'description' => __( 'Enter the TOTAL Sale product.', 'woosalescountdown' ),
				'value'       => $_quantity_discount ? $_quantity_discount : 0
			)
		);
		@woocommerce_wp_text_input(
			array(
				'id'          => '_quantity_sale[' . $loop . ']',
				'label'       => __( 'Quantity product sale', 'woosalescountdown' ),
				'placeholder' => '',
				'desc_tip'    => 'true',
				'description' => __( 'Quantity sale of this product is sold.', 'woosalescountdown' ),
				'value'       => $_quantity_sale ? $_quantity_sale : 0
			)
		);
		echo '</td></tr>';
		//$loop++;
	}

	/**
	 * Function save custom field in product variable
	 *
	 * @param $variation_id POST ID
	 */

	function woo_add_custom_product_variable_save( $variation_id ) {
		if ( isset( $_POST['variable_post_id'] ) ) {
			// Text Field
			$variable_post_ids = $_POST['variable_post_id'];
		} else {
			$variable_post_ids = array();
		}
		$max_loop = count( $variable_post_ids );
		if ( isset( $_POST['_quantity_sale'] ) ) {
			$woocommerce_quantity_sale = $_POST['_quantity_sale'];
		} else {
			$woocommerce_quantity_sale = array();
		}
		if ( isset( $_POST['_quantity_discount'] ) ) {
			$woocommerce_quantity_discount = $_POST['_quantity_discount'];
		} else {
			$woocommerce_quantity_discount = array();
		}
		if ( isset( $_POST['_woosale_from_time'] ) ) {
			$_woosale_from_time = $_POST['_woosale_from_time'];
		} else {
			$_woosale_from_time = array();
		}
		if ( isset( $_POST['_woosale_to_time'] ) ) {
			$_woosale_to_time = $_POST['_woosale_to_time'];
		} else {
			$_woosale_to_time = array();
		}

		for ( $i = 0; $i < $max_loop; $i ++ ) {
			if ( $variation_id == $variable_post_ids[$i] ) {
				if ( !empty( $woocommerce_quantity_sale[$i] ) ) {
					update_post_meta( $variation_id, '_quantity_sale', esc_attr( $woocommerce_quantity_sale[$i] ) );
				} else {
					update_post_meta( $variation_id, '_quantity_sale', 0 );
				}

				if ( !$woocommerce_quantity_discount ) {
					$woocommerce_quantity_discount = $_POST['_stock'];
				}
				if ( !empty( $woocommerce_quantity_discount[$i] ) ) {
					update_post_meta( $variation_id, '_quantity_discount', esc_attr( $woocommerce_quantity_discount[$i] ) );
				} else {
					update_post_meta( $variation_id, '_quantity_discount', 0 );
				}
				/* Save specified time */
				if ( !empty( $_woosale_from_time[$i] ) ) {
					update_post_meta( $variation_id, '_woosale_from_time', esc_attr( $_woosale_from_time[$i] ) );
				} else {
					update_post_meta( $variation_id, '_woosale_from_time', '' );
				}
				if ( !empty( $_woosale_to_time[$i] ) ) {
					update_post_meta( $variation_id, '_woosale_to_time', esc_attr( $_woosale_to_time[$i] ) );
				} else {
					update_post_meta( $variation_id, '_woosale_to_time', '' );
				}
			}
		}

	}

	/**
	 * Update quantity of sales when changing the status of orders to completed.
	 */
	function woo_sale_update_quantity_sale( $order_id ) {
		$current_order = new WC_Order( $order_id );
		$order_stt = $current_order->get_status();
		if( $order_stt == 'completed' ){
			return $this->woo_update_sale( $current_order );
		}
	}

	/**
	 * Load and custom CSS from setting
	 */
	function woosalescountdown_style_load() {
		@$colors = array_map( 'esc_attr', (array) get_option( 'woocommerce_frontend_css_colors' ) );

		// Defaults
		if ( empty( $colors['primary'] ) ) {
			$colors['primary'] = '#ad74a2';
		}
		if ( empty( $colors['secondary'] ) ) {
			$colors['secondary'] = '#f7f6f7';
		}


		@$ob_use_color = get_option( 'ob_use_color' );
		if ( $ob_use_color ) {
			@$background_color = get_option( 'ob_background_color' );
			@$time_color = get_option( 'ob_time_color' );
			@$ob_bar_color = get_option( 'ob_bar_color' );
			@$ob_bg_bar_color = get_option( 'ob_bg_bar_color' );
		} else {
			@$background_color = $colors['secondary'];
			@$time_color = $colors['primary'];
			@$ob_bar_color = $time_color;
			@$ob_bg_bar_color = $background_color;
		}
		echo "<style type='text/css'>
			.counter-block .counter .number{background-color:$background_color;color:$time_color;}
			.ob_discount{background-color:$ob_bg_bar_color;}
			.ob_sale{background-color:$ob_bar_color}
		</style>";
	}

	/**
	 * Init when plugin load
	 */
	function init_woosalecountdown() {
		load_plugin_textdomain( 'woosalescountdown' );
		$this->load_plugin_textdomain();
		require_once( 'woosalescountdown-admin.php' );
		$init = new woosalescountdownadmin();
	}

	/*Load Language*/
	function replace_woosalescountdown_default_language_files() {


		$locale = apply_filters( 'plugin_locale', get_locale(), 'woosalescountdown' );

		return WP_PLUGIN_DIR . "/woosalescountdown/languages/woosalescountdown-$locale.mo";

	}

	/**
	 * Function load language
	 */
	public function load_plugin_textdomain() {
		$locale = apply_filters( 'plugin_locale', get_locale(), 'woosalescountdown' );

		// Admin Locale
		if ( is_admin() ) {
			load_textdomain( 'woosalescountdown', WP_PLUGIN_DIR . "/woosalescountdown/languages/woosalescountdown-$locale.mo" );
		}

		// Global + Frontend Locale
		load_textdomain( 'woosalescountdown', WP_PLUGIN_DIR . "/woosalescountdown/languages/woosalescountdown-$locale.mo" );
		load_plugin_textdomain( 'woosalescountdown', false, WP_PLUGIN_DIR . "/woosalescountdown/languages/" );
	}

	/*
	 * Function Setting link in plugin manager
	 */
	function settings_link( $links ) {
		$settings_link = '<a href="admin.php?page=wc-settings&tab=woosalescountdown" title="' . __( 'Settings', 'woosalescountdown' ) . '">' . __( 'Settings', 'woosalescountdown' ) . '</a>';
		array_unshift( $links, $settings_link );

		return $links;
	}

	/**
	 * Function hide product
	 *
	 * @param $id product ID
	 *
	 * @return bool
	 */
	protected function hideProduct( $id ) {
		if ( get_option( 'ob_hide_product', 0 ) ) {
			$my_post = array(
				'ID'          => $id,
				'post_status' => 'trash'
			);
			wp_update_post( $my_post );
		}

		if ( get_option( 'ob_remove_sale_price', 0 ) ) {
			update_post_meta( $id, '_sale_price', '' );
			update_post_meta( $id, '_price', get_post_meta( $id, '_regular_price', true ) );
		}
		update_post_meta( $id, '_sale_price_dates_from', '' );
		update_post_meta( $id, '_sale_price_dates_to', '' );
		update_post_meta( $id, '_woosale_from_time', '' );
		update_post_meta( $id, '_woosale_to_time', '' );

		return true;
	}

	/**
	 * Clear cache. Support Supper Cache
	 */
	protected function clear_cache() {
		include_once( ABSPATH . 'wp-admin/includes/plugin.php' );
//		print_r(get_option('active_plugins'));
		/*Clear Cache with Supper Cache*/
		if ( is_plugin_active( 'wp-super-cache/wp-cache.php' ) ) {
			global $file_prefix;
			wp_cache_clean_cache( $file_prefix );
		}

	}
}


$woosalescountdown = new WSCD();
?>