<?php
/**
 * Functions which enhance the theme by hooking into WordPress
 *
 * @package _sumun
 */

if ( ! function_exists( 'smn_support' ) ) :

	/**
	 * Sets up theme defaults and registers support for various WordPress features.
	 *
	 *
	 * @return void
	 */
	function smn_support() {

		// Add support for block styles.
		add_theme_support( 'wp-block-styles' );

		// Add support for editor styles.
		add_theme_support( 'editor-styles' );

		// Enqueue editor styles.
		add_editor_style( 'style.css' );

		// Add support for excerpts in pages.
		add_post_type_support( 'page', 'excerpt' );

		// To use your template part inside your theme’s create a .html in /parts
		// and then put the php function "block_template_part( 'part-name' );" where you want to call it.
		// You can also create a template like page.html in /templates. And insert a part inside it: <!-- wp:template-part {"slug":"part-name"} /-->
		add_theme_support( 'block-template-parts' );

	}

endif;

add_action( 'after_setup_theme', 'smn_support' );


/**
 * Adds custom classes to the array of body classes.
 *
 * @param array $classes Classes for the body element.
 * @return array
 */
function smn_hybrid_body_classes( $classes ) {
	// Adds a class of hfeed to non-singular pages.
	if ( ! is_singular() ) {
		$classes[] = 'hfeed';
	}

	// Adds a class of no-sidebar when there is no sidebar present.
	if ( ! is_active_sidebar( 'sidebar-1' ) ) {
		$classes[] = 'no-sidebar';
	}

	return $classes;
}
add_filter( 'body_class', 'smn_hybrid_body_classes' );

/**
 * Add a pingback url auto-discovery header for single posts, pages, or attachments.
 */
function smn_hybrid_pingback_header() {
	if ( is_singular() && pings_open() ) {
		printf( '<link rel="pingback" href="%s">', esc_url( get_bloginfo( 'pingback_url' ) ) );
	}
}
add_action( 'wp_head', 'smn_hybrid_pingback_header' );

function smn_add_body_lines_element() {
	echo '<div class="body-lines">
			<div class="body-lines--inner">
				<div class="line"></div>
				<div class="line"></div>
				<div class="line"></div>
				<div class="line"></div>
				<div class="line"></div>
			</div>
		</div>';
}
add_action( 'wp_body_open', 'smn_add_body_lines_element' );

/**
 * Obtiene la imagen destacada (thumbnail_id) de un término.
 * Si no existe, obtiene la imagen destacada del primer post tipo "solucion" que tenga ese término.
 *
 * @param WP_Term|int $term El objeto término o su ID.
 * @return int|null ID de la imagen destacada o null si no existe.
 */
function smn_get_term_thumbnail_id( $term ) {
	$term_id = is_object( $term ) ? $term->term_id : intval( $term );

	// Intenta obtener el thumbnail_id del término.
	$thumbnail_id = get_term_meta( $term_id, 'thumbnail_id', true );
	if ( ! empty( $thumbnail_id ) ) {
		return (int) $thumbnail_id;
	}

	// Si no existe, busca el primer post tipo "solucion" con ese término.
	$args = array(
		'post_type'      => 'solucion',
		'posts_per_page' => 1,
		'tax_query'      => array(
			array(
				'taxonomy' => get_term( $term_id )->taxonomy,
				'field'    => 'term_id',
				'terms'    => $term_id,
			),
		),
		'post_status'    => 'publish',
		'fields'         => 'ids',
	);

	$query = new WP_Query( $args );
	if ( ! empty( $query->posts ) ) {
		$post_id = $query->posts[0];
		$post_thumbnail_id = get_post_thumbnail_id( $post_id );
		if ( $post_thumbnail_id ) {
			return (int) $post_thumbnail_id;
		}
	}

	return null;
}