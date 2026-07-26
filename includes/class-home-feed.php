<?php
/**
 * Home Feed compatibility and official content slots.
 *
 * @package SabriUnifiedApplicationShell
 */

namespace Sabri\UnifiedShell;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/** Provides canonical Shell slots while retaining the legacy Latest shortcode. */
final class HomeFeed {
	private static $auto_inserted = false;
	private static $official_slots_rendered = false;

	/** Register hooks. */
	public static function register() {
		add_shortcode( 'sabri_shell_home_feed', array( __CLASS__, 'shortcode' ) );
		// Official File 20 slots run before File 21's backward-compatible fallback.
		add_filter( 'the_content', array( __CLASS__, 'render_official_content_slots' ), 5 );
		add_filter( 'the_content', array( __CLASS__, 'maybe_append_to_front_page' ), 20 );
		add_filter( 'sabri_shell_rendering_slots', array( __CLASS__, 'advertise_rendering_slots' ) );
	}

	/** Legacy shortcode callback. */
	public static function shortcode( $atts ) {
		$settings = Settings::get();
		$atts = shortcode_atts( array( 'count' => $settings['home_feed']['posts_count'] ), $atts, 'sabri_shell_home_feed' );
		return self::render( absint( $atts['count'] ) );
	}

	/** Machine-readable rendering-slot contract for File 21 diagnostics. */
	public static function advertise_rendering_slots( $slots ) {
		$slots = is_array( $slots ) ? $slots : array();
		$official = array(
			'sabri_shell_home_before_main' => 'action',
			'sabri_shell_home_main' => 'action',
			'sabri_shell_home_after_main' => 'action',
			'sabri_shell_home_right_sidebar' => 'action',
			'sabri_shell_news_main' => 'action',
		);
		if ( array_is_list( $slots ) ) {
			$slots = array_fill_keys( array_filter( array_map( 'sanitize_key', $slots ) ), 'action' );
		}
		return array_merge( $slots, $official );
	}

	/**
	 * Render versioned Home/News slots into the theme's main content stream.
	 *
	 * File 20 owns placement. File 21 and later modules own the content emitted
	 * into these actions. The original database page content is never mutated.
	 */
	public static function render_official_content_slots( $content ) {
		if ( self::$official_slots_rendered || ! is_string( $content ) || ! self::is_main_public_content_request() ) {
			return $content;
		}
		$is_home_context = function_exists( 'is_front_page' ) && is_front_page();
		$is_news_context = self::is_news_context();
		if ( ! $is_home_context && ! $is_news_context ) {
			return $content;
		}
		self::$official_slots_rendered = true;
		ob_start();
		if ( $is_home_context ) {
			do_action( 'sabri_shell_home_before_main' );
			echo '<section class="sabri-shell-content-slot sabri-shell-content-slot--home" data-sabri-shell-slot="home-main">';
			do_action( 'sabri_shell_home_main' );
			echo '</section>';
			self::render_home_right_sidebar_slot();
			do_action( 'sabri_shell_home_after_main' );
		} else {
			echo '<section class="sabri-shell-content-slot sabri-shell-content-slot--news" data-sabri-shell-slot="news-main">';
			do_action( 'sabri_shell_news_main' );
			echo '</section>';
		}
		$slot = (string) ob_get_clean();
		if ( '' === trim( wp_strip_all_tags( $slot ) ) && false === strpos( $slot, 'data-sabri-' ) ) {
			return $content;
		}
		return $content . $slot;
	}

	/** Render a semantic Home right-sidebar slot only when a provider is attached. */
	private static function render_home_right_sidebar_slot() {
		if ( ! function_exists( 'has_action' ) || false === has_action( 'sabri_shell_home_right_sidebar' ) ) {
			return;
		}
		if ( class_exists( __NAMESPACE__ . '\\Layout' ) && ! Layout::right_sidebar_allowed() ) {
			return;
		}
		ob_start();
		do_action( 'sabri_shell_home_right_sidebar' );
		$output = (string) ob_get_clean();
		if ( '' === trim( wp_strip_all_tags( $output ) ) && false === strpos( $output, 'data-sabri-' ) ) {
			return;
		}
		echo '<aside class="sabri-shell-content-slot sabri-shell-content-slot--home-right" aria-label="' . esc_attr__( 'Home context', 'sabri-unified-application-shell' ) . '" data-sabri-shell-slot="home-right-sidebar">';
		echo $output; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Providers own escaped slot output.
		echo '</aside>';
	}

	/** Append the legacy Shell Latest Feed only when no official provider is attached. */
	public static function maybe_append_to_front_page( $content ) {
		$settings = Settings::get();
		if ( self::$auto_inserted || empty( $settings['home_feed']['auto_insert'] ) || self::official_home_provider_attached() ) {
			return $content;
		}
		if ( ! function_exists( 'is_front_page' ) || ! is_front_page() || ( function_exists( 'is_home' ) && is_home() ) ) {
			return $content;
		}
		if ( ! in_the_loop() || ! is_main_query() || has_shortcode( $content, 'sabri_shell_home_feed' ) ) {
			return $content;
		}
		self::$auto_inserted = true;
		return $content . self::render( absint( $settings['home_feed']['posts_count'] ) );
	}

	/** Render chronological legacy Latest Feed. */
	public static function render( $count = 10 ) {
		$count = $count ? min( 20, max( 1, $count ) ) : 10;
		$post_types = apply_filters( 'sabri_shell_home_feed_post_types', array( 'post' ) );
		$post_types = array_values(
			array_filter(
				array_map( 'sanitize_key', (array) $post_types ),
				static function ( $post_type ) { return 'post' === $post_type || post_type_exists( $post_type ); }
			)
		);
		if ( empty( $post_types ) ) {
			$post_types = array( 'post' );
		}
		$paged = max( 1, absint( get_query_var( 'paged' ) ? get_query_var( 'paged' ) : get_query_var( 'page' ) ) );
		$query = new \WP_Query(
			array(
				'post_type' => $post_types,
				'post_status' => 'publish',
				'posts_per_page' => $count,
				'paged' => $paged,
				'ignore_sticky_posts' => true,
			)
		);
		$html = '<section class="sabri-shell-home-feed" aria-labelledby="sabri-shell-home-feed-title">';
		$html .= '<h2 id="sabri-shell-home-feed-title">' . esc_html__( 'Latest', 'sabri-unified-application-shell' ) . '</h2>';
		if ( $query->have_posts() ) {
			$html .= '<div class="sabri-shell-feed-list">';
			while ( $query->have_posts() ) {
				$query->the_post();
				$html .= self::render_feed_card();
			}
			$html .= '</div>';
			$links = paginate_links(
				array(
					'total' => $query->max_num_pages,
					'current' => $paged,
					'type' => 'list',
					'prev_text' => __( 'Previous', 'sabri-unified-application-shell' ),
					'next_text' => __( 'Next', 'sabri-unified-application-shell' ),
				)
			);
			if ( $links ) {
				$html .= '<nav class="sabri-shell-feed-pagination" aria-label="' . esc_attr__( 'Latest posts pagination', 'sabri-unified-application-shell' ) . '">' . wp_kses_post( $links ) . '</nav>';
			}
		} else {
			$html .= '<p>' . esc_html__( 'No public posts are available yet.', 'sabri-unified-application-shell' ) . '</p>';
		}
		$html .= '</section>';
		wp_reset_postdata();
		return $html;
	}

	/** Whether an external canonical Home provider is attached. */
	public static function official_home_provider_attached() {
		return function_exists( 'has_action' ) && false !== has_action( 'sabri_shell_home_main' );
	}

	/** Reset request guards for integration tests. */
	public static function reset_runtime_guards() {
		self::$auto_inserted = false;
		self::$official_slots_rendered = false;
	}

	/** Validate main-loop public content. */
	private static function is_main_public_content_request() {
		if ( function_exists( 'is_admin' ) && is_admin() ) {
			return false;
		}
		if ( function_exists( 'in_the_loop' ) && ! in_the_loop() ) {
			return false;
		}
		if ( function_exists( 'is_main_query' ) && ! is_main_query() ) {
			return false;
		}
		return Layout::MINIMAL !== Layout::current_mode();
	}

	/** News route detection remains filterable for File 21 canonical routes. */
	private static function is_news_context() {
		$is_news = false;
		if ( function_exists( 'is_post_type_archive' ) && is_post_type_archive( 'sabri_news' ) ) {
			$is_news = true;
		} elseif ( function_exists( 'is_singular' ) && is_singular( 'sabri_news' ) ) {
			$is_news = true;
		} elseif ( function_exists( 'get_query_var' ) && get_query_var( 'sabri_news_route' ) ) {
			$is_news = true;
		}
		return (bool) apply_filters( 'sabri_shell_is_news_context', $is_news );
	}

	/** Render one legacy Feed card. */
	private static function render_feed_card() {
		$html = '<article class="sabri-shell-feed-card">';
		if ( has_post_thumbnail() ) {
			$html .= '<a class="sabri-shell-feed-image" href="' . esc_url( get_permalink() ) . '" aria-hidden="true" tabindex="-1">';
			$html .= get_the_post_thumbnail( get_the_ID(), 'medium_large', array( 'loading' => 'lazy' ) );
			$html .= '</a>';
		}
		$html .= '<div class="sabri-shell-feed-body"><h3><a href="' . esc_url( get_permalink() ) . '">' . esc_html( get_the_title() ) . '</a></h3>';
		$html .= '<p class="sabri-shell-feed-meta">' . esc_html( get_the_author() ) . ' &middot; ' . esc_html( get_the_date() );
		$categories = get_the_category_list( ', ' );
		if ( $categories ) {
			$html .= ' &middot; ' . wp_kses_post( $categories );
		}
		$html .= '</p><div class="sabri-shell-feed-excerpt">' . wp_kses_post( wpautop( get_the_excerpt() ) ) . '</div></div></article>';
		return $html;
	}
}
