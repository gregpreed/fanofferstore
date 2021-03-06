<?php $types = Advanced_Ads::get_instance()->ad_types; ?>
<?php
/**
 * when changing ad type ad parameter content is loaded via ajax
 * @filesource admin/assets/js/admin.js
 * @filesource includes/class-ajax-callbacks.php ::load_ad_parameters_metabox
 * @filesource classes/ad-type-content.php :: renter_parameters()
 */
do_action( 'advanced-ads-ad-params-before', $ad, $types ); ?>
<div id="advanced-ads-ad-parameters">
    <?php $type = (isset($types[$ad->type])) ? $types[$ad->type] : current( $types );
		$type->render_parameters( $ad );
	?>
    <div id="advanced-ads-ad-parameters-size">
        <p><?php _e( 'size:', ADVADS_SLUG ); ?></p>
        <label><?php _e( 'width', ADVADS_SLUG ); ?><input type="number" size="4" maxlength="4" value="<?php echo isset($ad->width) ? $ad->width : 0; ?>" name="advanced_ad[width]">px</label>
        <label><?php _e( 'height', ADVADS_SLUG ); ?><input type="number" size="4" maxlength="4" value="<?php echo isset($ad->height) ? $ad->height : 0; ?>" name="advanced_ad[height]">px</label>
    </div>
</div>
<?php do_action( 'advanced-ads-ad-params-after', $ad, $types );
