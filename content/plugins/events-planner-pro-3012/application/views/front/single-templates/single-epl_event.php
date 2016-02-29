<?php global $event_details; ?>

<div class="event_wrapper clearfix">

    <div class="col_left">

        <div class="event_description clearfix">
            <?php

            setup_event_details();

            ##########  DO NOT USE the_content() #############
            echo $content;
            ?>

        </div>

        <div class ="event_dates">
            <span class=""><?php epl_e( 'Dates' ); ?></span>

            <?php

            $alt_text = epl_get_element( '_epl_dates_alt_text', $event_details, '' );

            if ( $alt_text == '' ):
                ?>

                <?php echo get_the_event_dates(); ?>
                <?php

                //echo get_the_event_session_table();

                $d = epl_get_event_property( '_epl_date_display_type', true );

                if ( $d != 0 ):

                    echo get_the_event_dates_cal();
                endif;
            else:
                echo $alt_text;

            endif;
            ?>
        </div>

        <?php if ( !epl_is_time_optonal() ): ?>
            <div class ="event_times">
                <span class=""><?php epl_e( 'Times' ); ?></span>
                <?php echo get_the_event_times(); ?>
            </div>
        <?php endif; ?>

        <div class ="event_prices" style="clear:both;">
            <span class=""><?php epl_e( 'Prices' ); ?></span>
            <?php echo get_the_event_prices(); ?>
        </div>
    </div>


    <div class="col_right">

        <?php

        //location id is stored in $event_details['_epl_event_location']
        ?>
        <div class="event_location">

            <?php if ( !epl_is_multi_location() && epl_get_event_property( '_epl_event_location', true ) > 0 ): ?>
                <span class="heading"><?php epl_e( 'Location' ); ?></span>
                <?php echo get_the_location_name(); ?>
                <br />

                <?php echo get_the_location_address(); ?><br />
                <?php echo (get_the_location_address2() != '') ? get_the_location_address2() . '<br />' : ''; ?>
                <?php echo get_the_location_city(); ?> <?php echo get_the_location_state(); ?> <?php echo get_the_location_zip(); ?><br />
                <?php echo get_the_location_phone(); ?><br />
                <?php echo (get_the_location_website() ? epl_anchor( get_the_location_website(), epl__('Visit Website') . '<br />') : ''); ?>
                <?php echo get_the_location_gmap_icon(); ?>

            <?php elseif ( epl_is_multi_location() ): ?>

                <?php epl_e( 'Multiple locations' ); ?>

            <?php endif; ?>


        </div>


        <?php

        //organization id is stored in $event_details['_epl_event_organization']
        if ( epl_get_event_property( '_epl_event_organization' ) != '' ):
            ?>
            <div class ="event_organization">
                <span class="heading">Hosted By</span>
                <a href="<?php echo get_permalink( $event_details['_epl_event_organization'] ); ?>" title="<?php echo get_the_organization_name(); ?>"><?php echo get_the_organization_name(); ?></a><br />
                <?php echo get_the_organization_address(); ?><br />
                <?php echo get_the_organization_city(); ?>  <?php echo get_the_organization_state(); ?> <?php echo get_the_organization_zip(); ?><br />
                <?php echo get_the_organization_phone(); ?><br />
                <?php echo epl_anchor( get_the_organization_website(), 'Visit Website' ); ?><br />
            </div>
        <?php endif; ?>
    </div>

    <div class ="register_button_wrapper" style="clear:both;">

        <?php echo get_the_register_button(); ?>
    </div>
</div>
