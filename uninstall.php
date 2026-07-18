<?php
/**
 * Uninstall handler.
 *
 * @package SabriUnifiedApplicationShell
 */

if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

$settings = get_option( 'sabri_shell_settings', array() );

if ( is_array( $settings ) && ! empty( $settings['delete_on_uninstall'] ) ) {
	delete_option( 'sabri_shell_settings' );
	delete_option( 'sabri_shell_activation_snapshot' );
	delete_option( 'sabri_shell_flush_rewrite_rules' );
	delete_transient( 'sabri_shell_navigation_cache_v1' );
	delete_transient( 'sabri_shell_integration_cache' );
}
