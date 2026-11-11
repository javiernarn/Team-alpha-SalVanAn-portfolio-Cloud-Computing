<?php
/**
 * Ignite Agency Portfolio Theme Customizer.
 *
 * @link https://developer.WordPress.org/themes/basics/theme-functions/
 *
 * @package ignite_agency_portfolio
 */

if( ! function_exists( 'ignite_agency_portfolio_customize_register' ) ):  
/**
 * Add postMessage support for site title and description for the Theme Customizer.F
 *
 * @param WP_Customize_Manager $wp_customize Theme Customizer object.
 */
function ignite_agency_portfolio_customize_register( $wp_customize ) {
    require get_parent_theme_file_path('/inc/controls/changeable-icon.php');

    require get_parent_theme_file_path('/inc/controls/sortable-control.php');
    
    //Register the sortable control type.
    $wp_customize->register_control_type( 'Ignite_Agency_Portfolio_Control_Sortable' ); 
    

    if ( version_compare( get_bloginfo('version'),'4.9', '>=') ) {
        $wp_customize->get_section( 'static_front_page' )->title = __( 'Static Front Page', 'ignite-agency-portfolio' );
    }
	
    /* Option list of all post */	
    $ignite_agency_portfolio_options_posts = array();
    $ignite_agency_portfolio_options_posts_obj = get_posts('posts_per_page=-1');
    $ignite_agency_portfolio_options_posts[''] = esc_html__( 'Choose Post', 'ignite-agency-portfolio' );
    foreach ( $ignite_agency_portfolio_options_posts_obj as $ignite_agency_portfolio_posts ) {
    	$ignite_agency_portfolio_options_posts[$ignite_agency_portfolio_posts->ID] = $ignite_agency_portfolio_posts->post_title;
    }
    
    /* Option list of all categories */
    $ignite_agency_portfolio_args = array(
	   'type'                     => 'post',
	   'orderby'                  => 'name',
	   'order'                    => 'ASC',
	   'hide_empty'               => 1,
	   'hierarchical'             => 1,
	   'taxonomy'                 => 'category'
    ); 
    $ignite_agency_portfolio_option_categories = array();
    $ignite_agency_portfolio_category_lists = get_categories( $ignite_agency_portfolio_args );
    $ignite_agency_portfolio_option_categories[''] = esc_html__( 'Choose Category', 'ignite-agency-portfolio' );
    foreach( $ignite_agency_portfolio_category_lists as $ignite_agency_portfolio_category ){
        $ignite_agency_portfolio_option_categories[$ignite_agency_portfolio_category->term_id] = $ignite_agency_portfolio_category->name;
    }
    
    /** Default Settings */    
    $wp_customize->add_panel( 
        'wp_default_panel',
         array(
            'priority' => 10,
            'capability' => 'edit_theme_options',
            'theme_supports' => '',
            'title' => esc_html__( 'Default Settings', 'ignite-agency-portfolio' ),
            'description' => esc_html__( 'Default section provided by WordPress customizer.', 'ignite-agency-portfolio' ),
        ) 
    );
    
    $wp_customize->get_section( 'title_tagline' )->panel                  = 'wp_default_panel';
    $wp_customize->get_section( 'colors' )->panel                         = 'wp_default_panel';
    $wp_customize->get_section( 'header_image' )->panel                   = 'wp_default_panel';
    $wp_customize->get_section( 'background_image' )->panel               = 'wp_default_panel';
    $wp_customize->get_section( 'static_front_page' )->panel              = 'wp_default_panel';
    
    $wp_customize->get_setting( 'blogname' )->transport         = 'postMessage';
	$wp_customize->get_setting( 'blogdescription' )->transport  = 'postMessage';
	$wp_customize->get_setting( 'header_textcolor' )->transport = 'postMessage';
    
    /** Default Settings Ends */
    
    /** Site Title control */
    $wp_customize->add_setting( 
        'header_site_title', 
        array(
            'default'           => true,
            'sanitize_callback' => 'ignite_agency_portfolio_sanitize_checkbox',
        ) 
    );

    $wp_customize->add_control(
        'header_site_title',
        array(
            'label'       => __( 'Show / Hide Site Title', 'ignite-agency-portfolio' ),
            'section'     => 'title_tagline',
            'type'        => 'checkbox',
        )
    );

    /** Tagline control */
    $wp_customize->add_setting( 
        'header_tagline', 
        array(
            'default'           => false,
            'sanitize_callback' => 'ignite_agency_portfolio_sanitize_checkbox',
        ) 
    );

    $wp_customize->add_control(
        'header_tagline',
        array(
            'label'       => __( 'Show / Hide Tagline', 'ignite-agency-portfolio' ),
            'section'     => 'title_tagline',
            'type'        => 'checkbox',
        )
    );

    $wp_customize->add_setting('logo_width', array(
        'sanitize_callback' => 'absint', 
    ));

    // Add a control for logo width
    $wp_customize->add_control('logo_width', array(
        'label' => __('Logo Width', 'ignite-agency-portfolio'),
        'section' => 'title_tagline',
        'type' => 'number',
        'input_attrs' => array(
            'min' => '50', 
            'max' => '500', 
            'step' => '5', 
    ),
        'default' => '100', 
    ));

    $wp_customize->add_setting( 'ignite_agency_portfolio_site_title_size', array(
        'default'           => 20, // Default font size in pixels
        'sanitize_callback' => 'absint', // Sanitize the input as a positive integer
    ) );

    // Add control for site title size
    $wp_customize->add_control( 'ignite_agency_portfolio_site_title_size', array(
        'type'        => 'number',
        'section'     => 'title_tagline', // You can change this section to your preferred section
        'label'       => __( 'Site Title Font Size', 'ignite-agency-portfolio' ),
        'input_attrs' => array(
            'min'  => 10,
            'max'  => 100,
            'step' => 1,
        ),
    ) );

    /** Post & Pages Settings */
    $wp_customize->add_panel( 
        'ignite_agency_portfolio_post_settings',
         array(
            'priority' => 11,
            'capability' => 'edit_theme_options',
            'title' => esc_html__( 'Post & Pages Settings', 'ignite-agency-portfolio' ),
            'description' => esc_html__( 'Customize Post & Pages Settings', 'ignite-agency-portfolio' ),
        ) 
    );

        /** Post Layouts */
    
    $wp_customize->add_section(
        'ignite_agency_portfolio_post_layout_section',
        array(
            'title' => esc_html__( 'Post Layout Settings', 'ignite-agency-portfolio' ),
            'priority' => 20,
            'capability' => 'edit_theme_options',
            'panel' => 'ignite_agency_portfolio_post_settings',
        )
    );

    $wp_customize->add_setting('ignite_agency_portfolio_post_layout_setting', array(
        'default'           => 'right-sidebar',
        'sanitize_callback' => 'ignite_agency_portfolio_sanitize_post_layout',
    ));

    $wp_customize->add_control('ignite_agency_portfolio_post_layout_setting', array(
        'label'    => __('Post Column Settings', 'ignite-agency-portfolio'),
        'section'  => 'ignite_agency_portfolio_post_layout_section',
        'settings' => 'ignite_agency_portfolio_post_layout_setting',
        'type'     => 'select',
        'choices'  => array(
            'one-column'   => __('One Column', 'ignite-agency-portfolio'),
            'right-sidebar'   => __('Right Sidebar', 'ignite-agency-portfolio'),
            'left-sidebar'   => __('Left Sidebar', 'ignite-agency-portfolio'),
            'three-column'   => __('Three Columns', 'ignite-agency-portfolio'),
            'four-column'   => __('Four Columns', 'ignite-agency-portfolio'),
        ),
    ));

     /** Post Layouts Ends */
     
    /** Post Settings */
    $wp_customize->add_section(
        'ignite_agency_portfolio_post_settings',
        array(
            'title' => esc_html__( 'Post Settings', 'ignite-agency-portfolio' ),
            'priority' => 20,
            'capability' => 'edit_theme_options',
            'panel' => 'ignite_agency_portfolio_post_settings',
        )
    );

    /** Post Heading control */
    $wp_customize->add_setting( 
        'ignite_agency_portfolio_post_heading_setting', 
        array(
            'default'           => true,
            'sanitize_callback' => 'ignite_agency_portfolio_sanitize_checkbox',
        ) 
    );

    $wp_customize->add_control(
        'ignite_agency_portfolio_post_heading_setting',
        array(
            'label'       => __( 'Show / Hide Post Heading', 'ignite-agency-portfolio' ),
            'section'     => 'ignite_agency_portfolio_post_settings',
            'type'        => 'checkbox',
        )
    );

    /** Post Meta control */
    $wp_customize->add_setting( 
        'ignite_agency_portfolio_post_meta_setting', 
        array(
            'default'           => true,
            'sanitize_callback' => 'ignite_agency_portfolio_sanitize_checkbox',
        ) 
    );

    $wp_customize->add_control(
        'ignite_agency_portfolio_post_meta_setting',
        array(
            'label'       => __( 'Show / Hide Post Meta', 'ignite-agency-portfolio' ),
            'section'     => 'ignite_agency_portfolio_post_settings',
            'type'        => 'checkbox',
        )
    );

    /** Post Image control */
    $wp_customize->add_setting( 
        'ignite_agency_portfolio_post_image_setting', 
        array(
            'default'           => true,
            'sanitize_callback' => 'ignite_agency_portfolio_sanitize_checkbox',
        ) 
    );

    $wp_customize->add_control(
        'ignite_agency_portfolio_post_image_setting',
        array(
            'label'       => __( 'Show / Hide Post Image', 'ignite-agency-portfolio' ),
            'section'     => 'ignite_agency_portfolio_post_settings',
            'type'        => 'checkbox',
        )
    );

    /** Post Content control */
    $wp_customize->add_setting( 
        'ignite_agency_portfolio_post_content_setting', 
        array(
            'default'           => true,
            'sanitize_callback' => 'ignite_agency_portfolio_sanitize_checkbox',
        ) 
    );

    $wp_customize->add_control(
        'ignite_agency_portfolio_post_content_setting',
        array(
            'label'       => __( 'Show / Hide Post Content', 'ignite-agency-portfolio' ),
            'section'     => 'ignite_agency_portfolio_post_settings',
            'type'        => 'checkbox',
        )
    );
    /** Post ReadMore control */
     $wp_customize->add_setting( 'ignite_agency_portfolio_read_more_setting`', array(
        'default'           => true,
        'sanitize_callback' => 'ignite_agency_portfolio_sanitize_checkbox',
    ) );

    $wp_customize->add_control( 'ignite_agency_portfolio_read_more_setting`', array(
        'type'        => 'checkbox',
        'section'     => 'ignite_agency_portfolio_post_settings', 
        'label'       => __( 'Display Read More Button', 'ignite-agency-portfolio' ),
    ) );

    $wp_customize->add_setting('ignite_agency_portfolio_blog_meta_order', array(
        'default' => array('heading', 'author', 'featured-image', 'content','button'),
        'sanitize_callback' => 'ignite_agency_portfolio_sanitize_sortable',
    ));
    $wp_customize->add_control(new Ignite_Agency_Portfolio_Control_Sortable($wp_customize, 'ignite_agency_portfolio_blog_meta_order', array(
        'label' => esc_html__('Post Meta Ordering', 'ignite-agency-portfolio'),
        'description' => __('Drag & drop post items to rearrange the ordering ( this control will not function by post format )', 'ignite-agency-portfolio') ,
        'section' => 'ignite_agency_portfolio_post_settings',
        'choices' => array(
            'heading' => __('heading', 'ignite-agency-portfolio') ,
            'author' => __('author', 'ignite-agency-portfolio') ,
            'featured-image' => __('featured-image', 'ignite-agency-portfolio') ,
            'content' => __('content', 'ignite-agency-portfolio') ,
            'button' => __('button', 'ignite-agency-portfolio') ,
        ) ,
    )));

    /** Post Settings Ends */

     /** Single Post Settings */
    $wp_customize->add_section(
        'ignite_agency_portfolio_single_post_settings',
        array(
            'title' => esc_html__( 'Single Post Settings', 'ignite-agency-portfolio' ),
            'priority' => 20,
            'capability' => 'edit_theme_options',
            'panel' => 'ignite_agency_portfolio_post_settings',
        )
    );

    /** Single Post Meta control */
    $wp_customize->add_setting( 
        'ignite_agency_portfolio_single_post_meta_setting', 
        array(
            'default'           => true,
            'sanitize_callback' => 'ignite_agency_portfolio_sanitize_checkbox',
        ) 
    );

    $wp_customize->add_control(
        'ignite_agency_portfolio_single_post_meta_setting',
        array(
            'label'       => __( 'Show / Hide Single Post Meta', 'ignite-agency-portfolio' ),
            'section'     => 'ignite_agency_portfolio_single_post_settings',
            'type'        => 'checkbox',
        )
    );

    /** Single Post Content control */
    $wp_customize->add_setting( 
        'ignite_agency_portfolio_single_post_content_setting', 
        array(
            'default'           => true,
            'sanitize_callback' => 'ignite_agency_portfolio_sanitize_checkbox',
        ) 
    );

    $wp_customize->add_control(
        'ignite_agency_portfolio_single_post_content_setting',
        array(
            'label'       => __( 'Show / Hide Single Post Content', 'ignite-agency-portfolio' ),
            'section'     => 'ignite_agency_portfolio_single_post_settings',
            'type'        => 'checkbox',
        )
    );

    //Global Color
    $wp_customize->add_section(
        'ignite_agency_portfolio_global_color',
        array(
            'title' => esc_html__( 'Global Color Settings', 'ignite-agency-portfolio' ),
            'priority' => 20,
            'capability' => 'edit_theme_options',
            'panel' => 'ignite_agency_portfolio_general_settings',
        )
    );

    $wp_customize->add_setting('ignite_agency_portfolio_primary_color', array(
        'default'           => '#4C788E',
        'sanitize_callback' => 'sanitize_hex_color',
    ));
    $wp_customize->add_control(new WP_Customize_Color_Control($wp_customize, 'ignite_agency_portfolio_primary_color', array(
        'label'    => __('Theme Primary Color', 'ignite-agency-portfolio'),
        'section'  => 'ignite_agency_portfolio_global_color',
        'settings' => 'ignite_agency_portfolio_primary_color',
    ))); 
    
    $wp_customize->add_setting('ignite_agency_portfolio_secondary_color', array(
        'default'           => '#1C2630',
        'sanitize_callback' => 'sanitize_hex_color',
    ));
    $wp_customize->add_control(new WP_Customize_Color_Control($wp_customize, 'ignite_agency_portfolio_secondary_color', array(
        'label'    => __('Theme Secondary Color', 'ignite-agency-portfolio'),
        'section'  => 'ignite_agency_portfolio_global_color',
        'settings' => 'ignite_agency_portfolio_secondary_color',
    ))); 

    $wp_customize->add_setting('ignite_agency_portfolio_tertiary_color', array(
        'default'           => '#DFEAEE',
        'sanitize_callback' => 'sanitize_hex_color',
    ));
    $wp_customize->add_control(new WP_Customize_Color_Control($wp_customize, 'ignite_agency_portfolio_tertiary_color', array(
        'label'    => __('Theme Tertiary Color', 'ignite-agency-portfolio'),
        'section'  => 'ignite_agency_portfolio_global_color',
        'settings' => 'ignite_agency_portfolio_tertiary_color',
    ))); 

    /** Single Post Settings Ends */

         // Typography Settings Section
    $wp_customize->add_section('ignite_agency_portfolio_typography_settings', array(
        'title'      => esc_html__('Typography Settings', 'ignite-agency-portfolio'),
        'priority'   => 30,
        'capability' => 'edit_theme_options',
        'panel' => 'ignite_agency_portfolio_general_settings',
    ));

    // Array of fonts to choose from
    $font_choices = array(
        ''               => __('Select', 'ignite-agency-portfolio'),
        'Arial'          => 'Arial, sans-serif',
        'Verdana'        => 'Verdana, sans-serif',
        'Helvetica'      => 'Helvetica, sans-serif',
        'Times New Roman'=> '"Times New Roman", serif',
        'Georgia'        => 'Georgia, serif',
        'Courier New'    => '"Courier New", monospace',
        'Trebuchet MS'   => '"Trebuchet MS", sans-serif',
        'Tahoma'         => 'Tahoma, sans-serif',
        'Palatino'       => '"Palatino Linotype", serif',
        'Garamond'       => 'Garamond, serif',
        'Impact'         => 'Impact, sans-serif',
        'Comic Sans MS'  => '"Comic Sans MS", cursive, sans-serif',
        'Lucida Sans'    => '"Lucida Sans Unicode", sans-serif',
        'Arial Black'    => '"Arial Black", sans-serif',
        'Gill Sans'      => '"Gill Sans", sans-serif',
        'Segoe UI'       => '"Segoe UI", sans-serif',
        'Open Sans'      => '"Open Sans", sans-serif',
        'Unbounded'   => 'Unbounded, sans-serif',
        'Inter'   => 'Inter, sans-serif',
        'Lato'           => 'Lato, sans-serif',
        'Montserrat'     => 'Montserrat, sans-serif',
        'Libre Baskerville' => 'Libre Baskerville',
    );

    // Heading Font Setting
    $wp_customize->add_setting('ignite_agency_portfolio_heading_font_family', array(
        'default'           => '',
        'sanitize_callback' => 'ignite_agency_portfolio_sanitize_choicess',
    ));
    $wp_customize->add_control('ignite_agency_portfolio_heading_font_family', array(
        'type'    => 'select',
        'choices' => $font_choices,
        'label'   => __('Select Font for Heading', 'ignite-agency-portfolio'),
        'section' => 'ignite_agency_portfolio_typography_settings',
    ));

    // Body Font Setting
    $wp_customize->add_setting('ignite_agency_portfolio_body_font_family', array(
        'default'           => '',
        'sanitize_callback' => 'ignite_agency_portfolio_sanitize_choicess',
    ));
    $wp_customize->add_control('ignite_agency_portfolio_body_font_family', array(
        'type'    => 'select',
        'choices' => $font_choices,
        'label'   => __('Select Font for Body', 'ignite-agency-portfolio'),
        'section' => 'ignite_agency_portfolio_typography_settings',
    ));

    /** Typography Settings Section End */

    /** General Settings */
    $wp_customize->add_panel( 
        'ignite_agency_portfolio_general_settings',
         array(
            'priority' => 11,
            'capability' => 'edit_theme_options',
            'title' => esc_html__( 'General Settings', 'ignite-agency-portfolio' ),
            'description' => esc_html__( 'Customize General Settings', 'ignite-agency-portfolio' ),
        ) 
    );

    /** General Settings */
    $wp_customize->add_section(
        'ignite_agency_portfolio_general_settings',
        array(
            'title' => esc_html__( 'Loader Settings', 'ignite-agency-portfolio' ),
            'priority' => 30,
            'capability' => 'edit_theme_options',
            'panel' => 'ignite_agency_portfolio_general_settings',
        )
    );

    /** Preloader control */
    $wp_customize->add_setting( 
        'ignite_agency_portfolio_header_preloader', 
        array(
            'default' => false,
            'sanitize_callback' => 'ignite_agency_portfolio_sanitize_checkbox',
        ) 
    );

    $wp_customize->add_control(
        'ignite_agency_portfolio_header_preloader',
        array(
            'label'       => __( 'Show Preloader', 'ignite-agency-portfolio' ),
            'section'     => 'ignite_agency_portfolio_general_settings',
            'type'        => 'checkbox',
        )
    );

    $wp_customize->add_setting('ignite_agency_portfolio_loader_layout_setting', array(
        'default' => 'load',
        'sanitize_callback' => 'sanitize_text_field',
    ));

    // Add control for loader layout
    $wp_customize->add_control('ignite_agency_portfolio_loader_layout_control', array(
        'label' => __('Preloader Layout', 'ignite-agency-portfolio'),
        'section' => 'ignite_agency_portfolio_general_settings',
        'settings' => 'ignite_agency_portfolio_loader_layout_setting',
        'type' => 'select',
        'choices' => array(
            'load' => __('Preloader 1', 'ignite-agency-portfolio'),
            'load-one' => __('Preloader 2', 'ignite-agency-portfolio'),
            'ctn-preloader' => __('Preloader 3', 'ignite-agency-portfolio'),
        ),
    ));

    /** Header Section Settings */
    $wp_customize->add_section(
        'ignite_agency_portfolio_header_section_settings',
        array(
            'title' => esc_html__( 'Header Section Settings', 'ignite-agency-portfolio' ),
            'priority' => 30,
            'capability' => 'edit_theme_options',
            'panel' => 'ignite_agency_portfolio_home_page_settings',
        )
    );

    /** Sticky Header control */
    $wp_customize->add_setting( 
        'ignite_agency_portfolio_sticky_header', 
        array(
            'default' => false,
            'sanitize_callback' => 'ignite_agency_portfolio_sanitize_checkbox',
        ) 
    );

    $wp_customize->add_control(
        'ignite_agency_portfolio_sticky_header',
        array(
            'label'       => __( 'Show Sticky Header', 'ignite-agency-portfolio' ),
            'section'     => 'ignite_agency_portfolio_header_section_settings',
            'type'        => 'checkbox',
        )
    );

    // header button Text
    $wp_customize->add_setting('ignite_agency_portfolio_header_btn_text', 
        array(
        'default'           => '',
        'type'              => 'theme_mod',
        'capability'        => 'edit_theme_options',    
        'sanitize_callback' => 'sanitize_text_field'
        )
    );

    $wp_customize->add_control('ignite_agency_portfolio_header_btn_text', 
        array(
        'label'       => __('Button Text', 'ignite-agency-portfolio'),
        'section'     => 'ignite_agency_portfolio_header_section_settings',   
        'settings'    => 'ignite_agency_portfolio_header_btn_text',
        'type'        => 'text'
        )
    );

    // header button Url
    $wp_customize->add_setting('ignite_agency_portfolio_header_btn_url', 
        array(
        'default'           => '',
        'type'              => 'theme_mod',
        'capability'        => 'edit_theme_options',    
        'sanitize_callback' => 'esc_url_raw'
        )
    );

    $wp_customize->add_control('ignite_agency_portfolio_header_btn_url', 
        array(
        'label'       => __('Button URL', 'ignite-agency-portfolio'),
        'section'     => 'ignite_agency_portfolio_header_section_settings',   
        'settings'    => 'ignite_agency_portfolio_header_btn_url',
        'type'        => 'url'
        )
    );

    // Add Setting for Menu Font Weight
    $wp_customize->add_setting( 'ignite_agency_portfolio_menu_font_weight', array(
        'default'           => '500',
        'sanitize_callback' => 'ignite_agency_portfolio_sanitize_font_weight',
    ) );

    // Add Control for Menu Font Weight
    $wp_customize->add_control( 'ignite_agency_portfolio_menu_font_weight', array(
        'label'    => __( 'Menu Font Weight', 'ignite-agency-portfolio' ),
        'section'  => 'ignite_agency_portfolio_header_section_settings',
        'type'     => 'select',
        'choices'  => array(
            '100' => __( '100 - Thin', 'ignite-agency-portfolio' ),
            '200' => __( '200 - Extra Light', 'ignite-agency-portfolio' ),
            '300' => __( '300 - Light', 'ignite-agency-portfolio' ),
            '400' => __( '400 - Normal', 'ignite-agency-portfolio' ),
            '500' => __( '500 - Medium', 'ignite-agency-portfolio' ),
            '600' => __( '600 - Semi Bold', 'ignite-agency-portfolio' ),
            '700' => __( '700 - Bold', 'ignite-agency-portfolio' ),
            '800' => __( '800 - Extra Bold', 'ignite-agency-portfolio' ),
            '900' => __( '900 - Black', 'ignite-agency-portfolio' ),
        ),
    ) );

    // Add Setting for Menu Text Transform
    $wp_customize->add_setting( 'ignite_agency_portfolio_menu_text_transform', array(
        'default'           => 'capitalize',
        'sanitize_callback' => 'ignite_agency_portfolio_sanitize_text_transform',
    ) );

    // Add Control for Menu Text Transform
    $wp_customize->add_control( 'ignite_agency_portfolio_menu_text_transform', array(
        'label'    => __( 'Menu Text Transform', 'ignite-agency-portfolio' ),
        'section'  => 'ignite_agency_portfolio_header_section_settings',
        'type'     => 'select',
        'choices'  => array(
            'none'       => __( 'None', 'ignite-agency-portfolio' ),
            'capitalize' => __( 'Capitalize', 'ignite-agency-portfolio' ),
            'uppercase'  => __( 'Uppercase', 'ignite-agency-portfolio' ),
            'lowercase'  => __( 'Lowercase', 'ignite-agency-portfolio' ),
        ),
    ) );

    // Menu Hover Style	
    $wp_customize->add_setting('ignite_agency_portfolio_menus_style',array(
        'default' => '',
        'sanitize_callback' => 'ignite_agency_portfolio_sanitize_choices'
	));
	$wp_customize->add_control('ignite_agency_portfolio_menus_style',array(
        'type' => 'select',
		'label' => __('Menu Hover Style','ignite-agency-portfolio'),
		'section' => 'ignite_agency_portfolio_header_section_settings',
		'choices' => array(
         'None' => __('None','ignite-agency-portfolio'),
         'Zoom In' => __('Zoom In','ignite-agency-portfolio'),
      ),
	));

    $wp_customize->add_setting(
        'ignite_agency_portfolio_header_settings_upgraded_features',
        array(
            'sanitize_callback' => 'sanitize_text_field'
        )
    );
    $wp_customize->add_control(
        'ignite_agency_portfolio_header_settings_upgraded_features',
        array(
            'type'=> 'hidden',
            'description' => "
                <div class='notice-pro-features'>
                    <div class='notice-pro-icon'>
                        <i class='fas fa-crown'></i>
                    </div>
                    <div class='notice-pro-content'>
                        <h3>Unlock Premium Features</h3>
                        <p>Enhance your website with advanced layouts, premium sections, and powerful customization tools.</p>
                    </div>
                    <div class='notice-pro-button'>
                        <a target='_blank' href='". esc_url(IGNITE_AGENCY_PORTFOLIO_URL) ."' class='notice-upgrade-btn'>
                            Upgrade to Pro<i class='fas fa-rocket'></i>
                        </a>
                    </div>
                </div>
            ",
            'section' => 'ignite_agency_portfolio_header_section_settings'
        )
    );

    /** Home Page Settings */
    $wp_customize->add_panel(
        'ignite_agency_portfolio_home_page_settings',
         array(
            'priority' => 9,
            'capability' => 'edit_theme_options',
            'title' => esc_html__( 'Home Page Settings', 'ignite-agency-portfolio' ),
            'description' => esc_html__( 'Customize Home Page Settings', 'ignite-agency-portfolio' ),
        ) 
    );

    $wp_customize->add_section(
        'ignite_agency_portfolio_banner_section_settings',
        array(
            'title' => esc_html__( 'Banner Settings', 'ignite-agency-portfolio' ),
            'priority' => 30,
            'capability' => 'edit_theme_options',
            'panel' => 'ignite_agency_portfolio_home_page_settings',
        )
    );
    /** Banner Section Settings */
    $wp_customize->add_section(
        'ignite_agency_portfolio_banner_section_settings',
        array(
            'title' => esc_html__( 'Banner Section Settings', 'ignite-agency-portfolio' ),
            'priority' => 30,
            'capability' => 'edit_theme_options',
            'panel' => 'ignite_agency_portfolio_home_page_settings',
        )
    );

    /** Banner Section control */
    $wp_customize->add_setting( 
        'ignite_agency_portfolio_banner_setting', 
        array(
            'default' => false,
            'sanitize_callback' => 'ignite_agency_portfolio_sanitize_checkbox',
        ) 
    );

    $wp_customize->add_control(
        'ignite_agency_portfolio_banner_setting',
        array(
            'label'       => __( 'Show Banner', 'ignite-agency-portfolio' ),
            'section'     => 'ignite_agency_portfolio_banner_section_settings',
            'type'        => 'checkbox',
        )
    );

    $wp_customize->add_setting('ignite_agency_portfolio_head_info_image', array(
        'default' => '',
        'transport' => 'refresh',
        'sanitize_callback' => 'esc_url_raw', 
    ));

    $wp_customize->add_control(new WP_Customize_Image_Control($wp_customize, 'ignite_agency_portfolio_head_info_image', array(
        'label' => __('Let\'s Talk Image', 'ignite-agency-portfolio'),
        'section' => 'ignite_agency_portfolio_banner_section_settings',
        'settings' => 'ignite_agency_portfolio_head_info_image',
    )));

    // Section Text
    $wp_customize->add_setting('ignite_agency_portfolio_head_info_title', 
        array(
        'default'           => '',
        'type'              => 'theme_mod',
        'capability'        => 'edit_theme_options',    
        'sanitize_callback' => 'sanitize_text_field'
        )
    );

    $wp_customize->add_control('ignite_agency_portfolio_head_info_title', 
        array(
        'label'       => __('Let\'s Talk Title', 'ignite-agency-portfolio'),
        'section'     => 'ignite_agency_portfolio_banner_section_settings',   
        'settings'    => 'ignite_agency_portfolio_head_info_title',
        'type'        => 'text'
        )
    );

    $wp_customize->add_setting('ignite_agency_portfolio_head_info_content', 
        array(
        'default'           => '',
        'type'              => 'theme_mod',
        'capability'        => 'edit_theme_options',    
        'sanitize_callback' => 'sanitize_text_field'
        )
    );

    $wp_customize->add_control('ignite_agency_portfolio_head_info_content', 
        array(
        'label'       => __('Let\'s Talk Heading', 'ignite-agency-portfolio'),
        'section'     => 'ignite_agency_portfolio_banner_section_settings',   
        'settings'    => 'ignite_agency_portfolio_head_info_content',
        'type'        => 'text'
        )
    );

    // banner button Text
    $wp_customize->add_setting('ignite_agency_portfolio_head_info_btn_text', 
        array(
        'default'           => '',
        'type'              => 'theme_mod',
        'capability'        => 'edit_theme_options',    
        'sanitize_callback' => 'sanitize_text_field'
        )
    );

    $wp_customize->add_control('ignite_agency_portfolio_head_info_btn_text', 
        array(
        'label'       => __('Let\'s Talk Button Text', 'ignite-agency-portfolio'),
        'section'     => 'ignite_agency_portfolio_banner_section_settings',   
        'settings'    => 'ignite_agency_portfolio_head_info_btn_text',
        'type'        => 'text'
        )
    );

    // banner button Url
    $wp_customize->add_setting('ignite_agency_portfolio_head_info_btn_url', 
        array(
        'default'           => '',
        'type'              => 'theme_mod',
        'capability'        => 'edit_theme_options',    
        'sanitize_callback' => 'esc_url_raw'
        )
    );

    $wp_customize->add_control('ignite_agency_portfolio_head_info_btn_url', 
        array(
        'label'       => __('Let\'s Talk Button URL', 'ignite-agency-portfolio'),
        'section'     => 'ignite_agency_portfolio_banner_section_settings',   
        'settings'    => 'ignite_agency_portfolio_head_info_btn_url',
        'type'        => 'url'
        )
    );

        // Section Text
    $wp_customize->add_setting('ignite_agency_portfolio_idea_title', 
        array(
        'default'           => '',
        'type'              => 'theme_mod',
        'capability'        => 'edit_theme_options',    
        'sanitize_callback' => 'sanitize_text_field'
        )
    );

    $wp_customize->add_control('ignite_agency_portfolio_idea_title', 
        array(
        'label'       => __('Idea Title', 'ignite-agency-portfolio'),
        'section'     => 'ignite_agency_portfolio_banner_section_settings',   
        'settings'    => 'ignite_agency_portfolio_idea_title',
        'type'        => 'text'
        )
    );

    $wp_customize->add_setting('ignite_agency_portfolio_idea_content', 
        array(
        'default'           => '',
        'type'              => 'theme_mod',
        'capability'        => 'edit_theme_options',    
        'sanitize_callback' => 'sanitize_text_field'
        )
    );

    $wp_customize->add_control('ignite_agency_portfolio_idea_content', 
        array(
        'label'       => __('Idea Content', 'ignite-agency-portfolio'),
        'section'     => 'ignite_agency_portfolio_banner_section_settings',   
        'settings'    => 'ignite_agency_portfolio_idea_content',
        'type'        => 'text'
        )
    );

    $wp_customize->add_setting('ignite_agency_portfolio_banner_first_title', array(
        'default'           => '',
        'sanitize_callback' => 'sanitize_text_field',
    ));
    $wp_customize->add_control('ignite_agency_portfolio_banner_first_title', array(
        'label'    => __('Banner First Title ( 1 word only )', 'ignite-agency-portfolio'),
        'section'  => 'ignite_agency_portfolio_banner_section_settings',
        'type'     => 'text',
    ));

    $wp_customize->add_setting('ignite_agency_portfolio_right_image_box_3_count', array(
        'default'           => '',
        'sanitize_callback' => 'sanitize_text_field',
    ));
    $wp_customize->add_control('ignite_agency_portfolio_right_image_box_3_count', array(
        'label'    => __('Review Count', 'ignite-agency-portfolio'),
        'section'  => 'ignite_agency_portfolio_banner_section_settings',
        'type'     => 'text',
        'input_attrs' => array(
            'placeholder' => __( '4.8k Plus', 'ignite-agency-portfolio' ),
        ),
    ));

    $wp_customize->add_setting('ignite_agency_portfolio_right_image_box_3_text', array(
        'default'           => '',
        'sanitize_callback' => 'sanitize_text_field',
    ));
    $wp_customize->add_control('ignite_agency_portfolio_right_image_box_3_text', array(
        'label'    => __('Review Text ( 2 words only )', 'ignite-agency-portfolio'),
        'section'  => 'ignite_agency_portfolio_banner_section_settings',
        'type'     => 'text',
    ));

    $wp_customize->add_setting('ignite_agency_portfolio_slider_team_image_1',
        array(
            'sanitize_callback' => 'esc_url_raw',
        )
    );
    $wp_customize->add_control(
        new WP_Customize_Image_Control( $wp_customize,'ignite_agency_portfolio_slider_team_image_1',
            array(
                'label' => __('Review Image 1','ignite-agency-portfolio'),
                'section' => 'ignite_agency_portfolio_banner_section_settings',
                'settings' => 'ignite_agency_portfolio_slider_team_image_1',
            )
        )
    );

    $wp_customize->add_setting('ignite_agency_portfolio_slider_team_image_2',
        array(
            'sanitize_callback' => 'esc_url_raw',
        )
    );
    $wp_customize->add_control(
        new WP_Customize_Image_Control( $wp_customize,'ignite_agency_portfolio_slider_team_image_2',
            array(
                'label' => __('Review Image 2','ignite-agency-portfolio'),
                'section' => 'ignite_agency_portfolio_banner_section_settings',
                'settings' => 'ignite_agency_portfolio_slider_team_image_2',
            )
        )
    );

    $wp_customize->add_setting('ignite_agency_portfolio_slider_team_image_3',
        array(
            'sanitize_callback' => 'esc_url_raw',
        )
    );
    $wp_customize->add_control(
        new WP_Customize_Image_Control( $wp_customize,'ignite_agency_portfolio_slider_team_image_3',
            array(
                'label' => __('Review Image 3','ignite-agency-portfolio'),
                'section' => 'ignite_agency_portfolio_banner_section_settings',
                'settings' => 'ignite_agency_portfolio_slider_team_image_3',
            )
        )
    );

    $wp_customize->add_setting('ignite_agency_portfolio_slider_team_image_4',
        array(
            'sanitize_callback' => 'esc_url_raw',
        )
    );
    $wp_customize->add_control(
        new WP_Customize_Image_Control( $wp_customize,'ignite_agency_portfolio_slider_team_image_4',
            array(
                'label' => __('Review Image 4','ignite-agency-portfolio'),
                'section' => 'ignite_agency_portfolio_banner_section_settings',
                'settings' => 'ignite_agency_portfolio_slider_team_image_4',
            )
        )
    );

    $wp_customize->add_setting('ignite_agency_portfolio_banner_second_title', array(
        'default'           => '',
        'sanitize_callback' => 'sanitize_text_field',
    ));
    $wp_customize->add_control('ignite_agency_portfolio_banner_second_title', array(
        'label'    => __('Banner Second Title', 'ignite-agency-portfolio'),
        'section'  => 'ignite_agency_portfolio_banner_section_settings',
        'type'     => 'text',
    ));

    $wp_customize->add_setting('ignite_agency_portfolio_banner_content', 
        array(
        'default'           => '',
        'type'              => 'theme_mod',
        'capability'        => 'edit_theme_options',    
        'sanitize_callback' => 'sanitize_text_field'
        )
    );

    $wp_customize->add_control('ignite_agency_portfolio_banner_content', 
        array(
        'label'       => __('Banner Content', 'ignite-agency-portfolio'),
        'section'     => 'ignite_agency_portfolio_banner_section_settings',   
        'settings'    => 'ignite_agency_portfolio_banner_content',
        'type'        => 'text'
        )
    );


    $wp_customize->add_setting('ignite_agency_portfolio_banner_background_image', array(
        'default'           => get_template_directory_uri() . '/images/banner.png',
        'sanitize_callback' => 'esc_url_raw',
    ));

    $wp_customize->add_control(new WP_Customize_Image_Control($wp_customize, 'ignite_agency_portfolio_banner_background_image', array(
        'label' => __('Banner Background Image', 'ignite-agency-portfolio'),
        'section' => 'ignite_agency_portfolio_banner_section_settings',
        'settings' => 'ignite_agency_portfolio_banner_background_image',
    )));

    $wp_customize->add_setting(
        'ignite_agency_portfolio_banner_settings_upgraded_features',
        array(
            'sanitize_callback' => 'sanitize_text_field'
        )
    );
    $wp_customize->add_control(
        'ignite_agency_portfolio_banner_settings_upgraded_features',
        array(
            'type'=> 'hidden',
            'description' => "
                <div class='notice-pro-features'>
                    <div class='notice-pro-icon'>
                        <i class='fas fa-crown'></i>
                    </div>
                    <div class='notice-pro-content'>
                        <h3>Unlock Premium Features</h3>
                        <p>Enhance your website with advanced layouts, premium sections, and powerful customization tools.</p>
                    </div>
                    <div class='notice-pro-button'>
                        <a target='_blank' href='". esc_url(IGNITE_AGENCY_PORTFOLIO_URL) ."' class='notice-upgrade-btn'>
                            Upgrade to Pro<i class='fas fa-rocket'></i>
                        </a>
                    </div>
                </div>
            ",
            'section' => 'ignite_agency_portfolio_banner_section_settings'
        )
    );

   /** About Section Start */

   $wp_customize->add_section(
        'ignite_agency_portfolio_classes_section_settings',
        array(
            'title' => esc_html__( 'About Section Settings', 'ignite-agency-portfolio' ),
            'priority' => 30,
            'capability' => 'edit_theme_options',
            'panel' => 'ignite_agency_portfolio_home_page_settings',
        )
    );

    $wp_customize->add_setting( 
        'ignite_agency_portfolio_about_setting', 
        array(
            'default' => false,
            'sanitize_callback' => 'ignite_agency_portfolio_sanitize_checkbox',
        ) 
    );

    $wp_customize->add_control(
        'ignite_agency_portfolio_about_setting',
        array(
            'label'       => __( 'Show About Section', 'ignite-agency-portfolio' ),
            'section'     => 'ignite_agency_portfolio_classes_section_settings',
            'type'        => 'checkbox',
        )
    );

    // Section Text
    $wp_customize->add_setting('ignite_agency_portfolio_about_text_extra', 
        array(
        'default'           => '',
        'type'              => 'theme_mod',
        'capability'        => 'edit_theme_options',    
        'sanitize_callback' => 'sanitize_text_field'
        )
    );

    $wp_customize->add_control('ignite_agency_portfolio_about_text_extra', 
        array(
        'label'       => __('Section Sub Title', 'ignite-agency-portfolio'),
        'section'     => 'ignite_agency_portfolio_classes_section_settings',   
        'settings'    => 'ignite_agency_portfolio_about_text_extra',
        'type'        => 'text'
        )
    );

    // Section Text
    $wp_customize->add_setting('ignite_agency_portfolio_about_title', 
        array(
        'default'           => '',
        'type'              => 'theme_mod',
        'capability'        => 'edit_theme_options',    
        'sanitize_callback' => 'sanitize_text_field'
        )
    );

    $wp_customize->add_control('ignite_agency_portfolio_about_title', 
        array(
        'label'       => __('Section Title', 'ignite-agency-portfolio'),
        'section'     => 'ignite_agency_portfolio_classes_section_settings',   
        'settings'    => 'ignite_agency_portfolio_about_title',
        'type'        => 'text'
        )
    );

    $wp_customize->add_setting('ignite_agency_portfolio_about_content', 
        array(
        'default'           => '',
        'type'              => 'theme_mod',
        'capability'        => 'edit_theme_options',    
        'sanitize_callback' => 'sanitize_text_field'
        )
    );

    $wp_customize->add_control('ignite_agency_portfolio_about_content', 
        array(
        'label'       => __('Section Content', 'ignite-agency-portfolio'),
        'section'     => 'ignite_agency_portfolio_classes_section_settings',   
        'settings'    => 'ignite_agency_portfolio_about_content',
        'type'        => 'text'
        )
    );

    $wp_customize->add_setting('ignite_agency_portfolio_about_icon', array(
        'default' => 'fas fa-square-check',
        'sanitize_callback' => 'sanitize_text_field'
    ));

    $wp_customize->add_control(new Ignite_Agency_Portfolio_Changeable_Icon(
        $wp_customize,
        'ignite_agency_portfolio_about_icon',
        array(
            'label'   => __('Icon', 'ignite-agency-portfolio'),
            'section' => 'ignite_agency_portfolio_classes_section_settings',
            'type'    => 'icon'
        )
    ));

    for ( $ignite_agency_portfolio_i = 1; $ignite_agency_portfolio_i <= 6; $ignite_agency_portfolio_i++ ) {

        $wp_customize->add_setting( 'ignite_agency_portfolio_icon_text_' . $ignite_agency_portfolio_i, array(
            'default'           => '',
            'type'              => 'theme_mod',
            'capability'        => 'edit_theme_options',
            'sanitize_callback' => 'sanitize_text_field',
        ) );

        $wp_customize->add_control( 'ignite_agency_portfolio_icon_text_' . $ignite_agency_portfolio_i, array(
            /* translators: %d: Icon number. */
            'label'    => sprintf( __('Icon Text %d', 'ignite-agency-portfolio'), $ignite_agency_portfolio_i ),
            'section'  => 'ignite_agency_portfolio_classes_section_settings',
            'type'     => 'text',
        ) );
    }

    // about button Text
    $wp_customize->add_setting('ignite_agency_portfolio_about_btn_text', 
        array(
        'default'           => '',
        'type'              => 'theme_mod',
        'capability'        => 'edit_theme_options',    
        'sanitize_callback' => 'sanitize_text_field'
        )
    );

    $wp_customize->add_control('ignite_agency_portfolio_about_btn_text', 
        array(
        'label'       => __('Button Text', 'ignite-agency-portfolio'),
        'section'     => 'ignite_agency_portfolio_classes_section_settings',   
        'settings'    => 'ignite_agency_portfolio_about_btn_text',
        'type'        => 'text'
        )
    );

    // about button Url
    $wp_customize->add_setting('ignite_agency_portfolio_about_btn_url', 
        array(
        'default'           => '',
        'type'              => 'theme_mod',
        'capability'        => 'edit_theme_options',    
        'sanitize_callback' => 'esc_url_raw'
        )
    );

    $wp_customize->add_control('ignite_agency_portfolio_about_btn_url', 
        array(
        'label'       => __('Button URL', 'ignite-agency-portfolio'),
        'section'     => 'ignite_agency_portfolio_classes_section_settings',   
        'settings'    => 'ignite_agency_portfolio_about_btn_url',
        'type'        => 'url'
        )
    );

    $wp_customize->add_setting('ignite_agency_portfolio_about_expereince_count', array(
        'default'           => '',
        'sanitize_callback' => 'sanitize_text_field',
    ));
    $wp_customize->add_control('ignite_agency_portfolio_about_expereince_count', array(
        'label'    => __('Years Of Experience Count', 'ignite-agency-portfolio'),
        'section'  => 'ignite_agency_portfolio_classes_section_settings',
        'type'     => 'text',
        'input_attrs' => array(
            'placeholder' => __( '15+', 'ignite-agency-portfolio' ),
        ),
    ));

    $wp_customize->add_setting('ignite_agency_portfolio_about_expereince_text', array(
        'default'           => '',
        'sanitize_callback' => 'sanitize_text_field',
    ));
    $wp_customize->add_control('ignite_agency_portfolio_about_expereince_text', array(
        'label'    => __('Experience Text ( 3 words only )', 'ignite-agency-portfolio'),
        'section'  => 'ignite_agency_portfolio_classes_section_settings',
        'type'     => 'text',
    ));

    $wp_customize->add_setting('ignite_agency_portfolio_about_image', array(
        'default' => '',
        'transport' => 'refresh',
        'sanitize_callback' => 'esc_url_raw', 
    ));

    $wp_customize->add_control(new WP_Customize_Image_Control($wp_customize, 'ignite_agency_portfolio_about_image', array(
        'label' => __('Add Image', 'ignite-agency-portfolio'),
        'section' => 'ignite_agency_portfolio_classes_section_settings',
        'settings' => 'ignite_agency_portfolio_about_image',
    )));

    $wp_customize->add_setting(
        'ignite_agency_portfolio_classes_settings_upgraded_features',
        array(
            'sanitize_callback' => 'sanitize_text_field'
        )
    );
    $wp_customize->add_control(
        'ignite_agency_portfolio_classes_settings_upgraded_features',
        array(
            'type'=> 'hidden',
            'description' => "
                <div class='notice-pro-features'>
                    <div class='notice-pro-icon'>
                        <i class='fas fa-crown'></i>
                    </div>
                    <div class='notice-pro-content'>
                        <h3>Unlock Premium Features</h3>
                        <p>Enhance your website with advanced layouts, premium sections, and powerful customization tools.</p>
                    </div>
                    <div class='notice-pro-button'>
                        <a target='_blank' href='". esc_url(IGNITE_AGENCY_PORTFOLIO_URL) ."' class='notice-upgrade-btn'>
                            Upgrade to Pro<i class='fas fa-rocket'></i>
                        </a>
                    </div>
                </div>
            ",
            'section' => 'ignite_agency_portfolio_classes_section_settings'
        )
    );

    /** Choose Section End */

    /** Home Page Settings Ends */
    
    /** Footer Section */
    $wp_customize->add_section(
        'ignite_agency_portfolio_footer_section',
        array(
            'title' => __( 'Footer Settings', 'ignite-agency-portfolio' ),
            'priority' => 70,
            'panel' => 'ignite_agency_portfolio_home_page_settings',
        )
    );
    
    /** Copyright Text */
    $wp_customize->add_setting(
        'ignite_agency_portfolio_footer_copyright_text',
        array(
            'default' => '',
            'sanitize_callback' => 'sanitize_text_field',
        )
    );
    
    $wp_customize->add_control(
        'ignite_agency_portfolio_footer_copyright_text',
        array(
            'label' => __( 'Copyright Info', 'ignite-agency-portfolio' ),
            'section' => 'ignite_agency_portfolio_footer_section',
            'type' => 'text',
        )
    );  
    $wp_customize->add_setting('ignite_agency_portfolio_footer_background_image',
        array(
        'default' => '',
        'capability' => 'edit_theme_options',
        'sanitize_callback' => 'absint',
        )
    );


    $wp_customize->add_control(
         new WP_Customize_Cropped_Image_Control($wp_customize, 'ignite_agency_portfolio_footer_background_image',
            array(
                'label' => esc_html__('Footer Background Image', 'ignite-agency-portfolio'),
                /* translators: 1: image width in pixels, 2: image height in pixels */
                'description' => sprintf(esc_html__('Recommended Size %1$s px X %2$s px', 'ignite-agency-portfolio'), 1024, 800),
                'section' => 'ignite_agency_portfolio_footer_section',
                'width' => 1024,
                'height' => 800,
                'flex_width' => true,
                'flex_height' => true,
            )
        )
    );

    /** Footer Background Image Attachment */
    $wp_customize->add_setting('ignite_agency_portfolio_background_attachment', array(
        'default'           => 'scroll',
        'sanitize_callback' => 'ignite_agency_portfolio_sanitize_choices',
    ));

    $wp_customize->add_control('ignite_agency_portfolio_background_attachment', array(
        'label'    => __('Footer Background Attachment', 'ignite-agency-portfolio'),
        'section'  => 'ignite_agency_portfolio_footer_section',
        'settings' => 'ignite_agency_portfolio_background_attachment',
        'type'     => 'select',
        'choices'  => array(
            'fixed' => __('fixed','ignite-agency-portfolio'),
            'scroll' => __('scroll','ignite-agency-portfolio'),
        ),
    ));

    /* Footer Background Color*/
    $wp_customize->add_setting(
        'ignite_agency_portfolio_footer_background_color',
        array(
            'default' => '#000',
            'sanitize_callback' => 'sanitize_hex_color',
        )
    );
    $wp_customize->add_control(
        new WP_Customize_Color_Control(
            $wp_customize,
            'ignite_agency_portfolio_footer_background_color',
            array(
                'label' => __('Footer Widget Area Background Color', 'ignite-agency-portfolio'),
                'section' => 'ignite_agency_portfolio_footer_section',
                'type' => 'color',
            )
        )
    );

     $wp_customize->add_setting('ignite_agency_portfolio_scroll_icon',array(
        'default'   => 'fas fa-arrow-up',
        'sanitize_callback' => 'sanitize_text_field'
    ));
    $wp_customize->add_control(new Ignite_Agency_Portfolio_Changeable_Icon(
        $wp_customize,'ignite_agency_portfolio_scroll_icon',array(
        'label' => __('Scroll Top Icon','ignite-agency-portfolio'),
        'transport' => 'refresh',
        'section'   => 'ignite_agency_portfolio_footer_section',
        'type'      => 'icon'
    )));

    /** Scroll to top button shape */
    $wp_customize->add_setting('ignite_agency_portfolio_scroll_to_top_radius', array(
        'default'           => 'curved-box',
        'sanitize_callback' => 'ignite_agency_portfolio_sanitize_choices',
    ));

    $wp_customize->add_control('ignite_agency_portfolio_scroll_to_top_radius', array(
        'label'    => __('Scroll Top Button Shape', 'ignite-agency-portfolio'),
        'section'  => 'ignite_agency_portfolio_footer_section',
        'settings' => 'ignite_agency_portfolio_scroll_to_top_radius',
        'type'     => 'select',
        'choices'  => array(
            'box'        => __( 'Box', 'ignite-agency-portfolio' ),
            'curved-box' => __( 'Curved Box', 'ignite-agency-portfolio' ),
            'circle'     => __( 'Circle', 'ignite-agency-portfolio' ),
        ),
    ));

    $wp_customize->add_setting(
        'ignite_agency_portfolio_footer_settings_upgraded_features',
        array(
            'sanitize_callback' => 'sanitize_text_field'
        )
    );
    $wp_customize->add_control(
        'ignite_agency_portfolio_footer_settings_upgraded_features',
        array(
            'type'=> 'hidden',
            'description' => "
                <div class='notice-pro-features'>
                    <div class='notice-pro-icon'>
                        <i class='fas fa-crown'></i>
                    </div>
                    <div class='notice-pro-content'>
                        <h3>Unlock Premium Features</h3>
                        <p>Enhance your website with advanced layouts, premium sections, and powerful customization tools.</p>
                    </div>
                    <div class='notice-pro-button'>
                        <a target='_blank' href='". esc_url(IGNITE_AGENCY_PORTFOLIO_URL) ."' class='notice-upgrade-btn'>
                            Upgrade to Pro<i class='fas fa-rocket'></i>
                        </a>
                    </div>
                </div>
            ",
            'section' => 'ignite_agency_portfolio_footer_section'
        )
    );

    // 404 PAGE SETTINGS
    $wp_customize->add_section(
        'ignite_agency_portfolio_404_section',
        array(
            'title' => __( '404 Page Settings', 'ignite-agency-portfolio' ),
            'priority' => 70,
            'panel' => 'ignite_agency_portfolio_general_settings',
        )
    );
   
    $wp_customize->add_setting('404_page_image', array(
        'default' => '',
        'transport' => 'refresh',
        'sanitize_callback' => 'esc_url_raw', // Sanitize as URL
    ));

    $wp_customize->add_control(new WP_Customize_Image_Control($wp_customize, '404_page_image', array(
        'label' => __('404 Page Image', 'ignite-agency-portfolio'),
        'section' => 'ignite_agency_portfolio_404_section',
        'settings' => '404_page_image',
    )));

    $wp_customize->add_setting('404_pagefirst_header', array(
        'default' => __('404', 'ignite-agency-portfolio'),
        'transport' => 'refresh',
        'sanitize_callback' => 'sanitize_text_field', // Sanitize as text field
    ));

    $wp_customize->add_control('404_pagefirst_header', array(
        'type' => 'text',
        'label' => __('Heading', 'ignite-agency-portfolio'),
        'section' => 'ignite_agency_portfolio_404_section',
    ));

    // Setting for 404 page header
    $wp_customize->add_setting('404_page_header', array(
        'default' => __('Sorry, that page can\'t be found!', 'ignite-agency-portfolio'),
        'transport' => 'refresh',
        'sanitize_callback' => 'sanitize_text_field', // Sanitize as text field
    ));

    $wp_customize->add_control('404_page_header', array(
        'type' => 'text',
        'label' => __('Heading', 'ignite-agency-portfolio'),
        'section' => 'ignite_agency_portfolio_404_section',
    ));

}
add_action( 'customize_register', 'ignite_agency_portfolio_customize_register' );
endif;

/**
 * Binds JS handlers to make Theme Customizer preview reload changes asynchronously.
 */
function ignite_agency_portfolio_customize_preview_js() {
    // Use minified libraries if SCRIPT_DEBUG is false
    $ignite_agency_portfolio_build  = ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) ? '/build' : '';
    $ignite_agency_portfolio_suffix = ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) ? '' : '.min';
	wp_enqueue_script( 'ignite_agency_portfolio_customizer', get_template_directory_uri() . '/js' . $ignite_agency_portfolio_build . '/customizer' . $ignite_agency_portfolio_suffix . '.js', array( 'customize-preview' ), '20130508', true );
}
add_action( 'customize_preview_init', 'ignite_agency_portfolio_customize_preview_js' );