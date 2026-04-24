<?php
/**
 * Template part for displaying posts
 *
 * @link https://developer.wordpress.org/themes/basics/template-hierarchy/
 *
 * @package _sumun
 */

?>

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

echo '<div class="wp-block-spacer" style="height:var(--wp--preset--spacing--40)" aria-hidden="true"></div>';

get_template_part( 'parts/galeria' );

get_template_part( 'parts/descargables' );

echo '<div id="presupuesto"></div>';

block_template_part( 'area-pedir-presupuesto' );

wp_link_pages(
	array(
		'before' => '<div class="page-links">' . esc_html__( 'Pages:', 'sorribes' ),
		'after'  => '</div>',
	)
);
?>

<footer class="entry-footer">
<?php smn_hybrid_entry_footer(); ?>
</footer><!-- .entry-footer -->

