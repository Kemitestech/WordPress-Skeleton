<?php

class EPL_email {

    private static $instance;


    function __construct() {
        epl_log( 'init', get_class() . " initialized" );


        self::$instance = $this;

        add_action( 'init', array( $this, 'load_components' ) );
    }


    public static function get_instance() {
        if ( !self::$instance ) {

            self::$instance = new EPL_email;
        }

        return self::$instance;
    }


    function load_components() {
        $this->epl = EPL_base::get_instance();

        $this->ecm = $this->epl->load_model( 'epl-common-model' );
        $this->erm = $this->epl->load_model( 'epl-registration-model' );
        $this->rm = $this->epl->load_model( 'epl-recurrence-model' );
        $this->opt = $this->ecm->get_epl_options();


    }

    function send_confirmation_email( $data ) {
            global $organization_details, $customer_email, $event_details;

            $data['eb'] = '';

            $default_email_body = $this->epl->load_view( 'front/registration/regis-confirm-email', $data, true );

            $_notif = epl_get_element( '_epl_event_notification', $event_details );

            $_notif_data = array( );
            if ( $_notif && (!epl_is_empty_array( $_notif ) || $_notif != '') ) {

                $id = is_array( $_notif ) ? current( $_notif ) : $_notif;
                $_notif_data = get_post( $id, ARRAY_A ) + ( array ) $this->ecm->get_post_meta_all( $id );

                $data['eb'] = nl2br( $this->notif_tags( stripslashes_deep( html_entity_decode( $_notif_data['post_content'], ENT_QUOTES ) ) ) );
            }

            if ( epl_is_empty_array( $_notif ) || $_notif == '' || epl_get_element( '_epl_notification_replace', $event_details, 0 ) == 0 )
                $email_body = $this->epl->load_view( 'front/registration/regis-confirm-email', $data, true );
            else
                $email_body = $data['eb'];

            $email_body = preg_replace( '/<div class=\'epl_(.*?)_message\'>(.*?)<\/div>/', '', $email_body );

            $from_name = html_entity_decode( epl_get_element( '_epl_email_from_name', $_notif_data, get_bloginfo( 'name' ) ), ENT_QUOTES );
            $from_email = epl_get_element( '_epl_from_email', $_notif_data, get_bloginfo( 'admin_email' ) );
            $subject = html_entity_decode( epl_get_element( '_epl_email_subject', $_notif_data, epl__( 'Registration Confirmation' ) ), ENT_QUOTES );

            $headers = "From: \"" . $from_name . "\" <{$from_email}> \r\n";
            $headers .= 'Reply-To: ' . $from_email . "\r\n";
            $headers .= 'X-Mailer: PHP/' . phpversion();
            $headers .= "MIME-Version: 1.0\r\n";
            $headers .= "Content-Type: text/html; charset=UTF-8\r\n";


            if ( (isset( $customer_email )) && $customer_email != '' )
                @wp_mail( $customer_email, $subject, $email_body, $headers );

            //admin email
            $_email = epl_get_event_property( '_epl_alt_admin_email' );

            if ( $_email == '' ) {

                $_email = epl_nz( epl_get_event_option( 'epl_default_notification_email' ), get_bloginfo( 'admin_email' ) );
            }

            @wp_mail( $_email, epl__( 'New Registration' ) . ': ' . get_the_event_title(), $default_email_body, $headers );
        }


}