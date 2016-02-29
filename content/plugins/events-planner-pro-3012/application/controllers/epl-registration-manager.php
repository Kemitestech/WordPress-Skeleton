<?php

/*
 * Cleanup planned
 */
if ( !class_exists( 'EPL_Registration_Manager' ) ) {

    class EPL_Registration_Manager extends EPL_Controller {

        const post_type = 'epl_registration';


        function __construct() {

            parent::__construct();
            global $epl_on_admin;
            $epl_on_admin = true;

            epl_log( 'init', get_class() . " initialized" );
            global $epl_fields;
            $this->epl->load_config( 'regis-fields' );
            $this->epl_fields = $epl_fields; //this is a multi-dimensional array of all the fields
            $this->ind_fields = $this->epl_util->combine_array_keys( $this->epl_fields ); //this is each individual field array




            $this->erm = $this->epl->load_model( 'epl-registration-model' );
            //$this->earm = $this->epl->load_model( 'epl-regis-admin-model' );
            $this->ecm = $this->epl->load_model( 'epl-common-model' );
            $this->erptm = $this->epl->load_model( 'epl-report-model' );
            $this->edbm = $this->epl->load_model( 'epl-db-model' );
            $this->erm->on_admin = true;

            $this->edit_mode = (epl_get_element( 'post', $_GET ) || epl_get_element( 'post_ID', $_REQUEST ));

            if ( isset( $_REQUEST['print'] ) || isset( $_REQUEST['epl_download_trigger'] ) || ($GLOBALS['epl_ajax'] ) ) {

                $this->run();
            }
            else {
                $this->personal_field_ids = $this->ecm->get_personal_field_ids();
                add_action( 'default_title', array( $this, 'pre' ) );
                add_action( 'add_meta_boxes', array( $this, 'add_meta_boxes' ) );
                add_action( 'save_post', array( $this, 'save_postdata' ) );

                //post list manage screen columns - extra columns
                add_filter( 'manage_' . self::post_type . '_posts_columns', array( $this, 'add_new_columns' ) );
                //post list manage screen - column data 
                add_action( 'manage_' . self::post_type . '_posts_custom_column', array( $this, 'column_data' ), 10, 2 );
                add_filter( 'restrict_manage_posts', array( $this, 'filters' ) );
                //add_filter( 'request', array( $this, 'request_filter' ) );
                add_filter( 'parse_query', array( $this, 'request_filter_2' ) );
                // make these columns sortable
                //add_filter( "manage_edit-" . self::post_type . "_sortable_columns", array( $this,"sortable_columns") );

                add_action( 'admin_head', array( $this, 'admin_table_header' ) );
                add_action( 'admin_init', array( $this, 'admin_init' ) );
            }
        }


        function admin_init() {

            add_action( 'wp_trash_post', array( $this, 'delete_post' ) );

            //works, using instead of untrash_post
            add_action( 'untrashed_post', array( $this, 'populate_custom_tables' ) );
        }


        function run() {
            //$this->get_values();

            if ( isset( $_REQUEST['epl_action'] ) ) {

                //POST has higher priority
                $epl_action = esc_attr( isset( $_POST['epl_action'] ) ? $_POST['epl_action'] : $_REQUEST['epl_action']  );
                $this->epl_action = $epl_action;
                if ( method_exists( $this, $epl_action ) ) {

                    $epl_current_step = $epl_action;

                    $r = $this->$epl_action();
                } else
                    $r = epl__( 'Request Error' );
            }
            else {
                
            }
            
            echo $this->epl_util->epl_response( array( 'html' => $r ) );
            die();
        }


        function save_postdata( $post_ID ) {
            //return;
            if ( (defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) || empty( $_POST ) || epl_get_element( 'action', $_REQUEST ) == 'inline-save' )
                return;

            epl_delete_transient();

            $initial_save = false;
            $this->get_values();

            global $wpdb;

            $this->erm->set_mode( 'edit' )->setup_current_data( $this->regis_meta );
            $this->erm->calculate_cart_totals();
            $this->erm->add_registration_to_db( $this->regis_meta['__epl'] );


            $this->update_payment_details();
            $this->populate_custom_tables( $post_ID );

            return;
        }


        function delete_post( $post_ID ) {

            if ( current_user_can( 'delete_posts' ) )
                $this->edbm->reset_tables( $post_ID );
        }


        function populate_custom_tables( $post_ID ) {

            $this->edbm->reset_table_for_registration( $post_ID );
        }


        //lookup for the sign in table
        function wildcard_lookup_new( $limit = 5 ) {

            global $wpdb;
            //   if(!epl_user_is_admin()) 
            //     return '';

            $lookup = epl_get_element( 'lookup', $_REQUEST );

            $l = $wpdb->get_results(
                    $wpdb->prepare(
                            "SELECT * FROM {$wpdb->epl_regis_form_data} 
                    WHERE value like %s 
                    GROUP BY id,value 
                    ORDER BY id DESC 
                    LIMIT 10", "%$lookup%"
                    )
            );

            $data['avail_fields'] = $this->ecm->get_list_of_available_fields();
            $data['lookup_list'] = $l;


            $this->epl->load_view( 'admin/user-regis-manager/regis-name-lookup-result-att-row', $data );
        }


        function admin_table_header() {

            echo '<style type="text/css">';
            echo '.wp-list-table .column-cb { width: 3%; }';
            echo '.wp-list-table .column-attendees { width: 12%; }';
            echo '.wp-list-table .column-title { width: 10%; }';
            echo '.wp-list-table .column-event { width: 30%; }';
            echo '.wp-list-table .column-payment_status { width: 20%; }';
            echo '.wp-list-table .column-date { width: 10%; }';
            echo '</style>';
        }


        function send_email() {
            /*
             * - send the from name, email, subject, content
             * - setup customer name and email
             */

            $to_emails = epl_get_element( 'to_emails', $_POST );

            if ( !$to_emails || empty( $_POST['email_body'] ) )
                return epl__( 'Please select at least one email address.' );

            global $event_details;

            $notif_data = $this->ecm->setup_notif_details( intval( epl_get_element( '_epl_notification_id', $_POST ) ) );

            $notif_data['_epl_email_from_name'] = epl_get_element( '_epl_email_from_name', $_POST, $notif_data['_epl_email_from_name'] );
            $notif_data['_epl_from_email'] = epl_get_element( '_epl_from_email', $_POST, $notif_data['_epl_from_email'] );
            $notif_data['_epl_email_cc'] = epl_get_element( '_epl_email_cc', $_POST, '' );
            $notif_data['_epl_email_bcc'] = epl_get_element( '_epl_email_bcc', $_POST, '' );
            $notif_data['_epl_email_subject'] = epl_get_element( '_epl_email_subject', $_POST, $notif_data['_epl_email_subject'] );

            $this->erm->set_mode( 'overview' );

            foreach ( $to_emails as $regis_post_id => $emails ) {

                $this->get_values( $regis_post_id );

                $data['customer_email'] = $this->get_primary_email_address();
                $data['customer_email'] = implode(',', $emails);

                $this->epl->epl_util->regis_id = $regis_post_id;

                $this->erm->setup_current_data( $this->regis_meta );

                $data['regis_form'] = $this->erm->regis_form( null, false, false );

                $data['email_body'] = $this->epl->epl_util->notif_tags( epl_get_element( 'email_body', $_POST ), $data );
                $data['email_template'] = epl_get_element( '_epl_email_template', $notif_data, 'default' );
                $data['notif_data'] = $notif_data;
                $attach_pdf = (epl_get_element( 'attach_pdf_invoice', $_POST ) == 1);
                $msg = '';
                if ( $attach_pdf ) {

                    $invoice = $this->erptm->invoice( $regis_post_id );
                    $data['attachment'] = $this->epl->epl_util->make_pdf( $invoice, true, true );
                    $msg = ' ,' . epl__( "Invoice" );
                }

                $r = $this->epl->epl_util->send_email( $data );
                if ( $r )
                    add_post_meta( $regis_post_id, '_epl_regis_note', array( 'action' => epl__( 'Email Sent' ) . $msg, 'timestamp' => EPL_TIME ) );

                if ( $attach_pdf )
                    $this->epl->epl_util->delete_file( $data['attachment'] );
            }


            if ( $r ) {

                return epl__( 'Email Sent' );
            }

            return epl__( 'An error has occured' );
        }


        //works also
        function request_filter( $request ) {


            if ( !is_admin() )
                return $request;

            if ( isset( $_GET['event_id'] ) && $_GET['event_id'] != '' ) {
                $request['meta_key'] = '_total_att_' . intval( $_GET['event_id'] );
                //$request['meta_value'] = '';
            }
            elseif ( isset( $_GET['_epl_regis_status'] ) && !empty( $_GET['_epl_regis_status'] ) ) {
                $request['meta_key'] = '_epl_regis_status';
                $request['meta_value'] = intval( $_GET['_epl_regis_status'] );
            }


            return $request;
        }


        function request_filter_2( $query ) {
            global $pagenow, $post_type;


            if ( !is_admin() || $pagenow != 'edit.php' || $post_type != 'epl_registration' )
                return;

            $q = array( );

            if ( isset( $_GET['event_id'] ) && !empty( $_GET['event_id'] ) ) {
                //set_query_var( 'meta_query', array( array( 'key' => '_total_att_' . intval( $_GET['event_id'] ) ) ) );
                $q[] = array( 'key' => '_total_att_' . intval( $_GET['event_id'] ) );
            }
            if ( isset( $_GET['_epl_regis_status'] ) && !empty( $_GET['_epl_regis_status'] ) ) {
                //set_query_var( 'meta_query', array( array( 'key' => '_epl_regis_status', 'value' => intval( $_GET['_epl_regis_status'] ) ) ) );
                $q[] = array( 'key' => '_epl_regis_status', 'value' => intval( $_GET['_epl_regis_status'] ) );
            }
            if ( isset( $_GET['_last_name_filter'] ) && !empty( $_GET['_last_name_filter'] ) ) {
                //set_query_var( 'meta_query', array( array( 'key' => '__epl', 'value' => $_GET['_last_name_filter'], 'compare' => 'LIKE' ) ) );
                $q[] = array( 'key' => '__epl', 'value' => $_GET['_last_name_filter'], 'compare' => 'LIKE' );
            }

            set_query_var( 'meta_query', $q );
        }


        function get_the_email_list() {

            $regis_id = epl_get_element( 'post_ID', $_POST, null );
            return $this->erptm->attendee_list( true, true, $regis_id );
            return $this->ecm->epl_attendee_list( false, true, $regis_id );
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
            $data['event_id'] = $this->event_id;
            $email_list = $this->erptm->get_the_email_addresses();

            $data['email_list_for_copy'] = implode( ', ', $email_list );
            $to_emails = array(
                'input_type' => 'checkbox',
                'input_name' => 'to_emails[]',
                'options' => $email_list,
                'default_checked' => true
            );

            $data['emails'] = $this->epl->epl_util->create_element( $to_emails );

            echo $this->epl->load_view( 'admin/registration/regis-email-form', $data, true );
        }


        function get_the_email_form_editor() {

            $notif_id = epl_get_element( 'notif_id', $_POST );

            if ( !$notif_id )
                return epl__( 'Please select an email template.' );

            $notif_data = $this->ecm->setup_notif_details( $notif_id );

            $data['notif_data'] = $notif_data;
            $data['notif_data']['post_content'] = $notif_data['post_content'];

            return $this->epl->load_view( 'admin/registration/regis-email-form-editor', $data, true );
        }


        function filters() {

            global $wpdb, $epl_fields;

            $events = $this->ecm->the_list_of_active_events();

            $_f = $epl_fields['epl_regis_payment_fields']['_epl_regis_status'];
            $_f['options'][''] = epl__( 'Show all Registrations' );
            $_f['default_value'] = '';
            $_f['input_type'] = 'select';
            //$_f['class'] = 'chzn-select';
            $_f['value'] = intval( $_GET['_epl_regis_status'] );
            ksort( $_f['options'] );
            $regis_status_dd = $this->epl->epl_util->create_element( $_f, 0 );
            ?>
            <select name="event_id">
                <option value=""><?php epl_e( 'Show All Events' ); ?></option>
                <?php foreach ( $events as $event_id => $event ) {
                    ?>
                    <option value="<?php echo esc_attr( $event_id ); ?>" <?php selected( epl_get_element( 'event_id', $_GET ), $event_id ); ?>><?php echo esc_attr( $event ); ?></option>
                <?php } ?>
            </select>


            <?php

            echo $regis_status_dd['field'];
            echo epl__( "First/Last Name, Email" ) . ': ' . "<input type='text' name='_last_name_filter' value='" . $_GET['_last_name_filter'] . "' />";
            //echo '<input type="reset" value="Reset" class="button-secondary">';
        }


        function pre( $title ) {

            /* $new_id = $this->erm->create_new_regis_record();

              wp_redirect("post.php?post={$new_id}&action=edit");
              die();
             */
            $title = strtoupper( $this->epl_util->make_unique_id( epl_nz( epl_get_regis_setting( 'epl_regis_id_length' ), 10 ) ) );

            return $title;
        }


        function process_cart_action() {


            if ( isset( $_REQUEST['cart_action'] ) ) {

                //POST has higher priority
                $cart_action = esc_attr( $_POST['cart_action'] ? $_POST['cart_action'] : $_GET['cart_action']  );

                if ( method_exists( $this, $cart_action ) ) {

                    $epl_current_step = $cart_action;

                    $r = $this->$cart_action();
                }
            }
            else {
                //Hmm
            }

            return $r;



            echo $this->epl_util->epl_response( array( 'html' => $r ) );
            die();
        }


        function calc_total( $meta = null ) {
            global $cart_totals;
            $this->get_values();
            $this->get_totals();
            $data['money_totals'] = $cart_totals['money_totals'];

            return $this->epl->load_view( 'admin/cart/cart-totals', $data, true );
        }


        function get_totals() {


            $this->erm->set_mode( 'edit' )->setup_current_data( $this->regis_meta );
            $r = $this->erm->calculate_cart_totals( true );

            return $r;
        }


        function send_waitlist_approval_email() {
            global $event_details;
            $this->get_values();
            //get the email_field
            //get the notification id

            if ( !epl_is_waitlist_approved() )
                return epl_create_message( epl__( 'This waitlist needs to be approved before the email can be sent.' ), 'warning' );



            $_notif = epl_get_element( '_epl_waitlist_approved_notification', $event_details );

            $id = is_array( $_notif ) ? current( $_notif ) : $_notif;
            $notif_data = get_post( $id, ARRAY_A ) + ( array ) $this->ecm->get_post_meta_all( $id );


            $this->erm->regis_id = $this->post_ID;
            $this->epl->epl_util->regis_id = $this->post_ID;

            update_post_meta( $this->post_ID, '_epl_waitlist_email_time', EPL_TIME );

            $data['email_template'] = epl_get_element( '_epl_email_template', $notif_data, 'default' );


            $data['notif_data'] = $notif_data;
            $data['email_body'] = nl2br( $this->epl->epl_util->notif_tags( stripslashes_deep( html_entity_decode( $notif_data['post_content'], ENT_QUOTES ) ), $data ) );
            $data['customer_email'] = $this->get_primary_email_address();

            $r = $this->epl->epl_util->send_email( $data );

            return $r ? epl__( 'Email Sent' ) : epl__( 'An error has occured' );
        }


        function get_primary_email_address() {

            $regis_id = $this->regis_meta['__epl']['_regis_id'];

            if ( $r = epl_get_element( 0, $this->regis_meta['__epl'][$regis_id]['_attendee_info']['4e794a6eeeb9a'] ) )
                return $r;

            $r = epl_get_element_m( 0, $this->event_id, $this->regis_meta['__epl'][$regis_id]['_attendee_info']['4e794a6eeeb9a'] );

            return $r;
        }


        function populate_db_tables( $regis_id = null, $empty = true ) {

            if ( !epl_user_is_admin() )
                return "Not Authorized";
            $regis_id = is_null( $regis_id ) ? epl_get_element( 'regis_id', $_REQUEST, null ) : null;

            $this->edbm->reset_tables();
            $this->edbm->populate_db_tables( $regis_id );

            if ( isset( $_REQUEST['r'] ) ) {
                update_option( '_epl_last_table_refresh', current_time( 'mysql' ) );
                wp_redirect( admin_url( '/edit.php?post_type=epl_event&page=epl_settings&tab=api-settings' ) );
            }
        }


        function new_report() {

            $data['list'] = $this->edbm->get_the_list_new();

            $this->epl->load_view( 'admin/reports/regis-list-new', $data );
        }


        function epl_daily_schedule() {
            $this->get_values();

            $r = $this->ecm->epl_daily_schedule( epl_get_element( 'table_view', $_REQUEST, false ) );

            if ( $GLOBALS['epl_ajax'] )
                return $r;

            if ( isset( $_REQUEST['print'] ) ) {
                $data['content'] = $r;
                $this->epl->load_view( 'admin/template', $data );
            }
            //echo $r;
        }


        function epl_attendee_list() {
            $this->get_values();

            $r = $this->ecm->epl_attendee_list( epl_get_element( 'table_view', $_REQUEST, false ) );
            //$r = $this->erptm->attendee_form_data( epl_get_element( 'table_view', $_REQUEST, false ) );
            //echo $this->erptm->attendee_list( epl_get_element( 'table_view', $_REQUEST, false ) );

            if ( $GLOBALS['epl_ajax'] )
                return $r;
            echo $r;
        }


        function update_payment_details() {
            if ( (defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE) || empty( $_POST ) )
                return;


            if ( !empty( $_POST ) && check_admin_referer( 'epl_form_nonce', '_epl_nonce' ) ) {

                if ( $this->erm->update_payment_data( $_POST ) )
                    return $this->payment_info_box( $_POST['post_ID'] );
            }

            return "Error";
        }


        function epl_event_snapshot() {

            global $wpdb, $event_details;


            $data['event_snapshot'] = $this->erm->event_snapshot( intval( $_POST['event_id'] ) );

            $r = $this->epl->load_view( 'admin/event-snapshot', $data, true );

            if ( $GLOBALS['epl_ajax'] )
                return $r;
            echo $r;
        }


        function epl_regis_snapshot() {
            return $this->regis_meta_box();
        }


        function epl_payment_snapshot() {

            return $this->payment_meta_box();
        }


        function regis_meta_box( $post = '', $values = '' ) {
            global $regis_details, $epl_fields;
            $events = $this->ecm->the_list_of_active_events();
            $this->erm->on_admin = true;
            $this->get_values();

            $data['event_id'] = $this->event_id;

            //events, dates, times, prices, quantities

            $this->erm->dest = 'admin';

            $this->erm->set_mode( ( $GLOBALS['epl_ajax'] ) ? 'overview' : 'edit'  );
            $this->erm->setup_current_data( $this->regis_meta );

            $data['edit_mode'] = $this->edit_mode;
            $data['cart_data'] = $this->erm->show_cart();
            $data['discount_field'] = $this->erm->get_discount_field();
            $data['donation_field'] = $this->erm->get_donation_field();

            $data['cart_data'] = $this->epl->load_view( 'admin/registration/regis-cart-section', $data, true );

            $data['cart_totals'] = $this->erm->calculate_cart_totals();

            if ( $this->edit_mode && !$_POST ) {
                $r_id = $regis_details['__epl']['_regis_id'];

                if ( version_compare( epl_regis_plugin_version(), '1.4', '<' ) ) {
                    $data['cart_totals'] = current( $regis_details['__epl'][$r_id]['_events'] );
                }
                else {


                    $data['cart_totals']['money_totals']['grand_total'] = epl_get_element( '_epl_grand_total', $regis_details );

                    $data['cart_totals']['money_totals']['pre_discount_total'] = epl_get_element( '_epl_pre_discount_total', $regis_details );

                    $data['cart_totals']['money_totals']['discount_amount'] = epl_get_element( '_epl_discount_amount', $regis_details );
                    $data['cart_totals']['money_totals']['donation_amount'] =  epl_get_element( '_epl_donation_amount', $regis_details ) ;

                    if ( ($disc_code_id = epl_get_element( '_epl_discount_code_id', $regis_details, false )) !== false ) {
                        $this->edm = $this->epl->load_model( 'epl-discount-model' );
                        $dc = $this->edm->get_available_discount_codes();
                        $data['cart_totals']['money_totals']['discount_description'] = epl_get_element_m( $disc_code_id, '_epl_discount_description', $dc );
                    }

                    //explore this method
                    //$defaults = $this->epl_util->remove_array_vals( array_flip( array_keys( $epl_fields['epl_regis_payment_fields'] ) ) );
                    //$data['cart_totals']['money_totals'] = array_intersect_key( $regis_details, $defaults );
                }
            }

            //$data['money_totals'] = $data['cart_totals']['money_totals'];
            $data['money_totals'] = get_the_regis_cart_money_totals();

            //totals
            $data['cart_totals'] = $this->epl->load_view( 'admin/cart/cart-totals', $data, true );

            //registration form
            $data['attendee_info'] = $this->erm->regis_form();

            //the list of events
            $params = array(
                'input_type' => 'select',
                'input_name' => 'add_event_id',
                'id' => 'add_event_id',
                'label' => epl__( 'Event' ),
                'options' => $this->ecm->the_list_of_active_events(),
                'value' => !empty( $_GET['event_id'] ) ? $_GET['event_id'] : '',
                //'overview' => $this->edit_mode,
                'style' => 'font-size:1.3em;',
                'empty_row' => true
                    //'show_value_only' => true
            );

            $data['fields'][] = $this->epl_util->create_element( $params );

            $data['epl_action'] = epl_get_element( 'epl_action', $_REQUEST );

            $r = $this->epl->load_view( 'admin/registration/registration-attendee-meta-box', $data, true );

            if ( $GLOBALS['epl_ajax'] )
                return $r;
            echo $r;
        }


        function regis_form() {


            $post_id = intval( $_POST['post_ID'] );
            $regis_id = intval( $_POST['post_title'] );
            $event_id = intval( $_POST['event_id'] );

            $this->get_values();
            $this->erm->on_admin = true;
            $this->erm->set_mode( 'edit' )->setup_current_data( $this->regis_meta );
            return $this->erm->regis_form();
        }

        /*
         * This is fired only when necessary, big mess, gotta clean up
         */


        function get_values( $post_ID = null, $event_id = null, $add = false ) {

            $this->data['values'] = ''; //$this->ecm->get_post_meta_all( ( int ) $post_ID );

            if ( is_null( $post_ID ) || $post_ID == 0 ) {

                if ( isset( $_GET['post'] ) )
                    $post_ID = $_GET['post'];
                elseif ( isset( $_REQUEST['post_ID'] ) )
                    $post_ID = $_REQUEST['post_ID'];
            }
            $this->edit_mode = ($post_ID != '');


            $this->regis_meta = ( array ) $this->ecm->setup_regis_details( ( int ) $post_ID );
            $this->data['values'] = $this->regis_meta;
            $this->post_ID = intval( $post_ID );

            if ( $_POST && $GLOBALS['epl_ajax'] ) {
                $this->regis_id = epl_get_element( 'post_title', $_POST, $this->regis_meta['__epl']['_regis_id'] );
                $this->event_id = !is_null( $event_id ) ? $event_id : intval( epl_get_element( 'event_id', $_POST, epl_get_element( 'add_event_id', $_POST ) ) );
                //return;
            }
            else {

                $this->regis_id = $this->regis_meta['__epl']['_regis_id'];
                $this->event_id = key( ( array ) $this->regis_meta['_epl_events'] );
            }

            if ( !$this->event_id )
                $this->event_id = key( ( array ) $this->regis_meta['__epl'][$this->regis_id]['_events'] );

            $this->event_meta = ( array ) $this->ecm->setup_event_details( $this->event_id );

            //if a brand new regis, set up minimum structure.
            if ( empty( $this->regis_meta['__epl'] ) || $this->regis_meta['__epl']['_regis_id'] == '' ) {

                $this->regis_meta['__epl']['_regis_id'] = $this->regis_id;
                $this->regis_meta['__epl']['post_ID'] = $this->post_ID;
                $this->regis_meta['__epl'][$this->regis_id] = array( );
            }

            $secondary_action = array(
                'process_cc',
                'get_cc_form',
                'epl_regis_snapshot',
                'send_email',
                'epl_payment_snapshot',
                'update_payment_details',
                'send_waitlist_approval_email'
            );
            $secondary_action = in_array( $_POST['epl_action'], $secondary_action );


            if ( $_POST && isset( $_POST['post_ID'] ) && !$secondary_action ) {

                $this->regis_id = epl_get_element( 'post_title', $_POST, $this->regis_meta['__epl']['_regis_id'] );
                $this->event_id = epl_get_element( '_epl_events', $_POST, false ) ? array_flip( ( array ) $_POST['_epl_events'] ) : epl_get_element( 'event_id', $_POST );

                $this->regis_meta['__epl']['_regis_id'] = $this->regis_id;
                $this->regis_meta['__epl']['post_ID'] = $this->post_ID;
                $this->regis_meta['__epl']['_epl_regis_status'] = epl_get_element( '_epl_regis_status', $_POST, $this->regis_meta['__epl']['_epl_regis_status'] );
                $this->regis_meta['__epl']['_epl_waitlist_status'] = epl_get_element( '_epl_waitlist_status', $_POST, $this->regis_meta['__epl']['_epl_waitlist_status'] );

                $this->regis_meta['__epl'][$this->regis_id] = array( );
                $this->regis_meta['__epl'][$this->regis_id]['_dates'] = array( );

                $available_fields = ( array ) $this->ecm->get_list_of_available_fields();

                if ( !$add ) {

                    $this->regis_meta['__epl'][$this->regis_id]['_dates']['_epl_start_date'] = epl_get_element( '_epl_start_date', $_POST, array( ) );
                    $this->regis_meta['__epl'][$this->regis_id]['_dates']['_epl_start_time'] = epl_get_element( '_epl_start_time', $_POST, array( ) );
                    $this->regis_meta['__epl'][$this->regis_id]['_dates']['_att_quantity'] = epl_get_element( '_att_quantity', $_POST, array( ) );
                    $this->regis_meta['__epl'][$this->regis_id]['_dates']['_epl_discount_code'] = epl_get_element( '_epl_discount_code', $_POST, array( ) );
                    $this->regis_meta['__epl'][$this->regis_id]['_epl_donation_amount'] = epl_get_element( '_epl_donation_amount', $_POST, array( ) );

                    // TODO - don't like this at all
                    if ( epl_get_element( '_epl_payment_method', $_POST ) )
                        $this->regis_meta['__epl'][$this->regis_id]['_dates']['_epl_payment_method'][0] = epl_get_element( '_epl_payment_method', $_POST, array( ) );


                    $this->regis_meta['__epl'][$this->regis_id]['_attendee_info'] = array_intersect_key( $_POST, $available_fields );

                    if ( isset( $_POST['deleted_event'] ) )
                        $this->regis_meta['__epl'][$this->regis_id]['_old_attendee_info'] = $this->data['values']['__epl'][$this->regis_id]['_attendee_info'];

                    $this->regis_meta['__epl'][$this->regis_id]['_events'] = array_flip( epl_get_element( '_epl_events', $_POST, array( ) ) );
                }else
                    $this->regis_meta['__epl'][$this->regis_id]['_events'] = array_flip( ( array ) epl_get_element( 'add_event_id', $_POST ) );
            }

            $this->regis_meta = apply_filters('epl_cerm__get_values__regis_meta', $this->regis_meta);

            //exit;
            
        }


        function add_event() {

            $event_id = intval( $_POST['add_event_id'] );
            $this->get_values( null, $event_id, true );

            $data['event_id'] = $event_id;

            $this->erm->on_admin = true;
            $this->erm->set_mode( 'edit' )->setup_current_data( $this->regis_meta );

            $data['cart_data'] = $this->erm->show_cart();
            $data['mode'] = 'add';

            return $this->epl->load_view( 'admin/registration/regis-cart-section', $data, true );
        }


        function add_meta_boxes() {
            $this->get_values();
            add_meta_box( 'epl-payment-meta-box', epl__( 'Payment Information' ), array( $this, 'payment_meta_box' ), self::post_type, 'normal', 'core' );
            add_meta_box( 'epl-regis-meta-box', epl__( 'Registration Information' ), array( $this, 'regis_meta_box' ), self::post_type, 'normal', 'core' );
            add_meta_box( 'epl-regis-action-meta-box', epl__( 'Other Options' ), array( $this, 'action_meta_box' ), self::post_type, 'side', 'low' );
        }


        function get_cc_form() {

            $this->get_values();

            //events, dates, times, prices, quantities
            $this->erm->set_mode( ( $GLOBALS['epl_ajax'] ) ? 'overview' : 'edit'  );
            $this->erm->setup_current_data( $this->regis_meta );

            $_f = epl_cc_billing_fields();

            $gateway_info = $this->erm->get_gateway_info( $_REQUEST['gateway_id'] );

            $data['post_ID'] = $_REQUEST['post_ID'];
            $data['gateway_id'] = $_REQUEST['gateway_id'];

            if ( !$this->erm->has_selected_cc_payment( $gateway_info ) ) {

                $r = epl__( 'This is not a credit card gateway' );
            }
            else {

                $accepted_cards = $gateway_info['_epl_accepted_cards'];

                //Temp solution
                foreach ( $_f['epl_cc_billing_fields']['_epl_cc_card_type']['options'] as $k => $v ) {
                    if ( !in_array( $k, $accepted_cards ) )
                        unset( $_f['epl_cc_billing_fields']['_epl_cc_card_type']['options'][$k] );
                }

                $_field_args = array(
                    'section' => $_f['epl_cc_billing_fields'],
                    'fields_to_display' => array_keys( $_f['epl_cc_billing_fields'] ),
                    'meta' => array( '_view' => 0, '_type' => 'ind', 'value' => $_POST )
                );


                $data['_f'] = $this->epl_util->render_fields( $_field_args );

                $data['cc_form'] = $this->epl->load_view( 'admin/registration/regis-cc-form', $data, true );

                $r = $data['cc_form'];
            }

            if ( $GLOBALS['epl_ajax'] )
                return $r;
            echo $r;
        }

        /*
         * some of this stuff is repeated in front controller.  TODO - combine
         */


        function process_cc() {
            global $cart_totals;
            $this->get_values();

            //events, dates, times, prices, quantities
            $this->erm->set_mode( ( $GLOBALS['epl_ajax'] ) ? 'overview' : 'edit'  );
            $this->erm->setup_current_data( $this->regis_meta );

            $_f = epl_cc_billing_fields();

            $gateway_info = $this->erm->get_gateway_info( $_REQUEST['gateway_id'] );

            $egm = $this->epl->load_model( 'epl-gateway-model' );

            $pay_type = $gateway_info['_epl_pay_type'];

            $is_cc = ($gateway_info['_epl_pay_type'] == '_pp_pro'
                    || $gateway_info['_epl_pay_type'] == '_auth_net_aim'
                    || $gateway_info['_epl_pay_type'] == '_firstdata'
                    || $gateway_info['_epl_pay_type'] == '_qbmc') ? true : false;


            if ( $pay_type == '_pp_pro' ) {
                $r = $egm->paypal_pro_process();
            }
            elseif ( $pay_type == '_auth_net_aim' ) {
                $r = $egm->authnet_aim_process();
            }
            elseif ( $pay_type == '_firstdata' ) {
                $r = $egm->firstdata_process();
            }

            if ( $r === true ) {
                $this->epl->epl_util->set_response_param( 'cc_processed', 1 );
            }

            $this->edbm->reset_table_for_registration( $_REQUEST['post_ID'] );
            if ( $GLOBALS['epl_ajax'] )
                return $r;
            echo $r;
        }


        function payment_meta_box() {

            global $event_details, $wpdb;

            $this->get_values();

            unset( $this->epl_fields['epl_regis_payment_fields']['_epl_waitlist_status'] );
            unset( $this->epl_fields['epl_regis_payment_fields']['_epl_grand_total'] );
            unset( $this->epl_fields['epl_regis_payment_fields']['_epl_balance_due'] );

            if ( $this->epl_action != 'epl_payment_snapshot' )
                unset( $this->epl_fields['epl_regis_payment_fields']['_epl_regis_status'] );

            $this->temp_set_payment_method_id();


            $epl_fields_to_display = array_keys( $this->epl_fields['epl_regis_payment_fields'] );

            $_field_args = array(
                'section' => $this->epl_fields['epl_regis_payment_fields'],
                'fields_to_display' => $epl_fields_to_display,
                'meta' => array( '_view' => 3, '_type' => 'row', 'value' => array( ) )
            );



            $data['epl_regis_payment_fields'] = $this->epl_util->render_fields( $_field_args );


            if ( $GLOBALS['epl_ajax'] )
                $data['save_button'] = true;

            $data['edit_mode'] = $this->edit_mode;
            $data['post_ID'] = $this->post_ID;
            $data['event_id'] = $this->event_id;


            $data['regis_notes'] = $wpdb->get_results(
                    $wpdb->prepare( "SELECT meta_id, meta_key, meta_value 
                    FROM {$wpdb->postmeta} 
                        WHERE meta_key = '_epl_regis_note' AND post_id = %d order by meta_id DESC", $this->post_ID ) );

            $r = $this->epl->load_view( 'admin/registration/regis-payment-meta-box', $data, true );

            if ( $GLOBALS['epl_ajax'] )
                return $r;
            echo $r;
        }


        function action_meta_box() {

            global $event_details, $wpdb;

            $epl_fields_to_display = array(
                '_epl_regis_status' => '_epl_regis_status',
                '_epl_waitlist_status' => '_epl_waitlist_status'
            );

            if ( !epl_is_waitlist_record() )
                unset( $epl_fields_to_display['_epl_waitlist_status'] );

            $_field_args = array(
                'section' => $this->epl_fields['epl_regis_payment_fields'],
                'fields_to_display' => $epl_fields_to_display,
                'meta' => array( '_view' => 3, '_type' => 'row', 'value' => $this->data['values'] )
            );

            $_field_args = apply_filters('epl_cerm__action_meta_box__field_args', $_field_args);
            $data['epl_regis_payment_fields'] = $this->epl_util->render_fields( $_field_args );


            $data += $this->get_waitlist_info();

            if ( $GLOBALS['epl_ajax'] )
                $data['save_button'] = true;

            $data['edit_mode'] = $this->edit_mode;
            $data['post_ID'] = $this->post_ID;
            $data['event_id'] = $this->event_id;


            $r = $this->epl->load_view( 'admin/registration/regis-actions-meta-box', $data, true );

            if ( $GLOBALS['epl_ajax'] )
                return $r;
            echo $r;
        }

        /* TODO temp solution until everyone is switched over to v1.3+ */


        function temp_set_payment_method_id() {

            if ( ($current_payment_id = ( int ) epl_get_element( '_epl_payment_method', $this->regis_meta )) != 0 ) {
                
            }
            elseif ( ($current_payment_id = epl_get_element( '_epl_payment_profile_id', $this->regis_meta )) != '' ) {
                
            }
            else {
                $this->erm->set_mode( 'edit' )->setup_current_data( $this->regis_meta );
                $current_payment_id = $this->erm->get_payment_profile_id();
            }

            $this->data['values']['_epl_payment_method'] = $current_payment_id;
            $this->regis_meta['_epl_payment_method'] = $current_payment_id;
        }


        function get_waitlist_info() {
            global $event_details;

            $data = array( );
            $data['send_waitlist_approval_email'] = '';
            $data['waitlist_status'] = '';
            $data['waitlist_email_time'] = '';

            //when the waitlist status is approved, send email
            if ( !empty( $this->regis_meta['_epl_waitlist_status'] ) && $this->regis_meta['_epl_waitlist_status'] == 10 && $_POST )
                $this->send_waitlist_approval_email();

            if ( !epl_is_waitlist_record() )
                return $data;

            $data['send_waitlist_approval_email'] = epl_anchor( '#', epl__( 'Send Waitlist Approval Email' ), '_blank', 'class="send_waitlist_approval_email button-primary" data-post_ID="' . $this->post_ID . '" data-event_id="' . $this->event_id . '" ' );

            $wl_time_limit = epl_waitlist_approved_regis_time_limit();

            $data['waitlist_status'] = (isset( $this->regis_meta['_epl_waitlist_status'] )) ? $this->ind_fields['_epl_waitlist_status']['options'][$this->regis_meta['_epl_waitlist_status']] : '';
            $data['waitlist_email_time'] = ($wl_email_time = epl_get_element( '_epl_waitlist_email_time', $this->regis_meta )) ? epl__( 'Email Sent on' ) . ' ' . date_i18n( "Y-m-d H:i", $wl_email_time ) : '';

            return $data;
        }


        function sort_regis_list() {
            
        }

        /*
         * Data for the modified cols
         */


        function payment_info_box( $post_ID = null ) {
            global $regis_details;
            if ( is_null( $post_ID ) )
                $post_ID = ( int ) $_POST['post_ID'];


            if ( $GLOBALS['epl_ajax'] || !isset( $this->regis_meta ) ) {
                $regis_details = $this->regis_meta = $this->ecm->setup_regis_details( $post_ID, true );
            }


            $this->temp_set_payment_method_id();

            $data['post_ID'] = $post_ID;
            //$data['event_id'] = $this->event_id;
            $data['regis_status_id'] =  $this->regis_meta['_epl_regis_status'] ;
            $data['regis_status'] = (isset( $this->regis_meta['_epl_regis_status'] )) ? $this->ind_fields['_epl_regis_status']['options'][$this->regis_meta['_epl_regis_status']] : '';

            $data['payment_method'] = (isset( $this->regis_meta['_epl_payment_method'] ) && $this->regis_meta['_epl_payment_method'] != '') ? $this->ind_fields['_epl_payment_method']['options'][$this->regis_meta['_epl_payment_method']] : '';

            $data += $this->get_waitlist_info();

            $grand_total = get_the_regis_total_amount( false );  //epl_get_formatted_curr( epl_nz( $this->regis_meta['_epl_grand_total'], 0 ) );
            $amount_paid = epl_get_formatted_curr( epl_nz( $this->regis_meta['_epl_payment_amount'], 0 ) );


            $data['grand_total'] = epl_get_formatted_curr( $grand_total, null, true );

            $href = add_query_arg( array( 'epl_action' => 'epl_payment_snapshot', 'post_ID' => $post_ID ), epl_get_url() );

            $data['snapshot_link'] = '<a data-post_id = "' . $post_ID . '" class="epl_payment_snapshot" href="#"><img src="' . EPL_FULL_URL . 'images/application_view_list.png" /> </a>';


            //$data['snapshot_link'] = '<img id = "' . $post_ID . '" class="epl_payment_snapshot" src="' . EPL_FULL_URL . 'images/application_view_list.png" />';


            $data['status_class'] = 'epl_status_pending';

            if ( $this->regis_meta['_epl_regis_status'] == 1 )
                $data['status_class'] = 'epl_status_incomplete';
            elseif ( $this->regis_meta['_epl_regis_status'] == 5 )
                $data['status_class'] = 'epl_status_paid';
            elseif ( $this->regis_meta['_epl_regis_status'] == 10 )
                $data['status_class'] = 'epl_status_cancelled';
            elseif ( $this->regis_meta['_epl_regis_status'] == 15 )
                $data['status_class'] = 'epl_status_refunded';
            elseif ( $this->regis_meta['_epl_regis_status'] == 20 )
                $data['status_class'] = 'epl_status_waitlist';


            return $this->epl->load_view( 'admin/registration/regis-list-payment-info', $data, true );
        }


        function add_new_columns( $current_columns ) {

            $new_columns['cb'] = '<input type="checkbox" />';

            //$new_columns['id'] = __( 'ID' );
            $new_columns['attendees'] = epl__( 'Attendees' );
            $new_columns['title'] = epl__( 'Registration ID' );
            $new_columns['event'] = epl__( 'Event' );
            //$new_columns['num_attendees'] = epl__( '#' );
            $new_columns['payment_status'] = epl__( 'Registration Status' );
            //$new_columns['payment'] = epl__( 'Payment Status' );
            //$new_columns['images'] = __( 'Images' );
            //$new_columns['author'] = __( 'Author' );
            //$new_columns['categories'] = __( 'Categories' );
            //$new_columns['events_planner_categories'] = __( 'Categories' );
            //$new_columns['tags'] = __( 'Tags' );

            $new_columns['date'] = epl__( 'On' );
            $new_columns = apply_filters('epl_cerm__add_new_columns__new_columns', $new_columns);
            return $new_columns;
        }


        function sortable_columns() {
            return array(
                'event' => '_epl_event_id',
                'payment_status' => '_epl_regis_status'
            );
        }


        function column_data( $column_name, $post_ID ) {

            global $epl_fields, $event_details, $regis_details;

            $this->regis_meta = $this->ecm->setup_regis_details( $post_ID );
            $regis_id = $this->regis_meta['__epl']['_regis_id'];
            $event_id = '';
            $event_name = '';
            $num_attendees = '';

            if ( isset( $this->regis_meta['_epl_events'] ) && !empty( $this->regis_meta['_epl_events'] ) ) {
                $event_id = key( $this->regis_meta['_epl_events'] );
                $this->ecm->setup_event_details( $event_id );
                //$event_name = get_post( $event_id )->post_title;
                $event_name = $event_details['post_title'];
                $href = add_query_arg( array( 'epl_action' => 'epl_attendee_list', 'epl_download_trigger' => 1, 'event_id' => $event_id ), epl_get_url() );
                $xl_href = add_query_arg( array( 'epl_action' => 'epl_excel_attendee_list', 'epl_download_trigger' => 1, 'post_ID' => $post_ID, 'event_id' => $event_id ), epl_get_url() );
                //$event_name = '<a href="' . $href . '"><img src="' . EPL_FULL_URL . 'images/doc_excel_csv.png" /></a> <a data-post_id = "' . $post_ID . '" data-event_id="' . $event_id . '" class="epl_event_snapshot" href="#"><img id = "' . $event_id . '"  src="' . EPL_FULL_URL . 'images/application_view_list.png" /> </a><span class="">' . $event_name . '</span>';
                //$xevent_name = '<a href="' . $xl_href . '"><img src="' . EPL_FULL_URL . 'images/doc_excel_csv.png" /></a>';
                //$event_name = $xevent_name . '<a href="' . $href . '"><img src="' . EPL_FULL_URL . 'images/doc_excel_csv.png" /></a><span class="event_name">' . $event_name . '</span>';
                $xevent_name = '<span class="event_name1">' . $event_name . '</span><br />'; //<a href="' . $xl_href . '">Excel</a>, ';
                $event_name = $xevent_name . '<a href="' . $href . '">CSV</a>';
            }


            switch ( $column_name )
            {
                case 'attendees':

                    if ( is_array( $this->regis_meta['__epl'][$regis_id]['_attendee_info'] ) && is_array( $this->personal_field_ids ) ) {
                        $d = array_intersect_key( $this->regis_meta['__epl'][$regis_id]['_attendee_info'], $this->personal_field_ids );

                        $r = array( );

                        $fn = epl_array_flatten( current( $d ) );
                        $ln = epl_array_flatten( next( $d ) );

                        foreach ( ( array ) $fn as $k => $v ) {

                            $r[] = $v . ' ' . stripslashes_deep( html_entity_decode( htmlspecialchars_decode( epl_get_element( $k, $ln ) ), ENT_QUOTES, 'UTF-8' ) );
                        }

                        echo implode( '<br>', array_unique( $r ) );
                    }
                    break;

                case 'id':
                    echo $post_ID;
                    break;


                case 'event':

                    //TODO move to view
                    $data = array( );
                    echo '<table class="epl_regis_list_regis_details">';

                    foreach ( ( array ) $this->regis_meta['__epl'][$regis_id]['_events'] as $event_id => $totals ) {
                        setup_event_details( $event_id );
                        $data['event_name'] = $event_details['post_title'];
                        $data['quantity'] = epl_get_element( $event_id, epl_get_element_m( 'total', '_att_quantity', $totals ), 0 );
                        if ( $data['quantity'] == 0 ) { //1.3 fix
                            foreach ( $this->regis_meta['__epl'][$regis_id]['_dates']['_att_quantity'][$event_id] as $pr => $q )
                                $data['quantity'] += array_sum( $q );
                        }
                        $link = epl_anchor( admin_url( 'post.php?post=' . $event_details['ID'] . '&action=edit' ), $event_details['post_title'] );
                        echo "<tr><td>{$link}</td><td class='qty'>{$data['quantity']}</td></tr>";
                    }
                    echo '</table>';

                    break;
                case 'num_attendees':
                    /*
                      $this->get_values( $post_ID );
                      $data['event_id'] = $event_id;

                      //events, dates, times, prices, quantities
                      $data['cart_data'] = $this->earm->__in( $this->event_meta + $this->regis_meta )->show_cart();
                      echo $this->epl->load_view( 'admin/registration/regis-list-cart-section', $data, true );
                     */
                    $num_attendees = $this->regis_meta['_total_att_' . $event_id];

                    if ( epl_is_waitlist_record() ) {


                        foreach ( $this->regis_meta as $key => $value ) {
                            if ( strpos( $key, "_total_waitlist_att_" ) !== false ) {
                                $num_attendees = $value . ' (' . epl__( 'waitlist' ) . ')';
                                break;
                            }
                        }
                    }

                    echo "<span class='num_attendees'>$num_attendees</span> ";

                    if ( $num_attendees > 0 ) {
                        $href = add_query_arg( array( 'epl_action' => 'epl_regis_snapshot', 'post_ID' => $post_ID, 'event_id' => $event_id ), epl_get_url() );

                        echo ' <a data-post_id = "' . $post_ID . '" data-event_id="' . $event_id . '" class="epl_regis_snapshot" href="#"><img src="' . EPL_FULL_URL . 'images/application_view_list.png" /> </a>';
                    }




                    echo epl_get_send_email_button( $post_ID, $event_id, true );

                    break;
                case 'payment_status':

                    $payment_info = $this->payment_info_box( $post_ID );

                    echo $payment_info;

                    $notes = epl_get_element( '_epl_regis_note', $this->regis_meta, null );

                    if ( $notes ) {
                        $notes = get_post_meta( $post_ID, '_epl_regis_note', false );
                        foreach ( $notes as $note ) {
                            $d = date_i18n( 'Y-m-d H:i', $note['timestamp'] );
                            echo "<p>{$note['action']}<span style='float:right'>{$d}</span></p>";
                        }
                    }

                    break;
                case 'payment':

                    echo "Payment Info";

                    break;
                default:
                    break;
            } // end switch
        }

    }

}