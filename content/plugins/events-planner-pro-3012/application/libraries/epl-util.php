<?php

//TODO - move some functions into model
class EPL_util {

    private static $instance;
    private $response_params = array();
    private $debug_message = array();


    function __construct() {
        epl_log( 'init', get_class() . " initialized" );


        self::$instance = $this;

        add_action( 'init', array( $this, 'load_components' ) );
    }


    public static function get_instance() {
        if ( !self::$instance ) {

            self::$instance = new EPL_util;
        }

        return self::$instance;
    }


    function load_components() {
        if ( !headers_sent() ) {

            if ( !session_id() ) {
                session_start();

                if ( isset( $_GET['destroy_epl_sess'] ) )
                    session_destroy();

                if ( !isset( $_SESSION['__epl'] ) ) {
                    @session_regenerate_id( true );
                    $_SESSION['__epl'] = array();
                }
            }
        }
        $this->epl = EPL_base::get_instance();

        $this->ecm = $this->epl->load_model( 'epl-common-model' );
        $this->erm = $this->epl->load_model( 'epl-registration-model' );
        $this->rm = $this->epl->load_model( 'epl-recurrence-model' );
        $this->erptm = $this->epl->load_model( 'epl-report-model' );
        $this->opt = $this->ecm->get_epl_options();

        $this->override_fields = stripslashes_deep( get_option( '_epl_override' ) );
    }


    /**
     * Creates fields for different areas of Events Planner
     *
     * @since 1.0.0
     * @param int $var
     * @return string
     */
    function render_fields( $args ) {
        global $fields, $event_details;

        extract( $args );

        $defaults = array(
            '_view' => 0,
            '_type' => 'ind',
            '_rows' => 1,
            '_content' => ''
        );

        $meta = wp_parse_args( $meta, $defaults );

        $_table = $section; //$fields[$section];

        if ( empty( $_table ) || empty( $fields_to_display ) )
            return null;

        //make the values of the arrays into keys
        $fields_to_display = array_flip( $fields_to_display );

        //return only the array keys that match our $fields_to_display array
        $fields_to_display = array_intersect_key( ( array ) $_table, $fields_to_display );

        //if we want to see the form in a table row format
        if ( $meta['_type'] == 'row' ) {

            //For fields that are added via a filter for the first time and there is data for an event, there will not be any
            //data for the new fields.  We grab the keys of the master field (usually the first one), so that we can assign
            //the keys to the new field.  Otherwise, if there are already rows of data, only one row will be returned
            $master_keys = '';
            $_master_key = '';
            $r = array();
            //The number of rows to display.  This is determined by how many rows of data there are
            for ( $i = 0; $i < $meta['_rows']; $i++ ) {

                $_r = '';
                $_row_key = '';
                if ( $meta['_view'] == 'raw' )
                    $_r = array();


                $tmp_key = $this->make_unique_id( 6 );

                //cycle through the fields that need to be displayed (from the config array)
                foreach ( $fields_to_display as $field_name => $field_attr ) {
                    $field_attr['input_slug'] = $field_name;
                    //$field_attr['key'] = '';
                    //if there is a value associated, meaning it is being edited
                    if ( isset( $meta['value'] ) ) {
                        //prepare the value to be passed to the next function

                        if ( isset( $field_attr['parent_keys'] ) )
                            $master_keys = (isset( $meta['value'][$field_name] )) ? $meta['value'][$field_name] : '';

                        $field_attr['value'] = (isset( $meta['value'][$field_name] )) ? $meta['value'][$field_name] : '';
                        $k = '';

                        //if the value is an array (from dynamically created fields)
                        if ( isset( $meta['value'][$field_name] ) && is_array( $meta['value'][$field_name] ) ) {

                            $k = array_keys( $meta['value'][$field_name] ); //will be used for checking dinamically added row data

                            $field_attr['value'] = $meta['value'][$field_name];


                            //$field_attr['key'] = epl_get_element( $i, $k ); //the selected row, for select, radio, checkbox
                            $field_attr['key'] = $_row_key == '' ? epl_get_element( $i, $k ) : $_row_key; //the selected row, for select, radio, checkbox
                        }
                        elseif ( $master_keys != '' ) {
                            //this will be used for newly added fields that will be stores as rows, like dates, times....
                            $k = array_keys( ( array ) $master_keys );

                            $field_attr['value'] = $this->remove_array_vals( $master_keys );

                            //$field_attr['key'] = $k[$i];
                            $field_attr['key'] = $_row_key == '' ? epl_get_element( $i, $k ) : $_row_key;
                        }

                        if ( $_row_key == '' ) {
                            if ( isset( $k[$i] ) )
                                $_row_key = $k[$i];
                            elseif ( isset( $tmp_key ) )
                                $_row_key = $tmp_key;
                        }
                    }

                    if ( isset( $field_attr['parent_keys'] ) && isset( $field_attr['key'] ) )
                        $_master_key = $field_attr['key'];



                    $field_attr['tmp_key'] = $tmp_key;


                    /* ---------------------------------------
                     * SPECIAL CIRCUMSTANCE.
                     * @TODO - make a config
                     * ---------------------------------------- */

                    if ( $field_name == '_epl_date_specific_time' ) {

                        if ( isset( $meta['value'][$field_name] ) && !epl_is_empty_array( $meta['value'][$field_name] ) && !epl_is_empty_array( epl_get_element( $_master_key, $meta['value'][$field_name] ) ) ) {
                            $cnt = count( $meta['value'][$field_name][$_master_key] );
                            $_r[$field_name]['field'] = '';

                            foreach ( $meta['value'][$field_name][$_master_key] as $__k => $__v ) {

                                $epl_fields = array(
                                    'input_type' => 'text',
                                    'input_name' => "_epl_date_specific_time[{$_master_key}][$__k]",
                                    'value' => epl_admin_date_display( epl_get_element( $__k, $event_details['_epl_start_date'], $__v ) )
                                );

                                $epl_fields = wp_parse_args( $epl_fields, $field_attr );

                                unset( $epl_fields['key'] );
                                $__r = $this->create_element( $epl_fields, $meta['_view'] );

                                $_r[$field_name]['field'] .= $__r['field'];
                            }
                        }
                    }
                    elseif ( $field_name == '_epl_date_specific_price' ) {

                        if ( isset( $meta['value'][$field_name] ) && !epl_is_empty_array( $meta['value'][$field_name] ) && !epl_is_empty_array( epl_get_element( $_master_key, $meta['value'][$field_name] ) ) ) {
                            $cnt = count( $meta['value'][$field_name][$_master_key] );
                            $_r[$field_name]['field'] = '';

                            foreach ( $meta['value'][$field_name][$_master_key] as $__k => $__v ) {

                                $epl_fields = array(
                                    'input_type' => 'text',
                                    'input_name' => "_epl_date_specific_price[{$_master_key}][$__k]",
                                    'value' => epl_admin_date_display( epl_get_element( $__k, $event_details['_epl_start_date'], $__v ) )
                                );

                                $epl_fields = wp_parse_args( $epl_fields, $field_attr );

                                unset( $epl_fields['key'] );
                                $__r = $this->create_element( $epl_fields, $meta['_view'] );

                                $_r[$field_name]['field'] .= $__r['field'];
                            }
                            //echo "<pre class='prettyprint'>" . __LINE__ . "> " . print_r($__r, true). "</pre>";
                        }
                    }
                    elseif ( $field_name == '_epl_time_specific_price' ) {

                        if ( isset( $meta['value'][$field_name] ) && !epl_is_empty_array( $meta['value'][$field_name] ) && !epl_is_empty_array( epl_get_element( $_master_key, $meta['value'][$field_name] ) ) ) {
                            $cnt = count( $meta['value'][$field_name][$_master_key] );
                            $_r[$field_name]['field'] = '';

                            foreach ( $meta['value'][$field_name][$_master_key] as $__k => $__v ) {

                                $epl_fields = array(
                                    'input_type' => 'text',
                                    'input_name' => "_epl_time_specific_price[{$_master_key}][$__k]",
                                    'value' => epl_get_element( $__k, $event_details['_epl_start_time'], $__v )
                                );

                                $epl_fields = wp_parse_args( $epl_fields, $field_attr );

                                unset( $epl_fields['key'] );
                                $__r = $this->create_element( $epl_fields, $meta['_view'] );

                                $_r[$field_name]['field'] .= $__r['field'];
                            }
                            //echo "<pre class='prettyprint'>" . __LINE__ . "> " . print_r($__r, true). "</pre>";
                        }
                    }
                    else {

                        if ( $meta['_view'] == 'raw' )
                            $_r[$field_name] = $this->create_element( $field_attr, $meta['_view'] );
                        else
                            $_r .= $this->create_element( $field_attr, $meta['_view'] );
                    }
                }

                if ( isset( $field_attr['key'] ) && $field_attr['key'] == '' && $_row_key == '' )
                    $_row_key = $field_attr['tmp_key'];
                //else
                //  $_row_key = isset( $field_attr['key'] ) ? $field_attr['key'] : '';

                $r[$_row_key] = $_r;

                $_row_key = '';
            }
        }
        else {

            foreach ( $fields_to_display as $key => $field ) {
                $field['input_slug'] = $key;
                if ( isset( $meta['value'] ) )
                    $field['value'] = (isset( $meta['value'][$key] )) ? $meta['value'][$key] : '';
                if ( isset( $meta['overview'] ) )
                    $field['overview'] = $meta['overview'];

                $field['content'] = epl_get_element( '_content', $meta, epl_get_element( 'content', $field ) );
                $_r[$key] = $this->create_element( $field, $meta['_view'] );
            }

            $r = $_r;
        }


        return $r;
    }


    /**
     * Create a form field
     *
     * @param array $args (input_type,name, value, id, options, opt_key,opt_value, multiple, label, description, class, wrapper, size,readonly, multiple, size, $style)
     *
     * @return form field
     */
    function create_element( $args = array(), $response_view = 0 ) {

        if ( empty( $args ) )
            return null;

        $response_veiws = array( '', 'common/form-table-row', 'common/form-table-cell' ); //views used for returning the fields

        $defaults = array(
            'input_type' => '',
            'input_name' => '',
            'input_slug' => '',
            'return' => 1,
            'name' => '',
            'key' => '',
            'second_key' => '',
            'auto_key' => false,
            'value' => null,
            'placeholder' => '',
            'default_value' => null,
            'default_checked' => false,
            'force_uncheck' => false,
            'id' => '',
            'options' => '',
            'empty_options_msg' => '',
            'empty_row' => false,
            'opt_key' => '',
            'opt_value' => '',
            'label' => '',
            'description' => '',
            'help_text' => '',
            'help_icon_type' => '',
            'class' => '',
            'rel' => '',
            'wrapper' => '',
            'size' => '',
            'readonly' => false,
            'required' => 0,
            'validation' => '',
            'multiple' => false,
            'style' => '',
            'content' => '',
            'display_inline' => false,
            'overview' => 0,
            'tmp_key' => '',
            'data_type' => '',
            '__func' => '',
            'weight' => 0,
            'show_value_only' => false,
            'data_id' => null,
            'data_attr' => array()
                //'response_view' => $response_view,
        );


        $args = wp_parse_args( $args, $defaults );

        if ( EPL_IS_ADMIN && epl_get_element_m( 'active', $args['input_slug'], $this->override_fields, 10 ) != 10 )
            return null;

        $args['label'] = epl_get_element_m( 'label', $args['input_slug'], $this->override_fields, $args['label'] );
        $args['help_text'] = epl_get_element_m( 'help_text', $args['input_slug'], $this->override_fields, $args['help_text'] );

        extract( $args );

        if ( $return == 0 )
            return null;

        $value = stripslashes_deep( ($value == '' && !is_null( $default_value )) ? $default_value : $value  );

        //all the values come in as strings.  For cbox, radio and selects,
        //the options loop key is numeric.  I am doing a cast comparison so
        //string 0 != int 0, lots of issues.
        //NEW issue:  phone number is converted to int if it comes in 5557778888
        $value = (is_numeric( $value ) && $value < 1000 && ($data_type == 'int' || $data_type != 'float')) ? ( int ) $value : $value;


        $data = array(
            'label' => '',
            'description' => '',
            'response_view' => $response_view,
            'input_type' => $input_type,
            'overview' => '',
            'weight' => $weight
        );

        $name = ($input_name != '') ? $input_name : $name;

        //Doing this for the very first key of the a new record.
        //Since we want to keep track of keys for registration purposes,
        //leaving [] will make the key assignment automatic, creating problems when deleting or adding.moving records.
        $name = (($input_type == 'text' || $input_type == 'hidden' || $input_type == 'select' || $input_type == 'textarea' ) && $key === '') ? str_replace( "[]", "[{$tmp_key}]", $name ) : $name;

        //if a text field has already been saved with a key, assign the key to the name
        if ( $key !== '' && ($input_type == 'text' || $input_type == 'hidden' || $input_type == 'select' || $input_type == 'textarea' ) ) {
            $name = str_replace( "[", "[" . $key, $name );
            $value = html_entity_decode( epl_get_element( $key, $value ) );
        }

        // TODO -  why stripslashes here and above?
        if ( !is_numeric( $value ) )
            $value = stripslashes_deep( $value );

        //echo "<pre class='prettyprint'>" . __LINE__ . '> ' . print_r($value, true). "</pre>";
        if ( $readonly != false && !$overview ) {
            $readonly = 'readonly="readonly"';

            if ( $input_type == 'checkbox' ) {
                //$readonly .= ' disabled="disabled"';
                $style .= "visibility:hidden;";
            }
            if ( $input_type == 'radio' ) {

                $readonly = ' disabled="disabled"';
                $style .= "visibility:hidden;";
            }
        }
        if ( $size != '' )
            $size = "size='$size'";

        if ( $multiple != false ) {
            $multiple = 'multiple="multiple"';

            $size = ($size != '' ? "size='$size'" : "size='5'"); //default size needed for wordpress
            $style .= " height:auto !important;"; //override wp height
        }

        if ( $required != 0 ) {
            $required = '<em>*</em>';
            $class .= ' required';
        }
        else
            $required = '';

        if ( $validation != '' ) {
            $class .= ' ' . $validation;
        }

        if ( $input_type == 'datepicker' ) {
            $class .= ' datepicker';
        }

        $data_attr = $this->make_data_attr( $data_attr );
        //if ( $help_text != '' && $label != $help_text )
        //  $help_text = "class='help_tooltip_trigger' original-title='" . $help_text . "'";


        if ( $label != '' || $response_view == 2 ) {
            $data['label_text'] = stripslashes_deep( $label );
            $data['label'] = "<label for='$id'>" . $data['label_text'] . "{$required}</label>";
        }

        if ( $description != '' || $response_view == 2 )
            $data['description'] = "<span class='description'>$description</span>";

        if ( $help_text != '' )
            $data['label'] .= "<img src='" . EPL_FULL_URL . "images/information-balloon{$help_icon_type}.png' class='help_tooltip_trigger' original-title='" . $help_text . "'>";

        $class = trim( $class );

        $data_id = epl_wrap( $data_id, " data-id='", "'" );

        $data['field'] = '';
        switch ( $input_type )
        {
            case 'section':
                $data['response_view'] = 8;

                $data['field'] = "<div id=\"{$id}\"  class=\"{$class}\" style=\"font-size:16px;\">{$content}</div> \n";
                break;
            case 'datepicker':
                $input_type = 'text';
                EPL_Init::get_instance()->load_datepicker_files();
            case 'text':
                $data['overview'] = $value;
                $value = esc_attr( $value );
            case 'hidden':
            case 'password':
            case 'submit':

                if ( $__func != '' ) {
                    if ( function_exists( $__func ) )
                        $value = $__func( $value );
                }

                $data['field'] = "<input type=\"$input_type\" id=\"{$id}\" name=\"{$name}\" class=\"$class\" rel=\"$rel\" placeholder=\"{$placeholder}\" style=\"{$style}\" {$size} value=\"$value\" $readonly $data_id $data_attr /> \n";
                $data['value'] = $value;

                break;
            case 'textarea':
                if ( $__func != '' ) {
                    if ( function_exists( $__func ) )
                        $value = $__func( $value );
                }
                $data['overview'] = $value;
                $value = esc_attr( $value );
                $data['field'] = "<textarea cols = '60' rows='3' id='{$id}' name='{$name}'  class='{$class}'  style='{$style}' $data_attr>$value</textarea> \n";
                $data['value'] = $value;
                break;
            /* separated out cb and radio, easier to manage */
            case 'table_checkbox':

                $data['response_view'] = 'table_checkbox';
            case 'checkbox':

                if ( $default_checked == 1 )
                    $checked = ' checked = "checked"';

                $display_inline = $display_inline ? '' : '<br />';
                $data['table_checkbox'] = array();
                if ( is_array( $options ) && !empty( $options ) ) {

                    foreach ( $options as $k => $v ) {
                        $v = stripslashes( html_entity_decode( $v ) );
                        if ( isset( $this->override_fields[$args['input_slug']] ) )
                            $v = epl_get_element_m( $k, 'options', $this->override_fields[$args['input_slug']], $v );
                        $checked = '';
                        if ( $default_checked == 1 && ($value === '' || is_null( $value )) ) {

                            $checked = 'checked = "checked"';
                        }
                        elseif (
                                $k === $value || (is_array( $value ) && in_array( strval( $k ), $value, true )) || (epl_is_multi_array( $value ) && in_array( strval( $k ), epl_get_element( $key, $value, array() ), true ) )
                        ) {

                            $checked = 'checked = "checked"';
                        }

                        if ( $force_uncheck )
                            $checked = '';

                        if ( $checked != '' )
                            $data['overview'] .= " $v  \n" . $display_inline;

                        $_name = $name;
                        if ( !$auto_key && strpos( $name, '[' ) !== false )
                            $_name = str_replace( "[", "[" . (($key != '') ? $key : $tmp_key), $name );

                        if ( $second_key != '' )
                            $second_key = "[$k]";

                        $data['field'] .= "<input type=\"checkbox\" id=\"{$id}\" name=\"{$_name}{$second_key}\"  class=\"$class\"  style='{$style}' value=\"$k\" $checked $readonly $data_id $data_attr /> $v \n" . $display_inline;
                        $data['table_checkbox'][$k]['f'] = "<input type=\"checkbox\" id=\"{$id}\" name=\"{$_name}{$second_key}\"  class=\"$class\"  style='{$style}' value=\"$k\" $checked $readonly $data_id $data_attr/> \n";
                        $data['table_checkbox'][$k]['l'] = "$v \n";
                    }
                }
                else {
                    $checked = (isset( $k ) && $value == '' && (in_array( $k, ( array ) $value, true ))) ? 'checked = "checked"' : '';
                    $data['field'] .= "<input type=\"checkbox\" id=\"{$name}\" name=\"{$name}\"  class=\"$class\"  style='{$style}' value=\"1\" $checked $data_id $data_attr /> \n" . $display_inline;
                    $data['overview'] .= " $value  \n" . $display_inline;
                }

                break;
            case 'radio':

                $display_inline = $display_inline ? '' : '<br />';
                if ( is_array( $options ) && !empty( $options ) ) {

                    foreach ( $options as $k => $v ) {
                        $v = stripslashes( html_entity_decode( $v ) );
                        if ( isset( $this->override_fields[$args['input_slug']] ) )
                            $v = epl_get_element_m( $k, 'options', $this->override_fields[$args['input_slug']], $v );
                        $checked = '';

                        if ( ($default_checked == 1 && ($value === '' || is_null( $value ) ) ) ) {

                            $checked = 'checked = "checked"';
                        }
                        elseif ( (is_array( $value ) && in_array( ( string ) $k, $value, true )) || (!is_array( $value ) && (( string ) $value === ( string ) $k)) ) {

                            $checked = 'checked = "checked"';
                        }


                        if ( $checked != '' )
                            $data['overview'] = " $v  \n" . $display_inline;

                        $data['field'] .= "<input type=\"{$input_type}\" id=\"{$id}\" name=\"{$name}\"  class=\"$class\"  style='{$style}' value=\"$k\" $checked $readonly $data_id $data_attr /> $v  \n" . $display_inline;
                    }
                }
                else {
                    $checked = ((in_array( $k, ( array ) $value, true ))) ? 'checked = "checked"' : '';
                    $data['field'] .= "<input type=\"{$input_type}\" id=\"{$name}\" name=\"{$name}\"  class=\"$class\"  style='{$style}' value=\"1\" $checked $data_id $data_attr /> \n" . $display_inline;
                    $data['overview'] .= " $v  \n" . $display_inline;
                }

                break;
            case 'radio-switch':
                //will investigate using this in dynamic table fields.
                //$tmp_key = $this->make_unique_id(7);

                $display_inline = ''; //$display_inline ? '' : '<br />';
                if ( is_array( $options ) && !empty( $options ) ) {
                    $counter = 1;
                    foreach ( $options as $k => $v ) {

                        $v = stripslashes( html_entity_decode( $v ) );
                        if ( isset( $this->override_fields[$args['input_slug']] ) )
                            $v = epl_get_element_m( $k, 'options', $this->override_fields[$args['input_slug']], $v );
                        $checked = '';

                        if ( ($default_checked == 1 && ($value === '' || is_null( $value ) ) ) ) {

                            $checked = 'checked = "checked"';
                        }
                        elseif ( ( string ) $k === ( string ) $value || (is_array( $value ) && in_array( ( string ) $k, $value, true )) ) {

                            $checked = 'checked = "checked"';
                        }


                        if ( $checked != '' )
                            $data['overview'] = " $v  \n" . $display_inline;

                        $id = $k . str_replace( array( '[', ']' ), '', $name ) . $tmp_key; //using tmp key for dynamic rows.

                        $data['field'] .= "<input type=\"radio\" id=\"{$id}\" name=\"{$name}\" value=\"$k\" $checked $readonly $data_id $data_attr /><label for=\"$id\" onclick=\"\">$v</label>";
                    }
                }

                $data['field'] = '<div class="switch-toggle candy yellow">' . $data['field'] . '<a></a></div>';

                break;
            case 'select':

                if ( !epl_is_empty_array( $options ) ) {

                    $select = "<select name = '{$name}' id = '{$id}' class='$class' style='$style' $multiple $size $readonly $data_id $data_attr>";

                    if ( $empty_row )
                        $select .= "<option></option>  \n";

                    foreach ( ( array ) $options as $k => $v ) {
                        $v = stripslashes( html_entity_decode( $v ) );
                        if ( isset( $this->override_fields[$args['input_slug']] ) )
                            $v = epl_get_element_m( $k, 'options', $this->override_fields[$args['input_slug']], $v );

                        $selected = ((is_array( $value ) && in_array( ( string ) $k, $value )) || (!is_array( $value ) && (( string ) $value === ( string ) $k))) ? "selected = 'selected'" : '';

                        if ( $show_value_only && $selected == '' )
                            continue;

                        $select .= "<option value='{$k}' $selected>{$v}</option>";

                        if ( $selected != '' )
                            $data['overview'] = " $v  \n";
                    }

                    $data['field'] = $select . "</select>";
                } else {

                    $data['field'] = $empty_options_msg;
                }
                $data['value'] = $v;
                break;
        }


        if ( $overview ) {
            //echo "<br />OVERVIEW2 " . $data['overview'] . "<br >";
            $data['field'] = "<div class='overview_value' style='{$style}'>" . $data['overview'] . '</div>';
            unset( $data['overview'] );
        }


        if ( $data['response_view'] === 0 || $data['response_view'] == 'raw' ) {

            return $data;
        }

        $r = $this->epl->load_view( 'common/form-field-view', $data, true );

        return $r;
    }


    function make_data_attr( $data ) {

        $r = '';
        if ( !empty( $data ) ) {
            foreach ( $data as $k => $v )
                $r .= " data-{$k}='{$v}'";
        }
        return $r;
    }


    /**
     * Get all the meta information associated with a post
     *
     * @since 1.0.0
     * @param int $post_id
     * @return $array - meta_key => $meta_value
     */
    function send_repsonse( $r ) {
        if ( !$GLOBALS['epl_ajax'] ) {
            echo $r;
            return;
        }
        echo $this->epl_util->epl_response( array( 'html' => $r ) );
        die();
    }


    function set_debug_message( $param, $value ) {
        static $counter = 0;

        $value = (is_bool( $value ) ? var_export( $value, true ) : print_r( $value, true ));

        $this->debug_message[$param . $counter] = $value;
        $counter++;
    }


    function set_response_param( $param, $value ) {

        $this->response_params[$param] = $value;
    }


    function epl_response( $params ) {
        $defaults = array(
            'is_error' => 0,
            'error_text' => '',
            'html' => ''
        );

        $params += $this->response_params;

        if ( isset( $_REQUEST['epl_ajax'] ) && $_REQUEST['epl_ajax'] == 1 ) {

            $params = wp_parse_args( $params, $defaults );

            $params['debug_message'] = $this->debug_message;

            return json_encode( $params );
        }
        else
            echo $defaults['html'];
    }


    function epl_invoke_error( $error_code = 0, $custom_text = null, $ajax = true, $type = 'error' ) {

        $error_codes = array(
            0 => 'Sorry, something went wrong.  Please try again',
            1 => 'Sorry, something went wrong.  Please try again',
            20 => 'Your cart is empty',
            21 => 'Please select a date.'
        );

        $error_text = '123' . "<div class='epl_{$type}'>" . (!is_null( $custom_text )) ? $custom_text : epl__( $error_codes[$error_code] ) . '</div>';

        if ( $ajax )
            return $this->epl_response( array( 'is_error' => 1, 'error_text' => $error_text ) );
        else
            return $error_text;
    }


    function get_key( $arr, $key ) {
        if ( empty( $arr ) || $key == '' )
            return null;

        return key( $arr );
    }


    function sort_array_by_array( $array, $orderArray ) {

        $ordered = array();
        foreach ( ( array ) $orderArray as $key => $value ) {
            if ( array_key_exists( $key, $array ) ) {
                $ordered[$key] = $array[$key];
                //unset( $array[$key] );
            }
        }
        return $ordered; // + $array;
    }


    function is_empty_array( $array ) {

        if ( !array_filter( ( array ) $array, 'trim' ) )
            return true;

        return false;
    }


    function clean_request( $post ) {

        foreach ( $post as $k => $v ) {
            $post[$k] = esc_sql( $v );
        }
    }


    function make_unique_id( $length = 10 ) {



        $max = ceil( $length / 40 );
        $random = '';
        //for ( $i = 0; $i < $max; $i++ ) {
        $random = sha1( microtime( true ) . mt_rand( 10000, 90000 ) );
        //}
        return substr( $random, 0, $length );
    }


    function process_data_type( &$value, $data_type, $mode = 's' ) {

        switch ( $data_type )
        {
            case "date":
                $value = epl_formatted_date( strtotime( $value ), "Y-m-d" );
                break;
            case "unix_time":

                $value = strtotime( epl_admin_dmy_convert( $value ) );
                break;
        }
    }


    function process_mode( $value, $data_type, $mode = 's' ) {
        
    }


    function construct_date_display_table() {

        global $event_details;
        //echo "<pre class='prettyprint'>" . __LINE__ . "> " . print_r($event_details, true). "</pre>";
        //echo "<pre class='prettyprint'>" . __LINE__ . "> " . print_r($meta, true). "</pre>";
        $tmpl = array( 'table_open' => '<table cellpadding="0" cellspacing="0" class="event_dates_table">' );

        $this->epl->epl_table->set_template( $tmpl );
        //$this->epl->epl_table->set_heading( epl__( 'Start Date' ), epl__( 'End Date' ), '' );

        foreach ( $event_details['_epl_start_date'] as $date_key => $date ) {




            if ( $event_details['_epl_event_status'] == 3 || epl_get_date_timestamp( $date ) >= EPL_DATE ) {

                $date = epl_formatted_date( $date );
                $end_date = epl_formatted_date( $event_details['_epl_end_date'][$date_key] );
                $_note = epl_get_element_m( $date_key, '_epl_date_note', $event_details );

                $_location = '';
                if ( epl_get_element( $date_key, epl_get_element( '_epl_date_location', $event_details ) ) ) {
                    $_location_id = epl_get_element( $date_key, $event_details['_epl_date_location'] );
                    $l = the_location_details( $_location_id ); //sets up the location info
                    $_location = get_the_location_name() . ' <br /> ' . get_the_location_address() . ' ' . get_the_location_city() . ' ' . get_the_location_state() . ' ' . get_the_location_zip() . ' ' . get_the_location_phone() . ' ' . get_the_location_gmap_icon( epl__( 'See Map' ) );
                }

                $t_row = array( $date, ' ' . epl__( 'to' ) . ' ', $end_date, $_note, $_location );

                if ( $date == $end_date ) {
                    unset( $t_row[1] );
                    unset( $t_row[2] );
                }


                $this->epl->epl_table->add_row( $t_row );
            }
        }
        $r = $this->epl->epl_table->generate();
        $this->epl->epl_table->clear();
        return $r;
    }

    /*
     * registration template tag processors
     */


    function get_the_regis_event_name() {

        global $regis_details, $event_details;

        return stripslashes_deep( $event_details['post_title'] );
    }


    function get_the_regis_id() {

        global $regis_details, $event_details;

        return stripslashes_deep( $regis_details['post_title'] );
    }


    function get_the_regis_dates_times_prices( $regis_id = null, $raw = false ) {

        global $regis_details, $event_details;

        if ( !is_null( $regis_id ) && epl_get_element( 'ID', $regis_details ) != $regis_id )
            $this->ecm->setup_regis_details( epl_nz( $regis_id, $this->erm->get_regis_post_id() ) );

        $this->erm->setup_current_data( $regis_details );

        $regis_events = $this->erm->get_regis_events();

        foreach ( $regis_events as $event_id => $totals ) {
            setup_event_details( $event_id );

            $regis_date_section = $this->erm->get_regis_dates();

            $regis_dates = epl_get_element_m( $event_details['ID'], '_epl_start_date', $regis_date_section );
            $regis_times = epl_get_element_m( $event_details['ID'], '_epl_start_time', $regis_date_section );
            $regis_tickets = epl_get_element_m( $event_details['ID'], '_att_quantity', $regis_date_section );


            $time_format = get_option( 'time_format' );


            $_r[$event_id] = array();

            foreach ( $event_details['_epl_start_date'] as $date_key => $date ) {
                $total_tickets = 0;
                $dg = epl_get_element_m( $date_key, '_epl_date_group_no', $event_details, '' );

                if ( in_array( $date_key, $regis_dates ) || ($dg != '' && in_array( $date_key, $regis_dates[$dg] )) ) {

                    $_r[$event_id][$date_key]['date'] = array(
                        'disp' => epl_formatted_date( $date )
                    );


                    $_l = '';
                    if ( epl_get_element( $date_key, epl_get_element( '_epl_date_location', $event_details ) ) ) {
                        $_location_id = epl_get_element( $date_key, $event_details['_epl_date_location'] );
                        the_location_details( $_location_id ); //sets up the location info

                        $_r[$event_id][$date_key]['date']['location'] = get_the_location_name() . ' <br /> ' . get_the_location_address() . ' ' . get_the_location_city() . ' ' . get_the_location_state() . ' ' . get_the_location_zip() . ' ' . get_the_location_phone() . ' ' . get_the_location_gmap_icon( epl__( 'See Map' ) );
                    }


                    if ( $date != $event_details['_epl_end_date'][$date_key] )
                        $_r[$event_id][$date_key]['date'] = array( 'disp' => epl_formatted_date( $date ) . ' - ' . epl_formatted_date( $event_details['_epl_end_date'][$date_key] ) . '<br /> ' . $_l );

                    $event_times = epl_get_element( '_epl_start_time', $event_details );
                    $_empty_time = false;
                    if ( epl_is_empty_array( $event_times ) ) {
                        $_empty_time = true;
                    }
                    foreach ( $event_times as $time_key => $times ) {


                        if ( (!epl_is_date_level_time() && ($_empty_time || in_array( $time_key, ( array ) $regis_times ))) || (epl_is_date_level_time() && $regis_times[$date_key] == $time_key) ) {

                            $start_time = date_i18n( $time_format, strtotime( $times ) );
                            $end_time = date_i18n( $time_format, strtotime( $event_details['_epl_end_time'][$time_key] ) );

                            $_r[$event_id][$date_key]['time'][$time_key] = $_empty_time ? '' : array( 'disp' => $start_time . ' - ' . $end_time . epl_prefix( ' - ', epl_get_element_m( $time_key, '_epl_time_note', $event_details ) ) );
                            $total_tickets = 0;
                            foreach ( $event_details['_epl_price_name'] as $price_key => $price_name ) {

                                //-----------------
                                //if ( (!epl_is_date_level_price ( ) && array_key_exists( $price_key, $regis_tickets )) || (epl_is_date_level_price ( ) && $regis_tickets[$price_key][$date_key] == $time_key) ) {

                                if ( array_key_exists( $price_key, $regis_tickets ) ) {
                                    $total_tickets++;
                                    $num_att = 0;
                                    if ( epl_is_date_level_price() && epl_get_element_m( $date_key, $price_key, $regis_tickets ) > 0 )
                                        $num_att = epl_get_element_m( $date_key, $price_key, $regis_tickets );
                                    else
                                        $num_att = (is_array( $regis_tickets[$price_key] )) ? array_sum( $regis_tickets[$price_key] ) : $regis_tickets[$price_key];
                                    if ( $num_att > 0 ) {
                                        $true_price = $totals['money_totals'][$price_key] / $num_att;

                                        $_r[$event_id][$date_key]['time'][$time_key]['price'][$price_key] = array(
                                            'disp' => $price_name,
                                            'qty' => $num_att,
                                            'ticket_price' => $event_details['_epl_price'][$price_key],
                                            'raw_price' => $true_price,
                                            'price' => epl_get_formatted_curr( $true_price, null, true ) );

                                        if ( epl_get_element( '_epl_pricing_type', $event_details ) == 10 && $time_key != $event_details['_epl_price_parent_time_id'][$price_key] ) {
                                            unset( $_r[$event_id][$date_key]['time'][$time_key]['price'][$price_key] );
                                            $total_tickets--;
                                        }
                                        if ( (epl_is_date_level_price() && epl_get_element_m( $date_key, $price_key, $regis_tickets ) == 0 ) ) {
                                            unset( $_r[$event_id][$date_key]['time'][$time_key]['price'][$price_key] );
                                            $total_tickets--;
                                        }

                                        $_r[$event_id][$date_key]['time']['total_tickets'] = $total_tickets;
                                    }
                                }


                                //----------
                            }
                        }
                    }
                }
            }
        }

        if ( $raw )
            return $_r;
        return $this->epl->load_view( 'front/registration/regis-dates-times-prices', array( 'table_data' => $_r ), true );
        return $r;
    }


    function get_the_regis_dates() {

        global $regis_details, $event_details;

        $dates = ( array ) $event_details['_epl_start_date'];
        $regis_dates = $regis_details['_epl_dates']['_epl_start_date'][$event_details['ID']];

        $tmpl = array( 'table_open' => '<table cellpadding="0" cellspacing="0" class="event_dates_table">' );

        $this->epl->epl_table->set_template( $tmpl );
        //$this->epl->epl_table->set_heading( epl__( 'Start Date' ), epl__( 'End Date' ), '' );
        foreach ( $dates as $key => $date ) {

            if ( in_array( $key, $regis_dates ) ) {

                $t_row = array( $date, $meta['_epl_end_date'][$key] );

                if ( $date == $meta['_epl_end_date'][$key] )
                    $t_row = array( $date );


                $this->epl->epl_table->add_row( $t_row );
            }
        }
        $r = $this->epl->epl_table->generate();
        $this->epl->epl_table->clear();
        return $r;
    }


    function get_the_regis_times() {
        //extract( $args );
        global $regis_details, $event_details;

        $time_format = get_option( 'time_format' );

        $regis_times = $regis_details['_epl_dates']['_epl_start_time'][$event_details['ID']];

        $tmpl = array( 'table_open' => '<table cellpadding="0" cellspacing="0" class="event_times_table">' );

        $this->epl->epl_table->set_template( $tmpl );

        foreach ( $event_details['_epl_start_time'] as $time_key => $times ) {

            if ( in_array( $time_key, $regis_times ) ) {

                $start_time = date_i18n( $time_format, strtotime( $times ) );
                $end_time = date_i18n( $time_format, strtotime( $event_details['_epl_end_time'][$time_key] ) );

                $this->epl->epl_table->add_row( $start_time . ' - ' . $end_time );
            }
        }

        $r = $this->epl->epl_table->generate();
        $this->epl->epl_table->clear();
        return $r;
    }


    function get_the_regis_prices() {
        global $event_details, $regis_details;

        if ( $this->is_empty_array( $event_details['_epl_price_name'] ) )
            return;


        $price_fileds = $epl_fields['epl_price_fields'];
        $regis_tickets = $regis_details['_epl_dates']['_att_quantity'][$event_details['ID']];

        foreach ( $event_details['_epl_price_name'] as $price_key => $price_data ) {
            $r = array();
            if ( array_key_exists( $price_key, $regis_tickets ) ) {

                $num_att = (is_array( $regis_tickets[$price_key] )) ? current( $regis_tickets[$price_key] ) : $regis_tickets[$price_key];
                if ( $num_att > 0 )
                    $this->epl->epl_table->add_row( $event_details['_epl_price_name'][$price_key] . ' - ' . $num_att );
            }
        }

        $r = $this->epl->epl_table->generate();
        $this->epl->epl_table->clear();
        return $r;
    }


    function get_the_regis_status( $status = null, $id_only = false ) {

        global $regis_details, $epl_fields;
        $this->epl->load_config( 'regis-fields' );

        if ( is_null( $status ) && epl_is_empty_array( $regis_details ) && (epl_get_element( 'epl_rid', $_REQUEST ) || epl_get_element( 'regis_id', $_REQUEST )) ) {

            $_rid = epl_get_element( 'epl_rid', $_REQUEST ) ? epl_get_element( 'epl_rid', $_REQUEST ) : epl_get_element( 'regis_id', $_REQUEST );
            $this->ecm->setup_regis_details( intval( $_rid ) );
        }

        $_status = (!is_null( $status )) ? $status : epl_get_element( '_epl_regis_status', $regis_details );

        if ( $id_only )
            return $_status;

        return $epl_fields['epl_regis_payment_fields']['_epl_regis_status']['options'][$_status];
    }


    function get_the_regis_total_amount( $symbol = true ) {

        global $regis_details, $event_details, $epl_current_step;

        if ( !empty( $regis_details ) && isset( $regis_details['__epl'] ) && !$_SERVER['REQUEST_METHOD'] == $_POST ) {
            $regis_id = $regis_details['__epl']['_regis_id'];
            $source = $regis_details['__epl'][$regis_id];
            if ( epl_get_element_m( 'money_totals', 'cart_totals', $source, '' ) == '' )
                $source = $regis_details;
        }
        else {

            $regis_id = EPL_registration_model::get_instance()->regis_id;
            $source = EPL_registration_model::get_instance()->current_data[$regis_id];
        }

        $grand_total = epl_get_element_m( 'money_totals', 'cart_totals', $source, '' );

        $grand_total = epl_nz( $grand_total !== '' ? $grand_total['grand_total'] : $source['_epl_grand_total']  );

        if ( !$symbol )
            return $grand_total;
        return ( epl_get_formatted_curr( $grand_total, null, true ) );
    }


    function get_the_regis_original_amount() {

        global $regis_details, $event_details;

        return ( epl_get_formatted_curr( epl_nz( $regis_details['_epl_grand_total'] ), null, true ) );
    }


    function get_the_regis_payment_amount() {

        global $regis_details, $event_details;

        return ( epl_get_formatted_curr( epl_nz( $regis_details['_epl_payment_amount'], 0 ), null, true ) );
    }


    function get_the_regis_pre_discount_amount() {

        global $regis_details, $event_details;

        return ( epl_get_formatted_curr( epl_nz( $regis_details['_epl_payment_amount'] ), null, true ) );
    }


    function get_the_regis_discount_amount() {

        global $regis_details, $event_details;

        return ( epl_get_formatted_curr( epl_nz( $regis_details['_epl_payment_amount'] ), null, true ) );
    }


    function get_the_regis_balance_due() {

        global $regis_details, $event_details;
        $payment_data = epl_get_regis_payments();

        $grand_total = $this->get_the_regis_total_amount( false );

        $total_paid = 0;
        foreach ( $payment_data as $time => $p )
            $total_paid += $p['_epl_payment_amount'];


        return epl_nz( $grand_total - $total_paid );
    }


    function get_the_regis_payment_date() {

        global $regis_details, $event_details;

        if ( $regis_details['_epl_payment_date'] != '' )
            return date_i18n( get_option( 'date_format' ), strtotime( epl_dmy_convert( $regis_details['_epl_payment_date'] ) ) );

        return '';
    }


    function get_the_regis_transaction_id() {

        global $regis_details, $event_details;

        return $regis_details['_epl_transaction_id'];
    }

    /*
     * end registration template tag processors
     */


    function construct_calendar( $dates = array(), $style = 'epl_course_cal' ) {


        if ( empty( $dates ) )
            return;

        $c = '';
        $prefs['template'] = '
    {table_open}<div class="epl_calendar_wrapper"><table id="" class="' . $style . '">{/table_open}

   {heading_row_start}<tr scope="col">{/heading_row_start}
    {caption_open}<caption>{/caption_open}
    {caption_close}</caption>{/caption_close}
   {heading_previous_cell}<th><a href="{previous_url}" class="epl_next_prev_link">&lt;&lt;</a></th>{/heading_previous_cell}
   {heading_title_cell}<th colspan="{colspan}">{heading}</th>{/heading_title_cell}
   {heading_next_cell}<th><a href="{next_url}"  class="epl_next_prev_link">&gt;&gt;</a></th>{/heading_next_cell}

   {heading_row_end}</tr>{/heading_row_end}

    {week_day_cell}<th class="day_header">{week_day}</th>{/week_day_cell}
    {cal_cell_start_today}<td id ="today">{/cal_cell_start_today}
    {cal_cell_content}<div class="widget_has_data day_listing day_listing_content" id="epl_{content}">{day}</div>{/cal_cell_content}
    {cal_cell_content_today}<div class="today widget_has_data day_listing" id="epl_{content}">{day}</div>{/cal_cell_content_today}

    {cal_cell_no_content}<div class= "day_listing ">{day}</div>{/cal_cell_no_content}
    {cal_cell_no_content_today}<div class="">{day}</div>{/cal_cell_no_content_today}
    {table_close}</table></div>{/table_close}
';


        $this->epl->epl_calendar->initialize( $prefs );

        $this->epl->epl_calendar->show_next_prev = false;

        foreach ( $dates as $year => $month ) {

            foreach ( $month as $_month => $_days ) {
                $c .= $this->epl->epl_calendar->generate( $year, $_month, $_days );
            }
        }

        return $c;
    }


    function alter_array( $arr, $k, $new_val ) {
        
    }


    function get_epl_options( $section ) {

        //foreach ($this->fields as $section=>$fields){

        return get_option( maybe_unserialize( $section ) );

        //}
        return $r;
    }


    function calculate_grand_total( $args = array() ) {
        
    }


    function view_cart_link() {

        return "<a href = '?epl_action=show_cart'>View Cart</a>";
    }


    function get_time_display( $args ) {
        //extract( $args );
        global $event_details;
        if ( $this->is_empty_array( $event_details['_epl_start_time'] ) )
            return;

        $time_format = get_option( 'time_format' );

        $tmpl = array( 'table_open' => '<table cellpadding="0" cellspacing="0" class="event_times_table">' );

        $this->epl->epl_table->set_template( $tmpl );
        //$this->epl->epl_table->set_heading( epl__( 'Start Time' ), epl__( 'End Time' ), '' );
        foreach ( $event_details['_epl_start_time'] as $time_key => $times ) {

            $start_time = date_i18n( $time_format, strtotime( $times ) );
            $end_time = date_i18n( $time_format, strtotime( $event_details['_epl_end_time'][$time_key] ) );

            $this->epl->epl_table->add_row( $start_time . ' - ' . $end_time );
        }

        $r = $this->epl->epl_table->generate();
        $this->epl->epl_table->clear();
        return $r;
    }


    function get_prices_display() {
        global $event_details, $epl_fields;
        if ( $this->is_empty_array( $event_details['_epl_price_name'] ) )
            return;

        //echo "<pre class='prettyprint'>" . print_r( $event_details['_epl_price_name'], true ) . "</pre>";

        $this->epl->load_config( 'event-fields' );


        $price_fileds = $epl_fields['epl_price_fields'];

        foreach ( $event_details['_epl_price_name'] as $price_key => $price_data ) {
            $r = array();
            foreach ( $price_fileds as $field_name => $field_values ) {

                if ( array_key_exists( $field_name, $event_details ) ) {

                    $r[] = $event_details[$field_name][$price_key];
                }
            }

            $this->epl->epl_table->add_row( $r );
        }

        $r = $this->epl->epl_table->generate();
        $this->epl->epl_table->clear();
        return $r;
    }

    /* not used ??? */


    function list_events( $param = array() ) {

        $args = array(
            'post_type' => 'epl_event',
            'meta_query' => array(
                array(
                    'key' => '_q_epl_regis_start_date',
                    'value' => array( strtotime( '2011-09-11 1pm' ), strtotime( '2011-09-20 23:59:59' ) ),
                    //'type' => 'date',
                    'compare' => 'BETWEEN'
                )
            )
        );
        // The Query

        $the_query = new WP_Query( $args );

        $epl_options = $this->epl_util->get_epl_options( 'events_planner_event_options' );


        ob_start();
        while ( $the_query->have_posts() ) :
            $the_query->the_post();



            $post_mata = $this->ecm->get_post_meta_all( get_the_ID() );
            //echo "<pre class='prettyprint'>POST META" . print_r($post_mata, true). "</pre>";
            //$this->epl_util

            echo "<h1>" . get_the_title() . "</h1>";

            echo $this->epl_util->get_time_display( $post_mata );
            echo $this->epl_util->get_prices_display( $post_mata );

            $epl_options['epl_show_event_description'] != 0 ? the_content() : '';

            echo $this->epl_util->construct_date_display_table( array( 'post_ID' => get_the_ID(), 'meta' => $post_mata ) );
        //echo $this->epl_util->construct_calendar($pm['epl_date_blueprint']);
        endwhile;
        $r = ob_get_contents();
        ob_end_clean();

        //wp_reset_postdata();
        return $r;
    }


    function combine_array_keys( $array = array() ) {
        $_r = array();
        foreach ( $array as $_a )
            $_r += ( array ) $_a;
        return $_r;
    }


    function rekey_fields_array( $fields ) {

        $r = array();
        if ( !empty( $fields ) ) {
            foreach ( $fields as $field_id => $field_data ) {

                $r[$field_data['input_name']] = $field_data;
            }
        }
        return $r;
    }


    function get_field_options( $fields ) {

        if ( !empty( $fields ) ) {
            foreach ( $fields as $field_id => $field_data ) {

                if ( array_key_exists( 'epl_field_choice_value', $field_data ) ) {


                    if ( $this->is_empty_array( ( array ) $field_data['epl_field_choice_value'], 'trim' ) ) {
                        $options = $field_data['epl_field_choice_text'];
                    } //else we will combine the field values and choices into an array for use in the dropdown, or radio or checkbox
                    else {
                        $options = array_combine( $field_data['epl_field_choice_value'], $field_data['epl_field_choice_text'] );
                    }

                    $fields[$field_id]['options'] = $options;
                }
            }
        }
    }


// check the current post for the existence of a short code
    function has_shortcode( $shortcode = '', $post_id = null ) {

        if ( is_null( $post_id ) )
            return false;

        $post_to_check = get_pages( $post_id );

        // false because we have to search through the post content first
        $found = false;

        // if no short code was provided, return false
        if ( !$shortcode ) {
            return $found;
        }
        // check the post content for the short code
        if ( stripos( $post_to_check->post_content, '[' . $shortcode ) !== false ) {
            // we have found the short code
            $found = true;
        }

        // return our final results
        return $found;
    }

    /*
     * Event Template Tag handlers
     */


    function get_the_event_title( $post_ID = null ) {

        global $post;
        if ( is_null( $post->ID ) )
            return null;

        return sprintf( '<a href="%s" title="%s">%s</a>', $_SERVER['REQUEST_URI'] . '?event_id=' . $post->ID . '&epl_action=event_details', get_the_title(), get_the_title() );
    }


    function set_the_event_details( $args = array() ) {

        global $post;
        if ( is_null( $post->ID ) )
            return null;
        $this->ecm = $this->epl->load_model( 'epl-common-model' );


        global $event_details;
        //$event_details = $this->ecm->get_post_meta_all( $post->ID );
        $event_details = $this->ecm->setup_event_details( $post->ID );
    }


    function get_the_event_session_table() {

        global $event_details;

        if ( !$event_details )
            $this->set_the_event_details();

        $session_dates = epl_get_element( '_epl_class_session_date', $event_details );

        if ( (!$session_dates || epl_is_empty_array( $session_dates )) && epl_is_empty_array( $event_details['_epl_class_session_name'] ) )
            return null;


        $tmpl = array( 'table_open' => '<table cellpadding="0" cellspacing="0" class="event_session_table">' );

        $this->epl->epl_table->set_template( $tmpl );
        foreach ( $session_dates as $date_key => $date ) {



            $date = epl_formatted_date( $date );

            $session_name = $event_details['_epl_class_session_name'][$date_key];
            $session_note = $event_details['_epl_class_session_note'][$date_key];



            $t_row = array( $date, $session_name, $session_note );
            if ( $date == '' )
                unset( $t_row[0] );

            $this->epl->epl_table->add_row( $t_row );
        }
        $r = $this->epl->epl_table->generate();
        $this->epl->epl_table->clear();
        return $r;
    }


    function get_the_event_dates( $raw = false ) {

        global $event_details;

        if ( !$event_details )
            $this->set_the_event_details();

        if ( $raw )
            return $event_details['_epl_start_date'];

        return $this->construct_date_display_table();
    }


    function get_the_event_dates_cal() {

        global $event_details;

        $event_type = $event_details['_epl_event_type'];
        $event_rec_frequency = $event_details['_epl_recurrence_frequency'];
        $d = array();
        if ( $event_type == 10 ) {

            if ( $event_rec_frequency != '0' ) {

                $d = $this->rm->recurrence_dates_from_db( $event_details );
            }
            elseif ( !epl_is_empty_array( $event_details['_epl_class_session_date'] ) ) {

                $d = $this->rm->recurrence_dates_from_sessions_section();
            }
        }

        if ( empty( $d ) )
            $d = $this->rm->recurrence_dates_from_dates_section();

        return $this->construct_calendar( $d );
    }


    function get_the_event_times( $post_ID = null ) {

        global $event_details;

        if ( !$event_details )
            $this->set_the_event_details();

        return $this->get_time_display( array( 'post_ID' => $post_ID, 'meta' => $event_details ) );
    }


    function get_the_event_prices() {

        global $event_details;

        if ( !$event_details )
            $this->set_the_event_details();
        $price_opt = true;
        $tmpl = array( 'table_open' => '<table cellpadding="0" cellspacing="0" class="event_prices_table">' );

        $this->epl->epl_table->set_template( $tmpl );
        foreach ( $event_details['_epl_price_name'] as $price_key => $price_data ) {
            //at least one price is visible
            if ( $event_details['_epl_price_hide'][$price_key] == 0 && $event_details['_epl_price'][$price_key] != '' )
                $price_opt = false;

            if ( $event_details['_epl_price_hide'][$price_key] == 10 )
                continue;
            $price_name = $event_details['_epl_price_name'][$price_key];
            $price = (epl_is_free_event() || $event_details['_epl_price'][$price_key] == 0) ? '' : epl_get_formatted_curr( $event_details['_epl_price'][$price_key], null, true );
            $this->epl->epl_table->add_row( $price_name, $price );

            //$this->epl->epl_table->add_row( $r );
        }

        $r = $this->epl->epl_table->generate();
        $this->epl->epl_table->clear();
        return $price_opt ? '' : $r;
    }


    function get_the_event_dates_times_prices() {
        global $event_details, $event_snapshot;
        $_today = EPL_DATE;
        $_r = array();

        foreach ( $event_details['_epl_start_date'] as $date_key => $date ) {
            if ( epl_is_ongoing_event() || true ) {
                $_note = epl_get_element_m( $date_key, '_epl_date_note', $event_details );

                $_location = '';
                if ( epl_get_element( $date_key, epl_get_element( '_epl_date_location', $event_details ) ) ) {
                    $_location_id = epl_get_element( $date_key, $event_details['_epl_date_location'] );
                    $l = the_location_details( $_location_id ); //sets up the location info
                    $_location = $l['post_title']; //get_the_location_name();// . ' ' . get_the_location_gmap_icon();
                    //echo "<pre class='prettyprint'>" . __LINE__ . "> " . print_r( epl_formatted_date( $date ) . $_location_id, true ) . "</pre>";
                }

                $_end = ($date != $event_details['_epl_end_date'][$date_key]) ? ' - ' . epl_formatted_date( $event_details['_epl_end_date'][$date_key] ) : '';
                $_r[$date_key]['date'] = array( 'disp' => epl_formatted_date( $date ) . $_end . ' ' . $_location );

                foreach ( $event_details['_epl_start_time'] as $time_key => $time ) {

                    if ( epl_is_date_level_time() && epl_is_date_specific_time( $time_key ) && !epl_get_element_m( $date_key, $time_key, $event_details['_epl_date_specific_time'] ) ) {
                        continue;
                    }

                    $_r[$date_key]['time'][$time_key] = array( 'disp' => $time . epl_prefix( ' - ', epl_get_element_m( $time_key, '_epl_time_note', $event_details ) ) );

                    foreach ( $event_details['_epl_price_name'] as $price_key => $price_name ) {
                        if ( epl_is_date_level_price() && epl_is_date_specific_price( $price_key ) && !epl_get_element_m( $date_key, $price_key, $event_details['_epl_date_specific_price'] ) )
                            continue;
                        //echo "<pre class='prettyprint'>" . __LINE__ . "> " . print_r($event_details['_epl_price_name'], true). "</pre>";
                        $_r[$date_key]['time'][$time_key]['price'][$price_key] = array( 'disp' => $price_name, 'price' => epl_get_formatted_curr( $event_details['_epl_price'][$price_key], null, true ) );


                        if ( epl_is_time_specific_price( $price_key ) && !epl_get_element_m( $time_key, $price_key, epl_get_element( '_epl_time_specific_price', $event_details ) ) )
                            unset( $_r[$date_key]['time'][$time_key]['price'][$price_key] );
                        if ( epl_is_date_specific_price( $price_key ) && !epl_get_element_m( $date_key, $price_key, epl_get_element( '_epl_date_specific_price', $event_details ) ) )
                            unset( $_r[$date_key]['time'][$time_key]['price'][$price_key] );
                    }
                }
            }
        }
        return $this->epl->load_view( 'front/dates-times-prices', array( 'table_data' => $_r ), true );
    }


    function get_the_register_button( $event_id = null, $url_only = false, $args = array() ) {
        global $post, $event_details;

        $args = apply_filters( 'epl__get_the_register_button_args', $args );

        if ( is_null( $event_id ) && !epl_is_ok_to_show_regis_button() && !$url_only )
            return null;

        $button_text = isset( $args['button_text'] ) ? $args['button_text'] : epl_nz( epl_get_setting( 'epl_event_options', 'epl_register_button_text' ), epl__( 'Register' ) );
        $url_vars = array();
        $class = epl_get_element( 'class', $args, 'epl_button ' );
        $locked = false;

        //The shortcode page id.  Everythng goes through the shortcode

        static $page_id = null; // get_option( 'epl_shortcode_page_id' );

        $page_id = epl_get_shortcode_page_id();

        if ( is_null( $page_id ) ) {

            //'post_status' => 'publish'

            $pages = get_pages();

            foreach ( $pages as $page ) {
                if ( !$page_id && stripos( $page->post_content, '[events_planner' ) !== false ) {
                    $page_id = $page->ID;
                }
            }
        }

        $url_vars = array(
            'page_id' => $page_id,
            'epl_action' => epl_regis_flow() <= 2 ? 'process_cart_action' : 'regis_form',
            'cart_action' => 'add',
            'event_id' => ($event_id) ? $event_id : $event_details['ID'],
            'epl_event' => false,
            '_rand' => uniqid(),
            '_date_id' => epl_get_element( '_date_id', $args ),
            '_time_id' => epl_get_element( '_time_id', $args ),
        );


        $url_vars = apply_filters( 'epl_get_the_register_button_url_vars', $url_vars );

        $regis_url = add_query_arg( $url_vars, epl_get_sortcode_url() ); //epl_get_url() );

        if ( epl_get_element( '_epl_alt_regis_url', $event_details, '' ) != '' )
            $regis_url = epl_get_element( '_epl_alt_regis_url', $event_details );

        $member_only = epl_get_element( '_epl_regis_privilege', $event_details, false );

        $url = $regis_url;

        if ( ($member_only == 1 && !is_user_logged_in()) || epl_get_element( 'member_only', $args, 0 ) == 1 ) {
            $class = trim( $class ); // . ' lightbox_login';
            $url = wp_login_url( $regis_url );
            $locked = true;
            $button_text = $button_text != '' ? $button_text : epl__( 'Login to Register' );
        }

        if ( !$locked && epl_sc_is_enabled() && empty( $args['no_modal'] ) ) {
            $class .= ' epl_register_button';
            $events_in_cart = $this->erm->get_events_in_cart();
            if ( epl_sc_is_enabled() == 15 ) {
                $class .= ' button_cart';
            }

            if ( isset( $events_in_cart[$event_id] ) ) {
                //TODO - come up with non global solution
                global $epl_wp_localize_script_args;
                $button_text = $epl_wp_localize_script_args['cart_added_btn_txt'];
                $class .= ' in_cart';
            }
        }
        if ( !empty( $args['no_modal'] ) )
            $class .= ' epl-no-modal';

        $url = apply_filters( 'epl_get_the_register_button_final_url', $url, $regis_url );
        $class = apply_filters( 'epl_get_the_register_button_final_class', $class );

        if ( $url_only )
            return $url;

        return "<a id='{$event_details['ID']}' class='$class' href='" . esc_url_raw( $url ) . "' data-redirect_to='{$regis_url}'>{$button_text}</a>";
    }


    function get_the_attendee_list_link( $anchor = '', $url_only = false ) {
        global $post, $event_details, $epl_current_step;

        //ok to show the list
        //where to show


        if ( epl_get_element( '_epl_show_attendee_list_link', $event_details, 0 ) == 0 )
            return null;

        $link_location = epl_get_element( '_epl_show_attendee_list_button_location', $event_details );

        if ( $link_location != $epl_current_step )
            return null;

        //$button_text = epl_nz( epl_get_setting( 'epl_event_options', 'epl_register_button_text' ), epl__( 'Register' ) );
        $button_text = ($anchor != '') ? $anchor : epl__( 'Attendees' );

        //The shortcode page id.  Everythng goes through the shortcode

        static $page_id = null; // get_option( 'epl_shortcode_page_id' );

        $page_id = epl_get_shortcode_page_id();

        if ( is_null( $page_id ) ) {

            //'post_status' => 'publish'

            $pages = get_pages();

            foreach ( $pages as $page ) {
                if ( !$page_id && stripos( $page->post_content, '[events_planner' ) !== false ) {
                    $page_id = $page->ID;
                }
            }
        }

        $url_vars = array(
            'page_id' => $page_id,
            'epl_action' => 'show_attendee_list',
            'event_id' => $event_details['ID'],
            'epl_event' => false,
        );

        $url = add_query_arg( $url_vars, epl_get_sortcode_url() ); //epl_get_url() );

        if ( $url_only )
            return $url;


        return "<a id='attendee_list_link-{$event_details['ID']}' class='attendee_list_link' href='" . $url . "'>{$button_text}</a>";
    }


    function the_event_dates() {
        global $post;

        echo $this->construct_date_display_table( array( 'post_ID' => $post_ID, 'meta' => $post_mata ) );
    }

    /*
     * END Event Template Tag handlers
     */


    function get_widget_options( $widget = null ) {

        if ( $widget == '' )
            return null;

        $opt = get_option( $widget );
        $r = array();
        foreach ( $opt as $k => $v ) {
            if ( !empty($v['title']) ) {
                $r = $v;
                break;
            }
        }

        return $r;
    }


    function get_widget_cal() {

        $c_year = (isset( $_REQUEST['c_year'] ) ? ( int ) $_REQUEST['c_year'] : date_i18n( "Y" ) );
        $c_month = (isset( $_REQUEST['c_month'] ) ? ( int ) $_REQUEST['c_month'] : date_i18n( "m" ) );
        $data = $this->get_days_for_widget( 1 );
        global $prefs;

        $prefs['template'] = '
    {table_open}<div class="epl_calendar_wrapper"><table id="" class="epl-adv-calendar">{/table_open}
    {caption_open}<caption>{/caption_open}
    {caption_close}</caption>{/caption_close}

   {heading_row_start}<tr scope="col">{/heading_row_start}

   {heading_previous_cell}<th><a href="{previous_url}" class="epl_next_prev_link">&lt;&lt;</a></th>{/heading_previous_cell}
   {heading_title_cell}<th colspan="{colspan}">{heading}</th>{/heading_title_cell}
   {heading_next_cell}<th><a href="{next_url}"  class="epl_next_prev_link">&gt;&gt;</a></th>{/heading_next_cell}

   {heading_row_end}</tr>{/heading_row_end}

    {week_day_cell}<th class="day_header">{week_day}</th>{/week_day_cell}
    {cal_cell_start}<td class="{cal_cell_start}">{/cal_cell_start}
    {cal_cell_start_today}<td id ="today">{/cal_cell_start_today}
    {cal_cell_content}<div class="widget_has_data day_listing day_listing_content" id="epl_{content}">{day}</div>{/cal_cell_content}
    {cal_cell_content_today}<div class="today widget_has_data day_listing" id="epl_{content}">{day}</div>{/cal_cell_content_today}

    {cal_cell_no_content}<div class= "day_listing ">{day}</div>{/cal_cell_no_content}
    {cal_cell_no_content_today}<div class="">{day}</div>{/cal_cell_no_content_today}
    {table_close}</table><div class="calendar_slide" style=""><span class="slide_content"></span><span class="close_calendar_slide">CLOSE</span></div></div>{/table_close}
';


        return $this->epl->epl_calendar->initialize( $prefs )->generate( $c_year, $c_month, $data );
    }

    /*
     * will be revisited
     */


    function get_days_for_widget( $for = 0, $args = array() ) {

        //echo "<pre class='prettyprint'>" . __LINE__ . "> " . print_r($args, true). "</pre>";
        //$for = 3 = fullcalendar

        global $wpdb;

        $defined_widgets = array(
            0 => 'widget_epl_upcoming_events_widget',
            1 => 'widget_epl_advanced_cal_widget'
        );

        $defined_widgets = apply_filters( 'epl_defined_widgets', $defined_widgets );

        $widget_options = (!epl_is_empty_array( $args )) ? $args : $this->get_widget_options( epl_get_element( $for, $defined_widgets ) );

        $c_year = (isset( $_REQUEST['c_year'] ) ? ( int ) $_REQUEST['c_year'] : date_i18n( "Y" ) );
        $c_month = (isset( $_REQUEST['c_month'] ) ? ( int ) $_REQUEST['c_month'] : date_i18n( "m" ) );

        $l_d = $this->epl->epl_calendar->get_total_days( $c_month, $c_year );

        $day = 1;

        $from = "$c_year-$c_month-01";
        $to = "$c_year-$c_month-$l_d";
        $exclude_past_events = false;
        $content_to_show = epl_get_element( 'content_to_show', $args, 'content' );
        $num_words_to_show = epl_get_element( 'num_words_to_show', $args, 60 );
        switch ( $for )
        {

            case 0:
                $from = EPL_DATE;
                $days_to_show = epl_get_element( 'days_to_show', $widget_options, 60 );
                $content_to_show = epl_get_element( 'content_to_show', $widget_options, 'content' );
                $num_words_to_show = epl_get_element( 'num_words_to_show', $widget_options, 60 );
                $taxonomy = epl_get_element( 'tax_filter', $widget_options, array() );
                if ( !epl_is_empty_array( $taxonomy ) )
                    $args['taxonomy'] = $taxonomy;

                //$to = $from . " + $days_to_show days";
                $to = $from + ($days_to_show * 60 * 60 * 24);
                break;
            case 1;
                $exclude_past_events = (epl_get_element( 'exclude_past_events', $widget_options ) == 'on');
                break;
            case 0:
            case 3:
                //these will work with ajax method of event retrieval
                //$from = intval( epl_get_element( 'start', $_REQUEST, null ) );
                //$to = intval( epl_get_element( 'end', $_REQUEST, null ) );
                $to = "2100-01-01";
                
                //TODO - Urgent, why has this been like this all this time?
                
                $exclude_past_events = true;
                break;
        }

        $from = epl_get_date_timestamp( $from );
        $to = epl_get_date_timestamp( $to );

        $qry_args = array(
            'post_type' => 'epl_event',
            'post_status' => 'publish',
            'posts_per_page' => -1,
            'orderby' => 'meta_value_num',
            'order' => 'ASC',
            'meta_key' => '_q__epl_start_date',
                /* 'meta_query' => array(
                  'relation' => 'AND',
                  array(
                  'key' => '_q__epl_start_date',
                  'value' => array( $from, $to ),
                  'type' => 'NUMERIC',
                  'compare' => 'BETWEEN'
                  ),
                  array(
                  'key' => '_epl_event_status',
                  'value' => 1,
                  'type' => 'NUMERIC',
                  'compare' => '='
                  )
                  ) */
        );

        //for the ongoing events

        if ( epl_get_element( 'show_past', $args ) == 1 ) {
            //unset( $qry_args['post__in'] );
            //unset( $qry_args['p'] );
            unset( $qry_args['meta_key'] );
            $exclude_past_events = false;
        }

        if ( $event_id = epl_get_element( 'event_id', $args, null ) ) {

            if ( strpos( $event_id, ',' ) !== false ) {

                $qry_args['post__in'] = explode( ',', $event_id );
            }
            else
                $qry_args['p'] = intval( epl_get_element( 'event_id', $args ) );
        }
        else {

            $WHERE = "AND pm2.meta_key = '_q__epl_start_date' AND CAST(pm2.meta_value AS SIGNED) >= " . $from . "
                AND pm2.meta_key = '_q__epl_start_date' AND CAST(pm2.meta_value AS SIGNED) <= " . $to . "";

            if ( $exclude_past_events == false && $for > 0 )
                $WHERE = '';

            $post_ids = $wpdb->get_col( "
            SELECT pm.post_id
            FROM $wpdb->postmeta pm
            INNER JOIN $wpdb->postmeta pm2 ON (pm.post_id = pm2.post_id)
            WHERE
                ((pm.meta_key = '_epl_event_status' AND CAST(pm.meta_value AS SIGNED) = '1' $WHERE)
            OR
                (pm.meta_key = '_epl_event_status' AND CAST(pm.meta_value AS SIGNED) = '3'))
            GROUP BY pm.post_id

        " );

            $post_ids = $post_ids;

            $qry_args['post__in'] = $post_ids;
        }


        if ( epl_get_element( 'location', $args ) != '' ) {

            $_l = $args['location'];

            $qry_args['meta_query'][] = array(
                'key' => '_epl_event_location',
                'value' => $_l,
                'type' => 'NUMERIC',
                'compare' => '='
            );
        }

        if ( epl_get_element( 'org', $args ) != '' ) {

            $_l = $args['org'];

            $qry_args['meta_query'][] = array(
                'key' => '_epl_event_organization',
                'value' => $_l,
                'type' => 'NUMERIC',
                'compare' => '='
            );
        }

        $qry_args['tax_query'] = array();
        if ( ($_t = epl_get_element_m( 'taxonomy', 'shortcode_atts', $args )) == '' ) {
            $_t = epl_get_element( 'taxonomy', $args, '' );
        }

        if ( $_t != '' ) {

            if ( !is_array( $_t ) && strpos( $_t, ',' ) !== false ) {

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

        if ( ($_te = epl_get_element_m( 'taxonomy_exclude', 'shortcode_atts', $args, '' )) == '' ) {
            $_te = epl_get_element( 'taxonomy_exclude', $args, '' );
        }

        if ( $_te != '' ) {

            if ( !is_array( $_te ) && strpos( $_te, ',' ) !== false ) {

                $_t = array();
                $_te = explode( ',', $_te );
            }


            $qry_args['tax_query'] += array(
                array(
                    'taxonomy' => 'epl_event_categories',
                    'terms' => $_te,
                    'field' => 'slug',
                    'operator' => 'NOT IN',
                ),
            );
        }

        /* if ( epl_get_element( 'show_past', $args ) == 1 ) {
          //unset( $qry_args['post__in'] );
          //unset( $qry_args['p'] );
          $exclude_past_events = false;
          }
          /* elseif ( epl_is_empty_array( $qry_args['post__in'] ) ) {
          //$meta_query['relation'] = 'AND';
          $qry_args['meta_query'][] = array(
          'key' => '_epl_event_status',
          'value' => 1,
          'type' => 'NUMERIC',
          'compare' => '='
          );
          } */

        $exclude_event_ids = explode( ',', epl_get_element( 'exclude_event_ids', $args ) );

        global $event_details;
        $d = array();
        $q = new WP_Query( $qry_args );

        $new = true;
        while ( $q->have_posts() ) {
            $q->the_post();

            $s = array();
            $t = get_the_title();
            setup_event_details( get_the_ID() );

            $e = $event_details['_epl_end_date'];
            $alt_regis_url = ($event_details['_epl_alt_regis_url'] != '') ? 1 : 0;

            $event_status = $event_details['_epl_event_status'];
            $event_type = $event_details['_epl_event_type'];
            $event_rec_frequency = $event_details['_epl_recurrence_frequency'];

            //if class and upcoming widget or fullcalendar
            if ( $event_type == 10 && ($for == 0 || $for == 3) ) {
                //if there is recurrence formula
                if ( $event_rec_frequency != '0' ) {
                    $this->rm->hide_past = $exclude_past_events;
                    $s = $this->rm->recurrence_dates_from_db( $event_details );
                    $s = $this->rm->construct_table_array( $s, true );

                    $e = $s['_epl_end_date'];
                    $s = $s['_epl_start_date'];
                }//else if there are session dates
                elseif ( !epl_is_empty_array( $event_details['_epl_class_session_date'] ) ) {

                    $s = $event_details['_epl_class_session_date'];
                }
            }

            if ( empty( $s ) || (epl_get_element( 'class_display_type', $widget_options, 2 ) == 1 && epl_get_element( 'class_display_type', $args, 0 ) == 1) )
                $s = $event_details['_epl_start_date'];

            if ( epl_is_empty_array( $s ) || in_array( get_the_ID(), $exclude_event_ids ) || $event_status == 2 )
                continue;



            $d = $this->make_cal_day_array( array(
                'event_id' => get_the_ID(),
                'event_status' => $event_status,
                'event_type' => epl_get_element( '_epl_event_type', $event_details ),
                'start_dates' => $s,
                'end_dates' => $e,
                'event_times' => array( 'start' => $event_details['_epl_start_time'], 'end' => $event_details['_epl_end_time'] ),
                'description' => epl_trunc( ($content_to_show == 'content' ? do_shortcode( get_the_content() ) : get_the_excerpt() ), $num_words_to_show ), //substr(  get_the_content() , 0, 200 ),
                'register_link_type' => ($alt_regis_url == 1) ? $alt_regis_url : epl_get_element( '_epl_cal_link_destination', $event_details, 0 ),
                'register_link' => (epl_get_element( '_epl_cal_link_destination', $event_details, 0 ) == 0) ? $this->get_the_register_button( get_the_ID(), true, array( 'for' => $for ) ) : get_permalink(),
                'title' => $t,
                'current_month' => $c_month,
                'from' => $from,
                'to' => $to,
                'to' => $to,
                'exclude_past_events' => $exclude_past_events,
                'term_list' => substr( array_reduce( ( array ) epl_object_to_array( get_the_terms( get_the_ID(), 'epl_event_categories' ) ), create_function( '$t,$_terms', '$t .= "{$_terms["slug"]},"; return $t;' ) ), 0, -1 )
                    ), $for, $d );
            $new = false;
            //echo "<pre class='prettyprint'>" . __LINE__ . "> " . print_r($d, true). "</pre>";
        }

        wp_reset_query();

        ksort( $d );

        $num_events_to_show = epl_get_element( 'num_events_to_show', $args, '' );
        if ( $num_events_to_show != '' && $for == 0 ) {
            $d = array_slice( $d, 0, $num_events_to_show );
        }



        return $d;
    }

    /*
     * TODO: optimize
     */


    function make_cal_day_array( $args = array(), $for = 0, $days = array() ) {

        //static $days = array( ); //problem when more than one widget used on a page

        static $counter = 0;


        foreach ( $args['start_dates'] as $date_id => $date ) {
            if ( $date == '' )
                continue;

            $_d = epl_get_date_timestamp( $date );


            //for the upcoming events widget (0) and fullcalendar (3)
            if ( $for == 0 || $for == 3 ) {

                if ( $args['exclude_past_events'] === false || $_d >= EPL_DATE || ($args['event_status'] == 3 && epl_get_date_timestamp( $args['end_dates'][$date_id] ) >= EPL_DATE) ) {

                    $tmp = array(
                        'event_id' => $args['event_id'],
                        'date_id' => $date_id,
                        'date' => $_d,
                        'end' => epl_get_date_timestamp( epl_get_element( $date_id, $args['end_dates'] ) ),
                        'times' => epl_adjust_dst( $date_id, $args['event_times'], $args['start_dates'] ),
                        'description' => $args['description'],
                        'register_link' => $args['register_link'] . (($args['register_link_type'] == 0 && $args['event_type'] < 10) ? '&_date_id=' . $date_id : ''),
                        'register_link_type' => $args['register_link_type'],
                        'term_list' => $args['term_list'],
                        'title' => html_entity_decode( htmlspecialchars_decode( $args['title'] ), ENT_QUOTES, 'UTF-8' ) );

                    $first_time = current( $tmp['times']['start'] );
                    $first_time = $first_time != '' ? strtotime( $first_time, $_d ) : $_d;
                    if ( isset( $days[$first_time . '.' . $counter] ) )
                        $counter++;

                    $days[$first_time . '.' . $counter] = $tmp;

                    if ( $args['event_status'] != 3 && $for == 0 && !($_d >= $args['from'] && $_d <= $args['to']) ) {
                        unset( $days[$first_time . '.' . $counter] );
                    }
                }
            }
            else {

                $_month = date_i18n( "m", $_d );
                if ( $_month == $args['current_month'] ) {
                    // if use "d" will not work in the calendar
                    // TODO - why twice?  Also, initialize array

                    $_date = date_i18n( "j", $_d );

                    if ( !isset( $days[$_date]['term_list'] ) )
                        $days[$_date]['term_list'] = '';

                    if ( $args['exclude_past_events'] ) {
                        if ( $_d >= EPL_DATE ) {

                            $days[$_date]['date'] = epl_get_date_timestamp( $date );
                            $days[$_date]['term_list'] .= $args['term_list'];
                        }
                        else {
                            unset( $days[$_date] );
                        }
                    }
                    else {
                        $days[$_date]['date'] = epl_get_date_timestamp( $date );
                        $days[$_date]['term_list'] .= $args['term_list'];
                    }
                }
            }
        }


        return $days;
    }

    /*
     * pull the fullcalendar dates
     */


    function get_days_for_fc( $args = array() ) {

        global $post;

//page specific caching
        if ( !epl_user_is_admin() && epl_get_setting( 'epl_fullcalendar_options', 'epl_fullcalendar_enable_cache', 10 ) == 10 && false !== ( $fc_dates = get_transient( 'epl_transient__get_days_for_fc__' . $post->ID ) ) ) {

            // return $fc_dates;
        }


        $show_att_counts = intval( epl_get_setting( 'epl_fullcalendar_options', 'epl_fullcalendar_show_att_count', 0 ) );

        if ( $show_att_counts === 0 ) {
            $show_att_counts = epl_get_element( 'show_att_counts', $args ) === true ? true : $show_att_counts;
        }

        if ( $show_att_counts === 1 && !epl_user_is_admin() )
            $show_att_counts = false;

        if ( $show_att_counts === 2 && !is_user_logged_in() )
            $show_att_counts = false;


        global $event_details;

        $events = $this->get_days_for_widget( 3, $args );
        $show_first_date_only = apply_filters( 'epl__get_days_for_fc__', false );

        $event_bcg_color = epl_get_setting( 'epl_fullcalendar_options', 'epl_fullcalendar_tax_bcg_color' );
        $event_font_color = epl_get_setting( 'epl_fullcalendar_options', 'epl_fullcalendar_tax_font_color' );

        $r = array();
        $counter = 0;
        $date_sel_mode = false; //(epl_get_element( 'date_selector', $args, 0 ) == 1);

        foreach ( $events as $date => $event_data ) {

            $data['_event'] = $event_data;
            setup_event_details( $event_data['event_id'] );

            $c = $this->epl->load_view( 'front/tooltip/template-1', $data, true );


            $_t = explode( ',', $event_data['term_list'] );

            $bc = epl_get_element( current( $_t ), $event_bcg_color, '#ffffff' );
            $tc = epl_get_element( current( $_t ), $event_font_color, 'blue' );

            $_r = array(
                'title' => ($date_sel_mode) ? 'Select' : $event_data['title'],
                'raw_title' => $event_data['title'],
                'description' => ($date_sel_mode) ? $this->erm->get_the_dates( $event_data['date_id'] ) : $c,
                'term_list' => $event_data['term_list'],
                'start_timestamp' => $event_data['date'],
                'start' => epl_formatted_date( $event_data['date'], 'Y-m-d' ),
                'end_timestamp' => $event_data['end'],
                'end' => epl_formatted_date( $event_data['end'], 'Y-m-d' ),
                'url' => ($date_sel_mode) ? '' : $event_data['register_link'],
                'edit_url' => get_edit_post_link(),
                'backgroundColor' => $bc,
                'borderColor' => $bc,
                'textColor' => $tc,
                'className' => $event_data['register_link_type'] == 1 ? 'epl-no-modal' : '',
                //'allDay' => true,
                'id' => $event_data['event_id']
            );
            $weekday = date( 'N', $event_data['date'] );
            //if multiple times, display the event for each time.
            if ( count( $event_data['times']['start'] ) > 1 && !$date_sel_mode ) {

                $_tmp_title = $_r['title'];
                $_tmp_url = $_r['url'];


                foreach ( $event_data['times']['start'] as $time_id => $time ) {
                    if ( $time == '' )
                        continue;
                    if ( epl_is_date_level_time() ) {

                        if ( epl_is_date_specific_time( $time_id ) && !isset( $event_details['_epl_date_specific_time'][$time_id][$event_data['date_id']] ) ) {

                            continue;
                        }
                    }

                    $weekday_specific = epl_get_element_m( $time_id, '_epl_weekday_specific_time', $event_details, array() );

                    if ( !epl_is_empty_array( $weekday_specific ) && !isset( $weekday_specific[$weekday] ) )
                        continue;

                    //$_r['title'] = '<span class="epl_fc_title_time"> ' . $time . '</span>' . $_tmp_title;
                    $_r['title'] = $_tmp_title;
                    $_r['raw_title'] = $_tmp_title;


                    if ( $show_att_counts ) {
                        $_r['title'] .= ' ' . '<span style="background-color:red;color:#fff;padding:0 4px;white-space: nowrap;"> ' . epl_get_att_count( array( 'for' => 'time', 'date_id' => $event_data['date_id'], 'time_id' => $time_id, 'default' => 0 ) );
                        $_r['title'] .= ((epl_get_time_capacity( $time_id ) != '') ? ' / ' . epl_get_time_capacity( $time_id ) : '') . '</span>';
                        $_r['att_counts'] = epl_get_att_count( array( 'for' => 'time', 'date_id' => $event_data['date_id'], 'time_id' => $time_id, 'default' => 0 ) );
                        $_r['att_counts'] .= ((epl_get_time_capacity( $time_id ) != '') ? ' / ' . epl_get_time_capacity( $time_id ) : '');
                    }
                    $_r['url'] = ($date_sel_mode) ? '' : add_query_arg( array( '_time_id' => $time_id ), $event_data['register_link'] );

                    $tmp_key_for_sorting = strtotime( $time, $event_data['date'] );
                    if ( isset( $r[$tmp_key_for_sorting] ) ) {
                        $tmp_key_for_sorting .= '.' . $counter;
                        $counter++;
                    }
                    $_r['start'] = epl_formatted_date( strtotime( $time, $event_data['date'] ), 'Y-m-d H:i:s' );

                    $_r['end'] = epl_formatted_date( strtotime( $event_data['times']['end'][$time_id], ($show_first_date_only ? $event_data['start'] : $event_data['end'] ) ), 'Y-m-d H:i:s' );

                    //$_r['allDay'] = false;
                    $r[$tmp_key_for_sorting] = $_r;

                    //$r[] = $_r;
                    $counter++;
                }
                continue;
            }
            else {

                $time = current( $event_data['times']['start'] );

                //$_r['title'] = ($time != '' ? '<span class="epl_fc_title_time"> ' . $time . '</span>' : '') . $_r['title'];
                $_r['title'] = $_r['title'];

                if ( epl_is_empty_array( $event_data['times']['start'] ) ) {
                    $_r['start'] = epl_formatted_date( $event_data['date'] + 25200, 'Y-m-d H:i:s' );
                }
                else
                    $_r['start'] = epl_formatted_date( strtotime( current( $event_data['times']['start'] ) . ' ', $event_data['date'] ), 'Y-m-d H:i:s' );

                if ( epl_is_empty_array( $event_data['times']['end'] ) ) {
                    $_r['end'] = epl_formatted_date( $event_data['end'] + 86399, 'Y-m-d H:i:s' );
                }
                else
                    $_r['end'] = epl_formatted_date( strtotime( current( $event_data['times']['end'] ) . ' ', ($show_first_date_only ? $event_data['start'] : $event_data['end'] ) ), 'Y-m-d H:i:s' );


                if ( $show_att_counts ) {
                    $_r['title'] .= ' ' . '<span style="background-color:red;color:#fff;padding:0 4px;white-space: nowrap;"> ' . epl_get_att_count( array( 'for' => 'date', 'date_id' => $event_data['date_id'], 'default' => 0 ) );
                    $_r['title'] .= ((epl_get_date_capacity( $event_data['date_id'] ) != '') ? ' / ' . epl_get_date_capacity( $event_data['date_id'] ) : '') . '</span>';

                    $_r['att_counts'] = epl_get_att_count( array( 'for' => 'date', 'date_id' => $event_data['date_id'], 'default' => 0 ) );
                    $_r['att_counts'] .= ((epl_get_date_capacity( $event_data['date_id'] ) != '') ? ' / ' . epl_get_date_capacity( $event_data['date_id'] ) : '');
                }
            }


            $tmp_key_for_sorting = strtotime( current( $event_data['times']['start'] ) . ' ', $event_data['date'] );
            if ( isset( $r[$tmp_key_for_sorting] ) ) {
                $tmp_key_for_sorting .= '.' . $counter;
                $counter++;
            }


            $_r['sort_time'] = $tmp_key_for_sorting;

            $r[$tmp_key_for_sorting] = $_r;

            //$_r = apply_filters( 'epl_get_cal_dates_response_loop', $r );

            $counter++;
        }
        $raw = apply_filters( 'epl_get_cal_dates_response', $r );
        ksort( $raw );

        //@TODO - temp solution for showing cal in the month of first avail. event
        global $first_event_date;
        $first_event_date = key( $raw );

        if ( isset( $args['raw'] ) )
            return $raw;
        //using array_values to get rid of temp keys, fullcalendar doesn't seem to like them :)
        $r = json_encode( array_values( $r ) );

        if ( !epl_user_is_admin() )
            set_transient( 'epl_transient__get_days_for_fc__' . $post->ID, base64_encode( $r ), 60 * 60 * 4 );

        return $r;
    }


    function get_sess_days_for_fc( $args = array() ) {
        global $event_details;

        $event_id = epl_get_element( 'event_id', $args );


        $event_bcg_color = epl_get_setting( 'epl_fullcalendar_options', 'epl_fullcalendar_tax_bcg_color' );
        $event_font_color = epl_get_setting( 'epl_fullcalendar_options', 'epl_fullcalendar_tax_font_color' );

        $r = array();
        $dates = epl_get_element( '_epl_class_session_date', $event_details );
        $start_times = epl_get_element( '_epl_class_session_start_time', $event_details );
        $end_times = epl_get_element( '_epl_class_session_end_time', $event_details );
        $class_name = epl_get_element( 'post_title', $event_details );

        if ( epl_is_empty_array( $dates ) )
            return array();

        foreach ( $dates as $date_id => $date ) {

            $data['_event'] = $d;

            //$c = $this->epl->load_view( 'front/tooltip/template-1', $data, true );


            $_t = explode( ',', $event_data['term_list'] );

            $bc = epl_get_element( current( $_t ), $event_bcg_color, '#ffffff' );
            $tc = epl_get_element( current( $_t ), $event_font_color, 'blue' );


            $r[] = array(
                'title' => $class_name,
                //'description' => '<strong>' .date_i18n( get_option( 'date_format' ), epl_get_date_timestamp( $event_data['date'] ) ) . '</strong><br />' . $event_data['description'],
                //'description' => ($date_sel_mode) ? $this->erm->get_the_dates( $event_data['date_id'] ) : $c,
                'term_list' => $event_data['term_list'],
                //'start' =>date_i18n( "Y-m-d H:i", strtotime($start_times[$date_id], epl_get_date_timestamp( $date ) )),
                'start' => date_i18n( "Y-m-d\TH:i:s", strtotime( $start_times[$date_id], epl_get_date_timestamp( $date ) ) ),
                'end' => date_i18n( "Y-m-d\TH:i:s", strtotime( $end_times[$date_id], epl_get_date_timestamp( $date ) ) ),
                //'end' =>date_i18n( "Y-m-d", epl_get_date_timestamp( $date ) ). ' ' . $end_times[$date_id],
                'minTime ' => $start_times[$date_id],
                'maxTime ' => $end_times[$date_id],
                'url' => ($date_sel_mode) ? '' : $event_data['register_link'], //$this->epl_util->get_the_register_button( $event_data['event_id'], true ),
                //'backgroundColor' => $bc,
                //'borderColor' => $bc,
                //'textColor' => $tc,
                'id' => $event_data['event_id'],
                'allDay' => 0
            );
        }
        $r = apply_filters( 'epl_get_cal_dates_response', $r );

        return json_encode( $r );
    }

    /*
     * called by the advanced cal widget when a date is pressed
     */


    function get_events_for_day( $date ) {
        global $event_details, $wpdb;

        $start_of_day = ( int ) $date;
        $end_of_day = strtotime( date_i18n( 'Y-m-d', $start_of_day ) . ' 23:59:59' );

        $args = array(
            'post_type' => 'epl_event',
            'post_status' => 'publish',
            'posts_per_page' => -1,
            'orderby' => 'meta_value_num',
            'order' => 'ASC',
            'meta_key' => '_epl_event_sort_order',
            'meta_query' => array(
            /* 'relation' => 'AND',
              array(
              'key' => '_q__epl_start_date',
              'value' => array( $start_of_day, $end_of_day ),
              //'type' => 'date',
              'compare' => 'BETWEEN'
              ),
              array(
              'key' => '_epl_event_status',
              'value' => 1,
              'type' => 'NUMERIC',
              'compare' => '='
              ) */
            )
        );

        $post_ids = $wpdb->get_col( "
            SELECT pm.post_id
            FROM $wpdb->postmeta pm
            INNER JOIN $wpdb->postmeta pm2 ON (pm.post_id = pm2.post_id)
            WHERE
                ((pm.meta_key = '_epl_event_status' AND CAST(pm.meta_value AS SIGNED) = '1'
                AND pm2.meta_key = '_q__epl_start_date' AND (CAST(pm2.meta_value AS SIGNED) >= " . $start_of_day . " AND CAST(pm2.meta_value AS SIGNED) <= " . $end_of_day . "))
               OR
                (pm.meta_key = '_epl_event_status' AND CAST(pm.meta_value AS SIGNED) = '3'
                AND pm2.meta_key = '_q__epl_end_date' AND CAST(pm2.meta_value AS SIGNED) >= " . EPL_DATE . ")
            )

        " );

        $post_ids = array_unique( $post_ids );

        $args['post__in'] = $post_ids;

        $event_data = array();
        $q = new WP_Query( $args );

        while ( $q->have_posts() ) {
            $q->the_post();
            $this->ecm->setup_event_details( get_the_ID() );

            if ( !in_array( $start_of_day, ( array ) $event_details['_epl_start_date'] ) )
                continue;
            $r = array(
                'title' => get_the_title(),
                'regis_link' => (epl_get_element( '_epl_cal_link_destination', $event_details, 0 ) == 0) ? $this->get_the_register_button( get_the_ID(), true ) : get_permalink(),
            );


            $event_data[get_the_ID()] = $r;
        }


        return $event_data;
    }


//there is another way, coming
    function remove_array_vals( $array = array(), $replace = '' ) {

        foreach ( $array as $k => $v ) {
            $array[$k] = $replace;
        }

        return $array;
    }


    function clean_input( $data ) {

        if ( !is_array( $data ) ) {
            $this->clean_input_process( $data, null );
        }
        else
            array_walk_recursive( $data, array( get_class(), 'clean_input_process' ) );

        return $data;
    }


    function clean_input_process( &$item, $key ) {

        $item = wp_kses_post( $item );
    }


    function clean_input_old( $data ) {
        return array_map( array( get_class(), 'clean_input_process_old' ), $data );
    }


    function clean_input_process_old( $data ) {
        global $wpdb;
        if ( is_array( $data ) ) {
            $k = key( $data );
            $data[$k] = self::clean_input_process( current( $data ) );
            //return self::clean_input_process( current( $data ) );
            return $data;
        }

        return $wpdb->escape( trim( strip_tags( $data ) ) );
        //return  htmlentities( strip_tags( trim($data) ), ENT_QUOTES, 'UTF-8' ) ;
    }


    function clean_output( $data ) {
        if ( !is_array( $data ) || empty( $data ) )
            return $data;

        return array_map( array( get_class(), 'clean_output_process' ), $data );
    }


    function clean_output_process( $data ) {
        return stripslashes_deep( $data );
    }


    function extract_labels( $fields ) {

        $r = array();

        foreach ( $fields as $k => $v )
            $r[$k] = epl_get_element( 'label', $v );

        return $r;
    }


    function epl_terms_field( $args = array() ) {

        $defaults = array(
            'class' => '',
            'type' => 'checkbox',
            'value' => array(),
            'empty_row' => false,
            'display_inline' => false
        );

        $args = wp_parse_args( $args, $defaults );
        extract( $args );


        $terms = epl_term_list();

        $f = array(
            'input_type' => $type,
            'input_name' => $name,
            'options' => $terms,
            'value' => $value,
            'class' => $class,
            'empty_row' => $empty_row,
            'display_inline' => $display_inline,
        );

        return $this->create_element( $f, 0 );
    }

    /*
     * send general email
     */


    function send_email( $args = array() ) {
        global $organization_details, $customer_email, $event_details;

        extract( $args );

        $email_template = ($email_template == '') ? 'default' : $email_template;

        $data['email_body'] = preg_replace( '/<div class=\'epl_(.*?)_message\'>(.*?)<\/div>/', '', $email_body );

        $data['base_url'] = EPL_EMAIL_TEMPLATES_URL . $email_template . '/';

        $email_template = 'email/' . $email_template . '/template.php';

        $email_body = html_entity_decode( $this->epl->load_view( $email_template, $data, true ), ENT_QUOTES );


        $from_name = stripslashes_deep(html_entity_decode( epl_get_element( '_epl_email_from_name', $notif_data, get_bloginfo( 'name' ) ), ENT_QUOTES ));
        $from_email = epl_get_element( '_epl_from_email', $notif_data, get_bloginfo( 'admin_email' ) );
        $subject = stripslashes_deep(html_entity_decode( epl_get_element( '_epl_email_subject', $notif_data, epl__( 'Registration Confirmation' ) ), ENT_QUOTES ));




        $headers = "From: \"" . $from_name . "\" <{$from_email}> \r\n";
        $headers .= 'Reply-To: ' . $from_email . "\r\n";
        $headers .= 'X-Mailer: PHP/' . phpversion();
        $headers .= "MIME-Version: 1.0\r\n";
        $headers .= "Content-Type: text/html; charset=UTF-8\r\n";

        if ( !empty( $notif_data['_epl_email_cc'] ) )
            $headers .= "Cc: {$notif_data['_epl_email_cc']} \r\n";

        if ( !empty( $notif_data['_epl_email_bcc'] ) )
            $headers .= "Bcc: {$notif_data['_epl_email_bcc']} \r\n";

        if ( (isset( $customer_email )) && $customer_email != '' )
            $e = @wp_mail( $customer_email, $subject, $email_body, $headers, epl_get_element( 'attachment', $args, null ) );

        return $e;
    }


//TODO - refactor
    function send_confirmation_email( $data ) {
        global $organization_details, $customer_email, $event_details, $email_regis_form;

        //$data['email_body'] = '';
        $attach_pdf = (epl_get_setting( 'epl_api_option_fields', 'epl_invoice_attach_to_conf', 0 ) == 10 && epl_is_addon_active( '_epl_atp' ));

        $attach_pdf = apply_filters( 'epl_attach_invoice_to_conf_email', $attach_pdf );

        $defaults = array(
            'admin_subject' => epl__( 'New Registration' ),
            'subject' => epl__( 'Registration Confirmation' )
        );

        $default_email_body = $this->epl->load_view( 'email/default/template-no-custom', $data, true );

        $_notif = epl_get_element( '_epl_event_notification', $event_details );

        if ( epl_is_waitlist_flow() ) {
            $_notif = epl_get_element( '_epl_waitlist_notification', $event_details );
            $defaults['admin_subject'] = epl__( 'New Waitlist Addition' );
        }
        $_notif_data = array();

        if ( epl_sc_is_enabled() && !epl_is_waitlist_flow() )
            $_notif = epl_get_setting( 'epl_sc_options', 'epl_sc_notification' );


        if ( $_notif && (!epl_is_empty_array( $_notif ) || $_notif != '') ) {

            $id = is_array( $_notif ) ? current( $_notif ) : $_notif;
            $_notif_data = get_post( $id, ARRAY_A ) + ( array ) $this->ecm->get_post_meta_all( $id );

            $data['email_body'] = $this->notif_tags( $_notif_data['post_content'], $data );
        }

        if ( epl_is_empty_array( $_notif ) || $_notif == '' ) {
            $email_template = 'email/default/template-no-custom';

            $email_template_name = epl_get_element( '_epl_email_template', $_notif_data );

            $email_template = $email_template_name ? 'email/' . $email_template_name . '/template.php' : $email_template;

            $data['base_url'] = EPL_EMAIL_TEMPLATES_URL . $email_template_name . '/';

            $email_body = $this->epl->load_view( $email_template, $data, true );
        }
        else
            $email_body = $data['email_body'];


        $email_body = preg_replace( '/<div class=\'epl_(.*?)_message\'>(.*?)<\/div>/', '', $email_body );

        $from_name = stripslashes_deep(html_entity_decode( epl_get_element( '_epl_email_from_name', $_notif_data, get_bloginfo( 'name' ) ), ENT_QUOTES ));
        $from_email = epl_get_element( '_epl_from_email', $_notif_data, get_bloginfo( 'admin_email' ) );
        $subject = stripslashes_deep(html_entity_decode( epl_get_element( '_epl_email_subject', $_notif_data, $defaults['subject'] ), ENT_QUOTES ));

        $headers = "From: \"" . $from_name . "\" <{$from_email}> \r\n";
        $headers .= 'Reply-To: ' . $from_email . "\r\n";
        $headers .= 'X-Mailer: PHP/' . phpversion();
        $headers .= "MIME-Version: 1.0\r\n";
        $headers .= "Content-Type: text/html; charset=UTF-8\r\n";

        if ( $attach_pdf ) {

            $invoice = $this->erptm->invoice( $data['post_ID'] );
            $data['attachment'] = $this->make_pdf( $invoice, true, true );
        }


        if ( (isset( $customer_email )) && count( $customer_email ) > 0 ) {
            $to = implode( ',', $customer_email );

            @wp_mail( $to, $subject, $email_body, $headers, epl_get_element( 'attachment', $data, null ) );
        }
        //admin email
        $_email = epl_get_event_property( '_epl_alt_admin_email' );

        if ( $_email == '' ) {

            $_email = epl_nz( epl_get_event_option( 'epl_default_notification_email' ), get_bloginfo( 'admin_email' ) );
        }
        $admin_email = array(
            'email' => $_email,
            'title' => $defaults['admin_subject'] . ': ' . get_the_event_title(),
            'email_body' => $email_body,
            'headers' => $headers,
            'data' => $data
        );
        $admin_email = apply_filters( 'epl_admin_send_confirmation_email_args', $admin_email );

        @wp_mail( $admin_email['email'], $admin_email['title'], $admin_email['email_body'], $admin_email['headers'], epl_get_element( 'attachment', $admin_email['data'], null ) );

        if ( $attach_pdf )
            $this->delete_file( $data['attachment'] );
    }


    function notif_tags( $email_body, $data ) {
        global $event_details, $system_email_tags, $email_regis_form;
        if ( $this->regis_id == '' )
            return null;

        $email_body = nl2br( stripslashes_deep( html_entity_decode( $email_body, ENT_QUOTES ) ) );
        $regis_meta = $this->ecm->setup_regis_details( $this->regis_id );
        $reigs_id = $regis_meta['__epl']['_regis_id'];
        $event_id = key( $regis_meta['__epl'][$reigs_id]['_events'] );


        //find the list of all forms
        $available_forms = $this->ecm->get_list_of_available_forms();
        $available_fields = $this->ecm->get_list_of_available_fields();

        $attendee_info = $regis_meta['__epl'][$reigs_id]['_attendee_info'];

        $_attendee_info = array();

        foreach ( $attendee_info as $_f_id => $_att_data ) {

            if ( !isset( $available_fields[$_f_id] ) )
                continue;
            //$_attendee_info[$_f_id] = epl_get_element( 0, epl_get_element( $event_id, $_att_data ) );

            $value = epl_get_element( 0, $_att_data, false );

            if ( !$value )
                $value = epl_get_element( 0, epl_get_element( $event_id, $_att_data ) );

            $input_type = epl_get_element( 'input_type', $available_fields[$_f_id] );

            if ( $input_type == 'select' || $input_type == 'radio' ) {
                $field_choice_text = epl_get_element( $value, $available_fields[$_f_id]['epl_field_choice_text'], null );
                $value = ($field_choice_text && $field_choice_text != '') ? $field_choice_text : $value;
            }
            elseif ( $input_type == 'checkbox' ) {

                $value = (implode( ',', ( array ) $value ) );
            }
            else {
                //TODO - eek, find a better way
                $value = html_entity_decode( htmlspecialchars_decode( $value, ENT_QUOTES ) );
            }

            $_attendee_info[$_f_id] = $value;
        }


        $event_ticket_buyer_forms = array_flip( ( array ) $event_details['_epl_primary_regis_forms'] );

        $gateway_info = array();
        if ( !epl_is_free_event() ) {
            $gw_id = $this->erm->get_payment_profile_id();
            $gateway_info = $this->ecm->get_post_meta_all( $gw_id );
        }


        $data['payment_instructions'] = $this->epl->load_view( 'front/registration/regis-payment-instr', array( 'gateway_info' => $gateway_info ), true );
        $data['payment_details'] = $this->epl->load_view( 'front/registration/regis-payment-details', $data, true );


        $registration_detail_link = add_query_arg( array( 'epl_token' => epl_get_token() ), get_permalink( $this->regis_id ) );

        $_system_email_tags = array(
            'registration_id' => get_the_regis_id(),
            'registration_detail_link' => '<a href="' . $registration_detail_link . '" target="_blank" alt="' . epl__( 'Registration Detail Link' ) . '">' . epl__( 'Click here' ) . '</a>',
            'event_name' => get_the_title( $event_id ),
            'registration_details' => str_replace( 'class="epl_dates_times_prices_table"', 'style="clear:both;width:100%;margin:10px auto;border:1px solid #eee"', get_the_regis_dates_times_prices( $data['post_ID'] ) ),
            'registration_form_data' => $email_regis_form, // str_replace( 'for=\'\'', 'style="font-style:italic;color:#555555;font-size:12px"', $data['regis_form'] ),
            'payment_details' => str_replace( 'class="epl_payment_details_table"', 'style="clear:both;width:100%;margin:10px auto;border:1px solid #eee"', $data['payment_details'] ),
            'waitlist_approved_link' => epl_anchor( epl_get_waitlist_approved_url(), epl__( 'Register' ) ),
            'waitlist_approved_until' => epl_waitlist_approved_until(),
            'event_details_link' => epl_anchor( get_permalink( $event_details['ID'] ), get_the_title( $event_details['ID'] ) ),
            'location_details' => ((!epl_is_multi_location() && epl_get_event_property( '_epl_event_location', true ) > 0 ) ?
                    epl_suffix( ', ', get_the_location_name() ) .
                    epl_suffix( ', ', get_the_location_address() . ' ' . get_the_location_address2() ) .
                    get_the_location_city() . ' ' .
                    get_the_location_state() . ' ' .
                    get_the_location_zip() : ''),
            'location_map_link' => get_the_location_gmap_icon( epl__( 'Click here' ), true )
        );

        $_system_email_tags = apply_filters( 'epl_system_email_tags', $_system_email_tags );



        //isolate the forms that are selected inside the event
        // $ticket_buyer_forms = array_intersect_key( $available_forms, $event_ticket_buyer_forms );


        /*
          $tickey_buyer_fields = array( );
          foreach ( $ticket_buyer_forms as $_form_id => $_form_info )
          $tickey_buyer_fields += $_form_info['epl_form_fields'];
         */

        preg_match_all( '/(?<=\{)(.*?)(?=\})/', $email_body, $matches );

        $_tags_in_body = array_flip( epl_get_element( 0, $matches, array() ) );

        $_field_input_tags = array();
        foreach ( $available_fields as $f_id => $f_data ) {
            $_field_input_tags[$f_data['input_slug']] = $f_id;
        }

        $tagss = array_intersect_key( $_field_input_tags, $_tags_in_body );


        $final_tags = array();

        foreach ( $tagss as $k => $v ) {
            $final_tags[$k] = epl_get_element( $v, $_attendee_info );
        }


        $final_tags += $_system_email_tags;

        $find = array_keys( $final_tags );
        array_walk( $find, create_function( '&$val', '$val = "{".$val."}";' ) );


        $replace = array_values( $final_tags );

        return str_ireplace( $find, $replace, $email_body );
    }


    function make_pdf( $data, $save = false, $force = false, $attachment = true, $paper = 'portrait' ) {
        if ( ( $force || epl_get_element( 'pdf', $_REQUEST, 0 ) == 1 ) && $data != '' ) {

            //$r = $this->epl->load_view( 'admin/template', $data, true );
            //images will not render correctly on some hosts
            $data = str_ireplace( home_url(), ABSPATH, $data );

            $this->epl->load_file( 'libraries/dompdf/dompdf_config.inc.php' );


            if ( get_magic_quotes_gpc() )
                $r = stripslashes( $data );

            $dompdf = new DOMPDF();
            $dompdf->load_html( $data );
            $dompdf->set_paper( '', $paper );
            $dompdf->render();
            $file_name = date( 'Y-m-d-h-i-s', time() ) . '.pdf';
            if ( $save ) {
                $pdf = $dompdf->output();

                $full_path = epl_upload_dir_path() . $file_name;

                file_put_contents( $full_path, $pdf );
                return $full_path;
            }

            $dompdf->stream( $file_name, array( "Attachment" => $attachment ) );
            exit( 0 );
        }
        return $data;
    }


    function delete_file( $full_path = null ) {

        if ( $full_path && file_exists( $full_path ) )
            @unlink( $full_path );
    }

}
