
<div class="epl_upcoming_events_widget_wrapper <?php echo $css_class; ?>">
    <?php echo $title; ?>
    <?php

    if ( !empty( $events ) ):
        global $event_details;
        foreach ( $events as $event ):
            setup_event_details( $event['event_id'] );
            //echo "<pre class='prettyprint'>" . __LINE__ . "> " . basename(__FILE__) . " > " . print_r($event, true) . "</pre>";
            $_tn = '';

            $link_type = $event['register_link_type'];
            $link_class = ($link_type == 0 ? 'epl_register_button' : '');

            if ( has_post_thumbnail( $event['event_id'] ) ) {
                $_tn = get_the_post_thumbnail( $event['event_id'], $thumbnail_size );
            }
            ?>
            <div class="details <?php echo $enable_tooltip ?>">
                <span class="title"><a class="<?php echo $link_class; ?>" href="<?php echo $event['register_link']; ?>" title="<?php echo $event['title']; ?>"><?php echo $event['title']; ?></a></span>
                <div class="banner">
                    <?php if ( $_tn != '' ): ?>
                        <div class="image"><a class="title" href="<?php echo $event['register_link']; ?>"><?php echo $_tn; ?></a></div>
                    <?php endif; ?>
                </div>


                <span class="date">
                    <?php echo date_i18n( 'M d, Y', epl_get_date_timestamp( $event['date'] ) ); ?>

                    <?php

                    $end = epl_get_element( 'end', $event, null );
                    if ( $end && $end != $event['date'] ):
                        ?>
                        - <?php echo date_i18n( 'M d, Y', epl_get_date_timestamp( $end ) ); ?>
                    <?php endif; ?>
                </span>

                <span class="time">
                    <?php

                    foreach ( $event['times']['start'] as $k => $v ) {
                        $_e = ($v != $event['times']['end'][$k]) ? ' - ' . $event['times']['end'][$k] : '';
                        echo epl_prefix( "<br />", $v . $_e );
                    }
                    ?>

                </span>
                <br />

                <span style="display:none;" class="event_details_hidden"><?php echo strip_tags( $event['description'], '<img>' ) ?></span>

                <?php if ( !is_null( $loc_id = epl_get_element_m( $event['date_id'], '_epl_date_location', $event_details, epl_get_event_property( '_epl_event_location', true ) > 0 ) ) ): ?>
                    <div class="location_details">
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

            </div>



        <?php endforeach; ?>

    <?php else: ?>
        <p><?php epl_e( 'No Upcoming Events' ); ?></p>

    <?php endif; ?>


</div>

