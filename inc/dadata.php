<?php
/**
 * DaData city suggestions.
 *
 * @package Woo_City_Select
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Woo_City_Select_Dadata {

	const ENDPOINT = 'https://suggestions.dadata.ru/suggestions/api/4_1/rs/suggest/address';

	public static function init() {
		add_action( 'wp_ajax_woo_city_select_search', array( __CLASS__, 'search' ) );
		add_action( 'wp_ajax_nopriv_woo_city_select_search', array( __CLASS__, 'search' ) );
	}

	public static function search() {
		check_ajax_referer( 'woo_city_select', 'nonce' );

		$token = Woo_City_Select_Settings::token();
		if ( current_user_can( 'manage_woocommerce' ) && ! empty( $_POST['token'] ) ) {
			$token = sanitize_text_field( wp_unslash( $_POST['token'] ) );
		}

		if ( '' === $token ) {
			wp_send_json_error(
				array(
					'code'    => 'not_configured',
					'message' => __( 'Поиск не настроен', 'woo-city-select' ),
				),
				400
			);
		}

		$query = isset( $_POST['query'] ) ? sanitize_text_field( wp_unslash( $_POST['query'] ) ) : '';
		if ( strlen( $query ) < 3 ) {
			wp_send_json_success(
				array(
					'items' => array(),
				)
			);
		}

		$country = Woo_City_Select_Settings::country();
		if ( current_user_can( 'manage_woocommerce' ) && ! empty( $_POST['country'] ) ) {
			$country = sanitize_text_field( wp_unslash( $_POST['country'] ) );
		}
		$body    = wp_json_encode(
			array(
				'query'          => $query,
				'count'          => 10,
				'from_bound'     => array( 'value' => 'city' ),
				'to_bound'       => array( 'value' => 'settlement' ),
				'restrict_value' => true,
				'locations'      => array(
					array(
						'country_iso_code' => $country,
					),
				),
			)
		);

		$response = wp_remote_post(
			self::ENDPOINT,
			array(
				'timeout' => 10,
				'headers' => array(
					'Content-Type'  => 'application/json',
					'Accept'        => 'application/json',
					'Authorization' => 'Token ' . $token,
				),
				'body'    => $body,
			)
		);

		if ( is_wp_error( $response ) ) {
			wp_send_json_error(
				array(
					'code'    => 'unavailable',
					'message' => __( 'Подбор сейчас недоступен', 'woo-city-select' ),
				),
				502
			);
		}

		$code = wp_remote_retrieve_response_code( $response );
		if ( $code < 200 || $code >= 300 ) {
			wp_send_json_error(
				array(
					'code'    => 'unavailable',
					'message' => __( 'Подбор сейчас недоступен', 'woo-city-select' ),
				),
				502
			);
		}

		$payload = json_decode( wp_remote_retrieve_body( $response ), true );
		$items   = array();

		if ( ! empty( $payload['suggestions'] ) && is_array( $payload['suggestions'] ) ) {
			foreach ( $payload['suggestions'] as $suggestion ) {
				$data = isset( $suggestion['data'] ) ? $suggestion['data'] : array();
				if ( ! empty( $data['street'] ) || ! empty( $data['house'] ) ) {
					continue;
				}
				$fias_level = isset( $data['fias_level'] ) ? (string) $data['fias_level'] : '';
				if ( '' !== $fias_level && ! in_array( $fias_level, array( '4', '6' ), true ) ) {
					continue;
				}
				$city = '';
				if ( ! empty( $data['settlement'] ) ) {
					$city = $data['settlement'];
				} elseif ( ! empty( $data['city'] ) ) {
					$city = $data['city'];
				}
				$region = isset( $data['region_with_type'] ) ? $data['region_with_type'] : '';
				if ( '' === $city || '' === $region ) {
					continue;
				}
				$items[] = array(
					'city'   => $city,
					'region' => $region,
					'label'  => $city . ', ' . $region,
				);
			}
		}

		wp_send_json_success(
			array(
				'items' => $items,
			)
		);
	}
}
