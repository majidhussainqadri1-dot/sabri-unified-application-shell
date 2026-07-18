<?php
/**
 * Default schema and static destination data.
 *
 * @package SabriUnifiedApplicationShell
 */

namespace Sabri\UnifiedShell;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Central defaults for settings, navigation, and schema versioning.
 */
final class Defaults {
	const OPTION_NAME          = 'sabri_shell_settings';
	const SNAPSHOT_OPTION_NAME = 'sabri_shell_activation_snapshot';
	const NAV_CACHE_KEY        = 'sabri_shell_navigation_cache_v1';
	const SCHEMA_VERSION       = 1;

	/**
	 * Return all default navigation destinations.
	 *
	 * @return array<string,array<string,mixed>>
	 */
	public static function destinations() {
		return array(
			'home'         => array(
				'label'       => __( 'Home', 'sabri-unified-application-shell' ),
				'group'       => 'main',
				'slugs'       => array( 'home', 'front-page' ),
				'shortcodes'  => array( 'sabri_shell_home_feed' ),
				'post_type'   => '',
				'order'       => 10,
				'bottom_nav'  => true,
				'visibility'  => 'public',
			),
			'news'         => array(
				'label'      => __( 'News', 'sabri-unified-application-shell' ),
				'group'      => 'main',
				'slugs'      => array( 'news', 'latest-news', 'blog' ),
				'shortcodes' => array(),
				'post_type'  => 'post',
				'order'      => 20,
			),
			'founder'      => array(
				'label'      => __( 'Founder', 'sabri-unified-application-shell' ),
				'group'      => 'main',
				'slugs'      => array( 'founder', 'dr-allama-majid-hussain-sabri', 'about-founder' ),
				'shortcodes' => array(),
				'post_type'  => '',
				'order'      => 30,
			),
			'learn'        => array(
				'label'      => __( 'Learn Sabri Classical Homeopathy', 'sabri-unified-application-shell' ),
				'group'      => 'learning',
				'slugs'      => array( 'learn-sabri-classical-homeopathy', 'learn', 'courses', 'education' ),
				'shortcodes' => array( 'sabri_courses', 'sabri_learning' ),
				'post_type'  => '',
				'order'      => 40,
			),
			'encyclopedia' => array(
				'label'      => __( 'Encyclopedia', 'sabri-unified-application-shell' ),
				'group'      => 'learning',
				'slugs'      => array( 'encyclopedia', 'materia-medica', 'remedies' ),
				'shortcodes' => array( 'sabri_encyclopedia' ),
				'post_type'  => 'encyclopedia',
				'order'      => 50,
				'bottom_nav' => true,
			),
			'doctors'      => array(
				'label'      => __( 'Doctors', 'sabri-unified-application-shell' ),
				'group'      => 'doctors',
				'slugs'      => array( 'doctors', 'doctor-directory', 'find-a-doctor' ),
				'shortcodes' => array( 'sabri_doctors' ),
				'post_type'  => 'doctor',
				'order'      => 60,
				'bottom_nav' => true,
			),
			'clinic'       => array(
				'label'      => __( 'Worldwide Clinic', 'sabri-unified-application-shell' ),
				'group'      => 'doctors',
				'slugs'      => array( 'worldwide-clinic', 'global-clinic', 'clinic-directory' ),
				'shortcodes' => array( 'sabri_worldwide_clinic', 'sabri_global_clinic' ),
				'post_type'  => 'clinic',
				'order'      => 70,
			),
			'video_wall'   => array(
				'label'      => __( 'Video Wall', 'sabri-unified-application-shell' ),
				'group'      => 'media',
				'slugs'      => array( 'video-wall', 'videos' ),
				'shortcodes' => array( 'sabri_video_wall' ),
				'post_type'  => 'video',
				'order'      => 80,
			),
			'reels'        => array(
				'label'      => __( 'Reels', 'sabri-unified-application-shell' ),
				'group'      => 'media',
				'slugs'      => array( 'reels', 'short-videos' ),
				'shortcodes' => array( 'sabri_reels' ),
				'post_type'  => 'reel',
				'order'      => 90,
			),
			'pdf_library'  => array(
				'label'      => __( 'PDF Library', 'sabri-unified-application-shell' ),
				'group'      => 'media',
				'slugs'      => array( 'pdf-library', 'library', 'downloads' ),
				'shortcodes' => array( 'sabri_pdf_library' ),
				'post_type'  => 'sabri_pdf',
				'order'      => 100,
			),
			'radar'        => array(
				'label'      => __( 'Radar', 'sabri-unified-application-shell' ),
				'group'      => 'learning',
				'slugs'      => array( 'radar', 'sabri-radar' ),
				'shortcodes' => array( 'sabri_radar' ),
				'post_type'  => '',
				'order'      => 110,
			),
			'ai'           => array(
				'label'      => __( 'Sabri Classical Homeopathy AI', 'sabri-unified-application-shell' ),
				'group'      => 'learning',
				'slugs'      => array( 'sabri-classical-homeopathy-ai', 'ai', 'sabri-ai' ),
				'shortcodes' => array( 'sabri_ai' ),
				'post_type'  => '',
				'order'      => 120,
			),
			'network'      => array(
				'label'      => __( 'Network', 'sabri-unified-application-shell' ),
				'group'      => 'social',
				'slugs'      => array( 'network', 'community' ),
				'shortcodes' => array( 'sabri_network' ),
				'post_type'  => 'sabri_network_post',
				'order'      => 130,
			),
			'marketplace'  => array(
				'label'      => __( 'Marketplace', 'sabri-unified-application-shell' ),
				'group'      => 'platform',
				'slugs'      => array( 'marketplace', 'shop' ),
				'shortcodes' => array( 'sabri_marketplace' ),
				'post_type'  => 'product',
				'order'      => 140,
			),
			'university'   => array(
				'label'      => __( 'University', 'sabri-unified-application-shell' ),
				'group'      => 'learning',
				'slugs'      => array( 'university', 'sabri-university' ),
				'shortcodes' => array( 'sabri_university' ),
				'post_type'  => '',
				'order'      => 150,
			),
			'research'     => array(
				'label'      => __( 'Research Center', 'sabri-unified-application-shell' ),
				'group'      => 'learning',
				'slugs'      => array( 'research-center', 'research' ),
				'shortcodes' => array( 'sabri_research_center' ),
				'post_type'  => 'research',
				'order'      => 160,
			),
			'appointments' => array(
				'label'      => __( 'Appointments', 'sabri-unified-application-shell' ),
				'group'      => 'doctors',
				'slugs'      => array( 'appointments', 'book-appointment' ),
				'shortcodes' => array( 'sabri_appointments' ),
				'post_type'  => 'appointment',
				'order'      => 170,
				'visibility' => 'logged_in',
			),
			'messages'     => array(
				'label'      => __( 'Messages', 'sabri-unified-application-shell' ),
				'group'      => 'social',
				'slugs'      => array( 'messages', 'inbox' ),
				'shortcodes' => array( 'sabri_messages' ),
				'post_type'  => '',
				'order'      => 180,
				'bottom_nav' => true,
				'visibility' => 'logged_in',
			),
			'notifications' => array(
				'label'      => __( 'Notifications', 'sabri-unified-application-shell' ),
				'group'      => 'social',
				'slugs'      => array( 'notifications', 'alerts' ),
				'shortcodes' => array( 'sabri_notifications' ),
				'post_type'  => '',
				'order'      => 190,
				'visibility' => 'logged_in',
			),
			'saved'        => array(
				'label'      => __( 'Saved', 'sabri-unified-application-shell' ),
				'group'      => 'social',
				'slugs'      => array( 'saved', 'bookmarks' ),
				'shortcodes' => array( 'sabri_saved' ),
				'post_type'  => '',
				'order'      => 200,
				'visibility' => 'logged_in',
			),
			'support'      => array(
				'label'      => __( 'Support', 'sabri-unified-application-shell' ),
				'group'      => 'platform',
				'slugs'      => array( 'support', 'help' ),
				'shortcodes' => array( 'sabri_support' ),
				'post_type'  => '',
				'order'      => 210,
			),
			'settings'     => array(
				'label'      => __( 'Settings', 'sabri-unified-application-shell' ),
				'group'      => 'platform',
				'slugs'      => array( 'settings', 'account-settings' ),
				'shortcodes' => array( 'sabri_settings' ),
				'post_type'  => '',
				'order'      => 220,
				'visibility' => 'logged_in',
			),
		);
	}

	/**
	 * Default settings.
	 *
	 * @return array<string,mixed>
	 */
	public static function settings() {
		$destinations = self::destinations();
		$navigation   = array();
		$visibility   = array();

		foreach ( $destinations as $key => $destination ) {
			$navigation[ $key ] = array(
				'enabled'      => true,
				'label'        => $destination['label'],
				'page_id'      => 0,
				'shortcode'    => isset( $destination['shortcodes'][0] ) ? $destination['shortcodes'][0] : '',
				'slug'         => isset( $destination['slugs'][0] ) ? $destination['slugs'][0] : '',
				'url_override' => '',
				'order'        => isset( $destination['order'] ) ? (int) $destination['order'] : 999,
			);
			$visibility[ $key ] = true;
		}

		return array(
			'schema_version'       => self::SCHEMA_VERSION,
			'enabled'              => true,
			'emergency_disabled'   => false,
			'delete_on_uninstall'  => false,
			'home_feed'            => array(
				'auto_insert' => true,
				'posts_count' => 10,
			),
			'layout'               => array(
				'max_width'                 => 1600,
				'left_width'                => 280,
				'right_width'               => 340,
				'gap'                       => 24,
				'sticky_header'             => true,
				'compact_desktop'           => false,
				'worldwide_clinic_page_id'  => 0,
				'clinic_post_type'          => 'doctor',
				'excluded_page_ids'         => array(),
				'per_page_overrides'        => array(),
				'theme_content_selector'    => '',
				'hide_theme_header'         => false,
				'hide_theme_footer'         => false,
				'custom_hide_selectors'     => '',
			),
			'header'               => array(
				'enabled'       => true,
				'platform_title'=> __( 'Sabri Social Homeopathy', 'sabri-unified-application-shell' ),
				'search'        => true,
				'create'        => true,
				'messages'      => true,
				'notifications' => true,
				'help'          => true,
				'language'      => true,
				'profile'       => true,
				'allowed_roles' => array( 'administrator', 'editor' ),
			),
			'navigation'           => $navigation,
			'left_sidebar'         => array(
				'enabled'          => true,
				'groups'           => array(
					'main'      => true,
					'learning'  => true,
					'doctors'   => true,
					'media'     => true,
					'social'    => true,
					'platform'  => true,
				),
				'items'            => $visibility,
				'footer_mappings'  => array(
					'privacy'    => '',
					'terms'      => '',
					'disclaimer' => '',
					'guidelines' => '',
					'contact'    => '',
					'whatsapp'   => '',
				),
			),
			'right_sidebar'        => array(
				'enabled'          => true,
				'hide_missing'     => true,
				'home_modules'     => array(
					'founder'      => true,
					'announcement' => true,
					'network'      => true,
					'doctors'      => true,
					'latest_posts' => true,
					'research'     => true,
					'marketplace'  => true,
					'quick_access' => true,
				),
				'clinic_modules'   => array(
					'finder'       => true,
					'filters'      => true,
					'doctors'      => true,
					'appointments' => true,
					'emergency'    => true,
					'whatsapp'     => true,
				),
				'single_modules'   => array(
					'profile'      => true,
					'appointment'  => true,
					'message'      => true,
					'contact'      => true,
					'reviews'      => true,
					'safety'       => true,
				),
				'announcement'     => '',
				'emergency_notice' => __( 'For medical emergencies, contact local emergency services immediately.', 'sabri-unified-application-shell' ),
			),
			'mobile'               => array(
				'bottom_nav'               => true,
				'drawers'                  => true,
				'create_or_doctors'         => 'auto',
				'menu_label'               => __( 'Menu', 'sabri-unified-application-shell' ),
				'create_label'             => __( 'Create', 'sabri-unified-application-shell' ),
			),
			'integrations'         => array(
				'functions' => array(
					'notifications' => '',
					'network'       => '',
					'messages'      => '',
					'appointments'  => '',
				),
				'urls'      => array(
					'messages'      => '',
					'notifications' => '',
					'appointments'  => '',
					'help'          => '',
					'whatsapp'      => '',
				),
			),
			'appearance'           => array(
				'color_mode'    => 'system',
				'density'       => 'comfortable',
				'primary_color' => '#f26100',
				'border_radius' => 8,
				'font_scale'    => 1,
			),
		);
	}

	/**
	 * Groups and labels for the left sidebar.
	 *
	 * @return array<string,string>
	 */
	public static function groups() {
		return array(
			'main'     => __( 'Main', 'sabri-unified-application-shell' ),
			'learning' => __( 'Learning', 'sabri-unified-application-shell' ),
			'doctors'  => __( 'Doctors and Care', 'sabri-unified-application-shell' ),
			'media'    => __( 'Media', 'sabri-unified-application-shell' ),
			'social'   => __( 'Social', 'sabri-unified-application-shell' ),
			'platform' => __( 'Platform', 'sabri-unified-application-shell' ),
		);
	}
}
