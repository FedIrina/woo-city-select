<?php
/**
 * Major cities list for the modal.
 *
 * @package Woo_City_Select
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Woo_City_Select_Cities {

	const OPTION = 'woo_city_select_cities';

	public static function init() {
	}

	public static function defaults() {
		return array(
			array(
				'city'   => 'Москва',
				'region' => 'г Москва',
			),
			array(
				'city'   => 'Санкт-Петербург',
				'region' => 'г Санкт-Петербург',
			),
			array(
				'city'   => 'Владивосток',
				'region' => 'Приморский край',
			),
			array(
				'city'   => 'Волгоград',
				'region' => 'Волгоградская обл',
			),
			array(
				'city'   => 'Воронеж',
				'region' => 'Воронежская обл',
			),
			array(
				'city'   => 'Екатеринбург',
				'region' => 'Свердловская обл',
			),
			array(
				'city'   => 'Иркутск',
				'region' => 'Иркутская обл',
			),
			array(
				'city'   => 'Казань',
				'region' => 'Респ Татарстан',
			),
			array(
				'city'   => 'Кемерово',
				'region' => 'Кемеровская область - Кузбасс',
			),
			array(
				'city'   => 'Краснодар',
				'region' => 'Краснодарский край',
			),
			array(
				'city'   => 'Красноярск',
				'region' => 'Красноярский край',
			),
			array(
				'city'   => 'Мурманск',
				'region' => 'Мурманская обл',
			),
			array(
				'city'   => 'Нижний Новгород',
				'region' => 'Нижегородская обл',
			),
			array(
				'city'   => 'Новокузнецк',
				'region' => 'Кемеровская область - Кузбасс',
			),
			array(
				'city'   => 'Новосибирск',
				'region' => 'Новосибирская обл',
			),
			array(
				'city'   => 'Омск',
				'region' => 'Омская обл',
			),
			array(
				'city'   => 'Пермь',
				'region' => 'Пермский край',
			),
			array(
				'city'   => 'Ростов-на-Дону',
				'region' => 'Ростовская обл',
			),
			array(
				'city'   => 'Самара',
				'region' => 'Самарская обл',
			),
			array(
				'city'   => 'Сургут',
				'region' => 'Ханты-Мансийский АО - Югра',
			),
			array(
				'city'   => 'Тула',
				'region' => 'Тульская обл',
			),
			array(
				'city'   => 'Тюмень',
				'region' => 'Тюменская обл',
			),
			array(
				'city'   => 'Уфа',
				'region' => 'Респ Башкортостан',
			),
			array(
				'city'   => 'Хабаровск',
				'region' => 'Хабаровский край',
			),
			array(
				'city'   => 'Челябинск',
				'region' => 'Челябинская обл',
			),
			array(
				'city'   => 'Ярославль',
				'region' => 'Ярославская обл',
			),
		);
	}

	public static function all() {
		$saved = get_option( self::OPTION, null );
		if ( null === $saved ) {
			return self::defaults();
		}

		if ( ! is_array( $saved ) ) {
			return self::defaults();
		}

		$list = array();
		foreach ( $saved as $row ) {
			if ( empty( $row['city'] ) || empty( $row['region'] ) ) {
				continue;
			}
			$list[] = array(
				'city'   => (string) $row['city'],
				'region' => (string) $row['region'],
			);
		}

		return $list;
	}
}
