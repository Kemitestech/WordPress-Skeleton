<?php

class EPL_report_model extends EPL_Model {

    private static $instance;
    public $WHERE = '';


    function __construct() {
        parent::__construct();

        global $event_details;
        self::$instance = $this;

        $this->ecm = $this->epl->load_model( 'epl-common-model' );
        $this->edbm = $this->epl->load_model( 'epl-db-model' );
        $this->erm = $this->epl->load_model( 'epl-registration-model' );

        $this->delim = EPL_db_model::get_instance()->delim;
    }


    public static function get_instance() {
        if ( !self::$instance ) {

            self::$instance = new EPL_report_model;
        }

        return self::$instance;
    }


    function daterange_filter( $t = 'r' ) {
        $where = '';

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
            $this->WHERE .= $where = " AND $t.regis_date >= '" . date_i18n( "Y-m-d 00:00:01", strtotime( $dates[0] ) ) . "'";
        }
        if ( !empty( $dates[1] ) ) {
            $this->WHERE .= $where .= "  AND $t.regis_date <= '" . date_i18n( "Y-m-d 11:59:59", strtotime( $dates[1] ) ) . "'";
        }

        return $where;
    }


    function query_where( $table = '', $limit_to = array() ) {
        global $wpdb;

        $this->WHERE = '';
        $table = epl_suffix( '.', $table );

        $this->daterange_filter();

        $filter_params = array(
            'regis_id',
            'event_id',
            'date_id',
            'time_id',
            'price_id',
            'status'
        );

        if ( !epl_is_empty_array( $limit_to ) )
            $filter_params = $limit_to;

        foreach ( $filter_params as $prefix => $filter ) {
            if ( isset( $_REQUEST[$filter] ) && $_REQUEST[$filter] != '' )
                $this->WHERE .= " AND {$table}{$filter} = '" . esc_sql( $_REQUEST[$filter] ) . "'";
        }
    }


    function query_group_by() {
        
    }


    function query_order_by() {
        
    }


    function transactions( $where = null ) {
        global $wpdb;
        //$wpdb->flush();

        $limit = array(
            'rd' => 'event_id',
            'r' => 'status'
        );

        $this->query_where( '', $limit );

        if ( $where )
            $this->WHERE = $where;

        $q = $wpdb->get_results(
                "SELECT
          r.*, rp.*,
          (select sum(srp.payment_amount) from {$wpdb->epl_regis_payment} srp where srp.regis_id = r.regis_id) as payment_amount
          FROM {$wpdb->epl_registration} r
          INNER JOIN {$wpdb->epl_regis_payment} rp
          ON r.regis_id = rp.regis_id
          INNER JOIN {$wpdb->epl_regis_data} rd
          ON r.regis_id = rd.regis_id
          WHERE 1=1 
          {$this->WHERE} 
          GROUP BY r.regis_id
          ORDER BY  r.regis_date ASC
          "
        );
        //echo "<pre class='prettyprint'>" . __LINE__ . "> " . print_r( $wpdb->last_query, true ) . "</pre>";
        return $q;
    }


    function cash_totals( $where = null ) {

        global $wpdb;
        //$wpdb->flush();
        $wpdb->show_errors();

        $limit = array(
            'rd' => 'event_id',
            'r' => 'status'
        );

        $this->query_where( '', $limit );

        if ( $where )
            $this->WHERE = $where;

        $q = $wpdb->get_results(
                "SELECT 
                    MONTH(r.regis_date) as m,
                    DAY(r.regis_date) as d, 
                    YEAR(r.regis_date) as y,
                    SUM(CASE WHEN r.status = 1 THEN 1 ELSE 0 END) AS cnt_incomplete,
                    SUM(CASE WHEN r.status = 2 THEN 1 ELSE 0 END) AS cnt_pending,
                    SUM(CASE WHEN r.status = 5 THEN 1 ELSE 0 END) AS cnt_complete,

                    SUM(CASE WHEN r.status = 1 THEN r.grand_total ELSE 0 END) AS sum_incomplete,
                    SUM(CASE WHEN r.status = 2 THEN r.grand_total ELSE 0 END) AS sum_pending,
                    SUM(CASE WHEN r.status = 5 THEN rp.payment_amount ELSE 0 END) AS sum_complete 

          FROM {$wpdb->epl_registration} r
          INNER JOIN {$wpdb->epl_regis_payment} rp
          ON r.regis_id = rp.regis_id
          WHERE 1=1 
          {$this->WHERE} 
          GROUP BY  m,d,y
          ORDER BY  r.regis_date DESC
          "
        );
        //echo "<pre class='prettyprint'>" . __LINE__ . "> " . print_r( $wpdb->last_query, true ) . "</pre>";
        //echo "<pre class='prettyprint'>" . __LINE__ . "> " . print_r( $wpdb->print_error(), true ) . "</pre>";
        return $q;

        /*
          SELECT productId, Size,
          SUM(CASE WHEN storeID IN ('BCN', 'BCN2') THEN stock ELSE 0 END) AS stockBCN,
          SUM(CASE WHEN storeID = 'MAD' THEN stock ELSE 0 END) AS stockMAD
          FROM stocks
          GROUP BY productId, Size
         * 
         */
    }


    function get_the_email_addresses( $filter = null, $regis_id = null ) {

        global $wpdb, $event_details;

        $this->WHERE = ' AND r.status between 2 and 5 ';


        $arr = array(
            'regis_id' => (!is_null( $regis_id ) ? $regis_id : epl_get_element( 'post_ID', $_REQUEST, false )),
            'event_id' => $_REQUEST['event_id'],
            'date_id' => epl_get_element( 'date_id', $_REQUEST, null ),
            'time_id' => epl_get_element( 'time_id', $_REQUEST, null ),
            'names_only' => epl_get_element( 'names_only', $_REQUEST, 0 ),
        );

        setup_event_details( $arr['event_id'] );

        $data['pack_regis'] = (epl_get_element( '_epl_pack_regis', $event_details, 0 ) == 10);
        $_filter = array();
        if ( $data['pack_regis'] ) {
            //find all the registrations for this event
            //for each one, find out if package
            //for each one that is pack, find the pack * X days
            //contstruct array


            $event_date_keys = array_keys( $event_details['_epl_start_date'] );

            $pack_counts = epl_get_element( '_epl_price_pack_size', $event_details, array() );


            $registrations = $wpdb->get_results( "SELECT * FROM {$wpdb->epl_regis_data} WHERE event_id = " . intval( $event_details['ID'] ) );

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

                        if ( ( $arr['date_id'] && !isset( $attendance_dates[$regis->id][$arr['date_id']] )) || ($arr['time_id'] && $arr['time_id'] != $regis->time_id )
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

        $_filter = implode( ',', $_filter );

        if ( $_filter != '' ) {

            $_filter = " AND rd.id IN ({$_filter})";
        }

        if ( ($regis_id = epl_get_element( 'post_ID', $_REQUEST, $arr['regis_id'] )) !== false ){
            
            $this->WHERE = ' AND r.status between 1 and 5 ';
            $this->WHERE .= ' AND r.regis_id =' . $wpdb->escape( $regis_id );

        }
        if ( ($_event_id = epl_get_element( 'event_id', $_REQUEST, false )) !== false )
            $this->WHERE .= ' AND rd.event_id =' . $wpdb->escape( $_event_id );

        if ( !$_filter && ($_date_id = epl_get_element( 'date_id', $_REQUEST, false )) !== false )
            $this->WHERE .= ' AND rd.date_id ="' . $wpdb->escape( $_date_id ) . '"';

        if ( !$_filter && ($_time_id = epl_get_element( 'time_id', $_REQUEST, false )) !== false )
            $this->WHERE .= ' AND rd.time_id ="' . $wpdb->escape( $_time_id ) . '"';

        $form_to_look_at = 'AND rf.form_no = 0';

        $who_to_email = epl_get_setting( 'epl_registration_options', 'epl_send_customer_confirm_message_to', 1 );

        if ( $who_to_email == 2 || !epl_has_primary_forms() )
            $form_to_look_at = '';

        $q = $wpdb->get_results(
                "SELECT
          r.regis_id, r.status, r.regis_key, rd.event_id, rf.field_id, rf.form_no, rf.input_slug, rf.value
         
          FROM {$wpdb->epl_regis_data} rd
          INNER JOIN {$wpdb->epl_registration} r
          ON r.regis_id = rd.regis_id
          INNER JOIN {$wpdb->epl_regis_form_data} rf
          ON (rd.regis_id = rf.regis_id AND rd.event_id = rf.event_id)
          WHERE 1=1 
          {$this->WHERE} {$form_to_look_at} {$_filter}
          GROUP BY rf.value
          ORDER BY  r.regis_date, rd.id
          "
        );


        $email_list = array(
            'raw_list' => array(),
            'display_list' => '' //array()
        );
        $num_emails = 0;
        if ( $q ) {
            foreach ( $q as $row ) {
                setup_event_details( $row->event_id );

                //Redundant.  But on line 280, from the registration list individual email, event id is not available.
                if ( $who_to_email == 1 && (epl_has_primary_forms() &&  $row->form_no > 0) )
                    continue;
                
                $d = $this->get_form_data_array( $row->input_slug, $row->value );

                if ( $d['email'] == '' )
                    continue;
                //if ( !is_array( $email_list['display_list'][$row->regis_id] ) )
                //  $email_list['display_list'][$row->regis_id] = array();

                $this->epl_table->add_row(
                        '<input type="checkbox" id="" name="to_emails[' . $row->regis_id . '][]" class="regis_status_' . $row->status .'" style="" value="'.$d['email'] . '" checked="checked">', $d['email'], $d['first_name'] . ' ' . $d['last_name'], epl_anchor( admin_url( 'post.php?post=' . $row->regis_id . '&action=edit' ), $row->regis_key ), get_the_regis_status( $row->status )
                );
                $num_emails++;
                //$email_list['display_list'][$row->regis_id] = $d['email'] . epl_prefix( ', ', $d['first_name'] . ' ' . $d['last_name'] ) . ', ' . epl_anchor( admin_url( 'post.php?post=' . $row->regis_id . '&action=edit' ), $row->regis_key ) . ', ' . get_the_regis_status( $row->status );
                $email_list['raw_list'][] = $d['email'];
            }
        }

        $tmpl = array( 'table_open' => '<table cellpadding="0" cellspacing="0" class="epl_email_to_list" style="">' );

        $this->epl_table->set_template( $tmpl );
        
        $email_list['display_list'] = $this->epl_table->generate();
        $email_list['display_list'] = $this->epl->load_view( 'admin/registration/regis-email-form-regis-list', $email_list, true );
        
        $email_list['num_emails'] = $num_emails;
        // echo "<pre class='prettyprint'>" . __LINE__ . "> " . basename(__FILE__) . " > " . print_r($email_list['display_list'], true) . "</pre>";
        return $email_list;
    }


    function get_form_data_array( $field_ids = array(), $values = array() ) {


        if ( strpos( $field_ids, EPL_PLUGIN_DB_DELIM ) ) {
            $fields = explode( EPL_PLUGIN_DB_DELIM, $field_ids );
            $values = explode( EPL_PLUGIN_DB_DELIM, $values );
        }
        else {
            $fields = array( $field_ids );
            $values = array( $values );
        }


        return array_combine( $fields, $values );
    }


    function invoice( $regis_id = null ) {

        global $wpdb, $event_details;

        $this->WHERE = '';

        $arr = array(
            'regis_id' => $_REQUEST['post_ID'],
            'event_id' => $_REQUEST['event_id'],
            'date_id' => epl_get_element( 'date_id', $_REQUEST, null ),
            'time_id' => epl_get_element( 'time_id', $_REQUEST, null ),
            'names_only' => epl_get_element( 'names_only', $_REQUEST, 0 ),
        );


        if ( $regis_id || ($regis_id = epl_get_element( 'regis_id', $_REQUEST, false )) !== false )
            $this->WHERE = ' AND r.regis_id =' . $wpdb->escape( $regis_id );

        $data['registration'] = $wpdb->get_results(
                "SELECT * 
                    FROM {$wpdb->epl_registration} r
                    LEFT JOIN {$wpdb->epl_regis_payment} rp
                    ON r.regis_id = rp.regis_id
                    INNER JOIN {$wpdb->epl_regis_form_data} rf
                    ON r.regis_id = rf.regis_id
                    WHERE 1=1 AND rf.form_no = 0
                    {$this->WHERE} 
                    GROUP BY r.regis_id
                    "
        );


        //redundant, rd...           
        if ( $regis_id )
            $this->WHERE = ' AND rd.regis_id =' . $wpdb->escape( $regis_id );

        $data['regis_data'] = $wpdb->get_results(
                "SELECT
                    rd.*, sum(rd.quantity) as total_qty, 
                    re.*
                    FROM {$wpdb->epl_regis_data} rd
                    INNER JOIN {$wpdb->epl_regis_events} re
                    ON (rd.regis_id = re.regis_id AND rd.event_id = re.event_id)
                    WHERE 1=1
                    {$this->WHERE} 
                    GROUP BY rd.event_id, rd.date_id, rd.time_id, rd.price_id
                    ORDER BY rd.id
                    "
        );

        $data['invoice_settings'] = get_option( 'epl_api_option_fields' );

        $data['content'] = $this->epl->load_view( 'admin/reports/regis-invoice-1', $data, true );
        return $this->epl->load_view( 'admin/generic-page-wrapper', $data, true );
    }


    function attendee_form_data( $filter = null, $event_id = null ) {

        global $wpdb, $event_details;
        $event_id = $event_id ? $event_id : epl_get_element( 'event_id', $_REQUEST, null );
        $filter = (is_array( $filter ) ? null : $filter);

        if ( ($_event_id = $event_id) !== null )
            $this->WHERE .= ' AND rd.event_id =' . $wpdb->escape( $_event_id );

        if ( !$filter && ($_date_id = epl_get_element( 'date_id', $_REQUEST, false )) !== false )
            $this->WHERE .= ' AND rd.date_id ="' . $wpdb->escape( $_date_id ) . '"';

        if ( !$filter && ($_time_id = epl_get_element( 'time_id', $_REQUEST, false )) !== false )
            $this->WHERE .= ' AND rd.time_id ="' . $wpdb->escape( $_time_id ) . '"';

        $status_filter = " AND (r.status >= 2 AND r.status <= 5)";
        
        if(($status = epl_get_element( 'status', $_REQUEST, false )) !== false)
                $status_filter = " AND (r.status = $status)";
        
        setup_event_details( $event_id );
        $q = $wpdb->get_results(
                "SELECT
          r.*,
          rd.id as rd_id, rd.event_id,rd.date_id,rd.time_id,rd.price_id,rd.price,rd.quantity,rd.total_quantity,
          sum(rp.payment_amount) as payment_amount,rp.payment_method_id,
          re.grand_total as event_total,
          re.num_dates
          FROM {$wpdb->epl_regis_data} rd
          INNER JOIN {$wpdb->epl_registration} r
          ON r.regis_id = rd.regis_id
          INNER JOIN {$wpdb->epl_regis_events} re
          ON (r.regis_id = re.regis_id AND rd.event_id = re.event_id)
          LEFT JOIN {$wpdb->epl_regis_payment} rp
          ON r.regis_id = rp.regis_id
          WHERE 1=1 
          {$status_filter}
          {$this->WHERE} {$filter}
          GROUP BY rd.id
          ORDER BY  r.regis_date, rd.id
          "
        );


        //echo "<pre class='prettyprint'>" . __LINE__ . "> " . print_r( $wpdb->last_query, true ) . "</pre>";
        return $q;
    }


    function get_all_data( $filter = null, $limit = null ) {

        global $wpdb, $event_details;
        //$wpdb->flush();
        $filter = (is_array( $filter ) ? null : $filter);
        if ( is_null( $limit ) ) {
            $limit = array(
                'rd' => 'event_id',
                'r' => 'status'
            );
        }

        $this->query_where( 'rd', $limit );
        //setup_event_details( $_event_id );
        $q = $wpdb->get_results(
                "SELECT
          r.*,
          rd.id as rd_id,rd.event_id,rd.date_id,rd.time_id,rd.price_id,rd.price,rd.quantity,
          sum(rp.payment_amount) as payment_amount,
          re.grand_total as event_total
          FROM {$wpdb->epl_regis_data} rd
          INNER JOIN {$wpdb->epl_registration} r
          ON r.regis_id = rd.regis_id
          INNER JOIN {$wpdb->epl_regis_events} re
          ON (r.regis_id = re.regis_id AND rd.event_id = re.event_id)
          LEFT JOIN {$wpdb->epl_regis_payment} rp
          ON r.regis_id = rp.regis_id
          WHERE 1=1 
          {$this->WHERE} {$filter}
          GROUP BY rd.id
          ORDER BY  r.regis_date,rd.id
          "
        );
        //echo "<pre class='prettyprint'>" . __LINE__ . "> " . print_r( $wpdb->last_query, true ) . "</pre>";

        return $q;
    }


    function get_all_data_2( $filter = null ) {

        global $wpdb, $event_details;

        $filter = (is_array( $filter ) ? null : $filter);

        if ( ($_event_id = epl_get_element( 'event_id', $_REQUEST, false )) !== false )
            $this->WHERE .= ' AND rd.event_id =' . $wpdb->escape( $_event_id );

        if ( !$filter && ($_date_id = epl_get_element( 'date_id', $_REQUEST, false )) !== false )
            $this->WHERE .= ' AND rd.date_id ="' . $wpdb->escape( $_date_id ) . '"';

        if ( !$filter && ($_time_id = epl_get_element( 'time_id', $_REQUEST, false )) !== false )
            $this->WHERE .= ' AND rd.time_id ="' . $wpdb->escape( $_time_id ) . '"';

        $this->WHERE .= ' AND (r.status >= 2 AND r.status <= 5)';

        //setup_event_details( $_event_id );
        $q = $wpdb->get_results(
                "SELECT
          r.*,
          rd.id as rd_id,rd.event_id,rd.date_id,rd.time_id,rd.price_id,rd.price,rd.quantity,rd.total_quantity,
          sum(rp.payment_amount) as payment_amount,rp.payment_method_id,
          re.grand_total as event_total
          FROM {$wpdb->epl_regis_data} rd
          INNER JOIN {$wpdb->epl_registration} r
          ON r.regis_id = rd.regis_id
          INNER JOIN {$wpdb->epl_regis_events} re
          ON (r.regis_id = re.regis_id AND rd.event_id = re.event_id)
          LEFT JOIN {$wpdb->epl_regis_payment} rp
          ON r.regis_id = rp.regis_id
          WHERE 1=1 
          {$this->WHERE} {$filter}
          GROUP BY rd.id
          ORDER BY  r.regis_date, rd.id
          "
        );
        //echo "<pre class='prettyprint'>" . __LINE__ . "> " . print_r( $wpdb->last_query, true ) . "</pre>";

        return $q;
    }


    function get_regis_forms( $event_id = null, $scope = '' ) {
        global $event_details;

        $primary_form = $this->erm->get_registration_forms(
                array(
                    'scope' => 'ticket_buyer',
                    'event_id' => $event_id,
                    'process' => 'non_esc',
                    'forms' => '_epl_primary_regis_forms',
                    'price_name' => '',
                    'date_display' => '',
                    'price_id' => null,
                    'return' => true ) );
        $sc = (epl_sc_is_enabled() && epl_get_setting( 'epl_sc_options', 'epl_sc_forms_to_use' ) == 1 );
        $_att = array(
            'scope' => 'regis_forms',
            'event_id' => $event_id,
            'process' => $sc ? 'esc' : 'non_esc',
            'forms' => ($sc ? 'epl_sc_addit_regis_forms' : '_epl_addit_regis_forms'),
            'attendee_qty' => 1,
            'price_id' => null,
            'date_display' => '',
            'return' => true,
        );

        $att_form = $this->erm->get_registration_forms( $_att );

        $_other_forms = array();

        $price_forms = epl_get_element( '_epl_price_forms', $event_details, array() );

        if ( !epl_is_empty_array( $price_forms ) ) {

            foreach ( $price_forms as $forms ) {
                $_other_forms += $forms;
            }
            $forms_to_display = $this->ecm->get_list_of_available_forms();
            $_other_forms = array_intersect_key( $forms_to_display, $_other_forms );
        }

        $discount_forms = epl_get_element( '_epl_discount_forms', $event_details, array() );

        if ( !epl_is_empty_array( $discount_forms ) ) {

            foreach ( $discount_forms as $forms ) {
                $_other_forms += $forms;
            }
            $forms_to_display = $this->ecm->get_list_of_available_forms();
            $_other_forms = array_intersect_key( $forms_to_display, $_other_forms );
        }

        if ( $scope == 'primary_form' )
            return $primary_form;

        if ( $scope == 'att_form' && !empty( $att_form ) )
            return $att_form;

        if ( $scope == 'other' && !empty( $_other_forms ) )
            return $_other_forms;

        if ( !$primary_form )
            $primary_form = array();

        if ( !epl_is_empty_array( $att_form ) )
            $primary_form += $att_form;

        if ( !epl_is_empty_array( $_other_forms ) )
            $primary_form += $_other_forms;


        return $primary_form;
    }


    function get_form_fields( $forms = null, $scope = '' ) {

        if ( is_null( $forms ) )
            $forms = $this->get_regis_forms( $_REQUEST['event_id'], $scope );

        $r = array();

        foreach ( $forms as $k => $atts ) {

            $r = array_merge( $r, $atts['epl_form_fields'] );
        }

        return $r;
    }


    function get_form_data( $regis_id = null, $event_id = null, $form_no = null ) {
        global $wpdb;
        $wpdb->flush();
        $WHERE = "";
        if ( $regis_id )
            $WHERE .= " AND f.regis_id = " . $wpdb->escape( $regis_id );
        if ( $event_id )
            $WHERE .= " AND f.event_id = " . $wpdb->escape( $event_id );
        if ( !is_null( $form_no ) )
            $WHERE .= " AND f.form_no = " . $wpdb->escape( $form_no );

        $q = $wpdb->get_results(
                "SELECT * FROM {$wpdb->epl_regis_form_data} f 
                   WHERE 1 = 1
                        {$WHERE}
                   ORDER BY f.id
            "
        );
        //echo "<pre class='prettyprint'>" . __LINE__ . "> " . print_r( $wpdb->last_query, true ) . "</pre>";
        return $q;
    }


    function get_the_regis_events() {
        global $regis_details;

        return ( array ) $regis_details['__epl'][$this->regis_id]['_events'];
    }


    function get_pack_count_filter() {
        
    }


    function get_attendee_counts( $event_id = null, $refresh = false, $all_statuses = false ) {
        global $event_details, $wpdb, $current_att_count;

        if ( !$refresh && isset( $event_details['_epl_att_counts'] ) ) {
            $current_att_count = $event_details['_epl_att_counts'];
            return $current_att_count;
        }

        $WHERE = '';

        $event_id = is_null( $event_id ) ? epl_get_element( 'ID', $event_details, null ) : $event_id;

        $dates = $event_details['_epl_start_date'];

        if ( epl_is_empty_array( $dates ) )
            return null;

        $event_date_keys = array_keys( $dates );

        $times = $event_details['_epl_start_time'];
        $pack_regis = (epl_get_element( '_epl_pack_regis', $event_details, 0 ) == 10);
        $date_specifc_time = epl_get_element( '_epl_date_specific_time', $event_details, false );

        if ( !is_null( $event_id ) )
            $WHERE .= " AND rd.event_id = {$event_id}";

        if ( !$all_statuses )
            $WHERE .= " AND (r.status >= 2 AND r.status <= 5) ";

        $registrations = $wpdb->get_results( "SELECT rd.* , r.*
        FROM {$wpdb->epl_regis_data} rd 
        INNER JOIN {$wpdb->epl_registration} r
            ON r.regis_id = rd.regis_id
        WHERE 1=1
             {$WHERE}
             " );


        $current_att_count = array();
        $number_of_time_slots = count( $event_details['_epl_start_time'] );

        foreach ( $registrations as $regis ) {
            
            $qty = epl_get_element_m( $regis->price_id, '_epl_price_offset_count', $event_details, 1 );
            $offset_another_key = epl_get_element_m( $regis->price_id, '_epl_price_to_offset', $event_details );
            $offset_another = ($qty > 0 && $offset_another_key);
            $qty = ($qty == 0 ? 1 : $qty);
            $price_type = epl_get_element_m($regis->price_id, '_epl_price_type', $event_details, 'att');
                    
            
            if ( !$pack_regis ) {

                if ( $offset_another ) {
                    $current_att_count["_total_{$price_type}_{$event_id}_price_{$regis->date_id}_{$regis->time_id}_" . $offset_another_key] = $qty;
                }

                if ( !isset( $current_att_count["_total_{$price_type}_{$event_id}_date_{$regis->date_id}"] ) ) {
                    $current_att_count["_total_{$price_type}_{$event_id}_date_{$regis->date_id}"] = $qty;
                    $current_att_count["_total_{$price_type}_{$event_id}_time_{$regis->date_id}_{$regis->time_id}"] = $qty;
                    $current_att_count["_total_{$price_type}_{$event_id}_price_{$regis->date_id}_{$regis->time_id}_{$regis->price_id}"] = $qty;
                    //"_total_att_" . $event_details['ID'] . '_price_' . $_date_key . '_' . $_time_key . '_' . $_price_key;
                }
                else {
                    $current_att_count["_total_{$price_type}_{$event_id}_date_{$regis->date_id}"]+=$qty;
                    $current_att_count["_total_{$price_type}_{$event_id}_time_{$regis->date_id}_{$regis->time_id}"]+=$qty;
                    $current_att_count["_total_{$price_type}_{$event_id}_price_{$regis->date_id}_{$regis->time_id}_{$regis->price_id}"] += $qty;
                }
            }
            else {

                //find the weekday and time of registrations
                //foreach date, if weekday is the same, count
                $regis_weekday = date( 'N', $event_details['_epl_start_date'][$regis->date_id] );
                $pack_size = epl_get_element_m( $regis->price_id, '_epl_price_pack_size', $event_details, 1 );
                $start = false;
                foreach ( $event_details['_epl_start_date'] as $date_id => $date ) {

                    if ( !$start && $date_id != $regis->date_id )
                        continue;

                    $start = true;

                    $_weekday = date( 'N', $date );

                    if ( $regis_weekday != $_weekday || $pack_size == 0 )
                        continue;
                    $pack_size--;


                    (isset( $current_att_count["_total_{$price_type}_{$event_id}_date_{$date_id}"] ) ) ? $current_att_count["_total_att_{$event_id}_date_{$date_id}"]+=$qty : $current_att_count["_total_att_{$event_id}_date_{$date_id}"] = $qty;
                    (isset( $current_att_count["_total_{$price_type}_{$event_id}_time_{$date_id}_{$regis->time_id}"] ) ) ? $current_att_count["_total_att_{$event_id}_time_{$date_id}_{$regis->time_id}"]+=$qty : $current_att_count["_total_att_{$event_id}_time_{$date_id}_{$regis->time_id}"] = $qty;
                }


                //$offset = array_search( $regis->date_id, $event_date_keys );
                //$attendance_dates = array_slice( $event_details['_epl_start_date'], $offset, $pack_size );


                /* $pack_size = epl_get_element_m( $regis->price_id, '_epl_price_pack_size', $event_details, 1 );

                  //if ( $pack_size > 1 ) {
                  //find the next $pack_size dates
                  $offset = array_search( $regis->date_id, $event_date_keys );

                  $attendance_dates = array_slice( $event_details['_epl_start_date'], $offset, $pack_size );

                  if ( !epl_is_empty_array( $attendance_dates ) ) {


                  foreach ( $attendance_dates as $att_date_key => $att_date ) {

                  (isset( $current_att_count["_total_att_{$event_id}_date_{$att_date_key}"] ) ) ? $current_att_count["_total_att_{$event_id}_date_{$att_date_key}"]++ : $current_att_count["_total_att_{$event_id}_date_{$att_date_key}"] = 1;
                  (isset( $current_att_count["_total_att_{$event_id}_time_{$att_date_key}_{$regis->time_id}"] ) ) ? $current_att_count["_total_att_{$event_id}_time_{$att_date_key}_{$regis->time_id}"]++ : $current_att_count["_total_att_{$event_id}_time_{$att_date_key}_{$regis->time_id}"] = 1;
                  }
                  }
                  //} */
            }
        }


        //set_transient( '_epl_transient__att_regis_counts__' . $event_id, $current_att_count, 60 * 60 );
        update_post_meta( $event_id, '_epl_att_counts', $current_att_count );
        return $current_att_count;
    }


    function get_event_money_totals( $event_id = null, $all_statuses = false, $refresh = true ) {

        global $event_details, $wpdb;

        if ( !$refresh && false !== ( $money_totals = get_transient( '_epl_transient__regis_money_totals__' . $event_id ) ) )
            return $money_totals;

        $WHERE = '';

        $event_id = is_null( $event_id ) ? epl_get_element( 'ID', $event_details, null ) : $event_id;

        $debug = false;
        // if ( $event_id == 11145 )
        //   $debug = true;

        $dates = $event_details['_epl_start_date'];

        $event_date_keys = array_keys( $dates );
        if ( epl_is_empty_array( $event_date_keys ) )
            return null;
        $times = $event_details['_epl_start_time'];
        $pack_regis = (epl_get_element( '_epl_pack_regis', $event_details, 0 ) == 10);
        $date_specifc_time = epl_get_element( '_epl_date_specific_time', $event_details, false );

        if ( !is_null( $event_id ) )
            $WHERE .= " AND rd.event_id = {$event_id}";

        if ( !$all_statuses )
            $WHERE .= " AND (r.status >= 2 AND r.status <= 5) ";

        $registrations = $wpdb->get_results( "
        SELECT 
          r.*,
          rd.id as rd_id, rd.event_id,rd.date_id,rd.time_id,rd.price_id,rd.price,rd.quantity,rd.total_quantity,
          rp.payment_amount,
          re.grand_total as event_total,
          re.num_dates
        
        FROM {$wpdb->epl_regis_data} rd 
        INNER JOIN {$wpdb->epl_registration} r
            ON r.regis_id = rd.regis_id
        INNER JOIN {$wpdb->epl_regis_events} re
            ON (rd.regis_id = re.regis_id AND rd.event_id = re.event_id)
        LEFT JOIN {$wpdb->epl_regis_payment} rp
            ON r.regis_id = rp.regis_id
        WHERE 1=1
             {$WHERE}
        GROUP BY rd.id
        ORDER BY rd.id" );

        if ( $debug )
            echo "<pre class='prettyprint'>" . __LINE__ . "> " . print_r( $wpdb->last_query, true ) . "</pre>";

        $money_totals = array();

        if ( $debug )
            echo "<pre class='prettyprint'>" . __LINE__ . "> " . print_r( $event_date_keys, true ) . "</pre>";

        foreach ( $registrations as $row ) {
            $true_discount = 0;

            if ( $row->discount_amount > 0 ) {
                $true_discount = $row->discount_amount / $row->total_tickets;
            }

            $donation = 0;

            if ( $row->donation_amount != 0 ) {
                $donation = $row->donation_amount / $row->total_tickets;
            }

            $payment_amount = ($row->payment_amount == 0) ? 0 : ($row->payment_amount > $row->event_total && $row->num_events > 1) ? $row->event_total : $row->payment_amount;

            $money = $row->price;

            if ( $row->total_quantity > 1 )
                $money = $payment_amount / $row->total_quantity;

            if ( $row->event_total == $payment_amount )
                $money = $row->price;

            if ( $pack_size > 1 ) {

                $money = $payment_amount / $row->total_quantity;
            }
            else {
                if ( $row->num_events > 1 ) {
                    if ( $payment_amount > $row->price )
                        $money = $row->price;
                    elseif ( $row->num_dates > 0 ) {
                        $money = $payment_amount / $row->num_dates / $row->total_quantity;
                    }
                }

                $money -= $true_discount;
                $money += $donation;

                if ( $row->total_quantity >= 1 && $payment_amount < ($row->price * $row->total_quantity * $row->num_dates) ) {
                    $money = $payment_amount / $row->num_dates / $row->total_quantity;
                }

                if ( $row->total_quantity == 1 && $payment_amount < $row->price ) {
                    $money = $payment_amount;
                }
            }

            if ( !$pack_regis ) {
                if ( $debug ) {
                    echo "<pre class='prettyprint'>" . __LINE__ . ">$row->regis_key " . basename( __FILE__ ) . " > " . print_r( $money, true ) . "</pre>";
                }
                $money_totals["_money_total_{$event_id}_date_{$row->date_id}"] += $money;
                $money_totals["_money_total_{$event_id}_time_{$row->date_id}_{$row->time_id}"] += $money;
            }
            else {

                $regis_weekday = date( 'N', $event_details['_epl_start_date'][$row->date_id] );
                $pack_decrement_conter = $pack_size = epl_get_element_m( $row->price_id, '_epl_price_pack_size', $event_details, 1 );
                $start = false;
                foreach ( $event_details['_epl_start_date'] as $date_id => $date ) {

                    //find the first date of the pack
                    if ( !$start && $date_id != $row->date_id )
                        continue;

                    $start = true;

                    $_weekday = date( 'N', $date );

                    if ( $regis_weekday != $_weekday || $pack_decrement_conter == 0 )
                        continue;
                    $pack_decrement_conter--;
                    if ( isset( $money_totals["_money_total_{$event_id}_date_{$date_id}"] ) )
                        $money_totals["_money_total_{$event_id}_date_{$date_id}"] += ($money / $pack_size);
                    else
                        $money_totals["_money_total_{$event_id}_date_{$date_id}"] = ($money / $pack_size);

                    if ( isset( $money_totals["_money_total_{$event_id}_time_{$date_id}_{$row->time_id}"] ) )
                        $money_totals["_money_total_{$event_id}_time_{$date_id}_{$row->time_id}"]+= ($money / $pack_size);
                    else
                        $money_totals["_money_total_{$event_id}_time_{$date_id}_{$row->time_id}"] = ($money / $pack_size);
                }
            }
        }

        if ( $debug )
            echo "<pre class='prettyprint'>" . __LINE__ . "> " . print_r( $money_totals, true ) . "</pre>";
        set_transient( '_epl_transient__regis_money_totals__' . $event_id, $money_totals, 60 * 60 );

        return $money_totals;
    }

}
