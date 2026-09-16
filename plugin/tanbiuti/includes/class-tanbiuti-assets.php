<?php
/**
 * Enqueues the compiled front-end assets (assets/build/index.css, assets/build/index.js) only on
 * the plugin's own `/minha-conta/*` routes and its login screen — never site-wide.
 *
 * @package Tanbiuti
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Tanbiuti_Assets.
 */
class Tanbiuti_Assets {

	/**
	 * Hooks asset enqueueing into WordPress.
	 */
	public static function init() {
		add_action( 'wp_enqueue_scripts', array( __CLASS__, 'maybe_enqueue' ) );
	}

	/**
	 * Enqueues the built CSS/JS bundle when the current request is one of our front-end routes.
	 */
	public static function maybe_enqueue() {
		if ( ! get_query_var( 'tanbiuti_account' ) ) {
			return;
		}

		$asset_file = TANBIUTI_DIR . 'assets/build/index.asset.php';

		if ( ! file_exists( $asset_file ) ) {
			return; // Front-end assets haven't been built yet (`npm run build`).
		}

		$asset = require $asset_file;

		// The design tokens (see assets/src/scss/abstracts/_variables.scss) were measured from
		// the real source app, which uses Inter for UI text and Raleway for headings — neither
		// is bundled with WordPress, so both are pulled from Google Fonts.
		wp_enqueue_style(
			'tanbiuti-fonts',
			'https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Raleway:wght@600;700&display=swap',
			array(),
			null // phpcs:ignore WordPress.WP.EnqueuedResourceParameters.MissingVersion -- external URL, Google's own cache-busting applies.
		);
		wp_enqueue_style( 'tanbiuti', TANBIUTI_URL . 'assets/build/style-index.css', array( 'tanbiuti-fonts' ), $asset['version'] );
		wp_enqueue_script( 'tanbiuti', TANBIUTI_URL . 'assets/build/index.js', $asset['dependencies'], $asset['version'], true );
	}
}
