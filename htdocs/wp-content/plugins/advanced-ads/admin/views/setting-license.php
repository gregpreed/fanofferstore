<?php
$errortext = '';
$expires = get_option( $options_slug . '-license-expires', 0 );
$expired = false;
if( $expires ){
    $expires_time = strtotime( $expires );
    $days_left = ( $expires_time - time() ) / DAY_IN_SECONDS;
    if( $days_left <= 0 ){
	$plugin_url = isset( $plugin_url ) ? $plugin_url : ADVADS_URL;
	$errortext = sprintf(__( 'Your license expired. Please visit <a href="%s" target="_blank">the plugin page</a> to renew it.', ADVADS_SLUG ), $plugin_url );
	$expired = true;
    }
};
?><input type="text" class="regular-text" placeholder="<?php _e( 'License key', ADVADS_SLUG ); ?>"
       name="<?php echo ADVADS_SLUG . '-licenses'; ?>[<?php echo $index; ?>]"
       value="<?php echo esc_attr_e($license_key); ?>"/><?php
if( $license_status !== false && $license_status == 'valid' && ! $expired ) :
    $show_active = true;
else :
    $show_active = false;
    if($license_key !== '') :
        ?><button type="button" class="button-secondary advads-license-activate"
		data-addon="<?php echo $index; ?>"
		data-pluginname="<?php echo $plugin_name; ?>"
		data-optionslug="<?php echo $options_slug; ?>"
		name="advads_license_activate"><?php _e('Activate License'); ?></button><?php
    endif;
    if( ! $expired ){
	$errortext = ( ! $license_status || $license_status == 'invalid') ? __('license key invalid', ADVADS_SLUG) : '';
    }
endif;
if( $errortext ) :
?><span class="advads-license-activate-error"><?php echo $errortext; ?></span><?php
endif;
?><span class="advads-license-activate-active" <?php if( ! $show_active ) echo 'style="display: none;"'; ?>><?php _e( 'active', ADVADS_SLUG );
?>&nbsp;<?php if( isset( $days_left ) && $days_left > 0 ) echo sprintf( __('(%d days left)', ADVADS_SLUG ), $days_left );
?></span><?php
if($license_key === '') :
    ?><p class="description"><?php _e( '1. enter the key and save options; 2. click the activate button behind the field', ADVADS_SLUG ); ?></p><?php
endif;