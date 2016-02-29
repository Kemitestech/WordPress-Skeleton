<?php the_registration_details(); ?>
<div class="epl_cart_wrapper epl_thank_you_page">

    <?php do_action( 'epl_regis_complete_top_message' ); ?>

    <?php if ( epl_is_waitlist_record() ): ?>


        <div class="epl_info"><?php epl_e( 'Your name has been added to the waiting list.  You will receive an email once a spot opens.' ); ?></div>


    <?php endif; ?>

    <div class="epl_section">

        <div class="epl_fl_r">
            <div><strong><?php epl_e( 'Registration ID' ); ?>: <?php echo get_the_regis_id(); ?></strong></div>
            <?php echo get_the_attendee_list_link(); ?>

        </div>


    </div>
    <hr />
    <?php

    //If payment details are available, show them
    if ( isset( $payment_details ) && $payment_details != '' && !epl_is_free_event() )
        echo $payment_details;

    if ( $regis_status_id != 10 && $regis_status_id != 15 ):
        ?>



        <div class="epl_section">
            <div class="epl_section_header"><?php epl_e( 'Registration Details' ); ?></div>
            <?php echo get_the_regis_dates_times_prices( $post_ID ); ?>

        </div>



        <?php

        //show the registration form
        echo $regis_form;

    endif;
    ?>

    <?php do_action( 'epl_regis_complete_bottom_message' ); ?>

</div>
<?php

if ( isset( $tracking_code ) || epl_get_element( 'cnv_tr', $_GET ) ) {
    echo epl_get_regis_setting( 'epl_tracking_code' );
}
?>
