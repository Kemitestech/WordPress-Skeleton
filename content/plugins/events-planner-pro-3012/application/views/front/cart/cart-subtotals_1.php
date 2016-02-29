<div id="epl_totals_wrapper" class="epl_section epl_ov_a">

    <?php

    echo $cart_totals;
    ?>
    <?php if ( $mode != 'overview' ): ?>
        <a href="#" id="calculate_total_due" class="epl_button_small epl_fr"><?php epl_e( 'Update Total' ); ?></a>
    <?php endif; ?>
</div>