<?php
/**
 * Home feed shortcode and optional front-page insertion.
 *
 * @package SabriUnifiedApplicationShell
 */

namespace Sabri\UnifiedShell;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Chronological WordPress home feed.
 */
final class HomeFeed {
	/**
	 * Prevent duplicate automatic insertion.
	 *
	 * @var bool
	 */
	private static $auto_inserted = false;

	/**
	 * Register hooks.
	 *
	 * @return void
	 */
	public static function register() {
		add_shortcode( 'sabri_shell_home_feed', array( __CLASS__, 'shortcode' ) );
		add_filter( 'the_content', array( __CLASS__, 'maybe_append_to_front_page' ), 20 );
	}

	/**
	 * Shortcode callback.
	 *
	 * @param array<string,mixed> $atts Attributes.
	 * @return string
	 */
	public static function shortcode( $atts ) {
		$settings = Settings::get();
		$atts     = shortcode_atts(
			array(
				'count' => $settings['home_feed']['posts_count'],
			),
			$atts,
			'sabri_shell_home_feed'
		);

		return self::render( absint( $atts['count'] ) );
	}

	/**
	 * Append feed once to a static front page when enabled.
	 *
	 * @param string $content Existing content.
	 * @return string
	 */
	public static function maybe_append_to_front_page( $content ) {
		$settings = Settings::get();

		if ( self::$auto_inserted || empty( $settings['home_feed']['auto_insert'] ) ) {
			return $content;
		}

		if ( ! function_exists( 'is_front_page' ) || ! is_front_page() || ( function_exists( 'is_home' ) && is_home() ) ) {
			return $content;
		}

		if ( ! in_the_loop() || ! is_main_query() ) {
			return $content;
		}

		if ( has_shortcode( $content, 'sabri_shell_home_feed' ) ) {
			return $content;
		}

		self::$auto_inserted = true;

		return $content . self::render( absint( $settings['home_feed']['posts_count'] ) );
	}

	/**
	 * Render chronological Latest feed.
	 *
	 * @param int $count Posts per page.
	 * @return string
	 */
	public static function render( $count = 10 ) {
		$count      = $count ? min( 20, max( 1, $count ) ) : 10;
		$post_types = apply_filters( 'sabri_shell_home_feed_post_types', array( 'post' ) );
		$post_types = array_values(
			array_filter(
				array_map( 'sanitize_key', (array) $post_types ),
				static function ( $post_type ) {
					return 'post' === $post_type || post_type_exists( $post_type );
				}
			)
		);

		if ( empty( $post_types ) ) {
			$post_types = array( 'post' );
		}

		$paged = max( 1, absint( get_query_var( 'paged' ) ? get_query_var( 'paged' ) : get_query_var( 'page' ) ) );
		$query = new \WP_Query(
			array(
				'post_type'           => $post_types,
				'post_status'         => 'publish',
				'posts_per_page'      => $count,
				'paged'               => $paged,
				'ignore_sticky_posts' => true,
			)
		);

		$html  = '<section class="sabri-shell-home-feed" aria-labelledby="sabri-shell-home-feed-title">';
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
					'total'     => $query->max_num_pages,
					'current'   => $paged,
					'type'      => 'list',
					'prev_text' => __( 'Previous', 'sabri-unified-application-shell' ),
					'next_text' => __( 'Next', 'sabri-unified-application-shell' ),
				)
			);

			if ( $links ) {
				$html .= '<nav class="sabri-shell-feed-pagination" aria-label="' . esc_attr__( 'Latest posts pagination', 'sabri-unified-application-shell' ) . '">';
				$html .= wp_kses_post( $links );
				$html .= '</nav>';
			}
		} else {
			$html .= '<p>' . esc_html__( 'No public posts are available yet.', 'sabri-unified-application-shell' ) . '</p>';
		}

		$html .= '</section>';
		wp_reset_postdata();

		return $html;
	}

	/**
	 * Render one feed card.
	 *
	 * @return string
	 */
	private static function render_feed_card() {
		$html = '<article class="sabri-shell-feed-card">';
		if ( has_post_thumbnail() ) {
			$html .= '<a class="sabri-shell-feed-image" href="' . esc_url( get_permalink() ) . '" aria-hidden="true" tabindex="-1">';
			$html .= get_the_post_thumbnail( get_the_ID(), 'medium_large', array( 'loading' => 'lazy' ) );
			$html .= '</a>';
		}
		$html .= '<div class="sabri-shell-feed-body">';
		$html .= '<h3><a href="' . esc_url( get_permalink() ) . '">' . esc_html( get_the_title() ) . '</a></h3>';
		$html .= '<p class="sabri-shell-feed-meta">';
		$html .= esc_html( get_the_author() ) . ' &middot; ';
		$html .= esc_html( get_the_date() );
		$categories = get_the_category_list( ', ' );
		if ( $categories ) {
			$html .= ' &middot; ' . wp_kses_post( $categories );
		}
		$html .= '</p>';
		$html .= '<div class="sabri-shell-feed-excerpt">' . wp_kses_post( wpautop( get_the_excerpt() ) ) . '</div>';
		$html .= '</div>';
		$html .= '</article>';

		return $html;
	}
}
