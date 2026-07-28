<?php
/** Static contracts for official File 20/File 21 rendering slots. */
$root = dirname( __DIR__ );
$failures = array();
$assert = static function ( $condition, $message ) use ( &$failures ) { if ( ! $condition ) { $failures[] = $message; } };

$home = file_get_contents( $root . '/includes/class-home-feed.php' );
foreach ( array(
	'sabri_shell_home_before_main',
	'sabri_shell_home_main',
	'sabri_shell_home_after_main',
	'sabri_shell_home_right_sidebar',
	'sabri_shell_news_main',
	'sabri_shell_rendering_slots',
	'advertise_rendering_slots',
	'render_official_content_slots',
	'render_home_right_sidebar_slot',
	'official_home_provider_attached',
	'provider_attached',
	'Layout::right_sidebar_allowed',
	'Layout::MINIMAL',
	'sabri_shell_is_news_context',
	"is_page( 'news' )",
	"is_page( 'sabri-news' )",
	'data-sabri-shell-slot="home-right-sidebar"',
	'is_list_array',
) as $needle ) {
	$assert( false !== strpos( $home, $needle ), 'Official content-slot contract missing: ' . $needle );
}

$assert( false !== strpos( $home, "add_filter( 'the_content', array( __CLASS__, 'render_official_content_slots' ), 5 )" ), 'Official slots must run before File 21 fallback mounting.' );
$assert( false !== strpos( $home, "add_filter( 'the_content', array( __CLASS__, 'maybe_append_to_front_page' ), 20 )" ), 'Legacy Latest fallback registration is missing.' );
$assert( false !== strpos( $home, "add_filter( 'sabri_shell_rendering_slots', array( __CLASS__, 'advertise_rendering_slots' ) )" ), 'Machine-readable slot advertisement is missing.' );
$assert( false !== strpos( $home, 'self::official_home_provider_attached()' ), 'Legacy Latest Feed is not suppressed when File 21 owns Home.' );
$assert( false !== strpos( $home, "self::provider_attached( 'sabri_shell_home_right_sidebar' )" ), 'Empty right-sidebar hook must not create a blank aside.' );
$assert( false === strpos( $home, 'array_is_list' ), 'File 20 declares PHP 7.4 support and may not call array_is_list().' );
$assert( false === strpos( $home, 'ob_start' ), 'Official slot placement must not introduce whole-content output buffering.' );
$assert( false === stripos( $home, 'update_post(' ), 'File 20 slot rendering must not mutate Page content.' );
$assert( false === stripos( $home, 'wp_update_post(' ), 'File 20 slot rendering must not mutate Page content.' );

if ( $failures ) { fwrite( STDERR, implode( "\n", $failures ) . "\n" ); exit( 1 ); }
echo "File 20/File 21 official slot tests passed.\n";
