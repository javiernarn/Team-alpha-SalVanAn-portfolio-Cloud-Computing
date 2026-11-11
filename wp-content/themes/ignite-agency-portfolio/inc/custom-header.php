<?php
/**
 * Sample implementation of the Custom Header feature
 *
 * You can add an optional custom header image to header.php like so ...
 *
 * <?php the_header_image_tag(); 
 *
 * @link https://developer.WordPress.org/themes/functionality/custom-headers/
 *
 * @package Ignite Agency Portfolio
 */

/**
 * Set up the WordPress core custom header feature.
 *
 * @uses ignite_agency_portfolio_header_style()
 */
function ignite_agency_portfolio_custom_header_setup() {
	add_theme_support( 'custom-header', apply_filters( 'ignite_agency_portfolio_custom_header_args', array(
		'default-image'          => '',
		'width'                  => 1200,
		'height'                 => 100,
		'flex-height'            => true,
		'flex-width'             => true,
		'wp-head-callback'       => 'ignite_agency_portfolio_header_style',
	) ) );

	// Register default headers.
	register_default_headers( array(
		'default-banner' => array(
			'url'           => '%s/images/default.png',
			'thumbnail_url' => '%s/images/default.png',
			'description'   => esc_html_x( 'Default Banner', 'header image description', 'ignite-agency-portfolio' ),
		),

	) );
}
add_action( 'after_setup_theme', 'ignite_agency_portfolio_custom_header_setup' );

function ignite_agency_portfolio_header_style() {

	$ignite_agency_portfolio_header_text_color = get_header_textcolor();

	if ( get_theme_support( 'custom-header', 'default-text-color' ) === $ignite_agency_portfolio_header_text_color ) {
		return;
	}

	if ( ! display_header_text() ) :

		echo '<style>
		.site-title,
		.site-description{
			position:absolute;
			clip:rect(1px,1px,1px,1px);
		}
		</style>';

	else :

		echo '<style>
		.site-title a,
		.site-description{
			color:#' . esc_attr( $ignite_agency_portfolio_header_text_color ) . ' !important;
		}
		</style>';

	endif;
}