<?php
/**
 * Banner Section
 * 
 * @package ignite_agency_portfolio
 */
$ignite_agency_portfolio_banner = get_theme_mod( 'ignite_agency_portfolio_banner_setting',false );
$ignite_agency_portfolio_head_info_btn_url  = get_theme_mod( 'ignite_agency_portfolio_head_info_btn_url' );
$ignite_agency_portfolio_head_info_btn_text = get_theme_mod( 'ignite_agency_portfolio_head_info_btn_text' );
?>
<?php if ( $ignite_agency_portfolio_banner ){?>
    <div class="banner wow fadeInDown" data-wow-duration="1.5s">
      <div class="banner_box">
        <div class="container">
          <div class="banner-inner-info">
            <div class="head-info-sec d-flex gap-3 align-items-center">
                <div class="head-info-inner">
                  <div class="head-info-img">
                    <?php if ( get_theme_mod('ignite_agency_portfolio_head_info_image') ) : ?>
                        <img src="<?php echo esc_url(get_theme_mod('ignite_agency_portfolio_head_info_image'));?>">
                    <?php else: ?>
                        <img src="<?php echo get_stylesheet_directory_uri() . '/images/default.png'; ?>">
                    <?php endif; ?>
                  </div>
                  <div class="head-information">
                    <div class="head-titles">
                      <?php 
                          $ignite_agency_portfolio_head_info_title = get_theme_mod('ignite_agency_portfolio_head_info_title');
                          if ( $ignite_agency_portfolio_head_info_title ) : ?>
                          <h4>
                              <?php echo esc_html($ignite_agency_portfolio_head_info_title); ?>
                          </h4>
                      <?php endif; ?>
                      <?php if ( get_theme_mod('ignite_agency_portfolio_head_info_content') ) : ?>
                          <h3><?php echo esc_html(get_theme_mod('ignite_agency_portfolio_head_info_content')); ?></h3>
                      <?php endif; ?>
                    </div>
                    <div class="banner-buttons">
                      <div class="head-info-btn">
                          <?php if ($ignite_agency_portfolio_head_info_btn_text ) : ?>
                          <a href="<?php echo esc_url( $ignite_agency_portfolio_head_info_btn_url ); ?>">      
                            <i class="fas fa-plus"></i>             
                              <?php echo esc_html( $ignite_agency_portfolio_head_info_btn_text ); ?>
                          </a>
                          <?php endif; ?>
                      </div>
                    </div>
                  </div>
                </div>
                <br>
                <div class="idea-sec">
                  <?php 
                      $ignite_agency_portfolio_idea_title = get_theme_mod('ignite_agency_portfolio_idea_title');
                      if ( $ignite_agency_portfolio_idea_title ) : ?>
                      <h5>
                          <?php echo esc_html($ignite_agency_portfolio_idea_title); ?>
                      </h5>
                  <?php endif; ?>
                  <?php if ( get_theme_mod('ignite_agency_portfolio_idea_content') ) : ?>
                      <p><?php echo esc_html(get_theme_mod('ignite_agency_portfolio_idea_content')); ?></p>
                  <?php endif; ?>
                </div>
            </div>
            <div class="d-flex align-items-center align-content-center gap-4">
              <?php 
                  $ignite_agency_portfolio_banner_first_title = get_theme_mod('ignite_agency_portfolio_banner_first_title');
                  if ( $ignite_agency_portfolio_banner_first_title ) : ?>
                  <h1>
                      <?php echo esc_html($ignite_agency_portfolio_banner_first_title); ?>
                  </h1>
              <?php endif; ?>
              <div class="review-box">
                <?php if ( get_theme_mod('ignite_agency_portfolio_right_image_box_3_text') != '' || get_theme_mod('ignite_agency_portfolio_right_image_box_3_count') != '' ) { ?>
                    <div class="image-box mt-4">
                      <div class="review-content">
                              <p class="mb-1 count"><?php echo esc_html(get_theme_mod('ignite_agency_portfolio_right_image_box_3_count', '')); ?></p>
                              <p class="mb-0 mt-0 count-info"><?php echo esc_html(get_theme_mod('ignite_agency_portfolio_right_image_box_3_text', '')); ?></p>
                      </div>
                      <div class="image-team">
                          <?php if ( get_theme_mod( 'ignite_agency_portfolio_slider_team_image_1' ) ) { ?>
                              <img class="team-img-small-1" src="<?php echo esc_url( get_theme_mod( 'ignite_agency_portfolio_slider_team_image_1' ) ); ?>">
                          <?php } ?>
                          <?php if ( get_theme_mod( 'ignite_agency_portfolio_slider_team_image_2' ) ) { ?>
                              <img class="team-img-small-2" src="<?php echo esc_url( get_theme_mod( 'ignite_agency_portfolio_slider_team_image_2' ) ); ?>">
                          <?php } ?>
                          <?php if ( get_theme_mod( 'ignite_agency_portfolio_slider_team_image_3' ) ) { ?>
                              <img class="team-img-small-3" src="<?php echo esc_url( get_theme_mod( 'ignite_agency_portfolio_slider_team_image_3' ) ); ?>">
                          <?php } ?>
                          <?php if ( get_theme_mod( 'ignite_agency_portfolio_slider_team_image_4' ) ) { ?>
                              <img class="team-img-small-4" src="<?php echo esc_url( get_theme_mod( 'ignite_agency_portfolio_slider_team_image_4' ) ); ?>">
                          <?php } ?>
                      </div>
                    </div>
                <?php } ?>
              </div>
            </div>
            <div class="d-flex align-items-center align-content-center gap-5 mt-2 second-div">
              <?php if ( get_theme_mod('ignite_agency_portfolio_banner_content') ) : ?>
                  <p class="slide-extra-content"><?php echo esc_html(get_theme_mod('ignite_agency_portfolio_banner_content')); ?></p>
              <?php endif; ?>
                <?php 
                  $ignite_agency_portfolio_banner_second_title = get_theme_mod('ignite_agency_portfolio_banner_second_title');
                  if ( $ignite_agency_portfolio_banner_second_title ) : ?>
                  <h2 class="slide-second-title">
                      <?php echo esc_html($ignite_agency_portfolio_banner_second_title); ?>
                  </h2>
              <?php endif; ?>
            </div>
        </div>
      </div>
    </div>
  </div>
<?php } ?>