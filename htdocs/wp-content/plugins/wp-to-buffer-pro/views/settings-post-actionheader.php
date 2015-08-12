<!-- Action Header -->
<div class="postbox">
	<h3 class="hndle">
		<?php echo _e( 'Settings', $this->plugin->name ); ?>
	</h3>

    <!-- Account Enabled -->
    <div class="option">
        <p>
            <label for="<?php echo $profile_id; ?>_enabled">
                <strong><?php _e( 'Account Enabled', $this->plugin->name ); ?></strong>
                <input type="checkbox" id="<?php echo $profile_id; ?>_enabled" name="<?php echo $this->plugin->name; ?>[<?php echo $profile_id; ?>][enabled]" id="<?php echo $profile_id; ?>_enabled" value="1"<?php checked( $this->get_setting( $post_type, '[' . $profile_id . '][enabled]', 0 ), 1, true ); ?> />
            </label>
        </p>

        <p class="description"><?php _e( 'Enabling this social media account means that Posts will be sent to this social media account, if the conditions in the Settings are met.', $this->plugin->name ); ?></p>
    </div>

    <!-- Override Default Settings -->
    <div class="option">
        <p>
            <label for="<?php echo $profile_id; ?>_override">
                <strong><?php _e( 'Override Defaults', $this->plugin->name ); ?></strong>
                <input type="checkbox" id="<?php echo $profile_id; ?>_override" name="<?php echo $this->plugin->name; ?>[<?php echo $profile_id; ?>][override]" id="<?php echo $profile_id; ?>_override" value="1"<?php checked( $this->get_setting( $post_type, '[' . $profile_id . '][override]', 0 ), 1, true ); ?> data-conditional="<?php echo $post_type; ?>-<?php echo $profile_id; ?>-actions-panel" />
            </label>
        </p>

        <p class="description"><?php _e( 'Check this box to define custom settings when publishing or updating to this social media account. Not checking this box will mean that this social media account uses settings from the "Settings" tab', $this->plugin->name ); ?></p>
    </div>
</div>