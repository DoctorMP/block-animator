<?php
/**
 * Registrable interface.
 *
 * Every service that hooks into WordPress must implement this contract.
 * This is the foundation for the Dependency Inversion and Interface
 * Segregation principles used throughout the plugin.
 *
 * @package Block Animator
 * @since   0.1.0
 */

namespace BlockAnimator;

/**
 * Interface Registrable
 *
 * @since 0.1.0
 */
interface Registrable {

	/**
	 * Attach all WordPress hooks and filters for this service.
	 *
	 * Called once during plugin bootstrap. Implementations must not
	 * execute logic directly — only add_action() / add_filter() calls.
	 *
	 * @since  0.1.0
	 * @return void
	 */
	public function register(): void;
}
