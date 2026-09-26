<?php
/**
 * Plugin Name: Woo City Select
 * Description: Выбор города: вывод блока, сохранение выбора и передача города в доставку WooCommerce.
 * Version: 0.1.0
 * Author: Irina Fedorova
 * Text Domain: woo-city-select
 *
 * @package Woo_City_Select
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'WOO_CITY_SELECT_FILE', __FILE__ );
define( 'WOO_CITY_SELECT_DIR', __DIR__ );
define( 'WOO_CITY_SELECT_URL', plugin_dir_url( __FILE__ ) );

class Woo_City_Select {

	public static function init() {
		add_action( 'plugins_loaded', array( __CLASS__, 'boot' ) );
	}

	public static function boot() {
		load_plugin_textdomain( 'woo-city-select', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );
		self::includes();

		Woo_City_Select_Storage::init();
		Woo_City_Select_Cities::init();
		Woo_City_Select_Render::init();
		Woo_City_Select_Ajax::init();
		Woo_City_Select_Dadata::init();

		if ( class_exists( 'WooCommerce' ) ) {
			Woo_City_Select_Checkout::init();
			Woo_City_Select_Settings::init();
		}
	}

	private static function includes() {
		require_once WOO_CITY_SELECT_DIR . '/inc/storage.php';
		require_once WOO_CITY_SELECT_DIR . '/inc/cities.php';
		require_once WOO_CITY_SELECT_DIR . '/inc/render.php';
		require_once WOO_CITY_SELECT_DIR . '/inc/ajax.php';
		require_once WOO_CITY_SELECT_DIR . '/inc/checkout.php';
		require_once WOO_CITY_SELECT_DIR . '/inc/settings.php';
		require_once WOO_CITY_SELECT_DIR . '/inc/dadata.php';
	}
}

Woo_City_Select::init();
