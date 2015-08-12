<div class="advads-group-slider-options" <?php if( 'slider' !== $group->type ) : ?>style="display: none;"<?php endif; ?>><label><strong><?php _e('Slide delay', AAS_SLUG );
?></strong><input type="number" name="advads-groups[<?php echo $group->id; ?>][options][slider][delay]" value="<?php echo $delay; ?>"/></label>
<p class="description"><?php _e('Pause for each ad slide in milliseconds', AAS_SLUG); ?></p></div>
<script>
    jQuery(document).ready(function($){
	$('.advads-ad-group-type input').click(function(){
	    console.log(this);
	    var slider_options = $(this).parents('.advads-ad-group-form').find('.advads-group-slider-options');
	    console.log(slider_options);
	    if( 'slider' === $(this).val() ) {
		slider_options.show();
	    } else {
		slider_options.hide();
	    }
	});
    });
</script>