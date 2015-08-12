jQuery(document).ready(function($) {	
	/**
	* Top level tabbed interface. If defined in the view:
	* - tabs are set to display, as JS is enabled
	* - the selected tab's panel is displayed, with all others hidden
	* - clicking a tab will switch which panel is displayed
	*/
	if ($('h2.nav-tab-wrapper.needs-js').length > 0) {
		// Show tabbed bar
		$('h2.nav-tab-wrapper.needs-js').fadeIn('fast', function() {
			$(this).removeClass('needs-js');
		});
		
		// Hide all panels except the active one
		$('#normal-sortables div.panel').hide();
		var activeTab = $('h2.nav-tab-wrapper a.nav-tab-active').attr('href')+'-panel';
		$(activeTab).show();
		
		// Change active panel on tab change
		$('h2.nav-tab-wrapper a').click(function(e) {
			e.preventDefault();
			
			// Deactivate all tabs, hide all panels
			$('h2.nav-tab-wrapper a').removeClass('nav-tab-active');
			$('#normal-sortables div.panel').hide();
			
			// Set clicked tab to active, show panel
			$(this).addClass('nav-tab-active');
			var activeTab = $(this).attr('href')+'-panel';
			$(activeTab).show();
		});
	}
	
	/**
	* Second level tabbed interface. If defined in the view:
	* - tabs are set to display, as JS is enabled
	* - the selected tab's panel is displayed, with all others hidden
	* - clicking a tab will switch which panel is displayed
	*/
	if ($('h3.nav-tab-wrapper.needs-js').length > 0) {
		// Iterate through each sub tab bar
		$('h3.nav-tab-wrapper.needs-js').each(function() {
			// Show tabbed bar
			$(this).fadeIn('fast', function() {
				$(this).removeClass('needs-js');
			});

			// Get active tab
			var activeTab = window.location.hash;
			if ( activeTab == '' ) {
				// Make first sub panel active
				activeTab = $('a:first-child', $(this)).attr('href');
			} else {
				activeTab = activeTab;
			}

			// Show active tab 
			$('a', $(this)).removeClass('nav-tab-active');
			$('a[href="' + activeTab + '"]', $(this)).addClass('nav-tab-active');

			// Amend the form target to include this hash so the user is then taken to this tab's panel after submitting the form
			var formActionParts = $( this ).closest( 'form' ).attr('action').split( '#' );
			$( this ).closest( 'form' ).attr('action', formActionParts[0] + activeTab );
			
			// Show active tab's panel
			activeTab = activeTab + '-panel';
			$('div.sub-panel', $(this).parent()).hide();
			$(activeTab).show();

			// Change active panel on tab change
			$('a', $(this)).click(function(e) {

				// Get active tab
				var activeTab = $(this).attr('href');

				// Show active tab 
				$('a', $(this).parent()).removeClass('nav-tab-active');
				$(this).addClass('nav-tab-active');

				// Amend the form target to include this hash so the user is then taken to this tab's panel after submitting the form
				var formActionParts = $( this ).closest( 'form' ).attr('action').split( '#' );
				$( this ).closest( 'form' ).attr('action', formActionParts[0] + activeTab );
				
				// Show active tab's panel
				activeTab = activeTab + '-panel';
				$('div.sub-panel', $(this).parent().parent()).hide();
				$(activeTab).show();

			});
		});
	}

	/**
	* Conditional Fields
	*/
	$('input,select').conditional();
});