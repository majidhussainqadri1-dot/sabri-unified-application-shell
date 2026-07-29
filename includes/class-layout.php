<?php
/**
 * Public layout resolver.
 *
 * @package SabriUnifiedApplicationShell
 */

namespace Sabri\UnifiedShell;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Resolves three-column, two-column, and minimal modes.
 */
final class Layout {
	const THREE   = 'three';
	const TWO     = 'two';
	const MINIMAL = 'minimal';

	/**
	 * Fallback selectors for the public content target.
	 *
	 * @return array<int,string>
	 */
	public static function content_target_fallbacks() {
		return array(
			'.wp-site-blocks',
			'#page',
			'.site',
			'main',
			'#content',
			'.site-content',
		);
	}

	/**
	 * Return configured selector followed by fallback selectors.
	 *
	 * @param array<string,mixed>|null $settings Settings.
	 * @return array<int,string>
	 */
	public static function content_target_candidates( $settings = null ) {
		if ( null === $settings ) {
			$settings = Settings::get();
		}

		$candidates = array();
		if ( ! empty( $settings['layout']['theme_content_selector'] ) ) {
			$candidates[] = $settings['layout']['theme_content_selector'];
		}

		return array_values( array_unique( array_merge( $candidates, self::content_target_fallbacks() ) ) );
	}

	/**
	 * Human-readable target resolver summary for System Check.
	 *
	 * @param array<string,mixed>|null $settings Settings.
	 * @return string
	 */
	public static function content_target_report( $settings = null ) {
		if ( null === $settings ) {
			$settings = Settings::get();
		}

		if ( ! empty( $settings['layout']['theme_content_selector'] ) ) {
			return sprintf(
				/* translators: %s is a CSS selector. */
				__( 'Configured selector preferred at runtime: %s', 'sabri-unified-application-shell' ),
				$settings['layout']['theme_content_selector']
			);
		}

		return sprintf(
			/* translators: %s is a comma-separated selector list. */
			__( 'Runtime fallback order: %s', 'sabri-unified-application-shell' ),
			implode( ', ', self::content_target_fallbacks() )
		);
	}

	/**
	 * Resolve the current request layout.
	 *
	 * @return string
	 */
	public static function current_mode() {
		$settings = Settings::get();

		if ( empty( $settings['enabled'] ) || SafeMode::disabled() || self::is_excluded_request() ) {
			return self::MINIMAL;
		}

		$page_id = self::current_page_id();
		if ( $page_id && in_array( $page_id, $settings['layout']['excluded_page_ids'], true ) ) {
			return self::MINIMAL;
		}

		if ( $page_id && ! empty( $settings['layout']['per_page_overrides'][ $page_id ] ) ) {
			$override = $settings['layout']['per_page_overrides'][ $page_id ];
			if ( 'default' !== $override && in_array( $override, array( self::THREE, self::TWO, self::MINIMAL ), true ) ) {
				return apply_filters( 'sabri_shell_layout_mode', $override, $settings );
			}
		}

		if ( self::is_three_column_context( $settings, $page_id ) ) {
			return apply_filters( 'sabri_shell_layout_mode', self::THREE, $settings );
		}

		return apply_filters( 'sabri_shell_layout_mode', self::TWO, $settings );
	}

	/**
	 * Whether the right sidebar may render.
	 *
	 * @return bool
	 */
	public static function right_sidebar_allowed() {
		$settings = Settings::get();
		return self::THREE === self::current_mode() && ! empty( $settings['right_sidebar']['enabled'] );
	}

	/**
	 * Determine three-column contexts.
	 *
	 * @param array<string,mixed> $settings Settings.
	 * @param int                 $page_id Current page ID.
	 * @return bool
	 */
	public static function is_three_column_context( array $settings, $page_id = 0 ) {
		if ( function_exists( 'is_front_page' ) && is_front_page() ) {
			return true;
		}

		$clinic_page_id = isset( $settings['layout']['worldwide_clinic_page_id'] ) ? absint( $settings['layout']['worldwide_clinic_page_id'] ) : 0;
		if ( $clinic_page_id && $page_id && $clinic_page_id === $page_id ) {
			return true;
		}

		$post_type = isset( $settings['layout']['clinic_post_type'] ) ? sanitize_key( $settings['layout']['clinic_post_type'] ) : '';
		if ( $post_type && function_exists( 'is_singular' ) && is_singular( $post_type ) ) {
			return true;
		}

		return false;
	}

	/**
	 * Determine if the request must be excluded.
	 *
	 * @return bool
	 */
	public static function is_excluded_request() {
		if ( is_admin() ) {
			return true;
		}

		if ( function_exists( 'wp_doing_ajax' ) && wp_doing_ajax() ) {
			return true;
		}

		if ( function_exists( 'wp_doing_cron' ) && wp_doing_cron() ) {
			return true;
		}

		if ( defined( 'REST_REQUEST' ) && REST_REQUEST ) {
			return true;
		}

		if ( defined( 'XMLRPC_REQUEST' ) && XMLRPC_REQUEST ) {
			return true;
		}

		$conditional_checks = array( 'is_feed', 'is_embed', 'is_preview', 'is_customize_preview', 'is_robots', 'is_favicon', 'is_trackback' );
		foreach ( $conditional_checks as $function ) {
			if ( function_exists( $function ) && $function() ) {
				return true;
			}
		}

		if ( function_exists( 'is_singular' ) && is_singular() && function_exists( 'post_password_required' ) && post_password_required() ) {
			return true;
		}

		if ( ! empty( $_GET['print'] ) || ! empty( $_GET['sabri_shell_maintenance'] ) ) {
			return true;
		}

		$uri = isset( $_SERVER['REQUEST_URI'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REQUEST_URI'] ) ) : '';
		if ( preg_match( '#/(wp-login\.php|login|signup|register|lostpassword|password-reset|account-verification)(/|\?|$)#i', $uri ) ) {
			return true;
		}

		if ( preg_match( '#/(wp-json|feed|robots\.txt|sitemap[^/]*)#i', $uri ) ) {
			return true;
		}

		return false;
	}

	/**
	 * Return current public page ID when available.
	 *
	 * @return int
	 */
	public static function current_page_id() {
		if ( function_exists( 'get_queried_object_id' ) ) {
			return absint( get_queried_object_id() );
		}

		return 0;
	}
}
