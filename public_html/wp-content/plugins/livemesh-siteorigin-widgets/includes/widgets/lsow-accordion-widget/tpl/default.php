<?php

/**
 * @var $settings
 */
?>

<?php 
if ( !empty( $instance['title'] ) ) {
    echo $args['before_title'] . esc_html( $instance['title'] ) . $args['after_title'];
}
$settings = apply_filters( 'lsow_accordion_' . $this->id . '_settings', $settings );
$output = '<div class="lsow-accordion ' . esc_attr( $settings['style'] ) . '" data-toggle="' . esc_attr( ( $settings['toggle'] ? "true" : "false" ) ) . '" data-expanded="' . esc_attr( ( $settings['expanded'] ? "true" : "false" ) ) . '">';
foreach ( $settings['accordion'] as $panel ) {
    $panel_id = '';
    $child_output = '<div class="lsow-panel" id="' . esc_attr( $panel_id ) . '">';
    $child_output .= '<div class="lsow-panel-title">' . htmlspecialchars_decode( esc_html( $panel['title'] ) ) . '</div>';
    $child_output .= '<div class="lsow-panel-content">' . do_shortcode( $panel['panel_content'] ) . '</div>';
    $child_output .= '</div><!-- .lsow-panel -->';
    $output .= apply_filters(
        'lsow_accordion_item_output',
        $child_output,
        $panel,
        $settings
    );
}
$output .= '</div><!-- .lsow-accordion -->';
echo apply_filters( 'lsow_accordion_output', $output, $settings );