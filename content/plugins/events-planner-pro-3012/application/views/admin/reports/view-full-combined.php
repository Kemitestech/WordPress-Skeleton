
<?php //echo $print_icon;                                     ?>
<h3><?php echo $event_title; ?> <?php echo $event_date; ?> <?php echo $event_time; ?> | <?php echo count( $list ); ?> Attendees</h3>

<?php

//custom view names template for Jessi

global $event_details;
$erptm = EPL_report_model::get_instance();
$ecm = EPL_common_model::get_instance();
$delim = EPL_PLUGIN_DB_DELIM;

$tmpl = array( 'table_open' => '<table border="1" cellpadding="0" cellspacing="0" class="epl_daily_schedule_table" id="">' );

$this->epl->epl_table->set_template( $tmpl );

$all_forms = $ecm->get_list_of_available_forms();

//Ticket buyer forms
$tb_forms = epl_get_element( '_epl_primary_regis_forms', $event_details, array() );

$tb_fields = array_flip( epl_get_fields_inside_form( $tb_forms ) );

//attendee forms
$att_forms = epl_get_element( '_epl_addit_regis_forms', $event_details, array() );
$att_fields = array_flip( epl_get_fields_inside_form( $att_forms ) );

$avail_fields = epl_get_list_of_available_fields();

$ticket_buyer_avail_fields = array_intersect_key( $avail_fields, $tb_fields );

epl_sort_array_by_array( $ticket_buyer_avail_fields, $tb_fields );

$avail_fields = array_intersect_key( $avail_fields, $avail_fields );

$tmp_regis_id = '';
$total_revenue = 0;

foreach ( $list as $row ) {
    setup_event_details( $row->event_id );

    $true_discount = 0;

    if ( $row->discount_amount > 0 ) {
        $true_discount = $row->discount_amount / $row->total_tickets;
    }
    //elseif ( $row->subtotal > $row->payment_amount ) {
    //  $true_discount = ($row->subtotal - $row->payment_amount) / $row->total_tickets;
    //}

    $donation = 0;

    if ( $row->donation_amount != 0 ) {
        $donation = $row->donation_amount / $row->total_tickets;
    }


    //get the ticket buyer data for each registration
    if ( $tmp_regis_id == '' || $tmp_regis_id != $row->regis_id ) {
        $tmp_regis_id = $row->regis_id;
        $new_record = true;
        $ticket_buyer_data = array();
        $form_counter = 0;
        $pay_per_att = 0;
        //get data from ticket buyer form.
        $form_data = $erptm->get_form_data( $row->regis_id, $row->event_id, 0 );

        if ( $form_data ):
            foreach ( $form_data as $r ):


                if ( strpos( $r->field_id, $delim ) ) {
                    $fields = explode( $delim, $r->field_id );
                    $values = explode( $delim, $r->value );
                }
                else {
                    $fields = array( $r->field_id );
                    $values = array( $r->value );
                }

                $tb_only_fields = $fields;

                $full_data = array_combine( $fields, $values );

                $_row = array_intersect_key( $full_data, $ticket_buyer_avail_fields );

                $ticket_buyer_data += epl_process_fields_for_display( $_row );

            endforeach;
        endif;
        $form_counter++;
    }
    $_r = array();
    $_r['regis_key'] = epl_anchor( admin_url( 'post.php?post=' . $row->regis_id . '&action=edit' ), $row->regis_key );
    $_r['regis_date'] = $row->regis_date;
    $_r['status'] = get_the_regis_status( $row->status );
    $_r['ticket'] = $event_details['_epl_price_name'][$row->price_id];
    $_r['price'] = $row->price;
    $_r['event_total'] = $row->event_total;
    $payment_amount = ($row->payment_amount == 0) ? 0 : ($row->payment_amount > $row->event_total && $row->num_events > 1) ? $row->event_total : $row->payment_amount;
    $_r['paid'] = $row->payment_amount; //($row->payment_amount == 0) ? 0 : ($row->payment_amount > $row->event_total && $row->num_events > 1) ? $row->event_total : $row->payment_amount;
    $_r['discount_code'] = $row->discount_code;
    $_r['discount_amount'] = round( $row->discount_amount, 2 );
    if ( epl_get_regis_setting( 'epl_enable_donation' ) == 10 )
        $_r['donation_amount'] = round( $row->donation_amount, 2 );
    $_r['date'] = epl_formatted_date( $event_details['_epl_start_date'][$row->date_id] );
    $_r['time'] = $event_details['_epl_start_time'][$row->time_id];

    //find if today is second to last

    if ( $pack_regis && $filters['date_id'] != '' && ($p_size = epl_get_element_m( $row->price_id, '_epl_price_pack_size', $event_details, 1 )) > 1 ) {
        if ( isset( $attendance_dates[$row->rd_id] ) ) {
            $n = array_search( $filters['date_id'], array_keys( $attendance_dates[$row->rd_id] ) );
            if ( $n !== false )
                $n++;
            $extra = "$n / {$p_size}";
            if ( (count( $attendance_dates[$row->rd_id] ) - $n) <= 2 )
                $extra = "<img src='" . EPL_FULL_URL . "images/error.png' />" . $extra;
            $extra = "<span style='float:right;margin-left:10px;color:#F8F7D8;'>$extra</span>";
            $_r['ticket'] .= $extra;
        }
    }

    $pack_size = epl_get_element_m( $row->price_id, '_epl_price_pack_size', $event_details, 1 );


    $_r += array_values( $ticket_buyer_data );

    unset( $_r['event_total'] );
    //attendee data
    $form_data = $erptm->get_form_data( $row->regis_id, $row->event_id, $form_counter );

    if ( $form_data ):

        foreach ( $form_data as $r ):

            if ( strpos( $r->field_id, $delim ) ) {
                $fields = explode( $delim, $r->field_id );
                $values = explode( $delim, $r->value );
            }
            else {
                $fields = array( $r->field_id );
                $values = array( $r->value );
            }

            $full = array_combine( $fields, $values );

            $_row = array_intersect_key( $full, $avail_fields );

            $_r += epl_process_fields_for_display( $_row );

        endforeach;
    //append the primary regis form data

    endif;

    $this->epl->epl_table->add_row( array_values( $_r ) );

    $new_record = false;
    $form_counter++;
}

//construct header row

$header = array();
$header[] = epl__( 'Regis ID' );
$header[] = epl__( 'Regis Date' );
$header[] = epl__( 'Status' );
$header[] = epl__( 'Purchase' );
$header[] = epl__( 'Amount' );
$header[] = epl__( 'TotalPaid' );
$header[] = epl__( 'Discount Code' );
$header[] = epl__( 'Discount Amount' );

if ( epl_get_regis_setting( 'epl_enable_donation' ) == 10 )
    $header[] = epl__( 'Donation' );

$header[] = epl__( 'Date' );
$header[] = epl__( 'Time' );


epl_sort_array_by_array( $avail_fields, array_flip( $fields ) );

$header = array_merge( $header, array_values( epl_get_field_labels( array_intersect_key( $ticket_buyer_avail_fields, array_flip( $tb_only_fields ) ) ) ) );
$header = array_merge( $header, epl_get_field_labels( array_intersect_key( $avail_fields, array_flip( $fields ) ) ) );




//$footer = $this->epl->epl_util->remove_array_vals( array_keys( $header ) );
//$footer[4] = epl_get_formatted_curr( $total_revenue );
//$this->epl->epl_table->set_footer( array_values( $footer ) );

$this->epl->epl_table->set_heading( $header );

echo $this->epl->epl_table->generate();
?>
