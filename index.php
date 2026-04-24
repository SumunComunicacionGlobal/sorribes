<?php
/**
 * The main template file
 *
 * This is the most generic template file in a WordPress theme
 * and one of the two required files for a theme (the other being style.css).
 * It is used to display a page when nothing more specific matches a query.
 * E.g., it puts together the home page when no home.php file exists.
 *
 * @link https://developer.wordpress.org/themes/basics/template-hierarchy/
 *
 * @package _sumun
 */

get_header();
?>

	<main id="primary" class="site-main">

		<div class="has-global-padding is-layout-constrained">

			<?php if ( is_home() && ! is_front_page() ) :
				$page_for_posts_id = get_option( 'page_for_posts' );
				if ( $page_for_posts_id ) {
					$page = get_post( $page_for_posts_id );
					$post_content = apply_filters( 'the_content', $page->post_content );
					echo $post_content;
				}
			endif; ?>

			<?php
			if ( have_posts() ) : ?>

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

				endwhile;

				the_posts_navigation();

			else :

				get_template_part( 'template-parts/content', 'none' );

			endif;
			?>

		</div>

	</main><!-- #main -->

<?php
get_footer();
