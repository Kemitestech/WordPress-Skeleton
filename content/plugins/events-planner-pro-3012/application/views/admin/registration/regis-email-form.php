<form action=""  id="epl_email_form" method="post">

    <div style="padding:10px;border:1px solid #eee;display: block;margin-bottom:10px;max-height: 150px;overflow: auto;">
        <div><?php epl_e( 'Please check the email addresses that you would like to send this email to.' ); ?> (<?php echo $num_emails; ?>)</div>
        <?php echo $emails; ?>
    </div>


    <div style="padding:10px;border:1px solid #eee;display: block;margin-bottom:10px;">
         <button class="button-primary epl_fl_r" id="epl_send_email_button" style="float:right;"><?php epl_e( 'Send Email' ); ?></button>
        <?php echo $available_notifications['label']; ?>
        <?php echo $available_notifications['field']; ?>
        
         <?php if ( epl_is_addon_active( '_epl_atp' ) && epl_get_setting( 'epl_api_option_fields', 'epl_invoice_attach_to_conf', false ) ): //do not deacitvate, will not work   ?>
        <?php epl_e('Attach PDF invoice'); ?> <input type="checkbox" name="attach_pdf_invoice" id="attach_pdf_invoice" value="1" /> 
        <?php endif; ?>
    </div>


    <div style="padding:10px;border:1px solid #eee;display: block;margin-bottom:10px;overflow:hidden;" id="epl_email_editor_wrapper">
        <div class="epl_warning"><?php epl_e( 'Please select a template to begin.' ); ?></div>
    </div>

    <div style="padding:10px;border:1px solid #eee;display: block;margin-bottom:10px;">
        <?php epl_e( 'Copy these email addresses if you would like to use another email client.' ); ?>

        <textarea cols="80" rows="5"><?php echo $email_list_for_copy; ?></textarea>
    </div>
    <input type="hidden" name="post_ID" value="<?php echo $post_ID; ?>" />
    <input type="hidden" name="event_id" value="<?php echo $event_id; ?>" />

</form>
