<?php
/**
 * Integration detection.
 *
 * @package SabriUnifiedApplicationShell
 */

namespace Sabri\UnifiedShell;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Detects existing systems without creating duplicate backends.
 */
final class Integrations {
	/**
	 * Detect integration status.
	 *
	 * @return array<string,mixed>
	 */
	public static function detect() {
		$settings = Settings::get();
		$roles    = self::roles();

		return array(
			'notifications'          => self::has_configured_function( 'notifications', $settings ) || shortcode_exists( 'sabri_notifications' ) || ! empty( $settings['integrations']['urls']['notifications'] ),
			'network'                => self::has_configured_function( 'network', $settings ) || shortcode_exists( 'sabri_network' ) || post_type_exists( 'sabri_network_post' ),
			'messages'               => self::has_configured_function( 'messages', $settings ) || shortcode_exists( 'sabri_messages' ) || ! empty( $settings['integrations']['urls']['messages'] ),
			'marketplace'            => post_type_exists( 'product' ) || shortcode_exists( 'sabri_marketplace' ),
			'appointments'           => self::has_configured_function( 'appointments', $settings ) || shortcode_exists( 'sabri_appointments' ) || post_type_exists( 'appointment' ) || ! empty( $settings['integrations']['urls']['appointments'] ),
			'language'               => function_exists( 'pll_the_languages' ) || function_exists( 'icl_get_languages' ) || function_exists( 'weglot_get_current_language' ),
			'doctor_roles'           => array_values( array_intersect( array( 'doctor', 'verified_doctor', 'approved_doctor', 'founder' ), $roles ) ),
			'verified_doctor_roles'  => array_values( array_intersect( array( 'verified_doctor', 'approved_doctor' ), $roles ) ),
			'clinic_post_types'      => self::existing_post_types( array( 'doctor', 'clinic', 'global_clinic', 'sabri_clinic' ) ),
			'configured_functions'   => $settings['integrations']['functions'],
			'configured_urls'        => $settings['integrations']['urls'],
		);
	}

	/**
	 * Determine whether a configured callback exists.
	 *
	 * @param string              $key Integration key.
	 * @param array<string,mixed> $settings Settings.
	 * @return bool
	 */
	private static function has_configured_function( $key, array $settings ) {
		$function = isset( $settings['integrations']['functions'][ $key ] ) ? $settings['integrations']['functions'][ $key ] : '';
		return $function && function_exists( $function );
	}

	/**
	 * Get available role names.
	 *
	 * @return array<int,string>
	 */
	public static function roles() {
		if ( ! function_exists( 'wp_roles' ) ) {
			return array();
		}

		$roles = wp_roles();
		if ( ! $roles || empty( $roles->roles ) || ! is_array( $roles->roles ) ) {
			return array();
		}

		return array_keys( $roles->roles );
	}

	/**
	 * Get existing post types from a candidate list.
	 *
	 * @param array<int,string> $candidates Candidate slugs.
	 * @return array<int,string>
	 */
	private static function existing_post_types( array $candidates ) {
		$existing = array();
		foreach ( $candidates as $post_type ) {
			if ( post_type_exists( $post_type ) ) {
				$existing[] = $post_type;
			}
		}

		return $existing;
	}
}
