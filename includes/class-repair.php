<?php
/**
 * Complete Repair implementation.
 *
 * @package SabriUnifiedApplicationShell
 */

namespace Sabri\UnifiedShell;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Repairs shell-only configuration and caches.
 */
final class Repair {
	/**
	 * Run complete repair.
	 *
	 * @return array<int,string>
	 */
	public static function run() {
		$messages = array();

		$before = Settings::get();
		Settings::ensure_defaults();
		$after = Settings::get();

		if ( $before !== $after ) {
			$messages[] = __( 'Missing defaults were merged without deleting unknown settings.', 'sabri-unified-application-shell' );
		} else {
			$messages[] = __( 'Settings already matched the current schema.', 'sabri-unified-application-shell' );
		}

		Navigation::invalidate_cache();
		$messages[] = __( 'Navigation cache cleared.', 'sabri-unified-application-shell' );

		delete_transient( 'sabri_shell_integration_cache' );
		$messages[] = __( 'Shell-only transients cleared.', 'sabri-unified-application-shell' );

		update_option( 'sabri_shell_flush_rewrite_rules', 1, false );
		$messages[] = __( 'A one-time rewrite-rule flush was scheduled.', 'sabri-unified-application-shell' );

		do_action( 'sabri_shell_complete_repair_ran' );

		return $messages;
	}
}
