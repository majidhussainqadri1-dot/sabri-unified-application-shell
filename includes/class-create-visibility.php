<?php
/**
 * File 22 Create visibility producer and legacy Shell compatibility bridge.
 *
 * @package SabriUnifiedApplicationShell
 */

namespace Sabri\UnifiedShell;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Resolves one fail-closed Create decision for the existing desktop and mobile
 * Shell renderers without granting capabilities or persisting user-specific
 * settings.
 */
final class CreateVisibility {
	/** @var bool Prevent recursive request-time option filtering. */
	private static $resolving = false;

	/** Register the request-time compatibility filter on public Shell requests. */
	public static function register() {
		if ( function_exists( 'is_admin' ) && is_admin() ) {
			return;
		}

		if ( function_exists( 'add_filter' ) ) {
			add_filter( 'option_' . Defaults::OPTION_NAME, array( __CLASS__, 'filter_runtime_settings' ), 100, 2 );
		}
	}

	/** Whether the versioned producer is loaded and its kill switches are clear. */
	public static function contract_available() {
		return ! SafeMode::disabled();
	}

	/**
	 * Whether the current page can display the enabled desktop Create gateway.
	 *
	 * @return bool
	 */
	public static function visible_for_current_user() {
		if ( ! self::contract_available() || ! function_exists( 'is_user_logged_in' ) || ! is_user_logged_in() ) {
			return false;
		}

		$settings = Settings::get();
		if ( empty( $settings['header']['enabled'] ) || empty( $settings['header']['create'] ) ) {
			return false;
		}

		return self::legacy_renderer_allows_current_user( $settings );
	}

	/**
	 * Transform only the in-memory public-request settings consumed by the
	 * historical Renderer. The database option is never written here.
	 *
	 * @param mixed  $value  Raw option value.
	 * @param string $option Option name.
	 * @return mixed
	 */
	public static function filter_runtime_settings( $value, $option = '' ) {
		unset( $option );

		$value = is_array( $value ) ? $value : array();
		if ( self::$resolving ) {
			return $value;
		}

		self::$resolving = true;
		try {
			$settings = self::merge_runtime_settings( $value );
			$allowed  = self::resolve_final_authorization( $settings );
			$roles    = self::current_roles();

			if ( ! isset( $value['header'] ) || ! is_array( $value['header'] ) ) {
				$value['header'] = array();
			}
			if ( ! isset( $value['mobile'] ) || ! is_array( $value['mobile'] ) ) {
				$value['mobile'] = array();
			}

			$allowed_roles = isset( $settings['header']['allowed_roles'] ) && is_array( $settings['header']['allowed_roles'] ) ? $settings['header']['allowed_roles'] : array();
			if ( $allowed ) {
				$allowed_roles = array_values( array_unique( array_merge( $allowed_roles, $roles ) ) );
				$value['mobile']['create_or_doctors'] = 'auto';
			} else {
				$allowed_roles = array_values( array_diff( $allowed_roles, $roles ) );
				$value['mobile']['create_or_doctors'] = 'doctors';
			}

			$value['header']['allowed_roles'] = $allowed_roles;
			return $value;
		} finally {
			self::$resolving = false;
		}
	}

	/** Resolve the non-persistent, centrally extensible authorization decision. */
	private static function resolve_final_authorization( array $settings ) {
		if ( ! function_exists( 'is_user_logged_in' ) || ! is_user_logged_in() || SafeMode::disabled() ) {
			return false;
		}

		$user_id       = self::current_user_id();
		$legacy_result = self::legacy_renderer_allows_current_user( $settings );

		if ( ! function_exists( 'apply_filters' ) ) {
			return $legacy_result;
		}

		return (bool) apply_filters( 'sabri_shell_can_show_create', $legacy_result, $user_id, $settings );
	}

	/** Mirror the real historical Renderer permission test. */
	private static function legacy_renderer_allows_current_user( array $settings ) {
		if ( ! function_exists( 'current_user_can' ) || ! current_user_can( 'edit_posts' ) ) {
			return false;
		}

		$allowed_roles = isset( $settings['header']['allowed_roles'] ) && is_array( $settings['header']['allowed_roles'] ) ? $settings['header']['allowed_roles'] : array();
		return (bool) array_intersect( self::current_roles(), $allowed_roles );
	}

	/** @return array<int,string> */
	private static function current_roles() {
		if ( ! function_exists( 'wp_get_current_user' ) ) {
			return array();
		}

		$user  = wp_get_current_user();
		$roles = is_object( $user ) && isset( $user->roles ) && is_array( $user->roles ) ? $user->roles : array();
		return array_values( array_filter( array_map( 'sanitize_key', $roles ) ) );
	}

	/** Return the authenticated subject ID without accepting caller input. */
	private static function current_user_id() {
		$user = function_exists( 'wp_get_current_user' ) ? wp_get_current_user() : null;
		return is_object( $user ) && isset( $user->ID ) ? (int) $user->ID : 0;
	}

	/**
	 * Merge only the two settings groups needed by this bridge. Calling
	 * Settings::get() here would recurse through the option filter.
	 *
	 * @param array<string,mixed> $raw Raw stored settings.
	 * @return array<string,mixed>
	 */
	private static function merge_runtime_settings( array $raw ) {
		$defaults = Defaults::settings();
		$header   = isset( $raw['header'] ) && is_array( $raw['header'] ) ? array_merge( $defaults['header'], $raw['header'] ) : $defaults['header'];
		$mobile   = isset( $raw['mobile'] ) && is_array( $raw['mobile'] ) ? array_merge( $defaults['mobile'], $raw['mobile'] ) : $defaults['mobile'];

		return array(
			'header' => $header,
			'mobile' => $mobile,
		);
	}
}
