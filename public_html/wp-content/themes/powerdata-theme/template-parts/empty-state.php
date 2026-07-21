<?php
/**
 * Friendly zero-results state for listing templates.
 * Usage: get_template_part( 'template-parts/empty-state', null, [
 *     'message' => 'Custom copy for this listing.',
 * ] );
 */
$message = isset( $args['message'] )
	? $args['message']
	: "We couldn't find any articles here yet. Check back soon.";
?>
<div class="pd-empty-state" style="text-align:center;padding:60px 20px;">
	<p class="lede"><?php echo esc_html( $message ); ?></p>
</div>
