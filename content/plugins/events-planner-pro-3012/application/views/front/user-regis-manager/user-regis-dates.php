<?php global $event_details; ?>

<style>

    #epl_user_schedule_wrapper table {

        border:1px solid #ccc;
}

#epl_user_schedule_wrapper .epl_user_schedule_table{
    
}

.epl_user_schedule_table td {
    padding:5px;
}
</style>

<h2><?php echo $event_title; ?> <span style="float:right;"><?php echo epl__( 'ID' ); ?> <?php echo $regis_id; ?></span></h2>

<table>

    <tr>
        <td></td>
        <td></td>
        <td><?php echo epl__( 'Status' ); ?></td>
        <td><?php echo get_the_regis_status(); ?></td>
    </tr>

</table>


<h3> <?php epl_e( 'Registered On' ); ?>: <?php echo $regis_date; ?></h3>

<!--<div style="float:right;width:200px;">
    <?php echo $regis_dates_cal; ?>
</div>-->
<?php if (!epl_is_empty_array($class_dates)): ?>
<table class="epl_user_schedule_table" cellspacing="0" style="">
    <thead>
        <tr>
            <th>Date</th>
            <th>Start Time</th>
            <th>End Time</th>
            <th>Notes</th>
            <th>Absent</th>
            <th></th>
        </tr>
    </thead>
    <tbody>
<?php

    foreach ( $class_dates as $_key => $_date ):
        $absentee_key = "_epl_user_absent_{$event_id}_{$_key}_" . get_current_user_id();


?>

        <tr>
            <td><?php echo epl_formatted_date( $_date ); ?></td>

        <?php $start_time = epl_get_element( $_key, $class_start_times ) ? epl_get_element( $_key, $class_start_times ) : current( $event_details['_epl_start_time'] ); ?>
            <td><?php echo $start_time; ?></td>
<?php $end_time = epl_get_element( $_key, $class_end_times ) ? epl_get_element( $_key, $class_end_times ) : current( $event_details['_epl_end_time'] ); ?>
            <td><?php echo $end_time; ?></td>
            <td><?php echo epl_get_element_m( $_key, '_epl_class_session_note', $event_details ); ?></td>
            <td>
<?php echo (epl_get_element( $absentee_key, $absentees )) ? epl__( 'Yes' ) : ($_date <= EPL_DATE ? epl__( 'No' ) : ''); ?>

            </td>
            <td><?php echo ($_date >= EPL_DATE) ? '<a href="#" class="epl_reschedule_link">Reschedule</a>' : ''; ?></td>
        </tr>

<?php endforeach; ?>
    </tbody>

    <?php endif; ?>

</table>

