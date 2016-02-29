<div class="" style="">

    <table class="epl_form_data_table" cellspacing ="0" id="">
        <thead>


        <th><?php epl_e( 'Location' ); ?></th>
        <th><?php epl_e( 'Type' ); ?></th>
        <th colspan="2"><?php epl_e( 'Message' ); ?></th>
        </thead>
        <tfoot>
            <tr>
                <td colspan="7" style="vertical-align: middle;">
                    <a href="#" class="add_table_row"><img src ="<?php echo EPL_FULL_URL ?>images/add.png" /></a>
                </td>
            </tr>


        </tfoot>
        <tbody class="events_planner_tbody">

            <?php foreach ( $epl_message_fields as $row ) : ?>



                <tr class="copy_">


                    <td style="vertical-align: top;"><?php echo $row['_epl_message_location']['field']; ?></td>
                    <td style="vertical-align: top;"><?php echo $row['_epl_message_type']['field']; ?></td>
                    <td><?php echo $row['_epl_message']['field']; ?></td>


                    <td>
                        <div class="epl_action epl_delete"></div>
                    </td>


                </tr>

<?php endforeach; ?>

        </tbody>


    </table>



</div>

<script>

    jQuery(document).ready(function($){

        var i=1;
        $('.mceeditor').each(function(e)
        {
            var id = $(this).attr('id');

            if (!id)
            {
                id = 'customEditor-' + i++;
                $(this).attr('id',id);
            }

            //tinyMCE.execCommand('mceAddControl', false, id);

        });


    });


</script>