<h4><?php epl_e('Click on a date to add it to the section that opened this calendar.'); ?></h4>
<div id='fc_calendar' style="width:250px;"></div>


<script type='text/javascript'>

    jQuery(document).ready(function($) {

        var calendar = $('#fc_calendar').fullCalendar({
            height: 200,
            aspectRatio: 1,
            firstDay: EPL.firstDay,
            header: {
                left: 'title',
                center: '',
                right: 'today prev,next'
            },

            selectable: true,
            selectHelper: true,
            select: function(start, end, allDay) {
                var date_format = EPL.date_format;
                //date_format = //date_format.replace(/m/, 'MM'); //m in full calendar is minutes
                var currentTime = new Date();
                //var _today = '<?php echo epl_fc_date_format(); ?>';
                
                var _today = $.fullCalendar.formatDate( currentTime, '<?php echo epl_fc_date_format(); ?>');
                var _date = $.fullCalendar.formatDate( start, '<?php echo epl_fc_date_format(); ?>');

                var par = '<?php echo $parent; ?>';

                if (_date) {

                    var _table = $('#epl_class_session_table');

                    if (par == 'epl_dates_table')
                        _table = $('#epl_dates_table');


                    if($("> tbody >tr", _table).size() == 1 && typeof(calendar._static) == 'undefined')
                    {
                        //if the first row, add the dates
                        var row = $(_table).find('tbody tr:first');
                        calendar._static = 0;

                    } else {
                        //new row
                        var row =  _EPL.add_table_row({
                            table: _table
                        });
                    }

                    if (par == 'epl_class_session_table'){
                        $('input[name^="_epl_class_session_date"]', row).val(_date);
                    } else {

                        //row = row.closest('table.epl_dates_row_table');

                        $('input[name^="_epl_start_date"]', row).val(_date);
                        $('input[name^="_epl_end_date"]', row).val(_date);
                        $('input[name^="_epl_regis_start_date"]', row).val(_today);
                        $('input[name^="_epl_regis_end_date"]', row).val(_date);
                    }

                    calendar._static++;
                }
                //calendar.fullCalendar('unselect');
            },
            editable: true,

            loading: function(bool) {
                if (bool) epl_loader('show');
                else epl_loader('hide');
            }

        });

    });

</script>