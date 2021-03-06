<?php
/*
 * Do not edit anything here.
 */

global $libraries, $system_libraries, $helpers, $epl_fields, $_long_name, $_short_name, $epl_help_links;

$_long_name = "events_planner";
$_short_name = "epl";


$epl_help_links = array(

    10 => array('s'=> 'epl_event_type'),

);

//helper files
$helpers = apply_filters( $_short_name . '_helpers', array( 'common-helper', 'template-tags', 'pr-helper', 'date-helper' ) );

// Libraries that are autoloaded

$libraries = array( 'epl-table', 'epl-util', 'epl-calendar' );
$libraries = apply_filters( $_short_name . '_libraries', $libraries );


/*
 * These are the list of valid controllers that the system recognizes.  If someone passes
 * a fake controller name from the front or admin, the system will not do anything
 */
global $valid_controllers;
$valid_controllers = array(
    $_short_name . '_event' => array( 'object_name' => 'epl', 'class' => 'epl-event-manager', 'location' => 'epl-event-manager' ),
    $_short_name . '_event_manager' => array( 'object_name' => 'epl', 'class' => 'epl-event-manager', 'location' => 'epl-event-manager' ),
    $_short_name . '_settings' => array( 'object_name' => 'settings', 'class' => 'epl-settings-manager', 'location' => 'epl-settings-manager' ),
    $_short_name . '_editor' => array( 'object_name' => 'editor', 'class' => 'epl-editor', 'location' => 'epl-editor' ),
    $_short_name . '_location' => array( 'object_name' => 'locations', 'class' => 'epl-location-manager', 'location' => 'epl-location-manager' ),
    $_short_name . '_registration' => array( 'object_name' => 'registrations', 'class' => 'epl-registration-manager', 'location' => 'epl-registration-manager' ),
    $_short_name . '_notification' => array( 'object_name' => 'notification', 'class' => 'epl-notification-manager', 'location' => 'epl-notification-manager' ),
    $_short_name . '_discount' => array( 'object_name' => 'discount', 'class' => 'epl-discount-manager', 'location' => 'epl-discount-manager' ),
    $_short_name . '_form_manager' => array( 'object_name' => 'forms', 'class' => 'epl-form-manager', 'location' => 'epl-form-manager' ),
    $_short_name . '_org' => array( 'object_name' => 'forms', 'class' => 'epl-org-manager', 'location' => 'epl-org-manager' ),
    $_short_name . '_pay_profile' => array('class' => 'epl-pay-profile-manager', 'location' => 'epl-pay-profile-manager' ),
    $_short_name . '_global_discount' => array('class' => 'epl-global-discount-manager', 'location' => 'epl-global-discount-manager' ),
    $_short_name . '_front' => array( 'object_name' => 'front', 'class' => 'epl-front', 'location' => 'epl-front' ),
    $_short_name . '_registration_front' => array( 'object_name' => 'front', 'class' => 'epl_registration_front', 'location' => 'epl-registration-front' ),
    $_short_name . '_help' => array( 'object_name' => 'help', 'class' => 'epl-help-manager', 'location' => 'epl-help-manager' ),
    $_short_name . '_user_regis_manager' => array( 'object_name' => 'user_registration', 'class' => 'epl-user-regis-manager', 'location' => 'epl-user-regis-manager' ),
    $_short_name . '_user_self_pages_manager' => array( 'object_name' => 'user_registration', 'class' => 'epl-user-self-pages-manager', 'location' => 'epl-user-self-pages-manager' ),
    $_short_name . '_report_manager' => array( 'object_name' => 'report', 'class' => 'epl-report-manager', 'location' => 'epl-report-manager' ),
    $_short_name . '_dashboard_manager' => array( 'object_name' => 'dashboard', 'class' => 'epl-dashboard-manager', 'location' => 'epl-dashboard-manager' )
);

$valid_controllers = apply_filters( $_short_name . '_valid_controllers', $valid_controllers );

global $table_template;

$table_template = array(
    'table_open' => '<table border="0" cellpadding="4" cellspacing="0" class="form-table">',
    'heading_row_start' => '<tr>',
    'heading_row_end' => '</tr>',
    'heading_cell_start' => '<th>',
    'heading_cell_end' => '</th>',
    'row_start' => '<tr>',
    'row_end' => '</tr>',
    'cell_start' => '<td>',
    'cell_end' => '</td>',
    'row_alt_start' => '<tr>',
    'row_alt_end' => '</tr>',
    'cell_alt_start' => '<td>',
    'cell_alt_end' => '</td>',
    'table_close' => '</table>'
);


global $cal_template1, $cal_template2;

$prefs['template'] = '
    {table_open}<table class="epl_widget_calendar">{/table_open}

   {heading_row_start}<tr>{/heading_row_start}

   {heading_previous_cell}<th><a href="{previous_url}" class="epl_next_prev_link">&lt;&lt;</a></th>{/heading_previous_cell}
   {heading_title_cell}<th colspan="{colspan}">{heading}</th>{/heading_title_cell}
   {heading_next_cell}<th><a href="{next_url}"  class="epl_next_prev_link">&gt;&gt;</a></th>{/heading_next_cell}

   {heading_row_end}</tr>{/heading_row_end}

    {week_day_cell}<th class="day_header">{week_day}</th>{/week_day_cell}

    {cal_cell_content}<div class="widget_has_data day_listing day_listing_content">{day}</div>{/cal_cell_content}
    {cal_cell_content_today}<div class="today widget_has_data day_listing">{day}</div>{/cal_cell_content_today}

    {cal_cell_no_content}<div class= "day_listing ">{day}</div>{/cal_cell_no_content}
    {cal_cell_no_content_today}<div class="today"{day}</div>{/cal_cell_no_content_today}
';