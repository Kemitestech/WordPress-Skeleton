<?php

global $event_details;
$event_id = $event_details['ID'];
$class_session_dates = epl_get_element( '_epl_class_session_date', $event_details );

?>


<style>

    .epl_absent, .epl_check_in {

        height:16px;
        margin-left: 10px;
        width:16px;
        cursor:pointer;
        background:transparent url('<?php echo EPL_FULL_URL; ?>images/accept.png') no-repeat;
    }


    .epl_absent{
        background:transparent url('<?php echo EPL_FULL_URL; ?>images/delete.png') no-repeat;

    }



</style>

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


<script>
    
    jQuery(document).ready(function($){
        $('body').on('click', 'div.epl_check_in', function(){

            var _me = this;
            var me = $(this);
            var state = this.getAttribute('data-state');

            var id = me.prop('id');

            var data = "epl_action=user_check_in&epl_controller=epl_front&id=" + id + '&state=' + state + "&rand=" + Math.random();

            events_planner_do_ajax( data, function(r){
                var d = r.html;

                if(r.is_error == 0){
                    me.toggleClass('epl_absent');
                    _me.setAttribute('data-state', Math.abs(state-1));
                } else
                    alert('Error');
                //alert(d);
            });
            return false;
            
        });
        
        
    });
    
</script>