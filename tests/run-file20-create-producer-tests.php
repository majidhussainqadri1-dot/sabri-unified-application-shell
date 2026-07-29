<?php
/**
 * Focused runtime and source contracts for File 20 v1.0.1 Create producer.
 *
 * @package SabriUnifiedApplicationShell
 */

declare(strict_types=1);

namespace {
	define( 'ABSPATH', __DIR__ . '/' );

	$GLOBALS['file20_admin']               = false;
	$GLOBALS['file20_logged_in']           = false;
	$GLOBALS['file20_caps']                = array();
	$GLOBALS['file20_user']                = (object) array( 'ID' => 1, 'roles' => array() );
	$GLOBALS['file20_stored_settings']     = array();
	$GLOBALS['file20_safe_mode']           = false;
	$GLOBALS['file20_safe_reads_settings'] = false;
	$GLOBALS['file20_filter_callback']     = null;
	$GLOBALS['file20_filter_calls']        = 0;
	$GLOBALS['file20_registered_filters']  = array();
	$GLOBALS['file20_option_writes']       = 0;

	function __( $text, $domain = '' ) {
		unset( $domain );
		return $text;
	}

	function sanitize_key( $key ) {
		return preg_replace( '/[^a-z0-9_\-]/', '', strtolower( (string) $key ) );
	}

	function is_admin() {
		return (bool) $GLOBALS['file20_admin'];
	}

	function add_filter( $hook, $callback, $priority = 10, $accepted_args = 1 ) {
		$GLOBALS['file20_registered_filters'][] = array( $hook, $callback, $priority, $accepted_args );
		return true;
	}

	function is_user_logged_in() {
		return (bool) $GLOBALS['file20_logged_in'];
	}

	function current_user_can( $capability ) {
		return in_array( $capability, $GLOBALS['file20_caps'], true );
	}

	function wp_get_current_user() {
		return $GLOBALS['file20_user'];
	}

	function apply_filters( $hook, $value ) {
		$args = func_get_args();
		if ( 'sabri_shell_can_show_create' !== $hook ) {
			return $value;
		}

		$GLOBALS['file20_filter_calls']++;
		$callback = $GLOBALS['file20_filter_callback'];
		return is_callable( $callback ) ? call_user_func_array( $callback, array_slice( $args, 1 ) ) : $value;
	}

	function update_option( $key, $value, $autoload = null ) {
		unset( $key, $value, $autoload );
		$GLOBALS['file20_option_writes']++;
		return true;
	}
}

namespace Sabri\UnifiedShell {
	final class Defaults {
		const OPTION_NAME = 'sabri_shell_settings';

		public static function settings() {
			return array(
				'emergency_disabled' => false,
				'header' => array(
					'enabled'       => true,
					'create'        => true,
					'allowed_roles' => array( 'administrator', 'editor' ),
				),
				'mobile' => array(
					'create_or_doctors' => 'auto',
				),
			);
		}
	}

	final class Settings {
		public static function get() {
			$raw = CreateVisibility::filter_runtime_settings( $GLOBALS['file20_stored_settings'], Defaults::OPTION_NAME );
			return array_replace_recursive( Defaults::settings(), is_array( $raw ) ? $raw : array() );
		}
	}

	final class SafeMode {
		public static function disabled() {
			if ( $GLOBALS['file20_safe_reads_settings'] ) {
				$settings = Settings::get();
				return ! empty( $settings['emergency_disabled'] );
			}
			return (bool) $GLOBALS['file20_safe_mode'];
		}
	}

	require_once dirname( __DIR__ ) . '/includes/class-create-visibility.php';

	$failures = array();
	$assert   = static function ( $condition, $message ) use ( &$failures ) {
		if ( ! $condition ) {
			$failures[] = $message;
		}
	};

	$reset = static function () {
		$GLOBALS['file20_admin']               = false;
		$GLOBALS['file20_logged_in']           = false;
		$GLOBALS['file20_caps']                = array();
		$GLOBALS['file20_user']                = (object) array( 'ID' => 1, 'roles' => array() );
		$GLOBALS['file20_stored_settings']     = array();
		$GLOBALS['file20_safe_mode']           = false;
		$GLOBALS['file20_safe_reads_settings'] = false;
		$GLOBALS['file20_filter_callback']     = static function ( $value ) { return $value; };
		$GLOBALS['file20_filter_calls']        = 0;
		$GLOBALS['file20_registered_filters']  = array();
		$GLOBALS['file20_option_writes']       = 0;
	};

	$reset();
	CreateVisibility::register();
	$assert( 1 === count( $GLOBALS['file20_registered_filters'] ), 'Public request did not register the runtime settings filter.' );
	$assert( 'option_sabri_shell_settings' === $GLOBALS['file20_registered_filters'][0][0], 'Create producer registered on the wrong option filter.' );
	$GLOBALS['file20_admin'] = true;
	CreateVisibility::register();
	$assert( 1 === count( $GLOBALS['file20_registered_filters'] ), 'Create producer registered a user-specific settings filter in wp-admin.' );

	$reset();
	$GLOBALS['file20_user'] = (object) array( 'ID' => 10, 'roles' => array( 'administrator' ) );
	$GLOBALS['file20_filter_callback'] = static function () { return true; };
	$logged_out = CreateVisibility::filter_runtime_settings( array( 'mobile' => array( 'create_or_doctors' => 'create' ) ) );
	$assert( 'doctors' === $logged_out['mobile']['create_or_doctors'], 'Logged-out explicit mobile Create was not neutralized.' );
	$assert( ! in_array( 'administrator', $logged_out['header']['allowed_roles'], true ), 'Logged-out role remained Create-eligible.' );
	$assert( 0 === $GLOBALS['file20_filter_calls'], 'External authorization filter ran before the login safeguard.' );

	$reset();
	$GLOBALS['file20_logged_in'] = true;
	$GLOBALS['file20_safe_mode'] = true;
	$GLOBALS['file20_caps']      = array( 'edit_posts' );
	$GLOBALS['file20_user']      = (object) array( 'ID' => 10, 'roles' => array( 'administrator' ) );
	$GLOBALS['file20_filter_callback'] = static function () { return true; };
	$safe_mode = CreateVisibility::filter_runtime_settings( array( 'mobile' => array( 'create_or_doctors' => 'create' ) ) );
	$assert( 'doctors' === $safe_mode['mobile']['create_or_doctors'], 'Safe Mode explicit mobile Create was not neutralized.' );
	$assert( ! in_array( 'administrator', $safe_mode['header']['allowed_roles'], true ), 'Safe Mode role remained Create-eligible.' );
	$assert( 0 === $GLOBALS['file20_filter_calls'], 'External authorization filter ran before the Safe Mode safeguard.' );

	$reset();
	$GLOBALS['file20_logged_in'] = true;
	$GLOBALS['file20_caps']      = array( 'edit_posts' );
	$GLOBALS['file20_user']      = (object) array( 'ID' => 10, 'roles' => array( 'administrator' ) );
	$legacy = CreateVisibility::filter_runtime_settings( array() );
	$assert( 'auto' === $legacy['mobile']['create_or_doctors'], 'Legacy administrator Create was not preserved.' );
	$GLOBALS['file20_stored_settings'] = $legacy;
	$assert( true === CreateVisibility::visible_for_current_user(), 'Visible producer rejected the legacy administrator.' );

	$reset();
	$GLOBALS['file20_logged_in'] = true;
	$GLOBALS['file20_caps']      = array( 'edit_posts' );
	$GLOBALS['file20_user']      = (object) array( 'ID' => 22, 'roles' => array( 'sabri_verified_doctor' ) );
	$GLOBALS['file20_filter_callback'] = static function ( $legacy_result, $user_id, $settings ) {
		return false === $legacy_result && 22 === $user_id && ! empty( $settings['header']['create'] );
	};
	$doctor = CreateVisibility::filter_runtime_settings( array( 'mobile' => array( 'create_or_doctors' => 'create' ) ) );
	$assert( in_array( 'sabri_verified_doctor', $doctor['header']['allowed_roles'], true ), 'Authorized doctor role was not added to the request-time Renderer decision.' );
	$assert( 'auto' === $doctor['mobile']['create_or_doctors'], 'Authorized doctor did not receive shared mobile Create authorization.' );
	$GLOBALS['file20_stored_settings'] = $doctor;
	$assert( true === CreateVisibility::visible_for_current_user(), 'Visible producer rejected the centrally authorized doctor.' );

	$reset();
	$GLOBALS['file20_logged_in'] = true;
	$GLOBALS['file20_caps']      = array( 'edit_posts' );
	$GLOBALS['file20_user']      = (object) array( 'ID' => 11, 'roles' => array( 'administrator' ) );
	$GLOBALS['file20_filter_callback'] = static function () { return false; };
	$narrowed = CreateVisibility::filter_runtime_settings( array( 'mobile' => array( 'create_or_doctors' => 'create' ) ) );
	$assert( ! in_array( 'administrator', $narrowed['header']['allowed_roles'], true ), 'Integration denial did not narrow the legacy role result.' );
	$assert( 'doctors' === $narrowed['mobile']['create_or_doctors'], 'Denied explicit mobile Create was not changed to Doctors.' );
	$GLOBALS['file20_stored_settings'] = $narrowed;
	$assert( false === CreateVisibility::visible_for_current_user(), 'Visible producer ignored the narrowed authorization result.' );

	$reset();
	$GLOBALS['file20_logged_in'] = true;
	$GLOBALS['file20_caps']      = array( 'edit_posts' );
	$GLOBALS['file20_user']      = (object) array( 'ID' => 10, 'roles' => array( 'administrator' ) );
	$GLOBALS['file20_stored_settings'] = array( 'header' => array( 'create' => false ) );
	$assert( false === CreateVisibility::visible_for_current_user(), 'Disabled Header Create was reported as visible.' );

	$reset();
	$GLOBALS['file20_logged_in']           = true;
	$GLOBALS['file20_caps']                = array( 'edit_posts' );
	$GLOBALS['file20_user']                = (object) array( 'ID' => 10, 'roles' => array( 'administrator' ) );
	$GLOBALS['file20_safe_reads_settings'] = true;
	$GLOBALS['file20_stored_settings']     = array( 'emergency_disabled' => true );
	$recursive = CreateVisibility::filter_runtime_settings( $GLOBALS['file20_stored_settings'] );
	$assert( 'doctors' === $recursive['mobile']['create_or_doctors'], 'Recursive Safe Mode settings read did not fail closed.' );
	$assert( false === CreateVisibility::contract_available(), 'Emergency Disable was not reflected by the producer contract.' );

	$reset();
	$malformed = CreateVisibility::filter_runtime_settings( 'not-an-array' );
	$assert( is_array( $malformed ) && isset( $malformed['header']['allowed_roles'], $malformed['mobile']['create_or_doctors'] ), 'Malformed option value was not normalized safely.' );
	$assert( 0 === $GLOBALS['file20_option_writes'], 'Request-time Create producer wrote settings to the database.' );

	$helper_source    = (string) file_get_contents( dirname( __DIR__ ) . '/includes/class-create-visibility.php' );
	$plugin_source    = (string) file_get_contents( dirname( __DIR__ ) . '/includes/class-plugin.php' );
	$bootstrap_source = (string) file_get_contents( dirname( __DIR__ ) . '/sabri-unified-application-shell.php' );
	$renderer_source  = (string) file_get_contents( dirname( __DIR__ ) . '/includes/class-renderer.php' );
	$assert( false !== strpos( $bootstrap_source, 'Version: 1.0.1' ) && false !== strpos( $bootstrap_source, "SABRI_SHELL_VERSION', '1.0.1" ), 'File 20 runtime version is not 1.0.1.' );
	$assert( false !== strpos( $bootstrap_source, "SABRI_SHELL_CREATE_CONTRACT_VERSION', '1.0.0" ), 'Versioned Create contract constant is missing.' );
	$assert( false !== strpos( $bootstrap_source, 'sabri_shell_create_contract_available' ) && false !== strpos( $bootstrap_source, 'sabri_shell_create_visible_for_current_user' ), 'Read-only Create producer functions are missing.' );
	$assert( strpos( $plugin_source, 'CreateVisibility::register();' ) < strpos( $plugin_source, 'Settings::register();' ), 'Create compatibility filter is registered after the first settings service.' );
	$assert( false !== strpos( $helper_source, "['create_or_doctors'] = 'doctors'" ) && false !== strpos( $helper_source, "['create_or_doctors'] = 'auto'" ), 'Request-time mobile bypass neutralization is missing.' );
	$assert( false === strpos( $helper_source, 'update_option(' ) && false === strpos( $helper_source, 'delete_option(' ), 'Create producer contains a settings write path.' );
	$assert( false !== strpos( $renderer_source, "'create' === \$settings['mobile']['create_or_doctors']" ), 'Test precondition changed: historical explicit mobile branch is no longer present.' );

	if ( $failures ) {
		fwrite( STDERR, implode( PHP_EOL, $failures ) . PHP_EOL );
		exit( 1 );
	}

	echo "File 20 Create producer contracts passed.\n";
}
