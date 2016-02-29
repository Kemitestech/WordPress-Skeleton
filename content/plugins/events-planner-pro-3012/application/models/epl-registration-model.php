<?php

class EPL_registration_model extends EPL_model {

    public $regis_id;
    public $data = null;
    public $mode;
    public $data_source;
    public $dest = 'front';
    private static $instance;


    function __construct() {
        parent::__construct();

        global $event_details, $epl_on_admin;
        $event_details = array();

        $this->on_admin = false;

        //$this->setup_date_source();

        $this->ecm = $this->epl->load_model( 'epl-common-model' );
        $this->edm = $this->epl->load_model( 'epl-discount-model' );
        $this->ercm = $this->epl->load_model( 'epl-recurrence-model' );
        $this->edbm = $this->epl->load_model( 'epl-db-model' );
        $this->mode = 'edit';
        $this->flow_mode = epl_get_element( 'epl_r_m', $_REQUEST, 'n' );
        $this->overview_trigger = null;
        $this->ok_to_proceed = true;
        $this->waitlist_flow = false;
        $this->extra_response_vals = null;
        $this->discountable_step = false;
        //$this->_is_cart_expired();
        $this->event_id = intval( epl_get_element( 'event_id', $_REQUEST ) );
        $this->setup_current_data();
        self::$instance = $this;
    }


    function __destruct() {

        $this->refresh_data();
    }


    public static function get_instance() {
        if ( !self::$instance ) {

            self::$instance = new EPL_registration_model;
        }

        return self::$instance;
    }


    function setup_current_data( $current_data = null ) {

        if ( is_null( $current_data ) && !epl_is_single() ) {
            //$this->current_data = epl_get_cart_cookie_val();
            $this->current_data = $_SESSION['__epl'];
        }
        else {
            $this->current_data = $current_data['__epl'];
            $this->full_data = $current_data;
        }


        $this->_start_cart_session();

        $this->dest = $this->on_admin ? 'admin' : $this->dest;


        return $this;
    }

    /*
     * refactor
     */


    function _is_cart_expired() {

        if ( isset( $this->current_data['_cart_time'] ) ) {
            $cart_time = $this->current_data['_cart_time'];
            $now = EPL_TIME;

            $cart_active_time = ($now - $cart_time) / 60;
            echo $cart_active_time;

            if ( $cart_active_time >= 1 ) {
                $this->regis_id = $this->current_data['_regis_id'];
                $this->current_data['_cart_time'] = time();
                $this->current_data[$this->regis_id] = array();

                $redir = add_query_arg( 'epl_action', 'process_cart_action', epl_get_url() );
                wp_redirect( $redir );
            }
        }
    }


    function _start_cart_session( $start_new = false ) {

        if ( $start_new ) {
            unset( $this->current_data );
            $this->current_data = array();
        }

        if ( isset( $this->current_data['_regis_id'] ) ) {
            $this->regis_id = $this->current_data['_regis_id'];
        }
        else {
            $this->regis_id = strtoupper( $this->epl_util->make_unique_id( epl_nz( epl_get_regis_setting( 'epl_regis_id_length' ), 10 ) ) );
            $this->current_data['_regis_id'] = $this->regis_id;
            $this->current_data['_cart_time'] = time();
        }
    }


    function refresh_data( $clear_session = false ) {

        if ( epl_is_single() )
            return;

        //if ( !$this->on_admin || (epl_is_waitlist_record() && epl_is_valid_waitlist_hash()) )
        if ( !$this->on_admin ) {
            $_SESSION['__epl'] = (!$clear_session && is_array( $_SESSION['__epl'] )) ? $_SESSION['__epl'] : array();
            $_SESSION['__epl'] = array_merge( ( array ) $_SESSION['__epl'], ( array ) $this->current_data );
        }
    }


    function setup_session_data( $data = null ) {

        if ( is_null( $data ) )
            return;
        $this->current_data = $data;
    }


    function get_regis_id() {
        return $this->regis_id;
    }


    function _process_session_cart( $act = null ) {
        global $event_details;


        $defaults = array(
            'cart_action' => 'add',
            'event_id' => null
        );
        $args = array_intersect_key( $_REQUEST, $defaults );

        $args = $this->epl_util->clean_input( $args );

        $act = epl_get_element( 'cart_action', $args, null );
        if ( $act == 'add' || $act == 'delete' ) {
            if ( !isset( $args['event_id'] ) )
                $args['event_id'] = epl_get_element( 'event_id', $_REQUEST );

            $this->_event_in_session( $args );

            if ( $act == 'delete' && epl_get_element( 'caller', $_REQUEST ) == 'summary' ) {
                $this->discountable_step = true;
                return $this->get_the_cart_totals( false, true );
            }
        }
        elseif ( epl_get_element( 'cart_action', $args ) == 'calculate_total_due' ) {

            $this->discountable_step = true;

            //get the values in the session
            $events_in_cart = ( array ) $this->get_events_in_cart();

            //FOR NOW, one event, so we need the event id
            foreach ( $events_in_cart as $event_id => $event_date ) {
                //set the global event_details 
                $this->ecm->setup_event_details( $event_id );
                //$this->event_snapshot();
            }
            $this->_set_relevant_data( null, null, true );
            $this->adjust_for_sc_ok();
            //set the dates section with the cart dates, times, prices
            //$this->_set_relevant_data( '_dates', $_POST );
            //set the global capacity and current attendee information
            $this->set_event_capacity_info();

            $r = $this->get_the_totals();
            $this->get_the_cart_totals();
            $this->refresh_data();

            return $r;
        }
        //return $this->epl->epl_util->view_cart_link();
    }


    /**
     * Adds or removes event in the session.
     *
     * @since 1.0.0
     * @param int $var
     * @return string
     */
    function _event_in_session( $args ) {
        global $event_details;
        $action = $args['cart_action'];
        $event_id = $args['event_id'];

        if ( !epl_sc_is_enabled() && !isset( $this->current_data[$this->regis_id]['_events'][$event_id] ) ) {
            $this->current_data[$this->regis_id] = array();
        }


        if ( !is_null( $event_id ) ) {
            if ( ($action == 'add' && !isset( $this->current_data[$this->regis_id]['_events'][$event_id] ) ) ) {
                $this->current_data[$this->regis_id]['_events'][$event_id] = array();
                epl_set_cart_cookie();
            }
            elseif ( $action == 'delete' ) {
                unset( $this->current_data[$this->regis_id]['_events'][$event_id] );
                unset( $this->current_data[$this->regis_id]['_dates']['_epl_start_date'][$event_id] );
                unset( $this->current_data[$this->regis_id]['_dates']['_epl_start_time'][$event_id] );
                unset( $this->current_data[$this->regis_id]['_dates']['_att_quantity'][$event_id] );

                if ( empty( $this->current_data[$this->regis_id]['_events'] ) )
                    epl_set_cart_cookie( 'del' );
            }
        }
        $this->adjust_for_sc_ok();
        $this->refresh_data();
        epl_do_messages();
        return 1;
    }


    function adjust_for_sc_ok() {
        $num_events = count( $this->current_data[$this->regis_id]['_events'] );
        if ( $num_events <= 1 )
            return;

        global $event_details;

        $event_id = epl_get_element( 'event_id', $_REQUEST );
        setup_event_details( $event_id );
        $exclude_from_sc = (epl_get_element( 'epl_exclude_from_sc', $event_details, 0 ) == 10);

        foreach ( $this->current_data[$this->regis_id]['_events'] as $_event_id => $event_data ) {
            setup_event_details( $_event_id );

            if ( $event_id != $_event_id && ($exclude_from_sc || epl_get_element( 'epl_exclude_from_sc', $event_details ) == 10) ) {
                unset( $this->current_data[$this->regis_id]['_events'][$_event_id] );
                unset( $this->current_data[$this->regis_id]['_dates']['_epl_start_date'][$_event_id] );
                unset( $this->current_data[$this->regis_id]['_dates']['_epl_start_time'][$_event_id] );
                unset( $this->current_data[$this->regis_id]['_dates']['_att_quantity'][$_event_id] );
            }
        }
    }


    function set_mode( $mode = 'edit' ) {

        $this->mode = $mode;

        if ( $this->mode == 'overview' ) {
            $this->overview_trigger = array();
            $this->overview_trigger['overview'] = 1;
        }

        return $this;
    }


    /**
     * Display the cart for the user or the admin to select the dates, times and prices
     *
     * @since 1.0.0
     * @param int $var
     * @return string
     */
    function show_cart( $values = null, $_event_id = null, $section = true ) {

        do_action( 'epl_show_cart', $_REQUEST );

        if ( is_null( $values ) )
            $events_in_cart = $this->get_cart_values( '_events' );
        else
            $events_in_cart = $values['_events'];
        if ( empty( $events_in_cart ) )
            return $this->epl_util->epl_invoke_error( 20 );

        $events_in_cart = $this->epl_util->clean_output( $events_in_cart );

        global $event_details, $multi_time, $multi_price, $capacity, $current_att_count, $cart_totals;



        $r = array();
        foreach ( $events_in_cart as $event_id => $event_date ) {

            if ( $event_id != '' || !is_null( $_event_id ) && $_event_id != $event_id ) {

                setup_event_details( $event_id );

                $this->set_event_capacity_info();

                //$this->event_snapshot($event_id);
                // echo "<pre class='prettyprint'>" . __LINE__ . "> " . print_r($event_details, true). "</pre>";       
                if ( $event_details['post_status'] == 'trash' ) {
                    $r['message'] = epl__( 'You have reached this page in error.' );
                }

                $r['available_spaces'][$event_id] = '';

                //if ( $event_details['_epl_event_available_space_display'] )
                if ( epl_get_element( '_epl_event_available_space_display', $event_details ) != 0 )
                    $r['available_spaces'][$event_id] = $this->available_spaces_table( $event_id );

                $multi_time = (isset( $event_details['_epl_multi_time_select'] ) && $event_details['_epl_multi_time_select'] == 10);
                $multi_price = (isset( $event_details['_epl_multi_price_select'] ) && $event_details['_epl_multi_price_select'] == 10);


                $r['cart_items'][$event_id]['title'] = get_the_title( $event_id );
                $r['cart_items'][$event_id]['event_type'] = $event_details['_epl_event_type'];


                $r['cart_items'][$event_id]['event_dates'] = $this->get_the_dates(); //= $this->epl_util->create_element( $epl_fields );

                $r['cart_items'][$event_id]['event_time_and_prices'] = $this->get_time_and_prices_for_cart();

                //$r['cart_items'][$event_id]['section'] = $this->epl->load_view( 'front/cart/date-selector-cal.php', null, true );
                $r['cart_items'][$event_id]['show_date_selector_cal'] = (epl_get_element( '_epl_event_type', $event_details, 5 ) < 7 ? epl_get_element( '_epl_enable_front_date_selector_cal', $event_details, 0 ) : 0);

                $r['cart_items'][$event_id]['cart_totals'] = $this->get_the_totals( $event_id );
            }
        }

        $r['view_mode'] = $this->mode;
        $r['discount_field'] = $this->get_discount_field();
        $r['donation_field'] = $this->get_donation_field();
        $r['pay_options'] = $this->get_payment_options();

        $r['cart_grand_totals'] = $this->get_the_cart_totals();
        $r['cart_totals'] = $cart_totals;




        $r['fc_cal'] = $this->epl->load_view( 'front/cart/date-selector-cal.php', null, true );

        if ( epl_admin_override() ) {

            $field = array(
                'input_type' => 'text',
                'input_name' => "alt_total",
                'class' => 'epl_w80',
                'default_value' => '',
                'data_type' => 'float',
                'value' => epl_get_element_m( 'alt_total', $this->regis_id, $this->get_current_data(), '' ),
            );
            $field += ( array ) $this->overview_trigger;
            $f = $this->epl_util->create_element( $field );
            $r['alt_total'] = $f['field'];

            $r['show_date_selector_cal'] = (epl_get_regis_setting( 'epl_enable_admin_override_cal' ) == 10);
        }

        return $r;
    }


    function get_waitlist_trigger_fields() {

        if ( epl_get_element( 'epl_rid', $_GET ) ) {
            $field = array(
                'input_type' => 'text',
                'input_name' => "epl_rid",
                'value' => intval( epl_get_element( 'epl_rid', $_GET ) ),
            );

            $f = $this->epl_util->create_element( $field );

            $r = $f['field'];

            $field = array(
                'input_type' => 'text',
                'input_name' => "epl_wlh",
                'value' => epl_get_element( 'epl_wlh', $_GET ),
            );

            $f = $this->epl_util->create_element( $field );

            return $r . $f['field'];
        }
        return null;
    }


    function get_discount_field() {
        global $event_details;

        $event_id = $event_details['ID'];
        if ( epl_regis_plugin_version() >= 1.4 )
            $event_id = 0;

        $value = epl_get_element_m( $event_id, '_epl_discount_code', epl_get_element( '_dates', $this->current_data[$this->regis_id], array() ), '' );


        $epl_fields = array(
            'input_type' => 'text',
            'input_name' => "_epl_discount_code[]",
            'value' => $value,
            'style' => 'float:right;',
        );

        if ( epl_regis_flow() != 10 && !EPL_IS_ADMIN )
            $epl_fields += ( array ) $this->overview_trigger;
        $data['date'] = $this->epl_util->create_element( $epl_fields );

        return $data['date']['field'];
    }


    function get_donation_field() {
        global $event_details;
        $value = epl_get_element_m( '_epl_donation_amount', $this->regis_id, $this->current_data );

        $epl_fields = array(
            'input_type' => 'text',
            'input_name' => "_epl_donation_amount",
            'value' => $value ? abs( $value ) : $value,
            'style' => 'float:right;',
        );

        if ( epl_regis_flow() != 10 && !EPL_IS_ADMIN )
            $epl_fields += ( array ) $this->overview_trigger;
        $data['date'] = $this->epl_util->create_element( $epl_fields );

        return $data['date']['field'];
    }


    function get_the_totals( $_event_id = null, $cart_grand_totals = true ) {
        global $event_details, $cart_totals, $event_totals;

        //echo "<pre class='prettyprint'>" . __LINE__ . "> " . print_r($event_details, true). "</pre>";
        $totals = $this->calculate_cart_totals( true );

        //echo "<pre class='prettyprint'>" . __LINE__ . "> " . print_r($totals, true). "</pre>";
        //if ( $data[$event_id]['money_totals']['grand_total'] == 0 )
        //     $this->epl->epl_util->set_response_param( 'hide_payment_choices', true );

        $events_in_cart = ( array ) $this->get_events_in_cart();

        $r = array();
        $data = array( 'money_totals' => array() );

        foreach ( $events_in_cart as $event_id => $totals ) {
            $data['money_totals'] = epl_get_element( 'money_totals', $totals, array() );
            $data['mode'] = $this->mode;

            $ok = $this->ok_to_proceed( false, $event_id );

            if ( $ok !== '' )
                $r[$event_id] = $ok;
            else {

                $r[$event_id] = $this->epl->load_view( 'front/cart/cart-totals', $data, true );
            }
        }


        $data['individual_event_totals'] = $data;
        $data['money_totals'] = $cart_totals;
        // $cart_grand_totals = $this->epl->load_view('front/cart/cart-grand-totals', $data, true);
        //$this->epl->epl_util->set_response_param( 'cart_grand_totals', $cart_grand_totals );

        if ( !is_null( $_event_id ) )
            return $r[$_event_id];
        return $r;
    }


    function get_the_cart_totals( $event_list = true, $ck_out_button = false ) {

        global $event_details, $cart_totals, $event_totals;
        $data['ck_out_button'] = $ck_out_button;
        if ( epl_get_element( 'from_modal', $_REQUEST ) == 1 || epl_get_element( 'caller', $_REQUEST ) == 'summary' || epl_get_element( 'epl_m', $_REQUEST ) == 2 ) {
            $data['ck_out_button'] = true;
            $this->epl->epl_util->set_response_param( 'show_footer_total', 1 );
        }

        $events_in_cart = ( array ) $this->get_events_in_cart();

        if ( epl_is_empty_array( $events_in_cart ) )
            return null;
        if ( $event_list === true || epl_get_element( 'caller', $_REQUEST ) == 'summary' ) {

            $this->calculate_cart_totals( true );
            $data['events'] = $events_in_cart;

            $data['cart_event_list'] = $this->epl->load_view( 'front/cart/cart-grand-totals-events-list', $data, true );
        }
        //echo "<pre class='prettyprint'>" . __LINE__ . "> " . print_r($cart_totals, true). "</pre>";
        $data['money_totals'] = $cart_totals['money_totals'];
        $data['flow_mode'] = $this->flow_mode;
        $data['mode'] = $this->mode;
        $cart_grand_totals = $this->epl->load_view( 'front/cart/cart-grand-totals', $data, true );
        $this->epl->epl_util->set_response_param( 'cart_grand_totals', $cart_grand_totals );
        //echo "<pre class='prettyprint'>" . __LINE__ . "> " . print_r($cart_grand_totals, true). "</pre>";
        return $cart_grand_totals;
    }

    /*
     * calculate totals in the cart.  using this method to apply a filter
     * 
     */


    function calculate_cart_totals( $refresh = true, $refresh_curr_data = true ) {
        $t = $this->calculate_cart_totals_process( $refresh, $refresh_curr_data );

        $this->epl->epl_util->set_response_param( 'num_events_in_cart', epl_get_num_events_in_cart() );
        
        return apply_filters( 'epl__erm__calculate_cart_totals', $t );
    }


    function calculate_cart_totals_process( $refresh = true, $refresh_curr_data = true ) {

        global $event_details, $epl_current_step, $event_totals, $cart_totals, $hide_offline_payment_options;
        //$db = debug_backtrace();
        //echo "<pre class='prettyprint'>" . __LINE__ . "> calculate_cart_totals " . print_r( $db[0]['line'] . '>>' . $db[0]['file'], true ) . "</pre>";

        if ( !$refresh && !epl_is_empty_array( $event_totals ) )
            return $event_totals;


        if ( !$refresh && ($data = wp_cache_get( 'cart_totals_' . $this->get_current_event_id() )) !== false ) {
            $event_totals = $data;
            return $data;
        }

        $_discount_steps = array( 'process_cart_action', 'regis_form' );
        $is_discountable_step = in_array( $epl_current_step, $_discount_steps );
        //need this check for waitlist record otherwise the whole cart
        //total will be assigned to the waitlist registration
        //if uncomment this line, discount will get overwritten in show cart overview
        //if ( !$refresh ) {

        if ( (!in_array( $epl_current_step, $_discount_steps ) || (in_array( $epl_current_step, $_discount_steps ) && !$_POST)) && isset( $this->current_data[$this->regis_id]['cart_totals']['money_totals'] ) ) {

            $data = $this->current_data[$this->regis_id]['_events'][$this->get_current_event_id()];
            $cart_totals = $this->current_data[$this->regis_id]['cart_totals'];

            wp_cache_set( 'cart_totals', $cart_totals );
            $event_totals = $data;

            return $cart_totals;
        }
        //}

        $this->discountable_step = true;
        $this->ecm->setup_event_details( $this->get_current_event_id() );


        $events = ( array ) $this->get_events_in_cart();

        if ( empty( $events ) )
            return $this->epl_util->epl_invoke_error( 20 );

        if ( isset( $_REQUEST['alt_total'] ) && $_REQUEST['alt_total'] == '' )
            unset( $this->current_data[$this->regis_id]['alt_total'] );
        if ( isset( $_REQUEST['_epl_donation_amount'] ) && $_REQUEST['_epl_donation_amount'] == '' )
            unset( $this->current_data[$this->regis_id]['_epl_donation_amount'] );

        $price_multiplier = 1;

        $pay_deposit_now = (($_POST && $is_discountable_step) ? epl_get_element( '_epl_pay_deposit', $_POST, 0 ) : epl_get_element_m( 'pay_deposit', 'money_totals', $this->current_data[$this->regis_id]['cart_totals'], 0 ));
        if ( $pay_deposit_now == 1 ) {

            $hide_offline_payment_options = true;
        }
        $cart_totals = array(
            'money_totals' => array(),
            '_att_quantity' => array(),
        );

        $cart_totals['money_totals']['grand_total'] = 0;
        $cart_totals['money_totals']['min_deposit'] = 0;
        $cart_totals['money_totals']['pay_deposit'] = $pay_deposit_now;
        $cart_totals['money_totals']['subtotal'] = 0;
        $cart_totals['money_totals']['surcharge'] = 0;
        $cart_totals['money_totals']['discountable_total'] = 0;
        $cart_totals['money_totals']['non_discountable_total'] = 0;
        $cart_totals['money_totals']['donation_amount'] = 0;
        $cart_totals['_att_quantity']['total'] = 0;
        $cart_totals['_att_quantity']['total_n'] = 0;
        $cart_totals['_att_quantity']['total_non_disc'] = 0;
        $cart_totals['money_totals']['num_events_in_cart'] = epl_get_num_events_in_cart();
        $cart_totals['money_totals']['num_discountable_events_in_cart'] = epl_get_num_events_in_cart();

        //for each event in the cart

        foreach ( $events as $event_id => $val ) {

            if ( !is_int( $event_id ) )
                continue;
            setup_event_details( $event_id );
            $surcharge_after_discount = (epl_get_element( '_epl_surcharge_before_discount', $event_details, 0 ) == 0);
            //not to everride other events in the cart
            if ( (!empty( $_REQUEST['event_id'] ) && $_REQUEST['event_id'] != $event_id) || isset( $_REQUEST['epl_m'] ) || epl_get_element( 'caller', $_REQUEST ) == 'summary' ) {

                $data[$event_id] = $this->current_data[$this->regis_id]['_events'][$event_id];
                if ( isset( $data[$event_id]['money_totals'] ) ) {
                    $cart_totals['money_totals']['grand_total'] += $data[$event_id]['money_totals']['grand_total'];
                    $cart_totals['money_totals']['min_deposit'] = epl_get_element_m( 'min_deposit', 'money_totals', $this->current_data[$this->regis_id]['cart_totals'] );
                    $cart_totals['money_totals']['subtotal'] += $data[$event_id]['money_totals']['subtotal'];
                    $cart_totals['money_totals']['surcharge'] += $data[$event_id]['money_totals']['surcharge'];
                    $cart_totals['money_totals']['discountable_total'] += $data[$event_id]['money_totals']['discountable_total'];
                    $cart_totals['money_totals']['non_discountable_total'] += $data[$event_id]['money_totals']['non_discountable_total'];
                    $cart_totals['_att_quantity']['total'] += $data[$event_id]['_att_quantity']['total'][$event_id];
                    $cart_totals['_att_quantity']['total_n'] += $data[$event_id]['_att_quantity']['total_n'][$event_id];
                    $cart_totals['_att_quantity']['total_non_disc'] += $data[$event_id]['_att_quantity']['total_non_disc'][$event_id];
                }
                continue;
            }
            else {
                //echo "<pre class='prettyprint'>" . __LINE__ . "> " . print_r($event_id, true). "</pre>";

                $_allow_glob_disc = (epl_get_element( '_epl_allow_global_discounts', $event_details, 0 ) == 10);
                //$has_event_level_disc = array_sum($event_details['_epl_discount_active']) > 0;
                //number of dates in the cart for this event.
                $dates = (isset( $this->current_data[$this->regis_id]['_dates']['_epl_start_date'][$event_id] )) ? $this->current_data[$this->regis_id]['_dates']['_epl_start_date'][$event_id] : array();
                $num_days_in_cart = count( $dates );

                //if price per date
                $price_multiplier = (($event_details['_epl_price_per'] == 10) ? $num_days_in_cart : 1);
                $_total_qty = 0;
                $_total_n_qty = 0;
                $_total_non_disc_qty = 0;

                if ( !is_null( $this->get_att_quantity_values() ) ) {

                    //attendee quantities in the cart for this event
                    $att_qty = epl_get_element( $event_id, $this->current_data[$this->regis_id]['_dates']['_att_quantity'] );
                    //echo "<pre class='prettyprint'>" . __LINE__ . "> $event_id >>> " . print_r($att_qty, true). "</pre>";
                    //total attendees for the event
                    $day_total = array_sum( ( array ) $att_qty );
                    //$data[$event_id] = array( );
                    $data[$event_id]['money_totals'] = array();
                    $data[$event_id]['money_totals']['grand_total'] = 0;
                    $data[$event_id]['money_totals']['min_deposit'] = 0;
                    $data[$event_id]['money_totals']['subtotal'] = 0;
                    $data[$event_id]['money_totals']['surcharge'] = 0;
                    $data[$event_id]['money_totals']['discountable_total'] = 0;
                    $data[$event_id]['money_totals']['non_discountable_total'] = 0;
                    $data[$event_id]['_att_quantity']['total'][$event_id] = 0;

                    $_price = 0;

                    $prices = $this->get_event_property( '_epl_price' );
                    $member_prices = $this->get_event_property( '_epl_member_price' );

                    //price covers event or per date
                    $price_per = epl_nz( $this->get_event_property( '_epl_price_per' ), 10 );
                    foreach ( ( array ) $att_qty as $price_id => $price_qty ) {

                        $_type = epl_get_element( $price_id, epl_get_element( '_epl_price_type', $event_details ), 'att' );
                        $_disc = epl_get_element( $price_id, epl_get_element( '_epl_price_discountable', $event_details ), 10 );

                        //echo "<pre class='prettyprint'>" . __LINE__ . "> " . print_r( $_disc, true ) . "</pre>";
                        //if ( epl_get_element( $price_id, epl_get_element( '_epl_price_type', $event_details ), 'att' ) == 'att' ) {
                        //if array,
                        if ( is_array( $price_qty ) ) {
                            $_qty = array_sum( $price_qty );
                            if ( epl_is_date_level_price() ) {
                                $price_multiplier = 1;
                            }
                        }
                        else {
                            $_qty = $price_qty;
                        }

                        $_price = ( int ) $_qty * epl_nz( epl_get_element( $price_id, $prices ), 0 );

                        if ( isset( $member_prices[$price_id] ) && $member_prices[$price_id] != '' && is_user_logged_in() )
                            $_price = ( int ) $_qty * epl_nz( epl_get_element( $price_id, $member_prices ), 0 );


                        if ( $_type == 'att' )
                            $_total_qty += $_qty;
                        else
                            $_total_n_qty += $_qty;

                        $data[$event_id]['money_totals']['subtotal'] += $_price;

                            $_tmpsurcharge = array(
                                'total' => $_price,
                                'price_id' => $price_id,
                                'qty' => $_qty
                            );

                            $_tmpsurcharge = $this->process_price_surcharge( $_tmpsurcharge );

                            $_tmpsurcharge['surcharge'] = epl_get_element( 'surcharge', $_tmpsurcharge, 0 );

                            if ( $_tmpsurcharge['surcharge'] > 0 ) {
                                $data[$event_id]['money_totals']['surcharge'] += epl_get_element( 'surcharge', $_tmpsurcharge, 0 );

                                $_price += $_tmpsurcharge['surcharge'];
                            }
                        

                        $data[$event_id]['_att_quantity'][$price_id] = $_qty;
                        $data[$event_id]['money_totals'][$price_id] = $_price;

                        if ( $_disc == 0 ) {
                            $data[$event_id]['money_totals']['non_discountable_total']+= $_price;

                            if ( $_qty > 0 ) {
                                $_total_non_disc_qty+=$_qty;
                            }
                        }
                        else
                            $data[$event_id]['money_totals']['discountable_total'] += $_price;

                        $data[$event_id]['money_totals']['grand_total'] += $_price;
                        //}
                    }
                }
                (epl_get_element( 'discountable_total', $data[$event_id]['money_totals'] ) ? $data[$event_id]['money_totals']['discountable_total'] *= $price_multiplier : null);
                $data[$event_id]['money_totals']['grand_total'] *= $price_multiplier;
                if ( $price_per == 10 )
                    $data[$event_id]['money_totals']['subtotal'] = $data[$event_id]['money_totals']['grand_total'];
                $data[$event_id]['_att_quantity']['total'][$event_id] = $_total_qty;
                $data[$event_id]['_att_quantity']['total_n'][$event_id] = $_total_n_qty;
                $data[$event_id]['_att_quantity']['total_non_disc'][$event_id] = $_total_non_disc_qty;

                $data[$event_id] = $this->process_event_surcharge( $data[$event_id], 'before_discount' );
                //epl_debug_message( basename( __FILE__ ) . '(' . __LINE__ . ')', $data[$event_id] );
                //if ( epl_sc_is_enabled() && epl_get_num_events_in_cart() > 1 )
                //  $data[$event_id] = $this->edm->process_discount( $data[$event_id] );
                if ( !$surcharge_after_discount )
                    $data[$event_id] = $this->process_event_surcharge( $data[$event_id], 'after_discount' );

                //epl_debug_message( basename( __FILE__ ) . '(' . __LINE__ . ')', $data[$event_id] );
                $cart_totals['money_totals']['grand_total'] += $data[$event_id]['money_totals']['grand_total'];
                $cart_totals['money_totals']['min_deposit'] += $this->calculate_deposit( $data[$event_id]['money_totals']['grand_total'] );
                $cart_totals['money_totals']['surcharge'] += $data[$event_id]['money_totals']['surcharge'];
                $cart_totals['money_totals']['subtotal'] += $data[$event_id]['money_totals']['subtotal'];
                $cart_totals['money_totals']['discountable_total'] += $data[$event_id]['money_totals']['discountable_total'];
                $cart_totals['money_totals']['non_discountable_total'] += $data[$event_id]['money_totals']['non_discountable_total'];
                $cart_totals['_att_quantity']['total'] += $data[$event_id]['_att_quantity']['total'][$event_id];
                $cart_totals['_att_quantity']['total_n'] += $data[$event_id]['_att_quantity']['total_n'][$event_id];
                $cart_totals['_att_quantity']['total_non_disc'] += $data[$event_id]['_att_quantity']['total_non_disc'][$event_id];

                //if($surcharge_after_discount)
                //   $cart_totals['money_totals']['discountable_total'] += $cart_totals['money_totals']['surcharge'];

                if ( !$_allow_glob_disc )
                    $cart_totals['money_totals']['num_discountable_events_in_cart'] --;
            }
        }
        //epl_debug_message( basename( __FILE__ ) . '(' . __LINE__ . ')', $data );
        $event_totals = $data;

        //$cart_totals = $this->process_cart_surcharge( $cart_totals, 'before_discount' );
        if ( $this->discountable_step ) {

            $cart_totals = $this->process_pay_profile_disc( $cart_totals );
            $cart_totals = $this->edm->process_discount( $cart_totals );
        }
        $cart_totals = $this->process_cart_surcharge( $cart_totals, 'after_discount' );

        $this->current_data[$this->regis_id]['_events'] = $data;
        if ( ($donation = epl_get_element_m( '_epl_donation_amount', $this->regis_id, $this->get_current_data(), '' )) != '' ) {
            $donation = abs( $donation );
            $cart_totals['money_totals']['donation_amount'] = $donation;
            $cart_totals['money_totals']['grand_total'] += $donation;
        }

        if ( ($alt_total = epl_get_element_m( 'alt_total', $this->regis_id, $this->get_current_data(), '' )) != '' ) {
            $cart_totals['money_totals']['original_total'] = $cart_totals['money_totals']['grand_total'];
            $cart_totals['money_totals']['grand_total'] = $alt_total;
        }
        wp_cache_set( 'cart_totals', $data );

        $this->current_data[$this->regis_id]['cart_totals'] = $cart_totals;

        $this->epl->epl_util->set_response_param( 'total_due', $cart_totals['money_totals']['grand_total'] );
        $this->epl->epl_util->set_response_param( 'hide_offline_payment_options', $hide_offline_payment_options );
        if ( $refresh_curr_data )
            $this->refresh_data();

        return $data;
    }


    function process_pay_profile_disc( $cart_totals ) {

        $selected_payment = $this->get_payment_profile_id();

        $pay_profile = $this->ecm->get_post_meta_all( $selected_payment );
        if ( !$pay_profile )
            return $cart_totals;

        $discount_amount = epl_get_element( '_epl_pay_discount_amount', $pay_profile, 0 );

        if ( $discount_amount > 0 && $cart_totals['money_totals']['grand_total'] > 0 ) {

            $discount_type = epl_get_element( '_epl_pay_discount_type', $pay_profile, 0 );
            $label = epl_get_element( '_epl_pay_discount_label', $pay_profile );


            if ( $discount_type == 5 ) { //fixed
                $discounted_amount = $discount_amount;
                $cart_totals['money_totals']['grand_total'] = ($cart_totals['money_totals']['grand_total'] - $discount_amount);
                $cart_totals['money_totals']['discountable_total'] = ($cart_totals['money_totals']['discountable_total'] - $discount_amount);
            }
            elseif ( $discount_type == 10 ) { //percent
                $discounted_amount = round( ($cart_totals['money_totals']['grand_total'] * $discount_amount / 100 ), 2 );
                $cart_totals['money_totals']['grand_total'] -= $discounted_amount;
                $cart_totals['money_totals']['discountable_total'] -= round( ($cart_totals['money_totals']['discountable_total'] * $discount_amount / 100 ), 2 );
            }

            $cart_totals['money_totals']['pre_discount_total'] = $cart_totals['money_totals']['discountable_total'];
            $cart_totals['money_totals']['discount_amount'] = $discounted_amount;
            $cart_totals['money_totals']['discount_description'] = $label;
        }

        return $cart_totals;
    }


    function process_price_surcharge( $args ) {

        global $event_details;

        extract( $args );
        $_surcharge_amount = 0;
        //if surcharge is greater than 0
        if ( ($surcharge_amount = epl_get_element_m( $price_id, '_epl_price_surcharge_amount', $event_details )) > 0 ) {


            $surcharge_type = epl_get_element_m( $price_id, '_epl_price_surcharge_type', $event_details );
            $surcharge_per = epl_get_element_m( $price_id, '_epl_price_surcharge_per', $event_details );

            if ( $surcharge_type == 5 ) { //fixed
                $_surcharge_amount = ($surcharge_amount * ($surcharge_per == 5 ? $qty : 1));
                //echo "<pre class='prettyprint'>" . __LINE__ . "> " . print_r(, true). "</pre>";
            }
            elseif ( $surcharge_type == 10 ) { //percent
                $_surcharge_amount = round( ($total * $surcharge_amount / 100 ), 2 );

                if ( $surcharge_per == 10 )
                    $_surcharge_amount = round( ($total / $qty * $surcharge_amount / 100 ), 2 );
            }
        }


        return array( 'surcharge' => $_surcharge_amount );
    }


    function process_event_surcharge( $totals, $step ) {
        global $event_details;

        if ( $totals['money_totals']['grand_total'] == 0 )
            return $totals;
        //if surcharge is greater than 0
        if ( ($surcharge_amount = epl_get_element( '_epl_surcharge_amount', $event_details )) > 0 ) {

            $surcharge_before_discount = epl_get_element( '_epl_surcharge_before_discount', $event_details );
            $surcharge_type = epl_get_element( '_epl_surcharge_type', $event_details );

            //apply before the disount
            if ( ($surcharge_before_discount == 10 && $step == 'before_discount') || ($surcharge_before_discount == 0 && $step == 'after_discount') ) {

                if ( $surcharge_type == 5 ) { //fixed
                    $totals['money_totals']['surcharge'] = $surcharge_amount;
                    $totals['money_totals']['grand_total'] = $totals['money_totals']['grand_total'] + $surcharge_amount;
                }
                elseif ( $surcharge_type == 10 ) { //percent
                    $totals['money_totals']['surcharge'] = round( $totals['money_totals']['grand_total'] * $surcharge_amount / 100, 2 );
                    $totals['money_totals']['grand_total'] = round( $totals['money_totals']['grand_total'] + $totals['money_totals']['surcharge'], 2 );
                }
                if ( $step == 'before_discount' )
                    $totals['money_totals']['discountable_total'] = $totals['money_totals']['grand_total'];
            }
        }


        return $totals;
    }


    function process_cart_surcharge( $cart_totals, $step ) {
        global $event_details, $event_totals;

        if ( $cart_totals['money_totals']['grand_total'] == 0 )
            return $cart_totals;

        $tmp_pct_surcharge_amount = 0;
        $tmp_fixed_surcharge_amount = 0;
//if surcharge is greater than 0

        foreach ( $event_totals as $event_id => $_totals ) {
            setup_event_details( $event_id );

            if ( ($surcharge_amount = epl_get_element( '_epl_surcharge_amount', $event_details, 0 )) > 0 ) {

                $surcharge_before_discount = epl_get_element( '_epl_surcharge_before_discount', $event_details );
                $surcharge_type = epl_get_element( '_epl_surcharge_type', $event_details );

                //apply before the disount
                if ( $surcharge_before_discount == 0 && $step == 'after_discount' ) {

                    if ( $surcharge_type == 5 ) { //fixed
                        $tmp_fixed_surcharge_amount += $surcharge_amount;
                    }
                    elseif ( $surcharge_type == 10 ) { //percent
                        //$tmp_surcharge_amount += round( $cart_totals['money_totals']['grand_total'] * $surcharge_amount / 100, 2 );
                        $tmp_pct_surcharge_amount += $surcharge_amount;
                    }
                }
            }
        }

        if ( $tmp_pct_surcharge_amount > 0 || $tmp_fixed_surcharge_amount > 0 ) {
            $tmp_pct_surcharge_amount = round( $cart_totals['money_totals']['grand_total'] * $tmp_pct_surcharge_amount / 100, 2 );
            $cart_totals['money_totals']['surcharge'] = $tmp_pct_surcharge_amount + $tmp_fixed_surcharge_amount;
            $cart_totals['money_totals']['grand_total'] += $tmp_pct_surcharge_amount + $tmp_fixed_surcharge_amount;
        }
        return $cart_totals;
    }


    function calculate_deposit( $amount ) {
        global $event_details;

        if ( $amount == 0 || $amount === '' )
            return $amount;

        $active = (epl_get_element( '_epl_enable_deposit_payment', $event_details, 0 ) == 10);
        $deposit_amount = epl_get_element( '_epl_deposit_amount', $event_details, '' );

        if ( !$active || $deposit_amount === '' )
            return 0;

        $type = epl_get_element( '_epl_deposit_type', $event_details, 10 );

        if ( $type == 5 ) { //fixed
            $amount = $deposit_amount;
        }
        elseif ( $type == 10 ) { //percent
            $amount = round( ($amount * $deposit_amount / 100 ), 2 );
        }

        return $amount;
    }


    function is_ok_to_register() {

        $this->set_event_capacity_info();
    }


    function get_event_property( $prop = '', $key = '' ) {

        if ( $prop == '' )
            return null;

        global $event_details;

        if ( $key !== '' ) {
            if ( array_key_exists( $key, $event_details[$prop] ) )
                return $event_details[$prop][$key];
        } elseif ( isset( $event_details[$prop] ) )
            return $event_details[$prop];
    }

    /*
     * sets global $capacity and $current_att_count for the event in the loop
     */


    function set_event_capacity_info() {

        //echo "<pre class='prettyprint'>" . print_r( $event_info, true ) . "</pre>";

        /*
         * -need to find out
         * -capacity
         * -capacity per
         * -current number of attendees.
         */
        global $capacity, $current_att_count, $event_details;
        $capacity = array();

        $this->ecm->get_current_att_count();


        $capacity['per'] = epl_get_element( '_epl_event_capacity_per', $event_details );  //event,  date,  time, price

        $capacity['cap'] = epl_get_element( '_epl_event_capacity', $event_details );
        $capacity['date'] = epl_get_element( '_epl_date_capacity', $event_details );
    }

    /*
     * gets the number for the QTY dropdown
     */


    function get_allowed_quantity( $price_key, $min = null, $max = null, $limit_to = null ) {
        global $event_details, $regis_details;

        $r = array(); //empty row.

        if ( !is_null( $limit_to ) ) {
            $r[$limit_to] = $limit_to;
        }
        else {
            $r[0] = 0;
            $min = epl_get_element( '_epl_min_attendee_per_regis', $event_details, 0 );
            $max = epl_get_element( '_epl_max_attendee_per_regis', $event_details, 0 );

            $price_qty = $this->get_allowed_quantity_per_price( $price_key, $min, $max );

            for ( $i = $price_qty['min']; $i <= $price_qty['max']; $i++ )
                $r[$i] = $i;

            if ( !$price_qty['zero_qty'] )
                unset( $r[0] );
        }
        return $r;
    }


    function get_allowed_quantity_per_price( $price_key, $min = null, $max = null ) {
        global $event_details, $regis_details;

        $r = array();

        $r['min'] = epl_nz( epl_get_element( $price_key, $event_details['_epl_price_min_qty'] ), 1 );
        $r['max'] = epl_nz( epl_get_element( $price_key, $event_details['_epl_price_max_qty'] ), 1 );

        $r['zero_qty'] = (!$this->on_admin ? epl_nz( epl_get_element( $price_key, epl_get_element( '_epl_price_zero_qty', $event_details ) ), true ) : true);


        if ( !EPL_IS_ADMIN && epl_is_waitlist_session_approved() ) {

            $event_id = $this->get_current_event_id();
            $this->ecm->setup_regis_details( intval( epl_get_element( 'epl_rid', $_REQUEST ) ) );

            $wl_quantities = epl_get_element( '_att_quantity', $this->current_data[$this->regis_id]['_dates'] );

            if ( epl_get_element_m( $price_key, $event_id, $wl_quantities ) ) {

                $r['max'] = current( epl_get_element_m( $price_key, $event_id, $wl_quantities ) );
            }
            elseif ( epl_get_element_m( 'waitlist_approved', '__epl', $_SESSION ) == $event_id ) {
                $r['max'] = 0;
            }
        }

        //$qty_available = epl_nz( epl_get_element( $price_key, epl_get_element( '_epl_price_max_qty', $event_details, 1 ) ), 1 );


        return $r;
    }

    /*
     * get a value from the session, based on key
     */


    function get_current_value( $part = '_dates', $field = null, $key_1 = null, $key_2 = null ) {

        global $epl_on_admin;

        $sess_base = $this->current_data[$this->regis_id];


        if ( empty( $sess_base[$part] ) )
            return null;

        if ( array_key_exists( $key_2, epl_get_element( $key_1, epl_get_element( $field, $sess_base[$part] ), array() ) ) )
            return $sess_base[$part][$field][$key_1][$key_2];
        elseif ( array_key_exists( $field, ( array ) $sess_base[$part] ) && array_key_exists( $key_1, ( array ) $sess_base[$part][$field] ) )
            return $sess_base[$part][$field][$key_1];
        else
            return null;
    }


    function get_gateway_info( $gateway_id = null ) {
        global $gateway_info;
        //if ( !$gateway_info = wp_cache_get( 'gateway_info_' . $gateway_id ) )
        //  return $gateway_info;
        $gateway_id = epl_get_element( 'gateway_id', $_REQUEST, $gateway_id );
        $gateway_info = $this->ecm->get_post_meta_all( !is_null( $gateway_id ) ? $gateway_id : $this->get_payment_profile_id()  );

        //wp_cache_add( 'gateway_info_' . $gateway_id, $gateway_info );
        return $gateway_info;
    }

    /*
     * get the dates for the cart, both edit and overview
     */


    function get_the_dates( $date_id = null ) {
        global $event_details, $multi_time, $multi_price, $event_snapshot;


        $data['date'] = array();
        $data['time'] = array();
        $dates_data = array();
        $event_id = $event_details['ID'];

        $event_type = epl_nz( $event_details['_epl_event_type'], 5 );

        $input_type = ($event_type == 5 ) ? 'radio' : 'checkbox';

        $rolling_regis = (!epl_user_is_admin() && epl_get_element( '_epl_rolling_regis', $event_details ) == 10);

        $first = apply_filters( 'epl_erm__get_the_dates__first', true );

        $event_status = epl_get_element( '_epl_event_status', $event_details );

        $value = (isset( $this->current_data[$this->regis_id]['_dates']['_epl_start_date'][$event_details['ID']] )) ? $this->current_data[$this->regis_id]['_dates']['_epl_start_date'][$event_details['ID']] : array();

        $value = $this->epl->epl_util->clean_input( $value );

        $data['show_date_selector_link'] = ($event_type < 7) ? (epl_get_element( '_epl_enable_front_date_selector_cal', $event_details, 0 ) != 0) : false;

        if ( epl_admin_override() && epl_get_regis_setting( 'epl_enable_admin_override_cal' ) == 10 )
            $data['show_date_selector_link'] = true;



        if ( !is_null( $date_id ) || (($date_id = epl_get_element( '_date_id', $_REQUEST )) != '' ) && ($event_type != 7 && $event_type != 10) ) {
            //$dates_data = array_intersect_key( $event_details['_epl_start_date'], array( $date_id => 1 ) );
            $epl_fields['default_checked'] = 1;
            $value = ( array ) $this->epl->epl_util->clean_input( $date_id );
        }

        if ( epl_is_empty_array( $dates_data ) )
            $dates_data = $event_details['_epl_start_date'];
        $_value = $value;

        if ( empty( $dates_data ) )
            return null;

        $date_format = apply_filters( 'epl_erm__get_the_dates__date_format', null );

        $one_day_showing = false;


        foreach ( $dates_data as $event_date_id => $event_date ) {
            $go = true;

            if ( !epl_admin_override() && $rolling_regis && ($one_day_showing || $event_snapshot[$event_id][$event_date_id]['date']['hide'] === true) )
                continue;
            //$open_for_regis = epl_compare_dates( $event_details['_epl_regis_start_date'][$event_date_id],date_i18n( "m/d/Y" ), "<=" );
            //if ( $data['show_date_selector_link'] && ($value != '' && !in_array( $event_date_id, $value )) )
            //continue;



            $start_date = epl_formatted_date( $event_date, $date_format );
            $end_date = epl_formatted_date( $event_details['_epl_end_date'][$event_date_id], $date_format );

            $end_date = ($start_date != $end_date ? ' - ' . $end_date : '');

            $date_group = ($dg = epl_get_element_m( $event_date_id, '_epl_date_group_no', $event_details, '' )) != '' ? "[$dg]" : '';

            $epl_fields = array(
                'input_type' => $input_type,
                'input_name' => "_epl_start_date[{$event_details['ID']}]{$date_group}[]",
                'options' => array( $event_date_id => $start_date . $end_date ),
                'display_inline' => true,
                'value' => (!epl_is_empty_array( $value ) ? $value : null) //$value
            );

            if ( $dg != '' ) {
                $epl_fields['input_type'] = 'radio';
                $epl_fields['default_checked'] = 1;
                $epl_fields['value'] = epl_get_element( $dg, $_value, null );
            }



            $ok_to_register = epl_is_ok_to_register( $event_details, $event_date_id );

            $avail_spaces = epl_get_date_avail_spaces( $event_id, $event_date_id );


            if ( $this->flow_mode == 'n' && !$this->on_admin && ($ok_to_register !== true || ($avail_spaces === 0 && epl_waitlist_spaces_open() === false)) ) { //&& $event_status <> 3 - may be ongoing, but still need to keep track of regis end date.
                $epl_fields['readonly'] = 1;
                $epl_fields['default_checked'] = 0;
                $epl_fields['options'][$event_date_id] .= epl_wrap( "<span class='epl_font_red'> - ", '</span>', ($ok_to_register !== true) ? $ok_to_register : ($avail_spaces == 0 ? '' : '')  );

                if ( $ok_to_register !== true )
                    $epl_fields['force_uncheck'] = true;
            }
            else {

                //this will make sure that only the first date is selected by default
                if ( $first ) {
                    $epl_fields['default_checked'] = 1;
                    $first = false;
                }

                $epl_fields += ( array ) $this->overview_trigger;
                //has to register for all dates.
                if ( $event_type == 5 ) {
                    $epl_fields['input_type'] = 'radio';

                    if ( count( $dates_data ) == 1 ) {
                        $epl_fields['default_checked'] = 1;
                    }
                }
                elseif ( $event_type == 7 || $event_type == 10 ) {

                    $epl_fields['readonly'] = 1;
                    $epl_fields['default_checked'] = 1;
                }
            }

            if ( $this->mode == 'overview' && !in_array( $event_date_id, ( array ) $epl_fields['value'] ) ) {
                $go = false;
            }
            else {
                if ( $rolling_regis )
                    if ( $event_status != 3 && $event_date >= EPL_DATE ) {
                        $one_day_showing = true;
                        $epl_fields['default_checked'] = 1;
                    }


                $data['date'][$event_date_id] = $this->epl_util->create_element( $epl_fields );
                $data['date'][$event_date_id]['input_name'] = $epl_fields['input_name'];
            }

            $data['date'][$event_date_id]['avail_spaces'] = $avail_spaces;


            if ( epl_is_date_level_time() && epl_is_addon_active( '_epl_atp' ) ) {

                $data['time'][$event_date_id] = $this->_get_time_fields( $event_date_id );
            }
            if ( epl_is_date_level_price() && epl_is_addon_active( '_epl_atp' ) ) {

                $data['prices'][$event_date_id] = $this->_get_prices( $event_date_id );
            }

            if ( $this->flow_mode == 'p' && !in_array( $event_date_id, ( array ) $value ) )
                $go = false;

            if ( !$go )
                unset( $data['date'][$event_date_id] );

            $data = apply_filters( 'epl_erm__get_the_dates__date_field_loop_end', $data, $event_date_id );

            //}
        }

        $data['value'] = $_value;
        $data['event_type'] = $event_type;
        $data['mode'] = $this->mode;
        $data['flow_mode'] = $this->flow_mode;

        $r = $this->epl->load_view( $this->dest . '/cart/cart-dates', $data, true );

        return $r;
    }

    /*
     * gets the time and prices fields for the cart
     */


    function get_time_and_prices_for_cart() {
        global $event_details, $multi_time, $multi_price;

        $r = '';
        //if each time slot has its own pricing
        if ( epl_get_element( '_epl_pricing_type', $event_details ) == 10 ) {
            $r = '';
            foreach ( $event_details['_epl_start_time'] as $time_id => $time ) {

                $epl_fields = array(
                    'input_type' => 'hidden',
                    'input_name' => "_epl_start_time[{$event_details['ID']}][{$time_id}]",
                    'label' => $time . ' - ' . $event_details['_epl_end_time'][$time_id],
                    'value' => $time_id //$v['epl_start_time'][$event_details['ID']][$date_id]
                );
                $epl_fields += ( array ) $this->overview_trigger;

                $data['event_time'] = $this->epl_util->create_element( $epl_fields );

                $data['event_time'] = $data['event_time']['field'] . $data['event_time']['label'];


                $r .= $this->epl->load_view( $this->dest . '/cart/cart-times', $data, true );

                $r .= $this->_get_prices_per_time( $time_id );
            }
            return $r;
        }
        else {

            if ( !epl_is_date_level_time() ) {
                $r .= $this->_get_time_fields();
            }
            if ( !epl_is_date_level_price() ) {
                $r .= $this->_get_prices();
            }
        }

        return $r;
    }

    /*
     * applies to times with different prices
     */


    function _get_time_for_price_fields( $date_id = null ) {
        global $event_details;

        $input_type = ( $event_details['_epl_pricing_type'] == 10 ) ? 'text' : 'select';


        $epl_fields = array(
            'input_type' => $input_type,
            'input_name' => "_epl_start_time[{$event_details['ID']}][{$date_id}]",
            'options' => $event_details['_epl_start_time'],
            'value' => $this->get_current_value( '_dates', '_epl_start_time', $event_details['ID'], $date_id )
        );
        $epl_fields += ( array ) $this->overview_trigger;

        $data['event_time'] = $this->epl_util->create_element( $epl_fields );

        $data['event_time'] = $data['event_time']['field'];

        if ( !is_null( $date_id ) )
            return $data['event_time'];

        return $this->epl->load_view( $this->dest . '/cart/cart-times', $data, true );
    }

    /*
     * search an array key for a pattern based on a
     */


    function preg_grep_keys( $pattern, $input, $flags = 0 ) {
        // echo "<pre class='prettyprint'>" . print_r( $pattern, true ) . "</pre>";
        $keys = preg_grep( $pattern, array_keys( $input ), $flags );
        $vals = array();
        foreach ( $keys as $key ) {
            $vals[$key] = $input[$key];
        }
        return current( $vals );
    }

    /*
     * construct the time fields dropdown
     */


    function _get_time_fields( $date_id = null ) {
        global $event_details, $capacity, $current_att_count, $event_snapshot;

        //if it is time specific pricing, value hidden
        $times = $event_details['_epl_start_time'];

        $input_type = (epl_get_element( '_epl_pricing_type', $event_details ) == 10 ) ? 'text' : 'select';
        $date_specifc_time = epl_get_element( '_epl_date_specific_time', $event_details, array() );
        $rolling_regis = (epl_get_element( '_epl_rolling_regis', $event_details ) == 10);
        $event_id = $this->get_current_event_id();
        $next_available_slot_found = false;
        if ( epl_is_empty_array( $times ) )
            return null;
        $number_of_times = count( $times );
        //adding the end time to the displayed value.  Notice the reference
        foreach ( $times as $time_key => &$v ) {

            if ( !is_null( $date_id ) ) {
                $weekday = date( 'N', $event_details['_epl_start_date'][$date_id] );
                $weekday_specific = epl_get_element_m( $time_key, '_epl_weekday_specific_time', $event_details, array() );

                if ( !epl_is_empty_array( $weekday_specific ) && !isset( $weekday_specific[$weekday] ) ) {
                    unset( $times[$time_key] );
                    continue;
                }
            }

            $_set = true;
            if ( $v == '' || epl_get_element( $time_key, epl_get_element( '_epl_time_hide', $event_details ) ) == 10 || ( epl_is_date_level_time() && !epl_is_empty_array( $date_specifc_time ) && (array_key_exists( $time_key, $date_specifc_time ) && !array_key_exists( $date_id, $date_specifc_time[$time_key] )) ) ) {
                unset( $times[$time_key] );
                $_set = false;
            }
            if ( $_set )
                $v .= (( $event_details['_epl_end_time'][$time_key] != '') ? ' - ' . $event_details['_epl_end_time'][$time_key] : '') . epl_prefix( ' - ', epl_get_element_m( $time_key, '_epl_time_note', $event_details ) );

            $v = apply_filters( 'epl_erm__get_time_fields__time_option_fields', $v, $time_key );

            if ( $rolling_regis ) {

                if ( $event_snapshot[$event_id][$date_id]['time'][$time_key]['avail'] === 0 || $next_available_slot_found === true ) {
                    if ( !EPL_IS_ADMIN )
                        unset( $times[$time_key] );
                } else {

                    if ( $next_available_slot_found == true || $number_of_times == 1 )
                        continue;

                    $next_available_slot_found = true;

                    if ( !EPL_IS_ADMIN ) {
                        if ( is_null( $date_id ) ) {
                            $this->current_data[$this->regis_id]['_dates']['_epl_start_time'][$event_id][] = $time_key;
                        }
                        else {
                            $this->current_data[$this->regis_id]['_dates']['_epl_start_time'][$event_id][$date_id] = $time_key;
                        }
                    }

                    //$this->current_data;
                }
            }
        }

        $times = apply_filters( 'epl_erm__get_time_fields__times', $times, $date_id );

        //user can select a date for each time
        if ( is_null( $date_id ) )
            $value = $this->get_current_value( '_dates', '_epl_start_time', $event_details['ID'] );
        else {
            $value = $this->get_current_value( '_dates', '_epl_start_time', $event_details['ID'], $date_id );
        }
        $data['time_optional'] = false;
        $style = '';
        if ( epl_is_empty_array( $times ) ) {
            $time_key = key( $event_details['_epl_start_time'] );

            $times = array( $time_key => 1 );
            //$style = "display:none";
            $data['time_optional'] = true;
        }
        //echo "<pre class='prettyprint'>" . __LINE__ . "> " . print_r($value, true). "</pre>";


        $epl_fields = array(
            'input_type' => $input_type,
            'input_name' => "_epl_start_time[{$event_details['ID']}][{$date_id}]",
            'class' => 'epl_time_dd',
            'options' => $times,
            'style' => $style,
            'default_value' => ($value == '' && isset( $_REQUEST['_time_id'] ) ? sanitize_text_field( $_REQUEST['_time_id'] ) : ''),
            'value' => $value //$v['epl_start_time'][$event_details['ID']][$date_id]
        );
        $epl_fields += ( array ) $this->overview_trigger;

        //if (epl_get_element($event_details['_epl_time_hide']))

        $data['event_time'] = $this->epl_util->create_element( $epl_fields );

        $data['event_time'] = $data['event_time']['field'];

        if ( !is_null( $date_id ) )
            return $data['event_time'];

        return $this->epl->load_view( $this->dest . '/cart/cart-times', $data, true );
    }

    /*
     * construct the prices fields
     */


    function _get_prices( $date_id = null ) {
        global $event_details, $regis_details;

        $r = '';
        $data = array();
        $date_specific_price = epl_get_element( '_epl_date_specific_price', $event_details );

        $data['has_date_limit'] = ((array_sum( ( array ) $event_details['_epl_price_date_from'] ) + array_sum( ( array ) $event_details['_epl_price_date_to'] )) > 0);

        foreach ( $event_details['_epl_price_name'] as $_price_key => $_price_name ) {
            $date_specifc = epl_get_element_m( $_price_key, '_epl_date_specific_price', $event_details, array() );
            //if date level and
            if ( epl_is_date_level_price() && !epl_is_empty_array( $date_specifc ) && !epl_is_empty_array( $date_specific_price ) && !isset( $date_specifc[$date_id] ) )
                continue;


            $data['price_name'] = $_price_name;
            $price_type = epl_get_element_m( $_price_key, '_epl_price_type', $event_details );



            $data['price'] = $event_details['_epl_price'][$_price_key];
            $data['member_price'] = epl_get_element_m( $_price_key, '_epl_member_price', $event_details );

            $value = $this->get_current_value( '_dates', '_att_quantity', $event_details['ID'], $_price_key );


            if ( !is_null( $date_id ) )
                $value = epl_get_element( $date_id, $value );

            $epl_fields = array(
                'input_type' => 'select',
                'input_name' => "_att_quantity[{$event_details['ID']}][{$_price_key}][{$date_id}]",
                'options' => $this->get_allowed_quantity( $_price_key ),
                'value' => $value,
                'class' => 'epl_att_qty_dd',
                'id' => "_att_quantity-{$event_details['ID']}-{$_price_key}-{$date_id}"
            );

            if ( epl_is_addon_active( 'DASFERWEQREWE' ) ) {
                if ( epl_get_element_m( $_price_key, '_epl_price_pack_type', $event_details ) == 'time' ) {
                    $mem_l = epl_get_element_m( $_price_key, '_epl_price_pack_time_length', $event_details );
                    $mem_lt = epl_get_element_m( $_price_key, '_epl_price_pack_time_length_type', $event_details );

                    $start = (!epl_is_empty_array( $regis_details ) ? strtotime( $regis_details['post_date'] ) : EPL_DATE);

                    $data['price_name'] .= ' - ' . epl__( 'until' ) . ' ' . epl_formatted_date( strtotime( "+ $mem_l $mem_lt", $start ) );
                }
            }


            $epl_fields += ( array ) $this->overview_trigger;
            $qty = 0;
            if ( $this->flow_mode == 'p' ) {
                if ( !is_null( $value ) ) {
                    if ( count( $value, 1 ) > 1 )
                        $qty = current( epl_get_element( $_price_key, $value, array() ) );
                    else
                        $qty = epl_get_element( 0, $value, 0 );
                }
                if ( $qty > 0 )
                    $epl_fields['options'] = $this->get_allowed_quantity( $_price_key, null, null, $qty );
            }

            $data['mode'] = $this->mode;
            $data['regis_expiration']['ok'] = 1;
            $data['regis_expiration'] = apply_filters( 'epl_prices_exp_dates', $_price_key, $qty );



            if ( !$this->on_admin && $data['regis_expiration']['ok'] == 0 )
                $data['price_qty_dd'] = '';
            else {
                $data['price_qty_dd'] = $this->epl_util->create_element( $epl_fields );
            }

            $member_only = (epl_get_element_m( $_price_key, '_epl_price_member_only', $event_details, 0 ) == 10);
            $min_level = epl_get_element_m( $_price_key, '_epl_price_membership_min_level', $event_details, 0 );


            if ( !is_user_logged_in() && $this->mode != 'overview' ) {

                if ( $data['member_price'] != '' ) {

                    $data['price_qty_dd']['field'] .= ' ' . get_the_register_button( null, false, array( 'button_text' => sprintf( epl__( 'Login to access %s member price' ), epl_get_formatted_curr( $data['member_price'], null, true ) ), 'class' => ' ', 'member_only' => 1, 'no_modal' => true ) );
                }
                if ( $member_only )
                    $data['price_qty_dd']['field'] = get_the_register_button( null, false, array( 'button_text' => epl__( 'Login to access this members only price' ), 'class' => ' ', 'member_only' => 1, 'no_modal' => true ) );
            } elseif ( $data['member_price'] != '' ) {

                $data['price'] = $data['member_price'];
            }


            if ( defined( 'S2MEMBER_CURRENT_USER_ACCESS_LEVEL' ) && is_user_logged_in() ) {

                if ( S2MEMBER_CURRENT_USER_ACCESS_LEVEL < $min_level )
                    $data['price_qty_dd']['field'] = epl__( 'Higher membership level required' );
            }

            if ( epl_get_element_m( $_price_key, '_epl_price_surcharge_amount', $event_details, 0 ) != 0 ) {

                $_tmpsurcharge = array(
                    'total' => $data['price'],
                    'price_id' => $_price_key,
                    'qty' => 1
                );
                $_tmpsurcharge = $this->process_price_surcharge( $_tmpsurcharge );

                $data['price_qty_dd']['field'] .= ' +' . epl_get_formatted_curr( $_tmpsurcharge['surcharge'], null, true ) . ($event_details['_epl_price_surcharge_per'][$_price_key] == 5 ? epl__( '/each' ) : '');
            }

            $data['price_note'] = epl_get_element( $_price_key, $event_details['_epl_price_note'] );

//            echo "<pre class='prettyprint'>" . __LINE__ . "> " . basename( __FILE__ ) . " > " . print_r( $data, true ) . "</pre>";
            if ( $this->mode == 'overview' && array_sum( ( array ) $value ) == 0 || (epl_get_element( $_price_key, epl_get_element( '_epl_price_hide', $event_details ) ) == 10) ) {
                //    || (!is_null( $date_id ) && !isset( $date_specifc[$date_id] ) ) )
            }
            else
                $r .= $this->epl->load_view( $this->dest . '/cart/cart-prices-row', $data, true );
        }

        return $this->epl->load_view( $this->dest . '/cart/cart-prices', array( 'prices_table' => $r ), true );
    }

    /*
     * construct the prices fields for time specific pricing
     */


    function _get_prices_per_time( $time_id ) {
        global $event_details;
        $r = '';

        $prices_data = ($this->mode == 'overview') ? $this->current_data[$this->regis_id]['_dates']['_att_quantity'][$event_details['ID']] : $event_details['_epl_price_name'];

        foreach ( $prices_data as $_price_key => $_v ) {
            if ( $time_id == $event_details['_epl_price_parent_time_id'][$_price_key] ) {

                $data['price_name'] = $event_details['_epl_price_name'][$_price_key];
                $data['price'] = $event_details['_epl_price'][$_price_key];
                $value = $this->get_current_value( '_dates', '_att_quantity', $event_details['ID'], $_price_key );

                $epl_fields = array(
                    'input_type' => 'select',
                    'input_name' => "_att_quantity[{$event_details['ID']}][{$_price_key}]",
                    'options' => $this->get_allowed_quantity( $_price_key ),
                    'value' => $value
                );
                $epl_fields += ( array ) $this->overview_trigger;

                $data['price_qty_dd'] = $this->epl_util->create_element( $epl_fields );
                if ( $this->mode == 'overview' && $value == 0 ) {
                    
                }
                else
                    $r .= $this->epl->load_view( $this->dest . '/cart/cart-prices-row', $data, true );
            }
        }
        return $this->epl->load_view( $this->dest . '/cart/cart-prices', array( 'prices_table' => $r ), true );
    }

    /*
     * could be the db, session, post
     */


    function get_cart_values( $values = '_dates' ) {

        return epl_get_element( $values, $this->current_data[$this->regis_id] );
        return $this->data[$this->regis_id][$values];
    }

    /*
     * could be the db, session, current logged in user infor
     */

    /*
     * gets the values that have already been entered in the regis forms
     */


    function get_relevant_regis_values() {

        return (isset( $this->current_data[$this->regis_id]['_attendee_info'] )) ? $this->current_data[$this->regis_id]['_attendee_info'] : '';
    }


    function get_old_regis_values() {

        return (isset( $this->current_data[$this->regis_id]['_old_attendee_info'] )) ? $this->current_data[$this->regis_id]['_old_attendee_info'] : '';
    }


    /**
     * Either way, the data will end up in a session.  When editing, data is pulled from the db.  This
     * function sets a data variable for access by other functions
     *
     * long description
     *
     * @since 1.0.0
     * @param int $var
     * @return string
     */
    function _refresh_data() {
        global $epl_on_admin;

        if ( !is_null( $this->data ) )
            return;

        if ( $epl_on_admin ) {
            global $post;

            $v = $this->ecm->get_post_meta_all( get_the_ID() );

            $v = $v['__epl'];
            $this->current_data = $v;
        }
        else
            $v = $this->current_data;

        $this->data = stripslashes_deep( $v ); //array( '__epl' => stripslashes_deep( $v ) );
        $this->regis_id = $this->data['_regis_id'];
    }

    /*
     * Set the values in the session,
     */


    function _set_relevant_data( $index = null, $value = null, $force = false ) {
        global $epl_on_admin, $epl_current_step;

        if ( !$_POST && !$force )
            return;
        $events_in_cart = $this->get_events_in_cart();

        foreach ( $events_in_cart as $event_id => $totals ) {

            //$event_id = $this->get_current_event_id();

            setup_event_details( $event_id );

            //adjust for date specific price.
            if ( epl_is_date_level_price() && !empty( $_POST['_epl_start_date'][$event_id] ) && isset( $_POST['_att_quantity'] ) ) {
                $dates = array_flip( epl_get_element_m( $event_id, '_epl_start_date', $_POST, array() ) );

                $prices = epl_get_element_m( $event_id, '_att_quantity', $_POST );

                foreach ( $prices as $price_id => $data ) {

                    $_temp = array_intersect_key( $data, $dates );

                    $_REQUEST['_att_quantity'][$event_id][$price_id] = $_temp;
                }
            }
            //adjust for date specific time.
            if ( !epl_is_time_optonal() && epl_is_date_level_time() && !empty( $_POST['_epl_start_date'][$event_id] ) && isset( $_POST['_epl_start_time'] ) ) {
                $dates = array_flip( ( array ) epl_get_element_m( $event_id, '_epl_start_date', $_POST ) );

                $times = epl_get_element_m( $event_id, '_epl_start_time', $_POST );

                $_temp = array_intersect_key( $times, $dates );

                $_REQUEST['_epl_start_time'][$event_id] = $_temp;
            }
        }


        $rel_fields = array(
            '_dates' => array(
                '_epl_start_date' => '',
                '_epl_start_time' => '',
                '_att_quantity' => '',
                '_epl_discount_code' => '',
            //'_epl_selected_payment' => '',
            //'_epl_payment_method' => '',
            ),
            '_attendee_info' => $this->ecm->get_list_of_available_fields(),
            'newsletter_signup' => '',
            '_epl_start_week' => 0,
            '_epl_donation_amount' => '',
            '_epl_payment_method' => '',
            'alt_total' => ''
        );

        $temp_fields = array(
            'user_id' => (!epl_user_is_admin() ? get_current_user_id() : ''),
            'user_login' => '',
            'user_pass' => '',
            'user_pass_confirm' => '',
        );

        if ( !epl_is_empty_array( epl_get_element( 'temp_fields', $_SESSION ) ) )
            $temp_fields = epl_get_element( 'temp_fields', $_SESSION );

        $incoming_data = apply_filters( 'epl_erm__set_relevant_data__incoming_data', $_REQUEST );

        //if waitlist record.
        if ( epl_get_element( 'epl_rid', $_REQUEST ) && ((epl_is_waitlist_record() && epl_is_waitlist_approved()) || epl_is_valid_url_hash( false ) ) ) {
            global $regis_details;
            $regis_details = $this->ecm->setup_regis_details( intval( epl_get_element( 'epl_rid', $_REQUEST ) ) );

            $this->regis_id = $regis_details['post_title'];

            if ( ($this->flow_mode == 'p' && $epl_current_step == 'process_cart_action') || epl_is_waitlist_record() ) {
                $this->current_data = $regis_details['__epl'];
                $this->current_data['post_ID'] = intval( epl_get_element( 'epl_rid', $_REQUEST ) );
            }
        }


        foreach ( $rel_fields as $rel_section => $rel_field ) {

            $old_val = '';
            //need to use the old val so that when a new event is added to the cart, the old value does not get overwritten
            //as the post vals for the other events will not be coming in
            if ( epl_get_element( 'from_modal', $_REQUEST ) == 1 )
                $old_val = epl_get_element_m( $rel_section, $this->regis_id, $this->current_data );

            if ( is_array( $rel_field ) ) {

                //$old_val = array_intersect_key( $incoming_data, $old_val );
                $value = array_intersect_key( $incoming_data, $rel_field );

                if ( !epl_is_empty_array( $old_val ) ) {
                    $value = $this->array_merge_recursive_new( $old_val, $value );
                }

                if ( !epl_is_empty_array( $value ) ) {
                    $value = $this->epl_util->clean_input( $value );
                    $this->data[$this->regis_id][$rel_section] = $value;
                    $this->current_data[$this->regis_id][$rel_section] = $value;
                }
            }
            else {
                $value = $this->epl_util->clean_input( epl_get_element( $rel_section, $_REQUEST, '' ) );
                //redundant
                if ( !epl_is_empty_array( $old_val ) ) {
                    $value = $this->array_merge_recursive_new( $old_val, $value );
                }

                if ( $value !== '' )
                    $this->current_data[$this->regis_id][$rel_section] = $value;
            }
        }

        if ( $_SERVER['REQUEST_METHOD'] == 'POST' ) {

            $merged_temp_fields = array_intersect_key( $incoming_data, $temp_fields );

            $_SESSION['temp_fields'] = (epl_is_empty_array( $merged_temp_fields ) ? $temp_fields : $merged_temp_fields);
        }

        //if edit mode
//        $this->calculate_cart_totals();

        $this->refresh_data();
    }


    function limit_events_in_current_data( $events = array() ) {

        if ( !empty( $events ) ) {

            $this->current_data[$this->regis_id]['_events'] = array_intersect_key( $this->current_data[$this->regis_id]['_events'], array_flip( $events ) );
        }
    }


    function remove_events_from_current_data( $events = array(), $refresh = true ) {

        if ( !empty( $events ) ) {
            $this->current_data[$this->regis_id]['_events'] = array_diff_key( $this->current_data[$this->regis_id]['_events'], array_flip( $events ) );
            if ( $refresh )
                $this->refresh_data();
        }
    }


    function array_merge_recursive_new() {

        $arrays = func_get_args();
        $base = array_shift( $arrays );

        foreach ( $arrays as $array ) {
            reset( $base ); //important
            while ( list($key, $value) = @each( $array ) ) {
                if ( is_array( $value ) && @is_array( $base[$key] ) ) {
                    $base[$key] = $this->array_merge_recursive_new( $base[$key], $value );
                }
                else {
                    $base[$key] = $value;
                }
            }
        }

        return $base;
    }


    function payment_choices() {
        global $event_details;

        $this->payment_choices = epl_get_element( '_epl_payment_choices', $event_details, array() );
        $this->default_selected = epl_get_element( '_epl_default_selected_payment', $event_details, null );

        if ( epl_sc_is_enabled() ) {
            $this->payment_choices = epl_get_setting( 'epl_sc_options', 'epl_sc_payment_choices', array() );
            $this->default_selected = epl_get_setting( 'epl_sc_options', 'epl_sc_default_selected_payment', array() );
        }
    }


    function get_payment_options() {
        global $event_details;

        $this->payment_choices();

        $offline_payments = array( '_cash', '_check' );

        $_o = array();
        $data = array();
        $date['date'] = '';
        $date['field'] = '';

        $value = $this->get_payment_profile_id();

        foreach ( ( array ) $this->payment_choices as $payment_choice ) {
            $q = $this->ecm->get_post_meta_all( $payment_choice );
            if ( !$q )
                continue;
            $label = epl_get_element( '_epl_pay_display', $q );

            if ( ($pay_discount = epl_get_element( '_epl_pay_discount_amount', $q )) > 0 ) {

                $label .= epl_wrap( epl_get_element( '_epl_pay_discount_label', $q ), ' (', ')' );
            }

            $_payment = array(
                'input_type' => 'radio',
                'input_name' => '_epl_payment_method[]',
                'label' => $label,
                'options' => array( $payment_choice => '' ),
                'default_checked' => (is_null( $value ) && ($this->default_selected == $payment_choice || is_null( $this->default_selected )) || count( $this->payment_choices ) == 1) ? 1 : 0,
                'display_inline' => 1,
                'required' => true,
                'value' => $value
            );

            $data['payment_choice'] = $this->epl_util->create_element( $_payment );
            $data['offline'] = in_array( epl_get_element( '_epl_pay_type', $q ), $offline_payments ) ? 'epl_offline_payment' : '';
            $date['field'] .= $this->epl->load_view( 'front/cart/cart-payment-choices', $data, true );
        }

        return '<table class="epl_payment_options">' . $date['field'] . '</table>';
    }


    function construct_payment_option() {
        
    }

    /*
     * First, save the post data from the cart, then display the registration form
     */


    function regis_form( $values = null, $primary_only = false, $show_cc_form = true, $show_new_user_form = true, $meta = array() ) {

        global $event_details, $epl_error, $email_regis_form;

        $email_regis_form = '';

        if ( epl_is_empty_array( $event_details ) ) {
            $this->ecm->setup_event_details( ( int ) $_REQUEST['event_id'] );
        }
        $data = array();
        $data['forms'] = '';

        $action = epl_get_element( 'epl_action', $_GET );

        if ( $this->mode == 'edit' ) { //not overview
            $_data = apply_filters( 'epl_regis_form_edit_mode', $_REQUEST );

            $this->_set_relevant_data();

            $this->set_event_capacity_info(); //
            $this->ok_to_proceed(); //Are there available spaces for the dates, times, prices in the cart?

            $this->event_snapshot();
        }

        if ( (epl_regis_flow() == 2 && $action == 'regis_form') || ($this->mode == 'overview' && $action == 'show_cart_overview') || $action == 'payment_page' ) {//overview mode comes after user enters their info in the fields and submits
            if ( $_GET['epl_action'] != 'payment_page' ) {

                //$this->_set_relevant_data( '_attendee_info', $_POST ); //from the regis form, add to session
                //$this->add_registration_to_db( $this->current_data ); //create the record
            }
            $data['cc_form'] = '';

            if ( $this->has_selected_cc_payment() && $show_cc_form && !epl_is_zero_total() && !epl_is_waitlist_flow() ) {
                $_f = epl_cc_billing_fields();

                $gateway_info = $this->get_gateway_info();

                $accepted_cards = ( array ) $gateway_info['_epl_accepted_cards'];

                //Temp solution
                foreach ( $_f['epl_cc_billing_fields']['_epl_cc_card_type']['options'] as $k => $v ) {
                    if ( !in_array( $k, $accepted_cards ) )
                        unset( $_f['epl_cc_billing_fields']['_epl_cc_card_type']['options'][$k] );
                }

                $_field_args = array(
                    'section' => $_f['epl_cc_billing_fields'],
                    'fields_to_display' => array_keys( $_f['epl_cc_billing_fields'] ),
                    'meta' => array( '_view' => 0, '_type' => 'ind', 'value' => $_POST )
                );

                $data['_f'] = $this->epl_util->render_fields( $_field_args );

                if ( $this->has_selected_cc_payment( null, true ) == '_stripe' ) {
                    $egm = $this->epl->load_model( 'epl-gateway-model' );
                    $data['cc_form'] = $egm->setup_stripe_form();
                }
                else
                    $data['cc_form'] = $this->epl->load_view( $this->dest . '/registration/regis-cc-form', $data, true );
            }
        }

        $this->num_events_in_cart = 0;

        if ( is_null( $values ) )
            $values = $this->get_cart_values( '_events' );
        else
            $values = $values['_events'];

        if ( empty( $values ) )
            return $this->epl_util->epl_invoke_error( 20 );

        $r = '';
        $data['forms'].= $this->get_user_list_dd();
        //$events = array_keys( $values['_epl_start_date'] );
        //if ( epl_sc_is_enabled() && epl_get_num_events_in_cart() > 1 ) {

        $is_waitlist = (!empty( $meta['waitlist_flow'] ) || epl_is_waitlist_flow());

        if ( version_compare( epl_regis_plugin_version(), '1.4', '>=' ) && epl_sc_is_enabled() && epl_get_setting( 'epl_sc_options', 'epl_sc_forms_to_use' ) == 1 ) {

            if ( !$primary_pulled ) {
                $data['forms'] .= $this->get_registration_forms(
                        array(
                            'scope' => $is_waitlist ? 'waitlist' : 'ticket_buyer',
                            'process' => 'esc',
                            'forms' => 'epl_sc_primary_regis_forms',
                            'price_name' => '',
                            'date_display' => '',
                            'price_id' => null ) );

                $data['forms'].= $this->get_new_user_form( $show_new_user_form );

                $primary_pulled = true;
            }
            $_cart = $this->get_cart_values( '_dates' );
            $attendee_forms = epl_get_setting( 'epl_sc_options', 'epl_sc_addit_regis_forms' );
            if ( !epl_is_empty_array( $attendee_forms ) && !epl_is_waitlist_flow() ) {

                foreach ( ( array ) $_cart['_att_quantity'] as $event_id => $quantities ) {
                    if ( !isset( $values[$event_id] ) )
                        continue;
                    setup_event_details( $event_id );
                    $reset_count = true;
                    foreach ( $quantities as $price_id => $qty ) {
                        $attendee_qty = (is_array( $qty )) ? array_sum( $qty ) : $qty;
                        if ( $attendee_qty > 0 ) {
                            $data['forms'] .= $this->get_registration_forms(
                                    array(
                                        'scope' => 'regis_forms',
                                        'event_id' => $event_id,
                                        'process' => 'esc',
                                        'forms' => 'epl_sc_addit_regis_forms',
                                        'attendee_qty' => $attendee_qty,
                                        'price_id' => $price_id,
                                        'date_display' => '',
                                        'reset_count' => $reset_count,
                                        'price_name' => $event_details['_epl_price_name'][$price_id] ) );

                            $reset_count = false;
                        }
                    }
                }
            }
        }
        else {
            $primary_pulled = false;
            $_cart = $this->get_cart_values( '_dates' );

            $this->pulling_forms = 'pri';
            $this->num_events_in_cart = count( $values );
            foreach ( $values as $event_id => $event_dates ) {

                if ( !empty( $meta['event_id'] ) && $meta['event_id'] != $event_id )
                    continue;
                setup_event_details( $event_id );

                //display the ticket purchaser form.
                //we need to combine primary forms so the info is only displayed once.
                if ( !$primary_pulled ) {

                    $data['forms'] .= $this->get_registration_forms(
                            array(
                                'scope' => $is_waitlist ? 'waitlist' : 'ticket_buyer',
                                'event_id' => $event_id,
                                'process' => 'non_esc',
                                'forms' => '_epl_primary_regis_forms',
                                'price_name' => '',
                                'date_display' => '',
                                'price_id' => null ) );



                    //$primary_pulled = true;
                }
            }

            $data['forms'].= $this->get_new_user_form( $show_new_user_form );

            $this->pulling_forms = 'att';
            //if ( !$primary_only ) {
            if ( !epl_is_waitlist_flow() ) {
                foreach ( $values as $event_id => $event_dates ) {

                    //if ( epl_is_empty_array( epl_get_element( '_epl_addit_regis_forms', $event_details ) ) )
                    // continue;
                    setup_event_details( $event_id );

                    foreach ( ( array ) $_cart['_att_quantity'][$event_id] as $price_id => $qty ) {
                        $attendee_qty = (is_array( $qty )) ? array_sum( $qty ) : $qty;
                        if ( $attendee_qty > 0 ) {

                            $data['forms'] .= $this->get_registration_forms(
                                    array(
                                        'scope' => 'regis_forms',
                                        'event_id' => $event_id,
                                        'process' => 'non_esc',
                                        'forms' => '_epl_addit_regis_forms',
                                        'attendee_qty' => $attendee_qty,
                                        'price_id' => $price_id,
                                        'date_display' => '',
                                        'price_name' => $event_details['_epl_price_name'][$price_id] ) );
                        }
                    }
                }
                //$data['forms'] .= $this->get_registration_forms( array( 'scope' => 'regis_forms', 'event_id' => $event_id, 'forms' => '_epl_addit_regis_forms', 'attendee_qty' => $attendee_qty ) );
                //$r .= $thiis->epl->load_view( 'front/registration/regis-page', $data, true );
            }
        }

        //TODO - temp solution
        $egm = $this->epl->load_model( 'epl-gateway-model' );

        $data['redirect_form_data'] = $egm->get_redirect_form_data();

        $r = $this->epl->load_view( $this->dest . '/registration/regis-page', $data, true );

        return $r;
    }

    /*
     * gets the registration forms, called from regis_form(), based on the event settings
     */


    function get_registration_forms( $args ) {
        global $event_totals, $cart_totals, $event_details;

        extract( $args );
        if ( $process != 'esc' || $scope == 'waitlist' ) {
            $event_id = ( int ) $event_id;

            if ( $process != 'esc' && is_null( $event_id ) || is_null( $forms ) )
                return;

            global $epl_fields, $event_details;

            $r = '';

            $this->fields = $epl_fields;
            //$event_details = ( array ) $this->ecm->get_post_meta_all( $event_id );


            $price_forms = (isset( $price_id )) ? epl_get_element( $price_id, epl_get_element( '_epl_price_forms', $event_details, array() ) ) : null;

            $discount_code_id = epl_get_element_m( 'discount_code_id', 'money_totals', $cart_totals );
            $discount_forms = array();
            if ( $discount_code_id && $scope == 'regis_forms' ) {

                //do not show any attendee forms for specific disocunt codes
                if ( epl_get_element( $discount_code_id, epl_get_element( '_epl_discount_forms_per', $event_details ) ) == 3 )
                    return null;

                $discount_forms = epl_get_element( $discount_code_id, epl_get_element( '_epl_discount_forms', $event_details, array() ) );
            }


            $regis_forms = array();
            if ( $scope == 'waitlist' ) {
                global $epl_waitlist_flow;
                $epl_waitlist_flow = true;

                $regis_forms = array( epl_get_element( '_epl_waitlist_form', $event_details, array() ) => 1 );
            }
            elseif ( $scope == 'regis_forms' ) {
                if ( !$price_forms && (!is_array( $event_details[$forms] ) || empty( $event_details[$forms] )) )
                    return null;
            }elseif ( !is_array( $event_details[$forms] ) || empty( $event_details[$forms] ) ) {
                // if ( $scope == 'ticket_buyer' )
                //   $regis_forms = array( '4e8b3920c839b' => 1 );
                //else
                return null;
            }
            //find the forms selected in the event

            if ( !epl_is_empty_array( $discount_forms ) ) {
                $regis_forms = $discount_forms;
                $_form_per = epl_get_element( $discount_code_id, epl_get_element( '_epl_discount_forms_per', $event_details ) );
                $attendee_qty = ($_form_per == 2) ? 1 : $attendee_qty;
            }
            elseif ( $price_forms ) {
                $regis_forms = $price_forms;
                $_form_per = epl_get_element( $price_id, epl_get_element( '_epl_price_forms_per', $event_details ) );
                $attendee_qty = ($_form_per == 2) ? 1 : $attendee_qty;
            }
            elseif ( epl_is_empty_array( $regis_forms ) )
                $regis_forms = array_flip( $event_details[$forms] );
        } else {

            $regis_forms = array_flip( epl_get_setting( 'epl_sc_options', $forms ) );
        }

        //find the list of all forms
        $forms_to_display = $this->ecm->get_list_of_available_forms();

        //isolate the forms in that are selected inside the event
        $forms_to_display = array_intersect_key( $forms_to_display, $regis_forms );

        if ( epl_get_element( 'return', $args ) )
            return $forms_to_display;
        //find a list of all fields so that we can construct the form
        //$available_fields = $this->ecm->get_list_of_available_fields();

        /*
         * sets how many forms will be dispayed based on the forms selected in the event "Forms for all attendees" section
         */
        if ( !isset( $attendee_qty ) ) {
            $loop_start = 0;
            $loop_end = 0;
        }
        else {
            $loop_start = 1;
            $loop_end = $attendee_qty;
        }

        $args = array();
        //for each attendee,construct the appropriate forms
        for ( $i = $loop_start; $i <= $loop_end; $i++ ) {
            //echo "<pre class='prettyprint'>" . __LINE__ . "> " . print_r(epl_get_element($price_id, $event_details['_epl_price_type'], ''), true). "</pre>";
            //if ( !isset( $price_id ) || (epl_get_element( $price_id, epl_get_element( '_epl_price_type', $event_details ), 'att' ) == 'att') )
            if ( !isset( $price_id ) || (epl_get_element( $price_id, epl_get_element( '_epl_price_type', $event_details ), 'att' ) == 'att') || $price_forms )
                $r .= $this->construct_form( $scope, $event_id, $forms_to_display, $i, $price_name, $price_id, $date_display );
        }
        return $r;
    }

    /*
     * construct a form,
     */


    function construct_form( $scope, $event_id, $forms, $attendee_number, $price_name = '', $price_id = null, $date_display = null ) {

        static $ticket_number = 0; //keeps track of the attendee count for dispalay
        static $primary_counter = 1; //keeps track of the attendee count for dispalay
        static $primary_forms = array();
        global $event_details, $customer_email, $customer_name, $regis_details;
        global $email_regis_form; //TODO temp solution
        $has_email_field = false;
        if ( !is_array( $customer_email ) ) {
            $customer_email = array();
            $customer_name = array();
        }
        if ( $email_regis_form == '' )
            $ticket_number = 0;

        if ( $scope != 'waitlist' && ($this->pulling_forms == 'pri' && $this->num_events_in_cart > 1 && $primary_counter < $this->num_events_in_cart) ) {
            $ticket_number = 0;

            $primary_forms += $forms;
            $primary_counter++;

            return '';
        }

        $ur_specific = '';
        if ( $scope == 'ticket_buyer' && !is_user_logged_in() && epl_um_is_enabled() && $this->add_new_user_enable() != 0 && $this->add_new_user_method() == 1 ) {

            $ur_specific = epl__( "This email will be used to grant you membership access to our website." );
            if ( !$this->add_new_user_show_pass_fields() )
                $ur_specific .= '<br />' . epl__( "  You will receive an email with your password after completing this registration." );
            if ( $this->mode != 'overview' )
                $ur_specific .= '<br />' . sprintf( epl__( "If you are already a member of the website, please %s" ), "<a href=" . wp_login_url( epl_get_url() ) . " class='' title='Login'>" . epl__( 'Login Here' ) . "</a>" );
        }

        if ( $ticket_number == 0 && !empty( $primary_forms ) ) {
            $forms += $primary_forms;
        }
        $vals = $this->get_relevant_regis_values(); //if data has already been entered into the session, get that data
        //$ticket_number = $attendee_number;
        $data['mode'] = $this->mode;

//to compensate for pre 1.2.9 data 

        if ( !is_null( $price_id ) && version_compare( epl_regis_plugin_version(), '1.2.9', '<' ) ) {

            if ( $temp_price_id != $price_id ) {

                $temp_price_id = $price_id;

                $ticket_number = 1;
            }
        }

        $data['ticket_number'] = $ticket_number; //counter
        $ticket_number = $attendee_number; //counter
        $data['ticket_counter_label'] = epl_get_element( '_epl_addit_regis_form_counter_label', $event_details, epl__( 'Attendee' ) ); //counter
        $data['price_name'] = $date_display != '' ? $date_display . ' - ' . $price_name : $price_name; //ticket name
        //if it is the ticket buyer form (the main required form)
        if ( $scope == 'ticket_buyer' ) {
            unset( $data['ticket_number'] );
            unset( $data['price_name'] );
        }

        $data['copy_link'] = false;
        if ( $scope == 'regis_forms' )
            $data['copy_link'] = (epl_get_element( '_epl_enable_form_to_form_copy', $event_details, 0 ) == 10 || epl_get_setting( 'epl_sc_options', 'epl_sc_form_to_form_copy', 0 ) == 10);

        $data['fields'] = '';
        $data['forms'] = '';
        $data['form'] = '';
        $data['email_fields'] = '';
        $data['email_body_form'] = '';

        $available_fields = ( array ) $this->ecm->get_list_of_available_fields(); //get the list of all available fields made with form manager

        $who_to_email = epl_get_setting( 'epl_registration_options', 'epl_send_customer_confirm_message_to', 1 );

        if ( $who_to_email == 2 || !epl_has_primary_forms() )
            $who_to_email = 2;

        foreach ( $forms as $form_id => $form_atts ) {

            $r = '';
            $data['fields'] = '';
            $data['email_fields'] = '';

            $epl_fields_inside_form = array_flip( $form_atts['epl_form_fields'] ); //get the field ids inside the form
            //when creating a form in form manager, the user may rearrange fields.  Find their desired order
            $epl_fields_to_display = $this->epl_util->sort_array_by_array( $available_fields, $epl_fields_inside_form );

            //for each field, there are attributes, like name, label, ....
            foreach ( $epl_fields_to_display as $field_id => $field_atts ) {

                if ( $field_atts['input_type'] == 'hidden' && !EPL_IS_ADMIN )
                    continue;

                if ( epl_get_element( 'admin_only', $field_atts, 0 ) == 10 && !epl_user_is_admin() && !EPL_IS_ADMIN )
                    continue;

                //if the field choices values are not given for select, radio, or checkbox
                //we will use field labels as values
                if ( !array_filter( ( array ) $field_atts['epl_field_choice_value'], 'trim' ) ) {
                    $options = $field_atts['epl_field_choice_text'];
                } //else we will combine the field values and choices into an array for use in the dropdown, or radio or checkbox
                else {
                    $options = array_combine( $field_atts['epl_field_choice_value'], $field_atts['epl_field_choice_text'] );
                }


                //this will give the ability to select more than one option, for checkboxes and later, selects
                $adjuster = ($field_atts['input_type'] == 'checkbox') ? '[]' : '';

                $_price_id_adjuster = (!is_null( $price_id ) ) ? "[{$price_id}]" : null;
                $event_id_adjuster = "[{$event_id}]";
                /* $_val = ($vals != ''
                  && epl_get_element( $field_atts['input_name'], $vals ) && epl_get_element( $event_id, $vals[$field_atts['input_name']] ) )
                  ? epl_get_element( $ticket_number, (is_null( $_price_id_adjuster )? $vals[$field_atts['input_name']][$event_id]
                  : epl_get_element( $price_id, $vals[$field_atts['input_name']][$event_id] ) ) )
                  : null; */
                //echo "<pre class='prettyprint'>" . __LINE__ . "> $price_id " . print_r( $_price_id_adjuster, true ) . "</pre>";
                $_val = null;

                if ( $vals != '' ) {
                    if ( epl_get_element( $field_atts['input_name'], $vals ) ) {
                        //not sure why I had || in here

                        if ( isset( $vals[$field_atts['input_name']][$ticket_number] ) ) {

                            $_val = epl_get_element( $ticket_number, $vals[$field_atts['input_name']] );

                            /* if ( !is_null( $price_id ) ){
                              $_val = epl_get_element_m($ticket_number, $price_id, $_val );
                              echo "<pre class='prettyprint'>" . __LINE__ . ">$ticket_number " . print_r($_val, true). "</pre>";
                              } */
                        }
                        elseif ( !empty( $_POST['deleted_event'] ) ) {

                            //if this element is posted, that means an event was removed from the cart.  
                            //it will contain the event id, price id and the quantities.

                            $vals = $this->get_old_regis_values(); //get the values that were save in the db previously

                            foreach ( $_POST['deleted_event'] as $deleted_event_id => $deleted_data ) {
                                foreach ( $deleted_data as $deleted_price_id => $quantities ) {
                                    if ( $quantities == 0 )
                                        continue;
                                    if ( is_null( $_price_id_adjuster ) ) {
                                        $_val = epl_get_element( $ticket_number, $vals[$field_atts['input_name']][$deleted_event_id] );
                                    }
                                    else {
                                        $_val = epl_get_element( $ticket_number, epl_get_element( $deleted_price_id, $vals[$field_atts['input_name']][$deleted_event_id] ) );
                                    }

                                    if ( $_val != '' )
                                        break 2;
                                }
                            }
                        }
                        elseif ( epl_get_element( $event_id, $vals[$field_atts['input_name']] ) ) {

                            if ( is_null( $_price_id_adjuster ) ) {
                                $_val = epl_get_element( $ticket_number, $vals[$field_atts['input_name']][$event_id] );
                            }
                            else {
                                $_val = epl_get_element( $ticket_number, epl_get_element( $price_id, $vals[$field_atts['input_name']][$event_id] ) );
                            }
                        }
                    }
                }

                if ( $scope == 'ticket_buyer' || $scope == 'waitlist' ) {
                    $event_id_adjuster = '';
                }

                $args = array(
                    'input_type' => $field_atts['input_type'],
                    'input_name' => $field_atts['input_name'] . $event_id_adjuster . $_price_id_adjuster . "[{$ticket_number}]" . $adjuster,
                    'label' => $field_atts['label'],
                    'description' => $field_atts['description'] . ($field_atts['input_name'] == '4e794a6eeeb9a' ? $ur_specific : ''),
                    'required' => $field_atts['required'],
                    'validation' => epl_get_element( 'validation', $field_atts, '' ),
                    'options' => $options,
                    'value' => $_val,
                    'class' => 'epl_field epl_field-' . $field_atts['input_type'],
                    'data_attr' => array( 'ticket_no' => $ticket_number )
                );


                if ( empty( $_val ) ) {
                    $last_regis_data = $this->epl_get_last_regis_form_data_values( $ticket_number );

                    if ( !epl_is_empty_array( $last_regis_data ) ) {
                        $args['default_value'] = epl_get_element( $field_atts['input_name'], $last_regis_data );
                    }
                    elseif ( $ticket_number == 0 ) {
                        $def_val = apply_filters( 'epl_construct_form_default_value', $field_atts );

                        $args['default_value'] = (!is_array( $def_val ) && !is_null( $def_val )) ? $def_val : $field_atts['default_value'];
                    }
                    else
                        $args['default_value'] = $field_atts['default_value'];
                }

                if ( (($who_to_email == 1 && $ticket_number == 0) || $who_to_email == 2 ) ) {
                    if ( stripos( $field_atts['input_slug'], 'email' ) !== false ) {
                        $customer_email[$ticket_number] = $args['value'];
                        $has_email_field = true;
                    }
                    if ( stripos( $field_atts['input_slug'], 'first_name' ) !== false || stripos( $field_atts['input_slug'], 'last_name' ) !== false )
                        $customer_name[$ticket_number][$field_atts['input_slug']] = $args['value'];
                }
                //if overview, we don't want to display the field, just the value
                if ( $this->mode == 'overview' ) {
                    $args += ( array ) $this->overview_trigger;
                    unset( $args['required'] );
                }
                $data['el'] = $this->epl_util->create_element( $args, 0 );

                $data['fields'] .= $this->epl->load_view( $this->dest . '/registration/regis-field-row', $data, true );
                $data['email_fields'] .= $this->epl->load_view( $this->dest . '/registration/regis-email-field-row', $data, true );
            }
            $data['event_title'] = $event_details['post_title'];
            $data['form_label'] = (isset( $form_atts['epl_form_options'] ) && in_array( 0, ( array ) $form_atts['epl_form_options'] ) ? $form_atts['epl_form_label'] : '');
            $data['form_descr'] = (isset( $form_atts['epl_form_options'] ) && in_array( 10, ( array ) $form_atts['epl_form_options'] ) ? $form_atts['epl_form_descritption'] : '');


            $r = $this->epl->load_view( $this->dest . '/registration/regis-form-wrap', $data, true );
            $data['form'] .= $r;

            $data['email_body_form'] = $this->epl->load_view( $this->dest . '/registration/regis-email-form-wrap', $data, true );
            $email_regis_form .= $data['email_body_form'];
        }


        if ( $scope == 'ticket_buyer' || !epl_has_primary_forms() ) {

            /*
             * - if enabled for all events
             * - and not set to no for this event
             * - or set to yes for this event
             */

            $global_newsletter_ok = false;

            if ( epl_get_setting( 'epl_api_option_fields', 'epl_mc_key' ) != '' && epl_get_setting( 'epl_api_option_fields', 'epl_mc_action' ) != 0 && epl_get_element( '_epl_offer_notification_sign_up', $event_details ) != 0 ) {

                $global_newsletter_ok = true;
            }

            if ( $has_email_field && epl_get_setting( 'epl_api_option_fields', 'epl_mc_action' ) != 0 && (epl_get_element( '_epl_offer_notification_sign_up', $event_details ) == 1 && $global_newsletter_ok) ) {

                $_newsletter_signup = array(
                    'input_type' => 'select',
                    'input_name' => "newsletter_signup[{$ticket_number}]",
                    'label' => epl_get_setting( 'epl_api_option_fields', 'epl_mc_permission_label' ),
                    'options' => epl_yes_no(),
                    'value' => epl_get_element( $ticket_number, $this->current_data[$this->regis_id]['newsletter_signup'] ),
                    'class' => 'epl_w70'
                );
                $data['el'] = $this->epl_util->create_element( $_newsletter_signup + ( array ) $this->overview_trigger, 0 );
                $data['form'] .= '<div class="epl_section epl_regis_field_wrapper regis_form">' . $this->epl->load_view( $this->dest . '/registration/regis-field-row', $data, true ) . '</div>';
            }
        }
        //if ( $event_details[''] )
        //  $copy_from = '';
        if ( ($this->mode != 'overview' && $data['copy_link']) || $this->on_admin )
            $copy_from = '<a href="#" style="float:right;" class="epl_copy_from epl_button_small">' . epl__( 'Copy From' ) . '</a>';


        $lookup_form = (epl_get_element( 'epl_m', $_POST, 0 ) == 0 && epl_um_is_enabled() && epl_user_is_admin() && $this->mode == 'edit' ) ? ' <a href="#"  style="float:right;" class="open_lookup_form epl_button_small">' . epl__( 'Lookup' ) . '</a>' : '';

        if ( $ticket_number == 0 ) {
            $edit_profile_link = apply_filters( 'epl_edit_profile_link', null );
            $ticket_buyer_legend = apply_filters( 'epl_ticket_buyer_form_legend', epl__( 'Primary Registrant' ) );
            $r = "<div id='epl_form_section--0' class='epl_regis_attendee_wrapper'><fieldset class='epl_fieldset'><legend>" . $ticket_buyer_legend . ' ' . $edit_profile_link . "</legend>" . $lookup_form . $data['form'] . '</fieldset></div>';
        }
        else {
            $delete_att = '';

            if ( EPL_IS_ADMIN ) {
                $delete_att = "<a href='#' class='epl_button_small epl_admin_del_attendee' data-event_id='{$event_details['ID']}' data-price_id='$price_id' data-ticket_no={$ticket_number}>" . epl__( "Delete" ) . "</a>";
                $data['form'] .= "<input type='hidden' class='epl_ticket_no-{$event_id}-{$price_id}' value='{$ticket_number}' />";
            }
            $r = "<div id='epl_form_section--" . (isset( $ticket_number ) ? $event_id . '-' . $price_id . '-' . $ticket_number : 0) . "' class='epl_regis_attendee_wrapper'><fieldset class='epl_fieldset'><legend>" . $data['ticket_counter_label'] . ' ' . $ticket_number . ': ' . $data['price_name'] . " - {$event_details['post_title']}</legend> {$lookup_form} $copy_from $delete_att" . $data['form'] . '</fieldset></div>';
        }

        $ticket_number++;

        return $r;
    }


    function epl_get_last_regis_form_data_values( $ticket_no = 0, $user_id = '' ) {
        if ( (!is_user_logged_in()) || (epl_user_is_admin() && !$user_id) )
            return null;

        $form_data = wp_cache_get( 'epl_last_regis_form_data_' . $ticket_no . $user_id );

        if ( $form_data !== false )
            return $form_data;

        $user_id = is_numeric( $user_id ) ? $user_id : get_current_user_id();

        global $wpdb;

        $registrations = $wpdb->get_results(
                $wpdb->prepare( "SELECT meta_key 
                    FROM {$wpdb->usermeta} 
                        WHERE user_id=%d 
                        AND meta_key like %s 
                        ORDER BY umeta_id", $user_id, '_epl_regis_post_id_%' )
        );

        if ( !$registrations )
            return $registrations;

        $regis_ids = array();
        foreach ( $registrations as $regis )
            $regis_ids[] = str_replace( '_epl_regis_post_id_', '', $regis->meta_key );

        $regis_ids = implode( ',', $regis_ids );

        $form_data = $wpdb->get_row( "SELECT f.field_id, f.value 
            FROM {$wpdb->epl_regis_form_data} f
                INNER JOIN {$wpdb->epl_registration} r
                    ON r.regis_id = f.regis_id
                WHERE f.regis_id IN ({$regis_ids}) 
                    AND form_no={$ticket_no}
                    AND (r.status >=2 AND r.status <=5) 
                    ORDER BY r.regis_date DESC
                    LIMIT 1" );

        if ( $form_data ) {
            $this->epl->load_model( 'epl-report-model' );
            $form_data = EPL_report_model::get_instance()->get_form_data_array( $form_data->field_id, $form_data->value );
        }
        wp_cache_add( 'epl_last_regis_form_data_' . $ticket_no . $user_id, $form_data );

        return $form_data;
    }


//move to common helper
    function avail_spaces( $cap, $num_regis ) {

        if ( $cap === '' || $cap == 0 )
            return $cap;

        $avail = intval( $cap - epl_nz( $num_regis, 0 ) );
        return $avail <= 0 ? 0 : $avail;
    }


    function capacity_per() {
        global $event_details;
        return $event_details['_epl_event_capacity_per'];
    }


    function _trigger( $step, $trigger = 'save_to_db' ) {


        /*
         * check what step and when to save
         * check what step and when to email
         * -if saving data to the db, also update the capacity
         */

        $opt = get_option( 'events_planner_registration_options' );

        switch ( $trigger )
        {

            case 'db_update_event_capacity':

                break;
        }
    }


    function add_registration_to_db( $meta = array() ) {

        //get_the_event_prices();
        $meta = $this->current_data;

        global $wpdb, $event_details, $multi_time, $multi_price, $epl_is_waitlist_flow, $epl_current_step, $event_totals, $cart_totals;

        if ( epl_is_empty_array( $event_details ) )
            $this->ecm->setup_event_details( intval( epl_get_element( 'event_id', $_REQUEST ) ) );

        $event_id = $this->get_current_event_id();

        //have to create the dates manually as on some servers the 
        //wp_insert_post would set the dates back to UTC
        $now = date_i18n( "Y-m-d H:i:s" );
        $_post = array(
            "post_date" => date_i18n( "Y-m-d H:i:s" ),
            "post_date_gmt" => get_gmt_from_date( $now ),
            'post_type' => 'epl_registration',
            'post_title' => $this->regis_id,
            'post_content' => '',
            'post_status' => 'publish'
        );
        if ( is_user_logged_in() ) {
            $current_user = wp_get_current_user();
            $_post['post_author'] = $current_user->ID;
        }

        $_post = apply_filters( 'erm__add_registration_to_db__post_array', $_post );
        //$_db_insert_method = 'update_post_meta';
        $_db_insert_method = 'add_post_meta'; //as of 1.3, faster to delete everything and add than update_post_meta
        $regis_post_ID = null;
        if ( isset( $this->current_data['post_ID'] ) ) {
            //If this post is already in the db, the meta will be deleted before
            $_post['ID'] = intval( $this->current_data['post_ID'] );
            $regis_post_ID = $_post['ID'];

            //as of 1.3, much faster to delete and insert then to update_post_meta

            $wpdb->query( $wpdb->prepare(
                            "DELETE FROM  $wpdb->postmeta 
                        WHERE post_id = %d 
                        AND (NOT meta_key = '_epl_payment_note' 
                        AND NOT meta_key = '_epl_regis_note' 
                        AND NOT meta_key = '_epl_regis_incr_id')", $_post['ID'] ) );
        }
        else {
            $regis_key = epl_get_element( '_regis_id', $_SESSION['__epl'], null );
            if ( $regis_key ) {

                $regis_post_ID = $wpdb->get_var(
                        $wpdb->prepare( "SELECT ID FROM {$wpdb->posts}
                 WHERE post_status ='publish' 
                 AND post_type='epl_registration' 
                 AND post_title=%s
                        ORDER BY ID DESC
                        LIMIT 1", $regis_key )
                );
            }

            if ( is_null( $regis_post_ID ) ) {
                //create new regis record.
                $regis_post_ID = wp_insert_post( $_post );
                $this->current_data['post_ID'] = $regis_post_ID;
            }
            //$_db_insert_method = 'add_post_meta';
        }
        $this->current_data['post_ID'] = $regis_post_ID;
        $db_data = array();

        //get the attendee and money totals for the cart
        $_totals = $this->calculate_cart_totals( true );


        $start_week = epl_get_element_m( '_epl_start_week', $this->regis_id, $meta, 0 );



        $attendance_dates_total = array();
        $_price_type = ''; //temp holder



        $events_in_cart = $this->get_events_in_cart();

        $grand_total = $cart_totals['money_totals']['grand_total'];
        $original_total = $cart_totals['money_totals']['original_total'];

        $db_data[] = array( $regis_post_ID, '_epl_original_total', $original_total );
        $db_data[] = array( $regis_post_ID, '_epl_grand_total', $grand_total );
        $db_data[] = array( $regis_post_ID, '_epl_pre_discount_total', epl_get_element( 'pre_discount_total', $cart_totals['money_totals'], $grand_total ) );
        $db_data[] = array( $regis_post_ID, '_epl_discount_amount', epl_get_element( 'discount_amount', $cart_totals['money_totals'], 0 ) );
        $db_data[] = array( $regis_post_ID, '_epl_discount_code_id', epl_get_element( 'discount_code_id', $cart_totals['money_totals'], '' ) );
        $db_data[] = array( $regis_post_ID, '_epl_discount_code_source', epl_get_element( 'discount_code_source', $cart_totals['money_totals'], '' ) );
        $db_data[] = array( $regis_post_ID, '_epl_discount_code', epl_get_element( 'discount_code', $cart_totals['money_totals'], '' ) );
        $db_data[] = array( $regis_post_ID, '_epl_surcharge', epl_get_element( 'surcharge', $cart_totals['money_totals'], 0 ) );
        $db_data[] = array( $regis_post_ID, '_epl_subtotal', epl_get_element( 'subtotal', $cart_totals['money_totals'], 0 ) );


        foreach ( $events_in_cart as $event_id => $totals ) {
            setup_event_details( $event_id );
            $event_dates = epl_get_element( '_epl_start_date', $event_details );
            $event_dates_keys = array_keys( $event_dates );
            $event_type = epl_get_element( '_epl_event_type', $event_details );

            $db_data[] = array( $regis_post_ID, '_epl_original_total_' . $event_id, $_totals[$event_id]['money_totals']['original_total'] );
            $db_data[] = array( $regis_post_ID, '_epl_grand_total_' . $event_id, $_totals[$event_id]['money_totals']['grand_total'] );
            $db_data[] = array( $regis_post_ID, '_epl_pre_discount_total_' . $event_id, epl_get_element( 'pre_discount_total', $_totals[$event_id]['money_totals'], 0 ) );
            $db_data[] = array( $regis_post_ID, '_epl_discount_amount_' . $event_id, epl_get_element( 'discount_amount', $_totals[$event_id]['money_totals'], 0 ) );
            $db_data[] = array( $regis_post_ID, '_epl_surcharge_' . $event_id, epl_get_element( 'surcharge', $_totals[$event_id]['money_totals'], 0 ) );
            $db_data[] = array( $regis_post_ID, '_epl_subtotal_' . $event_id, epl_get_element( 'subtotal', $_totals[$event_id]['money_totals'], 0 ) );
            $db_data[] = array( $regis_post_ID, '_epl_discount_code_' . $event_id, epl_get_element( 'discount_code', $_totals[$event_id], '' ) );


            //add the total number of attendees for the whole event
            //add the number of attendees for each date in the cart.
            $dates = ( array ) $meta[$this->regis_id]['_dates']['_epl_start_date'][$event_id];
            $times = epl_get_element( $event_id, epl_get_element( '_epl_start_time', $meta[$this->regis_id]['_dates'] ), epl_get_element( '_epl_start_time', $event_details ) );
            $prices = $meta[$this->regis_id]['_dates']['_att_quantity'][$event_id];
            //$total_att = array_sum( ( array ) $meta[$this->regis_id]['_dates']['_att_quantity'][$event_details['ID']] );

            $start_week = epl_get_element_m( '_epl_start_week', $this->regis_id, $meta, 0 );

            $pack_regis = (epl_get_element( '_epl_pack_regis', $event_details ) == 10);
            $pack_regis_consecutive = (epl_get_element( '_epl_pack_regis_consecutive', $event_details, 10 ) == 10);

            $rolling_regis = (epl_get_element( '_epl_rolling_regis', $event_details ) == 10);



            $discount_code_id = epl_get_element( 'discount_code_id', $totals['money_totals'], null );

            if ( !is_null( $discount_code_id ) )
                $db_data[] = array( $regis_post_ID, '_epl_discount_id_' . $discount_code_id, 1 );

            foreach ( $dates as $_dkey => $_date_id ) {
                //if multiple dates, find the qty for a specific date.

                $date_total_att = 0;
                $time_total_att = 0;

                $dg = epl_get_element_m( $_dkey, '_epl_date_group_no', $event_details, false );

                if ( $event_type == 20 ) {
                    $_date_id = current( $_date_id );
                }

                foreach ( $times as $_tkey => $_time_id ) {
                    $time_total_att = 0;

                    foreach ( $prices as $_pkey => $_price_id ) {

                        $price_type = epl_get_element( $_pkey, epl_get_element( '_epl_price_type', $event_details ), 'att' );


                        if ( $_price_type == '' || $_price_type != $price_type ) {
                            $_price_type = $price_type;
                            //$time_total_att = 0;
                        }


                        $price_att_qty = $meta[$this->regis_id]['_dates']['_att_quantity'][$event_id][$_pkey];

                        /*
                         * if date level price
                         * - for each price key,
                         * -- combine dates listed in the price key with the dates in teh cart
                         */
                        if ( epl_is_date_level_price() && !isset( $price_att_qty[$_date_id] ) ) {
                            continue;
                        }


                        //$price_att_qty = array_sum( ( array ) $meta[$this->regis_id]['_dates']['_att_quantity'][$event_id][$_pkey] );
                        //will only insert attendee price type
                        $price_qty_meta_key = "_total_{$price_type}_" . $event_id . '_price_' . $_date_id . '_' . $_time_id . '_' . $_pkey;

                        $offset_another = false;

                        $offset_another_key = epl_get_element_m( $_pkey, '_epl_price_to_offset', $event_details );
                        $offset_another_count = intval( epl_get_element_m( $_pkey, '_epl_price_offset_count', $event_details, 0 ) );

                        if ( $offset_another_key && $offset_another_count > 0 ) {
                            $offset_another = true;
                            $price_qty_offset_meta_key = "_total_{$price_type}_" . $event_id . '_price_' . $_date_id . '_' . $_time_id . '_' . $offset_another_key;
                        }

                        if ( epl_is_time_specific_price( $_pkey ) ) {
                            // $price_att_qty = $meta[$this->regis_id]['_dates']['_att_quantity'][$event_id][$_pkey];

                            if ( $price_att_qty > 0 && $event_details['_epl_price_parent_time_id'][$_pkey] == $_time_id ) {
                                $time_total_att += $price_att_qty;
                                $db_data[] = array( $regis_post_ID, $price_qty_meta_key, $price_att_qty );
                                if ( $offset_another ) {
                                    $db_data[] = array( $regis_post_ID, $price_qty_offset_meta_key, $offset_another_count );

                                    if ( $offset_another_key != $_pkey )
                                        $time_total_att += ($offset_another_count - $price_att_qty);
                                }
                            }
                        }
                        else {

                            if ( epl_is_date_level_price() )
                                $price_att_qty = $price_att_qty[$_date_id];
                            else
                                $price_att_qty = array_sum( ( array ) $price_att_qty );

                            if ( !epl_is_date_level_time() ) {
                                $time_total_att += $price_att_qty;
                                $db_data[] = array( $regis_post_ID, $price_qty_meta_key, $price_att_qty );
                                if ( $offset_another && $price_att_qty > 0 ) {
                                    $db_data[] = array( $regis_post_ID, $price_qty_offset_meta_key, $offset_another_count );

                                    if ( $offset_another_key != $_pkey )
                                        $time_total_att += ($offset_another_count - $price_att_qty);
                                }
                            }
                            else {
                                if ( $_date_id == $_tkey ) {
                                    $time_total_att += $price_att_qty;
                                    $db_data[] = array( $regis_post_ID, $price_qty_meta_key, $price_att_qty );
                                    if ( $offset_another && $price_att_qty > 0 ) {
                                        $db_data[] = array( $regis_post_ID, $price_qty_offset_meta_key, $offset_another_count );
                                        if ( $offset_another_key != $_pkey )
                                            $time_total_att += ($offset_another_count - $price_att_qty);
                                    }
                                }
                            }
                        }

                        if ( $price_type != 'att' ) {

                            $time_total_att -= $price_att_qty;
                        }

                        if ( $_price_type != $price_type ) {
                            $_price_type = $price_type;

                            //$time_total_att -= $price_att_qty;
                        }


                        $pack_size = epl_get_element_m( $_pkey, '_epl_price_pack_size', $event_details, null );

                        if ( $price_att_qty > 0 && $pack_size && $pack_regis && $pack_regis_consecutive ) {

                            $attendance_start_date = $event_dates[$_date_id];

                            //find the position of the date in the cart
                            $pos = array_search( $_date_id, $event_dates_keys );

                            if ( $start_week == 1 ) {

                                $_new_date_key = epl_get_element( $pos++, $event_dates_keys );
                                $att_start_date = epl_get_element( $_new_date_key, $event_dates );
                            }

                            $attendance_dates = array_slice( $event_dates, $pos, $pack_size, true );
                            $attendance_dates_total[$_date_id][$_time_id][$_pkey]['count'] = $price_att_qty;
                            $attendance_dates_total[$_date_id][$_time_id][$_pkey]['dates'] = $attendance_dates;


                            $attendance_end_date = end( $attendance_dates );


                            for ( $i = 1; $i <= $price_att_qty; $i++ ) {
                                $price_pack_meta_key = "_pack_attendance_dates_" . $event_id . '_' . $_pkey . '_' . $i;
                                $db_data[] = array( $regis_post_ID, $price_pack_meta_key, $attendance_dates );
                            }
                        }
                    }

                    if ( $time_total_att > 0 ) {
                        $time_qty_meta_key = "_total_att_" . $event_id . '_time_' . $_date_id . '_' . $_time_id;

                        if ( !epl_is_date_level_time() ) {
                            $date_total_att += $time_total_att;

                            if ( !$pack_regis || !$pack_regis_consecutive )
                                $db_data[] = array( $regis_post_ID, $time_qty_meta_key, $time_total_att );
                        }
                        else {
                            if ( $_date_id == $_tkey ) {
                                $date_total_att += $time_total_att;
                                $db_data[] = array( $regis_post_ID, $time_qty_meta_key, $time_total_att );
                            }
                        }
                    }
                }
                //date
                if ( $date_total_att > 0 ) {

                    $date_qty_meta_key = "_total_att_" . $event_id . '_date_' . $_date_id;

                    if ( epl_is_waitlist_flow() || epl_is_waitlist_record() ) {

                        if ( !$pack_regis ) {
                            $date_waitlist_qty_meta_key = "_total_waitlist_att_" . $event_id . '_date_' . $_date_id;
                        }

                        $db_data[] = array( $regis_post_ID, $date_waitlist_qty_meta_key, $date_total_att );
                    }
                    elseif ( !$pack_regis || !$pack_regis_consecutive )
                        $db_data[] = array( $regis_post_ID, $date_qty_meta_key, $date_total_att );
                }
            }

            $qty_meta_key = "_total_att_" . $event_id;
            $total_att = $totals['_att_quantity']['total'][$event_id];

            if ( !epl_is_waitlist_flow() )
                $db_data[] = array( $regis_post_ID, $qty_meta_key, $total_att );


            $db_data[] = array( $regis_post_ID, '_epl_event_id', $event_id );
            do_action( 'epl_erm__add_registration_to_db', $regis_post_ID, $event_id );
        }


        //store the whole session, useful for admin side or future edit
        //also store individual ones for easier data access and queries.
        //update_post_meta( $regis_post_ID, '_grand_total', $meta[$this->regis_id]['grand_total'] );
        //removing in 1.4
        // $db_data[] = array( $regis_post_ID, '_epl_events', $this->epl_util->clean_input( $meta[$this->regis_id]['_events'] ) );
        //$db_data[] = array( $regis_post_ID, '_epl_dates', $this->epl_util->clean_input( $meta[$this->regis_id]['_dates'] ) );
        //$db_data[] = array( $regis_post_ID, '_epl_attendee_info', $this->epl_util->clean_input( $meta[$this->regis_id]['_attendee_info'] ) );

        $db_data[] = array( $regis_post_ID, '_epl_plugin_version', EPL_PLUGIN_VERSION ); //TODO - move this to run just once
        $db_data[] = array( $regis_post_ID, '_epl_payment_method', $this->get_payment_profile_id() );
        $db_data[] = array( $regis_post_ID, '_epl_regis_user_id', get_current_user_id() );

        $gateway_info = $this->get_gateway_info();


        if ( $epl_current_step == 'show_cart_overview' || $epl_current_step == 'payment_page' ) {
            $this->update_payment_data( array(
                'post_ID' => $regis_post_ID,
                '_epl_regis_status' => 1,
                '_epl_grand_total' => $grand_total,
                '_epl_payment_method' => $this->get_payment_profile_id() ) );
        }

        $total_counts = array();


        if ( $pack_regis && $pack_regis_consecutive ) {

            $totals_data = array();
            //get readdy to VOMIT :)
            foreach ( $attendance_dates_total as $date_key => $date_data ) {

                foreach ( $date_data as $time_key => $time_data ) {

                    foreach ( $time_data as $price_key => $price_dates ) {

                        foreach ( $price_dates['dates'] as $_date_key => $timestamp ) {

                            if ( isset( $totals_data["_total_att_" . $event_id . '_date_' . $_date_key] ) ) {
                                $totals_data["_total_att_" . $event_id . '_date_' . $_date_key]+=$price_dates['count'];
                                $totals_data["_total_att_" . $event_id . '_time_' . $_date_key . '_' . $time_key]+=$price_dates['count'];
                            }
                            else {
                                $totals_data["_total_att_" . $event_id . '_date_' . $_date_key] = $price_dates['count'];
                                $totals_data["_total_att_" . $event_id . '_time_' . $_date_key . '_' . $time_key] = $price_dates['count'];
                            }
                        }
                    }
                }
            }

            foreach ( $totals_data as $db_ky => $count ) {
                $db_data[] = array( $regis_post_ID, $db_ky, $count );
            }
        }
        $db_data[] = array( $regis_post_ID, '__epl', $this->epl_util->clean_input( $meta ) );

        /*
         * as of 1.4, switching to one insert call with multiple values instead of add_post_meta which will run 
         * an insert statement for each key.  Much faster.  Doing add_post_meta for array
         * as sanitized input (slashes) will mess up the serialize/unserialize.
         */

        $i = '';
        foreach ( $db_data as $k => $values ) {
            if ( is_array( $values[2] ) ) {

                add_post_meta( $regis_post_ID, $values[1], $values[2] );
            }
            else
                $i .= "($values[0],'$values[1]', '$values[2]'),";
        }

        $i = substr( $i, 0, -1 );


        global $wpdb;

        $i = "INSERT INTO {$wpdb->postmeta} (post_id,meta_key,meta_value) VALUES $i";

        $i = $wpdb->query( $i );

        $this->current_data['post_ID'] = $regis_post_ID;

        $this->refresh_data();
    }


    function assign_incremental_id( $post_ID = null, $force = false ) {

        global $wpdb;

        $assign_incremental_id = apply_filters( 'assign_incremental_id', true );

        if ( $assign_incremental_id === false )
            return false;

        $highest_increment = $wpdb->get_row( "SELECT meta_value FROM {$wpdb->postmeta} WHERE meta_key = '_epl_regis_incr_id' ORDER by meta_id DESC LIMIT 1" );
        $next_num = epl_nz( $highest_increment->meta_value, 0 );
        $next_num++;

        if ( $post_ID ) {
            if ( get_post_meta( $post_ID, '_epl_regis_incr_id', true ) == '' )
                add_post_meta( $post_ID, '_epl_regis_incr_id', $next_num );
        }
        else {

            $args = array(
                'post_type' => array( 'epl_registration' ),
                'post_status' => array( 'publish', 'private' ),
                'posts_per_page' => -1,
                'orderby' => 'date',
                'order' => 'ASC',
                'meta_query' => array(
                    /* array(
                      'key' => '_epl_regis_status',
                      'value' => array( 2, 5 ),
                      'compare' => 'IN',
                      ), */
                    array(
                        'key' => '_epl_regis_incr_id',
                        'compare' => 'NOT EXISTS',
                    )
                )
            );


            if ( $post_ID )
                $args['p'] = intval( $post_ID );

            $query = new WP_Query( $args );
            if ( $query->have_posts() ) {
                while ( $query->have_posts() ) {
                    $query->the_post();

                    add_post_meta( get_the_ID(), '_epl_regis_incr_id', $next_num );
                    $next_num++;
                }
            }
            wp_reset_postdata();
        }
    }


    function ok_to_proceed( $force_check = false, $event_id = null ) {

        global $event_details, $current_att_count, $event_snapshot, $epl_error;
        setup_event_details( $event_id );
        $_response = '';
        $_errors = array();


        $event_cart_details = $this->get_events_in_cart( $event_id );

        foreach ( $event_cart_details as $event_id => $_totals ) {
            if ( $_totals == 0 )
                continue;
            $total_att = epl_get_element_m( $event_id, 'total', epl_get_element( '_att_quantity', $_totals, array() ) );

            if ( $total_att == 0 && (epl_regis_flow() <= 2 || $force_check ) ) {
                $_errors[] = array( '', epl__( 'Please select a quantity.' ) );
            }

            $cart_dates = $this->get_the_cart_dates();


            if ( epl_is_empty_array( epl_get_element( $event_id, $cart_dates, array() ) ) && epl_get_element( 'cart_action', $_REQUEST ) != 'add' ) {
                $_errors[] = array( '', epl__( 'Please select at least one date.' ) );
            }
            $_snapshot = $this->event_snapshot( $event_id );

            if ( isset( $_snapshot[$event_id]['error'] ) ) {
                $_errors = array_merge( $_errors, $_snapshot[$event_id]['error'] );
            }


            $_errors = apply_filters( 'epl_erm__ok_to_proceed__var_errors', $_errors );

            if ( !epl_is_empty_array( $_errors ) ) {
                $tmpl = array( 'table_open' => '<table border="1" cellpadding="2" cellspacing="1" class="epl_error">' );
                $this->epl_table->set_template( $tmpl );
                $this->epl_table->set_heading( '', '' );
                $_response = $this->epl_table->generate( $_errors );
                $this->epl_table->clear();
                $this->epl->epl_util->set_response_param( 'is_ok_for_waitlist', true ); //epl_is_waitlist_flow());                
            }
        }
        $this->ok_to_proceed = $_response;

        if ( $_response !== '' )
            $this->epl->epl_util->set_response_param( 'cart_errors_present', 1 );
        return $_response;
    }


    /**
     * This function will create a global variable called $event_snapshot, which holds all the following information about the event
     * -Availability for each date, each time inside each date, each price inside each date and time.
     * -Availability errors
     *
     * Uses global vars $event_details, $current_att_count
     *
     * @since 1.0.0
     * @param event_id
     * @param refresh
     * @return  Sets the global $event_snapshot variable
     */
    function event_snapshot( $event_id = null, $refresh = false ) {


        global $event_details, $capacity, $current_att_count, $event_snapshot, $epl_error, $event_totals;



        $event_id = is_null( $event_id ) ? $this->get_current_event_id() : $event_id;

        setup_event_details( $event_id );

        $meta = $this->current_data[$this->regis_id];
        $cart_selected_dates = epl_get_element( $event_id, epl_get_element( '_epl_start_date', $this->get_cart_values( '_dates' ) ) );
        $cart_selected_times = ( array ) epl_get_element( $event_id, epl_get_element( '_epl_start_time', $this->get_cart_values( '_dates' ) ) );
        $cart_selected_quantities = ( array ) epl_get_element( $event_id, epl_get_element( '_att_quantity', $this->get_cart_values( '_dates' ) ) );

        static $_cache = array();

        $_is_cached = epl_get_element( $event_id, $_cache );

        if ( $_is_cached )
            return $_cache[$event_id];

        if ( empty( $cart_selected_dates ) && epl_get_element( 'cart_action', $_REQUEST ) != 'add' ) {
            $epl_error[] = array( '', epl__( 'Please select at least one date.' ) );
        }

        $current_att_count = EPL_report_model::get_instance()->get_attendee_counts( $event_id, true );

        $sold_out_text = apply_filters( 'merm__event_snapshot__sold_out_text', epl__( 'Sold Out.' ) );

        //get the attendee and money totals
        //$_totals = $this->calculate_cart_totals();
        setup_event_details( $event_id );

        $grand_total = epl_get_element_m( 'grand_total', 'money_totals', $event_totals );
        $grand_total_key = "_grand_total";

        //this will hold the snapshot
        $event_snapshot = array();

        $qty_meta_key = "_total_att_" . $event_id;
        //$total_att = array_sum( ( array ) $meta[$this->regis_id]['_dates']['_att_quantity'][$event_details['ID']] );
        $total_att = epl_get_element_m( $event_id, 'total', epl_get_element( '_att_quantity', $event_totals ) );

        //event dates, times and prices
        $dates = epl_get_element( '_epl_start_date', $event_details );
        $times = epl_get_element( '_epl_start_time', $event_details );
        $prices = epl_get_element( '_epl_price_name', $event_details );

        $rolling_regis = (epl_get_element( '_epl_rolling_regis', $event_details ) == 10);

        if ( epl_is_empty_array( $dates ) )
            return;


        //foreach event date
        foreach ( $dates as $_date_key => $date_timestamp ) {
            $date_timestamp = epl_get_date_timestamp( $date_timestamp );
            //number registered for the date
            $date_total_att = 0;

            $_date = epl_formatted_date( $event_details['_epl_start_date'][$_date_key], 'Y-m-d', 'date' );

            //the date to display
            $_displ_date = epl_formatted_date( $_date );


            $qty_meta_key = "_total_att_" . $event_details['ID'] . '_date_' . $_date_key;

            //find the capacity for this date.
            $cap = $event_details['_epl_date_capacity'][$_date_key];

            //find the number of people regitered for this date
            $num_att = epl_get_element( $qty_meta_key, $current_att_count, 0 );
            //find the available spcaes.  If there is no capacity, always available
            $date_avail = $this->avail_spaces( $cap, $num_att );

            $_past = epl_compare_dates( EPL_TIME, $_date . ' 23:59:59', ">" );

            $_date_avail_display = epl_is_ok_to_register( $event_details, $_date_key );
            $_date_avail_display = ($_date_avail_display === true) ? epl__( 'Available' ) : $_date_avail_display;
            //snapshot template
            $_t = array(
                'timestamp' => $date_timestamp,
                'disp' => $_displ_date,
                'avail' => $date_avail,
                'avail_display' => $_date_avail_display,
                'regis' => $num_att,
                'db_key' => $qty_meta_key,
                'cart' => 0,
                'past' => $_past,
                'hide' => ($date_avail != '' && $date_avail <= 0)
            );

            //Set the snapshot for this date
            $event_snapshot[$event_id][$_date_key]['date'] = $_t;
            $rolling_regis_time_avail = 0;
            //foreach time available for the event
            foreach ( $times as $_time_key => $_time_id ) {

                $time_total_att = 0;


                $_time = $event_details['_epl_start_time'][$_time_key];

                $qty_meta_key = "_total_att_" . $event_details['ID'] . '_time_' . $_date_key . '_' . $_time_key;

                //$cap = $capacity['time'][$_time_key];
                $cap = epl_get_element( $_time_key, epl_get_element( '_epl_time_capacity', $event_details ), '' );

                if ( $rolling_regis && $cap == '' )
                    $cap = epl_get_element_m( $_date_key, '_epl_date_per_time_capacity', $event_details );


                $num_att = epl_nz( epl_get_element( $qty_meta_key, $current_att_count ), 0 );
                $time_avail = $this->avail_spaces( $cap, $num_att );

                $rolling_regis_time_avail += ($cap == '' ? 999 : epl_nz( $time_avail, 0 ));

                $_comp_time = epl_get_element( $_time_key, $event_details['_epl_regis_endtime'] );

                $_comp_time = (!$_comp_time) ? $_time : $_comp_time;
                //Is this time for this date in the past and not available any more?

                $_past = epl_compare_dates( EPL_TIME, $_date . ' ' . $_comp_time, ">" );

                if ( $rolling_regis && $_past ) {
                    $event_snapshot[$event_id][$_date_key]['date']['hide'] = true;
                }
                $_t = array(
                    'timestamp' => strtotime( $times[$_time_key], $date_timestamp ),
                    'disp' => $times[$_time_key],
                    'avail' => $time_avail,
                    'avail_display' => $_date_avail_display,
                    'regis' => $num_att,
                    'db_key' => $qty_meta_key,
                    'past' => $_past
                );

                //Set the snapsot for this time for this date
                $event_snapshot[$event_id][$_date_key]['time'][$_time_key] = $_t;


                foreach ( $prices as $_price_key => $_price_id ) {
                    $_price = $event_details['_epl_price_name'][$_price_key];
                    $price_avail = 0;
                    $do_count = true;
                    $price_att = 0;


                    $price_type = epl_get_element( $_price_key, epl_get_element( '_epl_price_type', $event_details ), 'att' );

                    if ( isset( $meta['_dates']['_att_quantity'][$event_details['ID']] ) ) {
                        if ( is_array( epl_get_element( $_price_key, $meta['_dates']['_att_quantity'][$event_details['ID']] ) ) )
                            $price_att = array_sum( ( array ) epl_get_element( $_price_key, $meta['_dates']['_att_quantity'][$event_details['ID']] ) );
                        else
                            $price_att = epl_get_element( $_price_key, $meta['_dates']['_att_quantity'][$event_details['ID']] );
                    }
                    $qty_meta_key = "_total_att_" . $event_details['ID'] . '_price_' . $_date_key . '_' . $_time_key . '_' . $_price_key;

                    if ( epl_is_date_level_price() ) {
                        $price_att = epl_get_element_m( $_date_key, $_price_key, $cart_selected_quantities );
                    }
                    $cap = epl_get_element( $_price_key, epl_get_element( '_epl_price_capacity', $event_details ), '' );

                    $num_att = epl_nz( epl_get_element( $qty_meta_key, $current_att_count ), 0 );
                    $price_avail = $this->avail_spaces( $cap, $num_att );

                    if ( !epl_is_empty_array( $offset = $this->is_offsetter_price( $_price_key ) ) && $cap !== '' && $price_avail > 0 ) {
                        //check to make sure users are not using the price as offset against itself
                        if ( $offset['offset_key'] != $_price_key ) {
                            // see if available count of the offseter is > capacity for this price
                            $offset_avail = $event_snapshot[$event_id][$_date_key]['time'][$_time_key]['price'][$offset['offset_key']]['avail'];
                            $offset_count = $offset['offset_count'];

                            if ( ( int ) $offset_count > ( int ) $offset_avail ) {
                                $price_avail = 0;
                            }
                            elseif ( $offset_avail >= $offset_count ) {
                                $price_avail = intval( $offset_avail / $offset_count );
                            }
                        }
                    }
                    //echo "<pre class='prettyprint'>" . __LINE__ . "> $event_id >>>> " . print_r($price_avail, true). "</pre>";
                    $time_total_att += $price_att;

                    $_t = array(
                        'disp' => $prices[$_price_key],
                        'avail' => $price_avail,
                        'avail_display' => $_date_avail_display,
                        'regis' => $num_att,
                        'db_key' => $qty_meta_key,
                        'cart' => $price_att,
                        'past' => $_past //same as time
                    );


                    $event_snapshot[$event_id][$_date_key]['time'][$_time_key]['price'][$_price_key] = $_t;


                    if ( epl_is_date_level_time() && epl_get_element( $_date_key, $cart_selected_times ) != $_time_key ) {
                        $do_count = false;
                    }

                    if ( epl_is_date_level_price() && !in_array( $_date_key, ( array ) $cart_selected_dates ) ) {
                        //if ( $_date_key != $_price_key ) {
                        $do_count = false;
                        //}
                    }

                    if ( epl_is_time_specific_price( $_price_key ) ) {

                        if ( $event_details['_epl_price_parent_time_id'][$_price_key] != $_time_key ) {

                            unset( $event_snapshot[$event_id][$_date_key]['time'][$_time_key]['price'][$_price_key] );
                            $do_count = false;
                        }
                    }

                    if ( (!epl_is_time_optonal() && !in_array( $_time_key, $cart_selected_times )) || !in_array( $_date_key, ( array ) $cart_selected_dates ) ) {
                        $do_count = false;
                    }

                    if ( !$do_count || $price_type != 'att' ) {
                        $time_total_att -= $price_att;

                        if ( isset( $event_snapshot[$event_id][$_date_key]['time'][$_time_key]['price'][$_price_key]['cart'] ) )
                            $event_snapshot[$event_id][$_date_key]['time'][$_time_key]['price'][$_price_key]['cart'] = 0;
                    }
                    else {
                        //check for a price availability for each date/time
                        if ( $price_att > 0 && $price_avail !== '' ) {
                            $_error = array();


                            if ( $price_avail === 0 || $price_avail < 0 ) {
                                $_error = array( $_displ_date . '<br />' . $_time . '<br />' . $_price, $sold_out_text );
                            }
                            elseif ( $price_att > epl_nz( $price_avail, 1000 ) ) {
                                $_error = array( $_displ_date . '<br />' . $_time . '<br />' . $_price, sprintf( epl__( ' Only %d spaces left.' ), $price_avail ) );
                            }
                            if ( !empty( $_error ) ) {
                                $epl_error[] = $_error;
                                $event_snapshot[$event_id]['error'][] = $_error;
                            }
                            $event_snapshot[$event_id][$_date_key]['time'][$_time_key]['price'][$_price_key]['avail_display'] = epl_get_element( 1, $_error );
                        }
                    }

                    /* if( $price_type != 'att' )
                      $time_total_att -= $price_att; */
                }

                $date_total_att += $time_total_att;
                $event_snapshot[$event_id][$_date_key]['time'][$_time_key]['cart'] = $time_total_att;
                //echo "<pre class='prettyprint'>" . __LINE__ . "> $_date "  . print_r($time_total_att, true). "</pre>";
                //check for time availablility for each date
                if ( $this->flow_mode == 'n' && !epl_is_time_optonal() && $time_total_att > 0 && ($time_avail !== '' || $event_snapshot[$event_id][$_date_key]['time'][$_time_key]['past'] === true) ) {
                    $_error = array();

                    if ( !epl_is_ongoing_event() && $event_snapshot[$event_id][$_date_key]['time'][$_time_key]['past'] == 1 ) {

                        $_error = array( $_displ_date . '<br />' . $_time, epl__( 'This time has passed.' ) );
                    }
                    if ( $time_avail === 0 || $time_avail < 0 ) {
                        $_error = array( $_displ_date . '<br />' . $_time, $sold_out_text );
                    }
                    elseif ( $time_total_att > epl_nz( $time_avail, 1000 ) ) {
                        $_error = array( $_displ_date . '<br />' . $_time, sprintf( epl__( 'Only %d spaces left.' ), $time_avail ) );
                    }
                    if ( !empty( $_error ) ) {
                        $epl_error[] = $_error;
                        $event_snapshot[$event_id]['error'][] = $_error;
                    }
                    $event_snapshot[$event_id][$_date_key]['time'][$_time_key]['avail_display'] = epl_get_element( 1, $_error );
                }
            }


            if ( $rolling_regis && $rolling_regis_time_avail == 0 && $event_snapshot[$event_id][$_date_key]['date'] == '' ) {

                $event_snapshot[$event_id][$_date_key]['date']['hide'] = true;
            }

            //check for the date availability
            $event_snapshot[$event_id][$_date_key]['date']['cart'] = $date_total_att;

            if ( $date_total_att > 0 && ($date_avail !== '' || $event_snapshot[$event_id][$_date_key]['date']['past'] === true) && (!epl_is_waitlist_approved() && !epl_is_waitlist_session_approved()) ) {
                $_error = array();

                if ( $this->flow_mode == 'n' && ($date_avail === 0 || $date_avail < 0 ) ) {
                    $_error = array( $_displ_date, $sold_out_text );

                    if ( epl_is_ok_for_waitlist() && ($wl_spaces_left = epl_waitlist_spaces_open()) !== false ) {


                        $_error[1] .= '<br />' . epl__( 'If you would like to be added to the waiting list, please click on the button below.  You will not be charged at this time.' );

                        if ( $wl_spaces_left !== true ) {
                            $_error[1] .= '<br />' . sprintf( 'Spaces available on the waiting list: %d', $wl_spaces_left );
                        }
                        if ( epl_waitlist_enough_spaces( $event_id ) == false ) {
                            $_error[1] .= '<br />' . sprintf( 'To continue, please select only %d.', $wl_spaces_left );
                        }
                        else {
                            $_error[1] .= '<br />' . epl_anchor( '#', epl__( 'Click here to add your name to the waitlist' ), null, " class='open_add_to_waitlist_form epl_button' data-event_id='$event_id'" );
                        }

                        $this->epl->epl_util->set_response_param( 'waitlist_form', '' );
                    }
                }
                elseif ( $this->flow_mode == 'n' && !epl_is_ongoing_event() && $event_snapshot[$event_id][$_date_key]['date']['past'] == 1 ) {

                    $_error = array( $_displ_date, epl__( 'This date has passed.' ) );
                }
                elseif ( $this->flow_mode == 'n' && $date_total_att > epl_nz( $date_avail, 1000 ) ) {
                    $_error = array( $_displ_date, sprintf( epl__( 'Only %d spaces left.' ), $date_avail ) );
                }
                $event_snapshot[$event_id][$_date_key]['date']['avail_display'] = epl_get_element( 1, $_error );
                if ( !empty( $_error ) ) {
                    $event_snapshot[$event_id]['error'][] = $_error;
                    $epl_error[] = $_error;
                }
            }
            //$this->epl_table->add_row( '', $event_details['_epl_start_date'][$_date_key], $avail );
        }
        $_cache[$event_id] = $event_snapshot;

        return $event_snapshot;
    }


    function is_offsetter_price( $price_key ) {
        global $event_details;

        $offset_another_key = epl_get_element_m( $price_key, '_epl_price_to_offset', $event_details );
        $offset_another_count = intval( epl_get_element_m( $price_key, '_epl_price_offset_count', $event_details ) );

        if ( $offset_another_key && $offset_another_count > 0 ) {
            return array(
                'offset_key' => $offset_another_key,
                'offset_count' => $offset_another_count
            );
        }

        return false;
    }


    function available_spaces_table( $event_id ) {

        $table_data = $this->event_snapshot( $event_id );

        $table_data = $table_data[$event_id];

        return $this->epl->load_view( 'front/cart/cart-available-spaces', array( 'table_data' => $table_data ), true );
    }

    /*
     * TODO - refactor, this is crap
     */


    function attendee_list_table( $args = array() ) {

        $defaults = array( 'event_id' => epl_get_element( 'event_id', $_REQUEST ) );

        $args = wp_parse_args( $args, $defaults );

        extract( $args );

        global $event_details;
        $this->ecm->set_event_regis_post_ids( $event_id );
        //$event_id = ( int ) (!isset( $args['event_id'] ) ? $_REQUEST['event_id'] : $event_id);
        $_totals = $this->ecm->get_event_regis_snapshot( $event_id );


        //if ( epl_is_empty_array( $event_details ) )
        $this->ecm->setup_event_details( $event_id );

        $display = epl_get_element( '_epl_show_attendee_list_template', $event_details, 'attendee-list-1' );

        if ( epl_get_element( '_epl_show_attendee_list_link', $event_details, 0 ) == 0 && !current_user_can( 'manage_options' ) )
            return null;


        $event_ticket_buyer_forms = array_flip( ( array ) $event_details['_epl_primary_regis_forms'] );
        $event_addit_forms = (isset( $event_details['_epl_addit_regis_forms'] ) && $event_details['_epl_addit_regis_forms'] != '') ? array_flip( $event_details['_epl_addit_regis_forms'] ) : array();

        //find the list of all forms
        $available_forms = $this->ecm->get_list_of_available_forms();
        $available_fields = $this->ecm->get_list_of_available_fields();

        //isolate the forms that are selected inside the event
        $ticket_buyer_forms = array_intersect_key( $available_forms, $event_ticket_buyer_forms );
        $addit_forms = array_intersect_key( $available_forms, $event_addit_forms );

        //This will combine all the fields in all the forms so that we can construct a header row.
        $tickey_buyer_fields = array();
        foreach ( $ticket_buyer_forms as $_form_id => $_form_info )
            $tickey_buyer_fields += $_form_info['epl_form_fields'];

        $event_addit_fields = array();
        foreach ( $addit_forms as $_form_id => $_form_info )
            $event_addit_fields += $_form_info['epl_form_fields'];



        $epl_fields_inside_form = array_flip( $tickey_buyer_fields ); //get the field ids inside the form
        $epl_addit_fields_inside_form = array_flip( $event_addit_fields ); //get the field ids inside the form
        //when creating a form in form manager, the user may rearrange fields.  Find their desired order
        $epl_fields_to_display = $this->epl_util->sort_array_by_array( $available_fields, $event_details['_epl_attendee_list_field'] );
        $epl_addit_fields_to_display = $this->epl_util->sort_array_by_array( $available_fields, $event_details['_epl_attendee_list_field'] );

        $epl_fields_to_display = $epl_fields_to_display + $epl_addit_fields_to_display;

        $csv_row = '';
        $list = array();
        $header_row = array();
        $header_pulled = false;
        $row = array();
        //$header_row[] = '';
        //$header_row[] = epl__( 'Regis Date' );
        //$header_row[] = epl__( 'Event Date' );
        //$header_row[] = epl__( 'Time' );



        $regis_ids = $this->ecm->get_event_regis_post_ids( false );


        //as of 1.1, the dates are stored as timestamps.
        //This will format the date for display based on the settings admin date format.
        //foreach ( $event_details['_epl_start_date'] as $k => &$v )
        //  $v = epl_admin_date_display( $v );


        foreach ( $regis_ids as $regis_id => $att_count ) {
            //$regis_data = $this->ecm->get_post_meta_all( $regis_id );
            $regis_data = $this->ecm->setup_regis_details( $regis_id, true );

            //Sometime there may be incomplete db records.  These will cause issues below.
            //In those cases, skip and move to the next item
            if ( !isset( $regis_data['_epl_dates']['_epl_start_date'][$event_id] ) )
                continue;

            if ( ($startus = get_the_regis_status( null, true )) && $startus <= 1 || $startus > 5 )
                continue;
            $event_times = $regis_data['_epl_dates']['_epl_start_time'][$event_id];
            //$event_prices = $regis_data['_epl_dates']['_epl_start_time'][$event_id];

            $regis_date = implode( ' & ', array_intersect_key( epl_get_element( '_epl_start_date', $event_details, array() ), array_flip( ( array ) $regis_data['_epl_dates']['_epl_start_date'][$event_id] ) ) );
            $regis_time = implode( ' & ', array_intersect_key( epl_get_element( '_epl_start_time', $event_details, array() ), array_flip( ( array ) $regis_data['_epl_dates']['_epl_start_time'][$event_id] ) ) );

            $date_labels = array();
            $date_labels[0] = '';
            $time_labels = array();
            $time_labels[0] = '';
            $ticket_labels = array();
            $ticket_labels[0] = $att_count;
            $purchased_tickets = ( array ) $regis_data['_epl_dates']['_att_quantity'][$event_id];
            $attendee_info = $regis_data['_epl_attendee_info'];
            $start = 1;
            foreach ( $purchased_tickets as $price_id => $qty ) {

                if ( $event_details['_epl_price_type'][$price_id] != 'att' ) {

                    continue;
                }

                $_qty = (is_array( $qty )) ? array_sum( $qty ) : $qty; //current( $qty );


                if ( $_qty > 0 ) {

                    $date_label[] = current( ( array ) $regis_data['_epl_dates']['_epl_start_date'][$event_id] );
                    if ( epl_get_element( '_epl_pricing_type', $event_details ) == 10 ) {
                        if ( in_array( $event_details['_epl_price_parent_time_id'][$price_id], ( array ) $regis_data['_epl_dates']['_epl_start_time'][$event_id] ) ) {

                            $time_labels = array_pad( $time_labels, $start + $_qty, epl_get_element( $event_details['_epl_price_parent_time_id'][$price_id], $event_details['_epl_start_time'] ) );
                        }
                        else {
                            $time_labels = array_pad( $time_labels, $start + $_qty, '' );
                        }
                    }


                    $ticket_labels = array_pad( $ticket_labels, $start + $_qty, $event_details['_epl_price_name'][$price_id] );

                    $start+=$_qty;
                }
            }

            $_r = array();

            $grand_total = epl_get_formatted_curr( epl_nz( $regis_data['_epl_grand_total'], 0.00 ) );
            $amount_paid = epl_get_formatted_curr( epl_nz( $regis_data['_epl_payment_amount'], 0.00 ) );


            $tickets_to_show = array_intersect_key( $purchased_tickets, $event_details['_epl_price_name'] );


            $att_counter = 1;
            $counter = 0;
            foreach ( $tickets_to_show as $ticket_id => $ticket_qty ) {
                if ( is_array( $ticket_qty ) ) {
                    $tmp_price_inner_keys = array_keys( $ticket_qty );
                    $ticket_qty = array_sum( $ticket_qty );
                }
                if ( $ticket_qty == 0 )
                    continue;

                for ( $i = 0; $i <= $ticket_qty; $i++ ) {

                    if ( $i == 0 && !epl_is_empty_array( $event_addit_forms ) ) {
                        //continue;
                        //$row[] = epl__( 'Registrant' );
                    }
                    else {
                        //$row[] = epl__( 'Attendee' );
                        $grand_total = '';
                        $amount_paid = '';
                        $regis_status = '';
                        $payment_method = '';
                    }


                    foreach ( $epl_fields_to_display as $field_id => $field_atts ) {
                        if ( !$header_pulled )
                            $header_row[] = html_entity_decode( htmlspecialchars_decode( $field_atts['label'], ENT_QUOTES ) );

                        $value = '';

                        //new v1.2.b9+

                        if ( isset( $attendee_info[$field_id][$event_id][$ticket_id] ) ) {

                            $value = epl_get_element( $counter, $attendee_info[$field_id][$event_id][$ticket_id] );
                        }
                        elseif ( isset( $attendee_info[$field_id][$event_id][$counter] ) ) {
                            $value = $attendee_info[$field_id][$event_id][$counter];
                        }

                        if ( $field_atts['input_type'] == 'select' || $field_atts['input_type'] == 'radio' ) {

                            $value = (isset( $field_atts['epl_field_choice_text'][$value] ) && $field_atts['epl_field_choice_text'][$value] !== '') ? $field_atts['epl_field_choice_text'][$value] : $value;
                        }
                        elseif ( $field_atts['input_type'] == 'checkbox' ) {

                            if ( !epl_is_empty_array( $field_atts['epl_field_choice_value'] ) )
                                $value = (implode( ',', ( array ) $value ) );
                            elseif ( !epl_is_empty_array( $value ) ) {
                                $value = (implode( ',', array_intersect_key( $field_atts['epl_field_choice_text'], array_flip( $value ) ) ));
                            }
                            else {
                                $value = html_entity_decode( htmlspecialchars_decode( $value ) );
                            }
                        }
                        /* else {

                          $value = html_entity_decode( htmlspecialchars_decode( $value ) );
                          } */

                        $row[] = html_entity_decode( htmlspecialchars_decode( $value, ENT_QUOTES ) );
                    }
                    $header_pulled = true;
                    //decode special chars (Swedish, Nordic)
                    //array_walk( $row, create_function( '&$item', '$item = utf8_decode($item);' ) );
                    if ( !epl_is_empty_array( $row ) ) {
                        $list[$regis_id]['att_count'] = $att_count;
                        $list[$regis_id]['attendees'][] = $row;
                        //$this->epl->epl_table->add_row( $row );
                    }
                    //$csv_row .= implode( ",", $row ) . "\r\n";

                    $row = array();
                    $counter++;
                    $att_counter++;
                }
            }
            $counter = 0;
        }

        //$tmpl = array( 'table_open' => '<table cellpadding="2" cellspacing="0" border="1" class="event_attendee_list_table">' );
        $data['event_title'] = epl_format_string( $event_details['post_title'] );
        $data['header_row'] = $header_row;
        $data['list'] = $list;

        return $this->epl->load_view( 'front/attendee-list/' . $display, $data, true );
    }


    function update_payment_data( $args = array() ) {
        global $epl_fields, $regis_details;

        $this->epl->load_config( 'regis-fields' );

        if ( EPL_IS_ADMIN )
            $_epl_payment_data = epl_get_element( '_epl_payment_data', $_POST, array() );
        else
            $_epl_payment_data = epl_get_element( '_epl_payment_data', $regis_details, array() );

        $defaults = $this->epl_util->remove_array_vals( array_flip( array_keys( $epl_fields['epl_regis_payment_fields'] ) ) );

        $args = wp_parse_args( $args, $defaults );


        if ( !isset( $args['post_ID'] ) )
            return false;

        $post_ID = intval( $args['post_ID'] );
        update_post_meta( $post_ID, '_epl_regis_status', epl_get_element( '_epl_regis_status', $args, $this->current_data[$this->regis_id]['_epl_regis_status'] ) );
        update_post_meta( $post_ID, '_epl_waitlist_status', epl_get_element( '_epl_waitlist_status', $args, $this->current_data[$this->regis_id]['_epl_regis_status'] ) );

        if ( isset( $args['_epl_payment_amount'] ) && $args['_epl_payment_amount'] == '' ) {

            if ( empty( $_epl_payment_data ) ) {
                //$_epl_payment_data[time()] = array_merge($defaults, $regis_details);
            }

            update_post_meta( $post_ID, '_epl_payment_data', $_epl_payment_data );
            return false;
        }

        $tmp = array();
        foreach ( $defaults as $meta_key => $meta_value ) {
            if ( $args[$meta_key] == '' ) {

                $default = (isset( $epl_fields['epl_regis_payment_fields'][$meta_key]['default_value'] )) ? $epl_fields['epl_regis_payment_fields'][$meta_key]['default_value'] : '';
                $args[$meta_key] = $default;
            }

            update_post_meta( $post_ID, $meta_key, $args[$meta_key] );
            $tmp[$meta_key] = $args[$meta_key];
        }

        //update_post_meta( $post_ID, '_epl_balance_due', $args['_epl_balance_due'] );
        //update_post_meta( $post_ID, '_epl_grand_total', $args['_epl_grand_total'] );

        $_epl_payment_data[time()] = $tmp;
        update_post_meta( $post_ID, '_epl_payment_data', $_epl_payment_data );

        if ( empty( $regis_details ) )
            setup_regis_details( $post_ID );

        $regis_details['_epl_payment_data'] = $_epl_payment_data;

        $this->update_regis_status();
        return true;
    }


    function cart_selected_dates() {
        $s = $this->current_data[$this->regis_id];
        //return epl_get_element('dates', $array)
    }


    function is_empty_cart() {

        if ( !isset( $this->current_data[$this->regis_id]['_events'] ) || empty( $this->current_data[$this->regis_id]['_events'] ) )
            return true;

        return false;
    }

    /*
     * get events in the session
     */


    function get_current_event_id() {
        global $event_details;

        $event_id = epl_get_element( 'event_id', $_REQUEST, null );

        if ( !$event_id ) {
            $event_id = key( ( array ) epl_get_element( '_events', $this->get_current_cart_values(), null ) );
        }
        if ( !$event_id ) {
            $event_id = epl_get_element( 'ID', $event_details );
        }
        if ( !$event_id ) {
            $event_id = epl_get_element( 'e_ID', $_REQUEST );
        }


        return intval( $event_id );
    }


    function get_regis_post_id() {
        static $r = null;

        if ( !is_null( $r ) )
            return $r;

        global $wpdb;

        $r = epl_get_element( 'post_ID', $this->get_current_data(), null );

        if ( is_null( $r ) )
            $r = epl_get_element( 'post_ID', $_POST, null );

        if ( is_null( $r ) ) {
            $regis_key = $_SESSION['__epl']['_regis_id'];

            $r = $wpdb->get_var(
                    $wpdb->prepare( "SELECT ID FROM {$wpdb->posts}
                 WHERE post_status ='publish' 
                 AND post_type='epl_registration' 
                 AND post_title=%s
                        ORDER BY ID DESC
                        LIMIT 1", $regis_key )
            );
        }
        if ( is_null( $r ) )
            $r = get_the_ID();

        return $r;
    }


    function get_events_in_cart( $event_id = null ) {

        $e = epl_get_element( '_events', $this->get_current_cart_values(), null );

        if ( is_null( $e ) )
            $e = $this->get_the_cart_dates();

        if ( !is_null( $event_id ) )
            return array( $event_id => epl_get_element( $event_id, $e, null ) );

        return ( array ) $e;
    }


    function get_att_quantity_values( $event_id = null ) {

        $r = epl_get_element_m( '_att_quantity', '_dates', $this->get_current_cart_values(), null );

        if ( !is_null( $event_id ) )
            return epl_get_element( $event_id, $r );
        return $r;
    }


    function get_the_cart_dates() {
        return epl_get_element_m( '_epl_start_date', '_dates', $this->get_current_cart_values(), null );
    }


    function get_the_cart_times() {

        return epl_get_element_m( '_epl_start_time', '_dates', $this->get_current_cart_values(), null );
    }


    function get_the_cart_prices() {

        return epl_get_element_m( '_att_quantity', '_dates', $this->get_current_cart_values(), null );
    }


    function get_the_attendee_info() {

        return epl_get_element( '_attendee_info', $this->get_current_cart_values(), null );
    }


    function get_current_cart_values() {

        return epl_get_element( $this->regis_id, $this->current_data );
    }


    function set_current_cart_value( $index, $subindex, $value ) {
        //$this->current_data$this->regis_id][$index][$subindex]
    }


    function get_current_data() {

        return $this->current_data;
    }


    function get_regis_events() {

        return epl_get_element( '_events', $this->get_current_cart_values(), null );
    }


    function get_regis_dates() {

        return epl_get_element( '_dates', $this->get_current_cart_values(), null );
    }


    function get_regis_data() {
        global $regis_details;
    }


    function get_attendee_form_value( $form = null, $field = null ) {

        if ( !$form )
            return null;
        global $epl_fields;

        static $data = array();

        if ( epl_is_empty_array( $data ) ) {
            $form_fields = $this->ecm->get_list_of_available_fields();
            $current_data = $this->current_data[$this->regis_id];
            $attendee_info = ( array ) $this->get_the_attendee_info();
            $_data = array_merge_recursive( $attendee_info, $form_fields );

            foreach ( $_data as $k => $v ) {
                $data[$v['input_slug']] = $v;
            }
        }

        $event_id = $this->get_current_event_id();

        if ( !$field )
            return $data;

        if ( $form == 'ticket_buyer' ) {

            if ( ($r = epl_get_element( 0, $data[$field], '' )) == '' )
                $r = epl_get_element_m( 0, $event_id, $data[$field] );
        }

        return $r;
    }


    function get_the_cart_selected_payment() {

        global $event_details;

        $p = epl_get_element( '_epl_payment_method', $_POST );

        if ( !$p )
            $p = epl_get_element( '_epl_payment_method', $this->current_data[$this->regis_id] );

        if ( !$p )
            $p = epl_get_element_m( '_epl_payment_method', '_dates', $this->current_data[$this->regis_id] );

        if ( !$p ) //pre v1.3
            $p = epl_get_element_m( '_epl_selected_payment', '_dates', $this->current_data[$this->regis_id] );

        if ( !$p) {
            $p = epl_get_element( '_epl_default_selected_payment', $event_details, array() );
        }

        if ( !$p )
            $p = ( int ) epl_get_element( '_epl_payment_method', $this->full_data );
        
        return (epl_is_empty_array( $p ) && !is_int( $p )) ? null : $p;
    }


    function get_payment_profile_id() {
        $p = $this->get_the_cart_selected_payment();

        if ( is_array( $p ) )
            return epl_get_element( 0, $p, null );

        return $p;
    }


    function get_primary_field_value( $field = null, $from = 'session' ) {

        if ( !$field || !$from )
            return null;

        global $event_details;

        $event_id = $event_details['ID'];

        $_field_keys = array(
            'email' => '4e794a6eeeb9a',
            'first_name' => '4e794a9a6b04f',
            'last_name' => '4e794ab9c1731',
        );

        switch ( $from )
        {

            case 'session':

                $_cp = $this->get_the_attendee_info();

                return $_cp[$_field_keys[$field]][$event_id][0];

                break;
        }
    }


    function has_selected_cc_payment( $gateway_info = null, $return_pay_type = null ) {

        $gateway_info = (!is_null( $gateway_info ) ? $gateway_info : $this->get_gateway_info());

        $pay_type = epl_get_element( '_epl_pay_type', $gateway_info );

        if ( !is_null( $return_pay_type ) )
            return $pay_type;

        if ( $pay_type == '_stripe' || $pay_type == '_pp_pro' || $pay_type == '_auth_net_aim' || $pay_type == '_qbmc' || $pay_type == '_firstdata' || $pay_type == '_usa_epay' || $pay_type == '_pp_payflow' )
            return true;

        return false;
    }


    function has_selected_offline_payment() {

        $gateway_info = $this->get_gateway_info();

        $pay_type = epl_get_element( '_epl_pay_type', $gateway_info );

        if ( $pay_type == '_cash' || $pay_type == '_check' )
            return true;

        return false;
    }


    function create_new_regis_record( $args = array() ) {
        $_post = array(
            'post_type' => 'epl_registration',
            'post_title' => strtoupper( $this->epl_util->make_unique_id( epl_nz( epl_get_regis_setting( 'epl_regis_id_length' ), 10 ) ) ),
            'post_content' => '',
            'post_status' => 'draft'
        );

        return wp_insert_post( $_post );
    }


    function maybe_add_new_user() {

        if ( !is_user_logged_in() && epl_um_is_enabled() ) {
            $ur = $this->add_new_user_enable();
            if ( $ur > 0 ) {
                $pw = $this->epl_util->make_unique_id( 8 );

                $arr = array(
                    'user_login' => epl_get_attendee_form_value( 'ticket_buyer', 'email' ),
                    'user_pass' => $pw,
                    'user_email' => epl_get_attendee_form_value( 'ticket_buyer', 'email' ),
                    'first_name' => epl_get_attendee_form_value( 'ticket_buyer', 'first_name' ),
                    'last_name' => epl_get_attendee_form_value( 'ticket_buyer', 'last_name' ),
                    'role' => epl_get_setting( 'epl_api_option_fields', 'epl_um_user_regis_role', 'subscriber' )
                );
                $values = $_SESSION['temp_fields'];
                echo "<pre class='prettyprint'>" . __LINE__ . "> " . basename( __FILE__ ) . " > " . print_r( $values, true ) . "</pre>";
                if ( $this->add_new_user_show_pass_fields() ) {

                    $pw = $values['user_pass'];
                    $arr['user_pass'] = $pw;
                }
                if ( $ur == 2 && $values['user_login'] != '' ) {
                    $arr = array(
                        'user_login' => epl_get_element( 'user_login', $values, epl_get_attendee_form_value( 'ticket_buyer', 'email' ) ),
                        'user_pass' => $pw,
                    );
                }

                if ( $arr['user_pass'] == '' )
                    $arr['user_pass'] = $this->epl_util->make_unique_id( 8 );

                $new_user = wp_insert_user( $arr );

                if ( !is_wp_error( $new_user ) ) {
                    wp_new_user_notification( $new_user, $pw );

                    $_SESSION['temp_fields']['user_id'] = $new_user;

                    return $new_user;
                }
            }
        }
        return false;
    }


    function get_new_user_form( $show = true ) {

        if ( !$show )
            return '';

        if ( is_user_logged_in() && epl_um_is_enabled() || epl_is_waitlist_flow() )
            return '';

        $ur = $this->add_new_user_enable();

        $data['show_pass'] = ($this->add_new_user_show_pass_fields());

        if ( $this->add_new_user_method() == 1 && (!$data['show_pass'] || $this->mode == 'overview') )
            return '';

        $data['show_user_login'] = ($this->add_new_user_method() == 2);


        if ( $ur == 0 )
            return '';

        if ( $ur == 1 )
            $data['required'] = ($ur == 1);

        $data['mode'] = $this->mode;

        $data['values'] = $_SESSION['temp_fields'];

        $fields = epl_add_user_fields();
        $_field_args = array(
            'section' => $fields['epl_add_user_fields'],
            'fields_to_display' => array_keys( $fields['epl_add_user_fields'] ),
            'meta' => array(
                '_view' => 0,
                '_type' => 'ind',
                'value' => $data['values'],
                'overview' => ( $this->mode == 'overview' )
            )
        );


        $data['fields'] = $this->epl_util->render_fields( $_field_args );

        return $this->epl->load_view( 'front/registration/regis-new-user-form', $data, true );
    }


    function get_user_list_dd( $regis_id = null, $user_id = null ) {

        $show_user_list_dd = apply_filters( 'epl_erm__get_user_list_dd__show_user_list_dd', true );

        if ( !$show_user_list_dd )
            return null;

        if ( epl_is_waitlist_flow() )
            return null;


        if ( epl_um_is_enabled() && epl_user_is_admin() && $this->mode == 'edit' ) {
            $user_id = $this->edbm->find_user_id_for_regis( $regis_id, $user_id );

            $users = get_users( 'orderby=email' );
            $user_dd_options = array();
            foreach ( $users as $user ) {
                if ( $user->user_email == '' )
                    continue;
                $user_dd_options[$user->ID] = $user->user_email . " ({$user->first_name} {$user->last_name})";
            }
            $params = array(
                'input_type' => 'select',
                'input_name' => 'user_id',
                'id' => 'user_id',
                'label' => epl__( 'User' ),
                'options' => $user_dd_options,
                'style' => 'width:100%',
                'empty_row' => true,
                'description' => epl__( 'You can assign this registration to a member of your website.' ),
                'value' => $user_id
            );
            $params += ( array ) $this->overview_trigger;

            $data['user_list_dd'] = $this->epl_util->create_element( $params );
            $r = $this->epl->load_view( 'admin/user-regis-manager/user-list-dd', $data, true );

            return $r;
        }
    }


    function add_new_user_enable() {
        return epl_get_setting( 'epl_api_option_fields', 'epl_um_enable_user_regis', 0 );
    }


    function add_new_user_method() {
        return epl_get_setting( 'epl_api_option_fields', 'epl_um_user_regis_username', 0 );
    }


    function add_new_user_show_pass_fields() {
        return (epl_get_setting( 'epl_api_option_fields', 'epl_um_user_regis_password' ) == 2);
    }


    function assign_event_to_user( $regis_post_ID, $user_id ) {
        $events_in_cart = $this->get_events_in_cart();

        foreach ( $events_in_cart as $event_id => $event_data )
            update_user_meta( $user_id, '_epl_regis_post_id_' . $regis_post_ID, $event_id );
    }


    function update_regis_status() {

        update_post_meta( $this->get_regis_post_id(), '_epl_regis_status', epl_get_true_regis_status() );
    }

}

?>
