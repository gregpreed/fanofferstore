<?php
/**
* WP Cube Dashboard Widget (No RSS feed available)
* 
* @package WP Cube
* @subpackage Dashboard
* @author Tim Carr
* @version 1.0
* @copyright WP Cube
*/
?>
<div class="rss-widget">
	<img src="<?php echo $this->dashboard_url; ?>images/logo.png" class="alignleft" style="margin: 0 10px 0 0;" />
	<p><?php _e( 'Thanks for using our plugins - why not check out some of our other amazing Premium WordPress Plugins?', $this->dashboard->name ); ?></p>
	<p>
		<a href="https://www.wpcube.co.uk/?utm_source=wordpress&utm_medium=link&utm_content=dashboard&utm_campaign=general" target="_blank" class="button">
			<?php _e( 'Visit WP Cube', $this->dashboard->name ); ?>
		</a>
	</p>
</div>