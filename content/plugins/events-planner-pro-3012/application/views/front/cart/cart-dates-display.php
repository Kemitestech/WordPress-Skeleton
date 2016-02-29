<?php

if ( $show_date_selector_link ==true && $mode != 'overview' ): ?>
    <div class="epl_section">
        <div  class="epl_section_header"><?php epl_e( 'Please click to select a date' ); ?></div>

        <div id="epl_date_selector-<?php echo $event_id; ?>" class="epl_date_selector" style="margin-top:10px;"></div>
    </div>

<?php endif; ?>                        
<div class="epl_section epl_cart_dates">
    <div  class="epl_section_header"><?php //epl_e( 'Dates' ); ?></div>

    <div id="epl_cart_dates_body-<?php echo $event_id; ?>" style="max-height: 250px;overflow: auto;">

        <?php echo $event_dates; ?>
    </div>
</div>