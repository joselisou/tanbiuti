<?php
/**
 * Shared top bar + off-canvas navigation, included by every `templates/sections/*.php`
 * template. The real source app uses a hamburger/off-canvas nav at every breakpoint (not just
 * mobile) — a fixed top bar with a hamburger button and a centered title/selector, and the nav
 * itself slides in as a drawer when toggled (see assets/src/ts/nav-toggle.ts). Each nav item has
 * a leading icon and "Sair" sits in the normal list flow, not pinned to the drawer's bottom.
 *
 * Nav icons are Simple Line Icons (MIT license, thesabbir/simple-line-icons on GitHub) — the
 * same open icon font the source app itself uses for these links, so this reproduces the
 * source's visual language without depending on (or copying) any of its own drawn assets.
 * The "Editar perfil" pencil is hand-drawn (own artwork), since that icon there is a bespoke
 * image asset in the source app, not from that open icon set.
 *
 * @package Tanbiuti
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$icons     = array(
	'agenda'      => '<svg class="tanbiuti-nav__icon" viewBox="0 0 1024 1024" aria-hidden="true"><path d="M960 95.888 703.776 95.889V32.113c0-17.68-14.32-32-32-32s-32 14.32-32 32v63.76h-256v-63.76c0-17.68-14.32-32-32-32s-32 14.32-32 32v63.76H64c-35.344 0-64 28.656-64 64v800c0 35.343 28.656 64 64 64h896c35.344 0 64-28.657 64-64v-800c0-35.329-28.656-63.985-64-63.985m0 863.985H64v-800h255.776v32.24c0 17.679 14.32 32 32 32s32-14.321 32-32v-32.224h256v32.24c0 17.68 14.32 32 32 32s32-14.32 32-32v-32.24H960zM736 511.888h64c17.664 0 32-14.336 32-32v-64c0-17.664-14.336-32-32-32h-64c-17.664 0-32 14.336-32 32v64c0 17.664 14.336 32 32 32m0 255.984h64c17.664 0 32-14.32 32-32v-64c0-17.664-14.336-32-32-32h-64c-17.664 0-32 14.336-32 32v64c0 17.696 14.336 32 32 32m-192-128h-64c-17.664 0-32 14.336-32 32v64c0 17.68 14.336 32 32 32h64c17.664 0 32-14.32 32-32v-64c0-17.648-14.336-32-32-32m0-255.984h-64c-17.664 0-32 14.336-32 32v64c0 17.664 14.336 32 32 32h64c17.664 0 32-14.336 32-32v-64c0-17.68-14.336-32-32-32m-256 0h-64c-17.664 0-32 14.336-32 32v64c0 17.664 14.336 32 32 32h64c17.664 0 32-14.336 32-32v-64c0-17.68-14.336-32-32-32m0 255.984h-64c-17.664 0-32 14.336-32 32v64c0 17.68 14.336 32 32 32h64c17.664 0 32-14.32 32-32v-64c0-17.648-14.336-32-32-32"/></svg>',
	'comandas'    => '<svg class="tanbiuti-nav__icon" viewBox="0 0 1024 1024" aria-hidden="true"><path d="m21.84 301.808 475.09 258.72a32.1 32.1 0 0 0 15.312 3.904 32 32 0 0 0 15.184-3.84l480.096-258.72c10.464-5.631 16.975-16.624 16.815-28.528a32.09 32.09 0 0 0-17.504-28.16L531.713 3.904c-9.055-4.592-19.744-4.624-28.88-.064L22.785 245.12c-10.624 5.343-17.44 16.16-17.632 28.064s6.256 22.944 16.687 28.624M517.153 68.287l406.159 206.271L512.336 496.03 106.16 274.846zm484.187 412.031-94.974-48.225-68.56 36.976 80 40.624-410.96 221.456-406.191-221.184 85.311-42.88-68.368-37.248-100.32 50.4c-10.624 5.344-17.44 16.16-17.633 28.065s6.256 22.944 16.688 28.624l475.088 258.72a32.1 32.1 0 0 0 15.312 3.903 32 32 0 0 0 15.184-3.84l480.096-258.72c10.464-5.631 16.975-16.624 16.815-28.528a32 32 0 0 0-17.487-28.143zm.01 223.999-89.966-44.224-68.56 36.976 75.008 36.624-410.976 221.456-406.192-221.184 79.312-35.872-68.368-37.248-94.32 43.408C6.662 709.597-.154 720.413-.346 732.318s6.255 22.944 16.687 28.624l475.088 258.72a32.1 32.1 0 0 0 15.313 3.903 32 32 0 0 0 15.183-3.84l480.096-258.72c10.464-5.632 16.976-16.624 16.816-28.528a32 32 0 0 0-17.488-28.16z"/></svg>',
	'comissoes'   => '<svg class="tanbiuti-nav__icon" viewBox="0 0 1024 1024" aria-hidden="true"><path d="M960-.096H64c-35.184 0-64 28.816-64 64v896.192c0 35.184 28.816 64 64 64h896c35.184 0 64-28.816 64-64V63.904c0-35.184-28.816-64-64-64m0 960.193H64V63.905h896zM224 352.305h64v64c0 17.664 14.336 32 32 32s32-14.336 32-32v-64h64c17.664 0 32-14.336 32-32s-14.336-32-32-32h-64v-64c0-17.664-14.336-32-32-32s-32 14.336-32 32v64h-64c-17.664 0-32 14.336-32 32s14.336 32 32 32m209.136 238.847c-12.496-12.496-32.752-12.497-45.248-.001L320 659.023l-67.887-67.872c-12.496-12.496-32.752-12.496-45.264 0-12.496 12.496-12.496 32.769 0 45.265l67.872 67.872-67.872 67.872c-12.496 12.496-12.496 32.768 0 45.264s32.752 12.497 45.264 0L320 749.568l67.888 67.872c12.496 12.496 32.752 12.496 45.248 0s12.496-32.768 0-45.264l-67.872-67.873 67.872-67.872c12.496-12.511 12.496-32.767 0-45.279M608 352.304h192c17.664 0 32-14.336 32-32s-14.336-32-32-32H608c-17.664 0-32 14.336-32 32s14.336 32 32 32m0 320h192c17.664 0 32-14.336 32-32s-14.336-32-32-32H608c-17.664 0-32 14.336-32 32s14.336 32 32 32m0 128h192c17.664 0 32-14.336 32-32s-14.336-32-32-32H608c-17.664 0-32 14.336-32 32s14.336 32 32 32"/></svg>',
	'clientes'    => '<svg class="tanbiuti-nav__icon" viewBox="0 0 1024 1024" aria-hidden="true"><path d="M746 835.28 544.529 723.678c74.88-58.912 95.216-174.688 95.216-239.601v-135.12c0-89.472-118.88-189.12-238.288-189.12-119.376 0-241.408 99.664-241.408 189.12v135.12c0 59.024 24.975 178.433 100.624 239.089L54 835.278S0 859.342 0 889.342v81.088c0 29.84 24.223 54.064 54 54.064h692c29.807 0 54.031-24.224 54.031-54.064v-81.087c0-31.808-54.032-54.064-54.032-54.064zm-9.967 125.215H64.002V903.28c4.592-3.343 11.008-7.216 16.064-9.536 1.503-.688 3.007-1.408 4.431-2.224l206.688-112.096c18.848-10.224 31.344-29.184 33.248-50.528s-7.008-42.256-23.712-55.664c-53.664-43.024-76.656-138.32-76.656-189.152V348.96c0-45.968 86.656-125.12 177.408-125.12 92.432 0 174.288 78.065 174.288 125.12v135.12c0 50.128-15.568 145.84-70.784 189.28a64.1 64.1 0 0 0-24.224 55.664 64.1 64.1 0 0 0 33.12 50.849l201.472 111.6c1.777.975 4.033 2.031 5.905 2.848 4.72 2 10.527 5.343 14.783 8.288v57.887zM969.97 675.936 765.505 564.335c74.88-58.912 98.224-174.688 98.224-239.601v-135.12c0-89.472-121.872-190.128-241.28-190.128-77.6 0-156.943 42.192-203.12 96.225 26.337 1.631 55.377 1.664 80.465 9.664 33.711-26.256 76.368-41.872 122.656-41.872 92.431 0 177.278 79.055 177.278 126.128v135.12c0 50.127-18.56 145.84-73.775 189.28a64.1 64.1 0 0 0-24.224 55.664 64.1 64.1 0 0 0 33.12 50.848l204.465 111.6c1.776.976 4.032 2.032 5.904 2.848 4.72 2 10.527 5.344 14.783 8.288v56.912H830.817c19.504 14.72 25.408 35.776 32.977 64h106.192c29.807 0 54.03-24.224 54.03-54.064V730.03c-.015-31.84-54.047-54.096-54.047-54.096z"/></svg>',
	'vale-rapido' => '<svg class="tanbiuti-nav__icon" viewBox="0 0 1024 1024" aria-hidden="true"><path d="M960-.096H64c-35.184 0-64 28.816-64 64v896.192c0 35.184 28.816 64 64 64h896c35.184 0 64-28.816 64-64V63.904c0-35.184-28.816-64-64-64m0 960.193H64V63.905h896zM224 352.305h64v64c0 17.664 14.336 32 32 32s32-14.336 32-32v-64h64c17.664 0 32-14.336 32-32s-14.336-32-32-32h-64v-64c0-17.664-14.336-32-32-32s-32 14.336-32 32v64h-64c-17.664 0-32 14.336-32 32s14.336 32 32 32m209.136 238.847c-12.496-12.496-32.752-12.497-45.248-.001L320 659.023l-67.887-67.872c-12.496-12.496-32.752-12.496-45.264 0-12.496 12.496-12.496 32.769 0 45.265l67.872 67.872-67.872 67.872c-12.496 12.496-12.496 32.768 0 45.264s32.752 12.497 45.264 0L320 749.568l67.888 67.872c12.496 12.496 32.752 12.496 45.248 0s12.496-32.768 0-45.264l-67.872-67.873 67.872-67.872c12.496-12.511 12.496-32.767 0-45.279M608 352.304h192c17.664 0 32-14.336 32-32s-14.336-32-32-32H608c-17.664 0-32 14.336-32 32s14.336 32 32 32m0 320h192c17.664 0 32-14.336 32-32s-14.336-32-32-32H608c-17.664 0-32 14.336-32 32s14.336 32 32 32m0 128h192c17.664 0 32-14.336 32-32s-14.336-32-32-32H608c-17.664 0-32 14.336-32 32s14.336 32 32 32"/></svg>',
);
$icon_sair = '<svg class="tanbiuti-nav__icon" viewBox="0 0 1024 1024" aria-hidden="true"><path d="M116.832 543.664H671.28c17.696 0 32-14.336 32-32s-14.304-32-32-32H118.832l115.76-115.76c12.496-12.496 12.496-32.752 0-45.248s-32.752-12.496-45.248 0l-189.008 194 189.008 194c6.256 6.256 14.432 9.376 22.624 9.376s16.368-3.12 22.624-9.376c12.496-12.496 12.496-32.752 0-45.248zM959.664 0H415.663c-35.36 0-64 28.656-64 64v288h64.416V103.024c0-21.376 17.344-38.72 38.72-38.72h464.72c21.391 0 38.72 17.344 38.72 38.72l1.007 818.288c0 21.376-17.328 38.72-38.72 38.72h-465.71c-21.376 0-38.72-17.344-38.72-38.72V670.944l-64.416.08V960c0 35.344 28.64 64 64 64h543.984c35.36 0 64.016-28.656 64.016-64V64c-.015-35.344-28.671-64-64.015-64z"/></svg>';
$icon_edit = '<svg class="tanbiuti-nav__edit-icon" viewBox="0 0 24 24" aria-hidden="true"><path d="M14.06 4.94 4 15v5h5L19.06 9.94l-5-5ZM16.5 3.5l4 4L22 6l-4-4-1.5 1.5Z"/></svg>';

$current_section = isset( $GLOBALS['tanbiuti_current_section'] ) ? $GLOBALS['tanbiuti_current_section'] : '';
$labels          = array(
	'agenda'      => __( 'Agenda', 'tanbiuti' ),
	'comandas'    => __( 'Comandas', 'tanbiuti' ),
	'comissoes'   => __( 'Comissões', 'tanbiuti' ),
	'clientes'    => __( 'Clientes', 'tanbiuti' ),
	'vale-rapido' => __( 'Vale Rápido', 'tanbiuti' ),
);
$current_user    = wp_get_current_user();
// Section templates may set this before requiring nav.php to put a page-specific action
// (e.g. Clientes' "+" add button) at the topbar's right edge, matching the real app.
$topbar_action = isset( $GLOBALS['tanbiuti_topbar_action'] ) ? $GLOBALS['tanbiuti_topbar_action'] : '';
?>
<div class="tanbiuti-topbar">
	<button type="button" class="tanbiuti-hamburger" aria-label="<?php esc_attr_e( 'Abrir menu', 'tanbiuti' ); ?>" aria-expanded="false" aria-controls="tanbiuti-nav">
		<span></span><span></span><span></span>
	</button>

	<?php if ( 'agenda' === $current_section ) : ?>
		<span class="tanbiuti-topbar__title tanbiuti-topbar__title--selector">
			<?php echo esc_html( $current_user->display_name ); ?> <span aria-hidden="true">&#8964;</span>
		</span>
	<?php else : ?>
		<span class="tanbiuti-topbar__title">
			<?php echo esc_html( isset( $labels[ $current_section ] ) ? $labels[ $current_section ] : '' ); ?>
		</span>
	<?php endif; ?>

	<span class="tanbiuti-topbar__action">
		<?php echo $topbar_action; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- pre-rendered by the caller, already escaped there. ?>
	</span>
</div>

<div class="tanbiuti-nav-overlay" hidden></div>

<nav class="tanbiuti-nav" id="tanbiuti-nav" aria-label="<?php esc_attr_e( 'Navegação do painel', 'tanbiuti' ); ?>">
	<div class="tanbiuti-nav__user">
		<?php echo get_avatar( $current_user->ID, 72 ); ?>
		<span class="tanbiuti-nav__greeting">
			<?php
			printf(
				/* translators: %s: display name of the logged-in user */
				esc_html__( 'Olá, %s', 'tanbiuti' ),
				esc_html( $current_user->display_name )
			);
			?>
		</span>
		<a class="tanbiuti-nav__edit-profile" href="<?php echo esc_url( admin_url( 'profile.php' ) ); ?>">
			<?php esc_html_e( 'Editar perfil', 'tanbiuti' ); ?>
			<?php echo $icon_edit; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- static, hand-written SVG constant. ?>
		</a>
	</div>

	<ul class="tanbiuti-nav__list">
		<?php foreach ( $labels as $slug => $label ) : ?>
			<li class="tanbiuti-nav__item<?php echo $slug === $current_section ? ' is-active' : ''; ?>">
				<a href="<?php echo esc_url( Tanbiuti_Frontend_Router::url_for( $slug ) ); ?>">
					<?php echo $icons[ $slug ]; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- static, hand-written SVG constants. ?>
					<?php echo esc_html( $label ); ?>
				</a>
			</li>
		<?php endforeach; ?>
		<li class="tanbiuti-nav__item">
			<a href="<?php echo esc_url( wp_logout_url( home_url( '/' ) ) ); ?>">
				<?php echo $icon_sair; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- static, hand-written SVG constant. ?>
				<?php esc_html_e( 'Sair', 'tanbiuti' ); ?>
			</a>
		</li>
	</ul>
</nav>
