<?php

/*
Plugin Name: Advanced Custom Fields: Woocommerce Location Rules
Plugin URI: https://github.com/morganleek/acf-wc-product-type-locations
Description: An add-on allows you to speicify ACF location rules based on Woocommerce attributes.
Version: 1.0.5
Author: Morgan Leek
Author URI: http://morganleek.me/
License: GPL
Copyright: Morgan Leek
*/

defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

// Enqueue JS
add_action('acf/input/admin_enqueue_scripts', 'acf_wc_input_admin_enqueue_scripts', 10); // Enque JS

// Location Rules
add_filter('acf/location/rule_types', 'wc_product_acf_location_rule_types', 50, 1); 
add_filter('acf/location/rule_values/woocommerce_product_type', 'wc_product_acf_location_rule_types_woocommerce_product_type', 50, 1);
add_filter('acf/location/rule_values/woocommerce_variations', 'wc_product_acf_location_rule_types_woocommerce_variations', 50, 1);
add_filter('acf/location/rule_values/woocommerce_taxation', 'wc_product_acf_location_rule_types_woocommerce_taxation', 50, 1);

// Rule Validation
add_filter('acf/location/rule_match/woocommerce_product_type', 'rule_match_woocommerce_product_type', 50, 3); // Rule match tester for when the post edit page is loaded
add_filter('acf/location/rule_match/woocommerce_variations', 'rule_match_woocommerce_bools', 50, 3);
add_filter('acf/location/rule_match/woocommerce_taxation', 'rule_match_woocommerce_bools', 50, 3);

add_filter('acf/parse_types', 'wc_acf_location_parse_types', 1, 1);

// Position Rules
add_action('acf/create_field', 'wc_product_acf_location_rule_types_create_field', 4, 1);

function wc_acf_location_parse_types( $value ) {
	if(is_array($value) && !empty($value) && isset($value['post_id']) && $value['post_id'] != 0) {
		if(!array_key_exists('woocommerce_product_type', $value) && array_key_exists('post_id',	$value) && array_key_exists('post_type', $value) && $value['post_type'] == "product") {
			// Get Product
			$product = wc_get_product($value['post_id']);
		
			// Woocommerce Product Variables
			$value['woocommerce_product_type'] = $product->product_type;
			$value['woocommerce_is_in_stock'] = $product->stock_status;
			$value['woocommerce_is_downloadable'] = $product->is_downloadable(); // $value['woocommerce_is_downloadable'] = (method_exists($product, 'is_downloadable')) ? $product->is_downloadable() : false;
			$value['woocommerce_is_virtual'] = $product->is_virtual();
			$value['woocommerce_is_sold_individually'] = $product->is_sold_individually();
			// $value['woocommerce_needs_shipping'] = $product->needs_shipping();
			$value['woocommerce_is_taxable'] = $product->is_taxable();
			$value['woocommerce_is_shipping_taxable'] = $product->is_shipping_taxable();

			// _d($value);
		}
	}
	
	return $value;
}


function acf_wc_input_admin_enqueue_scripts() {
	$settings = array(
		'path' => apply_filters('acf/helpers/get_path', __FILE__),
		'dir' => apply_filters('acf/helpers/get_dir', __FILE__),
		'version' => '1.0.3'
	);
	
	// register acf scripts
	wp_register_script( 'acf-wc-input-product-type-locations', $settings['dir'] . 'js/input.js', array('acf-input'), $settings['version'] );
	
	// scripts
	wp_enqueue_script(array('acf-wc-input-product-type-locations'));		
}

function wc_product_acf_location_rule_types($choices) {    
    $choices[__("Woocommerce")] = array(
    	'woocommerce_product_type' => __("Product Type", 'acf'),
    	'woocommerce_variations' => __("Product Variations", 'acf'),
    	'woocommerce_taxation' => __("Product Taxation", 'acf')
    );

    return $choices;
}

function wc_product_acf_location_rule_types_woocommerce_product_type($choices) {
	$choices = wc_get_product_types();
	
	return $choices;
}

function wc_product_acf_location_rule_types_woocommerce_variations($choices) {
	$choices = array(
		// 'is_in_stock'        => 'In Stock',
		'is_downloadable'     	=> 'Downloadable',
		'is_virtual'          	=> 'Virtual',
		'is_sold_individually'	=> 'Sold Individually',
	);

	return $choices;
}

function wc_product_acf_location_rule_types_woocommerce_taxation($choises) {
	$choices = array(
		'is_taxable'			=> 'Is Taxable',
		'is_shipping_taxable'	=> 'Is Shipping Taxable'
	);

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

function rule_match_woocommerce_bools($match, $rule, $options) {
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

	if(!array_key_exists('woocommerce_is_virtual', $options) && !array_key_exists('value', $rule)) {
		return false;
	}

	$key = 'woocommerce_' . $rule['value'];

	if($rule['operator'] == "==") {
		$match = ( $options[$key] === 1 );
	}
	elseif($rule['operator'] == "!=") {
		$match = ( $options[$key] !== 1 );
	}

	return $match;
}

function wc_product_acf_location_rule_types_create_field($fields) {
	$fields['choices']['woocommerce_products_general_tab'] = __('Woocommerce Products General Tab', 'acf');
	// if($fields['name'] == 'options[position]') {
	// 	_d($fields);
	// }

	return $fields;
}

?>
