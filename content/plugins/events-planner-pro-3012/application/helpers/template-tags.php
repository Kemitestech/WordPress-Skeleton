<?php

/*
 * Event specific template tags
 */


function the_event_list( $args = array() ) {

    return EPL_Common_Model::get_instance()->events_list( $args );
}


function setup_event_details( $event_id = null, $refresh = false, $check_for_draft = false ) {
    $event_id = is_null( $event_id ) ? get_the_ID() : $event_id;
    EPL_Common_Model::get_instance()->setup_event_details( $event_id, $refresh, $check_for_draft );
}


function setup_regis_details( $regis_id = null, $refresh = true ) {
    EPL_Common_Model::get_instance()->setup_regis_details( $regis_id, $refresh );
}


function get_the_event_title( $post_ID = null ) {


    //return EPL_util::get_instance()->get_the_event_title( $post_ID );
    return EPL_util::get_instance()->get_the_regis_event_name();
}


function get_the_event_dates_times_prices() {


    return EPL_util::get_instance()->get_the_event_dates_times_prices();
}


function get_the_event_dates( $raw = false ) {


    return EPL_util::get_instance()->get_the_event_dates( $raw );
}


function get_the_event_session_table() {


    return EPL_util::get_instance()->get_the_event_session_table();
}


function get_the_event_dates_cal() {


    return EPL_util::get_instance()->get_the_event_dates_cal();
}


function get_the_event_times() {

    return EPL_util::get_instance()->get_the_event_times();
}


function get_the_event_prices() {

    return EPL_util::get_instance()->get_the_event_prices();
}


function get_the_register_button( $event_id = null, $url_only = false, $args = array() ) {

    return EPL_util::get_instance()->get_the_register_button( $event_id, $url_only, $args );
}


function get_the_attendee_list_link( $anchor = '' ) {

    return EPL_util::get_instance()->get_the_attendee_list_link( $anchor );
}


function get_the_registration_details() {

    return EPL_common_model::get_instance()->get_registration_details( get_the_ID() );
}


function get_the_event_location_id() {
    global $event_details;
    return epl_get_element( '_epl_event_location', $event_details );
}


function the_event_meta( $post_ID ) {

    return EPL_util::get_instance()->the_event_meta( $post_ID );
}

/*
 * Location template tags
 */


function the_location_details( $location_id = null ) {

    return EPL_Common_Model::get_instance()->setup_location_details( $location_id );
}


function get_the_location_id() {

    return _get_the_location_field( 'ID' );
}


function get_the_location_name() {

    return stripslashes_deep( _get_the_location_field( 'post_title' ) );
}


function get_the_location_address() {

    return _get_the_location_field( '_epl_location_address' );
}


function get_the_location_address2() {

    return _get_the_location_field( '_epl_location_address2' );
}


function get_the_location_city() {

    return _get_the_location_field( '_epl_location_city' );
}


function get_the_location_country() {

    return _get_the_location_field( '_epl_location_country' );
}


function get_the_location_state() {

    return _get_the_location_field( '_epl_location_state' );
}


function get_the_location_zip() {

    return _get_the_location_field( '_epl_location_zip' );
}


function get_the_location_phone() {

    return _get_the_location_field( '_epl_location_phone' );
}


function get_the_location_lat() {

    return _get_the_location_field( '_epl_location_lat' );
}


function get_the_location_long() {

    return _get_the_location_field( '_epl_location_long' );
}


function get_the_location_gmap_icon( $text = '', $link_only = false ) {

    if ( _get_the_location_field( '_epl_location_display_map_link' ) == 10 ) {

        if ( $text == '' )
            $text = '<img alt="Map" src="' . EPL_FULL_URL . 'images/map.png" />';

        global $location_details;

        $full_address = "{$location_details['_epl_location_address']} {$location_details['_epl_location_city']} {$location_details['_epl_location_state']} {$location_details['_epl_location_zip']}";
        $link = '<a target="_blank" href="http://maps.google.com/maps?q=' . urlencode( $full_address ) . '">' . $text . '</a> ';

        if ( $link_only )
            return $link;

        return '<div class="epl_show_gmap" id ="location_id_' . get_the_location_id() . '">' . $link . '</div>';
    }

    return null;
}


function get_the_location_website() {

    return _get_the_location_field( '_epl_location_url' );
}


function _get_the_location_field( $field = null ) {
    if ( is_null( $field ) )
        return null;


    global $location_details, $post;

    $id = null;
    if ( $post->post_type != 'epl_location' ) {
        global $event_details;
        if ( !epl_is_empty_array( $event_details ) )
            $id = epl_get_element( '_epl_event_location', $event_details );

        if ( !$id && !epl_is_multi_location() )
            return null;
    }

    if ( !$location_details || (!epl_is_multi_location() && $id != $location_details['ID'] ) )
        EPL_Common_Model::get_instance()->setup_location_details( $id );


    return epl_get_element( $field, $location_details );
}

/*
 * End Location template tags
 */

/*
 * Organization template tags
 */


function the_organization_details() {

    return EPL_Common_Model::get_instance()->setup_org_details();
}


function get_the_organization_name() {

    return stripslashes_deep( _get_the_organization_field( 'post_title' ) );
}


function get_the_organization_permalink( $org_id = null ) {

    return get_permalink( $org_id );
}


function get_the_organization_address() {

    return _get_the_organization_field( '_epl_org_address' );
}


function get_the_organization_address2() {

    return _get_the_organization_field( '_epl_org_address2' );
}


function get_the_organization_city() {

    return _get_the_organization_field( '_epl_org_city' );
}


function get_the_organization_state() {

    return _get_the_organization_field( '_epl_org_state' );
}


function get_the_organization_zip() {

    return _get_the_organization_field( '_epl_org_zip' );
}


function get_the_organization_phone() {

    return _get_the_organization_field( '_epl_org_phone' );
}


function get_the_organization_email() {

    return _get_the_organization_field( '_epl_org_email' );
}


function get_the_organization_website() {

    return _get_the_organization_field( '_epl_org_website' );
}


function _get_the_organization_field( $field = null ) {
    if ( is_null( $field ) )
        return null;
    global $organization_details;

    $id = null;
    global $event_details;
    if ( !epl_is_empty_array( $event_details ) )
        $id = epl_get_element( '_epl_event_organization', $event_details );

    EPL_Common_Model::get_instance()->setup_org_details( $id );


    return epl_get_element( $field, $organization_details );
}

/*
 * End Organization template tags
 */

/*
 * Instructor template tags //Incomplete
 */


function the_instructor_details( $instr_id = null ) {

    return EPL_Common_Model::get_instance()->setup_instructor_details( $instr_id );
}


function get_the_instructor_name( $link = false, $raw = false, $label = '' ) {

    $label = (($label === false) ? '' : $label == '' ? epl__( 'Instructor' ) : $label);

    global $instructor_details, $event_details;
    if ( !$instructor_details )
        EPL_Common_Model::get_instance()->setup_instructor_details();

    $r = '';
    $_i = array();

    if ( epl_get_element( '_epl_event_instructor', $event_details ) ) {

        foreach ( $event_details['_epl_event_instructor'] as $instr_id ) {

            if ( !isset( $instructor_details[$instr_id] ) )
                EPL_Common_Model::get_instance()->setup_instructor_details( $instr_id );

            $_link = ($link) ? "<a href='" . get_permalink( $instr_id ) . "' title='{$instructor_details['post_title']}'>{$instructor_details['post_title']}</a>" : $instructor_details['post_title'];
            $_i[$instructor_details['ID']] = $_link;
            $r .= "<dd>$_link</dd>";
        }
        if ( $raw )
            return $_i;

        return "<dl class='epl_instructor_dl'><dt>" . $label . "</dt>" . $r . "</dl>";
    }

    if ( $raw )
        return $_i;

    return null;
}


function _get_the_instructor_field( $field = null ) {
    if ( is_null( $field ) )
        return null;
    global $instructor_details;
    EPL_Common_Model::get_instance()->setup_instructor_details();

    return $instructor_details[$field];
}

/*
 * End Organization template tags
 */


/*
 * Generic functions
 */


function epl_get_the_field( $field, $fields ) {

    return $fields[$field]['field'];
}


function epl_get_the_label( $field, $fields ) {

    return $fields[$field]['label'];
}


function epl_get_the_desc( $field, $fields ) {

    return $fields[$field]['description'];
}

/*
 * Registration Template Tags
 */


function the_registration_details() {

    return EPL_Common_Model::get_instance()->setup_regis_details();
}


function get_the_regis_dates_times_prices( $regis_id = null, $raw = false ) {

    return EPL_util::get_instance()->get_the_regis_dates_times_prices( $regis_id, $raw );
}


function get_the_regis_dates() {

    return EPL_util::get_instance()->get_the_regis_dates();
}


function get_the_regis_times() {

    return EPL_util::get_instance()->get_the_regis_times();
}


function get_the_regis_prices() {

    return EPL_util::get_instance()->get_the_regis_prices();
}


function get_the_regis_status( $status = null, $id_only = false ) {

    return EPL_util::get_instance()->get_the_regis_status( $status, $id_only );
}


function get_the_regis_original_amount() {

    return EPL_util::get_instance()->get_the_regis_original_amount();
}


function get_the_regis_cart_money_totals() {

    global $regis_details, $event_details;

    $regis_id = $regis_details['post_title'];

    return epl_get_element_m( 'money_totals', 'cart_totals', $regis_details['__epl'][$regis_id], array() );
}


function get_the_regis_total_amount( $symbol = true ) {

    return EPL_util::get_instance()->get_the_regis_total_amount( $symbol );
}


function get_the_regis_balance_due() {

    return EPL_util::get_instance()->get_the_regis_balance_due();
}


function get_the_regis_payment_amount() {

    return EPL_util::get_instance()->get_the_regis_payment_amount();
}


function get_the_regis_payment_date() {

    return EPL_util::get_instance()->get_the_regis_payment_date();
}


function get_the_regis_transaction_id() {

    return EPL_util::get_instance()->get_the_regis_transaction_id();
}


function get_the_regis_id() {

    return EPL_util::get_instance()->get_the_regis_id();
}


function _get_the_regis_field( $field = null ) {
    if ( is_null( $field ) )
        return null;
    global $regis_details;


    /*
      $id = null;
      global $event_details;
      if (isset($event_details))
      $id = $event_details['_epl_event_organization'];

      EPL_Common_Model::get_instance()->setup_regis_details($id);
     */

    return $regis_details[$field];
}


function epl_cart_top() {
    global $epl_current_step;
    if ( 'show_cart' == $epl_current_step || ('process_cart_action' == $epl_current_step && epl_get_element( 'cart_action', $_GET ) == 'add' ) )
        do_action( 'epl_cart_top_message' );
}


function epl_cart_bottom() {
    global $epl_current_step;
    if ( 'show_cart' == $epl_current_step || ('process_cart_action' == $epl_current_step && epl_get_element( 'cart_action', $_GET ) == 'add' ) )
        do_action( 'epl_cart_bottom_message' );
}


function epl_regis_form_top() {
    global $epl_current_step;

    if ( 'regis_form' == $epl_current_step )
        do_action( 'epl_regis_form_top_message' );
}


function epl_regis_form_bottom() {
    global $epl_current_step;

    if ( 'regis_form' == $epl_current_step )
        do_action( 'epl_regis_form_bottom_message' );
}


function epl_get_event_status( $raw = false ) {

    global $epl_fields, $event_details;

    $status = epl_get_element( '_epl_event_status', $event_details );

    if ( $raw )
        return array( $status => epl_get_element_m( $status, 'options', epl_get_element( '_epl_event_status', $epl_fields['epl_option_fields'] ) ) );

    return epl_get_element_m( $status, 'options', epl_get_element( '_epl_event_status', $epl_fields['epl_option_fields'] ) );
}


function epl_get_regis_status( $date_key = null ) {


    global $event_details, $capacity, $current_att_count, $available_space_arr;

    $today = date_i18n( 'm/d/Y H:i:s', EPL_TIME );

    $regis_start_date = epl_get_date_timestamp( epl_admin_dmy_convert( epl_get_element( $date_key, $event_details['_epl_regis_start_date'], $today ) ) );
    $regis_end_date = epl_get_date_timestamp( epl_admin_dmy_convert( epl_get_element( $date_key, $event_details['_epl_regis_end_date'], $today ) ) );

    $ok = epl_compare_dates( $today, $regis_start_date, ">=" );

    if ( !$ok )
        return epl__( "Opens on" ) . ' ' . epl_formatted_date( $event_details['_epl_regis_start_date'][$date_key] );

    $ok = epl_compare_dates( $today, strtotime( '23:59:59', $regis_end_date ), "<=" );

    if ( !$ok )
        return epl__( 'Closed' );

    return epl__( 'Open' );
}


function get_ticket_buyer_name( $regis_id = null ) {

    global $epl_fields, $event_details, $regis_details;

    if ( epl_is_empty_array( $regis_details ) )
        epl_setup_regis_details( !is_null( $regis_id ) ? $regis_id : get_the_ID()  );

    $regis_attendee_info = $regis_details['_epl_attendee_info'];
    $first_name = current( epl_get_element( '4e794a9a6b04f', $regis_attendee_info ) );
    $last_name = current( epl_get_element( '4e794ab9c1731', $regis_attendee_info ) );


    return $first_name[0] . ' ' . $last_name[0];
}


function epl_get_all_events() {

    return EPL_Common_Model::get_instance()->get_all_events();
}

?>
