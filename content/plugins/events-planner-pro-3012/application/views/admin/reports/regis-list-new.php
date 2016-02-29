<?php
global $event_details;
$epl = EPL_Base::get_instance();
        
//echo "<pre class='prettyprint'>" . __LINE__ . "> " . print_r($list, true). "</pre>";


foreach ($list as $k => $v) {
    setup_event_details($v->event_id);
    
    $arr = array (
        $v->regis_key,
        get_the_regis_status($v->status),
        $event_details['post_title'],
        epl_formatted_date($event_details['_epl_start_date'][$v->date_id]),
        $event_details['_epl_start_time'][$v->time_id],
        $event_details['_epl_price_name'][$v->price_id],
        $v->quantity
        
        
    );
    
    
    $epl->epl_table->add_row($arr);
}

echo $epl->epl_table->generate();
?>
