<form id="epl_waitlist_form">
    <?php

    echo $form;
    ?>
    <input type="hidden" value="<?php echo epl_get_element('event_id', $_POST,''); ?>" name="event_id" id="epl_waitlist_event_id" />
    <input type="submit" name="submit" id="epl_waitlist_form_submit" class="epl_button_small" value="Submit" />
</form>

<script>
    
    jQuery(document).ready(function($){
        
       $('#epl_waitlist_form').validate();
      
        
        
    });
    
</script>