<?php

/*
Plugin Name: Advanced Custom Fields: Woocommerce Product Type Locations
Plugin URI: http://morganleek.me/
Description: An add-on allows you to speicify ACF locations based on Woocommerce product types.
Version: 1.0
Author: Morgan Leek
Author URI: http://morganleek.me/
License: GPL
Copyright: Morgan Leek
*/

defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

// Left most location rule
add_filter('acf/location/rule_types', 'wc_product_acf_location_rule_types', 50, 1);

function wc_product_acf_location_rule_types($choices) {
    if(class_exists('acf') && class_exists('WooCommerce')) {
	    $choices[__("Woocommerce")] = array(
	    	'woocommerce_product_type' => __("Product Type", 'acf')
	    );
	}
 
    return $choices;
}

add_filter('acf/location/rule_values/woocommerce_product_type', 'wc_product_acf_location_rule_types_woocommerce_product_type', 50, 1);

// Right most ajax loaded location rule
function wc_product_acf_location_rule_types_woocommerce_product_type($choices) {
	if(class_exists('acf') && class_exists('WooCommerce')) {
		$choices = wc_get_product_types();
	}

	return $choices;
}

add_filter('acf/location/rule_match/woocommerce_product_type', 'rule_match_woocommerce_product_type', 50, 3);

// Rule match tester for when the post edit page is loaded
function rule_match_woocommerce_product_type($match, $rule, $options) {
	if(class_exists('acf') && class_exists('WooCommerce')) {
		$post_type = $options['post_type'];

		if(!$post_type) {
			if(!$options['post_id']) {
				return false;
			}
			
			$post_type = get_post_type($options['post_id']);
		}
		
		if( $post_type != 'product') {
			return false;
		}		

		// Get Woocommerce product
		$wc_product = new WC_Product($options['post_id']);
		$wc_product_factory = new WC_Product_Factory();
		$wc_product = $wc_product_factory->get_product($wc_product);

		if($rule['operator'] == "==") {
			$match = ( $wc_product->product_type === $rule['value'] );
		}
		elseif($rule['operator'] == "!=") {
			$match = ( $wc_product->product_type !== $rule['value'] );
		}
    }

	return $match;
}

?>