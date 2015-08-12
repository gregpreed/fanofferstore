<div class="option sortable<?php echo ( $key == 0 ? ' first' : '' ); ?>" data-status-index="<?php echo $key; ?>">
    <!-- Count + Delete -->
    <div class="number">
        <a href="#" class="count" title="<?php _e( 'Drag status to reorder', $this->plugin->name ); ?>">#<?php echo ( $key + 1 ); ?></a>
        <a href="#" class="dashicons dashicons-trash delete" title="<?php _e( 'Delete Condition', $this->plugin->name ); ?>"></a>
    </div>

    <div class="status">
        <!-- Status -->
        <p>
            <!-- Tags -->
            <select size="1" class="left tags">
                <option value=""><?php _e( '--- Insert Tag ---', $this->plugin->name ); ?></option>
                <?php
                foreach ( $tags as $tag_group => $tag_group_tags ) {
                    ?>
                    <optgroup label="<?php echo $tag_group; ?>">
                        <?php
                        foreach ( $tag_group_tags as $tag => $tag_label ) {
                            ?>
                            <option value="<?php echo $tag; ?>"><?php echo $tag_label; ?></option>
                            <?php
                        }
                        ?>
                    </optgroup>
                    <?php
                }
                ?>
            </select>

            <?php
            // If this profile has subprofiles, display a dropdown to let the user choose a subprofile
            // Also force featured images on, as they're required
            if ( isset( $profile['subprofiles'] ) ) {
                ?>
                <!-- Use Feat. Image -->
                <input type="hidden" name="<?php echo $this->plugin->name; ?>[<?php echo $profile_id; ?>][<?php echo $action; ?>][status][image][]" value="1" />
                
                <!-- Subprofile -->                    
                <select name="<?php echo $this->plugin->name; ?>[<?php echo $profile_id; ?>][<?php echo $action; ?>][status][sub_profile][]" size="1" class="right">
                    <option value="" class="disabled"><?php _e( 'Choose a Pinterest Board', $this->plugin->name ); ?></option>
                    <?php
                    foreach ( $profile['subprofiles'] as $sub_profile ) {
                        ?>
                        <option value="<?php echo $sub_profile['id']; ?>"<?php selected( $this->get_setting( $post_type, '[' . $profile_id . '][' . $action . '][status][' . $key . '][sub_profile]', '' ), $sub_profile['id'] ); ?>>- <?php echo $sub_profile['name']; ?></option>
                        <?php
                    }
                    ?>
                </select> 
                <?php
            } else {
                ?>
                <!-- Use Feat. Image -->
                <select name="<?php echo $this->plugin->name; ?>[<?php echo $profile_id; ?>][<?php echo $action; ?>][status][image][]" size="1" class="right">
                    <option value="0"<?php selected( $this->get_setting( $post_type, '[' . $profile_id . '][' . $action . '][status][' . $key . '][image]', 0 ), 0, true ); ?>><?php _e( 'No Feat. Image', $this->plugin->name ); ?></option>
                    <option value="1"<?php selected( $this->get_setting( $post_type, '[' . $profile_id . '][' . $action . '][status][' . $key . '][image]', 0 ), 1, true ); ?>><?php _e( 'Use Feat. Image', $this->plugin->name ); ?></option>
                </select>
                <?php
            }
            ?>
        </p>
        <p>
        	<textarea name="<?php echo $this->plugin->name; ?>[<?php echo $profile_id; ?>][<?php echo $action; ?>][status][message][]" class="widefat" style="width:100%"><?php echo $this->get_setting( $post_type, '[' . $profile_id . '][' . $action . '][status][' . $key . '][message]', $this->plugin->publish_default_status ); ?></textarea>
        </p>

        <!-- Scheduling -->
        <p>
            <select name="<?php echo $this->plugin->name; ?>[<?php echo $profile_id; ?>][<?php echo $action; ?>][status][schedule][]" size="1" data-conditional="schedule" data-conditional-value="custom">
                <?php
                foreach ( $schedule as $schedule_option => $label ) {
                    ?>
                    <option value="<?php echo $schedule_option; ?>"<?php selected( $this->get_setting( $post_type, '[' . $profile_id . '][' . $action . '][status][' . $key . '][schedule]', '' ), $schedule_option ); ?>><?php echo $label; ?></option>
                    <?php
                }
                ?>
            </select> 

            <span class="schedule">
                <input type="number"name="<?php echo $this->plugin->name; ?>[<?php echo $profile_id; ?>][<?php echo $action; ?>][status][days][]" id="<?php echo $profile_id; ?>_<?php echo $action; ?>_status_<?php echo $key; ?>_days" min="0" max="30" step="1" value="<?php echo $this->get_setting( $post_type, '[' . $profile_id . '][' . $action . '][status][' . $key . '][days]', 0 ); ?>" />
                <label for="<?php echo $profile_id; ?>_<?php echo $action; ?>_status_<?php echo $key; ?>_days"><?php _e( 'Days, ', $this->plugin->name ); ?></label>
                
                <input type="number"name="<?php echo $this->plugin->name; ?>[<?php echo $profile_id; ?>][<?php echo $action; ?>][status][hours][]" id="<?php echo $profile_id; ?>_<?php echo $action; ?>_status_<?php echo $key; ?>_hours" min="0" max="23" step="1" value="<?php echo $this->get_setting( $post_type, '[' . $profile_id . '][' . $action . '][status][' . $key . '][hours]', 0 ); ?>" />
                <label for="<?php echo $profile_id; ?>_<?php echo $action; ?>_status_<?php echo $key; ?>_hours"><?php _e( 'Hours, ', $this->plugin->name ); ?></label>
                
                <input type="number"name="<?php echo $this->plugin->name; ?>[<?php echo $profile_id; ?>][<?php echo $action; ?>][status][minutes][]" id="<?php echo $profile_id; ?>_<?php echo $action; ?>_status_<?php echo $key; ?>_minutes" min="0" max="30" step="1" value="<?php echo $this->get_setting( $post_type, '[' . $profile_id . '][' . $action . '][status][' . $key . '][minutes]', 0 ); ?>" />
                <label for="<?php echo $profile_id; ?>_<?php echo $action; ?>_status_<?php echo $key; ?>_minutes">
                    <?php 
                    _e( 'Minutes after ', $this->plugin->name ); 
                    echo $post_type . ' ' . $action;
                    ?>
                </label>
            </span>
        </p>
    </div>
</div>