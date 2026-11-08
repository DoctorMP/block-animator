=== Animora: Animations for Blocks ===
Contributors: michalpawelczyk
Tags: animation, scroll, blocks
Requires at least: 6.5
Tested up to: 7.1
Stable tag: 1.0.0
License: GPLv2 or later

Add scroll-triggered CSS animations to any block in the WordPress block editor.

== Description ==

Animora lets you animate any block as it scrolls into view — no shortcodes, no page builder lock-in, just an extra panel in the block editor's settings sidebar.

== What can you do with Animora? ==

* add scroll-triggered animations to blocks
* add attention-seeking (repeated, not triggered) animations to blocks
* choose default animations if you plan to add them in many places
* choose animation timing function, duration and delay
* override the global defaults with per-block settings

== Getting started ==

1. Install the plugin
2. Set global defaults (optional)
3. Add animations to blocks while you edit posts with the editor.

== Frequently Asked Questions ==

= What theme do I need to work with Animora? =
Any theme should be OK. Animora is theme-agnostic.

= Are there any caveats? =
The animations are written in CSS using transforms and opacity. Can work unpredictably if your theme sets transformations or opacity in the block styles.

= Do I need any programming skills to use Animora? =
No. If you know how to edit content in Gutenberg - you're good to go.

= Where can I set the defaults? =
Go to the Settings->Animora page in your WordPress admin and use the settings form.

= Does the plugin load any large resources? =
No. A small CSS file and a few lines of JavaScript to trigger the animations. The JS is going to be removed when the support for animation-timeline gets more popular.

= Does it work with any block, including third-party and nested blocks? =
Yes, core and third-party blocks, in posts, pages, templates, patterns and widgets. The animation goes on the block's outermost element.

= Does it work with dynamic (server-rendered) blocks? =
Yes.

= Does it collect data or contact external services? =
No. Animora doesn't collect any data, including private info. Noe does it send anywhere.

== Plans and ideas for the (nearest) future ==

* Add more animations
* Remove JS when all major browsers have working support for animation-timeline
* Add custom timing functions
* Add animation-timeline option to animation settings

== Changelog ==

= 0.1.0 =
* Initial scaffold.

= 1.0.0 =
* Release