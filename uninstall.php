<?php
/**
 * Uninstall the Web Monetization plugin.
 *
 * This file is called when the plugin is uninstalled.
 * It removes all options and metadata related to the plugin.
 *
 * @package WebMonetization
 */

if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

delete_option( 'wm_enabled' );
delete_option( 'wm_wallet_address' );
delete_option( 'wm_wallet_address_connected' );
delete_option( 'wm_enable_authors' );
delete_option( 'wm_multi_wallets_option' );
delete_option( 'wm_post_type_settings' );
delete_option( 'wm_banner_enabled' );
delete_option( 'wm_excluded_authors' );
delete_option( 'wm_banner_config' );
delete_option( 'wm_banner_published' );
delete_option( 'wm_enable_country_wallets' );
delete_option( 'wm_wallet_address_overrides' );

delete_metadata( 'user', 0, 'wm_wallet_address', '', true );
delete_metadata( 'post', 0, 'wm_disabled', '', true );
delete_metadata( 'post', 0, 'wm_wallet_address', '', true );
