<div class="" style="">

    <?php if (  epl_sc_is_enabled()): ?>
    <div class="epl_error"><?php epl_e( 'Events specific discounts are not available when event cart is enabled.  Please use global discounts instead.' ); ?></div>
    <?php endif; ?>
    <?php if ( !isset( $_POST['event_list_discount_import_dd'] ) ): ?>
        <div class="epl_info">
            <?php echo epl__( 'Import' ) . ' ' . epl__( 'and' ) . ' ' . $discount_import_action['field'] . ' ' . epl__( 'from' ) . ' ' . $event_list_discount_import_dd['field']; ?>

        </div>



        <table class="epl_form_data_table" cellspacing ="0">
            <thead>

            <th colspan="2"></th>
            </thead>
            <?php

            echo current( $epl_discount_option_fields );
            ?>
        </table>   
        <br />
    <?php endif; ?>

    <div id="epl_discount_data_wrapper">

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

                <?php

                foreach ( $epl_discount_fields as $row ) :
                    ?>

                    <tr class="copy_">

                        <td>
                            <div class="handle"></div>
                        </td>
                        <td>

                            <table class="epl_form_data_table" cellspacing ="0">


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

                                    <?php if ( epl_is_addon_active( '_epl_atp' ) ): //do not deacitvate, will not work    ?>
                                        <tr>

                                            <td><?php echo $row['_epl_discount_forms']['label']; ?></td>
                                            <td colspan="5"><?php echo $row['_epl_discount_forms']['field']; ?></td>
                                            <td colspan="2"><?php echo $row['_epl_discount_forms_per']['label']; ?>
                                                <?php echo $row['_epl_discount_forms_per']['field']; ?></td>


                                        </tr>



                                    <?php endif; ?>

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

    </div>


</div>
