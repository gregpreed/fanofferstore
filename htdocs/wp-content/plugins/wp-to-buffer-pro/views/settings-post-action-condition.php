<div class="option">
	<p>
		<?php 
		_e( 'Choose one or more taxonomy terms that must be included in the', $this->plugin->name );
		echo ' ' . $post_type . ' ';
		_e ( 'for the above Publish/Update actions to take effect for this profile.', $this->plugin->name ); 
		?>
	</p>
</div>

<?php
// Output taxonomies
foreach ( $taxonomies as $taxonomy => $details ) {
	?>
	<div class="option">
		<p>
			<?php echo $details->labels->singular_name; ?>
		</p>

		<?php
		switch ( $details->hierarchical ) {
			/**
			* Hierarchal (e.g. Category based)
			*/
			case true:
				?>
				<div class="tax-selection">
					<div class="tabs-panel trigger-tax-<?php echo $type; ?>" style="height: 70px;">
						<ul class="list:category categorychecklist form-no-clear" style="margin: 0; padding: 0;">				                    			
							<?php
							foreach ( $details->terms as $term ) {
								?>
		                        <li>
									<label class="selectit">
										<input type="checkbox" name="<?php echo $this->plugin->name; ?>[<?php echo $profile_id; ?>][<?php echo $action; ?>][<?php echo $taxonomy; ?>][<?php echo $term->term_id; ?>]" value="1" <?php checked( $this->get_setting( $post_type, '[' . $profile_id . '][' . $action .'][' . $taxonomy . '][' . $term->term_id . ']', 0 ), 1, true ); ?> />
										<?php echo $term->name; ?>      
									</label>
								</li>
		                        <?php
							}	
							?>
						</ul>
					</div>
				</div>
				<?php
				break;

			/**
			* Non-hierarchal (e.g. Taxonomy based)
			*/
			case false:
				?>
				<input type="text" name="<?php echo $this->plugin->name; ?>[<?php echo $profile_id; ?>][<?php echo $action; ?>][<?php echo $taxonomy; ?>]" value="<?php echo $this->get_setting( $post_type, '[' . $profile_id . '][' . $action .'][' . $taxonomy . ']', '' ); ?>" />
				<?php
				break;
		}
		?>
	</div>
	<?php
}
?>