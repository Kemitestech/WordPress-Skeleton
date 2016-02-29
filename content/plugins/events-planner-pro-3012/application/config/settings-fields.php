<?php

$default_button_css = "a.epl_button, input.epl_button,.epl_button_small  {
    background-image:none !important;
    backround-repeat:no-repeat;
    border-radius: 0 !important;
    display: inline-block;
    margin: 0;
    padding: 4px 14px !important;
    border: 1px solid transparent;
    color: #ffffff !important;
    vertical-align: middle;
    text-align: center;
    font-weight: normal !important;
    font-size: 13px !important;
    line-height: 1.5384615384615385 !important;
    cursor: pointer;
    outline: none;
    background-color: #3bafda !important;
    border-color: #3bafda !important;
    -webkit-transition: all 0.15s ease-in-out;
    -moz-transition: all 0.15s ease-in-out;
    -o-transition: all 0.15s ease-in-out;
    transition: all 0.15s ease-in-out;
    -webkit-box-sizing: border-box;
    -moz-box-sizing: border-box;
    box-sizing: border-box;
    text-decoration:none;
}

.epl_button_small {
    padding: 2px 15px !important;
    font-size: 0.8em !important;
    margin-top:10px !important;
}
.epl_button:hover, .epl_button_small:hover, input[type=submit].epl_button_small:hover {
    background-color: #66c1e2 !important;
    border-color: #66c1e2 !important;
}
.epl_button:active, .epl_button_small:active, input[type=submit].epl_button_small:active {
    position:relative;
    top:1px;
}
";

global $epl_fields;

$epl_fields['epl_general_options'] = array(
    'epl_sort_event_list_by' => array(
        'input_type' => 'select',
        'input_name' => 'epl_sort_event_list_by',
        'label' => epl__( 'Sort Events By' ),
        'options' => array( 'date' => epl__( 'Date Published' ), 'title' => epl__( 'Event Name' ), 'start_date' => epl__( 'Event Start Date' ) ),
        'default_value' => 'date' ),
    'epl_sort_event_list_order' => array(
        'input_type' => 'select',
        'input_name' => 'epl_sort_event_list_order',
        'label' => epl__( 'Sort Order' ),
        'options' => array( 'ASC' => epl__( 'Ascending' ), 'DESC' => epl__( 'Descending' ) ),
        'default_value' => 10 ),
    'epl_currency_code' => array(
        'input_type' => 'select',
        'input_name' => 'epl_currency_code',
        'label' => epl__( 'Currency Code' ),
        'help_text' => epl__( 'This will be used in payment gateways. ' ),
        'options' => array( 'AUD' => 'AUD', 'CAD' => 'CAD', 'CHF' => 'CHF (Switzerland Franc)', 'EUR' => 'EUR', 'GBP' => 'GBP', 'HKD' => 'HKD', 'JPY' => 'JPY', 'NOK' => 'NOK', 'NZD' => 'NZD', 'PHP' => 'PHP', 'USD' => 'USD', 'SAR' => 'SAR', 'SGD' => 'SGD (Singapore Dollar)', 'SEK' => 'SEK' )
    ),
    'epl_currency_symbol' => array(
        'input_type' => 'text',
        'input_name' => 'epl_currency_symbol',
        'label' => epl__( 'Currency Symbol' ),
        'help_text' => epl__( "This will appear next to all the currency figures on the website.  Ex. $, USD... " ),
        'class' => 'epl_w50' ),
    'epl_currency_symbol_location' => array(
        'input_type' => 'select',
        'input_name' => 'epl_currency_symbol_location',
        'label' => epl__( 'Currency Symbol Location' ),
        'options' => array( 'b' => epl__( 'Before' ), 'a' => epl__( 'After' ) ),
        'default_value' => 'b',
    ),
    'epl_currency_display_format' => array(
        'input_type' => 'select',
        'input_name' => 'epl_currency_display_format',
        'options' => array( 1 => '1,234.56', 2 => '1,234', 3 => '1234', 4 => '1234.56', 5 => '1 234,00', 6 => '1 234.00' ),
        'default_value' => 1,
        'label' => epl__( 'Currency display format' ),
        'help_text' => epl__( 'This determines how your currency is displayed.  Ex. 1,234.56 or 1,200 or 1200.  This is only used for display purposes.' )
    ),
    'epl_shortcode_page_id' => array(
        'input_type' => 'select',
        'input_name' => 'epl_shortcode_page_id',
        'label' => epl__( 'Shortcode Page' ),
        'help_text' => epl__( 'Required.  Please select the page that contains the [events_planner] shortcode. ' ),
        'options' => epl_sortcode_pages()
    ),
    'epl_admin_date_format' => array(
        'input_type' => 'select',
        'input_name' => 'epl_admin_date_format',
        'options' => array( 'm/d/Y' => 'm/d/Y', 'd/m/Y' => 'd/m/Y', 'Y-m-d' => 'Y-m-d' ),
        'default_value' => 'm/d/Y',
        'label' => epl__( 'Admin Date Format' ),
        'help_text' => epl__( 'This date format is used for displaying dates on the Admin screens of Events Planner.  On the front end, the WordPress General settings date is used.' )
    ),
    'epl_disable_defult_css' => array(
        'input_type' => 'select',
        'input_name' => 'epl_disable_defult_css',
        'options' => epl_yes_no(),
        'default_value' => 0,
        'label' => epl__( 'Disable Default CSS?' ),
        'help_text' => epl__( "ADVANCED: If disabled, the default css file (events-planner > css > events-planner-style1.css) will not be loaded.  <br />This way, you can copy the contents of that file into your theme's style.css and modify any way you would like." )
    ),
    'epl_button_css' => array(
        'input_type' => 'textarea',
        'input_name' => 'epl_button_css',
        'options' => epl_yes_no(),
        'style' => 'width:600px;height:250px;',
        'default_value' => $default_button_css,
        'label' => epl__( 'Custom CSS' ),
        'help_text' => epl__( "You can add custom CSS in this box to override front end CSS rules.  This data is stored in the database and is safe from plugin updates." )
    )
);

$epl_fields['epl_general_options'] = apply_filters( 'epl_general_options_fields', $epl_fields['epl_general_options'] );

$epl_fields['epl_registration_options'] = array(
    'epl_regis_id_length' => array(
        'weight' => 10,
        'input_type' => 'select',
        'input_name' => 'epl_regis_id_length',
        'label' => epl__( 'Registration ID length?' ),
        'help_text' => epl__( 'This will be an alphanumeric string.' ),
        'options' => epl_make_array( 10, 26 ),
        'default_value' => 10 ),
    'epl_regis_enable_ssl' => array(
        'weight' => 15,
        'input_type' => 'select',
        'input_name' => 'epl_regis_enable_ssl',
        'label' => epl__( 'Enable SSL for registrations?' ),
        'help_text' => sprintf( epl__( '%sIf you plan on accepting Credit Cards on your website, this must be set to Yes for PCI Compliance.  You need to have a SSL Certificate installed for this domain.%s' ), '<span class=""><strong>', '</strong></span>' ),
        'options' => epl_yes_no(),
        'default_value' => 0 ),
    'epl_regis_add_url_token' => array(
        'weight' => 11,
        'input_type' => 'select',
        'input_name' => 'epl_regis_add_url_token',
        'label' => epl__( 'Add security token to registration confirmation url?' ),
        'description' => epl__( "This will add a token to the registration url so that others can't accidentally stumble upon the registration details.  The registration confirmation url will change from ...registration/ASDFREWTR to ...registration/ASDFREWTR?epl_token=adlkfalsdfjl0ufj0923r." ),
        'options' => epl_yes_no(),
        'default_value' => 10 ),
    'epl_surcharge_section' => array(
        'weight' => 19,
        'input_type' => 'section',
        'label' => '',
        'class' => 'epl_font_555 epl_font_bold',
        'content' => epl__( 'Default Surcharge Settings' ),
        'help_text' => epl__( 'You can override these settings for each event.' ),
    ),
    'epl_surcharge_global' => array(
        'weight' => 21,
        'input_type' => 'select',
        'input_name' => 'epl_surcharge_global',
        'label' => epl__( 'Apply Surcharge To' ),
        'options' => array( 5 => epl__( 'All current and future events' ), 10 => epl__( 'Let me choose for each event' ) ),
        'default_value' => 5
    ),
    'epl_surcharge_label' => array(
        'weight' => 23,
        'input_type' => 'text',
        'input_name' => 'epl_surcharge_label',
        'label' => epl__( 'Surcharge Label (default)' ),
        'help_text' => epl__( 'Label that will be displayed to the user.' ),
        'default_value' => epl__( 'Surcharge' ) ),
    'epl_surcharge_amount' => array(
        'weight' => 25,
        'input_type' => 'text',
        'input_name' => 'epl_surcharge_amount',
        'label' => epl__( 'Surcharge Amount (default)' ),
        'help_text' => epl__( 'This surcharge amount will apply by default to each event.  You will have the option to adjust this number for each event.' ),
        'data_type' => 'float',
        'default_value' => '0.00' ),
    'epl_surcharge_type' => array(
        'weight' => 30,
        'input_type' => 'select',
        'input_name' => 'epl_surcharge_type',
        'label' => epl__( 'Surcharge Type (default)' ),
        'options' => array( 5 => epl__( 'Fixed' ), 10 => epl__( 'Percent' ) ),
        'default_value' => 10
    ),
    'epl_surcharge_before_discount' => array(
        'weight' => 35,
        'input_type' => 'select',
        'input_name' => 'epl_surcharge_before_discount',
        'label' => epl__( 'Apply surcharge (default)' ),
        'options' => array( 10 => epl__( 'Before discount' ), 0 => epl__( 'After discount' ) ),
        'default_value' => 10
    ),
    'epl_conv_section' => array(
        'weight' => 39,
        'input_type' => 'section',
        'label' => '',
        'class' => 'epl_font_555 epl_font_bold',
        'content' => '',
    ),
    'epl_tracking_code' => array(
        'weight' => 45,
        'input_type' => 'textarea',
        'input_name' => 'epl_tracking_code',
        'label' => epl__( 'Conversion Tracking Code' ),
        'help_text' => epl__( 'You can paste javascript code in this box if you would like to track registration conversions (e.g. from Google Adwords).  This code will be included in the final page when the registration is succressfully completed, and only once.  The code must be wrapped in "script" tags.' ),
        'style' => 'width:90%;height:120px;',
        '_save_func' => 'wp_kses_post'
    ),
    'epl_enable_admin_override' => array(
        'weight' => 55,
        'input_type' => 'select',
        'input_name' => 'epl_enable_admin_override',
        'label' => epl__( 'Enable Admin Override?' ),
        'options' => epl_yes_no(),
        'default_value' => 0,
        'help_text' => epl__( 'When an admin user goes through the registration on the front end of the website, the user can override the total amount.' ),
    ),
    'epl_enable_admin_override_cal' => array(
        'weight' => 57,
        'input_type' => 'select',
        'input_name' => 'epl_enable_admin_override_cal',
        'label' => epl__( 'Enable Admin Override Calendar?' ),
        'options' => epl_yes_no(),
        'default_value' => 0,
        'help_text' => epl__( 'When an admin user goes through the registration on the front end of the website, a date selector calendar will be visible instead of the list of dates.' ),
    ),
    'epl_enable_donation' => array(
        'weight' => 60,
        'input_type' => 'select',
        'input_name' => 'epl_enable_donation',
        'label' => epl__( 'Enable Donations?' ),
        'options' => epl_yes_no(),
        'default_value' => 0,
        'help_text' => epl__( 'This will allow the registrants to make donations during the registration.' ),
    ),
    'epl_show_event_details_on_conf' => array(
        'weight' => 62,
        'input_type' => 'select',
        'input_name' => 'epl_show_event_details_on_conf',
        'label' => epl__( 'Display event details on confirmation page?' ),
        'options' => epl_yes_no(),
        'default_value' => 0,
    ),
    'epl_send_customer_confirm_message_to' => array(
        'weight' => 64,
        'input_type' => 'select',
        'input_name' => 'epl_send_customer_confirm_message_to',
        'options' => array( 1 => epl__( 'Only the primary registrant' ), 2 => epl__( 'All email addresses in all forms' ) ),
        'label' => epl__( 'Send confirmtion message to' ),
        'default_value' => 1
    ),
);



$epl_fields['epl_registration_options'] = apply_filters( 'epl_registration_options_fields', $epl_fields['epl_registration_options'] );
uasort( $epl_fields['epl_registration_options'], 'epl_sort_by_weight' );


$epl_fields['epl_event_options'] = array(
    'epl_register_button_text' => array(
        'input_type' => 'text',
        'input_name' => 'epl_register_button_text',
        'label' => epl__( 'Registration Button Text ' ),
        'help_text' => epl__( 'Register, Sign Up, Buy Tickets...' ),
    ),
    'epl_date_location' => array(
        'input_type' => 'select',
        'input_name' => 'epl_date_location',
        'options' => epl_yes_no(),
        'label' => epl__( 'Enable Date Specific Location' ),
        'help_text' => epl__( 'This setting will let you indicate a specific location for each date.' ),
    ),
    'epl_date_note_enable' => array(
        'input_type' => 'select',
        'input_name' => 'epl_date_note_enable',
        'options' => epl_yes_no(),
        'label' => epl__( 'Enable Date Specific Notes' ),
        'help_text' => epl__( 'This setting will let you indicate a specific note for each date.' ),
    ),
    'epl_default_notification_email' => array(
        'input_type' => 'text',
        'input_name' => 'epl_default_notification_email',
        'label' => epl__( 'Default Notification Email' ),
        'help_text' => epl__( 'Registration Notifications will be sent to this email, unless Alternate Notification Emails are indicated for the an event.' ),
        'default_value' => get_bloginfo( 'admin_email' )
    ),
    'epl_admin_event_list_version' => array(
        'input_type' => 'select',
        'input_name' => 'epl_admin_event_list_version',
        'options' => array( 1 => epl__( 'Version' ) . " 1", 2 => epl__( 'Version' ) . " 2" ),
        'label' => epl__( 'Manage Event page version' ),
        'default_value' => 2
    ),
);



$epl_fields['epl_event_options'] = apply_filters( 'epl_event_options_fields', $epl_fields['epl_event_options'] );

$home_url = trailingslashit( home_url() );
$epl_fields['epl_fullcalendar_options'] = array(
    'epl_fullcalendar_theme' => array(
        'input_type' => 'select',
        'input_name' => 'epl_fullcalendar_theme',
        'options' => array(
            '' => epl__( 'Default' ),
            'base' => 'Base',
            'black-tie' => 'Black Tie',
            'blitzer' => 'Blitzer',
            'cupertino' => 'Cupertino',
            'dark-hive' => 'Dark Hive',
            'dot-luv' => 'Dot Luv',
            'eggplant' => 'Eggplant',
            'excite-bike' => 'Excite Bike',
            'hot-sneaks' => 'Hot Sneaks',
            'humanity' => 'Humanity',
            'le-frog' => 'Le Frog',
            'mint-choc' => 'Mint Choc',
            'overcast' => 'Overcast',
            'pepper-grinder' => 'Pepper Grinder',
            'redmond' => 'Redmond',
            'smoothness' => 'Smoothness',
            'south-street' => 'South Street',
            'start' => 'Start',
            'sunny' => 'Sunny',
            'swanky-purse' => 'Swantky Purse',
            'trontastic' => 'Trontastic',
            'ui-darkness' => 'UI Darkness',
            'ui-lightness' => 'UI Lightness',
            'vader' => 'Vader',
        ),
        'default_value' => '',
        'label' => epl__( 'FullCalendar Theme' ),
        'description' => epl__( "These styles are loaded from the Google CDN." ) . ' ' . epl_anchor( 'http://jqueryui.com/themeroller/', 'jQuery UI Styles' )
    ),
    'epl_fullcalendar_enable_tooltip' => array(
        'input_type' => 'select',
        'input_name' => 'epl_fullcalendar_enable_tooltip',
        'options' => epl_yes_no(),
        'label' => epl__( 'Enable Tooltip' ),
        'default_value' => 10
    ),
    'epl_fullcalendar_show_legend' => array(
        'input_type' => 'select',
        'input_name' => 'epl_fullcalendar_show_legend',
        'options' => array( 0 => epl__( 'No' ), 1 => epl__( 'Above Calendar' ), 10 => epl__( 'Below Calendar' ) ),
        'label' => epl__( 'Display Category Legends' ),
        'default_value' => 10
    ),
    'epl_fullcalendar_show_att_count' => array(
        'input_type' => 'select',
        'input_name' => 'epl_fullcalendar_show_att_count',
        'options' => array( 0 => epl__( 'No' ), 1 => epl__( 'Admin Only' ), 2 => epl__( 'Logged-in Users' ), 3 => epl__( 'All Users' ) ),
        'label' => epl__( 'Display Attendee Counts on Calendar?' ),
        'default_value' => 0
    ),
    'epl_fullcalendar_enable_cache' => array(
        'input_type' => 'select',
        'input_name' => 'epl_fullcalendar_enable_cache',
        'options' => epl_yes_no(),
        'label' => epl__( 'Enable Caching?' ),
        'help_text' => epl__( 'When this is enabled, the calendar generation query is refreshed every 4 hours or when an event is updated.  Caching will make the calendar load much faster.' ),
        'default_value' => 10
    ),
    'epl_fullcalendar_iCal' => array(
        'input_type' => 'section',
        'label' => '',
        'class' => '',
        'style' => 'font-size:12px;',
        'content' => epl__( "iCal Feeds" ) .
        "<div style='font-size:12px'>" . epl__( 'Regular iCal Feed' ) . ': ' .
        epl_anchor( add_query_arg( array( 'epl_action' => 'ical' ), $home_url ), add_query_arg( array( 'epl_action' => 'ical' ), $home_url ), '_blank' ) .
        "</div> " .
        "<div style='font-size:12px'>" . epl__( 'iCal Feed With Attendee Counts' ) . ': ' .
        epl_anchor( add_query_arg( array( 'epl_action' => 'ical', 'ical_token' => md5( NONCE_KEY ) ), $home_url ), add_query_arg( array( 'epl_action' => 'ical', 'ical_token' => md5( NONCE_KEY ) ), $home_url ), '_blank' ) .
        "</div> "
    ),
    'epl_fullcalendar_tax_bcg_color' => array(
        'input_type' => 'text',
        'input_name' => 'epl_fullcalendar_tax_bcg_color[]',
        'label' => epl__( 'Event Taxonomy color' ),
    ),
    'epl_fullcalendar_tax_font_color' => array(
        'input_type' => 'text',
        'input_name' => 'epl_fullcalendar_tax_font_color[]',
        'label' => epl__( 'Event Taxonomy font color' ),
    )
);



$epl_fields['epl_fullcalendar_options'] = apply_filters( 'epl_fullcalendar_options_fields', $epl_fields['epl_fullcalendar_options'] );

do_action( 'epl_settings_fields' );
