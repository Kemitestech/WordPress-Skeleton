
<table class="epl_prices_table">
    <tr>
        <th>
            <?php

            epl_e( 'Type' );
            ?>
        </th>

        <th>
            <?php

            epl_e( 'Price' );
            ?>
        </th>
        <th>
            <?php

            echo apply_filters( 'epl_cart_prices_quanitity_label', epl__( 'Quantity' ) );
            ?>
        </th>
        <?php if ( epl_is_addon_active( '_epl_atp' ) && $mode!='overview'): //do not deacitvate, will not work?>
                <th>
            <?php

                epl_e( 'Ends' );
            ?>
            </th>
        <?php endif; ?>

            </tr>


    <?php echo $prices_table; ?>


</table>