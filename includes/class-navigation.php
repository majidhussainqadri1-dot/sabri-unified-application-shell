<?php
/**
 * Navigation resolution and cache invalidation.
 *
 * @package SabriUnifiedApplicationShell
 */

namespace Sabri\UnifiedShell;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Resolves shell navigation against real WordPress destinations.
 */
final class Navigation {
	/**
	 * Register cache invalidation hooks.
	 *
	 * @return void
	 */
	public static function register_cache_hooks() {
		add_action( 'update_option_' . Defaults::OPTION_NAME, array( __CLASS__, 'invalidate_cache' ) );
		add_action( 'save_post_page', array( __CLASS__, 'invalidate_cache' ) );
		add_action( 'trashed_post', array( __CLASS__, 'invalidate_cache' ) );
		add_action( 'untrashed_post', array( __CLASS__, 'invalidate_cache' ) );
		add_action( 'deleted_post', array( __CLASS__, 'invalidate_cache' ) );
		add_action( 'registered_post_type', array( __CLASS__, 'invalidate_cache' ) );
		add_action( 'update_option_permalink_structure', array( __CLASS__, 'invalidate_cache' ) );
	}

	/**
	 * Delete resolved navigation cache.
	 *
	 * @return void
	 */
	public static function invalidate_cache() {
		delete_transient( Defaults::NAV_CACHE_KEY );
	}

	/**
	 * Get resolved navigation.
	 *
	 * @return array<string,array<string,mixed>>
	 */
	public static function resolved() {
		$cached = get_transient( Defaults::NAV_CACHE_KEY );
		if ( is_array( $cached ) ) {
			return $cached;
		}

		$settings     = Settings::get();
		$destinations = apply_filters( 'sabri_shell_navigation_destinations', Defaults::destinations() );
		$items        = array();

		foreach ( $destinations as $key => $destination ) {
			$config = isset( $settings['navigation'][ $key ] ) ? $settings['navigation'][ $key ] : array();
			if ( isset( $config['enabled'] ) && ! $config['enabled'] ) {
				continue;
			}

			$item = self::resolve_item( $key, $destination, $config );
			if ( ! empty( $item['url'] ) ) {
				$items[ $key ] = $item;
			}
		}

		uasort(
			$items,
			static function ( $a, $b ) {
				return (int) $a['order'] <=> (int) $b['order'];
			}
		);

		set_transient( Defaults::NAV_CACHE_KEY, $items, HOUR_IN_SECONDS );

		return $items;
	}

	/**
	 * Resolve one navigation item using required precedence.
	 *
	 * @param string              $key Destination key.
	 * @param array<string,mixed> $destination Destination defaults.
	 * @param array<string,mixed> $config Admin config.
	 * @return array<string,mixed>
	 */
	public static function resolve_item( $key, array $destination, array $config ) {
		$label = ! empty( $config['label'] ) ? $config['label'] : $destination['label'];
		$order = isset( $config['order'] ) ? (int) $config['order'] : ( isset( $destination['order'] ) ? (int) $destination['order'] : 999 );
		$item  = array(
			'key'    => $key,
			'label'  => $label,
			'url'    => '',
			'reason' => 'unresolved',
			'order'  => $order,
			'group'  => isset( $destination['group'] ) ? $destination['group'] : 'platform',
		);

		$page_id = isset( $config['page_id'] ) ? absint( $config['page_id'] ) : 0;
		if ( $page_id && self::published_page_url( $page_id ) ) {
			$item['url']    = self::published_page_url( $page_id );
			$item['reason'] = 'configured_page_id';
			return $item;
		}

		$shortcodes = array();
		if ( ! empty( $config['shortcode'] ) ) {
			$shortcodes[] = sanitize_key( $config['shortcode'] );
		}
		if ( ! empty( $destination['shortcodes'] ) && is_array( $destination['shortcodes'] ) ) {
			$shortcodes = array_merge( $shortcodes, $destination['shortcodes'] );
		}
		$shortcode_url = self::find_page_by_shortcodes( array_filter( array_unique( $shortcodes ) ) );
		if ( $shortcode_url ) {
			$item['url']    = $shortcode_url;
			$item['reason'] = 'page_shortcode';
			return $item;
		}

		$post_type = isset( $destination['post_type'] ) ? sanitize_key( $destination['post_type'] ) : '';
		if ( $post_type && 'post' !== $post_type && post_type_exists( $post_type ) ) {
			$archive = get_post_type_archive_link( $post_type );
			if ( $archive ) {
				$item['url']    = $archive;
				$item['reason'] = 'post_type_archive';
				return $item;
			}
		}

		if ( 'post' === $post_type ) {
			$posts_page = (int) get_option( 'page_for_posts' );
			if ( $posts_page && self::published_page_url( $posts_page ) ) {
				$item['url']    = self::published_page_url( $posts_page );
				$item['reason'] = 'posts_page';
				return $item;
			}
			$item['url']    = home_url( '/' );
			$item['reason'] = 'post_archive_fallback';
			return $item;
		}

		$slugs = array();
		if ( ! empty( $config['slug'] ) ) {
			$slugs[] = sanitize_title( $config['slug'] );
		}
		if ( ! empty( $destination['slugs'] ) && is_array( $destination['slugs'] ) ) {
			$slugs = array_merge( $slugs, $destination['slugs'] );
		}
		$slug_url = self::find_page_by_slugs( array_filter( array_unique( $slugs ) ) );
		if ( $slug_url ) {
			$item['url']    = $slug_url;
			$item['reason'] = 'slug_match';
			return $item;
		}

		if ( ! empty( $config['url_override'] ) ) {
			$url = Settings::sanitize_url( $config['url_override'] );
			if ( $url ) {
				$item['url']    = $url;
				$item['reason'] = 'url_override';
				return $item;
			}
		}

		if ( 'home' === $key ) {
			$item['url']    = home_url( '/' );
			$item['reason'] = 'home_url';
		}

		return $item;
	}

	/**
	 * Return URL for a published page.
	 *
	 * @param int $page_id Page ID.
	 * @return string
	 */
	private static function published_page_url( $page_id ) {
		if ( ! $page_id || 'publish' !== get_post_status( $page_id ) ) {
			return '';
		}

		$url = get_permalink( $page_id );
		return $url ? $url : '';
	}

	/**
	 * Find a published page containing any shortcode.
	 *
	 * @param array<int,string> $shortcodes Shortcode names.
	 * @return string
	 */
	private static function find_page_by_shortcodes( array $shortcodes ) {
		if ( empty( $shortcodes ) ) {
			return '';
		}

		$pages = get_posts(
			array(
				'post_type'      => 'page',
				'post_status'    => 'publish',
				'posts_per_page' => 50,
				'fields'         => 'all',
				'orderby'        => 'menu_order',
				'order'          => 'ASC',
			)
		);

		foreach ( $pages as $page ) {
			$content = isset( $page->post_content ) ? $page->post_content : '';
			foreach ( $shortcodes as $shortcode ) {
				if ( has_shortcode( $content, $shortcode ) ) {
					return get_permalink( $page );
				}
			}
		}

		return '';
	}

	/**
	 * Find a published page by slug candidates.
	 *
	 * @param array<int,string> $slugs Slug candidates.
	 * @return string
	 */
	private static function find_page_by_slugs( array $slugs ) {
		foreach ( $slugs as $slug ) {
			$page = get_page_by_path( $slug );
			if ( $page && 'publish' === get_post_status( $page ) ) {
				return get_permalink( $page );
			}
		}

		return '';
	}

	/**
	 * Check whether URL should be marked active.
	 *
	 * @param string $url URL.
	 * @return bool
	 */
	public static function is_active_url( $url ) {
		$current = self::current_url_path();
		$target  = wp_parse_url( $url, PHP_URL_PATH );
		if ( ! $target ) {
			$target = '/';
		}

		return untrailingslashit( $current ) === untrailingslashit( $target );
	}

	/**
	 * Current request path.
	 *
	 * @return string
	 */
	private static function current_url_path() {
		$uri  = isset( $_SERVER['REQUEST_URI'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REQUEST_URI'] ) ) : '/';
		$path = wp_parse_url( $uri, PHP_URL_PATH );
		return $path ? $path : '/';
	}
}
