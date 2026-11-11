<?php
/**
 * Template part for displaying page content in page.php.
 *
 * @link https://codex.WordPress.org/Template_Hierarchy
 *
 * @package ignite_agency_portfolio
 */

?>

<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
	

	<div class="entry-content">
		<?php

			the_content();

			wp_link_pages( array(
				'before' => '<div class="page-links">' . esc_html__( 'Pages:', 'ignite-agency-portfolio' ),
				'after'  => '</div>',
			) );
		?>
	</div><!-- .entry-content -->
</article><!-- #post-## -->
