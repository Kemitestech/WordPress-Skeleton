<?php

if ( !class_exists( 'EPL_Event_Manager' ) ) {

    class EPL_Event_Manager extends EPL_Controller {


        function __construct() {

            parent::__construct();

            epl_log( 'init', get_class() . " initialized" );

            $this->pricing_type = 0;
            $this->data['values'] = array();


            $this->ecm = $this->epl->load_model( 'epl-common-model' );
            $this->erm = $this->epl->load_model( 'epl-registration-model' );
            $this->erptm = $this->epl->load_model( 'epl-report-model' );
            $post_ID = '';
            if ( isset( $_GET['post'] ) )
                $post_ID = $_GET['post'];
            elseif ( isset( $_POST['post_ID'] ) )
                $post_ID = $_POST['post_ID'];

            $this->edit_mode = (isset( $_POST['post_action'] ) && $_REQUEST['post_action'] == 'edit' || (isset( $_GET['action'] ) && $_GET['action'] == 'edit'));

            //if ( $this->edit_mode ) {
            if ( $post_ID != '' ) {
                $this->data['values'] = $this->ecm->setup_event_details( ( int ) $post_ID, false, true );
                $this->event_id = $post_ID;
            }
            global $epl_fields;

            $this->pass_to_all_views = array( 'event_id' => $post_ID );

            $this->epl->load_config( 'event-fields' );
            $this->epl->load_config( 'form-fields' );


            $this->fields = $epl_fields;
            $this->override_data = get_option( '_epl_override' );

            $this->epl_fields = $this->epl_util->combine_array_keys( $this->fields );

            if ( isset( $_REQUEST['epl_download_trigger'] ) || ($GLOBALS['epl_ajax'] ) ) {
                $this->run();
            }
            elseif ( epl_get_element( 'page', $_GET ) == 'epl_event_manager' ) {
                add_action( 'admin_notices', array( $this, 'manage_events_2' ) );
            }
            else {

                $this->erm->event_snapshot( $post_ID );

                add_action( 'default_title', array( $this, 'pre' ) );
                //add_filter( 'enter_title_here', array( $this, 'pre' ) );
                add_action( 'add_meta_boxes', array( $this, 'epl_add_meta_boxes' ) );
                add_action( 'save_post', array( $this, 'save_postdata' ) );

                //post list manage screen columns - extra columns
                add_filter( 'manage_edit-epl_event_columns', array( $this, 'add_new_epl_columns' ) );
                //post list manage screen - column data
                add_action( 'manage_epl_event_posts_custom_column', array( $this, 'epl_column_data' ), 10, 2 );
                //sorting
                add_filter( 'manage_edit-epl_event_sortable_columns', array( $this, 'add_new_epl_columns' ), 10 );

                add_filter( 'post_row_actions', array( $this, 'add_post_row_action' ), 10, 2 );
                //adjust list manage screen - table header widths
                add_action( 'admin_head', array( $this, 'admin_table_header' ) );
                //add category dropdown to list manage screen
                add_action( 'restrict_manage_posts', array( $this, 'restrict_events_by_category' ), 10, 2 );
                add_filter( 'parse_query', array( $this, 'convert_restrict_events' ) );

                add_filter( 'request', array( $this, 'event_column_order_by' ) );
                add_action( 'admin_init', array( $this, 'admin_init' ) );
                add_action( 'load-edit.php', array( $this, 'trashed_redirect' ) );
            }
        }


        function admin_init() {

            add_action( 'wp_trash_post', array( $this, 'manage_events_2' ) );

            //works, using instead of untrash_post
            add_action( 'untrashed_post', array( $this, 'manage_events_2' ) );
        }


        function run() {

            //get_remote_help();


            if ( isset( $_POST['epl_load_feedback_form'] ) && isset( $_POST['epl_load_feedback_form'] ) == 1 ) {
                global $current_user;
                get_currentuserinfo();

                $data = array();
                $data['name'] = $current_user->first_name . ' ' . $current_user->last_name;
                $data['email'] = $current_user->user_email;
                $data['section'] = $_POST['section'];

                $r = $this->epl->load_view( 'admin/feedback-form', $data, true );
            }
            elseif ( $_REQUEST['epl_action'] == 'epl_attendee_list' ) {

                //$r = $this->ecm->epl_attendee_list( isset( $_REQUEST['table_view'] ) );
            }
            elseif ( $_REQUEST['epl_action'] == 'epl_pricing_type' ) {

                $this->pricing_type = ( int ) $_REQUEST['_epl_pricing_type'];
                $r = $this->time_price_section();
            }
            elseif ( $_REQUEST['epl_action'] == 'load_fullcalendar' ) {

                $r = $this->fullcalendar();
            }
            elseif ( $_REQUEST['epl_action'] == 'import_discount' ) {

                $r = $this->discount_section( true );
            }
            elseif ( $_REQUEST['epl_action'] == 'bulk_action' ) {

                $r = $this->bulk_action( true );
            }
            elseif ( $_POST['epl_action'] == 'recurrence_preview' || $_POST['epl_action'] == 'recurrence_process' ) {
                $this->r_mode = $_POST['epl_action'];
                $this->ercm = $this->epl->load_model( 'epl-recurrence-model' );
                $r = $this->ercm->recurrence_dates_from_post( $this->fields, $this->data['values'], $this->r_mode );
            }

            echo $this->epl_util->epl_response( array( 'html' => $r ) );
            die();
        }


        function trashed_redirect() {
            $screen = get_current_screen();

            if ( 'edit-epl_event' == $screen->id && epl_get_setting( 'epl_event_options', 'epl_admin_event_list_version', 2 ) == 2 ) {
                if ( isset( $_GET['trashed'] ) && intval( $_GET['trashed'] ) > 0 ) {
                    $url = admin_url( 'edit.php?post_type=epl_event&page=epl_event_manager&trashed=1' );
                    wp_redirect( $url );
                    exit();
                }
            }
        }


        function bulk_action() {
            if ( empty( $_POST['event_ids'] ) )
                return epl__( 'Please select at least one event.' );

            if ( $_POST['what'] == 'change_event_status' ) {
                $event_ids = $_POST['event_ids'];
                $event_status = $_POST['_epl_event_status'];
                foreach ( $event_ids as $event_id )
                    update_post_meta( $event_id, '_epl_event_status', $event_status );

                return epl__( 'Updated.  Please refresh to see the change.' );
            }
            return '';
        }


        function manage_events_2() {

            $current_filter = current_filter();

            wp_enqueue_script( 'ColumnFilterWidgets', $this->epl->load_asset( 'js/ColumnFilterWidgets.js' ), array( 'jquery' ) );

            //if ( false !== ( $manage_events_list = get_transient( '_epl_transient__manage_events_list' ) ) ) {
            //  echo $manage_events_list;
            // return null;
            //}
            $data['event_list'] = $this->ecm->events_list( array( 'show_past' => 1, 'draft' => true ) );

            $r = $this->epl->load_view( 'admin/events/event-table', '', true );
            //set_transient( '_epl_transient__manage_events_list', $r, 60 * 60 );
            echo $r;
        }


        function event_column_order_by( $vars ) {
            if ( isset( $vars['orderby'] ) && 'Date' == $vars['orderby'] ) {

                $this->ecm->_adjust_available_dates();
                $this->ecm->_setup_event_display_order();

                $vars = array_merge( $vars, array(
                    'meta_key' => '_epl_event_sort_order',
                    'orderby' => 'meta_value_num'
                        ) );
            }

            return $vars;
        }


        //adjustments to manage events table header widths
        function admin_table_header() {

            echo '<style type="text/css">';
            echo '.wp-list-table .column-id { width: 5%; }';
            echo '.wp-list-table .column-title { width: 35%; }';
            echo '.wp-list-table .column-author { width: 17%; }';
            echo '.wp-list-table .column-status { width: 10%; }';
            echo '.wp-list-table .column-date { width: 10%; }';
            echo '.wp-list-table .column-cb { width: 3%; }';
            echo '.wp-list-table .column-location { width: 20; }';
            echo '</style>';
        }


        function restrict_events_by_category() {
            global $typenow;
            $post_type = 'epl_event';
            $taxonomy = 'epl_event_categories';
            if ( $typenow == $post_type ) {
                $selected = isset( $_GET[$taxonomy] ) ? $_GET[$taxonomy] : '';
                $info_taxonomy = get_taxonomy( $taxonomy );
                wp_dropdown_categories( array(
                    'show_option_all' => __( "Show All Categories" ),
                    'taxonomy' => $taxonomy,
                    'name' => $taxonomy,
                    'orderby' => 'name',
                    'selected' => $selected,
                    'show_count' => true,
                    'hide_empty' => true,
                ) );
            };
        }


        function convert_restrict_events( $query ) {
            global $pagenow;
            $post_type = 'epl_event';
            $taxonomy = 'epl_event_categories';

            if ( $pagenow == 'edit.php' && isset( $query->query_vars['post_type'] ) && $query->query_vars['post_type'] == $post_type && isset( $query->query_vars[$taxonomy] ) && is_numeric( $query->query_vars[$taxonomy] ) && $query->query_vars[$taxonomy] != 0 ) {
                $term = get_term_by( 'id', $query->query_vars[$taxonomy], $taxonomy );
                $query->query_vars[$taxonomy] = $term->slug;
            }
        }


        //adding custom action links for snapshot display
        function add_post_row_action( $actions, $post ) {

            if ( $post->post_type == "epl_event" ) {

                $actions['epl_event_snapshot'] = epl_anchor( $_SERVER['PHP_SELF'] . '&epl_action=epl_event_snapshot&event_id=' . $post->ID, epl__( 'Snapshot' ), '_blank', "class='epl_event_snapshot' data-event_id = '" . $post->ID . "'" );
            }
            return $actions;
        }


        function pre( $title ) {

//doing this because of no title is entered, the whole post will get messed up, in some installs.
            return $title = epl__( "Enter Event Name Here" );
        }


        function fullcalendar() {

            $data['parent'] = esc_attr( $_POST['parent'] );

            return $this->epl->load_view( 'admin/events/fullcalendar', $data, true );
        }


        function epl_add_meta_boxes() {
            global $epl_help_links;

            if ( $this->edit_mode ) {
                //     $this->data['values'] = $this->epl_util->get_post_meta_all( ( int ) $_GET['post'], $this->epl_fields );

                if ( isset( $this->data['values']['_epl_pricing_type'] ) )
                    $this->pricing_type = $this->data['values']['_epl_pricing_type'];
            }


            add_meta_box( 'epl-dates-meta-box', epl__( 'Dates' ), array( $this, 'event_dates_meta_box' ), "epl_event", 'normal', 'core' );

            add_meta_box( 'epl-times-meta-box', epl__( 'Times and Prices' ), array( $this, 'event_times_meta_box' ), "epl_event", 'normal', 'core' );

            add_meta_box( 'epl-other-settings-meta-box', epl__( 'Settings' ), array( $this, 'settings_sections' ), "epl_event", 'normal', 'core' );


//side boxes
            add_meta_box( 'epl-options-meta-box', epl__( 'Options' ), array( $this, 'options_meta_box' ), "epl_event", 'side', 'core' );
        }


        function save_postdata( $post_ID ) {
            //autosave or quick edit, do nothing
            if ( (defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) || empty( $_POST ) || epl_get_element( 'action', $_REQUEST ) == 'inline-save' )
                return;

            // exit;
            epl_delete_transient();
//Since these are checkboxes, they will not come in in the POST
//if it is not checked.


            update_post_meta( $post_ID, '_epl_price_forms', '' );
            update_post_meta( $post_ID, '_epl_discount_forms', '' );
            update_post_meta( $post_ID, '_epl_primary_regis_forms', '' );
            update_post_meta( $post_ID, '_epl_addit_regis_forms', '' );
            update_post_meta( $post_ID, '_epl_payment_choices', '' );
            update_post_meta( $post_ID, '_epl_event_instructor', '' );
            update_post_meta( $post_ID, '_epl_date_specific_time', '' );
            update_post_meta( $post_ID, '_epl_date_specific_price', '' );
            update_post_meta( $post_ID, '_epl_attendee_list_field', '' );
            update_post_meta( $post_ID, '_epl_weekday_specific_time', '' );

            do_action( 'epl_event_manager_controller__save_postdata__pre_save', $post_ID );

            $this->ecm->_save_postdata( array( 'post_ID' => $post_ID, 'fields' => $this->epl_fields, 'edit_mode' => $this->edit_mode ) );

            $this->ecm->_adjust_available_dates();
            $this->ecm->_setup_event_display_order();
        }


        function remove_empty_keys( $array ) {

            if ( !is_array( $array ) )
                return $array;

            $temp_arr = array();

            foreach ( $array as $k => $this->data['values'] ) {
                if ( !(is_null( $this->data['values'] ) && $this->data['values'] == '') )
                    $temp_arr[$k] = $this->data['values'];
            }

            return $temp_arr;
        }


        function event_type_section() {

            $data = array();
            $data['help_link'] = get_help_icon( array( 'section' => 'epl_event_type', 'id' => null ) );
            $data['epl_event_type'] = array();


            $epl_fields_to_create = $this->fields['epl_event_type_fields']['_epl_event_type'];

            $first = true;

            foreach ( $epl_fields_to_create['options'] as $k => $v ) {

                $field = array(
                    'input_type' => $epl_fields_to_create['input_type'],
                    'input_name' => $epl_fields_to_create['input_name'],
                    'input_slug' => '_epl_event_type',
                    'options' => array( $k => $v ),
                    'value' => (isset( $this->data['values']['_epl_event_type'] )) ? $this->data['values']['_epl_event_type'] : '',
                );

                if ( $first ) {
                    $field['default_checked'] = 1;
                    $first = false;
                }


                $data['epl_event_type'][$k] = $this->epl_util->create_element( $field, 0 );
            }

            return $this->epl->load_view( 'admin/events/event-type-section', $data, true );
        }


        function settings_sections( $param ) {

            $data['location_and_other_section'] = $this->settings_section();
            $data['registration_options_section'] = $this->registration_forms_meta_box();
            $data['other_options_section'] = $this->other_options_section();
            $data['message_section'] = $this->message_section();
            $data['report_section'] = $this->report_meta_box();
            $data['attendee_list_section'] = $this->attendee_list_meta_box();
            $data['waitlist_section'] = $this->waitlist_meta_box();
            $data['discounts_section'] = $this->discount_section();
            $data['surcharge_section'] = $this->surcharge_section();

            echo $this->epl->load_view( 'admin/events/settings-meta-box', $data, true );
        }


        function settings_section() {

            $_field_args = array(
                'section' => $this->fields['epl_other_settings_fields'],
                'fields_to_display' => array_keys( $this->fields['epl_other_settings_fields'] ),
                'meta' => array( '_view' => 3, '_type' => 'row', 'value' => $this->data['values'] )
            );

            $data['epl_general_fields'] = $this->epl_util->render_fields( $_field_args );

            return $this->epl->load_view( 'admin/events/other-settings-meta-box', $data, true );
        }


        function message_section() {

            $rows_to_display = $this->edit_mode ? count( epl_get_element( '_epl_message', $this->data['values'], 1 ) ) : 1;
            $epl_fields_to_display = array_keys( $this->fields['epl_message_fields'] );

            $_field_args = array(
                'section' => $this->fields['epl_message_fields'],
                'fields_to_display' => $epl_fields_to_display,
                'meta' => array( '_view' => 'raw', '_type' => 'row', '_rows' => $rows_to_display, 'value' => $this->data['values'] )
            );

            $data['epl_message_fields'] = $this->epl_util->render_fields( $_field_args );

            return $this->epl->load_view( 'admin/events/message-meta-box', $data, true );
        }


        function report_meta_box() {


            $data['fields'] = $this->ecm->_get_fields( 'epl_fields' );
            $data['values'] = $this->data['values'];

            if ( !epl_is_empty_array( epl_get_element( '_epl_attendee_list_field', $this->data['values'] ) ) ) {
                epl_sort_array_by_array( $data['fields'], $this->data['values']['_epl_attendee_list_field'] );
            }

            return $this->epl->load_view( 'admin/events/report-meta-box', $data, true );
        }


        function attendee_list_meta_box() {

            unset( $this->fields['epl_attendee_list_fields']['_epl_attendee_list_field'] );

            $_field_args = array(
                'section' => $this->fields['epl_attendee_list_fields'],
                'fields_to_display' => array_keys( $this->fields['epl_attendee_list_fields'] ),
                'meta' => array( '_view' => 3, '_type' => 'row', 'value' => $this->data['values'] )
            );

            $data['fields1'] = $this->epl_util->render_fields( $_field_args );

            $data['fields'] = $this->ecm->_get_fields( 'epl_fields' );
            $data['values'] = $this->data['values'];

            if ( !epl_is_empty_array( epl_get_element( '_epl_attendee_list_field', $this->data['values'] ) ) ) {
                epl_sort_array_by_array( $data['fields'], $this->data['values']['_epl_attendee_list_field'] );
            }

            return $this->epl->load_view( 'admin/events/attendee-list-meta-box', $data, true );
        }


        function waitlist_meta_box() {


            $_field_args = array(
                'section' => $this->fields['epl_waitlist_fields'],
                'fields_to_display' => array_keys( $this->fields['epl_waitlist_fields'] ),
                'meta' => array( '_view' => 3, '_type' => 'row', 'value' => $this->data['values'] )
            );

            $data['fields1'] = $this->epl_util->render_fields( $_field_args );

            $data['fields'] = $this->ecm->_get_fields( 'epl_fields' );
            $data['values'] = $this->data['values'];


            return $this->epl->load_view( 'admin/events/waitlist-meta-box', $data, true );
        }


        function event_dates_meta_box() {


            $data['epl_event_type'] = array();

            $data['event_type_section'] = $this->event_type_section();
            $data['dates_section'] = $this->dates_section();
            $data['dates_options_section'] = $this->date_options_section();
            $data['class_session_section'] = $this->class_session_section();
            $data['event_recurrence_section'] = $this->event_recurrence_meta_box();

            echo $this->epl->load_view( 'admin/events/dates-meta-box', $data, true );
        }


        function dates_section() {

            $rows_to_display = $this->edit_mode ? epl_nz( count( $this->data['values']['_epl_start_date'] ), 1 ) : 1;
            $epl_fields_to_display = array_keys( $this->fields['epl_date_fields'] );

            $_field_args = array(
                'section' => $this->fields['epl_date_fields'],
                'fields_to_display' => $epl_fields_to_display,
                'meta' => array( '_view' => 'raw', '_type' => 'row', '_rows' => $rows_to_display, 'value' => $this->data['values'] )
            );


            $data['date_fields'] = $this->epl_util->render_fields( $_field_args );

//echo "<pre class='prettyprint'>" . __LINE__ . "> " . print_r($data['date_fields'], true). "</pre>";
            $data['date_field_labels'] = $this->epl_util->extract_labels( $this->fields['epl_date_fields'] );
            $data += $this->pass_to_all_views;
            return $this->epl->load_view( 'admin/events/dates-section', $data, true );
        }


        function date_options_section() {

            $_field_args = array(
                'section' => $this->fields['epl_date_option_fields'],
                'fields_to_display' => array_keys( $this->fields['epl_date_option_fields'] ),
                'meta' => array( '_view' => 'raw', '_type' => 'ind', 'value' => $this->data['values'] )
            );

            $data['date_option_fields'] = $this->epl_util->render_fields( $_field_args );

            $r = $this->epl->load_view( 'admin/events/dates-options-section', $data, true );
//$r .= $this->capacity_meta_box();

            return $r;
        }


        function class_session_section() {


            $rows_to_display = $this->edit_mode ? count( epl_get_element( '_epl_class_session_date', $this->data['values'], 1 ) ) : 1;
            $epl_fields_to_display = array_keys( $this->fields['epl_class_session_fields'] );

            $_field_args = array(
                'section' => $this->fields['epl_class_session_fields'],
                'fields_to_display' => $epl_fields_to_display,
                'meta' => array( '_view' => 1, '_type' => 'row', '_rows' => $rows_to_display, 'value' => $this->data['values'] )
            );

            $data['class_session_fields'] = $this->epl_util->render_fields( $_field_args );
            $data['class_session_field_labels'] = $this->epl_util->extract_labels( $this->fields['epl_class_session_fields'] );

            return $this->epl->load_view( 'admin/events/class-session-section', $data, true );
        }


        /**
         * Makes the registration form selection meta box.  Called by add_meta_boxes action
         *
         * @since 1.0.0
         * @param int $post
         * @param int $values
         * @return prints html
         */
        function registration_forms_meta_box() {


            $list_of_forms = $this->ecm->_get_fields( 'epl_forms' );

            $_o = array();
            foreach ( ( array ) $list_of_forms as $form_key => $form_atts ) {
                $_o[$form_key] = $form_atts['epl_form_label'];
            }

            $_ao = $_o;

            epl_sort_array_by_array( $_o, $this->data['values']['_epl_primary_regis_forms'] );
            epl_sort_array_by_array( $_ao, $this->data['values']['_epl_addit_regis_forms'] );

            $this->fields['epl_regis_form_fields']['_epl_primary_regis_forms']['options'] = $_o;

            $this->fields['epl_regis_form_fields']['_epl_addit_regis_forms']['options'] = $_ao;

            $epl_fields_to_display = array_keys( $this->fields['epl_regis_form_fields'] );

            $_field_args = array(
                'section' => $this->fields['epl_regis_form_fields'],
                'fields_to_display' => $epl_fields_to_display,
                'meta' => array( '_view' => 3, '_type' => 'row', 'value' => $this->data['values'] )
            );

            $data['epl_forms_fields'] = $this->epl_util->render_fields( $_field_args );

            return $this->epl->load_view( 'admin/events/forms-meta-box', $data, true );
        }


        function discount_section( $import = false ) {



            if ( $import ) {
                $event_id = intval( epl_get_element( 'event_list_discount_import_dd', $_POST ) );
                $import_action = esc_html( epl_get_element( 'discount_import_action', $_POST ) );
                $old_data = $this->data['values'];

                $this->edit_mode = true;
                $this->data['values'] = $this->ecm->setup_event_details( $event_id );

                if ( $import_action == 'append' ) {

                    global $epl_fields;

                    $old_data = array_intersect_key( $old_data, $epl_fields['epl_discount_fields'] );
                    $new_data = array_intersect_key( $this->data['values'], $epl_fields['epl_discount_fields'] );

                    //Rekey the new ones so to make sure there are no collisions
                    $number_of_new_discount = count( $new_data['_epl_discount_code'] );
                    $new_keys = array();
                    for ( $i = 0; $i < $number_of_new_discount; $i++ )
                        $new_keys[] = $this->epl->epl_util->make_unique_id( 6 );
                    $new_data = epl_rekey_array( $new_data, $new_keys );

                    $this->data['values'] = array_merge_recursive( $old_data, $new_data );
                }
            }


            //the list of events
            $params = array(
                'input_type' => 'select',
                'input_name' => 'event_list_discount_import_dd',
                'id' => 'event_list_discount_import_dd',
                'label' => epl__( 'Event' ),
                'options' => $this->ecm->get_all_events(),
                //'value' => $this->event_id,
                'empty_row' => true
            );


            $data['event_list_discount_import_dd'] = $this->epl_util->create_element( $params );
            //the list of events
            $params = array(
                'input_type' => 'select',
                'input_name' => 'discount_import_action',
                'id' => 'discount_import_action',
                'label' => epl__( 'Event' ),
                'options' => array( 'replace' => epl__( 'Replace' ), 'append' => epl__( 'Append' ) )
            );


            $data['discount_import_action'] = $this->epl_util->create_element( $params );

            $rows_to_display = $this->edit_mode ? count( epl_get_element( '_epl_discount_method', $this->data['values'], 1 ) ) : 1;

            $_field_args = array(
                'section' => $this->fields['epl_discount_fields'],
                'fields_to_display' => array( '_epl_show_discount_code_input', '_epl_allow_global_discounts', '_epl_discount_input_label' ),
                'meta' => array( '_view' => 3, '_type' => 'row', '_rows' => 1, 'value' => $this->data['values'] )
            );

            unset( $this->fields['epl_discount_fields']['_epl_show_discount_code_input'] );
            unset( $this->fields['epl_discount_fields']['_epl_allow_global_discounts'] );
            unset( $this->fields['epl_discount_fields']['_epl_discount_input_label'] );

            $data['epl_discount_option_fields'] = $this->epl_util->render_fields( $_field_args );


            $list_of_forms = $this->ecm->_get_fields( 'epl_forms' );

            $_o = array();
            foreach ( ( array ) $list_of_forms as $form_key => $form_atts ) {
                $_o[$form_key] = $form_atts['epl_form_label'];
            }


            $this->fields['epl_discount_fields']['_epl_discount_forms']['options'] = $_o;

            $epl_fields_to_display = array_keys( $this->fields['epl_discount_fields'] );

            $_field_args = array(
                'section' => $this->fields['epl_discount_fields'],
                'fields_to_display' => $epl_fields_to_display,
                'meta' => array( '_view' => 'raw', '_type' => 'row', '_rows' => $rows_to_display, 'value' => $this->data['values'], 're_key' => false )
            );

            $data['epl_discount_fields'] = $this->epl_util->render_fields( $_field_args );

            return $this->epl->load_view( 'admin/events/discounts-meta-box', $data, true );
        }


        function time_price_section( $param = null ) {

            $rows_to_display = $this->edit_mode ? epl_nz( count( $this->data['values']['_epl_start_time'] ), 1 ) : 1;

            $data['epl_price_parent_time_id_key'] = (isset( $this->data['values']['_epl_price_parent_time_id'] )) ? $this->data['values']['_epl_price_parent_time_id'] : '';

            $epl_fields_to_display = array_keys( $this->fields['epl_time_fields'] );

            $_field_args = array(
                'section' => $this->fields['epl_time_fields'],
                'fields_to_display' => $epl_fields_to_display,
                'meta' => array( '_view' => 'raw', '_type' => 'row', '_rows' => $rows_to_display, 'value' => $this->data['values'] )
            );

            $data['time_fields'] = $this->epl_util->render_fields( $_field_args );
            $data['time_field_labels'] = $this->epl_util->extract_labels( $this->fields['epl_time_fields'] );


//when a new event is opened for creation, the parent id key
//needs to be passed to a hidden field for time specific pricing type
            if ( !$this->edit_mode ) {

                /* preg_match( '/\[.+\]/', key( $data['time_fields'] ), $_first_time_key );

                  $_first_time_key = str_replace( array( '[', ']' ), '', current( ( array ) $_first_time_key ) ); */
                $_first_time_key = key( $data['time_fields'] );

                $this->fields['epl_price_fields']['_epl_price_parent_time_id']['default_value'] = $_first_time_key;
            }

            $list_of_forms = $this->ecm->_get_fields( 'epl_forms' );

            $_o = array();
            foreach ( ( array ) $list_of_forms as $form_key => $form_atts ) {
                $_o[$form_key] = $form_atts['epl_form_label'];
            }


            $this->fields['epl_price_fields']['_epl_price_forms']['options'] = $_o;

            if ( isset( $this->fields['epl_price_fields']['_epl_price_to_offset'] ) ) {
                $this->fields['epl_price_fields']['_epl_price_to_offset']['options'] = $this->data['values']['_epl_price_name'];
            }

            $rows_to_display = $this->edit_mode ? epl_nz( count( $this->data['values']['_epl_price_name'] ), 1 ) : 1;
            $epl_fields_to_display = array_keys( $this->fields['epl_price_fields'] );

            $_field_args = array(
                'section' => $this->fields['epl_price_fields'],
                'fields_to_display' => $epl_fields_to_display,
                'meta' => array( '_view' => 'raw', '_type' => 'row', '_rows' => $rows_to_display, 'value' => $this->data['values'] )
            );
            //echo "<pre class='prettyprint'>" . __LINE__ . "> " . print_r($this->fields['epl_price_fields'], true). "</pre>";
            $data['price_fields'] = $this->epl_util->render_fields( $_field_args );
            $data['price_field_labels'] = $this->epl_util->extract_labels( $this->fields['epl_price_fields'] );

            //$data['surcharge_section'] = $this->surcharge_section();

            return $this->epl->load_view( 'admin/events/time-price-' . $this->pricing_type, $data, true );
        }


        function surcharge_section() {

            $epl_fields_to_display = array_keys( $this->fields['epl_surcharge_fields'] );

            $_field_args = array(
                'section' => $this->fields['epl_surcharge_fields'],
                'fields_to_display' => $epl_fields_to_display,
                'meta' => array( '_view' => '3', '_type' => 'row', 'value' => $this->data['values'] )
            );

            $data['surcharge_fields'] = $this->epl_util->render_fields( $_field_args );
            //$data['surcharge_fields_labels'] = $this->epl_util->extract_labels( $this->fields['epl_surcharge_fields'] );

            return $this->epl->load_view( 'admin/events/surcharge-section', $data, true );
        }


        function options_meta_box() {

            $_field_args = array(
                'section' => $this->fields['epl_option_fields'],
                'fields_to_display' => array_keys( $this->fields['epl_option_fields'] ),
                'meta' => array( '_view' => 3, '_type' => 'row', 'value' => $this->data['values'] )
            );

            $data['fields'] = $this->epl_util->render_fields( $_field_args );

            $this->epl->load_view( 'admin/events/options-meta-box', $data );
        }


        function other_options_section() {

            $_field_args = array(
                'section' => $this->fields['epl_display_option_fields'],
                'fields_to_display' => array_keys( $this->fields['epl_display_option_fields'] ),
                'meta' => array( '_view' => 3, '_type' => 'row', 'value' => $this->data['values'] )
            );

            $data['fields'] = $this->epl_util->render_fields( $_field_args );

            $r = $this->epl->load_view( 'admin/events/display-options-meta-box', $data, true );
//$r .= $this->capacity_meta_box();

            return $r;
        }


        function capacity_meta_box() {

            $_field_args = array(
                'section' => $this->fields['epl_capacity_fields'],
                'fields_to_display' => array_keys( $this->fields['epl_capacity_fields'] ),
                'meta' => array( '_view' => 0, '_type' => 'ind', 'value' => $this->data['values'] )
            );

            $data['_f'] = $this->epl_util->render_fields( $_field_args );

            return $this->epl->load_view( 'admin/events/capacity-meta-box', $data, true );
        }


        function recurrence_section( $param = null ) {

            $_field_args = array(
                'section' => $this->fields['epl_recurrence_fields'],
                'fields_to_display' => array_keys( $this->fields['epl_recurrence_fields'] ),
                'meta' => array( '_view' => 0, '_type' => 'ind', 'value' => $this->data['values'] )
            );

            $data['r_f'] = $this->epl_util->render_fields( $_field_args );

            return $this->epl->load_view( 'admin/events/recurrence-fields', $data, true );
        }


        function event_recurrence_meta_box() {

            $data['recurrence_section'] = $this->recurrence_section();
            return $this->epl->load_view( 'admin/events/recurrence-meta-box', $data, true );
        }


        function event_times_meta_box() {

            $data['epl_pricing_type'] = array();

            $_field_args = array(
                'section' => $this->fields['epl_time_option_fields'],
                'fields_to_display' => array_keys( $this->fields['epl_time_option_fields'] ),
                'meta' => array( 'value' => $this->data['values'] )
            );

            $data['time_option_fields'] = $this->epl_util->render_fields( $_field_args );


            $_field_args = array(
                'section' => $this->fields['epl_price_option_fields'],
                'fields_to_display' => array_keys( $this->fields['epl_price_option_fields'] ),
                'meta' => array( 'value' => $this->data['values'] )
            );

            $data['price_option_fields'] = $this->epl_util->render_fields( $_field_args );

            $_field_args = array(
                'section' => $this->fields['epl_special_fields'],
                'fields_to_display' => array( '_epl_pricing_type' ),
                'meta' => array( '_view' => 1, '_type' => 'row', 'value' => $this->data['values'] )
            );
            if ( !$this->edit_mode )
                $data['epl_pricing_type'] = $this->epl_util->render_fields( $_field_args );


            $data['time_price_section'] = $this->time_price_section();

            echo $this->epl->load_view( 'admin/events/times-meta-box', $data, true );
        }

        /*
         * Modify the custom post type cols
         */


        function add_new_epl_columns( $current_columns ) {

            $new_columns['cb'] = '<input type="checkbox" />';


            $new_columns['title'] = epl__( 'Event Name' );
            //$new_columns['dates'] = epl__( 'Date(s)' );
//$new_columns['actions'] = epl__( 'Actions' );
            $new_columns['status'] = epl__( 'Status' );
            //$new_columns['location'] = epl__( 'Location' );
            $new_columns['start_date'] = epl__( 'Date' );
            //$new_columns['start_time'] = epl__( 'Time' );
//$new_columns['images'] = __( 'Images' );
            // $new_columns['author'] = __( 'Manager' );
//$new_columns['categories'] = __( 'Categories' );
//$new_columns['epl_categories'] = __( 'Categories' );
//$new_columns['tags'] = __( 'Tags' );
            $new_columns['id'] = __( 'ID' );
            //$new_columns['actions'] = __( 'Actions' );
//$new_columns['date'] = _x( 'Date', 'column name' );

            return $new_columns;
        }

        /*
         * Data for the modified cols
         */


        function epl_column_data( $column_name, $post_ID ) {
            global $epl_fields, $event_details, $event_snapshot;

            $this->ecm->setup_event_details( $post_ID );
            $this->erm->event_snapshot( $post_ID );

            switch ( $column_name )
            {
                case 'id':
                    echo $post_ID;
                    break;

                case 'location':
                    echo get_the_location_name();
                    break;
                case 'status':

                    $s = epl_get_event_status( true );

                    $class = 'status_' . key( $s );

                    echo "<span class='status $class'>" . current( $s ) . '</span>';
                    break;

                /*  case 'epl_categories':

                  foreach ( wp_get_object_terms( $id, 'epl_categories' ) as $tax )
                  $r[] = $tax->name;

                  echo!is_array( $r ) ? '' : implode( ", ", $r );

                  break; */
                case 'start_date':
                    $base_url = epl_get_url();
                    $event_regis_data = current( $event_snapshot );
                    $table_link_arr = array( 'epl_action' => 'view_names', 'epl_download_trigger' => 1, 'table_view' => 1, 'epl_controller' => 'epl_report_manager', 'event_id' => $event_id );
                    $csv_link_arr = array( 'epl_action' => 'epl_attendee_list', 'epl_download_trigger' => 1, 'epl_controller' => 'epl_registration', 'event_id' => $event_id );

                    if ( epl_is_empty_array( $event_regis_data ) )
                        break;
                    $counter = 1;
                    foreach ( $event_regis_data as $date_id => $date_data ):

                        $last_day = end( $event_details['_epl_start_date'] );

                        if ( $last_day > EPL_DATE && ($event_details['_epl_event_status'] == 1 && $date_data['date']['timestamp'] < EPL_DATE) || $counter > 1 )
                            continue;
                        $counter++;
                        $date = $date_data['date']['disp'];

                        $date_capacity = epl_get_element_m( $date_id, '_epl_date_capacity', $event_details );
                        $times = $date_data['time'];
                        ?>

                        <table id="event_snapshot_table" class="event_snapshot_sorting">
                            <thead>
                                <tr>
                                    <th><?php epl_e( 'Date' ); ?></th>
                                    <th><?php epl_e( 'Time' ); ?></th>
                                    <th><?php epl_e( 'Attendees' ); ?></th>
                                    <th></th>
                                </tr>

                            </thead>

                            <?php

                            foreach ( $times as $time_id => $time_data ):

                                if ( epl_is_date_level_time() && !epl_is_empty_array( $date_specifc_time ) && (!isset( $date_specifc_time[$time_id] ) || !isset( $date_specifc_time[$time_id][$date_id] )) )
                                    continue;
                                $time_capacity = epl_get_element_m( $time_id, '_epl_time_capacity', $event_details );
                                $capacity = ($time_capacity) ? $time_capacity : ($date_capacity ? $date_capacity : epl_get_element_m( $date_id, '_epl_date_per_time_capacity', $event_details ));

                                $dt_array = array(
                                    'date_id' => $date_id,
                                    'time_id' => $time_id,
                                    'event_id' => $post_ID
                                );

                                $table_link_arr = array_merge( $table_link_arr, $dt_array );
                                $csv_link_arr += $dt_array;
                                ?>

                                <tr class="epl_date_time">

                                    <td><?php echo date_i18n( 'D M d, Y', epl_get_date_timestamp( epl_get_element( $date_id, $event_details['_epl_start_date'] ) ) ); ?></td>

                                    <?php if ( epl_is_time_optonal() ): ?>
                                        <td colspan="1"> - </td>
                                    <?php else: ?>
                                        <td><?php echo $time_data['disp']; ?></td>

                                    <?php endif; ?>

                                    <td><?php echo $time_data['regis']; ?> / <?php echo ($capacity) ? $capacity : '&#8734;'; ?></td>


                                    <td>
                                        <?php

                                        echo epl_anchor( add_query_arg( array_merge( $table_link_arr, $dt_array ) + array( 'names_only' => 1 ), $base_url ), epl__( 'View Attendees' ), null, 'class="epl_view_attendee_list_table button-secondary"' );
                                        $table_link_arr['epl_action'] = 'epl_attendee_list';
                                        $table_link_arr['epl_controller'] = 'epl_registration';
                                        echo epl_anchor( add_query_arg( $table_link_arr, $base_url ), epl__( 'View Full Data' ), null, 'class="epl_view_attendee_list_table button-secondary"' );
                                        echo epl_anchor( add_query_arg( array_merge( $csv_link_arr, $dt_array ), $base_url ), epl__( 'Export CSV' ), null, 'class="button-secondary"' );

                                        //echo  epl_anchor( $_SERVER['PHP_SELF'] . '&epl_action=epl_event_snapshot&event_id=' . $post->ID, epl__( 'Snapshot' ), '_blank', "class='epl_event_snapshot' data-event_id = '" . $post->ID . "'" );
                                        ?>


                                    </td>

                                </tr>

                                <?php

                            endforeach;
                        endforeach;
                        ?>


                    </table>
                    <?php

                    break;
                default:
                    break;
                case 'actions':

                    $url_vars = array(
                        'epl_action' => 'duplicate_event',
                        'event_id' => $post_ID
                    );

                    $url = add_query_arg( $url_vars, epl_get_url() );


                    echo " <a href='epl_action=duplicate_event&event_id={$post_ID}'><img src='" . EPL_FULL_URL . "images/status_online.png' title='" . epl__( 'Attendees' ) . "' alt='" . epl__( 'Attendees' ) . "' /></a>";
                    echo " <a href='epl_action=duplicate_event&event_id={$post_ID}'><img src='" . EPL_FULL_URL . "images/doc_excel_csv.png' title='" . epl__( 'Attendees' ) . "' alt='" . epl__( 'Attendees' ) . "' /></a>";
                    break;
            } // end switch
        }

    }

}