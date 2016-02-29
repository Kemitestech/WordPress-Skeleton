

            <h3><a href="#"><?php echo $event_title; ?> - <?php echo get_the_regis_status(); ?></a></h3>
            <div>

                <div class="epl_user_regis_wrapper">
                    <strong style="float:left;">Regis ID: <?php echo $regis_id; ?></strong>
                    <strong style="float:right">On <?php echo $regis_date; ?></strong>
                    <div class="clear"></div>

                    <?php

                    $r = get_the_regis_dates_times_prices( $regis_post_id );
                    echo $r
                    ?>

                    <table class="epl_payment_details_table">
                        <tbody>

                            <?php

                            if ( $payment_instructions != '' && !epl_is_zero_total() && !epl_is_free_event() )
                                echo $payment_instructions;
                            ?>


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
                    <?php

                    global $email_regis_form;

                    echo $email_regis_form;
                    ?>

                </div>



            </div>
