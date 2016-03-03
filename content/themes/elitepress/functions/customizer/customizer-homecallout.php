<?php 
function elitepress_home_callout_customizer( $wp_customize ){
$wp_customize->add_panel( 'home_callout_setting', array(
		'priority'       => 400,
		'capability'     => 'edit_theme_options',
		'title'      => __('Home callout Settings', 'elitepress'),
	) );
//Home top callout
$wp_customize->add_section(
        'header_home_callout',
        array(
            'title' => 'Header Home callout setting',
           'priority'    => 4,
			'panel' => 'home_callout_setting',
        )
    );
	
//Contact mail and number
$wp_customize->add_setting(
	'elitepress_lite_options[header_call_out_area_enabled]', array(
        'default'        => true,
        'capability'     => 'edit_theme_options',
		'sanitize_callback' => 'sanitize_text_field',
		'type' => 'option',
    ));
	$wp_customize->add_control('elitepress_lite_options[header_call_out_area_enabled]', array(
        'label'   => __('Enable Header top call out area on front page.', 'elitepress'),
        'section' => 'header_home_callout',
        'type'    => 'checkbox',
		'priority'   => 2,
    )); // enable / disable home callout area
	
	//Header Top Call Out Title
	$wp_customize->add_setting(
	'elitepress_lite_options[header_call_out_title]', array(
        'default'        => __('Want to say Hey or find out more?','elitepress'),
        'capability'     => 'edit_theme_options',
		'sanitize_callback' => 'sanitize_text_field',
		'type' => 'option',
    ));
	$wp_customize->add_control('elitepress_lite_options[header_call_out_title]', array(
        'label'   => __('Header Top Call Out Title', 'elitepress'),
        'section' => 'header_home_callout',
        'type'    => 'text',
    ));
	
	
	//Header Top Call Out Description
	$wp_customize->add_setting(
	'elitepress_lite_options[header_call_out_description]', array(
        'default'        => __('Reprehen derit in voluptate velit cillum dolore eu fugiat nulla pariaturs  sint occaecat proidentse.','elitepress'),
        'capability'     => 'edit_theme_options',
		'sanitize_callback' => 'sanitize_text_field',
		'type' => 'option',
    ));
	$wp_customize->add_control('elitepress_lite_options[header_call_out_description]', array(
        'label'   => __('Header Top Call Out Description', 'elitepress'),
        'section' => 'header_home_callout',
        'type'    => 'textarea',
    ));
	
	//Home callout read more button
	$wp_customize->add_setting(
	'elitepress_lite_options[header_call_out_btn_text]', array(
        'default'        => __('Buy It Now!','elitepress'),
        'capability'     => 'edit_theme_options',
		'sanitize_callback' => 'sanitize_text_field',
		'type' => 'option',
    ));
	$wp_customize->add_control('elitepress_lite_options[header_call_out_btn_text]', array(
        'label'   => __('Header Top Call Out Button Text', 'elitepress'),
        'section' => 'header_home_callout',
        'type'    => 'text',
    ));
	
	//Home callout read more button link
	$wp_customize->add_setting(
	'elitepress_lite_options[header_call_out_btn_link]', array(
        'default'        => __('#','elitepress'),
        'capability'     => 'edit_theme_options',
		'sanitize_callback' => 'sanitize_text_field',
		'type' => 'option',
    ));
	$wp_customize->add_control('elitepress_lite_options[header_call_out_btn_link]', array(
        'label'   => __('Header Top Call Out Button Link', 'elitepress'),
        'section' => 'header_home_callout',
        'type'    => 'text',
    ));
	
	//Home callout read more button link target
	$wp_customize->add_setting(
	'elitepress_lite_options[header_call_out_btn_link_target]', array(
        'default'        => true ,
        'capability'     => 'edit_theme_options',
		'sanitize_callback' => 'sanitize_text_field',
		'type' => 'option',
    ));
	$wp_customize->add_control('elitepress_lite_options[header_call_out_btn_link_target]', array(
        'label'   => __('Open link in a new window/tab', 'elitepress'),
        'section' => 'header_home_callout',
        'type'    => 'checkbox',
    ));
	
	}
	add_action( 'customize_register', 'elitepress_home_callout_customizer' );
	?>