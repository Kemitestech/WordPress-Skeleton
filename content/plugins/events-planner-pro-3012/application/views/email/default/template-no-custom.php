<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en">
    <head>
        <meta content="text/html; charset=utf-8" http-equiv="Content-Type" />
        <title>Registration Confirmation</title>
        <!--general stylesheet-->
        <style type="text/css">
            p { padding: 0; margin: 0; }
            h1, h2, h3, p, li { font-family: Helvetica Neue, Helvetica, Arial, sans-serif; }
            td { vertical-align:top;}
            ul, ol { margin: 0; padding: 0;}
            .heading {
                border-radius: 3px;
                -webkit-border-radius: 3px;
                -moz-border-radius: 3px;
                -khtml-border-radius: 3px;
                -icab-border-radius: 3px;
            }


            .epl_info_message, .epl_success_message, .epl_warning_message, .epl_error_message {
                border: 1px solid;
                margin: 10px 0px;
                padding:15px 10px 15px 50px;
                background-repeat: no-repeat;
                background-position: 10px center;
                border-radius: 3px;
                box-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
            }
            .epl_info_message {
                color: #00529B;
                background-color: #BDE5F8;
                /*background-image: url('info.png');*/
            }
            .epl_success_message {
                color: #4F8A10;
                background-color: #DFF2BF;
                /*background-image:url('success.png');*/
            }
            .epl_warning_message {
                color: #9F6000;
                background-color: #FEEFB3;
                /*background-image: url('warning.png');*/
            }
            .epl_error_message {
                color: #D8000C;
                background-color: #FFBABA;
                /*background-image: url('error.png');*/
            }

        </style>
    </head>
    <body marginheight="0" topmargin="0" marginwidth="0" leftmargin="0" background="" style="margin: 0px; background-color: #ffffff;" bgcolor="#ffffff">
        <table cellspacing="0" border="0" cellpadding="0" width="100%">
            <tbody>
                <tr valign="top">
                    <td><!--container-->
                        <table cellspacing="0" cellpadding="0" border="0" align="center" width="750" bgcolor="#ffffff">
                            <tbody>

                                <tr>
                                    <td valign="top" bgcolor="#ffffff" align="center">
                                        <table cellspacing="0" border="0" cellpadding="0" width="700">
                                            <tbody>

                                                <tr>
                                                    <td valign="top" height="34" style="height: 34px; border-bottom-color: #d6d6d6; border-bottom-width: 1px; border-bottom-style: solid;">


                                                          <div class="" style="margin:10px auto;border:1px solid #eee;padding: 10px;">
                                                         <div class="section" style="display:block;">
                                                                <div class="section_header" style="display:block;width: 595px;font-size:15px;font-weight: bold;"><?php epl_e( 'Purchase Details' ); ?></div>


                                                            </div>

                                                            <div class="section" style="margin:10px;">
                                                                
                                                                <?php 
                                                                $_style = 'style="clear:both;width:100%;margin:10px auto;border:1px solid #eee"';
                                                                
                                                                $_t = get_the_regis_dates_times_prices();

                                                                echo str_replace('class="epl_dates_times_prices_table"', $_style, $_t);

                                                                ?>

                                                                </div>



                                                            </div>
                                                            <div class="" style="margin:10px auto;border:1px solid #eee;padding: 10px;">

                                                            <?php

                                                                    if ( isset( $payment_details ) && $payment_details != '' && !epl_is_free_event() ){

                                                                        echo str_replace('class="epl_payment_details_table"', $_style, $payment_details);
                                                                    }

                                                                    global $email_regis_form;
                                                            echo $email_regis_form;

                                                             ?>
                                                            </div>
                                                        
                                                    </td>
                                                </tr>


                                            </tbody>
                                        </table>
                                    </td>
                                    <!--/content-->
                                </tr>
                            </tbody>
                        </table><!--/container-->
                    </td>
                </tr>
            </tbody>
        </table>
    </body>
</html>