<?php
/**
 * The template for displaying the footer.
 *
 * Contains the closing of the #content div and all content after.
 *
 * @link https://developer.WordPress.org/themes/basics/template-files/#template-partials
 *
 * @package ignite_agency_portfolio
 */
$ignite_agency_portfolio_scroll_top  = get_theme_mod( 'ignite_agency_portfolio_scroll_to_top', true );
$ignite_agency_portfolio_footer_background = get_theme_mod('ignite_agency_portfolio_footer_background_image');
$ignite_agency_portfolio_footer_background_url = '';
if(!empty($ignite_agency_portfolio_footer_background)){
    $ignite_agency_portfolio_footer_background = absint($ignite_agency_portfolio_footer_background);
    $ignite_agency_portfolio_footer_background_url = wp_get_attachment_url($ignite_agency_portfolio_footer_background);
}

$ignite_agency_portfolio_footer_background_color = get_theme_mod('ignite_agency_portfolio_footer_background_color', '#000'); // New line

$ignite_agency_portfolio_footer_background_style = '';
if (!empty($ignite_agency_portfolio_footer_background_url)) {
    $ignite_agency_portfolio_footer_background_style = ' style="background-image: url(\'' . esc_url($ignite_agency_portfolio_footer_background_url) . '\'); background-repeat: no-repeat; background-size: cover;"';
} else {
    $ignite_agency_portfolio_footer_background_style = ' style="background-color: ' . esc_attr($ignite_agency_portfolio_footer_background_color) . ';"'; // Updated line
}
?>

</div>
</div>
</div>
</div>

<footer class="site-footer"<?php echo $ignite_agency_portfolio_footer_background_style  ?>>
    <?php if( is_active_sidebar( 'footer-one' ) || is_active_sidebar( 'footer-two' ) || is_active_sidebar( 'footer-three' ) || is_active_sidebar( 'footer-four' ) ){ ?>
        <div class="footer-t">
            <div class="container">
                <div class="row">
                    <?php 
                    if( is_active_sidebar( 'footer-one') ) {
                        echo '<div class="col-xl-3 col-lg-3 col-md-4 col-sm-6">';
                        dynamic_sidebar( 'footer-one' ); 
                        echo '</div>';
                    }
                    
                    if( is_active_sidebar( 'footer-two') ) {
                        echo '<div class="col-xl-3 col-lg-3 col-md-4 col-sm-6">';
                        dynamic_sidebar( 'footer-two' );
                        echo '</div>';
                    }
                    
                    if( is_active_sidebar( 'footer-three') ) {
                        echo '<div class="col-xl-3 col-lg-3 col-md-4 col-sm-6">';
                        dynamic_sidebar( 'footer-three' );
                        echo '</div>';
                    }
                    
                    if( is_active_sidebar( 'footer-four' ) ) {
                        echo '<div class="col-xl-3 col-lg-3 col-md-4 col-sm-6">';
                        dynamic_sidebar( 'footer-four' );
                        echo '</div>';
                    }
                    ?>
                </div>
            </div>
        </div>
    <?php } else { ?>
        <div class="footer-t">
            <div class="container">
                <div class="row">
                    <!-- Archive -->
                    <aside id="archive" class="widget widget_archive col-xl-3 col-lg-3 col-md-4 col-sm-6" role="complementary" aria-label="<?php esc_attr_e('secondsidebar', 'ignite-agency-portfolio'); ?>">
                        <h2 class="widget-title"><?php esc_html_e('Archive List', 'ignite-agency-portfolio'); ?></h2>
                        <ul>
                            <?php wp_get_archives('type=monthly'); ?>
                        </ul>
                    </aside>
                    <!-- Recent Posts -->
                    <aside id="recent-posts" class="widget widget_recent_posts col-xl-3 col-lg-3 col-md-4 col-sm-6" role="complementary" aria-label="<?php esc_attr_e('thirdsidebar', 'ignite-agency-portfolio'); ?>">
                        <h2 class="widget-title"><?php esc_html_e('Recent Posts', 'ignite-agency-portfolio'); ?></h2>
                        <ul>
                            <?php
                            $args = array(
                                'post_type'      => 'post',
                                'posts_per_page' => 5,
                            );
                            $ignite_agency_portfolio_recent_posts = new WP_Query($args);

                            while ($ignite_agency_portfolio_recent_posts->have_posts()) : $ignite_agency_portfolio_recent_posts->the_post();
                            ?>
                                <li><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></li>
                            <?php endwhile; ?>
                            <?php wp_reset_postdata(); ?>
                        </ul>
                    </aside>
                    <!-- Categories -->
                    <aside id="categories" class="widget widget_categories col-xl-3 col-lg-3 col-md-4 col-sm-6" role="complementary"  aria-label="<?php esc_attr_e('fourthsidebar', 'ignite-agency-portfolio'); ?>">
                        <h2 class="widget-title"><?php esc_html_e('Categories', 'ignite-agency-portfolio'); ?></h2>
                        <ul>
                            <?php
                            $args = array(
                                'title_li' => '',
                            );
                            wp_list_categories($args);
                            ?>
                        </ul>
                    </aside>
                    <!-- Tags Widget -->
                    <aside id="tags" class="widget widget_tags col-xl-3 col-lg-3 col-md-4 col-sm-6" role="complementary" aria-label="<?php esc_attr_e('fifthsidebar', 'ignite-agency-portfolio'); ?>">
                        <h2 class="widget-title"><?php esc_html_e('Tags', 'ignite-agency-portfolio'); ?></h2>
                        <div class="tag-cloud">
                            <?php wp_tag_cloud(); ?>
                        </div>
                    </aside>
                </div>
            </div>
        </div>
    <?php } 
    do_action( 'ignite_agency_portfolio_footer' );
    ?>
    <?php  
    if ( $ignite_agency_portfolio_scroll_top ){ ?>
        <a id="button"><i class="<?php echo esc_attr(get_theme_mod('ignite_agency_portfolio_scroll_icon','fas fa-arrow-up')); ?>"></i></a>
    <?php } ?>
</footer>
</div>
</div>

<?php wp_footer(); ?>

</body>
</html>