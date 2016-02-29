<?php

if ( !class_exists( 'EPL_User_Regis_Manager' ) ) {

    class EPL_User_Regis_Manager extends EPL_Controller {

        public $active_event_ids;


        function __construct() {

            parent::__construct();

            $this->user_id = get_current_user_id();
            $this->ecm = $this->epl->load_model( 'EPL_Common_Model' );
            $this->erm = $this->epl->load_model( 'EPL_registration_model' );
            $this->erptm = $this->epl->load_model( 'epl-report-model' );
            global $epl_fields;

            $this->fields = $epl_fields;
            //add_action( 'admin_notices', array( $this, 'regis_list_page' ) );
            //add_action( 'admin_init', array( $this, 'set_options' ) );

            if ( isset( $_REQUEST['epl_action'] ) || isset( $_REQUEST['print'] ) || isset( $_REQUEST['epl_download_trigger'] ) || ($GLOBALS['epl_ajax'] ) ) {

                $this->run();
            }
            else {
                //add_action( 'admin_notices', array( $this, 'upcoming_event_list' ) );
                add_action( 'admin_notices', array( $this, 'upcoming_event_list' ) );
                add_action( 'admin_notices', array( $this, 'user_registrations' ) );
            }
        }


        function run() {

            if ( isset( $_REQUEST['epl_action'] ) ) {

                //POST has higher priority
                $epl_action = esc_attr( isset( $_REQUEST['epl_action'] ) ? $_REQUEST['epl_action'] : $_REQUEST['epl_action']  );

                if ( method_exists( $this, $epl_action ) ) {

                    $epl_current_step = $epl_action;

                    $r = $this->$epl_action();
                }
                else
                    $r = epl__( 'Error' );
            }
            else {
                
            }

            if ( isset( $GLOBALS['epl_ajax'] ) && $GLOBALS['epl_ajax'] == true ) {
                $this->setup_response_counts();
                echo $this->epl_util->epl_response( array( 'html' => $r ) );
                exit;
            }
            return $r;
            die( $r );
        }


        function get_last_regis_form_data() {

            $fd = $this->erm->epl_get_last_regis_form_data_values( 0, $_POST['user_id'] );

            $fd['user_id'] = $_POST['user_id'];


            $this->epl_util->set_response_param( 'form_data', json_encode( $fd ) );

            return '';
            echo "<pre class='prettyprint'>" . __LINE__ . "> " . print_r( $fd, true ) . "</pre>";
        }


        //lookup for the sign in table
        function wildcard_lookup( $limit = 5 ) {

            if ( !epl_user_is_admin() )
                return '';
            global $wpdb;

            $lookup = epl_get_element( 'lookup', $_REQUEST );

            $l = $wpdb->get_results(
                    $wpdb->prepare(
                            "SELECT r.user_id,r.regis_key, rf.* FROM {$wpdb->epl_regis_form_data} rf
                                INNER JOIN {$wpdb->epl_registration} r
                                    ON r.regis_id = rf.regis_id
                    WHERE rf.value like %s 
                    GROUP BY rf.id,rf.value 
                    ORDER BY rf.id DESC 
                    LIMIT 10", "%$lookup%"
                    )
            );


            $data['avail_fields'] = $this->ecm->get_list_of_available_fields();
            $data['lookup_list'] = $l;


            return $this->epl->load_view( 'admin/user-regis-manager/regis-name-lookup-result-att-row', $data, true );
        }


        //lookup for the sign in table
        function wildcard_lookup_old( $limit = 5 ) {
            if ( !epl_user_is_admin() )
                return '';

            if ( epl_get_element( 'scope', $_REQUEST ) == 'regis_forms' )
                return $this->wildcard_lookup_for_form();

            return $this->ecm->wildcard_lookup( $_POST['lookup'] );
        }


        //lookup for the automatic form filler
        function wildcard_lookup_for_form( $limit = 5 ) {

            $data['lookup'] = epl_get_element( 'lookup', $_REQUEST, '' );
            $data['att_form_data'] = $this->erptm->attendee_list( false, false, false, false, true );

            return $this->epl->load_view( 'admin/user-regis-manager/regis-name-lookup-result-att-row', $data, true );
        }


        //wildcard lookup form
        function wildcard_lookup_form() {

            return $this->epl->load_view( 'admin/user-regis-manager/regis-name-lookup-form', '', true );
        }


        //list of classes coming up
        function upcoming_event_list() {

            if ( !empty( $_GET['tab'] ) && $_GET['tab'] != 'epl_user_regis_manager' )
                return '';

            global $event_list;

            $dates = array();
            $data['range_defined'] = false;
            if ( isset( $_REQUEST['daterange'] ) && $_REQUEST['daterange'] != '' ) {

                if ( strpos( $_REQUEST['daterange'], '-' ) !== false ) {
                    $dates = explode( '-', $_REQUEST['daterange'] );
                }
                else {
                    $dates[0] = $_REQUEST['daterange'];
                }

                $data['range_defined'] = true;
            }

            $data['start_date_filter'] = epl_get_element( 0, $dates, '' );
            $data['end_date_filter'] = epl_get_element( 1, $dates, '' );


            $this->ecm->events_list( array(
                'show_upcoming' => 1,
                //'event_id' => '3036,2340', 
                'fields' => 'ids' ) );

            $this->active_event_ids = $event_list->posts;

            $this->setup_checked_in_counts();

            $data['content'] = $this->load_view( 'admin/user-regis-manager/daily-checkin-class-list', $data, true );

            if ( epl_is_ajax() )
                return $data['content'];

            $this->load_view( 'admin/user-regis-manager/daily-checkin', $data );
        }


        function user_registrations() {

            if ( empty( $_GET['tab'] ) || $_GET['tab'] != 'epl_user_regis_list' )
                return '';

            $data['registrants'] = $this->get_list_of_registrants( true );

            $data['content'] = $this->load_view( 'admin/user-regis-manager/user-regis-all', $data, true );

            $this->load_view( 'admin/user-regis-manager/daily-checkin', $data );
        }


        function setup_checked_in_counts( $user_also = false ) {

            static $processed = false;

            if ( $processed )
                return null;

            global $wpdb, $session_signed_in_counts, $user_session_signed_in_counts, $user_ticket_use_counts;
            $WHERE = '';
            if ( !epl_is_empty_array( $this->active_event_ids ) )
                $WHERE = ' AND ra.event_id IN (' . implode( ',', $this->active_event_ids ) . ")";
            elseif ( isset( $_POST['event_id'] ) )
                $WHERE = ' AND ra.event_id =' . $wpdb->escape( $_POST['event_id'] );

            $checked_in_counts = $wpdb->get_results(
                    "SELECT ra.regis_id,ra.event_id,ra.date_id,ra.date_ts,ra.time_id,ra.user_id,ra.price_id,ra.regis_data_id  FROM
                        {$wpdb->epl_attendance} ra
                            INNER JOIN {$wpdb->posts} p
                                ON ra.regis_id = p.ID 
                               WHERE 1=1 AND
                               p.post_status = 'publish'
                               $WHERE
                               GROUP BY ra.regis_id, ra.event_id, ra.date_id,ra.date_id,ra.date_ts,ra.time_id,ra.user_id,ra.regis_data_id
                "
            );

            $session_signed_in_counts = array();
            $user_session_signed_in_counts = array();
            $start = true;

            foreach ( $checked_in_counts as $c ) {
                setup_event_details( $c->event_id );

                $time_id = (epl_is_time_optonal() ? '' : $c->time_id);

                if ( $start ) {
                    $session_signed_in_counts["{$c->event_id}-{$c->date_id}-{$c->date_ts}-{$c->time_id}"] = 1;
                    $user_session_signed_in_counts["{$c->regis_id}-{$c->event_id}-{$c->regis_data_id}-{$c->date_id}-{$c->date_ts}-{$c->time_id}-{$c->price_id}-{$c->user_id}"] = 1;
                    $user_ticket_use_counts["{$c->regis_id}-{$c->event_id}-{$c->regis_data_id}-{$c->date_id}-{$time_id}-{$c->price_id}-{$c->user_id}"] = 1;
                }
                else {
                    $session_signed_in_counts["{$c->event_id}-{$c->date_id}-{$c->date_ts}-{$c->time_id}"] ++;
                    $user_session_signed_in_counts["{$c->regis_id}-{$c->event_id}-{$c->regis_data_id}-{$c->date_id}-{$c->date_ts}-{$c->time_id}-{$c->price_id}-{$c->user_id}"] ++;
                    $user_ticket_use_counts["{$c->regis_id}-{$c->event_id}-{$c->regis_data_id}-{$c->date_id}-{$time_id}-{$c->price_id}-{$c->user_id}"] ++;
                }
                $start = false;
            }
            $processed = true;
        }


        //set a global var of all the checking counts
        function get_list_of_checked_in_users() {

            global $wpdb;

            setup_event_details( $_POST['event_id'] );

            $WHERE = " AND ra.event_id = " . $wpdb->escape( $_POST['event_id'] );
            $WHERE .= " AND ra.date_id = '" . $wpdb->escape( $_POST['date_id'] ) . "'";
            $WHERE .= " AND ra.date_ts = " . $wpdb->escape( $_POST['date_ts'] );

            $WHERE .= " AND ra.time_id = '" . $wpdb->escape( $_POST['time_id'] ) . "'";

            $data['checked_in_users'] = $wpdb->get_results(
                    "SELECT count(ra.id) as num_used,ra.*,r.regis_key FROM
                        {$wpdb->epl_attendance} ra
                           INNER JOIN {$wpdb->epl_regis_data} rd
                               ON (ra.regis_data_id=rd.id)
                           INNER JOIN {$wpdb->epl_registration} r
                               ON ra.regis_id=r.regis_id
                               WHERE 1=1
                               $WHERE
                               GROUP BY ra.regis_data_id 
                               ORDER BY r.regis_date, rd.id
                "
            );

            $r .= $this->epl->load_view( 'admin/user-regis-manager/daily-checked-in-users', $data, true );
            return $r;
        }


        //get list of attendees that have checked in for a session
        function get_list_of_registrants( $query_only = false ) {
            global $wpdb, $event_details;
            $r = '';
            $WHERE = '';
            $GROUP_BY = '';
            if ( $_POST ) {
                setup_event_details( $_POST['event_id'] );

                $WHERE = " AND rd.event_id = " . $wpdb->escape( $_POST['event_id'] );
                $WHERE .= " AND rd.date_id = '" . $wpdb->escape( $_POST['date_id'] ) . "'";

                if ( epl_has_attendee_forms() )
                    $WHERE .= " AND rf.form_no > 0";
                else
                    $WHERE .= " AND rf.form_no = 0";

                if ( !epl_is_time_optonal() )
                    $WHERE .= " AND rd.time_id = '" . $wpdb->escape( $_POST['time_id'] ) . "'";

                $GROUP_BY = " GROUP BY regis_data_id, rf.form_no";
            }

            $limit = array(
                'event_id',
                'date_id',
                'time_id',
                'status'
            );

            $data['registrants'] = $this->erptm->get_all_data(null, $limit);

            if ( $query_only )
                return $data['registrants'];

            $this->setup_checked_in_counts();

            $data['checked_in_users'] = $this->get_list_of_checked_in_users();

            $r .= $this->epl->load_view( 'admin/user-regis-manager/daily-checkin-registrants', $data, true );

            return $r;
        }


        function setup_response_counts() {

            global $event_details, $session_signed_in_counts, $user_session_signed_in_counts, $user_ticket_use_counts;

            $this->setup_checked_in_counts();

            //$signed_in_key = "{$_POST['event_id']}-{$_POST['regis_data_id']}-{$_POST['date_id']}-{$_POST['date_ts']}-{$_POST['time_id']}";
            $signed_in_key = "{$_POST['event_id']}-{$_POST['date_id']}-{$_POST['date_ts']}-{$_POST['time_id']}";
            $user_ticket_use_count_key = "{$_POST['regis_id']}-{$_POST['event_id']}-{$_POST['regis_data_id']}-{$_POST['date_id']}-{$_POST['time_id']}-{$_POST['price_id']}-{$_POST['user_id']}";
            $user_session_use_count_key = "{$_POST['regis_id']}-{$_POST['event_id']}-{$_POST['regis_data_id']}-{$_POST['date_id']}-{$_POST['date_ts']}-{$_POST['time_id']}-{$_POST['price_id']}-{$_POST['user_id']}";
            

            $signed_in_count = epl_get_element( $signed_in_key, $session_signed_in_counts, 0 );

            $user_ticket_use_count = epl_get_element( $user_ticket_use_count_key, $user_ticket_use_counts, 0 );
            $user_session_signed_in_count = epl_get_element( $user_session_use_count_key, $user_session_signed_in_counts, 0 );

            $this->epl_util->set_response_param( 'signed_in_count', $signed_in_count );
            $this->epl_util->set_response_param( 'user_ticket_use_count', $user_ticket_use_count );
            $this->epl_util->set_response_param( 'user_session_signed_in_counts', $user_session_signed_in_count );
        }


        //check in a user based on the class session
        function checkin_user() {
            global $wpdb;
            $i = $wpdb->insert(
                    $wpdb->epl_attendance, array(
                'regis_id' => $_POST['regis_id'],
                'event_id' => $_POST['event_id'],
                'regis_data_id' => $_POST['regis_data_id'],
                'date_id' => $_POST['date_id'],
                'date_ts' => $_POST['date_ts'],
                'time_id' => $_POST['time_id'],
                'price_id' => $_POST['price_id'],
                'user_id' => $_POST['user_id'],
                'form_no' => $_POST['form_no'],
                    ), array(
                '%d',
                '%d',
                '%d',
                '%s',
                '%d',
                '%s',
                '%s',
                '%d',
                '%d',
                    )
            );


            $r = $this->get_list_of_registrants();

            return $r;
        }


        //delete a checking record
        function delete_checkin_record() {
            global $wpdb;

            if ( epl_user_is_admin() ) {
                $wpdb->query( $wpdb->prepare( "DELETE FROM {$wpdb->epl_attendance} WHERE id=%d", $_POST['att_id'] ) );
            }
        }

    }

}
