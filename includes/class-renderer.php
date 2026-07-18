<?php
/**
 * Public shell renderer.
 *
 * @package SabriUnifiedApplicationShell
 */

namespace Sabri\UnifiedShell;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Renders balanced standalone shell components.
 */
final class Renderer {
	/**
	 * Once-only Notifications guard.
	 *
	 * @var bool
	 */
	private static $notifications_rendered = false;

	/**
	 * Register render hooks.
	 *
	 * @return void
	 */
	public static function register() {
		add_filter( 'body_class', array( __CLASS__, 'body_classes' ) );
		add_action( 'wp_enqueue_scripts', array( 'Sabri\\UnifiedShell\\Assets', 'enqueue' ) );
		add_action( 'wp_head', array( 'Sabri\\UnifiedShell\\Assets', 'print_custom_properties' ), 20 );
		add_action( 'wp_body_open', array( __CLASS__, 'render_shell_start' ), 5 );
		add_action( 'wp_footer', array( __CLASS__, 'render_shell_footer' ), 20 );
	}

	/**
	 * Add scoped body classes.
	 *
	 * @param array<int,string> $classes Existing classes.
	 * @return array<int,string>
	 */
	public static function body_classes( $classes ) {
		$mode      = Layout::current_mode();
		$classes[] = 'sabri-shell-layout-' . $mode;

		if ( Layout::MINIMAL !== $mode ) {
			$classes[] = 'sabri-shell-enabled';
		}

		$settings = Settings::get();
		$classes[] = 'sabri-shell-theme-' . sanitize_html_class( $settings['appearance']['color_mode'] );
		$classes[] = 'sabri-shell-density-' . sanitize_html_class( $settings['appearance']['density'] );
		$classes[] = ! empty( $settings['layout']['sticky_header'] ) ? 'sabri-shell-sticky-header' : 'sabri-shell-static-header';
		$classes[] = ! empty( $settings['layout']['compact_desktop'] ) ? 'sabri-shell-compact-desktop' : 'sabri-shell-standard-desktop';

		return $classes;
	}

	/**
	 * Render header, nav, and desktop sidebars as standalone balanced components.
	 *
	 * @return void
	 */
	public static function render_shell_start() {
		$mode = Layout::current_mode();
		if ( Layout::MINIMAL === $mode ) {
			return;
		}

		$settings = Settings::get();
		$nav      = Navigation::resolved();
		$has_right_sidebar = Layout::THREE === $mode && ! empty( $settings['right_sidebar']['enabled'] ) && self::right_sidebar_has_modules( $settings, $nav );

		echo '<a class="sabri-shell-skip-link" href="#sabri-shell-main-content">' . esc_html__( 'Skip to main content', 'sabri-unified-application-shell' ) . '</a>';
		echo '<span id="sabri-shell-main-content" class="sabri-shell-main-anchor" tabindex="-1"></span>';

		if ( ! empty( $settings['header']['enabled'] ) ) {
			self::render_header( $settings, $nav );
		}

		self::render_primary_nav( $nav );

		if ( $has_right_sidebar ) {
			echo '<button type="button" class="sabri-shell-context-button" data-sabri-drawer-trigger="sabri-shell-drawer-context" data-sabri-open-label="' . esc_attr__( 'Open context panel', 'sabri-unified-application-shell' ) . '" data-sabri-close-label="' . esc_attr__( 'Close context panel', 'sabri-unified-application-shell' ) . '" aria-controls="sabri-shell-drawer-context" aria-expanded="false" aria-label="' . esc_attr__( 'Open context panel', 'sabri-unified-application-shell' ) . '">' . esc_html__( 'Context', 'sabri-unified-application-shell' ) . '</button>';
		}

		if ( ! empty( $settings['left_sidebar']['enabled'] ) ) {
			self::render_left_sidebar( $settings, $nav, 'desktop' );
		}

		if ( $has_right_sidebar ) {
			self::render_right_sidebar( $settings, $nav );
		}

		echo '<div id="sabri-shell-layout-host" class="sabri-shell-layout-host" data-sabri-shell-component="layout-host" data-sabri-shell-layout-mode="' . esc_attr( $mode ) . '" hidden></div>';
	}

	/**
	 * Render mobile drawers and bottom navigation.
	 *
	 * @return void
	 */
	public static function render_shell_footer() {
		$mode = Layout::current_mode();
		if ( Layout::MINIMAL === $mode ) {
			return;
		}

		$settings = Settings::get();
		$nav      = Navigation::resolved();
		$has_right_sidebar = Layout::THREE === $mode && ! empty( $settings['right_sidebar']['enabled'] ) && self::right_sidebar_has_modules( $settings, $nav );

		if ( ! empty( $settings['mobile']['drawers'] ) ) {
			echo '<div class="sabri-shell-drawer-overlay" data-sabri-drawer-overlay hidden></div>';
			echo '<aside id="sabri-shell-drawer-nav" class="sabri-shell-drawer" aria-label="' . esc_attr__( 'Navigation menu', 'sabri-unified-application-shell' ) . '" aria-hidden="true" inert>';
			echo '<button type="button" class="sabri-shell-drawer-close" data-sabri-drawer-close aria-label="' . esc_attr__( 'Close menu', 'sabri-unified-application-shell' ) . '"><span aria-hidden="true">&times;</span></button>';
			self::render_left_sidebar( $settings, $nav, 'drawer' );
			echo '</aside>';

			if ( $has_right_sidebar ) {
				echo '<aside id="sabri-shell-drawer-context" class="sabri-shell-drawer sabri-shell-drawer-context" aria-label="' . esc_attr__( 'Context panel', 'sabri-unified-application-shell' ) . '" aria-hidden="true" inert>';
				echo '<button type="button" class="sabri-shell-drawer-close" data-sabri-drawer-close aria-label="' . esc_attr__( 'Close context panel', 'sabri-unified-application-shell' ) . '"><span aria-hidden="true">&times;</span></button>';
				self::render_right_sidebar( $settings, $nav, true );
				echo '</aside>';
			}
		}

		if ( ! empty( $settings['mobile']['bottom_nav'] ) ) {
			self::render_mobile_bottom_nav( $settings, $nav );
		}
	}

	/**
	 * Render the global header.
	 *
	 * @param array<string,mixed> $settings Settings.
	 * @param array<string,mixed> $nav Resolved nav.
	 * @return void
	 */
	private static function render_header( array $settings, array $nav ) {
		$title = ! empty( $settings['header']['platform_title'] ) ? $settings['header']['platform_title'] : __( 'Sabri Social Homeopathy', 'sabri-unified-application-shell' );

		echo '<header class="sabri-shell-header" role="banner" data-sabri-shell-component="header">';
		echo '<div class="sabri-shell-header-inner">';
		echo '<button type="button" class="sabri-shell-icon-button sabri-shell-menu-button" data-sabri-drawer-trigger="sabri-shell-drawer-nav" data-sabri-open-label="' . esc_attr__( 'Open menu', 'sabri-unified-application-shell' ) . '" data-sabri-close-label="' . esc_attr__( 'Close menu', 'sabri-unified-application-shell' ) . '" aria-controls="sabri-shell-drawer-nav" aria-expanded="false" aria-label="' . esc_attr__( 'Open menu', 'sabri-unified-application-shell' ) . '"><span aria-hidden="true">&#9776;</span></button>';
		echo '<a class="sabri-shell-brand" href="' . esc_url( home_url( '/' ) ) . '"><span class="sabri-shell-logo" aria-hidden="true">S</span><span class="sabri-shell-brand-text">' . esc_html( $title ) . '</span></a>';

		if ( ! empty( $settings['header']['search'] ) ) {
			self::render_search();
		}

		echo '<nav class="sabri-shell-header-actions" aria-label="' . esc_attr__( 'Account and platform actions', 'sabri-unified-application-shell' ) . '">';

		if ( self::can_show_create( $settings ) && ! empty( $settings['header']['create'] ) ) {
			$create_url = apply_filters( 'sabri_shell_create_url', admin_url( 'post-new.php' ) );
			echo '<a class="sabri-shell-action sabri-shell-create-action" href="' . esc_url( $create_url ) . '">' . esc_html__( 'Create', 'sabri-unified-application-shell' ) . '</a>';
		}

		if ( ! empty( $settings['header']['messages'] ) ) {
			self::render_header_action( 'messages', __( 'Messages', 'sabri-unified-application-shell' ), $nav, $settings );
		}

		if ( ! empty( $settings['header']['notifications'] ) ) {
			self::render_notifications_once( $nav, $settings );
		}

		if ( ! empty( $settings['header']['help'] ) ) {
			self::render_header_action( 'support', __( 'Help', 'sabri-unified-application-shell' ), $nav, $settings );
		}

		if ( ! empty( $settings['header']['language'] ) && Integrations::detect()['language'] ) {
			echo '<span class="sabri-shell-action sabri-shell-language">';
			echo esc_html__( 'Language', 'sabri-unified-application-shell' );
			echo '</span>';
		}

		self::render_profile_or_auth( $settings );

		echo '</nav>';
		echo '</div>';
		echo '</header>';
	}

	/**
	 * Render search form.
	 *
	 * @return void
	 */
	private static function render_search() {
		$query = get_search_query();
		echo '<form class="sabri-shell-search" role="search" method="get" action="' . esc_url( home_url( '/' ) ) . '">';
		echo '<label class="screen-reader-text" for="sabri-shell-search-field">' . esc_html__( 'Search', 'sabri-unified-application-shell' ) . '</label>';
		echo '<input id="sabri-shell-search-field" type="search" name="s" value="' . esc_attr( $query ) . '" placeholder="' . esc_attr__( 'Search', 'sabri-unified-application-shell' ) . '">';
		echo '<button type="submit" aria-label="' . esc_attr__( 'Submit search', 'sabri-unified-application-shell' ) . '"><span aria-hidden="true">&#8981;</span></button>';
		echo '</form>';
	}

	/**
	 * Render a simple resolved header action.
	 *
	 * @param string              $key Destination key.
	 * @param string              $label Label.
	 * @param array<string,mixed> $nav Resolved nav.
	 * @param array<string,mixed> $settings Settings.
	 * @return void
	 */
	private static function render_header_action( $key, $label, array $nav, array $settings ) {
		$url = self::destination_url( $key, $nav, $settings );
		if ( ! $url ) {
			return;
		}

		echo '<a class="sabri-shell-action" href="' . esc_url( $url ) . '">' . esc_html( $label ) . '</a>';
	}

	/**
	 * Render Notifications exactly once.
	 *
	 * @param array<string,mixed> $nav Resolved nav.
	 * @param array<string,mixed> $settings Settings.
	 * @return void
	 */
	private static function render_notifications_once( array $nav, array $settings ) {
		if ( self::$notifications_rendered ) {
			return;
		}

		$url = self::destination_url( 'notifications', $nav, $settings );
		if ( ! $url ) {
			return;
		}

		self::$notifications_rendered = true;
		echo '<a class="sabri-shell-action sabri-shell-notifications" data-sabri-notifications-output="header" href="' . esc_url( $url ) . '" aria-label="' . esc_attr__( 'Notifications', 'sabri-unified-application-shell' ) . '"><span aria-hidden="true">&#9679;</span><span class="screen-reader-text">' . esc_html__( 'Notifications', 'sabri-unified-application-shell' ) . '</span></a>';
	}

	/**
	 * Render profile menu or auth links.
	 *
	 * @param array<string,mixed> $settings Settings.
	 * @return void
	 */
	private static function render_profile_or_auth( array $settings ) {
		if ( empty( $settings['header']['profile'] ) ) {
			return;
		}

		if ( is_user_logged_in() ) {
			$user = wp_get_current_user();
			echo '<details class="sabri-shell-profile">';
			echo '<summary>' . esc_html( $user->display_name ? $user->display_name : __( 'Profile', 'sabri-unified-application-shell' ) ) . '</summary>';
			echo '<a href="' . esc_url( admin_url( 'profile.php' ) ) . '">' . esc_html__( 'Profile', 'sabri-unified-application-shell' ) . '</a>';
			echo '<a href="' . esc_url( wp_logout_url( home_url( '/' ) ) ) . '">' . esc_html__( 'Log out', 'sabri-unified-application-shell' ) . '</a>';
			echo '</details>';
			return;
		}

		$redirect = self::safe_login_redirect();
		echo '<a class="sabri-shell-action" href="' . esc_url( wp_login_url( $redirect ) ) . '">' . esc_html__( 'Log In', 'sabri-unified-application-shell' ) . '</a>';
		if ( get_option( 'users_can_register' ) ) {
			echo '<a class="sabri-shell-action" href="' . esc_url( wp_registration_url() ) . '">' . esc_html__( 'Sign Up', 'sabri-unified-application-shell' ) . '</a>';
		}
	}

	/**
	 * Render primary horizontal nav.
	 *
	 * @param array<string,mixed> $nav Resolved nav.
	 * @return void
	 */
	private static function render_primary_nav( array $nav ) {
		$primary_keys = array( 'home', 'news', 'founder', 'learn', 'encyclopedia', 'doctors', 'clinic', 'video_wall', 'reels', 'pdf_library', 'radar', 'ai', 'network', 'marketplace' );
		echo '<nav class="sabri-shell-primary-nav" aria-label="' . esc_attr__( 'Primary navigation', 'sabri-unified-application-shell' ) . '" data-sabri-shell-component="primary-nav">';
		echo '<ul>';
		foreach ( $primary_keys as $key ) {
			if ( empty( $nav[ $key ] ) || ! self::item_visible_to_user( $nav[ $key ] ) ) {
				continue;
			}
			self::render_nav_item( $nav[ $key ] );
		}
		echo '</ul>';
		echo '</nav>';
	}

	/**
	 * Render left sidebar or drawer content.
	 *
	 * @param array<string,mixed> $settings Settings.
	 * @param array<string,mixed> $nav Resolved nav.
	 * @param string              $variant desktop|drawer.
	 * @return void
	 */
	private static function render_left_sidebar( array $settings, array $nav, $variant ) {
		$classes = 'sabri-shell-left-sidebar';
		if ( 'drawer' === $variant ) {
			$classes .= ' sabri-shell-left-sidebar-drawer';
		}

		echo '<aside class="' . esc_attr( $classes ) . '" aria-label="' . esc_attr__( 'Sabri navigation', 'sabri-unified-application-shell' ) . '" data-sabri-shell-component="left-sidebar">';
		self::render_user_card();

		$groups = Defaults::groups();
		foreach ( $groups as $group_key => $group_label ) {
			if ( empty( $settings['left_sidebar']['groups'][ $group_key ] ) ) {
				continue;
			}

			$group_items = array_filter(
				$nav,
				static function ( $item ) use ( $group_key, $settings ) {
					return isset( $item['group'] ) && $group_key === $item['group'] && ! empty( $settings['left_sidebar']['items'][ $item['key'] ] );
				}
			);

			if ( empty( $group_items ) ) {
				continue;
			}

			echo '<section class="sabri-shell-sidebar-group">';
			echo '<h2>' . esc_html( $group_label ) . '</h2>';
			echo '<ul>';
			foreach ( $group_items as $item ) {
				if ( self::item_visible_to_user( $item ) ) {
					self::render_nav_item( $item );
				}
			}
			echo '</ul>';
			echo '</section>';
		}

		self::render_left_footer_links( $settings );
		echo '</aside>';
	}

	/**
	 * Render user or visitor card.
	 *
	 * @return void
	 */
	private static function render_user_card() {
		echo '<div class="sabri-shell-user-card">';
		if ( is_user_logged_in() ) {
			$user = wp_get_current_user();
			echo get_avatar( $user->ID, 48, '', '', array( 'class' => 'sabri-shell-avatar' ) );
			echo '<div><strong>' . esc_html( $user->display_name ) . '</strong><span>' . esc_html__( 'Signed in', 'sabri-unified-application-shell' ) . '</span></div>';
		} else {
			$redirect = self::safe_login_redirect();
			echo '<strong>' . esc_html__( 'Welcome', 'sabri-unified-application-shell' ) . '</strong>';
			echo '<p>' . esc_html__( 'Log in to access your account tools.', 'sabri-unified-application-shell' ) . '</p>';
			echo '<a href="' . esc_url( wp_login_url( $redirect ) ) . '">' . esc_html__( 'Log In', 'sabri-unified-application-shell' ) . '</a>';
			if ( get_option( 'users_can_register' ) ) {
				echo '<a href="' . esc_url( wp_registration_url() ) . '">' . esc_html__( 'Create Account', 'sabri-unified-application-shell' ) . '</a>';
			}
		}
		echo '</div>';
	}

	/**
	 * Render footer links.
	 *
	 * @param array<string,mixed> $settings Settings.
	 * @return void
	 */
	private static function render_left_footer_links( array $settings ) {
		$labels = array(
			'privacy'    => __( 'Privacy', 'sabri-unified-application-shell' ),
			'terms'      => __( 'Terms', 'sabri-unified-application-shell' ),
			'disclaimer' => __( 'Medical Disclaimer', 'sabri-unified-application-shell' ),
			'guidelines' => __( 'Community Guidelines', 'sabri-unified-application-shell' ),
			'contact'    => __( 'Contact', 'sabri-unified-application-shell' ),
			'whatsapp'   => __( 'WhatsApp', 'sabri-unified-application-shell' ),
		);

		echo '<nav class="sabri-shell-sidebar-footer" aria-label="' . esc_attr__( 'Footer links', 'sabri-unified-application-shell' ) . '">';
		foreach ( $labels as $key => $label ) {
			$url = isset( $settings['left_sidebar']['footer_mappings'][ $key ] ) ? Settings::sanitize_url( $settings['left_sidebar']['footer_mappings'][ $key ] ) : '';
			if ( $url ) {
				echo '<a href="' . esc_url( $url ) . '">' . esc_html( $label ) . '</a>';
			}
		}
		echo '</nav>';
	}

	/**
	 * Render a nav item.
	 *
	 * @param array<string,mixed> $item Item.
	 * @return void
	 */
	private static function render_nav_item( array $item ) {
		$active = Navigation::is_active_url( $item['url'] );
		echo '<li>';
		echo '<a href="' . esc_url( $item['url'] ) . '"' . ( $active ? ' aria-current="page"' : '' ) . '>';
		echo esc_html( $item['label'] );
		echo '</a>';
		echo '</li>';
	}

	/**
	 * Determine whether the right sidebar has real output for this request.
	 *
	 * @param array<string,mixed> $settings Settings.
	 * @param array<string,mixed> $nav Resolved nav.
	 * @return bool
	 */
	private static function right_sidebar_has_modules( array $settings, array $nav ) {
		$context = self::right_sidebar_context( $settings );

		if ( 'home' === $context ) {
			return self::home_right_sidebar_has_modules( $settings, $nav );
		}

		if ( 'clinic' === $context ) {
			return self::clinic_directory_sidebar_has_modules( $settings );
		}

		return self::single_clinic_sidebar_has_modules( $settings );
	}

	/**
	 * Resolve the current right sidebar context.
	 *
	 * @param array<string,mixed> $settings Settings.
	 * @return string
	 */
	private static function right_sidebar_context( array $settings ) {
		if ( function_exists( 'is_front_page' ) && is_front_page() ) {
			return 'home';
		}

		if ( self::is_clinic_directory( $settings ) ) {
			return 'clinic';
		}

		return 'single';
	}

	/**
	 * Home right sidebar availability.
	 *
	 * @param array<string,mixed> $settings Settings.
	 * @param array<string,mixed> $nav Resolved nav.
	 * @return bool
	 */
	private static function home_right_sidebar_has_modules( array $settings, array $nav ) {
		$modules = $settings['right_sidebar']['home_modules'];

		if ( ! empty( $modules['founder'] ) && ! empty( $nav['founder']['url'] ) ) {
			return true;
		}
		if ( ! empty( $modules['announcement'] ) && ! empty( $settings['right_sidebar']['announcement'] ) ) {
			return true;
		}
		if ( ! empty( $modules['network'] ) && ! empty( $nav['network']['url'] ) && ! empty( Integrations::detect()['network'] ) ) {
			return true;
		}
		if ( ! empty( $modules['latest_posts'] ) && self::has_posts_for_type( 'post' ) ) {
			return true;
		}
		if ( ! empty( $modules['doctors'] ) && self::has_verified_doctors() ) {
			return true;
		}
		if ( ! empty( $modules['marketplace'] ) && post_type_exists( 'product' ) && self::has_posts_for_type( 'product' ) ) {
			return true;
		}
		if ( ! empty( $modules['research'] ) && post_type_exists( 'research' ) && self::has_posts_for_type( 'research' ) ) {
			return true;
		}
		if ( ! empty( $modules['quick_access'] ) && self::has_quick_access( $nav ) ) {
			return true;
		}

		return self::has_missing_admin_modules( $settings, 'home' );
	}

	/**
	 * Clinic directory right sidebar availability.
	 *
	 * @param array<string,mixed> $settings Settings.
	 * @return bool
	 */
	private static function clinic_directory_sidebar_has_modules( array $settings ) {
		$modules = $settings['right_sidebar']['clinic_modules'];

		if ( ! empty( $modules['finder'] ) || ! empty( $modules['filters'] ) ) {
			return true;
		}
		if ( ! empty( $modules['doctors'] ) && self::has_verified_doctors() ) {
			return true;
		}
		if ( ! empty( $modules['appointments'] ) && ! empty( Integrations::detect()['appointments'] ) && ! empty( $settings['integrations']['urls']['appointments'] ) ) {
			return true;
		}
		if ( ! empty( $modules['emergency'] ) && ! empty( $settings['right_sidebar']['emergency_notice'] ) ) {
			return true;
		}
		if ( ! empty( $modules['whatsapp'] ) && ! empty( $settings['integrations']['urls']['whatsapp'] ) ) {
			return true;
		}

		return self::has_missing_admin_modules( $settings, 'clinic' );
	}

	/**
	 * Single clinic right sidebar availability.
	 *
	 * @param array<string,mixed> $settings Settings.
	 * @return bool
	 */
	private static function single_clinic_sidebar_has_modules( array $settings ) {
		$modules = $settings['right_sidebar']['single_modules'];
		$post_id = function_exists( 'get_the_ID' ) ? absint( get_the_ID() ) : 0;

		if ( ! $post_id ) {
			return ! empty( $modules['safety'] ) || self::has_missing_admin_modules( $settings, 'single' );
		}

		if ( ! empty( $modules['profile'] ) && self::has_single_profile_data( $post_id ) ) {
			return true;
		}
		if ( ! empty( $modules['appointment'] ) && ! empty( Integrations::detect()['appointments'] ) && ! empty( $settings['integrations']['urls']['appointments'] ) ) {
			return true;
		}
		if ( ! empty( $modules['message'] ) && ! empty( Integrations::detect()['messages'] ) && ! empty( $settings['integrations']['urls']['messages'] ) ) {
			return true;
		}
		if ( ! empty( $modules['contact'] ) && self::has_public_contact_data( $post_id ) ) {
			return true;
		}
		if ( ! empty( $modules['reviews'] ) && function_exists( 'get_comments_number' ) && get_comments_number( $post_id ) > 0 ) {
			return true;
		}
		if ( ! empty( $modules['safety'] ) ) {
			return true;
		}

		return self::has_missing_admin_modules( $settings, 'single' );
	}

	/**
	 * Whether missing module notices may render for administrators.
	 *
	 * @param array<string,mixed> $settings Settings.
	 * @return bool
	 */
	private static function should_render_missing_admin_notice( array $settings ) {
		return empty( $settings['right_sidebar']['hide_missing'] ) && is_user_logged_in() && current_user_can( 'manage_options' );
	}

	/**
	 * Whether missing module notices have real content.
	 *
	 * @param array<string,mixed> $settings Settings.
	 * @param string              $context Context.
	 * @return bool
	 */
	private static function has_missing_admin_modules( array $settings, $context ) {
		return self::should_render_missing_admin_notice( $settings ) && ! empty( self::right_sidebar_missing_modules( $settings, $context ) );
	}

	/**
	 * Check for public posts of a type.
	 *
	 * @param string $post_type Post type.
	 * @return bool
	 */
	private static function has_posts_for_type( $post_type ) {
		$posts = get_posts(
			array(
				'post_type'      => $post_type,
				'post_status'    => 'publish',
				'posts_per_page' => 1,
				'no_found_rows'  => true,
				'fields'         => 'ids',
			)
		);

		return ! empty( $posts );
	}

	/**
	 * Check whether verified doctors exist.
	 *
	 * @return bool
	 */
	private static function has_verified_doctors() {
		$roles = Integrations::detect()['verified_doctor_roles'];
		if ( empty( $roles ) ) {
			return false;
		}

		$users = get_users(
			array(
				'role__in' => $roles,
				'number'   => 1,
				'fields'   => 'ID',
			)
		);

		return ! empty( $users );
	}

	/**
	 * Check quick access availability.
	 *
	 * @param array<string,mixed> $nav Resolved nav.
	 * @return bool
	 */
	private static function has_quick_access( array $nav ) {
		foreach ( array( 'clinic', 'doctors', 'appointments', 'encyclopedia', 'support' ) as $key ) {
			if ( ! empty( $nav[ $key ] ) && self::item_visible_to_user( $nav[ $key ] ) ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Check single profile fields.
	 *
	 * @param int $post_id Post ID.
	 * @return bool
	 */
	private static function has_single_profile_data( $post_id ) {
		if ( get_the_title( $post_id ) ) {
			return true;
		}

		foreach ( array( 'sabri_public_fee', 'sabri_public_timings', 'sabri_public_languages', 'sabri_public_specialty' ) as $field ) {
			if ( '' !== (string) get_post_meta( $post_id, $field, true ) ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Check single public contact fields.
	 *
	 * @param int $post_id Post ID.
	 * @return bool
	 */
	private static function has_public_contact_data( $post_id ) {
		foreach ( array( 'sabri_public_phone', 'sabri_public_whatsapp' ) as $field ) {
			if ( '' !== (string) get_post_meta( $post_id, $field, true ) ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Render right sidebar.
	 *
	 * @param array<string,mixed> $settings Settings.
	 * @param array<string,mixed> $nav Resolved nav.
	 * @param bool                $inside_drawer Whether rendering in drawer.
	 * @return void
	 */
	private static function render_right_sidebar( array $settings, array $nav, $inside_drawer = false ) {
		$classes = 'sabri-shell-right-sidebar';
		if ( $inside_drawer ) {
			$classes .= ' sabri-shell-right-sidebar-drawer';
		}

		$context = self::right_sidebar_context( $settings );
		echo '<aside class="' . esc_attr( $classes ) . '" aria-label="' . esc_attr__( 'Context sidebar', 'sabri-unified-application-shell' ) . '" data-sabri-shell-component="right-sidebar" data-sabri-right-context="' . esc_attr( $context ) . '">';

		if ( 'home' === $context ) {
			self::render_home_right_sidebar( $settings, $nav );
		} elseif ( 'clinic' === $context ) {
			self::render_clinic_directory_sidebar( $settings );
		} else {
			self::render_single_clinic_sidebar( $settings );
		}

		if ( self::should_render_missing_admin_notice( $settings ) ) {
			self::render_missing_admin_panel( $settings, $context );
		}

		echo '</aside>';
	}

	/**
	 * Render home right sidebar modules using real data only.
	 *
	 * @param array<string,mixed> $settings Settings.
	 * @param array<string,mixed> $nav Resolved nav.
	 * @return void
	 */
	private static function render_home_right_sidebar( array $settings, array $nav ) {
		$modules = $settings['right_sidebar']['home_modules'];

		if ( ! empty( $modules['founder'] ) && ! empty( $nav['founder']['url'] ) ) {
			self::render_panel( __( 'Founder', 'sabri-unified-application-shell' ), '<p><a href="' . esc_url( $nav['founder']['url'] ) . '">' . esc_html( $nav['founder']['label'] ) . '</a></p>', 'home-founder' );
		}

		if ( ! empty( $modules['announcement'] ) && ! empty( $settings['right_sidebar']['announcement'] ) ) {
			self::render_panel( __( 'Announcement', 'sabri-unified-application-shell' ), '<p>' . esc_html( $settings['right_sidebar']['announcement'] ) . '</p>', 'home-announcement' );
		}

		if ( ! empty( $modules['network'] ) && ! empty( $nav['network']['url'] ) && ! empty( Integrations::detect()['network'] ) ) {
			self::render_panel( __( 'Network', 'sabri-unified-application-shell' ), '<p><a href="' . esc_url( $nav['network']['url'] ) . '">' . esc_html__( 'Open Network', 'sabri-unified-application-shell' ) . '</a></p>', 'home-network' );
		}

		if ( ! empty( $modules['latest_posts'] ) ) {
			self::render_latest_posts_panel( 'home-latest-posts' );
		}

		if ( ! empty( $modules['doctors'] ) ) {
			self::render_verified_doctors_panel();
		}

		if ( ! empty( $modules['marketplace'] ) && post_type_exists( 'product' ) ) {
			self::render_post_type_panel( __( 'Marketplace', 'sabri-unified-application-shell' ), 'product', 'home-marketplace' );
		}

		if ( ! empty( $modules['research'] ) && post_type_exists( 'research' ) ) {
			self::render_post_type_panel( __( 'Research', 'sabri-unified-application-shell' ), 'research', 'home-research' );
		}

		if ( ! empty( $modules['quick_access'] ) ) {
			self::render_quick_access_panel( $nav );
		}
	}

	/**
	 * Render an administrator-only notice for enabled modules with missing real data.
	 *
	 * @param array<string,mixed> $settings Settings.
	 * @param string              $context Context.
	 * @return void
	 */
	private static function render_missing_admin_panel( array $settings, $context ) {
		if ( ! self::should_render_missing_admin_notice( $settings ) ) {
			return;
		}

		$missing = self::right_sidebar_missing_modules( $settings, $context );
		if ( empty( $missing ) ) {
			return;
		}

		self::render_panel(
			__( 'Missing Modules', 'sabri-unified-application-shell' ),
			'<p>' . esc_html__( 'Enabled modules hidden because real data or integrations are unavailable:', 'sabri-unified-application-shell' ) . ' ' . esc_html( implode( ', ', $missing ) ) . '</p>',
			'missing-admin'
		);
	}

	/**
	 * List enabled modules that do not have real public data.
	 *
	 * @param array<string,mixed> $settings Settings.
	 * @param string              $context Context.
	 * @return array<int,string>
	 */
	private static function right_sidebar_missing_modules( array $settings, $context ) {
		$missing = array();
		if ( 'clinic' === $context ) {
			$modules = $settings['right_sidebar']['clinic_modules'];
			if ( ! empty( $modules['doctors'] ) && ! self::has_verified_doctors() ) {
				$missing[] = __( 'verified doctors', 'sabri-unified-application-shell' );
			}
			if ( ! empty( $modules['appointments'] ) && ( empty( Integrations::detect()['appointments'] ) || empty( $settings['integrations']['urls']['appointments'] ) ) ) {
				$missing[] = __( 'appointments', 'sabri-unified-application-shell' );
			}
			if ( ! empty( $modules['whatsapp'] ) && empty( $settings['integrations']['urls']['whatsapp'] ) ) {
				$missing[] = __( 'WhatsApp', 'sabri-unified-application-shell' );
			}
		} elseif ( 'single' === $context ) {
			$modules = $settings['right_sidebar']['single_modules'];
			$post_id = function_exists( 'get_the_ID' ) ? absint( get_the_ID() ) : 0;
			if ( ! empty( $modules['profile'] ) && ( ! $post_id || ! self::has_single_profile_data( $post_id ) ) ) {
				$missing[] = __( 'profile data', 'sabri-unified-application-shell' );
			}
			if ( ! empty( $modules['appointment'] ) && ( empty( Integrations::detect()['appointments'] ) || empty( $settings['integrations']['urls']['appointments'] ) ) ) {
				$missing[] = __( 'appointment integration', 'sabri-unified-application-shell' );
			}
			if ( ! empty( $modules['message'] ) && ( empty( Integrations::detect()['messages'] ) || empty( $settings['integrations']['urls']['messages'] ) ) ) {
				$missing[] = __( 'message integration', 'sabri-unified-application-shell' );
			}
			if ( ! empty( $modules['contact'] ) && ( ! $post_id || ! self::has_public_contact_data( $post_id ) ) ) {
				$missing[] = __( 'public contact fields', 'sabri-unified-application-shell' );
			}
			if ( ! empty( $modules['reviews'] ) && ( ! $post_id || ! function_exists( 'get_comments_number' ) || get_comments_number( $post_id ) < 1 ) ) {
				$missing[] = __( 'public reviews', 'sabri-unified-application-shell' );
			}
		} else {
			$modules = $settings['right_sidebar']['home_modules'];
			if ( ! empty( $modules['doctors'] ) && ! self::has_verified_doctors() ) {
				$missing[] = __( 'verified doctors', 'sabri-unified-application-shell' );
			}
			if ( ! empty( $modules['marketplace'] ) && ( ! post_type_exists( 'product' ) || ! self::has_posts_for_type( 'product' ) ) ) {
				$missing[] = __( 'marketplace items', 'sabri-unified-application-shell' );
			}
			if ( ! empty( $modules['research'] ) && ( ! post_type_exists( 'research' ) || ! self::has_posts_for_type( 'research' ) ) ) {
				$missing[] = __( 'research items', 'sabri-unified-application-shell' );
			}
		}

		return $missing;
	}

	/**
	 * Render clinic directory sidebar.
	 *
	 * @param array<string,mixed> $settings Settings.
	 * @return void
	 */
	private static function render_clinic_directory_sidebar( array $settings ) {
		$modules = $settings['right_sidebar']['clinic_modules'];

		if ( ! empty( $modules['finder'] ) ) {
			echo '<section class="sabri-shell-panel" data-sabri-right-module="clinic-finder"><h2>' . esc_html__( 'Doctor Finder', 'sabri-unified-application-shell' ) . '</h2>';
			echo '<form method="get" action="' . esc_url( get_permalink() ) . '">';
			echo '<label><span>' . esc_html__( 'Search doctors', 'sabri-unified-application-shell' ) . '</span><input type="search" name="doctor_search" value="' . esc_attr( isset( $_GET['doctor_search'] ) ? sanitize_text_field( wp_unslash( $_GET['doctor_search'] ) ) : '' ) . '"></label>';
			echo '<button type="submit">' . esc_html__( 'Search', 'sabri-unified-application-shell' ) . '</button>';
			echo '</form></section>';
		}

		if ( ! empty( $modules['filters'] ) ) {
			echo '<section class="sabri-shell-panel" data-sabri-right-module="clinic-filters"><h2>' . esc_html__( 'Filters', 'sabri-unified-application-shell' ) . '</h2>';
			echo '<form method="get" action="' . esc_url( get_permalink() ) . '">';
			echo '<label><span>' . esc_html__( 'Country', 'sabri-unified-application-shell' ) . '</span><input type="text" name="country" value="' . esc_attr( isset( $_GET['country'] ) ? sanitize_text_field( wp_unslash( $_GET['country'] ) ) : '' ) . '"></label>';
			echo '<label><span>' . esc_html__( 'Language', 'sabri-unified-application-shell' ) . '</span><input type="text" name="language" value="' . esc_attr( isset( $_GET['language'] ) ? sanitize_text_field( wp_unslash( $_GET['language'] ) ) : '' ) . '"></label>';
			echo '<label><span>' . esc_html__( 'Specialty', 'sabri-unified-application-shell' ) . '</span><input type="text" name="specialty" value="' . esc_attr( isset( $_GET['specialty'] ) ? sanitize_text_field( wp_unslash( $_GET['specialty'] ) ) : '' ) . '"></label>';
			echo '<button type="submit">' . esc_html__( 'Apply', 'sabri-unified-application-shell' ) . '</button>';
			echo '</form></section>';
		}

		if ( ! empty( $modules['doctors'] ) ) {
			self::render_verified_doctors_panel( 'clinic-doctors' );
		}

		if ( ! empty( $modules['appointments'] ) && ! empty( Integrations::detect()['appointments'] ) && ! empty( $settings['integrations']['urls']['appointments'] ) ) {
			self::render_panel( __( 'Appointment Help', 'sabri-unified-application-shell' ), '<p><a href="' . esc_url( $settings['integrations']['urls']['appointments'] ) . '">' . esc_html__( 'Open appointment support', 'sabri-unified-application-shell' ) . '</a></p>', 'clinic-appointments' );
		}

		if ( ! empty( $modules['emergency'] ) && ! empty( $settings['right_sidebar']['emergency_notice'] ) ) {
			self::render_panel( __( 'Emergency Notice', 'sabri-unified-application-shell' ), '<p>' . esc_html( $settings['right_sidebar']['emergency_notice'] ) . '</p>', 'clinic-emergency' );
		}

		if ( ! empty( $modules['whatsapp'] ) && ! empty( $settings['integrations']['urls']['whatsapp'] ) ) {
			self::render_panel( __( 'WhatsApp Help', 'sabri-unified-application-shell' ), '<p><a href="' . esc_url( $settings['integrations']['urls']['whatsapp'] ) . '">' . esc_html__( 'Open WhatsApp help', 'sabri-unified-application-shell' ) . '</a></p>', 'clinic-whatsapp' );
		}
	}

	/**
	 * Render single doctor or clinic sidebar.
	 *
	 * @param array<string,mixed> $settings Settings.
	 * @return void
	 */
	private static function render_single_clinic_sidebar( array $settings ) {
		$modules = $settings['right_sidebar']['single_modules'];
		if ( ! function_exists( 'get_the_ID' ) || ! get_the_ID() ) {
			if ( ! empty( $modules['safety'] ) ) {
				self::render_panel( __( 'Medical Safety', 'sabri-unified-application-shell' ), '<p>' . esc_html__( 'This page is educational and is not a replacement for urgent medical care.', 'sabri-unified-application-shell' ) . '</p>', 'single-safety' );
			}
			return;
		}

		$post_id = get_the_ID();
		$title   = get_the_title( $post_id );
		$fields  = array(
			'fee'       => get_post_meta( $post_id, 'sabri_public_fee', true ),
			'timings'   => get_post_meta( $post_id, 'sabri_public_timings', true ),
			'languages' => get_post_meta( $post_id, 'sabri_public_languages', true ),
			'specialty' => get_post_meta( $post_id, 'sabri_public_specialty', true ),
		);

		if ( ! empty( $modules['profile'] ) && self::has_single_profile_data( $post_id ) ) {
			echo '<section class="sabri-shell-panel" data-sabri-right-module="single-profile"><h2>' . esc_html( $title ) . '</h2><dl>';
			foreach ( $fields as $label => $value ) {
				if ( '' === (string) $value ) {
					continue;
				}
				echo '<dt>' . esc_html( ucfirst( $label ) ) . '</dt><dd>' . esc_html( $value ) . '</dd>';
			}
			echo '</dl></section>';
		}

		if ( ! empty( $modules['appointment'] ) && ! empty( Integrations::detect()['appointments'] ) && ! empty( $settings['integrations']['urls']['appointments'] ) ) {
			self::render_panel( __( 'Appointment', 'sabri-unified-application-shell' ), '<p><a href="' . esc_url( $settings['integrations']['urls']['appointments'] ) . '">' . esc_html__( 'Request an appointment', 'sabri-unified-application-shell' ) . '</a></p>', 'single-appointment' );
		}

		if ( ! empty( $modules['message'] ) && ! empty( Integrations::detect()['messages'] ) && ! empty( $settings['integrations']['urls']['messages'] ) ) {
			self::render_panel( __( 'Message', 'sabri-unified-application-shell' ), '<p><a href="' . esc_url( $settings['integrations']['urls']['messages'] ) . '">' . esc_html__( 'Open messages', 'sabri-unified-application-shell' ) . '</a></p>', 'single-message' );
		}

		if ( ! empty( $modules['contact'] ) && self::has_public_contact_data( $post_id ) ) {
			$contact_html = '<dl>';
			foreach ( array( 'phone' => 'sabri_public_phone', 'whatsapp' => 'sabri_public_whatsapp' ) as $label => $field ) {
				$value = get_post_meta( $post_id, $field, true );
				if ( '' !== (string) $value ) {
					$contact_html .= '<dt>' . esc_html( ucfirst( $label ) ) . '</dt><dd>' . esc_html( $value ) . '</dd>';
				}
			}
			$contact_html .= '</dl>';
			self::render_panel( __( 'Public Contact', 'sabri-unified-application-shell' ), $contact_html, 'single-contact' );
		}

		if ( ! empty( $modules['reviews'] ) && function_exists( 'get_comments_number' ) && get_comments_number( $post_id ) > 0 ) {
			self::render_panel( __( 'Reviews', 'sabri-unified-application-shell' ), '<p><a href="' . esc_url( get_permalink( $post_id ) ) . '#comments">' . esc_html__( 'View public reviews', 'sabri-unified-application-shell' ) . '</a></p>', 'single-reviews' );
		}

		if ( ! empty( $modules['safety'] ) ) {
			self::render_panel( __( 'Medical Safety', 'sabri-unified-application-shell' ), '<p>' . esc_html__( 'For emergencies, contact local emergency services. Do not share private patient data through public profiles.', 'sabri-unified-application-shell' ) . '</p>', 'single-safety' );
		}
	}

	/**
	 * Render latest posts panel.
	 *
	 * @return void
	 */
	private static function render_latest_posts_panel( $module = 'home-latest-posts' ) {
		$posts = get_posts(
			array(
				'post_type'      => 'post',
				'post_status'    => 'publish',
				'posts_per_page' => 5,
				'no_found_rows'  => true,
			)
		);

		if ( empty( $posts ) ) {
			return;
		}

		$html = '<ul>';
		foreach ( $posts as $post ) {
			$html .= '<li><a href="' . esc_url( get_permalink( $post ) ) . '">' . esc_html( get_the_title( $post ) ) . '</a></li>';
		}
		$html .= '</ul>';

		self::render_panel( __( 'Latest Posts', 'sabri-unified-application-shell' ), $html, $module );
	}

	/**
	 * Render verified doctors from real roles only.
	 *
	 * @return void
	 */
	private static function render_verified_doctors_panel( $module = 'home-doctors' ) {
		$roles = Integrations::detect()['verified_doctor_roles'];
		if ( empty( $roles ) ) {
			return;
		}

		$users = get_users(
			array(
				'role__in' => $roles,
				'number'   => 5,
				'fields'   => array( 'ID', 'display_name' ),
			)
		);

		if ( empty( $users ) ) {
			return;
		}

		$html = '<ul>';
		foreach ( $users as $user ) {
			$html .= '<li>' . esc_html( $user->display_name ) . '</li>';
		}
		$html .= '</ul>';

		self::render_panel( __( 'Verified Doctors', 'sabri-unified-application-shell' ), $html, $module );
	}

	/**
	 * Render real latest items for a post type.
	 *
	 * @param string $title Panel title.
	 * @param string $post_type Post type.
	 * @return void
	 */
	private static function render_post_type_panel( $title, $post_type, $module ) {
		$items = get_posts(
			array(
				'post_type'      => $post_type,
				'post_status'    => 'publish',
				'posts_per_page' => 4,
				'no_found_rows'  => true,
			)
		);

		if ( empty( $items ) ) {
			return;
		}

		$html = '<ul>';
		foreach ( $items as $item ) {
			$html .= '<li><a href="' . esc_url( get_permalink( $item ) ) . '">' . esc_html( get_the_title( $item ) ) . '</a></li>';
		}
		$html .= '</ul>';

		self::render_panel( $title, $html, $module );
	}

	/**
	 * Render quick access panel.
	 *
	 * @param array<string,mixed> $nav Resolved nav.
	 * @return void
	 */
	private static function render_quick_access_panel( array $nav ) {
		$keys = array( 'clinic', 'doctors', 'appointments', 'encyclopedia', 'support' );
		$html = '<ul>';
		$count = 0;
		foreach ( $keys as $key ) {
			if ( empty( $nav[ $key ] ) || ! self::item_visible_to_user( $nav[ $key ] ) ) {
				continue;
			}
			$html .= '<li><a href="' . esc_url( $nav[ $key ]['url'] ) . '">' . esc_html( $nav[ $key ]['label'] ) . '</a></li>';
			$count++;
		}
		$html .= '</ul>';

		if ( $count ) {
			self::render_panel( __( 'Quick Access', 'sabri-unified-application-shell' ), $html, 'home-quick-access' );
		}
	}

	/**
	 * Render mobile bottom nav.
	 *
	 * @param array<string,mixed> $settings Settings.
	 * @param array<string,mixed> $nav Resolved nav.
	 * @return void
	 */
	private static function render_mobile_bottom_nav( array $settings, array $nav ) {
		$third_key = 'doctors';
		if ( 'create' === $settings['mobile']['create_or_doctors'] || ( 'auto' === $settings['mobile']['create_or_doctors'] && self::can_show_create( $settings ) ) ) {
			$third_key = 'create';
		}

		echo '<nav class="sabri-shell-bottom-nav" aria-label="' . esc_attr__( 'Mobile navigation', 'sabri-unified-application-shell' ) . '" data-sabri-shell-component="bottom-nav">';
		self::render_bottom_link( 'home', __( 'Home', 'sabri-unified-application-shell' ), isset( $nav['home']['url'] ) ? $nav['home']['url'] : home_url( '/' ) );
		self::render_bottom_link( 'encyclopedia', __( 'Encyclopedia', 'sabri-unified-application-shell' ), isset( $nav['encyclopedia']['url'] ) ? $nav['encyclopedia']['url'] : '' );
		if ( 'create' === $third_key ) {
			self::render_bottom_link( 'create', $settings['mobile']['create_label'], apply_filters( 'sabri_shell_create_url', admin_url( 'post-new.php' ) ) );
		} else {
			self::render_bottom_link( 'doctors', __( 'Doctors', 'sabri-unified-application-shell' ), isset( $nav['doctors']['url'] ) ? $nav['doctors']['url'] : '' );
		}
		self::render_bottom_link( 'messages', __( 'Messages', 'sabri-unified-application-shell' ), self::destination_url( 'messages', $nav, $settings ) );
		echo '<button type="button" class="sabri-shell-bottom-item" data-sabri-drawer-trigger="sabri-shell-drawer-nav" data-sabri-open-label="' . esc_attr__( 'Open menu', 'sabri-unified-application-shell' ) . '" data-sabri-close-label="' . esc_attr__( 'Close menu', 'sabri-unified-application-shell' ) . '" aria-controls="sabri-shell-drawer-nav" aria-expanded="false" aria-label="' . esc_attr__( 'Open menu', 'sabri-unified-application-shell' ) . '"><span aria-hidden="true">&#9776;</span><span>' . esc_html( $settings['mobile']['menu_label'] ) . '</span></button>';
		echo '</nav>';
	}

	/**
	 * Render one bottom nav link.
	 *
	 * @param string $key Key.
	 * @param string $label Label.
	 * @param string $url URL.
	 * @return void
	 */
	private static function render_bottom_link( $key, $label, $url ) {
		if ( ! $url ) {
			echo '<span class="sabri-shell-bottom-item sabri-shell-bottom-item-disabled"><span>' . esc_html( $label ) . '</span></span>';
			return;
		}

		$active = Navigation::is_active_url( $url );
		echo '<a class="sabri-shell-bottom-item" href="' . esc_url( $url ) . '"' . ( $active ? ' aria-current="page"' : '' ) . '><span aria-hidden="true">' . esc_html( strtoupper( substr( $key, 0, 1 ) ) ) . '</span><span>' . esc_html( $label ) . '</span></a>';
	}

	/**
	 * Render a reusable panel.
	 *
	 * @param string $title Title.
	 * @param string $html Escaped HTML body.
	 * @return void
	 */
	private static function render_panel( $title, $html, $module = '' ) {
		echo '<section class="sabri-shell-panel"' . ( $module ? ' data-sabri-right-module="' . esc_attr( $module ) . '"' : '' ) . '>';
		echo '<h2>' . esc_html( $title ) . '</h2>';
		echo wp_kses_post( $html );
		echo '</section>';
	}

	/**
	 * Resolve destination URL with integration URL fallback.
	 *
	 * @param string              $key Key.
	 * @param array<string,mixed> $nav Nav.
	 * @param array<string,mixed> $settings Settings.
	 * @return string
	 */
	private static function destination_url( $key, array $nav, array $settings ) {
		if ( ! empty( $nav[ $key ]['url'] ) ) {
			return $nav[ $key ]['url'];
		}

		if ( ! empty( $settings['integrations']['urls'][ $key ] ) ) {
			return Settings::sanitize_url( $settings['integrations']['urls'][ $key ] );
		}

		return '';
	}

	/**
	 * Whether an item is visible to the current user.
	 *
	 * @param array<string,mixed> $item Item.
	 * @return bool
	 */
	private static function item_visible_to_user( array $item ) {
		$destinations = Defaults::destinations();
		$key          = isset( $item['key'] ) ? $item['key'] : '';
		$visibility   = isset( $destinations[ $key ]['visibility'] ) ? $destinations[ $key ]['visibility'] : 'public';

		if ( 'logged_in' === $visibility && ! is_user_logged_in() ) {
			return false;
		}

		return true;
	}

	/**
	 * Whether Create can render.
	 *
	 * @param array<string,mixed> $settings Settings.
	 * @return bool
	 */
	private static function can_show_create( array $settings ) {
		if ( ! is_user_logged_in() || ! current_user_can( 'edit_posts' ) ) {
			return false;
		}

		$user  = wp_get_current_user();
		$roles = is_array( $user->roles ) ? $user->roles : array();

		return (bool) array_intersect( $roles, $settings['header']['allowed_roles'] );
	}

	/**
	 * Safe login redirect using validated referrer/request URI/home fallback.
	 *
	 * @return string
	 */
	private static function safe_login_redirect() {
		$home     = home_url( '/' );
		$referrer = wp_get_referer();
		if ( $referrer ) {
			return wp_validate_redirect( $referrer, $home );
		}

		$request_uri = isset( $_SERVER['REQUEST_URI'] ) ? wp_unslash( $_SERVER['REQUEST_URI'] ) : '';
		if ( is_string( $request_uri ) && 0 === strpos( $request_uri, '/' ) && 0 !== strpos( $request_uri, '//' ) ) {
			return wp_validate_redirect( home_url( $request_uri ), $home );
		}

		return $home;
	}

	/**
	 * Whether current page is the configured clinic directory.
	 *
	 * @param array<string,mixed> $settings Settings.
	 * @return bool
	 */
	private static function is_clinic_directory( array $settings ) {
		$page_id = Layout::current_page_id();
		return $page_id && ! empty( $settings['layout']['worldwide_clinic_page_id'] ) && absint( $settings['layout']['worldwide_clinic_page_id'] ) === $page_id;
	}
}
