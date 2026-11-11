<?php
/**
 * Right Buttons Panel.
 *
 * @package ignite_agency_portfolio
 */
?>
<div class="panel-right">
	<div class="panel-aside">
		<h4 class="heading"><?php esc_html_e( 'Upgrade To Pro Version', 'ignite-agency-portfolio' ); ?></h4>
		<p class="panel-info">
			<?php
			echo wp_kses_post(
				__( 'Get Extra <span class="off">20% OFF</span>, Use Code <span class="code">“NY2026”</span> at the time of Checkout', 'ignite-agency-portfolio' )
			);
			?>
		</p>
		<div class="pro-img">
			<img src="<?php echo esc_url(get_stylesheet_directory_uri()) .'/screenshot.png'; ?>" alt="<?php echo esc_attr( 'screenshot', 'ignite-agency-portfolio'); ?>"/>
		</div>
		<a class="button button-primary first-color" href="<?php echo esc_url( IGNITE_AGENCY_PORTFOLIO_URL ); ?>" title="<?php esc_attr_e( 'View Premium Version', 'ignite-agency-portfolio' ); ?>" target="_blank">
            <?php esc_html_e( 'Upgrade To Pro With Just $40', 'ignite-agency-portfolio' ); ?>
        </a>
	</div>
	<div>
		<h4><?php esc_html_e( 'ABOUT THEME', 'ignite-agency-portfolio' ); ?></h4>
		<p><?php esc_html_e( 'The Pro version of our theme offers a seamless website customization experience with its intuitive interface. With just a few clicks, you can effortlessly transform the look and feel of your website. Enjoy the freedom to personalize various elements such as colors, background images, patterns, and fonts, elevating your website s aesthetics and brand identity. In addition, the Pro theme goes beyond the basic features of the free version by providing an expanded selection of homepage sections. This enables you to effectively showcase your agency, portfolio and service offerings, empowering the growth of your business. Moreover, the Pro version includes a wide range of professionally designed page templates, giving you a head start in creating impactful web pages with ease. To ensure a top-notch experience, the Pro version receives regular updates, ensuring compatibility with the latest web technologies and maintaining optimal performance. Additionally, our dedicated support team is readily available to address any questions or concerns you may have, providing timely assistance when you need it most.', 'ignite-agency-portfolio' ); ?></p>
	</div>
</div>
