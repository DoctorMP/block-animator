<?php
/**
 * Frontend assets.
 *
 * Enqueues the CSS and JS that drive scroll-triggered animations on the
 * public-facing site. Enqueued unconditionally on every request, since
 * animated blocks can come from post content, template parts, patterns,
 * or widgets alike.
 *
 * @package Block Animator
 * @since   0.1.0
 */

namespace BlockAnimator;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Frontend_Assets
 *
 * @since 0.1.0
 */
class Frontend_Assets implements Registrable {

	/**
	 * {@inheritDoc}
	 *
	 * @since  0.1.0
	 * @return void
	 */
	public function register(): void {
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue' ) );
	}

	/**
	 * Enqueue the frontend stylesheet and script.
	 *
	 * @since  0.1.0
	 * @return void
	 */
	public function enqueue(): void {
		$css_file = BLOCK_ANIMATOR_PLUGIN_PATH . 'assets/css/frontend.css';
		$js_file  = BLOCK_ANIMATOR_PLUGIN_PATH . 'assets/js/frontend.js';

		if ( file_exists( $css_file ) ) {
			wp_enqueue_style(
				'block-animator-frontend',
				BLOCK_ANIMATOR_PLUGIN_URL . 'assets/css/frontend.css',
				array(),
				(string) filemtime( $css_file )
			);

			wp_add_inline_style( 'block-animator-frontend', $this->root_custom_properties() );
		}

		if ( file_exists( $js_file ) ) {
			wp_enqueue_script(
				'block-animator-frontend',
				BLOCK_ANIMATOR_PLUGIN_URL . 'assets/js/frontend.js',
				array(),
				(string) filemtime( $js_file ),
				true
			);
		}
	}

	/**
	 * Build the :root declaration exposing the site-wide animation
	 * defaults as CSS custom properties, so they're available to any CSS
	 * on the page — not just the markup produced by Block_Renderer,
	 * which additionally writes each animated block's own resolved
	 * values inline (see Block_Renderer::inject_custom_properties()).
	 *
	 * Scroll and repeat mode use distinct property names (--ba-duration
	 * vs --ba-repeat-duration) even though both are "a duration" —
	 * they're independent site-wide settings and can't share one :root
	 * slot without one silently overwriting the other.
	 *
	 * @since  0.1.0
	 * @return string
	 */
	private function root_custom_properties(): string {
		return sprintf(
			':root{--ba-duration:%1$dms;--ba-delay:%2$dms;--ba-timing:%3$s;--ba-repeat-duration:%4$dms;}',
			Settings::get_default_duration(),
			Settings::get_default_delay(),
			Settings::get_default_timing_function(),
			Settings::get_default_repeat_duration()
		);
	}
}
