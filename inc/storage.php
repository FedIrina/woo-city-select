<?php
/**
 * Cookie storage for selected city and region.
 *
 * @package Woo_City_Select
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Woo_City_Select_Storage {

	const COOKIE = 'woo_city_select';
	const DAYS   = 365;

	public static function init() {
		add_action( 'init', array( __CLASS__, 'ensure' ) );
	}

	public static function ensure() {
		$data = self::read_cookie();
		if ( null === $data ) {
			self::set( 'Москва', 'г Москва' );
		}
	}

	public static function get() {
		$data = self::read_cookie();
		if ( null === $data ) {
			return array(
				'city'   => 'Москва',
				'region' => 'г Москва',
			);
		}
		return $data;
	}

	public static function set( $city, $region ) {
		$value = rawurlencode(
			wp_json_encode(
				array(
					'city'   => $city,
					'region' => $region,
				)
			)
		);

		$expire = time() + self::DAYS * DAY_IN_SECONDS;
		setcookie( self::COOKIE, $value, $expire, '/', '', is_ssl(), true );
		$_COOKIE[ self::COOKIE ] = $value;
	}

	private static function read_cookie() {
		if ( empty( $_COOKIE[ self::COOKIE ] ) ) {
			return null;
		}

		$raw = rawurldecode( wp_unslash( $_COOKIE[ self::COOKIE ] ) );
		$data = json_decode( $raw, true );

		if ( ! is_array( $data ) || empty( $data['city'] ) || empty( $data['region'] ) ) {
			return null;
		}

		return array(
			'city'   => (string) $data['city'],
			'region' => (string) $data['region'],
		);
	}
}
