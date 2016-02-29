<?php echo $print_icon; ?>
<h4><?php echo $event_title; ?> <?php echo $event_date; ?> <?php echo $event_time; ?></h4>

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

$avail_fields = epl_get_list_of_available_fields();
$default_row = array_fill_keys( array_keys( $avail_fields ), null );

$limited = false;


if ( !epl_is_empty_array( $show_only ) ) {
    $default_row = $show_only;
    $limited = true;
}

$tmp_regis_id = '';

foreach ( $list as $row ) {
    $_combned = array( );
    $_combned['regis_key'] = epl_anchor( admin_url( 'post.php?post=' . $row->regis_id . '&action=edit' ), $row->regis_key );

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
            if ( $form_counter == 0 )
                $this->epl->epl_table->add_row( array_values( $_combned + $ticket_buyer_data ) );
        endif;

        $form_counter++;
    }


    $_r['paid'] = '';

//attendee data
    $form_data = $erptm->get_form_data( $row->regis_id, $row->event_id, $form_counter );

    if ( $form_data ):
        foreach ( $form_data as $f ):

            $_f = array( );

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

        if ( !epl_is_empty_array( $_f ) )
            $this->epl->epl_table->add_row( array_values( $_combned + $_f ) );

    endif;


    $new_record = false;
    $form_counter++;
}

//construct header row
$header = array( 'Regis Key' );
$header = array_merge( $header, epl_get_field_labels( array_intersect_key( $avail_fields, $default_row ) ) );

$table_id = 'd' . uniqid();
$tmpl = array( 'table_open' => '<table border="1" cellpadding="0" cellspacing="0" class="" id="' . $table_id . '">' );

$this->epl->epl_table->set_template( $tmpl );

$this->epl->epl_table->set_heading( $header );

echo $this->epl->epl_table->generate();
?>

<script type="text/javascript">

    do_datatable('#<?php echo $table_id ?>');

</script>