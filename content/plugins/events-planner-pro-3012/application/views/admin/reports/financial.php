<?php $table_id = 'table_' . time(); ?>

<table id="<?php echo $table_id; ?>" class="epl_financial_report epl_report_table">
    <thead>
        <tr>
            <th>Regis. Date</th>
            <th>ID</th>

            <th>Total Due</th>
            <th>Total Paid</th>
            <th>Total Qty</th>
            <th>Payment Method</th>
            <th>Transaction ID</th>

            <th>Status</th>
        </tr>
    </thead>
    <?php

    global $regis_details, $post, $epl_fields, $event_details;


    $total_due = 0;
    $total_paid = 0;
    if ( $regis_list->have_posts() ):

        while ( $regis_list->have_posts() ) :
            $total_att = 0;
            $zebra = ($zebra == 'odd') ? 'even' : 'odd';

            $regis_list->the_post();
            //setup_event_details();
            setup_regis_details( get_the_ID() );

            $payment_method = (isset( $regis_details['_epl_payment_method'] ) && $regis_details['_epl_payment_method'] != '') ? $epl_fields['epl_regis_payment_fields']['_epl_payment_method']['options'][$regis_details['_epl_payment_method']] : '';

            $total_due += $regis_details['_epl_grand_total'];
            $total_paid += $regis_details['_epl_payment_amount'];


            foreach ( ( array ) $regis_details['_epl_events'] as $event_id => $totals ) {
                setup_event_details( $event_id );
                $data['event_name'] = $event_details['post_title'];
                $data['quantity'] = $totals['_att_quantity']['total'][$event_id];
                // echo "<tr><td>{$event_details['post_title']}</td><td class='qty'>{$data['quantity']}</td></tr>";
                $total_att += $totals['_att_quantity']['total'][$event_id];
            }
            ?>

            <tr class="fin <?php echo $zebra; ?>">
                <td><?php echo epl_formatted_date( $post->post_date ); ?></td>
                <td><a href="<?php echo admin_url( "post.php?post={$post->ID}&action=edit" ); ?>" target="_blank"><?php the_title(); ?></a></td>

                <td><?php echo epl_get_formatted_curr( $regis_details['_epl_grand_total'] ); ?></td>
                <td><?php echo epl_get_formatted_curr( $regis_details['_epl_payment_amount'] ); ?></td>
                <td><?php echo $total_att; ?></td>
                <td><?php echo $payment_method . ' ' . epl_get_element( '_epl_cc_type', $regis_details ); ?> </td>
                <td><?php echo $regis_details['_epl_transaction_id']; ?></td>

                <td class="epl_status_<?php echo $regis_details['_epl_regis_status'] ?>"><?php echo get_the_regis_status() ?></td>
            </tr>

            <?php

        endwhile;

    endif;
    ?>
    <tfoot>
        <tr>
            <th></th>
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
            "bJQueryUI": true,
            "sPaginationType": "full_numbers",
            "iDisplayLength": 20,
            "sDom": 'T<"clear">lfrtip',
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