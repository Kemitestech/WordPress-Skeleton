<?php

//this file will be used as a template for printing purposes.
?>
<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
        <title>Print</title>
        <meta name="description" content="">
            <meta name="viewport" content="width=device-width">
                <script src="//ajax.googleapis.com/ajax/libs/jquery/1.9.1/jquery.min.js"></script>
                <style>
                    table {
                        border-collapse: collapse;
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

                </style>

                <script>
        
                    jQuery(document).ready(function($){
                        $("a#hide_empty_columns").click(function(e){
                            e.preventDefault();
                            var table = $('body table');
            
                            $('th', table).each(function(i) {
                                var me = $(this);
                                var remove = 0;

                                var tds = $(this).parents('table').find('tr td:nth-child(' + (i + 1) + ')')
                                tds.each(function(j) { if (this.innerHTML == '') remove++; });

                                if (remove == ($('tr', table).length - 1)) {
                                    $(this).hide();
                                    tds.hide();
                                }
                            });
               
                        });


                    });
        
                </script>
                </head>
                <body>
                    <a href="#" id="hide_empty_columns">Hide empty columns</a>
                    <?php echo $content; ?>
                </body>
                </html>