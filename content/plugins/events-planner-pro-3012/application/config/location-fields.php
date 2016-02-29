<?php

global $epl_fields;
$epl_fields['epl_location_fields'] =
        array(
            '_epl_location_address' => array(
                'weight'=>5,
                'input_type' => 'text',
                'input_name' => '_epl_location_address',
                'label' => epl__( 'Address' ),
                'class' => 'epl_w300 req' ),
            '_epl_location_address2' => array(
                'weight'=>10,
                'input_type' => 'text',
                'input_name' => '_epl_location_address2',
                'label' => epl__( 'Address 2' ),
                'description' => '',
                'class' => 'epl_w300' ),
            '_epl_location_city' => array(
                'weight'=>15,
                'input_type' => 'text',
                'input_name' => '_epl_location_city',
                'label' => epl__( 'City' ),
                'class' => 'req'),
            '_epl_location_state' => array(
                'weight'=>20,
                'input_type' => 'text',
                'input_name' => '_epl_location_state',
                'label' => epl__( 'State' ),
                'class' => 'req'),
            '_epl_location_country' => array(
                'weight'=>22,
                'input_type' => 'text',
                'input_name' => '_epl_location_country',
                'label' => epl__( 'Country' ),
                'class' => 'req'),
            '_epl_location_zip' => array(
                'weight'=>25,
                'input_type' => 'text',
                'input_name' => '_epl_location_zip',
                'label' => epl__( 'Zip' ) ),
            '_epl_location_phone' => array(
                'weight'=>30,
                'input_type' => 'text',
                'input_name' => '_epl_location_phone',
                'label' => epl__( 'Phone' ) ),
            '_epl_location_email' => array(
                'weight'=>35,
                'input_type' => 'text',
                'input_name' => '_epl_location_email',
                'label' => epl__( 'Email' ),
                'class' => 'epl_w300' ),
            '_epl_location_url' => array(
                'weight'=>40,
                'input_type' => 'text',
                'input_name' => '_epl_location_url',
                'label' => epl__( 'Website' ),
                'description' => epl__( 'Please enter http://...' ),
                'class' => 'epl_w300' ),
            '_epl_location_display_map_link' => array(
                'weight'=>45,
                'input_type' => 'select',
                'input_name' => '_epl_location_display_map_link',
                'options' => epl_yes_no(),
                'label' => epl__( 'Display map link icon?' ),
                'description' => epl__( 'If yes, a map icon will be displayed on the event list.' ),
                'default_value' => 10
                ),
            '_epl_location_long' => array(
                'weight'=>50,
                'input_type' => 'text',
                'input_name' => '_epl_location_long',
                'label' => epl__( 'Longitude' ),
                'description' => epl__( 'This information is automatically obtained from Google when this record is saved.  Address, City, State are required.' ),
                'class' => 'epl_w300',
                'data_type' => 'float'),

            '_epl_location_lat' => array(
                'weight'=>55,
                'input_type' => 'text',
                'input_name' => '_epl_location_lat',
                'label' => epl__( 'Latitude' ),
                'description' => epl__( 'This information is automatically obtained from Google when this record is saved.  Address, City, State are required.' ),
                'class' => 'epl_w300',
                'data_type' => 'float'),
            '_epl_location_notes' => array(
                'weight'=>60,
                'input_type' => 'textarea',
                'input_name' => '_epl_location_notes',
                'label' => epl__( 'Notes' ),
            )
);

$epl_fields['epl_location_fields'] = apply_filters('epl_location_fields', $epl_fields['epl_location_fields']);
uasort( $epl_fields['epl_location_fields'], 'epl_sort_by_weight' );