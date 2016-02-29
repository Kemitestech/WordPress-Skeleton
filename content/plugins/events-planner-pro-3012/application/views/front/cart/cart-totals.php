<?php

global $event_details;
/* this is the totals table in the cart */

$subtotal = epl_get_element( 'subtotal', $money_totals, 0 );
$total = epl_get_element( 'grand_total', $money_totals, 0 );
?>

<table id="epl_totals_table_<?php echo $event_details['ID']; ?>" class="epl_totals_table">
    <?php if ( epl_get_element( 'discount_message', $money_totals ) && $mode != 'overview' ): ?>
        <tr class="">

            <td colspan="2"><span class="epl_font_red epl_fr"> <?php echo $money_totals['discount_message']; ?></span></td>
        </tr>
    <?php endif; ?>
    <?php if ( $subtotal && $subtotal != $total ): ?>
        <tr class="epl_subtotal">

            <td class="epl_w200"><?php echo epl_e( 'Subtotal' ); ?></td>
            <td class="epl_total_price epl_w100 epl_ta_r"> <?php echo epl_get_formatted_curr( $money_totals['subtotal'], null, true ); ?></td>
        </tr>
    <?php endif; ?>

    <?php if ( epl_get_element( 'discount_amount', $money_totals ) ): ?>
        <tr class="epl_discount_amount">

            <td class="epl_w200">
                <?php echo epl_e( 'Discount' ); ?>
                <?php if ( epl_get_element( 'discount_description', $money_totals ) != '' ): ?>
                    <span class="discount_description"> (<?php echo epl_format_string( $money_totals['discount_description'] ); ?>)</span>
                <?php endif; ?>
            </td>
            <td class="epl_total_price epl_w100 epl_ta_r"> -<?php echo epl_get_formatted_curr( $money_totals['discount_amount'], null, true ); ?></td>
        </tr>
    <?php endif; ?>
    <?php if ( ($surcharge = epl_get_element( 'surcharge', $money_totals )) > 0 ): ?>
        <tr class="epl_surcharge">

            <td class="epl_w200"><?php echo epl_get_element( '_epl_surcharge_label', $event_details, epl__( 'Surcharge' ) ); ?></td>
            <td class="epl_total_price epl_w100 epl_ta_r"> +<?php echo epl_get_formatted_curr( $surcharge, null, true ); ?></td>
        </tr>
    <?php endif; ?>
    <?php if ( ($original_total = epl_get_element( 'original_total', $money_totals )) > 0 ): ?>
        <tr class="epl_original_total" style="background-color: #ffcccc ">

            <td class="epl_w200"><?php epl_e( 'Original Total' ); ?></td>
            <td class="epl_total_price epl_w100 epl_ta_r"> <?php echo epl_get_formatted_curr( $original_total, null, true ); ?></td>
        </tr>
    <?php endif; ?>
    <tr class="epl_grand_total">

        <td class="epl_w200"><?php echo epl_e( 'Event Total' ); ?></td>
        <td class="epl_total_price epl_w100 epl_ta_r"> <?php echo epl_get_formatted_curr( $total, null, true ); ?></td>
    </tr>


</table>