<?php

/*
Class Name: Widget Countdown timer

Author: Andy Ha (tu@wpbriz.com)
Author URI: http://wpbriz.com
Copyright 2007-2014 wpBriz.com. All rights reserved.
*/

class woosalescountdown extends WP_Widget {
	function woosalescountdown() {
		$widget_ops = array( 'classname'   => 'woosalescountdown_class',
							 'description' => 'Show products with timer countdown' );
		$this->WP_Widget( 'woosalescountdown', 'WooCommerce Sales Countdown', $widget_ops );
	}

	function form( $instance ) {
		$arg = array(
			'post_type'   => 'product',
			'post_status' => 'publish'
		);

		$the_query = new WP_Query( $arg );
		$defaults  = array( 'product_id' => '',
							'title'      => 'Product Sale'
		);
		@$instance = wp_parse_args( (array) $instance, $defaults );
		$title  = $instance['title'];
		$option = '';

		// The Loop
		if ( $the_query->have_posts() ) {
			while ( $the_query->have_posts() ) {
				$the_query->the_post();
				$checked = '';
				if ( @in_array( $the_query->post->ID, $instance['product_id'] ) ) {
					$checked = "selected='selected'";
				}
				$option .= "<option value='{$the_query->post->ID}' {$checked}>{$the_query->post->post_title}</option>";
			}
		}
		// Reset Post Data
		wp_reset_postdata();

		?>

		<p><label><?php echo __( 'Title', 'woosalescountdown' ); ?></label>
			<input class="widefat" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>" />
		</p>

		<p><label><?php echo __( 'Please select products', 'woosalescountdown' ); ?></label>
			<select name="<?php echo $this->get_field_name( 'product_id' ); ?>[]" multiple="true">
				<?php echo $option; ?>
			</select>
		</p>
	<?php
	}

	function update( $new_instance, $old_instance ) {
		$instance               = $old_instance;
		$instance['product_id'] = esc_sql( $new_instance['product_id'] );
		$instance['title']      = sanitize_text_field( $new_instance['title'] );

		return $instance;
	}

	function widget( $args, $instance ) {
		extract( $args );
		//echo $before_widget;
		if ( $instance['title'] ) {
			echo "<h3>{$instance['title']}</h3>";
		} ?>
		<div class="woocommerce ob_widget">
			<ul class="products">
				<?php
				@$show_datetext = get_option( 'ob_datetext_show', 1 );
				$date_text    = $message = $time_text = '';
				$localization = ', localization:{ days: "' . __( 'days', 'woosalescountdown' ) . '", hours: "' . __( 'hours', 'woosalescountdown' ) . '", minutes: "' . __( 'minutes', 'woosalescountdown' ) . '", seconds: "' . __( 'seconds', 'woosalescountdown' ) . '" }';
				if ( !$show_datetext ) {
					$date_text = ', showText:0';
				}
				if ( count( $instance['product_id'] ) > 0 ) {
					for ( $i = 0; $i < count( $instance['product_id'] ); $i ++ ) {

						$product             = get_product( $instance['product_id'][$i] );
						$_turn_off_countdown = get_post_meta( $product->id, '_turn_off_countdown', true );
						$hide_countdown      = get_post_meta( $product->id, "_hide_only_countdown", true );
						$hide_salebar        = get_post_meta( $product->id, "_hide_only_salebar", true );

						if ( trim( $product->product_type ) != 'variable' ) {
							$time_from = get_post_meta( $product->id, "_sale_price_dates_from", true );
							$time_end  = get_post_meta( $product->id, "_sale_price_dates_to", true );

							$ob_product_show_title     = get_option( 'ob_product_show_title', 'yes' );
							$ob_product_show_price     = get_option( 'ob_product_show_price', 'yes' );
							$ob_product_show_image     = get_option( 'ob_product_show_image', 'yes' );
							$ob_product_show_addtocart = get_option( 'ob_product_show_addtocart', 'yes' );
							$ob_product_show_linkable  = get_option( 'ob_product_show_linkable', 'yes' );
							$ob_product_images_sizes   = get_option( 'ob_product_images_sizes', 'shop_thumbnail' );
							$show_message              = get_option( 'ob_message_show', 1 );
							?>
							<li class="product">
								<?php if (trim( $ob_product_show_linkable ) == 'yes'){ ?>
								<a href="<?php echo esc_url( get_permalink( $product->id ) ); ?>">
									<?php } ?>
									<?php if ( trim( $ob_product_show_image ) == 'yes' ) { ?>
										<?php echo $product->get_image( $ob_product_images_sizes ); ?>
									<?php } ?>
									<?php if ( trim( $ob_product_show_title ) == 'yes' ) { ?>
										<h3><?php echo $product->get_title() ?></h3>
									<?php } ?>
									<?php if ( trim( $ob_product_show_price ) == 'yes' ) {
										echo $product->get_price_html();
									} ?>
									<?php if (trim( $ob_product_show_linkable ) == 'yes'){ ?>
								</a>
							<?php } ?>
								<?php
								if ( $time_from && $time_end && !$_turn_off_countdown ) {
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
									$date_format  = get_option( 'date_format', 'F j, Y' );
									if ( $current_time < $time_from ) {
										$time = $time_from; // - time();

										@$message = esc_html( __( get_option( 'ob_title_coming', 'Coming' ), 'woosalescountdown' ) );

										$time_text = '<h5 class="schedule_text">' . date( $date_format, $time_from ) . __( ' to ', 'woosalescountdown' ) . date( $date_format, $time_end ) . '</h5>';
									} else {
										$time = $time_end; // - time();

										@$message = esc_html( __( get_option( 'ob_title_sale', 'Sale' ), 'woosalescountdown' ) );

										$time_text = '<h5 class="schedule_text">' . date( $date_format, $time_from ) . __( ' to ', 'woosalescountdown' ) . date( $date_format, $time_end ) . '</h5>';
									}
									if ( $current_time > $time_end ) {
										continue;
									}
									$check_sale = 1;
									if ( $sale < $discount ) {
										@$per_sale = intval( $sale / $discount * 100 );
									} else {
										$check_sale = 0;
									}

									@$ob_time_show = get_option( 'ob_time_show' );
									/*Clear Cache*/

									switch ( $ob_time_show ) {
										case 1:
											if ( $hide_countdown != 'yes' ) {
												?>
												<div class="ob_wrapper">
													<?php if ( $show_message ) { ?>
														<h3><?php echo $message ?></h3>
													<?php } ?>
												</div>

												<div class="widget_myCounter_<?php echo $product->id; ?> widget_product"></div>
											<?php
											}
											break;
										case 2:
											if ( $check_sale && $hide_salebar != 'yes' ) {
												?>
												<div class="ob_wrapper">
													<?php if ( $show_message ) { ?>
														<h3><?php echo $message ?></h3>
													<?php } ?>

													<div class="ob_discount">
														<div class="ob_sale" style="width:<?php echo $per_sale ?>%"></div>
													</div>
													<span><?php echo $sale . ' ' . __( 'sold', 'woosalescountdown' ) ?></span>

												</div>
											<?php
											}
											break;
										case 3:
											?>
											<div class="ob_wrapper">
												<?php if ( $show_message ) { ?>
													<h3><?php echo $message ?></h3>

												<?php } ?>
												<?php echo $time_text; ?>
											</div>


											<?php break;
										default:
											?>
											<div class="ob_wrapper">
												<?php if ( $show_message ) { ?>
													<h3><?php echo $message ?></h3>
												<?php } ?>
												<?php if ( $check_sale && $hide_salebar != 'yes' ) { ?>
													<div class="ob_discount">
														<div class="ob_sale" style="width:<?php echo $per_sale ?>%"></div>
													</div>
													<span><?php echo $sale . ' ' . __( 'sold', 'woosalescountdown' ) ?></span>
												<?php } ?>
											</div>
											<?php if ( $hide_countdown != 'yes' ) { ?>
											<div class="widget_myCounter_<?php echo $product->id; ?> widget_product"></div>
										<?php } ?>

										<?php
									} ?>
									<?php echo "<script type='text/javascript'>
									jQuery(function () {
										jQuery('.widget_myCounter_" . $product->id . "').mbComingsoon({ expiryDate: new Date(" . date( "Y", $time ) . ", " . ( date( "m", $time ) - 1 ) . ", " . date( "d", $time ) . "), speed:500, gmt:" . get_option( 'gmt_offset' ) . $date_text . $localization . " });
									});
									</script>";
								}
								if ( trim( $ob_product_show_addtocart ) == 'yes' ) {
									echo apply_filters( 'woocommerce_loop_add_to_cart_link',
										sprintf( '<a href="%s" rel="nofollow" data-product_id="%s" data-product_sku="%s" class="button add_to_cart_button %s product_type_%s">%s</a>',
											esc_url( $product->add_to_cart_url() ),
											esc_attr( $product->id ),
											esc_attr( $product->get_sku() ),
											$product->is_purchasable() ? 'add_to_cart_button' : '',
											esc_attr( $product->product_type ),
											esc_html( $product->add_to_cart_text() ) ),
										$product );
								}
								?>
							</li>
						<?php
						} elseif ( $product->product_type == 'variable' ) {

							$_product_variables        = $product->get_available_variations();
							$check_default             = get_post_meta( $product->id, '_default_attributes', true );
							$ob_bar_sale_variations    = get_option( 'ob_bar_sale_variations', 0 );
							$ob_product_show_title     = get_option( 'ob_product_show_title', 'yes' );
							$ob_product_show_price     = get_option( 'ob_product_show_price', 'yes' );
							$ob_product_show_image     = get_option( 'ob_product_show_image', 'yes' );
							$ob_product_show_addtocart = get_option( 'ob_product_show_addtocart', 'yes' );
							$ob_product_show_linkable  = get_option( 'ob_product_show_linkable', 'yes' );
							$ob_product_images_sizes   = get_option( 'ob_product_images_sizes', 'shop_thumbnail' );
							$show_message              = get_option( 'ob_message_show', 1 );

							$total_discount = $total_sold = 0;
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
							if ( $product_id == $product->id ) {
								continue;
							}

							$time_from = get_post_meta( $product_id, "_sale_price_dates_from", true );
							$time_end  = get_post_meta( $product_id, "_sale_price_dates_to", true );
							if ( $ob_bar_sale_variations ) {
								$discount = $total_discount;
								$sale     = $total_sold;
							} else {
								$discount = get_post_meta( $product_id, "_quantity_discount", true );
								$sale     = get_post_meta( $product_id, "_quantity_sale", true );
							}
							$stock         = get_post_meta( $product_id, "_stock", true );
							$_manage_stock = get_post_meta( $product_id, "_manage_stock", true );


							?>
							<li class="product">
								<?php if (trim( $ob_product_show_linkable ) == 'yes'){ ?>
								<a href="<?php echo esc_url( get_permalink( $product->id ) ); ?>">
									<?php } ?>
									<?php if ( trim( $ob_product_show_image ) == 'yes' ) { ?>
										<?php echo $product->get_image( $ob_product_images_sizes ); ?>
									<?php } ?>
									<?php if ( trim( $ob_product_show_title ) == 'yes' ) { ?>
										<h3><?php echo $product->get_title() ?></h3>
									<?php } ?>
									<?php if ( trim( $ob_product_show_price ) == 'yes' ) {
										echo $product->get_price_html();
									} ?>
									<?php if (trim( $ob_product_show_linkable ) == 'yes'){ ?>
								</a>
							<?php } ?>
								<?php
								if ( $time_from && $time_end && !$_turn_off_countdown ) {
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
									$date_format  = get_option( 'date_format', 'F j, Y' );
									if ( $current_time < $time_from ) {
										$time = $time_from; // - time();

										@$message = esc_html( __( get_option( 'ob_title_coming', 'Coming' ), 'woosalescountdown' ) );

										$time_text = '<h5 class="schedule_text">' . date( $date_format, $time_from ) . __( ' to ', 'woosalescountdown' ) . date( $date_format, $time_end ) . '</h5>';
									} else {
										$time = $time_end; // - time();

										@$message = esc_html( __( get_option( 'ob_title_sale', 'Sale' ), 'woosalescountdown' ) );

										$time_text = '<h5 class="schedule_text">' . date( $date_format, $time_from ) . __( ' to ', 'woosalescountdown' ) . date( $date_format, $time_end ) . '</h5>';
									}
									if ( $current_time > $time_end ) {
										continue;
									}
									$check_sale = 1;
									if ( $sale < $discount ) {
										@$per_sale = intval( $sale / $discount * 100 );
									} else {
										$check_sale = 0;
									}

									@$ob_time_show = get_option( 'ob_time_show' );
									/*Clear Cache*/

									switch ( $ob_time_show ) {
										case 1:
											if ( $hide_countdown != 'yes' ) {
												?>
												<div class="ob_wrapper">
													<?php if ( $show_message ) { ?>
														<h3><?php echo $message ?></h3>
													<?php } ?>
												</div>

												<div class="widget_myCounter_<?php echo $product->id; ?> widget_product"></div>
											<?php
											}
											break;
										case 2:
											if ( $check_sale && $hide_salebar != 'yes' ) {
												?>
												<div class="ob_wrapper">
													<?php if ( $show_message ) { ?>
														<h3><?php echo $message ?></h3>
													<?php } ?>

													<div class="ob_discount">
														<div class="ob_sale" style="width:<?php echo $per_sale ?>%"></div>
													</div>
													<span><?php echo $sale . ' ' . __( 'sold', 'woosalescountdown' ) ?></span>

												</div>
											<?php
											}
											break;
										case 3:
											?>
											<div class="ob_wrapper">
												<?php if ( $show_message ) { ?>
													<h3><?php echo $message ?></h3>

												<?php } ?>
												<?php echo $time_text; ?>
											</div>


											<?php break;
										default:
											?>
											<div class="ob_wrapper">
												<?php if ( $show_message ) { ?>
													<h3><?php echo $message ?></h3>
												<?php } ?>
												<?php if ( $check_sale && $hide_salebar != 'yes' ) { ?>
													<div class="ob_discount">
														<div class="ob_sale" style="width:<?php echo $per_sale ?>%"></div>
													</div>
													<span><?php echo $sale . '/' . $discount . ' ' . __( 'sold', 'woosalescountdown' ) ?></span>
												<?php } ?>
											</div>
											<?php if ( $hide_countdown != 'yes' ) { ?>
											<div class="widget_myCounter_<?php echo $product->id; ?> widget_product"></div>
										<?php } ?>

										<?php
									} ?>
									<?php echo "<script type='text/javascript'>
									jQuery(function () {
										jQuery('.widget_myCounter_" . $product->id . "').mbComingsoon({ expiryDate: new Date(" . date( "Y", $time ) . ", " . ( date( "m", $time ) - 1 ) . ", " . date( "d", $time ) . "), speed:500, gmt:" . get_option( 'gmt_offset' ) . $date_text . $localization . " });
									});
									</script>";
								}
								if ( trim( $ob_product_show_addtocart ) == 'yes' ) {
									echo apply_filters( 'woocommerce_loop_add_to_cart_link',
										sprintf( '<a href="%s" rel="nofollow" data-product_id="%s" data-product_sku="%s" class="button add_to_cart_button %s product_type_%s">%s</a>',
											esc_url( $product->add_to_cart_url() ),
											esc_attr( $product->id ),
											esc_attr( $product->get_sku() ),
											$product->is_purchasable() ? 'add_to_cart_button' : '',
											esc_attr( $product->product_type ),
											esc_html( $product->add_to_cart_text() ) ),
										$product );
								}
								?>
							</li>
						<?php } else {

							$ob_product_show_title     = get_option( 'ob_product_show_title', 'yes' );
							$ob_product_show_price     = get_option( 'ob_product_show_price', 'yes' );
							$ob_product_show_image     = get_option( 'ob_product_show_image', 'yes' );
							$ob_product_show_addtocart = get_option( 'ob_product_show_addtocart', 'yes' );
							$ob_product_show_linkable  = get_option( 'ob_product_show_linkable', 'yes' );
							$ob_product_images_sizes   = get_option( 'ob_product_images_sizes', 'shop_thumbnail' );

							?>
							<li class="product">
								<?php if (trim( $ob_product_show_linkable ) == 'yes'){ ?>
								<a href="<?php echo esc_url( get_permalink( $product->id ) ); ?>">
									<?php } ?>
									<?php if ( trim( $ob_product_show_image ) == 'yes' ) { ?>
										<?php echo $product->get_image( $ob_product_images_sizes ) ?>
									<?php } ?>
									<?php if ( trim( $ob_product_show_title ) == 'yes' ) { ?>
										<h3><?php echo $product->get_title() ?></h3>
									<?php } ?>
									<?php if ( trim( $ob_product_show_price ) == 'yes' ) {
										echo $product->get_price_html();
									} ?>
									<?php if (trim( $ob_product_show_linkable ) == 'yes'){ ?>
								</a>
							<?php
							}


							if ( trim( $ob_product_show_addtocart ) == 'yes' ) {
								echo apply_filters( 'woocommerce_loop_add_to_cart_link',
									sprintf( '<a href="%s" rel="nofollow" data-product_id="%s" data-product_sku="%s" class="button add_to_cart_button  %s product_type_%s">%s</a>',
										esc_url( $product->add_to_cart_url() ),
										esc_attr( $product->id ),
										esc_attr( $product->get_sku() ),
										$product->is_purchasable() ? 'add_to_cart_button' : '',
										esc_attr( $product->product_type ),
										esc_html( $product->add_to_cart_text() ) ),
									$product );
							}
							?>
							</li>
						<?php
						}
					}
				} ?>
			</ul>
		</div>
	<?php
	}
}

?>