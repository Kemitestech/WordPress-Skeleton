
<div class="epl_section">

    <div class="epl_section_header"> <?php epl_e( 'Payment Details' ); ?> </div>

    <table class="epl_payment_details_table">
        <tbody>

            <?php if ( $payment_instructions != '' )
                    echo $payment_instructions; ?>

                <tr>
                    <td><?php epl_e( 'Total' ); ?></td>
                    <td><?php echo get_the_regis_total_amount(); ?></td>
                </tr>
                <tr>
                    <td><?php epl_e( 'Amount Paid' ); ?></td>
                    <td><?php echo get_the_regis_payment_amount(); ?></td>
                </tr>
                <tr class="balance">
                    <td><?php epl_e( 'Balance Due' ); ?></td>
                    <td><?php echo get_the_regis_balance_due(); ?></td>
                </tr>

        </tbody>
    </table>

</div>

