<div id="event_filter_wrapper" class="event_filter_wrapper" style="">
    <form id="event_list_search_form" method="post" action="">
        <div class="event_filters">


            <?php foreach ( $filters as $filter => $options ): ?>

                <div style="float:left;">
                    <?php echo epl_get_element( 'label', $options ); ?>
                    <?php echo epl_get_element( 'field', $options ); ?>
                </div>

            <?php endforeach; ?>
            <input type="hidden" name= "result_view" value="<?php echo $result_view; ?>" />
            <input type="hidden" name= "display_cols" value="<?php echo $shortcode_atts['display_cols']; ?>" />

            <div class="event_list_search_button_wrapper">
                <input type="submit" name="submit" value="<?php epl_e( 'Search' ); ?>" id="event_list_search_button" />
                <input type="reset" value="Reset" />
            </div>
        </div>
    </form>
</div>
<script>
    jQuery(document).ready(function($) {

        create_datepicker('.datepicker');

        $('#event_list_search_form').submit(function() {

            epl_filter_event_list();
            return false;
        });

        $('input,select', '#event_list_search_form').change(function() {

            //epl_filter_event_list();

        });

        function epl_filter_event_list() {

            var me = $(this);

            var par_form = $('#event_list_search_form');

            var data = par_form.serialize() + "&epl_action=event_list_search&epl_controller=epl_front";

            events_planner_do_ajax(data, function(r) {
                var d = r.html;
                $('#event_list_wrapper').html(d);

            });
            //window.history.pushState("string", "Title", data);
            return false;
        }
        ;
    });

</script>