/**
 * Block Animator — frontend script.
 *
 * Watches every element with the ba-has-scroll-animation class and adds
 * ba-animated to it the first time it scrolls into the viewport. Each
 * element is unobserved right after triggering, so the animation plays
 * once per page load. Elements already in the viewport at page load are
 * animated immediately, without waiting for a scroll — see isInViewport().
 */
( function () {
	'use strict';

	/**
	 * Whether any part of an element is currently within the viewport.
	 * Deliberately looser than the observer's own 0.1 threshold below —
	 * this only runs once, at page load, to decide "does this need a
	 * scroll at all", so erring toward animating a barely-visible
	 * element immediately is the right tradeoff.
	 *
	 * @param {Element} element Element to check.
	 * @return {boolean} in viewport
	 */
	function isInViewport( element ) {
		var rect = element.getBoundingClientRect();
		var viewportHeight = window.innerHeight || document.documentElement.clientHeight;
		var viewportWidth = window.innerWidth || document.documentElement.clientWidth;

		return rect.bottom > 0 && rect.top < viewportHeight && rect.right > 0 && rect.left < viewportWidth;
	}

	/**
	 * Set up the IntersectionObserver and start watching elements.
	 *
	 * @return {void}
	 */
	function initBlockAnimator() {
		var elements = document.querySelectorAll( '.ba-has-scroll-animation' );

		if ( ! elements.length ) {
			return;
		}

		if ( ! ( 'IntersectionObserver' in window ) ) {
			elements.forEach( function ( element ) {
				element.classList.add( 'ba-animated' );
			} );
			return;
		}

		var observer = new IntersectionObserver( function ( entries, obs ) {
			entries.forEach( function ( entry ) {
				if ( entry.isIntersecting ) {
					entry.target.classList.add( 'ba-animated' );
					obs.unobserve( entry.target );
				}
			} );
		}, {
			threshold: 0.1,
		} );

		elements.forEach( function ( element ) {
			// Rely on an upfront check rather than the observer's own
			// initial callback for elements already on screen — that
			// callback is spec'd to fire immediately, but isn't
			// reliably prompt in practice once the script runs from
			// the footer while layout is still settling (fonts/images).
			if ( isInViewport( element ) ) {
				element.classList.add( 'ba-animated' );
				return;
			}

			observer.observe( element );
		} );
	}

	if ( 'loading' === document.readyState ) {
		document.addEventListener( 'DOMContentLoaded', initBlockAnimator );
	} else {
		initBlockAnimator();
	}
} )();
