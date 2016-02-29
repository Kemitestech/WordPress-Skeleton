<?php

global $event_details, $wpdb;

$event_id = $event_details['ID'];
$erptm = EPL_report_model::get_instance();


$dates = $event_details['_epl_start_date'];


$event_date_keys = array_keys( $dates );

$times = $event_details['_epl_start_time'];
$pack_regis = (epl_get_element( '_epl_pack_regis', $event_details, 0 ) == 10);
$date_specifc_time = epl_get_element( '_epl_date_specific_time', $event_details, false );

$base_url = admin_url( 'edit.php?post_type=epl_event' );

$table_link_arr = array( 'epl_action' => 'view_names', 'epl_download_trigger' => 1, 'table_view' => 1, 'epl_controller' => 'epl_report_manager', 'event_id' => $event_id );
$csv_link_arr = array( 'epl_action' => 'epl_attendee_list', 'epl_download_trigger' => 1, 'epl_controller' => 'epl_registration', 'event_id' => $event_id );

$counts = $erptm->get_attendee_counts($event_id);


?>

<table id="event_snapshot_table" class="event_snapshot_sorting">

    <thead>
        <tr>
            <th style="padding-left:38px;"><?php epl_e( 'Day' ); ?></th>
            <th><?php epl_e( 'Date' ); ?></th>
            <th><?php epl_e( 'Time' ); ?></th>
            <th><?php epl_e( 'Status' ); ?></th>
            <th><?php epl_e( 'Attendees' ); ?></th>
            <th>
                <?php

                if ( epl_get_element( '_epl_pack_regis', $event_details ) == 10 )
                    echo epl_anchor( add_query_arg( array( 'epl_action' => 'epl_daily_schedule', 'table_view' => 1, 'epl_controller' => 'epl_registration', 'event_id' => $event_id ), $base_url ), epl__( 'Daily Schedule' ), null, 'class="epl_view_attendee_list_table button-secondary"' );

//echo epl_anchor( add_query_arg( $table_link_arr + array( 'names_only' => 1 ), $base_url ), epl__( 'View Names' ), null, 'class="epl_view_attendee_list_table button-secondary"' );
                echo epl_anchor( add_query_arg( $table_link_arr + array('names_only'=>1), $base_url ), epl__( 'View All Attendees' ), null, 'class="epl_view_attendee_list_table button-secondary"' );
                echo epl_anchor( add_query_arg( $table_link_arr, $base_url ), epl__( 'View Full Data' ), null, 'class="epl_view_attendee_list_table button-secondary"' );

                echo epl_anchor( add_query_arg( $csv_link_arr, $base_url ), epl__( 'Export Full CSV' ), null, 'class="button-secondary"' );
                ?>
            </th>

        </tr>
    </thead>
    <tbody>

        <?php

        foreach ( $dates as $date_id => $date ) {

            $date_capacity = epl_get_element_m( $date_id, '_epl_date_capacity', $event_details );

            foreach ( $times as $time_id => $time ) {

                if ( epl_is_date_level_time() && $date_specifc_time && (!isset( $date_specifc_time[$time_id] ) || !isset( $date_specifc_time[$time_id][$date_id] )) )
                    continue;


                $time_capacity = epl_get_element_m( $time_id, '_epl_time_capacity', $event_details );
                $capacity = ($time_capacity) ? $time_capacity : ($date_capacity ? $date_capacity : epl_get_element_m( $date_id, '_epl_date_per_time_capacity', $event_details, '&#8734;' ));

                $num_regis = epl_get_element( "_total_att_{$event_id}_time_{$date_id}_{$time_id}", $counts, 0 );
                $links = '';
                if ( $num_regis > 0 ) {

                    $dt_array = array(
                        'date_id' => $date_id,
                        'time_id' => $time_id
                    );

                    $table_link_arr = array_merge( $table_link_arr, $dt_array );
                    $csv_link_arr += $dt_array;
                    $send_email_arr = array(
                        'epl_action' => 'get_the_email_form',
                        'epl_controller' => 'epl_registration',
                        'event_id' => $event_id,
                        'post_type' => false
                    );
                    $links = epl_anchor( add_query_arg( array_merge( $table_link_arr, $dt_array ) + array( 'names_only' => 1 ), $base_url ), epl__( 'View Attendees' ), null, 'class="epl_view_attendee_list_table button-secondary"' );

                    // $table_link_arr['epl_action'] = 'epl_attendee_list';
                    // $table_link_arr['epl_controller'] = 'epl_registration';
                    $links .= epl_anchor( add_query_arg( $table_link_arr, $base_url ), epl__( 'View Full Data' ), null, 'class="epl_view_attendee_list_table button-secondary"' );
                    $links .= epl_anchor( add_query_arg( array_merge( $csv_link_arr, $dt_array ), $base_url ), epl__( 'Export CSV' ), null, 'class="button-secondary"' );
                    $links .= epl_anchor( add_query_arg( $send_email_arr + $dt_array, $base_url ), epl__( 'Send Email' ), null, "class='epl_send_email_form_link button-secondary' data-post_ID='$post_ID' data-event_id='$event_id'" );
                }

                $row = array(
                    epl_formatted_date( $date, 'D' ),
                    epl_formatted_date( $date ),
                    epl_is_time_optonal() ? epl__( 'All Day' ) : $time . ' - ' . $event_details['_epl_end_time'][$time_id],
                    epl_get_regis_status( $date_id ),
                    $num_regis . '/' . $capacity,
                    $links
                );

                echo '<tr><td>' . implode( '</td><td>', $row ) . "</td><tr>";
            }
        }
        ?>
    </tbody>
</table>