<?php
$ignite_agency_portfolio_custom_css = "";
$ignite_agency_portfolio_primary_color = get_theme_mod('ignite_agency_portfolio_primary_color');
$ignite_agency_portfolio_secondary_color = get_theme_mod('ignite_agency_portfolio_secondary_color');
$ignite_agency_portfolio_tertiary_color = get_theme_mod('ignite_agency_portfolio_tertiary_color');

/*------------------ Primary Global Color -----------*/

if ($ignite_agency_portfolio_primary_color) {
  $ignite_agency_portfolio_custom_css .= ':root {';
  $ignite_agency_portfolio_custom_css .= '--primary-color: ' . esc_attr($ignite_agency_portfolio_primary_color) . ' !important;';
  $ignite_agency_portfolio_custom_css .= '} ';
}

/*------------------ Secondary Global Color -----------*/

if ($ignite_agency_portfolio_secondary_color) {
  $ignite_agency_portfolio_custom_css .= ':root {';
  $ignite_agency_portfolio_custom_css .= '--secondary-color: ' . esc_attr($ignite_agency_portfolio_secondary_color) . ' !important;';
  $ignite_agency_portfolio_custom_css .= '} ';
}

/*------------------ Tertiary Global Color -----------*/

if ($ignite_agency_portfolio_tertiary_color) {
  $ignite_agency_portfolio_custom_css .= ':root {';
  $ignite_agency_portfolio_custom_css .= '--tertiary-color: ' . esc_attr($ignite_agency_portfolio_tertiary_color) . ' !important;';
  $ignite_agency_portfolio_custom_css .= '} ';
}

/*-------------------- Scroll Top Alignment-------------------*/

$ignite_agency_portfolio_scroll_top_alignment = get_theme_mod( 'ignite_agency_portfolio_scroll_top_alignment','right-align');

if($ignite_agency_portfolio_scroll_top_alignment == 'right-align'){
$ignite_agency_portfolio_custom_css .='#button{';
	$ignite_agency_portfolio_custom_css .='right: 3%;';
$ignite_agency_portfolio_custom_css .='}';
}else if($ignite_agency_portfolio_scroll_top_alignment == 'center-align'){
$ignite_agency_portfolio_custom_css .='#button{';
	$ignite_agency_portfolio_custom_css .='right:0; left:0; margin: 0 auto;';
$ignite_agency_portfolio_custom_css .='}';
}else if($ignite_agency_portfolio_scroll_top_alignment == 'left-align'){
$ignite_agency_portfolio_custom_css .='#button{';
	$ignite_agency_portfolio_custom_css .='left: 3%;';
$ignite_agency_portfolio_custom_css .='}';
}

/*-------------------- Archive Page Pagination Alignment-------------------*/

$ignite_agency_portfolio_archive_pagination_alignment = get_theme_mod( 'ignite_agency_portfolio_archive_pagination_alignment','left-align');

if($ignite_agency_portfolio_archive_pagination_alignment == 'right-align'){
$ignite_agency_portfolio_custom_css .='.pagination{';
	$ignite_agency_portfolio_custom_css .='justify-content: end;';
$ignite_agency_portfolio_custom_css .='}';
}else if($ignite_agency_portfolio_archive_pagination_alignment == 'center-align'){
$ignite_agency_portfolio_custom_css .='.pagination{';
	$ignite_agency_portfolio_custom_css .='justify-content: center;';
$ignite_agency_portfolio_custom_css .='}';
}else if($ignite_agency_portfolio_archive_pagination_alignment == 'left-align'){
$ignite_agency_portfolio_custom_css .='.pagination{';
	$ignite_agency_portfolio_custom_css .='justify-content: start;';
$ignite_agency_portfolio_custom_css .='}';
}

/*-------------------- Scroll Top Responsive-------------------*/

$ignite_agency_portfolio_resp_scroll_top = get_theme_mod( 'ignite_agency_portfolio_resp_scroll_top',true);
if($ignite_agency_portfolio_resp_scroll_top == true && get_theme_mod( 'ignite_agency_portfolio_scroll_to_top',true) != true){
	$ignite_agency_portfolio_custom_css .='#button.show{';
		$ignite_agency_portfolio_custom_css .='visibility:hidden !important;';
	$ignite_agency_portfolio_custom_css .='} ';
}
if($ignite_agency_portfolio_resp_scroll_top == true){
	$ignite_agency_portfolio_custom_css .='@media screen and (max-width:575px) {';
	$ignite_agency_portfolio_custom_css .='#button.show{';
		$ignite_agency_portfolio_custom_css .='visibility:visible !important;';
	$ignite_agency_portfolio_custom_css .='} }';
}else if($ignite_agency_portfolio_resp_scroll_top == false){
	$ignite_agency_portfolio_custom_css .='@media screen and (max-width:575px){';
	$ignite_agency_portfolio_custom_css .='#button.show{';
		$ignite_agency_portfolio_custom_css .='visibility:hidden !important;';
	$ignite_agency_portfolio_custom_css .='} }';
}

/*-------------------- Preloader Responsive-------------------*/

$ignite_agency_portfolio_resp_loader = get_theme_mod('ignite_agency_portfolio_resp_loader',false);
if($ignite_agency_portfolio_resp_loader == true && get_theme_mod('ignite_agency_portfolio_header_preloader',false) == false){
	$ignite_agency_portfolio_custom_css .='@media screen and (min-width:575px){
		.preloader{';
		$ignite_agency_portfolio_custom_css .='display:none !important;';
	$ignite_agency_portfolio_custom_css .='} }';
}

if($ignite_agency_portfolio_resp_loader == false){
	$ignite_agency_portfolio_custom_css .='@media screen and (max-width:575px){
		.preloader{';
		$ignite_agency_portfolio_custom_css .='display:none !important;';
	$ignite_agency_portfolio_custom_css .='} }';
}

// Scroll to top button shape 

$ignite_agency_portfolio_scroll_border_radius = get_theme_mod( 'ignite_agency_portfolio_scroll_to_top_radius','curved-box');
if($ignite_agency_portfolio_scroll_border_radius == 'box'){
	$ignite_agency_portfolio_custom_css .='#button{';
		$ignite_agency_portfolio_custom_css .='border-radius: 0px;';
	$ignite_agency_portfolio_custom_css .='}';
}else if($ignite_agency_portfolio_scroll_border_radius == 'curved-box'){
	$ignite_agency_portfolio_custom_css .='#button{';
		$ignite_agency_portfolio_custom_css .='border-radius: 4px;';
	$ignite_agency_portfolio_custom_css .='}';
}
else if($ignite_agency_portfolio_scroll_border_radius == 'circle'){
	$ignite_agency_portfolio_custom_css .='#button{';
		$ignite_agency_portfolio_custom_css .='border-radius: 50%;';
	$ignite_agency_portfolio_custom_css .='}';
}

// Footer Background Image Attachment 

$ignite_agency_portfolio_footer_attachment = get_theme_mod( 'ignite_agency_portfolio_background_attachment','scroll');
if($ignite_agency_portfolio_footer_attachment == 'fixed'){
	$ignite_agency_portfolio_custom_css .='.site-footer{';
		$ignite_agency_portfolio_custom_css .='background-attachment: fixed;';
	$ignite_agency_portfolio_custom_css .='}';
}elseif ($ignite_agency_portfolio_footer_attachment == 'scroll'){
	$ignite_agency_portfolio_custom_css .='.site-footer{';
		$ignite_agency_portfolio_custom_css .='background-attachment: scroll;';
	$ignite_agency_portfolio_custom_css .='}';
}

// Menu Hover Style	

$ignite_agency_portfolio_menus_item = get_theme_mod( 'ignite_agency_portfolio_menus_style','None');
if($ignite_agency_portfolio_menus_item == 'None'){
	$ignite_agency_portfolio_custom_css .='#site-navigation .menu ul li a:hover, .main-navigation .menu li a:hover{';
		$ignite_agency_portfolio_custom_css .='';
	$ignite_agency_portfolio_custom_css .='}';
}else if($ignite_agency_portfolio_menus_item == 'Zoom In'){
	$ignite_agency_portfolio_custom_css .='#site-navigation .menu ul li a:hover, .main-navigation .menu li a:hover{';
		$ignite_agency_portfolio_custom_css .='transition: all 0.3s ease-in-out !important; transform: scale(1.2) !important;';
	$ignite_agency_portfolio_custom_css .='}';

	$ignite_agency_portfolio_custom_css .= '.main-navigation ul ul li a:hover {';
	$ignite_agency_portfolio_custom_css .= 'margin-left: 20px;';
	$ignite_agency_portfolio_custom_css .= '}';
}

// Banner Background Image
$ignite_agency_portfolio_banner_bg = get_theme_mod('ignite_agency_portfolio_banner_background_image');
if ( empty( $ignite_agency_portfolio_banner_bg ) ) {
	$ignite_agency_portfolio_banner_bg = get_template_directory_uri() . '/images/banner.png';
}
$ignite_agency_portfolio_custom_css .= "
.banner::before {
	background-image: url('{$ignite_agency_portfolio_banner_bg}');
	background-size: cover;
	background-position: center;
	background-repeat: no-repeat;
    position: absolute;
    width: 100%;
    min-height: 700px;
}";