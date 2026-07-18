<?php
/**
 * WordPress test stubs for the Sabri Shell test suite.
 *
 * @package SabriUnifiedApplicationShell
 */

if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', __DIR__ . '/' );
}

if ( ! defined( 'HOUR_IN_SECONDS' ) ) {
	define( 'HOUR_IN_SECONDS', 3600 );
}

$GLOBALS['sabri_test_options']    = array();
$GLOBALS['sabri_test_transients'] = array();
$GLOBALS['sabri_test_context']    = array(
	'is_admin'              => false,
	'is_front_page'         => false,
	'is_home'               => false,
	'is_singular'           => false,
	'singular_post_type'    => '',
	'queried_object_id'     => 0,
	'is_feed'               => false,
	'is_embed'              => false,
	'is_preview'            => false,
	'is_customize_preview'  => false,
	'is_robots'             => false,
	'is_favicon'            => false,
	'is_trackback'          => false,
	'doing_ajax'            => false,
	'doing_cron'            => false,
	'post_password_required'=> false,
	'is_ssl'                => true,
	'is_user_logged_in'     => false,
	'current_user_can'      => array(),
	'current_user'          => (object) array(
		'ID'           => 1,
		'display_name' => 'Test User',
		'roles'        => array(),
	),
);
$GLOBALS['sabri_test_pages']      = array();
$GLOBALS['sabri_test_post_types'] = array( 'post' => true );

function sabri_test_reset() {
	$GLOBALS['sabri_test_options']    = array();
	$GLOBALS['sabri_test_transients'] = array();
	$GLOBALS['sabri_test_pages']      = array();
	$GLOBALS['sabri_test_post_types'] = array( 'post' => true );
	$GLOBALS['sabri_test_context']    = array_merge(
		$GLOBALS['sabri_test_context'],
		array(
			'is_admin'              => false,
			'is_front_page'         => false,
			'is_home'               => false,
			'is_singular'           => false,
			'singular_post_type'    => '',
			'queried_object_id'     => 0,
			'is_feed'               => false,
			'is_embed'              => false,
			'is_preview'            => false,
			'is_customize_preview'  => false,
			'is_robots'             => false,
			'is_favicon'            => false,
			'is_trackback'          => false,
			'doing_ajax'            => false,
			'doing_cron'            => false,
			'post_password_required'=> false,
			'is_ssl'                => true,
			'is_user_logged_in'     => false,
			'current_user_can'      => array(),
			'current_user'          => (object) array(
				'ID'           => 1,
				'display_name' => 'Test User',
				'roles'        => array(),
			),
		)
	);
	$_GET = array();
	$_SERVER['REQUEST_URI'] = '/';
}

function __( $text, $domain = null ) { return $text; }
function esc_html__( $text, $domain = null ) { return $text; }
function esc_attr__( $text, $domain = null ) { return $text; }
function esc_html( $text ) { return htmlspecialchars( (string) $text, ENT_QUOTES, 'UTF-8' ); }
function esc_attr( $text ) { return htmlspecialchars( (string) $text, ENT_QUOTES, 'UTF-8' ); }
function esc_textarea( $text ) { return htmlspecialchars( (string) $text, ENT_NOQUOTES, 'UTF-8' ); }
function esc_url( $url ) { return (string) $url; }
function esc_url_raw( $url, $protocols = null ) { return (string) $url; }
function wp_kses_post( $html ) { return (string) $html; }
function sanitize_key( $key ) { return strtolower( preg_replace( '/[^a-zA-Z0-9_\-]/', '', (string) $key ) ); }
function sanitize_title( $title ) { return trim( strtolower( preg_replace( '/[^a-zA-Z0-9]+/', '-', (string) $title ) ), '-' ); }
function sanitize_text_field( $text ) { return trim( preg_replace( '/[\r\n\t]+/', ' ', strip_tags( (string) $text ) ) ); }
function sanitize_textarea_field( $text ) { return trim( strip_tags( (string) $text ) ); }
function sanitize_html_class( $class ) { return sanitize_key( $class ); }
function wp_unslash( $value ) { return $value; }
function absint( $value ) { return abs( (int) $value ); }
function checked( $checked, $current = true, $echo = true ) { $out = $checked == $current ? 'checked="checked"' : ''; if ( $echo ) { echo $out; } return $out; }
function selected( $selected, $current = true, $echo = true ) { $out = $selected == $current ? 'selected="selected"' : ''; if ( $echo ) { echo $out; } return $out; }
function register_setting() {}
function add_action() {}
function add_filter() {}
function do_action() {}
function apply_filters( $hook, $value ) { return $value; }
function add_shortcode() {}
function shortcode_atts( $pairs, $atts ) { return array_merge( $pairs, (array) $atts ); }
function has_shortcode( $content, $tag ) { return false !== strpos( (string) $content, '[' . $tag ); }
function shortcode_exists( $tag ) { return false; }
function get_option( $key, $default = false ) { return array_key_exists( $key, $GLOBALS['sabri_test_options'] ) ? $GLOBALS['sabri_test_options'][ $key ] : $default; }
function update_option( $key, $value, $autoload = null ) { $GLOBALS['sabri_test_options'][ $key ] = $value; return true; }
function delete_option( $key ) { unset( $GLOBALS['sabri_test_options'][ $key ] ); return true; }
function get_transient( $key ) { return array_key_exists( $key, $GLOBALS['sabri_test_transients'] ) ? $GLOBALS['sabri_test_transients'][ $key ] : false; }
function set_transient( $key, $value, $ttl = 0 ) { $GLOBALS['sabri_test_transients'][ $key ] = $value; return true; }
function delete_transient( $key ) { unset( $GLOBALS['sabri_test_transients'][ $key ] ); return true; }
function current_time( $type, $gmt = false ) { return '2026-07-18 00:00:00'; }
function is_admin() { return $GLOBALS['sabri_test_context']['is_admin']; }
function wp_doing_ajax() { return $GLOBALS['sabri_test_context']['doing_ajax']; }
function wp_doing_cron() { return $GLOBALS['sabri_test_context']['doing_cron']; }
function is_front_page() { return $GLOBALS['sabri_test_context']['is_front_page']; }
function is_home() { return $GLOBALS['sabri_test_context']['is_home']; }
function is_feed() { return $GLOBALS['sabri_test_context']['is_feed']; }
function is_embed() { return $GLOBALS['sabri_test_context']['is_embed']; }
function is_preview() { return $GLOBALS['sabri_test_context']['is_preview']; }
function is_customize_preview() { return $GLOBALS['sabri_test_context']['is_customize_preview']; }
function is_robots() { return $GLOBALS['sabri_test_context']['is_robots']; }
function is_favicon() { return $GLOBALS['sabri_test_context']['is_favicon']; }
function is_trackback() { return $GLOBALS['sabri_test_context']['is_trackback']; }
function is_singular( $post_type = '' ) { return $GLOBALS['sabri_test_context']['is_singular'] && ( ! $post_type || $post_type === $GLOBALS['sabri_test_context']['singular_post_type'] ); }
function post_password_required() { return $GLOBALS['sabri_test_context']['post_password_required']; }
function get_queried_object_id() { return $GLOBALS['sabri_test_context']['queried_object_id']; }
function is_user_logged_in() { return $GLOBALS['sabri_test_context']['is_user_logged_in']; }
function current_user_can( $cap ) { return in_array( $cap, $GLOBALS['sabri_test_context']['current_user_can'], true ); }
function wp_get_current_user() { return $GLOBALS['sabri_test_context']['current_user']; }
function wp_http_validate_url( $url ) { return (bool) preg_match( '#^https?://#', $url ); }
function home_url( $path = '/' ) { return 'https://example.test' . ( 0 === strpos( $path, '/' ) ? $path : '/' . $path ); }
function admin_url( $path = '' ) { return 'https://example.test/wp-admin/' . ltrim( $path, '/' ); }
function wp_login_url( $redirect = '' ) { return 'https://example.test/wp-login.php?redirect_to=' . rawurlencode( $redirect ); }
function wp_registration_url() { return 'https://example.test/wp-login.php?action=register'; }
function wp_logout_url( $redirect = '' ) { return 'https://example.test/wp-login.php?action=logout'; }
function wp_get_referer() { return false; }
function wp_validate_redirect( $location, $fallback = '' ) { return 0 === strpos( $location, 'https://example.test' ) ? $location : $fallback; }
function wp_parse_url( $url, $component = -1 ) { return parse_url( $url, $component ); }
function untrailingslashit( $value ) { return rtrim( $value, '/' ); }
function get_bloginfo( $show = '' ) { return '6.6'; }
function wp_get_theme() { return new class() { public function get( $key ) { return 'Stub Theme'; } }; }
function wp_is_block_theme() { return true; }
function is_ssl() { return $GLOBALS['sabri_test_context']['is_ssl']; }
function get_posts( $args = array() ) { return array_values( $GLOBALS['sabri_test_pages'] ); }
function get_page_by_path( $slug ) { foreach ( $GLOBALS['sabri_test_pages'] as $page ) { if ( $page->post_name === $slug ) { return $page; } } return null; }
function get_post_status( $post ) { $id = is_object( $post ) ? $post->ID : $post; foreach ( $GLOBALS['sabri_test_pages'] as $page ) { if ( (int) $page->ID === (int) $id ) { return $page->post_status; } } return 'publish'; }
function get_permalink( $post = null ) { $id = is_object( $post ) ? $post->ID : ( $post ? $post : 0 ); return 'https://example.test/page-' . $id . '/'; }
function get_post_type_archive_link( $post_type ) { return post_type_exists( $post_type ) ? 'https://example.test/' . $post_type . '/' : false; }
function post_type_exists( $post_type ) { return ! empty( $GLOBALS['sabri_test_post_types'][ $post_type ] ); }
function wp_roles() { return (object) array( 'roles' => array( 'administrator' => array(), 'editor' => array(), 'doctor' => array(), 'verified_doctor' => array() ) ); }

require_once __DIR__ . '/../includes/class-defaults.php';
require_once __DIR__ . '/../includes/class-settings.php';
require_once __DIR__ . '/../includes/class-safe-mode.php';
require_once __DIR__ . '/../includes/class-snapshot.php';
require_once __DIR__ . '/../includes/class-layout.php';
require_once __DIR__ . '/../includes/class-integrations.php';
require_once __DIR__ . '/../includes/class-navigation.php';
