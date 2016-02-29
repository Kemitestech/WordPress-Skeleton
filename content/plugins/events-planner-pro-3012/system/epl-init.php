<?php

/*
 * Initializes menus, post types, loads assets, etc
 * Right now loads everything.  Page specific loading ... some day.
 *
 */

class EPL_Init {

    private static $instance;
    private $epl_update_endpoint = 'http://www.wpeventsplanner.com';


    function EPL_Init() {

        $this->__constuct();
    }


    function __constuct() {

        $this->epl = EPL_Base::get_instance();

        add_action( 'init', array( $this, 'create_post_types' ) );

        add_action( 'admin_menu', array( $this, 'admin_specific' ) );
        add_action( 'admin_head', array( $this, 'reorder_menu' ), 100 );

        add_action( 'wp_print_styles', array( $this, 'front_specific_styles' ), 1, 1 );
        add_action( 'wp_print_styles', array( $this, 'add_custom_css' ), 100 );

        add_action( 'wp_enqueue_scripts', array( $this, 'front_specific_js' ) );
        //add_filter( 'wp_handle_upload', array( $this, 'handle_upload' ) ); //passes fielename, url, and post_id that I send from the fileupload form.
        //add_action( 'add_attachment', array( $this, 'handle_upload' ) ); //only passes attachment post id


        add_filter( 'upload_mimes', array( $this, 'mime_types' ), 1, 1 );

        add_action( 'init', array( $this, 'register_tables' ), 1 );
        if ( !defined( 'EPL_IS_ADMIN' ) )
            define( 'EPL_IS_ADMIN', is_admin() );

        add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue_scripts' ) );


        //add_action( 'switch_blog', 'register_epl_tables' );
    }


    public static function get_instance() {
        if ( !self::$instance ) {

            self::$instance = new EPL_Init;
        }

        return self::$instance;
    }


    function cron() {
        //do cron stuff, nothing yet :-)
    }


    function check_for_new() {
        //$db = debug_backtrace();
        //for testing
        //this line will not let the update message appear for some reason
        //delete_site_transient( 'update_plugins' );

        add_filter( 'pre_set_site_transient_update_plugins', array( $this, 'epl_api_check' ) );
        add_filter( 'plugins_api', array( $this, 'epl_api_information' ), 10, 3 );
    }


    function admin_enqueue_scripts() {
        if ( 'epl_registration' == get_post_type() )
            wp_dequeue_script( 'autosave' );
    }


    function reorder_menu() {
        global $submenu;
        if ( !epl_user_is_admin() || epl_get_setting( 'epl_event_options', 'epl_admin_event_list_version', 2 ) == 1 || !isset($submenu['edit.php?post_type=epl_event']) )
            return false;

        foreach ( $submenu['edit.php?post_type=epl_event'] as $key => $_submenu ) {
            if ( $_submenu[2] == 'epl_event_manager' )
                unset( $submenu['edit.php?post_type=epl_event'][$key] );

            if ( $_submenu[2] == 'edit.php?post_type=epl_event' )
                $submenu['edit.php?post_type=epl_event'][$key][2] = 'epl_event_manager';

            if ( $_submenu[2] == 'epl_dashboard1' ) {
                $submenu['edit.php?post_type=epl_event'][1] = $_submenu;
                unset( $submenu['edit.php?post_type=epl_event'][$key] );
            }
        }
        ksort( $submenu['edit.php?post_type=epl_event'] );
    }


    function mime_types( $mime_types ) {
        $mime_types['pem'] = 'vapplication/x-pem-file';
        return $mime_types;
    }


    function add_custom_css() {
        echo "<style>" . str_replace( array( "\r\n", "\r" ), "", epl_get_general_setting( 'epl_button_css' ) ) . "</style>";
    }


    function handle_upload( $file ) {

        if ( !empty( $_POST['post_id'] ) ) {

            $post_id = intval( $_POST['post_id'] );

            $post_type = get_post_type( $post_id );
            global $valid_controllers;

            if ( isset( $valid_controllers[$post_type] ) )
                EPL_Common_Model::get_instance()->handle_upload( $post_id, $post_type, $file );
        }
    }


    function admin_specific() {

        //only load the admin js/css files in the events planner pages
        global $pagenow, $typenow, $valid_controllers;
        $_t = '';
        if ( empty( $typenow ) && !empty( $_GET['post'] ) ) {
            $post = get_post( $_GET['post'] );
            $_t = $post->post_type;
        }
        elseif ( isset( $_GET['page'] ) ) {
            $_t = $_GET['page'];
        }
        elseif ( isset( $_GET['post_type'] ) ) {
            $_t = $_GET['post_type'];
        }

        if ( is_admin() && array_key_exists( $_t, $valid_controllers ) ) {

            //if ( $pagenow == 'post-new.php' OR $pagenow == 'post.php' ) {

            $this->common_js_files()
                    ->admin_js_files( $_t )
                    ->load_datepicker_files()
                    ->load_common_stylesheets()
                    ->load_admin_stylesheets();


            add_action( 'admin_footer', array( $this, 'load_slide_down_box' ) );
            add_action( 'admin_print_footer_scripts', 'wp_tiny_mce', 25 );

            do_action( 'epl_admin_specific_js', $this );

            //}
        }

        $this->create_admin_menu();
    }


    function front_specific_js() {

        $this->common_js_files()
                ->front_js_files();
        add_action( 'wp_footer', array( $this, 'load_slide_down_box' ) );
    }


    function front_specific_styles() {

        $this->load_common_stylesheets()
                ->load_front_stylesheets();
    }

    /*
     * JS files
     */


    function common_js_files() {
        global $epl_wp_localize_script_args;
        $_f = array( 'F', 'm', 'Y', 'j', 'l', 'd', 'S' );
        $_r = array( 'MM', 'mm', 'yy', 'd', 'DD', 'dd', '' );

        //$date_format_for_dp = (strpos('F', get_option( 'date_format' ) === false))?str_ireplace('Y','yy',get_option( 'date_format' )):'m/d/yy';
        $date_format_for_dp = str_replace( $_f, $_r, epl_nz( epl_get_general_setting( 'epl_admin_date_format' ), get_option( 'date_format' ) ) );
        wp_enqueue_script( 'jquery' );
        wp_enqueue_script( 'jquery-migrate-js', "//code.jquery.com/jquery-migrate-1.2.1.min.js", array( 'jquery' ) );
        if ( EPL_IS_ADMIN || epl_user_is_admin() )
            wp_enqueue_script( 'select2', $this->epl->load_asset( 'js/select2.min.js' ), array( 'jquery' ) );

        wp_enqueue_script( 'events_planner_js', $this->epl->load_asset( 'js/events-planner.js' ), array( 'jquery' ) );

        wp_enqueue_script( 'tipsy-js', EPL_FULL_URL . 'js/tipsy.js', array( 'jquery' ) );

        wp_enqueue_script( 'full-calendar-js', $this->epl->load_asset( 'js/fullcalendar.min.js' ), array( 'jquery' ) );

        wp_enqueue_script( 'jquery-form-js', $this->epl->load_asset( 'js/jquery.validate.min.js' ), array( 'jquery' ) );

        //wp_enqueue_script( 'datatables-js', epl_get_protocol() . '://ajax.aspnetcdn.com/ajax/jquery.dataTables/1.9.4/jquery.dataTables.min.js' );
        wp_enqueue_script( 'datatables-js', $this->epl->load_asset( 'js/jquery.dataTables.min.js' ), array( 'jquery' ) );
        $epl_wp_localize_script_args = array(
            'ajaxurl' => admin_url( 'admin-ajax.php', epl_get_protocol() ),
            'plugin_url' => EPL_FULL_URL,
            'date_format' => $date_format_for_dp,
            'time_format' => get_option( 'time_format' ),
            'firstDay' => get_option( 'start_of_week' ),
            'yearRange' => 'c-10:c+10',
            'sc' => ( epl_sc_is_enabled() ) ? 1 : 0,
            'debug' => EPL_DEBUG ? 1 : 0,
            'cart_added_btn_txt' => epl__('In the cart (View)')
        );
        $epl_wp_localize_script_args = apply_filters( 'epl_wp_localize_script_args', $epl_wp_localize_script_args );
        wp_localize_script( 'events_planner_js', 'EPL', $epl_wp_localize_script_args );


        do_action( 'epl_init_common_js_files', $this );

        return $this;
    }


    function admin_js_files( $post_type = null ) {
        //wp_enqueue_script(array('editor', 'thickbox', 'media-upload'));
        //wp_enqueue_script(array('editor'));

        wp_enqueue_script( 'farbtastic' );
        wp_enqueue_script( 'jquery-ui-sortable' );
        wp_enqueue_script( 'epl-forms-js', $this->epl->load_asset( 'js/events-planner-forms.js' ), array( 'jquery' ) );
        wp_enqueue_script( 'epl-event-manager-js', $this->epl->load_asset( 'js/epl-event-manager.js' ), array( 'jquery' ) );
        wp_enqueue_script( 'jquery-ui-timepicker', $this->epl->load_asset( 'js/jquery.ui.timepicker.js' ), array( 'jquery' ) );
        //wp_enqueue_script( 'chosen', $this->epl->load_asset( 'js/chosen.jquery.min.js' ), array( 'jquery' ) );



        wp_enqueue_script( 'tabletools-js', $this->epl->load_asset( 'js/TableTools.min.js' ), array( 'jquery' ) );
        wp_enqueue_script( 'zeroclipboard-js', $this->epl->load_asset( 'js/ZeroClipboard.js' ), array( 'jquery' ) );
        wp_enqueue_script( 'daterangepicker.date-js', $this->epl->load_asset( 'js/date.js' ), array( 'jquery' ) );
        wp_enqueue_script( 'jquery-blockUI-js', $this->epl->load_asset( 'js/jquery.blockUI.min.js' ), array( 'jquery' ) );
        wp_enqueue_script( 'daterangepicker.jQuery.compressed-js', $this->epl->load_asset( 'js/daterangepicker.jQuery.compressed.js' ), array( 'jquery' ) );

        //wp_enqueue_script( 'flot-js', $this->epl->load_asset( 'js/jquery.flot.min.js' ), array( 'jquery' ) );
        //wp_enqueue_script( 'flot-pie-js', $this->epl->load_asset( 'js/jquery.flot.pie.min.js' ), array( 'jquery' ) );
        //wp_enqueue_script( 'copy_csv_xls_pdf-js', $this->epl->load_asset( 'js/copy_csv_xls_pdf.swf' ), array( 'jquery' ) );
        wp_enqueue_script( 'jquery-ui-accordion' );
        wp_enqueue_script( 'jquery-ui-tabs' );
        wp_enqueue_script( 'jquery-ui-resizable' );
        wp_enqueue_script( 'jquery-effects-core' );
        wp_enqueue_script( 'jquery-effects-highlight' );
        //wp_enqueue_script( 'jquery-ui-draggable' );

        do_action( 'epl_init_admin_js_files', $this );
        if ( $post_type == 'epl_global_discount' ) {
            //in wp 3.6, this featured image did not work in event editing page
            if ( function_exists( 'wp_enqueue_media' ) )
                wp_enqueue_media(); //in WP 3.5
        }
        return $this;
    }


    function front_js_files() {
        wp_enqueue_script( 'events-planner-front-js', $this->epl->load_asset( 'js/epl-front.js' ), array( 'jquery' ) );

        wp_enqueue_script( 'google-maps-api', epl_get_protocol() . '://maps.googleapis.com/maps/api/js?sensor=false' );



        $this->load_datepicker_files();

        //wp_enqueue_script( 'jquery-ui-map-js', $this->epl->load_asset( 'js/jquery.ui.map.min.js' ), array( 'jquery' ) );

        do_action( 'epl_init_front_js_files', $this );

        return $this;
    }

    /*
     * CSS files
     */


    function load_common_stylesheets() {

        wp_enqueue_style( 'events-planner-stylesheet-main', $this->epl->load_asset( 'css/style.css' ) );
        wp_enqueue_style( 'fullcalendar-stylesheet', $this->epl->load_asset( 'css/fullcalendar.css' ) );
        //wp_enqueue_style( 'widget-calendar-css', EPL_FULL_URL . 'css/calendar/widget-calendar-default.css' );
        wp_enqueue_style( 'small-calendar-css', $this->epl->load_asset( 'css/calendar/small-calendar.css' ) );

        if ( EPL_IS_ADMIN || epl_user_is_admin() )
            wp_enqueue_style( 'select2', $this->epl->load_asset( 'css/select2.css' ) );

        wp_enqueue_style( 'jquery-dataTables', $this->epl->load_asset( 'css/jquery.dataTables.css' ) );

        do_action( 'epl_init_load_common_stylesheets', $this );

        return $this;
    }


    function load_admin_stylesheets() {

        wp_enqueue_style( 'TableTools_JUI', $this->epl->load_asset( 'css/TableTools.css' ) );
        wp_enqueue_style( 'ui.daterangepicker', $this->epl->load_asset( 'css/ui.daterangepicker.css' ) );
        wp_enqueue_style( 'farbtastic' );

        wp_enqueue_style( 'events-planner-admin-stylesheet', $this->epl->load_asset( 'css/admin_style.css' ) );
        //wp_enqueue_style( 'fontawesome ', (is_ssl() ? 'https' : 'http' ) . '://netdna.bootstrapcdn.com/font-awesome/4.1.0/css/font-awesome.min.css' );

        do_action( 'epl_init_load_admin_stylesheets', $this );

        return $this;
    }


    function load_datepicker_files() {
        //since wp 3.3 includes datepicker

        wp_enqueue_script( 'jquery-ui-core' );

        wp_enqueue_script( 'jquery-ui-datepicker' );
        wp_enqueue_script( 'jquery-ui-tabs' );

        wp_enqueue_style( 'events-planner-jquery-ui-style', (is_ssl() ? 'https' : 'http' ) . '://ajax.googleapis.com/ajax/libs/jqueryui/1.11.0/themes/smoothness/jquery-ui.css' );

        do_action( 'epl_init_load_datepicker_files', $this );

        return $this;
    }


    function load_front_stylesheets() {



        //wp_enqueue_style( 'fullcalendar-stylesheet', EPL_FULL_URL . 'css/fullcalendar.css' );
        wp_enqueue_style( 'course-calendar-css', EPL_FULL_URL . 'css/calendar/epl-course-cal.css' );

        if ( epl_get_setting( 'epl_general_options', 'epl_disable_defult_css' ) != 10 )
            wp_enqueue_style( 'events-planner-stylesheet', $this->epl->load_asset( 'css/events-planner-style1.css' ) );
        //wp_enqueue_style( 'events-planner-stylesheet', EPL_FULL_URL . 'css/events-planner-style1.css' );

        $theme = epl_get_setting( 'epl_fullcalendar_options', 'epl_fullcalendar_theme', '' );
        if ( $theme != '' )
            wp_enqueue_style( 'fullcalendar-jquery-ui-style', epl_get_protocol() . '://ajax.googleapis.com/ajax/libs/jqueryui/1.11.0/themes/' . $theme . '/jquery-ui.css' );

        do_action( 'epl_init_load_front_stylesheets', $this );
    }

    /*
     * HTML
     */


    function load_slide_down_box() {
        echo $this->epl->load_view( 'common/slide-down-box', '', true );
    }

    /*
     * Menus
     */


    function create_admin_menu() {

        if ( epl_get_setting( 'epl_event_options', 'epl_admin_event_list_version', 2 ) == 2 )
            add_submenu_page( 'edit.php?post_type=epl_event', epl__( 'Manage Events V2' ), epl__( 'Manage Events V2' ), 'manage_options', 'epl_event_manager', array( $this, 'route' ) );

        add_submenu_page( 'edit.php?post_type=epl_event', epl__( 'Reports' ), epl__( 'Reports' ), 'manage_options', 'epl_report_manager', array( $this, 'route' ) );
        //add_submenu_page( 'edit.php?post_type=epl_event', epl__( 'Dashboard' ), epl__( 'Dashboard' ), 'manage_categories', 'epl_dashboard_manager', array( $this, 'route' ) );

        add_submenu_page( 'edit.php?post_type=epl_event', epl__( 'Form Manager' ), epl__( 'Form Manager' ), 'manage_options', 'epl_form_manager', array( $this, 'route' ) );
        add_submenu_page( 'edit.php?post_type=epl_event', epl__( 'Settings' ), epl__( 'Settings' ) . ' (' . EPL_PLUGIN_VERSION . ')', 'manage_options', 'epl_settings', array( $this, 'route' ) );

        do_action( 'epl_init_create_admin_menu', $this );
    }

    /*
     * Misc
     */


    function route( $args = null ) {

        $r = EPL_router::get_instance()->route( $args );

        if ( 'init' == current_filter() )
            echo $r;


        return $r;
    }


    function shortcode_route( $args = array() ) {


        return EPL_router::get_instance()->shortcode_route( $args );
    }


    function register_widgets() {

        $this->epl->load_file( 'libraries/epl-advanced-cal-widget.php' );
        $this->epl->load_file( 'libraries/epl-upcoming-events-widget.php' );


        register_widget( 'EPL_advanced_cal_widget' );
        register_widget( 'EPL_upcoming_events_widget' );

        do_action( 'epl_init_register_widgets', $this );
    }

    /*
     * Custom Post types (http://codex.wordpress.org/Function_Reference/register_post_type)
     * Will be refactored
     */


    function ms_error() {

        echo "<div class='error'><p>This is a multisite installation.  As is, Events Planner will function properly only on the very first blog.  Please contact help@wpeventsplanner.com to get a multisite support license and corresponding files.</p></div>";
    }


    function create_post_types() {

        /*
         * i18n
         */
        load_plugin_textdomain( 'events-planner', false, EPL_REL_PATH . '/languages/' );

        //will add the Post category meta box to the event edit view
        //needs to be called @ init, can be used from functions.php
        //register_taxonomy_for_object_type('category','epl_event');


        global $epl_post_types;

        $epl_post_types = array(
            'epl_event' => array(
                'public' => true,
                'show_in_menu' => true,
                'show_in_nav_menus ' => false,
                'query_var' => 'epl_event',
                'rewrite' => array(
                    'slug' => 'event',
                    'with_front' => false,
                ),
                'menu_icon' => EPL_FULL_URL . 'images/calendar.png',
                'supports' => array( 'title', 'thumbnail', 'editor', 'excerpt', 'custom-fields' ), // 'editor','excerpt'
                'labels' => array(
                    'name' => epl__( 'Events' ),
                    'singular_name' => epl__( 'Event' ),
                    'add_new' => epl__( 'Add New Event' ),
                    'add_new_item' => epl__( 'Add New Event' ),
                    'edit_item' => epl__( 'Edit Event' ),
                    'new_item' => epl__( 'New Event' ),
                    'view_item' => epl__( 'View Event' ),
                    'search_items' => epl__( 'Search Events' ),
                    'not_found' => epl__( 'No Events Found' ),
                    'not_found_in_trash' => epl__( 'No Events Found In Trash' ),
                    'menu_name' => epl__( 'Events Planner' ),
                    'all_items' => epl__( 'Manage Events' )
                ),
                'capabilities' => array(
                    'publish_posts' => 'manage_options',
                    'edit_posts' => 'manage_options',
                    'edit_others_posts' => 'manage_options',
                    'delete_posts' => 'manage_options',
                    'delete_others_posts' => 'manage_options',
                    'read_private_posts' => 'manage_options',
                    'edit_post' => 'manage_options',
                    'delete_post' => 'manage_options',
                    'read_post' => 'manage_options',
                ),
            ),
            'epl_registration' => array(
                'public' => true,
                'exclude_from_search' => true,
                'show_in_menu' => true,
                'query_var' => 'epl_registration',
                'exclude_from_search' => true,
                'rewrite' => array(
                    'slug' => 'registration',
                    'with_front' => false,
                ),
                'supports' => array( 'title' ),
                'labels' => array(
                    'name' => epl__( 'Registrations' ),
                    'singular_name' => epl__( 'Registration' ),
                    'add_new' => epl__( 'Add New Registration' ),
                    'add_new_item' => epl__( 'Add New Registration' ),
                    'edit_item' => epl__( 'Edit Registration' ),
                    'new_item' => epl__( 'New Registration' ),
                    'view_item' => epl__( 'View Registration' ),
                    'search_items' => epl__( 'Search Registrations' ),
                    'not_found' => epl__( 'No Registrations Found' ),
                    'not_found_in_trash' => epl__( 'No Registrations Found In Trash' )
                ),
                'capabilities' => array(
                    'publish_posts' => 'manage_options',
                    'edit_posts' => 'manage_options',
                    'edit_others_posts' => 'manage_options',
                    'delete_posts' => 'manage_options',
                    'delete_others_posts' => 'manage_options',
                    'read_private_posts' => 'manage_options',
                    'edit_post' => 'manage_options',
                    'delete_post' => 'manage_options',
                    'read_post' => 'manage_options',
                ),
                'show_in_menu' => 'edit.php?post_type=epl_event'
            ),
            'epl_location' => array(
                'public' => true,
                'query_var' => 'epl_location',
                'rewrite' => array(
                    'slug' => 'location',
                    'with_front' => false,
                ),
                'supports' => array( 'title', 'editor', 'thumbnail' ),
                'labels' => array(
                    'name' => epl__( 'Locations' ),
                    'singular_name' => epl__( 'Location' ),
                    'add_new' => epl__( 'Add New Location' ),
                    'add_new_item' => epl__( 'Add New Location' ),
                    'edit_item' => epl__( 'Edit Location' ),
                    'new_item' => epl__( 'New Location' ),
                    'view_item' => epl__( 'View Location' ),
                    'search_items' => epl__( 'Search Locations' ),
                    'not_found' => epl__( 'No Locations Found' ),
                    'not_found_in_trash' => epl__( 'No Locations Found In Trash' )
                ),
                'capabilities' => array(
                    'publish_posts' => 'manage_options',
                    'edit_posts' => 'manage_options',
                    'edit_others_posts' => 'manage_options',
                    'delete_posts' => 'manage_options',
                    'delete_others_posts' => 'manage_options',
                    'read_private_posts' => 'manage_options',
                    'edit_post' => 'manage_options',
                    'delete_post' => 'manage_options',
                    'read_post' => 'manage_options',
                ),
                'show_in_menu' => 'edit.php?post_type=epl_event'
            ),
            'epl_pay_profile' => array(
                'show_ui' => true,
                'publicly_queryable' => false,
                'exclude_from_search' => true,
                'show_in_nav_menus' => false,
                'show_in_menu' => true,
                'query_var' => 'epl_pay_profile',
                'supports' => array( 'title' ),
                'labels' => array(
                    'name' => epl__( 'Payment Profiles' ),
                    'singular_name' => epl__( 'Payment Profile' ),
                    'add_new' => epl__( 'Add New Payment Profile' ),
                    'add_new_item' => epl__( 'Add New Payment Profile' ),
                    'edit_item' => epl__( 'Edit Payment Profiles' ),
                    'new_item' => epl__( 'New Payment Profile' ),
                    'view_item' => epl__( 'View Payment Profile' ),
                    'search_items' => epl__( 'Search Payment Profiles' ),
                    'not_found' => epl__( 'No Payment Profiles Found' ),
                    'not_found_in_trash' => epl__( 'No Payment Profiles Found In Trash' )
                ),
                'capabilities' => array(
                    'publish_posts' => 'manage_options',
                    'edit_posts' => 'manage_options',
                    'edit_others_posts' => 'manage_options',
                    'delete_posts' => 'manage_options',
                    'delete_others_posts' => 'manage_options',
                    'read_private_posts' => 'manage_options',
                    'edit_post' => 'manage_options',
                    'delete_post' => 'manage_options',
                    'read_post' => 'manage_options',
                ),
                'show_in_menu' => 'edit.php?post_type=epl_event'
            ),
            'epl_notification' => array(
                'show_ui' => true,
                'publicly_queryable' => false,
                'exclude_from_search' => true,
                'show_in_nav_menus' => false,
                'show_in_menu' => true,
                'query_var' => 'epl_notification',
                'supports' => array( 'title', 'editor' ),
                'labels' => array(
                    'name' => epl__( 'Notification Manager' ),
                    'singular_name' => epl__( 'Notification' ),
                    'add_new' => epl__( 'Add New Notification' ),
                    'add_new_item' => epl__( 'Add New Notification' ),
                    'edit_item' => epl__( 'Edit Notifications' ),
                    'new_item' => epl__( 'New Notification' ),
                    'view_item' => epl__( 'View Notification' ),
                    'search_items' => epl__( 'Search Notifications' ),
                    'not_found' => epl__( 'No Notifications Found' ),
                    'not_found_in_trash' => epl__( 'No Notifications Found In Trash' )
                ),
                'capabilities' => array(
                    'publish_posts' => 'manage_options',
                    'edit_posts' => 'manage_options',
                    'edit_others_posts' => 'manage_options',
                    'delete_posts' => 'manage_options',
                    'delete_others_posts' => 'manage_options',
                    'read_private_posts' => 'manage_options',
                    'edit_post' => 'manage_options',
                    'delete_post' => 'manage_options',
                    'read_post' => 'manage_options',
                ),
                'show_in_menu' => 'edit.php?post_type=epl_event'
            ),
            'epl_org' => array(
                'public' => true,
                'query_var' => 'epl_org',
                'rewrite' => array(
                    'slug' => 'org',
                    'with_front' => true,
                ),
                'supports' => array( 'title', 'editor', 'thumbnail' ),
                'labels' => array(
                    'name' => epl__( 'Organizations' ),
                    'singular_name' => epl__( 'Organization' ),
                    'add_new' => epl__( 'Add New Organization' ),
                    'add_new_item' => epl__( 'Add New Organization' ),
                    'edit_item' => epl__( 'Edit Organization' ),
                    'new_item' => epl__( 'New Organization' ),
                    'view_item' => epl__( 'View Organization' ),
                    'search_items' => epl__( 'Search Organizations' ),
                    'not_found' => epl__( 'No Payment Organizations Found' ),
                    'not_found_in_trash' => epl__( 'No Payment Organizations Found In Trash' )
                ),
                'capabilities' => array(
                    'publish_posts' => 'manage_options',
                    'edit_posts' => 'manage_options',
                    'edit_others_posts' => 'manage_options',
                    'delete_posts' => 'manage_options',
                    'delete_others_posts' => 'manage_options',
                    'read_private_posts' => 'manage_options',
                    'edit_post' => 'manage_options',
                    'delete_post' => 'manage_options',
                    'read_post' => 'manage_options',
                ),
                'show_in_menu' => 'edit.php?post_type=epl_event'
            )
        );


        $epl_post_types = apply_filters( 'epl_post_types', $epl_post_types );

        foreach ( ( array ) $epl_post_types as $post_type => $args ) {

            register_post_type( $post_type, $args );
        }


        /*
         * event categories
         */

        $events_planner_cat_args = array(
            'hierarchical' => true,
            //'query_var' => 'event_categories',
            'show_tagcloud' => true,
            'rewrite' => array(
                'slug' => 'event-categories',
                'with_front' => true
            ),
            'labels' => array(
                'name' => epl__( 'Event Categories' ),
                'singular_name' => epl__( 'Event Category' ),
                'edit_item' => epl__( 'Edit Event Category' ),
                'update_item' => epl__( 'Update Event Category' ),
                'add_new_item' => epl__( 'Add New Event Category' ),
                'new_item_name' => epl__( 'New Event Category Name' ),
                'all_items' => epl__( 'All Event Categories' ),
                'search_items' => epl__( 'Search Event Categories' ),
                'parent_item' => epl__( 'Parent Category' ),
                'parent_item_colon' => epl__( 'Parent Category:' )
            ),
            'capabilities' => array(
                'manage_terms' => 'manage_options',
                'edit_terms' => 'manage_options',
                'delete_terms' => 'manage_options',
                'assign_terms' => 'manage_options',
            ),
        );

        $events_planner_cat_args = apply_filters( 'epl_category_args', $events_planner_cat_args );
        /* Register the event taxonomy. */
        register_taxonomy( 'epl_event_categories', array( 'epl_event' ), $events_planner_cat_args );

        $events_planner_tag_args = array(
            'hierarchical' => false,
            //'query_var' => 'event_tags',
            'show_tagcloud' => true,
            'rewrite' => array(
                'slug' => 'event_tags',
                'with_front' => true
            ),
            'labels' => array(
                'name' => epl__( 'Event Tags' ),
                'singular_name' => epl__( 'Tag' ),
                'edit_item' => epl__( 'Edit Tag' ),
                'update_item' => epl__( 'Update Tag' ),
                'add_new_item' => epl__( 'Add New Tag' ),
                'new_item_name' => epl__( 'New Tag Name' ),
                'all_items' => epl__( 'All Tag' ),
                'search_items' => epl__( 'Search Tag' ),
                'parent_item' => epl__( 'Parent Tag' ),
                'parent_item_colon' => epl__( 'Parent Tag:' )
            ),
            'capabilities' => array(
                'manage_terms' => 'manage_options',
                'edit_terms' => 'manage_options',
                'delete_terms' => 'manage_options',
                'assign_terms' => 'manage_options',
            ),
        );
        $events_planner_tag_args = apply_filters( 'epl_tag_args', $events_planner_tag_args );
        register_taxonomy( 'epl_event_tags', array( 'epl_event' ), $events_planner_tag_args );
    }


    function register_tables() {
        global $wpdb;
        $wpdb->epl_registration = "{$wpdb->prefix}epl_registration";
        $wpdb->epl_regis_events = "{$wpdb->prefix}epl_regis_events";
        $wpdb->epl_regis_data = "{$wpdb->prefix}epl_regis_data";
        $wpdb->epl_regis_payment = "{$wpdb->prefix}epl_regis_payment";
        $wpdb->epl_regis_form_data = "{$wpdb->prefix}epl_regis_form_data";
        $wpdb->epl_attendance = "{$wpdb->prefix}epl_attendance";
    }


    function tables() {
        global $wpdb, $charset_collate;
        $tables = array();


        $tables[] = "
          CREATE TABLE `{$wpdb->epl_registration}` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `regis_id` int(11) NOT NULL,
            `regis_key` varchar(26) DEFAULT NULL,
            `num_events` mediumint(9) NOT NULL DEFAULT  '1',
            `status` tinyint(4) NOT NULL DEFAULT '1',
            `subtotal` double(10,2) NOT NULL DEFAULT '0.00',
            `surcharge` double(10,2) NOT NULL DEFAULT '0.00',
            `discountable_total` double(10,2) NOT NULL DEFAULT '0.00',
            `non_discountable_total` double(10,2) NOT NULL DEFAULT '0.00',
            `pre_discount_total` double(10,2) NOT NULL DEFAULT '0.00',
            `discount_amount` double(10,2) NOT NULL DEFAULT '0.00',
            `discount_code_id` varchar(25) DEFAULT NULL,
            `discount_source_id` int(11) NULL DEFAULT NULL,
            `discount_code` varchar(25) DEFAULT NULL,
            `donation_amount` double(10,2) NOT NULL DEFAULT '0.00',
            `grand_total` double(10,2) NOT NULL DEFAULT '0.00',
            `original_total` double(10,2) NOT NULL DEFAULT '0.00',
            `balance_due` double(10,2) NOT NULL DEFAULT '0.00',
            `regis_date` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
            `user_id` int(11) NOT NULL DEFAULT '0',
            `total_tickets` mediumint(9) NOT NULL DEFAULT  '1',
            PRIMARY KEY  (`id`),
            KEY `regis_id` (`regis_id`)
            ) $charset_collate; 
        ";

        $tables[] = "
          CREATE TABLE `{$wpdb->epl_regis_events}` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `regis_id` int(11) NOT NULL,
            `event_id` int(11) NOT NULL,
            `num_dates` int(11) NOT NULL,
            `subtotal` double(10,2) NOT NULL DEFAULT '0.00',
            `surcharge` double(10,2) NOT NULL DEFAULT '0.00',
            `discountable_total` double(10,2) NOT NULL DEFAULT '0.00',
            `non_discountable_total` double(10,2) NOT NULL DEFAULT '0.00',
            `pre_discount_total` double(10,2) NOT NULL DEFAULT '0.00',
            `discount_amount` double(10,2) NOT NULL DEFAULT '0.00',
            `discount_code` varchar(25) DEFAULT NULL,
            `discount_code_id` varchar(25) DEFAULT NULL,
            `discount_source_id` int(11) NULL DEFAULT NULL,
            `grand_total` double(10,2) NOT NULL DEFAULT '0.00',
            `meta` longtext NULL DEFAULT NULL,
            PRIMARY KEY  (`id`),
            KEY `regis_id` (`regis_id`),
            KEY `event_id` (`event_id`)
            ) $charset_collate; 
        ";

        $tables[] = "
          CREATE TABLE `{$wpdb->epl_regis_data}` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `regis_id` int(11) NOT NULL,
            `event_id` int(11) NOT NULL,
            `date_id` varchar(15) NOT NULL,
            `time_id` varchar(10) DEFAULT NULL,
            `price_id` varchar(10) NOT NULL,
            `price` double(10,2) DEFAULT '0.00',
            `quantity` int(11) NOT NULL DEFAULT '0',
            `total_quantity` int(11) NOT NULL DEFAULT '0',
            `meta` longtext NULL DEFAULT NULL,
            PRIMARY KEY  (`id`),
            KEY `regis_id` (`regis_id`),
            KEY `event_id` (`event_id`)
            ) $charset_collate; 
        ";
        $tables[] = "
          CREATE TABLE `{$wpdb->epl_regis_form_data}` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `regis_id` int(11) NOT NULL,
            `event_id` int(11) DEFAULT NULL,
            `form_no` tinyint(4) NOT NULL DEFAULT '0',
            `first_name` varchar(30) DEFAULT NULL,
            `last_name` varchar(30) DEFAULT NULL,
            `email` varchar(50) DEFAULT NULL,
            `field_id` text NOT NULL,
            `input_slug` text,
            `value` text NOT NULL,
            PRIMARY KEY  (`id`),
            KEY `regis_id` (`regis_id`),
            KEY `event_id` (`event_id`)                                                   
            ) $charset_collate; 
        ";
        $tables[] = "
          CREATE TABLE `{$wpdb->epl_regis_payment}` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `regis_id` int(11) NOT NULL,
            `payment_amount` double(10,2) DEFAULT '0.00',
            `payment_date` datetime NULL DEFAULT NULL,
            `payment_method_id` int(11) NOT NULL,
            `transaction_id` varchar(50) NOT NULL,
            `note` text NULL DEFAULT NULL,
            PRIMARY KEY  (`id`),                                                                                                   
            KEY `regis_id` (`regis_id`)
            ) $charset_collate; 
        ";
        $tables[] = "
          CREATE TABLE `{$wpdb->epl_attendance}` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `regis_id` bigint(20) NOT NULL DEFAULT '0',
            `event_id` bigint(20) NOT NULL DEFAULT '0',
            `regis_data_id` bigint(20) NOT NULL DEFAULT '0',
            `date_id` varchar(15) COLLATE utf8_unicode_ci DEFAULT NULL,
            `date_ts` int(10) DEFAULT NULL,
            `time_id` varchar(10) COLLATE utf8_unicode_ci DEFAULT NULL,
            `price_id` varchar(10) COLLATE utf8_unicode_ci DEFAULT NULL,
            `user_id` int(11) NOT NULL DEFAULT '0',
            `form_no` tinyint(4) DEFAULT NULL,
            `checkin_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY  (`id`),
            KEY `regis_id` (`regis_id`),
            KEY `event_id` (`event_id`),
            KEY `user_id` (`user_id`)
            ) $charset_collate; 
        ";

        return $tables;
    }


    function epl_api_check( $transient ) {

        // Check if the transient contains the 'checked' information
        // If no, just return its value without hacking it
        if ( empty( $transient->checked ) )
            return $transient;

        // The transient contains the 'checked' information
        // Now append to it information form your own API
        $plugin_slug = EPL_BASENAME;

        // POST data to send to your API
        $args = array(
            'action' => 'epl-update-check',
            'key' => 123,
            'plugin_name' => $plugin_slug,
            'version' => $transient->checked[$plugin_slug],
        );

        // Send request checking for an update
        $response = $this->epl_api_request( $args );

        // If response is false, don't alter the transient
        if ( false !== $response ) {
            $transient->response[$plugin_slug] = $response;
        }

        return $transient;
    }


// Send a request to the alternative API, return an object
    function epl_api_request( $args ) {

        // Send request
        $request = wp_remote_post( $this->epl_update_endpoint, array( 'body' => $args ) );

        // Make sure the request was successful
        if ( is_wp_error( $request )
                or
                wp_remote_retrieve_response_code( $request ) != 200
        ) {
            // Request failed
            return false;
        }

        // Read server response, which should be an object
        $response = maybe_unserialize( wp_remote_retrieve_body( $request ) );
        if ( is_object( $response ) ) {
            return $response;
        }
        else {
            // Unexpected response
            return false;
        }
    }


// Hook into the plugin details screen


    function epl_api_information( $false, $action, $args ) {

        $plugin_slug = EPL_BASENAME;

        // Check if this plugins API is about this plugin
        if ( $args->slug != $plugin_slug ) {
            return false;
        }

        // POST data to send to your API
        $args = array(
            'action' => 'plugin_information',
            'plugin_name' => $plugin_slug,
            'version' => $transient->checked[$plugin_slug],
        );

        // Send request for detailed information
        $response = $this->epl_api_request( $args );

        // Send request checking for information
        $request = wp_remote_post( epl_ALT_API, array( 'body' => $args ) );

        return $response;
    }

}
