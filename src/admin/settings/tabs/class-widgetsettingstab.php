<?php
/**
 * Interledger Web Monetization Module for Widget Settings Tab
 *
 * @package Interledger\WebMonetization
 */

namespace Interledger\WebMonetization\Admin\Settings\Tabs;

/**
 * Class GeneralTab
 *
 * @package Interledger\WebMonetization\Admin\Settings\Tabs
 */
class WidgetSettingsTab {

	/**
	 * Register the settings for the Widget tab.
	 */
	public static function register(): void {
		register_setting(
			'intlwemo_display',
			'intlwemo_custom_banner',
			array(
				'type'              => 'string',
				'sanitize_callback' => 'sanitize_text_field',
			)
		);

		wp_localize_script(
			'intlwemo-widget',
			'intlwemoBannerConfig',
			array(
				'nonce' => wp_create_nonce( 'intlwemo_save_banner_config' ),
			)
		);

		add_settings_section(
			'intlwemo_display_section',
			'',
			'__return_false',
			'intlwemo_display'
		);
	}
	/**
	 * Render the settings form.
	 */
	public static function render(): void {
		?>
		<div id="intlwemo-banner-app"></div>
		<?php
	}
	/**
	 * Save the banner configuration.
	 */
	public static function save_banner_config() {
		check_ajax_referer( 'intlwemo_save_banner_config' );

		$config = json_decode( sanitize_text_field( wp_unslash( $_POST['config'] ?? '{}' ) ), true );
		if ( ! is_array( $config ) ) {
			wp_send_json_error( 'Invalid config' );
		}

		update_option( 'intlwemo_banner_config', $config );
		wp_send_json_success();
	}
	/**
	 * Publish the banner configuration.
	 */
	public static function publish_banner_config() {
		check_ajax_referer( 'intlwemo_save_banner_config' );
		$config = json_decode( sanitize_text_field( wp_unslash( $_POST['config'] ?? '{}' ) ), true );
		if ( ! is_array( $config ) ) {
			wp_send_json_error( 'Invalid config' );
		}

		update_option( 'intlwemo_banner_published', $config );
		wp_send_json_success();
	}
}
