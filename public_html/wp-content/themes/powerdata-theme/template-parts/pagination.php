<?php
/**
 * Shared pagination for listing templates.
 * Usage: get_template_part( 'template-parts/pagination' );
 */
the_posts_pagination( [
	'prev_text' => '← Newer',
	'next_text' => 'Older →',
	'mid_size'  => 1,
] );
