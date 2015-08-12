<?php
/**
 * Admin setting class
 *
 * @author  Andy ha
 * @package wpbriz.com
 */


if ( !class_exists( 'woosalescountdownadmin' ) ) {
	/**
	 * Admin class.
	 * The class manage all the admin behaviors.
	 *
	 * @since 1.0.0
	 */
	class woosalescountdownadmin {

		public function __construct() {

			//Actions
			add_action( 'init', array( $this, 'init' ) );

			add_action( 'woocommerce_settings_tabs_woosalescountdown', array( $this, 'print_plugin_options' ) );
			add_action( 'woocommerce_update_options_woosalescountdown', array( $this, 'update_options' ) );


			//Filters
			add_filter( 'woocommerce_settings_tabs_array', array( $this, 'add_tab_woocommerce' ), 30 );

		}


		/**
		 * Init method:
		 *  - default options
		 *
		 * @access public
		 * @since  1.0.0
		 */
		public function init() {
			$this->options = $this->_initOptions();
			//$this->_default_options();
		}


		/**
		 * Update plugin options.
		 *
		 * @return void
		 * @since 1.0.0
		 */
		public function update_options() {
			foreach ( $this->options as $option ) {
				woocommerce_update_options( $option );
			}
		}


		/**
		 * Add Magnifier's tab to Woocommerce -> Settings page
		 *
		 * @access public
		 *
		 * @param array $tabs
		 *
		 * @return array
		 */
		public function add_tab_woocommerce( $tabs ) {
			$tabs['woosalescountdown'] = __( 'Products Sales Countdown', 'woosalescountdown' );

			return $tabs;
		}


		/**
		 * Print all plugin options.
		 *
		 * @return void
		 * @since 1.0.0
		 */
		public function print_plugin_options() {
			?>
			<div class="subsubsub_section">

				<?php foreach ( $this->options as $id => $tab ) : ?>
					<!-- tab #<?php echo $id ?> -->
					<div class="section" id="yith_woocompare_<?php echo $id ?>">
						<?php woocommerce_admin_fields( $this->options[$id] ) ?>
					</div>
				<?php endforeach ?>
			</div>
		<?php
		}


		/**
		 * Initialize the options
		 *
		 * @access protected
		 * @return array
		 * @since  1.0.0
		 */
		protected function _initOptions() {
			$options = array(
				'general' => array(
					array( 'title' => __( 'General Options', 'woosalescountdown' ),
						   'type'  => 'title',
						   'desc'  => '',
						   'id'    => 'product_sale_options' ),
					array(
						'title'    => __( 'Show count down', 'woosalescountdown' ),
						'id'       => 'ob_time_show',
						'default'  => '0',
						'type'     => 'radio',
						'desc_tip' => __( 'Please select what you want show in sale countdown.', 'woosalescountdown' ),
						'options'  => array(
							'0' => __( 'Time and bar sale', 'woosalescountdown' ),
							'1' => __( 'Only time ', 'woosalescountdown' ),
							'2' => __( 'Only bar sale', 'woosalescountdown' ),
							'3' => __( 'Only schedule', 'woosalescountdown' )
						),
					),
					array(
						'title'    => __( 'Product Sale Countdown show on', 'woosalescountdown' ),
						'id'       => 'ob_where_show',
						'default'  => '0',
						'type'     => 'radio',
						'desc_tip' => __( 'Please select where you want show Product Sale countdown.', 'woosalescountdown' ),
						'options'  => array(
							'0' => __( 'Both categories and product detail', 'woosalescountdown' ),
							'1' => __( 'Only categories ', 'woosalescountdown' ),
							'2' => __( 'Only product detail', 'woosalescountdown' )
						),
					),
					array(
						'title'    => __( 'Use color', 'woosalescountdown' ),
						'id'       => 'ob_use_color',
						'default'  => '1',
						'type'     => 'radio',
						'desc_tip' => __( 'Please select color for countdown.', 'woosalescountdown' ),
						'options'  => array(
							'0' => __( 'WooCommerce Frontend Styles', 'woosalescountdown' ),
							'1' => __( 'Custom below', 'woosalescountdown' )
						),
					),
					array(
						'title'   => __( 'Time color', 'woosalescountdown' ),
						'id'      => 'ob_time_color',
						'default' => '#000000',
						'type'    => 'text',
						'class'   => 'colorpick',
						'desc'    => __( 'Set color for time on Countdown.', 'woosalescountdown' ),
					),
					array(
						'title'   => __( 'Background color', 'woosalescountdown' ),
						'id'      => 'ob_background_color',
						'default' => '#cccccc',
						'type'    => 'text',
						'class'   => 'colorpick',
						'desc'    => __( 'Set background color for time on Countdown.', 'woosalescountdown' ),
					),
					array(
						'title'   => __( 'Bar Sale color', 'woosalescountdown' ),
						'id'      => 'ob_bar_color',
						'default' => '#ff0000',
						'type'    => 'text',
						'class'   => 'colorpick',
						'desc'    => __( 'Set bar\'s color what number sales on Countdown bar sale.', 'woosalescountdown' ),
					),
					array(
						'title'   => __( 'Bar Sale Background color', 'woosalescountdown' ),
						'id'      => 'ob_bg_bar_color',
						'default' => '#006699',
						'type'    => 'text',
						'class'   => 'colorpick',
						'desc'    => __( 'Set bar\'s background color what number sales on Countdown bar sale.', 'woosalescountdown' ),
					),
					array(
						'title'   => __( 'Product sale\'s title', 'woosalescountdown' ),
						'id'      => 'ob_title_sale',
						'default' => 'Sale',
						'type'    => 'text',
						'desc'    => __( 'Title of product what is saling.', 'woosalescountdown' )
					),
					array(
						'title'   => __( 'Product coming\'s title', 'woosalescountdown' ),
						'id'      => 'ob_title_coming',
						'default' => 'Comming',
						'type'    => 'text',
						'desc'    => __( 'Title of product what is coming sale.', 'woosalescountdown' )
					),
					array(
						'title'    => __( 'Show Sales Countdown message', 'woosalescountdown' ),
						'id'       => 'ob_message_show',
						'default'  => '1',
						'type'     => 'radio',
						'desc_tip' => __( 'Show message of product Comming title or product Sale title.', 'woosalescountdown' ),
						'options'  => array(
							'0' => __( 'No', 'woosalescountdown' ),
							'1' => __( 'Yes', 'woosalescountdown' ),
						),
					),
					array(
						'title'    => __( 'Bar sale for product variations', 'woosalescountdown' ),
						'id'       => 'ob_bar_sale_variations',
						'default'  => '0',
						'type'     => 'radio',
						'desc_tip' => __( 'Show product quantity sold with format', 'woosalescountdown' ),
						'options'  => array(
							'0' => __( 'Private', 'woosalescountdown' ),
							'1' => __( 'Total variations', 'woosalescountdown' ),
						),
					),
					array(
						'title'    => __( 'Show date text', 'woosalescountdown' ),
						'id'       => 'ob_datetext_show',
						'default'  => '1',
						'type'     => 'radio',
						'desc_tip' => __( 'Show Days, Hours, Mins, Sec.', 'woosalescountdown' ),
						'options'  => array(
							'0' => __( 'No', 'woosalescountdown' ),
							'1' => __( 'Yes', 'woosalescountdown' ),
						),
					),
					array(
						'title'   => __( 'Hide product', 'woosalescountdown' ),
						'id'      => 'ob_hide_product',
						'default' => '0',
						'type'    => 'radio',
						'desc'    => __( 'Product will be hide when time schedule expired.', 'woosalescountdown' ),
						'options' => array(
							'0' => __( 'No', 'woosalescountdown' ),
							'1' => __( 'Yes', 'woosalescountdown' )
						),
					),
					array(
						'title'   => __( 'Remove Sale Price', 'woosalescountdown' ),
						'id'      => 'ob_remove_sale_price',
						'default' => '1',
						'type'    => 'radio',
						'desc'    => __( 'Sale price will be remove when time schedule expired.', 'woosalescountdown' ),
						'options' => array(
							'0' => __( 'No', 'woosalescountdown' ),
							'1' => __( 'Yes', 'woosalescountdown' )
						),
					),

					array( 'type' => 'sectionend', 'id' => 'product_sale_options' ),

					array( 'title' => __( 'Product Detail', 'woosalescountdown' ),
						   'type'  => 'title',
						   'desc'  => '',
						   'id'    => 'wooscd_detail_product' ),
					array(
						'title'    => __( 'CountDown position', 'woosalescountdown' ),
						'id'       => 'ob_detail_position',
						'default'  => '0',
						'type'     => 'radio',
						'desc_tip' => __( 'Please choose postion where countdown show on product detail.', 'woosalescountdown' ),
						'options'  => array(
							'0' => __( 'Above tabs area', 'woosalescountdown' ),
							'1' => __( 'Below tabs area', 'woosalescountdown' ),
							'2' => __( 'Above short description', 'woosalescountdown' ),
							'3' => __( 'Below short description', 'woosalescountdown' ),
							'4' => __( 'Above Add to cart', 'woosalescountdown' ),
							'5' => __( 'Below Add to cart', 'woosalescountdown' )
						),
					),

					array( 'type' => 'sectionend', 'id' => 'wooscd_detail_product' ),

					array( 'title' => __( 'Product Category', 'woosalescountdown' ),
						   'type'  => 'title',
						   'desc'  => '',
						   'id'    => 'wooscd_product_category' ),
					array(
						'title'    => __( 'CountDown position on category', 'woosalescountdown' ),
						'id'       => 'ob_category_position',
						'default'  => '0',
						'type'     => 'radio',
						'desc_tip' => __( 'Please choose postion where countdown show on category.', 'woosalescountdown' ),
						'options'  => array(
							'0' => __( 'Above price', 'woosalescountdown' ),
							'1' => __( 'Above title', 'woosalescountdown' ),
							'2' => __( 'Above Add to Cart', 'woosalescountdown' ),
							'3' => __( 'Below Add to Cart', 'woosalescountdown' )
						),
					),
					array( 'type' => 'sectionend', 'id' => 'wooscd_product_category' ),

					array( 'title' => __( 'Product SHORTCODE vs Product WIDGET', 'woosalescountdown' ),
						   'type'  => 'title',
						   'desc'  => '',
						   'id'    => 'wooscd_product_shortcode' ),
					array(
						'title'         => __( 'Product Show', 'woocommerce' ),
						'desc'          => __( 'Show title', 'woocommerce' ),
						'id'            => 'ob_product_show_title',
						'default'       => 'yes',
						'type'          => 'checkbox',
						'checkboxgroup' => 'start'
					),

					array(
						'desc'          => __( 'Show price', 'woocommerce' ),
						'id'            => 'ob_product_show_price',
						'default'       => 'yes',
						'type'          => 'checkbox',
						'checkboxgroup' => '',
						'autoload'      => false
					),

					array(
						'desc'          => __( 'Show Image', 'woocommerce' ),
						'id'            => 'ob_product_show_image',
						'default'       => 'yes',
						'type'          => 'checkbox',
						'checkboxgroup' => '',
						'autoload'      => false
					),

					array(
						'desc'          => __( 'Linkable', 'woocommerce' ),
						'id'            => 'ob_product_show_linkable',
						'default'       => 'yes',
						'type'          => 'checkbox',
						'checkboxgroup' => '',
						'autoload'      => false
					),

					array(
						'desc'          => __( 'Show Add To Cart button', 'woocommerce' ),
						'id'            => 'ob_product_show_addtocart',
						'default'       => 'yes',
						'type'          => 'checkbox',
						'checkboxgroup' => 'end',
						'autoload'      => false
					),
					array(
						'title'   => __( 'Product Image Sizes', 'woosalescountdown' ),
						'id'      => 'ob_product_images_sizes',
						'default' => 'shop_thumbnail',
						'type'    => 'radio',
						'desc'    => __( 'Image Sizes will be follow WooCommerce Setting. Setting at <a href="admin.php?page=wc-settings&tab=products">Product setting</a>.', 'woosalescountdown' ),
						'options' => array(
							'shop_catalog'   => __( 'Catalog Images', 'woosalescountdown' ),
							'shop_single'    => __( 'Single Product Image ', 'woosalescountdown' ),
							'shop_thumbnail' => __( 'Product Thumbnails', 'woosalescountdown' )
						)
					),
					array(
						'title'   => __( 'Use Specified Schedule Time', 'woosalescountdown' ),
						'id'      => 'use_specified_time',
						'default' => '0',
						'type'    => 'radio',
						'desc'    => __( 'Do you want to include specifying time into your schedule of sales?', 'woosalescountdown' ),
						'options' => array(
							'0' => __( 'No', 'woosalescountdown' ),
							'1' => __( 'Yes', 'woosalescountdown' )
						),
					),
					array( 'type' => 'sectionend', 'id' => 'wooscd_product_shortcode' )
				)
			);

			return apply_filters( 'woosalescountdown_tab_options', $options );
		}
	}
}
