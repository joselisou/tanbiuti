<?php
/**
 * "Vale Rápido" section — this feature is disabled on the source account as of the last
 * reconnaissance (see docs/api-reconnaissance.md), so there is no real data to show yet. The
 * CPT and this template exist so the section is ready the moment the feature is activated and
 * data starts flowing through the extractor.
 *
 * @package Tanbiuti
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

require TANBIUTI_DIR . 'templates/app-header.php';
require TANBIUTI_DIR . 'templates/nav.php';

$vales = get_posts(
	array(
		'post_type'      => 'tanbiuti_vale_rapido',
		'posts_per_page' => 50,
	)
);
?>
<main class="tanbiuti-section tanbiuti-section--vale-rapido">
	<?php if ( ! $vales ) : ?>
		<p><?php esc_html_e( 'Esta funcionalidade ainda não está ativa na conta de origem — não há dados para mostrar.', 'tanbiuti' ); ?></p>
	<?php else : ?>
		<ul class="tanbiuti-list">
			<?php foreach ( $vales as $post ) : ?>
				<li class="tanbiuti-card">
					<span class="tanbiuti-card__title"><?php echo esc_html( $post->post_title ); ?></span>
					<span class="tanbiuti-card__value"><?php echo esc_html( Tanbiuti_Formatting::money( get_post_meta( $post->ID, '_tanbiuti_valor', true ) ) ); ?></span>
				</li>
			<?php endforeach; ?>
		</ul>
	<?php endif; ?>
</main>
<?php
require TANBIUTI_DIR . 'templates/app-footer.php';
