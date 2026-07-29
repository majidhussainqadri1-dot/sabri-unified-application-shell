<?php
/** Fail-closed roleless-subject regression for File 20 Create producer. */

declare(strict_types=1);

namespace {
	define( 'ABSPATH', __DIR__ . '/' );
	$GLOBALS['f20_roleless_filter_calls'] = 0;

	function __( $text, $domain = '' ) { unset( $domain ); return $text; }
	function sanitize_key( $key ) { return preg_replace( '/[^a-z0-9_\-]/', '', strtolower( (string) $key ) ); }
	function is_user_logged_in() { return true; }
	function current_user_can( $capability ) { return 'edit_posts' === $capability; }
	function wp_get_current_user() { return (object) array( 'ID' => 44, 'roles' => array() ); }
	function apply_filters( $hook, $value ) {
		unset( $value );
		if ( 'sabri_shell_can_show_create' === $hook ) {
			$GLOBALS['f20_roleless_filter_calls']++;
			return true;
		}
		return false;
	}
}

namespace Sabri\UnifiedShell {
	final class Defaults {
		const OPTION_NAME = 'sabri_shell_settings';
		public static function settings() {
			return array(
				'header' => array( 'enabled' => true, 'create' => true, 'allowed_roles' => array( 'administrator' ) ),
				'mobile' => array( 'create_or_doctors' => 'create' ),
			);
		}
	}
	final class SafeMode { public static function disabled() { return false; } }
	final class Settings {
		public static function get() {
			return array(
				'header' => array( 'enabled' => true, 'create' => true, 'allowed_roles' => array( 'administrator' ) ),
				'mobile' => array( 'create_or_doctors' => 'create' ),
			);
		}
	}

	require_once dirname( __DIR__ ) . '/includes/class-create-visibility.php';

	$result = CreateVisibility::filter_runtime_settings(
		array(
			'header' => array( 'enabled' => true, 'create' => true, 'allowed_roles' => array( 'administrator' ) ),
			'mobile' => array( 'create_or_doctors' => 'create' ),
		)
	);

	$failures = array();
	if ( 'doctors' !== ( $result['mobile']['create_or_doctors'] ?? '' ) ) {
		$failures[] = 'Roleless subject retained explicit mobile Create.';
	}
	if ( 0 !== $GLOBALS['f20_roleless_filter_calls'] ) {
		$failures[] = 'File 22 extension filter ran for a roleless subject.';
	}
	if ( CreateVisibility::visible_for_current_user() ) {
		$failures[] = 'Roleless subject was reported as Create-visible.';
	}

	if ( $failures ) {
		fwrite( STDERR, implode( PHP_EOL, $failures ) . PHP_EOL );
		exit( 1 );
	}

	echo "File 20 roleless-subject contract passed.\n";
}
