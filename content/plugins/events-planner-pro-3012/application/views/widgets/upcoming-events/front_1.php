
<div class="epl_upcoming_events_widget_wrapper">


    <?php

    if ( !empty( $events ) ):
        //echo "<pre class='prettyprint'>" . print_r( $events, true ) . "</pre>";
    ?>
        <ul class="epl_upcoming_event_list">

        <?php foreach ( $events as $event ): ?>

            <li class="epl_list_row" id="epl_<?php echo $event['event_id']; ?>">

                <div class="epl-ue-widget-calendar ">
                    <span class="month"><?php echo date_i18n( 'M', epl_get_date_timestamp( $event['date'] ) ); ?></span>
                    <span class="day"><?php echo date_i18n( 'd', epl_get_date_timestamp( $event['date'] ) ); ?></span>
                </div>

                <div class="event_details"><a class="event_detail_link" href="<?php echo $event['register_link']; ?>"><?php echo $event['title']; ?></a><span style="display:none;" class="event_details_hidden"><?php echo $event['description']; ?></span></div>

            </li>

        <?php endforeach; ?>
        </ul>

    <?php else: ?>
                <p><?php epl_e( 'No Upcoming Events' ); ?></p>

    <?php endif; ?>


</div>

<script>
    jQuery(document).ready(function($){

        $('.event_detail_link').each(function(){
            var me = $(this);
            var c = me.next('.event_details_hidden').html();

            if (c != '...'){
            me.tooltipsy({

                offset: [-10, 0],
                show: function (e, $el) {
                    $el.css({
                        'left': parseInt($el[0].style.left.replace(/[a-z]/g, '')) - 50 + 'px',
                        'opacity': '0.0',
                        'display': 'block'
                    }).animate({
                        'left': parseInt($el[0].style.left.replace(/[a-z]/g, '')) + 50 + 'px',
                        'opacity': '1.0'
                    }, 300);
                },
                hide: function (e, $el) {
                    $el.slideUp(100);
                },
                content: c,
                //className: 'bubbletooltip_tip'
                css: {
                    'padding': '5px',
                    'font-size': '10px',
                    'max-width': '200px',
                    'color': '#303030',
                    'background-color': '#f5f5b5',
                    'border': '1px solid #deca7e',
                    '-moz-box-shadow': '0 0 10px rgba(0, 0, 0, .5)',
                    '-webkit-box-shadow': '0 0 10px rgba(0, 0, 0, .5)',
                    'box-shadow': '0 0 10px rgba(0, 0, 0, .5)',
                    'text-shadow': 'none'
                }

            });
        }
    });


});




</script>