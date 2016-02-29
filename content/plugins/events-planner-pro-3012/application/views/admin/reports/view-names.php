<?php //echo $print_icon;                                  ?>
<h3><?php echo $event_title; ?> <?php echo $event_date; ?> <?php echo $event_time; ?></h3>

<style>
    #view_names_table {
        width: 800px;
    }

    #view_names_table td {
        white-space: normal;
        padding: 0 2px;
    }

</style>

<?php

global $event_details, $wpdb;
$erptm = EPL_report_model::get_instance();
$has_att_forms = epl_has_attendee_forms();

$tmpl = array( 'table_open' => '<table border="1" cellpadding="0" cellspacing="0" class="epl_daily_schedule_table dataTable" id="">' );

$this->epl->epl_table->set_template( $tmpl );

$show_only = apply_filters( 'epl_view_names_report_show_fields', array() );

$avail_fields = epl_get_list_of_available_fields();
//epl_sort_array_by_array( $avail_fields, $fields );
//only get the attendee forms
$fields = $erptm->get_form_fields( null, 'att_form' );
$fields = array_merge( $fields, $erptm->get_form_fields( null, 'other' ) );

$default_row = array_fill_keys( array_values( $fields ), null );

$limited = false;

if ( !epl_is_empty_array( $show_only ) ) {
    $default_row = $show_only;
    $limited = true;
}

$tmp_regis_id = '';
$total_revenue = 0;

foreach ( $list as $row ) {
    setup_event_details( $row->event_id );

    $true_discount = 0;

    if ( $row->discount_amount > 0 ) {
        $true_discount = $row->discount_amount / $row->total_tickets;
    }

    $donation = 0;

    if ( $row->donation_amount != 0 ) {
        $donation = $row->donation_amount / $row->total_tickets;
    }

    $_combned = array();
    $_p = array();
    $_a = array();

    $_combned['regis_key'] = epl_anchor( admin_url( 'post.php?post=' . $row->regis_id . '&action=edit' ), $row->regis_key );
    $_combned['regis_date'] = $row->regis_date;
    $_combned['status'] = get_the_regis_status( $row->status );

    $_p['ticket'] = '';

    $_p['price'] = $row->grand_total;
    $_p['paid'] = ($row->payment_amount == 0) ? 0 : $row->payment_amount > $row->event_total ? $row->event_total : $row->payment_amount;
    $_p['paid_per_class'] = '';
    $_p['discount_code'] = $row->discount_code;
    $_p['discount_amount'] = round( $row->discount_amount, 2 );
    if ( epl_get_regis_setting( 'epl_enable_donation' ) == 10 )
        $_p['donation_amount'] = round( $row->donation_amount, 2 );
    $_p['date'] = '';
    $_p['time'] = '';

    $_a['ticket'] = $event_details['_epl_price_name'][$row->price_id];

    $_a['price'] = $row->price;
    $_a['paid'] = ($row->payment_amount == 0) ? 0 : ($row->payment_amount > $row->event_total && $row->num_events > 1) ? $row->event_total : $row->payment_amount;
    $_a['paid_per_class'] = '';
    $_a['discount_code'] = '';
    $_a['discount_amount'] = round( $true_discount, 2 );
    if ( epl_get_regis_setting( 'epl_enable_donation' ) == 10 )
        $_a['donation_amount'] = round( $row->donation_amount, 2 );
    $_a['date'] = epl_formatted_date( $event_details['_epl_start_date'][$row->date_id] );
    $_a['time'] = $event_details['_epl_start_time'][$row->time_id];


    if ( $tmp_regis_id == '' || $tmp_regis_id != $row->regis_id ) {
        $tmp_regis_id = $row->regis_id;
        $new_record = true;
        $ticket_buyer_data = array();
        $form_counter = 0;
        if ( $has_att_forms )
            $form_counter++;
    }

    $payment_amount = ($row->payment_amount == 0) ? 0 : ($row->payment_amount > $row->event_total && $row->num_events > 1) ? $row->event_total : $row->payment_amount;

    $pack_size = epl_get_element_m( $row->price_id, '_epl_price_pack_size', $event_details, 1 );

    $_a['paid_per_class'] = $row->price;

    if ( $row->total_quantity > 1 )
        $_a['paid_per_class'] = $payment_amount / $row->total_quantity;

    if ( $row->event_total == $payment_amount )
        $_a['paid_per_class'] = $row->price;


    if ( $pack_size > 1 ) {

        $_a['paid_per_class'] = $payment_amount / $row->total_quantity / $pack_size;
    }
    else {

        if ( $row->num_events > 1 ) {
            if ( $payment_amount > $_a['price'] )
                $_a['paid_per_class'] = $_a['price'];
            else
                $_a['paid_per_class'] = $payment_amount / $row->num_dates / $row->total_quantity;
        }


        $_a['paid_per_class'] -= $true_discount;
        $_a['paid_per_class'] += $donation;

        if ( $row->total_quantity >= 1 && $payment_amount < ($row->price * $row->total_quantity * $row->num_dates) ) {
            $_a['paid_per_class'] = $payment_amount / $row->num_dates / $row->total_quantity;
        }

        if ( $row->total_quantity == 1 && $payment_amount < $row->price ) {
            $_a['paid_per_class'] = $payment_amount;
        }
    }
    $_a['paid_per_class'] = epl_get_formatted_curr( epl_nz( $_a['paid_per_class'], 0 ) );
    $total_revenue += str_replace( ',', '', ($_a['paid_per_class'] ) );
    unset( $_a['paid_per_class'] );

//attendee data
    $form_data = $erptm->get_form_data( $row->regis_id, $row->event_id, $form_counter );
    $_f = array();
    if ( $form_data ):
        foreach ( $form_data as $f ):



            if ( strpos( $f->field_id, EPL_PLUGIN_DB_DELIM ) ) {
                $fields = explode( EPL_PLUGIN_DB_DELIM, $f->field_id );
                $values = explode( EPL_PLUGIN_DB_DELIM, $f->value );
            }
            else {
                $fields = array( $f->field_id );
                $values = array( $f->value );
            }

            $full = array_combine( $fields, $values );
            if ( !$limited )
                $_row = array_merge( $default_row, $full );
            else
                $_row = array_intersect_key( $full, $default_row );

            $_f += epl_process_fields_for_display( $_row );


        endforeach;
    endif;

    if ( epl_is_empty_array( $_f ) )
        $_f = $default_row;

    $__row = array_values( $_combned + $_a );


    $form_data = array();

    foreach ( $ticket_buyer_data as $k => $v ) {
        $form_data[$k] = epl_get_element( $k, $_f, $v );
    }

    $this->epl->epl_table->add_row( array_values( $__row ) + $_f );


    $new_record = false;
    if ( $has_att_forms )
        $form_counter++;
}

//construct header row
$header = array();
$header[] = epl__( 'Regis ID' );
$header[] = epl__( 'Regis Date' );
$header[] = epl__( 'Status' );
$header[] = epl__( 'Purchase' );
$header[] = epl__( 'Amount' );
$header[] = epl__( 'Paid' );
$header[] = epl__( 'Discount Code' );
$header[] = epl__( 'Discount Amount' );

if ( epl_get_regis_setting( 'epl_enable_donation' ) == 10 )
    $header[] = epl__( 'Donation' );

$header[] = epl__( 'Date' );
$header[] = epl__( 'Time' );


epl_sort_array_by_array( $avail_fields, $default_row );
$header = array_merge( $header, epl_get_field_labels( array_intersect_key( $avail_fields, $default_row ) ) );

$this->epl->epl_table->set_heading( $header );

/* $footer = $this->epl->epl_util->remove_array_vals( array_keys( $header ) );
  $footer[4] = epl__( 'Total Paid' );
  $footer[5] = epl_get_formatted_curr( $total_revenue );
  $this->epl->epl_table->set_footer( array_values( $footer ) ); */


echo $this->epl->epl_table->generate();
