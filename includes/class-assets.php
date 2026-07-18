<?php
/**
 * Asset registration.
 *
 * @package SabriUnifiedApplicationShell
 */

namespace Sabri\UnifiedShell;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Enqueues CSS and JavaScript only for public shell requests.
 */
final class Assets {
	/**
	 * Register public assets.
	 *
	 * @return void
	 */
	public static function enqueue() {
		if ( Layout::MINIMAL === Layout::current_mode() ) {
			return;
		}

		wp_enqueue_style(
			'sabri-shell',
			SABRI_SHELL_URL . 'assets/css/shell.css',
			array(),
			SABRI_SHELL_VERSION
		);

		wp_enqueue_script(
			'sabri-shell',
			SABRI_SHELL_URL . 'assets/js/shell.js',
			array(),
			SABRI_SHELL_VERSION,
			true
		);

		$settings = Settings::get();
		wp_localize_script(
			'sabri-shell',
			'SabriShell',
			array(
				'desktopBreakpoint' => 1024,
				'rightBreakpoint'   => 1200,
				'closeLabel'        => __( 'Close menu', 'sabri-unified-application-shell' ),
				'openLabel'         => __( 'Open menu', 'sabri-unified-application-shell' ),
				'closeContextLabel' => __( 'Close context panel', 'sabri-unified-application-shell' ),
				'openContextLabel'  => __( 'Open context panel', 'sabri-unified-application-shell' ),
				'contentSelector'   => $settings['layout']['theme_content_selector'],
				'contentCandidates' => Layout::content_target_candidates( $settings ),
				'appearance'        => $settings['appearance'],
			)
		);
	}

	/**
	 * Print safe CSS custom properties and optional theme visibility selectors.
	 *
	 * @return void
	 */
	public static function print_custom_properties() {
		if ( Layout::MINIMAL === Layout::current_mode() ) {
			return;
		}

		$settings   = Settings::get();
		$layout     = $settings['layout'];
		$appearance = $settings['appearance'];
		$selectors  = array();

		if ( ! empty( $layout['hide_theme_header'] ) ) {
			$selectors[] = '.site-header';
			$selectors[] = 'header.wp-block-template-part';
		}

		if ( ! empty( $layout['hide_theme_footer'] ) ) {
			$selectors[] = '.site-footer';
			$selectors[] = 'footer.wp-block-template-part';
		}

		if ( ! empty( $layout['custom_hide_selectors'] ) ) {
			$selectors[] = $layout['custom_hide_selectors'];
		}

		?>
		<style id="sabri-shell-vars">
			body.sabri-shell-enabled {
				--sabri-shell-max-width: <?php echo esc_html( absint( $layout['max_width'] ) ); ?>px;
				--sabri-shell-left-width: <?php echo esc_html( absint( $layout['left_width'] ) ); ?>px;
				--sabri-shell-right-width: <?php echo esc_html( absint( $layout['right_width'] ) ); ?>px;
				--sabri-shell-gap: <?php echo esc_html( absint( $layout['gap'] ) ); ?>px;
				--sabri-shell-primary: <?php echo esc_html( $appearance['primary_color'] ); ?>;
				--sabri-shell-radius: <?php echo esc_html( absint( $appearance['border_radius'] ) ); ?>px;
				--sabri-shell-font-scale: <?php echo esc_html( (float) $appearance['font_scale'] ); ?>;
			}
			<?php if ( ! empty( $selectors ) ) : ?>
			body.sabri-shell-enabled <?php echo implode( ', body.sabri-shell-enabled ', $selectors ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- selectors are validated by Settings::sanitize_css_selector_list(). ?> {
				display: none !important;
			}
			<?php endif; ?>
		</style>
		<?php
	}
}
