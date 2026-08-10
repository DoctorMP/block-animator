<?php
/**
 * Block attributes registrar.
 *
 * Registers the plugin's animation attributes on every block type's
 * PHP-side schema too — not just the JS-side schema added by
 * assets/js/editor.js's `blocks.registerBlockType` filter.
 *
 * Without this, a dynamic block (one with a "render" in block.json,
 * previewed in the editor via <ServerSideRender>) fails to load: the
 * /wp/v2/block-renderer REST endpoint validates the "attributes"
 * request param against that block's PHP-registered schema with
 * additionalProperties:false, so any attribute known only client-side
 * gets rejected with rest_additional_properties_forbidden — surfaced
 * in the editor as a generic "Invalid parameter(s): attributes"
 * block-loading error, with nothing in the JS console since it's a
 * normal (if unhelpfully generic) REST error response, not a thrown
 * exception.
 *
 * The attribute_schema() below must be kept in sync with
 * assets/js/editor.js's addAnimationAttributes() — same keys, types,
 * and defaults.
 *
 * @package Block Animator
 * @since   0.1.0
 */

namespace BlockAnimator;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Block_Attributes
 *
 * @since 0.1.0
 */
class Block_Attributes implements Registrable {

	/**
	 * {@inheritDoc}
	 *
	 * @since  0.1.0
	 * @return void
	 */
	public function register(): void {
		add_filter( 'register_block_type_args', array( $this, 'add_attributes' ) );
	}

	/**
	 * Merge the animation attributes into a block type's registration
	 * args. A block's own attribute definition — however unlikely a
	 * name collision is given the "ba" prefix — always wins.
	 *
	 * @since  0.1.0
	 * @param  array<string, mixed> $args Block type registration args.
	 * @return array<string, mixed>
	 */
	public function add_attributes( array $args ): array {
		$attributes = isset( $args['attributes'] ) && is_array( $args['attributes'] ) ? $args['attributes'] : array();

		$args['attributes'] = array_merge( self::attribute_schema(), $attributes );

		return $args;
	}

	/**
	 * The animation attributes added to every block. Must match
	 * assets/js/editor.js's addAnimationAttributes() exactly.
	 *
	 * @since  0.1.0
	 * @return array<string, array<string, mixed>>
	 */
	private static function attribute_schema(): array {
		return array(
			'baAnimationType'           => array(
				'type'    => 'string',
				'default' => 'scroll',
			),
			'baAnimation'               => array(
				'type'    => 'string',
				'default' => Animations::NONE,
			),
			'baAnimationDuration'       => array(
				'type' => 'number',
			),
			'baAnimationDelay'          => array(
				'type' => 'number',
			),
			'baAnimationTimingFunction' => array(
				'type'    => 'string',
				'default' => Timing_Functions::DEFAULT_SENTINEL,
			),
			'baRepeatAnimation'         => array(
				'type'    => 'string',
				'default' => Animations::NONE,
			),
			'baRepeatDuration'          => array(
				'type' => 'number',
			),
		);
	}
}
