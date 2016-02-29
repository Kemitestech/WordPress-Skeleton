<?php

class EPL_Common_Model extends EPL_Model {

    private static $instance;


    function __construct() {
        parent::__construct();
        epl_log( 'init', get_class() . " initialized" );
        global $ecm;
//$ecm = & $this;
//$this->erm = $this->epl->load_model('epl-registration-model');

        self::$instance = $this;
    }


    public static function get_instance() {
        if ( !self::$instance ) {

            self::$instance = new EPL_common_model;
        }

        return self::$instance;
    }


    function epl_get_event_data() {
        
    }


//To be able to use sorting by event date, we need
    function _adjust_available_dates() {
        global $wpdb;
        $_d = epl_nz( EPL_DATE, '' );

//the list of non ongoing events.  WHY INNER JOIN????
        /* $post_ids = $wpdb->get_col( $wpdb->prepare( "
          SELECT pm.post_id
          FROM $wpdb->postmeta pm
          INNER JOIN $wpdb->postmeta pm2 ON (pm.post_id = pm2.post_id)
          WHERE
          (pm.meta_key = '_epl_event_status' AND CAST(pm2.meta_value AS SIGNED) <> '3')
          GROUP BY pm.post_id

          " ) ); */
        $post_ids = $wpdb->get_col( "
            SELECT pm.post_id
            FROM $wpdb->postmeta pm
            WHERE
                (pm.meta_key = '_epl_event_status' AND CAST(pm.meta_value AS SIGNED) <> '3')
            GROUP BY pm.post_id

        " );

        if ( epl_is_empty_array( $post_ids ) )
            return null;

        $wpdb->query( $wpdb->prepare( "
        DELETE FROM $wpdb->postmeta
        WHERE `meta_key` = '_q__epl_start_date' and `meta_value` < %d AND post_id in (" . implode( ',', array_unique( $post_ids ) ) . ")
         ", $_d ) );
    }


    function _setup_event_display_order() {
        global $wpdb;

        $events = $wpdb->get_results( "
            SELECT p.id, p.post_title, pm.meta_key, pm.meta_value
            FROM $wpdb->posts p
            INNER JOIN $wpdb->postmeta pm ON (p.id = pm.post_id)
            WHERE pm.meta_key = '_epl_event_status' AND (pm.meta_value =1 OR pm.meta_value =3) AND post_type = 'epl_event'

        " );

        if ( $wpdb->num_rows == 0 )
            return;
        $order = array();
        $wpdb->query( "DELETE FROM $wpdb->postmeta WHERE meta_key ='_epl_event_sort_order'" );
        foreach ( $events as $row ) {

            $start_date = get_post_meta( $row->id, '_epl_start_date', true );
            $end_date = get_post_meta( $row->id, '_epl_end_date', true );
            $start_time = get_post_meta( $row->id, '_epl_start_time', true );

            foreach ( $start_date as $date_id => $timestamp ) {

                if ( $timestamp >= EPL_DATE || ($row->meta_value == 3 && epl_get_element( $date_id, $end_date, $timestamp ) >= EPL_DATE) ) {

                    $order[$row->id] = $timestamp;
                    if ( $row->meta_value == 3 )
                        $order[$row->id] = EPL_TIME;

                    if ( !epl_is_empty_array( $start_time ) ) {
                        foreach ( $start_time as $time_id => $time ) {
                            if ( $time != '' ) {
                                $timestamp = strtotime( $time, $timestamp );

                                if ( $timestamp < EPL_TIME )
                                    continue;

                                $order[$row->id] = $timestamp;
                                break 2;
                            }
                        }
                    }
                    break;
                }
            }
        }
        asort( $order );
        //$order = array_keys( $order );
        $r = '';
        if ( epl_is_empty_array( $order ) )
            return;


        //can use array_reduce but this will be faster
        foreach ( $order as $post_id => $timestamp ) {
            $r .= "($post_id,'_epl_event_sort_order', $timestamp),";
        }

        $r = substr( $r, 0, -1 );
        $r = "INSERT INTO {$wpdb->postmeta} (post_id,meta_key,meta_value) VALUES $r";

        $wpdb->query( $r );

        return;
    }


    function _delete() {

        if ( !empty( $_POST ) && check_admin_referer( 'epl_form_nonce', '_epl_nonce' ) ) {

            global $epl_fields;

            $this->scope = esc_sql( $_POST['form_scope'] );

            if ( !array_key_exists( $this->scope, $epl_fields ) )
                exit( $this->epl_util->epl_invoke_error( 1 ) );

            $this->d[$this->scope] = $this->_get_fields( $this->scope );

            $_key = $_POST['_id'];

            unset( $this->d[$this->scope][$_key] );

//if a quesiton is being deleted, we need to make sure
//that question is removed from all forms also
            if ( $this->scope == 'epl_fields' ) {
                $this->d['epl_forms'] = $this->_get_fields( 'epl_forms' );

                if ( !empty( $this->d['epl_forms'] ) ) {

                    foreach ( $this->d['epl_forms'] as $form_id => $form_data ) {

                        if ( is_array( $form_data['epl_form_fields'] ) ) {

                            $_tmp_key = array_search( $_key, $form_data['epl_form_fields'] );

                            if ( $_tmp_key !== false ) {
                                unset( $this->d['epl_forms'][$form_id]['epl_form_fields'][$_tmp_key] );
                            }
                        }
                    }

                    update_option( 'epl_forms', $this->d['epl_forms'] );
                }
            }

//epl_log( 'debug', "<pre>" . print_r( $this->d[$this->scope], true ) . "</pre>" );

            update_option( $this->scope, $this->d[$this->scope] );

            return true;
        }
        return false;
    }


    function _save() {

        if ( !empty( $_POST ) && check_admin_referer( 'epl_form_nonce', '_epl_nonce' ) ) {
            global $epl_fields;

//tells us which form the data comes from
            $this->scope = esc_sql( $_POST['form_scope'] );

//Check to see if this is a valid scope.  All forms require a config array
            if ( !array_key_exists( $this->scope, $epl_fields ) )
                exit( $this->epl_util->epl_invoke_error( 1, 'no scope' ) );

//get the options already saved for this scope
            $this->d[$this->scope] = $this->_get_fields( $this->scope );

//get all the relevant fields associated with this scope
            $_fields = $epl_fields[$this->scope];

//get the name of the unique id field.  The FIRST ARRAY ITEM is always the id field
            $id_field = key( $_fields );
//epl_log( 'debug', "<pre>" . print_r( $id_field, true ) . "</pre>", 1 );
            if ( is_null( $id_field ) )
                exit( $this->epl_util->epl_invoke_error( 1, 'no id' ) );

//if adding then the id field will come in as empty
//we create a unique id based on the microtime
//and add it to the post
            if ( $_POST['epl_form_action'] == 'add' ) {
//$_key = (string) microtime(true); //making this string so it can be used in array-flip, can also use uniqid()
                $_key = uniqid(); //usnig uniqid because the microtime(true) will not work in js ID field
                $_POST[$id_field] = $_key;
            }
            else {
//in edit mode, we expect a unique id already present.
//if not, something must have gone wrong
                $_key = $_POST[$id_field];
                if ( is_null( $_key ) )
                    exit( $this->epl_util->epl_invoke_error( 1, 'no' ) );
            }

//this field comes in based on the row order of the form table that has sortable enabled.
//we append the new key to the _order, for use below in rearranging
//the order of the keys based on user sortable action on the form
            if ( isset( $_POST['_order'] ) && is_array( $_POST['_order'] ) )
                $_POST['_order'][] = $_key;

//We only want to save posted data that is relevant to this scope
//so we only grab the appropriate values from the $_POST and ignore everything else
            $_post = array_intersect_key( $_POST, $_fields );

//Since we already have the options pulled from the db into the $this->d var,
//we just append the new key OR replace its values
            $this->d[$this->scope][$_key] = $_post;

//temporarily assign the data to this var for reordering
            $_meta = $this->d[$this->scope];

//if the _order field is set, we need to rearrange the keys in the order that
//the user has selected to keep the data in
            if ( isset( $_POST['_order'] ) && is_array( $_POST['_order'] ) )
                $_meta = $this->epl_util->sort_array_by_array( $this->d[$this->scope], array_flip( $_POST['_order'] ) ); //can use uasort()
//Save the options
//epl_log( 'debug', "<pre>" . print_r( $_meta, true ) . "</pre>", 1 );
            update_option( $this->scope, $_meta );

//Get ready to send the new row back
            $data[$this->scope] = $this->d[$this->scope];

//the data that will be sent back as a table row
            $data['params']['values'][$_key] = $_post;

//Special circumstance:
//since the associaton between the form and fields is key based, we want
//to display the field name also.  This makes it happen
            if ( $this->scope == 'epl_forms' || $this->scope == 'epl_admin_forms' )
                $data['epl_fields'] = $this->_get_fields( $this->scope );

//views to use based on the scope
//TODO make this a config item, out of this file.
            $response_views = array(
                'epl_fields' => 'admin/forms/field-small-block',
                'epl_forms' => 'admin/forms/form-small-block',
                'epl_admin_fields' => 'admin/forms/field-small-block',
                'epl_admin_forms' => 'admin/forms/form-small-block',
            );

//return the relevant view based on scope
            return $this->epl->load_view( $response_views[$this->scope], $data, true );
        }
        return false;
    }


    function _get_fields( $scope = null, $key = null ) {

        if ( !get_option( 'epl_fields' ) )
            epl_activate();

        if ( is_null( $scope ) )
            return null;

        static $_cache = array();

        if ( array_key_exists( $scope, $_cache ) ) {
            return $_cache[$scope];
        }

        $r = get_option( maybe_unserialize( $scope ) );


        if ( !is_null( $key ) ) {
            $r = array_key_exists( $key, $r ) ? $r[$key] : $r;
        }

        $_cache[$scope] = stripslashes_deep( $r );
        return $_cache[$scope];
    }


    function get_metabox_content( $param = array() ) {
        
    }


    function get_list_of_available_forms( $scope = 'epl_forms' ) {


        return $this->_get_fields( $scope );
    }


    function get_list_of_available_fields( $scope = 'epl_fields' ) {


        return $this->_get_fields( $scope );
    }


    function get_personal_field_ids() {

        static $r = array();
        if ( !empty( $r ) )
            return $r;

        $_f = apply_filters( 'epl_get_personal_field_ids', array( 'first_name', 'last_name' ) );


        $fields = $this->get_list_of_available_fields();
        foreach ( $fields as $k => $v ) {

            if ( in_array( $v['input_slug'], $_f ) )
                $r[$k] = $k;
            /* if ( $v['input_slug'] == 'first_name' )
              $r[$k] = $k;
              if ( $v['input_slug'] == 'last_name' )
              $r[$k] = $k; */
        }
        return $r;
    }


    function setup_event_details( $event_id = null, $refresh = false, $check_for_draft = false ) {

        if ( is_null( $event_id ) || $event_id === false )
            return null;
        
        static $_cache = array(); //will keep the data just in case this method gets called again for this id
        static $_field_cache = array(); //will keep the data just in case this method gets called again for this id
        global $event_details, $event_fields;
        if ( !$refresh && epl_get_element( $event_id, $_cache ) ) {
            $event_details = $_cache[$event_id];
            $event_fields = epl_get_element( $event_id, $_field_cache );
            return $_cache[$event_id];
        }

        $post_data = get_post( $event_id, ARRAY_A );
        $post_meta = $this->get_post_meta_all( $event_id, $refresh, $check_for_draft );
        $event_details = ( array ) $post_data + ( array ) $post_meta;

        $_cache[$event_id] = $event_details;

        $this->epl->load_config( 'event-fields' );

        global $epl_fields;

        $_epl_fields = $this->epl_util->combine_array_keys( $epl_fields );

        $event_fields = $_epl_fields;
        $_field_cache[$event_id] = $event_fields;

        return $event_details;
    }


    function setup_location_details( $location_id = null ) {

        if ( is_null( $location_id ) || $location_id == '' )
            return null;

        static $_cache = array(); //will keep the data just in case this method gets called again for this id
        global $post, $location_details;

        if ( isset( $_cache[$location_id] ) ) {
            $location_details = $_cache[$location_id];
        }
        else {

            $id = (!is_null( $location_id )) ? ( int ) $location_id : $post->ID;

            $post_data = get_post( $id, ARRAY_A );

            $post_meta = $this->get_post_meta_all( $id );
            $location_details = ( array ) $post_data + ( array ) $post_meta;

            $_cache[$location_id] = $location_details;
        }

        return $location_details;
    }


    function setup_notif_details( $notif_id = null ) {

        if ( is_null( $notif_id ) || $notif_id == '' )
            return null;

        static $_cache = array(); //will keep the data just in case this method gets called again for this id
        global $post, $notif_details;

        if ( array_key_exists( $notif_id, $_cache ) ) {
            $notif_details = $_cache[$notif_id];
        }
        else {

            $id = (!is_null( $notif_id )) ? ( int ) $notif_id : $post->ID;

            $post_data = get_post( $id, ARRAY_A );

            $post_meta = $this->get_post_meta_all( $id );
            $notif_details = ( array ) $post_data + ( array ) $post_meta;

            $_cache[$notif_id] = $notif_details;
        }

        return $notif_details;
    }


    function setup_org_details( $org_id = null ) {

        static $current_org_id = null;


        global $post, $organization_details;

        $id = (!is_null( $org_id )) ? ( int ) $org_id : $post->ID;

//this makes sure that the org info is queried only once.
        if ( $current_org_id == $id )
            return;


        $post_data = get_post( $id, ARRAY_A );

        $post_meta = $this->get_post_meta_all( $id );
        $organization_details = ( array ) $post_data + ( array ) $post_meta;

        $current_org_id = $id;
//epl_log( "debug", "<pre>" . print_r($organization_details, true ) . "</pre>" );

        return $organization_details;
    }


    function setup_instructor_details( $instr_id = null ) {

        global $event_details, $post, $instructor_details;

        $instr = $event_details['_epl_event_instructor'];

        static $_cache = array();
        if ( array_key_exists( $instr_id, $_cache ) ) {
            $instructor_details = $_cache[$instr_id];
            return $_cache[$instr_id];
        }

        $post_data = get_post( $instr_id, ARRAY_A );


        $_cache [$instr_id] = ( array ) $post_data; // + ( array ) $post_meta;

        $instructor_details = $_cache[$instr_id];

        return $instructor_details;
    }

    /*
     * don't really need to cache these as they are called only once anyway
     */


    function setup_regis_details( $regis_id = null, $refresh = true ) {

        global $post, $regis_details;

        /* if ( !$refresh && ($_rd = wp_cache_get( 'regis_details_' . $regis_id )) !== false ) {
          $regis_details = $_rd;
          return $regis_details;
          } */

        $id = (!is_null( $regis_id ) && $regis_id > 0) ? ( int ) $regis_id : $post->ID;


        $post_data = get_post( $id, ARRAY_A );

        if ( $post_data['post_status'] == 'trash' && !EPL_IS_ADMIN ) {
            $regis_details = array();
            return $regis_details;
        }

        $post_meta = $this->get_post_meta_all( $id, $refresh );
        $regis_details = ( array ) $post_data + ( array ) $post_meta;

        //wp_cache_set( 'regis_details_' . $regis_id, $regis_details );

        return $regis_details;
    }


    function get_post_meta_all( $post_ID = '', $refresh = false, $check_for_draft = false ) {

        //TODO combine check
        if ( is_array( $post_ID ) || empty( $post_ID ) || $post_ID === false || $post_ID == '' || $post_ID == 0 || is_null( $post_ID ) )
            return __return_empty_array();


        static $_cache = array(); //will keep the data just in case this method gets called again for this id

        if ( epl_get_element( $post_ID, $_cache ) && !$refresh )
            return $_cache[$post_ID];


        if ( $check_for_draft !== false )
            $check_for_draft = " OR p.post_status = 'draft'";

        global $wpdb;
        $data = array();
        $wpdb->query( $wpdb->prepare( "
        SELECT meta_id, post_id, meta_key, meta_value
        FROM $wpdb->postmeta pm
        INNER JOIN $wpdb->posts p ON p.ID = pm.post_id
        WHERE (p.post_status = 'publish' $check_for_draft)
        AND `post_id` = %d ORDER BY meta_id
         ", $post_ID ) );

        foreach ( $wpdb->last_result as $k => $v ) {

            $data[$v->meta_key] = maybe_unserialize( $v->meta_value );
        };

        if ( !$refresh )
            $_cache[$post_ID] = $data;

        return $data;
    }


    function get_user_meta_all( $user_ID, $refresh = false ) {
        if ( $user_ID == '' || $user_ID == 0 )
            return __return_empty_array();


        static $_cache = array(); //will keep the data just in case this method gets called again for this id

        if ( array_key_exists( $user_ID, $_cache ) && !$refresh )
            return $_cache[$user_ID];


        global $wpdb;
        $data = array();
        $wpdb->query( $wpdb->prepare( "
        SELECT umeta_id, user_id, meta_key, meta_value
        FROM $wpdb->usermeta
        WHERE `user_id` = %d ORDER BY umeta_id
         ", $user_ID ) );

        foreach ( $wpdb->last_result as $k => $v ) {

            $data[$v->meta_key] = maybe_unserialize( $v->meta_value );

//}
        };
        $_cache[$user_ID] = $data;

        return $data;
    }


    function get_all_events() {

        if ( ($r = wp_cache_get( 'epl_full_event_list' )) !== false )
            return $r;

        $args = array(
            'post_type' => 'epl_event',
            'posts_per_page' => -1,
        );
        $e = new WP_Query( $args );
        $r = array();

        if ( $e->have_posts() ) {

            while ( $e->have_posts() ) :
                $e->the_post();

                $r[get_the_ID()] = get_the_title();

            endwhile;
        }
        asort( $r );

        wp_cache_add( 'epl_full_event_list', $r );

        wp_reset_postdata();
        return $r;
    }

    /* TODO this function also exists in the regis model */


    function get_current_event_id() {
        global $event_details;

        $event_id = epl_get_element( 'ID', $event_details );

        if ( !$event_id ) {
            $event_id = key( ( array ) $_SESSION['__epl'][$this->regis_id]['_events'] );
        }

        return $event_id;
    }

    /*
     * sets global var $current_att_count for the event in the loop
     */


    function get_current_att_count( $event_id = null ) {
        global $post, $event_details, $wpdb, $current_att_count;

        $event_id = $event_id ? $event_id : ( int ) $this->get_current_event_id();
        $current_att_count = EPL_report_model::get_instance()->get_attendee_counts( $event_id, true );

        return $current_att_count;

        static $_cache = array();




        if ( epl_is_empty_array( $event_details ) )
            $this->setup_event_details( $event_id );

        if ( isset( $_cache[$event_id] ) ) {
            $current_att_count = $_cache[$event_id];
            return $_cache[$event_id];
        }

        $current_att_count = array();

        $_totals = $this->get_event_regis_snapshot( $event_details['ID'] );

        $completed_filter = '';
        if ( isset( $_totals['status_complete'] ) && !epl_is_empty_array( $_totals['status_complete'] ) ) {

            $completed_ids = implode( ',', array_keys( $_totals['status_complete'] ) );

            $completed_filter = " AND post_id IN ($completed_ids) ";
        }
        else {
            return null;
        }



//After the user clicks on the Overview, the info is in the db so
//we don't want to count that as a record

        $excl_this_regis_post_id = '';
        $this_regis_post_id = ( isset( $_SESSION['__epl']['post_ID'] )) ? ( int ) $_SESSION['__epl']['post_ID'] : null;

        if ( !is_null( $this_regis_post_id ) && is_int( $this_regis_post_id ) )
            $excl_this_regis_post_id = " AND NOT post_id = " . $this_regis_post_id;


        $q = $wpdb->get_results( "SELECT meta_key, SUM(meta_value) as num_attendees
                FROM $wpdb->postmeta as pm
                INNER JOIN $wpdb->posts p ON p.ID = pm.post_id
                WHERE p.post_status = 'publish'
                AND meta_key LIKE '_total_att_{$event_details['ID']}%' $excl_this_regis_post_id $completed_filter
                GROUP BY meta_key", ARRAY_A );



        if ( $wpdb->num_rows > 0 ) {

            foreach ( $q as $k => $v ) {

                $current_att_count[$v['meta_key']] = $v['num_attendees'];
            }
        }

        $_cache[$event_id] = $current_att_count;
    }

    /*
     * find all the regis info for an event
     */


    function get_event_regis_snapshot( $event_id ) {
        $this->set_event_regis_post_ids( $event_id );
        $arr = array();

        $arr['total_att_count'] = $this->get_current_att_count_admin( $event_id );
        $arr['status_complete'] = $this->get_current_complete_count_admin( $event_id );
        $arr['total_paid'] = $this->get_total_money_paid_admin( $event_id );
//echo "<pre class='prettyprint'>" . print_r($arr, true). "</pre>";
        return $arr;
    }


    function set_event_regis_post_ids( $event_id, $regis_id = null ) {

        global $post, $event_details, $wpdb, $event_regis_post_ids;

        if ( ($r = wp_cache_get( 'event_regis_post_ids_' . $event_id )) !== false ) {


            if ( $regis_id )
                return array( $regis_id => $r[$regis_id] );
            else {
                $event_regis_post_ids = $r;
                return $event_regis_post_ids;
            }
        }

        $event_regis_post_ids = array();
        $regis_id_filter = '';
        if ( $regis_id )
            $regis_id_filter = " AND post_id = $regis_id";

        $q = $wpdb->get_results( $wpdb->prepare( "SELECT post_id, meta_value
                FROM $wpdb->postmeta as pm
                INNER JOIN $wpdb->posts p ON p.ID = pm.post_id
                WHERE p.post_status = 'publish'
                AND meta_key = '_total_att_%d' $regis_id_filter
                ORDER BY post_id", $event_id ), ARRAY_A );


        if ( $wpdb->num_rows > 0 ) {

            foreach ( $q as $k => $v ) {
                $event_regis_post_ids[$v['post_id']] = $v['meta_value'];
            }
        }

        wp_cache_add( 'event_regis_post_ids_' . $event_id, $event_regis_post_ids );
    }


//needs to be refactored, too many calls here.
    function get_event_regis_post_ids( $implode = true, $regis_post_id = null ) {
        global $event_regis_post_ids;


        if ( $implode )
            return implode( ',', array_keys( $event_regis_post_ids ) );

        if ( $regis_post_id && ($rpid = epl_get_element( $regis_post_id, $event_regis_post_ids )) )
            return array( $regis_post_id => $rpid );

        return $event_regis_post_ids;
    }

    /*
     * $current_att_count for already registered attendees.
     */


    function get_current_att_count_admin( $event_id ) {
        global $post, $event_details, $wpdb, $current_att_count, $event_regis_post_ids;

        $current_att_count = array();


        $q = $wpdb->get_results( $wpdb->prepare( "SELECT meta_key, SUM(meta_value) as num_attendees
                FROM $wpdb->postmeta as pm
                INNER JOIN $wpdb->posts p ON p.ID = pm.post_id
                WHERE p.post_status = 'publish'
                AND meta_key = '_total_att_%d'
                GROUP BY meta_key", $event_id ), ARRAY_A );

        if ( $wpdb->num_rows > 0 ) {

            foreach ( $q as $k => $v ) {

                $current_att_count[$v['meta_key']] = $v['num_attendees'];
            }
        }

        return $current_att_count;
        echo "<pre class='prettyprint'>" . print_r( $current_att_count, true ) . "</pre>";
    }


    function get_current_complete_count_admin( $event_id ) {
        global $post, $event_details, $wpdb, $current_att_count, $event_regis_post_ids;

        $_where_regis_post_ids = '';
        $_count = array();
        if ( !empty( $event_regis_post_ids ) ) {
            $_where_regis_post_ids = " AND post_id IN ( " . $this->get_event_regis_post_ids() . ")";
        }
        else
            return $_count;

        $q = $wpdb->get_results( "SELECT post_id, meta_key, meta_value
                FROM $wpdb->postmeta as pm
                INNER JOIN $wpdb->posts p ON p.ID = pm.post_id
                WHERE p.post_status = 'publish'
                AND meta_key = '_epl_regis_status'
                AND (meta_value = 5 OR meta_value = 2)
                $_where_regis_post_ids", ARRAY_A );

        if ( $wpdb->num_rows > 0 ) {

            foreach ( $q as $k => $v ) {

                $_count[$v['post_id']] = $event_regis_post_ids[$v['post_id']]; //$v['num_attendees'];
            }
        }

        return $_count;
// echo "<pre class='prettyprint'>" . print_r( $current_att_count, true ) . "</pre>";
    }


    function setup_current_waitlist_count( $event_id = null ) {

        global $event_details, $wpdb, $current_waitlist_count;



        $event_id = $event_details['ID'];

        if ( wp_cache_get( 'current_waitlist_count_' . $event_id ) !== false )
            return;

        $q = $wpdb->get_col( $wpdb->prepare( "SELECT SUM(meta_value)
                FROM $wpdb->postmeta as pm
                INNER JOIN $wpdb->posts p ON p.ID = pm.post_id
                WHERE p.post_status = 'publish'
                AND meta_key LIKE '_total_waitlist_att_%d%%'
                ORDER BY post_id", $event_id ) );

        if ( $wpdb->num_rows > 0 ) {
            $current_waitlist_count = current( $q );
        }
        else
            $current_waitlist_count = 0;

        wp_cache_add( 'current_waitlist_count_' . $event_id, $current_waitlist_count );
    }


    function get_total_money_paid_admin( $event_id ) {
        global $post, $event_details, $wpdb, $current_att_count, $event_regis_post_ids;

        $_where_regis_post_ids = '';
        $_count = array();
        if ( !empty( $event_regis_post_ids ) ) {
            $_where_regis_post_ids = " AND post_id IN ( " . $this->get_event_regis_post_ids() . ")";
        }
        else
            return $_count;

        $q = $wpdb->get_results( "SELECT post_id, meta_key, meta_value
                FROM $wpdb->postmeta as pm
                INNER JOIN $wpdb->posts p ON p.ID = pm.post_id
                WHERE p.post_status = 'publish'
                AND meta_key = '_epl_payment_amount'
                AND meta_value >0
                $_where_regis_post_ids", ARRAY_A );

        if ( $wpdb->num_rows > 0 ) {

            foreach ( $q as $k => $v ) {

                $_count[$v['post_id']] = floatval( $v['meta_value'] ); //$v['num_attendees'];
            }
        }

        return $_count;
    }


    function events_list( $args = array() ) {


        $args = apply_filters( 'epl_event_list_args', $args );

        if ( epl_get_element( 'display', $args ) == 'calendar' )
            return;

        global $event_list, $wpdb, $post;


        $qry_args = array();

//$event_list = get_transient( 'epl_event_list_' . $page_id  );
//if ( $event_list )
//    return $event_list;


        $start = epl_get_date_timestamp( epl_get_element( 'start', $args, EPL_DATE ) );

        if ( ($end = epl_get_element( 'end', $args, '' )) != '' ) {

            $end = " AND CAST(pm2.meta_value AS SIGNED) <= " . $end;
        }

        if ( $event_id = epl_get_element( 'event_id', $args, null ) ) {

            if ( strpos( $event_id, ',' ) !== false ) {

                $qry_args['post__in'] = explode( ',', $event_id );
            }
            else
                $qry_args['p'] = intval( epl_get_element( 'event_id', $args ) );
        }
        elseif ( !isset( $args['show_upcoming'] ) ) {

            $post_ids = $wpdb->get_col( "
            SELECT pm.post_id
            FROM $wpdb->postmeta pm
            INNER JOIN $wpdb->postmeta pm2 ON (pm.post_id = pm2.post_id)
            WHERE
                ((pm.meta_key = '_epl_event_status' AND CAST(pm.meta_value AS SIGNED) = '1'
                AND pm2.meta_key = '_q__epl_start_date' AND (CAST(pm2.meta_value AS SIGNED) >= " . $start . $end . "))
               OR
                (pm.meta_key = '_epl_event_status' AND CAST(pm.meta_value AS SIGNED) = '3'
                AND pm2.meta_key = '_q__epl_end_date' AND CAST(pm2.meta_value AS SIGNED) >= " . EPL_DATE . ")
            )

        " );

            $post_ids = array_unique( $post_ids );

            $qry_args['post__in'] = $post_ids;
        }

//doing this to get the ongoing events going.

        $meta_query = array();

        if ( isset( $args['location'] ) ) {

            $_l = $args['location'];
            $meta_query[] = array(
                'key' => '_epl_event_location',
                'value' => $_l,
                'type' => 'NUMERIC',
                'compare' => '='
            );
        }

        if ( isset( $args['org'] ) ) {

            $_l = $args['org'];

            $meta_query[] = array(
                'key' => '_epl_event_organization',
                'value' => $_l,
                'type' => 'NUMERIC',
                'compare' => '='
            );
        }

        $orderby = epl_nz( epl_get_setting( 'epl_general_options', 'epl_sort_event_list_by' ), 'date' );
        $order = epl_nz( epl_get_setting( 'epl_general_options', 'epl_sort_event_list_order' ), 'DESC' );


        if ( epl_get_element( 'show_past', $args ) == 1 ) {
            unset( $qry_args['post__in'] );
            unset( $qry_args['p'] );
        }
        elseif ( epl_is_empty_array( $qry_args['post__in'] ) && !isset( $qry_args['p'] ) ) {
            //$meta_query['relation'] = 'AND';
            $meta_query[] = array(
                'key' => '_epl_event_status',
                'value' => array( 1, 3 ),
                'type' => 'NUMERIC',
                'compare' => 'IN'
            );
        }

        $qry_args += array(
            'post_type' => 'epl_event',
            'post_status' => array( 'publish' ),
            'posts_per_page' => -1,
            'orderby' => $orderby,
            'order' => $order,
            'meta_query' => $meta_query
        );
        
        if(isset($args['draft']))
            array_push($qry_args['post_status'], 'draft');

        if ( $orderby == 'start_date' && !isset( $qry_args['p'] ) ) {
            $qry_args['orderby'] = 'meta_value_num';
            $qry_args['meta_key'] = '_epl_event_sort_order';
        }

        if ( epl_get_element( 'show_past', $args ) == 1 ) {
            unset( $qry_args['meta_key'] );
        }

        if ( isset( $args['taxonomy'] ) ) {

            $_t = $args['taxonomy'];

            if ( strpos( $_t, ',' ) !== false ) {
                $_t = array();
                $_t = explode( ',', $args['taxonomy'] );
            }

            $qry_args['tax_query'] = array(
                array(
                    'taxonomy' => 'epl_event_categories',
                    'field' => 'slug',
                    'terms' => $_t
                )
            );
        }

        if ( isset( $args['show_upcoming'] ) ) {

            $qry_args['orderby'] = 'meta_value_num';
            $qry_args['meta_key'] = '_epl_event_sort_order';
        }

        if ( !empty( $args['orderby'] ) ) {
            $qry_args['orderby'] = $args['orderby'];
            $qry_args['order'] = $args['order'];
        }

        if ( epl_get_element( 'fields', $args ) != '' )
            $qry_args['fields'] = epl_get_element( 'fields', $args );

        $qry_args = apply_filters( 'epl_event_list_query_args', $qry_args );

        $event_list = new WP_Query( $qry_args );

//set_transient( 'epl_event_list_' . $page_id, $event_list, 60 * 60 * 24 );
        wp_reset_query();
        return;
    }


    function event_location_details( $param = array() ) {

        $args = array(
            'post_type' => 'epl_location'
        );

        global $event_list;
        $event_list = new WP_Query( $args );



        return;
    }


    function get_epl_options( $param = array() ) {
        $this->epl_options = array();
        $this->epl_options = ( array ) get_option( 'events_planner_general_options' );
        $this->epl_options += ( array ) get_option( 'epl_addon_options' );
    }


    function epl_insert_post( $post_type, $meta ) {

// Create post object
        $my_post = array(
            'post_type' => 'epl_registration',
            'post_title' => strtoupper( $this->epl_util->make_unique_id( 20 ) ),
            'post_content' => "$meta",
            'post_status' => 'draft'
        );

// Insert the post into the database
        $post_ID = wp_insert_post( $my_post );

        add_post_meta( $post_ID, 'regis_fields', $meta );
    }

    /* When the post is saved, saves our custom data */


    function _save_postdata( $args = array() ) {

        //check if doing quick-edit
        if ( epl_get_element( 'action', $_REQUEST ) == 'inline-edit' )
            return;

        extract( $args );


        if ( !isset( $fields ) || empty( $fields ) )
            return;

//From the config file, only get the fields that pertain to this section
//We are only interested in the posted fields that pertain to events planner
        $event_meta = array_intersect_key( $_POST, $fields );

//epl_log( "debug", "<pre>THE META" . print_r($event_meta, true ) . "</pre>" );
//post save callback function, if adding
        $_save_cb = 'epl_add_post_meta';

//if editing, callback is different
        if ( $edit_mode )
            $_save_cb = 'epl_update_post_meta';



        foreach ( $event_meta as $k => $data['values'] ) {

            $meta_k = $k;

            /*
             * since we need the dates to be saved as individual records (so we can query),
             * we need to check the field attribute for save_type
             *
             * TODO check if save type is ind_row > save as individual
             *  if it is individual, check if array.  If so, loop, and for each one,
             *  save accordingly
             * TODO check if data_type exists > convert to data type
             * TODO if they delete a row, need to delete it from the meta table also
             */

            /*
             * when data comes in as an array, sometimes we want to save each one of the values as
             * individual rows in the meta table so that we can query it more efficiently with the WP_Query.
             *
             */

            $_q = array_key_exists( 'query', $fields[$meta_k] );
            $_dt = array_key_exists( 'data_type', $fields[$meta_k] );

//check if save_type is defined for this field
            if ( $_q || $_dt ) {

                if ( $_q )
                    delete_post_meta( $post_ID, '_q_' . $meta_k ); //these are special meta keys that will allow querying
//check if this is an array

                if ( is_array( $data['values'] ) ) {

                    foreach ( $data['values'] as $_k => &$_v ) {

                        if ( isset( $fields[$meta_k]['data_type'] ) ) {

//epl_log( "debug", "<pre>" . print_r( $_v, true ) . "</pre>" );

                            $this->epl->epl_util->process_data_type( $_v, $fields[$meta_k]['data_type'], 's' );
                        }
                        if ( $_q )
                            $this->epl_add_post_meta( $post_ID, '_q_' . $meta_k, $_v, $_k );
                    }
                } else {
                    $this->epl->epl_util->process_data_type( $data['values'], $fields[$meta_k]['data_type'], 's' );
                }
            }


            if ( !is_array( $data['values'] ) ) {

                $data['values'] = esc_attr( $data['values'] );
            }
            else {
                /* if ( isset( $fields[$meta_k]['_save_func'] ) ) {

                  $_save_func = $fields[$meta_k]['_save_func'];
                  if ( function_exists( $_save_func ) )
                  $data['values'] = $_save_func( $data['values'] );
                  } else */
                $data['values'] = $this->epl_util->clean_input( $data['values'] );
            }
            $this->$_save_cb( $post_ID, $meta_k, $data['values'], '' );
        }
    }


    function epl_add_post_meta( $post_id, $meta_k, $meta_value ) {

        add_post_meta( $post_id, $meta_k, $meta_value );
    }


    function epl_update_post_meta( $post_id, $meta_key, $meta_value ) {

        update_post_meta( $post_id, $meta_key, $meta_value );
    }


    function get_registration_details( $post_id = null ) {


        global $epl_current_step, $regis_details, $event_details;
        $epl_current_step = 'thank_you_page';

        $post_id = epl_nz( $post_id, get_the_ID() );

        $regis_meta = $this->setup_regis_details( get_the_ID() );

        if ( !epl_user_is_admin() && epl_check_token() === false ) {
            @header( 'HTTP/1.0 404 Not Found' );
            return "<div class='epl_error'>" . epl__( 'You have reached this page in error.' ) . "</div>";
        }
        $event_id = key( ( array ) $regis_meta['_epl_events'] );

        $event_meta = $this->setup_event_details( $event_id );


//$earm = $this->epl->load_model( 'epl-regis-admin-model' );
        $erm = $this->epl->load_model( 'epl-registration-model' );
        $ercm = $this->epl->load_model( 'epl-recurrence-model' );

        $data['event_id'] = $event_id;
        $regis_id = $regis_details['__epl']['_regis_id'];
        epl_do_messages( $regis_details['__epl'][$regis_id]['_events'] );

        $data['regis_status_id'] = get_the_regis_status( null, true );

        $erm->set_mode( 'overview' )->setup_current_data( $regis_meta );

        $data['cart_data'] = $erm->show_cart();
        $data['cart_data'] = $this->epl->load_view( 'front/registration/regis-cart-section', $data, true );

        $data['cart_totals'] = $erm->calculate_cart_totals();

        $redirect_to = apply_filters( 'epl_ecm__get_registration_details__redirect_to', '' );

        if ( $redirect_to != '' ) {
            wp_redirect( $redirect_to, 301 );
            die();
        }

        // $data['cart_totals'] = $this->epl->load_view( 'front/registration/regis-totals-section', $data, true );

        $payment_method_id = EPL_registration_model::get_instance()->get_payment_profile_id();

        if ( !epl_is_free_event() )
            $data['gateway_info'] = $this->get_post_meta_all( $payment_method_id );

        $data['payment_instructions'] = $this->epl->load_view( 'front/registration/regis-payment-instr', $data, true );
        $data['payment_details'] = $this->epl->load_view( 'front/registration/regis-payment-details', $data, true );

//registration form
        $data['regis_form'] = $erm->regis_form( null, 'front/registration/' );

        /* $ercm->hide_past = false;
          $d = $ercm->recurrence_dates_from_dates_section( );
          echo $this->epl_util->construct_calendar( $d ); */

//the list of events
        $params = array(
            'input_type' => 'select',
            'input_name' => 'event_list_id',
            'id' => 'event_list_id',
            'label' => epl__( 'Event' ),
            'options' => $this->get_all_events(),
                //'value' => $data['values']
        );
//echo "<pre class='prettyprint'>" . print_r($params, true). "</pre>";
        $data['fields'][] = $this->epl_util->create_element( $params );

        $r = $this->epl->load_view( 'front/registration/regis-thank-you-page', $data, true );

        return $r;
    }


    function event_search_box( $result_view = 'list', $search_fields = null, $shortcode_atts = array() ) {


        global $event_list, $epl_fields;

        $this->epl->load_config( 'event-fields' );

        $data['filters'] = array();

        $terms = epl_object_to_array( get_terms( 'epl_event_categories' ) );


        if ( !epl_is_empty_array( $terms ) || isset( $shortcode_atts['taxonomy'] ) ) {
            $_opt = array();
            $field_type = 'hidden';
            $label = '';
            $value = epl_get_element( 'taxonomy', $shortcode_atts );

            if ( !isset( $shortcode_atts['taxonomy'] ) ) {

                foreach ( $terms as $k => $_v ) {

                    $_opt[$_v['slug']] = $_v['name'];
                }

                $field_type = 'select';
                $label = epl__( 'Category' );
                $value = null;
            }



            $_ar = array(
                'weight' => 20,
                'input_type' => $field_type,
                'input_name' => 'taxonomy',
                'label' => $label,
                //'empty_row' => true,
                'options' => array( '' => epl__( 'All Categories' ) ) + $_opt,
                'value' => $value
            );
            $_ar = apply_filters( 'epl_event_search_box_taxonomy_field_arr', $_ar );
            $data['filters']['taxonomy'] = EPL_util::get_instance()->create_element( $_ar );
        }


        $_ar = array(
            'weight' => 10,
            'input_type' => 'datepicker',
            'input_name' => '_epl_from_date',
            'label' => epl__( 'From' ),
            'placeholder' => epl__( 'From' ),
        );


        $data['filters']['date_from'] = EPL_util::get_instance()->create_element( $_ar );

        $_ar = array(
            'weight' => 15,
            'input_type' => 'datepicker',
            'input_name' => '_epl_to_date',
            'label' => epl__( 'To' ),
            'placeholder' => epl__( 'To' ),
        );

        $data['filters']['date_to'] = EPL_util::get_instance()->create_element( $_ar );

        $_opt = array( '' => epl__( 'All Locations' ) );

        $_ar = array(
            'weight' => 25,
            'input_type' => 'select',
            'input_name' => '_epl_event_location',
            'label' => epl__( 'Location' ),
            //'empty_row' => true,
            'options' => array( '' => epl__( 'All Locations' ) ) + get_list_of_available_locations()
        );

        $data['filters']['location'] = EPL_util::get_instance()->create_element( $_ar );

        if ( $search_fields )
            $data['filters'] = array_intersect_key( $data['filters'], array_flip( $search_fields ) );

        $data['filters'] = apply_filters( 'epl_event_search_box', $data['filters'] );

        uasort( $data['filters'], 'epl_sort_by_weight' );



        $data['result_view'] = $result_view;
        $data['shortcode_atts'] = $shortcode_atts;

        return $this->epl->load_view( 'front/event-search-box', $data, true );
    }


    function the_list_of_active_events( $show_date = false ) {
        global $wpdb, $event_details;
        static $events = array();

        if ( !empty( $events ) )
            return $events;

        $_events = $wpdb->get_results( "
			SELECT id, post_title
			FROM {$wpdb->posts} WHERE post_type = 'epl_event' and post_status = 'publish'
			ORDER BY post_title ASC
		", OBJECT_K );

        $_events = epl_object_to_array( $_events );

        foreach ( $_events as $k => $v ) {
            setup_event_details( $k );
            //if(!epl_event_fully_expired()){
            $events[$k] = $v['post_title'] . epl_wrap( $v['id'], ' (', ') ' ) . ' - ' . epl_formatted_date( current( ( array ) $event_details['_epl_start_date'] ), 'D, M d ' );
            //}
        }

        return $events;
    }


    function epl_attendee_list( $table = true, $email_only = false, $regis_post_id = null ) {

        global $epl_fields;

        $event_id = intval( $_REQUEST['event_id'] );
        $date_id = epl_get_element( 'date_id', $_REQUEST );
        $time_id = epl_get_element( 'time_id', $_REQUEST );
        $names_only = (epl_get_element( 'names_only', $_REQUEST, 0 ) == 1);


        $email_list = array();
        //$regis_post_id = epl_get_element( 'post_ID', $_REQUEST, null );
        $this->setup_event_details( $event_id );

        $_totals = $this->get_event_regis_snapshot( $event_id );


        $this->set_event_regis_post_ids( $event_id, $regis_post_id );

        global $event_details, $regis_details;


        $event_title = $event_details['post_title'];
        $event_date_keys = array_keys( $event_details['_epl_start_date'] );
        if ( !$table ) {
            $filename = str_replace( array( " ", ',' ), "-", $event_title ) . "_" . date_i18n( "Y-m-d" );
//header( "Content-type: application/x-msdownload", true, 200 );
            header( 'Content-Encoding: UTF-8' );

            header( 'Content-Type: application/csv;charset=UTF-8' );
            header( "Content-Disposition: attachment; filename={$filename}.csv" );
            header( "Pragma: no-cache" );
            header( "Expires: 0" );
            echo "\xEF\xBB\xBF"; //BOM to make other utf-8 chars work
        }
        else {
            
        }

        //$this->setup_event_details( $event_id );
        //echo "<pre class='prettyprint'>" . print_r($event_details, true). "</pre>";
        //find the forms that the user has selected for this event
        $event_ticket_buyer_forms = array_flip( ( array ) $event_details['_epl_primary_regis_forms'] );
        $event_addit_forms = (epl_get_element( '_epl_addit_regis_forms', $event_details )) ? array_flip( $event_details['_epl_addit_regis_forms'] ) : array();


        /*
         * find price forms if any.
         */

        $price_forms = epl_get_element( '_epl_price_forms', $event_details, array() );

        $_price_forms = array();
        foreach ( $price_forms as $k => $v ) {
            $_price_forms += $v;
        }


        //find the list of all forms
        $available_forms = $this->get_list_of_available_forms();
        $available_fields = $this->get_list_of_available_fields();

        //isolate the ticket buyer forms that are selected inside the event
        $ticket_buyer_forms = array_intersect_key( $available_forms, $event_ticket_buyer_forms );

        //isolate the additional forms for attendees.
        $addit_forms = array_intersect_key( $available_forms, array_merge( $event_addit_forms, $_price_forms ) );

        //This will combine all the fields in all the forms so that we can construct a header row.
        $tickey_buyer_fields = array();
        foreach ( $ticket_buyer_forms as $_form_id => $_form_info )
            $tickey_buyer_fields = array_merge( $tickey_buyer_fields, $_form_info['epl_form_fields'] );

        //combine all the fields from the attendee forms
        $event_addit_fields = array();
        foreach ( $addit_forms as $_form_id => $_form_info ) {

            //$event_addit_fields += $_form_info['epl_form_fields'];
            $event_addit_fields = array_merge( $event_addit_fields, $_form_info['epl_form_fields'] );
        }

        $glob_discounts = EPL_discount_model::get_instance()->get_global_discount_configs();
        $this->epl->load_config( 'global-discount-fields' );

        $epl_fields_inside_form = array_flip( $tickey_buyer_fields ); //get the field ids inside the form
        $epl_addit_fields_inside_form = array_flip( $event_addit_fields ); //get the field ids inside the form
        //when creating a form in form manager, the user may rearrange fields.  Find their desired order
        $epl_fields_to_display = $this->epl_util->sort_array_by_array( $available_fields, $epl_fields_inside_form );
        $epl_addit_fields_to_display = $this->epl_util->sort_array_by_array( $available_fields, $epl_addit_fields_inside_form );

        //final list of all the fields to display
        $epl_fields_to_display = $epl_fields_to_display;


        $csv_row = '';
        $header_row = array();
        $header_pulled = false;
        $tb_header_pulled = false;
        $row = array();
        $tb_form_data_header = array();
        $form_data_header = array();
        $tb_form_data = array();
        $form_data = array();
        //$header_row[] = '';
        $header_row[] = epl__( 'Regis ID' );
        $header_row[] = epl__( 'Regis Date' );

        $header_row[] = epl__( 'Status' );
        $header_row[] = epl__( 'Payment Method' );
        $header_row[] = epl__( 'Total' );
        $header_row[] = epl__( 'Amount Paid' );
        $header_row[] = epl__( 'Discount Code' );

        $header_row[] = epl__( 'Event Date' );
        $header_row[] = epl__( 'Time' );
        $header_row[] = epl__( 'Ticket' );

        foreach ( $epl_fields_to_display as $field_id => $field_atts )
            $tb_form_data_header[] = epl_format_string( $field_atts['label'] );

        if ( !epl_is_empty_array( $epl_addit_fields_to_display ) ) {
            foreach ( $epl_addit_fields_to_display as $field_id => $field_atts )
                $form_data_header[] = epl_format_string( $field_atts['label'] );
        }

        //get all the registration post ids for this event
        $regis_ids = $this->get_event_regis_post_ids( false, $regis_post_id );

        //as of 1.1, the dates are stored as timestamps.
        //This will format the date for display based on the settings admin date format.
        foreach ( $event_details['_epl_start_date'] as $k => &$v )
            $v = epl_admin_date_display( $v );

        $pack_regis = (epl_get_element( '_epl_pack_regis', $event_details, 0 ) == 10);

        $zebra = 'odd';
        //for each registration
        foreach ( $regis_ids as $regis_id => $att_count ) {
            //setup the registration details
            $regis_data = $this->setup_regis_details( $regis_id, true );
            $version = preg_replace( '/(\.\w\d+)/', '', epl_get_element( '_epl_plugin_version', $regis_details, EPL_PLUGIN_VERSION ) );
            //echo "<pre class='prettyprint'>" . __LINE__ . "> " . print_r($regis_data, true). "</pre>";
            //Sometime there may be incomplete db records.  These will cause issues below.
            //In those cases, skip and move to the next item
            if ( !isset( $regis_data['_epl_dates']['_epl_start_date'][$event_id] ) )
                continue;

            if ( get_the_regis_status( null, true ) <= 1 )
                continue;

            $zebra = ($zebra == 'odd') ? 'even' : 'odd';

            $total_att = epl_get_element( '_total_att_' . $event_id, $regis_data );

            //event times and prices
            //$event_times = $regis_data['_epl_dates']['_epl_start_time'][$event_id];
            //$event_prices = $regis_data['_epl_dates']['_epl_start_time'][$event_id];

            $disc_code_id = trim( epl_get_element( 'discount_code_id', $regis_data['_epl_events'][$event_id]['money_totals'] ) );

            $disc_source = $event_details;
            $disc_code = epl_get_element_m( $disc_code_id, '_epl_discount_code', $disc_source, '' );

            if ( $disc_code == '' ) {
                $disc_code = epl_get_element_m( $disc_code_id, '_epl_discount_code', $glob_discounts );
                $disc_source = $glob_discounts;
            }
            if ( $disc_code_id != '' ) {

                $code_amount = epl_get_element_m( $disc_code_id, '_epl_discount_amount', $disc_source );
                $code_type = epl_get_element_m( $disc_code_id, '_epl_discount_type', $disc_source );
                $code_type = $epl_fields['epl_discount_fields']['_epl_discount_type']['options'][$code_type];

                $disc_code .= " ($code_amount $code_type)";
            }

            $reserved_dates = epl_get_element_m( $event_id, '_epl_start_date', $regis_data['_epl_dates'], array() );
            $reserved_times = epl_get_element_m( $event_id, '_epl_start_time', $regis_data['_epl_dates'], array() );

            if ( !$pack_regis && $date_id && !in_array( $date_id, $reserved_dates ) )
                continue;

            if ( !$pack_regis && $time_id && !in_array( $time_id, $reserved_times ) )
                continue;
            //this isolates the dates and times that the user has registered for
            $reserved_dates = implode( ' & ', array_intersect_key( $event_details['_epl_start_date'], array_flip( $reserved_dates ) ) );
            $reserved_times_display = implode( ' & ', array_intersect_key( $event_details['_epl_start_time'], array_flip( $reserved_times ) ) );



            //init vars
            $date_labels = array();
            $date_labels[0] = '';
            $time_labels = array();
            $time_labels[0] = '';
            $ticket_labels = array();
            $ticket_labels[0] = $att_count;
            $purchased_tickets = ( array ) $regis_data['_epl_dates']['_att_quantity'][$event_id];

            //?????????
            $start = 1;
            foreach ( $purchased_tickets as $price_id => $qty ) {

                if ( epl_is_date_level_price() && $date_id ) {
                    $_qty = $qty[$date_id]; //current( $qty );
                }
                else
                    $_qty = (is_array( $qty )) ? array_sum( $qty ) : $qty; //current( $qty );


                if ( $_qty > 0 ) {

                    $date_label[] = current( ( array ) $regis_data['_epl_dates']['_epl_start_date'][$event_id] );
                    if ( epl_get_element( '_epl_pricing_type', $event_details ) == 10 ) {
                        if ( in_array( $event_details['_epl_price_parent_time_id'][$price_id], ( array ) $regis_data['_epl_dates']['_epl_start_time'][$event_id] ) ) {

                            $time_labels = array_pad( $time_labels, $start + $_qty, epl_get_element( $event_details['_epl_price_parent_time_id'][$price_id], $event_details['_epl_start_time'] ) );
                        }
                        else {
                            $time_labels = array_pad( $time_labels, $start + $_qty, '' );
                        }
                    }


                    $ticket_labels = array_pad( $ticket_labels, $start + $_qty, $event_details['_epl_price_name'][$price_id] );

                    $start+=$_qty;
                }

                if ( epl_is_date_level_price() && $date_id )
                    $purchased_tickets[$price_id] = array_intersect_key( $purchased_tickets[$price_id], array_flip( $date_label ) );
            }

            $_r = array();

            $regis_status = (isset( $regis_data['_epl_regis_status'] )) ? $epl_fields['epl_regis_payment_fields']['_epl_regis_status']['options'][$regis_data['_epl_regis_status']] : '';
            $_pm_id = EPL_Registration_model::get_instance()->setup_current_data( $regis_data )->get_payment_profile_id();

            $payment_method = $epl_fields['epl_regis_payment_fields']['_epl_payment_method']['options'][$_pm_id];

            $grand_total = epl_get_formatted_curr( epl_nz( $regis_data['_epl_grand_total'], 0.00 ) );
            $amount_paid = epl_get_formatted_curr( epl_nz( $regis_data['_epl_payment_amount'], 0.00 ) );

            $attendee_info = $regis_data['_epl_attendee_info'];

            //################################### Ticket buyer Data ############################################
            //$row[] = epl__( 'Registrant' );

            $row[] = $regis_data['__epl']['_regis_id']; //epl_anchor( admin_url( "post.php?post={$regis_data['ID']}&action=edit" ), $regis_data['__epl']['_regis_id'] );
            $row[] = $regis_data['post_date'];

            $row[] = $regis_status;
            $row[] = epl_escape_csv_val( $payment_method );
            $row[] = $table ? $grand_total : epl_escape_csv_val( $grand_total );
            $row[] = $table ? $amount_paid : epl_escape_csv_val( $amount_paid );
            $row[] = epl_escape_csv_val( $disc_code );

            //      $tb_form_data[] = ''; //epl_escape_csv_val( $regis_date );
            //    $tb_form_data[] = ''; //$regis_time; //(epl_is_date_level_time ( ))?$regis_time:$time_labels[$i]; //
//
            //          $tb_form_data[] = ''; //epl_escape_csv_val( epl_get_element( $ticket_id, $event_details['_epl_price_name'] ) ); //$regis_price;


            foreach ( $epl_fields_to_display as $field_id => $field_atts ) {

                $value = (isset( $attendee_info[$field_id] )) ? epl_get_element( 0, $attendee_info[$field_id][$event_id] ) : '';

                if ( $field_atts['input_slug'] == 'email' ) {

                    $email_list[$regis_id] = $value;

                    if ( $regis_post_id && $regis_post_id != $regis_id )
                        unset( $email_list[$regis_id] );
                }

                if ( $field_atts['input_type'] == 'select' || $field_atts['input_type'] == 'radio' ) {

                    $value = (isset( $field_atts['epl_field_choice_text'][$value] ) && $field_atts['epl_field_choice_text'][$value] !== '') ? $field_atts['epl_field_choice_text'][$value] : $value;
                }
                elseif ( $field_atts['input_type'] == 'checkbox' ) {

                    if ( !epl_is_empty_array( $field_atts['epl_field_choice_value'] ) )
                        $value = (implode( ',', ( array ) $value ) );
                    else
                    if ( is_array( $value ) )
                        $value = (implode( ',', array_intersect_key( $field_atts['epl_field_choice_text'], array_flip( ( array ) $value ) ) ));
                }

                //if ( !$names_only || ($names_only && in_array( $field_atts['input_slug'], array( 'first_name', 'last_name' ) ) !== false) ) {
                $tb_form_data[] = epl_escape_csv_val( html_entity_decode( htmlspecialchars_decode( $value, ENT_QUOTES ) ) );
                //if ( !$tb_header_pulled )
                // $tb_form_data_header[] = epl_escape_csv_val( html_entity_decode( htmlspecialchars_decode( $field_atts['label'], ENT_QUOTES ) ) );
                //}
            }

            $tb_header_pulled = true;
            $row = (!$names_only) ? array_merge( $row, $tb_form_data ) : array_merge( $row, $tb_form_data );
            $row = apply_filters( 'ecm__epl_attendee_list__row_primary_registrant', $row );
            //if ( $table && (!$names_only || epl_is_empty_array( $addit_forms )) )
            //  $this->epl->epl_table->add_row( $row, $zebra );
            //if ( !$names_only || epl_is_empty_array( $addit_forms ) )
            //  $csv_row .= implode( ",", $row ) . "\r\n";
            $row = array();


            //###################  End Ticket Buyer Data #########################################

            $tickets_to_show = array_intersect_key( $purchased_tickets, $event_details['_epl_price_name'] );

            $counter = 1;
            $att_counter = 1;

            foreach ( $tickets_to_show as $ticket_id => $ticket_quantities ) {

                if ( is_array( $ticket_quantities ) ) {
                    $tmp_price_inner_keys = array_keys( $ticket_quantities );
                    $ticket_qty = array_sum( $ticket_quantities );
                }
                if ( $ticket_qty == 0 )
                    continue;

                //if(strlen($ticket_id) > 2)

                foreach ( $ticket_quantities as $ticket_qty_id => $quantities ) {
                    if ( version_compare( $version, '1.2.9', '<' ) )
                        $counter = 1;

                    for ( $i = 0; $i < $quantities; $i++ ) {

                        //not good, runs every time in the loop
                        if ( $pack_regis && $attendance_dates = epl_get_element( "_pack_attendance_dates_{$event_id}_{$ticket_id}_" . ($i + 1), $regis_data, null ) ) {

                            $pack_count = count( $attendance_dates );
                            $attendance_date_number = array_search( $date_id, array_keys( $attendance_dates ) ) + 1;

                            //from the even dates, we want to find the next consecutive x days.
                            $first_date_key = key( $attendance_dates );

                            $offset = array_search( $first_date_key, $event_date_keys );
                            $attendance_dates = array_slice( $event_details['_epl_start_date'], $offset, $pack_count );
                        }

                        if ( $pack_regis && $date_id && !isset( $attendance_dates[$date_id] ) ) {
                            break;
                            continue;
                        }


                        //$row[] = ''; //epl_get_element( '_epl_addit_regis_form_counter_label', $event_details, epl__( 'Attendee' ) ) . ' ' . $att_counter;
                        /* $grand_total = '';
                          $amount_paid = '';
                          $regis_status = '';
                          $payment_method = ''; */

                        $ticket_label = epl_escape_csv_val( epl_get_element( $ticket_id, $event_details['_epl_price_name'] ) );

                        if ( epl_is_date_level_price() ) {
                            $reserved_date_key = $ticket_qty_id;
                            $reserved_dates = epl_get_element_m( $ticket_qty_id, '_epl_start_date', $event_details );
                        }

                        if ( epl_is_date_level_time() ) {

                            $reserved_time_key = $reserved_times[$ticket_qty_id];

                            $reserved_times_display = epl_get_element_m( $reserved_time_key, '_epl_start_time', $event_details );
                        }


                        //$row[] = epl__( 'Attendee' );
                        $row[] = $regis_data['__epl']['_regis_id'];
                        $row[] = $regis_data['post_date'];
                        $row[] = $regis_status;

                        $row[] = epl_escape_csv_val( $payment_method );
                        $row[] = $table ? $grand_total : epl_escape_csv_val( $grand_total );
                        $row[] = $table ? $amount_paid : epl_escape_csv_val( $amount_paid );


                        $row[] = epl_escape_csv_val( $disc_code ); //discount code placeholder
                        $row[] = epl_escape_csv_val( $reserved_dates );
                        $row[] = $reserved_times_display; //(epl_is_date_level_time ( ))?$regis_time:$time_labels[$i]; //

                        $row[] = $ticket_label . ($pack_regis ? ' ' . $attendance_date_number . '/' . $pack_count : ''); //$regis_price;




                        /* form data, if any */
                        foreach ( $epl_addit_fields_to_display as $field_id => $field_atts ) {
                            if ( !$header_pulled ) {
                                // if ( !$names_only || ($names_only && in_array( $field_atts['input_slug'], array( 'first_name', 'last_name' ) ) !== false) )// TODO - make better
                                //   $form_data_header[] = epl_escape_csv_val( html_entity_decode( htmlspecialchars_decode( $field_atts['label'], ENT_QUOTES ) ) );
                            }
                            $value = '';

                            //new v1.2.b9+

                            if ( isset( $attendee_info[$field_id][$event_id][$ticket_id] ) ) {

                                $value = epl_get_element( $counter, $attendee_info[$field_id][$event_id][$ticket_id] );
                            }
                            elseif ( isset( $attendee_info[$field_id][$event_id][$counter] ) ) {
                                $value = $attendee_info[$field_id][$event_id][$counter];
                            }

                            if ( $field_atts['input_type'] == 'select' || $field_atts['input_type'] == 'radio' ) {

                                $value = (isset( $field_atts['epl_field_choice_text'][$value] ) && $field_atts['epl_field_choice_text'][$value] !== '') ? $field_atts['epl_field_choice_text'][$value] : $value;
                            }
                            elseif ( $field_atts['input_type'] == 'checkbox' ) {

                                if ( !epl_is_empty_array( $field_atts['epl_field_choice_value'] ) )
                                    $value = (implode( ',', ( array ) $value ) );
                                elseif ( !epl_is_empty_array( $value ) ) {
                                    $value = (implode( ',', array_intersect_key( $field_atts['epl_field_choice_text'], array_flip( $value ) ) ));
                                }
                                else {
                                    $value = html_entity_decode( htmlspecialchars_decode( $value ) );
                                }
                            }
                            /* else {

                              $value = html_entity_decode( htmlspecialchars_decode( $value ) );
                              } */
                            //if ( !$names_only || ($names_only && in_array( $field_atts['input_slug'], array( 'first_name', 'last_name' ) ) !== false) )
                            $form_data[] = epl_escape_csv_val( html_entity_decode( htmlspecialchars_decode( $value, ENT_QUOTES ) ) );
                        }

                        $header_pulled = true;
                        //decode special chars (Swedish, Nordic)

                        $row = (!$names_only) ? array_merge( $row, array_merge( $tb_form_data, $form_data ) ) : array_merge( $tb_form_data, $form_data );

                        $row = apply_filters( 'ecm__epl_attendee_list__row_attendee', $row );
                        array_walk( $row, create_function( '&$item', '$item = utf8_decode($item);' ) );
                        if ( $table ) {
                            $this->epl->epl_table->add_row( $row, $zebra );
                        }
                        $csv_row .= implode( ",", $row ) . "\r\n";
                        $row = array();
                        $form_data = array();
                        $counter++;
                        $att_counter++;
                    }
                }
            }
            $tb_form_data = array();
        }

        $header_row = (!$names_only) ? array_merge( $header_row, array_merge( $tb_form_data_header, $form_data_header ) ) : array_merge( $tb_form_data_header, $form_data_header );

        $header_row = apply_filters( 'ecm__epl_attendee_list__header_row', $header_row );

        array_walk( $header_row, create_function( '&$item', '$item = utf8_decode($item);' ) );



        if ( $table ) {
            $tmpl = array( 'table_open' => '<table border="1" cellpadding="0" cellspacing="0" class="" id="epl_attendee_list_table">' );

            $this->epl->epl_table->set_template( $tmpl );

            $this->epl->epl_table->set_heading( $header_row );
            $t = $this->epl->epl_table->generate();

            return $t;
        }
        elseif ( $email_only ) {
            return $email_list;
        }
        else {
            echo implode( ",", $header_row ) . "\r\n";
            echo $csv_row;
            exit();
        }
    }

    /* csv download, attendee list table, email addresses */


    function epl_daily_schedule( $table = false, $email_only = false, $regis_post_id = null ) {

        global $epl_fields;

        $event_id = ( int ) $_REQUEST['event_id'];
        $date_id = epl_get_element( 'date_id', $_REQUEST );
        $time_id = epl_get_element( 'time_id', $_REQUEST );
        $names_only = (epl_get_element( 'names_only', $_REQUEST, 0 ) == 1);


        $email_list = array();
        //$regis_post_id = epl_get_element( 'post_ID', $_REQUEST, null );
        $this->setup_event_details( $event_id );

        $_totals = $this->get_event_regis_snapshot( $event_id );


        $this->set_event_regis_post_ids( $event_id, $regis_post_id );

        global $event_details;


        $event_title = $event_details['post_title'];

        if ( !$table ) {
            $filename = str_replace( " ", "-", $event_title ) . "_" . date_i18n( "m-d-Y" );
//header( "Content-type: application/x-msdownload", true, 200 );
            /* header( 'Content-Type: application/csv' );
              header( "Content-Disposition: attachment; filename={$filename}.csv" );
              header( "Pragma: no-cache" );
              header( "Expires: 0" ); */
        }
        else {
            
        }

        //$this->setup_event_details( $event_id );
        //echo "<pre class='prettyprint'>" . print_r($event_details, true). "</pre>";
        //find the forms that the user has selected for this event
        $event_ticket_buyer_forms = array_flip( ( array ) $event_details['_epl_primary_regis_forms'] );
        $event_addit_forms = (epl_get_element( '_epl_addit_regis_forms', $event_details )) ? array_flip( $event_details['_epl_addit_regis_forms'] ) : array();


        /*
         * find price forms if any.
         */

        $price_forms = epl_get_element( '_epl_price_forms', $event_details, array() );

        $_price_forms = array();
        foreach ( $price_forms as $k => $v ) {
            $_price_forms += $v;
        }


        //find the list of all forms
        $available_forms = $this->get_list_of_available_forms();
        $available_fields = $this->get_list_of_available_fields();

        //isolate the ticket buyer forms that are selected inside the event
        $ticket_buyer_forms = array_intersect_key( $available_forms, $event_ticket_buyer_forms );

        //isolate the additional forms for attendees.
        $addit_forms = array_intersect_key( $available_forms, array_merge( $event_addit_forms, $_price_forms ) );

        //This will combine all the fields in all the forms so that we can construct a header row.
        $tickey_buyer_fields = array();
        foreach ( $ticket_buyer_forms as $_form_id => $_form_info )
            $tickey_buyer_fields += $_form_info['epl_form_fields'];

        //combine all the fields from the attendee forms
        $event_addit_fields = array();
        foreach ( $addit_forms as $_form_id => $_form_info ) {

            //$event_addit_fields += $_form_info['epl_form_fields'];
            $event_addit_fields = array_merge( $event_addit_fields, $_form_info['epl_form_fields'] );
        }

        $epl_fields_inside_form = array_flip( $tickey_buyer_fields ); //get the field ids inside the form
        $epl_addit_fields_inside_form = array_flip( $event_addit_fields ); //get the field ids inside the form
        //when creating a form in form manager, the user may rearrange fields.  Find their desired order
        $epl_fields_to_display = $this->epl_util->sort_array_by_array( $available_fields, $epl_fields_inside_form );
        $epl_addit_fields_to_display = $this->epl_util->sort_array_by_array( $available_fields, $epl_addit_fields_inside_form );

        //final list of all the fields to display
        $epl_fields_to_display = $epl_fields_to_display + $epl_addit_fields_to_display;


        $csv_row = '';
        $header_row = array();
        $header_pulled = false;
        $row = array();
        $form_data_header = array();
        $form_data = array();
        //$header_row[] = '';
        $header_row[] = epl__( 'Regis ID' );
        $header_row[] = epl__( 'Regis Date' );

        $header_row[] = epl__( 'Status' );
        $header_row[] = epl__( 'Payment Method' );
        $header_row[] = epl__( 'Total' );
        $header_row[] = epl__( 'Amount Paid' );
        $header_row[] = epl__( 'Discount Code' );

        $form_data_header[] = epl__( 'Event Date' );
        $form_data_header[] = epl__( 'Time' );
        $form_data_header[] = epl__( 'Ticket' );

        //get all the registration post ids for this event
        $regis_ids = $this->get_event_regis_post_ids( false, $regis_post_id );

        //as of 1.1, the dates are stored as timestamps.
        //This will format the date for display based on the settings admin date format.
        foreach ( $event_details['_epl_start_date'] as $k => &$v ) {
            //if ( $v >= EPL_DATE )
            // $v = epl_admin_date_display( $v );
            //else
            // unset( $event_details['_epl_start_date'][$k] );
        }

        $has_addit_att_forms = !epl_is_empty_array( $event_details['_epl_addit_regis_forms'] );




        $zebra = 'odd';
        //for each registration
        foreach ( $regis_ids as $regis_id => $att_count ) {
            //setup the registration details
            $regis_data = $this->setup_regis_details( $regis_id );
            //echo "<pre class='prettyprint'>" . __LINE__ . "> " . print_r($regis_data, true). "</pre>";
            //Sometime there may be incomplete db records.  These will cause issues below.
            //In those cases, skip and move to the next item
            if ( !isset( $regis_data['_epl_dates']['_epl_start_date'][$event_id] ) )
                continue;


            if ( get_the_regis_status( null, true ) <= 1 )
                continue;
            $zebra = ($zebra == 'odd') ? 'even' : 'odd';

            //event times and prices
            //$event_times = $regis_data['_epl_dates']['_epl_start_time'][$event_id];
            //$event_prices = $regis_data['_epl_dates']['_epl_start_time'][$event_id];

            $disc_code_id = epl_get_element( 'discount_code_id', $regis_data['_epl_events'][$event_id]['money_totals'] );

            $disc_code = epl_get_element_m( $disc_code_id, '_epl_discount_code', $event_details );

            $reserved_dates = epl_get_element_m( $event_id, '_epl_start_date', $regis_data['_epl_dates'], array() );
            $reserved_times = epl_get_element_m( $event_id, '_epl_start_time', $regis_data['_epl_dates'], array() );

            if ( $date_id && !in_array( $date_id, $reserved_dates ) )
                continue;

            if ( $time_id && !in_array( $time_id, $reserved_times ) )
                continue;
            //this isolates the dates and times that the user has registered for
            //$reserved_dates = implode( ' & ', array_intersect_key( $event_details['_epl_start_date'], array_flip( $reserved_dates ) ) );
            $reserved_times_display = implode( ' & ', array_intersect_key( $event_details['_epl_start_time'], array_flip( $reserved_times ) ) );



            //init vars
            $date_labels = array();
            $date_labels[0] = '';
            $time_labels = array();
            $time_labels[0] = '';
            $ticket_labels = array();
            $ticket_labels[0] = $att_count;
            $purchased_tickets = ( array ) $regis_data['_epl_dates']['_att_quantity'][$event_id];

            //?????????
            $start = 1;
            foreach ( $purchased_tickets as $price_id => $qty ) {


                $_qty = (is_array( $qty )) ? array_sum( $qty ) : $qty; //current( $qty );
                if ( $_qty > 0 ) {

                    $date_label[] = current( ( array ) $regis_data['_epl_dates']['_epl_start_date'][$event_id] );
                    if ( epl_get_element( '_epl_pricing_type', $event_details ) == 10 ) {
                        if ( in_array( $event_details['_epl_price_parent_time_id'][$price_id], ( array ) $regis_data['_epl_dates']['_epl_start_time'][$event_id] ) ) {

                            $time_labels = array_pad( $time_labels, $start + $_qty, epl_get_element( $event_details['_epl_price_parent_time_id'][$price_id], $event_details['_epl_start_time'] ) );
                        }
                        else {
                            $time_labels = array_pad( $time_labels, $start + $_qty, '' );
                        }
                    }


                    $ticket_labels = array_pad( $ticket_labels, $start + $_qty, $event_details['_epl_price_name'][$price_id] );

                    $start+=$_qty;
                }
            }

            $_r = array();

            $regis_status = (isset( $regis_data['_epl_regis_status'] )) ? $epl_fields['epl_regis_payment_fields']['_epl_regis_status']['options'][$regis_data['_epl_regis_status']] : '';
            $_pm_id = EPL_Registration_model::get_instance()->setup_current_data( $regis_data )->get_payment_profile_id();

            $payment_method = (isset( $regis_data['_epl_payment_method'] ) && $regis_data['_epl_payment_method'] != '') ? $epl_fields['epl_regis_payment_fields']['_epl_payment_method']['options'][$_pm_id] : '';

            $grand_total = epl_get_formatted_curr( epl_nz( $regis_data['_epl_grand_total'], 0.00 ) );
            $amount_paid = epl_get_formatted_curr( epl_nz( $regis_data['_epl_payment_amount'], 0.00 ) );

            $attendee_info = $regis_data['_epl_attendee_info'];

            //################################### Ticket buyer Data ############################################
            //$row[] = epl__( 'Registrant' );

            if ( !$has_addit_att_forms ) {


                $form_data[] = ''; //epl_escape_csv_val( $regis_date );
                $form_data[] = ''; //$regis_time; //(epl_is_date_level_time ( ))?$regis_time:$time_labels[$i]; //

                $form_data[] = ''; //epl_escape_csv_val( epl_get_element( $ticket_id, $event_details['_epl_price_name'] ) ); //$regis_price;


                foreach ( $epl_fields_to_display as $field_id => $field_atts ) {

                    $value = (isset( $attendee_info[$field_id] )) ? epl_get_element( 0, $attendee_info[$field_id][$event_id] ) : '';

                    if ( $field_atts['input_slug'] == 'email' ) {

                        $email_list[$regis_id] = $value;

                        if ( $regis_post_id && $regis_post_id != $regis_id )
                            unset( $email_list[$regis_id] );
                    }

                    if ( $field_atts['input_type'] == 'select' || $field_atts['input_type'] == 'radio' ) {

                        $value = (isset( $field_atts['epl_field_choice_text'][$value] ) && $field_atts['epl_field_choice_text'][$value] !== '') ? $field_atts['epl_field_choice_text'][$value] : $value;
                    }
                    elseif ( $field_atts['input_type'] == 'checkbox' ) {

                        if ( !epl_is_empty_array( $field_atts['epl_field_choice_value'] ) )
                            $value = (implode( ',', ( array ) $value ) );
                        else
                        if ( is_array( $value ) )
                            $value = (implode( ',', array_intersect_key( $field_atts['epl_field_choice_text'], array_flip( ( array ) $value ) ) ));
                    }

                    $form_data[] = epl_escape_csv_val( html_entity_decode( htmlspecialchars_decode( $value, ENT_QUOTES ) ) );
                }

                $row = (!$names_only) ? array_merge( $row, $form_data ) : $form_data;

                if ( $table )
                    $this->epl->epl_table->add_row( $row, $zebra );


                $csv_row .= implode( ",", $row ) . "\r\n";
                $row = array();
            }
            //###################  End Ticket Buyer Data #########################################

            $tickets_to_show = array_intersect_key( $purchased_tickets, $event_details['_epl_price_name'] );

            $counter = 1;
            $att_counter = 1;

            foreach ( $tickets_to_show as $ticket_id => $ticket_qty ) {

                if ( is_array( $ticket_qty ) ) {
                    $tmp_price_inner_keys = array_keys( $ticket_qty );
                    $ticket_qty = array_sum( $ticket_qty );
                }
                if ( $ticket_qty == 0 )
                    continue;


                for ( $i = 0; $i < $ticket_qty; $i++ ) {


                    //$row[] = ''; //epl_get_element( '_epl_addit_regis_form_counter_label', $event_details, epl__( 'Attendee' ) ) . ' ' . $att_counter;
                    $grand_total = '';
                    $amount_paid = '';
                    $regis_status = get_the_regis_status();
                    $payment_method = '';

                    $ticket_label = epl_escape_csv_val( epl_get_element( $ticket_id, $event_details['_epl_price_name'] ) );

                    if ( epl_is_date_level_price() ) {
                        $reserved_date_key = $tmp_price_inner_keys[$i];
                        //echo "<pre class='prettyprint'>" . __LINE__ . "> " . print_r($reserved_date_key, true). "</pre>";
                        //$reserved_dates = epl_get_element_m( $reserved_date_key, '_epl_start_date', $event_details );
                    }

                    if ( epl_is_date_level_time() ) {
                        $reserved_time_key = $reserved_times[$reserved_date_key];
                        $reserved_times_display = epl_get_element_m( $reserved_time_key, '_epl_start_time', $event_details );
                    }

                    $key = $regis_id . '_' . $counter;

                    $form_data[$key]['att_dates'] = array_flip( $reserved_dates );
                    $form_data[$key]['att_times'] = $reserved_times_display; //(epl_is_date_level_time ( ))?$regis_time:$time_labels[$i]; //

                    $form_data[$key]['ticket'] = $ticket_label; //$regis_price;
                    $form_data[$key]['regis_status'] = $regis_status; //$regis_price;
                    $form_data[$key]['regis_status_id'] = get_the_regis_status( null, true ); //$regis_price;
                    //$form_data[] = epl_get_element('_pack_attendance_dates_2035_' . $ticket_id . '_' . ($i+1) ); //$regis_price;

                    $pack_dates = epl_get_element( "_pack_attendance_dates_{$event_id}_{$ticket_id}_" . ($i + 1), $regis_data ); //$regis_price;

                    if ( !epl_is_empty_array( $pack_dates ) ) {
                        $form_data[$key]['att_dates'] = $pack_dates;
                    }
                    $form_data[$key]['pack_size'] = epl_get_element_m( $ticket_id, '_epl_price_pack_size', $event_details ); //$regis_price;

                    /* form data, if any */
                    foreach ( $epl_fields_to_display as $field_id => $field_atts ) {
                        if ( !$header_pulled )
                            $form_data_header[] = epl_escape_csv_val( html_entity_decode( htmlspecialchars_decode( $field_atts['label'], ENT_QUOTES ) ) );

                        $value = '';


                        if ( isset( $attendee_info[$field_id][$event_id][$ticket_id] ) ) {

                            $value = epl_get_element( $counter, $attendee_info[$field_id][$event_id][$ticket_id] );
                        }
                        elseif ( isset( $attendee_info[$field_id][$event_id][$counter] ) ) {
                            $value = $attendee_info[$field_id][$event_id][$counter];
                        }



                        if ( $field_atts['input_slug'] == 'first_name' || $field_atts['input_slug'] == 'last_name' ) {


                            $form_data[$key][$field_atts['input_slug']] = epl_escape_csv_val( html_entity_decode( htmlspecialchars_decode( $value, ENT_QUOTES ) ) );
                        }
                    }

                    $header_pulled = true;
                    //decode special chars (Swedish, Nordic)

                    $row = (!$names_only) ? array_merge( $row, $form_data ) : $form_data;

                    array_walk( $row, create_function( '&$item', 'if(!is_array($item))$item = utf8_decode($item);' ) );




                    $csv_row .= implode( ",", $row ) . "\r\n";
                    $row = array();
                    //$form_data = array( );
                    $counter++;
                    $att_counter++;
                }
            }
        }

        $header_row = (!$names_only) ? array_merge( $header_row, $form_data_header ) : $form_data_header;

        array_walk( $header_row, create_function( '&$item', '$item = utf8_decode($item);' ) );


        $header_row = array();
        $header_row_pulled = false;
        //placeholders
        $header_row[] = '<div style="width:100px;">&nbsp;</div>';
        $header_row[] = '';
        $header_row[] = '';
        $header_row[] = '';
        $header_row[] = 'Status';

        if ( $table ) {
            $tmpl = array( 'table_open' => '<table border="1" cellpadding="0" cellspacing="0" class="" id="epl_daily_schedule_table">' );

            $this->epl->epl_table->set_template( $tmpl );

            //$this->epl->epl_table->set_heading( $header_row );

            $footer = array();
            $footer[] = 'Totals';
            $footer[] = '';
            $footer[] = '';
            $footer[] = '';
            $footer[] = '';

            $day_total = 0;
            foreach ( $form_data as $regis_id => $reg_data ) {

                $pack_counter = count( $reg_data['att_dates'] );
                $start_date_key = key( $reg_data['att_dates'] );
                $this_ticket_is_ok = false;

                $display_row = array();
                $display_row[] = $reg_data['ticket'];
                $display_row[] = $reg_data['att_times'];
                $display_row[] = $reg_data['first_name'];
                $display_row[] = $reg_data['last_name'];
                $display_row[] = $reg_data['regis_status'];
                $counter = 1;

                //if( $reg_data['regis_status'] == )

                foreach ( $event_details['_epl_start_date'] as $date_key => $timestamp ) {

                    if ( !isset( $footer[$date_key] ) )
                        $footer[$date_key] = 0;
                    //if ( isset( $reg_data['att_dates'][$date_key] ) ) {
                    if ( ($date_key == $start_date_key || $this_ticket_is_ok == true) && $pack_counter > 0 ) {

                        if ( $reg_data['regis_status_id'] == 5 )
                            $footer[$date_key] += 1;

                        if ( $reg_data['pack_size'] != '' ) {
                            $this_ticket_is_ok = true;
                            //$display_row[] = "<img src='" . EPL_FULL_URL . "images/accept.png' /><br />" . $counter . '/' . $reg_data['pack_size'];

                            $renew = '';
                            if ( ($reg_data['pack_size'] - $counter) == 1 )
                                $renew = "<img src='" . EPL_FULL_URL . "images/error.png' />";

                            $display_row[] = $counter . '/' . $reg_data['pack_size'] . $renew;
                            $counter++;
                            $pack_counter--;
                        }else {
                            $display_row[] = "<img src='" . EPL_FULL_URL . "images/accept.png' />";
                            $this_ticket_is_ok = false;
                        }
                    }
                    else {
                        $display_row[] = '';
                        $this_ticket_is_ok = false;
                    }

                    if ( !$header_row_pulled ) {

                        $_d = epl_admin_date_display( $timestamp );
                        if ( $timestamp < EPL_DATE )
                            $header_row[] = "<span style='color:#bbb'>$_d</span>";
                        else
                            $header_row[] = $_d;
                    }
                }
                //$footer[] = $day_total;
                //$day_total = 0;
                $header_row_pulled = true;

                $zebra = ($reg_data['regis_status_id'] == 1) ? 'epl_incomplete' : '';
                $zebra_style = ($reg_data['regis_status_id'] == 1) ? 'background-color:  #feffd4;' : '';

                $this->epl->epl_table->add_row( $display_row, $zebra, $zebra_style );
            }

            $this->epl->epl_table->add_row( $footer );

            $this->epl->epl_table->set_heading( $header_row );



            $t = $this->epl->epl_table->generate();
            $url = admin_url( "edit.php?post_type=epl_event&epl_action=epl_daily_schedule&table_view=1&epl_controller=epl_registration&event_id=$event_id&print=1" );
            $print_icon = (!isset( $_REQUEST['print'] )) ? '<div><a href="' . $url . '" target="_blank"><img src="' . EPL_FULL_URL . 'images/printer.png" /></a></div>' : '';
            return $print_icon . $t;
        }
        elseif ( $email_only ) {
            return $email_list;
        }
        else {
            echo implode( ",", $header_row ) . "\r\n";
            echo $csv_row;
            exit();
        }
    }


    function epl_excel_attendee_list() {


        $event_id = $_REQUEST['event_id'];
        $_totals = $this->get_event_regis_snapshot( $_REQUEST['event_id'] );

        //echo "<pre class='prettyprint'>" . print_r( $_totals, true ) . "</pre>";
        $this->set_event_regis_post_ids( $_REQUEST['event_id'] );

        global $event_details;
        //epl_log( "debug", "<pre>" . __LINE__ . '> ' . print_r($event_details, true ) . "</pre>" );

        $event_title = $event_details['post_title'];
        $filename = str_replace( " ", "-", $event_title ) . "_" . date_i18n( "m-d-Y" );
        /* header( "Content-type: application/x-msdownload; charset=UTF-8", true, 200 );
          header( "Content-Disposition: attachment; filename={$filename}.csv" );
          header( "Pragma: no-cache" );
          header( "Expires: 0" ); */

        //$this->setup_event_details( $event_id );
        $this->get_values();

        //echo "<pre class='prettyprint'>" . print_r($event_details, true). "</pre>";
        $event_ticket_buyer_forms = array_flip( ( array ) $event_details['_epl_primary_regis_forms'] );
        $event_addit_forms = (isset( $event_details['_epl_addit_regis_forms'] ) && $event_details['_epl_addit_regis_forms'] != '') ? array_flip( $event_details['_epl_addit_regis_forms'] ) : array();

        //find the list of all forms
        $available_forms = $this->get_list_of_available_forms();
        $available_fields = $this->get_list_of_available_fields();

        //isolate the forms that are selected inside the event
        $ticket_buyer_forms = array_intersect_key( $available_forms, $event_ticket_buyer_forms );
        $addit_forms = array_intersect_key( $available_forms, $event_addit_forms );

        //This will combine all the fields in all the forms so that we can construct a header row.
        $tickey_buyer_fields = array();
        foreach ( $ticket_buyer_forms as $_form_id => $_form_info )
            $tickey_buyer_fields += $_form_info['epl_form_fields'];

        $event_addit_fields = array();
        foreach ( $addit_forms as $_form_id => $_form_info )
            $event_addit_fields += $_form_info['epl_form_fields'];



        $epl_fields_inside_form = array_flip( $tickey_buyer_fields ); //get the field ids inside the form
        $epl_addit_fields_inside_form = array_flip( $event_addit_fields ); //get the field ids inside the form
        //when creating a form in form manager, the user may rearrange fields.  Find their desired order
        $epl_fields_to_display = $this->epl_util->sort_array_by_array( $available_fields, $epl_fields_inside_form );
        $epl_addit_fields_to_display = $this->epl_util->sort_array_by_array( $available_fields, $epl_addit_fields_inside_form );

        $epl_fields_to_display = $epl_fields_to_display + $epl_addit_fields_to_display;
        $csv_row = '';
        $header_row = array();
        $header_pulled = false;
        $row = array();
        $header_row[] = '';
        $header_row[] = epl__( 'Registration ID' );
        //$header_row[] = epl__( 'Regis Date' );
        //$header_row[] = epl__( 'Event Date' );
        //$header_row[] = epl__( 'Time' );
        $header_row[] = epl__( 'Ticket' );
        $header_row[] = epl__( 'Status' );
        $header_row[] = epl__( 'Payment Method' );
        $header_row[] = epl__( 'Total' );
        $header_row[] = epl__( 'Amount Paid' );


        $regis_ids = $this->get_event_regis_post_ids( false );


        //as of 1.1, the dates are stored as timestamps.
        //This will format the date for display based on the settings admin date format.
        foreach ( $event_details['_epl_start_date'] as $k => &$v )
            $v = epl_admin_date_display( $v );

        $_d = array();

        foreach ( $regis_ids as $regis_id => $att_count ) {
            //$regis_data = $this->get_post_meta_all( $regis_id );
            $regis_data = $this->setup_regis_details( $regis_id );
            // epl_log( "debug", "<pre>" . __LINE__ . '> ' . print_r($regis_data, true ) . "</pre>" );
            //Sometime there may be incomplete db records.  These will cause issues below.
            //In those cases, skip and move to the next item
            if ( !isset( $regis_data['_epl_dates']['_epl_start_date'][$event_id] ) )
                continue;

            $event_times = $regis_data['_epl_dates']['_epl_start_time'][$event_id];
            $event_prices = $regis_data['_epl_dates']['_epl_start_time'][$event_id];

            $regis_date = implode( ' & ', array_intersect_key( $event_details['_epl_start_date'], array_flip( ( array ) $regis_data['_epl_dates']['_epl_start_date'][$event_id] ) ) );
            $regis_time = implode( ' & ', array_intersect_key( $event_details['_epl_start_time'], array_flip( ( array ) $regis_data['_epl_dates']['_epl_start_time'][$event_id] ) ) );

            $date_labels = array();
            $date_labels[0] = '';
            $time_labels = array();
            $time_labels[0] = '';
            $ticket_labels = array();
            $ticket_labels[0] = $att_count;
            $purchased_tickets = ( array ) $regis_data['_epl_dates']['_att_quantity'][$event_id];

            $start = 1;
            foreach ( $purchased_tickets as $price_id => $qty ) {
                $_qty = (is_array( $qty )) ? array_sum( $qty ) : $qty; //current( $qty );
                if ( $_qty > 0 ) {

                    $date_label[] = current( ( array ) $regis_data['_epl_dates']['_epl_start_date'][$event_id] );
                    if ( epl_get_element( '_epl_pricing_type', $event_details ) == 10 ) {
                        if ( in_array( $event_details['_epl_price_parent_time_id'][$price_id], ( array ) $regis_data['_epl_dates']['_epl_start_time'][$event_id] ) ) {

                            $time_labels = array_pad( $time_labels, $start + $_qty, epl_get_element( $event_details['_epl_price_parent_time_id'][$price_id], $event_details['_epl_start_time'] ) );
                        }
                        else {
                            $time_labels = array_pad( $time_labels, $start + $_qty, '' );
                        }
                    }


                    $ticket_labels = array_pad( $ticket_labels, $start + $_qty, $event_details['_epl_price_name'][$price_id] );

                    $start+=$_qty;
                }
            }

            $_r = array();


            $regis_status = (isset( $regis_data['_epl_regis_status'] )) ? $this->ind_fields['_epl_regis_status']['options'][$regis_data['_epl_regis_status']] : '';
            $payment_method = (isset( $regis_data['_epl_payment_method'] ) && $regis_data['_epl_payment_method'] != '') ? $this->ind_fields['_epl_payment_method']['options'][$regis_data['_epl_payment_method']] : '';

            $grand_total = epl_get_formatted_curr( epl_nz( $regis_data['_epl_grand_total'], 0.00 ) );
            $amount_paid = epl_get_formatted_curr( epl_nz( $regis_data['_epl_payment_amount'], 0.00 ) );


            for ( $i = 0; $i <= $att_count; $i++ ) {
                $registrant = false;
                $attendee_info = $regis_data['_epl_attendee_info'];
                if ( $i == 0 ) {
                    $registrant = true;
                    $row[] = epl__( 'Registrant' );
                }
                else {
                    $row[] = epl_get_element( '_epl_addit_regis_form_counter_label', $event_details, epl__( 'Attendee' ) );
                    //$grand_total = '';
                    //$amount_paid = '';
                    //$regis_status = '';
                    //$payment_method = '';
                }
                $row[] = $regis_data['__epl']['_regis_id'];
                //$row[] = $regis_data['post_date'];
                //$row[] = epl_escape_csv_val( $regis_date );
                //$row[] = $regis_time; //(epl_is_date_level_time ( ))?$regis_time:$time_labels[$i]; //

                $row[] = htmlspecialchars_decode( $ticket_labels[$i] ); //$regis_price;

                $row[] = $regis_status;
                $row[] = $payment_method;
                $row[] = epl_get_currency_symbol( $grand_total );
                $row[] = $amount_paid;


                foreach ( $epl_fields_to_display as $field_id => $field_atts ) {
                    if ( !$header_pulled )
                        $header_row[] = epl_escape_csv_val( html_entity_decode( htmlspecialchars_decode( $field_atts['label'] ), ENT_QUOTES ) );

                    $value = '';


                    $value = ((isset( $attendee_info[$field_id][$event_id][$i] )) ? $attendee_info[$field_id][$event_id][$i] : '');

                    if ( $field_atts['input_type'] == 'select' || $field_atts['input_type'] == 'radio' ) {

                        $value = (isset( $field_atts['epl_field_choice_text'][$value] ) && $field_atts['epl_field_choice_text'][$value] != '') ? $field_atts['epl_field_choice_text'][$value] : $value;
                    }
                    elseif ( $field_atts['input_type'] == 'checkbox' ) {
                        $value = (implode( ',', array_intersect_key( $field_atts['epl_field_choice_text'], array_flip( ( array ) $value ) ) ));
                    }
                    else {

                        $value = html_entity_decode( htmlspecialchars_decode( epl_get_element( $i, $attendee_info[$field_id][$event_id] ) ), ENT_QUOTES );
                    }

                    $row[] = htmlentity_decode( $value, ENT_QUOTES );
                    //decode special chars (Swedish, Nordic)
                    //array_walk( $row, create_function( '&$item', '$item = utf8_decode($item);' ) );
                }

                $header_pulled = true;

                if ( !$registrant )
                    $_d[] = $row;
                //$csv_row .= implode( ",", $row ) . "\r\n";
                $row = array();
            }
        }

        $filename = epl__( 'Attendee List' ) . ', ' . $event_title . ", " . date_i18n( "F j, Y" );

        $this->excel_file_generator( $header_row, $_d, $filename );

        //echo implode( ",", $header_row ) . "\r\n";
        //echo $csv_row;

        exit();
    }


    function excel_file_generator( $headings, $data, $filename = 'Attendee List' ) {



        $this->epl->load_library( "php_writeexcel-0.3.0/class.writeexcel_workbook.inc.php", false );
        $this->epl->load_library( "php_writeexcel-0.3.0/class.writeexcel_worksheet.inc.php", false );

        $fname = tempnam( EPL_FULL_PATH, "tempexport" );
        $workbook = new writeexcel_workbook( $fname );
        $worksheet = $workbook->addworksheet();

        $heading = $workbook->addformat( array(
            bold => 1,
            color => 'black',
            size => 11,
            merge => 1,
            align => 'left',
            border_color => "black",
            top => 1,
            bottom => 1,
            left => 1,
            right => 1
                ) );
        $heading->set_text_wrap();
        //Column widths

        $worksheet->set_column( 'A:S', 14 );


        //using this so the Nordic letters are encoded correctly
        //array_walk( $headings, create_function( '&$item', '$item = utf8_decode($item);' ) );
        $worksheet->set_row( 0, 30 );
        $worksheet->write( 0, 0, $headings, $heading );

        $heading = $workbook->addformat( array(
            bold => 0,
            color => 'blue',
            size => 11,
            align => 'left'
                ) );

        $format1 = $workbook->addformat();
        $format1->set_color( 'black' );
        //$border1->set_bold();
        $format1->set_size( 11 );
        $format1->set_pattern( 0x1 );
        $format1->set_fg_color( 'white' );
        $format1->set_border_color( 'black' );
        $format1->set_top( 1 );
        $format1->set_bottom( 1 );
        $format1->set_left( 1 );
        $format1->set_right( 1 );
        $format1->set_align( 'left' );
        $format1->set_align( 'vcenter' );
        $format1->set_text_wrap();

        //TODO make this better, doing all this for the set_zize 8
        $format2 = $workbook->addformat();
        $format2->set_color( 'black' );
        $format2->set_size( 8 );
        $format2->set_pattern( 0x1 );
        $format2->set_fg_color( 'white' );
        $format2->set_border_color( 'black' );
        $format2->set_top( 1 );
        $format2->set_bottom( 1 );
        $format2->set_left( 1 );
        $format2->set_right( 1 );
        $format2->set_align( 'left' );
        $format2->set_align( 'vcenter' );
        $format2->set_text_wrap();


        $data_row = 1;

        foreach ( $data as $k => $v ) {

            $worksheet->set_row( $data_row, 53 );

            $col = 0;



            foreach ( $v as $_k => $_v ) {
                $_format = $format1;
                if ( $col == 2 ) {
                    $_format = $format2;
                }

                if ( $col == 1 )
                    $_v = $_v . ' ';


                $worksheet->write( $data_row, $col, $_v, $_format );

                $col++;
            }
            $data_row++;
        }

        $worksheet->print_area( 0, 0, --$data_row, --$col );
        $worksheet->set_zoom( 85 );
        $worksheet->fit_to_pages( 1, 1 );
        $worksheet->set_margins_LR( 0.64 );
        $worksheet->set_margins_TB( 0.5 );
        $worksheet->set_landscape();

        $workbook->close();
        header( "Content-Type: application/x-msexcel; name=\"attendee_list.xls\"" );
        header( "Content-Disposition: inline; filename=\"{$filename}.xls\"" );
        $fh = fopen( $fname, "rb" );
        fpassthru( $fh );
        @unlink( $fname );
    }


    function wildcard_lookup( $lookup, $limit = 5 ) {

        global $wpdb, $regis_details;

        $lookup = $wpdb->escape( $lookup );

        $s_key = explode( '-', epl_get_element( 's_key', $_REQUEST, false ) );

        $filter_event_id = $s_key[0];
        $q = $wpdb->get_results( "SELECT p.ID, p.post_title,p.post_date,  pm.meta_key, pm.meta_value 
                FROM {$wpdb->postmeta} pm 
                    JOIN {$wpdb->posts} p 
                        ON p.ID = pm.post_id 
                        WHERE meta_key='__epl' 
                        AND p.post_status ='publish' 
                        AND meta_value like '%$lookup%' 
                            ORDER BY meta_id DESC LIMIT $limit" );
        $_r = array();
        $data = array();
        $r = '';

        $available_fields = $this->get_list_of_available_fields();

        foreach ( $q as $row ) {
            setup_regis_details( $row->ID );

            $regis_data = maybe_unserialize( $row->meta_value );
            $regis_id = $regis_data['_regis_id'];


            if ( $filter_event_id && !isset( $regis_data[$regis_id]['_events'][$filter_event_id] ) )
                continue;
            $event_id = $filter_event_id;
            setup_event_details( $filter_event_id );
            $form_data = maybe_unserialize( $regis_data[$regis_id]['_attendee_info'] );

            $first_name = $this->get_attendee_form_value( 'ticket_buyer', 'first_name', $form_data, true );
            $last_name = $this->get_attendee_form_value( 'ticket_buyer', 'last_name', $form_data );
            $email = $this->get_attendee_form_value( 'ticket_buyer', 'email', $form_data );
            $found = false;
            switch ( $lookup )
            {
                case (stripos( $first_name, $lookup ) !== false):
                    $found = true;
                    break;
                case (stripos( $last_name, $lookup ) !== false):
                    $found = true;
                    break;
                case (stripos( $email, $lookup ) !== false):
                    $found = true;
                    break;
            }
            //if (!$found) continue;

            $t = $this->get_purchased_tickets( $regis_id, $event_id );

            $data['r'] = array(
                'regis_date' => $row->post_date,
                'regis_post_id' => $row->ID,
                'event_id' => $event_id,
                'regis_id' => $regis_id,
                'first_name' => $first_name,
                'last_name' => $last_name,
                'email' => $email,
                'form_data' => $form_data,
                'available_fields' => $available_fields,
                'tickets' => $t,
                'regis_status' => get_the_regis_status(),
                'regis_user_id' => $regis_details['_epl_regis_user_id'],
            );

            $r .= $this->epl->load_view( 'admin/user-regis-manager/regis-name-lookup-result-rows', $data, true );
        }

        $data['r'] = $r;

        $r = $this->epl->load_view( 'admin/user-regis-manager/regis-name-lookup-result-response', $data, true );

        return $r;

        return $this->epl_util->epl_response( array( 'html' => $r ) );
    }


    function get_attendee_form_value( $form = null, $field = null, $attendee_info, $refresh = false ) {

        if ( !$form )
            return null;
        static $data = array();
        if ( $refresh ) {

            $form_fields = $this->get_list_of_available_fields();

            $_data = array_merge_recursive( $attendee_info, $form_fields );

            foreach ( $_data as $k => $v ) {
                $data[$v['input_slug']] = $v;
            }
        }

        if ( !$field )
            return $data;

        if ( $form == 'ticket_buyer' ) {

            if ( ($r = epl_get_element( 0, $data[$field], '' )) == '' ) {
                $event_id = key( $data[$field] );

                //echo "<pre class='prettyprint'>" . __LINE__ . ">$field " . print_r($data, true). "</pre>";
                $r = epl_get_element_m( 0, $event_id, $data[$field] );
            }
        }

        return $r;
    }


    function get_purchased_tickets( $regis_id, $event_id ) {
        global $event_details, $regis_details;

        $purchased_tickets = $regis_details['__epl'][$regis_id]['_dates']['_att_quantity'][$event_id];
        $r = array();
        foreach ( $event_details['_epl_price_name'] as $price_id => $price_name ) {
            if ( $purchased_tickets[$price_id][0] > 0 ) {

                $exp = '';

                if ( epl_get_element_m( $price_id, '_epl_price_pack_type', $event_details ) == 'time' ) {
                    $mem_l = epl_get_element_m( $price_id, '_epl_price_pack_time_length', $event_details );
                    $mem_lt = epl_get_element_m( $price_id, '_epl_price_pack_time_length_type', $event_details );

                    $start = (!epl_is_empty_array( $regis_details ) ? strtotime( $regis_details['post_date'] ) : EPL_DATE);

                    $exp = ' (' . epl_formatted_date( strtotime( "+ $mem_l $mem_lt", $start ) ) . ')';
                }

                $r[$price_id] = array(
                    'ticket_name' => $price_name . $exp,
                    'qty' => $purchased_tickets[$price_id][0]
                );
            }
        }


        return $r;
    }


    function get_used_pack_counts( $user_id ) {
        global $wpdb;

        $q = $wpdb->query( 'SELECT SUM' );
    }


    function handle_upload( $post_id, $attachment_id, $file = null ) {

        if ( is_null( $file ) )
            return;

        global $epl_fields;
        $this->epl->load_config( 'global-discount-fields' );

        $fields = array_keys( $epl_fields['epl_social_discount_fields'] );


        foreach ( $fields as $v )
            $$v = array();

        $arr = array();
        $row = 0;
        if ( ($handle = fopen( $file, "r" )) !== FALSE ) {

            while ( ($data = fgetcsv( $handle, 2000, "," )) !== FALSE ) {
                $row++;
                //if ( $row == 1 )
                //  continue;

                /* if ( $row == 400 ) {

                  break;
                  break;
                  } */

                $code_id = $data[0];

                $_epl_discount_code[$code_id] = $data[0];
                $_epl_discount_buyer[$code_id] = $data[1];
                $_epl_discount_amount[$code_id] = ($data[2] != '' ? $data[2] : 0);
                $_epl_discount_max_usage[$code_id] = ($data[3] != '' ? $data[3] : 1);
                $_epl_discount_end_date[$code_id] = epl_get_date_timestamp( $data[4] );

                //$_epl_discount_status[$code_id] = $data[3];


                $_epl_discount_type[$code_id] = 5;
                $_epl_discount_active[$code_id] = 10;
            }
            fclose( $handle );



            foreach ( $fields as $v )
                add_post_meta( $post_id, $v, $$v );
        }
    }

}
