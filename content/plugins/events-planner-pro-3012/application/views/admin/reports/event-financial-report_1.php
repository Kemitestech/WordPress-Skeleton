event report
<table class="epl_financial_report epl_report_table">
    <tr>
        <th>Regis. Date</th>
        <th>ID</th>
        <th>Status</th>
        <th>Total</th>

    </tr>

    <?php

    global $regis_details, $post, $epl_fields;
    
    $event_id = intval($_POST['event_id']);

    $total_due = 0;
    $total_paid = 0;
    if ( $regis_list->have_posts() ):

        while ( $regis_list->have_posts() ) :

            $regis_list->the_post();
            setup_regis_details( get_the_ID() );
    
            $_event_totals = $regis_details['_epl_events'][$event_id]['money_totals'];
            
          

            $payment_method = (isset( $regis_details['_epl_payment_method'] ) && $regis_details['_epl_payment_method'] != '') ? $epl_fields['epl_regis_payment_fields']['_epl_payment_method']['options'][$regis_details['_epl_payment_method']] : '';
            
            $total_due += $_event_totals['grand_total'];
           // $total_paid += $_event_totals['payment_amount'];
            
            
            
            ?>

            <tr>
                <td><?php echo epl_formatted_date( $post->post_date ); ?></td>
                <td><a href="<?php echo admin_url("post.php?post={$post->ID}&action=edit"); ?>" target="_blank"><?php the_title(); ?></a></td>
                <td class="epl_status_<?php echo $regis_details['_epl_regis_status'] ?>"><?php echo get_the_regis_status() ?></td>
                <td class="total"><?php echo epl_get_formatted_curr( $_event_totals['grand_total'] ); ?></td>


            </tr>
            <?php

        endwhile;

    endif;
    ?>
            
            <tr>
                <td></td>
                <td></td>
                <td></td>
                <td class="total"><b><?php echo epl_get_formatted_curr( $total_due ); ?></b></td>

            </tr>
            
            </table>


        <script type="text/javascript">

            jQuery(document).ready(function($) {
                var oTable = $('.epl_financial_report').dataTable( { 
                    "bJQueryUI": true,
                    "sPaginationType": "full_numbers",
                    "iDisplayLength": 20,
                    "sDom": 'T<"clear">lfrtip',
                    "oTableTools": {
                        "sSwfPath": "<?php echo EPL_FULL_URL; ?>swf/copy_csv_xls_pdf.swf"
                    }
                                
                });
                /*     "bPaginate": true,
                    "sPaginationType": "full_numbers",
                    "aoColumnDefs": [
                        { "bSortable": false, "aTargets": [ 6 ] }
                    ],
                    "oColumnFilterWidgets": {
                        "aiExclude": [ 0, 1, 2, 3, 4, 5, 6, 7, 8 ],
                        "sSeparator": ',',
                        "bGroupTerms": true
                    },
                    "sDom": '<"top_filters"fW><"clear">rtlip',
                    "aaSorting": [[ 0, "asc" ]],
                    "bLengthChange": true,
                    "bFilter": true,
                    "bSort": true,
                    "bAutoWidth": false,
                    "bInfo": true,
                    /*"bAutoWidth": false,*/
                /* "oLanguage": {
                        "sInfo": "Showing _START_ to _END_ of _TOTAL_ events",
                        "sSearch": "Smart Sort:",
                        "sZeroRecords": "No upcoming events found. Expand your search."
                    }
                });*/
                //oTable.fnSetColumnVis( 9, false, false );  //Age
                //oTable.fnSetColumnVis( 10, false, false );  //Type
                //oTable.fnSetColumnVis( 11, false, false );  //Participation
                        				
                $('.column-filter-widgets').prepend('<div class="column-filter-widgets-label">Search By:</div>');
                        				
            });

        </script>
