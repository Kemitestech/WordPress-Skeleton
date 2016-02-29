<?php


function epl_prices_exp_dates( $price_key = null, $value = null ) {
    global $event_details;
    //$r = timespan( EPL_TIME, strtotime( epl_dmy_convert( $event_details['_epl_price_date_to'][$price_key] ) . ' 23:59:59' ) );
    $r = ok_to_show_qty_dd( $price_key,$value );

    return $r;

    return epl_display_date_diff( $r );
}

add_filter( 'epl_prices_exp_dates', 'epl_prices_exp_dates', 10, 2 );


function epl_display_date_diff( $data ) {

    $r = '';

    if ( $data['w'] >= 1 || $data['m'] >= 1 || $data['y'] >= 1 ) {
        $r = epl_formatted_date( $data['_date'] );
    }
    else {

        if ( $data['d'] > 0 )
            $r = $data['d'] . epl__( 'd' ) . ' ';
        if ( $data['h'] > 0 )
            $r .= $data['h'] . epl__( 'h' ) . ' ';
        if ( $data['i'] > 0 )
            $r .= $data['i'] . epl__( 'm' ) . '';
    }
    return $r;
}


function ok_to_show_qty_dd( $price_key = null, $qty = 0 ) {

    if ( is_null( $price_key ) )
        return false;
    
    global $event_details;
   
    $r = array(
        'ok' => 1,
        'msg' => epl__( '-' )
    );

    $price_start_date = epl_get_element( $price_key, $event_details['_epl_price_date_from'] );


    if ( $price_start_date ) {

        $diff = epl_timespan( EPL_TIME, strtotime( "00:00:00", epl_get_date_timestamp( $price_start_date ) ) );
        $diff['_date'] = $price_start_date;
        //epl_log( "debug", "<pre>" . print_r($diff, true ) . "</pre>" );

        $r['msg'] = epl__( 'Available on' ) . ' ' . epl_formatted_date( $price_start_date );

        if ( $diff['past'] == 0 ) {
            $r['ok'] = 0;
        }
    }

    $price_end_date = epl_get_element( $price_key, $event_details['_epl_price_date_to'] );

    if ( $r['ok'] == 1 && $price_end_date ) {

        $diff = epl_timespan( EPL_TIME, strtotime( "23:59:59", epl_get_date_timestamp( $price_end_date ) ) );
        $diff['_date'] = $price_end_date;
        $r['ok'] = 0;
        $r['msg'] = epl_formatted_date( $price_end_date ) . ' ' . epl__( 'Ended' );

        if ( $diff['past'] == 0 ) {
            $r['ok'] = 1;
            $r['msg'] = epl_display_date_diff( $diff );
        }
        
        if($qty > 0)
            $r['ok'] = 1;
    }
    //epl_log( "debug", "<pre>" . print_r( $diff, true ) . "</pre>" );
    return $r;
}














































































































/* -------------------------------------------------------------------------------------------
 * Hi there.  Before you turn off the following functions, please keep in mind that
 * I have put a lot of my time and efforts to get the plugin to this level.  By purchasing
 * licenses you ensure that the plugin gets further enhancements and professional support,
 * giving you more opportunities to find clients and make more money...  Think karma...
 * Thanks, Abel
 * ------------------------------------------------------------------------------------------- */


function __epl_pr_api_key( $refresh = false ) {


    $next = null;
    $k = epl_has_a_key();
    if ( $k == '' ) {
        add_action( 'admin_notices', 'epl_empty_api_key_msg', 100 );
        epl_dg();
        return;
        $next = null;
    }
    elseif ( epl_has_a_valid_key() !== true ) {
        add_action( 'admin_notices', 'epl_empty_api_key_msg', 100 );
        epl_dg();
        $next = 'check';
    }

    if ( !$refresh )
        $next = null;
    else
        $next = 'check';

    switch ( $next )
    {
        case null:
            break;
        case 'check':

            $cf = epl_get_remote_config();

            if ( isset( $cf->response_code ) ) {

                $v = $cf->response_code;
                $m = $cf->response_message;
                $a = $cf->atp_response_message;
                $b = $cf->mc_response_message;
                $c = $cf->us_response_message;
                $d = $cf->sc_response_message;

                update_option( '_epl_key_valid', $v );
                update_option( '_epl_key_message', $m );
                update_option( '_epl_atp', $a );
                update_option( 'ASDFAWEEFADSF', $b );
                update_option( 'DASFERWEQREWE', $c );
                update_option( 'ETDFGWETSDFGR', $d );

                if ( epl_has_a_valid_key( $v ) !== true ) {
                    //add_action( 'admin_notices', 'epl_empty_api_key_msg', 100 );
                }
                else {
                    remove_action( 'admin_notices', 'epl_empty_api_key_msg', 100 );
                }
            }


            break;
    }
}


function epl_has_a_key() {
    $k = '';
    if ( $_POST && isset( $_POST['epl_api_key'] ) )
        $k = $_POST['epl_api_key'];

    if ( $k == '' ) {
        $k = epl_get_setting( 'epl_api_option_fields', 'epl_api_key' );
    }
    return $k;
}


function epl_has_a_valid_key( $_v = null ) {

    $v = ( $_v ) ? $_v : get_option( '_epl_key_valid' );


    if ( $v ) {
        return true;
    }
    return false;
}


function epl_empty_api_key_msg() {
    echo "<div class='error'><p>" . epl_get_message( 90 ) . "</p></div>";
}


function epl_invalid_api_key_msg() {
    echo "<div class='error'><p>Events Planner API Message: " . get_option( '_epl_key_message' ) . "</p></div>";
}


function epl_get_remote_config( $action = 'check' ) {

    if ( $_POST )
        $key = esc_attr( trim( $_POST['epl_api_key'] ) );
    else
        $key = epl_get_setting( 'epl_api_option_fields', 'epl_api_key' );


    if ( is_null( $key ) || $key == '' )
        return null;

    global $blog_id;
    $r = wp_remote_post( 'http://www.wpeventsplanner.com/?check_epl_api_key=1', array(
        'body' => array(
            'epl_api_key' => $key,
            'url' => home_url(),
            'blog_id' => $blog_id,
            'wp_version' => get_bloginfo( 'version' ),
            'plugin_version' => EPL_PLUGIN_VERSION ) ) );


    $r = wp_remote_retrieve_body( $r );
    $r = json_decode( $r );

    return $r;
}


function epl_admin_notices() {

    echo "<div class='error'><p>" . epl_get_message( 90 ) . "</p></div>";
}


function epl_admin_notices1() {
    
}


function epl_dg() {
    update_option( '_epl_key_valid', 0 );
    update_option( '_epl_key_message', 'INVALID KEY' );
    remove_filter( 'epl_gateway_type_fields', 'epl_gateway_type_fields', 1 );
    remove_filter( 'epl_event_type_fields', 'epl_event_type_fields', 1 );
    remove_filter( 'epl_regis_payment_fields', 'epl_regis_payment_fields', 1 );
    remove_filter( 'epl_pay_profile_manager_fields', 'epl_pay_profile_manager_fields', 1 );
    remove_filter( 'init', 'epl_extra_post_types', 1 );
    remove_filter( 'epl_price_fields', 'epl_price_fields', 1 );
    remove_filter( 'epl_time_fields', 'epl_time_fields', 1 );
    remove_filter( 'epl_price_option_fields', 'epl_price_option_fields', 1 );
    remove_filter( 'epl_time_option_fields', 'epl_time_option_fields', 1 );
    remove_filter( 'epl_fields_fields', '_epl_fields_fields', 1 );
    remove_filter( 'epl_construct_form_default_value', '_epl_construct_form_default_value', 1 );
    remove_filter( 'epl_registration_options_fields', 'epl_registration_options_fields', 1 );
    add_action( 'epl_post_cart_container', '__epl_post_cart_container' );
    add_action( 'epl_post_event_list', '__epl_post_event_list' );
}


function __epl_post_cart_container() {
    echo '<center><b><a href="http://www.wpeventsplanner.com/" target="_blank">Event Registration</a> Powered by Events Planner for WordPress</b></center>';
}


function __epl_post_event_list() {
    echo '<center><b><a href="http://www.wpeventsplanner.com/" target="_blank">Event Registration</a> Powered by Events Planner for WordPress</b></center>';
}

add_action( 'init', '__epl_pr_api_key', 1 );


add_action( 'epl_erm__add_registration_to_db', '__epl_erm__add_registration_to_db', 10, 3 );


function __epl_erm__add_registration_to_db( $regis_post_ID, $event_id, $user_id = 0 ) {
    if ( !epl_is_addon_active( 'DASFERWEQREWE' ) )
        return;
    EPL_base::get_instance()->load_model( 'epl-db-model' );
    $user_id = ($user_id > 0 ? $user_id : EPL_db_model::get_instance()->find_user_id_for_regis( $regis_post_ID ));
    if ( $user_id != 0 )
        update_user_meta( $user_id, '_epl_regis_post_id_' . $regis_post_ID, $regis_post_ID . EPL_PLUGIN_DB_DELIM . $event_id );
}

add_action( 'epl_init_create_admin_menu', 'epl_init_create_admin_menu', 10, 1 );


function epl_init_create_admin_menu( $init ) {

    if ( epl_is_addon_active( 'DASFERWEQREWE' ) ) {
        $label = apply_filters( 'epl_my_registration_menu_label', epl__( 'My Registrations' ) );
        if ( !epl_user_is_admin() )
            add_menu_page( $label, $label, 'read', 'epl_user_self_pages_manager', array( $init, 'route' ), EPL_FULL_URL . 'images/calendar.png' );
        add_submenu_page( 'edit.php?post_type=epl_event', epl__( 'User Check-in' ), epl__( 'User Check-in' ), 'manage_options', 'epl_user_regis_manager', array( $init, 'route' ) );
    }
}


function epl_sc_is_enabled( $r = false ) {

    $a = epl_is_addon_active( 'ETDFGWETSDFGR' );
    $m = epl_get_setting( 'epl_sc_options', 'epl_sc_enable' );
    if ( !$a || $m == 0 )
        return false;

    return $m;
}


function epl_um_is_active() {
    return epl_is_addon_active( 'DASFERWEQREWE' );
}


function epl_um_is_enabled() {
    return (epl_um_is_active() && epl_get_setting( 'epl_api_option_fields', 'epl_um_enable_user_regis' ) == 1);
}


function epl_wp_login( $user_login, $user ) {
    if ( !epl_um_is_active() )
        return null;
    if ( !EPL_IS_ADMIN && !isset( $_SESSION['temp_fields']['user_id'] ) )
        $_SESSION['temp_fields']['user_id'] = $user->ID;
}

add_action( 'wp_login', 'epl_wp_login', 10, 2 );
//$eum = EPL_Base::get_instance()->load_model('epl-user-model');
//add_filter('manage_users_columns', array($eum,'user_list_col_header'));
//add_action('manage_users_custom_column',  array($eum,'user_list_col_content'), 1, 3);
?>
