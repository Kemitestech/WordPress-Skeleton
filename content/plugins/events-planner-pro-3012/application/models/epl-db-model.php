<?php

// For some reason can't extend wpdb and get it to work.  Will research.


class EPL_db_model extends EPL_model {

    private static $instance;
    private $db;
    public $delim = EPL_PLUGIN_DB_DELIM;
    private $old_regis_data_ids = null;


    function __construct() {
        global $wpdb;
        parent::__construct();
        $this->db = $wpdb;
        $this->ecm = EPL_base::get_instance()->load_model( 'epl-common-model' );
    }


    public static function get_instance() {
        if ( !self::$instance ) {

            self::$instance = new EPL_db_model;
        }

        return self::$instance;
    }


    function query( $sql ) {


        echo "<pre class='prettyprint'>" . __LINE__ . "> " . print_r( $sql, true ) . "</pre>";
        return $this->db->query( $sql );
    }


    function find_user_id_for_regis( $regis_id = null, $user_id = null ) {



        if ( !is_null( $user_id ) )
            return $user_id;

        if ( EPL_IS_ADMIN && epl_get_element( 'user_id', $_POST, '' ) != '' )
            return $_POST['user_id'];

        if ( !EPL_IS_ADMIN && isset( $_SESSION['temp_fields']['user_id'] ) )
            return $_SESSION['temp_fields']['user_id'];

        global $wpdb;

        if ( !is_numeric( $regis_id ) && EPL_IS_ADMIN )
            $regis_id = epl_get_element( 'post', $_REQUEST );


        $r = $wpdb->get_row( "
            SELECT user_id
            FROM {$wpdb->epl_registration}
            WHERE regis_id = {$regis_id}", ARRAY_A );

        $user_id = epl_get_element( 'user_id', $r, 0 );

        if ( $user_id > 0 )
            return $user_id;

        $r = $wpdb->get_row( "
            SELECT *
            FROM {$wpdb->usermeta}
            WHERE meta_key = '_epl_regis_post_id_{$regis_id}' ORDER BY umeta_id DESC LIMIT 1", ARRAY_A );

        $user_id = epl_get_element( 'user_id', $r, 0 );

        return $user_id;
    }


    function reset_table_for_registration( $post_ID = null ) {
        global $wpdb;

        if ( !is_null( $post_ID ) ) {

            $this->old_regis_data_ids = $wpdb->get_results( $wpdb->prepare( 'SELECT * from ' . $wpdb->epl_regis_data . ' WHERE regis_id = %d', $post_ID ), ARRAY_A );
        }

        $this->reset_tables( $post_ID );
        $this->populate_db_tables( $post_ID );
    }


    function reset_tables( $post_ID = null ) {
        global $wpdb;

        $WHERE = '';

        if ( !is_null( $post_ID ) )
            $WHERE = " WHERE regis_id = " . $wpdb->escape( $post_ID );

        $wpdb->query( 'DELETE FROM ' . $wpdb->epl_registration . $WHERE );
        $wpdb->query( 'DELETE FROM ' . $wpdb->epl_regis_payment . $WHERE );
        $wpdb->query( 'DELETE FROM ' . $wpdb->epl_regis_data . $WHERE );
        $wpdb->query( 'DELETE FROM ' . $wpdb->epl_regis_form_data . $WHERE );
        $wpdb->query( 'DELETE FROM ' . $wpdb->epl_regis_events . $WHERE );
    }


    function populate_db_tables( $post_ID = null, $event_id = null ) {
        //echo "<pre class='prettyprint'>" . __LINE__ . "> " . print_r( (memory_get_usage( true ) / 1048576 ), true ) . "</pre>";
        if ( !$post_ID ) {
            set_time_limit( 300 );
            @ini_set( 'memory_limit', '1024M' );
        }

        EPL_registration_model::get_instance()->assign_incremental_id( $post_ID );
        global $event_details, $regis_details, $post, $wpdb;
        $this->debug = false;
        $regis_list = $this->get_the_data_new( $post_ID );

        $csv_row = '';
        $form_data_array = array();
        $form_data_array_header = array();

        $main_table = array_flip( $this->tables( 'wp_epl_registration' ) );
        $payment_table = array_flip( $this->tables( 'wp_epl_regis_payment' ) );

        if ( is_null( $regis_list ) )
            exit( 'no records found' );

        //echo "<pre class='prettyprint'>" . __LINE__ . "> " . print_r( epl_memory_get_usage(), true ) . "</pre>";
        foreach ( $regis_list as $regis ) :

            $regis_id = $regis->ID;
            //if ( $regis_id == 12973 )
            //  $this->debug = true;

            $this->ecm->setup_regis_details( $regis_id );
            $_regis_key = $regis_details['__epl']['_regis_id'];

            $regis_event_data = $regis_details['__epl'][$_regis_key];
            $user_id = $this->find_user_id_for_regis( $regis_id );
            if ( $this->debug )
                echo "<pre class='prettyprint'>" . __LINE__ . "> " . print_r( $regis_details, true ) . "</pre>";

            #################
            # insert main table data 
            #################
            $ins = array();
            if ( isset( $regis_event_data['cart_totals'] ) )
                $ins = array_intersect_key( $regis_event_data['cart_totals']['money_totals'], $main_table );
            else {
                foreach ( $main_table as $k => $v ) {
                    $ins[$k] = epl_get_element( '_epl_' . $k, $regis_details );
                }
            }

            $ins['regis_id'] = $regis_id;
            $ins['regis_key'] = $_regis_key;
            $ins['status'] = $regis_details['_epl_regis_status'];
            $ins['regis_date'] = $regis_details['post_date'];
            $ins['user_id'] = $user_id;


            $regis_data = $this->epl->epl_util->get_the_regis_dates_times_prices( $regis_id, true );

            $ins['num_events'] = count( $regis_data );


            $wpdb->insert( $wpdb->epl_registration, $ins );

            $epl_registration_id = $wpdb->insert_id;
            $total_tickets = 0;
            #################
            # insert payment data, added the $payment_data as of 2.0
            #################
            $payment_data = epl_get_element( '_epl_payment_data', $regis_details, array() );

            if ( empty( $payment_data ) )
                $payment_data[] = $regis_details;

            foreach ( $payment_data as $k => $p ) {
                $ins = array(
                    'regis_id' => $regis_id,
                    'payment_amount' => $p['_epl_payment_amount'],
                    'payment_date' => (!empty( $p['_epl_payment_date'] )) ? date( 'Y-m-d H:i:s', epl_get_date_timestamp( $p['_epl_payment_date'] ) ) : null,
                    'payment_method_id' => epl_get_element( '_epl_payment_method', $p, 0 ),
                    'transaction_id' => $p['_epl_transaction_id'],
                    'note' => $p['note']
                );

                $wpdb->insert( $wpdb->epl_regis_payment, $ins );
            }
            $this->primary_pulled = false;

            foreach ( $regis_data as $event_id => $date_data ):
                $this->form_no = 0;
                setup_event_details( $event_id );

                #################
                # insert event specific totals
                #################


                $event_totals = ( array ) $regis_event_data['_events'][$event_id]['money_totals'];
                $event_counts = ( array ) $regis_event_data['_events'][$event_id]['_att_quantity'];

                $num_dates = ($event_details['_epl_event_type'] != 7 ? count( $regis_data[$event_id] ) : 1);

                $ins = array(
                    'regis_id' => $regis_id,
                    'event_id' => $event_id,
                    'num_dates' => $num_dates,
                    'subtotal' => epl_get_element( 'subtotal', $event_totals, 0 ),
                    'surcharge' => epl_get_element( 'surcharge', $event_totals, 0 ),
                    'discountable_total' => epl_get_element( 'discountable_total', $event_totals, 0 ),
                    'non_discountable_total' => epl_get_element( 'non_discountable_total', $event_totals, 0 ),
                    'pre_discount_total' => epl_get_element( 'pre_discount_total', $event_totals, 0 ),
                    'discount_amount' => epl_get_element( 'discount_amount', $event_totals, 0 ),
                    'discount_code' => epl_get_element( 'discount_code', $event_totals ),
                    'grand_total' => epl_get_element( 'grand_total', $event_totals, 0 )
                );

                $wpdb->insert( $wpdb->epl_regis_events, $ins );


                $pack_regis = (epl_get_element( '_epl_pack_regis', $event_details ) == 10);

                $purchased_tickets = ( array ) $regis_event_data['_dates']['_att_quantity'][$event_id];

                $tickets_to_show = array_intersect_key( $purchased_tickets, $event_details['_epl_price_name'] );

                #################
                # insert form data
                #################

                $this->form_data( $regis_id, $_regis_key, $event_id, $tickets_to_show );


                /*
                 *                                
                 */

                foreach ( $date_data as $date_id => $data ):

                    $times = $data['time'];

                    foreach ( $times as $time_id => $time_data ):
                        if ( $time_id == 'total_tickets' )
                            continue;
                        $prices = $time_data['price'];


                        foreach ( $prices as $price_id => $price_data ):
                            $price_per_att = ($event_totals[$price_id] / $price_data['qty']);
                            if ( $price_data['qty'] > 0 ) :

                                for ( $i = 1; $i <= $price_data['qty']; $i++ ):

                                    /* if ( $pack_regis ) {

                                      $attendance_dates = epl_get_element( "_pack_attendance_dates_{$event_id}_{$price_id}_" . $i, $regis_details, null );
                                      echo "<pre class='prettyprint'>" . __LINE__ . "> " . print_r( $attendance_dates, true ) . "</pre>";
                                      $pack_count = count( $attendance_dates );
                                      $attendance_date_number = array_search( $date_id, array_keys( $attendance_dates ) ) + 1;
                                      } */

                                    $ins = array(
                                        'regis_id' => $regis_id,
                                        'event_id' => $event_id,
                                        'date_id' => $date_id,
                                        'time_id' => $time_id,
                                        'price_id' => $price_id,
                                        'price' => $price_data['raw_price'],
                                        'quantity' => 1,
                                        'total_quantity' => $price_data['qty']
                                    );

                                    if ( $event_details['_epl_event_type'] != 7 )
                                        $total_tickets++;
                                    else
                                        $total_tickets = 1;

                                    $wpdb->insert( $wpdb->epl_regis_data, $ins );
                                    $new_id = $wpdb->insert_id;

                                    $this->update_attendance_regis_data_ids( $new_id, $ins );

                                endfor;
                            endif;
                        endforeach;

                    endforeach;



                endforeach;


            endforeach;

            $wpdb->update( $wpdb->epl_registration, array( 'total_tickets' => ($total_tickets == 0 ? 1 : $total_tickets) ), array( 'id' => $epl_registration_id ) );
            if ( $this->debug )
                exit;
            //$wpdb->print_error();
        endforeach;


        //exit;
        //echo "<pre class='prettyprint'>" . __LINE__ . "> " . print_r( epl_memory_get_usage(), true ) . "</pre>";
    }


    function update_attendance_regis_data_ids( $new_id, $new_data = array() ) {
        if ( is_null( $this->old_regis_data_ids ) )
            return;

        unset( $new_data['price'] );

        global $wpdb;

        foreach ( $this->old_regis_data_ids as $id => $old_data ) {

            $old_id = $old_data['id'];
            unset( $old_data['id'] );
            unset( $old_data['meta'] );
            unset( $old_data['price'] );

            $diff = array_diff( $new_data, $old_data );

            if ( count( $diff ) == 0 ) {
                $wpdb->query( 'UPDATE ' . $wpdb->epl_attendance . " SET regis_data_id= $new_id WHERE regis_data_id = $old_id" );
                unset( $this->old_regis_data_ids[$id] );
                break;
            }
        }
    }


    function form_data( $regis_id, $_regis_key, $event_id, $tickets_to_show = null ) {

        global $event_details, $regis_details, $wpdb;


        $attendee_info = $regis_details['__epl'][$_regis_key]['_attendee_info'];

        if ( $this->debug )
            echo "<pre class='prettyprint'>" . __LINE__ . "> " . basename( __FILE__ ) . " > " . print_r( $attendee_info, true ) . "</pre>";
        $event_ticket_buyer_forms = array_flip( epl_get_element( '_epl_primary_regis_forms', $event_details, array() ) );
        $event_addit_forms = (epl_get_element( '_epl_addit_regis_forms', $event_details )) ? array_flip( $event_details['_epl_addit_regis_forms'] ) : array();

        if ( version_compare( epl_regis_plugin_version(), '1.4', '>=' ) && epl_sc_is_enabled() && epl_get_setting( 'epl_sc_options', 'epl_sc_forms_to_use' ) == 1 ) {
            $sc_event_ticket_buyer_forms = array_flip( epl_get_setting( 'epl_sc_options', 'epl_sc_primary_regis_forms', array() ) );
            $event_ticket_buyer_forms = $sc_event_ticket_buyer_forms; // + $event_ticket_buyer_forms;
            $event_addit_forms = array_flip( epl_get_setting( 'epl_sc_options', 'epl_sc_addit_regis_forms', array() ) );
        }

        if ( empty( $event_ticket_buyer_forms ) )
            $event_ticket_buyer_forms = array( '4e8b3920c839b' => 1 );
        /*
         * find price forms if any.
         */

        $price_forms = epl_get_element( '_epl_price_forms', $event_details, array() );

        $_price_forms = array();
        foreach ( $price_forms as $k => $v ) {
            $_price_forms += $v;
        }


        //find the list of all forms
        $available_forms = $this->ecm->get_list_of_available_forms();
        $available_fields = $this->ecm->get_list_of_available_fields();

        //isolate the ticket buyer forms that are selected inside the event
        $ticket_buyer_forms = array_intersect_key( $available_forms, $event_ticket_buyer_forms );

        //isolate the additional forms for attendees.
        $addit_forms = array_intersect_key( $available_forms, array_merge( $event_addit_forms, $_price_forms ) );

        //This will combine all the fields in all the forms so that we can construct a header row.
        $tickey_buyer_fields = array();
        foreach ( $ticket_buyer_forms as $_form_id => $_form_info )
            $tickey_buyer_fields = array_merge( $tickey_buyer_fields, $_form_info['epl_form_fields'] );

        //combine all the fields from the attendee forms
        $event_addit_fields = array();
        foreach ( $addit_forms as $_form_id => $_form_info ) {

            //$event_addit_fields += $_form_info['epl_form_fields'];
            $event_addit_fields = array_merge( $event_addit_fields, $_form_info['epl_form_fields'] );
        }

        $epl_fields_inside_form = array_flip( $tickey_buyer_fields ); //get the field ids inside the form
        $epl_addit_fields_inside_form = array_flip( $event_addit_fields ); //get the field ids inside the form
        //when creating a form in form manager, the user may rearrange fields.  Find their desired order
        $epl_fields_to_display = $this->epl_util->sort_array_by_array( $available_fields, $epl_fields_inside_form );
        $epl_addit_fields_to_display = $this->epl_util->sort_array_by_array( $available_fields, $epl_addit_fields_inside_form );

        //final list of all the fields to display
        //$epl_fields_to_display = $epl_fields_to_display + $epl_addit_fields_to_display;

        $ins = array();

        $ins['regis_id'] = $regis_id;
        $ins['event_id'] = $event_id;

        $ins['field_id'] = array();
        $ins['input_slug'] = array();

        $ins['value'] = array();
        if ( !$this->primary_pulled ) {
            //################################### Ticket buyer form data ############################################
            if ( $this->debug )
                echo "<pre class='prettyprint'>" . __LINE__ . "> " . basename( __FILE__ ) . " > " . print_r( $epl_fields_to_display, true ) . "</pre>";


            $form_data_array_tmp = array();
            foreach ( $epl_fields_to_display as $field_id => $field_atts ) {

                //1.3 stores in [field id][event id][0]
                //2.0 stores in [field id][0]
                //if ( epl_sc_is_enabled() && isset( $attendee_info[$field_id][0] ) ) {
                if ( isset( $attendee_info[$field_id][0] ) ) {

                    $value = epl_get_element( 0, $attendee_info[$field_id] );
                }
                else {
                    $value = (isset( $attendee_info[$field_id] )) ? epl_get_element( 0, $attendee_info[$field_id][$event_id] ) : '';
                }

                $raw_value = $value;

                if ( $field_atts['input_slug'] == 'email' ) {

                    $email_list[$regis_post_id] = $value;

                    if ( $regis_post_id && $regis_post_id != $this_regis_post_id )
                        unset( $email_list[$regis_post_id] );
                }

                if ( $field_atts['input_type'] == 'select' || $field_atts['input_type'] == 'radio' ) {

                    $value = $raw_value; //(isset( $field_atts['epl_field_choice_text'][$value] ) && $field_atts['epl_field_choice_text'][$value] !== '') ? $field_atts['epl_field_choice_text'][$value] : $value;
                }
                elseif ( $field_atts['input_type'] == 'checkbox' ) {

                    if ( !epl_is_empty_array( $field_atts['epl_field_choice_value'] ) )
                        $value = (implode( ',', ( array ) $raw_value ) );
                    elseif ( is_array( $value ) ) {

                        $value = implode( ',', $raw_value );
                    }
                }
                //if ( $value != '' ) {

                $ins['field_id'][] = $field_id;
                $ins['input_slug'][] = $field_atts['input_slug'];
                $ins['form_no'] = $this->form_no;
                $ins['value'][] = str_replace( $this->delim, " ", $value );

                //$wpdb->insert( $wpdb->epl_regis_form_data, $ins );
                //}
            }



            $ins['field_id'] = implode( $this->delim, $ins['field_id'] );
            $ins['input_slug'] = implode( $this->delim, $ins['input_slug'] );
            $ins['form_no'] = $this->form_no;
            $ins['value'] = implode( $this->delim, $ins['value'] );

            if ( $this->debug )
                echo "<pre class='prettyprint'>" . __LINE__ . "> " . basename( __FILE__ ) . " > " . print_r( $ins, true ) . "</pre>";


            $wpdb->insert( $wpdb->epl_regis_form_data, $ins );


            //###################  End Ticket Buyer Data #########################################
        }

        //$this->primary_pulled = true;

        $ins['event_id'] = $event_id;
        $ins['field_id'] = array();
        $ins['input_slug'] = array();
        $ins['value'] = array();
        $counter = 0;
        $att_counter = 1;

        foreach ( $tickets_to_show as $ticket_id => $ticket_quantities ) {

            if ( is_array( $ticket_quantities ) ) {
                $tmp_price_inner_keys = array_keys( $ticket_quantities );
                $ticket_qty = array_sum( $ticket_quantities );
            }
            if ( $ticket_qty == 0 )
                continue;

            if ( epl_is_empty_array( $price_forms ) ) {
                
            }
            if ( $this->debug )
                echo "<pre class='prettyprint'>" . __LINE__ . "> " . basename( __FILE__ ) . " > " . print_r( $epl_addit_fields_to_display, true ) . "</pre>";
            foreach ( $ticket_quantities as $ticket_qty_id => $quantities ) {



                if ( version_compare( $version, '1.2.9', '<' ) )
                    $counter = 1;

                for ( $i = 0; $i < $quantities; $i++ ) {
                    $this->form_no++;
                    //not good, runs every time in the loop
                    /* if ( $pack_regis && $attendance_dates = epl_get_element( "_pack_attendance_dates_{$event_id}_{$ticket_id}_" . ($i + 1), $regis_data, null ) ) {

                      $pack_count = count( $attendance_dates );
                      $attendance_date_number = array_search( $date_id, array_keys( $attendance_dates ) ) + 1;
                      }

                      if ( $pack_regis && $date_id && !isset( $attendance_dates[$date_id] ) ) {
                      break;
                      continue;
                      } */


                    $ticket_label = epl_escape_csv_val( epl_get_element( $ticket_id, $event_details['_epl_price_name'] ) );

                    if ( epl_is_date_level_price() ) {
                        $reserved_date_key = $ticket_qty_id;
                        $reserved_dates = epl_get_element_m( $ticket_qty_id, '_epl_start_date', $event_details );
                    }

                    if ( epl_is_date_level_time() ) {

                        $reserved_time_key = $reserved_times[$ticket_qty_id];

                        $reserved_times_display = epl_get_element_m( $reserved_time_key, '_epl_start_time', $event_details );
                    }

                    $ins['field_id'] = array();
                    $ins['input_slug'] = array();
                    $ins['value'] = array();

                    foreach ( $epl_addit_fields_to_display as $field_id => $field_atts ) {

                        $value = '';

                        //if ( $this->debug )
                          //  echo "<pre class='prettyprint'>" . __LINE__ . "> " . basename( __FILE__ ) . " > " . print_r( epl_get_num_events_in_cart(), true ) . "</pre>";
                        //new v1.2.b9+
                        //if ( epl_sc_is_enabled() || isset( $attendee_info[$field_id][0] ) ) { //if this, price specific forms will get the primary form vals also
                        if ( epl_sc_is_enabled() ) {

                            $value = epl_get_element( 0, $attendee_info[$field_id] );
                        }
                        else
                            $value = (isset( $attendee_info[$field_id] )) ? epl_get_element( 0, $attendee_info[$field_id][$event_id] ) : '';

                        if ( isset( $attendee_info[$field_id][$event_id][$ticket_id] ) ) {

                            $value = epl_get_element( $counter, $attendee_info[$field_id][$event_id][$ticket_id] );
                        }
                        elseif ( isset( $attendee_info[$field_id][$event_id][$counter] ) ) {
                            $value = $attendee_info[$field_id][$event_id][$counter];
                        }
                        $raw_value = $value;
                        if ( $field_atts['input_type'] == 'select' || $field_atts['input_type'] == 'radio' ) {

                            $value = $raw_value; //(isset( $field_atts['epl_field_choice_text'][$value] ) && $field_atts['epl_field_choice_text'][$value] !== '') ? $field_atts['epl_field_choice_text'][$value] : $value;
                        }
                        elseif ( $field_atts['input_type'] == 'checkbox' ) {

                            if ( !epl_is_empty_array( $field_atts['epl_field_choice_value'] ) )
                                $value = (implode( ',', ( array ) $raw_value ) );
                            elseif ( !epl_is_empty_array( $value ) ) {
                                $value = implode( ',', $raw_value );
                            }
                            else {
                                $value = html_entity_decode( htmlspecialchars_decode( $value ) );
                            }
                        }
                        /* else {

                          $value = html_entity_decode( htmlspecialchars_decode( $value ) );
                          } */
                        // if ( $value != '' ) { //FOR NOW, WILL ENTER EMPTY ROW IN THE TABLE, WILL HELP TRACK COUNTS

                        $ins['field_id'][] = $field_id;
                        $ins['input_slug'][] = $field_atts['input_slug'];
                        $ins['form_no'] = $this->form_no;
                        $ins['value'][] = str_replace( $this->delim, " ", $value );

                        //$wpdb->insert( $wpdb->epl_regis_form_data, $ins );
                        //}
                    }

                    //if ( !epl_is_empty_array( $ins['field_id'] ) ) {
                    // echo "<pre class='prettyprint'>" . __LINE__ . "> " . print_r($ins, true). "</pre>";
                    $ins['field_id'] = implode( $this->delim, $ins['field_id'] );
                    $ins['input_slug'] = implode( $this->delim, $ins['input_slug'] );
                    $ins['form_no'] = $this->form_no;
                    $ins['value'] = implode( $this->delim, $ins['value'] );
                    if ( $this->debug )
                        echo "<pre class='prettyprint'>" . __LINE__ . "> " . basename( __FILE__ ) . " >counter:$counter , ticket_id: $ticket_id " . print_r( $ins, true ) . "</pre>";
                    $wpdb->insert( $wpdb->epl_regis_form_data, $ins );
                    //}


                    $counter++;
                    $att_counter++;
                }
            }
        }
    }


    function get_the_data_new( $post_ID = null, $exclude = null ) {
        global $wpdb;
        $WHERE = "WHERE post_type='epl_registration' AND (`post_status`='publish' || `post_status`='private')";

        if ( !is_null( $post_ID ) )
            $WHERE .= " AND ID= " . intval( $post_ID );

        $r = $this->db->get_results( "SELECT * FROM {$wpdb->posts} $WHERE ORDER BY ID" );

        return $r;
    }


    function get_the_data( $post_ID = null ) {
        $meta_query = array();

        $qry_args = array(
            'post_type' => 'epl_registration',
            'post_status ' => array( 'publish' ),
            'posts_per_page' => -1,
            'orderby' => 'date',
            'order' => 'ASC',
            'meta_query' => $meta_query
        );

        if ( $_REQUEST['_epl_regis_status'] != '' ) {

            $meta_query[] = array(
                'key' => '_epl_regis_status',
                'value' => intval( $_REQUEST['_epl_regis_status'] ),
                'type' => 'NUMERIC',
                'compare' => '='
            );
        }

        if ( $_REQUEST['event_id'] != '' ) {

            $meta_query[] = array(
                'key' => '_total_att_' . intval( $_REQUEST['event_id'] ),
                    /* 'value' => intval( $_REQUEST['_epl_regis_status'] ),
                      'type' => 'NUMERIC',
                      'compare' => '=' */
            );
        }

        if ( $_REQUEST['lookup'] != '' ) {

            $meta_query[] = array(
                'key' => '__epl',
                'value' => $_REQUEST['lookup'],
                //'type' => 'NUMERIC',
                'compare' => 'LIKE'
            );

            //$qry_args['posts_per_page'] = 10;
            $qry_args['order'] = 'DESC';
        }

        if ( !is_null( $post_ID ) ) {
            $qry_args['p'] = $post_ID;
        }
        else {

            $qry_args['meta_query'] = $meta_query;
        }


        add_filter( 'posts_where', array( $this, 'filter_where' ) );

        $r = new WP_Query( $qry_args );

        remove_filter( 'posts_where', array( $this, 'filter_where' ) );
        return $r;
    }


    function filter_where( $where = '' ) {

        if ( isset( $_REQUEST['daterange'] ) && $_REQUEST['daterange'] != '' ) {
            $dates = array();
            if ( strpos( $_REQUEST['daterange'], '-' ) !== false ) {
                $dates = explode( '-', $_REQUEST['daterange'] );
            }
            else {
                $dates[0] = $_REQUEST['daterange'];
            }
        }


        if ( !empty( $dates[0] ) ) {
            $where .= " AND post_date >= '" . date_i18n( "Y-m-d", epl_get_date_timestamp( $dates[0] ) ) . "'";
        }
        if ( !empty( $dates[1] ) ) {
            $where .= "  AND post_date <= '" . date_i18n( "Y-m-d", epl_get_date_timestamp( $dates[1] ) ) . "'";
        }

        return $where;
    }


    function get_the_list_new() {

        $r = $this->db->get_results(
                $this->db->prepare(
                        "SELECT rd.*, r.* 
                    FROM  {$this->db->epl_regis_data} rd
                    INNER JOIN {$this->db->epl_registration} r
                        ON r.regis_id = rd.regis_id
                    WHERE rd.event_id = %d", $_REQUEST['event_id'] ) );
        return $r;
        echo "<pre class='prettyprint'>" . __LINE__ . "> " . print_r( $this->db->last_query, true ) . "</pre>";
        //echo "<pre class='prettyprint'>" . __LINE__ . "> " . print_r( $r, true ) . "</pre>";
    }


    function tables( $table ) {

        global $wpdb;

        $tables = array();


        $tables['wp_epl_registration'] = array(
            'regis_id',
            'regis_key',
            'num_events',
            'status',
            'subtotal',
            'surcharge',
            'discountable_total',
            'non_discountable_total',
            'pre_discount_total',
            'discount_amount',
            'discount_code',
            'discount_code_id',
            'discount_source_id',
            'donation_amount',
            'grand_total',
            'original_total',
            'balance_due',
            'regis_date'
        );

        $tables['wp_epl_regis_data'] = array(
            'regis_id',
            'event_id',
            'date_id',
            'time_id',
            'meta',
        );

        $tables['wp_epl_regis_payment'] = array(
            'regis_id',
            'payment_amount',
            'payment_date',
            'payment_method_id',
            'transaction_id'
        );
        $tables['wp_epl_regis_form_data'] = array(
            'regis_id',
            'event_id',
            'form_no',
            'form_field_id',
            'value'
        );

        return $tables[$table];
    }

}
