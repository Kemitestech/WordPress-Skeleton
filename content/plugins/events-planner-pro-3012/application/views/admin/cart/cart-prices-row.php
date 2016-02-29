<tr>
    <td>
        <?php

        echo $price_name;
        ?>
    </td>

    <td>
        <?php

        echo ($price != 0) ? epl_get_formatted_curr( $price,null, true ) : '';
        ?>
    </td>
    <td>
        <?php

        echo epl_get_element( 'field', $price_qty_dd, '&nbsp;' );
        ?>

    </td>

    <?php if ( epl_is_addon_active( '_epl_atp' ) && $mode!='overview' ): //do not deacitvate, will not work ?>
            <td>
        <?php

            if ( $regis_expiration != '' )
                echo ' ' . $regis_expiration['msg'];
        ?>

        </td>
    <?php endif; ?>


        </tr>
<?php if ( $price_note != '' ): ?>

                <tr>
                    <td colspan="4">
                        <i><?php echo $price_note; ?></i>
                    </td>
                </tr>


<?php endif; ?>
