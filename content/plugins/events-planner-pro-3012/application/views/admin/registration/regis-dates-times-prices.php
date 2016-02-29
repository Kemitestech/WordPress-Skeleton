
    <table class="epl_dates_times_prices_table" >
        <?php
        global $event_details;


        foreach ( $table_data as $date_id => $data ):
            $date = $data['date']['disp'];
            $times = $data['time'];
        ?>

        <tr class="epl_date"><td><?php echo $data['date']['disp']; ?></td><td>&nbsp;</td></tr>

        <?php

            foreach ( $times as $time_id => $time_data ):
                //if ( epl_get_element( $time_id, epl_get_element( '_epl_time_hide', $event_details ) ) == 0 ):
                    $prices = $time_data['price'];
        ?>

                    <tr class="epl_time"><td><?php echo epl_get_element('disp', $time_data); ?></td><td><?php epl_e('Qty'); ?></td></tr>


        <?php

                    foreach ( $prices as $price_id => $price_data ):
                        if ( epl_get_element( $price_id, epl_get_element( '_epl_price_hide', $event_details ) ) == 0 ):
        ?>

                            <tr class="epl_price"><td><?php echo $price_data['disp']; ?></td><td><?php echo $price_data['qty']; ?></td></tr>

        <?php endif;
                        endforeach; ?>


        <?php //endif;
                    endforeach; ?>



<?php endforeach; ?>

    </table>
