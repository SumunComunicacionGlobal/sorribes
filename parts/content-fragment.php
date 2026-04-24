<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

if ( !is_tax( 'tipo' ) ) return false;

$term_slug = get_queried_object()->slug;

$args = array(
    'post_type'      => 'content_fragment',
    'posts_per_page' => 1,
    'name'         => $term_slug,
);

$fragment_query = new WP_Query( $args );

if ( $fragment_query->have_posts() ) {
    while ( $fragment_query->have_posts() ) {
        $fragment_query->the_post();
        the_content();
    }
    wp_reset_postdata();
}