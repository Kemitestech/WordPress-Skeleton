<?php

/* this is the totals table in the cart */
?>
<table class="epl_totals_table">
    <?php if ( epl_get_element( 'discount_message', $money_totals ) && $mode != 'overview' ): ?>
        <tr class="">

            <td colspan="2"><span class="epl_font_red epl_fr"> <?php echo $money_totals['discount_message']; ?></span></td>
        </tr>
    <?php endif; ?>
    <?php if ( epl_get_element( 'pre_discount_total', $money_totals ) ): ?>
        <tr class="epl_subtotal">

            <td class="epl_w200"><?php echo epl_e( 'Subtotal' ); ?></td>
            <td class="epl_total_price epl_w100 epl_ta_r"> <?php echo epl_get_formatted_curr( $money_totals['pre_discount_total'], null, true ); ?></td>
        </tr>
    <?php endif; ?>
    <?php if ( epl_get_element( 'discount_amount', $money_totals ) ): ?>
        <tr class="epl_discount_amount">

            <td class="epl_w200">
                <?php echo epl_e( 'Discount' ); ?>
                <?php if ( epl_get_element( 'discount_description', $money_totals, '' ) != '' ): ?>
                    <span class="discount_description"> (<?php echo epl_format_string( $money_totals['discount_description'] ); ?>)</span>
                <?php endif; ?>
            </td>
            <td class="epl_total_price epl_w100 epl_ta_r"> <?php echo epl_get_formatted_curr( $money_totals['discount_amount'], null, true ); ?></td>
        </tr>
    <?php endif; ?>
    <?php if ( epl_get_element( 'donation_amount', $money_totals ) ): ?>
        <tr class="epl_discount_amount">

            <td class="epl_w200">
                <?php echo epl_e( 'Donation' ); ?>

            </td>
            <td class="epl_total_price epl_w100 epl_ta_r"> <?php echo epl_get_formatted_curr( $money_totals['donation_amount'], null, true ); ?></td>
        </tr>
    <?php endif; ?>
    <tr class="epl_grand_total">

        <td class="epl_w200"><?php echo epl_e( 'Total' ); ?></td>
        <td class="epl_total_price epl_w100 epl_ta_r"> <?php echo epl_get_formatted_curr( epl_get_element( 'grand_total', $money_totals ), null, true ); ?></td>
    </tr>


</table>