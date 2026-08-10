<?php
/**
 * Plugin bootstrap.
 *
 * Depends on the Registrable abstraction, not on concrete classes.
 * This satisfies the Dependency Inversion Principle: the boot sequence
 * is decoupled from the individual services it boots.
 *
 * To add a new service, implement Registrable and pass an instance to
 * the constructor in block-animator.php — no changes needed here.
 *
 * @package Block Animator
 * @since   0.1.0
 */

namespace BlockAnimator;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Bootstrap
 *
 * @since 0.1.0
 */
class Bootstrap {

	/**
	 * Registrable services to boot.
	 *
	 * @since 0.1.0
	 * @var   Registrable[]
	 */
	private array $registrables;

	/**
	 * Constructor.
	 *
	 * @since 0.1.0
	 * @param Registrable[] $registrables Array of registrable services.
	 */
	public function __construct( array $registrables ) {
		$this->registrables = $registrables;
	}

	/**
	 * Call register() on every service.
	 *
	 * @since  0.1.0
	 * @return void
	 */
	public function boot(): void {
		foreach ( $this->registrables as $registrable ) {
			$registrable->register();
		}
	}
}
