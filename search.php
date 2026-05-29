<?php
/**
 * The template for displaying search results pages
 *
 * @link https://developer.wordpress.org/themes/basics/template-hierarchy/#search-result
 *
 * @package _sumun
 */

get_header();
?>

	<main id="primary" class="site-main">

		<div class="has-global-padding is-layout-constrained">

			<?php if ( have_posts() ) : ?>

				<ul class="wp-block-list is-style-arrow-separator-list search-results-list mb-3">

				<?php
				/* Start the Loop */
				while ( have_posts() ) :
					the_post();

					/**
					 * Run the loop for the search to output the results.
					 * If you want to overload this in a child theme then include a file
					 * called content-search.php and that will be used instead.
					 */
					// get_template_part( 'template-parts/content', 'search' );
					?>

					<li class="has-heading-4-font-size">
						<a href="<?php the_permalink(); ?>" title="<?php the_title_attribute(); ?>">
							<?php the_title(); ?>
						</a>
						<?php edit_post_link( ' - ' . __( 'Edit', '_sumun' ), '<span class="edit-link">', '</span>' ); ?>
					</li>

					<?php

				endwhile; ?>

				</ul>

				<?php
				the_posts_navigation();

			else :

				get_template_part( 'template-parts/content', 'none' );

			endif;
			?>

		</div>

	</main><!-- #main -->

<?php
get_footer();
