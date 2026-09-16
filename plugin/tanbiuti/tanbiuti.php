<?php
/**
 * Plugin Name:       Tanbiuti
 * Description:       Clona os dados e a UI funcional de um painel de gestão de salão (agenda, comandas, comissões, clientes) via import WXR em Custom Post Types.
 * Version:           0.1.0
 * Requires at least: 6.4
 * Requires PHP:      7.4
 * Author:            Tanbiuti
 * License:           GPL v2 or later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       tanbiuti
 *
 * @package Tanbiuti
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

define( 'TANBIUTI_VERSION', '0.1.0' );
define( 'TANBIUTI_FILE', __FILE__ );
define( 'TANBIUTI_DIR', plugin_dir_path( __FILE__ ) );
define( 'TANBIUTI_URL', plugin_dir_url( __FILE__ ) );

require_once TANBIUTI_DIR . 'includes/class-tanbiuti-cpt-registrar.php';
require_once TANBIUTI_DIR . 'includes/class-tanbiuti-taxonomy-registrar.php';
require_once TANBIUTI_DIR . 'includes/class-tanbiuti-importer.php';
require_once TANBIUTI_DIR . 'includes/class-tanbiuti-cleaner.php';
require_once TANBIUTI_DIR . 'includes/class-tanbiuti-admin-page.php';
require_once TANBIUTI_DIR . 'includes/class-tanbiuti-cli-commands.php';
require_once TANBIUTI_DIR . 'includes/class-tanbiuti-auth-gate.php';
require_once TANBIUTI_DIR . 'includes/class-tanbiuti-frontend-router.php';
require_once TANBIUTI_DIR . 'includes/class-tanbiuti-assets.php';
require_once TANBIUTI_DIR . 'includes/class-tanbiuti-formatting.php';

/**
 * Boots every plugin subsystem. Kept as plain function calls (no container/DI) since the plugin
 * is small and each class only needs `add_action`/`add_filter` wiring, not shared state.
 */
function tanbiuti_bootstrap() {
	Tanbiuti_CPT_Registrar::init();
	Tanbiuti_Taxonomy_Registrar::init();
	Tanbiuti_Importer::init();
	Tanbiuti_Cleaner::init();
	Tanbiuti_Admin_Page::init();
	Tanbiuti_Auth_Gate::init();
	Tanbiuti_Frontend_Router::init();
	Tanbiuti_Assets::init();

	if ( defined( 'WP_CLI' ) && WP_CLI ) {
		Tanbiuti_CLI_Commands::init();
	}
}
add_action( 'plugins_loaded', 'tanbiuti_bootstrap' );

/**
 * Registers CPTs/taxonomies and flushes rewrite rules so their archives/endpoints work immediately.
 */
function tanbiuti_activate() {
	Tanbiuti_CPT_Registrar::register();
	Tanbiuti_Taxonomy_Registrar::register();
	Tanbiuti_Frontend_Router::register_endpoints();
	flush_rewrite_rules();
}
register_activation_hook( __FILE__, 'tanbiuti_activate' );

/**
 * Deactivation only flushes rewrite rules — imported data is never deleted implicitly.
 * Use the admin "Limpar dados" action (or `wp tanbiuti clean`) to remove it explicitly.
 */
function tanbiuti_deactivate() {
	flush_rewrite_rules();
}
register_deactivation_hook( __FILE__, 'tanbiuti_deactivate' );
