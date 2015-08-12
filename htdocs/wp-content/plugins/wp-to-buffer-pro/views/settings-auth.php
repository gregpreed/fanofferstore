<div class="postbox panel">
    <h3 class="hndle"><?php _e( 'Buffer Authentication', $this->plugin->name ); ?></h3>
    
	<?php
    $access_token = $this->get_setting( '', 'access_token' );
	if ( ! empty ( $access_token ) ) {
		// Already authenticated
		?>
		<div class="option">
			<p>
				<?php _e('Thanks - you\'ve authenticated the plugin with your Buffer account.', $this->plugin->name); ?>
                <input type="hidden" name="access_token" value="<?php echo $this->get_setting( '', 'access_token' ); ?>" />
				<a href="admin.php?page=<?php echo $this->plugin->name; ?>-settings&amp;disconnect=1" class="button button-red">
					<?php _e( 'Disconnect Buffer Account', $this->plugin->name ); ?>
				</a>
			</p>
		</div>
		<?php
	} else {
		// Need to authenticate with Buffer
    	?>
    	<div class="option">
    		<p>
    			<strong><?php _e( 'Access Token', $this->plugin->name ); ?></strong>
            	<input type="text" name="access_token" value="" />
            </p>
        </div>
        <div class="option">
            <p>
            	<?php _e( 'You can obtain an access token to allow this Plugin to post updates to your Buffer account by', $this->plugin->name ); ?>
            	<a href="http://bufferapp.com/developers/apps/create" target="_blank"><?php _e( 'Registering an Application', $this->plugin->name ); ?></a>
            </p>
            <p>
            	Set the Callback URL to <i><?php bloginfo('url'); ?>/wp-admin/admin.php?page=<?php echo $this->plugin->name; ?>-settings</i>
            </p>
            <p>
            	<?php _e( 'You can set the other settings to anything.', $this->plugin->name ); ?>
            </p>
        </div>
    	<?php
	}
	?>

    <!-- Plugin Settings -->
    <div class="option">
        <p>
            <strong><?php _e( 'Use WP Cron?', $this->plugin->name ); ?></strong>
            <input type="checkbox" name="cron" value="1" <?php checked( $this->get_setting( '', 'cron' ), 1 ); ?> />
        </p>
        <p>
            <?php _e( 'If enabled, status updates will be scheduled for sending to Buffer using the WordPress Cron, instead of being sent immediately when publishing or updating a Post.', $this->plugin->name ); ?>
            <br />
            <?php _e( 'Whilst this improves plugin performance on Post Admin screens, social media updates may take a few minutes to appear on Buffer. Also, if your site has low traffic volumes, the WordPress Cron may take several minutes, even hours, to trigger.', $this->plugin->name ); ?>
            <br />
            <?php _e( 'To monitor your WordPress Cron jobs, we recommend installing <a href="" target="_blank">WP Crontrol</a>.  WordPress to Buffer Pro will display its jobs with the Hook Name <strong>wp_to_buffer_pro_publish_cron</strong>', $this->plugin->name ); ?>
        </p>
    </div>

    <div class="option">
        <p>
            <strong><?php _e( 'Enable Logging?', $this->plugin->name ); ?></strong>
            <input type="checkbox" name="log" value="1" <?php checked( $this->get_setting( '', 'log' ), 1 ); ?> />
        </p>
        <p>
            <?php _e( 'If enabled, each Post will display Log information detailing what information was sent to Buffer, and the response received. As this dataset can be quite large, we only recommend this be enabled when troubleshooting issues.', $this->plugin->name ); ?>
        </p>
    </div>
</div>