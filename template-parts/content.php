<?php
/**
 * Template part for displaying posts
 *
 * @link https://developer.wordpress.org/themes/basics/template-hierarchy/
 *
 * @package _sumun
 */

global $post;

$post_type = get_post_type();
$post_class = '';
if ( !is_singular() ) {
	$post_class = 'card';
}
$title_tag = 'h2';
if ( 'solucion' === $post_type ) {
	$title_tag = 'h3';
}
?>

<article id="post-<?php the_ID(); ?>" <?php post_class( $post_class ); ?>>
	<header class="entry-header">
		<?php
		if ( 'post' === $post_type ) : ?>

			<div class="entry-meta">
				<?php
				smn_hybrid_posted_on();
				?>
			</div><!-- .entry-meta -->

		<?php elseif( 'solucion' == $post_type ) : ?>

			<div class="entry-meta">
				<span class="is-style-pill"><?php _e( 'Ver ficha técnica', 'sorribes' ); ?></span>
			</div><!-- .entry-meta -->

		<?php endif; ?>

		<?php if ( !is_singular() ) :
			the_title( '<' . $title_tag . ' class="entry-title"><a href="' . esc_url( get_permalink() ) . '" rel="bookmark">', '</a></' . $title_tag . '>' );
			$excerpt = $post->post_excerpt;
			if ( $excerpt ) :
				echo '<div class="entry-summary">' . wpautop( $excerpt ) . '</div>';
			endif;
		endif; ?>


	</header><!-- .entry-header -->

	<?php if ( is_singular() ) : ?>

		<div class="entry-content">
			<?php
			the_content(
				sprintf(
					wp_kses(
						/* translators: %s: Name of current post. Only visible to screen readers */
						__( 'Continue reading<span class="screen-reader-text"> "%s"</span>', 'sorribes' ),
						array(
							'span' => array(
								'class' => array(),
							),
						)
					),
					wp_kses_post( get_the_title() )
				)
			);

			wp_link_pages(
				array(
					'before' => '<div class="page-links">' . esc_html__( 'Pages:', 'sorribes' ),
					'after'  => '</div>',
				)
			);
			?>
		</div><!-- .entry-content -->

	<?php endif; ?>

	<?php smn_hybrid_post_thumbnail(); ?>

</article><!-- #post-<?php the_ID(); ?> -->
