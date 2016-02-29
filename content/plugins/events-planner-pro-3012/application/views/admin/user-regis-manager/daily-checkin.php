<style>
        table.epl_standard_table th {
        border: none;
    }
    
    .dataTables_wrapper 				{ float: left !important;min-width: 1200px;overflow: hidden; }

    .dataTables_wrapper .events-filters 						{ 
        margin-bottom: 15px; 
        float: left; 
        width: 98%; 
        line-height: 22px; 
        border: 1px solid #DFDFDF; 
        border-radius: 3px; 
        padding: 13px; 
        text-align: left;
        background: linear-gradient(to top, #ECECEC, #F9F9F9) repeat scroll 0 0 #F1F1F1;


    }
    .dataTables_wrapper .events-filters .column-filter-widgets  { float: left; width: 100%; margin-top: 10px; }
    .dataTables_wrapper .events-filters .column-filter-widget-menus { float:left; width: 100%; }
    .dataTables_wrapper .events-filters .column-filter-widget-selected-terms { float:left; width: 100%; margin-top: 10px; padding: 0; border-radius: 3px; background: #f8f8f8; border: 1px solid #DFDFDF; }
    .dataTables_wrapper .events-filters a.filter-term 			{ text-decoration: none; margin: 5px !important; }
    .dataTables_wrapper .events-filters a.filter-term:hover 	{ text-decoration: line-through; }
    .dataTables_wrapper .dataTables_filter 						{ float: left; width: auto; margin-right: 10px; }
    .dataTables_wrapper .column-filter-widget 					{ float: left; margin-right: 10px; }
    .dataTables_wrapper .column-filter-widget select			{ margin-top: 2px; }
    .dataTables_wrapper .events-filters p.column-label 	        { float: left; margin: 0 10px 0 0; font-weight: bold; }
    .dataTables_wrapper .events-filters a 						{ float: left; margin-right: 10px; color: #21759B; font-weight: bold; }
    .dataTables_wrapper .events-filters label 					{ font-weight: bold; float: left; }
    .dataTables_wrapper .events-filters input.filter-date 		{ float: left; margin-right: 10px; margin-left: 10px; width: 90px; }

    .dataTables_wrapper table 									{ font-size: 12px; background-color: #F9F9F9; border-color: #DFDFDF; border-radius: 3px 3px 3px 3px; border-style: solid; border-width: 1px; border-spacing: 0; border-collapse: separate; }

    .dataTables_wrapper table tr.odd                            { background-color: #fcfcfc; }
    .dataTables_wrapper table tr.even                            { background-color: #f9f9f9; }

    .dataTables_wrapper table thead tr th, .dataTables_wrapper table tfoot tr th { font-family: Arial !important;text-align: left; background: linear-gradient(to top, #ECECEC, #F9F9F9) repeat scroll 0 0 #F1F1F1; border-bottom: 1px solid #DFDFDF; border-top-color: #FFFFFF; color: #21759B; font-family: Georgia; font-size: 14px; font-weight: 400; }
    .dataTables_wrapper table thead tr th.input                 { width: 2%; }
    .dataTables_wrapper table thead tr th.title				    { width: 35%; }
    .dataTables_wrapper table thead tr th.day                   { width: 5%; }
    .dataTables_wrapper table thead tr th.date                  { width: 10%; }
    .dataTables_wrapper table thead tr th.time                  { width: 10%; }
    .dataTables_wrapper table thead tr th.regis                 { width: 10%; }
    .dataTables_wrapper table thead tr th.fees                  { width: 5%; }
    .dataTables_wrapper table thead tr th.att                   { width: 12%; }
    .dataTables_wrapper table thead tr th.action                 {  }
    .dataTables_wrapper table thead tr th.sorting_asc           {font-weight: bold;  }


    .dataTables_wrapper table tbody tr.odd:hover td				{/* background-color:  #f5f5f5;*/}
    .dataTables_wrapper table tbody tr td 					{ /*white-space:nowrap; padding: 10px;*/ }

    .dataTables_wrapper table tbody tr td a 				{ color: #21759B; font-weight: bold; }

    .dataTables_wrapper table tbody tr td span.sublinks a 				{ display:none; font-weight: normal; margin-left: 5px; }
    .dataTables_wrapper table tbody tr td.title:hover span.sublinks a 	{ display: inline; }

    .dataTables_wrapper table tbody tr td.attendees a 		{  }

    .dataTables_wrapper table tbody tr.attendees-open td, .dataTables_wrapper table tbody tr.attendees-open th { background: #F8F7D8; }

    .dataTables_wrapper table tbody tr td.info_row 			{ background: #2E3035; color: #888; border-top: 1px solid #111; max-width: 1000px;overflow: auto; }
    .dataTables_wrapper table tbody tr td.info_row p 		{ line-height: 1.6em; }
    .dataTables_wrapper table tbody tr td.info_row h3       { display: none; }

    .ui-tooltip-dark  										{ background: #000; text-align: center; color: #FFF; border-radius: 3px; border-color: #000; }
    .ui-tooltip-dark .qtip-tip 								{ background: #000; }
    .ui-tooltip-dark .qtip-content 							{ text-align: center; }

    .dataTables_wrapper table tbody tr td .label 			{ margin-right: 5px; border-radius: 3px; background-color: #999999; color: #FFFFFF; display: inline-block; font-size: 11px; font-weight: normal; line-height: 10px; padding: 4px 4px; vertical-align: baseline; white-space: nowrap; }
    .dataTables_wrapper table tbody tr td .label.green 		{ background-color: #468847; }
    .dataTables_wrapper table tbody tr td .label.red 		{ background-color: #B94A48; }
    .dataTables_wrapper table tbody tr td .price.free 		{ border-bottom: 1px dotted; cursor: pointer; }
    .dataTables_wrapper table tbody tr td .price.multi		{ border-bottom: 1px dotted; cursor: pointer; }

    .dataTables_wrapper table tbody tr td .btn, a.paginate_button, a.paginate_active, .dataTables_wrapper .events-filters a.filter-term {
        background-color: #F5F5F5;
        background-image: -moz-linear-gradient(center top , #FFFFFF, #E6E6E6);
        background-repeat: repeat-x;
        border-color: #E6E6E6 #E6E6E6 #A2A2A2;
        border-image: none;
        border-style: solid;
        border-width: 1px;
        box-shadow: 0 1px 0 rgba(255, 255, 255, 0.2) inset, 0 1px 2px rgba(0, 0, 0, 0.05);
        color: #333333;
        cursor: pointer;
        display: inline-block;
        font-size: 10px;
        line-height: 12px;
        margin-bottom: 0;
        margin-top: -2px;
        padding: 4px 6px;
        text-align: center;
        text-shadow: 0 1px 1px rgba(255, 255, 255, 0.75);
        vertical-align: middle;
        text-decoration: none;
    }

    a.paginate_button { font-size: 12px; }
    a.paginate_active { font-weight: bold; }

    .dataTables_wrapper .dataTables_length, .dataTables_wrapper .dataTables_info { float: left; clear: none; width: auto; line-height: 20px; margin-right: 10px; }
    .dataTables_wrapper .dataTables_paginate { float: left; line-height: 20px; }

    .DTTT_container                           { margin-bottom: 0; float:left; }
    /*.DTTT_container a.DTTT_button             { background: none; box-shadow: none; color: #333 !important; font-size: 12px; border: 0; text-decoration: underline !important; padding: 5p 5px; margin-right: 3px; }*/

    .ui-buttonset                                         { margin-right: 0; padding-right: 8px; }
    .dataTables_info                                      { padding-left: 8px; }
    .dataTables_paginate                                  {  }
    .dataTables_info, .dataTables_paginate      { margin-top: 3px; padding-top: 0; }
    .dataTables_paginate a                                { background: none; color: #21759B; font-weight: normal; padding: 0 5px; text-decoration: none; border: 0; }
    .dataTables_paginate a.ui-state-disabled              { color: #888 !important; font-size: 12px; }
    
    .dtTop {
         background-color: #F5F5F5;
        background-image: -moz-linear-gradient(center top , #FFFFFF, #E6E6E6);
        background-repeat: repeat-x;
        border-color: #E6E6E6 #E6E6E6 #A2A2A2;
        border-image: none;
        border-style: solid;
        border-width: 1px;
        box-shadow: 0 1px 0 rgba(255, 255, 255, 0.2) inset, 0 1px 2px rgba(0, 0, 0, 0.05);
        color: #333333;
        cursor: pointer;
        display: inline-block;
        font-size: 10px;
        line-height: 12px;
        margin-bottom: 0;
        margin-top: -2px;
        text-align: center;
        text-shadow: 0 1px 1px rgba(255, 255, 255, 0.75);
        vertical-align: middle;
        text-decoration: none;
        /*border: 1px solid #ccc;*/
        overflow: hidden;
        padding:5px 10px;
    }
</style>
<div id="wpbody-content" style="overflow: auto;">

    <div class="wrap">

        <div id="icon-users" class="icon32"><br></div>
        <h2 class="nav-tab-wrapper">

            <?php

            $tabs = array(
                'epl_user_regis_manager' => epl__( 'Check In Page' ),
                //'epl_user_regis_list' => epl__( 'User Registrations' ),
            );
            $current = 'epl_user_regis_manager';
            if ( isset( $_GET['tab'] ) )
                $current = $_GET['tab'];

            $base_url = epl_get_url();

            foreach ( $tabs as $tab => $name ) {
                $class = ( $tab == $current ) ? ' nav-tab-active' : '';
                echo "<a class='nav-tab$class' href='" . add_query_arg( array( 'tab' => $tab ), $base_url ) . "'>$name</a>";
            }
            ?>
        </h2>

        <div id="poststuff">
            <?php

            $epl = EPL_Base::get_instance();
            $ecm = EPL_common_model::get_instance();
            $rm = $this->epl->load_model( 'epl-recurrence-model' );

            $curr_date = getdate( EPL_DATE );

            $curr_year = $curr_date['year'];
            $curr_month = $curr_date['mon'];
            $curr_day = $curr_date['mday'];
            ?>

            <style>
                table.epl_class_sd th {
                    font-size: 14px;
                    font-weight: 700;
                    color: #777;
                    border: none;
                    border-bottom: 1px solid #CCC;
                    border-top: 1px solid #CCC;
                    padding: 10px 5px;

                }
                .epl_class_sd tr>:first-child{
                    text-align:left !important;

                }

                .class_list, #epl_lookup_table_result {
                    border-collapse: collapse;
                }
                .class_list, 
                .class_list th, 
                .class_list td,
                #epl_lookup_table_result, 
                #epl_lookup_table_result th, 
                #epl_lookup_table_result td 
                {
                    border: 1px solid #eee;
                }
                .class_list tr:hover {
                    background-color: #f4f4f4;
                }

                #checkin_list td {
                    font-size: 14px;
                }

                #epl_lookup_table_result {
                    margin: 10px 0;
                }
            </style>

            <div id="class_list">

                <?php echo $content; ?>

            </div>

        </div>
    </div>
</div>

<script type="text/javascript">

    jQuery(document).ready(function($) {
        
        $('body').on('click','#refresh_class_list', function(){
           
            var vars = {
                        
                'daterange': $('#daterange').val()
                                                    
            };
            vars = $.param(vars);


            var data ="page=epl_user_regis_manager&epl_controller=epl_user_regis_manager&epl_action=upcoming_event_list&" +vars  ;
                     
            events_planner_do_ajax( data, function(r){

                $('#class_list').html(r.html);
  
            });
                                               
            return false;
           
            
            
        });
        
        
        /*$('.daterange').daterangepicker({
            posX: null,
            posY: null
            // onOpen:function(){ if(inframe){ $(window.parent.document).find('iframe:eq(0)').width(700).height('35em');} }, 
            //onClose: function(){ if(inframe){ $(window.parent.document).find('iframe:eq(0)').width('100%').height('5em');} }
        }); */
        


                                        				
        $('.column-filter-widgets').prepend('<div class="column-filter-widgets-label">Search By:</div>');
                                                
        $('body').on('click','a.sign_in_link', function(){
            
            var me=$(this);
            var vars = $.param(me.data());
            
            $('a.sign_in_link').removeClass('active_checkin_link');
            me.addClass('active_checkin_link');
            $('body').data('current_check_in',me );

            var data ="page=epl_user_regis_manager&epl_controller=epl_user_regis_manager&epl_action=get_list_of_registrants&" +vars  ;
            var target = $('#sign_in_name_section');             
            epl_block(target); 
                                         
            events_planner_do_ajax( data, function(r){
                target.unblock().html(r.html);
            });
                                               
            return false;
        });

        $('body').on('click', '.epl_user_check_in', function(){
            var me = $(this);
            var data = $.param(me.data());

            var current_check_in = $('body').data('current_check_in');
            
            $('a.epl_user_check_in').removeClass('active_checkin_link');
            me.addClass('active_checkin_link');
            
            data ="page=epl_user_regis_manager&epl_controller=epl_user_regis_manager&epl_action=checkin_user&" +data;

            var target = $('#sign_in_name_section');             
            epl_block(target);

            events_planner_do_ajax( data, function(r){

                target.html(r.html);
                current_check_in.parents('tr').find('.singed_in_count').html(r.signed_in_count).effect('highlight');
                            
            });
            return false;
        });
                          
        $('body').on('click','.epl_delete_checkin_record', function(){
            var me =$(this);
            if(confirm("Are you sure?")===false)
                return false;
            var vars = {
                'page':'epl_user_regis_manager',
                'epl_action':'delete_checkin_record',
                'epl_controller':'epl_user_regis_manager',
                'att_id':me.data('att_id')
                                                    
            };

            var current_check_in = $('body').data('current_check_in') 

            var data = $.param(vars);

            events_planner_do_ajax( data, function(r){

                //me.parents('tr').fadeOut().remove();
                //current_check_in.parents('tr').find('.singed_in_count').html(r.signed_in_count);
                current_check_in.trigger('click');

            });
                                               
            return false;           
                            

                   
        });
                                        				
    });

</script>
