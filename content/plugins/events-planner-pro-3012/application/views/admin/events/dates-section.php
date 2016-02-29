<?php

$display_note_field = ( epl_nz( epl_get_event_option( 'epl_date_note_enable' ), 10 ) == 10 );
$display_location_dd = ( epl_nz( epl_get_event_option( 'epl_date_location' ), 10 ) == 10 );
?>



<?php echo epl_show_ad( 'Control Registration Start and End times in the pro version.' ); ?>
<table class="epl_form_data_table" cellspacing ="0" id="epl_dates_table">
    <thead>
    <th></th>
    <?php //echo epl_get_the_labels( $date_field_labels );  ?>
    <th colspan="3"><img src="<?php echo EPL_FULL_URL; ?>images/calendar.png" class="load_fullcalendar epl_cur_pointer epl_fr" /></th>
</thead>
<tfoot>
    <tr>
        <td colspan="7" style="vertical-align: middle;">
            <a href="#" class="add_table_row"><img src ="<?php echo EPL_FULL_URL ?>images/add.png" /></a>
        </td>
    </tr>


</tfoot>
<tbody class="events_planner_tbody">

    <?php

    foreach ( $date_fields as $date_id => $row ) :
        $num_regis = epl_get_from_snapshot( array( 'event_id' => $event_id, 'section' => 'date', 'date_id' => $date_id, 'index' => 'regis' ) );
        ?>


        <tr class="copy_">
            <td>
                <div class="handle"></div>
            </td>

            <td>
                <table class="epl_form_data_table epl_dates_row_table" cellspacing ="0">


                    <thead>
                        <tr>
                            <th><?php echo $row['_epl_start_date']['label']; ?></th>
                            <th><?php echo $row['_epl_end_date']['label']; ?></th>
                            <th><?php echo $row['_epl_regis_start_date']['label']; ?></th>
                            <th><?php echo $row['_epl_regis_end_date']['label']; ?></th>
                            <th><?php echo $row['_epl_date_capacity']['label']; ?></th>

                            <?php do_action( 'epl_admin_view_event_dates_section_header_row', $date_id, $row ); ?>
                        </tr>
                    </thead>

                    <tbody>
                        <tr>

                            <td><?php echo $row['_epl_start_date']['field']; ?></td>
                            <td><?php echo $row['_epl_end_date']['field']; ?></td>
                            <td><?php echo $row['_epl_regis_start_date']['field']; ?></td>
                            <td><?php echo $row['_epl_regis_end_date']['field']; ?></td>

                            <td><?php echo $row['_epl_date_capacity']['field']; ?>




                        </tr>
                        <tr>
                            <?php if ( $display_note_field ): ?>
                                <td colspan="3"><?php echo $row['_epl_date_note']['field']; ?></td>
                            <?php endif; ?>

                            <?php if ( $display_location_dd ): ?>
                                <td colspan="2"><?php echo $row['_epl_date_location']['field']; ?></td>
                            <?php endif; ?>

                        </tr>

                        <?php do_action( 'epl_admin_view_event_dates_section_data_row', $date_id, $row ); ?>

                    </tbody>

                </table>

            </td>



            <td>
                <div class="epl_action epl_delete"></div>
            </td>


        </tr>
        <!--<tr class="extra_">
            <td colspan="5" style="border:none;color:blue;font-style: italic;text-align: left;">
                └ <?php echo $num_regis; ?> <?php epl_e( 'registered' ); ?></td>
                
            </td>
            
        </tr>-->

    <?php endforeach; ?>

</tbody>


</table>

<?php    do_action ('epl_admin_view_event_dates_section_bottom'); ?>
    