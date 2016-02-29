
<?php if ( isset( $message1 ) ): ?>
    <div class="epl_error">
        <div class="">
            <?php echo $message; ?>
        </div>
    </div>
<?php else: ?>


    <table class="epl_form_data_table" cellpadding="0" cellspacing="0">

        <?php

        foreach ( $fields as $k => $f ):
            ?>

            <tr>
                <td><?php echo $f['label']; ?></td>
                <td><?php echo $f['field']; ?></td>

                <td><a id="add_event_to_cart" herf="#" class="button-primary"><?php epl_e( 'Add this event' ); ?></a></td>


            </tr>

        <?php endforeach; ?>

    </table>

    <div id="epl_regis_cart_data" class="epl_cart_section">
        <?php

        // event date/time/price selections
        if ( isset( $cart_data ) )
            echo $cart_data;
        ?>

    </div>

    <div id="admin_discount_section" class="epl_section">

        <table class="">
            <tbody>
                <tr class="">

                    <td class="epl_w200"><?php echo epl__( 'Discount Code' ); ?></td>
                    <td class="epl_w200"><?php echo $discount_field; ?></td>

                </tr>
                <tr class="">

                    <td class="epl_w200"><?php echo epl__( 'Donation Amount' ); ?></td>
                    <td class="epl_w200"><?php echo $donation_field; ?></td>

                </tr>


            </tbody>
        </table>
    </div>



    <?php if ( !$GLOBALS['epl_ajax'] ): ?>
        <div class="epl_section" style="overflow: hidden;">
            <a href="#" id="admin_calc_total" class="button-primary" style="float:right;"><?php epl_e( 'Calculate Total' ); ?></a>
        </div>
    <?php endif; ?>

    <div id="admin_totals_section" class="epl_section">

        <?php

        // cart totals section
        if ( isset( $cart_totals ) )
            echo $cart_totals;
        ?>

    </div>

    <?php if ( !$GLOBALS['epl_ajax'] ): ?>
        <div id="" class="epl_section" style="overflow: hidden;">

            <a href="#" id="admin_get_regis_form"  class="button-primary" style="float:right;"><?php epl_e( 'Get Registration Forms' ); ?></a>

        </div>
    <?php endif; ?>
    <div id="admin_regis_section">
        <?php

        // registration forms
        if ( isset( $attendee_info ) )
            echo $attendee_info;
        ?>
    </div>



<?php endif; ?>

<?php if ( !empty( $_GET['event_id'] ) ): ?>
    <script>
                                    
        jQuery(document).ready(function($){
                                        
            $('#add_event_to_cart').trigger('click');
                                        
        });
                                    
    </script>

<?php endif; ?>

<script>
        
    jQuery(document).ready(function($){
        
        function check_for_required(){
          
            var sum = 0;
            $('.epl_att_qty_dd').each(function() {
                sum += Number($(this).val());
        
            });

            if(sum == 0 ){
                alert('Please select at least one quantity');
                return false;
            }
            return true;
        }

        $("#post").validate({
            submitHandler: function(form) {
                var c = check_for_required();
                
                if (c)
                    form.submit();
            }
        })
    });
        
</script>
