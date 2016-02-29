<?php global $event_details, $epl_current_step; ?>

<div class="epl_cart_wrapper">
    <?php

    epl_cart_top();
    ?>
    <?php if ( isset( $message ) ): ?>
        <div class="epl_regis_message_warn">
            <?php echo $message; ?>
        </div>

    <?php endif; ?>


    <?php if ( isset( $error ) && $error != '' ): ?>

        <div class="epl_error">
            <?php echo $error; ?>
        </div>

    <?php endif; ?>

    <?php

    foreach ( ( array ) $cart_data['cart_items'] as $event_id => $event ):
        setup_event_details( $event_id );

        $max_regis = epl_get_element( '_epl_registration_max', $event_details, null );
        ?>


        <div class="epl_individual_event_wrapper epl_individual_event_wrapper-<?php echo $event_id; ?>" style="">
            <?php if ( !EPL_IS_ADMIN ): ?>
                <input type="hidden" class="epl_registration_max" value="<?php echo $max_regis; ?>" />            
            <?php endif; ?>
            <div class="epl_cart_controls">

                <?php if ( $mode != 'overview' && epl_sc_is_enabled() ): ?>
                    <a href ="#" class="delete_cart_item" id="<?php echo $event_id; ?>">Delete</a>
                <?php endif; ?>
            </div>
            <div class='epl_event_title'><?php echo $event['title']; ?></div>
            <?php if ( !EPL_IS_ADMIN && $max_regis > 0 && apply_filters( 'epl_cart__show_max_regis_message', __return_true() ) ): ?>
                <div class="epl_info">
                    <?php epl_e( sprintf( 'Please note that you can register a maximum of %d people for this event.', $max_regis ) ); ?>
                </div>
            <?php endif; ?>
            <div class="epl_event_selections"> 


                <?php if ( epl_get_element( '_epl_event_detail_cart_display', $event_details, 10 ) == 10 && $mode != 'overview' ): ?>
                    <div class="epl_section">

                        <div class="epl_section_header expand_trigger"><?php epl_e( 'Event Details' ); ?></div>
                        <div class="toggle_container">
                            <?php

                            echo stripslashes_deep( do_shortcode( $event_details['post_content'] ) );
                            ?>

                        </div>
                    </div>
                <?php endif; ?>
                <?php if ( isset( $cart_data['available_spaces'][$event_id] ) && $mode != 'overview' ): ?>

                    <?php echo $cart_data['available_spaces'][$event_id]; ?>

                <?php endif; ?>

                <?php if ( $event['show_date_selector_cal'] != 0 && $mode != 'overview' ): ?>
                    <div class="epl_section">
                        <div  class="epl_section_header"><?php epl_e( 'Please click to select a date' ); ?></div>

                        <div id="epl_date_selector-<?php echo $event_id; ?>" class="epl_date_selector" style="margin-top:10px;"></div>
                    </div>
                <?php endif; ?>

                <div class="epl_section epl_cart_dates">
                    <?php $date_label = apply_filters( 'epl_cart_dates_section_header_label', epl__( 'Dates' ) ); ?>

                    <div class="epl_section_header"><?php //echo $date_label;         ?></div>

                    <div id="epl_cart_dates_body-<?php echo $event_id; ?>">

                        <?php echo $event['event_dates']; ?>
                    </div>
                </div>

                <?php if ( $event['event_time_and_prices'] ): ?>
                    <div class="epl_section epl_cart_time_price_epl_section">
                        <?php echo $event['event_time_and_prices']; ?>
                    </div>
                <?php endif; ?>
            </div>

            <div id="epl_totals_wrapper_<?php echo $event_id; ?>" class="epl_section epl_ov_a">
                <?php echo $event['cart_totals']; ?>

            </div>
        </div>



    <?php endforeach; ?>






    <?php if ( !epl_is_free_event() && $mode != 'overview' && epl_get_setting( 'epl_registration_options', '_epl_display_discount_input_field', 10 ) == 10 && apply_filters( 'epl_show_discount_field', true ) === true ): ?>

        <div id="epl_discount_wrapper" class="epl_section epl_ov_a epl_ta_r">


            <div class="epl_discount_label"><?php

                echo epl_get_setting( 'epl_registration_options', '_epl_discount_input_label', epl__( 'Discount Code' ) ) . "&nbsp;";
                ?>
            </div>
            <div class="epl_discount_field">
                <?php

                echo $cart_data['discount_field'];
                ?>
            </div>

        </div>
    <?php endif; ?>
    <?php if ( ($mode != 'overview' && epl_get_regis_setting( 'epl_enable_donation' ) == 10) || ($mode == 'overview' && epl_get_element_m( 'donation_amount', 'money_totals', $cart_data['cart_totals'], 0 ) > 0) ): ?>
        <div id="epl_donation_wrapper" class="epl_section epl_ov_a epl_ta_r">

            <?php

            echo epl__( 'Donation' ) . ":&nbsp;" . $cart_data['donation_field'];
            ?>

        </div>
    <?php endif; ?>

    <?php if ( !epl_is_zero_total() ): ?>

        <div id="epl_cart_totals_wrapper" class="">

            <div>
                <?php

                echo $cart_data['cart_grand_totals'];
                ?>
            </div>
            <?php if ( $mode != 'overview' ): ?>
                <a href="#" id="calculate_total_due" class="epl_button_small epl_fr"><?php epl_e( 'Update Total' ); ?></a>
            <?php endif; ?>
        </div>
        <?php if ( isset( $cart_data['alt_total'] ) ): ?>
            <div id="epl_alt_totals_wrapper" class="epl_section epl_ov_a epl_ta_r" style="background-color:pink;font-size:20px;">

                <span class="epl_fl"><?php epl_e( 'Alternate Total' ); ?></span><span class=""> <?php echo $cart_data['alt_total']; ?></span>

            </div>

            <?php

        endif;
        if ( !epl_is_free_event() ):
            if ( $mode != 'overview' ):
                ?>
                <div id="epl_payment_choices_wrapper" class="epl_section">
                    <div  class="epl_section_header"><?php epl_e( 'Select a payment method' ); ?></div>
                    <?php

                    echo $cart_data['pay_options'];
                    ?>
                </div>
            <?php endif; ?>
        <?php endif; ?>
    <?php endif; ?>

    <?php epl_cart_bottom(); ?>

</div>

<script>

    jQuery(document).ready(function ($) {
        $('.expand_collaps').click(function () {
            var me = $(this);
            var par = me.parents('.epl_individual_event_wrapper');

            var _height = me.hasClass('expanded') ? par.find('.epl_event_selections').innerHeight() + 120 : 150;
            me.toggleClass('expanded');

            par.not(':animated').animate({height: _height + 'px'}, 400);
            return false;
        })

    });

</script>
