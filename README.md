# Block Animator

A WordPress plugin that adds animations to any block in the block editor — a "Scroll Animation" panel appears in every block's settings sidebar, letting you pick one of two animation types:

- **Scroll-triggered** — plays once as the visitor scrolls the block into view (fade in, zoom in, etc.).
- **Repeated** — an "attention seeker" animation (shake, pulse, heartbeat, blink) that loops continuously on a set interval, no scrolling required.

No shortcodes, no wrapper blocks, no page-builder lock-in.

## Status

Early development. The editor panel, settings page, and the class/data-attribute rendering logic all work end-to-end for both animation types. Not done yet: the actual `@keyframes`/animation CSS (blocks currently get the right classes and data attributes, but nothing visibly animates).

## Settings

**Settings → Block Animator** lets an administrator set the site-wide defaults separately for each animation type — used whenever a block's own value is left on "Default" (or, for animation/duration/delay/interval, simply never set).

## Development

No build tooling is set up yet — the editor script (`assets/js/editor.js`) is plain ES5 against the `wp.*` globals, no webpack/Babel step required. Just activate the plugin.
