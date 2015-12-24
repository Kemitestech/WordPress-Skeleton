<?php
/**Theme Name	: elitepress-child
 * Theme Core Functions and Codes
*/
/**Includes reqired resources here**/
define('WEBRITI_TEMPLATE_DIR_URI', get_stylesheet_directory_uri());
define('WEBRITI_TEMPLATE_DIR',get_stylesheet_directory());
define('WEBRITI_THEME_FUNCTIONS_PATH',WEBRITI_TEMPLATE_DIR.'/functions');

require( WEBRITI_THEME_FUNCTIONS_PATH . '/widget/custom-sidebar.php');
require_once( WEBRITI_THEME_FUNCTIONS_PATH . '/scripts/scripts.php');


add_action ('wp_enqueue_scripts','theme_enqueue_style');
function theme_enqueue_style() {
	wp_enqueue_style ('parent-style', get_template_directory_uri() . '/style.css');
}


