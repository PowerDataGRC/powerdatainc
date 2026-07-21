<?php
/**
 * PowerData — Article Import Script
 *
 * Creates the three PRIAM insight articles as WordPress Posts and sets
 * the "PD Article" page template on each.
 *
 * Run once via WP-CLI from the project root:
 *
 *   wp eval-file wp-content/themes/powerdata-theme/dev/import-articles.php
 *
 * Safe to re-run: skips any post whose slug already exists.
 *
 * @package PowerData
 */

if ( ! defined( 'ABSPATH' ) ) {
	echo "This file must be run via WP-CLI (wp eval-file ...).\n";
	exit( 1 );
}

// Default author — first administrator account.
$admin_users = get_users( array( 'role' => 'administrator', 'number' => 1 ) );
$author_id   = $admin_users ? $admin_users[0]->ID : 1;

// Ensure "Insights" category exists.
$cat_id = get_cat_ID( 'Insights' );
if ( ! $cat_id ) {
	$cat_id = wp_insert_category( array( 'cat_name' => 'Insights' ) );
}

// ─────────────────────────────────────────────────────────────────────────────
// Article 1 content
// ─────────────────────────────────────────────────────────────────────────────
$content_1 = '<p>For small human service organizations, &ldquo;risk management&rdquo; often feels like a luxury reserved for large hospitals or enterprises. Your focus is rightly on clients, care quality, and funding. However, it&rsquo;s the <em>operational</em> cracks&mdash;not clinical errors&mdash;that most often lead to compliance headaches, financial loss, or reputational damage.</p>'
	. '<p>At <strong>PowerData</strong>, we&rsquo;ve spoken with dozens of small providers who operate on lean teams. Here are five operational risks we see commonly overlooked&mdash;and how a simple Software-as-a-Service (SaaS) platform can fix them.</p>'
	. '<h2>1. The &ldquo;Laptop Walked Out&rdquo; Gap (Asset Blindness)</h2>'
	. '<p>You know who your clients are, but do you have a real-time register of <em>your</em> assets? A staff laptop containing client notes goes missing. Without a central log of who had which device and its status, you have no starting point for a report.</p>'
	. '<ul><li><strong>The Overlooked Risk:</strong> Lost unencrypted devices = mandatory breach notification.</li>'
	. '<li><strong>PRIAM&rsquo;s Solution:</strong> The <strong>Asset</strong> module tracks devices, employees, and even suppliers&mdash;all linked to incidents. When a laptop disappears, your asset record is ready.</li></ul>'
	. '<h2>2. The Unread PDF Policy (Policy Drift)</h2>'
	. '<p>Your data protection or code of conduct policy is a PDF on a shared drive. You assume everyone read it. But when an incident occurs, you discover a staff member never opened the document.</p>'
	. '<ul><li><strong>The Overlooked Risk:</strong> Unacknowledged policies are effectively non-existent during an audit or lawsuit.</li>'
	. '<li><strong>PRIAM&rsquo;s Solution:</strong> <strong>Policy Management</strong> with one-click acknowledgements. Publish a revision, and the team sees it at login. You know who has read it&mdash;and who hasn&rsquo;t.</li></ul>'
	. '<h2>3. The Spreadsheet Risk Review (Static Assessment)</h2>'
	. '<p>Last year, someone built a risk assessment in Excel. It sat on a desktop. Since then, your operations have changed, you&rsquo;ve added telehealth, or a supplier went under. That spreadsheet is already outdated.</p>'
	. '<ul><li><strong>The Overlooked Risk:</strong> Assessing risk annually means you&rsquo;re always reacting to last year&rsquo;s problems.</li>'
	. '<li><strong>PRIAM&rsquo;s Solution:</strong> <strong>Adaptive Risk Questionnaires</strong> tailored to your industry. The health check is continuous, not static, and skips questions that don&rsquo;t apply to you.</li></ul>'
	. '<h2>4. The Supplier Blind Spot (Third-Party Risk)</h2>'
	. '<p>Your EHR vendor has a breach, or your billing contractor loses a drive. You have no procedure for vendor access reviews or no record of their last security attestation.</p>'
	. '<ul><li><strong>The Overlooked Risk:</strong> Your vendors&rsquo; failures become <em>your</em> compliance failure under HIPAA or state law.</li>'
	. '<li><strong>PRIAM&rsquo;s Solution:</strong> Track suppliers as <strong>Assets</strong>, link them to <strong>Policies</strong> (e.g., BAAs), and log <strong>Incidents</strong> related to vendor performance&mdash;all in one register.</li></ul>'
	. '<h2>5. The &ldquo;Sarah&rsquo;s Laptop&rdquo; Black Hole (No Incident Queue)</h2>'
	. '<p>A staff member reports, &ldquo;Has anyone seen Sarah&rsquo;s laptop?&rdquo; via email or Slack. The message gets buried. No owner is assigned. No steps are logged.</p>'
	. '<ul><li><strong>The Overlooked Risk:</strong> Informal reporting guarantees incomplete investigation and missing audit trails.</li>'
	. '<li><strong>PRIAM&rsquo;s Solution:</strong> A <strong>queue-based incident system</strong>. Every report has a priority and an owner (IT lead, ops manager). Status moves from Submitted &rarr; Assigned &rarr; Closed, with an immutable audit trail.</li></ul>'
	. '<h2>The Bottom Line for Small Providers</h2>'
	. '<p>Risk doesn&rsquo;t wait for you to be ready. You don&rsquo;t need a GRC specialist. You need a single source of truth. With <strong>PRIAM</strong>, you can move from overlooked risks to operational visibility&mdash;setup in 15 minutes, no credit card required.</p>'
	. '<p><em>Ready to stop juggling spreadsheets? <a href="https://priamtiv.com/webform/contact">Schedule a 30-min walkthrough</a> and find out how PRIAM can simplify your operation.</em></p>';

// ─────────────────────────────────────────────────────────────────────────────
// Article 2 content
// ─────────────────────────────────────────────────────────────────────────────
$content_2 = '<p>If you run a small human service organization, you likely check the HIPAA box the same way every year:</p>'
	. '<ol><li>Run an online training video for staff.</li><li>Have everyone sign a piece of paper.</li><li>File it away until next year.</li></ol>'
	. '<p>But true <strong>HIPAA readiness</strong> is not an event. It&rsquo;s a continuous state of visibility across four domains that annual training completely misses. And when a laptop goes missing or a phishing email succeeds, that signed training form won&rsquo;t protect you.</p>'
	. '<p>Here&rsquo;s how to move beyond annual training using the four pillars built into <strong>PRIAM</strong>&mdash;the simple SaaS platform for small businesses.</p>'
	. '<h2>Pillar 1: Living Policies, Not Dead PDFs</h2>'
	. '<p>Annual training assumes a policy is read once. In reality, policies change (e.g., new telehealth rules, remote work procedures).</p>'
	. '<ul><li><strong>The Gap:</strong> Staff reference a policy that&rsquo;s two versions old.</li>'
	. '<li><strong>PRIAM&rsquo;s Approach:</strong> <strong>Versioned, living policies</strong> with effective dates. When a policy updates, the team acknowledges it <em>at next login</em>. You can prove who read the <em>current</em> version, not just the one from last year&rsquo;s training.</li></ul>'
	. '<h2>Pillar 2: Continuous Risk Assessment, Not a One-Time Checklist</h2>'
	. '<p>Annual training is backwards-looking. Your risk assessment should be forward-looking and adaptive.</p>'
	. '<ul><li><strong>The Gap:</strong> You assess risk in January, but change vendors in March. That risk is unassessed until next January.</li>'
	. '<li><strong>PRIAM&rsquo;s Approach:</strong> An <strong>adaptive health check</strong> tailored to your industry. As you answer questions, the engine adapts, digging deeper where needed. Your risk score is a living metric, not a dusty file.</li></ul>'
	. '<h2>Pillar 3: Auditable Incident Response, Not an Email Chain</h2>'
	. '<p>Training tells staff <em>what</em> to do if there&rsquo;s a breach. But how do you track <em>that they did it</em>? An email to &ldquo;compliance@&rdquo; gets lost.</p>'
	. '<ul><li><strong>The Gap:</strong> No clear owner, no status, no timeline for containment.</li>'
	. '<li><strong>PRIAM&rsquo;s Approach:</strong> A <strong>queue-based incident system</strong> with priority levels (Critical, High, Medium). Every incident has an owner from submission through closure. The audit trail logs every status change&mdash;essential for breach response.</li></ul>'
	. '<h2>Pillar 4: Complete Asset Visibility, Not Guesswork</h2>'
	. '<p>HIPAA requires you to know where PHI is stored and on which devices. Annual training doesn&rsquo;t help you track a device&rsquo;s chain of custody.</p>'
	. '<ul><li><strong>The Gap:</strong> You don&rsquo;t know which staff member had which laptop or when it was last seen.</li>'
	. '<li><strong>PRIAM&rsquo;s Approach:</strong> An <strong>asset register</strong> for people <em>and</em> things (devices, vehicles). Every asset links to incidents. When a device goes missing, you don&rsquo;t scramble&mdash;the record is already there.</li></ul>'
	. '<h2>From Training to True Readiness</h2>'
	. '<p>Annual training is table stakes. True HIPAA readiness requires <strong>Policies, Risk, Incidents, and Assets</strong> to work together. That&rsquo;s exactly what PRIAM delivers&mdash;one dashboard, no specialist vocabulary, and an audit trail that builds itself. Set it up Monday, run a real assessment Friday.</p>'
	. '<p><em>Ready to stop juggling spreadsheets? <a href="https://priamtiv.com/webform/contact">Schedule a 30-min walkthrough</a> and find out how PRIAM can simplify your operation.</em></p>';

// ─────────────────────────────────────────────────────────────────────────────
// Article 3 content
// ─────────────────────────────────────────────────────────────────────────────
$content_3 = '<p>Let&rsquo;s clear up a common and costly confusion: <strong>Incident reporting is not risk management.</strong></p>'
	. '<p>Many small human service organizations believe that because they have a log of &ldquo;what went wrong&rdquo; (lost device, privacy slip, client incident), they are managing risk. In reality, incident reporting is purely <em>reactive</em>&mdash;it&rsquo;s the ambulance at the bottom of the cliff.</p>'
	. '<p><strong>Risk management</strong> is the fence at the top. It&rsquo;s the process of identifying <em>what could go wrong</em> before it does.</p>'
	. '<p>Here&rsquo;s why confusing the two leaves your organization vulnerable&mdash;and how <strong>PRIAM</strong> bridges the gap with an integrated approach.</p>'
	. '<table><thead><tr><th>Incident Reporting (Reactive)</th><th>Risk Management (Proactive)</th></tr></thead>'
	. '<tbody>'
	. '<tr><td>Answers: &ldquo;What just happened?&rdquo;</td><td>Answers: &ldquo;What could happen next?&rdquo;</td></tr>'
	. '<tr><td>Focuses on a single event</td><td>Focuses on patterns and probabilities</td></tr>'
	. '<tr><td>Produces a ticket or log entry</td><td>Produces a prioritized action plan</td></tr>'
	. '<tr><td>Lives in an email inbox or spreadsheet</td><td>Lives in a continuous assessment engine</td></tr>'
	. '</tbody></table>'
	. '<h2>The Three Dangers of the &ldquo;Incidents-Only&rdquo; Approach</h2>'
	. '<h3>1. You Fix the Symptom, Not the Cause</h3>'
	. '<p>You log three &ldquo;lost device&rdquo; incidents in a month. Your incident report shows each one closed. Great. But no one asked: <em>Why do we keep losing devices?</em> Is it a bad checkout process? Lack of asset tags? No&mdash;your incident system doesn&rsquo;t track assets.</p>'
	. '<p><strong>PRIAM&rsquo;s Fix:</strong> Link each incident back to an <strong>Asset</strong> (specific laptop, employee). Over a 30-day trend, you see the pattern as a procurement or process problem, not three isolated events.</p>'
	. '<h3>2. You Have No Warning System for High-Risk Areas</h3>'
	. '<p>Incident reports only arrive <em>after</em> damage is done. What about the risk of a phishing attack <em>before</em> someone clicks? Or the risk of a vendor failing a security audit <em>before</em> they lose your data?</p>'
	. '<p><strong>PRIAM&rsquo;s Fix:</strong> The <strong>Risk Assessment</strong> module gives you a health score and category-by-category breakdown (Cyber, Ops, Regulatory). You see &ldquo;High Risk&rdquo; in Cyber <em>before</em> an incident occurs&mdash;and get prioritized recommendations.</p>'
	. '<h3>3. Your &ldquo;Risk Register&rdquo; Is a Joke (or Doesn&rsquo;t Exist)</h3>'
	. '<p>A true risk register links risks to policies, assets, and past incidents. An incident log alone has no context. &ldquo;High turnover risk&rdquo;&mdash;is that linked to an HR policy? An asset (key staff)?</p>'
	. '<p><strong>PRIAM&rsquo;s Fix:</strong> The <strong>Management Dashboard</strong> provides cross-cutting visibility. A single risk is connected to the policy that mitigates it, the asset it affects, and any relevant past incidents. It&rsquo;s one source of truth.</p>'
	. '<h2>From Reactive Logs to Proactive Management</h2>'
	. '<p>Stop mistaking a rearview mirror for a windshield. Move from simple incident reporting to true <strong>PRIAM</strong> risk management: Policies that are read, Risks that are continuously assessed, Incidents with clear owners, and Assets that are tracked. Setup in 15 minutes. No credit card required.</p>'
	. '<p><em>Ready to stop juggling spreadsheets? <a href="https://priamtiv.com/webform/contact">Schedule a 30-min walkthrough</a> and find out how PRIAM can simplify your operation.</em></p>';

// ─────────────────────────────────────────────────────────────────────────────
// Article definitions
// ─────────────────────────────────────────────────────────────────────────────
$articles = array(
	array(
		'title'   => 'Beyond Binders &amp; Spreadsheets: 5 Hidden Operational Risks Facing Small Human Service Organizations',
		'slug'    => 'five-operational-risks-small-human-service-organizations',
		'excerpt' => 'For small human service organizations, operational risks often fly under the radar. Here are five gaps we see regularly — and how a simple SaaS platform closes them.',
		'content' => $content_1,
	),
	array(
		'title'   => 'Why Your HIPAA &#8220;Annual Training&#8221; Isn&#8217;t Enough: A 4-Pillar Approach to True Readiness',
		'slug'    => 'hipaa-readiness-beyond-annual-training',
		'excerpt' => "Annual HIPAA training is table stakes, not a compliance strategy. True readiness requires four pillars working together — here's what most small organizations are missing.",
		'content' => $content_2,
	),
	array(
		'title'   => 'You&#8217;re Logging Incidents, But Are You Managing Risk? The Critical Difference for Small Human Service Orgs',
		'slug'    => 'incident-reporting-is-not-risk-management',
		'excerpt' => 'Incident reporting and risk management are not the same thing. Confusing the two leaves your organization vulnerable — here\'s how to bridge the gap.',
		'content' => $content_3,
	),
);

// ─────────────────────────────────────────────────────────────────────────────
// Insert posts
// ─────────────────────────────────────────────────────────────────────────────
foreach ( $articles as $article ) {

	// Skip if the slug already exists.
	$existing = get_page_by_path( $article['slug'], OBJECT, 'post' );
	if ( $existing ) {
		WP_CLI::log( 'SKIP (already exists): ' . $article['slug'] );
		continue;
	}

	$post_id = wp_insert_post( array(
		'post_title'    => $article['title'],
		'post_name'     => $article['slug'],
		'post_excerpt'  => $article['excerpt'],
		'post_content'  => $article['content'],
		'post_status'   => 'publish',
		'post_type'     => 'post',
		'post_author'   => $author_id,
		'post_category' => array( $cat_id ),
	), true );

	if ( is_wp_error( $post_id ) ) {
		WP_CLI::warning( 'ERROR creating "' . $article['slug'] . '": ' . $post_id->get_error_message() );
		continue;
	}

	// single.php renders every post automatically — no page template needed.

	WP_CLI::success( 'Created (ID ' . $post_id . '): ' . $article['slug'] );
}

WP_CLI::log( 'Done.' );
