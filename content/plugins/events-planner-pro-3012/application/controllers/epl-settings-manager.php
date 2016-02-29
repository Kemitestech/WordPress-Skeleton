<?php

if ( !class_exists( 'EPL_Settings_Manager' ) ) {

    class EPL_Settings_Manager extends EPL_Controller {


        function __construct() {

            parent::__construct();
            global $epl_fields;

            $this->epl->load_config( 'event-fields' );
            $this->epl->load_config( 'settings-fields' );

            $this->fields = $epl_fields;
            add_action( 'admin_notices', array( $this, 'settings_page' ) );
            add_action( 'admin_init', array( $this, 'set_options' ) );
            epl_has_a_key();
        }


        function settings_page() {


            if ( $_POST )
                $this->set_options();

            $v = $this->get_options();

            $data['epl_fields'] = $this->fields;

            $data['tabs'] = array(
                'general' => epl__( 'General' ),
                'registrations' => epl__( 'Registrations' ),
                'event-management' => epl__( 'Event Management' ),
                'fullcalendar-settings' => epl__( 'Calendar' ),
                'api-settings' => epl__( 'Advanced' )
            );


            $_field_args = array(
                'section' => $this->fields['epl_general_options'],
                'fields_to_display' => array_keys( $this->fields['epl_general_options'] ),
                'meta' => array( '_view' => 3, 'value' => $v['epl_general_options'] )
            );


            $data['epl_general_option_fields'] = $this->epl_util->render_fields( $_field_args );
            $data['settings_updated'] = '';


            $_field_args = array(
                'section' => $this->fields['epl_registration_options'],
                'fields_to_display' => array_keys( $this->fields['epl_registration_options'] ),
                'meta' => array( '_view' => 3, 'value' => $v['epl_registration_options'] )
            );

            $data['epl_registration_options'] = $this->epl_util->render_fields( $_field_args );

            $_field_args = array(
                'section' => $this->fields['epl_event_options'],
                'fields_to_display' => array_keys( $this->fields['epl_event_options'] ),
                'meta' => array( '_view' => 3, 'value' => $v['epl_event_options'] )
            );

            $data['epl_event_options'] = $this->epl_util->render_fields( $_field_args );
            if ( apply_filters( 'epl_enable_feature_override', false ) )
                $data['tabs']['feature-override'] = epl__( 'Override' );

            if ( epl_is_addon_active( 'ETDFGWETSDFGR' ) ) {

                $data['tabs']['shopping-cart'] = epl__( 'Event Cart' );

                epl_sort_array_by_array( $this->fields['epl_sc_options']['epl_sc_primary_regis_forms']['options'], $v['epl_sc_options']['epl_sc_primary_regis_forms'] );
                epl_sort_array_by_array( $this->fields['epl_sc_options']['epl_sc_addit_regis_forms']['options'], $v['epl_sc_options']['epl_sc_addit_regis_forms'] );
                epl_sort_array_by_array( $this->fields['epl_sc_options']['epl_sc_payment_choices']['options'], $v['epl_sc_options']['epl_sc_payment_choices'] );
                $_field_args = array(
                    'section' => $this->fields['epl_sc_options'],
                    'fields_to_display' => array_keys( $this->fields['epl_sc_options'] ),
                    'meta' => array( '_view' => 3, 'value' => $v['epl_sc_options'] )
                );
                $data['epl_sc_options'] = $this->epl_util->render_fields( $_field_args );
            }

            unset( $this->fields['epl_fullcalendar_options']['epl_fullcalendar_tax_bcg_color'] );
            unset( $this->fields['epl_fullcalendar_options']['epl_fullcalendar_tax_font_color'] );


            $terms = epl_object_to_array( get_terms( 'epl_event_categories', array( 'hide_empty' => false ) ) );

            if ( !epl_is_empty_array( $terms ) ) {

                $vals = epl_get_element( 'epl_fullcalendar_options', $v, array() );

                foreach ( $terms as $k => $_v ) {

                    $_bcg_val = epl_get_element( $_v['slug'], epl_get_element( 'epl_fullcalendar_tax_bcg_color', $vals ), '#ffffff' );
                    $_font_val = epl_get_element( $_v['slug'], epl_get_element( 'epl_fullcalendar_tax_font_color', $vals ), 'blue' );

                    $_d = array(
                        'input_type' => 'text',
                        'label' => $_v['name'],
                        'class' => 'epl_w80'
                    );

                    $f = $_d + array(
                        'input_name' => 'epl_fullcalendar_tax_bcg_color[' . $_v['slug'] . ']',
                        'value' => $_bcg_val,
                        'style' => 'background-color:' . $_bcg_val
                    );

                    $data['_tax_color'][$_v['slug']] = $this->epl_util->create_element( $f, 0 );

                    $f = $_d + array(
                        'input_name' => 'epl_fullcalendar_tax_font_color[' . $_v['slug'] . ']',
                        'value' => $_font_val,
                        'style' => 'background-color:' . $_font_val
                    );


                    $data['_font_color'][$_v['slug']] = $this->epl_util->create_element( $f, 0 );
                }
            }


            $_field_args = array(
                'section' => $this->fields['epl_fullcalendar_options'],
                'fields_to_display' => array_keys( $this->fields['epl_fullcalendar_options'] ),
                'meta' => array( '_view' => 3, 'value' => $v['epl_fullcalendar_options'] )
            );

            $data['epl_fullcalendar_options'] = $this->epl_util->render_fields( $_field_args );



            if ( epl_check_for_it() ) {


                $_field_args = array(
                    'section' => $this->fields['epl_api_option_fields'],
                    'fields_to_display' => array_keys( $this->fields['epl_api_option_fields'] ),
                    'meta' => array( '_view' => 3, 'value' => $v['epl_api_option_fields'] )
                );

                $data['epl_api_options'] = $this->epl_util->render_fields( $_field_args );
            }
            $this->epl->load_view( 'admin/settings/settings-page', $data );
        }


        function set_options() {

            if ( function_exists( 'wp_enqueue_media' ) )
                wp_enqueue_media(); //in WP 3.5

            if ( !empty( $_POST ) && check_admin_referer( 'epl_form_nonce', '_epl_nonce' ) ) {

                if ( !empty( $_POST['feature-override'] ) ) {

                    update_option( '_epl_override', $_POST['_epl_override'] );
                }
                else {

                    foreach ( $this->fields as $section => $epl_fields ) {


                        $epl_settings_fields = array_flip( array_keys( $epl_fields ) );

                        $epl_settings_meta = array_intersect_key( $_POST, $epl_settings_fields ); //We are only interested in the posted fields that pertain to events planner
                        if ( !epl_is_empty_array( $epl_settings_meta ) )
                            update_option( $section, $epl_settings_meta );
                        //update_option( 'epl_date_options', $epl_settings );
                    }
                }
                $this->settings_updated = true;

                if ( function_exists( '__epl_pr_api_key' ) ) {
                    __epl_pr_api_key( true );
                }
            }
        }


        function get_options() {

            epl_get_api_fields(); //refresh

            global $epl_fields;

            $this->fields = $epl_fields;

            foreach ( $this->fields as $section => $epl_fields ) {

                $r[$section] = $this->epl_util->get_epl_options( $section );
            }


            return $r;
        }

    }

}