<?php
/**
 * Animation registry.
 *
 * Single source of truth for the list of available animations, shared by
 * both the per-block Inspector panel and the site-wide settings page.
 * Animations are grouped into two categories: scroll-triggered animations
 * (played once, when a block scrolls into view) and attention seekers
 * (looped continuously on a fixed interval, no trigger needed). Add a
 * new animation here and it becomes available everywhere its category
 * is used.
 *
 * @package Block Animator
 * @since   0.1.0
 */

namespace BlockAnimator;

/**
 * Class Animations
 *
 * @since 0.1.0
 */
class Animations {

	/**
	 * Slug meaning "no animation".
	 *
	 * @since 0.1.0
	 * @var   string
	 */
	const NONE = 'none';

	/**
	 * Slug meaning "use the site-wide default from the settings page".
	 * Only ever valid as a per-block choice, never as the site default
	 * itself (that would be self-referential).
	 *
	 * @since 0.1.0
	 * @var   string
	 */
	const DEFAULT_SENTINEL = 'default';

	/**
	 * Category: played once, triggered by scrolling into view.
	 *
	 * @since 0.1.0
	 * @var   string
	 */
	const CATEGORY_SCROLL = 'scroll';

	/**
	 * Category: looped continuously on a fixed interval, no trigger.
	 *
	 * @since 0.1.0
	 * @var   string
	 */
	const CATEGORY_ATTENTION = 'attention';

	/**
	 * Full animation registry.
	 *
	 * "category" is null for the "none"/"default" sentinels, meaning
	 * they belong to every category's choice lists.
	 *
	 * @since  0.1.0
	 * @return array<string, array{label: string, site_default: bool, category: string|null}>
	 */
	public static function all(): array {
		static $animations = null;

		if ( null === $animations ) {
			$animations = array(
				self::NONE             => array(
					'label'        => __( 'None', 'block-animator' ),
					'site_default' => true,
					'category'     => null,
				),
				self::DEFAULT_SENTINEL => array(
					'label'        => __( 'Default', 'block-animator' ),
					'site_default' => false,
					'category'     => null,
				),
				'fade-in'              => array(
					'label'        => __( 'Fade In', 'block-animator' ),
					'site_default' => true,
					'category'     => self::CATEGORY_SCROLL,
				),
				'fade-in-up'           => array(
					'label'        => __( 'Fade In Up', 'block-animator' ),
					'site_default' => true,
					'category'     => self::CATEGORY_SCROLL,
				),
				'fade-in-right'        => array(
					'label'        => __( 'Fade In Right', 'block-animator' ),
					'site_default' => true,
					'category'     => self::CATEGORY_SCROLL,
				),
				'fade-in-left'         => array(
					'label'        => __( 'Fade In Left', 'block-animator' ),
					'site_default' => true,
					'category'     => self::CATEGORY_SCROLL,
				),
				'zoom-in'              => array(
					'label'        => __( 'Zoom In', 'block-animator' ),
					'site_default' => true,
					'category'     => self::CATEGORY_SCROLL,
				),
				'zoom-and-fade'        => array(
					'label'        => __( 'Zoom and Fade', 'block-animator' ),
					'site_default' => true,
					'category'     => self::CATEGORY_SCROLL,
				),
				'zoom-and-spin'        => array(
					'label'        => __( 'Zoom and Spin', 'block-animator' ),
					'site_default' => true,
					'category'     => self::CATEGORY_SCROLL,
				),
				'shake'                => array(
					'label'        => __( 'Shake', 'block-animator' ),
					'site_default' => true,
					'category'     => self::CATEGORY_ATTENTION,
				),
				'pulse'                => array(
					'label'        => __( 'Pulse', 'block-animator' ),
					'site_default' => true,
					'category'     => self::CATEGORY_ATTENTION,
				),
				'heartbeat'            => array(
					'label'        => __( 'Heartbeat', 'block-animator' ),
					'site_default' => true,
					'category'     => self::CATEGORY_ATTENTION,
				),
				'blink'                => array(
					'label'        => __( 'Blink', 'block-animator' ),
					'site_default' => true,
					'category'     => self::CATEGORY_ATTENTION,
				),
			);
		}

		return $animations;
	}

	/**
	 * Whether an entry belongs to a category, or is a universal sentinel
	 * ("none"/"default") that belongs to every category.
	 *
	 * @since  0.1.0
	 * @param  array<string, mixed> $entry    Registry entry.
	 * @param  string               $category Category to check against.
	 * @return bool
	 */
	private static function entry_matches_category( array $entry, string $category ): bool {
		return null === $entry['category'] || $category === $entry['category'];
	}

	/**
	 * Slug => label for every choice in a category, including "None" and
	 * "Default" — used by the per-block Inspector panel.
	 *
	 * @since  0.1.0
	 * @param  string $category self::CATEGORY_SCROLL or self::CATEGORY_ATTENTION.
	 * @return array<string, string>
	 */
	public static function choices_for_block( string $category ): array {
		$entries = array_filter(
			self::all(),
			static function ( array $entry ) use ( $category ): bool {
				return self::entry_matches_category( $entry, $category );
			}
		);

		return wp_list_pluck( $entries, 'label' );
	}

	/**
	 * Slug => label for choices valid as the site-wide default within a
	 * category — excludes the "Default" sentinel, which would be
	 * self-referential.
	 *
	 * @since  0.1.0
	 * @param  string $category self::CATEGORY_SCROLL or self::CATEGORY_ATTENTION.
	 * @return array<string, string>
	 */
	public static function choices_for_site_default( string $category ): array {
		$eligible = array_filter(
			self::all(),
			static function ( array $entry ) use ( $category ): bool {
				return $entry['site_default'] && self::entry_matches_category( $entry, $category );
			}
		);

		return wp_list_pluck( $eligible, 'label' );
	}

	/**
	 * Whether a slug is a recognised animation within a category
	 * (including the universal "none"/"default" sentinels).
	 *
	 * @since  0.1.0
	 * @param  string $slug     Slug to check.
	 * @param  string $category self::CATEGORY_SCROLL or self::CATEGORY_ATTENTION.
	 * @return bool
	 */
	public static function is_valid( string $slug, string $category ): bool {
		$all = self::all();

		return isset( $all[ $slug ] ) && self::entry_matches_category( $all[ $slug ], $category );
	}
}
