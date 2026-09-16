<?php
/**
 * A topbar variant for sub-route screens the real app opens as their own page (e.g. Comissões'
 * "Serviços" detail) — a back arrow instead of the hamburger/drawer, and a centered title.
 * Expects the including template to set `$tanbiuti_back_url` and `$tanbiuti_back_title` first.
 *
 * @package Tanbiuti
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<div class="tanbiuti-topbar">
	<a href="<?php echo esc_url( $tanbiuti_back_url ); ?>" class="tanbiuti-back-arrow" aria-label="<?php esc_attr_e( 'Voltar', 'tanbiuti' ); ?>">
		<svg viewBox="0 0 1024 1024" aria-hidden="true"><path d="M752.145 0c8.685 0 17.572 3.434 24.237 10.099 13.33 13.33 13.33 35.143 0 48.473L320.126 515.03l449.591 449.591c13.33 13.33 13.33 35.144 0 48.474-13.33 13.33-35.142 13.33-48.472 0L247.418 539.268c-13.33-13.33-13.33-35.144 0-48.474L727.91 10.1C734.575 3.435 743.46.002 752.146.002z"/></svg>
	</a>
	<span class="tanbiuti-topbar__title"><?php echo esc_html( $tanbiuti_back_title ); ?></span>
	<span class="tanbiuti-topbar__action"></span>
</div>
