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
	'sabri_shell_news_main',
	'render_official_content_slots',
	'official_home_provider_attached',
	'wp_strip_all_tags',
	'Layout::MINIMAL',
	'sabri_shell_is_news_context',
) as $needle ) {
	$assert( false !== strpos( $home, $needle ), 'Official content-slot contract missing: ' . $needle );
}

$assert( false !== strpos( $home, "add_filter( 'the_content', array( __CLASS__, 'render_official_content_slots' ), 5 )" ), 'Official slots must run before File 21 fallback mounting.' );
$assert( false !== strpos( $home, "add_filter( 'the_content', array( __CLASS__, 'maybe_append_to_front_page' ), 20 )" ), 'Legacy Latest fallback registration is missing.' );
$assert( false !== strpos( $home, 'self::official_home_provider_attached()' ), 'Legacy Latest Feed is not suppressed when File 21 owns Home.' );
$assert( false === stripos( $home, 'update_post(' ), 'File 20 slot rendering must not mutate page content.' );
$assert( false === stripos( $home, 'wp_update_post(' ), 'File 20 slot rendering must not mutate page content.' );

if ( $failures ) { fwrite( STDERR, implode( "\n", $failures ) . "\n" ); exit( 1 ); }
echo "File 20/File 21 official slot tests passed.\n";