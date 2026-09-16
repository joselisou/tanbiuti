<?php
/**
 * WP-CLI commands: `wp tanbiuti import-wxr <path>` and `wp tanbiuti clean`.
 *
 * @package Tanbiuti
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Tanbiuti_CLI_Commands.
 */
class Tanbiuti_CLI_Commands {

	/**
	 * Registers the `wp tanbiuti` command family. Only called when WP_CLI is loaded.
	 */
	public static function init() {
		WP_CLI::add_command( 'tanbiuti import-wxr', array( __CLASS__, 'import_wxr' ) );
		WP_CLI::add_command( 'tanbiuti clean', array( __CLASS__, 'clean' ) );
	}

	/**
	 * Imports a WXR file.
	 *
	 * ## OPTIONS
	 *
	 * <path>
	 * : Absolute or relative path to the WXR (XML) file to import.
	 *
	 * ## EXAMPLES
	 *
	 *     wp tanbiuti import-wxr ../../data/fake/tanbiuti-fake-dataset.xml
	 *
	 * @param array $args Positional arguments.
	 */
	public static function import_wxr( $args ) {
		list( $path ) = $args;

		try {
			$result = Tanbiuti_Importer::import_file( $path );
		} catch ( InvalidArgumentException $e ) {
			WP_CLI::error( $e->getMessage() );
			return;
		}

		WP_CLI::success(
			sprintf(
				'Imported %d posts (%d created, %d updated).',
				$result['total'],
				$result['created'],
				$result['updated']
			)
		);
	}

	/**
	 * Deletes every post previously imported by Tanbiuti.
	 *
	 * ## EXAMPLES
	 *
	 *     wp tanbiuti clean
	 */
	public static function clean() {
		$deleted = Tanbiuti_Cleaner::clean();
		WP_CLI::success( sprintf( 'Deleted %d posts.', $deleted ) );
	}
}
