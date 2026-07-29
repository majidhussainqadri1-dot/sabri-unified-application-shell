<?php
/** Focused File 20 v1.0.1 Create producer contracts. */

declare(strict_types=1);

namespace {
	define( 'ABSPATH', __DIR__ . '/' );
	define( 'SABRI_SHELL_CREATE_CONTRACT_VERSION', '1.0.1' );
	define( 'SABRI_SHELL_CREATE_CONTRACT_OWNER', 'sabri-unified-application-shell' );
	define( 'SABRI_SHELL_CREATE_FUNCTIONS_OWNED', true );
	$GLOBALS['f20_admin']        = false;
	$GLOBALS['f20_logged_in']    = false;
	$GLOBALS['f20_caps']         = array();
	$GLOBALS['f20_user']         = (object) array( 'ID' => 1, 'roles' => array() );
	$GLOBALS['f20_settings']     = array();
	$GLOBALS['f20_safe']         = false;
	$GLOBALS['f20_safe_reads']   = false;
	$GLOBALS['f20_filter']       = null;
	$GLOBALS['f20_url_filter']   = null;
	$GLOBALS['f20_filter_calls'] = 0;
	$GLOBALS['f20_url_calls']    = 0;
	$GLOBALS['f20_hooks']        = array();

	function __( $text, $domain = '' ) { unset( $domain ); return $text; }
	function sanitize_key( $key ) { return preg_replace( '/[^a-z0-9_\-]/', '', strtolower( (string) $key ) ); }
	function is_admin() { return (bool) $GLOBALS['f20_admin']; }
	function is_user_logged_in() { return (bool) $GLOBALS['f20_logged_in']; }
	function current_user_can( $capability ) { return in_array( $capability, $GLOBALS['f20_caps'], true ); }
	function wp_get_current_user() { return $GLOBALS['f20_user']; }
	function admin_url( $path = '' ) { return 'https://example.test/wp-admin/' . ltrim( (string) $path, '/' ); }
	function home_url( $path = '' ) { return 'https://example.test/' . ltrim( (string) $path, '/' ); }
	function wp_parse_url( $url ) { return parse_url( (string) $url ); }
	function wp_validate_redirect( $url, $fallback = '' ) { return filter_var( $url, FILTER_VALIDATE_URL ) ? $url : $fallback; }
	function esc_url_raw( $url, $protocols = null ) { unset( $protocols ); return (string) $url; }
	function add_filter( $hook, $callback, $priority = 10, $accepted_args = 1 ) {
		$GLOBALS['f20_hooks'][] = array( $hook, $callback, $priority, $accepted_args );
		return true;
	}
	function apply_filters( $hook, $value ) {
		$args = func_get_args();
		if ( 'sabri_shell_can_show_create' === $hook ) {
			$GLOBALS['f20_filter_calls']++;
			return is_callable( $GLOBALS['f20_filter'] ) ? call_user_func_array( $GLOBALS['f20_filter'], array_slice( $args, 1 ) ) : $value;
		}
		if ( 'sabri_shell_create_url' === $hook ) {
			$GLOBALS['f20_url_calls']++;
			return is_callable( $GLOBALS['f20_url_filter'] ) ? call_user_func( $GLOBALS['f20_url_filter'], $value ) : $value;
		}
		return $value;
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
		$GLOBALS['f20_url_filter']   = static function ( $url ) { return $url; };
		$GLOBALS['f20_filter_calls'] = 0;
		$GLOBALS['f20_url_calls']    = 0;
		$GLOBALS['f20_hooks']        = array();
	};

	$reset();
	CreateVisibility::register();
	$assert( 2 === count( $GLOBALS['f20_hooks'] ) && 'option_sabri_shell_settings' === $GLOBALS['f20_hooks'][0][0] && 'sabri_shell_create_url' === $GLOBALS['f20_hooks'][1][0], 'Public filter registration failed.' );
	$GLOBALS['f20_admin'] = true;
	CreateVisibility::register();
	$assert( 2 === count( $GLOBALS['f20_hooks'] ), 'User-specific filters registered in wp-admin.' );

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
	$GLOBALS['f20_filter']    = static function () { return true; };
	foreach ( array( 'enabled', 'create' ) as $switch ) {
		$header = array( 'enabled' => true, 'create' => true );
		$header[ $switch ] = false;
		$GLOBALS['f20_filter_calls'] = 0;
		$result = CreateVisibility::filter_runtime_settings( array( 'header' => $header, 'mobile' => array( 'create_or_doctors' => 'create' ) ) );
		$assert( 'doctors' === $result['mobile']['create_or_doctors'], 'Disabled Header/Create retained explicit mobile Create: ' . $switch );
		$assert( 0 === $GLOBALS['f20_filter_calls'], 'File 22 filter ran after disabled Header/Create: ' . $switch );
	}

	$reset();
	$GLOBALS['f20_logged_in'] = true;
	$GLOBALS['f20_caps']      = array( 'edit_posts' );
	$GLOBALS['f20_user']      = (object) array( 'ID' => 22, 'roles' => array( 'sabri_verified_doctor' ) );
	$GLOBALS['f20_filter']    = static function ( $allowed, $user_id ) { unset( $allowed ); return 22 === $user_id; };
	$result = CreateVisibility::filter_runtime_settings( array( 'mobile' => array( 'create_or_doctors' => 'create' ) ) );
	$assert( in_array( 'sabri_verified_doctor', $result['header']['allowed_roles'], true ), 'Central authorization did not bridge legacy role list.' );
	$assert( 'create' === $result['mobile']['create_or_doctors'], 'Authorized explicit Create preference was not preserved.' );
	$GLOBALS['f20_settings'] = array();
	$assert( true === CreateVisibility::visible_for_current_user(), 'Visible producer rejected centrally authorized doctor.' );
	$assert( 'https://example.test/wp-admin/post-new.php' === CreateVisibility::create_url(), 'Authorized same-origin HTTPS fallback URL was rejected.' );

	$GLOBALS['f20_url_filter'] = static function () { return 'https://evil.example/create/'; };
	$assert( 'https://example.test/wp-admin/post-new.php' === CreateVisibility::create_url(), 'Cross-origin Create URL was not replaced by the safe fallback.' );
	$GLOBALS['f20_url_filter'] = static function () { return 'http://example.test/create/'; };
	$assert( 'https://example.test/wp-admin/post-new.php' === CreateVisibility::create_url(), 'HTTP downgrade Create URL was not replaced by the safe fallback.' );
	$GLOBALS['f20_url_filter'] = static function () { return 'https://user:pass@example.test/create/'; };
	$assert( 'https://example.test/wp-admin/post-new.php' === CreateVisibility::create_url(), 'Credential-bearing Create URL was not replaced by the safe fallback.' );

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
	$assert( false !== strpos( $bootstrap, 'Version: 1.0.1' ) && false !== strpos( $bootstrap, "SABRI_SHELL_CREATE_CONTRACT_VERSION', '1.0.1" ), 'Versioned runtime contract is incomplete.' );
	$assert( false !== strpos( $bootstrap, 'SABRI_SHELL_CREATE_CONTRACT_OWNER' ) && false !== strpos( $bootstrap, 'SABRI_SHELL_CREATE_FUNCTIONS_OWNED' ), 'Contract ownership proof is missing.' );
	$assert( strpos( $plugin, 'CreateVisibility::register();' ) < strpos( $plugin, 'Settings::register();' ), 'Create bridge is registered too late.' );
	$assert( false === strpos( $helper, 'update_option(' ) && false === strpos( $helper, 'delete_option(' ), 'Create bridge contains a database-write path.' );
	$assert( false !== strpos( $helper, "empty( $settings['header']['enabled'] )" ) && false !== strpos( $helper, "empty( $settings['header']['create'] )" ), 'Header/Create fail-closed parity is missing.' );
	$assert( false !== strpos( $helper, 'same_origin_https_url' ), 'Same-origin HTTPS Create URL validation is missing.' );

	if ( $failures ) {
		fwrite( STDERR, implode( PHP_EOL, $failures ) . PHP_EOL );
		exit( 1 );
	}
	echo "File 20 Create producer contracts passed.\n";
}
