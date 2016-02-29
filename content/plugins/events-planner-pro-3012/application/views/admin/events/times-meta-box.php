<div id="epl_event_type_0">

    <div class="">
        <div class="epl_box_content">
            <table class="epl_form_data_table epl_w800" cellspacing="0">
                <thead>
                    <tr>
                        <th colspan="4">
                            <?php epl_e( 'Time and price options' ); ?>
                        </th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ( epl_is_addon_active( '_epl_atp' ) ): ?>
                        <tr>
                            <td><?php echo epl_get_the_label( '_epl_multi_time_select', $time_option_fields ); ?></td>
                            <td colspan="3">
                                <?php echo epl_get_the_field( '_epl_multi_time_select', $time_option_fields ); ?>
                                <?php echo epl_get_the_desc( '_epl_multi_time_select', $time_option_fields ); ?>
                            </td>

                        </tr>
                        <tr>
                            <td><?php echo epl_get_the_label( '_epl_multi_price_select', $price_option_fields ); ?></td>
                            <td colspan="3">
                                <?php echo epl_get_the_field( '_epl_multi_price_select', $price_option_fields ); ?>
                                <?php echo epl_get_the_desc( '_epl_multi_price_select', $price_option_fields ); ?>
                            </td>

                        </tr>
                        <tr>
                            <td><?php echo epl_get_the_label( '_epl_pack_regis', $time_option_fields ); ?></td>
                            <td>
                                <?php echo epl_get_the_field( '_epl_pack_regis', $time_option_fields ); ?>
                                <?php echo epl_get_the_desc( '_epl_pack_regis', $time_option_fields ); ?>
                            </td>
                            <td>
                                <?php echo epl_get_the_label( '_epl_pack_regis_consecutive', $time_option_fields ); ?>
                            </td>
                            <td>
                                <?php echo epl_get_the_field( '_epl_pack_regis_consecutive', $time_option_fields ); ?>
                            </td>

                        </tr>
                        <tr>
                            <td><?php echo epl_get_the_label( '_epl_rolling_regis', $time_option_fields ); ?></td>
                            <td colspan="3">
                                <?php echo epl_get_the_field( '_epl_rolling_regis', $time_option_fields ); ?>
                                <?php echo epl_get_the_desc( '_epl_rolling_regis', $time_option_fields ); ?>
                            </td>

                        </tr>
                        <tr>
                            <td><?php echo epl_get_the_label( '_epl_enable_deposit_payment', $time_option_fields ); ?></td>
                            <td colspan="3">
                                <?php echo epl_get_the_field( '_epl_enable_deposit_payment', $time_option_fields ); ?>
                                <?php echo epl_get_the_desc( '_epl_enable_deposit_payment', $time_option_fields ); ?>
                            </td>

                        </tr>
                        <tr class="dependence-_epl_enable_deposit_payment">
                            <td>└ <?php echo epl_get_the_label( '_epl_deposit_type', $time_option_fields ); ?></td>
                            <td colspan="3">
                                <?php echo epl_get_the_field( '_epl_deposit_type', $time_option_fields ); ?>
                                <?php echo epl_get_the_desc( '_epl_deposit_type', $time_option_fields ); ?>
                            </td>

                        </tr>
                        <tr class="dependence-_epl_enable_deposit_payment">
                            <td>└ <?php echo epl_get_the_label( '_epl_deposit_amount', $time_option_fields ); ?></td>
                            <td colspan="3">
                                <?php echo epl_get_the_field( '_epl_deposit_amount', $time_option_fields ); ?>
                                <?php echo epl_get_the_desc( '_epl_deposit_amount', $time_option_fields ); ?>
                            </td>

                        </tr>
                    <?php endif; ?>
                    <tr>
                        <td><?php echo epl_get_the_label( '_epl_free_event', $price_option_fields ); ?></td>
                        <td colspan="3">
                            <?php echo epl_get_the_field( '_epl_free_event', $price_option_fields ); ?>
                        </td>

                    </tr>
                    <tr>
                        <td><?php echo epl_get_the_label( '_epl_price_per', $price_option_fields ); ?></td>
                        <td colspan="3">
                            <?php echo epl_get_the_field( '_epl_price_per', $price_option_fields ); ?>
                            <?php echo epl_get_the_desc( '_epl_price_per', $price_option_fields ); ?>
                        </td>
                    </tr>
                </tbody>

            </table>

        </div>

    </div>


    <div id="epl_time_price_section" class="">

        <?php echo $time_price_section; ?>


    </div>

</div>

<script>
    
    jQuery(document).ready(function($){
        
        $('.dependence_check').change(function(){
            epl_dependece_check();
        });
        epl_dependece_check();
        function epl_dependece_check(){
            $('.dependence_check').each(function(){
                var me = $(this);
                //console.log($(this).prop('name'));
                //console.log('.dependence-' + me.prop('name') );
                if (me.val() == 0)
                    $('.dependence-' + me.prop('name') ).hide();
                else
                    $('.dependence-' + me.prop('name') ).slideDown('fast');
            })
        }
    });
    
</script>