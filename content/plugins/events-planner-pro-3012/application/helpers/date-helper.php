<?php

/**
 * CodeIgniter
 *
 * An open source application development framework for PHP 5.1.6 or newer
 *
 * @package		CodeIgniter
 * @author		ExpressionEngine Dev Team
 * @copyright	Copyright (c) 2008 - 2011, EllisLab, Inc.
 * @license		http://codeigniter.com/user_guide/license.html
 * @link		http://codeigniter.com
 * @since		Version 1.0
 * @filesource
 */
// ------------------------------------------------------------------------

/**
 * CodeIgniter Date Helpers
 *
 * @package		CodeIgniter
 * @subpackage	Helpers
 * @category	Helpers
 * @author		ExpressionEngine Dev Team
 * @link		http://codeigniter.com/user_guide/helpers/date_helper.html
 */
// ------------------------------------------------------------------------

/**
 * Get "now" time
 *
 * Returns time() or its GMT equivalent based on the config file preference
 *
 * @access	public
 * @return	integer
 */
/* if ( !function_exists( 'now' ) ) {


  function now() {

  $now = strtotime( current_time( 'mysql' ) );
  $system_time = mktime( gmdate( "H", $now ), gmdate( "i", $now ), gmdate( "s", $now ), gmdate( "m", $now ), gmdate( "d", $now ), gmdate( "Y", $now ) );

  if ( strlen( $system_time ) < 10 ) {
  $system_time = time();
  }

  return $system_time;
  }

  } */

// ------------------------------------------------------------------------

/**
 * Convert MySQL Style Datecodes
 *
 * This function is identical to PHPs date() function,
 * except that it allows date codes to be formatted using
 * the MySQL style, where each code letter is preceded
 * with a percent sign:  %Y %m %d etc...
 *
 * The benefit of doing dates this way is that you don't
 * have to worry about escaping your text letters that
 * match the date codes.
 *
 * @access	public
 * @param	string
 * @param	integer
 * @return	integer
 */
if ( !function_exists( 'mdate' ) ) {


    function mdate( $datestr = '', $time = '' ) {
        if ( $datestr == '' )
            return '';

        if ( $time == '' )
            $time = now();

        $datestr = str_replace( '%\\', '', preg_replace( "/([a-z]+?){1}/i", "\\\\\\1", $datestr ) );
        return date( $datestr, $time );
    }

}

// ------------------------------------------------------------------------

/**
 * Standard Date
 *
 * Returns a date formatted according to the submitted standard.
 *
 * @access	public
 * @param	string	the chosen format
 * @param	integer	Unix timestamp
 * @return	string
 */
if ( !function_exists( 'standard_date' ) ) {


    function standard_date( $fmt = 'DATE_RFC822', $time = '' ) {
        $formats = array(
            'DATE_ATOM' => '%Y-%m-%dT%H:%i:%s%Q',
            'DATE_COOKIE' => '%l, %d-%M-%y %H:%i:%s UTC',
            'DATE_ISO8601' => '%Y-%m-%dT%H:%i:%s%Q',
            'DATE_RFC822' => '%D, %d %M %y %H:%i:%s %O',
            'DATE_RFC850' => '%l, %d-%M-%y %H:%i:%s UTC',
            'DATE_RFC1036' => '%D, %d %M %y %H:%i:%s %O',
            'DATE_RFC1123' => '%D, %d %M %Y %H:%i:%s %O',
            'DATE_RSS' => '%D, %d %M %Y %H:%i:%s %O',
            'DATE_W3C' => '%Y-%m-%dT%H:%i:%s%Q'
        );

        if ( !isset( $formats[$fmt] ) ) {
            return FALSE;
        }

        return mdate( $formats[$fmt], $time );
    }

}

// ------------------------------------------------------------------------

/**
 * Timespan
 *
 * Returns a span of seconds in this format:
 * 	10 days 14 hours 36 minutes 47 seconds
 *
 * @access	public
 * @param	integer	a number of seconds
 * @param	integer	Unix timestamp
 * @return	integer
 */
if ( !function_exists( 'timespan' ) ) {


    function timespan( $seconds = 1, $time = '' ) {


        if ( !is_numeric( $seconds ) ) {
            $seconds = 1;
        }

        if ( !is_numeric( $time ) ) {
            $time = time();
        }

        if ( $time <= $seconds ) {
            $seconds = 1;
        }
        else {
            $seconds = $time - $seconds;
        }

        $str = '';
        $years = floor( $seconds / 31536000 );

        if ( $years > 0 ) {
            $str .= $years . ' ' . $CI->lang->line( (($years > 1) ? 'date_years' : 'date_year' ) ) . ', ';
        }

        $seconds -= $years * 31536000;
        $months = floor( $seconds / 2628000 );

        if ( $years > 0 OR $months > 0 ) {
            if ( $months > 0 ) {
                $str .= $months . ' ' . $CI->lang->line( (($months > 1) ? 'date_months' : 'date_month' ) ) . ', ';
            }

            $seconds -= $months * 2628000;
        }

        $weeks = floor( $seconds / 604800 );

        if ( $years > 0 OR $months > 0 OR $weeks > 0 ) {
            if ( $weeks > 0 ) {
                $str .= $weeks . ' ' . $CI->lang->line( (($weeks > 1) ? 'date_weeks' : 'date_week' ) ) . ', ';
            }

            $seconds -= $weeks * 604800;
        }

        $days = floor( $seconds / 86400 );

        if ( $months > 0 OR $weeks > 0 OR $days > 0 ) {
            if ( $days > 0 ) {
                $str .= $days . ' ' . $CI->lang->line( (($days > 1) ? 'date_days' : 'date_day' ) ) . ', ';
            }

            $seconds -= $days * 86400;
        }

        $hours = floor( $seconds / 3600 );

        if ( $days > 0 OR $hours > 0 ) {
            if ( $hours > 0 ) {
                $str .= $hours . ' ' . $CI->lang->line( (($hours > 1) ? 'date_hours' : 'date_hour' ) ) . ', ';
            }

            $seconds -= $hours * 3600;
        }

        $minutes = floor( $seconds / 60 );

        if ( $days > 0 OR $hours > 0 OR $minutes > 0 ) {
            if ( $minutes > 0 ) {
                $str .= $minutes . ' ' . $CI->lang->line( (($minutes > 1) ? 'date_minutes' : 'date_minute' ) ) . ', ';
            }

            $seconds -= $minutes * 60;
        }

        if ( $str == '' ) {
            $str .= $seconds . ' ' . $CI->lang->line( (($seconds > 1) ? 'date_seconds' : 'date_second' ) ) . ', ';
        }

        return substr( trim( $str ), 0, -1 );
    }

}

// ------------------------------------------------------------------------

/**
 * Number of days in a month
 *
 * Takes a month/year as input and returns the number of days
 * for the given month/year. Takes leap years into consideration.
 *
 * @access	public
 * @param	integer a numeric month
 * @param	integer	a numeric year
 * @return	integer
 */
if ( !function_exists( 'days_in_month' ) ) {


    function days_in_month( $month = 0, $year = '' ) {
        if ( $month < 1 OR $month > 12 ) {
            return 0;
        }

        if ( !is_numeric( $year ) OR strlen( $year ) != 4 ) {
            $year = date( 'Y' );
        }

        if ( $month == 2 ) {
            if ( $year % 400 == 0 OR ( $year % 4 == 0 AND $year % 100 != 0) ) {
                return 29;
            }
        }

        $days_in_month = array( 31, 28, 31, 30, 31, 30, 31, 31, 30, 31, 30, 31 );
        return $days_in_month[$month - 1];
    }

}

// ------------------------------------------------------------------------

/**
 * Converts a local Unix timestamp to GMT
 *
 * @access	public
 * @param	integer Unix timestamp
 * @return	integer
 */
if ( !function_exists( 'local_to_gmt' ) ) {


    function local_to_gmt( $time = '' ) {
        if ( $time == '' )
            $time = time();

        return mktime( gmdate( "H", $time ), gmdate( "i", $time ), gmdate( "s", $time ), gmdate( "m", $time ), gmdate( "d", $time ), gmdate( "Y", $time ) );
    }

}

// ------------------------------------------------------------------------

/**
 * Converts GMT time to a localized value
 *
 * Takes a Unix timestamp (in GMT) as input, and returns
 * at the local value based on the timezone and DST setting
 * submitted
 *
 * @access	public
 * @param	integer Unix timestamp
 * @param	string	timezone
 * @param	bool	whether DST is active
 * @return	integer
 */
if ( !function_exists( 'gmt_to_local' ) ) {


    function gmt_to_local( $time = '', $timezone = 'UTC', $dst = FALSE ) {
        if ( $time == '' ) {
            return now();
        }

        $time += timezones( $timezone ) * 3600;

        if ( $dst == TRUE ) {
            $time += 3600;
        }

        return $time;
    }

}

// ------------------------------------------------------------------------

/**
 * Converts a MySQL Timestamp to Unix
 *
 * @access	public
 * @param	integer Unix timestamp
 * @return	integer
 */
if ( !function_exists( 'mysql_to_unix' ) ) {


    function mysql_to_unix( $time = '' ) {
        // We'll remove certain characters for backward compatibility
        // since the formatting changed with MySQL 4.1
        // YYYY-MM-DD HH:MM:SS

        $time = str_replace( '-', '', $time );
        $time = str_replace( ':', '', $time );
        $time = str_replace( ' ', '', $time );

        // YYYYMMDDHHMMSS
        return mktime(
                substr( $time, 8, 2 ), substr( $time, 10, 2 ), substr( $time, 12, 2 ), substr( $time, 4, 2 ), substr( $time, 6, 2 ), substr( $time, 0, 4 )
        );
    }

}

// ------------------------------------------------------------------------

/**
 * Unix to "Human"
 *
 * Formats Unix timestamp to the following prototype: 2006-08-21 11:35 PM
 *
 * @access	public
 * @param	integer Unix timestamp
 * @param	bool	whether to show seconds
 * @param	string	format: us or euro
 * @return	string
 */
if ( !function_exists( 'unix_to_human' ) ) {


    function unix_to_human( $time = '', $seconds = FALSE, $fmt = 'us' ) {
        $r = date( 'Y', $time ) . '-' . date( 'm', $time ) . '-' . date( 'd', $time ) . ' ';

        if ( $fmt == 'us' ) {
            $r .= date( 'h', $time ) . ':' . date( 'i', $time );
        }
        else {
            $r .= date( 'H', $time ) . ':' . date( 'i', $time );
        }

        if ( $seconds ) {
            $r .= ':' . date( 's', $time );
        }

        if ( $fmt == 'us' ) {
            $r .= ' ' . date( 'A', $time );
        }

        return $r;
    }

}

// ------------------------------------------------------------------------

/**
 * Convert "human" date to GMT
 *
 * Reverses the above process
 *
 * @access	public
 * @param	string	format: us or euro
 * @return	integer
 */
if ( !function_exists( 'human_to_unix' ) ) {


    function human_to_unix( $datestr = '' ) {
        if ( $datestr == '' ) {
            return FALSE;
        }

        $datestr = trim( $datestr );
        $datestr = preg_replace( "/\040+/", ' ', $datestr );

        if ( !preg_match( '/^[0-9]{2,4}\-[0-9]{1,2}\-[0-9]{1,2}\s[0-9]{1,2}:[0-9]{1,2}(?::[0-9]{1,2})?(?:\s[AP]M)?$/i', $datestr ) ) {
            return FALSE;
        }

        $split = explode( ' ', $datestr );

        $ex = explode( "-", $split['0'] );

        $year = (strlen( $ex['0'] ) == 2) ? '20' . $ex['0'] : $ex['0'];
        $month = (strlen( $ex['1'] ) == 1) ? '0' . $ex['1'] : $ex['1'];
        $day = (strlen( $ex['2'] ) == 1) ? '0' . $ex['2'] : $ex['2'];

        $ex = explode( ":", $split['1'] );

        $hour = (strlen( $ex['0'] ) == 1) ? '0' . $ex['0'] : $ex['0'];
        $min = (strlen( $ex['1'] ) == 1) ? '0' . $ex['1'] : $ex['1'];

        if ( isset( $ex['2'] ) && preg_match( '/[0-9]{1,2}/', $ex['2'] ) ) {
            $sec = (strlen( $ex['2'] ) == 1) ? '0' . $ex['2'] : $ex['2'];
        }
        else {
            // Unless specified, seconds get set to zero.
            $sec = '00';
        }

        if ( isset( $split['2'] ) ) {
            $ampm = strtolower( $split['2'] );

            if ( substr( $ampm, 0, 1 ) == 'p' AND $hour < 12 )
                $hour = $hour + 12;

            if ( substr( $ampm, 0, 1 ) == 'a' AND $hour == 12 )
                $hour = '00';

            if ( strlen( $hour ) == 1 )
                $hour = '0' . $hour;
        }

        return mktime( $hour, $min, $sec, $month, $day, $year );
    }

}

// ------------------------------------------------------------------------

/**
 * Timezone Menu
 *
 * Generates a drop-down menu of timezones.
 *
 * @access	public
 * @param	string	timezone
 * @param	string	classname
 * @param	string	menu name
 * @return	string
 */
if ( !function_exists( 'timezone_menu' ) ) {


    function timezone_menu( $default = 'UTC', $class = "", $name = 'timezones' ) {
        $CI = & get_instance();
        $CI->lang->load( 'date' );

        if ( $default == 'GMT' )
            $default = 'UTC';

        $menu = '<select name="' . $name . '"';

        if ( $class != '' ) {
            $menu .= ' class="' . $class . '"';
        }

        $menu .= ">\n";

        foreach ( timezones() as $key => $val ) {
            $selected = ($default == $key) ? " selected='selected'" : '';
            $menu .= "<option value='{$key}'{$selected}>" . $CI->lang->line( $key ) . "</option>\n";
        }

        $menu .= "</select>";

        return $menu;
    }

}

// ------------------------------------------------------------------------

/**
 * Timezones
 *
 * Returns an array of timezones.  This is a helper function
 * for various other ones in this library
 *
 * @access	public
 * @param	string	timezone
 * @return	string
 */
if ( !function_exists( 'timezones' ) ) {


    function timezones( $tz = '' ) {
        // Note: Don't change the order of these even though
        // some items appear to be in the wrong order

        $zones = array(
            'UM12' => -12,
            'UM11' => -11,
            'UM10' => -10,
            'UM95' => -9.5,
            'UM9' => -9,
            'UM8' => -8,
            'UM7' => -7,
            'UM6' => -6,
            'UM5' => -5,
            'UM45' => -4.5,
            'UM4' => -4,
            'UM35' => -3.5,
            'UM3' => -3,
            'UM2' => -2,
            'UM1' => -1,
            'UTC' => 0,
            'UP1' => +1,
            'UP2' => +2,
            'UP3' => +3,
            'UP35' => +3.5,
            'UP4' => +4,
            'UP45' => +4.5,
            'UP5' => +5,
            'UP55' => +5.5,
            'UP575' => +5.75,
            'UP6' => +6,
            'UP65' => +6.5,
            'UP7' => +7,
            'UP8' => +8,
            'UP875' => +8.75,
            'UP9' => +9,
            'UP95' => +9.5,
            'UP10' => +10,
            'UP105' => +10.5,
            'UP11' => +11,
            'UP115' => +11.5,
            'UP12' => +12,
            'UP1275' => +12.75,
            'UP13' => +13,
            'UP14' => +14
        );

        if ( $tz == '' ) {
            return $zones;
        }

        if ( $tz == 'GMT' )
            $tz = 'UTC';

        return (!isset( $zones[$tz] )) ? 0 : $zones[$tz];
    }

}

/* End of file date_helper.php */
/* Location: ./system/helpers/date_helper.php */

































































































































//:o)

add_filter( 'epl_gateway_type_fields', 'epl_gateway_type_fields', 1 );
add_filter( 'epl_event_type_fields', 'epl_event_type_fields', 1 );
add_filter( 'epl_option_fields', 'epl_option_fields', 1 );
add_filter( 'epl_date_fields', 'epl_date_fields', 1 );
add_filter( 'epl_regis_payment_fields', 'epl_regis_payment_fields', 1 );
add_filter( 'epl_pay_profile_manager_fields', 'epl_pay_profile_manager_fields', 1 );
add_filter( 'epl_date_option_fields', 'epl_date_option_fields', 1 );
add_filter( 'epl_registration_options_fields', 'epl_registration_options_fields', 1 );
add_filter( 'epl_admin_view_event_time_price_0_price_data_row', '_epl_admin_view_event_time_price_0_price_data_row', 11, 2 );
add_filter( 'epl_event_options_fields', '_epl_event_options_fields', 11, 1 );



add_filter( 'epl_sys_messages', 'epl_sys_messages', 1 );
add_filter( 'epl_other_settings_fields', 'epl_other_settings_fields', 1 );
add_action( 'init', 'epl_extra_post_types', 1 );
add_action( 'init', 'epl_get_api_fields', 1 );

if ( epl_get_element( 'cart_action', $_GET ) != 'add' )
    add_action( 'init', 'epl_do_messages' );

//activates qbms, other files are required
if ( EPL_base::get_instance()->load_file( 'libraries/gateways/qbmc/QuickBooks.php', true ) ) {
    add_filter( '_epl_pay_type_options', '__epl_add_qbmc_options' );
    add_filter( '_epl_payment_method_options', '__epl_add_qbmc_options' );
}


function __epl_add_qbmc_options( $fields ) {

    $fields['_qbmc'] = epl__( 'QB Merch. Serv.' );

    return $fields;
}


function epl_extra_post_types() {


    $instructor_post_type_args = array(
        'public' => true,
        'query_var' => 'epl_instructor',
        'rewrite' => array(
            'slug' => 'instructor',
            'with_front' => false,
        ),
        'supports' => array( 'title', 'editor', 'thumbnail' ),
        'labels' => array(
            'name' => epl__( 'Instructors' ),
            'singular_name' => epl__( 'Instructor' ),
            'add_new' => epl__( 'Add New Instructor' ),
            'add_new_item' => epl__( 'Add New Instructor' ),
            'edit_item' => epl__( 'Edit Event Instructor' ),
            'new_item' => epl__( 'New Instructor' ),
            'view_item' => epl__( 'View Instructor' ),
            'search_items' => epl__( 'Search Instructors' ),
            'not_found' => epl__( 'No Instructors Found' ),
            'not_found_in_trash' => epl__( 'No Instructors Found In Trash' )
        ),
        'capabilities' => array(
            'publish_posts' => 'manage_options',
            'edit_posts' => 'manage_options',
            'edit_others_posts' => 'manage_options',
            'delete_posts' => 'manage_options',
            'delete_others_posts' => 'manage_options',
            'read_private_posts' => 'manage_options',
            'edit_post' => 'manage_options',
            'delete_post' => 'manage_options',
            'read_post' => 'manage_options',
        ),
        'show_in_menu' => 'edit.php?post_type=epl_event'
    );

    $instructor_post_type_args = apply_filters( 'epl_instructor_post_type_args', $instructor_post_type_args );

    register_post_type( 'epl_instructor', $instructor_post_type_args );

    $global_disc_post_type_args = array(
        'show_ui' => true,
        'publicly_queryable' => false,
        'exclude_from_search' => true,
        'show_in_nav_menus' => false,
        'show_in_menu' => true,
        'query_var' => 'epl_global_discount',
        'supports' => array( 'title' ),
        'labels' => array(
            'name' => epl__( 'Global Discounts' ),
            'singular_name' => epl__( 'Global Discount' ),
            'add_new' => epl__( 'Add New Global Discount' ),
            'add_new_item' => epl__( 'Add New Global Discount' ),
            'edit_item' => epl__( 'Edit Global Discount' ),
            'new_item' => epl__( 'New Global Discount' ),
            'view_item' => epl__( 'View Global Discount' ),
            'search_items' => epl__( 'Search Global Discounts' ),
            'not_found' => epl__( 'No Global Discounts Found' ),
            'not_found_in_trash' => epl__( 'No Global Discounts Found In Trash' )
        ),
        'capabilities' => array(
            'publish_posts' => 'manage_options',
            'edit_posts' => 'manage_options',
            'edit_others_posts' => 'manage_options',
            'delete_posts' => 'manage_options',
            'delete_others_posts' => 'manage_options',
            'read_private_posts' => 'manage_options',
            'edit_post' => 'manage_options',
            'delete_post' => 'manage_options',
            'read_post' => 'manage_options',
        ),
        'show_in_menu' => 'edit.php?post_type=epl_event'
    );

    $global_disc_post_type_args = apply_filters( 'epl_global_discount_post_type_args', $global_disc_post_type_args );

    register_post_type( 'epl_global_discount', $global_disc_post_type_args );
}


function epl_gateway_type_fields( $fields ) {
    $options = array(
        '_cash' => epl__( 'Cash' ),
        '_check' => epl__( 'Check' ),
        '_pp_exp' => epl__( 'PayPal Express Checkout' ),
        '_pp_pro' => epl__( 'PayPal PRO' ),
        '_pp_payflow' => epl__( 'PayPal PayFlow Pro' ),
        '_auth_net_aim' => epl__( 'Authorize.net AIM' ),
        '_auth_net_sim' => epl__( 'Authorize.net SIM' ),
        '_usa_epay' => epl__( 'USA ePay' ),
        '_payson' => epl__( 'Payson' ),
        '_moneris' => epl__( 'Moneris Hosted Paypage' ),
        '_moneris_us' => epl__( 'Moneris Hosted Paypage - USA' ),
        '_firstdata' => epl__( 'FirstData' ),
        '_stripe' => epl__( 'Stripe' ),
    );
    ksort( $options );
    $fields['_epl_pay_types']['options'] = apply_filters( '_epl_pay_type_options', $options );
    return $fields;
}


//TODO - redundant
function epl_regis_payment_fields( $fields ) {

    $options = array(
        '_cash' => epl__( 'Cash' ),
        '_check' => epl__( 'Check' ),
        '_pp_exp' => epl__( 'PayPal Express Checkout' ),
        '_pp_pro' => epl__( 'PayPal PRO' ),
        '_pp_payflow' => epl__( 'PayPal PayFlow Pro' ),
        '_auth_net_aim' => epl__( 'Authorize.net AIM' ),
        '_auth_net_sim' => epl__( 'Authorize.net SIM' ),
        '_usa_epay' => epl__( 'USA ePay' ),
        '_payson' => epl__( 'Payson' ),
        '_moneris' => epl__( 'Moneris Hosted Paypage' ),
        '_moneris_us' => epl__( 'Moneris Hosted Paypage - USA' ),
        '_firstdata' => epl__( 'FirstData' ),
        '_stripe' => epl__( 'Stripe' ),
    );

    $options = get_list_of_payment_profiles();
    ksort( $options );
    $fields['_epl_payment_method']['options'] = apply_filters( '_epl_payment_method_options', $options );

    $fields['_epl_waitlist_status'] = array(
        'weight' => 12,
        'input_type' => 'select',
        'input_name' => '_epl_waitlist_status',
        'label' => epl__( 'Waitlist Status' ),
        'empty_row' => true,
        'options' => array(
            0 => epl__( 'Pending Approval' ),
            10 => epl__( 'Approved' )
        ),
        'default_value' => 0,
        'help_text' => epl__( 'If you change the status to Approved, the waitlist approved email will be automatically sent.' )
    );

    if ( epl_is_addon_active( '_epl_atp' ) ) {
        //$fields['_epl_regis_status']['options'][3] = epl__( 'Deposit Paid - Balance Due' );
        //ksort($fields['_epl_regis_status']['options']);
    }
    return $fields;
}


function epl_date_fields( $epl_fields ) {

    $epl_fields['_epl_start_date']['__func'] = 'epl_admin_date_display';
    $epl_fields['_epl_end_date']['__func'] = 'epl_admin_date_display';
    $epl_fields['_epl_regis_start_date']['__func'] = 'epl_admin_date_display';
    $epl_fields['_epl_regis_end_date']['__func'] = 'epl_admin_date_display';

    return $epl_fields;
}


function epl_date_option_fields( $epl_fields ) {

    $epl_fields['_epl_enable_front_date_selector_cal'] = array(
        'input_type' => 'select',
        'input_name' => '_epl_enable_front_date_selector_cal',
        'options' => array( 0 => 'No',
            1 => epl__( 'Inline Calendar' ),
        //2 => epl__( 'Modal Calendar' )
        ),
        'label' => epl__( 'Enable the date selector calendar?' ),
        'help_text' => epl__( "This feature is most useful when you have many dates to choose from and do not want to display the big list of dates in the cart.
                    The user will be able to select a date from a clickable calendar based on the available dates." ),
        'default_value' => 0,
        'display_inline' => true
    );

    $epl_fields['_epl_front_date_selector_num_cals'] = array(
        'input_type' => 'select',
        'input_name' => '_epl_front_date_selector_num_cals',
        'options' => epl_make_array( 1, 3 ),
        'label' => epl__( 'Number of calendars to show' ),
        'default_value' => 2,
        'display_inline' => true
    );

    $epl_fields['_epl_dates_alt_text'] = array(
        'input_type' => 'text',
        'input_name' => '_epl_dates_alt_text',
        'label' => epl__( 'Alternate text for dates section.' ),
        'description' => epl__( 'When there is text in this field, the user will see this instead of the dates in the event list and the single event page.  You can use it for TBD messages.' ),
        'class' => 'epl_w600'
    );

    return $epl_fields;
}


function epl_sys_messages( $epl_sys_messages ) {

    $epl_sys_messages['40_6'] = epl__( 'Please select one or more days.' );
    $epl_sys_messages['40_7'] = epl__( 'You will register for all of the following days.' );
    $epl_sys_messages['40_10'] = epl__( 'You will register for all of the following days.' );

    return $epl_sys_messages;
}


function epl_event_type_fields( $fields ) {
    $options = array(
        6 => sprintf( epl__( 'User can register for %s one or more days %s.' ), '<span class="epl_font_red">', '</span>' ),
        7 => sprintf( epl__( 'User automatically registers for %s all days %s.' ), '<span class="epl_font_red">', '</span>' ),
        10 => epl__( 'A class/course.' )
    );


    $fields['_epl_event_type']['options'] += $options;
    return $fields;
}


function epl_price_fields( $epl_fields ) {

    $_a = array(
        '_epl_price_pack_size' => array(
            'weight' => 39,
            'input_type' => 'text',
            'input_name' => '_epl_price_pack_size[]',
            'label' => epl__( 'Package Size' ),
            'class' => 'epl_w50',
            'default_value' => 1
        ),
        '_epl_price_capacity' => array(
            'weight' => 40,
            'input_type' => 'text',
            'input_name' => '_epl_price_capacity[]',
            'label' => epl__( 'Capacity' ),
            'class' => 'epl_w50' ),
        '_epl_price_date_from' => array(
            'weight' => 45,
            'input_type' => 'text',
            'input_name' => '_epl_price_date_from[]',
            'label' => epl__( 'From' ),
            'class' => 'epl_w90 datepicker',
            'data_type' => 'unix_time',
            '__func' => 'epl_admin_date_display' ),
        '_epl_price_date_to' => array(
            'weight' => 50,
            'input_type' => 'text',
            'input_name' => '_epl_price_date_to[]',
            'label' => epl__( 'To' ),
            'class' => 'epl_w90 datepicker',
            'data_type' => 'unix_time',
            '__func' => 'epl_admin_date_display' ),
        '_epl_price_member_only' => array(
            'weight' => 53,
            'input_type' => 'select',
            'input_name' => '_epl_price_member_only[]',
            'label' => epl__( 'Member Only Price' ),
            'class' => '',
            'options' => epl_yes_no(),
            'default_value' => 0
        ),
        '_epl_price_forms' => array(
            'weight' => 60,
            'input_type' => 'checkbox',
            'input_name' => '_epl_price_forms[]',
            'label' => epl__( 'Price Specific Form (optional)' ),
            'options' => array(),
            'second_key' => '[]',
            'help_text' => epl__( 'Use these forms only if you would like to collect different information if the user selects this price.' ),
        ),
        '_epl_price_forms_per' => array(
            'weight' => 65,
            'input_type' => 'select',
            'input_name' => '_epl_price_forms_per[]',
            'label' => epl__( 'Display the form ' ),
            'options' => array( 1 => epl__( 'For each Attendee' ), 2 => epl__( 'Only Once' ) ),
        ),
        '_epl_date_specific_price' => array(
            'weight' => 35,
            'input_type' => 'text',
            'input_name' => '_epl_date_specific_price[]',
            'label' => epl__( 'Date Specific?' ),
            'readonly' => true,
            'display_inline' => true,
            'class' => 'epl_w80'
        ),
        '_epl_time_specific_price' => array(
            'weight' => 35,
            'input_type' => 'text',
            'input_name' => '_epl_time_specific_price[]',
            'label' => epl__( 'Time Specific?' ),
            'readonly' => true,
            'display_inline' => true,
            'class' => 'epl_w80'
        ),
        '_epl_price_to_offset' => array(
            'weight' => 80,
            'input_type' => 'select',
            'input_name' => '_epl_price_to_offset[]',
            'options' => array(),
            'empty_row' => true,
            'class' => 'price_to_offset reset_val'
        ),
        '_epl_price_offset_count' => array(
            'weight' => 85,
            'input_type' => 'text',
            'input_name' => '_epl_price_offset_count[]',
            'class' => 'epl_w40 reset_val',
            'default_value' => 0
        ),
        '_epl_price_surcharge_method' => array(
            'weight' => 90,
            'input_type' => 'select',
            'input_name' => '_epl_price_surcharge_method[]',
            'label' => epl__( 'Surcharge Method' ),
            'options' => array(
                'add' => epl__( 'Add' ),
            //'include' => epl__( 'Includes' )
            ),
            //'empty_row' => true,
            'class' => '' ),
        '_epl_price_surcharge_amount' => array(
            'weight' => 92,
            'input_type' => 'text',
            'input_name' => '_epl_price_surcharge_amount[]',
            'label' => epl__( 'Amount' ),
            'class' => 'epl_w60',
            'data_type' => 'float',
        ),
        '_epl_price_surcharge_type' => array(
            'weight' => 94,
            'input_type' => 'select',
            'input_name' => '_epl_price_surcharge_type[]',
            'label' => epl__( 'Surcharge Type' ),
            'options' => array( 5 => epl__( 'Fixed' ), 10 => epl__( 'Percent' ) ),
        ),
        '_epl_price_surcharge_per' => array(
            'weight' => 96,
            'input_type' => 'select',
            'input_name' => '_epl_price_surcharge_per[]',
            'label' => epl__( 'for' ),
            'options' => array( 5 => epl__( 'Each quantity' ), 10 => epl__( 'Only once' ) ),
        ),
    );


    if ( epl_get_regis_setting( '_epl_enable_PP_parallel_pay' ) == 10 ) {

        $_a['_epl_price_parallel_pay_email'] = array(
            'weight' => 100,
            'input_type' => 'text',
            'input_name' => '_epl_price_parallel_pay_email[]',
            'label' => epl__( 'PayPal Parallel Pay Email' ),
            'class' => '',
        );
    }

    //$_a = ( array ) maybe_unserialize( get_option( 'epl_advance_cap_mod_a' ) );
    //$_a = ( array ) maybe_unserialize( base64_decode(get_option( 'epl_advance_cap_mod', true )) );

    $epl_fields += $_a;


    if ( epl_is_addon_active( 'DASFERWEQREWE' ) ) {

        $epl_fields['_epl_price_pack_type'] = array(
            'weight' => 66,
            'input_type' => 'select',
            'input_name' => '_epl_price_pack_type[]',
            'label' => epl__( 'Package Type' ),
            'options' => array( 'count' => epl__( 'Count Based' ), 'time' => epl__( 'Time Based' ) ),
                //'help_text' => epl__( 'This applies to time based membership price type.' ),
        );

        $epl_fields['_epl_price_pack_time_length'] = array(
            'weight' => 65,
            'input_type' => 'text',
            'input_name' => '_epl_price_pack_time_length[]',
            'label' => epl__( 'Time Length' ),
            'default_value' => 1,
            'class' => 'epl_w40',
        );
        $epl_fields['_epl_price_pack_time_length_type'] = array(
            'weight' => 66,
            'input_type' => 'select',
            'input_name' => '_epl_price_pack_time_length_type[]',
            'label' => '',
            'options' => array( 'day' => epl__( 'Day' ), 'week' => epl__( 'Week' ), 'month' => epl__( 'Month' ) ),
        );

        /*     $epl_fields['_epl_price_membership_min_level'] = array(
          'weight' => 67,
          'input_type' => 'checkbox',
          'input_name' => '_epl_price_membership_min_level[]',
          'label' => epl__( 'Minimum Membership Level' ),
          'options' => epl_get_roles_arr(),
          'second_key' => '[]',
          'display_inline' => true,
          ); */
    }

    return $epl_fields;
}


function _epl_admin_view_event_time_price_0_price_data_row( $price_id, $row_data ) {



    echo "<tr><td colspan='12'>{$row_data['_epl_price_parallel_pay_email']['label']} {$row_data['_epl_price_parallel_pay_email']['field']}</td></tr>";
}


function epl_time_fields( $epl_fields ) {
    global $event_details;
    $_a = array( '_epl_regis_endtime' => array(
            'weight' => 30,
            'input_type' => 'text',
            'input_name' => '_epl_regis_endtime[]',
            'label' => epl__( 'Regis. End Time' ),
            'class' => 'epl_w60 timepicker' ),
        '_epl_time_capacity' => array(
            'weight' => 35,
            'input_type' => 'text',
            'input_name' => '_epl_time_capacity[]',
            'label' => epl__( 'Capacity' ),
            'class' => 'epl_w50' ),
        '_epl_date_specific_time' => array(
            'weight' => 36,
            'input_type' => 'text',
            'input_name' => '_epl_date_specific_time[]',
            'label' => epl__( 'Date Specific?' ),
            'readonly' => true,
            'display_inline' => true,
            'class' => 'epl_w80'
        ),
        '_epl_weekday_specific_time' => array(
            'weight' => 37,
            'input_type' => 'checkbox',
            'input_name' => '_epl_weekday_specific_time[]',
            'label' => epl__( 'Weekday Specific?' ),
            'options' => array( 1 => 'Monday', 2 => 'Tuesday', 3 => 'Wednesday', 4 => 'Thursday', 5 => 'Friday', 6 => 'Saturday', 7 => 'Sunday' ),
            'second_key' => '[]',
            'display_inline' => true,
        )
    );

    $epl_fields += $_a;

    return $epl_fields;
}


function epl_price_option_fields( $epl_fields ) {

    $_a = array( '_epl_multi_price_select' => array(
            'input_type' => 'select',
            'input_name' => '_epl_multi_price_select',
            'options' => epl_yes_no(),
            'label' => epl__( 'Date level price' ),
            'help_text' => epl__( 'If the event happens on different days, can the user select a different price for each one of the days?' ),
            'default_value' => 0,
        ),
    );

    $epl_fields += $_a;

    return $epl_fields;
}


function epl_time_option_fields( $epl_fields ) {

    $_a = array(
        '_epl_multi_time_select' => array(
            'weight' => 10,
            'input_type' => 'select',
            'input_name' => '_epl_multi_time_select',
            'options' => epl_yes_no(),
            'label' => epl__( 'Date level time' ),
            'help_text' => epl__( 'If the event happens on different days, can the user select a different time for each one of the days?' ),
            'default_value' => 0,
        ),
        '_epl_rolling_regis' => array(
            'weight' => 11,
            'input_type' => 'select',
            'input_name' => '_epl_rolling_regis',
            'options' => epl_yes_no(),
            'label' => epl__( 'Enable Rolling Registration?' ),
            'help_text' => epl__( 'With this setting, the user will be only allowed to register for the next available open date or time slot.' ),
            'default_value' => 0,
        ),
        '_epl_pack_regis' => array(
            'weight' => 12,
            'input_type' => 'select',
            'input_name' => '_epl_pack_regis',
            'options' => epl_yes_no(),
            'label' => epl__( 'Enable Pack Registration?' ),
            'default_value' => 0,
            'help_text' => epl__( 'This setting will let you indicate how many days a certain price will reserve if you offer a multiday / multiweek events.' ),
            'help_icon_type' => '-red',
        ),
        '_epl_pack_regis_consecutive' => array(
            'weight' => 13,
            'input_type' => 'select',
            'input_name' => '_epl_pack_regis_consecutive',
            'options' => epl_yes_no(),
            'label' => epl__( 'Consecutive?' ),
            'default_value' => 10,
            'help_text' => epl__( 'If yes, the user will register for the next X consecutive classes.' ),
        ),
        '_epl_enable_deposit_payment' => array(
            'weight' => 13,
            'input_type' => 'select',
            'input_name' => '_epl_enable_deposit_payment',
            'options' => epl_yes_no(),
            'label' => epl__( 'Enable deposit payment?' ),
            'default_value' => 0,
            'class' => 'dependence_check',
            'help_text' => epl__( 'If yes, the user will have the option of paying a deposit during the registration and pay the balance at a later date.' ),
        ),
        '_epl_deposit_type' => array(
            'weight' => 14,
            'input_type' => 'select',
            'input_name' => '_epl_deposit_type',
            'options' => array( 5 => epl__( 'Fixed' ), 10 => epl__( 'Percent' ) ),
            'label' => epl__( 'Deposit Type' ),
            'default_value' => 1,
            'help_text' => epl__( 'If Percent, the deposit amount will inclde any surcharges but will exclude discounts.' ),
        ),
        '_epl_deposit_amount' => array(
            'weight' => 15,
            'input_type' => 'text',
            'input_name' => '_epl_deposit_amount',
            'label' => epl__( 'Deposit Amount' ),
        ),
    );

    $epl_fields += $_a;

    return $epl_fields;
}

/*

 */


function epl_other_settings_fields( $epl_fields ) {

    $_a = array(
        '_epl_event_notification' => array(
            'weight' => 24,
            'input_type' => 'select',
            'input_name' => '_epl_event_notification',
            'options' => get_list_of_available_notifications(),
            'empty_row' => true,
            'label' => epl__( 'Email message to use for confirmations.' ),
            'empty_options_msg' => epl__( 'No email messages found.  Please go to Events Planner > Notification Manager to create notifications.' )
        ),
        /* '_epl_notification_replace' => array(
          'weight' => 25,
          'input_type' => 'select',
          'input_name' => '_epl_notification_replace',
          'options' => epl_yes_no(),
          'default_value' => 0,
          'label' => epl__( 'Replace the default email?' ),
          'help_text' => '<b>DEPRECATED</b>.  Please use the notification manager to create custom notifications.  Will be removed in 1.3.1. ', //epl__( 'If no, the message in the notification will get appended to the default confirmation email.' ),
          'style' => "margin-top:7px;"
          ), */
        '_epl_event_instructor' => array(
            'weight' => 28,
            'input_type' => 'checkbox',
            'input_name' => '_epl_event_instructor[]',
            'options' => get_list_of_instructors(),
            'label' => epl__( 'Presenter, instructor, Trainer...' ),
            'style' => "",
            'empty_options_msg' => epl__( 'No Presenter/instructor found.  Please go to Events Planner > Instructors to create some.' ),
            'auto_key' => true
        ),
            /* '_epl_event_regis_flow' => array(
              'weight' => 30,
              'input_type' => 'radio',
              'input_name' => '_epl_event_regis_flow',
              'options' => array(
              1 => epl__( 'Register Button > Cart > Registration Forms > Overview > Pay and/or Complete' ),
              2 => 'BETA! WILL NOT WORK FOR MONERIS. ' . epl__( 'Register Button > Cart > Registration Forms > Pay and/or Complete' ),
              10 => 'BETA!  ' . epl__( 'Register Button > Cart + Registration Forms > Pay and/or Complete' ),
              ),
              'label' => epl__( 'Registration Flow' ) . '. DISABLED FOR FURTHER REVIEW.',
              'help_text' => epl__( 'EXPERIMENTAL FEATURE.  The third option will only work if you have one payment type, and no attendee information needs to be collected.' ),
              'help_icon_type' => '-red',
              'default_value' => epl_get_setting( 'epl_registration_options', '_epl_default_event_regis_flow', 1 ),
              'style' => "",
              ), */
    );

    $epl_fields += $_a;

    if ( epl_is_addon_active( 'ASDFAWEEFADSF' ) && epl_get_setting( 'epl_api_option_fields', 'epl_mc_key' ) != '' ) {


        $epl_fields['_epl_offer_notification_sign_up'] = array(
            'weight' => 26,
            'input_type' => 'select',
            'input_name' => '_epl_offer_notification_sign_up',
            'options' => array( 0 => epl__( 'No' ), 1 => epl__( 'Ask' ), 3 => epl__( 'Automatically add' ) ),
            'default_value' => epl_get_setting( 'epl_api_option_fields', '_epl_mc_offer_notification_sign_up' ),
            'label' => epl__( 'Offer to sign up to a notification list?' ),
            'help_text' => epl__( 'If yes, select a notification list below.' )
        );
        $epl_fields['_epl_notification_list'] = array(
            'weight' => 27,
            'input_type' => 'select',
            'input_name' => '_epl_notification_list',
            'options' => epl_get_mailchimp_lists(),
            'empty_row' => true,
            'default_value' => epl_get_setting( 'epl_api_option_fields', '_epl_mc_default_list' ),
            'label' => epl__( 'Select a notification list.' ),
        );
    }

    return $epl_fields;
}

/*
 * PRO configurations
 */


function epl_pay_profile_manager_fields( $epl_fields ) {

    $epl_fields['_pp_pro_fields'] = array(
        '_epl_pay_help' => array(
            'input_type' => 'section',
            'label' => epl__( 'PayPal PRO' ),
            'content' => sprintf( epl__( 'Accept Credit and Debit Cards without having the customer leave your website. Currently available for US and Canada.
                <br />%sMUST USE SSL FOR PCI COMPLIANCE.  You can enable SSL from Events Planner > Settings > Registration tab.%s' ), '<br /><span class="epl_font_red"><strong>', '</strong></span>' )
        ),
        '_epl_pay_type' => array(
            'input_type' => 'hidden',
            'input_name' => '_epl_pay_type',
            'default_value' => '_pp_pro',
        ),
        '_epl_pay_display' => array(
            'input_type' => 'textarea',
            'input_name' => '_epl_pay_display',
            'label' => epl__( 'Label' ),
            'help_text' => epl__( 'What the customer will see as an option.  You can use text or embed and image.' ),
            'class' => 'epl_w300',
            'required' => true ),
        '_epl_user' => array(
            'input_type' => 'text',
            'input_name' => '_epl_user',
            'id' => '',
            'label' => epl__( 'API Username' ),
            'help_text' => epl__( 'Ex: some_api1.youremailaddress.com' ),
            'class' => 'epl_w300',
            'required' => true ),
        '_epl_pwd' => array(
            'input_type' => 'password',
            'input_name' => '_epl_pwd',
            'id' => '',
            'label' => epl__( 'API Password' ),
            'help_text' => epl__( 'Ex: SDFE23D5SFD324' ),
            'class' => '_epl_w300',
            'required' => true ),
        '_epl_sig' => array(
            'input_type' => 'password',
            'input_name' => '_epl_sig',
            'id' => '',
            'label' => epl__( 'Signature' ),
            'help_text' => epl__( 'Will be a very long string. Ex. SRl31AbcSd9fIqew......' ),
            'class' => 'epl_w300',
            'required' => true ),
        '_epl_accepted_cards' => array(
            'input_type' => 'checkbox',
            'input_name' => '_epl_accepted_cards[]',
            'label' => epl__( 'Accepted Cards' ),
            'auto_key' => true,
            'options' => array( 'Visa' => 'Visa', 'MasterCard' => 'Master Card', 'Discover' => 'Discover', 'Amex' => 'Amex' ),
            'help_text' => epl__( 'For Canada, only MasterCard and Visa are allowable and Interac debit cards are not supported.' ),
            'default_checked' => 1,
            'required' => true ),
        '_epl_sandbox' => array(
            'input_type' => 'select',
            'input_name' => '_epl_sandbox',
            'label' => epl__( 'Test Mode?' ),
            'options' => epl_yes_no(),
            'help_text' => epl__( 'If yes, please make sure you use Sandbox credentials above.' ),
            'class' => '' ),
        '_epl_default_selected' => array(
            'input_type' => 'select',
            'input_name' => '_epl_default_selected',
            'label' => epl__( 'Automatically assign to all new events?' ),
            'options' => epl_yes_no(),
            'help_text' => epl__( 'If this option is set to yes, all future events will have this payment profile automatically selected as a payment choice.' ),
            'class' => '' ),
    );

    $epl_fields['_pp_pro_fields'] = apply_filters( 'epl_pp_pro_fields', $epl_fields['_pp_pro_fields'] );

    $epl_fields['_pp_payflow_fields'] = array(
        '_epl_pay_help' => array(
            'input_type' => 'section',
            'label' => epl__( 'PayPal PayFlow PRO' ),
            'content' => sprintf( epl__( 'Accept Credit and Debit Cards without having the customer leave your website.
                <br />%sMUST USE SSL FOR PCI COMPLIANCE.  You can enable SSL from Events Planner > Settings > Registration tab.%s' ), '<br /><span class="epl_font_red"><strong>', '</strong></span>' )
        ),
        '_epl_pay_type' => array(
            'input_type' => 'hidden',
            'input_name' => '_epl_pay_type',
            'default_value' => '_pp_payflow',
        ),
        '_epl_pay_display' => array(
            'input_type' => 'textarea',
            'input_name' => '_epl_pay_display',
            'label' => epl__( 'Label' ),
            'help_text' => epl__( 'What the customer will see as an option.  You can use text or embed and image.' ),
            'class' => 'epl_w300',
            'required' => true ),
        '_epl_partner' => array(
            'input_type' => 'text',
            'input_name' => '_epl_partner',
            'id' => '',
            'label' => epl__( 'Partner' ),
            'class' => 'epl_w300',
            'required' => true ),
        '_epl_vendor' => array(
            'input_type' => 'text',
            'input_name' => '_epl_vendor',
            'id' => '',
            'label' => epl__( 'Vendor' ),
            'class' => 'epl_w300',
            'required' => true ),
        '_epl_user' => array(
            'input_type' => 'text',
            'input_name' => '_epl_user',
            'id' => '',
            'label' => epl__( 'User' ),
            'class' => 'epl_w300',
            'required' => true ),
        '_epl_pwd' => array(
            'input_type' => 'password',
            'input_name' => '_epl_pwd',
            'id' => '',
            'label' => epl__( 'Password' ),
            'class' => '_epl_w300',
            'required' => true ),
        '_epl_accepted_cards' => array(
            'input_type' => 'checkbox',
            'input_name' => '_epl_accepted_cards[]',
            'label' => epl__( 'Accepted Cards' ),
            'auto_key' => true,
            'options' => array( 'Visa' => 'Visa', 'MasterCard' => 'Master Card', 'Discover' => 'Discover', 'Amex' => 'Amex' ),
            'help_text' => epl__( 'For Canada, only MasterCard and Visa are allowable and Interac debit cards are not supported.' ),
            'default_checked' => 1,
            'required' => true ),
        '_epl_sandbox' => array(
            'input_type' => 'select',
            'input_name' => '_epl_sandbox',
            'label' => epl__( 'Test Mode?' ),
            'options' => epl_yes_no(),
            'help_text' => epl__( 'If yes, please make sure you use Sandbox credentials above.' ),
            'class' => '' ),
        '_epl_default_selected' => array(
            'input_type' => 'select',
            'input_name' => '_epl_default_selected',
            'label' => epl__( 'Automatically assign to all new events?' ),
            'options' => epl_yes_no(),
            'help_text' => epl__( 'If this option is set to yes, all future events will have this payment profile automatically selected as a payment choice.' ),
            'class' => '' ),
    );

    $epl_fields['_pp_payflow_fields'] = apply_filters( 'epl_pp_payflow_fields', $epl_fields['_pp_payflow_fields'] );

    $epl_fields['_auth_net_aim_fields'] = array(
        '_epl_pay_help' => array(
            'input_type' => 'section',
            'label' => epl__( 'Authorize.net AIM' ),
            'content' => sprintf( epl__( 'Accept Credit and Debit Cards without having the customer leave your website. %sMUST USE SSL FOR PCI COMPLIANCE.
                You can enable SSL from Events Planner > Settings > Registration tab.%s' ), '<br /><span class="epl_font_red"><strong>', '</strong></span>' )
        ),
        '_epl_pay_type' => array(
            'input_type' => 'hidden',
            'input_name' => '_epl_pay_type',
            'default_value' => '_auth_net_aim',
        ),
        '_epl_pay_display' => array(
            'input_type' => 'textarea',
            'input_name' => '_epl_pay_display',
            'id' => '',
            'label' => epl__( 'Label' ),
            'help_text' => epl__( 'What the customer will see as an option.  Ex. Credit Card.' ),
            'class' => 'epl_w300',
            'required' => true ),
        '_epl_user' => array(
            'input_type' => 'password',
            'input_name' => '_epl_user',
            'id' => '',
            'label' => epl__( 'API Login ID' ),
            'help_text' => epl__( 'This number can be obtained by logging into Authorize.net > Account > API Login ID and Transaction Key' ),
            'class' => 'epl_w300',
            'required' => true ),
        '_epl_pwd' => array(
            'input_type' => 'password',
            'input_name' => '_epl_pwd',
            'id' => '',
            'label' => epl__( 'API Transactioin Key' ),
            'class' => 'epl_w300',
            'required' => true ),
        '_epl_accepted_cards' => array(
            'input_type' => 'checkbox',
            'input_name' => '_epl_accepted_cards[]',
            'label' => epl__( 'Accepted Cards' ),
            'auto_key' => true,
            'options' => array( 'Visa' => 'Visa', 'MasterCard' => 'Master Card', 'Discover' => 'Discover', 'Amex' => 'Amex' ),
            'class' => '' ),
        '_epl_sandbox' => array(
            'input_type' => 'select',
            'input_name' => '_epl_sandbox',
            'label' => epl__( 'Test Mode?' ),
            'options' => epl_yes_no(),
            'help_text' => epl__( 'If yes, please make sure you use Sandbox credentials above.' ),
            'class' => '' ),
        '_epl_default_selected' => array(
            'input_type' => 'select',
            'input_name' => '_epl_default_selected',
            'label' => epl__( 'Automatically assign to all new events?' ),
            'options' => epl_yes_no(),
            'help_text' => epl__( 'If this option is set to yes, all future events will have this payment profile automatically selected as a payment choice.' ),
            'class' => '' ),
    );

    $epl_fields['_auth_net_aim_fields'] = apply_filters( 'epl_auth_net_aim_fields', $epl_fields['_auth_net_aim_fields'] );

    $epl_fields['_auth_net_sim_fields'] = array(
        '_epl_pay_help' => array(
            'input_type' => 'section',
            'label' => epl__( 'Authorize.net SIM' ),
            'content' => epl__( 'Accept credit cards on Authorize.net hosted page.' )
        ),
        '_epl_pay_type' => array(
            'input_type' => 'hidden',
            'input_name' => '_epl_pay_type',
            'default_value' => '_auth_net_sim',
        ),
        '_epl_pay_display' => array(
            'input_type' => 'textarea',
            'input_name' => '_epl_pay_display',
            'id' => '',
            'label' => epl__( 'Label' ),
            'help_text' => epl__( 'What the customer will see as an option.  Ex. Credit Card.' ),
            'class' => 'epl_w300',
            'required' => true ),
        '_epl_user' => array(
            'input_type' => 'password',
            'input_name' => '_epl_user',
            'id' => '',
            'label' => epl__( 'API Login ID' ),
            'help_text' => epl__( 'This number can be obtained by logging into Authorize.net > Account > API Login ID and Transaction Key' ),
            'class' => 'epl_w300',
            'required' => true ),
        '_epl_pwd' => array(
            'input_type' => 'password',
            'input_name' => '_epl_pwd',
            'id' => '',
            'label' => epl__( 'API Transactioin Key' ),
            'class' => 'epl_w300',
            'required' => true ),
        '_epl_md5_hash' => array(
            'input_type' => 'password',
            'input_name' => '_epl_md5_hash',
            'id' => '',
            'label' => 'MD5 Hash',
            'class' => 'epl_w300',
            'required' => true,
            'help_text' => 'If you entered an MD5 Hash in your authorize.net account admin settings, enter it here also.'
        ),
        '_epl_sandbox' => array(
            'input_type' => 'select',
            'input_name' => '_epl_sandbox',
            'label' => epl__( 'Test Mode?' ),
            'options' => epl_yes_no(),
            'help_text' => epl__( 'If yes, please make sure you use Sandbox credentials above.' ),
            'class' => '' ),
        '_epl_default_selected' => array(
            'input_type' => 'select',
            'input_name' => '_epl_default_selected',
            'label' => epl__( 'Automatically assign to all new events?' ),
            'options' => epl_yes_no(),
            'help_text' => epl__( 'If this option is set to yes, all future events will have this payment profile automatically selected as a payment choice.' ),
            'class' => '' ),
    );

    $epl_fields['_auth_net_sim_fields'] = apply_filters( 'epl_auth_net_sim_fields', $epl_fields['_auth_net_sim_fields'] );

    $epl_fields['_qbmc_fields'] = array(
        '_epl_pay_help' => array(
            'input_type' => 'section',
            'label' => epl__( 'QuickBooks Merc. Serv.' ),
            'content' => sprintf( epl__( 'Accept Credit and Debit Cards using QuickBooks Merchant Services. %sMUST USE SSL FOR PCI COMPLIANCE.
                You can enable SSL from Events Planner > Settings > Registration tab.%s' ), '<br /><span class="epl_font_red"><strong>', '</strong></span>' )
        ),
        '_epl_pay_type' => array(
            'input_type' => 'hidden',
            'input_name' => '_epl_pay_type',
            'default_value' => '_qbmc',
        ),
        '_epl_pay_display' => array(
            'input_type' => 'textarea',
            'input_name' => '_epl_pay_display',
            'id' => '',
            'label' => epl__( 'Label' ),
            'help_text' => epl__( 'What the customer will see as an option.  Ex. Credit Card.' ),
            'class' => 'epl_w300',
            'required' => true ),
        '_epl_user' => array(
            'input_type' => 'password',
            'input_name' => '_epl_user',
            'id' => '',
            'label' => epl__( 'Application Login' ),
            'help_text' => '',
            'class' => 'epl_w300',
            'required' => true ),
        '_epl_pwd' => array(
            'input_type' => 'password',
            'input_name' => '_epl_pwd',
            'id' => '',
            'label' => epl__( 'Connection Ticket' ),
            'class' => 'epl_w300',
            'required' => true ),
        '_epl_accepted_cards' => array(
            'input_type' => 'checkbox',
            'input_name' => '_epl_accepted_cards[]',
            'label' => epl__( 'Accepted Cards' ),
            'options' => array( 'Visa' => 'Visa', 'MasterCard' => 'Master Card', 'Discover' => 'Discover', 'Amex' => 'Amex' ),
            'class' => '' ),
        '_epl_sandbox' => array(
            'input_type' => 'select',
            'input_name' => '_epl_sandbox',
            'label' => epl__( 'Test Mode?' ),
            'options' => epl_yes_no(),
            'help_text' => epl__( 'If yes, please make sure you use Sandbox credentials above.' ),
            'class' => '' ),
        '_epl_default_selected' => array(
            'input_type' => 'select',
            'input_name' => '_epl_default_selected',
            'label' => epl__( 'Automatically assign to all new events?' ),
            'options' => epl_yes_no(),
            'help_text' => epl__( 'If this option is set to yes, all future events will have this payment profile automatically selected as a payment choice.' ),
            'class' => '' ),
    );

    $epl_fields['_qbmc_fields'] = apply_filters( 'epl_qbmc_fields', $epl_fields['_qbmc_fields'] );

    $epl_fields['_check_fields'] = array(
        '_epl_pay_help' => array(
            'input_type' => 'section',
            'label' => epl__( 'Checks/Money Orders' ),
            'content' => epl__( 'You can use this to give your customers the ability to pay using a Check.' )
        ),
        '_epl_pay_type' => array(
            'input_type' => 'hidden',
            'input_name' => '_epl_pay_type',
            'default_value' => '_check',
        ),
        '_epl_pay_display' => array(
            'input_type' => 'textarea',
            'input_name' => '_epl_pay_display',
            'id' => '',
            'label' => epl__( 'Display Label' ),
            'help_text' => epl__( 'What the customer will see as an option.' ),
            'class' => 'epl_w300' ),
        '_epl_check_payable_to' => array(
            'input_type' => 'text',
            'input_name' => '_epl_check_payable_to',
            'id' => '_epl_form_label',
            'label' => epl__( 'Make Payable To' ),
            'help_text' => epl__( 'Who will get this check?' ),
            'class' => 'epl_w300',
            'required' => true ),
        '_epl_check_address' => array(
            'input_type' => 'textarea',
            'input_name' => '_epl_check_address',
            'label' => epl__( 'Send Payment To' ),
            'help_text' => epl__( 'The address.' ),
            'class' => 'epl_w300', ),
        '_epl_instructions' => array(
            'input_type' => 'textarea',
            'input_name' => '_epl_instructions',
            'id' => '',
            'label' => epl__( 'Instructions' ),
            'help_text' => epl__( 'Special Instruction to the customer.' ),
            'class' => 'epl_w300' ),
        '_epl_default_selected' => array(
            'input_type' => 'select',
            'input_name' => '_epl_default_selected',
            'label' => epl__( 'Automatically assign to all new events?' ),
            'options' => epl_yes_no(),
            'help_text' => epl__( 'If this option is set to yes, all future events will have this payment profile automatically selected as a payment choice.' ),
            'class' => '' ),
        '_epl_pay_discount_amount' => array(
            'input_type' => 'text',
            'input_name' => '_epl_pay_discount_amount',
            'label' => epl__( 'Discount for using this payment method' ),
            'help_text' => epl__( 'Leave empty if no discount.  Enter in 3.00 or 0.1 format.  Please note that this amount is applied before any other discount.' ),
            'class' => 'epl_w40' ),
        '_epl_pay_discount_type' => array(
            'input_type' => 'select',
            'input_name' => '_epl_pay_discount_type',
            'label' => epl__( 'Discount Type' ),
            'options' => array( 5 => epl__( 'Fixed' ), 10 => epl__( 'Percent' ) ),
        ),
        '_epl_pay_discount_label' => array(
            'input_type' => 'text',
            'input_name' => '_epl_pay_discount_label',
            'label' => epl__( 'Discount label' ),
            'class' => 'epl_w300' ),
            /* '_epl_pay_discount_when' => array(
              'input_type' => 'select',
              'input_name' => '_epl_pay_discount_when',
              'label' => epl__( 'Discount Type' ),
              'options' => array( 5 => epl__( 'Before Other Discounts' ), 10 => epl__( 'After Other Discounts' ) ),
              ), */
    );

    $epl_fields['_check_fields'] = apply_filters( 'epl_check_fields', $epl_fields['_check_fields'] );

    $epl_fields['_cash_fields'] = array(
        '_epl_pay_help' => array(
            'input_type' => 'section',
            'label' => epl__( 'Cash' ),
            'content' => epl__( 'You can use this to give your customers the ability to pay using Cash.' )
        ),
        '_epl_pay_type' => array(
            'input_type' => 'hidden',
            'input_name' => '_epl_pay_type',
            'default_value' => '_cash',
        ),
        '_epl_pay_display' => array(
            'input_type' => 'textarea',
            'input_name' => '_epl_pay_display',
            'id' => '',
            'label' => epl__( 'Display Label' ),
            'help_text' => epl__( 'What the customer will see as an option.' ),
            'class' => 'epl_w300' ),
        '_epl_instructions' => array(
            'input_type' => 'textarea',
            'input_name' => '_epl_instructions',
            'id' => '',
            'label' => epl__( 'Instructions' ),
            'help_text' => epl__( 'Special Instruction to the customer.' ),
            'class' => 'epl_w300' ),
        '_epl_default_selected' => array(
            'input_type' => 'select',
            'input_name' => '_epl_default_selected',
            'label' => epl__( 'Automatically assign to all new events?' ),
            'options' => epl_yes_no(),
            'help_text' => epl__( 'If this option is set to yes, all future events will have this payment profile automatically selected as a payment choice.' ),
            'class' => '' ),
        '_epl_pay_discount_amount' => array(
            'input_type' => 'text',
            'input_name' => '_epl_pay_discount_amount',
            'label' => epl__( 'Discount for using this payment method' ),
            'help_text' => epl__( 'Leave empty if no discount.  Enter in 3.00 or 0.1 format.  Please note that this amount is applied before any other discount.' ),
            'class' => 'epl_w40' ),
        '_epl_pay_discount_type' => array(
            'input_type' => 'select',
            'input_name' => '_epl_pay_discount_type',
            'label' => epl__( 'Discount Type' ),
            'options' => array( 5 => epl__( 'Fixed' ), 10 => epl__( 'Percent' ) ),
        ),
        '_epl_pay_discount_label' => array(
            'input_type' => 'text',
            'input_name' => '_epl_pay_discount_label',
            'label' => epl__( 'Discount label' ),
            'class' => 'epl_w300' ),
    );

    $epl_fields['_cash_fields'] = apply_filters( 'epl_cash_fields', $epl_fields['_cash_fields'] );

    $epl_fields['_payson_fields'] = array(
        '_epl_pay_help' => array(
            'input_type' => 'section',
            'label' => epl__( 'PAYSON' ),
            'content' => epl__( 'Safely and securely accept payments on hosted Payson servers.' )
        ),
        '_epl_pay_type' => array(
            'input_type' => 'hidden',
            'input_name' => '_epl_pay_type',
            'default_value' => '_payson',
        ),
        '_epl_pay_display' => array(
            'input_type' => 'textarea',
            'input_name' => '_epl_pay_display',
            'id' => '',
            'label' => epl__( 'Label' ),
            'help_text' => epl__( 'What the customer will see as an option.  Ex. Credit Card.' ),
            'class' => 'epl_w300',
            'required' => true ),
        '_epl_seller_email' => array(
            'input_type' => 'text',
            'input_name' => '_epl_seller_email',
            'id' => '',
            'label' => epl__( 'Seller Email' ),
            'class' => 'epl_w300',
            'required' => true ),
        '_epl_user' => array(
            'input_type' => 'password',
            'input_name' => '_epl_user',
            'id' => '',
            'label' => epl__( 'Agent ID' ),
            'help_text' => epl__( 'This number can be obtained by logging into Authorize.net > Account > API Login ID and Transaction Key' ),
            'class' => 'epl_w300',
            'required' => true ),
        '_epl_pwd' => array(
            'input_type' => 'password',
            'input_name' => '_epl_pwd',
            'id' => '',
            'label' => epl__( 'MD5' ),
            'class' => 'epl_w300',
            'required' => true ),
        '_epl_selected_by_default' => array(
            'input_type' => 'radio',
            'input_name' => '_epl_selected_by_default',
            'id' => '',
            'label' => epl__( 'Assign by default to new events?' ),
            'options' => epl_yes_no(),
            'display_inline' => true,
            'default_value' => 0,
            'help_text' => epl__( 'When you create new events, this paymetn profile will be automatically assigned to that event.' ),
            'required' => true ),
        '_epl_default_selected' => array(
            'input_type' => 'select',
            'input_name' => '_epl_default_selected',
            'label' => epl__( 'Automatically assign to all new events?' ),
            'options' => epl_yes_no(),
            'help_text' => epl__( 'If this option is set to yes, all future events will have this payment profile automatically selected as a payment choice.' ),
            'class' => '' ),
    );

    $epl_fields['_payson_fields'] = apply_filters( 'epl_payson_fields', $epl_fields['_payson_fields'] );

    $epl_fields['_moneris_fields'] = array(
        '_epl_pay_help' => array(
            'input_type' => 'section',
            'label' => epl__( 'Moneris Hosted Paypage' ),
            'content' => epl__( 'Accept payments using Moneris Hosted Paypage.' )
        ),
        '_epl_pay_type' => array(
            'input_type' => 'hidden',
            'input_name' => '_epl_pay_type',
            'default_value' => '_moneris',
        ),
        '_epl_pay_display' => array(
            'input_type' => 'textarea',
            'input_name' => '_epl_pay_display',
            'id' => '',
            'label' => epl__( 'Label' ),
            'help_text' => epl__( 'What the customer will see as an option.  Ex. Credit Card.' ),
            'class' => 'epl_w300',
            'required' => true ),
        '_epl_moneris_country' => array(
            'input_type' => 'select',
            'input_name' => '_epl_moneris_country',
            'label' => epl__( 'Version' ),
            'options' => array('ca' => epl__('Canada'), 'usa'=>epl__('USA')),
            'class' => '' ),
        '_epl_user' => array(
            'input_type' => 'password',
            'input_name' => '_epl_user',
            'id' => '',
            'label' => epl__( 'Store ID' ) . ', ' .epl__('HHP_id for USA'),
            'help_text' => '',
            'class' => 'epl_w300',
            'required' => true ),
        '_epl_pwd' => array(
            'input_type' => 'password',
            'input_name' => '_epl_pwd',
            'id' => '',
            'label' => epl__( 'HPP Key' ),
            'class' => 'epl_w300',
            'required' => true ),
        '_epl_sandbox' => array(
            'input_type' => 'select',
            'input_name' => '_epl_sandbox',
            'label' => epl__( 'Test Mode?' ),
            'options' => epl_yes_no(),
            'help_text' => epl__( 'If yes, please make sure you use Sandbox credentials above.' ),
            'class' => '' ),
        '_epl_pay_urls' => array(
            'input_type' => 'section',
            'label' => epl__( 'Your paypage urls' ),
            'content' => epl__( 'Approved URL:' ) . ' ' . add_query_arg( array( 'epl_action' => '_moneris_process' ), epl_get_sortcode_url() ) . '<br />' .
            epl__( 'Decline URL:' ) . ' ' . add_query_arg( array( 'epl_action' => '_moneris_process' ), epl_get_sortcode_url() ) 
        ),
        '_epl_default_selected' => array(
            'input_type' => 'select',
            'input_name' => '_epl_default_selected',
            'label' => epl__( 'Automatically assign to all new events?' ),
            'options' => epl_yes_no(),
            'help_text' => epl__( 'If this option is set to yes, all future events will have this payment profile automatically selected as a payment choice.' ),
            'class' => '' ),
    );

    $epl_fields['_moneris_fields'] = apply_filters( 'epl_moneris_fields', $epl_fields['_moneris_fields'] );

    $epl_fields['_firstdata_fields'] = array(
        '_epl_pay_help' => array(
            'input_type' => 'section',
            'label' => epl__( 'Firstdata' ),
            'content' => sprintf( epl__( 'Accept Credit and Debit Cards without having the customer leave your website. %sMUST USE SSL FOR PCI COMPLIANCE.
                You can enable SSL from Events Planner > Settings > Registration tab.%s' ), '<br /><span class="epl_font_red"><strong>', '</strong></span>' )
        ),
        '_epl_pay_pem_location' => array(
            'input_type' => 'section',
            'content' => epl_wrap( '<span class="epl_font_red">', '<span>', sprintf( epl__( 'Please place your .pem file in %s' ), epl_upload_dir_path( true ) . 'firstdata/' ) )
        ),
        '_epl_pay_type' => array(
            'input_type' => 'hidden',
            'input_name' => '_epl_pay_type',
            'default_value' => '_firstdata',
        ),
        '_epl_pay_display' => array(
            'input_type' => 'textarea',
            'input_name' => '_epl_pay_display',
            'id' => '',
            'label' => epl__( 'Label' ),
            'help_text' => epl__( 'What the customer will see as an option.  Ex. Credit Card.' ),
            'class' => 'epl_w300',
            'required' => true ),
        '_epl_user' => array(
            'input_type' => 'password',
            'input_name' => '_epl_user',
            'id' => '',
            'label' => epl__( 'Store Number' ),
            //'help_text' => epl__( 'This number can be obtained by logging into Authorize.net > Account > API Login ID and Transaction Key' ),
            'class' => 'epl_w300',
            'required' => true ),
        /* '_epl_pwd' => array(
          'input_type' => 'password',
          'input_name' => '_epl_pwd',
          'id' => '',
          'label' => epl__( 'API Transactioin Key' ),
          'class' => 'epl_w300',
          'required' => true ),
          '_epl_pem_file' => array(
          'input_type' => 'password',
          'input_name' => '_epl_pem_file',
          'label' => epl__( 'PEM File' ),
          'class' => 'epl_file_upload_trigger epl_w300',
          'required' => true,
          'placeholder' => epl__( 'Click here to upload a .pem file' ),
          'description' => epl__( 'Click on the field above to upload a .pem file' )
          ), */
        '_epl_accepted_cards' => array(
            'input_type' => 'checkbox',
            'input_name' => '_epl_accepted_cards[]',
            'label' => epl__( 'Accepted Cards' ),
            'auto_key' => true,
            'required' => true,
            'options' => array( 'Visa' => 'Visa', 'MasterCard' => 'Master Card', 'Discover' => 'Discover', 'Amex' => 'Amex' ),
            'class' => '' ),
        '_epl_sandbox' => array(
            'input_type' => 'select',
            'input_name' => '_epl_sandbox',
            'label' => epl__( 'Test Mode?' ),
            'options' => epl_yes_no(),
            'help_text' => epl__( 'If yes, please make sure you use Sandbox credentials above.' ),
            'class' => '' ),
        '_epl_default_selected' => array(
            'input_type' => 'select',
            'input_name' => '_epl_default_selected',
            'label' => epl__( 'Automatically assign to all new events?' ),
            'options' => epl_yes_no(),
            'help_text' => epl__( 'If this option is set to yes, all future events will have this payment profile automatically selected as a payment choice.' ),
            'help_text' => epl__( 'If this option is set to yes, all future events will have this payment profile automatically selected as a payment choice.' ),
            'class' => '' ),
    );

    $epl_fields['_firstdata_fields'] = apply_filters( 'epl_firstdata_fields', $epl_fields['_firstdata_fields'] );

    $epl_fields['_usa_epay_fields'] = array(
        '_epl_pay_help' => array(
            'input_type' => 'section',
            'label' => epl__( 'USA ePay' ),
            'content' => sprintf( epl__( 'Accept Credit and Debit Cards without having the customer leave your website. %sMUST USE SSL FOR PCI COMPLIANCE.
                You can enable SSL from Events Planner > Settings > Registration tab.%s' ), '<br /><span class="epl_font_red"><strong>', '</strong></span>' )
        ),
        '_epl_pay_type' => array(
            'input_type' => 'hidden',
            'input_name' => '_epl_pay_type',
            'default_value' => '_usa_epay',
        ),
        '_epl_pay_display' => array(
            'input_type' => 'textarea',
            'input_name' => '_epl_pay_display',
            'id' => '',
            'label' => epl__( 'Label' ),
            'help_text' => epl__( 'What the customer will see as an option.  Ex. Credit Card.' ),
            'class' => 'epl_w300',
            'required' => true ),
        /* '_epl_user' => array(
          'input_type' => 'password',
          'input_name' => '_epl_user',
          'id' => '',
          'label' => epl__( 'API Login ID' ),
          'help_text' => epl__( 'This number can be obtained by logging into Authorize.net > Account > API Login ID and Transaction Key' ),
          'class' => 'epl_w300',
          'required' => true ), */
        '_epl_pwd' => array(
            'input_type' => 'password',
            'input_name' => '_epl_pwd',
            'id' => '',
            'label' => epl__( 'Merchant Key' ),
            'class' => 'epl_w300',
            'required' => true ),
        /* '_epl_accepted_cards' => array(
          'input_type' => 'checkbox',
          'input_name' => '_epl_accepted_cards[]',
          'label' => epl__( 'Accepted Cards' ),
          'auto_key' => true,
          'options' => array( 'Visa' => 'Visa', 'MasterCard' => 'Master Card', 'Discover' => 'Discover', 'Amex' => 'Amex' ),
          'class' => '' ), */
        '_epl_sandbox' => array(
            'input_type' => 'select',
            'input_name' => '_epl_sandbox',
            'label' => epl__( 'Test Mode?' ),
            'options' => epl_yes_no(),
            'help_text' => epl__( 'If yes, please make sure you use Sandbox credentials above.' ),
            'class' => '' ),
        '_epl_default_selected' => array(
            'input_type' => 'select',
            'input_name' => '_epl_default_selected',
            'label' => epl__( 'Automatically assign to all new events?' ),
            'options' => epl_yes_no(),
            'help_text' => epl__( 'If this option is set to yes, all future events will have this payment profile automatically selected as a payment choice.' ),
            'class' => '' ),
    );

    $epl_fields['_usa_epay_fields'] = apply_filters( 'epl_usa_epay_fields', $epl_fields['_usa_epay_fields'] );


    $epl_fields['_stripe_fields'] = array(
        '_epl_pay_help' => array(
            'input_type' => 'section',
            'label' => epl__( 'Stripe' ),
            'content' => sprintf( epl__( 'Accept Credit and Debit Cards without having the customer leave your website. %sMUST USE SSL FOR PCI COMPLIANCE.
                You can enable SSL from Events Planner > Settings > Registration tab.%s' ), '<br /><span class="epl_font_red"><strong>', '</strong></span>' )
        ),
        '_epl_pay_type' => array(
            'input_type' => 'hidden',
            'input_name' => '_epl_pay_type',
            'default_value' => '_stripe',
        ),
        '_epl_pay_display' => array(
            'input_type' => 'textarea',
            'input_name' => '_epl_pay_display',
            'id' => '',
            'label' => epl__( 'Label' ),
            'help_text' => epl__( 'What the customer will see as an option.  Ex. Credit Card.' ),
            'class' => 'epl_w300',
            'required' => true ),
        '_epl_secret_key' => array(
            'input_type' => 'password',
            'input_name' => '_epl_secret_key',
            'id' => '',
            'label' => epl__( 'Secret Key' ),
            'class' => 'epl_w300',
            'required' => true ),
        '_epl_publishable_key' => array(
            'input_type' => 'password',
            'input_name' => '_epl_publishable_key',
            'id' => '',
            'label' => epl__( 'Publishable Key' ),
            'class' => 'epl_w300',
            'required' => true ),
        '_epl_sandbox' => array(
            'input_type' => 'select',
            'input_name' => '_epl_sandbox',
            'label' => epl__( 'Test Mode?' ),
            'options' => epl_yes_no(),
            'help_text' => epl__( 'If yes, please make sure you use Sandbox credentials above.' ),
            'class' => '' ),
        '_epl_default_selected' => array(
            'input_type' => 'select',
            'input_name' => '_epl_default_selected',
            'label' => epl__( 'Automatically assign to all new events?' ),
            'options' => epl_yes_no(),
            'help_text' => epl__( 'If this option is set to yes, all future events will have this payment profile automatically selected as a payment choice.' ),
            'class' => '' ),
    );

    $epl_fields['_usa_epay_fields'] = apply_filters( 'epl_usa_epay_fields', $epl_fields['_usa_epay_fields'] );

    return $epl_fields;
}


function epl_cc_billing_fields() {
    global $epl_fields;

    $epl_fields['epl_cc_billing_fields'] = array(
        '_epl_cc_first_name' => array(
            'weight' => 5,
            'input_type' => 'text',
            'input_name' => '_epl_cc_first_name',
            'label' => epl__( 'First Name' ),
            'default_value' => epl_get_attendee_form_value( 'ticket_buyer', 'first_name' ),
            'required' => true ),
        '_epl_cc_last_name' => array(
            'weight' => 10,
            'input_type' => 'text',
            'input_name' => '_epl_cc_last_name',
            'label' => epl__( 'Last Name' ),
            'default_value' => epl_get_attendee_form_value( 'ticket_buyer', 'last_name' ),
            'required' => true ),
        '_epl_cc_address' => array(
            'weight' => 15,
            'input_type' => 'text',
            'input_name' => '_epl_cc_address',
            'label' => epl__( 'Address' ),
            'default_value' => epl_get_attendee_form_value( 'ticket_buyer', 'address' ),
            'required' => true ),
        '_epl_cc_city' => array(
            'weight' => 20,
            'input_type' => 'text',
            'input_name' => '_epl_cc_city',
            'label' => epl__( 'City' ),
            'default_value' => epl_get_attendee_form_value( 'ticket_buyer', 'city' ),
            'required' => true ),
        '_epl_cc_state' => array(
            'weight' => 25,
            'input_type' => 'text',
            'input_name' => '_epl_cc_state',
            'label' => epl__( 'State' ),
            'default_value' => epl_get_attendee_form_value( 'ticket_buyer', 'state' ),
            'required' => true ),
        '_epl_cc_zip' => array(
            'weight' => 30,
            'input_type' => 'text',
            'input_name' => '_epl_cc_zip',
            'label' => epl__( 'Zip' ),
            'default_value' => epl_get_attendee_form_value( 'ticket_buyer', 'zip' ),
            'required' => true ),
        '_epl_cc_country' => array(
            'weight' => 35,
            'input_type' => 'select',
            'input_name' => '_epl_cc_country',
            'label' => epl__( 'Country' ),
            'options' => epl_country_codes(),
            'default_value' => 'US',
            'class' => 'epl_70',
            'value' => epl_get_element( '_epl_cc_country', $_POST ),
            'required' => true
        ),
        '_epl_cc_card_type' => array(
            'weight' => 40,
            'input_type' => 'select',
            'input_name' => '_epl_cc_card_type',
            'label' => epl__( 'Card Type' ),
            'options' => array( 'Visa' => 'Visa', 'MasterCard' => 'Master Card', 'Discover' => 'Discover', 'Amex' => 'Amex' ),
            'class' => 'epl_70',
            'value' => epl_get_element( '_epl_cc_exp_month', $_POST ),
            'required' => true
        ),
        '_epl_cc_num' => array(
            'weight' => 45,
            'input_type' => 'text',
            'input_name' => '_epl_cc_num',
            'label' => epl__( 'Card Number' ),
            'required' => true ),
        '_epl_cc_cvv' => array(
            'weight' => 50,
            'input_type' => 'text',
            'input_name' => '_epl_cc_cvv',
            'label' => epl__( 'CVV Code' ),
            'required' => true ),
        '_epl_cc_exp_month' => array(
            'weight' => 55,
            'input_type' => 'select',
            'input_name' => '_epl_cc_exp_month',
            'label' => epl__( 'Expiration Month' ),
            'options' => epl_month_dd(),
            'class' => 'epl_w70',
            'value' => epl_get_element( '_epl_cc_exp_month', $_POST ),
            'required' => true
        ),
        '_epl_cc_exp_year' => array(
            'weight' => 60,
            'input_type' => 'select',
            'input_name' => '_epl_cc_exp_year',
            'label' => epl__( 'Expiration Year' ),
            'options' => epl_make_array( date_i18n( 'Y' ), date_i18n( 'Y' ) + 10 ),
            'class' => 'epl_w70',
            'value' => epl_get_element( '_epl_cc_exp_year', $_POST ),
            'required' => true
        ),
    );

    $epl_fields['epl_cc_billing_fields'] = apply_filters( 'epl_cc_billing_fields', $epl_fields['epl_cc_billing_fields'] );
    uasort( $epl_fields['epl_cc_billing_fields'], 'epl_sort_by_weight' );

    return $epl_fields;
}


function epl_get_api_fields() {
    global $epl_fields;
    $epl_fields['epl_api_option_fields'] = array(
        'epl_api_key' => array(
            'input_type' => 'text',
            'input_name' => 'epl_api_key',
            'label' => epl__( 'Support License Key' ),
            'help_text' => 'The Support License Key will enable the pro version, allow automatic downloads of new versions of the plugin and modules that you have purchased.  The key can be obtained from ' . get_option( 'epl_help_url', true ),
            'class' => 'epl_w400',
            'required' => true ),
        'epl_api_section' => array(
            'input_type' => 'section',
            'label' => '',
            'class' => '',
            'style' => 'font-size:12px',
            'content' => "Custom Tables" .
            "<div style='font-size:12px;'>Version 2.0 introduces custom registration db tables.  They need to be populated with current registration data.  " .
            "WARNING. It is highly encouraged to do a backup of your database, just in case things go wrong (unlikely but we want to be safe).  If you have a large data set (>1000 registrations), this may take up to a few minutes. " .
            epl_anchor( admin_url( '/edit.php?post_type=epl_event&epl_action=populate_db_tables&epl_controller=epl_registration&r=1&epl_download_trigger=1' ), 'Click here', '_self', 'class="button-secondary"' ) . " to populate/repopulate the tables." .
            " Latest run was performed on: " . '<strong>' . (get_option( '_epl_last_table_refresh', 'Never' )) . '</strong>'
        ),
    );

    if ( epl_is_addon_active( '_epl_atp' ) ) {

        $epl_fields['epl_api_option_fields']+= array(
            'epl_atp_section' => array(
                'input_type' => 'section',
                'label' => '',
                'class' => 'epl_font_555 epl_font_bold',
                'content' => epl__( 'Advanced Time/Price Settings' )
            ),
            'epl_atp_enable_date_specific_time' => array(
                'input_type' => 'select',
                'input_name' => 'epl_atp_enable_date_specific_time',
                'options' => epl_yes_no(),
                'default_value' => 10,
                'label' => epl__( 'Enable date specific times?' ),
            //'help_text' => epl__( 'If yes, select a notification list below.' )
            ),
            'epl_atp_enable_date_specific_price' => array(
                'input_type' => 'select',
                'input_name' => 'epl_atp_enable_date_specific_price',
                'options' => epl_yes_no(),
                'default_value' => 10,
                'label' => epl__( 'Enable date specific price?' ),
            //'help_text' => epl__( 'If yes, select a notification list below.' )
            ),
            /* 'epl_atp_enable_time_specific_price' => array(
              'input_type' => 'select',
              'input_name' => 'epl_atp_enable_time_specific_price',
              'options' => epl_yes_no(),
              'default_value' => 10,
              'label' => epl__( 'Enable time specific price?' ) . ' EXPERIMENTAL!!!',
              //'help_text' => epl__( 'If yes, select a notification list below.' )
              ), */
            'epl_atp_enable_price_specific_form' => array(
                'input_type' => 'select',
                'input_name' => 'epl_atp_enable_price_specific_form',
                'options' => epl_yes_no(),
                'default_value' => 10,
                'label' => epl__( 'Enable price specific forms?' ),
            //'help_text' => epl__( 'If yes, select a notification list below.' )
            ),
            'epl_atp_enable_table_price_type' => array(
                'input_type' => 'select',
                'input_name' => 'epl_atp_enable_table_price_type',
                'options' => epl_yes_no(),
                'default_value' => 10,
                'label' => epl__( 'Enable table price type?' ),
            //'help_text' => epl__( 'If yes, select a notification list below.' )
            )
        );
    }


    if ( !epl_is_addon_active( 'ASDFAWEEFADSF' ) )
        return $epl_fields;

    $epl_fields['epl_api_option_fields']+= array(
        'epl_mc_section' => array(
            'input_type' => 'section',
            'label' => '',
            'class' => 'epl_font_555 epl_font_bold',
            'content' => epl__( 'MailChimp Settings.  Please enter your key and hit Save Changes to begin.' )
        ),
        'epl_mc_key' => array(
            'input_type' => 'password',
            'input_name' => 'epl_mc_key',
            'label' => epl__( 'Mailchimp API Key' ),
            'class' => 'epl_w400',
            'required' => true
        )
    );


    if ( epl_get_setting( 'epl_api_option_fields', 'epl_mc_key' ) != '' ) {

        $epl_fields['epl_api_option_fields']+= array(
            'epl_mc_action' => array(
                'input_type' => 'select',
                'input_name' => 'epl_mc_action',
                'options' => array( 0 => epl__( 'No' ), 1 => epl__( 'Enable for all current and future events' ), 2 => epl__( 'I will choose for each event' ) ),
                'help_text' => epl__( 'If you select No, this feature will be diabled at global and event level. If you select the second option, you can still override it for each event.' ),
                'id' => 'epl_mc_default_action',
                'label' => epl__( 'Enable MailChimp Extension' ),
                'help_icon_type' => '-red',
                'default_value' => 1,
                'class' => 'epl_field_type',
                'default_value' => '' ),
            'epl_mc_double_opt_in' => array(
                'input_type' => 'select',
                'input_name' => 'epl_mc_double_opt_in',
                'options' => epl_yes_no(),
                'id' => 'epl_mc_double_opt_in',
                'label' => epl__( 'Double Opt In (global)' ),
                'help_text' => epl__( 'When this option is enabled, MailChimp will send a confirmation email to the user and will only add them to your MailChimp list upon confirmation.' ),
                'default_value' => 0,
            ),
            'epl_mc_send_welcome_email' => array(
                'input_type' => 'select',
                'input_name' => 'epl_mc_send_welcome_email',
                'id' => 'epl_mc_send_welcome_email',
                'label' => epl__( 'Send Welcome Email (global)' ),
                'help_text' => epl__( 'When this option is enabled, users will receive an automatic welcome email from MailChimp upon being added to your MailChimp list.' ),
                'options' => epl_yes_no(),
                'class' => 'epl_field_type',
                'default_value' => '' ),
            '_epl_mc_offer_notification_sign_up' => array(
                'input_type' => 'select',
                'input_name' => '_epl_mc_offer_notification_sign_up',
                'options' => array( 1 => epl__( 'Ask' ), 3 => epl__( 'Automatically add' ) ),
                'default_value' => 1,
                'label' => epl__( 'User Signup Method (default)' ),
            ),
            'epl_mc_permission_label' => array(
                'input_type' => 'text',
                'input_name' => 'epl_mc_permission_label',
                'label' => epl__( 'Permission Label' ),
                'help_text' => epl__( 'This label will be displayed during the registration process if you choose to ask the users for permission to sign them up.' ),
                'class' => 'epl_w400',
                'default_value' => epl__( 'Subscribe to our newsletter' )
            ),
            '_epl_mc_default_list' => array(
                //'weight' => 27,
                'input_type' => 'select',
                'input_name' => '_epl_mc_default_list',
                'options' => epl_get_mailchimp_lists(),
                'empty_row' => true,
                'default_value' => '',
                'label' => epl__( 'Default Notification List' ),
            )
        );
    }

    if ( epl_um_is_active() ) {

        $epl_fields['epl_api_option_fields']+= array(
            'epl_um_section' => array(
                'input_type' => 'section',
                'label' => '',
                'class' => 'epl_font_555 epl_font_bold',
                'content' => epl__( 'User Manager Settings' ) .
                '<br/><span style="font-size:12px;"> - ' . epl__( "Please note that this feature will only work if the Primary Registrant form is enabled." ) . '</span>'
            ),
            'epl_um_enable_user_regis' => array(
                'input_type' => 'select',
                'input_name' => 'epl_um_enable_user_regis',
                'label' => epl__( 'Enable User Registration?' ),
                'options' => array(
                    0 => epl__( 'No' ),
                    1 => epl__( 'Yes' ),
                //2 => epl__( 'Yes, optional' ),
                ),
                'default_value' => 0
            ),
            'epl_um_user_regis_username' => array(
                'input_type' => 'select',
                'input_name' => 'epl_um_user_regis_username',
                'label' => epl__( 'Username' ),
                'options' => array( 1 => epl__( 'Email' ), 2 => epl__( 'Let user choose' ) ),
                'default_value' => 1
            ),
            'epl_um_user_regis_password' => array(
                'input_type' => 'select',
                'input_name' => 'epl_um_user_regis_password',
                'label' => epl__( 'Password' ),
                'options' => array( 1 => epl__( 'Automatic' ), 2 => epl__( 'Let user choose' ) ),
                'default_value' => 1
            ),
            'epl_um_user_regis_role' => array(
                'input_type' => 'select',
                'input_name' => 'epl_um_user_regis_role',
                'label' => epl__( 'Role to assign' ),
                'options' => epl_get_roles_arr(),
                'default_value' => 'subscriber'
            )
        );
    }

    $epl_fields['epl_api_option_fields']+= array(
        'epl_invoice_section' => array(
            'input_type' => 'section',
            'label' => '',
            'class' => 'epl_font_555 epl_font_bold',
            'content' => epl__( 'Invoice Settings' )
        ),
        'epl_invoice_attach_to_conf' => array(
            'input_type' => 'select',
            'input_name' => 'epl_invoice_attach_to_conf',
            'id' => 'epl_invoice_send_in_conf',
            'label' => epl__( 'Attach invoice to confirmation emails?' ),
            'help_text' => epl__( 'If no, you can still attach invoices to emails that are sent manually.' ),
            'options' => epl_yes_no(),
            'default_value' => 0,
        ),
        'epl_invoice_logo' => array(
            'input_type' => 'text',
            'input_name' => 'epl_invoice_logo',
            'id' => 'epl_invoice_logo',
            'class' => 'epl_w400',
            'label' => epl__( 'Invoice Logo' ),
            'description' => epl_anchor( '#', epl__( 'Select File' ), null, ' id="epl_invoice_logo_select"' )
        ),
        'epl_invoice_display_id' => array(
            'input_type' => 'select',
            'input_name' => 'epl_invoice_display_id',
            'id' => 'epl_invoice_display_id',
            'label' => epl__( 'Invoice Id' ),
            'options' => array( 1 => epl__( 'Registration Post ID' ), 2 => epl__( 'Registration Title' ), 3 => epl__( 'Incremental ID' ) ),
        ),
        'epl_invoice_due' => array(
            'input_type' => 'text',
            'input_name' => 'epl_invoice_due',
            'class' => 'epl_w40',
            'label' => epl__( 'Invoice Payment Due' ),
            'help_text' => epl__( 'Number of days after issue date when the payment is due.' )
        ),
        'epl_invoice_company_info' => array(
            'input_type' => 'textarea',
            'input_name' => 'epl_invoice_company_info',
            'id' => 'epl_invoice_company_info',
            'label' => epl__( 'Invoice Company Information' ),
            'help_text' => epl__( 'This will be posted on the top right cornerof the invoice.  Can use HTML' ),
            'style' => 'width:300px;height:120px;',
            '_save_func' => 'wp_kses_post'
        ),
        'epl_invoice_instruction' => array(
            'input_type' => 'textarea',
            'input_name' => 'epl_invoice_instruction',
            'id' => 'epl_invoice_instruction',
            'label' => epl__( 'Instructions' ),
            'help_text' => epl__( 'This will be posted on the bottom of the invoice.' ),
            'style' => 'width:90%;height:120px;',
            '_save_func' => 'wp_kses_post'
        ),
    );

    return $epl_fields;
}


function epl_instructor_fields() {
    global $epl_fields;
    $epl_fields['epl_instructor_fields'] = array(
        '_epl_instructor_name' => array(
            'input_type' => 'text',
            'input_name' => '_epl_instructor_name',
            'label' => epl__( 'Instructor Name' ),
            'class' => 'epl_w300 req' )
    );
}


function epl_country_codes() {

    $country_codes = array(
        "AF" => "Afghanistan",
        "AX" => "Aland Islands",
        "AL" => "Albania",
        "DZ" => "Algeria",
        "AS" => "American Samoa",
        "AD" => "Andorra",
        "AO" => "Angola",
        "AI" => "Anguilla",
        "AQ" => "Antarctica",
        "AG" => "Antigua And Barbuda",
        "AR" => "Argentina",
        "AM" => "Armenia",
        "AW" => "Aruba",
        "AU" => "Australia",
        "AT" => "Austria",
        "AZ" => "Azerbaijan",
        "BS" => "Bahamas",
        "BH" => "Bahrain",
        "BD" => "Bangladesh",
        "BB" => "Barbados",
        "BY" => "Belarus",
        "BE" => "Belgium",
        "BZ" => "Belize",
        "BJ" => "Benin",
        "BM" => "Bermuda",
        "BT" => "Bhutan",
        "BO" => "Bolivia",
        "BA" => "Bosnia And Herzegovina",
        "BW" => "Botswana",
        "BV" => "Bouvet Island",
        "BR" => "Brazil",
        "IO" => "British Indian Ocean Territory",
        "BN" => "Brunei Darussalam",
        "BG" => "Bulgaria",
        "BF" => "Burkina Faso",
        "BI" => "Burundi",
        "KH" => "Cambodia",
        "CM" => "Cameroon",
        "CA" => "Canada",
        "CV" => "Cape Verde",
        "KY" => "Cayman Islands",
        "CF" => "Central African Republic",
        "TD" => "Chad",
        "CL" => "Chile",
        "CN" => "China",
        "CX" => "Christmas Island",
        "CC" => "Cocos (Keeling) Islands",
        "CO" => "Colombia",
        "KM" => "Comoros",
        "CG" => "Congo",
        "CD" => "Congo, The Democratic Republic Of The",
        "CK" => "Cook Islands",
        "CR" => "Costa Rica",
        "CI" => "Cote D'Ivoire",
        "HR" => "Croatia",
        "CU" => "Cuba",
        "CY" => "Cyprus",
        "CZ" => "Czech Republic",
        "DK" => "Denmark",
        "DJ" => "Djibouti",
        "DM" => "Dominica",
        "DO" => "Dominican Republic",
        "EC" => "Ecuador",
        "EG" => "Egypt",
        "SV" => "El Salvador",
        "GQ" => "Equatorial Guinea",
        "ER" => "Eritrea",
        "EE" => "Estonia",
        "ET" => "Ethiopia",
        "FK" => "Falkland Islands (Malvinas)",
        "FO" => "Faroe Islands",
        "FJ" => "Fiji",
        "FI" => "Finland",
        "FR" => "France",
        "GF" => "French Guiana",
        "PF" => "French Polynesia",
        "TF" => "French Southern Territories",
        "GA" => "Gabon",
        "GM" => "Gambia",
        "GE" => "Georgia",
        "DE" => "Germany",
        "GH" => "Ghana",
        "GI" => "Gibraltar",
        "GR" => "Greece",
        "GL" => "Greenland",
        "GD" => "Grenada",
        "GP" => "Guadeloupe",
        "GU" => "Guam",
        "GT" => "Guatemala",
        "GG" => "Guernsey",
        "GN" => "Guinea",
        "GW" => "Guinea-Bissau",
        "GY" => "Guyana",
        "HT" => "Haiti",
        "HM" => "Heard Island And Mcdonald Islands",
        "VA" => "Holy See (Vatican City State)",
        "HN" => "Honduras",
        "HK" => "Hong Kong",
        "HU" => "Hungary",
        "IS" => "Iceland",
        "IN" => "India",
        "ID" => "Indonesia",
        "IR" => "Iran, Islamic Republic Of",
        "IQ" => "Iraq",
        "IE" => "Ireland",
        "IM" => "Isle Of Man",
        "IL" => "Israel",
        "IT" => "Italy",
        "JM" => "Jamaica",
        "JP" => "Japan",
        "JE" => "Jersey",
        "JO" => "Jordan",
        "KZ" => "Kazakhstan",
        "KE" => "Kenya",
        "KI" => "Kiribati",
        "KP" => "Korea, Democratic People'S Republic Of",
        "KR" => "Korea, Republic Of",
        "KW" => "Kuwait",
        "KG" => "Kyrgyzstan",
        "LA" => "Lao People'S Democratic Republic",
        "LV" => "Latvia",
        "LB" => "Lebanon",
        "LS" => "Lesotho",
        "LR" => "Liberia",
        "LY" => "Libyan Arab Jamahiriya",
        "LI" => "Liechtenstein",
        "LT" => "Lithuania",
        "LU" => "Luxembourg",
        "MO" => "Macao",
        "MK" => "Macedonia, The Former Yugoslav Republic Of",
        "MG" => "Madagascar",
        "MW" => "Malawi",
        "MY" => "Malaysia",
        "MV" => "Maldives",
        "ML" => "Mali",
        "MT" => "Malta",
        "MH" => "Marshall Islands",
        "MQ" => "Martinique",
        "MR" => "Mauritania",
        "MU" => "Mauritius",
        "YT" => "Mayotte",
        "MX" => "Mexico",
        "FM" => "Micronesia, Federated States Of",
        "MD" => "Moldova, Republic Of",
        "MC" => "Monaco",
        "MN" => "Mongolia",
        "MS" => "Montserrat",
        "MA" => "Morocco",
        "MZ" => "Mozambique",
        "MM" => "Myanmar",
        "NA" => "Namibia",
        "NR" => "Nauru",
        "NP" => "Nepal",
        "NL" => "Netherlands",
        "AN" => "Netherlands Antilles",
        "NC" => "New Caledonia",
        "NZ" => "New Zealand",
        "NI" => "Nicaragua",
        "NE" => "Niger",
        "NG" => "Nigeria",
        "NU" => "Niue",
        "NF" => "Norfolk Island",
        "MP" => "Northern Mariana Islands",
        "NO" => "Norway",
        "OM" => "Oman",
        "PK" => "Pakistan",
        "PW" => "Palau",
        "PS" => "Palestinian Territory, Occupied",
        "PA" => "Panama",
        "PG" => "Papua New Guinea",
        "PY" => "Paraguay",
        "PE" => "Peru",
        "PH" => "Philippines",
        "PN" => "Pitcairn",
        "PL" => "Poland",
        "PT" => "Portugal",
        "PR" => "Puerto Rico",
        "QA" => "Qatar",
        "RE" => "Reunion",
        "RO" => "Romania",
        "RU" => "Russian Federation",
        "RW" => "Rwanda",
        "SH" => "Saint Helena",
        "KN" => "Saint Kitts And Nevis",
        "LC" => "Saint Lucia",
        "PM" => "Saint Pierre And Miquelon",
        "VC" => "Saint Vincent And The Grenadines",
        "WS" => "Samoa",
        "SM" => "San Marino",
        "ST" => "Sao Tome And Principe",
        "SA" => "Saudi Arabia",
        "SN" => "Senegal",
        "CS" => "Serbia And Montenegro",
        "SC" => "Seychelles",
        "SL" => "Sierra Leone",
        "SG" => "Singapore",
        "SK" => "Slovakia",
        "SI" => "Slovenia",
        "SB" => "Solomon Islands",
        "SO" => "Somalia",
        "ZA" => "South Africa",
        "GS" => "South Georgia And The South Sandwich Islands",
        "ES" => "Spain",
        "LK" => "Sri Lanka",
        "SD" => "Sudan",
        "SR" => "Suriname",
        "SJ" => "Svalbard And Jan Mayen",
        "SZ" => "Swaziland",
        "SE" => "Sweden",
        "CH" => "Switzerland",
        "SY" => "Syrian Arab Republic",
        "TW" => "Taiwan, Province Of China",
        "TJ" => "Tajikistan",
        "TZ" => "Tanzania, United Republic Of",
        "TH" => "Thailand",
        "TL" => "Timor-Leste",
        "TG" => "Togo",
        "TK" => "Tokelau",
        "TO" => "Tonga",
        "TT" => "Trinidad And Tobago",
        "TN" => "Tunisia",
        "TR" => "Turkey",
        "TM" => "Turkmenistan",
        "TC" => "Turks And Caicos Islands",
        "TV" => "Tuvalu",
        "UG" => "Uganda",
        "UA" => "Ukraine",
        "AE" => "United Arab Emirates",
        "GB" => "United Kingdom",
        "US" => "United States",
        "UM" => "United States Minor Outlying Islands",
        "UY" => "Uruguay",
        "UZ" => "Uzbekistan",
        "VU" => "Vanuatu",
        "VE" => "Venezuela",
        "VN" => "Viet Nam",
        "VG" => "Virgin Islands, British",
        "VI" => "Virgin Islands, U.S.",
        "WF" => "Wallis And Futuna",
        "EH" => "Western Sahara",
        "YE" => "Yemen",
        "ZM" => "Zambia",
        "ZW" => "Zimbabwe"
    );

    return apply_filters( 'epl_country_codes', $country_codes );
}

if ( epl_is_addon_active( 'DASFERWEQREWE' ) ) {
    add_filter( 'epl_fields_fields', '_epl_fields_fields' );
    add_filter( 'epl_construct_form_default_value', '_epl_construct_form_default_value' );
}


function _epl_fields_fields( $epl_fields ) {

    $epl_fields['wp_user_map'] = array(
        'input_type' => 'text',
        'input_name' => 'wp_user_map',
        'id' => 'wp_user_map',
        'label' => epl__( 'User Table Slug' ),
        'help_text' => epl__( 'You can map this field to a user table meta_key.' ),
        'style' => '',
        'class' => 'epl_field_type',
        'default_value' => '' );
    $epl_fields['epl_membership_plugin'] = array(
        'input_type' => 'select',
        'input_name' => 'epl_membership_plugin',
        'id' => 'epl_membership_plugin',
        'label' => epl__( 'Membership Plugin' ),
        'help_text' => epl__( 'Since different membership plugins use different meta_key names, this will let events planner know which ones to look for. For now, <u>only text fields can be used</u>' ),
        'options' => array( 's2' => 'S2 Member' ),
        'class' => 'epl_field_type',
        'default_value' => '' );

    return $epl_fields;
}


function epl_option_fields( $epl_fields ) {

    if ( !epl_sc_is_enabled() )
        return $epl_fields;

    $epl_fields['epl_exclude_from_sc'] = array(
        'weight' => 15,
        'input_type' => 'select',
        'input_name' => '_epl_exclude_from_sc',
        'options' => epl_yes_no(),
        'default_value' => 0,
        'label' => epl__( 'Exclude from cart process?' ),
        'help_text' => epl__( 'If this is set to yes and this event is added to the cart, all other events will be removed from the cart.' )
    );
    return $epl_fields;
}


function epl_registration_options_fields() {

    global $epl_fields;

    $epl_fields['epl_registration_options']['_epl_default_event_regis_flow'] = $epl_fields['epl_other_settings_fields']['_epl_event_regis_flow'];
    $epl_fields['epl_registration_options']['_epl_default_event_regis_flow']['input_name'] = '_epl_default_event_regis_flow';
    $epl_fields['epl_registration_options']['_epl_default_event_regis_flow']['weight'] = 40;
    $epl_fields['epl_registration_options']['_epl_display_discount_input_field'] = array(
        'weight' => 100,
        'input_type' => 'select',
        'input_name' => '_epl_display_discount_input_field',
        'label' => epl__( 'Display the discount code input box?' ),
        'options' => epl_yes_no(),
        'default_value' => 10
    );
    $epl_fields['epl_registration_options']['_epl_discount_input_label'] = array(
        'weight' => 105,
        'input_type' => 'text',
        'input_name' => '_epl_discount_input_label',
        'label' => epl__( 'Discount input label' ),
        'default_value' => epl__( 'Discount Code' )
    );

    $epl_fields['epl_registration_options']['_epl_enable_PP_parallel_pay'] = array(
        'weight' => 110,
        'input_type' => 'select',
        'input_name' => '_epl_enable_PP_parallel_pay',
        'label' => epl__( 'Enable PayPal Parallel Payments?' ),
        'options' => epl_yes_no(),
        'default_value' => 0
    );

    $epl_fields['epl_registration_options']['_epl_enable_pay_now_link'] = array(
        'weight' => 64,
        'input_type' => 'select',
        'input_name' => '_epl_enable_pay_now_link',
        'label' => epl__( "Enable 'Pay Now' link on confirmation page?" ),
        'options' => epl_yes_no(),
        'default_value' => 10,
        'help_text' => epl__( 'This will allow the registrants to make an online payment at a later date if there is a balance due.' ),
    );

    return $epl_fields['epl_registration_options'];
}

add_action( 'epl_settings_fields', 'epl_sc_option_fields' );


function epl_sc_option_fields() {
    global $epl_fields;

    $epl_fields['epl_sc_options'] = array(
        'epl_sc_enable' => array(
            'weight' => 5,
            'input_type' => 'select',
            'input_name' => 'epl_sc_enable',
            'options' => array( 0 => epl__( 'No' ),
                10 => epl__( 'Yes. Pop-up based' ),
                15 => epl__( 'Yes. Button based' )
            ),
            'default_value' => 0,
            'label' => epl__( 'Enable Shopping Cart' ),
            'description' => epl__( 'When enabled, the user will have the ability to register for more than one event.  Pop-up based carts displays a modal box for the user to select the event.  Button based will update the button and mark it as added to the cart.  Users will have the option to update their selections in the cart regardless of which method you use.' ) ),
        'epl_sc_footer_subtotal' => array(
            'weight' => 6,
            'input_type' => 'select',
            'input_name' => 'epl_sc_footer_subtotal',
            'options' => epl_yes_no(),
            'default_value' => 10,
            'label' => epl__( 'Enable Footer Totals' ),
            'description' => epl__( 'When events are added to the cart, a small box in the footer will show what the user has selected and give him the option to delete items from the cart.' ) ),
        'epl_sc_forms_to_use' => array(
            'weight' => 7,
            'input_type' => 'select',
            'input_name' => 'epl_sc_forms_to_use',
            'options' => array( 1 => epl__( 'Use the forms below' ), 5 => epl__( 'Use the forms indicated for each event' ) ),
            'default_value' => 0,
            'label' => epl__( 'Registrations forms to use' ),
            'description' => epl__( 'When the users go through the registration and they have more than one event in the cart, the system can display the individual forms associated with each event or it can display one set of forms that you choose below. ' ) ),
        'epl_sc_primary_regis_forms' => array(
            'weight' => 10,
            'input_type' => 'table_checkbox',
            'input_name' => 'epl_sc_primary_regis_forms[]',
            'label' => epl__( 'Ticket buyer form' ),
            'options' => epl_get_list_of_available_forms(),
            'auto_key' => true,
            'class' => '',
            'description' => epl__( 'Optional.  This is the form that you will use for collecting information from the person that is doing the registration.' )
        ),
        'epl_sc_addit_regis_forms' => array(
            'weight' => 15,
            'input_type' => 'table_checkbox',
            'input_name' => 'epl_sc_addit_regis_forms[]',
            'label' => epl__( 'Forms for all attendees' ),
            'options' => epl_get_list_of_available_forms(),
            'auto_key' => true,
            'class' => '',
            'description' => '<img src="' . EPL_FULL_URL . 'images/error.png" /> ' . epl__( 'This information will be collected from all the attendees and will be recorded for each event.  If you do not need to collect individual information and only need the quantity, do not select any of these forms.' )
        ),
        'epl_sc_form_to_form_copy' => array(
            'weight' => 18,
            'input_type' => 'select',
            'input_name' => 'epl_sc_form_to_form_copy',
            'label' => epl__( 'Enable form to form copy?' ),
            'options' => epl_get_list_of_available_forms(),
            'options' => epl_yes_no(),
            'default_value' => 0,
            'class' => '',
        ),
        'epl_sc_payment_choices' => array(
            'weight' => 20,
            'input_type' => 'table_checkbox',
            'input_name' => 'epl_sc_payment_choices[]',
            'options' => get_list_of_payment_profiles(),
            'label' => epl__( 'Payment Choices' ),
            'empty_options_msg' => epl__( 'No Payment Profiles. Please go to Events Planner > Payment Profiles.' ),
            'class' => '',
            'default_value' => get_list_of_default_payment_profiles(),
            'auto_key' => true
        ),
        'epl_sc_default_selected_payment' => array(
            'weight' => 25,
            'input_type' => 'select',
            'input_name' => 'epl_sc_default_selected_payment',
            'options' => get_list_of_payment_profiles(),
            'label' => epl__( 'Default Selected Payment' ),
            'help_text' => epl__( 'This determines which payment method is automatically selected when the user visits the regsitration cart for the first time.' ),
            'empty_options_msg' => epl__( 'No Payment Profiles. Please go to Events Planner > Payment Profiles.' ),
            'class' => 'req',
        ),
        'epl_sc_notification' => array(
            'weight' => 30,
            'input_type' => 'select',
            'input_name' => 'epl_sc_notification',
            'options' => get_list_of_available_notifications(),
            'label' => epl__( 'Confirmation email' ),
            'help_text' => epl__( 'Email that is sent to the users after they register.' ),
            'empty_row' => true,
            'empty_options_msg' => epl__( 'No email messages found.  Please go to Events Planner > Notification Manager to create notifications.' )
        ),
    );

    $epl_fields['epl_sc_options'] = apply_filters( 'epl_shopping_cart_options_fields', $epl_fields['epl_sc_options'] );
    uasort( $epl_fields['epl_sc_options'], 'epl_sort_by_weight' );
}


function epl_add_user_fields() {
    global $epl_fields;

    $epl_fields['epl_add_user_fields'] = array(
        'user_login' => array(
            'weight' => 5,
            'input_type' => 'text',
            'input_name' => 'user_login',
            'id' => 'user_login',
            'label' => epl__( 'Username' ),
            'default_value' => '',
            'required' => (epl_get_setting( 'epl_api_option_fields', 'epl_um_enable_user_regis' ) == 1) ),
        'user_pass' => array(
            'weight' => 10,
            'input_type' => 'password',
            'input_name' => 'user_pass',
            'id' => 'user_pass',
            'label' => epl__( 'Password' ),
            'default_value' => '',
            'required' => (epl_get_setting( 'epl_api_option_fields', 'epl_um_enable_user_regis' ) == 1) ),
        'user_pass_confirm' => array(
            'weight' => 15,
            'input_type' => 'password',
            'input_name' => 'user_pass_confirm',
            'id' => 'user_pass_confirm',
            'label' => epl__( 'Repeat Password' ),
            'default_value' => '',
            'required' => (epl_get_setting( 'epl_api_option_fields', 'epl_um_enable_user_regis' ) == 1) ),
    );

    $epl_fields['epl_add_user_fields'] = apply_filters( 'epl_add_user_fields', $epl_fields['epl_add_user_fields'] );
    uasort( $epl_fields['epl_add_user_fields'], 'epl_sort_by_weight' );

    return $epl_fields;
}


function epl_global_discount_type( $epl_fields ) {
    $epl_fields['_epl_global_discount_type']['options']['social'] = epl__( 'Social' );
    return $epl_fields;
}


function _epl_event_options_fields( $epl_fields ) {

    return $epl_fields;
}

if ( epl_is_addon_active( '_epl_atp' ) ) {

    add_filter( 'epl_price_fields', 'epl_price_fields', 1 );
    add_filter( 'epl_time_fields', 'epl_time_fields', 1 );
    add_filter( 'epl_price_option_fields', 'epl_price_option_fields', 1 );
    add_filter( 'epl_time_option_fields', 'epl_time_option_fields', 1 );
    add_filter( 'epl_global_discount_type', 'epl_global_discount_type', 1 );
}
?>
