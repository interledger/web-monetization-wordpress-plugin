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

delete_option( 'intlwemo_enabled' );
delete_option( 'intlwemo_wallet_address' );
delete_option( 'intlwemo_wallet_address_connected' );
delete_option( 'intlwemo_enable_authors' );
delete_option( 'intlwemo_multi_wallets_option' );
delete_option( 'intlwemo_post_type_settings' );
delete_option( 'intlwemo_banner_enabled' );
delete_option( 'intlwemo_excluded_authors' );
delete_option( 'intlwemo_banner_config' );
delete_option( 'intlwemo_banner_published' );
delete_option( 'intlwemo_enable_country_wallets' );
delete_option( 'intlwemo_wallet_address_overrides' );
delete_option( 'intlwemo_wallet_address_connected_list' );

delete_metadata( 'user', 0, 'intlwemo_wallet_address', '', true );
delete_metadata( 'post', 0, 'intlwemo_disabled', '', true );
delete_metadata( 'post', 0, 'intlwemo_wallet_address', '', true );
