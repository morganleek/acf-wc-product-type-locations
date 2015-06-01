<?php

/*
Plugin Name: Advanced Custom Fields: Woocommerce Product Type Locations
Plugin URI: https://github.com/morganleek/acf-wc-product-type-locations
Description: An add-on allows you to speicify ACF locations based on Woocommerce product types.
Version: 1.0.2
Author: Morgan Leek
Author URI: http://morganleek.me/
License: GPL
Copyright: Morgan Leek
*/

defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

// Custom Actions
add_action('acf/input/admin_enqueue_scripts', 'acf_wc_input_admin_enqueue_scripts', 10); // Enque JS
// Custom Filters
add_filter('acf/location/rule_types', 'wc_product_acf_location_rule_types', 50, 1); // Left most location rule
add_filter('acf/location/rule_values/woocommerce_product_type', 'wc_product_acf_location_rule_types_woocommerce_product_type', 50, 1); // Right most ajax loaded location rule
add_filter('acf/location/rule_match/woocommerce_product_type', 'rule_match_woocommerce_product_type', 50, 3); // Rule match tester for when the post edit page is loaded


add_filter('acf/parse_types', 'wc_acf_location_parse_types', 1, 1);

function wc_acf_location_parse_types( $value ) {
	if(is_array($value) && !empty($value)) {
		if(!array_key_exists('woocommerce_product_type', $value) && array_key_exists('post_id',	$value)) {
			// Add current Product Type if one hasn't been set
			$wc_product = new WC_Product($value['post_id']);
			$wc_product_factory = new WC_Product_Factory();
			$wc_product = $wc_product_factory->get_product($wc_product);

			$value['woocommerce_product_type'] = $wc_product->product_type;
		}
	}
	
	return $value;
}


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

function wc_product_acf_location_rule_types($choices) {    
    $choices[__("Woocommerce")] = array(
    	'woocommerce_product_type' => __("Product Type", 'acf')
    );

    return $choices;
}

function wc_product_acf_location_rule_types_woocommerce_product_type($choices) {
	$choices = wc_get_product_types();
	
	return $choices;
}

function rule_match_woocommerce_product_type($match, $rule, $options) {

	$post_type = $options['post_type'];

	if(!$post_type) {
		if(!$options['post_id']) {
			return false;
		}
		
		$post_type = get_post_type($options['post_id']);
	}

	// Ensure is a product
	if( $post_type != 'product') {
		return false;
	}		

	// Ensure Product Type has been set
	if(!array_key_exists('woocommerce_product_type', $options)) {
		return false;
	}
	
	if($rule['operator'] == "==") {
		$match = ( $options['woocommerce_product_type'] === $rule['value'] );
	}
	elseif($rule['operator'] == "!=") {
		$match = ( $options['woocommerce_product_type'] !== $rule['value'] );
	}

	return $match;
}

?>