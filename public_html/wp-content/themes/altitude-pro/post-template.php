<?php
/**
 * Altitude Pro.
 *
 * This file adds the post page template to the Altitude Pro Theme.
 *
 * Template Name: Custom Post
 * Template Post Type: post 
 *
 * @package Altitude Pro
 * @author  MurrayS.
 * @license GPL-2.0-or-later
 * @link    https://my.studiopress.com/themes/altitude/
 */

add_filter( 'body_class', 'altitude_add_body_class' );
/**
 * Adds landing page body class.
 *
 * @since 1.0.0
 *
 * @param array $classes Original body classes.
 * @return array Modified body classes.
 */
function altitude_add_body_class( $classes ) {

	$classes[] = 'altitude-landing';

	return $classes;

}

// includes the header template file.
get_header();
if ( have_posts() ) : 
    while ( have_posts() ) : 
        the_post();
        the_title();
        the_content();
        comments_template();
        the_post_navigation();
    endwhile;
endif;

// Forces full width content layout.
add_filter( 'genesis_site_layout', '__genesis_return_full_width_content' );

// Removes site header elements.
remove_action( 'genesis_header', 'genesis_header_markup_open', 5 );
remove_action( 'genesis_header', 'genesis_do_header' );
remove_action( 'genesis_header', 'genesis_header_markup_close', 15 );

// Removes navigation.
remove_action( 'genesis_header', 'genesis_do_nav', 12 );
remove_action( 'genesis_header', 'genesis_do_subnav', 5 );
remove_action( 'genesis_footer', 'altitude_footer_menu', 7 );

// Removes breadcrumbs.
remove_action( 'genesis_before_loop', 'genesis_do_breadcrumbs' );

// Removes site footer widgets.
remove_action( 'genesis_before_footer', 'genesis_footer_widget_areas' );

// Removes site footer elements.
remove_action( 'genesis_footer', 'genesis_footer_markup_open', 5 );
remove_action( 'genesis_footer', 'genesis_do_footer' );
remove_action( 'genesis_footer', 'genesis_footer_markup_close', 15 );

// Runs the Genesis loop.
genesis();
