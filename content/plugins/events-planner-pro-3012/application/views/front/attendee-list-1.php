<h1><?php echo $event_title; ?> <?php epl_e('attendees'); ?></h1>

<?php
echo get_the_register_button();
if(epl_is_empty_array($header_row)){
    epl_e("No Attendees");
    return;
}

$colspan = count($header_row);

?>

<table id="epl_attendee_list_table">
    <thead>
        <tr><th><?php echo implode( '</th><th>', $header_row ); ?></th></tr>
    </thead>
    <tbody>
        <?php

        foreach ( $list as $regis_id => $info ) {

            echo "<tr>";
            echo "<td colspan='{$colspan}' class='att_count'>" .epl__('Attendees') .": {$info['att_count']}</td></tr>";

            foreach ( $info['attendees'] as $att ) {

                $v = implode( '</td><td>', $att );
                echo "<tr class='att_info'><td>{$v}</td></tr>";
            }
        }
        ?>
    </tbody>
</table>