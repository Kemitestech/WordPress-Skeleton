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
                                <tr><!--content-->
                                    <td valign="middle" bgcolor="#ebebeb" height="30" style="vertical-align: middle; border-bottom-color: #d6d6d6; border-bottom-width: 1px; border-bottom-style: solid;">
                                        <p style="font-size: 11px; font-weight: bold; color: #8a8a8a; text-align: center;">
                                            <?php epl_e( 'Registration Confirmation' ); ?>
                                        </p>
                                    </td>
                                </tr>
                                <tr>
                                    <td valign="top" bgcolor="#ffffff" align="center">
                                        <table cellspacing="0" border="0" cellpadding="0" width="700">
                                            <tbody>
                                                <tr>
                                                    <td valign="top" height="37" style="height: 37px;">&nbsp;</td>
                                                </tr>
                                                <tr>
                                                    <td valign="top" colspan="2" style="text-align: left;">
                                                        <h1 style="margin: 0; padding: 0; font-size: 22px; color: #fd2323; font-weight: bold;"><?php echo get_the_event_title(); ?> <?php echo get_the_organization_name()?epl_e( 'by' ) . ' ' .get_the_organization_name():'' ; ?></h1>

                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td valign="top" height="34" style="height: 34px; border-bottom-color: #d6d6d6; border-bottom-width: 1px; border-bottom-style: solid;">


                                                        <div class="" style="margin:10px auto;border:1px solid #eee;padding: 10px;">
                                                                <div class="section">
                                                                    <div style="width:600px;">

                                                                        <?php echo $eb; ?>


                                                                    </div>
                                                                </div>
                                                        </div>
                                                          <div class="" style="margin:10px auto;border:1px solid #eee;padding: 10px;">
                                                         <div class="section" style="display:block;">
                                                                <div class="section_header" style="display:block;width: 595px;font-size:15px;font-weight: bold;"><?php epl_e( 'Purchase Details' ); ?></div>
                                                                <div style="float:left;margin:10px;">
                                                                    <div class="event_name" style="font-size:17px;font-weight: bold;width: 590px"><?php echo get_the_event_title(); ?></div>
                                                                    <div><strong><?php epl_e( 'Regis. ID' ); ?>: <?php echo get_the_regis_id(); ?></strong></div>


                                                                </div>
                                                                <br /><br />
                                                                <?php if ( !epl_is_multi_location() &&  epl_get_event_property( '_epl_event_location', true) >0 ): ?>
                                                                <div class="" style="float: left;margin:10px;">
                                                                    <strong><?php epl_e( 'Location' ); ?></strong><br />
                                                                    <?php echo get_the_location_name(); ?><br />
                                                                    <?php echo get_the_location_address(); ?> <?php echo get_the_location_address2(); ?><br />
                                                                    <?php echo get_the_location_city(); ?>, <?php echo get_the_location_state(); ?> <?php echo get_the_location_zip(); ?><br />
                                                                </div>
                                                                <?php endif; ?>

                                                            </div>
                                                              <br /><br />
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

                                                                    if ( isset( $payment_details ) && $payment_details != '' && !epl_is_free_event() && !epl_is_zero_total())
                                                                        echo $payment_details;
                                                            ?>
                                                            <?php

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