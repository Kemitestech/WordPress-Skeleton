<?php

class EPL_discount_model extends EPL_Model {

    private static $instance;


    function __construct() {
        parent::__construct();

        global $event_details;
        self::$instance = $this;

        $this->ecm = $this->epl->load_model( 'epl_common_model' );

        $this->discount_amount = 0;
        $this->discounted_amount = 0;
        $this->auto_discounts = array();
        $this->all_discount_configs = array();
        $this->auto_discount_source = array();
    }


    public static function get_instance() {
        if ( !self::$instance ) {

            self::$instance = new EPL_discount_model;
        }

        return self::$instance;
    }


    function get_available_discount_codes() {

// $this->get_event_discount_codes();

        return $this->get_global_discount_configs();
    }


    function process_discount( $totals, $glob_disc = false ) {

        if ( epl_is_empty_array( $totals ) )
            return $totals;

        static $processed = false;

        //if ( $processed )
        //  return $totals;
        $processed = true;
        global $event_details, $epl_current_step;
        $this->discount_config_id = $event_details['ID'];
        $this->all_discount_configs[$this->discount_config_id] = $event_details;
        $this->totals = $totals;
        $this->original_totals = $totals;

        $this->discount_configs = $event_details;
        if ( epl_sc_is_enabled() )
            $this->discount_configs = $this->get_global_discount_configs( true );

        $_discount = array();

        $_entered_code = strtoupper( $this->discount_code_entered( $this->totals ) );
        $allow_global_discounts = (epl_get_element( '_epl_allow_global_discounts', $event_details, 0 ) == 10);
        //if discount code entered
        if ( $_entered_code ) {
            //check if the code exists

            $this->totals['money_totals']['discount_code'] = $_entered_code;

            if ( (!$available_discount_codes || $glob_disc) && $allow_global_discounts ) {

                if ( epl_sc_is_enabled() )
                    $this->all_discount_configs = $this->get_global_discount_configs();
                elseif ( $allow_global_discounts )
                    $this->all_discount_configs += $this->get_global_discount_configs();
            }

            $this->get_event_discount_codes();

            if ( !$this->code_discounts )
                return $this->totals;

            foreach ( $this->code_discounts as $discount_source_id => $active_discounts ) {

                $_code_id = array_search( strtolower( $_entered_code ), ( array ) $active_discounts );
                $this->discount_configs = $this->all_discount_configs[$discount_source_id];

                if ( $_code_id !== false ) {
                    $this->discount_source = $this->code_discounts_source[$discount_source_id];
                    $this->discount_source_id = $discount_source_id;

                    break;
                }
            }

            if ( $_code_id !== false ) {


                //if ( !EPL_IS_ADMIN ) {
                //code expired
                if ( $this->is_code_expired( $_code_id ) ) {
                    $this->totals['money_totals']['discount_code_id'] = '';
                    $this->totals['money_totals']['discount_code'] = '';
                    $this->totals['money_totals']['discount_message'] = epl__( 'The discount code has expired' );
                    return $this->totals;
                }

                //code use count exceeded
                if ( $this->is_code_maxed_out( $_code_id, $_entered_code ) ) {
                    $this->totals['money_totals']['discount_code_id'] = '';
                    $this->totals['money_totals']['discount_code'] = '';
                    $this->totals['money_totals']['discount_message'] = epl__( 'The discount code is no longer available' );
                    return $this->totals;
                }

                $_categories = epl_get_element( $_code_id, epl_get_element( '_epl_discount_cat_include', $this->discount_configs ), 0 );

                if ( !epl_is_empty_array( $_categories ) ) {

                    $this->adjust_discountable_amounts( 'category', $_categories );
                }

                /* TODO removing as of > 2.0.8
                  $_pay_profiles = epl_get_element( $_code_id, epl_get_element( '_epl_discount_pay_specific', $this->discount_configs ), 0 );

                  if ( !epl_is_empty_array( $_pay_profiles ) ) {

                  $this->adjust_discountable_amounts( 'pay_profiles', $_pay_profiles );
                  }
                 */

                $is_condition_ok = $this->is_condition_ok( $_code_id, $this->totals );
                if ( $is_condition_ok !== true ) {
                    $this->totals['money_totals']['discount_code_id'] = '';
                    $this->totals['money_totals']['discount_code'] = '';
                    $this->totals['money_totals']['discount_message'] = $is_condition_ok;
                    return $this->totals;
                }
                //}

                $amount_to_discount = epl_get_element( $_code_id, epl_get_element( '_epl_discount_amount', $this->discount_configs ) );
                $discount_description = epl_get_element( $_code_id, epl_get_element( '_epl_discount_description', $this->discount_configs ) );
                $discount_type = epl_get_element( $_code_id, epl_get_element( '_epl_discount_type', $this->discount_configs ) );

                $discounted_amount = $this->calculate_discount( $this->totals['money_totals'], $amount_to_discount, $discount_type );


                if ( $discounted_amount >= 0 ) {

                    $this->totals['money_totals']['pre_discount_total'] = $this->totals['money_totals']['grand_total'];
                    //$this->totals['money_totals']['discount_amount'] = $this->discount_amount;
                    $this->totals['money_totals']['discount_description'] = epl_suffix( ', ', $this->totals['money_totals']['discount_description'] ) . $discount_description;
                    $this->totals['money_totals']['discount_code_id'] = $_code_id;
                    $this->totals['money_totals']['discount_source_id'] = $this->discount_source_id;

                    //$this->totals['money_totals']['grand_total'] = $discounted_amount;
                }
            }
            else {

                //if ( !$glob_disc )
                //  $this->process_discount( $totals, true );
                //else
                $this->totals['money_totals']['discount_message'] = epl__( 'This discount code is invalid' );
            }
        }
        else {
            //check for automatic discounts
            //get list of available automatic discounts
            //$this->get_event_auto_discounts();

            if ( epl_sc_is_enabled() || (!$available_auto_discounts || $glob_disc) || $allow_global_discounts ) {

                if ( epl_sc_is_enabled() )
                    $this->all_discount_configs = $this->get_global_discount_configs();
                elseif ( $allow_global_discounts )
                    $this->all_discount_configs += $this->get_global_discount_configs();
            }
            $this->get_event_auto_discounts();

            if ( epl_is_empty_array( $this->auto_discounts ) )
                return $this->totals;


            foreach ( $this->auto_discounts as $discount_source_id => $active_discounts ) {

                $this->discount_configs = $this->all_discount_configs[$discount_source_id];

                foreach ( $active_discounts as $_code_id => $discount_source ) {
                    $this->discount_source = $discount_source;
                    $this->discount_source_id = $discount_source_id;
                    //code expired
                    if ( $this->is_code_expired( $_code_id ) ) {
                        continue;
                    }
                    //code use count exceeded
                    if ( $this->is_code_maxed_out( $_code_id, null ) ) {
                        continue;
                    }

                    /* TODO removing as of > 2.0.8
                     * $_pay_profiles = epl_get_element( $_code_id, epl_get_element( '_epl_discount_pay_specific', $this->discount_configs ), 0 );

                      if ( !epl_is_empty_array( $_pay_profiles ) ) {

                      $this->adjust_discountable_amounts( 'pay_profiles', $_pay_profiles );
                      } */

                    $_categories = epl_get_element( $_code_id, epl_get_element( '_epl_discount_cat_include', $this->discount_configs ), 0 );

                    if ( !epl_is_empty_array( $_categories ) ) {

                        $this->adjust_discountable_amounts( 'category', $_categories );
                    }

                    $is_condition_ok = $this->is_condition_ok( $_code_id );
                    if ( $is_condition_ok !== true ) {
                        $this->totals = $this->original_totals;
                        continue;
                    }


                    $amount_to_discount = epl_get_element( $_code_id, epl_get_element( '_epl_discount_amount', $this->discount_configs ) );
                    $discount_description = epl_get_element( $_code_id, epl_get_element( '_epl_discount_description', $this->discount_configs ) );
                    $discount_type = epl_get_element( $_code_id, epl_get_element( '_epl_discount_type', $this->discount_configs ) );

                    $discounted_amount = $this->calculate_discount( $this->totals['money_totals'], $amount_to_discount, $discount_type );

                    if ( $discounted_amount > 0 ) {

                        $this->totals['money_totals']['discount_code_id'] = $_code_id;
                        $this->totals['money_totals']['discount_source_id'] = $discount_source_id;
                        $this->totals['money_totals']['discount_description'] = epl_suffix( ', ', epl_get_element('discount_description', $this->totals['money_totals']) ) . $discount_description;

                        //only apply the first match
                        break 2;
                    }
                }
            }

            //return $this->totals;
        }
        $this->totals = apply_filters( 'epl__discount_model__processed_totals', $this->totals );

        return $this->totals;
    }


    function is_code_maxed_out( $_code_id, $_code = null ) {
        global $event_details;
        $discount_use_count = $this->get_discount_used_count( $_code_id, $_code );

        $_code_max_use = epl_get_element( $_code_id, epl_get_element( '_epl_discount_max_usage', $this->discount_configs ), 9999 );

        return ($discount_use_count >= $_code_max_use );
    }


    function get_discount_used_count( $discount_id, $discount_code = null ) {
        global $wpdb;

        $all_used_from_event = wp_cache_get( 'get_discount_used_count_all_used_from_event' );
        $all_used_global = wp_cache_get( 'get_discount_used_count_all_used_global' );

        if ( $all_used_from_event === false ) {

            $all_used_from_event = $wpdb->get_results( "SELECT re.event_id, count(r.ID) as discount_used_count,r.discount_code_id, r.discount_source_id
                FROM $wpdb->epl_registration as r
                INNER JOIN $wpdb->epl_regis_events re ON r.regis_id = re.regis_id
                WHERE (r.status = 2 OR r.status = 5)                
                AND NOT r.discount_code_id = '' 
                GROUP BY r.discount_code_id, re.event_id", OBJECT_K );

            $all_used_global = $wpdb->get_results( "SELECT CONCAT_WS('_',r.discount_code_id,r.discount_source_id) as discount_code_id, count(r.ID) as discount_used_count, r.discount_source_id
                FROM $wpdb->epl_registration as r
                INNER JOIN $wpdb->epl_regis_events re ON r.regis_id = re.regis_id
                WHERE (r.status = 2 OR r.status = 5)                
                AND NOT r.discount_code_id = '' 
                GROUP BY r.discount_code_id, r.discount_source_id", OBJECT_K );

            wp_cache_add( 'get_discount_used_count_all_used_from_event', $all_used_from_event );
            wp_cache_add( 'get_discount_used_count_all_used_global', $all_used_global );
        }


        $r = 0;

        if ( $all_used_from_event || $all_used_global ) {
            $all_used_from_event = epl_object_to_array( $all_used_from_event );

            if ( $this->discount_source == 'e' ) {

                if ( isset( $all_used_from_event[$this->discount_source_id] ) && $all_used_from_event[$this->discount_source_id]['discount_code_id'] == $discount_id )
                    $r = epl_get_element_m( 'discount_used_count', $this->discount_source_id, $all_used_from_event, 0 );
            }
            else {
                //Using combination $discount_id . '_' . $this->discount_source_id for key to 
                //take care of instances when users duplicate discounts with duplicate post plugin
                $all_used_global = epl_object_to_array( $all_used_global );
                $r = epl_get_element_m( 'discount_used_count', $discount_id . '_' . $this->discount_source_id, $all_used_global, 0 );
            }
        }


        return $r;
    }


    function discount_code_entered( $totals ) {
        global $epl_current_step;

        $_discount_steps = array( 'process_cart_action', 'regis_form' );

        if ( (EPL_IS_ADMIN || in_array( $epl_current_step, $_discount_steps )) && $_POST ) {
            return $this->epl_util->clean_input( current( epl_get_element( '_epl_discount_code', $_REQUEST, array() ) ) );
        }
        elseif ( EPL_IS_ADMIN && !$_POST ) {
            global $regis_details;
            return epl_get_element( '_epl_discount_code', $regis_details );
        }
        return epl_get_element_m( 'discount_code', 'money_totals', $totals, '' );
    }


    function get_event_discount_codes() {
        global $event_details;


        foreach ( $this->all_discount_configs as $disc_source_id => $discount_data ) {
            $_epl_discount_amount = epl_get_element( '_epl_discount_amount', $discount_data, null );
            $discount_type = epl_get_element( '_epl_global_discount_type', $discount_data, 'global' );
            if ( epl_is_empty_array( $_epl_discount_amount ) )
                continue;

            foreach ( $_epl_discount_amount as $disc_id => $amount ) {

                if ( $discount_type == 'global' && epl_get_element_m( $disc_id, '_epl_discount_method', $discount_data, 'global' ) != 5 ) //if not auto
                    continue;
                if ( epl_get_element( $disc_id, epl_get_element( '_epl_discount_active', $discount_data ) ) == 0 ) //if not active
                    continue;

                if ( !isset( $this->code_discounts[$disc_source_id] ) )
                    $this->code_discounts[$disc_source_id] = array();

                $this->code_discounts_source[$disc_source_id] = isset( $discount_data['_epl_start_date'] ) ? 'e' : 'g';
                $this->code_discounts[$disc_source_id][$disc_id] = strtolower( $discount_data['_epl_discount_code'][$disc_id] );
            }
        }
    }


    function get_global_discount_configs() {
        global $wpdb, $event_details;

        if ( ($gd = wp_cache_get( 'get_global_discount_configs' )) !== false )
            return $gd;

        $q = $wpdb->get_results( "SELECT id FROM $wpdb->posts WHERE post_type = 'epl_global_discount' AND post_status ='publish'" );

        $gd = array();

        if ( $wpdb->num_rows > 0 ) {

            foreach ( $q as $d ) {
                //this one combines all of the discounts together but we lose the discount post id
                //$gd = array_merge_recursive( $gd, $this->ecm->get_post_meta_all( $d->id ) );
                $gd[$d->id] = $this->ecm->get_post_meta_all( $d->id );
            }
        }
        wp_cache_add( 'get_global_discount_configs', $gd );
        //echo "<pre class='prettyprint'>" . __LINE__ . "> " . print_r( $gd, true ) . "</pre>";
        return $gd;
    }


    function is_condition_ok( $_code_id ) {

        global $event_details, $event_totals;

        $_code_condition = epl_get_element( $_code_id, epl_get_element( '_epl_discount_condition', $this->discount_configs ), 0 );

        if ( $_code_condition == 0 )
            return true;

        $_condition_logic = epl_get_element( $_code_id, epl_get_element( '_epl_discount_condition_logic', $this->discount_configs ) );
        $_condition_value = epl_get_element( $_code_id, epl_get_element( '_epl_discount_condition_value', $this->discount_configs ), 0 );
        $_condition_value2 = epl_get_element( $_code_id, epl_get_element( '_epl_discount_condition_value2', $this->discount_configs ), 0 );

        $r = null;

        switch ( $_code_condition )
        {
            case 5:

                $r = $this->apply_condition( $this->totals['money_totals']['grand_total'], $_condition_logic, $_condition_value, $_condition_value2 );

                if ( !$r )
                    return epl__( 'The discount code requires the total amount to be' ) . ' ' . $_condition_logic . ' ' . epl_get_formatted_curr( $_condition_value ) . ($_condition_logic == 'between' ? ' ' . epl__( 'and' ) . ' ' . epl_get_formatted_curr( $_condition_value2 ) : '');

                break;
            case 6:
                $disc_qty = current( ( array ) $this->totals['_att_quantity']['total'] ) - current( ( array ) $this->totals['_att_quantity']['total_non_disc'] );
                $r = $this->apply_condition( $disc_qty, $_condition_logic, $_condition_value, $_condition_value2 );

                if ( !$r )
                    return epl__( 'The discount code requires for total number of paying attendees to be' ) . ' ' . $_condition_logic . ' ' . $_condition_value . ($_condition_logic == 'between' ? ' ' . epl__( 'and' ) . ' ' . epl_get_formatted_curr( $_condition_value2 ) : '');

                break;
            case 7:

                $r = $this->apply_condition( $this->totals['money_totals']['num_discountable_events_in_cart'], $_condition_logic, $_condition_value, $_condition_value2 );

                if ( !$r )
                    return epl__( 'The discount code requires for total number of eligible events in the cart to be' ) . ' ' . $_condition_logic . ' ' . $_condition_value . ($_condition_logic == 'between' ? ' ' . epl__( 'and' ) . ' ' . epl_get_formatted_curr( $_condition_value2 ) : '');
                break;
                break;
            case 8:

                $dates = EPL_registration_model::get_instance()->get_the_cart_dates( $event_details['ID'] );

                if ( !empty( $dates ) ) {
                    $c = count( epl_get_element( $event_details['ID'], $dates ) );
                    $r = $this->apply_condition( $c, $_condition_logic, $_condition_value, $_condition_value2 );
                }
                if ( !$r )
                    return epl__( 'The discount code requires for total number of eligible dates in the cart to be' ) . ' ' . $_condition_logic . ' ' . $_condition_value . ($_condition_logic == 'between' ? ' ' . epl__( 'and' ) . ' ' . epl_get_formatted_curr( $_condition_value2 ) : '');
                break;
        }

        return true;
    }


    function is_category_ok( $_code_id ) {

        global $event_details, $event_totals;

        $_categories = epl_get_element( $_code_id, epl_get_element( '_epl_discount_cat_include', $this->discount_configs ), 0 );

        if ( !epl_is_empty_array( $_categories ) ) {

//$this->adjust_discountable_amounts( 'category', $_categories );
        }


        return true;
    }


    function adjust_discountable_amounts( $for = 'category', $args = null ) {
        global $event_totals;
        static $adjusted = false;
        if ( epl_is_empty_array( $event_totals ) )
            return false;

        $num_events = count( $event_totals );

        $pay_profile_ok = 1;

        if ( $for == 'pay_profiles' ) {
            $selected_pay_id = EPL_registration_model::get_instance()->get_payment_profile_id();

            $pay_profile_ok = ( $selected_pay_id && in_array( $selected_pay_id, $args ) );
        }

        foreach ( $event_totals as $event_id => $totals ) {

            setup_event_details( $event_id );
            //if the taxonomy in question is not assigned to this event
            //then adjust the totals to exclude this event numbers from the discount processes
            //same applies to pay profiles
            if ( ($for == 'category' && !has_term( $args, 'epl_event_categories', $event_id ) || !$pay_profile_ok ) ) {

                $this->totals['money_totals']['discountable_total'] -= epl_get_element_m('discountable_total','money_totals',$totals,0);
                $this->totals['money_totals']['non_discountable_total'] += epl_get_element_m('grand_total','money_totals',$totals,0);
                $this->totals['money_totals']['num_discountable_events_in_cart'] -= 1;
            }
        }

        $adjusted = true;

//return $totals;
    }


    function apply_condition( $value, $logic, $_condition_value, $_condition_value2 ) {

        switch ( html_entity_decode( $logic ) )
        {
            case 'between':
                return ($value >= $_condition_value && $value <= $_condition_value2);
                break;
            case '=':
                return ($value == $_condition_value);
                break;
            case '>':
                return ($value > $_condition_value);
                break;
            case '<':
                return ($value < $_condition_value);
                break;
            case '>=':
                return ($value >= $_condition_value);
                break;
            case '<=':
                return ($value <= $_condition_value);
                break;
        }
    }


    function get_event_auto_discounts( $global = false ) {
        global $event_details;


        foreach ( $this->all_discount_configs as $disc_source_id => $discount_data ) {
            $_epl_discount_method = epl_get_element( '_epl_discount_method', $discount_data, null );

            if ( epl_is_empty_array( $_epl_discount_method ) )
                continue;

            foreach ( $_epl_discount_method as $disc_id => $method ) {
                if ( $method != 10 ) //if not auto
                    continue;
                if ( epl_get_element( $disc_id, epl_get_element( '_epl_discount_active', $discount_data ) ) == 0 ) //if not active
                    continue;

                if ( !isset( $this->auto_discounts[$disc_source_id] ) ) {
                    $this->auto_discounts[$disc_source_id] = array();
                }
                $this->auto_discount_source[$disc_source_id] = isset( $discount_data['_epl_start_date'] ) ? 'e' : 'g';
                $this->auto_discounts[$disc_source_id][$disc_id] = isset( $discount_data['_epl_start_date'] ) ? 'e' : 'g';
            }
        }
    }


    function calculate_discount( $totals, $discount_amount, $discount_type ) {
        global $event_details;


        $t = 0;
        $discountable = epl_get_element( 'discountable_total', $this->totals['money_totals'] );
        $non_discountable = epl_get_element( 'non_discountable_total', $this->totals['money_totals'], 0 );
        if ( $discountable == 0 )
            return $non_discountable;

        if ( $surcharge_amount = epl_get_element( '_epl_surcharge_amount', $this->discount_configs ) > 0 ) {

            $surcharge_before_discount = epl_get_element( '_epl_surcharge_before_discount', $this->discount_configs );

            if ( $surcharge_before_discount == 10 ) {
                //$discountable = $this->totals['money_totals']['subtotal'];
            }
        }

        if ( $discount_type == 5 ) { //fixed
            $this->discount_amount = $discount_amount;
            $t = $discountable - $discount_amount;
        }
        elseif ( $discount_type == 10 ) { //percent
            $this->discount_amount = round( $discountable * $discount_amount / 100, 2 );

            $t = $discountable - $this->discount_amount;
        }

        $discounted_amount = round( $t + $non_discountable, 2 );
        $this->totals['money_totals']['pre_discount_total'] = $this->totals['money_totals']['grand_total'];
        $this->totals['money_totals']['discount_amount'] += round( $this->discount_amount, 2 );
        $this->totals['money_totals']['grand_total'] = $discounted_amount;
        $this->totals['money_totals']['grand_total'] += $this->totals['money_totals']['non_discount_grand_total'];

        return $discounted_amount;
    }


    function return_value( $v ) {
        return $v;
    }


    function is_code_expired( $_code ) {
        global $event_details;


        $_code_expiration_date = epl_get_element( $_code, epl_get_element( '_epl_discount_end_date', $this->discount_configs ), null );

        if ( !$_code_expiration_date )
            return false;


        $_code_expiration_date = epl_formatted_date( $_code_expiration_date, 'Y-m-d', 'date' );

        $_expired = epl_compare_dates( EPL_TIME, $_code_expiration_date . ' 23:59:59', ">" );

        if ( $_expired )
            return true;

        return false;
    }

}
