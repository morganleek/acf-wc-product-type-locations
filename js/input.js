(function($){
	/*
	*  acf.screen
	*
	*  Data used by AJAX to hide / show field groups
	*
	*  Extension of ACF inbuilt AJAX functionality
	*/

	acf.screen = {
		action 						:	'acf/location/match_field_groups_ajax',
		post_id						:	0,
		page_template				:	0,
		page_parent					:	0,
		page_type					:	0,
		post_category				:	0,
		post_format					:	0,
		taxonomy					:	0,
		lang						:	0,
		nonce						:	0,
		woocommerce_product_type	:   0
	};

	$(document).on('change', '#product-type', function(){
		
		acf.screen.woocommerce_product_type = $(this).val();
		
		$(document).trigger('acf/update_field_groups');
	    
	});
})(jQuery);