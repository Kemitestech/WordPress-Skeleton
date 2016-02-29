<?php global $event_details, $epl_current_step; ?>

<div class="epl_cart_wrapper">

    <?php if ( isset( $message ) ): ?>
        <div class="epl_regis_message_warn epl_rounded_corners_10">
            <?php echo $message; ?>
        </div>

    <?php endif; ?>


    <?php if ( isset( $error ) && $error != '' ): ?>

        <div class="epl_error">
            <?php echo $error; ?>
        </div>

    <?php endif; ?>

    <?php foreach ( ( array ) $cart_data['cart_items'] as
                $k => $event ): ?>

        <div class="event_name epl_section"><?php echo $event['title']; ?></div>
    <?php if ( epl_get_element( '_epl_event_detail_cart_display', $event_details, 10 ) == 10 && $mode != 'overview' ): ?>
            <div class="epl_section">

                <div class="epl_section_header expand_trigger"><?php epl_e( 'Event Details' ); ?></div>
                <div class="toggle_container">


                    <?php

                    echo nl2br( stripslashes_deep( do_shortcode( $event_details['post_content'] ) ) );
                    ?>

                </div>
            </div>
        <?php endif; ?>
        <?php if ( isset( $cart_data['available_spaces'][$k] ) && $mode != 'overview' ): ?>

            <?php echo $cart_data['available_spaces'][$k]; ?>

        <?php endif; ?>

                <?php if ( $event['show_date_selector_cal'] != 0 ): ?>
            <div class="epl_section">
                <div  class="epl_section_header"><?php epl_e( 'Please click to select a date' ); ?></div>

                <div id="epl_date_selector" style="margin-top:10px;"></div>
            </div>
    <?php endif; ?>

        <div class="epl_section epl_cart_dates">
            <div  class="epl_section_header"><?php //epl_e( 'Dates' ); ?></div>
    <?php //echo $cart_data['fc_cal'];  ?>

                    <div id="epl_cart_dates_body-<?php echo $event_id;?>">


    <?php echo $event['event_dates']; ?>
            </div>
        </div>

            <?php if ( $event['event_time_and_prices'] ): ?>
            <div class="epl_section epl_cart_time_price_epl_section">
            <?php echo $event['event_time_and_prices']; ?>
            </div>
    <?php endif; ?>



<?php endforeach; ?>

</div>






<?php if ( !epl_is_free_event() ): ?>

        <div id="epl_totals_wrapper" class="epl_section epl_ov_a">

            <?php

            echo $cart_data['cart_totals'];
            ?>
            <?php if ( $mode != 'overview' ): ?>
                <a href="#" id="calculate_total_due" class="epl_button_small epl_fr"><?php epl_e( 'Update Total' ); ?></a>
    <?php endif; ?>
        </div>

    <?php if ( $mode != 'overview' ): ?>
            <div class="epl_section">

                <?php

                echo $cart_data['pay_options'];
                ?>
            </div>
        <?php endif; ?>
<?php endif; ?>






<script>
    jQuery(document).ready(function($){

        $('body').on('click', '.epl_date_individual_date_wrapper .epl_delete_date', function(){

            var p = $(this).parents('.epl_date_individual_date_wrapper');
            p.slideUp(400, function(){
                p.remove();
                calculate_total_due();
            });



        });


        $('.load_date_selector_cal').click(function(){
            var par = $(this).closest('table');

            var data = "epl_action=load_date_selector_cal&epl_controller=epl_front&date_selector=1&event_id=" + <?php echo epl_get_element( 'event_id', $_GET, "" ); ?>;

            events_planner_do_ajax( data, function(r){

                show_slide_down(r.html);

            });


            return false;

        });

        //$('#calculate_total_due').trigger('click');
    });


</script>