<?php

global $event_details, $wpdb;
$erptm = EPL_report_model::get_instance();
$eum = EPL_user_model::get_instance();

$tmpl = array( 'table_open' => '<table border="1" cellpadding="0" cellspacing="0" class="epl_generic_table" id="">' );
$this->epl->epl_table->set_template( $tmpl );

//we are just limiting to first name and last name
$name_only = array(
    '4e794a9a6b04f',
    '4e794ab9c1731',
    '4e794c113ea62',
    '51a8b9ad73a83',
);

$avail_fields = epl_get_list_of_available_fields();

$default_row = array_fill_keys( array_values( $name_only ), null );

$limited = true;
$tmp_regis_id == '';

//using while so we can move internal pointer
while ( list($key, $row) = each( $list ) ) {

    if ( $tmp_regis_id == '' || $tmp_regis_id != $row->regis_id ) {
        $tmp_regis_id = $row->regis_id;
        $multi_form = false;
        $form_counter = 0;
        if ( $eum->get_event_form_setup( $row->event_id ) ) {
            $multi_form = true;
            $form_counter++;
        }
    }

    $_r = array( );

//attendee data
    $form_data = $erptm->get_form_data( $row->regis_id, $row->event_id, $form_counter );

    if ( $form_data ):
        foreach ( $form_data as $r ):

            $full = $erptm->get_form_data_array( $r->field_id, $r->value );
            if ( !$limited )
                $_row = array_merge( $default_row, $full );
            else
                $_row = array_intersect_key( $full, $default_row );

            $_r = array_merge( $_r, epl_process_fields_for_display( $_row ) );

        endforeach;

    endif;

    $this->epl->epl_table->add_row( array_values( $_r ) );
    if ( $multi_form )
        $form_counter++;
}

//construct header row
$header = array( );
$header = array_merge( $header, epl_get_field_labels( array_intersect_key( $avail_fields, $default_row ) ) );

$this->epl->epl_table->set_heading( $header );

echo $this->epl->epl_table->generate();
