<?php

class EPL_user_model extends EPL_model {

    public $regis_id;
    public $data = null;
    public $mode;
    public $on_admin = false;
    private static $instance;


    function __construct() {
        parent::__construct();

        //resetting the var just in case there is a widget on the left
        global $event_details;
        $event_details = array( );
        $this->user_id = get_current_user_id();
        $this->ecm = $this->epl->load_model( 'epl-common-model' );
        $this->ercm = $this->epl->load_model( 'epl-recurrence-model' );
        $this->eutil = EPL_Util::get_instance();

        $this->mode = 'edit';
        $this->overview_trigger = null;

        self::$instance = $this;
    }


    public static function get_instance() {
        if ( !self::$instance ) {

            self::$instance = new EPL_user_model;
        }

        return self::$instance;
    }

    function get_list_of_all_users( $param ) {
        
    }

    function user_list_col_header( $columns ) {
        $columns['registrations'] = epl__( 'Registrations' );
        return $columns;
    }


    function user_list_col_content( $value, $column_name, $user_id ) {
        global $wpdb;
        $user = get_userdata( $user_id );
        if ( 'registrations' == $column_name ) {
            $registrations = $wpdb->get_results( "
                SELECT umeta_id,user_id,meta_key,meta_value
                FROM $wpdb->usermeta
                WHERE meta_key like '_epl_regis_post_id%'
		AND user_id = {$user_id}"
            );
            foreach ( $registrations as $registration ) {
                $r_id = str_replace( '_epl_regis_post_id_', '', $registration->meta_key );
                //$this->ecm->get_registration_details($r_id);
                $regis_data = $this->ecm->setup_regis_details( $r_id, true );
                if ( $regis_data['post_status'] != 'publish' )
                    continue;
                $d = epl_get_element( '__epl', $regis_data );

                $data['regis_post_id'] = $r_id;
                $data['regis_id'] = esc_attr( $regis_data['post_title'] );
                $data['regis_date'] = epl_formatted_date( $regis_data['post_title'] );
                $data['regis_status'] = get_the_regis_status();
                $this->epl->epl_table->add_row( $data );
            }
            return $this->epl->epl_table->generate();
            //return "<pre class='prettyprint'>" . __LINE__ . "> " . print_r( $data, true ) . "</pre>";
        }
        return $value;
    }


    function user_bookings() {

        global $wpdb, $event_details, $epl_fields;


        $event_type = epl_get_element( '_epl_event_type', $event_details );

        $registrations = $this->get_current_registrations( $this->user_id );
        $r = array( );

        //$data['absentees'] = $this->get_user_absentee_data();
        $regis_ids = array( );

        foreach ( $registrations as $registration ) {

            $regis_id = intval( str_replace( '_epl_regis_post_id_', '', $registration->meta_key ) );

            $regis_ids[$regis_id] = $regis_id;
        }

        $regis_ids = implode( ',', $regis_ids );

        if ( !$regis_ids )
            return null;

        $q = $wpdb->get_results(
                "SELECT
          r.*,
          rd.id as rd_id,rd.event_id,rd.date_id,rd.time_id,rd.price_id,rd.price,sum(rd.quantity) as ticket_count,
          rp.payment_amount,
          re.grand_total as event_total
          FROM {$wpdb->epl_regis_data} rd
          INNER JOIN {$wpdb->epl_registration} r
          ON r.regis_id = rd.regis_id
          INNER JOIN {$wpdb->epl_regis_events} re
          ON (r.regis_id = re.regis_id AND rd.event_id = re.event_id)
          LEFT JOIN {$wpdb->epl_regis_payment} rp
          ON r.regis_id = rp.regis_id
          WHERE 1=1 AND 
          r.user_id = {$this->user_id}
          GROUP BY rd.regis_id, rd.event_id, rd.date_id, rd.time_id
          ORDER BY  r.regis_date DESC
          "
        );

        return $q;
    }


    function get_session_attendees() {

        global $event_details, $wpdb;
        $this->erptm = $this->epl->load_model( 'epl-report-model' );
        $filters = array(
            'event_id' => $_REQUEST['event_id'],
            'date_id' => epl_get_element( 'date_id', $_REQUEST, null ),
            'time_id' => epl_get_element( 'time_id', $_REQUEST, null ),
            'names_only' => epl_get_element( 'names_only', $_REQUEST, 1 ),
        );

        setup_event_details( $filters['event_id'] );


        $data['pack_regis'] = (epl_get_element( '_epl_pack_regis', $event_details, 0 ) == 10);
        $_filter = array( );
        if ( $data['pack_regis'] ) {
            //find all the registrations for this event
            //for each one, find out if package
            //for each one that is pack, find the pack * X days
            //contstruct array


            $event_date_keys = array_keys( $event_details['_epl_start_date'] );

            $pack_counts = epl_get_element( '_epl_price_pack_size', $event_details, array( ) );


            $regis_packs = $wpdb->get_results( "SELECT * FROM {$wpdb->epl_regis_data} WHERE event_id = " . intval( $event_details['ID'] ) );
            if ( $regis_packs ) {
                $attendance_dates = array( );
                foreach ( $regis_packs as $reg ) {

                    if ( isset( $pack_counts[$reg->price_id] ) ) {

                        $start_date_id = $reg->price_id;

                        $offset = array_search( $reg->date_id, $event_date_keys );

                        $attendance_dates[$reg->id] = array_slice( $event_details['_epl_start_date'], $offset, $pack_counts[$reg->price_id] );

                        $_filter[$reg->id] = $reg->id;

                        if ( $filters['date_id'] && !isset( $attendance_dates[$reg->id][$filters['date_id']] ) ) {
                            unset( $_filter[$reg->id] );
                            unset( $attendance_dates[$reg->id] );
                        }
                    }
                }
            }
        }


        if ( !epl_is_empty_array( $_filter ) ) {
            $_filter = implode( ',', $_filter );
            $_filter = " AND rd.id IN ({$_filter})";
        }


        if ( $filters['names_only'] == 1 )
            $data['list'] = $this->erptm->attendee_form_data( $_filter );
        else
            $data['list'] = $this->erptm->get_all_data( $_filter );


        $data['fields'] = $this->erptm->get_form_fields();
        $data['attendance_dates'] = $attendance_dates;

        $data['event_title'] = $event_details['post_title'];
        $data['event_date'] = isset( $_REQUEST['date_id'] ) ? epl_formatted_date( epl_get_element_m( $_REQUEST['date_id'], '_epl_start_date', $event_details ), "D, M j" ) : '';
        $data['event_time'] = isset( $_REQUEST['time_id'] ) ? epl_get_element_m( $_REQUEST['time_id'], '_epl_start_time', $event_details ) : '';

        $url = admin_url( "edit.php?post_type=epl_event&epl_action=view_names&table_view=1&epl_controller=epl_report_manager&print=1" );

        $url = add_query_arg( $filters, $url );

        $data['filters'] = $filters;

        $r = $this->epl->load_view( 'user-regis-manager/session-attendees', $data, true );

        return $r;
    }

    function get_event_form_setup( $event_id ) {

        global $event_details;
        setup_event_details( $event_id );
        $attendee_form = false;
        $one_form = false;
        //if sc and sc forms
        if ( version_compare( epl_regis_plugin_version(), '1.4', '>=' ) && epl_sc_is_enabled() && epl_get_setting( 'epl_sc_options', 'epl_sc_forms_to_use' ) == 1 ) {

            $attendee_form = epl_get_setting( 'epl_sc_options', 'epl_sc_addit_regis_forms', false );

            if ( $af )
                $attendee_form = true;

            return $attendee_form;
        }


        $price_form = !epl_is_empty_array( epl_get_element( '_epl_price_forms', $event_details, array( ) ) );

        if ( $price_form ) {
            $attendee_form = true;
            //epl_is_empty_array(epl_get_element('_epl_price_forms_per', $event_details, array()))
        }

        if ( !$attendee_form && !epl_is_empty_array( epl_get_element( '_epl_addit_regis_forms', $event_details, array( ) ) ) )
            $attendee_form = true;

        return $attendee_form;
    }


    function user_schedule_old() {
        global $wpdb, $event_details, $epl_fields;

        if ( !is_user_logged_in() ) {
            return login_with_ajax();
        }

        $event_type = epl_get_element( '_epl_event_type', $event_details );

        $registrations = $this->get_current_registrations( $this->user_id );
        $r = array( );

        $data['absentees'] = $this->get_user_absentee_data();
        //echo "<pre class='prettyprint'>" . __LINE__ . "> " . print_r($registrations, true). "</pre>";
        foreach ( $registrations as $registration ) {


            $r_id = str_replace( '_epl_regis_post_id_', '', $registration->meta_key );

            $regis_data = $this->ecm->setup_regis_details( $r_id );

            if ( epl_is_empty_array( $regis_data ) || !( $d = epl_get_element( '__epl', $regis_data, false )) )
                continue;

            if ( $regis_data['_epl_regis_status'] < 1 )
                continue;

            $data['regis_id'] = esc_attr( $regis_data['post_title'] );
            $data['regis_date'] = epl_formatted_date( $regis_data['post_date'] );

            //echo "<pre class='prettyprint'>" . __LINE__ . "> " . print_r( $regis_data, true ) . "</pre>";

            $_regis_id = $d['_regis_id'];

            $_events = $d[$_regis_id]['_events'];
            $_dates = $d[$_regis_id]['_dates']['_epl_start_date'];


            $event_id = key( $_events );
            $this->ecm->setup_event_details( $event_id );


            $data['event_id'] = $event_id;
            $data['event_title'] = esc_attr( $event_details['post_title'] );

            $data['event_start_date'] = $event_details['_epl_start_date'];
            $data['event_end_date'] = $event_details['_epl_end_date'];

            $data['class_dates'] = $event_details['_epl_class_session_date'];
            $data['class_start_times'] = $event_details['_epl_class_session_start_time'];
            $class_end_times = $event_details['_epl_class_session_end_time'];
            $class_session_name = $event_details['_epl_class_session_name'];
            $class_session_note = $event_details['_epl_class_session_note'];

            $data['fc_event_dates'] = $this->epl->epl_util->get_sess_days_for_fc();
            $data['class_end_times'] = $class_end_times;
            $data['regis_status'] = get_the_regis_status();
            $data['regis_dates_cal'] = $this->eutil->construct_calendar( $this->ercm->make_cal_dates_array( $data['class_dates'] ) );

            $data['list'][$_regis_id] = $this->epl->load_view( 'front/user-regis-manager/user-regis-dates', $data, true );
        }

        return $this->epl->load_view( 'front/user-regis-manager/user-regis-list-page', $data, true );
    }


    function user_check_in_page() {

        $params = array(
            'input_type' => 'select',
            'input_name' => 'event_id',
            'id' => 'class_name_dd',
            'label' => epl__( 'Class' ),
            'options' => $this->ecm->get_all_events(),
            'value' => $this->event_id,
                //'overview' => $this->edit_mode,
        );

        $data['event_dd'] = $this->epl_util->create_element( $params );

        return $this->epl->load_view( 'front/user-regis-manager/user-check-in-page', $data, true );
    }


    function user_check_in_table() {
        $event_id = epl_get_element( 'event_id', $_REQUEST );
        $data['absentees'] = $this->get_user_absentee_data();
        $data['registrations'] = $this->get_current_registrations( null, $event_id );

        return $this->epl->load_view( 'front/user-regis-manager/user-check-in-table', $data, true );
    }


    function get_current_registrations( $user_id = null, $event_id = null ) {
        global $wpdb;
        $user_id_filter = (!is_null( $user_id )) ? "AND user_id = {$user_id}" : '';
        $event_id_filter = (!is_null( $event_id )) ? "AND meta_value = {$event_id}" : '';
        if ( $event_id )
            $this->ecm->setup_event_details( $event_id );

        $r = $wpdb->get_results( "
                SELECT umeta_id,user_id,meta_key,meta_value
                FROM $wpdb->usermeta
                WHERE meta_key LIKE '_epl_regis_post_id%' 
		{$user_id_filter} {$event_id_filter} "
        );

        return $r;
    }


    function user_check_in() {


        $_ids = $this->epl_util->clean_input( epl_get_element( 'id', $_POST ) );

        $ids = explode( '_', $_ids );

        $user_id = intval( $ids[2] );
        $event_id = intval( $ids[0] );
        $date_id = $ids[1];


        $state = $this->epl_util->clean_input( epl_get_element( 'state', $_POST ) );

        $db_key = '_epl_user_absent_' . $_ids;


        if ( $state == 0 ) {

            return delete_user_meta( $user_id, $db_key );
        }


        return update_user_meta( $user_id, $db_key, 1 );
    }


    function get_user_absentee_data( $user_id = null, $event_id = null, $date_id = null ) {
        global $wpdb;

        /*
         * - _epl_user_absent_user_id_event_id_date_id
         */



        return $wpdb->get_results( "
                SELECT meta_key,meta_value
                FROM $wpdb->usermeta
                WHERE meta_key like '_epl_user_absent_{$user_id}%' 
		", OBJECT_K
        );
    }

}