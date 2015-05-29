<?php

/*
Plugin Name: Advanced Custom Fields: Woocommerce Product Type Locations (Pere Noel)
Plugin URI: http://morganleek.me/
Description: An add-on allows you to speicify ACF locations based on Woocommerce product types. Code named 'Pere Noel'
Version: 1.0.1
Author: Morgan Leek
Author URI: http://morganleek.me/
License: GPL
Copyright: Morgan Leek
*/

defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

// Enque JS
add_action('acf/input/admin_enqueue_scripts', 'acf_wc_input_admin_enqueue_scripts', 10);

function acf_wc_input_admin_enqueue_scripts() {
	$settings = array(
		'path' => apply_filters('acf/helpers/get_path', __FILE__),
		'dir' => apply_filters('acf/helpers/get_dir', __FILE__),
		'version' => '1.0.1'
	);
	
	// register acf scripts
	wp_register_script( 'acf-wc-input-product-type-locations', $settings['dir'] . 'js/input.js', array('acf-input'), $settings['version'] );
	
	// scripts
	wp_enqueue_script(array('acf-wc-input-product-type-locations'));		
}

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
		$product_type = "";
		if(array_key_exists('woocommerce_product_type', $options)) {
			// This can likely be streamlined
			$product_type = $options['woocommerce_product_type'];
		}
		else {
			$wc_product = new WC_Product($options['post_id']);
			$wc_product_factory = new WC_Product_Factory();
			$wc_product = $wc_product_factory->get_product($wc_product);
			$product_type = $wc_product->product_type;
		}

		if($rule['operator'] == "==") {
			$match = ( $product_type === $rule['value'] );
		}
		elseif($rule['operator'] == "!=") {
			$match = ( $product_type !== $rule['value'] );
		}
    }

	return $match;
}


?>