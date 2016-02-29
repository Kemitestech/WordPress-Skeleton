<?php



$erm = EPL_registration_model::get_instance();
$table_id = 'table_' . time();
?>

<table id="<?php echo $table_id; ?>" class="epl_financial_report epl_report_table epl_standard_table">
    <thead>
        <tr>
            <th>Regis. Date</th>
            <th>ID</th>

            <th>Total Due</th>
            <th>Total Paid</th>

            <th>Payment Method</th>
            <th>Transaction ID</th>

            <th>Status</th>
        </tr>
    </thead>
    <tbody>
        <?php

        global $epl_fields, $event_details;

        foreach ( $transactions as $t ):

            $gateway_info = $erm->get_gateway_info( $t->payment_method_id );

            ?>
            <tr>
                <td><?php echo $t->regis_date; ?></td>

                <td><a href="<?php echo admin_url( "post.php?post={$t->regis_id}&action=edit" ); ?>" target="_blank"><?php echo $t->regis_key; ?></a></td>

                <td><?php echo $t->grand_total; ?></td>
                <td><?php echo $t->payment_amount; ?></td>

                <td><?php echo $epl_fields['epl_regis_payment_fields']['_epl_payment_method']['options'][ $t->payment_method_id]; ?></td>
                <td><?php echo $t->transaction_id; ?></td>
                <td class="epl_status_<?php echo $t->status ?>"><?php echo get_the_regis_status( $t->status ); ?></td>
            </tr>
        <?php endforeach; ?>
    </tbody>
    <tfoot>
        <tr>
            <th></th>
            <th></th>
            <th></th>
            <th></th>
            <th></th>
            <th></th>
            <th></th>
        </tr>
    </tfoot>

</table>


<script type="text/javascript">

    jQuery(document).ready(function($) {
        var oTable = $('#<?php echo $table_id; ?>').dataTable( { 
            //"bJQueryUI": true,
            "sPaginationType": "full_numbers",
            "iDisplayLength": 20,
            //"sDom": 'Tlfrtip',
            "sDom": '<"dtTop"frtilTp>rt<"dtBottom"><"clear">',
            "oTableTools": {

                "sSwfPath": "<?php echo EPL_FULL_URL; ?>swf/copy_csv_xls_pdf.swf",
                "aButtons": [
                    "copy",
                    {
                        "sExtends": "csv",
                        "sTitle": 'Export.csv'
                    },
                    //"xls", //hmm, this downloads as csv
                    //"pdf",
                    "print",
                ]
                
            },
            "fnFooterCallback": function ( nRow, aaData, iStart, iEnd, aiDisplay ) {
                /*
                 * Calculate the total market share for all browsers in this table (ie inc. outside
                 * the pagination)
                 */
                var total_due = 0;
                for ( var i=0 ; i<aaData.length ; i++ )
                {
                    total_due += aaData[i][2]*1;
                }
                var total_paid = 0;
                for (  i=0 ; i<aaData.length ; i++ )
                {
                    total_paid += aaData[i][3]*1;
                }
			
			
                /* Modify the footer row to match what we want */
                var nCells = nRow.getElementsByTagName('th');
                nCells[2].innerHTML = total_due.toFixed(2);
                nCells[3].innerHTML = total_paid.toFixed(2);
            }
                                
        });
                        				
    });

</script>