<?php

if ( !defined( 'ABSPATH' ) )
    exit( 'No direct script access allowed' );
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
 * CodeIgniter Calendar Class
 *
 * This class enables the creation of calendars
 *
 * @package		CodeIgniter
 * @subpackage	Libraries
 * @category	Libraries
 * @author		ExpressionEngine Dev Team
 * @link		http://codeigniter.com/user_guide/libraries/calendar.html
 */
global $prefs;

$prefs['template'] = <<<T

   {table_open}<table border="1" cellpadding="2" cellspacing="2" class="epl_small_calendar">{/table_open}

   {heading_row_start}<tr>{/heading_row_start}

   {heading_previous_cell}<th><a href="{previous_url} class="epl_next_prev_link">&lt;&lt;</a></th>{/heading_previous_cell}
   {heading_title_cell}<th colspan="{colspan}">{heading}</th>{/heading_title_cell}
   {heading_next_cell}<th><a href="{next_url}  class="epl_next_prev_link">&gt;&gt;</a></th>{/heading_next_cell}

   {heading_row_end}</tr>{/heading_row_end}

   {week_row_start}<tr>{/week_row_start}
   {week_day_cell}<td  class="day_header">{week_day}</td>{/week_day_cell}
   {week_row_end}</tr>{/week_row_end}

   {cal_row_start}<tr>{/cal_row_start}
   {cal_cell_start}<td>{/cal_cell_start}

   {cal_cell_content}{day}{/cal_cell_content}
   {cal_cell_content_today}<div class="highlight">{day}</div>{/cal_cell_content_today}

   {cal_cell_no_content}{day}{/cal_cell_no_content}
   {cal_cell_no_content_today}<div class="highlight">{day}</div>{/cal_cell_no_content_today}

   {cal_cell_blank}&nbsp;{/cal_cell_blank}

   {cal_cell_end}</td>{/cal_cell_end}
   {cal_row_end}</tr>{/cal_row_end}

   {table_close}</table>{/table_close}
T;


$prefs = array(
    'show_next_prev' => TRUE,
    'next_prev_url' => '?epl_action=widget_cal_link'
);

$wd = array( 'sunday', 'monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday' );

$prefs['start_day'] = $wd[get_option( 'start_of_week' )];


$prefs['template'] = '
    {table_open}<div class="epl_calendar_wrapper"><table id="" class="epl_small_calendar">{/table_open}
    {caption_open}<caption>{/caption_open}
    {caption_close}</caption>{/caption_close}

   {heading_row_start}<tr scope="col">{/heading_row_start}

   {heading_previous_cell}<th><a href="{previous_url}" class="epl_next_prev_link">&lt;&lt;</a></th>{/heading_previous_cell}
   {heading_title_cell}<th colspan="{colspan}">{heading}</th>{/heading_title_cell}
   {heading_next_cell}<th><a href="{next_url}"  class="epl_next_prev_link">&gt;&gt;</a></th>{/heading_next_cell}

   {heading_row_end}</tr>{/heading_row_end}
   
    {week_day_cell}<th class="day_header">{week_day}</th>{/week_day_cell}
    {cal_cell_start_today}<td id ="today">{/cal_cell_start_today}
    {cal_cell_content}<div class="widget_has_data day_listing day_listing_content" id="epl_{content}">{day}</div>{/cal_cell_content}
    {cal_cell_content_today}<div class="today widget_has_data day_listing" id="epl_{content}">{day}</div>{/cal_cell_content_today}
    
    {cal_cell_no_content}<div class= "day_listing ">{day}</div>{/cal_cell_no_content}
    {cal_cell_no_content_today}<div class="">{day}</div>{/cal_cell_no_content_today}
    {table_close}</table></div>{/table_close}
';

class EPL_Calendar {

    private $lang;
    private $local_time;
    public $template = '';
    private $start_day = 'sunday';
    private $month_type = 'long';
    private $day_type = 'abr';
    public $show_next_prev = FALSE;
    public $next_prev_url = '';


    /**
     * Constructor
     *
     * Loads the calendar language file and sets the default time reference
     */
    public function __construct( $config = array( ) ) {

        //epl_log('debug', "<pre>" . print_r($config, true ) . "</pre>" );

        if ( empty( $config ) ) {
            global $prefs;
            $config = $prefs;
        }
        $this->local_time = time();

        if ( count( $config ) > 0 ) {
            $this->initialize( $config );
        }

        //log_message('debug', "Calendar Class Initialized");
    }

    // --------------------------------------------------------------------


    /**
     * Initialize the user preferences
     *
     * Accepts an associative array as input, containing display preferences
     *
     * @access	public
     * @param	array	config preferences
     * @return	void
     */
    function initialize( $config = array( ) ) {
        foreach ( $config as $key => $val ) {
            if ( isset( $this->$key ) ) {
                $this->$key = $val;
            }
        }

        return $this;
    }

    // --------------------------------------------------------------------


    /**
     * Generate the calendar
     *
     * @access	public
     * @param	integer	the year
     * @param	integer	the month
     * @param	array	the data to be shown in the calendar cells
     * @return	string
     */
    function generate( $year = '', $month = '', $data = array( ) ) {
        // Set and validate the supplied month/year
        if ( $year == '' )
            $year = date_i18n( "Y", $this->local_time );

        if ( $month == '' )
            $month = date_i18n( "m", $this->local_time );

        if ( strlen( $year ) == 1 )
            $year = '200' . $year;

        if ( strlen( $year ) == 2 )
            $year = '20' . $year;

        if ( strlen( $month ) == 1 )
            $month = '0' . $month;

        $adjusted_date = $this->adjust_date( $month, $year );

        $month = $adjusted_date['month'];
        $year = $adjusted_date['year'];

        // Determine the total days in the month
        $total_days = $this->get_total_days( $month, $year );

        // Set the starting day of the week
        $start_days = array( 'sunday' => 0, 'monday' => 1, 'tuesday' => 2, 'wednesday' => 3, 'thursday' => 4, 'friday' => 5, 'saturday' => 6 );
        $start_day = (!isset( $start_days[$this->start_day] )) ? 0 : $start_days[$this->start_day];

        // Set the starting day number
        $local_date = mktime( 12, 0, 0, $month, 1, $year );
        $date = getdate( $local_date );
        $day = $start_day + 1 - $date["wday"];

        while ( $day > 1 ) {
            $day -= 7;
        }

        // Set the current month/year/day
        // We use this to determine the "today" date
        $cur_year = date_i18n( "Y", $this->local_time );
        $cur_month = date_i18n( "m", $this->local_time );
        $cur_day = date_i18n( "j", $this->local_time );

        $is_current_month = ($cur_year == $year AND $cur_month == $month) ? TRUE : FALSE;

        // Generate the template data array
        $this->parse_template();

        // Begin building the calendar output
        $out = $this->temp['table_open'];
        //$out .= "\n";
        //$out .= $this->temp['caption_open'];
        //$out .= "\n";
        //$out .= $this->get_month_name( $month ) . "&nbsp;" . $year;
        //$out .= $this->temp['caption_close'];
        //$out .= "\n";


        $out .= "\n";
        $out .= $this->temp['heading_row_start'];
        $out .= "\n";

        // "previous" month link
        if ( $this->show_next_prev == TRUE ) {
            // Add a trailing slash to the  URL if needed
            //$this->next_prev_url = preg_replace( "/(.+?)\/*$/", "\\1/", $this->next_prev_url );

            $adjusted_date = $this->adjust_date( $month - 1, $year );
            //$out .= str_replace( '{previous_url}', $this->next_prev_url . $adjusted_date['year'] . '/' . $adjusted_date['month'], $this->temp['heading_previous_cell'] );
            $out .= str_replace( '{previous_url}', add_query_arg( array( 'c_year' => $adjusted_date['year'], 'c_month' => $adjusted_date['month'] ), $this->next_prev_url ), $this->temp['heading_previous_cell'] );
            $out .= "\n";
        }

        // Heading containing the month/year
        $colspan = ($this->show_next_prev == TRUE) ? 5 : 7;

        $this->temp['heading_title_cell'] = str_replace( '{colspan}', $colspan, $this->temp['heading_title_cell'] );
        $this->temp['heading_title_cell'] = str_replace( '{heading}', $this->get_month_name( $month ) . "&nbsp;" . $year, $this->temp['heading_title_cell'] );
        $this->temp['heading_title_cell'] = str_replace( '{heading}', '', $this->temp['heading_title_cell'] );

        $out .= $this->temp['heading_title_cell'];
        $out .= "\n";

        // "next" month link
        if ( $this->show_next_prev == TRUE ) {
            $adjusted_date = $this->adjust_date( $month + 1, $year );
            //$out .= str_replace( '{next_url}', $this->next_prev_url . $adjusted_date['year'] . '/' . $adjusted_date['month'], $this->temp['heading_next_cell'] );
            $out .= str_replace( '{next_url}', add_query_arg( array( 'c_year' => $adjusted_date['year'], 'c_month' => $adjusted_date['month'] ), $this->next_prev_url ), $this->temp['heading_next_cell'] );
        }

        $out .= "\n";
        $out .= $this->temp['heading_row_end'];
        $out .= "\n";

        // Write the cells containing the days of the week
        $out .= "\n";
        $out .= $this->temp['week_row_start'];
        $out .= "\n";

        $day_names = $this->get_day_names();

        for ( $i = 0; $i < 7; $i++ ) {
            $out .= str_replace( '{week_day}', $day_names[($start_day + $i) % 7], $this->temp['week_day_cell'] );
        }

        $out .= "\n";
        $out .= $this->temp['week_row_end'];
        $out .= "\n";

        // Build the main body of the calendar
        while ( $day <= $total_days ) {
            $out .= "\n";
            $out .= $this->temp['cal_row_start'];
            $out .= "\n";

            //cal_cell_start
            for ( $i = 0; $i < 7; $i++ ) {



                $_wrapper_td = ( $is_current_month == TRUE AND $day == $cur_day) ? $this->temp['cal_cell_start_today'] : $this->temp['cal_cell_start'];

                if ( $day > 0 AND $day <= $total_days ) {
                   
                    if ( isset( $data[$day] ) ) {
                        // Cells with content

                        $temp = ($is_current_month == TRUE AND $day == $cur_day) ? $this->temp['cal_cell_content_today'] : $this->temp['cal_cell_content'];

                        $cell_content = str_replace( '{day}', $day, str_replace( '{content}', (isset( $data[$day]['date'] )) ? $data[$day]['date'] : '', $temp ) );
                    }
                    else {
                        // Cells with no content
                        $temp = ($is_current_month == TRUE AND $day == $cur_day) ? $this->temp['cal_cell_no_content_today'] : $this->temp['cal_cell_no_content'];

                        $cell_content = str_replace( '{day}', $day, $temp );
                    }

                    $r = (isset( $data[$day]['term_list'] )) ? $data[$day]['term_list'] : '';
                   
                    $r = apply_filters( 'epl_cal_date_term_list', $r );
                    
                    $r = trim( str_replace( ",", ' ', $r) );

                    $_wrapper_td = str_replace( '{cal_cell_start}', $r, $_wrapper_td );


                    $out .= $_wrapper_td;
                    $out .= $cell_content;
                }
                else {
                    // Blank cells
                    $_wrapper_td = str_replace( '{cal_cell_start}', '', $_wrapper_td );
                    $out .= $_wrapper_td;
                    $out .= $this->temp['cal_cell_blank'];
                }


                $out .= ( $is_current_month == TRUE AND $day == $cur_day) ? $this->temp['cal_cell_end_today'] : $this->temp['cal_cell_end'];
                $day++;
            }

            $out .= "\n";
            $out .= $this->temp['cal_row_end'];
            $out .= "\n";
        }

        $out .= "\n";
        $out .= $this->temp['table_close'];
        //epl_log( "debug", "<pre>" . print_r($out, true ) . "</pre>" );

        return $out;
    }

    // --------------------------------------------------------------------


    /**
     * Get Month Name
     *
     * Generates a textual month name based on the numeric
     * month provided.
     *
     * @access	public
     * @param	integer	the month
     * @return	string
     */
    function get_month_name( $month ) {
        if ( $this->month_type == 'short' ) {
            $month_names = array( 
                '01' => epl__('Jan'),
                '02' => epl__('Feb'),
                '03' => epl__('Mar'),
                '04' => epl__('Apr'),
                '05' => epl__('May'),
                '06' => epl__('Jun'),
                '07' => epl__('Jul'),
                '08' => epl__('Aug'),
                '09' => epl__('Sep'),
                '10' => epl__('Oct'),
                '11' => epl__('Nov'),
                '12' => epl__('Dec') );
        }
        else {
            $month_names = array( 
                '01' => epl__('January'),
                '02' => epl__('February'),
                '03' => epl__('March'),
                '04' => epl__('April'),
                '05' => epl__('May'),
                '06' => epl__('June'),
                '07' => epl__('July'),
                '08' => epl__('August'),
                '09' => epl__('September'),
                '10' => epl__('October'),
                '11' => epl__('November'),
                '12' => epl__('December') );
        }

        $month = $month_names[$month];


        return ucfirst( epl__( $month ) );
    }

    // --------------------------------------------------------------------


    /**
     * Get Day Names
     *
     * Returns an array of day names (Sunday, Monday, etc.) based
     * on the type.  Options: long, short, abrev
     *
     * @access	public
     * @param	string
     * @return	array
     */
   function get_day_names( $day_type = '' ) {
        if ( $day_type != '' )
            $this->day_type = $day_type;

        if ( $this->day_type == 'long' ) {
            $day_names = array( epl__('Sunday'), epl__('Monday'), epl__('Tuesday'), epl__('Wednesday'), epl__('Thursday'), epl__('Friday'), epl__('Saturday') );
        }
        elseif ( $this->day_type == 'short' ) {
            $day_names = array( 'sun', 'mon', 'tue', 'wed', 'thu', 'fri', 'sat' );
        }
        else {
            $day_names = array( epl__('Su'), epl__('Mo'), epl__('Tu'), epl__('We'), epl__('Th'), epl__('Fr'), epl__('Sa') );
        }

        $days = array( );
        foreach ( $day_names as $val ) {
            //$days[] = ($this->CI->lang->line('cal_'.$val) === FALSE) ? ucfirst($val) : $this->CI->lang->line('cal_'.$val);
            $days[] = ucfirst( $val );
        }

        return $days;
    }

    // --------------------------------------------------------------------


    /**
     * Adjust Date
     *
     * This function makes sure that we have a valid month/year.
     * For example, if you submit 13 as the month, the year will
     * increment and the month will become January.
     *
     * @access	public
     * @param	integer	the month
     * @param	integer	the year
     * @return	array
     */
    function adjust_date( $month, $year ) {
        $date = array( );

        $date['month'] = $month;
        $date['year'] = $year;

        while ( $date['month'] > 12 ) {
            $date['month'] -= 12;
            $date['year']++;
        }

        while ( $date['month'] <= 0 ) {
            $date['month'] += 12;
            $date['year']--;
        }

        if ( strlen( $date['month'] ) == 1 ) {
            $date['month'] = '0' . $date['month'];
        }

        return $date;
    }

    // --------------------------------------------------------------------


    /**
     * Total days in a given month
     *
     * @access	public
     * @param	integer	the month
     * @param	integer	the year
     * @return	integer
     */
    function get_total_days( $month, $year ) {
        $days_in_month = array( 31, 28, 31, 30, 31, 30, 31, 31, 30, 31, 30, 31 );

        if ( $month < 1 OR $month > 12 ) {
            return 0;
        }

        // Is the year a leap year?
        if ( $month == 2 ) {
            if ( $year % 400 == 0 OR ($year % 4 == 0 AND $year % 100 != 0) ) {
                return 29;
            }
        }

        return $days_in_month[$month - 1];
    }

    // --------------------------------------------------------------------


    /**
     * Set Default Template Data
     *
     * This is used in the event that the user has not created their own template
     *
     * @access	public
     * @return array
     */
    function default_template() {
        return array(
            'table_open' => '<table border="0" cellpadding="4" cellspacing="0">',
            'caption_open' => '<caption>',
            'caption_close' => '</caption>',
            'heading_row_start' => '<tr>',
            'heading_previous_cell' => '<th><a href="{previous_url}">&lt;&lt;</a></th>',
            'heading_title_cell' => '<th colspan="{colspan}">{heading}</th>',
            'heading_next_cell' => '<th><a href="{next_url}">&gt;&gt;</a></th>',
            'heading_row_end' => '</tr>',
            'week_row_start' => '<tr>',
            'week_day_cell' => '<td>{week_day}</td>',
            'week_row_end' => '</tr>',
            'cal_row_start' => '<tr>',
            'cal_cell_start' => '<td>',
            'cal_cell_start_today' => '<td>',
            'cal_cell_content' => '<a href="{content}">{day}</a>',
            'cal_cell_content_today' => '<a href="{content}"><strong>{day}</strong></a>',
            'cal_cell_no_content' => '{day}',
            'cal_cell_no_content_today' => '<strong>{day}</strong>',
            'cal_cell_blank' => '&nbsp;',
            'cal_cell_end' => '</td>',
            'cal_cell_end_today' => '</td>',
            'cal_row_end' => '</tr>',
            'table_close' => '</table>'
        );
    }

    // --------------------------------------------------------------------


    /**
     * Parse Template
     *
     * Harvests the data within the template {pseudo-variables}
     * used to display the calendar
     *
     * @access	public
     * @return	void
     */
    function parse_template() {
        $this->temp = $this->default_template();

        if ( $this->template == '' ) {
            return;
        }

        $today = array( 'cal_cell_start_today', 'cal_cell_content_today', 'cal_cell_no_content_today', 'cal_cell_end_today' );

        foreach ( array( 'table_open', 'caption_open', 'caption_close', 'table_close', 'heading_row_start', 'heading_previous_cell', 'heading_title_cell', 'heading_next_cell', 'heading_row_end', 'week_row_start', 'week_day_cell', 'week_row_end', 'cal_row_start', 'cal_cell_start', 'cal_cell_content', 'cal_cell_no_content', 'cal_cell_blank', 'cal_cell_end', 'cal_row_end', 'cal_cell_start_today', 'cal_cell_content_today', 'cal_cell_no_content_today', 'cal_cell_end_today' ) as $val ) {
            if ( preg_match( "/\{" . $val . "\}(.*?)\{\/" . $val . "\}/si", $this->template, $match ) ) {
                $this->temp[$val] = $match['1'];
            }
            else {
                if ( in_array( $val, $today, TRUE ) ) {
                    $this->temp[$val] = $this->temp[str_replace( '_today', '', $val )];
                }
            }
        }
    }

}

// END CI_Calendar class

/* End of file Calendar.php */
/* Location: ./system/libraries/Calendar.php */