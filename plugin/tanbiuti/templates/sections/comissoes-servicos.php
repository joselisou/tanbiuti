<?php
/**
 * "Serviços" detail — the real app opens this as its own screen (back arrow instead of the
 * hamburger/drawer) when the Comissões "Serviços" panel is tapped, rather than expanding inline.
 * Lists every day in the current período as its own card (date + produção/rateio), each
 * expandable to the day's individual service line items.
 *
 * @package Tanbiuti
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

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
$tanbiuti_filter_recibo = isset( $_GET['recibo'] ) && 'nao_pago' === $_GET['recibo'] ? 'nao_pago' : 'todos'; // phpcs:ignore WordPress.Security.NonceVerification.Recommended

$recibos = get_posts(
	array(
		'post_type'      => 'tanbiuti_recibo',
		'posts_per_page' => 200, // phpcs:ignore WordPress.WP.PostsPerPage.posts_per_page_posts_per_page -- a full período's worth of line items, not a paginated listing.
		'orderby'        => 'date',
		'order'          => 'DESC',
		'date_query'     => array(
			array(
				'after'     => $tanbiuti_filter_inicio,
				'before'    => $tanbiuti_filter_fim,
				'inclusive' => true,
			),
		),
		'meta_query'     => 'nao_pago' === $tanbiuti_filter_recibo // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query
			? array(
				array(
					'key'   => '_tanbiuti_status',
					'value' => '0',
				),
			)
			: array(),
	)
);

$tanbiuti_by_day = array();
foreach ( $recibos as $post ) {
	$valor    = (float) get_post_meta( $post->ID, '_tanbiuti_valor', true );
	$comissao = (float) get_post_meta( $post->ID, '_tanbiuti_comissao', true );
	$dia      = get_the_date( 'Y-m-d', $post );

	if ( ! isset( $tanbiuti_by_day[ $dia ] ) ) {
		$tanbiuti_by_day[ $dia ] = array(
			'producao' => 0.0,
			'rateio'   => 0.0,
			'itens'    => array(),
		);
	}
	$tanbiuti_by_day[ $dia ]['producao'] += $valor;
	$tanbiuti_by_day[ $dia ]['rateio']   += $valor * ( $comissao / 100 );
	$tanbiuti_by_day[ $dia ]['itens'][]   = $post;
}
krsort( $tanbiuti_by_day );

$money               = array( 'Tanbiuti_Formatting', 'money' );
$tanbiuti_back_url   = add_query_arg(
	array(
		'inicio' => $tanbiuti_filter_inicio,
		'fim'    => $tanbiuti_filter_fim,
		'recibo' => $tanbiuti_filter_recibo,
	),
	Tanbiuti_Frontend_Router::url_for( 'comissoes' )
);
$tanbiuti_back_title = __( 'Serviços', 'tanbiuti' );

require TANBIUTI_DIR . 'templates/app-header.php';
require TANBIUTI_DIR . 'templates/partials/back-topbar.php';
?>
<main class="tanbiuti-section tanbiuti-section--comissoes-servicos">
	<?php if ( ! $tanbiuti_by_day ) : ?>
		<p><?php esc_html_e( 'Nenhum serviço no período selecionado.', 'tanbiuti' ); ?></p>
	<?php endif; ?>
	<?php foreach ( $tanbiuti_by_day as $tanbiuti_dia => $tanbiuti_day_data ) : ?>
		<div class="tanbiuti-panel" data-tanbiuti-panel-toggle>
			<div class="tanbiuti-panel__header">
				<div class="tanbiuti-panel__body">
					<span class="tanbiuti-panel__day-date"><?php echo esc_html( gmdate( 'd/m', strtotime( $tanbiuti_dia ) ) ); ?></span>
					<div class="tanbiuti-panel__columns">
						<div class="tanbiuti-panel__column">
							<span class="tanbiuti-panel__column-label"><?php esc_html_e( 'produção', 'tanbiuti' ); ?></span>
							<span class="tanbiuti-panel__column-value"><?php echo esc_html( $money( $tanbiuti_day_data['producao'] ) ); ?></span>
						</div>
						<div class="tanbiuti-panel__column">
							<span class="tanbiuti-panel__column-label"><?php esc_html_e( 'rateio', 'tanbiuti' ); ?></span>
							<span class="tanbiuti-panel__column-value"><?php echo esc_html( $money( $tanbiuti_day_data['rateio'] ) ); ?></span>
						</div>
					</div>
				</div>
				<span class="tanbiuti-panel__chevron" aria-hidden="true">&rsaquo;</span>
			</div>
			<div class="tanbiuti-panel__collapse" hidden>
				<ul class="tanbiuti-list">
					<?php foreach ( $tanbiuti_day_data['itens'] as $tanbiuti_item ) : ?>
						<?php
						$tanbiuti_valor        = (float) get_post_meta( $tanbiuti_item->ID, '_tanbiuti_valor', true );
						$tanbiuti_comissao     = (float) get_post_meta( $tanbiuti_item->ID, '_tanbiuti_comissao', true );
						$tanbiuti_comanda_id   = (int) get_post_meta( $tanbiuti_item->ID, '_tanbiuti_comanda_post_id', true );
						$tanbiuti_numero       = $tanbiuti_comanda_id ? get_post_meta( $tanbiuti_comanda_id, '_tanbiuti_numero', true ) : '';
						$tanbiuti_cliente_id   = $tanbiuti_comanda_id ? (int) get_post_meta( $tanbiuti_comanda_id, '_tanbiuti_cliente_post_id', true ) : 0;
						$tanbiuti_cliente_nome = $tanbiuti_cliente_id ? get_the_title( $tanbiuti_cliente_id ) : __( 'Cliente não identificado', 'tanbiuti' );
						?>
						<li class="tanbiuti-card tanbiuti-card--servico">
							<span class="tanbiuti-card__meta">
								<?php
								printf(
									/* translators: 1: comanda number, 2: date */
									esc_html__( 'Nº %1$s %2$s', 'tanbiuti' ),
									esc_html( $tanbiuti_numero ? $tanbiuti_numero : '—' ),
									esc_html( gmdate( 'd/m/Y', strtotime( $tanbiuti_dia ) ) )
								);
								?>
							</span>
							<span class="tanbiuti-card__title"><?php echo esc_html( $tanbiuti_item->post_title ); ?></span>
							<span class="tanbiuti-card__meta">
								<?php
								printf(
									/* translators: %s: client name */
									esc_html__( 'Cliente: %s', 'tanbiuti' ),
									esc_html( $tanbiuti_cliente_nome )
								);
								?>
							</span>
							<span class="tanbiuti-card__meta">
								<?php
								printf(
									/* translators: %s: formatted currency value */
									esc_html__( 'Valor: %s', 'tanbiuti' ),
									esc_html( $money( $tanbiuti_valor ) )
								);
								?>
							</span>
							<span class="tanbiuti-card__value">
								<?php
								printf(
									/* translators: 1: formatted commission value, 2: commission percentage */
									esc_html__( 'Comissão: %1$s (%2$s%%)', 'tanbiuti' ),
									esc_html( $money( $tanbiuti_valor * ( $tanbiuti_comissao / 100 ) ) ),
									esc_html( $tanbiuti_comissao )
								);
								?>
							</span>
						</li>
					<?php endforeach; ?>
				</ul>
			</div>
		</div>
	<?php endforeach; ?>
</main>
<?php
require TANBIUTI_DIR . 'templates/app-footer.php';
