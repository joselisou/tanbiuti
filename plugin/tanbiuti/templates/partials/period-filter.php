<?php
/**
 * Shared date-range filter bar (start date, end date, "Buscar") used by Comandas and Comissões,
 * matching the real app's own filter header. Expects the including template to have already set
 * `$tanbiuti_filter_inicio` and `$tanbiuti_filter_fim` ("YYYY-MM-DD" strings). If the including template
 * also sets `$tanbiuti_filter_extra_html` (a pre-rendered, already-escaped HTML string), it is
 * rendered inside the same gray bar, below the date row — e.g. Comissões' "Filtrar por recibo"
 * select, which the real app keeps in the same bar as the date filter.
 *
 * @package Tanbiuti
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$tanbiuti_calendar_icon = '<svg viewBox="0 0 1024 1024" aria-hidden="true"><path d="M960 95.888 703.776 95.889V32.113c0-17.68-14.32-32-32-32s-32 14.32-32 32v63.76h-256v-63.76c0-17.68-14.32-32-32-32s-32 14.32-32 32v63.76H64c-35.344 0-64 28.656-64 64v800c0 35.343 28.656 64 64 64h896c35.344 0 64-28.657 64-64v-800c0-35.329-28.656-63.985-64-63.985m0 863.985H64v-800h255.776v32.24c0 17.679 14.32 32 32 32s32-14.321 32-32v-32.224h256v32.24c0 17.68 14.32 32 32 32s32-14.32 32-32v-32.24H960zM736 511.888h64c17.664 0 32-14.336 32-32v-64c0-17.664-14.336-32-32-32h-64c-17.664 0-32 14.336-32 32v64c0 17.664 14.336 32 32 32m0 255.984h64c17.664 0 32-14.32 32-32v-64c0-17.664-14.336-32-32-32h-64c-17.664 0-32 14.336-32 32v64c0 17.696 14.336 32 32 32m-192-128h-64c-17.664 0-32 14.336-32 32v64c0 17.68 14.336 32 32 32h64c17.664 0 32-14.32 32-32v-64c0-17.648-14.336-32-32-32m0-255.984h-64c-17.664 0-32 14.336-32 32v64c0 17.664 14.336 32 32 32h64c17.664 0 32-14.336 32-32v-64c0-17.68-14.336-32-32-32m-256 0h-64c-17.664 0-32 14.336-32 32v64c0 17.664 14.336 32 32 32h64c17.664 0 32-14.336 32-32v-64c0-17.68-14.336-32-32-32m0 255.984h-64c-17.664 0-32 14.336-32 32v64c0 17.68 14.336 32 32 32h64c17.664 0 32-14.32 32-32v-64c0-17.648-14.336-32-32-32"/></svg>';

$tanbiuti_period_fields = array(
	'inicio' => $tanbiuti_filter_inicio,
	'fim'    => $tanbiuti_filter_fim,
);
?>
<div class="tanbiuti-filterbar">
	<form method="get" class="tanbiuti-filterbar__row">
		<?php foreach ( $tanbiuti_period_fields as $tanbiuti_field_id => $tanbiuti_field_iso ) : ?>
			<div class="tanbiuti-datepicker tanbiuti-datepicker--field" data-tanbiuti-datepicker data-date="<?php echo esc_attr( $tanbiuti_field_iso ); ?>" data-target="tanbiuti-period-<?php echo esc_attr( $tanbiuti_field_id ); ?>">
				<input type="hidden" id="tanbiuti-period-<?php echo esc_attr( $tanbiuti_field_id ); ?>" name="<?php echo esc_attr( $tanbiuti_field_id ); ?>" value="<?php echo esc_attr( $tanbiuti_field_iso ); ?>" />
				<button type="button" class="tanbiuti-datepicker__toggle tanbiuti-datepicker__toggle--field">
					<span class="tanbiuti-datepicker__display"><?php echo esc_html( gmdate( 'd/m/Y', strtotime( $tanbiuti_field_iso ) ) ); ?></span>
					<?php echo $tanbiuti_calendar_icon; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- static SVG constant. ?>
				</button>
				<div class="tanbiuti-datepicker__panel" hidden></div>
			</div>
		<?php endforeach; ?>
		<button type="submit" class="tanbiuti-btn tanbiuti-btn--primary tanbiuti-filterbar__submit"><?php esc_html_e( 'Buscar', 'tanbiuti' ); ?></button>
	</form>
	<?php if ( ! empty( $tanbiuti_filter_extra_html ) ) : ?>
		<?php echo $tanbiuti_filter_extra_html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- pre-rendered by the caller, already escaped there. ?>
	<?php endif; ?>
</div>
