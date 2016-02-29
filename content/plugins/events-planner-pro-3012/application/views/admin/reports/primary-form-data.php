
<?php

global $event_details;

$avail_fields = epl_get_list_of_available_fields();
$default_row = array_fill_keys( array_keys( $avail_fields ), null );


$tmp_regis_id = '';
$table_row = "";

foreach ( $form_list as $form ) {


           if ( strpos( $form->field_id, EPL_PLUGIN_DB_DELIM ) ) {
                $fields = explode( EPL_PLUGIN_DB_DELIM, $form->field_id );
                $values = explode( EPL_PLUGIN_DB_DELIM, $form->value );
            }
            else {
                $fields = array( $form->field_id );
                $values = array( $form->value );
            }

            $full = array_combine( $fields, $values );
            $_row = array_merge( $default_row, $full );
            $_r = epl_process_fields_for_display( $_row );




    if ( !epl_is_empty_array( $_r ) )
        $table_row[] = "<tr><td>" . implode( '</td><td>', $_r ) . "</td></tr>";


    $new_record = false;
    $form_counter++;
}

//construct header row

$header = epl_get_field_labels( $avail_fields );
$header = "<tr><td>" . implode( '</td><td>', $header ) . "</td></tr>";
$table_id = 'd'.uniqid();
echo <<< EOT
<table id="$table_id" class="epl_report_table">
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

    do_datatable('#<?php echo $table_id ?>');

</script>