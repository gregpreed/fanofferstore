<?php $types = Advanced_Ads::get_instance()->ad_types; ?>
<?php if ( empty($types) ) : ?>
    <p><?php _e( 'No ad types defined', ADVADS_SLUG ); ?></p>
<?php else : ?>
    <ul id="advanced-ad-type">
        <?php
			// choose first type if none set
			$type = (isset($ad->type)) ? $ad->type : current( $types )->ID;
		foreach ( $types as $_type ) : ?>
            <li>
                <input type="radio" name="advanced_ad[type]"
                       id="advanced-ad-type-<?php echo $_type->ID ?>"
                       value="<?php echo $_type->ID; ?>"
						<?php  checked( $type, $_type->ID ); ?>/>
                <label for="advanced-ad-type-<?php echo $_type->ID ?>"><?php echo (empty($_type->title)) ? $_type->ID : $_type->title; ?></label>
                <?php if ( ! empty($_type->description) ) : ?><span class="description"><?php echo $_type->description; ?></span><?php endif; ?>
            </li>
        <?php endforeach; ?>
    </ul>
<?php endif; ?>