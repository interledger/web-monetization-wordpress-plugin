<?php

/**
 * WebMonetization Admin Class
 *
 * @package WebMonetization
 */
namespace WebMonetization\Admin\Settings\Tabs;

/**
 * Class AboutTab
 *
 * @package WebMonetization\Admin\Settings\Tabs
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
			<h2><?php esc_html_e( 'About Web Monetization', 'web-monetization' ); ?></h2>

			<p><?php esc_html_e( 'Web Monetization introduces a new way for content owners and publishers to earn while allowing visitors to engage on their own terms. By enabling streaming micropayments, Web Monetization complements ads, subscriptions, and memberships, giving publishers more revenue options and visitors more ways to access and support content.', 'web-monetization' ); ?></p>

			<p><?php esc_html_e( 'The plugin lets you add a Web Monetization-compatible wallet address to monetize your entire site, individual posts, and pages. If you have multiple authors, you can also allow them to add their own wallet addresses to monetize their content.', 'web-monetization' ); ?></p>

			<hr />

			<h3><?php esc_html_e( 'Resources', 'web-monetization' ); ?></h3>

			<ul>
				<li>
					
					<?php esc_html_e( 'Learn More at WebMonetization.org', 'web-monetization' ); ?>
					( <a href="https://webmonetization.org/" target="_blank" rel="noopener noreferrer">https://webmonetization.org/</a> )
				</li>
				
			</ul>
			<ul>
				<li>
					
					<?php esc_html_e( 'Learn more about Web Monetization enabled wallets', 'web-monetization' ); ?>
					( <a href="https://webmonetization.org/wallets/" target="_blank" rel="noopener noreferrer">
						https://webmonetization.org/wallets/
					</a> )
				</li>
				
			</ul>
		</div>
		<?php
	}
}
