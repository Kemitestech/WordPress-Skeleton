<?php

global $epl_fields;


$epl_fields['epl_global_discount_type'] =
        array(
            '_epl_global_discount_type' => array(
                'input_type' => 'select',
                'input_name' => '_epl_global_discount_type',
                'label' => epl__( 'Discount Type' ),
                'id' => 'epl_global_discount_type',
                //'style' => 'display:none',
                'empty_row' => true,
                'options' => array(
                    'global' => epl__( 'Internal' ),
                    
                ),
            )
);

$epl_fields['epl_global_discount_type'] = apply_filters( 'epl_global_discount_type', $epl_fields['epl_global_discount_type'] );

$epl_fields['epl_global_discount_fields'] = array(
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
    '_epl_discount_cat_include' => array(
        'input_type' => 'checkbox',
        'input_name' => '_epl_discount_cat_include[]',
        'label' => epl__( 'Include Only Categories' ),
        'options' => epl_term_list(),
        'auto_key' => false,
        'second_key' => '[]',
        'display_inline' => true ),
    /*'_epl_discount_pay_specific' => array(
        'input_type' => 'checkbox',
        'input_name' => '_epl_discount_pay_specific[]',
        'label' => epl__( 'Include Only Pay Profile' ),
        'options' => get_list_of_payment_profiles(),
        'auto_key' => false,
        'second_key' => '[]',
        'display_inline' => true ),*/
    '_epl_discount_condition' => array(
        'input_type' => 'select',
        'input_name' => '_epl_discount_condition[]',
        'options' => array( 0 => '', 5 => epl__( 'Total Amount' ), 6 => epl__( 'Total Quantity' ), 7 => epl__( 'Number of Events' ) ),
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

$epl_fields['epl_global_discount_fields'] = apply_filters( 'epl_global_discount_fields', $epl_fields['epl_global_discount_fields'] );
//uasort( $epl_fields['epl_discount_fields'], 'epl_sort_by_weight' );

$epl_fields['epl_global_discount_rule_fields'] = array(
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
$epl_fields['epl_global_discount_rule_fields'] = apply_filters( 'epl_global_discount_rule_fields', $epl_fields['epl_global_discount_rule_fields'] );



$epl_fields['epl_social_discount_fields'] = array(
    '_epl_discount_code' => array(
        'input_type' => 'text',
        'input_name' => '_epl_discount_code[]',
        'label' => epl__( 'Discount Code' ),
        //'class' => 'epl_w80',
        'parent_keys' => true),
    '_epl_discount_buyer' => array(
        'input_type' => 'text',
        'input_name' => '_epl_discount_buyer[]',
        'label' => epl__( 'Buyer' ),
        //'class' => 'epl_w70' 
        ),
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
        'class' => 'epl_w40' ),
    '_epl_discount_status' => array(
        'input_type' => 'text',
        'input_name' => '_epl_discount_Status[]',
        'label' => epl__( 'Status' ),
        'class' => '' ),
    '_epl_discount_end_date' => array(
        'input_type' => 'text',
        'input_name' => '_epl_discount_end_date[]',
        'label' => epl__( 'Until' ),
        'class' => '',
        'data_type' => 'unix_time',
        '__func' => 'epl_admin_date_display' ),
    /*'_epl_discount_description' => array(
        'input_type' => 'text',
        'input_name' => '_epl_discount_description[]',
        'placeholder' => epl__( 'Discount Description (optional)' ),
        'class' => 'epl_w100pct',
        'help_text' => epl__( 'This label will appear in the total section as the discount description.' ) ),*/
    '_epl_discount_active' => array(
        'input_type' => 'select',
        'input_name' => '_epl_discount_active[]',
        'label' => epl__( 'Active' ),
        'options' => epl_yes_no(),
        'default_value' => 0 ),
   /* '_epl_discount_cat_include' => array(
        'input_type' => 'checkbox',
        'input_name' => '_epl_discount_cat_include[]',
        'label' => epl__( 'Include Only' ),
        'options' => epl_term_list(),
        'auto_key' => false,
        'second_key' => '[]',
        'display_inline' => true ),*/
);
