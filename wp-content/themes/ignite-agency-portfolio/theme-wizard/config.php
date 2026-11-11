<?php
/**
 * Settings for theme wizard
 *
 * @package Whizzie
 * @author Catapult Themes
 * @since 1.0.0
 */

/**
 * Define constants
 **/
if ( ! defined( 'WHIZZIE_DIR' ) ) {
	define( 'WHIZZIE_DIR', dirname( __FILE__ ) );
}
// Load the Whizzie class and other dependencies
require trailingslashit( WHIZZIE_DIR ) . 'whizzie.php';
// Gets the theme object
$current_theme = wp_get_theme();
$theme_title = $current_theme->get( 'Name' );


/**
 * Make changes below
 **/

// Change the title and slug of your wizard page
$config['page_slug'] 	= 'ignite-agency-portfolio';
$config['page_title']	= 'TI Setup Wizard';

// You can remove elements here as required
// Don't rename the IDs - nothing will break but your changes won't get carried through
$config['steps'] = array(
	'intro' => array(
		'id'			=> 'intro',
		'title'			=> __( 'Welcome to ', 'ignite-agency-portfolio' ) . $theme_title, // Section title
		'icon'			=> 'dashboard',
		'can_skip'		=> false
	),
	'plugins' => array(
		'id'			=> 'plugins',
		'title'			=> __( 'Plugins', 'ignite-agency-portfolio' ),
		'icon'			=> 'admin-plugins',
		'button_text'	=> __( 'Install Plugins', 'ignite-agency-portfolio' ),
		'can_skip'		=> false
	),
	'widgets' => array(
		'id'			=> 'widgets',
		'title'			=> __( 'Demo Importer', 'ignite-agency-portfolio' ),
		'icon'			=> 'welcome-widgets-menus',
		'button_text'	=> __( 'Import Demo', 'ignite-agency-portfolio' ),
		'can_skip'		=> false
	),
	'done' => array(
		'id'			=> 'done',
		'title'			=> __( 'All Done', 'ignite-agency-portfolio' ),
		'icon'			=> 'yes',
	)
);

/**
 * This kicks off the wizard
 **/
if( class_exists( 'Whizzie' ) ) {
	$Whizzie = new Whizzie( $config );
}
