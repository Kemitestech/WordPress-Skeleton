<?php global $event_details; ?>
<div class="epl_section">

    <div class="epl_section_header expand_trigger"><?php epl_e( 'Available Spaces' ); ?></div>
    <div class="toggle_container">


        <table class="epl_avail_spaces_table">
            <?php

            foreach ( $table_data as $date_id => $data ):

                if ( $data['date']['timestamp'] <= EPL_DATE && $event_details['_epl_event_status'] <> 3 )
                    continue;

                $date = $data['date']['disp'];
                $times = $data['time'];
                ?>

                <tr class="epl_date"><td><?php

                        echo $data['date']['disp'];
                        echo epl_prefix( ' - ', epl_get_element_m( $date_id, '_epl_date_note', $event_details, null ) );
                        ?></td><td><?php echo $data['date']['avail']; ?></td></tr>

                <?php

                if ( epl_is_addon_active( '_epl_atp' ) ):
                    foreach ( $times as $time_id => $time_data ):
                        //if ( $time_data['avail'] !== '' ):
                        $prices = $time_data['price'];

                        if ( $time_data['disp'] !== '' ):
                            ?>

                            <tr class="epl_time"><td><?php echo $time_data['disp']; ?></td><td><?php echo $time_data['avail']; ?></td></tr>

                            <?php

                        endif;

                        foreach ( $prices as $price_id => $price_data ):
                            //if ( $price_data['avail'] !== '' ):
                            ?>

                            <tr class="epl_price"><td><?php echo $price_data['disp']; ?></td><td><?php echo $price_data['avail']; ?></td></tr>

                            <?php

                            // endif;
                        endforeach;
                        ?>


                        <?php

                        //endif;
                    endforeach;
                endif;
                ?>



        <?php endforeach; ?>

        </table>
<?php //echo $available_spaces_table;        ?>



    </div>
</div>
