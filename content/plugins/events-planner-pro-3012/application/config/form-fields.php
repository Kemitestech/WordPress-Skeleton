<?php

global $epl_fields;
$epl_fields['epl_fields'] =
        array(
            'input_name' => array(
                'input_type' => 'hidden',
                'input_name' => 'input_name',
            ),
            'label' => array(
                'weight' => 5,
                'input_type' => 'text',
                'input_name' => 'label',
                'label' => epl__('Field Label'),
                'help_text' => epl__( 'Will be used in the field label.' ),
                'required' => true,
                'class' => 'epl_w300' ),
            'input_slug' => array(
                'weight' => 10,
                'input_type' => 'text',
                'input_name' => 'input_slug',
                'label' => epl__( 'Input Slug' ),
                'help_text' => epl__( 'Will be used in email templates. Ex. your_city, your_weight, height...  DO NOT CHANGE for email, first_name, last_name.' ),
                'required' => true,
                'class' => 'epl_w300 input_name' ),
            'input_type' => array(
                'weight' => 15,
                'input_type' => 'select',
                'input_name' => 'input_type',
                'options' => array( 'text' => 'Text', 'textarea' => 'Textarea', 'select' => 'Dropdown', 'radio' => 'Radio', 'checkbox' => 'Checkbox', 'hidden' => 'Hidden', 'datepicker' => 'Datepicker' ),
                'id' => 'input_type',
                'label' => epl__('Field Type'),
                'help_text' => '',
                'style' => '',
                'class' => 'epl_field_type',
                'default_value' => 'text' ),
            'epl_field_choices' => array(
                'input_type' => 'section',
                'class' => 'epl_field_choices'
            ),
            'epl_field_choice_default' => array(
                'return' => 0,
                'input_name' => 'epl_field_choice_default[]' ),
            'epl_field_choice_text' => array(
                'return' => 0,
                'input_name' => 'epl_field_choice_text[]' ),
            'epl_field_choice_value' => array(
                'return' => 0,
                'input_name' => 'epl_field_choice_value[]'
            ),
            'description' => array(
                'weight' => 20,
                'input_type' => 'textarea',
                'input_name' => 'description',
                'label' => epl__('Field Description'),
                'help_text' => epl__( 'Will be displayed below the field.  Can be used as help text.' ),
                'class' => 'epl_w300' ),
            'required' => array(
                'weight' => 25,
                'input_type' => 'select',
                'input_name' => 'required',
                'label' => epl__('Required'),
                'options' => epl_yes_no(),
                'default_value' => 0,
                'display_inline' => true ),
            'admin_only' => array(
                'weight' => 25,
                'input_type' => 'select',
                'input_name' => 'admin_only',
                'label' => epl__('Admin Only'),
                'options' => epl_yes_no(),
                'default_value' => 0,
                'help_text' => epl__( 'Date collection and display is only available to the admin.' ),
                'display_inline' => true ),
            /* TODO - REVISIT THIS */
            /* 'cb_required' => array(
              'input_type' => 'checkbox',
              'input_name' => 'cb_required[]',
              'label' => 'Required',
              'options' =>  array(1,2,3,4,5),
              ), */
            'default_value' => array(
                'weight' => 30,
                'input_type' => 'text',
                'input_name' => 'default_value',
                'label' => epl__('Default Value'),
                'help_text' => epl__( 'Default value for the field, ONLY FOR Text, Hidden, Textarea (for now).' ),
                'class' => 'epl_w300' ),
            'validation' => array(
                'weight' => 35,
                'input_type' => 'select',
                'input_name' => 'validation',
                'options' => array( 'email' => 'Email' ),
                'empty_row' => true,
                'id' => 'input_type',
                'label' => epl__('Validation'),
                'help_text' => epl__( 'More Coming Soon.' ),
                'style' => '',
                'class' => 'epl_field_type',
                'default_value' => 'text' ),

            'epl_controller' => array(
                'input_type' => 'hidden',
                'input_name' => 'epl_controller',
                'default_value' => 'epl_form_manager' ),
            'epl_system' => array(
                'input_type' => 'hidden',
                'input_name' => 'epl_system',
                'value' => 1,
            )
);
$epl_fields['epl_fields'] = apply_filters( 'epl_fields_fields', $epl_fields['epl_fields'] );

$epl_fields['epl_fields_choices'] =
        array(
            /* 'epl_field_choice_default' => array(
              'input_type' => 'checkbox',
              'input_name' => 'epl_field_choice_default[]' ), */
            'epl_field_choice_text' => array(
                'input_type' => 'text',
                'input_name' => 'epl_field_choice_text[]' ),
            'epl_field_choice_value' => array(
                'input_type' => 'text',
                'input_name' => 'epl_field_choice_value[]'
            )
);


$epl_fields['epl_forms'] =
        array(
            'epl_form_id' => array(
                'input_type' => 'hidden',
                'input_name' => 'epl_form_id' ),
            'epl_form_label' => array(
                'input_type' => 'text',
                'input_name' => 'epl_form_label',
                'id' => 'epl_form_label',
                'label' => epl__('Form Label'),
                'help_text' => epl__('Displayed form identifier'),
                'class' => 'epl_w300',
                'required' => true ),
            'epl_form_slug' => array(
                'input_type' => 'text',
                'input_name' => 'epl_form_slug',
                'label' => epl__('Form Slug'),
                'help_text' => epl__( 'Will be used in emails.' ),
                'class' => 'epl_w300 make_slug',
                'required' => true ),
            'epl_form_descritption' => array(
                'input_type' => 'textarea',
                'input_name' => 'epl_form_descritption',
                'label' => epl__('Form Description'),
                'help_text' => epl__('If you would like to give some form instructions, you can type them here.'),
                'class' => 'epl_w300' ),
            'epl_form_options' => array(
                'input_type' => 'checkbox',
                'input_name' => 'epl_form_options[]',
                'label' => epl__('On the registration form:'),
                'help_text' => '',
                'options' => array( 0 => epl__('Show Form Name.'), 10 => epl__('Show Form Description.') ),
                'class' => '' ),
            'epl_form_fields' => array(
                'return' => 0,
                'input_name' => 'epl_form_fields' ),
            'epl_controller' => array(
                'input_type' => 'hidden',
                'input_name' => 'epl_controller',
                'default_value' => 'epl_form_manager' ),
            'epl_system' => array(
                'input_type' => 'hidden',
                'input_name' => 'epl_system',
                'value' => 1 )
);

