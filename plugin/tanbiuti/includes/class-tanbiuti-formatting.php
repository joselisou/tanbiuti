<?php
/**
 * Shared display formatting used by the front-end templates.
 *
 * @package Tanbiuti
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Tanbiuti_Formatting.
 */
class Tanbiuti_Formatting {

	/**
	 * Formats a value as Brazilian currency (e.g. "R$ 1.234,56"), independent of the WordPress
	 * install's configured locale — this is Brazilian salon data, so it should always read that
	 * way, unlike `number_format_i18n()` which follows the site's language setting.
	 *
	 * @param float $value Amount to format.
	 * @return string
	 */
	public static function money( $value ) {
		return 'R$ ' . number_format( (float) $value, 2, ',', '.' );
	}
}
