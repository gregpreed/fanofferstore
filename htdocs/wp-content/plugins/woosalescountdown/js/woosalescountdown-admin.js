/**
 * Use
 */
"use strict";
jQuery(document).ready(function () {
	jQuery('.bulk_edit').on('click', function () {
		var value = prompt("Please enter your value");
		var field = jQuery('#field_to_edit option:selected').val();
		switch (field) {
			case "variable_sale_price_dates_from":
			case "variable_sale_price_dates_to":
			case "_quantity_discount":
			case "_quantity_sale":
				jQuery(':input[name^="' + field + '"]').val(value);
				break;
		}
	});
});