<?php
/**
 * Build release ZIP, SHA-256, and test report.
 *
 * @package SabriUnifiedApplicationShell
 */

$root        = dirname( __DIR__ );
$slug        = 'sabri-unified-application-shell';
$version     = '1.0.0';
$prefix      = '20-' . $slug . '-' . $version;
$release_dir = $root . '/release';
$zip_path    = $release_dir . '/' . $prefix . '.zip';
$sha_path    = $release_dir . '/' . $prefix . '.sha256';
$report_path = $release_dir . '/' . $prefix . '-TEST-REPORT.md';

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

$zip = new ZipArchive();
if ( true !== $zip->open( $zip_path, ZipArchive::CREATE | ZipArchive::OVERWRITE ) ) {
	fwrite( STDERR, "Unable to create release ZIP.\n" );
	exit( 1 );
}

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

	$zip->addFile( $file->getPathname(), $slug . '/' . $relative );
}

$zip->close();

$verify = new ZipArchive();
if ( true !== $verify->open( $zip_path, ZipArchive::CHECKCONS ) ) {
	fwrite( STDERR, "ZIP CRC verification failed.\n" );
	exit( 1 );
}

$top_levels = array();
$development_paths = array(
	'.github/',
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
