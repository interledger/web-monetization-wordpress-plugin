<?php
/**
 * Plugin Name: Web Monetization
 * Description: Web Monetization plugin for WordPress.
 * Version:     1.0.0
 * Author:      Interledger Foundation
 * Author URI:  https://interledger.org
 * License:     GPL-2.0-or-later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: web-monetization
 * Domain Path: /languages
 * Package:     WebMonetization
 */

defined( 'ABSPATH' ) || exit; // Prevent direct access

if ( file_exists( __DIR__ . '/vendor/autoload.php' ) ) {
	require __DIR__ . '/vendor/autoload.php';
}

define( 'WEB_MONETIZATION_PLUGIN_VERSION', '1.0.0' );
define( 'WEB_MONETIZATION_PLUGIN_DIR', plugin_dir_url( __FILE__ ) );
define( 'WEB_MONETIZATION_PLUGIN_PATH', plugin_dir_path( __FILE__ ) );

add_action(
	'plugins_loaded',
	function () {
		// Boot up the core plugin class
		WebMonetization\Core::init();
	}
);
register_activation_hook(
	__FILE__,
	function () {
		if ( class_exists( 'WebMonetization\Core' ) ) {
			WebMonetization\Core::activate();
		}
	}
);
register_deactivation_hook(
	__FILE__,
	function () {
		if ( class_exists( 'WebMonetization\Core' ) ) {
			WebMonetization\Core::deactivate();
		}
	}
);
