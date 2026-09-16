<?php
/**
 * "Comandas" section — lists tanbiuti_comanda posts with their resolved client name.
 *
 * @package Tanbiuti
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

require TANBIUTI_DIR . 'templates/app-header.php';
require TANBIUTI_DIR . 'templates/nav.php';

$tanbiuti_valid_date = function ( $value, $fallback ) {
	$value = is_string( $value ) ? $value : '';
	return preg_match( '/^\\d{4}-\\d{2}-\\d{2}$/', $value ) ? $value : $fallback;
};

$tanbiuti_filter_inicio = $tanbiuti_valid_date(
	isset( $_GET['inicio'] ) ? wp_unslash( $_GET['inicio'] ) : '', // phpcs:ignore WordPress.Security.NonceVerification.Recommended, WordPress.Security.ValidatedSanitizedInput.MissingUnslash
	current_time( 'Y-m-01' )
);
$tanbiuti_filter_fim    = $tanbiuti_valid_date(
	isset( $_GET['fim'] ) ? wp_unslash( $_GET['fim'] ) : '', // phpcs:ignore WordPress.Security.NonceVerification.Recommended, WordPress.Security.ValidatedSanitizedInput.MissingUnslash
	current_time( 'Y-m-d' )
);

$comandas = get_posts(
	array(
		'post_type'      => 'tanbiuti_comanda',
		'posts_per_page' => 50,
		'orderby'        => 'meta_value',
		'meta_key'       => '_tanbiuti_data',
		'order'          => 'DESC',
		'meta_query'     => array( // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query
			array(
				'key'     => '_tanbiuti_data',
				'value'   => array( $tanbiuti_filter_inicio, $tanbiuti_filter_fim ),
				'compare' => 'BETWEEN',
				'type'    => 'DATE',
			),
		),
	)
);
?>
<main class="tanbiuti-section tanbiuti-section--comandas">
	<?php require TANBIUTI_DIR . 'templates/partials/period-filter.php'; ?>

	<?php if ( ! $comandas ) : ?>
		<p><?php esc_html_e( 'Nenhuma comanda importada ainda.', 'tanbiuti' ); ?></p>
	<?php else : ?>
		<ul class="tanbiuti-list">
			<?php
			foreach ( $comandas as $post ) :
				$cliente_post_id = (int) get_post_meta( $post->ID, '_tanbiuti_cliente_post_id', true );
				$cliente_nome    = $cliente_post_id ? get_the_title( $cliente_post_id ) : __( 'Cliente não identificado', 'tanbiuti' );
				?>
				<li class="tanbiuti-card">
					<span class="tanbiuti-card__date"><?php echo esc_html( get_post_meta( $post->ID, '_tanbiuti_data', true ) ); ?></span>
					<span class="tanbiuti-card__title"><?php echo esc_html( $post->post_title ); ?> — <?php echo esc_html( $cliente_nome ); ?></span>
					<span class="tanbiuti-card__value"><?php echo esc_html( Tanbiuti_Formatting::money( get_post_meta( $post->ID, '_tanbiuti_total', true ) ) ); ?></span>
				</li>
			<?php endforeach; ?>
		</ul>
	<?php endif; ?>
</main>
<?php
require TANBIUTI_DIR . 'templates/app-footer.php';
