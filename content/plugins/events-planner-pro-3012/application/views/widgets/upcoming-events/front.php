
<div class="epl_upcoming_events_widget_wrapper">


    <?php

    if ( !empty( $events ) ):
        //echo "<pre class='prettyprint'>" . print_r( $events, true ) . "</pre>";
    ?>
        <ul class="epl_upcoming_event_list">
                        
<?php foreach ( $events as $event ): ?>

            <li class="epl_list_row" id="epl_<?php echo $event['event_id']; ?>">

                	<div class="epl-ue-widget-calendar ">
                            <span class="month"><?php echo date_i18n('M', epl_get_date_timestamp($event['date'])); ?></span>
		<span class="day"><?php echo date_i18n('d', epl_get_date_timestamp($event['date'])); ?></span>
	</div>

                    <div class="event_details"><a href="<?php echo $event['register_link']; ?>"><?php echo $event['title']; ?></a></div>

            </li>

<?php endforeach; ?>
        </ul>

    <?php else: ?>
    <p><?php epl_e('No Upcoming Events'); ?></p>

<?php endif; ?>


</div>