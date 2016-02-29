<?php

if ( epl_is_empty_array( $event_snapshot ) ) {
    epl_e( 'Please add dates for the event.' );
    return;
}
$event_regis_data = current( $event_snapshot );
//echo "<pre class='prettyprint'>" . __LINE__ . "> " . print_r($event_regis_data, true). "</pre>";
global $event_details;

$event_id = $event_details['ID'];

$base_url = admin_url( 'edit.php?post_type=epl_event' );

$table_link_arr = array( 'epl_action' => 'view_names', 'epl_download_trigger' => 1, 'table_view' => 1, 'epl_controller' => 'epl_report_manager', 'event_id' => $event_id );
$csv_link_arr = array( 'epl_action' => 'epl_attendee_list', 'epl_download_trigger' => 1, 'epl_controller' => 'epl_registration', 'event_id' => $event_id );
?>


<table id="event_snapshot_table" class="event_snapshot_sorting">

    <thead>
        <tr>
            <th style="padding-left:38px;"><?php epl_e( 'Day' ); ?></th>
            <th><?php epl_e( 'Date' ); ?></th>
            <th><?php epl_e( 'Start Time' ); ?></th>
            <th><?php epl_e( 'End Time' ); ?></th>
            <th><?php epl_e( 'Status' ); ?></th>
            <th><?php epl_e( 'Attendees' ); ?></th>
            <th>
                <?php

                //epl_e( 'Actions' );
                if(epl_get_element('_epl_pack_regis',$event_details)==10)
                echo epl_anchor( add_query_arg( array( 'epl_action' => 'epl_daily_schedule', 'table_view' => 1, 'epl_controller' => 'epl_registration', 'event_id' => $event_id ), $base_url ), epl__( 'Daily Schedule' ), null, 'class="epl_view_attendee_list_table button-secondary"' );
                
                //echo epl_anchor( add_query_arg( $table_link_arr + array( 'names_only' => 1 ), $base_url ), epl__( 'View Names' ), null, 'class="epl_view_attendee_list_table button-secondary"' );
                echo epl_anchor( add_query_arg( $table_link_arr, $base_url ), epl__( 'View Full Data' ), null, 'class="epl_view_attendee_list_table button-secondary"' );
                echo epl_anchor( add_query_arg( $table_link_arr, $base_url ), epl__( 'View All Attendees' ), null, 'class="epl_view_attendee_list_table button-secondary"' );
                echo epl_anchor( add_query_arg( $csv_link_arr, $base_url ), epl__( 'Export Full CSV' ) , null, 'class="button-secondary"');
                ?>
            </th>

        </tr>

    </thead>

    <?php
    $date_specifc_time = epl_get_element( '_epl_date_specific_time', $event_details, array( ) );
//echo "<pre class='prettyprint'>" . __LINE__ . "> " . print_r($event_regis_data, true). "</pre>";

                foreach ( $event_regis_data as $date_id => $date_data ):
                    
                    if($date_data['date']['timestamp'] < 1364083200)
                        continue;
                    
                    $date = $date_data['date']['disp'];

                    $date_capacity = epl_get_element_m( $date_id, '_epl_date_capacity', $event_details );
                    $times = $date_data['time'];


                    foreach ( $times as $time_id => $time_data ):

                        if ( epl_is_date_level_time() && !epl_is_empty_array( $date_specifc_time ) && (!isset( $date_specifc_time[$time_id] ) || !isset( $date_specifc_time[$time_id][$date_id] )) )
                            continue;
                        $time_capacity = epl_get_element_m( $time_id, '_epl_time_capacity', $event_details );
                        $capacity = ($time_capacity) ? $time_capacity : ($date_capacity ? $date_capacity : epl_get_element_m( $date_id, '_epl_date_per_time_capacity', $event_details ));
    ?>

                        <tr class="epl_date_time">
                            <td style="padding-left:38px;"><?php echo date_i18n( 'D', epl_get_element( $date_id, $event_details['_epl_start_date'] ) ); ?></td>
                            <td><?php echo date_i18n( 'M d, Y', epl_get_element( $date_id, $event_details['_epl_start_date'] ) ); ?></td>

<?php if ( epl_is_time_optonal ( ) ): ?>
                                <td colspan="2"><?php epl_e( 'All Day' ); ?></td>
<?php else: ?>
                                <td><?php echo $time_data['disp']; ?></td>
                                <td><?php echo epl_get_element( $time_id, $event_details['_epl_end_time'] ); ?></td>
<?php endif; ?>
                                <td><?php echo epl_get_regis_status( $date_id ); ?></td>
                                <td><?php echo $time_data['regis']; ?> / <?php echo ($capacity) ? $capacity : '&#8734;'; ?></td>


                                <td>


<?php

                                if ( $time_data['regis'] ) {

                                    $dt_array = array(
                                        'date_id' => $date_id,
                                        'time_id' => $time_id
                                    );

                                    $table_link_arr = array_merge($table_link_arr, $dt_array);
                                    $csv_link_arr += $dt_array;
                                    $send_email_arr = array(
                                        'epl_action' => 'get_the_email_form',
                                        'epl_controller' => 'epl_registration',
                                        'event_id' => $event_id,
                                        'post_type' => false
                                    );
                                   echo epl_anchor( add_query_arg( array_merge($table_link_arr, $dt_array) + array( 'names_only' => 1 ), $base_url ), epl__( 'View Names' ), null, 'class="epl_view_attendee_list_table button-secondary"' );
                                    echo epl_anchor( add_query_arg( $table_link_arr, $base_url ), epl__( 'View Full Data' ), null, 'class="epl_view_attendee_list_table button-secondary"' );
                                    echo epl_anchor( add_query_arg( array_merge($csv_link_arr,$dt_array), $base_url ), epl__( 'Export CSV' ), null, 'class="button-secondary"' );
                                    echo epl_anchor( add_query_arg( $send_email_arr + $dt_array, $base_url ), epl__( 'Send Email' ), null, "class='epl_send_email_form_link button-secondary' data-post_ID='$post_ID' data-event_id='$event_id'" );
                                }
?>
                            </td>
                        </tr>

<?php

                                endforeach;
?>



<?php endforeach; ?>

</table>
