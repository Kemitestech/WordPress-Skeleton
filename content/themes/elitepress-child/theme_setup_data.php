<?php
function theme_data_setup()
{	$slider_image = WEBRITI_TEMPLATE_DIR_URI . "/images/slider.jpg";
	$service_image = WEBRITI_TEMPLATE_DIR_URI . "/images/service.jpg";
	$portfolio_image = WEBRITI_TEMPLATE_DIR_URI . "/images/portfolio.jpg";
	return $theme_options=array(
			//Logo and Fevicon header
			'webriti_stylesheet'=>'default.css',
			'custom_background_enabled'=>'off',
			'upload_image_favicon'=>'',
			'webrit_custom_css'=>'',


			//Slider
			'home_slider_enabled'=>'on',
			'animation' => 'slide',
			'animationSpeed' => '1500',
			'slide_direction' => 'horizontal',
			'slideshowSpeed' => '2500',
			'slider_list'=>'',
			'total_slide'=>'',



			'home_banner_enabled'=>'on',
			'home_post_enabled' => 'on',
			'slider_total' => 4,
			'slider_radio' => 'demo',
			'slider_options'=> 'slide',
			'slider_transition_delay'=> '2000',
			'slider_select_category' => 'Uncategorized',
			'featured_slider_post' => '',

			// Social media links
			'header_social_media_enabled'=> 'on',
			'facebook_media_enabled'=> 'on',
			'twitter_media_enabled'=> 'on',
			'googleplus_media_enabled'=> 'off',
			'linkedin_media_enabled'=> 'on',
			'skype_media_enabled'=> 'off',
			'dribbble_media_enabled'=> 'off',
			'youtube_media_enabled'=> 'off',
			'vimeo_media_enabled'=> 'off',
			'pagelines_media_enabled'=> 'off',
			'social_media_facebook_link' => "#",
			'social_media_twitter_link' => "#",
			'social_media_googleplus_link' => "",
			'social_media_linkedin_link' => "#",
			'social_media_skype_link' => "",
			'social_media_dribbble_link' => "",
			'social_media_youtube_link' => "",
			'social_media_vimeo_link' => "",
			'social_media_pagelines_link' => "",

			//Contact Address Settings
			'contact_address_settings' => __('on','elitepress'),
			'contact_phone_number' => __('0121 772 0852','elitepress'),
			'contact_email' => __('enquiries@thearchesproject.org','elitepress'),



			//header logo setting
			'logo_section_settings' => 'on',
			'upload_image_logo'=>'',
			'height'=>'50',
			'width'=>'250',
			'text_title'=>'on',

			//header search Bar setting
			'header_search_bar_enabled' => 'on',

			//Home Top Call Out Area
			'header_call_out_area_enabled' => 'on',
			'header_call_out_title'=> __('Want to say Hey or find out more?','elitepress'),
			'header_call_out_description'=> __('Reprehen derit in voluptate velit cillum dolore eu fugiat nulla pariaturs  sint occaecat proidentse.','elitepress'),
			'header_call_out_btn_text'=> __('Buy It Now!','elitepress'),
			'header_call_out_btn_link'=>'',
			'header_call_out_btn_link_target'=>'on',


			//Footer Copyright custmization
			'footer_copyright_text' => __('<p>Copyright 2014 ElitePress <a href="#">WordPress Theme</a>. All rights reserved</p>','elitepress'),

			//Footer Menu bar Setting
			'footer_menu_bar_enabled' => 'on',

			//portfolio
			'portfolio_section_enabled' => 'on',

			'front_portfolio_title' => __('Latest Projects','elitepress'),
			'front_portfolio_description' => __ ('Morbi leo risus, porta ac consectetur vestibulum eros cras mattis consectetur purus sit...','elitepress'),

			'portfolio_one_title' => __('Business Growth','elitepress'),
			'portfolio_one_description' => __('Morbi leo risus, porta ac consectetur vestibulum eros cras 	mattis consectetur purus sit...','elitepress'),
			'portfolio_one_image' => $portfolio_image,

			'portfolio_two_title' => __('Functional Beauty','elitepress'),
			'portfolio_two_description' => __('Morbi leo risus, porta ac consectetur vestibulum eros cras mattis consectetur purus sit...','elitepress'),
			'portfolio_two_image' => $portfolio_image,

			'portfolio_three_title' => __('Planning Around','elitepress'),
			'portfolio_three_description' => __('Morbi leo risus, porta ac consectetur vestibulum eros cras mattis consectetur purus sit...','elitepress'),
			'portfolio_three_image' => $portfolio_image,


			// service
			'service_section_enabled' => 'on',
			'service_title' => __('Our Services','elitepress'),
			'service_description' => __('Duis aute irure dolor in reprehenderit in voluptate velit cillum dolore eu fugiat nulla pariatur. Excepteur sint occaecat cupid non proident, sunt in culpa qui officia deserunt mollit anim id est laborum.','elitepress'),
			/** Service One Setting **/
			'service_one_icon' => 'fa fa-shield',
			'service_one_title' => __('Responsive Design','elitepress'),
			'service_one_description' => __('Morbi leo risus, porta ac consectetur ac, vestibulum at eros. Cras mattis consectetur purus sit amet ferment etiam porta sem malesuada magna mollis.','elitepress'),
			/** Service Two Setting **/
			'service_two_icon' => 'fa fa-tablet',
			'service_two_title' => __('Twitter Bootstrap 3.2.0','elitepress'),
			'service_two_description' => __('Morbi leo risus, porta ac consectetur ac, vestibulum at eros. Cras mattis consectetur purus sit amet ferment etiam porta sem malesuada magna mollis.','elitepress'),
			/** Service Three Setting **/
			'service_three_icon' => 'fa fa-edit',
			'service_three_title' => __('Exclusive Support','elitepress'),
			'service_three_description' => __('Morbi leo risus, porta ac consectetur ac, vestibulum at eros. Cras mattis consectetur purus sit amet ferment etiam porta sem malesuada magna mollis.','elitepress'),
			/** Service Four Setting **/
			'service_four_icon' => 'fa fa-star-half-o',
			'service_four_title' => __('Incredibly Flexible','elitepress'),
			'service_four_description' => __('Morbi leo risus, porta ac consectetur ac, vestibulum at eros. Cras mattis consectetur purus sit amet ferment etiam porta sem malesuada magna mollis.','elitepress'),

			//Banner Heading

			'banner_title_category' => __('Categories','elitepress'),
			'banner_description_category' => __('','elitepress'),

			'banner_title_archive' => __('Archives','elitepress'),
			'banner_description_archive' => __('','elitepress'),

			'banner_title_author' => __('Author','elitepress'),
			'banner_description_author' => __('','elitepress'),

			'banner_title_404' => __('404','elitepress'),
			'banner_description_404' => __('','elitepress'),

			'banner_title_tag' => __('Tags','elitepress'),
			'banner_description_tag' => __('','elitepress'),

			'banner_title_search' => __('Search','elitepress'),
			'banner_description_search' => __('','elitepress'),

			);
}
?>
