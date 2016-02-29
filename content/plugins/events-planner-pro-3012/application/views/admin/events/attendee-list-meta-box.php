<table class="epl_form_data_table" cellspacing ="0">
<?php

echo current($fields1);


?>
</table>
                                <div class="epl_warning">
                                    <div class="epl_box_content">

        <?php epl_e( "Select the fields below to show to the public.  Move the fields up or down to determine the column order on the display page." ); ?>
                                </div>
                            </div>
<table class="epl_form_data_table epl_attendee_fields_table epl_w300" cellpadding="0" cellspacing="0" >
    <thead>

        <tr>
        <th></th>
        <th><?php epl_e('Field'); ?></th>
        
        </tr>
    </thead>
    <tbody>
        <?php foreach ( $fields as $k => $v ): ?>

            <tr>
                <td class="epl_w20"> <div class="handle"></div></td>
                <td>
                    <input type="checkbox" name="_epl_attendee_list_field[<?php echo $k; ?>]" value="<?php echo $k; ?>" <?php checked($k, epl_get_element($k, epl_get_element('_epl_attendee_list_field',$values))); ?> />

                    <?php echo $v['label']; ?>

                </td>

        </tr>

        <?php endforeach; ?>
    </tbody>
</table>

<script>
    jQuery(document).ready(function($){
        $('.epl_attendee_fields_table tbody').sortable();

    });


</script>