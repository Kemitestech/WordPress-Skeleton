<div class="epl_tabs" style="min-height: 500px;">

    <ul>
        <li><a href="#tabs-1"><?php epl_e( 'General' ); ?></a></li>
        <li><a href="#tabs-2"><?php epl_e( 'Forms' ); ?></a></li>
        <li><a href="#tabs-3"><?php epl_e( 'Display & Other Options' ); ?></a></li>
        <li><a href="#tabs-4"><?php epl_e( 'Messages' ); ?></a></li>
        <li><a href="#tabs-5"><?php epl_e( 'Discounts' ); ?></a></li>

        <li><a href="#tabs-7"><?php epl_e( 'Attendee List' ); ?></a></li>
        <li><a href="#tabs-8"><?php epl_e( 'Waitlist' ); ?></a></li>
        <li><a href="#tabs-9"><?php epl_e( 'Surcharge' ); ?></a></li>
       

    </ul>



<div id="tabs-1">

        <?php

        echo $location_and_other_section;
        ?>

</div>
<div id="tabs-2">

        <?php

        echo $registration_options_section;
        ?>

</div>
<div id="tabs-3">
 
        <?php

        echo $other_options_section;
        ?>

</div>
<div id="tabs-4">

        <?php

        echo $message_section;
        ?>

</div>
<div id="tabs-5">

        <?php

        echo $discounts_section;
        ?>

</div>

<div id="tabs-7">

        <?php

        echo $attendee_list_section;
        ?>

</div>
<div id="tabs-8">

        <?php

        echo $waitlist_section;
        ?>

</div>
<div id="tabs-9">

        <?php

        echo $surcharge_section;
        ?>

</div>

</div>    

        <input type="hidden" value="<?php echo epl_get_element('action', $_GET); ?>" name="post_action" />


<script type="text/javascript">
    jQuery(document).ready(function($){
        //$(".epl_tabs").tabs({ fx: {opacity: 'toggle', duration:'fast' } });
        $(".epl_tabs").tabs();

    });
</script>