<?php
/**
 * Registers the taxonomies used to classify Tanbiuti data.
 *
 * @package Tanbiuti
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Tanbiuti_Taxonomy_Registrar.
 */
class Tanbiuti_Taxonomy_Registrar {

	const TAXONOMIES = array(
		'agendamento_status' => 'tanbiuti_agendamento_status',
		'recibo_tipo'        => 'tanbiuti_recibo_tipo',
	);

	/**
	 * Hooks registration into WordPress.
	 */
	public static function init() {
		add_action( 'init', array( __CLASS__, 'register' ) );
	}

	/**
	 * Registers every Tanbiuti taxonomy. Terms themselves are created dynamically by the
	 * importer from the values actually seen in the source data, not declared here, since the
	 * The source API doesn't expose a closed enum for status/type values.
	 */
	public static function register() {
		register_taxonomy(
			self::TAXONOMIES['agendamento_status'],
			array( Tanbiuti_CPT_Registrar::POST_TYPES['agendamento'] ),
			array(
				'label'        => __( 'Status do agendamento', 'tanbiuti' ),
				'public'       => false,
				'show_ui'      => true,
				'hierarchical' => false,
			)
		);

		register_taxonomy(
			self::TAXONOMIES['recibo_tipo'],
			array( Tanbiuti_CPT_Registrar::POST_TYPES['recibo'] ),
			array(
				'label'        => __( 'Tipo de lançamento', 'tanbiuti' ),
				'public'       => false,
				'show_ui'      => true,
				'hierarchical' => false,
			)
		);
	}
}
