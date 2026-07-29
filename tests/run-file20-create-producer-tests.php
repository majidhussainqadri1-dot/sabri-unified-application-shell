<?php
/** Focused File 20 v1.0.1 Create producer contracts. */

declare(strict_types=1);

namespace {
	define( 'ABSPATH', __DIR__ . '/' );

	$GLOBALS['f20_admin']       = false;
	$GLOBALS['f20_logged_in']   = false;
	$GLOBALS['f20_caps']        = array();
	$GLOBALS['f20_user']        = (object) array( 'ID' => 1, 'roles' => array() );
	$GLOBALS['f20_settings']    = array();
	$GLOBALS['f20_safe']        = false;
	$GLOBALS['f20_safe_reads']  = false;
	$GLOBALS['f20_filter']      = null;
	$GLOBALS['f20_filter_calls']= 0;
	$GLOBALS['f20_hooks']       = array();

	function __( $text, $domain = '' ) { unset( $domain ); return $text; }
	function sanitize_key( $key ) { return preg_replace( '/[^a-z0-9_\-]/', '', strtolower( (string) $key ) ); }
	function is_admin() { return (bool) $GLOBALS['f20_admin']; }
	function is_user_logged_in() { return (bool) $GLOBALS['f20_logged_in']; }
	function current_user_can( $capability ) { return in_array( $capability, $GLOBALS['f20_caps'], true ); }
	function wp_get_current_user() { return $GLOBALS['f20_user']; }
	function add_filter( $hook, $callback, $priority = 10, $accepted_args = 1 ) {
		$GLOBALS['f20_hooks'][] = array( $hook, $callback, $priority, $accepted_args );
		return true;
	}
	function apply_filters( $hook, $value ) {
		$args = func_get_args();
		if ( 'sabri_shell_can_show_create' !== $hook ) { return $value; }
		$GLOBALS['f20_filter_calls']++;
		return is_callable( $GLOBALS['f20_filter'] ) ? call_user_func_array( $GLOBALS['f20_filter'], array_slice( $args, 1 ) ) : $value;
	}
}

namespace Sabri\UnifiedShell {
	final class Defaults {
		const OPTION_NAME = 'sabri_shell_settings';
		public static function settings() {
			return array(
				'emergency_disabled' => false,
				'header' => array( 'enabled' => true, 'create' => true, 'allowed_roles' => array( 'administrator', 'editor' ) ),
				'mobile' => array( 'create_or_doctors' => 'auto' ),
			);
		}
	}
	final class Settings {
		public static function get() {
			$raw = CreateVisibility::filter_runtime_settings( $GLOBALS['f20_settings'], Defaults::OPTION_NAME );
			return array_replace_recursive( Defaults::settings(), is_array( $raw ) ? $raw : array() );
		}
	}
	final class SafeMode {
		public static function disabled() {
			if ( $GLOBALS['f20_safe_reads'] ) {
				$settings = Settings::get();
				return ! empty( $settings['emergency_disabled'] );
			}
			return (bool) $GLOBALS['f20_safe'];
		}
	}

	require_once dirname( __DIR__ ) . '/includes/class-create-visibility.php';

	$failures = array();
	$assert = static function ( $condition, $message ) use ( &$failures ) {
		if ( ! $condition ) { $failures[] = $message; }
	};
	$reset = static function () {
		$GLOBALS['f20_admin']        = false;
		$GLOBALS['f20_logged_in']    = false;
		$GLOBALS['f20_caps']         = array();
		$GLOBALS['f20_user']         = (object) array( 'ID' => 1, 'roles' => array() );
		$GLOBALS['f20_settings']     = array();
		$GLOBALS['f20_safe']         = false;
		$GLOBALS['f20_safe_reads']   = false;
		$GLOBALS['f20_filter']       = static function ( $allowed ) { return $allowed; };
		$GLOBALS['f20_filter_calls'] = 0;
		$GLOBALS['f20_hooks']        = array();
	};

	$reset();
	CreateVisibility::register();
	$assert( 1 === count( $GLOBALS['f20_hooks'] ) && 'option_sabri_shell_settings' === $GLOBALS['f20_hooks'][0][0], 'Public request filter registration failed.' );
	$GLOBALS['f20_admin'] = true;
	CreateVisibility::register();
	$assert( 1 === count( $GLOBALS['f20_hooks'] ), 'User-specific option filter registered in wp-admin.' );

	$reset();
	$GLOBALS['f20_user']   = (object) array( 'ID' => 10, 'roles' => array( 'administrator' ) );
	$GLOBALS['f20_filter'] = static function () { return true; };
	$result = CreateVisibility::filter_runtime_settings( array( 'mobile' => array( 'create_or_doctors' => 'create' ) ) );
	$assert( 'doctors' === $result['mobile']['create_or_doctors'], 'Logged-out explicit mobile Create bypass remains.' );
	$assert( 0 === $GLOBALS['f20_filter_calls'], 'Extension filter ran before login safeguard.' );

	$reset();
	$GLOBALS['f20_logged_in'] = true;
	$GLOBALS['f20_safe']      = true;
	$GLOBALS['f20_caps']      = array( 'edit_posts' );
	$GLOBALS['f20_user']      = (object) array( 'ID' => 10, 'roles' => array( 'administrator' ) );
	$GLOBALS['f20_filter']    = static function () { return true; };
	$result = CreateVisibility::filter_runtime_settings( array( 'mobile' => array( 'create_or_doctors' => 'create' ) ) );
	$assert( 'doctors' === $result['mobile']['create_or_doctors'], 'Safe Mode explicit mobile Create bypass remains.' );
	$assert( ! in_array( 'administrator', $result['header']['allowed_roles'], true ), 'Safe Mode role remained eligible.' );
	$assert( 0 === $GLOBALS['f20_filter_calls'], 'Extension filter ran before Safe Mode safeguard.' );

	$reset();
	$GLOBALS['f20_logged_in'] = true;
	$GLOBALS['f20_caps']      = array( 'edit_posts' );
	$GLOBALS['f20_user']      = (object) array( 'ID' => 10, 'roles' => array( 'administrator' ) );
	$result = CreateVisibility::filter_runtime_settings( array() );
	$assert( 'auto' === $result['mobile']['create_or_doctors'], 'Legacy administrator fallback was not preserved.' );
	$GLOBALS['f20_settings'] = $result;
	$assert( true === CreateVisibility::visible_for_current_user(), 'Visible producer rejected legacy administrator.' );

	$reset();
	$GLOBALS['f20_logged_in'] = true;
	$GLOBALS['f20_caps']      = array( 'edit_posts' );
	$GLOBALS['f20_user']      = (object) array( 'ID' => 22, 'roles' => array( 'sabri_verified_doctor' ) );
	$GLOBALS['f20_filter']    = static function ( $allowed, $user_id ) { unset( $allowed ); return 22 === $user_id; };
	$result = CreateVisibility::filter_runtime_settings( array( 'mobile' => array( 'create_or_doctors' => 'create' ) ) );
	$assert( in_array( 'sabri_verified_doctor', $result['header']['allowed_roles'], true ), 'Central authorization did not bridge the legacy role list.' );
	$assert( 'auto' === $result['mobile']['create_or_doctors'], 'Authorized doctor mobile Create is not using shared authorization.' );
	$GLOBALS['f20_settings'] = array();
	$assert( true === CreateVisibility::visible_for_current_user(), 'Visible producer rejected centrally authorized doctor.' );

	$reset();
	$GLOBALS['f20_logged_in'] = true;
	$GLOBALS['f20_caps']      = array( 'edit_posts' );
	$GLOBALS['f20_user']      = (object) array( 'ID' => 11, 'roles' => array( 'administrator' ) );
	$GLOBALS['f20_filter']    = static function () { return false; };
	$result = CreateVisibility::filter_runtime_settings( array( 'mobile' => array( 'create_or_doctors' => 'create' ) ) );
	$assert( ! in_array( 'administrator', $result['header']['allowed_roles'], true ), 'Integration denial did not narrow legacy authorization.' );
	$assert( 'doctors' === $result['mobile']['create_or_doctors'], 'Denied explicit mobile Create was not neutralized.' );

	$reset();
	$GLOBALS['f20_logged_in'] = true;
	$GLOBALS['f20_caps']      = array( 'edit_posts' );
	$GLOBALS['f20_user']      = (object) array( 'ID' => 10, 'roles' => array( 'administrator' ) );
	$GLOBALS['f20_settings']  = array( 'header' => array( 'create' => false ) );
	$assert( false === CreateVisibility::visible_for_current_user(), 'Disabled Header Create was reported visible.' );

	$reset();
	$GLOBALS['f20_logged_in']  = true;
	$GLOBALS['f20_caps']       = array( 'edit_posts' );
	$GLOBALS['f20_user']       = (object) array( 'ID' => 10, 'roles' => array( 'administrator' ) );
	$GLOBALS['f20_safe_reads'] = true;
	$GLOBALS['f20_settings']   = array( 'emergency_disabled' => true );
	$result = CreateVisibility::filter_runtime_settings( $GLOBALS['f20_settings'] );
	$assert( 'doctors' === $result['mobile']['create_or_doctors'], 'Recursive Emergency Disable check did not fail closed.' );
	$assert( false === CreateVisibility::contract_available(), 'Emergency Disable did not close producer contract.' );

	$malformed = CreateVisibility::filter_runtime_settings( 'invalid' );
	$assert( is_array( $malformed ) && isset( $malformed['header']['allowed_roles'], $malformed['mobile']['create_or_doctors'] ), 'Malformed option was not normalized safely.' );

	$helper    = (string) file_get_contents( dirname( __DIR__ ) . '/includes/class-create-visibility.php' );
	$plugin    = (string) file_get_contents( dirname( __DIR__ ) . '/includes/class-plugin.php' );
	$bootstrap = (string) file_get_contents( dirname( __DIR__ ) . '/sabri-unified-application-shell.php' );
	$assert( false !== strpos( $bootstrap, 'Version: 1.0.1' ) && false !== strpos( $bootstrap, "SABRI_SHELL_CREATE_CONTRACT_VERSION', '1.0.0" ), 'Versioned runtime contract is incomplete.' );
	$assert( false !== strpos( $bootstrap, 'sabri_shell_create_contract_available' ) && false !== strpos( $bootstrap, 'sabri_shell_create_visible_for_current_user' ), 'Read-only producer functions are missing.' );
	$assert( strpos( $plugin, 'CreateVisibility::register();' ) < strpos( $plugin, 'Settings::register();' ), 'Create bridge is registered too late.' );
	$assert( false === strpos( $helper, 'update_option(' ) && false === strpos( $helper, 'delete_option(' ), 'Create bridge contains a database-write path.' );
	$assert( false !== strpos( $helper, "['create_or_doctors'] = 'doctors'" ) && false !== strpos( $helper, "['create_or_doctors'] = 'auto'" ), 'Mobile bypass neutralization is missing.' );

	if ( $failures ) {
		fwrite( STDERR, implode( PHP_EOL, $failures ) . PHP_EOL );
		exit( 1 );
	}
	echo "File 20 Create producer contracts passed.\n";
}
