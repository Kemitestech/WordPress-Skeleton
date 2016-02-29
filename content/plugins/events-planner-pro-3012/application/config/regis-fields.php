<?php

//pending pay, paid, cancelled-pending refund, cancel refunded, waiting list
global $epl_fields;
$epl_fields['epl_regis_payment_fields'] =
        array(
            '_epl_payment_method' => array(
                'weight' => 5,
                'input_type' => 'select',
                'input_name' => '_epl_payment_method',
                'label' => epl__( 'Payment Method' ),
                'options' => array(
                    '_pp_exp' => 'PayPal Expr. Checkout',

                ),
                'empty_row' => true
            ),
            '_epl_regis_status' => array(
                'weight' => 10,
                'input_type' => 'select',
                'input_name' => '_epl_regis_status',
                'label' => epl__( 'Regis. Status' ),
                'options' => array(
                    1 => epl__('Incomplete'),
                    2 => epl__('Pending Payment'),
                    5 => epl__('Complete'),
                    10 => epl__('Cancelled - pending refund'),
                    15 => epl__('Cancelled - refunded'),
                    20 => epl__('Waitlist'),
                ),
                'default_value' => 1

            ),
            '_epl_grand_total' => array(
                'weight' => 15,
                'input_type' => 'text',
                'input_name' => '_epl_grand_total',
                'label' => epl__( 'Total Due' ),
                'data_type' => 'float',
                'style' => '',
                'class' => '' ),
            '_epl_payment_amount' => array(
                'weight' => 20,
                'input_type' => 'text',
                'input_name' => '_epl_payment_amount',
                'label' => epl__( 'Total Paid' ),
                'data_type' => 'float',
                'style' => '',
                'class' => '' ),
            '_epl_balance_due' => array(
                'weight' => 22,
                'input_type' => 'text',
                'input_name' => '_epl_balance_due',
                'label' => epl__( 'Balance Due' ),
                'data_type' => 'float',
                'style' => '',
                'class' => '' ),
            '_epl_payment_date' => array(
                'weight' => 25,
                'input_type' => 'text',
                'input_name' => '_epl_payment_date',
                'label' => epl__( 'Date Paid' ),
                'class' => ' datepicker '
            ),
            /*'_epl_refund_method' => array(
                'input_type' => 'select',
                'input_name' => '_epl_refund_method',
                'label' => epl__( 'Refund Method' ),
                'options' => array(
                    '_cash' => epl__('Cash'),
                    '_check' => 'Check',
                    '_pp_exp' => 'PayPal Expr. Checkout',
                    '_other' => 'Other',
                ),
                'empty_row' => true
            ),
            '_epl_refund_amount' => array(
                'input_type' => 'text',
                'input_name' => '_epl_refund_amount',
                'label' => epl__( 'Refund Amount' ),
                'description' => '',
                'style' => '',
                'class' => '' ),
            '_epl_refund_date' => array(
                'input_type' => 'text',
                'input_name' => '_epl_refund_date',
                'label' => epl__( 'Refund Date' ),
                'class' => ' datepicker '
            ),*/
            '_epl_transaction_id' => array(
                'weight' => 30,
                'input_type' => 'text',
                'input_name' => '_epl_transaction_id',
                'label' => epl__( 'Trans. ID' ),
                'description' => '',
                'style' => '',
                'class' => '' ),
            '_epl_payment_note' => array(
                'weight' => 35,
                'input_type' => 'textarea',
                'input_name' => '_epl_payment_note',
                'label' => epl__( 'Notes' ),
                'style' => 'width:100%;',
                'default_value' => '')

);

$epl_fields['epl_regis_payment_fields'] = apply_filters('epl_regis_payment_fields', $epl_fields['epl_regis_payment_fields'] );
uasort( $epl_fields['epl_regis_payment_fields'], 'epl_sort_by_weight' );