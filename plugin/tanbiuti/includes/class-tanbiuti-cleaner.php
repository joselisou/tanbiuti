<?php
/**
 * Deletes every post imported by Tanbiuti, so a WXR file can be re-imported from a clean slate.
 *
 * @package Tanbiuti
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Tanbiuti_Cleaner.
 */
class Tanbiuti_Cleaner {

	const BATCH_SIZE = 200;

	/**
	 * No hooks to register — invoked directly by the admin screen and the WP-CLI command.
	 */
	public static function init() {}

	/**
	 * Deletes every Tanbiuti post (any of its registered post types) that carries the
	 * `_tanbiuti_source_id` meta — i.e. every post that came from an import, never a post a human
	 * created by hand in one of these post types.
	 *
	 * @return int Number of posts deleted.
	 */
	public static function clean() {
		$deleted = 0;

		do {
			$ids = get_posts(
				array(
					'post_type'              => array_values( Tanbiuti_CPT_Registrar::POST_TYPES ),
					'post_status'            => 'any',
					'meta_key'               => Tanbiuti_Importer::SOURCE_ID_META,
					'fields'                 => 'ids',
					'posts_per_page'         => self::BATCH_SIZE,
					'no_found_rows'          => true,
					'update_post_meta_cache' => false,
					'update_post_term_cache' => false,
				)
			);

			foreach ( $ids as $id ) {
				if ( wp_delete_post( $id, true ) ) {
					++$deleted;
				}
			}

			$batch_count = count( $ids );
		} while ( self::BATCH_SIZE === $batch_count );

		return $deleted;
	}
}
