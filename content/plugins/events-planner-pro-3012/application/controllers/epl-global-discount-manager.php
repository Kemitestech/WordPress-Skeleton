<?php

class EPL_Global_Discount_Manager extends EPL_Controller {

    const post_type = 'epl_global_discount';


    function __construct() {

        parent::__construct();

        epl_log( 'init', get_class() . "  initialized", 1 );

        $this->epl->load_config( 'global-discount-fields' );


        $this->ecm = $this->epl->load_model( 'epl-common-model' );

        global $epl_fields;
        $this->fields = $epl_fields;
        //$this->epl_fields = $this->epl_util->combine_array_keys( $this->fields );
        $this->epl_fields = $epl_fields;

        $post_ID = '';
        if ( isset( $_GET['post'] ) )
            $post_ID = $_GET['post'];
        elseif ( isset( $_POST['post_ID'] ) )
            $post_ID = $_POST['post_ID'];

        $this->data['values'] = $this->ecm->get_post_meta_all( ( int ) $post_ID, false, true );


        $this->edit_mode = (epl_get_element( 'post', $_GET ) || epl_get_element( 'post_ID', $_REQUEST ));


        if ( isset( $_REQUEST['epl_ajax'] ) && $_REQUEST['epl_ajax'] == 1 ) {
            $this->run();
        }
        else {
            add_action( 'default_title', array( $this, 'pre' ) );
            add_action( 'add_meta_boxes', array( $this, 'epl_add_meta_boxes' ) );
            add_action( 'save_post', array( $this, 'save_postdata' ) );
            add_filter( 'manage_edit-' . self::post_type . '_columns', array( $this, 'add_new_columns' ) );

            add_action( 'manage_' . self::post_type . '_posts_custom_column', array( $this, 'column_data' ), 10, 2 );
        }
    }


    function pre( $title ) {

        $title = "Please enter discount name here";

        return $title;
    }


    function run() {

        $r = '';

        if ( $_POST['epl_action'] == 'get_discount_fields' ) {

            $r = $this->get_discount_fields();
        }
        if ( $_POST['epl_action'] == 'process_csv' ) {

            $r = $this->process_csv();
        }
        if ( $_POST['epl_action'] == 'get_discount_usage_report' ) {

            $r = $this->get_discount_usage_report();
        }

        echo $this->epl_util->epl_response( array( 'html' => $r ) );
        die();
    }


    function epl_add_meta_boxes() {

        add_meta_box( 'epl-gd-meta-box', epl__( 'Discount Details' ), array( $this, 'gd_meta_box' ), self::post_type, 'normal', 'core' );
    }


    function save_postdata( $post_ID ) {
        if ( (defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) || empty( $_POST ) || epl_get_element( 'action', $_REQUEST ) == 'inline-save' )
            return;
        $this->epl_fields = $this->epl_util->combine_array_keys( $this->fields );
        update_post_meta( $post_ID, '_epl_discount_cat_include', '' );

        $this->ecm->_save_postdata( array( 'post_ID' => $post_ID, 'fields' => $this->epl_fields, 'edit_mode' => $this->edit_mode ) );
    }


    function process_csv() {
        if ( !empty( $_POST['post_id'] ) ) {

            $post_id = intval( $_POST['post_id'] );
            $attachment_id = intval( $_POST['attachment_id'] );


            //$post_type = get_post_type( $post_id );
            $file = get_attached_file( $attachment_id );

            global $valid_controllers;

            //if ( isset( $valid_controllers[$post_type] ) )
            $this->ecm->handle_upload( $post_id, $attachment_id, $file );
            $this->data['values'] = $this->ecm->get_post_meta_all( $post_id );
            $this->edit_mode = true;

            wp_delete_attachment( $attachment_id, true );
            return $this->get_social_discount_fields();
        }
    }


    function get_discount_usage_report() {

        $data['used_discount_codes'] = $this->get_used_discounts( 'discount_code_id' );

        $disc_type = 'epl_global_discount_fields';

        if ( $this->data['values']['_epl_global_discount_type'] == 'social' )
            $disc_type = 'epl_social_discount_fields';

        $rows_to_display = count( epl_get_element( '_epl_discount_amount', $this->data['values'], 1 ) );

        $_field_args = array(
            'section' => $this->fields['epl_global_discount_option_fields'],
            'fields_to_display' => array( '_epl_show_gd_code_input', '_epl_gd_input_label' ),
            'meta' => array( '_view' => 3, '_type' => 'row', '_rows' => 1, 'value' => $this->data['values'] )
        );

        $data['epl_discount_option_fields'] = $this->epl_util->render_fields( $_field_args );

        $list_of_forms = $this->ecm->_get_fields( 'epl_forms' );

        $_o = array( );
        foreach ( ( array ) $list_of_forms as $form_key => $form_atts ) {
            $_o[$form_key] = $form_atts['epl_form_label'];
        }


        $this->fields['epl_discount_fields']['_epl_discount_forms']['options'] = $_o;

        $epl_fields_to_display = array_keys( $this->fields['epl_global_discount_fields'] );

        $_field_args = array(
            'section' => $this->fields[$disc_type],
            'fields_to_display' => $epl_fields_to_display,
            'meta' => array( '_view' => 'raw', '_type' => 'row', '_rows' => $rows_to_display, 'value' => $this->data['values'], 're_key' => false )
        );

        $data['epl_discount_fields'] = $this->epl_util->render_fields( $_field_args );
        $data['vals'] = $this->data['values'];
        
        return $this->epl->load_view( 'admin/global-discounts/discount-usage-table', $data, true );
    }


    function gd_meta_box( $post, $values ) {

        $_field_args = $this->epl_fields['epl_global_discount_type']['_epl_global_discount_type'];

        $_field_args['value'] = $this->data['values']['_epl_global_discount_type'];

        if ( !$this->edit_mode )
            $data['epl_discount_type_dd'] = $this->epl_util->create_element( $_field_args );


        if ( $this->edit_mode ) {

            $data['discount_fields'] = $this->get_discount_fields();
        }

        echo $this->epl->load_view( 'admin/global-discounts/discounts-meta-box', $data, true );
    }


    function get_discount_fields() {


        $discount_type = epl_get_element( '_epl_global_discount_type', $_POST, false );

        if ( !$discount_type )
            $discount_type = epl_get_element( '_epl_global_discount_type', $this->data['values'], 'global' );

        $method = "get_{$discount_type}_discount_fields";


        if ( method_exists( __CLASS__, $method ) )
            return $this->$method();
    }


    function get_global_discount_fields() {


        $data['used_discount_codes'] = $this->get_used_discounts( 'discount_code_id' );
        $data['discount_import_action'] = $this->epl_util->create_element( $params );

        $rows_to_display = $this->edit_mode ? count( epl_get_element( '_epl_discount_method', $this->data['values'], 1 ) ) : 1;

        $_field_args = array(
            'section' => $this->fields['epl_global_discount_option_fields'],
            'fields_to_display' => array( '_epl_show_gd_code_input', '_epl_gd_input_label' ),
            'meta' => array( '_view' => 3, '_type' => 'row', '_rows' => 1, 'value' => $this->data['values'] )
        );

        $data['epl_discount_option_fields'] = $this->epl_util->render_fields( $_field_args );

        $list_of_forms = $this->ecm->_get_fields( 'epl_forms' );

        $_o = array( );
        foreach ( ( array ) $list_of_forms as $form_key => $form_atts ) {
            $_o[$form_key] = $form_atts['epl_form_label'];
        }


        $this->fields['epl_discount_fields']['_epl_discount_forms']['options'] = $_o;

        $epl_fields_to_display = array_keys( $this->fields['epl_global_discount_fields'] );

        $_field_args = array(
            'section' => $this->fields['epl_global_discount_fields'],
            'fields_to_display' => $epl_fields_to_display,
            'meta' => array( '_view' => 'raw', '_type' => 'row', '_rows' => $rows_to_display, 'value' => $this->data['values'], 're_key' => false )
        );

        $data['epl_discount_fields'] = $this->epl_util->render_fields( $_field_args );

        return $this->epl->load_view( 'admin/global-discounts/discount-global-discount-fields', $data, true );
    }


    function get_social_discount_fields() {
        $data['used_discount_codes'] = $this->get_used_discounts();
        //$this->fields['epl_discount_fields']['_epl_discount_forms']['options'] = $_o;
        $rows_to_display = $this->edit_mode ? count( epl_get_element( '_epl_discount_code', $this->data['values'], 1 ) ) : 1;

        $epl_fields_to_display = array_keys( $this->fields['epl_social_discount_fields'] );

        $_field_args = array(
            'section' => $this->fields['epl_social_discount_fields'],
            'fields_to_display' => $epl_fields_to_display,
            'meta' => array( '_view' => 'raw', '_type' => 'row', '_rows' => $rows_to_display, 'value' => $this->data['values'], 're_key' => false )
        );

        $data['epl_discount_fields'] = $this->epl_util->render_fields( $_field_args );

        $data['edit_mode'] = $this->edit_mode;

        return $this->epl->load_view( 'admin/global-discounts/discount-social-discount-fields', $data, true );
    }


    function get_used_discounts( $by = 'discount_code' ) {
        global $wpdb;
        $post_ID = intval($_POST['post_ID']);
        
        $WHERE = ($by == 'discount_code_id')?"discount_source_id = $post_ID AND":'';
        
        $regis = $wpdb->get_results( "SELECT * 
            FROM {$wpdb->epl_registration} 
            WHERE $WHERE NOT $by = ''" );
        
        $arr = array( );

        foreach ( $regis as $r ) {
            if ( !isset( $arr[$r->$by] ) )
                $arr[$r->$by] = array( );

            $arr[$r->$by][$r->regis_id] = array(
                'regis_id' => $r->regis_id,
                'regis_key' => $r->regis_key,
                'regis_date' => $r->regis_date,
            );
        }
        return $arr;
    }


    function add_new_columns( $current_columns ) {

        $new_columns['cb'] = '<input type="checkbox" />';

        //$new_columns['id'] = __( 'ID' );
        $new_columns['title'] = epl__( 'Coupon' );
        $new_columns['usage'] = '';

        $new_columns['author'] = epl__( 'Created By' );

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

            case 'type':

                break;
            case 'usage':
                echo "<a href='#' id ='{$id}' class='epl_discount_usage_view'>" . epl__( 'See Usage' ) . "</a>";
                break;

            default:
                break;
        }
    }

}
