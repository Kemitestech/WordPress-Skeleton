<?php  $regis_status_id = get_the_regis_status( null, true ); ?>

<?php if ($regis_status_id <= 2): //pending and below?>
<tr>
    <td><?php epl_e( 'Payment Method' ); ?></td>
    <td><?php echo htmlspecialchars_decode( $gateway_info['_epl_pay_display'] ); ?></td>
</tr>
<?php endif; ?>

<?php if ( $inst = epl_get_element( '_epl_instructions', $gateway_info ) ): ?>

    <tr>
        <td><?php epl_e( 'Payment Instructions' ); ?></td>
        <td><?php echo nl2br( $inst ); ?></td>
    </tr>

<?php endif; ?>

<?php if ( $gateway_info['_epl_pay_type'] == '_check' ): ?>

    <tr>
        <td><?php epl_e( 'Payable To' ); ?></td>
        <td><?php echo $gateway_info['_epl_check_payable_to']; ?></td>
    </tr>    
    <tr>    
        <td><?php epl_e( 'Send Payment To' ); ?></td>
        <td><?php echo nl2br( $gateway_info['_epl_check_address'] ); ?></td>
    </tr>

<?php endif; ?>




