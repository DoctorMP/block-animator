# Block Animator

A WordPress plugin that adds animations to any block in the block editor — an "Animation" panel appears in every block's settings sidebar, letting you pick one of two animation types:

- **Scroll-triggered** — plays once as the visitor scrolls the block into view (fade in, zoom in, etc.).
- **Repeated** — an "attention seeker" animation (shake, pulse, heartbeat, blink) that loops continuously, no scrolling required.

No shortcodes, no wrapper blocks, no page-builder lock-in.

## Status

Early development. The editor panel, settings page, and the rendering logic all work end-to-end for both animation types, and most of the animations have real `@keyframes` CSS now — a few are still to be written.

## Settings

**Settings → Block Animator** lets an administrator set the site-wide defaults separately for each animation type — used whenever a block's own value is left on "Default" (or, for animation/duration/delay/interval, simply never set).

## Development

The editor script (`assets/js/editor.js`) is plain ES5 against the `wp.*` globals — no build step, no webpack/Babel required.

The frontend stylesheet does need a build step, though: `assets/css/frontend.css` is compiled from the Sass source in `assets/scss/` and isn't committed to the repo, so a fresh clone has no CSS until you build it.

```bash
sass assets/scss/frontend.scss assets/css/frontend.css --no-source-map   # one-off build
sass --watch assets/scss:assets/css --no-source-map                      # rebuild on save
```

Requires [Dart Sass](https://sass-lang.com/dart-sass/) (`sass` on your `PATH`) — there's no `package.json`/npm wrapper for this yet, just the CLI directly.

## Building a release zip

The release zip is what you'd upload via *Plugins → Add New → Upload Plugin*. Run this from the plugin's folder (`wp-content/plugins/block-animator`):

```bash
sh release.sh
```

This compiles the CSS for you (same `sass` command as above — Dart Sass still needs to be on your `PATH`), then zips everything except the Sass source (`assets/scss/`), git files, log files, `release.sh` itself, the `release/` folder, and any `.md` files into `release/block-animator.zip`. No `npm install` step needed — there's no JS build.

The zip's top-level folder is always named `block-animator`, even if your local checkout is named something else (e.g. a GitHub archive download like `block-animator-main`) — the script renames it for the duration of the build and restores your original folder name afterward.
