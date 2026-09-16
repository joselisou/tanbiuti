<?php
/**
 * Opens the standalone HTML document shared by every `/minha-conta/*` template — the real source
 * app is full-screen with no site chrome around it, so these pages don't wrap the active theme's
 * header/footer either.
 *
 * @package Tanbiuti
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

?><!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>" />
	<meta name="viewport" content="width=device-width, initial-scale=1" />
	<title><?php echo esc_html( get_bloginfo( 'name' ) ); ?></title>
	<?php wp_head(); ?>
</head>
<body class="tanbiuti-app">
<?php wp_body_open(); ?>
