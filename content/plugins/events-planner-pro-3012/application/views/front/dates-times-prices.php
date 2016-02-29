<?php

global $event_details, $event_snapshot;
$date_specifc_time = epl_get_element( '_epl_date_specific_time', $event_details, array() );
$show_avail_spaces = ($event_details['_epl_event_available_space_display'] == 10);

//I hate doing this in the view :(
if ( $show_avail_spaces ) {
    EPL_registration_model::get_instance()->event_snapshot( $event_details['ID'] );
    $table_data = epl_get_element( $event_details['ID'], $event_snapshot, array() );
}
?>

<div class="epl_section">

    <table class="epl_avail_spaces_table">
        <?php

        global $event_details;

        if ( $show_avail_spaces ):
            ?>
            <thead>
                <tr>
                    <th></th>
                    <th></th>
                    <th><?php echo epl_e( 'Available Spaces' ); ?></th>
                </tr>
            </thead>


            <?php

        endif;

        foreach ( $table_data as $date_id => $data ):
            
            $date = '<span itemprop="startDate" content="' . epl_formatted_date( $data['date']['disp'], 'Y-m-d') . '">' . $data['date']['disp'] . '</span>';
            $times = $data['time'];

            $_note = epl_get_element_m( $date_id, '_epl_date_note', $event_details );

            $_location = '';
            if ( epl_get_element( $date_id, epl_get_element( '_epl_date_location', $event_details ) ) ) {
                $_location_id = epl_get_element( $date_id, $event_details['_epl_date_location'] );
                $l = the_location_details( $_location_id ); //sets up the location info
                $_location = $l['post_title']; 
            }
            ?>

            <tr class="epl_date">
                <td><?php echo $date  . epl_prefix(' - ', $_note) . epl_prefix(' - ', $_location); ?></td>
                <td></td>
                <td><?php echo $show_avail_spaces ? $data['date']['avail'] : ''; ?></td>

            </tr>

            <?php

            foreach ( $times as $time_id => $time_data ):
                //if ( $time_data['avail'] !== '' ):

                if ( epl_get_element( $time_id, epl_get_element( '_epl_time_hide', $event_details ) ) == 0 ):
                    if ( !epl_is_date_level_time() || (epl_is_date_level_time() && !epl_is_empty_array( $date_specifc_time ) && (array_key_exists( $time_id, $date_specifc_time ) && array_key_exists( $date_id, $date_specifc_time[$time_id] ))) ):
                        $prices = $time_data['price'];

                        if ( !epl_is_time_optonal() ):
                            ?>
                            <tr class="epl_time">
                                <td><?php echo $time_data['disp'] . epl_prefix (' - ', $event_details['_epl_end_time'][$time_id]) . epl_prefix( ' - ', epl_get_element_m( $time_id, '_epl_time_note', $event_details ) ); ?></td>
                                <td></td>
                                <td><?php echo $show_avail_spaces ? $time_data['avail'] : ''; ?></td>
                            </tr>
                            <?php

                        endif;
                        foreach ( $prices as $price_id => $price_data ):

                            if ( epl_is_time_specific_price( $price_id ) && !epl_get_element_m( $time_id, $price_id, epl_get_element( '_epl_time_specific_price', $event_details ) ) )
                                continue;
                            if ( epl_is_date_specific_price( $price_id ) && !epl_get_element_m( $date_id, $price_id, epl_get_element( '_epl_date_specific_price', $event_details ) ) )
                                continue;

                            if ( epl_get_element( $price_id, epl_get_element( '_epl_price_hide', $event_details ) ) == 0 ):
                                ?>

                                <tr class="epl_price">
                                    <td><?php echo $price_data['disp']; ?></td>
                                    <td><?php echo epl_get_formatted_curr( $event_details['_epl_price'][$price_id], null, true ); ?></td>
                                    <td><?php echo $show_avail_spaces ? $price_data['avail'] : ''; ?></td>
                                </tr>

                                <?php

                            endif;
                        endforeach;
                        ?>


                        <?php

                    endif;
                endif;
            endforeach;
            ?>



        <?php endforeach; ?>

    </table>
</div>