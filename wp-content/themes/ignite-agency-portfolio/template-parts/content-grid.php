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
$ignite_agency_portfolio_image_setting  = get_theme_mod( 'ignite_agency_portfolio_post_image_setting' , true );
$ignite_agency_portfolio_content_setting  = get_theme_mod( 'ignite_agency_portfolio_post_content_setting' , true );
$ignite_agency_portfolio_read_more_setting = get_theme_mod( 'ignite_agency_portfolio_read_more_setting' , true );
$ignite_agency_portfolio_read_more_text = get_theme_mod( 'ignite_agency_portfolio_read_more_text', __( 'Read More', 'ignite-agency-portfolio' ) );
?>

<div class="col-lg-4 col-md-6">
<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
    <?php 
    $ignite_agency_portfolio_meta_order = get_theme_mod('ignite_agency_portfolio_blog_meta_order', array('heading', 'author', 'featured-image', 'content', 'button'));
    
    foreach ($ignite_agency_portfolio_meta_order as $ignite_agency_portfolio_order) :
        if ('heading' === $ignite_agency_portfolio_order) :
            if ($ignite_agency_portfolio_heading_setting) { ?>
                <header class="entry-header">
                    <?php if (is_single()) {
                        the_title('<h1 class="entry-title" itemprop="headline">', '</h1>');
                    } else {
                        the_title('<h2 class="entry-title" itemprop="headline"><a href="' . esc_url(get_permalink()) . '" rel="bookmark">', '</a></h2>');
                    } ?>
                </header>
            <?php }
        endif;

        if ('author' === $ignite_agency_portfolio_order) :
            if ('post' === get_post_type() && $ignite_agency_portfolio_meta_setting) { ?>
                <div class="entry-meta">
                    <?php ignite_agency_portfolio_posted_on(); ?>
                </div>
            <?php }
        endif;

        if ('featured-image' === $ignite_agency_portfolio_order) :
            if ($ignite_agency_portfolio_image_setting) { ?>
                <?php echo (!is_single()) ? '<a href="' . esc_url(get_the_permalink()) . '" class="post-thumbnail">' : '<div class="post-thumbnail">'; ?>
                    <?php if (has_post_thumbnail()) {
                        if (is_active_sidebar('right-sidebar')) {
                            the_post_thumbnail('ignite-agency-portfolio-with-sidebar', array('itemprop' => 'image'));
                        } else {
                            the_post_thumbnail('ignite-agency-portfolio-without-sidebar', array('itemprop' => 'image'));
                        }
                    } else { ?>
                        <img src="<?php echo esc_url(get_stylesheet_directory_uri() . '/images/default.png'); ?>">
                    <?php } ?>
                <?php echo (!is_single()) ? '</a>' : '</div>'; ?>
            <?php }
        endif;

        if ('content' === $ignite_agency_portfolio_order) :
            if ($ignite_agency_portfolio_content_setting) { ?>
                <div class="entry-content" itemprop="text">
                    <?php if (is_single()) {
                        the_content(
                            sprintf(
                                wp_kses(
                                    __('Continue reading %s <span class="meta-nav">&rarr;</span>', 'ignite-agency-portfolio'),
                                    array('span' => array('class' => array()))
                                ),
                                '<span class="screen-reader-text">"' . get_the_title() . '"</span>'
                            )
                        );
                    } else {
                        the_excerpt();
                    }
                    
                    wp_link_pages(array(
                        'before' => '<div class="page-links">' . esc_html__('Pages:', 'ignite-agency-portfolio'),
                        'after'  => '</div>',
                    ));
                    ?>
                </div>
            <?php }
        endif;

        if ('button' === $ignite_agency_portfolio_order) :
            if (!is_single() && $ignite_agency_portfolio_read_more_setting) { ?>
                <div class="read-more-button">
                    <a href="<?php echo esc_url(get_permalink()); ?>" class="read-more-button"><?php echo esc_html($ignite_agency_portfolio_read_more_text); ?></a>
                </div>
            <?php }
        endif;
    endforeach; ?>
</article>
</div>