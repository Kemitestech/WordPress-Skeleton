
<div id='fc_calendar' style="width:450px;margin:0 auto;background-color: #fff;"></div>

<script>

    jQuery(document).ready(function($) {

        //TODO - remove php and use js vars???

        event_type = '<?php echo $event_type; ?>';

        $('#fc_calendar').fullCalendar({
            height: 200,
            aspectRatio: 1,
            header: {
                left: 'title',
                center: '',
                right: 'today prev,next'
            },
            firstDay: EPL.firstDay,
            //theme: <?php echo (epl_get_setting( 'epl_fullcalendar_options', 'epl_fullcalendar_theme', '' ) != '' ? 1 : 0); ?>, //change
            
            selectable: false,
            selectHelper: false,

            events: <?php echo $event_dates;?>,
            eventClick: function(calEvent, jsEvent, view) {

                var c = calEvent.description;
                
                if (event_type == 5)
                $('.epl_cart_dates_body').html(c);
                else
                    $('.epl_cart_dates_body').append(c);
                // change the border color just for fun
                $(this).css('border-color', 'red');

            },
            editable: false,
            loading: function(bool) {
                if (bool) epl_loader('show');
                else epl_loader('hide');
            }

        });


    });


</script>
