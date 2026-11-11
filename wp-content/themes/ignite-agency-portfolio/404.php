<?php
/**
 * The template for displaying 404 pages (not found).
 *
 * @link https://codex.WordPress.org/Creating_an_Error_404_Page
 *
 * @package ignite_agency_portfolio
 */

    ?>
    <?php get_header(); ?>
	   <main id="main" class="site-main" role="main">
		    <div class="error-holder">
		        <?php 
		        // Check if custom 404 image is set
		        $ignite_agency_portfolio_image_url = get_theme_mod('404_page_image', '');
		        if ($ignite_agency_portfolio_image_url) : ?>
		            <img src="<?php echo esc_url($ignite_agency_portfolio_image_url); ?>" alt="<?php esc_attr_e('404 Image', 'ignite-agency-portfolio'); ?>">
		        <?php endif; ?>
		        	<h1><?php echo esc_html(get_theme_mod('404_pagefirst_header', __('404', 'ignite-agency-portfolio'))); ?></h1>
					<h2><?php echo esc_html(get_theme_mod('404_page_header', __('Sorry, that page can\'t be found!', 'ignite-agency-portfolio'))); ?></h2>

		        <p class="btn-green mt-3 mb-0">
		            <a href="<?php echo esc_url(home_url('/')); ?>"><?php esc_html_e('Homepage', 'ignite-agency-portfolio'); ?></a>
		        </p>
		    </div>
		</main>

    <?php get_footer(); ?>