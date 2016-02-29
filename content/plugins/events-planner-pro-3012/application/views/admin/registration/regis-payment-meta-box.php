<style>
    tfoot.money_totals td {
        font-size: 1.2em;
        font-weight: bold;
    }
</style>
<?php

global $regis_details, $epl_fields;

$payment_data = epl_get_regis_payments();

$total_paid = 0;

if ( isset( $save_button ) ):
    ?>
    <form class="epl_regis_payment_meta_box_form" action="#" method="post">
        <input type="hidden" name ="post_ID" value="<?php echo ( int ) $_POST['post_ID']; ?>" />
        <?php

    endif;

    wp_nonce_field( 'epl_form_nonce', '_epl_nonce' );
    ?>
    <table class="epl_table epl_table_bordered epl_table_hover epl_table_condensed">
        <thead>
            <tr style="">
                <th><?php epl_e( 'Date' ); ?></th>
                <th><?php epl_e( 'Method' ); ?></th>
                <th><?php epl_e( 'Amount' ); ?></th>
                <th><?php epl_e( 'Transaction ID' ); ?></th>
                <th><?php epl_e( 'Note' ); ?></th>
                <th></th>
            </tr>
        </thead>
        <tbody>
            <?php if ( epl_is_empty_array( $payment_data ) ): ?>

                <tr>
                    <td colspan="6"><?php epl_e( 'No Payments Records Found' ); ?></td>
                </tr>

            <?php else: ?>

                <?php

                foreach ( $payment_data as $k => $p ):
                    if ( $p['_epl_payment_amount'] == '' )
                        continue;

                    $total_paid += epl_get_formatted_curr( $p['_epl_payment_amount'], 4 );
                    ?>
                    <tr>
                        <td>

                            <input type="text" size="10" class="datepicker" name="_epl_payment_data[<?php echo $k; ?>][_epl_payment_date]" value="<?php echo epl_formatted_date( $p['_epl_payment_date'] ); ?>" />
                        </td>
                        <td>
                            <?php

                            $field = $epl_fields['epl_regis_payment_fields']['_epl_payment_method'];
                            $field['input_name'] = "_epl_payment_data[{$k}][_epl_payment_method]";

                            $field['value'] = epl_get_element( '_epl_payment_method', $p );

                            $field = $this->epl_util->create_element( $field );
                            ?>
                            <?php echo $field['field']; ?>
                        </td>
                        <td>

                            <input type="text" name="_epl_payment_data[<?php echo $k; ?>][_epl_payment_amount]" value="<?php echo epl_get_formatted_curr( $p['_epl_payment_amount'] ); ?>" />
                        </td>
                        <td>

                            <input type="text" name="_epl_payment_data[<?php echo $k; ?>][_epl_transaction_id]" value="<?php echo $p['_epl_transaction_id']; ?>" />
                        </td>
                        <td>

                            <textarea style="font-size: 10px;" cols="25" rows="2" name="_epl_payment_data[<?php echo $k; ?>][_epl_payment_note]"><?php echo $p['_epl_payment_note']; ?></textarea>
                        </td>
                        <td style="vertical-align: middle;">
                            <div class="epl_action epl_delete force_delete"></div>
                        </td>
                    </tr>

                    <?php

                endforeach;
            endif;
            $grand_total = get_the_regis_total_amount( false );

            $balance = $grand_total - $total_paid;
            ?>
        </tbody>
        <tfoot class="money_totals">
            <tr>
                <td colspan="2" style="text-align: right;"><?php epl_e( 'Total Payments' ); ?></td>
                <td><?php echo epl_get_formatted_curr( $total_paid, null, true ); ?></td>
                <td colspan="3"></td>
            </tr>
            <tr>
                <td colspan="2" style="text-align: right;"><?php epl_e( 'Grand Total' ); ?></td>
                <td><?php echo epl_get_formatted_curr( $grand_total, null, true ); ?></td>
                <td colspan="3"></td>
            </tr>
            <tr>
                <td colspan="2" style="text-align: right;"><?php epl_e( 'Balance' ); ?></td>
                <td><?php echo epl_get_formatted_curr( $balance, null, true ); ?></td>
                <td colspan="3"></td>
            </tr>
        </tfoot>
    </table>


    <?php

    if ( $edit_mode ):
        ?>


    <?php endif; ?>
    <div class="epl_border epl_rounded_corners_3" style="padding:10px 5px;overflow: hidden;background-color: #f5f5f5;">
        <div class="expand_trigger section_header"><?php epl_e( 'Add Payment' ); ?></div>
        <div class="toggle_container">
            <div class="block">

                <table class="epl_form_data_table epl_regis_payment_meta_box" cellspacing="0">

                    <?php

                    //Print the fields
                    echo current( $epl_regis_payment_fields );
                    ?>
                    <?php if ( isset( $save_button ) ): ?>

                        <tr>
                            <td colspan="2">
                                <input type="submit" name="Submit" value ="Save" class="epl_save_payment_ajax" />
                            </td>
                        </tr>

                    <?php endif; ?>

                </table>
                <?php if ( !$save_button ): ?>
                    <a href="#" class="open_cc_form button-primary" style="float: right;" data-post_id="<?php echo $post_ID; ?>"><?php epl_e( 'Open Credit Card Form' ); ?></a>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <?php if ( isset( $save_button ) ): ?>
    </form>

<?php endif; ?>

<script>

    jQuery(document).ready(function ($) {
<?php if ( $save_button ): ?>
            $('.epl_regis_payment_meta_box_form .expand_trigger').trigger('click');
<?php endif; ?>
        create_datepicker('.datepicker');
        $('body').on('change', 'input[name=_epl_grand_total],input[name=_epl_payment_amount]', function () {
            update_balance_due();
        });

        $('a.open_cc_form').click(function () {

            var me = $(this);
            //var gateway_id = $('input[name="_epl_payment_method"]:checked').val();
            var gateway_id = $('select[name="_epl_payment_method"]').val();

            if (gateway_id === undefined) {
                alert('Please select a Payment Method');
                return false;
            }

            var data = {
                "epl_action": "get_cc_form",
                "epl_controller": "epl_registration",
                "post_ID": me.data('post_id'),
                "gateway_id": gateway_id
            }

            data = $.param(data);

            events_planner_do_ajax(data, function (r) {
                epl_modal.open({
                    content: r.html,
                    width: "700px",
                    height: "auto"
                });

            });


            return false;
        });
        $('body').on('submit', '#admin_cc_form', function () {


            var me = $(this);

            var data = {
                "epl_action": "process_cc",
                "epl_controller": "epl_registration"

            }

            data = $.param(data);
            data = data + '&' + me.serialize();

            events_planner_do_ajax(data, function (r) {

                if (r.cc_processed != undefined)
                    window.location.reload();
                else
                    $('#cc_response_message').html(r.html)


            });


            return false;
        });
    });

    function update_balance_due() {

        var total_due = jQuery('input[name=_epl_grand_total]').val();
        var total_paid = jQuery('input[name=_epl_payment_amount]').val();

        var balance_due = parseFloat(total_due - total_paid);

        jQuery('input[name=_epl_balance_due]').val(balance_due.toFixed(2));

        return;

    }
</script>