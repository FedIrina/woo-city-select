<?php
/**
 * AJAX save selected city.
 *
 * @package Woo_City_Select
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Woo_City_Select_Ajax {

	public static function init() {
		add_action( 'wp_ajax_woo_city_select_save', array( __CLASS__, 'save' ) );
		add_action( 'wp_ajax_nopriv_woo_city_select_save', array( __CLASS__, 'save' ) );
	}

	public static function save() {
		check_ajax_referer( 'woo_city_select', 'nonce' );

		$city   = isset( $_POST['city'] ) ? sanitize_text_field( wp_unslash( $_POST['city'] ) ) : '';
		$region = isset( $_POST['region'] ) ? sanitize_text_field( wp_unslash( $_POST['region'] ) ) : '';

		if ( '' === $city || '' === $region ) {
			wp_send_json_error(
				array(
					'message' => __( 'Укажите город и регион', 'woo-city-select' ),
				),
				400
			);
		}

		Woo_City_Select_Storage::set( $city, $region );

		wp_send_json_success(
			array(
				'city' => $city,
			)
		);
	}
}
