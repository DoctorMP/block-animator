<?php
/**
 * Editor assets.
 *
 * Enqueues the block-editor script that adds scroll-animation attributes
 * and an Inspector Controls panel to every block, and passes it the
 * shared animation/timing registries plus the resolved site defaults.
 *
 * @package Block Animator
 * @since   0.1.0
 */

namespace BlockAnimator;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Editor_Assets
 *
 * @since 0.1.0
 */
class Editor_Assets implements Registrable {

	/**
	 * {@inheritDoc}
	 *
	 * @since  0.1.0
	 * @return void
	 */
	public function register(): void {
		add_action( 'enqueue_block_editor_assets', array( $this, 'enqueue' ) );
	}

	/**
	 * Enqueue the editor script and localize its data.
	 *
	 * @since  0.1.0
	 * @return void
	 */
	public function enqueue(): void {
		$file = BLOCK_ANIMATOR_PLUGIN_PATH . 'assets/js/editor.js';

		if ( ! file_exists( $file ) ) {
			return;
		}

		wp_enqueue_script(
			'block-animator-editor',
			BLOCK_ANIMATOR_PLUGIN_URL . 'assets/js/editor.js',
			array( 'wp-blocks', 'wp-block-editor', 'wp-components', 'wp-compose', 'wp-element', 'wp-hooks', 'wp-i18n' ),
			(string) filemtime( $file ),
			true
		);

		wp_localize_script(
			'block-animator-editor',
			'blockAnimatorData',
			array(
				'scrollAnimations'    => Animations::choices_for_block( Animations::CATEGORY_SCROLL ),
				'attentionAnimations' => Animations::choices_for_block( Animations::CATEGORY_ATTENTION ),
				'timingFunctions'     => Timing_Functions::choices_for_block(),
				'defaults'            => array(
					'animation'       => Settings::get_default_animation(),
					'duration'        => Settings::get_default_duration(),
					'delay'           => Settings::get_default_delay(),
					'timingFunction'  => Settings::get_default_timing_function(),
					'repeatAnimation' => Settings::get_default_repeat_animation(),
					'repeatDuration'  => Settings::get_default_repeat_duration(),
					'repeatInterval'  => Settings::get_default_repeat_interval(),
				),
			)
		);

		// Explicit path, not relying on WordPress's automatic (no-path)
		// call for block.json "textdomain" fields — this plugin has none
		// (it doesn't register its own blocks), and even where that
		// mechanism applies elsewhere, an explicit path avoids depending
		// on WP_Textdomain_Registry already being populated by the time
		// this script registers. See form-blocks' block loader for the
		// same reasoning in more detail.
		wp_set_script_translations( 'block-animator-editor', 'block-animator', BLOCK_ANIMATOR_PLUGIN_PATH . 'languages' );
	}
}
