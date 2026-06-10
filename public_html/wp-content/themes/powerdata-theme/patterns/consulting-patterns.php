<?php
/**
 * Block Pattern: Consulting Services Grid
 *
 * Registers the three-service consulting layout as a reusable
 * block pattern available in Gutenberg → Patterns → PowerData.
 *
 * Usage: Gutenberg editor → + → Patterns → PowerData → Consulting Services Grid
 */

register_block_pattern(
	'powerdata/consulting-services-grid',
	[
		'title'       => __( 'Consulting — Services Grid', 'powerdata-theme' ),
		'description' => __( 'Three-column service cards with icon, heading, description, and feature list.', 'powerdata-theme' ),
		'categories'  => [ 'powerdata' ],
		'keywords'    => [ 'consulting', 'services', 'cards', 'features' ],
		'content'     => '<!-- wp:html --><section class="pd-section-tight" aria-labelledby="pattern-services-heading"><div class="pd-wrap"><div class="reveal" style="max-width:640px;margin-bottom:36px;"><span class="eyebrow">What we help with</span><h2 id="pattern-services-heading" class="h-section">A seasoned product mind, on your side of the table.</h2></div><div class="pd-svc-grid" role="list"><article class="card pd-svc reveal" role="listitem"><span class="pic" aria-hidden="true"><svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/></svg></span><h3>Business organization &amp; planning</h3><p>Customized strategic roadmaps that turn market opportunity into achievable objectives — with a clear sequence of what to do first.</p><ul><li><span class="num">→</span> Product strategy &amp; roadmaps</li><li><span class="num">→</span> Market &amp; positioning analysis</li><li><span class="num">→</span> Operating model &amp; process design</li></ul></article><article class="card pd-svc reveal" role="listitem"><span class="pic" aria-hidden="true"><svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10Z"/></svg></span><h3>Business health check &amp; continuity</h3><p>Robust business health assessment and impact analysis to forecast disruption and develop recovery strategies.</p><ul><li><span class="num">→</span> Business impact analysis (BIA)</li><li><span class="num">→</span> Continuity &amp; recovery planning</li><li><span class="num">→</span> Ongoing review in PRIAM</li></ul></article><article class="card pd-svc reveal" role="listitem"><span class="pic" aria-hidden="true"><svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 4h16v12H4z"/><path d="M2 20h20M9 8l2 2 4-4"/></svg></span><h3>M365 &amp; Google Workspace setup</h3><p>Configure M365 or Google Workspace properly and optimally for how your team actually works.</p><ul><li><span class="num">→</span> Tenant &amp; identity configuration</li><li><span class="num">→</span> Security baselines &amp; MFA</li><li><span class="num">→</span> User onboarding &amp; handover</li></ul></article></div></div></section><!-- /wp:html -->',
	]
);

register_block_pattern(
	'powerdata/four-step-process',
	[
		'title'       => __( 'Four-Step Process Row', 'powerdata-theme' ),
		'description' => __( 'Horizontal four-step numbered process strip on a light background.', 'powerdata-theme' ),
		'categories'  => [ 'powerdata' ],
		'keywords'    => [ 'process', 'steps', 'how it works', 'numbered' ],
		'content'     => '<!-- wp:html --><section class="pd-section-tight" style="background:var(--surface-2);border-top:1px solid var(--line);border-bottom:1px solid var(--line);" aria-labelledby="pattern-process-heading"><div class="pd-wrap"><div class="reveal" style="max-width:640px;margin-bottom:42px;"><span class="eyebrow">How we work</span><h2 id="pattern-process-heading" class="h-section">A proven planning process.</h2></div><div class="pd-proc" role="list"><div class="pd-pstep reveal" role="listitem"><div class="n">1</div><h3 style="font-size:19px;font-family:var(--font-display);margin-bottom:8px;">Listen</h3><p>A free 30-minute conversation about your goals, constraints, and what&#8217;s keeping you up at night.</p></div><div class="pd-pstep reveal" role="listitem"><div class="n">2</div><h3 style="font-size:19px;font-family:var(--font-display);margin-bottom:8px;">Assess</h3><p>We map your current state — operations, weak spots, and tools — to find the highest-leverage moves.</p></div><div class="pd-pstep reveal" role="listitem"><div class="n">3</div><h3 style="font-size:19px;font-family:var(--font-display);margin-bottom:8px;">Plan</h3><p>A prioritized roadmap you can actually execute, sequenced by impact and effort.</p></div><div class="pd-pstep reveal" role="listitem"><div class="n">4</div><h3 style="font-size:19px;font-family:var(--font-display);margin-bottom:8px;">Support</h3><p>Hands-on help to put it in motion — and PRIAM to keep it organized after we&#8217;re done.</p></div></div></div></section><!-- /wp:html -->',
	]
);

register_block_pattern(
	'powerdata/dark-cta-band',
	[
		'title'       => __( 'Dark CTA Band', 'powerdata-theme' ),
		'description' => __( 'Full-width dark ink call-to-action section with heading and buttons.', 'powerdata-theme' ),
		'categories'  => [ 'powerdata', 'call-to-action' ],
		'keywords'    => [ 'cta', 'call to action', 'dark', 'consultation' ],
		'content'     => '<!-- wp:html --><section class="pd-section-tight band-ink" aria-labelledby="pattern-cta-heading"><div class="pd-wrap" style="text-align:center;max-width:720px;margin:0 auto;"><span class="eyebrow on-dark center reveal">Let&#8217;s talk</span><h2 id="pattern-cta-heading" class="h-section reveal" style="color:#fff;">Not sure where to start? That&#8217;s what the free call is for.</h2><p class="lede reveal" style="margin-top:16px;">Tell us what&#8217;s keeping you up at night. We&#8217;ll point you to the fastest, most practical next step — and you&#8217;ll leave with a clear, prioritized action list. No sales pressure.</p><div class="reveal" style="display:flex;gap:13px;justify-content:center;margin-top:28px;flex-wrap:wrap;"><a class="btn btn-primary btn-lg" href="/#contact">Book your consultation <span class="arr">→</span></a></div></div></section><!-- /wp:html -->',
	]
);

register_block_pattern(
	'powerdata/hero-section',
	[
		'title'       => __( 'Page Hero — Text Only', 'powerdata-theme' ),
		'description' => __( 'Standard inner-page hero with eyebrow, h1, lede, and CTA buttons.', 'powerdata-theme' ),
		'categories'  => [ 'powerdata', 'header' ],
		'keywords'    => [ 'hero', 'header', 'page hero', 'intro' ],
		'content'     => '<!-- wp:html --><section class="pd-section-tight" style="padding-top:clamp(48px,7vw,84px);" aria-labelledby="pattern-hero-heading"><div class="pd-wrap"><span class="eyebrow reveal in">Section label</span><h1 id="pattern-hero-heading" class="reveal in" style="font-size:clamp(38px,5.4vw,66px);max-width:16ch;line-height:1.16;">Your headline <span class="serif-em">right here.</span></h1><p class="lede reveal in" style="margin-top:28px;max-width:600px;">Supporting paragraph that gives context and builds confidence. Keep it two to three sentences.</p><div class="hero-cta reveal in"><a class="btn btn-primary btn-lg" href="/#contact">Primary action <span class="arr">→</span></a><a class="btn btn-ghost btn-lg" href="#anchor">Secondary link</a></div></div></section><!-- /wp:html -->',
	]
);

register_block_pattern(
	'powerdata/contact-form',
	[
		'title'       => __( 'Contact Form with Turnstile', 'powerdata-theme' ),
		'description' => __( 'AJAX contact form with Cloudflare Turnstile human verification.', 'powerdata-theme' ),
		'categories'  => [ 'powerdata' ],
		'keywords'    => [ 'contact', 'form', 'turnstile', 'email' ],
		'content'     => '<!-- wp:html --><section class="pd-section-tight" id="contact" aria-labelledby="pattern-contact-heading"><div class="pd-wrap" style="max-width:560px;margin:0 auto;"><div class="card" style="padding:clamp(28px,4vw,44px);"><span class="eyebrow">Contact</span><h2 id="pattern-contact-heading" class="h-section">Get in touch.</h2><p class="lede" style="margin-top:14px;margin-bottom:26px;">Fill in your details and we&#8217;ll be in touch within one business day.</p><form id="pd-contact-form" class="pd-form" novalidate aria-label="Contact form"><div style="display:flex;flex-direction:column;gap:13px;"><div><label for="contact-name">Name</label><input id="contact-name" name="pd_name" type="text" required placeholder="Your name" autocomplete="name"></div><div><label for="contact-email">Email</label><input id="contact-email" name="pd_email" type="email" required placeholder="you@company.com" autocomplete="email"></div><div><label for="contact-message">Message</label><textarea id="contact-message" name="pd_message" rows="4" required placeholder="How can we help?"></textarea></div>[pd_turnstile action="contact"]<button type="submit" class="btn btn-primary" style="width:100%;justify-content:center;">Send message →</button><div class="pd-formmsg" role="alert" aria-live="polite"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" aria-hidden="true"><path d="M20 6 9 17l-5-5"/></svg><span class="pd-formmsg-text">Thanks — we&#8217;ll be in touch within one business day.</span></div></div></form></div></div></section><!-- /wp:html -->',
	]
);
