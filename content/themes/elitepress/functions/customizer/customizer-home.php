<?php
function elitepress_header_customizer( $wp_customize ) {

/* Header Section */
	$wp_customize->add_panel( 'header_options', array(
		'priority'       => 300,
		'capability'     => 'edit_theme_options',
		'title'      => __('Header Settings', 'elitepress'),
	) );
	
	
	/* favicon option */
    $wp_customize->add_section( 'elitepress_favicon' , array(
      'title'       => __( 'Site favicon', 'elitepress' ),
      'priority'    => 300,
      'description' => __( 'Upload a favicon', 'elitepress' ),
	  'panel'  => 'header_options',
    ) );
    
    $wp_customize->add_setting('elitepress_lite_options[upload_image_favicon]', array(
      'sanitize_callback' => 'esc_url_raw',
	   'capability'     => 'edit_theme_options',
	   'type' => 'option', 
    ) );
    
    $wp_customize->add_control( new WP_Customize_Image_Control( $wp_customize, 'elitepress_lite_options[upload_image_favicon]', array(
      'label'    => __( 'Choose your favicon (ideal width and height is 16x16 or 32x32)', 'elitepress' ),
      'section'  => 'elitepress_favicon',
    ) ) );
	
	
	//Header logo setting
	$wp_customize->add_section( 'header_logo' , array(
		'title'      => __('Header Logo setting', 'elitepress'),
		'panel'  => 'header_options',
		'priority'   => 400,
   	) );
	$wp_customize->add_setting(
		'elitepress_lite_options[upload_image_logo]'
		, array(
        'default'        => '',
        'capability'     => 'edit_theme_options',
		'sanitize_callback' => 'esc_url_raw',
		'type' => 'option',
    ));
	
	$wp_customize->add_control(
		   new WP_Customize_Image_Control(
			   $wp_customize,
			   'elitepress_lite_options[upload_image_logo]',
			   array(
				   'label'          => __( 'Upload a 150x150 for Logo Image', 'elitepress' ),
				   'section'        => 'header_logo',
				   'priority'   => 50,
			   )
		   )
	);
	
	//Enable/Disable logo text
	$wp_customize->add_setting(
    'elitepress_lite_options[text_title]',array(
	'default'    => true,
	'sanitize_callback' => 'sanitize_text_field',
	'type' => 'option'
	));

	$wp_customize->add_control(
    'elitepress_lite_options[text_title]',
    array(
        'type' => 'checkbox',
        'label' => __('Enable/Disabe Logo','elitepress'),
        'section' => 'header_logo',
		'priority'   => 100,
    )
	);
	
	
	//Logo width
	
	$wp_customize->add_setting(
    'elitepress_lite_options[width]',array(
	'sanitize_callback' => 'sanitize_text_field',
	'default' => 100,
	'type' => 'option',
	
	));

	$wp_customize->add_control(
    'elitepress_lite_options[width]',
    array(
        'type' => 'text',
        'label' => __('Enter Logo Width','elitepress'),
        'section' => 'header_logo',
		'priority'   => 400,
    )
	);
	
	//Logo Height
	$wp_customize->add_setting(
    'elitepress_lite_options[height]',array(
	'sanitize_callback' => 'sanitize_text_field',
	'default' => 50,
	'type'=>'option',
	
	));

	$wp_customize->add_control(
    'elitepress_lite_options[height]',
    array(
        'type' => 'text',
        'label' => __('Enter Logo Height','elitepress'),
        'section' => 'header_logo',
		'priority'   =>410,
    )
	);
	
	
	
	//Text logo
	$wp_customize->add_setting(
	'elitepress_lite_options[text_title]'
    ,array(
	'default' => true,
	'sanitize_callback' => 'sanitize_text_field',
	'type' =>'option',
	
	));

	$wp_customize->add_control(
    'elitepress_lite_options[text_title]',
    array(
        'type' => 'checkbox',
        'label' => __('Show Logo text','elitepress'),
        'section' => 'header_logo',
		'priority'   => 200,
    )
	);
	
	//Custom css
	$wp_customize->add_section( 'custom_css' , array(
		'title'      => __('Custom css', 'elitepress'),
		'panel'  => 'header_options',
		'priority'   => 100,
   	) );
	$wp_customize->add_setting(
	'elitepress_lite_options[webrit_custom_css]'
		, array(
        'default'        => '',
        'capability'     => 'edit_theme_options',
		'sanitize_callback' => 'sanitize_text_field',
		'type'=> 'option',
    ));
    $wp_customize->add_control( 'elitepress_lite_options[webrit_custom_css]', array(
        'label'   => __('Custom css snippet:', 'elitepress'),
        'section' => 'custom_css',
        'type' => 'textarea',
		'priority'   => 100,
    )); 	
	
	//Header social Icon

	$wp_customize->add_section(
        'header_social_icon',
        array(
            'title' => 'Social Link',
           'priority'    => 300,
			'panel' => 'header_options',
        )
    );
	
	//Show and hide Header Social Icons
	$wp_customize->add_setting(
	'elitepress_lite_options[header_social_media_enabled]'
    ,
    array(
        'default' => true,
		'capability'     => 'edit_theme_options',
		'sanitize_callback' => 'sanitize_text_field',
		'type' => 'option',
    )	
	);
	$wp_customize->add_control(
    'elitepress_lite_options[header_social_media_enabled]',
    array(
        'label' => __('Enable social icons','elitepress'),
        'section' => 'header_social_icon',
        'type' => 'checkbox',
    )
	);
	// Facebook link
	$wp_customize->add_setting(
    'elitepress_lite_options[social_media_facebook_link]',
    array(
        'default' => '#',
		'sanitize_callback' => 'sanitize_text_field',
		'type' => 'option',
    )
	
	);
	$wp_customize->add_control(
    'elitepress_lite_options[social_media_facebook_link]',
    array(
        'label' => __('Facebook URL','elitepress'),
        'section' => 'header_social_icon',
        'type' => 'text',
    )
	);

	$wp_customize->add_setting(
	'elitepress_lite_options[facebook_media_link_target]',array(
	'default' => true,
	'sanitize_callback' => 'sanitize_text_field',
	'type' => 'option',
	));

	$wp_customize->add_control(
    'elitepress_lite_options[facebook_media_link_target]',
    array(
        'type' => 'checkbox',
        'label' => __('Open Link New tab/window','elitepress'),
        'section' => 'header_social_icon',
    )
);

	//twitter link
	
	$wp_customize->add_setting(
    'elitepress_lite_options[social_media_twitter_link]',
    array(
        'default' => '#',
		'type' => 'theme_mod',
		'sanitize_callback' => 'sanitize_text_field',
		'type' => 'option',
    )
	
	);
	$wp_customize->add_control(
    'elitepress_lite_options[social_media_twitter_link]',
    array(
        'label' => __('Twitter URL','elitepress'),
        'section' => 'header_social_icon',
        'type' => 'text',
    )
	);

	$wp_customize->add_setting(
	'elitepress_lite_options[twitter_media_link_target]'
    ,array(
	'default' => true,
	'sanitize_callback' => 'sanitize_text_field',
	'type' => 'option',
	));

	$wp_customize->add_control(
    'elitepress_lite_options[twitter_media_link_target]',
    array(
        'type' => 'checkbox',
        'label' => __('Open Link New tab/window','elitepress'),
        'section' => 'header_social_icon',
    )
);
	//Linkdin link
	
	$wp_customize->add_setting(
	'elitepress_lite_options[social_media_linkedin_link]' ,
    array(
        'default' => '#',
		'sanitize_callback' => 'sanitize_text_field',
		'type' => 'option',
    )
	
	);
	$wp_customize->add_control(
    'elitepress_lite_options[social_media_linkedin_link]',
    array(
        'label' => __('Linkdin URL','elitepress'),
        'section' => 'header_social_icon',
        'type' => 'text',
    )
	);

	$wp_customize->add_setting(
	'elitepress_lite_options[linkedin_media_link_target]'
	,array(
	'default' => true,
	'sanitize_callback' => 'sanitize_text_field',
	'type' => 'option',
	));

	$wp_customize->add_control(
    	'elitepress_lite_options[linkedin_media_link_target]',
    array(
        'type' => 'checkbox',
        'label' => __('Open Link New tab/window','elitepress'),
        'section' => 'header_social_icon',
    )
);
	
	//Google plus
	$wp_customize->add_setting(
    'elitepress_lite_options[social_media_googleplus_link]',
    array(
        'default' => '#',
		'sanitize_callback' => 'sanitize_text_field',
		'type' => 'option',
    )
	
	);
	$wp_customize->add_control(
    'elitepress_lite_options[social_media_googleplus_link]',
    array(
        'label' => __('Google-Plus URL','elitepress'),
        'section' => 'header_social_icon',
        'type' => 'text',
    )
	);

	$wp_customize->add_setting(
	'elitepress_lite_options[google_media_link_target]',array(
	'default' => true,
	'sanitize_callback' => 'sanitize_text_field',
	'type' => 'option',
	));

	$wp_customize->add_control(
    'elitepress_lite_options[google_media_link_target]',
    array(
        'type' => 'checkbox',
        'label' => __('Open Link New tab/window','elitepress'),
        'section' => 'header_social_icon',
    )
);

//Skype
	$wp_customize->add_setting(
    'elitepress_lite_options[social_media_skype_link]',
    array(
        'default' => '#',
		'sanitize_callback' => 'sanitize_text_field',
		'type' => 'option',
    )
	
	);
	$wp_customize->add_control(
    'elitepress_lite_options[social_media_skype_link]',
    array(
        'label' => __('Skype URL','elitepress'),
        'section' => 'header_social_icon',
        'type' => 'text',
    )
	);

	$wp_customize->add_setting(
	'elitepress_lite_options[skype_media_link_target]',array(
	'default' => true,
	'sanitize_callback' => 'sanitize_text_field',
	'type' => 'option',
	));

	$wp_customize->add_control(
    'elitepress_lite_options[skype_media_link_target]',
    array(
        'type' => 'checkbox',
        'label' => __('Open Link New tab/window','elitepress'),
        'section' => 'header_social_icon',
    )
);

//Dribbble
	$wp_customize->add_setting(
    'elitepress_lite_options[social_media_dribbble_link]',
    array(
        'default' => '#',
		'sanitize_callback' => 'sanitize_text_field',
		'type' => 'option',
    )
	
	);
	$wp_customize->add_control(
    'elitepress_lite_options[social_media_dribbble_link]',
    array(
        'label' => __('Dribble URL','elitepress'),
        'section' => 'header_social_icon',
        'type' => 'text',
    )
	);

	$wp_customize->add_setting(
	'elitepress_lite_options[dribble_media_link_target]',array(
	'default' => true,
	'sanitize_callback' => 'sanitize_text_field',
	'type' => 'option',
	));

	$wp_customize->add_control(
    'elitepress_lite_options[dribble_media_link_target]',
    array(
        'type' => 'checkbox',
        'label' => __('Open Link New tab/window','elitepress'),
        'section' => 'header_social_icon',
    )
);

//Youtube
$wp_customize->add_setting(
    'elitepress_lite_options[social_media_youtube_link]',
    array(
        'default' => '#',
		'sanitize_callback' => 'sanitize_text_field',
		'type' => 'option',
    )
	
	);
	$wp_customize->add_control(
    'elitepress_lite_options[social_media_youtube_link]',
    array(
        'label' => __('Youtube URL','elitepress'),
        'section' => 'header_social_icon',
        'type' => 'text',
    )
	);

	$wp_customize->add_setting(
	'elitepress_lite_options[youtube_media_link_target]',array(
	'default' => true,
	'sanitize_callback' => 'sanitize_text_field',
	'type' => 'option',
	));

	$wp_customize->add_control(
    'elitepress_lite_options[youtube_media_link_target]',
    array(
        'type' => 'checkbox',
        'label' => __('Open Link New tab/window','elitepress'),
        'section' => 'header_social_icon',
    )
);

//Viemo

$wp_customize->add_setting(
    'elitepress_lite_options[social_media_vimeo_link]',
    array(
        'default' => '#',
		'sanitize_callback' => 'sanitize_text_field',
		'type' => 'option',
    )
	
	);
	$wp_customize->add_control(
    'elitepress_lite_options[social_media_vimeo_link]',
    array(
        'label' => __('Vimeo URL','elitepress'),
        'section' => 'header_social_icon',
        'type' => 'text',
    )
	);

	$wp_customize->add_setting(
	'elitepress_lite_options[viemo_media_link_target]',array(
	'default' => true,
	'sanitize_callback' => 'sanitize_text_field',
	'type' => 'option',
	));

	$wp_customize->add_control(
    'elitepress_lite_options[viemo_media_link_target]',
    array(
		'type' => 'checkbox',
        'label' => __('Open Link New tab/window','elitepress'),
        'section' => 'header_social_icon',
    )
);

//pagelines

$wp_customize->add_setting(
    'elitepress_lite_options[social_media_pagelines_link]',
    array(
        'default' => '#',
		'sanitize_callback' => 'sanitize_text_field',
		'type' => 'option',
    )
	
	);
	$wp_customize->add_control(
    'elitepress_lite_options[social_media_pagelines_link]',
    array(
        'label' => __('Pagelines URL','elitepress'),
        'section' => 'header_social_icon',
        'type' => 'text',
    )
	);

	$wp_customize->add_setting(
	'elitepress_lite_options[pagelines_media_link_target]',array(
	'default' => true,
	'sanitize_callback' => 'sanitize_text_field',
	'type' => 'option',
	));

	$wp_customize->add_control(
    'elitepress_lite_options[pagelines_media_link_target]',
    array(
        'type' => 'checkbox',
        'label' => __('Open Link New tab/window','elitepress'),
        'section' => 'header_social_icon',
    )
);

	//Contact setting
$wp_customize->add_section(
        'header_contact',
        array(
            'title' => 'Header Contact Setting',
           'priority'    => 350,
			'panel' => 'header_options',
        )
    );
	
//Contact mail and number
$wp_customize->add_setting(
	'elitepress_lite_options[contact_address_settings]', array(
        'default'        => true,
        'capability'     => 'edit_theme_options',
		'sanitize_callback' => 'sanitize_text_field',
		'type' => 'option',
    ));
	$wp_customize->add_control('elitepress_lite_options[contact_address_settings]', array(
        'label'   => __('Enable email and phone number on header section.', 'elitepress'),
        'section' => 'header_contact',
        'type'    => 'checkbox',
		'priority'   => 2,
    )); // enable / disable Contact mail and number
	
	
	//Contact email id
	$wp_customize->add_setting(
	'elitepress_lite_options[contact_email]', array(
        'default'        => __('info@elitepresstheme.com','elitepress'),
        'capability'     => 'edit_theme_options',
		'sanitize_callback' => 'sanitize_text_field',
		'type' => 'option',
    ));
	$wp_customize->add_control('elitepress_lite_options[contact_email]', array(
        'label'   => __('Input Email id:', 'elitepress'),
        'section' => 'header_contact',
        'type'    => 'text',
    ));
	
	
	//Contact phone number
	$wp_customize->add_setting(
	'elitepress_lite_options[contact_phone_number]', array(
        'default'        => __('+48-0987-654-321','elitepress'),
        'capability'     => 'edit_theme_options',
		'sanitize_callback' => 'sanitize_text_field',
		'type' => 'option',
    ));
	$wp_customize->add_control('elitepress_lite_options[contact_phone_number]', array(
        'label'   => __('Input Contact Phone Number:', 'elitepress'),
        'section' => 'header_contact',
        'type'    => 'text',
    ));
	
//Header social Icon

	$wp_customize->add_section(
        'header_search_bar',
        array(
            'title' => 'Search Bar ',
           'priority'    => 400,
			'panel' => 'header_options',
        )
    );
	
	
	//Show and hide Header Search Bar
	$wp_customize->add_setting(
	'elitepress_lite_options[header_search_bar_enabled]'
    ,
    array(
        'default' => true,
		'capability'     => 'edit_theme_options',
		'sanitize_callback' => 'sanitize_text_field',
		'type' => 'option',
    )	
	);
	$wp_customize->add_control(
    'elitepress_lite_options[header_search_bar_enabled]',
    array(
        'label' => __('Enable Search bar on Header','elitepress'),
        'section' => 'header_search_bar',
        'type' => 'checkbox',
    )
	);

	
	}
	add_action( 'customize_register', 'elitepress_header_customizer' );
	?>