<?php
/**
 * "Agenda" section — a single-day calendar grid (like the real source app's agenda: a time-slot
 * column plus a schedule column with colored appointment blocks positioned/sized by duration),
 * not a flat list. See the "Auditoria visual por tela" section of the project plan for the
 * measurements this is based on (44px per 30-minute row, block colors, etc.).
 *
 * @package Tanbiuti
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

require TANBIUTI_DIR . 'templates/app-header.php';
require TANBIUTI_DIR . 'templates/nav.php';

const TANBIUTI_AGENDA_ROW_HEIGHT = 44;
const TANBIUTI_AGENDA_START_HOUR = 9;
const TANBIUTI_AGENDA_END_HOUR   = 20;

$selected_date = isset( $_GET['data'] ) ? sanitize_text_field( wp_unslash( $_GET['data'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
if ( ! $selected_date || ! preg_match( '/^\d{4}-\d{2}-\d{2}$/', $selected_date ) ) {
	$selected_date = current_time( 'Y-m-d' );
}

$dias_semana    = array(
	'Sunday'    => 'Domingo',
	'Monday'    => 'Segunda-feira',
	'Tuesday'   => 'Terça-feira',
	'Wednesday' => 'Quarta-feira',
	'Thursday'  => 'Quinta-feira',
	'Friday'    => 'Sexta-feira',
	'Saturday'  => 'Sábado',
);
$timestamp      = strtotime( $selected_date );
$dia_semana_en  = gmdate( 'l', $timestamp );
$dia_semana_pt  = isset( $dias_semana[ $dia_semana_en ] ) ? $dias_semana[ $dia_semana_en ] : $dia_semana_en;
$data_formatada = gmdate( 'd/m/Y', $timestamp );

$agendamentos = get_posts(
	array(
		'post_type'      => 'tanbiuti_agendamento',
		'posts_per_page' => -1,
		'meta_key'       => '_tanbiuti_data',
		'meta_value'     => $selected_date,
	)
);

$blocos = array();
foreach ( $agendamentos as $post ) {
	$hora_inicio = (int) get_post_meta( $post->ID, '_tanbiuti_hora_inicio', true );
	$hora_fim    = (int) get_post_meta( $post->ID, '_tanbiuti_hora_fim', true );
	$cliente_id  = (int) get_post_meta( $post->ID, '_tanbiuti_cliente_post_id', true );
	$cliente     = $cliente_id ? get_the_title( $cliente_id ) : __( 'Cliente não identificado', 'tanbiuti' );
	$servico     = get_post_meta( $post->ID, '_tanbiuti_servico', true );

	$grid_start  = TANBIUTI_AGENDA_START_HOUR * 60;
	$top         = max( 0, ( $hora_inicio - $grid_start ) / 30 * TANBIUTI_AGENDA_ROW_HEIGHT );
	$height      = max( 1, ( $hora_fim - $hora_inicio ) / 30 * TANBIUTI_AGENDA_ROW_HEIGHT );
	$is_bloqueio = false !== stripos( $cliente, 'bloque' ) || false !== stripos( $cliente, 'folga' ) || false !== stripos( $cliente, 'almoco' ) || false !== stripos( $cliente, 'almoço' );

	$blocos[] = array(
		'top'      => $top,
		'height'   => $height,
		'cliente'  => $cliente,
		'servico'  => $servico,
		'bloqueio' => $is_bloqueio,
	);
}

$total_rows  = ( TANBIUTI_AGENDA_END_HOUR - TANBIUTI_AGENDA_START_HOUR ) * 2 + 1;
$grid_height = $total_rows * TANBIUTI_AGENDA_ROW_HEIGHT;
?>
<main class="tanbiuti-section tanbiuti-section--agenda">
	<div class="tanbiuti-agenda__header">
		<span class="tanbiuti-agenda__date-label"><?php echo esc_html( "{$dia_semana_pt} - {$data_formatada}" ); ?></span>
		<div class="tanbiuti-datepicker" data-tanbiuti-datepicker data-date="<?php echo esc_attr( $selected_date ); ?>" data-base-url="<?php echo esc_url( Tanbiuti_Frontend_Router::url_for( 'agenda' ) ); ?>">
			<button type="button" class="tanbiuti-datepicker__toggle" aria-label="<?php esc_attr_e( 'Escolher data', 'tanbiuti' ); ?>">
				<svg viewBox="0 0 1024 1024" aria-hidden="true"><path d="M960 95.888 703.776 95.889V32.113c0-17.68-14.32-32-32-32s-32 14.32-32 32v63.76h-256v-63.76c0-17.68-14.32-32-32-32s-32 14.32-32 32v63.76H64c-35.344 0-64 28.656-64 64v800c0 35.343 28.656 64 64 64h896c35.344 0 64-28.657 64-64v-800c0-35.329-28.656-63.985-64-63.985m0 863.985H64v-800h255.776v32.24c0 17.679 14.32 32 32 32s32-14.321 32-32v-32.224h256v32.24c0 17.68 14.32 32 32 32s32-14.32 32-32v-32.24H960zM736 511.888h64c17.664 0 32-14.336 32-32v-64c0-17.664-14.336-32-32-32h-64c-17.664 0-32 14.336-32 32v64c0 17.664 14.336 32 32 32m0 255.984h64c17.664 0 32-14.32 32-32v-64c0-17.664-14.336-32-32-32h-64c-17.664 0-32 14.336-32 32v64c0 17.696 14.336 32 32 32m-192-128h-64c-17.664 0-32 14.336-32 32v64c0 17.68 14.336 32 32 32h64c17.664 0 32-14.32 32-32v-64c0-17.648-14.336-32-32-32m0-255.984h-64c-17.664 0-32 14.336-32 32v64c0 17.664 14.336 32 32 32h64c17.664 0 32-14.336 32-32v-64c0-17.68-14.336-32-32-32m-256 0h-64c-17.664 0-32 14.336-32 32v64c0 17.664 14.336 32 32 32h64c17.664 0 32-14.336 32-32v-64c0-17.68-14.336-32-32-32m0 255.984h-64c-17.664 0-32 14.336-32 32v64c0 17.68 14.336 32 32 32h64c17.664 0 32-14.32 32-32v-64c0-17.648-14.336-32-32-32"/></svg>
			</button>
			<div class="tanbiuti-datepicker__panel" hidden></div>
			<noscript>
				<form method="get">
					<input type="date" name="data" value="<?php echo esc_attr( $selected_date ); ?>" onchange="this.form.submit()" />
				</form>
			</noscript>
		</div>
	</div>

	<div class="tanbiuti-agenda__grid" style="height: <?php echo (int) $grid_height; ?>px;">
		<?php for ( $minutes = TANBIUTI_AGENDA_START_HOUR * 60; $minutes <= TANBIUTI_AGENDA_END_HOUR * 60; $minutes += 30 ) : ?>
			<div class="tanbiuti-agenda__row">
				<span class="tanbiuti-agenda__time"><?php echo esc_html( sprintf( '%02d:%02d', (int) ( $minutes / 60 ), $minutes % 60 ) ); ?></span>
				<span class="tanbiuti-agenda__slot"></span>
			</div>
		<?php endfor; ?>

		<?php foreach ( $blocos as $bloco ) : ?>
			<div class="tanbiuti-agenda__booking<?php echo $bloco['bloqueio'] ? ' is-blocked' : ''; ?>" style="top: <?php echo (int) $bloco['top']; ?>px; height: <?php echo (int) $bloco['height']; ?>px;">
				<strong><?php echo esc_html( $bloco['cliente'] ); ?></strong>
				<?php if ( $bloco['servico'] ) : ?>
					<span><?php echo esc_html( $bloco['servico'] ); ?></span>
				<?php endif; ?>
			</div>
		<?php endforeach; ?>
	</div>

	<?php if ( ! $blocos ) : ?>
		<p class="tanbiuti-agenda__empty"><?php esc_html_e( 'Nenhum agendamento nesta data.', 'tanbiuti' ); ?></p>
	<?php endif; ?>
</main>
<?php
require TANBIUTI_DIR . 'templates/app-footer.php';
