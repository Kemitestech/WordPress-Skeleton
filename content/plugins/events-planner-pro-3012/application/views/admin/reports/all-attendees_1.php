
<?php

global $event_details;
$erptm = EPL_report_model::get_instance();
$delim = EPL_PLUGIN_DB_DELIM;

$avail_fields = epl_get_list_of_available_fields();
$default_row = array_fill_keys( array_keys( $avail_fields ), null );

$list = $erptm->attendee_form_data();

$tmp_regis_id = '';
$table_row = "";

foreach ( $form_list as $row ) {


    //get the ticket buyer data for each registration
    if ( $tmp_regis_id == '' || $tmp_regis_id != $row->regis_id ) {
        $tmp_regis_id = $row->regis_id;
        $new_record = true;
        $ticket_buyer_data = array( );
        $form_counter = 0;
    }
    $_r = array( );

    //attendee data
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

            $full = array_combine( $fields, $values );
            $_row = array_merge( $default_row, $full );
            $_r += epl_process_fields_for_display( $_row );

        endforeach;
    //append the primary regis form data

    endif;


    if ( !epl_is_empty_array( $_r ) )
        $table_row[] = "<tr><td>" . implode( '</td><td>', $_r ) . "</td></tr>";


    $new_record = false;
    $form_counter++;
}

//construct header row

$header = epl_get_field_labels( $avail_fields );
$header = "<tr><td>" . implode( '</td><td>', $header ) . "</td></tr>";

echo <<< EOT
<table id="datatables-1" class="epl_report_table">
		<thead>
		$header
		</thead>
EOT;
?>
<tbody>
    <?php

    echo implode( $table_row );
    ?>
</tbody>
</table>
<script type="text/javascript">

    do_datatable('#datatables-1');

</script>