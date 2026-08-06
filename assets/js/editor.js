/**
 * Block Animator — editor script.
 *
 * Adds animation attributes to every block, and a "Scroll Animation"
 * Inspector Controls panel to edit them. A block is animated in one of
 * two mutually exclusive ways (baAnimationType): a scroll-triggered
 * animation (played once, on entering the viewport) or a repeated
 * attention-seeker animation (looped on a fixed interval, no trigger).
 * Switching between the two keeps each mode's own settings intact.
 *
 * Written as plain ES5 against the wp.* globals (no build step) — uses
 * wp.element.createElement instead of JSX.
 */
( function ( wp ) {
	'use strict';

	var addFilter = wp.hooks.addFilter;
	var createHigherOrderComponent = wp.compose.createHigherOrderComponent;
	var InspectorControls = wp.blockEditor.InspectorControls;
	var PanelBody = wp.components.PanelBody;
	var SelectControl = wp.components.SelectControl;
	var TextControl = wp.components.TextControl;
	var el = wp.element.createElement;
	var Fragment = wp.element.Fragment;
	var __ = wp.i18n.__;

	var data = window.blockAnimatorData || {
		scrollAnimations: {},
		attentionAnimations: {},
		timingFunctions: {},
		defaults: {},
	};

	/**
	 * Convert a { slug: label } map into a SelectControl options array.
	 *
	 * @param {Object} map Slug-to-label map.
	 * @return {Array} options
	 */
	function toOptions( map ) {
		return Object.keys( map ).map( function ( slug ) {
			return { value: slug, label: map[ slug ] };
		} );
	}

	var animationTypeOptions = [
		{ value: 'scroll', label: __( 'Scroll-triggered animation', 'block-animator' ) },
		{ value: 'repeat', label: __( 'Repeated animation', 'block-animator' ) },
	];

	var scrollAnimationOptions = toOptions( data.scrollAnimations );
	var attentionAnimationOptions = toOptions( data.attentionAnimations );
	var timingOptions = toOptions( data.timingFunctions );

	/**
	 * Add the animation attributes to every block type.
	 *
	 * @param {Object} settings Block settings.
	 * @return {Object} settings
	 */
	function addAnimationAttributes( settings ) {
		settings.attributes = Object.assign( {}, settings.attributes, {
			baAnimationType: {
				type: 'string',
				default: 'scroll',
			},
			baAnimation: {
				type: 'string',
				default: 'none',
			},
			baAnimationDuration: {
				type: 'number',
			},
			baAnimationDelay: {
				type: 'number',
			},
			baAnimationTimingFunction: {
				type: 'string',
				default: 'default',
			},
			baRepeatAnimation: {
				type: 'string',
				default: 'none',
			},
			baRepeatDuration: {
				type: 'number',
			},
		} );

		return settings;
	}

	addFilter( 'blocks.registerBlockType', 'block-animator/attributes', addAnimationAttributes );

	/**
	 * Build the controls shown for scroll-triggered animations.
	 *
	 * @param {Object}   attributes    Block attributes.
	 * @param {Function} setAttributes Block attribute setter.
	 * @return {Array} control elements
	 */
	function scrollControls( attributes, setAttributes ) {
		var durationValue = attributes.baAnimationDuration === undefined ? '' : String( attributes.baAnimationDuration );
		var delayValue = attributes.baAnimationDelay === undefined ? '' : String( attributes.baAnimationDelay );

		return [
			el( SelectControl, {
				key: 'animation',
				label: __( 'Animation', 'block-animator' ),
				value: attributes.baAnimation,
				options: scrollAnimationOptions,
				onChange: function ( value ) {
					setAttributes( { baAnimation: value } );
				},
			} ),
			el( TextControl, {
				key: 'duration',
				type: 'number',
				label: __( 'Duration (ms)', 'block-animator' ),
				placeholder: String( data.defaults.duration ),
				value: durationValue,
				onChange: function ( value ) {
					setAttributes( { baAnimationDuration: '' === value ? undefined : Number( value ) } );
				},
			} ),
			el( TextControl, {
				key: 'delay',
				type: 'number',
				label: __( 'Delay (ms)', 'block-animator' ),
				placeholder: String( data.defaults.delay ),
				value: delayValue,
				onChange: function ( value ) {
					setAttributes( { baAnimationDelay: '' === value ? undefined : Number( value ) } );
				},
			} ),
			el( SelectControl, {
				key: 'timing',
				label: __( 'Timing function', 'block-animator' ),
				value: attributes.baAnimationTimingFunction,
				options: timingOptions,
				onChange: function ( value ) {
					setAttributes( { baAnimationTimingFunction: value } );
				},
			} ),
		];
	}

	/**
	 * Build the controls shown for repeated (attention-seeker) animations.
	 *
	 * @param {Object}   attributes    Block attributes.
	 * @param {Function} setAttributes Block attribute setter.
	 * @return {Array} control elements
	 */
	function repeatControls( attributes, setAttributes ) {
		var durationValue = attributes.baRepeatDuration === undefined ? '' : String( attributes.baRepeatDuration );

		return [
			el( SelectControl, {
				key: 'repeat-animation',
				label: __( 'Animation', 'block-animator' ),
				value: attributes.baRepeatAnimation,
				options: attentionAnimationOptions,
				onChange: function ( value ) {
					setAttributes( { baRepeatAnimation: value } );
				},
			} ),
			el( TextControl, {
				key: 'repeat-duration',
				type: 'number',
				label: __( 'Duration (ms)', 'block-animator' ),
				help: __( 'Number of milliseconds for one iteration of the animation and interval combined.', 'block-animator' ),
				placeholder: String( data.defaults.repeatDuration ),
				value: durationValue,
				onChange: function ( value ) {
					setAttributes( { baRepeatDuration: '' === value ? undefined : Number( value ) } );
				},
			} ),
		];
	}

	var withAnimationControls = createHigherOrderComponent( function ( BlockEdit ) {
		return function ( props ) {
			var attributes = props.attributes;
			var setAttributes = props.setAttributes;

			if ( ! attributes || undefined === attributes.baAnimationType ) {
				return el( BlockEdit, props );
			}

			var animationType = attributes.baAnimationType || 'scroll';
			var modeControls = 'repeat' === animationType
				? repeatControls( attributes, setAttributes )
				: scrollControls( attributes, setAttributes );

			var panelChildren = [
				el( SelectControl, {
					key: 'animation-type',
					label: __( 'Animation type', 'block-animator' ),
					value: animationType,
					options: animationTypeOptions,
					onChange: function ( value ) {
						setAttributes( { baAnimationType: value } );
					},
				} ),
			].concat( modeControls );

			return el(
				Fragment,
				{},
				el( BlockEdit, props ),
				el(
					InspectorControls,
					{},
					el.apply(
						null,
						[
							PanelBody,
							{ title: __( 'Animation', 'block-animator' ), initialOpen: false },
						].concat( panelChildren )
					)
				)
			);
		};
	}, 'withAnimationControls' );

	addFilter( 'editor.BlockEdit', 'block-animator/controls', withAnimationControls );
} )( window.wp );
