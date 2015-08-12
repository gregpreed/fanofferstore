<?php
/**
* WP Cube Dashboard Widget
*
* Included in all WP Cube plugins by default, it outputs 
* 
* @package WP Cube
* @subpackage Dashboard
* @author Tim Carr
* @version 1.0
* @copyright WP Cube
*/

if ( isset( $products ) && is_object( $products ) ) {
	?>
	<div class="rss-widget">
		<img src="<?php echo $this->dashboard_url; ?>images/logo.png" class="alignleft" style="margin: 0 10px 0 0;" />
		<p><?php _e( 'Thanks for using our plugins - why not check out some of our other amazing Premium WordPress Plugins below?', $this->dashboard->name ); ?></p>
		<ul>
			<?php
			foreach ( $products->item as $key => $product ) {
				?>
				<li>
					<a href="<?php echo (string) $product->link; ?>" target="_blank" class="rsswidget">
						<?php echo (string) $product->title; ?>
					</a>
					<span class="rss-date"></span>
					<div class="rssSummary">
						<?php echo (string) $product->description; ?>		
					</div>
				</li>
				<?php	
			}
			?>
			<li>
				<hr />
				<a href="https://www.wpcube.co.uk/?utm_source=wordpress&utm_medium=link&utm_content=dashboard&utm_campaign=general" target="_blank">
					<?php _e( 'Visit the WP Cube Web Site', $this->dashboard->name ); ?>
				</a>
			</li>
		</ul>
	</div>
	<?php
} else {
	?>
	<p><?php echo( __( 'Why not visit', $this->dashboard->name ).' <a href="https://www.wpcube.co.uk/?utm_source=wordpress&utm_medium=link&utm_content=dashboard&utm_campaign=general" target="_blank">wpcube.co.uk</a> ' . __( 'and check out some of our other amazing Premium WordPress Plugins?', $this->dashboard->name ) ); ?></p>
	<p><a href="https://www.wpcube.co.uk/?utm_source=wordpress&utm_medium=link&utm_content=dashboard&utm_campaign=general" target="_blank" class="button"><?php _e( 'Visit WP Cube', $this->dashboard->name ); ?></a></p>
	<?php
}
?>