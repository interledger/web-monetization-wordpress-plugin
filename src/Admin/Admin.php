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
			'plugin_action_links_' . plugin_basename( plugin_dir_path( dirname( __DIR__ ) ) . '/interledger-web-monetization-wordpress-plugin.php' ),
			array( $this, 'plugin_row_actions' )
		);

		add_action( 'wp_ajax_save_wm_banner_config', array( WidgetSettingsTab::class, 'save_banner_config' ) );
		add_action( 'wp_ajax_publish_wm_banner_config', array( WidgetSettingsTab::class, 'publish_banner_config' ) );
		add_action( 'wp_ajax_save_wallet_connection',  array( $this, 'save_wallet_connection_callback' ) );


		add_action( 'add_meta_boxes', array( $this, 'add_wallet_address_meta_box' ) );

		add_action( 'save_post', array( $this, 'save_post' ), 10, 2 );

		UserMeta::register_hooks();
	}


	public function save_wallet_connection_callback(): void {
		check_ajax_referer( 'wallet_connect_nonce' , 'nonce' );

		$wallet_field = isset( $_POST['wallet_field'] ) ? sanitize_text_field( wp_unslash( $_POST['wallet_field'] ) ) : '';
		if ( empty( $wallet_field ) ) {
			wp_send_json_error( 'Invalid wallet address' );
		}
		if(strpos( $wallet_field, 'wm_post_type_settings' ) === 0) {
			$this->update_connected_option( 'wm_post_type_settings' ,  $wallet_field);
		} else {
			update_option( $wallet_field . '_connected', '1' );
		}
		
		wp_send_json_success();
	}

	private function update_connected_option( string $option_name, string $string_field ): void {
		
		$settings = get_option( 'wm_post_type_settings', array() );

		preg_match_all('/\[([^\]]+)\]/', $string_field, $matches);
		$keys = $matches[1]; // ['post', 'wallet']

		$type = $keys[0] ?? '';

		if ( '' !== $type && isset( $settings[ $type ] ) ) {
			$settings[ $type ]['connected'] = '1';
			update_option( 'wm_post_type_settings', $settings );
		}
	}

	/**
	 * Save post WM meta data.
	 *
	 * @param int $post_id The post ID.
	 */
	public function save_post( $post_id ): void {
		if ( ! isset( $_POST['wm_wallet_address_post_nonce'] ) ||
			! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['wm_wallet_address_post_nonce'] ) ), 'save_post' ) ) {
			return;
		}
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return;
		}
		if ( wp_is_post_revision( $post_id ) || wp_is_post_autosave( $post_id ) ) {
			return;
		}
		if ( isset( $_POST['wm_wallet_address'] ) ) {
			update_post_meta( $post_id, 'wm_wallet_address', sanitize_text_field( wp_unslash( $_POST['wm_wallet_address'] ) ) );
		}

		if ( isset( $_POST['wm_wallet_address_connected'] ) ) {
			update_post_meta( $post_id, 'wm_wallet_address_connected', sanitize_text_field( wp_unslash( $_POST['wm_wallet_address_connected'] ) ) );
		}

		if ( isset( $_POST['wm_disabled'] ) ) {
			update_post_meta( $post_id, 'wm_disabled', '1' );
		} elseif ( isset( $_POST['_wp_http_referer'] ) ) {
			delete_post_meta( $post_id, 'wm_disabled' );
		}
	}

	/**
	 * Add a link to the plugin settings page in the plugin row actions.
	 *
	 * @param array $links The existing plugin action links.
	 * @return array The modified plugin action links.
	 */
	public function plugin_row_actions( $links ) {
		array_unshift( $links, '<a href="/wp-admin/admin.php?page=' . self::PAGE_SLUG . '">Settings</a>' );
		return $links;
	}


	/**
	 * Register the admin menu for the Web Monetization settings.
	 *
	 * @return void
	 */
	public static function register_menu(): void {
		add_menu_page(
			__( 'Web Monetization Settings', 'web-monetization' ),
			__( 'Web Monetization', 'web-monetization' ),
			'manage_options',
			self::PAGE_SLUG,
			array( SettingsPage::class, 'render' ),
			'', // Path to SVG icon.
			60                                             // Menu position (optional).
		);
	}
	/**
	 * Add a meta box for wallet address.
	 *
	 * @param string $post_type The post type.
	 */
	public function add_wallet_address_meta_box( $post_type ): void {
		global $post;
		if (
			current_user_can( 'publish_posts' ) &&
			! current_user_can( 'edit_others_posts' ) &&
			! get_option( 'wm_enable_authors' )
		) {
			return;
		}

		$excluded    = get_option( 'wm_excluded_authors', array() );
		$is_excluded = in_array( $post->post_author, $excluded, true );
		if ( $is_excluded ) {
			return;
		}
		add_meta_box(
			'wm_wallet_address_meta_box',
			__( 'Web Monetization', 'web-monetization' ),
			array( $this, 'render_wallet_address_meta_box' ),
			$post_type,
			'side',
			'high'
		);
	}

	/**
	 * Render the wallet address meta box on the post edit screen.
	 *
	 * @param \WP_Post $post The post object.
	 */
	public function render_wallet_address_meta_box( $post ): void {
		$wallet_address = get_post_meta( $post->ID, 'wm_wallet_address', true );
		$isConnected    = get_post_meta( $post->ID, 'wm_wallet_address_connected', true ) === '1';
		$wm_disabled    = get_post_meta( $post->ID, 'wm_disabled', true );

		wp_nonce_field( 'save_post', 'wm_wallet_address_post_nonce' );

		echo '<p>';
		echo '<label for="wm_wallet_address">' . esc_html__( 'Wallet Address:', 'web-monetization' ) . '</label>';
		echo '<input type="text" id="wm_wallet_address" name="wm_wallet_address" value="' . esc_attr( $wallet_address ) . '" class="widefat" '. ($isConnected ? ' readonly' : '').' />';
		printf(
			'<input type="hidden" id="wm_wallet_address_connected"  name="wm_wallet_address_connected" value="%1$s">',
			esc_attr( $isConnected ? '1' : '0' )
		);
		echo '</p>';
		
		echo '<p>';
		echo '<label for="wm_disabled">';
		echo '<input type="checkbox" id="wm_disabled" name="wm_disabled" value="1" ' . checked( '1' === $wm_disabled, true, false ) . ' />';
		echo ' ' . esc_html__( 'Disable Web Monetization for this post ', 'web-monetization' );

		echo '</label>';
		echo '</p>';
	}

	/**
	 * Get inline SVG content.
	 *
	 * @param string $path The path to the SVG file.
	 * @return string The SVG content or an empty string if the file does not exist.
	 */
	private function get_inline_svg( string $path ): string {
		if ( ! file_exists( $path ) || ! is_file( $path ) ) {
			return '';
		}
		$contents = file( $path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES );
		return is_array( $contents ) ? implode( "\n", $contents ) : '';
	}


	/**
	 * Inline SVG logo for the admin menu icon.
	 * This function replaces the default menu icon with a custom SVG logo.
	 *
	 * @return void
	 */
	public function inline_logo_menu_icon(): void {
		if ( 'toplevel_page_web-monetization' !== get_current_screen()->base ) {
			echo '<style>#adminmenu li a.toplevel_page_web-monetization-settings .wp-menu-image:before { display: none; }</style>';
		}
		echo '<script>
			document.addEventListener("DOMContentLoaded", function() {
				const img = document.querySelector("#adminmenu li a.toplevel_page_web-monetization-settings .wp-menu-image");
				if (img) {
					img.innerHTML = `<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 37 25" width="20" height="20" fill="currentColor">
						<path d="M28.49 20.49l-.01 2.15H2.42l.03-14.54h3.45V5.7H.1v1.51l-.04 16.76h.01v.14l28.42.05h2.35v-4.5H28.49Z"/>
						<path d="M8.95 22.61H2.42v2.37h6.53V22.61Z"/>
						<path d="M2.42 24.97l.01-6.53H.06l-.01 6.53H2.42Z"/>
						<path d="M2.43 18.43l.01-6.53H.07l-.01 6.53H2.43Z"/>
						<path d="M15.45 22.61H8.92v2.37h6.53V22.61Z"/>
						<path d="M21.99 22.62h-6.53v2.37h6.53v-2.37Z"/>
						<path d="M28.52 22.64h-6.53v2.37h6.53v-2.37Z"/>
						<path d="M30.84 25.01l.01-5.62h-2.37l-.01 5.62h2.37Z"/>
						<path d="M5.32 19.9h30.77l.04-19.25H5.36L5.32 19.9Z"/>
						<path d="M10.54 11.77h.2c.77.01 1.39-.61 1.39-1.38v-.2c.01-.77-.61-1.39-1.39-1.39h-.2a1.38 1.38 0 0 0-1.39 1.38v.2c0 .76.61 1.38 1.39 1.39Z"/>
						<path d="M20.73 15.15a4.79 4.79 0 1 0 0-9.58 4.79 4.79 0 0 0 0 9.58Z"/>
						<path d="M30.72 11.8h.2c.77.01 1.39-.61 1.39-1.38v-.2a1.39 1.39 0 0 0-1.39-1.39h-.2a1.38 1.38 0 0 0-1.39 1.38v.2c0 .76.62 1.38 1.39 1.39Z"/>
					</svg>`;
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
			WEB_MONETIZATION_PLUGIN_VERSION
		);

		$allowed_hooks = array(
			'toplevel_page_web-monetization-settings',
			'post.php',    // Editing a post
			'post-new.php' // Creating a new post
		);

		if ( ! in_array( $hook, $allowed_hooks, true ) ) {
			return;
		}

		wp_enqueue_script(
			'wm-admin-script',
			plugin_dir_url( dirname( __DIR__, 1 ) ) . 'build/admin.js',
			array(),
			WEB_MONETIZATION_PLUGIN_VERSION,
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
		wp_localize_script(
			'wm-admin-script',
			'walletConnectData',
			array(
				'ajaxUrl' => admin_url( 'admin-ajax.php' ),
				'nonce'   => wp_create_nonce( 'wallet_connect_nonce' ),
			),
			'after'
		);
		wp_enqueue_script( 'wm-admin-script' );

		wp_enqueue_script(
			'wm-widget',
			plugin_dir_url( dirname( __DIR__, 1 ) ) . 'build/widget.js',
			array( 'wp-components', 'wp-element', 'wp-i18n' ),
			WEB_MONETIZATION_PLUGIN_VERSION,
			true
		);

		wp_enqueue_style(
			'wm-widget-style',
			plugin_dir_url( dirname( __DIR__, 1 ) ) . 'build/widget.css',
			array(),
			WEB_MONETIZATION_PLUGIN_VERSION
		);
	}
}
