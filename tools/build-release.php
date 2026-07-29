<?php
/**
 * Build reproducible release ZIP, SHA-256, and test report.
 *
 * @package SabriUnifiedApplicationShell
 */
$root        = dirname( __DIR__ );
$slug        = 'sabri-unified-application-shell';
$version     = '1.0.1';
$prefix      = '20-' . $slug . '-' . $version;
$release_dir = $root . '/release';
$zip_path    = $release_dir . '/' . $prefix . '.zip';
$sha_path    = $release_dir . '/' . $prefix . '.sha256';
$report_path = $release_dir . '/' . $prefix . '-TEST-REPORT.md';
$epoch       = getenv( 'SOURCE_DATE_EPOCH' );
$epoch       = is_string( $epoch ) && ctype_digit( $epoch ) ? (int) $epoch : 1785283200;

if ( $epoch < 315532800 ) {
	fwrite( STDERR, "SOURCE_DATE_EPOCH must be a valid ZIP-era timestamp.\n" );
	exit( 1 );
}

if ( ! is_dir( $release_dir ) ) {
	mkdir( $release_dir, 0777, true );
}

foreach ( glob( $release_dir . '/*' ) as $file ) {
	if ( is_file( $file ) ) {
		unlink( $file );
	}
}

$argv[] = '--report=' . $report_path;
require $root . '/tools/run-tests.php';

$release_allowlist = array(
	'sabri-unified-application-shell.php',
	'includes/',
	'admin/',
	'assets/',
	'languages/',
	'uninstall.php',
	'readme.txt',
	'README.md',
	'CHANGELOG.md',
	'MIGRATION.md',
	'ROLLBACK.md',
	'STAGING-ACCEPTANCE.md',
);

$release_files = array();
$iterator = new RecursiveIteratorIterator( new RecursiveDirectoryIterator( $root, FilesystemIterator::SKIP_DOTS ) );
foreach ( $iterator as $file ) {
	if ( ! $file->isFile() ) {
		continue;
	}

	$relative = str_replace( '\\', '/', substr( $file->getPathname(), strlen( $root ) + 1 ) );
	$allowed  = false;
	foreach ( $release_allowlist as $allowed_path ) {
		$is_directory = '/' === substr( $allowed_path, -1 );
		if ( ( $is_directory && 0 === strpos( $relative, $allowed_path ) ) || ( ! $is_directory && $relative === $allowed_path ) ) {
			$allowed = true;
			break;
		}
	}
	if ( ! $allowed ) {
		continue;
	}
	if ( false !== strpos( $relative, '..' ) ) {
		fwrite( STDERR, "Path traversal candidate rejected: {$relative}\n" );
		exit( 1 );
	}
	$release_files[ $relative ] = $file->getPathname();
}
ksort( $release_files, SORT_STRING );

$zip = new ZipArchive();
if ( true !== $zip->open( $zip_path, ZipArchive::CREATE | ZipArchive::OVERWRITE ) ) {
	fwrite( STDERR, "Unable to create release ZIP.\n" );
	exit( 1 );
}

foreach ( $release_files as $relative => $source_path ) {
	$entry = $slug . '/' . $relative;
	$data  = file_get_contents( $source_path );
	if ( false === $data || ! $zip->addFromString( $entry, $data ) ) {
		fwrite( STDERR, "Unable to add release entry: {$relative}\n" );
		$zip->close();
		exit( 1 );
	}
	if ( method_exists( $zip, 'setMtimeName' ) ) {
		$zip->setMtimeName( $entry, $epoch );
	}
	if ( method_exists( $zip, 'setCompressionName' ) ) {
		$zip->setCompressionName( $entry, ZipArchive::CM_STORE );
	}
	if ( method_exists( $zip, 'setExternalAttributesName' ) ) {
		$zip->setExternalAttributesName( $entry, ZipArchive::OPSYS_UNIX, 0100644 << 16 );
	}
}
$zip->setArchiveComment( '' );
$zip->close();

$verify = new ZipArchive();
if ( true !== $verify->open( $zip_path, ZipArchive::CHECKCONS ) ) {
	fwrite( STDERR, "ZIP CRC verification failed.\n" );
	exit( 1 );
}

$top_levels = array();
$development_paths = array(
	'.github/',
	'patches/',
	'tools/',
	'tests/',
	'TASK_LOG.md',
	'.gitignore',
	'release/',
	'vendor/',
	'node_modules/',
);
for ( $i = 0; $i < $verify->numFiles; $i++ ) {
	$name = $verify->getNameIndex( $i );
	if ( false !== strpos( $name, '../' ) || 0 === strpos( $name, '/' ) ) {
		fwrite( STDERR, "Path traversal rejected in ZIP: {$name}\n" );
		exit( 1 );
	}
	$inside_plugin = preg_replace( '#^' . preg_quote( $slug, '#' ) . '/#', '', $name );
	foreach ( $development_paths as $development_path ) {
		if ( '' !== $development_path && ( $inside_plugin === rtrim( $development_path, '/' ) || 0 === strpos( $inside_plugin, $development_path ) ) ) {
			fwrite( STDERR, "Development-only path found in release ZIP: {$inside_plugin}\n" );
			exit( 1 );
		}
	}
	$stat = $verify->statIndex( $i );
	if ( ! is_array( $stat ) || (int) $stat['mtime'] !== $epoch ) {
		fwrite( STDERR, "Non-reproducible ZIP timestamp found: {$name}\n" );
		exit( 1 );
	}
	$top_levels[ strtok( $name, '/' ) ] = true;
}
$verify->close();

if ( array( $slug ) !== array_keys( $top_levels ) ) {
	fwrite( STDERR, "ZIP must contain exactly one top-level folder.\n" );
	exit( 1 );
}

$sha = hash_file( 'sha256', $zip_path );
file_put_contents( $sha_path, $sha . '  ' . basename( $zip_path ) . PHP_EOL );

echo "Built {$zip_path}\n";
echo "Built {$sha_path}\n";
echo "Built {$report_path}\n";
