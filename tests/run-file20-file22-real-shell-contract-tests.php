<?php
/**
 * Pinned hybrid contract for File 20 Create producer and actual File 22 Shell Bridge.
 *
 * FILE22_ROOT must point to the exact reviewed File 22 source checkout.
 */

declare(strict_types=1);

namespace {
	$file22_root = getenv( 'FILE22_ROOT' );
	if ( ! is_string( $file22_root ) || '' === $file22_root ) {
		fwrite( STDERR, "FILE22_ROOT is required.\n" );
		exit( 1 );
	}
	$file22_root = rtrim( $file22_root, '/\\' );

	$required = array(
		'includes/contracts/interface-adapter.php',
		'includes/contracts/interface-workflow-adapter.php',
		'includes/core/class-permission-resolver.php',
		'includes/core/class-registry.php',
		'includes/integration/class-shell-bridge.php',
	);
	foreach ( $required as $relative ) {
		if ( ! is_file( $file22_root . '/' . $relative ) ) {
			fwrite( STDERR, 'Missing File 22 source: ' . $relative . PHP_EOL );
			exit( 1 );
		}
	}

	define( 'ABSPATH', __DIR__ . '/' );
	define( 'SMC_VERSION', '1.0.1' );
	define( 'SUPC_MIN_SMC_VERSION', '1.0.1' );
	define( 'SUPC_ADAPTER_API_VERSION', '1.0.0' );
	define( 'SABRI_SHELL_CREATE_CONTRACT_VERSION', '1.0.1' );
	define( 'SABRI_SHELL_CREATE_CONTRACT_OWNER', 'sabri-unified-application-shell' );
	define( 'SABRI_SHELL_CREATE_FUNCTIONS_OWNED', true );

	$GLOBALS['real_shell_hooks'] = array();
	$GLOBALS['real_shell_filter_invocations'] = array();
	$GLOBALS['real_shell_options'] = array();
	$GLOBALS['real_shell_current_user'] = (object) array(
		'ID' => 22,
		'roles' => array( 'sabri_verified_doctor' ),
	);
	$GLOBALS['real_shell_logged_in'] = true;
	$GLOBALS['real_shell_status'] = 'verified';
	$GLOBALS['real_shell_caps'] = array(
		22 => array( 'edit_posts', 'sabri_feed_create_posts' ),
	);

	final class WP_Error {
		public function __construct(
			private string $code = '',
			private string $message = '',
			private mixed $data = null
		) {}
		public function get_error_code(): string { return $this->code; }
		public function get_error_message(): string { return $this->message; }
		public function get_error_data(): mixed { return $this->data; }
	}

	function __( $text, $domain = '' ) { unset( $domain ); return $text; }
	function sanitize_key( $key ) { return preg_replace( '/[^a-z0-9_\-]/', '', strtolower( (string) $key ) ); }
	function is_admin() { return false; }
	function is_user_logged_in() { return (bool) $GLOBALS['real_shell_logged_in']; }
	function wp_get_current_user() { return $GLOBALS['real_shell_current_user']; }
	function get_current_user_id() { return (int) $GLOBALS['real_shell_current_user']->ID; }
	function current_user_can( $capability ) { return user_can( get_current_user_id(), $capability ); }
	function user_can( $user_id, $capability ) { return in_array( $capability, $GLOBALS['real_shell_caps'][ (int) $user_id ] ?? array(), true ); }
	function get_userdata( $user_id ) { return (int) $user_id === get_current_user_id() ? $GLOBALS['real_shell_current_user'] : false; }
	function smc_user_status( $user_id ) { return (int) $user_id === get_current_user_id() ? (string) $GLOBALS['real_shell_status'] : 'rejected'; }
	function admin_url( $path = '' ) { return 'https://example.test/wp-admin/' . ltrim( (string) $path, '/' ); }
	function home_url( $path = '' ) { return 'https://example.test/' . ltrim( (string) $path, '/' ); }
	function wp_parse_url( $url ) { return parse_url( (string) $url ); }
	function wp_validate_redirect( $url, $fallback = '' ) { return filter_var( $url, FILTER_VALIDATE_URL ) ? $url : $fallback; }
	function esc_url_raw( $url, $protocols = null ) { unset( $protocols ); return (string) $url; }

	function add_filter( $hook, $callback, $priority = 10, $accepted_args = 1 ) {
		$GLOBALS['real_shell_hooks'][ $hook ][ (int) $priority ][] = array(
			'callback' => $callback,
			'accepted_args' => (int) $accepted_args,
		);
		return true;
	}

	function apply_filters( $hook, $value ) {
		$args = func_get_args();
		array_shift( $args );
		$GLOBALS['real_shell_filter_invocations'][ $hook ] = ( $GLOBALS['real_shell_filter_invocations'][ $hook ] ?? 0 ) + 1;
		$callbacks = $GLOBALS['real_shell_hooks'][ $hook ] ?? array();
		ksort( $callbacks );
		foreach ( $callbacks as $priority_callbacks ) {
			foreach ( $priority_callbacks as $entry ) {
				$args[0] = call_user_func_array( $entry['callback'], array_slice( $args, 0, max( 1, $entry['accepted_args'] ) ) );
			}
		}
		return $args[0];
	}

	function get_option( $key, $default = false ) {
		$value = array_key_exists( $key, $GLOBALS['real_shell_options'] ) ? $GLOBALS['real_shell_options'][ $key ] : $default;
		return apply_filters( 'option_' . $key, $value, $key );
	}
}

namespace Sabri\UniversalComposer\Core {
	final class Safe_Mode {
		public static bool $is_disabled = false;
		public static function disabled(): bool { return self::$is_disabled; }
	}
	final class Page_Resolver {
		public static bool $ready = true;
		public static string $resolved_url = 'https://example.test/create/';
		public static function is_ready(): bool { return self::$ready; }
		public static function url(): string { return self::$ready ? self::$resolved_url : ''; }
	}
}

namespace {
	require_once dirname( __DIR__ ) . '/includes/class-defaults.php';
	require_once dirname( __DIR__ ) . '/includes/class-settings.php';
	require_once dirname( __DIR__ ) . '/includes/class-safe-mode.php';
	require_once dirname( __DIR__ ) . '/includes/class-create-visibility.php';
	require_once $file22_root . '/includes/contracts/interface-adapter.php';
	require_once $file22_root . '/includes/contracts/interface-workflow-adapter.php';
	require_once $file22_root . '/includes/core/class-permission-resolver.php';
	require_once $file22_root . '/includes/core/class-registry.php';
	require_once $file22_root . '/includes/integration/class-shell-bridge.php';
}

namespace Sabri\File20File22RealContract {
	use Sabri\UniversalComposer\Contracts\Adapter;
	final class Test_Adapter implements Adapter {
		public function api_version(): string { return '1.0.0'; }
		public function key(): string { return 'social_publication'; }
		public function label(): string { return 'Social Post'; }
		public function description(): string { return 'Pinned hybrid contract adapter.'; }
		public function group(): string { return 'publishing'; }
		public function icon(): string { return 'admin-post'; }
		public function priority(): int { return 10; }
		public function native_module(): string { return 'sabri-complete-home-news-feed'; }
		public function minimum_native_version(): string { return '1.0.3'; }
		public function required_capability(): string { return 'sabri_feed_create_posts'; }
		public function privacy_classification(): string { return 'public'; }
		public function is_available(): bool { return true; }
		public function can_create( int $user_id ): bool { return 22 === $user_id; }
		public function start_url( int $user_id ): string { return 22 === $user_id ? 'https://example.test/create-post/' : ''; }
	}
}

namespace {
	use Sabri\File20File22RealContract\Test_Adapter;
	use Sabri\UnifiedShell\CreateVisibility;
	use Sabri\UnifiedShell\Defaults;
	use Sabri\UnifiedShell\Settings;
	use Sabri\UniversalComposer\Core\Page_Resolver;
	use Sabri\UniversalComposer\Core\Permission_Resolver;
	use Sabri\UniversalComposer\Core\Registry;
	use Sabri\UniversalComposer\Core\Safe_Mode;
	use Sabri\UniversalComposer\Integration\Shell_Bridge;

	$failures = array();
	$assert = static function ( bool $condition, string $message ) use ( &$failures ): void {
		if ( ! $condition ) { $failures[] = $message; }
	};

	$GLOBALS['real_shell_options'][ Defaults::OPTION_NAME ] = array(
		'header' => array(
			'enabled' => true,
			'create' => true,
			'allowed_roles' => array( 'administrator', 'editor' ),
		),
		'mobile' => array( 'create_or_doctors' => 'create' ),
		'emergency_disabled' => false,
	);

	$permissions = new Permission_Resolver();
	$registry = new Registry( $permissions );
	$assert( true === $registry->register( new Test_Adapter() ), 'Actual File 22 Registry rejected the synthetic adapter collaborator.' );
	CreateVisibility::register();
	$bridge = new Shell_Bridge( $registry );
	$bridge->register();

	$settings = Settings::get();
	$assert( in_array( 'sabri_verified_doctor', $settings['header']['allowed_roles'], true ), 'Actual File 22 Shell Bridge did not authorize the current doctor in File 20.' );
	$assert( 'create' === $settings['mobile']['create_or_doctors'], 'File 20 did not preserve authorized explicit mobile Create.' );
	$assert( CreateVisibility::visible_for_current_user(), 'File 20 producer did not report the pinned hybrid gateway as visible.' );
	$assert( 'https://example.test/create/' === CreateVisibility::create_url(), 'Actual File 22 Shell Bridge did not provide an accepted same-origin HTTPS Create URL.' );

	foreach ( array( 'enabled', 'create' ) as $switch ) {
		$registry->flush_cache();
		$GLOBALS['real_shell_options'][ Defaults::OPTION_NAME ]['header'][ $switch ] = false;
		$GLOBALS['real_shell_filter_invocations']['sabri_shell_can_show_create'] = 0;
		$settings = Settings::get();
		$assert( 'doctors' === $settings['mobile']['create_or_doctors'], 'Disabled Header/Create retained mobile Create: ' . $switch );
		$assert( 0 === $GLOBALS['real_shell_filter_invocations']['sabri_shell_can_show_create'], 'File 22 visibility filter ran after disabled Header/Create: ' . $switch );
		$GLOBALS['real_shell_options'][ Defaults::OPTION_NAME ]['header'][ $switch ] = true;
	}

	$registry->flush_cache();
	Safe_Mode::$is_disabled = true;
	$settings = Settings::get();
	$assert( 'doctors' === $settings['mobile']['create_or_doctors'], 'File 22 Safe Mode did not close File 20 mobile Create.' );
	Safe_Mode::$is_disabled = false;

	$registry->flush_cache();
	Page_Resolver::$ready = false;
	$settings = Settings::get();
	$assert( 'doctors' === $settings['mobile']['create_or_doctors'], 'Unavailable File 22 Create page did not close File 20 mobile Create.' );
	Page_Resolver::$ready = true;

	$registry->flush_cache();
	$GLOBALS['real_shell_status'] = 'suspended';
	$settings = Settings::get();
	$assert( 'doctors' === $settings['mobile']['create_or_doctors'], 'Suspended Membership Core subject retained File 20 Create.' );
	$GLOBALS['real_shell_status'] = 'verified';

	$registry->flush_cache();
	$GLOBALS['real_shell_caps'][22] = array( 'edit_posts' );
	$settings = Settings::get();
	$assert( 'doctors' === $settings['mobile']['create_or_doctors'], 'Missing central adapter capability retained File 20 Create.' );

	$registry->flush_cache();
	$GLOBALS['real_shell_caps'][22] = array( 'sabri_feed_create_posts' );
	$GLOBALS['real_shell_filter_invocations']['sabri_shell_can_show_create'] = 0;
	$settings = Settings::get();
	$assert( 'doctors' === $settings['mobile']['create_or_doctors'], 'Missing native edit_posts retained File 20 Create.' );
	$assert( 0 === $GLOBALS['real_shell_filter_invocations']['sabri_shell_can_show_create'], 'File 22 visibility filter ran before File 20 native capability safeguard.' );

	$GLOBALS['real_shell_caps'][22] = array( 'edit_posts', 'sabri_feed_create_posts' );
	$GLOBALS['real_shell_options'][ Defaults::OPTION_NAME ]['emergency_disabled'] = true;
	$settings = Settings::get();
	$assert( 'doctors' === $settings['mobile']['create_or_doctors'], 'File 20 Emergency Disable did not close the pinned hybrid gateway.' );
	$assert( ! CreateVisibility::contract_available(), 'File 20 contract remained available during Emergency Disable.' );

	if ( $failures ) {
		fwrite( STDERR, implode( PHP_EOL, $failures ) . PHP_EOL );
		exit( 1 );
	}
	echo "Pinned hybrid File 20 and File 22 Shell contract passed.\n";
}
