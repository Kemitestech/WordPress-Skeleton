<?php

class EPL_Notification_manager extends EPL_Controller {

    const post_type = 'epl_notification';


    function __construct() {

        parent::__construct();

        epl_log( 'init', get_class() );

        $this->epl->load_config( 'notification-fields' );

        global $epl_fields;
        $this->ecm = $this->epl->load_model( 'epl-common-model' );

        $this->epl_fields = $epl_fields;
        $this->ind_fields = $this->epl_util->combine_array_keys( $this->epl_fields ); //this is each individualt field array
        $post_ID = '';
        if ( isset( $_GET['post'] ) )
            $post_ID = $_GET['post'];
        elseif ( isset( $_POST['post_ID'] ) )
            $post_ID = $_POST['post_ID'];

        $this->data['values'] = $this->ecm->get_post_meta_all( ( int ) $post_ID, $this->epl_fields );

        $this->edit_mode = (epl_get_element( 'post', $_GET ) || epl_get_element( 'post_ID', $_REQUEST ));

        add_action( 'add_meta_boxes', array( $this, 'epl_add_meta_boxes' ) );
        add_action( 'save_post', array( $this, 'save_postdata' ) );

        //post list manage screen columns - extra columns
        add_filter( 'manage_edit-' . self::post_type . '_columns', array( $this, 'add_new_columns' ) );
        //post list manage screen - column data
        add_action( 'manage_' . self::post_type . '_posts_custom_column', array( $this, 'column_data' ), 10, 2 );



        //add_action('init', array( &$this, 'run' ));
        //$this->m = $this->load_model( 'admin/epl-manage-events-model' );
    }


    function run() {
        
    }


    function epl_add_meta_boxes() {

        add_meta_box( 'epl-notification-options-meta-box', epl__( 'Notification Options' ), array( $this, 'event_notification_option_meta_box' ), self::post_type, 'normal', 'core' );
        add_meta_box( 'epl-notification-system-tags-meta-box', epl__( 'Available System Tags' ), array( $this, 'available_tags_meta_box' ), self::post_type, 'side', 'low' );
        add_meta_box( 'epl-notification-form-tags-meta-box', epl__( 'Form Field Tags' ), array( $this, 'event_notification_form_tags_meta_box' ), self::post_type, 'side', 'low' );
    }


    function save_postdata( $post_ID ) {
        if ( (defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE) || empty( $_POST ) )
            return;

        $this->ecm->_save_postdata( array( 'post_ID' => $post_ID, 'fields' => $this->ind_fields, 'edit_mode' => $this->edit_mode ) );
    }


    function available_tags_meta_box( $param ) {

        echo "{registration_detail_link}";
        //echo '<br /><i>*' . epl__( 'requires single-epl_registration.php template' ) . '</i>';
        echo "<br />{registration_id}";
        echo "<br />{event_name}";
        echo "<br />{event_details_link}";
        echo "<br />{location_details}";
        echo "<br />{location_map_link}";
        echo "<br />{payment_details}";
        echo "<br />{registration_details}";
        echo "<br />{registration_form_data}";
        echo "<br />{waitlist_approved_link}";
        echo "<br />{waitlist_approved_until}";
        echo "<div class='epl_info'> More coming.</div>";
    }


    function event_notification_option_meta_box( $post, $values ) {

        $epl_fields_to_display = array_keys( $this->epl_fields['epl_notification_fields'] );

        $_field_args = array(
            'section' => $this->epl_fields['epl_notification_fields'],
            'fields_to_display' => $epl_fields_to_display,
            'meta' => array( '_view' => 3, '_type' => 'row', 'value' => $this->data['values'] )
        );

        $data['epl_location_field_list'] = $this->epl_util->render_fields( $_field_args );


        $this->epl->load_view( 'admin/notifications/notification-manager-view', $data );
    }


    function event_notification_form_tags_meta_box( $post, $values ) {

        echo "<div class='epl_warning'>" . epl__( 'Currently only data from the Ticket Buyer forms can be displayed using these tags.' ) . "</div>";
        $fields = $this->ecm->get_list_of_available_fields();

        //echo "<pre class='prettyprint'>" . print_r($fields, true). "</pre>";
        $tmpl = array( 'table_open' => '<table cellpadding="0" cellspacing="0" class="epl_form_data_table">' );

        $this->epl->epl_table->set_template( $tmpl );
        $this->epl_table->set_heading( 'Slug', 'field' );

        foreach ( $fields as $field_id => $field_data ) {
            if ( $field_data['input_slug'] == '' )
                $m = epl__( "Please use the form manager to add a slug for this field" );
            else
                $m = $field_data['input_slug'];
            $this->epl_table->add_row( '{' . $m . '}', epl_trunc( $field_data['label'], 1 ) );
        }

        echo $this->epl_table->generate();

        echo "<div class='epl_info'> More options coming.</div>";
        return;

        /*        $this->fields_to_display = array_keys( $this->fields['epl_location'] );

          $data['epl_location_fields'] = $this->epl_util->render_fields( $this->fields['epl_location'], $this->fields_to_display, array( '_view' => 3, '_type' => 'row', 'value' => &$this->data['values'] ) );

          $this->epl->load_view( 'admin/registrations/registration-attendee-meta-box', $data ); */
    }


    function add_new_columns( $current_columns ) {

        $new_columns['cb'] = '<input type="checkbox" />';

        //$new_columns['id'] = __( 'ID' );
        $new_columns['title'] = epl__( 'Notification Name' );


        $new_columns['date'] = _x( 'Date', 'column name' );

        return $new_columns;
    }

    /*
     * Data for the modified cols
     */


    function column_data( $column_name, $id ) {
        //global $wpdb;


        switch ( $column_name )
        {
            case 'id':
                echo $id;
                break;


                break;
            default:
                break;
        } // end switch
    }

}
