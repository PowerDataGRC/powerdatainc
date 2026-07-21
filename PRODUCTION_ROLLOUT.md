# Production Rollout Checklist — Article Architecture (Phases 1–6)

Everything below has been verified working on staging (`https://staging.powerdatainc.com`). Nothing in this checklist has been run against production — it's a plan, not a log.

**Why this exists:** merging the PRs to `main` only deploys *files* (via `deploy.yml`). None of the *database* state — Pods fields, taxonomy terms, category assignments, permalink structure, plugin activation — carries over automatically. Both halves are required before the site actually works as built.

## 0. Before starting

- [ ] Take a fresh production database backup. (An earlier one exists from before Phase 4, but everything since — Phases 4–6 — has changed a lot; back up again immediately before this rollout.)
- [ ] Confirm no one is actively editing posts/pages in WP Admin during the rollout (permalink structure and post-type changes touch every post).

## 1. Merge the code

Merge in order (each PR is stacked on the previous): #5 → #6 → #7 → #8 → #9, all the way to `main`. `deploy.yml` fires on the `main` push and rsyncs the theme to `~/domains/powerdatainc.com/public_html/`.

- [ ] Merge PR #5 (Phase 1: author bio → Pods)
- [ ] Merge PR #6 (Phase 2: Article Status taxonomy)
- [ ] Merge PR #7 (Phase 3: URL migration, templates, categories)
- [ ] Merge PR #8 (Phase 4: ACF → Pods)
- [ ] Merge PR #9 (Phase 5 & 6: retire DPT, search, performance, docs)
- [ ] Confirm the `Deploy to Hostinger` GitHub Action run succeeds

## 2. Database-level setup (WP-CLI, run in this exact order)

All commands run from `~/domains/powerdatainc.com/public_html/` (production's WP root — **not** `public_html/staging/`).

```bash
# Pods fields — user-level (author bio/title/photo/LinkedIn)
wp eval-file wp-content/themes/powerdata-theme/dev/setup-pods-user-fields.php

# Recover the author bio text trapped in a stale post revision (Phase 1 bug)
# and populate it via the fields just created above.
wp eval-file wp-content/themes/powerdata-theme/dev/migrate-author-bio.php

# Pods fields — post-level (article subtitle/related posts, project fields)
wp eval-file wp-content/themes/powerdata-theme/dev/setup-pods-post-fields.php

# Backfill article_status = "current" on every existing published post
# (the article_status taxonomy + its two terms register/seed themselves
# automatically the moment the code above is live — no separate step
# needed for that part)
wp eval-file wp-content/themes/powerdata-theme/dev/backfill-article-status.php

# Category taxonomy: creates the 5 approved categories and assigns them
# to the 5 existing articles
wp eval-file wp-content/themes/powerdata-theme/dev/setup-categories.php

# Convert the "Articles" listing (currently a Post with slug "posts",
# using a Display Post Types shortcode) into a Page with slug
# "articles" — MUST run before the permalink structure change below,
# or it'll collide with the new /articles/%postname%/ post permalinks
wp eval-file wp-content/themes/powerdata-theme/dev/convert-articles-page.php

# Assign the new real listing template to that page (by slug, so order
# relative to the conversion above doesn't matter, but run it after)
wp eval-file wp-content/themes/powerdata-theme/dev/assign-articles-template.php

# URL migration: change permalinks, keep category URLs clean
wp rewrite structure '/articles/%postname%/' --hard
wp option update category_base '/category'
wp rewrite flush --hard

# Search: install, activate, and build the Relevanssi index
wp plugin install relevanssi --activate
wp eval 'relevanssi_build_index();'

# Retire the two superseded plugins — deactivate, do NOT delete
# (keeps a rollback path; see brief Phase 4.6)
wp plugin deactivate advanced-custom-fields
wp plugin deactivate display-post-types

# Purge caches after all the above
wp litespeed-purge all
```

- [ ] Run each command above, in order, checking for errors after each

## 3. Verify

Same checks run on staging throughout this build — repeat them against production:

- [ ] `/` (homepage) — 200, updated Articles section links work
- [ ] Each of the 5 old post URLs (e.g. `/cybersecurity-interactive-plan-for-small-businesses/`) — 301s to `/articles/{slug}/`
- [ ] `/posts/` — 301s to `/articles/`
- [ ] `/articles/` — 200, lists all 5 articles, correct categories shown
- [ ] `/category/cyber-security/` (and the other 4) — 200, correct posts, clean URL (not `/articles/category/...`)
- [ ] `/articles/status/archived/` — 200, empty (nothing archived yet)
- [ ] `/?s=` a real search term — 200, relevant results
- [ ] One article's author bio box — shows Murray's bio, title, avatar
- [ ] Archive a test post via Quick Edit, confirm it disappears from home/category/search but stays live at its own URL with the notice banner, then un-archive it
- [ ] Enable `WP_DEBUG`/`WP_DEBUG_LOG` temporarily, click through the above, confirm no new PHP notices, then turn debug back off
- [ ] Regenerate/verify the XML sitemap reflects the new `/articles/` URLs (AIOSEO, dynamic — should update on its own)

## 4. After going live

- [ ] Resubmit the sitemap to Google Search Console (and Bing Webmaster Tools, if used) — the URLs changed, so the old ones need to drop out of the index and the new ones need (re-)discovery
- [ ] Leave ACF and Display Post Types **deactivated, not deleted**, for a full publishing cycle (per the brief's own Phase 4.6) before considering removing them entirely
- [ ] After that full cycle, revisit Phase 4.7: clean up the now-orphaned `_fieldname` ACF companion meta keys
- [ ] Watch error logs for the first few days — several plugins interact here (Pods, Relevanssi, AIOSEO, LiteSpeed) and staging's plugin *versions* may drift from production's before this rollout happens
