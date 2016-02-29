<?php //echo "<pre class='prettyprint'>" . __LINE__ . "> " . print_r($values, true). "</pre>";; ?>


<table class="epl_form_data_table epl_report_fields_table epl_w400" cellpadding="0" cellspacing="0" >
    <thead>

        <tr>
        <th></th>
        <th><?php epl_e('Field'); ?></th>
        <th><?php epl_e('Width'); ?></th>
        
        </tr>
    </thead>
    <tbody>
        <?php foreach ( $fields as $k => $v ): ?>

            <tr>
                <td class="epl_w20"> <div class="handle"></div></td>
                <td>
                    <input type="checkbox" name="_epl_report_column[<?php echo $k; ?>]" value="<?php echo $k; ?>" <?php checked($k, epl_get_element($k, epl_get_element('_epl_report_column',$values))); ?> />

                    <?php echo $v['label']; ?>

                </td>
                <td>    
                <input type="text" size ="3" name="_epl_report_column_width[<?php echo $k; ?>]" value="<?php echo epl_get_element($k,epl_get_element('_epl_report_column_width', $values), 20) ?>"  />

            </td>
        </tr>

        <?php endforeach; ?>
    </tbody>
</table>

<script>
    jQuery(document).ready(function($){
        $('.epl_report_fields_table tbody').sortable();
        /*$( "#slider-range-max" ).slider({
                range: "max",
                min: 1,
                max: 10,
                value: 2,
                slide: function( event, ui ) {
                    $( "#amount" ).val( ui.value );
                }
            });
            $( "#amount" ).val( $( "#slider-range-max" ).slider( "value" ) );
         */

    });


</script>