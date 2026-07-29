<?php
/**
 * Safe mode and emergency disable helpers.
 *
 * @package SabriUnifiedApplicationShell
 */

namespace Sabri\UnifiedShell;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Determines when the public shell should be suppressed.
 */
final class SafeMode {
	/**
	 * Whether the constant kill switch is active.
	 *
	 * @return bool
	 */
	public static function constant_disabled() {
		return defined( 'SABRI_SHELL_DISABLE' ) && SABRI_SHELL_DISABLE;
	}

	/**
	 * Whether an authorized safe mode URL flag is active.
	 *
	 * @return bool
	 */
	public static function query_safe_mode() {
		if ( empty( $_GET['sabri_shell_safe'] ) ) {
			return false;
		}

		return is_user_logged_in() && current_user_can( 'manage_options' );
	}

	/**
	 * Whether admin emergency disable is active.
	 *
	 * @return bool
	 */
	public static function emergency_disabled() {
		$settings = Settings::get();
		return ! empty( $settings['emergency_disabled'] );
	}

	/**
	 * Whether any disable path is active.
	 *
	 * @return bool
	 */
	public static function disabled() {
		return self::constant_disabled() || self::query_safe_mode() || self::emergency_disabled();
	}
}
