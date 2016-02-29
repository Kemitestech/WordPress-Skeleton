<?php

if ( !class_exists( 'EPL_report_manager' ) ) {

    class EPL_report_manager extends EPL_Controller {


        function __construct() {

            parent::__construct();
            global $epl_fields;

            $this->epl->load_config( 'event-fields' );
            $this->epl->load_config( 'regis-fields' );
            $this->fields = $epl_fields;
            $this->erm = $this->epl->load_model( 'epl-registration-model' );

            $this->ecm = $this->epl->load_model( 'epl-common-model' );
            $this->erptm = $this->epl->load_model( 'epl-report-model' );
            $this->edbm = $this->epl->load_model( 'epl-db-model' );


            $this->erm->on_admin = true;

            if ( isset( $_REQUEST['epl_action'] ) || isset( $_REQUEST['print'] ) || isset( $_REQUEST['epl_download_trigger'] ) || ($GLOBALS['epl_ajax'] ) ) {
                if(!is_user_logged_in()) exit;
                $this->run();
            }
            else {
                add_action( 'admin_notices', array( $this, 'reports_page' ) );
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

            $r = $this->epl_util->make_pdf( $r, false );

            echo $this->epl_util->epl_response( array( 'html' => $r ) );
            die();
        }


        function custom_pdf() {


            setup_regis_details( $_REQUEST['regis_id'] );

            if ( !epl_check_token() ) {
                exit( epl__( 'You have reached this page in error.' ) );
            }
            $data['content'] = apply_filters( 'epl_custom_admin_pdf_content', 'No Content' );
            $data['vars'] = apply_filters( 'epl_custom_admin_pdf_vars', array() );
            
            $view = apply_filters( 'epl_custom_admin_pdf_view_file', '' );
            if ( $view == '' )
                return '';

            $i = $this->load_view( $view, $data, true );

            $this->epl->epl_util->make_pdf( $i, false, true, false, 'portrait' );
            exit;
        }


        function custom_html( $pdf = false ) {

            $data['content'] = apply_filters( 'epl_custom_admin_html_content', 'No Content' );
            $data['vars'] = apply_filters( 'epl_custom_admin_html_vars', array() );
            $view = apply_filters( 'epl_custom_admin_html_view_file', '' );
            if ( $view == '' )
                return '';

            $i = $this->load_view( $view, $data, true );

            if ( !$pdf )
                exit( $i );

            $this->epl->epl_util->make_pdf( $i, false, true, false, 'portrait' );
            exit;
        }


        
        function reports_page() {
            global $epl_fields;

            $params = array(
                'input_type' => 'select',
                'input_name' => 'event_id',
                'id' => 'event_id',
                'label' => epl__( 'Event' ),
                'options' => array( '' => epl__( 'Show All Events' ) ) + $this->ecm->get_all_events(),
                'value' => $this->event_id,
                //'overview' => $this->edit_mode,
                'class' => 'chzn-select',
                'empty_row' => true,
                'show_value_only' => $this->edit_mode
            );

            $data['event_list_dd'] = $this->epl_util->create_element( $params );

            $epl_fields['epl_regis_payment_fields']['_epl_regis_status']['input_type'] = 'select';
            $_f = $epl_fields['epl_regis_payment_fields']['_epl_regis_status'];
            $_f['input_name'] = 'status';
            $_f['options'][''] = epl__( 'Show all Registrations' );
            $_f['default_value'] = '';
            $_f['class'] = 'chzn-select';
            $_f['value'] = intval( $_GET['_epl_regis_status'] );
            ksort( $_f['options'] );
            $data['regis_status_dd'] = $this->epl->epl_util->create_element( $_f, 0 );


            $this->epl->load_view( 'admin/reports/reports-page', $data );
        }


        function run_report() {

            global $wpdb, $event_details;


            //if ( $_REQUEST['event_id'] != '' )
            //  $prefix = 'event-';

            $m = str_replace( '-', '_', $_REQUEST['report_type'] );

            if ( method_exists( __CLASS__, $m ) ) {
                $r = $this->$m();
            }
            elseif ( $_REQUEST['report_type'] == 'full' ) {
                $r = $this->erptm->attendee_list( true, false, null, true );
            }
            elseif ( $_REQUEST['report_type'] == 'primary-form-data' ) {
                $r = $this->primary_form_data();
            }
            else {
                $this->regis_list = $this->edbm->get_the_data();
                $data['regis_list'] = $this->regis_list;
                $csv_link_arr = array( 'page' => 'epl_report_manager', 'epl_action' => 'attendee_list', 'epl_download_trigger' => 1, 'epl_controller' => 'epl_report_manager', 'event_id' => $event_id );
                $base_url = admin_url( 'edit.php?post_type=epl_event' );
                $data['csv_link'] = epl_anchor( add_query_arg( $csv_link_arr, $base_url ), epl__( 'Export Full CSV' ), null, 'class="button-primary"' );

                $this->ecm->events_list();
                $r = $this->epl->load_view( "admin/reports/{$_REQUEST['report_type']}", $data, true );
            }


            if ( $GLOBALS['epl_ajax'] )
                return $r;
            echo $r;
        }


        function full_report() {
            global $wpdb;

            $data = array();
            $data['list'] = $this->erptm->get_all_data();

            return $this->epl->load_view( "admin/reports/full-report", $data, true );
        }


        function primary_form_data() {
            global $wpdb;

            //get the primary registrant form data for all events
            $data = array();
            $data['form_list'] = $wpdb->get_results(
                    "SELECT
                        r.regis_id, r.regis_key,r.regis_key,
                        rf.*                        
                        FROM {$wpdb->epl_regis_form_data} rf
                        INNER JOIN {$wpdb->epl_registration} r
                        ON r.regis_id = rf.regis_id
                        WHERE 1=1 AND (r.status = 2 OR r.status = 5)
                        AND rf.form_no = 0
                        GROUP BY rf.regis_id
                        ORDER BY  r.regis_date
          "
            );

            return $this->epl->load_view( "admin/reports/primary-form-data", $data, true );
        }


        function transactions() {
            global $wpdb;

            $data['transactions'] = $this->erptm->transactions();

            return $this->epl->load_view( "admin/reports/transactions", $data, true );
            echo "<pre class='prettyprint'>" . __LINE__ . "> " . print_r( $data['transactions'], true ) . "</pre>";
        }


        function all_form_data() {
            global $wpdb;

            $data = array();
            $data['list'] = $this->erptm->get_all_data();

            return $this->epl->load_view( "admin/reports/all-form-data", $data, true );
        }


        function event_snapshot() {
            global $event_details, $regis_details, $event_snapshot;
            $erptm = EPL_report_model::get_instance();

            $data['event_snapshot'] = $this->erm->event_snapshot( $_GET['event_id'] );

            $this->epl->load_view( "admin/event-snapshot", $data );


            exit;
        }


        function view_names2() {
            if ( !is_user_logged_in() )
                exit;
            global $event_details, $wpdb;

            $registrations = $wpdb->get_results( "
                    SELECT rf.* 
                    FROM {$wpdb->epl_regis_form_data} rf
                        INNER JOIN {$wpdb->epl_registration} r
                            ON r.regis_id=rf.regis_id
                        WHERE 1=1 AND (r.status = 2 OR r.status = 5) 
                        AND field_id <> ''
                        GROUP BY value
                        ORDER BY id
                        " );

            $avail_fields = epl_get_list_of_available_fields();
            $default_row = array_fill_keys( array_keys( $avail_fields ), null );
            $tmpl = array( 'table_open' => '<table border="1" cellpadding="0" cellspacing="0" class="epl_daily_schedule_table  dataTable" id="">' );
            $this->epl->epl_table->set_template( $tmpl );
            foreach ( $registrations as $r ) {

                if ( strpos( $r->field_id, EPL_PLUGIN_DB_DELIM ) ) {
                    $fields = explode( EPL_PLUGIN_DB_DELIM, $r->field_id );
                    $values = explode( EPL_PLUGIN_DB_DELIM, $r->value );
                }
                else {
                    $fields = array( $r->field_id );
                    $values = array( $r->value );
                }


                $full = array_combine( $fields, $values );
                if ( !$limited )
                    $_row = array_merge( $default_row, $full );
                else
                    $_row = array_intersect_key( $full, $default_row );

                $this->epl->epl_table->add_row( array_values( $_row ) );

                //echo "<pre class='prettyprint'>" . __LINE__ . "> " . basename( __FILE__ ) . " > " . print_r( $_row, true ) . "</pre>";
            }

            $header = epl_get_field_labels( array_intersect_key( $avail_fields, $default_row ) );

            $this->epl->epl_table->set_heading( $header );
            echo $this->epl->epl_table->generate();
        }


        function view_names() {

            global $event_details, $wpdb;

            $filters = array(
                'event_id' => $_REQUEST['event_id'],
                'date_id' => epl_get_element( 'date_id', $_REQUEST, null ),
                'time_id' => epl_get_element( 'time_id', $_REQUEST, null ),
                'names_only' => epl_get_element( 'names_only', $_REQUEST, 0 ),
                'combined' => epl_get_element( 'combined', $_REQUEST, 0 ),
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
                $data['list'] = $this->erptm->attendee_form_data( $_filter );
            else
                $data['list'] = $this->erptm->get_all_data_2( $_filter );


            $data['fields'] = $this->erptm->get_form_fields();
            $data['attendance_dates'] = $attendance_dates;

            $data['event_title'] = $event_details['post_title'];
            $data['event_date'] = isset( $_REQUEST['date_id'] ) ? epl_formatted_date( epl_get_element_m( $_REQUEST['date_id'], '_epl_start_date', $event_details ), "D, M j" ) : '';
            $data['event_time'] = isset( $_REQUEST['time_id'] ) ? epl_get_element_m( $_REQUEST['time_id'], '_epl_start_time', $event_details ) : '';

            $url = admin_url( "edit.php?post_type=epl_event&epl_action=view_names&table_view=1&epl_controller=epl_report_manager&print=1" );

            $url = add_query_arg( $filters, $url );

            $data['print_icon'] = (!isset( $_REQUEST['print'] )) ? '<div><a href="' . $url . '" target="_blank"><img src="' . EPL_FULL_URL . 'images/printer.png" /></a></div>' : '';
            $data['filters'] = $filters;
            if ( $filters['names_only'] == 1 )
                $r = $this->epl->load_view( 'admin/reports/view-names', $data, true );
            elseif ( $filters['combined'] == 1 )
                $r = $this->epl->load_view( 'admin/reports/view-full-combined', $data, true );
            else
                $r = $this->epl->load_view( 'admin/reports/view-full-data', $data, true );


            if ( $GLOBALS['epl_ajax'] )
                return $r;

            if ( isset( $_REQUEST['print'] ) ) {
                $data['content'] = $r;
                $this->epl->load_view( 'admin/template', $data );
                return;
            }


            echo $r;
        }


        function get_the_email_form() {
            global $wpdb;
            $_field = array(
                'input_type' => 'select',
                'input_name' => '_epl_notification_id',
                'options' => get_list_of_available_notifications(),
                'empty_row' => true,
                'label' => epl__( 'Email Template' ),
                'empty_options_msg' => epl__( 'No email messages found.  Please go to Events Planner > Notification Manager to create notifications.' ),
                'class' => 'epl_notification_dd'
            );

            $data['available_notifications'] = $this->epl->epl_util->create_element( $_field, 0 );
            $data['post_ID'] = $this->post_ID;
            $data['event_id'] = intval( $_REQUEST['event_id'] );
            $email_list = $this->erptm->get_the_email_addresses( $data );

            $data['email_list_for_copy'] = implode( ', ', $email_list['raw_list'] );
            //$email_list = $email_list['display_list'];
            $data['num_emails'] = $email_list['num_emails'];

            /* $to_emails = array(
              'input_type' => 'checkbox',
              'input_name' => 'to_emails[]',
              'options' => $email_list,
              'default_checked' => true
              ); */
            $to_emails = $email_list['display_list'];
            $data['emails'] = $email_list['display_list']; //$this->epl->epl_util->create_element( $to_emails );

            return $this->epl->load_view( 'admin/registration/regis-email-form', $data, true );
        }


        function invoice() {

            return $this->erptm->invoice();
        }

    }

}