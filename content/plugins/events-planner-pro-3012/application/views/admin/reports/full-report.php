<?php $table_id = 'table_' . time(); ?>
<!--<h3><?php echo $event_title; ?> <?php echo $event_date; ?> <?php echo $event_time; ?></h3>-->

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

$tmpl = array( 'table_open' => '<table border="1" cellpadding="0" cellspacing="0" class="epl_daily_schedule_table epl_standard_table" id="' . $table_id . '">' );

$this->epl->epl_table->set_template( $tmpl );

$show_only = apply_filters( 'epl_view_names_report_show_fields', array( ) );

$avail_fields = epl_get_list_of_available_fields();

//epl_sort_array_by_array( $avail_fields, $fields );

$default_row = array_fill_keys( array_keys( $avail_fields ), null );

$limited = false;
//echo "<pre class='prettyprint'>" . __LINE__ . "> " . print_r($forms, true). "</pre>";
//$epl_fields_inside_form = array_flip( $forms['epl_form_fields'] ); //get the field ids inside the form
//when creating a form in form manager, the user may rearrange fields.  Find their desired order
//$epl_fields_to_display = $this->epl->epl_util->sort_array_by_array( $available_fields, $epl_fields_inside_form );


if ( !epl_is_empty_array( $show_only ) ) {
    $default_row = $show_only;
    $limited = true;
}

$tmp_regis_id = '';
//echo "<pre class='prettyprint'>" . __LINE__ . "> " . print_r($list, true). "</pre>";
//using while so we can move internal pointer
//while ( list($key, $row) = each( $list ) ) {
foreach ( $list as $row ) {

    setup_event_details( $row->event_id, true );
    $_combned = array( );
    $_p = array( );
    $_a = array( );

    $_combned['regis_key'] = epl_anchor( admin_url( 'post.php?post=' . $row->regis_id . '&action=edit' ), $row->regis_key );
    $_combned['regis_date'] = date( 'm/d/Y', strtotime( $row->regis_date ) );
    $_combned['status'] = get_the_regis_status( $row->status );

    $_p['event_name'] = '';
    $_p['ticket'] = '';
    $_p['discount_code'] = $row->discount_code;
    $_p['price'] = $row->grand_total;
    $_p['paid'] = ($row->payment_amount == 0) ? 0 : $row->payment_amount > $row->event_total ? $row->event_total : $row->payment_amount;
    $_p['date'] = '';
    $_p['time'] = '';


    $_a['event_name'] = $event_details['post_title'];
    $_a['ticket'] = $event_details['_epl_price_name'][$row->price_id] . ' ' . $row->price;
    $_a['discount_code'] = '';
    $_a['price'] = '';
    $_a['paid'] = '';
    $_a['date'] = epl_formatted_date( $event_details['_epl_start_date'][$row->date_id] );
    $_a['time'] = $event_details['_epl_start_time'][$row->time_id];


    if ( $tmp_regis_id == '' || $tmp_regis_id != $row->regis_id ) {
        $tmp_regis_id = $row->regis_id;
        $new_record = true;
        $ticket_buyer_data = array( );
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

                $_row = array_merge( $default_row, $full_data );

                $ticket_buyer_data += epl_process_fields_for_display( $_row );

            endforeach;
        endif;

        $this->epl->epl_table->add_row( array_values( $_combned + $_p + $ticket_buyer_data ), '', $row_style );

        $form_counter++;
    }


    $_r['paid'] = '';


//attendee data
    $form_data = $erptm->get_form_data( $row->regis_id, $row->event_id, $form_counter );
    $_f = array( );
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

    $this->epl->epl_table->add_row( array_values( $_combned + $_a + $_f ) );


    $new_record = false;
    $form_counter++;
}

//construct header row
$header = array(
    epl__( 'Regis ID' ),
    epl__( 'Regis Date' ),
    epl__( 'Status' ),
    epl__( 'Event' ),
    epl__( 'Purchase' ),
    epl__( 'Discount Code' ),
    epl__( 'Grand Total' ),
    epl__( 'Paid' ),
    epl__( 'Date' ),
    epl__( 'Time' )
);
$header = array_merge( $header, epl_get_field_labels( array_intersect_key( $avail_fields, $default_row ) ) );


$this->epl->epl_table->set_heading( $header );

echo $this->epl->epl_table->generate();
?>

<script type="text/javascript">

    jQuery(document).ready(function($) {
        
        do_datatable('#<?php echo $table_id; ?>');

                        				
    });

</script>