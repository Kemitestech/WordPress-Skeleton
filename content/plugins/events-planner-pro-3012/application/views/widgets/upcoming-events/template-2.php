
<div class="epl_upcoming_events_widget_wrapper <?php echo $css_class; ?>">
    <?php echo $title; ?>
    <?php

    if ( !empty( $events ) ):

        foreach ( $events as $event ):

            $_tn = '';

            $link_type = $event['register_link_type'];
            $link_class = ($link_type == 0 ? 'epl_register_button' : '');

            if ( has_post_thumbnail( $event['event_id'] ) ) {
                $_tn = get_the_post_thumbnail( $event['event_id'], $thumbnail_size );
            }
            ?>
            <div class="details <?php echo $enable_tooltip ?>">
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
                        echo "<br /> {$v}$_e";
                    }
                    ?>

                </span>
                <br />
                <span class="title"><a class="<?php echo $link_class; ?>" href="<?php echo $event['register_link']; ?>" title="<?php echo $event['title']; ?>"><?php echo $event['title']; ?></a></span>
                <span style="display:none;" class="event_details_hidden"><?php echo strip_tags( $event['description'], '<img>' ) ?></span>


            </div>



        <?php endforeach; ?>

    <?php else: ?>
        <p><?php epl_e( 'No Upcoming Events' ); ?></p>

    <?php endif; ?>


</div>

