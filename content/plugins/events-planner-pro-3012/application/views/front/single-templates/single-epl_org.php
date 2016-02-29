<?php

the_organization_details(); //puts the location details in a global variable $organization_details
global $organization_details; //This is an array with all the information from the db
//You can uncomment this line to see what is in $organization_details
//echo "<pre>ORGANIZATION DETAILS " . print_r( $organization_details, true ) . "</pre>";
?>


<?php echo $content; //DO NOT USER the_content() or you will end up in an infinite loop :o ?>




<div class="event_organization_wrapper">



    <div style="width:250px;margin:0 auto;text-align: center;">

        <div class ="event_organization">
            <?php echo get_the_organization_address(); ?><br />
            <?php echo get_the_organization_city(); ?>,  <?php echo get_the_organization_state(); ?> <?php echo get_the_organization_zip(); ?><br />
            <?php echo get_the_organization_phone(); ?><br />

        </div>

    </div>

</div>
