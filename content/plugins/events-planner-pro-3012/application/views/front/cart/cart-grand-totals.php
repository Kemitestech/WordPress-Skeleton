<?php

global $event_details, $epl_fields;
/* this is the totals table in the cart */

$subtotal = epl_get_element( 'subtotal', $money_totals, 0 );
$total = epl_get_element( 'grand_total', $money_totals, 0 );

if ( isset( $cart_event_list ) )
    echo $cart_event_list;
?>
<table id="epl_cart_totals_table">
    <?php if ( epl_get_element( 'discount_message', $money_totals ) && $mode != 'overview' ): ?>
        <tr class="">

            <td colspan="2"><span class="epl_font_red epl_fr"> <?php echo $money_totals['discount_message']; ?></span></td>
        </tr>
    <?php endif; ?>
    <?php if ( $subtotal && $subtotal != $total ): ?>
        <tr class="epl_subtotal">

            <td class="epl_w500"><?php echo epl_e( 'Subtotal' ); ?></td>
            <td class="epl_total_price epl_w100 epl_ta_r"> <?php echo epl_get_formatted_curr( $money_totals['subtotal'], null, true ); ?></td>
        </tr>
    <?php endif; ?>
    <?php if ( ($surcharge = epl_get_element( 'surcharge', $money_totals )) > 0 ): ?>
        <tr class="epl_surcharge">

            <td class="epl_w500"><?php echo epl_get_element( '_epl_surcharge_label', $event_details, epl__( 'Surcharge' ) ); ?></td>
            <td class="epl_total_price epl_w100 epl_ta_r"> +<?php echo epl_get_formatted_curr( $surcharge, null, true ); ?></td>
        </tr>
    <?php endif; ?>
    <?php if ( epl_get_element( 'discount_amount', $money_totals ) ): ?>
        <tr class="epl_discount_amount">

            <td class="epl_w500">
                <?php echo epl_e( 'Discount' ); ?>
                <?php if ( epl_get_element( 'discount_description', $money_totals ) != '' ): ?>
                    <span class="discount_description"> (<?php echo epl_format_string( $money_totals['discount_description'] ); ?>)</span>
                <?php endif; ?>
            </td>
            <td class="epl_total_price epl_w100 epl_ta_r"> -<?php echo epl_get_formatted_curr( $money_totals['discount_amount'], null, true ); ?></td>
        </tr>
    <?php endif; ?>

    <?php if ( ($original_total = epl_get_element( 'original_total', $money_totals )) > 0 ): ?>
        <tr class="epl_original_total" style="background-color: #ffcccc ">

            <td class="epl_w500"><?php epl_e( 'Original Total' ); ?></td>
            <td class="epl_total_price epl_w100 epl_ta_r"> <?php echo epl_get_formatted_curr( $original_total, null, true ); ?></td>
        </tr>
    <?php endif; ?>
    <?php if ( ($donation_amount = epl_get_element( 'donation_amount', $money_totals, 0 )) > 0 ): ?>
        <tr class="epl_donation_amount" style="">
            <td class="epl_w500"><?php epl_e( 'Donation' ); ?></td>
            <td class="epl_total_price epl_w100 epl_ta_r"> <?php echo epl_get_formatted_curr( $donation_amount, null, true ); ?></td>
        </tr>
    <?php endif; ?>
    <tr class="epl_grand_total">

        <td class="epl_total_price"><?php echo epl_e( 'Registration Total' ); ?></td>
        <td class="epl_total_price epl_w100 epl_ta_r"> <?php echo epl_get_formatted_curr( $total, null, true ); ?></td>
    </tr>
    <tr class="epl_grand_total" style="background-color: #fcf8e3;">

        <td class="epl_total_price epl_w500"><?php echo epl_e( 'Balance Due' ); ?></td>
        <td class="epl_total_price epl_w100 epl_ta_r"> <?php echo epl_get_formatted_curr( epl_get_balance_due(), null, true ); ?></td>
    </tr>

    <?php

    $payment_data = epl_get_regis_payments();
    $paying_deposit_now = epl_get_element( 'pay_deposit', $money_totals, '' );
    $min_deposit = epl_get_element( 'min_deposit', $money_totals, '' );

    if ( $flow_mode == 'n' && ($mode != 'overview' && empty( $payment_data ) && (($min_deposit != 0 && $min_deposit !== '' && $min_deposit < $total) || ($mode != 'overview' && $paying_deposit_now)) )) :
        ?>
        <tr class="epl_grand_total">

            <td class="epl_w500">                
                <?php echo epl_e( 'Minimum deposit' ); ?>
                <?php if ( empty( $ck_out_button ) && $mode != 'overview' ): ?>
                    <br /><input type="checkbox" name="_epl_pay_deposit" value="1" <?php checked( 1, $paying_deposit_now ); ?>/> - <?php epl_e( 'please check if you would like to only pay the deposit now.' ); ?>
                <?php elseif ( $paying_deposit_now == 1 ): ?>
                    - <?php epl_e( 'You have selected to pay the deposit only for now and the balance at a later date.' ); ?>
                <?php endif; ?>
            </td>
            <td class="epl_w100 epl_ta_r"> <?php echo epl_get_formatted_curr( $min_deposit, null, true ); ?></td>
        </tr>
    <?php endif; ?>

    <?php

    if ( !empty( $payment_data ) ):

        if ( count( $payment_data ) > 0 ):
            ?>

            <tr>
                <td colspan="2" style="font-weight: bold;text-align: center;">
                    <?php epl_e( 'Completed Payments' ); ?>
                </td>
            </tr>


            <?php

        endif;
        $total_paid = 0;
        foreach ( $payment_data as $time => $p ):
            if ( $p['_epl_payment_amount'] == 0 )
                continue;
            $payment_display = get_post_meta( $p['_epl_payment_method'], '_epl_pay_display', true );
            $total_paid += $p['_epl_payment_amount'];
            ?>
            <tr>
                <td>
                    <?php echo epl_formatted_date( $p['_epl_payment_date'] ); ?>
                </td>
                <td class="epl_ta_r epl_w300">
                    <?php echo epl_get_formatted_curr( $p['_epl_payment_amount'], null, true ); ?><br />
                    <small><?php echo epl_wrap( $payment_display, '(', ')' ); ?></small>
                </td>
            </tr>
            <?php

        endforeach;
    endif;
    ?>

</table>
<?php

if ( !empty( $ck_out_button ) ):
    ?>
    <div class="epl_fr">
        <!--<a href="<?php echo $clear_url; ?>/?clear_cart=1" class="epl_button_small">Clear</a>-->
        <a href="<?php echo add_query_arg( array( 'epl_action' => 'show_cart' ), epl_get_shortcode_page_permalink() ); ?>" class="epl_button_small">Checkout</a>
    </div>
<?php endif; ?>