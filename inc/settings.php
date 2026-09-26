<?php
/**
 * WooCommerce settings tab.
 *
 * @package Woo_City_Select
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Woo_City_Select_Settings {

	const TAB     = 'woo_city_select';
	const TOKEN   = 'woo_city_select_dadata_token';
	const COUNTRY = 'woo_city_select_dadata_country';

	public static function init() {
		add_filter( 'woocommerce_settings_tabs_array', array( __CLASS__, 'add_tab' ), 50 );
		add_action( 'woocommerce_settings_tabs_' . self::TAB, array( __CLASS__, 'output' ) );
		add_action( 'woocommerce_update_options_' . self::TAB, array( __CLASS__, 'save' ) );
		add_action( 'admin_enqueue_scripts', array( __CLASS__, 'enqueue' ) );
		add_filter( 'plugin_action_links_' . plugin_basename( WOO_CITY_SELECT_FILE ), array( __CLASS__, 'action_links' ) );
	}

	public static function add_tab( $tabs ) {
		$tabs[ self::TAB ] = __( 'Выбор города', 'woo-city-select' );
		return $tabs;
	}

	public static function action_links( $links ) {
		$url = admin_url( 'admin.php?page=wc-settings&tab=' . self::TAB );
		array_unshift(
			$links,
			'<a href="' . esc_url( $url ) . '">' . esc_html__( 'Настройки', 'woo-city-select' ) . '</a>'
		);
		return $links;
	}

	public static function enqueue( $hook ) {
		if ( 'woocommerce_page_wc-settings' !== $hook ) {
			return;
		}
		if ( empty( $_GET['tab'] ) || self::TAB !== $_GET['tab'] ) {
			return;
		}

		wp_enqueue_script( 'jquery-ui-sortable' );
		wp_enqueue_style(
			'woo-city-select-settings',
			WOO_CITY_SELECT_URL . 'assets/css/settings.css',
			array(),
			'0.1.1'
		);
		wp_enqueue_script(
			'woo-city-select-settings',
			WOO_CITY_SELECT_URL . 'assets/js/settings.js',
			array( 'jquery', 'jquery-ui-sortable' ),
			'0.1.1',
			true
		);
		wp_localize_script(
			'woo-city-select-settings',
			'wooCitySelectSettings',
			array(
				'ajaxUrl' => admin_url( 'admin-ajax.php' ),
				'nonce'   => wp_create_nonce( 'woo_city_select' ),
				'i18n'    => array(
					'nothingFound'  => __( 'Ничего не найдено', 'woo-city-select' ),
					'notConfigured' => __( 'Укажите API-ключ DaData выше', 'woo-city-select' ),
					'unavailable'   => __( 'Подбор сейчас недоступен', 'woo-city-select' ),
				),
			)
		);
	}

	public static function fields() {
		$countries = array();
		if ( function_exists( 'WC' ) && WC()->countries ) {
			$countries = WC()->countries->get_countries();
		}

		return array(
			array(
				'title' => __( 'Выбор города', 'woo-city-select' ),
				'type'  => 'title',
				'id'    => 'woo_city_select_section',
			),
			array(
				'title'    => __( 'API-ключ DaData', 'woo-city-select' ),
				'desc'     => __( 'Одного API-ключа достаточно. Секретный ключ не нужен.', 'woo-city-select' ),
				'id'       => self::TOKEN,
				'type'     => 'text',
				'default'  => '',
				'desc_tip' => true,
			),
			array(
				'title'    => __( 'Страна поиска', 'woo-city-select' ),
				'id'       => self::COUNTRY,
				'type'     => 'select',
				'options'  => $countries,
				'default'  => 'RU',
				'desc_tip' => true,
			),
			array(
				'type' => 'sectionend',
				'id'   => 'woo_city_select_section',
			),
		);
	}

	public static function output() {
		woocommerce_admin_fields( self::fields() );
		self::output_cities_table();
	}

	public static function save() {
		woocommerce_update_options( self::fields() );
		self::save_cities();
	}

	public static function token() {
		return (string) get_option( self::TOKEN, '' );
	}

	public static function country() {
		$country = (string) get_option( self::COUNTRY, 'RU' );
		return '' !== $country ? $country : 'RU';
	}

	private static function output_cities_table() {
		$cities = get_option( Woo_City_Select_Cities::OPTION, null );
		if ( null === $cities ) {
			$cities = Woo_City_Select_Cities::defaults();
		}
		if ( ! is_array( $cities ) ) {
			$cities = array();
		}
		?>
		<h2><?php esc_html_e( 'Крупные города', 'woo-city-select' ); ?></h2>
		<p class="description"><?php esc_html_e( 'Города в модалке до поиска. Вводите название и выбирайте пункт из подсказок DaData — регион подставится сам. Перетаскивайте строки, чтобы изменить порядок.', 'woo-city-select' ); ?></p>
		<table class="widefat woo-city-select-cities" id="woo-city-select-cities">
			<thead>
				<tr>
					<th class="woo-city-select-cities__handle-col"></th>
					<th><?php esc_html_e( 'Город', 'woo-city-select' ); ?></th>
					<th><?php esc_html_e( 'Регион', 'woo-city-select' ); ?></th>
					<th></th>
				</tr>
			</thead>
			<tbody>
				<?php
				if ( empty( $cities ) ) {
					self::city_row( '', '' );
				} else {
					foreach ( $cities as $row ) {
						self::city_row(
							isset( $row['city'] ) ? $row['city'] : '',
							isset( $row['region'] ) ? $row['region'] : ''
						);
					}
				}
				?>
			</tbody>
		</table>
		<p>
			<button type="button" class="button" id="woo-city-select-add-city"><?php esc_html_e( 'Добавить город', 'woo-city-select' ); ?></button>
		</p>
		<script type="text/html" id="tmpl-woo-city-select-city-row">
			<?php self::city_row( '', '' ); ?>
		</script>
		<?php
	}

	private static function city_row( $city, $region ) {
		?>
		<tr class="woo-city-select-cities__row">
			<td class="woo-city-select-cities__handle"><span class="dashicons dashicons-menu"></span></td>
			<td class="woo-city-select-cities__city-cell">
				<input type="text" name="woo_city_select_cities_city[]" value="<?php echo esc_attr( $city ); ?>" class="regular-text woo-city-select-cities__city" autocomplete="off">
				<div class="woo-city-select-cities__suggest" hidden></div>
			</td>
			<td>
				<input type="text" name="woo_city_select_cities_region[]" value="<?php echo esc_attr( $region ); ?>" class="regular-text woo-city-select-cities__region" readonly>
			</td>
			<td>
				<button type="button" class="button-link-delete woo-city-select-cities__remove"><?php esc_html_e( 'Удалить', 'woo-city-select' ); ?></button>
			</td>
		</tr>
		<?php
	}

	private static function save_cities() {
		$cities  = isset( $_POST['woo_city_select_cities_city'] ) ? wp_unslash( $_POST['woo_city_select_cities_city'] ) : array();
		$regions = isset( $_POST['woo_city_select_cities_region'] ) ? wp_unslash( $_POST['woo_city_select_cities_region'] ) : array();

		if ( ! is_array( $cities ) ) {
			$cities = array();
		}
		if ( ! is_array( $regions ) ) {
			$regions = array();
		}

		$list = array();
		$count = max( count( $cities ), count( $regions ) );
		for ( $i = 0; $i < $count; $i++ ) {
			$city   = isset( $cities[ $i ] ) ? sanitize_text_field( $cities[ $i ] ) : '';
			$region = isset( $regions[ $i ] ) ? sanitize_text_field( $regions[ $i ] ) : '';
			if ( '' === $city || '' === $region ) {
				continue;
			}
			$list[] = array(
				'city'   => $city,
				'region' => $region,
			);
		}

		update_option( Woo_City_Select_Cities::OPTION, $list, false );
	}
}
