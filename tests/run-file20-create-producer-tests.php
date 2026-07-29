<?php
/**
 * Focused runtime and source contracts for File 20 v1.0.1 Create producer.
 *
 * @package SabriUnifiedApplicationShell
 */

declare(strict_types=1);

namespace {
	define( 'ABSPATH', __DIR__ . '/' );

	$GLOBALS['file20_create_logged_in'] = false;
	$GLOBALS['file20_create_safe_mode'] = false;
	$GLOBALS['file20_create_caps']      = array();
	$GLOBALS['file20_create_user']      = (object) array(
		'ID'    => 1,
		'roles' => array(),
	);
	$GLOBALS['file20_create_settings']  = array(
		'header' => array(
			'create'        => true,
			'allowed_roles' => array( 'administrator', 'editor' ),
		),
		'mobile' => array(
			'create_or_doctors' => 'auto',
		),
	);
	$GLOBALS['file20_create_filter']       = null;
	$GLOBALS['file20_create_filter_calls'] = 0;

	function is_user_logged_in() {
		return (bool) $GLOBALS['file20_create_logged_in'];
	}

	function current_user_can( $capability ) {
		return in_array( $capability, $GLOBALS['file20_create_caps'], true );
	}

	function wp_get_current_user() {
		return $GLOBALS['file20_create_user'];
	}

	function apply_filters( $hook, $value ) {
		$args = func_get_args();
		if ( 'sabri_shell_can_show_create' !== $hook ) {
			return $value;
		}

		$GLOBALS['file20_create_filter_calls']++;
		$callback = $GLOBALS['file20_create_filter'];
		if ( is_callable( $callback ) ) {
			return call_user_func_array( $callback, array_slice( $args, 1 ) );
		}

		return $value;
	}
}

namespace Sabri\UnifiedShell {
	final class Settings {
		public static function get() {
			return $GLOBALS['file20_create_settings'];
		}
	}

	final class SafeMode {
		public static function disabled() {
			return (bool) $GLOBALS['file20_create_safe_mode'];
		}
	}

	require_once dirname( __DIR__ ) . '/includes/class-renderer.php';

	$failures = array();
	$assert   = static function ( $condition, $message ) use ( &$failures ) {
		if ( ! $condition ) {
			$failures[] = $message;
		}
	};

	$reset = static function () {
		$GLOBALS['file20_create_logged_in'] = false;
		$GLOBALS['file20_create_safe_mode'] = false;
		$GLOBALS['file20_create_caps']      = array();
		$GLOBALS['file20_create_user']      = (object) array(
			'ID'    => 1,
			'roles' => array(),
		);
		$GLOBALS['file20_create_settings']  = array(
			'header' => array(
				'create'        => true,
				'allowed_roles' => array( 'administrator', 'editor' ),
			),
			'mobile' => array(
				'create_or_doctors' => 'auto',
			),
		);
		$GLOBALS['file20_create_filter']       = static function ( $value ) {
			return $value;
		};
		$GLOBALS['file20_create_filter_calls'] = 0;
	};

	$reset();
	$GLOBALS['file20_create_filter'] = static function () {
		return true;
	};
	$assert( false === Renderer::create_visible_for_current_user(), 'Logged-out user bypassed the non-overridable Create safeguard.' );
	$assert( 0 === $GLOBALS['file20_create_filter_calls'], 'Create filter ran before the logged-in safeguard.' );

	$reset();
	$GLOBALS['file20_create_logged_in'] = true;
	$GLOBALS['file20_create_safe_mode'] = true;
	$GLOBALS['file20_create_filter']    = static function () {
		return true;
	};
	$assert( false === Renderer::create_visible_for_current_user(), 'Safe Mode was overridden by the Create filter.' );
	$assert( 0 === $GLOBALS['file20_create_filter_calls'], 'Create filter ran before the Safe Mode safeguard.' );

	$reset();
	$GLOBALS['file20_create_logged_in'] = true;
	$GLOBALS['file20_create_caps']      = array( 'edit_posts' );
	$GLOBALS['file20_create_user']      = (object) array(
		'ID'    => 10,
		'roles' => array( 'administrator' ),
	);
	$assert( true === Renderer::create_visible_for_current_user(), 'Legacy administrator fallback did not allow Create.' );

	$reset();
	$GLOBALS['file20_create_logged_in'] = true;
	$GLOBALS['file20_create_user']      = (object) array(
		'ID'    => 22,
		'roles' => array( 'sabri_verified_doctor' ),
	);
	$GLOBALS['file20_create_filter']    = static function ( $legacy_result, $user_id, $settings ) {
		return false === $legacy_result && 22 === $user_id && ! empty( $settings['header']['create'] );
	};
	$assert( true === Renderer::create_visible_for_current_user(), 'File 22 could not replace the legacy role-list result for an authorized publisher.' );

	$reset();
	$GLOBALS['file20_create_logged_in'] = true;
	$GLOBALS['file20_create_caps']      = array( 'edit_posts' );
	$GLOBALS['file20_create_user']      = (object) array(
		'ID'    => 11,
		'roles' => array( 'administrator' ),
	);
	$GLOBALS['file20_create_filter']    = static function () {
		return false;
	};
	$assert( false === Renderer::create_visible_for_current_user(), 'An integration could not narrow the legacy Create decision.' );

	$reset();
	$GLOBALS['file20_create_logged_in']            = true;
	$GLOBALS['file20_create_caps']                 = array( 'edit_posts' );
	$GLOBALS['file20_create_user']->roles          = array( 'administrator' );
	$GLOBALS['file20_create_settings']['header']['create'] = false;
	$assert( false === Renderer::create_visible_for_current_user(), 'Disabled desktop Create presentation was reported as visible.' );

	$renderer_source = (string) file_get_contents( dirname( __DIR__ ) . '/includes/class-renderer.php' );
	$bootstrap_source = (string) file_get_contents( dirname( __DIR__ ) . '/sabri-unified-application-shell.php' );
	$assert( false !== strpos( $renderer_source, "in_array( \$settings['mobile']['create_or_doctors'], array( 'create', 'auto' ), true ) && self::current_user_can_create( \$settings )" ), 'Explicit mobile Create does not use the shared final authorization decision.' );
	$assert( false === strpos( $renderer_source, "'create' === \$settings['mobile']['create_or_doctors'] ||" ), 'Historical explicit-mobile authorization bypass remains in source.' );
	$assert( false !== strpos( $bootstrap_source, "SABRI_SHELL_CREATE_CONTRACT_VERSION', '1.0.0" ), 'Versioned Create contract constant is missing.' );
	$assert( false !== strpos( $bootstrap_source, 'sabri_shell_create_contract_available' ), 'Create contract availability producer is missing.' );
	$assert( false !== strpos( $bootstrap_source, 'sabri_shell_create_visible_for_current_user' ), 'Current-user Create visibility producer is missing.' );

	if ( $failures ) {
		fwrite( STDERR, implode( PHP_EOL, $failures ) . PHP_EOL );
		exit( 1 );
	}

	echo "File 20 Create producer contracts passed.\n";
}
