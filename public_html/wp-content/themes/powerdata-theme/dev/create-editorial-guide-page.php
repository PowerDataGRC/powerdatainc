<?php
/**
 * Creates (or updates) the private "Editorial Guide" WordPress Page —
 * the live, publisher-facing version of ../EDITORIAL.md. Keep both in
 * sync by hand: edit EDITORIAL.md first, then update the content block
 * below to match, then re-run this script.
 *
 * Run: wp eval-file wp-content/themes/powerdata-theme/dev/create-editorial-guide-page.php
 * Safe to re-run — updates the existing page (matched by slug) instead
 * of creating a duplicate.
 */

if ( ! defined( 'ABSPATH' ) ) {
	echo "This file must be run via WP-CLI (wp eval-file ...).\n";
	exit( 1 );
}

$content = <<<'HTML'
<!-- wp:paragraph -->
<p>This is a plain-language guide for publishing and managing articles on powerdatainc.com. No developer knowledge required.</p>
<!-- /wp:paragraph -->

<!-- wp:heading -->
<h2>Publishing a new article</h2>
<!-- /wp:heading -->

<!-- wp:list {"ordered":true} -->
<ol>
<li>In WP Admin, go to <strong>Posts &rarr; Add New</strong>.</li>
<li>Write the title and body as normal.</li>
<li>Add a <strong>Featured Image</strong> if you have one &mdash; shows at the top of the article and on article cards. If you skip this, the article automatically gets an on-brand cover image based on its category (see &ldquo;Cover images&rdquo; below), so this step is optional, not required.</li>
<li>Choose a <strong>Category</strong> in the sidebar &mdash; see &ldquo;Which category?&rdquo; below. Pick exactly one.</li>
<li>Optionally add a <strong>subtitle</strong>: in the editor sidebar, look for the &ldquo;Article Subtitle&rdquo; field (Pods panel) and type a one-line summary. It shows directly under the title.</li>
<li>Set the post&rsquo;s <strong>Article Status</strong> to &ldquo;Current&rdquo; &mdash; new posts default to this automatically, so you usually don&rsquo;t need to touch it.</li>
<li>Publish as normal. The article automatically appears at <code>/articles/{your-title}/</code> &mdash; no template selection needed.</li>
</ol>
<!-- /wp:list -->

<!-- wp:heading -->
<h2>Archiving an article</h2>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p>Older articles don&rsquo;t need to be deleted or unpublished &mdash; they can be moved to the <strong>Archive</strong> instead. Archived articles:</p>
<!-- /wp:paragraph -->

<!-- wp:list -->
<ul>
<li>Disappear from the homepage, the main Articles list, category pages, and search</li>
<li>Stay live at their original web address</li>
<li>Show a small &ldquo;kept for reference&rdquo; notice at the top so readers know it may be outdated</li>
</ul>
<!-- /wp:list -->

<!-- wp:paragraph -->
<p><strong>To archive:</strong> open the post, find the <strong>Article Status</strong> checklist in the editor sidebar (or use <strong>Quick Edit</strong> from the Posts list), check <strong>Archived</strong> instead of <strong>Current</strong>, and save/update.</p>
<!-- /wp:paragraph -->

<!-- wp:paragraph -->
<p><strong>To bring an article back:</strong> same steps, check <strong>Current</strong> instead.</p>
<!-- /wp:paragraph -->

<!-- wp:paragraph -->
<p><strong>To archive several at once:</strong> on the Posts list, select the posts with the checkboxes, choose <strong>Edit</strong> from the Bulk Actions menu, and set Article Status there.</p>
<!-- /wp:paragraph -->

<!-- wp:heading -->
<h2>Cover images</h2>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p>Every article needs a cover image &mdash; it shows at the top of the article, on article cards across the site, and when the article is shared on social media.</p>
<!-- /wp:paragraph -->

<!-- wp:list -->
<ul>
<li><strong>If you upload a Featured Image</strong>, that always wins &mdash; it&rsquo;s used everywhere.</li>
<li><strong>If you don&rsquo;t</strong>, the article automatically gets one of PowerData&rsquo;s on-brand cover images, chosen based on the article&rsquo;s category. You don&rsquo;t need to do anything for this to happen; it&rsquo;s automatic the moment the category is set.</li>
<li>There&rsquo;s currently one cover design per category. If you want a specific look for a specific article, upload your own Featured Image instead &mdash; the 5 designed covers are also available to pick directly from the Featured Image panel (search &ldquo;PowerData Cover&rdquo;).</li>
<li>Recommended size for a custom Featured Image: 1600&times;900 pixels (16:9). WordPress will crop to fit if you upload something else.</li>
</ul>
<!-- /wp:list -->

<!-- wp:heading -->
<h2>Updating the author bio</h2>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p>The author bio shown at the bottom of every article is <strong>not</strong> set on the post &mdash; it&rsquo;s set once on the author&rsquo;s user profile, and it updates on every article they&rsquo;ve written automatically.</p>
<!-- /wp:paragraph -->

<!-- wp:paragraph -->
<p><strong>To update it:</strong> go to <strong>Users &rarr; Your Profile</strong> (or the relevant author&rsquo;s profile, if an admin), and look for the &ldquo;Author Bio&rdquo;, &ldquo;Author Title&rdquo;, &ldquo;Author Photo&rdquo;, and &ldquo;Author LinkedIn URL&rdquo; fields (added by Pods). Edit and save. Every article by that author reflects the change immediately.</p>
<!-- /wp:paragraph -->

<!-- wp:heading -->
<h2>Which category?</h2>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p>Pick exactly one per article. Use tags for anything more specific.</p>
<!-- /wp:paragraph -->

<!-- wp:table -->
<figure class="wp-block-table">
<table>
<thead>
<tr><th>Category</th><th>Use it for</th></tr>
</thead>
<tbody>
<tr><td><strong>Operational Efficiency</strong></td><td>Running the business day-to-day: process, staffing, tools, workflow</td></tr>
<tr><td><strong>Data Protection</strong></td><td>Protecting client/business data, backups, privacy</td></tr>
<tr><td><strong>Compliance</strong></td><td>Regulatory requirements &mdash; HIPAA, industry-specific rules</td></tr>
<tr><td><strong>Health Check</strong></td><td>Risk review, incident handling, &ldquo;how exposed are we&rdquo; type content</td></tr>
<tr><td><strong>Cyber-Security</strong></td><td>Cyber protection, IT security posture, security-specific how-tos</td></tr>
</tbody>
</table>
</figure>
<!-- /wp:table -->

<!-- wp:heading -->
<h2>Related articles</h2>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p>At the bottom of an article, up to 3 &ldquo;Related Insights&rdquo; can be shown. Set them via the <strong>Related Articles</strong> field in the Pods panel on the post editor &mdash; search and select up to 3 other articles.</p>
<!-- /wp:paragraph -->

<!-- wp:paragraph -->
<p>To feature articles inside the body of a post (e.g. a &ldquo;further reading&rdquo; callout), use the shortcode:</p>
<!-- /wp:paragraph -->

<!-- wp:code -->
<pre class="wp-block-code"><code>[pd_articles count="3" category="cyber-security"]</code></pre>
<!-- /wp:code -->

<!-- wp:paragraph -->
<p><code>category</code> is optional &mdash; omit it to pull from any category. <code>count</code> defaults to 3.</p>
<!-- /wp:paragraph -->
HTML;

$existing = get_page_by_path( 'editorial-guide', OBJECT, 'page' );

if ( $existing ) {
	wp_update_post( [
		'ID'           => $existing->ID,
		'post_title'   => 'Editorial Guide',
		'post_content' => $content,
		'post_status'  => 'private',
	] );
	WP_CLI::success( "Updated existing Editorial Guide page (ID {$existing->ID})." );
} else {
	$post_id = wp_insert_post( [
		'post_title'   => 'Editorial Guide',
		'post_name'    => 'editorial-guide',
		'post_content' => $content,
		'post_type'    => 'page',
		'post_status'  => 'private',
		'post_author'  => 1,
	] );
	if ( is_wp_error( $post_id ) ) {
		WP_CLI::error( $post_id->get_error_message() );
	}
	WP_CLI::success( "Created Editorial Guide page (ID $post_id)." );
}
