<?php
/** File 20 v1.0.1 local and CI regression runner. */

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
	$results[] = array( 'name' => $name, 'status' => $condition ? 'passed' : 'failed', 'details' => $details );
}

function sabri_file( $path ) {
	return (string) file_get_contents( dirname( __DIR__ ) . '/' . $path );
}

/** @return array<int,SplFileInfo> */
function sabri_files( $pattern ) {
	$root = dirname( __DIR__ );
	$files = array();
	$iterator = new RecursiveIteratorIterator( new RecursiveDirectoryIterator( $root, FilesystemIterator::SKIP_DOTS ) );
	foreach ( $iterator as $file ) {
		if ( ! $file instanceof SplFileInfo || ! $file->isFile() ) { continue; }
		$path = str_replace( '\\', '/', substr( $file->getPathname(), strlen( $root ) + 1 ) );
		if ( preg_match( '#^(?:\.git|release|node_modules|vendor)/#', $path ) ) { continue; }
		if ( preg_match( $pattern, $path ) ) { $files[] = $file; }
	}
	return $files;
}

function sabri_static_tests() {
	$main      = sabri_file( 'sabri-unified-application-shell.php' );
	$plugin    = sabri_file( 'includes/class-plugin.php' );
	$create    = sabri_file( 'includes/class-create-visibility.php' );
	$renderer  = sabri_file( 'includes/class-renderer.php' );
	$home      = sabri_file( 'includes/class-home-feed.php' );
	$css       = sabri_file( 'assets/css/shell.css' );
	$js        = sabri_file( 'assets/js/shell.js' );
	$assets    = sabri_file( 'includes/class-assets.php' );
	$layout    = sabri_file( 'includes/class-layout.php' );
	$system    = sabri_file( 'includes/class-system-check.php' );
	$readme    = sabri_file( 'README.md' );
	$wp_readme = sabri_file( 'readme.txt' );
	$change    = sabri_file( 'CHANGELOG.md' );
	$builder   = sabri_file( 'tools/build-release.php' );

	sabri_assert( 'Version consistency', false !== strpos( $main, 'Version: 1.0.1' ) && false !== strpos( $main, "SABRI_SHELL_VERSION', '1.0.1" ) && false !== strpos( $readme, 'Version: 1.0.1' ) && false !== strpos( $wp_readme, 'Stable tag: 1.0.1' ) && false !== strpos( $change, '## 1.0.1' ) && false !== strpos( $builder, "\$version     = '1.0.1'" ) );
	sabri_assert( 'Versioned Create contract', false !== strpos( $main, "SABRI_SHELL_CREATE_CONTRACT_VERSION', '1.0.0" ) && false !== strpos( $main, 'sabri_shell_create_contract_available' ) && false !== strpos( $main, 'sabri_shell_create_visible_for_current_user' ) );
	sabri_assert( 'Create bridge registration order', false !== strpos( $plugin, 'CreateVisibility::register();' ) && strpos( $plugin, 'CreateVisibility::register();' ) < strpos( $plugin, 'Settings::register();' ) );
	sabri_assert( 'Create bridge no-write boundary', false !== strpos( $create, "option_' . Defaults::OPTION_NAME" ) && false === strpos( $create, 'update_option(' ) && false === strpos( $create, 'delete_option(' ) );
	sabri_assert( 'Create bridge fail-closed controls', false !== strpos( $create, 'private static $resolving' ) && false !== strpos( $create, 'SafeMode::disabled()' ) && false !== strpos( $create, "current_user_can( 'edit_posts' )" ) );
	sabri_assert( 'Mobile bypass neutralization', false !== strpos( $create, "['create_or_doctors'] = 'doctors'" ) && false !== strpos( $create, "['create_or_doctors'] = 'auto'" ) );
	sabri_assert( 'README limitations', false !== strpos( $readme, 'messaging backend is not created by this plugin' ) && false !== strpos( $readme, 'Hostinger staging testing is required before production activation' ) );
	sabri_assert( 'No whole-page wrapper or buffering', false === strpos( $renderer, '<main' ) && false === strpos( $renderer, '</main>' ) && false === strpos( $renderer, 'ob_start' ) && false === strpos( $home, 'ob_start' ) );
	sabri_assert( 'One Notifications output marker', 1 === substr_count( $renderer, 'data-sabri-notifications-output' ) );
	sabri_assert( 'Conditional right sidebar', false !== strpos( $renderer, 'Layout::THREE === $mode' ) && false !== strpos( $renderer, 'right_sidebar_has_modules' ) );
	sabri_assert( 'Real responsive columns', false !== strpos( $css, 'grid-template-columns: var(--sabri-shell-left-width, 280px) minmax(0, 1fr) var(--sabri-shell-right-width, 340px);' ) && false !== strpos( $css, 'grid-template-columns: var(--sabri-shell-left-width, 280px) minmax(0, 1fr);' ) );
	sabri_assert( 'Theme content resolver', false !== strpos( $assets, "'contentSelector'" ) && false !== strpos( $layout, 'content_target_candidates' ) && false !== strpos( $js, 'resolveContentTarget' ) && false !== strpos( $system, 'Content target resolver' ) );
	sabri_assert( 'Accessible drawer behavior', false !== strpos( $js, 'aria-expanded' ) && false !== strpos( $js, 'Escape' ) && false !== strpos( $js, 'inert' ) );
	sabri_assert( 'Home duplicate protection', false !== strpos( $home, 'has_shortcode' ) && false !== strpos( $home, '$auto_inserted' ) );
	sabri_assert( 'Safe login redirect', false === strpos( $renderer, 'HTTP_HOST' ) && false !== strpos( $renderer, 'wp_validate_redirect' ) );
	sabri_assert( 'Candidate excludes development paths', false !== strpos( $builder, "'patches/'" ) && false !== strpos( $builder, "'tests/'" ) && false !== strpos( $builder, 'Development-only path found in release ZIP' ) );

	$all_php = '';
	$all_text = '';
	foreach ( sabri_files( '/\.php$/' ) as $file ) {
		$path = str_replace( '\\', '/', substr( $file->getPathname(), strlen( dirname( __DIR__ ) ) + 1 ) );
		if ( 'tools/run-tests.php' === $path ) { continue; }
		$all_php .= file_get_contents( $file->getPathname() );
	}
	foreach ( sabri_files( '/\.(?:php|css|js|md|txt|yml|yaml)$/i' ) as $file ) { $all_text .= file_get_contents( $file->getPathname() ); }
	sabri_assert( 'No remote runtime dependencies', 0 === preg_match( '#(cdn\.|fonts\.googleapis|fonts\.gstatic|@import\s+url|https?://(?!github\.com/majidhussainqadri1-dot/sabri-unified-application-shell|example\.test))#i', $all_text ) );
	sabri_assert( 'No bundled font binaries', empty( sabri_files( '/\.(?:woff2?|ttf|otf|eot)$/i' ) ) );
	$dangerous = array( '\beval\s*\(', '\bshell_exec\s*\(', '\bpassthru\s*\(', '\bproc_open\s*\(', '\bpopen\s*\(', '\bassert\s*\(' );
	$found = array();
	foreach ( $dangerous as $pattern ) { if ( preg_match( '/' . $pattern . '/', $all_php ) ) { $found[] = $pattern; } }
	sabri_assert( 'Dangerous PHP function scan', empty( $found ), implode( ', ', $found ) );
	sabri_assert( 'Hard-coded secret scan', 0 === preg_match( '/(ghp_|github_pat_|AKIA[0-9A-Z]{16}|BEGIN PRIVATE KEY|password\s*=\s*[\'\"][^\'\"]+)/i', $all_php . $readme ) );
	sabri_assert( 'CSS brace sanity', substr_count( $css, '{' ) === substr_count( $css, '}' ) );
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
	$saved = Settings::sanitize( array( '_active_tab' => 'appearance', 'appearance' => array( 'color_mode' => 'dark', 'density' => 'compact', 'primary_color' => '#f26100', 'border_radius' => 10, 'font_scale' => 1 ) ) );
	sabri_assert( 'Settings tab isolation', true === $saved['enabled'] && true === $saved['header']['enabled'] && true === $saved['left_sidebar']['enabled'] && true === $saved['right_sidebar']['enabled'] && 'kept' === $saved['future_setting'] );
	update_option( Defaults::OPTION_NAME, $saved, false );
	$off = Settings::sanitize( array( '_active_tab' => 'header', 'header' => array( 'enabled' => '0', 'platform_title' => 'Sabri', 'search' => '1', 'create' => '1', 'messages' => '1', 'notifications' => '1', 'help' => '1', 'language' => '1', 'profile' => '1', 'allowed_roles' => "administrator\neditor" ) ) );
	sabri_assert( 'Header checkbox can turn off', false === $off['header']['enabled'] );
	$invalid = Settings::sanitize( array( '_active_tab' => 'layout', 'layout' => array( 'max_width' => 99999, 'left_width' => 1, 'right_width' => 1, 'gap' => 999, 'theme_content_selector' => 'body', 'custom_hide_selectors' => 'script, .safe-selector' ) ) );
	sabri_assert( 'Selector and range sanitization', '' === $invalid['layout']['theme_content_selector'] && '.safe-selector' === $invalid['layout']['custom_hide_selectors'] && 1600 === $invalid['layout']['max_width'] );
	sabri_assert( 'Unsafe URL rejection', '' === Settings::sanitize_url( 'javascript:alert(1)' ) && '' === Settings::sanitize_url( 'ftp://example.test/file' ) );
}

function sabri_layout_tests() {
	sabri_test_reset();
	$settings = Settings::get();
	$settings['layout']['worldwide_clinic_page_id'] = 44;
	$settings['layout']['clinic_post_type'] = 'doctor';
	update_option( Defaults::OPTION_NAME, $settings, false );
	$GLOBALS['sabri_test_context']['is_front_page'] = true;
	sabri_assert( 'Home layout is three-column', Layout::THREE === Layout::current_mode() );
	$GLOBALS['sabri_test_context']['is_front_page'] = false;
	$GLOBALS['sabri_test_context']['queried_object_id'] = 44;
	sabri_assert( 'Clinic directory is three-column', Layout::THREE === Layout::current_mode() );
	$GLOBALS['sabri_test_context']['queried_object_id'] = 8;
	sabri_assert( 'Other pages are two-column', Layout::TWO === Layout::current_mode() );
	$settings['layout']['excluded_page_ids'] = array( 8 );
	update_option( Defaults::OPTION_NAME, $settings, false );
	sabri_assert( 'Excluded page is minimal', Layout::MINIMAL === Layout::current_mode() );
	$GLOBALS['sabri_test_context']['doing_ajax'] = true;
	sabri_assert( 'System request is minimal', Layout::MINIMAL === Layout::current_mode() );
}

function sabri_safe_snapshot_tests() {
	sabri_test_reset();
	$settings = Settings::get();
	update_option( Defaults::OPTION_NAME, $settings, false );
	sabri_assert( 'Safe Mode off by default', false === SafeMode::disabled() );
	$_GET['sabri_shell_safe'] = '1';
	$GLOBALS['sabri_test_context']['is_user_logged_in'] = true;
	$GLOBALS['sabri_test_context']['current_user_can'] = array( 'manage_options' );
	sabri_assert( 'Administrator Safe Mode URL', true === SafeMode::query_safe_mode() );
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
	sabri_assert( 'Snapshot and rollback boundaries', false === $rolled['enabled'] && 300 === $rolled['layout']['left_width'] );
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
	sabri_assert( 'Navigation precedence', 'configured_page_id' === $founder['reason'] && 'page_shortcode' === $encyclopedia['reason'] && 'post_type_archive' === $marketplace['reason'] && 'slug_match' === $doctors['reason'] );
	Navigation::invalidate_cache();
	sabri_assert( 'Navigation cache invalidation', false === get_transient( Defaults::NAV_CACHE_KEY ) );
}

sabri_static_tests();
sabri_settings_tests();
sabri_layout_tests();
sabri_safe_snapshot_tests();
sabri_navigation_tests();

$failed = array_filter( $results, static function ( $result ) { return 'failed' === $result['status']; } );
$report = "# Sabri Unified Application Shell 1.0.1 Test Report\n\nGenerated: " . gmdate( 'Y-m-d H:i:s' ) . " UTC\n\n## Automated and Static Tests\n\n";
foreach ( $results as $result ) {
	$report .= '- [' . ( 'passed' === $result['status'] ? 'x' : ' ' ) . '] ' . $result['name'] . ' - ' . strtoupper( $result['status'] );
	if ( $result['details'] ) { $report .= ' (' . $result['details'] . ')'; }
	$report .= "\n";
}
$report .= "\n## Manual staging still required\n\nFiles 00, 20, 21, and 22 must pass role, fallback, Safe Mode, mobile, browser, accessibility, Urdu RTL, backup, and rollback acceptance before merge or deployment.\n";

$report_path = null;
foreach ( $argv as $arg ) { if ( 0 === strpos( $arg, '--report=' ) ) { $report_path = substr( $arg, 9 ); } }
if ( $report_path ) {
	$dir = dirname( $report_path );
	if ( ! is_dir( $dir ) ) { mkdir( $dir, 0777, true ); }
	file_put_contents( $report_path, $report );
}
echo $report;
if ( $failed ) { exit( 1 ); }
