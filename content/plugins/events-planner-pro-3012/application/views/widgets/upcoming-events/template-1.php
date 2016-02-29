

<div class="epl_upcoming_events_widget_wrapper <?php echo $css_class; ?>">
    <?php echo $title; ?>

    <?php

    if ( !empty( $events ) ):
        ?>
        <ul class="epl_upcoming_event_list">

            <?php foreach ( $events as $event ): ?>

                <?php

                $link_type = $event['register_link_type'];
                $link_class = ($link_type == 0 ? 'epl_register_button' : '');
                ?>

                <li class="epl_list_row <?php echo $enable_tooltip ?>" id="epl_<?php echo $event['event_id']; ?>">

                    <div class="epl-ue-widget-calendar ">
                        <span class="month"><?php echo date_i18n( 'M', epl_get_date_timestamp( $event['date'] ) ); ?></span>
                        <span class="day"><?php echo date_i18n( 'd', epl_get_date_timestamp( $event['date'] ) ); ?></span>
                    </div>

                    <div class="event_details">
                        <a class="event_detail_link <?php echo $link_class; ?>" href="<?php echo $event['register_link']; ?>">
                            <?php echo $event['title']; ?>
                        </a>
                        <span style="display:none;" class="event_details_hidden"><?php echo strip_tags( $event['description'], '<img>' ) ?></span>
                    </div>

                </li>

            <?php endforeach; ?>
        </ul>

    <?php else: ?>
        <p><?php epl_e( 'No Upcoming Events' ); ?></p>

    <?php endif; ?>


</div>

