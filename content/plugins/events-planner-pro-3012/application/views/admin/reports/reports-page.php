<style>

    .tabs {
        margin: 0;
        padding:0;
        border-bottom: 1px solid #ddd;
        line-height: 0.8em;
    }
    .tabs li {

        border: 1px solid #ccc;
        border-bottom: 0;
        /*background: #e4e4e4;*/
        display: inline-block;
        padding: 6px 10px;
        text-decoration: none;
        margin:0;
        margin-left: 3px;
        background: linear-gradient(to top, #ECECEC, #F9F9F9) repeat scroll 0 0 #F1F1F1;
    }

    .tabs li:hover {
        background-color: #f5f5f5;
    }

    .tabs a {
        text-decoration:none;
        padding-top: 1px;
        float: left;
        font-size: 16px;
        line-height: 0.8em;
    }

    .tabs a.active {
        color:red;
        font-weight: bold;

    }

    div.tab_content {
        padding:0;

    }

    .tab_delete {
        cursor: pointer;
    }

    .tab_delete {
        cursor: pointer;
        float: right;
        margin-left: 10px;
        font-size: 12px;
        font-weight: bold;

    }
 
    
    table.epl_standard_table th {
        border: none;
    }
    
    .dataTables_wrapper 				{ float: left !important;min-width: 1200px;padding: 0 3px; }

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
    .dataTables_wrapper table tbody tr td 					{ /*white-space:nowrap;*/ padding: 10px; }

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
        background-image: -moz-linear-gradient(center top , #FFFFFF, #E6E6E6) !important;
        background-repeat: repeat-x;
        border-color: #E6E6E6 #E6E6E6 #A2A2A2;
        border-image: none;
        border-style: solid;
        border-width: 1px;
        box-shadow: 0 1px 0 rgba(255, 255, 255, 0.2) inset, 0 1px 2px rgba(0, 0, 0, 0.05);
        color: #333333;
        cursor: pointer;
        display: inline-block;
        font-size: 11px;
        line-height: 12px;
        margin-bottom: 0;
        margin-top: -2px;
        text-align: center;
        text-shadow: 0 1px 1px rgba(255, 255, 255, 0.75);
        vertical-align: middle;
        text-decoration: none;
        border: 1px solid #ccc;
        overflow: hidden;
        padding:5px 10px;
    }

</style>

<div id="wpbody-content" style="min-height: 100%;">

    <div class="wrap">

        <h2><?php epl_e( 'Events Planner Reports' ); ?></h2>


        <div id="poststuff" style="min-height: 100%;position: relative;">

            <form id="report_form" ction="<?php echo epl_get_url(); ?>" method="post">

                <table class="epl_table" style="width:800px;border:1px solid #e4e4e4;">
                    <tr>
                        <td colspan="4">
                            <label>Date</label><br />
                            <input type="text" name="daterange" class ="daterange" size="30" /> 
                        </td>

                    <tr>
                        <td><label>Event</label><br />
                            <?php echo $event_list_dd['field']; ?>
                        </td>
                        <td><label>Status</label><br />
                            <?php echo $regis_status_dd['field']; ?>
                        </td>
                        <td><label>Report</label><br />
                            <select name="report_type" class="chzn-select">

                                <option value="transactions">Transactions</option>

                                <option value="full-report">Full</option>

                                <!--<option value="daily">Snapshot</option>-->

                            </select>
                        </td>
                        <td>
                            <br />
                            <input type="submit" name="Submit"  class="submit_form button-primary" value="<?php epl_e( 'Search' ); ?>" />

                            <input type="reset" id="form_reset"  class="button-secondary" value="<?php epl_e( 'Reset' ); ?>" />

                        </td>


                    </tr>
                </table>



                <div style ="width: 90%;padding: 0;margin-bottom:10px; border: 0px solid #eee;" class="epl_report_reponse">

                    <ul class='tabs'>

                    </ul>

                </div>




        </div>

    </div>

</div>

<div class="clear"></div>
<iframe id="csv_iframe" src="" style="/*display:none; visibility:hidden;*/"></iframe>

<script>

    function do_datatable(elem) {
        return jQuery(elem).dataTable({
            //"bJQueryUI": true,
            "sPaginationType": "full_numbers",
            "iDisplayLength": 20,
            //"sDom": 'Tlfrtip',
            "sDom": '<"dtTop"frtilTp>rt<"dtBottom"><"clear">',
            "oTableTools": {
                "sSwfPath": "<?php echo EPL_FULL_URL; ?>swf/copy_csv_xls_pdf.swf",
                "aButtons": [
                    "copy",
                    {
                        "sExtends": "csv",
                        "sTitle": 'Export.csv'
                    },
                    //"xls", //hmm, this downloads as csv
                    //"pdf",
                    "print",
                ]

            }

        });

    }
    ;

    jQuery(document).ready(function($) {


        $('.daterange').daterangepicker({
            posX: null,
            posY: null
                    // onOpen:function(){ if(inframe){ $(window.parent.document).find('iframe:eq(0)').width(700).height('35em');} }, 
                    //onClose: function(){ if(inframe){ $(window.parent.document).find('iframe:eq(0)').width('100%').height('5em');} }
        });


        tabnumber = 0;

        //add_tab('header', 'content');
        $('body').on('click', '.tab_delete', function() {
            var tab_id = $(this).prev('a').prop('id');
            $('#tab_li_' + tab_id).remove();
            $('#tab' + tab_id).remove();

        });

        function add_tab(header, content) {
            tabnumber++;

            //$('ul.tabs').append("<li id='tab_li_" + tabnumber + "'><a id='" + tabnumber + "' href='#tab" + tabnumber + "'>" + header + " </a><a class='tab_delete'><span>&nbsp;X&nbsp;</span></a></li>").after("<div id='tab" + tabnumber + "' class='tab_content'>" + content + "</div>");
            $('ul.tabs').append("<li id='tab_li_" + tabnumber + "'><a id='" + tabnumber + "' href='#tab" + tabnumber + "'>" + header + " </a><img class='tab_delete' img src='" + EPL.plugin_url + "images/cross.png' /></li>").after("<div id='tab" + tabnumber + "' class='tab_content'>" + content + "</div>");


            do_tabs();

        }
        ;

        function do_tabs() {

            $('ul.tabs').each(function() {
                var $active, $content, $links = $(this).find('a');
                $active = $($links.filter('[href="' + location.hash + '"]')[0] || $links.filter('[href="#tab' + tabnumber + '"]')[0]);

                $active.addClass('active');
                $content = $($active.attr('href'));

                $links.not($active).each(function() {
                    $(this).removeClass('active');
                    $($(this).attr('href')).hide();
                });

                $(this).on('click', 'a', function(e) {

                    $active.removeClass('active');
                    $content.hide();

                    $active = $(this);
                    $content = $($(this).attr('href'));

                    $active.addClass('active');
                    $content.show();

                    e.preventDefault();
                });
            });
        }


        //$(".chzn-select").chosen();


        $('#form_reset').click(function() {
            // $(".chzn-select").val('').trigger("liszt:updated");
        });



        $('.submit_form, .export_csv').click(function(e) {

            e.preventDefault();
            var me = $(this);

            var dl = me.hasClass('export_csv') ? 1 : 0;

            var data = $('form#report_form').serialize() + "&page=epl_report_manager&epl_controller=epl_report_manager&epl_action=run_report&download_trigger=" + dl;

            //return false;
            if (me.hasClass('export_csv')) {
                var newURL = window.location.protocol + "//" + window.location.host + window.location.pathname + window.location.search;

                $("#csv_iframe").attr("src", newURL + '&' + data);
                return false;
            }

            events_planner_do_ajax(data, function(r) {

                add_tab($('select[name="report_type"] option:selected').text(), r.html);

            });

            return false;

        });

        create_sortable('.epl_subform_table tbody');
    });

</script>