<?php
// Footer copyright section 
	function elitepress_copyright_customizer( $wp_customize ) {
	$wp_customize->add_panel( 'elitepress_copyright_setting', array(
		'priority'       => 900,
		'capability'     => 'edit_theme_options',
		'title'      => __('Footer Copyright Settings', 'elitepress'),
	) );
	
	$wp_customize->add_section(
        'copyright_section_one',
        array(
            'title' => __('Footer Copyright Settings','elitepress'),
            'description' => __('This is a Footer section.','elitepress'),
            'priority' => 35,
			'panel' => 'elitepress_copyright_setting',
        )
    );
	
	
	$wp_customize->add_setting(
    'elitepress_lite_options[footer_copyright_text]',
    array(
       'default' => sprintf (__('@ Copyright 2014  elitepress Design & Developed by <a href="%1$s" target="_blank">Webriti</a>','elitepress'),'http://www.webriti.com'),
		
		'type' =>'option',
		'sanitize_callback' => 'elitepress_copyright_sanitize_text'
		
    )
);
$wp_customize->add_control(
    'elitepress_lite_options[footer_copyright_text]',
    array(
        'label' => __('Copyright text','elitepress'),
        'section' => 'copyright_section_one',
        'type' => 'textarea',
    ));
	
	
	$wp_customize->add_setting(
    'elitepress_lite_options[footer_menu_bar_enabled]',
    array(
        'default' => true ,
		'type' =>'option',
		'sanitize_callback' => 'elitepress_copyright_sanitize_text'
		
    )
);
$wp_customize->add_control(
    'elitepress_lite_options[footer_menu_bar_enabled]',
    array(
        'label' => __('Enable footer menu bar','elitepress'),
        'section' => 'copyright_section_one',
        'type' => 'checkbox',
    ));
	
	
	function elitepress_copyright_sanitize_text( $input ) {

    return wp_kses_post( force_balance_tags( $input ) );

}
	
	function elitepress_copyright_sanitize_html( $input ) {

    return force_balance_tags( $input );

}
}
add_action( 'customize_register', 'elitepress_copyright_customizer' );
?>