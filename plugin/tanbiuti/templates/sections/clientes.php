<?php
/**
 * "Clientes" section — lists tanbiuti_cliente posts.
 *
 * @package Tanbiuti
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// A plain, generic person-silhouette icon (own artwork) — not a copy of any
// icon from the source app.
$avatar_icon = '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 12a5 5 0 1 0 0-10 5 5 0 0 0 0 10Zm0 2c-4.4 0-9 2.2-9 5v3h18v-3c0-2.8-4.6-5-9-5Z"/></svg>';
// Generic circle-plus glyph (own artwork: a circle plus a cross, not copyrightable geometry) —
// matches the real app's small purple "add" button pinned to the topbar's right edge.
$icon_add_circle = '<svg viewBox="0 0 24 24" aria-hidden="true"><circle cx="12" cy="12" r="12" fill="currentcolor" /><rect x="11" y="6" width="2" height="12" fill="#fff" /><rect x="6" y="11" width="12" height="2" fill="#fff" /></svg>';

$GLOBALS['tanbiuti_topbar_action'] = sprintf(
	'<button type="button" class="tanbiuti-topbar__add" aria-label="%s">%s</button>',
	esc_attr__( 'Adicionar cliente', 'tanbiuti' ),
	$icon_add_circle
);

require TANBIUTI_DIR . 'templates/app-header.php';
require TANBIUTI_DIR . 'templates/nav.php';

$tanbiuti_search = isset( $_GET['busca'] ) ? sanitize_text_field( wp_unslash( $_GET['busca'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended

$clientes = get_posts(
	array(
		'post_type'      => 'tanbiuti_cliente',
		'posts_per_page' => 100,
		'orderby'        => 'title',
		'order'          => 'ASC',
		's'              => $tanbiuti_search,
	)
);
?>
<main class="tanbiuti-section tanbiuti-section--clientes">
	<div class="tanbiuti-filterbar">
		<form method="get" class="tanbiuti-search">
			<label for="tanbiuti-search-kind" class="screen-reader-text"><?php esc_html_e( 'Buscar por', 'tanbiuti' ); ?></label>
			<select id="tanbiuti-search-kind" class="tanbiuti-search__kind" disabled>
				<option><?php esc_html_e( 'Nome', 'tanbiuti' ); ?></option>
			</select>
			<label for="tanbiuti-search-busca" class="screen-reader-text"><?php esc_html_e( 'Procurar Clientes:', 'tanbiuti' ); ?></label>
			<input type="text" id="tanbiuti-search-busca" class="tanbiuti-search__input" name="busca" value="<?php echo esc_attr( $tanbiuti_search ); ?>" placeholder="<?php esc_attr_e( 'Procurar Clientes:', 'tanbiuti' ); ?>" />
			<button type="submit" class="tanbiuti-btn tanbiuti-btn--primary tanbiuti-search__submit"><?php esc_html_e( 'Buscar', 'tanbiuti' ); ?></button>
		</form>
	</div>

	<?php if ( ! $clientes ) : ?>
		<p>
			<?php
			echo esc_html(
				$tanbiuti_search
					? __( 'Nenhum cliente encontrado para essa busca.', 'tanbiuti' )
					: __( 'Nenhum cliente importado ainda.', 'tanbiuti' )
			);
			?>
		</p>
	<?php else : ?>
		<h2 class="tanbiuti-panel__title"><?php esc_html_e( 'Últimos Atendimentos', 'tanbiuti' ); ?></h2>
		<ul class="tanbiuti-list" data-tanbiuti-no-instant-filter>
			<?php foreach ( $clientes as $post ) : ?>
				<li class="tanbiuti-card">
					<span class="tanbiuti-card__avatar"><?php echo $avatar_icon; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- static, hand-written SVG constant, no user input. ?></span>
					<span class="tanbiuti-card__title"><?php echo esc_html( $post->post_title ); ?></span>
					<span class="tanbiuti-card__meta"><?php echo esc_html( get_post_meta( $post->ID, '_tanbiuti_celular', true ) ); ?></span>
					<span class="tanbiuti-card__meta"><?php echo esc_html( get_post_meta( $post->ID, '_tanbiuti_email', true ) ); ?></span>
				</li>
			<?php endforeach; ?>
		</ul>
	<?php endif; ?>
</main>
<?php
require TANBIUTI_DIR . 'templates/app-footer.php';
