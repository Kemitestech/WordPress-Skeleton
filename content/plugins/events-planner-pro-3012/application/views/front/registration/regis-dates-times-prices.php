

<?php

global $event_details;

foreach ( $table_data as $event_id => $date_data ):
    setup_event_details( $event_id );
    ?>
    <table class="epl_dates_times_prices_table" >
        <tr class="">
            <td><h2><?php echo epl_format_string( $event_details['post_title'] ) ?></h2></td>
            <td class="epl_w200">
                <?php if ( !epl_is_multi_location() && epl_get_event_property( '_epl_event_location', true ) > 0 ): ?>
                    <div class="">

                        <?php echo get_the_location_name(); ?><br />
                        <?php echo get_the_location_address(); ?> <?php echo get_the_location_address2(); ?><br />
                        <?php echo get_the_location_city(); ?> <?php echo get_the_location_state(); ?> <?php echo get_the_location_zip(); ?><br />
                    </div>
                <?php endif; ?>

            </td>
        </tr>

        <?php if ( epl_get_setting( 'epl_registration_options', 'epl_show_event_details_on_conf' ) == 10 ): ?>
            <tr>
                <td colspan="2">
                    <div class="epl_section">

                        <div class="epl_section_header expand_trigger"><?php epl_e( 'Event Details' ); ?></div>
                        <div class="toggle_container">
                            <?php

                            echo stripslashes_deep( do_shortcode( $event_details['post_content'] ) );
                            ?>

                        </div>
                    </div>
                </td>
            </tr>
        <?php endif; ?>

        <?php

        foreach ( $date_data as $date_id => $data ):
            $date = $data['date']['disp'];
            $times = $data['time'];
            $date_note = epl_get_element_m( $date_id, '_epl_date_note', $event_details, '' );
            ?>

            <tr class="epl_date">
                <td><?php echo $data['date']['disp']; ?><?php echo $date_note; ?></td>
                <td><?php echo epl_get_element( 'location', $data['date'], '&nbsp;' ); ?></td>
            </tr>

            <?php

            foreach ( $times as $time_id => $time_data ):
                if ( $time_id == 'total_tickets' )
                    continue;
                //if ( epl_get_element( $time_id, epl_get_element( '_epl_time_hide', $event_details ) ) == 0 ):
                $prices = $time_data['price'];
                ?>

                <tr class="epl_time"><td><?php echo epl_get_element( 'disp', $time_data ); ?></td><td style="width: 200px;"><?php epl_e( 'Qty' ); ?></td></tr>

                <?php

                foreach ( $prices as $price_id => $price_data ):
                    if ( epl_get_element( $price_id, epl_get_element( '_epl_price_hide', $event_details ) ) == 0 ):
                        ?>

                        <tr class="epl_price">
                            <td><?php echo $price_data['disp']; ?></td>                           
                            <td><?php echo $price_data['qty']; ?> x <?php echo epl_get_formatted_curr( $price_data['ticket_price'], null, true ); ?></td>
                        </tr>

                        <?php

                    endif;
                endforeach;
                ?>


                <?php

                //endif;
            endforeach;
            ?>



        <?php endforeach; ?>

    </table>
<?php endforeach; ?>


