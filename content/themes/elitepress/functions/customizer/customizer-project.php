<?php
function elitepress_portfolio_customizer( $wp_customize ) {

//Home portfolio Section
	$wp_customize->add_panel( 'elitepress_portfolio_setting', array(
		'priority'       => 700,
		'capability'     => 'edit_theme_options',
		'title'      => __('portfolio Settings', 'elitepress'),
	) );
	
	$wp_customize->add_section(
        'portfolio_section_settings',
        array(
            'title' => __('Home portfolio Settings','elitepress'),
            'description' => '',
			'panel'  => 'elitepress_portfolio_setting',)
    );
	
	
	//Show and hide portfolio section
	$wp_customize->add_setting(
	'elitepress_lite_options[portfolio_section_enabled]'
    ,
    array(
        'default' => true,
		'capability'     => 'edit_theme_options',
		'sanitize_callback' => 'sanitize_text_field',
		'type' => 'option',
    )	
	);
	$wp_customize->add_control(
    'elitepress_lite_options[portfolio_section_enabled]',
    array(
        'label' => __('Enable portfolio section on home Page','elitepress'),
        'section' => 'portfolio_section_settings',
        'type' => 'checkbox',
    )
	);
	//portfolio one Title
	$wp_customize->add_setting(
	'elitepress_lite_options[front_portfolio_title]', array(
        'default'        => __('Latest Projects','elitepress'),
        'capability'     => 'edit_theme_options',
		'sanitize_callback' => 'sanitize_text_field',
		'type' => 'option',
    ));
    $wp_customize->add_control('elitepress_lite_options[front_portfolio_title]', array(
        'label'   => __('portfolio Title', 'elitepress'),
        'section' => 'portfolio_section_settings',
		'type' => 'text',
    ));
	
	//portfolio Description
	$wp_customize->add_setting(
	'elitepress_lite_options[front_portfolio_description]', array(
        'default'        => __ ('Morbi leo risus, porta ac consectetur vestibulum eros cras mattis consectetur purus sit...','elitepress'),
        'capability'     => 'edit_theme_options',
		'sanitize_callback' => 'sanitize_text_field',
		'type' => 'option',
    ));
    $wp_customize->add_control('elitepress_lite_options[front_portfolio_description]', array(
        'label'   => __('portfolio Description', 'elitepress'),
        'section' => 'portfolio_section_settings',
		'type' => 'textarea',
    ));
	//portfolio One
	$wp_customize->add_section(
        'portfolio_one_section_settings',
        array(
            'title' => __('Home portfolio one','elitepress'),
            'description' => '',
			'panel'  => 'elitepress_portfolio_setting',)
    );
	
	//portfolio one Title
	$wp_customize->add_setting(
	'elitepress_lite_options[portfolio_one_title]', array(
        'default'        => __('Business Growth','elitepress'),
        'capability'     => 'edit_theme_options',
		'sanitize_callback' => 'sanitize_text_field',
		'type' => 'option',
    ));
    $wp_customize->add_control('elitepress_lite_options[portfolio_one_title]', array(
        'label'   => __('portfolio Title One', 'elitepress'),
        'section' => 'portfolio_one_section_settings',
		'type' => 'text',
    ));
	//portfolio one image
	$wp_customize->add_setting( 'elitepress_lite_options[portfolio_one_image]',array('default' => get_template_directory_uri().'/images/portfolio.jpg',
	'type' => 'option','sanitize_callback' => 'esc_url_raw',));
 
	$wp_customize->add_control(
		new WP_Customize_Image_Control(
			$wp_customize,
			'elitepress_lite_options[portfolio_one_image]',
			array(
				'label' => 'portfolio One Thumbnail',
				'section' => 'example_section_one',
				'settings' =>'elitepress_lite_options[portfolio_one_image]',
				'section' => 'portfolio_one_section_settings',
				'type' => 'upload',
			)
		)
	);
	
	//portfolio one Description
	$wp_customize->add_setting(
	'elitepress_lite_options[portfolio_one_description]', array(
        'default'=> __('Morbi leo risus, porta ac consectetur vestibulum eros cras 	mattis consectetur purus sit...','elitepress'),
        'capability'     => 'edit_theme_options',
		'sanitize_callback' => 'sanitize_text_field',
		'type' => 'option',
    ));
    $wp_customize->add_control('elitepress_lite_options[portfolio_one_description]', array(
        'label'   => __('portfolio Description One', 'elitepress'),
        'section' => 'portfolio_one_section_settings',
		'type' => 'textarea',
    ));
	
	
	
	//portfolio Two
	$wp_customize->add_section(
        'portfolio_two_section_settings',
        array(
            'title' => __('Home portfolio two','elitepress'),
            'description' => '',
			'panel'  => 'elitepress_portfolio_setting',)
    );
	
	//portfolio Two Title
	$wp_customize->add_setting(
	'elitepress_lite_options[portfolio_two_title]', array(
        'default'        => __('Functional Beauty','elitepress'),
        'capability'     => 'edit_theme_options',
		'sanitize_callback' => 'sanitize_text_field',
		'type' => 'option',
    ));
    $wp_customize->add_control('elitepress_lite_options[portfolio_two_title]', array(
        'label'   => __('portfolio Title Two', 'elitepress'),
        'section' => 'portfolio_two_section_settings',
		'type' => 'text',
    ));
	
	//portfolio two image
	$wp_customize->add_setting( 'elitepress_lite_options[portfolio_two_image]',array('default' => get_template_directory_uri().'/images/portfolio.jpg','type' => 'option','sanitize_callback' => 'esc_url_raw',));	
	$wp_customize->add_control(
		new WP_Customize_Image_Control(
			$wp_customize,
			'elitepress_lite_options[portfolio_two_image]',
			array(
				'label' => 'portfolio two Thumbnail',
				'section' => 'example_section_one',
				'settings' =>'elitepress_lite_options[portfolio_two_image]',
				'section' => 'portfolio_two_section_settings',
				'type' => 'upload',
			)
		)
	);
	
	//portfolio Two Description
	$wp_customize->add_setting(
	'elitepress_lite_options[portfolio_two_description]', array(
        'default'        => __('Morbi leo risus, porta ac consectetur vestibulum eros cras mattis consectetur purus sit...','elitepress'),
        'capability'     => 'edit_theme_options',
		'sanitize_callback' => 'sanitize_text_field',
		'type' => 'option',
    ));
    $wp_customize->add_control('elitepress_lite_options[portfolio_two_description]', array(
        'label'   => __('portfolio Title Two', 'elitepress'),
        'section' => 'portfolio_two_section_settings',
		'type' => 'textarea',
    ));
	

	//portfolio Three section
	$wp_customize->add_section(
        'portfolio_three_section_settings',
        array(
            'title' => __('Home portfolio Three','elitepress'),
            'description' => '',
			'panel'  => 'elitepress_portfolio_setting',)
    );
	
	
	
	//portfolio Title Title
	$wp_customize->add_setting(
	'elitepress_lite_options[portfolio_three_title]', array(
        'default'        => __('Planning Around','elitepress'),
        'capability'     => 'edit_theme_options',
		'sanitize_callback' => 'sanitize_text_field',
		'type' => 'option',
    ));
    $wp_customize->add_control('elitepress_lite_options[portfolio_three_title]', array(
        'label'   => __('portfolio Title Three', 'elitepress'),
        'section' => 'portfolio_three_section_settings',
		'type' => 'text',
    ));
	
	
	
	//portfolio three image
	$wp_customize->add_setting( 'elitepress_lite_options[portfolio_three_image]',array('default' => get_template_directory_uri().'/images/portfolio.jpg','type' => 'option','sanitize_callback' => 'esc_url_raw',));
 
	$wp_customize->add_control(
		new WP_Customize_Image_Control(
			$wp_customize,
			'elitepress_lite_options[portfolio_three_image]',
			array(
				'label' => 'portfolio Three Thumbnail',
				'section' => 'example_section_one',
				'settings' =>'elitepress_lite_options[portfolio_three_image]',
				'section' => 'portfolio_three_section_settings',
				'type' => 'upload',
			)
		)
	);
	
	//portfolio Three Description
	$wp_customize->add_setting(
	'elitepress_lite_options[portfolio_three_description]', array(
        'default'        => __('Morbi leo risus, porta ac consectetur vestibulum eros cras mattis consectetur purus sit...','elitepress'),
        'capability'     => 'edit_theme_options',
		'sanitize_callback' => 'sanitize_text_field',
		'type' => 'option',
    ));
    $wp_customize->add_control('elitepress_lite_options[portfolio_three_description]', array(
        'label'   => __('portfolio Description Three', 'elitepress'),
        'section' => 'portfolio_three_section_settings',
		'type' => 'textarea',
    ));
	
	
	
	
	$wp_customize->add_section( 'more_portfolio' , array(
		'title'      => __('Add More portfolio', 'elitepress'),
		'panel'  => 'elitepress_portfolio_setting',
		'priority'   => 400,
   	) );
	
	
	
	class WP_portfolio_Customize_Control extends WP_Customize_Control {
    public $type = 'new_menu';
    /**
    * Render the control's content.
    */
    public function render_content() {
    ?>
     <div class="pro-vesrion">
	 <P><?php _e('Want to add more portfolios and categorization than upgrade to pro','elitepress');?></P>
	 </div>
	  <div class="pro-box">
	 <a href="<?php echo esc_url( __('http://webriti.com/elitepress/', 'elitepress'));?>" target="_blank" class="service" id="review_pro"><?php _e( 'UPGRADE TO PRO','elitepress' ); ?></a>
	 <div>
    <?php
    }
}

$wp_customize->add_setting(
    'portfolio_pro',
    array(
        'default' => __('','elitepress'),
		'capability'     => 'edit_theme_options',
		'sanitize_callback' => 'sanitize_text_field',
    )	
);
$wp_customize->add_control( new WP_portfolio_Customize_Control( $wp_customize, 'portfolio_pro', array(	
		'label' => __('Discover elitepress Pro','elitepress'),
        'section' => 'more_portfolio',
		'setting' => 'portfolio_pro',
    ))
);
}		
	add_action( 'customize_register', 'elitepress_portfolio_customizer' );
?>