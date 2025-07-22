<?php
namespace WebMonetization\Admin;

class UserMeta {

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

		add_filter( 'user_row_actions', [ self::class, 'add_user_row_action' ], 10, 2 );
		add_action( 'admin_init', [ self::class, 'handle_toggle_exclude_action' ] );

		add_action( 'restrict_manage_users', [ self::class, 'add_excluded_users_filter' ] );
		add_filter( 'pre_get_users', [ self::class, 'filter_excluded_users_query' ] );


	}

	public static function add_excluded_users_filter( $which ) {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		$selected = $_GET['wm_excluded_filter'] ?? '';

		?>
		<select name="wm_excluded_filter">
			<option value=""><?php esc_html_e( 'All Users', 'web-monetization' ); ?></option>
			<option value="excluded" <?php selected( $selected, 'excluded' ); ?>><?php esc_html_e( 'Excluded Users Only', 'web-monetization' ); ?></option>
			<option value="included" <?php selected( $selected, 'included' ); ?>><?php esc_html_e( 'Non-Excluded Users Only', 'web-monetization' ); ?></option>
		</select>
		<?php
	}

	public static function filter_excluded_users_query( $query ) {
		if ( ! is_admin() || ! isset( $_GET['wm_excluded_filter'] ) || ! current_user_can( 'manage_options' ) ) {
			return $query;
		}

		$filter = $_GET['wm_excluded_filter'];
		$excluded = get_option( 'wm_excluded_authors', [] );

		if ( $filter === 'excluded' ) {
			if ( ! empty( $excluded ) ) {
				$query->set( 'include', $excluded );
			} else {
				// No excluded users, show none
				$query->set( 'include', [0] );
			}
		} elseif ( $filter === 'included' ) {
			if ( ! empty( $excluded ) ) {
				$query->set( 'exclude', $excluded );
			}
		}

		return $query;
	}

	function is_author_monetization_enabled_for_user( int $user_id ): bool {
		if ( ! get_option( 'wm_enable_authors' ) ) {
			return false;
		}

		$excluded = get_option( 'wm_excluded_authors', array() );

		return ! in_array( $user_id, $excluded, true );
	}


	/**
	 * Add Enable/Disable Web Monetization links to user row actions.
	 */
	public static function add_user_row_action( $actions, $user ): array {
		if ( ! current_user_can( 'edit_user', $user->ID ) || ! get_option( 'wm_enable_authors' ) ) {
			return $actions;
		}
		// Exclude administrators
		if ( in_array( 'administrator', (array) $user->roles, true ) ) {
			return $actions;
		}


		$excluded = get_option( 'wm_excluded_authors', [] );
		$is_excluded = in_array( $user->ID, $excluded, true );

		$action_nonce = wp_create_nonce( 'wm_toggle_exclude_' . $user->ID );

		$url = add_query_arg( [
			'wm_toggle_exclude' => $user->ID,
			'_wpnonce'          => $action_nonce,
		], admin_url( 'users.php' ) );

		$label = $is_excluded ? __( 'Enable Web Monetization', 'web-monetization' ) : __( 'Disable Web Monetization', 'web-monetization' );

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

		$user_id = absint( $_GET['wm_toggle_exclude'] );

		if ( ! wp_verify_nonce( $_GET['_wpnonce'], 'wm_toggle_exclude_' . $user_id ) ) {
			wp_die( __( 'Invalid nonce.', 'web-monetization' ) );
		}

		$excluded = get_option( 'wm_excluded_authors', [] );

		if ( in_array( $user_id, $excluded, true ) ) {
			// Enable WM: remove from excluded
			$excluded = array_diff( $excluded, [ $user_id ] );
		} else {
			// Disable WM: add to excluded
			$excluded[] = $user_id;
		}

		update_option( 'wm_excluded_authors', array_values( $excluded ) );

		wp_redirect( remove_query_arg( [ 'wm_toggle_exclude', '_wpnonce' ], wp_get_referer() ) );
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
		<h2><?php esc_html_e( 'Web Monetization Settings', 'web-monetization' ); ?></h2>
		<table class="form-table" role="presentation">
			<tr>
				<th scope="row"><?php esc_html_e( 'Exclude from Author Monetization', 'web-monetization' ); ?></th>
				<td>
					<label for="wm_exclude_author">
						<input type="checkbox" name="wm_exclude_author" id="wm_exclude_author" value="1" <?php checked( $is_excluded ); ?> />
						<?php esc_html_e( 'Prevent this author from using their own wallet address (site fallback will be used).', 'web-monetization' ); ?>
					</label>
				</td>
			</tr>
		</table>
		<?php
	}

	public static function save_exclude_checkbox( int $user_id ): void {
		if ( ! current_user_can( 'edit_user', $user_id ) ) {
			return;
		}

		$excluded = get_option( 'wm_excluded_authors', array() );

		if ( isset( $_POST['wm_exclude_author'] ) ) {
			if ( ! in_array( $user_id, $excluded, true ) ) {
				$excluded[] = $user_id;
			}
		} else {
			$excluded = array_diff( $excluded, array( $user_id ) );
		}

		update_option( 'wm_excluded_authors', array_values( $excluded ) );
	}

	public static function render_wallet_address_field( $user ): void {
		if ( ! current_user_can( 'edit_user', $user->ID ) ) {
			return;
		}

		if ( ! get_option( 'wm_enable_authors' ) ) {
			return;
		}

		$excluded = get_option( 'wm_excluded_authors', [] );
		$is_excluded = in_array( $user->ID, $excluded, true );
		if ( $is_excluded ) {
			return;
		}

		$wallet = get_user_meta( $user->ID, 'wm_wallet_address', true );
		?>
		<h2><?php esc_html_e( 'Web Monetization', 'web-monetization' ); ?></h2>
		<table class="form-table" role="presentation">
			<tr>
				<th><label for="wm_wallet_address"><?php esc_html_e( 'Wallet Address', 'web-monetization' ); ?></label></th>
				<td>
					<input type="text" name="wm_wallet_address" id="wm_wallet_address"
						value="<?php echo esc_attr( $wallet ); ?>"
						class="regular-text" placeholder="eg: https://wallet.example.com/author" />
					<p class="description"><?php esc_html_e( 'Enter your wallet address to enable web monetization.', 'web-monetization' ); ?></p>
				</td>
			</tr>
		</table>
		<?php
	}

	public static function save_wallet_address( $user_id ): void {
		if ( ! current_user_can( 'edit_user', $user_id ) ) {
			return;
		}

		if ( isset( $_POST['wm_wallet_address'] ) ) {
			update_user_meta(
				$user_id,
				'wm_wallet_address',
				sanitize_text_field( wp_unslash( $_POST['wm_wallet_address'] ) )
			);
		}
	}
}
