<?php
/**
 * Timing function registry.
 *
 * Single source of truth for the list of available CSS timing functions,
 * shared by both the per-block Inspector panel and the site-wide
 * settings page.
 *
 * @package Block Animator
 * @since   0.1.0
 */

namespace BlockAnimator;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Timing_Functions
 *
 * @since 0.1.0
 */
class Timing_Functions {

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
	 * Full timing function registry.
	 *
	 * @since  0.1.0
	 * @return array<string, array{label: string, site_default: bool}>
	 */
	public static function all(): array {
		static $functions = null;

		if ( null === $functions ) {
			$functions = array(
				self::DEFAULT_SENTINEL => array(
					'label'        => __( 'Default', 'block-animator' ),
					'site_default' => false,
				),
				'ease'                 => array(
					'label'        => __( 'Ease', 'block-animator' ),
					'site_default' => true,
				),
				'ease-in'              => array(
					'label'        => __( 'Ease In', 'block-animator' ),
					'site_default' => true,
				),
				'ease-out'             => array(
					'label'        => __( 'Ease Out', 'block-animator' ),
					'site_default' => true,
				),
				'ease-in-out'          => array(
					'label'        => __( 'Ease In Out', 'block-animator' ),
					'site_default' => true,
				),
				'linear'               => array(
					'label'        => __( 'Linear', 'block-animator' ),
					'site_default' => true,
				),
			);
		}

		return $functions;
	}

	/**
	 * Slug => label for every choice, including "Default" — used by the
	 * per-block Inspector panel.
	 *
	 * @since  0.1.0
	 * @return array<string, string>
	 */
	public static function choices_for_block(): array {
		return wp_list_pluck( self::all(), 'label' );
	}

	/**
	 * Slug => label for choices valid as the site-wide default — excludes
	 * the "Default" sentinel, which would be self-referential.
	 *
	 * @since  0.1.0
	 * @return array<string, string>
	 */
	public static function choices_for_site_default(): array {
		$eligible = array_filter(
			self::all(),
			static fn( array $function ): bool => $function['site_default']
		);

		return wp_list_pluck( $eligible, 'label' );
	}

	/**
	 * Whether a slug is a recognised timing function (including "default").
	 *
	 * @since  0.1.0
	 * @param  string $slug Slug to check.
	 * @return bool
	 */
	public static function is_valid( string $slug ): bool {
		return isset( self::all()[ $slug ] );
	}
}
