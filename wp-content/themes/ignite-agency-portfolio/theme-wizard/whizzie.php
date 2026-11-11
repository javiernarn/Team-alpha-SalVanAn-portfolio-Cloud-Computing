<?php

/**
 * Wizard
 *
 * @package Whizzie
 * @author Catapult Themes
 * @since 1.0.0
 */

class Whizzie {

	protected $version = '1.1.0';

	/** @var string Current theme name, used as namespace in actions. */
	protected $theme_name = '';
	protected $theme_title = '';

	protected $plugin_path = '';
	protected $parent_slug = '';

	/** @var string Wizard page slug and title. */
	protected $page_slug = '';
	protected $page_title = '';

	/** @var array Wizard steps set by user. */
	protected $config_steps = array();

	/**
	 * Relative plugin url for this plugin folder
	 * @since 1.0.0
	 * @var string
	 */
	protected $plugin_url = '';

	/**
	 * TGMPA instance storage
	 *
	 * @var object
	 */
	protected $tgmpa_instance;

	/**
	 * TGMPA Menu slug
	 *
	 * @var string
	 */
	protected $tgmpa_menu_slug = 'tgmpa-install-plugins';

	/**
	 * TGMPA Menu url
	 *
	 * @var string
	 */
	protected $tgmpa_url = 'themes.php?page=tgmpa-install-plugins';

	// Where to find the widget.wie file
	protected $widget_file_url = '';

	/**
	 * Constructor
	 *
	 * @param $config	Our config parameters
	 */
	public function __construct( $config ) {
		$this->set_vars( $config );
		$this->init();
	}

	/**
	 * Set some settings
	 * @since 1.0.0
	 * @param $config	Our config parameters
	 */
	public function set_vars( $config ) {

		require_once trailingslashit( WHIZZIE_DIR ) . 'tgm/tgm.php';

		if( isset( $config['page_slug'] ) ) {
			$this->page_slug = esc_attr( $config['page_slug'] );
		}
		if( isset( $config['page_title'] ) ) {
			$this->page_title = esc_attr( $config['page_title'] );
		}
		if( isset( $config['steps'] ) ) {
			$this->config_steps = $config['steps'];
		}

		$this->plugin_path = trailingslashit( dirname( __FILE__ ) );
		$relative_url = str_replace( get_template_directory(), '', $this->plugin_path );
		$this->plugin_url = trailingslashit( get_template_directory_uri() . $relative_url );
		$current_theme = wp_get_theme();
		$this->theme_title = $current_theme->get( 'Name' );
		$this->theme_name = strtolower( preg_replace( '#[^a-zA-Z]#', '', $current_theme->get( 'Name' ) ) );
		$this->page_slug = apply_filters( $this->theme_name . '_theme_setup_wizard_page_slug', $this->theme_name . '-wizard' );
		$this->parent_slug = apply_filters( $this->theme_name . '_theme_setup_wizard_parent_slug', '' );

	}

	/*
	 * Hooks and filters
	 * @since 1.0.0
	 */
	public function init() {

		if ( class_exists( 'TGM_Plugin_Activation' ) && isset( $GLOBALS['tgmpa'] ) ) {
			add_action( 'init', array( $this, 'get_tgmpa_instance' ), 30 );
			add_action( 'init', array( $this, 'set_tgmpa_url' ), 40 );
		}
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
		add_action( 'admin_menu', array( $this, 'menu_page' ) );
		add_action( 'admin_init', array( $this, 'get_plugins' ), 30 );
		add_filter( 'tgmpa_load', array( $this, 'tgmpa_load' ), 10, 1 );
		add_action( 'wp_ajax_setup_plugins', array( $this, 'setup_plugins' ) );
		add_action( 'wp_ajax_setup_widgets', array( $this, 'setup_widgets' ) );

	}

	public function enqueue_scripts($hook) {

		wp_enqueue_style( 'theme-wizard-style', get_template_directory_uri() . '/theme-wizard/assets/css/theme-wizard-style.css');

		wp_register_script( 'theme-wizard-script', get_template_directory_uri() . '/theme-wizard/assets/js/theme-wizard-script.js', array( 'jquery' ), time() );
		wp_localize_script(
			'theme-wizard-script',
			'ignite_agency_portfolio_whizzie_params',
			array(
				'ajaxurl' 		=> admin_url( 'admin-ajax.php' ),
				'wpnonce' 		=> wp_create_nonce( 'whizzie_nonce' ),
				'verify_text'	=> esc_html( 'verifying', 'ignite-agency-portfolio' )
			)
		);
		wp_enqueue_script( 'theme-wizard-script' );

	}

	public static function get_instance() {
		if ( ! self::$instance ) {
			self::$instance = new self;
		}
		return self::$instance;
	}

	public function tgmpa_load( $status ) {
		return is_admin() || current_user_can( 'install_themes' );
	}

	/**
	 * Get configured TGMPA instance
	 *
	 * @access public
	 * @since 1.1.2
	 */
	public function get_tgmpa_instance() {
		$this->tgmpa_instance = call_user_func( array( get_class( $GLOBALS['tgmpa'] ), 'get_instance' ) );
	}

	/**
	 * Update $tgmpa_menu_slug and $tgmpa_parent_slug from TGMPA instance
	 *
	 * @access public
	 * @since 1.1.2
	 */
	public function set_tgmpa_url() {
		$this->tgmpa_menu_slug = ( property_exists( $this->tgmpa_instance, 'menu' ) ) ? $this->tgmpa_instance->menu : $this->tgmpa_menu_slug;
		$this->tgmpa_menu_slug = apply_filters( $this->theme_name . '_theme_setup_wizard_tgmpa_menu_slug', $this->tgmpa_menu_slug );
		$tgmpa_parent_slug = ( property_exists( $this->tgmpa_instance, 'parent_slug' ) && $this->tgmpa_instance->parent_slug !== 'themes.php' ) ? 'admin.php' : 'themes.php';
		$this->tgmpa_url = apply_filters( $this->theme_name . '_theme_setup_wizard_tgmpa_url', $tgmpa_parent_slug . '?page=' . $this->tgmpa_menu_slug );
	}

	/**
	 * Make a modal screen for the wizard
	 */
	public function menu_page() {
		add_theme_page( esc_html( $this->page_title ), esc_html( $this->page_title ), 'manage_options', $this->page_slug, array( $this, 'ignite_agency_portfolio_setup_wizard' ) );
	}

	/**
	 * Make an interface for the wizard
	 */
	public function wizard_page() {
		tgmpa_load_bulk_installer();
		// install plugins with TGM.
		if ( ! class_exists( 'TGM_Plugin_Activation' ) || ! isset( $GLOBALS['tgmpa'] ) ) {
			die( 'Failed to find TGM' );
		}
		$url = wp_nonce_url( add_query_arg( array( 'plugins' => 'go' ) ), 'whizzie-setup' );

		// copied from TGM
		$method = ''; // Leave blank so WP_Filesystem can populate it as necessary.
		$fields = array_keys( $_POST ); // Extra fields to pass to WP_Filesystem.
		if ( false === ( $creds = request_filesystem_credentials( esc_url_raw( $url ), $method, false, false, $fields ) ) ) {
			return true; // Stop the normal page form from displaying, credential request form will be shown.
		}
		// Now we have some credentials, setup WP_Filesystem.
		if ( ! WP_Filesystem( $creds ) ) {
			// Our credentials were no good, ask the user for them again.
			request_filesystem_credentials( esc_url_raw( $url ), $method, true, false, $fields );
			return true;
		}
		/* If we arrive here, we have the filesystem */ ?>
		<div class="main-wrap">

			<?php if ( ! ignite_agency_portfolio_is_whizzie_dismissed() ) : ?>
				<div class="homepage-setup whizzie-notice fresh-whizzie-notice" data-notice="whizzie">
					<button class="whizzie-dismiss" aria-label="<?php esc_attr_e( 'Dismiss', 'ignite-agency-portfolio' ); ?>">×</button>

					<div class="homepage-setup-theme-bundle">
						<div class="homepage-setup-theme-bundle-one">
							<h1><?php echo wp_kses_post( 'WP Theme Bundle - Get All Themes For Just <span class="price">$79</span>' ); ?></h1>
							<p><?php esc_html_e( 'Get our all 70+ premium themes now and Transform your website with our Ultimate WordPress Theme Bundle.', 'ignite-agency-portfolio' ); ?></p>
						</div>

						<div class="homepage-setup-theme-bundle-two">
							<p><?php echo wp_kses_post( '<del>$2440</del> $79' ); ?></p>
							<a href="<?php echo esc_url( IGNITE_AGENCY_PORTFOLIO_BUNDLE_URL ); ?>" target="_blank">
								<p class="buy-themes"><?php esc_html_e( 'BUY ALL THEMES FOR $79', 'ignite-agency-portfolio' ); ?></p>
							</a>
						</div>

						<div class="homepage-setup-theme-bundle-three">
							<div class="extra-btn"> <p><?php echo wp_kses_post( 'Extra<div>20% OFF</div>', 'ignite-agency-portfolio' ); ?></p> </div>
							<img src="<?php echo esc_url( get_template_directory_uri() . '/images/notice.png' ); ?>" alt="">
						</div>
					</div>
				</div>
			<?php endif; ?>

			<?php
			echo '<div class="card whizzie-wrap">';
				// The wizard is a list with only one item visible at a time
				$steps = $this->get_steps();
				echo '<ul class="whizzie-menu">';
				foreach( $steps as $step ) {
					$class = 'step step-' . esc_attr( $step['id'] );
					echo '<li data-step="' . esc_attr( $step['id'] ) . '" class="' . esc_attr( $class ) . '">';
						printf( '<h2>%s</h2>', esc_html( $step['title'] ) );
						// $content is split into summary and detail
						$content = call_user_func( array( $this, $step['view'] ) );
						if( isset( $content['summary'] ) ) {
							printf(
								'<div class="summary">%s</div>',
								wp_kses_post( $content['summary'] )
							);
						}
						if( isset( $content['detail'] ) ) {
							// Add a link to see more detail
							printf( '<p><a href="#" class="more-info">%s</a></p>', __( 'More Info', 'ignite-agency-portfolio' ) );
							printf(
								'<div class="detail">%s</div>',
								$content['detail'] // Need to escape this
							);
						}
						// The next button

						$ignite_agency_portfolio_import_done = get_option( 'ignite_agency_portfolio_demo_import_done' );

						if ( isset( $step['button_text'] ) && $step['button_text'] ) {

							// INTRO STEP + DEMO ALREADY IMPORTED → VIEW SITE ONLY
							if ( $ignite_agency_portfolio_import_done && $step['id'] === 'intro' ) {

								echo '<div class="button-wrap">
										<a href="' . esc_url( home_url() ) . '"
										class="button button-primary"
										target="_blank">
										' . esc_html( $step['button_text'] ) . '
										</a>
									</div>';

							} else {

								// NORMAL WIZARD FLOW
								printf(
									'<div class="button-wrap">
										<a href="#"
										class="button button-primary do-it"
										data-callback="%s"
										data-step="%s">%s</a>
									</div>',
									esc_attr( $step['callback'] ),
									esc_attr( $step['id'] ),
									esc_html( $step['button_text'] )
								);
							}
						}

						// The skip button
						if( isset( $step['can_skip'] ) && $step['can_skip'] ) {
							printf(
								'<div class="button-wrap" style="margin-left: 0.5em;"><a href="#" class="button button-secondary do-it" data-callback="%s" data-step="%s">%s</a></div>',
								'do_next_step',
								esc_attr( $step['id'] ),
								__( 'Skip', 'ignite-agency-portfolio' )
							);
						}

					echo '</li>';
				}
				echo '</ul>';
				echo '<ul class="whizzie-nav">';
					foreach( $steps as $step ) {
						if( isset( $step['icon'] ) && $step['icon'] ) {
							echo '<li class="nav-step-' . esc_attr( $step['id'] ) . '">';
							if (isset($step['icon1'])) {
								require_once $step['icon1'];
							} else {
								echo '<span class="dashicons dashicons-' . esc_attr( $step['icon'] ) . '"></span>';
							}
							echo '</li>';
						}
					}
				echo '</ul>';
				?>
				<div class="step-loading">
					<span class="spinner">
						<svg version="1.2" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 562 656" width="50" height="50"><style>.a{fill:#fff;stroke:#fff;stroke-linejoin:round}</style><path fill-rule="evenodd" class="a" d="m302.5 0c6.4 0.1 8.9 3.3 13 5.5 3.2 1.7 6.9 2.1 10 4 4.5 2.7 9.1 6.2 13.5 9 6.2 3.9 12.8 6.2 19 10 13.6 8.5 26.6 18.5 39.5 28 8.6 6.3 16.8 14 25 21 2.2 1.8 3.2 5.3 5.5 7 18.7 14.2 36.6 36.2 50.5 55.5 7.5 10.4 16.6 19.9 23.5 31 17.3 27.8 31.6 59.7 43 93 1.2 3.7 0.9 6.3 2 10 3 9.9 5.4 21.4 8 31.5q0.5 6.5 1 13c1.8 7.2 2.1 17.6 4 25v12c3.3 14.2 2.1 33.7-1 47v14.5c-4.3 17.5-6.8 38.2-13 54.5-23.5 61.9-63.3 110.2-116 143-19.1 11.9-41.5 20.7-64 29-7.7 2.9-16.3 3.9-24.5 6-3.6 0.9-5.9 0.1-9.5 1q-3.4 0.9-6.8 1.6-3.4 0.7-6.8 1.2-3.5 0.5-7 0.8-3.4 0.3-6.9 0.4c-18.3 0.2-41.6 2.7-58-1l-14.5-1c-17.5-4.3-34.5-7.5-50.5-13-7.9-2.7-15.4-3.5-22.5-7-15.2-7.5-30.4-14.8-44-24-40.3-27-71.6-66.7-92-113.5-8.9-20.5-12.8-45.6-19-68.5-2.4-9 0.5-20.1-2-29.5-1.2-4.4-1-11.6-1-17.5 0-18.5 1.4-34.4 6-48 2.9-8.5 2.9-17.3 6-25.5 6.5-17.2 15.7-37.5 25-52.5 4.7-7.5 10.9-14.4 16-21.5 8.9-12.3 16.9-24.9 29-34l12-12.5c7.5-5.5 14.7-10.8 22.5-16 6-4 8.7-9 19-9 2.3 5.2 2 12.9 3.5 19.5v7.5c6.3 25.1 12.2 62.7 29 76.5 3-0.6 5.3-2.5 8-3.5 4.6-1.7 8.8-1.9 13-4 7.3-3.6 14.8-7.9 21.5-12 38.2-23.4 63.6-53.4 80.5-98 4.3-11.3 6.1-25 9-37q0.5-6.8 1-13.5c2.5-10.7 4.1-36.1 1.5-47.5q-0.3-9-0.5-18c-2.1-8.5-3-17.8-3-28.5q0.3-0.2 0.7-0.4 0.3-0.3 0.6-0.5 0.3-0.3 0.6-0.5 0.3-0.3 0.6-0.6z"></path></svg>
					</span>
				</div>
			</div><!-- .whizzie-wrap -->

		</div><!-- .wrap -->
	<?php }

	public function ignite_agency_portfolio_setup_wizard() {
		?>
			<div class="wrapper-info get-stared-page-wrap">
				<div id="demo_offer">
					<?php $this->wizard_page(); ?>
				</div>
			</div>
		<?php
	}

	/**
	 * Set options for the steps
	 * Incorporate any options set by the theme dev
	 * Return the array for the steps
	 * @return Array
	 */
	public function get_steps() {
		$ignite_agency_portfolio_import_done = get_option( 'ignite_agency_portfolio_demo_import_done' );
		$ignite_agency_portfolio_button_text = $ignite_agency_portfolio_import_done
			? __( 'View Site', 'ignite-agency-portfolio' )
			: get_theme_mod(
				'ignite_agency_portfolio_start_button_text',
				__( 'Start Now', 'ignite-agency-portfolio' )
			);
		$dev_steps = $this->config_steps;
		$steps = array(
			'intro' => array(
				'id'    => 'intro',
				'title' => __( 'Welcome to ', 'ignite-agency-portfolio' ) . $this->theme_title,
				'icon'  => 'dashboard',
				'icon1' => get_template_directory() . '/theme-wizard/assets/images/svg/Icon-01.svg',
				'view'  => 'get_step_intro',
				'callback' => $ignite_agency_portfolio_import_done ? '' : 'do_next_step',
				'button_text' => $ignite_agency_portfolio_button_text,
				'can_skip' => false
			),
			'plugins' => array(
				'id'			=> 'plugins',
				'title'			=> __( 'Plugins', 'ignite-agency-portfolio' ),
				'icon'			=> 'admin-plugins',
				'icon1'			=>	get_template_directory() . '/theme-wizard/assets/images/svg/Icon-02.svg',
				'view'			=> 'get_step_plugins',
				'callback'		=> 'install_plugins',
				'button_text'	=> __( 'Install Plugins', 'ignite-agency-portfolio' ),
				'can_skip'		=> true
			),
			'widgets' => array(
				'id'    => 'widgets',
				'title' => __( 'Demo Importer', 'ignite-agency-portfolio' ),
				'icon'  => 'welcome-widgets-menus',
				'icon1' => get_template_directory() . '/theme-wizard/assets/images/svg/Icon-03.svg',
				'view'  => 'get_step_widgets',

				// Keep the callback active even after a previous import so the
				// menu, pages and widgets can always be (re)generated instead
				// of the button becoming a dead click once the "done" flag is set.
				'callback' => 'install_widgets',

				'button_text' => $ignite_agency_portfolio_import_done
					? __( 'Re-import Demo', 'ignite-agency-portfolio' )
					: __( 'Import Demo', 'ignite-agency-portfolio' ),

				'can_skip' => true
			),
			'done' => array(
				'id'			=> 'done',
				'title'			=> __( 'All Done', 'ignite-agency-portfolio' ),
				'icon'			=> 'yes',
				'icon1'			=>	get_template_directory() . '/theme-wizard/assets/images/svg/Icon-04.svg',
				'view'			=> 'get_step_done',
				'callback'		=> ''
			)
		);

		// Iterate through each step and replace with dev config values
		if( $dev_steps ) {
			// Configurable elements - these are the only ones the dev can update from config.php
			$can_config = array( 'title', 'icon', 'button_text', 'can_skip' );
			foreach( $dev_steps as $dev_step ) {
				// We can only proceed if an ID exists and matches one of our IDs
				if( isset( $dev_step['id'] ) ) {
					$id = $dev_step['id'];
					if( isset( $steps[$id] ) ) {
						foreach( $can_config as $element ) {
							if( isset( $dev_step[$element] ) ) {
								$steps[$id][$element] = $dev_step[$element];
							}
						}
					}
				}
			}
		}
		return $steps;
	}

	/**
	 * Print the content for the intro step
	 */
		public function get_step_intro() { ?>
			<div class="summary">
				<p>
					<?php
					printf(
						/* translators: %s: Theme name */
						esc_html__('Thank you for choosing this %s Theme. Using this quick setup wizard, you will be able to configure your new website and get it running in just a few minutes. Just follow these simple steps mentioned in the wizard and get started with your website.', 'ignite-agency-portfolio'),
						$this->theme_title
					);
					?>
				</p>
				<p>
					<?php esc_html_e('You may even skip the steps and get back to the dashboard if you have no time at the present moment. You can come back any time if you change your mind.','ignite-agency-portfolio'); ?>
				</p>
			</div>
		<?php }

	/**
	 * Get the content for the plugins step
	 * @return $content Array
	 */
	public function get_step_plugins() {
		$plugins = $this->get_plugins();
		$content = array(); ?>
			<div class="summary">
				<p>
					<?php esc_html_e('Additional plugins always make your website exceptional. Install these plugins by clicking the install button. You may also deactivate them from the dashboard.','ignite-agency-portfolio') ?>
				</p>
			</div>
		<?php // The detail element is initially hidden from the user
		$content['detail'] = '<ul class="whizzie-do-plugins">';

		// Add each plugin into a list
		foreach( $plugins['all'] as $slug=>$plugin ) {
			$content['detail'] .= '<li data-slug="' . esc_attr( $slug ) . '">' . esc_html( $plugin['name'] ) . '<span>';
			$keys = array();
			if ( isset( $plugins['install'][ $slug ] ) ) {
			    $keys[] = 'Installation';
			}
			if ( isset( $plugins['update'][ $slug ] ) ) {
			    $keys[] = 'Update';
			}
			if ( isset( $plugins['activate'][ $slug ] ) ) {
			    $keys[] = 'Activation';
			}
			$content['detail'] .= implode( ' and ', $keys ) . ' required';
			$content['detail'] .= '</span></li>';
		}
		$content['detail'] .= '</ul>';

		return $content;
	}

	/**
	 * Print the content for the widgets step
	 * @since 1.1.0
	 */
	public function get_step_widgets() { ?>
		<div class="summary">
			<p>
				<?php esc_html_e('This theme supports importing the demo content and adding widgets. Get them installed with the below button. Using the Customizer, it is possible to update or even deactivate them','ignite-agency-portfolio'); ?>
			</p>
		</div>
	<?php }

	/**
	 * Print the content for the final step
	 */
	public function get_step_done() { ?>
		<div id="ti-demo-setup-guid">
			<div class="ti-setup-menu">
				<h3><?php esc_html_e('Setup Navigation Menu','ignite-agency-portfolio'); ?></h3>
				<p><?php esc_html_e('This theme supports importing the demo content and adding widgets. Get them installed with the below button. Using the Customizer, it is possible to update or even deactivate them','ignite-agency-portfolio'); ?></p>
				<h4><?php esc_html_e('A) Create Pages','ignite-agency-portfolio'); ?></h4>
				<ol>
					<li><?php esc_html_e('Go to Dashboard >> Pages >> Add New','ignite-agency-portfolio'); ?></li>
					<li><?php esc_html_e('Enter Page Details And Save Changes','ignite-agency-portfolio'); ?></li>
				</ol>
				<h4><?php esc_html_e('B) Add Pages To Menu','ignite-agency-portfolio'); ?></h4>
				<ol>
					<li><?php esc_html_e('Go to Dashboard >> Appearance >> Menu','ignite-agency-portfolio'); ?></li>
					<li><?php esc_html_e('Click On The Create Menu Option','ignite-agency-portfolio'); ?></li>
					<li><?php esc_html_e('Select The Pages And Click On The Add to Menu Button','ignite-agency-portfolio'); ?></li>
					<li><?php esc_html_e('Select Primary Menu From The Menu Setting','ignite-agency-portfolio'); ?></li>
					<li><?php esc_html_e('Click On The Save Menu Button','ignite-agency-portfolio'); ?></li>
				</ol>
			</div>
			<div class="ti-setup-widget">
				<h3><?php esc_html_e('Setup Footer Widgets','ignite-agency-portfolio'); ?></h3>
				<ol>
					<li><?php esc_html_e('Go to Dashboard >> Appearance >> Widgets','ignite-agency-portfolio'); ?></li>
					<li><?php esc_html_e('Drag And Add The Widgets In The Footer Columns','ignite-agency-portfolio'); ?></li>
				</ol>
			</div>
			<div class="ti-setup-dots">
				<button type="button" id="ti-prev" class="nav-btn prev"><?php esc_html_e('Previous','ignite-agency-portfolio'); ?></button>

				<input type="radio" name="r1" id="ti-setup-menu" checked hidden>
				<input type="radio" name="r1" id="ti-setup-widget" hidden>

				<button type="button" id="ti-next" class="nav-btn next"><?php esc_html_e('Next','ignite-agency-portfolio'); ?></button>
			</div>
			<div style="display:flex; justify-content:center; flex-wrap: wrap;">
			<div class="ti-setup-finish">
				<a target="_blank" href="<?php echo esc_url(home_url()); ?>" class="button button-primary">
					<?php esc_html_e('Visit Site','ignite-agency-portfolio'); ?>
				</a>
			</div>
			<div class="ti-setup-finish">
				<a target="_blank" href="<?php echo esc_url( admin_url('customize.php') ); ?>" class="button button-primary">
					<?php esc_html_e('Customize Your Demo','ignite-agency-portfolio'); ?>
				</a>
			</div>
			<div class="ti-setup-finish">
				<a target="_blank" href="<?php echo esc_url( admin_url('themes.php?page=ignite-agency-portfolio') ); ?>" class="button button-primary"><?php esc_html_e('Dashboard','ignite-agency-portfolio'); ?></a>
			</div>
		</div>
		</div>
	<?php }

	/**
	 * Get the plugins registered with TGMPA
	 */
	public function get_plugins() {
		$instance = call_user_func( array( get_class( $GLOBALS['tgmpa'] ), 'get_instance' ) );
		$plugins = array(
			'all' 		=> array(),
			'install'	=> array(),
			'update'	=> array(),
			'activate'	=> array()
		);
		foreach( $instance->plugins as $slug=>$plugin ) {
			if( $instance->is_plugin_active( $slug ) && false === $instance->does_plugin_have_update( $slug ) ) {
				// Plugin is installed and up to date
				continue;
			} else {
				$plugins['all'][$slug] = $plugin;
				if( ! $instance->is_plugin_installed( $slug ) ) {
					$plugins['install'][$slug] = $plugin;
				} else {
					if( false !== $instance->does_plugin_have_update( $slug ) ) {
						$plugins['update'][$slug] = $plugin;
					}
					if( $instance->can_plugin_activate( $slug ) ) {
						$plugins['activate'][$slug] = $plugin;
					}
				}
			}
		}
		return $plugins;
	}

	public function setup_plugins() {
		if ( ! check_ajax_referer( 'whizzie_nonce', 'wpnonce' ) || empty( $_POST['slug'] ) ) {
			wp_send_json_error( array( 'error' => 1, 'message' => esc_html__( 'No Slug Found','ignite-agency-portfolio' ) ) );
		}
		$json = array();
		// send back some json we use to hit up TGM
		$plugins = $this->get_plugins();

		// what are we doing with this plugin?
		foreach ( $plugins['activate'] as $slug => $plugin ) {
			if ( $_POST['slug'] == $slug ) {
				$json = array(
					'url'           => admin_url( $this->tgmpa_url ),
					'plugin'        => array( $slug ),
					'tgmpa-page'    => $this->tgmpa_menu_slug,
					'plugin_status' => 'all',
					'_wpnonce'      => wp_create_nonce( 'bulk-plugins' ),
					'action'        => 'tgmpa-bulk-activate',
					'action2'       => - 1,
					'message'       => esc_html__( 'Activating Plugin','ignite-agency-portfolio' ),
				);
				break;
			}
		}
		foreach ( $plugins['update'] as $slug => $plugin ) {
			if ( $_POST['slug'] == $slug ) {
				$json = array(
					'url'           => admin_url( $this->tgmpa_url ),
					'plugin'        => array( $slug ),
					'tgmpa-page'    => $this->tgmpa_menu_slug,
					'plugin_status' => 'all',
					'_wpnonce'      => wp_create_nonce( 'bulk-plugins' ),
					'action'        => 'tgmpa-bulk-update',
					'action2'       => - 1,
					'message'       => esc_html__( 'Updating Plugin','ignite-agency-portfolio' ),
				);
				break;
			}
		}
		foreach ( $plugins['install'] as $slug => $plugin ) {
			if ( $_POST['slug'] == $slug ) {
				$json = array(
					'url'           => admin_url( $this->tgmpa_url ),
					'plugin'        => array( $slug ),
					'tgmpa-page'    => $this->tgmpa_menu_slug,
					'plugin_status' => 'all',
					'_wpnonce'      => wp_create_nonce( 'bulk-plugins' ),
					'action'        => 'tgmpa-bulk-install',
					'action2'       => - 1,
					'message'       => esc_html__( 'Installing Plugin','ignite-agency-portfolio' ),
				);
				break;
			}
		}
		if ( $json ) {
			$json['hash'] = md5( serialize( $json ) ); // used for checking if duplicates happen, move to next plugin
			wp_send_json( $json );
		} else {
			wp_send_json( array( 'done' => 1, 'message' => esc_html__( 'Success','ignite-agency-portfolio' ) ) );
		}
		exit;
	}

	/**
	 * Find a published page by its title without relying on the deprecated
	 * get_page_by_title() core function.
	 *
	 * @param string $pagename Page title to look up.
	 * @return int Page ID, or 0 when no matching page exists.
	 */
	public static function get_page_id_by_title( $pagename ) {

		$args = array(
			'post_type' => 'page',
			'posts_per_page' => 1,
			'post_status' => 'publish',
			'title' => $pagename
		);
		$query = new WP_Query( $args );

		$page_id = 0;
		if ( isset( $query->post->ID ) ) {
			$page_id = $query->post->ID;
		}

		return $page_id;
	}

	public function create_theme_nav_menu(){

		// ------- Create Nav Menu --------
	   $menuname = 'Primary Menu';
	   $bpmenulocation = 'primary';
	   $menu_id = wp_get_nav_menu_object( $menuname );

			if( ! $menu_id ){
			$menu_id = wp_create_nav_menu($menuname);

			wp_update_nav_menu_item($menu_id, 0, array(
				'menu-item-title' =>  __('Home','ignite-agency-portfolio'),
				'menu-item-classes' => 'home-page',
				'menu-item-url' => home_url( '/' ),
				'menu-item-status' => 'publish'));

			wp_update_nav_menu_item($menu_id, 0, array(
				'menu-item-title' => __('Blog','ignite-agency-portfolio'),
				'menu-item-classes' => 'blog',
				'menu-item-url' => get_permalink( self::get_page_id_by_title( 'Blog' ) ),
				'menu-item-status' => 'publish',
			));
			} else {
				$menu_id = $menu_id->term_id;
			}

			// Always (re)assign the menu to the theme's Primary location so the
			// header navigation reflects the imported menu even if a previous
			// import already created the menu without assigning the location.
			$locations = get_theme_mod( 'nav_menu_locations', array() );
			if ( ! is_array( $locations ) ) {
				$locations = array();
			}
			$locations[ $bpmenulocation ] = $menu_id;
			set_theme_mod( 'nav_menu_locations', $locations );

			return $menu_id;
   		}

	/**
	 * Add a single widget instance into a registered sidebar.
	 *
	 * @param string $sidebar_id Registered sidebar/widget area id.
	 * @param string $id_base    Widget base id (e.g. 'text', 'nav_menu').
	 * @param array  $instance   Widget instance settings.
	 * @since 1.1.0
	 */
	public function add_widget_to_sidebar( $sidebar_id, $id_base, $instance ) {

		$widget_option  = 'widget_' . $id_base;
		$all_instances  = get_option( $widget_option, array() );
		if ( ! is_array( $all_instances ) ) {
			$all_instances = array();
		}

		$next_number = 2;
		foreach ( array_keys( $all_instances ) as $key ) {
			if ( is_numeric( $key ) && (int) $key >= $next_number ) {
				$next_number = (int) $key + 1;
			}
		}

		$all_instances[ $next_number ] = $instance;
		update_option( $widget_option, $all_instances );

		$widget_id       = $id_base . '-' . $next_number;
		$sidebars_widgets = get_option( 'sidebars_widgets', array() );
		if ( ! is_array( $sidebars_widgets ) ) {
			$sidebars_widgets = array();
		}
		if ( ! isset( $sidebars_widgets[ $sidebar_id ] ) || ! is_array( $sidebars_widgets[ $sidebar_id ] ) ) {
			$sidebars_widgets[ $sidebar_id ] = array();
		}
		if ( ! in_array( $widget_id, $sidebars_widgets[ $sidebar_id ], true ) ) {
			$sidebars_widgets[ $sidebar_id ][] = $widget_id;
		}
		update_option( 'sidebars_widgets', $sidebars_widgets );
	}

	/**
	 * Populate the theme's four footer widget areas with sample content
	 * so the footer isn't left empty after the demo import.
	 *
	 * @since 1.1.0
	 */
	public function setup_footer_widgets( $menu_id ) {

		// Footer One: about blurb.
		$this->add_widget_to_sidebar( 'footer-one', 'text', array(
			'title'  => __( 'About Us', 'ignite-agency-portfolio' ),
			'text'   => __( 'We are a full-service creative agency helping ambitious brands design, build and grow through strategy, design and technology.', 'ignite-agency-portfolio' ),
			'filter' => true,
		) );

		// Footer Two: quick links menu.
		$this->add_widget_to_sidebar( 'footer-two', 'nav_menu', array(
			'title'  => __( 'Quick Links', 'ignite-agency-portfolio' ),
			'nav_menu' => $menu_id,
		) );

		// Footer Three: latest posts.
		$this->add_widget_to_sidebar( 'footer-three', 'recent-posts', array(
			'title'  => __( 'Latest News', 'ignite-agency-portfolio' ),
			'number' => 5,
		) );

		// Footer Four: contact details.
		$this->add_widget_to_sidebar( 'footer-four', 'text', array(
			'title'  => __( 'Get In Touch', 'ignite-agency-portfolio' ),
			'text'   => __( "123 Business Avenue, Suite 100\nhello@example.com\n+1 (234) 567-890", 'ignite-agency-portfolio' ),
			'filter' => true,
		) );
	}

	/**
	 * Import agency/portfolio demo content.
	 *
	 * Creates the demo pages and populates the theme's own Banner and
	 * About section settings with sample agency/portfolio copy.
	 *
	 * @since 1.1.0
	 */
	public function setup_widgets() {

		// Create a front page and assign the home template
		$home_title = 'Home';
		if ( ! self::get_page_id_by_title( $home_title ) ) {
			$home_id = wp_insert_post( array(
				'post_type'   => 'page',
				'post_title'  => $home_title,
				'post_content' => '',
				'post_status' => 'publish',
				'post_author' => get_current_user_id(),
			) );
			// Set the home page template
			add_post_meta( $home_id, '_wp_page_template', '/template-home.php' );
		} else {
			$home_id = self::get_page_id_by_title( $home_title );
		}

		// Set the static front page
		update_option( 'page_on_front', $home_id );
		update_option( 'show_on_front', 'page' );

		// Create a blog page and assign it as the posts page
		$blog_title = 'Blog';
		if ( ! self::get_page_id_by_title( $blog_title ) ) {
			$blog_id = wp_insert_post( array(
				'post_type'   => 'page',
				'post_title'  => $blog_title,
				'post_status' => 'publish',
				'post_author' => get_current_user_id(),
				'post_content' => '',
			) );
		} else {
			$blog_id = self::get_page_id_by_title( $blog_title );
		}

		// Set the Blog page as the posts page
		update_option( 'page_for_posts', $blog_id );

		// Build the primary navigation menu
		$menu_id = $this->create_theme_nav_menu();

		// Populate the footer widget areas
		$this->setup_footer_widgets( $menu_id );

		/*--- Header Section Start ---*/

		set_theme_mod( 'ignite_agency_portfolio_header_btn_text', __( 'Get A Quote', 'ignite-agency-portfolio' ) );
		set_theme_mod( 'ignite_agency_portfolio_header_btn_url', '#' );

		/*--- Header Section End ---*/

		/*--- Banner Section Start ---*/

		set_theme_mod( 'ignite_agency_portfolio_banner_setting', true );
		set_theme_mod( 'ignite_agency_portfolio_head_info_title', __( "Let's Talk", 'ignite-agency-portfolio' ) );
		set_theme_mod( 'ignite_agency_portfolio_head_info_content', __( 'Have A Project In Mind?', 'ignite-agency-portfolio' ) );
		set_theme_mod( 'ignite_agency_portfolio_head_info_btn_text', __( 'Get In Touch', 'ignite-agency-portfolio' ) );
		set_theme_mod( 'ignite_agency_portfolio_head_info_btn_url', '#' );
		set_theme_mod( 'ignite_agency_portfolio_idea_title', __( 'Got An Idea?', 'ignite-agency-portfolio' ) );
		set_theme_mod( 'ignite_agency_portfolio_idea_content', __( "We'd Love To Hear It", 'ignite-agency-portfolio' ) );
		set_theme_mod( 'ignite_agency_portfolio_banner_first_title', __( 'Creative', 'ignite-agency-portfolio' ) );
		set_theme_mod( 'ignite_agency_portfolio_banner_second_title', __( 'Digital Agency', 'ignite-agency-portfolio' ) );
		set_theme_mod( 'ignite_agency_portfolio_banner_content', __( 'We craft memorable brands, websites and digital experiences that help businesses grow.', 'ignite-agency-portfolio' ) );
		set_theme_mod( 'ignite_agency_portfolio_right_image_box_3_count', __( '4.8k Plus', 'ignite-agency-portfolio' ) );
		set_theme_mod( 'ignite_agency_portfolio_right_image_box_3_text', __( 'Happy Clients', 'ignite-agency-portfolio' ) );

		/*--- Banner Section End ---*/

		/*--- About Section Start ---*/

		set_theme_mod( 'ignite_agency_portfolio_about_setting', true );
		set_theme_mod( 'ignite_agency_portfolio_about_text_extra', __( 'About Us', 'ignite-agency-portfolio' ) );
		set_theme_mod( 'ignite_agency_portfolio_about_title', __( 'Building Brands That Stand Out', 'ignite-agency-portfolio' ) );
		set_theme_mod( 'ignite_agency_portfolio_about_content', __( 'We are a full-service creative agency helping ambitious brands design, build and grow through strategy, design and technology.', 'ignite-agency-portfolio' ) );
		set_theme_mod( 'ignite_agency_portfolio_icon_text_1', __( 'Brand Strategy', 'ignite-agency-portfolio' ) );
		set_theme_mod( 'ignite_agency_portfolio_icon_text_2', __( 'Web Design', 'ignite-agency-portfolio' ) );
		set_theme_mod( 'ignite_agency_portfolio_icon_text_3', __( 'Development', 'ignite-agency-portfolio' ) );
		set_theme_mod( 'ignite_agency_portfolio_icon_text_4', __( 'Digital Marketing', 'ignite-agency-portfolio' ) );
		set_theme_mod( 'ignite_agency_portfolio_icon_text_5', __( 'UI/UX Design', 'ignite-agency-portfolio' ) );
		set_theme_mod( 'ignite_agency_portfolio_icon_text_6', __( 'Consulting', 'ignite-agency-portfolio' ) );
		set_theme_mod( 'ignite_agency_portfolio_about_btn_text', __( 'Learn More', 'ignite-agency-portfolio' ) );
		set_theme_mod( 'ignite_agency_portfolio_about_btn_url', '#' );
		set_theme_mod( 'ignite_agency_portfolio_about_expereince_count', __( '15+', 'ignite-agency-portfolio' ) );
		set_theme_mod( 'ignite_agency_portfolio_about_expereince_text', __( 'Years Of Experience', 'ignite-agency-portfolio' ) );

		/*--- About Section End ---*/

		/*--- Footer Section Start ---*/

		set_theme_mod( 'ignite_agency_portfolio_footer_copyright_text', sprintf(
			/* translators: %1$s: current year, %2$s: site name */
			__( '&copy; %1$s %2$s. All Rights Reserved.', 'ignite-agency-portfolio' ),
			gmdate( 'Y' ),
			get_bloginfo( 'name' )
		) );

		/*--- Footer Section End ---*/

		update_option( 'ignite_agency_portfolio_demo_import_done', true );
	}
}
