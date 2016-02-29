<div class="epl_cart_wrapper epl_thank_you_page">

    <?php echo do_action( 'epl_regis_complete_top_message' ); ?>

    <div class="epl_section">


        <div style="float:left;">
            <div class="event_name"><?php echo get_the_event_title(); ?></div>
            <div><strong><?php epl_e( 'Regis. ID' ); ?>: <?php echo get_the_regis_id(); ?></strong></div>
            <?php echo get_the_attendee_list_link(); ?>

        </div>
        <?php if ( !epl_is_multi_location() && epl_get_event_property( '_epl_event_location', true ) > 0 ): ?>
            <div class="" style="float: right;">
                <strong><?php epl_e( 'Location' ); ?></strong><br />
            <?php echo get_the_location_name(); ?><br />
            <?php echo get_the_location_address(); ?> <?php echo get_the_location_address2(); ?><br />
            <?php echo get_the_location_city(); ?> <?php echo get_the_location_state(); ?> <?php echo get_the_location_zip(); ?><br />
        </div>
        <?php endif; ?>
        </div>
        <div class="epl_section">
            <div class="epl_section_header"><?php epl_e( 'Details' ); ?></div>
        <?php echo get_the_regis_dates_times_prices(); ?>

        </div>

    <?php

            //If payment details are available, show them
            if ( isset( $payment_details ) && $payment_details != '' && !epl_is_free_event() && !epl_is_zero_total() )
                echo $payment_details;
    ?>


    <?php

            //show the registration form
            echo $regis_form;
    ?>

    <?php do_action( 'epl_regis_complete_bottom_message' ); ?>

</div>