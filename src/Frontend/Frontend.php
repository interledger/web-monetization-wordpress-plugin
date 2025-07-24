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
	public function register_hooks(): void {
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_wm_banner_script' ) );
		add_action( 'wp_head', array( $this, 'render_web_monetization_link' ) );

		// Feeds
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
			$link_tag = $this->generate_monetization_link_for_post(  get_the_ID() );
			if ( $link_tag ) {
				echo $link_tag;
			}
		} else {
			$wallet = $this->get_wallet_for_front_page();
			if ( $wallet ) {
				echo "<link rel=\"monetization\" href=\"{$wallet}\" />\n";
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
			echo $link_tag;
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
			echo $link_tag;
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
			echo '	<link rel="monetization" href="' . $url . '" />' . PHP_EOL;
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
			echo '	<atom:link rel="monetization" href="' . $url . '" />' . PHP_EOL;
		}
	}

	/**
	 * Generate monetization link for a post.
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

		if ( $mode === 'all' ) {
			foreach ( $wallets['list'] as $source => $wallet ) {
				$url     = esc_url( $this->clean_wallet_address( $wallet ), 'https' );
				$output .= "<{$element_type} rel=\"monetization\" href=\"{$url}\" data-wm-source=\"{$source}\" />\n";
			}
		} else {
			foreach ( array( 'article', 'author', 'post_type', 'site' ) as $key ) {
				if ( isset( $wallets['list'][ $key ] ) ) {
					$url     = esc_url( $this->clean_wallet_address( $wallets['list'][ $key ] ), 'https' );
					$output .= "<{$element_type} rel=\"monetization\" href=\"{$url}\" data-wm-source=\"{$key}\" />\n";
					break;
				}
			}
		}
		return $output;
	}

	/**
	 * Get wallets for a post with logic.
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
			if ( in_array( $post->post_author, $excluded ) ) {
				$author_disabled = 1;
			}
		}

		if ( ! $author_disabled ) {
			// Post-specific wallet
			$post_wallet = get_post_meta( $post->ID, 'wm_wallet_address', true );
			if ( $post_wallet && ! $disabled ) {
				$list['article'] = $post_wallet;
			}

			// Author wallet
			if ( get_option( 'wm_enable_authors', false ) ) {
				$author_wallet = get_user_meta( $post->post_author, 'wm_wallet_address', true );
				if ( $author_wallet ) {
					$list['author'] = $author_wallet;
				}
			}
		}

		// Post type wallet
		$post_type_wallets = get_option( 'wm_post_type_settings', array() );
		$config            = $post_type_wallets[ $post->post_type ] ?? null;
		if ( $config && ! empty( $config['enabled'] ) && ! empty( $config['wallet'] ) ) {
			$list['post_type'] = $config['wallet'];
		}

		// Site-wide wallet
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
	 */
	private function clean_wallet_address( string $wallet ): string {
		$wallet = trim( str_replace( 'http://', 'https://', $wallet ) );
		return $wallet[0] === '$' ? str_replace( '$', 'https://', $wallet ) : $wallet;
	}

	/**
	 * Check if monetization is globally enabled.
	 */
	private function is_enabled(): bool {
		return (bool) get_option( 'wm_enabled', 0 );
	}

	/**
	 * Enqueue frontend banner script.
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
