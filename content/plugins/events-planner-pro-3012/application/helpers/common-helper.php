<?php

/*
 * pardon the dust.  Cleanup planned
 */


function epl_e( $t ) {

    _e( $t, 'events-planner' );
}


function epl__( $t ) {

    return __( $t, 'events-planner' );
}

/*
 * checks for a null value and returns 0 or anything passed as $d
 */


function epl_nz( $v, $d = 0 ) {

    if ( !isset( $v ) || is_null( $v ) || $v === '' || $v === false )
        return $d;

    return $v;
}


function epl_debug_message( $param, $content ) {
    if ( EPL_DEBUG && epl_user_is_admin() )
        EPL_Base::get_instance()->epl_util->set_debug_message( $param, $content );
}


function epl_hourly_cron() {

    EPL_Init::get_instance()->cron();
}


function epl_data_type_process( $data, $type ) {

    switch ( $type )
    {

        case 'date':
            $data = date_i18n( 'Y-m-d' );
            break;
        case 'curr':
            return float_val( $data );
            break;
    }

    return $data;
}


function epl_yes_no() {

    return array( 0 => epl__( 'No' ), 10 => epl__( 'Yes' ) );
}


function epl_anchor( $url = null, $text = null, $target = '_blank', $attr = '' ) {

    return "<a href='{$url}' target='$target' $attr>{$text}</a>";
}


function epl_off_on() {

    return array( 0 => epl__( 'Off' ), 10 => epl__( 'On' ) );
}


function epl_is_ajax() {
    return (isset( $GLOBALS['epl_ajax'] ) && $GLOBALS['epl_ajax'] == true );
}


function epl_make_array( $start = 0, $end = 0, $prepend = null ) {


    $r = array();

    if ( !is_null( $prepend ) )
        $r += ( array ) $prepend;


    for ( $i = $start; $i <= $end; $i++ )
        $r[$i] = $i;

    return $r;
}


function epl_month_dd($num = false) {

    $a = array(
        epl__( 'Jan' ) => '01',
        epl__( 'Feb' ) => '02',
        epl__( 'Mar' ) => '03',
        epl__( 'Apr' ) => '04',
        epl__( 'May' ) => '05',
        epl__( 'Jun' ) => '06',
        epl__( 'Jul' ) => '07',
        epl__( 'Aug' ) => '08',
        epl__( 'Sep' ) => '09',
        epl__( 'Oct' ) => '10',
        epl__( 'Nov' ) => '11',
        epl__( 'Dec' ) => '12' );
    
    if($num){
        $v = array_values($a);
        $a = array_combine($v, $v);
    }
    
    return $a;
}


function get_list_of_available_notifications( $notification_id = null ) {

    /* $args = array(
      'post_type' => 'epl_notification',
      'posts_per_page' => -1
      );
      // The Query

      $the_query = new WP_Query( $args );

      $_a = array( );

      while ( $the_query->have_posts() ) :
      $the_query->the_post();
      $_a[get_the_ID()] = get_the_title();

      endwhile;


      wp_reset_postdata();
      return $_a; */

    global $wpdb;
    $_a = array();
    $result = $wpdb->get_results( "SELECT ID, post_title FROM $wpdb->posts WHERE `post_type` = 'epl_notification' AND `post_status` = 'publish' ORDER BY `post_title`" );

    foreach ( $result as $row ) {
        $_a[$row->ID] = $row->post_title;
    }


    //wp_reset_postdata();
    return $_a;
}


function get_list_of_available_locations( $location_id = null ) {

//THIS METHOD CHANGES THE GLOBAL $post, creates problems when someone goes into the registration page.
    /* $args = array(
      'post_type' => 'epl_location',
      'posts_per_page' => -1
      );
      // The Query

      $the_query = new WP_Query( $args );



      while ( $the_query->have_posts() ) :
      $the_query->the_post();
      $_a[get_the_ID()] = get_the_title();

      endwhile; */

    global $wpdb;
    $_a = array();
    $result = $wpdb->get_results( "SELECT ID, post_title FROM $wpdb->posts WHERE `post_type` = 'epl_location' AND `post_status` = 'publish' ORDER BY `post_title`" );

    foreach ( $result as $row ) {
        $_a[$row->ID] = $row->post_title;
    }


    //wp_reset_postdata();
    return $_a;
}


function epl_get_regis_payments() {
    global $regis_details;

    $source = !empty( $regis_details ) ? $regis_details : EPL_registration_model::get_instance()->current_data;

    $payment_data = epl_get_element( '_epl_payment_data', $source, array() );

    if ( empty( $payment_data ) )
        $payment_data[] = $source;

    foreach ( $payment_data as $k => $p )
        if ( epl_get_element( '_epl_payment_amount', $p, '' ) == '' )
            unset( $payment_data[$k] );

    return $payment_data;
}


function epl_get_balance_due() {
    global $cart_totals;

    $regis_total = get_the_regis_total_amount( false );
    $payment_data = epl_get_regis_payments();

    $alt_total_due = epl_get_element_m( 'pay_deposit', 'money_totals', $cart_totals ) == 1 ? epl_get_element_m( 'min_deposit', 'money_totals', $cart_totals, $regis_total ) : $regis_total;

    if ( !empty( $payment_data ) ) {
        $total_paid = 0;
        foreach ( $payment_data as $time => $p ) {
            if ( $p['_epl_payment_amount'] == 0 )
                continue;
            $regis_total -= epl_get_formatted_curr($p['_epl_payment_amount'],4);
        }
    } else {
        $regis_total = $alt_total_due;
    }

    return ($regis_total == 0) ? abs( $regis_total ) : $regis_total;
}


function epl_get_true_regis_status() {
    global $regis_details;
    $source = !empty( $regis_details ) ? $regis_details : EPL_registration_model::get_instance()->current_data;

    $balance_due = epl_get_balance_due();
    $current_status = epl_get_element( '_epl_regis_status', $source, 1 );
    $is_waitlist = ($current_status == 20);
    $is_active = ($current_status < 10);

    if ( $balance_due == 0 && $is_active && !$is_waitlist )
        return 5;
    elseif ( $balance_due > 0 && $is_active && !$is_waitlist )
        return 2;
    else
        return $current_status;
}


function get_list_of_payment_profiles() {

    global $wpdb;
    global $epl_fields;
    //echo "<pre class='prettyprint'>" . __LINE__ . "> " . print_r($epl_fields, true). "</pre>";
    $_a = array();
    $result = $wpdb->get_results( "SELECT ID, post_title FROM $wpdb->posts WHERE `post_type` = 'epl_pay_profile' AND `post_status` = 'publish' ORDER BY `post_title`" );

    foreach ( $result as $row ) {
        $_a[$row->ID] = $row->post_title;
    }


    //get_list_of_default_payment_profiles();
    return $_a;
}


function get_list_of_default_payment_profiles() {

    $pp = get_list_of_payment_profiles();

    $r = array();
    foreach ( $pp as $k => $v ) {
        $gw = get_gateway_info( $k );
        if ( epl_get_element( '_epl_default_selected', $gw ) == 10 )
            $r[] = strval( $k );
    }
    return $r;
}


function get_gateway_info( $gateway_id = null ) {
    static $cache = array();

    if ( $r = wp_cache_get( 'gateway_info_' . $gateway_id ) !== false )
        return $r;

    $gateway_info = EPL_common_model::get_instance()->get_post_meta_all( !is_null( $gateway_id ) ? $gateway_id : $this->get_payment_profile_id()  );

    wp_cache_add( 'gateway_info_' . $gateway_id, $gateway_info );

    return $gateway_info;
}


function get_list_of_orgs( $id = null ) {

    /* $args = array(
      'post_type' => 'epl_org',
      'posts_per_page' => -1
      );
      // The Query

      $the_query = new WP_Query( $args );

      $_a = array( );

      while ( $the_query->have_posts() ) :
      $the_query->the_post();
      $_a[get_the_ID()] = get_the_title();

      endwhile;


      wp_reset_postdata();
      return $_a; */

    global $wpdb;
    $_a = array();
    $result = $wpdb->get_results( "SELECT ID, post_title FROM $wpdb->posts WHERE `post_type` = 'epl_org' AND `post_status` = 'publish' ORDER BY `post_title`" );

    foreach ( $result as $row ) {
        $_a[$row->ID] = $row->post_title;
    }


    //wp_reset_postdata();
    return $_a;
}


function get_list_of_instructors( $id = null ) {

    /* $args = array(
      'post_type' => 'epl_instructor',
      'posts_per_page' => -1
      );
      // The Query

      $the_query = new WP_Query( $args );

      $_a = array( );

      while ( $the_query->have_posts() ) :
      $the_query->the_post();
      $_a[get_the_ID()] = get_the_title();

      endwhile;

      wp_reset_postdata();
      return $_a; */

    global $wpdb;
    $_a = array();
    $result = $wpdb->get_results( "SELECT ID, post_title FROM $wpdb->posts WHERE `post_type` = 'epl_instructor' AND `post_status` = 'publish' ORDER BY `post_title`" );

    foreach ( $result as $row ) {
        $_a[$row->ID] = $row->post_title;
    }


    //wp_reset_postdata();
    return $_a;
}


function epl_compare_dates( $date1, $date2, $logic = '=' ) {

    $date1 = ( is_numeric( $date1 ) && ( int ) $date1 == $date1 ) ? $date1 : strtotime( $date1 );
    $date2 = ( is_numeric( $date2 ) && ( int ) $date2 == $date2 ) ? $date2 : strtotime( $date2 );

    switch ( $logic )
    {

        case "=":
            return ($date1 == $date2);
            break;
        case ">=":
            return ($date1 >= $date2);
            break;
        case "<=":
            return ($date1 <= $date2);
            break;
        case ">":
            return ($date1 > $date2);
            break;
        case "<":
            return ($date1 < $date2);
            break;
    }
}


function epl_get_option( $var ) {

    $opt = get_option( $var );

    return $opt;
}


function epl_is_ok_to_register( $event_data, $current_key ) {

    /*
     * the event is marked as open for registration
     * registration start date is <= today -done
     * registration end date is >= today
     * there are available spaces
     *
     */

    global $event_details, $capacity, $current_att_count, $available_space_arr;

    $event_status = epl_get_element( '_epl_event_status', $event_details );

    //echo "<pre class='prettyprint'>$current_key" . print_r( $current_att_count, true ) . "</pre>";
    $today = date_i18n( 'Y-m-d H:i:s', EPL_TIME );

    $regis_start_date = epl_get_date_timestamp( epl_admin_dmy_convert( epl_get_element_m( $current_key, '_epl_regis_start_date', $event_details, $today ) ) );
    $regis_end_date = epl_get_date_timestamp( epl_admin_dmy_convert( epl_get_element_m( $current_key, '_epl_regis_end_date', $event_details, $today ) ) );

    $ok = epl_compare_dates( $today, $regis_start_date, ">=" );

    if ( !$ok )
        return epl__( "Available for registration on" ) . ' ' . epl_formatted_date( $event_details['_epl_regis_start_date'][$current_key] );

    $ok = epl_compare_dates( $today, strtotime( '23:59:59', $regis_end_date ), "<=" );

    if ( !$ok )
        return epl__( ' Registration Closed' );


    $avail_spaces = 0;
    if ( is_array( $available_space_arr ) && !epl_is_empty_array( $available_space_arr ) )
        if ( array_key_exists( $current_key, $available_space_arr ) && $available_space_arr[$current_key][1] ) {
            $avail_spaces = $available_space_arr[$current_key][1];

            $ok = is_numeric( $avail_spaces );
        }

    if ( !$ok )
        return epl__( 'Sold Out' );


    return true;
}


function epl_is_addon_active( $addon = '' ) {

    if ( $addon == '' )
        return false;


    $opt = get_option( $addon );

    if ( !$opt || $opt == 0 )
        return false;

    return true;
}


function epl_is_ok_to_show_regis_button() {


    global $event_details;

    if ( isset( $event_details['_epl_display_regis_button'] ) && $event_details['_epl_display_regis_button'] == 10 )
        return true;

    return false;
}


function epl_is_free_event() {

    global $event_details;

    if ( 10 == epl_get_element( '_epl_free_event', $event_details ) )
        return true;

    return false;
}


//This happens when there is a 0 price in the bunch and that's the only thing selected.
function epl_is_zero_total() {
    global $cart_totals, $event_totals;

    if ( epl_is_empty_array( $cart_totals ) )
        EPL_registration_model::get_instance()->calculate_cart_totals();

    if ( $cart_totals['money_totals']['grand_total'] == 0 && $cart_totals['_att_quantity']['total'] > 0 )
        return true;

    return false;
}


function epl_get_list_of_available_forms() {


    $_r = wp_cache_get( 'epl_get_list_of_available_forms' );

    if ( $_r !== false )
        return $_r;

    $forms = EPL_common_model::get_instance()->get_list_of_available_forms();

    $_r = array();

    foreach ( $forms as $form_key => $form_info ) {

        $_r[$form_key] = $form_info['epl_form_label'];
    }

    wp_cache_set( 'epl_get_list_of_available_forms', $_r );

    return $_r;
}


function epl_get_list_of_available_fields( $limit_to = array(), $refresh = false ) {


    $_r = wp_cache_get( 'epl_get_list_of_available_fields' );

    if ( $_r !== false )
        return $_r;

    $_r = EPL_common_model::get_instance()->get_list_of_available_fields();


    wp_cache_set( 'epl_get_list_of_available_fields', $_r );

    return $_r;
}


function epl_get_fields_inside_form( $form = array() ) {

    $all_forms = EPL_Common_Model::get_instance()->get_list_of_available_forms();

    $form = array_flip( $form );

    $forms = array_intersect_key( $all_forms, $form );
    $fields = array();

    foreach ( $forms as $form_id => $form_atts ) {
        if ( epl_is_empty_array( $form_atts['epl_form_fields'] ) )
            continue;

        $fields += $form_atts['epl_form_fields'];
    }

    return $fields;
}


function epl_get_field_labels( $fields ) {

    $r = array();

    foreach ( $fields as $field_id => $field_atts ) {
        $r[$field_id] = $field_atts['label'];
    }
    return $r;
}


function epl_get_event_option( $key = null ) {

    if ( is_null( $key ) )
        return null;

    $setting = 'epl_event_options';

    return epl_get_setting( $setting, $key );
}


function epl_get_general_setting( $key = null ) {

    if ( is_null( $key ) )
        return null;

    $setting = 'epl_general_options';

    return epl_get_setting( $setting, $key );
}


function epl_get_setting( $opt = '', $key = null, $default = null ) {

    if ( $opt == '' )
        return null;

    static $checked = array();

    //if ( array_key_exists( $key, $checked ) )
    //  return $checked[$key];

    $settings = get_option( $opt );

    if ( is_null( $settings ) )
        return null;

    if ( array_key_exists( $key, ( array ) $settings ) ) {
        $checked[$key] = $settings[$key];
        return $checked[$key];
    }

    return $default;
}


function epl_get_regis_setting( $opt = '' ) {

    if ( $opt == '' )
        return false;

    static $checked = array();

    if ( array_key_exists( $opt, $checked ) )
        return $checked[$opt];

    $settings = get_option( 'epl_registration_options' );


    if ( array_key_exists( $opt, ( array ) $settings ) ) {
        $checked[$opt] = $settings[$opt];
        return $checked[$opt];
    }

    return null;
}


function get_help_icon( $args = array() ) {

    return;
    $section = $args['section'];
    //$help_id = $args['id'];

    $h = '
        <a href="http://wpeventsplanner.com" class="epl_get_help" id ="_help_' . $section . '">
        <img  src ="' . EPL_FULL_URL . 'images/help.png" alt="Help" /></a>
        <a href="http://wpeventsplanner.com" class="epl_send_email" id ="_section__' . $section . '">
        <img  src ="' . EPL_FULL_URL . 'images/email.png" alt="Send Feedback" /></a>

        ';

    return $h;
}


function epl_show_ad( $content = '' ) {
    return;
    if ( get_option( 'epl_show_ad' ) == 1 || $content == '' )
        return null;


    //$section = $args['section'];
    //$help_id = $args['id'];

    $h = '
        <div class="epl_ad">

        <div>
        ' . $content . '
        <a href="http://wpeventsplanner.com" target="_blank">Learn more</a>
        </div>
        <a href="http://wpeventsplanner.com" target="_blank"><img src="' . EPL_FULL_URL . 'images/epl-url-small.png" alt="Events Planner for Wordpress" /></a>
        </div>';

    return $h;
}


function get_remote_help() {



    if ( isset( $_REQUEST['epl_get_help'] ) && $_REQUEST['epl_get_help'] == 1 ) {

        $r = wp_remote_post( 'http://www.wpeventsplanner.com/?get_remote_help=1', array( 'body' => array( 'help_context' => $_POST['section'], 'api_key' => '14654654dsfd4g54d5sd4fgsdfg' ) ) );
        $r = wp_remote_retrieve_body( $r );
        $r = json_decode( $r );

        echo EPL_Util::get_instance()->epl_response( array( 'html' => $r->help_text ) );
        die();
    }
}


function epl_donate_button() {
    // Please guys, this took a lot of work on my end //
    return '<a href="https://www.paypal.com/cgi-bin/webscr?cmd=_donations&business=abels122%40gmail%2ecom&lc=US&item_name=Events%20Planner%20for%20Wordpress&no_note=0&currency_code=USD&bn=PP%2dDonationsBF%3abtn_donateCC_LG%2egif%3aNonHostedGuest" target="_blank">
        <img src="https://www.paypalobjects.com/en_US/i/btn/btn_donateCC_LG.gif" alt ="Please Donate" /><a>
        ';
}



function epl_get_formatted_curr( $amount, $format = null, $currency_symbol = false ) {
    if ( $amount === '' )
        return $amount;
    
    //remove any comma, currency sign
    $amount = preg_replace('/[^\d\.]/', '', $amount);

    $v = epl_get_option( 'epl_general_options' );
    $format = ($format) ? $format : $v['epl_currency_display_format'];

    switch ( $format )
    {
        case 1:
            $amount = number_format( $amount, 2, '.', ',' );
            break;
        case 2:
            $amount = number_format( $amount, 0, '', ',' );
            break;
        case 3:
            $amount = number_format( $amount, 0, '', '' );
            break;
        case 4:
            $amount = number_format( $amount, 2, '.', '' );
            break;
        case 5:
            $amount = number_format( $amount, 2, ',', ' ' );
            break;
        case 6:
            $amount = number_format( $amount, 2, '.', ' ' );
            break;
    }
    
    if ( $currency_symbol )
        $amount = epl_get_currency_symbol( $amount );
    
    return $amount;
}


function epl_get_currency_symbol( $amount = null ) {
    $_amount = $amount;
    $opt = epl_get_option( 'epl_general_options' );
    $symbol = epl_get_element( 'epl_currency_symbol', $opt, '' );
    $location = epl_get_element( 'epl_currency_symbol_location', $opt, 'b' );
    if ( $symbol ) {
        if ( $location == 'b' )
            $amount = epl_prefix( $symbol, $amount );
        else
            $amount = epl_suffix( ' ' . $symbol, $amount );
    }

    $amount = apply_filters('epl_get_currency_symbol',$amount, $_amount, $symbol,$location);
    
    return $amount;
}


function epl_get_event_property( $prop = '', $raw = false, $key = '' ) {

    if ( $prop == '' )
        return null;

    global $event_details, $event_fields;


    if ( $key !== '' ) {
        if ( array_key_exists( $key, ( array ) $event_details[$prop] ) )
            return $event_details[$prop][$key];
    } elseif ( isset( $event_details[$prop] ) && $prop !== 0 && epl_get_element( $prop, $event_details, '' ) !== '' ) {

        if ( $raw )
            return $event_details[$prop];

        $_field_type = epl_get_element_m( 'input_type', $prop, $event_fields );
        $_p = $event_details[$prop];

        if ( $_field_type == 'select' || $_field_type == 'radio' ) {
            $_p = $event_fields[$prop]['options'][$event_details[$prop]];
        }
        elseif ( $_field_type == 'checkbox' && is_array( is_array( $_p ) ) ) {
            $_p = array_combine( $_p, $event_fields[$prop]['options'] );
            return implode( ',', $_p );
        }
        return $_p;
    }
    return null;
}


function epl_format_string( $string = '' ) {
    return nl2br( stripslashes_deep( html_entity_decode( htmlspecialchars_decode( $string, ENT_QUOTES ) ) ) );
}


function epl_escape_csv_val( $val ) {

    if ( preg_match( '/,/', $val ) ) {
        return '"' . $val . '"';
    }

    return $val;
}


function epl_get_selected_price_info( $values = array(), $prices = array() ) {


    return "<pre>" . print_r( $values, true ) . "</pre>" . "<pre>" . print_r( $prices, true ) . "</pre>";
    ;
}


function epl_admin_date_display( $date = null ) {
    if ( is_null( $date ) || $date == '' || !EPL_IS_ADMIN )
        return $date;

    $_format = epl_nz( epl_get_general_setting( 'epl_admin_date_format' ), 'Y-m-d' );
    //check if timestamp, TODO - do we really need to?
    $date = ( is_numeric( $date ) && ( int ) $date == $date ) ? $date : strtotime( epl_admin_dmy_convert( $date ) );
    return epl_formatted_date( $date, $_format );
    //return gmdate( $_format, $date );
}

/*
 * strtotime cannot porcess dates in d/m/Y format.  Need to convert it to ISO d-m-Y or Euro d.m.Y before feeding into strtotime
 */


function epl_dmy_convert( $date ) {

    return epl_admin_dmy_convert( $date );
    //deprecated as of 1.3
    $date_format = get_option( 'date_format' );
    $date_format = epl_nz( epl_get_general_setting( 'epl_admin_date_format' ), 'Y-m-d' );
    if ( $date_format == 'd/m/Y' || $date_format == 'd/m/y' ) {

        list($d, $m, $y) = explode( "/", $date );

        $date = $y . '-' . $m . '-' . $d;

        //return str_replace( '/', '-', $date );
    }

    return $date;
}


function epl_admin_dmy_convert( $date ) {

    $_format = epl_nz( epl_get_general_setting( 'epl_admin_date_format' ), 'Y-m-d' );
    if ( $_format == 'd/m/Y' ) {
        return str_replace( '/', '-', $date );
    }

    return $date;
}


function epl_formatted_date( $date, $format = null, $disp_func = 'date_i18n' ) {

    if ( $date == '' )
        return;

    $disp_func = 'date_i18n';

    $_d = epl_get_date_timestamp( epl_admin_dmy_convert( $date ) );

    $date_format = (!is_null( $format )) ? $format : get_option( 'date_format' );


    return date_i18n( $date_format, $_d );
}


function epl_get_date_timestamp( $date = null ) {

    if ( is_null( $date ) || $date == '' )
        return $date;

    return ( is_numeric( $date ) && ( int ) $date == $date ) ? $date : strtotime( epl_admin_dmy_convert( $date ) );
}


function epl_time() {

    static $_t = '';

    if ( $_t != '' )
        return $_t;


    $_t = strtotime( current_time( 'mysql' ) );

    return $_t;
}


function epl_end_of_day( $time = null, $format = 'unix' ) {

    $_t = '';


    $time = (!is_null( $time )) ? $time : '23:59:59';
    if ( $format == 'unix' )
        $_t = strtotime( date_i18n( "Y-m-d $time", epl_time() ) );
    else
        $_t = date_i18n( "Y-m-d $time", epl_time() );
    return $_t;
}


function epl_timespan( $seconds = 1, $time = '', $response_type = 'array' ) {

    $r = array( 'y' => 0, 'm' => 0, 'w' => 0, 'd' => 0, 'h' => 0, 'i' => 0, 's' => 0, 'past' => 0 );
    if ( !is_numeric( $seconds ) ) {
        $seconds = 1;
    }

    if ( !is_numeric( $time ) ) {
        $time = EPL_TIME;
    }

    if ( $time <= $seconds ) {
        $seconds = 1;
        $r['past'] = 1;
    }
    else {
        $seconds = $time - $seconds;
    }

    $str = '';

    $years = floor( $seconds / 31536000 );

    if ( $years > 0 ) {
        $str .= $years . ' ' . ($years > 1) ? epl__( 'years' ) : epl__( 'year' ) . ', ';
        $r['y'] = $years;
    }

    $seconds -= $years * 31536000;
    $months = floor( $seconds / 2628000 );

    if ( $years > 0 OR $months > 0 ) {
        if ( $months > 0 ) {
            $str .= $months . ' ' . ($months > 1) ? epl__( 'months' ) : epl__( 'month' ) . ', ';
            $r['m'] = $months;
        }

        $seconds -= $months * 2628000;
    }

    $weeks = floor( $seconds / 604800 );

    if ( $years > 0 OR $months > 0 OR $weeks > 0 ) {
        if ( $weeks > 0 ) {

            $str .= $weeks . ' ' . ($weeks > 1) ? epl__( 'weeks' ) : epl__( 'week' ) . ', ';
            $r['w'] = $weeks;
        }

        $seconds -= $weeks * 604800;
    }

    $days = floor( $seconds / 86400 );

    if ( $months > 0 OR $weeks > 0 OR $days > 0 ) {
        if ( $days > 0 ) {

            $str .= $days . ' ' . ($days > 1) ? epl__( 'days' ) : epl__( 'day' ) . ', ';
            $r['d'] = $days;
        }

        $seconds -= $days * 86400;
    }

    $hours = floor( $seconds / 3600 );

    if ( $days > 0 OR $hours > 0 ) {
        if ( $hours > 0 ) {
            $str .= $hours . ' ' . epl__( 'h' ) . ', ';
            $r['h'] = $hours;
        }

        $seconds -= $hours * 3600;
    }

    $minutes = floor( $seconds / 60 );

    if ( $days > 0 OR $hours > 0 OR $minutes > 0 ) {
        if ( $minutes > 0 ) {

            $str .= $minutes . ' ' . epl__( 'm' ) . ', ';
            $r['i'] = $minutes;
        }

        $seconds -= $minutes * 60;
    }

    if ( $str == '' ) {
        $str .= $seconds . ' ' . epl__( 's' ) . ' ';
        $r['s'] = $seconds;
    }

    if ( $response_type == 'string' )
        return substr( trim( $str ), 0, -1 );

    return $r;
}


/**
 * Return an array element
 *
 * @since 1.0
 * @param int/string $item
 * @param array $array
 * @param mixed $default
 * @return array element (if exists) or default
 */
function epl_get_element( $item, $array, $default = FALSE ) {

    if ( $item === '' || is_array( $item ) || empty( $array ) || !isset( $array[$item] ) || $array[$item] === "" ) {
        return $default;
    }

    return $array[$item];
}


function epl_get_element_m( $item, $item2, $array, $default = FALSE ) {

    return (epl_get_element( $item, epl_get_element( $item2, $array ), $default ));
}


function epl_get_elements( $items, $array, $default = FALSE ) {
    $return = array();

    if ( !is_array( $items ) ) {
        $items = array( $items );
    }

    foreach ( $items as $item ) {
        if ( isset( $array[$item] ) ) {
            $return[$item] = $array[$item];
        }
        else {
            $return[$item] = $default;
        }
    }

    return $return;
}


function days_in_month( $month = 0, $year = '' ) {
    if ( $month < 1 OR $month > 12 ) {
        return 0;
    }

    if ( !is_numeric( $year ) OR strlen( $year ) != 4 ) {
        $year = date_i18n( 'Y' );
    }

    if ( $month == 2 ) {
        if ( $year % 400 == 0 OR ( $year % 4 == 0 AND $year % 100 != 0) ) {
            return 29;
        }
    }

    $days_in_month = array( 31, 28, 31, 30, 31, 30, 31, 31, 30, 31, 30, 31 );
    return $days_in_month[$month - 1];
}

/*
 * Time specific pricing
 */

/*
  function epl_is_time_specific_price() {
  global $event_details;

  return epl_get_element( '_epl_pricing_type', $event_details, 0 );
  } */

/*
 * a different time selected for each date
 */


function epl_is_date_level_time() {

    global $event_details;

    return epl_get_element( '_epl_multi_time_select', $event_details, false );
}

/*
 * different prices selected for each date
 */


function epl_is_date_level_price() {

    global $event_details;

    return epl_get_element( '_epl_multi_price_select', $event_details, false );
}


function epl_adjust_dst( $date_id, $times, $event_dates ) {

    if ( epl_is_empty_array( $times ) )
        return $times;

    global $event_details;

    $weekday = date( 'N', $event_dates[$date_id] );

    foreach ( $times['start'] as $time_id => $dates ) {

        $dst = epl_get_element_m( $time_id, '_epl_date_specific_time', $event_details, false );

        if ( $dst && !isset( $dst[$date_id] ) ) {
            unset( $times['start'][$time_id] );
            unset( $times['end'][$time_id] );
            continue;
        }
        $weekday_specific = epl_get_element_m( $time_id, '_epl_weekday_specific_time', $event_details, array() );
        if ( !empty( $weekday_specific ) && !isset( $weekday_specific[$weekday] ) ) {
            unset( $times['start'][$time_id] );
            unset( $times['end'][$time_id] );
        }
    }
    return $times;
}


function epl_is_date_specific_time( $time_id = null ) {

    global $event_details;

    return !epl_is_empty_array( epl_get_element_m( $time_id, '_epl_date_specific_time', $event_details ) );
}


function epl_is_date_specific_price( $price_id = null ) {

    global $event_details;

    return !epl_is_empty_array( epl_get_element_m( $price_id, '_epl_date_specific_price', $event_details ) );
}


function epl_is_time_specific_price( $price_id = null ) {

    global $event_details;

    return !epl_is_empty_array( epl_get_element_m( $price_id, '_epl_time_specific_price', $event_details ) );
}


function epl_is_time_optonal() {

    global $event_details;

    if ( epl_is_empty_array( $event_details['_epl_start_time'] ) )
        return true;

    if ( !empty( $event_details['_epl_time_hide'] ) ) {
        if ( (array_sum( $event_details['_epl_time_hide'] ) / count( $event_details['_epl_time_hide'] )) == 10 )
            return true;
    }

    return false;
}


function epl_is_pack_regis() {
    global $event_details;

    return (epl_get_element( '_epl_pack_regis', $event_details, 0 ) == 10);
}


function epl_is_ongoing_event() {

    global $event_details;

    return ($event_details['_epl_event_status'] == 3);
}


function epl_get_the_labels( $arr = array() ) {
    $r = '';
    $r = array_reduce( $arr, create_function( '$r,$field', '$r .= "<th>" . $field . "</th>"; return $r;' ) );
    return $r;
}


function epl_sort_by_weight( $a, $b, $prop = 'weight' ) {
    if ( (!isset( $a[$prop] ) || !isset( $b[$prop] )) || ($a[$prop] == $b[$prop]) ) {
        return 0;
    }
    return ( $a[$prop] < $b[$prop] ) ? -1 : 1;
}


function epl_check_for_it() {
    if ( defined( "EPL_PATH" ) && EPL_PATH == "events-planner-pro/" ) {
        return 'P';
    }

    return 'L';
}


//Williams, Brad; Richard, Ozh; Tadlock, Justin (2011-02-17). Professional WordPress Plugin Development (p. 289). Wrox. Kindle Edition.

function epl_get_gmap_geocode( $address ) {


    $map_url = 'http://maps.google.com/maps/api/geocode/json?address=';
    $map_url .= urlencode( $address ) . '&sensor=false';
    $request = wp_remote_get( $map_url );
    $json = wp_remote_retrieve_body( $request );

    if ( empty( $json ) )
        return false;
    $json = json_decode( $json );
    $lat = $json->results[0]->geometry->location->lat;
    $long = $json->results[0]->geometry->location->lng;


    return compact( 'lat', 'long' );
}


function epl_get_message( $code = null ) {
    global $epl_sys_messages;

    if ( $epl_sys_messages == '' )
        EPL_Base::get_instance()->load_config( 'sys-messages' );

    return epl_get_element( $code, $epl_sys_messages );
}

/* Fullcalendar date format */


function epl_fc_date_format() {

    $_format = epl_nz( epl_get_general_setting( 'epl_admin_date_format' ), 'm/d/Y' );

    $_format = str_replace( array( 'Y', 'm', 'd' ), array( 'yyyy', 'MM', 'dd' ), $_format );

    return $_format;
}


function epl_is_multi_location() {
    global $event_details;

    return (!epl_is_empty_array( epl_get_element( '_epl_date_location', $event_details ) ) && count( epl_get_element( '_epl_date_location', $event_details ) ) > 1);
}


function epl_get_url( $ssl = null ) {

    $ssl_set = epl_get_setting( 'epl_registration_options', 'epl_regis_enable_ssl' );


    //$_base = ((!empty( $_SERVER['HTTPS'] ) && stripos( 'off', $_SERVER['HTTPS'] ) !== false || $_SERVER['SERVER_PORT'] == 443) || ($ssl_set == 10)) ? home_url( '', 'https' ) : home_url();
    //$_base .= $_SERVER['REQUEST_URI'];
    //$_q = ($_SERVER["QUERY_STRING"]) ? '?' . $_SERVER["QUERY_STRING"] : "";
    // from redirect_canonical()
    //fix for severs runnning iis
    if ( !isset( $_SERVER['REQUEST_URI'] ) || stripos( $_SERVER['SERVER_SOFTWARE'], 'iis' ) !== false ) {

        /* $_SERVER['REQUEST_URI'] = substr( $_SERVER['PHP_SELF'], 0 );
          if ( isset( $_SERVER['QUERY_STRING'] ) && $_SERVER['QUERY_STRING'] != "" ) {
          //$_SERVER['REQUEST_URI'] .= '?' . $_SERVER['QUERY_STRING'];
          } */

        //switching to HTTP_X_ method as for some reason 
        if ( isset( $_SERVER['HTTP_X_ORIGINAL_URL'] ) )
            $_SERVER['REQUEST_URI'] = $_SERVER['HTTP_X_ORIGINAL_URL'];
    }

    $requested_url = (is_ssl() || (!is_admin() && $ssl_set) && $ssl !== false) ? 'https://' : 'http://';
    $requested_url .= $_SERVER['HTTP_HOST'];
    $requested_url .= $_SERVER['REQUEST_URI'];

    return $requested_url;
}


function epl_get_profile_url( $param ) {
    
}


function epl_has_multi_payment_choices() {
    global $event_details;

    return (count( epl_get_element( '_epl_payment_choices', $event_details, array() ) ) > 1);
}


function epl_regis_flow() {
    return 1;
    global $event_details, $epl_is_waitlist_flow;

    if ( $epl_is_waitlist_flow == true )
        return 2;

    return epl_get_element( '_epl_event_regis_flow', $event_details, 1 );
}


function epl_is_multi_array( $array ) {

    $_c = count( $array );

    if ( $_c > 0 && ($_c != count( $array, COUNT_RECURSIVE )) )
        return true;

    return false;
}


function epl_is_empty_array( $array ) {

    if ( !is_array( $array ) || is_null( $array ) || $array == '' || count( $array ) == 0 )
        return true;

    $r = null;
    $r = (count( $array ) > 0) ? array_filter( $array, 'epl_trim' ) : null;

    if ( empty( $r ) )
        return true;

    return false;
}


function epl_trim( $v ) {

    if ( is_array( $v ) || is_object( $v ) )
        return $v;

    return trim( $v );
}


function epl_do_messages( $events_in_cart = null ) {

    if ( EPL_IS_ADMIN )
        return;

    global $event_details;
    $events_in_cart = $events_in_cart ? $events_in_cart : ( array ) EPL_registration_model::get_instance()->get_events_in_cart();

    if ( empty( $events_in_cart ) )
        return;

    global $epl_current_action, $epl_current_message, $epl_current_message_type;
    foreach ( $events_in_cart as $_event_id => $data ) {

        if ( !empty( $event_id ) && $event_id != $_event_id )
            continue;

        setup_event_details( $_event_id );
        $messages = epl_get_element( '_epl_message', $event_details );

        foreach ( ( array ) $messages as $k => $message ) {
            if ( isset( $event_details['_epl_message_location'] ) && epl_get_element( $k, $event_details['_epl_message_location'] ) != '' ) {

                $epl_current_action = $event_details['_epl_message_location'][$k];
                $epl_current_message = $message;
                $epl_current_message_type = $event_details['_epl_message_type'][$k];

                epl_display_message( $epl_current_action );

                add_action( $event_details['_epl_message_location'][$k], 'epl_display_message' );
            }
        }
    }
}


function epl_remove_array_keys( $keys = array(), $array = array() ) {

    return array_diff( $keys, $array );
}


function epl_sort_array_by_array( &$array, $order_array ) {

    if ( empty( $array ) || empty( $order_array ) )
        return null;
    $ordered = array();
    foreach ( $order_array as $key => $v ) {

        if ( isset( $array[$key] ) ) {

            $ordered[$key] = $array[$key];
            unset( $array[$key] );
        }
    }

    $array = $ordered + $array;
}


function epl_sort_posts( &$posts, $order_by ) {

    if ( strpos( $order_by, ',' === false ) )
        return;

    $order_by = explode( ',', $order_by );

    $ordered = array_flip( $order_by );
    foreach ( $posts as $post ) {
        $ordered[$post->ID] = $post;
    }

    $posts = array_values( $ordered ); //can also use array_merge($ordered);
}


function epl_display_message( $epl_current_action ) {
    global $epl_current_action, $epl_current_message, $epl_current_message_type;
    static $v = array();
    $v[$epl_current_action] = "<div class='{$epl_current_message_type}'>" . nl2br( stripslashes_deep( $epl_current_message ) ) . "</div>";

    if ( isset( $v[current_filter()] ) )
        echo $v[current_filter()];
}


function epl_sortcode_pages() {
    $pages = get_pages();
    $r = array();
    foreach ( $pages as $page ) {
        if ( stripos( $page->post_content, '[events_planner' ) !== false ) {

            $r[$page->ID] = $page->post_title;
        }
    }
    return $r;
}


function epl_get_sortcode_url() {
    static $pl = null;

    if ( $pl )
        return $pl;
    $ssl_set = epl_get_setting( 'epl_registration_options', 'epl_regis_enable_ssl' );
    $sc = epl_get_shortcode_page_id();

    if ( $sc ) {
        $pl = get_permalink( $sc );
    }
    else {

        $pages = get_pages();

        foreach ( $pages as $page ) {
            if ( !$pl && stripos( $page->post_content, '[events_planner' ) !== false ) {

                $pl = get_permalink( $page->ID );
            }
        }
    }

    $protocol = (is_ssl() || (!is_admin() && $ssl_set)) ? 'https://' : 'http://';
    return str_ireplace( 'http://', $protocol, $pl );
}


function epl_del_event_list_transient() {
    global $wpdb;
}


function epl_term_list( $raw = false ) {

    $terms = epl_object_to_array( get_terms( 'epl_event_categories' ) );

    if ( $raw )
        return $terms;

    $_o = array();
    $f = array();

    foreach ( $terms as $k => $v ) {
        $_o[$v['slug']] = $v['name'];
    }

    return $_o;
}


function epl_terms_field( $args = array() ) {
    $r = EPL_util::get_instance()->epl_terms_field( $args );

    return $r['field'];
}


function epl_get_shortcode_page_id() {
    return epl_get_setting( 'epl_general_options', 'epl_shortcode_page_id' );
}


function epl_get_shortcode_page_permalink() {
    return get_permalink( epl_get_shortcode_page_id() );
}


function epl_object_to_array( $object ) {
    if ( !is_object( $object ) && !is_array( $object ) ) {
        return $object;
    }
    if ( is_object( $object ) ) {
        $object = get_object_vars( $object );
    }
    return array_map( 'epl_object_to_array', $object );
}


function epl_get_user_field( $field = null ) {


    if ( EPL_IS_ADMIN || !is_user_logged_in() || is_null( $field ) )
        return null;

    static $user_data = array();

    if ( epl_is_empty_array( $user_data ) ) {

        $user_data = get_userdata( get_current_user_id() );
    }

    $user_meta = EPL_common_model::get_instance()->get_user_meta_all( get_current_user_id() );

    $r = epl_get_element( $field, epl_get_element( 'wp_s2member_custom_fields', $user_meta ) );

    if ( $r )
        return $r;



    return $user_data->$field;
}


function epl_is_eligible_for_member_price( $price_id ) {
    global $event_details;
    $member_only = (epl_get_element_m( $price_id, '_epl_price_member_only', $event_details, 0 ) == 10);
    $member_price = epl_get_element_m( $price_id, '_epl_member_price', $event_details, '' );

    if ( is_user_logged_in() && ($member_only || $member_price != '') ) {
        return true;
    }
    return false;
}


function epl_trunc( $phrase, $max_words = 50 ) {
    $phrase_array = explode( ' ', $phrase );
    if ( count( $phrase_array ) > $max_words && $max_words > 0 )
        $phrase = implode( ' ', array_slice( $phrase_array, 0, $max_words ) ) . '...';
    return $phrase;
}


//search array for a value recursively
function epl_in_array_r( $needle, $haystack, $strict = true ) {
    foreach ( $haystack as $item ) {
        if ( ($strict ? $item === $needle : $item == $needle) || (is_array( $item ) && epl_in_array_r( $needle, $item, $strict )) ) {
            return true;
        }
    }

    return false;
}


function epl_array_flatten( $array, $preserve_keys = 0, &$out = array() ) {
    if ( !is_array( $array ) )
        return $array;
    foreach ( $array as $key => $child )
        if ( is_array( $child ) )
            $out = epl_array_flatten( $child, $preserve_keys, $out );
        elseif ( $preserve_keys + is_string( $key ) > 1 )
            $out[$key] = $child;
        else
            $out[] = $child;
    return $out;
}


function epl_array_flatten_old( $array = array() ) {

    if ( !is_array( $array ) )
        return $array;

// Works great but will need to research more
    if ( version_compare( PHP_VERSION, '5.1.0' ) >= 0 )
        return iterator_to_array( new RecursiveIteratorIterator( new RecursiveArrayIterator( $array ) ), 0 );

    $obj_tmp = ( object ) array( 'flat' => array() );
//https://bugs.php.net/bug.php?id=52719
    array_walk_recursive( $array, create_function( '&$v, $k, &$t', '$t->flat[] = $v;' ), $obj_tmp );
    return $obj_tmp->flat;
}

/* old, not being used */


function epl_array_value_r( $array = array() ) {

    static $r = array();

    foreach ( ( array ) $array as $k => $v ) {
        if ( is_array( $v ) ) {
            epl_array_value_r( $v );
        }
        else {
            $r[$k] = $v;
        }
    }

    return $r;
}


function epl_get_protocol() {
    return (is_ssl() ? 'https' : 'http' );
}

if ( !function_exists( '__epl_server_details' ) ) {


    function __epl_server_details() {

        echo "<!--" . print_r( $_SERVER, true ) . '-->';
    }

}


function epl_is_ok_for_waitlist() {
    global $event_details;

    if ( epl_is_waitlist_approved() )
        return false;

    $r = (epl_get_element( '_epl_wailist_active', $event_details, 0 ) == 10);

    if ( $r === true ) {

        $r = epl_waitlist_spaces_open();
        if ( $r !== false ) {
            global $epl_is_waitlist_flow;
            $epl_is_waitlist_flow = true;
        }
    }

    return $r;
}


function epl_is_waitlist_full() {
    global $event_details;
}


function epl_is_waitlist_flow() {
    global $epl_is_waitlist_flow, $regis_details;

    if ( epl_get_element( 'epl_wl_flow', $_POST, 0 ) == 1 )
        return true;
    if ( !epl_is_waitlist_record() && EPL_IS_ADMIN )
        return false;
    $regis_status = get_the_regis_status( null, true );
    if ( $regis_status == 2 || $regis_status == 5 ) {
        return false;
    }
    if ( (!epl_is_waitlist_approved() && !epl_is_waitlist_session_approved() && ($epl_is_waitlist_flow == true || epl_get_element( 'epl_wl_flow', $_REQUEST, 0 ) == 1 || get_the_regis_status( null, true ) == 20) ) ) {
        return true;
    }
}


function epl_is_waitlist_record() {
    global $epl_is_waitlist_flow, $regis_details;

    return (get_the_regis_status( null, true ) == 20 );
}


function epl_is_waitlist_approved() {
    global $epl_is_waitlist_flow, $regis_details;

    return (epl_get_element( '_epl_waitlist_status', $regis_details, 0 ) == 10 );
}


function epl_is_waitlist_session_approved() {
    global $epl_is_waitlist_flow, $regis_details;

    return (epl_get_element_m( 'waitlist_approved', '__epl', $_SESSION ) >= 1);
}


function epl_waitlist_flow_trigger() {
    add_action( 'epl_regis_form_bottom_message', create_function( '', 'echo "<input type=\'hidden\' name=\'epl_wl_flow\' value=\'1\' />" ;' ) );
}


function epl_get_waitlist_approved_url( $force = false, $mode = 'wl' ) {

    if ( !$force && (epl_get_element( 'epl_rid', $_GET ) || !EPL_IS_ADMIN) )
        return;

    global $event_details, $regis_details;

    $regis_id = $regis_details['ID'];
    $event_id = $event_details['ID'];

    $url_vars = array(
        'page_id' => $page_id,
        'epl_action' => epl_regis_flow() <= 2 ? 'process_cart_action' : 'regis_form',
        'cart_action' => 'add',
        'event_id' => ($event_id) ? $event_id : $event_details['ID'],
        'epl_event' => false,
        'epl_rid' => $regis_id,
        'epl_r_m' => $mode,
        'epl_wlh' => MD5( $regis_details['post_date'] )
    );

    $base_url = get_the_register_button( $event_id, true );

    $regis_url = add_query_arg( $url_vars, $base_url );
    return $regis_url;
    //wp_redirect( $regis_url );
    //die();
}


function epl_is_valid_url_hash( $check_approved = true ) {
    $hash = epl_get_element( 'epl_wlh', $_REQUEST );
    if ( !$hash )
        return false;
    if ( $check_approved && epl_is_waitlist_session_approved() )
        return true;


    global $regis_details;

    if ( epl_is_empty_array( $regis_details ) )
        EPL_common_model::get_instance()->setup_regis_details( intval( epl_get_element( 'epl_rid', $_REQUEST ) ) );

    return ($hash == MD5( $regis_details['post_date'] ));
}


function epl_waitlist_spaces_open() {

    global $event_details, $wpdb, $current_waitlist_count;

    $event_id = $event_details['ID'];

    $max_spaces = intval( epl_get_element( '_epl_waitlist_max', $event_details ) );

    EPL_common_model::get_instance()->setup_current_waitlist_count();

    if ( $max_spaces > 0 ) {

        $avail = $max_spaces - $current_waitlist_count;

        if ( $avail > 0 )
            return $avail;

        return false;
    }

    return true;
}


function epl_waitlist_enough_spaces( $event_id = null ) {
    global $event_details, $wpdb, $current_waitlist_count, $current_att_count;

    $avail = epl_waitlist_spaces_open();

    //will be either true (unlimited), false (no more spaces), or an integer of what's left open
    if ( is_bool( $avail ) )
        return $avail;
    $event_id = $event_id ? $event_id : $event_details['ID'];
    $cart_totals = EPL_registration_model::get_instance()->calculate_cart_totals();

    $cart_total_att = epl_get_element_m( $event_id, 'total', $cart_totals[$event_id]['_att_quantity'] );

    return (($avail - $cart_total_att) >= 0);
}


function epl_is_waitlist_link_expired() {
    global $regis_details;
    $time_limit = epl_waitlist_approved_regis_time_limit();
    global $event_details;

    if ( !$time_limit )
        return false;
    $email_time = epl_get_element( '_epl_waitlist_email_time', $regis_details );

    return EPL_TIME > strtotime( "+ $time_limit hour", $email_time );
}


function epl_waitlist_approved_until() {
    global $regis_details;
    if ( !epl_is_waitlist_approved() )
        return '';
    $time_limit = epl_waitlist_approved_regis_time_limit();
    global $event_details;

    if ( !$time_limit )
        return epl__( "No Expiration" );
    $email_time = get_post_meta( $regis_details['ID'], '_epl_waitlist_email_time', true );

    $expiration_time = date_i18n( 'l, M d Y H:i', strtotime( "+ $time_limit hour", $email_time ) );

    return $expiration_time;
}


function epl_waitlist_approved_regis_time_limit() {

    return epl_get_waitlist_setting( '_epl_waitlist_approved_regis_time_limit' );
}


function epl_get_waitlist_setting( $setting ) {
    global $event_details;

    return epl_get_element( $setting, $event_details );
}


function epl_get_email_template_list( $path = EPL_EMAIL_TEMPLATES_FOLDER ) {

    $r = array();
    if ( $handle = opendir( $path ) ) {

        while ( false !== ($entry = readdir( $handle )) ) {
            if ( $entry != "." && $entry != ".." )
                $r[$entry] = ucwords( $entry );
        }

        closedir( $handle );
        return $r;
    }
}


function epl_create_message( $content, $message_type = 'info' ) {
    return "<div class='epl_{$message_type}'>$content</div>";
}


function epl_is_single( $act = 'get' ) {
    global $epl_is_single;

    if ( $act == 'set' )
        $epl_is_single = true;

    return (is_single() || $epl_is_single === true);
}


function epl_get_send_email_button( $regis_id = false, $event_id = false, $img = false ) {

    $send_email_arr = array(
        'epl_action' => 'get_the_email_form',
        'epl_controller' => 'epl_report_manager',
        'event_id' => $event_id,
        'post_ID' => $regis_id
    );

    $anchor = epl__( 'Send Email' );
    $class = 'button-primary';

    if ( $img ) {
        $anchor = '<img src="' . EPL_FULL_URL . 'images/email.png" />';
        $class = '';
    }

    return epl_anchor( add_query_arg( $send_email_arr, epl_get_url() ), $anchor, null, "class='epl_send_email_form_link $class' data-post_ID='$regis_id' data-event_id='$event_id'" );
}


function epl_setup_regis_details( $regis_id = null ) {

    EPL_common_model::get_instance()->setup_regis_details( $regis_id );
}

/* this is also in the recurrence model */


function epl_get_date_difference( $start_date, $end_date, $format = 'day' ) {

    if ( $start_date == '' || $end_date == '' )
        return false;

    /*
     * Can't remember where I got this.  Would love to credit the author.
     */

    /* $startdate = explode( "-", $start_date );

      $enddate = explode( "-", $end_date ); */

    $seconds_difference = $end_date - $start_date;

    switch ( $format )
    {

        case 'minute': // Difference in Minutes
            return floor( $seconds_difference / 60 );

        case 'hour': // Difference in Hours
            return floor( $seconds_difference / 60 / 60 );

        case 'day': // Difference in Days
            return floor( $seconds_difference / 60 / 60 / 24 );

        case 'week': // Difference in Weeks
            return floor( $seconds_difference / 60 / 60 / 24 / 7 );

        case 'month': // Difference in Months
            //return floor( $seconds_difference / 60 / 60 / 24 / 7 / 4 );
            $diff = abs( strtotime( $end_date ) - strtotime( $start_date ) );

            $years = floor( $diff / (365 * 60 * 60 * 24) );

            return floor( ($diff - $years * 365 * 60 * 60 * 24) / (30 * 60 * 60 * 24) );

        default: // Difference in Years
            return floor( $seconds_difference / 365 / 60 / 60 / 24 );
    }
}


function epl_get_grid_array( $shortcode_atts ) {
    $grid_arr = array();
    if ( $_grids = epl_get_element( 'grid', $shortcode_atts, false ) ) {

        if ( strpos( $_grids, ',' ) !== false ) {

            $_grids = explode( ',', $_grids );

            foreach ( $_grids as $event_id => $grid ) {
                $grids = explode( '|', $grid );
                $grid_arr[$grids[0]] = $grids[1];
            }
        }
        else
            $grids = explode( '-', $_grids );
        $grid_arr[$grids[0]] = $grids[1];
    }

    return $grid_arr;
}

/* used when copying discount codes from one event to another.  rekeying to eliminate conflict */


function epl_rekey_array( $array, $new_keys ) {

    foreach ( $array as $k => &$v ) {
        if ( is_array( $v ) ) {
            $v = array_combine( $new_keys, $v );
        }
    }
    return $array;
}


function epl_get_mailchimp_lists() {

    static $opt = array();

    if ( !empty( $opt ) )
        return $opt;
    $key = trim( epl_get_setting( 'epl_api_option_fields', 'epl_mc_key' ) );

    if ( $key ) {


        $api = EPL_base::get_instance()->load_library( 'mailchimpSF_MCAPI', true, $key );


        $lists = $api->lists( array(), 0, 100 );
        $lists = $lists['data'];


        if ( count( $lists ) == 0 ) {
            return array();
        }
        else {

            foreach ( $lists as $list ) {
                $option = get_option( 'mc_list_id' );
                $opt[$list['id']] = esc_html( $list['name'] );
            }
        }
    }
    return $opt;
}


function epl_mailchimp_subscribe() {


    if ( !epl_is_addon_active( 'ASDFAWEEFADSF' ) )
        return;
    global $event_details, $customer_email, $customer_name;
    $default_action = epl_get_setting( 'epl_api_option_fields', 'epl_mc_action' );

    $sign_up = epl_get_element( '_epl_offer_notification_sign_up', $event_details, '' );

    if ( $sign_up == '' )
        $sign_up = $default_action == 1 ? epl_get_setting( 'epl_api_option_fields', '_epl_mc_offer_notification_sign_up' ) : 0;

    if ( $sign_up == 0 || $default_action == 0 )
        return;

    $who_to_sign_up = epl_get_setting( 'epl_registration_options', 'epl_send_customer_confirm_message_to', 1 );

    if ( $who_to_sign_up == 2 || !epl_has_primary_forms() )
        $who_to_sign_up = 2;


    $erm = EPL_registration_model::get_instance();

    $list_id = epl_get_element( '_epl_notification_list', $event_details, false );

    if ( !$list_id && $default_action == 1 )
        $list_id = epl_get_setting( 'epl_api_option_fields', '_epl_mc_default_list' );


    $double_optin = ( bool ) epl_get_setting( 'epl_api_option_fields', 'epl_mc_double_opt_in' );
    $send_welcome = ( bool ) epl_get_setting( 'epl_api_option_fields', 'epl_mc_send_welcome_email' );


    $key = trim( epl_get_setting( 'epl_api_option_fields', 'epl_mc_key' ) );

    $api = EPL_base::get_instance()->load_library( 'mailchimpSF_MCAPI', true, $key );

    $go = false;

    $newsletter_signup = epl_get_element_m( 'newsletter_signup', $erm->regis_id, $erm->current_data, array() );
    
    if ( $sign_up == 1 && (array_sum( $newsletter_signup ) > 0 ) ) {
        $go = true;
    }
    elseif ( $sign_up == 3 )
        $go = true;
    

    if ( $list_id && $go && count( $customer_email ) > 0 ) {
        $customer_email = array_unique($customer_email);
        foreach ( $customer_email as $n => $email ) {

            if ( $sign_up < 3 && $newsletter_signup[$n] == 0 )
                continue;
            $email = $email;
            $merge_vars = array(
                'FNAME' => epl_get_element_m( 'first_name', $n, $customer_name ),
                'LNAME' => epl_get_element_m( 'last_name', $n, $customer_name )
            );

            $r = $api->listSubscribe( $list_id, $email, $merge_vars, $email_type = 'html', $double_optin, false, true, $send_welcome );

        }
    }
}


function epl_has_primary_forms() {
    global $event_details;

    if ( epl_sc_is_enabled() && epl_get_setting( 'epl_sc_options', 'epl_sc_forms_to_use' ) == 1 ) {
        $f = epl_get_setting( 'epl_sc_options', 'epl_sc_primary_regis_forms' );
        return !empty( $f );
    }

    return !(empty( $event_details['_epl_primary_regis_forms'] ));
}


function epl_has_attendee_forms() {
    global $event_details;

    if ( epl_sc_is_enabled() && epl_get_setting( 'epl_sc_options', 'epl_sc_forms_to_use' ) == 1 ) {
        $f = epl_get_setting( 'epl_sc_options', 'epl_sc_addit_regis_forms' );
        return !empty( $f );
    }

    return !empty( $event_details['_epl_addit_regis_forms'] );
}


function epl_get_attendee_form_value( $form = 'ticket_buyer', $field = null ) {

    if ( !$form || !$field )
        return null;

    return EPL_registration_model::get_instance()->get_attendee_form_value( $form, $field );
}


function _epl_construct_form_default_value( $field_atts ) {
    if ( epl_user_is_admin() )
        return null;
    return epl_get_user_field( epl_get_element( 'wp_user_map', $field_atts, null ) );
}


function epl_user_is_admin() {

    if ( is_user_logged_in() && current_user_can( 'activate_plugins' ) )
        return true;

    return false;
}


function epl_admin_override() {

    if ( epl_get_regis_setting( 'epl_enable_admin_override' ) == 10 && epl_user_is_admin() )
        return true;

    return false;
}


function epl_delete_transient( $key = null ) {
    global $wpdb;

    if ( wp_cache_get( 'epl_transient_deleted' ) !== false )
        return;

    $wpdb->query( "DELETE FROM {$wpdb->options} WHERE option_name LIKE '%epl_transient_%'" );

    wp_cache_set( 'epl_transient_deleted', true );
}


function epl_set_cart_cookie( $act = 'set' ) {

    if ( !headers_sent() ) {
        $exp = time() + 3600;
        if ( $act == 'del' )
            $exp -= 7200;
        setcookie( "epl_items_in_cart", 1, $exp, '/' );
    }
}


function epl_get_cart_cookie_val( $val = '' ) {
    $key = 'key';

    return maybe_unserialize( rtrim( mcrypt_decrypt( MCRYPT_RIJNDAEL_256, md5( $key ), base64_decode( $_COOKIE['__epl'] ), MCRYPT_MODE_CBC, md5( $key ) ), "\0" ) );
}


function epl_get_num_events_in_cart() {
    
    return (count( EPL_registration_model::get_instance()->get_events_in_cart() ));
}


function epl_get_the_content_with_formatting( $more_link_text = '(more...)', $stripteaser = 0, $more_file = '' ) {
    $content = get_the_content( $more_link_text, $stripteaser, $more_file );
    $content = apply_filters( 'the_content', $content );
    $content = str_replace( ']]>', ']]&gt;', $content );
    return $content;
}


function epl_get_att_count( array $args ) {

    global $event_details, $current_att_count;
    $default = '';
    extract( $args );

    $event_id = $event_details['ID'];
    $refresh = true;
    if ( epl_is_empty_array( $current_att_count ) || $refresh ) {
        $current_att_count = EPL_report_model::get_instance()->get_attendee_counts( $event_id );
        //EPL_common_model::get_instance()->get_current_att_count( $event_id );
    }
    switch ( $for )
    {
        case 'event':
            return epl_get_element( '_total_att_' . $event_id, $current_att_count, $default );

        case 'date':
            return epl_get_element( '_total_att_' . $event_id . "_date_{$date_id}", $current_att_count, $default );

        case 'time':
            return epl_get_element( '_total_att_' . $event_id . "_time_{$date_id}_{$time_id}", $current_att_count, $default );
        case 'price':
            return epl_get_element( '_total_att_' . $event_id . "_price_{$date_id}_{$time_id}_{$price_id}", $current_att_count, $default );
    }
}


function epl_get_date_capacity( $id, $default = '' ) {
    return epl_get_capacity( array( 'for' => 'date', 'date_id' => $id, 'default' => $default ) );
}


function epl_get_time_capacity( $id, $default = '' ) {
    return epl_get_capacity( array( 'for' => 'time', 'time_id' => $id, 'default' => $default ) );
}


function epl_get_price_capacity( $id, $default = '' ) {
    return epl_get_capacity( array( 'for' => 'price', 'price_id' => $id, 'default' => $default ) );
}


function epl_get_capacity( array $args ) {

    global $event_details;
    $default = '';
    extract( $args );

    $event_id = $event_details['ID'];

    switch ( $for )
    {

        case 'date':
            return epl_get_element_m( $date_id, '_epl_date_capacity', $event_details, $default );

        case 'time':
            return epl_get_element_m( $time_id, '_epl_time_capacity', $event_details, $default );
        case 'price':
            return epl_get_element_m( $price_id, '_epl_time_capacity', $event_details, $default );
    }
    return $default;
}


function epl_get_date_avail_spaces( $event_id, $date_id ) {

    $cap = epl_get_date_capacity( $date_id, '' );
    $regis = epl_get_att_count( array( 'for' => 'date', 'event_id' => $event_id, 'date_id' => $date_id, 'default' => 0 ) );
    return epl_avail_spaces( $cap, $regis );
}


function epl_avail_spaces( $cap, $num_regis ) {

    if ( $cap === '' || $cap == 0 )
        return $cap;

    $avail = intval( $cap - epl_nz( $num_regis, 0 ) );
    return $avail <= 0 ? 0 : $avail;
}


function epl_regis_plugin_version() {
    global $regis_details;
    return preg_replace( '/(\.\w\d+)/', '', epl_get_element( '_epl_plugin_version', $regis_details, EPL_PLUGIN_VERSION ) );
}


/**
 * returns value from snapshot based on keys supplied
 *
 * long description
 *
 * @since 1.4
 * @param array $args - array of what the call wants
 * @return string || null
 */
function epl_get_from_snapshot( $args = array() ) {
    global $event_snapshot;

    switch ( $args['section'] )
    {
        case 'date':
            return epl_get_element_m( $args['index'], 'date', epl_get_element_m( $args['date_id'], $args['event_id'], $event_snapshot ), null );
    }
}


function epl_array_values_recursive( $arr, &$_arr = array() ) {

    foreach ( $arr as $key => $value ) {
        if ( is_array( $value ) ) {
            epl_array_values_recursive( $value, $_arr );
        }
        else
            $_arr[] = $value;
    }


    return $_arr;
}


function epl_switch( $index, $arr ) {
    return $arr[$index - 1];
}


//always active by default as of 2.0
function epl_get_token( $v = null ) {
    //if ( epl_get_regis_setting( 'epl_regis_add_url_token' ) != 10 )
    //  return false;
    global $regis_details;

    $v = is_null( $v ) ? $regis_details['ID'] . $regis_details['post_date'] : $v;

    $t = md5( $v . 'SDTeWER12799!' );

    return $t;
}


function epl_check_token( $v = null, $forec_check = false ) {
    if ( epl_user_is_admin() )
        return true;
    if ( epl_get_regis_setting( 'epl_regis_add_url_token' ) != 10 )
        return true;
    if ( !isset( $_GET['epl_token'] ) )
        return false;

    return ($_GET['epl_token'] == epl_get_token( $v ));
}


function epl_prefix( $prefix = '', $term = '' ) {
    $term = trim( $term );
    return (trim( $term != '' )) ? $prefix . $term : '';
}


function epl_suffix( $suffix = '', $term = '' ) {
    $term = trim( $term );
    return ($term != '') ? $term . $suffix : '';
}


function epl_wrap( $term = '', $before = '', $after = '' ) {
    return $before . $term . $after;
}


function epl_make_array_from_string( $string, $delim = ',' ) {

    if ( stripos( $string, $delim ) )
        return explode( $delim, $string );

    return $string != '' ? array( $string ) : '';
}


function epl_get_roles_arr() {
    global $wp_roles;

    static $arr = array();

    if ( count( $wp_roles ) == 0 )
        return $arr;
    if ( epl_is_empty_array( $arr ) ) {
        foreach ( $wp_roles->roles as $key => $role )
            $arr[$key] = $role['name'];
    }

    return $arr;
}


function epl_event_fully_expired() {
    global $event_details;

    $expired = false;

    if ( (end( $event_details['_epl_end_date'] ) + 604800) < EPL_DATE ) {
        $expired = true;
    }
    elseif ( end( $event_details['_epl_start_date'] ) < EPL_DATE ) {
        $expired = true;
    }
    elseif ( $event_details['_epl_event_status'] == 3 && end( $event_details['_epl_end_date'] ) < EPL_DATE ) {
        $expired = true;
    }
    return $expired;
}


function epl_process_fields_for_display( $fields, $avail_fields = null ) {

    if ( is_null( $avail_fields ) )
        $avail_fields = epl_get_list_of_available_fields();

    foreach ( $fields as $field_id => &$value ) {

        if ( $avail_fields[$field_id]['input_type'] == 'select' || $avail_fields[$field_id]['input_type'] == 'radio' ) {

            $value = (isset( $avail_fields[$field_id]['epl_field_choice_text'][$value] ) && $avail_fields[$field_id]['epl_field_choice_text'][$value] !== '') ? $avail_fields[$field_id]['epl_field_choice_text'][$value] : $value;
        }
        elseif ( $avail_fields[$field_id]['input_type'] == 'checkbox' ) {
            $value = epl_make_array_from_string( $value );

            if ( !epl_is_empty_array( $value ) ) {
                if ( !epl_is_empty_array( $avail_fields[$field_id]['epl_field_choice_value'] ) )
                    $value = (implode( ',', array_intersect_key( $avail_fields[$field_id]['epl_field_choice_value'], array_flip( $value ) ) ));
                else {
                    $value = (implode( ',', array_intersect_key( $avail_fields[$field_id]['epl_field_choice_text'], array_flip( $value ) ) ));
                }
            }
        }
        else
            $value = html_entity_decode( htmlspecialchars_decode( $value ) );
    }

    return $fields;
}


function epl_upload_dir_path( $short = false ) {
    static $path = null;

    if ( !$path ) {
        $uploads = wp_upload_dir();
        $path = $uploads['basedir'] . '/epl_uploads/';
        if ( $short )
            $path = '/wp-content/uploads/epl_uploads/';
    }
    return $path;
}


function epl_ok_to_show_date() {

    $end_date = $event_details['_epl_end_date'][$date_key];

    //if in the past and not ongoing, skip
    if ( $start_date < EPL_DATE && $event_status != 3 )
        return false;

    //if ongoing and end date in the past, skip
    if ( !$show_all && $event_status == 3 ) {

        $ok = (($start_date >= $start_of_month && $start_date <= $end_of_month) && ( EPL_DATE < $end_date));


        if ( !$ok || $end_date < EPL_DATE )
            return false;
    }


    if ( (!$show_all) && $start_date < $start_of_month || ( $start_date > $end_of_month) )
        return false;
}


//http://docs.appthemes.com/tutorials/wordpress-check-user-role-function/
function epl_check_user_role( $role, $user_id = null ) {

    if ( is_numeric( $user_id ) )
        $user = get_userdata( $user_id );
    else
        $user = wp_get_current_user();

    if ( empty( $user ) )
        return false;

    return in_array( $role, ( array ) $user->roles );
}

class EPL_stop_watch {

    private static $total;


    public static function start() {
        self::$total = microtime( true );
    }


    public static function elapsed() {
        return microtime( true ) - self::$total . ' seconds';
    }

}


function epl_memory_get_usage( $p = '' ) {
    return $p . ' > ' . (memory_get_usage( true ) / 1048576 );
}

?>