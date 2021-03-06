<?php if( isset( $_GET['message'] ) && 6 === $_GET['message'] ) : ?>
<div id="advads-ad-info-top">
    <span id='advads-ad-info-close'>x</span>
    <h2>&#128077; <?php _e( 'Cool, you just published an ad. What now?', ADVADS_SLUG ); ?></h2>
    <h3><?php _e( 'Display the ad in …', ADVADS_SLUG ); ?></h3>
    <div id="advads-ad-options-accordion">
	<h3><?php _e( '… every post or page', ADVADS_SLUG ); ?></h3>
	<div>
	    <p><?php _e( 'Use placements to inject the ad automatically into posts and pages.', ADVADS_SLUG ); ?></p>
	    <a href="<?php echo admin_url( 'admin.php?action=edit&page=advanced-ads-placements' ); ?>" class="button button-primary"><?php _e( 'Configure Placements', ADVADS_SLUG ); ?></a>
	</div>
	<h3><?php _e( '… Sidebar or Widget Area', ADVADS_SLUG ); ?></h3>
	<div>
	    <p><?php _e( 'Use the <em>Advanced Ads</em> Widget to display ads in your sidebars.', ADVADS_SLUG ); ?></p>
	    <a href="<?php echo admin_url('widgets.php'); ?>" class="button button-primary"><?php _e( 'Configure a Widget', ADVADS_SLUG ); ?></a>
	</div>
	<h3><?php _e( '… a few hand selected posts or pages', ADVADS_SLUG ); ?></h3>
	<div>
	    <p><?php _e( 'Use the shortcode below to manually place the ad in the content editor of posts and pages.', ADVADS_SLUG ); ?></p>
	    <pre><input type="text" onclick="this.select();" value='[the_ad id="<?php echo $post->ID; ?>"]'/></pre>
	</div>
	<h3><?php _e( '… in a custom position in your theme', ADVADS_SLUG ); ?></h3>
	<div>
	    <p><?php _e( 'Use the function below to manually place the ad into your template files. This method is needed for more advanced placements like in the header of your theme.', ADVADS_SLUG ); ?></p>
	    <pre><input type="text" onclick="this.select();" value="&lt;?php if( function_exists('the_ad') ) the_ad(<?php echo $post->ID; ?>); ?&gt;"/></pre>
	</div>
	<h3><?php _e( '… in an anchor ad or pop-up', ADVADS_SLUG ); ?></h3>
	<div>
	    <?php if( ! defined( 'AASADS_SLUG' ) ) : ?>
	    <p><?php _e( 'Fix ads to the browser while users are scrolling and create best performing anchor ads.', ADVADS_SLUG ); ?></p>
	    <a class="button button-primary" href="<?php echo ADVADS_URL; ?>add-ons/sticky-ads/" target="_blank"><?php
			_e( 'Get the Sticky add-on', ADVADS_SLUG ); ?></a>
	    <?php else : ?>
	    <p><?php _e( 'You find the settings for the Sticky Ads below.', ADVADS_SLUG ); ?></p>
	    <?php endif; ?>
	    <?php if( ! defined( 'AAPLDS_SLUG' ) ) : ?>
	    <p><?php _e( 'Display content and ads in layers and popups on custom events.', ADVADS_SLUG ); ?></p>
	    <a class="button button-primary" href="<?php echo ADVADS_URL; ?>add-ons/popup-and-layer-ads/" target="_blank"><?php
			_e( 'Get the PopUp and Layer add-on', ADVADS_SLUG ); ?></a>
	    <?php else : ?>
	    <p><?php _e( 'You find the settings for the Layer and PopUp effects below.', ADVADS_SLUG ); ?></p>
	    <?php endif; ?>
	</div>
    </div>

    <p><?php printf( __( 'Learn more about your choices to display an ad in the <a href="%s" target="_blank">manual</a>', ADVADS_SLUG ), ADVADS_URL . 'manual/display-ads/' ); ?></p>
</div>
<script>
  jQuery(function() {
    jQuery( "#advads-ad-options-accordion" ).accordion({
	active: false,
	collapsible: true,
    });
  });
  jQuery('#advads-ad-info-close').click(function(){
      jQuery('#advads-ad-info-top').fadeOut();
  });
</script>
<?php endif;