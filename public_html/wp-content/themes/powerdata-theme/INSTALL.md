# PowerData Theme — Installation & Setup Guide

## What's in this package

```
powerdata-theme/
├── style.css                  ← Theme declaration + complete design system
├── functions.php              ← Genesis setup, forms, Turnstile, JSON-LD, footer
├── theme.json                 ← Gutenberg design tokens (colors, fonts, spacing)
├── assets/
│   ├── site.js                ← Scroll reveal, mobile nav, AJAX form handlers
│   └── editor-style.css       ← Block editor preview styles
├── patterns/
│   └── consulting-patterns.php ← 5 reusable block patterns (PowerData category)
└── page-*-blocks.html         ← Gutenberg block content for each page (paste-in)
```

---

## Step 1 — Add Turnstile keys to wp-config.php

Open `wp-config.php` (in your WordPress root) and add these two lines **before** `/* That's all, stop editing! */`:

```php
define( 'CF_TURNSTILE_SITE_KEY',   'YOUR_SITE_KEY_HERE' );
define( 'CF_TURNSTILE_SECRET_KEY', 'YOUR_SECRET_KEY_HERE' );
```

Replace with your actual keys from the Cloudflare Turnstile dashboard.

---

## Step 2 — Upload and activate the theme

1. Zip the entire `powerdata-theme/` folder
2. Go to **Appearance → Themes → Add New → Upload Theme**
3. Upload the zip and click **Activate**
4. Genesis Framework must already be installed and active (it's the parent theme)

> The theme will automatically dequeue SiteOrigin Page Builder scripts on the front end. Once new pages are live, go to **Plugins → SiteOrigin Page Builder → Deactivate** to remove it completely.

---

## Step 3 — Upload your logo

1. Go to **Appearance → Customize → Site Identity**
2. Upload your `powerdata.svg` (or PNG version) as the logo
3. Recommended dimensions: 394 × 74 px (or 2× for retina: 788 × 148 px)

---

## Step 4 — Set a static front page

1. Go to **Settings → Reading**
2. Set "Your homepage displays" to **A static page**
3. Create a page titled **Home** (slug: `home`) if it doesn't exist
4. Select it as the Homepage

---

## Step 5 — Create the pages

Create four pages in WordPress (**Pages → Add New**):

| Page Title       | Slug         | Template      |
|------------------|--------------|---------------|
| Home             | home         | (default)     |
| Consulting       | consulting   | (default)     |
| Training         | training     | (default)     |
| PRIAM Platform   | priam        | (default)     |

For each page, the Genesis "Full Width Content" layout is automatically applied.

---

## Step 6 — Paste block content into each page

For each page:

1. Open the page in the WordPress editor
2. Click **⋮ (Options)** in the top-right toolbar
3. Select **Code editor** (keyboard shortcut: `Ctrl+Shift+Alt+M`)
4. **Select all** existing content and delete it
5. **Copy** the entire content from the corresponding file:
   - Home → `page-home-blocks.html`
   - Consulting → `page-consulting-blocks.html`
   - Training → `page-training-blocks.html`
   - PRIAM → `page-priam-blocks.html`
6. **Paste** into the Code editor
7. Switch back to **Visual editor** to confirm layout looks correct
8. Click **Publish** (or **Update** if the page already exists)

> **Note:** Each `[pd_turnstile]` shortcode in the block content will render the Cloudflare Turnstile widget automatically once the site key is in wp-config.php.

---

## Step 7 — Set up navigation menu

1. Go to **Appearance → Menus** (or **Appearance → Customize → Menus**)
2. Create a menu named **Primary Navigation**
3. Add these pages:
   - Training → slug `/training/`
   - Consulting → slug `/consulting/`
   - PRIAM Platform → slug `/priam/`
   - Articles → custom link to `https://powerdatainc.com/posts/`
   - About → custom link to `/#about`
4. Assign to **Primary** menu location
5. Save

---

## Step 8 — Test Turnstile forms

1. Visit the **Home** page and scroll to the **#contact** section
2. Fill in the form — the Turnstile widget should appear
3. Submit — you should receive an email at your WordPress admin email
4. Visit **Training** and scroll to **#enroll** — test the enrollment form

**Troubleshooting Turnstile:**
- If the widget doesn't appear, check that your site key is in `wp-config.php`
- If admin emails aren't arriving, check your hosting SMTP setup (WP Mail SMTP plugin is a good free option)
- In the Cloudflare Turnstile dashboard, add `powerdatainc.com` as an allowed hostname

---

## Using Block Patterns

Five reusable patterns are registered under the **PowerData** category in Gutenberg:

| Pattern Name                   | Use case |
|--------------------------------|----------|
| Consulting Services Grid       | Three-column service cards |
| Four-Step Process Row          | Numbered horizontal process strip |
| Dark CTA Band                  | Full-width ink CTA with buttons |
| Page Hero — Text Only          | Inner page hero with eyebrow + h1 + CTA |
| Contact Form with Turnstile    | Standalone contact form anywhere |

**To use a pattern:**
1. In the block editor, click **+** to add a block
2. Click **Browse all** or switch to the **Patterns** tab
3. Filter by **PowerData**
4. Click a pattern to insert it

---

## Editing content after publishing

All sections are standard `wp:html` blocks. To edit:

1. Open the page in the editor
2. Click on any section to select the HTML block
3. Click the **Edit** (pencil) icon or **⋮ → Edit as HTML**
4. Edit the HTML directly
5. Click **Update**

For frequently-changed content (headlines, CTAs, pricing), you can also:
- Switch to Code Editor view and use Ctrl+F to find text
- Or use the Essential Blocks **Advanced Heading** block for editable headings outside the HTML wrapper

---

## Essential Blocks 6.1.4 — Recommended blocks to use alongside HTML blocks

These EB blocks work seamlessly with the PowerData design system:

| EB Block              | Good for |
|-----------------------|----------|
| **Advanced Heading**  | Page titles you want to edit visually |
| **Advanced Text**     | Body copy blocks |
| **Button**            | Standalone CTAs |
| **Notice**            | Alert/info callouts |
| **Table of Contents** | Long article pages |
| **Progress Bar**      | Course completion indicators |
| **Accordion**         | Additional FAQ sections |
| **Counter**           | Animated stat numbers |

To use EB's design tokens: In any EB block, the color picker will show the PowerData palette (defined in `theme.json`) under "Theme Colors".

---

## Removing SiteOrigin Page Builder

Once all new pages are published and confirmed working:

1. **Back up your site** (Plugins → WP-CLI or your host's backup tool)
2. Go to **Plugins → Installed Plugins**
3. Deactivate **SiteOrigin Page Builder**
4. Delete it
5. Any old SiteOrigin content on now-replaced pages can be safely ignored (the new blocks replace it entirely)

---

## AISEO notes

The theme automatically outputs:

- **JSON-LD structured data** (`Organization`, `WebSite`, `WebPage` on every page)
- **Service** schema on `/consulting/`
- **Course** schema on `/training/`
- **Open Graph** meta tags (title, description, image) on all pages
- **Twitter Card** meta tags
- Semantic HTML5 landmarks (`<section>`, `<article>`, `role="list"`, `aria-labelledby`)
- Proper heading hierarchy (H1 on every page, H2 for sections, H3 for cards)

**To add/update meta descriptions:**
Install **Yoast SEO** (free) or **Rank Math** (free) — both read standard WordPress excerpt/description fields and override the theme's basic OG tags with full control.

**To add an OG image:**
Place a 1200 × 630 px PNG at: `powerdata-theme/assets/og-default.png`

---

## Adding the `powerdata.svg` logo to the theme

Copy your logo file to:
```
powerdata-theme/assets/powerdata.svg
```

The footer PHP template references it via `get_custom_logo()` (the WordPress custom logo uploader), so uploading through **Appearance → Customize → Site Identity** is the easiest path.

---

## Quick reference: page anchors

| Anchor    | Page     | Section |
|-----------|----------|---------|
| `#services`  | Home | Three pillars |
| `#training`  | Home | Training spotlight |
| `#priam`     | Home | PRIAM spotlight |
| `#consulting`| Home | Consulting spotlight |
| `#about`     | Home | Stats / why PowerData |
| `#articles`  | Home | Article cards |
| `#contact`   | Home | Contact form |
| `#services`  | Consulting | Services grid |
| `#courses`   | Training | Course cards |
| `#enroll`    | Training | Enrollment form |
