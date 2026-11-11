<?php
/**
 * Template part for displaying posts.
 *
 * @link https://codex.WordPress.org/Template_Hierarchy
 *
 * @package ignite_agency_portfolio
 */
$ignite_agency_portfolio_heading_setting  = get_theme_mod( 'ignite_agency_portfolio_post_heading_setting' , true );
$ignite_agency_portfolio_meta_setting  = get_theme_mod( 'ignite_agency_portfolio_post_meta_setting' , true );
$ignite_agency_portfolio_content_setting  = get_theme_mod( 'ignite_agency_portfolio_post_content_setting' , true );
?>

<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
	<header class="entry-header">
		<?php
		  if ( $ignite_agency_portfolio_heading_setting ){ 
			if ( is_single() ) {
				the_title( '<h1 class="entry-title" itemprop="headline">', '</h1>' );
			} else {
				the_title( '<h2 class="entry-title" itemprop="headline"><a href="' . esc_url( get_permalink() ) . '" rel="bookmark">', '</a></h2>' );
			}
		  }

		if ( 'post' === get_post_type() ) : ?>
		<?php
		if ( $ignite_agency_portfolio_meta_setting ){ ?>
			<div class="entry-meta">
				<?php ignite_agency_portfolio_posted_on(); ?>
			</div><!-- .entry-meta -->
		<?php } ?>
		<?php
		endif; ?>
	</header><!-- .entry-header -->
	 <?php
			// Get the post ID
			$ignite_agency_portfolio_post_id = get_the_ID();

			// Check if there are videos embedded in the post content
			$ignite_agency_portfolio_post = get_post($ignite_agency_portfolio_post_id);
			$ignite_agency_portfolio_content = do_shortcode(apply_filters('the_content', $ignite_agency_portfolio_post->post_content));
			$ignite_agency_portfolio_embeds = get_media_embedded_in_content($ignite_agency_portfolio_content);

			if (!empty($ignite_agency_portfolio_embeds)) {
			    // Loop through embedded media and display videos
			    foreach ($ignite_agency_portfolio_embeds as $embed) {
			        // Check if the embed code contains a video tag or specific video providers like YouTube or Vimeo
			        if (strpos($embed, 'video') !== false || strpos($embed, 'youtube') !== false || strpos($embed, 'vimeo') !== false || strpos($embed, 'dailymotion') !== false || strpos($embed, 'vine') !== false || strpos($embed, 'WordPress.tv') !== false || strpos($embed, 'hulu') !== false) {
			            ?>
			            <div class="custom-embedded-video">
			                <div class="video-container">
			                    <?php echo $embed; ?>
			                </div>
			                <div class="video-comments">
			                    <?php
			                    // Add your comments section here
			                    comments_template(); // This will include the default WordPress comments template
			                    ?>
			                </div>
			            </div>
			            <?php
			        }
			    }
			}
	    ?>
    <?php
	if ( $ignite_agency_portfolio_content_setting ){ ?>
		<div class="entry-content" itemprop="text">
			<?php
			if( is_single()){
				the_content( sprintf(
					/* translators: %s: Name of current post. */
					wp_kses( __( 'Continue reading %s <span class="meta-nav">&rarr;</span>', 'ignite-agency-portfolio' ), array( 'span' => array( 'class' => array() ) ) ),
					the_title( '<span class="screen-reader-text">"', '"</span>', false )
				) );
				}else{
				the_excerpt();
				}
				wp_link_pages( array(
					'before' => '<div class="page-links">' . esc_html__( 'Pages:', 'ignite-agency-portfolio' ),
					'after'  => '</div>',
				) );
			?>
		</div><!-- .entry-content -->
    <?php } ?>
</article><!-- #post-## -->