# GutenBoing

WordPress plugin that adds animations to blocks. Rather than registering its own block(s), it hooks into the block editor so an "Scroll Animation" panel appears in the settings sidebar of *any* block (core or third-party) — the chosen animation is stored as block attributes and rendered on the frontend via the plugin's own CSS and JS.

A block is animated in exactly one of two mutually exclusive modes, chosen per-block via `gutenboingAnimationType`:
- **Scroll-triggered** (`scroll`, the default) — plays once, when the block scrolls into the viewport.
- **Repeated** (`repeat`) — an "attention seeker" animation that loops continuously, no trigger involved. Pure CSS once written.

## Architecture

### Directory layout

```
gutenboing.php                    Plugin entry point — requires inc/ files, boots GutenBoing\Bootstrap
inc/
  interface-registrable.php           Registrable contract — register(): void
  class-bootstrap.php                 Calls register() on every injected service
  class-animations.php                Single source of truth for the animation list — slug, label, site-default eligibility, and category (scroll vs attention)
  class-timing-functions.php          Same pattern for CSS timing functions (scroll mode only — repeat mode has no timing function)
  class-settings.php                  Settings → GutenBoing admin page — separate sections for scroll defaults and repeat defaults
  class-block-attributes.php          register_block_type_args filter — mirrors the JS-added attributes into every block's PHP-side schema too (see "Why PHP also needs to know" below)
  class-editor-assets.php             Enqueues assets/js/editor.js, localizes the registries + resolved defaults
  class-frontend-assets.php           Enqueues assets/css/frontend.css + assets/js/frontend.js on every page; also emits the :root custom-property defaults (see below)
  class-block-renderer.php            render_block filter — the actual class/style injection logic, branches on gutenboingAnimationType
assets/
  js/editor.js                        Block editor script — no build step, plain wp.* globals + wp.element.createElement
  js/frontend.js                      IntersectionObserver — adds gutenboing-animated once per scroll-mode element per page load. Never touches repeat-mode elements.
  scss/                                Sass source (auto-compiled by an external watcher — see project-level memory) → assets/css/frontend.css
```

All classes live under the `GutenBoing` PHP namespace (no class-name prefixing needed). This mirrors the `mp` theme's `Registrable`/bootstrap pattern rather than `form-blocks`' older prefixed-class convention — to add a new service, implement `GutenBoing\Registrable` and pass an instance into the array in `gutenboing.php`; `Bootstrap` needs no changes.

### Why PHP also needs to know about the attributes (not just JS)

`assets/js/editor.js` adds the animation attributes to every block type via the `blocks.registerBlockType` JS filter — that alone is enough for the attributes to persist (they serialize into the block's comment-delimiter JSON) and for `Block_Renderer` to read them (it parses that raw JSON directly, not through any schema).

But a **dynamic** block (one with a `"render"` in its `block.json`) previews in the editor via `<ServerSideRender>`, which calls `/wp/v2/block-renderer/{block}` and sends *all* of the block's current attributes. That REST endpoint validates the `attributes` param against a schema built from the block's **PHP-registered** attributes, with `additionalProperties: false` — so any attribute known only client-side gets rejected with `rest_additional_properties_forbidden`, surfacing in the editor as a generic, JS-console-silent "Invalid parameter(s): attributes" block-loading error. This broke `mp/featured-posts` in the `mp` theme the first time a dynamic block was edited after this plugin was installed.

`Block_Attributes` fixes this by hooking `register_block_type_args` (the same filter core itself uses to auto-inject the implicit `align` attribute) and merging the identical attribute schema into *every* block's PHP-side registration. **Its `attribute_schema()` must be kept in sync with `editor.js`'s `addAnimationAttributes()` by hand** — same keys, types, and defaults — since there's no shared build step to generate both from one source.

### Why scroll and repeat use separate attributes

Scroll mode: `gutenboingAnimation`, `gutenboingAnimationDuration`, `gutenboingAnimationDelay`, `gutenboingAnimationTimingFunction`.
Repeat mode: `gutenboingRepeatAnimation`, `gutenboingRepeatDuration` (no timing function, no delay — duration is the full loop cycle length).

These are deliberately two independent attribute sets rather than one shared set reinterpreted by mode — switching `gutenboingAnimationType` back and forth in the editor never clobbers the other mode's stored settings, and `Block_Renderer` never has to guess which validity domain (scroll animation slugs vs. attention-seeker slugs) a raw value belongs to.

### How a block gets animated

1. **Editor**: `assets/js/editor.js` filters `blocks.registerBlockType` to add the attributes (mode switch + both attribute sets) to *every* block type, and filters `editor.BlockEdit` to inject a "Scroll Animation" `InspectorControls` panel. The panel always shows an "Animation type" select first; the controls below it swap between the scroll set (animation/duration/delay/timing) and the repeat set (animation/duration) based on its value. Written as plain ES5 against `wp.*` globals (`wp.element.createElement` instead of JSX) — no build step, no `@wordpress/scripts` dependency yet.
2. **PHP schema**: `Block_Attributes` mirrors the same attributes into every block's server-side registration (see above) — required for `<ServerSideRender>`-previewed dynamic blocks to keep working, not needed for `Block_Renderer` itself.
3. **Frontend render**: `Block_Renderer::maybe_animate()` hooks `render_block` (fires for every block, top-level or nested, dynamic or static) and reads `$block['attrs']` directly from the parsed comment JSON. It branches on `gutenboingAnimationType` (`repeat` vs. anything else, defaulting to scroll) into `maybe_animate_scroll()` / `maybe_animate_repeat()`. If the resolved animation is `none`, the markup is returned untouched (zero footprint on blocks that were never touched). Otherwise it uses `WP_HTML_Tag_Processor` to add a marker class + `gutenboing-{animation}` to the root element's `class`, plus mode-specific values:
   - **Scroll**: `gutenboing-has-scroll-animation` class, and the resolved duration/delay/timing written as CSS custom properties directly in the element's `style` attribute (`style="--gutenboing-duration:800ms; --gutenboing-delay:0ms; --gutenboing-timing:ease"`).
   - **Repeat**: `gutenboing-has-repeat-animation` class, and the resolved duration written the same way (`style="--gutenboing-repeat-duration:1500ms"`) — a distinct property name from scroll's `--gutenboing-duration`, since the two modes have independent site-wide defaults that can't share one `:root` slot.

   Both modes go through the same `inject_custom_properties()` helper. The resolved value is always written, whether it came from the block's own override or from falling back to the site default — so the element never depends on inheriting a `:root` value that might not be present. Existing inline styles on the block are preserved/merged, not overwritten.
4. **Frontend trigger**: `assets/js/frontend.js` checks each `.gutenboing-has-scroll-animation` element's `getBoundingClientRect()` at page load first — anything already at least partly on screen gets `gutenboing-animated` immediately, without waiting on an observer callback. This isn't just an optimization: `IntersectionObserver`'s initial callback is spec'd to fire on the first render cycle after `observe()` regardless of scrolling, but in practice wasn't firing promptly for already-visible elements once the script runs from the footer while layout is still settling (fonts/images) — the upfront `getBoundingClientRect()` check sidesteps that timing dependency entirely. Only elements *not* already visible get handed to an `IntersectionObserver`; on first intersection it adds `gutenboing-animated` and unobserves (animation plays once per page load, not on every re-entry). It never selects `.gutenboing-has-repeat-animation` elements — repeat animations are `animation-iteration-count: infinite` CSS with no JS involvement at all.

### Why both modes use inline custom properties instead of `attr()`

The SCSS originally tried reading `data-gutenboing-duration` etc. directly in CSS via `attr(data-gutenboing-duration type(<time>))`. This doesn't work reliably: browser support for typed/advanced `attr()` (usable outside `content`) is still an active [Interop 2026](https://webkit.org/blog/17818/announcing-interop-2026/) target, essentially Chrome-only even where present, and on a browser that doesn't support it the *entire declaration* is invalid at parse time — it does not gracefully fall through to a `var()` fallback, it just silently produces no animation at all.

The fix, applied to both modes identically: `Frontend_Assets` emits site-wide defaults as `:root` custom properties (`--gutenboing-duration`, `--gutenboing-delay`, `--gutenboing-timing`, `--gutenboing-repeat-duration`) via `wp_add_inline_style()`, and the SCSS just reads `var(...)` directly — universally supported, no `attr()` involved. `Block_Renderer` writes each animated block's own fully-resolved values as a local `style` override, which naturally cascades over the `:root` value for that element.

### Value resolution — "Default" vs "None"

- **Animation** (both scroll and repeat variants) has both `none` (hard "no animation", the attribute's default for untouched blocks) and `default` (resolve to the site-wide setting for that mode). The settings page's own animation dropdowns offer `none` and all concrete animations in that category, but **not** `default` (self-referential). Consequence: a block left on "Default" ends up unanimated if the site default itself is set to "None" — this is intentional, confirmed with the plugin owner rather than assumed, and applies symmetrically to both modes.
- **Timing function** (scroll only) has only `default` (no per-block "none" concept — an animation, if it runs at all, always has some timing function). The settings page's timing dropdown offers only the concrete CSS values, not `default`. Repeat mode has no timing function control at all.
- **Duration / delay** are plain integers (milliseconds); an unset block attribute (`null`/absent) falls back to the site default, no sentinel needed.
- `Animations::is_valid( $slug, $category )` scopes validation to a category (`Animations::CATEGORY_SCROLL` / `CATEGORY_ATTENTION`) — a scroll animation slug submitted into a repeat-mode attribute (or vice versa) is rejected and treated as `none`, never silently cross-applied.
- All resolution happens in `Block_Renderer`'s private `resolve_*()` methods (shared `resolve_animation()` / `resolve_ms()` helpers parameterized by category/getter); `Settings::get_default_*()` are the single read path for the site-wide values (each merges the saved `gutenboing_settings` option over hardcoded fallbacks — scroll: `none` / 600ms / 0ms / `ease`; repeat: `none` / 1000ms — so the plugin works sensibly even before the settings page has ever been saved).

### Adding a new animation or timing function

Edit only `Animations::all()` (or `Timing_Functions::all()`) — the block editor panel, the settings page dropdowns, and the `render_block` validation (`is_valid()`) all read from that one array. Set `category` to `Animations::CATEGORY_SCROLL` or `CATEGORY_ATTENTION` on a new animation entry to place it in the right list; no other file needs to change.

### Adding a new block attribute (beyond animation)

Two files need the identical definition, by hand: `assets/js/editor.js`'s `addAnimationAttributes()` (client-side schema, drives the editor UI and serialization) and `Block_Attributes::attribute_schema()` (server-side schema, needed only so `<ServerSideRender>`-previewed dynamic blocks don't reject it — see above). `Block_Renderer` doesn't need updating for schema-only changes; it reads raw parsed attributes regardless of any registered schema.

## Not yet decided

- The actual `@keyframes` / `gutenboing-{animation}` / `gutenboing-animated` CSS rules for most animations — several exist under `assets/scss/animations/` already, more are still to be written.
- Whether `editor.js` ever gets a real build step (JSX, `@wordpress/scripts`) — currently plain ES5 deliberately, to avoid committing to tooling before it's needed.
- Whether frontend assets should eventually load conditionally (only on pages with an animated block) instead of unconditionally — unconditional loading was chosen for simplicity/robustness (works regardless of whether the animated block lives in post content, a template part, a pattern, or a widget).

## Coding conventions

- **PHP**: WordPress Coding Standards — notably **long `array()` syntax only**, short `[...]` syntax is rejected by this repo's phpcs config (`Generic.Arrays.DisallowShortArraySyntax`, part of the `WordPress` standard). The installed `phpcs` (3.5.4) also mis-parses arrow functions (`fn() => ...`) whose body contains a nested multi-argument call — it misreads the inner call's comma as belonging to the outer multi-line function call and flags "only one argument per line". Use a regular `function () use (...) {}` closure instead in that specific situation (single-argument arrow function bodies are fine). SOLID OOP — no logic in the main plugin file beyond bootstrapping. File-level and function-level DocBlocks with `@since`/`@package` tags. No closing `?>` tag. Escape all output; verify nonces on any state-changing request.
- **JS**: currently plain ES5 with no build step (see above) — if/when a build step is introduced, switch to `@wordpress/scripts` defaults for consistency with `form-blocks`.
- **No PHP or JS test suite exists.** Verify changes by exercising them in the running WordPress instance at localhost:8080 — `wp eval` with `do_blocks()` on synthetic block markup is an effective way to check `Block_Renderer`'s resolution logic without a browser; `rest_do_request()` against `/wp/v2/block-renderer/{block}` is the way to check the `<ServerSideRender>`/`Block_Attributes` interaction specifically.
