<?php
/**
 * Default template for individual articles (posts) — every post uses this
 * automatically, no per-post template selection needed. Markup lives in
 * inc/article-render.php, the single source of truth for the layout also
 * used by page-article.php.
 *
 * @package PowerData
 */

remove_action( 'genesis_loop', 'genesis_do_loop' );
add_action( 'genesis_loop', 'pd_render_article_template' );

genesis();
