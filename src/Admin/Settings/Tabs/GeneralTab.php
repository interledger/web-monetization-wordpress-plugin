<?php
/**
 * WebMonetization Module for Beaver Builder
 *
 * @package WebMonetization
 */

namespace WebMonetization\Admin\Settings\Tabs;

use WebMonetization\Admin\Rendering\FieldRenderer;

/**
 * Class GeneralTab
 *
 * @package WebMonetization\Admin\Settings\Tabs
 */
class GeneralTab {

	/**
	 * Register the settings for the General tab.
	 */
	public static function register(): void {
		register_setting( 'webmonetization_general', 'wm_enabled' );
		register_setting( 'webmonetization_general', 'wm_wallet_address' );
		register_setting( 'webmonetization_general', 'wm_enable_authors' );
		register_setting( 'webmonetization_general', 'wm_multi_wallets_option' );
		register_setting( 'webmonetization_general', 'wm_post_type_settings' );
		register_setting( 'webmonetization_general', 'wm_banner_enabled' );

		add_settings_section(
			'webmonetization_general_section',
			'',
			'__return_false',
			'webmonetization_general'
		);

		add_settings_field(
			'wm_enabled',
			__( 'Enable Web Monetization', 'web-monetization' ),
			array( self::class, 'render_field_enabled' ),
			'webmonetization_general',
			'webmonetization_general_section'
		);

		add_settings_field(
			'wm_wallet_address',
			__( 'Enter your wallet address', 'web-monetization' ),
			array( self::class, 'render_field_wallet_address' ),
			'webmonetization_general',
			'webmonetization_general_section'
		);

		add_settings_field(
			'wm_enable_authors',
			__( 'Enable Authors', 'web-monetization' ),
			array( self::class, 'render_field_enable_authors' ),
			'webmonetization_general',
			'webmonetization_general_section'
		);

		add_settings_field(
			'wm_multi_wallets_option',
			__( 'Set wallet behavior', 'web-monetization' ),
			array( self::class, 'render_field_multi_wallets' ),
			'webmonetization_general',
			'webmonetization_general_section'
		);

		add_settings_field(
			'wm_post_type_settings',
			__( 'Set up Web Monetization per post type', 'web-monetization' ),
			array( self::class, 'render_post_type_settings' ),
			'webmonetization_general',
			'webmonetization_general_section'
		);
		add_settings_field(
			'wm_banner_enabled',
			__( 'Enable the banner', 'web-monetization' ),
			array( self::class, 'render_field_banner_enabled' ),
			'webmonetization_general',
			'webmonetization_general_section'
		);
	}

	/**
	 * Render the settings form.
	 */
	public static function render(): void {
		?>
		<form id="webmonetization_general_form" method="post" action="options.php">
			<?php

			settings_fields( 'webmonetization_general' );
			do_settings_sections( 'webmonetization_general' );
			submit_button();
			?>
		</form>
		<?php
	}

	/**
	 * Render the "Enable Web Monetization" field.
	 */
	public static function render_field_enabled(): void {
		$value = get_option( 'wm_enabled', 1 );
		FieldRenderer::render_checkbox(
			'wm_enabled',
			'wm_enabled',
			$value,
			esc_html__( 'Enable Web Monetization globally.', 'web-monetization' )
		);
	}

	/**
	 * Render the "Wallet Address" field.
	 */
	public static function render_field_wallet_address(): void {
		$wallet       = get_option( 'wm_wallet_address', '' );
		$is_connected = get_option( 'wm_wallet_address_connected', '' ) === '1';

		FieldRenderer::render_text_input(
			'wm_wallet_address',
			'wm_wallet_address',
			$wallet,
			'e.g. https://walletprovider.com/MyWallet',
			$is_connected
		);

		FieldRenderer::render_hidden_input(
			'wm_wallet_address_connected',
			'wm_wallet_address_connected',
			$is_connected ? '1' : '0'
		);
	}

	/**
	 * Render the "Enable Authors" field.
	 */
	public static function render_field_enable_authors(): void {
		$value = get_option( 'wm_enable_authors', 0 );

		$excluded_users_notice = '';
		$excluded_users        = get_option( 'wm_excluded_authors', array() );
		if ( ! empty( $excluded_users ) ) {
			$is_only_one_author_excluded = count( $excluded_users ) === 1;
			$multiple_authors_text = sprintf(
				esc_html__( 'are %d authors', 'web-monetization' ),
				count( $excluded_users )
			);
			$excluded_users_notice       = ' <br><span class="description">' .
				esc_html__( 'Note: There ', 'web-monetization' ) .
				( $is_only_one_author_excluded ?
					esc_html__( 'is 1 author', 'web-monetization' ) :
					$multiple_authors_text
				) .
				esc_html__( ' excluded from Web Monetization. You can view them on the - filtered - ', 'web-monetization' ) .
				'<a href="' . admin_url( 'users.php?wm_excluded_filter=excluded' ) . '">Users page</a></span>';
		}
		FieldRenderer::render_checkbox(
			'wm_enable_authors',
			'wm_enable_authors',
			$value,
			esc_html__(
				'Let your authors enter their own wallet address.'
			) .
			'<br> <p  class="description">' . esc_html__(
				'Admins can disallow specific authors from the ',
				'web-monetization'
			) .
			'<a href="' . admin_url( 'users.php' ) . '">' . esc_html__( 'Users page', 'web-monetization' ) . '</a> </p>' . $excluded_users_notice
		);
	}

	/**
	 * Render the "Multi Wallets behavior" field.
	 */
	public static function render_field_multi_wallets(): void {
		$value = get_option( 'wm_multi_wallets_option', 'one' );
		FieldRenderer::render_radio_switch(
			'wm_multi_wallets_option',
			'wm_multi_wallets_option',
			$value,
			array(
				'one' => wp_kses_post( 'Only use one wallet (This option displays <strong>a single wallet address</strong> based on the following priority: article > post type >  author > site)', 'web-monetization' ),
				'all' => wp_kses_post( 'Use all wallets (This option displays <strong>all wallet addresses that are defined</strong> including site, author, post type and article wallets)', 'web-monetization' ),
			)
		);
		echo '<p class="description">' . esc_html__( 'Example:', 'web-monetization' ) . ' <br>' .
			esc_html__( 'If you choose to only use one wallet and if the author has their own wallet address, only that one will be used.', 'web-monetization' ) . '<br>' .
			esc_html__( 'If you choose to use all wallets and if all of the wallets are defined, they will all be included and used simultaneously.', 'web-monetization' ) .
		'</p>';
	}

	/**
	 * Render the "Enable Banner" field.
	 */
	public static function render_field_banner_enabled(): void {
		$value = get_option( 'wm_banner_enabled', 1 );
		FieldRenderer::render_checkbox(
			'wm_banner_enabled',
			'wm_banner_enabled',
			$value,
			__( 'Show a customizable banner to introduce Web Monetization to your website visitors. You can customize the banner on the ', 'web-monetization' ) .
			' <a href="' . admin_url( 'admin.php?page=web-monetization-settings&tab=widget' ) . '">' . __( 'Banner Settings', 'web-monetization' ) . '</a> ' .
			__( 'page', 'web-monetization' )
		);
	}

	/**
	 * Render the post type settings.
	 */
	public static function render_post_type_settings(): void {
		$settings      = get_option( 'wm_post_type_settings', array() );
		$content_types = get_post_types( array( 'public' => true ), 'objects' );

		$excluded_types = array(
			'attachment',
			'custom_css',
			'customize_changeset',
			'revision',
			'nav_menu_item',
			'oembed_cache',
			'user_request',
			'wp_block',
		);

		$supported_types = array();

		foreach ( $content_types as $post_type ) {
			$type_name = $post_type->name;

			if (
				! in_array( $type_name, $excluded_types, true ) &&
				post_type_supports( $type_name, 'custom-fields' )
			) {
				$supported_types[] = $post_type;
			}
		}
		echo '<p class="description">' . __( 'Enable Web Monetization per post type and provide a wallet address.', 'web-monetization' ) . '</p>';

		echo '<br><table class="widefat striped wm-post-type-settings">';
		echo '<thead>';
		echo '<tr>';
		echo '<th>' . esc_html__( 'Post Type', 'web-monetization' ) . '</th>';
		echo '<th> ' . esc_html__( 'Wallet Address', 'web-monetization' ) . '</th>';
		echo '</tr>';
		echo '</thead>';
		echo '<tbody>';
		foreach ( $supported_types as $post_type ) {
			$key   = $post_type->name;
			$label = $post_type->labels->singular_name;

			$enabled      = isset( $settings[ $key ]['enabled'] ) ? (bool) $settings[ $key ]['enabled'] : false;
			$wallet       = isset( $settings[ $key ]['wallet'] ) ? $settings[ $key ]['wallet'] : '';
			$is_connected = isset( $settings[ $key ]['connected'] ) ? '1' === $settings[ $key ]['connected'] : false;

			$wa_placeholder = 'https://walletprovider.com/MyWallet';

			echo '<tr>';
			echo '<td>';
			printf(
				'<label><input type="checkbox" name="wm_post_type_settings[%1$s][enabled]" value="1" %2$s> %3$s</label>',
				esc_attr( $key ),
				checked( $enabled, true, false ),
				esc_html( $label )
			);
			echo '</td>';
			echo '<td>';
			printf(
				'<input type="text" name="wm_post_type_settings[%1$s][wallet]" value="%2$s" class="regular-text" placeholder="e.g. %3$s" %4$s>',
				esc_attr( $key ),
				esc_attr( $wallet ),
				esc_attr( $wa_placeholder ),
				$is_connected ? 'readonly' : ''
			);
			printf(
				'<input type="hidden" name="wm_post_type_settings[%1$s][connected]" value="%2$s">',
				esc_attr( $key ),
				esc_attr( $is_connected ? '1' : '0' )
			);
			echo '</td>';
			echo '</tr>';
		}
		echo '</tbody>';
		echo '</table>';
	}
}
