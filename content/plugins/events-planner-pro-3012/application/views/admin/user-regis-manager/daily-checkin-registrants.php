<?php

if ( !$registrants ) {
    epl_e( 'No registrants found for this session' );
    return null;
}
?>
<h3><?php epl_e( 'Users that have registered for this session' ); ?></h3>
<?php

global $event_details, $session_signed_in_counts, $user_session_signed_in_counts, $user_ticket_use_counts;

$erptm = EPL_report_model::get_instance();

$show_only = array(
    '4e794a9a6b04f' => '',
    '4e794ab9c1731' => '',
    '4e794a6eeeb9a' => ''
);

$show_only = apply_filters( 'epl__daily_checkin_registrants__show_only', $show_only );

$avail_fields = epl_get_list_of_available_fields();

//$avail_fields = array_intersect_key( $avail_fields, $show_only );

$default_row = array_fill_keys( array_keys( $avail_fields ), null );
$tmpl = array( 'table_open' => '<table border="0" cellpadding="0" cellspacing="0" class="epl_standard_table" id="epl_daily_session_registrants">' );
$this->epl->epl_table->set_template( $tmpl );

$header = array(
    '',
    epl__( 'Regis ID' ),
    epl__( 'Status' ),
    epl__( 'Purchase' ),
    epl__( 'Package' ),
    epl__( 'Use Count' ),
);

$has_att_forms = epl_has_attendee_forms();
$tmp_regis_id = '';
$form_counter = 0;

foreach ( $registrants as $row ):

    if ( $tmp_regis_id == '' || $tmp_regis_id != $row->regis_id ) {
        $tmp_regis_id = $row->regis_id;
        $new_record = true;
        $ticket_buyer_data = array();
        $form_counter = 0;
        if ( $has_att_forms )
            $form_counter++;
    }

    setup_event_details( $row->event_id );
    $time_optional = epl_is_time_optonal();

    $time_id = $time_optional ? '' : $row->time_id;
    $session_time_id = $time_optional ? epl_get_element( 'time_id', $_POST, '' ) : $row->time_id;

    $_r = array();
    $_r['regis_key'] = epl_anchor( admin_url( 'post.php?post=' . $row->regis_id . '&action=edit' ), $row->regis_key );
    $_r['status'] = get_the_regis_status( $row->status );
    $_r['ticket'] = $event_details['_epl_price_name'][$row->price_id];

    $_r['package'] = '-';
    $user_ticket_use_count_key = "{$row->regis_id}-{$row->event_id}-{$row->rd_id}-{$row->date_id}-{$time_id}-{$row->price_id}-{$row->user_id}";

    $_r['use_count'] = epl_get_element( $user_ticket_use_count_key, $user_ticket_use_counts, 0 );

    $user_session_use_count_key = "{$row->regis_id}-{$row->event_id}-{$row->rd_id}-{$row->date_id}-{$_POST['date_ts']}-{$_POST['time_id']}-{$row->price_id}-{$row->user_id}";

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



    $form_data = current( $erptm->get_form_data( $row->regis_id, $row->event_id, $form_counter ) );
    $_form = $erptm->get_form_data_array( $form_data->field_id, $form_data->value );
    $_form = array_intersect_key( $_form, $avail_fields );

    $_r = array_merge( $_r, epl_process_fields_for_display( $_form ) );

    $_rr = $user_alredy_checked_in ? epl__( 'Checked in' ) : "<a href='#' 
        class='epl_user_check_in' 
        data-user_id='{$row->user_id}'
        data-regis_id='{$row->regis_id}' 
        data-event_id='{$row->event_id}' 
        data-regis_data_id='{$row->rd_id}' 
        data-date_id='{$row->date_id}' 
        data-date_ts='{$_POST['date_ts']}' 
        data-time_id='{$session_time_id}' 
        data-form_no='{$form_counter}' 
        data-price_id='{$row->price_id}'>" . epl__( 'Check In' ) . "</a>";
        
     array_unshift($_r, $_rr);   

    $this->epl->epl_table->add_row( array_values( $_r ) );
    $form_counter++;
endforeach;



epl_sort_array_by_array( $avail_fields, $_form );

$header = array_merge( $header, epl_get_field_labels( array_intersect_key( $avail_fields, $_form ) ) );

//$header[] = '';

$this->epl->epl_table->set_heading( $header );

echo $this->epl->epl_table->generate();
?>
<hr />
<h3 style="clear: both;"><?php epl_e( 'Checked-in users' ); ?></h3>
<?php echo $checked_in_users; ?>

<script>
    
    jQuery(document).ready(function($){
        
        var oTable = $('#epl_daily_session_registrants').dataTable( {

            "sPaginationType": "full_numbers",
            "iDisplayLength": 10,
            "aaSorting": [[ 1, "asc" ]],
            "aoColumnDefs": [
                {
                    "bSortable": false,
                    "aTargets": [ -1 ]
                }
            ],
            "sDom": '<"dtTop"frtilp>rt<"dtBottom"><"clear">',

        });
    });
    
</script>


