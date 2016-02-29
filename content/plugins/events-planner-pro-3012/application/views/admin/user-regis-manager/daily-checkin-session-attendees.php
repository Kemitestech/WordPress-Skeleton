<?php

global $event_details, $wpdb;

$event_id = epl_get_element( 'event_id', $_REQUEST );

?>
<table class="epl_form_data_table epl_w500" cellpadding="0" cellspacing="0">
    <thead>
        <tr>
            <th>Name</th>
            <th>Purchase</th>
            <th># Used</th>
            <th></th>
        </tr>
    </thead>
    <tbody>
        <?php

        foreach ( $currently_checked_in as &$checkin_data ):
            
            $checkin_data = str_replace('_epl_checkedin_', '', $checkin_data);

            $u = get_userdata( $checkin_data['user_id'] );
            
            //meta key comes in as $event_id-datetimestamp-timeid|regisid-price
            
            $m = explode( '|', $checkin_data['meta_key'] ); //checkin |regis_id|priceid

            //sign in info
            $s_info = explode('-', $m[0]);
            //regis info
            $r_info = explode('-', $m[1]);


            $r = $wpdb->get_row( "SELECT count(*) as cnt_used FROM {$wpdb->usermeta} WHERE meta_key like '%{$m[1]}%' AND user_id = {$checkin_data['user_id']}" );

            ?>
            <tr>
                <td><?php echo $u->user_firstname; ?> <?php echo $u->user_lastname; ?></td>
                <td><?php echo $event_details['_epl_price_name'][$r_info[1]]; ?></td>
                <td><?php echo $r->cnt_used; ?></td>
                <td><a href="#" class="epl_delete_checkin_record" data-umeta_id="<?php echo $checkin_data['umeta_id']; ?>">DELETE</a></td>
            </tr>
        <?php endforeach; ?>

    </tbody>

</table>