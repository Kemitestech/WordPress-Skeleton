<?php

global $epl_fields;

$epl_fields['epl_org_fields'] =
        array(
            '_epl_org_address' => array(
                'weight' => 5,
                'input_type' => 'text',
                'input_name' => '_epl_org_address',
                'label' => epl__( 'Address' ),
                'class' => 'epl_w300' ),
            '_epl_org_address2' => array(
                'weight' => 10,
                'input_type' => 'text',
                'input_name' => '_epl_org_address2',
                'label' => epl__( 'Address 2' ),
                'class' => 'epl_w300' ),
            '_epl_org_city' => array(
                'weight' => 15,
                'input_type' => 'text',
                'input_name' => '_epl_org_city',
                'label' => epl__( 'Zip' ),
                'class' => 'epl_w300' ),
            '_epl_org_state' => array(
                'weight' => 20,
                'input_type' => 'text',
                'input_name' => '_epl_org_state',
                'label' => epl__( 'State' ),
                'class' => 'epl_w300' ),
            '_epl_org_city' => array(
                'weight' => 25,
                'input_type' => 'text',
                'input_name' => '_epl_org_city',
                'label' => epl__( 'City' ),
                'class' => 'epl_w300' ),
            '_epl_org_zip' => array(
                'weight' => 30,
                'input_type' => 'text',
                'input_name' => '_epl_org_zip',
                'label' => epl__( 'Zip Code' ),
                'class' => 'epl_w300' ),
            '_epl_org_phone' => array(
                'weight' => 35,
                'input_type' => 'text',
                'input_name' => '_epl_org_phone',
                'label' => epl__( 'Phone' ),
                'class' => 'epl_w300' ),
            '_epl_org_email' => array(
                'weight' => 40,
                'input_type' => 'text',
                'input_name' => '_epl_org_email',
                'label' => epl__( 'Email' ),
                'class' => 'epl_w300' ),
            '_epl_org_website' => array(
                'weight' => 45,
                'input_type' => 'text',
                'input_name' => '_epl_org_website',
                'label' => epl__( 'Website' ),
                'class' => 'epl_w300' ),
);

$epl_fields['epl_org_fields'] = apply_filters( 'epl_org_fields', $epl_fields['epl_org_fields'] );
uasort( $epl_fields['epl_org_fields'], 'epl_sort_by_weight' );