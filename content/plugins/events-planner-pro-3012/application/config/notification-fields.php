<?php

global $epl_fields;
$epl_fields['epl_notification_fields'] =
        array(
            '_epl_email_template' => array(
                'input_type' => 'select',
                'input_name' => '_epl_email_template',
                'label' => epl__( 'Email Template' ),
                'options' => epl_get_email_template_list(),

                'default_value' => 'default',
                'description' =>  epl__('Many thanks to') . ' ' . epl_anchor('http://www.campaignmonitor.com/templates/', 'Campaign Monitor')
                ),
            '_epl_email_from_name' => array(
                'input_type' => 'text',
                'input_name' => '_epl_email_from_name',
                'label' => epl__( 'From Name' ),
                'default_value' => get_bloginfo( 'name' ),
                'class' => 'epl_w300 req' ),
            '_epl_from_email' => array(
                'input_type' => 'text',
                'input_name' => '_epl_from_email',
                'label' => epl__( 'From Email' ),
                'default_value' => get_bloginfo( 'admin_email' ),
                'class' => 'epl_w300' ),
            '_epl_email_subject' => array(
                'input_type' => 'text',
                'input_name' => '_epl_email_subject',
                'label' => epl__( 'Email Subject' ),
                'class' => 'epl_w300 req' ),
);

$epl_fields['epl_notification_fields'] = apply_filters( 'epl_notification_fields', $epl_fields['epl_notification_fields'] );