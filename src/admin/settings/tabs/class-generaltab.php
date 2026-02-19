<?php
/**
 * Interledger Web Monetization Module for General Settings Tab
 *
 * @package Interledger\WebMonetization
 */

namespace Interledger\WebMonetization\Admin\Settings\Tabs;

use Interledger\WebMonetization\Admin\Rendering\FieldRenderer;

/**
 * Class GeneralTab
 *
 * @package Interledger\WebMonetization\Admin\Settings\Tabs
 */
class GeneralTab {

	/**
	 * Register the settings for the General tab.
	 */
	public static function register(): void {
		register_setting(
			'intlwemo_general',
			'intlwemo_enabled',
			array(
				'type'              => 'string',
				'sanitize_callback' => 'sanitize_text_field',
			)
		);
		register_setting(
			'intlwemo_general',
			'intlwemo_wallet_address',
			array(
				'type'              => 'string',
				'sanitize_callback' => 'sanitize_text_field',
			)
		);
		register_setting(
			'intlwemo_general',
			'intlwemo_wallet_address_connected',
			array(
				'type'              => 'string',
				'sanitize_callback' => 'sanitize_text_field',
			)
		);
		register_setting(
			'intlwemo_general',
			'intlwemo_enable_authors',
			array(
				'type'              => 'string',
				'sanitize_callback' => 'sanitize_text_field',
			)
		);
		register_setting(
			'intlwemo_general',
			'intlwemo_multi_wallets_option',
			array(
				'type'              => 'string',
				'sanitize_callback' => 'sanitize_text_field',
			)
		);
		register_setting(
			'intlwemo_general',
			'intlwemo_post_type_settings',
			array(
				'type'              => 'array',
				'sanitize_callback' => array( self::class, 'sanitize_post_type_settings' ),
			)
		);
		register_setting(
			'intlwemo_general',
			'intlwemo_banner_enabled',
			array(
				'type'              => 'string',
				'sanitize_callback' => 'sanitize_text_field',
			)
		);
		register_setting(
			'intlwemo_general',
			'intlwemo_enable_country_wallets',
			array(
				'type'              => 'string',
				'sanitize_callback' => 'sanitize_text_field',
			)
		);
		register_setting(
			'intlwemo_general',
			'intlwemo_wallet_address_overrides',
			array(
				'type'              => 'array',
				'sanitize_callback' => array( self::class, 'sanitize_wallet_overrides' ),
			)
		);
		register_setting(
			'intlwemo_settings_group',
			'intlwemo_wallet_address_overrides',
			array(
				'type'              => 'array',
				'sanitize_callback' => array( self::class, 'sanitize_wallet_overrides' ),
			)
		);

		add_settings_section(
			'intlwemo_general_section',
			'',
			'__return_false',
			'intlwemo_general'
		);

		add_settings_field(
			'intlwemo_enabled',
			__( 'Enable Web Monetization', 'interledger-web-monetization-integration' ),
			array( self::class, 'render_field_enabled' ),
			'intlwemo_general',
			'intlwemo_general_section'
		);

		add_settings_field(
			'intlwemo_wallet_address',
			__( 'Enter your wallet address', 'interledger-web-monetization-integration' ),
			array( self::class, 'render_field_wallet_address' ),
			'intlwemo_general',
			'intlwemo_general_section'
		);

		add_settings_field(
			'intlwemo_enable_authors',
			__( 'Enable Authors', 'interledger-web-monetization-integration' ),
			array( self::class, 'render_field_enable_authors' ),
			'intlwemo_general',
			'intlwemo_general_section'
		);

		add_settings_field(
			'intlwemo_multi_wallets_option',
			__( 'Set wallet behavior', 'interledger-web-monetization-integration' ),
			array( self::class, 'render_field_multi_wallets' ),
			'intlwemo_general',
			'intlwemo_general_section'
		);

		add_settings_field(
			'intlwemo_post_type_settings',
			__( 'Set up Web Monetization per post type', 'interledger-web-monetization-integration' ),
			array( self::class, 'render_post_type_settings' ),
			'intlwemo_general',
			'intlwemo_general_section'
		);
		add_settings_field(
			'intlwemo_banner_enabled',
			__( 'Enable the banner', 'interledger-web-monetization-integration' ),
			array( self::class, 'render_field_banner_enabled' ),
			'intlwemo_general',
			'intlwemo_general_section'
		);

		add_settings_field(
			'intlwemo_enable_country_wallets',
			__( 'Enable country-specific wallet addresses', 'interledger-web-monetization-integration' ),
			array( self::class, 'render_field_enable_country_wallets' ),
			'intlwemo_general',
			'intlwemo_general_section'
		);

		add_settings_field(
			'intlwemo_wallet_address_overrides',
			'',
			array( self::class, 'render_field_country_wallet_overrides_echo' ),
			'intlwemo_general',
			'intlwemo_general_section'
		);
	}

	/**
	 * Sanitize the wallet overrides input.
	 *
	 * @param array $input The input data.
	 * @return array Sanitized output.
	 */
	public static function sanitize_wallet_overrides( $input ) {
		$output = array();

		foreach ( $input as $index => $wallet_data ) {
			$country   = sanitize_text_field( $wallet_data['country'] ?? sanitize_text_field( $index ) );
			$wallet    = sanitize_text_field( $wallet_data['wallet'] ?? '' );
			$connected = isset( $wallet_data['connected'] ) ? (bool) $wallet_data['connected'] : false;

			if ( $country && $wallet ) {
				$output[ $country ] = array(
					'wallet'    => $wallet,
					'connected' => $connected ? '1' : '0',
				);
			}
		}

		return $output;
	}

	/**
	 * Sanitize the post type settings input.
	 *
	 * @param array $input The input data.
	 * @return array Sanitized output.
	 */
	public static function sanitize_post_type_settings( $input ) {
		if ( ! is_array( $input ) ) {
			return array();
		}

		$content_types  = get_post_types( array( 'public' => true ), 'objects' );
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

		$supported_post_types = array();
		foreach ( $content_types as $post_type ) {
			if ( ! in_array( $post_type->name, $excluded_types, true ) ) {
				$supported_post_types[] = $post_type->name;
			}
		}

		$output = array();

		foreach ( $input as $post_type => $settings ) {
			if ( ! is_array( $settings ) ) {
				continue;
			}

			$post_type = sanitize_key( $post_type );

			if ( ! in_array( $post_type, $supported_post_types, true ) ) {
				continue;
			}

			$enabled   = isset( $settings['enabled'] ) ? (bool) $settings['enabled'] : false;
			$wallet    = isset( $settings['wallet'] ) ? sanitize_text_field( $settings['wallet'] ) : '';
			$connected = isset( $settings['connected'] ) ? (bool) $settings['connected'] : false;

			$output[ $post_type ] = array(
				'enabled'   => $enabled,
				'wallet'    => $wallet,
				'connected' => $connected ? '1' : '0',
			);
		}

		return $output;
	}

	/**
	 * Render the "Enable country-specific wallet addresses" field.
	 */
	public static function render_field_enable_country_wallets(): void {
		$value = get_option( 'intlwemo_enable_country_wallets', 0 );

		$geoip_available      = function_exists( 'geoip_detect2_get_info_from_current_ip' );
		$cloudflare_available = '' !== sanitize_text_field( wp_unslash( $_SERVER['HTTP_CF_IPCOUNTRY'] ?? '' ) );

		$label = esc_html__( 'Enable country-specific wallet addresses. If enabled, you can set different wallet addresses based on the visitor\'s country.', 'interledger-web-monetization-integration' );
		if ( ! $geoip_available && ! $cloudflare_available ) {
			$label .= ' ' . esc_html__( 'Note: GeoIP and Cloudflare country detection are not available.', 'interledger-web-monetization-integration' );
		}
		FieldRenderer::render_checkbox(
			'intlwemo_enable_country_wallets',
			'intlwemo_enable_country_wallets',
			$value,
			$label
		);
	}

	/**
	 * Echo wrapper for render_field_country_wallet_overrides.
	 */
	public static function render_field_country_wallet_overrides_echo(): void {
		// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		echo self::render_field_country_wallet_overrides();
	}

	/**
	 * Render a row for the country wallet overrides table.
	 *
	 * @return string HTML for the row.
	 */
	public static function render_field_country_wallet_overrides(): string {
		$enabled = get_option( 'intlwemo_enable_country_wallets', '0' ) === '1';

		$wallet_overrides     = get_option( 'intlwemo_wallet_address_overrides', array() );
		$geoip_available      = function_exists( 'geoip_detect2_get_info_from_current_ip' );
		$cloudflare_available = '' !== sanitize_text_field( wp_unslash( $_SERVER['HTTP_CF_IPCOUNTRY'] ?? '' ) );

		ob_start();
		?>
		<div id="intlwemo-country-wallets-wrapper" style="<?php echo $enabled ? '' : 'display:none;'; ?>">

		<?php if ( ! $geoip_available && ! $cloudflare_available ) : ?>
			<div class="notice notice-warning inline">
				<p>
					<?php
					printf(
						wp_kses(
							// translators: %s is the URL to the GeoIP Detection plugin.
							__( 'Country detection requires the <a href="%s" target="_blank" rel="noopener">GeoIP Detection plugin</a>, or the site must be <a href="https://www.cloudflare.com/" target="_blank" rel="noopener">running behind Cloudflare</a>', 'interledger-web-monetization-integration' ),
							array(
								'a' => array(
									'href'   => array(),
									'target' => array(),
									'rel'    => array(),
								),
							)
						),
						esc_url( 'https://wordpress.org/plugins/geoip-detect/' )
					);
					?>
				</p>
			</div>
		<?php endif; ?>

		<table id="intlwemo-wallet-country-table" class="widefat striped intlwemo-post-type-settings">
			<thead>
				<tr>
					<th><?php esc_html_e( 'Country Code', 'interledger-web-monetization-integration' ); ?><br>
						<span class="description"><?php esc_html_e( '(e.g. US, GB, FR)', 'interledger-web-monetization-integration' ); ?></span>
					</th>
					<th><?php esc_html_e( 'Wallet Address', 'interledger-web-monetization-integration' ); ?></th>
					<th></th>
				</tr>
			</thead>
			<tbody>
				<?php
				// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
				echo self::render_wallet_country_row();
				if ( ! empty( $wallet_overrides ) ) {
					foreach ( $wallet_overrides as $country => $wallet ) {
						// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
						echo self::render_wallet_country_row( $country, $wallet );
					}
				}
				?>
			</tbody>
		</table>

		<p>
			<button type="button" class="button" id="intlwemo-add-wallet-country-row"><?php esc_html_e( 'Add New Country Wallet', 'interledger-web-monetization-integration' ); ?></button>
		</p>
		</div>
		<?php
		return ob_get_clean();
	}

	/**
	 * Render a single row for the country wallet overrides table.
	 *
	 * @param string $country The country code.
	 * @param array  $wallet  The wallet address.
	 *
	 * @return string HTML for the row.
	 */
	private static function render_wallet_country_row( string $country = '', array $wallet = array(
		'country'   => '',
		'wallet'    => '',
		'connected' => '0',
	) ): string {
		$is_connected = '1' === $wallet['connected'];
		ob_start();
		?>
		<tr style="<?php echo empty( $country ) ? 'display:none;' : ''; ?>">
			<td><input type="text" name="intlwemo_wallet_address_overrides[<?php echo esc_attr( strtoupper( $country ) ); ?>][country]" value="<?php echo esc_attr( strtoupper( $country ) ); ?>" maxlength="2" style="width: 80px;" /></td>
			<td class="widefat row"><input type="text" name="intlwemo_wallet_address_overrides[<?php echo esc_attr( strtoupper( $country ) ); ?>][wallet]" value="<?php echo esc_attr( $wallet['wallet'] ); ?>" <?php echo $is_connected ? 'readonly' : ''; ?> style="width: 400px;" /></td>
			<td><button type="button" class="button remove-row">×</button></td>
		</tr>
		<input type="hidden" name="intlwemo_wallet_address_overrides[<?php echo esc_attr( strtoupper( $country ) ); ?>][connected]" value="<?php echo esc_attr( $wallet['connected'] ? '1' : '0' ); ?>" />
		<?php
		return ob_get_clean();
	}


	/**
	 * Render the settings form.
	 */
	public static function render(): void {
		?>
		<form id="intlwemo-general-form" method="post" action="options.php">
			<?php

			settings_fields( 'intlwemo_general' );
			do_settings_sections( 'intlwemo_general' );
			submit_button();
			?>
		</form>
		<?php
	}

	/**
	 * Render the "Enable Web Monetization" field.
	 */
	public static function render_field_enabled(): void {
		$value = get_option( 'intlwemo_enabled', 1 );
		FieldRenderer::render_checkbox(
			'intlwemo_enabled',
			'intlwemo_enabled',
			$value,
			esc_html__( 'Enable Web Monetization globally.', 'interledger-web-monetization-integration' )
		);
	}

	/**
	 * Render the "Wallet Address" field.
	 */
	public static function render_field_wallet_address(): void {
		$wallet       = get_option( 'intlwemo_wallet_address', '' );
		$is_connected = get_option( 'intlwemo_wallet_address_connected', '' ) === '1';

		FieldRenderer::render_text_input(
			'intlwemo_wallet_address',
			'intlwemo_wallet_address',
			$wallet,
			'e.g. https://walletprovider.com/MyWallet',
			$is_connected
		);

		echo '<br> <p  class="description">' . esc_html__(
			'Multiple wallet addresses can be added here separated by a space',
			'interledger-web-monetization-integration'
		) . '</p>';

		FieldRenderer::render_hidden_input(
			'intlwemo_wallet_address_connected',
			'intlwemo_wallet_address_connected',
			$is_connected ? '1' : '0'
		);
	}

	/**
	 * Render the "Enable Authors" field.
	 */
	public static function render_field_enable_authors(): void {
		$value = get_option( 'intlwemo_enable_authors', 0 );

		$excluded_users_notice = '';
		$excluded_users        = get_option( 'intlwemo_excluded_authors', array() );
		if ( ! empty( $excluded_users ) ) {
			$is_only_one_author_excluded = count( $excluded_users ) === 1;
			$multiple_authors_text       = sprintf(
				/* translators: %d is the number of excluded authors */
				esc_html__( 'are %d authors', 'interledger-web-monetization-integration' ),
				count( $excluded_users )
			);
			$excluded_users_notice .= ' <br><span class="description">' .
				esc_html__( 'Note: There ', 'interledger-web-monetization-integration' ) .
				( $is_only_one_author_excluded ?
					esc_html__( 'is 1 author', 'interledger-web-monetization-integration' ) :
					$multiple_authors_text
				) .
				esc_html__( ' excluded from Web Monetization. You can view them on the - filtered - ', 'interledger-web-monetization-integration' ) .
				'<a href="' . admin_url( 'users.php?wm_excluded_filter=excluded' ) . '">Users page</a></span>';
		}
		FieldRenderer::render_checkbox(
			'intlwemo_enable_authors',
			'intlwemo_enable_authors',
			$value,
			esc_html__(
				'Let your authors enter their own wallet address.',
				'interledger-web-monetization-integration'
			) .
			'<br> <p  class="description">' . esc_html__(
				'Admins can disallow specific authors from the ',
				'interledger-web-monetization-integration'
			) .
			'<a href="' . admin_url( 'users.php' ) . '">' . esc_html__( 'Users page', 'interledger-web-monetization-integration' ) . '</a> </p>' . $excluded_users_notice
		);
	}

	/**
	 * Render the "Multi Wallets behavior" field.
	 */
	public static function render_field_multi_wallets(): void {
		$value = get_option( 'intlwemo_multi_wallets_option', 'one' );
		FieldRenderer::render_radio_switch(
			'intlwemo_multi_wallets_option',
			'intlwemo_multi_wallets_option',
			$value,
			array(
				// translators: %s is HTML markup for <strong>.
				'one' => wp_kses_post( 'Only use one wallet field (This option displays <strong>a single wallet address</strong> based on the following priority: article > post type >  author > site)', 'interledger-web-monetization-integration' ),
				// translators: %s is HTML markup for <strong>.
				'all' => wp_kses_post( 'Use all wallets fields (This option displays <strong>all wallet addresses that are defined</strong> including site, author, post type and article wallets)', 'interledger-web-monetization-integration' ),
			)
		);
		echo '<p class="description">' . esc_html__( 'Example:', 'interledger-web-monetization-integration' ) . ' <br>' .
			esc_html__( 'If you choose to only use one wallet field and if the author has their own wallet address, only that one will be used.', 'interledger-web-monetization-integration' ) . '<br>' .
			esc_html__( 'If you choose to use all wallets and if all of the wallets are defined, they will all be included and used simultaneously.', 'interledger-web-monetization-integration' ) .
		'</p>';
	}

	/**
	 * Render the "Enable Banner" field.
	 */
	public static function render_field_banner_enabled(): void {
		$value = get_option( 'intlwemo_banner_enabled', 1 );
		FieldRenderer::render_checkbox(
			'intlwemo_banner_enabled',
			'intlwemo_banner_enabled',
			$value,
			__( 'Show a customizable banner to introduce Web Monetization to your website visitors. You can customize the banner on the ', 'interledger-web-monetization-integration' ) .
			' <a href="' . admin_url( 'admin.php?page=interledger-web-monetization-settings&tab=widget' ) . '">' . __( 'Banner Settings', 'interledger-web-monetization-integration' ) . '</a> ' .
			__( 'page', 'interledger-web-monetization-integration' )
		);
	}

	/**
	 * Render the post type settings.
	 */
	public static function render_post_type_settings(): void {
		$settings      = get_option( 'intlwemo_post_type_settings', array() );
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
			if ( ! in_array( $type_name, $excluded_types, true ) ) {
				$supported_types[] = $post_type;
			}
		}
		echo '<p class="description">' . esc_html__( 'Enable Web Monetization per post type and provide a wallet address.', 'interledger-web-monetization-integration' ) . '</p>';

		echo '<br><table class="widefat striped intlwemo-post-type-settings">';
		echo '<thead>';
		echo '<tr>';
		echo '<th>' . esc_html__( 'Post Type', 'interledger-web-monetization-integration' ) . '</th>';
		echo '<th> ' . esc_html__( 'Wallet Address', 'interledger-web-monetization-integration' ) . '</th>';
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
			echo '<td  style="width: 80px;" >';
			printf(
				'<label><input type="checkbox" name="intlwemo_post_type_settings[%1$s][enabled]" value="1" %2$s> %3$s</label>',
				esc_attr( $key ),
				checked( $enabled, true, false ),
				esc_html( $label )
			);
			echo '</td>';
			echo '<td>';
			printf(
				'<input type="text" name="intlwemo_post_type_settings[%1$s][wallet]" value="%2$s" class="regular-text" placeholder="e.g. %3$s" %4$s>',
				esc_attr( $key ),
				esc_attr( $wallet ),
				esc_attr( $wa_placeholder ),
				$is_connected ? 'readonly' : ''
			);
			printf(
				'<input type="hidden" name="intlwemo_post_type_settings[%1$s][connected]" value="%2$s">',
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
