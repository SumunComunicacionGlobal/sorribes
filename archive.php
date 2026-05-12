<?php
/**
 * The template for displaying archive pages
 *
 * @link https://developer.wordpress.org/themes/basics/template-hierarchy/
 *
 * @package _sumun
 */

get_header();

$show_posts = true;
$content_fragment = '';
$subcategories = '';

if ( is_tax( 'tipo' ) ) {
	ob_start();
	get_template_part( 'parts/content-fragment' );
	$content_fragment = ob_get_clean();
	if ( $content_fragment ) {
		$show_posts = false;
	} else {
		ob_start();
		get_template_part( 'parts/subcategories' );
		$subcategories = ob_get_clean();
	}

}
	
?>

	<main id="primary" class="site-main">

		<div class="has-global-padding is-layout-constrained">

			<?php
			if ( $content_fragment ) {
				echo $content_fragment;
			} else {
				echo $subcategories;
			}
			?>

			<?php if ( $show_posts && have_posts() ) : ?>

				<?php if ( is_tax( 'tipo') ) {
					$queried_object = get_queried_object();
					$term_name = $queried_object->name;
					echo '<div><p class="is-style-pill">' . __( 'Nuestros productos', 'sorribes' ) . '</p></div>';
					echo '<h2 class="loop-title">' . sprintf( __( 'Modelos de %s', 'sorribes' ), '<mark class="has-inline-color has-primary-30-color">' . strtolower( $term_name ) . '</mark>' ) . '</h2>';
				} ?>

				<div class="is-layout-grid smn-default-grid smn-archive-posts-grid">


					<?php
					/* Start the Loop */
					while ( have_posts() ) :
						the_post();

						/*
						* Include the Post-Type-specific template for the content.
						* If you want to override this in a child theme, then include a file
						* called content-___.php (where ___ is the Post Type name) and that will be used instead.
						*/
						get_template_part( 'template-parts/content', get_post_type() );

					endwhile; ?>

				</div>

				<?php the_posts_navigation();

			endif;
			?>

			<?php
			if ( is_tax('tipo') && !$content_fragment && !$subcategories ) {
				block_template_part( 'area-pedir-presupuesto' );
			} ?>

		</div><!-- .has-global-padding -->

	</main><!-- #main -->

<?php
if ( is_blog() ) get_sidebar();
get_footer();
