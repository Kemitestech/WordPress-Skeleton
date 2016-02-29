<?php

global $event_details, $location_details;
//$datepicker_dates = array( );
$datepicker_data = '';
$no_dates_selected = $show_date_selector_link ? "Please use the calendar above to select a date." : '';
$event_id = $event_details['ID'];
if ( $show_date_selector_link ):
    ?>
    <div class="epl_section">
        <div  class="epl_section_header"><?php epl_e( 'Please click to select a date' ); ?></div>

        <div id="epl_date_selector-<?php echo $event_id; ?>" class="epl_date_selector" style="margin-top:10px;"></div>
    </div>

<?php endif; ?>

<div id="epl_cart_dates_body-<?php echo $event_id; ?>">

    <?php

    foreach ( $date as $date_id => $field ):

        //will revisit, for the datepicker
        //$datepicker_dates[] = array( 'Title' => 'something', 'Date' =>date_i18n( 'D, d M y H:i:s', epl_get_element( $date_id, $event_details['_epl_start_date'] ) ) . " +0000" );
        //skip expired event dates
        //  if ( epl_get_element( '_epl_event_status', $event_details ) != 3 && epl_get_date_timestamp( $event_details['_epl_start_date'][$date_id] ) < EPL_DATE )
        //    continue;



        ob_start();
        ?>

        <div id="<?php echo $date_id; ?>" class="epl_date_individual_date_wrapper" style="border-bottom:0px solid #000; margin-bottom: 2px;background-color: #efefef;">


            <div class="epl_date_individual_date">

                <?php echo $field['field']; ?>
                <?php echo epl_get_element( $date_id, epl_get_element( '_epl_date_note', $event_details ) ); ?>
                <?php if ( $event_type == 6 && $show_date_selector_link && $mode == 'edit' ): ?>
                    <span class="epl_delete_date" style="float: right">
                        <img src="<?php echo EPL_FULL_URL; ?>/images/cross.png" class="epl_cur_pointer" alt="<?php epl_e( 'Delete' ); ?>" />
                    </span>
                <?php endif; ?>
            </div>
            <div class="epl_date_extra">



                <?php if ( epl_get_element( '_epl_date_location', $event_details ) && $event_details['_epl_date_location'][$date_id] != '' ): ?>
                    <?php

                    the_location_details( $event_details['_epl_date_location'][$date_id] ); //sets up the location info
                    $map_text = apply_filters( 'epl_get_the_location_gmap_icon_text_cart_dates', epl__( 'See Map' ) );
                    echo epl__( 'Location' ) . ': ' . get_the_location_name() . ' ' . get_the_location_address() . ' ' . get_the_location_city() . ' ' . get_the_location_state() . ' ' . get_the_location_zip();
                    ?>
                <?php endif; ?>

            </div>
            <?php if ( isset( $time[$date_id] ) ): ?>

                <div class="epl_ind_time_wrapper"> <?php epl_e( 'Time' ); ?> <?php echo $time[$date_id]; ?></div>


            <?php endif; ?>
            <?php if ( isset( $prices[$date_id] ) ): ?>

                <div class="epl_ind_price_wrapper"> <?php echo $prices[$date_id]; ?></div>


            <?php endif; ?>
        </div>


        <?php

        $date = ob_get_contents();
        @ob_end_clean();

        if ( $show_date_selector_link && (!in_array( $date_id, $value )) ) {
            echo $no_dates_selected; //echo just once
            $no_dates_selected = '';
        }
        else
            echo $date;

        $_d = str_replace( array( '"', "\n", "\r" ), array( "'", "", '' ), $date );

        $datepicker_data .= '{Field:"' . $_d . '",Date: new Date("' . date_i18n( 'm/d/Y', epl_get_element( $date_id, $event_details['_epl_start_date'] ) ) . '")},';
    endforeach;
    ?>
</div>
<?php if ( $show_date_selector_link ): ?>

    <script>

        jQuery(document).ready(function($){

            //this is the only way I can make it work.
            var events = [<?php echo $datepicker_data; ?>];

            //will revisit this
            //var events = <?php //echo json_encode( $datepicker_dates );          ?>;

            event_type = '<?php echo $event_type; ?>';

            $("#epl_date_selector-" + '<?php echo $event_id; ?>').datepicker({
                numberOfMonths: <?php echo epl_get_element( '_epl_front_date_selector_num_cals', $event_details, 2 ) ?>,
                firstDay: EPL.firstDay,
                beforeShowDay: function(date) {
                    var result = [true, '', null];
                    var matching = $.grep(events, function(event) {
                        if(typeof event != 'undefined')
                            return event.Date.valueOf() === date.valueOf();
                        return false;
                    });

                    if (matching.length) {
                        result = [true, 'highlight', null];
                    }
                    return result;
                },
                onSelect: function(dateText) {
                    var date,
                    selectedDate = new Date(dateText),
                    i = 0,
                    event = null;

                    while (i < events.length && !event) {
                        date = events[i].Date;

                        if (selectedDate.valueOf() === date.valueOf()) {
                            event = events[i];
                        }
                        i++;
                    }

                    if (event) {
                                            
                                            
                        var _f = $(event.Field);

                        var id = _f.prop('id');

                        var wrapper = '#epl_cart_dates_body-' + '<?php echo $event_id; ?>';

                        if($(wrapper + ' div#'+ id).length >0){
                            alert('Already selected.');
                            return;
                        }

                        if (event_type == 5){
                            $(wrapper).html(_f);
                            $(wrapper +' input:radio').prop('checked', true);
                        }
                        else {
                            $(wrapper).append(_f);
                            $(wrapper + ' input:checkbox').prop('checked', true);
                        }
                                        
                    }
                }
            });



        });

    </script>

<?php endif; ?>