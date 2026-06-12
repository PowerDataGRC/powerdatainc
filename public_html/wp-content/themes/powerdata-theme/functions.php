<?php
/**
 * PowerData Theme — functions.php
 * Genesis child theme for powerdatainc.com
 *
 * @package PowerData
 */

// ── 1. CONSTANTS ─────────────────────────────────────────────────────────────
define( 'POWERDATA_VERSION',   '1.0.7' );
define( 'POWERDATA_DIR',       get_stylesheet_directory() );
define( 'POWERDATA_URI',       get_stylesheet_directory_uri() );
define( 'POWERDATA_SITE_NAME', 'PowerData Solutions Inc.' );
define( 'POWERDATA_SITE_URL',  'https://powerdatainc.com' );

// ── 2. GENESIS SETUP ─────────────────────────────────────────────────────────
add_action( 'genesis_setup', 'powerdata_genesis_setup', 15 );
function powerdata_genesis_setup() {

	// Support for custom logo
	add_theme_support( 'custom-logo', [
		'height'      => 74,
		'width'       => 394,
		'flex-height' => true,
		'flex-width'  => true,
		'header-text' => [ '.site-title', '.site-description' ],
	] );

	// HTML5 markup
	add_theme_support( 'html5', [
		'search-form', 'comment-form', 'comment-list',
		'gallery', 'caption', 'style', 'script',
	] );

	// Remove Genesis layout options we don't need
	genesis_unregister_layout( 'content-sidebar' );
	genesis_unregister_layout( 'sidebar-content' );
	genesis_unregister_layout( 'content-sidebar-sidebar' );
	genesis_unregister_layout( 'sidebar-content-sidebar' );
	genesis_unregister_layout( 'sidebar-sidebar-content' );

	// Default layout: full width
	add_theme_support( 'genesis-full-width-content' );

	// Structural wrap
	add_theme_support( 'genesis-structural-wrap', [
		'header', 'menu-primary', 'menu-secondary', 'footer-widgets', 'footer',
	] );

	// Remove the Altitude Pro / Genesis default breadcrumbs
	remove_action( 'genesis_before_loop', 'genesis_do_breadcrumbs' );

	// Force full-width layout site-wide. Using genesis_site_layout (not
	// genesis_pre_get_option_site_layout) so it overrides any per-page
	// _genesis_layout post meta that may have been saved by a previous theme.
	add_filter( 'genesis_site_layout', '__genesis_return_full_width_content' );

	// Move primary nav inside the header so logo and links share one bar.
	// Must run here (genesis_setup priority 15), AFTER Genesis has registered
	// genesis_do_nav on genesis_after_header during genesis_init.
	remove_action( 'genesis_after_header', 'genesis_do_nav' );
	add_action( 'genesis_header', 'genesis_do_nav', 12 );

	// Only register primary nav — disables the secondary nav menu and prevents
	// any old secondary/social menu widgets from rendering below the header.
	add_theme_support( 'genesis-menus', [ 'primary' => __( 'Primary Navigation', 'powerdata-theme' ) ] );
}

// Deregister the Genesis header-right sidebar so old social-link widgets
// from a previous theme don't appear in the header area.
add_action( 'widgets_init', function () {
	unregister_sidebar( 'header-right' );
}, 20 );

// ── 3. ENQUEUE STYLES & SCRIPTS ──────────────────────────────────────────────
add_action( 'wp_enqueue_scripts', 'powerdata_enqueue_assets' );
function powerdata_enqueue_assets() {

	// Google Fonts (also in CSS @import as fallback — only one will fire)
	wp_enqueue_style(
		'powerdata-fonts',
		'https://fonts.googleapis.com/css2?family=Schibsted+Grotesk:wght@400;500;600;700;800&family=Hanken+Grotesk:wght@400;500;600;700&family=Instrument+Serif:ital@0;1&display=swap',
		[],
		null
	);

	// Genesis parent stylesheet (required)
	wp_enqueue_style(
		'genesis-style',
		get_template_directory_uri() . '/style.css',
		[],
		POWERDATA_VERSION
	);

	// Child theme stylesheet (loads after genesis — the @import handles it too)
	wp_enqueue_style(
		'powerdata-style',
		POWERDATA_URI . '/style.css',
		[ 'genesis-style' ],
		POWERDATA_VERSION
	);

	// Site JS (scroll reveal + mobile nav)
	wp_enqueue_script(
		'powerdata-site',
		POWERDATA_URI . '/assets/site.js',
		[],
		POWERDATA_VERSION,
		true
	);

	// Cloudflare Turnstile — load on pages/posts that have a contact/enroll form
	if ( powerdata_page_has_form() ) {
		wp_enqueue_script(
			'cf-turnstile',
			'https://challenges.cloudflare.com/turnstile/v0/api.js',
			[],
			null,
			true  // load in footer
		);
	}
}

/**
 * Decide which pages need Turnstile.
 * Checks for pages with slugs: home, consulting, training, contact,
 * or any page/post that has the [turnstile] shortcode in content.
 */
function powerdata_page_has_form() {
	if ( is_front_page() || is_home() ) return true;
	if ( is_page( [ 'consulting', 'training', 'contact' ] ) ) return true;
	global $post;
	if ( $post && has_shortcode( $post->post_content, 'pd_turnstile' ) ) return true;
	return false;
}

// ── 4. CLOUDFLARE TURNSTILE SHORTCODE ────────────────────────────────────────
/**
 * Usage in any Gutenberg HTML block or Classic block:
 *   [pd_turnstile]
 *
 * The site key is stored in wp-config.php or options table.
 * Add this to wp-config.php:
 *   define( 'CF_TURNSTILE_SITE_KEY',   'YOUR_SITE_KEY_HERE' );
 *   define( 'CF_TURNSTILE_SECRET_KEY', 'YOUR_SECRET_KEY_HERE' );
 */
add_shortcode( 'pd_turnstile', 'powerdata_turnstile_widget' );
function powerdata_turnstile_widget( $atts ) {
	$atts = shortcode_atts( [
		'theme'   => 'light',
		'size'    => 'normal',
		'action'  => '',
	], $atts, 'pd_turnstile' );

	$site_key = defined( 'CF_TURNSTILE_SITE_KEY' ) ? CF_TURNSTILE_SITE_KEY : get_option( 'cf_turnstile_site_key', '' );

	if ( empty( $site_key ) ) {
		if ( current_user_can( 'manage_options' ) ) {
			return '<p style="color:red;font-size:13px;">⚠ Turnstile: add CF_TURNSTILE_SITE_KEY to wp-config.php</p>';
		}
		return '';
	}

	return sprintf(
		'<div class="cf-turnstile" data-sitekey="%s" data-theme="%s" data-size="%s"%s></div>',
		esc_attr( $site_key ),
		esc_attr( $atts['theme'] ),
		esc_attr( $atts['size'] ),
		$atts['action'] ? ' data-action="' . esc_attr( $atts['action'] ) . '"' : ''
	);
}

/**
 * Server-side Turnstile verification helper.
 * Call this from any form-processing function:
 *
 *   $result = powerdata_verify_turnstile( $_POST['cf-turnstile-response'] );
 *   if ( ! $result ) { wp_send_json_error( 'Human verification failed.' ); }
 */
function powerdata_verify_turnstile( $token ) {
	$secret = defined( 'CF_TURNSTILE_SECRET_KEY' ) ? CF_TURNSTILE_SECRET_KEY : get_option( 'cf_turnstile_secret_key', '' );

	if ( empty( $secret ) || empty( $token ) ) return false;

	$response = wp_remote_post(
		'https://challenges.cloudflare.com/turnstile/v0/siteverify',
		[
			'body' => [
				'secret'   => $secret,
				'response' => $token,
				'remoteip' => $_SERVER['REMOTE_ADDR'] ?? '',
			],
		]
	);

	if ( is_wp_error( $response ) ) return false;

	$body = json_decode( wp_remote_retrieve_body( $response ), true );
	return ! empty( $body['success'] );
}

// ── 5. AJAX FORM HANDLERS ─────────────────────────────────────────────────────
/**
 * Contact form handler (homepage #contact section).
 * Called via fetch('/wp-admin/admin-ajax.php', { method:'POST', body: formData })
 */
add_action( 'wp_ajax_nopriv_pd_contact_form', 'powerdata_handle_contact_form' );
add_action( 'wp_ajax_pd_contact_form',        'powerdata_handle_contact_form' );
function powerdata_handle_contact_form() {
	check_ajax_referer( 'pd_contact_nonce', 'nonce' );

	// Turnstile check
	$token = sanitize_text_field( $_POST['cf-turnstile-response'] ?? '' );
	if ( ! powerdata_verify_turnstile( $token ) ) {
		wp_send_json_error( [ 'message' => 'Human verification failed. Please try again.' ] );
	}

	$name    = sanitize_text_field( $_POST['pd_name']    ?? '' );
	$email   = sanitize_email(      $_POST['pd_email']   ?? '' );
	$message = sanitize_textarea_field( $_POST['pd_message'] ?? '' );

	if ( empty( $name ) || ! is_email( $email ) || empty( $message ) ) {
		wp_send_json_error( [ 'message' => 'Please fill in all required fields.' ] );
	}

	$to      = get_option( 'admin_email' );
	$subject = 'PowerData — New consultation request from ' . $name;
	$body    = "Name: {$name}\nEmail: {$email}\n\nMessage:\n{$message}";
	$headers = [
		'Content-Type: text/plain; charset=UTF-8',
		'From: PowerData Website <noreply@powerdatainc.com>',
		'Reply-To: ' . $name . ' <' . $email . '>',
	];

	$sent = wp_mail( $to, $subject, $body, $headers );

	if ( $sent ) {
		wp_send_json_success( [ 'message' => "Thanks — we'll be in touch within one business day." ] );
	} else {
		wp_send_json_error( [ 'message' => 'Sorry, there was a problem sending your message. Please email us directly.' ] );
	}
}

/**
 * Training enrollment form handler.
 */
add_action( 'wp_ajax_nopriv_pd_enroll_form', 'powerdata_handle_enroll_form' );
add_action( 'wp_ajax_pd_enroll_form',        'powerdata_handle_enroll_form' );
function powerdata_handle_enroll_form() {
	check_ajax_referer( 'pd_enroll_nonce', 'nonce' );

	$token = sanitize_text_field( $_POST['cf-turnstile-response'] ?? '' );
	if ( ! powerdata_verify_turnstile( $token ) ) {
		wp_send_json_error( [ 'message' => 'Human verification failed. Please try again.' ] );
	}

	$name     = sanitize_text_field( $_POST['pd_name']     ?? '' );
	$email    = sanitize_email(      $_POST['pd_email']    ?? '' );
	$course   = sanitize_text_field( $_POST['pd_course']   ?? '' );
	$learners = sanitize_text_field( $_POST['pd_learners'] ?? '' );

	if ( empty( $name ) || ! is_email( $email ) ) {
		wp_send_json_error( [ 'message' => 'Please fill in your name and email.' ] );
	}

	$to      = get_option( 'admin_email' );
	$subject = 'PowerData — New enrollment request: ' . $course;
	$body    = "Name: {$name}\nEmail: {$email}\nCourse: {$course}\nLearners: {$learners}";
	$headers = [
		'Content-Type: text/plain; charset=UTF-8',
		'From: PowerData Website <noreply@powerdatainc.com>',
		'Reply-To: ' . $name . ' <' . $email . '>',
	];

	$sent = wp_mail( $to, $subject, $body, $headers );

	if ( $sent ) {
		wp_send_json_success( [ 'message' => "Thanks — we'll send enrollment details within one business day." ] );
	} else {
		wp_send_json_error( [ 'message' => 'Sorry, there was a problem. Please email us directly.' ] );
	}
}

// ── 5b. OUTBOUND SMTP ────────────────────────────────────────────────────────
// Password is stored in wp-config.php as: define( 'SMTP_PASSWORD', '...' );
// wp-config.php is git-ignored so the credential never enters the repo.
add_action( 'phpmailer_init', 'powerdata_smtp_config' );
function powerdata_smtp_config( $phpmailer ) {
	$phpmailer->isSMTP();
	$phpmailer->Host       = 'smtp.titan.email';
	$phpmailer->SMTPAuth   = true;
	$phpmailer->Port       = 465;
	$phpmailer->SMTPSecure = 'ssl';
	$phpmailer->Username   = 'letstalk@powerdatainc.com';
	$phpmailer->Password   = defined( 'SMTP_PASSWORD' ) ? SMTP_PASSWORD : '';
}

// ── 6. LOCALIZE AJAX DATA FOR JS ─────────────────────────────────────────────
add_action( 'wp_enqueue_scripts', 'powerdata_localize_ajax' );
function powerdata_localize_ajax() {
	wp_localize_script( 'powerdata-site', 'pdAjax', [
		'url'           => admin_url( 'admin-ajax.php' ),
		'contactNonce'  => wp_create_nonce( 'pd_contact_nonce' ),
		'enrollNonce'   => wp_create_nonce( 'pd_enroll_nonce' ),
	] );
}

// ── 7. JSON-LD STRUCTURED DATA ────────────────────────────────────────────────
add_action( 'wp_head', 'powerdata_json_ld', 5 );
function powerdata_json_ld() {
	$schema = [
		'@context' => 'https://schema.org',
		'@graph'   => [
			[
				'@type'  => 'Organization',
				'@id'    => POWERDATA_SITE_URL . '/#organization',
				'name'   => POWERDATA_SITE_NAME,
				'url'    => POWERDATA_SITE_URL,
				'logo'   => [
					'@type' => 'ImageObject',
					'url'   => POWERDATA_URI . '/assets/powerdata.svg',
				],
				'sameAs' => [
					'https://www.linkedin.com/company/powerdatasolutions/',
				],
				'contactPoint' => [
					'@type'       => 'ContactPoint',
					'contactType' => 'customer service',
					'url'         => POWERDATA_SITE_URL . '/#contact',
				],
				'description' => 'Practical training, product management consulting, and PRIAM — simple software for policies, risk, incidents, and assets. Built for small business owners.',
			],
			[
				'@type'           => 'WebSite',
				'@id'             => POWERDATA_SITE_URL . '/#website',
				'url'             => POWERDATA_SITE_URL,
				'name'            => POWERDATA_SITE_NAME,
				'publisher'       => [ '@id' => POWERDATA_SITE_URL . '/#organization' ],
				'potentialAction' => [
					'@type'       => 'SearchAction',
					'target'      => [
						'@type'       => 'EntryPoint',
						'urlTemplate' => POWERDATA_SITE_URL . '/?s={search_term_string}',
					],
					'query-input' => 'required name=search_term_string',
				],
			],
		],
	];

	// On individual pages, add WebPage schema
	if ( is_singular() ) {
		global $post;
		$schema['@graph'][] = [
			'@type'           => 'WebPage',
			'@id'             => get_permalink() . '#webpage',
			'url'             => get_permalink(),
			'name'            => wp_get_document_title(),
			'description'     => wp_strip_all_tags( get_the_excerpt( $post ) ),
			'isPartOf'        => [ '@id' => POWERDATA_SITE_URL . '/#website' ],
			'about'           => [ '@id' => POWERDATA_SITE_URL . '/#organization' ],
			'datePublished'   => get_the_date( 'c', $post ),
			'dateModified'    => get_the_modified_date( 'c', $post ),
			'inLanguage'      => 'en-US',
		];

		// Add Service schema on service pages
		if ( is_page( 'consulting' ) ) {
			$schema['@graph'][] = [
				'@type'       => 'Service',
				'@id'         => POWERDATA_SITE_URL . '/consulting/#service',
				'name'        => 'Product Management Consulting',
				'provider'    => [ '@id' => POWERDATA_SITE_URL . '/#organization' ],
				'description' => 'Business organization and planning, risk assessment, business continuity, and Microsoft 365 / Google Workspace setup for small businesses.',
				'areaServed'  => 'US',
				'url'         => POWERDATA_SITE_URL . '/consulting/',
				'serviceType' => 'Business Consulting',
			];
		}

		if ( is_page( 'training' ) ) {
			$schema['@graph'][] = [
				'@type'       => 'Course',
				'@id'         => POWERDATA_SITE_URL . '/training/#course-cyber',
				'name'        => 'Cyber Security Awareness',
				'provider'    => [ '@id' => POWERDATA_SITE_URL . '/#organization' ],
				'description' => 'Cyber awareness training for non-technical small business teams. Covers phishing, passwords, safe browsing, and incident reporting.',
				'url'         => POWERDATA_SITE_URL . '/training/',
				'offers'      => [
					'@type'         => 'Offer',
					'price'         => '149.00',
					'priceCurrency' => 'USD',
					'category'      => 'per learner',
				],
			];
		}
	}

	echo '<script type="application/ld+json">' . wp_json_encode( $schema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT ) . '</script>' . "\n";
}

// ── 8. SEO META TAGS (Open Graph + Twitter Card) ─────────────────────────────
add_action( 'wp_head', 'powerdata_og_meta', 2 );
function powerdata_og_meta() {
	global $post;

	$title       = wp_get_document_title();
	$description = get_bloginfo( 'description' );
	$url         = home_url( '/' );
	$image       = POWERDATA_URI . '/assets/og-default.png'; // add a 1200×630 OG image

	if ( is_singular() && $post ) {
		$description = wp_strip_all_tags( get_the_excerpt( $post ) ) ?: $description;
		$url         = get_permalink();
		if ( has_post_thumbnail( $post ) ) {
			$thumb = wp_get_attachment_image_src( get_post_thumbnail_id( $post ), 'large' );
			if ( $thumb ) $image = $thumb[0];
		}
	}
	?>
<meta property="og:type"        content="website">
<meta property="og:site_name"   content="<?php echo esc_attr( POWERDATA_SITE_NAME ); ?>">
<meta property="og:title"       content="<?php echo esc_attr( $title ); ?>">
<meta property="og:description" content="<?php echo esc_attr( $description ); ?>">
<meta property="og:url"         content="<?php echo esc_url( $url ); ?>">
<meta property="og:image"       content="<?php echo esc_url( $image ); ?>">
<meta name="twitter:card"        content="summary_large_image">
<meta name="twitter:title"       content="<?php echo esc_attr( $title ); ?>">
<meta name="twitter:description" content="<?php echo esc_attr( $description ); ?>">
<meta name="twitter:image"       content="<?php echo esc_url( $image ); ?>">
	<?php
}

// ── 9. REGISTER BLOCK PATTERNS ───────────────────────────────────────────────
add_action( 'init', 'powerdata_register_block_patterns' );
function powerdata_register_block_patterns() {

	register_block_pattern_category( 'powerdata', [
		'label' => 'PowerData',
	] );

	// Auto-load all .php files from the patterns directory
	$pattern_dir = POWERDATA_DIR . '/patterns';
	if ( is_dir( $pattern_dir ) ) {
		foreach ( glob( $pattern_dir . '/*.php' ) as $pattern_file ) {
			require $pattern_file;
		}
	}
}

// ── 10. REMOVE SITEORIGIN PAGE BUILDER ───────────────────────────────────────
/**
 * Deactivate SiteOrigin Page Builder gracefully.
 * Once new pages are live, you can deactivate/delete the plugin from
 * Plugins > Installed Plugins. This filter prevents it from loading
 * its scripts on the front end in the meantime.
 */
add_action( 'wp_enqueue_scripts', 'powerdata_dequeue_siteorigin', 100 );
function powerdata_dequeue_siteorigin() {
	// SiteOrigin Page Builder scripts/styles
	wp_dequeue_script( 'siteorigin-panels-front-styles' );
	wp_dequeue_style(  'siteorigin-panels-front' );
	wp_dequeue_style(  'siteorigin-panels-front-flex' );
}

// ── 11. GENESIS HEADER / NAV TWEAKS ──────────────────────────────────────────
// Keep the default Genesis header but remove the tagline
add_filter( 'genesis_seo_title', 'powerdata_custom_title_html', 10, 3 );
function powerdata_custom_title_html( $title, $inside, $wrap ) {
	// Replace the inner content with our logo if available
	if ( function_exists( 'get_custom_logo' ) && has_custom_logo() ) {
		$inside = get_custom_logo();
	}
	return '<' . $wrap . ' class="site-title"><a href="' . home_url( '/' ) . '" rel="home">' . $inside . '</a></' . $wrap . '>';
}
remove_action( 'genesis_site_description', 'genesis_seo_site_description' );

// ── 11b. MOBILE NAVIGATION ────────────────────────────────────────────────
add_action( 'genesis_header', 'powerdata_mobile_nav_toggle', 20 );
function powerdata_mobile_nav_toggle() {
    ?>
    <button class="pd-nav-toggle" data-menu-toggle aria-expanded="false" aria-controls="pd-mobile-menu" aria-label="Open navigation">
        <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
            <line x1="3" y1="6" x2="21" y2="6"/><line x1="3" y1="12" x2="21" y2="12"/><line x1="3" y1="18" x2="21" y2="18"/>
        </svg>
    </button>
    <?php
}

add_action( 'genesis_after_header', 'powerdata_mobile_menu', 4 );
function powerdata_mobile_menu() {
    ?>
    <nav id="pd-mobile-menu" aria-label="Mobile navigation">
        <?php wp_nav_menu( [
            'theme_location' => 'primary',
            'container'      => false,
        ] ); ?>
        <a class="btn btn-primary" href="<?php echo esc_url( home_url( '/#contact' ) ); ?>" style="margin-top:16px;width:100%;justify-content:center;display:inline-flex;">Let's Talk →</a>
    </nav>
    <?php
}

// ── 12. REMOVE GENESIS FEATURES WE DON'T USE ────────────────────────────────
remove_action( 'genesis_before_loop', 'genesis_do_breadcrumbs' );
add_filter( 'genesis_footer_backtotop_text', '__return_empty_string' );
add_filter( 'genesis_footer_creds_text',     '__return_empty_string' );
remove_action( 'genesis_footer', 'genesis_footer_markup_open', 5 );
remove_action( 'genesis_footer', 'genesis_do_footer' );
remove_action( 'genesis_footer', 'genesis_footer_markup_close', 15 );

// Add custom footer
add_action( 'genesis_footer', 'powerdata_custom_footer', 10 );
function powerdata_custom_footer() {
	?>
	<footer class="site-footer" itemscope itemtype="https://schema.org/WPFooter">
		<div class="pd-wrap">
			<div class="pd-footer-top">
				<div>
					<div style="margin-bottom:16px;">
						<?php if ( has_custom_logo() ) : ?>
							<?php echo get_custom_logo(); ?>
						<?php else : ?>
							<span style="font-family:var(--font-display);font-weight:700;font-size:22px;color:#fff;"><?php bloginfo( 'name' ); ?></span>
						<?php endif; ?>
					</div>
					<p class="pd-foot-desc">Practical cyber protection training, business planning consulting, and PRIAM — simple software for policies, risk, incidents, and assets. Built for small business owners.</p>
				</div>
				<div>
					<h5>Offerings</h5>
					<ul>
						<li><a href="<?php echo home_url( '/training/' ); ?>">Training</a></li>
						<li><a href="<?php echo home_url( '/consulting/' ); ?>">Consulting</a></li>
						<li><a href="<?php echo home_url( '/priam/' ); ?>">PRIAM Platform</a></li>
					</ul>
				</div>
				<div>
					<h5>Company</h5>
					<ul>
						<li><a href="<?php echo home_url( '/#about' ); ?>">About</a></li>
						<li><a href="<?php echo home_url( '/#articles' ); ?>">Articles</a></li>
						<li><a href="<?php echo home_url( '/#contact' ); ?>">Let's Talk</a></li>
						<li><a href="https://www.linkedin.com/company/powerdatasolutions/" target="_blank" rel="noopener">LinkedIn ↗</a></li>
					</ul>
				</div>
				<div>
					<h5>PRIAM</h5>
					<ul>
						<li><a href="<?php echo home_url( '/priam/' ); ?>">Overview</a></li>
						<li><a href="https://priamtiv.com" target="_blank" rel="noopener">priamtiv.com ↗</a></li>
						<li><a href="https://priamtiv.com/webform/contact" target="_blank" rel="noopener">Book a walkthrough ↗</a></li>
					</ul>
				</div>
			</div>
			<div class="pd-footer-bottom">
				<span>© <?php echo esc_html( date( 'Y' ) ); ?> PowerData Solutions Inc. All rights reserved.</span>
				<div class="links">
					<a href="https://priamtiv.com/privacy-policy" target="_blank" rel="noopener">Privacy</a>
					<a href="https://priamtiv.com/terms" target="_blank" rel="noopener">Terms</a>
				</div>
			</div>
		</div>
	</footer>
	<?php
}

// ── 13. GUTENBERG / BLOCK EDITOR SUPPORT ─────────────────────────────────────
add_theme_support( 'align-wide' );
add_theme_support( 'editor-styles' );
add_theme_support( 'responsive-embeds' );
add_theme_support( 'wp-block-styles' );

// Load editor styles that mirror front-end
add_editor_style( 'assets/editor-style.css' );

// Remove inline block CSS that conflicts with our design system
add_filter( 'should_load_separate_core_block_assets', '__return_false' );

// ── 14. HIDE PAGE TITLE OPTION ────────────────────────────────────────────────
add_action( 'add_meta_boxes', 'powerdata_add_page_options_box' );
function powerdata_add_page_options_box() {
	add_meta_box(
		'powerdata-page-options',
		'Page Options',
		'powerdata_page_options_html',
		[ 'page', 'post' ],
		'side',
		'default'
	);
}

function powerdata_page_options_html( $post ) {
	wp_nonce_field( 'powerdata_page_options', 'powerdata_page_options_nonce' );
	$hide = get_post_meta( $post->ID, '_pd_hide_title', true );
	echo '<label style="display:flex;align-items:center;gap:8px;font-size:13px;">';
	echo '<input type="checkbox" name="pd_hide_title" value="1" ' . checked( $hide, '1', false ) . '>';
	echo 'Hide page title</label>';
}

add_action( 'save_post', 'powerdata_save_page_options' );
function powerdata_save_page_options( $post_id ) {
	if ( ! isset( $_POST['powerdata_page_options_nonce'] ) ) return;
	if ( ! wp_verify_nonce( $_POST['powerdata_page_options_nonce'], 'powerdata_page_options' ) ) return;
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) return;
	if ( ! current_user_can( 'edit_post', $post_id ) ) return;

	update_post_meta( $post_id, '_pd_hide_title', isset( $_POST['pd_hide_title'] ) ? '1' : '' );
}

add_filter( 'genesis_post_title_output', 'powerdata_maybe_hide_title' );
function powerdata_maybe_hide_title( $title ) {
	if ( get_post_meta( get_the_ID(), '_pd_hide_title', true ) ) {
		return '';
	}
	return $title;
}
