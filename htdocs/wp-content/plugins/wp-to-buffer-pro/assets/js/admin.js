jQuery( document ).ready( function( $ ) {

	/**
	* Clear Log
	*/
	$( '#wp-to-buffer-pro-log a.clear-log' ).on( 'click', function(e) {
		e.preventDefault();

		// Confirm clear
		var result = confirm( wp_to_buffer_pro.clear_log_message );
		if ( ! result ) {
			return;
		}

		$.post( 
			wp_to_buffer_pro.ajax, 
			{
				'action': 						'wp_to_buffer_pro_clear_log',
				'post': 						$( 'input[name=post_ID]' ).val(),
				'wp-to-buffer-pro-clear-log': 	1,
				'nonce': 						wp_to_buffer_pro.clear_log_nonce
			},
			function(response) {
				if ( response == '1' ) {
					// Clear log from UI
					$( '#wp-to-buffer-pro-log table.widefat tbody' ).html( '<tr><td colspan="3">' + wp_to_buffer_pro.clear_log_completed + '</td></tr>' );
				}
            }
        );
	} );

	/**
	* Tooltips
	*/
	$( 'h3.nav-tab-wrapper a.nav-tab' ).tooltipster();

	/**
	* Tags
	*/
	var reinit_tags = function() {
		$( 'select.tags' ).each( function() {
			$( this ).unbind( 'change.wp-to-buffer-pro' ).on( 'change.wp-to-buffer-pro', function( e ) {
				// Insert tag into required textarea
				var tag 	= $( this ).val(),
					status 	= $( this ).closest( 'div.status' ),
					sel 	= $( 'textarea', $( status ) ),
					val 	= $( sel ).val();

				$( sel ).val( val += ' ' + tag );
			});
		});
	}
	reinit_tags();
	
	/**
	* Add Status Update
	*/
	$( 'a.button.add-status' ).on( 'click', function( e ) {
		e.preventDefault();

		// Setup vars
		var button 				= $( this ),
			button_container 	= $( button ).parent(),
			statuses_container 	= $( button ).closest( 'div.statuses' ),
			status 				= $( button_container ).prev().html();

		// Clone status
		$( button_container ).before( '<div class="option sortable">' + status + '</div>' );

		// Reindex statuses
		reindex_statuses( $( statuses_container ) );

		// Reload sortable
		$( 'div.statuses' ).sortable( 'refresh' );

		// Reload conditionals
		$( 'input,select' ).conditional();

		// Reload tag selector
		reinit_tags();

	});

	/**
	* Reorder Status Updates
	*/
	$( 'div.statuses' ).sortable({
		containment: 'parent',
		items: '.sortable',
		stop: function( e, ui ) {
			// Get status and container
			var status 				= $( ui.item ),
				statuses_container 	= $( status ).closest( 'div.statuses' );

			// Reindex statuses
			reindex_statuses( $( statuses_container ) );
		}
	});

	/**
	* Delete Status Update
	*/
	$( 'div.sub-panel' ).on( 'click', 'a.delete', function( e ) {
		e.preventDefault();

		// Confirm deletion
		var result = confirm( wp_to_buffer_pro.delete_status_message );
		if ( ! result ) {
			return;
		}

		// Get status and container
		var status 				= $( this ).closest( 'div.option' ),
			statuses_container 	= $( status ).closest( 'div.statuses' );

		// Delete status
		$( status ).remove();

		// Reindex statuses
		reindex_statuses( $( statuses_container ) );

	});

	/**
	* Changes the displayed index on each status within the given container
	*
	* @since 3.0
	*
	* @param obj status_container  		Status Container
	*/
	var reindex_statuses = function( statuses_container ) {

		// Find all sortable options in the status container (these are individual statuses)
		// and reindex them from 1
		$( 'div.option.sortable', $( statuses_container ) ).each(function( i ) {
			$( 'div.number a.count ', $( this ) ).html( '#' + ( i + 1 ) );

			// Set 'first' class
			if ( i == 0 ) {
				$( this ).addClass( 'first' );
			} else {
				$( this ).removeClass( 'first' );
			}
		});

	}

});