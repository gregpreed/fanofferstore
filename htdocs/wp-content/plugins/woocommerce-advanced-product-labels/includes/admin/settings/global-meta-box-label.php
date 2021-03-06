<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/**
 * Label settings
 *
 * Display the label settings in the meta box
 *
 * @author		Jeroen Sormani
 * @package		WooCommerce Advanced Product Labels
 * @version		1.0.0
 */
wp_nonce_field( 'wapl_global_label_meta_box', 'wapl_global_label_meta_box_nonce' );

global $post;
$values = get_post_meta( $post->ID, '_wapl_global_label', true );

$label_custom_bg_color 		= isset( $values['label_custom_background_color'] ) ? $values['label_custom_background_color'] : '#D9534F';
$label_custom_text_color 	= isset( $values['label_custom_text_color'] ) ? $values['label_custom_text_color'] : '#fff';

?><div class='wapl-meta-box'>

	<div class='wapl-column' style='width: 48%;'>


		<p class='wapl-global-option'>

			<label for='wapl_global_label_type'><?php _e( 'Label type', 'woocommerce-advanced-product-labels' ); ?></label>
			<select id='wapl_global_label_type' name='_wapl_global_label[type]'><?php
				foreach ( WooCommerce_Advanced_Product_Labels()->get_label_types() as $key => $value ) :
					?><option value='<?php echo $key; ?>' <?php @selected( $values['type'], $key ); ?>><?php echo $value; ?></option><?php
				endforeach;
			?></select>

		</p>


		<p class='wapl-global-option'>

			<label for='wapl_global_label_text'><?php _e( 'Label text', 'woocommerce-advanced-product-labels' ); ?></label>
			<input type='text' id='wapl_global_label_text' name='_wapl_global_label[text]' value='<?php echo @esc_attr( $values['text'] ); ?>' size='25'/>

		</p>


		<p class='wapl-global-option'>

			<label for='wapl_global_label_style'><?php _e( 'Label style', 'woocommerce-advanced-product-labels' ); ?></label>
			<select name='_wapl_global_label[style]' class='wapl-select' id='wapl_global_label_style'><?php

				foreach ( WooCommerce_Advanced_Product_Labels()->label_styles as $key => $value ) :

					?><option value='<?php echo $key; ?>' <?php @selected( $values['style'], $key); ?>><?php echo $value; ?></option><?php

				endforeach;

			?></select>

		</p>

		<p class='wapl-global-option custom-colors <?php echo isset( $values['style'] ) && $values['style'] == 'custom' ? '' : 'hidden'; ?>'>
			<label for='wapl-custom-background'><?php _e( 'Background color', 'woocommerce-advanced-product-labels' ); ?></label>
			<input type='text' name='_wapl_global_label[label_custom_background_color]' value='<?php echo $label_custom_bg_color; ?>' id='wapl-custom-background' class='color-picker' />

			<label for='wapl-custom-text'><?php _e( 'Text color', 'woocommerce-advanced-product-labels' ); ?></label>
			<input type='text' name='_wapl_global_label[label_custom_text_color]' value='<?php echo $label_custom_text_color; ?>' id='wapl-custom-text' class='color-picker' />
		</p>


		<p class='wapl-global-option'>

			<label for='wapl_global_label_align'><?php _e( 'Label align', 'woocommerce-advanced-product-labels' ); ?></label>
			<select name='_wapl_global_label[align]' class='wapl-select' id='wapl_global_label_align'>
				<option value='none' 	<?php @selected( $values['align'], 'none'); ?>><?php _e( 'None', 'woocommerce-advanced-product-labels' ); ?></option>
				<option value='left' 	<?php @selected( $values['align'], 'left'); ?>><?php _e( 'Left', 'woocommerce-advanced-product-labels' ); ?></option>
				<option value='center' 	<?php @selected( $values['align'], 'center'); ?>><?php _e( 'Center', 'woocommerce-advanced-product-labels' ); ?></option>
				<option value='right' 	<?php @selected( $values['align'], 'right'); ?>><?php _e( 'Right', 'woocommerce-advanced-product-labels' ); ?></option>
			</select>

		</p>

	</div>

	<div class='wapl-column' style='width: 10%;'>
		<h2 class='wapl-preview'>Preview</h2>
	</div>

	<div class='wapl-column' style='width: 28%; border-left: 1px solid #ddd; padding-left: 4%;'>

		<div id='wapl-global-preview'>
			<img src='<?php echo apply_filters( 'wapl_preview_image', 'data:image/gif;base64,R0lGODdhlgCWAOMAAMzMzJaWlr6+vpycnLGxsaOjo8XFxbe3t6qqqgAAAAAAAAAAAAAAAAAAAAAAAAAAACwAAAAAlgCWAAAE/hDISau9OOvNu/9gKI5kaZ5oqq5s675wLM90bd94ru987//AoHBILBqPyKRyyWw6n9CodEqtWq/YrHbL7Xq/4LB4TC6bz+i0es1uu9/wuHxOr9vv+Lx+z+/7/4CBgoOEhYaHiImKi4yNjo+QkZKTlJWWl5iZmpucnZ6foHcCAwMTAaenBxMCBQEFBiajpRKoqautr2cEp7MApwjAAhIGA64BvSK7x6YBwAjCAMTGyGK7rb3LFbsEAAgBqsnTptQA293fZQaq2b7krbACzSPq7eMW7wDxCGjsxwTPE4oNc2XhlIB4ATT0G/APGgCB0Qie6VcL2kIL3oDJy0ARlUVsz+TEsEPw6sDGi/dIFdgwsuRJkPxCZkNZAaFDDOwozIQ5MSREiAYkVggaAJZCnwkfJg26sucEcEol4NN3QRm3o08DJp260Uw2k9yYSjDnDarOAgVC6pwFNmJTsujKoD3VtFjauNKuXWh1wGSBffdaSbRbDFzenGNqLb12VcIoV0YrnKI1uWCtYYwpPM4VqrPnz6BDix5NurTp06hTq17NurXr17Bjy55Nu7bt27hz697Nu7fv38CDCx9OvLjx48iTK1/OvLnz59CjS59OvfqLCAA7' ); ?>' />
			<?php
			$type 		= isset( $values['type'] ) 	? $values['type'] 	: 'label';
			$text 		= isset( $values['text'] ) 	? $values['text'] 	: 'text';
			$style 		= isset( $values['style'] ) ? $values['style'] 	: 'style';
			$align 		= isset( $values['align'] ) ? $values['align'] 	: 'none';
			$style_attr = isset( $values['style'] ) && 'custom' == $values['style'] ? "style='background-color: $label_custom_bg_color; color: $label_custom_text_color;'" : '';
			new WAPL_Label( $type, $text, $style, $align, $style_attr );

			?><p><strong>Product name</strong></p>
		</div>
	</div>

</div>
