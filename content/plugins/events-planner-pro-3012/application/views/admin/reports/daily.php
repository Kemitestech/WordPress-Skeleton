<?php

$epl = EPL_Base::get_instance();
$ecm = EPL_common_model::get_instance();
?>

<div id="event_list_wrapper" class="class_list">

    <?php

    global $event_list;

    global $event_details, $event_fields, $current_att_count;


    $_from_date = epl_get_date_timestamp( epl_get_element( 'date_from', $_POST ) );
    $_to_date = epl_get_date_timestamp( epl_get_element( 'date_to', $_POST ) );

    $table_row = array( );


    $event_dates = array( );
    /* custom event list loop */
    if ( $event_list->have_posts() ):

        while ( $event_list->have_posts() ) :

            $event_list->the_post();
            setup_event_details();

            $ecm->get_current_att_count( get_the_ID() );

            $event_edit_link = epl_anchor( admin_url( "post.php?post=" . get_the_ID() . "&action=edit" ), get_the_title() );

            $date_specifc_time = epl_get_element( '_epl_date_specific_time', $event_details, array( ) );

            $register_button_url = array( );
            foreach ( $event_details['_epl_start_date'] as $date_id => $date ):

                $temp_table_row = '';

                if ( $date < EPL_DATE )
                    continue;

                $price = current( ( array ) $event_details['_epl_price'] );
                if ( $price <= 0 ) {
                    $print_price = "<span class='price_free'>Free</span>";
                }
                else {
                    $print_price = "<span class='price_amount'> " . epl_get_formatted_curr( $price, null, true ) . "</span>";
                }

                $start_time = current( ( array ) $event_details['_epl_start_time'] );

                $avail = $event_details['_epl_date_capacity'][$date_id];

                $num_regis = 0;


                if ( isset( $current_att_count['_total_att_' . get_the_ID() . "_date_{$date_id}"] ) )
                    $num_regis = epl_get_element( '_total_att_' . get_the_ID() . "_date_{$date_id}", $current_att_count, 0 );

                $avail-= $num_regis;

                if ( $avail <= 0 ) {
                    $open_spots = "<span class='spots_closed'>Sold Out</span>";
                }
                else {
                    $open_spots = "<span class='spots_open'>" . $avail . " Spots</span>";
                }

                if ( $avail <= 0 ) {
                    $register_button_text = "<span class='button_closed'>Sold<strong>Out</strong></span>";
                    if ( epl_is_ok_for_waitlist() && ($wl_spaces_left = epl_waitlist_spaces_open()) !== false ) {
                        $register_button_url = array( '_date_id' => $date_id, 'button_text' => "Waiting List", 'class' => 'epl_button button_waitlist' );
                    }
                }
                /* else if ( $avail <= 3 && $avail > 0 ) {
                  //$register_button = "<span class='button_closed'>". $avail ." Left!</span>"; //FIGURE HOW TO USE BUTTON AND BREAKS SOLD OUT
                  $register_button = get_the_register_button(null, false, array('_date_id'=>$date_id, 'button_text'=> "Book Now", 'class' => 'epl_button button_partial' ));
                  } */
                else {
                    $register_button_url = array( '_date_id' => $date_id, 'button_text' => "Book Now" );
                }

                $go = true;

                if ( $_from_date && epl_compare_dates( $date, $_from_date, '<' ) )
                    $go = false;

                if ( $_to_date && epl_compare_dates( $date, $_to_date, '>' ) )
                    $go = false;

                if ( !$go )
                    continue;





                foreach ( $event_details['_epl_start_time'] as $time_id => $time ) {

                    if ( epl_is_date_level_time() && !epl_is_empty_array( $date_specifc_time ) && (!isset( $date_specifc_time[$time_id] ) || !isset( $date_specifc_time[$time_id][$date_id] )) )
                        continue;

                    $regis_end_time = epl_get_element_m( $time_id, '_epl_regis_endtime', $event_details, null );

                    if ( $regis_end_time && epl_compare_dates( strtotime( $regis_end_time, $date ), EPL_TIME, '<' ) )
                        continue;

                    $register_button = get_the_register_button( null, false, $register_button_url + array( '_time_id' => $time_id ) );
                    $event_link = '';
                    if ( isset( $event_details['_epl_link'] ) && $event_details['_epl_link'] != '' ) {
                        $event_link = epl_anchor( $event_details['_epl_link'], 'Learn More', '_self', 'class="event_link"' ) . ' ' . $register_button;
                    }
                    else {
                        $event_link = $register_button;
                    }
                    //removed per Jessi
                    //if (!$regis_end_time && epl_compare_dates( strtotime($time, $date), EPL_TIME, '<' ))
                    //continue;

                    $event_id = get_the_ID();
                    $event_excerpt = get_the_excerpt();
                    $event_tooltip = '';
                    if ( $event_excerpt !== '' ) {
                        $event_tooltip = " <span class='tip event_tooltip' title='" . $event_excerpt . "'>i</span>";
                    }
                    //$event_title = epl_anchor($event_details['_epl_link'],get_the_title(),'_self','class="event_link"');
                    $event_title = get_the_title();
                    $event_categories = strip_tags( get_the_term_list( $event_id, 'epl_event_categories', '', ',', '' ) );
                    $event_participation = $event_details['_epl_participation'];
                    $event_ages = epl_get_element( '_epl_ages', $event_details, array( ) );
                    $event_dow =date_i18n( "D", $date );
                    $event_dom =date_i18n( "m/j/y", $date );
                    $end_time = $event_details['_epl_end_time'][$time_id];
                    $time_avail = (epl_get_time_capacity($time_id)!='')?' / ' .epl_get_time_capacity($time_id):''; //naah, crap
                    //$event_ages_range = ageRange2($event_ages);

                    $price = min( $event_details['_epl_price'] );
                    if ( $price <= 0 ) {
                        $print_price = "<span class='price_free'>Free</span>";
                    }
                    else {
                        $print_price = "<span class='price'>$" . $price . "</span>";
                    }
                    $avail = epl_get_element( $time_id, $event_details['_epl_time_capacity'] );
                    //build the td rows				
                    $temp_table_row = '<td>' . $event_dom . '</td>';
                    //$temp_table_row .= '<td>' . $event_dow . '</td>';
                    $temp_table_row .= '<td>' . $time . '</td>';
                    $temp_table_row .= '<td>' . epl_get_att_count( array( 'for' => 'time', 'date_id' => $date_id, 'time_id' => $time_id, 'default' => 0 ) ) .$time_avail. '</td>';
                    //$temp_table_row .= '<td>' . epl_get_element( '_total_att_' . get_the_ID() . "_time_{$date_id}_{$time_id}", $current_att_count, 0 ) . ($avail ? $avail : '/') . '</td>';
                    //$temp_table_row .= '<td>' . $end_time . '</td>';
                    $temp_table_row .= '<td class="event_title">' . $event_edit_link . '</td>';
                    // $temp_table_row .= '<td>' . $event_participation . '</td>';
                    //$temp_table_row .= '<td class="ages">' . $event_ages_range .'</td>';				
                    //$temp_table_row .= '<td>' . $print_price . '</td>';
                    //$temp_table_row .= '<td class="register_button">' . $event_link . '</td>';
                    //following columns will be hidden like ninjas by the Datatables dom filters


                    if ( $temp_table_row != '' )
                        $table_row[] = '<tr>' . $temp_table_row . '</tr>';
                }


            endforeach;

        endwhile;


        echo <<< EOT
<table id="datatables-1" class="dataTable" aria-describedby="datatables-1_info">
		<thead>
		<tr role="row">
		<th>Date</th>
                
		<th>Start Time</th>
		<th>Attendees</th>

		<th>Class Name</th>


		</thead> 
EOT;


        echo implode( $table_row );

        echo "</table>";
        ?>

        <script type="text/javascript">

           do_datatable('#datatables-1');

        </script>

    <?php else: ?>

        <div><?php epl_e( 'No records found.' ); ?></div>

    <?php

    endif;

    wp_reset_query();
    ?>



</div>


