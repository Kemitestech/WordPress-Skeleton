<h3><?php echo $event_title; ?> <?php echo $event_date; ?> <?php echo $event_time; ?></h3>

<style>
    #view_names_table {
        /*width: 800px;*/
    }

    #view_names_table td {
        white-space: normal;
        /*padding: 0 2px;*/
    }

    #view_names_table tr.zebra {
        background-color: #f8f8f8 !important;
    }

    #view_names_table tr.att_data_row td:first-child {
        padding-left: 15px;
    }

</style>

<?php

global $event_details, $wpdb;
$erptm = EPL_report_model::get_instance();
$has_att_forms = epl_has_attendee_forms();
$tmpl = array( 'table_open' => '<table border="1" cellpadding="0" cellspacing="0" class="" id="view_names_table">' );

$this->epl->epl_table->set_template( $tmpl );

$show_only = apply_filters( 'epl__view_full_data__show_fields', array() );

$avail_fields = epl_get_list_of_available_fields();
//epl_sort_array_by_array( $avail_fields, $fields );

$default_row = array_fill_keys( array_values( $fields ), null );

//$default_row = array_fill_keys( $fields, null );
$default_row = array_intersect_key( $default_row, $avail_fields );
$default_row = $this->epl_util->sort_array_by_array( $default_row, $event_details['_epl_attendee_list_field'] );


$limited = true;

if ( !epl_is_empty_array( $show_only ) ) {
    $default_row = $show_only;
    $limited = true;
}

$tmp_regis_id = '';
$total_revenue = 0;
$counter = 0;

foreach ( $list as $row ) {
    setup_event_details( $row->event_id );
    $row_class = ($counter++ % 2 == 1) ? '' : 'zebra';

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

    /* $_combned['regis_key'] = epl_anchor( admin_url( 'post.php?post=' . $row->regis_id . '&action=edit' ), $row->regis_key );
      $_combned['regis_date'] = $row->regis_date;
      $_combned['status'] = get_the_regis_status( $row->status );

      $_p['ticket'] = '';

      $_p['price'] = $row->grand_total;
      $_p['paid'] = $row->payment_amount;

      $_p['discount_code'] = $row->discount_code;
      $_p['discount_amount'] = $row->discount_amount;
      $_p['date'] = '';
      $_p['time'] = '';

      $_a['ticket'] = $event_details['_epl_price_name'][$row->price_id];

      $_a['price'] = $row->price;
      $_a['paid'] = '';

      $_a['discount_code'] = '';
      $_a['discount_amount'] = '';
      $_a['date'] = epl_formatted_date( $event_details['_epl_start_date'][$row->date_id] );
      $_a['time'] = $event_details['_epl_start_time'][$row->time_id]; */


    if ( $tmp_regis_id == '' || $tmp_regis_id != $row->regis_id ) {
        $tmp_regis_id = $row->regis_id;
        $new_record = true;
        $ticket_buyer_data = array();
        $form_counter = 0;


        $form_data = $erptm->get_form_data( $row->regis_id, $row->event_id, 0 );

        if ( $form_data ):
            foreach ( $form_data as $r ):


                if ( strpos( $r->field_id, EPL_PLUGIN_DB_DELIM ) ) {
                    $fields = explode( EPL_PLUGIN_DB_DELIM, $r->field_id );
                    $values = explode( EPL_PLUGIN_DB_DELIM, $r->value );
                }
                else {
                    $fields = array( $r->field_id );
                    $values = array( $r->value );
                }

                $tb_only_fields = $fields;

                $full_data = array_combine( $fields, $values );

                //$_row = array_merge( $default_row, $full_data );
                $_row = array_intersect_key( $full_data, $default_row );
                $_row = $this->epl_util->sort_array_by_array( $_row, $event_details['_epl_attendee_list_field'] );

                $ticket_buyer_data += epl_process_fields_for_display( $_row );

            endforeach;
        endif;

        $this->epl->epl_table->add_row( array_values( $_combned + $_p + $ticket_buyer_data ), $row_class, '' );

        $total_revenue += str_replace( ',', '', ($_p['paid'] ) );

        $form_counter++;
    }

    /*
      if ( $pack_regis && $filters['date_id'] != '' && ($p_size = epl_get_element_m( $row->price_id, '_epl_price_pack_size', $event_details, 1 )) > 1 ) {
      if ( isset( $attendance_dates[$row->rd_id] ) ) {
      $n = array_search( $filters['date_id'], array_keys( $attendance_dates[$row->rd_id] ) );
      $extra = "$n / {$p_size}";
      if ( (count( $attendance_dates[$row->rd_id] ) - $n) <= 2 )
      $extra = "<img src='" . EPL_FULL_URL . "images/error.png' />" . $extra;
      $extra = "<span style='float:right;margin-left:10px;color:#F8F7D8;'>$extra</span>";
      $_a['ticket'] .= $extra;
      }
      }

      $pack_size = epl_get_element_m( $row->price_id, '_epl_price_pack_size', $event_details, 1 );

     */
    if ( $has_att_forms ) {
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
                if ( $limited )
                    $_row = array_merge( $default_row, $full );
                else
                    $_row = array_intersect_key( $full, $default_row );

                $_f += epl_process_fields_for_display( $_row );


            endforeach;
        endif;
    } else continue;
    if ( epl_is_empty_array( $_f ) )
        $_f = $default_row;

    $__row = array_values( $_combned + $_a );

    $this->epl->epl_table->add_row( array_values( $__row ) + $_f, $row_class . ' att_data_row' );


    $new_record = false;
    $form_counter++;
}

//construct header row
$header = array();

epl_sort_array_by_array( $avail_fields, $default_row );
$header = array_merge( $header, epl_get_field_labels( array_intersect_key( $avail_fields, $default_row ) ) );

$this->epl->epl_table->set_heading( $header );

echo $this->epl->epl_table->generate();
