/**
 * Block Animator — frontend script.
 *
 * Watches every element with the ba-has-scroll-animation class and adds
 * ba-animated to it the first time it scrolls into the viewport. Each
 * element is unobserved right after triggering, so the animation plays
 * once per page load.
 */
( function () {
	'use strict';

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
			observer.observe( element );
		} );
	}

	if ( 'loading' === document.readyState ) {
		document.addEventListener( 'DOMContentLoaded', initBlockAnimator );
	} else {
		initBlockAnimator();
	}
} )();
