<?php global $event_details; ?>

<?php //if ( !epl_is_free_event() ): ?>

    <div id="epl_totals_wrapper_<?php echo $event_id; ?>" class="epl_section epl_ov_a">
        <?php

        echo $cart_totals;
        ?>

    </div>

<?php //endif; ?>