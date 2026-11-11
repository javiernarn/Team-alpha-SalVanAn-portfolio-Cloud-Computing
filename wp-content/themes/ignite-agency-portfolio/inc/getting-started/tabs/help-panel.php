<?php
/**
 * Help Panel.
 *
 * @package ignite_agency_portfolio
 */

$ignite_agency_portfolio_import_done = get_option( 'ignite_agency_portfolio_demo_import_done' );
$ignite_agency_portfolio_button_text = $ignite_agency_portfolio_import_done
	? __( 'View Site', 'ignite-agency-portfolio' )
	: __( 'Start Demo Import', 'ignite-agency-portfolio' );
$ignite_agency_portfolio_button_link = $ignite_agency_portfolio_import_done
	? home_url( '/' )
	: admin_url( 'themes.php?page=igniteagencyportfolio-wizard' );
?>
<div id="help-panel" class="panel-left visible">
    <div class="panel-aside active">
        <div class="demo-content">
            <div class="demo-info">
                <h4><?php esc_html_e( 'DEMO CONTENT IMPORTER', 'ignite-agency-portfolio' ); ?></h4>
                <p><?php esc_html_e('The Demo Content Importer helps you quickly set up your website to look exactly like the theme demo. Instead of building pages from scratch, you can import pre-designed layouts, pages, menus, images, and basic settings in just a few clicks.','ignite-agency-portfolio'); ?></p>
                <a class="button button-primary first-color" style="text-transform: capitalize" href="<?php echo esc_url( $ignite_agency_portfolio_button_link ); ?>" title="<?php echo esc_attr( $ignite_agency_portfolio_button_text ); ?>"
                    <?php echo $ignite_agency_portfolio_import_done ? 'target="_blank"' : ''; ?>>
                    <?php echo esc_html( $ignite_agency_portfolio_button_text ); ?>
                </a>
            </div>
            <div class="demo-img">
                <img src="<?php echo esc_url(get_stylesheet_directory_uri()) .'/screenshot.png'; ?>" alt="<?php echo esc_attr( 'screenshot', 'ignite-agency-portfolio'); ?>"/>
            </div>
        </div>
    </div>

    <div class="panel-aside" >
        <h4><?php esc_html_e( 'USEFUL LINKS', 'ignite-agency-portfolio' ); ?></h4>
        <p><?php esc_html_e( 'Find everything you need to set up, customize, and manage your website with ease. These helpful resources are designed to guide you at every step, from installation to advanced customization.', 'ignite-agency-portfolio' ); ?></p>
        <div class="useful-links">
            <a class="button button-primary second-color" href="<?php echo esc_url( IGNITE_AGENCY_PORTFOLIO_DEMO_URL ); ?>" title="<?php esc_attr_e( 'Live Demo', 'ignite-agency-portfolio' ); ?>" target="_blank">
                <?php esc_html_e( 'Live Demo', 'ignite-agency-portfolio' ); ?>
            </a>
            <a class="button button-primary first-color" href="<?php echo esc_url( IGNITE_AGENCY_PORTFOLIO_FREE_DOC_URL ); ?>" title="<?php esc_attr_e( 'Documentation', 'ignite-agency-portfolio' ); ?>" target="_blank">
                <?php esc_html_e( 'Documentation', 'ignite-agency-portfolio' ); ?>
            </a>
            <a class="button button-primary second-color" href="<?php echo esc_url( IGNITE_AGENCY_PORTFOLIO_URL ); ?>" title="<?php esc_attr_e( 'Get Premium', 'ignite-agency-portfolio' ); ?>" target="_blank">
                <?php esc_html_e( 'Get Premium', 'ignite-agency-portfolio' ); ?>
            </a>
            <a class="button button-primary first-color" href="<?php echo esc_url( IGNITE_AGENCY_PORTFOLIO_BUNDLE_URL ); ?>" title="<?php esc_attr_e( 'Get Bundle - 70+ Themes', 'ignite-agency-portfolio' ); ?>" target="_blank">
                <?php esc_html_e( 'Get Bundle - 70+ Themes', 'ignite-agency-portfolio' ); ?>
            </a>
        </div>
    </div>

    <div class="panel-aside" >
        <h4><?php esc_html_e( 'REVIEW', 'ignite-agency-portfolio' ); ?></h4>
        <p><?php esc_html_e( 'If you have a moment, please consider leaving a rating and short review. It only takes a minute, and your support means a lot to us.', 'ignite-agency-portfolio' ); ?></p>
        <a class="button button-primary first-color" href="<?php echo esc_url( IGNITE_AGENCY_PORTFOLIO_REVIEW_URL ); ?>" title="<?php esc_attr_e( 'Visit the Review', 'ignite-agency-portfolio' ); ?>" target="_blank">
            <?php esc_html_e( 'Leave a Review', 'ignite-agency-portfolio' ); ?>
        </a>
    </div>

    <div class="panel-aside">
        <h4><?php esc_html_e( 'CONTACT SUPPORT', 'ignite-agency-portfolio' ); ?></h4>
        <p>
            <?php esc_html_e( 'Thank you for choosing Ignite Agency Portfolio! We appreciate your interest in our theme and are here to assist you with any support you may need.', 'ignite-agency-portfolio' ); ?></p>
        <a class="button button-primary first-color" href="<?php echo esc_url( IGNITE_AGENCY_PORTFOLIO_SUPPORT_URL ); ?>" title="<?php esc_attr_e( 'Visit the Support', 'ignite-agency-portfolio' ); ?>" target="_blank">
            <?php esc_html_e( 'Contact Support', 'ignite-agency-portfolio' ); ?>
        </a>
    </div>
</div>
