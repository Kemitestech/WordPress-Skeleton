<?php

if ( !class_exists( 'EPL_User_Self_Pages_Manager' ) ) {

    class EPL_User_Self_Pages_Manager extends EPL_Controller {


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
                add_action( 'admin_notices', array( $this, 'regis_list_page' ) );
            }
        }


        function run() {

            if ( isset( $_REQUEST['epl_action'] ) ) {

                //POST has higher priority
                $epl_action = esc_attr( isset( $_REQUEST['epl_action'] ) ? $_REQUEST['epl_action'] : $_REQUEST['epl_action']  );

                if ( method_exists( $this, $epl_action ) ) {

                    $epl_current_step = $epl_action;

                    $r = $this->$epl_action();
                } else
                    $r = epl__( 'Error' );
            }
            else {
                
            }

            if ( isset( $GLOBALS['epl_ajax'] ) && $GLOBALS['epl_ajax'] == true ) {
                echo $this->epl_util->epl_response( array( 'html' => $r ) );
                exit;
            }
            return $r;
            die( $r );
        }


        function regis_list_page() {

            global $wpdb, $event_details, $regis_details;

            global $event_details;

            $this->eum = $this->epl->load_model( 'epl-user-model' );
            $this->epl->load_model( 'epl-report-model' );

            $data['user_bookings'] = $this->eum->user_bookings( $args );


            $data['content'] = $this->epl->load_view( 'user-regis-manager/user-bookings', $data, true );
            $this->epl->load_view( 'admin/user-regis-manager/admin-urm-page', $data );
            return null;
            $registrations = $wpdb->get_results( "
                SELECT umeta_id,user_id,meta_key,meta_value
                FROM $wpdb->usermeta
                WHERE meta_key like '_epl_regis_post_id%'
		AND user_id = {$this->user_id}"
            );
            $r = array( );
            $this->erm->dest = 'admin';
            $this->erm->on_admin = true;
            $this->erm->set_mode( 'overview' );
            $data['registrations'] = '';
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



                foreach ( $d as $k => $v ) {
                    $_regis_id = $d['_regis_id'];

                    $_events = $d[$_regis_id]['_events'];
                    $_dates = $d[$_regis_id]['_dates']['_epl_start_date'];


                    $event_id = key( $_events );

                    $this->ecm->setup_event_details( $event_id );
                    $data['event_title'] = esc_attr( $event_details['post_title'] );


                    $data['regis_dates'] = array_intersect_key( $event_details['_epl_start_date'], array_flip( $_dates[$event_id] ) );



                    $data['list'] = $this->epl->load_view( 'admin/user-regis-manager/user-regis-dates', $data, true );
                }
                $this->erm->setup_current_data( $regis_data );
                $data['regis_form'] = $this->erm->regis_form( null );

                $data['registrations'] .= $this->epl->load_view( 'admin/user-regis-manager/user-regis-list-page', $data, true );
            }

            return $this->epl->load_view( 'admin/user-regis-manager/user-regis-list-page-wrapper', $data, true );
        }

    }

}
