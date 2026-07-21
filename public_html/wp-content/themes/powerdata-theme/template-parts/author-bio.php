<?php
/**
 * Author bio block. Reads user-level Pods fields — the single source of
 * truth for author bio/title/photo. Do not duplicate this markup elsewhere.
 * Usage: get_template_part( 'template-parts/author-bio' );
 * Must be called inside the loop, or pass an explicit author ID via $args.
 */

$author_id = isset( $args['author_id'] ) ? (int) $args['author_id'] : (int) get_the_author_meta( 'ID' );
if ( ! $author_id ) {
    return;
}

$bio      = '';
$title    = '';
$photo    = '';
$linkedin = '';

if ( function_exists( 'pods' ) ) {
    $pod = pods( 'user', $author_id );
    if ( $pod && $pod->exists() ) {
        $bio      = $pod->display( 'author_bio' );
        $title    = $pod->display( 'author_title' );
        $photo    = $pod->field( 'author_photo.guid' );
        $linkedin = $pod->display( 'author_linkedin' );
    }
}

if ( ! $bio ) {
    $bio = get_the_author_meta( 'description', $author_id ); // core fallback
}
if ( ! $bio ) {
    return; // nothing to show — render nothing rather than an empty box
}

$name = get_the_author_meta( 'display_name', $author_id );
?>
<aside class="author-bio card">
    <div class="author-bio__media">
        <?php if ( $photo ) : ?>
            <img src="<?php echo esc_url( $photo ); ?>"
                 alt="<?php echo esc_attr( $name ); ?>"
                 width="72" height="72" loading="lazy">
        <?php else : ?>
            <?php echo get_avatar( $author_id, 72 ); ?>
        <?php endif; ?>
    </div>
    <div class="author-bio__body">
        <div class="author-bio__name-row">
            <b class="author-bio__name"><?php echo esc_html( $name ); ?></b>
            <?php if ( $title ) : ?>
                <span class="author-bio__title muted"><?php echo esc_html( $title ); ?></span>
            <?php endif; ?>
            <?php if ( $linkedin ) : ?>
                <a href="<?php echo esc_url( $linkedin ); ?>"
                   target="_blank" rel="noopener noreferrer"
                   class="author-bio__social"
                   aria-label="<?php echo esc_attr( $name ); ?> on LinkedIn">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                        <path d="M20.447 20.452h-3.554v-5.569c0-1.328-.027-3.037-1.852-3.037-1.853 0-2.136 1.445-2.136 2.939v5.667H9.351V9h3.414v1.561h.046c.477-.9 1.637-1.85 3.37-1.85 3.601 0 4.267 2.37 4.267 5.455v6.286zM5.337 7.433a2.062 2.062 0 01-2.063-2.065 2.064 2.064 0 112.063 2.065zm1.782 13.019H3.555V9h3.564v11.452zM22.225 0H1.771C.792 0 0 .774 0 1.729v20.542C0 23.227.792 24 1.771 24h20.451C23.2 24 24 23.227 24 22.271V1.729C24 .774 23.2 0 22.222 0h.003z"/>
                    </svg>
                </a>
            <?php endif; ?>
        </div>
        <div class="author-bio__text"><?php echo wp_kses_post( $bio ); ?></div>
    </div>
</aside>
