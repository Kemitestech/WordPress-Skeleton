<?php

global $event_details, $regis_details;
$regis = current( $registration );
setup_event_details( $regis->event_id );
$form_data = EPL_report_model::get_form_data_array( $regis->input_slug, $regis->value );

$payment_due = epl_get_element( 'epl_invoice_due', $invoice_settings, false );
$invoice_date = epl_get_element( 'inovice_date', $_POST, $regis->regis_date );
?>

<div id="epl_invoice_wrapper">
    <div class="invoice_section_full" >
        <table class="invoice_header">

            <tr>
                <td class="logo"> <img src="<?php echo $invoice_settings['epl_invoice_logo']; ?>" alt="<?php echo get_bloginfo(); ?>" /></td>
                <td class="address" style="text-align:right"><?php echo $invoice_settings['epl_invoice_company_info']; ?></td>
            </tr>


        </table>


    </div>

    <div class="invoice_section_full" style="border-top: 1px solid #f2f2f2;">
        <table class="payment_dates">

            <tbody>
                <tr>
                    <td rowspan="7" class="user_info">

                        <?php echo $form_data['first_name'] . ' ' . $form_data['last_name']; ?><br />
                        <?php echo $form_data['email']; ?><br />
                        <?php echo $form_data['address']; ?><br />
                        <?php echo "{$form_data['city']} {$form_data['state']} {$form_data['zip']}"; ?>

                    </td>
                </tr>
                <tr>
                    <td style="text-align:right;">
                        <span><?php epl_e( 'Invoice Date' ); ?></span>
                    </td>
                    <td class="invoice_info">

                        <?php echo epl_formatted_date( $invoice_date ); ?>
                    </td>
                </tr>
                <?php if ( epl_get_element( '_epl_attach_invoice', $event_details, 10 ) == 10 ): ?>
                    <tr>
                        <td style="text-align:right;">
                            <span><?php epl_e( 'Invoice #' ); ?></span>
                        </td>
                        <td class="invoice_info">

                            <?php

                            $epl_invoice_display_id = epl_get_setting( 'epl_api_option_fields', 'epl_invoice_display_id', 3 );

                            if ( $epl_invoice_display_id == 1 )
                                echo $regis_details['ID'];
                            elseif ( $epl_invoice_display_id == 2 )
                                echo $regis_details['post_title'];
                            else
                                echo $regis_details['_epl_regis_incr_id'];
                            ?>

                        </td>
                    </tr>
                <?php endif; ?>
                <?php if ( $payment_due ): ?>
                    <tr>
                        <td style="text-align:right;">
                            <span><?php epl_e( 'Date Due' ); ?></span>
                        </td>
                        <td class="invoice_info">

                            <?php echo epl_formatted_date( strtotime( $invoice_date . " +{$payment_due} day" ) ); ?>
                        </td>
                    </tr>
                <?php endif; ?>
                <tr>
                    <td style="text-align:right;">
                        <span><?php epl_e( 'Total Paid' ); ?></span>
                    </td>
                    <td class="invoice_info">

                        <?php
                        $paid = $regis->payment_amount?$regis->payment_amount:0;
                        echo epl_get_formatted_curr( $paid, null, true );
                        ?>

                    </td>
                </tr>
                <?php if ( $regis->payment_amount > 0 ): ?>
                    <tr>
                        <td style="text-align:right;">
                            <span><?php epl_e( 'Date Paid' ); ?></span>
                        </td>
                        <td class="invoice_info">

                            <?php echo epl_formatted_date( $regis->payment_date ); ?>

                        </td>
                    </tr>
                <?php endif; ?>
                <tr>
                    <td style="text-align:right;">
                        <span><?php epl_e( 'Balance Due' ); ?></span>
                    </td>
                    <td class="invoice_info">

                        <?php echo epl_get_formatted_curr( $regis->grand_total - $regis->payment_amount, null, true ); ?>

                    </td>
                </tr>

            </tbody>
        </table>

    </div>
    <div class="invoice_section_full">
        <table class="regis_details">
            <tr>

                <th><?php epl_e( 'Date' ); ?></th>
                <th><?php epl_e( 'Time' ); ?></th>
                <th><?php epl_e( 'Type' ); ?></th>
                <th><?php epl_e( 'Quantity' ); ?></th>    
                <th><?php epl_e( 'Price' ); ?></th>
                <th><?php echo epl_get_regis_setting( 'epl_surcharge_label' ); ?></th>

                <th><?php epl_e( 'Event Total' ); ?></th>                
            </tr>

            <?php

            $tmp_event = '';
            foreach ( $regis_data as $row ):
                setup_event_details( $row->event_id );
                $event = $event_details['post_title'];
                $date = epl_formatted_date( $event_details['_epl_start_date'][$row->date_id] );
                $time = $event_details['_epl_start_time'][$row->time_id];
                $ticket = $event_details['_epl_price_name'][$row->price_id];
                $price = epl_get_formatted_curr( $event_details['_epl_price'][$row->price_id], null, true );
                $total_qty = $row->total_qty;
                $surcharge = epl_get_formatted_curr( $row->surcharge, null, true );
                $event_total = epl_get_formatted_curr( $row->grand_total, null, true );

                if ( $tmp_event != $row->event_id ):
                    $tmp_event = $row->event_id;
                    ?>
                    <tr>
                        <td colspan="7" style="font-size: 1.2em !important;font-weight: bold;"><?php echo $event; ?></td>
                    </tr>

                <?php endif; ?>


                <tr>

                    <td><?php echo $date; ?></td>
                    <td><?php echo $time; ?></td>
                    <td><?php echo $ticket; ?></td>
                    <td><?php echo $total_qty; ?></td>    
                    <td><?php echo $price; ?></td>                
                    <td><?php echo $surcharge; ?></td>                

                    <td><?php echo $event_total; ?></td>                
                </tr>

            <?php endforeach; ?>
        </table>
    </div>

    <div class="invoice_section_full">
        <table class="regis_totals" style="width:400px;margin: 0 auto;">

            <tr>
                <td style="text-align: left;"><?php epl_e( 'Subtotal' ); ?></td>
                <td style="text-align: left;"><?php echo epl_get_formatted_curr( $regis->subtotal, null, true ); ?></td>
            </tr>
            <tr>
                <td style="text-align: left;"><?php echo epl_get_regis_setting( 'epl_surcharge_label' ); ?></td>
                <td style="text-align: left;"><?php echo epl_get_formatted_curr( $regis->surcharge, null, true ); ?></td>
            </tr>
            <tr>
                <td style="text-align: left;"><?php epl_e( 'Discount' ); ?></td>
                <td style="text-align: left;"><?php echo epl_get_formatted_curr( $regis->discount_amount, null, true ); ?></td>
            </tr>
            <tr>
                <td style="text-align: left;"><?php epl_e( 'Total' ); ?></td>
                <td style="text-align: left;"><?php echo epl_get_formatted_curr( $regis->grand_total, null, true ); ?></td>
            </tr>


        </table>
    </div>


    <div class="invoice_section_full" style="font-size: 0.8em;">
        <?php echo epl_format_string( $invoice_settings['epl_invoice_instruction'] ); ?>
    </div>

</div>
