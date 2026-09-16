<?php
/**
 * Admin screen to run/clean the WXR import — the top-level "Tanbiuti" menu the CPTs live under.
 *
 * @package Tanbiuti
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Tanbiuti_Admin_Page.
 */
class Tanbiuti_Admin_Page {

	const CAPABILITY   = 'manage_options';
	const NONCE_ACTION = 'tanbiuti_admin_action';

	/**
	 * Hooks the admin menu and form handling into WordPress.
	 */
	public static function init() {
		add_action( 'admin_menu', array( __CLASS__, 'register_menu' ) );
		add_action( 'admin_post_tanbiuti_import', array( __CLASS__, 'handle_import' ) );
		add_action( 'admin_post_tanbiuti_clean', array( __CLASS__, 'handle_clean' ) );
	}

	/**
	 * Registers the top-level "Tanbiuti" menu page that the CPTs attach their own submenus to.
	 */
	public static function register_menu() {
		add_menu_page(
			__( 'Tanbiuti', 'tanbiuti' ),
			__( 'Tanbiuti', 'tanbiuti' ),
			self::CAPABILITY,
			'tanbiuti',
			array( __CLASS__, 'render_page' ),
			'dashicons-store',
			25
		);
	}

	/**
	 * Renders the Import/Clean screen.
	 */
	public static function render_page() {
		if ( ! current_user_can( self::CAPABILITY ) ) {
			return;
		}

		$notice = isset( $_GET['tanbiuti_notice'] ) ? sanitize_text_field( wp_unslash( $_GET['tanbiuti_notice'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		?>
		<div class="wrap">
			<h1><?php esc_html_e( 'Tanbiuti', 'tanbiuti' ); ?></h1>

			<?php if ( $notice ) : ?>
				<div class="notice notice-success"><p><?php echo esc_html( $notice ); ?></p></div>
			<?php endif; ?>

			<h2><?php esc_html_e( 'Importar WXR', 'tanbiuti' ); ?></h2>
			<form method="post" enctype="multipart/form-data" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
				<?php wp_nonce_field( self::NONCE_ACTION ); ?>
				<input type="hidden" name="action" value="tanbiuti_import" />
				<input type="file" name="wxr_file" accept=".xml" required />
				<?php submit_button( __( 'Importar', 'tanbiuti' ) ); ?>
			</form>

			<h2><?php esc_html_e( 'Limpar dados importados', 'tanbiuti' ); ?></h2>
			<p><?php esc_html_e( 'Remove todos os posts criados por uma importação anterior (identificados por _tanbiuti_source_id), sem afetar nenhum outro conteúdo do site.', 'tanbiuti' ); ?></p>
			<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" onsubmit="return confirm('<?php echo esc_js( __( 'Tem certeza? Isso vai apagar todos os dados importados.', 'tanbiuti' ) ); ?>');">
				<?php wp_nonce_field( self::NONCE_ACTION ); ?>
				<input type="hidden" name="action" value="tanbiuti_clean" />
				<?php submit_button( __( 'Limpar dados', 'tanbiuti' ), 'delete' ); ?>
			</form>
		</div>
		<?php
	}

	/**
	 * Handles the "Importar" form submission.
	 */
	public static function handle_import() {
		check_admin_referer( self::NONCE_ACTION );

		if ( ! current_user_can( self::CAPABILITY ) ) {
			wp_die( esc_html__( 'Você não tem permissão para fazer isso.', 'tanbiuti' ) );
		}

		if ( empty( $_FILES['wxr_file']['tmp_name'] ) ) {
			self::redirect_with_notice( __( 'Nenhum arquivo enviado.', 'tanbiuti' ) );
		}

		// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.MissingUnslash
		$tmp_path = $_FILES['wxr_file']['tmp_name'];

		try {
			$result = Tanbiuti_Importer::import_file( $tmp_path );
		} catch ( InvalidArgumentException $e ) {
			self::redirect_with_notice( $e->getMessage() );
		}

		self::redirect_with_notice(
			sprintf(
				/* translators: 1: created count, 2: updated count */
				__( 'Importação concluída: %1$d posts criados, %2$d atualizados.', 'tanbiuti' ),
				$result['created'],
				$result['updated']
			)
		);
	}

	/**
	 * Handles the "Limpar dados" form submission.
	 */
	public static function handle_clean() {
		check_admin_referer( self::NONCE_ACTION );

		if ( ! current_user_can( self::CAPABILITY ) ) {
			wp_die( esc_html__( 'Você não tem permissão para fazer isso.', 'tanbiuti' ) );
		}

		$deleted = Tanbiuti_Cleaner::clean();

		self::redirect_with_notice(
			sprintf(
				/* translators: %d: number of deleted posts */
				__( '%d posts removidos.', 'tanbiuti' ),
				$deleted
			)
		);
	}

	/**
	 * Redirects back to the admin page with a plain-text notice, then exits.
	 *
	 * @param string $message Notice text.
	 */
	private static function redirect_with_notice( $message ) {
		wp_safe_redirect( add_query_arg( 'tanbiuti_notice', rawurlencode( $message ), admin_url( 'admin.php?page=tanbiuti' ) ) );
		exit;
	}
}
