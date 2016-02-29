<style>
    .dataTables_wrapper {
        min-height: 600px;
    }
</style>
    
<?php

if ( $user_bookings ):
    $erptm = EPL_report_model::get_instance();
    $attach_pdf = (epl_get_setting( 'epl_api_option_fields', 'epl_invoice_attach_to_conf', 0 ) == 10);

    ?>

    <table id="epl_user_bookings" class="epl_standard_table">

        <thead>
            <tr>
                <th><?php epl_e( 'Regis. ID' ); ?></th>
                <th><?php epl_e( 'Regis. Date' ); ?></th>
                <th><?php epl_e( 'Day' ); ?></th>
                <th><?php epl_e( 'Date' ); ?></th>
                <th><?php epl_e( 'Time' ); ?></th>
                <th><?php epl_e( 'Event' ); ?></th>
                <th><?php epl_e( 'Quantity' ); ?></th>

                <th><?php epl_e( 'Status' ); ?></th>
                <th></th>


            </tr>

        </thead>    
        <tbody>
            <?php

            global $event_details;
            $base_url = epl_get_url();

            foreach ( $user_bookings as $regis ) {

                $event_id = $regis->event_id;

                setup_event_details( $event_id );

                //date capacity
                $date_capacity = epl_get_element_m( $regis->date_id, '_epl_date_capacity', $event_details, '&infin;' );

                //if there is time capacity, grab that or default to date capacity
                $capacity = epl_get_element_m( $regis->time_id, '_epl_time_capacity', $event_details, $date_capacity );

                //get counts for this event, cached
                $counts = $erptm->get_attendee_counts( $event_id );

                //count date specific key
                $counts_day_key = $event_id . "_time_{$regis->date_id}";
                //counts time specific key
                $counts_time_key = $event_id . "_time_{$regis->date_id}_{$regis->time_id}";

                //if count for that time exists, get num regis, defaults to 0  
                $num_regis = epl_get_element( '_total_att_' . $counts_time_key, $counts, 0 );

                $url_params['epl_token'] = epl_get_token( $regis->regis_id . $regis->regis_date );

                $regis_summary_url = add_query_arg( $url_params, get_permalink( $regis->regis_id ) );

                $pdf_url = array(
                    'epl_action' => 'invoice',
                    'regis_id' => $regis->regis_id,
                    'epl_token' => epl_get_token( $regis->regis_id . $regis->regis_date )
                );

                $pdf_url = add_query_arg( $pdf_url, home_url() );


                $arr = array(
                    $regis->regis_key,
                    epl_formatted_date($regis->regis_date ),
                    epl_formatted_date( $event_details['_epl_start_date'][$regis->date_id], 'D' ),
                    epl_formatted_date( $event_details['_epl_start_date'][$regis->date_id] ),
                    $event_details['_epl_start_time'][$regis->time_id],
                    $event_details['post_title'],
                    $regis->ticket_count,
                    //"{$num_regis}", //. " <a href='#' class='epl_view_attendees_button' data-event_id='{$event_id}' data-date_id='{$regis->date_id}' data-time_id='{$regis->time_id}'>View</a>",
                    get_the_regis_status( $regis->status ),
                    epl_anchor( $regis_summary_url, epl__( 'Details' ) ) . ' ' . (!$attach_pdf || (epl_get_element( '_epl_attach_invoice', $event_details, 10 ) == 0) ? '' : epl_anchor( $pdf_url, epl__( 'PDF' ) ))
                );

                echo '<tr><td>' . implode( '</td><td>', $arr ) . '</td></tr>';
            }
            ?>
        </tbody>
    </table>
<?php else: ?>

<div><?php epl_e('No Registrations'); ?></div>

<?php endif; ?>

<script>
        
    jQuery(document).ready(function($){
        var oTable = $('#epl_user_bookings').dataTable({
            //'sDom': 'tlip',
            'aaSorting': [[ 1, 'desc' ]],
            'sPaginationType': 'full_numbers',
            'oLanguage': {

                //'sSearch': 'Search Keyword:',
                //'sZeroRecords': 'No bookings found. Please expand your search.'
            }
        });
        $('body').on('click', '.epl_view_attendees_button', function(){

            var me = $(this);
            var _me = this;
            
            if ( oTable.fnIsOpen( _me.parentNode.parentNode ) ) {
                oTable.fnClose( _me.parentNode.parentNode );
                me.parent().parent().removeClass('attendees-open');
                me.text('View');
                return false;
                
            }
            
            var data = {
                'epl_action':'get_session_attendees',
                'epl_controller':'epl_front',
                'event_id':me.data('event_id'),
                'date_id':me.data('date_id'),
                'time_id':me.data('time_id')
            }
           
            data  = $.param(data);
            events_planner_do_ajax( data, function(r){
            
                me.parent().parent().addClass('attendees-open');
                me.text('Hide');
                var row = oTable.fnOpen(
                _me.parentNode.parentNode,
                r.html,
                "info_row"
            );

            });
          
            return false;
        });
        
    });
        
</script>