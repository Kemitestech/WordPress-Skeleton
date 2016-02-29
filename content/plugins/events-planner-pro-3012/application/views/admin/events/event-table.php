<?php

$show_all = (epl_get_element( 'show_all', $_REQUEST ) == 1);
global $epl_fields;

$epl_fields['epl_option_fields']['_epl_event_status']['empty_row'] = 1;
$epl_fields['epl_option_fields']['_epl_event_status']['id'] = '_epl_event_status';
$event_status_dd = $this->epl_util->create_element( $epl_fields['epl_option_fields']['_epl_event_status'] );

?>


<script type="text/javascript" charset="utf-8">



    jQuery(document).ready(function($) {

        $('body').on('change', '#select_all_events', function() {
            var me = $(this);
            var par = $('table#DataTables_Table_0');
            $('.event_ids', par).prop('checked', me.prop('checked'));
            return false;
        });


        $('body').on('change', '#_epl_event_status', function() {
            var me = $(this);
            var what = 'change_event_status'; //me.prop('class');
            var val = me.val();
            var data = {
                'epl_action': 'bulk_action',
                'epl_controller': 'epl_event_manager',
                'do': 'bulk_action',
                'what':what ,
                /*'id': me.data('id'),
                'event_id': $('#event_id', me.parents('.info_row')).val(),
                'date_id': $('#date_id', me.parents('.info_row')).val(),
                'time_id': $('#time_id', me.parents('.info_row')).val(),*/
                'value': val,
            }
            data = $.param(data) + '&' + $('#epl_bulk_action_form').serialize();

            events_planner_do_ajax(data, function(r) {
                if(r.html != "")
                    alert(r.html);

            });

            if (what == 'change_event_status')
                me.val('');
            else
                me.prop('checked', false);

        });

        var now = new Date();
        var start_of_day = new Date(now.getFullYear(), now.getMonth(), now.getDate(), 0, 0, 0).getTime();
        var minDateFilter, maxDateFilter;
        minDateFilter = <?php echo $show_all ? 'null' : EPL_DATE; ?>;
        //var maxDateFilter =  new Date(minDateFilter + (7 * 24 * 60 * 60)).getTime();

        // Calendar functions init
        $.fn.dataTableExt.afnFiltering.push(
                function(oSettings, aData, iDataIndex) {

                    if (minDateFilter && !isNaN(minDateFilter)) {
                        if (aData[0] < minDateFilter) {

                            return false;
                        }
                    }

                    if (maxDateFilter && !isNaN(maxDateFilter)) {
                        if (aData[0] > maxDateFilter) {
                            return false;
                        }
                    }
                    return true;
                }
        );
        // Horizontal table init
        var oTable = $('.setup_dataTable').dataTable({
            'sDom': '<"events-filters"fW><"clear">rtlip',
            'bPaginate': true,
            'aaSorting': [[0, 'asc'], [4, 'asc']], // Sort on unix date AND time

            'bLengthChange': true,
            'bFilter': true,
            'bSort': true,
            'bInfo': true,
            'iDisplayLength': <?php echo apply_filters('epl_event_table_display_length',10);?>,
            'bAutoWidth': false,
            'oLanguage': {
                'sSearch': 'Search Keyword:',
                'sZeroRecords': 'No events found. Please expand your search.'

            },
            'sPaginationType': 'full_numbers',
            'oColumnFilterWidgets': {
                'aiExclude': [0, 3, 4, 5, 6, 7, 8, 10, 11],
                'sSeparator': ',',
                'bGroupTerms': true
            }/*,
             "fnFooterCallback": function ( nRow, aaData, iStart, iEnd, aiDisplay ) {
             
             //  Calculate Total Paid
             console.log(nRow);
             var total_paid = 0;
             var r;
             for (  i=0 ; i<aaData.length ; i++ )
             {
             r = Number(aaData[i][7].replace(/[^0-9\.]+/g,0))
             total_paid += r*1;
             }
             // Add to footer 
             var nCells = nRow.getElementsByTagName('th');
             nCells[7].innerHTML = total_paid.toFixed(2);
             }*/

        });
        oTable.fnSetColumnVis(0, false, false); // Hide unix Date
        oTable.fnSetColumnVis(1, false, false); // Hide categories
        oTable.fnSetColumnVis(9, false, false); // Hide Status
        //oTable.fnSetColumnVis( 8, false, false ); // Hide personnel
        //oTable.fnSetColumnVis( 9, false, false ); // Hide venues
        //oTable.fnSetColumnVis( 14, false, false); // Hide boat number
        //oTable.fnSetColumnVis( 14, false, false); // Hide event status

        $('.events-filters').prepend('<div class="dataTables_filter"><label class="column-label">Dates from:</label><input id="datepicker_min" class="filter-date"type="text"><label>to</label><input id="datepicker_max" class="filter-date"type="text"></div>');
        $('#DataTables_Table_0_wrapper .column-filter-widget-menus').prepend('<p class="column-label">Filter Events:</p>');
        $('.events-filters').append("<div class='' style='width:100%;clear:both;'><hr>Bulk Actions | Select All: <input type='checkbox' id='select_all_events'> Change Event Status: <?php echo str_replace("\n",'',$event_status_dd['field']); ?></div>");
        oTable.fnSortListener(document.getElementById('sorter1'), 0); // Sort on column 0, when 'sorter0' is clicked on
        oTable.fnSortListener(document.getElementById('sorter7'), 6); // Sort on column 7, when 'sorter7' is clicked on

        $('#datepicker_min').datepicker({
            changeMonth: true,
            changeYear: true,
            showButtonPanel: true,
            showOtherMonths: true,
            selectOtherMonths: true,
            "onSelect": function(date) {
                minDateFilter = (new Date(date).getTime() / 1000);
                oTable.fnDraw();
            }
        }).keyup(function() {
            minDateFilter = (new Date(date).getTime() / 1000);
            oTable.fnDraw();
        });
        $('#datepicker_max').datepicker({
            changeMonth: true,
            changeYear: true,
            showButtonPanel: true,
            showOtherMonths: true,
            selectOtherMonths: true,
            "onSelect": function(date) {
                maxDateFilter = (new Date(date).getTime() / 1000);
                oTable.fnDraw();
            }
        }).keyup(function() {
            maxDateFilter = (new Date(date).getTime() / 1000);
            oTable.fnDraw();
        });
        $('body').on('click', '.list_action_button', function() {
            var me = $(this);
            var _me = this;
            if (me.data('no_action'))
                return true;
            var url = me.prop('href');
            var modal = (me.data('load_in') == 'modal');
            url = url.split('?')[1];
            if (!modal && oTable.fnIsOpen(_me.parentNode.parentNode.parentNode)) {
                oTable.fnClose(_me.parentNode.parentNode.parentNode);
                me.parents('tr').removeClass('attendees-open');
                me.text(me.data('label'));
                return false;
            }

            events_planner_do_ajax(url, function(r) {
                //show_slide_down(r.html);

                if (modal) {
                    epl_modal.open({
                        content: r.html,
                        width: "700px"
                    });
                } else {
                    me.parents('tr').addClass('attendees-open');
                    me.text('Hide');
                    var row = oTable.fnOpen(
                            _me.parentNode.parentNode.parentNode,
                            r.html,
                            "info_row"
                            );
                    var new_table = $('table', row);
                    do_datatable(new_table);
                }
            });
            return false;
        });
        // Horizontal scrolling attendee table inits
        $('.info_row .attendee-table').dataTable({
            'sDom': '<"events-filters"f><"clear">rtli',
            'bSort': true,
            'bPaginate': false,
            "sScrollX": "100%",
            "sScrollXInner": "110%",
            "bScrollCollapse": true
        });
        function do_datatable(elem) {
            return jQuery(elem).dataTable({
                //"bJQueryUI": true,
                "bLengthChange": true,
                "bAutoWidth": false,
                "sPaginationType": "full_numbers",
                "iDisplayLength": 10,
                "lengthMenu": [ [10, 25, 50, -1], [10, 25, 50, "All"] ],
                //"sScrollX": "900px",
                //"sScrollXInner": "950px",
                //"bScrollCollapse": true,
                //"sDom": '<"dtTop"lfTrti>rt<"dtBottom"lp><"clear">',
                 "sDom": '<"dtTop"frtilTp>rt<"dtBottom"><"clear">',
                "oTableTools": {
                    "sSwfPath": "<?php echo EPL_FULL_URL; ?>swf/copy_csv_xls_pdf.swf",
                    "aButtons": [
                        "copy",
                        {
                            "sExtends": "csv",
                            "sTitle": custom_export_title(elem)
                        },
                        //"xls", //hmm, this downloads as csv
                        //"pdf",
                        "print",
                    ]
                }

            });
        }
        ;
        function custom_export_title(caller) {

            if (caller.prevObject[0] == 'undefined')
                return 'Filename';
            var par = $(caller.prevObject[0]);
            var title = $('h3', par).html();
            if (title !== undefined)
                return title.replace(/[^a-z0-9]/gi, '-')

            return 'Filename';
        }

        //Unhide content now that DOM is loaded and filters applied
        $("#loadpage").hide();
        
        $('.epl_del_event').click(function(){
            return confirm('Are you sure?');
        });
            
    });</script>

<style type="text/css" media="all">

    #container 							{ margin: 30px; width: 90%; }
    .wrap h2                            { margin-bottom: 10px; }

    #event_list_wrapper                 { position: relative; }

    .dataTables_wrapper 				{ float: left !important; }

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

    .dataTables_wrapper table thead tr th 			{ text-align: left; background: linear-gradient(to top, #ECECEC, #F9F9F9) repeat scroll 0 0 #F1F1F1; border-bottom: 1px solid #DFDFDF; border-top-color: #FFFFFF; padding: 8px; color: #21759B; font-family: Georgia; font-size: 14px; font-weight: 400; }
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
        -moz-border-bottom-colors: none;
        -moz-border-left-colors: none;
        -moz-border-right-colors: none;
        -moz-border-top-colors: none;
        background-color: #F5F5F5;
        background-image: -moz-linear-gradient(center top , #FFFFFF, #E6E6E6);
        background-repeat: repeat-x;
        border-color: #E6E6E6 #E6E6E6 #A2A2A2;
        border-image: none;
        border-radius: 3px;
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
    }

    a.paginate_button { font-size: 12px; }
    a.paginate_active { font-weight: bold; }

    .dataTables_wrapper .dataTables_length, .dataTables_wrapper .dataTables_info { float: left; clear: none; width: auto; line-height: 20px; margin-right: 10px;  }
    .dataTables_wrapper .dataTables_paginate { float: right; margin-top: 20px; line-height: 20px; }

    .info_row h4 { margin-bottom: -20px; color: #ccc; display: none; }

    .info_row .dataTables_filter                        { /*float: right;*/  }
    .info_row .dataTables_filter input                  {color:#333 !important; }

    .info_row .DTTT_container                           { margin-bottom: 0; }
    .info_row .DTTT_container a.DTTT_button             { background: none; box-shadow: none; color: #fff !important; font-size: 14px; border: 0; text-decoration: underline !important; padding: 4px 7px; margin-right: 3px; }

    table.epl_daily_schedule_table                      { color: #888; width: 100%; background: none; border-radius: 0; border: 0; border-collapse: collapse; border-bottom: 1px solid #444; }
    table.epl_daily_schedule_table thead tr th          { text-shadow: none; background: none; border: 0; border-bottom: 1px solid #444; color: #CCC; font-weight: bold;font-family: Helvetica, Arial, sans-serif; font-size: 12px; }
    table.epl_daily_schedule_table tfoot tr th          { text-shadow: none; background: none; border: 0; border-bottom: 1px solid #444; color: #CCC; font-weight: bold;font-family: Helvetica, Arial, sans-serif; font-size: 12px; }
    table.epl_daily_schedule_table tbody tr               {  border-bottom: 0px solid #3D3D3D;}
    table.epl_daily_schedule_table tbody tr.group_end               {  border-bottom: 1px solid #3D3D3D;}
    table.epl_daily_schedule_table tbody                {  }
    table.epl_daily_schedule_table tbody tr:hover td      { color: #fff !important; }
    table.epl_daily_schedule_table tbody tr td          { color: #ccc !important;font-size: 12px; border: none; padding: 5px 8px; }
    table.epl_daily_schedule_table tbody tr td a        { color: #F8F7D8; }
    table.epl_daily_schedule_table tbody tr.odd, 
    table.epl_daily_schedule_table tbody tr.even   { background: none; }
    td.sorting_1, td.sorting_2  { }

    .info_row .ui-buttonset                                         { margin-right: 0; padding-right: 8px; }
    .info_row .dataTables_info                                      { padding-left: 8px; }
    .info_row .dataTables_paginate                                  {  }
    .info_row .dataTables_info, .info_row .dataTables_paginate      { margin-top: 3px; padding-top: 0; }
    .info_row .dataTables_paginate a                                { float: left;color: #21759B; font-weight: normal; border: 0; }
    .info_row .dataTables_paginate a.ui-state-disabled              { color: #888 !important; font-size: 12px; }
    .info_row input { color: #eee !important; }

    body.DTTT_Print #wpcontent                                      { margin-left: 0; }
    body.DTTT_Print .dataTables_wrapper table                       { border: 0; }
    body.DTTT_Print table tbody tr td.info_row                      { background: #FFF !important; border-top: 0 !important; color: #000; padding: 0; }
    body.DTTT_Print table tbody tr td.info_row h3                   { margin-bottom: 20px; margin-left: 5px; display: block !important; color: #000; font-size: 16px; }
    body.DTTT_Print table.epl_daily_schedule_table                  { color: #000; border-bottom: 1px solid #000; background: #FFF; }
    body.DTTT_Print table.epl_daily_schedule_table thead tr th      { color: #000; border-bottom: 2px solid #000; padding: 5px; }
    body.DTTT_Print table.epl_daily_schedule_table tfoot tr th      { color: #000; border-bottom: 2px solid #000; padding: 5px; }
    body.DTTT_Print table.epl_daily_schedule_table thead tr th span { display: none; }
    body.DTTT_Print table.epl_daily_schedule_table tbody tr:hover   { color: #000; }
    body.DTTT_Print table.epl_daily_schedule_table tbody tr td      { border-bottom: 1px solid #555; padding: 10px 5px; color: #000 !important; }
    body.DTTT_Print table.epl_daily_schedule_table tbody tr td a    { color: #000; text-decoration: none; font-weight: bold; }

.dtTop {
        padding: 3px 10px;
        background: transparent;
        border-bottom: 1px solid #555;
        color: #fff;
        overflow: hidden;
        max-width: 1100px;
    }
</style>

<div class="wrap">

    <div class="icon32 icon32-posts-epl_event" id="icon-edit"><br></div>
    <h2><?php epl_e( 'Manage Events' ); ?> 
        <a class="add-new-h2" href="post-new.php?post_type=epl_event" target="_blank"><?php epl_e( 'Add New Event' ); ?></a>
        <?php if ( !$show_all ): ?>
            <a class="add-new-h2" href="<?php echo epl_get_url(); ?>&show_all=1" ><?php epl_e( 'Show All' ); ?></a>
        <?php endif; ?>
        <a class="add-new-h2" href="edit.php?post_type=epl_event" target="_blank"><?php epl_e( 'Legacy Event List' ); ?></a>

    </h2>
    <?php

    $epl = EPL_Base::get_instance();
    $ecm = EPL_common_model::get_instance();
    $erptm = EPL_report_model::get_instance();
    ?>
    <form id="epl_bulk_action_form">
    <div id="event_list_wrapper" class="" style="margin-bottom: 20px;">

        <div id="loadpage" style="position:absolute; left:0px; top:0; background-color:white; border:1px solid #efefef; border-radius:3px; height:100%; width:100%;"> 
            <p align="center" style="font-size: 14px; margin-top: 100px;">
                <img src="<?php echo EPL_FULL_URL; ?>/images/ajax-loader.gif"><br><br>
                <strong><?php epl_e( 'Loading Events' ); ?> ...</strong>
            </p>
        </div>
        <?php

        global $event_list;


        global $event_details, $event_fields, $current_att_count;


        $_from_date = epl_get_date_timestamp( epl_get_element( '_epl_from_date', $_POST ) );
        $_to_date = epl_get_date_timestamp( epl_get_element( '_epl_to_date', $_POST ) );

        $table_row = array();
        $base_url = admin_url( 'edit.php?post_type=epl_event' );

        $event_dates = array();
        /* custom event list loop */
        if ( $event_list->have_posts() ):

            while ( $event_list->have_posts() ) :

                $event_list->the_post();

                $event_id = get_the_ID();
                setup_event_details( $event_id, false, true );

                $no_date = false;

                if ( epl_is_empty_array( $event_details['_epl_start_date'] ) ) {

                    $event_details['_epl_start_date'] = array( EPL_DATE );
                    $no_date = true;
                }

                $ecm->get_current_att_count( $event_id );

                $counts = $erptm->get_attendee_counts( $event_id, true );

                $money_totals = $erptm->get_event_money_totals( $event_id );

                $event_edit_link = '';
                if ( epl_user_is_admin() )
                    $event_edit_link = epl_anchor( admin_url( "post.php?post=" . get_the_ID() . "&action=edit" ), get_the_title() );

                $date_specifc_time = epl_get_element( '_epl_date_specific_time', $event_details, array() );

                $_status = epl_get_event_status( true );
                $status_id = key( $_status );

                $status = current( $_status );
                $class = 'status_' . $status_id;
                $post_status = '';
                if ( $event_details['post_status'] != 'publish' )
                    $post_status = "<span class='epl_fr epl_font_bold epl_w50'>" . ucfirst( $event_details['post_status'] ) . "</span>";

                $formatted_status = "<span class='status $class'>&nbsp;&nbsp;</span>" . $post_status;

                foreach ( $event_details['_epl_start_date'] as $date_id => $date ):
                    $date = epl_get_date_timestamp( $date );
                    $temp_table_row = '';
                    $end_date = $event_details['_epl_end_date'][$date_id];

                    $unix_date = $date;

                    if ( $status_id == 3 ) {
                        if ( $date < EPL_DATE && $end_date > EPL_DATE ) {

                            $date = EPL_DATE;
                        }
                    }
                    $weekday = date_i18n( 'N', $date );
                    $price = current( ( array ) $event_details['_epl_price'] );
                    if ( $price <= 0 ) {
                        $print_price = "<span class='price_free'>Free</span>";
                    }
                    else {
                        $print_price = "<span class='price_amount'> " . epl_get_formatted_curr( $price, null, true ) . "</span>";
                    }

                    $start_time = current( ( array ) $event_details['_epl_start_time'] );

                    $date_capacity = $event_details['_epl_date_capacity'][$date_id];

                    $num_regis = 0;


                    if ( isset( $current_att_count['_total_att_' . get_the_ID() . "_date_{$date_id}"] ) )
                        $num_regis = epl_get_element( '_total_att_' . get_the_ID() . "_date_{$date_id}", $current_att_count, 0 );

                    $avail = $date_capacity - $num_regis;

                    if ( $avail <= 0 ) {
                        $open_spots = "<span class='spots_closed'>Sold Out</span>";
                    }
                    else {
                        $open_spots = "<span class='spots_open'>" . $avail . " Spots</span>";
                    }

                    if ( $avail <= 0 ) {
                        $register_button_text = "<span class='button_closed'>Sold<strong>Out</strong></span>";
                        if ( epl_is_ok_for_waitlist() && ($wl_spaces_left = epl_waitlist_spaces_open()) !== false ) {
                            $register_button_url = array( '_date_id' => $date_id, 'button_text' => "Waiting List", 'class' => 'epl_button button_waitlist' );
                        }
                    }
                    /* else if ( $avail <= 3 && $avail > 0 ) {
                      //$register_button = "<span class='button_closed'>". $avail ." Left!</span>"; //FIGURE HOW TO USE BUTTON AND BREAKS SOLD OUT
                      $register_button = get_the_register_button(null, false, array('_date_id'=>$date_id, 'button_text'=> "Book Now", 'class' => 'epl_button button_partial' ));
                      } */
                    else {
                        $register_button_url = array( '_date_id' => $date_id, 'button_text' => "Book Now" );
                    }


                    $date_note = epl_prefix( ' - ', epl_get_element_m( $date_id, '_epl_date_note', $event_details, '' ) );

                    if ( $no_date && epl_is_empty_array( $event_details['_epl_start_time'] ) ) {

                        $event_details['_epl_start_time'] = array( '' );
                    }

                    foreach ( $event_details['_epl_start_time'] as $time_id => $time ) {

                        if ( epl_is_date_level_time() && !epl_is_empty_array( $date_specifc_time ) && (!isset( $date_specifc_time[$time_id] ) || !isset( $date_specifc_time[$time_id][$date_id] )) )
                            continue;

                        $weekday_specific = epl_get_element_m( $time_id, '_epl_weekday_specific_time', $event_details, array() );

                        if ( !epl_is_empty_array( $weekday_specific ) && !isset( $weekday_specific[$weekday] ) )
                            continue;

                        $unix_time = $time != '' ? strtotime( $time, $date ) : $date;

                        if ( !$no_date && !$show_all && $unix_time < EPL_DATE )
                            continue;


                        $event_excerpt = get_the_excerpt();
                        $event_tooltip = '';
                        if ( $event_excerpt !== '' ) {
                            $event_tooltip = " <span class='tip event_tooltip' title='" . $event_excerpt . "'>i</span>";
                        }
                        //$event_title = epl_anchor($event_details['_epl_link'],get_the_title(),'_self','class="event_link"');
                        $event_title = get_the_title();
                        $event_categories = strip_tags( get_the_term_list( $event_id, 'epl_event_categories', '', ',', '' ) );
                        $event_participation = $event_details['_epl_participation'];
                        $event_ages = epl_get_element( '_epl_ages', $event_details, array() );
                        $event_dow = date_i18n( "D", $date );
                        $event_dom = date_i18n( "m/j/Y", $date );
                        $end_time = $event_details['_epl_end_time'][$time_id];
                        $event_venue_title = get_the_location_name();
                        $boat_number = "Boat " . epl_get_element( '_epl_boat_no', $event_details );
                        $event_personnel_name = "Leland";
                        //$event_ages_range = ageRange2( $event_ages );



                        $time_capacity = epl_get_element_m( $time_id, '_epl_time_capacity', $event_details );
                        $capacity = ($time_capacity) ? $time_capacity : ($date_capacity ? $date_capacity : epl_get_element_m( $date_id, '_epl_date_per_time_capacity', $event_details ));


                        $num_regis = 0;

                        $counts_day_key = $event_id . "_time_{$date_id}";
                        $counts_time_key = $event_id . "_time_{$date_id}_{$time_id}";

                        if ( isset( $counts['_total_att_' . $counts_time_key] ) )
                            $num_regis = epl_get_element( '_total_att_' . $counts_time_key, $counts, 0 );

                        $money_total = epl_get_formatted_curr( epl_get_element( '_money_total_' . $counts_time_key, $money_totals, 0.00 ) );

                        $avail-= $num_regis;

                        if ( $avail <= 0 ) {
                            $open_spots = "<span class='spots_closed'>Sold Out</span>";
                        }
                        else {
                            $open_spots = "<span class='spots_open'>" . $avail . " Spots</span>";
                        }

                        if ( $avail <= 0 ) {
                            $register_button_text = "<span class='button_closed'>Sold<strong>Out</strong></span>";
                            if ( epl_is_ok_for_waitlist() && ($wl_spaces_left = epl_waitlist_spaces_open()) !== false ) {
                                $register_button_url = array( '_date_id' => $date_id, 'button_text' => "Waiting List", 'class' => 'epl_button button_waitlist' );
                            }
                        }

                        $table_link_arr = array(
                            'epl_action' => 'view_names',
                            'epl_download_trigger' => 1,
                            'table_view' => 1,
                            'epl_controller' => 'epl_report_manager',
                            'event_id' => $event_id );

                        $dt_array = array(
                            'date_id' => $date_id,
                            'time_id' => $time_id,
                            'event_id' => $event_id
                        );

                        $table_link_arr = array_merge( $table_link_arr, $dt_array );

                        $load_att_link = add_query_arg( array_merge( $table_link_arr, $dt_array ) + array( 'names_only' => 1 ), $base_url );


                        $load_att_name_button = '<a href="' . $load_att_link . '" class="btn load_attendees list_action_button" title="Attendees" data-label="Att" href="#">Att</a>';
                        $table_link_arr[''] = 'view_names';

                        $load_att_link = add_query_arg( array_merge( $table_link_arr, $dt_array ), $base_url );
                        $load_att_full_data_button = '<a href="' . $load_att_link . '" class="btn load_attendees list_action_button" title="Full Data" data-label="Full" href="#">Full</a>';

                        $load_att_link = add_query_arg( array_merge( $table_link_arr, $dt_array ) + array( 'combined' => 1 ), $base_url );
                        $load_att_comb_data_button = '<a href="' . $load_att_link . '" class="btn load_attendees list_action_button" title="Combined" data-label="Comb." href="#">Comb.</a>';

                        $table_link_arr['epl_action'] = 'get_the_email_form';
                        $load_att_link = add_query_arg( array_merge( $table_link_arr, $dt_array ) + array( 'names_only' => 1 ), $base_url );

                        $email_form_link = '<a href="' . $load_att_link . '" class="btn load_attendees list_action_button" title="Email" data-label="Email" data-load_in="modal" href="#">Em</a>';


                        $add_regis_link = add_query_arg( array(
                            '_date_id' => $date_id,
                            '_time_id' => $time_id,
                            'event_id' => $event_id
                                ), admin_url( 'post-new.php?post_type=epl_registration' ) );

                        $delete_link = epl_anchor( get_delete_post_link(), '<img src="' . EPL_FULL_URL . '/images/delete.png" class="epl_fr epl_del_event" />', '_self' );

                        $add_regis_link = '<a href="' . $add_regis_link . '" class="btn load_attendees list_action_button" title="+ Att" data-no_action="1" target="_blank">+ Att</a>';

                        $temp_table_row = '';

                        $temp_table_row .= '<td>' . $unix_time . '</td>';

                        if ( $no_date ) {
                            $temp_table_row .= '<td>' . $event_categories . '</td>';
                            $temp_table_row .= '<td></td>';
                            $temp_table_row .= '<td>NO DATE</td>';
                            $temp_table_row .= '<td></td>';
                            $temp_table_row .= '<td class="title"><input type="checkbox" name="event_ids[' . $event_id . ']" class="event_ids" value="' . $event_id . '">&nbsp;&nbsp;' . $event_edit_link .   $date_note . $formatted_status . '</td>';
                            $temp_table_row .= '<td></td>';
                            $temp_table_row .= '<td></td>';
                            $temp_table_row .= '<td></td>';
                            $temp_table_row .= '<td>' . $status . '</td>';
                            $temp_table_row .= '<td>' . $event_id . ' ' . $delete_link . '</td>';
                        }
                        else {
                            $temp_table_row .= '<td>' . $event_categories . '</td>';
                            $temp_table_row .= '<td>' . $event_dow . '</td>';
                            $temp_table_row .= '<td>' . $event_dom . '</td>';
                            $temp_table_row .= '<td>' . $time . '</td>';
                            $temp_table_row .= '<td class="title"><input type="checkbox" name="event_ids[' . $event_id . ']" class="event_ids" value="' . $event_id . '">&nbsp;&nbsp;' . $event_edit_link .   $date_note . $formatted_status . '</td>';
                            $temp_table_row .= '<td>' . "{$num_regis}" . epl_prefix( ' / ', $capacity ) . '<div class="epl_action_button_wrapper" style="float:right;">' . ($num_regis > 0 ? $load_att_name_button . ' ' . $load_att_full_data_button . ' ' . $load_att_comb_data_button . ' ' . $email_form_link : '') . ' ' . $add_regis_link . '</div></td>';
                            $temp_table_row .= '<td>' . $money_total . '</td>';
                            $temp_table_row .= '<td></td>';
                            $temp_table_row .= '<td>' . $status . '</td>';
                            $temp_table_row .= '<td>' . $event_id . ' ' . $delete_link . '</td>';
                        }
                        //following columns will be hidden like ninjas by the Datatables dom filters
                        //$temp_table_row .= '<td><a href="#" class="btn" title="Edit Event">Ed</a><a href="#" class="btn" title="Reports">Re</a><a href="#" class="btn" title="Export CSV">Ex</a><a href="#" class="btn" title="Email Attendees">Em</a><a
                        //
                        //  href="#" class="btn" title="Notes">No</a><a href="#" class="btn" title="Add to Gcal/iCal">Ca</a></td>';

                        if ( $temp_table_row != '' )
                            $table_row[] = '<tr>' . $temp_table_row . '</tr>';
                    }


                endforeach;

            endwhile;
        endif;

        echo <<< EOT
<table id="" class="setup_dataTable wp-list-table widefat fixed posts" style="min-width:1100px;">
		<thead>
    		<tr role="row">

        		<th>Unix</th>
                <th>Categories</th>
        		<th class="day">Day</th>
        		<th class="date">Date</th>
        		<th class="time">Time</th>

        		<th class="title">Title</th>

        		<th class="att" style="width:240px;">Attendees</th>
        		<th class="att">Revenue</th>
                <th class="action"></th>
                <th class="action">Status</th>
                <th class="" style="width:75px;">Event ID</th>
                
            </tr>
		</thead>
                <tbody>
EOT;


        echo implode( $table_row );

        echo <<< EOT
        </tbody>
       </table>
EOT;

        wp_reset_query();
        ?>


    </div>
</form>
    <div style="float: right;margin-top: 20px;">

        <span class="status status_10">Cancelled</span>  
        <span class="status status_3">Ongoing</span>
        <span class="status status_2">Active (hidden)</span>
        <span class="status status_1">Active</span>
        <span class="status status_0">Inactive</span>

    </div>

</div>

<script>

    jQuery(document).ready(function($) {

        //$('.ttip').tipsy({gravity: 's', html: true});

    });

</script>

