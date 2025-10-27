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

	/**
	 * Register the settings page and its tabs.
	 */
	public static function register_settings(): void {
		GeneralTab::register();
		WidgetSettingsTab::register();
		AboutTab::register();
	}

	/**
	 * Render the settings page.
	 */
	public static function render(): void {
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$current_tab = isset( $_GET['tab'] )
			// phpcs:ignore WordPress.Security.NonceVerification.Recommended
			? sanitize_text_field( wp_unslash( $_GET['tab'] ) )
			: 'general';

		$tabs = array(
			'general' => 'General',
			'widget'  => 'Banner',
			'about'   => 'About',
		);

		echo '<div class="wrap">';

		self::render_header();

		echo '<nav class="nav-tab-wrapper">';
		foreach ( $tabs as $slug => $text ) {
			$active = $slug === $current_tab ? ' nav-tab-active' : '';
			$url    = admin_url( 'admin.php?page=' . self::PAGE_SLUG . '&tab=' . $slug );
			echo "<a class='nav-tab" . esc_attr( $active ) . "' href='" . esc_url( $url ) . "'>" . esc_attr( $text ) . '</a>';
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

	/**
	 * Render the header for the settings page.
	 */
	public static function render_header(): void {
		echo '<div class="wm-header">
				<div class="wm-header-inner">
					<img
						class="wm-logo"
						src="' . esc_attr( WEB_MONETIZATION_PLUGIN_DIR ) . 'assets/images/wm_logo.svg"
						alt="' . esc_html__( 'Web Monetization Settings', 'interledger-web-monetization-integration' ) . '"
					/>
					<h1 class="wm-title" >
						' . esc_html__( 'Web Monetization Settings', 'interledger-web-monetization-integration' ) . '
					</h1>
				</div>
			</div>';
	}

	/**
	 * Render the settings section heading.
	 *
	 * @param string $title       The title of the section.
	 * @param string $description The description of the section.
	 */
	public function render_settings_section_heading( string $title, string $description ): void {
		echo '<h2>' . esc_html( $title ) . '</h2>';
		echo '<p class="description">' . esc_html( $description ) . '</p>';
	}

	/**
	 * Render a text input field.
	 *
	 * @param string $id          The ID of the input field.
	 * @param string $name        The name of the input field.
	 * @param string $value       The value of the input field.
	 * @param string $placeholder The placeholder text for the input field.
	 */
	public function render_text_input_field( $id, $name, $value, $placeholder = '' ): void {
		printf(
			'<input type="text" id="%1$s" name="%2$s" value="%3$s" placeholder="%4$s" class="regular-text">',
			esc_attr( $id ),
			esc_attr( $name ),
			esc_attr( $value ),
			esc_attr( $placeholder )
		);
	}

	/**
	 * Render a hidden input field.
	 *
	 * @param string $id    The ID of the input field.
	 * @param string $name  The name of the input field.
	 * @param string $value The value of the input field.
	 * @param array  $options Additional options for the field.
	 */
	public function render_radio_switch_field( string $id, string $name, string $value, array $options ): void {
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
}
