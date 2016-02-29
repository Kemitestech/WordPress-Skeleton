<div class="epl_cart_wrapper">
    <?php

    global $epl_current_step;

    if ( 'regis_form' == $epl_current_step )
        do_action( 'epl_regis_form_top_message' ); ?>
    <?php

    echo $forms;
    ?>

    <?php echo isset( $cc_form ) ? $cc_form : ''; ?>
    <?php

    if ( 'regis_form' == $epl_current_step )
        do_action( 'epl_regis_form_bottom_message' );
    ?>
</div>