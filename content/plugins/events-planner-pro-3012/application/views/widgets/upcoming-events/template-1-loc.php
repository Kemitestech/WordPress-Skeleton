

<div class="epl_upcoming_events_widget_wrapper <?php echo $css_class; ?>">
    <?php echo $title; ?>

    <?php

    if ( !empty( $events ) ):
        global $event_details;
        ?>
        <ul class="epl_upcoming_event_list">

            <?php

            foreach ( $events as $event ):

                setup_event_details( $event['event_id'] );
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

                    <?php if ( !is_null( $loc_id = epl_get_element_m( $event['date_id'], '_epl_date_location', $event_details, epl_get_event_property( '_epl_event_location', true ) > 0 ) ) ): ?>
                        <div class="location_details" style="text-align: right;">
                            <div class="event_location">
                                <?php

                                the_location_details( $loc_id );
                                echo get_the_location_name();
                                ?>
                                <br />
                                <?php echo get_the_location_address(); ?><br />
                                <?php echo (get_the_location_address2() != '') ? get_the_location_address2() . '<br />' : ''; ?>
                                <?php echo get_the_location_city(); ?> <?php echo get_the_location_state(); ?> <?php echo get_the_location_zip(); ?><br />
                                <?php echo get_the_location_phone(); ?>
                                <?php echo get_the_location_gmap_icon( 'See Map' ); ?>
                            </div>
                        </div>
                    <?php endif; ?>



                </li>

            <?php endforeach; ?>
        </ul>

    <?php else: ?>
        <p><?php epl_e( 'No Upcoming Events' ); ?></p>

    <?php endif; ?>


</div>

