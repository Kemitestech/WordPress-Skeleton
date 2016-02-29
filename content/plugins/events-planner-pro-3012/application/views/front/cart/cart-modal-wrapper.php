<?php

do_action( 'epl_cart_modal_wrapper_top' );
global $event_details;
?>
<div class="epl_cart_wrapper">
    <form id="events_planner_shopping_cart">
        <div class="epl_individual_event_wrapper">
            <?php

            $max_regis = epl_get_element( '_epl_registration_max', $event_details, null );
            ?>

            <input type="hidden" class="epl_registration_max" value="<?php echo $max_regis; ?>" />            
            <?php if ( $max_regis > 0 ): ?>
                <div class="epl_info">
                    <?php epl_e( sprintf( 'Please note that you can register a maximum of %d people for this event.', $max_regis ) ); ?>
                </div>
            <?php endif; ?>
            <?php echo $modal_cart_content; ?>
        </div>
    </form>

</div>

<script>

    check_for_remaining();
</script>
<?php do_action( 'epl_cart_modal_wrapper_bottom' ); ?>