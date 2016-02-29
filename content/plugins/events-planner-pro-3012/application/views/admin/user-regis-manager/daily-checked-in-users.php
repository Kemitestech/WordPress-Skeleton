<?php

if ( !$checked_in_users ) {
    epl_e( 'No attendees have checked in for this session.' );
    return null;
}

global $event_details;

$erptm = EPL_report_model::get_instance();

$avail_fields = epl_get_list_of_available_fields();
$default_row = array_fill_keys( array_keys( $avail_fields ), null );
$tmpl = array( 'table_open' => '<table border="1" cellpadding="0" cellspacing="0" class="epl_standard_table" id="epl_daily_checked_in_users">' );
$this->epl->epl_table->set_template( $tmpl );
$has_att_forms = epl_has_attendee_forms();
$form_counter = 0;
$tmp_regis_id = '';

foreach ( $checked_in_users as $row ):
    setup_event_details( $row->event_id );
    $time_optional = epl_is_time_optonal();
    $session_time_id = $time_optional ? epl_get_element( 'time_id', $_POST, '' ) : $row->time_id;

    if ( $tmp_regis_id == '' || $tmp_regis_id != $row->regis_id ) {
        $tmp_regis_id = $row->regis_id;
        $new_record = true;
        $ticket_buyer_data = array();
        $form_counter = 0;
        if ( $has_att_forms )
            $form_counter++;
    }
    if ( !is_null( $row->form_no ) )
        $form_counter = $row->form_no;

    $_r = array();

    $_r['regis_id'] = epl_anchor( admin_url( 'post.php?post=' . $row->regis_id . '&action=edit' ), $row->regis_key );
    $_r['date_added'] = epl_formatted_date( $row->checkin_time, 'Y-m-d H:i' );
    $_r['ticket'] = $event_details['_epl_price_name'][$row->price_id];

    $form_data = current( $erptm->get_form_data( $row->regis_id, $row->event_id, $form_counter ) );
    $_form = $erptm->get_form_data_array( $form_data->field_id, $form_data->value );

    $_r = array_merge( $_r, epl_process_fields_for_display( $_form ) );

    $_rr = "<a href='#' 
        class='epl_delete_checkin_record'
         class='epl_user_check_in' 
        data-user_id='{$row->user_id}'
        data-regis_id='{$row->regis_id}' 
        data-event_id='{$row->event_id}' 
        data-regis_data_id='{$row->regis_data_id}' 
        data-date_id='{$row->date_id}' 
        data-date_ts='{$_POST['date_ts']}' 
        data-time_id='{$session_time_id}'
        data-price_id='{$row->price_id}'
        data-form_no='{$row->form_no}'
        data-att_id='{$row->id}'>" . epl__( 'Delete' ) . "</a>";
       
        array_unshift($_r, $_rr);

    $this->epl->epl_table->add_row( array_values( $_r ) );
    $form_counter++;
endforeach;

$header = array(
    '',
    epl__( 'Regis ID' ),
    epl__( 'Check-in Time' ),
    epl__( 'Purchase' ),
);
epl_sort_array_by_array( $avail_fields, $_form );
$header = array_merge( $header, epl_get_field_labels( array_intersect_key( $avail_fields, $_form ) ) );

$header[] = '';

$this->epl->epl_table->set_heading( $header );

echo $this->epl->epl_table->generate();
?>

