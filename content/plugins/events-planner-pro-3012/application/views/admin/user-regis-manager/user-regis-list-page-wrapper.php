<div id="wpbody-content" style="overflow: hidden;">
    <style>
        .epl_user_regis_wrapper {
            margin:10px;border:1px solid #eee; padding:10px 20px;
            max-width: 700px;
        }


        .epl_user_regis_wrapper .epl_dates_times_prices_table,
        .epl_user_regis_wrapper .epl_payment_details_table {
            margin:30px auto;
            border: 1px solid #eee;
            width: 500px;
        }

        .epl_user_regis_wrapper .epl_dates_times_prices_table td,
        .epl_user_regis_wrapper .epl_payment_details_table td {
            padding: 7px;
        }
        .epl_user_regis_wrapper .epl_dates_times_prices_table tr:not(:last-child) td ,
        .epl_user_regis_wrapper .epl_payment_details_table tr:not(:last-child) td {
            border-bottom: 1px solid #eee;
        }

    </style>


    <script type="text/javascript">
        jQuery(document).ready(function($){
            //$(".epl_tabs").tabs({ fx: {opacity: 'toggle', duration:'fast' } });

        });
    </script>
    <div class="wrap">
        <h1><?php epl_e( 'Registration History' ); ?></h1>
        <div class="accordion">

            <?php echo $registrations; ?>
        </div>


    </div>

</div>


<div class="clear"></div>


<script>
    jQuery(document).ready(function($) {
        $( ".accordion" ).accordion({ collapsible: true });
    });
</script>