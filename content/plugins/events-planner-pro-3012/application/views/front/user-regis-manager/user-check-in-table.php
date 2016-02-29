<?php

if (epl_is_empty_array($registrations)){
        echo "No record found";
        return;
}


global $event_details;
$event_id = $event_details['ID'];
$class_session_dates = epl_get_element( '_epl_class_session_date', $event_details );

echo "<pre class='prettyprint'>" . __LINE__ . "> " . print_r($registrations, true). "</pre>";
?>



<table class="epl_plain_table epl_w600" cellspacing="0">
    <thead>
        <tr>
            <th></th>
            <th></th>
        </tr>
    </thead>
    <tbody>
        <tr><td></td>
            <?php foreach ( $class_session_dates as $_date_id => $_date ) : ?>



                <td><?php echo epl_formatted_date( $_date ); ?></td>


            <?php endforeach; ?>
        </tr>



        <?php foreach ( $registrations as $regis ): ?>
            <tr>
                <td>

                    <?php

                    $user_data = get_userdata( $regis->user_id );
                    echo $user_data->display_name;
                    //echo "<pre class='prettyprint'>" . __LINE__ . "> " . print_r($user_data, true). "</pre>";
                    ?>
                </td>

                <?php

                foreach ( $class_session_dates as $_date_id => $_date ) :

                    $style = ($_date == EPL_DATE) ? ' style="background-color:#eee"' : '';
                    ?>



                    <td <?php echo $style; ?>>

                        <?php

                        $_key = $event_id . '_' . $_date_id . '_'  . $user_data->ID;
                        $absent = '';
                        $state = 1;

                        if ( isset( $absentees['_epl_user_absent_' . $_key] ) ) {

                            $absent = 'epl_absent';
                            $state = 0;
                        }
                        ?>

                        <div id="<?php echo $_key; ?>" class="epl_check_in <?php echo $absent; ?>" data-state="<?php echo $state; ?>"></div>

                    </td>


                <?php endforeach; ?>

            </tr>
        <?php endforeach; ?>
    </tbody>
</table>
