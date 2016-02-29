
<?php

$epl = EPL_Base::get_instance();
$delim =  EPL_db_model::get_instance()->delim;
$tmpl = array( 'table_open' => '<table border="1" cellpadding="0" cellspacing="0" class="epl_lookup_result_table">' );

$this->epl->epl_table->set_template( $tmpl );


$header = epl_get_field_labels( $avail_fields );


$_row = array_fill_keys( array_keys( $avail_fields ), null );


foreach ( $lookup_list as $r ):


    if ( strpos( $r->field_id, $delim ) ) {
        $fields = explode( $delim, $r->field_id );
        $values = explode( $delim, $r->value );
    }
    else {
        $fields = $r->field_id;
        $values = $r->value;
    }

    $raw = array_combine( $fields, $values );

    $row = array_merge( $_row, $raw );

    $row = epl_process_fields_for_display( $row, $avail_fields );
    
    $raw['user_id'] = $r->user_id;
    
    $regis_link = epl_anchor( admin_url( 'post.php?post=' . $r->regis_id . '&action=edit' ), $r->regis_key );

    $select_link = '<a href="#" class="epl_lookup_row_select">Select</a><span class="form_data" style="display:none;">' . json_encode( $raw ) . '</span>' . ($r->user_id>0?" ({$r->user_id})":'');
    array_unshift( $row, $select_link );
    array_unshift( $row, $regis_link );

    $epl->epl_table->add_row( $row );

endforeach;

array_unshift( $header, '' );
array_unshift( $header, '' );
$epl->epl_table->set_heading( $header );
echo $epl->epl_table->generate();




?>