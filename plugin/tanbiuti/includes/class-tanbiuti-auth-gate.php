<?php
/**
 * Gates the front-end "Minha Conta" area behind a login — single-tier access: any logged-in
 * user who can `read` sees every section, there is no per-client data partitioning.
 *
 * @package Tanbiuti
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Tanbiuti_Auth_Gate.
 */
class Tanbiuti_Auth_Gate {

	const REQUIRED_CAPABILITY = 'read';

	/**
	 * Hooks the login-form handler into WordPress. The actual "is this request even one of our
	 * front-end routes" check happens in the router, which calls {@see self::is_authorized()}.
	 */
	public static function init() {
		add_action( 'admin_post_nopriv_tanbiuti_login', array( __CLASS__, 'handle_login' ) );
	}

	/**
	 * Whether the current visitor may see the Minha Conta content.
	 *
	 * @return bool
	 */
	public static function is_authorized() {
		return is_user_logged_in() && current_user_can( self::REQUIRED_CAPABILITY );
	}

	/**
	 * Handles the custom login form's submission via `wp_signon()` — reusing WordPress's own
	 * authentication instead of rolling a parallel one.
	 */
	public static function handle_login() {
		$redirect_to = isset( $_POST['redirect_to'] ) ? esc_url_raw( wp_unslash( $_POST['redirect_to'] ) ) : home_url( '/minha-conta/' );

		if ( ! isset( $_POST['tanbiuti_login_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['tanbiuti_login_nonce'] ) ), 'tanbiuti_login' ) ) {
			wp_safe_redirect( add_query_arg( 'tanbiuti_login_error', '1', $redirect_to ) );
			exit;
		}

		$creds = array(
			'user_login'    => isset( $_POST['log'] ) ? sanitize_text_field( wp_unslash( $_POST['log'] ) ) : '',
			'user_password' => isset( $_POST['pwd'] ) ? wp_unslash( $_POST['pwd'] ) : '', // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.MissingUnslash
			'remember'      => true,
		);

		$user = wp_signon( $creds );

		if ( is_wp_error( $user ) ) {
			wp_safe_redirect( add_query_arg( 'tanbiuti_login_error', '1', $redirect_to ) );
			exit;
		}

		wp_safe_redirect( $redirect_to );
		exit;
	}
}
