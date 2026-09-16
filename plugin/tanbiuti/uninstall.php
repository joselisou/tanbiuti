<?php
/**
 * Uninstall handler. Deliberately does nothing to imported data — uninstalling the plugin
 * removes its code, not the client's imported data. Use the admin "Limpar dados" action (or
 * `wp tanbiuti clean`) beforehand if you actually want that data gone.
 *
 * @package Tanbiuti
 */

if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}
