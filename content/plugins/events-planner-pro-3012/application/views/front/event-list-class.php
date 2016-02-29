<?php 
/*
 * Events Planner tabular list template 1, v2.0.6
 */
?>
<style>

    #epl_event_list_tabular td {
        white-space: nowrap;
    }

</style>

<?php

$epl = EPL_Base::get_instance();
$ecm = EPL_common_model::get_instance();
$erptm = EPL_report_model::get_instance();
?>

<div id="event_list_wrapper" class="" style="margin-bottom: 20px;">


    <?php

    $default_fields = array_flip( array( 'title', 'day_of_week', 'date', 'time', 'category', 'location', 'instructor', 'available_spaces', 'regis_button' ) );
    $display_cols = epl_get_element( 'display_cols', $shortcode_atts );

    if ( $display_cols ) {
        $display_cols = strpos( $display_cols, ',' ) === false ? array( $default_fields ) : explode( ',', $display_cols );
        $display_cols = array_map( 'trim', $display_cols );
        $default_fields = array_flip( $display_cols );
    }

    global $event_list, $event_details, $event_fields;


    $_from_date = epl_get_date_timestamp( epl_get_element( '_epl_from_date', $_POST ) );
    $_to_date = epl_get_date_timestamp( epl_get_element( '_epl_to_date', $_POST ) );

    $table_row = array();

    $event_dates = array();
    /* custom event list loop */
    if ( $event_list->have_posts() ):

        while ( $event_list->have_posts() ) :

            $event_list->the_post();
            setup_event_details();
            $event_id = get_the_ID();

            if ( epl_is_empty_array( $event_details['_epl_start_date'] ) )
                continue;

            $counts = $erptm->get_attendee_counts( $event_id, true );

            $event_excerpt = get_the_excerpt();

            $date_specifc_time = epl_get_element( '_epl_date_specific_time', $event_details, array() );

            $_status = epl_get_event_status( true );
            $status_id = key( $_status );

            $status = current( $_status );
            $class = 'status_' . $status_id;

            //$formatted_status = "<span class='status $class'>" . $status . '</span>';
            $formatted_status = "<span class='status $class'>&nbsp;&nbsp;</span>";

            foreach ( $event_details['_epl_start_date'] as $date_id => $date ):
                $date = epl_get_date_timestamp( $date );
                $temp_table_row = '';
                $end_date = $event_details['_epl_end_date'][$date_id];

                $unix_date = $date;

                if ( $status_id == 3 ) {
                    if ( $date < EPL_DATE && $end_date > EPL_DATE ) {

                        $date = EPL_DATE;
                    }
                }
                $weekday = date_i18n( 'N', $date );
                $price = current( ( array ) $event_details['_epl_price'] );
                if ( $price <= 0 ) {
                    $print_price = "<span class='price_free'>Free</span>";
                }
                else {
                    $print_price = "<span class='price_amount'> " . epl_get_formatted_curr( $price, null, true ) . "</span>";
                }

                $start_time = current( ( array ) $event_details['_epl_start_time'] );

                $date_capacity = $event_details['_epl_date_capacity'][$date_id];

                $num_regis = 0;

                $avail = '';
                if ( isset( $current_att_count['_total_att_' . $event_id . "_date_{$date_id}"] ) ) {
                    $num_regis = epl_get_element( '_total_att_' . $event_id . "_date_{$date_id}", $current_att_count, 0 );
                    $avail = $date_capacity - $num_regis;
                }

                $date_note = epl_prefix( ' - ', epl_get_element_m( $date_id, '_epl_date_note', $event_details, '' ) );

                foreach ( $event_details['_epl_start_time'] as $time_id => $time ) {

                    if ( $event_details['_epl_time_hide'][$time_id] == 10 )
                        continue;

                    if ( epl_is_date_level_time() && !epl_is_empty_array( $date_specifc_time ) && (!isset( $date_specifc_time[$time_id] ) || !isset( $date_specifc_time[$time_id][$date_id] )) )
                        continue;

                    $weekday_specific = epl_get_element_m( $time_id, '_epl_weekday_specific_time', $event_details, array() );

                    if ( !epl_is_empty_array( $weekday_specific ) && !isset( $weekday_specific[$weekday] ) )
                        continue;

                    $unix_time = $time != '' ? strtotime( $time, $date ) : $date;

                    if ( !$show_all && $unix_time < EPL_DATE )
                        continue;

                    $event_tooltip = '';
                    if ( $event_excerpt !== '' ) {
                        $event_tooltip = " <span class='tip event_tooltip' title='" . $event_excerpt . "'>i</span>";
                    }

                    $event_title = "<a href =" . get_the_register_button( $event_id, true ) . ' title="' . get_the_title() . '">' . get_the_title() . "</a>";
                    if ( $event_details['_epl_title_link_destination'] == 0 )
                        $event_title = '<a href =' . get_permalink() . ' title="' . get_the_title() . '">' . get_the_title() . '</a>';


                    $event_categories = strip_tags( get_the_term_list( $event_id, 'epl_event_categories', '', ',', '' ) );
                    $event_participation = $event_details['_epl_participation'];
                    $event_ages = epl_get_element( '_epl_ages', $event_details, array() );
                    $event_dow = date_i18n( "D", $date );
                    $event_dom = date_i18n( "m/j/Y", $date );
                    $end_time = $event_details['_epl_end_time'][$time_id];
                    $event_venue_title = get_the_location_name();

                    $loc_id = epl_get_element_m( $date_id, '_epl_date_location', $event_details, epl_get_event_property( '_epl_event_location', true ) > 0 );
                    the_location_details( $loc_id );
                    $event_venue_title = get_the_location_name() . ' ' . get_the_location_gmap_icon( 'See Map' );

                    $time_capacity = epl_get_element_m( $time_id, '_epl_time_capacity', $event_details );
                    $capacity = ($time_capacity) ? $time_capacity : ($date_capacity ? $date_capacity : epl_get_element_m( $date_id, '_epl_date_per_time_capacity', $event_details ));

                    $counts_day_key = $event_id . "_date_{$date_id}";
                    $num_regis = epl_get_element( '_total_att_' . $counts_day_key, $counts, 0 );

                    $counts_time_key = $event_id . "_time_{$date_id}_{$time_id}";

                    //if ( $capacity !== false && isset( $counts['_total_att_' . $counts_time_key] ) ) {
                        $num_regis = epl_get_element( '_total_att_' . $counts_time_key, $counts, 0 );
                    //}

                    $avail = $capacity - $num_regis;

                    $regis_button_args = array(
                        '_date_id' => $date_id,
                        '_time_id' => $time_id,
                        'event_id' => $event_id
                    );

                    $temp_table_row = array();
                    $temp_table_row['title'] = '<td>' . $event_title . '</td>';
                    $temp_table_row['day_of_week'] = '<td>' . $event_dow . '</td>';
                    $temp_table_row['date'] = '<td>' . $event_dom . '</td>';
                    $temp_table_row['time'] = '<td>' . $time . epl_prefix( '-', $end_time ) . '</td>';
                    $temp_table_row['category'] = '<td>' . $event_categories . '</td>';
                    $temp_table_row['location'] = '<td>' . $event_venue_title . '</td>';
                    $temp_table_row['instructor'] = '<td>' . implode( ', ', get_the_instructor_name( false, true, false ) ) . '</td>';
                    $temp_table_row['available_spaces'] = '<td>' . ($capacity !== false ? $avail : '') . '</td>';
                    $temp_table_row['regis_button'] = '<td>' . (($capacity === false || ($avail !== '' && $avail > 0)) ? get_the_register_button( $event_id, false, $regis_button_args ) : epl__( 'Sold Out' ) ) . '</td>';

                    $temp_table_row = array_intersect_key( $temp_table_row, $default_fields );
                    epl_sort_array_by_array( $temp_table_row, $default_fields );
                    if ( !empty( $temp_table_row ) )
                        $table_row[] = '<tr>' . implode( $temp_table_row ) . '</tr>';
                }

            endforeach;

        endwhile;
    endif;

    $table_header = array();
    $table_header['title'] = epl__( 'Title' );
    $table_header['day_of_week'] = epl__( 'Day' );
    $table_header['date'] = epl__( 'Start Date' );
    $table_header['time'] = epl__( 'Time' );
    $table_header['category'] = epl__( 'Category' );
    $table_header['location'] = epl__( 'Location' );
    $table_header['instructor'] = epl__( 'Instructor' );
    $table_header['available_spaces'] = epl__( 'Available Spaces' );
    $table_header['regis_button'] = '';

    $table_header = array_intersect_key( $table_header, $default_fields );
    
    epl_sort_array_by_array( $table_header, $default_fields );
    ?>
    <table id="epl_event_list_tabular" class="epl_class epl_class_list">
        <thead>
            <tr>

                <?php echo '<tr><th>' . implode( '</th><th>', $table_header ) . '</tr>'; ?>

            </tr>
        </thead>
        <tbody>
            <?php echo implode( $table_row ); ?>
        </tbody>
    </table>

    <?php

    wp_reset_query();
    ?>


</div>
<?php

if ( isset( $shortcode_atts['datatable'] ) ):
    $sort_col = epl_get_element( 'datatable_sort_col', $shortcode_atts, 3 ) - 1;
    ?>
    <script>

        jQuery(document).ready(function($) {
            $('#epl_event_list_tabular').dataTable({
                'sDom': '<\"\"fWl><\"clear\">rtip',
                'bPaginate': true,
                'aaSorting': [[<?php echo $sort_col; ?>, 'asc']],
                'bLengthChange': true,
                'bFilter': true,
                'bSort': true,
                'bInfo': true,
                'iDisplayLength': 10,
                'bAutoWidth': false,
                'sPaginationType': 'full_numbers',
                /*'oColumnFilterWidgets': {
                    'aiExclude': [0, 3, 4, 5, 6, 7, 8, 10, 11],
                    'sSeparator': ',',
                    'bGroupTerms': true
                }*/

            });
        });

    </script>

<?php endif; ?>