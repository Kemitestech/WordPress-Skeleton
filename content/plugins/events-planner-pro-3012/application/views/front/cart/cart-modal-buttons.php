<p class="epl_button_wrapper" style="margin-top:10px;padding:10px 0;">
    <a href="#" class="epl_continue epl_button"><?php epl_e( 'Add more to your cart' ); ?></a>
    <a href="#" id="calculate_total_due" class="epl_button from_modal"><?php epl_e( 'Update Total' ); ?></a>
    <a href="<?php echo $checkout_url; ?>" class="epl_checkout epl_button"><?php epl_e( 'Checkout' ); ?></a>
    <input type="hidden" name="event_id" value="<?php echo $event_id; ?>" />
</p>
<script>
                
    jQuery(document).ready(function($){
        $('.epl_continue').click(function(){
            epl_modal.close();
            return false;
        })
    });
                
</script>