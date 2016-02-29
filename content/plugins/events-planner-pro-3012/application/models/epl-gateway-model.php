<?php

/*
 * TODO
 * - create a common event & registration details getter
 */

class EPL_Gateway_Model extends EPL_Model {

    public $redirect_form_data = '';


    function __construct() {
        parent::__construct();

        $this->erm = $this->epl->load_model( 'epl-registration-model' );
        $this->ecm = $this->epl->load_model( 'epl-common-model' );
    }

    /*
     * get the token and redirect to paypal
     */


    function setup_gw_params() {

        $this->params = array();
    }


    function _express_checkout_redirect() {
        global $event_details, $cart_totals;

        $event_id = $event_details['ID']; //key( ( array ) $_SESSION['__epl'][$regis_id]['events'] );

        if ( is_null( $event_id ) ) {
            //return false;
        }
        $this->epl->load_file( 'libraries/gateways/paypal/paypal.php' );

        $url = epl_get_url();

        $regis_id = $this->erm->get_regis_id();
        $gateway_info = $this->erm->get_gateway_info();

        $post_ID = $this->erm->get_regis_post_id();

        $line_item_surcharge = false;
        $line_item_surcharge = apply_filters( 'egm__pp_exp__line_item_surcharge', $line_item_surcharge );

        $_totals = $this->erm->calculate_cart_totals();

        //$amount = $cart_totals['money_totals']['grand_total'];
        $amount = epl_get_balance_due();
        $tax = epl_get_element_m( 'surcharge', 'money_totals', $cart_totals, 0 );
        $subtotal = epl_get_element_m( 'subtotal', 'money_totals', $cart_totals, 0 );
        $num_days_in_cart = array();
        $price_multiplier = array();
        $price_multiplier_label = array();

        $requestParams = array(
            'RETURNURL' => add_query_arg( array( 'cart_action' => false, 'p_ID' => $post_ID, 'regis_id' => $regis_id, 'epl_action' => '_exp_checkout_payment_success' ), $url ),
            'CANCELURL' => add_query_arg( array( 'cart_action' => false, 'p_ID' => $post_ID, 'regis_id' => $regis_id, 'epl_action' => 'show_cart_overview' ), $url ),
            "SOLUTIONTYPE" => 'Sole',
            "LANDINGPAGE" => epl_nz( $gateway_info['_epl_pp_landing_page'], 'Login' )
        );

        $discount_amount = number_format( epl_get_element( 'discount_amount', $cart_totals['money_totals'], 0 ), 2, ".", "" );
        //$subtotal = $discount_amount > 0 ? number_format( $subtotal - $discount_amount, 2 ) : $subtotal;

        $orderParams = array(
            'PAYMENTREQUEST_0_AMT' => $amount,
            'PAYMENTREQUEST_0_SHIPPINGAMT' => 0,
            'PAYMENTREQUEST_0_CURRENCYCODE' => epl_nz( epl_get_general_setting( 'epl_currency_code' ), 'USD' ),
            'PAYMENTREQUEST_0_ITEMAMT' => $amount - $tax, //($subtotal - $discount_amount),
            'PAYMENTREQUEST_0_TAXAMT' => $line_item_surcharge === false ? $tax : 0,
        );


        $pp_email = epl_get_element( '_epl_pp_exp_email', $gateway_info );

        $counter = 0;
        $tickets = $_SESSION['__epl'][$regis_id]['_dates']['_att_quantity'];
        $events = $_SESSION['__epl'][$regis_id]['_events'];
        $parallel_pay = (epl_get_regis_setting( '_epl_enable_PP_parallel_pay' ) == 10 && !epl_is_empty_array( epl_get_element( '_epl_price_parallel_pay_email', $event_details, array() ) ) );

        if ( $parallel_pay ) {
            $orderParams = array();
            foreach ( $tickets as $event_id => $ind_tickets ) {
                $this->ecm->setup_event_details( $event_id );
                foreach ( $ind_tickets as $ticket_id => $ticket_qty ) {

                    $ticket_name = epl_get_element( $ticket_id, $event_details['_epl_price_name'] );
                    $ticket_price = epl_get_element( $ticket_id, $event_details['_epl_price'] );
                    if ( epl_is_eligible_for_member_price( $ticket_id ) )
                        $ticket_price = epl_get_element_m( $ticket_id, '_epl_member_price', $event_details, $ticket_price );
                    $qty = (is_array( $ticket_qty )) ? array_sum( $ticket_qty ) : $ticket_qty;

                    if ( $qty > 0 ) {

                        $orderParams['PAYMENTREQUEST_' . $counter . '_AMT'] = $ticket_price;
                        $orderParams['PAYMENTREQUEST_' . $counter . '_SELLERPAYPALACCOUNTID'] = epl_get_element_m( $ticket_id, '_epl_price_parallel_pay_email', $event_details, $pp_email );
                        $orderParams['PAYMENTREQUEST_' . $counter . '_SHIPPINGAMT'] = 0;
                        $orderParams['PAYMENTREQUEST_' . $counter . '_CURRENCYCODE'] = epl_nz( epl_get_general_setting( 'epl_currency_code' ), 'USD' );
                        $orderParams['PAYMENTREQUEST_' . $counter . '_ITEMAMT'] = $ticket_price;
                        $orderParams['PAYMENTREQUEST_' . $counter . '_TAXAMT'] = 0;
                        $orderParams['PAYMENTREQUEST_' . $counter . '_DESC'] = substr( $ticket_name, 0, 126 );
                        $orderParams['PAYMENTREQUEST_' . $counter . '_PAYMENTREQUESTID'] = $post_ID . '-' . $counter;


                        $counter++;
                    }
                }
            }
        }


        $counter = 0;

        $dates = (isset( $_SESSION['__epl'][$regis_id]['_dates']['_epl_start_date'] )) ? $_SESSION['__epl'][$regis_id]['_dates']['_epl_start_date'] : array();


        $item = array();
        foreach ( $tickets as $event_id => $ind_tickets ) {
            $this->ecm->setup_event_details( $event_id );

            $num_days_in_cart[$event_id] = count( epl_get_element( $event_id, $dates, array() ) );
            $price_multiplier[$event_id] = (($event_details['_epl_price_per'] == 10 && !epl_is_date_level_price()) ? $num_days_in_cart[$event_id] : 1);
            $price_multiplier_label[$event_id] = ($price_multiplier[$event_id] > 1) ? ' - ' . $num_days_in_cart[$event_id] . ' ' . epl__( 'days' ) : '';

            foreach ( $ind_tickets as $ticket_id => $ticket_qty ) {

                $ticket_name = epl_get_element( $ticket_id, $event_details['_epl_price_name'] );
                $ticket_price = epl_get_element( $ticket_id, $event_details['_epl_price'] );
                if ( epl_is_eligible_for_member_price( $ticket_id ) )
                    $ticket_price = epl_get_element_m( $ticket_id, '_epl_member_price', $event_details, $ticket_price );
                $qty = (is_array( $ticket_qty )) ? array_sum( $ticket_qty ) : $ticket_qty;

                if ( $qty > 0 ) {
                    $item['L_PAYMENTREQUEST_0_NAME' . $counter] = substr( $event_details['post_title'], 0, 126 );
                    $item['L_PAYMENTREQUEST_0_DESC' . $counter] = $ticket_name . $price_multiplier_label[$event_id];
                    //$item['L_PAYMENTREQUEST_0_NUMBER' . $counter] = $ticket_id;
                    $item['L_PAYMENTREQUEST_0_AMT' . $counter] = $ticket_price;
                    $item['L_PAYMENTREQUEST_0_QTY' . $counter] = $qty * $price_multiplier[$event_id];

                    $counter++;
                }
            }
        }

        if ( $parallel_pay ) {
            $item = array();
            $counter = 0;
            $ticket_counter = 0; //this will be incremented if we do line item per ticket qty
            foreach ( $tickets as $event_id => $ind_tickets ) {
                $this->ecm->setup_event_details( $event_id );
                foreach ( $ind_tickets as $ticket_id => $ticket_qty ) {

                    $ticket_name = epl_get_element( $ticket_id, $event_details['_epl_price_name'] );
                    $ticket_price = epl_get_element( $ticket_id, $event_details['_epl_price'] );
                    if ( epl_is_eligible_for_member_price( $ticket_id ) )
                        $ticket_price = epl_get_element_m( $ticket_id, '_epl_member_price', $event_details, $ticket_price );
                    $qty = (is_array( $ticket_qty )) ? array_sum( $ticket_qty ) : $ticket_qty;

                    if ( $qty > 0 ) {
                        $item['L_PAYMENTREQUEST_' . $counter . '_NAME' . $ticket_counter] = substr( $event_details['post_title'], 0, 126 );
                        $item['L_PAYMENTREQUEST_' . $counter . '_DESC' . $ticket_counter] = $ticket_name . $price_multiplier_label[$event_id];
                        //$item['L_PAYMENTREQUEST_0_NUMBER' . $counter] = $ticket_id;
                        $item['L_PAYMENTREQUEST_' . $counter . '_AMT' . $ticket_counter] = $ticket_price;
                        $item['L_PAYMENTREQUEST_' . $counter . '_QTY' . $ticket_counter] = $qty * $price_multiplier[$event_id];

                        $counter++;
                    }
                }
            }
        }

        if ( !$parallel_pay ) {
            if ( $tax > 0 && $line_item_surcharge !== false ) {

                foreach ( $_SESSION['__epl'][$regis_id]['_events'] as $event_id => $event_totals ) {
                    $this->ecm->setup_event_details( $event_id );
                    $sc = epl_get_element_m( 'surcharge', 'money_totals', $event_totals, 0 );
                    if ( $sc == 0 )
                        continue;

                    $this->ecm->setup_event_details( $event_id );
                    $surcharge_label = epl_get_element( '_epl_surcharge_label', $event_details, epl__( 'Surcharge' ) );

                    $item['L_PAYMENTREQUEST_0_NAME' . $counter] = $surcharge_label;
                    $item['L_PAYMENTREQUEST_0_AMT' . $counter] = $sc;
                    $item['L_PAYMENTREQUEST_0_QTY' . $counter] = 1;
                    $orderParams['PAYMENTREQUEST_0_ITEMAMT'] += $sc;

                    $counter++;
                }
            }

            if ( $discount_amount > 0 ) {

                $discount_description = epl_get_element( 'discount_description', $cart_totals['money_totals'], null );

                $discount_description = substr( $discount_description, 0, 126 );

                $item['L_PAYMENTREQUEST_0_NAME' . $counter] = $discount_description;
                //$item['L_PAYMENTREQUEST_0_DESC' . $counter] = $discount_description;
                //$item['L_PAYMENTREQUEST_0_NUMBER' . $counter] = $ticket_id;
                $item['L_PAYMENTREQUEST_0_AMT' . $counter] = (-1 * $discount_amount);
                $item['L_PAYMENTREQUEST_0_QTY' . $counter] = 1;
            }

            $alt_total_due = (epl_get_element_m( 'pay_deposit', 'money_totals', $cart_totals ) == 1);
            if ( $alt_total_due > 0 ) {
                $counter++;

                $balance_due_desc = epl__( 'Deposit Offset.  Due at a later date.' );

                $balance_due_desc = substr( $balance_due_desc, 0, 126 );
                $balance_offset = get_the_regis_total_amount( false ) - $amount;
                $item['L_PAYMENTREQUEST_0_NAME' . $counter] = $balance_due_desc;
                //$item['L_PAYMENTREQUEST_0_DESC' . $counter] = $discount_description;
                //$item['L_PAYMENTREQUEST_0_NUMBER' . $counter] = $ticket_id;
                $item['L_PAYMENTREQUEST_0_AMT' . $counter] = (-1 * $balance_offset);
                $item['L_PAYMENTREQUEST_0_QTY' . $counter] = 1;
            }

            $payment_data = epl_get_regis_payments();

            if ( !empty( $payment_data ) ) {
                if ( count( $payment_data ) > 0 ) {
                    foreach ( $payment_data as $time => $p ) {
                        $counter++;

                        $payment_made_description = epl__( 'Credit for payment made on:' ) . ' ' . epl_formatted_date( $p['_epl_payment_date'] );

                        $payment_made_description = substr( $payment_made_description, 0, 126 );

                        $item['L_PAYMENTREQUEST_0_NAME' . $counter] = $payment_made_description;
                        //$item['L_PAYMENTREQUEST_0_DESC' . $counter] = $discount_description;
                        //$item['L_PAYMENTREQUEST_0_NUMBER' . $counter] = $ticket_id;
                        $item['L_PAYMENTREQUEST_0_AMT' . $counter] = (-1 * $p['_epl_payment_amount']);
                        $item['L_PAYMENTREQUEST_0_QTY' . $counter] = 1;
                    }
                }
            }

            if ( ($donation_amount = epl_get_element_m( 'donation_amount', 'money_totals', $cart_totals, 0 )) > 0 ) {
                $counter++;
                $item['L_PAYMENTREQUEST_0_NAME' . $counter] = epl__( 'Donation' );
                $item['L_PAYMENTREQUEST_0_AMT' . $counter] = $donation_amount;
                $item['L_PAYMENTREQUEST_0_QTY' . $counter] = 1;
            }
            //adjust for alt total
            if ( ($original_total = epl_get_element_m( 'original_total', 'money_totals', $cart_totals, 0 )) > 0 ) {
                $counter++;
                $item['L_PAYMENTREQUEST_0_NAME' . $counter] = epl__( 'Offset' );
                $item['L_PAYMENTREQUEST_0_AMT' . $counter] = -1 * ($original_total - $orderParams['PAYMENTREQUEST_0_AMT']);
                $item['L_PAYMENTREQUEST_0_QTY' . $counter] = 1;
            }
        }


        $paypal = new EPL_Paypal();

        $paypal->_credentials = array(
            'USER' => $gateway_info['_epl_pp_exp_user'],
            'PWD' => $gateway_info['_epl_pp_exp_pwd'],
            'SIGNATURE' => $gateway_info['_epl_pp_exp_sig'],
        );

        $request = $requestParams + $item + $orderParams;
        $request = apply_filters( 'epl_express_checkout_redirect_request_params', $request );
        $response = $paypal->request( 'SetExpressCheckout', $request );

        if ( is_array( $response ) && $response['ACK'] == 'Success' ) { //Request successful
            $token = $response['TOKEN'];

            $loc = 'https://www.paypal.com/webscr?cmd=_express-checkout&token=' . urlencode( $token );

            if ( $gateway_info['_epl_sandbox'] == 10 )
                $loc = 'https://www.sandbox.paypal.com/webscr?cmd=_express-checkout&token=' . urlencode( $token );

            $redir_method = apply_filters( 'epl_express_checkout_redirect_method', 'header' );

            if ( $redir_method == 'header' ) {

                header( 'Location:' . $loc );
                die();
            }
            if ( $redir_method == 'js' )
                die( 'Redirecting to PayPal, please wait ... <script> location.replace("' . $loc . '"); </script>' );
        }
        else {

            $error = 'ERROR: ' . $response['L_SHORTMESSAGE0'] . '. ' . $response['L_LONGMESSAGE0'];

            echo EPL_Util::get_instance()->epl_invoke_error( 0, $error, false );
        }
    }

    /*
     * payment successfull and  back to the overview page
     *
     */


    function _exp_checkout_payment_success() {


        $this->epl->load_file( 'libraries/gateways/paypal/paypal.php' );
        if ( isset( $_GET['token'] ) && !empty( $_GET['token'] ) ) { // Token parameter exists
            // Get checkout details, including buyer information.
            // We can save it for future reference or cross-check with the data we have
            $paypal = new EPL_Paypal();
            $gateway_info = $this->erm->get_gateway_info();
            $paypal->_credentials = array(
                'USER' => $gateway_info['_epl_pp_exp_user'],
                'PWD' => $gateway_info['_epl_pp_exp_pwd'],
                'SIGNATURE' => $gateway_info['_epl_pp_exp_sig'],
            );
            $checkoutDetails = $paypal->request( 'GetExpressCheckoutDetails', array( 'TOKEN' => $_GET['token'] ) );

            return true;
        }
        else {

            $error = 'ERROR: ' . $response['L_SHORTMESSAGE0'] . '. ' . $response['L_LONGMESSAGE0'];

            echo EPL_Util::get_instance()->epl_invoke_error( 0, $error, false );
        }

        return false;
    }

    /*
     * collect payment and send to payment made page.
     */


    function _exp_checkout_do_payment() {

        global $event_details, $cart_totals;
        $event_id = $event_details['ID'];

        if ( is_null( $event_id ) ) {
            //return false;
        }

        $regis_id = $this->erm->get_regis_id();


        $post_ID = $this->erm->get_regis_post_id();


        $this->ecm->setup_event_details( $event_id );

        $line_item_surcharge = false;
        $line_item_surcharge = apply_filters( 'egm__pp_exp__line_item_surcharge', $line_item_surcharge );

        $_totals = $this->erm->calculate_cart_totals();

        $amount = $cart_totals['money_totals']['grand_total'];
        $amount = epl_get_balance_due();
        $tax = epl_get_element_m( 'surcharge', 'money_totals', $cart_totals, 0 );
        $subtotal = epl_get_element_m( 'subtotal', 'money_totals', $cart_totals, 0 );
        $num_days_in_cart = array();
        $price_multiplier = array();
        $price_multiplier_label = array();

        $discount_amount = number_format( epl_get_element( 'discount_amount', $cart_totals['money_totals'], 0 ), 2, ".", "" );

        //$subtotal = $discount_amount > 0 ? number_format( $subtotal - $discount_amount, 2 ) : $subtotal;

        $this->epl->load_file( 'libraries/gateways/paypal/paypal.php' );
        $paypal = new EPL_Paypal();

        $gateway_info = $this->erm->get_gateway_info();
        $paypal->_credentials = array(
            'USER' => $gateway_info['_epl_pp_exp_user'],
            'PWD' => $gateway_info['_epl_pp_exp_pwd'],
            'SIGNATURE' => $gateway_info['_epl_pp_exp_sig'],
        );
        $requestParams = array(
            'TOKEN' => $_GET['token'],
            'PAYMENTACTION' => 'Sale',
            'PAYERID' => $_GET['PayerID']
        );
        $orderParams = array(
            'PAYMENTREQUEST_0_AMT' => $amount, // Same amount as in the original request
            'PAYMENTREQUEST_0_ITEMAMT' => $amount - $tax, //($subtotal - $discount_amount), //  ALANE ADDED - REQUIRED FOR LINE ITEMS
            'PAYMENTREQUEST_0_CURRENCYCODE' => epl_nz( epl_get_general_setting( 'epl_currency_code' ), 'USD' ),
            'PAYMENTREQUEST_0_TAXAMT' => $line_item_surcharge === false ? $tax : 0,
        );

        $counter = 0;
        $tickets = $_SESSION['__epl'][$regis_id]['_dates']['_att_quantity'];
        $dates = (isset( $_SESSION['__epl'][$regis_id]['_dates']['_epl_start_date'] )) ? $_SESSION['__epl'][$regis_id]['_dates']['_epl_start_date'] : array();

        $events = $_SESSION['__epl'][$regis_id]['_events'];
        $parallel_pay = (epl_get_regis_setting( '_epl_enable_PP_parallel_pay' ) == 10 && !epl_is_empty_array( epl_get_element( '_epl_price_parallel_pay_email', $event_details, array() ) ) );
        $pp_email = epl_get_element( '_epl_pp_exp_email', $gateway_info );

        if ( $parallel_pay ) {
            $orderParams = array();
            foreach ( $tickets as $event_id => $ind_tickets ) {
                $this->ecm->setup_event_details( $event_id );
                foreach ( $ind_tickets as $ticket_id => $ticket_qty ) {

                    $ticket_name = epl_get_element( $ticket_id, $event_details['_epl_price_name'] );
                    $ticket_price = epl_get_element( $ticket_id, $event_details['_epl_price'] );
                    if ( epl_is_eligible_for_member_price( $ticket_id ) )
                        $ticket_price = epl_get_element_m( $ticket_id, '_epl_member_price', $event_details, $ticket_price );
                    $qty = (is_array( $ticket_qty )) ? array_sum( $ticket_qty ) : $ticket_qty;

                    if ( $qty > 0 ) {

                        $orderParams['PAYMENTREQUEST_' . $counter . '_AMT'] = $ticket_price;
                        $orderParams['PAYMENTREQUEST_' . $counter . '_SELLERPAYPALACCOUNTID'] = epl_get_element_m( $ticket_id, '_epl_price_parallel_pay_email', $event_details, $pp_email );
                        $orderParams['PAYMENTREQUEST_' . $counter . '_SHIPPINGAMT'] = 0;
                        $orderParams['PAYMENTREQUEST_' . $counter . '_CURRENCYCODE'] = epl_nz( epl_get_general_setting( 'epl_currency_code' ), 'USD' );
                        $orderParams['PAYMENTREQUEST_' . $counter . '_ITEMAMT'] = $ticket_price;
                        $orderParams['PAYMENTREQUEST_' . $counter . '_TAXAMT'] = 0;
                        $orderParams['PAYMENTREQUEST_' . $counter . '_DESC'] = substr( $ticket_name, 0, 126 );
                        $orderParams['PAYMENTREQUEST_' . $counter . '_PAYMENTREQUESTID'] = $post_ID . '-' . $counter;


                        $counter++;
                    }
                }
            }
        }


        $counter = 0;

        $item = array();
        foreach ( $tickets as $event_id => $ind_tickets ) {
            $this->ecm->setup_event_details( $event_id );

            $num_days_in_cart[$event_id] = count( epl_get_element( $event_id, $dates, array() ) );
            $price_multiplier[$event_id] = (($event_details['_epl_price_per'] == 10 && !epl_is_date_level_price()) ? $num_days_in_cart[$event_id] : 1);
            $price_multiplier_label[$event_id] = ($price_multiplier[$event_id] > 1) ? ' - ' . $num_days_in_cart[$event_id] . ' ' . epl__( 'days' ) : '';

            foreach ( $ind_tickets as $ticket_id => $ticket_qty ) {

                $ticket_name = epl_get_element( $ticket_id, $event_details['_epl_price_name'] );
                $ticket_price = epl_get_element( $ticket_id, $event_details['_epl_price'] );
                if ( epl_is_eligible_for_member_price( $ticket_id ) )
                    $ticket_price = epl_get_element_m( $ticket_id, '_epl_member_price', $event_details, $ticket_price );
                $qty = (is_array( $ticket_qty )) ? array_sum( $ticket_qty ) : $ticket_qty;

                if ( $qty > 0 ) {
                    $item['L_PAYMENTREQUEST_0_NAME' . $counter] = substr( $event_details['post_title'], 0, 126 );
                    $item['L_PAYMENTREQUEST_0_DESC' . $counter] = $ticket_name . $price_multiplier_label[$event_id];
                    //$item['L_PAYMENTREQUEST_0_NUMBER' . $counter] = $ticket_id;
                    $item['L_PAYMENTREQUEST_0_AMT' . $counter] = $ticket_price;
                    $item['L_PAYMENTREQUEST_0_QTY' . $counter] = $qty * $price_multiplier[$event_id];

                    $counter++;
                }
            }
        }

        if ( $parallel_pay ) {
            $item = array();
            $counter = 0;
            $ticket_counter = 0; //this will be incremented if we do line item per ticket qty
            foreach ( $tickets as $event_id => $ind_tickets ) {
                $this->ecm->setup_event_details( $event_id );
                foreach ( $ind_tickets as $ticket_id => $ticket_qty ) {

                    $ticket_name = epl_get_element( $ticket_id, $event_details['_epl_price_name'] );
                    $ticket_price = epl_get_element( $ticket_id, $event_details['_epl_price'] );
                    if ( epl_is_eligible_for_member_price( $ticket_id ) )
                        $ticket_price = epl_get_element_m( $ticket_id, '_epl_member_price', $event_details, $ticket_price );
                    $qty = (is_array( $ticket_qty )) ? array_sum( $ticket_qty ) : $ticket_qty;

                    if ( $qty > 0 ) {
                        $item['L_PAYMENTREQUEST_' . $counter . '_NAME' . $ticket_counter] = substr( $event_details['post_title'], 0, 126 );
                        $item['L_PAYMENTREQUEST_' . $counter . '_DESC' . $ticket_counter] = $ticket_name . $price_multiplier_label[$event_id];
                        //$item['L_PAYMENTREQUEST_0_NUMBER' . $counter] = $ticket_id;
                        $item['L_PAYMENTREQUEST_' . $counter . '_AMT' . $ticket_counter] = $ticket_price;
                        $item['L_PAYMENTREQUEST_' . $counter . '_QTY' . $ticket_counter] = $qty * $price_multiplier[$event_id];

                        $counter++;
                    }
                }
            }
        }

        if ( !$parallel_pay ) {
            if ( $tax > 0 && $line_item_surcharge !== false ) {

                //$discount_description = epl_get_element( 'discount_description', $cart_totals['money_totals'], null );
                foreach ( $_SESSION['__epl'][$regis_id]['_events'] as $event_id => $event_totals ) {
                    $sc = epl_get_element_m( 'surcharge', 'money_totals', $event_totals, 0 );
                    if ( $sc == 0 )
                        continue;

                    $this->ecm->setup_event_details( $event_id );
                    $surcharge_label = epl_get_element( '_epl_surcharge_label', $event_details, epl__( 'Surcharge' ) );

                    $item['L_PAYMENTREQUEST_0_NAME' . $counter] = $surcharge_label;
                    //$item['L_PAYMENTREQUEST_0_DESC' . $counter] = $discount_description;
                    //$item['L_PAYMENTREQUEST_0_NUMBER' . $counter] = $ticket_id;
                    $item['L_PAYMENTREQUEST_0_AMT' . $counter] = $sc;
                    $item['L_PAYMENTREQUEST_0_QTY' . $counter] = 1;
                    $requestParams['PAYMENTREQUEST_0_ITEMAMT'] += $sc;

                    $counter++;
                }
            }

            if ( $discount_amount > 0 ) {

                $discount_description = epl_get_element( 'discount_description', $cart_totals['money_totals'], null );

                $discount_description = substr( $discount_description, 0, 126 );

                $item['L_PAYMENTREQUEST_0_NAME' . $counter] = $discount_description;
                //$item['L_PAYMENTREQUEST_0_DESC' . $counter] = $discount_description;
                //$item['L_PAYMENTREQUEST_0_NUMBER' . $counter] = $ticket_id;
                $item['L_PAYMENTREQUEST_0_AMT' . $counter] = (-1 * $discount_amount);
                $item['L_PAYMENTREQUEST_0_QTY' . $counter] = 1;
            }

            $alt_total_due = (epl_get_element_m( 'pay_deposit', 'money_totals', $cart_totals ) == 1);
            if ( $alt_total_due > 0 ) {
                $counter++;

                $balance_due_desc = epl__( 'Deposit Offset.  Due at a later date.' );

                $balance_due_desc = substr( $balance_due_desc, 0, 126 );
                $balance_offset = get_the_regis_total_amount( false ) - $amount;
                $item['L_PAYMENTREQUEST_0_NAME' . $counter] = $balance_due_desc;
                //$item['L_PAYMENTREQUEST_0_DESC' . $counter] = $discount_description;
                //$item['L_PAYMENTREQUEST_0_NUMBER' . $counter] = $ticket_id;
                $item['L_PAYMENTREQUEST_0_AMT' . $counter] = (-1 * $balance_offset);
                $item['L_PAYMENTREQUEST_0_QTY' . $counter] = 1;
            }

            $payment_data = epl_get_regis_payments();

            if ( !empty( $payment_data ) ) {
                if ( count( $payment_data ) > 0 ) {
                    foreach ( $payment_data as $time => $p ) {
                        $counter++;

                        $payment_made_description = epl__( 'Offset for payment made on: ' ) . epl_formatted_date( $p['_epl_payment_date'] );

                        $payment_made_description = substr( $payment_made_description, 0, 126 );

                        $item['L_PAYMENTREQUEST_0_NAME' . $counter] = $payment_made_description;
                        //$item['L_PAYMENTREQUEST_0_DESC' . $counter] = $discount_description;
                        //$item['L_PAYMENTREQUEST_0_NUMBER' . $counter] = $ticket_id;
                        $item['L_PAYMENTREQUEST_0_AMT' . $counter] = (-1 * $p['_epl_payment_amount']);
                        $item['L_PAYMENTREQUEST_0_QTY' . $counter] = 1;
                    }
                }
            }

            if ( ($donation_amount = epl_get_element_m( 'donation_amount', 'money_totals', $cart_totals, 0 )) > 0 ) {
                $counter++;
                $item['L_PAYMENTREQUEST_0_NAME' . $counter] = epl__( 'Donation' );
                $item['L_PAYMENTREQUEST_0_AMT' . $counter] = $donation_amount;
                $item['L_PAYMENTREQUEST_0_QTY' . $counter] = 1;
            }

            if ( ($original_total = epl_get_element_m( 'original_total', 'money_totals', $cart_totals, 0 )) > 0 ) {
                $counter++;
                $item['L_PAYMENTREQUEST_0_NAME' . $counter] = epl__( 'Offset' );
                $item['L_PAYMENTREQUEST_0_AMT' . $counter] = -1 * ($original_total - $orderParams['PAYMENTREQUEST_0_AMT']);
                $item['L_PAYMENTREQUEST_0_QTY' . $counter] = 1;
            }
        }

        $request = $requestParams + $item + $orderParams;
        $request = apply_filters( 'epl_express_checkout_do_payment_request_params', $request );
        $response = $paypal->request( 'DoExpressCheckoutPayment', $request );

        if ( is_array( $response ) && $response['ACK'] == 'Success' ) {

            $payment_amount = $response['PAYMENTINFO_0_AMT'];
            $counter = 0;
            if ( $parallel_pay ) {
                $payment_amount = 0;
                foreach ( $tickets as $event_id => $ind_tickets ) {
                    $this->ecm->setup_event_details( $event_id );
                    foreach ( $ind_tickets as $ticket_id => $ticket_qty ) {

                        $qty = (is_array( $ticket_qty )) ? array_sum( $ticket_qty ) : $ticket_qty;

                        if ( $qty > 0 ) {

                            $payment_amount += epl_get_element( 'PAYMENTINFO_' . $counter . '_AMT', $response, 0 );

                            $counter++;
                        }
                    }
                }
            }

            $data['post_ID'] = $post_ID;

            $data['_epl_grand_total'] = $cart_totals['money_totals']['grand_total'];
            $data['_epl_payment_amount'] = $payment_amount;
            $data['_epl_payment_date'] = current_time( 'mysql' );
            $data['_epl_payment_method'] = $this->erm->get_payment_profile_id();
            $data['_epl_transaction_id'] = $response['PAYMENTINFO_0_TRANSACTIONID'];
            $data['_epl_prediscount_total'] = epl_get_element( 'pre_discount_total', $cart_totals['money_totals'], 0 );
            $data['_epl_discount_amount'] = epl_get_element( 'discount_amount', $cart_totals['money_totals'], 0 );

            $data = apply_filters( 'epl_pp_exp_response_data', $data, $response );


            $this->erm->update_payment_data( $data );


            return true; //echo "DONE";
        }
        else {

            $error = 'ERROR: ' . $response['L_SHORTMESSAGE0'] . '. ' . $response['L_LONGMESSAGE0'];

            echo EPL_Util::get_instance()->epl_invoke_error( 0, $error, false );
        }

        return false;
    }


    function paypal_pro_process() {

        global $event_details, $cart_totals;
        $event_id = $event_details['ID'];

        if ( is_null( $event_id ) ) {
            return false;
        }

        $regis_id = $this->erm->get_regis_id();


        $post_ID = $this->erm->get_regis_post_id();


        $this->ecm->setup_event_details( $event_id );

        $this->erm->calculate_cart_totals( true );

        $min_deposit = epl_get_element_m( 'min_deposit', 'money_totals', $cart_totals, 0 );
        $min_deposit = ($min_deposit > 0 && (epl_get_element_m( 'pay_deposit', 'money_totals', $cart_totals, 0 ) == 1));

        $amount = $cart_totals['money_totals']['grand_total'];
        $tax = $min_deposit ? 0 : epl_get_element_m( 'surcharge', 'money_totals', $cart_totals, 0 );
        $subtotal = ($tax == 0) ? $amount : epl_get_element_m( 'subtotal', 'money_totals', $cart_totals, 0 );
        $discount_amount = epl_get_element( 'discount_amount', $cart_totals['money_totals'], 0 );
        //$subtotal = $discount_amount > 0 ? number_format( $subtotal - $discount_amount, 2 ) : $subtotal;

        $requestParams = array(
            'IPADDRESS' => $_SERVER['REMOTE_ADDR'],
            'PAYMENTACTION' => 'Sale'
        );

        foreach ( $_POST as $k => &$v )
            trim( esc_attr( $v ) );

        $creditCardDetails = array(
            'CREDITCARDTYPE' => $_POST['_epl_cc_card_type'],
            'ACCT' => $_POST['_epl_cc_num'],
            'EXPDATE' => date_i18n( "mY", strtotime( $_POST['_epl_cc_exp_month'] . $_POST['_epl_cc_exp_year'] ) ),
            'CVV2' => $_POST['_epl_cc_cvv']
        );

        $payerDetails = array(
            'FIRSTNAME' => $_POST['_epl_cc_first_name'],
            'LASTNAME' => $_POST['_epl_cc_last_name'],
            'COUNTRYCODE' => $_POST['_epl_cc_country'],
            'STATE' => $_POST['_epl_cc_state'],
            'CITY' => $_POST['_epl_cc_city'],
            'STREET' => $_POST['_epl_cc_address'],
            'ZIP' => $_POST['_epl_cc_zip']
        );

        $orderParams = array(
            'AMT' => epl_get_balance_due(),
            'ITEMAMT' => ($min_deposit ) ? epl_get_balance_due() : $subtotal,
            'TAXAMT' => $tax,
            'SHIPPINGAMT' => 0,
            'CURRENCYCODE' => epl_nz( epl_get_general_setting( 'epl_currency_code' ), 'USD' )
        );

        $item = array(
            'L_NAME0' => 'Event Registration',
            'L_DESC0' => $event_details['post_title'],
            'L_AMT0' => ($min_deposit ) ? epl_get_balance_due() : $subtotal,
            'L_QTY0' => '1'
        );


        $this->epl->load_file( 'libraries/gateways/paypal/paypal.php' );
        $paypal = new EPL_Paypal();

        $gateway_info = $this->erm->get_gateway_info();

        $paypal->_credentials = array(
            'USER' => trim( $gateway_info['_epl_user'] ),
            'PWD' => trim( $gateway_info['_epl_pwd'] ),
            'SIGNATURE' => trim( $gateway_info['_epl_sig'] )
        );


        $request = $requestParams + $creditCardDetails + $payerDetails + $orderParams + $item;
        $request = apply_filters( 'epl_paypal_pro_process_params', $request );
        $response = $paypal->request( 'DoDirectPayment', $request );

        if ( is_array( $response ) && $response['ACK'] == 'Success' ) { // Payment successful
            // We'll fetch the transaction ID for internal bookkeeping
            $transactionId = $response['TRANSACTIONID'];



            $data['post_ID'] = $post_ID;

            $data['_epl_grand_total'] = $_totals['money_totals']['grand_total'];
            $data['_epl_payment_amount'] = $response['AMT'];
            $data['_epl_payment_date'] = current_time( 'mysql' );
            $data['_epl_payment_method'] = $this->erm->get_payment_profile_id();
            $data['_epl_transaction_id'] = $response['TRANSACTIONID'];
            $data['_epl_prediscount_total'] = epl_get_element( 'pre_discount_total', $_totals['money_totals'], 0 );
            $data['_epl_discount_amount'] = epl_get_element( 'discount_amount', $_totals['money_totals'], 0 );
            //$data['_epl_payment_note'] = print_r($response,true);

            $data = apply_filters( 'epl_pp_pro_response_data', $data, $response );

            $this->erm->update_payment_data( $data );


            return true; //echo "DONE";
        }
        else {

            $r = '<div class="epl_error">ERROR: ' . $response['L_SHORTMESSAGE0'] . '. ' . $response['L_LONGMESSAGE0'] . '</div>';

            if ( isset( $response['L_SHORTMESSAGE1'] ) )
                $r .= '<div class="epl_error">ERROR: ' . $response['L_SHORTMESSAGE1'] . '. ' . $response['L_LONGMESSAGE1'] . '</div>';

            return $r;
        }
    }


    function payflow_pro_process() {

        global $event_details;
        $event_id = $event_details['ID'];

        if ( is_null( $event_id ) ) {
            return false;
        }

        $regis_id = $this->erm->get_regis_id();

        $post_ID = $this->erm->get_regis_post_id();

        $this->ecm->setup_event_details( $event_id );

        $_totals = $this->erm->calculate_cart_totals();

        $amount = $_totals['money_totals']['grand_total'];
        $tax = epl_get_element_m( 'surcharge', 'money_totals', $_totals, 0 );
        $subtotal = ($tax == 0) ? $amount : epl_get_element_m( 'subtotal', 'money_totals', $_totals, 0 );
        $discount_amount = epl_get_element( 'discount_amount', $_totals['money_totals'], 0 );
        //$subtotal = $discount_amount > 0 ? number_format( $subtotal - $discount_amount, 2 ) : $subtotal;

        foreach ( $_POST as $k => &$v )
            trim( esc_attr( $v ) );


        $this->epl->load_file( 'libraries/gateways/paypal/payflow-pro.php' );

        $gateway_info = $this->erm->get_gateway_info();

        try {

            $txn = new EPL_PayFlowTransaction();

            //these are provided by your payflow reseller
            $txn->PARTNER = trim( $gateway_info['_epl_partner'] );
            $txn->USER = trim( $gateway_info['_epl_user'] );
            $txn->PWD = trim( $gateway_info['_epl_pwd'] );
            $txn->VENDOR = trim( $gateway_info['_epl_vendor'] ); //or your vendor name
            // transaction information
            $txn->ACCT = $_POST['_epl_cc_num']; //cc number
            $txn->AMT = epl_get_balance_due(); //amount: 1 dollar
            $txn->EXPDATE = date_i18n( "my", strtotime( $_POST['_epl_cc_exp_month'] . $_POST['_epl_cc_exp_year'] ) ); //'0210'; //4 digit expiration date

            $txn->FIRSTNAME = $_POST['_epl_cc_first_name'];
            $txn->LASTNAME = $_POST['_epl_cc_last_name'];
            $txn->STREET = $_POST['_epl_cc_address'];
            $txn->CITY = $_POST['_epl_cc_city'];
            $txn->STATE = $_POST['_epl_cc_state'];
            $txn->ZIP = $_POST['_epl_cc_zip'];
            $txn->COUNTRY = $_POST['_epl_cc_country'];

            //https://www.paypalobjects.com/en_US/vhelp/paypalmanager_help/transaction_type_codes.htm
            $txn->TRXTYPE = 'S'; //txn type: sale
            $txn->TENDER = 'C'; //sets to a cc transaction
            $txn->environment = ($gateway_info['_epl_sandbox'] == 10) ? 'test' : 'live';
            //$txn->debug = true; //uncomment to see debugging information
            //$txn->avs_addr_required = 1; //set to 1 to enable AVS address checking, 2 to force "Y" response
            //$txn->avs_zip_required = 1; //set to 1 to enable AVS zip code checking, 2 to force "Y" response
            //$txn->cvv2_required = 1; //set to 1 to enable cvv2 checking, 2 to force "Y" response
            //$txn->fraud_protection = true; //uncomment to enable fraud protection

            $txn->process();

            $data['post_ID'] = $post_ID;

            $data['_epl_grand_total'] = $_totals['money_totals']['grand_total'];
            $data['_epl_payment_amount'] = $txn->AMT;
            $data['_epl_payment_date'] = current_time( 'mysql' );
            $data['_epl_payment_method'] = $this->erm->get_payment_profile_id();
            $data['_epl_transaction_id'] = $txn->response_arr['PNREF'];
            $data['_epl_prediscount_total'] = epl_get_element( 'pre_discount_total', $_totals['money_totals'], 0 );
            $data['_epl_discount_amount'] = epl_get_element( 'discount_amount', $_totals['money_totals'], 0 );

            $data = apply_filters( 'epl_pp_payflow_response_data', $data, $response );

            $this->erm->update_payment_data( $data );

            return true;
        } catch ( TransactionDataException $tde ) {
            $m = $tde->getMessage();
        } catch ( InvalidCredentialsException $e ) {
            $m = 'Invalid credentials';
        } catch ( InvalidResponseCodeException $irc ) {
            $m = $irc->getMessage();
        } catch ( AVSException $avse ) {
            $m = $avse->getMessage();
        } catch ( CVV2Exception $cvve ) {
            $m = $cvve->getMessage();
        } catch ( FraudProtectionException $fpe ) {
            $m = $fpe->getMessage();
        } catch ( Exception $e ) {
            $m = $e->getMessage();
        }

        return '<div class="epl_error">ERROR: ' . $m . '</div>';
    }


    function authnet_aim_process() {

        global $event_details, $cart_totals;
        $event_id = $event_details['ID'];

        // if ( is_null( $event_id ) ) {
        //   return false;
        //}

        $regis_id = $this->erm->get_regis_id();


        $post_ID = $this->erm->get_regis_post_id();


        $this->ecm->setup_event_details( $event_id );

        $_totals = $this->erm->calculate_cart_totals( true );
        $gateway_info = $this->erm->get_gateway_info();

        $post_url = "https://test.authorize.net/gateway/transact.dll";

        if ( $gateway_info['_epl_sandbox'] == 0 )
            $post_url = "https://secure.authorize.net/gateway/transact.dll";



        $post_values = array(
            // the API Login ID and Transaction Key must be replaced with valid values
            "x_login" => $gateway_info['_epl_user'],
            "x_tran_key" => $gateway_info['_epl_pwd'],
            "x_version" => "3.1",
            "x_delim_data" => "TRUE",
            "x_delim_char" => "|",
            "x_relay_response" => "FALSE",
            "x_type" => "AUTH_CAPTURE",
            "x_method" => "CC",
            "x_card_num" => $_POST['_epl_cc_num'],
            "x_card_code" => $_POST['_epl_cc_cvv'],
            "x_exp_date" => date_i18n( "mY", strtotime( $_POST['_epl_cc_exp_month'] . $_POST['_epl_cc_exp_year'] ) ),
            "x_amount" => epl_get_balance_due(),
            "x_description" => $regis_id . ' - ' . preg_replace( '/[^\w\s]+/', '', $event_details['post_title'] ) . ' ' . epl__( 'Qty' ) . ' ' . $cart_totals['_att_quantity']['total'], //no symbols
            "x_first_name" => $_POST['_epl_cc_first_name'],
            "x_last_name" => $_POST['_epl_cc_last_name'],
            "x_address" => $_POST['_epl_cc_address'],
            "x_city" => $_POST['_epl_cc_city'],
            "x_state" => $_POST['_epl_cc_state'],
            "x_zip" => $_POST['_epl_cc_zip'],
            "x_phone" => epl_get_element( '_epl_cc_phone', $_POST, '' ),
            "x_email" => epl_get_element( '_epl_cc_email', $_POST, '' )
                // Additional fields can be added here as outlined in the AIM integration
                // guide at: http://developer.authorize.net
        );

// This section takes the input fields and converts them to the proper format
// for an http post.  For example: "x_login=username&x_tran_key=a1B2c3D4"
        $post_string = "";
        foreach ( $post_values as $key => $value ) {
            $post_string .= "$key=" . urlencode( $value ) . "&";
        }
        $post_string = rtrim( $post_string, "& " );


        $request = curl_init( $post_url ); // initiate curl object
        curl_setopt( $request, CURLOPT_HEADER, 0 ); // set to 0 to eliminate header info from response
        curl_setopt( $request, CURLOPT_RETURNTRANSFER, 1 ); // Returns response data instead of TRUE(1)
        curl_setopt( $request, CURLOPT_POSTFIELDS, $post_string ); // use HTTP POST to send form data
        curl_setopt( $request, CURLOPT_SSL_VERIFYPEER, FALSE ); // uncomment this line if you get no gateway response.
        $post_response = curl_exec( $request ); // execute curl post and store results in $post_response
        // additional options may be required depending upon your server configuration
        // you can find documentation on curl options at http://www.php.net/curl_setopt
        curl_close( $request ); // close curl object

        $response_array = explode( $post_values["x_delim_char"], $post_response );


        if ( is_array( $response_array ) && $response_array[0] == 1 ) { // Payment successful
            $data['post_ID'] = $post_ID;

            $data['_epl_grand_total'] = $cart_totals['money_totals']['grand_total'];
            $data['_epl_payment_amount'] = $response_array[9];
            $data['_epl_payment_date'] = current_time( 'mysql' );
            $data['_epl_payment_method'] = $this->erm->get_payment_profile_id();
            $data['_epl_transaction_id'] = $response_array[6];
            $data['_epl_prediscount_total'] = epl_get_element( 'pre_discount_total', $cart_totals['money_totals'], 0 );
            $data['_epl_discount_amount'] = epl_get_element( 'discount_amount', $cart_totals['money_totals'], 0 );
            $data['_epl_cc_type'] = $response_array[51];

            $data = apply_filters( 'epl_auth_net_aim_response_data', $data, $response_array );

            $this->erm->update_payment_data( $data );


            return true; //echo "DONE";
        }
        else {
            return '<div class="epl_error">ERROR: ' . $response_array[3] . '</div>';
        }
    }


    function usa_epay_process() {

        global $event_details, $cart_totals;
        $event_id = $event_details['ID'];

        $regis_id = $this->erm->get_regis_id();


        $post_ID = $this->erm->get_regis_post_id();


        $this->ecm->setup_event_details( $event_id );

        $_totals = $this->erm->calculate_cart_totals( true );
        $gateway_info = $this->erm->get_gateway_info();

        $this->epl->load_file( 'libraries/gateways/usaepay/usaepay.php' );

        $tran = new EPL_umTransaction;

        $tran->key = $gateway_info['_epl_pwd'];

        $tran->usesandbox = ($gateway_info['_epl_sandbox'] == 10);
        $tran->card = $_POST['_epl_cc_num'];
        $tran->exp = date_i18n( "my", strtotime( $_POST['_epl_cc_exp_month'] . $_POST['_epl_cc_exp_year'] ) );
        $tran->amount = epl_get_balance_due();
        $tran->invoice = $post_ID;
        $tran->cardholder = $_POST['_epl_cc_first_name'] . ' ' . $_POST['_epl_cc_last_name'];
        $tran->street = $_POST['_epl_cc_address'];
        $tran->zip = $_POST['_epl_cc_zip'];
        $tran->description = $regis_id . ' - ' . preg_replace( '/[^\w\s]+/', '', $event_details['post_title'] ) . ' ' . epl__( 'Qty' ) . ' ' . $cart_totals['_att_quantity']['total'];
        $tran->cvv2 = $_POST['_epl_cc_cvv'];

        if ( $tran->Process() ) {

            $data['post_ID'] = $post_ID;

            $data['_epl_grand_total'] = $cart_totals['money_totals']['grand_total'];
            $data['_epl_payment_amount'] = $tran->amount;
            $data['_epl_payment_date'] = current_time( 'mysql' );
            $data['_epl_payment_method'] = $this->erm->get_payment_profile_id();
            $data['_epl_transaction_id'] = $tran->authcode;
            $data['_epl_prediscount_total'] = epl_get_element( 'pre_discount_total', $cart_totals['money_totals'], 0 );
            $data['_epl_discount_amount'] = epl_get_element( 'discount_amount', $cart_totals['money_totals'], 0 );

            $data = apply_filters( 'epl_usa_epay_response_data', $data, $tran );

            $this->erm->update_payment_data( $data );

            return true;
        }
        else {

            return '<div class="epl_error">ERROR<br>' . "<b>Card Declined</b> (" . $tran->result . ")<br>" . "<b>Reason:</b> " . $tran->error . "<br>" . '</div>';
        }
    }


    function firstdata_process() {

        global $event_details, $cart_totals;
        $event_id = $event_details['ID'];


        $regis_id = $this->erm->get_regis_id();


        $post_ID = $this->erm->get_regis_post_id();


        $this->ecm->setup_event_details( $event_id );

        $_totals = $this->erm->calculate_cart_totals( true );
        $gateway_info = $this->erm->get_gateway_info();

        $host = "staging.linkpt.net";

        if ( $gateway_info['_epl_sandbox'] == 0 )
            $host = "secure.linkpt.net";


        //$pem_file = get_attached_file( $gateway_info['_epl_pem_file'] );
        $pem_file = epl_upload_dir_path() . "firstdata/{$gateway_info['_epl_user']}.pem";
        $payment_amount = epl_get_element_m( 'pay_deposit', 'money_totals', $cart_totals ) == 1 ? epl_get_element_m( 'min_deposit', 'money_totals', $cart_totals, $cart_totals['money_totals']['grand_total'] ) : $cart_totals['money_totals']['grand_total'];



        $this->epl->load_file( 'libraries/gateways/firstdata/lphp.php' );
        $_tolphp = array();
        $lphp = new lphp;
        //$_tolphp["debugging"] = ($gateway_info['_epl_sandbox'] == 10); //set to true to see debug message
        $_tolphp["host"] = $host;
        $_tolphp["port"] = "1129";
        $_tolphp["keyfile"] = $pem_file;
        $_tolphp["configfile"] = $gateway_info['_epl_user'];

        $_tolphp["ordertype"] = "SALE";
        $_tolphp["result"] = "LIVE"; # LIVE for live, for test set to GOOD, DECLINE, DUPLICATE
        $_tolphp["cardnumber"] = $_POST['_epl_cc_num'];
        $_tolphp["cardexpmonth"] = date( 'm', strtotime( $_POST['_epl_cc_exp_month'] ) );
        $_tolphp["cardexpyear"] = substr( $_POST['_epl_cc_exp_year'], 0 - 2 );
        $_tolphp["chargetotal"] = epl_get_balance_due();

        $_tolphp["name"] = $_POST['_epl_cc_first_name'] . ' ' . $_POST['_epl_cc_last_name'];
        $_tolphp["address1"] = $_POST['_epl_cc_address'];
        $_tolphp["city"] = $_POST["_epl_cc_city"];
        $_tolphp["state"] = $_POST["_epl_cc_state"];
        $_tolphp["zip"] = $_POST["_epl_cc_zip"];
        $_tolphp["phone"] = epl_get_element( '_epl_cc_phone', $_POST, '' );
        $_tolphp["email"] = epl_get_element( '_epl_cc_email', $_POST, '' );

        $addrnum = $_POST['_epl_cc_address'];

        $temp_address = explode( " ", $_POST['_epl_cc_address'] );

        if ( count( $temp_address ) > 0 )
            $addrnum = $temp_address[0];

        $_tolphp["addrnum"] = $addrnum;

        $result = $lphp->curl_process( $_tolphp );


        if ( is_array( $result ) && $result['r_approved'] == 'APPROVED' ) { // Payment successful
            $data['post_ID'] = $post_ID;

            $data['_epl_grand_total'] = $cart_totals['money_totals']['grand_total'];
            $data['_epl_payment_amount'] = $cart_totals['money_totals']['grand_total'];
            $data['_epl_payment_date'] = current_time( 'mysql' );
            $data['_epl_payment_method'] = $this->erm->get_payment_profile_id();
            $data['_epl_transaction_id'] = $result['r_ref'];
            $data['_epl_prediscount_total'] = epl_get_element( 'pre_discount_total', $cart_totals['money_totals'], 0 );
            $data['_epl_discount_amount'] = epl_get_element( 'discount_amount', $cart_totals['money_totals'], 0 );

            $data = apply_filters( 'epl_firstdata_response_data', $data, $result );

            $this->erm->update_payment_data( $data );

            return true;
        }
        else {

            return '<div class="epl_error">ERROR: ' . $result['r_error'] . '</div>';
        }
    }


    function setup_authnet_sim_form() {



        global $event_details;
        $event_id = $event_details['ID'];

        if ( is_null( $event_id ) ) {
            return false;
        }

        $regis_id = $this->erm->get_regis_id();


        $post_ID = $this->erm->get_regis_post_id();


        $this->ecm->setup_event_details( $event_id );

        $_totals = $this->erm->calculate_cart_totals();
        $gateway_info = $this->erm->get_gateway_info();
        $gateway_id = $this->erm->get_payment_profile_id();


        $this->epl->load_file( 'libraries/gateways/authnet/AuthorizeNet.php' );
        $api_login_id = $gateway_info['_epl_user'];
        $transaction_key = $gateway_info['_epl_pwd'];
        //$amount = number_format( $_totals['money_totals']['grand_total'], 2, '.', '' );
        $amount = number_format( epl_get_balance_due(), 2, '.', '' );
        $description = $event_details['post_title'] . ', Qty: ' . $_totals['_att_quantity']['total'][$event_details['ID']];
        $fp_timestamp = time();
        $fp_sequence = $regis_id . time(); // Enter an invoice or other unique number.
        $fingerprint = AuthorizeNetSIM_Form::getFingerprint( $api_login_id, $transaction_key, $amount, $fp_sequence, $fp_timestamp );

        $return_url = get_permalink( $post_ID );

        $relay_url = add_query_arg( array( 'epl_action' => '_authnet_sim_process' ), epl_get_shortcode_page_permalink() );

        $first_name = epl_get_attendee_form_value( 'ticket_buyer', 'first_name' );
        $last_name = epl_get_attendee_form_value( 'ticket_buyer', 'last_name' );
        $address = epl_get_attendee_form_value( 'ticket_buyer', 'address' );
        $city = epl_get_attendee_form_value( 'ticket_buyer', 'city' );
        $state = epl_get_attendee_form_value( 'ticket_buyer', 'state' );
        $zip = epl_get_attendee_form_value( 'ticket_buyer', 'zip' );
        $email = epl_get_attendee_form_value( 'ticket_buyer', 'email' );

        //type=hidden doesn't work.  using a hidden wrapper div
        $this->redirect_form_data = <<< EOT
<div style="display:none">

            <input type='text' name="x_login" value="$api_login_id" />
            <input type='text' name="x_fp_hash" value="$fingerprint" />
            <input type='text' name="x_amount" value="$amount" />
            <input type='text' name="x_fp_timestamp" value="$fp_timestamp" />
            <input type='text' name="x_fp_sequence" value="$fp_sequence" />
            <input type='text' name="x_description" value="$description" />
            <input type='text' name="x_version" value="3.1" />
            <input type='text' name="x_show_form" value="PAYMENT_FORM" />
            <input type='text' name="x_test_request" value="false" />
            <input type='text' name="x_method" value="cc" />


            <input type='text' name="x_invoice_num" value="$regis_id" />
            <input type='text' name="x_first_name" value="$first_name" />
            <input type='text' name="x_last_name" value="$last_name" />
            <input type='text' name="x_address" value="$address" />
            <input type='text' name="x_city" value="$city" />
            <input type='text' name="x_state" value="$state" />
            <input type='text' name="x_zip" value="$zip" />
            <input type='text' name="x_email" value="$email" />

            <input type='text' name="e_ID" value="$event_id" />
            <input type='text' name="p_ID" value="$post_ID" />
            <input type='text' name="r_ID" value="$regis_id" />
            <input type='text' name="g_ID" value="$gateway_id" />

            <input type='text' name="x_receipt_link_method" value="POST" />
          <input type='text' name="x_receipt_link_text" value="PLEASE CLICK HERE TO FINISH YOUR REGISTRATION" />
          <input type='text' name="x_receipt_link_URL" value="$relay_url" />
              

</div>


EOT;

        /*
         *                         <input type='text' name="x_relay_response" value="TRUE">
          <input type='text' name="x_relay_url" value="$relay_url">
         *
         */
    }


    function _authnet_sim_process() {

        global $event_details, $wpdb;

        $event_id = intval( $_POST['e_ID'] );


        if ( is_null( $event_id ) ) {
            return false;
        }

        $regis_id = $wpdb->escape( $_POST['r_ID'] );



        $regis_id = $this->erm->get_regis_id();


        $post_ID = $this->erm->get_regis_post_id();

        //may need to resort to this if
        /*
          $post_ID = intval( $_POST['p_ID'] );
          $gateway_id = intval( $_POST['g_ID'] );

         *
          $regis_meta = ( array ) $this->ecm->setup_regis_details( $post_ID );
          $this->ecm->setup_event_details( $event_id );
          $this->erm->setup_current_data( $regis_meta['__epl'] );
         */

        $_totals = $this->erm->calculate_cart_totals();


        $gateway_info = $this->erm->get_gateway_info( $gateway_id );
        $api_login_id = $gateway_info['_epl_user'];
        $transaction_key = $gateway_info['_epl_pwd'];

        $this->epl->load_file( 'libraries/gateways/authnet/AuthorizeNet.php' );
        $response = new AuthorizeNetSIM( $api_login_id, $gateway_info['_epl_md5_hash'] );


        if ( $response->isAuthorizeNet() ) {



            if ( $response->approved ) {


                $data['post_ID'] = $post_ID;
                $data['_epl_regis_status'] = 5;
                $data['_epl_grand_total'] = epl_get_element( 'x_amount', $_POST );
                $data['_epl_payment_amount'] = epl_get_element( 'x_amount', $_POST );
                $data['_epl_payment_date'] = current_time( 'mysql' );
                $data['_epl_transaction_id'] = epl_get_element( 'x_trans_id', $_POST );

                $this->erm->update_payment_data( $data );

                return true;
            }
            else {
                return '<div class="epl_error">ERROR: ' . $response->response_reason_text . '</div>';
            }
        }
    }

    /*
     * Quickbooks merchant services, using the desktop method
     */


    function qbmc_process() {

        global $event_details;
        $event_id = $event_details['ID'];

        if ( is_null( $event_id ) ) {
            return false;
        }

        $regis_id = $this->erm->get_regis_id();


        $post_ID = $this->erm->get_regis_post_id();


        $this->ecm->setup_event_details( $event_id );

        $_totals = $this->erm->calculate_cart_totals();
        $gateway_info = $this->erm->get_gateway_info();

        $this->epl->load_file( 'libraries/gateways/qbmc/QuickBooks.php' );

        $dsn = null;
        $path_to_private_key_and_certificate = null;
        $application_login = trim( $gateway_info['_epl_user'] );

        $connection_ticket = trim( $gateway_info['_epl_pwd'] );

        $MS = new QuickBooks_MerchantService(
                $dsn, $path_to_private_key_and_certificate, $application_login, $connection_ticket );


        $test = ($gateway_info['_epl_sandbox'] == 0) ? false : true;
        $MS->useTestEnvironment( $test );


        $MS->useDebugMode( false );

        foreach ( $_POST as $k => &$v )
            trim( esc_attr( $v ) );


        $name = $_POST['_epl_cc_first_name'] . ' ' . $_POST['_epl_cc_last_name'];

        $number = $_POST['_epl_cc_num'];
        $expyear = $_POST['_epl_cc_exp_year'];
        $expmonth = date_i18n( "m", strtotime( $_POST['_epl_cc_exp_month'] ) );
        $address = $_POST['_epl_cc_address'];
        $postalcode = $_POST['_epl_cc_zip'];
        $cvv = $_POST['_epl_cc_cvv'];



        $Card = new QuickBooks_MerchantService_CreditCard( $name, $number, $expyear, $expmonth, $address, $postalcode, $cvv );

        $amount = $_totals['money_totals']['grand_total'];

        if ( $Transaction = $MS->authorize( $Card, $amount ) ) {

            $str = $Transaction->serialize();

            // Now convert it back to a transaction object
            $Transaction = QuickBooks_MerchantService_Transaction::unserialize( $str );

            $arr = $Transaction->toArray();
            // ... and back again?
            $Transaction = QuickBooks_MerchantService_Transaction::fromArray( $arr );

            // ... or an XML document?
            $xml = $Transaction->toXML();


            $Transaction = QuickBooks_MerchantService_Transaction::fromXML( $xml );


            if ( $Transaction = $MS->capture( $Transaction, $amount ) ) {

                $arr = $Transaction->toArray();

                $transactionId = $arr['CreditCardTransID'];


                $data['post_ID'] = $post_ID;

                $data['_epl_grand_total'] = epl_get_balance_due();
                $data['_epl_payment_amount'] = $_totals['money_totals']['grand_total'];
                $data['_epl_payment_date'] = current_time( 'mysql' );
                $data['_epl_payment_method'] = $this->erm->get_payment_profile_id();
                $data['_epl_transaction_id'] = $transactionId;
                $data['_epl_prediscount_total'] = epl_get_element( 'pre_discount_total', $_totals['money_totals'], 0 );
                $data['_epl_discount_amount'] = epl_get_element( 'discount_amount', $_totals['money_totals'], 0 );

                $data = apply_filters( 'epl_auth_net_aim_response_data', $data, $Transaction );

                $this->erm->update_payment_data( $data );


                return true;
            }
            else {

                return '<div class="epl_error">ERROR: ' . $MS->errorNumber() . ': ' . $MS->errorMessage() . '</div>';
                // print('An error occured during capture: ' . $MS->errorNumber() . ': ' . $MS->errorMessage() . "\n" );
            }
        }
        else {

            return '<div class="epl_error">ERROR: ' . $MS->errorNumber() . ': ' . $MS->errorMessage() . '</div>';
            //print('An error occured during authorization: ' . $MS->errorNumber() . ': ' . $MS->errorMessage() . "\n" );
        }
    }


    function payson_process( $ipn = false ) {

        global $event_details;

        $event_id = $this->erm->get_current_event_id(); //key( ( array ) $_SESSION['__epl'][$regis_id]['events'] );

        if ( is_null( $event_id ) ) {
            return false;
        }

        $this->epl->load_file( 'libraries/gateways/payson/paysonapi.php' );

        $url = epl_get_url();

        $regis_id = epl_get_element( 'regis_id', $_REQUEST, $this->erm->get_regis_id() );
        $post_ID = epl_get_element( 'trackingId', $_REQUEST ) ? intval( epl_get_element( 'trackingId', $_REQUEST ) ) : $this->erm->get_regis_post_id();


        if ( !$ipn ) {
            $gwID = epl_get_element( 'custom', $_REQUEST ) ? intval( epl_get_element( 'custom', $_REQUEST ) ) : $this->erm->get_payment_profile_id();

            $gw_credentials = $this->_get_credentials( $gwID, array( '_epl_seller_email' ) );
            $credentials = new PaysonCredentials( $gw_credentials['_epl_user'], $gw_credentials['_epl_pwd'] );
            $api = new PaysonApi( $credentials );
        }
        // echo "<pre class='prettyprint'>" . __LINE__ . "> " . print_r(get_post_meta($post_ID, '_epl_payment_method'), true). "</pre>";
        //ipn processor
        if ( $ipn && $post_ID ) {

            $gwID = get_post_meta( $post_ID, '_epl_payment_method', true );
            $gw_credentials = $this->_get_credentials( $gwID, array( '_epl_seller_email' ) );

            $credentials = new PaysonCredentials( $gw_credentials['_epl_user'], $gw_credentials['_epl_pwd'] );
            $api = new PaysonApi( $credentials );


            $postData = file_get_contents( "php://input" );

            $response = $api->validate( $postData );

            if ( $response->isVerified() ) {
                // IPN request is verified with Payson
                // Check details to find out what happened with the payment
                $details = $response->getPaymentDetails();



                $data['post_ID'] = $post_ID;



                $data['_epl_regis_status'] = 5;
                $data['_epl_payment_amount'] = get_post_meta( $post_ID, '_epl_grand_total', true ); //$details->receivers[0]['amount'];
                $data['_epl_payment_date'] = current_time( 'mysql' );

                //$data['_epl_transaction_id'] = $details->purchaseId;
                //$data = apply_filters( 'epl_payson_before_update_db', $data );



                $this->erm->update_payment_data( $data );
            }

            return true;
        }

        $this->ecm->setup_event_details( $event_id );

        $_totals = $this->erm->calculate_cart_totals();

        $returnUrl = add_query_arg( array( 'cart_action' => false, 'trackingId' => $post_ID, 'gw_id' => $gwID, 'custom' => $gwID, 'epl_action' => '_payson_success' ), $url );
        $cancelUrl = add_query_arg( array( 'cart_action' => false, 'trackingId' => $post_ID, 'gw_id' => $gwID, 'custom' => $gwID, 'epl_action' => 'regis_form' ), $url );

        $ipnUrl = add_query_arg( array( 'cart_action' => false, 'trackingId' => $post_ID, 'gw_id' => $gwID, 'custom' => $gwID, 'epl_action' => '_payson_ipn' ), $url );


        $buyer_email = $this->erm->get_primary_field_value( 'email' );
        $buyer_f_name = $this->erm->get_primary_field_value( 'first_name' );
        $buyer_l_email = $this->erm->get_primary_field_value( 'last_name' );
        $total = $_totals['money_totals']['grand_total'];
        $description = $event_details['post_title'];

        $receiver = new Receiver(
                $gw_credentials['_epl_seller_email'], // The email of the account to receive the money
                number_format( $total, 2, ',', '' ) ); // The amount you want to charge the user, here in SEK (the default currency)
        $receivers = array( $receiver );

        $sender = new Sender( $buyer_email, $buyer_f_name, $buyer_l_email );


        $payData = new PayData( $returnUrl, $cancelUrl, $ipnUrl, $description, $sender, $receivers );

// Set guarantee options
        $payData->setGuaranteeOffered( GuaranteeOffered::NO );

        /*
         * Step 2 initiate payment
         */
        $payResponse = $api->pay( $payData );

        /*
         * Step 3: verify that it suceeded
         */
        if ( $payResponse->getResponseEnvelope()->wasSuccessful() ) {

            $data['post_ID'] = $post_ID;
            $data['_epl_regis_status'] = 2;
            $data['_epl_grand_total'] = number_format( epl_get_balance_due(), 2 );
            $data['_epl_payment_amount'] = 0;
            $data['_epl_payment_date'] = '';
            $data['_epl_payment_method'] = $gwID;
            $data['_epl_transaction_id'] = '';
            $data['_epl_prediscount_total'] = number_format( epl_get_element( 'pre_discount_total', $_totals['money_totals'], 0 ), 2 );
            $data['_epl_discount_amount'] = number_format( epl_get_element( 'discount_amount', $_totals['money_totals'], 0 ), 2 );

            $data = apply_filters( 'epl_payson_before_update_db', $data );

            $this->erm->update_payment_data( $data );

            return $payResponse; //need to return the $payResponse object
        }
        else {
            return false;
        }
    }


    function setup_moneris_form() {


        global $event_details, $cart_totals;
        $event_id = $event_details['ID'];


        $regis_id = $this->erm->get_regis_id();


        $post_ID = $this->erm->get_regis_post_id();


        $this->ecm->setup_event_details( $event_id );

        $_totals = $this->erm->calculate_cart_totals();
        $gateway_info = $this->erm->get_gateway_info();

        $test_mode = ( $gateway_info['_epl_sandbox'] == 10 );
        $store_id = $gateway_info['_epl_user'];
        $api_token = $gateway_info['_epl_pwd'];

        $amount = number_format( epl_get_balance_due(), 2, '.', '' );

        $ind_items = '';

        $discount_amount = epl_get_element( 'discount_amount', $_totals['money_totals'], 0 );

        if ( $discount_amount > 0 ) {

            $discount_description = epl_get_element( 'discount_description', $_totals['money_totals'], null );

            $discount_description = ($discount_description) ? ", ({$discount_description}) " : '';



            $ind_items .= <<<EOT

                        <input type="hidden" name="description0" value="{$event_details['post_title']}, {$_totals['_att_quantity']['total'][$event_details['ID']]} {$discount_description}" />
                        <input type="hidden" name="quantity0" value="1" />
                        <input type="hidden" name="price0" value="{$amount}" />
                        <input type="hidden" name="subtotal0" value="$amount" />
EOT;
        }
        else {


            $counter = 0;
            $tickets = $_SESSION['__epl'][$regis_id]['_dates']['_att_quantity'][$event_id];
            $dates = (isset( $_SESSION['__epl'][$regis_id]['_dates']['_epl_start_date'][$event_id] )) ? $_SESSION['__epl'][$regis_id]['_dates']['_epl_start_date'][$event_id] : array();
            $num_days_in_cart = count( $dates );
            $price_multiplier = (($event_details['_epl_price_per'] == 10) ? $num_days_in_cart : 1);

            $price_multiplier_label = ($price_multiplier > 1) ? ' - ' . $num_days_in_cart . ' ' . epl__( 'days' ) : '';

            $item = array();
            foreach ( $tickets as $ticket_id => $ticket_qty ) {

                $ticket_name = epl_get_element( $ticket_id, $event_details['_epl_price_name'] );
                $ticket_price = epl_get_element( $ticket_id, $event_details['_epl_price'] );
                $qty = (is_array( $ticket_qty )) ? array_sum( $ticket_qty ) : $ticket_qty;

                if ( $qty > 0 ) {

                    $_qty = $qty * $price_multiplier;
                    $_subtotal = number_format( $_qty * $ticket_price, 2, '.', '' );
                    $ind_items .= <<<EOT
                       
                        <input type="hidden" name="description{$counter}" value="{$event_details['post_title']} {$ticket_name} {$price_multiplier_label}">
                        <input type="hidden" name="quantity{$counter}" value="{$_qty}">
                        <input type="hidden" name="price{$counter}" value="{$ticket_price}">
                        <input type="hidden" name="subtotal{$counter}" value="$_subtotal">
EOT;
                    $counter++;
                }
            }
        }

        $total = number_format( $cart_totals['money_totals']['grand_total'], 2, '.', '' );

        $this->redirect_form_data = <<< EOT
            <input type="hidden" name="ps_store_id" value="$store_id" />
            <input type="hidden" name="hpp_key" value="$api_token" />
            <input type="hidden" name="charge_total" value="{$total}" />
            {$ind_items}
            <input type="hidden" name="rvar_pid" value="{$post_ID}" />
            <input type="hidden" name="rvar_rid" value="{$regis_id}" />
            <input type="hidden" name="rvar_eid" value="{$event_id}" />
EOT;
    }


    //from Peder
    function setup_moneris_form_USA() {
        global $event_details;
        //global $customer_email;

        $event_id = $event_details['ID'];

        $gateway_info = $this->erm->get_gateway_info();
        $store_id = $gateway_info['_epl_user'];
        $api_token = $gateway_info['_epl_pwd'];
        $regis_id = $this->erm->get_regis_id();
        $post_ID = $_SESSION['__epl']['post_ID'];

        $this->ecm->setup_event_details( $event_id );
        $_totals = $this->erm->calculate_cart_totals();


        $test_mode = ( $gateway_info['_epl_sandbox'] == 10 );
        $amount = number_format( $_totals['money_totals']['grand_total'], 2, '.', '' );
        $ind_items = '';


        $discount_amount = epl_get_element( 'discount_amount', $_totals['money_totals'], 0 );

        if ( $discount_amount > 0 ) {
            $discount_description = epl_get_element( 'discount_description', $_totals['money_totals'], null );
            $discount_description = ($discount_description) ? ", ({$discount_description}) " : '';
            //-- this needs to be looked at, moneris USA doesn't do zero suffix
            $ind_items .= "<input type='hidden' name='li_description0' value='" . $event_details['post_title'] . " " . $_totals['_att_quantity']['total'][$event_details['ID']] . " " . $discount_description . "' />";
            $ind_items .= "<input type='hidden' name='li_quantity0' value='1' />";
            $ind_items .= "<input type='hidden' name='li_price0' value='$discount_amount' />";
        }
        else {
            $counter = 1;
            $tickets = $_SESSION['__epl'][$regis_id]['_dates']['_att_quantity'][$event_id];

            $dates = (isset( $_SESSION['__epl'][$regis_id]['_dates']['_epl_start_date'][$event_id] )) ? $_SESSION['__epl'][$regis_id]['_dates']['_epl_start_date'][$event_id] : array();
            $num_days_in_cart = count( $dates );
            $price_multiplier = (($event_details['_epl_price_per'] == 10) ? $num_days_in_cart : 1);

            $price_multiplier_label = ($price_multiplier > 1) ? ' - ' . $num_days_in_cart . ' ' . epl__( 'days' ) : '';

            $item = array();
            foreach ( $tickets as $ticket_id => $ticket_qty ) {
                $ticket_name = epl_get_element( $ticket_id, $event_details['_epl_price_name'] );
                $ticket_price = epl_get_element( $ticket_id, $event_details['_epl_price'] );
                $qty = (is_array( $ticket_qty )) ? array_sum( $ticket_qty ) : $ticket_qty;

                if ( $qty > 0 ) {
                    $_qty = $qty * $price_multiplier;
                    $_subtotal = number_format( $_qty * $ticket_price, 2, '.', '' );

                    //-- description must be sanitazied to only include alpha/num (no spaces ?) 
                    $ind_items .= "<input type='hidden' name='li_description$counter' value='" . $event_details['post_title'] . " " . $price_multiplier_label . "'>";
                    $ind_items .= "<input type='hidden' name='li_quantity$counter' value='$qty'>";
                    $ind_items .= "<input type='hidden' name='li_price$counter' value='$_subtotal'>";
                    //-- not sure what variable to pick
                    $ind_items .= "<input type='hidden' name='li_id$counter' value='$ticket_name'>";
                    $counter++;
                }
            }
        }

        $first_name = epl_get_attendee_form_value( 'ticket_buyer', 'first_name' );
        $last_name = epl_get_attendee_form_value( 'ticket_buyer', 'last_name' );
        $address = epl_get_attendee_form_value( 'ticket_buyer', 'address' );
        $city = epl_get_attendee_form_value( 'ticket_buyer', 'city' );
        $state = epl_get_attendee_form_value( 'ticket_buyer', 'state' );
        $zip = epl_get_attendee_form_value( 'ticket_buyer', 'zip' );
        $email = epl_get_attendee_form_value( 'ticket_buyer', 'email' );

        $total = number_format( $_totals['money_totals']['grand_total'], 2, '.', '' );

        $this->redirect_form_data = '';
        $this->redirect_form_data .= "<input type='hidden' name='hpp_id' value='$store_id' />";
        $this->redirect_form_data .= "<input type='hidden' name='hpp_key' value='$api_token' />";
        $this->redirect_form_data .= "<input type='hidden' name='amount' value='$total' />";
        //-- must be unique, regis_id should be OK
        $this->redirect_form_data .= "<input type='hidden' name='order_no' value='$regis_id' />";
        //--get registration email, so they can get receipt

        if ( !$test_mode )
            $this->redirect_form_data .= "<input type='hidden' name='client_email' value='$email' />";
        //--not sure what to use, payee name better then nothing. max 50 chars
        //$cust_id = substr( preg_replace( "/[^a-zA-Z0-9]/", " ", $first_name . " " . $last_name ), 0, 50 );
        $this->redirect_form_data .= "<input type='hidden' name='cust_id' value='$regis_id' />";
        //--not sure what to use, add option in payment profile, Thank you for choosing.....
        $this->redirect_form_data .= "<input type='hidden' name='note' value='$note' />";
        $this->redirect_form_data .= $ind_items;
        $this->redirect_form_data .= "<input type='hidden' name='rvar_pid' value='$post_ID' />";
        $this->redirect_form_data .= "<input type='hidden' name='rvar_rid' value='$regis_id' />";
        $this->redirect_form_data .= "<input type='hidden' name='rvar_eid' value='$event_id' />";
        $this->redirect_form_data .= "<input type='hidden' name='li_shipping' value='0.00' />";
        $this->redirect_form_data .= "<input type='hidden' name='li_taxes' value='0.00' />";
    }


    function moneris_process() {

        global $event_details;
        $event_id = $this->erm->get_current_event_id();

        $regis_id = $this->erm->get_regis_id();

        $msg = array();
        $post_ID = $this->erm->get_regis_post_id();


        $this->ecm->setup_event_details( $event_id );

        $_totals = $this->erm->calculate_cart_totals();
        $gateway_info = $this->erm->get_gateway_info();
        $store_id = $gateway_info['_epl_user'];
        $api_token = $gateway_info['_epl_pwd'];

        $transactionKey = epl_get_element( 'transactionKey', $_POST );

        $response_array = EPL_util::get_instance()->clean_input( $_POST );

        $rvar_eid = epl_get_element( 'rvar_eid', $response_array );
        $rvar_pid = epl_get_element( 'rvar_pid', $response_array );
        $rvar_rid = epl_get_element( 'rvar_rid', $response_array );

        $regis_id = $this->erm->get_regis_id();
        $response_code = epl_get_element( 'response_code', $_POST );
        if ( $response_code != 'null' ) {
            if ( $response_code < 50 ) {
                $status = 'paid';
            }
            else {
                $status = 'declined';
            }
        }
        else {
            $status = 'incomplete';
        }

        //approved
        if ( $status == 'paid' ) {

            $payment_amount = epl_get_element( 'charge_total', $response_array );
            $txn_id = epl_get_element( 'response_order_id', $response_array );

            if ( epl_get_element( '_epl_moneris_country', $gateway_info, 'ca' ) == 'usa' ) {
                $payment_amount = epl_get_element( 'amount', $response_array );
                $txn_id = epl_get_element( 'txn_num', $response_array );
            }

            $data['post_ID'] = $post_ID;

            $data['_epl_grand_total'] = number_format( epl_get_balance_due(), 2, '.', '' );
            $data['_epl_payment_amount'] = $payment_amount;
            $data['_epl_payment_date'] = current_time( 'mysql' );
            $data['_epl_payment_method'] = $this->erm->get_payment_profile_id();
            $data['_epl_transaction_id'] = $txn_id;
            $data['_epl_prediscount_total'] = number_format( epl_get_element( 'pre_discount_total', $_totals['money_totals'], 0 ), 2, '.', '' );
            $data['_epl_discount_amount'] = number_format( epl_get_element( 'discount_amount', $_totals['money_totals'], 0 ), 2, '.', '' );

            $this->erm->update_payment_data( $data );

            return true;
        }
        else {
            return '<div class="epl_error">ERROR: ' . $response_array['message'] . '</div>';
        }
    }


    function setup_stripe_form( $fields = array() ) {


        global $event_details, $cart_totals;
        $event_id = $event_details['ID'];


        $regis_id = $this->erm->get_regis_id();


        $post_ID = $this->erm->get_regis_post_id();

        $this->epl->load_file( 'libraries/gateways/stripe/Stripe.php' );
        $this->ecm->setup_event_details( $event_id );

        $_totals = $this->erm->calculate_cart_totals();
        $gateway_info = $this->erm->get_gateway_info();

        $test_mode = ( $gateway_info['_epl_sandbox'] == 10 );
        $secret_key = $gateway_info['_epl_secret_key'];
        $publishable_key = $gateway_info['_epl_publishable_key'];
        $data = array();

        $data['stripe'] = array(
            "secret_key" => $secret_key,
            "publishable_key" => $publishable_key,
        );

        $data['exp_fields'] = array(
            'exp_month' => array(
                'input_type' => 'select',
                'class' => 'epl_w70',
                'options' => epl_month_dd( true ),
                'data_attr' => array( 'stripe' => 'exp-month' )
            ),
            'exp_year' => array(
                'weight' => 60,
                'input_type' => 'select',
                'options' => epl_make_array( date_i18n( 'Y' ), date_i18n( 'Y' ) + 10 ),
                'class' => 'epl_w70',
                'data_attr' => array( 'stripe' => 'exp-year' )
            ),
        );

        $data['exp_month'] = $this->epl_util->create_element( $data['exp_fields']['exp_month'] );
        $data['exp_year'] = $this->epl_util->create_element( $data['exp_fields']['exp_year'] );

        Stripe::setApiKey( $data['stripe']['secret_key'] );

        $amount = number_format( epl_get_balance_due(), 2, '.', '' );

        return $this->epl->load_view( 'front/registration/regis-stripe-form', $data, true );
    }


    function stripe_process() {

        global $event_details, $cart_totals;
        $event_id = $event_details['ID'];

        $regis_id = $this->erm->get_regis_id();

        $post_ID = $this->erm->get_regis_post_id();
        $this->epl->load_file( 'libraries/gateways/stripe/Stripe.php' );
        $this->ecm->setup_event_details( $event_id );

        $_totals = $this->erm->calculate_cart_totals( true );
        $gateway_info = $this->erm->get_gateway_info();

        $secret_key = $gateway_info['_epl_secret_key'];
        $publishable_key = $gateway_info['_epl_publishable_key'];


        $data['stripe'] = array(
            "secret_key" => $secret_key,
            "publishable_key" => $publishable_key
        );

        Stripe::setApiKey( $data['stripe']['secret_key'] );
// Get the credit card details submitted by the form
        $token = $_POST['stripeToken'];

// Create the charge on Stripe's servers - this will charge the user's card
        try {
            $charge = Stripe_Charge::create( array(
                        "amount" => epl_get_balance_due() * 100, // amount in cents, again
                        "currency" => strtolower( epl_nz( epl_get_general_setting( 'epl_currency_code' ), 'USD' ) ),
                        "card" => $token,
                        "description" => $event_details['post_title'],
                        "metadata" => array( "regis_id" => $post_ID )
                            )
            );

            $data['post_ID'] = $post_ID;

            $data['_epl_grand_total'] = $cart_totals['money_totals']['grand_total'];
            $data['_epl_payment_amount'] = $cart_totals['money_totals']['grand_total'];
            $data['_epl_payment_date'] = current_time( 'mysql' );
            $data['_epl_payment_method'] = $this->erm->get_payment_profile_id();
            $data['_epl_transaction_id'] = $charge->id;
            $data['_epl_prediscount_total'] = epl_get_element( 'pre_discount_total', $cart_totals['money_totals'], 0 );
            $data['_epl_discount_amount'] = epl_get_element( 'discount_amount', $cart_totals['money_totals'], 0 );

            $data = apply_filters( 'epl_stripe_response_data', $data );

            $this->erm->update_payment_data( $data );

            return true;
        } catch ( Stripe_CardError $e ) {
            $body = $e->getJsonBody();
            $err = $body['error'];
            return '<div class="epl_error">Error: ' . $err['message'] . '</div>';
        }
    }


    function get_redirect_form_data() {

        return $this->redirect_form_data;
    }


    private function _get_credentials( $gateway_id = null, $credentials = array() ) {


        $default_credentials = array( '_epl_user', '_epl_pwd' );

        if ( !epl_is_empty_array( $credentials ) )
            $credentials = wp_parse_args( $credentials, $default_credentials );
        else
            $credentials = $default_credentials;

        $gateway_info = $this->erm->get_gateway_info( $gateway_id );

        $r = array();

        foreach ( $credentials as $cred ) {
            $r[$cred] = epl_get_element( $cred, $gateway_info );
        }

        return $r;
    }

}
