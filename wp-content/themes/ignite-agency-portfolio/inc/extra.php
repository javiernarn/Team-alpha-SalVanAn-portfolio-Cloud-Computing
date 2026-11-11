<?php
/**
 * Custom functions that act independently of the theme templates.
 *
 * Eventually, some of the functionality here could be replaced by core features.
 *
 * @package ignite_agency_portfolio
 */

/**
 * Adds custom classes to the array of body classes.
 *
 * @param array $classes Classes for the body element.
 * @return array
 */
function ignite_agency_portfolio_body_classes( $classes ) {
  global $ignite_agency_portfolio_post;
  
    if( !is_page_template( 'template-home.php' ) ){
        $classes[] = 'inner';
        // Adds a class of group-blog to blogs with more than 1 published author.
    }

    if ( is_multi_author() ) {
        $classes[] = 'group-blog ';
    }

    // Adds a class of custom-background-image to sites with a custom background image.
    if ( get_background_image() ) {
        $classes[] = 'custom-background-image';
    }
    
    // Adds a class of custom-background-color to sites with a custom background color.
    if ( get_background_color() != 'ffffff' ) {
        $classes[] = 'custom-background-color';
    }
    

    if( ignite_agency_portfolio_woocommerce_activated() && ( is_shop() || is_product_category() || is_product_tag() || 'product' === get_post_type() ) && ! is_active_sidebar( 'shop-sidebar' ) ){
        $classes[] = 'full-width';
    }    

    // Adds a class of hfeed to non-singular pages.
    if ( ! is_page() ) {
        $classes[] = 'hfeed ';
    }
  
    if( is_404() ||  is_search() ){
        $classes[] = 'full-width';
    }
  
    if( ! is_active_sidebar( 'right-sidebar' ) ) {
        $classes[] = 'full-width'; 
    }

    return $classes;
}
add_filter( 'body_class', 'ignite_agency_portfolio_body_classes' );

 /**
 * 
 * @link http://www.altafweb.com/2011/12/remove-specific-tag-from-php-string.html
 */
function ignite_agency_portfolio_strip_single( $tag, $string ){
    $string=preg_replace('/<'.$tag.'[^>]*>/i', '', $string);
    $string=preg_replace('/<\/'.$tag.'>/i', '', $string);
    return $string;
}

if ( ! function_exists( 'ignite_agency_portfolio_excerpt_more' ) ) :
/**
 * Replaces "[...]" (appended to automatically generated excerpts) with ... * 
 */
function ignite_agency_portfolio_excerpt_more($more) {
  return is_admin() ? $more : ' &hellip; ';
}
endif;
add_filter( 'excerpt_more', 'ignite_agency_portfolio_excerpt_more' );


if( ! function_exists( 'ignite_agency_portfolio_footer_credit' ) ):
/**
 * Footer Credits
*/
function ignite_agency_portfolio_footer_credit() {

    // Check if footer copyright is enabled
    $ignite_agency_portfolio_show_footer_copyright = get_theme_mod( 'ignite_agency_portfolio_footer_setting', true );

    if ( ! $ignite_agency_portfolio_show_footer_copyright ) {
        return; 
    }

    $ignite_agency_portfolio_copyright_text = get_theme_mod('ignite_agency_portfolio_footer_copyright_text');

    $ignite_agency_portfolio_text = '<div class="site-info"><div class="container"><span class="copyright">';
    if ($ignite_agency_portfolio_copyright_text) {
        $ignite_agency_portfolio_text .= wp_kses_post($ignite_agency_portfolio_copyright_text); 
    } else {
        $ignite_agency_portfolio_text .= esc_html__('&copy; ', 'ignite-agency-portfolio') . date_i18n(esc_html__('Y', 'ignite-agency-portfolio')); 
        $ignite_agency_portfolio_text .= ' <a href="' . esc_url(home_url('/')) . '">' . esc_html(get_bloginfo('name')) . '</a>' . esc_html__('. All Rights Reserved.', 'ignite-agency-portfolio');
    }
    $ignite_agency_portfolio_text .= '</span>';
    $ignite_agency_portfolio_text .= '<span class="by"> <a href="' . esc_url('https://www.themeignite.com/products/ignite-agency-portfolio') . '" rel="nofollow" target="_blank">' . IGNITE_AGENCY_PORTFOLIO_THEME_NAME . '</a>' . esc_html__(' By ', 'ignite-agency-portfolio') . '<a href="' . esc_url('https://themeignite.com/') . '" rel="nofollow" target="_blank">' . esc_html__('Themeignite', 'ignite-agency-portfolio') . '</a>.';
    /* translators: %s: link to WordPress.org */
    $ignite_agency_portfolio_text .= sprintf(esc_html__(' Powered By %s', 'ignite-agency-portfolio'), '<a href="' . esc_url(__('https://WordPress.org/', 'ignite-agency-portfolio')) . '" target="_blank">WordPress</a>.');
    if (function_exists('the_privacy_policy_link')) {
        $ignite_agency_portfolio_text .= get_the_privacy_policy_link();
    }
    $ignite_agency_portfolio_text .= '</span></div></div>';
    echo apply_filters('ignite_agency_portfolio_footer_text', $ignite_agency_portfolio_text);
}
add_action('ignite_agency_portfolio_footer', 'ignite_agency_portfolio_footer_credit');
endif;
/**
 * Is Woocommerce activated
*/
if ( ! function_exists( 'ignite_agency_portfolio_woocommerce_activated' ) ) {
  function ignite_agency_portfolio_woocommerce_activated() {
    if ( class_exists( 'woocommerce' ) ) { return true; } else { return false; }
  }
}

if( ! function_exists( 'ignite_agency_portfolio_change_comment_form_default_fields' ) ) :
/**
 * Change Comment form default fields i.e. author, email & url.
 * https://blog.josemcastaneda.com/2016/08/08/copy-paste-hurting-theme/
*/
function ignite_agency_portfolio_change_comment_form_default_fields( $fields ){    
    // get the current commenter if available
    $ignite_agency_portfolio_commenter = wp_get_current_commenter();
 
    // core functionality
    $req      = get_option( 'require_name_email' );
    $ignite_agency_portfolio_aria_req = ( $req ? " aria-required='true'" : '' );
    $ignite_agency_portfolio_required = ( $req ? " required" : '' );
    $ignite_agency_portfolio_author   = ( $req ? __( 'Name*', 'ignite-agency-portfolio' ) : __( 'Name', 'ignite-agency-portfolio' ) );
    $ignite_agency_portfolio_email    = ( $req ? __( 'Email*', 'ignite-agency-portfolio' ) : __( 'Email', 'ignite-agency-portfolio' ) );
 
    // Change just the author field
    $fields['author'] = '<p class="comment-form-author"><label class="screen-reader-text" for="author">' . esc_html__( 'Name', 'ignite-agency-portfolio' ) . '<span class="required">*</span></label><input id="author" name="author" placeholder="' . esc_attr( $ignite_agency_portfolio_author ) . '" type="text" value="' . esc_attr( $ignite_agency_portfolio_commenter['comment_author'] ) . '" size="30"' . $ignite_agency_portfolio_aria_req . $ignite_agency_portfolio_required . ' /></p>';
    
    $fields['email'] = '<p class="comment-form-email"><label class="screen-reader-text" for="email">' . esc_html__( 'Email', 'ignite-agency-portfolio' ) . '<span class="required">*</span></label><input id="email" name="email" placeholder="' . esc_attr( $ignite_agency_portfolio_email ) . '" type="text" value="' . esc_attr(  $ignite_agency_portfolio_commenter['comment_author_email'] ) . '" size="30"' . $ignite_agency_portfolio_aria_req . $ignite_agency_portfolio_required. ' /></p>';
    
    $fields['url'] = '<p class="comment-form-url"><label class="screen-reader-text" for="url">' . esc_html__( 'Website', 'ignite-agency-portfolio' ) . '</label><input id="url" name="url" placeholder="' . esc_attr__( 'Website', 'ignite-agency-portfolio' ) . '" type="text" value="' . esc_attr( $ignite_agency_portfolio_commenter['comment_author_url'] ) . '" size="30" /></p>'; 
    
    return $fields;    
}
endif;
add_filter( 'comment_form_default_fields', 'ignite_agency_portfolio_change_comment_form_default_fields' );

if( ! function_exists( 'ignite_agency_portfolio_change_comment_form_defaults' ) ) :
/**
 * Change Comment Form defaults
 * https://blog.josemcastaneda.com/2016/08/08/copy-paste-hurting-theme/
*/
function ignite_agency_portfolio_change_comment_form_defaults( $defaults ){    
    $defaults['comment_field'] = '<p class="comment-form-comment"><label class="screen-reader-text" for="comment">' . esc_html__( 'Comment', 'ignite-agency-portfolio' ) . '</label><textarea id="comment" name="comment" placeholder="' . esc_attr__( 'Comment', 'ignite-agency-portfolio' ) . '" cols="45" rows="8" aria-required="true" required></textarea></p>';
    
    return $defaults;    
}
endif;
add_filter( 'comment_form_defaults', 'ignite_agency_portfolio_change_comment_form_defaults' );

if( ! function_exists( 'ignite_agency_portfolio_escape_text_tags' ) ) :
/**
 * Remove new line tags from string
 *
 * @param $text
 * @return string
 */
function ignite_agency_portfolio_escape_text_tags( $text ) {
    return (string) str_replace( array( "\r", "\n" ), '', strip_tags( $text ) );
}
endif;

if( ! function_exists( 'wp_body_open' ) ) :
/**
 * Fire the wp_body_open action.
 * Added for backwards compatibility to support pre 5.2.0 WordPress versions.
*/
function wp_body_open() {
    /**
     * Triggered after the opening <body> tag.
    */
    do_action( 'wp_body_open' );
}
endif;

if ( ! function_exists( 'ignite_agency_portfolio_get_fallback_svg' ) ) :    
/**
 * Get Fallback SVG
*/
function ignite_agency_portfolio_get_fallback_svg( $ignite_agency_portfolio_post_thumbnail ) {
    if( ! $ignite_agency_portfolio_post_thumbnail ){
        return;
    }
    
    $ignite_agency_portfolio_image_size = ignite_agency_portfolio_get_image_sizes( $ignite_agency_portfolio_post_thumbnail );
     
    if( $ignite_agency_portfolio_image_size ){ ?>
        <div class="svg-holder">
             <svg class="fallback-svg" viewBox="0 0 <?php echo esc_attr( $ignite_agency_portfolio_image_size['width'] ); ?> <?php echo esc_attr( $ignite_agency_portfolio_image_size['height'] ); ?>" preserveAspectRatio="none">
                    <rect width="<?php echo esc_attr( $ignite_agency_portfolio_image_size['width'] ); ?>" height="<?php echo esc_attr( $ignite_agency_portfolio_image_size['height'] ); ?>" style="fill:#dedddd;"></rect>
            </svg>
        </div>
        <?php
    }
}
endif;

function ignite_agency_portfolio_enqueue_google_fonts() {

    require get_template_directory() . '/inc/wptt-webfont-loader.php';

    wp_enqueue_style(
        'google-fonts-unbounded',
        ignite_agency_portfolio_wptt_get_webfont_url( 'https://fonts.googleapis.com/css2?family=Unbounded:wght@200..900&display=swap' ),
        array(),
        '1.0'
    );

    wp_enqueue_style(
        'google-fonts-inter',
        ignite_agency_portfolio_wptt_get_webfont_url( 'https://fonts.googleapis.com/css2?family=Inter:ital,opsz,wght@0,14..32,100..900;1,14..32,100..900&display=swap' ),
        array(),
        '1.0'
    );
}
add_action( 'wp_enqueue_scripts', 'ignite_agency_portfolio_enqueue_google_fonts' );


if( ! function_exists( 'ignite_agency_portfolio_site_branding' ) ) :
/**
 * Site Branding
*/
function ignite_agency_portfolio_site_branding(){
    $ignite_agency_portfolio_logo_site_title = get_theme_mod( 'header_site_title', 1 );
    $ignite_agency_portfolio_tagline = get_theme_mod( 'header_tagline', false );
    $ignite_agency_portfolio_logo_width = get_theme_mod('logo_width', 100); // Retrieve the logo width setting

    ?>
    <div class="site-branding" style="max-width: <?php echo esc_attr(get_theme_mod('logo_width', '-1'))?>px;">
        <?php 
        // Check if custom logo is set and display it
        if (function_exists('has_custom_logo') && has_custom_logo()) {
            the_custom_logo();
        }
        if ($ignite_agency_portfolio_logo_site_title):
             if (is_front_page()): ?>
            <h1 class="site-title" style="font-size: <?php echo esc_attr(get_theme_mod('ignite_agency_portfolio_site_title_size', '20')); ?>px;">
            <a href="<?php echo esc_url(home_url('/')); ?>" rel="home"><?php bloginfo('name'); ?></a>
          </h1>
            <?php else: ?>
                <p class="site-title" itemprop="name">
                    <a href="<?php echo esc_url(home_url('/')); ?>" rel="home"><?php bloginfo('name'); ?></a>
                </p>
            <?php endif; ?>
        <?php endif; 
    
        if ($ignite_agency_portfolio_tagline) :
            $ignite_agency_portfolio_description = get_bloginfo('description', 'display');
            if ($ignite_agency_portfolio_description || is_customize_preview()) :
        ?>
                <p class="site-description" itemprop="description"><?php echo $ignite_agency_portfolio_description; ?></p>
            <?php endif;
        endif;
        ?>
    </div>
    <?php
}
endif;
if( ! function_exists( 'ignite_agency_portfolio_navigation' ) ) :
    /**
     * Site Navigation
    */
    function ignite_agency_portfolio_navigation(){
        ?>
        <nav class="main-navigation" id="site-navigation" role="navigation">
            <?php 
            wp_nav_menu( array( 
                'theme_location' => 'primary', 
                'menu_id' => 'primary-menu' 
            ) ); 
            ?>
        </nav>
        <?php
    }
endif;

if( ! function_exists( 'ignite_agency_portfolio_header' ) ) :
/**
 * Header Start
*/
function ignite_agency_portfolio_header(){
    $ignite_agency_portfolio_header_image = get_header_image();
    $ignite_agency_portfolio_sticky_header = get_theme_mod('ignite_agency_portfolio_sticky_header');
    $ignite_agency_portfolio_header_btn_url  = get_theme_mod( 'ignite_agency_portfolio_header_btn_url' );
    $ignite_agency_portfolio_header_btn_text = get_theme_mod( 'ignite_agency_portfolio_header_btn_text' );
    ?>
<div id="page-site-header">
    <header id="masthead" class="site-header header-inner" role="banner">
        <div class="theme-menu head_bg" data-sticky="<?php echo esc_attr( $ignite_agency_portfolio_sticky_header ?: '' ); ?>">
            <div class="topbar-header-2" <?php if ( ! empty( $ignite_agency_portfolio_header_image ) ) : ?>
                style="background-image:url('<?php echo esc_url( $ignite_agency_portfolio_header_image ); ?>');background-repeat:no-repeat;background-size:cover;"
            <?php endif; ?> data-sticky="<?php echo $ignite_agency_portfolio_sticky_header; ?>">
                <div class="container">
                    <div class="row">
                        <div class="col-lg-3 col-md-12 col-12 align-self-center acc-order">
                            <?php ignite_agency_portfolio_site_branding(); ?>
                        </div>
                        <div class="col-lg-6 col-md-12 col-12 align-self-center py-2 mail-box">
                            <div class="middle-header">
                                <?php ignite_agency_portfolio_navigation(); ?>
                            </div>
                        </div> 
                        <div class="col-lg-3 col-md-12 col-12 align-self-center text-lg-end">
                            <div class="header-buttons align-self-center d-flex flex-wrap">
                                <div class="slide-btn-green">
                                    <?php if ($ignite_agency_portfolio_header_btn_text ) : ?>
                                    <a href="<?php echo esc_url( $ignite_agency_portfolio_header_btn_url ); ?>">   
                                        <i class="fas fa-download"></i>                
                                        <?php echo esc_html( $ignite_agency_portfolio_header_btn_text ); ?>
                                    </a>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div> 
            </div>
        </div>
    </header>
</div>
    <?php
}
endif;
add_action( 'ignite_agency_portfolio_header', 'ignite_agency_portfolio_header', 20 );
