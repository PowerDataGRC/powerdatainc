<?php
/**
 * ACF Local Field Groups — PowerData Templates
 *
 * Registers field groups programmatically so no JSON export is needed.
 * Requires Advanced Custom Fields (free or Pro) to be installed & activated.
 *
 * Field groups are displayed based on the page template selected in the editor:
 *   - "PD Article" template  → article author fields
 *   - "PD Project" template  → project meta, stats, testimonial, toolbox
 *
 * @package PowerData
 */

if ( ! function_exists( 'acf_add_local_field_group' ) ) {
	return;
}

// ─────────────────────────────────────────────────────────────────────────────
// 1. ARTICLE TEMPLATE FIELDS
// ─────────────────────────────────────────────────────────────────────────────
acf_add_local_field_group( [
	'key'                   => 'group_pd_article',
	'title'                 => 'Article — Author & Related Posts',
	'fields'                => [

		// ── Author name ──────────────────────────────────────────────────────
		[
			'key'           => 'field_pd_article_author_name',
			'label'         => 'Author Name',
			'name'          => 'article_author_name',
			'type'          => 'text',
			'instructions'  => 'Full name shown in the byline and author bio box. Defaults to the WordPress user\'s display name if left blank.',
			'required'      => 0,
			'placeholder'   => 'Murray S.',
		],

		// ── Author title / role ───────────────────────────────────────────────
		[
			'key'           => 'field_pd_article_author_title',
			'label'         => 'Author Title / Role',
			'name'          => 'article_author_title',
			'type'          => 'text',
			'instructions'  => 'E.g. "Solutions Architect, PowerData Solutions".',
			'required'      => 0,
			'placeholder'   => 'Solutions Architect, PowerData Solutions',
		],

		// ── Author bio ────────────────────────────────────────────────────────
		[
			'key'           => 'field_pd_article_author_bio',
			'label'         => 'Author Bio',
			'name'          => 'article_author_bio',
			'type'          => 'textarea',
			'instructions'  => 'One or two sentences shown beneath the article body.',
			'required'      => 0,
			'rows'          => 3,
			'placeholder'   => 'Murray is a solutions architect with PowerData Solutions with over 20 years of experience in SMB risk management.',
		],

		// ── Author headshot ───────────────────────────────────────────────────
		[
			'key'           => 'field_pd_article_author_photo',
			'label'         => 'Author Photo',
			'name'          => 'article_author_photo',
			'type'          => 'image',
			'instructions'  => 'Square headshot. Displayed at 72 × 72 px.',
			'required'      => 0,
			'return_format' => 'array',
			'preview_size'  => 'thumbnail',
			'library'       => 'all',
		],

		// ── LinkedIn URL ──────────────────────────────────────────────────────
		[
			'key'           => 'field_pd_article_author_linkedin',
			'label'         => 'Author LinkedIn URL',
			'name'          => 'article_author_linkedin',
			'type'          => 'url',
			'instructions'  => 'Full LinkedIn profile URL. Leave blank to hide the icon.',
			'required'      => 0,
			'placeholder'   => 'https://www.linkedin.com/in/yourname/',
		],

		// ── Related posts ─────────────────────────────────────────────────────
		[
			'key'           => 'field_pd_article_related_posts',
			'label'         => 'Related Articles',
			'name'          => 'article_related_posts',
			'type'          => 'relationship',
			'instructions'  => 'Select up to 3 articles to show in the "Related Insights" section.',
			'required'      => 0,
			'post_type'     => [ 'post', 'page' ],
			'taxonomy'      => [],
			'filters'       => [ 'search' ],
			'min'           => 0,
			'max'           => 3,
			'return_format' => 'id',
		],

	],

	// Show on any post or page using the PD Article template.
	'location' => [
		[
			[ 'param' => 'page_template', 'operator' => '==', 'value' => 'page-article.php' ],
		],
		[
			[ 'param' => 'post_template', 'operator' => '==', 'value' => 'page-article.php' ],
		],
	],

	'menu_order'            => 0,
	'position'              => 'normal',
	'style'                 => 'default',
	'label_placement'       => 'top',
	'instruction_placement' => 'label',
	'active'                => true,
] );


// ─────────────────────────────────────────────────────────────────────────────
// 2. PROJECT TEMPLATE FIELDS
// ─────────────────────────────────────────────────────────────────────────────
acf_add_local_field_group( [
	'key'                   => 'group_pd_project',
	'title'                 => 'Project — Case Study Details',
	'fields'                => [

		// ── Hero image ────────────────────────────────────────────────────────
		[
			'key'           => 'field_pd_project_hero_image',
			'label'         => 'Hero Background Image',
			'name'          => 'project_hero_image',
			'type'          => 'image',
			'instructions'  => 'Optional. Used as a subtle dark-overlaid background in the hero section. Landscape, min 1400 px wide.',
			'required'      => 0,
			'return_format' => 'array',
			'preview_size'  => 'medium',
			'library'       => 'all',
		],

		// ── Client ────────────────────────────────────────────────────────────
		[
			'key'           => 'field_pd_project_client',
			'label'         => 'Client',
			'name'          => 'project_client',
			'type'          => 'text',
			'instructions'  => 'Client name or "Confidential". Displayed in the hero.',
			'required'      => 0,
			'placeholder'   => 'Confidential — Government Agency',
		],

		// ── Purpose / goal ────────────────────────────────────────────────────
		[
			'key'           => 'field_pd_project_purpose',
			'label'         => 'Project Goal',
			'name'          => 'project_purpose',
			'type'          => 'text',
			'instructions'  => 'One sentence describing what this project set out to achieve.',
			'required'      => 0,
			'placeholder'   => 'To deliver seamless, secure wireless connectivity across multiple locations.',
		],

		// ── Technology tags (hero pills) ──────────────────────────────────────
		[
			'key'           => 'field_pd_project_tech_tags',
			'label'         => 'Technology Tags',
			'name'          => 'project_tech_tags',
			'type'          => 'text',
			'instructions'  => 'Comma-separated short technology labels shown as pills in the hero. E.g. "Wi-Fi 6, Zero Trust, Extreme Wireless".',
			'required'      => 0,
			'placeholder'   => 'Wi-Fi 6, Zero Trust, Extreme Wireless',
		],

		// ── Stats (repeater) ──────────────────────────────────────────────────
		[
			'key'           => 'field_pd_project_stats',
			'label'         => 'At-a-Glance Stats',
			'name'          => 'project_stats',
			'type'          => 'repeater',
			'instructions'  => 'Add 2–4 key outcomes or metrics. Displayed in a highlight row beneath the hero.',
			'required'      => 0,
			'min'           => 0,
			'max'           => 4,
			'layout'        => 'table',
			'button_label'  => 'Add Stat',
			'sub_fields'    => [
				[
					'key'          => 'field_pd_stat_value',
					'label'        => 'Value',
					'name'         => 'stat_value',
					'type'         => 'text',
					'instructions' => 'E.g. "98%" or "8 weeks"',
					'required'     => 1,
					'placeholder'  => '98%',
					'column_width' => '',
				],
				[
					'key'          => 'field_pd_stat_label',
					'label'        => 'Label',
					'name'         => 'stat_label',
					'type'         => 'text',
					'instructions' => 'E.g. "Reduction in support tickets"',
					'required'     => 1,
					'placeholder'  => 'Reduction in support tickets',
					'column_width' => '',
				],
			],
		],

		// ── Testimonial quote ─────────────────────────────────────────────────
		[
			'key'           => 'field_pd_project_testimonial_quote',
			'label'         => 'Client Testimonial',
			'name'          => 'project_testimonial_quote',
			'type'          => 'textarea',
			'instructions'  => 'Quote text only — do not include quotation marks; they are added automatically.',
			'required'      => 0,
			'rows'          => 3,
			'placeholder'   => 'The team delivered exactly what they promised, on time and within budget.',
		],

		// ── Testimonial attribution ───────────────────────────────────────────
		[
			'key'           => 'field_pd_project_testimonial_attr',
			'label'         => 'Quote Attribution',
			'name'          => 'project_testimonial_attr',
			'type'          => 'text',
			'instructions'  => 'Name and / or role shown beneath the quote. E.g. "Director of IT, County Government".',
			'required'      => 0,
			'placeholder'   => 'Director of IT, County Government',
			'conditional_logic' => [
				[
					[
						'field'    => 'field_pd_project_testimonial_quote',
						'operator' => '!=empty',
					],
				],
			],
		],

		// ── Technologies / toolbox ────────────────────────────────────────────
		[
			'key'           => 'field_pd_project_technologies',
			'label'         => 'Technologies & Methodologies',
			'name'          => 'project_technologies',
			'type'          => 'text',
			'instructions'  => 'Comma-separated list displayed as styled tags in the toolbox section. E.g. "PRIAM, Microsoft 365, Cloudflare, NIST CSF".',
			'required'      => 0,
			'placeholder'   => 'PRIAM, Microsoft 365, Cloudflare, NIST CSF',
		],

	],

	// Show on any post or page using the PD Project template.
	'location' => [
		[
			[ 'param' => 'page_template', 'operator' => '==', 'value' => 'page-project.php' ],
		],
		[
			[ 'param' => 'post_template', 'operator' => '==', 'value' => 'page-project.php' ],
		],
	],

	'menu_order'            => 0,
	'position'              => 'normal',
	'style'                 => 'default',
	'label_placement'       => 'top',
	'instruction_placement' => 'label',
	'active'                => true,
] );
