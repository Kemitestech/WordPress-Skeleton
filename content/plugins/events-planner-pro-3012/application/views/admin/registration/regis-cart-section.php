<div class="epl_cart_wrapper">


    <?php

    global $event_details;
    $event_list_dd_arr = array(
        'input_type' => 'select',
        'input_name' => '_epl_events[]',
        'id' => 'event_list_id',
        'label' => epl__( 'Event' ),
        'options' => epl_get_all_events(),
        'style' => 'font-size:14px;font-weight:bold;margin:10px 0;display:none;',
        'class' => 'chosen',
        'show_value_only' => 1
    );

    if ( is_array( $cart_data['cart_items'] ) ):
        //foreach event in the cart
        foreach ( $cart_data['cart_items'] as $event_id => $event ):
            setup_event_details( $event_id );
            $event_list_dd_arr['value'] = $event_id;
            $event_list_dd = $this->epl_util->create_element( $event_list_dd_arr );
            ?>
            <div class="admin_cart_section" style="border-color: #ccc;box-shadow: 0 8px 6px -6px black; ">
                <?php if ( epl_sc_is_enabled() ): ?>
                    <div style="float:right;">
                        <a href="#" class="delete_cart_item" data-event_id="<?php echo $event_id; ?>">Delete</a>

                    </div>
                <?php endif; ?>
                <?php echo $event_list_dd['field']; ?>
                <h1><?php echo $event_details['post_title']; ?></h1>


                <div class="epl_event_section">

                    <?php if ( $cart_data['show_date_selector_cal'] == 100 && $mode != 'overview' ): ?>
                        <div class="epl_section">
                            <div  class="epl_section_header"><?php epl_e( 'Please click to select a date' ); ?></div>

                            <div id="epl_date_selector" style="margin-top:10px;"></div>
                        </div>
                    <?php endif; ?>
                    <div class="epl_section epl_cart_dates_body" style="">

                        <?php //echo $ev['event_dates']['field'];     ?>
                        <?php echo $event['event_dates']; ?>
                    </div>

                </div>
                <div class="epl_event_section">


                    <div class="section">
                        <?php echo $event['event_time_and_prices']; ?>
                    </div>


                </div>


            </div>



            <?php

        endforeach;
    endif;
    ?>
</div>



<script>
    
    jQuery(document).ready(function($){
        $('#event_list_dd').change(function(){
            var me = $(this);
            alert(me.val());
           
        });
        
        
});
    
</script>