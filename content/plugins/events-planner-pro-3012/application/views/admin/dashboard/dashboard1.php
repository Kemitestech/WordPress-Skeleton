<?php

$from = date_i18n( 'Y-m-d 00:00:00', strtotime( "15 days ago" ) );

$to = date( 'Y-m-d 23:59:59' );

$erptm = EPL_report_model::get_instance();
$cash_totals = $erptm->cash_totals( 'AND r.regis_date >= "' . $from . '" AND r.regis_date <= "' . $to . '"' );
?>

<div id="wpbody-content" style="min-height: 100%;">

    <div class="wrap">

        <h2><?php epl_e( 'Events Planner Dashboard' ); ?></h2>


        <div id="poststuff" style="min-height: 100%;position: relative;">

		<div class="demo-container" >
			<div id="placeholder" style="width:900px;height:500px;" class="demo-placeholder"></div>
		</div>
            <!-- Button trigger modal -->
            <script>
                
                jQuery(document).ready(function($){
                    $('.mdl').click(function(){
                        $('#myModal').modal();
                    
                        return false;
                    
                    });
                    
                    var d1 = [];
                    for (var i = 0; i < 14; i += 0.5) {
                        d1.push([i, Math.sin(i)]);
                    }

                    var d2 = [[0, 3], [4, 8], [8, 5], [9, 13]];

                    // A null signifies separate line segments

                    var d3 = [[0, 12], [7, 12], null, [7, 2.5], [12, 2.5]];

                    $.plot("#placeholder", [ d1, d2, d3 ]);

                    // Add the Flot version string to the footer

                    $(".wrap").append("Flot " + $.plot.version + " &ndash; ");
                    
                });
                
            </script>
            <button class="mdl btn btn-primary btn-lg">
                Launch demo modal
            </button>
            <a href="#" class="btn btn-lg btn-danger" data-toggle="popover" title="" data-content="And here's some amazing content. It's very engaging. right?" role="button" data-original-title="A Title">Click to toggle popover</a>

            <!-- Modal -->
            <div class="modal fade" id="myModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                            <h4 class="modal-title" id="myModalLabel">Modal title</h4>
                        </div>
                        <div class="modal-body">
                            this is the modal content so I can have anything I want in here.
                        </div>

                    </div><!-- /.modal-content -->
                </div><!-- /.modal-dialog -->
            </div><!-- /.modal -->

            <div class="panel panel-default">
                <div class="panel-heading">Cash - money that comes in on that day</div>
                <table class="table">
                    <thead>
                        <tr>
                            <th><?php epl_e( 'Date' ); ?></th>
                            <th><?php epl_e( '# Incomplete' ); ?></th>
                            <th><?php epl_e( '# Pending' ); ?></th>
                            <th><?php epl_e( '# Complete' ); ?></th>
                            <th><?php epl_e( 'Total Incomplete' ); ?></th>
                            <th><?php epl_e( 'Total Pending' ); ?></th>
                            <th><?php epl_e( 'Total Complete' ); ?></th>


                        </tr>
                    </thead>
                    <tbody>
                        <?php

                        foreach ( $cash_totals as $t ):
                            ?>
                            <tr>
                                <td><?php echo $t->m; ?>/<?php echo $t->d; ?>/<?php echo $t->y; ?></td>
                                <td><?php echo $t->cnt_incomplete; ?></td>
                                <td><?php echo $t->cnt_pending; ?></td>
                                <td><?php echo $t->cnt_complete; ?></td>
                                <td><?php echo $t->sum_incomplete; ?></td>
                                <td><?php echo $t->sum_pending; ?></td>
                                <td><?php echo $t->sum_complete; ?></td>
                            </tr>
<?php endforeach; ?>
                    </tbody>
                </table>

            </div>
            <div class="panel panel-success">
                <div class="panel-heading">Accrual - money that is earned that day</div>
                <div class="panel-body">
                    use the event list and calculate the revenue.  do the same as event list
                </div>
            </div>

            <div class="panel panel-default">
                <!-- Default panel contents -->
                <div class="panel-heading">Panel heading</div>
                <div class="panel-body">
                    <p>Some default panel content here. Nulla vitae elit libero, a pharetra augue. Aenean lacinia bibendum nulla sed consectetur. Aenean eu leo quam. Pellentesque ornare sem lacinia quam venenatis vestibulum. Nullam id dolor id nibh ultricies vehicula ut id elit.</p>
                </div>

                <!-- Table -->

            </div>

        </div>

    </div>

</div>

<style>


    .panel {
        margin-bottom: 20px;
        background-color: #ffffff;
        border: 1px solid transparent;
        border-radius: 4px;
        -webkit-box-shadow: 0 1px 1px rgba(0, 0, 0, 0.05);
        box-shadow: 0 1px 1px rgba(0, 0, 0, 0.05);
    }

    .panel-body {
        padding: 15px;
    }

    .panel-body:before,
    .panel-body:after {
        display: table;
        content: " ";
    }

    .panel-body:after {
        clear: both;
    }

    .panel-body:before,
    .panel-body:after {
        display: table;
        content: " ";
    }

    .panel-body:after {
        clear: both;
    }

    .panel > .list-group {
        margin-bottom: 0;
    }

    .panel > .list-group .list-group-item {
        border-width: 1px 0;
    }

    .panel > .list-group .list-group-item:first-child {
        border-top-right-radius: 0;
        border-top-left-radius: 0;
    }

    .panel > .list-group .list-group-item:last-child {
        border-bottom: 0;
    }

    .panel-heading + .list-group .list-group-item:first-child {
        border-top-width: 0;
    }

    .panel > .table,
    .panel > .table-responsive {
        margin-bottom: 0;
    }

    .panel > .panel-body + .table,
    .panel > .panel-body + .table-responsive {
        border-top: 1px solid #dddddd;
    }

    .panel > .table-bordered,
    .panel > .table-responsive > .table-bordered {
        border: 0;
    }

    .panel > .table-bordered > thead > tr > th:first-child,
    .panel > .table-responsive > .table-bordered > thead > tr > th:first-child,
    .panel > .table-bordered > tbody > tr > th:first-child,
    .panel > .table-responsive > .table-bordered > tbody > tr > th:first-child,
    .panel > .table-bordered > tfoot > tr > th:first-child,
    .panel > .table-responsive > .table-bordered > tfoot > tr > th:first-child,
    .panel > .table-bordered > thead > tr > td:first-child,
    .panel > .table-responsive > .table-bordered > thead > tr > td:first-child,
    .panel > .table-bordered > tbody > tr > td:first-child,
    .panel > .table-responsive > .table-bordered > tbody > tr > td:first-child,
    .panel > .table-bordered > tfoot > tr > td:first-child,
    .panel > .table-responsive > .table-bordered > tfoot > tr > td:first-child {
        border-left: 0;
    }

    .panel > .table-bordered > thead > tr > th:last-child,
    .panel > .table-responsive > .table-bordered > thead > tr > th:last-child,
    .panel > .table-bordered > tbody > tr > th:last-child,
    .panel > .table-responsive > .table-bordered > tbody > tr > th:last-child,
    .panel > .table-bordered > tfoot > tr > th:last-child,
    .panel > .table-responsive > .table-bordered > tfoot > tr > th:last-child,
    .panel > .table-bordered > thead > tr > td:last-child,
    .panel > .table-responsive > .table-bordered > thead > tr > td:last-child,
    .panel > .table-bordered > tbody > tr > td:last-child,
    .panel > .table-responsive > .table-bordered > tbody > tr > td:last-child,
    .panel > .table-bordered > tfoot > tr > td:last-child,
    .panel > .table-responsive > .table-bordered > tfoot > tr > td:last-child {
        border-right: 0;
    }

    .panel > .table-bordered > thead > tr:last-child > th,
    .panel > .table-responsive > .table-bordered > thead > tr:last-child > th,
    .panel > .table-bordered > tbody > tr:last-child > th,
    .panel > .table-responsive > .table-bordered > tbody > tr:last-child > th,
    .panel > .table-bordered > tfoot > tr:last-child > th,
    .panel > .table-responsive > .table-bordered > tfoot > tr:last-child > th,
    .panel > .table-bordered > thead > tr:last-child > td,
    .panel > .table-responsive > .table-bordered > thead > tr:last-child > td,
    .panel > .table-bordered > tbody > tr:last-child > td,
    .panel > .table-responsive > .table-bordered > tbody > tr:last-child > td,
    .panel > .table-bordered > tfoot > tr:last-child > td,
    .panel > .table-responsive > .table-bordered > tfoot > tr:last-child > td {
        border-bottom: 0;
    }

    .panel-heading {
        padding: 10px 15px;
        border-bottom: 1px solid transparent;
        border-top-right-radius: 3px;
        border-top-left-radius: 3px;
    }

    .panel-title {
        margin-top: 0;
        margin-bottom: 0;
        font-size: 16px;
    }

    .panel-title > a {
        color: inherit;
    }

    .panel-footer {
        padding: 10px 15px;
        background-color: #f5f5f5;
        border-top: 1px solid #dddddd;
        border-bottom-right-radius: 3px;
        border-bottom-left-radius: 3px;
    }

    .panel-group .panel {
        margin-bottom: 0;
        overflow: hidden;
        border-radius: 4px;
    }

    .panel-group .panel + .panel {
        margin-top: 5px;
    }

    .panel-group .panel-heading {
        border-bottom: 0;
    }

    .panel-group .panel-heading + .panel-collapse .panel-body {
        border-top: 1px solid #dddddd;
    }

    .panel-group .panel-footer {
        border-top: 0;
    }

    .panel-group .panel-footer + .panel-collapse .panel-body {
        border-bottom: 1px solid #dddddd;
    }

    .panel-default {
        border-color: #dddddd;
    }

    .panel-default > .panel-heading {
        color: #333333;
        background-color: #f5f5f5;
        border-color: #dddddd;
    }

    .panel-default > .panel-heading + .panel-collapse .panel-body {
        border-top-color: #dddddd;
    }

    .panel-default > .panel-footer + .panel-collapse .panel-body {
        border-bottom-color: #dddddd;
    }

    .panel-primary {
        border-color: #428bca;
    }

    .panel-primary > .panel-heading {
        color: #ffffff;
        background-color: #428bca;
        border-color: #428bca;
    }

    .panel-primary > .panel-heading + .panel-collapse .panel-body {
        border-top-color: #428bca;
    }

    .panel-primary > .panel-footer + .panel-collapse .panel-body {
        border-bottom-color: #428bca;
    }

    .panel-success {
        border-color: #d6e9c6;
    }

    .panel-success > .panel-heading {
        color: #468847;
        background-color: #dff0d8;
        border-color: #d6e9c6;
    }

    .panel-success > .panel-heading + .panel-collapse .panel-body {
        border-top-color: #d6e9c6;
    }

    .panel-success > .panel-footer + .panel-collapse .panel-body {
        border-bottom-color: #d6e9c6;
    }

    .panel-warning {
        border-color: #faebcc;
    }

    .panel-warning > .panel-heading {
        color: #c09853;
        background-color: #fcf8e3;
        border-color: #faebcc;
    }

    .panel-warning > .panel-heading + .panel-collapse .panel-body {
        border-top-color: #faebcc;
    }

    .panel-warning > .panel-footer + .panel-collapse .panel-body {
        border-bottom-color: #faebcc;
    }

    .panel-danger {
        border-color: #ebccd1;
    }

    .panel-danger > .panel-heading {
        color: #b94a48;
        background-color: #f2dede;
        border-color: #ebccd1;
    }

    .panel-danger > .panel-heading + .panel-collapse .panel-body {
        border-top-color: #ebccd1;
    }

    .panel-danger > .panel-footer + .panel-collapse .panel-body {
        border-bottom-color: #ebccd1;
    }

    .panel-info {
        border-color: #bce8f1;
    }

    .panel-info > .panel-heading {
        color: #3a87ad;
        background-color: #d9edf7;
        border-color: #bce8f1;
    }

    .panel-info > .panel-heading + .panel-collapse .panel-body {
        border-top-color: #bce8f1;
    }

    .panel-info > .panel-footer + .panel-collapse .panel-body {
        border-bottom-color: #bce8f1;
    }




</style>