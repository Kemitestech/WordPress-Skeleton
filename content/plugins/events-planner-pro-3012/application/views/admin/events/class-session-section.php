<div class="for_class" style="">

    <table class="epl_form_data_table epl_w800" cellspacing ="0" id="epl_class_session_table">
        <thead>
        <th></th>
        <?php echo epl_get_the_labels( $class_session_field_labels ); ?>
        <th><img src="<?php echo EPL_FULL_URL; ?>images/calendar.png" class="load_fullcalendar epl_cur_pointer" /></th>
        </thead>
        <tfoot>
            <tr>
                <td colspan="7" style="vertical-align: middle;">


                    <a href="#" class="add_table_row"><img src ="<?php echo EPL_FULL_URL ?>images/add.png" /></a>
                </td>
            </tr>


        </tfoot>
        <tbody class="events_planner_tbody">

            <?php foreach ( $class_session_fields as $k => $row ) : ?>
              <tr class="copy_">
                    <td>
                        <div class="handle"></div>
                    </td>
                <?php

                echo $row;
                ?>
                <td>

                    <div class="epl_action epl_delete"></div>
                </td>


            </tr>

<?php endforeach; ?>

        </tbody>


    </table>


</div>

