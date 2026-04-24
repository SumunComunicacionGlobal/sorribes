<?php

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

if ( !is_tax() && !is_category() && !is_tag() ) return;
$current_term = get_queried_object();


$terms = do_shortcode( '[terms taxonomy="' . $current_term->taxonomy . '" parent="' . $current_term->term_id . '"]' );
if ( $terms ) {
	echo $terms;
}