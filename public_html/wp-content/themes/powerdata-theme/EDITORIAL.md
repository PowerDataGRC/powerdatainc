# Editorial Guide — Publishing on powerdatainc.com

**This file is the version-controlled source.** The live version publishers actually use is a private WordPress Page ("Editorial Guide", reachable from WP Admin → Pages while logged in — not publicly visible). Update both when this content changes: edit here first, then update the WP Page to match.

This is a plain-language guide for publishing and managing articles. No developer knowledge required.

## Publishing a new article

1. In WP Admin, go to **Posts → Add New**.
2. Write the title and body as normal.
3. Add a **Featured Image** if you have one — shows at the top of the article and on article cards. If you skip this, the article automatically gets an on-brand cover image based on its category (see "Cover images" below), so this step is optional, not required.
4. Choose a **Category** in the sidebar — see "Which category?" below. Pick exactly one.
5. Optionally add a **subtitle**: in the editor sidebar, look for the "Article Subtitle" field (Pods panel) and type a one-line summary. It shows directly under the title.
6. Set the post's **Article Status** to "Current" — new posts default to this automatically, so you usually don't need to touch it.
7. Publish as normal. The article automatically appears at `/articles/{your-title}/` — no template selection needed.

## Archiving an article

Older articles don't need to be deleted or unpublished — they can be moved to the **Archive** instead. Archived articles:
- Disappear from the homepage, the main Articles list, category pages, and search
- Stay live at their original web address
- Show a small "kept for reference" notice at the top so readers know it may be outdated

**To archive:** open the post, find the **Article Status** checklist in the editor sidebar (or use **Quick Edit** from the Posts list), check **Archived** instead of **Current**, and save/update.

**To bring an article back:** same steps, check **Current** instead.

**To archive several at once:** on the Posts list, select the posts with the checkboxes, choose **Edit** from the Bulk Actions menu, and set Article Status there.

## Cover images

Every article needs a cover image — it shows at the top of the article, on article cards across the site, and when the article is shared on social media.

- **If you upload a Featured Image**, that always wins — it's used everywhere.
- **If you don't**, the article automatically gets one of PowerData's on-brand cover images, chosen based on the article's category. You don't need to do anything for this to happen; it's automatic the moment the category is set.
- There's currently one cover design per category. If you want a specific look for a specific article, upload your own Featured Image instead.
- Recommended size for a custom Featured Image: 1600×900 pixels (16:9). WordPress will crop to fit if you upload something else.

## Updating the author bio

The author bio shown at the bottom of every article is **not** set on the post — it's set once on the author's user profile, and it updates on every article they've written automatically.

**To update it:** go to **Users → Your Profile** (or the relevant author's profile, if an admin), and look for the "Author Bio", "Author Title", "Author Photo", and "Author LinkedIn URL" fields (added by Pods). Edit and save. Every article by that author reflects the change immediately.

## Which category?

Pick exactly one per article. Use tags for anything more specific.

| Category | Use it for |
|---|---|
| **Operational Efficiency** | Running the business day-to-day: process, staffing, tools, workflow |
| **Data Protection** | Protecting client/business data, backups, privacy |
| **Compliance** | Regulatory requirements — HIPAA, industry-specific rules |
| **Health Check** | Risk review, incident handling, "how exposed are we" type content |
| **Cyber-Security** | Cyber protection, IT security posture, security-specific how-tos |

## Related articles

At the bottom of an article, up to 3 "Related Insights" can be shown. Set them via the **Related Articles** field in the Pods panel on the post editor — search and select up to 3 other articles.

To feature articles inside the body of a post (e.g. a "further reading" callout), use the shortcode:

```
[pd_articles count="3" category="cyber-security"]
```

`category` is optional — omit it to pull from any category. `count` defaults to 3.
