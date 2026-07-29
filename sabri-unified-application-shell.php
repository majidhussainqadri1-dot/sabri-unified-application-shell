<?php
/**
 * Plugin Name: Sabri Unified Application Shell
 * Plugin URI: https://github.com/majidhussainqadri1-dot/sabri-unified-application-shell
 * Description: Secure responsive public application shell for the Sabri Social Homeopathy Platform.
 * Version: 1.0.1
 * Author: Dr. Allama Majid Hussain Sabri
 * Text Domain: sabri-unified-application-shell
 * Domain Path: /languages
 * Requires at least: 6.0
 * Requires PHP: 7.4
 *
 * @package SabriUnifiedApplicationShell
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'SABRI_SHELL_VERSION', '1.0.1' );
define( 'SABRI_SHELL_CREATE_CONTRACT_VERSION', '1.0.0' );
define( 'SABRI_SHELL_FILE', __FILE__ );
define( 'SABRI_SHELL_PATH', plugin_dir_path( __FILE__ ) );
define( 'SABRI_SHELL_URL', plugin_dir_url( __FILE__ ) );
define( 'SABRI_SHELL_SLUG', 'sabri-unified-application-shell' );
define( 'SABRI_SHELL_TEXT_DOMAIN', 'sabri-unified-application-shell' );

spl_autoload_register(
	static function ( $class_name ) {
		$prefix = 'Sabri\\UnifiedShell\\';
		if ( 0 !== strpos( $class_name, $prefix ) ) {
			return;
		}

		$relative = substr( $class_name, strlen( $prefix ) );
		$relative = str_replace( '\\', DIRECTORY_SEPARATOR, $relative );
		$parts    = explode( DIRECTORY_SEPARATOR, $relative );
		$base     = array_shift( $parts );
		$file     = 'class-' . strtolower( preg_replace( '/(?<!^)[A-Z]/', '-$0', $base ) ) . '.php';

		if ( ! empty( $parts ) ) {
			$file = implode( DIRECTORY_SEPARATOR, $parts ) . DIRECTORY_SEPARATOR . $file;
		}

		$paths = array(
			SABRI_SHELL_PATH . 'includes' . DIRECTORY_SEPARATOR . $file,
			SABRI_SHELL_PATH . 'admin' . DIRECTORY_SEPARATOR . $file,
		);

		foreach ( $paths as $path ) {
			if ( file_exists( $path ) ) {
				require_once $path;
				return;
			}
		}
	}
);

/** Whether the exact File 22 Create producer contract is loaded and safe. */
if ( ! function_exists( 'sabri_shell_create_contract_available' ) ) {
	function sabri_shell_create_contract_available() {
		return defined( 'SABRI_SHELL_CREATE_CONTRACT_VERSION' )
			&& version_compare( (string) SABRI_SHELL_CREATE_CONTRACT_VERSION, '1.0.0', '>=' )
			&& class_exists( 'Sabri\\UnifiedShell\\CreateVisibility' )
			&& Sabri\UnifiedShell\CreateVisibility::contract_available();
	}
}

/** Whether the current user has a visible, enabled Header Create gateway. */
if ( ! function_exists( 'sabri_shell_create_visible_for_current_user' ) ) {
	function sabri_shell_create_visible_for_current_user() {
		return sabri_shell_create_contract_available()
			&& Sabri\UnifiedShell\CreateVisibility::visible_for_current_user();
	}
}

register_activation_hook( __FILE__, array( 'Sabri\\UnifiedShell\\Plugin', 'activate' ) );
register_deactivation_hook( __FILE__, array( 'Sabri\\UnifiedShell\\Plugin', 'deactivate' ) );

add_action(
	'plugins_loaded',
	static function () {
		load_plugin_textdomain( 'sabri-unified-application-shell', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );
		Sabri\UnifiedShell\Plugin::instance()->register();
	}
);
