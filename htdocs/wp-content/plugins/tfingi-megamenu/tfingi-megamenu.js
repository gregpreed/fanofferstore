function add_megamenu_item(e, url, title){
    e.preventDefault();
    var menu = jQuery('#menu').val(),
    nonce = jQuery('#menu-settings-column-nonce').val(),
    data = {
        'action': 'add-menu-item',
        'menu': menu,
        'menu-settings-column-nonce': nonce,
        'menu-item[-1][menu-item-type]': 'custom',
        'menu-item[-1][menu-item-url]': url,
        'menu-item[-1][menu-item-title]': title
    };
    jQuery.post(ajaxurl, data,
        function(response) {
            var ins = jQuery('#menu-instructions');

            // Make it stand out a bit more visually, by adding a fadeIn

            if( ! ins.hasClass( 'menu-instructions-inactive' ) && ins.siblings().length )
            ins.addClass( 'menu-instructions-inactive' );
            jQuery( '#menu-to-edit' ).append( response );
            jQuery( 'li.pending' ).hide().fadeIn('slow');
            jQuery( '.drag-instructions' ).show();
        }
    );
}

jQuery(document).ready(function(){
    jQuery('#submit-megamenu-column').on('click', function(e) {add_megamenu_item(e, '#MegaMenuColumn', 'Mega Menu Column');});
    jQuery('#submit-megamenu-heading-item').on('click', function(e) {add_megamenu_item(e, '#MegaMenuHeading', 'Mega Menu Heading');});
    jQuery('#submit-megamenu-content-item').on('click', function(e) {add_megamenu_item(e, '#MegaMenuContent', 'Mega Menu Content');});
});