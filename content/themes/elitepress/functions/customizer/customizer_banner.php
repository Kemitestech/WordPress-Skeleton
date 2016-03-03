<?php 

// Adding customizer home page settings

function elitepress_banner_page_customizer( $wp_customize ){

	/* Home Page Panel */
	$wp_customize->add_panel( 'banner_page', array(
		'priority'       => 900,
		'capability'     => 'edit_theme_options',
		'title'      => __('Banner Setting', 'elitepress'),
	) );
	
	/* Category Banner */
	$wp_customize->add_section( 'category_banner' , array(
		'title'      => __('Banner Configuration For Category Template', 'elitepress'),
		'panel'  => 'banner_page',
   	) );
	

	// Banner Configuration For Category Template
	$wp_customize->add_setting(
    'elitepress_lite_options[banner_title_category]',
    array(
        'default' => __('Category Title','elitepress'),
		'sanitize_callback' => 'sanitize_text_field',
		'type' => 'option',
    )
	
	);
	$wp_customize->add_control(
    'elitepress_lite_options[banner_title_category]',
    array(
        'label' => __('Category Banner Tagline One','elitepress'),
        'section' => 'category_banner',
        'type' => 'text',
    )
	);

	
	$wp_customize->add_setting(
    'elitepress_lite_options[banner_description_category]',
    array(
        'default' => __(' Autem vel eum iriure dolor in hendrerit in vulputate velit esse molestie consequat, vel illum dolore eu feugiat nulla facilisis at vero eros et dolore feugait.','elitepress'),
		'sanitize_callback' => 'sanitize_text_field',
		'type' => 'option',
    )
	
	);
	$wp_customize->add_control(
    'elitepress_lite_options[banner_description_category]',
    array(
        'label' => __('Category Banner Description','elitepress'),
        'section' => 'category_banner',
        'type' => 'text',
    )
	);
	
	
	/* Archive Banner */
	$wp_customize->add_section( 'archive_banner' , array(
		'title'      => __('Banner Configuration For Archive Template', 'elitepress'),
		'panel'  => 'banner_page',
   	) );
	

	// Banner Configuration For Archive Template
	$wp_customize->add_setting(
    'elitepress_lite_options[banner_title_archive]',
    array(
        'default' => __('Archive Title','elitepress'),
		'sanitize_callback' => 'sanitize_text_field',
		'type' => 'option',
    )
	
	);
	$wp_customize->add_control(
    'elitepress_lite_options[banner_title_archive]',
    array(
        'label' => __('Category Banner Tagline One','elitepress'),
        'section' => 'archive_banner',
        'type' => 'text',
    )
	);

	
	$wp_customize->add_setting(
    'elitepress_lite_options[banner_description_archive]',
    array(
        'default' => __(' Autem vel eum iriure dolor in hendrerit in vulputate velit esse molestie consequat, vel illum dolore eu feugiat nulla facilisis at vero eros et dolore feugait.','elitepress'),
		'sanitize_callback' => 'sanitize_text_field',
		'type' => 'option',
    )
	
	);
	$wp_customize->add_control(
    'elitepress_lite_options[banner_description_archive]',
    array(
        'label' => __('Archive Banner Description','elitepress'),
        'section' => 'archive_banner',
        'type' => 'text',
    )
	);
	
	
	/* Author Banner */
	$wp_customize->add_section( 'author_banner' , array(
		'title'      => __('Banner Configuration For Author Template', 'elitepress'),
		'panel'  => 'banner_page',
   	) );
	

	// Banner Configuration For Archive Template
	$wp_customize->add_setting(
    'elitepress_lite_options[banner_title_author]',
    array(
        'default' => __('Author Title','elitepress'),
		'sanitize_callback' => 'sanitize_text_field',
		'type' => 'option',
    )
	
	);
	$wp_customize->add_control(
    'elitepress_lite_options[banner_title_author]',
    array(
        'label' => __('Author Title','elitepress'),
        'section' => 'author_banner',
        'type' => 'text',
    )
	);

	
	$wp_customize->add_setting(
    'elitepress_lite_options[banner_description_author]',
    array(
        'default' => __(' Autem vel eum iriure dolor in hendrerit in vulputate velit esse molestie consequat, vel illum dolore eu feugiat nulla facilisis at vero eros et dolore feugait.','elitepress'),
		'sanitize_callback' => 'sanitize_text_field',
		'type' => 'option',
    )
	
	);
	$wp_customize->add_control(
    'elitepress_lite_options[banner_description_author]',
    array(
        'label' => __('Author Banner Description','elitepress'),
        'section' => 'author_banner',
        'type' => 'text',
    )
	);
	
	
	/* 404 page Banner */
	$wp_customize->add_section( 'banner_404_banner' , array(
		'title'      => __('Banner Configuration For 404 Template', 'elitepress'),
		'panel'  => 'banner_page',
   	) );
	

	// Banner Configuration For 404 Template
	$wp_customize->add_setting(
    'elitepress_lite_options[banner_title_404]',
    array(
        'default' => __('404 Title','elitepress'),
		'sanitize_callback' => 'sanitize_text_field',
		'type' => 'option',
    )
	
	);
	$wp_customize->add_control(
    'elitepress_lite_options[banner_title_404]',
    array(
        'label' => __('404 Banner Tagline One','elitepress'),
        'section' => 'banner_404_banner',
        'type' => 'text',
    )
	);

	
	$wp_customize->add_setting(
    'elitepress_lite_options[banner_description_404]',
    array(
        'default' => __(' Autem vel eum iriure dolor in hendrerit in vulputate velit esse molestie consequat, vel illum dolore eu feugiat nulla facilisis at vero eros et dolore feugait.','elitepress'),
		'sanitize_callback' => 'sanitize_text_field',
		'type' => 'option',
    )
	
	);
	$wp_customize->add_control(
    'elitepress_lite_options[banner_description_404]',
    array(
        'label' => __('404 Banner Description','elitepress'),
        'section' => 'banner_404_banner',
        'type' => 'text',
    )
	);
	
	
	///* Tag Banner */
	$wp_customize->add_section( 'tag_banner' , array(
		'title'      => __('Banner Configuration For Tag Template', 'elitepress'),
		'panel'  => 'banner_page',
   	) );
	

	// Banner Configuration For Tag Template
	$wp_customize->add_setting(
    'elitepress_lite_options[banner_title_tag]',
    array(
        'default' => __('Tag Title','elitepress'),
		'sanitize_callback' => 'sanitize_text_field',
		'type' => 'option',
    )
	
	);
	$wp_customize->add_control(
    'elitepress_lite_options[banner_title_tag]',
    array(
        'label' => __('Tag Title','elitepress'),
        'section' => 'tag_banner',
        'type' => 'text',
    )
	);

	
	$wp_customize->add_setting(
    'elitepress_lite_options[banner_description_tag]',
    array(
        'default' => __(' Autem vel eum iriure dolor in hendrerit in vulputate velit esse molestie consequat, vel illum dolore eu feugiat nulla facilisis at vero eros et dolore feugait.','elitepress'),
		'sanitize_callback' => 'sanitize_text_field',
		'type' => 'option',
    )
	
	);
	$wp_customize->add_control(
    'elitepress_lite_options[banner_description_tag]',
    array(
        'label' => __('Tag Banner Description','elitepress'),
        'section' => 'tag_banner',
        'type' => 'text',
    )
	);
	
	
	
	
	///* Search Banner */
	$wp_customize->add_section( 'search_banner' , array(
		'title'      => __('Banner Configuration For Search Template', 'elitepress'),
		'panel'  => 'banner_page',
   	) );
	

	// Banner Configuration For Search Template
	$wp_customize->add_setting(
    'elitepress_lite_options[banner_title_search]',
    array(
        'default' => __('Search Title','elitepress'),
		'sanitize_callback' => 'sanitize_text_field',
		'type' => 'option',
    )
	
	);
	$wp_customize->add_control(
    'elitepress_lite_options[banner_title_search]',
    array(
        'label' => __('Search Title','elitepress'),
        'section' => 'search_banner',
        'type' => 'text',
    )
	);

	
	$wp_customize->add_setting(
    'elitepress_lite_options[banner_description_search]',
    array(
        'default' => __(' Autem vel eum iriure dolor in hendrerit in vulputate velit esse molestie consequat, vel illum dolore eu feugiat nulla facilisis at vero eros et dolore feugait.','elitepress'),
		'sanitize_callback' => 'sanitize_text_field',
		'type' => 'option',
    )
	
	);
	$wp_customize->add_control(
    'elitepress_lite_options[banner_description_search]',
    array(
        'label' => __('Search Banner Description','elitepress'),
        'section' => 'search_banner',
        'type' => 'text',
    )
	);
}
add_action( 'customize_register', 'elitepress_banner_page_customizer' );