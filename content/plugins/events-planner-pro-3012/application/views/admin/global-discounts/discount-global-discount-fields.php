<table class="epl_form_data_table" cellspacing ="0" id="epl_discount_table">
    <thead>

    <th colspan="7"></th>
</thead>
<tfoot>
    <tr>
        <td colspan="7" style="vertical-align: middle;">
            <a href="#" class="add_table_row"><img src ="<?php echo EPL_FULL_URL ?>images/add.png" /></a>
        </td>
    </tr>


</tfoot>
<tbody class="events_planner_tbody">

    <?php foreach ( $epl_discount_fields as $row ) : ?>

        <tr class="copy_">

            <td>
                <div class="handle"></div>
            </td>
            <td>

                <table class="epl_discount_row_table" cellspacing ="0">


                    <thead>
                        <tr>
                            <th><?php echo $row['_epl_discount_method']['label']; ?></th>
                            <th><?php echo $row['_epl_discount_code']['label']; ?></th>
                            <th><?php echo $row['_epl_discount_amount']['label']; ?></th>
                            <th><?php echo $row['_epl_discount_type']['label']; ?></th>
                            <th><?php echo $row['_epl_discount_max_usage']['label']; ?></th>
                            <th><?php echo $row['_epl_discount_end_date']['label']; ?></th>
                            <th><?php echo $row['_epl_discount_active']['label']; ?></th>

                        </tr>
                    </thead>

                    <tbody>
                        <tr>
                            <td><?php echo $row['_epl_discount_method']['field']; ?></td>
                            <td><?php echo $row['_epl_discount_code']['field']; ?></td>
                            <td><?php echo $row['_epl_discount_amount']['field']; ?></td>
                            <td><?php echo $row['_epl_discount_type']['field']; ?></td>
                            <td><?php echo $row['_epl_discount_max_usage']['field']; ?></td>
                            <td><?php echo $row['_epl_discount_end_date']['field']; ?></td>
                            <td><?php echo $row['_epl_discount_active']['field']; ?></td>

                        </tr>

                        <tr>
                            <td colspan="7">
                                <?php epl_e( 'Apply to' ); ?> <?php echo $row['_epl_discount_target']['field']; ?>
                                <?php epl_e( 'if (optional)' ); ?> <?php echo $row['_epl_discount_condition']['field']; ?>
                                <?php echo $row['_epl_discount_condition_logic']['field']; ?>
                                <?php echo $row['_epl_discount_condition_value']['field']; ?> and
                                <?php echo $row['_epl_discount_condition_value2']['field']; ?>
                            </td>
                        </tr>

                        <tr>
                            <td colspan="7">

                                <?php echo $row['_epl_discount_description']['field']; ?>
                                <?php echo $row['_epl_discount_description']['description']; ?>

                            </td>
                        </tr>
                        <tr>
                            <td colspan="7">

                                <?php echo $row['_epl_discount_cat_include']['label']; ?>: 
                                <?php echo $row['_epl_discount_cat_include']['field']; ?>


                            </td>
                        </tr>
                        <!-- removing in > v2.0.8
                        <tr>
                            <td colspan="7">

                                <?php echo $row['_epl_discount_pay_specific']['label']; ?>: 
                                <?php echo $row['_epl_discount_pay_specific']['field']; ?>


                            </td>
                        </tr>-->


                    </tbody>


                </table>
            </td>

            <td>

                <div class="epl_action epl_delete"></div>
            </td>

        </tr>


    <?php endforeach; ?>

</tbody>


</table>