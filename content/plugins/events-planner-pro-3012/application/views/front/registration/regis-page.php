<div class="epl_cart_wrapper">
    <?php

    epl_regis_form_top();

    echo $forms;
    ?>

    <?php echo isset( $cc_form ) ? $cc_form : ''; ?>
    <?php echo isset( $redirect_form_data ) ? $redirect_form_data : ''; ?>
    <?php

    epl_regis_form_bottom();
    ?>
</div>
