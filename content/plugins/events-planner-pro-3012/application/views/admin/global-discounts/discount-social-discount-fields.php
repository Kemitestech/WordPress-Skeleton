<style>
    table#epl_discount_table tbody tr:not(:first-child) td table thead{

        display:  none;
        height: 0;
    }
</style>

<div class="epl_info">
    <?php epl_e( 'You can use this feature to track discounts from many sources.  Please see the table below on how to format the CSV file.' ); ?>
</div>

<table class="epl_standard_table">
    <thead>
        <tr>
            <th><?php epl_e( 'Column' ); ?></th>
            <th><?php epl_e( 'Expected Value' ); ?></th>
            <th><?php epl_e( 'Required' ); ?></th>
        </tr>
    </thead>
    <tr>
        <td>A</td>
        <td><?php epl_e( 'Code' ); ?></td>       
        <td><?php epl_e( 'Yes' ); ?></td>       
    </tr>
    <tr>
        <td>B</td>
        <td><?php epl_e( 'Buyer Name' ); ?></td>       
        <td><?php epl_e( 'No' ); ?></td>       
    </tr>
    <tr>
        <td>C</td>
        <td><?php epl_e( 'Amount' ); ?></td>       
        <td><?php epl_e( 'No' ); ?></td>       
    </tr>
    <tr>
        <td>D</td>
        <td><?php epl_e( 'Max Use' ); ?></td>       
        <td><?php epl_e( 'No' ); ?></td>       
    </tr>
    <tr>
        <td>E</td>
        <td><?php epl_e( 'Expiration Date' ); ?></td>       
        <td><?php epl_e( 'No' ); ?></td>       
    </tr>


</table>



<div class="code_uploader" style="padding: 20px 0;">
    <?php epl_e( 'Please use the Upload button to upload a CSV list of codes.' ); ?>
    <button class="button-primary" name="" id=""><?php epl_e( 'Upload CSV File' ); ?></button>
</div>

<?php epl_e( 'Auto-fill Amount' ); ?> <input id="autofil_epl_discount_amount" />
<?php epl_e( 'Auto-fill Discount Type' ); ?>
<select id="autofil_epl_discount_type">
    <option value=""></option>
    <option value="5"><?php epl_e( 'Fixed' ); ?></option>
    <option value="10"><?php epl_e( 'Percent' ); ?></option>
</select>

<table cellspacing ="0" id="epl_discount_table" >

    <tfoot>
        <tr>
            <td colspan="7" style="vertical-align: middle;">
                <a href="#" class="add_table_row"><img src ="<?php echo EPL_FULL_URL ?>images/add.png" /></a>
            </td>
        </tr>


    </tfoot>
    <tbody class="">

        <?php

        $counter = 1;
        foreach ( $epl_discount_fields as $disc_code => $row ) :

            $discount_used = '';
            if ( epl_get_element( $disc_code, $used_discount_codes ) ) {
                foreach ( $used_discount_codes[$disc_code] as $used ) {
                    $this->epl->epl_table->add_row(
                            epl_anchor( admin_url( 'post.php?post=' . $used['regis_id'] . '&action=edit' ), $used['regis_key'] ), epl_formatted_date( $used['regis_date'] )
                    );
                }

                $discount_used = $this->epl->epl_table->generate();
                $this->epl->epl_table->clear();
            }
            ?>

            <tr class="copy_">


                <td>

                    <table cellspacing ="0" class="epl_standard_table" style="margin:0;">

                        <?php if ( $counter == 1 ): ?>
                            <thead>
                                <tr>

                                    <th><?php echo $row['_epl_discount_code']['label']; ?></th>
                                    <th><?php echo $row['_epl_discount_buyer']['label']; ?></th>
                                    <th>
                                        <?php echo $row['_epl_discount_amount']['label']; ?>

                                    </th>
                                    <th><?php echo $row['_epl_discount_type']['label']; ?><br />
                                    <th><?php echo $row['_epl_discount_max_usage']['label']; ?></th>
                                    <th><?php echo $row['_epl_discount_end_date']['label']; ?></th>
                                    <th><?php echo $row['_epl_discount_active']['label']; ?></th>
                                    <th></th>

                                </tr>
                            </thead>
                        <?php endif; ?>
                        <tbody>
                            <tr>

                                <td><?php echo $row['_epl_discount_code']['field']; ?></td>
                                <td><?php echo $row['_epl_discount_buyer']['field']; ?></td>
                                <td><?php echo $row['_epl_discount_amount']['field']; ?></td>
                                <td><?php echo $row['_epl_discount_type']['field']; ?></td>
                                <td><?php echo $row['_epl_discount_max_usage']['field']; ?></td>
                                <td><?php echo $row['_epl_discount_end_date']['field']; ?></td>
                                <td><?php echo $row['_epl_discount_active']['field']; ?></td>
                                <td><?php echo $discount_used; ?></td>

                            </tr>


                        </tbody>


                    </table>
                </td>

                <td>

                    <div class="epl_action epl_delete"></div>
                </td>

            </tr>


            <?php

            $counter++;
        endforeach;
        ?>

    </tbody>


</table>

