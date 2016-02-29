<?php

$epl = EPL_Base::get_instance();
$ecm = EPL_common_model::get_instance();
?>

<div id="event_list_wrapper" class="class_list" style="margin:20px 10px; border: 1px solid #eee;padding: 20px;">

    <?php

    global $event_list;
    ?>



    <?php

    global $event_details, $event_fields, $current_att_count;


    $_from_date = epl_get_date_timestamp( epl_get_element( '_epl_from_date', $_POST ) );
    $_to_date = epl_get_date_timestamp( epl_get_element( '_epl_to_date', $_POST ) );

    $table_row = array( );


    $event_dates = array( );
    /* custom event list loop */
    if ( $event_list->have_posts() ):

        while ( $event_list->have_posts() ) :

            $event_list->the_post();
            setup_event_details();

            $ecm->get_current_att_count( get_the_ID() );

            $event_edit_link = '';
            if ( epl_user_is_admin() )
                $event_edit_link = epl_anchor( admin_url( "post.php?post=" . get_the_ID() . "&action=edit" ), '&nbsp;<span style="float:right;">Edit</span> ' );

            $date_specifc_time = epl_get_element( '_epl_date_specific_time', $event_details, array( ) );

            foreach ( $event_details['_epl_start_date'] as $date_key => $date ):

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

                $avail = $event_details['_epl_date_capacity'][$date_key];

                $num_regis = 0;


                if ( isset( $current_att_count['_total_att_' . get_the_ID() . "_date_{$date_key}"] ) )
                    $num_regis = epl_get_element( '_total_att_' . get_the_ID() . "_date_{$date_key}", $current_att_count, 0 );

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
                        $register_button_url = array( '_date_id' => $date_key, 'button_text' => "Waiting List", 'class' => 'epl_button button_waitlist' );
                    }
                }
                /* else if ( $avail <= 3 && $avail > 0 ) {
                  //$register_button = "<span class='button_closed'>". $avail ." Left!</span>"; //FIGURE HOW TO USE BUTTON AND BREAKS SOLD OUT
                  $register_button = get_the_register_button(null, false, array('_date_id'=>$date_key, 'button_text'=> "Book Now", 'class' => 'epl_button button_partial' ));
                  } */
                else {
                    $register_button_url = array( '_date_id' => $date_key, 'button_text' => "Book Now" );
                }

                $go = true;

                if ( $_from_date && epl_compare_dates( $date, $_from_date, '<' ) )
                    $go = false;

                if ( $_to_date && epl_compare_dates( $date, $_to_date, '>' ) )
                    $go = false;

                if ( !$go )
                    continue;





                foreach ( $event_details['_epl_start_time'] as $time_key => $time ) {

                    if ( epl_is_date_level_time() && !epl_is_empty_array( $date_specifc_time ) && (!isset( $date_specifc_time[$time_key] ) || !isset( $date_specifc_time[$time_key][$date_key] )) )
                        continue;

                    $regis_end_time = epl_get_element_m( $time_key, '_epl_regis_endtime', $event_details, null );

                    if ( $regis_end_time && epl_compare_dates( strtotime( $regis_end_time, $date ), EPL_TIME, '<' ) )
                        continue;

                    // $register_button = get_the_register_button( null, false, $register_button_url + array( '_time_id' => $time_key ) );
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
                    $event_dow = date_i18n( "D", $date );
                    $event_dom = date_i18n( "D, m/j/y", $date );
                    $end_time = $event_details['_epl_end_time'][$time_key];

                    $event_ages_range = $event_ages;

                    $price = min( $event_details['_epl_price'] );
                    if ( $price <= 0 ) {
                        $print_price = "<span class='price_free'>Free</span>";
                    }
                    else {
                        $print_price = "<span class='price'>$" . $price . "</span>";
                    }

                    //build the td rows				
                    $temp_table_row = '<td>' . $event_dom . '</td>';

                    $temp_table_row .= '<td>' . $time . '</td>';
                    $temp_table_row .= '<td>' . $end_time . '</td>';
                    $temp_table_row .= '<td class="event_title">' . $event_title . '</td>';
                    $temp_table_row .= '<td>' . get_the_instructor_name() . '</td>';
                    $temp_table_row .= '<td>Sigh In (0/10)</td>';


                    if ( $temp_table_row != '' )
                        $table_row[] = '<tr>' . $temp_table_row . '</tr>';
                }


            endforeach;

        endwhile;


        echo <<< EOT
<table id="leland-1" class="dataTable" aria-describedby="leland-1_info">
		<thead>
		<tr role="row">
		<th>Date</th>

		<th>Start Time</th>
		<th>End Time</th>
		<th>Class Name</th>
		<th>Instructor</th>
		<th></th>

                </tr>
		</thead> 
EOT;


        echo implode( $table_row );

        echo "</table>";
        ?>

        <script type="text/javascript">

            jQuery(document).ready(function($) {
                var oTable = $('#leland-1').dataTable( {
                    "bJQueryUI": true,
                    "sPaginationType": "full_numbers",
                    "iDisplayLength": 20
                    //"sDom": 'T<"clear">lfrtip'

                });
                //oTable.fnSetColumnVis( 9, false, false );  //Age
                //oTable.fnSetColumnVis( 10, false, false );  //Type
                //oTable.fnSetColumnVis( 11, false, false );  //Participation
        				
                $('.column-filter-widgets').prepend('<div class="column-filter-widgets-label">Search By:</div>');
        				
            });

        </script>

    <?php else: ?>

        <div><?php epl_e( 'Sorry, there are no events currently available.' ); ?></div>

    <?php

    endif;

    wp_reset_query();
    ?>

    <?php do_action( 'epl_post_event_list' ); ?>

</div>


