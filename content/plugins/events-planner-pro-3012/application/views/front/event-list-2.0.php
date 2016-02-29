<?php

if ( !$_POST )
    do_action( 'event_list_top' );

global $event_list;
?>

<div id="event_list_wrapper">

    <?php

    global $event_details, $event_fields;
    /* custom event list loop */
    if ( $event_list->have_posts() ):

        while ( $event_list->have_posts() ) :

            $event_list->the_post();
            setup_event_details();


            //this makes sure <!--more--> tag works
            global $more;
            $more = 0;


            /*
             * As you can see, all the information is wrappeed in divs.  The styling comes from events-planner > css > events-planner-style1.css
             * You can copy the style into your theme and modify
             */
            ?>
            <!-- individual event wrapper -->
            <div class="event_wrapper epl_section clearfix">
            <h2>
                        <?php 

                        if ($event_details['_epl_title_link_destination'] == 0):?>                     
                            <a href ="<?php echo get_permalink(); ?>" title="<?php the_title(); ?>"><?php the_title(); ?></a>
                        <?php else: ?>
                            <a href ="<?php echo get_the_register_button( get_the_ID(), true ); ?>" title="<?php the_title(); ?>"><?php the_title(); ?></a>
                        <?php endif; ?>
   
                        <?php if(epl_is_ok_to_show_regis_button()): ?>
                        <?php echo get_the_register_button( get_the_ID(), false, array('class'=>'arrow') ); ?>
                        <?php endif; ?>
                    </h2>
                <div class="col_left">

                    <div class="event_description clearfix">

                        <?php

                        $d = epl_get_event_property( '_epl_display_content', true );
                        ($d == 1 ? the_excerpt() : ($d == 2 || is_null( $d )) ? the_content() : '');


                        echo get_the_event_session_table();
                        ?>

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
                            <span class="heading"><?php epl_e( 'Hosted By' ); ?></span>
                            <?php echo get_the_organization_name(); ?><br />
                            <?php echo get_the_organization_address(); ?><br />
                            <?php echo get_the_organization_city(); ?>  <?php echo get_the_organization_state(); ?> <?php echo get_the_organization_zip(); ?><br />
                            <?php echo get_the_organization_phone(); ?><br />
                            <?php echo (get_the_organization_website() != '') ? epl_anchor( get_the_organization_website(), epl__( 'Visit Website' ) ) : ''; ?><br />
                        </div>

                    <?php endif; ?>


                    <?php echo get_the_instructor_name( true, false ); ?>
                    <?php echo get_the_attendee_list_link(); ?>



                </div>



                <div class="clearfix"></div>

                <?php

                //the more expandable box
                $d = epl_get_event_property( '_epl_date_display_type', true );

                if ( $d != 0 ):
                    ?>


                    <div class="expand_box_wrapper">
                        <div class="expand_trigger"><?php epl_e( 'More' ); ?></div>
                        <div class="toggle_container">

                            <?php

                            $alt_text = epl_get_element( '_epl_dates_alt_text', $event_details, '' );
                            if ( $alt_text == '' ) {
                                if ( $d == 5 )
                                    echo get_the_event_dates_times_prices();
                                elseif ( $d == 10 )
                                    echo get_the_event_dates_cal();
                            } else {
                                echo $alt_text;
                            }
                            ?>



                        </div>
                    </div>
                <?php endif; ?>

            </div>
            <?php

        endwhile;
    else:
        ?>
        <div> <?php epl_e( 'Sorry, there are no events currently available.' ); ?></div>
    <?php

    endif;
    wp_reset_query();
    ?>

    <?php do_action( 'epl_post_event_list' ); ?>

</div>
