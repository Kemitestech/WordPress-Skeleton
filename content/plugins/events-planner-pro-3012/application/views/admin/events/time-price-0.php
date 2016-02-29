<style>
    
    .price_specific_form_row
    {background-color: #ececec !important;}
    /*
     * CSS TOGGLE SWITCHES
     * Unlicense
     *
     * Ionuț Colceriu - ghinda.net
     * https://github.com/ghinda/css-toggle-switch
     *
    */
    /* Toggle Switches
    */
    /* Shared
    */
    /* Checkbox
    */
    /* Radio Switch
    */
    /* Hide by default
    */
    .switch-toggle a, .switch-light span span {
        display: none; }

    /* We can't test for a specific feature,
     * so we only target browsers with support for media queries.
    */
    @media only screen {
        /* Checkbox switch
        */
        /* Radio switch
        */
        /* Standalone Themes */
        /* Candy Theme
               * Based on the "Sort Switches / Toggles (PSD)" by Ormal Clarck
               * http://www.premiumpixels.com/freebies/sort-switches-toggles-psd/
        */
        /* Android Theme
        */
        /* iOS Theme
        */
        .switch-light {

            display: block;
            height: 30px;
            /* Outline the toggles when the inputs are focused
            */
            position: relative;
            overflow: visible;
            padding: 0;
            margin-left: 100px;
            /* Position the label over all the elements, except the slide-button (<a>)
                 * Clicking anywhere on the label will change the switch-state
            */
            /* Don't hide the input from screen-readers and keyboard access
            */ }
        .switch-light * {
            -webkit-box-sizing: border-box;
            -moz-box-sizing: border-box;
            box-sizing: border-box; }
        .switch-light a {
            display: block;
            -webkit-transition: all 0.3s ease-out;
            -moz-transition: all 0.3s ease-out;
            transition: all 0.3s ease-out; }
        .switch-light label, .switch-light > span {
            line-height: 30px;
            vertical-align: middle; }
        .switch-light input:focus ~ a, .switch-light input:focus + label {
            outline: 1px dotted #888888; }
        .switch-light label {
            position: relative;
            z-index: 3;
            display: block;
            width: 100%; }
        .switch-light input {
            position: absolute;
            opacity: 0;
            z-index: 5; }
        .switch-light input:checked ~ a {
            right: 0%; }
        .switch-light > span {
            position: absolute;
            left: -100px;
            width: 100%;
            margin: 0;
            padding-right: 100px;
            text-align: left; }
        .switch-light > span span {
            position: absolute;
            top: 0;
            left: 0;
            z-index: 5;
            display: block;
            width: 50%;
            margin-left: 100px;
            text-align: center; }
        .switch-light > span span:last-child {
            left: 50%; }
        .switch-light a {
            position: absolute;
            right: 50%;
            top: 0;
            z-index: 4;
            display: block;
            width: 50%;
            height: 100%;
            padding: 0; }

        .switch-toggle {
            width: 100px;
            display: block;
            height: 25px;
            /* Outline the toggles when the inputs are focused
            */
            position: relative;
            /* For callout panels in foundation
            */
            padding: 0 !important;
            /* Generate styles for the multiple states */ }
        .switch-toggle * {
            -webkit-box-sizing: border-box;
            -moz-box-sizing: border-box;
            box-sizing: border-box; }
        .switch-toggle a {
            display: block;
            -webkit-transition: all 0.3s ease-out;
            -moz-transition: all 0.3s ease-out;
            transition: all 0.3s ease-out; }
        .switch-toggle label, .switch-toggle > span {
            line-height: 25px;
            vertical-align: middle; }
        .switch-toggle input:focus ~ a, .switch-toggle input:focus + label {
            outline: 1px dotted #888888; }
        .switch-toggle input {
            position: absolute;
            opacity: 0; }
        .switch-toggle label {
            position: relative;
            z-index: 2;
            float: left;
            width: 50%;
            height: 100%;
            margin: 0;
            text-align: center; }
        .switch-toggle a {
            position: absolute;
            top: 0;
            left: 0;
            padding: 0;
            z-index: 1;
            width: 50%;
            height: 100%; }
        .switch-toggle input:last-of-type:checked ~ a {
            left: 50%; }
        .switch-toggle.switch-3 label, .switch-toggle.switch-3 a {
            width: 33.33333%; }
        .switch-toggle.switch-3 input:checked:nth-of-type(2) ~ a {
            left: 33.33333%; }
        .switch-toggle.switch-3 input:checked:last-of-type ~ a {
            left: 66.66667%; }
        .switch-toggle.switch-4 label, .switch-toggle.switch-4 a {
            width: 25%; }
        .switch-toggle.switch-4 input:checked:nth-of-type(2) ~ a {
            left: 25%; }
        .switch-toggle.switch-4 input:checked:nth-of-type(3) ~ a {
            left: 50%; }
        .switch-toggle.switch-4 input:checked:last-of-type ~ a {
            left: 75%; }
        .switch-toggle.switch-5 label, .switch-toggle.switch-5 a {
            width: 20%; }
        .switch-toggle.switch-5 input:checked:nth-of-type(2) ~ a {
            left: 20%; }
        .switch-toggle.switch-5 input:checked:nth-of-type(3) ~ a {
            left: 40%; }
        .switch-toggle.switch-5 input:checked:nth-of-type(4) ~ a {
            left: 60%; }
        .switch-toggle.switch-5 input:checked:last-of-type ~ a {
            left: 80%; }
        .candy {
            /*background-color: #2d3035;*/
            border-radius: 3px;
            color: white;
            font-weight: normal;
            text-align: center;
            /*text-shadow: 1px 1px 1px #191b1e;*/
            box-shadow: inset 0 2px 6px rgba(0, 0, 0, 0.3), 0 1px 0px rgba(255, 255, 255, 0.2); }
        .candy label {
            color: #818181;
            -webkit-transition: color 0.2s ease-out;
            -moz-transition: color 0.2s ease-out;
            transition: color 0.2s ease-out; }
        .candy input:checked + label {
            color: #333333;
            text-shadow: 0 1px 0 rgba(255, 255, 255, 0.5); }
        .candy a {
            border: 1px solid #9FBB56;
            background-color: #70c66b;
            border-radius: 3px;
            background-image: -webkit-linear-gradient(top, rgba(255, 255, 255, 0.2), rgba(0, 0, 0, 0));
            background-image: linear-gradient(to  bottom, rgba(255, 255, 255, 0.2), rgba(0, 0, 0, 0));
            box-shadow: 0 1px 1px rgba(0, 0, 0, 0.2), inset 0 1px 1px rgba(255, 255, 255, 0.45); }
        .candy > span {
            color: #333333;
            text-shadow: none; }
        .candy span {
            color: white; }
        .candy.blue a {
            background-color: #38a3d4; }
        .candy.yellow a {
            background-color: #D7EC64; }

    }

    /* Bugfix for older Webkit, including mobile Webkit. Adapted from
     * http://css-tricks.com/webkit-sibling-bug/
     *
     * Improved by @seantimm, to fix memory use issues
     * https://github.com/zurb/foundation/pull/2725
    */
    @media only screen and (-webkit-min-device-pixel-ratio: 0) and (max-device-width: 480px) {
        .switch-light, .switch-toggle {
            -webkit-animation: webkitSiblingBugfix infinite 1s; } }
    @media only screen and (-webkit-min-device-pixel-ratio: 1.5) {
        .switch-light, .switch-toggle {
            -webkit-animation: none 0; } }

    @-webkit-keyframes webkitSiblingBugfix {
        from {
        position: relative; }

    to {
        position: relative; } }
    </style>
    <script>
        //TODO - move to event management specific js file
        jQuery(document).ready(function($){
            //will switch to .on down the road

            function date_specific_dd_populate(){

                var avail_dates = $('.epl_dates_row_table input[name^="_epl_start_date"]');

                $('.date_specific_selector, .date_specific_price_selector')
                .html($('<option>', { value : '' })
                .text('Date Specific?'));

                //date_specific_selector

                $(avail_dates).each(function(){
                    var me = $(this);
                    var patrn = /\[\w+\]|\[\]/g;
                    var tmp_name = me.attr('name');

                    var new_name = patrn.exec(tmp_name);

                    $('.date_specific_selector, .date_specific_price_selector')
                    .append($('<option>', { value : new_name })
                    .text(me.val()));

                });

            };

            function time_specific_dd_populate(){

                var avail_times = $('.epl_time_row_table input[name^="_epl_start_time"]');

                $('.time_specific_price_selector')
                .html($('<option>', { value : '' })
                .text('Time Specific?'));

                //date_specific_selector

                $(avail_times).each(function(){
                    var me = $(this);
                    var patrn = /\[\w+\]|\[\]/g;
                    var tmp_name = me.attr('name');

                    var new_name = patrn.exec(tmp_name);

                    $('.time_specific_price_selector')
                    .append($('<option>', { value : new_name })
                    .text(me.val()));

                });

            };
            $('body').on('click', '#epl_prices_table .add_table_row', function(){

           
                price_name_dd_populate();
           
                return false;
            });
        
            $('body').on('click', '.price_to_offset', function(){
                price_name_dd_populate($(this));
            });

            function price_name_dd_populate(caller){

                var avail_prices = $('.epl_prices_row_table input[name^="_epl_price_name"]');

                $(avail_prices).each(function(){
                    var me = $(this);
                
                    if(me == caller) {

                        var patrn =  /(?!\[)\w+(?=\])/i;
                        var tmp_name = me.attr('name');

                        var field_id = patrn.exec(tmp_name);

                        $('.price_to_offset').each(function(){
                            var dd = $(this);
                    
                            var exists = $('option[value='+field_id+']',dd);
                            //if doesn't exist, add, else, change the text since the price name may change.
                            if(exists.length == 0){
                                dd.append($('<option>', { value : field_id }).text(field_id+me.val()));
                            } else { exists.text(me.val())}

                       
                        });
                    }
                });

            };

            /* refactor */
            $('body').on('change', '.date_specific_selector, .date_specific_price_selector', function(){
                var me = $(this);

                var type = 'time';
                var par_selector = '_epl_start_time';
            
                if(me.hasClass('date_specific_price_selector')){

                    var type = 'price';
                    var par_selector = '_epl_price';

                }

                var cont = me.parents('tr');
                var patrn = /\[\w+\]|\[\]/g;
                var v = me.val();

                var par_id = $('input[name^="'+ par_selector + '"]', cont).prop('name');
                var par_id = patrn.exec(par_id);
                var ins = '<input type="text" style="display:inlline;" readonly="readonly" name="_epl_date_specific_'+ type + par_id + v + '" value="' + $('option:selected', me).text() + '"/>' ;
                $('td.date_specific', cont).append(ins);
                me.val('');
            });
            $('body').on('change', '.time_specific_price_selector', function(){
                var me = $(this);

                var type = 'price';
                var par_selector = '_epl_price';
            
                var cont = me.parents('tr');
                var patrn = /\[\w+\]|\[\]/g;
                var v = me.val();

                var par_id = $('input[name^="'+ par_selector + '"]', cont).prop('name');
                var par_id = patrn.exec(par_id);
                var ins = '<input type="text" style="display:inlline;" readonly="readonly" name="_epl_time_specific_'+ type + par_id + v + '" value="' + $('option:selected', me).text() + '"/>' ;
                $('td.time_specific', cont).append(ins);
                me.val('');
            });


            $('body').on('click', '.date_specific_selector_refresh', function(){
                date_specific_dd_populate();
            });
            $('body').on('click', '.time_specific_selector_refresh', function(){
                time_specific_dd_populate();
            });
            $('body').on('dblclick', 'input[name^="_epl_date_specific_"],input[name^="_epl_time_specific_"]', function(){
                $(this).remove();
                $('#fc_tooltip').remove();
            });
            $('body').on('mouseover', 'input[name^="_epl_date_specific_"],input[name^="_epl_time_specific_"]', function(){
                $('body').append('<div id="fc_tooltip"><div class="tip_body">Doublclick to delete</div></div>');

                var ttp =  $('#fc_tooltip');
                var ttp_h = ttp.height();

                var el_offset= $(this).offset();

                ttp.css('top', el_offset.top - ttp_h - 20 ).css('left', el_offset.left - 130 ).delay(300).fadeIn(200, function(){
                    var new_height = $('#fc_tooltip').height();
                    //alert(ttp_h);
                    //alert(new_height);
                    if(new_height != ttp_h){

                        $('#fc_tooltip').animate({

                            top: '-=' + (new_height - ttp_h)
                        },200);

                    };
                });
            }).on('mouseout',function(){$('#fc_tooltip').remove()});

            //_epl_date_specific_time
        });

    </script>



    <table class="epl_form_data_table  epl_w800" cellspacing ="0" id="event_times_table">
    <thead>
    <th colspan="8"><?php epl_e( 'Times' ); ?> (<?php echo epl_e( 'optional' ); ?>)</th>

</thead>
<tfoot>
    <tr>
        <td colspan="7">
            <a href="#" class="add_table_row"><img src ="<?php echo EPL_FULL_URL ?>images/add.png" /></a>
        </td>
    </tr>
</tfoot>
<tbody class="events_planner_tbody">

    <?php

    foreach ( $time_fields as $time_field_key => $time_field_row ) :
        ?>

        <tr class="copy_">
            <td><div class="handle"></div></td>
            <?php

            //echo $row;
            ?>


            <td>

                <table class="epl_time_row_table epl_form_data_table" cellspacing ="0">


                    <thead>
                        <tr>
                            <th><?php echo $time_field_row['_epl_start_time']['label']; ?></th>
                            <th><?php echo $time_field_row['_epl_end_time']['label']; ?></th>

                            <?php if ( epl_is_addon_active( '_epl_atp' ) ): //do not deacitvate, will not work   ?>
                                <th><?php echo $time_field_row['_epl_regis_endtime']['label']; ?></th>
                                <th><?php echo $time_field_row['_epl_time_capacity']['label']; ?></th>
                            <?php endif; ?>
                            <th><?php echo $time_field_row['_epl_time_hide']['label']; ?></th>
                            <?php do_action( 'epl_admin_view_event_time_price_0_header_row', $time_field_key, $time_field_row ); ?>
                        </tr>
                    </thead>

                    <tbody>
                        <tr>

                            <td><?php echo $time_field_row['_epl_start_time']['field']; ?></td>
                            <td><?php echo $time_field_row['_epl_end_time']['field']; ?></td>

                            <?php if ( epl_is_addon_active( '_epl_atp' ) ): //do not deacitvate, will not work   ?>
                                <td><?php echo $time_field_row['_epl_regis_endtime']['field']; ?></td>
                                <td><?php echo $time_field_row['_epl_time_capacity']['field']; ?></td>

                            <?php endif; ?>
                            <td><?php echo $time_field_row['_epl_time_hide']['field']; ?></td>



                        </tr>
                        <?php if ( epl_is_addon_active( '_epl_atp' ) ): //do not deacitvate, will not work   ?>
                            <?php if ( epl_get_setting( 'epl_api_option_fields', 'epl_atp_enable_date_specific_time', 10 ) == 10 ): ?>
                                <tr class="time_row_two">
                                    <td colspan="2" style="vertical-align:middle;">

                                        <select class="date_specific_selector"><option><?php epl_e( 'Date specific?' ); ?></option>

                                        </select> <img src="<?php echo EPL_FULL_URL; ?>images/arrow_refresh.png" style="cursor:pointer;" class="date_specific_selector_refresh" />

                                    </td>
                                    <td class="date_specific" colspan="4"><?php echo epl_get_element( 'field', epl_get_element( '_epl_date_specific_time', $time_field_row ) ); ?></td>
                                </tr>
                            <?php endif; ?>

                            <tr class="time_row_three">
                                <td colspan="6" style="vertical-align:middle;">
                                    <?php echo epl_get_element( 'field', epl_get_element( '_epl_weekday_specific_time', $time_field_row ) ); ?>


                                </td>

                            </tr>
                            <tr class="time_row_four">
                                <td colspan="7" style="vertical-align:middle;">

                                    <?php echo epl_get_element( 'label', epl_get_element( '_epl_time_note', $time_field_row ) ); ?>
                                    <?php echo epl_get_element( 'field', epl_get_element( '_epl_time_note', $time_field_row ) ); ?>
                                </td>

                            </tr> 

                            <?php do_action( 'epl_admin_view_event_time_price_0_time_data_row', $time_field_key, $time_field_row ); ?>

                        <?php endif; ?>
                    </tbody>


                </table>
            </td>






            <td>

                <div class="epl_action epl_delete"></div>
            </td>

        </tr>

    <?php endforeach; ?>

</tbody>


</table>
<div class="epl_warning epl_w500">
    <div class="epl_box_content">

        <?php epl_e( "At lease one price is required, even if it's a free event.  Enter price in 0.00 format." ); ?>
    </div>
</div>

<table class="epl_form_data_table" cellspacing ="0" id="epl_prices_table">
    <thead>
    <th colspan="10"><?php epl_e( 'Prices' ); ?></th>

</thead>
<tfoot>
    <tr><td colspan ="12">
            <a href="#" class="add_table_row"><img src ="<?php echo EPL_FULL_URL ?>images/add.png" /></a>
        </td></tr>
</tfoot>
<tbody class="events_planner_tbody">
    <?php

    foreach ( $price_fields as $price_key => $price_field_row ) :
        ?>

        <tr class="copy_">
            <td><div class="handle"></div></td>

            <td>

                <table class="epl_prices_row_table epl_form_data_table" cellspacing ="0">


                    <thead>
                        <tr>
                            <th><?php echo $price_field_row['_epl_price_name']['label']; ?></th>
                            <th><?php echo $price_field_row['_epl_price']['label']; ?></th>
                            <?php if ( epl_is_addon_active( 'DASFERWEQREWE' ) ): //do not deacitvate, will not work   ?>
                                <th><?php echo $price_field_row['_epl_member_price']['label']; ?></th>
                            <?php endif; ?>

                            <th><?php echo $price_field_row['_epl_price_min_qty']['label']; ?></th>
                            <th><?php echo $price_field_row['_epl_price_max_qty']['label']; ?></th>
                            <th><?php echo $price_field_row['_epl_price_zero_qty']['label']; ?></th>

                            <?php if ( epl_is_addon_active( '_epl_atp' ) ): //do not deacitvate, will not work   ?>

                                <th><?php echo $price_field_row['_epl_price_capacity']['label']; ?></th>
                                <th><?php echo $price_field_row['_epl_price_date_from']['label']; ?></th>
                                <th><?php echo $price_field_row['_epl_price_date_to']['label']; ?></th>
                                <th><?php echo $price_field_row['_epl_price_member_only']['label']; ?></th>
                            <?php endif; ?>
                            <th><?php echo $price_field_row['_epl_price_hide']['label']; ?></th>


                        </tr>
                    </thead>

                    <tbody>
                        <tr>

                            <td><?php echo $price_field_row['_epl_price_name']['field']; ?></td>
                            <td><?php echo $price_field_row['_epl_price']['field']; ?></td>
                            <?php if ( epl_is_addon_active( 'DASFERWEQREWE' ) ): //do not deacitvate, will not work   ?>
                                <td><?php echo $price_field_row['_epl_member_price']['field']; ?></td>
                            <?php endif; ?>
                            <td><?php echo $price_field_row['_epl_price_min_qty']['field']; ?></td>
                            <td><?php echo $price_field_row['_epl_price_max_qty']['field']; ?></td>
                            <td><?php echo $price_field_row['_epl_price_zero_qty']['field']; ?></td>

                            <?php if ( epl_is_addon_active( '_epl_atp' ) ): //do not deacitvate, will not work   ?>

                                <td><?php echo $price_field_row['_epl_price_capacity']['field']; ?></td>
                                <td><?php echo $price_field_row['_epl_price_date_from']['field']; ?></td>
                                <td><?php echo $price_field_row['_epl_price_date_to']['field']; ?></td>
                                <td><?php echo $price_field_row['_epl_price_member_only']['field']; ?></td>
                            <?php endif; ?>
                            <td><?php echo $price_field_row['_epl_price_hide']['field']; ?></td>



                        </tr>

                        <tr>
                            <td colspan="12"><?php echo $price_field_row['_epl_price_type']['label']; ?> <?php echo $price_field_row['_epl_price_type']['field']; ?>

                                <?php echo $price_field_row['_epl_price_discountable']['label']; ?> <?php echo $price_field_row['_epl_price_discountable']['field']; ?>

                                <?php echo $price_field_row['_epl_price_note']['field']; ?>
                            </td>
                        </tr>
                        <tr>
                            <td colspan="12">
                                <?php if ( epl_is_addon_active( '_epl_atp' ) ): //do not deacitvate, will not work   ?>
                                    <?php echo $price_field_row['_epl_price_pack_size']['label']; ?> <?php echo $price_field_row['_epl_price_pack_size']['field']; ?>
                                <?php endif; ?>
                                <?php if ( epl_is_addon_active( 'DASFERWEQREWE' ) ) : ?>

                                    <?php echo $price_field_row['_epl_price_pack_type']['label']; ?>

                                    <?php echo $price_field_row['_epl_price_pack_type']['field']; ?> <?php epl_e( 'If time based, length is' ); ?>
                                    <?php echo $price_field_row['_epl_price_pack_time_length']['field']; ?> <?php echo $price_field_row['_epl_price_pack_time_length_type']['field']; ?>
                                    <?php echo $price_field_row['_epl_price_membership_min_level']['label']; ?> <?php echo $price_field_row['_epl_price_membership_min_level']['field']; ?>

                                <?php endif; ?>
                            </td>
                        </tr>


                        <?php if ( isset( $price_field_row['_epl_price_surcharge_method']['field'] ) ): ?>
                            <tr>

                                <td colspan="12"><?php echo $price_field_row['_epl_price_surcharge_method']['label']; ?> <?php echo $price_field_row['_epl_price_surcharge_method']['field']; ?>
                                    <?php echo $price_field_row['_epl_price_surcharge_amount']['label']; ?> <?php echo $price_field_row['_epl_price_surcharge_amount']['field']; ?>
                                    <?php echo $price_field_row['_epl_price_surcharge_type']['label']; ?> <?php echo $price_field_row['_epl_price_surcharge_type']['field']; ?>
                                    <?php echo $price_field_row['_epl_price_surcharge_per']['label']; ?> <?php echo $price_field_row['_epl_price_surcharge_per']['field']; ?>

                                </td>


                            </tr>
                        <?php endif; ?>
                        <?php if ( epl_is_addon_active( '_epl_atp' ) ): //do not deacitvate, will not work   ?>

                            <?php if ( epl_get_setting( 'epl_api_option_fields', 'epl_atp_enable_date_specific_price', 10 ) == 10 ): ?>
                                <tr class="">
                                    <td colspan="1" style="vertical-align:middle;">

                                        <select class="date_specific_price_selector"><option><?php epl_e( 'Date specific?' ); ?></option>

                                        </select> <img src="<?php echo EPL_FULL_URL; ?>images/arrow_refresh.png" style="cursor:pointer;" class="date_specific_selector_refresh" />

                                    </td>
                                    <td class="date_specific" colspan="11"><?php echo epl_get_element( 'field', epl_get_element( '_epl_date_specific_price', $price_field_row ) ); ?></td>
                                </tr>
                            <?php endif; ?>
                            <?php if ( epl_get_setting( 'epl_api_option_fields', 'epl_atp_enable_time_specific_price', 10 ) == 101 ): ?>
                                <tr class="">
                                    <td colspan="1" style="vertical-align:middle;">

                                        <select class="time_specific_price_selector"><option><?php epl_e( 'Time specific?' ); ?></option>

                                        </select> <img src="<?php echo EPL_FULL_URL; ?>images/arrow_refresh.png" style="cursor:pointer;" class="time_specific_selector_refresh" />
                                        <br />EXPERIMENTAL!!!
                                    </td>
                                    <td class="time_specific" colspan="11"><?php echo epl_get_element( 'field', epl_get_element( '_epl_time_specific_price', $price_field_row ) ); ?></td>
                                </tr>
                            <?php endif; ?>
                            <?php if ( epl_get_setting( 'epl_api_option_fields', 'epl_atp_enable_table_price_type', 10 ) == 10 ): ?>
                                <tr class="epl_table_type_row">
                                    <td colspan="12"><?php epl_e( 'Optionally, when this price is selected, deduct' ); ?> <?php echo $price_field_row['_epl_price_offset_count']['field']; ?> <?php epl_e( 'from' ); ?> <?php echo $price_field_row['_epl_price_to_offset']['field']; ?>
                                </tr>
                            <?php endif; ?>
                            <?php if ( epl_get_setting( 'epl_api_option_fields', 'epl_atp_enable_price_specific_form', 10 ) == 10 ): ?>
                                <tr class="price_specific_form_row">
                                    <td colspan="12">
                                        <strong><?php echo $price_field_row['_epl_price_forms']['label']; ?></strong>

                                    </td>
                                </tr>
                                <tr class="price_specific_form_row">
                                    <td colspan="6"><?php echo $price_field_row['_epl_price_forms']['field']; ?></td>
                                    <td colspan="6"><?php echo $price_field_row['_epl_price_forms_per']['label']; ?>
                                        <?php echo $price_field_row['_epl_price_forms_per']['field']; ?></td>
                                </tr>
                            <?php endif; ?>
                        <?php endif; ?>
                        <?php do_action( 'epl_admin_view_event_time_price_0_price_data_row', $price_key, $price_field_row ); ?>
                    </tbody>

                </table>
            </td>
            <td>
                <div class="epl_action epl_delete"></div>

            </td>

        </tr>
    <?php endforeach; ?>
</tbody>


</table>

<script>

    jQuery(document).ready(function($){

        $("table#event_times_table > tbody").sortable();

        create_timepicker(jQuery('.timepicker'));
        create_datepicker(jQuery('.datepicker'));


    });

</script>
