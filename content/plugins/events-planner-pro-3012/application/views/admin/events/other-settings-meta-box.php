<table class="epl_form_data_table" cellspacing ="0">
<?php

echo current($epl_general_fields);


?>
</table>

<input type="hidden" value="<?php echo epl_get_element('action',$_GET);?>" name="post_action" />