<?php
/**
 * Plugin Name: Interledger Web Monetization Integration
 * Description: Implements the open Web Monetization standard in WordPress. Developed and maintained by the Interledger Foundation.
 * Version:     1.0.2
 * Author:      Interledger Foundation
 * Author URI:  https://interledger.org
 * License: Apache-2.0
 * License URI: http://www.apache.org/licenses/LICENSE-2.0.txt
 * Text Domain: interledger-web-monetization-integration
 * Domain Path: /languages
 *
 * @package     Interledger\WebMonetization
 */

defined( 'ABSPATH' ) || exit;

if ( file_exists( __DIR__ . '/vendor/autoload.php' ) ) {
	require __DIR__ . '/vendor/autoload.php';
}

require_once __DIR__ . '/src/frontend/class-frontend.php';
require_once __DIR__ . '/src/admin/rendering/class-fieldrenderer.php';
require_once __DIR__ . '/src/admin/settings/class-settingspage.php';
require_once __DIR__ . '/src/admin/settings/tabs/class-widgetsettingstab.php';
require_once __DIR__ . '/src/admin/settings/tabs/class-generaltab.php';
require_once __DIR__ . '/src/admin/settings/tabs/class-abouttab.php';
require_once __DIR__ . '/src/admin/class-usermeta.php';
require_once __DIR__ . '/src/admin/class-admin.php';
require_once __DIR__ . '/src/class-core.php';

define( 'INTLWEMO_PLUGIN_VERSION', '1.0.0' );
define( 'INTLWEMO_PLUGIN_DIR', plugin_dir_url( __FILE__ ) );
define( 'INTLWEMO_PLUGIN_PATH', plugin_dir_path( __FILE__ ) );

add_action(
	'plugins_loaded',
	function () {
		Interledger\WebMonetization\Core::init();
	}
);
register_activation_hook(
	__FILE__,
	function () {
		if ( class_exists( 'Interledger\WebMonetization\Core' ) ) {
			Interledger\WebMonetization\Core::activate();
		}
	}
);
register_deactivation_hook(
	__FILE__,
	function () {
		if ( class_exists( 'Interledger\WebMonetization\Core' ) ) {
			Interledger\WebMonetization\Core::deactivate();
		}
	}
);
