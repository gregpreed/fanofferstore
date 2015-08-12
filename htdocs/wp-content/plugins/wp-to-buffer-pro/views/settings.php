<div class="wrap">
    <h2 class="wpcube">
    	<?php echo $this->plugin->displayName; ?> 
    	&raquo; 
    	<?php 
    	_e( 'Settings', $this->plugin->name ); 
		?>
    </h2>

    <?php
    // Notices
    foreach ( $notices as $type => $notices_type ) {
    	if ( count( $notices_type ) == 0 ) {
    		continue;
    	}
    	?>
    	<div class="<?php echo ( ( $type == 'success' ) ? 'updated' : $type ); ?> notice">
	    	<?php
	    	foreach ( $notices_type as $notice ) {
	    		?>
	    		<p><?php echo $notice; ?></p>
	    		<?php
	    	}
	    	?>
	    </div>
	    <?php
    }
    ?>
    
    <!-- Tabs -->
	<h2 class="nav-tab-wrapper">
		<a href="admin.php?page=<?php echo $this->plugin->name; ?>-settings" class="nav-tab<?php echo ($tab == 'auth' ? ' nav-tab-active' : ''); ?>">
			<span class="dashicons dashicons-lock"></span> 
			<?php _e( 'Settings', $this->plugin->name ); ?>
		</a>
		<?php                            	
    	// Go through all Post Types, if Buffer is authenticated
    	$access_token = $this->get_setting( '', 'access_token' );
	    if ( ! empty ( $access_token ) ) {                	
	    	foreach ( $post_types as $type => $post_type_obj ) {
	    		// Work out the icon to display
	    		$icon = '';
	    		if ( ! empty( $post_type_obj->menu_icon ) ) {
	    			$icon = 'dashicons ' . $post_type_obj->menu_icon;
	    		} else {
	    			if ( $type == 'post' || $type == 'page' ) {
	    				$icon = 'dashicons dashicons-admin-' . $type;
	    			}
	    		}
	    		?>
	    		<a href="admin.php?page=<?php echo $this->plugin->name; ?>-settings&amp;tab=post&amp;type=<?php echo $type; ?>" class="nav-tab<?php echo ( $post_type == $type ? ' nav-tab-active' : '' ); ?>">
	    			<span class="<?php echo $icon; ?>"></span>
	    			<?php echo $post_type_obj->labels->name; ?>
	    		</a>
	    		<?php
	    	}
    	}
    	?>
	</h2>
	
	<!-- Form Start -->
	<form name="post" method="post" action="<?php echo $_SERVER['REQUEST_URI']; ?>" id="<?php echo $this->plugin->name; ?>">    
	    <div id="poststuff">
	    	<div id="post-body" class="metabox-holder columns-1">
	    		<!-- Content -->
	    		<div id="post-body-content">
		            <div id="normal-sortables" class="meta-box-sortables ui-sortable publishing-defaults">  
		            	<?php
		            	// Load sub view
		            	require_once( $this->plugin->folder . 'views/settings-' . $tab . '.php' );
		            	?>

		            	<!-- Save -->
			    		<div>
			    			<?php wp_nonce_field( $this->plugin->name, $this->plugin->name . '_nonce' ); ?>
							<input type="submit" name="submit" value="<?php _e( 'Save', $this->plugin->name ); ?>" class="button button-primary" />
						</div>
					</div>
					<!-- /normal-sortables -->
	    		</div>
	    		<!-- /post-body-content -->
	    	</div>
		</div> 
	</form>
	<!-- /form end -->		         
</div>