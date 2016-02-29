<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml">
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
        <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1" />
        <title></title>

        <meta name="viewport" content="width=device-width">
            <script src="//ajax.googleapis.com/ajax/libs/jquery/1.9.1/jquery.min.js"></script>
            <style>
                /* reset */
                html, body, div, span, applet, object, iframe,
                h1, h2, h3, h4, h5, h6, p, blockquote, pre,
                a, abbr, acronym, address, big, cite, code,
                del, dfn, em, img, ins, kbd, q, s, samp,
                small, strike,  sub, sup, tt, var,
                u, i, center,
                dl, dt, dd, ol, ul, li,
                fieldset, form, label, legend,
                table, caption, tbody, tfoot, thead, tr, th, td,
                article, aside, canvas, details, embed, 
                figure, figcaption, footer, header, hgroup, 
                menu, nav, output, ruby, section, summary,
                time, mark, audio, video {
                    margin: 0;
                    padding: 0;
                    border: 0;
                    font-size: 100%;
                    font: inherit;
                    vertical-align: baseline;
                }
                /* HTML5 display-role reset for older browsers */
                article, aside, details, figcaption, figure, 
                footer, header, hgroup, menu, nav, section {
                    display: block;
                }
                body {
                    line-height: 1;
                    font-family: 'Helvetica', Arial,Verdana,sans-serif !important;
                }
                ol, ul {
                    list-style: none;
                }
                blockquote, q {
                    quotes: none;
                }
                blockquote:before, blockquote:after,
                q:before, q:after {
                    content: '';
                    content: none;
                }
                table {
                    border-collapse: collapse;
                    border-spacing: 0;
                }
                /* end reset */
                
                table {

                    width: 800px;


                }
                td, th {
                    padding: 2px 4px;
                    white-space: normal;
                }

                tr td:first-child {

                    /*min-width: 250px;*/
                }

                @media print {

                    #hide_empty_columns {
                        display: none;
                    }

                }

                #epl_invoice_wrapper {
                    margin:10px auto;
                    border: 1px solid #eee;
                    padding:10px;
                    width: 700px;
                    overflow: hidden;
                    font-size: 0.9em;
                    position: relative;
                    /*font-family: Arial,Verdana,sans-serif;*/
                }

                #epl_invoice_wrapper div {
                    outline: 0px solid #eee;
                }
                #epl_invoice_wrapper .invoice_section {
                    width:300px;
                    margin-bottom: 20px;
                    padding: 5px;
                    display: inline;

                }


                #epl_invoice_wrapper .fl_l{
                    float: left;
                }
                #epl_invoice_wrapper .fl_r{
                    float: right;
                }

                #epl_invoice_wrapper .invoice_section_full {
                    width: 99%;
                    margin-bottom: 20px;
                    clear: both;

                }
                #epl_invoice_wrapper table td {
                    vertical-align: top;
                    font-size: 0.9em !important;
                }

                #epl_invoice_wrapper .payment_dates{
                    /*table-layout: fixed;    */
                }
                #epl_invoice_wrapper .invoice_header,
                #epl_invoice_wrapper .regis_totals,
                #epl_invoice_wrapper .payment_dates,
                #epl_invoice_wrapper .regis_details {
                    border: 1px solid #f2f2f2;
                    width: 100%;
                }

                #epl_invoice_wrapper .invoice_header {
                    border: none;
                }
                #epl_invoice_wrapper .invoice_header td p {
                    margin:3px;
                }


                #epl_invoice_wrapper .regis_totals td,
                #epl_invoice_wrapper .regis_details td,
                #epl_invoice_wrapper .regis_details th{
                    border-bottom: 1px solid #eee;
                    background-color:  #f2f2f2;
                    text-align: left;
                    padding:5px 3px;
                }

                #epl_invoice_wrapper .regis_totals td,
                #epl_invoice_wrapper .regis_details td{
                    background-color: #fff;
                    padding:10px 5px;
                }

                /* #epl_invoice_wrapper .regis_totals tr td:nth-child(2) {
                     padding: 10px;
                     font-weight: bold;
                 }*/

                #epl_invoice_wrapper .payment_dates  {
                    border: none;

                }
                #epl_invoice_wrapper .payment_dates td {

                    font-weight: bold;
                    text-align: center;
                    vertical-align: top;
                    padding: 5px;


                }
                #epl_invoice_wrapper .payment_dates td.user_info {
                    text-align: left;
                    width: 450px;
                    font-weight: normal;
                    line-height: 1.4em;
                }
                #epl_invoice_wrapper .payment_dates td.invoice_info {
                    text-align: right;
                }
                #epl_invoice_wrapper .payment_dates td span {
                    float: right;
                    font-weight: normal;
                    color:#bbb;
                }
                #epl_invoice_wrapper td.ta_right {
                    text-align: right;
                }

            </style>

    </head>
    <body>

        <?php echo $content; ?>
    </body>
</html>