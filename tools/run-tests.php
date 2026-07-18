<?php
/**
 * Local and CI test runner for the Sabri Shell.
 *
 * @package SabriUnifiedApplicationShell
 */

$root = dirname( __DIR__ );
require_once $root . '/tests/bootstrap.php';

use Sabri\UnifiedShell\Defaults;
use Sabri\UnifiedShell\Layout;
use Sabri\UnifiedShell\Navigation;
use Sabri\UnifiedShell\SafeMode;
use Sabri\UnifiedShell\Settings;
use Sabri\UnifiedShell\Snapshot;

$results = array();

function sabri_assert( $name, $condition, $details = '' ) {
	global $results;
	$results[] = array(
		'name'    => $name,
		'status'  => $condition ? 'passed' : 'failed',
		'details' => $details,
	);
}

function sabri_file( $path ) {
	return file_get_contents( dirname( __DIR__ ) . '/' . $path );
}

function sabri_files( $pattern ) {
	$root = dirname( __DIR__ );
	$files = array();
	$skip_prefixes = array(
		'.git/',
		'release/',
		'node_modules/',
		'vendor/',
	);
	$iterator = new RecursiveIteratorIterator( new RecursiveDirectoryIterator( $root, FilesystemIterator::SKIP_DOTS ) );

	foreach ( $iterator as $file ) {
		if ( ! $file instanceof SplFileInfo || ! $file->isFile() ) {
			continue;
		}

		$path = sabri_relative_path( $file );
		foreach ( $skip_prefixes as $prefix ) {
			if ( 0 === strpos( $path, $prefix ) ) {
				continue 2;
			}
		}

		if ( preg_match( $pattern, $path ) ) {
			$files[] = $file;
		}
	}

	return $files;
}

function sabri_relative_path( SplFileInfo $file ) {
	$root = dirname( __DIR__ );
	return str_replace( '\\', '/', substr( $file->getPathname(), strlen( $root ) + 1 ) );
}

function sabri_file_helper_tests() {
	$php_files = sabri_files( '/\.php$/' );
	$paths     = array_map( 'sabri_relative_path', $php_files );

	sabri_assert( 'sabri_files returns an array', is_array( $php_files ) );
	sabri_assert( 'sabri_files preserves SplFileInfo values', ! empty( $php_files ) && $php_files[0] instanceof SplFileInfo );
	sabri_assert( 'sabri_files returns only regular files', count( $php_files ) === count( array_filter( $php_files, static function ( $file ) { return $file instanceof SplFileInfo && $file->isFile(); } ) ) );
	sabri_assert( 'sabri_files contains expected project files', in_array( 'sabri-unified-application-shell.php', $paths, true ) && in_array( 'tools/run-tests.php', $paths, true ) );
	sabri_assert( 'sabri_files excludes ignored output paths', 0 === count( array_filter( $paths, static function ( $path ) { return 0 === strpos( $path, '.git/' ) || 0 === strpos( $path, 'release/' ) || 0 === strpos( $path, 'node_modules/' ) || 0 === strpos( $path, 'vendor/' ); } ) ) );
}

sabri_file_helper_tests();

function sabri_static_tests() {
	$main     = sabri_file( 'sabri-unified-application-shell.php' );
	$renderer = sabri_file( 'includes/class-renderer.php' );
	$home     = sabri_file( 'includes/class-home-feed.php' );
	$css      = sabri_file( 'assets/css/shell.css' );
	$js       = sabri_file( 'assets/js/shell.js' );
	$readme   = sabri_file( 'README.md' );
	$changelog= sabri_file( 'CHANGELOG.md' );

	sabri_assert( 'Version consistency', false !== strpos( $main, 'Version: 1.0.0' ) && false !== strpos( $main, "SABRI_SHELL_VERSION', '1.0.0" ) && false !== strpos( $changelog, '1.0.0' ) );
	sabri_assert( 'Plugin header consistency', false !== strpos( $main, 'Plugin Name: Sabri Unified Application Shell' ) && false !== strpos( $main, 'Text Domain: sabri-unified-application-shell' ) );
	sabri_assert( 'README limitations documented', false !== strpos( $readme, 'messaging backend is not created by this plugin' ) && false !== strpos( $readme, 'Hostinger staging testing is required before production activation' ) );
	sabri_assert( 'No wp_body_open-to-wp_footer wrapper', false === strpos( $renderer, '<main' ) && false === strpos( $renderer, '</main>' ) );
	sabri_assert( 'No whole-page output buffering', false === strpos( $renderer, 'ob_start' ) && false === strpos( $home, 'ob_start' ) );
	sabri_assert( 'Exactly one Notifications output marker', 1 === substr_count( $renderer, 'data-sabri-notifications-output' ) );
	sabri_assert( 'Right Sidebar not always rendered', false !== strpos( $renderer, 'Layout::THREE === $mode' ) );
	sabri_assert( 'Home feed duplicate protection', false !== strpos( $home, 'has_shortcode' ) && false !== strpos( $home, '$auto_inserted' ) );
	sabri_assert( 'Role-aware Create', false !== strpos( $renderer, "current_user_can( 'edit_posts' )" ) && false !== strpos( $renderer, "allowed_roles" ) );
	sabri_assert( 'Safe login redirect avoids raw HTTP_HOST', false === strpos( $renderer, 'HTTP_HOST' ) && false !== strpos( $renderer, 'wp_validate_redirect' ) );
	sabri_assert( 'Responsive overflow rules', false !== strpos( $css, 'overflow-x: clip' ) && false !== strpos( $css, 'min-width: 0' ) && false !== strpos( $css, 'env(safe-area-inset-bottom' ) );
	sabri_assert( 'Chrome height JavaScript', false !== strpos( $js, '--sabri-shell-chrome-height' ) && false !== strpos( $js, 'document.fonts.ready' ) );
	sabri_assert( 'Accessible drawers JavaScript', false !== strpos( $js, 'aria-expanded' ) && false !== strpos( $js, 'Escape' ) && false !== strpos( $js, 'inert' ) );
	$all_php = '';
	$all_text = '';
	foreach ( sabri_files( '/\.php$/' ) as $file ) {
		if ( 'tools/run-tests.php' === str_replace( '\\', '/', substr( $file->getPathname(), strlen( dirname( __DIR__ ) ) + 1 ) ) ) {
			continue;
		}
		$all_php .= file_get_contents( $file->getPathname() );
	}
	foreach ( sabri_files( '/\.(php|css|js|md|txt|yml|yaml)$/i' ) as $file ) {
		$all_text .= file_get_contents( $file->getPathname() );
	}
	sabri_assert( 'No external runtime, CDN, or remote font dependencies', 0 === preg_match( '#(cdn\.|fonts\.googleapis|fonts\.gstatic|@import\s+url|https?://(?!github\.com/majidhussainqadri1-dot/sabri-unified-application-shell|example\.test))#i', $all_text ) );
	sabri_assert( 'No bundled font binaries', empty( sabri_files( '/\.(woff2?|ttf|otf|eot)$/i' ) ) );

	$dangerous = array( '\beval\s*\(', '\bshell_exec\s*\(', '\bpassthru\s*\(', '\bproc_open\s*\(', '\bpopen\s*\(', '\bassert\s*\(' );
	$found = array();
	foreach ( $dangerous as $pattern ) {
		if ( preg_match( '/' . $pattern . '/', $all_php ) ) {
			$found[] = $pattern;
		}
	}
	sabri_assert( 'Dangerous PHP function scan', empty( $found ), implode( ', ', $found ) );
	sabri_assert( 'Hard-coded secret scan', 0 === preg_match( '/(ghp_|github_pat_|AKIA[0-9A-Z]{16}|BEGIN PRIVATE KEY|password\s*=\s*[\'"][^\'"]+)/i', $all_php . $readme ) );
}

function sabri_css_json_tests() {
	$css = sabri_file( 'assets/css/shell.css' );
	sabri_assert( 'CSS brace sanity', substr_count( $css, '{' ) === substr_count( $css, '}' ) );

	foreach ( sabri_files( '/\.json$/' ) as $file ) {
		$data = json_decode( file_get_contents( $file->getPathname() ), true );
		sabri_assert( 'JSON validation: ' . $file->getFilename(), JSON_ERROR_NONE === json_last_error() );
	}
}

function sabri_settings_tests() {
	sabri_test_reset();
	$settings = Settings::get();
	$settings['future_setting'] = 'kept';
	$settings['enabled'] = true;
	$settings['header']['enabled'] = true;
	$settings['left_sidebar']['enabled'] = true;
	$settings['right_sidebar']['enabled'] = true;
	update_option( Defaults::OPTION_NAME, $settings, false );

	$saved = Settings::sanitize(
		array(
			'_active_tab' => 'appearance',
			'appearance'  => array(
				'color_mode'    => 'dark',
				'density'       => 'compact',
				'primary_color' => '#f26100',
				'border_radius' => 10,
				'font_scale'    => 1,
			),
		)
	);
	sabri_assert( 'Saving Appearance does not disable Shell', true === $saved['enabled'] );
	sabri_assert( 'Saving Appearance does not disable Header', true === $saved['header']['enabled'] );
	sabri_assert( 'Saving Appearance does not disable either Sidebar', true === $saved['left_sidebar']['enabled'] && true === $saved['right_sidebar']['enabled'] );
	sabri_assert( 'Unknown future settings survive', 'kept' === $saved['future_setting'] );
	sabri_assert( 'Values from other tabs survive', true === $saved['header']['search'] );

	update_option( Defaults::OPTION_NAME, $saved, false );
	$off = Settings::sanitize(
		array(
			'_active_tab' => 'header',
			'header'      => array(
				'enabled'       => '0',
				'platform_title'=> 'Sabri',
				'search'        => '1',
				'create'        => '1',
				'messages'      => '1',
				'notifications' => '1',
				'help'          => '1',
				'language'      => '1',
				'profile'       => '1',
				'allowed_roles' => "administrator\neditor",
			),
		)
	);
	sabri_assert( 'Checkbox can change from ON to OFF on its own tab', false === $off['header']['enabled'] );

	$invalid = Settings::sanitize(
		array(
			'_active_tab' => 'layout',
			'layout'      => array(
				'max_width'             => 99999,
				'left_width'            => 1,
				'right_width'           => 1,
				'gap'                   => 999,
				'theme_content_selector'=> 'body',
				'custom_hide_selectors' => 'script, .safe-selector',
			),
		)
	);
	sabri_assert( 'Sanitization rejects invalid selectors and ranges', '' === $invalid['layout']['theme_content_selector'] && '.safe-selector' === $invalid['layout']['custom_hide_selectors'] && 1600 === $invalid['layout']['max_width'] );
	sabri_assert( 'Sanitization rejects invalid URLs', '' === Settings::sanitize_url( 'javascript:alert(1)' ) && '' === Settings::sanitize_url( 'ftp://example.test/file' ) );
}

function sabri_layout_tests() {
	sabri_test_reset();
	$settings = Settings::get();
	$settings['layout']['worldwide_clinic_page_id'] = 44;
	$settings['layout']['clinic_post_type'] = 'doctor';
	update_option( Defaults::OPTION_NAME, $settings, false );

	$GLOBALS['sabri_test_context']['is_front_page'] = true;
	sabri_assert( 'Layout resolver: Home = three', Layout::THREE === Layout::current_mode() );

	$GLOBALS['sabri_test_context']['is_front_page'] = false;
	$GLOBALS['sabri_test_context']['queried_object_id'] = 44;
	sabri_assert( 'Layout resolver: Clinic directory = three', Layout::THREE === Layout::current_mode() );

	$GLOBALS['sabri_test_context']['queried_object_id'] = 7;
	$GLOBALS['sabri_test_context']['is_singular'] = true;
	$GLOBALS['sabri_test_context']['singular_post_type'] = 'doctor';
	sabri_assert( 'Layout resolver: Single clinic = three', Layout::THREE === Layout::current_mode() );

	$GLOBALS['sabri_test_context']['is_singular'] = false;
	$GLOBALS['sabri_test_context']['queried_object_id'] = 8;
	sabri_assert( 'Layout resolver: Other pages = two', Layout::TWO === Layout::current_mode() );

	$settings['layout']['excluded_page_ids'] = array( 8 );
	update_option( Defaults::OPTION_NAME, $settings, false );
	sabri_assert( 'Layout resolver: excluded page = minimal', Layout::MINIMAL === Layout::current_mode() );

	$GLOBALS['sabri_test_context']['doing_ajax'] = true;
	sabri_assert( 'Layout resolver: system request = minimal', Layout::MINIMAL === Layout::current_mode() );
}

function sabri_safe_snapshot_tests() {
	sabri_test_reset();
	$settings = Settings::get();
	update_option( Defaults::OPTION_NAME, $settings, false );
	sabri_assert( 'Safe Mode off by default', false === SafeMode::disabled() );

	$_GET['sabri_shell_safe'] = '1';
	$GLOBALS['sabri_test_context']['is_user_logged_in'] = true;
	$GLOBALS['sabri_test_context']['current_user_can'] = array( 'manage_options' );
	sabri_assert( 'Safe Mode URL for administrator', true === SafeMode::query_safe_mode() );

	$_GET = array();
	$settings['emergency_disabled'] = true;
	update_option( Defaults::OPTION_NAME, $settings, false );
	sabri_assert( 'Emergency Disable', true === SafeMode::emergency_disabled() );

	sabri_test_reset();
	update_option( Defaults::OPTION_NAME, array( 'enabled' => false, 'layout' => array( 'left_width' => 300 ) ), false );
	Snapshot::capture_activation_snapshot();
	Settings::ensure_defaults();
	$current = Settings::get();
	$current['enabled'] = true;
	update_option( Defaults::OPTION_NAME, $current, false );
	Snapshot::rollback();
	$rolled = get_option( Defaults::OPTION_NAME );
	sabri_assert( 'Activation snapshot before mutation', isset( get_option( Defaults::SNAPSHOT_OPTION_NAME )['settings']['enabled'] ) && false === get_option( Defaults::SNAPSHOT_OPTION_NAME )['settings']['enabled'] );
	sabri_assert( 'Rollback data boundaries', false === $rolled['enabled'] && 300 === $rolled['layout']['left_width'] );
}

function sabri_navigation_tests() {
	sabri_test_reset();
	$GLOBALS['sabri_test_pages'][10] = (object) array( 'ID' => 10, 'post_name' => 'configured', 'post_status' => 'publish', 'post_content' => '' );
	$GLOBALS['sabri_test_pages'][11] = (object) array( 'ID' => 11, 'post_name' => 'shortcode-page', 'post_status' => 'publish', 'post_content' => '[sabri_encyclopedia]' );
	$GLOBALS['sabri_test_pages'][12] = (object) array( 'ID' => 12, 'post_name' => 'doctors', 'post_status' => 'publish', 'post_content' => '' );
	$GLOBALS['sabri_test_post_types']['product'] = true;

	$settings = Settings::get();
	$settings['navigation']['founder']['page_id'] = 10;
	$settings['navigation']['marketplace']['url_override'] = 'https://example.test/shop';
	update_option( Defaults::OPTION_NAME, $settings, false );

	$founder = Navigation::resolve_item( 'founder', Defaults::destinations()['founder'], $settings['navigation']['founder'] );
	$encyclopedia = Navigation::resolve_item( 'encyclopedia', Defaults::destinations()['encyclopedia'], $settings['navigation']['encyclopedia'] );
	$marketplace = Navigation::resolve_item( 'marketplace', Defaults::destinations()['marketplace'], $settings['navigation']['marketplace'] );
	$doctors = Navigation::resolve_item( 'doctors', Defaults::destinations()['doctors'], $settings['navigation']['doctors'] );

	sabri_assert( 'Navigation precedence: configured Page ID', 'configured_page_id' === $founder['reason'] );
	sabri_assert( 'Navigation precedence: shortcode page', 'page_shortcode' === $encyclopedia['reason'] );
	sabri_assert( 'Navigation precedence: post-type archive before URL override', 'post_type_archive' === $marketplace['reason'] );
	sabri_assert( 'Navigation precedence: slug candidate match', 'slug_match' === $doctors['reason'] );

	Navigation::invalidate_cache();
	sabri_assert( 'Navigation cache invalidation', false === get_transient( Defaults::NAV_CACHE_KEY ) );
}

sabri_static_tests();
sabri_css_json_tests();
sabri_settings_tests();
sabri_layout_tests();
sabri_safe_snapshot_tests();
sabri_navigation_tests();

$failed = array_filter(
	$results,
	static function ( $result ) {
		return 'failed' === $result['status'];
	}
);

$report = "# Sabri Unified Application Shell Test Report\n\n";
$report .= "Generated: " . gmdate( 'Y-m-d H:i:s' ) . " UTC\n\n";
$report .= "## Automated and Static Tests\n\n";
foreach ( $results as $result ) {
	$report .= '- [' . ( 'passed' === $result['status'] ? 'x' : ' ' ) . '] ' . $result['name'] . ' - ' . strtoupper( $result['status'] );
	if ( $result['details'] ) {
		$report .= ' (' . $result['details'] . ')';
	}
	$report .= "\n";
}
$report .= "\n## WordPress Stub Tests\n\n";
$report .= "Settings-tab isolation, layout resolver, navigation precedence, Safe Mode, Emergency Disable, and Snapshot/Rollback boundaries ran against local WordPress stubs.\n\n";
$report .= "## Manual Staging Tests Still Required\n\n";
$report .= "- Activate on Hostinger staging before production.\n";
$report .= "- Verify live theme spacing across 320, 360, 390, 480, 768, 900, 1024, 1100, 1280, 1366, 1440, 1600, and 1920 px.\n";
$report .= "- Verify real companion-plugin destinations for messages, notifications, appointments, marketplace, doctors, and clinic pages.\n";
$report .= "- Verify cross-browser behavior manually. This report does not claim live production, live database, Hostinger, or cross-browser testing.\n\n";
$report .= "Messaging backend is not created by this plugin. Real calls are not created. End-to-end encryption is not claimed. Live streaming is not created. AI recommendations are not claimed. Full compatibility with every WordPress theme is not claimed. Hostinger staging testing is required before production activation.\n";

$report_path = null;
foreach ( $argv as $arg ) {
	if ( 0 === strpos( $arg, '--report=' ) ) {
		$report_path = substr( $arg, 9 );
	}
}

if ( $report_path ) {
	$dir = dirname( $report_path );
	if ( ! is_dir( $dir ) ) {
		mkdir( $dir, 0777, true );
	}
	file_put_contents( $report_path, $report );
}

echo $report;

if ( ! empty( $failed ) ) {
	exit( 1 );
}
