<?php

if ( !$registrants ) {
    epl_e( 'No registrants found for this session' );
    return null;
}
?>
<h3><?php epl_e( 'All Purchases' ); ?></h3>
<?php

global $event_details, $session_signed_in_counts, $user_session_signed_in_counts, $user_ticket_use_counts;

$erptm = EPL_report_model::get_instance();

$avail_fields = epl_get_list_of_available_fields();
$default_row = array_fill_keys( array_keys( $avail_fields ), null );
$tmpl = array( 'table_open' => '<table border="1" cellpadding="0" cellspacing="0" class="epl_standard_table" id="epl_daily_session_registrants">' );
$this->epl->epl_table->set_template( $tmpl );

$header = array(
    epl__( 'Regis ID' ),
    epl__( 'Status' ),
    epl__( 'Purchase' ),
    epl__( 'Package' ),
    epl__( 'Use Count' ),
);

foreach ( $registrants as $row ):
    setup_event_details( $row->event_id );
    $time_optional = epl_is_time_optonal();

    $time_id = $time_optional ?'' : $row->time_id;
    $session_time_id = $time_optional ? epl_get_element( 'time_id', $_POST, '' ) : $row->time_id;

    $_r = array( );
    $_r['regis_key'] = epl_anchor( admin_url( 'post.php?post=' . $row->regis_id . '&action=edit' ), $row->regis_key );
    $_r['status'] = get_the_regis_status( $row->status );
    $_r['ticket'] = $event_details['_epl_price_name'][$row->price_id];

    $_r['package'] = '-';
    $user_ticket_use_count_key = "{$row->regis_id}-{$row->event_id}-{$row->date_id}-{$time_id}-{$row->price_id}-{$row->user_id}";

    $_r['use_count'] = epl_get_element( $user_ticket_use_count_key, $user_ticket_use_counts, 0 );

    $user_session_use_count_key = "{$row->regis_id}-{$row->event_id}-{$row->date_id}-{$_POST['date_ts']}-{$_POST['time_id']}-{$row->price_id}-{$row->user_id}";

    $user_alredy_checked_in = epl_get_element( $user_session_use_count_key, $user_session_signed_in_counts, false );

    if ( epl_is_pack_regis() ) {
        $pack_size = epl_get_element_m( $row->price_id, '_epl_price_pack_size', $event_details, 1 );
        $exp = '';
        if ( epl_get_element_m( $row->price_id, '_epl_price_pack_type', $event_details ) == 'time' ) {
            $mem_l = epl_get_element_m( $row->price_id, '_epl_price_pack_time_length', $event_details );
            $mem_lt = epl_get_element_m( $row->price_id, '_epl_price_pack_time_length_type', $event_details );

            $start = strtotime( $row->regis_date );
            $until = strtotime( "+ $mem_l $mem_lt", $start );

            if ( $until < EPL_DATE ) {
                $exp = epl_wrap( epl__( "Expired" ), '<span class="epl_font_red"> - ', '</span>' );
            }

            $_r['package'] = epl__( 'Until' ) . ' ' . epl_formatted_date( $until ) . $exp;
        }
        else {
            $remaining = ($pack_size - $_r['use_count']);
            if ( $remaining <= 0 )
                $exp = epl_wrap( epl__( "Credits used up" ), ' <span class="epl_font_red">', '</span>' );
            $_r['package'] = $pack_size . $exp;

            $_r['use_count'] = "{$_r['use_count']}/{$_r['package']}";
        }
    }




    $_form = $erptm->get_form_data_array( $row->field_id, $row->value );

    $_r = array_merge( $_r, epl_process_fields_for_display( $_form ) );



    $this->epl->epl_table->add_row( array_values( $_r ) );

endforeach;



epl_sort_array_by_array( $avail_fields, $_form );

$header = array_merge( $header, epl_get_field_labels( array_intersect_key( $avail_fields, $_form ) ) );



$this->epl->epl_table->set_heading( $header );

echo $this->epl->epl_table->generate();
?>


