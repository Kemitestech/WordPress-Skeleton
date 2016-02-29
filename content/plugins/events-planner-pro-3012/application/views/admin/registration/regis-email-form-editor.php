<label class="epl_w100 epl_dsp_i_bl"><?php epl_e('From Name'); ?></label> <input type="text" name="_epl_email_from_name" class="epl_w300" value="<?php echo $notif_data['_epl_email_from_name']; ?>" /><br />
<label class="epl_w100 epl_dsp_i_bl"><?php epl_e('From Email'); ?></label> <input type="text" name="_epl_from_email" class="epl_w300" value="<?php echo $notif_data['_epl_from_email']; ?>" /><br />
<label class="epl_w100 epl_dsp_i_bl">Cc</label> <input type="text" name="_epl_email_cc" class="epl_w300" value="" /><br />
<label class="epl_w100 epl_dsp_i_bl">Bcc</label> <input type="text" name="_epl_email_bcc" class="epl_w300" value="" /><br />
<label class="epl_w100 epl_dsp_i_bl"><?php epl_e('Email Subject'); ?></label> <input type="text" name="_epl_email_subject" class="epl_w300" value="<?php echo $notif_data['_epl_email_subject']; ?>" /><br />

<textarea cols="" rows=""  name="email_body" class="email_body"><?php echo $notif_data['post_content']; ?></textarea>