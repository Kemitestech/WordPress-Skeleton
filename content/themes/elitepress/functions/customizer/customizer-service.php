<?php
function elitepress_service_customizer( $wp_customize ) {
 
//Service section panel
$wp_customize->add_panel( 'elitepress_service_options', array(
		'priority'       => 500,
		'capability'     => 'edit_theme_options',
		'title'      => __('Service Settings', 'elitepress'),
	) );

	
	$wp_customize->add_section( 'service_section_head' , array(
		'title'      => __('Service Heading ', 'elitepress'),
		'panel'  => 'elitepress_service_options',
		'priority'   => 50,
   	) );
	
	
	//Hide Index Service Section
	
	$wp_customize->add_setting(
    'elitepress_lite_options[service_section_enabled]',
    array(
        'default' => true,
		'capability'     => 'edit_theme_options',
		'sanitize_callback' => 'sanitize_text_field',
		'type' => 'option'
    )	
	);
	$wp_customize->add_control(
    'elitepress_lite_options[service_section_enabled]',
    array(
        'label' => __(' Enable Service Section on front page.','elitepress'),
        'section' => 'service_section_head',
        'type' => 'checkbox',
    )
	);
	
	$wp_customize->add_setting(
    'elitepress_lite_options[service_title]',
    array(
        'default' =>__('Our Services','elitepress'),
		'capability'     => 'edit_theme_options',
		'sanitize_callback' => 'sanitize_text_field',
		'type' => 'option'
    )	
	);
	$wp_customize->add_control(
    'elitepress_lite_options[service_title]',
    array(
        'label' => __('Service Title','elitepress'),
        'section' => 'service_section_head',
        'type' => 'text',
    )
	);
	
	$wp_customize->add_setting(
    'elitepress_lite_options[service_description]',
    array(
        'default' => __('Duis aute irure dolor in reprehenderit in voluptate velit cillum dolore eu fugiat nulla pariatur. Excepteur sint occaecat cupid non proident, sunt in culpa qui officia deserunt mollit anim id est laborum.','elitepress'),
		'sanitize_callback' => 'sanitize_text_field',
		'type' => 'option'
    )	
	);
	$wp_customize->add_control(
    'elitepress_lite_options[service_description]',
    array(
        'label' => __('Service Description','elitepress'),
        'section' => 'service_section_head',
        'type' => 'text',
		'sanitize_callback' => 'sanitize_text_field',
    )
	);	
	
//service section one
	$wp_customize->add_section( 'service_section_one' , array(
		'title'      => __('Service Section one', 'elitepress'),
		'panel'  => 'elitepress_service_options',
		'priority'   => 100,
		'sanitize_callback' => 'sanitize_text_field',
   	) );
	$wp_customize->add_setting(
		'elitepress_lite_options[service_one_icon]', array(
		 'sanitize_callback' => 'sanitize_text_field',
        'default'        => 'fa fa-shield',
        'capability'     => 'edit_theme_options',
		'type' => 'option',
    ));
	
	$wp_customize->add_control( 'elitepress_lite_options[service_one_icon]', array(
        'label'   => __('Service icon', 'elitepress'),
		'section' => 'service_section_one',
        'type'    => 'text',
    ));	

	$wp_customize->add_setting(
    'elitepress_lite_options[service_one_title]',
    array(
        'default' => __('Easy to Use','elitepress'),
		'capability'     => 'edit_theme_options',
		'sanitize_callback' => 'sanitize_text_field',
		'type' => 'option'
    )	
	);
	$wp_customize->add_control(
    'elitepress_lite_options[service_one_title]',
    array(
        'label' => __('Service Title','elitepress'),
        'section' => 'service_section_one',
        'type' => 'text',
    )
	);

	$wp_customize->add_setting(
    'elitepress_lite_options[service_one_description]',
    array(
        'default' => __('Morbi leo risus, porta ac consectetur ac, vestibulum at eros. Cras mattis consectetur purus sit amet ferment etiam porta sem malesuada magna mollis.','elitepress'),
		 'capability'     => 'edit_theme_options',
		 'sanitize_callback' => 'sanitize_text_field',
		 'type' => 'option'
    )	
	);
	$wp_customize->add_control(
    'elitepress_lite_options[service_one_description]',
    array(
        'label' => __('Service Description','elitepress'),
        'section' => 'service_section_one',
        'type' => 'text',	
    )
);
//Second service

$wp_customize->add_section( 'service_section_two' , array(
		'title'      => __('Service Section Two', 'elitepress'),
		'panel'  => 'elitepress_service_options',
		'priority'   => 200,
   	) );


$wp_customize->add_setting(
    'elitepress_lite_options[service_two_icon]',
    array(
        'type' =>'option',
		'default' => 'fa fa-tablet',
		 'capability'     => 'edit_theme_options',
		 'sanitize_callback' => 'sanitize_text_field',
		 
    )	
);
$wp_customize->add_control(
    'elitepress_lite_options[service_two_icon]',
    array(
        'label' => __('Service Icon','elitepress'),
        'section' => 'service_section_two',
        'type' => 'text',
    )
);

$wp_customize->add_setting(
    'elitepress_lite_options[service_two_title]',
    array(
        'default' => __('Twitter Bootstrap 3.2.0','elitepress'),
		'capability'     => 'edit_theme_options',
		'sanitize_callback' => 'sanitize_text_field',
		'type' => 'option',
    )	
);
$wp_customize->add_control(
    'elitepress_lite_options[service_two_title]',
    array(
        'label' => __('Service Title','elitepress'),
        'section' => 'service_section_two',
        'type' => 'text',
    )
);

$wp_customize->add_setting(
    'elitepress_lite_options[service_two_description]',
    array(
        'default' => __('Lorem ipsum dolor sit amet, consectetur adipisicing elit. Consec tetur adipisicing elit dignissim dapib tumst.','elitepress'),
		 'capability'     => 'edit_theme_options',
		 'sanitize_callback' => 'sanitize_text_field',
		 'type' => 'option',
    )	
);
$wp_customize->add_control(
		'elitepress_lite_options[service_two_description]',
		array(
        'label' => __('Service Description','elitepress'),
        'section' => 'service_section_two',
        'type' => 'text',
    )
);
//Third Service section
$wp_customize->add_section( 'service_section_three' , array(
		'title'      => __('Service Section Three', 'elitepress'),
		'panel'  => 'elitepress_service_options',
		'priority'   => 300,
   	) );


$wp_customize->add_setting(
    'elitepress_lite_options[service_three_icon]',
    array(
        'default' => 'fa fa-edit',
		'capability'     => 'edit_theme_options',
		'sanitize_callback' => 'sanitize_text_field',
		'type' => 'option',
		
    )	
);
$wp_customize->add_control(
'elitepress_lite_options[service_three_icon]',
    array(
        'label' => __('Service Icon','elitepress'),
        'section' => 'service_section_three',
        'type' => 'text',
		
    )
);

$wp_customize->add_setting(
    'elitepress_lite_options[service_three_title]',
    array(
        'default' => __('Exclusive Support','elitepress'),
		'capability'     => 'edit_theme_options',
		'sanitize_callback' => 'sanitize_text_field',
		'type' =>'option',
    )	
);
$wp_customize->add_control(
    'elitepress_lite_options[service_three_title]',
    array(
        'label' => __('Service Title','elitepress'),
        'section' => 'service_section_three',
        'type' => 'text',
    )
);

$wp_customize->add_setting(
    'elitepress_lite_options[service_three_description]',
    array(
        'default' => __('Morbi leo risus, porta ac consectetur ac, vestibulum at eros. Cras mattis consectetur purus sit amet ferment etiam porta sem malesuada magna mollis.','elitepress'),
		'capability'     => 'edit_theme_options',
		'sanitize_callback' => 'sanitize_text_field',
		'type' =>'option',
    )	
);
$wp_customize->add_control(
    'elitepress_lite_options[service_three_description]',
    array(
        'label' => __('Service Description','elitepress'),
        'section' => 'service_section_three',
        'type' => 'text',
    )
);
//Four Service section

$wp_customize->add_section( 'service_section_four' , array(
		'title'      => __('Service Section Four', 'elitepress'),
		'panel'  => 'elitepress_service_options',
		'priority'   => 400,
   	) );
	

$wp_customize->add_setting(
    'elitepress_lite_options[service_four_icon]',
    array(
        'default' => 'fa fa-star-half-o',
		'capability'     => 'edit_theme_options',
		'sanitize_callback' => 'sanitize_text_field',
		'type' =>'option',
    )	
);
$wp_customize->add_control(
    'elitepress_lite_options[service_four_icon]',
    array(
        'label' => __('Service Icon','elitepress'),
        'section' => 'service_section_four',
        'type' => 'text',
    )
);

$wp_customize->add_setting(
    'elitepress_lite_options[service_four_title]',
    array(
        'default' => __('Incredibly Flexible','elitepress'),
		'capability'     => 'edit_theme_options',
		'sanitize_callback' => 'sanitize_text_field',
		'type' => 'option'
    )	
);
$wp_customize->add_control(
    'elitepress_lite_options[service_four_title]',
    array(
        'label' => __('Service Title','elitepress'),
        'section' => 'service_section_four',
        'type' => 'text',
    )
);

$wp_customize->add_setting(
   'elitepress_lite_options[service_four_description]',
    array(
        'default' => __('Morbi leo risus, porta ac consectetur ac, vestibulum at eros. Cras mattis consectetur purus sit amet ferment etiam porta sem malesuada magna mollis.','elitepress'),
		'capability'     => 'edit_theme_options',
		'sanitize_callback' => 'sanitize_text_field',
		'type' => 'option'
    )	
);
$wp_customize->add_control(
    'elitepress_lite_options[service_four_description]',
    array(
        'label' => __('Service Description','elitepress'),
        'section' => 'service_section_four',
        'type' => 'text',
		'sanitize_callback' => 'sanitize_text_field',
    )
);
class WP_service_Customize_Control extends WP_Customize_Control {
    public $type = 'new_menu';
    /**
    * Render the control's content.
    */
    public function render_content() {
    ?>
      <div class="pro-vesrion">
	 <P><?php _e('Want to add more service than upgrade to pro','elitepress');?></P>
	 </div>
	  <div class="pro-box">
	 <a href="<?php echo esc_url( __('http://webriti.com/elitepress/', 'elitepress'));?>" target="_blank" class="service" id="review_pro"><?php _e( 'UPGRADE TO PRO','elitepress' ); ?></a>
	 <div>
    <?php
    }
}
//Pro service section
$wp_customize->add_section( 'service_section_pro' , array(
		'title'      => __('Add More service', 'elitepress'),
		'panel'  => 'elitepress_service_options',
		'priority'   => 700,
   	) );


$wp_customize->add_setting(
     'elitepress_lite_options[service_pro]',
    array(
        'default' => __('','elitepress'),
		'capability'     => 'edit_theme_options',
		'sanitize_callback' => 'sanitize_text_field',
		'type' => 'option',
    )	
);
$wp_customize->add_control( new WP_service_Customize_Control( $wp_customize, 'elitepress_lite_options[service_pro]', array(	
		'label' => __('Discover elitepress Pro','elitepress'),
        'section' => 'service_section_pro',
		'setting' => 'elitepress_lite_options[service_pro]',
    ))
);

}
add_action( 'customize_register', 'elitepress_service_customizer' );
?>