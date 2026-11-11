<?php require get_template_directory() . '/theme-wizard/tgm/class-tgm-plugin-activation.php';

function ignite_agency_portfolio_register_recommended_plugins() {
	$plugins = array(
		array(
			'name'             => __( 'Contact Form 7', 'ignite-agency-portfolio' ),
			'slug'             => 'contact-form-7',
			'source'           => '',
			'required'         => false,
			'force_activation' => false,
		),
		array(
			'name'             => __( 'Classic Widgets', 'ignite-agency-portfolio' ),
			'slug'             => 'classic-widgets',
			'source'           => '',
			'required'         => false,
			'force_activation' => false,
		),
		array(
			'name'             => __( 'BtnBuddy - Dynamic Floating Button', 'ignite-agency-portfolio' ),
			'slug'             => 'btnbuddy-dynamic-floating-button',
			'source'           => '',
			'required'         => false,
			'force_activation' => false,
		),
	);
	$config = array();
	tgmpa( $plugins, $config );
}
add_action( 'tgmpa_register', 'ignite_agency_portfolio_register_recommended_plugins' );
