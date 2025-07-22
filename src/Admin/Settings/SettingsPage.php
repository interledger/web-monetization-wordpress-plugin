<?php
/**
 * WebMonetization Admin Settings Page Class
 *
 * @package WebMonetization
 */
namespace WebMonetization\Admin\Settings;

use WebMonetization\Admin\Settings\Tabs\GeneralTab;
use WebMonetization\Admin\Settings\Tabs\WidgetSettingsTab;
use WebMonetization\Admin\Settings\Tabs\AboutTab;


/**
 * Class SettingsPage
 *
 * Handles the registration and rendering of the settings page.
 */
class SettingsPage {

	const PAGE_SLUG = 'web-monetization-settings';



	public static function register_settings(): void {
		GeneralTab::register();
		WidgetSettingsTab::register();
		AboutTab::register();
	}

	public static function render(): void {
		$current_tab = $_GET['tab'] ?? 'general';
		$tabs        = array(
			'general' => __( 'General', 'web-monetization' ),
			'widget'  => __( 'Banner', 'web-monetization' ),
			'about'   => __( 'About', 'web-monetization' ),
		);

		echo '<div class="wrap">';

		self::renderHeader();

		echo '<nav class="nav-tab-wrapper">';
		foreach ( $tabs as $slug => $label ) {
			$active = $slug === $current_tab ? ' nav-tab-active' : '';
			$url    = admin_url( 'admin.php?page=' . self::PAGE_SLUG . '&tab=' . $slug );
			echo "<a class='nav-tab$active' href='" . esc_url( $url ) . "'>$label</a>";
		}
		echo '</nav>';

		switch ( $current_tab ) {
			case 'about':
				AboutTab::render();
				break;
			case 'widget':
				WidgetSettingsTab::render();
				break;
			case 'general':
			default:
				GeneralTab::render();
				break;
		}

		echo '</div>';
	}

	public static function renderHeader(): void {
		echo '<div class="wm-header">
				<div class="wm-header-inner">
					<img
						class="wm-logo"
						src="' . WEB_MONETIZATION_PLUGIN_DIR . 'assets/images/wm_logo.svg"
						alt="' . esc_html__( 'Web Monetization Settings', 'web-monetization' ) . '"
					/>
					<h1 class="wm-title" >
						' . esc_html__( 'Web Monetization Settings', 'web-monetization' ) . '
					</h1>
				</div>
			</div>';
	}

	function render_settings_section_heading( string $title, string $description ): void {
		echo '<h2>' . esc_html( $title ) . '</h2>';
		echo '<p class="description">' . esc_html( $description ) . '</p>';
	}
	function render_text_input_field( $id, $name, $value, $placeholder = '' ): void {
		printf(
			'<input type="text" id="%1$s" name="%2$s" value="%3$s" placeholder="%4$s" class="regular-text">',
			esc_attr( $id ),
			esc_attr( $name ),
			esc_attr( $value ),
			esc_attr( $placeholder )
		);
	}

	function render_radio_switch_field( string $id, string $name, string $value, array $options ): void {
		echo '<fieldset>';
		foreach ( $options as $option_value => $label ) {
			printf(
				'<label><input type="radio" name="%1$s" value="%2$s" %3$s /> %4$s</label><br>',
				esc_attr( $name ),
				esc_attr( $option_value ),
				checked( $value, $option_value, false ),
				esc_html( $label )
			);
		}
		echo '</fieldset>';
	}

	function render_settings_section( string $title, string $description ): void {
		echo '<div class="wm-settings-section">';
		echo '<h2>' . esc_html( $title ) . '</h2>';
		echo '<p class="description">' . esc_html( $description ) . '</p>';
		echo '</div>';
	}
	function render_settings_section_end(): void {
		echo '</div>';
	}
	function render_settings_section_start( string $title, string $description ): void {
		echo '<div class="wm-settings-section">';
		echo '<h2>' . esc_html( $title ) . '</h2>';
		echo '<p class="description">' . esc_html( $description ) . '</p>';
		echo '<div class="wm-settings-section-content">';
	}
	function render_settings_section_start_end( string $title, string $description ): void {
		echo '<div class="wm-settings-section">';
		echo '<h2>' . esc_html( $title ) . '</h2>';
		echo '<p class="description">' . esc_html( $description ) . '</p>';
		echo '<div class="wm-settings-section-content">';
		echo '</div>';
		echo '</div>';
		echo '<div class="wm-settings-section">';
		echo '<h2>' . esc_html( $title ) . '</h2>';
		echo '<p class="description">' . esc_html( $description ) . '</p>';
		echo '<div class="wm-settings-section-content">';
		echo '</div>';
		echo '</div>';
	}
}
