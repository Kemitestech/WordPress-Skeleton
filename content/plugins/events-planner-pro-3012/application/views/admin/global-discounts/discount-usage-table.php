<style>
    table#epl_discount_table tbody tr:not(:first-child) td table thead{

        display:  none;
        height: 0;
    }
</style>


<table cellspacing ="0" id="epl_discount_table" >

    <tbody class="">

        <?php

        $tmpl = array( 'table_open' => '<table cellpadding="0" cellspacing="0" style="margin:15px">' );

        $this->epl->epl_table->set_template( $tmpl );

        foreach ( $epl_discount_fields as $disc_code => $row ) :

        $counter = 1;
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

            <tr>

                <td>

                    <table cellspacing ="0" class="epl_standard_table" style="margin:0;border:1px solid #eee;width: 350px;">

                        <?php if ( $counter == 1 ): ?>
                            <thead>
                                <tr>


                                    <th><?php epl_e( 'Code' ); ?></th>
                                    <th><?php epl_e( 'Amount' ); ?></th>
                                    <th><?php epl_e( 'Type' ); ?></th>

                                </tr>
                            </thead>
                        <?php endif; ?>
                        <tbody>
                            <tr>

                                <td><?php echo $row['_epl_discount_code']['value']; ?></td>
                                <td><?php echo $row['_epl_discount_amount']['value']; ?></td>
                                <td><?php echo $row['_epl_discount_type']['value']; ?></td>

                            </tr>

                            <tr>
                                <td colspan="4">
                                    <?php echo $discount_used; ?>
                                </td>

                            </tr>

                        </tbody>


                    </table>
                </td>


            </tr>


            <?php

            $counter++;
        endforeach;
        ?>

    </tbody>


</table>

