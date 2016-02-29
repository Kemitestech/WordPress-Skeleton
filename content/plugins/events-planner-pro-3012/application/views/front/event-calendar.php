<?php

$legend_location = epl_nz( epl_get_setting( 'epl_fullcalendar_options', 'epl_fullcalendar_show_legend' ), 0 );
$legend = array( );
if ( $legend_location != 0 ) {
    $legend[$legend_location] = '';
    $terms = epl_object_to_array( get_terms( 'epl_event_categories' ) );

    $event_bcg_color = epl_get_setting( 'epl_fullcalendar_options', 'epl_fullcalendar_tax_bcg_color' );
    $event_font_color = epl_get_setting( 'epl_fullcalendar_options', 'epl_fullcalendar_tax_font_color' );

    foreach ( $terms as $term ) {
        $bc = epl_get_element( $term['slug'], $event_bcg_color, '#ffffff' );
        $tc = epl_get_element( $term['slug'], $event_font_color, 'blue' );

        $legend[$legend_location] .= "<span class='epl_fc_legend_cat' style='background-color:{$bc};color:{$tc}'>{$term['name']}</span>";
    }
    $legend[$legend_location] = '<div class="epl_fc_legend">' . $legend[$legend_location] . '</div>';
}
?>

<?php echo epl_get_element( 1, $legend ); ?>

<div id='calendar'></div>

<?php echo epl_get_element( 10, $legend ); ?>



<script type='text/javascript'>

    jQuery(document).ready(function($) {

        //TODO - remove php and use js vars

        $('#calendar').fullCalendar({

            header: {
                left: '<?php echo epl_get_element( 'cal_views', $shortcode_atts, null ); ?>',
                center: 'select-my', //CAN'T USE THE title in header
                right: 'today,prev,next'
            },

            selectMY: {
                years: 2
            },
            firstDay: EPL.firstDay,
            month:<?php echo (epl_get_element( 'start_month', $shortcode_atts, date_i18n( 'm' ) ) - 1); ?>,
            year:<?php echo epl_get_element( 'start_year', $shortcode_atts, date( 'Y' ) ); ?>,
            theme: <?php echo (epl_get_setting( 'epl_fullcalendar_options', 'epl_fullcalendar_theme', '' ) != '' ? 1 : 0); ?>, //change
            editable: false,
            allDaySlot: false,
            allDayDefault: false,
            defaultView: "<?php echo epl_get_element( 'cal_default_view', $shortcode_atts, 'month' ); ?>",
            minTime: 7,

            events: <?php echo $cal_dates; ?>,
            eventRender: function(event, element) {

                element.find('span.fc-event-title').html(element.find('span.fc-event-title').text());


            },
            eventMouseover:function( event, jsEvent, view ) {

                var title = event.title;
                var content = event.description;


<?php if ( epl_get_setting( 'epl_fullcalendar_options', 'epl_fullcalendar_enable_tooltip' ) == 10 ): ?>
                    $('body').append('<div id="fc_tooltip"><div class="tip_body">' + content + '</div></div>');

                    var ttp =  $('#fc_tooltip');
                    var ttp_h = ttp.height();

                    var el_offset= $(this).offset();

                    ttp.css('top', el_offset.top - ttp_h - 20 ).css('left', el_offset.left - 130 ).delay(300).fadeIn(200, function(){
                        var new_height = $('#fc_tooltip').height();
                        //alert(ttp_h);
                        //alert(new_height);
                        if(new_height != ttp_h){

                            $('#fc_tooltip').animate({

                                top: '-=' + (new_height - ttp_h)
                            },200);

                        };
                    });

<?php endif; ?>
            
                //ttp.fadeTo('10',0.9);

            },
            eventMouseout:function( event, jsEvent, view ) {


                $('#fc_tooltip').remove();


            },
                            
            loading: function(bool) {
                if (bool) epl_loader('show');
                else epl_loader('hide');
            }

        });


    });


</script>