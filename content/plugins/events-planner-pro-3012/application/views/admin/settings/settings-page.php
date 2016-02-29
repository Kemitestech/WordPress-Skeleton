
<div id="wpbody-content" style="overflow: auto;">

    <div class="wrap">

        <h2><?php epl_e( 'Events Planner Settings' ); ?></h2>

        <div id="icon-options-general" class="icon32"><br></div>
        <h2 class="nav-tab-wrapper">

            <?php

            $current = 'general';
            if ( isset( $_GET['tab'] ) )
                $current = $_GET['tab'];


            if ( 'true' == esc_attr( $_GET['updated'] ) )
                echo '<div class="updated" ><p>' . epl__( 'Settings updated.' ) . '</p></div>';

            $base_url = epl_get_url();

            foreach ( $tabs as $tab => $name ) {
                $class = ( $tab == $current ) ? ' nav-tab-active' : '';
                echo "<a class='nav-tab$class' href='" . add_query_arg( array( 'tab' => $tab ), $base_url ) . "'>$name</a>";
            }
            ?>
        </h2>

        <div id="poststuff">
            <form action="<?php echo epl_get_url(); ?>" method="post">
                <?php

                wp_nonce_field( 'epl_form_nonce', '_epl_nonce' );
                global $pagenow;

                if ( $pagenow == 'edit.php' && $_GET['page'] == 'epl_settings' ) {

                    echo '<table class="form-table epl_settings_table">';
                    switch ( $current )
                    {
                        case 'general' :
                            ?>
                            <tr>

                                <td>

                                    <table class="" cellspacing="0">
                                        <?php

                                        foreach ( $epl_general_option_fields as $field ) :
                                            ?>
                                            <tr>
                                                <?php

                                                echo $field;
                                                ?>
                                            </tr>
                                        <?php endforeach; ?>

                                    </table>

                                </td>
                            </tr>
                            <?php

                            break;
                        case 'registrations' :
                            ?>
                            <tr>

                                <td>

                                    <table class="" cellspacing="0">

                                        <?php

                                        foreach ( $epl_registration_options as $field ) :
                                            ?>
                                            <tr>
                                                <?php

                                                echo $field;
                                                ?>
                                            </tr>
                                        <?php endforeach; ?>

                                    </table>

                                </td>
                            </tr>
                            <?php

                            break;
                        case 'event-management' :
                            ?>
                            <tr>
                                <td>
                                    <table class="" cellspacing="0">

                                        <?php

                                        foreach ( $epl_event_options as $field ) :
                                            ?>
                                            <tr>
                                                <?php

                                                echo $field;
                                                ?>
                                            </tr>
                                        <?php endforeach; ?>

                                    </table>
                                </td>
                            </tr>
                            <?php

                            break;
                        case 'fullcalendar-settings' :
                            ?>
                            <tr>
                                <td>

                                    <table class="" cellspacing="0">




                                        <?php

                                        foreach ( $epl_fullcalendar_options as $field ) :
                                            ?>
                                            <tr>
                                                <?php

                                                echo $field;
                                                ?>
                                            </tr>
                                        <?php endforeach; ?>

                                    </table>
                                    <div class="epl_info">
                                        <div class="epl_box_content">

                                            <?php epl_e( "Use the setting below to assign specifc colors to each event category.  If an event has more than one category, the colors from the first category will be used." ); ?>
                                        </div>
                                    </div>
                                    <table class="epl_form_data_table epl_w500" cellspacing="0">
                                        <thead>
                                            <tr>

                                                <th><?php epl_e( 'Category' ); ?></th>
                                                <th><?php epl_e( 'Background Color' ); ?></th>
                                                <th><?php epl_e( 'Font Color' ); ?></th>
                                            </tr></thead>
                                        <tbody>
                                            <?php

                                            foreach ( ( array ) $_tax_color as $k => $v ):
                                                ?>

                                                <tr>
                                                    <td>
                                                        <?php echo $v['label']; ?>
                                                    </td>
                                                    <td>
                                                        <?php echo $v['field']; ?>
                                                        <input type='button' class='pick_color button-secondary' value='...'>
                                                        <div class='epl_color_picker'></div>
                                                    </td>
                                                    <td>
                                                        <?php echo $_font_color[$k]['field']; ?>
                                                        <input type='button' class='pick_color button-secondary' value='...'>
                                                        <div class='epl_color_picker'></div>
                                                    </td>
                                                </tr>

                                            <?php endforeach; ?>

                                        </tbody>
                                    </table>



                                </td>
                            </tr>
                            <?php

                            break;
                        case 'api-settings' :
                            ?>
                            <tr>
                                <td>
                                    <table class="" cellspacing="0">

                                        <?php

                                        foreach ( $epl_api_options as $field ) :
                                            ?>
                                            <tr>
                                                <?php

                                                echo $field;
                                                ?>
                                            </tr>
                                        <?php endforeach; ?>

                                    </table>




                                </td>
                            </tr>
                            <?php

                            break;
                        case 'feature-override' :

                            //echo "<pre class='prettyprint'>" . __LINE__ . "> " . basename( __FILE__ ) . " > " . print_r( $epl_fields, true ) . "</pre>";
                            $event_fields = $epl_fields;

                            unset( $event_fields['epl_api_option_fields'] );
                            $override_fields = $this->epl_util->combine_array_keys( $event_fields );
                            $override_fields = $event_fields;

//echo "<pre class='prettyprint'>" . __LINE__ . "> " . basename( __FILE__ ) . " > " . print_r( $override_fields, true ) . "</pre>";
                            ?>
                            <tr>
                                <td>

                                    <table>
                                        <thead>
                                            <tr>
                                                <th style="width:60px;">Enable</th>
                                                <th>slug</th>
                                                <th>Label</th>
                                                <th>Help text</th>
                                            </tr>
                                        </thead>

                                        <?php

                                        $override_values = stripslashes_deep( get_option( '_epl_override' ) );

                                        foreach ( $override_fields as $section ):
                                            foreach ( $section as $field_slug => $field_info ):

                                                $label = epl_get_element_m( 'label', $field_slug, $override_values, $field_info['label'] );
                                                $help_text = epl_get_element_m( 'help_text', $field_slug, $override_values, $field_info['help_text'] );
                                                ?>
                                                <tr>
                                                    <td>
                                                        <?php

                                                        if ( epl_get_element( 'deact', $field_info, true ) == true ) {
                                                            $c = $this->epl->epl_util->create_element(
                                                                    array(
                                                                        'input_type' => 'radio',
                                                                        'input_name' => "_epl_override[$field_slug][active]",
                                                                        'options' => array( 10 => 'Y', 0 => 'N' ),
                                                                        'value' => epl_get_element_m( 'active', $field_slug, $override_values, 10 ),
                                                                        'display_inline' => true,
                                                                    )
                                                            );
                                                            echo $c['field'];
                                                        }
                                                        ?>
                                                        <!--<input name="_epl_override[<?php echo $field_slug; ?>][active]" type="checkbox" value="10" checked="checked" />-->
                                                    </td>
                                                    <td><?php echo $field_slug; ?></td>
                                                    <td>
                                                        <input type="text" name="_epl_override[<?php echo $field_slug; ?>][label]" value="<?php echo $label; ?>" size="80" />
                                                        <?php

                                                        if ( isset( $field_info['options'] ) ):
                                                            ?>
                                                            <div style="margin-left: 15px;"><b>Option Labels</b>
                                                                <?php

                                                                foreach ( $field_info['options'] as $option_key => $option ):
                                                                    $option = epl_get_element_m( $option_key, 'options', $override_values[$field_slug], $option );
                                                                    ?>
                                                                    <br /> <textarea name="_epl_override[<?php echo $field_slug; ?>][options][<?php echo $option_key; ?>]" rows="1" cols="80"><?php echo $option; ?></textarea>
                                                            <?php endforeach; ?>
                                                            </div>
                                                            <?php

                                                        endif;
                                                        ?>
                                                    </td>
                                                    <td><textarea name="_epl_override[<?php echo $field_slug; ?>][help_text]" rows="2" cols="90"><?php echo $help_text; ?></textarea></td>
                                                </tr>

                                            <?php endforeach; ?>
            <?php endforeach; ?>
                                    </table>
                                    <input type="hidden" name="feature-override" value="1" />
                                </td>
                            </tr>
                            <?php

                            break;
                        case 'shopping-cart' :
                            ?>
                            <tr>
                                <td>
                                    <table class="" cellspacing="0">

                                        <?php

                                        //echo "<pre class='prettyprint'>" . __LINE__ . "> " . print_r($epl_sc_options, true). "</pre>";
                                        foreach ( $epl_sc_options as $field ) :
                                            ?>
                                            <tr>
                                                <?php

                                                echo $field;
                                                ?>
                                            </tr>
            <?php endforeach; ?>

                                    </table>




                                </td>
                            </tr>
                            <?php

                            break;
                    }
                    echo '</table>';
                }
                ?>
                <p class="submit" style="clear: both;">
                    <input type="submit" name="Submit"  class="button-primary" value="<?php epl_e( 'Save Changes' ); ?>" />

                </p>
            </form>


        </div>

    </div>

</div>

<div class="clear"></div>
<script>
    
    jQuery(document).ready(function($){
        // $(".chzn-select").chosen();
        create_sortable('.epl_subform_table tbody');


        var _custom_media = true,
        _orig_send_attachment = wp.media.editor.send.attachment;
        $('body').on('click', '#epl_invoice_logo_select', function(){
            
            var send_attachment_bkp = wp.media.editor.send.attachment;
            var me = $(this);
            var field = $('#epl_invoice_logo');
            _custom_media = true;
            wp.media.editor.send.attachment = function(props, attachment){

                var size = props.size;
                if ( _custom_media ) {
                    field.val(attachment.sizes[size].url);

                } else {
                    return _orig_send_attachment.apply( this, [props, attachment] );
                };
            }

            wp.media.editor.open(me);
            return false;
        });

        tinyMCE.init({
            mode: "exact",
            elements : "epl_invoice_instruction",
            //theme : "advanced", //turned this off as of wp 3.9 as it looks like the advanced theme does not exist any more.  The system uses 'modern' instead
            relative_urls : false,
            remove_script_host : false,

            width : "600",
            height : "200",
            theme_advanced_toolbar_location : "top",
            theme_advanced_toolbar_align : "left",
            theme_advanced_statusbar_location : "bottom",
            theme_advanced_resizing : true
        });

        tinyMCE.init({
            mode: "exact",
            elements : "epl_invoice_company_info",
            //theme : "advanced",
            relative_urls : false,
            remove_script_host : false,
            width : "600",
            height : "200",

            theme_advanced_toolbar_location : "top",
            theme_advanced_toolbar_align : "left",
            theme_advanced_statusbar_location : "bottom",
            theme_advanced_resizing : true
        });
            
            

    });



    
</script>