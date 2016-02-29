<?php

/**
 * This controller handles all the calls from the front of the website, uses multiple models
 *
 * @package		Events Planner for Wordpress
 * @author		Abel Sekepyan
 * @link		http://wpeventsplanner.com
 */
if ( !class_exists( 'EPL_front' ) ) {

    class EPL_front extends EPL_Controller {


        function __construct() {
            global $_on_admin, $current_location;
            $_on_admin = false;

            parent::__construct();

            $this->error = '';


            $this->erm = $this->epl->load_model( 'epl-registration-model' );
            $this->ecm = $this->epl->load_model( 'epl-common-model' );
            $this->egm = $this->epl->load_model( 'epl-gateway-model' );
            $this->eum = $this->epl->load_model( 'epl-user-model' );
        }


        function __return_empty_string( $string ) {

            return '';
        }


        /**
         * Handles all the calls from the front, triggered by the [events_planner] shortcode, then processes based on epl_action variable.
         *
         * @since 1.0
         * @param array $atts shortcode attributes
         * @return string Depending on the epl_action variable, either the event list is retured or the cart/gateway responses are processed
         */
        function run( $atts = array() ) {

            // epl_log( "debug", "<pre>" . print_r( $atts, true ) . "</pre>" );
            $this->shortcode_atts = $atts;
            global $epl_fields, $epl_next_step, $epl_current_step;

            if ( isset( $_REQUEST['epl_action'] ) || isset( $atts['epl_action'] ) ) {

//POST has higher priority
                if ( !isset( $atts['epl_action'] ) )
                    $epl_action = esc_attr( isset( $_POST['epl_action'] ) ? $_POST['epl_action'] : $_GET['epl_action']  );
                else
                    $epl_action = $atts['epl_action'];


                if ( method_exists( $this, $epl_action ) ) {
                    $this->setup_base_url();

                    $epl_current_step = $epl_action;


                    if ( isset( $GLOBALS['epl_ajax'] ) && $GLOBALS['epl_ajax'] == true )
                        die( $this->$epl_action( $atts ) );
                    else
                        return $this->$epl_action( $atts );
                }
            }
            else {

                /*
                 * get the event list
                 * in the loop, a global var $event_details is set for the the template tags
                 */
                $epl_current_step = 'event_list';

                add_action( 'the_post', array( $this, 'setup_event_details' ) );

                return $this->respond();
            }
        }


        function custom_call() {
            $r = apply_filters( 'epl_front_custom_call', array( 'html' => '' ) );
            return $this->epl_util->epl_response( $r );
        }


        function username_exists( $param ) {
            $u = username_exists( $_POST['user_login'] );
            $msg = epl__( 'Available' );
            $this->epl_util->set_response_param( 'username_ok', 1 );
            if ( $u ) {
                $msg = epl__( 'Username Taken' );
                $this->epl_util->set_response_param( 'username_ok', 0 );
            }
            return $this->epl_util->epl_response( array( 'html' => $msg ) );
        }


        function ical() {

            $this->epl->load_file( 'libraries/EasyPeasyICS.php' );

            $ICS = new EasyPeasyICS( get_bloginfo() );

            $ical_token = (epl_get_element( 'ical_token', $_GET, false ) == md5( NONCE_KEY ));

            //$d = $this->epl->epl_util->get_days_for_fc( array('event_id' => 10157,'raw' => 1, 'show_att_counts' => $ical_token ) );
            $d = $this->epl->epl_util->get_days_for_fc( array(
                'raw' => 1,
                'show_att_counts' => $ical_token,
                'show_past' => epl_get_element( 'show_past', $_GET, 0 ),
                'taxonomy' => epl_get_element( 'taxonomy', $_GET, '' ),
                'taxonomy_exclude' => epl_get_element( 'taxonomy_exclude', $_GET, '' ),
                'event_id' => epl_get_element( 'event_id', $_GET, '' ),
                    ) );

            foreach ( $d as $k => $v ) {
                $ICS->addEvent( strtotime( $v['start'] ), strtotime( $v['end'] ), $v['raw_title'] . ' ' . $v['att_counts'], '', $v['edit_url'] );
            }

            $ICS->render();
            exit;
        }


        function open_add_to_waitlist_form() {

            $data['form'] = $this->erm->regis_form( null, true, false, false, array(
                'event_id' => intval( epl_get_element( 'event_id', $_POST, null ) ),
                'waitlist_flow' => 1
                    ) );

            $form = $this->load_view( 'front/registration/waitlist-regis-form', $data, true );

            return $this->epl_util->epl_response( array( 'html' => $form ) );
        }


        function add_waitlist_record() {
            $event_id = intval( epl_get_element( 'event_id', $_POST, '' ) );
            $this->erm->_set_relevant_data( '_attendee_info', $_POST, false, array( $event_id ) ); //from the regis form, add to session
            $original_data = $this->erm->current_data;
            $this->erm->limit_events_in_current_data( array( $event_id ) ); //only address the waitlist record.
            $this->erm->calculate_cart_totals( true, false );
            $this->erm->add_registration_to_db( $_SESSION['__epl'], array( $event_id ) );

            $regis_key = $_SESSION['__epl']['_regis_id'];

            $data['cart_data'] = $this->erm->show_cart();
            $data['regis_form'] = $this->erm->regis_form( null, false, false, false );

            $money_totals = $_SESSION['__epl'][$regis_key]['cart_totals']['money_totals'];

            $data['_epl_regis_status'] = 20;

            $data['post_ID'] = epl_get_element_m( 'post_ID', '__epl', $_SESSION, null );
            $data['_epl_prediscount_total'] = epl_get_element( 'pre_discount_total', $money_totals, 0 );
            $data['_epl_discount_amount'] = epl_get_element( 'discount_amount', $money_totals, 0 );
            $data['_epl_grand_total'] = epl_get_element( 'grand_total', $money_totals, 0 );
            $data['_epl_balance_due'] = epl_get_element( 'grand_total', $money_totals, 0 );
            $data['_epl_transaction_id'] = '';

            $this->erm->update_payment_data( $data );

            $this->ecm->setup_regis_details( $data['post_ID'] );
            $this->erm->set_mode( 'overview' );

            $this->epl_util->regis_id = $data['post_ID'];
            $this->epl_util->send_confirmation_email( $data );
            //now remove the waitlist event from the session so the user can continue with the other events, if any.

            $this->erm->current_data = $original_data;

            $this->erm->remove_events_from_current_data( array( $event_id ) );

            //get rid of post_ID to force creation of new record
            unset( $this->erm->current_data['post_ID'] );

            //change regis_id as 
            $new_regis_key = strtoupper( $this->epl_util->make_unique_id( epl_nz( epl_get_regis_setting( 'epl_regis_id_length' ), 10 ) ) );
            $this->erm->current_data['_regis_id'] = $new_regis_key;

            //move the data to the new regis key 
            $this->erm->current_data[$new_regis_key] = $this->erm->current_data[$regis_key];

            //remove the old regis key
            unset( $this->erm->current_data[$regis_key] );
            $this->erm->calculate_cart_totals( true, true );
            $this->erm->refresh_data( true );

            $r = epl__( "Your name has been added to the waitlist.  You will receive an email shortly and will be notified when any spaces open up." );
            $r = "<div class='epl_success'>" . $r . "</div>";
            return $this->epl_util->epl_response( array( 'html' => $r ) );
        }


        function invoice() {


            setup_regis_details( $_REQUEST['regis_id'] );

            if ( !epl_check_token() ) {
                return epl__( 'You have reached this page in error.' );
            }
            $this->epl->load_model( 'epl-report-model' );
            $i = EPL_report_model::get_instance()->invoice();


            $this->epl->epl_util->make_pdf( $i, false, true, false );
            exit;
        }


        function custom_pdf() {


            setup_regis_details( $_REQUEST['regis_id'] );

            if ( !epl_check_token() ) {
                exit( epl__( 'You have reached this page in error.' ) );
            }
            $data['content'] = apply_filters( 'epl_custom_pdf_content', 'No Content' );
            $data['vars'] = apply_filters( 'epl_custom_pdf_vars', array() );
            $view = apply_filters( 'epl_custom_pdf_view_file', '' );
            if ( $view == '' )
                return '';

            $i = $this->load_view( $view, $data, true );

            $this->epl->epl_util->make_pdf( $i, false, true, false, 'landscape' );
            exit;
        }


        function custom_html( $pdf = false ) {

            $data['content'] = apply_filters( 'epl_custom_html_content', 'No Content' );
            $data['vars'] = apply_filters( 'epl_custom_html_vars', array() );
            $view = apply_filters( 'epl_custom_html_view_file', '' );
            if ( $view == '' )
                return '';

            $i = $this->load_view( $view, $data, true );
//exit($i);
            if ( !$pdf )
                exit( $i );

            //$this->epl->epl_util->make_pdf( $i, false, true, false, 'landscape' );
            exit;
        }


        function load_custom_list() {

            $data = array();

            $data['start_date'] = epl_get_date_timestamp( $_POST['start_date'] );
            $data['end'] = epl_get_date_timestamp( $_POST['end'] );
            $data['raw'] = 1;
            $data['scope'] = epl_get_date_timestamp( $_POST['end'] );


            $r = $this->ecm->events_list( $data );

            $v = $this->load_view( 'front/event-list-weekly', $data, true );

            $this->epl_util->set_response_param( 'tab_label', 'Week of ' . date( 'M d', $data['start'] ) );
            $this->epl_util->set_response_param( 'new_start', strtotime( "+7 day", $data['start'] ) );
            $this->epl_util->set_response_param( 'new_end', strtotime( "+7 day", $data['end'] ) );

            return $this->epl_util->epl_response( array( 'html' => $v ) );
        }


        function load_daily_list() {

            $data = array();

            $direction = ($_POST['direction'] == 'next' ? '+' : '-');



            $specific_week = epl_get_element( 'specific_week', $_POST, false );

            $data['first_date'] = !$specific_week ? strtotime( $direction . "7 day", epl_get_date_timestamp( $_POST['first_date'] ) ) : epl_get_date_timestamp( $_POST['first_date'] );

            $data['current_first_date'] = date( 'D M j', epl_get_date_timestamp( $data['first_date'] ) );

            $data['end'] = epl_get_date_timestamp( $_POST['end'] );
            $data['raw'] = 1;
            $data['scope'] = $_POST['scope'];
            $data['days_to_load'] = $_POST['days_to_load'];
            $data['taxonomy'] = $_POST['taxonomy'];
            $data['container'] = range( 1, $data['days_to_load'] );
            $r = $this->ecm->events_list( $data );

            $v = $this->load_view( 'front/event-list-daily', $data, true );


            $this->epl_util->set_response_param( 'current_first_date', $data['current_first_date'] );
            $this->epl_util->set_response_param( 'first_date', $data['first_date'] );


            return $this->epl_util->epl_response( array( 'html' => $v ) );
        }


        function wildcard_lookup( $limit = 5 ) {

            return $this->ecm->wildcard_lookup( $_POST['lookup'] );
        }


        function user_check_in() {

            $r = $this->eum->user_check_in();


            return $this->epl_util->epl_response( array( 'html' => $_id ) );
            die();
        }


        function login_form() {


            $form = $this->epl->load_view( 'front/login-form', '', true );
            return $this->epl_util->epl_response( array( 'html' => $form ) );
            die();
        }


        function _moneris_process() {

            $r = $this->egm->moneris_process();

            if ( $r === true ) {
                return $this->thank_you_page( false );
            }
            else {
                global $epl_current_step;

                $epl_current_step = 'show_cart_overview';
                $this->error = $r;

                return $this->show_cart_overview();
            }
        }


        function _authnet_sim_process() {

            if ( !count( $_POST ) > 0 )
                return;
            $r = $this->egm->_authnet_sim_process();


            if ( $r === true ) {

                return $this->thank_you_page( false );
            }
            else {

                $this->error = $r;

                return $this->show_cart_overview();
            }
        }


        function single_default_template( $args = array() ) {
            global $post, $epl_is_single;


            if ( stripos( $post->post_type, 'epl_' ) !== false && !$this->epl->load_template_file( 'single-' . $post->post_type . '.php', true ) ) {


                if ( $post->post_type == 'epl_registration' ) {

                    add_filter( 'previous_post_link', '__return_false' );
                    add_filter( 'next_post_link', '__return_false' );
                }

                //for now, substituting the_content with the
                add_filter( 'the_content', array( $this, 'load_single_default_template' ), 99 );

                //can also actually use another template.  not using this for now as different themes produce different results because of css.
                //return $this->epl->locate_template( 'single-' . $post->post_type );
            }
            else
                return epl_get_element( 'template', $args );
        }


        function load_single_default_template( $content ) {

            global $event_details, $epl_post_types, $post, $epl_is_single;


            if ( !is_single() || !isset( $epl_post_types[$post->post_type] ) )
                return $content;

            $data['content'] = do_shortcode( $content );

            return $this->epl->load_view( 'front/single-templates/single-' . $post->post_type, $data, true );
        }


        function load_ajax_login_form() {

            return $this->epl_util->epl_response( array( 'html' => preg_replace( '/[\r\n\t]+/', "", login_with_ajax() ) ) );
        }


        function load_cart_in_modal( $m = 1 ) {

            global $event_details;

            $this->erm->set_mode( 'edit' );

            $event_id = intval( $_REQUEST['event_id'] );

            $data = array();
            //cart
            $data['cart_data'] = $this->erm->show_cart( null, $event_id );
            $data['mode'] = 'edit';
            $data['event_id'] = $event_id;

            setup_event_details( $event_id );
            $data['show_date_selector_link'] = (epl_nz( $event_details['_epl_event_type'], 5 ) < 7) ? (epl_get_element( '_epl_enable_front_date_selector_cal', $event_details, 0 ) != 0) : false;

            if ( epl_admin_override() && epl_get_regis_setting( 'epl_enable_admin_override_cal' ) == 10 )
                $data['show_date_selector_link'] = true;

            //dates
            $dates = $data['cart_data']['cart_items'][$event_id]['event_dates'];
            $data['modal_cart_content'] = "<div class='epl_event_title'><h2>" . get_the_title( $event_id ) . "</h2></div>";
            $data['modal_cart_content'] .= $this->epl->load_view( 'front/cart/cart-dates-display', $data + array( 'event_dates' => $dates ), true );
            //times + prices
            $times_prices = $data['cart_data']['cart_items'][$event_id]['event_time_and_prices'];
            $data['modal_cart_content'] .= $this->epl->load_view( 'front/cart/cart-time-price-display', array( 'event_time_and_prices' => $times_prices ), true );

            //calculate fresh total
            $_totals = $this->erm->calculate_cart_totals( true );

            //get totals for that event
            $data['money_totals'] = epl_get_element( 'money_totals', $_totals[$event_id], array() );

            //cart totals for that event
            $data['cart_totals'] = $this->epl->load_view( 'front/cart/cart-totals', $data, true );

            $data['modal_cart_content'] .= $this->epl->load_view( 'front/cart/cart-subtotals', $data, true );

            //the checkout url button
            $data['checkout_url'] = add_query_arg( array( 'epl_action' => 'show_cart' ), epl_get_shortcode_page_permalink() );
            $data['event_id'] = $event_id;
            $data['modal_cart_content'] .= $this->epl->load_view( 'front/cart/cart-modal-buttons', $data, true );

            $r = $this->epl->load_view( 'front/cart/cart-modal-wrapper', $data, true );

            return $this->epl_util->epl_response( array( 'html' => $r ) );
        }

        /*
         * Temp solution as of 2.0.2.  Will refactor.
         */


        function show_attendee_list( $args = array() ) {
            global $event_details, $wpdb;
            $this->erptm = EPL_report_model::get_instance();
            $filters = array(
                'event_id' => intval( epl_get_element( 'event_id', $args, $_REQUEST['event_id'] ) ),
                'date_id' => epl_get_element( 'date_id', $_REQUEST, null ),
                'time_id' => epl_get_element( 'time_id', $_REQUEST, null ),
                'names_only' => epl_get_element( 'names_only', $_REQUEST, 1 ),
            );

            setup_event_details( $filters['event_id'] );

            $show_second_to_last = apply_filters( 'epl_erpt__view_names__show_second_to_last', true );
            $data['pack_regis'] = (epl_get_element( '_epl_pack_regis', $event_details, 0 ) == 10);
            $data['pack_consecutive'] = (epl_get_element( '_epl_pack_regis_consecutive', $event_details, 0 ) == 10);
            $_filter = array();
            $attendance_dates = array();

            //if this is a pack class
            if ( $data['pack_regis'] ) {
                //find all the registrations for this event
                //for each one, find out if package
                //for each one that is pack, find the pack * X days
                //contstruct array


                $event_date_keys = array_keys( $event_details['_epl_start_date'] );

                $pack_counts = epl_get_element( '_epl_price_pack_size', $event_details, array() );

                $registrations = $wpdb->get_results( "
                    SELECT rd.* 
                    FROM {$wpdb->epl_regis_data} rd
                        INNER JOIN {$wpdb->epl_registration} r
                            ON r.regis_id=rd.regis_id
                        WHERE 1=1 AND (r.status = 2 OR r.status = 5)
                        AND event_id = " . intval( $event_details['ID'] ) );

                if ( $registrations ) {

                    foreach ( $registrations as $regis ) {
                        $regis_weekday = date( 'N', $event_details['_epl_start_date'][$regis->date_id] );

                        if ( isset( $pack_counts[$regis->price_id] ) ) {
                            $pack_counter = epl_get_element( $regis->price_id, $pack_counts, 1 );
                            $start = false;
                            foreach ( $event_details['_epl_start_date'] as $date_id => $date ) {

                                if ( !$start && $date_id != $regis->date_id )
                                    continue;

                                $start = true;

                                $_weekday = date( 'N', $date );

                                if ( $regis_weekday != $_weekday || $pack_counter == 0 )
                                    continue;

                                $pack_counter--;

                                $attendance_dates[$regis->id][$date_id] = $date;
                                $_filter[$regis->id] = $regis->id;
                            }

                            if ( ( $filters['date_id'] && !isset( $attendance_dates[$regis->id][$filters['date_id']] )) || ($filters['time_id'] && $filters['time_id'] != $regis->time_id )
                            ) {

                                unset( $_filter[$regis->id] );
                                unset( $attendance_dates[$regis->id] );
                            }
                            //$offset = array_search( $regis->date_id, $event_date_keys );
                            //$attendance_dates[$regis->id] = array_slice( $event_details['_epl_start_date'], $offset, $pack_counts[$regis->price_id] );
                        }
                    }
                }
            }

            if ( !epl_is_empty_array( $_filter ) ) {
                $_filter = implode( ',', $_filter );
                $_filter = " AND rd.id IN ({$_filter})";
            }


            if ( $filters['names_only'] == 1 )
                $data['list'] = $this->erptm->attendee_form_data( $_filter, $filters['event_id'] );
            else
                $data['list'] = $this->erptm->get_all_data_2( $_filter );


            $data['fields'] = $this->erptm->get_form_fields();
            $data['attendance_dates'] = $attendance_dates;

            $data['event_title'] = get_the_title( $filters['event_id'] );
            $data['event_date'] = isset( $_REQUEST['date_id'] ) ? epl_formatted_date( epl_get_element_m( $_REQUEST['date_id'], '_epl_start_date', $event_details ), "D, M j" ) : '';
            $data['event_time'] = isset( $_REQUEST['time_id'] ) ? epl_get_element_m( $_REQUEST['time_id'], '_epl_start_time', $event_details ) : '';

            $url = admin_url( "edit.php?post_type=epl_event&epl_action=view_names&table_view=1&epl_controller=epl_report_manager&print=1" );

            $url = add_query_arg( $filters, $url );

            $data['print_icon'] = (!isset( $_REQUEST['print'] )) ? '<div><a href="' . $url . '" target="_blank"><img src="' . EPL_FULL_URL . 'images/printer.png" /></a></div>' : '';
            $data['filters'] = $filters;
            $display = epl_get_element( '_epl_show_attendee_list_template', $event_details, 'attendee-list-1' );

            $r = $this->epl->load_view( 'front/attendee-list/' . $display, $data, true );

            // if ( $GLOBALS['epl_ajax'] )
            //   return $r;

            /* if ( isset( $_REQUEST['print'] ) ) {
              $data['content'] = $r;
              $this->epl->load_view( 'admin/template', $data );
              return;
              } */


            return $r;
        }


        function show_attendee_list_old( $args = array() ) {
            global $event_details, $event_regis_post_ids;


            return $this->erm->attendee_list_table( $args );
        }


        function user_bookings( $args = array(), $cal = false ) {

            if ( !is_user_logged_in() ) {
                wp_redirect( wp_login_url( epl_get_url() ) );
            }
            global $event_details;

            $this->eum = $this->epl->load_model( 'epl-user-model' );
            $this->epl->load_model( 'epl-report-model' );

            $data['user_bookings'] = $this->eum->user_bookings( $args );

            $v = 'user-bookings';
            if ( $cal )
                $v = 'user-bookings-cal';

            return $this->epl->load_view( 'user-regis-manager/' . $v, $data, true );
        }


        function get_session_attendees( $args = array() ) {
            // if ( !epl_user_is_admin() )
            //   return $this->epl_util->epl_response( array( 'html' => 'nope' ) );

            $this->eum = $this->epl->load_model( 'epl-user-model' );
            $this->erptm = $this->epl->load_model( 'epl-report-model' );
            $event_id = intval( $_REQUEST['event_id'] );

            $data['session_attendees'] = $this->eum->get_session_attendees( $args );


            $data['form_data'] = $this->erptm->get_form_data( null, $event_id );

            //$r = $this->epl->load_view( 'user-regis-manager/session-attendees', $data, true );

            return $this->epl_util->epl_response( array( 'html' => $data['session_attendees'] ) );
        }


        function check_in_page( $args = array() ) {
            global $event_details;


            return $this->eum->user_check_in_page( $args );
        }


        function user_check_in_table( $args = array() ) {
            global $event_details;


            $r = $this->eum->user_check_in_table( $args );

            return $this->epl_util->epl_response( array( 'html' => $r ) );
        }


        function setup_event_details( $param ) {
            global $post;
//$this->epl_util->set_the_event_details($this->shortcode_atts);

            global $event_details;
//$event_details = $this->ecm->get_post_meta_all( $post->ID );
            $event_details = $this->ecm->setup_event_details( $post->ID, $this->shortcode_atts );
        }


        function get_location_map() {
            $location_id = ( int ) str_replace( 'location_id_', '', $_POST['location_id'] );

            global $location_details;
            the_location_details( $location_id );

            $r = $this->epl->load_view( "front/location-map", '', true );
            return $this->epl_util->epl_response( array( 'html' => $r ) );
        }

        /*
         * for the calendar widget
         */


        function get_events_for_day() {
            $date = str_replace( 'epl_', '', $_POST['date'] );

            $data['button_text'] = epl_nz( epl_get_setting( 'epl_event_options', 'epl_register_button_text' ), epl__( 'Register' ) );
            $data['date'] = date_i18n( get_option( 'date_format' ), $date );
            $data['events'] = $this->epl_util->get_events_for_day( $date );

            $r = $this->epl->load_view( 'widgets/advanced-cal/event-list', $data, true );

            return $this->epl_util->epl_response( array( 'html' => $r ) );
            die();
        }

        /*
         * for fullcalendar dynamic loading
         */


//for fullcalendar, these data come in through post
        function get_cal_dates( $args = null ) {


            /*  $args['taxonomy'] = sanitize_text_field( epl_get_element( 'epl_taxonomy', $filters ) );
              $args['location'] = sanitize_text_field( epl_get_element( 'epl_location', $filters ) );
              $args['org'] = sanitize_text_field( epl_get_element( 'epl_org', $filters ) );
              $args['event_id'] = intval( epl_get_element( 'event_id', $filters ) );
              $args['date_selector'] = intval( epl_get_element( 'date_selector', $filters ) );
              $args['show_past'] = intval( epl_get_element( 'show_past', $filters ) ); */

            return $this->epl->epl_util->get_days_for_fc( $args );
        }


        function load_date_selector_cal() {
            global $event_details;

            $data['event_dates'] = $this->get_cal_dates();

            $data['event_type'] = epl_get_element( '_epl_event_type', $event_details );
            $r = $this->epl->load_view( 'front/cart/date-selector-cal', $data, true );
            return $this->epl_util->epl_response( array( 'html' => $r ) );
        }


        function load_user_schedule_cal() {
            global $event_details;

            $data['event_dates'] = $this->get_cal_dates();

            $data['event_type'] = epl_get_element( '_epl_event_type', $event_details );
            $r = $this->epl->load_view( 'front/cart/date-selector-cal', $data, true );
            return $this->epl_util->epl_response( array( 'html' => $r ) );
        }


        function respond() {
            global $post, $event_list;



            extract( shortcode_atts( array(
                'display' => 'list',
                'search_form' => null,
                'search_fields' => null,
                'taxonomy' => null,
                'show_past' => null,
                            ), $this->shortcode_atts ) );

            $data['search_form'] = '';
            if ( $search_form ) {

                if ( $search_fields )
                    $search_fields = (strpos( $search_fields, ',' ) !== false ) ? explode( ',', $search_fields ) : array( $search_fields );


                $data['search_form'] = $this->ecm->event_search_box( $display, $search_fields, $this->shortcode_atts );
            }
            if ( isset( $this->shortcode_atts['display'] ) ) {

//return $this->epl->load_view( 'front/event-calendar', '', true );
            }


            if ( stripos( epl_get_element( 'display', $this->shortcode_atts ), 'regis_button' ) !== false ) {

                $event_id = intval( $this->shortcode_atts['event_id'] );
                //setup_event_details($event_id);

                return get_the_register_button( $event_id );
            }
            if ( stripos( epl_get_element( 'display', $this->shortcode_atts ), 'attendee-list' ) !== false ) {
                return $this->show_attendee_list( $this->shortcode_atts );
            }

            if ( epl_get_element( 'display', $this->shortcode_atts ) == 'user-bookings' ) {
                return $this->user_bookings( $this->shortcode_atts );
            }
            if ( epl_get_element( 'display', $this->shortcode_atts ) == 'user-bookings-cal' ) {
                return $this->user_bookings( $this->shortcode_atts, true );
            }

            if ( stripos( epl_get_element( 'display', $this->shortcode_atts ), 'check-in' ) !== false ) {
                return $this->check_in_page( $this->shortcode_atts );
            }
            if ( stripos( epl_get_element( 'display', $this->shortcode_atts ), 'list' ) !== false ) {
                
            }

            $this->ecm->events_list( $this->shortcode_atts );
            $data['event_list'] = $event_list;


            if ( stripos( epl_get_element( 'display', $this->shortcode_atts ), 'ui-map' ) !== false ) {

                return $this->epl->load_view( 'front/event-ui-map', '', true );
            }

//this stuff is used for the fullcalendar
            $data['taxonomy'] = (isset( $this->shortcode_atts['taxonomy'] )) ? $this->shortcode_atts['taxonomy'] : epl_get_element( 'epl_taxonomy', $_POST );
            $data['location'] = (isset( $this->shortcode_atts['location'] )) ? $this->shortcode_atts['location'] : epl_get_element( 'epl_location', $_POST );
            $data['org'] = (isset( $this->shortcode_atts['org'] )) ? $this->shortcode_atts['org'] : epl_get_element( 'epl_org', $_POST );
            $data['event_id'] = (isset( $this->shortcode_atts['event_id'] )) ? $this->shortcode_atts['event_id'] : epl_get_element( 'event_id', $_POST );
            $data['show_past'] = (isset( $this->shortcode_atts['show_past'] )) ? $this->shortcode_atts['show_past'] : epl_get_element( 'show_past', $_POST );
            $data['start'] = epl_get_date_timestamp( (isset( $this->shortcode_atts['start'] )) ? $this->shortcode_atts['start'] : epl_get_element( 'start', $_POST )  );
            $data['end'] = epl_get_date_timestamp( (isset( $this->shortcode_atts['end'] )) ? $this->shortcode_atts['end'] : epl_get_element( 'end', $_POST )  );
            $data['shortcode_atts'] = $this->shortcode_atts;

            if ( stripos( epl_get_element( 'display', $this->shortcode_atts ), 'calendar' ) !== false ) {
                $data['cal_dates'] = $this->get_cal_dates( $data );

                if ( !empty( $this->shortcode_atts['start_at_first_event'] ) ) {
                    global $first_event_date;
                    $data['shortcode_atts']['start_month'] = date_i18n( 'm', $first_event_date );
                }
            }
            $r = null;

            $r = $this->epl->load_template_file( 'event-list.php', true );

//template not found
            if ( is_null( $r ) || isset( $this->shortcode_atts['display'] ) ) {
                $r = $this->epl->load_view( "front/event-{$display}", $data, true );
            }
            else
                $r = $this->epl->load_template_file( 'event-list.php' );

            return $data['search_form'] . $r;
        }

        /*
         * add, delete, calc total ON CART
         */


        function process_cart_action() {

            $this->erm->_set_relevant_data( null, null, true );
            $r = $this->erm->_process_session_cart();


            if ( isset( $_REQUEST['epl_m'] ) )
                return $this->load_cart_in_modal( $_REQUEST['epl_m'] );

            if ( !$GLOBALS['epl_ajax'] ) {
                return $this->show_cart();
            }

            echo $this->epl_util->epl_response( array( 'html' => $r ) );
            die();
        }


        function event_list_search() {

            $this->shortcode_atts['display'] = esc_attr( epl_nz( epl_get_element( 'result_view', $_POST ), 'list' ) );
            $this->shortcode_atts['display_cols'] = esc_attr( epl_nz( epl_get_element( 'display_cols', $_POST ) ) );

            add_filter( 'epl_event_list_query_args', array( $this, '_epl_event_list_query_arg' ) );
            add_filter( 'epl_event_list_args', array( $this, '_epl_event_list_args' ) );
//add_filter( 'posts_clauses', array( $this, '_intercept_query_clauses' ), 20, 1 );

            $r = $this->respond();

            if ( !$GLOBALS['epl_ajax'] ) {
                return $this->respond();
            }
            echo $this->epl_util->epl_response( array( 'html' => $r ) );
            die();
        }


        function _intercept_query_clauses( $pieces ) {

            if ( current_user_can( 'manage_options' ) ) {
                
            }

            return $pieces;
        }

        /* arguments passed to event list wp_query */


        function _epl_event_list_query_arg( $args ) {

            $_filters = array(
                '_epl_event_location' => array(),
                '_epl_course' => array(),
                '_epl_course_level' => array(),
                '_epl_event_sex' => array(),
            );



            foreach ( $_filters as $_filter => $_options ) {

                if ( epl_get_element( $_filter, $_POST ) ) {

                    $_v = epl_get_element( $_filter, $_POST );

                    $args['meta_query'][] = array(
                        'key' => $_filter,
                        'value' => isset( $_options['func'] ) ? $_options['func']( $_v ) : $_v,
                        'compare' => epl_get_element( 'compare', $_options, '=' )
                    );
                }
            }

            if ( epl_get_element( '_epl_from_date', $_POST ) ) {

                $_f = epl_get_date_timestamp( epl_get_element( '_epl_from_date', $_POST ) );
                $_t = epl_get_element( '_epl_to_date', $_POST );

                $args['meta_query'][] = array(
                    'key' => '_q__epl_start_date',
                    'type' => 'numeric',
                    'value' => $_t ? array( $_f, epl_get_date_timestamp( $_t ) ) : $_f,
                    'compare' => $_t ? 'BETWEEN' : '>='
                );
            }

            return $args;
        }

        /* arguments passed to the event list function for processing before wp_query */


        function _epl_event_list_args( $args ) {

            $_filters = array(
                'taxonomy' => array(),
            );



            foreach ( $_filters as $_filter => $_options ) {

                if ( epl_get_element( $_filter, $_POST ) ) {

                    $_v = epl_get_element( $_filter, $_POST );

                    $args[$_filter] = $_v;
                }
            }


            return $args;
        }


        function widget_cal_next_prev() {

            $r = $this->epl_util->get_widget_cal( 1 );
            echo $this->epl_util->epl_response( array( 'html' => $r ) );
            die();
        }


        function setup_base_url() {

            $this->url = epl_get_url();
        }


        function check_for_waitlist() {



            if ( epl_is_waitlist_record() && !epl_is_waitlist_session_approved() ) {

                if ( !epl_is_valid_url_hash() )
                    return epl__( 'There seems to be something wrong with the url.  Please contact the website administrator.' );

                if ( !epl_is_waitlist_approved() )
                    return epl__( 'This waitlist has not been approved.' );

                if ( epl_is_waitlist_link_expired() )
                    return epl__( 'This link has expired.' );

                $_SESSION['__epl']['waitlist_approved'] = intval( $_GET['event_id'] );
                $this->erm->setup_current_data();

                wp_redirect( add_query_arg( array( 'epl_rid' => false, 'epl_wlh' => false, ), epl_get_url() ) );
                die();
            }

            return true;
        }


        function show_cart() {
            global $event_details;

            $wl_check = $this->check_for_waitlist();

            if ( $wl_check !== true ) {
                return $this->epl_util->epl_invoke_error( 1, $wl_check, false );
            }

            $this->erm->set_mode( 'edit' );
            $data['cart_data'] = $this->erm->show_cart();
            $data['mode'] = 'edit';

            $data['content'] = $this->epl->load_view( 'front/cart/cart', $data, true );


            $data = array_merge( $data, $this->set_next_step_vars( 'show_cart' ) );

            return $this->epl->load_view( 'front/cart-container', $data, true );
        }


        function regis_form() {
            global $epl_error, $event_details, $epl_is_waitlist_flow;

            if ( $this->erm->is_empty_cart() ) {

//return $this->epl_util->epl_invoke_error( 20, null, false );
            }

            //$this->ecm->setup_event_details( intval( $_REQUEST['event_id'] ) );

            $_ok = $this->erm->ok_to_proceed();

            if ( $_ok !== '' ) {

                if ( !epl_is_waitlist_flow() && (epl_is_ok_for_waitlist() && epl_waitlist_enough_spaces()) ) {
                    epl_waitlist_flow_trigger();
                }
                else
                    return $_ok;
            }

            $this->erm->_process_session_cart();
            //$this->erm->_set_relevant_data(null, null, true);
            $data['content'] = '';

            $this->regis_flow = epl_regis_flow();

// if ( $this->regis_flow <= 2 && $_POST ) {

            /* if ( !epl_has_all_req_user_fields() ) {
              $event_id = intval( epl_get_element( 'event_id', $_REQUEST ) );
              $url = get_the_register_button( $event_id, true );
              wp_redirect( $url );
              die();
              } */

//from the shopping cart
//}

            $data['mode'] = 'edit';

            if ( $this->regis_flow == 2 || $this->regis_flow == 10 ) {

                if ( $this->regis_flow == 2 )
                    $data['mode'] = 'overview';

                $this->erm->set_mode( $data['mode'] );

                $data['cart_data'] = $this->erm->show_cart();

                $data['content'] = $this->epl->load_view( 'front/cart/cart', $data, true );
            }

            $data = array_merge( $data, $this->set_next_step_vars( 'regis_form' ) );
            $this->erm->set_mode( 'edit' );
            $data['mode'] = 'edit';

            $data['content'] .= $this->erm->regis_form();

            /* if ( empty( $epl_error ) )
              $data['next_step_label'] = apply_filters( 'epl_regis_form_next_step_label', epl__( 'Next' ) );
             */





            return $this->epl->load_view( 'front/cart-container', $data, true );
        }


        function show_cart_overview( $next_step = null ) {


            if ( $this->erm->is_empty_cart() ) {

                return $this->epl_util->epl_invoke_error( 20, null, false );
            }
            else {
                $this->erm->set_mode( 'overview' );
                //$_totals = $this->erm->calculate_cart_totals();

                if ( $_POST ) {
                    $this->erm->_set_relevant_data( '_attendee_info', $_POST ); //from the regis form, add to session
                    $this->erm->add_registration_to_db( $_SESSION['__epl'] );
                    $post_ID = epl_get_element_m( 'post_ID', '__epl', $_SESSION, null );
                    EPL_db_model::get_instance()->reset_table_for_registration( $post_ID, get_current_user_id() );
                }
                else
                    $this->erm->_set_relevant_data( null, null, true );
                $data['error'] = $this->error;

                $data['mode'] = 'overview';
                $data['cart_data'] = $this->erm->show_cart();


                $data['content'] = $this->epl->load_view( 'front/cart/cart', $data, true );

                $data['next_step'] = $next_step;
                $data['prev_step_url'] = add_query_arg( 'epl_action', 'regis_form', $this->url );

                if ( is_null( $data['next_step'] ) )
                    $data['next_step'] = 'payment_page';

//find the payment type

                $gateway_info = $this->erm->get_gateway_info();

                $this->_setup_payment_type( $gateway_info );

                $data = array_merge( $data, $this->set_next_step_vars( 'show_cart_overview' ) );

                $data['content'] .= $this->erm->regis_form();

                $data = apply_filters( 'epl_show_cart_overview_data', $data );

                return $this->epl->load_view( 'front/cart-container', $data, true );
            }
        }


        function payment_page() {

            if ( $this->erm->is_empty_cart() ) {

                return $this->epl_util->epl_invoke_error( 20, null, false );
            }

            global $event_details;


            $this->erm->_set_relevant_data();

            $this->ecm->setup_event_details( $this->erm->get_current_event_id() );

            $flow = epl_regis_flow();


            if ( $flow == 2 || $flow == 10 ) {

                $this->erm->_set_relevant_data(); //from the regis form, add to session
                $this->erm->add_registration_to_db();
            }

            $this->erm->set_mode( 'overview' );
            $data['cart_data'] = $this->erm->show_cart();

            $gateway_info = $this->erm->get_gateway_info();

            $egm = $this->epl->load_model( 'epl-gateway-model' );

            $this->_setup_payment_type( $gateway_info );

            $pay_type = $gateway_info['_epl_pay_type'];

            $is_cc = ($gateway_info['_epl_pay_type'] == '_pp_pro' || $gateway_info['_epl_pay_type'] == '_auth_net_aim' || $gateway_info['_epl_pay_type'] == '_firstdata' || $gateway_info['_epl_pay_type'] == '_usa_epay' || $gateway_info['_epl_pay_type'] == '_qbmc') ? true : false;

//will be refactored
            if ( $pay_type == '_stripe' ) {
                $r = $egm->stripe_process();

                if ( $r === true ) {

                    return $this->thank_you_page( $add_to_db );
                }
                else {
                    $this->error = $r;

                    return $this->show_cart_overview();
                }
            }
            if ( $pay_type == '_pp_pro' ) {
                $r = $egm->paypal_pro_process();

                if ( $r === true ) {

                    return $this->thank_you_page( $add_to_db );
                }
                else {
                    $this->error = $r;

                    return $this->show_cart_overview();
                }
            }
            if ( $pay_type == '_pp_payflow' ) {
                $r = $egm->payflow_pro_process();

                if ( $r === true ) {

                    return $this->thank_you_page( $add_to_db );
                }
                else {
                    $this->error = $r;

                    return $this->show_cart_overview();
                }
            }
            elseif ( $pay_type == '_auth_net_aim' ) {
                $r = $egm->authnet_aim_process();

                if ( $r === true ) {
                    return $this->thank_you_page( $add_to_db );
                }
                else {
                    $this->error = $r;

                    return $this->show_cart_overview();
                }
            }
            elseif ( $pay_type == '_usa_epay' ) {

                $r = $egm->usa_epay_process();

                if ( $r === true ) {
                    return $this->thank_you_page( $add_to_db );
                }
                else {
                    $this->error = $r;

                    return $this->show_cart_overview();
                }
            }
            elseif ( $pay_type == '_firstdata' ) {
                $r = $egm->firstdata_process();

                if ( $r === true ) {
                    return $this->thank_you_page( $add_to_db );
                }
                else {
                    $this->error = $r;

                    return $this->show_cart_overview();
                }
            }
            elseif ( $pay_type == '_qbmc' ) {


                $r = $egm->qbmc_process();

                if ( $r === true ) {
                    return $this->thank_you_page( $add_to_db );
                }
                else {

                    $this->error = $r;

                    return $this->show_cart_overview();
                }
            }
            elseif ( $pay_type == '_payson' ) {


                $r = $egm->payson_process();


                if ( $r !== false ) {
                    header( "Location: " . PaysonApi::get_instance()->getForwardPayUrl( $r ) );
                }
                else {

                    $this->error = '<div class="epl_error">ERROR: Please notify the website administrator.</div>';


                    return $this->regis_form();
                }
            }
            elseif ( $pay_type == '_moneris' ) {


                $r = $egm->moneris_process();

                if ( $r === true ) {
                    return $this->thank_you_page( false );
                }
                else {

                    $this->error = $r;

                    return $this->show_cart_overview();
                }
            }
            else
                $egm->_express_checkout_redirect();
        }


        function thank_you_page( $add_to_db = true ) {
            global $cart_totals, $wpdb;
            if ( $this->erm->is_empty_cart() ) {

                return $this->epl_util->epl_invoke_error( 20, null, false );
            }

            global $event_details;



            $this->erm->_set_relevant_data();

            if ( epl_is_waitlist_flow() )
                $this->erm->add_registration_to_db();
            // echo "<pre class='prettyprint'>" . __LINE__ . "> " . print_r($_SESSION, true). "</pre>";



            $this->ecm->setup_event_details( $this->erm->get_current_event_id() );




            if ( epl_regis_flow() == 2 && $add_to_db ) {
                $this->erm->add_registration_to_db();
            }

            if ( epl_regis_flow() == 10 ) {

                if ( $this->erm->ok_to_proceed( true ) !== true )
                    return $this->erm->ok_to_proceed;
            }



            $post_ID = epl_get_element_m( 'post_ID', '__epl', $_SESSION, null );

            if ( !$post_ID ) {

                $regis_key = $_SESSION['__epl']['_regis_id'];

                $post_ID = $wpdb->get_var(
                        $wpdb->prepare( "SELECT ID FROM {$wpdb->posts}
                 WHERE post_status ='publish' 
                 AND post_type='epl_registration' 
                 AND post_title=%s
                        ORDER BY ID DESC
                        LIMIT 1", $regis_key )
                );
                $_SESSION['__epl']['post_ID'] = $post_ID;
            }

            $this->regis_id = $post_ID;
            $this->ecm->setup_regis_details( $post_ID );
            $this->erm->set_mode( 'overview' );

            $_totals = $this->erm->calculate_cart_totals();

            $grand_total = $cart_totals['money_totals']['grand_total'];
            $data['cart_data'] = $this->erm->show_cart();


            $data['regis_form'] = $this->erm->regis_form( null, false, false, false );

            $gateway_info = $this->erm->get_gateway_info();




            $data['post_ID'] = $post_ID;
            $data['_epl_regis_status'] = 1;
            $data['_epl_payment_method'] = (epl_is_free_event() || epl_is_zero_total()) ? '1' : $gateway_info['_epl_pay_type'];

            $data['payment_instructions'] = '';


            if ( !epl_is_waitlist_flow() && (epl_is_free_event() || epl_is_zero_total()) ) {

                $data['_epl_regis_status'] = 5;
                $data['_epl_grand_total'] = '0';
                $data['_epl_payment_amount'] = '0';
                $data['_epl_payment_date'] = '';
                $data['_epl_payment_method'] = '1';
                $data['_epl_transaction_id'] = '';

                $this->erm->update_payment_data( $data );
            }
            elseif ( $this->erm->has_selected_offline_payment() || epl_is_waitlist_flow() ) {

                $data['_epl_regis_status'] = epl_is_waitlist_flow() ? 20 : 2;
                $data['_epl_payment_method'] = epl_is_waitlist_flow() ? '' : $gateway_info['_epl_pay_type'];
                $data['_epl_prediscount_total'] = epl_get_element( 'pre_discount_total', $cart_totals['money_totals'], 0 );
                $data['_epl_discount_amount'] = epl_get_element( 'discount_amount', $cart_totals['money_totals'], 0 );
                $data['_epl_grand_total'] = $grand_total;
                $data['_epl_balance_due'] = $grand_total;
                $data['_epl_transaction_id'] = '';

                $data['payment_instructions'] = $this->epl->load_view( 'front/registration/regis-payment-instr', array( 'gateway_info' => $gateway_info ), true );

                $this->erm->update_payment_data( $data );
            }

            $this->ecm->setup_regis_details( $post_ID );

            $data['payment_details'] = $this->epl->load_view( 'front/registration/regis-payment-details', $data, true );
            $data = apply_filters( 'epl_update_payment_data_thank_you_page', $data );


            if ( $this->erm->has_selected_offline_payment() ) {
                $data['payment_instructions'] = epl_get_element( '_epl_check_instructions', $gateway_info );
            }

            $data['mode'] = 'overview';
            $data['tracking_code'] = 1;
            $data['overview'] = $this->epl->load_view( 'front/registration/regis-thank-you-page', $data, true );



            $this->epl_util->regis_id = $post_ID;

            if ( epl_is_addon_active( 'ASDFAWEEFADSF' ) )
                epl_mailchimp_subscribe();


            do_action( 'epl_efc__thank_you_page__before_session_destroy', $post_ID );

            $new_user = $this->erm->maybe_add_new_user();

            if ( $new_user !== false ) {
                $this->erm->assign_event_to_user( $post_ID, $new_user );
            }

            $this->epl->load_model( 'epl-db-model' );
            EPL_db_model::get_instance()->reset_table_for_registration( $post_ID, get_current_user_id() );
            $this->epl_util->send_confirmation_email( $data );

            $url = apply_filters( 'epl_efc__thank_you_page__redirect_url', '' );

            $_SESSION['__epl'] = array();
            unset( $_SESSION['__epl'] );
            @session_destroy();
            $_SESSION = array();


            epl_delete_transient();

            if ( $url != '' || $this->epl->locate_template( 'single-epl_registration.php' ) ) {

                $url = $url != '' ? $url : get_permalink( $this->regis_id );
                $url_params = array();

                if ( epl_get_regis_setting( 'epl_tracking_code' ) ) {

                    $url_params['cnv_tr'] = 1;
                }

                $url_params['epl_token'] = epl_get_token();

                $url_params = apply_filters( 'epl_efc__thank_you_page__url_params', $url_params );

                $url = add_query_arg( $url_params, $url );

                wp_redirect( $url, 301 );
                die();
            }

            return $data['overview'];
        }


        function _exp_checkout_payment_cancel() {

            return $this->show_cart_overview();
        }


        function _payson_success() {

            return $this->thank_you_page();
        }


        function _payson_cancel() {

            return $this->regis_form();
        }


        function _payson_ipn() {

            $egp = $this->epl->load_model( 'epl-gateway-model' );
            return $egp->payson_process( true );
        }


        function _exp_checkout_payment_success() {
            global $gateway_info;

            $egp = $this->epl->load_model( 'epl-gateway-model' );

            if ( $egp->_exp_checkout_payment_success() ) {

                $this->erm->set_mode( 'overview' );
                $this->erm->_set_relevant_data( null, null, true );
                $data['message'] = epl__( "Please review and finalize your payment to complete the registration." );
                $data['message'] = epl_get_element( '_epl_pre_confirm_message', $gateway_info, $data['message'] );
                $data['cart_data'] = $this->erm->show_cart();
                $data['mode'] = 'overview';
                $data['content'] = $this->epl->load_view( 'front/cart/cart', $data, true );


                $data['content'] .= $this->erm->regis_form();


                $data['form_action'] = add_query_arg( 'epl_action', '_exp_checkout_do_payment', $this->url );

                $data['next_step'] = '_exp_checkout_do_payment';
                $data['next_step_label'] = 'Confirm Payment and Finish';

                return $this->epl->load_view( 'front/cart-container', $data, true );
            }
            else {
                echo "Sorry, something must have gone wrong.  Please notify the site administrator.";
            }
        }


        function _exp_checkout_do_payment() {
            $this->erm->set_mode( 'overview' );
            $this->erm->_set_relevant_data( null, null, true );
            $data['cart_data'] = $this->erm->show_cart();

            $egp = $this->epl->load_model( 'epl-gateway-model' );


            $r = $egp->_exp_checkout_do_payment();

            if ( $r === true ) {

                return $this->thank_you_page( false );
            }
            else {
                echo "Sorry, something must have gone wrong.  Please notify the site administrator.";
            }
        }


        function validate_data() {
            $v = $_SESSION['events_planner']['POST_EVENT_VARS'];

            foreach ( ( array ) $v['epl_start_date'] as $event_id => $event_dates ) {
                
            }
        }


        function _setup_payment_type( $gateway_info ) {

            if ( epl_is_empty_array( $gateway_info ) )
                $gateway_info = $this->erm->get_gateway_info();

            $this->pay_type = $gateway_info['_epl_pay_type'];

            $this->is_cc = $this->erm->has_selected_cc_payment();
        }


        function set_next_step_vars( $current_step = null ) {

            //$bt = debug_backtrace();
            //echo "<pre class='prettyprint'>" . __LINE__ . "> " . print_r($bt[0]['line'], true). "</pre>"; 
            //echo "<pre class='prettyprint'>" . __LINE__ . "> " . print_r($bt[0]['function'], true). "</pre>"; 


            /*
             * - if on regis form and offline or free, thank you
             * - if regis form and cc or pp, overview
             * - if overview and cc, show card form, complete
             * - if overview and pp, continue
             * - if overview and offline, complete
             *
             * - if epl_regis_flow() and regis form -> payment -> complete
             * - if epl_regis_flow() and regis form and free of offline -> complete
             */

            global $epl_current_step;

            $regis_flow_steps = array(
                1 => 'show_cart',
                5 => 'regis_form',
                10 => 'show_cart_overview',
                30 => 'redirect',
                100 => 'thank_you_page'
            );

            $this->steps = array(
                'show_cart' => array(
                    1 => array( 'next' => 'regis_form', 'next_label' => epl__( 'Attendee Information' ) ),
                    2 => array( 'next' => 'regis_form', 'next_label' => epl__( 'Attendee Information' ) ),
                //10 => array( 'prev' => 'show_cart', 'next' => 'thank_you_page' ),
                ),
                'regis_form' => array(
                    1 => array( 'prev' => 'show_cart', 'next' => 'show_cart_overview', 'next_label' => epl__( 'Overview' ) ),
                    2 => array( 'prev' => 'show_cart', 'next' => 'thank_you_page', 'next_label' => epl__( 'Confirm and Complete' ) ),
                    10 => array( 'next' => 'thank_you_page', 'next_label' => epl__( 'Confirm and Complete' ) ),
                ),
                'show_cart_overview' => array(
                    1 => array( 'prev' => 'regis_form', 'next' => 'thank_you_page', 'next_label' => epl__( 'Confirm and Complete' ) ),
                    2 => array( 'prev' => 'regis_form', 'next' => 'show_cart_overview', 'next_label' => epl__( 'Confirm and Complete' ) ),
                    10 => array( 'prev' => 'show_cart', 'next' => 'thank_you_page' ),
                ),
                'payment_page' => array(
                    1 => array( 'prev' => 'regis_form', 'next' => 'payment_page', 'next_label' => epl__( 'Overview' ) ),
                    2 => array( 'prev' => 'regis_form', 'next' => 'payment_page', 'next_label' => epl__( 'Confirm and Complete' ) ),
                    10 => array( 'next' => 'thank_you_page', 'next_label' => epl__( 'Confirm and Complete' ) ),
                ),
            );

            $flow = epl_regis_flow();

            $this->step_data = array();

            $gateway_info = $this->erm->get_gateway_info();

            $url_vars_next = $url_vars_prev = array(
                'epl_action' => $this->steps[$current_step][$flow]['next'],
                'cart_action' => false,
                '_date_id' => false
            );


            $url_vars_prev['epl_action'] = epl_get_element( 'prev', $this->steps[$current_step][$flow] );

            $this->step_data['form_action'] = add_query_arg( $url_vars_next, $this->url );

            $gw_check = false;

            if ( $flow == 1 && $epl_current_step == 'show_cart_overview' )
                $gw_check = true;
            elseif ( ($flow == 2 || $flow == 10) && ($epl_current_step == 'regis_form') )
                $gw_check = true;

            if ( $epl_current_step != 'process_cart_action' && $epl_current_step != 'show_cart' )
                $this->step_data['prev_step_url'] = add_query_arg( $url_vars_prev, $this->url );


            $this->step_data['next_step'] = $this->steps[$current_step][$flow]['next'];
            $this->step_data['next_step_label'] = $this->steps[$current_step][$flow]['next_label'];


            if ( (epl_is_free_event() || (epl_regis_flow() < 10 && epl_is_zero_total()) || $this->erm->has_selected_offline_payment()) || (epl_is_waitlist_flow() && !epl_is_waitlist_approved()) ) {

                $this->step_data['next_step_label'] = apply_filters( 'epl_show_cart_overview_free_next_step_label', $this->steps[$current_step][$flow]['next_label'] );
            }
            elseif ( epl_get_element( '_epl_pay_type', $gateway_info ) == '_pp_exp' && $gw_check ) {

                $url_vars_next = array(
                    'epl_action' => 'payment_page',
                    'cart_action' => false,
                    '_date_id' => false
                );

                $url_vars_prev['epl_action'] = $this->steps[$current_step][$flow]['prev'];

                $this->step_data['form_action'] = add_query_arg( $url_vars_next, $this->url );


                $this->step_data['next_step_label'] = apply_filters( 'epl_show_cart_overview_pp_exp_next_step_label', epl__( 'Confirm and Continue to PayPal' ) );
            }
            elseif ( epl_get_element( '_epl_pay_type', $gateway_info ) == '_payson' && $gw_check ) {

                $url_vars_next = array(
                    'epl_action' => 'payment_page',
                    'cart_action' => false,
                    '_date_id' => false
                );

                $url_vars_prev['epl_action'] = $this->steps[$current_step][$flow]['prev'];

                $this->step_data['form_action'] = add_query_arg( $url_vars_next, $this->url );


                $this->step_data['next_step_label'] = apply_filters( 'epl_show_cart_overview_pp_exp_next_step_label', epl__( 'Continue to Pay' ) );
            }
            elseif ( epl_get_element( '_epl_pay_type', $gateway_info ) == '_moneris' && $gw_check ) {

                $this->step_data['next_step_label'] = apply_filters( 'epl_show_cart_overview_pp_exp_next_step_label', epl__( 'Continue to Pay' ) );
                $us_version = (epl_get_element( '_epl_moneris_country', $gateway_info, 'ca' ) == 'usa');

                $this->step_data['form_action'] = 'https://www3.moneris.com/HPPDP/index.php';
                if ( $gateway_info['_epl_sandbox'] == 10 )
                    $this->step_data['form_action'] = 'https://esqa.moneris.com/HPPDP/index.php';

                if ( $us_version ) {
                    $this->step_data['form_action'] = 'https://esplus.moneris.com/DPHPP/index.php';
                    if ( $gateway_info['_epl_sandbox'] == 10 )
                        $this->step_data['form_action'] = 'https://esplusqa.moneris.com/DPHPP/index.php';
                    $this->egm->setup_moneris_form_USA();
                } else {
                    $this->egm->setup_moneris_form();
                }
            }
            elseif ( epl_get_element( '_epl_pay_type', $gateway_info ) == '_auth_net_sim' && $gw_check ) {

                $this->step_data['next_step_label'] = apply_filters( 'epl_show_cart_overview_pp_exp_next_step_label', epl__( 'Continue to Pay' ) );

                $this->step_data['form_action'] = 'https://secure.authorize.net/gateway/transact.dll';
                if ( $gateway_info['_epl_sandbox'] == 10 )
                    $this->step_data['form_action'] = 'https://test.authorize.net/gateway/transact.dll';

                $this->egm->setup_authnet_sim_form();
            }
            elseif ( $this->erm->has_selected_cc_payment() && ($gw_check || $this->error) ) {

                $url_vars_next = array(
                    'epl_action' => 'payment_page',
                    'cart_action' => false,
                    '_date_id' => false
                );


                $this->step_data['form_action'] = add_query_arg( $url_vars_next, $this->url );

                $this->step_data['next_step_label'] = apply_filters( 'epl_show_cart_overview_cc_next_step_label', $this->steps[$current_step][$flow]['next_label'] );
            }

            $this->step_data = apply_filters( 'epl_front__set_next_step_vars__final', $this->step_data, $flow );
            return $this->step_data;
        }

    }

}
