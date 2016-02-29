<div id="epl_main_container" class="<?php echo $mode; ?>">
    <?php if ( epl_get_num_events_in_cart() == 0 ): ?>
        <div class="epl_info"><?php epl_e( 'Your event cart is empty.' ); ?></div>
    <?php else: ?>
        <?php if ( isset( $cart_data['message'] ) ): ?>

            <div class="epl_error"><?php echo $cart_data['message']; ?></div>

            <?php

            return;
        endif;
        ?>


        <div id="epl_ajax_content">

            <span style="display: block;text-align: right;font-size: 0.8em;"></span>

            <form action="<?php echo $form_action; ?>" method="post" id="events_planner_shopping_cart">
                <?php do_action( 'epl_regis_all_message_top' ); ?>

                <?php echo $content; ?>

                <?php do_action( 'epl_regis_all_message_bottom' ); ?>


                <p class="epl_button_wrapper">
                    <?php if ( isset( $prev_step_url ) ): ?>
                        <a href="<?php echo $prev_step_url; ?>" class="epl_button" ><?php epl_e( 'Back' ); ?></a>
                    <?php endif; ?>

                    <?php if ( isset( $next_step_label ) ): ?>
                        <input type="submit" name="next" class="epl_button" value="<?php echo (isset( $next_step_label )) ? $next_step_label : epl_e( 'Next' ); ?>" >
                    <?php endif; ?>

                </p>
                <?php if ( ($epl_rid = epl_get_element( 'epl_rid', $_REQUEST, '' )) != '' ): ?>
                    <input type="hidden" name="epl_rid" value="<?php echo $epl_rid; ?>" />
                    <input type="hidden" name="epl_r_m" value="<?php echo epl_get_element( 'epl_r_m', $_REQUEST, '' ); ?>" />
                <?php endif; ?>
            </form>


        </div>
    <?php endif; ?>
</div>