# Article Cover System — Developer Guide

How the article-cover image library is built, how it's wired into WordPress, and how to maintain or extend it. For editorial/publishing usage, see the Editorial Guide instead (linked from WP Admin, source at `../EDITORIAL.md`).

## What this is

One fixed HTML/CSS template, rendered per category with different colors/headline/motif, rasterized to static WebP files, and used as WordPress Featured Images. There is no live HTML rendering on the front end — everything is a pre-built static image.

## File map

```
covers/
  render/
    template.js     the ONE template — all layout/markup lives here
    data.js          per-category colors, headline, subhead, motif SVG
    render.js        the pipeline: builds HTML, screenshots, resizes, writes covers.json
    package.json     playwright + sharp
  {slug}.html         generated preview/debug HTML per category (open directly in a browser)
  {slug}-01.webp       hero image, 1600×900
  {slug}-01-thumb.webp thumbnail image, 800×450
  covers.json          manifest — file/thumb/category/field/alt/created per cover
  DEVELOPERS.md        this file

../assets/fonts/
  schibsted-grotesk-800.woff2       headline sans (weight 800)
  instrument-serif-italic-400.woff2 accent serif (italic, weight 400)

../inc/covers.php       add_image_size() registration + the auto-rotation fallback filter
```

## How a cover is built

1. `render.js` reads `data.js` (one entry per category) and calls `template.js`'s `renderCoverHTML(cat)` to get a complete HTML document as a string.
2. That HTML is written to `covers/{slug}.html` — mainly so you can double-click it and preview a cover directly in a browser without running the pipeline.
3. Playwright loads that file, waits for `document.fonts.ready` (critical — without this, the screenshot can race font loading and fall back to a system font), and screenshots the `#cover` element at `deviceScaleFactor: 2` (renders at 3200×1800 physical pixels for a 1600×900 CSS box).
4. Sharp resizes that screenshot down to 1600×900 (hero) and separately to 800×450 (thumb) as WebP.
5. For the thumb, before the second screenshot, `render.js` adds a `.thumb` class to `#cover` via `page.evaluate()`. CSS in `template.js` hides `.kicker-text` and `.subhead` and shrinks `.motif` when that class is present — same template, no separate markup to maintain.
6. `covers.json` is rewritten from scratch every run, in category order.

## Adding or changing a category

Everything category-specific lives in `data.js` — never edit `template.js` for a single category's content.

1. Add/edit an entry in `covers/render/data.js`. Every field is required: `slug` (must match the real WordPress category slug), `field` (background hex), `bucket` (`'dark'` or `'bright'` — informational only, doesn't affect rendering, just documents which text-color rule was applied), `textHex`, `serifTint`, `supportTint`, `decoDark`, `decoLight`, `headSans`, `headSerif`, `subhead`, `motif` (raw inline SVG markup, sized roughly 160-190px wide).
2. Text color rule: dark field background → cream text (`#FAF8F4`); bright field background → ink text (`#0F1B2D`). Pick `serifTint`/`supportTint` as lighter/darker variants of the same bucket so the hierarchy (headline > accent word > kicker/subhead) still reads correctly.
3. Run the pipeline (below). It's fully idempotent — re-running regenerates every category's files and covers.json from scratch, there's no partial/incremental state to worry about.
4. If it's a genuinely new category (not editing an existing one), also add it to `dev/setup-categories.php` (creates the WP category term) and to the Editorial Guide's category table.

## Running the pipeline

```bash
cd wp-content/themes/powerdata-theme/covers/render
npm install
npx playwright install chromium   # first time only
npm run render
```

Re-run any time you change `template.js` or `data.js`. It overwrites every `.html`/`.webp` file and `covers.json` — there's nothing to clean up first.

## Changing the template itself

`template.js` is the single source of the fixed skeleton (kicker top-left, motif top-right, headline+subhead bottom-left, two decorative circles). If you need to change layout, spacing, or type sizing for every cover at once, edit it there — it applies to all five categories on the next render.

The `@font-face` rules point at `../../assets/fonts/*.woff2` (relative from `covers/{slug}.html`) — don't switch these back to a Google Fonts URL. The whole point of self-hosting is that Playwright's `file://` load never depends on network access at render time. If you add a font weight/style that doesn't already exist in `assets/fonts/`, download the actual static `.woff2` (not a variable font) and add a matching `@font-face` block.

## Getting the images into WordPress

The pipeline only produces files on disk — it doesn't touch WordPress. After running it:

1. Deploy the theme (covers + fonts are just theme files, no different from any other asset).
2. `inc/covers.php` registers two image sizes on `after_setup_theme`: `pd-article-hero` (1600×900, hard crop) and `pd-article-thumb` (800×450, hard crop). These are for **real uploaded Featured Images** — if an editor uploads their own photo, WordPress crops it to these dimensions automatically. Run **Regenerate Thumbnails** (plugin) or `wp media regenerate` after adding/changing these sizes so existing uploads gain them.
3. The auto-rotation fallback (see below) serves the static `covers/*.webp` files directly by URL — it does not go through WordPress's image-size/cropping system at all, since those files are already the exact target dimensions.

## Auto-rotation fallback — how it actually fires

`inc/covers.php` hooks `post_thumbnail_html` (the filter `get_the_post_thumbnail()`/`the_post_thumbnail()` output passes through). If `$html` is already non-empty (a real Featured Image exists), the filter returns it untouched — a real upload always wins. If it's empty, the filter looks up the post's primary category, counts how many covers exist for that slug (via `covers.json`), and picks one deterministically: `($post_id % $count) + 1`. Same post always gets the same cover; different posts in the same category spread across whatever covers exist (currently 1 per category, so they'll currently all get `-01`).

**This only works because the theme templates check the *returned HTML string*, not `has_post_thumbnail()`.** `has_post_thumbnail()` looks at whether a real attachment is set — it's `false` for every post relying on the fallback, so a template that gates on it (e.g. `if ( has_post_thumbnail() ) { the_post_thumbnail(); }`) will skip calling `get_the_post_thumbnail()` entirely and the filter never gets a chance to run. `inc/article-render.php` and `template-parts/content-card.php` were both changed to call `get_the_post_thumbnail()` unconditionally and check whether the *string it returns* is non-empty instead. If you add a new template that renders a featured image, follow the same pattern — don't reintroduce a `has_post_thumbnail()` gate around it.

## Manifest (`covers.json`)

Rebuilt by `render.js` on every run — don't hand-edit it, edit `data.js` instead. Shape:

```json
{
  "file": "cyber-security-01.webp",
  "thumb": "cyber-security-01-thumb.webp",
  "category": "cyber-security",
  "field": "#fe5a1d",
  "alt": "PowerData field guide: cyber awareness — spotting the trick in the inbox before anyone clicks it.",
  "created": "2026-07-22"
}
```

`inc/covers.php` reads this at runtime (cached per-request via a static variable) to count covers-per-category and to pull the pre-written alt text — it never re-derives alt text itself.

## Accessibility / SEO notes

- Alt text is generated once per cover from its headline + subhead (see `altText()` in `render.js`) and stored in the manifest — it's never regenerated at request time, so it stays stable even if `inc/covers.php`'s logic changes later.
- These are original assets. Don't push them through a third-party CDN or shared image host that would let the exact same file get indexed elsewhere — the uniqueness is the SEO value.
- Open Graph: the Featured Image already drives `og:image` via the SEO plugin (AIOSEO), for both real uploads and auto-rotated covers, since both are ultimately just featured-image HTML output. A dedicated 1200×630 crop was scoped as optional in the build brief and hasn't been built — the 16:9 hero is used as-is for `og:image` today.
- Staging caveat: third-party social-card validators (Facebook/LinkedIn/Twitter preview tools) can't fetch a non-public staging URL. To confirm `og:image` is correct pre-launch, view page source on staging directly rather than relying on those tools.
