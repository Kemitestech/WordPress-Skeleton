<?php global $epl_fields; ?>

<div class="epl_regis_list_payment_info_wrapper_<?php echo $post_ID; ?> epl_regis_list_payment_info <?php echo $status_class; ?>">


    <table class="epl_regis_list_payment_info <?php echo $status_class; ?>" style="color:#333;">

        <tr>

            <td>
                <?php

                echo $snapshot_link;
                if($regis_status_id != 20)
                echo epl_get_send_email_button( $post_ID, null, true );
                ?>
            </td>
            <td style="text-align: right; width: 70%;color:#212121">
                <?php echo $regis_status; ?><?php echo $waitlist_status != '' ? ' - ' . $waitlist_status : ''; ?>
                 <?php if($regis_status_id <= 2)
                 echo '<br>' . $payment_method; 
                 ?>
            </td>
        </tr>
        <tr>
            <td style="width:50%"><span class="small1"><?php epl_e( 'Total' ); ?>: </span><span  class="amount"><?php echo $grand_total; ?></span></td>
            <td><span class="small1"><?php epl_e( 'Balance Due' ); ?>: </span><span  class="amount"><?php echo epl_get_formatted_curr( epl_get_balance_due(), null, true ); ?></span></td>

        </tr>
        <?php

        $payment_data = epl_get_regis_payments();

        foreach ( $payment_data as $t => $p ):
            if ( $p['_epl_payment_amount'] == 0 )
                continue;
            ?>
            <tr style="border-top:1px solid #d7d7d7;">
                <td><?php echo epl_formatted_date( $p['_epl_payment_date'] ); ?></td>
                <td><?php echo epl_get_formatted_curr( $p['_epl_payment_amount'], null, true ); ?>
                    <span style="float: right;"><?php echo epl_trunc( epl_get_element( $p['_epl_payment_method'], $epl_fields['epl_regis_payment_fields']['_epl_payment_method']['options'], '' ), 10 ); ?></span>
                </td>              
            </tr>
        <?php endforeach; ?>

        <tr>
            <td colspan="2">

                <span class="epl_font_small"><?php echo $waitlist_email_time != '' ? $waitlist_email_time : ''; ?></span>


            </td>
        </tr>

    </table>
</div>