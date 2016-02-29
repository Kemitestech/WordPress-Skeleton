<div class="epl_section">

    <div class="epl_section_header expand_trigger"><?php epl_e( 'Available Spaces' ); ?></div>
    <div class="toggle_container">


        <table class="epl_avail_spaces_table">
            <?php

            foreach ( $table_data as $date_id => $data ):
                $date = $data['date']['disp'];
                $times = $data['time'];
            ?>




                <tr class="date"><td><?php echo $data['date']['disp']; ?></td><td><?php echo $data['date']['avail']; ?></td></tr>

            <?php

                if ( epl_is_addon_active( '_epl_atp' ) ):
                    foreach ( $times as $time_id => $time_data ):
                        //if ( $time_data['avail'] !== '' ):
                        $prices = $time_data['price'];

                        if ( $time_data['disp'] !== '' ):
            ?>

                            <tr class="time"><td><?php echo $time_data['disp']; ?></td><td><?php echo $time_data['avail']; ?></td></tr>


            <?php

                            endif;
                            
                            foreach ( $prices as $price_id => $price_data ):
                                if ( $price_data['avail'] !== '' ):
            ?>

                                    <tr class="price"><td><?php echo $price_data['disp']; ?></td><td><?php echo $price_data['avail']; ?></td></tr>

            <?php endif;
                                endforeach; ?>


            <?php

                                //endif;
                                endforeach;
                            endif;
            ?>



<?php endforeach; ?>

                        </table>
<?php //echo $available_spaces_table;     ?>



    </div>
</div>
