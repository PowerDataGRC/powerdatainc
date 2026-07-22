<?php
/**
 * Template Name: PD Article
 * Template Post Type: post, page
 *
 * Kept for any Page that explicitly selects this template. All posts
 * render the same layout automatically via single.php — the markup
 * itself lives in inc/article-render.php, the single source of truth.
 *
 * @package PowerData
 */

remove_action( 'genesis_loop', 'genesis_do_loop' );
add_action( 'genesis_loop', 'pd_render_article_template' );

genesis();
