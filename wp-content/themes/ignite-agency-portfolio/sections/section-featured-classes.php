<?php
/**
 * Choose Section
 * 
 * @package ignite_agency_portfolio
 */
$ignite_agency_portfolio_classes = get_theme_mod('ignite_agency_portfolio_about_setting', false);
$ignite_agency_portfolio_about_btn_url  = get_theme_mod( 'ignite_agency_portfolio_about_btn_url' );
$ignite_agency_portfolio_about_btn_text = get_theme_mod( 'ignite_agency_portfolio_about_btn_text' );
?>
<?php if ( $ignite_agency_portfolio_classes ) { ?>
    <div class="about-section">
        <div class="container">
            <div class="row">
                <div class="col-lg-6 col-md-12 align-self-center about-info wow fadeInDown" data-wow-duration="1.5s">
                    <?php 
                        $ignite_agency_portfolio_about_text_extra = get_theme_mod('ignite_agency_portfolio_about_text_extra');
                        if ( $ignite_agency_portfolio_about_text_extra ) : ?>
                        <h4>
                            <?php echo esc_html($ignite_agency_portfolio_about_text_extra); ?>
                        </h4>
                    <?php endif; ?>
                    <?php 
                        $ignite_agency_portfolio_about_title = get_theme_mod('ignite_agency_portfolio_about_title');
                        if ( $ignite_agency_portfolio_about_title ) : ?>
                        <h2>
                            <?php echo esc_html($ignite_agency_portfolio_about_title); ?>
                        </h2>
                    <?php endif; ?>
                    <?php if ( get_theme_mod('ignite_agency_portfolio_about_content') ) : ?>
                        <p class="slide-extra-content"><?php echo esc_html(get_theme_mod('ignite_agency_portfolio_about_content')); ?></p>
                    <?php endif; ?>
                    <div class="icon-section">
                        <?php for ( $ignite_agency_portfolio_i = 1; $ignite_agency_portfolio_i <= 6; $ignite_agency_portfolio_i++ ) : ?>
                            <?php if ( get_theme_mod( 'ignite_agency_portfolio_icon_text_' . $ignite_agency_portfolio_i ) ) : ?>
                                <div class="icon-sec">
                                    <span class="about-icon">
                                        <i class="<?php echo esc_attr( get_theme_mod( 'ignite_agency_portfolio_about_icon', 'fas fa-square-check' ) ); ?>"></i>
                                    </span>

                                    <p class="icon-content">
                                        <?php echo esc_html( get_theme_mod( 'ignite_agency_portfolio_icon_text_' . $ignite_agency_portfolio_i ) ); ?>
                                    </p>
                                </div>
                            <?php endif; ?>
                        <?php endfor; ?>
                    </div>
                    <div class="about-buttons">
                        <div class="slide-btn-green">
                            <?php if ($ignite_agency_portfolio_about_btn_text ) : ?>
                            <a href="<?php echo esc_url( $ignite_agency_portfolio_about_btn_url ); ?>">  
                                <i class="fas fa-user"></i>                 
                                <?php echo esc_html( $ignite_agency_portfolio_about_btn_text ); ?>
                            </a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                <div class="col-lg-6 col-md-12 about-us position-relative">
                    <div class="about-img wow zoomIn delay-1000" data-wow-duration="2s">
                        <?php if ( get_theme_mod('ignite_agency_portfolio_about_image') ) : ?>
                            <div class="about-banner-img about-image">
                                <img class="banner-img" src="<?php echo esc_url(get_theme_mod('ignite_agency_portfolio_about_image'));?>">
                            </div>
                        <?php else: ?>
                        <div class="about-image">
                            <img src="<?php echo get_stylesheet_directory_uri() . '/images/default1.png'; ?>">
                        </div>
                        <?php endif; ?>
                    </div>
                    <?php if ( get_theme_mod('ignite_agency_portfolio_about_expereince_count') != '' ) { ?>
                        <div class="experience-content">
                                <h4 class="mb-0 ex-count"><?php echo esc_html(get_theme_mod('ignite_agency_portfolio_about_expereince_count', '')); ?></h4>
                                <p class="mb-0 mt-0 count-text"><?php echo esc_html(get_theme_mod('ignite_agency_portfolio_about_expereince_text', '')); ?></p>
                        </div>
                    <?php } ?>
                </div>
            </div>
        </div>
    </div>
<?php } ?>

