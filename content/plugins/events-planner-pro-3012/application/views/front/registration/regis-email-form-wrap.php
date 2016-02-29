
<table  cellpadding="3" cellspacing="0" align="center" style="margin:10px 0px;border:1px solid #eee;width: 100%;" border="0">

    <tr>
        <td colspan="2" style="font-weight: bold;font-size: 16px;">
            <?php if ( isset( $form_label ) && $form_label != '' ): ?>
                <?php echo $form_label; ?>
            <?php endif; ?>
            <span style="float:right;"><?php echo $ticket_number>0?$price_name:''; ?></span>
        </td>
    </tr>



<?php echo $email_fields; ?>
</table>
