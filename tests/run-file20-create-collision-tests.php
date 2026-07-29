<?php
/** Fail-closed File 20 Create producer ownership-collision regression. */

declare(strict_types=1);

namespace {
	define( 'ABSPATH', __DIR__ . '/' );
	define( 'SABRI_SHELL_CREATE_CONTRACT_VERSION', '1.0.1' );
	define( 'SABRI_SHELL_CREATE_CONTRACT_OWNER', 'foreign-producer' );
	define( 'SABRI_SHELL_CREATE_FUNCTIONS_OWNED', true );

	function sabri_shell_create_contract_available() { return true; }
	function plugin_dir_path( $file ) { return dirname( (string) $file ) . '/'; }
	function plugin_dir_url( $file ) { unset( $file ); return 'https://example.test/wp-content/plugins/sabri-unified-application-shell/'; }
	function register_activation_hook( $file, $callback ) { unset( $file, $callback ); }
	function register_deactivation_hook( $file, $callback ) { unset( $file, $callback ); }
	function add_action( $hook, $callback, $priority = 10, $accepted_args = 1 ) { unset( $hook, $callback, $priority, $accepted_args ); return true; }
	function load_plugin_textdomain( $domain, $deprecated = false, $path = false ) { unset( $domain, $deprecated, $path ); return true; }
	function plugin_basename( $file ) { return basename( (string) $file ); }

	require dirname( __DIR__ ) . '/sabri-unified-application-shell.php';

	$failures = array();
	if ( function_exists( 'sabri_shell_create_visible_for_current_user' ) ) {
		$failures[] = 'File 20 defined a partial producer beside a foreign contract.';
	}
	if ( 'foreign-producer' !== SABRI_SHELL_CREATE_CONTRACT_OWNER ) {
		$failures[] = 'File 20 overwrote the foreign contract owner marker.';
	}
	if ( ! SABRI_SHELL_CREATE_FUNCTIONS_OWNED ) {
		$failures[] = 'Test fixture ownership marker was unexpectedly changed.';
	}
	if ( ! function_exists( 'sabri_shell_create_contract_available' ) || ! sabri_shell_create_contract_available() ) {
		$failures[] = 'Foreign producer fixture was unexpectedly replaced.';
	}

	if ( $failures ) {
		fwrite( STDERR, implode( PHP_EOL, $failures ) . PHP_EOL );
		exit( 1 );
	}

	echo "File 20 Create ownership collision failed closed.\n";
}
