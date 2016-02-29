
<script type="text/javascript">
    jQuery(document).ready(function($){
        $(".epl_tabs").tabs({ 'selected':0,fx: {opacity: 'toggle', duration:'200' } });

    });
</script>

<div class="epl_tabs">
    <ul>
        <li><a href="#tabs-1"><?php epl_e('Dates'); ?></a></li>
        <li><a href="#tabs-2"><?php epl_e('Times/Prices'); ?></a></li>
        <li><a href="#tabs-3"><?php epl_e('Location/Organization'); ?></a></li>
        <li><a href="#tabs-4"><?php epl_e('Registration Fields'); ?></a></li>
        <li><a href="#tabs-5"><?php epl_e('Other Settings'); ?></a></li>
    </ul>
    <div id="tabs-1">

        <?php echo $event_dates_section; ?>
        <?php echo $event_recurrence_section; ?>

    </div>



    <div id="tabs-2">
        <?php echo $event_times_prices_section; ?>
    </div>
    <div id="tabs-3">
       <?php echo $location_option_section; ?>

    </div>
    <div id="tabs-4">
       <?php echo $registration_options_section; ?>
    </div>
    <div id="tabs-5">
        <?php echo $other_options_section; ?>
    </div>



</div>




<div class="clear"></div>

