
<?php

if ( !epl_is_waitlist_flow() ):

    global $cart_totals, $epl_fields;
    $money_totals = epl_get_element( 'money_totals', $cart_totals, array() );
    ?>
    <div class="epl_section">

        <div class="epl_section_header"> <?php epl_e( 'Payment Details' ); ?> </div>

        <table class="epl_payment_details_table">
            <tbody>

                <?php

                if ( $payment_instructions != '' && !epl_is_zero_total() && !epl_is_free_event() )
                    echo $payment_instructions;


                $regis_status_id = get_the_regis_status( null, true );
                ?>

                <tr>
                    <td><?php epl_e( 'Registration Status' ); ?></td>

                    <td class="epl_status_<?php echo $regis_status_id; ?>"><?php echo get_the_regis_status(); ?></td>
                </tr>

                <?php if ( !empty( $money_totals['subtotal'] ) ): ?>
                    <tr>
                        <td><?php epl_e( 'Subtotal' ); ?></td>
                        <td><?php echo epl_get_formatted_curr( $money_totals['subtotal'], null, true ) ?></td>
                    </tr>
                <?php endif; ?>
                <?php if ( epl_get_element( 'surcharge', $money_totals, 0 ) > 0 ): ?>
                    <tr>
                        <td><?php epl_e( 'Surcharge' ); ?></td>
                        <td><?php echo epl_get_formatted_curr( $money_totals['surcharge'], null, true ) ?></td>
                    </tr>
                <?php endif; ?>
                <?php if ( epl_get_element( 'discount_amount', $money_totals, 0 ) > 0 ): ?>
                    <tr>
                        <td><?php epl_e( 'Discount' ); ?></td>
                        <td>
                            <?php echo epl_get_formatted_curr( $money_totals['discount_amount'], null, true ) ?>
                            <?php echo epl_wrap( $money_totals['discount_description'], ' (', ')' ) ?>
                        </td>
                    </tr>
                <?php endif; ?>
                <?php if ( epl_get_element( 'donation_amount', $money_totals, 0 ) > 0 ): ?>
                    <tr>
                        <td><?php epl_e( 'Donation' ); ?></td>
                        <td>
                            <?php echo epl_get_formatted_curr( $money_totals['donation_amount'], null, true ) ?>

                        </td>
                    </tr> 
                <?php endif; ?>
                <tr>
                    <td><?php epl_e( 'Total' ); ?></td>
                    <td><?php echo get_the_regis_total_amount(); ?></td>
                </tr>
                <?php $balance = get_the_regis_balance_due(); ?>
                <tr class="balance" style="background-color: #fcf8e3;">
                    <td><?php epl_e( 'Balance Due' ); ?></td>
                    <td>
                        <?php

                        echo epl_get_formatted_curr( $balance, null, true );
                        $show_pay_now = ( epl_get_setting( 'epl_registration_options', '_epl_enable_pay_now_link', 10 ) == 10 );
                        $show_pay_now = apply_filters( 'epl_show_pay_now_link', $show_pay_now );
                        if ( $balance > 0 && $show_pay_now ) {
                            echo '&nbsp;' . epl_anchor( epl_get_waitlist_approved_url( true, 'p' ), epl__( 'Pay Now' ), null );
                        }
                        ?>


                    </td>
                </tr>

                <?php

                $payment_data = epl_get_regis_payments();


                if ( !empty( $payment_data ) && !epl_is_zero_total() ):

                    if ( count( $payment_data ) > 0 ):
                        ?>
                        <tr>
                            <td colspan="2" style="text-align: center;font-weight: bold;"><?php epl_e( 'Completed Payments' ); ?></td>
                        </tr>

                        <?php

                    endif;

                    $total_paid = 0;

                    foreach ( $payment_data as $time => $p ):
                        $payment_display = epl_format_string( get_post_meta( $p['_epl_payment_method'], '_epl_pay_display', true ) );
                        $total_paid += $p['_epl_payment_amount'];
                        ?>
                        <tr>
                            <td>
                                <?php echo epl_formatted_date( $p['_epl_payment_date'] ); ?>                  

                            </td>
                            <td>
                                <?php echo epl_get_formatted_curr( $p['_epl_payment_amount'], null, true ); ?>
                                <?php echo epl_wrap( $payment_display, '(', ')' ); ?>
                            </td>
                        </tr>
                        <?php

                    endforeach;
                endif;
                ?>                    

            </tbody>
        </table>

    </div>

<?php endif; ?>