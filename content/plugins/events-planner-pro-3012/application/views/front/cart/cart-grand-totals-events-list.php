<?php if ( !epl_is_empty_array( $events ) ):     ?>
    <table>
        <?php

        global $event_details;

        foreach ( $events as $event_id => $totals ): setup_event_details( $event_id );
            ?>

            <tr>
                <td>
                    <?php echo epl_get_element('post_title',$event_details); ?>
                </td>
                <td class="epl_ta_r">
                    <?php echo epl_get_formatted_curr( epl_get_element_m('grand_total', 'money_totals', $totals), null, true ); ?>
                </td>
                <td class="epl_cart_totals_row_delete">
                    <a href="#" class="delete_cart_item" data-caller="summary" id="<?php echo $event_id; ?>">
                        <img src="<?php echo EPL_FULL_URL; ?>/images/cross.png" alt="Delete" />
                    </a>
                </td>
            </tr>
    <?php endforeach; ?>
    </table>
<?php endif; ?>