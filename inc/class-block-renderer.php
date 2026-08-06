<?php
/**
 * Block renderer.
 *
 * Adds animation classes and inline CSS custom property overrides to a
 * block's rendered HTML, resolving "default" values against the
 * site-wide settings. Works on
 * any block — core, third-party, static, or dynamic — since it filters
 * the final rendered markup rather than requiring the block itself to
 * be animation-aware.
 *
 * A block is animated in exactly one of two mutually exclusive ways,
 * chosen via its baAnimationType attribute:
 * - "scroll" (default): a scroll-triggered animation, played once when
 *   the block enters the viewport (see assets/js/frontend.js).
 * - "repeat": an attention-seeker animation, looped continuously on a
 *   fixed interval — pure CSS, no JS trigger involved.
 *
 * @package Block Animator
 * @since   0.1.0
 */

namespace BlockAnimator;

/**
 * Class Block_Renderer
 *
 * @since 0.1.0
 */
class Block_Renderer implements Registrable {

	/**
	 * Marker class watched by assets/js/frontend.js's IntersectionObserver.
	 *
	 * @since 0.1.0
	 * @var   string
	 */
	const SCROLL_MARKER_CLASS = 'ba-has-scroll-animation';

	/**
	 * Marker class for looped attention-seeker animations. Deliberately
	 * not observed by any JS — purely a CSS hook.
	 *
	 * @since 0.1.0
	 * @var   string
	 */
	const REPEAT_MARKER_CLASS = 'ba-has-repeat-animation';

	/**
	 * {@inheritDoc}
	 *
	 * @since  0.1.0
	 * @return void
	 */
	public function register(): void {
		add_filter( 'render_block', array( $this, 'maybe_animate' ), 10, 2 );
	}

	/**
	 * Add animation classes/data attributes to a block's markup, if it
	 * has an animation set (or resolves to one via the site default).
	 *
	 * @since  0.1.0
	 * @param  string               $block_content Rendered block HTML.
	 * @param  array<string, mixed> $block         Parsed block, incl. 'attrs'.
	 * @return string
	 */
	public function maybe_animate( string $block_content, array $block ): string {
		if ( '' === trim( $block_content ) ) {
			return $block_content;
		}

		$attrs = is_array( $block['attrs'] ?? null ) ? $block['attrs'] : array();
		$type  = $attrs['baAnimationType'] ?? 'scroll';

		if ( 'repeat' === $type ) {
			return $this->maybe_animate_repeat( $block_content, $attrs );
		}

		return $this->maybe_animate_scroll( $block_content, $attrs );
	}

	/**
	 * Resolve and inject a scroll-triggered animation.
	 *
	 * @since  0.1.0
	 * @param  string               $block_content Rendered block HTML.
	 * @param  array<string, mixed> $attrs         Block attributes.
	 * @return string
	 */
	private function maybe_animate_scroll( string $block_content, array $attrs ): string {
		$animation = $this->resolve_scroll_animation( $attrs['baAnimation'] ?? Animations::NONE );

		if ( Animations::NONE === $animation ) {
			return $block_content;
		}

		$duration = $this->resolve_ms( $attrs['baAnimationDuration'] ?? null, array( Settings::class, 'get_default_duration' ) );
		$delay    = $this->resolve_ms( $attrs['baAnimationDelay'] ?? null, array( Settings::class, 'get_default_delay' ) );
		$timing   = $this->resolve_timing( $attrs['baAnimationTimingFunction'] ?? Timing_Functions::DEFAULT_SENTINEL );

		return $this->inject_custom_properties(
			$block_content,
			array( self::SCROLL_MARKER_CLASS, 'ba-' . $animation ),
			array(
				'--ba-duration' => $duration . 'ms',
				'--ba-delay'    => $delay . 'ms',
				'--ba-timing'   => $timing,
			)
		);
	}

	/**
	 * Resolve and inject a repeated (attention-seeker) animation.
	 *
	 * @since  0.1.0
	 * @param  string               $block_content Rendered block HTML.
	 * @param  array<string, mixed> $attrs         Block attributes.
	 * @return string
	 */
	private function maybe_animate_repeat( string $block_content, array $attrs ): string {
		$animation = $this->resolve_repeat_animation( $attrs['baRepeatAnimation'] ?? Animations::NONE );

		if ( Animations::NONE === $animation ) {
			return $block_content;
		}

		$duration = $this->resolve_ms( $attrs['baRepeatDuration'] ?? null, array( Settings::class, 'get_default_repeat_duration' ) );

		return $this->inject_custom_properties(
			$block_content,
			array( self::REPEAT_MARKER_CLASS, 'ba-' . $animation ),
			array(
				'--ba-repeat-duration' => $duration . 'ms',
			)
		);
	}

	/**
	 * Resolve the block's raw scroll-animation attribute to a concrete
	 * slug, or Animations::NONE if it should not animate at all.
	 *
	 * @since  0.1.0
	 * @param  mixed $raw Raw attribute value.
	 * @return string
	 */
	private function resolve_scroll_animation( $raw ): string {
		return $this->resolve_animation( $raw, Animations::CATEGORY_SCROLL, array( Settings::class, 'get_default_animation' ) );
	}

	/**
	 * Resolve the block's raw repeat-animation attribute to a concrete
	 * slug, or Animations::NONE if it should not animate at all.
	 *
	 * @since  0.1.0
	 * @param  mixed $raw Raw attribute value.
	 * @return string
	 */
	private function resolve_repeat_animation( $raw ): string {
		return $this->resolve_animation( $raw, Animations::CATEGORY_ATTENTION, array( Settings::class, 'get_default_repeat_animation' ) );
	}

	/**
	 * Shared animation-slug resolution: "none" always wins, "default"
	 * defers to the site setting, anything else is validated against
	 * the given category.
	 *
	 * @since  0.1.0
	 * @param  mixed    $raw             Raw attribute value.
	 * @param  string   $category        Animations::CATEGORY_SCROLL or Animations::CATEGORY_ATTENTION.
	 * @param  callable $default_getter  Returns the resolved site-wide default for this category.
	 * @return string
	 */
	private function resolve_animation( $raw, string $category, callable $default_getter ): string {
		$raw = is_string( $raw ) && '' !== $raw ? $raw : Animations::NONE;

		if ( Animations::NONE === $raw ) {
			return Animations::NONE;
		}

		if ( Animations::DEFAULT_SENTINEL === $raw ) {
			return $default_getter();
		}

		return Animations::is_valid( $raw, $category ) ? $raw : Animations::NONE;
	}

	/**
	 * Resolve a millisecond value (duration/delay/interval), falling
	 * back to the site default when the block didn't set one.
	 *
	 * @since  0.1.0
	 * @param  mixed    $raw            Raw attribute value.
	 * @param  callable $default_getter Returns the resolved site-wide default.
	 * @return int
	 */
	private function resolve_ms( $raw, callable $default_getter ): int {
		return null === $raw || '' === $raw ? $default_getter() : max( 0, (int) $raw );
	}

	/**
	 * Resolve the block's timing function, falling back to the site
	 * default when the block is left on "Default" or holds an
	 * unrecognised value.
	 *
	 * @since  0.1.0
	 * @param  mixed $raw Raw attribute value.
	 * @return string
	 */
	private function resolve_timing( $raw ): string {
		$raw = is_string( $raw ) && '' !== $raw ? $raw : Timing_Functions::DEFAULT_SENTINEL;

		if ( Timing_Functions::DEFAULT_SENTINEL === $raw || ! Timing_Functions::is_valid( $raw ) ) {
			return Settings::get_default_timing_function();
		}

		return $raw;
	}

	/**
	 * Inject classes and CSS custom property overrides (in the `style`
	 * attribute) into a block's root element. Used instead of data
	 * attributes so the values are directly usable from CSS via var() —
	 * no reliance on the still not-widely-supported typed attr().
	 *
	 * The resolved value is always written, whether it came from the
	 * block's own explicit override or from falling back to the site
	 * default — so the element never depends on inheriting a `:root`
	 * value that might not be present.
	 *
	 * @since  0.1.0
	 * @param  string                $html         Rendered block HTML.
	 * @param  string[]              $classes      Classes to add (marker + animation-name).
	 * @param  array<string, string> $custom_props Custom property name => value pairs, e.g. '--ba-duration' => '600ms'.
	 * @return string
	 */
	private function inject_custom_properties( string $html, array $classes, array $custom_props ): string {
		$processor = new \WP_HTML_Tag_Processor( $html );

		if ( ! $processor->next_tag() ) {
			return $html;
		}

		$this->add_classes( $processor, $classes );

		$declarations = array();
		foreach ( $custom_props as $name => $value ) {
			$declarations[] = $name . ':' . $value;
		}

		$existing_style = trim( (string) $processor->get_attribute( 'style' ) );
		$existing_style = '' !== $existing_style ? rtrim( $existing_style, '; ' ) . '; ' : '';

		$processor->set_attribute( 'style', $existing_style . implode( '; ', $declarations ) );

		return $processor->get_updated_html();
	}

	/**
	 * Merge classes into a tag's existing `class` attribute.
	 *
	 * @since  0.1.0
	 * @param  \WP_HTML_Tag_Processor $processor  Tag processor, already positioned on a tag.
	 * @param  string[]               $new_classes Classes to add.
	 * @return void
	 */
	private function add_classes( \WP_HTML_Tag_Processor $processor, array $new_classes ): void {
		$existing_classes = preg_split( '/\s+/', (string) $processor->get_attribute( 'class' ), -1, PREG_SPLIT_NO_EMPTY );
		$classes          = array_unique( array_merge( $existing_classes, $new_classes ) );

		$processor->set_attribute( 'class', implode( ' ', $classes ) );
	}
}
