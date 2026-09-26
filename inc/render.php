<?php
/**
 * Frontend trigger and modal markup.
 *
 * @package Woo_City_Select
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Woo_City_Select_Render {

	private static $used = false;

	public static function init() {
		add_shortcode( 'woo_city_select', array( __CLASS__, 'shortcode' ) );
		add_action( 'wp_footer', array( __CLASS__, 'render_dialog' ), 15 );
		add_action( 'wp_footer', array( __CLASS__, 'maybe_enqueue' ), 19 );
	}

	public static function shortcode() {
		return self::render_trigger();
	}

	public static function render_trigger() {
		self::$used = true;
		$data       = Woo_City_Select_Storage::get();

		ob_start();
		?>
		<a class="woo-city-select" href="#" role="button">
			<span class="woo-city-select__name"><?php echo esc_html( $data['city'] ); ?></span>
		</a>
		<?php
		return ob_get_clean();
	}

	public static function maybe_enqueue() {
		if ( ! self::$used ) {
			return;
		}

		wp_enqueue_style(
			'woo-city-select-modal',
			WOO_CITY_SELECT_URL . 'assets/css/modal.css',
			array(),
			'0.1.4'
		);

		wp_enqueue_script(
			'woo-city-select-modal',
			WOO_CITY_SELECT_URL . 'assets/js/modal.js',
			array(),
			'0.1.4',
			true
		);

		wp_localize_script(
			'woo-city-select-modal',
			'wooCitySelect',
			array(
				'ajaxUrl' => admin_url( 'admin-ajax.php' ),
				'nonce'   => wp_create_nonce( 'woo_city_select' ),
				'i18n'    => array(
					'nothingFound'   => __( 'Ничего не найдено', 'woo-city-select' ),
					'notConfigured'  => __( 'Поиск не настроен', 'woo-city-select' ),
					'unavailable'    => __( 'Подбор сейчас недоступен', 'woo-city-select' ),
				),
			)
		);
	}

	public static function render_dialog() {
		if ( ! self::$used ) {
			return;
		}

		$cities = Woo_City_Select_Cities::all();
		?>
		<dialog id="woo-city-select-dialog" class="woo-city-select-modal">
			<button type="button" class="woo-city-select-modal__close" aria-label="<?php esc_attr_e( 'Закрыть', 'woo-city-select' ); ?>">
				<svg width="14" height="14" viewBox="0 0 16 16" aria-hidden="true" focusable="false">
					<path fill="currentColor" d="M.293.293a1 1 0 0 1 1.414 0L8 6.586 14.293.293a1 1 0 1 1 1.414 1.414L9.414 8l6.293 6.293a1 1 0 0 1-1.414 1.414L8 9.414l-6.293 6.293a1 1 0 0 1-1.414-1.414L6.586 8 .293 1.707a1 1 0 0 1 0-1.414z"/>
				</svg>
			</button>
			<p class="woo-city-select-modal__title"><?php esc_html_e( 'Выберите город', 'woo-city-select' ); ?></p>
			<div class="woo-city-select-modal__search-row">
				<input
					type="search"
					class="woo-city-select-modal__search"
					placeholder="<?php esc_attr_e( 'Найти город', 'woo-city-select' ); ?>"
					autocomplete="off"
				>
				<span class="woo-city-select-modal__search-icon" aria-hidden="true">
					<svg width="16" height="16" viewBox="0 0 17 16" focusable="false">
						<path fill="currentColor" fill-rule="evenodd" clip-rule="evenodd" d="M6.707 1.5a5.182 5.182 0 100 10.364 5.182 5.182 0 000-10.364zM.025 6.682a6.682 6.682 0 1111.908 4.165l3.873 3.873a.75.75 0 11-1.06 1.06l-3.874-3.873A6.682 6.682 0 01.025 6.682z"/>
					</svg>
				</span>
			</div>
			<p class="woo-city-select-modal__error" hidden></p>
			<div class="woo-city-select-modal__list" data-woo-city-select-major>
				<?php foreach ( $cities as $row ) : ?>
					<button
						type="button"
						class="woo-city-select-modal__item"
						data-city="<?php echo esc_attr( $row['city'] ); ?>"
						data-region="<?php echo esc_attr( $row['region'] ); ?>"
					>
						<?php echo esc_html( $row['city'] ); ?>
					</button>
				<?php endforeach; ?>
			</div>
			<div class="woo-city-select-modal__list" data-woo-city-select-suggest hidden></div>
		</dialog>
		<?php
	}
}
