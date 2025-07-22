<?php
/**
 * Web Monetization Plugin
 *
 * @package WebMonetization
 */

namespace WebMonetization\Admin;

use WebMonetization\Admin\Settings\SettingsPage;
use WebMonetization\Admin\Settings\Tabs\WidgetSettingsTab;
use WebMonetization\Admin\UserMeta;

/**
 * Admin class for handling admin-related functionality.
 */
class Admin {
	const PAGE_SLUG = 'web-monetization-settings';
	/**
	 * Constructor.
	 */
	public function register_hooks(): void {

		add_action( 'admin_head', array( $this, 'inline_logo_menu_icon' ) );
		add_action( 'admin_menu', array( $this, 'register_menu' ) );
		add_action( 'admin_init', array( SettingsPage::class, 'register_settings' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_assets' ) );

		add_action(
			'plugin_action_links_web-monetization-wordpress-plugin/interledger-web-monetization-wordpress-plugin.php',
			array( $this, 'plugin_row_actions' ),
			10,
			3
		);

		add_action( 'wp_ajax_save_wm_banner_config', array( WidgetSettingsTab::class, 'save_banner_config' ) );

		add_action( 'add_meta_boxes', array( $this, 'add_wallet_address_meta_box' ) );

		add_action( 'save_post', array( $this, 'save_post' ), 10, 2 );

		UserMeta::register_hooks();
	}
	public function save_post( $post_id ): void {
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return;
		}

		if ( isset( $_POST['wm_wallet_address'] ) ) {
			update_post_meta( $post_id, 'wm_wallet_address', sanitize_text_field( $_POST['wm_wallet_address'] ) );
		}

		update_post_meta( $post_id, 'wm_disabled', sanitize_text_field( $_POST['wm_disabled'] ?? '' ) );
	}

	public function plugin_row_actions( $links ) {
		array_unshift( $links, '<a href="/wp-admin/admin.php?page=' . self::PAGE_SLUG . '">Settings</a>' );
		return $links;
	}

	public static function register_menu(): void {
		add_menu_page(
			__( 'Web Monetization Settings', 'web-monetization' ), // Page title
			__( 'Web Monetization', 'web-monetization' ),          // Menu title
			'manage_options',                             // Capability
			self::PAGE_SLUG,                              // Menu slug
			array( SettingsPage::class, 'render' ),                      // Callback to render page
			'', // Path to SVG icon
			60                                             // Menu position (optional)
		);
	}
	/**
	 * Add a meta box for wallet address.
	 *
	 * @param string $post_type The post type.
	 */
	public function add_wallet_address_meta_box( $post_type ): void {
		global $post;
		if ( current_user_can( 'author' ) && ! get_option( 'wm_enable_authors' ) ) {
			return;

		}
		$excluded    = get_option( 'wm_excluded_authors', array() );
		$is_excluded = in_array( $post->post_author, $excluded, false );
		if ( $is_excluded ) {
			return;
		}
		add_meta_box(
			'wm_wallet_address',
			__( 'Web Monetization', 'web-monetization' ),
			array( $this, 'render_wallet_address_meta_box' ),
			$post_type,
			'side',
			'high'
		);
	}

	public function render_wallet_address_meta_box( $post ): void {
		$wallet_address = get_post_meta( $post->ID, 'wm_wallet_address', true );
		$wm_disabled    = get_post_meta( $post->ID, 'wm_disabled', true );

		wp_nonce_field( 'wm_save_wallet_address', 'wm_wallet_address_nonce' );

		echo '<p>';
		echo '<label for="wm_wallet_address">' . __( 'Wallet Address:', 'web-monetization' ) . '</label>';
		echo '<input type="text" id="wm_wallet_address" name="wm_wallet_address" value="' . esc_attr( $wallet_address ) . '" class="widefat" />';
		echo '</p>';

		echo '<p>';
		echo '<label for="wm_disabled">';
		echo '<input type="checkbox" id="wm_disabled" name="wm_disabled" value="1" ' . checked( $wm_disabled === '1', true, false ) . ' />';
		echo ' ' . __( 'Disable Web Monetization for this post', 'web-monetization' );
		echo '</label>';
		echo '</p>';
	}


	private function get_inline_svg( $path ) {
		if ( ! file_exists( $path ) ) {
			echo '<!-- SVG file not found: ' . esc_html( $path ) . ' -->';
			return '';
		}
		return file_exists( $path ) ? file_get_contents( $path ) : '';
	}

	public function inline_logo_menu_icon(): void {
		if ( get_current_screen()->base === 'toplevel_page_web-monetization' ) {
			echo '<style>#adminmenu li a.toplevel_page_web-monetization-settings .wp-menu-image:before { display: none; }</style>';
		}
		echo '<script>
			document.addEventListener("DOMContentLoaded", function() {
				const img = document.querySelector("#adminmenu li a.toplevel_page_web-monetization-settings .wp-menu-image");
				if (img) {
					img.innerHTML = `' . $this->get_inline_svg( WEB_MONETIZATION_PLUGIN_PATH . 'assets/images/wm_logo_mono.svg' ) . '`;
				}
			});
		</script>';
	}

	/**
	 * Enqueue admin assets.
	 *
	 * @param string $hook The current admin page hook.
	 */
	public function enqueue_admin_assets( $hook ): void {

		wp_enqueue_style(
			'wm-admin-style',
			plugin_dir_url( dirname( __DIR__, 1 ) ) . 'build/admin.css',
			array(),
			'1.0.0'
		);
		// Only enqueue on your plugin's settings page

		if ( $hook !== 'toplevel_page_web-monetization-settings' ) {
			return;
		}

		wp_enqueue_script(
			'wm-admin-script',
			plugin_dir_url( dirname( __DIR__, 1 ) ) . 'build/admin.js',
			array(),
			'1.0.0',
			false
		);

		$config = get_option( 'wm_banner_config', array() );

		wp_localize_script(
			'wm-admin-script',
			'wm',
			array(
				'wmBannerConfig' => wp_json_encode(
					array(
						'nonce'  => wp_create_nonce( 'wm_save_banner_config' ),
						'config' => $config,
					)
				),
			),
			'after'
		);
		wp_enqueue_script( 'wm-admin-script' );

		wp_enqueue_script(
			'wm-widget',
			plugin_dir_url( dirname( __DIR__, 1 ) ) . 'build/widget.js',
			array( 'wp-components', 'wp-element', 'wp-i18n' ),
			'1.0.0' . rand( 1, 1000 ),
			true
		);

		wp_enqueue_style(
			'wm-widget-style',
			plugin_dir_url( dirname( __DIR__, 1 ) ) . 'build/widget.css',
			array(),
			'1.0.0'
		);
	}
}
