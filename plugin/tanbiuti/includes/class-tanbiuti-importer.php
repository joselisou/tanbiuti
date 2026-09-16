<?php
/**
 * Imports a WXR file into the Tanbiuti post types, upserting by `_tanbiuti_source_id` so a file
 * can be re-imported without duplicating posts.
 *
 * @package Tanbiuti
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Tanbiuti_Importer.
 */
class Tanbiuti_Importer {

	const SOURCE_ID_META = '_tanbiuti_source_id';

	/**
	 * No hooks to register today — the importer is invoked directly by the admin screen and the
	 * WP-CLI command, not by a WordPress action.
	 */
	public static function init() {}

	/**
	 * Imports every `<item>` in a WXR file.
	 *
	 * @param string $file_path Absolute path to the WXR (XML) file.
	 * @return array{created:int,updated:int,total:int} Counts for the caller to report back.
	 *
	 * @throws InvalidArgumentException If the file doesn't exist or isn't well-formed XML.
	 */
	public static function import_file( $file_path ) {
		if ( ! file_exists( $file_path ) ) {
			// phpcs:ignore WordPress.Security.EscapeOutput.ExceptionNotEscaped -- caught internally, never echoed raw.
			throw new InvalidArgumentException( "WXR file not found: {$file_path}" );
		}

		libxml_use_internal_errors( true );
		$xml = simplexml_load_file( $file_path );

		if ( false === $xml ) {
			$errors = array_map(
				static function ( $error ) {
					return trim( $error->message );
				},
				libxml_get_errors()
			);
			libxml_clear_errors();
			// phpcs:ignore WordPress.Security.EscapeOutput.ExceptionNotEscaped -- caught internally, never echoed raw.
			throw new InvalidArgumentException( 'Invalid WXR file: ' . implode( '; ', $errors ) );
		}

		$wp_namespace = 'http://wordpress.org/export/1.2/';
		$created      = 0;
		$updated      = 0;

		foreach ( $xml->channel->item as $item ) {
			$wp_fields = $item->children( $wp_namespace );
			$source_id = self::extract_source_id( $wp_fields );

			if ( null === $source_id ) {
				continue; // Not an Tanbiuti item (no source id), skip.
			}

			$post_type = (string) $wp_fields->post_type;
			$upserted  = self::upsert_post( $item, $wp_fields, $post_type, $source_id );

			if ( null === $upserted ) {
				continue;
			}

			self::sync_postmeta( $upserted['post_id'], $wp_fields );
			self::sync_terms( $upserted['post_id'], $item, $post_type );

			if ( $upserted['was_existing'] ) {
				++$updated;
			} else {
				++$created;
			}
		}

		self::resolve_relations();

		return array(
			'created' => $created,
			'updated' => $updated,
			'total'   => $created + $updated,
		);
	}

	/**
	 * Reads the `_tanbiuti_source_id` value out of an item's `<wp:postmeta>` entries.
	 *
	 * @param SimpleXMLElement $wp_fields The item's `wp:` namespaced children.
	 * @return string|null
	 */
	private static function extract_source_id( SimpleXMLElement $wp_fields ) {
		foreach ( $wp_fields->postmeta as $meta ) {
			if ( self::SOURCE_ID_META === (string) $meta->meta_key ) {
				return (string) $meta->meta_value;
			}
		}
		return null;
	}

	/**
	 * Finds an existing post by `(post_type, _tanbiuti_source_id)` and updates it, or inserts a new one.
	 *
	 * @param SimpleXMLElement $item      The `<item>` element.
	 * @param SimpleXMLElement $wp_fields The item's `wp:` namespaced children.
	 * @param string           $post_type Target post type.
	 * @param string           $source_id Value of `_tanbiuti_source_id` for this item.
	 * @return array{post_id:int,was_existing:bool}|null Null if `wp_insert_post`/`wp_update_post` failed.
	 */
	private static function upsert_post( SimpleXMLElement $item, SimpleXMLElement $wp_fields, $post_type, $source_id ) {
		$existing_id = self::find_existing_post_id( $post_type, $source_id );

		$postarr = array(
			'post_type'    => $post_type,
			'post_title'   => (string) $item->title,
			'post_status'  => (string) $wp_fields->status ? (string) $wp_fields->status : 'publish',
			'post_date'    => (string) $wp_fields->post_date,
			'post_content' => '',
		);

		if ( $existing_id ) {
			$postarr['ID'] = $existing_id;
			$result        = wp_update_post( $postarr, true );
		} else {
			$result = wp_insert_post( $postarr, true );
		}

		if ( is_wp_error( $result ) ) {
			return null;
		}

		return array(
			'post_id'      => (int) $result,
			'was_existing' => null !== $existing_id,
		);
	}

	/**
	 * Looks up a post by post type and `_tanbiuti_source_id` meta value.
	 *
	 * @param string $post_type Post type to search.
	 * @param string $source_id Source id to match.
	 * @return int|null
	 */
	private static function find_existing_post_id( $post_type, $source_id ) {
		$query = new WP_Query(
			array(
				'post_type'              => $post_type,
				'post_status'            => 'any',
				'meta_key'               => self::SOURCE_ID_META,
				'meta_value'             => $source_id,
				'fields'                 => 'ids',
				'posts_per_page'         => 1,
				'no_found_rows'          => true,
				'update_post_meta_cache' => false,
				'update_post_term_cache' => false,
			)
		);

		return $query->posts ? (int) $query->posts[0] : null;
	}

	/**
	 * Replaces every `_tanbiuti_*` postmeta on a post with the values from its WXR item.
	 *
	 * @param int              $post_id   Target post.
	 * @param SimpleXMLElement $wp_fields The item's `wp:` namespaced children.
	 */
	private static function sync_postmeta( $post_id, SimpleXMLElement $wp_fields ) {
		foreach ( $wp_fields->postmeta as $meta ) {
			$key   = (string) $meta->meta_key;
			$value = (string) $meta->meta_value;
			update_post_meta( $post_id, $key, $value );
		}
	}

	/**
	 * Assigns every `<category domain="...">` term on the item to the post.
	 *
	 * @param int              $post_id   Target post.
	 * @param SimpleXMLElement $item      The `<item>` element.
	 * @param string           $post_type Target post type (used to skip taxonomies not
	 *                                    registered for it, which would otherwise warn).
	 */
	private static function sync_terms( $post_id, SimpleXMLElement $item, $post_type ) {
		if ( ! isset( $item->category ) ) {
			return;
		}

		$terms_by_taxonomy = array();

		foreach ( $item->category as $category ) {
			$taxonomy = (string) $category->attributes()->domain;
			if ( ! $taxonomy || ! is_object_in_taxonomy( $post_type, $taxonomy ) ) {
				continue;
			}
			$terms_by_taxonomy[ $taxonomy ][] = (string) $category;
		}

		foreach ( $terms_by_taxonomy as $taxonomy => $term_names ) {
			wp_set_object_terms( $post_id, $term_names, $taxonomy, false );
		}
	}

	/**
	 * Second pass: resolves every `_tanbiuti_*_source_id` meta (e.g. `_tanbiuti_cliente_source_id`) into
	 * the matching post's real ID (`_tanbiuti_cliente_post_id`). Runs after every item has been
	 * imported so relations work regardless of the order posts appeared in the WXR file.
	 */
	private static function resolve_relations() {
		global $wpdb;

		$like = $wpdb->esc_like( '_tanbiuti_' ) . '%' . $wpdb->esc_like( '_source_id' );

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		$rows = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT post_id, meta_key, meta_value FROM {$wpdb->postmeta} WHERE meta_key LIKE %s",
				$like
			)
		);

		foreach ( $rows as $row ) {
			if ( self::SOURCE_ID_META === $row->meta_key ) {
				continue; // That's the post's own identity, not a relation to resolve.
			}

			// Turns e.g. meta key "_tanbiuti_cliente_source_id" into the relation name "cliente",
			// so it can be resolved into the target post's id under "_tanbiuti_cliente_post_id".
			$relation   = str_replace( array( '_tanbiuti_', '_source_id' ), '', $row->meta_key );
			$target_ids = get_posts(
				array(
					// Not 'any': our post types are registered with `public => false`, which
					// makes `exclude_from_search` default to true — and 'any' silently skips
					// every post type that excludes itself from search.
					'post_type'              => array_values( Tanbiuti_CPT_Registrar::POST_TYPES ),
					'post_status'            => 'any',
					'meta_key'               => self::SOURCE_ID_META,
					'meta_value'             => $row->meta_value,
					'fields'                 => 'ids',
					'posts_per_page'         => 1,
					'no_found_rows'          => true,
					'update_post_meta_cache' => false,
					'update_post_term_cache' => false,
				)
			);

			if ( $target_ids ) {
				update_post_meta( $row->post_id, "_tanbiuti_{$relation}_post_id", (int) $target_ids[0] );
			}
		}
	}
}
