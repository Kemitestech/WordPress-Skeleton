<?php

/*
 * Configuration fields for the event.
 *
 * DO NOT MODIFY.
 *
 * Visit wpeventsplanner.com for instructions
 */

global $epl_fields;

$epl_fields['epl_event_type_fields'] =
        array(
            '_epl_event_type' => array(
                'deact' => false,
                'input_type' => 'radio',
                'input_name' => '_epl_event_type',
                'options' => array(
                    5 => sprintf( epl__( 'User can only register for %s one day %s.' ), '<span class="epl_font_red">', '</span>' )
                ),
                'default_value' => 5,
                'default_checked' => 1
            ),
);

$epl_fields['epl_event_type_fields'] = apply_filters( 'epl_event_type_fields', $epl_fields['epl_event_type_fields'] );


$epl_fields['epl_price_fields'] =
        array(
            '_epl_price_name' => array(
                'weight' => 10,
                'input_type' => 'text',
                'input_name' => '_epl_price_name[]',
                'label' => epl__( 'Price Label' ),
                'class' => 'epl_w100pct req epl_font_bold',
                'deact' => false,
                'parent_keys' => true ),
            '_epl_price' => array(
                'weight' => 15,
                'input_type' => 'text',
                'input_name' => '_epl_price[]',
                'label' => epl__( 'Price' ),
                'deact' => false,
                'class' => 'epl_w70 req' ),
            '_epl_member_price' => array(
                'weight' => 17,
                'input_type' => 'text',
                'input_name' => '_epl_member_price[]',
                'label' => epl__( 'Member Price' ),
                'class' => 'epl_w70 req' ),
            '_epl_price_min_qty' => array(
                'weight' => 20,
                'input_type' => 'text',
                'input_name' => '_epl_price_min_qty[]',
                'label' => epl__( 'Min.' ),
                'class' => 'epl_w30 req',
                'default_value' => 1
            ),
            '_epl_price_max_qty' => array(
                'weight' => 25,
                'input_type' => 'text',
                'input_name' => '_epl_price_max_qty[]',
                'label' => epl__( 'Max.' ),
                'class' => 'epl_w30 req',
                'default_value' => 1 ),
            '_epl_price_zero_qty' => array(
                'weight' => 27,
                'input_type' => 'select',
                'input_name' => '_epl_price_zero_qty[]',
                'label' => epl__( 'Show 0?' ),
                'options' => epl_yes_no(),
                'default_value' => 10 ),
            '_epl_price_type' => array(
                'weight' => 50,
                'input_type' => 'select',
                'input_name' => '_epl_price_type[]',
                'label' => epl__( 'Type' ),
                'options' => array( 'att' => epl__( 'Attendee' ), 'non_att' => epl__( 'Non-attendee' ) ),
                'default_value' => 1 ),
            '_epl_price_discountable' => array(
                'weight' => 52,
                'input_type' => 'select',
                'input_name' => '_epl_price_discountable[]',
                'label' => epl__( 'Discountable?' ),
                'options' => epl_yes_no(),
                'default_value' => 10 ),
            '_epl_price_note' => array(
                'weight' => 58,
                'input_type' => 'text',
                'input_name' => '_epl_price_note[]',
                'label' => epl__( 'Note' ),
                'placeholder' => epl__( 'Note to Customer (Optional)' ),
                'class' => 'epl_w500' ),
            '_epl_price_hide' => array(
                'weight' => 200,
                'input_type' => 'select',
                'input_name' => '_epl_price_hide[]',
                'options' => epl_yes_no(),
                'label' => epl__( 'Hide' ) ),
            '_epl_price_parent_time_id' => array(
                'weight' => 500,
                'input_type' => 'hidden',
                'input_name' => '_epl_price_parent_time_id[]',
                'default_value' => 0 )
);

$epl_fields['epl_price_fields'] = apply_filters( 'epl_price_fields', $epl_fields['epl_price_fields'] );
uasort( $epl_fields['epl_price_fields'], 'epl_sort_by_weight' );

$epl_fields['epl_price_option_fields'] =
        array(
            '_epl_free_event' => array(
                'input_type' => 'select',
                'input_name' => '_epl_free_event',
                'label' => epl__( 'Is this a free event? ' ),
                'options' => epl_yes_no(),
                'default_value' => 0
            ),
            '_epl_price_per' => array(
                'input_type' => 'select',
                'input_name' => '_epl_price_per',
                'label' => epl__( 'Apply prices to' ),
                'help_text' => epl__( 'If you are offering multiple days, this will determine if the user pays per day or for the whole event.' ),
                'options' => array( 0 => epl__( 'The Whole Event' ), 10 => epl__( 'Each Event Date' ) )
            ),
);

$epl_fields['epl_price_option_fields'] = apply_filters( 'epl_price_option_fields', $epl_fields['epl_price_option_fields'] );

$epl_fields['epl_surcharge_fields'] =
        array(
            '_epl_surcharge_label' => array(
                'weight' => 2,
                'input_type' => 'text',
                'input_name' => '_epl_surcharge_label',
                'label' => epl__( 'Surcharge Label' ),
                'help_text' => epl__( 'Label that will be displayed to the user' ),
                'default_value' => epl_get_regis_setting( 'epl_surcharge_label' ) ),
            /* '_epl_surcharge_note' => array(
              'weight' => 3,
              'input_type' => 'text',
              'input_name' => '_epl_surcharge_note',
              'label' => epl__( 'Surcharge Note' ),
              'default_value' => epl_get_regis_setting( 'epl_surcharge_note' ),
              'class' => 'epl_w400'
              ), */
            '_epl_surcharge_method' => array(
                'weight' => 5,
                'input_type' => 'select',
                'input_name' => '_epl_surcharge_method',
                'label' => epl__( 'Surcharge Method' ),
                'options' => array(
                    'add' => epl__( 'Add' ),
                //'include' => epl__( 'Includes' )
                ),
                //'empty_row' => true,
                'class' => '' ),
            '_epl_surcharge_amount' => array(
                'weight' => 10,
                'input_type' => 'text',
                'input_name' => '_epl_surcharge_amount',
                'label' => epl__( 'Amount' ),
                'class' => 'epl_w60',
                'data_type' => 'float',
                'default_value' => epl_nz( epl_get_regis_setting( 'epl_surcharge_amount' ), '0.00' )
            ),
            '_epl_surcharge_type' => array(
                'weight' => 15,
                'input_type' => 'select',
                'input_name' => '_epl_surcharge_type',
                'label' => epl__( 'Surcharge Type' ),
                'options' => array( 5 => epl__( 'Fixed' ), 10 => epl__( 'Percent' ) ),
                'default_value' => epl_get_regis_setting( 'epl_surcharge_before_discount' )
            ),
            '_epl_surcharge_before_discount' => array(
                'weight' => 20,
                'input_type' => 'select',
                'input_name' => '_epl_surcharge_before_discount',
                'label' => epl__( 'Apply surcharge' ),
                'options' => array(10=>epl__('Before discount'), 0=>epl__('After discount')),
                'default_value' => epl_get_regis_setting( 'epl_surcharge_type' )
            ),
            /*'_epl_surcharge_payment_method' => array(
                'weight' => 25,
                'input_type' => 'checkbox',
                'input_name' => '_epl_surcharge_payment_method',
                'label' => epl__( 'Payment method specific?' ),
                'options' => get_list_of_payment_profiles(),
                'help_text' => epl__( "Leave these un-checked to apply to all." ),
            )*/
);

$epl_fields['epl_surcharge_fields'] = apply_filters( 'epl_surcharge_fields', $epl_fields['epl_surcharge_fields'] );
uasort( $epl_fields['epl_surcharge_fields'], 'epl_sort_by_weight' );

$epl_fields['epl_time_option_fields'] = array( );

$epl_fields['epl_time_option_fields'] = apply_filters( 'epl_time_option_fields', $epl_fields['epl_time_option_fields'] );

$epl_fields['epl_time_fields'] =
        array(
            '_epl_start_time' => array(
                'weight' => 10,
                'input_type' => 'text',
                'input_name' => '_epl_start_time[]',
                'label' => epl__( 'Start Time' ),
                'class' => 'epl_w80 timepicker req',
                'parent_keys' => true ),
            '_epl_end_time' => array(
                'weight' => 15,
                'input_type' => 'text',
                'input_name' => '_epl_end_time[]',
                'label' => epl__( 'End Time' ),
                'class' => 'epl_w80 timepicker req' ),
            '_epl_time_hide' => array(
                'weight' => 100,
                'input_type' => 'select',
                'input_name' => '_epl_time_hide[]',
                'options' => epl_yes_no(),
                'label' => epl__( 'Hide' ),
            ),
            '_epl_time_note' => array(
                'weight' => 60,
                'input_type' => 'text',
                'input_name' => '_epl_time_note[]',
                'help_text' => '',
                'placeholder' => epl__( 'Note (optional)' ),
                'class' => 'epl_w100pct'
            ),
);
$epl_fields['epl_time_fields'] = apply_filters( 'epl_time_fields', $epl_fields['epl_time_fields'] );
uasort( $epl_fields['epl_time_fields'], 'epl_sort_by_weight' );


$epl_fields['epl_date_fields'] =
        array(
            '_epl_start_date' => array(
                'weight' => 5,
                'input_type' => 'text',
                'input_name' => '_epl_start_date[]',
                'label' => epl__( 'Start Date' ),
                'class' => 'epl_w100 datepicker req',
                'query' => 1,
                'data_type' => 'unix_time',
                'parent_keys' => true,
                '__func' => 'epl_admin_date_display' ),
            '_epl_end_date' => array(
                'weight' => 10,
                'input_type' => 'text',
                'input_name' => '_epl_end_date[]',
                'label' => epl__( 'End Date' ),
                'class' => 'epl_w100 datepicker req ',
                'query' => 1,
                'data_type' => 'unix_time',
                '__func' => 'epl_admin_date_display' ),
            '_epl_regis_start_date' => array(
                'weight' => 15,
                'input_type' => 'text',
                'input_name' => '_epl_regis_start_date[]',
                'label' => epl__( 'Regis. Starts On' ),
                'class' => 'epl_w100 datepicker req',
                'query' => 1,
                'data_type' => 'unix_time',
                '__func' => 'epl_admin_date_display' ),
            '_epl_regis_end_date' => array(
                'weight' => 20,
                'input_type' => 'text',
                'input_name' => '_epl_regis_end_date[]',
                'label' => epl__( 'Regis. Ends On' ),
                'class' => 'epl_w100 datepicker req',
                'query' => 1,
                'data_type' => 'unix_time',
                '__func' => 'epl_admin_date_display' ),
            '_epl_date_capacity' => array(
                'weight' => 25,
                'input_type' => 'text',
                'input_name' => '_epl_date_capacity[]',
                'label' => epl__( 'Capacity' ),
                'class' => 'epl_w40'
            ),
/*            '_epl_date_hide' => array(
                'weight' => 26,
                'input_type' => 'radio',
                'input_name' => '_epl_date_hide[]',
                'options' => epl_yes_no(),
                'label' => epl__( 'Hide?' ),
                'default_value' => 0,
            ),*/
            '_epl_date_note' => array(
                'weight' => 30,
                'input_type' => 'text',
                'input_name' => '_epl_date_note[]',
                'help_text' => '',
                'placeholder' => epl__( 'Note' ),
                'class' => 'epl_w400'
            ),
            '_epl_date_location' => array(
                'input_type' => 'select',
                'input_name' => '_epl_date_location[]',
                'empty_row' => true,
                'options' => get_list_of_available_locations()
            )
);

$epl_fields['epl_date_fields'] = apply_filters( 'epl_date_fields', $epl_fields['epl_date_fields'] );
uasort( $epl_fields['epl_date_fields'], 'epl_sort_by_weight' );

$epl_fields['epl_date_option_fields'] = array( );

$epl_fields['epl_date_option_fields'] = apply_filters( 'epl_date_option_fields', $epl_fields['epl_date_option_fields'] );

$epl_fields['epl_class_session_fields'] =
        array(
            '_epl_class_session_date' => array(
                'weight' => 5,
                'input_type' => 'text',
                'input_name' => '_epl_class_session_date[]',
                'label' => epl__( 'Session Date' ),
                'help_text' => '',
                'class' => 'epl_w100 datepicker req',
                '__func' => 'epl_admin_date_display',
                'data_type' => 'unix_time',
                'parent_keys' => true
            ), //epl_nz(epl_get_general_setting('epl_admin_date_format'), get_option('date_format'))),
            '_epl_class_session_name' => array(
                'weight' => 10,
                'input_type' => 'text',
                'input_name' => '_epl_class_session_name[]',
                'label' => epl__( 'Session Name' ),
                'help_text' => '',
                'class' => 'epl_w100' ),
            '_epl_class_session_note' => array(
                'weight' => 15,
                'input_type' => 'text',
                'input_name' => '_epl_class_session_note[]',
                'label' => epl__( 'Session Note' ),
                'help_text' => '',
                'class' => 'epl_w200 ' )
);
$epl_fields['epl_class_session_fields'] = apply_filters( 'epl_class_session_fields', $epl_fields['epl_class_session_fields'] );
uasort( $epl_fields['epl_class_session_fields'], 'epl_sort_by_weight' );

$epl_fields['epl_recurrence_fields'] =
        array(
            '_epl_rec_first_start_date' => array(
                'input_type' => 'text',
                'input_name' => '_epl_rec_first_start_date',
                'style' => 'width:100px;',
                'class' => ' datepicker req',
                'query' => 1,
                'data_type' => 'unix_time',
                '__func' => 'epl_admin_date_display' ),
            '_epl_rec_first_end_date' => array(
                'input_type' => 'text',
                'input_name' => '_epl_rec_first_end_date',
                'style' => 'width:100px;',
                'class' => ' datepicker req',
                'query' => 1,
                'data_type' => 'unix_time',
                '__func' => 'epl_admin_date_display' ),
            '_epl_rec_regis_start_date' => array(
                'input_type' => 'text',
                'input_name' => '_epl_rec_regis_start_date',
                'style' => 'width:100px;',
                'class' => ' datepicker req',
                'query' => 1,
                'data_type' => 'unix_time',
                '__func' => 'epl_admin_date_display' ),
            '_epl_rec_regis_start_days_before_start_date' => array(
                'input_type' => 'text',
                'input_name' => '_epl_rec_regis_start_days_before_start_date',
                'class' => 'epl_w40 req' ),
            '_epl_rec_regis_end_date' => array(
                'input_type' => 'text',
                'input_name' => '_epl_rec_regis_end_date',
                'style' => 'width:100px;',
                'class' => ' datepicker req',
                'query' => 1,
                'data_type' => 'unix_time',
                '__func' => 'epl_admin_date_display' ),
            '_epl_rec_regis_end_days_before_start_date' => array(
                'input_type' => 'text',
                'input_name' => '_epl_rec_regis_end_days_before_start_date',
                'class' => 'epl_w40 req',
                'help_text' => '',
                'default_value' => '' ),
            '_epl_recurrence_frequency' => array(
                'input_type' => 'select',
                'input_name' => '_epl_recurrence_frequency',
                'options' => array( 0 => 'Never', 'day' => 'Daily', 'week' => 'Weekly', 'month' => 'Monthly' ),
                'class' => 'req'
            ),
            '_epl_recurrence_interval' => array(
                'input_type' => 'select',
                'input_name' => '_epl_recurrence_interval',
                'options' => epl_make_array( 1, 30 ),
                'class' => 'req'
            ),
            '_epl_recurrence_end' => array(
                'input_type' => 'text',
                'input_name' => '_epl_recurrence_end',
                'label' => '',
                'help_text' => '',
                'class' => 'datepicker req epl_w100',
                'data_type' => 'unix_time',
                '__func' => 'epl_admin_date_display' ),
            '_epl_recurrence_weekdays' => array(
                'input_type' => 'checkbox',
                'input_name' => '_epl_recurrence_weekdays[]',
                'options' => array(
                    0 => epl__( 'Sun' ),
                    1 => epl__( 'Mon' ),
                    2 => epl__( 'Tue' ),
                    3 => epl__( 'Wed' ),
                    4 => epl__( 'Thu' ),
                    5 => epl__( 'Fri' ),
                    6 => epl__( 'Sat' )
                ),
                'default_checked' => 1,
                'display_inline' => true,
                'class' => 'req'
            ),
            '_epl_recurrence_repeat_by' => array(
                'input_type' => 'radio',
                'input_name' => '_epl_recurrence_repeat_by',
                'options' => array(
                    0 => epl__( 'Day of Month' ),
                ),
                'default_value' => 0,
                'help_text' => epl__( 'Coming soon, ability to select per week of the month (i.e. first, second, last week).' ),
            ),
            '_epl_recurrence_capacity' => array(
                'input_type' => 'text',
                'input_name' => '_epl_recurrence_capacity',
                'help_text' => '',
                'class' => 'epl_w50' ),
);
$epl_fields['epl_recurrence_fields'] = apply_filters( 'epl_recurrence_fields', $epl_fields['epl_recurrence_fields'] );

$epl_fields['epl_special_fields'] =
        array(
            '_epl_pricing_type' => array(
                'input_type' => 'select',
                'input_name' => '_epl_pricing_type',
                'options' => array(
                    0 => 'All the offered times have the same prices',
                    10 => 'Each time has special pricing' ),
                'label' => 'Event Type',
                'help_text' => 'Different Event types'
            )
);

$epl_fields['epl_regis_form_fields'] =
        array(
            '_epl_primary_regis_forms' => array(
                'input_type' => 'table_checkbox',
                'input_name' => '_epl_primary_regis_forms[]',
                'label' => epl__( 'Primary registrant forms' ),
                'options' => array( ),
                'auto_key' => true,
                'description' => epl__( 'Optional.  This is the form that you will use for collecting information from the person that is doing the registration.' )
            ),
            '_epl_addit_regis_forms' => array(
                'input_type' => 'table_checkbox',
                'input_name' => '_epl_addit_regis_forms[]',
                'label' => epl__( 'Forms for all attendees' ),
                'options' => array( ),
                'auto_key' => true,
                'help_text' => '<img src="' . EPL_FULL_URL . 'images/error.png" /> ' . epl__( 'This information will be collected from all the attendees.  If you do not need to collect individual information and only need the quantity, do not select any of these forms.' )
            ),
            /* '_epl_primary_regis_form_is_att' => array(
              'input_type' => 'select',
              'input_name' => '_epl_primary_regis_form_is_att[]',
              'label' => epl__( 'Count the ticket buyer form as an attendee form?' ),
              'options' => epl_yes_no(),
              'default_value' => 0,
              'description' => 'BETA! ' . epl__( 'If yes, a separate ticket buyer form will not be added for the primary registrant.' )
              ), */
            '_epl_addit_regis_form_counter_label' => array(
                'input_type' => 'text',
                'input_name' => '_epl_addit_regis_form_counter_label',
                'label' => epl__( 'Form Section Counter Label' ),
                'default_value' => epl__( 'Attendee' ),
                'help_text' => epl__( 'When additional forms are presented besides the Ticket Buyer form, this value will be presented before the counter.  For example, Attendee #1, Attendee #2...' ) ),
            '_epl_enable_form_to_form_copy' => array(
                'input_type' => 'select',
                'input_name' => '_epl_enable_form_to_form_copy',
                'options' => epl_yes_no(),
                'default_value' => 0,
                'label' => epl__( 'Enable form to form copy?' ),
                'default_value' => epl__( 'Attendee' ),
                'help_text' => epl__( 'This will give your users the ability to copy data from form to form during the registration.' ) )
);


$epl_fields['epl_other_settings_fields'] =
        array(
            '_epl_event_location' => array(
                'weight' => 5,
                'input_type' => 'select',
                'input_name' => '_epl_event_location',
                'options' => get_list_of_available_locations(),
                'empty_row' => true,
                'empty_options_msg' => epl__( 'No Locations found. Please go to Events Planner > Event Locations.' ),
                'label' => epl__( 'Event Location' ), ),
            '_epl_event_sublocation' => array(
                'weight' => 10,
                'input_type' => 'text',
                'input_name' => '_epl_event_sublocation',
                'label' => epl__( 'Room, suite...' ),
                'help_text' => epl__( 'Enter more specific information about the location.' ),
                'class' => 'epl_w300' ),
            '_epl_payment_choices' => array(
                'weight' => 15,
                'input_type' => 'table_checkbox',
                'input_name' => '_epl_payment_choices[]',
                'options' => get_list_of_payment_profiles(),
                'label' => epl__( 'Payment Choices' ),
                'empty_options_msg' => epl__( 'No Payment Profiles. Please go to Events Planner > Payment Profiles.' ),
                'class' => 'req',
                'default_value' => get_list_of_default_payment_profiles(),
                'auto_key' => true,
                'style' => '' ),
            '_epl_default_selected_payment' => array(
                'weight' => 17,
                'input_type' => 'select',
                'input_name' => '_epl_default_selected_payment',
                'options' => get_list_of_payment_profiles(),
                'label' => epl__( 'Default Selected Payment' ),
                'help_text' => epl__( 'This determines which payment method is automatically selected when the user visits the regsitration cart for the first time.' ),
                'empty_options_msg' => epl__( 'No Payment Profiles. Please go to Events Planner > Payment Profiles.' ),
                'class' => 'req',
            ),
            '_epl_event_organization' => array(
                'weight' => 20,
                'input_type' => 'select',
                'input_name' => '_epl_event_organization',
                'options' => get_list_of_orgs(),
                'empty_options_msg' => epl__( 'No Organizations found. Please go to Events Planner > Organizations.' ),
                'empty_row' => true,
                'label' => epl__( 'Organization hosting the event' ), ),
);

$epl_fields['epl_other_settings_fields'] = apply_filters( 'epl_other_settings_fields', $epl_fields['epl_other_settings_fields'] );
uasort( $epl_fields['epl_other_settings_fields'], 'epl_sort_by_weight' );


$epl_fields['epl_option_fields'] =
        array(
            '_epl_event_status' => array(
                'weight' => 5,
                'input_type' => 'select',
                'input_name' => '_epl_event_status',
                'label' => epl__( 'Status' ),
                'options' => array(
                    0 => epl__( 'Inactive' ),
                    1 => epl__( 'Active' ),
                    2 => epl__( 'Active (hidden)' ),
                    3 => epl__( 'Ongoing' ),
                    10 => epl__( 'Cancelled' )
                ),
                'class' => '' ),
            '_epl_regis_privilege' => array(
                'weight' => 10,
                'input_type' => 'select',
                'input_name' => '_epl_regis_privilege',
                'label' => epl__( 'Registration Availability' ),
                'options' => array(
                    0 => epl__( 'Open to public' ),
                    1 => epl__( 'Members Only' ),
                ),
                'class' => '' ),
            '_epl_registration_max' => array(
                'weight' => 15,
                'input_type' => 'text',
                'input_name' => '_epl_registration_max',
                'label' => epl__( 'Max. per registration' ),
                'class' => '' ),
);

$epl_fields['epl_option_fields'] = apply_filters( 'epl_option_fields', $epl_fields['epl_option_fields'] );
uasort( $epl_fields['epl_option_fields'], 'epl_sort_by_weight' );

$epl_fields['epl_display_option_fields'] =
        array(
            '_epl_display_regis_button' => array(
                'input_type' => 'select',
                'input_name' => '_epl_display_regis_button',
                'label' => epl__( 'Show Registration Button' ),
                'options' => epl_yes_no(),
                'default_value' => 10,
                'class' => '' ),
            '_epl_date_display_type' => array(
                'input_type' => 'select',
                'input_name' => '_epl_date_display_type',
                'label' => epl__( 'Date Display Type' ),
                'options' => array( 0 => epl__( 'None' ), 5 => epl__( 'Table' ), 10 => epl__( 'Calendar' ) ),
                'help_text' => epl__( 'Determines if the dates displayed on the event list are in table format or in small calendar format.' ),
                'default_value' => 5,
                'class' => '' ),
            '_epl_display_content' => array(
                'input_type' => 'select',
                'input_name' => '_epl_display_content',
                'label' => epl__( 'Content Display Type' ),
                'options' => array( 0 => epl__( 'None' ), 1 => epl__( 'Excerpt' ), 2 => epl__( 'Content' ) ),
                'help_text' => epl__( 'Determines which content to display in the event list.' ),
                'default_value' => 2,
                'class' => '' ),
            /* '_epl_display_time' => array(
              'input_type' => 'select',
              'input_name' => '_epl_display_time',
              'label' => epl__( 'Display times on the event list?' ),
              'options' => epl_yes_no(),
              'default_value' => 10,
              'class' => '' ), */
            '_epl_event_available_space_display' => array(
                'input_type' => 'select',
                'input_name' => '_epl_event_available_space_display',
                'label' => epl__( 'Display available spaces?' ),
                'help_text' => '',
                'options' => epl_yes_no()
            ),
            '_epl_event_detail_cart_display' => array(
                'input_type' => 'select',
                'input_name' => '_epl_event_detail_cart_display',
                'label' => epl__( 'Display Event Details in cart?' ),
                'help_text' => '',
                'options' => epl_yes_no()
            ),
            /* '_epl_display_org_info' => array(
              'input_type' => 'select',
              'input_name' => '_epl_display_org_info',
              'label' => epl__( 'Display Organization Info' ),
              'help_text' => epl__( 'Display the Organization info on the event list?' ) . ' DEPRECATED.  Use the empty row in the organization dropdown.',
              'options' => epl_yes_no(),
              'default_value' => 0,
              'class' => '' ), *///DEPRECATE AS OF 1.2
            '_epl_alt_admin_email' => array(
                'input_type' => 'text',
                'input_name' => '_epl_alt_admin_email',
                'label' => epl__( 'Alternate Notification Emails' ),
                'help_text' => epl__( 'Enter a comma separated list of email addresses that you would like to send the registration confirmation emails to.' ),
                'class' => 'epl_w400' ),
            '_epl_alt_regis_url' => array(
                'input_type' => 'text',
                'input_name' => '_epl_alt_regis_url',
                'label' => epl__( 'Alternate Registration URL' ),
                'help_text' => epl__( 'If you would like to have the register button point to an alternate registration page or form.' ),
                'class' => 'epl_w400' ),
            '_epl_cal_link_destination' => array(
                'input_type' => 'select',
                'input_name' => '_epl_cal_link_destination',
                'label' => epl__( 'Calendar/Widget link destination' ),
                'options' => array( 0 => epl__( 'Registration Page' ), 1 => epl__( 'Event Details' ) ),
                'default_value' => 0 ),
            '_epl_title_link_destination' => array(
                'input_type' => 'select',
                'input_name' => '_epl_title_link_destination',
                'label' => epl__( 'Event Title link destination' ),
                'options' => array( 0 => epl__( 'Event Details' ), 1 => epl__( 'Registration Page' ) ),
                'default_value' => 0 ),
);

$epl_fields['epl_display_option_fields'] = apply_filters( 'epl_display_option_fields', $epl_fields['epl_display_option_fields'] );
//uasort( $epl_fields['epl_display_option_fields'], 'epl_sort_by_weight' );


$epl_fields['epl_message_fields'] =
        array(
            '_epl_message_location' => array(
                'weight' => 10,
                'input_type' => 'select',
                'input_name' => '_epl_message_location[]',
                'label' => epl__( 'Message Location' ),
                'empty_row' => 1,
                'options' => array(
                    'epl_regis_all_message_top' => epl__( 'All registration pages - top' ),
                    'epl_regis_all_message_bottom' => epl__( 'All registration pages - bottom' ),
                    'epl_cart_top_message' => epl__( 'Cart - top' ),
                    'epl_cart_bottom_message' => epl__( 'Cart - bottom' ),
                    'epl_regis_form_top_message' => epl__( 'Registration form - top' ),
                    'epl_regis_form_bottom_message' => epl__( 'Registration form - bottom' ),
                    'epl_regis_complete_top_message' => epl__( 'Registration complete - top' ),
                    'epl_regis_complete_bottom_message' => epl__( 'Registration complete - bottom' ),
                //'epl_regis_confirm_email_top' => epl__('Confirmation Email - top'),
                ),
                'class' => 'req',
                'parent_keys' => true ),
            '_epl_message_type' => array(
                'weight' => 15,
                'input_type' => 'select',
                'input_name' => '_epl_message_type[]',
                'empty_row' => 1,
                'options' => array(
                    'epl_info_message' => epl__( 'Info' ),
                    'epl_success_message' => epl__( 'Success' ),
                    'epl_warning_message' => epl__( 'Warning' ),
                    'epl_error_message' => epl__( 'Error' ),
                ),
                'class' => '' ),
            '_epl_message' => array(
                'weight' => 20,
                'input_type' => 'textarea',
                'input_name' => '_epl_message[]',
                'label' => epl__( 'Min.' ),
                'class' => 'epl_w100pct mceeditor req',
                '_save_func' => 'wp_kses_post'
            )
);

$epl_fields['epl_message_fields'] = apply_filters( 'epl_message_fields', $epl_fields['epl_message_fields'] );
uasort( $epl_fields['epl_message_fields'], 'epl_sort_by_weight' );

$epl_fields['epl_report_fields'] =
        array(
            '_epl_report_column' => array(
                'weight' => 10,
                'input_type' => 'checkbox',
                'input_name' => '_epl_report_column[]',
            ),
            '_epl_report_column_width' => array(
                'weight' => 15,
                'input_type' => 'text',
                'input_name' => '_epl_report_column_width[]',
            ),
);
$epl_fields['epl_report_fields'] = apply_filters( 'epl_report_fields', $epl_fields['epl_report_fields'] );
uasort( $epl_fields['epl_report_fields'], 'epl_sort_by_weight' );

$epl_fields['epl_attendee_list_fields'] =
        array(
            '_epl_show_attendee_list_link' => array(
                'weight' => 10,
                'input_type' => 'select',
                'input_name' => '_epl_show_attendee_list_link',
                'label' => epl__( 'Show Attendee List Link?' ),
                'options' => epl_yes_no(),
                'class' => '' ),
            '_epl_show_attendee_list_button_location' => array(
                'weight' => 20,
                'input_type' => 'select',
                'input_name' => '_epl_show_attendee_list_button_location',
                'label' => epl__( 'Link Location' ),
                'options' => array( 'event_list' => epl__( 'Event List' ), 'thank_you_page' => epl__( 'Registration Confirmation Page' ), 'shortcode' => epl__( 'Shortcode' ) ),
                'class' => '' ),
            '_epl_show_attendee_list_template' => array(
                'weight' => 20,
                'input_type' => 'select',
                'input_name' => '_epl_show_attendee_list_template',
                'label' => epl__( 'Attendee List Template' ),
                'options' => array( 'attendee-list-1' => epl__( 'Template 1' ), 'attendee-list-2' => epl__( 'Template 2' ) ),
                'class' => '' ),
            '_epl_attendee_list_field' => array(
                'input_type' => 'checkbox',
                'input_name' => '_epl_attendee_list_field[]',
            ),
);
$epl_fields['epl_attendee_list_fields'] = apply_filters( 'epl_attendee_list_fields', $epl_fields['epl_attendee_list_fields'] );
uasort( $epl_fields['epl_attendee_list_fields'], 'epl_sort_by_weight' );


$epl_fields['epl_discount_fields'] = array(
    '_epl_allow_global_discounts' => array(
        'input_type' => 'select',
        'input_name' => '_epl_allow_global_discounts',
        'label' => epl__( 'Allow global discounts for this event?' ),
        'help_text' => epl__( 'This setting determines if global coupon codes or automatic discounts can be applied to this event.' ),
        'options' => epl_yes_no(),
        'default_value' => 10,
        'class' => '' ),
    '_epl_discount_method' => array(
        'input_type' => 'select',
        'input_name' => '_epl_discount_method[]',
        'options' => array( 5 => epl__( 'Code' ), 10 => epl__( 'Automatic' ) ),
        'label' => epl__( 'Discount Method' ),
        'class' => '',
        'parent_keys' => true ),
    '_epl_discount_code' => array(
        'input_type' => 'text',
        'input_name' => '_epl_discount_code[]',
        'label' => epl__( 'Discount Code' ),
        'class' => 'epl_w80' ),
    '_epl_discount_amount' => array(
        'input_type' => 'text',
        'input_name' => '_epl_discount_amount[]',
        'label' => epl__( 'Amount' ),
        'class' => 'epl_w70' ),
    '_epl_discount_type' => array(
        'input_type' => 'select',
        'input_name' => '_epl_discount_type[]',
        'label' => epl__( 'Discount Type' ),
        'options' => array( 5 => epl__( 'Fixed' ), 10 => epl__( 'Percent' ) ),
        'class' => '' ),
    '_epl_discount_max_usage' => array(
        'input_type' => 'text',
        'input_name' => '_epl_discount_max_usage[]',
        'label' => epl__( 'Max Use' ),
        'class' => 'epl_w50' ),
    '_epl_discount_end_date' => array(
        'input_type' => 'text',
        'input_name' => '_epl_discount_end_date[]',
        'label' => epl__( 'Until' ),
        'class' => 'datepicker epl_w100',
        'data_type' => 'unix_time',
        '__func' => 'epl_admin_date_display' ),
    '_epl_discount_description' => array(
        'input_type' => 'text',
        'input_name' => '_epl_discount_description[]',
        'placeholder' => epl__( 'Discount Description (optional)' ),
        'class' => 'epl_w100pct',
        'help_text' => epl__( 'This label will appear in the total section as the discount description.' ) ),
    '_epl_discount_active' => array(
        'input_type' => 'select',
        'input_name' => '_epl_discount_active[]',
        'label' => epl__( 'Active' ),
        'options' => epl_yes_no(),
        'default_value' => 0 ),
    '_epl_discount_condition' => array(
        'input_type' => 'select',
        'input_name' => '_epl_discount_condition[]',
        'options' => array( 0 => '', 5 => epl__( 'Total Amount' ), 6 => epl__( 'Total Quantity' ),8 => epl__( 'Number of Days' ) ),
        'class' => '' ),
    '_epl_discount_condition_logic' => array(
        'input_type' => 'select',
        'input_name' => '_epl_discount_condition_logic[]',
        'options' => array( '=' => '=', '>' => '>', '>=' => '>=', '<=' => '<=', 'between' => epl__( 'Between' ) ),
        'class' => '' ),
    '_epl_discount_condition_value' => array(
        'input_type' => 'text',
        'input_name' => '_epl_discount_condition_value[]',
        'class' => 'epl_w70' ),
    '_epl_discount_condition_value2' => array(
        'input_type' => 'text',
        'input_name' => '_epl_discount_condition_value2[]',
        'class' => 'epl_w70' ),
    '_epl_discount_target' => array(
        'input_type' => 'select',
        'input_name' => '_epl_discount_target[]',
        'options' => array( 5 => epl__( 'Total Amount' ) ),
        'class' => '' ),
    '_epl_discount_target_price_id' => array(
        'input_type' => 'select',
        'input_name' => '_epl_discount_target_price_id[]',
        'options' => array( ),
        'class' => '' ),
    '_epl_discount_forms' => array(
        'weight' => 60,
        'input_type' => 'checkbox',
        'input_name' => '_epl_discount_forms[]',
        'label' => epl__( 'Discount Specific Form' ),
        'options' => array( ),
        'second_key' => '[]'
    ),
    '_epl_discount_forms_per' => array(
        'weight' => 65,
        'input_type' => 'select',
        'input_name' => '_epl_discount_forms_per[]',
        'label' => epl__( 'Display the form ' ),
        'options' => array( 1 => epl__( 'For each Attendee' ), 2 => epl__( 'Only Once' ), 3 => epl__( 'Do not show any attendee forms' ) ),
    ),
);

$epl_fields['epl_discount_fields'] = apply_filters( 'epl_discount_fields', $epl_fields['epl_discount_fields'] );
//uasort( $epl_fields['epl_discount_fields'], 'epl_sort_by_weight' );

$epl_fields['epl_discount_rule_fields'] = array(
    '_epl_discount_trigger' => array(
        'input_type' => 'select',
        'input_name' => '_epl_discount_trigger[]',
        'options' => array( 0 => '', 5 => epl__( 'Total Amount' ), 6 => epl__( 'Total Quantity' ) ),
        'class' => '' ),
    '_epl_discount_amount' => array(
        'input_type' => 'text',
        'input_name' => '_epl_discount_amount[]',
        'label' => epl__( 'Amount' ),
        'class' => 'epl_w70' ),
    '_epl_discount_type' => array(
        'input_type' => 'select',
        'input_name' => '_epl_discount_type[]',
        'label' => epl__( 'Discount Type' ),
        'options' => array( 5 => epl__( 'Fixed' ), 10 => epl__( 'Percent' ) ),
        'class' => '' ),
    '_epl_discount_max_usage' => array(
        'input_type' => 'text',
        'input_name' => '_epl_discount_max_usage[]',
        'label' => epl__( 'Max Use' ),
        'class' => 'epl_w50' ),
    '_epl_discount_end_date' => array(
        'input_type' => 'text',
        'input_name' => '_epl_discount_end_date[]',
        'label' => epl__( 'Until' ),
        'class' => 'datepicker epl_w80',
        'data_type' => 'unix_time' ),
    '_epl_discount_active' => array(
        'input_type' => 'select',
        'input_name' => '_epl_discount_active[]',
        'label' => epl__( 'Until' ),
        'options' => epl_yes_no(),
        'default_value' => 0 ),
);
$epl_fields['epl_discount_rule_fields'] = apply_filters( 'epl_discount_rule_fields', $epl_fields['epl_discount_rule_fields'] );

$epl_fields['epl_waitlist_fields'] = array(
    '_epl_wailist_active' => array(
        'input_type' => 'select',
        'input_name' => '_epl_wailist_active',
        'label' => epl__( 'Enable Waitlist?' ),
        'options' => epl_yes_no(),
        'default_value' => 0 ),
    '_epl_waitlist_form' => array(
        'input_type' => 'select',
        'input_name' => '_epl_waitlist_form',
        'label' => epl__( 'Form to display' ),
        'options' => epl_get_list_of_available_forms(),
        'help_text' => epl__( 'The form that will be presented to the registrant.' ),
        'class' => '' ),
    '_epl_waitlist_max' => array(
        'input_type' => 'text',
        'input_name' => '_epl_waitlist_max',
        'label' => epl__( 'Max waitlist size' ),
        'help_text' => epl__( 'Leave empty if waitlist has no limit.' ),
        'class' => 'epl_w50' ),
    '_epl_waitlist_approved_regis_time_limit' => array(
        'input_type' => 'text',
        'input_name' => '_epl_waitlist_approved_regis_time_limit',
        'label' => epl__( 'Maximum response time' ),
        'help_text' => epl__( 'Amount of time (in hours) a user is given before the approved waitlist link expires.  Example, for 1 day, enter 24, for 3 hours and 30 minutes enter 3.5.' ),
        'class' => 'epl_w50',
    ),
    '_epl_waitlist_notification' => array(
        'input_type' => 'select',
        'input_name' => '_epl_waitlist_notification',
        'options' => get_list_of_available_notifications(),
        'label' => epl__( 'Waitlist confirmation email' ),
        'help_text' => epl__( 'Email that is sent to the users after they are placed on a waiting list.' ),
        'empty_options_msg' => epl__( 'No email messages found.  Please go to Events Planner > Notification Manager to create notifications.' )
    ),
    '_epl_waitlist_approved_notification' => array(
        'input_type' => 'select',
        'input_name' => '_epl_waitlist_approved_notification',
        'options' => get_list_of_available_notifications(),
        'label' => epl__( 'Waitlist approved email' ),
        'help_text' => epl__( 'Email that is sent to the users once the waitlist is approved.' ),
        'empty_options_msg' => epl__( 'No email messages found.  Please go to Events Planner > Notification Manager to create notifications.' )
    )
);

$epl_fields['epl_waitlist_fields'] = apply_filters( 'epl_waitlist_fields', $epl_fields['epl_waitlist_fields'] );
