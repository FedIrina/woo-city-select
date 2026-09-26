<?php
/**
 * Prefill checkout city and region from cookie.
 *
 * @package Woo_City_Select
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Woo_City_Select_Checkout {

	public static function init() {
		add_filter( 'woocommerce_checkout_get_value', array( __CLASS__, 'fill' ), 10, 2 );
	}

	public static function fill( $value, $input ) {
		$city_keys   = array( 'shipping_city', 'billing_city' );
		$region_keys = array( 'shipping_state', 'billing_state' );

		if ( '' !== $value && null !== $value ) {
			return $value;
		}

		$data = Woo_City_Select_Storage::get();

		if ( in_array( $input, $city_keys, true ) ) {
			return $data['city'];
		}

		if ( in_array( $input, $region_keys, true ) ) {
			return $data['region'];
		}

		return $value;
	}
}
