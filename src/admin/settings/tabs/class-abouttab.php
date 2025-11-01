<?php
/**
 * Interledger Web Monetization Admin About Tab Class
 *
 * @package Interledger\WebMonetization
 */

namespace Interledger\WebMonetization\Admin\Settings\Tabs;

/**
 * Class AboutTab
 *
 * @package Interledger\WebMonetization\Admin\Settings\Tabs
 */
class AboutTab {

	/**
	 * Register the settings for the About tab.
	 */
	public static function register(): void {
	}

	/**
	 * Render the settings form.
	 */
	public static function render(): void {
		?>
		<div class="wrap wm-about-tab">
			<h2><?php esc_html_e( 'About Web Monetization', 'interledger-web-monetization-integration' ); ?></h2>

			<p><?php esc_html_e( 'Web Monetization introduces a new way for content owners and publishers to earn while allowing visitors to engage on their own terms. By enabling streaming micropayments, Web Monetization complements ads, subscriptions, and memberships, giving publishers more revenue options and visitors more ways to access and support content.', 'interledger-web-monetization-integration' ); ?></p>

			<p><?php esc_html_e( 'The plugin lets you add a Web Monetization-compatible wallet address to monetize your entire site, individual posts, and pages. If you have multiple authors, you can also allow them to add their own wallet addresses to monetize their content.', 'interledger-web-monetization-integration' ); ?></p>

			<hr />

			<h3><?php esc_html_e( 'Resources', 'interledger-web-monetization-integration' ); ?></h3>

			<ul>
				<li>
					
					<?php esc_html_e( 'Learn More at WebMonetization.org', 'interledger-web-monetization-integration' ); ?>
					( <a href="https://webmonetization.org/" target="_blank" rel="noopener noreferrer">https://webmonetization.org/</a> )
				</li>
				
			</ul>
			<ul>
				<li>
					
					<?php esc_html_e( 'Learn more about Web Monetization enabled wallets', 'interledger-web-monetization-integration' ); ?>
					( <a href="https://webmonetization.org/wallets/" target="_blank" rel="noopener noreferrer">
						https://webmonetization.org/wallets/
					</a> )
				</li>
				
			</ul>

			<h3><?php esc_html_e( 'About this plugin', 'interledger-web-monetization-integration' ); ?></h3>
			<p>
				<?php
				printf(
					/* translators: 1: Interledger Foundation link, 2: GitHub issues link */
					esc_html__( 'The %1$s created, maintains, and releases this Web Monetization plugin for WordPress. Contribute feature requests and bug reports on %2$s.', 'interledger-web-monetization-integration' ),
					'<a href="https://interledger.org/" target="_blank" rel="noopener noreferrer">Interledger Foundation</a>',
					'<a href="https://github.com/interledger/web-monetization-wordpress-plugin/issues/" target="_blank" rel="noopener noreferrer">GitHub</a>'
				);
				?>
			</p>

			<p>
				<?php
				esc_html_e(
					'The Interledger Foundation (ILF) is a mission-driven nonprofit ensuring that no one is left behind in the digital economy. We support inclusive innovation and financial infrastructure that connects people, communities, and entire economies globally.',
					'interledger-web-monetization-integration'
				);
				?>
			</p>

		</div>
		<?php
	}
}
