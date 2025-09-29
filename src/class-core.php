<?php
/**
 * Web Monetization Plugin
 *
 * @package WebMonetization
 */

namespace WebMonetization;

use WebMonetization\Admin\Admin;
use WebMonetization\Frontend;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
/**
 * Core class for the Web Monetization plugin.
 *
 * This class handles the initialization of the plugin, including loading text domains,
 * registering hooks for admin and frontend functionality
 */
class Core {

	/**
	 * The single instance of the class.
	 *
	 * @var Core
	 */
	public static function init() {

		add_action(
			'init',
			function () {
				register_post_meta(
					'',
					'wm_wallet_address',
					array(
						'show_in_rest'  => true,
						'single'        => true,
						'type'          => 'string',
						'auth_callback' => function () {
							return current_user_can( 'edit_posts' );
						},
					)
				);
			}
		);

		// Load text domain for translations.
		load_plugin_textdomain( 'web-monetization', false, dirname( __DIR__, 1 ) . '/languages' );
		// Initialize admin or public functionality based on context.
		if ( is_admin() ) {
			( new Admin() )->register_hooks();
		}
		( new Frontend\Frontend() )->register_hooks();
		// Register post meta for wallet address.
		add_post_type_support( 'post', 'custom-fields' );

		// Register other block builders.
	}

	/**
	 * Activate the plugin.
	 *
	 * This method is called when the plugin is activated.
	 */
	public static function activate() {
		// Set default options.

		$default_wm_banner_config = array(
			'title'       => 'How to support?',
			'message'     => 'You can support this page and my work by a one time donation or proportional to the time you spend on this website through web monetization.',
			'bgColor'     => '#7f76b2',
			'textColor'   => '#ffffff',
			'position'    => 'bottom',
			'animation'   => true,
			'borderStyle' => 'rounded',
			'font'        => 'Arial',
			'fontSize'    => 17,
		);
		if ( is_multisite() ) {
			$sites = get_sites();
			foreach ( $sites as $site ) {
				switch_to_blog( $site->blog_id );

				add_option( 'wm_enabled', 1 );
				add_option( 'wm_wallet_address', '' );
				add_option( 'wm_enable_authors', 0 );
				add_option( 'wm_multi_wallets_option', 'one' ); // Default to 'one' wallet per post.
				add_option( 'wm_post_type_settings', array() ); // Default to no specific post type settings.
				add_option( 'wm_banner_enabled', 1 ); // Default to enabled banner.
				add_option( 'wm_excluded_authors', array() ); // Default to no excluded authors.
				add_option( 'wm_banner_config', $default_wm_banner_config ); // Default banner configuration.
				add_option( 'wm_banner_published', $default_wm_banner_config ); // Default to no published banner.
				add_option( 'wm_enable_country_wallets', 0 ); // Default to disabled country-specific wallets.
				add_option( 'wm_wallet_address_overrides', array() ); // Default to no wallet address overrides.

				restore_current_blog();
			}
		} else {
			// Single site.
			add_option( 'wm_enabled', 1 );
			add_option( 'wm_wallet_address', '' );
			add_option( 'wm_enable_authors', 0 );
			add_option( 'wm_multi_wallets_option', 'one' ); // Default to 'one' wallet per post.
			add_option( 'wm_post_type_settings', array() ); // Default to no specific post type settings.
			add_option( 'wm_banner_enabled', 1 ); // Default to enabled banner.
			add_option( 'wm_excluded_authors', array() ); // Default to no excluded.
			add_option( 'wm_banner_config', $default_wm_banner_config ); // Default banner configuration.
			add_option( 'wm_banner_published', $default_wm_banner_config ); // Default to no published banner.
			add_option( 'wm_enable_country_wallets', 0 ); // Default to disabled country-specific wallets.
			add_option( 'wm_wallet_address_overrides', array() ); // Default to no wallet address overrides.
		}
	}
	/**
	 * Deactivate the plugin.
	 *
	 * This method is called when the plugin is deactivated.
	 */
	public static function deactivate() {
		// No specific actions needed on deactivation.
	}
}
