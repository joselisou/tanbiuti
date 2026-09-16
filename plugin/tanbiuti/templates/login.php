<?php
/**
 * Login gate shown for `/minha-conta/*` when the visitor isn't authenticated. Layout mirrors the
 * real source app's login (two-column, brand-color panel hidden on mobile) — see the "Design tokens
 * e branding" section of the project plan for the measurements and the branding boundary (own
 * wordmark/illustration, never the source app's actual logo or background artwork).
 *
 * @package Tanbiuti
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

require __DIR__ . '/app-header.php';

$error       = isset( $_GET['tanbiuti_login_error'] ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
$redirect_to = home_url( add_query_arg( array(), $_SERVER['REQUEST_URI'] ?? '/minha-conta/' ) ); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.MissingUnslash, WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
?>
<div class="tanbiuti-login">
	<div class="tanbiuti-login__panel">
		<svg class="tanbiuti-login__waves" viewBox="0 0 600 300" preserveAspectRatio="none" aria-hidden="true">
			<path d="M0,180 C120,140 180,220 300,190 C420,160 480,240 600,200 L600,300 L0,300 Z" fill="rgba(255,255,255,0.12)" />
			<path d="M0,220 C130,260 220,190 340,225 C440,255 520,200 600,235 L600,300 L0,300 Z" fill="rgba(255,255,255,0.18)" />
		</svg>
		<h1><?php esc_html_e( 'Bem-vindo.', 'tanbiuti' ); ?></h1>
		<p><?php esc_html_e( 'O seu painel de gestão, espelhado num WordPress só seu — pronto para virar dados e decisões.', 'tanbiuti' ); ?></p>
	</div>

	<div class="tanbiuti-login__form-panel">
		<p class="tanbiuti-login__wordmark"><?php esc_html_e( 'Tanbiuti', 'tanbiuti' ); ?></p>

		<h2><?php esc_html_e( 'Entrar', 'tanbiuti' ); ?></h2>
		<p class="tanbiuti-login__hint"><?php esc_html_e( 'Digite seu e-mail e senha do WordPress para acessar o painel.', 'tanbiuti' ); ?></p>

		<?php if ( $error ) : ?>
			<p class="tanbiuti-login__error"><?php esc_html_e( 'E-mail ou senha inválidos.', 'tanbiuti' ); ?></p>
		<?php endif; ?>

		<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php?action=tanbiuti_login' ) ); ?>">
			<?php wp_nonce_field( 'tanbiuti_login', 'tanbiuti_login_nonce' ); ?>
			<input type="hidden" name="redirect_to" value="<?php echo esc_url( $redirect_to ); ?>" />

			<label for="tanbiuti-log"><?php esc_html_e( 'E-mail', 'tanbiuti' ); ?></label>
			<input type="text" name="log" id="tanbiuti-log" placeholder="<?php esc_attr_e( 'Digite aqui...', 'tanbiuti' ); ?>" required />

			<label for="tanbiuti-pwd"><?php esc_html_e( 'Senha', 'tanbiuti' ); ?></label>
			<div class="tanbiuti-password-field">
				<input type="password" name="pwd" id="tanbiuti-pwd" placeholder="<?php esc_attr_e( 'Digite sua senha...', 'tanbiuti' ); ?>" required />
				<button type="button" class="tanbiuti-password-field__toggle" data-tanbiuti-password-toggle="tanbiuti-pwd" aria-label="<?php esc_attr_e( 'Mostrar senha', 'tanbiuti' ); ?>">
					<svg class="tanbiuti-eye-icon" viewBox="0 0 1024 1024" aria-hidden="true">
						<path d="M515.472 321.408c-106.032 0-192 85.968-192 192 0 106.016 85.968 192 192 192s192-85.968 192-192-85.968-192-192-192zm0 320c-70.576 0-129.473-58.816-129.473-129.393s57.424-128 128-128c70.592 0 128 57.424 128 128s-55.935 129.393-126.527 129.393zm508.208-136.832c-.368-1.616-.207-3.325-.688-4.91-.208-.671-.624-1.055-.864-1.647-.336-.912-.256-1.984-.72-2.864-93.072-213.104-293.663-335.76-507.423-335.76S95.617 281.827 2.497 494.947c-.4.897-.336 1.824-.657 2.849-.223.624-.687.975-.895 1.567-.496 1.616-.304 3.296-.608 4.928-.591 2.88-1.135 5.68-1.135 8.592 0 2.944.544 5.664 1.135 8.591.32 1.6.113 3.344.609 4.88.208.72.672 1.024.895 1.68.336.88.256 1.968.656 2.848 93.136 213.056 295.744 333.712 509.504 333.712 213.776 0 416.336-120.4 509.44-333.505.464-.912.369-1.872.72-2.88.224-.56.655-.976.848-1.6.496-1.568.336-3.28.687-4.912.56-2.864 1.088-5.664 1.088-8.624 0-2.816-.528-5.6-1.104-8.497zM512 800.595c-181.296 0-359.743-95.568-447.423-287.681 86.848-191.472 267.68-289.504 449.424-289.504 181.68 0 358.496 98.144 445.376 289.712C872.561 704.53 693.744 800.595 512 800.595z"/>
						<line class="tanbiuti-eye-icon__slash" x1="120" y1="120" x2="904" y2="904" />
					</svg>
				</button>
			</div>

			<a class="tanbiuti-login__forgot" href="<?php echo esc_url( wp_lostpassword_url( $redirect_to ) ); ?>"><?php esc_html_e( 'Esqueci minha senha', 'tanbiuti' ); ?></a>

			<button type="submit" class="tanbiuti-btn tanbiuti-btn--primary"><?php esc_html_e( 'Acessar conta', 'tanbiuti' ); ?></button>
			<a class="tanbiuti-btn tanbiuti-btn--secondary" href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php esc_html_e( 'Voltar para o site', 'tanbiuti' ); ?></a>
		</form>
	</div>
</div>
<?php
require __DIR__ . '/app-footer.php';
