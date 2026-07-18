<?php
/**
 * Activation snapshot and rollback.
 *
 * @package SabriUnifiedApplicationShell
 */

namespace Sabri\UnifiedShell;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Stores shell-only pre-activation state before defaults or migrations run.
 */
final class Snapshot {
	/**
	 * Capture snapshot before any settings mutation.
	 *
	 * @return void
	 */
	public static function capture_activation_snapshot() {
		if ( false !== get_option( Defaults::SNAPSHOT_OPTION_NAME, false ) ) {
			return;
		}

		$snapshot = array(
			'captured_at'        => current_time( 'mysql', true ),
			'settings'           => get_option( Defaults::OPTION_NAME, null ),
			'navigation_cache'   => get_transient( Defaults::NAV_CACHE_KEY ),
			'front_page'         => get_option( 'page_on_front' ),
			'show_on_front'      => get_option( 'show_on_front' ),
			'flush_scheduled'    => get_option( 'sabri_shell_flush_rewrite_rules', null ),
			'schema_version'     => Defaults::SCHEMA_VERSION,
			'allowed_boundaries' => array(
				'shell settings',
				'shell navigation mappings',
				'shell front-page-related configuration',
				'shell theme-visibility settings',
			),
		);

		update_option( Defaults::SNAPSHOT_OPTION_NAME, $snapshot, false );
	}

	/**
	 * Restore only shell-owned data.
	 *
	 * @return bool
	 */
	public static function rollback() {
		$snapshot = get_option( Defaults::SNAPSHOT_OPTION_NAME, false );
		if ( ! is_array( $snapshot ) ) {
			return false;
		}

		if ( array_key_exists( 'settings', $snapshot ) ) {
			if ( null === $snapshot['settings'] ) {
				delete_option( Defaults::OPTION_NAME );
			} else {
				update_option( Defaults::OPTION_NAME, $snapshot['settings'], false );
			}
		}

		Navigation::invalidate_cache();
		update_option( 'sabri_shell_flush_rewrite_rules', 1, false );

		return true;
	}
}
