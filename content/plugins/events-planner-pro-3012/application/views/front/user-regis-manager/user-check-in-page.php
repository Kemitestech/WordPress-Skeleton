
<div style="padding:5px 10px;display: block;background-color: #eee;margin-bottom: 10px;">
    
   <?php echo $event_dd['label']; ?> <?php echo $event_dd['field']; ?> <button id="epl_load_event_check_in">Load</button>
    
</div>

<div id="epl_check_in_table_wrapper" style="padding:5px 10px;display: block;background-color: #eee;margin-bottom: 10px;">

Please select a class to begin.
</div>


<script>
    
    jQuery(document).ready(function($){
        $('body').on('click', '#epl_load_event_check_in', function(){

            var data = "epl_action=user_check_in_table&epl_controller=epl_front&event_id=" + $('#class_name_dd').val() + "&rand=" + Math.random();

    events_planner_do_ajax( data, function(r){
                var d = r.html;

                if(r.is_error == 0){
                    $('#epl_check_in_table_wrapper').html(r.html);
                } else
                    alert('Error');
                //alert(d);
            });

           return false;

        });
        $('body').on('click', 'div.epl_check_in', function(){

            var _me = this;
            var me = $(this);
            var state = this.getAttribute('data-state');

            var id = me.prop('id');

            var data = "epl_action=user_check_in&epl_controller=epl_front&id=" + id + '&state=' + state + "&rand=" + Math.random();

            events_planner_do_ajax( data, function(r){
                var d = r.html;

                if(r.is_error == 0){
                    me.toggleClass('epl_absent');
                    _me.setAttribute('data-state', Math.abs(state-1));
                } else
                    alert('Error');
                //alert(d);
            });
            return false;
            
        });
        
        
    });
    
</script>