<?php
/**
 * WebMonetization Frontend Class
 *
 * @package WebMonetization
 */

namespace WebMonetization\Frontend;

/**
 * Class Frontend
 *
 * Handles the frontend functionality of the Web Monetization plugin.
 */
class Frontend {

	/**
	 * Allowed HTML tags for monetization links.
	 *
	 * @var array
	 */
	private array $allowed_tags = array(
		'link' => array(
			'rel'            => true,
			'href'           => true,
			'type'           => true,
			'title'          => true,
			'media'          => true,
			'data-wm-source' => true,
		),
	);
	/**
	 * Register hooks for the frontend.
	 */
	public function register_hooks(): void {
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_wm_banner_script' ) );
		add_action( 'wp_head', array( $this, 'render_web_monetization_link' ) );

		// Feeds action hooks.
		add_action( 'rss2_head', array( $this, 'add_monetization_atom_link_to_feed_head' ) );
		add_action( 'rss2_item', array( $this, 'add_monetization_atom_link_to_feed_item' ) );
		add_action( 'atom_entry', array( $this, 'add_monetization_link_to_feed_item' ) );
		add_action( 'atom_head', array( $this, 'add_site_monetization_link_to_feed_head' ) );
	}

	/**
	 * Render monetization link in page head.
	 */
	public function render_web_monetization_link(): void {
		if ( ! $this->is_enabled() ) {
			return;
		}
		if ( is_singular() ) {
			// For singular pages, generate monetization link for the post.
			$link_tag = $this->generate_monetization_link_for_post( get_the_ID() );
			if ( $link_tag ) {
				echo wp_kses( $link_tag, $this->allowed_tags );
			}
		} else {
			$wallet = $this->get_wallet_for_front_page();
			if ( $wallet ) {
				echo '<link rel="monetization" href="' . esc_url( $wallet, 'https' ) . '" />' . PHP_EOL;
			}
		}
	}

	/**
	 * Add monetization link to each feed item.
	 */
	public function add_monetization_atom_link_to_feed_item(): void {
		global $post;
		if ( ! $post instanceof \WP_Post || ! $this->is_enabled() ) {
			return;
		}

		$link_tag = $this->generate_monetization_link_for_post( $post->ID, 'atom:link' );
		if ( $link_tag ) {
			echo wp_kses( $link_tag, $this->allowed_tags );
		}
	}

	/**
	 * Add monetization link to each feed item.
	 */
	public function add_monetization_link_to_feed_item(): void {
		global $post;
		if ( ! $post instanceof \WP_Post || ! $this->is_enabled() ) {
			return;
		}

		$link_tag = $this->generate_monetization_link_for_post( $post->ID, 'atom:link' );
		if ( $link_tag ) {
			echo wp_kses( $link_tag, $this->allowed_tags );
		}
	}

	/**
	 * Add site-wide monetization link to Atom feed head.
	 */
	public function add_site_monetization_link_to_feed_head(): void {
		if ( ! $this->is_enabled() ) {
			return;
		}

		$site_wallet = get_option( 'wm_wallet_address', '' );
		if ( $site_wallet ) {
			$url = esc_url( $this->clean_wallet_address( $site_wallet ), 'https' );
			echo '	<link rel="monetization" href="' . esc_url( $url, 'https' ) . '" />' . PHP_EOL;
		}
	}
	/**
	 * Add site-wide monetization link to Atom feed head.
	 */
	public function add_monetization_atom_link_to_feed_head(): void {
		if ( ! $this->is_enabled() ) {
			return;
		}

		$site_wallet = get_option( 'wm_wallet_address', '' );
		if ( $site_wallet ) {
			$url = esc_url( $this->clean_wallet_address( $site_wallet ), 'https' );
			echo '	<atom:link rel="monetization" href="' . esc_url( $url, 'https' ) . '" />' . PHP_EOL;
		}
	}

	/**
	 * Generate monetization link for a post.
	 *
	 * @param int    $post_id The post ID.
	 * @param string $element_type The type of element to generate (e.g., 'link', 'atom:link').
	 * @return string|null The monetization link or null if monetization is not enabled or no wallet is found.
	 */
	public function generate_monetization_link_for_post( $post_id, $element_type = 'link' ): ?string {

		if ( ! $this->is_enabled() ) {
			return null;
		}

		$post = get_post( $post_id );
		if ( ! $post ) {
			return null;
		}

		$wallets = $this->get_wallets_for_post( $post );
		if ( empty( $wallets['list'] ) ) {
			return null;
		}

		$output = '';
		$mode   = get_option( 'wm_multi_wallets_option', 'one' );

		if ( 'all' === $mode ) {
			foreach ( $wallets['list'] as $source => $wallet ) {
				$output .= $this->render_monetization_link( $wallet, $source );
			}
		} else {
			foreach ( array( 'article', 'author', 'post_type', 'site' ) as $key ) {
				if ( isset( $wallets['list'][ $key ] ) ) {
					if ( 'site' === $key && 0 < strpos( $wallets['list'][ $key ], ' ' ) ) {
						$wallets = explode( ' ', $wallets['list'][ $key ] );
						foreach ( $wallets as $wallet ) {
							$output .= $this->render_monetization_link( $wallet, $key, $element_type );
						}
					} else {
						$output .= $this->render_monetization_link( $wallets['list'][ $key ], $key, $element_type );
					}
					break;
				}
			}
		}
		return $output;
	}

	/**
	 * Render monetization link.
	 *
	 * @param string $wallet The wallet address.
	 * @param string $source The source of the wallet (e.g., 'article', 'author', 'post_type', 'site').
	 * @param string $element_type The type of element to generate (e.g., 'link', 'atom:link').
	 * @return string The rendered monetization link.
	 */
	private function render_monetization_link( string $wallet, string $source, string $element_type = 'link' ): string {
		$url = esc_url( $this->clean_wallet_address( $wallet ), 'https' );
		return "<{$element_type} rel=\"monetization\" href=\"{$url}\" data-wm-source=\"{$source}\" />" . PHP_EOL;
	}
	/**
	 * Get wallets for a post with logic.
	 *
	 * @param \WP_Post $post The post object.
	 * @return array An array containing the wallets and a disabled flag.
	 */
	private function get_wallets_for_post( $post ): array {
		$list     = array();
		$disabled = get_post_meta( $post->ID, 'wm_disabled', true ) === '1';

		if ( $disabled ) {
			return array(
				'list'     => array(),
				'disabled' => true,
			);
		}
		$author_disabled = 0;

		if ( get_option( 'wm_enable_authors', false ) ) {
			$excluded = get_option( 'wm_excluded_authors', array() );
			if ( in_array( $post->post_author, $excluded, true ) ) {
				$author_disabled = 1;
			}
		}

		if ( ! $author_disabled ) {
			// Post-specific wallet.
			$post_wallet = get_post_meta( $post->ID, 'wm_wallet_address', true );
			if ( $post_wallet && ! $disabled ) {
				$list['article'] = $post_wallet;
			}

			// Author wallet.
			if ( get_option( 'wm_enable_authors', false ) ) {
				$author_wallet = get_user_meta( $post->post_author, 'wm_wallet_address', true );
				if ( $author_wallet ) {
					$list['author'] = $author_wallet;
				}
			}
		}

		// Post type wallet.
		$post_type_wallets = get_option( 'wm_post_type_settings', array() );
		$config            = $post_type_wallets[ $post->post_type ] ?? null;
		if ( $config && ! empty( $config['enabled'] ) && ! empty( $config['wallet'] ) ) {
			$list['post_type'] = $config['wallet'];
		}

		// Site-wide wallet.
		$site_wallet = get_option( 'wm_wallet_address', '' );
		if ( $site_wallet ) {
			$list['site'] = $site_wallet;
		}

		return array(
			'list'     => $list,
			'disabled' => $disabled,
		);
	}

	/**
	 * Get wallet for front page.
	 *
	 * @return string|null
	 */
	private function get_wallet_for_front_page(): ?string {
		if ( ! $this->is_enabled() ) {
			return null;
		}

		$site_wallet = get_option( 'wm_wallet_address', '' );
		if ( $site_wallet ) {
			return esc_url( $this->clean_wallet_address( $site_wallet ), 'https' );
		}

		return null;
	}

	/**
	 * Clean wallet address.
	 *
	 * @param string $wallet The wallet address.
	 * @return string Cleaned wallet address.
	 */
	private function clean_wallet_address( string $wallet ): string {
		$wallet = trim( str_replace( 'http://', 'https://', $wallet ) );
		return '$' === $wallet[0] ? str_replace( '$', 'https://', $wallet ) : $wallet;
	}

	/**
	 * Check if monetization is globally enabled.
	 *
	 * @return bool True if enabled, false otherwise.
	 */
	private function is_enabled(): bool {
		return (bool) get_option( 'wm_enabled', 0 );
	}

	/**
	 * Enqueue frontend banner script.
	 *
	 * This method checks if the banner is enabled and if the current page is suitable for displaying the banner.
	 * It registers and enqueues the script that handles the banner functionality.
	 */
	public function enqueue_wm_banner_script(): void {
		if ( is_attachment() || ( ! is_singular() && ! is_front_page() && ! is_author() ) ) {
			return;
		}
		if ( ! get_option( 'wm_banner_enabled', 1 ) || ! $this->is_enabled() ) {
			return;
		}

		wp_register_script(
			'wm-banner-script',
			plugin_dir_url( dirname( __DIR__, 1 ) ) . 'build/frontend.js',
			array(),
			'1.0.0',
			true
		);

		wp_localize_script(
			'wm-banner-script',
			'wm',
			array(
				'wmBannerConfig' => get_option( 'wm_banner_published', array() ),
				'wmEnabled'      => get_option( 'wm_enabled', 0 ),
				'wmBuildUrl'     => plugin_dir_url( dirname( __DIR__, 1 ) ) . 'build/',
			)
		);

		wp_enqueue_script( 'wm-banner-script' );
	}
}
