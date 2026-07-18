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

		echo '<a class="sabri-shell-skip-link" href="#sabri-shell-main-content">' . esc_html__( 'Skip to main content', 'sabri-unified-application-shell' ) . '</a>';
		echo '<span id="sabri-shell-main-content" class="sabri-shell-main-anchor" tabindex="-1"></span>';

		if ( ! empty( $settings['header']['enabled'] ) ) {
			self::render_header( $settings, $nav );
		}

		self::render_primary_nav( $nav );

		if ( ! empty( $settings['left_sidebar']['enabled'] ) ) {
			self::render_left_sidebar( $settings, $nav, 'desktop' );
		}

		if ( Layout::THREE === $mode && ! empty( $settings['right_sidebar']['enabled'] ) ) {
			self::render_right_sidebar( $settings, $nav );
		}
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

		if ( ! empty( $settings['mobile']['drawers'] ) ) {
			echo '<div class="sabri-shell-drawer-overlay" data-sabri-drawer-overlay hidden></div>';
			echo '<aside id="sabri-shell-drawer-nav" class="sabri-shell-drawer" aria-label="' . esc_attr__( 'Navigation menu', 'sabri-unified-application-shell' ) . '" aria-hidden="true" inert>';
			echo '<button type="button" class="sabri-shell-drawer-close" data-sabri-drawer-close aria-label="' . esc_attr__( 'Close menu', 'sabri-unified-application-shell' ) . '"><span aria-hidden="true">&times;</span></button>';
			self::render_left_sidebar( $settings, $nav, 'drawer' );
			echo '</aside>';

			if ( Layout::THREE === $mode && ! empty( $settings['right_sidebar']['enabled'] ) ) {
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
		echo '<button type="button" class="sabri-shell-icon-button sabri-shell-menu-button" data-sabri-drawer-trigger="sabri-shell-drawer-nav" aria-controls="sabri-shell-drawer-nav" aria-expanded="false" aria-label="' . esc_attr__( 'Open menu', 'sabri-unified-application-shell' ) . '"><span aria-hidden="true">&#9776;</span></button>';
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

		echo '<aside class="' . esc_attr( $classes ) . '" aria-label="' . esc_attr__( 'Context sidebar', 'sabri-unified-application-shell' ) . '" data-sabri-shell-component="right-sidebar">';

		if ( function_exists( 'is_front_page' ) && is_front_page() ) {
			self::render_home_right_sidebar( $settings, $nav );
		} elseif ( self::is_clinic_directory( $settings ) ) {
			self::render_clinic_directory_sidebar( $settings );
		} else {
			self::render_single_clinic_sidebar( $settings );
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
			self::render_panel( __( 'Founder', 'sabri-unified-application-shell' ), '<p><a href="' . esc_url( $nav['founder']['url'] ) . '">' . esc_html( $nav['founder']['label'] ) . '</a></p>' );
		}

		if ( ! empty( $modules['announcement'] ) && ! empty( $settings['right_sidebar']['announcement'] ) ) {
			self::render_panel( __( 'Announcement', 'sabri-unified-application-shell' ), '<p>' . esc_html( $settings['right_sidebar']['announcement'] ) . '</p>' );
		}

		if ( ! empty( $modules['network'] ) && ! empty( $nav['network']['url'] ) && ! empty( Integrations::detect()['network'] ) ) {
			self::render_panel( __( 'Network', 'sabri-unified-application-shell' ), '<p><a href="' . esc_url( $nav['network']['url'] ) . '">' . esc_html__( 'Open Network', 'sabri-unified-application-shell' ) . '</a></p>' );
		}

		if ( ! empty( $modules['latest_posts'] ) ) {
			self::render_latest_posts_panel();
		}

		if ( ! empty( $modules['doctors'] ) ) {
			self::render_verified_doctors_panel();
		}

		if ( ! empty( $modules['marketplace'] ) && post_type_exists( 'product' ) ) {
			self::render_post_type_panel( __( 'Marketplace', 'sabri-unified-application-shell' ), 'product' );
		}

		if ( ! empty( $modules['research'] ) && post_type_exists( 'research' ) ) {
			self::render_post_type_panel( __( 'Research', 'sabri-unified-application-shell' ), 'research' );
		}

		if ( ! empty( $modules['quick_access'] ) ) {
			self::render_quick_access_panel( $nav );
		}
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
			echo '<section class="sabri-shell-panel"><h2>' . esc_html__( 'Doctor Finder', 'sabri-unified-application-shell' ) . '</h2>';
			echo '<form method="get" action="' . esc_url( get_permalink() ) . '">';
			echo '<label><span>' . esc_html__( 'Search doctors', 'sabri-unified-application-shell' ) . '</span><input type="search" name="doctor_search" value="' . esc_attr( isset( $_GET['doctor_search'] ) ? sanitize_text_field( wp_unslash( $_GET['doctor_search'] ) ) : '' ) . '"></label>';
			if ( ! empty( $modules['filters'] ) ) {
				echo '<label><span>' . esc_html__( 'Country', 'sabri-unified-application-shell' ) . '</span><input type="text" name="country" value="' . esc_attr( isset( $_GET['country'] ) ? sanitize_text_field( wp_unslash( $_GET['country'] ) ) : '' ) . '"></label>';
				echo '<label><span>' . esc_html__( 'Language', 'sabri-unified-application-shell' ) . '</span><input type="text" name="language" value="' . esc_attr( isset( $_GET['language'] ) ? sanitize_text_field( wp_unslash( $_GET['language'] ) ) : '' ) . '"></label>';
				echo '<label><span>' . esc_html__( 'Specialty', 'sabri-unified-application-shell' ) . '</span><input type="text" name="specialty" value="' . esc_attr( isset( $_GET['specialty'] ) ? sanitize_text_field( wp_unslash( $_GET['specialty'] ) ) : '' ) . '"></label>';
			}
			echo '<button type="submit">' . esc_html__( 'Search', 'sabri-unified-application-shell' ) . '</button>';
			echo '</form></section>';
		}

		if ( ! empty( $modules['appointments'] ) && ! empty( Integrations::detect()['appointments'] ) && ! empty( $settings['integrations']['urls']['appointments'] ) ) {
			self::render_panel( __( 'Appointment Help', 'sabri-unified-application-shell' ), '<p><a href="' . esc_url( $settings['integrations']['urls']['appointments'] ) . '">' . esc_html__( 'Open appointment support', 'sabri-unified-application-shell' ) . '</a></p>' );
		}

		if ( ! empty( $modules['emergency'] ) && ! empty( $settings['right_sidebar']['emergency_notice'] ) ) {
			self::render_panel( __( 'Emergency Notice', 'sabri-unified-application-shell' ), '<p>' . esc_html( $settings['right_sidebar']['emergency_notice'] ) . '</p>' );
		}

		if ( ! empty( $modules['whatsapp'] ) && ! empty( $settings['integrations']['urls']['whatsapp'] ) ) {
			self::render_panel( __( 'WhatsApp Help', 'sabri-unified-application-shell' ), '<p><a href="' . esc_url( $settings['integrations']['urls']['whatsapp'] ) . '">' . esc_html__( 'Open WhatsApp help', 'sabri-unified-application-shell' ) . '</a></p>' );
		}
	}

	/**
	 * Render single doctor or clinic sidebar.
	 *
	 * @param array<string,mixed> $settings Settings.
	 * @return void
	 */
	private static function render_single_clinic_sidebar( array $settings ) {
		if ( ! function_exists( 'get_the_ID' ) || ! get_the_ID() ) {
			self::render_panel( __( 'Medical Safety', 'sabri-unified-application-shell' ), '<p>' . esc_html__( 'This page is educational and is not a replacement for urgent medical care.', 'sabri-unified-application-shell' ) . '</p>' );
			return;
		}

		$post_id = get_the_ID();
		$title   = get_the_title( $post_id );
		$fields  = array(
			'fee'       => get_post_meta( $post_id, 'sabri_public_fee', true ),
			'timings'   => get_post_meta( $post_id, 'sabri_public_timings', true ),
			'languages' => get_post_meta( $post_id, 'sabri_public_languages', true ),
			'specialty' => get_post_meta( $post_id, 'sabri_public_specialty', true ),
			'phone'     => get_post_meta( $post_id, 'sabri_public_phone', true ),
			'whatsapp'  => get_post_meta( $post_id, 'sabri_public_whatsapp', true ),
		);

		echo '<section class="sabri-shell-panel"><h2>' . esc_html( $title ) . '</h2><dl>';
		foreach ( $fields as $label => $value ) {
			if ( '' === (string) $value ) {
				continue;
			}
			echo '<dt>' . esc_html( ucfirst( $label ) ) . '</dt><dd>' . esc_html( $value ) . '</dd>';
		}
		echo '</dl></section>';

		if ( ! empty( $settings['integrations']['urls']['appointments'] ) ) {
			self::render_panel( __( 'Appointment', 'sabri-unified-application-shell' ), '<p><a href="' . esc_url( $settings['integrations']['urls']['appointments'] ) . '">' . esc_html__( 'Request an appointment', 'sabri-unified-application-shell' ) . '</a></p>' );
		}

		if ( ! empty( $settings['integrations']['urls']['messages'] ) ) {
			self::render_panel( __( 'Message', 'sabri-unified-application-shell' ), '<p><a href="' . esc_url( $settings['integrations']['urls']['messages'] ) . '">' . esc_html__( 'Open messages', 'sabri-unified-application-shell' ) . '</a></p>' );
		}

		self::render_panel( __( 'Medical Safety', 'sabri-unified-application-shell' ), '<p>' . esc_html__( 'For emergencies, contact local emergency services. Do not share private patient data through public profiles.', 'sabri-unified-application-shell' ) . '</p>' );
	}

	/**
	 * Render latest posts panel.
	 *
	 * @return void
	 */
	private static function render_latest_posts_panel() {
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

		self::render_panel( __( 'Latest Posts', 'sabri-unified-application-shell' ), $html );
	}

	/**
	 * Render verified doctors from real roles only.
	 *
	 * @return void
	 */
	private static function render_verified_doctors_panel() {
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

		self::render_panel( __( 'Verified Doctors', 'sabri-unified-application-shell' ), $html );
	}

	/**
	 * Render real latest items for a post type.
	 *
	 * @param string $title Panel title.
	 * @param string $post_type Post type.
	 * @return void
	 */
	private static function render_post_type_panel( $title, $post_type ) {
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

		self::render_panel( $title, $html );
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
			self::render_panel( __( 'Quick Access', 'sabri-unified-application-shell' ), $html );
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
		echo '<button type="button" class="sabri-shell-bottom-item" data-sabri-drawer-trigger="sabri-shell-drawer-nav" aria-controls="sabri-shell-drawer-nav" aria-expanded="false"><span aria-hidden="true">&#9776;</span><span>' . esc_html( $settings['mobile']['menu_label'] ) . '</span></button>';
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
	private static function render_panel( $title, $html ) {
		echo '<section class="sabri-shell-panel">';
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
