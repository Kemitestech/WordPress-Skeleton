<a href="#" class="add_time_block button-primary"><?php epl_e( 'Add Time Block' ); ?></a>
<?php

foreach ( $time_fields as $k => $row ) :
    $_time_key = $k;
?>

    <div class="time-box">
        <div class="epl_action epl_delete"></div>
        <table class="epl_form_data_table" cellspacing ="0" id="" style="width:300px;">
            <thead>

            <?php echo epl_get_the_labels( $time_field_labels ); ?>
        <th></th>
        </thead>

        <tbody class="events_planner_tbody">

            <tr>
                <?php

                echo $row;
                ?>


            </tr>

        </tbody>


    </table>
    <div class="price-box" style="margin-left:25px;">

        <table class="epl_form_data_table " cellspacing ="0" id="epl_prices_table" style="width:100%;">
            <thead>
            <th colspan="10"></th>

            </thead>
            <tfoot>
                <tr>
                    <td colspan ="12">
                        <a href="#" class="add_table_row"><img src ="<?php echo EPL_FULL_URL ?>images/add.png" /></a>
                    </td>
                </tr>
            </tfoot>
            <tbody class="events_planner_tbody">


                <?php

                foreach ( $price_fields as $_price_key => $price_field_row ) :

                    if ( $epl_price_parent_time_id_key == '' || $_time_key == $epl_price_parent_time_id_key[$_price_key] ):
                ?>

                        <tr class="copy_">
                            <td><div class="handle"></div></td>


                            <td>

                                <table class="epl_prices_row_table" cellspacing ="0">


                                    <thead>
                                        <tr>
                                            <th><?php echo $price_field_row['_epl_price_name']['label']; ?></th>
                                            <th><?php echo $price_field_row['_epl_price']['label']; ?></th>
                                            <th><?php echo $price_field_row['_epl_price_min_qty']['label']; ?></th>
                                            <th><?php echo $price_field_row['_epl_price_max_qty']['label']; ?></th>
                                            <th><?php echo $price_field_row['_epl_price_zero_qty']['label']; ?></th>
                                    <?php if ( epl_is_addon_active( '_epl_atp' ) ): ?>
                                        <th><?php echo $price_field_row['_epl_price_capacity']['label']; ?></th>
                                        <th><?php echo $price_field_row['_epl_price_date_from']['label']; ?></th>
                                        <th><?php echo $price_field_row['_epl_price_date_to']['label']; ?></th>
                                    <?php endif; ?>
                                        <th><?php echo $price_field_row['_epl_price_hide']['label']; ?></th>




                                    </tr>
                                </thead>

                                <tbody>
                                    <tr>

                                        <td><?php echo $price_field_row['_epl_price_name']['field']; ?></td>
                                        <td><?php echo $price_field_row['_epl_price']['field']; ?></td>
                                        <td><?php echo $price_field_row['_epl_price_min_qty']['field']; ?></td>
                                        <td><?php echo $price_field_row['_epl_price_max_qty']['field']; ?></td>
                                        <td><?php echo $price_field_row['_epl_price_zero_qty']['field']; ?></td>
                                    <?php if ( epl_is_addon_active( '_epl_atp' ) ): ?>
                                            <td><?php echo $price_field_row['_epl_price_capacity']['field']; ?></td>
                                            <td><?php echo $price_field_row['_epl_price_date_from']['field']; ?></td>
                                            <td><?php echo $price_field_row['_epl_price_date_to']['field']; ?></td>
                                    <?php endif; ?>
                                            <td><?php echo $price_field_row['_epl_price_hide']['field']; ?><?php echo $price_field_row['_epl_price_parent_time_id']['field']; //DO NOT DELETE THIS ?></td>



                                        </tr>


                                    </tbody>

                                </table>
                            </td>
                            <td>

                                <div class="epl_action epl_delete"></div>
                            </td>

                        </tr>
                <?php endif;
                                        endforeach; ?>
                                    </tbody>


                                </table>



                            </div>
                        </div>
<?php endforeach; ?>

<script>


    jQuery(document).ready(function(){

        create_timepicker(jQuery('.timepicker'));
        create_datepicker(jQuery('.datepicker'));

    });

</script>