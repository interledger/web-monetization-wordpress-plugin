<?php
/**
 * User Meta Class for Web Monetization
 *
 * @package WebMonetization
 */

namespace WebMonetization\Admin;

/**
 * Class UserMeta
 *
 * Handles user meta functionality for Web Monetization.
 * This includes rendering wallet address fields, saving user settings,
 * and managing author monetization exclusions.
 */
class UserMeta {

	/**
	 * Register hooks for user meta functionality.
	 */
	public static function register_hooks(): void {
		if ( ! get_option( 'wm_enable_authors' ) ) {
			return;
		}
		add_action( 'show_user_profile', array( self::class, 'render_wallet_address_field' ) );
		add_action( 'edit_user_profile', array( self::class, 'render_exclude_checkbox' ) );
		add_action( 'edit_user_profile', array( self::class, 'render_wallet_address_field' ) );
		add_action( 'edit_user_profile_update', array( self::class, 'save_exclude_checkbox' ) );

		add_action( 'personal_options_update', array( self::class, 'save_wallet_address' ) );
		add_action( 'edit_user_profile_update', array( self::class, 'save_wallet_address' ) );

		add_filter( 'user_row_actions', array( self::class, 'add_user_row_action' ), 10, 2 );
		add_action( 'admin_init', array( self::class, 'handle_toggle_exclude_action' ) );

		add_action( 'restrict_manage_users', array( self::class, 'add_excluded_users_filter' ) );
		add_filter( 'pre_get_users', array( self::class, 'filter_excluded_users_query' ) );
	}

	/**
	 * Add Excluded Users filter to user list.
	 *
	 * @param string $which The location of the filter (top or bottom).
	 */
	public static function add_excluded_users_filter( $which ) {
		if ( ! current_user_can( 'manage_options' ) || 'top' !== $which ) {
			return;
		}
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$selected = isset( $_GET['wm_excluded_filter'] )
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended
			? sanitize_text_field( wp_unslash( $_GET['wm_excluded_filter'] ) )
			: '';
		?>
		<select name="wm_excluded_filter"  onchange="this.form.submit()">
			<option value=""><?php esc_html_e( 'All Users', 'web-monetization-by-interledger' ); ?></option>
			<option value="excluded" <?php selected( $selected, 'excluded' ); ?>><?php esc_html_e( 'Excluded Users Only', 'web-monetization-by-interledger' ); ?></option>
			<option value="included" <?php selected( $selected, 'included' ); ?>><?php esc_html_e( 'Non-Excluded Users Only', 'web-monetization-by-interledger' ); ?></option>
		</select>
		<?php
	}

	/**
	 * Filter the user query based on excluded users.
	 *
	 * @param WP_User_Query $query The user query.
	 * @return WP_User_Query The modified user query.
	 */
	public static function filter_excluded_users_query( $query ) {
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended
		if ( ! is_admin() || ! isset( $_GET['wm_excluded_filter'] ) || ! current_user_can( 'manage_options' ) ) {
			return $query;
		}
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$filter   = sanitize_text_field( wp_unslash( $_GET['wm_excluded_filter'] ) );
		$excluded = get_option( 'wm_excluded_authors', array() );

		if ( 'excluded' === $filter ) {
			if ( ! empty( $excluded ) ) {
				$query->set( 'include', $excluded );
			} else {
				// No excluded users, show none.
				$query->set( 'include', array( 0 ) );
			}
		} elseif ( 'included' === $filter ) {
			if ( ! empty( $excluded ) ) {
				$query->set( 'exclude', $excluded );
			}
		}

		return $query;
	}

	/**
	 * Add a row action to toggle author exclusion from monetization.
	 *
	 * @param array   $actions The existing row actions.
	 * @param WP_User $user The user object.
	 * @return array The modified row actions.
	 * This method adds a custom action link to the user list for toggling
	 * the exclusion of a user from Web Monetization.
	 * It checks if the current user can edit the user and if author monetization is enabled
	 * This allows administrators to easily manage which authors are allowed to use their own wallet addresses for Web Monetization.
	 */
	public static function add_user_row_action( $actions, $user ): array {

		if ( ! current_user_can( 'edit_user', $user->ID ) || ! get_option( 'wm_enable_authors' ) ) {
			return $actions;
		}

		$excluded    = get_option( 'wm_excluded_authors', array() );
		$is_excluded = in_array( $user->ID, $excluded, true );

		$action_nonce = wp_create_nonce( 'wm_toggle_exclude_' . $user->ID );

		$url = add_query_arg(
			array(
				'wm_toggle_exclude' => $user->ID,
				'_wpnonce'          => $action_nonce,
			),
			admin_url( 'users.php' )
		);

		$label = $is_excluded ? esc_html__( 'Enable Web Monetization', 'web-monetization-by-interledger' ) : esc_html__( 'Disable Web Monetization', 'web-monetization-by-interledger' );

		$actions['wm_toggle_exclude'] = sprintf(
			'<a href="%s">%s</a>',
			esc_url( $url ),
			esc_html( $label )
		);

		return $actions;
	}


	/**
	 * Handle Enable/Disable WM toggle action from user list.
	 */
	public static function handle_toggle_exclude_action(): void {
		if (
			! isset( $_GET['wm_toggle_exclude'], $_GET['_wpnonce'] )
			|| ! current_user_can( 'edit_users' )
		) {
			return;
		}

		$user_id = absint( wp_unslash( $_GET['wm_toggle_exclude'] ) );

		if ( ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_GET['_wpnonce'] ) ), 'wm_toggle_exclude_' . $user_id ) ) {
			wp_die( esc_html__( 'Invalid nonce.', 'web-monetization-by-interledger' ) );
		}

		$excluded = get_option( 'wm_excluded_authors', array() );

		if ( in_array( $user_id, $excluded, true ) ) {
			// Enable WM: remove from excluded.
			$excluded = array_diff( $excluded, array( $user_id ) );
		} else {
			// Disable WM: add to excluded.
			$excluded[] = $user_id;
		}

		update_option( 'wm_excluded_authors', array_values( $excluded ) );

		wp_safe_redirect( remove_query_arg( array( 'wm_toggle_exclude', '_wpnonce' ), wp_get_referer() ) );
		exit;
	}

	/**
	 * Render the checkbox to exclude an author from monetization.
	 *
	 * @param WP_User $user The user object.
	 */
	public static function render_exclude_checkbox( $user ): void {
		if ( ! current_user_can( 'edit_user', $user->ID ) ) {
			return;
		}

		$excluded    = get_option( 'wm_excluded_authors', array() );
		$is_excluded = in_array( $user->ID, $excluded, true );

		?>
		<h2><?php esc_html_e( 'Web Monetization Settings', 'web-monetization-by-interledger' ); ?></h2>
		<table class="form-table" role="presentation">
			<tr>
				<th scope="row"><?php esc_html_e( 'Exclude from Author Monetization', 'web-monetization-by-interledger' ); ?></th>
				<td>
					<label for="wm_exclude_author">
						<input type="checkbox" name="wm_exclude_author" id="wm_exclude_author" value="1" <?php checked( $is_excluded ); ?> />
						<?php esc_html_e( 'Prevent this author from using their own wallet address (site fallback will be used).', 'web-monetization-by-interledger' ); ?>
						<?php wp_nonce_field( 'wm_toggle_exclude_' . $user->ID, 'wm_toggle_exclude_nonce' ); ?>
					</label>
				</td>
			</tr>
		</table>
		<?php
	}

	/**
	 * Save the exclude checkbox state for a user.
	 *
	 * @param int $user_id The user ID.
	 * Saves the state of the exclude checkbox for the user.
	 * If the user cannot edit the profile, the function returns early.
	 * If the checkbox is checked, the user is added to the excluded authors list.
	 * If the checkbox is unchecked, the user is removed from the excluded authors list.
	 * The excluded authors list is stored in the 'wm_excluded_authors' option.
	 * The function updates the option with the new list of excluded authors.
	 */
	public static function save_exclude_checkbox( int $user_id ): void {
		if ( ! current_user_can( 'edit_user', $user_id ) ) {
			return;
		}
		if ( ! isset( $_POST['wm_toggle_exclude_nonce'] ) ||
			! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['wm_toggle_exclude_nonce'] ) ), 'wm_toggle_exclude_' . $user_id ) ) {
			return;
		}
		$excluded = get_option( 'wm_excluded_authors', array() );
		if ( ! is_array( $excluded ) ) {
			$excluded = array();
		}
		if ( isset( $_POST['wm_exclude_author'] ) ) {
			if ( ! in_array( $user_id, $excluded, true ) ) {
				$excluded[] = $user_id;
			}
		} else {
			$excluded = array_diff( $excluded, array( $user_id ) );
		}

		update_option( 'wm_excluded_authors', array_values( $excluded ) );
	}

	/**
	 * Render the wallet address field for a user.
	 *
	 * @param WP_User $user The user object.
	 * Renders the wallet address input field for the user profile.
	 * This allows authors to set their own wallet address for Web Monetization.
	 * If the user cannot edit the profile or if author monetization is disabled,
	 * the function returns early without rendering anything.
	 */
	public static function render_wallet_address_field( $user ): void {
		if ( ! current_user_can( 'edit_user', $user->ID ) ) {
			return;
		}
		if ( in_array( 'administrator', (array) $user->roles, true ) ) {
			return;
		}
		if ( ! get_option( 'wm_enable_authors' ) ) {
			return;
		}

		$excluded    = get_option( 'wm_excluded_authors', array() );
		$is_excluded = in_array( $user->ID, $excluded, true );
		if ( $is_excluded ) {
			return;
		}

		$wallet       = get_user_meta( $user->ID, 'wm_wallet_address', true );
		$is_connected = get_user_meta( $user->ID, 'wm_wallet_address_connected', true ) === '1';
		?>
		<h2><?php esc_html_e( 'Web Monetization', 'web-monetization-by-interledger' ); ?></h2>
		<table class="form-table" role="presentation">
			<tr>
				<th><label for="wm_wallet_address"><?php esc_html_e( 'Wallet Address', 'web-monetization-by-interledger' ); ?></label></th>
				<td>
					<input type="text" name="wm_wallet_address" id="wm_wallet_address"
						value="<?php echo esc_attr( $wallet ); ?>"
						class="regular-text" placeholder="eg: https://walletprovider.com/MyWallet" <?php echo $is_connected ? 'readonly' : ''; ?> />
					<?php
					printf(
						'<input type="hidden" id="wm_wallet_address_connected" name="wm_wallet_address_connected" value="%1$s">',
						esc_attr( $is_connected ? '1' : '0' )
					);
					?>
					<?php wp_nonce_field( 'wm_save_wallet_address', 'wm_wallet_address_nonce' ); ?>
					<p class="description"><?php esc_html_e( 'Enter your wallet address to enable Web Monetization.', 'web-monetization-by-interledger' ); ?></p>
				</td>
			</tr>
		</table>
		<?php
	}

	/**
	 * Save the wallet address for a user.
	 *
	 * @param int $user_id The user ID.
	 * Saves the wallet address entered by the user in their profile.
	 * If the user cannot edit the profile, the function returns early.
	 */
	public static function save_wallet_address( $user_id ): void {
		if ( ! current_user_can( 'edit_user', $user_id ) ) {
			return;
		}
		if ( ! isset( $_POST['wm_wallet_address_nonce'] ) ||
			! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['wm_wallet_address_nonce'] ) ), 'wm_save_wallet_address' ) ) {
			return;
		}

		if ( isset( $_POST['wm_wallet_address'] ) ) {
			update_user_meta(
				$user_id,
				'wm_wallet_address',
				sanitize_text_field( wp_unslash( $_POST['wm_wallet_address'] ) )
			);
		}
		if ( isset( $_POST['wm_wallet_address_connected'] ) ) {
			update_user_meta(
				$user_id,
				'wm_wallet_address_connected',
				sanitize_text_field( wp_unslash( $_POST['wm_wallet_address_connected'] ) )
			);
		}
	}
}
