<?php
/**
 * WebMonetization Module for Beaver Builder
 *
 * @package WebMonetization
 */
namespace WebMonetization\Admin\Settings\Tabs;

/**
 * Class GeneralTab
 *
 * @package WebMonetization\Admin\Settings\Tabs
 */
class WidgetSettingsTab {

	/**
	 * Register the settings for the Widget tab.
	 */
	public static function register(): void {
		register_setting( 'webmonetization_display', 'webmonetization_custom_banner' );

		wp_localize_script(
			'wm-banner-tool',
			'wmBannerConfig',
			array(
				'nonce' => wp_create_nonce( 'wm_save_banner_config' ),
			)
		);

		add_settings_section(
			'webmonetization_display_section',
			'',
			'__return_false',
			'webmonetization_display'
		);
	}
	/**
	 * Render the settings form.
	 */
	public static function render(): void {
		?>
		<div id="wm-banner-app"></div>
		<?php
	}
	public static function save_banner_config() {
		check_ajax_referer( 'wm_save_banner_config' );

		$config = json_decode( wp_unslash( $_POST['config'] ?? '{}' ), true );
		if ( ! is_array( $config ) ) {
			wp_send_json_error( 'Invalid config' );
		}

		update_option( 'wm_banner_config', $config );
		wp_send_json_success();
	}
}
