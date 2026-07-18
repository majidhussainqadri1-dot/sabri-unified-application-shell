<?php
/**
 * Admin settings UI.
 *
 * @package SabriUnifiedApplicationShell
 */

namespace Sabri\UnifiedShell;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Provides the Sabri Shell top-level admin page.
 */
final class Admin {
	/**
	 * Tab definitions.
	 *
	 * @return array<string,string>
	 */
	private static function tabs() {
		return array(
			'overview'      => __( 'Overview', 'sabri-unified-application-shell' ),
			'layout'        => __( 'Layout', 'sabri-unified-application-shell' ),
			'header'        => __( 'Header', 'sabri-unified-application-shell' ),
			'navigation'    => __( 'Navigation', 'sabri-unified-application-shell' ),
			'left-sidebar'  => __( 'Left Sidebar', 'sabri-unified-application-shell' ),
			'right-sidebar' => __( 'Right Sidebar', 'sabri-unified-application-shell' ),
			'mobile'        => __( 'Mobile', 'sabri-unified-application-shell' ),
			'integrations'  => __( 'Integrations', 'sabri-unified-application-shell' ),
			'appearance'    => __( 'Appearance', 'sabri-unified-application-shell' ),
			'system-check'  => __( 'System Check', 'sabri-unified-application-shell' ),
			'repair'        => __( 'Repair', 'sabri-unified-application-shell' ),
			'safe-mode'     => __( 'Safe Mode', 'sabri-unified-application-shell' ),
		);
	}

	/**
	 * Register admin hooks.
	 *
	 * @return void
	 */
	public static function register() {
		add_action( 'admin_menu', array( __CLASS__, 'menu' ) );
		add_action( 'admin_post_sabri_shell_repair', array( __CLASS__, 'handle_repair' ) );
		add_action( 'admin_post_sabri_shell_rollback', array( __CLASS__, 'handle_rollback' ) );
		add_action( 'admin_post_sabri_shell_emergency', array( __CLASS__, 'handle_emergency' ) );
	}

	/**
	 * Add menu.
	 *
	 * @return void
	 */
	public static function menu() {
		add_menu_page(
			__( 'Sabri Shell', 'sabri-unified-application-shell' ),
			__( 'Sabri Shell', 'sabri-unified-application-shell' ),
			'manage_options',
			'sabri-shell',
			array( __CLASS__, 'render' ),
			'dashicons-layout',
			58
		);
	}

	/**
	 * Render admin page.
	 *
	 * @return void
	 */
	public static function render() {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'You do not have permission to manage the Sabri Shell.', 'sabri-unified-application-shell' ) );
		}

		$tabs     = self::tabs();
		$active   = isset( $_GET['tab'] ) ? sanitize_key( wp_unslash( $_GET['tab'] ) ) : 'overview';
		$active   = isset( $tabs[ $active ] ) ? $active : 'overview';
		$settings = Settings::get();

		echo '<div class="wrap sabri-shell-admin">';
		echo '<h1>' . esc_html__( 'Sabri Shell', 'sabri-unified-application-shell' ) . '</h1>';
		self::render_notices();
		echo '<nav class="nav-tab-wrapper" aria-label="' . esc_attr__( 'Sabri Shell settings tabs', 'sabri-unified-application-shell' ) . '">';
		foreach ( $tabs as $key => $label ) {
			$url = add_query_arg( array( 'page' => 'sabri-shell', 'tab' => $key ), admin_url( 'admin.php' ) );
			echo '<a class="nav-tab ' . esc_attr( $active === $key ? 'nav-tab-active' : '' ) . '" href="' . esc_url( $url ) . '">' . esc_html( $label ) . '</a>';
		}
		echo '</nav>';

		if ( in_array( $active, array( 'system-check', 'repair' ), true ) ) {
			self::render_special_tab( $active, $settings );
		} else {
			echo '<form method="post" action="options.php">';
			settings_fields( 'sabri_shell_settings' );
			echo '<input type="hidden" name="' . esc_attr( Defaults::OPTION_NAME ) . '[_active_tab]" value="' . esc_attr( $active ) . '">';
			self::render_tab( $active, $settings );
			submit_button();
			echo '</form>';
			if ( 'safe-mode' === $active ) {
				self::safe_mode_controls();
			}
		}

		echo '</div>';
	}

	/**
	 * Render notices.
	 *
	 * @return void
	 */
	private static function render_notices() {
		if ( empty( $_GET['sabri_shell_notice'] ) ) {
			return;
		}

		$notice = sanitize_key( wp_unslash( $_GET['sabri_shell_notice'] ) );
		$map    = array(
			'repaired'          => __( 'Complete Repair finished.', 'sabri-unified-application-shell' ),
			'rollback-success'  => __( 'Rollback restored shell-owned settings from the activation snapshot.', 'sabri-unified-application-shell' ),
			'rollback-missing'  => __( 'No activation snapshot was available for rollback.', 'sabri-unified-application-shell' ),
			'emergency-on'      => __( 'Emergency Disable is active.', 'sabri-unified-application-shell' ),
			'emergency-off'     => __( 'The shell has been re-enabled.', 'sabri-unified-application-shell' ),
		);

		if ( isset( $map[ $notice ] ) ) {
			echo '<div class="notice notice-info is-dismissible"><p>' . esc_html( $map[ $notice ] ) . '</p></div>';
		}
	}

	/**
	 * Render a normal form tab.
	 *
	 * @param string              $tab Tab.
	 * @param array<string,mixed> $settings Settings.
	 * @return void
	 */
	private static function render_tab( $tab, array $settings ) {
		switch ( $tab ) {
			case 'overview':
				self::overview( $settings );
				break;
			case 'layout':
				self::layout( $settings );
				break;
			case 'header':
				self::header( $settings );
				break;
			case 'navigation':
				self::navigation( $settings );
				break;
			case 'left-sidebar':
				self::left_sidebar( $settings );
				break;
			case 'right-sidebar':
				self::right_sidebar( $settings );
				break;
			case 'mobile':
				self::mobile( $settings );
				break;
			case 'integrations':
				self::integrations( $settings );
				break;
			case 'appearance':
				self::appearance( $settings );
				break;
			case 'safe-mode':
				self::safe_mode( $settings );
				break;
		}
	}

	/**
	 * Render special tabs.
	 *
	 * @param string              $tab Tab.
	 * @param array<string,mixed> $settings Settings.
	 * @return void
	 */
	private static function render_special_tab( $tab, array $settings ) {
		if ( 'system-check' === $tab ) {
			self::system_check();
			return;
		}

		self::repair( $settings );
	}

	/**
	 * Overview tab.
	 *
	 * @param array<string,mixed> $settings Settings.
	 * @return void
	 */
	private static function overview( array $settings ) {
		echo '<h2>' . esc_html__( 'Overview', 'sabri-unified-application-shell' ) . '</h2>';
		echo '<p>' . esc_html__( 'Activate on staging first, verify the shell with real pages and integrations, then deploy to production.', 'sabri-unified-application-shell' ) . '</p>';
		self::checkbox( 'enabled', __( 'Enable Shell', 'sabri-unified-application-shell' ), $settings['enabled'] );
		echo '<p><strong>' . esc_html__( 'Version', 'sabri-unified-application-shell' ) . ':</strong> ' . esc_html( SABRI_SHELL_VERSION ) . '</p>';
		echo '<p><strong>' . esc_html__( 'Current layout mode', 'sabri-unified-application-shell' ) . ':</strong> ' . esc_html( Layout::current_mode() ) . '</p>';
		echo '<p><strong>' . esc_html__( 'Emergency status', 'sabri-unified-application-shell' ) . ':</strong> ' . esc_html( ! empty( $settings['emergency_disabled'] ) ? __( 'Disabled', 'sabri-unified-application-shell' ) : __( 'Enabled', 'sabri-unified-application-shell' ) ) . '</p>';
		echo '<p><a href="' . esc_url( add_query_arg( array( 'page' => 'sabri-shell', 'tab' => 'system-check' ), admin_url( 'admin.php' ) ) ) . '">' . esc_html__( 'Open System Check', 'sabri-unified-application-shell' ) . '</a></p>';
		echo '<p><a href="' . esc_url( add_query_arg( array( 'page' => 'sabri-shell', 'tab' => 'safe-mode' ), admin_url( 'admin.php' ) ) ) . '">' . esc_html__( 'Safe Mode instructions', 'sabri-unified-application-shell' ) . '</a></p>';
		self::checkbox( 'delete_on_uninstall', __( 'Delete shell settings on uninstall', 'sabri-unified-application-shell' ), $settings['delete_on_uninstall'] );
	}

	/**
	 * Layout tab.
	 *
	 * @param array<string,mixed> $settings Settings.
	 * @return void
	 */
	private static function layout( array $settings ) {
		$layout = $settings['layout'];
		echo '<h2>' . esc_html__( 'Layout', 'sabri-unified-application-shell' ) . '</h2>';
		self::number( 'layout[max_width]', __( 'Maximum shell width', 'sabri-unified-application-shell' ), $layout['max_width'], 960, 2400 );
		self::number( 'layout[left_width]', __( 'Left Sidebar width', 'sabri-unified-application-shell' ), $layout['left_width'], 220, 380 );
		self::number( 'layout[right_width]', __( 'Right Sidebar width', 'sabri-unified-application-shell' ), $layout['right_width'], 260, 460 );
		self::number( 'layout[gap]', __( 'Gap', 'sabri-unified-application-shell' ), $layout['gap'], 8, 48 );
		self::checkbox( 'layout[sticky_header]', __( 'Sticky Header', 'sabri-unified-application-shell' ), $layout['sticky_header'] );
		self::checkbox( 'layout[compact_desktop]', __( 'Compact desktop', 'sabri-unified-application-shell' ), $layout['compact_desktop'] );
		self::number( 'layout[worldwide_clinic_page_id]', __( 'Worldwide Clinic page ID', 'sabri-unified-application-shell' ), $layout['worldwide_clinic_page_id'], 0, 999999 );
		self::text( 'layout[clinic_post_type]', __( 'Doctor/clinic post type', 'sabri-unified-application-shell' ), $layout['clinic_post_type'] );
		self::textarea( 'layout[excluded_page_ids]', __( 'Excluded page IDs', 'sabri-unified-application-shell' ), implode( ',', $layout['excluded_page_ids'] ) );
		self::textarea( 'layout[per_page_overrides]', __( 'Per-page layout overrides', 'sabri-unified-application-shell' ), self::overrides_to_text( $layout['per_page_overrides'] ), __( 'Use one line per page, for example 42:three, 99:two, 100:minimal.', 'sabri-unified-application-shell' ) );
		self::text( 'layout[theme_content_selector]', __( 'Theme content selector', 'sabri-unified-application-shell' ), $layout['theme_content_selector'] );
		self::checkbox( 'layout[hide_theme_header]', __( 'Hide Theme Header', 'sabri-unified-application-shell' ), $layout['hide_theme_header'] );
		self::checkbox( 'layout[hide_theme_footer]', __( 'Hide Theme Footer', 'sabri-unified-application-shell' ), $layout['hide_theme_footer'] );
		self::textarea( 'layout[custom_hide_selectors]', __( 'Validated custom hide selectors', 'sabri-unified-application-shell' ), $layout['custom_hide_selectors'] );
		echo '<p>' . esc_html__( 'Theme compatibility can require mapping the main content selector. The shell does not claim universal compatibility with every WordPress theme.', 'sabri-unified-application-shell' ) . '</p>';
	}

	/**
	 * Header tab.
	 *
	 * @param array<string,mixed> $settings Settings.
	 * @return void
	 */
	private static function header( array $settings ) {
		$header = $settings['header'];
		echo '<h2>' . esc_html__( 'Header', 'sabri-unified-application-shell' ) . '</h2>';
		self::checkbox( 'header[enabled]', __( 'Enable Header', 'sabri-unified-application-shell' ), $header['enabled'] );
		self::text( 'header[platform_title]', __( 'Platform title', 'sabri-unified-application-shell' ), $header['platform_title'] );
		foreach ( array( 'search', 'create', 'messages', 'notifications', 'help', 'language', 'profile' ) as $key ) {
			self::checkbox( 'header[' . $key . ']', ucfirst( $key ), $header[ $key ] );
		}
		self::textarea( 'header[allowed_roles]', __( 'Allowed Create roles', 'sabri-unified-application-shell' ), implode( "\n", $header['allowed_roles'] ), __( 'Existing role slugs only. The shell does not grant publishing capabilities.', 'sabri-unified-application-shell' ) );
	}

	/**
	 * Navigation tab.
	 *
	 * @param array<string,mixed> $settings Settings.
	 * @return void
	 */
	private static function navigation( array $settings ) {
		echo '<h2>' . esc_html__( 'Navigation', 'sabri-unified-application-shell' ) . '</h2>';
		echo '<table class="widefat striped"><thead><tr><th>' . esc_html__( 'Destination', 'sabri-unified-application-shell' ) . '</th><th>' . esc_html__( 'Enabled', 'sabri-unified-application-shell' ) . '</th><th>' . esc_html__( 'Label', 'sabri-unified-application-shell' ) . '</th><th>' . esc_html__( 'Page ID', 'sabri-unified-application-shell' ) . '</th><th>' . esc_html__( 'Shortcode', 'sabri-unified-application-shell' ) . '</th><th>' . esc_html__( 'Slug', 'sabri-unified-application-shell' ) . '</th><th>' . esc_html__( 'URL override', 'sabri-unified-application-shell' ) . '</th><th>' . esc_html__( 'Order', 'sabri-unified-application-shell' ) . '</th></tr></thead><tbody>';
		foreach ( Defaults::destinations() as $key => $destination ) {
			$row = $settings['navigation'][ $key ];
			echo '<tr><td>' . esc_html( $destination['label'] ) . '</td>';
			echo '<td>' . self::checkbox_html( 'navigation[' . $key . '][enabled]', $row['enabled'] ) . '</td>';
			echo '<td><input type="text" name="' . esc_attr( Defaults::OPTION_NAME . '[navigation][' . $key . '][label]' ) . '" value="' . esc_attr( $row['label'] ) . '"></td>';
			echo '<td><input type="number" min="0" name="' . esc_attr( Defaults::OPTION_NAME . '[navigation][' . $key . '][page_id]' ) . '" value="' . esc_attr( $row['page_id'] ) . '"></td>';
			echo '<td><input type="text" name="' . esc_attr( Defaults::OPTION_NAME . '[navigation][' . $key . '][shortcode]' ) . '" value="' . esc_attr( $row['shortcode'] ) . '"></td>';
			echo '<td><input type="text" name="' . esc_attr( Defaults::OPTION_NAME . '[navigation][' . $key . '][slug]' ) . '" value="' . esc_attr( $row['slug'] ) . '"></td>';
			echo '<td><input type="url" name="' . esc_attr( Defaults::OPTION_NAME . '[navigation][' . $key . '][url_override]' ) . '" value="' . esc_attr( $row['url_override'] ) . '"></td>';
			echo '<td><input type="number" min="0" name="' . esc_attr( Defaults::OPTION_NAME . '[navigation][' . $key . '][order]' ) . '" value="' . esc_attr( $row['order'] ) . '"></td></tr>';
		}
		echo '</tbody></table>';
	}

	/**
	 * Left sidebar tab.
	 *
	 * @param array<string,mixed> $settings Settings.
	 * @return void
	 */
	private static function left_sidebar( array $settings ) {
		echo '<h2>' . esc_html__( 'Left Sidebar', 'sabri-unified-application-shell' ) . '</h2>';
		self::checkbox( 'left_sidebar[enabled]', __( 'Enable Left Sidebar', 'sabri-unified-application-shell' ), $settings['left_sidebar']['enabled'] );
		echo '<h3>' . esc_html__( 'Groups', 'sabri-unified-application-shell' ) . '</h3>';
		foreach ( Defaults::groups() as $key => $label ) {
			self::checkbox( 'left_sidebar[groups][' . $key . ']', $label, ! empty( $settings['left_sidebar']['groups'][ $key ] ) );
		}
		echo '<h3>' . esc_html__( 'Items', 'sabri-unified-application-shell' ) . '</h3>';
		foreach ( Defaults::destinations() as $key => $destination ) {
			self::checkbox( 'left_sidebar[items][' . $key . ']', $destination['label'], ! empty( $settings['left_sidebar']['items'][ $key ] ) );
		}
		echo '<h3>' . esc_html__( 'Footer mappings', 'sabri-unified-application-shell' ) . '</h3>';
		foreach ( $settings['left_sidebar']['footer_mappings'] as $key => $url ) {
			self::url( 'left_sidebar[footer_mappings][' . $key . ']', ucfirst( str_replace( '_', ' ', $key ) ), $url );
		}
	}

	/**
	 * Right sidebar tab.
	 *
	 * @param array<string,mixed> $settings Settings.
	 * @return void
	 */
	private static function right_sidebar( array $settings ) {
		echo '<h2>' . esc_html__( 'Right Sidebar', 'sabri-unified-application-shell' ) . '</h2>';
		self::checkbox( 'right_sidebar[enabled]', __( 'Enable Right Sidebar', 'sabri-unified-application-shell' ), $settings['right_sidebar']['enabled'] );
		self::checkbox( 'right_sidebar[hide_missing]', __( 'Hide missing integrations and data', 'sabri-unified-application-shell' ), $settings['right_sidebar']['hide_missing'] );
		foreach ( array( 'home_modules', 'clinic_modules', 'single_modules' ) as $group ) {
			echo '<h3>' . esc_html( ucwords( str_replace( '_', ' ', $group ) ) ) . '</h3>';
			foreach ( $settings['right_sidebar'][ $group ] as $key => $enabled ) {
				self::checkbox( 'right_sidebar[' . $group . '][' . $key . ']', ucwords( str_replace( '_', ' ', $key ) ), $enabled );
			}
		}
		self::textarea( 'right_sidebar[announcement]', __( 'Announcement', 'sabri-unified-application-shell' ), $settings['right_sidebar']['announcement'] );
		self::text( 'right_sidebar[emergency_notice]', __( 'Emergency notice', 'sabri-unified-application-shell' ), $settings['right_sidebar']['emergency_notice'] );
	}

	/**
	 * Mobile tab.
	 *
	 * @param array<string,mixed> $settings Settings.
	 * @return void
	 */
	private static function mobile( array $settings ) {
		echo '<h2>' . esc_html__( 'Mobile', 'sabri-unified-application-shell' ) . '</h2>';
		self::checkbox( 'mobile[bottom_nav]', __( 'Bottom Navigation', 'sabri-unified-application-shell' ), $settings['mobile']['bottom_nav'] );
		self::checkbox( 'mobile[drawers]', __( 'Accessible drawers', 'sabri-unified-application-shell' ), $settings['mobile']['drawers'] );
		self::select( 'mobile[create_or_doctors]', __( 'Create-or-Doctors behavior', 'sabri-unified-application-shell' ), $settings['mobile']['create_or_doctors'], array( 'auto' => __( 'Auto', 'sabri-unified-application-shell' ), 'create' => __( 'Create', 'sabri-unified-application-shell' ), 'doctors' => __( 'Doctors', 'sabri-unified-application-shell' ) ) );
		self::text( 'mobile[menu_label]', __( 'Menu label', 'sabri-unified-application-shell' ), $settings['mobile']['menu_label'] );
		self::text( 'mobile[create_label]', __( 'Create label', 'sabri-unified-application-shell' ), $settings['mobile']['create_label'] );
	}

	/**
	 * Integrations tab.
	 *
	 * @param array<string,mixed> $settings Settings.
	 * @return void
	 */
	private static function integrations( array $settings ) {
		$detected = Integrations::detect();
		echo '<h2>' . esc_html__( 'Integrations', 'sabri-unified-application-shell' ) . '</h2>';
		echo '<table class="widefat striped"><tbody>';
		foreach ( array( 'notifications', 'network', 'messages', 'marketplace', 'appointments', 'language' ) as $key ) {
			echo '<tr><th>' . esc_html( ucfirst( $key ) ) . '</th><td>' . esc_html( ! empty( $detected[ $key ] ) ? __( 'Detected', 'sabri-unified-application-shell' ) : __( 'Not detected', 'sabri-unified-application-shell' ) ) . '</td></tr>';
		}
		echo '<tr><th>' . esc_html__( 'Doctor roles', 'sabri-unified-application-shell' ) . '</th><td>' . esc_html( implode( ', ', $detected['doctor_roles'] ) ) . '</td></tr>';
		echo '<tr><th>' . esc_html__( 'Verified doctor roles', 'sabri-unified-application-shell' ) . '</th><td>' . esc_html( implode( ', ', $detected['verified_doctor_roles'] ) ) . '</td></tr>';
		echo '<tr><th>' . esc_html__( 'Clinic post types', 'sabri-unified-application-shell' ) . '</th><td>' . esc_html( implode( ', ', $detected['clinic_post_types'] ) ) . '</td></tr>';
		echo '</tbody></table>';
		echo '<h3>' . esc_html__( 'Configured functions', 'sabri-unified-application-shell' ) . '</h3>';
		foreach ( $settings['integrations']['functions'] as $key => $function ) {
			self::text( 'integrations[functions][' . $key . ']', ucfirst( $key ), $function );
		}
		echo '<h3>' . esc_html__( 'Configured URLs', 'sabri-unified-application-shell' ) . '</h3>';
		foreach ( $settings['integrations']['urls'] as $key => $url ) {
			self::url( 'integrations[urls][' . $key . ']', ucfirst( $key ), $url );
		}
		echo '<p>' . esc_html__( 'The shell only links to existing integrations. It does not create duplicate messaging, appointment, marketplace, notification, publishing, or profile databases.', 'sabri-unified-application-shell' ) . '</p>';
	}

	/**
	 * Appearance tab.
	 *
	 * @param array<string,mixed> $settings Settings.
	 * @return void
	 */
	private static function appearance( array $settings ) {
		$appearance = $settings['appearance'];
		echo '<h2>' . esc_html__( 'Appearance', 'sabri-unified-application-shell' ) . '</h2>';
		self::select( 'appearance[color_mode]', __( 'Color mode', 'sabri-unified-application-shell' ), $appearance['color_mode'], array( 'light' => __( 'Light', 'sabri-unified-application-shell' ), 'dark' => __( 'Dark', 'sabri-unified-application-shell' ), 'system' => __( 'System', 'sabri-unified-application-shell' ) ) );
		self::select( 'appearance[density]', __( 'Density', 'sabri-unified-application-shell' ), $appearance['density'], array( 'comfortable' => __( 'Comfortable', 'sabri-unified-application-shell' ), 'compact' => __( 'Compact', 'sabri-unified-application-shell' ) ) );
		self::text( 'appearance[primary_color]', __( 'Primary bright orange', 'sabri-unified-application-shell' ), $appearance['primary_color'] );
		self::number( 'appearance[border_radius]', __( 'Border radius', 'sabri-unified-application-shell' ), $appearance['border_radius'], 0, 20 );
		self::text( 'appearance[font_scale]', __( 'Font scale', 'sabri-unified-application-shell' ), $appearance['font_scale'] );
	}

	/**
	 * Safe Mode tab.
	 *
	 * @param array<string,mixed> $settings Settings.
	 * @return void
	 */
	private static function safe_mode( array $settings ) {
		echo '<h2>' . esc_html__( 'Safe Mode', 'sabri-unified-application-shell' ) . '</h2>';
		echo '<p>' . esc_html__( 'Administrators can add ?sabri_shell_safe=1 to a public URL to suppress the shell for that request.', 'sabri-unified-application-shell' ) . '</p>';
		echo '<p>' . esc_html__( 'A developer can also define SABRI_SHELL_DISABLE as true in wp-config.php for an emergency constant kill switch.', 'sabri-unified-application-shell' ) . '</p>';
		self::checkbox( 'emergency_disabled', __( 'Emergency Disable', 'sabri-unified-application-shell' ), $settings['emergency_disabled'] );
	}

	/**
	 * Render Safe Mode action controls outside the settings form.
	 *
	 * @return void
	 */
	private static function safe_mode_controls() {
		echo '<h3>' . esc_html__( 'Emergency controls', 'sabri-unified-application-shell' ) . '</h3>';
		echo '<p>' . esc_html__( 'These controls only toggle the shell renderer. They do not remove posts, pages, users, media, comments, messages, appointments, marketplace data, clinic data, or companion-plugin tables.', 'sabri-unified-application-shell' ) . '</p>';
		echo '<form method="post" action="' . esc_url( admin_url( 'admin-post.php' ) ) . '" style="display:inline-block;margin-inline-end:8px;">';
		wp_nonce_field( 'sabri_shell_emergency' );
		echo '<input type="hidden" name="action" value="sabri_shell_emergency">';
		echo '<input type="hidden" name="disable" value="1">';
		submit_button( __( 'Emergency Disable', 'sabri-unified-application-shell' ), 'delete', 'submit', false );
		echo '</form>';
		echo '<form method="post" action="' . esc_url( admin_url( 'admin-post.php' ) ) . '" style="display:inline-block;">';
		wp_nonce_field( 'sabri_shell_emergency' );
		echo '<input type="hidden" name="action" value="sabri_shell_emergency">';
		echo '<input type="hidden" name="disable" value="0">';
		submit_button( __( 'Re-enable', 'sabri-unified-application-shell' ), 'secondary', 'submit', false );
		echo '</form>';
	}

	/**
	 * System Check tab.
	 *
	 * @return void
	 */
	private static function system_check() {
		echo '<h2>' . esc_html__( 'System Check', 'sabri-unified-application-shell' ) . '</h2>';
		echo '<table class="widefat striped"><thead><tr><th>' . esc_html__( 'Check', 'sabri-unified-application-shell' ) . '</th><th>' . esc_html__( 'Result', 'sabri-unified-application-shell' ) . '</th><th>' . esc_html__( 'Status', 'sabri-unified-application-shell' ) . '</th></tr></thead><tbody>';
		foreach ( SystemCheck::report() as $row ) {
			echo '<tr><th>' . esc_html( $row['label'] ) . '</th><td>' . esc_html( $row['value'] ) . '</td><td>' . esc_html( strtoupper( $row['status'] ) ) . '</td></tr>';
		}
		echo '</tbody></table>';
	}

	/**
	 * Repair tab.
	 *
	 * @param array<string,mixed> $settings Settings.
	 * @return void
	 */
	private static function repair( array $settings ) {
		echo '<h2>' . esc_html__( 'Repair', 'sabri-unified-application-shell' ) . '</h2>';
		echo '<p>' . esc_html__( 'Complete Repair merges missing defaults, clears shell caches, schedules one rewrite-rule flush, revalidates schema, and refreshes integration detection. It never deletes posts, pages, users, media, comments, or companion-plugin data.', 'sabri-unified-application-shell' ) . '</p>';
		echo '<form method="post" action="' . esc_url( admin_url( 'admin-post.php' ) ) . '">';
		wp_nonce_field( 'sabri_shell_repair' );
		echo '<input type="hidden" name="action" value="sabri_shell_repair">';
		submit_button( __( 'Run Complete Repair', 'sabri-unified-application-shell' ), 'primary', 'submit', false );
		echo '</form>';
		echo '<hr>';
		echo '<p>' . esc_html__( 'Rollback restores only shell-owned settings from the activation snapshot.', 'sabri-unified-application-shell' ) . '</p>';
		echo '<form method="post" action="' . esc_url( admin_url( 'admin-post.php' ) ) . '">';
		wp_nonce_field( 'sabri_shell_rollback' );
		echo '<input type="hidden" name="action" value="sabri_shell_rollback">';
		submit_button( __( 'Rollback Shell Settings', 'sabri-unified-application-shell' ), 'secondary', 'submit', false );
		echo '</form>';
	}

	/**
	 * Handle repair.
	 *
	 * @return void
	 */
	public static function handle_repair() {
		self::require_manage_options( 'sabri_shell_repair' );
		Repair::run();
		wp_safe_redirect( add_query_arg( array( 'page' => 'sabri-shell', 'tab' => 'repair', 'sabri_shell_notice' => 'repaired' ), admin_url( 'admin.php' ) ) );
		exit;
	}

	/**
	 * Handle rollback.
	 *
	 * @return void
	 */
	public static function handle_rollback() {
		self::require_manage_options( 'sabri_shell_rollback' );
		$success = Snapshot::rollback();
		wp_safe_redirect( add_query_arg( array( 'page' => 'sabri-shell', 'tab' => 'repair', 'sabri_shell_notice' => $success ? 'rollback-success' : 'rollback-missing' ), admin_url( 'admin.php' ) ) );
		exit;
	}

	/**
	 * Handle emergency toggle.
	 *
	 * @return void
	 */
	public static function handle_emergency() {
		self::require_manage_options( 'sabri_shell_emergency' );
		$settings                       = Settings::get();
		$settings['emergency_disabled'] = ! empty( $_POST['disable'] );
		update_option( Defaults::OPTION_NAME, $settings, false );
		wp_safe_redirect( add_query_arg( array( 'page' => 'sabri-shell', 'tab' => 'safe-mode', 'sabri_shell_notice' => $settings['emergency_disabled'] ? 'emergency-on' : 'emergency-off' ), admin_url( 'admin.php' ) ) );
		exit;
	}

	/**
	 * Capability and nonce guard.
	 *
	 * @param string $nonce_action Nonce action.
	 * @return void
	 */
	private static function require_manage_options( $nonce_action ) {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'You do not have permission to manage the Sabri Shell.', 'sabri-unified-application-shell' ) );
		}

		check_admin_referer( $nonce_action );
	}

	/**
	 * Field helpers.
	 */
	private static function checkbox( $name, $label, $checked ) {
		echo '<p><label>' . self::checkbox_html( $name, $checked ) . ' ' . esc_html( $label ) . '</label></p>';
	}

	private static function checkbox_html( $name, $checked ) {
		$full = self::field_name( $name );
		return '<input type="hidden" name="' . esc_attr( $full ) . '" value="0"><input type="checkbox" name="' . esc_attr( $full ) . '" value="1" ' . checked( $checked, true, false ) . '>';
	}

	private static function text( $name, $label, $value ) {
		echo '<p><label><span class="sabri-shell-admin-label">' . esc_html( $label ) . '</span><input class="regular-text" type="text" name="' . esc_attr( self::field_name( $name ) ) . '" value="' . esc_attr( $value ) . '"></label></p>';
	}

	private static function url( $name, $label, $value ) {
		echo '<p><label><span class="sabri-shell-admin-label">' . esc_html( $label ) . '</span><input class="regular-text" type="url" name="' . esc_attr( self::field_name( $name ) ) . '" value="' . esc_attr( $value ) . '"></label></p>';
	}

	private static function number( $name, $label, $value, $min, $max ) {
		echo '<p><label><span class="sabri-shell-admin-label">' . esc_html( $label ) . '</span><input type="number" min="' . esc_attr( $min ) . '" max="' . esc_attr( $max ) . '" name="' . esc_attr( self::field_name( $name ) ) . '" value="' . esc_attr( $value ) . '"></label></p>';
	}

	private static function textarea( $name, $label, $value, $help = '' ) {
		echo '<p><label><span class="sabri-shell-admin-label">' . esc_html( $label ) . '</span><textarea class="large-text" rows="4" name="' . esc_attr( self::field_name( $name ) ) . '">' . esc_textarea( $value ) . '</textarea></label>';
		if ( $help ) {
			echo '<br><span class="description">' . esc_html( $help ) . '</span>';
		}
		echo '</p>';
	}

	private static function select( $name, $label, $value, array $options ) {
		echo '<p><label><span class="sabri-shell-admin-label">' . esc_html( $label ) . '</span><select name="' . esc_attr( self::field_name( $name ) ) . '">';
		foreach ( $options as $key => $text ) {
			echo '<option value="' . esc_attr( $key ) . '" ' . selected( $value, $key, false ) . '>' . esc_html( $text ) . '</option>';
		}
		echo '</select></label></p>';
	}

	private static function field_name( $name ) {
		$parts = preg_split( '/\[|\]/', $name, -1, PREG_SPLIT_NO_EMPTY );
		return Defaults::OPTION_NAME . '[' . implode( '][', $parts ) . ']';
	}

	private static function overrides_to_text( array $overrides ) {
		$lines = array();
		foreach ( $overrides as $id => $mode ) {
			$lines[] = absint( $id ) . ':' . $mode;
		}

		return implode( "\n", $lines );
	}
}
