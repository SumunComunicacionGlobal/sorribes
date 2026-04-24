<?php
/**
 * The template for displaying the footer
 *
 * Contains the closing of the #content div and all content after.
 *
 * @link https://developer.wordpress.org/themes/basics/template-files/#template-partials
 *
 * @package _sumun
 */

?>

	<?php 
	if ( is_singular( 'solucion' ) ) {
		block_template_part( 'prefooter-producto' ); 
	}	
	?>

	<footer id="colophon" class="site-footer">
		<?php block_template_part( 'footer' ); ?>
	</footer><!-- #colophon -->
</div><!-- #page -->

<?php wp_footer(); ?>

</body>
</html>
