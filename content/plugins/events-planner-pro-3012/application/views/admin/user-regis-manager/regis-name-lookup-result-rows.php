
<?php

global $wpdb;

$s_key = epl_get_element( 's_key', $_REQUEST );

foreach ( $r['tickets'] as $ticket_id => $ticket_data ):

    $c_key = $s_key . '|' . $r['regis_post_id'] . '-' . $ticket_id;

    $q_c_key = '|' . $r['regis_post_id'] . '-' . $ticket_id;

    $num_used = $wpdb->get_row( "SELECT count(*) as cnt_used FROM {$wpdb->usermeta} WHERE meta_key like '%$q_c_key%' AND user_id = {$r['regis_user_id']}" );

    ?>
    <tr>
        <td>
            <?php echo $r['regis_id']; ?><br />
            <small><?php echo epl_formatted_date( $r['regis_date'] ); ?></small>
        </td>
        <td><?php echo $r['regis_status']; ?></td>
        <td><?php echo $r['first_name']; ?></td>
        <td><?php echo $r['last_name']; ?></td>
        <td><?php echo $r['email']; ?> (<?php echo $r['regis_user_id']; ?>)</td>
        <td><?php echo $ticket_data['ticket_name']; ?></td>
        <td><?php echo $num_used->cnt_used; ?></td>
        <?php ?>
        <td>
            <a href="" class="epl_check_in"  data-c_key="<?php echo $c_key; ?>" data-u_id="<?php echo $r['regis_user_id']; ?>">Select</a>
        </td>


    </tr>


<?php endforeach; ?>